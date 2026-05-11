<?php
// Acceso: /parkingsure/controllers/ModuloController.php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if (!isset($modulos)) {
    require_once __DIR__ . '/../../models/ModuloModel.php';
    $model   = new ModuloModel();
    $modulos = $model->getAll();
}

$esAdmin    = currentUser()['rol'] === 'ADMINISTRADOR';
$flash      = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$conteo = ['DISPONIBLE' => 0, 'OCUPADO' => 0, 'MANTENIMIENTO' => 0];
foreach ($modulos as $m) {
    $key = $m['estado'];
    $conteo[$key] = ($conteo[$key] ?? 0) + 1;
}
$total = count($modulos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARKINGSURE — Módulos</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; font-family: 'Segoe UI', sans-serif; color: #e2e8f0; min-height: 100vh; }

        header {
            background: #1e293b; border-bottom: 1px solid #334155;
            padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 50;
        }
        header h1 { color: #38bdf8; font-size: 1.2rem; letter-spacing: 2px; }
        header nav a { color: #94a3b8; text-decoration: none; margin-left: 1.4rem; font-size: .88rem; transition: color .15s; }
        header nav a:hover, header nav a.active { color: #e2e8f0; }

        main { padding: 2rem; max-width: 1300px; margin: 0 auto; }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h2 { font-size: 1.25rem; }

        .flash { padding: .8rem 1.1rem; border-radius: 8px; margin-bottom: 1.4rem; font-size: .9rem; }
        .flash.ok  { background: #052e16; border: 1px solid #166534; color: #86efac; }
        .flash.err { background: #450a0a; border: 1px solid #7f1d1d; color: #fca5a5; }

        /* Stats */
        .stats-row { display: flex; gap: .8rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .stat-card { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: .9rem 1.4rem; min-width: 130px; }
        .stat-card .n { font-size: 1.9rem; font-weight: 700; }
        .stat-card .l { font-size: .72rem; color: #64748b; text-transform: uppercase; margin-top: .1rem; }
        .stat-card.verde .n { color: #4ade80; }
        .stat-card.rojo  .n { color: #f87171; }
        .stat-card.ambar .n { color: #fbbf24; }
        .stat-card.blue  .n { color: #38bdf8; }

        /* Leyenda */
        .legend { display: flex; gap: 1.2rem; margin-bottom: 1.5rem; flex-wrap: wrap; font-size: .82rem; color: #94a3b8; }
        .legend span { display: flex; align-items: center; gap: .35rem; }
        .dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .dot.verde { background: #4ade80; }
        .dot.rojo  { background: #f87171; }
        .dot.ambar { background: #fbbf24; }

        /* Grid parqueadero */
        .parking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: .85rem;
            margin-bottom: 2.5rem;
        }

        .slot {
            border-radius: 12px; padding: 1rem .9rem;
            text-align: center; border: 2px solid transparent;
            transition: transform .15s, box-shadow .15s;
            cursor: default; position: relative;
        }
        .slot:hover { transform: translateY(-3px); box-shadow: 0 6px 24px rgba(0,0,0,.35); }

        .slot.disponible    { background: #052e16; border-color: #166534; }
        .slot.ocupado       { background: #3b0a0a; border-color: #7f1d1d; }
        .slot.mantenimiento { background: #3b2000; border-color: #92400e; }

        .slot .icon    { font-size: 1.8rem; margin-bottom: .3rem; }
        .slot .ubicacion { font-size: .92rem; font-weight: 700; word-break: break-all; }
        .slot .mod-id  { font-size: .72rem; color: #64748b; margin-top: .15rem; }
        .slot .estado-label {
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .5px; margin-top: .35rem;
        }
        .slot.disponible    .estado-label { color: #4ade80; }
        .slot.ocupado       .estado-label { color: #f87171; }
        .slot.mantenimiento .estado-label { color: #fbbf24; }

        /* Acciones en slot */
        .slot-actions { display: flex; gap: .3rem; margin-top: .6rem; justify-content: center; flex-wrap: wrap; }
        .sbtn {
            padding: .22rem .55rem; border-radius: 5px; border: 1px solid transparent;
            font-size: .7rem; font-weight: 700; cursor: pointer; transition: background .12s;
        }
        .sbtn-edit  { background: #1e293b; border-color: #334155; color: #94a3b8; }
        .sbtn-edit:hover { background: #334155; color: #e2e8f0; }
        .sbtn-lib   { background: #052e16; border-color: #166534; color: #4ade80; }
        .sbtn-lib:hover { background: #064e3b; }
        .sbtn-mant  { background: #3b2000; border-color: #92400e; color: #fbbf24; }
        .sbtn-mant:hover { background: #4c2a00; }
        .sbtn-del   { background: #3b0a0a; border-color: #7f1d1d; color: #f87171; }
        .sbtn-del:hover { background: #4c0f0f; }

        /* Tabla detalle */
        h3 { font-size: 1rem; color: #94a3b8; font-weight: 500; margin-bottom: 1rem; }
        .table-wrap { overflow-x: auto; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; min-width: 500px; }
        thead th { background: #0f172a; padding: .7rem 1rem; text-align: left; font-size: .74rem; color: #64748b; text-transform: uppercase; }
        tbody td { padding: .7rem 1rem; border-bottom: 1px solid #0f172a; font-size: .88rem; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #162032; }

        .badge { display: inline-block; padding: .2rem .65rem; border-radius: 20px; font-size: .74rem; font-weight: 700; }
        .badge.disponible    { background: #052e16; color: #4ade80; }
        .badge.ocupado       { background: #3b0a0a; color: #f87171; }
        .badge.mantenimiento { background: #3b2000; color: #fbbf24; }

        /* Botones generales */
        .btn { padding: .5rem 1.1rem; border-radius: 8px; border: none; font-size: .87rem; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-block; }
        .btn-primary { background: #0ea5e9; color: #fff; }
        .btn-primary:hover { background: #0284c7; }
        .btn-cancel { background: #334155; color: #94a3b8; }
        .btn-cancel:hover { background: #475569; }

        /* Modales */
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.75); z-index: 200; align-items: center; justify-content: center; padding: 1rem; }
        .overlay.open { display: flex; }
        .modal { background: #1e293b; border: 1px solid #334155; border-radius: 14px; padding: 2rem; width: 100%; max-width: 420px; }
        .modal h3 { color: #38bdf8; margin-bottom: 1.3rem; font-size: 1.05rem; }
        .form-group { margin-bottom: 1.1rem; }
        .form-group label { display: block; font-size: .8rem; color: #94a3b8; margin-bottom: .35rem; }
        .form-group input,
        .form-group select { width: 100%; padding: .62rem .9rem; background: #0f172a; border: 1px solid #334155; border-radius: 8px; color: #e2e8f0; font-size: .9rem; }
        .form-group input:focus,
        .form-group select:focus { outline: none; border-color: #38bdf8; }
        .modal-footer { display: flex; gap: .75rem; margin-top: 1.4rem; }
        .modal-footer button,
        .modal-footer .btn { flex: 1; padding: .7rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer; font-size: .92rem; text-align: center; }
        .btn-save-blue  { background: #0ea5e9; color: #fff; }
        .btn-save-blue:hover { background: #0284c7; }
        .btn-save-green { background: #16a34a; color: #fff; }
        .btn-save-green:hover { background: #15803d; }

        .empty-msg { text-align: center; color: #475569; padding: 3rem; background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body>

<header>
    <h1>🅿 PARKINGSURE</h1>
    <nav>
        <a href="/parkingsure/views/dashboard/index.php">Dashboard</a>
        <a href="/parkingsure/controllers/VehiculoController.php">Vehículos</a>
        <a href="/parkingsure/controllers/ServicioController.php">Servicios</a>
        <a href="/parkingsure/views/servicios/entrada.php">+ Entrada</a>
        <a href="/parkingsure/controllers/ModuloController.php" class="active">Módulos</a>
        <a href="/parkingsure/controllers/FacturaController.php">Facturas</a>
        <a href="/parkingsure/controllers/PersonalController.php">Usuarios</a>
        <a href="/parkingsure/includes/logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>

    <?php if ($flash):      ?><div class="flash ok">✓ <?= $flash ?></div><?php endif; ?>
    <?php if ($flashError): ?><div class="flash err">⚠ <?= $flashError ?></div><?php endif; ?>

    <div class="page-header">
        <h2>Parqueadero Virtual — Módulos</h2>
        <?php if ($esAdmin): ?>
            <button class="btn btn-primary" onclick="openModal('modal-crear')">+ Nuevo módulo</button>
        <?php endif; ?>
    </div>

    <!-- Estadísticas -->
    <div class="stats-row">
        <div class="stat-card blue">
            <div class="n"><?= $total ?></div>
            <div class="l">Total módulos</div>
        </div>
        <div class="stat-card verde">
            <div class="n"><?= $conteo['DISPONIBLE'] ?></div>
            <div class="l">Disponibles</div>
        </div>
        <div class="stat-card rojo">
            <div class="n"><?= $conteo['OCUPADO'] ?></div>
            <div class="l">Ocupados</div>
        </div>
        <div class="stat-card ambar">
            <div class="n"><?= $conteo['MANTENIMIENTO'] ?></div>
            <div class="l">Mantenimiento</div>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="legend">
        <span><span class="dot verde"></span>Disponible</span>
        <span><span class="dot rojo"></span>Ocupado (asignado por el sistema)</span>
        <span><span class="dot ambar"></span>Mantenimiento</span>
    </div>

    <!-- ── GRID VISUAL ─────────────────────────────────────── -->
    <?php if (empty($modulos)): ?>
        <div class="empty-msg">
            No hay módulos registrados aún.
            <?php if ($esAdmin): ?>
                Usa el botón <strong>"+ Nuevo módulo"</strong> para crear el primero.
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="parking-grid">
        <?php foreach ($modulos as $m):
            $cls  = strtolower($m['estado']);
            $icon = $cls === 'disponible' ? '🟢' : ($cls === 'ocupado' ? '🔴' : '🟡');
        ?>
            <div class="slot <?= $cls ?>">
                <div class="icon"><?= $icon ?></div>
                <div class="ubicacion"><?= htmlspecialchars($m['ubicacion']) ?></div>
                <div class="mod-id">#<?= $m['id_modulo'] ?></div>
                <div class="estado-label"><?= $m['estado'] ?></div>

                <?php if ($esAdmin): ?>
                <div class="slot-actions">
                    <!-- Editar siempre disponible para admin -->
                    <button class="sbtn sbtn-edit"
                        onclick="openEditar(<?= $m['id_modulo'] ?>, '<?= htmlspecialchars($m['ubicacion']) ?>', '<?= $m['estado'] ?>')">
                        ✏️
                    </button>

                    <!-- Liberar: solo si está en mantenimiento (OCUPADO lo libera el sistema) -->
                    <?php if ($cls === 'mantenimiento'): ?>
                    <form method="POST" action="/parkingsure/controllers/ModuloController.php?accion=estado">
                        <input type="hidden" name="id_modulo" value="<?= $m['id_modulo'] ?>">
                        <input type="hidden" name="estado"    value="DISPONIBLE">
                        <button type="submit" class="sbtn sbtn-lib">Liberar</button>
                    </form>
                    <?php endif; ?>

                    <!-- Poner en mantenimiento: solo si está disponible -->
                    <?php if ($cls === 'disponible'): ?>
                    <form method="POST" action="/parkingsure/controllers/ModuloController.php?accion=estado">
                        <input type="hidden" name="id_modulo" value="<?= $m['id_modulo'] ?>">
                        <input type="hidden" name="estado"    value="MANTENIMIENTO">
                        <button type="submit" class="sbtn sbtn-mant">Mant.</button>
                    </form>
                    <?php endif; ?>

                    <!-- Eliminar: solo si está disponible -->
                    <?php if ($cls === 'disponible'): ?>
                    <form method="POST" action="/parkingsure/controllers/ModuloController.php?accion=eliminar"
                          onsubmit="return confirm('¿Eliminar módulo #<?= $m['id_modulo'] ?> — <?= htmlspecialchars($m['ubicacion']) ?>?')">
                        <input type="hidden" name="id_modulo" value="<?= $m['id_modulo'] ?>">
                        <button type="submit" class="sbtn sbtn-del">✕</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>

        <!-- ── TABLA DETALLE ────────────────────────────────── -->
        <h3>Detalle de todos los módulos</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <?php if ($esAdmin): ?><th>Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($modulos as $m):
                    $cls = strtolower($m['estado']);
                ?>
                    <tr>
                        <td>#<?= $m['id_modulo'] ?></td>
                        <td><strong><?= htmlspecialchars($m['ubicacion']) ?></strong></td>
                        <td><span class="badge <?= $cls ?>"><?= $m['estado'] ?></span></td>
                        <?php if ($esAdmin): ?>
                        <td>
                            <button class="sbtn sbtn-edit"
                                onclick="openEditar(<?= $m['id_modulo'] ?>, '<?= htmlspecialchars($m['ubicacion']) ?>', '<?= $m['estado'] ?>')">
                                ✏️ Editar
                            </button>
                            <?php if ($cls === 'disponible'): ?>
                            <form method="POST" action="/parkingsure/controllers/ModuloController.php?accion=eliminar"
                                  style="display:inline"
                                  onsubmit="return confirm('¿Eliminar módulo #<?= $m['id_modulo'] ?>?')">
                                <input type="hidden" name="id_modulo" value="<?= $m['id_modulo'] ?>">
                                <button type="submit" class="sbtn sbtn-del">✕ Eliminar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<!-- ═══════════════════════════════════
     MODAL: Crear módulo
═══════════════════════════════════ -->
<?php if ($esAdmin): ?>
<div class="overlay" id="modal-crear">
    <div class="modal">
        <h3>+ Nuevo módulo</h3>
        <form method="POST" action="/parkingsure/controllers/moduloController.php?accion=crear">
            <div class="form-group">
                <label>Ubicación / Nombre del módulo *</label>
                <input type="text" name="ubicacion" required
                       placeholder="Ej: A-01, Nivel 2 - B3, Zona Norte 5">
            </div>
            <div class="form-group">
                <label>Estado inicial</label>
                <select name="estado">
                    <option value="DISPONIBLE">Disponible</option>
                    <option value="MANTENIMIENTO">En Mantenimiento</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-save-green">✓ Crear módulo</button>
                <button type="button" class="btn-cancel btn" onclick="closeModal('modal-crear')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════
     MODAL: Editar módulo
═══════════════════════════════════ -->
<div class="overlay" id="modal-editar">
    <div class="modal">
        <h3>✏️ Editar módulo</h3>
        <form method="POST" action="/parkingsure/controllers/ModuloController.php?accion=editar">
            <input type="hidden" name="id_modulo" id="edit-id">
            <div class="form-group">
                <label>ID del módulo</label>
                <input type="text" id="edit-id-display" disabled
                       style="background:#0f172a; border:1px solid #334155; border-radius:8px; padding:.6rem .9rem; color:#64748b; width:100%;">
            </div>
            <div class="form-group">
                <label>Ubicación / Nombre *</label>
                <input type="text" name="ubicacion" id="edit-ubicacion" required
                       placeholder="Ej: A-01">
            </div>
            <div class="form-group">
                <label>Estado</label>
                <select name="estado" id="edit-estado">
                    <option value="DISPONIBLE">Disponible</option>
                    <option value="OCUPADO">Ocupado</option>
                    <option value="MANTENIMIENTO">En Mantenimiento</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-save-blue">💾 Guardar cambios</button>
                <button type="button" class="btn-cancel btn" onclick="closeModal('modal-editar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php endif; ?>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

function openEditar(id, ubicacion, estado) {
    document.getElementById('edit-id').value        = id;
    document.getElementById('edit-id-display').value = '#' + id;
    document.getElementById('edit-ubicacion').value  = ubicacion;
    document.getElementById('edit-estado').value     = estado;
    openModal('modal-editar');
}
</script>

</body>
</html>