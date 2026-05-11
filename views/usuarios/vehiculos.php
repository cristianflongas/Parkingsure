<?php
// Control de sesión y rol
session_start();
$rolUsuario = $_SESSION['rol'] ?? 'OPERADOR';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Vehículos</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    .layout { display:grid; grid-template-columns:1fr 1.8fr; gap:18px; align-items:start; }
    @media(max-width:860px){ .layout { grid-template-columns:1fr; } }

    /* Vehicle cards grid */
    .veh-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px; }
    .veh-card {
      background:var(--surface-2); border:1px solid var(--border);
      border-radius:var(--r-md); padding:16px;
      transition:border-color .18s, transform .15s;
      cursor:pointer;
    }
    .veh-card:hover { border-color:var(--border-md); transform:translateY(-2px); }
    .veh-card.activo  { border-left:3px solid var(--text-secondary); }
    .veh-card.inactivo{ border-left:3px solid var(--text-muted); }
    .vc-top { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; }
    .vc-placa {
      font-family:monospace; font-size:16px; font-weight:800;
      letter-spacing:1.5px; color:var(--text-primary);
      background:var(--surface-3); padding:4px 10px; border-radius:6px;
    }
    .vc-tipo { font-size:22px; }
    .vc-model { font-size:12px; color:var(--text-secondary); margin-bottom:4px; }
    .vc-owner { font-size:13px; font-weight:600; color:var(--text-primary); }
    .vc-foot { display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid var(--border); }

    /* Empty state */
    .empty-state { text-align:center; padding:48px 24px; color:var(--text-muted); }
    .empty-icon { font-size:40px; margin-bottom:12px; }
    .empty-text { font-size:14px; }
    .stats-grid{
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 1rem;
    }
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
    <a class="nb active" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <?php if ($rolUsuario === 'ADMINISTRADOR'): ?>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <?php endif; ?>
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
    <div class="page-eyebrow">Gestión operativa</div>
    <h1 class="page-title">Vehículos</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Registro y administración del catálogo de vehículos</p>
  </div>

  <!-- Stats -->
  <div class="stats-grid stats-grid-4" style="margin-bottom:20px">
    <div class="stat-card"><div class="stat-label">Total Registrados</div><div class="stat-value gold" id="stTotal">5</div><div class="stat-sub">vehículos</div></div>
    <div class="stat-card"><div class="stat-label">Activos</div><div class="stat-value gold" id="stActivos">3</div><div class="stat-sub">en sistema</div></div>
    <div class="stat-card"><div class="stat-label">Inactivos</div><div class="stat-value gold" id="stInactivos">2</div><div class="stat-sub">desactivados</div></div>
    <div class="stat-card"><div class="stat-label">En Parqueadero</div><div class="stat-value gold" id="stDentro">3</div><div class="stat-sub">actualmente</div></div>
  </div>

  <div class="layout">
    <!-- Form card -->
    <div class="card">
      <div class="card-title"><span class="card-title-icon">+</span> Registrar Vehículo</div>
      <div class="form-group">
        <label class="form-label">Placa</label>
        <input class="form-input" id="vPlaca" placeholder="Ej: ABC-123" style="text-transform:uppercase;font-family:monospace;font-size:15px;letter-spacing:1px">
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
        <input class="form-input" id="vColor" placeholder="Ej: Blanco perla">
      </div>
      <div class="form-group">
        <label class="form-label">Tipo de Registro</label>
        <select class="form-select" id="tipoRegistro" onchange="toggleClienteForm()">
          <option value="existente">Cliente Existente</option>
          <option value="nuevo">Nuevo Cliente</option>
        </select>
      </div>
      <div id="clienteExistenteForm">
        <div class="form-group">
          <label class="form-label">Seleccionar Cliente</label>
          <select class="form-select" id="selectCliente">
            <option value="">Seleccione un cliente...</option>
          </select>
        </div>
      </div>
      <div id="nuevoClienteForm" style="display:none">
        <div class="form-group">
          <label class="form-label">Nombre del Cliente</label>
          <input class="form-input" id="nombreCliente" placeholder="Nombre completo del cliente">
        </div>
        <div class="form-group">
          <label class="form-label">Cédula</label>
          <input class="form-input" id="cedulaCliente" placeholder="Número de cédula">
        </div>
        <div class="form-group">
          <label class="form-label">Teléfono (opcional)</label>
          <input class="form-input" id="telCliente" placeholder="Ej: 3001234567" type="tel">
        </div>
        <div class="form-group">
          <label class="form-label">Correo (opcional)</label>
          <input class="form-input" id="correoCliente" placeholder="correo@ejemplo.com" type="email">
        </div>
      </div>
      <div id="vError" class="alert alert-error" style="display:none"></div>
      <button class="btn btn-primary btn-full btn-lg" onclick="registrar()">Registrar Vehículo</button>
    </div>

    <!-- List card -->
    <div class="card">
      <div class="card-title" style="flex-wrap:wrap;gap:10px">
        <span class="card-title-icon">🚗</span> Vehículos Registrados
        <div style="margin-left:auto;display:flex;gap:8px;align-items:center">
          <select class="form-select" id="filtroEstado" onchange="renderVehiculos()" style="padding:6px 10px;font-size:12px;width:auto">
            <option value="todos">Todos</option>
            <option value="Activo">Activos</option>
            <option value="Inactivo">Inactivos</option>
          </select>
        </div>
      </div>
      <div class="search-wrap" style="margin-bottom:14px">
        <span class="s-icon">🔍</span>
        <input placeholder="Buscar por placa, modelo o propietario…" oninput="renderVehiculos()" id="buscar">
      </div>
      <div id="vehGrid"></div>
      <div class="pagination" id="vehiculos-pagination">
        <!-- Los botones de paginación se cargarán dinámicamente -->
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Detalle / Editar vehículo -->
<div class="overlay" id="detOverlay">
  <div class="modal" style="max-width:460px">
    <div class="modal-header">
      <div class="modal-title" id="detTitle">Detalle del Vehículo</div>
      <div class="modal-sub" id="detSub"></div>
    </div>

    <!-- Vista detalle -->
    <div id="detView">
      <div style="text-align:center;font-size:52px;margin:8px 0 16px" id="detIcon">🚗</div>
      <div id="detRows"></div>
      <div class="modal-actions" style="margin-top:16px">
        <button class="btn btn-ghost" onclick="closeO('detOverlay')">Cerrar</button>
        <button class="btn btn-secondary" onclick="switchToEdit()">✏️ Editar</button>
        <button class="btn btn-danger" id="detToggleBtn" onclick="toggleFromModal()">Desactivar</button>
        <button class="btn btn-del" onclick="eliminarFromModal()">🗑 Eliminar</button>
      </div>
    </div>

    <!-- Vista edición -->
    <div id="detEdit" style="display:none">
      <div class="form-group"><label class="form-label">Tipo</label>
        <select class="form-select" id="eTipoVeh">
          <option value="🚗">🚗 Automóvil</option><option value="🏍️">🏍️ Motocicleta</option>
          <option value="🚛">🚛 Camión</option><option value="🚌">🚌 Bus</option>
        </select></div>
      <div class="form-group"><label class="form-label">Modelo</label><input class="form-input" id="eModelo"></div>
      <div class="form-group"><label class="form-label">Color</label><input class="form-input" id="eColor"></div>
      <div class="form-group"><label class="form-label">Propietario</label><input class="form-input" id="eOwner"></div>
      <div class="form-group"><label class="form-label">Teléfono</label><input class="form-input" id="eTel" type="tel"></div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="switchToView()">Cancelar</button>
        <button class="btn btn-primary" style="flex:1" onclick="guardarEdicion()">Guardar Cambios</button>
      </div>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  let vehiculos = [];
  let currentDetIdx = -1;

  // Variables globales para paginación de vehículos
  let currentVehiculosPage = 1;
  let totalVehiculosPages = 1;
  let totalVehiculosItems = 0;

  // Cargar vehículos desde la base de datos con paginación
  async function cargarVehiculos(page = 1) {
    try {
      currentVehiculosPage = page;
      console.log('Cargando vehículos página:', page); // Debug
      
      const response = await fetch(`../../controllers/vehiculosapi.php?action=getAll`);
      const result = await response.json();
      
      console.log('Respuesta de vehículos:', result); // Debug
      
      if (result.success) {
        // Mapear datos de vehículos
        const nuevosVehiculos = result.data.map(v => ({
          placa: v.placa,
          tipo: '🚗', // Por defecto, se puede mejorar después
          modelo: v.modelo || `${v.marca || ''} ${v.modelo || ''}`.trim(),
          color: v.color || '',
          owner: v.nombre_cliente || 'Sin asignar',
          tel: v.telefono_cliente || '',
          estado: 'Activo', // Por defecto
          dentro: false // Por defecto
        }));

        console.log('Vehículos mapeados:', nuevosVehiculos); // Debug

        // Si es la primera página, reemplazar todo; si no, añadir
        if (page === 1) {
          vehiculos = nuevosVehiculos;
        } else {
          // Mantener vehículos de páginas anteriores para búsqueda
          vehiculos = [...vehiculos, ...nuevosVehiculos];
        }
        
        console.log('Total vehículos:', vehiculos.length); // Debug
        renderVehiculos();
        
        // Actualizar paginación
        if (result.pagination) {
          totalVehiculosPages = result.pagination.totalPages;
          totalVehiculosItems = result.pagination.totalItems;
          renderPagination('vehiculos-pagination', page, totalVehiculosPages, cargarVehiculos);
        }
      } else {
        console.error('Error cargando vehículos:', result.message);
        const grid = document.getElementById('vehGrid');
        grid.innerHTML = `<div class="empty-state"><div class="empty-icon">⚠️</div><div class="empty-text">Error: ${result.message}</div></div>`;
      }
    } catch (error) {
      console.error('Error de red:', error);
      const grid = document.getElementById('vehGrid');
      grid.innerHTML = `<div class="empty-state"><div class="empty-icon">🔌</div><div class="empty-text">Error de conexión</div></div>`;
    }
  }

  function renderVehiculos(){
    const q      = (document.getElementById('buscar').value||'').toLowerCase();
    const filtro = document.getElementById('filtroEstado').value;
    const lista  = vehiculos.filter(v => {
      const match = v.placa.toLowerCase().includes(q) || v.modelo.toLowerCase().includes(q) || v.owner.toLowerCase().includes(q);
      const est   = filtro==='todos' || v.estado===filtro;
      return match && est;
    });
    const grid = document.getElementById('vehGrid');
    if(!lista.length){
      grid.innerHTML=`<div class="empty-state"><div class="empty-icon">🔍</div><div class="empty-text">Sin resultados para tu búsqueda</div></div>`;
      return;
    }
    grid.innerHTML='';
    const g = document.createElement('div'); g.className='veh-grid';
    lista.forEach(v => {
      const realIdx = vehiculos.indexOf(v);
      const d = document.createElement('div');
      d.className=`veh-card ${v.estado.toLowerCase()}`;
      d.innerHTML=`
        <div class="vc-top">
          <span class="vc-placa">${v.placa}</span>
          <span class="vc-tipo">${v.tipo}</span>
        </div>
        <div class="vc-model">${v.modelo}${v.color?' · '+v.color:''}</div>
        <div class="vc-owner">👤 ${v.owner}</div>
        <div class="vc-foot">
          <span class="badge ${v.estado==='Activo'?'badge-emerald':'badge-crimson'} badge-dot">${v.estado}</span>
          ${v.dentro?'<span class="badge badge-gold badge-dot" style="font-size:10px">En parqueadero</span>':''}
        </div>`;
      d.onclick = () => openDet(realIdx);
      g.appendChild(d);
    });
    grid.appendChild(g);
    syncStats();
  }

  function syncStats(){
    document.getElementById('stTotal').textContent    = vehiculos.length;
    document.getElementById('stActivos').textContent  = vehiculos.filter(v=>v.estado==='Activo').length;
    document.getElementById('stInactivos').textContent= vehiculos.filter(v=>v.estado==='Inactivo').length;
    document.getElementById('stDentro').textContent   = vehiculos.filter(v=>v.dentro).length;
  }

  function openDet(i){
    currentDetIdx = i;
    const v = vehiculos[i];
    document.getElementById('detTitle').textContent = v.placa;
    document.getElementById('detSub').textContent   = `${v.tipo}  ·  ${v.estado}`;
    document.getElementById('detIcon').textContent  = v.tipo;
    document.getElementById('detRows').innerHTML = `
      <div class="info-row"><span class="ir-label">Placa</span><span class="ir-value" style="font-family:monospace">${v.placa}</span></div>
      <div class="info-row"><span class="ir-label">Modelo</span><span class="ir-value">${v.modelo}</span></div>
      <div class="info-row"><span class="ir-label">Color</span><span class="ir-value">${v.color||'—'}</span></div>
      <div class="info-row"><span class="ir-label">Propietario</span><span class="ir-value">${v.owner}</span></div>
      <div class="info-row"><span class="ir-label">Teléfono</span><span class="ir-value">${v.tel||'—'}</span></div>
      <div class="info-row"><span class="ir-label">Estado</span><span class="ir-value"><span class="badge ${v.estado==='Activo'?'badge-emerald':'badge-crimson'} badge-dot">${v.estado}</span></span></div>
      <div class="info-row"><span class="ir-label">En parqueadero</span><span class="ir-value">${v.dentro?'<span class="badge badge-gold badge-dot">Sí</span>':'No'}</span></div>`;
    document.getElementById('detToggleBtn').textContent = v.estado==='Activo'?'Desactivar':'Activar';
    switchToView();
    document.getElementById('detOverlay').classList.add('open');
  }

  function switchToEdit(){
    const v = vehiculos[currentDetIdx];
    document.getElementById('eTipoVeh').value = v.tipo;
    document.getElementById('eModelo').value  = v.modelo;
    document.getElementById('eColor').value   = v.color||'';
    document.getElementById('eOwner').value   = v.owner;
    document.getElementById('eTel').value     = v.tel||'';
    document.getElementById('detView').style.display='none';
    document.getElementById('detEdit').style.display='block';
  }
  function switchToView(){
    document.getElementById('detView').style.display='block';
    document.getElementById('detEdit').style.display='none';
  }
  function guardarEdicion(){
    const v = vehiculos[currentDetIdx];
    v.tipo   = document.getElementById('eTipoVeh').value;
    v.modelo = document.getElementById('eModelo').value.trim();
    v.color  = document.getElementById('eColor').value.trim();
    v.owner  = document.getElementById('eOwner').value.trim();
    v.tel    = document.getElementById('eTel').value.trim();
    openDet(currentDetIdx);
    renderCards();
    toast(`✓ ${v.placa} actualizado`);
  }
  function toggleFromModal(){
    const v = vehiculos[currentDetIdx];
    v.estado = v.estado==='Activo'?'Inactivo':'Activo';
    openDet(currentDetIdx);
    renderCards();
    toast(`✓ ${v.placa} ${v.estado.toLowerCase()}`);
  }
  function eliminarFromModal(){
    const v = vehiculos[currentDetIdx];
    if(!confirm(`¿Eliminar el vehículo ${v.placa}?`)) return;
    vehiculos.splice(currentDetIdx,1);
    closeO('detOverlay');
    renderCards();
    toast(`✓ ${v.placa} eliminado`);
  }

  // Cargar clientes existentes
  async function cargarClientes() {
    try {
      const response = await fetch('../../controllers/clientevehiculocontroller.php?action=getClientes');
      const result = await response.json();
      if (result.success) {
        const select = document.getElementById('selectCliente');
        select.innerHTML = '<option value="">Seleccione un cliente...</option>';
        result.data.forEach(cliente => {
          const option = document.createElement('option');
          option.value = cliente.id_cliente;
          option.textContent = `${cliente.nombre} (${cliente.cedula})`;
          select.appendChild(option);
        });
      }
    } catch (error) {
      console.error('Error cargando clientes:', error);
    }
  }

  // Toggle entre cliente existente y nuevo
  function toggleClienteForm() {
    const tipo = document.getElementById('tipoRegistro').value;
    const existente = document.getElementById('clienteExistenteForm');
    const nuevo = document.getElementById('nuevoClienteForm');
    
    if (tipo === 'existente') {
      existente.style.display = 'block';
      nuevo.style.display = 'none';
    } else {
      existente.style.display = 'none';
      nuevo.style.display = 'block';
    }
  }

  // Registrar vehículo y cliente
  async function registrar() {
    const placa = document.getElementById('vPlaca').value.trim().toUpperCase();
    const tipo = document.getElementById('vTipo').value;
    const modelo = document.getElementById('vModelo').value.trim();
    const color = document.getElementById('vColor').value.trim();
    const tipoRegistro = document.getElementById('tipoRegistro').value;
    const err = document.getElementById('vError');
    
    // Validaciones básicas
    if (!placa || !modelo) {
      err.textContent = 'La placa y el modelo son obligatorios.';
      err.style.display = 'flex';
      return;
    }
    
    // Validar placa duplicada
    if (vehiculos.find(v => v.placa === placa)) {
      err.textContent = `La placa ${placa} ya está registrada.`;
      err.style.display = 'flex';
      return;
    }
    
    err.style.display = 'none';
    
    try {
      let data = {
        placa_vehiculo: placa,
        marca: modelo.split(' ')[0] || '',
        modelo: modelo,
        color: color || ''
      };
      
      if (tipoRegistro === 'existente') {
        // Cliente existente
        const idCliente = document.getElementById('selectCliente').value;
        if (!idCliente) {
          err.textContent = 'Seleccione un cliente existente.';
          err.style.display = 'flex';
          return;
        }
        data.id_cliente = idCliente;
        
        // Registrar vehículo con cliente existente
        const response = await fetch('../../controllers/vehiculosapi.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'create',
            placa: data.placa_vehiculo,
            id_cliente: data.id_cliente,
            marca: data.marca,
            modelo: data.modelo,
            color: data.color
          })
        });
        
        const result = await response.json();
        if (result.success) {
          toast(`✓ ${placa} registrado exitosamente`);
          limpiarFormulario();
          cargarVehiculos();
        } else {
          err.textContent = result.message;
          err.style.display = 'flex';
        }
        
      } else {
        // Nuevo cliente
        const nombre = document.getElementById('nombreCliente').value.trim();
        const cedula = document.getElementById('cedulaCliente').value.trim();
        const telefono = document.getElementById('telCliente').value.trim();
        const correo = document.getElementById('correoCliente').value.trim();
        
        if (!nombre || !cedula) {
          err.textContent = 'El nombre y la cédula del cliente son obligatorios.';
          err.style.display = 'flex';
          return;
        }
        
        data.nombre_cliente = nombre;
        data.cedula_cliente = cedula;
        data.telefono_cliente = telefono;
        data.correo_cliente = correo;
        
        // Registrar cliente y vehículo juntos
        const response = await fetch('../../controllers/clientevehiculocontroller.php?action=registrarIntegrado', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (result.success) {
          toast(`✓ ${placa} y cliente ${nombre} registrados exitosamente`);
          limpiarFormulario();
          cargarVehiculos();
          cargarClientes(); // Recargar lista de clientes
        } else {
          err.textContent = result.message;
          err.style.display = 'flex';
        }
      }
      
    } catch (error) {
      console.error('Error en registro:', error);
      err.textContent = 'Error de conexión. Intente nuevamente.';
      err.style.display = 'flex';
    }
  }

  // Limpiar formulario
  function limpiarFormulario() {
    ['vPlaca', 'vModelo', 'vColor', 'nombreCliente', 'cedulaCliente', 'telCliente', 'correoCliente'].forEach(id => {
      document.getElementById(id).value = '';
    });
    document.getElementById('selectCliente').selectedIndex = 0;
    document.getElementById('vTipo').selectedIndex = 0;
    document.getElementById('tipoRegistro').value = 'existente';
    toggleClienteForm();
  }

  function closeO(id){ document.getElementById(id).classList.remove('open'); }
  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
  document.querySelectorAll('.overlay').forEach(o=>o.addEventListener('click',e=>{if(e.target===o)o.classList.remove('open');}));
  
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
    info.textContent = containerId === 'vehiculos-pagination' ? 
      `${totalVehiculosItems} vehículos` : 
      `${totalEventsItems} eventos`;
    container.appendChild(info);
  }

  // Cargar datos al iniciar la página
  document.addEventListener('DOMContentLoaded', function() {
    cargarVehiculos();
    cargarClientes();
    
    // Forzar inicialización de paginación de vehículos
    const paginationContainer = document.getElementById('vehiculos-pagination');
    if (paginationContainer) {
      // Inicializar variables globales
      totalVehiculosItems = vehiculos.length;
      totalVehiculosPages = Math.max(1, Math.ceil(totalVehiculosItems / 12));
      currentVehiculosPage = 1;
      
      // Mostrar paginación inicial
      renderPagination('vehiculos-pagination', currentVehiculosPage, totalVehiculosPages, cargarVehiculos);
    }
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