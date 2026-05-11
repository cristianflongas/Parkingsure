<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/ModuloModel.php';
require_once __DIR__ . '/../../config/database.php';

requireLogin();

$moduloModel = new ModuloModel();
$disponibles = $moduloModel->getDisponibles();

// Cargar tipos de servicio desde BD
$pdo  = getDB();
$tipos = $pdo->query('SELECT id_tipo_servicio, nombre_tipo_servicio, tarifa FROM tipo_servicio WHERE estado = "ACTIVO" ORDER BY nombre_tipo_servicio')->fetchAll();

$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARKINGSURE — Registrar Entrada</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; font-family: 'Segoe UI', sans-serif; color: #e2e8f0; min-height: 100vh; }
        header {
            background: #1e293b; border-bottom: 1px solid #334155;
            padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;
        }
        header h1 { color: #38bdf8; font-size: 1.2rem; letter-spacing: 2px; }
        header nav a { color: #94a3b8; text-decoration: none; margin-left: 1.5rem; font-size: .9rem; }
        header nav a:hover { color: #e2e8f0; }
        main { padding: 2rem; max-width: 680px; margin: 0 auto; }
        h2 { font-size: 1.3rem; margin-bottom: .4rem; }
        .subtitle { color: #64748b; font-size: .88rem; margin-bottom: 2rem; }
        .card {
            background: #1e293b; border: 1px solid #334155;
            border-radius: 12px; padding: 2rem;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }
        .form-group { margin-bottom: 1.3rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; font-size: .82rem; color: #94a3b8; margin-bottom: .4rem; }
        input, select {
            width: 100%; padding: .65rem 1rem;
            background: #0f172a; border: 1px solid #334155;
            border-radius: 8px; color: #e2e8f0; font-size: .95rem;
            transition: border .2s;
        }
        input:focus, select:focus { outline: none; border-color: #38bdf8; }
        input.placa { text-transform: uppercase; font-size: 1.2rem; font-weight: 700;
                      letter-spacing: 3px; text-align: center; }
        .tarifa-preview {
            background: #0f172a; border: 1px solid #334155; border-radius: 8px;
            padding: .65rem 1rem; font-size: .88rem; color: #64748b; min-height: 42px;
            display: flex; align-items: center;
        }
        .tarifa-preview span { color: #4ade80; font-weight: 700; font-size: 1rem; }
        .modulo-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: .6rem; margin-top: .4rem;
        }
        .modulo-btn {
            background: #052e16; border: 2px solid #166534; border-radius: 8px;
            padding: .7rem .4rem; text-align: center; cursor: pointer;
            transition: all .15s; position: relative;
        }
        .modulo-btn:hover { background: #064e3b; border-color: #4ade80; }
        .modulo-btn input[type="radio"] { display: none; }
        .modulo-btn.selected { background: #064e3b; border-color: #4ade80; }
        .modulo-btn .mod-id { font-size: .75rem; color: #64748b; }
        .modulo-btn .mod-loc { font-size: .82rem; font-weight: 600; color: #6ee7b7; }
        .no-modulos { color: #f87171; font-size: .9rem; padding: .5rem 0; }
        .btn-submit {
            width: 100%; padding: .85rem; background: #16a34a; border: none;
            border-radius: 8px; color: #fff; font-size: 1rem; font-weight: 700;
            cursor: pointer; transition: background .2s; margin-top: .5rem;
        }
        .btn-submit:hover { background: #15803d; }
        .btn-submit:disabled { background: #1e3a2e; color: #4b5563; cursor: not-allowed; }
        .flash-error {
            background: #450a0a; border: 1px solid #7f1d1d; color: #fca5a5;
            padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem;
        }
        .section-title { font-size: .78rem; color: #64748b; text-transform: uppercase;
                         letter-spacing: 1px; margin-bottom: .6rem; }
        .now-badge {
            background: #1e293b; border: 1px solid #334155; border-radius: 8px;
            padding: .65rem 1rem; color: #94a3b8; font-size: .9rem;
        }
        .now-badge strong { color: #e2e8f0; }
    </style>
</head>
<body>
<header>
    <h1>🅿 PARKINGSURE</h1>
    <nav>
        <a href="/parkingsure/views/dashboard/index.php">Dashboard</a>
        <a href="/parkingsure/controllers/ServicioController.php">Servicios</a>
        <a href="/parkingsure/controllers/PersonalController.php">Usuarios</a>
        <a href="/parkingsure/includes/logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>
    <?php if ($flashError): ?>
        <div class="flash-error">⚠ <?= htmlspecialchars($flashError) ?></div>
    <?php endif; ?>

    <h2>🚗 Registrar Entrada</h2>
    <p class="subtitle">HU-06 — Registro de entrada con asignación de módulo</p>

    <div class="card">
        <form method="POST" action="/parkingsure/controllers/ServicioController.php?accion=entrada"
              id="form-entrada">

            <div class="form-row">
                <!-- Placa -->
                <div class="form-group">
                    <label>Placa del vehículo *</label>
                    <input type="text" name="placa" class="placa"
                           placeholder="ABC-123" required maxlength="8"
                           oninput="this.value=this.value.toUpperCase()">
                </div>

                <!-- Fecha/hora entrada (solo referencia visual, el servidor usa NOW()) -->
                <div class="form-group">
                    <label>Fecha y hora de entrada</label>
                    <div class="now-badge">
                        <strong id="reloj">—</strong>
                        <span style="font-size:.8rem; color:#64748b;"> (automática)</span>
                    </div>
                </div>
            </div>

            <!-- Tipo de servicio -->
            <div class="form-group">
                <label>Tipo de servicio *</label>
                <?php if (empty($tipos)): ?>
                    <p style="color:#f87171; font-size:.88rem;">
                        ⚠ No hay tipos de servicio activos. Agrega uno en la tabla <code>tipo_servicio</code>.
                    </p>
                    <input type="hidden" name="id_tipo_servicio" value="0">
                <?php else: ?>
                    <select name="id_tipo_servicio" required id="sel-tipo" onchange="actualizarTarifa()">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($tipos as $t): ?>
                            <option value="<?= $t['id_tipo_servicio'] ?>"
                                    data-tarifa="<?= $t['tarifa'] ?>">
                                <?= htmlspecialchars($t['nombre_tipo_servicio']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>

            <!-- Preview tarifa -->
            <div class="form-group">
                <label>Tarifa por hora</label>
                <div class="tarifa-preview" id="tarifa-preview">
                    Selecciona un tipo de servicio…
                </div>
            </div>

            <!-- Selección de módulo -->
            <div class="form-group">
                <div class="section-title">Módulo asignado *</div>
                <?php if (empty($disponibles)): ?>
                    <p class="no-modulos">⚠ No hay módulos disponibles en este momento.</p>
                    <input type="hidden" name="id_modulo" value="0">
                <?php else: ?>
                    <div class="modulo-grid" id="modulo-grid">
                        <?php foreach ($disponibles as $m): ?>
                            <label class="modulo-btn" id="lbl-mod-<?= $m['id_modulo'] ?>"
                                   onclick="seleccionarModulo(<?= $m['id_modulo'] ?>)">
                                <input type="radio" name="id_modulo"
                                       value="<?= $m['id_modulo'] ?>" required>
                                <div class="mod-loc"><?= htmlspecialchars($m['ubicacion']) ?></div>
                                <div class="mod-id">#<?= $m['id_modulo'] ?></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-submit"
                <?= (empty($disponibles) || empty($tipos)) ? 'disabled' : '' ?>>
                ✓ Registrar Entrada
            </button>
        </form>
    </div>
</main>

<script>
// Reloj en tiempo real
function tick() {
    const now = new Date();
    document.getElementById('reloj').textContent =
        now.toLocaleDateString('es-CO') + '  ' +
        now.toLocaleTimeString('es-CO');
}
tick(); setInterval(tick, 1000);

// Actualizar preview de tarifa
function actualizarTarifa() {
    const sel = document.getElementById('sel-tipo');
    const opt = sel.options[sel.selectedIndex];
    const preview = document.getElementById('tarifa-preview');
    if (opt && opt.dataset.tarifa) {
        const tarifa = parseFloat(opt.dataset.tarifa).toFixed(2);
        preview.innerHTML = '<span>$' + tarifa + '</span> / hora';
    } else {
        preview.textContent = 'Selecciona un tipo de servicio…';
    }
}

// Resaltar módulo seleccionado
function seleccionarModulo(id) {
    document.querySelectorAll('.modulo-btn').forEach(b => b.classList.remove('selected'));
    const lbl = document.getElementById('lbl-mod-' + id);
    if (lbl) {
        lbl.classList.add('selected');
        lbl.querySelector('input[type="radio"]').checked = true;
    }
}
</script>
</body>
</html>