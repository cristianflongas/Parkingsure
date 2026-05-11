<?php
// ============================================================
//  PARKINGSURE - Modelo: Usuario (conexión a base de datos)
//  Archivo: conexionbd.php - Clase para gestión de usuarios
// ============================================================

// Línea 2: Define la clase Usuario para manejar operaciones CRUD de usuarios
class Usuario {
    
    // Línea 3: Propiedad privada para almacenar la conexión a la base de datos
    private $conn;
    
    // Línea 4: Propiedad privada con el nombre de la tabla principal
    private $table = "personal";

    // Línea 6-8: Constructor - recibe la conexión y la asigna a la propiedad
    public function __construct($db){
        $this->conn = $db;  // Asigna la conexión recibida
    }

    // =================================================================
    // MÉTODO: obtenerTodos() - Obtiene todos los usuarios del sistema
    // =================================================================
    public function obtenerTodos(){
        // Líneas 12-14: Construye consulta SQL para obtener todos los usuarios
        $sql = "SELECT id_personal, rol, nombre, telefono, correo      // Campos seleccionados
                FROM {$this->table}                                   // Tabla personal
                ORDER BY id_personal DESC";                           // Ordena por ID descendente

        // Línea 16: Prepara la consulta SQL para prevenir inyección SQL
        $stmt = $this->conn->prepare($sql);
        
        // Línea 17: Ejecuta la consulta preparada
        $stmt->execute();

        // Línea 19: Retorna todos los resultados como array asociativo
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =================================================================
    // MÉTODO: crear() - Inserta un nuevo usuario en la base de datos
    // =================================================================
    public function crear($rol, $nombre, $telefono, $correo){
        // Líneas 24-25: Construye consulta SQL para insertar nuevo usuario
        $sql = "INSERT INTO {$this->table} (rol, nombre, telefono, correo)
                VALUES (:rol, :nombre, :telefono, :correo)";

        // Línea 27: Prepara la consulta SQL
        $stmt = $this->conn->prepare($sql);

        // Líneas 29-35: Ejecuta la inserción con los parámetros recibidos
        return $stmt->execute([
            ":rol" => $rol,           // Rol del usuario (ej: ADMINISTRADOR, OPERADOR)
            ":nombre" => $nombre,      // Nombre completo del usuario
            ":telefono" => $telefono,  // Número de teléfono
            ":correo" => $correo       // Correo electrónico
        ]);
    }

    // =================================================================
    // MÉTODO: eliminar() - Elimina un usuario por su ID
    // =================================================================
    public function eliminar($id){
        // Línea 39: Construye consulta SQL para eliminar usuario por ID
        $sql = "DELETE FROM {$this->table} WHERE id_personal = :id";

        // Línea 41: Prepara la consulta SQL
        $stmt = $this->conn->prepare($sql);

        // Línea 43: Ejecuta la eliminación con el ID del usuario
        return $stmt->execute([":id" => $id]);
    }
}
?>