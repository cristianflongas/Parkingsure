<?php
// ============================================================
//  PARKINGSURE - Parqueadero Virtual 
//  Archivo: parqueadero.php - Con módulos funcionando
// ============================================================
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
  <title>ParkingSure — Parqueadero Virtual</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css?v=2" rel="stylesheet">
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
    }
    .lot-header {
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
    .module.libre   { background:#e8f5e8; border-color:#4caf50; color:#2e7d32; }
    .module.libre:hover  { box-shadow:0 4px 18px rgba(76,175,80,.3); border-color:#388e3c; }
    .module.ocupado { background:#ffebee; border-color:#f44336; color:#c62828; }
    .module.ocupado:hover { box-shadow:0 4px 18px rgba(244,67,54,.3); border-color:#d32f2f; }
    .module.mantenimiento { background:#fff8e1; border-color:#ffc107; color:#f57c00; }
    .module.mantenimiento:hover { box-shadow:0 4px 18px rgba(255,193,7,.3); border-color:#ffb300; }
    .module.nuevo   { background:transparent; border:1.5px dashed var(--border-md); color:var(--text-muted); }
    .module.nuevo:hover  { border-color:var(--gold); color:var(--gold); }
    .mod-icon  { font-size:18px; line-height:1; }
    .mod-id    { font-size:8.5px; font-weight:700; letter-spacing:.5px; opacity:.85; }
    .mod-plate { font-size:7px; font-weight:800; letter-spacing:.8px; background:rgba(0,0,0,.2); padding:1px 4px; border-radius:3px; margin-top:1px; }

    /* Context menu */
    .context-menu {
      position: fixed;
      background: var(--surface-2);
      border: 1px solid var(--border-md);
      border-radius: var(--r-md);
      padding: 8px 0;
      min-width: 180px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 1000;
      display: none;
    }
    .context-menu.show { display: block; }
    .context-item {
      padding: 10px 16px;
      font-size: 13px;
      cursor: pointer;
      transition: background 0.15s;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .context-item:hover { background: var(--surface-3); }
    .context-item.disabled {
      color: var(--text-muted);
      cursor: not-allowed;
      opacity: 0.6;
    }
    .context-item.disabled:hover { background: transparent; }
    .context-divider {
      height: 1px;
      background: var(--border);
      margin: 4px 0;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0,0,0,0.5);
    }
    .modal.show { display: flex; align-items: center; justify-content: center; }
    .modal-content {
      background: var(--surface-1);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      max-width: 500px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 24px;
      border-bottom: 1px solid var(--border);
    }
    .modal-header h3 {
      margin: 0;
      color: var(--text-primary);
      font-size: 18px;
      font-weight: 600;
    }
    .modal-close {
      background: none;
      border: none;
      font-size: 24px;
      color: var(--text-muted);
      cursor: pointer;
      padding: 0;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: var(--r-sm);
      transition: all 0.2s;
    }
    .modal-close:hover {
      background: var(--surface-3);
      color: var(--text-primary);
    }
    .modal-body {
      padding: 24px;
    }
    .modal-footer {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      padding: 20px 24px;
      border-top: 1px solid var(--border);
      background: var(--surface-1);
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-primary);
    }
    .form-group input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      background: var(--surface-1);
      color: var(--text-primary);
      font-size: 14px;
      transition: border-color 0.2s;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(232,184,75,0.1);
    }
    .modulo-info {
      background: var(--surface-1);
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      padding: 12px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .modulo-id {
      background: var(--gold);
      color: var(--surface-1);
      padding: 4px 8px;
      border-radius: var(--r-xs);
      font-weight: 600;
      font-size: 12px;
    }
    .modulo-ubicacion {
      color: var(--text-secondary);
      font-size: 14px;
    }
    .vehiculo-info {
      background: var(--surface-1);
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      padding: 12px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .vehiculo-placa {
      background: var(--error);
      color: white;
      padding: 4px 8px;
      border-radius: var(--r-sm);
      font-weight: 600;
      font-size: 12px;
    }
    .vehiculo-tiempo {
      color: var(--text-secondary);
      font-size: 14px;
    }
    .alert {
      padding: 12px 16px;
      border-radius: var(--r-sm);
      margin: 16px 0;
      border: 1px solid;
    }
    .alert-warning {
      background: rgba(255,193,7,.1);
      border-color: rgba(255,193,7,.3);
      color: var(--gold);
    }
    .btn-danger {
      background: var(--error);
      color: white;
      border: 1px solid var(--error);
    }
    .btn-danger:hover {
      background: #b71c1c;
      border-color: #b71c1c;
    }

    /* Grid de 4 columnas para estadísticas */
    .stats-grid-4 { 
      display:grid; 
      grid-template-columns:repeat(4,1fr); 
      gap:20px; 
    }
    @media(max-width:900px){ 
      .lot-grid { grid-template-columns:repeat(4,1fr); }
      .stats-grid-4 { grid-template-columns:repeat(2,1fr); }
    }
    @media(max-width:600px){ 
      .lot-grid { grid-template-columns:repeat(3,1fr); } 
      .legend { gap:12px; }
      .stats-grid-4 { grid-template-columns:repeat(1,1fr); }
    }
  </style>
</head>
<body>

<nav class="topbar">
  <a class="logo">
    <div class="logo-mark">P</div>
    <div class="logo-text">PARKING<em>SURE</em></div>
  </a>
  <button class="hamburger" id="hamburger-btn">
    <span></span>
    <span></span>
    <span></span>
  </button>
  <div class="nav-links" id="nav-links">
    <a class="nb" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb active" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
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
    <a class="btn-logout" href="../../index.php" style="background:var(--surface-2);color:var(--text-secondary);border-color:var(--border-md);margin-right:8px;">Ver Web</a>
    <a class="btn-logout" href="../../controllers/logout.php">Salir</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <div class="page-eyebrow">Módulo operativo</div>
    <h1 class="page-title">Parqueadero Virtual</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Estado de módulos en tiempo real — selecciona un espacio para registrar entrada o salida</p>
  </div>

  <div class="stats-grid stats-grid-4">
    <div class="stat-card">
      <div class="stat-label">Módulos Totales</div>
      <div class="stat-value gold" id="st-total">0</div>
      <div class="stat-sub">configurados</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Disponibles</div>
      <div class="stat-value gold" id="st-disponibles">0</div>
      <div class="stat-sub">listos para uso</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Ocupados</div>
      <div class="stat-value gold" id="st-ocupados">0</div>
      <div class="stat-sub">con vehículos</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Mantenimiento</div>
      <div class="stat-value gold" id="st-mantenimiento">0</div>
      <div class="stat-sub">no disponibles</div>
    </div>
  </div>

  <div class="toolbar">
    <div class="legend">
      <div class="leg"><div class="leg-sq" style="background:#e8f5e8;border:1px solid #4caf50"></div>Libre</div>
      <div class="leg"><div class="leg-sq" style="background:#ffebee;border:1px solid #f44336"></div>Ocupado</div>
      <div class="leg"><div class="leg-sq" style="background:#fff8e1;border:1px solid #ffc107"></div>Mantenimiento</div>
    </div>
    <div style="display:flex;gap:10px;">
      <button class="btn btn-secondary" onclick="window.location.href='vehiculos.php'">🚗 Registrar Vehículo</button>
      <button class="btn btn-primary" onclick="openAdd()">+ Agregar Módulo</button>
    </div>
  </div>

  <div class="lot-shell">
    <div class="lot-header">
      <span class="lot-arrow">⬅ ENTRADA</span>
      <span class="lot-label-pill">🅿 Parqueadero</span>
      <span class="lot-arrow">SALIDA ➡</span>
    </div>
    <div class="lot-grid" id="lotGrid"></div>
  </div>
</div>

<!-- Modal de Asignación de Vehículo -->
<div class="modal" id="asignarModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Asignar Vehículo a Módulo</h3>
      <button class="modal-close" onclick="closeAsignarModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Módulo a asignar:</label>
        <div class="modulo-info" id="moduloInfo">
          <span class="modulo-id">M--</span>
          <span class="modulo-ubicacion">--</span>
        </div>
      </div>
      <div class="form-group">
        <label for="placaInput">Placa del Vehículo <span style="color:var(--gold)">*</span></label>
        <input type="text" id="placaInput" placeholder="Ej: ABC123" maxlength="10" style="text-transform:uppercase;">
        <div id="placaFeedback" style="font-size:12px;margin-top:5px;color:var(--text-muted)"></div>
      </div>
      <div class="form-group">
        <label for="tipoServicio">Tipo de Servicio <span style="color:var(--gold)">*</span></label>
        <select id="tipoServicio" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:var(--r-sm);background:var(--surface-1);color:var(--text-primary);font-size:14px;">
          <option value="">Cargando tipos de servicio...</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeAsignarModal()">Cancelar</button>
      <button class="btn btn-primary" id="btnConfirmarAsignacion" onclick="confirmarAsignacion()">Asignar Vehículo</button>
    </div>
  </div>
</div>

<!-- Modal de Liberación de Módulo -->
<div class="modal" id="liberarModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Liberar Módulo</h3>
      <button class="modal-close" onclick="closeLiberarModal()">&times;</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label>Módulo a liberar:</label>
        <div class="modulo-info" id="liberarModuloInfo">
          <span class="modulo-id">M--</span>
          <span class="modulo-ubicacion">--</span>
        </div>
      </div>
      <div class="form-group">
        <label>Vehículo estacionado:</label>
        <div class="vehiculo-info" id="liberarVehiculoInfo">
          <span class="vehiculo-placa">--</span>
          <span class="vehiculo-tiempo">--</span>
        </div>
      </div>
      <div class="alert alert-warning">
        <strong>⚠️ Atención:</strong> Al liberar este módulo, el vehículo podrá salir del parqueadero.
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeLiberarModal()">Cancelar</button>
      <button class="btn btn-danger" onclick="confirmarLiberacion()">Liberar Módulo</button>
    </div>
  </div>
</div>

<!-- Menú Contextual para Módulos -->
<div class="context-menu" id="contextMenu">
  <div class="context-item" id="ctxItemEstado" onclick="toggleModuloEstado()">
    <span>[MTTO]</span> Poner en Mantenimiento
  </div>
  <div class="context-divider"></div>
  <div class="context-item" onclick="editModuloInfo()">
    <span>[EDIT]</span> Editar Información
  </div>
  <div class="context-item" onclick="deleteModuloInfo()">
    <span>[DEL]</span> Eliminar Módulo
  </div>
</div>

<div id="toast"></div>

<script>
  /* ─── Estado de módulos ─── */
  let modules = [];
  let currentModuloIndex = -1;
  let currentModuloAsignar = null; // Para asignación de vehículos
  let currentModuloLiberar = null; // Para liberación de vehículos

  // Cargar módulos desde la BD
  async function cargarModulos() {
    try {
      const response = await fetch('../../controllers/moduloapi.php?action=getAll');
      const result = await response.json();
      if (result.success) {
        modules = result.data.map(m => ({
          db_id:      m.id,
          id:         m.id.toString().padStart(2, '0'),
          estado:     m.estado,        // DISPONIBLE | OCUPADO | MANTENIMIENTO
          ocupaciones:m.ocupaciones,
          ubicacion:  m.ubicacion,
          placa:      m.placa || null  // Placa del vehículo si está OCUPADO
        }));
        renderLot();
        cargarStats();
      } else {
        console.error('Error al cargar módulos:', result.message);
        toast('[ERROR] No se pudieron cargar los módulos');
      }
    } catch (error) {
      console.error('Error cargando módulos:', error);
      toast('[ERROR] Error de conexión al cargar módulos');
    }
  }

  // Cargar estadísticas
  async function cargarStats() {
    try {
      const response = await fetch('../../controllers/moduloapi.php?action=getStats');
      const result = await response.json();
      if (result.success) {
        const stats = result.data;
        
        // Actualizar las nuevas tarjetas
        const totalEl = document.getElementById('st-total');
        const disponiblesEl = document.getElementById('st-disponibles');
        const ocupadosEl = document.getElementById('st-ocupados');
        const mantenimientoEl = document.getElementById('st-mantenimiento');
        
        if (totalEl) totalEl.textContent = stats.total;
        if (disponiblesEl) disponiblesEl.textContent = stats.disponibles;
        if (ocupadosEl) ocupadosEl.textContent = stats.ocupados;
        if (mantenimientoEl) mantenimientoEl.textContent = stats.mantenimiento;
        
        // Actualizar también las estadísticas antiguas para compatibilidad
        const freeEl = document.getElementById('st-free');
        const busyEl = document.getElementById('st-busy');
        
        if (freeEl) freeEl.textContent = stats.libres;
        if (busyEl) busyEl.textContent = stats.ocupados;
        
        console.log('📊 Estadísticas actualizadas:', stats);
      }
    } catch (error) {
      console.error('Error cargando estadísticas:', error);
    }
  }

  /* ─── Render ─── */
  function renderLot(){
    const g = document.getElementById('lotGrid');
    g.innerHTML = '';
    
    modules.forEach((m, i) => {
      const d = document.createElement('div');

      // El estado viene directamente de la BD (ya corregido por el backend)
      // DISPONIBLE → verde/libre | OCUPADO → rojo | MANTENIMIENTO → amarillo
      let cssClass;
      switch (m.estado) {
        case 'OCUPADO':       cssClass = 'ocupado';       break;
        case 'MANTENIMIENTO': cssClass = 'mantenimiento'; break;
        default:              cssClass = 'libre';          break; // DISPONIBLE
      }

      // Icono según estado
      const icon = m.estado === 'OCUPADO' ? '🚗' : m.estado === 'MANTENIMIENTO' ? '🔧' : '🅿️';

      d.className = `module ${cssClass}`;
      d.innerHTML = `
        <div class="mod-icon">${icon}</div>
        <div class="mod-id">M${m.id}</div>
        ${m.placa ? `<div class="mod-plate">${m.placa}</div>` : ''}
      `;

      // Comportamiento del clic según estado real
      if (m.estado === 'DISPONIBLE') {
        d.title = `Módulo ${m.id} — Disponible. Clic para asignar vehículo.`;
        d.onclick = () => openAsignarModal(i);
      } else if (m.estado === 'OCUPADO') {
        d.title = `Módulo ${m.id} — Ocupado por ${m.placa || 'vehículo'}. Clic para liberar.`;
        d.onclick = () => openLiberarModal(i);
      } else {
        d.title = `Módulo ${m.id} — En mantenimiento.`;
        d.onclick = () => toast(`ℹ️ Módulo ${m.id} está en mantenimiento`);
      }

      d.oncontextmenu = (e) => {
        e.preventDefault();
        showModMenu(e, i);
      };
      g.appendChild(d);
    });

    // Celda para agregar nuevo módulo
    const add = document.createElement('div');
    add.className = 'module nuevo';
    add.innerHTML = '<div style="font-size:20px">+</div><div class="mod-id">NUEVO</div>';
    add.title = 'Agregar nuevo módulo';
    add.onclick = openAdd;
    g.appendChild(add);
  }

  /* ─── Modal de Asignación ─── */
  function openAsignarModal(i) {
    currentModuloAsignar = modules[i];
    const modal = document.getElementById('asignarModal');
    const moduloInfo = document.getElementById('moduloInfo');
    
    // Mostrar información del módulo
    moduloInfo.innerHTML = `
      <span class="modulo-id">${currentModuloAsignar.id}</span>
      <span class="modulo-ubicacion">${currentModuloAsignar.ubicacion}</span>
    `;
    
    // Limpiar input de placa y feedback
    document.getElementById('placaInput').value = '';
    document.getElementById('placaFeedback').textContent = '';
    document.getElementById('placaFeedback').style.color = 'var(--text-muted)';
    
    // Mostrar modal
    modal.classList.add('show');
    
    // Cargar tipos de servicio
    cargarTiposServicio();
    
    // Enfocar input de placa
    setTimeout(() => {
      document.getElementById('placaInput').focus();
    }, 100);
    
    // Validación en tiempo real de la placa
    const placaInput = document.getElementById('placaInput');
    placaInput.oninput = debounce(async function() {
      const placa = this.value.trim().toUpperCase();
      const feedback = document.getElementById('placaFeedback');
      if (placa.length < 3) {
        feedback.textContent = '';
        return;
      }
      try {
        const res = await fetch(`../../controllers/vehiculosapi.php?action=getById&placa=${encodeURIComponent(placa)}`);
        const data = await res.json();
        if (data.success) {
          const v = data.data;
          feedback.textContent = `✅ ${v.marca || ''} ${v.modelo || ''} — ${v.nombre_cliente || 'Sin cliente'}`;
          feedback.style.color = '#4caf50';
        } else {
          feedback.textContent = '⚠️ Placa no registrada. Regístrela en el módulo Vehículos.';
          feedback.style.color = '#f59e0b';
        }
      } catch(e) {
        feedback.textContent = '';
      }
    }, 400);
  }
  
  function closeAsignarModal() {
    document.getElementById('asignarModal').classList.remove('show');
    document.getElementById('placaInput').oninput = null;
    document.getElementById('placaFeedback').textContent = '';
    currentModuloAsignar = null;
  }

  // Cargar tipos de servicio
  async function cargarTiposServicio() {
    try {
      const response = await fetch('../../controllers/tiposervicioapi.php?action=getAll');
      const result = await response.json();
      
      const select = document.getElementById('tipoServicio');
      
      if (result.success && result.data.length > 0) {
        select.innerHTML = '';
        result.data.forEach(tipo => {
          const option = document.createElement('option');
          option.value = tipo.id_tipo_servicio;
          option.textContent = tipo.nombre_tipo_servicio;
          select.appendChild(option);
        });
      } else {
        // Si no hay tipos de servicio o hay error, usar valor por defecto
        select.innerHTML = '<option value="1">Servicio Estándar</option>';
      }
    } catch (error) {
      console.error('Error cargando tipos de servicio:', error);
      // Valor por defecto en caso de error
      const select = document.getElementById('tipoServicio');
      select.innerHTML = '<option value="1">Servicio Estándar</option>';
    }
  }

  /* ─── Modal de Liberación ─── */
  function openLiberarModal(i) {
    const modulo = modules[i];
    const modal = document.getElementById('liberarModal');
    const moduloInfo = document.getElementById('liberarModuloInfo');
    const vehiculoInfo = document.getElementById('liberarVehiculoInfo');

    // Mostrar información del módulo
    moduloInfo.innerHTML = `
      <span class="modulo-id">M${modulo.id}</span>
      <span class="modulo-ubicacion">${modulo.ubicacion}</span>
    `;

    // Mostrar placa mientras carga el tiempo
    vehiculoInfo.innerHTML = `
      <span class="vehiculo-placa">${modulo.placa || 'SIN PLACA'}</span>
      <span class="vehiculo-tiempo" id="tiempoEstancia">Calculando tiempo...</span>
    `;

    // Guardar referencia al módulo
    currentModuloLiberar = modulo;

    // Mostrar modal
    modal.classList.add('show');

    // Cargar tiempo de estancia desde la entrada activa
    if (modulo.placa) {
      fetch(`../../controllers/entradaapi.php?action=getActive`)
        .then(r => r.json())
        .then(result => {
          if (result.success) {
            const entrada = result.data.find(e =>
              parseInt(e.id_modulo) === parseInt(modulo.db_id)
            );
            if (entrada) {
              const desde = new Date(entrada.fecha_hora_entrada);
              const ahora = new Date();
              const diffMs = ahora - desde;
              const diffH  = Math.floor(diffMs / 3600000);
              const diffM  = Math.floor((diffMs % 3600000) / 60000);
              const tiempoEl = document.getElementById('tiempoEstancia');
              if (tiempoEl) {
                tiempoEl.textContent = diffH > 0
                  ? `Hace ${diffH}h ${diffM}min`
                  : `Hace ${diffM} minutos`;
              }
            }
          }
        })
        .catch(() => {});
    }
  }
  
  function closeLiberarModal() {
    document.getElementById('liberarModal').classList.remove('show');
    currentModuloLiberar = null;
  }
  
  // Ir a pagos (misma pestaña, la factura ya quedó en BD como PENDIENTE)
  function abrirPagosConFactura(factura) {
    sessionStorage.setItem('facturaPrecargada', JSON.stringify(factura));
    window.location.href = 'pagos.php';
  }

  async function confirmarLiberacion() {
    if (!currentModuloLiberar) return;

    const btnLiberar = document.querySelector('#liberarModal .btn-danger');
    if (btnLiberar) { btnLiberar.disabled = true; btnLiberar.textContent = 'Liberando…'; }

    try {
      // Obtener la entrada activa para este módulo
      const response = await fetch('../../controllers/entradaapi.php?action=getActive');
      const result   = await response.json();

      if (!result.success) {
        toast('❌ Error al obtener entradas activas');
        return;
      }

      const entrada = result.data.find(e =>
        parseInt(e.id_modulo) === parseInt(currentModuloLiberar.db_id)
      );

      if (!entrada) {
        toast('❌ No se encontró la entrada activa para este módulo');
        return;
      }

      // Registrar salida
      const liberarResponse = await fetch('../../controllers/salidaapi.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ action: 'release', id_entrada: entrada.id_entrada })
      });
      const liberarResult = await liberarResponse.json();

      if (liberarResult.success) {
        closeLiberarModal();
        cargarModulos();

        if (liberarResult.factura) {
          const f   = liberarResult.factura;
          const fmt = n => new Intl.NumberFormat('es-CO',{style:'currency',currency:'COP',minimumFractionDigits:0}).format(n);
          mostrarNotificacionFactura(f, fmt);
        } else {
          toast(`✅ Módulo ${currentModuloLiberar.id} liberado correctamente`);
        }
      } else {
        toast(`❌ ${liberarResult.message}`);
      }
    } catch (error) {
      console.error('Error en liberación:', error);
      toast('❌ Error de conexión');
    } finally {
      if (btnLiberar) { btnLiberar.disabled = false; btnLiberar.textContent = 'Liberar Módulo'; }
    }
  }

  // Notificación de factura generada (reemplaza el confirm() nativo)
  function mostrarNotificacionFactura(f, fmt) {
    // Crear modal de notificación
    const overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:transparent;z-index:3000;display:flex;align-items:center;justify-content:center;animation:fadeIn .2s';
    overlay.innerHTML = `
      <div style="background:var(--surface-2);border:1px solid var(--border-md);border-radius:16px;padding:28px;max-width:400px;width:90%;position:relative">
        <div style="text-align:center;margin-bottom:16px">
          <div style="font-size:36px">🧾</div>
          <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:var(--text-primary);margin:6px 0">Factura Generada</div>
          <div style="font-size:13px;color:var(--text-secondary)">El módulo fue liberado correctamente</div>
        </div>
        <div style="background:var(--surface-1);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:16px">
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px">
            <span style="color:var(--text-muted)">Placa</span>
            <strong style="font-family:monospace">${f.placa}</strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px">
            <span style="color:var(--text-muted)">Servicio</span>
            <span>${f.tipo_servicio || '—'}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px">
            <span style="color:var(--text-muted)">Tiempo</span>
            <span>${f.tiempo_estancia}</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:13px">
            <span style="color:var(--text-muted)">Horas cobradas</span>
            <span>${f.horas_cobradas} h × ${fmt(f.tarifa_hora)}</span>
          </div>
          <div style="border-top:1px dashed var(--border-md);margin:8px 0"></div>
          <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:16px;font-weight:800">
            <span style="color:var(--text-muted)">TOTAL</span>
            <span style="color:var(--gold)">${fmt(f.monto_total)}</span>
          </div>
        </div>
        <p style="font-size:12px;color:var(--text-muted);text-align:center;margin-bottom:14px">
          La factura quedó <strong style="color:#f59e0b">PENDIENTE</strong>. Puedes cobrarla ahora o después desde el módulo de Pagos.
        </p>
        <div style="display:flex;gap:10px">
          <button onclick="this.closest('[style*=fixed]').remove()" 
            style="flex:1;padding:11px;background:var(--surface-3);border:1px solid var(--border-md);border-radius:10px;color:var(--text-secondary);font-size:13px;font-weight:600;cursor:pointer">
            Quedar aquí
          </button>
          <button onclick="abrirPagosConFactura(${JSON.stringify(f).replace(/"/g,'&quot;')})"
            style="flex:1;padding:11px;background:var(--gold);border:none;border-radius:10px;color:#08090c;font-family:'Syne',sans-serif;font-size:13px;font-weight:800;cursor:pointer">
            Ir a Pagos 💳
          </button>
        </div>
      </div>`;
    document.body.appendChild(overlay);
  }
  
  async function confirmarAsignacion() {
    if (!currentModuloAsignar) return;
    
    const placa = document.getElementById('placaInput').value.trim().toUpperCase();
    const idTipoServicio = document.getElementById('tipoServicio').value;
    const btn = document.getElementById('btnConfirmarAsignacion');
    
    if (!placa) {
      toast('[ERROR] Debe ingresar una placa');
      return;
    }
    
    if (!idTipoServicio) {
      toast('[ERROR] Debe seleccionar un tipo de servicio');
      return;
    }
    
    // Deshabilitar botón para evitar doble envío
    btn.disabled = true;
    btn.textContent = 'Asignando...';
    
    try {
      // Verificar que la placa esté registrada en el sistema
      const checkResponse = await fetch(`../../controllers/vehiculosapi.php?action=getById&placa=${encodeURIComponent(placa)}`);
      const checkResult = await checkResponse.json();
      
      if (!checkResult.success) {
        toast('[ERROR] La placa no está registrada. Regístrela primero en Vehículos.');
        btn.disabled = false;
        btn.textContent = 'Asignar Vehículo';
        return;
      }
      
      // Asignar vehículo al módulo (registrar entrada)
      const asignarResponse = await fetch('../../controllers/entradaapi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'assign',
          id_modulo: currentModuloAsignar.db_id,
          placa: placa,
          id_tipo_servicio: parseInt(idTipoServicio)
        })
      });
      
      const asignarResult = await asignarResponse.json();
      
      if (asignarResult.success) {
        toast(`✅ Vehículo ${placa} asignado al módulo ${currentModuloAsignar.id}`);
        closeAsignarModal();
        cargarModulos();
      } else {
        toast(`[ERROR] ${asignarResult.message}`);
      }
    } catch (error) {
      console.error('Error en asignación:', error);
      toast('[ERROR] Error de conexión al servidor');
    } finally {
      btn.disabled = false;
      btn.textContent = 'Asignar Vehículo';
    }
  }

  /* ─── Menú Contextual ─── */
  function showModMenu(e, i) {
    currentModuloIndex = i;
    const menu = document.getElementById('contextMenu');
    const m = modules[i];
    
    // Actualizar opciones según el estado actual
    const items = menu.querySelectorAll('.context-item');
    items.forEach(item => {
      item.classList.remove('disabled');
    });
    
    // Actualizar texto del botón según estado
    const ctxItemEstado = document.getElementById('ctxItemEstado');
    if (m.estado === 'MANTENIMIENTO') {
      ctxItemEstado.innerHTML = '<span>[DISP]</span> Poner Disponible';
    } else {
      ctxItemEstado.innerHTML = '<span>[MTTO]</span> Poner en Mantenimiento';
    }
    
    // Deshabilitar opciones según el estado actual
    // Si está ocupado, no puede ponerse en mantenimiento
    if (m.ocupaciones > 0 && m.estado !== 'MANTENIMIENTO') {
      items[0].classList.add('disabled'); // No puede poner en mantenimiento si está ocupado
    }
    
    // Posicionar menú
    menu.style.left = e.pageX + 'px';
    menu.style.top = e.pageY + 'px';
    menu.classList.add('show');
    
    // Cerrar al hacer clic fuera
    setTimeout(() => {
      document.addEventListener('click', hideModMenu);
    }, 100);
  }

  function hideModMenu() {
    document.getElementById('contextMenu').classList.remove('show');
    document.removeEventListener('click', hideModMenu);
  }

  async function toggleModuloEstado() {
    if (currentModuloIndex === -1) return;
    
    const m = modules[currentModuloIndex];
    
    // Determinar el nuevo estado según el estado actual
    const nuevoEstado = m.estado === 'MANTENIMIENTO' ? 'DISPONIBLE' : 'MANTENIMIENTO';
    const mensajeConfirmacion = nuevoEstado === 'MANTENIMIENTO' 
      ? `¿Poner el módulo ${m.id} en Mantenimiento?`
      : `¿Poner el módulo ${m.id} Disponible?`;
    const mensajeExito = nuevoEstado === 'MANTENIMIENTO'
      ? `[OK] Módulo ${m.id} puesto en Mantenimiento`
      : `[OK] Módulo ${m.id} puesto Disponible`;
    
    // Validaciones
    if (nuevoEstado === 'MANTENIMIENTO' && m.ocupaciones > 0) {
      toast('[ERROR] No se puede poner en mantenimiento un módulo ocupado');
      hideModMenu();
      return;
    }
    
    // Confirmación
    if (!confirm(mensajeConfirmacion)) {
      hideModMenu();
      return;
    }
    
    try {
      const response = await fetch('../../controllers/moduloapi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'changeState',
          id: m.db_id,
          estado: nuevoEstado
        })
      });
      
      const result = await response.json();
      if (result.success) {
        hideModMenu();
        cargarModulos(); // Recargar para actualizar la vista
        toast(mensajeExito);
      } else {
        toast(`[ERROR] Error: ${result.message}`);
      }
    } catch (error) {
      console.error('Error cambiando estado:', error);
      toast('[ERROR] Error de conexión');
    }
    
    hideModMenu();
  }

  async function editModuloInfo() {
    if (currentModuloIndex === -1) return;
    const m = modules[currentModuloIndex];
    const nuevaUbicacion = prompt(`Editar ubicación del módulo ${m.id}:`, m.ubicacion);
    if (!nuevaUbicacion || nuevaUbicacion === m.ubicacion) {
      hideModMenu();
      return;
    }
    
    try {
      const response = await fetch('../../controllers/moduloapi.php', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'update',
          id: m.db_id,
          ubicacion: nuevaUbicacion,
          estado: m.estado
        })
      });
      
      const result = await response.json();
      if (result.success) {
        hideModMenu();
        cargarModulos(); // Recargar para actualizar la vista
        toast(`[OK] Módulo ${m.id} actualizado`);
      } else {
        toast(`[ERROR] Error: ${result.message}`);
      }
    } catch (error) {
      console.error('Error editando módulo:', error);
      toast('[ERROR] Error de conexión');
    }
    
    hideModMenu();
  }

  async function deleteModuloInfo() {
    if (currentModuloIndex === -1) return;
    const m = modules[currentModuloIndex];
    
    // Verificar si el módulo tiene ocupaciones activas
    if (m.ocupaciones > 0) {
      toast('[ERROR] No se puede eliminar un módulo ocupado');
      hideModMenu();
      return;
    }
    
    if (!confirm(`¿Estás seguro de eliminar el módulo ${m.id} (${m.ubicacion})?`)) {
      hideModMenu();
      return;
    }
    
    try {
      const response = await fetch(`../../controllers/moduloapi.php?id=${m.db_id}`, {
        method: 'DELETE'
      });
      
      const result = await response.json();
      if (result.success) {
        hideModMenu();
        cargarModulos(); // Recargar para actualizar la vista
        toast(`[OK] Módulo ${m.id} eliminado`);
      } else {
        toast(`[ERROR] Error: ${result.message}`);
      }
    } catch (error) {
      console.error('Error eliminando módulo:', error);
      toast('[ERROR] Error de conexión');
    }
    
    hideModMenu();
  }

  function openMod(i) {
    const m = modules[i];
    if (m.estado === 'DISPONIBLE') {
      toast(`ℹ️ Módulo ${m.id} está disponible`);
    } else if (m.estado === 'OCUPADO') {
      toast(`ℹ️ Módulo ${m.id} está ocupado por ${m.placa || 'un vehículo'}`);
    } else {
      toast(`ℹ️ Módulo ${m.id} está en mantenimiento`);
    }
  }

  async function openAdd() {
    const nuevaUbicacion = prompt('Ingrese la ubicación del nuevo módulo (ej: Piso 4 - Sector A):');
    if (!nuevaUbicacion) return;
    
    try {
      const response = await fetch('../../controllers/moduloapi.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'create',
          ubicacion: nuevaUbicacion,
          estado: 'DISPONIBLE'
        })
      });
      
      const result = await response.json();
      if (result.success) {
        cargarModulos(); // Recargar para actualizar la vista
        toast(`[OK] Nuevo módulo agregado`);
      } else {
        toast(`[ERROR] Error: ${result.message}`);
      }
    } catch (error) {
      console.error('Error agregando módulo:', error);
      toast('[ERROR] Error de conexión');
    }
  }

  function toast(msg){
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3200);
  }

  // Utilidad debounce para validación en tiempo real
  function debounce(fn, delay) {
    let timer;
    return function(...args) {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  }

  // Cargar datos al iniciar la página
  document.addEventListener('DOMContentLoaded', function() {
    cargarModulos();
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
