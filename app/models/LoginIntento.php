<?php
/**
 * Limita los intentos fallidos de inicio de sesión (protección contra fuerza bruta).
 *
 * Reglas:
 *  - Por usuario + IP: MAX_POR_USUARIO fallos en VENTANA_MIN minutos => bloqueo de BLOQUEO_MIN minutos.
 *    (Se combina con la IP para que un atacante no pueda bloquear la cuenta de otro desde fuera.)
 *  - Por IP: MAX_POR_IP fallos en VENTANA_MIN minutos (con cualquier usuario) => bloqueo de la IP.
 *
 * Si la tabla no existe o hay un error de BD, el limitador "falla abierto" (no bloquea),
 * para no dejar a nadie fuera del sistema por un problema de infraestructura.
 */
class LoginIntento {
    const MAX_POR_USUARIO = 5;
    const MAX_POR_IP      = 20;
    const VENTANA_MIN     = 15;
    const BLOQUEO_MIN     = 15;

    private $conn;
    private static $tablaVerificada = false;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
        $this->asegurarTabla();
    }

    private function asegurarTabla() {
        if (self::$tablaVerificada || !$this->conn) return;
        try {
            $this->conn->exec("CREATE TABLE IF NOT EXISTS `login_intentos` (
                `id` int NOT NULL AUTO_INCREMENT,
                `usuario` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
                `ip` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
                `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_li_usuario_ip` (`usuario`, `ip`, `created_at`),
                KEY `idx_li_ip` (`ip`, `created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            self::$tablaVerificada = true;
        } catch (Throwable $e) {
            error_log('[LoginIntento] No se pudo crear la tabla: ' . $e->getMessage());
        }
    }

    public static function normalizarUsuario($usuario) {
        return mb_substr(mb_strtolower(trim((string)$usuario)), 0, 100);
    }

    public static function ipCliente() {
        // Solo REMOTE_ADDR: las cabeceras X-Forwarded-For las puede falsificar el atacante.
        return substr($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 0, 45);
    }

    /**
     * Segundos que faltan para poder volver a intentar (0 = no bloqueado).
     */
    public function segundosBloqueo($usuario, $ip) {
        if (!$this->conn) return 0;
        try {
            $st = $this->conn->prepare("SELECT COUNT(*) AS n,
                        TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(MAX(created_at), INTERVAL " . self::BLOQUEO_MIN . " MINUTE)) AS resta
                    FROM login_intentos
                    WHERE usuario = ? AND ip = ? AND created_at > NOW() - INTERVAL " . self::VENTANA_MIN . " MINUTE");
            $st->execute([$usuario, $ip]);
            $u = $st->fetch(PDO::FETCH_ASSOC);

            $st = $this->conn->prepare("SELECT COUNT(*) AS n,
                        TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(MAX(created_at), INTERVAL " . self::BLOQUEO_MIN . " MINUTE)) AS resta
                    FROM login_intentos
                    WHERE ip = ? AND created_at > NOW() - INTERVAL " . self::VENTANA_MIN . " MINUTE");
            $st->execute([$ip]);
            $i = $st->fetch(PDO::FETCH_ASSOC);

            $resta = 0;
            if ((int)$u['n'] >= self::MAX_POR_USUARIO) $resta = max($resta, (int)$u['resta']);
            if ((int)$i['n'] >= self::MAX_POR_IP)      $resta = max($resta, (int)$i['resta']);
            return max(0, $resta);
        } catch (Throwable $e) {
            error_log('[LoginIntento] ' . $e->getMessage());
            return 0;
        }
    }

    /** Intentos que le quedan a ese usuario desde esa IP antes del bloqueo. */
    public function intentosRestantes($usuario, $ip) {
        if (!$this->conn) return self::MAX_POR_USUARIO;
        try {
            $st = $this->conn->prepare("SELECT COUNT(*) FROM login_intentos
                    WHERE usuario = ? AND ip = ? AND created_at > NOW() - INTERVAL " . self::VENTANA_MIN . " MINUTE");
            $st->execute([$usuario, $ip]);
            return max(0, self::MAX_POR_USUARIO - (int)$st->fetchColumn());
        } catch (Throwable $e) {
            return self::MAX_POR_USUARIO;
        }
    }

    public function registrarFallo($usuario, $ip) {
        if (!$this->conn) return;
        try {
            $st = $this->conn->prepare("INSERT INTO login_intentos (usuario, ip, user_agent) VALUES (?, ?, ?)");
            $st->execute([$usuario, $ip, mb_substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)]);
            // Limpieza ocasional de registros viejos
            if (random_int(1, 50) === 1) {
                $this->conn->exec("DELETE FROM login_intentos WHERE created_at < NOW() - INTERVAL 1 DAY");
            }
        } catch (Throwable $e) {
            error_log('[LoginIntento] ' . $e->getMessage());
        }
    }

    /** Tras un login correcto se olvidan los fallos de ese usuario desde esa IP. */
    public function limpiar($usuario, $ip) {
        if (!$this->conn) return;
        try {
            $st = $this->conn->prepare("DELETE FROM login_intentos WHERE usuario = ? AND ip = ?");
            $st->execute([$usuario, $ip]);
        } catch (Throwable $e) {
            error_log('[LoginIntento] ' . $e->getMessage());
        }
    }

    public static function formatoEspera($segundos) {
        $min = (int)ceil($segundos / 60);
        return $min <= 1 ? '1 minuto' : "$min minutos";
    }
}
