<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

if (!isset($facturas)) {
    require_once __DIR__ . '/../../models/FacturaModel.php';
    $model    = new FacturaModel();
    $facturas = $model->getAll();
}

$flash      = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Totales
$totalPagado   = 0;
$totalPendiente = 0;
foreach ($facturas as $f) {
    if ($f['estado_pago'] === 'PAGADA')    $totalPagado    += $f['monto_total'];
    if ($f['estado_pago'] === 'PENDIENTE') $totalPendiente += $f['monto_total'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PARKINGSURE — Facturas</title>
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
        h2 { font-size: 1.2rem; margin-bottom: 1.5rem; }
        .stats { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .stat { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 1rem 1.5rem; min-width: 160px; }
        .stat .n { font-size: 1.6rem; font-weight: 700; }
        .stat .l { font-size: .75rem; color: #64748b; text-transform: uppercase; margin-top: .2rem; }
        .stat.verde  .n { color: #4ade80; }
        .stat.rojo   .n { color: #f87171; }
        .stat.blue   .n { color: #38bdf8; }
        /* Filtros */
        .filters { display: flex; gap: .75rem; margin-bottom: 1.5rem; align-items: center; flex-wrap: wrap; }
        .filters select, .filters input {
            padding: .5rem .9rem; background: #1e293b; border: 1px solid #334155;
            border-radius: 8px; color: #e2e8f0; font-size: .88rem;
        }
        .filters label { font-size: .82rem; color: #94a3b8; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; border-radius: 10px; overflow: hidden; }
        th { background: #0f172a; padding: .75rem 1rem; text-align: left; font-size: .78rem; color: #64748b; text-transform: uppercase; }
        td { padding: .75rem 1rem; border-bottom: 1px solid #0f172a; font-size: .88rem; vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #162032; }
        .badge { display: inline-block; padding: .2rem .65rem; border-radius: 20px; font-size: .74rem; font-weight: 700; }
        .badge.pagada    { background: #052e16; color: #4ade80; }
        .badge.pendiente { background: #450a0a; color: #f87171; }
        .monto { font-weight: 700; color: #e2e8f0; }
        .btn-pagar {
            padding: .3rem .8rem; background: #16a34a; color: #fff;
            border: none; border-radius: 6px; font-size: .8rem;
            font-weight: 600; cursor: pointer;
        }
        .btn-pagar:hover { background: #15803d; }
        .flash-success { background: #052e16; border: 1px solid #166534; color: #86efac; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .flash-error   { background: #450a0a; border: 1px solid #7f1d1d; color: #fca5a5; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .empty { color: #475569; text-align: center; padding: 2.5rem; background: #1e293b; border-radius: 10px; }
        /* Modal pago */
        .modal-bg { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.75); z-index: 100; align-items: center; justify-content: center; }
        .modal-bg.open { display: flex; }
        .modal { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 2rem; width: 100%; max-width: 360px; }
        .modal h3 { color: #38bdf8; margin-bottom: 1.2rem; }
        .info-row { display: flex; justify-content: space-between; padding: .5rem 0; border-bottom: 1px solid #334155; font-size: .9rem; }
        .info-row .key { color: #94a3b8; }
        .form-group { margin: 1.1rem 0 0; }
        .form-group label { display: block; font-size: .82rem; color: #94a3b8; margin-bottom: .35rem; }
        .form-group select { width: 100%; padding: .6rem .9rem; background: #0f172a; border: 1px solid #334155; border-radius: 7px; color: #e2e8f0; font-size: .9rem; }
        .modal-actions { display: flex; gap: .75rem; margin-top: 1.3rem; }
        .btn-full { flex: 1; padding: .65rem; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: .92rem; }
        .btn-confirm { background: #16a34a; color: #fff; }
        .btn-confirm:hover { background: #15803d; }
        .btn-cancel-m { background: #334155; color: #94a3b8; }
    </style>
</head>
<body>
<header>
    <h1>🅿 PARKINGSURE</h1>
    <nav>
        <a href="/parkingsure/views/dashboard/index.php">Dashboard</a>
        <a href="/parkingsure/controllers/ServicioController.php">Servicios</a>
        <a href="/parkingsure/views/servicios/entrada.php">Nueva Entrada</a>
        <a href="/parkingsure/includes/logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>
    <?php if ($flash):      ?><div class="flash-success">✓ <?= htmlspecialchars($flash) ?></div><?php endif; ?>
    <?php if ($flashError): ?><div class="flash-error">⚠ <?= htmlspecialchars($flashError) ?></div><?php endif; ?>

    <h2>Historial de Facturas</h2>

    <!-- Resumen -->
    <div class="stats">
        <div class="stat verde">
            <div class="n">$<?= number_format($totalPagado, 2) ?></div>
            <div class="l">Total cobrado</div>
        </div>
        <div class="stat rojo">
            <div class="n">$<?= number_format($totalPendiente, 2) ?></div>
            <div class="l">Por cobrar</div>
        </div>
        <div class="stat blue">
            <div class="n"><?= count($facturas) ?></div>
            <div class="l">Total facturas</div>
        </div>
    </div>

    <!-- Filtro rápido cliente -->
    <div class="filters">
        <label>Filtrar por placa:</label>
        <input type="text" id="filtro-placa" placeholder="Ej: ABC123"
               oninput="filtrarTabla()" style="text-transform:uppercase; width:130px;">
        <label>Estado:</label>
        <select id="filtro-estado" onchange="filtrarTabla()">
            <option value="">Todos</option>
            <option value="PAGADA">Pagadas</option>
            <option value="PENDIENTE">Pendientes</option>
        </select>
    </div>

    <?php if (empty($facturas)): ?>
        <div class="empty">No hay facturas registradas aún.</div>
    <?php else: ?>
        <table id="tabla-facturas">
            <thead>
                <tr>
                    <th>#Fact</th><th>Placa</th><th>Entrada</th><th>Salida</th>
                    <th>Monto Total</th><th>Método Pago</th><th>Emisión</th>
                    <th>Operador</th><th>Estado</th><th>Acción</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($facturas as $f): ?>
                <tr data-placa="<?= strtoupper($f['placa']) ?>"
                    data-estado="<?= $f['estado_pago'] ?>">
                    <td>#<?= $f['id_factura'] ?></td>
                    <td><strong><?= htmlspecialchars($f['placa']) ?></strong></td>
                    <td><?= htmlspecialchars($f['fecha_entrada']) ?></td>
                    <td><?= $f['fecha_salida'] ? htmlspecialchars($f['fecha_salida']) : '—' ?></td>
                    <td class="monto">$<?= number_format($f['monto_total'], 2) ?></td>
                    <td><?= htmlspecialchars($f['metodo_pago'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($f['fecha_emision']) ?></td>
                    <td><?= htmlspecialchars($f['operador']) ?></td>
                    <td>
                        <span class="badge <?= strtolower($f['estado_pago']) ?>">
                            <?= $f['estado_pago'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($f['estado_pago'] === 'PENDIENTE'): ?>
                            <button class="btn-pagar"
                                onclick="abrirPago(
                                    <?= $f['id_factura'] ?>,
                                    '<?= htmlspecialchars($f['placa']) ?>',
                                    '<?= number_format($f['monto_total'], 2) ?>'
                                )">
                                Cobrar
                            </button>
                        <?php else: ?>
                            <span style="color:#4ade80; font-size:.82rem;">✓ Cobrado</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>

<!-- Modal: Cobrar factura -->
<div class="modal-bg" id="modal-pago">
    <div class="modal">
        <h3>💵 Registrar Pago</h3>
        <div class="info-row"><span class="key">Factura</span><strong id="mp-id">—</strong></div>
        <div class="info-row"><span class="key">Placa</span><span id="mp-placa">—</span></div>
        <div class="info-row"><span class="key">Monto</span><strong id="mp-monto" style="color:#4ade80">—</strong></div>
        <form method="POST" action="/parkingsure/controllers/FacturaController.php?accion=pagar">
            <input type="hidden" name="id_factura" id="mp-hidden-id">
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
                <button type="submit" class="btn-full btn-confirm">Confirmar Pago</button>
                <button type="button" class="btn-full btn-cancel-m"
                    onclick="document.getElementById('modal-pago').classList.remove('open')">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirPago(id, placa, monto) {
    document.getElementById('mp-id').textContent       = '#' + id;
    document.getElementById('mp-placa').textContent    = placa;
    document.getElementById('mp-monto').textContent    = '$' + monto;
    document.getElementById('mp-hidden-id').value      = id;
    document.getElementById('modal-pago').classList.add('open');
}

function filtrarTabla() {
    const placaFiltro  = document.getElementById('filtro-placa').value.toUpperCase();
    const estadoFiltro = document.getElementById('filtro-estado').value;
    document.querySelectorAll('#tabla-facturas tbody tr').forEach(tr => {
        const placa  = tr.dataset.placa  || '';
        const estado = tr.dataset.estado || '';
        const ok = (!placaFiltro  || placa.includes(placaFiltro))
                && (!estadoFiltro || estado === estadoFiltro);
        tr.style.display = ok ? '' : 'none';
    });
}
</script>
</body>
</html>