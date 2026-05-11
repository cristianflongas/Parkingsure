<?php
// ============================================================
//  PARKINGSURE - Generador de PDF Optimizado
//  Archivo: PDFGenerator.php - Clase centralizada para PDFs
// ============================================================

class PDFGenerator {
    private $pdf;
    private $pageTitle;
    private $author = 'PARKINGSURE';
    private $tempDir;
    
    public function __construct() {
        $this->tempDir = __DIR__ . '/../temp';
        $this->ensureTempDir();
        
        $this->pdf = new FPDF();
        $this->pdf->SetAutoPageBreak(true, 15);
        $this->pdf->SetLeftMargin(15);
        $this->pdf->SetRightMargin(15);
        $this->pdf->SetTopMargin(20);
    }
    
    private function ensureTempDir() {
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }
    
    public function setTitle($title) {
        $this->pageTitle = $title;
    }
    
    public function setAuthor($author) {
        $this->author = $author;
    }
    
    private function addHeader() {
        $this->pdf->AddPage();
        $this->pdf->SetFont('Arial', 'B', 24);
        $this->pdf->SetTextColor(255, 215, 0); // Dorado
        $this->pdf->Cell(0, 10, 'PARKINGSURE', 0, 1, 'C');
        
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'Sistema de Gestión de Parqueadero', 0, 1, 'C');
        
        $this->pdf->Ln(10);
        
        // Título del documento
        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Cell(0, 8, $this->pageTitle, 0, 1, 'C');
        
        // Línea separadora
        $this->pdf->Ln(5);
        $this->pdf->SetDrawColor(255, 215, 0);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        $this->pdf->Ln(10);
    }
    
    private function addFooter() {
        $this->pdf->SetY(-30);
        $this->pdf->SetFont('Arial', 'I', 9);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 5, 'Sistema PARKINGSURE © 2026', 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'Gracias por preferir nuestros servicios', 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'Tel: (1) 234-5678 | Email: info@parkingsure.com', 0, 1, 'C');
    }
    
    private function formatCurrency($amount) {
        return '$' . number_format($amount, 0, ',', '.');
    }
    
    private function formatDate($date) {
        return date('d/m/Y H:i', strtotime($date));
    }
    
    public function generateFactura($facturaData) {
        $this->setTitle('FACTURA');
        
        $this->addHeader();
        
        // Información de la factura
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(0, 6, 'No. Factura: ' . $facturaData['id_factura'], 0, 1);
        $this->pdf->Cell(0, 6, 'Fecha: ' . $this->formatDate($facturaData['fecha_emision']), 0, 1);
        $this->pdf->Ln(8);
        
        // Datos del cliente y vehículo
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'DATOS DEL CLIENTE', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Placa:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(0, 6, $facturaData['placa'], 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Módulo:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(0, 6, $facturaData['ubicacion'] ?? 'N/A', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Tipo Servicio:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(0, 6, $facturaData['nombre_tipo_servicio'] ?? 'N/A', 0, 1);
        $this->pdf->Ln(10);
        
        // Detalle del servicio
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'DETALLE DEL SERVICIO', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        if (isset($facturaData['fecha_hora_entrada'])) {
            $this->pdf->Cell(60, 6, 'Fecha Entrada:', 0, 0);
            $this->pdf->Cell(0, 6, $this->formatDate($facturaData['fecha_hora_entrada']), 0, 1);
            $this->pdf->Ln(6);
        }
        
        if (isset($facturaData['fecha_hora_salida'])) {
            $this->pdf->Cell(60, 6, 'Fecha Salida:', 0, 0);
            $this->pdf->Cell(0, 6, $this->formatDate($facturaData['fecha_hora_salida']), 0, 1);
            $this->pdf->Ln(6);
        }
        
        if (isset($facturaData['tiempo_estancia'])) {
            $this->pdf->Cell(60, 6, 'Tiempo Estancia:', 0, 0);
            $this->pdf->Cell(0, 6, $facturaData['tiempo_estancia'], 0, 1);
            $this->pdf->Ln(6);
        }
        
        $this->pdf->Cell(60, 6, 'Tarifa:', 0, 0);
        $this->pdf->Cell(0, 6, $this->formatCurrency($facturaData['tarifa'] ?? 0), 0, 1);
        $this->pdf->Ln(10);
        
        // Total
        $this->pdf->SetFont('Arial', 'B', 14);
        $this->pdf->SetTextColor(255, 215, 0); // Dorado
        $this->pdf->Cell(120, 8, 'TOTAL A PAGAR:', 0, 0);
        $this->pdf->Cell(0, 8, $this->formatCurrency($facturaData['monto_total']), 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        
        // Método de pago y estado
        $this->pdf->Ln(10);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Método de Pago:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(0, 6, $facturaData['metodo_pago'] ?? 'PENDIENTE', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Estado:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        
        if ($facturaData['estado_pago'] === 'PAGADA') {
            $this->pdf->SetTextColor(0, 150, 0); // Verde
            $this->pdf->Cell(0, 6, 'PAGADA', 0, 1);
        } else {
            $this->pdf->SetTextColor(200, 0, 0); // Rojo
            $this->pdf->Cell(0, 6, 'PENDIENTE', 0, 1);
        }
        
        $this->pdf->SetTextColor(0, 0, 0);
        
        $this->addFooter();
        
        return $this->outputPDF();
    }
    
    public function generateReporteIngresos($data, $fechaInicio = null, $fechaFin = null) {
        $this->setTitle('REPORTE DE INGRESOS');
        
        $this->addHeader();
        
        // Período del reporte
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'PERÍODO DEL REPORTE', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        if ($fechaInicio && $fechaFin) {
            $this->pdf->Cell(0, 6, 'Desde: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - Hasta: ' . date('d/m/Y', strtotime($fechaFin)), 0, 1);
        } else {
            $this->pdf->Cell(0, 6, 'Todos los registros', 0, 1);
        }
        $this->pdf->Ln(10);
        
        // Resumen
        $totalIngresos = 0;
        $totalFacturas = count($data);
        
        foreach ($data as $item) {
            $totalIngresos += $item['monto_total'];
        }
        
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'RESUMEN', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Total Facturas:', 0, 0);
        $this->pdf->Cell(0, 6, $totalFacturas, 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->Cell(60, 6, 'Total Ingresos:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->Cell(0, 6, $this->formatCurrency($totalIngresos), 0, 1);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Ln(15);
        
        // Tabla de detalles
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'DETALLE DE INGRESOS', 0, 1);
        $this->pdf->Ln(8);
        
        // Encabezados de tabla
        $headers = ['Fecha', 'Placa', 'Servicio', 'Método', 'Estado', 'Monto'];
        $widths = [25, 20, 30, 25, 25, 35];
        
        $this->pdf->SetFont('Arial', 'B', 10);
        foreach ($headers as $i => $header) {
            $this->pdf->Cell($widths[$i], 8, $header, 1, 0, 'C');
        }
        $this->pdf->Ln();
        
        // Datos de la tabla
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetFillColor(245, 245, 245);
        
        foreach ($data as $item) {
            $this->pdf->Cell($widths[0], 8, $this->formatDate($item['fecha_emision']), 1, 0, 'C');
            $this->pdf->Cell($widths[1], 8, $item['placa'], 1, 0, 'C');
            $this->pdf->Cell($widths[2], 8, $item['nombre_tipo_servicio'], 1, 0, 'C');
            $this->pdf->Cell($widths[3], 8, $item['metodo_pago'], 1, 0, 'C');
            $this->pdf->Cell($widths[4], 8, $item['estado_pago'], 1, 0, 'C');
            $this->pdf->Cell($widths[5], 8, $this->formatCurrency($item['monto_total']), 1, 0, 'R');
            $this->pdf->Ln();
        }
        
        $this->addFooter();
        
        return $this->outputPDF();
    }
    
    public function generateReporteOcupacion($data) {
        $this->setTitle('REPORTE DE OCUPACIÓN');
        
        $this->addHeader();
        
        // Resumen de ocupación
        $totalModulos = count($data);
        $modulosOcupados = 0;
        $modulosDisponibles = 0;
        
        foreach ($data as $modulo) {
            if ($modulo['estado'] === 'OCUPADO') {
                $modulosOcupados++;
            } else {
                $modulosDisponibles++;
            }
        }
        
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'RESUMEN DE OCUPACIÓN', 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Cell(60, 6, 'Total Módulos:', 0, 0);
        $this->pdf->Cell(0, 6, $totalModulos, 0, 1);
        $this->pdf->Ln(6);
        
        $this->pdf->Cell(60, 6, 'Ocupados:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->SetTextColor(200, 0, 0); // Rojo
        $this->pdf->Cell(0, 6, $modulosOcupados, 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Ln(6);
        
        $this->pdf->Cell(60, 6, 'Disponibles:', 0, 0);
        $this->pdf->SetFont('Arial', 'B', 11);
        $this->pdf->SetTextColor(0, 150, 0); // Verde
        $this->pdf->Cell(0, 6, $modulosDisponibles, 0, 1);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('Arial', '', 11);
        $this->pdf->Ln(15);
        
        // Tabla de módulos
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 6, 'ESTADO DE MÓDULOS', 0, 1);
        $this->pdf->Ln(8);
        
        // Encabezados de tabla
        $headers = ['ID', 'Ubicación', 'Estado', 'Vehículo'];
        $widths = [20, 60, 40, 40];
        
        $this->pdf->SetFont('Arial', 'B', 10);
        foreach ($headers as $i => $header) {
            $this->pdf->Cell($widths[$i], 8, $header, 1, 0, 'C');
        }
        $this->pdf->Ln();
        
        // Datos de la tabla
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetFillColor(245, 245, 245);
        
        foreach ($data as $modulo) {
            $this->pdf->Cell($widths[0], 8, $modulo['id_modulo'], 1, 0, 'C');
            $this->pdf->Cell($widths[1], 8, $modulo['ubicacion'], 1, 0, 'C');
            
            // Color según estado
            if ($modulo['estado'] === 'OCUPADO') {
                $this->pdf->SetTextColor(200, 0, 0); // Rojo
            } elseif ($modulo['estado'] === 'DISPONIBLE') {
                $this->pdf->SetTextColor(0, 150, 0); // Verde
            } else {
                $this->pdf->SetTextColor(150, 150, 0); // Gris
            }
            
            $this->pdf->Cell($widths[2], 8, $modulo['estado'], 1, 0, 'C');
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Cell($widths[3], 8, $modulo['placa'] ?? '—', 1, 0, 'C');
            $this->pdf->Ln();
        }
        
        $this->addFooter();
        
        return $this->outputPDF();
    }
    
    private function outputPDF() {
        try {
            // Generar nombre de archivo único
            $sanitizedTitle = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->pageTitle);
            $filename = $sanitizedTitle . '_' . date('Y_m_d_H_i_s') . '.pdf';
            $filepath = $this->tempDir . '/' . $filename;
            
            // Guardar PDF
            $this->pdf->Output($filepath, 'F');
            
            // Verificar que el archivo se creó correctamente
            if (!file_exists($filepath)) {
                throw new Exception('No se pudo crear el archivo PDF');
            }
            
            $fileSize = filesize($filepath);
            if ($fileSize === 0) {
                throw new Exception('El archivo PDF está vacío');
            }
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'url' => 'temp/' . $filename,
                'size' => $fileSize,
                'size_formatted' => $this->formatFileSize($fileSize)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'filename' => null,
                'filepath' => null,
                'url' => null,
                'size' => 0
            ];
        }
    }
    
    private function formatFileSize($bytes) {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
?>
