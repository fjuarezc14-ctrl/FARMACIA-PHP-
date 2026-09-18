<?php
class Producto {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $query = "SELECT p.*, c.nombre as categoria, l.nombre as laboratorio 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id 
                  LEFT JOIN laboratorios l ON p.id_laboratorio = l.id 
                  WHERE p.estado = 1 ORDER BY p.nombre_comercial";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllAdmin() {
        $query = "SELECT p.*, c.nombre as categoria, l.nombre as laboratorio 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id 
                  LEFT JOIN laboratorios l ON p.id_laboratorio = l.id 
                  ORDER BY p.nombre_comercial";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene productos con filtros de búsqueda y paginación para el panel de administración
     */
    public function getPaginadosAdmin($filtros = [], $limit = 25, $offset = 0) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtros['search'])) {
            $where[] = "(p.codigo_barras LIKE :s OR p.nombre_comercial LIKE :s OR p.nombre_generico LIKE :s)";
            $params[':s'] = "%" . $filtros['search'] . "%";
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = "p.id_categoria = :cat";
            $params[':cat'] = (int)$filtros['id_categoria'];
        }
        if (!empty($filtros['id_laboratorio'])) {
            $where[] = "p.id_laboratorio = :lab";
            $params[':lab'] = (int)$filtros['id_laboratorio'];
        }
        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = "p.estado = :est";
            $params[':est'] = (int)$filtros['estado'];
        }

        $whereClause = implode(" AND ", $where);
        $limitInt = max(1, (int)$limit);
        $offsetInt = max(0, (int)$offset);

        $query = "SELECT p.*, c.nombre as categoria, l.nombre as laboratorio 
                  FROM productos p 
                  LEFT JOIN categorias c ON p.id_categoria = c.id 
                  LEFT JOIN laboratorios l ON p.id_laboratorio = l.id 
                  WHERE $whereClause 
                  ORDER BY p.nombre_comercial ASC 
                  LIMIT $limitInt OFFSET $offsetInt";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta productos según los filtros aplicados para la paginación
     */
    public function contarProductosAdmin($filtros = []) {
        $where = ["1=1"];
        $params = [];

        if (!empty($filtros['search'])) {
            $where[] = "(p.codigo_barras LIKE :s OR p.nombre_comercial LIKE :s OR p.nombre_generico LIKE :s)";
            $params[':s'] = "%" . $filtros['search'] . "%";
        }
        if (!empty($filtros['id_categoria'])) {
            $where[] = "p.id_categoria = :cat";
            $params[':cat'] = (int)$filtros['id_categoria'];
        }
        if (!empty($filtros['id_laboratorio'])) {
            $where[] = "p.id_laboratorio = :lab";
            $params[':lab'] = (int)$filtros['id_laboratorio'];
        }
        if (isset($filtros['estado']) && $filtros['estado'] !== '') {
            $where[] = "p.estado = :est";
            $params[':est'] = (int)$filtros['estado'];
        }

        $whereClause = implode(" AND ", $where);
        $query = "SELECT COUNT(*) as total 
                  FROM productos p 
                  WHERE $whereClause";

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function getProductosBajoStock() {
        $query = "SELECT p.*, l.nombre as laboratorio 
                  FROM productos p 
                  LEFT JOIN laboratorios l ON p.id_laboratorio = l.id 
                  WHERE p.estado = 1 AND p.stock_actual <= p.stock_minimo 
                  ORDER BY p.stock_actual ASC, p.nombre_comercial ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO productos (codigo_barras, nombre_generico, codigo_prin_activo, nombre_comercial, concentracion, forma_farmaceutica, registro_sanitario, condicion_venta, id_laboratorio, id_categoria, precio_compra, precio_venta, precio_mayor, margen_ganancia, unidad_medida, requiere_receta, stock_minimo, fraccionable, unidades_por_caja, unidad_fraccion, precio_fraccion) 
                  VALUES (:cb, :ng, :cpa, :nc, :conc, :ff, :rs, :cv, :idl, :idc, :pc, :pv, :pmay, :mg, :um, :rr, :sm, :frac, :upc, :ufrac, :pfrac)";
        $stmt = $this->conn->prepare($query);
        $this->bindParams($stmt, $data);
        return $stmt->execute();
    }

    public function update($data) {
        $query = "UPDATE productos SET codigo_barras=:cb, nombre_generico=:ng, codigo_prin_activo=:cpa, nombre_comercial=:nc, concentracion=:conc, forma_farmaceutica=:ff, registro_sanitario=:rs, condicion_venta=:cv, id_laboratorio=:idl, id_categoria=:idc, precio_compra=:pc, precio_venta=:pv, precio_mayor=:pmay, margen_ganancia=:mg, unidad_medida=:um, requiere_receta=:rr, stock_minimo=:sm, fraccionable=:frac, unidades_por_caja=:upc, unidad_fraccion=:ufrac, precio_fraccion=:pfrac WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->bindParams($stmt, $data);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }

    public function quickUpdate($id, $data) {
        $query = "UPDATE productos SET 
                    nombre_comercial = :nc, 
                    codigo_barras = :cb, 
                    id_categoria = :idc, 
                    id_laboratorio = :idl, 
                    precio_venta = :pv, 
                    precio_compra = :pc, 
                    stock_minimo = :sm, 
                    fraccionable = :frac, 
                    unidades_por_caja = :upc, 
                    unidad_fraccion = :ufrac, 
                    precio_fraccion = :pfrac 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':nc', $data['nombre_comercial']);
        $stmt->bindValue(':cb', !empty($data['codigo_barras']) ? $data['codigo_barras'] : null);
        $stmt->bindValue(':idc', !empty($data['id_categoria']) ? (int)$data['id_categoria'] : null);
        $stmt->bindValue(':idl', !empty($data['id_laboratorio']) ? (int)$data['id_laboratorio'] : null);
        $stmt->bindValue(':pv', (float)$data['precio_venta']);
        $stmt->bindValue(':pc', (float)$data['precio_compra']);
        $stmt->bindValue(':sm', (int)$data['stock_minimo']);
        $stmt->bindValue(':frac', (int)$data['fraccionable']);
        $stmt->bindValue(':upc', (int)$data['unidades_por_caja']);
        $stmt->bindValue(':ufrac', !empty($data['unidad_fraccion']) ? $data['unidad_fraccion'] : null);
        $stmt->bindValue(':pfrac', (float)$data['precio_fraccion']);
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    
    private function bindParams($stmt, $data) {
        $stmt->bindParam(':cb', $data['codigo_barras']);
        $stmt->bindParam(':ng', $data['nombre_generico']);
        $cpa = !empty($data['codigo_prin_activo']) ? (int)$data['codigo_prin_activo'] : null;
        $stmt->bindParam(':cpa', $cpa);
        $stmt->bindParam(':nc', $data['nombre_comercial']);
        $stmt->bindParam(':conc', $data['concentracion']);
        $stmt->bindParam(':ff', $data['forma_farmaceutica']);
        $stmt->bindParam(':rs', $data['registro_sanitario']);
        $stmt->bindParam(':cv', $data['condicion_venta']);
        $stmt->bindParam(':idl', $data['id_laboratorio']);
        $stmt->bindParam(':idc', $data['id_categoria']);
        $stmt->bindParam(':pc', $data['precio_compra']);
        $stmt->bindParam(':pv', $data['precio_venta']);
        $pmay = isset($data['precio_mayor']) && $data['precio_mayor'] !== '' ? (float)$data['precio_mayor'] : null;
        $stmt->bindParam(':pmay', $pmay);
        $stmt->bindParam(':mg', $data['margen_ganancia']);
        $stmt->bindParam(':um', $data['unidad_medida']);
        $stmt->bindParam(':rr', $data['requiere_receta']);
        $stmt->bindParam(':sm', $data['stock_minimo']);
        $stmt->bindParam(':frac', $data['fraccionable']);
        $stmt->bindParam(':upc', $data['unidades_por_caja']);
        $stmt->bindParam(':ufrac', $data['unidad_fraccion']);
        $stmt->bindParam(':pfrac', $data['precio_fraccion']);
    }

    public function toggleEstado($id) {
        $stmt = $this->conn->prepare("UPDATE productos SET estado = CASE WHEN estado = 1 THEN 0 ELSE 1 END WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getByCodigoBarras($codigo) {
        if (empty($codigo)) return false;
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE codigo_barras = :cb LIMIT 1");
        $stmt->bindParam(':cb', $codigo);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByNombre($nombre) {
        if (empty($nombre)) return false;
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE LOWER(TRIM(nombre_comercial)) = LOWER(TRIM(:nom)) LIMIT 1");
        $stmt->bindParam(':nom', $nombre);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarStock($id, $nuevoStock) {
        $stmt = $this->conn->prepare("UPDATE productos SET stock_actual = :stk WHERE id = :id");
        $stmt->bindParam(':stk', $nuevoStock);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
