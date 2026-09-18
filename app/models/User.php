<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function login($username, $password) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE usuario = :usuario AND estado = 1 LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":usuario", $username);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function updateLastLogin($id) {
        $query = "UPDATE " . $this->table_name . " SET ultimo_login = NOW() WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
    }

    public function getAll() {
        $query = "SELECT u.id, u.nombres, u.apellidos, u.usuario, u.email, u.rol_id, u.estado, u.ultimo_login, r.nombre as rol_nombre 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN roles r ON u.rol_id = r.id 
                  ORDER BY u.id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " SET nombres=:nombres, apellidos=:apellidos, usuario=:usuario, password=:password, email=:email, rol_id=:rol_id, estado=:estado";
        $stmt = $this->conn->prepare($query);
        
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        
        $stmt->bindParam(":nombres", $data['nombres']);
        $stmt->bindParam(":apellidos", $data['apellidos']);
        $stmt->bindParam(":usuario", $data['usuario']);
        $stmt->bindParam(":password", $hash);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":rol_id", $data['rol_id']);
        $stmt->bindParam(":estado", $data['estado']);
        
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET nombres=:nombres, apellidos=:apellidos, usuario=:usuario, email=:email, rol_id=:rol_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nombres", $data['nombres']);
        $stmt->bindParam(":apellidos", $data['apellidos']);
        $stmt->bindParam(":usuario", $data['usuario']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":rol_id", $data['rol_id']);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()){
            return true;
        }
        return false;
    }

    public function updatePassword($id, $password) {
        $query = "UPDATE " . $this->table_name . " SET password=:password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt->bindParam(":password", $hash);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function toggleEstado($id) {
        $query = "UPDATE " . $this->table_name . " SET estado = IF(estado=1, 0, 1) WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function updateProfile($id, $data) {
        $query = "UPDATE " . $this->table_name . " SET nombres=:nombres, apellidos=:apellidos, email=:email WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(":nombres", $data['nombres']);
        $stmt->bindParam(":apellidos", $data['apellidos']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    public function verifyPassword($id, $password) {
        $query = "SELECT password FROM " . $this->table_name . " WHERE id = :id LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $row['password'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario tiene registros operativos vinculados
     * (Ventas, Compras, Cajas, Auditorías, Kardex)
     */
    public function hasAssociatedRecords($id) {
        $checks = [
            "SELECT COUNT(*) FROM ventas WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM compras WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM cajas WHERE usuario_id = ?",
            "SELECT COUNT(*) FROM kardex WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM inventario_auditorias WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM compras_devoluciones WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM cliente_puntos_historial WHERE id_usuario = ?"
        ];

        foreach ($checks as $sql) {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id]);
            if ((int)$stmt->fetchColumn() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Elimina físicamente un usuario de la base de datos
     */
    public function delete($id) {
        if ((int)$id === 1) {
            return false;
        }

        // Limpiar registros secundarios de logs si existen para permitir borrado limpio
        try {
            $this->conn->beginTransaction();

            $stmtLog1 = $this->conn->prepare("DELETE FROM audit_accesos WHERE id_usuario = ?");
            $stmtLog1->execute([$id]);

            $stmtLog2 = $this->conn->prepare("DELETE FROM audit_acciones WHERE id_usuario = ?");
            $stmtLog2->execute([$id]);

            $stmtUser = $this->conn->prepare("DELETE FROM " . $this->table_name . " WHERE id = ?");
            $deleted = $stmtUser->execute([$id]);

            $this->conn->commit();
            return $deleted;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("[User::delete] Error al eliminar usuario ID #$id: " . $e->getMessage());
            return false;
        }
    }
}
