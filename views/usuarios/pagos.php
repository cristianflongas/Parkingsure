<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Pagos</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    .pay-layout { display:grid; grid-template-columns:1fr 1fr; gap:18px; align-items:start; }

    /* Ticket */
    .ticket {
      background: var(--surface-2);
      border: 1px solid var(--border-md);
      border-radius: var(--r-xl);
      padding: 30px;
      position: relative;
      overflow: hidden;
    }
    .ticket::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: linear-gradient(90deg, var(--gold), transparent);
    }
    .ticket-head { text-align:center; margin-bottom:20px; }
    .ticket-emoji { font-size:36px; margin-bottom:8px; }
    .ticket-caption {
      font-size:10px; font-weight:700; letter-spacing:3px;
      text-transform:uppercase; color:var(--text-muted); margin-bottom:10px;
    }
    .ticket-amount {
      font-family:'Syne',sans-serif; font-size:52px; font-weight:800;
      color:var(--gold); line-height:1;
    }
    .ticket-currency { font-size:13px; color:var(--text-muted); margin-top:4px; }

    .t-divider {
      border: none;
      border-top: 1px dashed var(--border-md);
      margin: 16px 0;
    }
    .t-row { display:flex; justify-content:space-between; padding:8px 0; font-size:13px; }
    .t-row .t-label { color:var(--text-muted); }
    .t-row .t-value { font-weight:600; }

    .pay-method {
      display:grid; grid-template-columns:1fr 1fr; gap:10px; margin:16px 0;
    }
    .pay-opt {
      border: 1.5px solid var(--border-md);
      border-radius: var(--r-md);
      padding: 12px;
      text-align: center;
      cursor: pointer;
      transition: border-color .18s, background .18s;
      font-size: 13px; font-weight: 600; color: var(--text-secondary);
    }
    .pay-opt:hover { border-color: var(--gold); color: var(--text-primary); }
    .pay-opt.selected { border-color: var(--gold); background: var(--gold-dim); color: var(--gold); }
    .pay-opt .pm-icon { font-size:22px; display:block; margin-bottom:5px; }

    /* Confirm */
    .pay-done {
      background: rgba(16,185,129,.06);
      border: 1px solid rgba(16,185,129,.2);
      border-radius: var(--r-xl);
      padding: 40px 28px;
      text-align: center;
    }
    .pay-done-icon { font-size:48px; margin-bottom:10px; }
    .pay-done-title { font-family:'Syne',sans-serif; font-size:22px; font-weight:800; color:var(--emerald); margin-bottom:6px; }
    .pay-done-sub { font-size:13px; color:var(--text-secondary); }

    @media(max-width:860px){ .pay-layout { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<nav class="topbar">
  <a class="logo" ><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <div class="nav-links">
     <a class="nb" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb active" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
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
    <div class="page-eyebrow">Módulo financiero</div>
    <h1 class="page-title">Gestión de Pagos</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Calcula el cobro, selecciona el método de pago y genera la factura</p>
  </div>

  <div class="pay-layout">
    <!-- Form -->
    <div>
      <div class="card">
        <div class="card-title"><span class="card-title-icon">🔢</span> Calcular Cobro</div>
        <div class="form-group">
          <label class="form-label">Placa del vehículo</label>
          <div class="input-wrap">
            <span class="i-icon">🚗</span>
            <input class="form-input" id="payPlaca" placeholder="Ej: ABC-123" style="padding-left:38px;text-transform:uppercase">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Tipo de servicio / Tarifa</label>
          <select class="form-select" id="payTarifa">
            <option value="3000">🚗 Automóvil — $3.000/hora</option>
            <option value="2000">🏍️ Motocicleta — $2.000/hora</option>
            <option value="6000">🚛 Camión — $6.000/hora</option>
            <option value="5000">🚌 Bus — $5.000/hora</option>
          </select>
        </div>
        <div id="payError" class="alert alert-error" style="display:none"></div>
        <button class="btn btn-primary btn-full btn-lg" onclick="calcularPago()">Calcular Cobro →</button>
      </div>

      <!-- Confirmation -->
      <div class="pay-done" id="payConfirm" style="display:none; margin-top:18px;">
        <div class="pay-done-icon">✅</div>
        <div class="pay-done-title">Pago Registrado</div>
        <div class="pay-done-sub">La transacción fue procesada y la factura generada exitosamente.</div>
        <button class="btn btn-secondary" style="margin-top:16px" onclick="resetPago()">← Nuevo Cobro</button>
      </div>
    </div>

    <!-- Ticket -->
    <div id="ticketWrap" style="display:none">
      <div class="ticket">
        <div class="ticket-head">
          <div class="ticket-emoji">🎫</div>
          <div class="ticket-caption">Ticket de Cobro</div>
          <div class="ticket-amount" id="tPrice">$0</div>
          <div class="ticket-currency">Pesos Colombianos (COP)</div>
        </div>
        <hr class="t-divider">
        <div class="t-row"><span class="t-label">Placa</span><span class="t-value" id="tPlaca" style="font-family:monospace">—</span></div>
        <div class="t-row"><span class="t-label">Módulo</span><span class="t-value" id="tModulo">—</span></div>
        <div class="t-row"><span class="t-label">Hora de entrada</span><span class="t-value" id="tEntrada">—</span></div>
        <div class="t-row"><span class="t-label">Hora de salida</span><span class="t-value" id="tSalida">—</span></div>
        <div class="t-row"><span class="t-label">Duración</span><span class="t-value" id="tDur">—</span></div>
        <div class="t-row"><span class="t-label">Tarifa aplicada</span><span class="t-value" id="tTarifa">—</span></div>
        <hr class="t-divider">

        <label class="form-label" style="margin-bottom:10px">Método de Pago</label>
        <div class="pay-method">
          <div class="pay-opt selected" id="pm-efectivo" onclick="selectPM('efectivo')">
            <span class="pm-icon">💵</span>Efectivo
          </div>
          <div class="pay-opt" id="pm-transferencia" onclick="selectPM('transferencia')">
            <span class="pm-icon">📲</span>Transferencia
          </div>
        </div>

        <div style="display:flex;gap:10px">
          <button class="btn btn-ghost" onclick="toast('📄 Factura PDF generada')">Factura PDF</button>
          <button class="btn btn-primary" style="flex:1" onclick="registrarPago()">Registrar Pago ✓</button>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  const activos={'ABC-123':{modulo:'A-03',entrada:'09:14'},'PLT-456':{modulo:'A-01',entrada:'08:30'},'MOT-001':{modulo:'B-02',entrada:'07:55'},'GHI-654':{modulo:'C-01',entrada:'10:00'}};
  let selectedPM = 'efectivo';

  function calcularPago(){
    const placa=document.getElementById('payPlaca').value.trim().toUpperCase();
    const tarifa=parseInt(document.getElementById('payTarifa').value);
    const err=document.getElementById('payError');
    if(!placa){err.textContent='Ingresa la placa del vehículo.';err.style.display='flex';return;}
    const datos=activos[placa];
    if(!datos){err.textContent=`El vehículo ${placa} no se encuentra activo en el parqueadero.`;err.style.display='flex';document.getElementById('ticketWrap').style.display='none';return;}
    err.style.display='none';
    const horas=2, total=horas*tarifa;
    const ahora=new Date().toLocaleTimeString('es-CO',{hour:'2-digit',minute:'2-digit'});
    document.getElementById('tPrice').textContent=`$${total.toLocaleString('es-CO')}`;
    document.getElementById('tPlaca').textContent=placa;
    document.getElementById('tModulo').textContent=datos.modulo;
    document.getElementById('tEntrada').textContent=datos.entrada;
    document.getElementById('tSalida').textContent=ahora;
    document.getElementById('tDur').textContent=`${horas}h 00m`;
    document.getElementById('tTarifa').textContent=`$${tarifa.toLocaleString('es-CO')}/hora`;
    document.getElementById('ticketWrap').style.display='block';
    document.getElementById('payConfirm').style.display='none';
  }
  function selectPM(pm){
    selectedPM=pm;
    document.getElementById('pm-efectivo').classList.toggle('selected',pm==='efectivo');
    document.getElementById('pm-transferencia').classList.toggle('selected',pm==='transferencia');
  }
  function registrarPago(){
    document.getElementById('ticketWrap').style.display='none';
    document.getElementById('payConfirm').style.display='block';
    toast(`✓ Pago registrado · Método: ${selectedPM}`);
  }
  function resetPago(){
    document.getElementById('payConfirm').style.display='none';
    document.getElementById('payPlaca').value='';
    toast('Listo para nuevo cobro');
  }
  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
</script>
</body>
</html>