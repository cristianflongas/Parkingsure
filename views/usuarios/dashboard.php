<?php
// Iniciar sesión y verificar autenticación
session_start();

// Redirigir al login si no hay sesión activa
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$rolUsuario = $_SESSION['rol'] ?? 'OPERADOR';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Dashboard</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css?v=2" rel="stylesheet">
  <style>
    .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 18px; }
    .occupancy-bars { display: flex; flex-direction: column; gap: 14px; }
    .occ-item { }
    .occ-head { display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 7px; }
    .occ-qty  { color: var(--text-secondary); font-size: 12px; font-weight: 400; }
    .occ-track { height: 6px; background: var(--surface-3); border-radius: 3px; overflow: hidden; }
    .occ-fill  { height: 100%; border-radius: 3px; background: var(--gold); transition: width .6s ease; }

    /* Entry/exit event list */
    .event-dot {
      width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
    }
    .event-dot.in  { background: var(--success); }
    .event-dot.out { background: var(--text-muted); }

    /* Live indicator */
    .live-pill {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--surface-1); border: 1px solid var(--border-md);
      border-radius: 20px; padding: 4px 12px;
      font-size: 11px; font-weight: 700; letter-spacing: 1px;
      color: var(--text-primary); text-transform: uppercase;
    }
    .live-dot {
      width: 8px; height: 8px; border-radius: 50%;
      background: var(--success);
    }

    @media(max-width:900px){ .main-grid { grid-template-columns: 1fr; } }
    .stats-grid{
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }
  </style>
</head>
<body>

<!-- TOPBAR -->
<nav class="topbar">
  <a class="logo">
    <div class="logo-mark">P</div>
    <div class="logo-text">PARKING<em>SURE</em></div>
  </a>
  <button class="hamburger" id="hamburger-btn">
    <span></span>
    <span></span>
    <span></span>
  </button>
  <div class="nav-links" id="nav-links">
    <a class="nb active" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <?php if ($rolUsuario === 'ADMINISTRADOR'): ?>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <?php endif; ?>
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

  <!-- Page header -->
  <div class="page-header">
    <div class="page-eyebrow">Panel de control</div>
    <h1 class="page-title">Dashboard</h1>
    <div class="title-rule"></div>
    <p class="page-sub" id="fecha-hoy" ></p>
  </div>

  <!-- Stat cards originales -->
  <div class="stats-grid stats-grid-4" style="margin-bottom:28px">
    <div class="stat-card">
      <div class="stat-label">Módulos Totales</div>
      <div class="stat-value" id="total-modulos">-</div>
      <div class="stat-sub">configurados</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Disponibles</div>
      <div class="stat-value" id="modulos-disponibles">-</div>
      <div class="stat-sub">libres ahora</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Ocupados</div>
      <div class="stat-value" id="modulos-ocupados">-</div>
      <div class="stat-sub">en uso</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Ingresos Hoy</div>
      <div class="stat-value" id="ingresos-hoy">-</div>
      <div class="stat-sub">del día</div>
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
          <tbody id="tabla-eventos">
            <!-- Las filas se cargarán dinámicamente -->
          </tbody>
        </table>
        <div class="pagination" id="eventos-pagination">
          <!-- Los botones de paginación se cargarán dinámicamente -->
        </div>
      </div>
    </div>

    <!-- Occupancy by type -->
    <div class="card">

      <div class="card-title" style="margin-bottom:12px; border-bottom:none; padding-bottom:0;">
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
  // Formatear moneda
  function formatCurrency(amount) {
    return new Intl.NumberFormat('es-CO', {
      style: 'currency',
      currency: 'COP',
      minimumFractionDigits: 0
    }).format(amount);
  }

  // Formatear hora
  function formatTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleTimeString('es-CO', { 
      hour: '2-digit', 
      minute: '2-digit' 
    });
  }

  // Obtener ícono para tipo de vehículo
  function getVehicleIcon(tipo) {
    const icons = {
      'Automóvil': '🚗',
      'Motocicleta': '🏍️',
      'Camión': '🚛',
      'Bus': '🚌'
    };
    return icons[tipo] || '🚗';
  }

  // Cargar estadísticas
  async function loadStats() {
    try {
      console.log('🔄 Cargando estadísticas del dashboard...');
      
      // Mostrar valores por defecto mientras carga
      const totalModulosElement = document.getElementById('total-modulos');
      const disponiblesElement = document.getElementById('modulos-disponibles');
      const ocupadosElement = document.getElementById('modulos-ocupados');
      const ingresosElement = document.getElementById('ingresos-hoy');
      
      if (totalModulosElement) totalModulosElement.textContent = 'Cargando...';
      if (disponiblesElement) disponiblesElement.textContent = 'Cargando...';
      if (ocupadosElement) ocupadosElement.textContent = 'Cargando...';
      if (ingresosElement) ingresosElement.textContent = 'Cargando...';
      
      console.log('🌐 Haciendo petición a: ../../controllers/dashboardapi.php?action=getStats');
      
      const response = await fetch('../../controllers/dashboardapi.php?action=getStats');
      
      console.log('📡 Estado de respuesta:', response.status, response.statusText);
      console.log('📡 Headers:', [...response.headers.entries()]);
      
      if (!response.ok) {
        const errorText = await response.text();
        console.error('❌ Error HTTP:', response.status, errorText);
        throw new Error(`HTTP error! status: ${response.status}, text: ${errorText}`);
      }
      
      const result = await response.json();
      console.log('📊 Respuesta de estadísticas:', result); // Debug
      
      if (result.success) {
        const data = result.data;
        console.log('📋 Datos recibidos:', data); // Debug
        
        // Forzar actualización con valores por defecto si no hay datos
        const totalModulos = data.total_modulos || 0;
        const disponibles = data.disponibles || 0;
        const ocupados = data.ocupados || 0;
        const ingresos = data.ingresos_hoy || 0;
        
        console.log('🔍 Valores a mostrar:', { totalModulos, disponibles, ocupados, ingresos });
        
        if (totalModulosElement) {
          totalModulosElement.textContent = totalModulos;
          console.log('✅ Total módulos actualizado:', totalModulos);
        } else {
          console.error('❌ Elemento total-modulos no encontrado');
        }
        
        if (disponiblesElement) {
          disponiblesElement.textContent = disponibles;
          console.log('✅ Módulos disponibles actualizado:', disponibles);
        } else {
          console.error('❌ Elemento modulos-disponibles no encontrado');
        }
        
        if (ocupadosElement) {
          ocupadosElement.textContent = ocupados;
          console.log('✅ Módulos ocupados actualizado:', ocupados);
        } else {
          console.error('❌ Elemento modulos-ocupados no encontrado');
        }
        
        if (ingresosElement) {
          ingresosElement.textContent = formatCurrency(ingresos);
          console.log('✅ Ingresos hoy actualizado:', ingresos);
        } else {
          console.error('❌ Elemento ingresos-hoy no encontrado');
        }
        
      } else {
        console.error('❌ Error en respuesta de API:', result.message);
        // Mostrar valores de error con mensaje específico
        const errorMsg = result.message || 'Error desconocido';
        if (totalModulosElement) totalModulosElement.textContent = 'Error';
        if (disponiblesElement) disponiblesElement.textContent = 'Error';
        if (ocupadosElement) ocupadosElement.textContent = 'Error';
        if (ingresosElement) ingresosElement.textContent = 'Error';
        
        // Mostrar toast con el error
        toast(`Error: ${errorMsg}`);
      }
    } catch (error) {
      console.error('❌ Error al cargar estadísticas:', error);
      console.error('❌ Stack trace:', error.stack);
      
      // Mostrar valores de error con mensaje específico
      if (totalModulosElement) totalModulosElement.textContent = 'Error';
      if (disponiblesElement) disponiblesElement.textContent = 'Error';
      if (ocupadosElement) ocupadosElement.textContent = 'Error';
      if (ingresosElement) ingresosElement.textContent = 'Error';
      
      // Mostrar toast con el error
      toast(`Error de conexión: ${error.message}`);
    }
  }

  // Variables globales para paginación de eventos
  let currentEventsPage = 1;
  let totalEventsPages = 1;
  let totalEventsItems = 0;

  // Cargar eventos recientes con paginación
  async function loadRecentEvents(page = 1) {
    try {
      currentEventsPage = page;
      const response = await fetch(`../../controllers/dashboardapi.php?action=getRecentEvents&page=${page}`);
      const result = await response.json();
      
      console.log('Respuesta de eventos:', result); // Debug
      
      if (result.success) {
        const tbody = document.getElementById('tabla-eventos');
        tbody.innerHTML = '';
        
        if (result.data.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">No hay eventos registrados</td></tr>';
        } else {
          result.data.forEach(event => {
            const row = document.createElement('tr');
            const eventoIcon = event.tipo === 'entrada' ? '▲' : '▼';
            const eventoColor = event.tipo === 'entrada' ? 'var(--text-secondary)' : 'var(--text-muted)';
            const estadoBadge = event.estado === 'ACTIVO' ? 'Activo' : 'Finalizado';
            
            row.innerHTML = `
              <td><strong>${event.placa || 'N/A'}</strong></td>
              <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">${event.ubicacion || 'N/A'}</span></td>
              <td><span style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:${eventoColor};font-weight:600">${eventoIcon} ${event.evento}</span></td>
              <td style="color:var(--text-secondary)">${formatTime(event.hora)}</td>
              <td><span class="badge badge-primary badge-dot">${estadoBadge}</span></td>
            `;
            tbody.appendChild(row);
          });
        }

        // Actualizar paginación
        if (result.pagination) {
          totalEventsPages = result.pagination.totalPages;
          totalEventsItems = result.pagination.totalItems;
          console.log('Actualizando paginación:', {totalEventsPages, totalEventsItems}); // Debug
          renderPagination('eventos-pagination', page, totalEventsPages, loadRecentEvents);
        } else {
          // Si no hay paginación, mostrar mensaje
          const paginationContainer = document.getElementById('eventos-pagination');
          if (paginationContainer) {
            paginationContainer.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:16px">No hay suficientes eventos para paginar</div>';
          }
        }
      } else {
        console.error('Error en respuesta:', result.message);
        const tbody = document.getElementById('tabla-eventos');
        tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">Error: ${result.message}</td></tr>`;
      }
    } catch (error) {
      console.error('Error al cargar eventos recientes:', error);
      const tbody = document.getElementById('tabla-eventos');
      tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--text-muted);padding:28px">Error de conexión</td></tr>';
    }
  }

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
    
    // Aplicar estilos directamente
    prevBtn.style.cssText = `
      min-width: 40px !important;
      height: 40px !important;
      padding: 0 12px !important;
      background: var(--surface-2) !important;
      border: 2px solid var(--border-md) !important;
      border-radius: 10px !important;
      color: var(--text-secondary) !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
      font-family: 'Outfit', sans-serif !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      position: relative !important;
      box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
      margin: 0 !important;
    `;
    
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
      
      // Aplicar estilos directamente
      firstBtn.style.cssText = `
        min-width: 40px !important;
        height: 40px !important;
        padding: 0 12px !important;
        background: var(--surface-2) !important;
        border: 2px solid var(--border-md) !important;
        border-radius: 10px !important;
        color: var(--text-secondary) !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        font-family: 'Outfit', sans-serif !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
        margin: 0 !important;
      `;
      
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
      
      // Aplicar estilos directamente
      if (i === currentPage) {
        pageBtn.style.cssText = `
          min-width: 40px !important;
          height: 40px !important;
          padding: 0 12px !important;
          background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%) !important;
          border: 2px solid var(--gold) !important;
          border-radius: 10px !important;
          color: var(--ink-900) !important;
          font-size: 14px !important;
          font-weight: 700 !important;
          cursor: pointer !important;
          transition: all 0.2s ease !important;
          font-family: 'Outfit', sans-serif !important;
          display: inline-flex !important;
          align-items: center !important;
          justify-content: center !important;
          position: relative !important;
          box-shadow: 0 4px 12px rgba(232,184,75,0.4) !important;
          margin: 0 !important;
          transform: scale(1.1) !important;
        `;
      } else {
        pageBtn.style.cssText = `
          min-width: 40px !important;
          height: 40px !important;
          padding: 0 12px !important;
          background: var(--surface-2) !important;
          border: 2px solid var(--border-md) !important;
          border-radius: 10px !important;
          color: var(--text-secondary) !important;
          font-size: 14px !important;
          font-weight: 600 !important;
          cursor: pointer !important;
          transition: all 0.2s ease !important;
          font-family: 'Outfit', sans-serif !important;
          display: inline-flex !important;
          align-items: center !important;
          justify-content: center !important;
          position: relative !important;
          box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
          margin: 0 !important;
        `;
      }
      
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
      
      // Aplicar estilos directamente
      lastBtn.style.cssText = `
        min-width: 40px !important;
        height: 40px !important;
        padding: 0 12px !important;
        background: var(--surface-2) !important;
        border: 2px solid var(--border-md) !important;
        border-radius: 10px !important;
        color: var(--text-secondary) !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        cursor: pointer !important;
        transition: all 0.2s ease !important;
        font-family: 'Outfit', sans-serif !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
        margin: 0 !important;
      `;
      
      container.appendChild(lastBtn);
    }

    // Botón siguiente
    const nextBtn = document.createElement('button');
    nextBtn.className = 'pagination-btn';
    nextBtn.innerHTML = '→';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => loadFunction(currentPage + 1);
    
    // Aplicar estilos directamente
    nextBtn.style.cssText = `
      min-width: 40px !important;
      height: 40px !important;
      padding: 0 12px !important;
      background: var(--surface-2) !important;
      border: 2px solid var(--border-md) !important;
      border-radius: 10px !important;
      color: var(--text-secondary) !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
      font-family: 'Outfit', sans-serif !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      position: relative !important;
      box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
      margin: 0 !important;
    `;
    
    container.appendChild(nextBtn);

    // Información de total
    const info = document.createElement('span');
    info.className = 'pagination-info';
    info.textContent = containerId === 'eventos-pagination' ? 
      `${totalEventsItems} eventos` : 
      `${totalEventsItems} eventos`;
    container.appendChild(info);
  }

  // Cargar ocupación por tipo
  async function loadOccupancyByType() {
    try {
      const response = await fetch('../../controllers/dashboardapi.php?action=getOccupancyByType');
      const result = await response.json();
      
      if (result.success) {
        const container = document.getElementById('ocupacion-por-tipo');
        container.innerHTML = '';
        
        if (!result.data || result.data.length === 0) {
          container.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:20px">No hay datos de ocupación</div>';
          return;
        }
        
        result.data.forEach(item => {
          const percentage = item.total > 0 ? Math.round((item.occupied / item.total) * 100) : 0;
          
          const occItem = document.createElement('div');
          occItem.className = 'occ-item';
          occItem.innerHTML = `
            <div class="occ-head">
              <span>${item.tipo}</span>
              <span class="occ-qty">${item.occupied}/${item.total}</span>
            </div>
            <div class="occ-track">
              <div class="occ-fill" style="width: ${percentage}%"></div>
            </div>
          `;
          container.appendChild(occItem);
        });
        
        console.log('✅ Ocupación por tipo actualizada');
      } else {
        console.error('❌ Error en respuesta de ocupación:', result.message);
      }
    } catch (error) {
      console.error('❌ Error al cargar ocupación por tipo:', error);
    }
  }

  // Inicializar dashboard
  async function initDashboard() {
    await Promise.all([
      loadStats(),
      loadRecentEvents(),
      loadOccupancyByType()
    ]);
  }

  // Configurar fecha actual
  document.getElementById('fecha-hoy').textContent =
    new Date().toLocaleDateString('es-CO', { weekday:'long', year:'numeric', month:'long', day:'numeric' });

  // Función toast
  function toast(msg){ 
    const t=document.getElementById('toast'); 
    t.textContent=msg; 
    t.classList.add('show'); 
    setTimeout(()=>t.classList.remove('show'),3000); 
  }

  // Variables globales para estadísticas
  let estadisticasDiarias = {};
  let estadisticasSemanales = {};
  let fechaUltimaActualizacion = null;

  // Función para obtener fecha actual sin hora
  function getFechaActual() {
    const ahora = new Date();
    return new Date(ahora.getFullYear(), ahora.getMonth(), ahora.getDate());
  }

  // Función para obtener inicio de semana (lunes)
  function getInicioSemana(fecha) {
    const dia = fecha.getDay();
    const diasLunes = dia === 0 ? 6 : dia - 1; // Si es domingo (0), retroceder 6 días
    const inicio = new Date(fecha);
    inicio.setDate(fecha.getDate() - diasLunes);
    return inicio;
  }

  // Función para obtener fin de semana (domingo)
  function getFinSemana(fecha) {
    const dia = fecha.getDay();
    const diasDomingo = dia === 0 ? 0 : 7 - dia; // Si es domingo (0), hoy es fin
    const fin = new Date(fecha);
    fin.setDate(fecha.getDate() + diasDomingo);
    return fin;
  }

  // Función para verificar si es nuevo día
  function esNuevoDia() {
    const ahora = new Date();
    const ultimaActualizacion = fechaUltimaActualizacion ? new Date(fechaUltimaActualizacion) : null;
    
    if (!ultimaActualizacion) {
      return true; // Primera vez que se ejecuta
    }
    
    return ahora.toDateString() !== ultimaActualizacion.toDateString();
  }

  // Función para cargar estadísticas diarias (solo para reportes)
  async function cargarEstadisticasDiarias() {
    try {
      const response = await fetch('../../controllers/dashboardapi.php?action=getEstadisticasDiarias');
      const result = await response.json();
      
      if (result.success) {
        estadisticasDiarias = result.data;
        // No actualizar dashboard - solo para reportes
      }
    } catch (error) {
      console.error('Error cargando estadísticas diarias:', error);
    }
  }

  // Función para cargar estadísticas semanales (solo para reportes)
  async function cargarEstadisticasSemanales() {
    try {
      const response = await fetch('../../controllers/dashboardapi.php?action=getEstadisticasSemanales');
      const result = await response.json();
      
      if (result.success) {
        estadisticasSemanales = result.data;
        // No actualizar dashboard - solo para reportes
      }
    } catch (error) {
      console.error('Error cargando estadísticas semanales:', error);
    }
  }

  // Función para reiniciar estadísticas al nuevo día
  function reiniciarEstadisticasDiarias() {
    if (esNuevoDia()) {
      console.log('🔄 Nuevo día detectado, reiniciando estadísticas diarias...');
      
      // Limpiar estadísticas diarias
      estadisticasDiarias = {};
      
      // Forzar recarga de estadísticas del nuevo día
      cargarEstadisticasDiarias();
      
      // Actualizar fecha de última actualización
      fechaUltimaActualizacion = new Date();
      
      // Recargar dashboard para mostrar solo estadísticas del día actual
      loadStats();
      
      // Mostrar notificación de reinicio
      toast('📅 Nuevo día: Estadísticas reiniciadas');
      
      console.log('✅ Estadísticas diarias reiniciadas exitosamente');
    }
  }

  // Función principal de inicialización de estadísticas
  async function initEstadisticas() {
    await cargarEstadisticasDiarias();
    await cargarEstadisticasSemanales();
    reiniciarEstadisticasDiarias();
  }

  // Función principal de inicialización del dashboard
  async function initDashboard() {
    console.log('🚀 Inicializando dashboard...');
    
    // Cargar estadísticas principales
    await loadStats();
    
    // Cargar eventos recientes
    await loadRecentEvents();
    
    // Cargar ocupación por tipo
    await loadOccupancyByType();
    
    console.log('✅ Dashboard inicializado completamente');
  }

  // Cargar datos al iniciar
  document.addEventListener('DOMContentLoaded', function() {
    console.log('📋 DOM cargado, iniciando inicialización...');
    
    // Esperar un poco y luego cargar datos para asegurar que el DOM esté listo
    setTimeout(() => {
      console.log('⏰ Iniciando funciones del dashboard...');
      initDashboard(); // Inicializar dashboard con estadísticas
      initEstadisticas(); // Inicializar estadísticas para reportes
    }, 100);
    
    // Forzar inicialización de paginación si hay contenedor
    const paginationContainer = document.getElementById('eventos-pagination');
    if (paginationContainer) {
      // Llamar a renderPagination con valores iniciales para mostrar la paginación
      renderPagination('eventos-pagination', 1, 1, loadRecentEvents);
    }
    
    // Verificar cada minuto si es nuevo día para reiniciar estadísticas
    setInterval(reiniciarEstadisticasDiarias, 60000); // Cada minuto
    
    // Actualizar estadísticas cada 5 minutos
    setInterval(async () => {
      await cargarEstadisticasDiarias();
      await cargarEstadisticasSemanales();
    }, 300000); // Cada 5 minutos
    
    // Actualizar datos del dashboard cada 30 segundos
    setInterval(async () => {
      console.log('🔄 Actualizando dashboard automáticamente...');
      await initDashboard();
    }, 30000);
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