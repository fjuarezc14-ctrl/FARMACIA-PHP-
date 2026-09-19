<?php
class Punto {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Obtiene la configuración del sistema de fidelización de puntos
     */
    public function getConfig() {
        $query = "SELECT clave, valor FROM configuracion WHERE clave IN ('puntos_consumo_base', 'puntos_valor_canje', 'puntos_habilitado')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'consumo_base' => isset($rows['puntos_consumo_base']) && is_numeric($rows['puntos_consumo_base']) && (float)$rows['puntos_consumo_base'] > 0 ? (float)$rows['puntos_consumo_base'] : 10.00,
            'valor_canje'  => isset($rows['puntos_valor_canje']) && is_numeric($rows['puntos_valor_canje']) && (float)$rows['puntos_valor_canje'] > 0 ? (float)$rows['puntos_valor_canje'] : 0.10,
            'habilitado'   => isset($rows['puntos_habilitado']) ? (int)$rows['puntos_habilitado'] : 1
        ];
    }

    /**
     * Actualiza las reglas de acumulación y canje de puntos
     */
    public function updateConfig($consumoBase, $valorCanje, $habilitado) {
        try {
            $this->conn->beginTransaction();

            $sql = "INSERT INTO configuracion (clave, valor) VALUES (:k, :v) ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
            $stmt = $this->conn->prepare($sql);

            $stmt->execute([':k' => 'puntos_consumo_base', ':v' => number_format((float)$consumoBase, 2, '.', '')]);
            $stmt->execute([':k' => 'puntos_valor_canje',  ':v' => number_format((float)$valorCanje, 2, '.', '')]);
            $stmt->execute([':k' => 'puntos_habilitado',   ':v' => (int)$habilitado]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("[Punto::updateConfig] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un movimiento de puntos en el historial y actualiza el saldo del cliente
     */
    public function registrarMovimiento($idCliente, $idUsuario, $tipo, $puntosDelta, $motivo, $idVenta = null) {
        if ($idCliente == 1) {
            return false; // Público general no maneja puntos
        }

        try {
            $this->conn->beginTransaction();

            // 1. Obtener saldo actual del cliente con bloqueo FOR UPDATE
            $stmtCli = $this->conn->prepare("SELECT puntos_acumulados FROM clientes WHERE id = :id FOR UPDATE");
            $stmtCli->bindParam(':id', $idCliente);
            $stmtCli->execute();
            $cli = $stmtCli->fetch(PDO::FETCH_ASSOC);

            if (!$cli) {
                $this->conn->rollBack();
                return false;
            }

            $saldoAnterior = (int)$cli['puntos_acumulados'];
            $saldoNuevo = max(0, $saldoAnterior + $puntosDelta);

            // 2. Insertar en cliente_puntos_historial
            $sqlH = "INSERT INTO cliente_puntos_historial 
                        (id_cliente, id_usuario, tipo, puntos, saldo_anterior, saldo_nuevo, motivo, id_venta) 
                     VALUES 
                        (:cli, :usr, :tipo, :pts, :sant, :snue, :mot, :vta)";
            $stmtH = $this->conn->prepare($sqlH);
            $stmtH->execute([
                ':cli'  => $idCliente,
                ':usr'  => $idUsuario,
                ':tipo' => $tipo,
                ':pts'  => $puntosDelta,
                ':sant' => $saldoAnterior,
                ':snue' => $saldoNuevo,
                ':mot'  => $motivo,
                ':vta'  => $idVenta
            ]);

            // 3. Actualizar saldo en clientes
            $stmtUpd = $this->conn->prepare("UPDATE clientes SET puntos_acumulados = :snue WHERE id = :id");
            $stmtUpd->execute([':snue' => $saldoNuevo, ':id' => $idCliente]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("[Punto::registrarMovimiento] Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Listado de clientes con paginación y búsqueda para el módulo de puntos
     */
    public function getClientesPuntos($search = '', $limit = 25, $offset = 0) {
        $where = ["c.id != 1", "c.estado = 1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(c.nombres LIKE :s1 OR c.num_documento LIKE :s2 OR c.telefono LIKE :s3)";
            $params[':s1'] = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }

        $whereSql = implode(" AND ", $where);
        $query = "SELECT c.id, c.tipo_documento, c.num_documento, c.nombres, c.telefono, c.puntos_acumulados,
                         (SELECT COUNT(*) FROM cliente_puntos_historial h WHERE h.id_cliente = c.id) as total_movimientos
                  FROM clientes c
                  WHERE $whereSql
                  ORDER BY c.puntos_acumulados DESC, c.nombres ASC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta clientes para paginación
     */
    public function contarClientesPuntos($search = '') {
        $where = ["c.id != 1", "c.estado = 1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(c.nombres LIKE :s1 OR c.num_documento LIKE :s2 OR c.telefono LIKE :s3)";
            $params[':s1'] = "%$search%";
            $params[':s2'] = "%$search%";
            $params[':s3'] = "%$search%";
        }

        $whereSql = implode(" AND ", $where);
        $query = "SELECT COUNT(*) as total FROM clientes c WHERE $whereSql";
        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Obtiene el historial de movimientos de un cliente
     */
    public function getHistorialCliente($idCliente, $limit = 50) {
        $query = "SELECT h.*, u.nombres as usuario_nombre, v.tipo_comprobante, v.serie_comprobante, v.num_comprobante
                  FROM cliente_puntos_historial h
                  INNER JOIN usuarios u ON h.id_usuario = u.id
                  LEFT JOIN ventas v ON h.id_venta = v.id
                  WHERE h.id_cliente = :cli
                  ORDER BY h.id DESC
                  LIMIT :lim";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':cli', (int)$idCliente, PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Métricas globales de puntos para la vista del módulo
     */
    public function getMetricasPuntos() {
        // Total puntos activos acumulados
        $q1 = $this->conn->query("SELECT SUM(puntos_acumulados) as total_puntos, COUNT(*) as total_clientes FROM clientes WHERE id != 1 AND estado = 1");
        $r1 = $q1->fetch(PDO::FETCH_ASSOC);

        // Total puntos canjeados en el mes actual
        $q2 = $this->conn->query("SELECT SUM(ABS(puntos)) as canjeados_mes FROM cliente_puntos_historial WHERE tipo = 'CANJE' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $r2 = $q2->fetch(PDO::FETCH_ASSOC);

        return [
            'total_puntos'    => (int)($r1['total_puntos'] ?? 0),
            'total_clientes'  => (int)($r1['total_clientes'] ?? 0),
            'canjeados_mes'   => (int)($r2['canjeados_mes'] ?? 0)
        ];
    }
}
