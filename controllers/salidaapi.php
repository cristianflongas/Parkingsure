<?php
// ============================================================
//  PARKINGSURE - API de Salidas
//  Archivo: salidaapi.php - Controlador para gestión de salidas
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
        case 'getAll':
            // Obtener todas las salidas
            $stmt = $conn->prepare(
                'SELECT s.id_salida, s.id_entrada, s.fecha_hora_salida,
                        m.ubicacion, v.marca, v.modelo, e.fecha_hora_entrada, e.placa
                 FROM salida s
                 LEFT JOIN entrada e ON s.id_entrada = e.id_entrada
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa = v.placa
                 ORDER BY s.fecha_hora_salida DESC'
            );
            $stmt->execute();
            $salidas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $salidas
            ]);
            break;
            
        case 'getActive':
            // Obtener vehículos estacionados (entradas sin salida)
            $stmt = $conn->prepare(
                'SELECT e.id_entrada, e.id_modulo, e.placa_vehiculo, e.fecha_hora_entrada, e.estado,
                        m.ubicacion, v.marca, v.modelo
                 FROM entrada e
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa_vehiculo = v.placa
                 WHERE e.estado = "ACTIVO"
                 ORDER BY e.fecha_hora_entrada DESC'
            );
            $stmt->execute();
            $activos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $activos
            ]);
            break;
            
        case 'getById':
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID de salida es obligatorio']);
                break;
            }
            
            $stmt = $conn->prepare(
                'SELECT s.*, m.ubicacion, v.marca, v.modelo, e.fecha_hora_entrada, e.placa
                 FROM salida s
                 LEFT JOIN entrada e ON s.id_entrada = e.id_entrada
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa = v.placa
                 WHERE s.id_salida = ?'
            );
            $stmt->execute([$id]);
            $salida = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($salida) {
                echo json_encode(['success' => true, 'data' => $salida]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Salida no encontrada']);
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
        case 'release':
            // Liberar módulo (registrar salida)
            $id_entrada = $data['id_entrada'] ?? '';

            if (empty($id_entrada)) {
                echo json_encode(['success' => false, 'message' => 'ID de entrada es obligatorio']);
                break;
            }

            // Buscar la entrada (activa O ya finalizada con salida huérfana)
            $stmtCheck = $conn->prepare(
                'SELECT e.id_entrada, e.id_modulo, e.placa, e.fecha_hora_entrada,
                        e.estado, e.id_tipo_servicio,
                        ts.tarifa, ts.nombre_tipo_servicio
                 FROM entrada e
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 WHERE e.id_entrada = ?'
            );
            $stmtCheck->execute([$id_entrada]);
            $entrada = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$entrada) {
                echo json_encode(['success' => false, 'message' => 'Entrada no encontrada']);
                break;
            }

            // Verificar si ya existe una salida para esta entrada
            $stmtSalidaExiste = $conn->prepare(
                'SELECT id_salida, fecha_hora_salida FROM salida WHERE id_entrada = ?'
            );
            $stmtSalidaExiste->execute([$id_entrada]);
            $salidaExistente = $stmtSalidaExiste->fetch(PDO::FETCH_ASSOC);

            // Si la entrada ya está FINALIZADO y tiene salida → inconsistencia resuelta
            if ($entrada['estado'] === 'FINALIZADO' && $salidaExistente) {
                // Solo limpiar el módulo si quedó en OCUPADO por error
                $stmtModulo = $conn->prepare('UPDATE modulo SET estado = "DISPONIBLE" WHERE id_modulo = ? AND estado = "OCUPADO"');
                $stmtModulo->execute([$entrada['id_modulo']]);
                echo json_encode(['success' => false, 'message' => 'Esta entrada ya fue finalizada anteriormente']);
                break;
            }

            // Calcular tiempo de estancia
            $fecha_entrada  = new DateTime($entrada['fecha_hora_entrada']);
            $fecha_salida_dt = new DateTime();

            // Si ya hay salida registrada, usar su fecha (no crear otra)
            if ($salidaExistente) {
                $fecha_salida_dt = new DateTime($salidaExistente['fecha_hora_salida']);
            }

            $diferencia   = $fecha_entrada->diff($fecha_salida_dt);
            $horas_totales = ($diferencia->days * 24) + $diferencia->h + ($diferencia->i / 60);
            $horas_cobrar  = max(1, (int) ceil($horas_totales));
            $tarifa        = (float) $entrada['tarifa'];
            $monto_total   = $horas_cobrar * $tarifa;

            $conn->beginTransaction();
            try {
                if ($salidaExistente) {
                    // Ya existe salida — solo completar la limpieza
                    $id_salida = $salidaExistente['id_salida'];

                    // Crear factura si no existe
                    $stmtFactExiste = $conn->prepare('SELECT id_factura FROM factura WHERE id_salida = ?');
                    $stmtFactExiste->execute([$id_salida]);
                    if (!$stmtFactExiste->fetch()) {
                        $stmtFactura = $conn->prepare(
                            'INSERT INTO factura (id_salida, monto_total, metodo_pago, estado_pago)
                             VALUES (?, ?, NULL, "PENDIENTE")'
                        );
                        $stmtFactura->execute([$id_salida, $monto_total]);
                    }
                } else {
                    // Insertar nueva salida
                    $stmtSalida = $conn->prepare(
                        'INSERT INTO salida (id_entrada, fecha_hora_salida) VALUES (?, NOW())'
                    );
                    $stmtSalida->execute([$entrada['id_entrada']]);
                    $id_salida = $conn->lastInsertId();

                    // Crear factura (metodo_pago NULL hasta que el usuario lo defina en Pagos)
                    $stmtFactura = $conn->prepare(
                        'INSERT INTO factura (id_salida, monto_total, metodo_pago, estado_pago)
                         VALUES (?, ?, NULL, "PENDIENTE")'
                    );
                    $stmtFactura->execute([$id_salida, $monto_total]);
                }

                // Marcar entrada como FINALIZADO
                $stmtEntrada = $conn->prepare(
                    'UPDATE entrada SET estado = "FINALIZADO" WHERE id_entrada = ?'
                );
                $stmtEntrada->execute([$id_entrada]);

                // Liberar módulo
                $stmtModulo = $conn->prepare(
                    'UPDATE modulo SET estado = "DISPONIBLE" WHERE id_modulo = ?'
                );
                $stmtModulo->execute([$entrada['id_modulo']]);

                $conn->commit();

                // Obtener el id_factura real recién creado
                $stmtGetFact = $conn->prepare('SELECT id_factura FROM factura WHERE id_salida = ?');
                $stmtGetFact->execute([$id_salida]);
                $id_factura_nuevo = (int) $stmtGetFact->fetchColumn();

                $tiempo_estancia = sprintf(
                    '%d días, %d horas, %d minutos',
                    $diferencia->days,
                    $diferencia->h,
                    $diferencia->i
                );

                echo json_encode([
                    'success' => true,
                    'message' => 'Módulo liberado correctamente',
                    'factura' => [
                        'id_factura'     => $id_factura_nuevo,
                        'id_salida'      => $id_salida,
                        'monto_total'    => $monto_total,
                        'horas_cobradas' => $horas_cobrar,
                        'tarifa_hora'    => $tarifa,
                        'tiempo_estancia'=> $tiempo_estancia,
                        'nombre_tipo_servicio' => $entrada['nombre_tipo_servicio'],
                        'tipo_servicio'  => $entrada['nombre_tipo_servicio'],
                        'placa'          => $entrada['placa'],
                        'ubicacion'      => null,
                        'estado_pago'    => 'PENDIENTE',
                        'fecha_emision'  => date('Y-m-d H:i:s')
                    ]
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                throw $e;
            }
            break;
            
        case 'create':
            // Crear salida manual
            $id_modulo = $data['id_modulo'] ?? '';
            $placa = $data['placa'] ?? '';
            
            if (empty($id_modulo) || empty($placa)) {
                echo json_encode(['success' => false, 'message' => 'ID de módulo y placa son obligatorios']);
                break;
            }
            
            // Buscar entrada activa
            $stmtEntrada = $conn->prepare(
                'SELECT id_entrada, id_modulo FROM entrada WHERE placa = ? AND estado = "ACTIVO"'
            );
            $stmtEntrada->execute([$placa]);
            $entrada = $stmtEntrada->fetch(PDO::FETCH_ASSOC);
            
            if (!$entrada) {
                echo json_encode(['success' => false, 'message' => 'No se encontró una entrada activa para este módulo y vehículo']);
                break;
            }
            
            // Usar la misma lógica que release
            $data['id_entrada'] = $entrada['id_entrada'];
            $data['action'] = 'release';
            
            // Re-ejecutar con release
            handlePost($conn);
            return;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
}

function handlePut($conn) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de salida es obligatorio']);
        return;
    }
    
    // Actualizar salida (generalmente no se usa)
    $data = json_decode(file_get_contents('php://input'), true);
    $stmt = $conn->prepare('UPDATE salida SET estado = ? WHERE id_salida = ?');
    $resultado = $stmt->execute([$data['estado'] ?? 'COMPLETADO', $id]);
    
    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Salida actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar salida']);
    }
}

function handleDelete($conn) {
    $id = $_GET['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['success' => false, 'message' => 'ID de salida es obligatorio']);
        return;
    }
    
    // Eliminar salida (solo si está completada)
    $stmt = $conn->prepare('DELETE FROM salida WHERE id_salida = ? AND estado = "COMPLETADO"');
    $resultado = $stmt->execute([$id]);
    
    if ($resultado) {
        echo json_encode(['success' => true, 'message' => 'Salida eliminada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar salida o salida no está completada']);
    }
}
?>
