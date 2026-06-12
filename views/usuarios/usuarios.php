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
  <title>ParkingSure — Usuarios</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css?v=2" rel="stylesheet">
  <style>
    /* User cards */
    .usr-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px; }
    .usr-card {
      background:var(--surface-2); border:1px solid var(--border);
      border-radius:var(--r-md); padding:18px;
      transition:border-color .18s, transform .15s; cursor:pointer;
    }
    .usr-card:hover { border-color:var(--border-md); transform:translateY(-2px); }
    .usr-card-top { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
    .usr-av {
      width:44px; height:44px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      font-family:'Syne',sans-serif; font-weight:800; font-size:16px;
      color:var(--ink-900); flex-shrink:0; background:var(--gold);
    }
    .usr-av.operador { background:var(--text-secondary); }
    .usr-name { font-size:14px; font-weight:700; color:var(--text-primary); margin-bottom:2px; }
    .usr-role { font-size:11px; color:var(--text-muted); }
    .usr-row { display:flex; justify-content:space-between; padding:5px 0; font-size:12px; border-bottom:1px solid var(--border); }
    .usr-row:last-child { border:none; }
    .usr-row span:first-child { color:var(--text-muted); }

    /* Steps breadcrumb */
    .steps-bar { display:flex; align-items:center; gap:8px; margin-bottom:18px; }
    .sb-step { font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; padding:3px 10px; border-radius:20px; border:1px solid var(--border-md); color:var(--text-muted); }
    .sb-step.done   { background:var(--surface-2); border-color:var(--border-md); color:var(--text-secondary); }
    .sb-step.active { background:var(--gold-dim); border-color:rgba(232,184,75,.3); color:var(--gold); }
    .sb-arrow { font-size:10px; color:var(--text-muted); }
    .step { display:none; } .step.active { display:block; }
    .confirm-box { background:var(--surface-2); border:1px solid var(--border-md); border-radius:var(--r-xl); padding:32px 24px; text-align:center; }
    .confirm-icon { font-size:44px; margin-bottom:8px; }
    .confirm-title { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:var(--text-primary); margin-bottom:6px; }
    .confirm-sub { font-size:13px; color:var(--text-secondary); }
    .form-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  </style>
</head>
<body>

<?php
  // ── Conexión única para toda la página ──────────────────────────────────────
  require_once __DIR__ . "/../../config/database.php";
  $database = new Database();
  $conn     = $database->conectar();
?>

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
    <a class="nb active" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <a class="nb" href="reportes.php"><span class="nav-icon"></span> Reportes</a>
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
    <div class="page-eyebrow">Administración</div>
    <h1 class="page-title">Gestión de Usuarios</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Crear, modificar y gestionar usuarios y roles del sistema</p>
  </div>

  <?php if(isset($_GET['msg']) && $_GET['msg'] === 'creado'): ?>
    <div class="alert alert-success" style="margin-bottom:18px">✅ Usuario creado correctamente</div>
  <?php endif; ?>

  <?php if(isset($_GET['error'])): ?>
    <div class="alert alert-error" style="margin-bottom:18px">❌ Error: <?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <!-- ── Stats ──────────────────────────────────────────────────────────────── -->
  <div class="stats-grid stats-grid-3" style="margin-bottom:20px">

    <div class="stat-card">
      <div class="stat-label">Total Usuarios</div>
      <div class="stat-value gold">
        <?php
          try {
            echo $conn->query("SELECT COUNT(*) FROM personal")->fetchColumn();
          } catch(Exception $e){ echo '—'; }
        ?>
      </div>
      <div class="stat-sub">en el sistema</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Administradores</div>
      <div class="stat-value gold">
        <?php
          try {
            echo $conn->query("
              SELECT COUNT(*)
              FROM personal p
              INNER JOIN rol r ON p.id_rol = r.id_rol
              WHERE r.nombre_rol = 'ADMINISTRADOR'
            ")->fetchColumn();
          } catch(Exception $e){ echo '—'; }
        ?>
      </div>
      <div class="stat-sub">con acceso total</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Operadores</div>
      <div class="stat-value gold">
        <?php
          try {
            echo $conn->query("
              SELECT COUNT(*)
              FROM personal p
              INNER JOIN rol r ON p.id_rol = r.id_rol
              WHERE r.nombre_rol = 'OPERADOR'
            ")->fetchColumn();
          } catch(Exception $e){ echo '—'; }
        ?>
      </div>
      <div class="stat-sub">operadores activos</div>
    </div>

  </div>

  <!-- ── Toolbar ────────────────────────────────────────────────────────────── -->
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px">
    <div class="search-wrap" style="max-width:320px;flex:1">
      <span class="s-icon">🔍</span>
      <input placeholder="Buscar usuario…" oninput="filtrarUsuarios()" id="buscarUsr">
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <select class="form-select" id="filtroRol" onchange="filtrarUsuarios()" style="padding:8px 12px;font-size:12px;width:auto">
        <option value="todos">Todos los roles</option>
        <option value="ADMINISTRADOR">Administradores</option>
        <option value="OPERADOR">Operadores</option>
      </select>
      <button class="btn btn-primary" onclick="abrirNuevo()">+ Nuevo Usuario</button>
    </div>
  </div>

  <!-- ── Grid de usuarios (renderizado por JS con datos de PHP) ─────────────── -->
  <?php
    require_once __DIR__ . "/../../models/UsuarioModel.php";
    try {
      $usuarioModel = new UsuarioModel($conn);
      $usuarios = $usuarioModel->obtenerTodos();
    } catch(Exception $e){
      $usuarios = [];
    }

    $usersJson = json_encode($usuarios, JSON_UNESCAPED_UNICODE);
  ?>
  <div id="usrGrid"></div>
  <div class="pagination" id="usuarios-pagination">
    <!-- Los botones de paginación se cargarán dinámicamente -->
  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: Nuevo usuario (4 pasos)                                           -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="overlay" id="nuevoOverlay">
  <div class="modal" style="max-width:500px">

    <!-- Breadcrumb -->
    <div class="steps-bar">
      <span class="sb-step active" id="nu1">1 · Datos personales</span>
      <span class="sb-arrow">›</span>
      <span class="sb-step" id="nu2">2 · Acceso</span>
      <span class="sb-arrow">›</span>
      <span class="sb-step" id="nu3">3 · Confirmar</span>
    </div>

    <!-- STEP 1: Datos personales -->
    <div class="step active" id="nuStep1">
      <div class="modal-header">
        <div class="modal-title">Datos Personales</div>
        <div class="modal-sub">Información del nuevo usuario</div>
      </div>
      <div class="form-grid2">
        <div class="form-group">
          <label class="form-label">Nombre completo</label>
          <input class="form-input" id="nuNombre" placeholder="Nombre completo">
        </div>
        <div class="form-group">
          <label class="form-label">Teléfono</label>
          <input class="form-input" id="nuTel" placeholder="3001234567" type="tel">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">N° Documento (Cédula)</label>
        <input class="form-input" id="nuCedula" placeholder="Número de cédula">
      </div>
      <div class="form-group">
        <label class="form-label">Correo electrónico</label>
        <input class="form-input" id="nuCorreo" type="email" placeholder="correo@ejemplo.com">
      </div>
      <div id="nuErr1" class="alert alert-error" style="display:none"></div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeO('nuevoOverlay')">Cancelar</button>
        <button class="btn btn-primary" style="flex:1" onclick="nuNext1()">Siguiente →</button>
      </div>
    </div>

    <!-- STEP 2: Credenciales -->
    <div class="step" id="nuStep2">
      <div class="modal-header">
        <div class="modal-title">Acceso al Sistema</div>
        <div class="modal-sub">Credenciales de inicio de sesión</div>
      </div>
      <div class="form-group">
        <label class="form-label">Rol en el sistema</label>
        <select class="form-select" id="nuRol">
          <option value="ADMINISTRADOR">⚙️ Administrador</option>
          <option value="OPERADOR">👤 Operador</option>
        </select>
      </div>
      <div class="form-grid2">
        <div class="form-group">
          <label class="form-label">Usuario</label>
          <input class="form-input" id="nuUsuario" placeholder="Nombre de usuario">
        </div>
        <div class="form-group">
          <label class="form-label">Contraseña</label>
          <input class="form-input" id="nuPassword" type="password" placeholder="••••••••">
        </div>
      </div>
      <div id="nuErr2" class="alert alert-error" style="display:none"></div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="nuBack1()">← Atrás</button>
        <button class="btn btn-primary" style="flex:1" onclick="nuNext2()">Revisar →</button>
      </div>
    </div>

    <!-- STEP 3: Confirmar -->
    <div class="step" id="nuStep3">
      <div class="modal-header">
        <div class="modal-title">Confirmar Usuario</div>
        <div class="modal-sub">Revisa antes de crear</div>
      </div>
      <div style="background:var(--surface-3);border-radius:var(--r-md);padding:18px;margin:12px 0">
        <div class="t-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Nombre</span><span id="cfNombre" style="font-weight:600">—</span>
        </div>
        <div class="t-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Cédula</span><span id="cfCedula" style="font-weight:600">—</span>
        </div>
        <div class="t-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Correo</span><span id="cfCorreo" style="font-weight:600">—</span>
        </div>
        <div class="t-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Teléfono</span><span id="cfTel" style="font-weight:600">—</span>
        </div>
        <div class="t-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;border-bottom:1px solid var(--border)">
          <span style="color:var(--text-muted)">Usuario</span><span id="cfUsuario" style="font-weight:600;font-family:monospace">—</span>
        </div>
        <div class="t-row" style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px">
          <span style="color:var(--text-muted)">Rol</span><span id="cfRol" style="font-weight:600">—</span>
        </div>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="nuBack2()">← Atrás</button>
        <button class="btn btn-primary" style="flex:1" onclick="submitNuevo()">✓ Crear Usuario</button>
      </div>
    </div>

    <!-- STEP 4: Éxito -->
    <div class="step" id="nuStep4">
      <div class="confirm-box" style="margin-top:8px">
        <div class="confirm-icon">✅</div>
        <div class="confirm-title">Usuario Creado</div>
        <div class="confirm-sub" id="nuSuccessMsg">El usuario fue registrado correctamente.</div>
      </div>
      <div class="modal-actions" style="margin-top:18px">
        <button class="btn btn-secondary btn-full" onclick="closeO('nuevoOverlay');location.reload()">Cerrar</button>
      </div>
    </div>

  </div>
</div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- MODAL: Detalle de usuario                                                -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<div class="overlay" id="detOverlay">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <div class="modal-title" id="detNombre">Usuario</div>
      <div class="modal-sub"  id="detRol"></div>
    </div>
    <div style="text-align:center;margin:8px 0 18px">
      <div id="detAvatar" style="width:60px;height:60px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:800;font-size:22px;color:var(--ink-900);background:var(--gold)"></div>
    </div>
    <div id="detRows"></div>
    <div class="modal-actions" style="margin-top:16px">
      <button class="btn btn-ghost" onclick="closeO('detOverlay')">Cerrar</button>
    </div>
  </div>
</div>

<div id="toast"></div>

<!-- ══════════════════════════════════════════════════════════════════════════ -->
<!-- JavaScript                                                               -->
<!-- ══════════════════════════════════════════════════════════════════════════ -->
<script>
  /* Usuarios inyectados desde PHP (ya con nombre_rol, nombre, telefono, correo) */
  const phpUsuarios = <?= $usersJson ?? '[]' ?>;

  // Variables globales para paginación de usuarios
  let currentUsuariosPage = 1;
  let totalUsuariosPages = 1;
  let totalUsuariosItems = 0;

  /* ── Render grid ────────────────────────────────────────────────── */
  function renderGrid(lista) {
    const grid = document.getElementById('usrGrid');
    if (!lista.length) {
      grid.innerHTML = `
        <div style="text-align:center;padding:48px;color:var(--text-muted)">
          <div style="font-size:40px;margin-bottom:12px">👤</div>
          <div>Sin usuarios registrados</div>
        </div>`;
      return;
    }

    const g = document.createElement('div');
    g.className = 'usr-grid';

    lista.forEach(u => {
      const initials = (u.nombre || '?').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
      const isAdmin = u.rol === 'ADMINISTRADOR';

      const d = document.createElement('div');
      d.className = 'usr-card';
      d.innerHTML = `
        <div class="usr-card-top">
          <div class="usr-av ${isAdmin ? '' : 'operador'}">${initials}</div>
          <div>
            <div class="usr-name">${u.nombre || '—'}</div>
            <div class="usr-role">${u.rol}</div>
          </div>
        </div>
        <div class="usr-row"><span>ID</span><span style="font-family:monospace">#${u.id_personal}</span></div>
        <div class="usr-row"><span>Usuario</span><span style="font-family:monospace">${u.usuario || '—'}</span></div>
        <div class="usr-row"><span>Teléfono</span><span>${u.telefono || '—'}</span></div>
        <div class="usr-row"><span>Correo</span><span style="font-size:11px">${u.correo || '—'}</span></div>
        <div style="margin-top:10px;padding-top:10px;border-top:1px solid var(--border)">
          <span class="badge ${isAdmin ? 'badge-primary' : 'badge-secondary'} badge-dot">${u.rol}</span>
        </div>
      `;
      d.onclick = () => abrirDet(u);
      g.appendChild(d);
    });

    grid.innerHTML = '';
    grid.appendChild(g);
    
    // Actualizar paginación de usuarios
    if (totalUsuariosPages > 1) {
      renderPagination('usuarios-pagination', currentUsuariosPage, totalUsuariosPages, cargarUsuariosPaginado);
    }
  }

  // Cargar usuarios paginados
  function cargarUsuariosPaginado(page = 1) {
    currentUsuariosPage = page;
    
    const itemsPerPage = 12;
    const startIndex = (page - 1) * itemsPerPage;
    const endIndex = startIndex + itemsPerPage;
    const paginatedItems = phpUsuarios.slice(startIndex, endIndex);
    
    totalUsuariosItems = phpUsuarios.length;
    totalUsuariosPages = Math.ceil(totalUsuariosItems / itemsPerPage);
    
    renderGrid(paginatedItems);
  }

  /* ── Filtro ─────────────────────────────────────────────────────────────── */
  function filtrarUsuarios() {
    const q   = (document.getElementById('buscarUsr').value || '').toLowerCase();
    const rol = document.getElementById('filtroRol').value;

    const lst = phpUsuarios.filter(u => {
      const matchQ = !q
        || (u.nombre  || '').toLowerCase().includes(q)
        || (u.correo  || '').toLowerCase().includes(q)
        || (u.usuario || '').toLowerCase().includes(q);
      const matchR = rol === 'todos' || u.rol === rol;
      return matchQ && matchR;
    });
    renderGrid(lst);
  }

  /* ── Modal detalle ──────────────────────────────────────────────────────── */
  function abrirDet(u) {
    const initials = (u.nombre || '?').split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
    const isAdmin  = u.rol === 'ADMINISTRADOR';

    document.getElementById('detNombre').textContent = u.nombre || '—';
    document.getElementById('detRol').textContent    = u.rol;
    document.getElementById('detAvatar').textContent = initials;
    document.getElementById('detAvatar').style.background = isAdmin ? 'var(--gold)' : 'var(--text-secondary)';

    document.getElementById('detRows').innerHTML = `
      <div class="info-row"><span class="ir-label">ID</span><span class="ir-value" style="font-family:monospace">#${u.id_personal}</span></div>
      <div class="info-row"><span class="ir-label">Nombre</span><span class="ir-value">${u.nombre || '—'}</span></div>
      <div class="info-row"><span class="ir-label">Usuario</span><span class="ir-value" style="font-family:monospace">${u.usuario || '—'}</span></div>
      <div class="info-row"><span class="ir-label">Rol</span><span class="ir-value">
        <span class="badge ${isAdmin ? 'badge-primary' : 'badge-secondary'} badge-dot">${u.rol}</span>
      </span></div>
      <div class="info-row"><span class="ir-label">Teléfono</span><span class="ir-value">${u.telefono || '—'}</span></div>
      <div class="info-row"><span class="ir-label">Correo</span><span class="ir-value">${u.correo || '—'}</span></div>`;

    document.getElementById('detOverlay').classList.add('open');
  }

  /* ── Nuevo usuario — pasos ──────────────────────────────────────────────── */
  function abrirNuevo() {
    resetNuSteps();
    ['nuNombre','nuTel','nuCedula','nuCorreo','nuUsuario','nuPassword'].forEach(id => {
      document.getElementById(id).value = '';
    });
    ['nuErr1','nuErr2'].forEach(id => {
      document.getElementById(id).style.display = 'none';
    });
    document.getElementById('nuevoOverlay').classList.add('open');
  }

  function nuNext1() {
    const nombre = document.getElementById('nuNombre').value.trim();
    const correo = document.getElementById('nuCorreo').value.trim();
    const cedula = document.getElementById('nuCedula').value.trim();
    const err    = document.getElementById('nuErr1');

    if (!nombre || !correo || !cedula) {
      err.textContent = 'Nombre, cédula y correo son obligatorios.';
      err.style.display = 'flex';
      return;
    }
    err.style.display = 'none';
    showNuStep(2);
  }

  function nuBack1() { showNuStep(1); }

  function nuNext2() {
    const usuario = document.getElementById('nuUsuario').value.trim();
    const pass    = document.getElementById('nuPassword').value;
    const err     = document.getElementById('nuErr2');

    if (!usuario || !pass) {
      err.textContent = 'Usuario y contraseña son obligatorios.';
      err.style.display = 'flex';
      return;
    }
    if (pass.length < 6) {
      err.textContent = 'La contraseña debe tener al menos 6 caracteres.';
      err.style.display = 'flex';
      return;
    }
    err.style.display = 'none';

    /* Rellenar pantalla de confirmación */
    document.getElementById('cfNombre').textContent  = document.getElementById('nuNombre').value.trim();
    document.getElementById('cfCedula').textContent  = document.getElementById('nuCedula').value.trim();
    document.getElementById('cfCorreo').textContent  = document.getElementById('nuCorreo').value.trim();
    document.getElementById('cfTel').textContent     = document.getElementById('nuTel').value.trim() || '—';
    document.getElementById('cfUsuario').textContent = usuario;
    document.getElementById('cfRol').textContent     = document.getElementById('nuRol').value;

    showNuStep(3);
  }

  function nuBack2() { showNuStep(2); }

  function submitNuevo() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '../../controllers/controllerusuario.php';

    const fields = {
      nombre:   document.getElementById('nuNombre').value,
      telefono: document.getElementById('nuTel').value,
      cedula:   document.getElementById('nuCedula').value,
      correo:   document.getElementById('nuCorreo').value,
      rol:      document.getElementById('nuRol').value,
      usuario:  document.getElementById('nuUsuario').value,
      password: document.getElementById('nuPassword').value,
    };

    Object.entries(fields).forEach(([k, v]) => {
      const i = document.createElement('input');
      i.type = 'hidden'; i.name = k; i.value = v;
      form.appendChild(i);
    });

    /* Mostrar paso de éxito y luego enviar */
    document.getElementById('nuSuccessMsg').textContent =
      `${fields.nombre} (${fields.rol}) fue registrado correctamente.`;
    showNuStep(4);
    setTimeout(() => { document.body.appendChild(form); form.submit(); }, 1400);
  }

  /* ── Helpers de pasos ───────────────────────────────────────────────────── */
  function resetNuSteps() {
    ['nuStep1','nuStep2','nuStep3','nuStep4'].forEach((id, k) => {
      document.getElementById(id).classList.toggle('active', k === 0);
    });
    ['nu1','nu2','nu3'].forEach(id => {
      document.getElementById(id).classList.remove('active','done');
    });
    document.getElementById('nu1').classList.add('active');
  }

  function showNuStep(n) {
    ['nuStep1','nuStep2','nuStep3','nuStep4'].forEach((id, k) => {
      document.getElementById(id).classList.toggle('active', k === n - 1);
    });
    const ids = ['nu1','nu2','nu3'];
    ids.forEach((id, k) => {
      document.getElementById(id).classList.remove('active','done');
      if (k < n - 1) document.getElementById(id).classList.add('done');
    });
    if (n <= 3) document.getElementById(ids[n - 1]).classList.add('active');
  }

  /* ── Utilidades globales ────────────────────────────────────────────────── */
  function closeO(id) { document.getElementById(id).classList.remove('open'); }

  function toast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3200);
  }

  /* Cerrar overlay al hacer clic fuera del modal */
  document.querySelectorAll('.overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
  });

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
    info.textContent = containerId === 'usuarios-pagination' ? 
      `${totalUsuariosItems} usuarios` : 
      `${totalUsuariosItems} usuarios`;
    container.appendChild(info);
  }

  /* Render inicial */
  document.addEventListener('DOMContentLoaded', function() {
    renderGrid(phpUsuarios);
    
    // Forzar inicialización de paginación de usuarios
    const paginationContainer = document.getElementById('usuarios-pagination');
    if (paginationContainer) {
      // Inicializar variables globales
      totalUsuariosItems = phpUsuarios.length;
      totalUsuariosPages = Math.max(1, Math.ceil(totalUsuariosItems / 12));
      currentUsuariosPage = 1;
      
      // Mostrar paginación inicial
      renderPagination('usuarios-pagination', currentUsuariosPage, totalUsuariosPages, cargarUsuariosPaginado);
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