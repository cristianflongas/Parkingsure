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
    }

    .lot-header {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      margin-bottom: 24px;
    }
    .lot-arrow {
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-muted);
    }
    .lot-label-pill {
      background: var(--gold-dim);
      border: 1px solid rgba(232,184,75,.2);
      border-radius: 20px;
      padding: 4px 16px;
      font-size: 10px;
      font-weight: 700;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--gold);
    }

    .lot-grid {
      display: grid;
      grid-template-columns: repeat(8, 1fr);
      gap: 10px;
    }

    /* Module cells */
    .module {
      aspect-ratio: 1;
      border-radius: 10px;
      border: 1.5px solid;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: transform .15s, box-shadow .15s;
      padding: 6px;
      gap: 2px;
    }
    .module:hover { transform: scale(1.06); z-index: 2; }

    .module.libre {
      background: rgba(16,185,129,.07);
      border-color: rgba(16,185,129,.3);
      color: var(--emerald);
    }
    .module.libre:hover { box-shadow: 0 4px 18px rgba(16,185,129,.2); border-color: var(--emerald); }

    .module.ocupado {
      background: rgba(239,68,68,.07);
      border-color: rgba(239,68,68,.3);
      color: var(--crimson);
    }
    .module.ocupado:hover { box-shadow: 0 4px 18px rgba(239,68,68,.2); border-color: var(--crimson); }

    .module.nuevo {
      background: transparent;
      border: 1.5px dashed var(--border-md);
      color: var(--text-muted);
    }
    .module.nuevo:hover { border-color: var(--gold); color: var(--gold); }

    .mod-icon  { font-size: 18px; line-height: 1; }
    .mod-id    { font-size: 8.5px; font-weight: 700; letter-spacing: .5px; opacity: .85; }
    .mod-plate {
      font-size: 7px; font-weight: 800; letter-spacing: .8px;
      background: rgba(0,0,0,.2); padding: 1px 4px; border-radius: 3px;
      margin-top: 1px;
    }

    @media(max-width:900px){ .lot-grid { grid-template-columns: repeat(4,1fr); } }
    @media(max-width:600px){ .lot-grid { grid-template-columns: repeat(3,1fr); } .legend { gap:12px; } }
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
    <p class="page-sub">Estado de módulos en tiempo real — selecciona un espacio para asignar o liberar</p>
  </div>

  <!-- Stats -->
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

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="legend">
      <div class="leg"><div class="leg-sq" style="background:rgba(16,185,129,.5);border:1px solid var(--emerald)"></div>Libre</div>
      <div class="leg"><div class="leg-sq" style="background:rgba(239,68,68,.5);border:1px solid var(--crimson)"></div>Ocupado</div>
    </div>
    <button class="btn btn-primary" onclick="openAdd()">+ Agregar Módulo</button>
  </div>

  <!-- Lot -->
  <div class="lot-shell">
    <div class="lot-header">
      <span class="lot-arrow">⬅ ENTRADA</span>
      <span class="lot-label-pill">🅿️ Parqueadero Doña Luz</span>
      <span class="lot-arrow">SALIDA ➡</span>
    </div>
    <div class="lot-grid" id="lotGrid"></div>
  </div>
</div>

<!-- Modal: Module detail -->
<div class="overlay" id="modOverlay">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="modTitle">Módulo</div>
      <div class="modal-sub" id="modSub"></div>
    </div>
    <div id="modContent"></div>
    <div class="modal-actions" id="modActions"></div>
  </div>
</div>

<!-- Modal: Add module -->
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
  let modules = [
    {id:'A-01',t:'🚗',s:'ocupado',p:'PLT-456'},{id:'A-02',t:'🚗',s:'libre'},
    {id:'A-03',t:'🚗',s:'ocupado',p:'ABC-123'},{id:'A-04',t:'🚗',s:'libre'},
    {id:'B-01',t:'🏍️',s:'libre'},{id:'B-02',t:'🏍️',s:'ocupado',p:'MOT-001'},
    {id:'B-03',t:'🏍️',s:'libre'},{id:'B-04',t:'🏍️',s:'libre'},
    {id:'C-01',t:'🚗',s:'libre'},{id:'C-02',t:'🚗',s:'ocupado',p:'GHI-654'},
    {id:'C-03',t:'🚗',s:'libre'},{id:'C-04',t:'🚗',s:'libre'},
    {id:'D-01',t:'🚛',s:'libre'},{id:'D-02',t:'🚛',s:'libre'},
    {id:'E-01',t:'🚌',s:'libre'},{id:'E-02',t:'🚌',s:'libre'},
  ];

  function renderLot(){
    const g=document.getElementById('lotGrid'); g.innerHTML='';
    modules.forEach((m,i)=>{
      const d=document.createElement('div');
      d.className=`module ${m.s}`;
      d.innerHTML=`<div class="mod-icon">${m.t}</div><div class="mod-id">${m.id}</div>${m.p?`<div class="mod-plate">${m.p}</div>`:''}`;
      d.onclick=()=>openMod(i);
      g.appendChild(d);
    });
    const add=document.createElement('div');
    add.className='module nuevo';
    add.innerHTML='<div style="font-size:20px">+</div><div class="mod-id">NUEVO</div>';
    add.onclick=openAdd;
    g.appendChild(add);
    syncStats();
  }

  function openMod(i){
    const m=modules[i];
    document.getElementById('modTitle').textContent=`Módulo ${m.id}`;
    document.getElementById('modSub').textContent=`Tipo: ${m.t}  ·  ${m.s.charAt(0).toUpperCase()+m.s.slice(1)}`;
    const badgeClass = m.s==='libre'?'badge-emerald':'badge-crimson';
    document.getElementById('modContent').innerHTML=`
      <div class="info-row"><span class="ir-label">Identificador</span><span class="ir-value">${m.id}</span></div>
      <div class="info-row"><span class="ir-label">Tipo de vehículo</span><span class="ir-value">${m.t}</span></div>
      <div class="info-row"><span class="ir-label">Estado</span><span class="ir-value"><span class="badge ${badgeClass} badge-dot">${m.s}</span></span></div>
      ${m.p?`<div class="info-row"><span class="ir-label">Placa asignada</span><span class="ir-value" style="font-family:monospace;background:var(--surface-3);padding:2px 10px;border-radius:4px">${m.p}</span></div>`:''}`;
    const ac=document.getElementById('modActions');
    if(m.s==='libre')
      ac.innerHTML=`<button class="btn btn-ghost" onclick="closeO('modOverlay')">Cancelar</button><button class="btn btn-primary" style="flex:1" onclick="assignMod(${i})">Asignar Vehículo</button>`;
    else
      ac.innerHTML=`<button class="btn btn-ghost" onclick="closeO('modOverlay')">Cancelar</button><button class="btn btn-danger" style="flex:1" onclick="freeMod(${i})">Liberar Módulo</button>`;
    document.getElementById('modOverlay').classList.add('open');
  }

  function assignMod(i){
    const p=prompt('Ingresa la placa del vehículo:','');
    if(p&&p.trim()){modules[i].s='ocupado';modules[i].p=p.trim().toUpperCase();closeO('modOverlay');renderLot();toast(`✓ Módulo ${modules[i].id} asignado a ${modules[i].p}`);}
  }
  function freeMod(i){
    const id=modules[i].id;modules[i].s='libre';delete modules[i].p;closeO('modOverlay');renderLot();toast(`✓ Módulo ${id} liberado`);
  }
  function openAdd(){ document.getElementById('addOverlay').classList.add('open'); }
  function addModule(){
    const id=document.getElementById('newId').value.trim();
    const t=document.getElementById('newType').value;
    if(!id)return;
    modules.push({id,t,s:'libre'});
    closeO('addOverlay');
    document.getElementById('newId').value='';
    renderLot();
    toast(`✓ Módulo ${id} agregado`);
  }
  function closeO(id){ document.getElementById(id).classList.remove('open'); }
  function syncStats(){
    const total=modules.length, busy=modules.filter(m=>m.s==='ocupado').length;
    document.getElementById('st-total').textContent=total;
    document.getElementById('st-free').textContent=total-busy;
    document.getElementById('st-busy').textContent=busy;
  }
  function toast(msg){ const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200); }
  document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open');}));
  renderLot();
</script>
</body>
</html>