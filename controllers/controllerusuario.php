<?php

require_once "../config/database.php";

$database = new Database();
$conn = $database->conectar();

$nombre   = $_POST['nombre'];

$telefono = $_POST['telefono'];
$cedula   = $_POST['cedula'];
$rol      = $_POST['rol'];
$correo   = $_POST['correo'];        // ← NUEVO
$usuario  = $_POST['usuario'];
$password = $_POST['password'];

$password_hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO personal
        (nombre, telefono, cedula, rol, correo, usuario, password_hash)
        VALUES
        (:nombre, :telefono, :cedula, :rol, :correo, :usuario, :password_hash)";

$stmt = $conn->prepare($sql);

$stmt->bindParam(":nombre",        $nombre);
$stmt->bindParam(":telefono",      $telefono);
$stmt->bindParam(":cedula",        $cedula);
$stmt->bindParam(":rol",           $rol);
$stmt->bindParam(":correo",        $correo);        // ← NUEVO
$stmt->bindParam(":usuario",       $usuario);
$stmt->bindParam(":password_hash", $password_hash);

if($stmt->execute()){
    header("Location: ../views/usuarios/usuarios.php?msg=creado");
} else {
    header("Location: ../views/usuarios/usuarios.php?msg=error");
}