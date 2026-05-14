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
    <a class="btn-logout" href="../../index.php" style="background:var(--surface-2);color:var(--text-secondary);border-color:var(--border-md);margin-right:8px;">Ver Web</a>
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
     GENERAR REPORTE IMPRIMIBLE / PDF
  ════════════════════════════════════════ */
  function descargarReporte() {
    console.log('Iniciando generación de PDF descargable...');
    
    // Capturar el contenido visible de la vista
    const contenidoVisible = capturarContenidoVista();
    
    if (!contenidoVisible) {
      toast('⚠️ No hay contenido visible para generar el PDF');
      return;
    }

    // Generar PDF descargable con jsPDF
    generarPDFDescargable(contenidoVisible);
  }

  function generarPDFDescargable(contenido) {
    toast('🔄 Generando PDF profesional...');
    
    // Crear instancia de jsPDF
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    // Configuración de página
    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();
    const margin = 15;
    let yPosition = margin;
    
    // Colores blanco y negro formal con toques corporativos
    const colors = {
      // Solo mantenemos el dorado para elementos decorativos mínimos
      gold: [232, 184, 75],        // #e8b84b - Solo para PARKINGSURE y líneas decorativas
      
      // Esquema blanco y negro formal
      black: [0, 0, 0],            // Negro puro para texto
      white: [255, 255, 255],      // Blanco para fondos
      lightGray: [240, 240, 240],  // Gris claro para fondos sutiles
      mediumGray: [150, 150, 150], // Gris medio para bordes
      darkGray: [100, 100, 100],   // Gris oscuro para texto secundario
      
      // Texto formal
      text: [0, 0, 0],             // Negro para texto principal
      textSecondary: [100, 100, 100], // Gris para texto secundario
      textMuted: [150, 150, 150]   // Gris claro para texto muted
    };
    
    // Función para agregar texto con salto de página automático
    function addText(text, fontSize = 12, fontStyle = 'normal', color = colors.text) {
      doc.setTextColor(...color);
      doc.setFontSize(fontSize);
      doc.setFont('helvetica', fontStyle);
      
      const lines = doc.splitTextToSize(text, pageWidth - 2 * margin);
      lines.forEach(line => {
        if (yPosition > pageHeight - margin) {
          doc.addPage();
          yPosition = margin;
          // Agregar encabezado en nueva página
          addHeader();
        }
        doc.text(line, margin, yPosition);
        yPosition += fontSize * 0.4;
      });
      return yPosition;
    }
    
    // Función para agregar encabezado en cada página
    function addHeader() {
      // Línea decorativa superior dorada (único toque corporativo)
      doc.setFillColor(...colors.gold);
      doc.rect(margin, 5, pageWidth - 2 * margin, 1.5, 'F');
      
      // Logo PARKINGSURE con toque dorado mínimo
      doc.setTextColor(...colors.black);
      doc.setFontSize(18);
      doc.setFont('helvetica', 'bold');
      doc.text('PARKING', margin, 15);
      
      doc.setTextColor(...colors.gold);
      doc.text('SURE', margin + 29, 15);
      
      // Línea de separación negra formal
      doc.setDrawColor(...colors.black);
      doc.setLineWidth(0.5);
      doc.line(margin, 20, pageWidth - margin, 20);
      
      yPosition = 28;
    }
    
    // Encabezado principal
    addHeader();
    
    // Información del reporte en formato formal
    doc.setTextColor(...colors.darkGray);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.text('SISTEMA DE GESTIÓN DE PARQUEADERO', margin, yPosition);
    yPosition += 8;
    
    const fechaActual = new Date().toLocaleDateString('es-CO', { 
      day: '2-digit', month: 'long', year: 'numeric' 
    });
    const horaActual = new Date().toLocaleTimeString('es-CO', { 
      hour: '2-digit', minute: '2-digit' 
    });
    doc.text(`Reporte generado: ${fechaActual} - ${horaActual}hs`, margin, yPosition);
    yPosition += 12;
    
    // Título del reporte - primero el recuadro, luego el texto
    const tituloY = yPosition;
    
    // Preparar el texto y calcular altura necesaria
    const tituloTexto = contenido.subtitulo.toUpperCase();
    const lines = doc.splitTextToSize(tituloTexto, pageWidth - 2 * margin - 10);
    const tituloHeight = Math.max(15, lines.length * 6 + 8);
    
    // Dibujar el recuadro primero
    doc.setFillColor(...colors.white);
    doc.rect(margin, tituloY - 3, pageWidth - 2 * margin, tituloHeight, 'F');
    doc.setDrawColor(...colors.black);
    doc.setLineWidth(0.5);
    doc.rect(margin, tituloY - 3, pageWidth - 2 * margin, tituloHeight);
    
    // Escribir el texto encima del recuadro
    doc.setTextColor(...colors.black);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    
    lines.forEach((line, index) => {
      doc.text(line, margin + 5, tituloY + 8 + (index * 6));
    });
    
    // Actualizar posición Y después del título
    yPosition = tituloY + tituloHeight + 8;
    
    // Sección de estadísticas con diseño mejorado
    yPosition += 5;
    doc.setTextColor(...colors.text);
    doc.setFontSize(14);
    doc.setFont('helvetica', 'bold');
    yPosition = addText('RESUMEN EJECUTIVO', 14, 'bold', colors.text);
    yPosition += 5;
    
    // Estadísticas en tarjetas formales blanco y negro
    const stats = [
      { label: 'Total Ingresos', value: contenido.estadisticas.ingresos },
      { label: 'Vehículos Atendidos', value: contenido.estadisticas.vehiculos },
      { label: 'Tiempo Promedio', value: contenido.estadisticas.tiempo },
      { label: 'Mejor Servicio', value: contenido.estadisticas.mejor }
    ];
    
    const boxWidth = (pageWidth - 2 * margin) / 4 - 3;
    const boxHeight = 26;
    const spacing = 3;
    
    stats.forEach((stat, index) => {
      const x = margin + index * (boxWidth + spacing);
      
      // Fondo blanco
      doc.setFillColor(...colors.white);
      doc.rect(x, yPosition, boxWidth, boxHeight, 'F');
      
      // Borde negro formal
      doc.setDrawColor(...colors.black);
      doc.setLineWidth(0.5);
      doc.rect(x, yPosition, boxWidth, boxHeight);
      
      // Borde superior dorado (único toque corporativo)
      doc.setFillColor(...colors.gold);
      doc.rect(x, yPosition, boxWidth, 1.5, 'F');
      
      // Valor principal en negro
      doc.setTextColor(...colors.black);
      doc.setFontSize(10);
      doc.setFont('helvetica', 'bold');
      doc.text(stat.value, x + boxWidth/2, yPosition + 12, { align: 'center' });
      
      // Línea separadora gris
      doc.setDrawColor(...colors.mediumGray);
      doc.setLineWidth(0.3);
      doc.line(x + 4, yPosition + 16, x + boxWidth - 4, yPosition + 16);
      
      // Etiqueta en gris
      doc.setTextColor(...colors.darkGray);
      doc.setFontSize(6);
      doc.setFont('helvetica', 'normal');
      doc.text(stat.label.toUpperCase(), x + boxWidth/2, yPosition + 22, { align: 'center' });
    });
    
    yPosition += boxHeight + 15;
    
    // Sección de análisis por tipo de servicio
    if (contenido.datosBarras.length > 0) {
      yPosition += 8;
      doc.setTextColor(...colors.black);
      doc.setFontSize(12);
      doc.setFont('helvetica', 'bold');
      yPosition = addText('ANÁLISIS POR TIPO DE SERVICIO', 12, 'bold', colors.black);
      yPosition += 6;
      
      // Fondo blanco para la sección
      const sectionHeight = contenido.datosBarras.length * 6 + 10;
      doc.setFillColor(...colors.white);
      doc.rect(margin, yPosition - 2, pageWidth - 2 * margin, sectionHeight, 'F');
      doc.setDrawColor(...colors.black);
      doc.setLineWidth(0.5);
      doc.rect(margin, yPosition - 2, pageWidth - 2 * margin, sectionHeight);
      
      contenido.datosBarras.forEach((dato, index) => {
        doc.setTextColor(...colors.black);
        doc.setFontSize(9);
        doc.setFont('helvetica', 'normal');
        yPosition = addText(`• ${dato}`, 9, 'normal', colors.black);
        yPosition += 2;
      });
      yPosition += 12;
    }
    
    // Tabla de transacciones formal blanco y negro
    if (contenido.filasDetalle.length > 0) {
      yPosition += 8;
      doc.setTextColor(...colors.black);
      doc.setFontSize(12);
      doc.setFont('helvetica', 'bold');
      yPosition = addText('DETALLE DE TRANSACCIONES', 12, 'bold', colors.black);
      yPosition += 6;
      
      // Encabezados de tabla formales
      const headers = ['HORA', 'PLACA', 'SERVICIO', 'MÓDULO', 'DURACIÓN', 'MONTO', 'ESTADO'];
      const colWidth = (pageWidth - 2 * margin) / headers.length;
      
      // Fondo de encabezados blanco con borde negro
      doc.setFillColor(...colors.white);
      doc.rect(margin, yPosition, pageWidth - 2 * margin, 8, 'F');
      doc.setDrawColor(...colors.black);
      doc.setLineWidth(0.5);
      doc.rect(margin, yPosition, pageWidth - 2 * margin, 8);
      
      // Encabezados con texto negro
      headers.forEach((header, index) => {
        const x = margin + index * colWidth;
        doc.setTextColor(...colors.black);
        doc.setFontSize(7);
        doc.setFont('helvetica', 'bold');
        doc.text(header, x + colWidth/2, yPosition + 5, { align: 'center' });
      });
      yPosition += 8;
      
      // Filas de datos con diseño formal
      contenido.filasDetalle.forEach((fila, rowIndex) => {
        if (yPosition > pageHeight - margin - 25) {
          doc.addPage();
          yPosition = margin;
          addHeader();
          
          // Repetir encabezados en nueva página
          doc.setFillColor(...colors.white);
          doc.rect(margin, yPosition, pageWidth - 2 * margin, 8, 'F');
          doc.setDrawColor(...colors.black);
          doc.setLineWidth(0.5);
          doc.rect(margin, yPosition, pageWidth - 2 * margin, 8);
          headers.forEach((header, index) => {
            const x = margin + index * colWidth;
            doc.setTextColor(...colors.black);
            doc.setFontSize(7);
            doc.setFont('helvetica', 'bold');
            doc.text(header, x + colWidth/2, yPosition + 5, { align: 'center' });
          });
          yPosition += 8;
        }
        
        // Fondo blanco para todas las filas
        doc.setFillColor(...colors.white);
        doc.rect(margin, yPosition, pageWidth - 2 * margin, 6, 'F');
        
        // Borde negro formal
        doc.setDrawColor(...colors.black);
        doc.setLineWidth(0.3);
        doc.rect(margin, yPosition, pageWidth - 2 * margin, 6);
        
        // Datos de la fila en negro
        fila.forEach((dato, index) => {
          const x = margin + index * colWidth;
          
          // Todo el texto en negro
          doc.setTextColor(...colors.black);
          doc.setFontSize(6);
          doc.setFont('helvetica', 'normal');
          
          // Alineación específica por columna
          let align = 'left';
          let xPos = x + 2;
          
          if (index === 0) { // Hora - centrado
            align = 'center';
            xPos = x + colWidth/2;
          } else if (index === 5) { // Monto - derecha
            align = 'right';
            xPos = x + colWidth - 2;
          } else if (index === 6) { // Estado - centrado
            align = 'center';
            xPos = x + colWidth/2;
          }
          
          doc.text(dato.toString(), xPos, yPosition + 4, { align: align });
        });
        yPosition += 6;
      });
      yPosition += 10;
    }
    
    // Footer formal blanco y negro
    yPosition = pageHeight - 18;
    
    // Línea dorada del footer (único toque corporativo)
    doc.setDrawColor(...colors.gold);
    doc.setLineWidth(0.8);
    doc.line(margin, yPosition, pageWidth - margin, yPosition);
    yPosition += 6;
    
    // Información del footer en negro y gris
    doc.setTextColor(...colors.darkGray);
    doc.setFontSize(7);
    doc.setFont('helvetica', 'italic');
    doc.text('PARKINGSURE - SISTEMA DE GESTIÓN DE PARQUEADERO', pageWidth/2, yPosition, { align: 'center' });
    yPosition += 4;
    
    doc.setTextColor(...colors.mediumGray);
    doc.setFontSize(6);
    doc.setFont('helvetica', 'normal');
    doc.text('Reporte generado automáticamente - Documento confidencial', pageWidth/2, yPosition, { align: 'center' });
    
    // Número de página en gris
    const pageNumber = doc.internal.getCurrentPageInfo().pageNumber;
    doc.setTextColor(...colors.mediumGray);
    doc.setFontSize(6);
    doc.text(`Pág. ${pageNumber}`, pageWidth - margin - 5, pageHeight - 8);
    
    // Generar nombre de archivo
    const hoyLocal = new Date();
    const fechaInicio = document.getElementById('fechaInicio').value || hoyLocal.getFullYear() + '-' + 
                 String(hoyLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(hoyLocal.getDate()).padStart(2, '0');
    const fechaFin = document.getElementById('fechaFin').value || hoyLocal.getFullYear() + '-' + 
                 String(hoyLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                 String(hoyLocal.getDate()).padStart(2, '0');
    const fileName = `reporte_parkingsure_${fechaInicio}_${fechaFin}.pdf`;
    
    // Descargar el PDF
    doc.save(fileName);
    
    toast('✅ PDF descargado correctamente');
  }

  function capturarContenidoVista() {
    // Capturar las estadísticas principales
    const estadisticas = {
      ingresos: document.getElementById('rIngresos')?.textContent || '0',
      vehiculos: document.getElementById('rVehiculos')?.textContent || '0',
      tiempo: document.getElementById('rTiempo')?.textContent || '—',
      mejor: document.getElementById('rMejor')?.textContent || '—'
    };

    // Capturar el subtítulo con el rango de fechas
    const subtitulo = document.getElementById('subtitulo')?.textContent || 'Reporte';
    
    // Capturar la tabla de detalle si existe
    const tablaDetalle = document.getElementById('repTable');
    let filasDetalle = [];
    
    if (tablaDetalle) {
      const filas = tablaDetalle.querySelectorAll('tr');
      filas.forEach(fila => {
        const celdas = fila.querySelectorAll('td');
        if (celdas.length > 0) {
          const datosFila = [];
          celdas.forEach(celda => {
            datosFila.push(celda.textContent.trim());
          });
          filasDetalle.push(datosFila);
        }
      });
    }

    // Capturar las barras de gráficos si existen
    const barrasChart = document.getElementById('barsChart');
    let datosBarras = [];
    
    if (barrasChart) {
      const barras = barrasChart.querySelectorAll('.bar-row');
      barras.forEach(barra => {
        const texto = barra.textContent?.trim();
        if (texto) {
          datosBarras.push(texto);
        }
      });
    }

    return {
      estadisticas,
      subtitulo,
      filasDetalle,
      datosBarras
    };
  }

  function generarHTMLParaPDF(contenido) {
    const fechaActual = new Date().toLocaleDateString('es-CO');
    const horaActual = new Date().toLocaleTimeString('es-CO');
    
    let html = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Reporte ParkingSure</title>
        <style>
          body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            color: #000; 
            font-size: 12px;
          }
          .header { 
            text-align: center; 
            border-bottom: 2px solid #000; 
            padding-bottom: 15px; 
            margin-bottom: 20px; 
          }
          .logo { 
            font-size: 24px; 
            font-weight: bold; 
            margin-bottom: 5px; 
          }
          .logo span { color: #b8860b; }
          .fecha { 
            font-size: 11px; 
            color: #666; 
            margin-top: 5px; 
          }
          .titulo { 
            font-size: 16px; 
            font-weight: bold; 
            text-align: center; 
            margin-bottom: 20px; 
          }
          .estadisticas { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 15px; 
            margin-bottom: 25px; 
          }
          .stat-box { 
            border: 1px solid #ccc; 
            padding: 10px; 
            text-align: center; 
            border-radius: 5px; 
          }
          .stat-value { 
            font-size: 18px; 
            font-weight: bold; 
            color: #b8860b; 
            margin-bottom: 3px; 
          }
          .stat-label { 
            font-size: 10px; 
            color: #666; 
          }
          .seccion { 
            font-size: 14px; 
            font-weight: bold; 
            border-bottom: 1px solid #ccc; 
            padding-bottom: 5px; 
            margin: 20px 0 10px; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
          }
          th, td { 
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left; 
          }
          th { 
            background-color: #f5f5f5; 
            font-weight: bold; 
          }
          .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 10px; 
            color: #666; 
            border-top: 1px solid #ccc; 
            padding-top: 10px; 
          }
          @media print {
            body { margin: 10px; }
            .estadisticas { grid-template-columns: repeat(2, 1fr); }
          }
        </style>
      </head>
      <body>
        <div class="header">
          <div class="logo">PARKING<span>SURE</span></div>
          <div class="fecha">Generado el ${fechaActual} a las ${horaActual}</div>
        </div>
        
        <div class="titulo">${contenido.subtitulo}</div>
        
        <div class="estadisticas">
          <div class="stat-box">
            <div class="stat-value">${contenido.estadisticas.ingresos}</div>
            <div class="stat-label">Total Ingresos</div>
          </div>
          <div class="stat-box">
            <div class="stat-value">${contenido.estadisticas.vehiculos}</div>
            <div class="stat-label">Vehículos Atendidos</div>
          </div>
          <div class="stat-box">
            <div class="stat-value">${contenido.estadisticas.tiempo}</div>
            <div class="stat-label">Tiempo Promedio</div>
          </div>
          <div class="stat-box">
            <div class="stat-value">${contenido.estadisticas.mejor}</div>
            <div class="stat-label">Mejor Servicio</div>
          </div>
        </div>
    `;

    // Agregar datos de barras si existen
    if (contenido.datosBarras.length > 0) {
      html += `
        <div class="seccion">Ingresos por Tipo de Servicio</div>
        <table>
          <thead>
            <tr>
              <th>Tipo de Servicio</th>
              <th>Ingresos</th>
            </tr>
          </thead>
          <tbody>
      `;
      
      contenido.datosBarras.forEach(dato => {
        html += `<tr><td>${dato}</td></tr>`;
      });
      
      html += `
          </tbody>
        </table>
      `;
    }

    // Agregar tabla de detalle si existe
    if (contenido.filasDetalle.length > 0) {
      html += `
        <div class="seccion">Detalle de Transacciones</div>
        <table>
          <thead>
            <tr>
              <th>Hora</th>
              <th>Placa</th>
              <th>Tipo</th>
              <th>Módulo</th>
              <th>Duración</th>
              <th>Monto</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
      `;
      
      contenido.filasDetalle.forEach(fila => {
        html += '<tr>';
        fila.forEach(dato => {
          html += `<td>${dato}</td>`;
        });
        html += '</tr>';
      });
      
      html += `
          </tbody>
        </table>
      `;
    }

    html += `
        <div class="footer">
          ParkingSure · Sistema de Gestión de Parqueadero · Reporte generado automáticamente
        </div>
      </body>
      </html>
    `;

    return html;
  }

  function generarPDFConDatosPrueba() {
    console.log('Generando PDF con datos de prueba...');
    
    // Datos de prueba
    const datosPrueba = {
      resumen: {
        ingresos: 1500000,
        vehiculos: 25,
        tiempo_prom: '2h 30m',
        mejor_servicio: 'Estándar'
      },
      tipo: [
        { tipo: 'Estándar', cantidad: 15, ingresos: 750000 },
        { tipo: 'Premium', cantidad: 8, ingresos: 600000 },
        { tipo: 'VIP', cantidad: 2, ingresos: 150000 }
      ],
      detalle: [
        {
          fecha_hora_entrada: '2024-01-15 08:30:00',
          placa: 'ABC123',
          tipo: 'Estándar',
          modulo: 'A1',
          duracion: '2h 15m',
          monto_total: 50000,
          estado_pago: 'PAGADA'
        },
        {
          fecha_hora_entrada: '2024-01-15 10:45:00',
          placa: 'XYZ789',
          tipo: 'Premium',
          modulo: 'B2',
          duracion: '1h 30m',
          monto_total: 75000,
          estado_pago: 'PAGADA'
        }
      ]
    };

    // Usar datos de prueba para generar el PDF
    const fechaInicio = document.getElementById('fechaInicio').value || new Date().toISOString().split('T')[0];
    const fechaFin = document.getElementById('fechaFin').value || new Date().toISOString().split('T')[0];
    const fechaLabel = getLabelRango(fechaInicio, fechaFin);

    // Rellenar encabezado
    document.getElementById('rpFecha').textContent = `Reporte ${fechaLabel} (DATOS DE PRUEBA)`;

    // Estadísticas
    document.getElementById('rpStats').innerHTML = `
      <div class="rp-stat"><div class="rp-stat-val">${fmt(datosPrueba.resumen.ingresos)}</div><div class="rp-stat-lbl">Total Ingresos</div></div>
      <div class="rp-stat"><div class="rp-stat-val">${datosPrueba.resumen.vehiculos}</div><div class="rp-stat-lbl">Vehículos Atendidos</div></div>
      <div class="rp-stat"><div class="rp-stat-val">${datosPrueba.resumen.tiempo_prom}</div><div class="rp-stat-lbl">Tiempo Promedio</div></div>
      <div class="rp-stat"><div class="rp-stat-val">${datosPrueba.resumen.mejor_servicio}</div><div class="rp-stat-lbl">Mejor Servicio</div></div>`;

    // Tabla por tipo
    const tbTipo = document.querySelector('#rpTipoTable tbody');
    tbTipo.innerHTML = '';
    datosPrueba.tipo.forEach(r => {
      tbTipo.innerHTML += `<tr><td>${r.tipo}</td><td>${r.cantidad}</td><td>${fmt(r.ingresos)}</td></tr>`;
    });

    // Tabla detalle
    const tbDet = document.querySelector('#rpDetalleTable tbody');
    tbDet.innerHTML = '';
    datosPrueba.detalle.forEach(r => {
      tbDet.innerHTML += `<tr>
        <td>${fmtH(r.fecha_hora_entrada)}</td>
        <td>${r.placa}</td>
        <td>${r.tipo}</td>
        <td>${r.modulo}</td>
        <td>${r.duracion}</td>
        <td>${fmt(r.monto_total)}</td>
        <td>${r.estado_pago}</td>
      </tr>`;
    });

    console.log('PDF con datos de prueba listo para imprimir');
    toast('📄 Generando PDF con datos de prueba...');
    
    // Imprimir
    window.print();
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
