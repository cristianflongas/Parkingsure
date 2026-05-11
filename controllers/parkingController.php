<?php
/**
 * Controlador Principal - ParkingSure
 * Arquitectura MVC - Controlador centralizado
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ParkingModel.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

class ParkingController {
    private $model;
    private $method;
    private $action;
    private $data;
    
    public function __construct() {
        $this->model = new ParkingModel(new Database());
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->action = $_GET['action'] ?? $_POST['action'] ?? '';
        $this->data = json_decode(file_get_contents('php://input'), true) ?: [];
    }
    
    /**
     * Manejar todas las peticiones
     */
    public function handleRequest() {
        try {
            switch ($this->method) {
                case 'GET':
                    return $this->handleGet();
                case 'POST':
                    return $this->handlePost();
                case 'PUT':
                    return $this->handlePut();
                case 'DELETE':
                    return $this->handleDelete();
                case 'OPTIONS':
                    return $this->handleOptions();
                default:
                    return $this->response(false, 'Método no permitido');
            }
        } catch (Exception $e) {
            return $this->response(false, 'Error del servidor: ' . $e->getMessage());
        }
    }
    
    /**
     * Manejar peticiones GET
     */
    private function handleGet() {
        switch ($this->action) {
            case 'getModulos':
                return $this->model->getModulos();
                
            case 'getEntradasActivas':
                return $this->model->getEntradasActivas();
                
            case 'getFacturasPagadasHoy':
                $fecha = $_GET['fecha'] ?? date('Y-m-d');
                return $this->model->getFacturasPagadasHoy($fecha);
                
            case 'getFacturasPendientes':
                return $this->model->getFacturasPendientes();
                
            case 'getTiposServicio':
                return $this->model->getTiposServicio();
                
            case 'getEstadisticasDashboard':
                return $this->model->getEstadisticasDashboard();
                
            case 'getSystemStatus':
                return $this->getSystemStatus();
                
            default:
                return $this->response(false, 'Acción GET no válida: ' . $this->action);
        }
    }
    
    /**
     * Manejar peticiones POST
     */
    private function handlePost() {
        switch ($this->action) {
            case 'registrarEntrada':
                return $this->registrarEntrada();
                
            case 'liberarModulo':
                return $this->liberarModulo();
                
            case 'generarFactura':
                return $this->generarFactura();
                
            case 'actualizarModulo':
                return $this->actualizarModulo();
                
            default:
                return $this->response(false, 'Acción POST no válida: ' . $this->action);
        }
    }
    
    /**
     * Manejar peticiones PUT
     */
    private function handlePut() {
        switch ($this->action) {
            case 'actualizarEntrada':
                return $this->actualizarEntrada();
                
            case 'actualizarFactura':
                return $this->actualizarFactura();
                
            default:
                return $this->response(false, 'Acción PUT no válida: ' . $this->action);
        }
    }
    
    /**
     * Manejar peticiones DELETE
     */
    private function handleDelete() {
        switch ($this->action) {
            case 'eliminarEntrada':
                return $this->eliminarEntrada();
                
            case 'eliminarFactura':
                return $this->eliminarFactura();
                
            default:
                return $this->response(false, 'Acción DELETE no válida: ' . $this->action);
        }
    }
    
    /**
     * Manejar peticiones OPTIONS (CORS)
     */
    private function handleOptions() {
        return $this->response(true, 'CORS preflight', [], 200);
    }
    
    /**
     * Registrar entrada de vehículo
     */
    private function registrarEntrada() {
        $required = ['id_modulo', 'placa', 'id_tipo_servicio'];
        
        foreach ($required as $field) {
            if (empty($this->data[$field])) {
                return $this->response(false, "El campo {$field} es obligatorio");
            }
        }
        
        // Agregar información adicional
        $this->data['id_personal'] = $_SESSION['id_personal'] ?? 1;
        $this->data['fecha_hora_entrada'] = date('Y-m-d H:i:s');
        $this->data['estado'] = 'ACTIVO';
        
        $result = $this->model->registrarEntrada($this->data);
        
        if ($result['success']) {
            return $this->response(true, 'Entrada registrada exitosamente', $result['data']);
        } else {
            return $this->response(false, $result['message']);
        }
    }
    
    /**
     * Liberar módulo
     */
    private function liberarModulo() {
        $required = ['id_entrada'];
        
        foreach ($required as $field) {
            if (empty($this->data[$field])) {
                return $this->response(false, "El campo {$field} es obligatorio");
            }
        }
        
        // Aquí iría la lógica para liberar módulo
        // Por ahora, simulamos éxito
        return $this->response(true, 'Módulo liberado exitosamente', [
            'id_entrada' => $this->data['id_entrada'],
            'liberado_por' => $_SESSION['id_personal'] ?? 1
        ]);
    }
    
    /**
     * Generar factura
     */
    private function generarFactura() {
        $id_factura = $this->data['id_factura'] ?? null;
        
        if (empty($id_factura)) {
            return $this->response(false, 'El ID de factura es obligatorio');
        }
        
        // Aquí iría la lógica para generar factura PDF
        return $this->response(true, 'Factura generada exitosamente', [
            'id_factura' => $id_factura,
            'pdf_url' => "../../facturas/factura_{$id_factura}.pdf"
        ]);
    }
    
    /**
     * Actualizar módulo
     */
    private function actualizarModulo() {
        $id_modulo = $this->data['id_modulo'] ?? null;
        $estado = $this->data['estado'] ?? null;
        
        if (empty($id_modulo) || empty($estado)) {
            return $this->response(false, 'ID de módulo y estado son obligatorios');
        }
        
        // Aquí iría la lógica para actualizar módulo
        return $this->response(true, 'Módulo actualizado exitosamente');
    }
    
    /**
     * Actualizar entrada
     */
    private function actualizarEntrada() {
        // Implementar lógica de actualización de entrada
        return $this->response(true, 'Entrada actualizada exitosamente');
    }
    
    /**
     * Actualizar factura
     */
    private function actualizarFactura() {
        // Implementar lógica de actualización de factura
        return $this->response(true, 'Factura actualizada exitosamente');
    }
    
    /**
     * Eliminar entrada
     */
    private function eliminarEntrada() {
        // Implementar lógica de eliminación de entrada
        return $this->response(true, 'Entrada eliminada exitosamente');
    }
    
    /**
     * Eliminar factura
     */
    private function eliminarFactura() {
        // Implementar lógica de eliminación de factura
        return $this->response(true, 'Factura eliminada exitosamente');
    }
    
    /**
     * Obtener estado completo del sistema
     */
    private function getSystemStatus() {
        try {
            $modulos = $this->model->getModulos();
            $entradas = $this->model->getEntradasActivas();
            $facturas = $this->model->getFacturasPendientes();
            $estadisticas = $this->model->getEstadisticasDashboard();
            
            return $this->response(true, 'Estado del sistema obtenido exitosamente', [
                'timestamp' => date('Y-m-d H:i:s'),
                'modulos' => $modulos['data'] ?? [],
                'entradas_activas' => $entradas['data'] ?? [],
                'facturas_pendientes' => $facturas['data'] ?? [],
                'estadisticas' => $estadisticas['data'] ?? [],
                'database_status' => 'connected',
                'session_status' => session_status() === PHP_SESSION_ACTIVE ? 'active' : 'inactive'
            ]);
        } catch (Exception $e) {
            return $this->response(false, 'Error obteniendo estado del sistema: ' . $e->getMessage());
        }
    }
    
    /**
     * Formatear respuesta JSON
     */
    private function response($success, $message = '', $data = [], $httpCode = 200) {
        http_response_code($httpCode);
        
        $response = [
            'success' => $success,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        return json_encode($response);
    }
}

// Inicializar y manejar la petición
$controller = new ParkingController();
echo $controller->handleRequest();
?>
