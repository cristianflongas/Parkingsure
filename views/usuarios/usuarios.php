<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Usuarios</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    .usr-layout { display:grid; grid-template-columns:1fr 1px 1fr; gap:0; background:var(--surface-1); border:1px solid var(--border); border-radius:var(--r-lg); overflow:hidden; }
    .col-div { background:var(--border); }
    .col-form, .col-list { padding:28px 30px; }
    .col-title { font-family:'Syne',sans-serif; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:1.2px; color:var(--text-primary); margin-bottom:22px; display:flex; align-items:center; gap:9px; }
    .col-title::before { content:''; width:3px; height:16px; background:var(--gold); border-radius:2px; flex-shrink:0; display:block; }
    .form-grid2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

    /* User item */
    .user-item {
      display:flex; align-items:center; justify-content:space-between;
      padding:13px 14px; background:var(--surface-2);
      border-radius:var(--r-md); margin-bottom:10px;
      border:1px solid var(--border);
      transition:border-color .18s;
    }
    .user-item:hover { border-color:var(--border-md); }
    .user-left { display:flex; align-items:center; gap:12px; }
    .user-av {
      width:38px; height:38px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      font-family:'Syne',sans-serif; font-weight:800; font-size:14px; color:var(--ink-900);
      flex-shrink:0;
    }
    .user-name { font-size:13px; font-weight:700; color:var(--text-primary); }
    .user-meta { font-size:11px; color:var(--text-muted); margin-top:2px; }
    .user-acts { display:flex; gap:6px; align-items:center; }

    @media(max-width:860px){ .usr-layout { grid-template-columns:1fr; } .col-div { height:1px; width:100%; } .form-grid2 { grid-template-columns:1fr; } }
    .usuarios{
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
  </style>
</head>
<body>
<nav class="topbar">
  <a class="logo"><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <div class="nav-links">
     <a class="nb " href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <a class="nb active" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
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
    <div class="page-eyebrow">Administración</div>
    <h1 class="page-title">Gestión de Usuarios</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Crear, modificar y gestionar usuarios y roles del sistema</p>
  </div>

  <?php if(isset($_GET['msg'])&&$_GET['msg']=='creado'): ?>
    <div class="alert alert-success" style="margin-bottom:18px">✅ Usuario creado correctamente</div>
  <?php endif; ?>

  <div class="usr-layout">
    <!-- Form -->
    <div class="col-form">
      <div class="col-title">Nuevo Usuario</div>
      <form action="../../controllers/controllerusuario.php" method="POST">
        <div class="form-grid2">
          <div class="form-group">
            <label class="form-label">Nombre completo</label>
            <input class="form-input" name="nombre" placeholder="Nombre completo">
          </div>
          <div class="form-group">
            <label class="form-label">Teléfono</label>
            <input class="form-input" name="telefono" placeholder="Ej: 3001234567">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">N° Documento (Cédula)</label>
          <input class="form-input" name="cedula" placeholder="Número de cédula">
        </div>
        <div class="form-group">
          <label class="form-label">Rol en el sistema</label>
          <select class="form-select" name="rol">
            <option value="ADMINISTRADOR">⚙️ Administrador</option>
            <option value="EMPLEADO">👤 Empleado</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Correo electrónico</label>
          <input class="form-input" name="correo" type="email" placeholder="correo@ejemplo.com">
        </div>
        <div class="form-grid2">
          <div class="form-group">
            <label class="form-label">Usuario</label>
            <input class="form-input" name="usuario" placeholder="Nombre de usuario">
          </div>
          <div class="form-group">
            <label class="form-label">Contraseña</label>
            <input class="form-input" name="password" type="password" placeholder="••••••••">
          </div>
        </div>
        <button class="btn btn-primary btn-full" type="submit">Crear Usuario</button>
      </form>
    </div>

    <div class="col-div"></div>

    <!-- List -->
    <div class="col-list">
      <div class="col-title">Usuarios del Sistema</div>
      <?php
    
      require_once __DIR__ . "/../../config/database.php";

$database = new Database();
$conn = $database->conectar();

$query = "SELECT id_personal, rol, nombre, telefono, correo 
          FROM personal 
          ORDER BY id_personal ASC";

$stmt = $conn->prepare($query);
$stmt->execute();

$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <table class="usuarios">
        
    <thead>
        <tr>
            <th>ID</th>
            <th>ROL</th>
            <th>NOMBRE</th>
            <th>TELEFONO</th>
            <th>CORREO</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row["id_personal"]) ?></td>
            <td><?= htmlspecialchars($row["rol"]) ?></td>
            <td><?= htmlspecialchars($row["nombre"]) ?></td>
            <td><?= htmlspecialchars($row["telefono"]) ?></td>
            <td><?= htmlspecialchars($row["correo"]) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
</script>
</body>
</html>