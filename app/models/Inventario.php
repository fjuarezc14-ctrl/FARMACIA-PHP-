<?php
class Inventario {
    private $conn;

    public function __construct($dbConn = null) {
        if ($dbConn) {
            $this->conn = $dbConn; // permitir inyectar conexion para transacciones
        } else {
            $db = new Database();
            $this->conn = $db->getConnection();
        }
    }

    public function getResumenKpisLotes() {
        $sql = "SELECT 
                    COUNT(*) as total_lotes,
                    SUM(CASE WHEN l.fecha_vencimiento < CURDATE() THEN 1 ELSE 0 END) as vencidos,
                    SUM(CASE WHEN l.fecha_vencimiento >= CURDATE() AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as criticos_30,
                    SUM(CASE WHEN l.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as riesgo_90,
                    SUM(CASE WHEN l.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 1 ELSE 0 END) as sanos,
                    COALESCE(SUM(l.cantidad_disponible), 0) as unidades_totales
                FROM inventario_lotes l
                WHERE l.cantidad_disponible > 0 AND l.estado = 1";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_lotes' => 0, 'vencidos' => 0, 'criticos_30' => 0, 'riesgo_90' => 0, 'sanos' => 0, 'unidades_totales' => 0
        ];
    }

    public function getLotesPaginados($filtros = [], $limit = 25, $offset = 0) {
        $sql = "SELECT l.*, 
                       p.nombre_comercial, p.nombre_generico, p.forma_farmaceutica, p.concentracion,
                       c.nombre as categoria, 
                       lab.nombre as laboratorio,
                       DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
                FROM inventario_lotes l
                INNER JOIN productos p ON l.id_producto = p.id
                LEFT JOIN categorias c ON p.id_categoria = c.id
                LEFT JOIN laboratorios lab ON p.id_laboratorio = lab.id
                WHERE l.estado = 1 ";

        if (empty($filtros['stock']) || $filtros['stock'] !== 'todos') {
            $sql .= "AND l.cantidad_disponible > 0 ";
        }

        if (!empty($filtros['search'])) {
            $sql .= "AND (p.nombre_comercial LIKE :s1 OR p.nombre_generico LIKE :s2 OR l.codigo_lote LIKE :s3) ";
        }

        if (!empty($filtros['alerta'])) {
            if ($filtros['alerta'] === 'vencidos') {
                $sql .= "AND l.fecha_vencimiento < CURDATE() ";
            } elseif ($filtros['alerta'] === 'criticos_30') {
                $sql .= "AND l.fecha_vencimiento >= CURDATE() AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
            } elseif ($filtros['alerta'] === 'riesgo_90') {
                $sql .= "AND l.fecha_vencimiento >= CURDATE() AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) ";
            } elseif ($filtros['alerta'] === 'sanos') {
                $sql .= "AND l.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 90 DAY) ";
            }
        }

        if (!empty($filtros['id_laboratorio'])) {
            $sql .= "AND p.id_laboratorio = :id_lab ";
        }

        if (!empty($filtros['id_categoria'])) {
            $sql .= "AND p.id_categoria = :id_cat ";
        }

        $sql .= "ORDER BY l.fecha_vencimiento ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        if (!empty($filtros['search'])) {
            $s = "%{$filtros['search']}%";
            $stmt->bindValue(':s1', $s, PDO::PARAM_STR);
            $stmt->bindValue(':s2', $s, PDO::PARAM_STR);
            $stmt->bindValue(':s3', $s, PDO::PARAM_STR);
        }
        if (!empty($filtros['id_laboratorio'])) {
            $stmt->bindValue(':id_lab', (int)$filtros['id_laboratorio'], PDO::PARAM_INT);
        }
        if (!empty($filtros['id_categoria'])) {
            $stmt->bindValue(':id_cat', (int)$filtros['id_categoria'], PDO::PARAM_INT);
        }

        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarLotes($filtros = []) {
        $sql = "SELECT COUNT(*) as total
                FROM inventario_lotes l
                INNER JOIN productos p ON l.id_producto = p.id
                WHERE l.estado = 1 ";

        if (empty($filtros['stock']) || $filtros['stock'] !== 'todos') {
            $sql .= "AND l.cantidad_disponible > 0 ";
        }

        if (!empty($filtros['search'])) {
            $sql .= "AND (p.nombre_comercial LIKE :s1 OR p.nombre_generico LIKE :s2 OR l.codigo_lote LIKE :s3) ";
        }

        if (!empty($filtros['alerta'])) {
            if ($filtros['alerta'] === 'vencidos') {
                $sql .= "AND l.fecha_vencimiento < CURDATE() ";
            } elseif ($filtros['alerta'] === 'criticos_30') {
                $sql .= "AND l.fecha_vencimiento >= CURDATE() AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) ";
            } elseif ($filtros['alerta'] === 'riesgo_90') {
                $sql .= "AND l.fecha_vencimiento >= CURDATE() AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 90 DAY) ";
            } elseif ($filtros['alerta'] === 'sanos') {
                $sql .= "AND l.fecha_vencimiento > DATE_ADD(CURDATE(), INTERVAL 90 DAY) ";
            }
        }

        if (!empty($filtros['id_laboratorio'])) {
            $sql .= "AND p.id_laboratorio = :id_lab ";
        }

        if (!empty($filtros['id_categoria'])) {
            $sql .= "AND p.id_categoria = :id_cat ";
        }

        $stmt = $this->conn->prepare($sql);

        if (!empty($filtros['search'])) {
            $s = "%{$filtros['search']}%";
            $stmt->bindValue(':s1', $s, PDO::PARAM_STR);
            $stmt->bindValue(':s2', $s, PDO::PARAM_STR);
            $stmt->bindValue(':s3', $s, PDO::PARAM_STR);
        }
        if (!empty($filtros['id_laboratorio'])) {
            $stmt->bindValue(':id_lab', (int)$filtros['id_laboratorio'], PDO::PARAM_INT);
        }
        if (!empty($filtros['id_categoria'])) {
            $stmt->bindValue(':id_cat', (int)$filtros['id_categoria'], PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function getLotesActivos() {
        // Trae los lotes que aún tienen stock disponible o están por vencer
        $query = "SELECT l.*, p.nombre_comercial, p.forma_farmaceutica, p.concentracion, c.nombre as categoria, lab.nombre as laboratorio
                  FROM inventario_lotes l
                  INNER JOIN productos p ON l.id_producto = p.id
                  LEFT JOIN categorias c ON p.id_categoria = c.id
                  LEFT JOIN laboratorios lab ON p.id_laboratorio = lab.id
                  WHERE l.cantidad_disponible > 0 AND l.estado = 1
                  ORDER BY l.fecha_vencimiento ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getLotesProximosVencer($rango = '90', $id_laboratorio = null) {
        $sql = "SELECT p.id as id_producto, p.nombre_comercial as producto, lab.nombre as laboratorio,
                       l.id as id_lote, l.codigo_lote as lote, l.fecha_vencimiento, l.cantidad_disponible as stock,
                       DATEDIFF(l.fecha_vencimiento, CURDATE()) as dias_restantes
                FROM inventario_lotes l
                INNER JOIN productos p ON l.id_producto = p.id
                LEFT JOIN laboratorios lab ON p.id_laboratorio = lab.id
                WHERE l.cantidad_disponible > 0 
                AND l.estado = 1 ";

        if ($rango === 'vencidos') {
            $sql .= "AND l.fecha_vencimiento < CURDATE() ";
        } elseif ($rango === 'todos') {
            // Sin filtro de fecha
        } else {
            $dias = is_numeric($rango) ? (int)$rango : 90;
            $sql .= "AND l.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL :dias DAY) ";
        }

        if (!empty($id_laboratorio)) {
            $sql .= "AND p.id_laboratorio = :id_lab ";
        }

        $sql .= "ORDER BY l.fecha_vencimiento ASC";

        $stmt = $this->conn->prepare($sql);

        if ($rango !== 'vencidos' && $rango !== 'todos') {
            $dias = is_numeric($rango) ? (int)$rango : 90;
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        }
        if (!empty($id_laboratorio)) {
            $stmt->bindValue(':id_lab', (int)$id_laboratorio, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProductosBajoStock($limite = 20) {
        $query = "SELECT id, nombre_comercial as producto, stock_actual as stock, unidad_medida 
                  FROM productos 
                  WHERE stock_actual <= :limite AND estado = 1
                  ORDER BY stock_actual ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getKardex($id_producto = null) {
        $query = "SELECT k.*, p.nombre_comercial, u.nombres as usuario 
                  FROM kardex k
                  INNER JOIN productos p ON k.id_producto = p.id
                  INNER JOIN usuarios u ON k.id_usuario = u.id ";
        if ($id_producto) {
            $query .= "WHERE k.id_producto = :id_producto ";
        }
        $query .= "ORDER BY k.id DESC LIMIT 500";
        
        $stmt = $this->conn->prepare($query);
        if ($id_producto) $stmt->bindParam(':id_producto', $id_producto);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Registra la entrada (Kardex, Lote y actualización de Stock) - ASUME ESTAR EN TRANSACCION
    public function registrarEntrada($id_producto, $id_usuario, $cantidad, $motivo, $lote, $vencimiento, $id_compra_detalle = null) {
        // 1. Obtener stock actual
        $stmt = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE");
        $stmt->bindParam(':id', $id_producto);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $saldo_anterior = $row ? $row['stock_actual'] : 0;
        $nuevo_saldo = $saldo_anterior + $cantidad;

        // 2. Insertar Lote
        $stmt2 = $this->conn->prepare("INSERT INTO inventario_lotes (id_producto, id_compra_detalle, codigo_lote, fecha_vencimiento, cantidad_inicial, cantidad_disponible) 
                                       VALUES (:prod, :det, :lote, :venc, :cant_ini, :cant_disp)");
        $stmt2->bindParam(':prod', $id_producto);
        $stmt2->bindParam(':det', $id_compra_detalle);
        $stmt2->bindParam(':lote', $lote);
        $stmt2->bindParam(':venc', $vencimiento);
        $stmt2->bindParam(':cant_ini', $cantidad);
        $stmt2->bindParam(':cant_disp', $cantidad);
        $stmt2->execute();

        // 3. Insertar Kardex
        $stmt3 = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                       VALUES (:prod, :usr, 'ENTRADA', :motivo, :cant, :saldo)");
        $stmt3->bindParam(':prod', $id_producto);
        $stmt3->bindParam(':usr', $id_usuario);
        $stmt3->bindParam(':motivo', $motivo);
        $stmt3->bindParam(':cant', $cantidad);
        $stmt3->bindParam(':saldo', $nuevo_saldo);
        $stmt3->execute();

        // 4. Actualizar Stock en Producto
        $stmt4 = $this->conn->prepare("UPDATE productos SET stock_actual = :saldo WHERE id = :prod");
        $stmt4->bindParam(':saldo', $nuevo_saldo);
        $stmt4->bindParam(':prod', $id_producto);
        $stmt4->execute();
        
        return true;
    }

    // --- MÓDULO DE INVENTARIO FÍSICO (FASE 11) ---

    public function iniciarAuditoria($id_usuario, $observaciones = '') {
        try {
            $this->conn->beginTransaction();
            
            // 1. Crear Cabecera
            $stmt = $this->conn->prepare("INSERT INTO inventario_auditorias (id_usuario, observaciones) VALUES (:uid, :obs)");
            $stmt->bindParam(':uid', $id_usuario);
            $stmt->bindParam(':obs', $observaciones);
            $stmt->execute();
            $id_audit = $this->conn->lastInsertId();

            // 2. Capturar "Foto" de lotes activos (con stock > 0)
            $stmt2 = $this->conn->prepare("INSERT INTO inventario_auditoria_detalles (id_auditoria, id_lote, stock_sistema)
                                           SELECT :id, id, cantidad_disponible FROM inventario_lotes WHERE cantidad_disponible > 0 AND estado = 1");
            $stmt2->bindParam(':id', $id_audit);
            $stmt2->execute();

            $this->conn->commit();
            return $id_audit;
        } catch (Exception $e) {
            error_log("[Inventario::iniciarAuditoria] Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->conn->rollBack();
            return false;
        }
    }

    public function getDetallesAuditoria($id_audit) {
        $query = "SELECT d.*, p.nombre_comercial, l.codigo_lote, l.fecha_vencimiento
                  FROM inventario_auditoria_detalles d
                  INNER JOIN inventario_lotes l ON d.id_lote = l.id
                  INNER JOIN productos p ON l.id_producto = p.id
                  WHERE d.id_auditoria = :id
                  ORDER BY p.nombre_comercial ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_audit);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function finalizarAuditoria($id_audit, $conteos, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            foreach ($conteos as $lote_id => $fisico) {
                // 1. Obtener datos actuales del detalle y del producto
                $stmt = $this->conn->prepare("SELECT d.*, l.id_producto FROM inventario_auditoria_detalles d 
                                              INNER JOIN inventario_lotes l ON d.id_lote = l.id 
                                              WHERE d.id_auditoria = :ida AND d.id_lote = :idl");
                $stmt->bindParam(':ida', $id_audit);
                $stmt->bindParam(':idl', $lote_id);
                $stmt->execute();
                $det = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$det) continue;

                $fisico = (int)$fisico;
                $sistema = (int)$det['stock_sistema'];
                $dif = $fisico - $sistema;

                // 2. Actualizar detalle de auditoría
                $updDet = $this->conn->prepare("UPDATE inventario_auditoria_detalles SET stock_fisico = :fis, diferencia = :dif WHERE id = :id");
                $updDet->bindParam(':fis', $fisico);
                $updDet->bindParam(':dif', $dif);
                $updDet->bindParam(':id', $det['id']);
                $updDet->execute();

                if ($dif != 0) {
                    // 3. Actualizar Lote
                    $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = :fis WHERE id = :idl");
                    $updLote->bindParam(':fis', $fisico);
                    $updLote->bindParam(':idl', $lote_id);
                    $updLote->execute();

                    // 4. Registrar en Kardex el AJUSTE
                    $motivo = "Ajuste por Inventario Físico #" . $id_audit;
                    $tipo = ($dif > 0) ? 'AJUSTE' : 'SALIDA'; // Podría ser ENTRADA/SALIDA, usamos AJUSTE como comodín o ENUM según DB

                    // Recalcular saldo parcial para el Kardex
                    $stmtS = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :idp FOR UPDATE");
                    $stmtS->bindParam(':idp', $det['id_producto']);
                    $stmtS->execute();
                    $stock_anterior = $stmtS->fetch(PDO::FETCH_ASSOC)['stock_actual'];
                    $nuevo_saldo = $stock_anterior + $dif;

                    $stmtK = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                                   VALUES (:idp, :usr, 'AJUSTE', :mot, :cant, :sld)");
                    $stmtK->bindParam(':idp', $det['id_producto']);
                    $stmtK->bindParam(':usr', $id_usuario);
                    $stmtK->bindParam(':mot', $motivo);
                    $stmtK->bindParam(':cant', $dif);
                    $stmtK->bindParam(':sld', $nuevo_saldo);
                    $stmtK->execute();

                    // 5. Actualizar Stock del Producto
                    $updP = $this->conn->prepare("UPDATE productos SET stock_actual = :sld WHERE id = :idp");
                    $updP->bindParam(':sld', $nuevo_saldo);
                    $updP->bindParam(':idp', $det['id_producto']);
                    $updP->execute();
                }
            }

            // 6. Marcar auditoría como finalizada
            $stmtF = $this->conn->prepare("UPDATE inventario_auditorias SET estado = 'Finalizada', fecha_fin = NOW() WHERE id = :id");
            $stmtF->bindParam(':id', $id_audit);
            $stmtF->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            error_log("[Inventario::finalizarAuditoria] Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->conn->rollBack();
            return $e->getMessage();
        }
    }
}
