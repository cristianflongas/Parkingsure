<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Acceso</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --ink-900:#08090c; --ink-800:#0d1017; --ink-700:#131822; --ink-600:#1a2130;
      --ink-500:#22293a; --ink-400:#2c3549; --ink-300:#3d4f68; --ink-200:#5a6f8a;
      --gold:#e8b84b; --gold-light:#f5d278; --gold-dim:rgba(232,184,75,.12);
      --emerald:#10b981; --crimson:#ef4444;
      --text-primary:#eef2f8; --text-secondary:#8a9db8; --text-muted:#4d607a;
      --border:rgba(255,255,255,.07); --border-md:rgba(255,255,255,.12);
      --surface-1:#131822; --surface-2:#1a2130; --surface-3:#22293a;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Outfit', sans-serif;
      background-color: white;
      min-height: 100vh;
      display: flex;
      -webkit-font-smoothing: antialiased;
    }

    /* ── LEFT PANEL ── */
    .brand-side {
      flex: 1.1;
      background: white;
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 52px 56px;
      position: relative;
      overflow: hidden;
    }

    /* Decorative hex grid */
    .brand-side::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: radial-gradient(circle, rgba(232,184,75,.06) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
    }

    /* Gold vertical bar accent */
    .brand-side::after {
      content: '';
      position: absolute;
      top: 0; right: 0;
      width: 1px;
      height: 100%;
      background: linear-gradient(to bottom, transparent, var(--gold), transparent);
      opacity: .3;
    }

    .brand-top { position: relative; z-index: 1; }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 48px;
    }
    .logo-hex {
      width: 44px; height: 44px;
      background: var(--gold);
      clip-path: polygon(50% 0%,100% 25%,100% 75%,50% 100%,0% 75%,0% 25%);
      display: flex; align-items: center; justify-content: center;
      font-family: 'Syne', sans-serif; font-weight: 800; font-size: 17px;
      color: var(--ink-900);
    }
    .logo-name {
      font-family: 'Syne', sans-serif; font-size: 22px; font-weight: 800;
      color: black; letter-spacing: .5px;
    }
    .logo-name em { color: var(--gold); font-style: normal; }

    .brand-tagline {
      font-size: 10px; font-weight: 700; letter-spacing: 3px;
      text-transform: uppercase; color: black; margin-bottom: 16px;
    }

    .brand-headline {
      font-family: 'Syne', sans-serif; font-size: 34px; font-weight: 800;
      color: var(--black); line-height: 1.2; margin-bottom: 20px;
    }
    .brand-headline em {
      color: var(--gold); font-style: normal; display: block;
    }

    .brand-desc {
      font-size: 14px; line-height: 1.7; color: black;
      font-weight: 400; margin-bottom: 36px; max-width: 340px;
    }

    .feature-list { display: flex; flex-direction: column; gap: 12px; position: relative; z-index: 1; }
    .feature-item {
      display: flex; align-items: center; gap: 12px;
      font-size: 13px; color: var(--text-secondary); font-weight: 400;
      padding: 12px 16px;
      background: rgba(255,255,255,.03);
      border: 1px solid var(--border);
      border-radius: 10px;
      transition: border-color .2s, background .2s;
      animation: slide-up .6s ease both;
    }
    .feature-item:nth-child(1) { animation-delay: .3s; }
    .feature-item:nth-child(2) { animation-delay: .45s; }
    .feature-item:nth-child(3) { animation-delay: .6s; }
    .feature-item:hover { border-color: rgba(232,184,75,.2); background: var(--gold-dim); }
    .feature-icon {
      width: 32px; height: 32px;
      background: var(--gold-dim);
      border: 1px solid rgba(232,184,75,.2);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 15px; flex-shrink: 0;
    }
    .feature-text strong { display: block; font-size: 13px; font-weight: 600; color: black; }
    .feature-text span   { font-size: 11px; color: black; }

    .brand-bottom { position: relative; z-index: 1; }
    .brand-footer { font-size: 11px; color: var(--text-muted); }

    /* ── RIGHT PANEL ── */
    .form-side {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 52px 60px;
      background: var(--ink-900);
      animation: fade-right .6s ease both;
    }

    .form-eyebrow {
      font-size: 10px; font-weight: 700; letter-spacing: 2.5px;
      text-transform: uppercase; color: var(--gold); margin-bottom: 8px;
    }
    .form-title {
      font-family: 'Syne', sans-serif; font-size: 30px; font-weight: 800;
      color: var(--text-primary); margin-bottom: 6px;
    }
    .form-sub { font-size: 13.5px; color: var(--text-secondary); margin-bottom: 36px; font-weight: 400; }

    /* divider rule */
    .form-title + .title-rule {
      display: flex; align-items: center; gap: 8px; margin-bottom: 28px;
    }
    .title-rule::before { content:''; width: 28px; height: 2px; background: var(--gold); border-radius:1px; display:block; }
    .title-rule::after  { content:''; width: 5px; height: 5px; border-radius:50%; background: var(--gold); opacity:.5; display:block; }

    form { display: flex; flex-direction: column; gap: 0; }

    .form-group { margin-bottom: 18px; }
    .form-label {
      display: block; font-size: 10px; font-weight: 700; letter-spacing: 1.5px;
      text-transform: uppercase; color: var(--text-secondary); margin-bottom: 7px;
    }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .i-icon {
      position: absolute; left: 13px;
      color: var(--text-muted); pointer-events: none; transition: color .18s;
      display: flex; align-items: center;
    }
    .form-input {
      width: 100%; padding: 12px 14px 12px 40px;
      background: var(--surface-2); border: 1px solid var(--border-md);
      border-radius: 10px; color: var(--text-primary);
      font-family: 'Outfit', sans-serif; font-size: 14px; outline: none;
      transition: border-color .18s, box-shadow .18s, background .18s;
    }
    .form-input::placeholder { color: var(--text-muted); }
    .form-input:focus { border-color: var(--gold); background: var(--surface-3); box-shadow: 0 0 0 3px rgba(232,184,75,.12); }
    .input-wrap:focus-within .i-icon { color: var(--gold); }

    .form-select {
      width: 100%; padding: 12px 36px 12px 14px;
      background: var(--surface-2); border: 1px solid var(--border-md);
      border-radius: 10px; color: var(--text-primary);
      font-family: 'Outfit', sans-serif; font-size: 14px; outline: none; cursor: pointer;
      transition: border-color .18s; -webkit-appearance: none; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%235a6f8a' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 14px center;
    }
    .form-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(232,184,75,.12); }
    .form-select option { background: var(--surface-2); }

    .forgot-link {
      display: inline-block; margin-top: 6px; font-size: 12px;
      color: var(--gold); text-decoration: none; font-weight: 500;
      transition: opacity .15s;
    }
    .forgot-link:hover { opacity: .75; }

    .btn-login {
      width: 100%; padding: 14px;
      background: var(--gold); color: var(--ink-900);
      border: none; border-radius: 10px;
      font-family: 'Syne', sans-serif; font-size: 16px; font-weight: 800;
      letter-spacing: 2px; cursor: pointer;
      box-shadow: 0 4px 20px rgba(232,184,75,.3);
      transition: background .2s, transform .15s, box-shadow .2s;
      margin-top: 8px;
    }
    .btn-login:hover { background: var(--gold-light); transform: translateY(-1px); box-shadow: 0 8px 28px rgba(232,184,75,.45); }
    .btn-login:active { transform: translateY(0); }

    .error-msg {
      display: flex; align-items: center; gap: 8px;
      background: rgba(239,68,68,.08); border: 1px solid rgba(239,68,68,.25);
      border-radius: 8px; padding: 11px 14px;
      font-size: 13px; color: #f87171; margin-top: 14px; font-weight: 500;
    }

    .form-footer { font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 24px; }

    /* Animations */
    @keyframes slide-up   { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
    @keyframes fade-right { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }

    /* Responsive */
    @media (max-width: 860px) {
      body { flex-direction: column; }
      .brand-side { padding: 36px 28px; min-height: auto; }
      .brand-headline { font-size: 24px; color:black; }
      .form-side { padding: 36px 28px; }
    }
  </style>
</head>
<body>

  <!-- BRAND PANEL -->
  <div class="brand-side">
    <div class="brand-top">
      <div class="brand-logo">
        <div class="logo-hex">P</div>
        <div class="logo-name">PARKING<em>SURE</em></div>
      </div>
      <div class="brand-tagline">Sistema de Gestión Premium</div>
      <h1 class="brand-headline">
        Control total<br>de tu parqueadero.
        <em>En tiempo real.</em>
      </h1>
      <p class="brand-desc">
        Plataforma integral para la administración profesional de estacionamientos. Gestiona módulos, vehículos, pagos y reportes desde un solo lugar.
      </p>
    </div>

    <div class="feature-list">
      <div class="feature-item">
        <div class="feature-text">
          <strong>Parqueadero Virtual</strong>
          <span>Estado de módulos en tiempo real</span>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-text">
          <strong>Facturación Automática</strong>
          <span>Tickets y facturas en PDF al instante</span>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-text">
          <strong>Reportes Ejecutivos</strong>
          <span>Ventas, entradas y estadísticas diarias</span>
        </div>
      </div>
    </div>

    <div class="brand-bottom">
      <p class="brand-footer">© 2026 ParkingSure· Todos los derechos reservados</p>
    </div>
  </div>

  <!-- FORM PANEL -->
  <div class="form-side">
    <div class="form-eyebrow">Bienvenido de vuelta</div>
    <h2 class="form-title">Iniciar Sesión</h2>
    <div class="title-rule"></div>
    <p class="form-sub">Ingresa tus credenciales para acceder al sistema.</p>

    <form action="../../controllers/controllerlogin.php" method="POST">

      <div class="form-group">
        <label class="form-label">Rol de acceso</label>
        <select class="form-select" name="rol" id="rol">
          <option value="ADMINISTRADOR">⚙️  ADMINISTRADOR</option>
          <option value="EMPLEADO">👤  EMPLEADO</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Usuario</label>
        <div class="input-wrap">
          <span class="i-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          </span>
          <input type="text" class="form-input" name="usuario" id="usuario" placeholder="Nombre de usuario" autocomplete="username">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Contraseña</label>
        <div class="input-wrap">
          <span class="i-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          </span>
          <input type="password" class="form-input" name="password" id="password" placeholder="••••••••" autocomplete="current-password">
        </div>
        <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
      </div>

      <button class="btn-login" type="submit">INGRESAR AL SISTEMA</button>
    </form>

    <?php if(isset($_GET['error'])): ?>
      <div class="error-msg">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        Credenciales Incorrectas. Inténtalo de nuevo.
      </div>
    <?php endif; ?>

    <p class="form-footer">Sistema protegido · Acceso solo para personal autorizado</p>
  </div>

</body>
</html>
