<?php
// Esta vista recibe $servicios y $serviciosActivos desde ServicioController.php
// Acceso directo: /parkingsure/controllers/ServicioController.php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if (!isset($serviciosActivos)) {
    require_once __DIR__ . '/../../models/ServicioModel.php';
    $model = new ServicioModel();
    $serviciosActivos = $model->getActivos();
    $servicios        = $model->getAll();
}

$flashError   = $_SESSION['flash_error']   ?? '';
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARKINGSURE — Servicios</title>
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
        main { padding: 2rem; max-width: 1200px; margin: 0 auto; }
        .top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        h2 { font-size: 1.2rem; }
        h3 { font-size: 1rem; color: #94a3b8; margin: 2rem 0 1rem; }
        .btn {
            padding: .55rem 1.2rem; border-radius: 8px; border: none;
            font-size: .9rem; cursor: pointer; font-weight: 600;
            text-decoration: none; display: inline-block;
        }
        .btn-success { background: #16a34a; color: #fff; }
        .btn-success:hover { background: #15803d; }
        .btn-danger  { background: #7f1d1d; color: #fca5a5; border: none; cursor: pointer; padding: .35rem .9rem; border-radius: 6px; font-size: .83rem; font-weight: 600; }
        .btn-danger:hover { background: #991b1b; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 10px; overflow: hidden; }
        th { background: #0f172a; padding: .75rem 1rem; text-align: left; font-size: .78rem; color: #64748b; text-transform: uppercase; }
        td { padding: .75rem 1rem; border-bottom: 1px solid #0f172a; font-size: .88rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #162032; }
        .badge { display: inline-block; padding: .2rem .65rem; border-radius: 20px; font-size: .74rem; font-weight: 700; }
        .badge.activo     { background: #052e16; color: #4ade80; }
        .badge.finalizado { background: #1e293b; color: #64748b; border: 1px solid #334155; }
        .duracion { font-size: .8rem; color: #94a3b8; }
        .flash-success { background: #052e16; border: 1px solid #166534; color: #86efac; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .flash-error   { background: #450a0a; border: 1px solid #7f1d1d; color: #fca5a5; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .empty { color: #475569; text-align: center; padding: 2rem; }

        /* Modal salida */
        .modal-bg { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.75); z-index: 100; align-items: center; justify-content: center; }
        .modal-bg.open { display: flex; }
        .modal { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 2rem; width: 100%; max-width: 400px; }
        .modal h3 { color: #38bdf8; margin-bottom: 1.3rem; }
        .info-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #334155; font-size: .9rem; }
        .info-row:last-of-type { border-bottom: none; }
        .info-row .key { color: #94a3b8; }
        .form-group { margin: 1.2rem 0 0; }
        .form-group label { display: block; font-size: .82rem; color: #94a3b8; margin-bottom: .4rem; }
        .form-group select { width: 100%; padding: .6rem .9rem; background: #0f172a; border: 1px solid #334155; border-radius: 7px; color: #e2e8f0; font-size: .9rem; }
        .modal-actions { display: flex; gap: .75rem; margin-top: 1.4rem; }
        .btn-full { flex: 1; padding: .7rem; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: .95rem; }
        .btn-confirm { background: #dc2626; color: #fff; }
        .btn-confirm:hover { background: #b91c1c; }
        .btn-cancel-m { background: #334155; color: #94a3b8; }
        .btn-cancel-m:hover { background: #475569; }
    </style>
</head>
<body>
<header>
    <h1>🅿 PARKINGSURE</h1>
    <nav>
        <a href="/parkingsure/views/dashboard/index.php">Dashboard</a>
        <a href="/parkingsure/views/servicios/entrada.php">Nueva Entrada</a>
        <a href="/parkingsure/controllers/PersonalController.php">Usuarios</a>
        <a href="/parkingsure/includes/logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>
    <?php if ($flashSuccess): ?><div class="flash-success">✓ <?= htmlspecialchars($flashSuccess) ?></div><?php endif; ?>
    <?php if ($flashError):   ?><div class="flash-error">⚠ <?= htmlspecialchars($flashError) ?></div><?php endif; ?>

    <div class="top">
        <h2>Servicios</h2>
        <a href="/parkingsure/views/servicios/entrada.php" class="btn btn-success">+ Nueva Entrada</a>
    </div>

    <!-- ── Vehículos activos (HU-07: botón registrar salida) ── -->
    <h3>🟢 Vehículos en el parqueadero ahora (<?= count($serviciosActivos) ?>)</h3>

    <?php if (empty($serviciosActivos)): ?>
        <p class="empty">No hay vehículos activos en este momento.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#Serv</th><th>Placa</th><th>Módulo</th>
                    <th>Tipo Servicio</th><th>Entrada</th>
                    <th>Tiempo</th><th>Tarifa/hr</th><th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($serviciosActivos as $s):
                $entrada  = new DateTime($s['fecha_entrada']);
                $ahora    = new DateTime();
                $diff     = $ahora->diff($entrada);
                $tiempoStr = ($diff->h > 0 ? $diff->h . 'h ' : '') . $diff->i . 'min';
            ?>
                <tr>
                    <td>#<?= $s['id_servicio'] ?></td>
                    <td><strong><?= htmlspecialchars($s['placa']) ?></strong></td>
                    <td><?= htmlspecialchars($s['modulo']) ?></td>
                    <td><?= htmlspecialchars($s['nombre_tipo_servicio']) ?></td>
                    <td><?= htmlspecialchars($s['fecha_entrada']) ?></td>
                    <td><span class="duracion">⏱ <?= $tiempoStr ?></span></td>
                    <td>$<?= number_format($s['tarifa'], 2) ?></td>
                    <td>
                        <button class="btn-danger"
                            onclick="abrirModalSalida(
                                <?= $s['id_servicio'] ?>,
                                '<?= htmlspecialchars($s['placa']) ?>',
                                '<?= htmlspecialchars($s['modulo']) ?>',
                                '<?= htmlspecialchars($s['fecha_entrada']) ?>',
                                '<?= $tiempoStr ?>'
                            )">
                            Registrar Salida
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- ── Historial general ── -->
    <h3>📋 Historial de Servicios</h3>
    <?php if (empty($servicios)): ?>
        <p class="empty">No hay servicios registrados aún.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Placa</th><th>Tipo</th><th>Módulo</th>
                    <th>Entrada</th><th>Salida</th><th>Operador</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($servicios as $s): ?>
                <tr>
                    <td>#<?= $s['id_servicio'] ?></td>
                    <td><strong><?= htmlspecialchars($s['placa']) ?></strong></td>
                    <td><?= htmlspecialchars($s['nombre_tipo_servicio']) ?></td>
                    <td><?= htmlspecialchars($s['modulo']) ?></td>
                    <td><?= htmlspecialchars($s['fecha_entrada']) ?></td>
                    <td><?= $s['fecha_salida'] ? htmlspecialchars($s['fecha_salida']) : '<span style="color:#64748b">—</span>' ?></td>
                    <td><?= htmlspecialchars($s['operador']) ?></td>
                    <td>
                        <span class="badge <?= strtolower($s['estado']) ?>">
                            <?= htmlspecialchars($s['estado']) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<!-- Modal: Registrar Salida -->
<div class="modal-bg" id="modal-salida">
    <div class="modal">
        <h3>🚗 Registrar Salida</h3>
        <div class="info-row"><span class="key">Placa</span>     <strong id="ms-placa">—</strong></div>
        <div class="info-row"><span class="key">Módulo</span>    <span id="ms-modulo">—</span></div>
        <div class="info-row"><span class="key">Entrada</span>   <span id="ms-entrada">—</span></div>
        <div class="info-row"><span class="key">Tiempo</span>    <span id="ms-tiempo">—</span></div>

        <form method="POST" action="/parkingsure/controllers/ServicioController.php?accion=salida">
            <input type="hidden" name="id_servicio" id="ms-id">
            <div class="form-group">
                <label>Método de pago</label>
                <select name="metodo_pago">
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="TARJETA">Tarjeta</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="NEQUI">Nequi</option>
                </select>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn-full btn-confirm">Confirmar Salida</button>
                <button type="button" class="btn-full btn-cancel-m"
                    onclick="document.getElementById('modal-salida').classList.remove('open')">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalSalida(id, placa, modulo, entrada, tiempo) {
    document.getElementById('ms-id').value      = id;
    document.getElementById('ms-placa').textContent  = placa;
    document.getElementById('ms-modulo').textContent = modulo;
    document.getElementById('ms-entrada').textContent = entrada;
    document.getElementById('ms-tiempo').textContent = tiempo;
    document.getElementById('modal-salida').classList.add('open');
}
</script>
</body>
</html>