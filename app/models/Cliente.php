<?php
class Cliente {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE estado = 1 ORDER BY nombres");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene clientes paginados con filtro de búsqueda por DNI, Nombre o Teléfono
     */
    public function getPaginados($search = '', $limit = 25, $offset = 0) {
        $limitInt = max(1, (int)$limit);
        $offsetInt = max(0, (int)$offset);

        if (empty($search)) {
            $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE estado = 1 ORDER BY id = 1 DESC, nombres ASC LIMIT $limitInt OFFSET $offsetInt");
            $stmt->execute();
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE estado = 1 AND (num_documento LIKE :s1 OR nombres LIKE :s2 OR telefono LIKE :s3) ORDER BY nombres ASC LIMIT $limitInt OFFSET $offsetInt");
            $term = "%$search%";
            $stmt->bindValue(':s1', $term);
            $stmt->bindValue(':s2', $term);
            $stmt->bindValue(':s3', $term);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cuenta el total de clientes según el término de búsqueda
     */
    public function contarClientes($search = '') {
        if (empty($search)) {
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM clientes WHERE estado = 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        } else {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM clientes WHERE estado = 1 AND (num_documento LIKE :s1 OR nombres LIKE :s2 OR telefono LIKE :s3)");
            $term = "%$search%";
            $stmt->bindValue(':s1', $term);
            $stmt->bindValue(':s2', $term);
            $stmt->bindValue(':s3', $term);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? (int)$row['total'] : 0;
        }
    }
    
    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM clientes WHERE id = :id AND estado = 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO clientes (tipo_documento, num_documento, nombres, telefono, direccion) VALUES (:tipo, :num, :nom, :tel, :dir)");
        $stmt->bindParam(':tipo', $data['tipo_documento']);
        $stmt->bindParam(':num', $data['num_documento']);
        $stmt->bindParam(':nom', $data['nombres']);
        $stmt->bindParam(':tel', $data['telefono']);
        $stmt->bindParam(':dir', $data['direccion']);
        if ($stmt->execute()) {
            return (int)$this->conn->lastInsertId();
        }
        return false;
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE clientes SET tipo_documento = :tipo, num_documento = :num, nombres = :nom, telefono = :tel, direccion = :dir WHERE id = :id");
        $stmt->bindParam(':tipo', $data['tipo_documento']);
        $stmt->bindParam(':num', $data['num_documento']);
        $stmt->bindParam(':nom', $data['nombres']);
        $stmt->bindParam(':tel', $data['telefono']);
        $stmt->bindParam(':dir', $data['direccion']);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }

    public function delete($id) {
        if($id == 1) return false; // El 1 es el público en general protegido
        $stmt = $this->conn->prepare("UPDATE clientes SET estado = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function actualizarPuntos($id, $puntos_delta) {
        if($id == 1) return true; // Público general no acumula puntos
        // $puntos_delta puede ser negativo si se descuentan por canje, o por anulación
        $stmt = $this->conn->prepare("UPDATE clientes SET puntos_acumulados = puntos_acumulados + :puntos WHERE id = :id");
        $stmt->bindParam(':puntos', $puntos_delta);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
