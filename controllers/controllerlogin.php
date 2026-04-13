<?php
session_start();
require_once "../config/database.php";

$database = new Database();
$conn = $database->conectar();

$usuario  = $_POST['usuario'];
$password = $_POST['password'];
$rol      = $_POST['rol'];

// Busca el usuario en la BD por usuario y rol
$sql = "SELECT * FROM personal WHERE usuario = :usuario AND rol = :rol";

$stmt = $conn->prepare($sql);
$stmt->bindParam(":usuario", $usuario);
$stmt->bindParam(":rol",     $rol);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica que exista y que la contraseña sea correcta
if($user && password_verify($password, $user['password_hash'])){

    // Guarda datos en sesión
    $_SESSION['id']      = $user['id_personal'];
    $_SESSION['nombre']  = $user['nombre'];
    $_SESSION['usuario'] = $user['usuario'];
    $_SESSION['rol']     = $user['rol'];
    $_SESSION['correo']  = $user['correo'];   // solo para recuperar contraseña

    // Redirige al dashboard
    header("Location: ../views/usuarios/dashboard.php");
    exit();

} else {
    // Credenciales incorrectas
    header("Location: ../views/usuarios/login.php?error=1");
    exit();
}
?>