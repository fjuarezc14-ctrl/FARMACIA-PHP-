<?php
class Categoria {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM categorias WHERE estado = 1 ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO categorias (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        return $stmt->execute();
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE categorias SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("UPDATE categorias SET estado = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getPaginadas($search = '', $limit = 15, $offset = 0) {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM productos p WHERE p.id_categoria = c.id AND p.estado = 1) as total_productos
                FROM categorias c 
                WHERE c.estado = 1 ";
        
        if (!empty($search)) {
            $sql .= "AND (c.nombre LIKE :s1 OR c.descripcion LIKE :s2) ";
        }
        
        $sql .= "ORDER BY c.nombre ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        if (!empty($search)) {
            $s = "%{$search}%";
            $stmt->bindValue(':s1', $s, PDO::PARAM_STR);
            $stmt->bindValue(':s2', $s, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarCategorias($search = '') {
        $sql = "SELECT COUNT(*) as total FROM categorias WHERE estado = 1 ";
        if (!empty($search)) {
            $sql .= "AND (nombre LIKE :s1 OR descripcion LIKE :s2) ";
        }
        $stmt = $this->conn->prepare($sql);
        if (!empty($search)) {
            $s = "%{$search}%";
            $stmt->bindValue(':s1', $s, PDO::PARAM_STR);
            $stmt->bindValue(':s2', $s, PDO::PARAM_STR);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function contarProductosAsociados($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM productos WHERE id_categoria = :id AND estado = 1");
        $stmt->bindValue(':id', (int)$id, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT id FROM categorias WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(:nom)) AND estado = 1";
        if ($excluirId) {
            $sql .= " AND id != :excluirId";
        }
        $sql .= " LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nom', $nombre, PDO::PARAM_STR);
        if ($excluirId) {
            $stmt->bindValue(':excluirId', (int)$excluirId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }

    public function reasignarYEliminar($idOrigen, $idDestino) {
        try {
            $this->conn->beginTransaction();

            // 1. Reasignar productos de categoría origen a destino
            $stmtUpdate = $this->conn->prepare("UPDATE productos SET id_categoria = :destino WHERE id_categoria = :origen");
            $stmtUpdate->bindValue(':destino', (int)$idDestino, PDO::PARAM_INT);
            $stmtUpdate->bindValue(':origen', (int)$idOrigen, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // 2. Desactivar categoría origen
            $stmtDelete = $this->conn->prepare("UPDATE categorias SET estado = 0 WHERE id = :origen");
            $stmtDelete->bindValue(':origen', (int)$idOrigen, PDO::PARAM_INT);
            $stmtDelete->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error en Categoria::reasignarYEliminar: " . $e->getMessage());
            return false;
        }
    }

    public function findOrCreate($nombre) {
        $nombre = trim($nombre);
        if (empty($nombre)) return null;
        $stmt = $this->conn->prepare("SELECT id FROM categorias WHERE LOWER(TRIM(nombre)) = LOWER(:nom) LIMIT 1");
        $stmt->bindParam(':nom', $nombre);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int)$row['id'];
        
        $insert = $this->conn->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (:nom, 'Importado de catálogo Excel', 1)");
        $insert->bindParam(':nom', $nombre);
        $insert->execute();
        return (int)$this->conn->lastInsertId();
    }
}

