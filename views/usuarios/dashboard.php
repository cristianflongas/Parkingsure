<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Dashboard</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; }
    .occupancy-bars { display: flex; flex-direction: column; gap: 14px; }
    .occ-item { }
    .occ-head { display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 7px; }
    .occ-qty  { color: var(--text-muted); font-size: 12px; font-weight: 400; }
    .occ-track { height: 6px; background: var(--surface-3); border-radius: 3px; overflow: hidden; }
    .occ-fill  { height: 100%; border-radius: 3px; background: var(--gold); transition: width .6s cubic-bezier(.4,0,.2,1); }

    /* Entry/exit event list */
    .event-dot {
      width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0;
      box-shadow: 0 0 6px currentColor;
    }
    .event-dot.in  { background: var(--emerald); color: var(--emerald); }
    .event-dot.out { background: var(--crimson);  color: var(--crimson); }

    /* Live indicator */
    .live-pill {
      display: inline-flex; align-items: center; gap: 5px;
      background: rgba(16,185,129,.1); border: 1px solid rgba(16,185,129,.2);
      border-radius: 20px; padding: 3px 10px;
      font-size: 10px; font-weight: 700; letter-spacing: .8px;
      color: var(--emerald); text-transform: uppercase;
    }
    .live-dot {
      width: 6px; height: 6px; border-radius: 50%;
      background: var(--emerald);
      animation: pulse 1.8s ease infinite;
    }
    @keyframes pulse {
      0%,100% { opacity:1; transform:scale(1);   }
      50%      { opacity:.5; transform:scale(1.4); }
    }

    /* Stat card accent lines */
    .sc-total   { border-top: 2px solid rgba(252, 252, 252, 0.15); }
    .sc-free    { border-top: 2px solid var(--emerald); }
    .sc-busy    { border-top: 2px solid var(--crimson); }
    .sc-revenue { border-top: 2px solid var(--gold); }

    @media(max-width:900px){ .main-grid { grid-template-columns: 1fr; } }
  </style>
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar">
  <a class="logo">
    <div class="logo-mark">P</div>
    <div class="logo-text">PARKING<em>SURE</em></div>
  </a>
  <div class="nav-links">
    <a class="nb active" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <a class="nb" href="reportes.php"><span class="nav-icon"></span> Reportes</a>
  </div>
  <div class="nav-right">
    <div class="user-avatar">C</div>
    <div class="user-info">
      <div class="u-name">Cristian Longas</div>
      <div class="u-role">Administrador</div>
    </div>
    <a class="btn-logout" href="login.php">Salir</a>
  </div>
</nav>

<div class="page">

  <!-- Page header -->
  <div class="page-header">
    <div class="page-eyebrow">Panel de control</div>
    <h1 class="page-title">Dashboard</h1>
    <div class="title-rule"></div>
    <p class="page-sub" id="fecha-hoy" ></p>
  </div>

  <!-- Stat cards -->
  <div class="stats-grid stats-grid-4" style="margin-bottom:24px">
    <div class="stat-card sc-revenue">
      <div class="stat-label">Módulos Totales</div>
      <div class="stat-value gold">16</div>
      <div class="stat-sub">configurados</div>
    </div>
    <div class="stat-card sc-revenue">
      <div class="stat-label">Disponibles</div>
      <div class="stat-value gold">10</div>
      <div class="stat-sub">libres ahora</div>
    </div>
    <div class="stat-card sc-revenue">
      <div class="stat-label">Ocupados</div>
      <div class="stat-value gold">6</div>
      <div class="stat-sub">en uso</div>
    </div>
    <div class="stat-card sc-revenue">
      <div class="stat-label">Ingresos Hoy</div>
      <div class="stat-value gold">$48.500</div>
      <div class="stat-sub">COP</div>
    </div>
  </div>

  <!-- Main grid -->
  <div class="main-grid">

    <!-- Entries/Exits table -->
    <div class="card">
      <div class="card-title">
        <span class="card-title-icon"></span>
        Últimas Entradas y Salidas
        <span style="margin-left:auto"><span class="live-pill"><span class="live-dot"></span>En vivo</span></span>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Placa</th>
              <th>Módulo</th>
              <th>Evento</th>
              <th>Hora</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><strong>ABC-123</strong></td>
              <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">A-03</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:white;font-weight:600">▲ Entrada</span></td>
              <td style="color:var(--text-secondary)">09:14</td>
              <td><span class="badge badge-gold badge-dot">Activo</span></td>
            </tr>
            <tr>
              <td><strong>XYZ-789</strong></td>
              <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">B-07</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:white);font-weight:600">▼ Salida</span></td>
              <td style="color:var(--text-secondary)">08:52</td>
              <td><span class="badge badge-gold badge-dot">Salida</span></td>
            </tr>
            <tr>
              <td><strong>PLT-456</strong></td>
              <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">A-01</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:white;font-weight:600">▲ Entrada</span></td>
              <td style="color:var(--text-secondary)">08:30</td>
              <td><span class="badge badge-gold badge-dot">Activo</span></td>
            </tr>
            <tr>
              <td><strong>MOT-321</strong></td>
              <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">C-02</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:white;font-weight:600">▼ Salida</span></td>
              <td style="color:var(--text-secondary)">08:10</td>
              <td><span class="badge badge-gold badge-dot">Salida</span></td>
            </tr>
            <tr>
              <td><strong>GHI-654</strong></td>
              <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">C-01</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:white;font-weight:600">▲ Entrada</span></td>
              <td style="color:var(--text-secondary)">07:48</td>
              <td><span class="badge badge-gold badge-dot">Activo</span></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Occupancy by type -->
    <div class="card">
      <div class="card-title">
        <span class="card-title-icon">📈</span>
        Ocupación por Tipo
      </div>
      <div class="occupancy-bars">
        <div class="occ-item">
          <div class="occ-head"><span>🚗 Automóvil</span><span class="occ-qty">5 veh.</span></div>
          <div class="occ-track"><div class="occ-fill" style="width:62%;background:var(--gold)"></div></div>
        </div>
        <div class="occ-item">
          <div class="occ-head"><span>🏍️ Motocicleta</span><span class="occ-qty">3 veh.</span></div>
          <div class="occ-track"><div class="occ-fill" style="width:37%;background:var(--gold)"></div></div>
        </div>
        <div class="occ-item">
          <div class="occ-head"><span>🚛 Camión</span><span class="occ-qty">2 veh.</span></div>
          <div class="occ-track"><div class="occ-fill" style="width:25%;background:var(--gold)"></div></div>
        </div>
        <div class="occ-item">
          <div class="occ-head"><span>🚌 Bus</span><span class="occ-qty">0 veh.</span></div>
          <div class="occ-track"><div class="occ-fill" style="width:0%;background:var(--gold)"></div></div>
        </div>
      </div>

      <hr class="divider">

      <div class="card-title" style="margin-bottom:12px">
        <span class="card-title-icon">⚡</span>
        Accesos Rápidos
      </div>
      <div style="display:flex;flex-direction:column;gap:8px">
        <a href="parqueadero.php" class="btn btn-secondary btn-full" style="justify-content:flex-start;gap:10px">
          🅿️ &nbsp;Ver Parqueadero Virtual
        </a>
        <a href="vehiculos.php" class="btn btn-secondary btn-full" style="justify-content:flex-start;gap:10px">
          🚗 &nbsp;Registrar Vehículo
        </a>
        <a href="pagos.php" class="btn btn-secondary btn-full" style="justify-content:flex-start;gap:10px">
          💳 &nbsp;Gestionar Pago
        </a>
      </div>
    </div>

  </div>
</div>

<div id="toast"></div>
<script>
  document.getElementById('fecha-hoy').textContent =
    new Date().toLocaleDateString('es-CO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
  function toast(msg){ const t=document.getElementById('toast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),3000); }
</script>
</body>
</html>