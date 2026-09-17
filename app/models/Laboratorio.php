<?php
class Laboratorio {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function getAll() {
        $stmt = $this->conn->prepare("SELECT * FROM laboratorios WHERE estado = 1 ORDER BY nombre");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->conn->prepare("INSERT INTO laboratorios (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        return $stmt->execute();
    }

    public function update($data) {
        $stmt = $this->conn->prepare("UPDATE laboratorios SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':id', $data['id']);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("UPDATE laboratorios SET estado = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function findOrCreate($nombre) {
        $nombre = trim($nombre);
        if (empty($nombre)) return null;
        $stmt = $this->conn->prepare("SELECT id FROM laboratorios WHERE LOWER(TRIM(nombre)) = LOWER(:nom) LIMIT 1");
        $stmt->bindParam(':nom', $nombre);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) return (int)$row['id'];
        
        $insert = $this->conn->prepare("INSERT INTO laboratorios (nombre, descripcion, estado) VALUES (:nom, 'Importado de catálogo Excel', 1)");
        $insert->bindParam(':nom', $nombre);
        $insert->execute();
        return (int)$this->conn->lastInsertId();
    }
}
