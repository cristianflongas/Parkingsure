<?php
// ============================================================
//  PARKINGSURE - Controlador: Usuario
//  Archivo: controllerusuario.php - Procesamiento de usuarios
//  Arquitectura MVC - Controlador de usuarios
// ============================================================
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/UsuarioModel.php';

// Verificar que el usuario esté autenticado y sea ADMINISTRADOR
if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'ADMINISTRADOR') {
    header('Location: ../views/usuarios/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar y limpiar datos del formulario
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cedula   = trim($_POST['cedula'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');
    $rol      = trim($_POST['rol'] ?? 'OPERADOR');
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validar campos obligatorios
    if (empty($nombre) || empty($cedula) || empty($correo) || empty($usuario) || empty($password)) {
        header('Location: ../views/usuarios/usuarios.php?error=' . urlencode('Todos los campos son obligatorios excepto el teléfono.'));
        exit();
    }

    try {
        $database = new Database();
        $db = $database->conectar();
        
        $usuarioModel = new UsuarioModel($db);

        // 1. Validar duplicado de nombre de usuario en 'personal'
        $stmtCheckUser = $db->prepare("SELECT id_personal FROM personal WHERE usuario = :usuario");
        $stmtCheckUser->execute([':usuario' => $usuario]);
        if ($stmtCheckUser->fetch()) {
            header('Location: ../views/usuarios/usuarios.php?error=' . urlencode("El nombre de usuario '{$usuario}' ya está registrado."));
            exit();
        }

        // 2. Validar duplicado de cédula en 'personal'
        $stmtCheckCedula = $db->prepare("SELECT id_personal FROM personal WHERE cedula_users = :cedula");
        $stmtCheckCedula->execute([':cedula' => $cedula]);
        if ($stmtCheckCedula->fetch()) {
            header('Location: ../views/usuarios/usuarios.php?error=' . urlencode("La cédula '{$cedula}' ya está registrada para otro personal del sistema."));
            exit();
        }

        // Preparar datos para inserción
        $data = [
            'nombre'   => $nombre,
            'telefono' => $telefono,
            'cedula'   => $cedula,
            'correo'   => $correo,
            'rol'      => $rol,
            'usuario'  => $usuario,
            'password' => $password
        ];

        // Guardar usando el modelo
        if ($usuarioModel->crear($data)) {
            header('Location: ../views/usuarios/usuarios.php?msg=creado');
            exit();
        } else {
            header('Location: ../views/usuarios/usuarios.php?error=' . urlencode('Error al registrar el usuario en la base de datos.'));
            exit();
        }

    } catch (Exception $e) {
        header('Location: ../views/usuarios/usuarios.php?error=' . urlencode($e->getMessage()));
        exit();
    }

} else {
    // Redireccionar si se intenta ingresar por GET
    header('Location: ../views/usuarios/usuarios.php');
    exit();
}
?>
