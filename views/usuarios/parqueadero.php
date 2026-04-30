<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Parqueadero Virtual</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    /* Toolbar */
    .toolbar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:14px; margin-bottom:20px; }
    .legend { display:flex; gap:20px; }
    .leg { display:flex; align-items:center; gap:7px; font-size:12px; font-weight:600; color:var(--text-secondary); }
    .leg-sq { width:12px; height:12px; border-radius:3px; flex-shrink:0; }

    /* Lot */
    .lot-shell {
      background: var(--surface-1);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: 28px;
      position: relative;
      overflow: hidden;
    }
    .lot-shell::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(232,184,75,.03) 0%, transparent 50%);
      pointer-events: none;
    }.lot-header {
      display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 24px;
    }
    .lot-arrow { font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:var(--text-muted); }
    .lot-label-pill {
      background: var(--gold-dim); border: 1px solid rgba(232,184,75,.2);
      border-radius: 20px; padding: 4px 16px;
      font-size: 10px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--gold);
    }
    .lot-grid { display: grid; grid-template-columns: repeat(8, 1fr); gap: 10px; }

    /* Module cells */
    .module {
      aspect-ratio: 1; border-radius: 10px; border: 1.5px solid;
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      cursor: pointer; transition: transform .15s, box-shadow .15s; padding: 6px; gap: 2px;
    }
    .module:hover { transform: scale(1.06); z-index: 2; }
    .module.libre   { background:rgba(16,185,129,.07); border-color:rgba(16,185,129,.3); color:var(--emerald); }
    .module.libre:hover  { box-shadow:0 4px 18px rgba(16,185,129,.2); border-color:var(--emerald); }
    .module.ocupado { background:rgba(239,68,68,.07); border-color:rgba(239,68,68,.3); color:var(--crimson); }
    .module.ocupado:hover { box-shadow:0 4px 18px rgba(239,68,68,.2); border-color:var(--crimson); }
    .module.nuevo   { background:transparent; border:1.5px dashed var(--border-md); color:var(--text-muted); }
    .module.nuevo:hover  { border-color:var(--gold); color:var(--gold); }
    .mod-icon  { font-size:18px; line-height:1; }
    .mod-id    { font-size:8.5px; font-weight:700; letter-spacing:.5px; opacity:.85; }
    .mod-plate { font-size:7px; font-weight:800; letter-spacing:.8px; background:rgba(0,0,0,.2); padding:1px 4px; border-radius:3px; margin-top:1px; }

    /* ── MODAL STEPS ── */
    .step { display:none; }
    .step.active { display:block; }

    /* Ticket inside modal */
    .mini-ticket {
      background:var(--surface-3); border:1px solid var(--border-md);
      border-radius:var(--r-md); padding:20px; margin:16px 0;
    }
    .mt-row { display:flex; justify-content:space-between; padding:7px 0; font-size:13px; border-bottom:1px solid var(--border); }
    .mt-row:last-child { border:none; }
    .mt-label { color:var(--text-muted); }
    .mt-value { font-weight:600; }
    .mt-total { font-family:'Syne',sans-serif; font-size:32px; font-weight:800; color:var(--gold); text-align:center; margin:12px 0 4px; }
    .mt-currency { font-size:12px; color:var(--text-muted); text-align:center; margin-bottom:12px; }

    /* Payment methods */
    .pay-method { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:14px 0; }
    .pay-opt {
      border:1.5px solid var(--border-md); border-radius:var(--r-md); padding:12px;
      text-align:center; cursor:pointer; transition:border-color .18s, background .18s;
      font-size:13px; font-weight:600; color:var(--text-secondary);
    }
    .pay-opt:hover { border-color:var(--gold); color:var(--text-primary); }
    .pay-opt.selected { border-color:var(--gold); background:var(--gold-dim); color:var(--gold); }
    .pay-opt .pm-icon { font-size:20px; display:block; margin-bottom:5px; }

    /* Confirm success */
    .confirm-box {
      background:rgba(16,185,129,.06); border:1px solid rgba(16,185,129,.2);
      border-radius:var(--r-xl); padding:32px 24px; text-align:center;
    }
    .confirm-icon { font-size:44px; margin-bottom:8px; }
    .confirm-title { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:var(--emerald); margin-bottom:6px; }
    .confirm-sub { font-size:13px; color:var(--text-secondary); }

    /* Entry steps breadcrumb */
    .steps-bar { display:flex; align-items:center; gap:8px; margin-bottom:20px; }
    .sb-step { font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase;
               padding:3px 10px; border-radius:20px; border:1px solid var(--border-md); color:var(--text-muted); }
    .sb-step.done  { background:rgba(16,185,129,.1); border-color:rgba(16,185,129,.3); color:var(--emerald); }
    .sb-step.active { background:var(--gold-dim); border-color:rgba(232,184,75,.3); color:var(--gold); }
    .sb-arrow { font-size:10px; color:var(--text-muted); }

    @media(max-width:900px){ .lot-grid { grid-template-columns:repeat(4,1fr); } }
    @media(max-width:600px){ .lot-grid { grid-template-columns:repeat(3,1fr); } .legend { gap:12px; } }
  </style>
</head>
<body>

<nav class="topbar">
  <a class="logo">
    <div class="logo-mark">P</div>
    <div class="logo-text">PARKING<em>SURE</em></div>
  </a>
  <div class="nav-links">
    <a class="nb" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb active" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <a class="nb" href="reportes.php"><span class="nav-icon"></span> Reportes</a>
  </div>
  <div class="nav-right">
    <div class="user-avatar">C</div>
    <div class="user-info"><div class="u-name">Cristian Longas</div><div class="u-role">Administrador</div></div>
    <a class="btn-logout" href="login.php">Salir</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <div class="page-eyebrow">Módulo operativo</div>
    <h1 class="page-title">Parqueadero Virtual</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Estado de módulos en tiempo real — selecciona un espacio para registrar entrada o salida</p>
  </div>

  <div class="stats-grid stats-grid-3">
    <div class="stat-card">
      <div class="stat-label">Módulos Totales</div>
      <div class="stat-value gold" id="st-total">16</div>
      <div class="stat-sub">configurados</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Disponibles</div>
      <div class="stat-value gold" id="st-free">10</div>
      <div class="stat-sub">libres ahora</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Ocupados</div>
      <div class="stat-value gold" id="st-busy">6</div>
      <div class="stat-sub">en uso</div>
    </div>
  </div>

  <div class="toolbar">
    <div class="legend">
      <div class="leg"><div class="leg-sq" style="background:rgba(16,185,129,.5);border:1px solid var(--emerald)"></div>Libre</div>
      <div class="leg"><div class="leg-sq" style="background:rgba(239,68,68,.5);border:1px solid var(--crimson)"></div>Ocupado</div>
    </div>
    <button class="btn btn-primary" onclick="openAdd()">+ Agregar Módulo</button>
  </div>

  <div class="lot-shell">
    <div class="lot-header">
      <span class="lot-arrow">⬅ ENTRADA</span>
      <span class="lot-label-pill">🅿️ Parqueadero Doña Luz</span>
      <span class="lot-arrow">SALIDA ➡</span>
    </div>
    <div class="lot-grid" id="lotGrid"></div>
  </div>
</div>


<!-- ════════════════════════════════════════
     MODAL: ENTRADA (módulo libre)
════════════════════════════════════════ -->
<div class="overlay" id="entradaOverlay">
  <div class="modal" style="max-width:480px">

    <!-- Breadcrumb -->
    <div class="steps-bar" id="entradaStepsBar">
      <span class="sb-step active" id="sb1">1 · Datos</span>
      <span class="sb-arrow">›</span>
      <span class="sb-step" id="sb2">2 · Servicio</span>
      <span class="sb-arrow">›</span>
      <span class="sb-step" id="sb3">3 · Confirmar</span>
    </div>

    <!-- STEP 1: Placa + propietario -->
    <div class="step active" id="eStep1">
      <div class="modal-header">
        <div class="modal-title" id="eTitle">Registrar Entrada</div>
        <div class="modal-sub" id="eSubtitle">Módulo seleccionado</div>
      </div>
      <div class="form-group">
        <label class="form-label">Placa del vehículo</label>
        <div class="input-wrap">
          <input class="form-input" id="ePlaca" placeholder="Ej: ABC-123" style="text-transform:uppercase">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Propietario / Cliente</label>
        <input class="form-input" id="eOwner" placeholder="Nombre del cliente (opcional)">
      </div>
      <div id="eErr1" class="alert alert-error" style="display:none"></div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeO('entradaOverlay')">Cancelar</button>
        <button class="btn btn-primary" style="flex:1" onclick="eNext1()">Siguiente →</button>
      </div>
    </div>

    <!-- STEP 2: Tipo de vehículo + tarifa -->
    <div class="step" id="eStep2">
      <div class="modal-header">
        <div class="modal-title">Tipo de Servicio</div>
        <div class="modal-sub">Selecciona la tarifa aplicable</div>
      </div>
      <div class="form-group">
        <label class="form-label">Tipo de vehículo</label>
        <select class="form-select" id="eTipoVeh">
          <option value="🚗">🚗 Automóvil</option>
          <option value="🏍️">🏍️ Motocicleta</option>
          <option value="🚛">🚛 Camión</option>
          <option value="🚌">🚌 Bus</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Tarifa aplicada</label>
        <select class="form-select" id="eTarifa">
          <option value="3000">🚗 Automóvil — $3.000/hora</option>
          <option value="2000">🏍️ Motocicleta — $2.000/hora</option>
          <option value="6000">🚛 Camión — $6.000/hora</option>
          <option value="5000">🚌 Bus — $5.000/hora</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Hora de entrada</label>
        <input class="form-input" type="time" id="eHoraEntrada">
      </div>
      <div id="eErr2" class="alert alert-error" style="display:none"></div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="eBack1()">← Atrás</button>
        <button class="btn btn-primary" style="flex:1" onclick="eNext2()">Revisar →</button>
      </div>
    </div>

    <!-- STEP 3: Confirmación -->
    <div class="step" id="eStep3">
      <div class="modal-header">
        <div class="modal-title">Confirmar Entrada</div>
        <div class="modal-sub">Verifica los datos antes de registrar</div>
      </div>
      <div class="mini-ticket">
        <div class="mt-row"><span class="mt-label">Módulo</span><span class="mt-value" id="confModulo">—</span></div>
        <div class="mt-row"><span class="mt-label">Placa</span><span class="mt-value" id="confPlaca" style="font-family:monospace">—</span></div>
        <div class="mt-row"><span class="mt-label">Propietario</span><span class="mt-value" id="confOwner">—</span></div>
        <div class="mt-row"><span class="mt-label">Tipo</span><span class="mt-value" id="confTipo">—</span></div>
        <div class="mt-row"><span class="mt-label">Tarifa</span><span class="mt-value" id="confTarifa">—</span></div>
        <div class="mt-row"><span class="mt-label">Hora entrada</span><span class="mt-value" id="confHora">—</span></div>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="eBack2()">← Atrás</button>
        <button class="btn btn-primary" style="flex:1" onclick="confirmarEntrada()">✓ Registrar Entrada</button>
      </div>
    </div>

    <!-- STEP 4: Éxito -->
    <div class="step" id="eStep4">
      <div class="confirm-box" style="margin-top:8px">
        <div class="confirm-icon">✅</div>
        <div class="confirm-title">Entrada Registrada</div>
        <div class="confirm-sub" id="confSuccessMsg">El vehículo fue asignado correctamente.</div>
      </div>
      <div class="modal-actions" style="margin-top:18px">
        <button class="btn btn-secondary btn-full" onclick="closeO('entradaOverlay')">Cerrar</button>
      </div>
    </div>

  </div>
</div>


<!-- ════════════════════════════════════════
     MODAL: SALIDA (módulo ocupado)
════════════════════════════════════════ -->
<div class="overlay" id="salidaOverlay">
  <div class="modal" style="max-width:480px">

    <!-- Breadcrumb -->
    <div class="steps-bar" id="salidaStepsBar">
      <span class="sb-step active" id="ss1">1 · Estadía</span>
      <span class="sb-arrow">›</span>
      <span class="sb-step" id="ss2">2 · Pago</span>
      <span class="sb-arrow">›</span>
      <span class="sb-step" id="ss3">3 · Confirmar</span>
    </div>

    <!-- STEP 1: Resumen de estadía -->
    <div class="step active" id="sStep1">
      <div class="modal-header">
        <div class="modal-title" id="sTitle">Registrar Salida</div>
        <div class="modal-sub" id="sSubtitle">Calculando estadía…</div>
      </div>
      <div class="form-group">
        <label class="form-label">Hora de salida</label>
        <input class="form-input" type="time" id="sHoraSalida">
      </div>
      <div class="mini-ticket" id="salidaTicket">
        <div class="mt-row"><span class="mt-label">Módulo</span><span class="mt-value" id="sModulo">—</span></div>
        <div class="mt-row"><span class="mt-label">Placa</span><span class="mt-value" id="sPlaca" style="font-family:monospace">—</span></div>
        <div class="mt-row"><span class="mt-label">Propietario</span><span class="mt-value" id="sOwner">—</span></div>
        <div class="mt-row"><span class="mt-label">Hora entrada</span><span class="mt-value" id="sEntrada">—</span></div>
        <div class="mt-row"><span class="mt-label">Hora salida</span><span class="mt-value" id="sSalida">—</span></div>
        <div class="mt-row"><span class="mt-label">Duración</span><span class="mt-value" id="sDuracion">—</span></div>
        <div class="mt-row"><span class="mt-label">Tarifa</span><span class="mt-value" id="sTarifaLabel">—</span></div>
      </div>
      <div class="mt-total" id="sTotal">$0</div>
      <div class="mt-currency">Pesos Colombianos (COP)</div>
      <div id="sErr1" class="alert alert-error" style="display:none"></div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeO('salidaOverlay')">Cancelar</button>
        <button class="btn btn-primary" style="flex:1" onclick="sNext1()">Ir a Pago →</button>
      </div>
    </div>

    <!-- STEP 2: Método de pago -->
    <div class="step" id="sStep2">
      <div class="modal-header">
        <div class="modal-title">Método de Pago</div>
        <div class="modal-sub">Selecciona cómo abona el cliente</div>
      </div>
      <div style="text-align:center;margin:16px 0 4px">
        <div class="mt-total" id="sTotal2">$0</div>
        <div class="mt-currency">Total a cobrar</div>
      </div>
      <div class="pay-method">
        <div class="pay-opt selected" id="sp-efectivo" onclick="selectSPM('efectivo')">
          <span class="pm-icon">💵</span>Efectivo
        </div>
        <div class="pay-opt" id="sp-transferencia" onclick="selectSPM('transferencia')">
          <span class="pm-icon">📲</span>Transferencia
        </div>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="sBack1()">← Atrás</button>
        <button class="btn btn-primary" style="flex:1" onclick="sNext2()">Revisar →</button>
      </div>
    </div>

    <!-- STEP 3: Confirmar cobro -->
    <div class="step" id="sStep3">
      <div class="modal-header">
        <div class="modal-title">Confirmar Cobro</div>
        <div class="modal-sub">Resumen final antes de liberar el módulo</div>
      </div>
      <div class="mini-ticket">
        <div class="mt-row"><span class="mt-label">Módulo</span><span class="mt-value" id="sConfModulo">—</span></div>
        <div class="mt-row"><span class="mt-label">Placa</span><span class="mt-value" id="sConfPlaca" style="font-family:monospace">—</span></div>
        <div class="mt-row"><span class="mt-label">Duración</span><span class="mt-value" id="sConfDur">—</span></div>
        <div class="mt-row"><span class="mt-label">Total cobrado</span><span class="mt-value" style="color:var(--gold)" id="sConfTotal">—</span></div>
        <div class="mt-row"><span class="mt-label">Método pago</span><span class="mt-value" id="sConfMetodo">—</span></div>
      </div>
      <div style="display:flex;gap:10px;margin-top:8px">
        <button class="btn btn-ghost" onclick="toast('📄 Factura PDF generada')">Factura PDF</button>
        <button class="btn btn-primary" style="flex:1" onclick="confirmarSalida()">✓ Cobrar y Liberar</button>
      </div>
      <div style="margin-top:6px">
        <button class="btn btn-ghost" onclick="sBack2()">← Atrás</button>
      </div>
    </div>

    <!-- STEP 4: Éxito -->
    <div class="step" id="sStep4">
      <div class="confirm-box" style="margin-top:8px">
        <div class="confirm-icon">✅</div>
        <div class="confirm-title">Pago Registrado</div>
        <div class="confirm-sub" id="salidaSuccessMsg">El módulo fue liberado y la transacción guardada.</div>
      </div>
      <div class="modal-actions" style="margin-top:18px">
        <button class="btn btn-secondary btn-full" onclick="closeO('salidaOverlay')">Cerrar</button>
      </div>
    </div>

  </div>
</div>


<!-- ════════════════════════════════════════
     MODAL: AGREGAR MÓDULO
════════════════════════════════════════ -->
<div class="overlay" id="addOverlay">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Nuevo Módulo</div>
      <div class="modal-sub">Configura el nuevo espacio de parqueo</div>
    </div>
    <div class="form-group">
      <label class="form-label">Identificador</label>
      <input class="form-input" id="newId" placeholder="Ej: F-01">
    </div>
    <div class="form-group">
      <label class="form-label">Tipo permitido</label>
      <select class="form-select" id="newType">
        <option value="🚗">🚗 Automóvil</option>
        <option value="🏍️">🏍️ Motocicleta</option>
        <option value="🚛">🚛 Camión</option>
        <option value="🚌">🚌 Bus</option>
      </select>
    </div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeO('addOverlay')">Cancelar</button>
      <button class="btn btn-primary" style="flex:1" onclick="addModule()">Agregar Módulo</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<script>
  /* ─── Tarifas por tipo ─── */
  const TARIFAS = {
    '🚗': { label:'Automóvil', valor:3000 },
    '🏍️': { label:'Motocicleta', valor:2000 },
    '🚛': { label:'Camión', valor:6000 },
    '🚌': { label:'Bus', valor:5000 },
  };

  /* ─── Estado de módulos ─── */
  let modules = [
    {id:'A-01',t:'🚗',s:'ocupado',p:'PLT-456',owner:'Carlos Ruiz',entrada:'08:30',tarifa:3000},
    {id:'A-02',t:'🚗',s:'libre'},
    {id:'A-03',t:'🚗',s:'ocupado',p:'ABC-123',owner:'Juan Pérez',entrada:'09:14',tarifa:3000},
    {id:'A-04',t:'🚗',s:'libre'},
    {id:'B-01',t:'🏍️',s:'libre'},
    {id:'B-02',t:'🏍️',s:'ocupado',p:'MOT-001',owner:'Laura Soto',entrada:'07:55',tarifa:2000},
    {id:'B-03',t:'🏍️',s:'libre'},
    {id:'B-04',t:'🏍️',s:'libre'},
    {id:'C-01',t:'🚗',s:'libre'},
    {id:'C-02',t:'🚗',s:'ocupado',p:'GHI-654',owner:'Pedro Gómez',entrada:'10:00',tarifa:3000},
    {id:'C-03',t:'🚗',s:'libre'},
    {id:'C-04',t:'🚗',s:'libre'},
    {id:'D-01',t:'🚛',s:'libre'},
    {id:'D-02',t:'🚛',s:'libre'},
    {id:'E-01',t:'🚌',s:'libre'},
    {id:'E-02',t:'🚌',s:'libre'},
  ];

  let currentIdx = -1;
  let selectedSPM = 'efectivo';
  let salidaData = {};

  /* ─── Render ─── */
  function renderLot(){
    const g = document.getElementById('lotGrid');
    g.innerHTML = '';
    modules.forEach((m,i) => {
      const d = document.createElement('div');
      d.className = `module ${m.s}`;
      d.innerHTML = `<div class="mod-icon">${m.t}</div>
        <div class="mod-id">${m.id}</div>
        ${m.p ? `<div class="mod-plate">${m.p}</div>` : ''}`;
      d.onclick = () => openMod(i);
      g.appendChild(d);
    });
    const add = document.createElement('div');
    add.className = 'module nuevo';
    add.innerHTML = '<div style="font-size:20px">+</div><div class="mod-id">NUEVO</div>';
    add.onclick = openAdd;
    g.appendChild(add);
    syncStats();
  }

  function syncStats(){
    const total = modules.length;
    const busy  = modules.filter(m => m.s === 'ocupado').length;
    document.getElementById('st-total').textContent = total;
    document.getElementById('st-free').textContent  = total - busy;
    document.getElementById('st-busy').textContent  = busy;
  }

  /* ─── Abrir modal según estado ─── */
  function openMod(i){
    currentIdx = i;
    const m = modules[i];
    if(m.s === 'libre'){
      openEntrada(i);
    } else {
      openSalida(i);
    }
  }

  /* ══════════════════════════════════════
     FLUJO ENTRADA
  ══════════════════════════════════════ */
  function openEntrada(i){
    const m = modules[i];
    resetEntradaSteps();
    document.getElementById('eTitle').textContent    = `Registrar Entrada — Módulo ${m.id}`;
    document.getElementById('eSubtitle').textContent = `Tipo: ${m.t}  ·  Disponible`;
    document.getElementById('ePlaca').value   = '';
    document.getElementById('eOwner').value   = '';
    document.getElementById('eErr1').style.display = 'none';

    /* Pre-seleccionar tipo de vehículo según módulo */
    const selTipo  = document.getElementById('eTipoVeh');
    const selTarif = document.getElementById('eTarifa');
    selTipo.value  = m.t;
    /* Set tarifa matching */
    for(let opt of selTarif.options){
      if(opt.value == TARIFAS[m.t].valor){ selTarif.value = opt.value; break; }
    }

    const now = new Date();
    document.getElementById('eHoraEntrada').value = now.toTimeString().slice(0,5);

    document.getElementById('entradaOverlay').classList.add('open');
  }

  function resetEntradaSteps(){
    ['eStep1','eStep2','eStep3','eStep4'].forEach((id,k) => {
      const el = document.getElementById(id);
      el.classList.remove('active');
      if(k===0) el.classList.add('active');
    });
    setSB(['sb1','sb2','sb3'], 'active', 0);
  }

  function eNext1(){
    const placa = document.getElementById('ePlaca').value.trim().toUpperCase();
    const err   = document.getElementById('eErr1');
    if(!placa){ err.textContent='Ingresa la placa del vehículo.'; err.style.display='flex'; return; }
    err.style.display='none';
    document.getElementById('ePlaca').value = placa;
    showEStep(2);
  }

  function eBack1(){ showEStep(1); }

  function eNext2(){
    const hora = document.getElementById('eHoraEntrada').value;
    const err  = document.getElementById('eErr2');
    if(!hora){ err.textContent='Ingresa la hora de entrada.'; err.style.display='flex'; return; }
    err.style.display='none';

    const placa  = document.getElementById('ePlaca').value.trim().toUpperCase();
    const owner  = document.getElementById('eOwner').value.trim() || 'No registrado';
    const tipo   = document.getElementById('eTipoVeh').value;
    const tarifa = parseInt(document.getElementById('eTarifa').value);
    const m      = modules[currentIdx];

    document.getElementById('confModulo').textContent  = m.id;
    document.getElementById('confPlaca').textContent   = placa;
    document.getElementById('confOwner').textContent   = owner;
    document.getElementById('confTipo').textContent    = tipo + ' ' + TARIFAS[tipo].label;
    document.getElementById('confTarifa').textContent  = `$${tarifa.toLocaleString('es-CO')}/hora`;
    document.getElementById('confHora').textContent    = hora;
    showEStep(3);
  }

  function eBack2(){ showEStep(2); }

  function confirmarEntrada(){
    const m      = modules[currentIdx];
    const placa  = document.getElementById('ePlaca').value.trim().toUpperCase();
    const owner  = document.getElementById('eOwner').value.trim() || 'No registrado';
    const tipo   = document.getElementById('eTipoVeh').value;
    const tarifa = parseInt(document.getElementById('eTarifa').value);
    const hora   = document.getElementById('eHoraEntrada').value;

    modules[currentIdx] = { ...m, s:'ocupado', p:placa, owner, t:tipo, entrada:hora, tarifa };
    renderLot();
    document.getElementById('confSuccessMsg').textContent =
      `${placa} asignado al módulo ${m.id} desde las ${hora}.`;
    showEStep(4);
    toast(`✓ Entrada registrada · ${placa} → Módulo ${m.id}`);
  }

  function showEStep(n){
    ['eStep1','eStep2','eStep3','eStep4'].forEach((id,k) => {
      document.getElementById(id).classList.toggle('active', k===n-1);
    });
    const sbMap = {1:0, 2:1, 3:2, 4:3};
    setSB(['sb1','sb2','sb3'], 'active', sbMap[n]);
    if(n > 1) document.getElementById('sb1').classList.add('done');
    if(n > 2) document.getElementById('sb2').classList.add('done');
    if(n > 3) document.getElementById('sb3').classList.add('done');
  }

  /* ══════════════════════════════════════
     FLUJO SALIDA
  ══════════════════════════════════════ */
  function openSalida(i){
    const m = modules[i];
    resetSalidaSteps();
    selectedSPM = 'efectivo';
    ['sp-efectivo','sp-transferencia','sp-tarjeta','sp-datafono'].forEach(id =>
      document.getElementById(id).classList.toggle('selected', id==='sp-efectivo'));

    document.getElementById('sTitle').textContent    = `Registrar Salida — Módulo ${m.id}`;
    document.getElementById('sSubtitle').textContent = `Vehículo: ${m.p}  ·  Entrada: ${m.entrada||'—'}`;

    const now = new Date();
    document.getElementById('sHoraSalida').value = now.toTimeString().slice(0,5);

    /* Llenar ticket */
    document.getElementById('sModulo').textContent  = m.id;
    document.getElementById('sPlaca').textContent   = m.p;
    document.getElementById('sOwner').textContent   = m.owner || 'No registrado';
    document.getElementById('sEntrada').textContent = m.entrada || '—';

    calcularSalida();
    document.getElementById('sHoraSalida').addEventListener('change', calcularSalida);
    document.getElementById('salidaOverlay').classList.add('open');
  }

  function calcularSalida(){
    const m       = modules[currentIdx];
    const entrada = m.entrada || '00:00';
    const salida  = document.getElementById('sHoraSalida').value || entrada;
    const tarifa  = m.tarifa || 3000;

    const [hE,mE] = entrada.split(':').map(Number);
    const [hS,mS] = salida.split(':').map(Number);
    let minutos = (hS*60+mS) - (hE*60+mE);
    if(minutos < 0) minutos += 24*60;
    if(minutos === 0) minutos = 60; /* mínimo 1 hora */

    const horas    = minutos / 60;
    const total    = Math.ceil(horas) * tarifa;
    const durLabel = `${Math.floor(minutos/60)}h ${String(minutos%60).padStart(2,'0')}m`;

    document.getElementById('sSalida').textContent      = salida;
    document.getElementById('sDuracion').textContent    = durLabel;
    document.getElementById('sTarifaLabel').textContent = `$${tarifa.toLocaleString('es-CO')}/hora`;
    document.getElementById('sTotal').textContent       = `$${total.toLocaleString('es-CO')}`;
    document.getElementById('sTotal2').textContent      = `$${total.toLocaleString('es-CO')}`;

    salidaData = { modulo:m.id, placa:m.p, durLabel, total, tarifa, salida };
  }

  function sNext1(){
    const salida = document.getElementById('sHoraSalida').value;
    const err    = document.getElementById('sErr1');
    if(!salida){ err.textContent='Ingresa la hora de salida.'; err.style.display='flex'; return; }
    err.style.display='none';
    calcularSalida();
    showSStep(2);
  }

  function sBack1(){ showSStep(1); }

  function selectSPM(pm){
    selectedSPM = pm;
    ['sp-efectivo','sp-transferencia','sp-tarjeta','sp-datafono'].forEach(id =>
      document.getElementById(id).classList.toggle('selected', id===`sp-${pm}`));
  }

  function sNext2(){
    document.getElementById('sConfModulo').textContent  = salidaData.modulo;
    document.getElementById('sConfPlaca').textContent   = salidaData.placa;
    document.getElementById('sConfDur').textContent     = salidaData.durLabel;
    document.getElementById('sConfTotal').textContent   = `$${salidaData.total.toLocaleString('es-CO')}`;
    document.getElementById('sConfMetodo').textContent  = selectedSPM.charAt(0).toUpperCase() + selectedSPM.slice(1);
    showSStep(3);
  }

  function sBack2(){ showSStep(2); }

  function confirmarSalida(){
    const m = modules[currentIdx];
    document.getElementById('salidaSuccessMsg').textContent =
      `Módulo ${m.id} liberado · $${salidaData.total.toLocaleString('es-CO')} cobrado via ${selectedSPM}.`;

    modules[currentIdx] = { id:m.id, t:m.t, s:'libre' };
    renderLot();
    showSStep(4);
    toast(`✓ Salida registrada · ${salidaData.placa} · $${salidaData.total.toLocaleString('es-CO')}`);
  }

  function resetSalidaSteps(){
    ['sStep1','sStep2','sStep3','sStep4'].forEach((id,k) => {
      const el = document.getElementById(id);
      el.classList.remove('active');
      if(k===0) el.classList.add('active');
    });
    setSB(['ss1','ss2','ss3'], 'active', 0);
  }

  function showSStep(n){
    ['sStep1','sStep2','sStep3','sStep4'].forEach((id,k) => {
      document.getElementById(id).classList.toggle('active', k===n-1);
    });
    const sbMap = {1:0, 2:1, 3:2, 4:3};
    setSB(['ss1','ss2','ss3'], 'active', sbMap[n]);
    if(n > 1) document.getElementById('ss1').classList.add('done');
    if(n > 2) document.getElementById('ss2').classList.add('done');
    if(n > 3) document.getElementById('ss3').classList.add('done');
  }

  /* ─── Agregar módulo ─── */
  function openAdd(){ document.getElementById('addOverlay').classList.add('open'); }
  function addModule(){
    const id = document.getElementById('newId').value.trim();
    const t  = document.getElementById('newType').value;
    if(!id) return;
    modules.push({id, t, s:'libre'});
    closeO('addOverlay');
    document.getElementById('newId').value = '';
    renderLot();
    toast(`✓ Módulo ${id} agregado`);
  }

  /* ─── Helpers ─── */
  function closeO(id){
    document.getElementById(id).classList.remove('open');
  }

  function setSB(ids, cls, activeIdx){
    ids.forEach((id,k) => {
      document.getElementById(id).classList.remove('active','done');
      if(k === activeIdx) document.getElementById(id).classList.add(cls);
    });
  }

  function toast(msg){
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3200);
  }

  /* Cerrar overlay al click exterior */
  document.querySelectorAll('.overlay').forEach(o =>
    o.addEventListener('click', e => { if(e.target === o) o.classList.remove('open'); }));

  renderLot();
</script>
</body>
</html>