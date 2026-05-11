<?php
// ============================================================
//  PARKINGSURE - API de Reportes
//  Datos reales desde BD para el módulo de reportes
// ============================================================
error_reporting(0); // Evitar que warnings contaminen el JSON
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    $db   = new Database();
    $conn = $db->conectar();

    $action = $_GET['action'] ?? '';
    $fecha = $_GET['fecha'] ?? date('Y-m-d'); // Por defecto hoy
    $fecha_inicio = $_GET['fecha_inicio'] ?? $fecha;
    $fecha_fin = $_GET['fecha_fin'] ?? $fecha;

    switch ($action) {

        // ── Estadísticas del día ──────────────────────────────
        case 'getResumen':
            error_log("DEBUG: getResumen - fecha_inicio: $fecha_inicio, fecha_fin: $fecha_fin");
            
            // Total ingresos (facturas PAGADAS del rango)
            $stmtIngresos = $conn->prepare(
                'SELECT COALESCE(SUM(f.monto_total), 0) AS ingresos
                 FROM factura f
                 WHERE f.estado_pago = "PAGADA"
                   AND DATE(f.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin'
            );
            $stmtIngresos->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $ingresos = (float) $stmtIngresos->fetchColumn();
            
            error_log("DEBUG: getResumen - ingresos encontrados: $ingresos");
            
            // Debug: Verificar facturas existentes en el rango
            $stmtDebug = $conn->prepare(
                'SELECT COUNT(*) as total_facturas, DATE(f.fecha_emision) as fecha
                 FROM factura f
                 WHERE f.estado_pago = "PAGADA"
                   AND DATE(f.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin
                 GROUP BY DATE(f.fecha_emision)'
            );
            $stmtDebug->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $debugRows = $stmtDebug->fetchAll(PDO::FETCH_ASSOC);
            error_log("DEBUG: getResumen - facturas por fecha: " . json_encode($debugRows));

            // Vehículos atendidos (entradas del rango)
            $stmtVeh = $conn->prepare(
                'SELECT COUNT(*) FROM entrada
                 WHERE DATE(fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin'
            );
            $stmtVeh->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $vehiculos = (int) $stmtVeh->fetchColumn();
            
            error_log("DEBUG: getResumen - vehículos encontrados: $vehiculos");
            
            // Debug: Verificar entradas existentes en el rango
            $stmtDebug2 = $conn->prepare(
                'SELECT COUNT(*) as total_entradas, DATE(fecha_hora_entrada) as fecha
                 FROM entrada
                 WHERE DATE(fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin
                 GROUP BY DATE(fecha_hora_entrada)'
            );
            $stmtDebug2->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $debugRows2 = $stmtDebug2->fetchAll(PDO::FETCH_ASSOC);
            error_log("DEBUG: getResumen - entradas por fecha: " . json_encode($debugRows2));

            // Tiempo promedio de estancia (entradas con salida del rango)
            $stmtTiempo = $conn->prepare(
                'SELECT AVG(TIMESTAMPDIFF(MINUTE, e.fecha_hora_entrada, s.fecha_hora_salida)) AS minutos
                 FROM entrada e
                 INNER JOIN salida s ON s.id_entrada = e.id_entrada
                 WHERE DATE(e.fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin'
            );
            $stmtTiempo->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $minutos      = (float) $stmtTiempo->fetchColumn();
            $horas_prom   = $minutos > 0 ? (int) floor($minutos / 60) : 0;
            $minutos_prom = $minutos > 0 ? (int) fmod($minutos, 60)   : 0;
            $tiempo_prom  = $minutos > 0 ? "{$horas_prom}h {$minutos_prom}m" : '—';
            
            error_log("DEBUG: getResumen - tiempo promedio: $tiempo_prom");

            // Mejor servicio (tipo con más entradas del rango)
            $stmtMejor = $conn->prepare(
                'SELECT ts.nombre_tipo_servicio, COUNT(*) AS cnt
                 FROM entrada e
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 WHERE DATE(e.fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin
                 GROUP BY ts.nombre_tipo_servicio
                 ORDER BY cnt DESC
                 LIMIT 1'
            );
            $stmtMejor->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $mejor = $stmtMejor->fetch(PDO::FETCH_ASSOC);
            
            error_log("DEBUG: getResumen - mejor servicio: " . json_encode($mejor));

            echo json_encode([
                'success' => true,
                'data'    => [
                    'ingresos'      => $ingresos,
                    'vehiculos'     => $vehiculos,
                    'tiempo_prom'   => $tiempo_prom,
                    'mejor_servicio'=> $mejor ? $mejor['nombre_tipo_servicio'] : '—',
                    'fecha'         => $fecha
                ]
            ]);
            break;

        // ── Ingresos por tipo de servicio ─────────────────────
        case 'getPorTipo':
            error_log("DEBUG: getPorTipo - fecha_inicio: $fecha_inicio, fecha_fin: $fecha_fin");
            
            // Modificado para mostrar todas las entradas, no solo las facturadas
            $stmt = $conn->prepare(
                'SELECT ts.nombre_tipo_servicio AS tipo,
                        COUNT(e.id_entrada)     AS cantidad,
                        COALESCE(SUM(f.monto_total), 0) AS ingresos
                 FROM entrada e
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 LEFT  JOIN salida  s ON s.id_entrada = e.id_entrada
                 LEFT  JOIN factura f ON f.id_salida  = s.id_salida
                                     AND f.estado_pago = "PAGADA"
                 WHERE DATE(e.fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin
                 GROUP BY ts.id_tipo_servicio, ts.nombre_tipo_servicio
                 ORDER BY cantidad DESC, ingresos DESC'
            );
            $stmt->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("DEBUG: getPorTipo - rows encontrados: " . json_encode($rows));

            // Calcular porcentaje respecto al máximo (ahora basado en cantidad)
            $maxCantidad = max(array_column($rows, 'cantidad') ?: [0]);
            foreach ($rows as &$r) {
                $r['pct']      = $maxCantidad > 0 ? round(($r['cantidad'] / $maxCantidad) * 100) : 0;
                $r['ingresos'] = (float) $r['ingresos'];
                $r['cantidad'] = (int)   $r['cantidad'];
            }

            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        // ── Vehículos por franja horaria ──────────────────────
        case 'getPorHora':
            $stmt = $conn->prepare(
                'SELECT HOUR(fecha_hora_entrada) AS hora, COUNT(*) AS cantidad
                 FROM entrada
                 WHERE DATE(fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin
                 GROUP BY HOUR(fecha_hora_entrada)
                 ORDER BY hora'
            );
            $stmt->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $porHora = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construir franjas de 2h (06-08, 08-10 … 20-22)
            $franjas = [];
            for ($h = 6; $h <= 22; $h += 2) {
                $cant = 0;
                foreach ($porHora as $r) {
                    if ((int)$r['hora'] >= $h && (int)$r['hora'] < $h + 2) {
                        $cant += (int)$r['cantidad'];
                    }
                }
                $franjas[] = [
                    'label'    => sprintf('%02d–%02d', $h, $h + 2),
                    'cantidad' => $cant
                ];
            }

            echo json_encode(['success' => true, 'data' => $franjas]);
            break;

        // ── Detalle de transacciones del rango ──────────────────
        case 'getDetalle':
            error_log("DEBUG: getDetalle - fecha_inicio: $fecha_inicio, fecha_fin: $fecha_fin");
            
            // Modificado para mostrar todas las entradas, no solo las facturadas
            $stmt = $conn->prepare(
                'SELECT
                    e.fecha_hora_entrada,
                    e.placa,
                    ts.nombre_tipo_servicio AS tipo,
                    m.ubicacion             AS modulo,
                    s.fecha_hora_salida,
                    f.monto_total,
                    f.metodo_pago,
                    f.estado_pago,
                    TIMESTAMPDIFF(MINUTE, e.fecha_hora_entrada,
                        COALESCE(s.fecha_hora_salida, NOW())) AS minutos_estancia
                 FROM entrada e
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 INNER JOIN modulo        m  ON e.id_modulo        = m.id_modulo
                 LEFT  JOIN salida        s  ON s.id_entrada       = e.id_entrada
                 LEFT  JOIN factura       f  ON f.id_salida        = s.id_salida
                 WHERE DATE(e.fecha_hora_entrada) BETWEEN :fecha_inicio AND :fecha_fin
                 ORDER BY e.fecha_hora_entrada DESC'
            );
            $stmt->execute([':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("DEBUG: getDetalle - rows encontrados: " . count($rows));

            foreach ($rows as &$r) {
                $min = (int)$r['minutos_estancia'];
                $r['duracion']    = $min > 0 ? floor($min/60).'h '.($min%60).'m' : '—';
                $r['monto_total'] = $r['monto_total'] !== null ? (float)$r['monto_total'] : null;
            }

            echo json_encode(['success' => true, 'data' => $rows]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
