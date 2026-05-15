<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}
$rolUsuario = $_SESSION['rol'] ?? 'OPERADOR';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Reportes</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css?v=2" rel="stylesheet">
  <style>
    .filter-row { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .filter-row .fg { flex:1; min-width:140px; }
    .filter-row label { display:block; font-size:10px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--text-muted); margin-bottom:6px; }

    .stats-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:20px; }
    @media(max-width:860px){ .stats-grid-4 { grid-template-columns:repeat(2,1fr); } }

    .two-col { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
    @media(max-width:900px){ .two-col { grid-template-columns:1fr; } }

    /* Barras */
    .bar-row { margin-bottom:16px; }
    .bar-head { display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--text-primary); }
    .bar-sub  { color:var(--text-muted); font-size:11px; font-weight:400; }
    .bar-track { height:7px; background:var(--surface-3); border-radius:4px; overflow:hidden; }
    .bar-fill  { height:100%; border-radius:4px; background:var(--gold); transition:width .2s ease; }

    /* Franjas horarias */
    .hora-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; }
    @media(max-width:600px){ .hora-grid { grid-template-columns:repeat(2,1fr); } }
    .hora-cell { text-align:center; padding:12px 6px; border-radius:var(--r-sm); border:1px solid var(--border); background:var(--surface-2); }
    .hora-cell.activa { background:rgba(232,184,75,.07); border-color:rgba(232,184,75,.25); }
    .hora-num { font-size:9px; font-weight:700; letter-spacing:.5px; color:var(--text-muted); margin-bottom:6px; text-transform:uppercase; }
    .hora-qty { font-family:'Syne',sans-serif; font-size:22px; font-weight:800; color:var(--text-primary); }
    .hora-cell.activa .hora-qty { color:var(--gold); }

    /* Badges */
    .badge-pend { background:rgba(245,158,11,.15); color:#f59e0b; border:1px solid rgba(245,158,11,.3); border-radius:20px; padding:2px 9px; font-size:11px; font-weight:700; }
    .badge-paid { background:rgba(16,185,129,.15); color:#10b981; border:1px solid rgba(16,185,129,.3); border-radius:20px; padding:2px 9px; font-size:11px; font-weight:700; }
    .badge-act  { background:rgba(59,130,246,.15);  color:#3b82f6; border:1px solid rgba(59,130,246,.3);  border-radius:20px; padding:2px 9px; font-size:11px; font-weight:700; }

    .empty-state { text-align:center; padding:36px 20px; color:var(--text-muted); }
    .empty-state .es-icon { font-size:32px; margin-bottom:8px; }

    /* ── REPORTE IMPRIMIBLE ── */
    #reporteImprimible { display:none; }

    @media print {
      body > *:not(#reporteImprimible) { display:none !important; }
      #reporteImprimible {
        display:block !important;
        font-family: Arial, sans-serif;
        color: #000;
        padding: 20px;
      }
      .rp-header { text-align:center; border-bottom:2px solid #000; padding-bottom:12px; margin-bottom:20px; }
      .rp-logo   { font-size:24px; font-weight:900; letter-spacing:1px; }
      .rp-logo span { color:#b8860b; }
      .rp-fecha  { font-size:13px; color:#555; margin-top:4px; }
      .rp-stats  { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px; }
      .rp-stat   { border:1px solid #ccc; border-radius:6px; padding:12px; text-align:center; }
      .rp-stat-val { font-size:20px; font-weight:900; color:#b8860b; }
      .rp-stat-lbl { font-size:11px; color:#666; margin-top:4px; }
      .rp-section { font-size:14px; font-weight:700; border-bottom:1px solid #ccc; padding-bottom:6px; margin:16px 0 10px; }
      .rp-table  { width:100%; border-collapse:collapse; font-size:12px; }
      .rp-table th { background:#f0f0f0; padding:7px 10px; text-align:left; border:1px solid #ccc; font-weight:700; }
      .rp-table td { padding:6px 10px; border:1px solid #ddd; }
      .rp-table tr:nth-child(even) td { background:#fafafa; }
      .rp-footer { text-align:center; font-size:11px; color:#888; margin-top:20px; border-top:1px solid #ccc; padding-top:10px; }
    }
  </style>
</head>
<body>

<nav class="topbar">
  <a class="logo"><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <button class="hamburger" id="hamburger-btn"><span></span><span></span><span></span></button>
  <div class="nav-links" id="nav-links">
    <a class="nb" href="dashboard.php"> Dashboard</a>
    <a class="nb" href="parqueadero.php"> Parqueadero</a>
    <a class="nb" href="vehiculos.php"> Vehículos</a>
    <a class="nb" href="pagos.php"> Pagos</a>
    <?php if ($rolUsuario === 'ADMINISTRADOR'): ?>
    <a class="nb" href="usuarios.php"> Usuarios</a>
    <a class="nb" href="servicios.php"> Servicios</a>
    <?php endif; ?>
    <a class="nb active" href="reportes.php"> Reportes</a>
  </div>
  <div class="nav-right">
    <div class="user-avatar"><?php echo htmlspecialchars(substr($_SESSION['nombre'] ?? 'U', 0, 1)); ?></div>
    <div class="user-info">
      <div class="u-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></div>
      <div class="u-role"><?php echo htmlspecialchars($_SESSION['rol'] ?? 'Operador'); ?></div>
    </div>
    <a class="btn-logout" href="../../controllers/logout.php">Salir</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <div class="page-eyebrow">Inteligencia de negocio</div>
    <h1 class="page-title">Reportes</h1>
    <div class="title-rule"></div>
    <p class="page-sub" id="subtitulo">Cargando…</p>
  </div>

  <!-- Filtro -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-title"><span class="card-title-icon">🔍</span> Filtro de Reportes</div>
    <div class="filter-row">
      <div class="fg">
        <label>Fecha Inicio</label>
        <input class="form-input" type="date" id="fechaInicio">
      </div>
      <div class="fg">
        <label>Fecha Fin</label>
        <input class="form-input" type="date" id="fechaFin">
      </div>
      <div class="fg">
        <label>Período Rápido</label>
        <select class="form-select" id="periodoRapido" onchange="setPeriodoRapido()">
          <option value="">Seleccionar...</option>
          <option value="hoy">Hoy</option>
          <option value="ayer">Ayer</option>
          <option value="semana">Esta Semana</option>
          <option value="mes">Este Mes</option>
          <option value="mes_anterior">Mes Anterior</option>
          <option value="trimestre">Este Trimestre</option>
          <option value="anio">Este Año</option>
        </select>
      </div>
      <div style="align-self:flex-end;display:flex;gap:8px">
        <button class="btn btn-secondary" onclick="limpiarFechas()">Limpiar</button>
        <button class="btn btn-primary" onclick="cargarReporte()">Generar Reporte</button>
        <button class="btn btn-secondary" onclick="descargarReporte()" id="btnDescargar" style="display:none">
          ⬇ Descargar PDF
        </button>
      </div>
    </div>
  </div>

  <!-- Estadísticas -->
  <div class="stats-grid-4">
    <div class="stat-card">
      <div class="stat-label">Total Ingresos</div>
      <div class="stat-value gold" id="rIngresos">—</div>
      <div class="stat-sub" id="rFechaLabel">hoy</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Vehículos Atendidos</div>
      <div class="stat-value gold" id="rVehiculos">—</div>
      <div class="stat-sub">entradas registradas</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Tiempo Promedio</div>
      <div class="stat-value gold" id="rTiempo">—</div>
      <div class="stat-sub">por visita</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Mejor Servicio</div>
      <div class="stat-value gold" id="rMejor" style="font-size:18px">—</div>
      <div class="stat-sub">más solicitado</div>
    </div>
  </div>

  <!-- Gráficas -->
  <div class="two-col">
    <div class="card" style="margin-bottom:0">
      <div class="card-title"><span class="card-title-icon">📊</span> Ingresos por Tipo de Servicio</div>
      <div id="barsChart"><div class="empty-state"><div class="es-icon">📊</div><p>Sin datos</p></div></div>
    </div>
    <div class="card" style="margin-bottom:0">
      <div class="card-title"><span class="card-title-icon">🕐</span> Vehículos por Franja Horaria</div>
      <div class="hora-grid" id="horaGrid"></div>
    </div>
  </div>

  <!-- Tabla detalle -->
  <div class="card" style="margin-top:18px">
    <div class="card-title">
      <span class="card-title-icon">📋</span> Detalle del Día
      <span style="margin-left:auto;font-size:11px;color:var(--text-muted)" id="totalDetalle"></span>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Hora entrada</th><th>Placa</th><th>Tipo</th>
            <th>Módulo</th><th>Duración</th><th>Monto</th><th>Estado</th>
          </tr>
        </thead>
        <tbody id="repTable">
          <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:28px">Selecciona una fecha y haz clic en Generar Reporte</td></tr>
        </tbody>
      </table>
    </div>
    <!-- Controles de paginación -->
    <div class="pagination" id="paginacionDetalle">
      <!-- Los botones de paginación se cargarán dinámicamente -->
    </div>
  </div>
</div>

<!-- ── REPORTE IMPRIMIBLE (oculto, solo visible al imprimir) ── -->
<div id="reporteImprimible">
  <div class="rp-header">
    <div class="rp-logo">PARKING<span>SURE</span></div>
    <div class="rp-fecha" id="rpFecha"></div>
    <div style="font-size:11px;color:#888;margin-top:2px">Generado el <?php echo date('d/m/Y H:i'); ?> — <?php echo htmlspecialchars($_SESSION['nombre'] ?? ''); ?></div>
  </div>

  <div class="rp-stats" id="rpStats"></div>

  <div class="rp-section">Ingresos por Tipo de Servicio</div>
  <table class="rp-table" id="rpTipoTable">
    <thead><tr><th>Tipo</th><th>Vehículos</th><th>Ingresos</th></tr></thead>
    <tbody></tbody>
  </table>

  <div class="rp-section">Detalle de Servicios</div>
  <table class="rp-table" id="rpDetalleTable">
    <thead><tr><th>Hora</th><th>Placa</th><th>Tipo</th><th>Módulo</th><th>Duración</th><th>Monto</th><th>Estado</th></tr></thead>
    <tbody></tbody>
  </table>

  <div class="rp-footer">ParkingSure · Sistema de Gestión de Parqueadero · Reporte generado automáticamente</div>
</div>

<div id="toast"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
  const API = '../../controllers/reportesapi.php';
  const fmt = n => new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0}).format(n);
  const fmtH = s => s ? new Date(s).toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'}) : '—';

  // Datos globales para el reporte imprimible
  let datosResumen = null;
  let datosTipo    = [];
  let datosDetalle = [];

  // Funciones obsoletas eliminadas - ahora usamos el sistema de rango de fechas

  function labelFecha(f) {
    const hoyLocal = new Date();
    const hoy = hoyLocal.getFullYear() + '-' + 
                 String(hoyLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(hoyLocal.getDate()).padStart(2, '0');
    const ayerLocal = new Date(hoyLocal.getTime() - 86400000);
    const ayer = ayerLocal.getFullYear() + '-' + 
                 String(ayerLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(ayerLocal.getDate()).padStart(2, '0');
    if (f === hoy)  return 'hoy';
    if (f === ayer) return 'ayer';
    // Formato dd/mm/yyyy
    const [y,m,d] = f.split('-');
    return `${d}/${m}/${y}`;
  }

  /* ════════════════════════════════════════
     FUNCIONES DE RANGO DE FECHAS
  ════════════════════════════════════════ */
  
  // Variable global para evitar bucles
let cargandoReporte = false;

function setPeriodoRapido() {
    console.log('🔄 setPeriodoRapido() iniciado');
    
    const select = document.getElementById('periodoRapido');
    if (!select) {
      console.log('❌ No se encontró el select periodoRapido');
      return;
    }
    
    const periodo = select.value;
    
    console.log('📋 Select encontrado:', select);
    console.log('📋 Valor seleccionado:', periodo);
    console.log('📋 Opciones disponibles:', select.options);
    
    if (!periodo) {
      console.log('⚠️ No se seleccionó período');
      return;
    }
    
    // Verificar si el evento se está disparando al cargar la página
    if (periodo === '' && select.selectedIndex === 0) {
      console.log('⚠️ Posible disparo al cargar página, ignorando');
      return;
    }
    
    // Protección contra bucles infinitos
    if (cargandoReporte) {
      console.log('⚠️ Ya está cargando un reporte, evitando bucle');
      return;
    }
    
    const hoy = new Date();
    let fechaInicio, fechaFin;
    
    switch(periodo) {
      case 'hoy':
        fechaInicio = fechaFin = hoy;
        break;
      case 'ayer':
        const ayer = new Date(hoy);
        ayer.setDate(ayer.getDate() - 1);
        fechaInicio = fechaFin = ayer;
        break;
      case 'semana':
        const inicioSemana = new Date(hoy);
        // Corregir: Si es domingo (0), restar 6 días para empezar el lunes
        // Si es lunes (1), restar 0 días
        const diaSemana = hoy.getDay();
        const diasARestar = diaSemana === 0 ? 6 : diaSemana - 1;
        inicioSemana.setDate(hoy.getDate() - diasARestar);
        
        // La semana debe terminar el domingo actual
        const finSemana = new Date(inicioSemana);
        finSemana.setDate(inicioSemana.getDate() + 6);
        
        fechaInicio = inicioSemana;
        fechaFin = finSemana;
        
        console.log('🗓️ Debug semana - día de semana:', diaSemana);
        console.log('🗓️ Debug semana - días a restar:', diasARestar);
        console.log('🗓️ Debug semana - fecha inicio:', inicioSemana);
        console.log('🗓️ Debug semana - fecha fin:', finSemana);
        console.log('🗓️ Debug semana - días de diferencia:', (finSemana - inicioSemana) / (1000 * 60 * 60 * 24));
        break;
      case 'mes':
        fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        fechaFin = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);
        break;
      case 'mes_anterior':
        fechaInicio = new Date(hoy.getFullYear(), hoy.getMonth() - 1, 1);
        fechaFin = new Date(hoy.getFullYear(), hoy.getMonth(), 0);
        break;
      case 'trimestre':
        const trimestreActual = Math.floor(hoy.getMonth() / 3);
        fechaInicio = new Date(hoy.getFullYear(), trimestreActual * 3, 1);
        fechaFin = new Date(hoy.getFullYear(), trimestreActual * 3 + 3, 0);
        break;
      case 'anio':
        fechaInicio = new Date(hoy.getFullYear(), 0, 1);
        fechaFin = new Date(hoy.getFullYear(), 11, 31);
        break;
      default:
        console.log('⚠️ Período no reconocido:', periodo);
        return;
    }
    
    // Usar formato local YYYY-MM-DD
    const formatoFecha = (fecha) => {
      return fecha.getFullYear() + '-' + 
             String(fecha.getMonth() + 1).padStart(2, '0') + '-' + 
             String(fecha.getDate()).padStart(2, '0');
    };
    
    const fechaInicioStr = formatoFecha(fechaInicio);
    const fechaFinStr = formatoFecha(fechaFin);
    
    console.log('📅 Fechas a establecer:', {inicio: fechaInicioStr, fin: fechaFinStr});
    
    // Establecer fechas en los inputs
    const inputInicio = document.getElementById('fechaInicio');
    const inputFin = document.getElementById('fechaFin');
    
    console.log('📝 Inputs encontrados:', {inicio: !!inputInicio, fin: !!inputFin});
    
    if (inputInicio) {
      inputInicio.value = formatoFecha(fechaInicio);
      console.log('📅 Input inicio establecido:', inputInicio.value);
    }
    
    if (inputFin) {
      inputFin.value = formatoFecha(fechaFin);
      console.log('📅 Input fin establecido:', inputFin.value);
    }
    
    // Limpiar el select para evitar bucles
    document.getElementById('periodoRapido').value = '';
    
    // Cargar el reporte con las nuevas fechas
    console.log('🔄 Llamando a cargarReporte() desde setPeriodoRapido()');
    setTimeout(() => {
      console.log('🔄 Ejecutando cargarReporte() con timeout');
      cargarReporte();
    }, 100);
  }
  
  function limpiarFechas() {
    document.getElementById('fechaInicio').value = '';
    document.getElementById('fechaFin').value = '';
    document.getElementById('periodoRapido').value = '';
  }

  function getLabelRango(fechaInicio, fechaFin) {
    const hoyLocal = new Date();
    const hoy = hoyLocal.getFullYear() + '-' + 
                 String(hoyLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(hoyLocal.getDate()).padStart(2, '0');
    const ayerLocal = new Date(hoyLocal.getTime() - 86400000);
    const ayer = ayerLocal.getFullYear() + '-' + 
                 String(ayerLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(ayerLocal.getDate()).padStart(2, '0');
    
    if (fechaInicio === fechaFin) {
      if (fechaInicio === hoy) return 'hoy';
      if (fechaInicio === ayer) return 'ayer';
      return `el ${formatDate(fechaInicio)}`;
    }
    
    return `del ${formatDate(fechaInicio)} al ${formatDate(fechaFin)}`;
  }
  
  function formatDate(fechaStr) {
    const [y,m,d] = fechaStr.split('-');
    return `${d}/${m}/${y}`;
  }

  /* ════════════════════════════════════════
     CARGAR REPORTE CON RANGO DE FECHAS
  ════════════════════════════════════════ */
  async function cargarReporte() {
    console.log('🔄 cargarReporte() iniciado');
    console.log('🔍 Estado cargandoReporte:', cargandoReporte);
    
    if (cargandoReporte) {
      console.log('⚠️ Ya está cargando, evitando duplicado');
      return; // Evitar bucles
    }
    
    console.log('✅ Iniciando carga de reporte');
    cargandoReporte = true;
    
    const fechaInicio = document.getElementById('fechaInicio').value;
    const fechaFin = document.getElementById('fechaFin').value;
    
    console.log('📅 Fechas seleccionadas:', {inicio: fechaInicio, fin: fechaFin});
    
    if (!fechaInicio || !fechaFin) {
      console.log('⚠️ Faltan fechas');
      toast('Por favor seleccione un rango de fechas');
      cargandoReporte = false;
      return;
    }
    
    if (fechaInicio > fechaFin) {
      console.log('⚠️ Fecha inicio mayor que fecha fin');
      toast('La fecha de inicio no puede ser mayor que la fecha fin');
      cargandoReporte = false;
      return;
    }
    
    const lbl = getLabelRango(fechaInicio, fechaFin);
    console.log('📋 Etiqueta del rango:', lbl);
    document.getElementById('subtitulo').textContent = `Datos ${lbl}`;
    document.getElementById('rFechaLabel').textContent = lbl;

    // Mostrar loading en tarjetas
    ['rIngresos','rVehiculos','rTiempo','rMejor'].forEach(id => {
      document.getElementById(id).textContent = '…';
    });

    try {
      console.log('Cargando reporte para rango:', fechaInicio, 'a', fechaFin);
      
      // Validar fechas antes de hacer la petición
      if (!fechaInicio || !fechaFin) {
        throw new Error('Las fechas de inicio y fin son requeridas');
      }
      
      const urls = [
        `${API}?action=getResumen&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`,
        `${API}?action=getPorTipo&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`,
        `${API}?action=getPorHora&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`,
        `${API}?action=getDetalle&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`
      ];
      
      console.log('URLs a consultar:', urls);
      
      const responses = await Promise.all(urls.map(async (url, index) => {
        try {
          const response = await fetch(url);
          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }
          const data = await response.json();
          console.log(`Respuesta ${index + 1}:`, data);
          return data;
        } catch (error) {
          console.error(`Error en petición ${index + 1} (${url}):`, error);
          return { success: false, message: error.message, data: null };
        }
      }));

      const [resumen, porTipo, porHora, detalle] = responses;

      console.log('Respuestas del API:', { resumen, porTipo, porHora, detalle });

      // Verificar si todas las respuestas fueron exitosas
      const hasErrors = responses.some(r => !r.success);
      if (hasErrors) {
        console.warn('Algunas respuestas tuvieron errores:', responses);
        toast('⚠️ Algunos datos no pudieron cargarse');
      }

      renderResumen(resumen);
      renderBarras(porTipo);
      renderHoras(porHora);
      renderDetalle(detalle);

      // Guardar para reporte imprimible
      datosResumen = resumen.success ? resumen.data : null;
      datosTipo    = porTipo.success  ? porTipo.data  : [];
      datosDetalle = detalle.success  ? detalle.data  : [];

      console.log('Datos guardados para PDF:', {
        datosResumen,
        datosTipo,
        datosDetalle
      });
      
      // Resetear bandera de carga
      console.log('✅ Reporte cargado exitosamente, reseteando bandera');
      cargandoReporte = false;
      
      // Mostrar botón de descarga después de cargar datos
      if (datosResumen || datosTipo.length > 0 || datosDetalle.length > 0) {
        document.getElementById('btnDescargar').style.display = 'inline-block';
      }

    } catch (error) {
      console.error('Error cargando reporte:', error);
      toast('❌ Error al cargar el reporte');
      cargandoReporte = false;
      toast(`❌ Error: ${error.message}`);
    }
  }

  /* ── Resumen ── */
  function renderResumen(result) {
    if (!result.success) {
      console.error('getResumen error:', result.message);
      ['rIngresos','rVehiculos','rTiempo','rMejor'].forEach(id => {
        document.getElementById(id).textContent = '0';
      });
      return;
    }
    const d = result.data;
    document.getElementById('rIngresos').textContent = fmt(d.ingresos   || 0);
    document.getElementById('rVehiculos').textContent = d.vehiculos      || 0;
    document.getElementById('rTiempo').textContent    = d.tiempo_prom    || '—';
    document.getElementById('rMejor').textContent     = d.mejor_servicio || '—';
  }

  /* ── Barras ── */
  function renderBarras(result) {
    const cont = document.getElementById('barsChart');
    if (!result.success || !result.data.length) {
      cont.innerHTML = '<div class="empty-state"><div class="es-icon">📊</div><p>Sin entradas registradas</p></div>';
      return;
    }
    cont.innerHTML = '';
    result.data.forEach(r => {
      cont.innerHTML += `
        <div class="bar-row">
          <div class="bar-head">
            <span>${r.tipo} <span class="bar-sub">(${r.cantidad} veh.)</span></span>
            <span style="color:var(--gold);font-weight:700">${fmt(r.ingresos)}</span>
          </div>
          <div class="bar-track"><div class="bar-fill" style="width:${r.pct}%"></div></div>
        </div>`;
    });
  }

  /* ── Franjas horarias ── */
  function renderHoras(result) {
    const grid = document.getElementById('horaGrid');
    grid.innerHTML = '';
    if (!result.success) { grid.innerHTML = '<p style="color:var(--text-muted);font-size:13px;padding:10px">Sin datos</p>'; return; }
    result.data.forEach(f => {
      const cell = document.createElement('div');
      cell.className = `hora-cell ${f.cantidad > 0 ? 'activa' : ''}`;
      cell.innerHTML = `<div class="hora-num">${f.label}</div><div class="hora-qty">${f.cantidad}</div>`;
      grid.appendChild(cell);
    });
  }

  /* ── Variables globales para paginación ── */
  let paginaActualDetalle = 1;
  let registrosPorPagina = 10;
  let todosLosDetalles = [];
  let totalDetallesPages = 1;
  let totalDetallesItems = 0;

  /* ── Tabla detalle ── */
  function renderDetalle(result) {
    const tb = document.getElementById('repTable');
    tb.innerHTML = '';
    
    if (!result.success || !result.data.length) {
      const fechaInicio = document.getElementById('fechaInicio').value;
      const fechaFin = document.getElementById('fechaFin').value;
      const labelRango = getLabelRango(fechaInicio, fechaFin);
      tb.innerHTML = `<tr><td colspan="8"><div class="empty-state"><div class="es-icon">📋</div><p>Sin registros para ${labelRango}</p></div></td></tr>`;
      document.getElementById('totalDetalle').textContent = '';
      document.getElementById('paginacionDetalle').innerHTML = '';
      return;
    }
    
    // Guardar todos los datos para paginación
    todosLosDetalles = result.data;
    paginaActualDetalle = 1; // Resetear a primera página
    
    // Actualizar variables de paginación
    totalDetallesItems = result.data.length;
    totalDetallesPages = Math.max(1, Math.ceil(totalDetallesItems / registrosPorPagina));
    
    // Actualizar contador total
    document.getElementById('totalDetalle').textContent = `${result.data.length} registros`;
    
    // Renderizar primera página
    renderPaginaDetalle();
    renderPagination('paginacionDetalle', paginaActualDetalle, totalDetallesPages, cambiarPaginaDetalle);
  }

  /* ── Renderizar página de detalles ── */
  function renderPaginaDetalle() {
    const tb = document.getElementById('repTable');
    tb.innerHTML = '';
    
    const inicio = (paginaActualDetalle - 1) * registrosPorPagina;
    const fin = Math.min(inicio + registrosPorPagina, todosLosDetalles.length);
    const paginaDatos = todosLosDetalles.slice(inicio, fin);
    
    paginaDatos.forEach(r => {
      const tr = document.createElement('tr');
      let badge = '';
      if (!r.fecha_hora_salida)        badge = '<span class="badge-act">En curso</span>';
      else if (r.estado_pago==='PAGADA') badge = '<span class="badge-paid">Pagado</span>';
      else                               badge = '<span class="badge-pend">Pendiente</span>';

      tr.innerHTML = `
        <td style="color:var(--text-muted);font-size:12px">${fmtH(r.fecha_hora_entrada)}</td>
        <td><strong style="font-family:monospace">${r.placa}</strong></td>
        <td style="font-size:12px">${r.tipo}</td>
        <td><span style="font-family:monospace;font-size:11px;background:var(--surface-3);padding:2px 7px;border-radius:4px">${r.modulo}</span></td>
        <td style="color:var(--text-secondary);font-size:12px">${r.duracion}</td>
        <td><strong style="color:${r.monto_total?'var(--gold)':'var(--text-muted)'}">${r.monto_total?fmt(r.monto_total):'—'}</strong></td>
        <td>${badge}</td>`;
      tb.appendChild(tr);
    });
  }

  /* ── Cambiar página de detalles ── */
  function cambiarPaginaDetalle(nuevaPagina) {
    if (nuevaPagina < 1 || nuevaPagina > totalDetallesPages) {
      return;
    }
    
    paginaActualDetalle = nuevaPagina;
    renderPaginaDetalle();
    renderPagination('paginacionDetalle', paginaActualDetalle, totalDetallesPages, cambiarPaginaDetalle);
    
    // Scroll al inicio de la tabla
    document.getElementById('repTable').scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  // Función reutilizable para renderizar paginación
  function renderPagination(containerId, currentPage, totalPages, loadFunction) {
    const container = document.getElementById(containerId);
    container.innerHTML = '';

    if (totalPages <= 1) return;

    // Botón anterior
    const prevBtn = document.createElement('button');
    prevBtn.className = 'pagination-btn';
    prevBtn.innerHTML = '←';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => loadFunction(currentPage - 1);
    container.appendChild(prevBtn);

    // Lógica de páginas a mostrar
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }

    // Primera página si no está visible
    if (startPage > 1) {
      const firstBtn = document.createElement('button');
      firstBtn.className = 'pagination-btn';
      firstBtn.textContent = '1';
      firstBtn.onclick = () => loadFunction(1);
      container.appendChild(firstBtn);
      
      if (startPage > 2) {
        const dots = document.createElement('span');
        dots.className = 'pagination-info';
        dots.textContent = '...';
        container.appendChild(dots);
      }
    }

    // Páginas numeradas
    for (let i = startPage; i <= endPage; i++) {
      const pageBtn = document.createElement('button');
      pageBtn.className = 'pagination-btn';
      if (i === currentPage) {
        pageBtn.classList.add('active');
      }
      pageBtn.textContent = i;
      pageBtn.onclick = () => loadFunction(i);
      container.appendChild(pageBtn);
    }

    // Última página si no está visible
    if (endPage < totalPages) {
      if (endPage < totalPages - 1) {
        const dots = document.createElement('span');
        dots.className = 'pagination-info';
        dots.textContent = '...';
        container.appendChild(dots);
      }
      
      const lastBtn = document.createElement('button');
      lastBtn.className = 'pagination-btn';
      lastBtn.textContent = totalPages;
      lastBtn.onclick = () => loadFunction(totalPages);
      container.appendChild(lastBtn);
    }

    // Botón siguiente
    const nextBtn = document.createElement('button');
    nextBtn.className = 'pagination-btn';
    nextBtn.innerHTML = '→';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => loadFunction(currentPage + 1);
    container.appendChild(nextBtn);

    // Información de total
    const info = document.createElement('span');
    info.className = 'pagination-info';
    info.textContent = `${totalDetallesItems} registros`;
    container.appendChild(info);
  }

  /* ════════════════════════════════════════
     GENERAR REPORTE IMPRIMIBLE / PDF (ACTUALIZADO)
  ════════════════════════════════════════ */
  function descargarReporte() {
    console.log('Iniciando generación de PDF descargable...');
    
    if (!datosResumen || datosDetalle.length === 0) {
      toast('⚠️ No hay datos para generar el PDF');
      return;
    }

    generarPDFDescargable(datosResumen, datosTipo, datosDetalle);
  }

  function generarPDFDescargable(resumen, tipos, detalles) {
    toast('🔄 Generando PDF profesional...');
    
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    let yPos = margin;
    
    const colors = {
      gold: [232, 184, 75],
      black: [20, 20, 20],
      white: [255, 255, 255],
      gray: [100, 100, 100],
      lightGray: [245, 245, 245],
      border: [220, 220, 220]
    };
    
    const drawHeader = (doc) => {
      doc.setFillColor(...colors.black);
      doc.rect(0, 0, pageWidth, 5, 'F');
      doc.setFillColor(...colors.gold);
      doc.rect(0, 5, pageWidth, 1.5, 'F');
      
      doc.setTextColor(...colors.black);
      doc.setFontSize(22);
      doc.setFont('helvetica', 'bold');
      doc.text('PARKING', margin, 20);
      
      doc.setTextColor(...colors.gold);
      doc.text('SURE', margin + 37, 20);
      
      doc.setTextColor(...colors.gray);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'bold');
      doc.text('REPORTE DE OPERACIONES', pageWidth - margin, 18, { align: 'right' });
      
      const fechaInicio = document.getElementById('fechaInicio').value;
      const fechaFin = document.getElementById('fechaFin').value;
      const labelRango = getLabelRango(fechaInicio, fechaFin);
      
      doc.setFontSize(9);
      doc.setFont('helvetica', 'normal');
      doc.text(`Período: ${labelRango.charAt(0).toUpperCase() + labelRango.slice(1)}`, pageWidth - margin, 23, { align: 'right' });
    };

    const drawFooter = (doc, pageNumber, totalPages) => {
      const footerY = pageHeight - 15;
      
      doc.setDrawColor(...colors.border);
      doc.setLineWidth(0.5);
      doc.line(margin, footerY, pageWidth - margin, footerY);
      
      doc.setTextColor(...colors.gray);
      doc.setFontSize(8);
      doc.setFont('helvetica', 'italic');
      doc.text('ParkingSure - Sistema de Gestión de Parqueadero', margin, footerY + 5);
      
      const fechaActual = new Date().toLocaleString('es-CO');
      doc.text(`Generado el: ${fechaActual}`, margin, footerY + 10);
      
      if (totalPages) {
        doc.text(`Página ${pageNumber} de ${totalPages}`, pageWidth - margin, footerY + 5, { align: 'right' });
      }
    };
    
    drawHeader(doc);
    yPos = 35;
    
    doc.setTextColor(...colors.black);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    doc.text('RESUMEN EJECUTIVO', margin, yPos);
    
    doc.setDrawColor(...colors.gold);
    doc.setLineWidth(0.8);
    doc.line(margin, yPos + 2, margin + 55, yPos + 2);
    
    yPos += 12;
    
    const boxWidth = (pageWidth - 2 * margin - 15) / 4;
    const boxHeight = 22;
    
    const stats = [
      { label: 'INGRESOS', value: fmt(resumen.ingresos || 0) },
      { label: 'VEHÍCULOS', value: resumen.vehiculos || 0 },
      { label: 'TIEMPO PROM.', value: resumen.tiempo_prom || '—' },
      { label: 'MEJOR SERVICIO', value: resumen.mejor_servicio || '—' }
    ];
    
    stats.forEach((stat, i) => {
      const x = margin + i * (boxWidth + 5);
      
      doc.setFillColor(...colors.white);
      doc.setDrawColor(...colors.border);
      doc.setLineWidth(0.3);
      doc.roundedRect(x, yPos, boxWidth, boxHeight, 2, 2, 'FD');
      
      doc.setFillColor(...colors.gold);
      doc.roundedRect(x, yPos, boxWidth, 2, 2, 2, 'F');
      doc.rect(x, yPos + 1, boxWidth, 1, 'F');
      
      doc.setTextColor(...colors.black);
      doc.setFontSize(12);
      doc.setFont('helvetica', 'bold');
      
      let textWidth = doc.getTextWidth(stat.value.toString());
      if (textWidth > boxWidth - 4) {
        doc.setFontSize(9);
      }
      doc.text(stat.value.toString(), x + boxWidth / 2, yPos + 12, { align: 'center' });
      
      doc.setTextColor(...colors.gray);
      doc.setFontSize(7);
      doc.setFont('helvetica', 'normal');
      doc.text(stat.label, x + boxWidth / 2, yPos + 18, { align: 'center' });
    });
    
    yPos += boxHeight + 15;
    
    if (tipos && tipos.length > 0) {
      doc.setTextColor(...colors.black);
      doc.setFontSize(14);
      doc.setFont('helvetica', 'bold');
      doc.text('INGRESOS POR TIPO DE SERVICIO', margin, yPos);
      
      doc.setDrawColor(...colors.gold);
      doc.setLineWidth(0.8);
      doc.line(margin, yPos + 2, margin + 85, yPos + 2);
      
      yPos += 8;
      
      const tableDataTipos = tipos.map(t => [
        t.tipo,
        t.cantidad.toString(),
        fmt(t.ingresos),
        `${t.pct}%`
      ]);
      
      doc.autoTable({
        startY: yPos,
        head: [['Tipo de Servicio', 'Vehículos', 'Ingresos', 'Porcentaje']],
        body: tableDataTipos,
        theme: 'plain',
        headStyles: { fillColor: colors.black, textColor: colors.white, fontStyle: 'bold', fontSize: 9 },
        bodyStyles: { fontSize: 9, textColor: colors.black },
        alternateRowStyles: { fillColor: colors.lightGray },
        margin: { left: margin, right: margin },
        styles: { cellPadding: 3 },
        columnStyles: {
          1: { halign: 'center' },
          2: { halign: 'right' },
          3: { halign: 'center' }
        }
      });
      
      yPos = doc.lastAutoTable.finalY + 15;
    }
    
    if (detalles && detalles.length > 0) {
      if (yPos > pageHeight - 40) {
        doc.addPage();
        drawHeader(doc);
        yPos = 35;
      }
      
      doc.setTextColor(...colors.black);
      doc.setFontSize(14);
      doc.setFont('helvetica', 'bold');
      doc.text('DETALLE DE TRANSACCIONES', margin, yPos);
      
      doc.setDrawColor(...colors.gold);
      doc.setLineWidth(0.8);
      doc.line(margin, yPos + 2, margin + 75, yPos + 2);
      
      yPos += 8;
      
      const tableDataDetalle = detalles.map(d => {
        let estado = 'Pendiente';
        if (!d.fecha_hora_salida) estado = 'En curso';
        else if (d.estado_pago === 'PAGADA') estado = 'Pagado';
        
        return [
          fmtH(d.fecha_hora_entrada),
          d.placa,
          d.tipo,
          d.modulo,
          d.duracion || '—',
          d.monto_total ? fmt(d.monto_total) : '—',
          estado
        ];
      });
      
      doc.autoTable({
        startY: yPos,
        head: [['Hora', 'Placa', 'Servicio', 'Módulo', 'Duración', 'Monto', 'Estado']],
        body: tableDataDetalle,
        theme: 'grid',
        headStyles: { fillColor: colors.black, textColor: colors.white, fontStyle: 'bold', fontSize: 8 },
        bodyStyles: { fontSize: 8, textColor: colors.black },
        alternateRowStyles: { fillColor: colors.lightGray },
        margin: { left: margin, right: margin, top: 35, bottom: 25 },
        styles: { cellPadding: 2.5, lineColor: colors.border, lineWidth: 0.1 },
        columnStyles: {
          0: { halign: 'center' },
          1: { fontStyle: 'bold', halign: 'center' },
          4: { halign: 'center' },
          5: { halign: 'right', fontStyle: 'bold' },
          6: { halign: 'center' }
        },
        didDrawPage: function(data) {
          if (data.pageNumber > 1) {
            drawHeader(doc);
          }
        }
      });
    }
    
    const totalPages = doc.internal.getNumberOfPages();
    for (let i = 1; i <= totalPages; i++) {
      doc.setPage(i);
      drawFooter(doc, i, totalPages);
    }
    
    const fechaInicio = document.getElementById('fechaInicio').value || new Date().toISOString().split('T')[0];
    const fechaFin = document.getElementById('fechaFin').value || new Date().toISOString().split('T')[0];
    const fileName = `Reporte_ParkingSure_${fechaInicio}_al_${fechaFin}.pdf`;
    
    doc.save(fileName);
    toast('✅ PDF descargado correctamente');
  }

  /* ── Toast ── */
  function toast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'), 3200);
  }

  /* ── Hamburger ── */
  const hamburgerBtn = document.getElementById('hamburger-btn');
  const navLinks     = document.getElementById('nav-links');
  
  // Asegurarse de que los elementos existen antes de agregar eventos
  if (hamburgerBtn && navLinks) {
    console.log('🍔 Inicializando menú hamburguesa');
    
    hamburgerBtn.addEventListener('click', (e) => {
      e.preventDefault();
      console.log('🍔 Click en hamburguesa, toggle menú');
      navLinks.classList.toggle('active');
      hamburgerBtn.classList.toggle('active');
    });
    
    document.addEventListener('click', (e) => {
      if (!hamburgerBtn.contains(e.target) && !navLinks.contains(e.target)) {
        console.log('🍔 Click fuera, cerrando menú');
        navLinks.classList.remove('active');
        hamburgerBtn.classList.remove('active');
      }
    });
    
    // Prevenir que el menú se cierre al hacer click dentro
    navLinks.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  } else {
    console.error('🍔 Error: No se encontraron elementos del menú hamburguesa');
  }

  // ── Inicialización ── */
  document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM cargado, iniciando configuración inicial');
    
    // Sistema automático de detección de fecha local (sin ajustes manuales)
    const hoyLocal = new Date();
    const hoy = hoyLocal.getFullYear() + '-' + 
                 String(hoyLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(hoyLocal.getDate()).padStart(2, '0');
    
    console.log('🌍 Zona horaria detectada automáticamente');
    console.log('🕐 Fecha local actual:', hoyLocal.toString());
    console.log('📅 Fecha formateada (local):', hoy);
    console.log('⏰ Offset zona horaria:', hoyLocal.getTimezoneOffset(), 'minutos');
    
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');
    
    if (fechaInicio) {
      fechaInicio.value = hoy;
    }
    
    if (fechaFin) {
      fechaFin.value = hoy;
    }
    
    console.log('🌍 Zona horaria detectada automáticamente');
    console.log('🕐 Fecha local actual:', hoyLocal.toString());
    console.log('📅 Fecha formateada (local):', hoy);
    console.log('⏰ Offset zona horaria:', hoyLocal.getTimezoneOffset(), 'minutos');
    
    // Establecer bandera para evitar doble carga
    cargandoReporte = true;
    
    // Cargar reporte inicial
    setTimeout(() => {
      console.log('🔄 Carga inicial programada');
      cargarReporte();
      
      // Resetear bandera después de la carga inicial
      setTimeout(() => {
        console.log('🔄 Resetear bandera de carga inicial');
        cargandoReporte = false;
      }, 1000);
    }, 500);
  });
</script>
</body>
</html>
