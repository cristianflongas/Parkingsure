<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Vehículos</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    .layout { display:grid; grid-template-columns:1fr 1.6fr; gap:18px; align-items:start; }
    @media(max-width:860px){ .layout { grid-template-columns:1fr; } }
  </style>
</head>
<body>
<nav class="topbar">
  <a class="logo"><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <div class="nav-links">
    <a class="nb " href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb active" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
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
    <div class="page-eyebrow">Gestión operativa</div>
    <h1 class="page-title">Vehículos</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Registro y administración del catálogo de vehículos</p>
  </div>

  <div class="layout">
    <!-- Form -->
    <div class="card">
      <div class="card-title"><span class="card-title-icon" >+</span> Registrar Vehículo</div>
      <div class="form-group">
        <label class="form-label">Placa</label>
        <input class="form-input" id="vPlaca" placeholder="Ej: ABC-123" style="text-transform:uppercase">
      </div>
      <div class="form-group">
        <label class="form-label">Tipo de vehículo</label>
        <select class="form-select" id="vTipo">
          <option value="🚗">🚗 Automóvil</option>
          <option value="🏍️">🏍️ Motocicleta</option>
          <option value="🚛">🚛 Camión</option>
          <option value="🚌">🚌 Bus</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Marca / Modelo / Año</label>
        <input class="form-input" id="vModelo" placeholder="Ej: Toyota Corolla 2022">
      </div>
      <div class="form-group">
        <label class="form-label">Color</label>
        <input class="form-input" id="vColor" placeholder="Ej: Blanco">
      </div>
      <div class="form-group">
        <label class="form-label">Propietario</label>
        <input class="form-input" id="vOwner" placeholder="Nombre del cliente">
      </div>
      <div id="vError" class="alert alert-error" style="display:none"></div>
      <button class="btn btn-primary btn-full btn-lg" onclick="registrar()">Registrar Vehículo</button>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="card-title"><span class="card-title-icon">🚗</span> Vehículos Registrados</div>
      <div class="search-wrap">
        <span class="s-icon">🔍</span>
        <input placeholder="Buscar por placa o propietario..." oninput="filtrar()" id="buscar">
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Placa</th><th>Tipo</th><th>Modelo</th><th>Propietario</th><th>Estado</th><th>Acciones</th></tr></thead>
          <tbody id="vTable"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  let vehiculos = [
    {placa:'ABC-123',tipo:'🚗',modelo:'Corolla 2020',  owner:'Juan Pérez',  estado:'Activo'},
    {placa:'XYZ-789',tipo:'🏍️',modelo:'Pulsar 200',   owner:'María López', estado:'Inactivo'},
    {placa:'PLT-456',tipo:'🚗',modelo:'Spark GT 2021', owner:'Carlos Ruiz', estado:'Activo'},
    {placa:'MOT-001',tipo:'🏍️',modelo:'CB190R 2022',  owner:'Laura Soto',  estado:'Activo'},
    {placa:'GHI-654',tipo:'🚗',modelo:'Sandero 2019',  owner:'Pedro Gómez', estado:'Inactivo'},
  ];
  function renderTabla(lista){
    const tb=document.getElementById('vTable'); tb.innerHTML='';
    if(!lista.length){tb.innerHTML='<tr><td colspan="6" style="text-align:center;color:var(--text-muted);padding:28px">Sin resultados</td></tr>';return;}
    lista.forEach((v,i)=>{
      const tr=document.createElement('tr');
      tr.innerHTML=`<td><strong style="font-family:monospace;font-size:13px">${v.placa}</strong></td>
        <td>${v.tipo}</td><td style="color:var(--text-secondary)">${v.modelo}</td><td>${v.owner}</td>
        <td><span class="badge ${v.estado==='Activo'?'badge-emerald':'badge-crimson'} badge-dot">${v.estado}</span></td>
        <td style="display:flex;gap:6px">
          <button class="btn-edit" onclick="toggle(${i})">${v.estado==='Activo'?'Desactivar':'Activar'}</button>
          <button class="btn-del" onclick="eliminar(${i})">Eliminar</button>
        </td>`;
      tb.appendChild(tr);
    });
  }
  function registrar(){
    const placa=document.getElementById('vPlaca').value.trim().toUpperCase();
    const tipo=document.getElementById('vTipo').value;
    const modelo=document.getElementById('vModelo').value.trim();
    const color=document.getElementById('vColor').value.trim();
    const owner=document.getElementById('vOwner').value.trim();
    const err=document.getElementById('vError');
    if(!placa||!modelo||!owner){err.textContent='Completa todos los campos obligatorios.';err.style.display='flex';return;}
    if(vehiculos.find(v=>v.placa===placa)){err.textContent=`La placa ${placa} ya está registrada en el sistema.`;err.style.display='flex';return;}
    err.style.display='none';
    vehiculos.push({placa,tipo,modelo:modelo+(color?' · '+color:''),owner,estado:'Activo'});
    renderTabla(vehiculos);
    ['vPlaca','vModelo','vColor','vOwner'].forEach(id=>document.getElementById(id).value='');
    toast(`✓ Vehículo ${placa} registrado exitosamente`);
  }
  function toggle(i){vehiculos[i].estado=vehiculos[i].estado==='Activo'?'Inactivo':'Activo';renderTabla(vehiculos);toast('✓ Estado actualizado');}
  function eliminar(i){if(!confirm(`¿Eliminar el vehículo ${vehiculos[i].placa}?`))return;const p=vehiculos[i].placa;vehiculos.splice(i,1);renderTabla(vehiculos);toast(`✓ ${p} eliminado`);}
  function filtrar(){const q=document.getElementById('buscar').value.toLowerCase();renderTabla(vehiculos.filter(v=>v.placa.toLowerCase().includes(q)||v.owner.toLowerCase().includes(q)));}
  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
  renderTabla(vehiculos);
</script>
</body>
</html>