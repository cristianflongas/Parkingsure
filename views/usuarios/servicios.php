<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Servicios</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    .svc-layout { display:grid; grid-template-columns:1fr 1.4fr; gap:18px; align-items:start; }
    /* Service cards */
    .svc-item {
      background:var(--surface-2); border:1px solid var(--border);
      border-radius:var(--r-md); padding:16px; margin-bottom:10px;
      transition:border-color .18s;
    }
    .svc-item:hover { border-color:var(--border-md); }
    .svc-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px; }
    .svc-nombre { font-size:14px; font-weight:700; color:var(--text-primary); }
    .svc-acts { display:flex; gap:6px; flex-shrink:0; }
    .svc-row { display:flex; justify-content:space-between; padding:6px 0; font-size:12.5px; border-bottom:1px solid var(--border); }
    .svc-row:last-child { border:none; }
    .svc-row span:first-child { color:var(--text-muted); }
    .svc-row span:last-child  { font-weight:600; }
    @media(max-width:860px){ .svc-layout { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<nav class="topbar">
  <a class="logo" href="dashboard.php"><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <div class="nav-links">
     <a class="nb " href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb active" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
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
    <div class="page-eyebrow">Configuración</div>
    <h1 class="page-title">Gestión de Servicios</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Catálogo de tipos de servicio y tarifas del parqueadero</p>
  </div>

  <div class="stats-grid stats-grid-3">
    <div class="stat-card">
      <div class="stat-label">Servicios Activos</div>
      <div class="stat-value gold" id="stActivos">4</div>
      <div class="stat-sub">disponibles</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Tarifa Mínima</div>
      <div class="stat-value gold" id="stMin">$2.000</div>
      <div class="stat-sub">por hora</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Tarifa Máxima</div>
      <div class="stat-value gold" id="stMax">$6.000</div>
      <div class="stat-sub">por hora</div>
    </div>
  </div>

  <div class="svc-layout">
    <!-- Form -->
    <div class="card">
      <div class="card-title" id="formTitle"><span class="card-title-icon">+</span> Nuevo Servicio</div>
      <div class="form-group">
        <label class="form-label">Tipo de Vehículo</label>
        <select class="form-select" id="sTipo">
          <option value="🚗 Automóvil">🚗 Automóvil</option>
          <option value="🏍️ Motocicleta">🏍️ Motocicleta</option>
          <option value="🚛 Camión">🚛 Camión</option>
          <option value="🚌 Bus">🚌 Bus</option>
          <option value="🚐 Van">🚐 Van</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Tarifa por Hora (COP)</label>
        <input class="form-input" id="sTarifa" type="number" min="0" placeholder="Ej: 3000">
      </div>
      <div class="form-group">
        <label class="form-label">Tarifa Mínima / Primera hora (COP)</label>
        <input class="form-input" id="sMinima" type="number" min="0" placeholder="Ej: 2000">
      </div>
      <div class="form-group">
        <label class="form-label">Horario de Servicio</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
          <div>
            <label class="form-label" style="font-size:9px">Apertura</label>
            <input class="form-input" id="sHoraInicio" type="time" value="06:00">
          </div>
          <div>
            <label class="form-label" style="font-size:9px">Cierre</label>
            <input class="form-input" id="sHoraFin" type="time" value="22:00">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Descripción / Observaciones</label>
        <input class="form-input" id="sDesc" placeholder="Ej: Incluye cubierta techada">
      </div>
      <div id="sError" class="alert alert-error" style="display:none"></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-primary btn-full" id="btnGuardar" onclick="guardar()">Crear Servicio</button>
        <button class="btn btn-ghost" id="btnCancelar" style="display:none" onclick="cancelar()">Cancelar</button>
      </div>
    </div>

    <!-- Catalog -->
    <div class="card">
      <div class="card-title"><span class="card-title-icon"></span> Catálogo de Servicios</div>
      <div id="catalogoLista"></div>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  let servicios = [
    {tipo:'🚗 Automóvil',tarifa:3000,minima:2000,inicio:'06:00',fin:'22:00',desc:'Estacionamiento cubierto',estado:'Activo'},
    {tipo:'🏍️ Motocicleta',tarifa:2000,minima:1500,inicio:'06:00',fin:'22:00',desc:'Zona de motos techada',estado:'Activo'},
    {tipo:'🚛 Camión',tarifa:6000,minima:5000,inicio:'07:00',fin:'20:00',desc:'Zona de carga y descarga',estado:'Activo'},
    {tipo:'🚌 Bus',tarifa:5000,minima:4000,inicio:'06:00',fin:'21:00',desc:'Zona externa descubierta',estado:'Inactivo'},
  ];
  let editIdx=-1;

  function renderCatalogo(){
    const c=document.getElementById('catalogoLista'); c.innerHTML='';
    if(!servicios.length){c.innerHTML='<p style="color:var(--text-muted);text-align:center;padding:24px">Sin servicios registrados</p>';return;}
    servicios.forEach((s,i)=>{
      const d=document.createElement('div'); d.className='svc-item';
      d.innerHTML=`
        <div class="svc-top">
          <div class="svc-nombre">${s.tipo}</div>
          <div class="svc-acts">
            <span class="badge ${s.estado==='Activo'?'badge-emerald':'badge-crimson'} badge-dot">${s.estado}</span>
            <button class="btn-edit" onclick="editar(${i})">Editar</button>
            <button class="btn-del" onclick="desactivar(${i})">${s.estado==='Activo'?'Desactivar':'Activar'}</button>
          </div>
        </div>
        <div class="svc-row"><span>Tarifa/hora</span><span style="color:var(--gold)">$${s.tarifa.toLocaleString('es-CO')}</span></div>
        <div class="svc-row"><span>Tarifa mínima</span><span>$${s.minima.toLocaleString('es-CO')}</span></div>
        <div class="svc-row"><span>Horario</span><span>${s.inicio} – ${s.fin}</span></div>
        ${s.desc?`<div class="svc-row"><span>Descripción</span><span style="color:var(--text-secondary)">${s.desc}</span></div>`:''}`;
      c.appendChild(d);
    });
    updateStats();
  }

  function guardar(){
    const tipo=document.getElementById('sTipo').value;
    const tarifa=parseInt(document.getElementById('sTarifa').value)||0;
    const minima=parseInt(document.getElementById('sMinima').value)||0;
    const inicio=document.getElementById('sHoraInicio').value;
    const fin=document.getElementById('sHoraFin').value;
    const desc=document.getElementById('sDesc').value.trim();
    const err=document.getElementById('sError');
    if(!tarifa){err.textContent='La tarifa por hora es obligatoria.';err.style.display='flex';return;}
    err.style.display='none';
    if(editIdx>=0){ servicios[editIdx]={tipo,tarifa,minima,inicio,fin,desc,estado:servicios[editIdx].estado}; cancelar(); toast('✓ Servicio actualizado'); }
    else {
      if(servicios.find(s=>s.tipo===tipo&&s.estado==='Activo')){err.textContent='Ya existe un servicio activo con ese tipo.';err.style.display='flex';return;}
      servicios.push({tipo,tarifa,minima,inicio,fin,desc,estado:'Activo'});
      toast('✓ Servicio creado');
    }
    renderCatalogo();
    ['sTarifa','sMinima','sDesc'].forEach(id=>document.getElementById(id).value='');
  }

  function editar(i){
    editIdx=i; const s=servicios[i];
    document.getElementById('sTipo').value=s.tipo;
    document.getElementById('sTarifa').value=s.tarifa;
    document.getElementById('sMinima').value=s.minima;
    document.getElementById('sHoraInicio').value=s.inicio;
    document.getElementById('sHoraFin').value=s.fin;
    document.getElementById('sDesc').value=s.desc;
    document.getElementById('formTitle').innerHTML='<span class="card-title-icon">✏️</span> Editar Servicio';
    document.getElementById('btnGuardar').textContent='Guardar Cambios';
    document.getElementById('btnCancelar').style.display='block';
  }

  function cancelar(){
    editIdx=-1;
    document.getElementById('formTitle').innerHTML='<span class="card-title-icon">➕</span> Nuevo Servicio';
    document.getElementById('btnGuardar').textContent='Crear Servicio';
    document.getElementById('btnCancelar').style.display='none';
    ['sTarifa','sMinima','sDesc'].forEach(id=>document.getElementById(id).value='');
  }

  function desactivar(i){
    servicios[i].estado=servicios[i].estado==='Activo'?'Inactivo':'Activo';
    renderCatalogo(); toast(`✓ Servicio ${servicios[i].estado.toLowerCase()}`);
  }

  function updateStats(){
    const activos=servicios.filter(s=>s.estado==='Activo');
    document.getElementById('stActivos').textContent=activos.length;
    if(activos.length){
      document.getElementById('stMin').textContent='$'+Math.min(...activos.map(s=>s.tarifa)).toLocaleString('es-CO');
      document.getElementById('stMax').textContent='$'+Math.max(...activos.map(s=>s.tarifa)).toLocaleString('es-CO');
    }
  }

  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
  renderCatalogo();
</script>
</body>
</html>