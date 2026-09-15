<?php
/**
 * SUNAT UBL 2.1 Generator - GENÉRICO Y REUTILIZABLE
 * ---------------------------------------------------
 * Funciona con cualquier empresa (botica, veterinaria, minimarket, etc.)
 * Solo requiere pasar el array $empresa con las claves correctas.
 *
 * Uso:
 *   require_once 'SunatUblGenerator.php';
 *   $xmlFile = SunatUblGenerator::generarXML($venta, $detalles, $empresa);
 */
class SunatUblGenerator {

    /**
     * Genera el archivo XML UBL 2.1 para Boleta o Factura Electrónica.
     *
     * @param  array  $venta     Cabecera de la venta (total, igv, tipo_comprobante, serie, etc.)
     * @param  array  $detalles  Líneas de detalle (nombre_comercial, cantidad, precio_unitario, subtotal)
     * @param  array  $empresa   Configuración de la empresa (clave => ['valor' => '...'])
     * @param  string $outputDir Directorio donde guardar el XML (opcional, usa el default si vacío)
     * @return string $filename  Nombre del archivo XML generado
     */
    public static function generarXML(array $venta, array $detalles, array $empresa, string $outputDir = ''): string {

        // ─── 1. DATOS DEL EMISOR (GENÉRICO) ─────────────────────────────────────
        // Busca nombre_empresa primero, luego nombre_botica como fallback (compatibilidad)
        $ruc          = self::cfg($empresa, ['ruc'], '20000000001');
        $razon_social = self::cfg($empresa, ['nombre_empresa', 'nombre_botica'], 'EMPRESA S.A.C.');
        $direccion    = self::cfg($empresa, ['direccion'], 'AV. PRINCIPAL 123');
        $ubigeo       = self::cfg($empresa, ['ubigeo'], '150101');  // Lima por defecto

        // ─── 2. DATOS DEL COMPROBANTE ─────────────────────────────────────────────
        $tipoDoc    = ($venta['tipo_comprobante'] === 'Factura') ? '01' : '03';
        $serie      = $venta['serie_comprobante'];
        $correlativo= $venta['num_comprobante'];
        $fechaEmision = isset($venta['fecha_venta']) ? date('Y-m-d', strtotime($venta['fecha_venta'])) : date('Y-m-d');
        $horaEmision  = isset($venta['fecha_venta']) ? date('H:i:s', strtotime($venta['fecha_venta'])) : date('H:i:s');
        $moneda     = 'PEN';

        // ─── 3. DATOS DEL CLIENTE ─────────────────────────────────────────────────
        $docCliente      = $venta['doc_cliente']  ?? '00000000';
        $nombreCliente   = $venta['cliente']       ?? 'PUBLICO GENERAL';
        $tipoDocCliente  = $venta['tipo_doc_cliente'] ?? '1'; // 1=DNI, 6=RUC, 0=Sin doc

        if ($tipoDoc === '01') {
            // Factura → obligatorio RUC del cliente
            $tipoDocCliente = '6';
            if (empty($docCliente) || strlen($docCliente) != 11) {
                $docCliente = $venta['doc_cliente'] ?? '20000000001';
            }
        } elseif ($nombreCliente === 'PUBLICO GENERAL' || empty($docCliente)) {
            $tipoDocCliente = '0'; // Sin documento
            $docCliente     = '00000000';
        }

        // ─── 4. TOTALES ───────────────────────────────────────────────────────────
        $mTotal    = (float)$venta['total'];
        $mIgv      = (float)$venta['igv'];
        $mSubtotal = (float)$venta['subtotal'];
        $mDesc     = (float)($venta['descuento'] ?? 0);

        $totalIgv  = number_format($mIgv,      2, '.', '');
        $totalVenta= number_format($mTotal,    2, '.', '');
        $subTotal  = number_format($mSubtotal, 2, '.', '');

        // Monto en letras (requerido SUNAT)
        $montoEnLetras = 'SON: ' . strtoupper(self::numeroALetras($mTotal)) . ' SOLES';

        // ─── 5. CONSTRUIR XML ─────────────────────────────────────────────────────
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $invoice = $dom->createElement('Invoice');
        $invoice->setAttribute('xmlns',      'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac',  'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc',  'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoice->setAttribute('xmlns:ccts', 'urn:un:unece:uncefact:documentation:2');
        $invoice->setAttribute('xmlns:ds',   'http://www.w3.org/2000/09/xmldsig#');
        $invoice->setAttribute('xmlns:ext',  'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $invoice->setAttribute('xmlns:qdt',  'urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2');
        $invoice->setAttribute('xmlns:udt',  'urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2');
        $dom->appendChild($invoice);

        // UBL Extensions (reservado para firma digital futura)
        $exts = $dom->createElement('ext:UBLExtensions');
        $ext  = $dom->createElement('ext:UBLExtension');
        $ext->appendChild($dom->createElement('ext:ExtensionContent', ''));
        $exts->appendChild($ext);
        $invoice->appendChild($exts);

        // Cabecera
        $invoice->appendChild($dom->createElement('cbc:UBLVersionID',       '2.1'));
        $invoice->appendChild($dom->createElement('cbc:CustomizationID',    '2.0'));
        $invoice->appendChild($dom->createElement('cbc:ID',                 "{$serie}-{$correlativo}"));
        $invoice->appendChild($dom->createElement('cbc:IssueDate',          $fechaEmision));
        $invoice->appendChild($dom->createElement('cbc:IssueTime',          $horaEmision));
        $tcNode = $dom->createElement('cbc:InvoiceTypeCode', $tipoDoc);
        $tcNode->setAttribute('listAgencyName',    'PE:SUNAT');
        $tcNode->setAttribute('listName',          'Tipo de Documento');
        $tcNode->setAttribute('listURI',           'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01');
        $invoice->appendChild($tcNode);

        $noteNode = $dom->createElement('cbc:Note', $montoEnLetras);
        $noteNode->setAttribute('languageLocaleID', '1000');
        $invoice->appendChild($noteNode);
        $invoice->appendChild($dom->createElement('cbc:DocumentCurrencyCode', $moneda));

        // ─── EMISOR ───────────────────────────────────────────────────────────────
        $supplier = $dom->createElement('cac:AccountingSupplierParty');
        $party    = $dom->createElement('cac:Party');

        $partyId  = $dom->createElement('cac:PartyIdentification');
        $idNode   = $dom->createElement('cbc:ID', $ruc);
        $idNode->setAttribute('schemeID',       '6');
        $idNode->setAttribute('schemeName',     'Registro Único de Contribuyentes');
        $idNode->setAttribute('schemeAgencyName','PE:SUNAT');
        $idNode->setAttribute('schemeURI',      'urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');
        $partyId->appendChild($idNode);
        $party->appendChild($partyId);

        $partyName = $dom->createElement('cac:PartyName');
        $partyName->appendChild($dom->createElement('cbc:Name', $razon_social));
        $party->appendChild($partyName);

        $partyLegal = $dom->createElement('cac:PartyLegalEntity');
        $partyLegal->appendChild($dom->createElement('cbc:RegistrationName', $razon_social));
        $regAddr = $dom->createElement('cac:RegistrationAddress');
        $regAddr->appendChild($dom->createElement('cbc:AddressTypeCode', '0000'));
        $addrLine = $dom->createElement('cac:AddressLine');
        $addrLine->appendChild($dom->createElement('cbc:Line', $direccion));
        $regAddr->appendChild($addrLine);
        $partyLegal->appendChild($regAddr);
        $party->appendChild($partyLegal);

        $supplier->appendChild($party);
        $invoice->appendChild($supplier);

        // ─── CLIENTE ──────────────────────────────────────────────────────────────
        $customer = $dom->createElement('cac:AccountingCustomerParty');
        $partyC   = $dom->createElement('cac:Party');

        $partyIdC = $dom->createElement('cac:PartyIdentification');
        $idNodeC  = $dom->createElement('cbc:ID', $docCliente);
        $idNodeC->setAttribute('schemeID', $tipoDocCliente);
        $partyIdC->appendChild($idNodeC);
        $partyC->appendChild($partyIdC);

        $partyLegalC = $dom->createElement('cac:PartyLegalEntity');
        $partyLegalC->appendChild($dom->createElement('cbc:RegistrationName', htmlspecialchars($nombreCliente)));
        $partyC->appendChild($partyLegalC);
        $customer->appendChild($partyC);
        $invoice->appendChild($customer);

        // ─── TAX TOTAL GLOBAL ─────────────────────────────────────────────────────
        $taxTotal   = $dom->createElement('cac:TaxTotal');
        $taxAmt     = $dom->createElement('cbc:TaxAmount', $totalIgv);
        $taxAmt->setAttribute('currencyID', $moneda);
        $taxTotal->appendChild($taxAmt);

        $taxSub = $dom->createElement('cac:TaxSubtotal');
        $txAmt1 = $dom->createElement('cbc:TaxableAmount', $subTotal);
        $txAmt1->setAttribute('currencyID', $moneda);
        $taxSub->appendChild($txAmt1);
        $txAmt2 = $dom->createElement('cbc:TaxAmount', $totalIgv);
        $txAmt2->setAttribute('currencyID', $moneda);
        $taxSub->appendChild($txAmt2);

        $taxCat = $dom->createElement('cac:TaxCategory');
        $taxCat->appendChild($dom->createElement('cbc:ID', 'S'));
        $taxCat->appendChild($dom->createElement('cbc:Percent', '18.00'));
        $taxCat->appendChild($dom->createElement('cbc:TaxExemptionReasonCode', '10'));
        $taxScheme = $dom->createElement('cac:TaxScheme');
        $taxScheme->appendChild($dom->createElement('cbc:ID', '1000'));
        $taxScheme->appendChild($dom->createElement('cbc:Name', 'IGV'));
        $taxScheme->appendChild($dom->createElement('cbc:TaxTypeCode', 'VAT'));
        $taxCat->appendChild($taxScheme);
        $taxSub->appendChild($taxCat);
        $taxTotal->appendChild($taxSub);
        $invoice->appendChild($taxTotal);

        // ─── TOTALES MONETARIOS ───────────────────────────────────────────────────
        $lmt = $dom->createElement('cac:LegalMonetaryTotal');
        if ($mDesc > 0) {
            $am = $dom->createElement('cbc:AllowanceTotalAmount', number_format($mDesc, 2, '.', ''));
            $am->setAttribute('currencyID', $moneda);
            $lmt->appendChild($am);
        }
        $lineExt = $dom->createElement('cbc:LineExtensionAmount', $subTotal);
        $lineExt->setAttribute('currencyID', $moneda);
        $lmt->appendChild($lineExt);
        $taxInc = $dom->createElement('cbc:TaxInclusiveAmount', $totalVenta);
        $taxInc->setAttribute('currencyID', $moneda);
        $lmt->appendChild($taxInc);
        $payable = $dom->createElement('cbc:PayableAmount', $totalVenta);
        $payable->setAttribute('currencyID', $moneda);
        $lmt->appendChild($payable);
        $invoice->appendChild($lmt);

        // ─── LÍNEAS DE DETALLE ────────────────────────────────────────────────────
        $lineId = 1;
        foreach ($detalles as $det) {
            $subtotalDet = (float)$det['subtotal'];
            $precioUnit  = (float)$det['precio_unitario'];
            $precioSinIgv = round($precioUnit / 1.18, 10);
            $igvLinea = round($subtotalDet - ($subtotalDet / 1.18), 2);
            $baseLinea = round($subtotalDet / 1.18, 2);

            $unitCode = ($det['tipo_unidad'] === 'CAJA') ? 'NIU' : 'C62';

            $il = $dom->createElement('cac:InvoiceLine');
            $il->appendChild($dom->createElement('cbc:ID', $lineId));

            $cantNode = $dom->createElement('cbc:InvoicedQuantity', number_format((float)$det['cantidad'], 2, '.', ''));
            $cantNode->setAttribute('unitCode', $unitCode);
            $cantNode->setAttribute('unitCodeListID', 'UN/ECE rec 20');
            $il->appendChild($cantNode);

            $lea = $dom->createElement('cbc:LineExtensionAmount', number_format($baseLinea, 2, '.', ''));
            $lea->setAttribute('currencyID', $moneda);
            $il->appendChild($lea);

            // PricingReference (precio con IGV incluido al cliente)
            $pr  = $dom->createElement('cac:PricingReference');
            $acp = $dom->createElement('cac:AlternativeConditionPrice');
            $pa  = $dom->createElement('cbc:PriceAmount', number_format($precioUnit, 2, '.', ''));
            $pa->setAttribute('currencyID', $moneda);
            $acp->appendChild($pa);
            $acp->appendChild($dom->createElement('cbc:PriceTypeCode', '01'));
            $pr->appendChild($acp);
            $il->appendChild($pr);

            // TaxTotal por línea
            $ltt  = $dom->createElement('cac:TaxTotal');
            $lta  = $dom->createElement('cbc:TaxAmount', number_format($igvLinea, 2, '.', ''));
            $lta->setAttribute('currencyID', $moneda);
            $ltt->appendChild($lta);
            $lts  = $dom->createElement('cac:TaxSubtotal');
            $ltsa = $dom->createElement('cbc:TaxableAmount', number_format($baseLinea, 2, '.', ''));
            $ltsa->setAttribute('currencyID', $moneda);
            $lts->appendChild($ltsa);
            $ltsa2= $dom->createElement('cbc:TaxAmount', number_format($igvLinea, 2, '.', ''));
            $ltsa2->setAttribute('currencyID', $moneda);
            $lts->appendChild($ltsa2);
            $ltc  = $dom->createElement('cac:TaxCategory');
            $ltc->appendChild($dom->createElement('cbc:Percent', '18.00'));
            $ltc->appendChild($dom->createElement('cbc:TaxExemptionReasonCode', '10'));
            $lsc  = $dom->createElement('cac:TaxScheme');
            $lsc->appendChild($dom->createElement('cbc:ID', '1000'));
            $lsc->appendChild($dom->createElement('cbc:Name', 'IGV'));
            $lsc->appendChild($dom->createElement('cbc:TaxTypeCode', 'VAT'));
            $ltc->appendChild($lsc);
            $lts->appendChild($ltc);
            $ltt->appendChild($lts);
            $il->appendChild($ltt);

            // Item description
            $itemNode = $dom->createElement('cac:Item');
            $itemNode->appendChild($dom->createElement('cbc:Description', htmlspecialchars($det['nombre_comercial'] ?? '')));
            $il->appendChild($itemNode);

            // Price (sin IGV)
            $priceNode = $dom->createElement('cac:Price');
            $pva = $dom->createElement('cbc:PriceAmount', number_format($precioSinIgv, 10, '.', ''));
            $pva->setAttribute('currencyID', $moneda);
            $priceNode->appendChild($pva);
            $il->appendChild($priceNode);

            $invoice->appendChild($il);
            $lineId++;
        }

        // ─── GUARDAR ARCHIVO ──────────────────────────────────────────────────────
        $xmlString = $dom->saveXML();

        // Directorio de salida: parámetro, o constante BASE_PATH, o DOCUMENT_ROOT
        if (empty($outputDir)) {
            if (defined('BASE_PATH')) {
                $outputDir = BASE_PATH . 'public/sunat/xml/';
            } else {
                // Detecta automáticamente la raíz del proyecto
                $outputDir = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' .
                             trim(parse_url(defined('BASE_URL') ? BASE_URL : '', PHP_URL_PATH), '/') .
                             '/public/sunat/xml/';
            }
        }

        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $filename = "{$ruc}-{$tipoDoc}-{$serie}-{$correlativo}.xml";
        file_put_contents($outputDir . $filename, $xmlString);

        return $filename;
    }

    // ─── HELPER: leer config genérico con múltiples fallbacks ────────────────────
    private static function cfg(array $empresa, array $claves, string $default = ''): string {
        foreach ($claves as $clave) {
            if (!empty($empresa[$clave]['valor'])) {
                return $empresa[$clave]['valor'];
            }
        }
        return $default;
    }

    // ─── HELPER: Número a Letras (SUNAT requiere monto en palabras) ──────────────
    public static function numeroALetras(float $numero): string {
        $entero  = (int) $numero;
        $decimal = round(($numero - $entero) * 100);

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
                     'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE',
                     'DIECIOCHO', 'DIECINUEVE', 'VEINTE'];
        $decenas  = ['', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
                     'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $convertirGrupo = function(int $n) use ($unidades, $decenas, $centenas): string {
            $resultado = '';
            if ($n == 100) return 'CIEN';
            if ($n >= 100) {
                $resultado .= $centenas[(int)($n / 100)] . ' ';
                $n %= 100;
            }
            if ($n <= 20) {
                $resultado .= $unidades[$n];
            } else {
                $resultado .= $decenas[(int)($n / 10)];
                if ($n % 10 > 0) $resultado .= ' Y ' . $unidades[$n % 10];
            }
            return trim($resultado);
        };

        if ($entero == 0) return 'CERO CON ' . sprintf('%02d', $decimal) . '/100';

        $letras = '';
        if ($entero >= 1000000) {
            $mill = (int)($entero / 1000000);
            $letras .= ($mill == 1 ? 'UN MILLON' : $convertirGrupo($mill) . ' MILLONES') . ' ';
            $entero %= 1000000;
        }
        if ($entero >= 1000) {
            $miles = (int)($entero / 1000);
            $letras .= ($miles == 1 ? 'MIL' : $convertirGrupo($miles) . ' MIL') . ' ';
            $entero %= 1000;
        }
        if ($entero > 0) {
            $letras .= $convertirGrupo($entero);
        }

        return trim($letras) . ' CON ' . sprintf('%02d', $decimal) . '/100';
    }
}
