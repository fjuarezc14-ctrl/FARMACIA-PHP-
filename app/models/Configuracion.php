<?php
class Configuracion {
    private $conn;
    private $table_name = "configuracion";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Obtener todas las configuraciones en un arreglo asociativo clave => valor
    public function getAll() {
        $query = "SELECT clave, valor, descripcion FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['clave']] = [
                'valor' => $row['valor'],
                'descripcion' => $row['descripcion']
            ];
        }
        return $results;
    }
    
    // Obtener solo el valor directo de una clave específica (con fallback opcional)
    public function get($clave, $default = null) {
        $query = "SELECT valor FROM " . $this->table_name . " WHERE clave = :clave LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();
        
        if($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return ($row['valor'] !== null && $row['valor'] !== '') ? $row['valor'] : $default;
        }
        return $default;
    }

    // Actualizar múltiple recibiendo un array de clave=>valor (con inserción segura en caso no exista)
    public function updateMultiples($data) {
        try {
            $this->conn->beginTransaction();
            $query = "INSERT INTO " . $this->table_name . " (clave, valor) VALUES (:clave, :valor) 
                      ON DUPLICATE KEY UPDATE valor = VALUES(valor)";
            $stmt = $this->conn->prepare($query);
            
            foreach($data as $clave => $valor) {
                $stmt->bindParam(':valor', $valor);
                $stmt->bindParam(':clave', $clave);
                $stmt->execute();
            }
            
            $this->conn->commit();
            return true;
        } catch(PDOException $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
