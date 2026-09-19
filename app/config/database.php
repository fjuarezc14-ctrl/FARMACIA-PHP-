<?php
if (!class_exists('Database')) {
    class Database {
        private $host;
        private $db_name;
        private $username;
        private $password;
        private $port;
        public $conn;

        public function __construct() {
            $this->host = getenv('DB_HOST') ?: "localhost";
            $this->db_name = getenv('DB_NAME') ?: "botica_db";
            $this->username = getenv('DB_USER') ?: "root";
            $this->password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : "";
            $this->port = getenv('DB_PORT') ?: "3306";
        }

        public function getConnection() {
            $this->conn = null;
            try {
                $dsn = "mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name . ";charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => true,
                ];
                $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            } catch(PDOException $exception) {
                error_log("[Database Connection Error] " . $exception->getMessage());
                if (php_sapi_name() === 'cli') {
                    fwrite(STDERR, "Error de conexión a la base de datos: " . $exception->getMessage() . "\n");
                    return null;
                }
                http_response_code(503);
                header('Content-Type: text/html; charset=utf-8');
                ?>
                <!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <title>Servicio Temporalmente No Disponible | Farmacia Prueba</title>
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <style>
                        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #FFF3EC; color: #1A2238; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
                        .card { background: #FFFFFF; padding: 35px; border-radius: 16px; box-shadow: 0 4px 25px rgba(0,0,0,0.06); text-align: center; max-width: 480px; border: 1px solid #E8DFD8; }
                        h2 { color: #047B07; margin-top: 0; margin-bottom: 12px; font-weight: 800; font-size: 24px; }
                        p { color: #5A6478; line-height: 1.5; font-size: 14px; margin: 8px 0; }
                        .btn { display: inline-block; background: #047B07; color: white; padding: 12px 26px; border-radius: 10px; text-decoration: none; font-weight: 700; margin-top: 20px; font-size: 14px; border: none; cursor: pointer; transition: background 0.2s; }
                        .btn:hover { background: #035E05; }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <h2>Farmacia Prueba</h2>
                        <p><strong>El servicio de base de datos no se encuentra disponible temporalmente.</strong></p>
                        <p>El sistema se encuentra reintentando la sincronización con el servidor de datos. Por favor espere unos instantes y recargue la página.</p>
                        <button onclick="location.reload()" class="btn">🔄 Reintentar Conexión</button>
                    </div>
                </body>
                </html>
                <?php
                exit;
            }
            return $this->conn;
        }
    }
}
?>
