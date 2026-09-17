<?php
class Venta {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT v.*, c.nombres as cliente, u.nombres as cajero 
                  FROM ventas v 
                  INNER JOIN clientes c ON v.id_cliente = c.id 
                  INNER JOIN usuarios u ON v.id_usuario = u.id 
                  ORDER BY v.id DESC LIMIT 1000";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDateRange($startDate, $endDate) {
        $query = "SELECT v.*, c.nombres as cliente, u.nombres as cajero 
                  FROM ventas v 
                  INNER JOIN clientes c ON v.id_cliente = c.id 
                  INNER JOIN usuarios u ON v.id_usuario = u.id 
                  WHERE v.fecha_venta >= :start AND v.fecha_venta <= :end 
                  ORDER BY v.fecha_venta DESC";
        $stmt = $this->conn->prepare($query);
        $start = $startDate . " 00:00:00";
        $end   = $endDate . " 23:59:59";
        $stmt->bindParam(':start', $start);
        $stmt->bindParam(':end', $end);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el listado de ventas con filtros avanzados y paginación
     */
    public function getVentasPaginadas($filtros = [], $limit = 25, $offset = 0) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $where[] = "v.fecha_venta >= :start";
            $params[':start'] = $filtros['fecha_inicio'] . " 00:00:00";
        }
        if (!empty($filtros['fecha_fin'])) {
            $where[] = "v.fecha_venta <= :end";
            $params[':end'] = $filtros['fecha_fin'] . " 23:59:59";
        }
        if (!empty($filtros['id_cliente'])) {
            $where[] = "v.id_cliente = :id_cliente";
            $params[':id_cliente'] = (int)$filtros['id_cliente'];
        }
        if (!empty($filtros['id_usuario'])) {
            $where[] = "v.id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$filtros['id_usuario'];
        }
        if (!empty($filtros['metodo_pago'])) {
            $where[] = "v.metodo_pago = :metodo_pago";
            $params[':metodo_pago'] = $filtros['metodo_pago'];
        }

        $whereClause = implode(" AND ", $where);
        $limitInt = max(1, (int)$limit);
        $offsetInt = max(0, (int)$offset);

        $query = "SELECT v.*, c.nombres as cliente, u.nombres as cajero 
                  FROM ventas v 
                  INNER JOIN clientes c ON v.id_cliente = c.id 
                  INNER JOIN usuarios u ON v.id_usuario = u.id 
                  WHERE $whereClause 
                  ORDER BY v.id DESC 
                  LIMIT $limitInt OFFSET $offsetInt";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de ventas según los filtros aplicados para la paginación
     */
    public function contarVentas($filtros = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtros['fecha_inicio'])) {
            $where[] = "v.fecha_venta >= :start";
            $params[':start'] = $filtros['fecha_inicio'] . " 00:00:00";
        }
        if (!empty($filtros['fecha_fin'])) {
            $where[] = "v.fecha_venta <= :end";
            $params[':end'] = $filtros['fecha_fin'] . " 23:59:59";
        }
        if (!empty($filtros['id_cliente'])) {
            $where[] = "v.id_cliente = :id_cliente";
            $params[':id_cliente'] = (int)$filtros['id_cliente'];
        }
        if (!empty($filtros['id_usuario'])) {
            $where[] = "v.id_usuario = :id_usuario";
            $params[':id_usuario'] = (int)$filtros['id_usuario'];
        }
        if (!empty($filtros['metodo_pago'])) {
            $where[] = "v.metodo_pago = :metodo_pago";
            $params[':metodo_pago'] = $filtros['metodo_pago'];
        }

        $whereClause = implode(" AND ", $where);
        $query = "SELECT COUNT(*) as total 
                  FROM ventas v 
                  INNER JOIN clientes c ON v.id_cliente = c.id 
                  INNER JOIN usuarios u ON v.id_usuario = u.id 
                  WHERE $whereClause";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }
    
    public function getDetalles($id_venta) {
        $query = "SELECT vd.*, p.nombre_comercial, p.unidades_por_caja, p.fraccionable, p.unidad_medida, p.unidad_fraccion, l.codigo_lote 
                  FROM venta_detalles vd 
                  INNER JOIN productos p ON vd.id_producto = p.id 
                  LEFT JOIN inventario_lotes l ON vd.id_lote = l.id
                  WHERE vd.id_venta = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_venta);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // REGISTRO DE VENTA CON MOTOR FEFO (First Expired, First Out)
    public function registrarVenta($cabecera, $detalles, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            // 1. Insertar Cabecera de Venta
            $query = "INSERT INTO ventas (caja_id, id_cliente, id_usuario, tipo_comprobante, serie_comprobante, num_comprobante, subtotal, descuento, tipo_descuento, motivo_descuento, igv, total, monto_efectivo, monto_transferencia, monto_tarjeta, num_operacion_trans, num_operacion_tarj, metodo_pago, pago_recibido, vuelto, puntos_ganados, puntos_usados, medico_cmp) 
                      VALUES (:caj, :cli, :usr, :tip, :ser, :num, :sub, :desc, :tdesc, :mdesc, :igv, :tot, :m_efe, :m_tra, :m_tar, :op_tra, :op_tar, :met, :pag, :vue, :pgan, :puso, :cmp)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':caj', $cabecera['caja_id']);
            $stmt->bindParam(':cli', $cabecera['id_cliente']);
            $stmt->bindParam(':usr', $id_usuario);
            $stmt->bindParam(':tip', $cabecera['tipo_comprobante']);
            $stmt->bindParam(':ser', $cabecera['serie_comprobante']);
            $stmt->bindParam(':num', $cabecera['num_comprobante']); // EJ: T-0001
            $stmt->bindParam(':sub', $cabecera['subtotal']);
            $stmt->bindParam(':desc', $cabecera['descuento']);
            $stmt->bindValue(':tdesc', $cabecera['tipo_descuento'] ?? null);
            $stmt->bindValue(':mdesc', $cabecera['motivo_descuento'] ?? null);
            $stmt->bindParam(':igv', $cabecera['igv']);
            $stmt->bindParam(':tot', $cabecera['total']);
            $stmt->bindValue(':m_efe', $cabecera['monto_efectivo'] ?? 0.00);
            $stmt->bindValue(':m_tra', $cabecera['monto_transferencia'] ?? 0.00);
            $stmt->bindValue(':m_tar', $cabecera['monto_tarjeta'] ?? 0.00);
            $stmt->bindValue(':op_tra', $cabecera['num_operacion_trans'] ?? null);
            $stmt->bindValue(':op_tar', $cabecera['num_operacion_tarj'] ?? null);
            $stmt->bindParam(':met', $cabecera['metodo_pago']);
            $stmt->bindParam(':pag', $cabecera['pago_recibido']);
            $stmt->bindParam(':vue', $cabecera['vuelto']);
            $stmt->bindParam(':pgan', $cabecera['puntos_ganados']);
            $stmt->bindParam(':puso', $cabecera['puntos_usados']);
            $stmt->bindParam(':cmp', $cabecera['medico_cmp']);
            $stmt->execute();
            
            $id_venta = $this->conn->lastInsertId();
            $motivo_kardex = "Venta " . $cabecera['tipo_comprobante'] . " " . $cabecera['serie_comprobante'] . "-" . $cabecera['num_comprobante'];

            // 2. Procesar Detalles y FEFO
            foreach ($detalles as $det) {
                // Obtener factor fraccionario
                $prodQuery = $this->conn->prepare("SELECT unidades_por_caja, fraccionable, unidad_medida, unidad_fraccion FROM productos WHERE id = :id");
                $prodQuery->bindParam(':id', $det['id_producto']);
                $prodQuery->execute();
                $prodData = $prodQuery->fetch(PDO::FETCH_ASSOC);
                
                $factor = ($prodData['fraccionable'] == 1 && $prodData['unidades_por_caja'] > 0) ? $prodData['unidades_por_caja'] : 1;
                $unidad_fraccion = $prodData['unidad_fraccion'] ? $prodData['unidad_fraccion'] : 'Unidad';

                $tipo_venta = isset($det['tipo_unidad']) ? $det['tipo_unidad'] : 'CAJA';

                if ($tipo_venta == 'CAJA') {
                    $cant_requerida = $det['cantidad'] * $factor; // Convertir a unidades mínimas
                    $precio_unitario_minimo = $det['precio_unitario'] / $factor;
                } else {
                    $cant_requerida = $det['cantidad'];
                    $precio_unitario_minimo = $det['precio_unitario'];
                }
                
                // Obtener lote(s) que vencerán más pronto con saldo > 0 
                $selLotes = $this->conn->prepare("SELECT id, codigo_lote, cantidad_disponible 
                                                  FROM inventario_lotes 
                                                  WHERE id_producto = :prod AND cantidad_disponible > 0 AND estado = 1 
                                                  ORDER BY fecha_vencimiento ASC FOR UPDATE");
                $selLotes->bindParam(':prod', $det['id_producto']);
                $selLotes->execute();
                $lotesD = $selLotes->fetchAll(PDO::FETCH_ASSOC);
                
                $cant_restante = $cant_requerida;

                // Restar saldos de lotes
                foreach ($lotesD as $loteObj) {
                    if ($cant_restante <= 0) break; // Ya cubrimos la necesidad
                    
                    $descuento = 0;
                    if ($loteObj['cantidad_disponible'] >= $cant_restante) {
                        $descuento = $cant_restante;
                        $cant_restante = 0;
                    } else {
                        $descuento = $loteObj['cantidad_disponible'];
                        $cant_restante -= $descuento;
                    }
                    
                    // Actualizar Lote físico
                    $nuevo_saldo_lote = $loteObj['cantidad_disponible'] - $descuento;
                    $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = :nuevo WHERE id = :idl");
                    $updLote->bindParam(':nuevo', $nuevo_saldo_lote);
                    $updLote->bindParam(':idl', $loteObj['id']);
                    $updLote->execute();
                    
                    // Insertar Venta Detalle (dividido por lote para trazabilidad exacta)
                    $subtotal_fraccion = $descuento * $precio_unitario_minimo;
                    
                    if ($tipo_venta == 'CAJA') {
                        $tipo_unidad_save = $prodData['fraccionable'] == 1 ? $prodData['unidad_medida'] : 'Unidad';
                    } else {
                        $tipo_unidad_save = $prodData['unidad_fraccion'] ? $prodData['unidad_fraccion'] : 'Unidad';
                    }

                    $insDet = $this->conn->prepare("INSERT INTO venta_detalles (id_venta, id_producto, cantidad, precio_unitario, subtotal, id_lote, tipo_unidad) 
                                                    VALUES (:v, :p, :c, :pre, :sub, :il, :tu)");
                    $insDet->bindParam(':v', $id_venta);
                    $insDet->bindParam(':p', $det['id_producto']);
                    $insDet->bindParam(':c', $descuento);
                    $insDet->bindParam(':pre', $precio_unitario_minimo);
                    $insDet->bindParam(':sub', $subtotal_fraccion);
                    $insDet->bindParam(':il', $loteObj['id']);
                    $insDet->bindParam(':tu', $tipo_unidad_save);
                    $insDet->execute();
                }

                if ($cant_restante > 0) {
                    // Si ocurre esto, quiere decir que alguien vendió sin stock suficiente.
                    // Rechazamos la transacción para evitar saldo negativo fantasma.
                    throw new Exception("Stock insuficiente del Lote FEFO en producto ID " . $det['id_producto']);
                }

                // Actualizar Catálogo General
                $sProd = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE");
                $sProd->bindParam(':id', $det['id_producto']);
                $sProd->execute();
                $stock_ant = $sProd->fetch(PDO::FETCH_ASSOC)['stock_actual'];
                
                $nuevo_stock = $stock_ant - $cant_requerida;
                
                $uProd = $this->conn->prepare("UPDATE productos SET stock_actual = :nst WHERE id = :id");
                $uProd->bindParam(':nst', $nuevo_stock);
                $uProd->bindParam(':id', $det['id_producto']);
                $uProd->execute();

                // Registrar SALIDA en Kardex General
                $kardex = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                                VALUES (:pro, :usr, 'SALIDA', :mot, :cnt, :sld)");
                $kardex->bindParam(':pro', $det['id_producto']);
                $kardex->bindParam(':usr', $id_usuario);
                $kardex->bindParam(':mot', $motivo_kardex);
                $kardex->bindParam(':cnt', $cant_requerida);
                $kardex->bindParam(':sld', $nuevo_stock);
                $kardex->execute();
            }

            $this->conn->commit();
            return $id_venta;

        } catch (Exception $e) {
            error_log("[Venta::registrarVenta] Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->conn->rollBack();
            return false;
        }
    }

    public function anularVenta($id_venta, $id_usuario) {
        try {
            $this->conn->beginTransaction();

            // 1. Verificar si ya está anulada
            $stmtV = $this->conn->prepare("SELECT estado, tipo_comprobante, serie_comprobante, num_comprobante, id_cliente, puntos_ganados, puntos_usados FROM ventas WHERE id = :id FOR UPDATE");
            $stmtV->bindParam(':id', $id_venta);
            $stmtV->execute();
            $venta = $stmtV->fetch(PDO::FETCH_ASSOC);

            if(!$venta || $venta['estado'] == 'Anulada') {
                $this->conn->rollBack();
                return false;
            }

            // 2. Obtener detalles de la venta
            $detalles = $this->getDetalles($id_venta);
            
            $motivo_kardex = "Anulación " . $venta['tipo_comprobante'] . " " . $venta['serie_comprobante'] . "-" . $venta['num_comprobante'];

            foreach($detalles as $det) {
                // Dado que en el nuevo diseño 'cantidad' en la base de datos se almacena siempre en unidades mínimas,
                // la cantidad a restaurar es directamente el valor de 'cantidad'.
                $cant_restaurar = $det['cantidad'];

                // Devolver a Lote si aplica
                if ($det['id_lote']) {
                    $updLote = $this->conn->prepare("UPDATE inventario_lotes SET cantidad_disponible = cantidad_disponible + :cant WHERE id = :idl");
                    $updLote->bindParam(':cant', $cant_restaurar);
                    $updLote->bindParam(':idl', $det['id_lote']);
                    $updLote->execute();
                }

                // Devolver al stock general maestro
                $sProd = $this->conn->prepare("SELECT stock_actual FROM productos WHERE id = :id FOR UPDATE");
                $sProd->bindParam(':id', $det['id_producto']);
                $sProd->execute();
                $stock_ant = $sProd->fetch(PDO::FETCH_ASSOC)['stock_actual'];
                
                $nuevo_stock = $stock_ant + $cant_restaurar;
                
                $uProd = $this->conn->prepare("UPDATE productos SET stock_actual = :nst WHERE id = :id");
                $uProd->bindParam(':nst', $nuevo_stock);
                $uProd->bindParam(':id', $det['id_producto']);
                $uProd->execute();

                // Registrar ENTRADA en Kardex
                $kardex = $this->conn->prepare("INSERT INTO kardex (id_producto, id_usuario, tipo_movimiento, motivo, cantidad, saldo_actual) 
                                                VALUES (:pro, :usr, 'ENTRADA', :mot, :cnt, :sld)");
                $kardex->bindParam(':pro', $det['id_producto']);
                $kardex->bindParam(':usr', $id_usuario);
                $kardex->bindParam(':mot', $motivo_kardex);
                $kardex->bindParam(':cnt', $cant_restaurar);
                $kardex->bindParam(':sld', $nuevo_stock);
                $kardex->execute();
            }

            // 3. Actualizar estado de Venta
            $updVenta = $this->conn->prepare("UPDATE ventas SET estado = 'Anulada' WHERE id = :id");
            $updVenta->bindParam(':id', $id_venta);
            $updVenta->execute();

            // 4. Revertir puntos del cliente
            if($venta['id_cliente'] != 1) {
                // Si ganó puntos, se los quitamos (-puntos_ganados)
                // Si usó puntos, se los devolvemos (+puntos_usados)
                $delta_puntos = $venta['puntos_usados'] - $venta['puntos_ganados'];
                if($delta_puntos != 0) {
                    $updCli = $this->conn->prepare("UPDATE clientes SET puntos_acumulados = puntos_acumulados + :delta WHERE id = :idc");
                    $updCli->bindParam(':delta', $delta_puntos);
                    $updCli->bindParam(':idc', $venta['id_cliente']);
                    $updCli->execute();
                }
            }

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            error_log("[Venta::anularVenta] Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->conn->rollBack();
            return false;
        }
    }
}
