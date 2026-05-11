<?php
// ============================================================
//  PARKINGSURE - Dashboard API
//  Solo lectura — nunca modifica ni elimina tablas
// ============================================================
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

try {
    $db   = new Database();
    $conn = $db->conectar();
    $action = $_GET['action'] ?? '';

    switch ($action) {

        // ── Estadísticas principales del dashboard ────────────
        case 'getStats':
            // Módulos
            $stmtMod = $conn->query(
                'SELECT COUNT(*) AS total,
                        SUM(estado = "DISPONIBLE") AS disponibles,
                        SUM(estado = "OCUPADO")    AS ocupados
                 FROM modulo'
            );
            $modulos = $stmtMod->fetch(PDO::FETCH_ASSOC);

            // Ingresos hoy (TODOS los ingresos del día)
            $stmtIng = $conn->prepare(
                'SELECT COALESCE(SUM(monto_total), 0) AS ingresos_hoy
                 FROM factura
                 WHERE DATE(fecha_emision) = CURDATE()'
            );
            $stmtIng->execute();
            $ingresos = $stmtIng->fetchColumn();

            echo json_encode([
                'success' => true,
                'data'    => [
                    'total_modulos' => (int)$modulos['total'],
                    'disponibles'   => (int)$modulos['disponibles'],
                    'ocupados'      => (int)$modulos['ocupados'],
                    'ingresos_hoy'  => (float)$ingresos
                ]
            ]);
            break;

        // ── Obtener todos los servicios ───────────────────────
        case 'getServicios':
            $stmt = $conn->prepare(
                'SELECT * FROM tipo_servicio 
                 ORDER BY nombre_tipo_servicio'
            );
            $stmt->execute();
            $servicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data'    => $servicios
            ]);
            break;

        // ── Últimas entradas y salidas (paginado) ─────────────
        case 'getRecentEvents':
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $limit  = 10;
            $offset = ($page - 1) * $limit;

            // Total
            $stmtCount = $conn->query(
                'SELECT (SELECT COUNT(*) FROM entrada) +
                        (SELECT COUNT(*) FROM salida)  AS total'
            );
            $total      = (int)$stmtCount->fetchColumn();
            $totalPages = (int)ceil($total / $limit);

            $stmt = $conn->prepare(
                'SELECT e.placa, m.ubicacion,
                        "Entrada" AS evento,
                        e.fecha_hora_entrada AS hora,
                        e.estado, "entrada" AS tipo
                 FROM entrada e
                 INNER JOIN modulo m ON e.id_modulo = m.id_modulo
                 UNION ALL
                 SELECT e.placa, m.ubicacion,
                        "Salida" AS evento,
                        s.fecha_hora_salida AS hora,
                        "FINALIZADO" AS estado, "salida" AS tipo
                 FROM salida s
                 INNER JOIN entrada e ON s.id_entrada = e.id_entrada
                 INNER JOIN modulo  m ON e.id_modulo  = m.id_modulo
                 ORDER BY hora DESC
                 LIMIT :lim OFFSET :off'
            );
            $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
            $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success'    => true,
                'data'       => $events,
                'pagination' => [
                    'currentPage' => $page,
                    'totalPages'  => $totalPages,
                    'totalItems'  => $total,
                    'itemsPerPage'=> $limit
                ]
            ]);
            break;

        // ── Ocupación por tipo de servicio ────────────────────
        case 'getOccupancyByType':
            $stmt = $conn->query(
                'SELECT ts.nombre_tipo_servicio AS tipo,
                        COUNT(e.id_entrada)     AS occupied,
                        (SELECT COUNT(*) FROM modulo) AS total
                 FROM entrada e
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 WHERE e.estado = "ACTIVO"
                 GROUP BY ts.id_tipo_servicio, ts.nombre_tipo_servicio
                 ORDER BY occupied DESC'
            );
            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
