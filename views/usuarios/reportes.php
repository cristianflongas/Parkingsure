<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ParkingSure — Reportes</title>
  <link rel="shortcut icon" href="../../img/logo.png">
  <link href="style/ps-core.css" rel="stylesheet">
  <style>
    /* Filters */
    .filter-row { display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; }
    .filter-row .form-group { margin:0; flex:1; min-width:140px; }

    /* Bar chart */
    .bar-row { margin-bottom:16px; }
    .bar-head { display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:600; margin-bottom:7px; }
    .bar-amount { color:var(--text-muted); font-size:12px; font-weight:500; }
    .bar-track { height:6px; background:var(--surface-3); border-radius:3px; overflow:hidden; }
    .bar-fill { height:100%; border-radius:3px; background:var(--gold); transition:width .7s cubic-bezier(.4,0,.2,1); }

    /* Hour grid */
    .hora-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:8px; }
    .hora-cell {
      text-align:center; padding:10px 4px;
      border-radius:var(--r-sm); border:1px solid var(--border);
      background:var(--surface-2); transition:border-color .18s;
    }
    .hora-cell.activa { background:rgba(16,185,129,.07); border-color:rgba(16,185,129,.2); }
    .hora-num { font-size:9px; font-weight:700; letter-spacing:.5px; color:var(--text-muted); margin-bottom:5px; text-transform:uppercase; }
    .hora-qty { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; color:var(--text-primary); }
    .hora-cell.activa .hora-qty { color:var(--emerald); }

    .two-col { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
    @media(max-width:900px){ .two-col { grid-template-columns:1fr; } .hora-grid { grid-template-columns:repeat(3,1fr); } }
  </style>
</head>
<body>
<nav class="topbar">
  <a class="logo"><div class="logo-mark">P</div><div class="logo-text">PARKING<em>SURE</em></div></a>
  <div class="nav-links">
     <a class="nb" href="dashboard.php"><span class="nav-icon"></span> Dashboard</a>
    <a class="nb" href="parqueadero.php"><span class="nav-icon"></span> Parqueadero</a>
    <a class="nb" href="vehiculos.php"><span class="nav-icon"></span> Vehículos</a>
    <a class="nb" href="pagos.php"><span class="nav-icon"></span> Pagos</a>
    <a class="nb" href="usuarios.php"><span class="nav-icon"></span> Usuarios</a>
    <a class="nb" href="servicios.php"><span class="nav-icon"></span> Servicios</a>
    <a class="nb active" href="reportes.php"><span class="nav-icon"></span> Reportes</a>
  </div>
  <div class="nav-right">
    <div class="user-avatar">C</div>
    <div class="user-info"><div class="u-name">Cristian Longas</div><div class="u-role">Administrador</div></div>
    <a class="btn-logout" href="login.php">Salir</a>
  </div>
</nav>

<div class="page">
  <div class="page-header">
    <div class="page-eyebrow">Inteligencia de negocio</div>
    <h1 class="page-title">Reportes de Ventas</h1>
    <div class="title-rule"></div>
    <p class="page-sub">Análisis de ingresos, vehículos atendidos y actividad por hora</p>
  </div>

  <!-- Filters -->
  <div class="card" style="margin-bottom:20px">
    <div class="card-title"><span class="card-title-icon">🔍</span> Filtros del Reporte</div>
    <div class="filter-row">
      <div class="form-group">
        <label class="form-label">Tipo de reporte</label>
        <select class="form-select" id="repTipo" onchange="actualizarStats()">
          <option value="diario">Diario</option>
          <option value="semanal">Semanal</option>
          <option value="mensual">Mensual</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Fecha desde</label>
        <input class="form-input" type="date" id="repFecha">
      </div>
      <div class="form-group">
        <label class="form-label">Tipo de vehículo</label>
        <select class="form-select" id="repVehiculo">
          <option value="todos">Todos</option>
          <option value="auto">🚗 Automóvil</option>
          <option value="moto">🏍️ Motocicleta</option>
          <option value="camion">🚛 Camión</option>
        </select>
      </div>
      <div style="align-self:flex-end">
        <button class="btn btn-primary" onclick="toast('✓ Reporte generado exitosamente')">
          Generar Reporte
        </button>
      </div>
      <div style="align-self:flex-end">
        <button class="btn btn-ghost" onclick="toast('📄 PDF descargado')">
          ↓ Exportar PDF
        </button>
      </div>
    </div>
  </div>

  <!-- Stats -->
  <div class="stats-grid stats-grid-4" style="margin-bottom:20px">
    <div class="stat-card">
      <div class="stat-label">Total Ingresos</div>
      <div class="stat-value gold" id="rTotal">$48.500</div>
      <div class="stat-sub" id="rPeriodo">hoy</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Vehículos Atendidos</div>
      <div class="stat-value gold" id="rVehiculos">12</div>
      <div class="stat-sub">servicios</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Tiempo Promedio</div>
      <div class="stat-value gold">2h 35m</div>
      <div class="stat-sub">por visita</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Mejor Servicio</div>
      <div class="stat-value " style="font-size:30px">🚗</div>
      <div class="stat-sub">Automóvil</div>
    </div>
  </div>

  <!-- Charts row -->
  <div class="two-col">
    <div class="card" style="margin-bottom:0">
      <div class="card-title"><span class="card-title-icon">📊</span> Ingresos por Tipo de Vehículo</div>
      <div class="bar-row">
        <div class="bar-head"><span>🚗 Automóvil</span><span class="bar-amount">$28.700</span></div>
        <div class="bar-track"><div class="bar-fill" style="width:70%"></div></div>
      </div>
      <div class="bar-row">
        <div class="bar-head"><span>🏍️ Motocicleta</span><span class="bar-amount">$9.200</span></div>
        <div class="bar-track"><div class="bar-fill" style="width:28%;background:var(--gold)"></div></div>
      </div>
      <div class="bar-row">
        <div class="bar-head"><span>🚛 Camión</span><span class="bar-amount">$10.600</span></div>
        <div class="bar-track"><div class="bar-fill" style="width:38%;background:var(--gold)"></div></div>
      </div>
      <div class="bar-row">
        <div class="bar-head"><span>🚌 Bus</span><span class="bar-amount">$0</span></div>
        <div class="bar-track"><div class="bar-fill" style="width:0%"></div></div>
      </div>
    </div>

    <div class="card" style="margin-bottom:0">
      <div class="card-title"><span class="card-title-icon">🕐</span> Vehículos por Franja Horaria</div>
      <div class="hora-grid" id="horaGrid"></div>
    </div>
  </div>

  <!-- Detail table -->
  <div class="card" style="margin-top:18px">
    <div class="card-title"><span class="card-title-icon">📋</span> Detalle del Período</div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Hora</th><th>Placa</th><th>Tipo</th><th>Módulo</th><th>Duración</th><th>Monto</th></tr></thead>
        <tbody id="repTable"></tbody>
      </table>
    </div>
  </div>
</div>

<div id="toast"></div>
<script>
  const detalles=[
    {hora:'18:33',placa:'GHI-654',tipo:'🚗',modulo:'B-03',dur:'3h 20m',monto:'$13.400'},
    {hora:'15:10',placa:'QRS-112',tipo:'🏍️',modulo:'B-01',dur:'4h 00m',monto:'$8.000'},
    {hora:'13:45',placa:'TUV-887',tipo:'🚗',modulo:'A-02',dur:'1h 50m',monto:'$5.500'},
    {hora:'11:20',placa:'DEF-340',tipo:'🚛',modulo:'D-01',dur:'2h 30m',monto:'$15.000'},
    {hora:'09:00',placa:'ABC-123',tipo:'🚗',modulo:'A-03',dur:'—',monto:'—'},
    {hora:'08:30',placa:'PLT-456',tipo:'🚗',modulo:'A-01',dur:'—',monto:'—'},
  ];
  const horas=[{h:'06–08',q:0},{h:'08–10',q:3},{h:'10–12',q:2},{h:'12–14',q:1},{h:'14–16',q:2},{h:'16–18',q:4}];

  function renderTabla(){
    const tb=document.getElementById('repTable'); tb.innerHTML='';
    detalles.forEach(d=>{
      const tr=document.createElement('tr');
      tr.innerHTML=`<td style="color:var(--text-muted)">${d.hora}</td>
        <td><strong style="font-family:monospace;font-size:13px">${d.placa}</strong></td>
        <td>${d.tipo}</td>
        <td><span style="font-family:monospace;font-size:12px;background:var(--surface-3);padding:2px 8px;border-radius:4px">${d.modulo}</span></td>
        <td style="color:var(--text-secondary)">${d.dur}</td>
        <td><strong style="color:${d.monto!=='—'?'var(--gold)':'var(--text-muted)'}">${d.monto}</strong></td>`;
      tb.appendChild(tr);
    });
  }

  function renderHoras(){
    const g=document.getElementById('horaGrid'); g.innerHTML='';
    horas.forEach(h=>{
      const d=document.createElement('div');
      d.className=`hora-cell ${h.q>0?'activa':''}`;
      d.innerHTML=`<div class="hora-num">${h.h}</div><div class="hora-qty">${h.q}</div>`;
      g.appendChild(d);
    });
  }

  function actualizarStats(){
    const tipo=document.getElementById('repTipo').value;
    const labels={diario:'hoy',semanal:'esta semana',mensual:'este mes'};
    document.getElementById('rPeriodo').textContent=labels[tipo];
    document.getElementById('rTotal').textContent=tipo==='diario'?'$48.500':tipo==='semanal'?'$312.000':'$1.245.800';
    document.getElementById('rVehiculos').textContent=tipo==='diario'?'12':tipo==='semanal'?'78':'287';
  }

  function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3200);}
  document.getElementById('repFecha').value=new Date().toISOString().split('T')[0];
  renderTabla(); renderHoras();
</script>
</body>
</html>