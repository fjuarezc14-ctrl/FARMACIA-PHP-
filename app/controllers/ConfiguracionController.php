<?php
class ConfiguracionController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $configModel = $this->model('Configuracion');
        $configs = $configModel->getAll();
        
        $data = [
            'title' => 'Configuración de Empresa',
            'configs' => $configs
        ];
        
        $this->view('configuracion/index', $data);
    }

    public function save() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->validateCsrf();
            $configModel = $this->model('Configuracion');
            
            $updates = [
                'nombre_botica'  => trim($_POST['nombre_botica']),
                'nombre_empresa' => trim($_POST['nombre_botica']), // sync genérico
                'ruc'            => trim($_POST['ruc']),
                'direccion'      => trim($_POST['direccion']),
                'telefono'       => trim($_POST['telefono']),
                'moneda'         => trim($_POST['moneda']),
                'igv'            => trim($_POST['igv'])
            ];
            
            // Upload Logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), $allowed)) {
                    $newFileName = 'logo_botica_' . time() . '.' . strtolower($ext);
                    $destPath = 'img/' . $newFileName;
                    if (move_uploaded_file($_FILES['logo']['tmp_name'], $destPath)) {
                        $updates['logo'] = BASE_URL . $destPath;
                    }
                }
            }
            
            // Parámetros de la Suite Nativa de Accesibilidad Web (Sanitización y Whitelist)
            $allowedPositions = ['bottom-right', 'bottom-left', 'top-right', 'top-left'];
            $selectedPosition = isset($_POST['a11y_posicion']) && in_array(trim($_POST['a11y_posicion']), $allowedPositions) 
                ? trim($_POST['a11y_posicion']) 
                : 'bottom-right';

            $updates['a11y_habilitado'] = isset($_POST['a11y_habilitado']) ? '1' : '0';
            $updates['a11y_posicion']   = $selectedPosition;
            $updates['a11y_lector_voz'] = isset($_POST['a11y_lector_voz']) ? '1' : '0';
            
            if ($configModel->updateMultiples($updates)) {
                $this->logAccion('Configuración', 'EDITAR', 'Actualización de ajustes generales y suite de accesibilidad');
                $_SESSION['mensaje'] = "Parámetros actualizados correctamente.";
            } else {
                $_SESSION['error'] = "Hubo un error al actualizar los datos de la botica en BD.";
            }
        }
        header('Location: ' . BASE_URL . 'configuracion/index');
        exit;
    }

    public function sunat() {
        $configModel = $this->model('Configuracion');
        $this->view('configuracion/sunat', [
            'title'   => 'Facturación Electrónica SUNAT',
            'configs' => $configModel->getAll()
        ]);
    }

    public function saveSunat() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'configuracion/sunat'); exit;
        }

        $this->validateCsrf();
        $configModel = $this->model('Configuracion');

        // Subida de certificado .p12
        $certPath = trim($_POST['sunat_cert_path'] ?? '');
        if (isset($_FILES['cert_file']) && $_FILES['cert_file']['error'] == UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['cert_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['p12', 'pfx'])) {
                $certDir = $_SERVER['DOCUMENT_ROOT'] . '/sistema-botica/public/sunat/certs/';
                if (!file_exists($certDir)) mkdir($certDir, 0755, true);
                $newCertName = 'cert_' . time() . '.' . $ext;
                $newCertPath = $certDir . $newCertName;
                if (move_uploaded_file($_FILES['cert_file']['tmp_name'], $newCertPath)) {
                    $certPath = $newCertPath;
                }
            }
        }

        $updates = [
            'sunat_sol_usuario'    => trim($_POST['sunat_sol_usuario']    ?? ''),
            'sunat_sol_clave'      => trim($_POST['sunat_sol_clave']      ?? ''),
            'sunat_cert_path'      => $certPath,
            'sunat_cert_password'  => trim($_POST['sunat_cert_password']  ?? ''),
            'sunat_modo'           => trim($_POST['sunat_modo']           ?? 'beta'),
            'sunat_url_beta'       => trim($_POST['sunat_url_beta']       ?? ''),
            'sunat_url_produccion' => trim($_POST['sunat_url_produccion'] ?? ''),
            'sunat_habilitado'     => isset($_POST['sunat_habilitado'])   ? '1' : '0',
        ];

        if ($configModel->updateMultiples($updates)) {
            $_SESSION['mensaje'] = "Configuración SUNAT guardada correctamente.";
        } else {
            $_SESSION['error'] = "Error al guardar la configuración SUNAT.";
        }

        header('Location: ' . BASE_URL . 'configuracion/sunat');
        exit;
    }
}
