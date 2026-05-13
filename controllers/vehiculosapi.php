<?php
/**
 * API de Vehículos - ParkingSure
 * Sistema MVC - Controlador para gestión de vehículos
 */
require_once __DIR__ . '/../config/database.php';

// Desactivar cualquier salida de errores y warnings
error_reporting(0);
ini_set('display_errors', 0);

// Limpiar cualquier salida anterior
if (ob_get_level()) {
    ob_end_clean();
}

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
    // Limpiar cualquier salida antes de enviar JSON
    ob_clean();
    
    // Capturar el error en el log pero no mostrarlo en pantalla
    error_log("❌ Error en API: " . $e->getMessage());
    
    // Enviar respuesta JSON limpia
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor',
        'error' => $e->getMessage()
    ]);
}

function handleGet($conn) {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'getClientes':
            // Obtener todos los clientes para el select
            error_log("🔍 API: Ejecutando getClientes");
            
            try {
                // Verificar si la tabla cliente existe y tiene datos
                $stmt = $conn->prepare("SELECT COUNT(*) as total FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'cliente'");
                $stmt->execute();
                $tableExists = $stmt->fetch(PDO::FETCH_ASSOC);
                error_log("📋 Tabla cliente existe: " . json_encode($tableExists));
                
                if ($tableExists['total'] > 0) {
                    // Construir consulta con JOIN entre cliente y users
                    $query = "SELECT 
                        c.id_cliente,
                        c.cedula_users as cedula,
                        c.telefono,
                        c.correo,
                        u.nombre as nombre_usuario,
                        u.usuario as username
                    FROM cliente c
                    LEFT JOIN users u ON c.cedula_users = u.cedula
                    ORDER BY u.nombre ASC";
                    
                    error_log("📋 Query con JOIN: " . $query);
                    
                    $stmt = $conn->prepare($query);
                    if ($stmt->execute()) {
                        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        error_log("📊 API: Se encontraron " . count($clientes) . " clientes con JOIN");
                        
                        // Formatear para compatibilidad con frontend
                        $clientesFormateados = [];
                        foreach ($clientes as $cliente) {
                            $clientesFormateados[] = [
                                'id_cliente' => $cliente['id_cliente'],
                                'nombre' => $cliente['nombre_usuario'] ?: $cliente['id_cliente'], // Usar nombre de usuario o ID como fallback
                                'cedula' => $cliente['cedula'],
                                'telefono' => $cliente['telefono'],
                                'correo' => $cliente['correo']
                            ];
                        }
                        
                        // Limpiar cualquier salida antes de enviar JSON
                        ob_clean();
                        
                        // Desactivar buffer de salida para evitar HTML mezclado
                        if (ob_get_level()) {
                            ob_end_clean();
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $clientesFormateados,
                            'count' => count($clientesFormateados),
                            'source' => 'cliente_users_join',
                            'query' => $query
                        ]);
                        
                        // Salir para evitar cualquier salida adicional
                        exit;
                    } else {
                        throw new Exception("Error ejecutando consulta con JOIN");
                    }
                } else {
                    error_log("⚠️ Tabla cliente no existe, intentando con users");
                    throw new Exception("Tabla cliente no existe");
                }
                
            } catch (Exception $e) {
                error_log("❌ API: Error principal: " . $e->getMessage());
                
                try {
                    // Alternativa: usar tabla users
                    $stmt = $conn->prepare("DESCRIBE users");
                    $stmt->execute();
                    $userColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("📋 Estructura tabla users: " . json_encode($userColumns));
                    
                    $userFields = array_column($userColumns, 'Field');
                    error_log("📋 Columnas users: " . json_encode($userFields));
                    
                    // Construir consulta para users - usar solo columnas que existen
                    $selectUserFields = [];
                    foreach ($userFields as $field) {
                        $selectUserFields[] = "u.`$field`";
                    }
                    $userFieldsStr = implode(', ', $selectUserFields);
                    
                    // Buscar columna de rol (puede tener diferentes nombres)
                    $rolColumn = null;
                    foreach ($userColumns as $col) {
                        $fieldName = strtolower($col['Field']);
                        if (strpos($fieldName, 'rol') !== false) {
                            $rolColumn = $col['Field'];
                            break;
                        }
                    }
                    
                    if ($rolColumn) {
                        $query = "SELECT $userFieldsStr FROM users u WHERE u.`$rolColumn` IN ('CLIENTE', 'OPERADOR', 'ADMINISTRADOR') ORDER BY u.nombre ASC";
                    } else {
                        // Si no hay columna de rol, traer todos los usuarios
                        $query = "SELECT $userFieldsStr FROM users u ORDER BY u.nombre ASC";
                    }
                    error_log("📋 Query users: " . $query);
                    
                    $stmt = $conn->prepare($query);
                    if ($stmt->execute()) {
                        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        error_log("� API: Se encontraron " . count($clientes) . " usuarios como clientes");
                        
                        echo json_encode([
                            'success' => true,
                            'data' => $clientes,
                            'count' => count($clientes),
                            'source' => 'tabla_users',
                            'query' => $query
                        ]);
                    } else {
                        throw new Exception("Error ejecutando consulta de users");
                    }
                    
                } catch (Exception $e2) {
                    error_log("❌ API: Error alternativo: " . $e2->getMessage());
                    
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error crítico: ' . $e2->getMessage(),
                        'debug' => [
                            'primary_error' => $e->getMessage(),
                            'fallback_error' => $e2->getMessage()
                        ]
                    ]);
                }
            }
            break;
            
        case 'getAll':
            // Obtener todos los vehículos con información del cliente
            $stmt = $conn->prepare(
                'SELECT v.placa, v.marca, v.modelo, v.anio, v.color,
                        u.nombre as nombre_cliente, u.telefono, u.correo
                 FROM vehiculo v
                 LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
                 LEFT JOIN users u ON c.cedula_users = u.cedula
                 ORDER BY v.placa'
            );
            $stmt->execute();
            $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $vehiculos]);
            break;
            
        case 'getById':
            $placa = $_GET['placa'] ?? '';
            if (empty($placa)) {
                echo json_encode(['success' => false, 'message' => 'Placa es obligatoria']);
                break;
            }
            
            $stmt = $conn->prepare(
                'SELECT v.placa, v.marca, v.modelo, v.anio, v.color,
                        u.nombre as nombre_cliente, u.telefono, u.correo
                 FROM vehiculo v
                 LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
                 LEFT JOIN users u ON c.cedula_users = u.cedula
                 WHERE v.placa = ?'
            );
            $stmt->execute([$placa]);
            $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vehiculo) {
                echo json_encode([
                    'success' => true,
                    'data' => $vehiculo
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Vehículo no encontrado']);
            }
            break;
            
        case 'getActivos':
            // Obtener vehículos activos (con entradas activas)
            $stmt = $conn->prepare(
                'SELECT v.placa, v.marca, v.modelo, v.anio, v.color,
                        e.id_entrada, e.fecha_hora_entrada, m.ubicacion,
                        u.nombre as nombre_cliente
                 FROM vehiculo v
                 INNER JOIN entrada e ON v.placa = e.placa AND e.estado = "ACTIVO"
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
                 LEFT JOIN users u ON c.cedula_users = u.cedula
                 ORDER BY e.fecha_hora_entrada DESC'
            );
            $stmt->execute();
            $vehiculos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $vehiculos_activos,
                'total' => count($vehiculos_activos)
            ]);
            break;
            
        case 'getDisponibles':
            // Obtener vehículos disponibles (sin entradas activas)
            $stmt = $conn->prepare(
                'SELECT v.placa, v.marca, v.modelo, v.anio, v.color,
                        u.nombre as nombre_cliente, u.telefono, u.correo
                 FROM vehiculo v
                 LEFT JOIN entrada e ON v.placa = e.placa AND e.estado = "ACTIVO"
                 LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
                 LEFT JOIN users u ON c.cedula_users = u.cedula
                 WHERE e.placa IS NULL
                 ORDER BY v.placa'
            );
            $stmt->execute();
            $vehiculos_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $vehiculos_disponibles,
                'total' => count($vehiculos_disponibles)
            ]);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción GET no válida']);
            break;
    }
}

function handlePost($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'create':
            // Crear nuevo vehículo
            $placa = $data['placa'] ?? '';
            $id_cliente = $data['id_cliente'] ?? '';
            $marca = $data['marca'] ?? '';
            $modelo = $data['modelo'] ?? '';
            $anio = $data['anio'] ?? null;
            $color = $data['color'] ?? '';
            
            if (empty($placa) || empty($id_cliente) || empty($marca)) {
                echo json_encode(['success' => false, 'message' => 'Placa, id_cliente y marca son obligatorios']);
                break;
            }
            
            // Verificar si el vehículo ya existe
            $stmt = $conn->prepare('SELECT placa FROM vehiculo WHERE placa = ?');
            $stmt->execute([$placa]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El vehículo ya existe']);
                break;
            }
            
            // Insertar vehículo
            $stmt = $conn->prepare(
                'INSERT INTO vehiculo (placa, id_cliente, marca, modelo, anio, color) 
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            
            $resultado = $stmt->execute([$placa, $id_cliente, $marca, $modelo, $anio, $color]);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Vehículo creado exitosamente',
                    'data' => ['placa' => $placa]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear vehículo']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción POST no válida']);
            break;
    }
}

function handlePut($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'update':
            // Actualizar vehículo
            $placa = $data['placa'] ?? '';
            $id_cliente = $data['id_cliente'] ?? null;
            $marca = $data['marca'] ?? null;
            $modelo = $data['modelo'] ?? null;
            $anio = $data['anio'] ?? null;
            $color = $data['color'] ?? null;
            
            if (empty($placa)) {
                echo json_encode(['success' => false, 'message' => 'Placa es obligatoria']);
                break;
            }
            
            // Construir consulta dinámica para actualizar solo los campos proporcionados
            $fields = [];
            $values = [];
            
            if ($id_cliente !== null) {
                $fields[] = 'id_cliente = ?';
                $values[] = $id_cliente;
            }
            if ($marca !== null) {
                $fields[] = 'marca = ?';
                $values[] = $marca;
            }
            if ($modelo !== null) {
                $fields[] = 'modelo = ?';
                $values[] = $modelo;
            }
            if ($anio !== null) {
                $fields[] = 'anio = ?';
                $values[] = $anio;
            }
            if ($color !== null) {
                $fields[] = 'color = ?';
                $values[] = $color;
            }
            
            if (empty($fields)) {
                echo json_encode(['success' => false, 'message' => 'No hay campos para actualizar']);
                break;
            }
            
            $values[] = $placa;
            
            $sql = 'UPDATE vehiculo SET ' . implode(', ', $fields) . ' WHERE placa = ?';
            $stmt = $conn->prepare($sql);
            $resultado = $stmt->execute($values);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Vehículo actualizado exitosamente'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar vehículo']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción PUT no válida']);
            break;
    }
}

function handleDelete($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    
    switch ($action) {
        case 'delete':
            // Eliminar vehículo
            $placa = $data['placa'] ?? '';
            
            if (empty($placa)) {
                echo json_encode(['success' => false, 'message' => 'Placa es obligatoria']);
                break;
            }
            
            // Verificar si el vehículo tiene entradas activas
            $stmt = $conn->prepare('SELECT COUNT(*) as count FROM entrada WHERE placa = ? AND estado = "ACTIVO"');
            $stmt->execute([$placa]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($count > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar un vehículo con entradas activas']);
                break;
            }
            
            // Eliminar vehículo
            $stmt = $conn->prepare('DELETE FROM vehiculo WHERE placa = ?');
            $resultado = $stmt->execute([$placa]);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Vehículo eliminado exitosamente'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar vehículo']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción DELETE no válida']);
            break;
    }
}
?>
