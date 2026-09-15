<?php
class SistemaController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['rol_id'] != 1) {
            header('Location: ' . BASE_URL . 'dashboard/index');
            exit;
        }
    }

    public function index() {
        $data = [
            'title' => 'Respaldos y Sistema',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];
        unset($_SESSION['error'], $_SESSION['success']);
        
        $this->view('sistema/index', $data);
    }

    public function backup() {
        // En Laragon: botica_db, root, sin contraseña
        $db_name = "botica_db";
        $username = "root";
        $password = "";
        
        $filename = "backup_botica_" . date("Y-m-d_H-i-s") . ".sql";
        $filepath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
        
        $command = "mysqldump --user={$username} ";
        if (!empty($password)) {
            $command .= "--password={$password} ";
        }
        $command .= "{$db_name} > {$filepath}";
        
        // Ejecutar comando
        exec($command, $output, $return_var);
        
        if ($return_var === 0 && file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
            unlink($filepath);
            exit;
        } else {
            $_SESSION['error'] = "Error al generar la copia de seguridad.";
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }
    }

    public function restaurar() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['backup_file'])) {
            $file = $_FILES['backup_file'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Error al subir el archivo.";
                header('Location: ' . BASE_URL . 'sistema/index');
                exit;
            }
            
            // Validar extensión
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'sql') {
                $_SESSION['error'] = "El archivo debe ser un .sql válido.";
                header('Location: ' . BASE_URL . 'sistema/index');
                exit;
            }
            
            $filepath = $file['tmp_name'];
            $db_name = "botica_db";
            $username = "root";
            $password = "";
            
            // Comando mysql para restaurar
            $command = "mysql --user={$username} ";
            if (!empty($password)) {
                $command .= "--password={$password} ";
            }
            $command .= "{$db_name} < {$filepath}";
            
            exec($command, $output, $return_var);
            
            if ($return_var === 0) {
                $_SESSION['success'] = "Base de datos restaurada correctamente.";
            } else {
                $_SESSION['error'] = "Error crítico al intentar restaurar la base de datos.";
            }
        }
        
        header('Location: ' . BASE_URL . 'sistema/index');
        exit;
    }

    public function reset() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $db = new Database();
            $conn = $db->getConnection();
            
            try {
                $conn->beginTransaction();
                
                // Deshabilitar FK temporalmente para truncar sin problemas
                $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
                
                // Opción seleccionada: 'transacciones' o 'todo'
                $tipo = $_POST['tipo_reset'] ?? 'todo';
                
                // Tablas Transaccionales
                $tablas_transaccionales = [
                    'venta_detalles', 'ventas',
                    'compra_detalles', 'compras',
                    'compras_devoluciones_detalles', 'compras_devoluciones',
                    'caja_movimientos', 'cajas',
                    'inventario_lotes', 'kardex',
                    'inventario_auditorias_detalles', 'inventario_auditorias',
                    'audit_accesos', 'audit_acciones'
                ];
                
                // Tablas de Catálogo (Maestras)
                $tablas_catalogo = [
                    'productos', 'categorias', 'laboratorios', 'clientes', 'proveedores'
                ];
                
                // Truncar Transaccionales
                foreach ($tablas_transaccionales as $tabla) {
                    $conn->exec("TRUNCATE TABLE `$tabla`");
                }
                
                // Si es reseteo total, truncar catálogos
                if ($tipo === 'todo') {
                    foreach ($tablas_catalogo as $tabla) {
                        $conn->exec("TRUNCATE TABLE `$tabla`");
                    }
                    // Limpiar notificaciones si existiese
                    // $conn->exec("TRUNCATE TABLE `notificaciones`");
                    
                    // Eliminar todos los usuarios excepto el actual logueado (Admin)
                    $admin_id = $_SESSION['user_id'];
                    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id != ?");
                    $stmt->execute([$admin_id]);
                }
                
                // Rehabilitar FK
                $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
                
                $conn->commit();
                
                $_SESSION['success'] = "Sistema reseteado exitosamente.";
                
            } catch (Exception $e) {
                $conn->rollBack();
                $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
                $_SESSION['error'] = "Error al resetear: " . $e->getMessage();
            }
        }
        
        header('Location: ' . BASE_URL . 'sistema/index');
        exit;
    }
}
?>
