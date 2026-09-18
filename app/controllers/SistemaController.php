<?php
class SistemaController extends Controller {

    public function __construct() {
        $this->requireRole(1, 'venta/pos');
    }

    public function index() {
        $data = [
            'title'   => 'Respaldos y Sistema',
            'error'   => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];
        unset($_SESSION['error'], $_SESSION['success']);
        
        $this->view('sistema/index', $data);
    }

    /**
     * Genera y descarga un volcado SQL completo de la base de datos
     * de manera nativa mediante PDO, sin requerir binarios externos de mysqldump.
     */
    public function backup() {
        try {
            $database = new Database();
            $connection = $database->getConnection();

            if (!$connection) {
                throw new Exception("No se pudo establecer conexión con la base de datos.");
            }

            $tables = [];
            $statement = $connection->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            while ($row = $statement->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $backupFilename = "backup_cengfarma_" . date("Y-m-d_H-i-s") . ".sql";

            header('Content-Description: File Transfer');
            header('Content-Type: application/sql; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $backupFilename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');

            $output = fopen('php://output', 'w');

            // Encabezado del script SQL
            fwrite($output, "-- ==========================================================\n");
            fwrite($output, "-- Respaldo de Base de Datos - CENGFARMA\n");
            fwrite($output, "-- Fecha y Hora: " . date('Y-m-d H:i:s') . "\n");
            fwrite($output, "-- ==========================================================\n\n");
            fwrite($output, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($output, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($output, "SET NAMES utf8mb4;\n\n");

            foreach ($tables as $table) {
                // Estructura de la tabla
                fwrite($output, "-- Estructura de tabla para `{$table}`\n");
                fwrite($output, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTableStmt = $connection->query("SHOW CREATE TABLE `{$table}`");
                $createTableRow = $createTableStmt->fetch(PDO::FETCH_NUM);
                fwrite($output, $createTableRow[1] . ";\n\n");

                // Datos de la tabla
                $rowsStmt = $connection->query("SELECT * FROM `{$table}`");
                $columnCount = $rowsStmt->columnCount();
                $rowCount = $rowsStmt->rowCount();

                if ($rowCount > 0) {
                    fwrite($output, "-- Volcado de datos para `{$table}`\n");
                    fwrite($output, "INSERT INTO `{$table}` VALUES \n");

                    $first = true;
                    while ($row = $rowsStmt->fetch(PDO::FETCH_NUM)) {
                        if (!$first) {
                            fwrite($output, ",\n");
                        }
                        $first = false;

                        $values = [];
                        for ($i = 0; $i < $columnCount; $i++) {
                            if ($row[$i] === null) {
                                $values[] = "NULL";
                            } else {
                                $values[] = $connection->quote($row[$i]);
                            }
                        }
                        fwrite($output, "(" . implode(", ", $values) . ")");
                    }
                    fwrite($output, ";\n\n");
                }
            }

            fwrite($output, "SET FOREIGN_KEY_CHECKS = 1;\n");
            fclose($output);
            exit;

        } catch (Exception $exception) {
            error_log("[SistemaController::backup] Fallo al generar respaldo: " . $exception->getMessage() . " en " . $exception->getFile() . ":" . $exception->getLine());
            $_SESSION['error'] = "Ocurrió un error al generar la copia de seguridad.";
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }
    }

    /**
     * Restaura la base de datos a partir de un archivo .sql subido
     * utilizando transacciones seguras en PDO.
     */
    public function restaurar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['backup_file'])) {
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }

        $this->validateCsrf();
        $file = $_FILES['backup_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Error al subir el archivo de respaldo.";
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'sql') {
            $_SESSION['error'] = "Formato no válido: Debe seleccionar un archivo .sql.";
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }

        $database = new Database();
        $connection = $database->getConnection();

        if (!$connection) {
            $_SESSION['error'] = "No se pudo establecer conexión con la base de datos.";
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }

        try {
            $sqlContent = file_get_contents($file['tmp_name']);
            if ($sqlContent === false || trim($sqlContent) === '') {
                throw new Exception("El archivo SQL se encuentra vacío o ilegible.");
            }

            $connection->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $connection->exec($sqlContent);
            $connection->exec("SET FOREIGN_KEY_CHECKS = 1;");

            $this->logAccion('SISTEMA', 'RESTAURAR', "Restauración de base de datos desde archivo " . basename($file['name']));
            $_SESSION['success'] = "Base de datos restaurada correctamente.";

        } catch (Exception $exception) {
            if ($connection instanceof PDO) {
                try { $connection->exec("SET FOREIGN_KEY_CHECKS = 1;"); } catch (Exception $e) {}
            }
            error_log("[SistemaController::restaurar] Fallo al restaurar base de datos: " . $exception->getMessage());
            $_SESSION['error'] = "Error al restaurar la base de datos. Verifique la sintaxis del archivo SQL.";
        }

        header('Location: ' . BASE_URL . 'sistema/index');
        exit;
    }

    /**
     * Purgado controlado de tablas de la base de datos.
     */
    public function reset() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }

        $this->validateCsrf();
        $database = new Database();
        $connection = $database->getConnection();
        if (!$connection) {
            $_SESSION['error'] = "No se pudo establecer conexión con la base de datos.";
            header('Location: ' . BASE_URL . 'sistema/index');
            exit;
        }

        try {
            $connection->beginTransaction();
            $connection->exec("SET FOREIGN_KEY_CHECKS = 0");

            $resetType = $_POST['tipo_reset'] ?? 'todo';

            $transactionalTables = [
                'venta_detalles', 'ventas',
                'compra_detalles', 'compras',
                'compras_devoluciones_detalles', 'compras_devoluciones',
                'caja_movimientos', 'cajas',
                'inventario_lotes', 'kardex',
                'inventario_auditorias_detalles', 'inventario_auditorias',
                'audit_accesos', 'audit_acciones'
            ];

            $catalogTables = [
                'productos', 'categorias', 'laboratorios', 'clientes', 'proveedores'
            ];

            foreach ($transactionalTables as $table) {
                $connection->exec("TRUNCATE TABLE `{$table}`");
            }

            if ($resetType === 'todo') {
                foreach ($catalogTables as $table) {
                    $connection->exec("TRUNCATE TABLE `{$table}`");
                }

                $adminId = $_SESSION['user_id'];
                $deleteStmt = $connection->prepare("DELETE FROM usuarios WHERE id != ?");
                $deleteStmt->execute([$adminId]);
            }

            $connection->exec("SET FOREIGN_KEY_CHECKS = 1");
            $connection->commit();

            $this->logAccion('SISTEMA', 'RESET', "Reseteo del sistema ejecutado (Alcance: {$resetType})");
            $_SESSION['success'] = "Sistema reseteado exitosamente.";

        } catch (Exception $exception) {
            if ($connection instanceof PDO) {
                if ($connection->inTransaction()) {
                    $connection->rollBack();
                }
                try { $connection->exec("SET FOREIGN_KEY_CHECKS = 1"); } catch (Exception $e) {}
            }
            error_log("[SistemaController::reset] Fallo en reseteo: " . $exception->getMessage());
            $_SESSION['error'] = "Error al resetear los datos del sistema: " . $exception->getMessage();
        }

        header('Location: ' . BASE_URL . 'sistema/index');
        exit;
    }
}
