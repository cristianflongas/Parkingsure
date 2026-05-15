<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Acceso</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root {
      --gold: #ffd700;
      --gold-light: #ffe44d;
      --gold-dim: rgba(255, 215, 0, 0.15);
      --ink-900: #0a0a0a;
      --ink-800: #1a1a1a;
      --ink-700: #2c2c2c;
      --text-primary: #0a0a0a;
      --text-secondary: #404040;
      --text-muted: #737373;
      --border: #e0e0e0;
      --border-md: #cccccc;
      --surface-1: #ffffff;
      --surface-2: #f0f2f5;
      --surface-3: #e4e6e9;
      --error: #c62828;
      --error-bg: #ffebee;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', -apple-system, sans-serif;
      background-color: var(--surface-2);
      min-height: 100vh;
      display: flex;
      -webkit-font-smoothing: antialiased;
    }

    /* ── LEFT PANEL ── */
    .brand-side {
      flex: 1.1;
      background: var(--surface-1);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 52px 56px;
      position: relative;
      overflow: hidden;
    }

    /* Subtle background element */
    .brand-side::before {
      content: '';
      position: absolute;
      top: -100px;
      left: -100px;
      width: 400px;
      height: 400px;
      background: radial-gradient(circle, var(--gold-dim) 0%, transparent 70%);
      pointer-events: none;
    }

    .brand-top { position: relative; z-index: 1; }

    .brand-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 48px;
    }
    .logo-hex {
      width: 40px; height: 40px;
      background: var(--gold);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-weight: 900; font-size: 18px;
      color: var(--ink-900);
    }
    .logo-name {
      font-size: 22px; font-weight: 900;
      color: var(--text-primary); letter-spacing: -0.5px;
    }
    .logo-name em { color: var(--text-muted); font-style: normal; font-weight: 500; }

    .brand-tagline {
      font-size: 11px; font-weight: 700; letter-spacing: 2px;
      text-transform: uppercase; color: var(--text-muted); margin-bottom: 16px;
    }

    .brand-headline {
      font-size: 36px; font-weight: 900;
      color: var(--text-primary); line-height: 1.1; margin-bottom: 20px;
      letter-spacing: -1px;
    }

    .brand-desc {
      font-size: 15px; line-height: 1.6; color: var(--text-secondary);
      font-weight: 400; margin-bottom: 40px; max-width: 380px;
    }

    .feature-list { display: flex; flex-direction: column; gap: 16px; position: relative; z-index: 1; }
    .feature-item {
      display: flex; align-items: center; gap: 16px;
      font-size: 14px; color: var(--text-secondary); font-weight: 500;
      padding: 16px;
      background: var(--surface-2);
      border: 1px solid var(--border);
      border-radius: 8px;
      transition: all .2s ease;
    }
    .feature-item:hover { border-color: var(--border-md); background: var(--surface-3); }
    .feature-icon {
      width: 36px; height: 36px;
      background: var(--surface-1);
      border: 1px solid var(--border);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; flex-shrink: 0;
    }
    .feature-text strong { display: block; font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 2px; }
    .feature-text span   { font-size: 12px; color: var(--text-secondary); font-weight: 400; }

    .brand-bottom { position: relative; z-index: 1; }
    .brand-footer { font-size: 12px; color: var(--text-muted); font-weight: 500; }

    /* ── RIGHT PANEL ── */
    .form-side {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 52px 80px;
      background: var(--surface-0);
    }

    .form-container {
      max-width: 400px;
      width: 100%;
      margin: 0 auto;
    }

    .form-eyebrow {
      font-size: 11px; font-weight: 700; letter-spacing: 1.5px;
      text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;
    }
    .form-title {
      font-size: 32px; font-weight: 900;
      color: var(--text-primary); margin-bottom: 12px;
      letter-spacing: -1px;
    }
    .form-sub { font-size: 14px; color: var(--text-secondary); margin-bottom: 40px; font-weight: 400; }

    .title-rule {
      width: 40px; height: 4px; background: var(--gold);
      margin-bottom: 24px;
    }

    form { display: flex; flex-direction: column; gap: 0; }

    .form-group { margin-bottom: 20px; }
    .form-label {
      display: block; font-size: 12px; font-weight: 600;
      color: var(--text-secondary); margin-bottom: 8px;
    }

    .input-wrap { position: relative; display: flex; align-items: center; }
    .i-icon {
      position: absolute; left: 14px;
      color: var(--text-muted); pointer-events: none; transition: color .2s;
      display: flex; align-items: center;
    }
    .form-input {
      width: 100%; padding: 12px 14px 12px 42px;
      background: var(--surface-1); border: 1px solid var(--border-md);
      border-radius: 8px; color: var(--text-primary);
      font-family: inherit; font-size: 14px; outline: none;
      transition: all .2s ease;
    }
    .form-input::placeholder { color: var(--text-muted); }
    .form-input:focus { border-color: var(--ink-800); box-shadow: 0 0 0 3px rgba(0,0,0,0.05); }
    .input-wrap:focus-within .i-icon { color: var(--ink-800); }

    .form-select {
      width: 100%; padding: 12px 36px 12px 14px;
      background: var(--surface-1); border: 1px solid var(--border-md);
      border-radius: 8px; color: var(--text-primary);
      font-family: inherit; font-size: 14px; font-weight: 500; outline: none; cursor: pointer;
      transition: all .2s ease; -webkit-appearance: none; appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23404040' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
      background-repeat: no-repeat; background-position: right 14px center;
    }
    .form-select:focus { border-color: var(--ink-800); box-shadow: 0 0 0 3px rgba(0,0,0,0.05); }

    .forgot-link {
      display: inline-block; margin-top: 8px; font-size: 13px;
      color: var(--ink-600); text-decoration: none; font-weight: 500;
      transition: color .2s;
    }
    .forgot-link:hover { color: var(--ink-900); text-decoration: underline; }

    .btn-login {
      width: 100%; padding: 14px;
      background: var(--gold); color: var(--ink-900);
      border: none; border-radius: 8px;
      font-family: inherit; font-size: 15px; font-weight: 700;
      cursor: pointer;
      transition: all .2s ease;
      margin-top: 12px;
    }
    .btn-login:hover { background: var(--gold-light); }
    .btn-login:active { transform: translateY(1px); }

    .error-msg {
      display: flex; align-items: center; gap: 8px;
      background: var(--error-bg); border: 1px solid rgba(198,40,40,.2);
      border-radius: 8px; padding: 12px 16px;
      font-size: 13px; color: var(--error); margin-top: 20px; font-weight: 500;
    }

    .form-footer { font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 32px; font-weight: 500; }

    /* Responsive */
    @media (max-width: 900px) {
      body { flex-direction: column; }
      .brand-side { padding: 40px 32px; min-height: auto; flex: none; border-right: none; border-bottom: 1px solid var(--border); }
      .brand-headline { font-size: 28px; }
      .form-side { padding: 40px 32px; }
      .form-container { max-width: 100%; }
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
      <div class="brand-tagline">Sistema de Gestión Profesional</div>
      <h1 class="brand-headline">
        Administración centralizada de tu parqueadero
      </h1>
      <p class="brand-desc">
        Plataforma integral diseñada para brindar confianza y eficiencia en la gestión de ingresos, módulos y facturación.
      </p>
    </div>

    <div class="feature-list">
      <div class="feature-item">
        <div class="feature-icon">🅿️</div>
        <div class="feature-text">
          <strong>Control de Módulos</strong>
          <span>Estado y ocupación en tiempo real</span>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">🧾</div>
        <div class="feature-text">
          <strong>Facturación Segura</strong>
          <span>Generación automática de comprobantes</span>
        </div>
      </div>
      <div class="feature-item">
        <div class="feature-icon">📊</div>
        <div class="feature-text">
          <strong>Reportes Precisos</strong>
          <span>Métricas y datos exportables al instante</span>
        </div>
      </div>
    </div>

    <div class="brand-bottom">
      <p class="brand-footer">© 2026 ParkingSure · Todos los derechos reservados</p>
    </div>
  </div>

  <!-- FORM PANEL -->
  <div class="form-side">
    <div class="form-container">
      <div class="form-eyebrow">Acceso Seguro</div>
      <h2 class="form-title">Iniciar Sesión</h2>
      <div class="title-rule"></div>
      <p class="form-sub">Ingresa tus credenciales para continuar.</p>

      <form action="../../controllers/loginController.php" method="POST">
      <input type="hidden" name="action" value="login">



        <div class="form-group">
          <label class="form-label">Usuario</label>
          <div class="input-wrap">
            <span class="i-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </span>
            <input type="text" class="form-input" name="usuario" id="usuario" placeholder="Ingresa tu usuario" autocomplete="username" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Contraseña</label>
          <div class="input-wrap">
            <span class="i-icon">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <input type="password" class="form-input" name="password" id="password" placeholder="••••••••" autocomplete="current-password" required>
          </div>
          <a href="#" class="forgot-link">¿Olvidaste tu contraseña?</a>
        </div>

        <button class="btn-login" type="submit" name="submit" value="login">INGRESAR AL SISTEMA</button>
      </form>

      <div style="text-align:center; margin-top:24px;">
        <a href="../../index.php" class="forgot-link" style="font-size:14px; font-weight:600; text-decoration:none;">← Volver al sitio web</a>
      </div>

      <?php if(isset($_GET['error'])): ?>
        <div class="error-msg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
          Credenciales incorrectas. Inténtalo de nuevo.
        </div>
      <?php endif; ?>

      <p class="form-footer">Sistema exclusivo para personal autorizado</p>
    </div>
  </div>

</body>
</html>
