<?php
// Acceso correcto: /parkingsure/controllers/VehiculoController.php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

// Si se llega directamente (sin controlador) cargamos los datos
if (!isset($vehiculos)) {
    require_once __DIR__ . '/../../models/VehiculoModel.php';
    require_once __DIR__ . '/../../models/ClienteModel.php';
    $vehiculoModel = new VehiculoModel();
    $clienteModel  = new ClienteModel();
    $vehiculos     = $vehiculoModel->getAll();
    $clientes      = $clienteModel->getAll();
}

$flash      = $_SESSION['flash_success'] ?? '';
$flashError = $_SESSION['flash_error']   ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARKINGSURE — Vehículos</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: #0f172a; font-family: 'Segoe UI', sans-serif; color: #e2e8f0; min-height: 100vh; }

        /* ── Header ── */
        header {
            background: #1e293b; border-bottom: 1px solid #334155;
            padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center;
            position: sticky; top: 0; z-index: 50;
        }
        header h1 { color: #38bdf8; font-size: 1.2rem; letter-spacing: 2px; }
        header nav a {
            color: #94a3b8; text-decoration: none; margin-left: 1.4rem;
            font-size: .88rem; transition: color .15s;
        }
        header nav a:hover, header nav a.active { color: #e2e8f0; }

        /* ── Layout ── */
        main { padding: 2rem; max-width: 1300px; margin: 0 auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .page-header h2 { font-size: 1.25rem; }

        /* ── Alertas flash ── */
        .flash {
            padding: .8rem 1.1rem; border-radius: 8px; margin-bottom: 1.4rem;
            font-size: .9rem; display: flex; align-items: center; gap: .5rem;
        }
        .flash.ok  { background: #052e16; border: 1px solid #166534; color: #86efac; }
        .flash.err { background: #450a0a; border: 1px solid #7f1d1d; color: #fca5a5; }

        /* ── Stat chips ── */
        .chips { display: flex; gap: .8rem; margin-bottom: 1.8rem; flex-wrap: wrap; }
        .chip {
            background: #1e293b; border: 1px solid #334155; border-radius: 10px;
            padding: .8rem 1.3rem; text-align: center; min-width: 120px;
        }
        .chip .n { font-size: 1.8rem; font-weight: 700; color: #38bdf8; }
        .chip .l { font-size: .72rem; color: #64748b; text-transform: uppercase; margin-top: .1rem; }

        /* ── Búsqueda ── */
        .search-bar {
            display: flex; gap: .7rem; margin-bottom: 1.2rem; flex-wrap: wrap; align-items: center;
        }
        .search-bar input {
            padding: .55rem .9rem; background: #1e293b; border: 1px solid #334155;
            border-radius: 8px; color: #e2e8f0; font-size: .88rem; width: 230px;
        }
        .search-bar input:focus { outline: none; border-color: #38bdf8; }

        /* ── Tabla ── */
        .table-wrap { overflow-x: auto; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; background: #1e293b; min-width: 760px; }
        thead th {
            background: #0f172a; padding: .7rem 1rem; text-align: left;
            font-size: .74rem; color: #64748b; text-transform: uppercase; letter-spacing: .5px;
        }
        tbody td { padding: .7rem 1rem; border-bottom: 1px solid #0f172a; font-size: .88rem; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #162032; }

        .placa-tag {
            background: #0f172a; border: 1px solid #334155; border-radius: 6px;
            padding: .2rem .7rem; font-weight: 700; font-size: .9rem;
            letter-spacing: 1px; color: #e2e8f0;
        }
        .empty-row td { text-align: center; color: #475569; padding: 2.5rem; }

        /* ── Botones ── */
        .btn {
            padding: .5rem 1.1rem; border-radius: 8px; border: none;
            font-size: .87rem; cursor: pointer; font-weight: 600;
            text-decoration: none; display: inline-block; transition: background .15s;
        }
        .btn-primary   { background: #0ea5e9; color: #fff; }
        .btn-primary:hover { background: #0284c7; }
        .btn-edit      { background: #1d4ed8; color: #bfdbfe; border: none; cursor: pointer;
                          padding: .28rem .75rem; border-radius: 6px; font-size: .78rem; font-weight: 600; }
        .btn-edit:hover { background: #1e40af; }
        .btn-del       { background: #7f1d1d; color: #fca5a5; border: none; cursor: pointer;
                          padding: .28rem .75rem; border-radius: 6px; font-size: .78rem; font-weight: 600; }
        .btn-del:hover  { background: #991b1b; }
        .btn-cancel    { background: #334155; color: #94a3b8; }
        .btn-cancel:hover { background: #475569; }
        .action-group  { display: flex; gap: .4rem; }

        /* ── Modales ── */
        .overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,.75);
            z-index: 200; align-items: center; justify-content: center; padding: 1rem;
        }
        .overlay.open { display: flex; }
        .modal {
            background: #1e293b; border: 1px solid #334155; border-radius: 14px;
            padding: 2rem; width: 100%; max-width: 500px; max-height: 90vh; overflow-y: auto;
        }
        .modal h3 { color: #38bdf8; margin-bottom: 1.4rem; font-size: 1.1rem; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { display: flex; flex-direction: column; gap: .35rem; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: .8rem; color: #94a3b8; }
        .form-group input,
        .form-group select {
            padding: .6rem .9rem; background: #0f172a; border: 1px solid #334155;
            border-radius: 8px; color: #e2e8f0; font-size: .9rem; transition: border .15s;
        }
        .form-group input:focus,
        .form-group select:focus { outline: none; border-color: #38bdf8; }
        .form-group input.placa-input {
            text-transform: uppercase; font-weight: 700; letter-spacing: 2px; text-align: center; font-size: 1rem;
        }
        .modal-footer { display: flex; gap: .75rem; margin-top: 1.5rem; }
        .modal-footer button { flex: 1; padding: .7rem; border-radius: 8px; font-weight: 700;
                                border: none; cursor: pointer; font-size: .92rem; }
        .btn-save   { background: #0ea5e9; color: #fff; }
        .btn-save:hover { background: #0284c7; }
        .btn-save-green { background: #16a34a; color: #fff; }
        .btn-save-green:hover { background: #15803d; }

        /* Cliente encontrado preview */
        .cliente-found {
            background: #0f172a; border: 1px solid #334155; border-radius: 8px;
            padding: .65rem .9rem; font-size: .85rem; color: #94a3b8; min-height: 42px;
            display: flex; align-items: center; gap: .5rem;
        }
        .cliente-found.ok { border-color: #166534; color: #86efac; }
        .cliente-found.err { border-color: #7f1d1d; color: #fca5a5; }

        /* Info row en modal editar */
        .info-chips { display: flex; gap: .5rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
        .info-chip {
            background: #0f172a; border: 1px solid #334155; border-radius: 6px;
            padding: .25rem .7rem; font-size: .78rem; color: #94a3b8;
        }
        .info-chip strong { color: #e2e8f0; }
    </style>
</head>
<body>

<header>
    <h1>🅿 PARKINGSURE</h1>
    <nav>
        <a href="/parkingsure/views/dashboard/index.php">Dashboard</a>
        <a href="/parkingsure/controllers/VehiculoController.php" class="active">Vehículos</a>
        <a href="/parkingsure/controllers/ServicioController.php">Servicios</a>
        <a href="/parkingsure/views/servicios/entrada.php">+ Entrada</a>
        <a href="/parkingsure/controllers/ModuloController.php">Módulos</a>
        <a href="/parkingsure/controllers/FacturaController.php">Facturas</a>
        <a href="/parkingsure/controllers/PersonalController.php">Usuarios</a>
        <a href="/parkingsure/includes/logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>

    <?php if ($flash):      ?><div class="flash ok">✓ <?= $flash ?></div><?php endif; ?>
    <?php if ($flashError): ?><div class="flash err">⚠ <?= $flashError ?></div><?php endif; ?>

    <div class="page-header">
        <h2>Vehículos registrados</h2>
        <button class="btn btn-primary" onclick="openModal('modal-crear')">+ Registrar vehículo</button>
    </div>

    <!-- Stat -->
    <div class="chips">
        <div class="chip">
            <div class="n"><?= count($vehiculos) ?></div>
            <div class="l">Total vehículos</div>
        </div>
        <div class="chip">
            <div class="n" style="color:#4ade80"><?= count($clientes) ?></div>
            <div class="l">Clientes</div>
        </div>
    </div>

    <!-- Búsqueda rápida -->
    <div class="search-bar">
        <input type="text" id="q" placeholder="🔍  Buscar por placa, cliente o marca…"
               oninput="filtrar()">
    </div>

    <!-- Tabla vehículos -->
    <div class="table-wrap">
        <table id="tabla-vehiculos">
            <thead>
                <tr>
                    <th>Placa</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año</th>
                    <th>Color</th>
                    <th>Cliente</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($vehiculos)): ?>
                <tr class="empty-row">
                    <td colspan="9">No hay vehículos registrados. Usa el botón "+ Registrar vehículo" para agregar el primero.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vehiculos as $v): ?>
                <tr data-search="<?= strtolower(
                    $v['placa'] . ' ' .
                    ($v['marca']  ?? '') . ' ' .
                    ($v['modelo'] ?? '') . ' ' .
                    $v['nombre_cliente'] . ' ' .
                    $v['cedula_cliente']
                ) ?>">
                    <td><span class="placa-tag"><?= htmlspecialchars($v['placa']) ?></span></td>
                    <td><?= htmlspecialchars($v['marca']  ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['modelo'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['anio']   ?? '—') ?></td>
                    <td><?= htmlspecialchars($v['color']  ?? '—') ?></td>
                    <td><strong><?= htmlspecialchars($v['nombre_cliente']) ?></strong></td>
                    <td><?= htmlspecialchars($v['cedula_cliente']) ?></td>
                    <td><?= htmlspecialchars($v['telefono_cliente'] ?? '—') ?></td>
                    <td>
                        <div class="action-group">
                            <button class="btn-edit" onclick="openEditar(
                                '<?= htmlspecialchars($v['placa']) ?>',
                                '<?= htmlspecialchars($v['marca']  ?? '') ?>',
                                '<?= htmlspecialchars($v['modelo'] ?? '') ?>',
                                '<?= $v['anio'] ?? '' ?>',
                                '<?= htmlspecialchars($v['color']  ?? '') ?>',
                                '<?= htmlspecialchars($v['nombre_cliente']) ?>'
                            )">Editar</button>
                            <form method="POST"
                                  action="/parkingsure/controllers/VehiculoController.php?accion=eliminar"
                                  onsubmit="return confirm('¿Eliminar el vehículo <?= htmlspecialchars($v['placa']) ?>?')">
                                <input type="hidden" name="placa" value="<?= htmlspecialchars($v['placa']) ?>">
                                <button type="submit" class="btn-del">Eliminar</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<!-- ═══════════════════════════════════════════════════════
     MODAL: Registrar nuevo vehículo
═══════════════════════════════════════════════════════ -->
<div class="overlay" id="modal-crear">
    <div class="modal">
        <h3>🚗 Registrar vehículo</h3>
        <form method="POST" action="/parkingsure/controllers/VehiculoController.php?accion=crear">
            <div class="form-grid">

                <!-- Placa -->
                <div class="form-group full">
                    <label>Placa *</label>
                    <input type="text" name="placa" class="placa-input"
                           required maxlength="8" placeholder="Ej: ABC-123"
                           oninput="this.value=this.value.toUpperCase()">
                </div>

                <!-- Buscar cliente por cédula -->
                <div class="form-group full">
                    <label>Buscar cliente por cédula *</label>
                    <input type="text" id="buscar-cedula" placeholder="Escribe la cédula del cliente…"
                           oninput="buscarCliente(this.value)">
                </div>

                <!-- Preview cliente encontrado -->
                <div class="form-group full">
                    <label>Cliente seleccionado</label>
                    <div class="cliente-found" id="cliente-preview">
                        Escribe la cédula para buscar el cliente…
                    </div>
                    <input type="hidden" name="id_cliente" id="id_cliente_hidden" value="">
                </div>

                <!-- O seleccionar de lista -->
                <div class="form-group full">
                    <label>O seleccionar de la lista</label>
                    <select onchange="seleccionarClienteLista(this)" id="select-cliente-lista">
                        <option value="">— Elegir cliente —</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?= $c['id_cliente'] ?>"
                                    data-nombre="<?= htmlspecialchars($c['nombre']) ?>"
                                    data-cedula="<?= htmlspecialchars($c['cedula']) ?>">
                                <?= htmlspecialchars($c['nombre']) ?> — <?= htmlspecialchars($c['cedula']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Marca</label>
                    <input type="text" name="marca" placeholder="Ej: Toyota">
                </div>
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="modelo" placeholder="Ej: Corolla">
                </div>
                <div class="form-group">
                    <label>Año</label>
                    <input type="number" name="anio" min="1970" max="<?= date('Y') + 1 ?>"
                           placeholder="<?= date('Y') ?>">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color" placeholder="Ej: Blanco">
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn-save-green">✓ Registrar vehículo</button>
                <button type="button" class="btn-cancel btn" onclick="closeModal('modal-crear')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════
     MODAL: Editar vehículo
═══════════════════════════════════════════════════════ -->
<div class="overlay" id="modal-editar">
    <div class="modal">
        <h3>✏️ Editar vehículo</h3>
        <div class="info-chips" id="edit-info-chips"></div>
        <form method="POST" action="/parkingsure/controllers/VehiculoController.php?accion=editar">
            <input type="hidden" name="placa" id="edit-placa">
            <div class="form-grid">
                <div class="form-group">
                    <label>Marca</label>
                    <input type="text" name="marca" id="edit-marca" placeholder="Ej: Toyota">
                </div>
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="modelo" id="edit-modelo" placeholder="Ej: Corolla">
                </div>
                <div class="form-group">
                    <label>Año</label>
                    <input type="number" name="anio" id="edit-anio"
                           min="1970" max="<?= date('Y') + 1 ?>">
                </div>
                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color" id="edit-color" placeholder="Ej: Blanco">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-save">💾 Guardar cambios</button>
                <button type="button" class="btn-cancel btn" onclick="closeModal('modal-editar')">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Datos de clientes para búsqueda rápida ──────────────────
const clientesData = <?= json_encode(array_map(fn($c) => [
    'id'     => $c['id_cliente'],
    'nombre' => $c['nombre'],
    'cedula' => $c['cedula'],
], $clientes)) ?>;

// ── Modales ─────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Cerrar al click fuera
document.querySelectorAll('.overlay').forEach(o => {
    o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

// ── Buscar cliente por cédula ────────────────────────────────
function buscarCliente(cedula) {
    const preview  = document.getElementById('cliente-preview');
    const hidden   = document.getElementById('id_cliente_hidden');
    const listaSel = document.getElementById('select-cliente-lista');
    cedula = cedula.trim();

    if (!cedula) {
        preview.className = 'cliente-found';
        preview.textContent = 'Escribe la cédula para buscar el cliente…';
        hidden.value = '';
        listaSel.value = '';
        return;
    }

    const found = clientesData.find(c => c.cedula === cedula);
    if (found) {
        preview.className = 'cliente-found ok';
        preview.textContent = '✓ ' + found.nombre + ' — CC ' + found.cedula;
        hidden.value  = found.id;
        listaSel.value = found.id;
    } else {
        preview.className = 'cliente-found err';
        preview.textContent = '✗ No se encontró ningún cliente con esa cédula';
        hidden.value  = '';
        listaSel.value = '';
    }
}

// ── Seleccionar cliente desde la lista desplegable ───────────
function seleccionarClienteLista(sel) {
    const opt    = sel.options[sel.selectedIndex];
    const preview = document.getElementById('cliente-preview');
    const hidden  = document.getElementById('id_cliente_hidden');
    const cedulaInput = document.getElementById('buscar-cedula');

    if (opt.value) {
        preview.className = 'cliente-found ok';
        preview.textContent = '✓ ' + opt.dataset.nombre + ' — CC ' + opt.dataset.cedula;
        hidden.value = opt.value;
        cedulaInput.value = opt.dataset.cedula;
    } else {
        preview.className = 'cliente-found';
        preview.textContent = 'Escribe la cédula para buscar el cliente…';
        hidden.value = '';
        cedulaInput.value = '';
    }
}

// ── Abrir modal de edición ───────────────────────────────────
function openEditar(placa, marca, modelo, anio, color, cliente) {
    document.getElementById('edit-placa').value  = placa;
    document.getElementById('edit-marca').value  = marca;
    document.getElementById('edit-modelo').value = modelo;
    document.getElementById('edit-anio').value   = anio;
    document.getElementById('edit-color').value  = color;
    document.getElementById('edit-info-chips').innerHTML =
        `<div class="info-chip">Placa: <strong>${placa}</strong></div>
         <div class="info-chip">Cliente: <strong>${cliente}</strong></div>`;
    openModal('modal-editar');
}

// ── Filtro de búsqueda en tabla ──────────────────────────────
function filtrar() {
    const q = document.getElementById('q').value.toLowerCase();
    document.querySelectorAll('#tabla-vehiculos tbody tr').forEach(tr => {
        if (tr.classList.contains('empty-row')) return;
        tr.style.display = (!q || tr.dataset.search.includes(q)) ? '' : 'none';
    });
}
</script>

</body>
</html>