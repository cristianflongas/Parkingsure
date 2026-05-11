<?php
// ============================================================
//  PARKINGSURE - API de Entradas
//  Archivo: entradaapi.php - Controlador para gestión de entradas
// ============================================================
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $database = new Database();
    $conn = $database->conectar();
    
    switch ($method) {
        case 'GET':
            handleGet($conn);
            break;
        case 'POST':
            handlePost($conn);
            break;
        case 'PUT':
            handlePut($conn);
            break;
        case 'DELETE':
            handleDelete($conn);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function handleGet($conn) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'getActive':
            // Obtener todas las entradas activas
            $stmt = $conn->prepare(
                'SELECT e.id_entrada, e.id_modulo, e.placa, e.fecha_hora_entrada, e.estado,
                        m.ubicacion, v.marca, v.modelo
                 FROM entrada e
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa = v.placa
                 WHERE e.estado = "ACTIVO"
                 ORDER BY e.fecha_hora_entrada DESC'
            );
            $stmt->execute();
            $entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $entradas
            ]);
            break;
            
        case 'getAll':
            // Obtener todas las entradas (incluyendo cerradas)
            $stmt = $conn->prepare(
                'SELECT e.id_entrada, e.id_modulo, e.placa, e.fecha_hora_entrada, e.estado,
                        m.ubicacion, v.marca, v.modelo
                 FROM entrada e
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa = v.placa
                 ORDER BY e.fecha_hora_entrada DESC'
            );
            $stmt->execute();
            $entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $entradas
            ]);
            break;
            
        case 'getById':
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID de entrada es obligatorio']);
                break;
            }
            
            $stmt = $conn->prepare(
                'SELECT e.*, m.ubicacion, v.marca, v.modelo
                 FROM entrada e
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa = v.placa
                 WHERE e.id_entrada = ?'
            );
            $stmt->execute([$id]);
            $entrada = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($entrada) {
                echo json_encode(['success' => true, 'data' => $entrada]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Entrada no encontrada']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'assign':
            // Asignar vehículo a módulo (registrar entrada)
            $id_modulo        = $data['id_modulo']        ?? '';
            $placa            = strtoupper(trim($data['placa'] ?? ''));
            $id_tipo_servicio = $data['id_tipo_servicio'] ?? '';
            
            if (empty($id_modulo) || empty($placa) || empty($id_tipo_servicio)) {
                echo json_encode(['success' => false, 'message' => 'Módulo, placa y tipo de servicio son obligatorios']);
                break;
            }
            
            // Obtener id_personal de la sesión
            session_start();
            $id_personal = $_SESSION['id_personal'] ?? 1;
            
            // Verificar que el módulo exista y esté DISPONIBLE
            $stmtMod = $conn->prepare('SELECT estado FROM modulo WHERE id_modulo = ?');
            $stmtMod->execute([$id_modulo]);
            $modulo = $stmtMod->fetch(PDO::FETCH_ASSOC);
            
            if (!$modulo) {
                echo json_encode(['success' => false, 'message' => 'Módulo no encontrado']);
                break;
            }
            if ($modulo['estado'] !== 'DISPONIBLE') {
                echo json_encode(['success' => false, 'message' => 'El módulo no está disponible (estado: ' . $modulo['estado'] . ')']);
                break;
            }
            
            // Verificar que el vehículo exista en la BD
            $stmtVeh = $conn->prepare('SELECT placa FROM vehiculo WHERE placa = ?');
            $stmtVeh->execute([$placa]);
            if (!$stmtVeh->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El vehículo con placa ' . $placa . ' no está registrado en el sistema']);
                break;
            }
            
            // Verificar que el vehículo no esté ya estacionado
            $stmtOcup = $conn->prepare('SELECT COUNT(*) FROM entrada WHERE placa = ? AND estado = "ACTIVO"');
            $stmtOcup->execute([$placa]);
            if ($stmtOcup->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'El vehículo ya está estacionado en otro módulo']);
                break;
            }
            
            // Verificar que el tipo de servicio exista
            $stmtTs = $conn->prepare('SELECT id_tipo_servicio FROM tipo_servicio WHERE id_tipo_servicio = ? AND estado = "ACTIVO"');
            $stmtTs->execute([$id_tipo_servicio]);
            if (!$stmtTs->fetch()) {
                echo json_encode(['success' => false, 'message' => 'Tipo de servicio no válido']);
                break;
            }
            
            // Iniciar transacción
            $conn->beginTransaction();
            try {
                // Registrar entrada
                $stmtEntrada = $conn->prepare(
                    'INSERT INTO entrada (id_modulo, placa, id_personal, id_tipo_servicio, fecha_hora_entrada, estado)
                     VALUES (?, ?, ?, ?, NOW(), "ACTIVO")'
                );
                $stmtEntrada->execute([$id_modulo, $placa, $id_personal, $id_tipo_servicio]);
                
                // Marcar módulo como OCUPADO
                $stmtModulo = $conn->prepare('UPDATE modulo SET estado = "OCUPADO" WHERE id_modulo = ?');
                $stmtModulo->execute([$id_modulo]);
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Entrada registrada correctamente',
                    'data'    => ['id_entrada' => $conn->lastInsertId()]
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Error al registrar entrada: ' . $e->getMessage()]);
            }
            break;
            
        case 'create':
            // Crear entrada manual
            $id_modulo = $data['id_modulo'] ?? '';
            $placa = $data['placa'] ?? '';
            
            if (empty($id_modulo) || empty($placa)) {
                echo json_encode(['success' => false, 'message' => 'ID de módulo y placa son obligatorios']);
                break;
            }
            
            // Lógica similar a assign
            $stmtCheck = $conn->prepare('SELECT estado FROM modulo WHERE id_modulo = ?');
            $stmtCheck->execute([$id_modulo]);
            $modulo = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if (!$modulo || $modulo['estado'] !== 'DISPONIBLE') {
                echo json_encode(['success' => false, 'message' => 'Módulo no disponible']);
                break;
            }
            
            $conn->beginTransaction();
            
            try {
                $stmtEntrada = $conn->prepare(
                    'INSERT INTO entrada (id_modulo, placa, fecha_hora_entrada, estado) VALUES (?, ?, NOW(), "ACTIVO")'
                );
                $stmtEntrada->execute([$id_modulo, $placa]);
                
                $stmtModulo = $conn->prepare('UPDATE modulo SET estado = "OCUPADO" WHERE id_modulo = ?');
                $stmtModulo->execute([$id_modulo]);
                
                $conn->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Entrada registrada correctamente'
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de entrada es obligatorio']);
        return;
    }
    
    // Actualizar entrada (generalmente para cerrarla)
    $stmt = $conn->prepare(
        'UPDATE entrada SET fecha_salida = NOW(), estado = "CERRADO" WHERE id_entrada = ? AND estado = "ACTIVO"'
    );
    $resultado = $stmt->execute([$id]);
    
    if ($resultado) {
        // Liberar módulo
        $stmtModulo = $conn->prepare(
            'UPDATE modulo SET estado = "DISPONIBLE" WHERE id_modulo = (SELECT id_modulo FROM entrada WHERE id_entrada = ?)'
        );
        $stmtModulo->execute([$id]);
        
        echo json_encode(['success' => true, 'message' => 'Entrada cerrada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al cerrar entrada']);
    }
}

function handleDelete($conn) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de entrada es obligatorio']);
        return;
    }
    
    // Eliminar entrada (solo si no está activa)
    $stmt = $conn->prepare('DELETE FROM entrada WHERE id_entrada = ? AND estado != "ACTIVO"');
    $resultado = $stmt->execute([$id]);
    
    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Entrada eliminada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar entrada o entrada está activa']);
    }
}
?>
