<?php
// ============================================================
//  PARKINGSURE - API Tipos de Servicio
//  Archivo: tiposervicioapi.php - Controlador de tipos de servicio
// ============================================================
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar solicitudes
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
        default:
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
}

function handleGet($conn) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'getAll':
            // Obtener todos los tipos de servicio activos
            $stmt = $conn->prepare('SELECT id_tipo_servicio, nombre_tipo_servicio, tarifa, estado FROM tipo_servicio WHERE estado = "ACTIVO" ORDER BY nombre_tipo_servicio');
            $stmt->execute();
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $tipos,
                'count' => count($tipos)
            ]);
            break;
            
        case 'getById':
            // Obtener tipo de servicio por ID
            $id = $_GET['id_tipo_servicio'] ?? '';
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID de tipo de servicio es obligatorio']);
                break;
            }
            
            $stmt = $conn->prepare('SELECT id_tipo_servicio, nombre_tipo_servicio, tarifa, estado FROM tipo_servicio WHERE id_tipo_servicio = ?');
            $stmt->execute([$id]);
            $tipo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tipo) {
                echo json_encode([
                    'success' => true,
                    'data' => $tipo
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Tipo de servicio no encontrado']);
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
        case 'create':
            // Crear nuevo tipo de servicio
            $nombre_tipo_servicio = $data['nombre_tipo_servicio'] ?? '';
            $tarifa = $data['tarifa'] ?? 0;
            $estado = $data['estado'] ?? 'ACTIVO';
            
            if (empty($nombre_tipo_servicio)) {
                echo json_encode(['success' => false, 'message' => 'Nombre del tipo de servicio es obligatorio']);
                break;
            }
            
            // Verificar que no exista un tipo con el mismo nombre
            $stmtCheck = $conn->prepare('SELECT COUNT(*) FROM tipo_servicio WHERE nombre_tipo_servicio = ?');
            $stmtCheck->execute([$nombre_tipo_servicio]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un tipo de servicio con ese nombre']);
                break;
            }
            
            $stmt = $conn->prepare('INSERT INTO tipo_servicio (nombre_tipo_servicio, tarifa, estado) VALUES (?, ?, ?)');
            $resultado = $stmt->execute([$nombre_tipo_servicio, $tarifa, $estado]);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de servicio creado correctamente',
                    'id_tipo_servicio' => $conn->lastInsertId()
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear tipo de servicio']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}
?>
