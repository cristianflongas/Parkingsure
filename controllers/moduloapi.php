<?php
// ============================================================
//  PARKINGSURE - Controlador API: Módulos de Parqueadero
//  Archivo: moduloapi.php - API REST para gestión de módulos
// ============================================================

require_once __DIR__ . '/../config/database.php';

// Habilitar CORS para peticiones AJAX
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$database = new Database();
$conn = $database->conectar();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($method) {
        case 'GET':
            if ($action === 'getAll') {
                // ── Paso 1: Limpiar entradas ACTIVO que ya tienen salida registrada ──
                // Esto ocurre cuando un INSERT en salida tuvo éxito pero el UPDATE
                // de entrada/módulo falló (rollback parcial o error previo)
                $stmtLimpiar = $conn->prepare(
                    'UPDATE entrada e
                     INNER JOIN salida s ON s.id_entrada = e.id_entrada
                     SET e.estado = "FINALIZADO"
                     WHERE e.estado = "ACTIVO"'
                );
                $stmtLimpiar->execute();

                // Si hubo entradas corregidas, liberar sus módulos también
                $stmtLimpiarModulos = $conn->prepare(
                    'UPDATE modulo m
                     SET m.estado = "DISPONIBLE"
                     WHERE m.estado = "OCUPADO"
                       AND NOT EXISTS (
                           SELECT 1 FROM entrada e
                           WHERE e.id_modulo = m.id_modulo
                             AND e.estado = "ACTIVO"
                       )'
                );
                $stmtLimpiarModulos->execute();

                // ── Paso 2: Obtener todos los módulos con su estado real ──
                $stmt = $conn->query(
                    'SELECT m.id_modulo, m.ubicacion, m.estado,
                            COUNT(e.id_entrada) AS ocupaciones,
                            MAX(e.placa)        AS placa
                     FROM modulo m
                     LEFT JOIN entrada e ON m.id_modulo = e.id_modulo
                                       AND e.estado = "ACTIVO"
                     GROUP BY m.id_modulo, m.ubicacion, m.estado
                     ORDER BY m.id_modulo ASC'
                );
                $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Corregir inconsistencias de estado en la BD y devolver estado real
                $modulosFormateados = [];
                foreach ($modulos as $m) {
                    $tieneEntradaActiva = (int)$m['ocupaciones'] > 0;
                    $estadoBD          = $m['estado'];

                    // Determinar estado real:
                    // - Si tiene entrada activa → OCUPADO (sin importar lo que diga la BD)
                    // - Si no tiene entrada activa y la BD dice OCUPADO → corregir a DISPONIBLE
                    // - MANTENIMIENTO se respeta siempre
                    if ($estadoBD === 'MANTENIMIENTO') {
                        $estadoReal = 'MANTENIMIENTO';
                    } elseif ($tieneEntradaActiva) {
                        $estadoReal = 'OCUPADO';
                    } else {
                        $estadoReal = 'DISPONIBLE';
                    }

                    // Si hay inconsistencia, corregir la BD silenciosamente
                    if ($estadoReal !== $estadoBD) {
                        $stmtFix = $conn->prepare('UPDATE modulo SET estado = ? WHERE id_modulo = ?');
                        $stmtFix->execute([$estadoReal, $m['id_modulo']]);
                    }

                    $modulosFormateados[] = [
                        'id'         => $m['id_modulo'],
                        'ubicacion'  => $m['ubicacion'],
                        'estado'     => $estadoReal,          // Estado real (ya corregido)
                        'ocupaciones'=> (int)$m['ocupaciones'],
                        'placa'      => $m['placa'] ?: null
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'data'    => $modulosFormateados
                ]);
            } elseif ($action === 'getById') {
                // Obtener módulo por ID
                $id = $_GET['id'] ?? '';
                if (empty($id)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'ID de módulo es obligatorio'
                    ]);
                    break;
                }
                
                $stmt = $conn->prepare(
                    'SELECT m.id_modulo, m.ubicacion, m.estado,
                            COUNT(e.id_entrada) as ocupaciones
                     FROM modulo m
                     LEFT JOIN entrada e ON m.id_modulo = e.id_modulo 
                                       AND e.estado = "ACTIVO"
                     WHERE m.id_modulo = ?
                     GROUP BY m.id_modulo, m.ubicacion, m.estado'
                );
                $stmt->execute([$id]);
                $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($modulo) {
                    echo json_encode([
                        'success' => true,
                        'data' => [
                            'id' => $modulo['id_modulo'],
                            'ubicacion' => $modulo['ubicacion'],
                            'estado' => $modulo['estado'],
                            'ocupado' => $modulo['ocupaciones'] > 0 ? 'ocupado' : 'libre',
                            'ocupaciones' => (int)$modulo['ocupaciones']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Módulo no encontrado'
                    ]);
                }
            } elseif ($action === 'getStats') {
                // Obtener estadísticas
                $stmtTotal = $conn->query('SELECT COUNT(*) FROM modulo');
                $total = $stmtTotal->fetchColumn();
                
                $stmtDisp = $conn->query('SELECT COUNT(*) FROM modulo WHERE estado = "DISPONIBLE"');
                $disponibles = $stmtDisp->fetchColumn();
                
                $stmtMant = $conn->query('SELECT COUNT(*) FROM modulo WHERE estado = "MANTENIMIENTO"');
                $mantenimiento = $stmtMant->fetchColumn();
                
                // Calcular ocupados reales (con entradas activas)
                $stmtOcup = $conn->query(
                    'SELECT COUNT(DISTINCT e.id_modulo) 
                     FROM entrada e 
                     WHERE e.estado = "ACTIVO"'
                );
                $ocupados = $stmtOcup->fetchColumn();
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total' => (int)$total,
                        'disponibles' => (int)$disponibles,
                        'mantenimiento' => (int)$mantenimiento,
                        'ocupados' => (int)$ocupados,
                        'libres' => (int)($disponibles - $ocupados)
                    ]
                ]);
            }
            break;

        case 'POST':
            // Determinar si es creación o cambio de estado
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (isset($data['action']) && $data['action'] === 'changeState') {
                // Cambiar estado de módulo existente
                $id = $data['id'] ?? '';
                $nuevoEstado = $data['estado'] ?? '';
                
                if (empty($id) || empty($nuevoEstado)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'ID y estado son obligatorios'
                    ]);
                    break;
                }
                
                // Validar estados permitidos
                $estadosPermitidos = ['DISPONIBLE', 'OCUPADO', 'MANTENIMIENTO'];
                if (!in_array($nuevoEstado, $estadosPermitidos)) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Estado no permitido'
                    ]);
                    break;
                }
                
                // Verificar si el módulo existe
                $stmtCheck = $conn->prepare('SELECT COUNT(*) FROM modulo WHERE id_modulo = ?');
                $stmtCheck->execute([$id]);
                if ($stmtCheck->fetchColumn() == 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Módulo no encontrado'
                    ]);
                    break;
                }
                
                // Verificar si tiene ocupaciones activas antes de cambiar a mantenimiento
                if ($nuevoEstado === 'MANTENIMIENTO') {
                    $stmtOcup = $conn->prepare(
                        'SELECT COUNT(*) FROM entrada WHERE id_modulo = ? AND estado = "ACTIVO"'
                    );
                    $stmtOcup->execute([$id]);
                    if ($stmtOcup->fetchColumn() > 0) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'No se puede poner en mantenimiento: tiene vehículos estacionados'
                        ]);
                        break;
                    }
                }
                
                // Actualizar estado
                $stmt = $conn->prepare('UPDATE modulo SET estado = ? WHERE id_modulo = ?');
                $resultado = $stmt->execute([$nuevoEstado, $id]);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Estado actualizado correctamente',
                        'data' => ['id' => $id, 'estado' => $nuevoEstado]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al actualizar el estado'
                    ]);
                }
                
            } else {
                // Crear nuevo módulo (código existente)
                if (empty($data['ubicacion'])) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'La ubicación es obligatoria'
                    ]);
                    break;
                }

                // Verificar si la ubicación ya existe
                $stmtCheck = $conn->prepare('SELECT COUNT(*) FROM modulo WHERE ubicacion = ?');
                $stmtCheck->execute([$data['ubicacion']]);
                if ($stmtCheck->fetchColumn() > 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'La ubicación ya está registrada'
                    ]);
                    break;
                }

                // Insertar módulo
                $stmt = $conn->prepare(
                    'INSERT INTO modulo (ubicacion, estado) VALUES (:ubicacion, :estado)'
                );
                $resultado = $stmt->execute([
                    ':ubicacion' => trim($data['ubicacion']),
                    ':estado' => $data['estado'] ?? 'DISPONIBLE'
                ]);

                if ($resultado) {
                    $id = $conn->lastInsertId();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Módulo creado correctamente',
                        'data' => ['id' => $id]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al crear el módulo'
                    ]);
                }
            }
            break;

        case 'PUT':
            // Actualizar módulo
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de módulo es obligatorio'
                ]);
                break;
            }

            // Verificar si el módulo existe
            $stmtCheck = $conn->prepare('SELECT COUNT(*) FROM modulo WHERE id_modulo = ?');
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Módulo no encontrado'
                ]);
                break;
            }

            // Actualizar módulo
            $stmt = $conn->prepare(
                'UPDATE modulo SET ubicacion = :ubicacion, estado = :estado 
                 WHERE id_modulo = :id'
            );
            $resultado = $stmt->execute([
                ':ubicacion' => trim($data['ubicacion']),
                ':estado' => $data['estado'] ?? 'DISPONIBLE',
                ':id' => (int) $id
            ]);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Módulo actualizado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el módulo'
                ]);
            }
            break;

        case 'DELETE':
            // Eliminar módulo
            $id = $_GET['id'] ?? '';
            
            if (empty($id)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID de módulo es obligatorio'
                ]);
                break;
            }

            // Verificar si el módulo existe
            $stmtCheck = $conn->prepare('SELECT COUNT(*) FROM modulo WHERE id_modulo = ?');
            $stmtCheck->execute([$id]);
            if ($stmtCheck->fetchColumn() == 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Módulo no encontrado'
                ]);
                break;
            }

            // Verificar si tiene entradas activas
            $stmtEntradas = $conn->prepare(
                'SELECT COUNT(*) FROM entrada WHERE id_modulo = ? AND estado = "ACTIVO"'
            );
            $stmtEntradas->execute([$id]);
            if ($stmtEntradas->fetchColumn() > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede eliminar: el módulo tiene entradas activas'
                ]);
                break;
            }

            // Eliminar módulo
            $stmt = $conn->prepare('DELETE FROM modulo WHERE id_modulo = ?');
            $resultado = $stmt->execute([$id]);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Módulo eliminado correctamente'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el módulo'
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            break;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>
