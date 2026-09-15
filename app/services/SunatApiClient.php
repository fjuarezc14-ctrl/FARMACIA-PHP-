<?php
/**
 * SUNAT REST API Client - GENÉRICO Y REUTILIZABLE
 * ------------------------------------------------
 * Envía comprobantes electrónicos directamente a los servidores de SUNAT.
 * Soporta modo BETA (pruebas) y PRODUCCIÓN.
 *
 * REQUISITOS PARA PRODUCCIÓN:
 *  1. Certificado Digital (.p12) emitido por entidad certificadora acreditada
 *  2. Contraseña del certificado
 *  3. Usuario SOL (RUC + usuario secundario SUNAT)
 *  4. Clave SOL (clave del usuario secundario SUNAT)
 *
 * Uso básico:
 *   $client = new SunatApiClient($config);
 *   $resultado = $client->enviarComprobante($xmlFilePath);
 */
class SunatApiClient {

    private string $ruc;
    private string $solUsuario;
    private string $solClave;
    private string $certPath;
    private string $certPassword;
    private string $modo;       // 'beta' o 'produccion'
    private string $urlServicio;
    private bool   $habilitado;

    // URLs oficiales SUNAT
    const URL_BETA       = 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
    const URL_PRODUCCION = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';
    const URL_CONSULTA_BETA = 'https://e-beta.sunat.gob.pe/ol-ti-itconsultaunificadageneral/billService';
    const URL_CONSULTA_PRD  = 'https://e-factura.sunat.gob.pe/ol-ti-itconsultaunificadageneral/billService';

    /**
     * @param array $empresa  Array de configuración (desde BD o manual)
     *                        Claves requeridas:
     *                        - ruc['valor']
     *                        - sunat_sol_usuario['valor']  → RUCxxxxxxxx (usuario secundario)
     *                        - sunat_sol_clave['valor']    → clave SOL
     *                        - sunat_cert_path['valor']    → ruta al .p12
     *                        - sunat_cert_password['valor']→ contraseña del .p12
     *                        - sunat_modo['valor']         → 'beta' | 'produccion'
     *                        - sunat_habilitado['valor']   → '1' | '0'
     */
    public function __construct(array $empresa) {
        $this->ruc          = $empresa['ruc']['valor']                ?? '';
        $this->solUsuario   = $empresa['sunat_sol_usuario']['valor']  ?? '';
        $this->solClave     = $empresa['sunat_sol_clave']['valor']    ?? '';
        $this->certPath     = $empresa['sunat_cert_path']['valor']    ?? '';
        $this->certPassword = $empresa['sunat_cert_password']['valor']?? '';
        $this->modo         = $empresa['sunat_modo']['valor']         ?? 'beta';
        $this->habilitado   = ($empresa['sunat_habilitado']['valor']  ?? '0') === '1';

        // URL según modo
        $this->urlServicio = ($this->modo === 'produccion')
            ? (empty($empresa['sunat_url_produccion']['valor']) ? self::URL_PRODUCCION : $empresa['sunat_url_produccion']['valor'])
            : (empty($empresa['sunat_url_beta']['valor'])       ? self::URL_BETA       : $empresa['sunat_url_beta']['valor']);
    }

    /**
     * Indica si el cliente está habilitado para enviar a SUNAT.
     */
    public function estaHabilitado(): bool {
        return $this->habilitado
            && !empty($this->solUsuario)
            && !empty($this->solClave)
            && !empty($this->ruc);
    }

    /**
     * Envía un comprobante a SUNAT (Boleta o Factura).
     * Retorna un array con el resultado del CDR.
     *
     * @param  string $xmlFilePath  Ruta completa al archivo XML generado por SunatUblGenerator
     * @return array  ['success' => bool, 'codigo' => '', 'descripcion' => '', 'cdr' => '...base64']
     */
    public function enviarComprobante(string $xmlFilePath): array {
        if (!$this->estaHabilitado()) {
            return [
                'success'     => false,
                'codigo'      => 'CONFIG_ERROR',
                'descripcion' => 'El cliente SUNAT no está habilitado. Configure las credenciales SOL en el panel SUNAT.',
                'cdr'         => null
            ];
        }

        if (!file_exists($xmlFilePath)) {
            return [
                'success'     => false,
                'codigo'      => 'FILE_NOT_FOUND',
                'descripcion' => "Archivo XML no encontrado: {$xmlFilePath}",
                'cdr'         => null
            ];
        }

        // 1. Leer y comprimir el XML en ZIP (formato requerido SUNAT)
        $xmlContent  = file_get_contents($xmlFilePath);
        $xmlFilename = basename($xmlFilePath);
        $zipName     = str_replace('.xml', '.zip', $xmlFilename);
        $zipPath     = dirname($xmlFilePath) . '/' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['success' => false, 'codigo' => 'ZIP_ERROR', 'descripcion' => 'No se pudo crear el ZIP del comprobante.', 'cdr' => null];
        }
        $zip->addFromString($xmlFilename, $xmlContent);
        $zip->close();

        // 2. Construir el SOAP envelope
        $zipBase64   = base64_encode(file_get_contents($zipPath));
        $soapEnvelope = $this->buildSoapEnvelope($zipName, $zipBase64);

        // 3. Enviar via cURL con autenticación HTTP Basic
        $response = $this->sendSoapRequest($soapEnvelope);

        if ($response === false) {
            return ['success' => false, 'codigo' => 'CURL_ERROR', 'descripcion' => 'Error de conexión con SUNAT. Verifique su acceso a internet y el URL del servicio.', 'cdr' => null];
        }

        // 4. Parsear la respuesta SOAP (CDR)
        return $this->parseSoapResponse($response);
    }

    /**
     * Consulta el estado de un comprobante ya enviado.
     *
     * @param string $ruc     RUC del emisor
     * @param string $tipoDoc '01' = Factura, '03' = Boleta
     * @param string $serie   Ej: B001, F001
     * @param int    $numero  Correlativo numérico
     */
    public function consultarEstado(string $ruc, string $tipoDoc, string $serie, int $numero): array {
        if (!$this->estaHabilitado()) {
            return ['success' => false, 'descripcion' => 'Credenciales no configuradas.'];
        }

        $urlConsulta = ($this->modo === 'produccion') ? self::URL_CONSULTA_PRD : self::URL_CONSULTA_BETA;

        $soapBody = <<<SOAP
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:ser="http://service.sunat.gob.pe">
    <soapenv:Header/>
    <soapenv:Body>
        <ser:getStatusCdr>
            <rucComprobante>{$ruc}</rucComprobante>
            <tipoComprobante>{$tipoDoc}</tipoComprobante>
            <serieComprobante>{$serie}</serieComprobante>
            <numeroComprobante>{$numero}</numeroComprobante>
        </ser:getStatusCdr>
    </soapenv:Body>
</soapenv:Envelope>
SOAP;

        $response = $this->sendSoapRequest($soapBody, $urlConsulta);
        if ($response === false) {
            return ['success' => false, 'descripcion' => 'Error de conexión al consultar estado.'];
        }

        return $this->parseSoapResponse($response);
    }

    // ─── PRIVATE HELPERS ─────────────────────────────────────────────────────────

    private function buildSoapEnvelope(string $fileName, string $contentBase64): string {
        $userToken = base64_encode("{$this->solUsuario}:{$this->solClave}");
        return <<<SOAP
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                  xmlns:ser="http://service.sunat.gob.pe"
                  xmlns:xsd="http://www.w3.org/2001/XMLSchema"
                  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <soapenv:Header>
        <wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <wsse:UsernameToken>
                <wsse:Username>{$this->solUsuario}</wsse:Username>
                <wsse:Password>{$this->solClave}</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <ser:sendBill>
            <fileName>{$fileName}</fileName>
            <contentFile>{$contentBase64}</contentFile>
        </ser:sendBill>
    </soapenv:Body>
</soapenv:Envelope>
SOAP;
    }

    private function sendSoapRequest(string $soapBody, string $url = ''): string|false {
        if (empty($url)) $url = $this->urlServicio;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $soapBody,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: text/xml; charset=UTF-8',
                'SOAPAction: ""',
                'Content-Length: ' . strlen($soapBody),
            ],
            CURLOPT_USERPWD        => "{$this->solUsuario}:{$this->solClave}",
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => false, // En beta puede requerirse desactivar
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        // Si hay certificado configurado, usarlo para firmar (producción)
        if (!empty($this->certPath) && file_exists($this->certPath)) {
            curl_setopt($ch, CURLOPT_SSLCERT,         $this->certPath);
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD,   $this->certPassword);
            curl_setopt($ch, CURLOPT_SSLCERTTYPE,     'P12');
        }

        $response = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("SunatApiClient cURL error: {$error}");
            return false;
        }

        return $response;
    }

    private function parseSoapResponse(string $response): array {
        // Suprimir warnings de XML mal formado
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($response);

        if ($xml === false) {
            return [
                'success'     => false,
                'codigo'      => 'PARSE_ERROR',
                'descripcion' => 'No se pudo parsear la respuesta de SUNAT.',
                'raw'         => $response,
                'cdr'         => null
            ];
        }

        $xml->registerXPathNamespace('s',   'http://schemas.xmlsoap.org/soap/envelope/');
        $xml->registerXPathNamespace('ser', 'http://service.sunat.gob.pe');

        // Buscar el applicationResponse (CDR en base64)
        $appResponse = $xml->xpath('//applicationResponse');
        $cdrBase64   = !empty($appResponse) ? (string)$appResponse[0] : null;
        $cdrContent  = $cdrBase64 ? base64_decode($cdrBase64) : null;

        // Buscar faultstring (errores SOAP)
        $fault = $xml->xpath('//faultstring');
        if (!empty($fault)) {
            return [
                'success'     => false,
                'codigo'      => 'SOAP_FAULT',
                'descripcion' => (string)$fault[0],
                'cdr'         => null
            ];
        }

        // Extraer código y descripción del CDR si existe
        $codigoRespuesta = '0';
        $descripcion     = 'Comprobante aceptado por SUNAT';

        if ($cdrContent) {
            // El CDR también es un ZIP que contiene un XML de respuesta
            $tmpZip = sys_get_temp_dir() . '/cdr_' . time() . '.zip';
            file_put_contents($tmpZip, $cdrContent);
            $zCdr = new ZipArchive();
            if ($zCdr->open($tmpZip) === true) {
                for ($i = 0; $i < $zCdr->numFiles; $i++) {
                    $cdrXml = $zCdr->getFromIndex($i);
                    if ($cdrXml) {
                        $cdrParsed = simplexml_load_string($cdrXml);
                        if ($cdrParsed) {
                            $cdrParsed->registerXPathNamespace('ar', 'urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2');
                            $cdrParsed->registerXPathNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
                            $cdrParsed->registerXPathNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

                            $codes = $cdrParsed->xpath('//cac:DocumentResponse/cac:Response/cbc:ResponseCode');
                            $descs = $cdrParsed->xpath('//cac:DocumentResponse/cac:Response/cbc:Description');

                            if (!empty($codes)) $codigoRespuesta = (string)$codes[0];
                            if (!empty($descs)) $descripcion     = (string)$descs[0];
                        }
                    }
                }
                $zCdr->close();
            }
            @unlink($tmpZip);
        }

        return [
            'success'     => ($codigoRespuesta === '0'),
            'codigo'      => $codigoRespuesta,
            'descripcion' => $descripcion,
            'cdr'         => $cdrBase64
        ];
    }
}
