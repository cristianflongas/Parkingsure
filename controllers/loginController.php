<?php
session_start();
require_once "../config/database.php";

$database = new Database();
$conn = $database->conectar();

$usuario  = $_POST['usuario'];
$password = $_POST['password'];

// Busca el usuario en la BD por usuario (con JOIN para obtener el rol y datos personales de users)
$sql = "SELECT p.*, r.nombre_rol, u.nombre, u.correo, u.telefono 
        FROM personal p 
        INNER JOIN rol r ON p.id_rol = r.id_rol 
        INNER JOIN users u ON p.cedula_users = u.cedula
        WHERE p.usuario = :usuario";

$stmt = $conn->prepare($sql);
$stmt->bindParam(":usuario", $usuario);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica que exista y que la contraseña sea correcta
if($user && password_verify($password, $user['password_hash'])){

    // Guarda datos en sesión
    $_SESSION['id_usuario'] = $user['id_personal'];
    $_SESSION['nombre']      = $user['nombre'];
    $_SESSION['usuario']     = $user['usuario'];
    $_SESSION['rol']         = $user['nombre_rol'];  // Usar nombre_rol del JOIN
    $_SESSION['correo']      = $user['correo'];   // solo para recuperar contraseña
    $_SESSION['user']        = $user['id_personal']; // Para compatibilidad con vistas

    // Redirige al dashboard
    header("Location: ../views/usuarios/dashboard.php");
    exit();

} else {
    // Credenciales incorrectas
    header("Location: ../views/usuarios/login.php");
    exit();
}
?>