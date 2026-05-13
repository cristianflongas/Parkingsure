<?php
// Control de acceso - Solo administradores pueden acceder
session_start();

// Verificar si hay sesión activa
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Verificar rol - Solo ADMINISTRADOR puede acceder
if ($_SESSION['rol'] !== 'ADMINISTRADOR') {
    header("Location: dashboard.php?error=sin_permisos");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Servicios</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    /* Service catalog cards */
    .svc-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:14px; }
    .svc-card {
      background:var(--surface-2); border:1px solid var(--border);
      border-radius:var(--r-md); padding:20px;
      transition:border-color .18s, transform .15s;
      position:relative; overflow:hidden;
    }
    .svc-card::before { content:''; position:absolute; top:0; left:0; width:3px; height:100%; }
    .svc-card.activo::before  { background:var(--text-secondary); }
    .svc-card.inactivo::before{ background:var(--text-muted); }
    .svc-card:hover { border-color:var(--border-md); transform:translateY(-2px); }
    .svc-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; }
    .svc-nombre { font-size:15px; font-weight:700; color:var(--text-primary); }
    .svc-rate { font-family:'Syne',sans-serif; font-size:26px; font-weight:800; color:var(--gold); }
    .svc-rate-label { font-size:11px; color:var(--text-muted); margin-bottom:12px; }
    .svc-row { display:flex; justify-content:space-between; padding:5px 0; font-size:12.5px; border-bottom:1px solid var(--border); }
    .svc-row:last-child { border:none; }
    .svc-row span:first-child { color:var(--text-muted); }
    .svc-row span:last-child  { font-weight:600; }
    .svc-actions { display:flex; gap:8px; margin-top:14px; padding-top:12px; border-top:1px solid var(--border); }
  </style>
</head>
<body>
<nav class="topbar">
  <a class="logo"><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <button class="hamburger" id="hamburger-btn">
    <span></span>
    <span></span>
    <span></span>
  </button>
  <div class="nav-links" id="nav-links">
    <a class="nb" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb active" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <a class="nb" href="reportes.php"><span class="nav-icon"></span> Reportes</a>
  </div>
  <div class="nav-right">
    <div class="user-avatar"><?php echo htmlspecialchars(substr($_SESSION['nombre'] ?? 'U', 0, 1)); ?></div>
    <div class="user-info"><div class="u-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></div><div class="u-role"><?php echo htmlspecialchars($_SESSION['rol'] ?? 'Operador'); ?></div></div>
    <a class="btn-logout" href="../../controllers/logout.php">Salir</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <div class="page-eyebrow">Configuración</div>
    <h1 class="page-title">Gestión de Servicios</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Catálogo de tipos de servicio y tarifas del parqueadero</p>
  </div>

  <div class="stats-grid stats-grid-3" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-label">Servicios Activos</div><div class="stat-value gold" id="stActivos">Cargando...</div><div class="stat-sub">disponibles</div></div>
    <div class="stat-card"><div class="stat-label">Tarifa Mínima</div><div class="stat-value gold" id="stMin">$2.000</div><div class="stat-sub">por hora</div></div>
    <div class="stat-card"><div class="stat-label">Tarifa Máxima</div><div class="stat-value gold" id="stMax">$6.000</div><div class="stat-sub">por hora</div></div>
  </div>

  <!-- Toolbar -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px">
    <div style="display:flex;gap:8px">
      <button class="btn btn-ghost" id="filtroTodos"   onclick="setFiltro('todos')"   style="font-size:12px">Todos</button>
      <button class="btn btn-ghost" id="filtroActivos" onclick="setFiltro('activos')" style="font-size:12px">Activos</button>
    </div>
    <button class="btn btn-primary" onclick="abrirNuevo()">+ Nuevo Servicio</button>
  </div>

  <div id="catalogoGrid"></div>
</div>

<!-- MODAL: Crear / Editar servicio -->
<div class="overlay" id="svcOverlay">
  <div class="modal" style="max-width:480px">
    <div class="modal-header">
      <div class="modal-title" id="svcModalTitle">Nuevo Servicio</div>
      <div class="modal-sub" id="svcModalSub">Completa los datos del servicio</div>
    </div>
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
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
      <div class="form-group">
        <label class="form-label">Tarifa por Hora (COP)</label>
        <input class="form-input" id="sTarifa" type="number" min="0" placeholder="Ej: 3000">
      </div>
      <div class="form-group">
        <label class="form-label">Tarifa Mínima (COP)</label>
        <input class="form-input" id="sMinima" type="number" min="0" placeholder="Ej: 2000">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Horario de Servicio</label>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <div><label class="form-label" style="font-size:9px">Apertura</label><input class="form-input" id="sHoraInicio" type="time" value="06:00"></div>
        <div><label class="form-label" style="font-size:9px">Cierre</label><input class="form-input" id="sHoraFin" type="time" value="22:00"></div>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Descripción</label>
      <input class="form-input" id="sDesc" placeholder="Ej: Incluye cubierta techada">
    </div>
    <div id="sError" class="alert alert-error" style="display:none"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeO('svcOverlay')">Cancelar</button>
      <button class="btn btn-primary" style="flex:1" id="svcGuardarBtn" onclick="guardarServicio()">Crear Servicio</button>
    </div>
  </div>
</div>

<!-- MODAL: Confirmar toggle -->
<div class="overlay" id="toggleOverlay">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <div class="modal-title" id="toggleTitle">Confirmar acción</div>
      <div class="modal-sub" id="toggleSub"></div>
    </div>
    <div id="toggleBody" style="margin:16px 0;font-size:14px;color:var(--text-secondary);line-height:1.6"></div>
    <div class="modal-actions">
      <button class="btn btn-ghost" onclick="closeO('toggleOverlay')">Cancelar</button>
      <button class="btn btn-primary" style="flex:1" id="toggleConfirmBtn" onclick="ejecutarToggle()">Confirmar</button>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  let servicios = [];
  let editIdx = -1;
  let filtroActual = 'todos';
  let pendingToggleIdx = -1;

  // Cargar servicios al inicio
  async function cargarServicios() {
    try {
        // Mostrar valores por defecto mientras carga
        const stActivosElement = document.getElementById('stActivos');
        if (stActivosElement) stActivosElement.textContent = 'Cargando...';
        
        const response = await fetch('../../controllers/dashboardapi.php?action=getServicios');
        const result = await response.json();
        
        if (result.success) {
            servicios = result.data;
            console.log(' Servicios cargados:', servicios.length, 'servicios');
            console.log(' Contenido de servicios:', servicios);
            renderCatalogo();
            updateStats();
            console.log(' Servicios cargados desde BD:', servicios.length);
        } else {
            console.error(' Error al cargar servicios:', result.message);
        }
    } catch (error) {
      console.error(' Error de conexión:', error);
      console.error(' Stack trace:', error.stack);
    }
  }

  function renderCatalogo(){
    
    const lista = filtroActual==='activos' ? servicios.filter(s=>s.estado==='ACTIVO') : servicios;
    
    const grid  = document.getElementById('catalogoGrid');
    
    if(!lista.length){
      grid.innerHTML=`<div style="text-align:center;padding:48px;color:var(--text-muted)"><div style="font-size:40px;margin-bottom:12px">🅿️</div><div>Sin servicios registrados</div></div>`;
      updateStats(); return;
    }
    
    const g = document.createElement('div'); g.className='svc-grid';
    
    lista.forEach((s,li)=>{
      const i = servicios.indexOf(s);
      const d = document.createElement('div');
      d.className=`svc-card ${s.estado.toLowerCase()}`;
      d.innerHTML=` 
        <div class="svc-top">
          <div class="svc-nombre">${s.nombre_tipo_servicio}</div>
          <span class="badge ${s.estado==='ACTIVO'?'badge-emerald':'badge-crimson'} badge-dot">${s.estado}</span>
        </div>
        <div class="svc-rate">$${parseFloat(s.tarifa).toLocaleString('es-CO')}</div>
        <div class="svc-rate-label">por hora</div>
        <div class="svc-actions">
          <button class="btn-edit" onclick="abrirEditar(${i})">✏️ Editar</button>
          <button class="btn-del" onclick="pedirToggle(${i})">${s.estado==='ACTIVO'?'Desactivar':'Activar'}</button>
        </div>`;
      d.onclick = () => abrirEditar(i);
      g.appendChild(d);
    });
    
    grid.innerHTML='';
    grid.appendChild(g);
    updateStats();
  }

  function setFiltro(f){
    filtroActual=f;
    document.getElementById('filtroTodos').style.borderColor   = f==='todos'?'var(--gold)':'';
    document.getElementById('filtroActivos').style.borderColor = f==='activos'?'var(--gold)':'';
    renderCatalogo();
  }

  function abrirNuevo(){
    editIdx=-1;
    document.getElementById('svcModalTitle').textContent='Nuevo Servicio';
    document.getElementById('svcModalSub').textContent='Completa los datos del servicio';
    document.getElementById('svcGuardarBtn').textContent='Crear Servicio';
    ['sTarifa','sMinima','sDesc'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('sTipo').selectedIndex=0;
    document.getElementById('sHoraInicio').value='06:00';
    document.getElementById('sHoraFin').value='22:00';
    document.getElementById('sError').style.display='none';
    document.getElementById('svcOverlay').classList.add('open');
  }

  function abrirEditar(i){
    editIdx=i; const s=servicios[i];
    document.getElementById('svcModalTitle').textContent='Editar Servicio';
    document.getElementById('svcModalSub').textContent=s.tipo;
    document.getElementById('svcGuardarBtn').textContent='Guardar Cambios';
    document.getElementById('sTipo').value=s.tipo;
    document.getElementById('sTarifa').value=s.tarifa;
    document.getElementById('sMinima').value=s.minima;
    document.getElementById('sHoraInicio').value=s.inicio;
    document.getElementById('sHoraFin').value=s.fin;
    document.getElementById('sDesc').value=s.desc||'';
    document.getElementById('sError').style.display='none';
    document.getElementById('svcOverlay').classList.add('open');
  }

  function guardarServicio(){
    const tipo  =document.getElementById('sTipo').value;
    const tarifa=parseInt(document.getElementById('sTarifa').value)||0;
    const minima=parseInt(document.getElementById('sMinima').value)||0;
    const inicio=document.getElementById('sHoraInicio').value;
    const fin   =document.getElementById('sHoraFin').value;
    const desc  =document.getElementById('sDesc').value.trim();
    const err   =document.getElementById('sError');
    if(!tarifa){err.textContent='La tarifa por hora es obligatoria.';err.style.display='flex';return;}
    if(editIdx<0 && servicios.find(s=>s.tipo===tipo&&s.estado==='Activo')){
      err.textContent='Ya existe un servicio activo para ese tipo de vehículo.';err.style.display='flex';return;
    }
    err.style.display='none';
    if(editIdx>=0){
      servicios[editIdx]={...servicios[editIdx],tipo,tarifa,minima,inicio,fin,desc};
      toast('✓ Servicio actualizado');
    } else {
      servicios.push({tipo,tarifa,minima,inicio,fin,desc,estado:'Activo'});
      toast('✓ Servicio creado');
    }
    closeO('svcOverlay');
    renderCatalogo();
  }

  function pedirToggle(i){
    pendingToggleIdx=i;
    const s=servicios[i];
    const accion = s.estado==='ACTIVO'?'desactivar':'activar';
    document.getElementById('toggleTitle').textContent=`¿${accion.charAt(0).toUpperCase()+accion.slice(1)} servicio?`;
    document.getElementById('toggleSub').textContent=s.nombre_tipo_servicio;
    document.getElementById('toggleBody').textContent=`Estás a punto de ${accion} el servicio de ${s.nombre_tipo_servicio}. ${s.estado==='ACTIVO'?'No estará disponible para nuevos cobros.':'Estará disponible para nuevos cobros.'}`;
    document.getElementById('toggleConfirmBtn').textContent=`Sí, ${accion}`;
    document.getElementById('toggleOverlay').classList.add('open');
  }

  function ejecutarToggle(){
    const s=servicios[pendingToggleIdx];
    s.estado=s.estado==='ACTIVO'?'INACTIVO':'ACTIVO';
    closeO('toggleOverlay');
    renderCatalogo();
    toast(`✓ Servicio ${s.estado.toLowerCase()}`);
  }

  function updateStats(){
    const activos=servicios.filter(s=>s.estado==='ACTIVO');
    document.getElementById('stActivos').textContent=activos.length;
    if(activos.length){
      document.getElementById('stMin').textContent='$'+Math.min(...activos.map(s=>parseFloat(s.tarifa))).toLocaleString('es-CO');
      document.getElementById('stMax').textContent='$'+Math.max(...activos.map(s=>parseFloat(s.tarifa))).toLocaleString('es-CO');
    } else {
      document.getElementById('stMin').textContent='$0';
      document.getElementById('stMax').textContent='$0';
    }
  }

  function closeO(id){document.getElementById(id).classList.remove('open');}
  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
  document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open');}));
  
  // Cargar servicios al iniciar la página
  document.addEventListener('DOMContentLoaded', function() {
    cargarServicios();
  });
</script>

<script>
  // Hamburger menu toggle
  const hamburgerBtn = document.getElementById('hamburger-btn');
  const navLinks = document.getElementById('nav-links');

  hamburgerBtn.addEventListener('click', () => {
    navLinks.classList.toggle('active');
    hamburgerBtn.classList.toggle('active');
  });

  // Close menu when clicking outside or on a link
  document.addEventListener('click', (e) => {
    if (!hamburgerBtn.contains(e.target) && !navLinks.contains(e.target)) {
      navLinks.classList.remove('active');
      hamburgerBtn.classList.remove('active');
    }
  });

  // Close menu when clicking on a nav link
  navLinks.addEventListener('click', (e) => {
    if (e.target.classList.contains('nb')) {
      navLinks.classList.remove('active');
      hamburgerBtn.classList.remove('active');
    }
  });
</script>
</body>
</html>