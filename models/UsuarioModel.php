<?php
// ============================================================
//  PARKINGSURE - Modelo: Usuario
//  Archivo: UsuarioModel.php - Clase para gestión de usuarios
//  Arquitectura MVC - Modelo de datos
// ============================================================

class UsuarioModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los usuarios de la base de datos
     */
    public function obtenerTodos() {
        $stmt = $this->conn->prepare("
            SELECT
              p.id_personal,
              p.usuario,
              r.nombre_rol  AS rol,
              u.nombre,
              u.telefono,
              u.correo,
              u.cedula
            FROM personal p
            INNER JOIN rol   r ON p.id_rol       = r.id_rol
            INNER JOIN users u ON p.cedula_users  = u.cedula
            ORDER BY p.id_personal ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un nuevo usuario en users y personal usando transacciones
     */
    public function crear($data) {
        $this->conn->beginTransaction();
        try {
            // 1. Insertar o actualizar en la tabla users
            $stmtUser = $this->conn->prepare("
                INSERT INTO users (cedula, nombre, telefono, correo)
                VALUES (:cedula, :nombre, :telefono, :correo)
                ON DUPLICATE KEY UPDATE nombre = :nombre, telefono = :telefono, correo = :correo
            ");
            $stmtUser->execute([
                ':cedula' => trim($data['cedula']),
                ':nombre' => trim($data['nombre']),
                ':telefono' => trim($data['telefono'] ?? ''),
                ':correo' => trim($data['correo'] ?? '')
            ]);

            // 2. Obtener el ID del rol desde su nombre
            $stmtRol = $this->conn->prepare("SELECT id_rol FROM rol WHERE nombre_rol = :nombre_rol");
            $stmtRol->execute([':nombre_rol' => $data['rol']]);
            $rolData = $stmtRol->fetch(PDO::FETCH_ASSOC);
            $id_rol = $rolData ? (int)$rolData['id_rol'] : 2; // Por defecto OPERADOR

            // 3. Generar hash de la contraseña
            $password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

            // 4. Insertar en la tabla personal
            $stmtPersonal = $this->conn->prepare("
                INSERT INTO personal (cedula_users, id_rol, usuario, password_hash)
                VALUES (:cedula_users, :id_rol, :usuario, :password_hash)
            ");
            $stmtPersonal->execute([
                ':cedula_users' => trim($data['cedula']),
                ':id_rol' => $id_rol,
                ':usuario' => trim($data['usuario']),
                ':password_hash' => $password_hash
            ]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
    
    /**
     * Eliminar un usuario por su ID de personal
     */
    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM personal WHERE id_personal = :id");
        return $stmt->execute([':id' => $id]);
    }
}
?>
