/**
 * Controlador MVC para el módulo de Pagos
 * Arquitectura Modelo-Vista-Controlador
 */
class PagosView {
    constructor() {
        this.elementos = {
            stHoy: document.getElementById('stHoy'),
            stTx: document.getElementById('stTx'),
            stProm: document.getElementById('stProm'),
            stPend: document.getElementById('stPend'),
            facturasTable: document.getElementById('facturasPendientesTable'),
            historialTable: document.getElementById('hisTable'),
            buscarFactura: document.getElementById('buscarFactura'),
            buscarHis: document.getElementById('buscarHis')
        };
        
        this.datos = {
            historial: [],
            activos: {},
            facturasPendientes: [],
            facturasFiltradas: []
        };
        
        this.paginacion = {
            currentHistorialPage: 1,
            totalHistorialPages: 1,
            totalHistorialItems: 0
        };
    }
    
    /**
     * Inicializar la vista
     */
    init() {
        this.cargarDatosIniciales();
    }
    
    /**
     * Cargar datos iniciales desde APIs
     */
    async cargarDatosIniciales() {
        this.log('🔄 Cargando datos iniciales desde APIs');
        
        try {
            // Limpiar datos primero
            this.limpiarDatos();
            
            // Forzar valores en $0
            this.forzarValoresIniciales();
            
            // Cargar datos en paralelo
            const [activosData, historialData, facturasData] = await Promise.all([
                this.API.getActivos(),
                this.API.getHistorial(),
                this.API.getFacturasPendientes()
            ]);
            
            // Procesar datos
            this.datos.activos = activosData;
            this.datos.historial = historialData;
            this.datos.facturasPendientes = facturasData;
            this.datos.facturasFiltradas = [...facturasData];
            
            // Actualizar UI
            this.actualizarUI();
            
            this.log('✅ Datos cargados exitosamente');
            
        } catch (error) {
            this.log(`❌ Error cargando datos: ${error.message}`, 'error');
            this.log('🔄 Usando datos de prueba', 'warning');
            this.cargarDatosPrueba();
        }
    }
    
    /**
     * Limpiar todos los datos
     */
    limpiarDatos() {
        this.datos.historial = [];
        this.datos.activos = {};
        this.datos.facturasPendientes = [];
        this.datos.facturasFiltradas = [];
        this.log('🧹 Datos limpiados');
    }
    
    /**
     * Forzar valores iniciales en $0
     */
    forzarValoresIniciales() {
        if (this.elementos.stHoy) this.elementos.stHoy.textContent = '$0';
        if (this.elementos.stTx) this.elementos.stTx.textContent = '0';
        if (this.elementos.stProm) this.elementos.stProm.textContent = '$0';
        if (this.elementos.stPend) this.elementos.stPend.textContent = '0';
        
        this.log('📊 Valores forzados a $0');
    }
    
    /**
     * Actualizar toda la interfaz
     */
    actualizarUI() {
        this.renderEstadisticas();
        this.renderFacturasPendientes();
        this.renderHistorial();
        this.log('🎨 UI actualizada');
    }
    
    /**
     * Renderizar estadísticas
     */
    renderEstadisticas() {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const manana = new Date(hoy);
        manana.setDate(manana.getDate() + 1);
        
        // Filtrar historial por fecha actual
        const historialHoy = this.datos.historial.filter(item => {
            if (item.fecha_emision) {
                const fechaItem = new Date(item.fecha_emision);
                return fechaItem >= hoy && fechaItem < manana;
            }
            return false;
        });
        
        // Calcular estadísticas del día
        const totalPagadoHoy = historialHoy.reduce((sum, item) => sum + (item.monto || 0), 0);
        const transaccionesHoy = historialHoy.length;
        const promedioHoy = transaccionesHoy > 0 ? Math.round(totalPagadoHoy / transaccionesHoy) : 0;
        const pendientes = Object.keys(this.datos.activos).length;
        
        // Actualizar elementos
        if (this.elementos.stHoy) {
            this.elementos.stHoy.textContent = '$' + totalPagadoHoy.toLocaleString('es-CO');
        }
        if (this.elementos.stTx) {
            this.elementos.stTx.textContent = transaccionesHoy;
        }
        if (this.elementos.stProm) {
            this.elementos.stProm.textContent = '$' + promedioHoy.toLocaleString('es-CO');
        }
        if (this.elementos.stPend) {
            this.elementos.stPend.textContent = pendientes;
        }
        
        this.log(`📊 Estadísticas actualizadas: Ingresos: $${totalPagadoHoy}, Transacciones: ${transaccionesHoy}, Promedio: $${promedioHoy}, Pendientes: ${pendientes}`);
    }
    
    /**
     * Renderizar facturas pendientes
     */
    renderFacturasPendientes() {
        if (!this.elementos.facturasTable) return;
        
        const facturas = this.datos.facturasFiltradas;
        
        if (facturas.length === 0) {
            this.elementos.facturasTable.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--text-muted);padding:28px">
                        Sin facturas pendientes
                    </td>
                </tr>
            `;
            return;
        }
        
        this.elementos.facturasTable.innerHTML = facturas.map(factura => `
            <tr>
                <td><strong style="font-family:monospace">${factura.placa}</strong></td>
                <td>${factura.ubicacion || 'N/A'}</td>
                <td>${factura.tiempo_estancia || 'N/A'}</td>
                <td class="his-amount">$${parseFloat(factura.monto_total).toLocaleString('es-CO')}</td>
                <td><span class="badge badge-gold" style="font-size:11px">${factura.estado_pago}</span></td>
                <td>
                    <button class="btn-edit" style="font-size:11px" onclick="pagosView.generarFactura('${factura.id_factura}')">
                        PDF
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.log(`📋 Renderizadas ${facturas.length} facturas pendientes`);
    }
    
    /**
     * Renderizar historial
     */
    renderHistorial() {
        if (!this.elementos.historialTable) return;
        
        const historial = this.datos.historial;
        
        if (historial.length === 0) {
            this.elementos.historialTable.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align:center;color:var(--text-muted);padding:28px">
                        Sin registros
                    </td>
                </tr>
            `;
            return;
        }
        
        this.elementos.historialTable.innerHTML = historial.map(item => `
            <tr>
                <td style="color:var(--text-muted)">${item.hora}</td>
                <td><strong style="font-family:monospace">${item.placa}</strong></td>
                <td style="color:var(--text-secondary)">${item.dur}</td>
                <td><span class="badge badge-gold" style="font-size:11px">${item.metodo}</span></td>
                <td class="his-amount">$${item.monto.toLocaleString('es-CO')}</td>
                <td>
                    <button class="btn-edit" style="font-size:11px" onclick="pagosView.generarFacturaHistorial('${item.placa}', '${item.hora}')">
                        PDF
                    </button>
                </td>
            </tr>
        `).join('');
        
        this.log(`📋 Renderizado historial con ${historial.length} registros`);
    }
    
    /**
     * Filtrar facturas pendientes
     */
    filtrarFacturasPendientes() {
        const query = (this.elementos.buscarFactura?.value || '').toLowerCase();
        this.datos.facturasFiltradas = this.datos.facturasPendientes.filter(factura =>
            factura.placa.toLowerCase().includes(query)
        );
        this.renderFacturasPendientes();
        this.log(`🔍 Filtradas ${this.datos.facturasFiltradas.length} facturas con: "${query}"`);
    }
    
    /**
     * Filtrar historial
     */
    filtrarHistorial() {
        const query = (this.elementos.buscarHis?.value || '').toLowerCase();
        const filtrados = this.datos.historial.filter(item =>
            item.placa.toLowerCase().includes(query) || item.metodo.toLowerCase().includes(query)
        );
        this.renderHistorial();
        this.log(`🔍 Filtrados ${filtrados.length} registros con: "${query}"`);
    }
    
    /**
     * Cargar datos de prueba
     */
    async cargarDatosPrueba() {
        this.log('🧪 Cargando datos de prueba');
        
        const hoy = new Date();
        
        // Datos de prueba para historial
        this.datos.historial = [
            {
                hora: '09:15',
                placa: 'ABC123',
                dur: '2 horas',
                metodo: 'Efectivo',
                monto: 6000,
                fecha_emision: hoy.toISOString()
            },
            {
                hora: '10:30',
                placa: 'DEF456',
                dur: '1 hora',
                metodo: 'Tarjeta',
                monto: 3000,
                fecha_emision: hoy.toISOString()
            },
            {
                hora: '11:45',
                placa: 'GHI789',
                dur: '3 horas',
                metodo: 'Efectivo',
                monto: 9000,
                fecha_emision: hoy.toISOString()
            }
        ];
        
        // Datos de prueba para activos
        this.datos.activos = {
            'JKL012': {
                modulo: 'A1',
                entrada: '08:00',
                tarifa: 3000,
                owner: 'Cliente 1',
                id_entrada: 1
            },
            'MNO345': {
                modulo: 'B2',
                entrada: '09:30',
                tarifa: 3000,
                owner: 'Cliente 2',
                id_entrada: 2
            },
            'PQR678': {
                modulo: 'C3',
                entrada: '10:15',
                tarifa: 3000,
                owner: 'Cliente 3',
                id_entrada: 3
            }
        };
        
        // Datos de prueba para facturas pendientes
        this.datos.facturasPendientes = [
            {
                id_factura: 1,
                placa: 'STU901',
                ubicacion: 'D4',
                tiempo_estancia: '1 hora 30 min',
                monto_total: 4500,
                estado_pago: 'PENDIENTE',
                fecha_emision: hoy.toISOString()
            },
            {
                id_factura: 2,
                placa: 'VWX234',
                ubicacion: 'E5',
                tiempo_estancia: '45 min',
                monto_total: 3000,
                estado_pago: 'PENDIENTE',
                fecha_emision: hoy.toISOString()
            }
        ];
        
        this.datos.facturasFiltradas = [...this.datos.facturasPendientes];
        
        this.actualizarUI();
        this.log('✅ Datos de prueba cargados');
    }
    
    /**
     * Resetear sistema
     */
    resetear() {
        this.log('🔄 Resetear sistema');
        
        this.limpiarDatos();
        this.forzarValoresIniciales();
        this.actualizarUI();
        
        // Recargar datos reales
        setTimeout(() => {
            this.cargarDatosIniciales();
        }, 1000);
        
        this.log('✅ Sistema reseteado');
    }
    
    /**
     * Diagnosticar sistema
     */
    async diagnosticar() {
        this.log('🔍 Iniciando diagnóstico completo');
        
        try {
            // Verificar APIs
            const [entradaResult, historialResult, facturasResult] = await Promise.all([
                this.API.getActivos(),
                this.API.getHistorial(),
                this.API.getFacturasPendientes()
            ]);
            
            let resultadoHTML = '<div style="line-height: 1.4;">';
            resultadoHTML += '<h5 style="color: #3498db; margin: 10px 0;">📋 ESTADO DE LAS APIS</h5>';
            
            resultadoHTML += '<div style="margin: 10px 0; padding: 10px; background: #34495e; border-radius: 5px; color: white;">';
            resultadoHTML += `<h6>Entrada API:</h6> ${entradaResult.success ? '✅ Funcionando' : '❌ Error'} - ${entradaResult.data ? entradaResult.data.length : 0} vehículos`;
            resultadoHTML += `<h6>Historial API:</h6> ${historialResult.success ? '✅ Funcionando' : '❌ Error'} - ${historialResult.data ? historialResult.data.length : 0} registros`;
            resultadoHTML += `<h6>Facturas API:</h6> ${facturasResult.success ? '✅ Funcionando' : '❌ Error'} - ${facturasResult.data ? facturasResult.data.length : 0} facturas`;
            resultadoHTML += '</div>';
            
            resultadoHTML += '<div style="margin: 10px 0; padding: 10px; background: #27ae60; border-radius: 5px; color: white;">';
            resultadoHTML += `<h6>Variables Locales:</h6>`;
            resultadoHTML += `Historial: ${this.datos.historial.length} registros<br>`;
            resultadoHTML += `Activos: ${Object.keys(this.datos.activos).length} vehículos<br>`;
            resultadoHTML += `Facturas: ${this.datos.facturasPendientes.length} pendientes`;
            resultadoHTML += '</div>';
            
            resultadoHTML += '</div>';
            
            // Mostrar en panel de diagnóstico
            const panel = document.getElementById('diagnosticoPanel');
            const contenido = document.getElementById('diagnosticoContenido');
            
            if (panel && contenido) {
                contenido.innerHTML = resultadoHTML;
                panel.style.display = 'block';
                
                setTimeout(() => {
                    panel.style.display = 'none';
                }, 15000);
            }
            
            this.log('✅ Diagnóstico completado');
            
        } catch (error) {
            this.log(`❌ Error en diagnóstico: ${error.message}`, 'error');
        }
    }
    
    /**
     * Generar factura PDF
     */
    async generarFactura(idFactura) {
        this.log(`📄 Generando factura ${idFactura}`);
        
        try {
            const response = await fetch(`../../controllers/facturaapi.php?action=generarPDF&id=${idFactura}`);
            const result = await response.json();
            
            if (result.success) {
                this.log(`✅ Factura ${idFactura} generada: ${result.message}`);
                // Abrir PDF si hay URL
                if (result.pdf_url) {
                    window.open(result.pdf_url, '_blank');
                }
            } else {
                this.log(`❌ Error generando factura: ${result.message}`, 'error');
            }
        } catch (error) {
            this.log(`❌ Error en generación de factura: ${error.message}`, 'error');
        }
    }
    
    /**
     * Generar factura desde historial
     */
    generarFacturaHistorial(placa, hora) {
        this.log(`📄 Generando factura para ${placa} - ${hora}`);
        // Implementar lógica de generación desde historial
        this.log('📄 Función de generación desde historial no implementada completamente');
    }
}

/**
 * Clase para manejar las APIs
 */
class PagosAPI {
    constructor() {
        this.baseURL = '../../controllers';
    }
    
    /**
     * Obtener vehículos activos
     */
    async getActivos() {
        try {
            const response = await fetch(`${this.baseURL}/entradaapi.php?action=getActive`);
            return await response.json();
        } catch (error) {
            console.error('Error en API de entrada:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Obtener historial de pagos
     */
    async getHistorial() {
        try {
            const hoy = new Date().toISOString().split('T')[0];
            const response = await fetch(`${this.baseURL}/facturaapi.php?action=getPagadas&fecha=${hoy}`);
            return await response.json();
        } catch (error) {
            console.error('Error en API de historial:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Obtener facturas pendientes
     */
    async getFacturasPendientes() {
        try {
            const response = await fetch(`${this.baseURL}/facturaapi.php?action=getPendientes`);
            return await response.json();
        } catch (error) {
            console.error('Error en API de facturas:', error);
            return { success: false, message: error.message };
        }
    }
}

/**
 * Controlador principal que une Vista y APIs
 */
class PagosController {
    constructor() {
        this.view = new PagosView();
        this.api = new PagosAPI();
    }
    
    /**
     * Inicializar el sistema
     */
    init() {
        this.view.init();
    }
    
    /**
     * Delegar métodos a la vista
     */
    get diagnosticar() { return this.view.diagnosticar.bind(this.view); }
    get cargarDatosPrueba() { return this.view.cargarDatosPrueba.bind(this.view); }
    get resetear() { return this.view.resetear.bind(this.view); }
    get filtrarFacturasPendientes() { return this.view.filtrarFacturasPendientes.bind(this.view); }
    get filtrarHistorial() { return this.view.filtrarHistorial.bind(this.view); }
    get generarFactura() { return this.view.generarFactura.bind(this.view); }
    get generarFacturaHistorial() { return this.view.generarFacturaHistorial.bind(this.view); }
}

// Instanciar y hacer disponible globalmente
const PagosView = new PagosView();
const PagosController = new PagosController();

// Hacer disponible para los onclick en el HTML
window.PagosView = PagosView;
window.PagosController = PagosController;

/**
 * Función de diagnóstico para el módulo de parqueadero
 */
window.diagnosticarParqueadero = async function() {
    console.log('🚗 INICIANDO DIAGNÓSTICO DE PARQUEADERO');
    
    try {
        // 1. Verificar APIs de parqueadero
        console.log('🌐 Verificando APIs de parqueadero...');
        
        const [modulosResult, entradaResult, salidaResult] = await Promise.all([
            fetch('../../controllers/moduloapi.php?action=getAll').then(r => r.json()),
            fetch('../../controllers/entradaapi.php?action=getActive').then(r => r.json()),
            fetch('../../controllers/salidaapi.php?action=getActive').then(r => r.json())
        ]);
        
        // 2. Verificar módulos disponibles
        console.log('📊 Módulos disponibles:', modulosResult);
        
        // 3. Verificar entradas activas
        console.log('🚗 Entradas activas:', entradaResult);
        
        // 4. Verificar salidas activas
        console.log('🚪 Salidas activas:', salidaResult);
        
        // 5. Generar diagnóstico
        let diagnosticoHTML = '<div style="line-height: 1.4;">';
        diagnosticoHTML += '<h5 style="color: #f39c12; margin: 10px 0;">🚗 DIAGNÓSTICO DE PARQUEADERO</h5>';
        
        diagnosticoHTML += '<div style="margin: 10px 0; padding: 10px; background: #34495e; border-radius: 5px; color: white;">';
        diagnosticoHTML += '<h6 style="color: #3498db;">📊 Estado del Sistema:</h6>';
        diagnosticoHTML += `Módulos: ${modulosResult.success ? modulosResult.data?.length || 0 : 'Error'}<br>`;
        diagnosticoHTML += `Entradas activas: ${entradaResult.success ? entradaResult.data?.length || 0 : 'Error'}<br>`;
        diagnosticoHTML += `Salidas activas: ${salidaResult.success ? salidaResult.data?.length || 0 : 'Error'}`;
        diagnosticoHTML += '</div>';
        
        diagnosticoHTML += '<div style="margin: 10px 0; padding: 10px; background: #27ae60; border-radius: 5px; color: white;">';
        diagnosticoHTML += '<h6 style="color: #f39c12;">🔍 Posibles Problemas:</h6>';
        diagnosticoHTML += '• Si no puedes asignar módulo:<br>';
        diagnosticoHTML += '  - Revisa si hay módulos disponibles<br>';
        diagnosticoHTML += '  - Verifica que los módulos no estén todos ocupados<br>';
        diagnosticoHTML += '• Si no puedes registrar entrada:<br>';
        diagnosticoHTML += '  - Revisa si el vehículo existe en el sistema<br>';
        diagnosticoHTML += '  - Verifica si hay módulos libres<br>';
        diagnosticoHTML += '• Si hay errores en las APIs:<br>';
        diagnosticoHTML += '  - Revisa la consola para ver mensajes de error<br>';
        diagnosticoHTML += '  - Verifica si las APIs están respondiendo correctamente';
        diagnosticoHTML += '</div>';
        
        diagnosticoHTML += '<div style="margin: 10px 0; padding: 10px; background: #e74c3c; border-radius: 5px; color: white;">';
        diagnosticoHTML += '<h6 style="color: #f39c12;">🎯 Recomendaciones:</h6>';
        diagnosticoHTML += '1. Ve al módulo de Parqueadero<br>';
        diagnosticoHTML += '2. Intenta asignar un vehículo a un módulo libre<br>';
        diagnosticoHTML += '3. Si hay errores, revisa la consola del navegador<br>';
        diagnosticoHTML += '4. Verifica que los módulos estén en estado "DISPONIBLE"';
        diagnosticoHTML += '</div>';
        
        diagnosticoHTML += '</div>';
        
        // 6. Mostrar en panel de diagnóstico existente
        const panel = document.getElementById('diagnosticoPanel');
        const contenido = document.getElementById('diagnosticoContenido');
        
        if (panel && contenido) {
            contenido.innerHTML = diagnosticoHTML;
            panel.style.display = 'block';
            
            // Auto-ocultar después de 20 segundos
            setTimeout(() => {
                panel.style.display = 'none';
            }, 20000);
        }
        
        console.log('✅ Diagnóstico de parqueadero completado');
        
    } catch (error) {
        console.error('❌ Error en diagnóstico de parqueadero:', error);
        alert('❌ Error al diagnosticar el sistema de parqueadero');
    }
};
