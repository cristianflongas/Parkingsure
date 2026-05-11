<?php
/**
 * Modelo de Datos - ParkingSure
 * Arquitectura MVC - Modelo centralizado
 */
class ParkingModel {
    private $conn;
    
    public function __construct($database) {
        $this->conn = $database->conectar();
    }
    
    /**
     * Obtener todos los módulos
     */
    public function getModulos() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT m.*, COUNT(e.id_entrada) as ocupados 
                 FROM modulo m 
                 LEFT JOIN entrada e ON m.id_modulo = e.id_modulo AND e.estado = 'ACTIVO'
                 GROUP BY m.id_modulo
                 ORDER BY m.id_modulo"
            );
            $stmt->execute();
            $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar estado de cada módulo
            foreach ($modulos as &$modulo) {
                $modulo['estado'] = $modulo['ocupados'] > 0 ? 'OCUPADO' : 'DISPONIBLE';
                $modulo['ocupados'] = (int)$modulo['ocupados'];
            }
            
            return [
                'success' => true,
                'data' => $modulos,
                'total' => count($modulos)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo módulos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener entradas activas
     */
    public function getEntradasActivas() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT e.id_entrada, e.id_modulo, e.placa, e.fecha_hora_entrada, e.estado,
                        m.ubicacion, v.marca, v.modelo, ts.nombre_tipo_servicio
                 FROM entrada e
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 LEFT JOIN vehiculo v ON e.placa = v.placa
                 LEFT JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 WHERE e.estado = 'ACTIVO'
                 ORDER BY e.fecha_hora_entrada DESC"
            );
            $stmt->execute();
            $entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $entradas,
                'total' => count($entradas)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo entradas activas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar nueva entrada
     */
    public function registrarEntrada($data) {
        try {
            // Validar que el módulo esté disponible
            $stmtModulo = $this->conn->prepare(
                "SELECT COUNT(*) as ocupados FROM entrada 
                 WHERE id_modulo = ? AND estado = 'ACTIVO'"
            );
            $stmtModulo->execute([$data['id_modulo']]);
            $moduloOcupado = $stmtModulo->fetch(PDO::FETCH_ASSOC)['ocupados'] > 0;
            
            if ($moduloOcupado) {
                return [
                    'success' => false,
                    'message' => 'El módulo ya está ocupado'
                ];
            }
            
            // Verificar si el vehículo existe
            $stmtVehiculo = $this->conn->prepare(
                "SELECT placa FROM vehiculo WHERE placa = ?"
            );
            $stmtVehiculo->execute([$data['placa']]);
            $vehiculoExiste = $stmtVehiculo->fetch(PDO::FETCH_ASSOC);
            
            if (!$vehiculoExiste) {
                // Crear vehículo si no existe
                $stmtCrearVehiculo = $this->conn->prepare(
                    "INSERT INTO vehiculo (placa, marca, modelo) VALUES (?, ?, ?)"
                );
                $stmtCrearVehiculo->execute([
                    $data['placa'],
                    $data['marca'] ?? 'Desconocida',
                    $data['modelo'] ?? 'Desconocido'
                ]);
            }
            
            // Registrar entrada
            $stmt = $this->conn->prepare(
                "INSERT INTO entrada (id_modulo, placa, id_tipo_servicio, id_personal, fecha_hora_entrada, estado) 
                 VALUES (?, ?, ?, ?, ?, 'ACTIVO')"
            );
            
            $resultado = $stmt->execute([
                $data['id_modulo'],
                $data['placa'],
                $data['id_tipo_servicio'],
                $data['id_personal'],
                $data['fecha_hora_entrada'] ?? date('Y-m-d H:i:s')
            ]);
            
            if ($resultado) {
                $id_entrada = $this->conn->lastInsertId();
                return [
                    'success' => true,
                    'message' => 'Entrada registrada exitosamente',
                    'data' => [
                        'id_entrada' => $id_entrada,
                        'placa' => $data['placa'],
                        'modulo' => $data['id_modulo']
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al registrar la entrada'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en el modelo: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener facturas pagadas del día
     */
    public function getFacturasPagadasHoy($fecha = null) {
        try {
            $fecha = $fecha ?? date('Y-m-d');
            
            $stmt = $this->conn->prepare(
                "SELECT f.id_factura, f.id_salida, f.monto_total, f.metodo_pago, f.estado_pago,
                        f.fecha_emision, s.fecha_hora_salida, e.placa, ts.nombre_tipo_servicio
                 FROM factura f
                 INNER JOIN salida s ON f.id_salida = s.id_salida
                 INNER JOIN entrada e ON s.id_entrada = e.id_entrada
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 WHERE f.estado_pago = 'PAGADA' 
                 AND DATE(f.fecha_emision) = ?
                 ORDER BY f.fecha_emision DESC"
            );
            $stmt->execute([$fecha]);
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $facturas,
                'total' => count($facturas),
                'fecha_filtro' => $fecha
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo facturas pagadas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener facturas pendientes
     */
    public function getFacturasPendientes() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT f.id_factura, f.id_salida, f.monto_total, f.metodo_pago, f.estado_pago,
                        f.fecha_emision, s.fecha_hora_salida, e.placa, ts.nombre_tipo_servicio,
                        m.ubicacion, TIMESTAMPDIFF(MINUTE, e.fecha_hora_entrada, s.fecha_hora_salida) as minutos_estancia
                 FROM factura f
                 INNER JOIN salida s ON f.id_salida = s.id_salida
                 INNER JOIN entrada e ON s.id_entrada = e.id_entrada
                 INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio
                 LEFT JOIN modulo m ON e.id_modulo = m.id_modulo
                 WHERE f.estado_pago = 'PENDIENTE'
                 ORDER BY f.fecha_emision DESC"
            );
            $stmt->execute();
            $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $facturas,
                'total' => count($facturas)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo facturas pendientes: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener tipos de servicio
     */
    public function getTiposServicio() {
        try {
            $stmt = $this->conn->prepare(
                "SELECT id_tipo_servicio, nombre_tipo_servicio, tarifa 
                 FROM tipo_servicio 
                 ORDER BY nombre_tipo_servicio"
            );
            $stmt->execute();
            $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => $tipos,
                'total' => count($tipos)
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo tipos de servicio: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas del dashboard
     */
    public function getEstadisticasDashboard() {
        try {
            // Obtener estadísticas generales optimizadas con COUNT, SUM y AVG
            $stmt = $this->conn->prepare(
                "SELECT 
                    COUNT(CASE WHEN e.estado = 'ACTIVO' THEN 1 END) as ocupados,
                    COUNT(CASE WHEN f.estado_pago = 'PAGADA' AND DATE(f.fecha_emision) = CURDATE() THEN 1 END) as pagos_hoy,
                    COALESCE(SUM(CASE WHEN f.estado_pago = 'PAGADA' AND DATE(f.fecha_emision) = CURDATE() THEN f.monto_total END), 0) as ingresos_hoy,
                    COALESCE(AVG(CASE WHEN f.estado_pago = 'PAGADA' AND DATE(f.fecha_emision) = CURDATE() THEN f.monto_total END), 0) as promedio_hoy
                 FROM entrada e
                 LEFT JOIN salida s ON e.id_entrada = s.id_entrada
                 LEFT JOIN factura f ON s.id_salida = f.id_salida
                 WHERE DATE(f.fecha_emision) = CURDATE() OR f.fecha_emision IS NULL"
            );
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'data' => [
                    'ocupados' => (int)$stats['ocupados'],
                    'pagos_hoy' => (int)$stats['pagos_hoy'],
                    'ingresos_hoy' => (float)$stats['ingresos_hoy'],
                    'promedio_hoy' => (float)$stats['promedio_hoy']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo estadísticas: ' . $e->getMessage()
            ];
        }
    }
}
?>
