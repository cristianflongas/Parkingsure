<?php
class Usuario {
    private $conn;
    private $table = "personal";

    public function __construct($db){
        $this->conn = $db;
    }

    // Obtener todos
    public function obtenerTodos(){
        $sql = "SELECT id_personal, rol, nombre, telefono, correo 
                FROM {$this->table} 
                ORDER BY id_personal DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Insertar
    public function crear($rol, $nombre, $telefono, $correo){
        $sql = "INSERT INTO {$this->table} (rol, nombre, telefono, correo)
                VALUES (:rol, :nombre, :telefono, :correo)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ":rol" => $rol,
            ":nombre" => $nombre,
            ":telefono" => $telefono,
            ":correo" => $correo
        ]);
    }

    // Eliminar
    public function eliminar($id){
        $sql = "DELETE FROM {$this->table} WHERE id_personal = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([":id" => $id]);
    }
}
?>