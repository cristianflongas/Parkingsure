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

  <title>ParkingSure — Pagos</title>

  <link rel="shortcut icon" href="../../img/logo.png">

  <link href="style/ps-core.css?v=2" rel="stylesheet">

  <style>

    .two-col { display:grid; grid-template-columns:1fr 1.5fr; gap:18px; align-items:start; }

    @media(max-width:900px){ .two-col { grid-template-columns:1fr; } }

    /* Mejoras responsive para mobile */
    @media (max-width: 768px) {
      .two-col {
        gap: 16px;
      }
      
      .stats-grid-4 {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
      }
      
      .card {
        padding: 16px;
      }
      
      .page-title {
        font-size: 24px;
      }
    }
    
    @media (max-width: 480px) {
      .stats-grid-4 {
        grid-template-columns: 1fr;
        gap: 10px;
      }
      
      .card {
        padding: 12px;
      }
      
      .page-title {
        font-size: 20px;
      }
      
      .btn {
        padding: 12px 16px;
        font-size: 14px;
      }
    }



    /* Ticket de factura */

    .ticket {

      background:var(--surface-2); border:1px solid var(--border-md);

      border-radius:var(--r-xl); padding:28px; position:relative; overflow:hidden;

    }

    .ticket::before {

      content:''; position:absolute; top:0; left:0; right:0; height:3px;

      background:linear-gradient(90deg,var(--gold),transparent);

    }

    .t-amount {

      font-family:'Syne',sans-serif; font-size:clamp(1.5rem, 6vw, 2.75rem); font-weight:800;

      color:var(--gold); text-align:center; margin:10px 0 2px;

    }

    .t-currency { font-size:12px; color:var(--text-muted); text-align:center; margin-bottom:16px; }

    .t-divider  { border:none; border-top:1px dashed var(--border-md); margin:12px 0; }

    .t-row      { display:flex; justify-content:space-between; padding:6px 0; font-size:13px; }

    .t-label    { color:var(--text-muted); }

    .t-value    { font-weight:600; color:var(--text-primary); }



    /* Métodos de pago */

    .pay-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:14px 0; }

    .pay-opt {

      border:1.5px solid var(--border-md); border-radius:var(--r-md);

      padding:14px 10px; text-align:center; cursor:pointer;

      transition:border-color .18s, background .18s;

      font-size:13px; font-weight:600; color:var(--text-secondary);

    }

    .pay-opt:hover  { border-color:var(--gold); color:var(--text-primary); }

    .pay-opt.active { border-color:var(--gold); background:var(--gold-dim); color:var(--gold); }

    .pay-opt .pm-icon { font-size:22px; display:block; margin-bottom:5px; }



    /* Historial */

    .his-amount { color:var(--gold); font-weight:700; }



    /* Badge estado */

    .badge-pend { background:rgba(245,158,11,.15); color:#f59e0b; border:1px solid rgba(245,158,11,.3); border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700; }

    .badge-paid { background:rgba(16,185,129,.15); color:#10b981; border:1px solid rgba(16,185,129,.3); border-radius:20px; padding:2px 10px; font-size:11px; font-weight:700; }



    /* Modal */

    .modal { display:none; position:fixed; z-index:2000; inset:0; background:rgba(0,0,0,0.5); align-items:center; justify-content:center; }

    .modal.show { display:flex; }

    .modal-content { background:var(--surface-1); border:1px solid var(--border-md); border-radius:var(--r-lg); width:90%; max-width:480px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 30px rgba(0,0,0,0.1); }

    .modal-header { display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid var(--border); }

    .modal-header h3 { margin:0; font-size:18px; font-weight:700; color:var(--text-primary); }

    .modal-close { background:none; border:none; font-size:22px; color:var(--text-muted); cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:var(--r-sm); transition:background .15s; }

    .modal-close:hover { background:var(--surface-3); color:var(--text-primary); }

    .modal-body   { padding:24px; }

    .modal-footer { display:flex; gap:10px; justify-content:flex-end; padding:16px 24px; border-top:1px solid var(--border); background:var(--surface-2); border-radius:0 0 var(--r-lg) var(--r-lg); }



    .stats-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }

    @media(max-width:860px){ .stats-grid-4 { grid-template-columns:repeat(2,1fr); } }

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

    <a class="nb active" href="pagos.php"> Pagos</a>

    <?php if ($rolUsuario === 'ADMINISTRADOR'): ?>

    <a class="nb" href="usuarios.php"> Usuarios</a>

    <a class="nb" href="servicios.php"> Servicios</a>

    <?php endif; ?>

    <a class="nb" href="reportes.php"> Reportes</a>

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

    <div class="page-eyebrow">Módulo financiero</div>

    <h1 class="page-title">Gestión de Pagos</h1>

    <div class="title-rule"></div>

    <p class="page-sub">Facturas pendientes de cobro e historial de pagos del día</p>

  </div>



  <!-- Estadísticas -->

  <div class="stats-grid-4" style="margin-bottom:22px">

    <div class="stat-card">

      <div class="stat-label">Ingresos Hoy</div>

      <div class="stat-value gold" id="stHoy">Cargando...</div>

      <div class="stat-sub">COP cobrados</div>

    </div>

    <div class="stat-card">

      <div class="stat-label">Transacciones</div>

      <div class="stat-value gold" id="stTx">0</div>

      <div class="stat-sub">pagos hoy</div>

    </div>

    <div class="stat-card">

      <div class="stat-label">Promedio</div>

      <div class="stat-value gold" id="stProm">$0</div>

      <div class="stat-sub">por cobro</div>

    </div>

    <div class="stat-card">

      <div class="stat-label">Pendientes</div>

      <div class="stat-value gold" id="stPend">0</div>

      <div class="stat-sub">por cobrar</div>

    </div>

  </div>



  <div class="two-col">



    <!-- Facturas Pendientes -->

    <div class="card">

      <div class="card-title"><span class="card-title-icon">⏳</span> Facturas Pendientes</div>

      <div class="search-wrap" style="margin-bottom:14px">

        <span class="s-icon">🔍</span>

        <input id="buscarFactura" placeholder="Buscar por placa…" oninput="filtrarPendientes()">

      </div>

      <div class="table-wrap">

        <table>

          <thead>

            <tr>

              <th>Placa</th>

              <th>Módulo</th>

              <th>Tiempo</th>

              <th>Total</th>

              <th>Acción</th>

            </tr>

          </thead>

          <tbody id="tbPendientes">

            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">Cargando…</td></tr>

          </tbody>

        </table>

      </div>

    </div>



    <!-- Historial de Pagos -->

    <div class="card">

      <div class="card-title"><span class="card-title-icon">✅</span> Historial de Cobros</div>

      <div class="search-wrap" style="margin-bottom:14px">

        <span class="s-icon">🔍</span>

        <input id="buscarHis" placeholder="Buscar por placa o método…" oninput="filtrarHistorial()">

      </div>

      <div class="table-wrap">

        <table>

          <thead>

            <tr><th>Hora</th><th>Placa</th><th>Servicio</th><th>Método</th><th>Monto</th></tr>

          </thead>

          <tbody id="tbHistorial">

            <tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">Cargando…</td></tr>

          </tbody>

        </table>

      </div>

    </div>



  </div>

</div>



<!-- ── Modal de Cobro ── -->

<div class="modal" id="modalCobro">

  <div class="modal-content">

    <div class="modal-header">

      <h3>💳 Procesar Pago</h3>

      <button class="modal-close" onclick="cerrarModalCobro()">&times;</button>

    </div>

    <div class="modal-body">



      <!-- Ticket resumen -->

      <div class="ticket">

        <div class="t-amount" id="ticketMonto">$0</div>

        <div class="t-currency">PESOS COLOMBIANOS</div>

        <hr class="t-divider">

        <div class="t-row"><span class="t-label">Placa</span>       <span class="t-value" id="ticketPlaca">—</span></div>

        <div class="t-row"><span class="t-label">Módulo</span>      <span class="t-value" id="ticketModulo">—</span></div>

        <div class="t-row"><span class="t-label">Servicio</span>    <span class="t-value" id="ticketServicio">—</span></div>

        <div class="t-row"><span class="t-label">Tiempo</span>      <span class="t-value" id="ticketTiempo">—</span></div>

        <div class="t-row"><span class="t-label">Horas cobradas</span><span class="t-value" id="ticketHoras">—</span></div>

        <div class="t-row"><span class="t-label">Tarifa/hora</span> <span class="t-value" id="ticketTarifa">—</span></div>

        <hr class="t-divider">

        <div class="t-row"><span class="t-label">Factura #</span>   <span class="t-value" id="ticketFactura">—</span></div>

        <div class="t-row"><span class="t-label">Emitida</span>     <span class="t-value" id="ticketFecha">—</span></div>

      </div>



      <!-- Selección de método de pago -->

      <p style="font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin:18px 0 8px">Método de pago</p>

      <div class="pay-grid">

        <div class="pay-opt active" data-metodo="EFECTIVO"    onclick="selMetodo(this)"><span class="pm-icon">💵</span>Efectivo</div>

        <div class="pay-opt"        data-metodo="TRANSFERENCIA" onclick="selMetodo(this)"><span class="pm-icon">🏦</span>Transferencia</div>

      </div>



      <p style="font-size:11px;color:var(--text-muted);margin-top:10px">

        ⚠️ Si el cliente no paga ahora, puedes cerrar esta ventana — la factura quedará <strong>PENDIENTE</strong> y podrás cobrarla después.

      </p>

    </div>

    <div class="modal-footer">

      <button class="btn btn-secondary" onclick="cerrarModalCobro()">Dejar Pendiente</button>

      <button class="btn btn-primary"   id="btnConfirmarPago" onclick="confirmarPago()">Confirmar Pago</button>

    </div>

  </div>

</div>



<div id="toast"></div>



<script>

  /* ── Estado global ── */

  let pendientes     = [];

  let pendientesFilt = [];

  let historialData  = [];

  let historialFilt  = [];

  let facturaActual  = null;

  let metodoActual   = 'EFECTIVO';



  /* ── Formateo ── */

  const fmt = n => new Intl.NumberFormat('es-CO', { style:'currency', currency:'COP', minimumFractionDigits:0 }).format(n);

  const fmtHora = s => new Date(s).toLocaleTimeString('es-CO', { hour:'2-digit', minute:'2-digit' });



  /* ════════════════════════════════════════

     FACTURAS PENDIENTES

  ════════════════════════════════════════ */

  async function cargarPendientes() {

    try {

      const res    = await fetch('../../controllers/facturaapi.php?action=getPendientes');

      const result = await res.json();



      if (result.success) {

        pendientes     = result.data;

        pendientesFilt = [...pendientes];

        renderPendientes();

        document.getElementById('stPend').textContent = pendientes.length;

      } else {

        pendientes = [];

        renderPendientes();

      }

    } catch (e) {

      console.error('Error cargando pendientes:', e);

      document.getElementById('tbPendientes').innerHTML =

        '<tr><td colspan="5" style="text-align:center;color:var(--error);padding:20px">Error al cargar facturas</td></tr>';

    }

  }



  function renderPendientes() {

    const tb = document.getElementById('tbPendientes');

    tb.innerHTML = '';



    if (!pendientesFilt.length) {

      tb.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">Sin facturas pendientes</td></tr>';

      return;

    }



    pendientesFilt.forEach(f => {

      const tr = document.createElement('tr');

      tr.innerHTML = `

        <td><strong style="font-family:monospace">${f.placa}</strong></td>

        <td><span style="font-family:monospace;font-size:11px;background:var(--surface-3);padding:2px 7px;border-radius:4px">${f.ubicacion || '—'}</span></td>

        <td style="font-size:12px;color:var(--text-secondary)">${f.tiempo_estancia || '—'}</td>

        <td><strong style="color:var(--gold)">${fmt(f.monto_total)}</strong></td>

        <td>

          <button class="btn btn-primary" style="font-size:11px;padding:5px 12px"

            onclick="abrirCobro(${JSON.stringify(f).replace(/"/g,'&quot;')})">

            Cobrar

          </button>

        </td>`;

      tb.appendChild(tr);

    });

  }



  function filtrarPendientes() {

    const q = document.getElementById('buscarFactura').value.toLowerCase();

    pendientesFilt = pendientes.filter(f =>

      f.placa.toLowerCase().includes(q) ||

      (f.ubicacion || '').toLowerCase().includes(q)

    );

    renderPendientes();

  }



  /* ════════════════════════════════════════

     HISTORIAL DE PAGOS

  ════════════════════════════════════════ */

  async function cargarHistorial() {

    try {

      // Corregir problema de zona horaria - usar fecha local
      const hoyLocal = new Date();
      const hoy = hoyLocal.getFullYear() + '-' + 
                  String(hoyLocal.getMonth() + 1).padStart(2, '0') + '-' + 
                  String(hoyLocal.getDate()).padStart(2, '0');
      
      console.log('🔄 Cargando historial de pagos para hoy:', hoy);
      console.log('🕐 Fecha local actual:', hoyLocal.toString());
      console.log('🌐 Fecha ISO (original):', new Date().toISOString().split('T')[0]);

      const res    = await fetch(`../../controllers/facturaapi.php?action=getPagadas&fecha=${hoy}`);
      console.log('📡 Respuesta HTTP:', res.status, res.statusText);

      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }

      const result = await res.json();
      console.log('📊 Datos recibidos:', result);

      if (result.success) {
        console.log('✅ Historial cargado:', result.data.length, 'registros');
        
        // Mostrar información completa de depuración
        console.log('🔍 Debug Info completo:', result.debug_info);
        console.log('📊 Total facturas en BD:', result.debug_info?.total_facturas);
        console.log('💳 Total facturas pagadas:', result.debug_info?.total_pagadas);
        console.log('📅 Fecha buscada:', result.debug_info?.fecha_buscada);
        console.log('🎯 Resultados encontrados:', result.debug_info?.resultados_encontrados);
        
        // Verificar si hay datos
        if (result.data && result.data.length > 0) {
          console.log('📋 Primer registro:', result.data[0]);
          console.log('📋 Todos los registros:', result.data);
        } else {
          console.log('⚠️ No hay registros de pagos para hoy');
          console.log('💡 Revisando posibles problemas:');
          console.log('  - ¿Hay facturas pagadas en la BD?', result.debug_info?.total_pagadas);
          console.log('  - ¿La fecha buscada es correcta?', result.debug_info?.fecha_buscada);
          console.log('  - ¿La tabla existe?', result.debug_info?.table_exists);
        }
        
        historialData = result.data || [];
        historialFilt = [...historialData];
        renderHistorial();
        actualizarStats();
      } else {
        console.error('❌ Error al cargar historial:', result.message);
        historialData = [];
        renderHistorial();
        actualizarStats();
      }
    } catch (e) {
      console.error('❌ Error cargando historial:', e);
      console.error('❌ Stack trace:', e.stack);
      historialData = [];
      renderHistorial();
    }
  }



  function renderHistorial() {
    console.log('🎨 Renderizando historial de pagos...');
    console.log('📋 Total de pagos:', historialFilt.length);

    const tb = document.getElementById('tbHistorial');
    console.log('🎯 Elemento tabla encontrado:', tb);

    tb.innerHTML = '';

    if (!historialFilt.length) {
      console.log('⚠️ No hay pagos para mostrar hoy');
      tb.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">Sin cobros registrados hoy</td></tr>';
      return;
    }

    console.log('🔄 Creando elementos para', historialFilt.length, 'pagos');
    
    historialFilt.forEach((f, index) => {
      console.log('💳 Procesando pago', index + 1, ':', f);
      console.log('🔍 Campos disponibles:', Object.keys(f));
      
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td style="color:var(--text-muted);font-size:12px">${fmtHora(f.fecha_emision)}</td>
        <td><strong style="font-family:monospace">${f.placa || 'N/A'}</strong></td>
        <td style="font-size:12px;color:var(--text-secondary)">${f.nombre_tipo_servicio || 'Servicio no disponible'}</td>
        <td><span class="badge badge-gold" style="font-size:11px">${f.metodo_pago || '—'}</span></td>
        <td class="his-amount">${fmt(f.monto_total)}</td>`;

      tb.appendChild(tr);

    });

  }



  function filtrarHistorial() {

    const q = document.getElementById('buscarHis').value.toLowerCase();

    historialFilt = historialData.filter(f =>

      f.placa.toLowerCase().includes(q) ||

      (f.metodo_pago || '').toLowerCase().includes(q)

    );

    renderHistorial();

  }



  function actualizarStats() {
    console.log('📊 Actualizando estadísticas de pagos...');
    console.log('📋 Total de registros en historialData:', historialData.length);

    const total   = historialData.reduce((s, f) => s + parseFloat(f.monto_total || 0), 0);
    const tx      = historialData.length;
    const promedio = tx ? Math.round(total / tx) : 0;

    console.log('💰 Total ingresos hoy:', total);
    console.log('📈 Total transacciones:', tx);
    console.log('📊 Promedio por transacción:', promedio);

    // Actualizar elementos del DOM
    const stHoyElement = document.getElementById('stHoy');
    const stTxElement = document.getElementById('stTx');
    const stPromElement = document.getElementById('stProm');

    if (stHoyElement) {
      stHoyElement.textContent = fmt(total);
      console.log('✅ stHoy actualizado:', fmt(total));
    } else {
      console.error('❌ Elemento stHoy no encontrado');
    }

    if (stTxElement) {
      stTxElement.textContent = tx;
      console.log('✅ stTx actualizado:', tx);
    } else {
      console.error('❌ Elemento stTx no encontrado');
    }

    if (stPromElement) {
      stPromElement.textContent = fmt(promedio);
      console.log('✅ stProm actualizado:', fmt(promedio));
    } else {
      console.error('❌ Elemento stProm no encontrado');
    }
  }



  /* ════════════════════════════════════════

     MODAL DE COBRO

  ════════════════════════════════════════ */

  function abrirCobro(factura) {

    facturaActual = factura;

    metodoActual  = 'EFECTIVO';



    // Resetear selección de método

    document.querySelectorAll('.pay-opt').forEach(el => el.classList.remove('active'));

    document.querySelector('[data-metodo="EFECTIVO"]').classList.add('active');



    // Rellenar ticket

    document.getElementById('ticketMonto').textContent   = fmt(factura.monto_total);

    document.getElementById('ticketPlaca').textContent   = factura.placa;

    document.getElementById('ticketModulo').textContent  = factura.ubicacion || '—';

    document.getElementById('ticketServicio').textContent= factura.nombre_tipo_servicio || '—';

    document.getElementById('ticketTiempo').textContent  = factura.tiempo_estancia || '—';

    document.getElementById('ticketHoras').textContent   = (factura.horas_cobradas || '—') + ' h';

    document.getElementById('ticketTarifa').textContent  = factura.tarifa ? fmt(factura.tarifa) + '/h' : '—';

    document.getElementById('ticketFactura').textContent = '#' + factura.id_factura;

    document.getElementById('ticketFecha').textContent   = factura.fecha_emision

      ? new Date(factura.fecha_emision).toLocaleString('es-CO')

      : '—';



    document.getElementById('modalCobro').classList.add('show');

  }



  function cerrarModalCobro() {

    document.getElementById('modalCobro').classList.remove('show');

    facturaActual = null;

  }



  function selMetodo(el) {

    document.querySelectorAll('.pay-opt').forEach(e => e.classList.remove('active'));

    el.classList.add('active');

    metodoActual = el.dataset.metodo;

  }



  async function confirmarPago() {

    if (!facturaActual) return;



    const btn = document.getElementById('btnConfirmarPago');

    btn.disabled    = true;

    btn.textContent = 'Procesando…';



    try {

      const res = await fetch('../../controllers/facturaapi.php', {

        method:  'POST',

        headers: { 'Content-Type': 'application/json' },

        body:    JSON.stringify({

          action:      'procesarPago',

          id_factura:  facturaActual.id_factura,

          metodo_pago: metodoActual

        })

      });

      const result = await res.json();



      if (result.success) {

        toast(`✅ Pago de ${fmt(facturaActual.monto_total)} registrado — ${metodoActual}`);

        cerrarModalCobro();

        // Recargar ambas listas

        await Promise.all([cargarPendientes(), cargarHistorial()]);

      } else {

        toast(`❌ ${result.message}`);

      }

    } catch (e) {

      console.error('Error procesando pago:', e);

      toast('❌ Error de conexión al procesar el pago');

    } finally {

      btn.disabled    = false;

      btn.textContent = 'Confirmar Pago';

    }

  }



  /* ── Toast ── */

  function toast(msg) {

    const t = document.getElementById('toast');

    t.textContent = msg;

    t.classList.add('show');

    setTimeout(() => t.classList.remove('show'), 3500);

  }



  /* ── Hamburger ── */

  const hamburgerBtn = document.getElementById('hamburger-btn');

  const navLinks     = document.getElementById('nav-links');

  hamburgerBtn.addEventListener('click', () => {

    navLinks.classList.toggle('active');

    hamburgerBtn.classList.toggle('active');

  });

  document.addEventListener('click', e => {

    if (!hamburgerBtn.contains(e.target) && !navLinks.contains(e.target)) {

      navLinks.classList.remove('active');

      hamburgerBtn.classList.remove('active');

    }

  });



  /* ── Inicialización ── */

  document.addEventListener('DOMContentLoaded', () => {

    cargarPendientes();

    cargarHistorial();

  });



  /* ── Precarga desde parqueadero (sessionStorage) ── */

  document.addEventListener('DOMContentLoaded', () => {

    const precargada = sessionStorage.getItem('facturaPrecargada');

    if (precargada) {

      sessionStorage.removeItem('facturaPrecargada');

      try {

        const factura = JSON.parse(precargada);

        // Esperar a que carguen las pendientes y luego abrir el modal

        setTimeout(() => abrirCobro(factura), 800);

      } catch(e) { /* ignorar */ }

    }

  });

</script>

</body>

</html>

