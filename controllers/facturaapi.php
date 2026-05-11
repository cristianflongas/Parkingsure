<?php
// ============================================================
//  PARKINGSURE - API de Facturas
//  Archivo: facturaapi.php - Controlador para gestión de facturas
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
        case 'getPendientes':
            // Obtener facturas pendientes con información completa
            $stmt = $conn->prepare(
                'SELECT f.id_factura, f.id_salida, f.monto_total, f.metodo_pago, f.estado_pago,
                        f.fecha_emision, s.fecha_hora_salida, e.placa, e.fecha_hora_entrada,
                        ts.nombre_tipo_servicio, ts.tarifa, m.ubicacion
                 FROM factura f
                 INNER JOIN salida s ON f.id_salida = s.id_salida
                 INNER JOIN entrada e ON s.id_entrada = e.id_entrada
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 INNER JOIN modulo m ON e.id_modulo = m.id_modulo
                 WHERE f.estado_pago = "PENDIENTE"
                 ORDER BY f.fecha_emision DESC'
            );
            $stmt->execute();
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular tiempo de estancia para cada factura
            foreach ($facturas as &$factura) {
                $fecha_entrada = new DateTime($factura['fecha_hora_entrada']);
                $fecha_salida = new DateTime($factura['fecha_hora_salida']);
                $diferencia = $fecha_entrada->diff($fecha_salida);
                
                $factura['tiempo_estancia'] = sprintf(
                    '%d días, %d horas, %d minutos',
                    $diferencia->days,
                    $diferencia->h,
                    $diferencia->i
                );
                
                $horas_totales = ($diferencia->days * 24) + $diferencia->h + ($diferencia->i / 60);
                $factura['horas_cobradas'] = ceil($horas_totales);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $facturas
            ]);
            break;
            
        case 'getPagadas':
            // Obtener facturas pagadas
            $fecha = $_GET['fecha'] ?? null;
            
            // Logging para depuración
            error_log("DEBUG: getPagadas llamado con fecha: " . ($fecha ? $fecha : 'NULL'));
            error_log("DEBUG: Parámetros GET recibidos: " . json_encode($_GET));
            
            try {
                // Verificar conexión básica
                error_log("DEBUG: Verificando conexión a BD...");
                $testStmt = $conn->query("SELECT 1 as test");
                $testResult = $testStmt->fetch();
                error_log("DEBUG: Conexión OK: " . json_encode($testResult));
                
                // Verificar si existe la tabla factura
                $tableCheck = $conn->query("SHOW TABLES LIKE 'factura'");
                $tableExists = $tableCheck->fetch();
                error_log("DEBUG: Tabla factura existe: " . ($tableExists ? "SÍ" : "NO"));
                
                // Verificar estructura de la tabla factura
                if ($tableExists) {
                    $structureStmt = $conn->query("DESCRIBE factura");
                    $structure = $structureStmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("DEBUG: Estructura tabla factura: " . json_encode($structure));
                    
                    // Contar todas las facturas
                    $allStmt = $conn->query("SELECT COUNT(*) as total FROM factura");
                    $allCount = $allStmt->fetchColumn();
                    error_log("DEBUG: Total facturas en BD: " . $allCount);
                    
                    // Contar facturas pagadas
                    $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM factura WHERE estado_pago = 'PAGADA'");
                    $checkStmt->execute();
                    $totalPagadas = $checkStmt->fetchColumn();
                    error_log("DEBUG: Total facturas pagadas en BD: " . $totalPagadas);
                    
                    // Mostrar todas las facturas (sin filtros)
                    $allFacturasStmt = $conn->query("SELECT * FROM factura ORDER BY fecha_emision DESC LIMIT 5");
                    $allFacturas = $allFacturasStmt->fetchAll(PDO::FETCH_ASSOC);
                    error_log("DEBUG: Últimas 5 facturas: " . json_encode($allFacturas));
                    
                    if ($totalPagadas > 0) {
                        // Consulta simple para facturas pagadas
                        $simpleStmt = $conn->prepare("SELECT * FROM factura WHERE estado_pago = 'PAGADA' ORDER BY fecha_emision DESC LIMIT 5");
                        $simpleStmt->execute();
                        $simpleResult = $simpleStmt->fetchAll(PDO::FETCH_ASSOC);
                        error_log("DEBUG: Facturas pagadas (consulta simple): " . json_encode($simpleResult));
                        
                        if ($fecha) {
                            error_log("DEBUG: Filtrando por fecha: " . $fecha);
                            
                            // Primero ver las fechas reales de las facturas pagadas
                            $fechasRealesStmt = $conn->prepare("SELECT id_factura, fecha_emision, DATE(fecha_emision) as fecha_solo_fecha FROM factura WHERE estado_pago = 'PAGADA' ORDER BY fecha_emision DESC LIMIT 5");
                            $fechasRealesStmt->execute();
                            $fechasReales = $fechasRealesStmt->fetchAll(PDO::FETCH_ASSOC);
                            error_log("DEBUG: Fechas reales de facturas pagadas: " . json_encode($fechasReales));
                            
                            // Intentar búsqueda normal con JOINs para obtener datos completos
                            $fechaStmt = $conn->prepare("SELECT f.id_factura, f.id_salida, f.fecha_emision, f.monto_total, f.metodo_pago, f.estado_pago,
                                                             s.fecha_hora_salida, e.placa, ts.nombre_tipo_servicio
                                                      FROM factura f
                                                      LEFT JOIN salida s ON f.id_salida = s.id_salida
                                                      LEFT JOIN entrada e ON s.id_entrada = e.id_entrada
                                                      LEFT JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                                                      WHERE f.estado_pago = 'PAGADA' AND DATE(f.fecha_emision) = ? 
                                                      ORDER BY f.fecha_emision DESC");
                            $fechaStmt->execute([$fecha]);
                            $fechaResult = $fechaStmt->fetchAll(PDO::FETCH_ASSOC);
                            error_log("DEBUG: Facturas pagadas para fecha " . $fecha . ": " . json_encode($fechaResult));
                            
                            // Si no hay resultados, intentar con diferentes formatos
                            if (empty($fechaResult)) {
                                error_log("DEBUG: Intentando búsqueda con diferentes formatos de fecha...");
                                
                                // Intentar sin DATE() pero con JOINs
                                $fechaStmt2 = $conn->prepare("SELECT f.id_factura, f.id_salida, f.fecha_emision, f.monto_total, f.metodo_pago, f.estado_pago,
                                                                 s.fecha_hora_salida, e.placa, ts.nombre_tipo_servicio
                                                          FROM factura f
                                                          LEFT JOIN salida s ON f.id_salida = s.id_salida
                                                          LEFT JOIN entrada e ON s.id_entrada = e.id_entrada
                                                          LEFT JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                                                          WHERE f.estado_pago = 'PAGADA' AND f.fecha_emision LIKE ? 
                                                          ORDER BY f.fecha_emision DESC");
                                $fechaStmt2->execute([$fecha . '%']);
                                $fechaResult2 = $fechaStmt2->fetchAll(PDO::FETCH_ASSOC);
                                error_log("DEBUG: Facturas con LIKE '" . $fecha . "%': " . json_encode($fechaResult2));
                                
                                // Si encontramos resultados con LIKE, usar esos
                                if (!empty($fechaResult2)) {
                                    $fechaResult = $fechaResult2;
                                }
                            }
                            
                            echo json_encode([
                                'success' => true,
                                'data' => $fechaResult,
                                'debug_info' => [
                                    'connection_test' => $testResult ?? null,
                                    'table_exists' => $tableExists ?? false,
                                    'total_facturas' => $allCount ?? 0,
                                    'total_pagadas' => $totalPagadas ?? 0,
                                    'fecha_buscada' => $fecha,
                                    'resultados_encontrados' => count($fechaResult ?? []),
                                    'fechas_reales' => $fechasReales ?? []
                                ]
                            ]);
                            return;
                        }
                    } else {
                        error_log("DEBUG: No hay facturas pagadas para mostrar");
                    }
                }
            } catch (Exception $e) {
                error_log("DEBUG: Error en consulta: " . $e->getMessage());
                echo json_encode([
                    'success' => false,
                    'message' => 'Error en consulta: ' . $e->getMessage()
                ]);
                return;
            }
            
            // Si no hay fecha especificada o no hay resultados, devolver array vacío
            echo json_encode([
                'success' => true,
                'data' => [],
                'debug_info' => [
                    'connection_test' => $testResult ?? null,
                    'table_exists' => $tableExists ?? false,
                    'total_facturas' => $allCount ?? 0,
                    'total_pagadas' => $totalPagadas ?? 0,
                    'fecha_buscada' => $fecha ?? 'null',
                    'resultados_encontrados' => 0
                ]
            ]);
            break;
            
        case 'getAll':
            // Obtener todas las facturas
            $estado = $_GET['estado'] ?? '';
            
            $sql = 'SELECT f.id_factura, f.id_salida, f.monto_total, f.metodo_pago, f.estado_pago,
                           f.fecha_emision, s.fecha_hora_salida, e.placa, ts.nombre_tipo_servicio
                    FROM factura f
                    INNER JOIN salida s ON f.id_salida = s.id_salida
                    INNER JOIN entrada e ON s.id_entrada = e.id_entrada
                    INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio';
            
            if (!empty($estado)) {
                $sql .= ' WHERE f.estado_pago = :estado';
            }
            
            $sql .= ' ORDER BY f.fecha_emision DESC';
            
            $stmt = $conn->prepare($sql);
            
            if (!empty($estado)) {
                $stmt->bindParam(':estado', $estado);
            }
            
            $stmt->execute();
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $facturas
            ]);
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
        case 'procesarPago':
            $id_factura  = $data['id_factura']  ?? '';
            $metodo_pago = $data['metodo_pago'] ?? 'EFECTIVO';

            if (empty($id_factura)) {
                echo json_encode(['success' => false, 'message' => 'ID de factura es obligatorio', 'recibido' => $data]);
                break;
            }

            // Forzar entero
            $id_factura = (int) $id_factura;

            $metodos_validos = ['EFECTIVO', 'TRANSFERENCIA', 'TARJETA', 'NEQUI'];
            if (!in_array($metodo_pago, $metodos_validos)) {
                $metodo_pago = 'EFECTIVO';
            }

            $stmt = $conn->prepare(
                'UPDATE factura
                 SET estado_pago = "PAGADA", metodo_pago = ?
                 WHERE id_factura = ? AND estado_pago = "PENDIENTE"'
            );
            $stmt->execute([$metodo_pago, $id_factura]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Pago procesado correctamente']);
            } else {
                // Verificar si existe pero ya estaba pagada
                $stmtCheck = $conn->prepare('SELECT estado_pago FROM factura WHERE id_factura = ?');
                $stmtCheck->execute([$id_factura]);
                $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
                if ($row && $row['estado_pago'] === 'PAGADA') {
                    echo json_encode(['success' => false, 'message' => 'Esta factura ya fue pagada']);
                } elseif (!$row) {
                    echo json_encode(['success' => false, 'message' => "Factura #{$id_factura} no encontrada en la BD"]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la factura']);
                }
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}

function handlePut($conn) {
    // Actualizar factura (cambiar estado o método de pago)
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de factura es obligatorio']);
        return;
    }
    
    $estado_pago = $data['estado_pago'] ?? '';
    $metodo_pago = $data['metodo_pago'] ?? '';
    
    // Construir query dinámica
    $updates = [];
    $params = [];
    
    if (!empty($estado_pago)) {
        $updates[] = 'estado_pago = ?';
        $params[] = $estado_pago;
    }
    
    if (!empty($metodo_pago)) {
        $updates[] = 'metodo_pago = ?';
        $params[] = $metodo_pago;
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'No hay campos para actualizar']);
        return;
    }
    
    $params[] = $id;
    $sql = 'UPDATE factura SET ' . implode(', ', $updates) . ' WHERE id_factura = ?';
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Factura actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se realizaron cambios']);
    }
}
