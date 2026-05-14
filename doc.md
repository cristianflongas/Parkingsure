const {
  Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
  HeadingLevel, AlignmentType, BorderStyle, WidthType, ShadingType,
  PageNumber, Footer, Header, LevelFormat, TabStopType, TabStopPosition,
  PageBreak
} = require('docx');
const fs = require('fs');

// ─── COLOR PALETTE ───────────────────────────────────────────────────────────
const C = {
  gold:      'C8960C',
  goldLight: 'F5E6B0',
  darkBg:    '0D1017',
  darkBlue:  '1A2130',
  blue:      '2C4A7C',
  blueLight: 'D6E4F7',
  green:     '166534',
  greenLight:'D1FAE5',
  red:       '7F1D1D',
  redLight:  'FEE2E2',
  gray:      '374151',
  grayLight: 'F3F4F6',
  grayMid:   'D1D5DB',
  white:     'FFFFFF',
  text:      '111827',
  textMid:   '374151',
  textMuted: '6B7280',
  purple:    '4C1D95',
  purpleLight:'EDE9FE',
  orange:    '92400E',
  orangeLight:'FEF3C7',
};

// ─── HELPERS ──────────────────────────────────────────────────────────────────
const border1 = (color = C.grayMid) => ({ style: BorderStyle.SINGLE, size: 1, color });
const borders = (color = C.grayMid) => ({ top: border1(color), bottom: border1(color), left: border1(color), right: border1(color) });
const noBorder = () => ({ style: BorderStyle.NONE, size: 0, color: 'FFFFFF' });
const noBorders = () => ({ top: noBorder(), bottom: noBorder(), left: noBorder(), right: noBorder() });

function txt(text, opts = {}) {
  return new TextRun({ text, font: 'Arial', size: opts.size || 20, bold: opts.bold, color: opts.color || C.text, italics: opts.italic, break: opts.break });
}

function para(children, opts = {}) {
  if (typeof children === 'string') children = [txt(children, opts)];
  return new Paragraph({
    children,
    alignment: opts.align || AlignmentType.LEFT,
    spacing: { before: opts.before ?? 80, after: opts.after ?? 80 },
    indent: opts.indent ? { left: opts.indent } : undefined,
    border: opts.borderBottom ? { bottom: { style: BorderStyle.SINGLE, size: 6, color: C.gold, space: 4 } } : undefined,
    shading: opts.shading ? { type: ShadingType.CLEAR, fill: opts.shading } : undefined,
  });
}

function h1(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_1,
    children: [new TextRun({ text, font: 'Arial', size: 36, bold: true, color: C.darkBg })],
    spacing: { before: 400, after: 160 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 8, color: C.gold, space: 6 } },
  });
}

function h2(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_2,
    children: [new TextRun({ text, font: 'Arial', size: 28, bold: true, color: C.blue })],
    spacing: { before: 320, after: 120 },
  });
}

function h3(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_3,
    children: [new TextRun({ text, font: 'Arial', size: 24, bold: true, color: C.gray })],
    spacing: { before: 240, after: 80 },
  });
}

function h4(text) {
  return new Paragraph({
    heading: HeadingLevel.HEADING_4,
    children: [new TextRun({ text, font: 'Arial', size: 22, bold: true, color: C.textMid })],
    spacing: { before: 160, after: 60 },
  });
}

function bullet(text, level = 0, color = C.text) {
  return new Paragraph({
    numbering: { reference: 'bullets', level },
    children: [txt(text, { color })],
    spacing: { before: 40, after: 40 },
  });
}

function numbered(text, level = 0) {
  return new Paragraph({
    numbering: { reference: 'numbers', level },
    children: [txt(text)],
    spacing: { before: 40, after: 40 },
  });
}

function code(text) {
  return new Paragraph({
    children: [new TextRun({ text, font: 'Courier New', size: 18, color: C.blue })],
    spacing: { before: 40, after: 40 },
    indent: { left: 360 },
    shading: { type: ShadingType.CLEAR, fill: 'EFF6FF' },
  });
}

function space(n = 1) {
  return new Paragraph({ children: [txt('')], spacing: { before: 0, after: n * 60 } });
}

function divider() {
  return new Paragraph({
    children: [txt('')],
    spacing: { before: 100, after: 100 },
    border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: C.grayMid } },
  });
}

// ─── INFO BOX ─────────────────────────────────────────────────────────────────
function infoBox(title, lines, bgColor = C.blueLight, borderColor = C.blue) {
  const cellBorders = borders(borderColor);
  const rows = [];

  // header row
  rows.push(new TableRow({
    children: [new TableCell({
      borders: { top: border1(borderColor), bottom: border1(borderColor), left: border1(borderColor), right: border1(borderColor) },
      shading: { type: ShadingType.CLEAR, fill: borderColor },
      margins: { top: 80, bottom: 80, left: 120, right: 120 },
      width: { size: 9360, type: WidthType.DXA },
      columnSpan: 1,
      children: [para([txt(title, { bold: true, color: C.white, size: 20 })], { before: 0, after: 0 })],
    })]
  }));

  // content rows
  for (const line of lines) {
    rows.push(new TableRow({
      children: [new TableCell({
        borders: { top: noBorder(), bottom: noBorder(), left: border1(borderColor), right: border1(borderColor) },
        shading: { type: ShadingType.CLEAR, fill: bgColor },
        margins: { top: 60, bottom: 60, left: 160, right: 120 },
        width: { size: 9360, type: WidthType.DXA },
        children: [para([txt(line, { size: 19 })], { before: 0, after: 0 })],
      })]
    }));
  }

  // bottom border row
  rows.push(new TableRow({
    children: [new TableCell({
      borders: { top: border1(borderColor), bottom: border1(borderColor), left: border1(borderColor), right: border1(borderColor) },
      shading: { type: ShadingType.CLEAR, fill: bgColor },
      height: { value: 60 },
      children: [para([txt('')], { before: 0, after: 0 })],
    })]
  }));

  return new Table({
    width: { size: 9360, type: WidthType.DXA },
    columnWidths: [9360],
    rows,
    margins: { top: 80, bottom: 80 },
  });
}

// ─── 2-COL TABLE ──────────────────────────────────────────────────────────────
function twoColTable(rows2, header1 = 'Elemento', header2 = 'Descripción', col1w = 2800) {
  const col2w = 9360 - col1w;
  const hdrBorder = borders(C.blue);
  const hdrShading = { type: ShadingType.CLEAR, fill: C.blue };
  const evenShading = { type: ShadingType.CLEAR, fill: C.grayLight };

  const tableRows = [
    new TableRow({
      children: [
        new TableCell({ borders: hdrBorder, shading: hdrShading, width: { size: col1w, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [para([txt(header1, { bold: true, color: C.white, size: 19 })], { before: 0, after: 0 })] }),
        new TableCell({ borders: hdrBorder, shading: hdrShading, width: { size: col2w, type: WidthType.DXA }, margins: { top: 80, bottom: 80, left: 120, right: 120 }, children: [para([txt(header2, { bold: true, color: C.white, size: 19 })], { before: 0, after: 0 })] }),
      ]
    })
  ];

  rows2.forEach(([c1, c2], i) => {
    const sh = i % 2 === 0 ? undefined : evenShading;
    tableRows.push(new TableRow({
      children: [
        new TableCell({ borders: borders(), shading: sh, width: { size: col1w, type: WidthType.DXA }, margins: { top: 60, bottom: 60, left: 120, right: 120 }, children: [para([txt(c1, { bold: true, size: 18, color: C.blue })], { before: 0, after: 0 })] }),
        new TableCell({ borders: borders(), shading: sh, width: { size: col2w, type: WidthType.DXA }, margins: { top: 60, bottom: 60, left: 120, right: 120 }, children: [para([txt(c2, { size: 18 })], { before: 0, after: 0 })] }),
      ]
    }));
  });

  return new Table({ width: { size: 9360, type: WidthType.DXA }, columnWidths: [col1w, col2w], rows: tableRows });
}

// ─── FLOW STEP ────────────────────────────────────────────────────────────────
function flowStep(num, title, desc) {
  return new Table({
    width: { size: 9360, type: WidthType.DXA },
    columnWidths: [800, 8560],
    rows: [new TableRow({
      children: [
        new TableCell({
          borders: noBorders(),
          shading: { type: ShadingType.CLEAR, fill: C.gold },
          width: { size: 800, type: WidthType.DXA },
          margins: { top: 80, bottom: 80, left: 100, right: 100 },
          children: [para([txt(num.toString(), { bold: true, color: C.white, size: 22 })], { before: 0, after: 0, align: AlignmentType.CENTER })]
        }),
        new TableCell({
          borders: borders(C.goldLight),
          shading: { type: ShadingType.CLEAR, fill: C.goldLight },
          width: { size: 8560, type: WidthType.DXA },
          margins: { top: 80, bottom: 80, left: 160, right: 120 },
          children: [
            para([txt(title, { bold: true, size: 20, color: C.orange })], { before: 0, after: 20 }),
            para([txt(desc, { size: 18 })], { before: 0, after: 0 }),
          ]
        }),
      ]
    })],
  });
}

// ─── PAGE BREAK ───────────────────────────────────────────────────────────────
function pageBreak() {
  return new Paragraph({ children: [new TextRun({ break: 1 })] });
}

// ══════════════════════════════════════════════════════════════════════════════
//  DOCUMENT CONTENT
// ══════════════════════════════════════════════════════════════════════════════
const children = [];

// ─── COVER ────────────────────────────────────────────────────────────────────
children.push(
  space(4),
  new Paragraph({
    children: [new TextRun({ text: 'PARKINGSURE', font: 'Arial', size: 72, bold: true, color: C.darkBg })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 0, after: 80 },
  }),
  new Paragraph({
    children: [new TextRun({ text: 'DOCUMENTACIÓN TÉCNICA COMPLETA DEL SISTEMA', font: 'Arial', size: 28, bold: true, color: C.gold })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 0, after: 160 },
  }),
  new Paragraph({
    children: [new TextRun({ text: 'Sistema de Gestión Profesional de Parqueaderos', font: 'Arial', size: 26, italic: true, color: C.textMuted })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 0, after: 400 },
  }),
  divider(),
  space(2),
  new Paragraph({
    children: [new TextRun({ text: 'Versión 3.0  ·  Mayo 2026  ·  Bogotá, Colombia', font: 'Arial', size: 22, color: C.textMuted })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 0, after: 80 },
  }),
  pageBreak(),
);

// ─── ÍNDICE MANUAL ────────────────────────────────────────────────────────────
children.push(
  h1('TABLA DE CONTENIDO'),
  ...['1. Introducción y Visión General', '2. Arquitectura del Sistema (MVC)', '3. Base de Datos — Tablas y Relaciones', '4. Módulo de Autenticación (login.php)', '5. Módulo Dashboard (dashboard.php)', '6. Módulo Parqueadero Virtual (parqueadero.php)', '7. Módulo Vehículos (vehiculos.php)', '8. Módulo Pagos y Facturación (pagos.php)', '9. Módulo Reportes (reportes.php)', '10. Módulo Usuarios (usuarios.php)', '11. Módulo Servicios (servicios.php)', '12. Controladores API — Referencia Completa', '13. Flujos de Negocio End-to-End', '14. Variables Globales y Estado de la Aplicación', '15. Seguridad y Consideraciones Técnicas'].map(t => bullet(t)),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  1. INTRODUCCIÓN
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('1. INTRODUCCIÓN Y VISIÓN GENERAL'),
  para('ParkingSure es un sistema web completo para la gestión de parqueaderos, construido con arquitectura MVC (Modelo-Vista-Controlador) usando PHP en el backend y JavaScript vanilla en el frontend. Permite registrar entradas y salidas de vehículos, calcular tarifas automáticamente, emitir facturas, procesar pagos y generar reportes financieros.', { size: 20 }),
  space(),

  h2('1.1 Stack Tecnológico'),
  twoColTable([
    ['Backend',       'PHP 7.4+ con extensión PDO para acceso a base de datos'],
    ['Base de Datos', 'MySQL 8.0 — motor InnoDB — charset utf8mb4 — puerto 3307'],
    ['Frontend',      'HTML5 + CSS3 + JavaScript vanilla (ES2020 async/await)'],
    ['Tipografías',   'Google Fonts: Syne (display/titulares) + Outfit (cuerpo de texto)'],
    ['PDF (reportes)','jsPDF 2.5.1 + html2canvas 1.4.1 cargados desde cdnjs'],
    ['Zona horaria',  'America/Bogota (UTC-5) — configurado en PHP y MySQL (SET time_zone)'],
    ['Servidor local','Apache con MySQL en puerto 3307 (no el estándar 3306)'],
  ], 'Capa', 'Detalle'),
  space(),

  h2('1.2 Estructura de Directorios'),
  code('PARKINGSURE/'),
  code('├── config/'),
  code('│   └── database.php          ← Clase Database: conexión PDO'),
  code('├── controllers/              ← APIs REST en PHP (JSON)'),
  code('│   ├── dashboardapi.php'),
  code('│   ├── entradaapi.php'),
  code('│   ├── facturaapi.php'),
  code('│   ├── loginController.php'),
  code('│   ├── logout.php'),
  code('│   ├── moduloapi.php'),
  code('│   ├── reportesapi.php'),
  code('│   ├── salidaapi.php'),
  code('│   ├── tiposervicioapi.php'),
  code('│   └── vehiculosapi.php'),
  code('├── models/                   ← Clases de abstracción de datos'),
  code('│   ├── clientemodel.php'),
  code('│   ├── conexionbd.php'),
  code('│   ├── ParkingModel.php'),
  code('│   └── vehiculomodel.php'),
  code('├── views/usuarios/           ← Vistas PHP+HTML+JS'),
  code('│   ├── dashboard.php'),
  code('│   ├── login.php'),
  code('│   ├── pagos.php'),
  code('│   ├── parqueadero.php'),
  code('│   ├── reportes.php'),
  code('│   ├── servicios.php'),
  code('│   ├── usuarios.php'),
  code('│   ├── vehiculos.php'),
  code('│   ├── js/pagos-controller.js'),
  code('│   └── style/ps-core.css    ← Sistema de diseño completo'),
  code('├── sql/PARKINGSURE.sql       ← Script completo de BD'),
  code('└── index.php                 ← Landing page pública'),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  2. ARQUITECTURA MVC
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('2. ARQUITECTURA DEL SISTEMA (MVC)'),

  h2('2.1 Capa Vista (View)'),
  para('Las vistas están en views/usuarios/. Cada archivo PHP genera el HTML de la página, incluye el CSS global (ps-core.css) y contiene los bloques <script> con todo el JavaScript de esa pantalla. No contienen lógica de negocio: solo renderizado, manejo de eventos del DOM y llamadas fetch() a los controladores.', { size: 20 }),
  space(),
  infoBox('Responsabilidades de la Vista', [
    '✔  Renderizar HTML con datos de sesión PHP ($_SESSION)',
    '✔  Escuchar eventos DOM (click, input, change, DOMContentLoaded)',
    '✔  Llamar APIs mediante fetch() y manejar la respuesta JSON',
    '✔  Actualizar el DOM dinámicamente sin recargar la página',
    '✔  Validar datos básicos en el frontend antes de enviarlos',
    '✗  NO ejecuta SQL ni contiene lógica de negocio',
  ]),
  space(),

  h2('2.2 Capa Controlador (Controller)'),
  para('Los controladores en controllers/ son endpoints HTTP que reciben peticiones (GET/POST/PUT/DELETE), validan los datos, ejecutan la lógica de negocio mediante PDO y devuelven JSON. Cada controlador es un archivo PHP independiente que responde directamente a fetch().', { size: 20 }),
  space(),
  infoBox('Responsabilidades del Controlador', [
    '✔  Detectar el método HTTP ($_SERVER["REQUEST_METHOD"])',
    '✔  Leer parámetros GET/POST o el body JSON (file_get_contents("php://input"))',
    '✔  Validar y sanitizar entradas (campos obligatorios, tipos, estados válidos)',
    '✔  Abrir transacciones PDO cuando la operación afecta varias tablas',
    '✔  Ejecutar sentencias SQL preparadas (prepare + execute)',
    '✔  Devolver siempre JSON: { success: bool, message: string, data: array }',
    '✔  Hacer rollback en caso de excepción (bloque try/catch)',
    '✗  NO genera HTML ni CSS',
  ]),
  space(),

  h2('2.3 Capa Modelo (Model)'),
  para('Los modelos en models/ son clases PHP que abstraen el acceso a la base de datos. No todos los controladores los usan; muchos acceden directamente vía PDO inline. Los modelos sirven para operaciones reutilizables.', { size: 20 }),
  space(),

  h2('2.4 Patrón de Comunicación Vista → Controlador'),
  para('El flujo exacto de una operación típica (p. ej. procesar un pago) es:', { size: 20 }),
  space(1),
  flowStep(1, 'Usuario dispara evento', 'El usuario hace clic en "Confirmar Pago" en pagos.php. El listener onclick llama a la función JavaScript confirmarPago().'),
  space(1),
  flowStep(2, 'fetch() al controlador', 'confirmarPago() construye el body JSON y ejecuta fetch("../../controllers/facturaapi.php", { method: "POST", body: JSON.stringify({...}) }).'),
  space(1),
  flowStep(3, 'PHP recibe y valida', 'facturaapi.php lee $method, decodifica el body, valida que id_factura y metodo_pago existan y sean válidos.'),
  space(1),
  flowStep(4, 'SQL preparado', 'Ejecuta UPDATE factura SET estado_pago="PAGADA", metodo_pago=? WHERE id_factura=? AND estado_pago="PENDIENTE".'),
  space(1),
  flowStep(5, 'Respuesta JSON', 'PHP devuelve { success: true, message: "Pago procesado correctamente" }.'),
  space(1),
  flowStep(6, 'Vista actualiza DOM', 'JavaScript recibe el JSON, muestra el toast de éxito y recarga las listas de facturas pendientes e historial.'),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  3. BASE DE DATOS
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('3. BASE DE DATOS — TABLAS Y RELACIONES'),
  para('Motor: MySQL 8.0.30 InnoDB. Charset: utf8mb4_general_ci. Zona horaria en MySQL: SET time_zone = \'-05:00\' (Bogotá). Puerto no estándar: 3307.', { size: 20 }),
  space(),

  h2('3.1 Tabla: users'),
  para('Tabla raíz del sistema. Almacena la identidad personal de CUALQUIER persona (clientes y personal). Es la fuente de verdad para nombre, teléfono y correo.', { size: 20 }),
  twoColTable([
    ['cedula (PK VARCHAR 20)', 'Número de cédula colombiana. Clave primaria natural. Referenciada por cliente.cedula_users y personal.cedula_users.'],
    ['nombre VARCHAR(80)', 'Nombre completo de la persona.'],
    ['telefono VARCHAR(20)', 'Teléfono de contacto. Puede ser NULL.'],
    ['correo VARCHAR(80)', 'Correo electrónico. Puede ser NULL.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.2 Tabla: rol'),
  para('Catálogo de roles del sistema. Solo tiene dos filas: id=1 ADMINISTRADOR e id=2 OPERADOR.', { size: 20 }),
  twoColTable([
    ['id_rol (PK INT AI)', 'Clave primaria autoincremental.'],
    ['nombre_rol VARCHAR(50) UNIQUE', 'Nombre del rol. Valores existentes: "ADMINISTRADOR", "OPERADOR".'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.3 Tabla: personal'),
  para('Usuarios del sistema operativo (quienes inician sesión). Cada fila corresponde a un empleado del parqueadero.', { size: 20 }),
  twoColTable([
    ['id_personal (PK INT AI)', 'Clave primaria. Guardado en $_SESSION["user"] y $_SESSION["id_usuario"] al hacer login.'],
    ['cedula_users VARCHAR(20) FK UNIQUE', 'Referencia a users.cedula. Un empleado tiene exactamente una identidad en users.'],
    ['id_rol INT FK', 'Referencia a rol.id_rol. Define si es ADMINISTRADOR u OPERADOR.'],
    ['usuario VARCHAR(50) UNIQUE', 'Nombre de usuario para login. Campo usado en la consulta SQL del loginController.'],
    ['password_hash VARCHAR(255)', 'Hash bcrypt generado con password_hash(). Verificado con password_verify().'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.4 Tabla: cliente'),
  para('Personas que usan el parqueadero (propietarios de vehículos). Su información personal está en users. La tabla cliente solo actúa como pivote.', { size: 20 }),
  twoColTable([
    ['id_cliente (PK INT AI)', 'Clave primaria. Referenciada por vehiculo.id_cliente.'],
    ['cedula_users VARCHAR(20) FK UNIQUE', 'Referencia a users.cedula. Un cliente = una persona en users.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.5 Tabla: tipo_servicio'),
  para('Catálogo de tarifas. Cada tipo representa una categoría de vehículo con su precio por hora. Usado al registrar una entrada y al calcular la factura.', { size: 20 }),
  twoColTable([
    ['id_tipo_servicio (PK INT AI)', 'Clave primaria. Referenciada por entrada.id_tipo_servicio.'],
    ['nombre_tipo_servicio VARCHAR(100)', 'Nombre descriptivo: "Automóvil", "Motocicleta", "Camión", "Bus", "Van".'],
    ['tarifa DECIMAL(10,2)', 'Precio en pesos colombianos por hora. Ejemplo: 3000.00 para Automóvil.'],
    ['estado VARCHAR(20)', 'CHECK: "ACTIVO" o "INACTIVO". Solo los ACTIVOS aparecen en el selector al registrar entradas.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.6 Tabla: modulo'),
  para('Espacios físicos del parqueadero. Cada fila es un lugar donde puede estacionarse un vehículo. Estado sincronizado automáticamente con la tabla entrada.', { size: 20 }),
  twoColTable([
    ['id_modulo (PK INT AI)', 'Clave primaria. Referenciada por entrada.id_modulo.'],
    ['ubicacion VARCHAR(50)', 'Etiqueta del espacio: "M01", "M02", ... "M07". Editable por el administrador.'],
    ['estado VARCHAR(20)', 'CHECK: "DISPONIBLE", "OCUPADO" o "MANTENIMIENTO". Actualizado automáticamente al registrar entrada/salida.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.7 Tabla: vehiculo'),
  para('Catálogo de vehículos registrados. La placa es la clave primaria. Un vehículo pertenece a exactamente un cliente.', { size: 20 }),
  twoColTable([
    ['placa (PK VARCHAR 8)', 'Clave primaria. Siempre guardada en mayúsculas. Ej: "ABC123".'],
    ['id_cliente INT FK', 'Referencia a cliente.id_cliente. No puede ser NULL (integridad referencial).'],
    ['marca VARCHAR(30)', 'Marca del fabricante: "Toyota", "Renault", "Honda", etc.'],
    ['modelo VARCHAR(30)', 'Modelo específico: "Corolla", "Sandero", "CB190R", etc.'],
    ['anio INT', 'Año de fabricación. Puede ser NULL.'],
    ['color VARCHAR(20)', 'Color del vehículo: "Blanco", "Negro", etc.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.8 Tabla: entrada'),
  para('Registro de cada vez que un vehículo ingresa al parqueadero. Es la tabla central del sistema operativo. Una entrada ACTIVA significa el vehículo sigue adentro.', { size: 20 }),
  twoColTable([
    ['id_entrada (PK INT AI)', 'Clave primaria. Referenciada por salida.id_entrada.'],
    ['placa VARCHAR(8) FK', 'Referencia a vehiculo.placa. ON UPDATE CASCADE: si cambia la placa, se actualiza aquí.'],
    ['id_modulo INT FK', 'Referencia a modulo.id_modulo. Indica en qué espacio está el vehículo.'],
    ['id_personal INT FK', 'Referencia a personal.id_personal. Quién registró la entrada (tomado de $_SESSION).'],
    ['id_tipo_servicio INT FK', 'Referencia a tipo_servicio.id_tipo_servicio. Define la tarifa aplicable.'],
    ['fecha_hora_entrada DATETIME', 'Timestamp de entrada. Generado con NOW() en el servidor PHP (nunca en el cliente).'],
    ['estado VARCHAR(20)', 'CHECK: "ACTIVO" (vehículo dentro) o "FINALIZADO" (ya salió). Cambia cuando se registra la salida.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.9 Tabla: salida'),
  para('Registra el momento exacto en que un vehículo abandona el parqueadero. Relación 1:1 con entrada (UNIQUE KEY en id_entrada).', { size: 20 }),
  twoColTable([
    ['id_salida (PK INT AI)', 'Clave primaria. Referenciada por factura.id_salida.'],
    ['id_entrada INT FK UNIQUE', 'Referencia a entrada.id_entrada. UNIQUE garantiza que no haya dos salidas para la misma entrada.'],
    ['fecha_hora_salida DATETIME DEFAULT NOW()', 'Timestamp de salida. Calculado en el servidor en el momento de la llamada.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.10 Tabla: factura'),
  para('Documento financiero generado automáticamente al registrar una salida. Estado inicial siempre PENDIENTE. Cambia a PAGADA cuando el operador procesa el cobro.', { size: 20 }),
  twoColTable([
    ['id_factura (PK INT AI)', 'Clave primaria. Mostrado al usuario como "Factura #N".'],
    ['id_salida INT FK UNIQUE', 'Referencia a salida.id_salida. UNIQUE: una factura por salida. NULL mientras el módulo sigue ocupado (no aplica aquí).'],
    ['fecha_emision DATETIME DEFAULT NOW()', 'Cuando se creó la factura (al registrar la salida).'],
    ['monto_total DECIMAL(10,2)', 'Calculado: ceil(horas_totales) × tarifa_por_hora. Mínimo 1 hora.'],
    ['metodo_pago VARCHAR(20)', 'NULL cuando está PENDIENTE. "EFECTIVO" o "TRANSFERENCIA" (únicos métodos) al pagarse.'],
    ['estado_pago VARCHAR(20)', '"PENDIENTE" al crearse. "PAGADA" tras procesarPago en facturaapi.php.'],
  ], 'Columna', 'Descripción detallada'),
  space(),

  h2('3.11 Diagrama de Relaciones'),
  infoBox('Cadena principal del flujo operativo', [
    'users ←─── personal (cedula_users) ─────→ [hace login, registra entradas]',
    'users ←─── cliente (cedula_users) ──────→ [dueño del vehículo]',
    'cliente ←── vehiculo (id_cliente) ──────→ [placa del auto]',
    'vehiculo ←─ entrada (placa) ────────────→ [ingreso al parqueadero]',
    'modulo ←──  entrada (id_modulo) ────────→ [espacio asignado]',
    'tipo_servicio ← entrada (id_tipo_servicio) → [tarifa aplicada]',
    'entrada ←── salida (id_entrada) ────────→ [momento de salida]',
    'salida ←─── factura (id_salida) ────────→ [cobro generado]',
  ], C.greenLight, C.green),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  4. AUTENTICACIÓN
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('4. MÓDULO DE AUTENTICACIÓN'),

  h2('4.1 Archivo: config/database.php — Clase Database'),
  para('Esta clase es el punto de entrada a la base de datos. Todos los controladores la instancian al inicio. Es el primer archivo que se carga en cualquier petición de la API.', { size: 20 }),
  space(),
  h3('Variables privadas (propiedades de conexión)'),
  twoColTable([
    ['$host = "127.0.0.1"', 'IP del servidor MySQL. Usar IP literal en lugar de "localhost" fuerza TCP/IP en vez de socket Unix.'],
    ['$port = "3307"', 'Puerto no estándar. El MySQL local corre en 3307 en lugar del 3306 por defecto.'],
    ['$db_name = "parkingsure"', 'Nombre de la base de datos a usar.'],
    ['$username = "root"', 'Usuario MySQL con privilegios completos sobre parkingsure.'],
    ['$password = ""', 'Contraseña vacía (entorno de desarrollo local).'],
    ['$conn = null', 'Propiedad pública. Almacena el objeto PDO activo después de llamar a conectar().'],
  ], 'Propiedad', 'Descripción'),
  space(),
  h3('Método: conectar()'),
  para('Crea el DSN (Data Source Name) con la cadena "mysql:host=127.0.0.1;port=3307;dbname=parkingsure", instancia new PDO($dsn, $username, $password), activa ERRMODE_EXCEPTION (las fallas SQL lanzan PDOException en lugar de retornar false silenciosamente) y ejecuta SET time_zone = \'-05:00\' para sincronizar la hora de MySQL con la zona horaria Colombia.', { size: 20 }),
  space(),

  h2('4.2 Archivo: views/usuarios/login.php'),
  para('Página pública de acceso. Renderiza el formulario de login y es la única vista sin validación de sesión. No contiene JavaScript significativo; el formulario usa action POST al controlador.', { size: 20 }),
  space(),
  h3('Variables PHP al renderizar'),
  twoColTable([
    ['$_GET["error"]', 'Si viene en la URL (ej. login.php?error=1), muestra el bloque de error "Credenciales Incorrectas". Lo establece loginController.php en caso de fallo.'],
  ], 'Variable', 'Uso'),
  space(),
  h3('Elementos del formulario HTML'),
  twoColTable([
    ['<select name="rol">', 'Desplegable con opciones ADMINISTRADOR y OPERADOR. El valor se envía al controlador pero en la versión actual NO se valida el rol contra la BD; el sistema solo verifica usuario y contraseña.'],
    ['<input name="usuario">', 'Nombre de usuario. Se compara contra personal.usuario en la BD.'],
    ['<input name="password">', 'Contraseña en texto plano. Se pasa a password_verify() contra personal.password_hash.'],
    ['<input name="action" value="login">', 'Campo oculto. Le indica al controlador qué acción ejecutar.'],
    ['action="../../controllers/loginController.php"', 'Ruta relativa al controlador de autenticación.'],
  ], 'Elemento', 'Descripción'),
  space(),

  h2('4.3 Archivo: controllers/loginController.php'),
  para('Controlador de autenticación. Es invocado solo via POST desde login.php. Valida credenciales, crea la sesión y redirige.', { size: 20 }),
  space(),
  h3('Flujo línea por línea'),
  numbered('session_start() — Inicia o reanuda la sesión PHP.'),
  numbered('require_once "../config/database.php" — Carga la clase Database.'),
  numbered('Instancia Database y llama conectar() para obtener $conn.'),
  numbered('Lee $_POST["usuario"], $_POST["password"] y $_POST["rol"].'),
  numbered('Prepara SELECT p.*, r.nombre_rol FROM personal p INNER JOIN rol r ON p.id_rol = r.id_rol WHERE p.usuario = :usuario.'),
  numbered('Ejecuta la consulta con el usuario enviado por el formulario.'),
  numbered('Llama a fetch(PDO::FETCH_ASSOC) para obtener la fila del empleado.'),
  numbered('Si existe la fila Y password_verify($password, $user["password_hash"]) retorna true: crea la sesión.'),
  numbered('Si falla: redirige a login.php (sin parámetro de error en la URL actual).'),
  space(),
  h3('Variables de sesión que se crean al autenticar exitosamente'),
  twoColTable([
    ['$_SESSION["id_usuario"]', 'Valor de personal.id_personal. Usado como identificador interno del usuario logueado.'],
    ['$_SESSION["nombre"]', 'Nombre completo leído de users (vía JOIN). Mostrado en el topbar de todas las vistas.'],
    ['$_SESSION["usuario"]', 'Nombre de usuario (login) de personal.usuario.'],
    ['$_SESSION["rol"]', 'Valor de rol.nombre_rol: "ADMINISTRADOR" u "OPERADOR". Controla qué menús y acciones son visibles.'],
    ['$_SESSION["correo"]', 'Correo del usuario (para recuperación de contraseña, pendiente de implementar).'],
    ['$_SESSION["user"]', 'Igual a id_personal. Mantenido para compatibilidad con las verificaciones if(!isset($_SESSION["user"])) en cada vista.'],
    ['$_SESSION["id_personal"]', 'Id del personal, usado en entradaapi.php para registrar qué operador realizó la entrada.'],
  ], 'Variable de Sesión', 'Descripción y uso'),
  space(),

  h2('4.4 Archivo: controllers/logout.php'),
  para('Destruye la sesión y redirige al login. Llamado desde el enlace "Salir" en el topbar de todas las vistas. Pasos: session_start() para acceder a la sesión, session_unset() para borrar todas las variables de sesión, session_destroy() para invalidar el ID de sesión, header("Location: ../views/usuarios/login.php") para redirigir.', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  5. DASHBOARD
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('5. MÓDULO DASHBOARD'),

  h2('5.1 Archivo: views/usuarios/dashboard.php'),
  para('Panel de control principal. Primera pantalla tras el login. Muestra estadísticas en tiempo real y la tabla de últimas entradas/salidas con paginación. Se actualiza automáticamente cada 30 segundos.', { size: 20 }),
  space(),
  h3('Verificación de sesión (PHP)'),
  para('Al inicio del archivo PHP: session_start(), if(!isset($_SESSION["user"])) header("Location: login.php") exit(). Cualquier visitante sin sesión activa es redirigido al login. $rolUsuario = $_SESSION["rol"] ?? "OPERADOR" determina qué elementos del menú se muestran.', { size: 20 }),
  space(),
  h3('Elementos del DOM con ID'),
  twoColTable([
    ['#total-modulos', 'Muestra el número total de módulos configurados. Actualizado por loadStats().'],
    ['#modulos-disponibles', 'Módulos con estado DISPONIBLE. Actualizado por loadStats().'],
    ['#modulos-ocupados', 'Módulos con estado OCUPADO. Actualizado por loadStats().'],
    ['#ingresos-hoy', 'Suma de monto_total de facturas del día. Formateado como COP. Actualizado por loadStats().'],
    ['#fecha-hoy', 'Fecha actual en formato "lunes, 13 de mayo de 2026". Generado con toLocaleDateString().'],
    ['#tabla-eventos', '<tbody> de la tabla de últimas entradas/salidas. Populado por loadRecentEvents().'],
    ['#eventos-pagination', 'Contenedor de botones de paginación. Populado por renderPagination().'],
    ['#toast', 'Contenedor del mensaje temporal tipo toast. Activado con la función toast(msg).'],
  ], 'ID de Elemento', 'Descripción y función que lo actualiza'),
  space(),
  h3('Variables JavaScript globales'),
  twoColTable([
    ['currentEventsPage', 'Número de página actual de la tabla de eventos. Inicia en 1. Actualizado por loadRecentEvents(page).'],
    ['totalEventsPages', 'Total de páginas calculado por el servidor. Recibido en result.pagination.totalPages.'],
    ['totalEventsItems', 'Total de registros (entradas + salidas). Recibido en result.pagination.totalItems.'],
    ['estadisticasDiarias', 'Objeto con datos del día para reportes. Poblado por cargarEstadisticasDiarias().'],
    ['estadisticasSemanales', 'Objeto con datos de la semana para reportes. Poblado por cargarEstadisticasSemanales().'],
    ['fechaUltimaActualizacion', 'Date object del último refresco. Usado por esNuevoDia() para detectar cambio de día.'],
  ], 'Variable', 'Descripción'),
  space(),
  h3('Funciones JavaScript — Descripción Detallada'),
  h4('formatCurrency(amount)'),
  para('Convierte un número a formato peso colombiano usando Intl.NumberFormat("es-CO", { style:"currency", currency:"COP", minimumFractionDigits:0 }). Ejemplo: 75000 → "$75.000". Usada en loadStats() para ingresos.', { size: 20 }),
  space(),
  h4('formatTime(dateString)'),
  para('Recibe un string datetime de MySQL ("2026-05-12 19:33:02"), lo convierte con new Date() y retorna la hora local en formato HH:MM usando toLocaleTimeString("es-CO"). Usada en la tabla de eventos.', { size: 20 }),
  space(),
  h4('loadStats() — async'),
  para('Fetch a dashboardapi.php?action=getStats. Si la respuesta es exitosa, extrae data.total_modulos, data.disponibles, data.ocupados y data.ingresos_hoy, y los escribe en los elementos #total-modulos, #modulos-disponibles, #modulos-ocupados e #ingresos-hoy. Muestra "Error" si falla y llama a toast() con el mensaje de error.', { size: 20 }),
  space(),
  h4('loadRecentEvents(page = 1) — async'),
  para('Fetch a dashboardapi.php?action=getRecentEvents&page=N. Recibe hasta 10 eventos (entradas y salidas combinados, ordenados por hora DESC). Por cada evento crea una fila <tr> en #tabla-eventos con: placa, ubicación del módulo, ícono de evento (▲ Entrada o ▼ Salida), hora formateada y badge de estado. Al terminar llama a renderPagination() con los datos de paginación.', { size: 20 }),
  space(),
  h4('renderPagination(containerId, currentPage, totalPages, loadFunction)'),
  para('Función reutilizable (usada también en vehiculos.php, usuarios.php y reportes.php). Borra el contenido de document.getElementById(containerId) y construye: botón "←" (deshabilitado en página 1), botones numéricos con ellipsis (...) para rangos largos, botón "→" (deshabilitado en última página) y un span con el total de ítems. Cada botón numérico llama a loadFunction(i) al hacer clic. El botón activo recibe estilos especiales (fondo gold, escala 1.1).', { size: 20 }),
  space(),
  h4('loadOccupancyByType() — async'),
  para('Fetch a dashboardapi.php?action=getOccupancyByType. Para cada tipo de servicio con vehículos activos, calcula el porcentaje (occupied/total × 100) y renderiza una barra de progreso en #ocupacion-por-tipo. Si no hay datos muestra "No hay datos de ocupación".', { size: 20 }),
  space(),
  h4('esNuevoDia()'),
  para('Compara la fecha actual (new Date().toDateString()) con fechaUltimaActualizacion.toDateString(). Si son distintas retorna true. Usada por reiniciarEstadisticasDiarias() que se ejecuta cada minuto con setInterval.', { size: 20 }),
  space(),
  h4('initDashboard() — async'),
  para('Función de inicialización principal. Ejecuta en paralelo loadStats(), loadRecentEvents() y loadOccupancyByType() con await Promise.all([...]). Asegura que los tres bloques se carguen simultáneamente para mayor velocidad.', { size: 20 }),
  space(),
  h3('Ciclo de actualización automática'),
  bullet('DOMContentLoaded: setTimeout(initDashboard, 100) — espera 100ms para que el DOM esté listo.'),
  bullet('setInterval(reiniciarEstadisticasDiarias, 60000) — verifica cada minuto si cambió el día.'),
  bullet('setInterval(initDashboard, 30000) — refresca todas las estadísticas cada 30 segundos.'),
  bullet('setInterval(cargarEstadisticasDiarias + cargarEstadisticasSemanales, 300000) — actualiza datos de reportes cada 5 minutos.'),
  space(),

  h2('5.2 Archivo: controllers/dashboardapi.php'),
  para('API de solo lectura (nunca modifica ni elimina datos). Responde a cuatro acciones GET.', { size: 20 }),
  space(),
  h3('Acción: getStats'),
  para('SQL 1: SELECT COUNT(*) AS total, SUM(estado="DISPONIBLE") AS disponibles, SUM(estado="OCUPADO") AS ocupados FROM modulo — cuenta módulos por estado. SQL 2: SELECT COALESCE(SUM(monto_total), 0) AS ingresos_hoy FROM factura WHERE DATE(fecha_emision) = CURDATE() — suma todos los ingresos del día (PAGADAS y PENDIENTES). Retorna { total_modulos, disponibles, ocupados, ingresos_hoy }.', { size: 20 }),
  space(),
  h3('Acción: getServicios'),
  para('SELECT * FROM tipo_servicio ORDER BY nombre_tipo_servicio. Usado por servicios.php para poblar la grilla de servicios al cargar la página.', { size: 20 }),
  space(),
  h3('Acción: getRecentEvents'),
  para('UNION ALL de dos consultas: (1) Entradas — SELECT e.placa, m.ubicacion, "Entrada" AS evento, e.fecha_hora_entrada AS hora, e.estado, "entrada" AS tipo FROM entrada e INNER JOIN modulo m ... y (2) Salidas — misma estructura pero para tabla salida. Ordenado por hora DESC. Paginado con LIMIT :lim OFFSET :off. Calcula totalPages = ceil(total / 10) con una subconsulta COUNT. Retorna data (array de eventos) y pagination (currentPage, totalPages, totalItems, itemsPerPage).', { size: 20 }),
  space(),
  h3('Acción: getOccupancyByType'),
  para('Cuenta entradas ACTIVAS agrupadas por tipo de servicio con el total de módulos como columna calculada. Retorna array de { tipo, occupied, total }.', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  6. PARQUEADERO VIRTUAL
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('6. MÓDULO PARQUEADERO VIRTUAL'),

  h2('6.1 Archivo: views/usuarios/parqueadero.php'),
  para('Vista principal de operaciones diarias. Muestra todos los módulos como celdas visuales de colores (verde=libre, rojo=ocupado, amarillo=mantenimiento). El operador asigna vehículos a módulos libres o libera módulos ocupados con un clic.', { size: 20 }),
  space(),
  h3('Variables JavaScript globales'),
  twoColTable([
    ['modules = []', 'Array de objetos módulo. Cada objeto tiene: db_id (id real en BD), id (string con padding "01","02"...), estado ("DISPONIBLE"/"OCUPADO"/"MANTENIMIENTO"), ocupaciones (int), ubicacion (string), placa (string|null). Populado por cargarModulos().'],
    ['currentModuloIndex', 'Índice en el array modules del módulo sobre el que se abrió el menú contextual (clic derecho). Usado por toggleModuloEstado(), editModuloInfo() y deleteModuloInfo().'],
    ['currentModuloAsignar', 'Objeto módulo seleccionado para la asignación de vehículo. Guardado cuando se abre el modal de asignación.'],
    ['currentModuloLiberar', 'Objeto módulo seleccionado para la liberación. Guardado cuando se abre el modal de liberación.'],
  ], 'Variable', 'Descripción'),
  space(),
  h3('Función: cargarModulos() — async'),
  para('Fetch a ../../controllers/moduloapi.php?action=getAll. El controlador hace auto-limpieza de inconsistencias antes de retornar datos. Mapea cada objeto de la respuesta a la estructura local del array modules (añade el campo id con padStart). Llama a renderLot() para dibujar la cuadrícula y a cargarStats() para las tarjetas de estadísticas.', { size: 20 }),
  space(),
  h3('Función: cargarStats() — async'),
  para('Fetch a moduloapi.php?action=getStats. Actualiza los elementos #st-total, #st-disponibles, #st-ocupados y #st-mantenimiento con los valores retornados.', { size: 20 }),
  space(),
  h3('Función: renderLot()'),
  para('Borra el contenido de #lotGrid. Por cada módulo en el array modules crea un div.module con clase CSS según estado: "libre" para DISPONIBLE, "ocupado" para OCUPADO, "mantenimiento" para MANTENIMIENTO. El ícono cambia (🅿️ / 🚗 / 🔧). Si el módulo tiene placa, muestra la placa dentro de un div.mod-plate. Asigna onclick según estado: DISPONIBLE→openAsignarModal(i), OCUPADO→openLiberarModal(i), MANTENIMIENTO→toast() informativo. Asigna oncontextmenu→showModMenu(e,i) para el menú contextual. Al final agrega la celda "+" para crear módulos nuevos.', { size: 20 }),
  space(),
  h3('Función: openAsignarModal(i)'),
  para('Guarda modules[i] en currentModuloAsignar. Rellena #moduloInfo con el id y ubicación del módulo. Limpia el input de placa y el feedback. Llama a cargarTiposServicio() para poblar el <select> de tipos. Muestra el modal #asignarModal con classList.add("show"). Añade un listener debounced (400ms de retardo) al input de placa que hace fetch a vehiculosapi.php?action=getById&placa=X para validar en tiempo real si la placa está registrada. El feedback cambia de color (verde=existe, amarillo=no registrada).', { size: 20 }),
  space(),
  h3('Función: cargarTiposServicio() — async'),
  para('Fetch a tiposervicioapi.php?action=getAll. Si hay datos, borra las opciones del <select id="tipoServicio"> y agrega una <option> por cada tipo activo. Si falla o no hay datos, agrega la opción de respaldo "Servicio Estándar" con value=1.', { size: 20 }),
  space(),
  h3('Función: confirmarAsignacion() — async'),
  para('Lee placa (en mayúsculas) e idTipoServicio del modal. Deshabilita el botón de confirmación para evitar doble envío. Hace fetch a vehiculosapi.php?action=getById&placa=X para verificar que la placa existe en la BD. Si no existe, muestra error y no continúa. Si existe, hace POST a entradaapi.php con body { action:"assign", id_modulo, placa, id_tipo_servicio }. Si success: llama toast() de éxito, closeAsignarModal() y cargarModulos() para refrescar la vista.', { size: 20 }),
  space(),
  h3('Función: openLiberarModal(i)'),
  para('Guarda modules[i] en currentModuloLiberar. Rellena los divs de información del módulo y vehículo. Llama a entradaapi.php?action=getActive para encontrar la entrada activa de ese módulo específico (filtra por id_modulo === modulo.db_id) y calcula el tiempo transcurrido: (ahora - fechaEntrada) en horas y minutos.', { size: 20 }),
  space(),
  h3('Función: confirmarLiberacion() — async'),
  para('Paso 1: GET entradaapi.php?action=getActive para obtener todas las entradas activas. Filtra por id_modulo === currentModuloLiberar.db_id para encontrar la entrada específica. Paso 2: POST salidaapi.php con body { action:"release", id_entrada }. Si success y hay liberarResult.factura, llama a mostrarNotificacionFactura() con los datos de la factura. Si no hay factura, muestra toast simple. En ambos casos cierra el modal y llama a cargarModulos().', { size: 20 }),
  space(),
  h3('Función: mostrarNotificacionFactura(f, fmt)'),
  para('Crea dinámicamente un div overlay con posición fixed sobre toda la pantalla. Muestra: placa, tipo de servicio, tiempo de estancia, horas cobradas × tarifa/hora y el total en gold. Dos botones: "Quedar aquí" (elimina el overlay) y "Ir a Pagos 💳" (llama a abrirPagosConFactura que guarda la factura en sessionStorage y redirige a pagos.php).', { size: 20 }),
  space(),
  h3('Función: abrirPagosConFactura(factura)'),
  para('sessionStorage.setItem("facturaPrecargada", JSON.stringify(factura)) guarda el objeto factura en el almacenamiento de sesión del navegador. Luego window.location.href = "pagos.php" redirige. Al cargar pagos.php, el listener DOMContentLoaded lee sessionStorage.getItem("facturaPrecargada"), lo elimina y llama a abrirCobro(factura) con un delay de 800ms.', { size: 20 }),
  space(),
  h3('Menú contextual (clic derecho sobre módulo)'),
  para('showModMenu(e, i): previene el menú nativo del navegador con e.preventDefault(). Guarda i en currentModuloIndex. Actualiza el texto del primer ítem según el estado actual ("Poner en Mantenimiento" o "Poner Disponible"). Si el módulo tiene ocupaciones y no está en mantenimiento, deshabilita esa opción. Posiciona el menú con e.pageX y e.pageY. Agrega un listener click en document para ocultar el menú al hacer clic fuera.', { size: 20 }),
  space(),
  h3('Función: toggleModuloEstado() — async'),
  para('Determina nuevoEstado: si MANTENIMIENTO→"DISPONIBLE", si no→"MANTENIMIENTO". Valida que no tenga vehículos estacionados para poner en mantenimiento. Pide confirmación con confirm(). POST a moduloapi.php con { action:"changeState", id, estado:nuevoEstado }. Si success llama a cargarModulos().', { size: 20 }),
  space(),
  h3('Función: openAdd() — async'),
  para('prompt() pide la ubicación del nuevo módulo. POST a moduloapi.php con { action:"create", ubicacion, estado:"DISPONIBLE" }. Si success llama a cargarModulos().', { size: 20 }),
  space(),
  h3('Función: debounce(fn, delay)'),
  para('Implementación estándar de debounce. Retorna una función que retrasa la ejecución de fn hasta que pasen delay milisegundos desde la última invocación. Usada en el listener del input de placa para no hacer fetch en cada tecla.', { size: 20 }),
  space(),

  h2('6.2 Archivo: controllers/moduloapi.php'),
  h3('GET action=getAll — Flujo completo de auto-limpieza'),
  para('Paso 1: UPDATE entrada e INNER JOIN salida s ON s.id_entrada = e.id_entrada SET e.estado = "FINALIZADO" WHERE e.estado = "ACTIVO" — Corrige entradas que tienen salida registrada pero siguen marcadas como ACTIVO. Paso 2: UPDATE modulo m SET m.estado = "DISPONIBLE" WHERE m.estado = "OCUPADO" AND NOT EXISTS (SELECT 1 FROM entrada e WHERE e.id_modulo = m.id_modulo AND e.estado = "ACTIVO") — Libera módulos marcados como OCUPADO sin entrada activa real. Paso 3: Consulta principal con LEFT JOIN para obtener módulos y sus entradas activas. Para cada módulo, determina estado real y si hay inconsistencia actualiza la BD silenciosamente. Retorna array formateado con id, ubicacion, estado (real), ocupaciones (int) y placa (del vehículo si está ocupado).', { size: 20 }),
  space(),
  h3('POST action=changeState'),
  para('Valida que nuevoEstado sea uno de ["DISPONIBLE","OCUPADO","MANTENIMIENTO"]. Si se intenta poner en MANTENIMIENTO, verifica que no haya entradas ACTIVAS con COUNT(*) FROM entrada WHERE id_modulo=? AND estado="ACTIVO". Ejecuta UPDATE modulo SET estado=? WHERE id_modulo=?.', { size: 20 }),
  space(),
  h3('POST action=create'),
  para('Verifica que ubicacion no esté vacía y que no exista ya en la BD (SELECT COUNT(*) FROM modulo WHERE ubicacion=?). Inserta con INSERT INTO modulo (ubicacion, estado) VALUES (?, "DISPONIBLE"). Retorna el id del nuevo módulo.', { size: 20 }),
  space(),
  h3('PUT — Actualizar módulo'),
  para('Actualiza ubicacion y estado de un módulo existente. Recibe id en el body JSON.', { size: 20 }),
  space(),
  h3('DELETE — Eliminar módulo'),
  para('Recibe id en $_GET["id"]. Verifica que no tenga entradas ACTIVAS. Ejecuta DELETE FROM modulo WHERE id_modulo=?.', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  7. VEHÍCULOS
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('7. MÓDULO VEHÍCULOS'),

  h2('7.1 Archivo: views/usuarios/vehiculos.php'),
  para('Permite registrar nuevos vehículos con su propietario (cliente existente o nuevo), buscarlos y editarlos. El grid de tarjetas se carga dinámicamente desde la API.', { size: 20 }),
  space(),
  h3('Variables JavaScript globales'),
  twoColTable([
    ['vehiculos = []', 'Array de todos los vehículos cargados desde la BD. Mapeados por cargarVehiculos(). Cada objeto tiene: placa, tipo, modelo, color, owner, tel, estado, dentro.'],
    ['currentDetIdx', 'Índice del vehículo abierto en el modal de detalle. Usado para editar, togglear estado o eliminar.'],
    ['currentVehiculosPage', 'Página actual de la paginación de vehículos.'],
    ['totalVehiculosPages', 'Total de páginas calculado del array en memoria.'],
    ['totalVehiculosItems', 'Total de vehículos en la BD.'],
  ], 'Variable', 'Descripción'),
  space(),
  h3('Función: cargarVehiculos(page) — async'),
  para('Fetch a vehiculosapi.php?action=getAll. Mapea cada vehículo recibido al formato local del array vehicles. Si page===1 reemplaza todo el array; si no, añade. Llama a renderVehiculos() y, si hay datos de paginación en la respuesta, llama a renderPagination().', { size: 20 }),
  space(),
  h3('Función: renderVehiculos()'),
  para('Lee el texto del input #buscar y el valor del select #filtroEstado. Filtra el array vehiculos. Si no hay resultados muestra el empty state. Crea tarjetas veh-card con: placa en estilo monoespaciado, icono del tipo, modelo y color, nombre del propietario, badge de estado (Activo/Inactivo) y badge "En parqueadero" si dentro===true. El onclick de cada tarjeta llama a openDet(realIdx).', { size: 20 }),
  space(),
  h3('Función: registrar() — async'),
  para('Lee todos los inputs del formulario de la izquierda. Valida que placa y modelo no estén vacíos. Valida que la placa no exista en el array local. Según el valor de #tipoRegistro: si "existente" lee #selectCliente.value como id_cliente; si "nuevo" lee los campos del cliente nuevo. Hace POST a vehiculosapi.php con action "create" o "createWithClient". Si success llama a limpiarFormulario() y cargarVehiculos().', { size: 20 }),
  space(),
  h3('Función: cargarClientes() — async'),
  para('Fetch a vehiculosapi.php?action=getClientes. Pobla el <select id="selectCliente"> con las opciones retornadas. Cada opción muestra "nombre (cédula)" y su value es id_cliente.', { size: 20 }),
  space(),
  h3('Función: toggleClienteForm()'),
  para('Lee el valor de #tipoRegistro. Si "existente" muestra #clienteExistenteForm y oculta #nuevoClienteForm. Si "nuevo" hace lo contrario.', { size: 20 }),
  space(),
  h3('Modal de detalle/edición'),
  para('openDet(i): Guarda i en currentDetIdx. Rellena todos los campos del modal con datos de vehicles[i]. Configura el texto del botón #detToggleBtn ("Desactivar" o "Activar"). switchToEdit(): oculta #detView y muestra #detEdit, poblando los inputs con los valores actuales. guardarEdicion(): lee los inputs del modo edición y actualiza vehicles[currentDetIdx] en memoria. Llama a openDet() y renderVehiculos(). (No persiste en BD en la versión actual.) toggleFromModal(): cambia estado Activo/Inactivo en el array local. eliminarFromModal(): pide confirm() y elimina del array con splice, recarga el grid.', { size: 20 }),
  space(),

  h2('7.2 Archivo: controllers/vehiculosapi.php'),
  h3('GET action=getAll'),
  para('SELECT v.placa, v.marca, v.modelo, v.anio, v.color, u.nombre as nombre_cliente, u.telefono, u.correo FROM vehiculo v LEFT JOIN cliente c ON v.id_cliente = c.id_cliente LEFT JOIN users u ON c.cedula_users = u.cedula ORDER BY v.placa. Retorna array de vehículos con datos del propietario.', { size: 20 }),
  space(),
  h3('GET action=getById'),
  para('Recibe placa en $_GET. Misma consulta que getAll pero con WHERE v.placa = ?. Retorna un solo objeto o { success: false } si no existe. Usado por parqueadero.php para validar la placa antes de asignar.', { size: 20 }),
  space(),
  h3('GET action=getClientes'),
  para('Consulta a la tabla cliente con LEFT JOIN a users para obtener nombre y cédula. Formatea cada resultado como { id_cliente, nombre, cedula, telefono, correo }. Usado por el select de clientes en vehiculos.php.', { size: 20 }),
  space(),
  h3('POST action=create'),
  para('Valida campos obligatorios (placa, id_cliente, marca). Verifica que la placa no exista con SELECT placa FROM vehiculo WHERE placa=?. Inserta con INSERT INTO vehiculo (placa, id_cliente, marca, modelo, anio, color) VALUES (?, ?, ?, ?, ?, ?).', { size: 20 }),
  space(),

  h2('7.3 Archivo: controllers/entradaapi.php — POST action=assign'),
  para('Acción central del flujo de ingreso de vehículos. Recibe id_modulo, placa e id_tipo_servicio. Realiza cuatro validaciones antes de insertar: (1) el módulo existe y está en estado DISPONIBLE, (2) el vehículo existe en la BD, (3) el vehículo no está ya estacionado en otro módulo (SELECT COUNT(*) FROM entrada WHERE placa=? AND estado="ACTIVO"), (4) el tipo de servicio existe y está ACTIVO. Si todas pasan, abre una transacción: INSERT INTO entrada ... con NOW() como fecha_hora_entrada, UPDATE modulo SET estado="OCUPADO" y commit. Si falla, rollback.', { size: 20 }),
  space(),
  h3('GET action=getActive'),
  para('SELECT e.id_entrada, e.id_modulo, e.placa, e.fecha_hora_entrada, e.estado, m.ubicacion, v.marca, v.modelo FROM entrada e LEFT JOIN modulo m ... LEFT JOIN vehiculo v ... WHERE e.estado = "ACTIVO". Retorna todas las entradas vigentes. Usado por parqueadero.php para calcular tiempo de estancia al abrir el modal de liberación.', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  8. PAGOS
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('8. MÓDULO PAGOS Y FACTURACIÓN'),

  h2('8.1 Archivo: views/usuarios/pagos.php'),
  para('Gestiona el cobro de facturas pendientes y muestra el historial de pagos del día. Se divide en dos columnas: izquierda con facturas pendientes y derecha con historial.', { size: 20 }),
  space(),
  h3('Variables JavaScript globales'),
  twoColTable([
    ['pendientes = []', 'Array de facturas con estado_pago="PENDIENTE". Cargado por cargarPendientes().'],
    ['pendientesFilt = []', 'Copia filtrada de pendientes según el texto en #buscarFactura. Usada por renderPendientes().'],
    ['historialData = []', 'Array de facturas PAGADAS del día. Cargado por cargarHistorial().'],
    ['historialFilt = []', 'Copia filtrada de historialData. Usada por renderHistorial().'],
    ['facturaActual = null', 'Objeto factura abierto en el modal de cobro. Guardado por abrirCobro().'],
    ['metodoActual = "EFECTIVO"', 'Método de pago seleccionado en el modal. Actualizado por selMetodo().'],
  ], 'Variable', 'Descripción'),
  space(),
  h3('Constante: fmt = n => ...'),
  para('Función de formateo de moneda. Usa Intl.NumberFormat("es-CO", { style:"currency", currency:"COP", minimumFractionDigits:0 }). Ejemplo: fmt(75000) → "$75.000".', { size: 20 }),
  space(),
  h3('Constante: fmtHora = s => ...'),
  para('Convierte un string datetime de MySQL a hora local en formato HH:MM. new Date(s).toLocaleTimeString("es-CO", { hour:"2-digit", minute:"2-digit" }).', { size: 20 }),
  space(),
  h3('Función: cargarPendientes() — async'),
  para('Fetch a facturaapi.php?action=getPendientes. Popula pendientes y pendientesFilt. Llama a renderPendientes(). Actualiza #stPend con pendientes.length. Si falla muestra "Error al cargar facturas" en la tabla.', { size: 20 }),
  space(),
  h3('Función: renderPendientes()'),
  para('Borra #tbPendientes. Si pendientesFilt está vacío muestra "Sin facturas pendientes". Por cada factura crea una fila con: placa en monoespaciado, ubicación del módulo, tiempo de estancia, monto total en gold y botón "Cobrar" que llama a abrirCobro(f) pasando el objeto completo de la factura.', { size: 20 }),
  space(),
  h3('Función: filtrarPendientes()'),
  para('Lee el texto de #buscarFactura en minúsculas. Filtra pendientes comparando contra placa y ubicacion. Actualiza pendientesFilt y llama a renderPendientes().', { size: 20 }),
  space(),
  h3('Función: cargarHistorial() — async'),
  para('Determina la fecha actual LOCAL (no UTC) con: const hoyLocal = new Date(); const hoy = hoyLocal.getFullYear()+"-"+String(hoyLocal.getMonth()+1).padStart(2,"0")+"-"+String(hoyLocal.getDate()).padStart(2,"0"). Fetch a facturaapi.php?action=getPagadas&fecha=YYYY-MM-DD. Popula historialData y historialFilt. Llama a renderHistorial() y actualizarStats().', { size: 20 }),
  space(),
  h3('Función: actualizarStats()'),
  para('Calcula desde historialData: total = suma de monto_total de todos los registros, tx = historialData.length (número de transacciones), promedio = tx > 0 ? Math.round(total/tx) : 0. Escribe los resultados en #stHoy, #stTx y #stProm usando fmt().', { size: 20 }),
  space(),
  h3('Función: abrirCobro(factura)'),
  para('Guarda factura en facturaActual. Resetea la selección de método a EFECTIVO. Rellena todos los campos del ticket del modal: #ticketMonto con fmt(monto_total), #ticketPlaca, #ticketModulo (ubicacion), #ticketServicio, #ticketTiempo, #ticketHoras, #ticketTarifa con fmt(tarifa)+"/h", #ticketFactura con "#"+id_factura y #ticketFecha con toLocaleString. Muestra el modal #modalCobro.', { size: 20 }),
  space(),
  h3('Función: selMetodo(el)'),
  para('Quita la clase "active" de todos los .pay-opt. Agrega "active" al elemento clickeado. Lee el atributo data-metodo del elemento y lo guarda en metodoActual.', { size: 20 }),
  space(),
  h3('Función: confirmarPago() — async'),
  para('Deshabilita #btnConfirmarPago para evitar doble envío. Hace POST a facturaapi.php con { action:"procesarPago", id_factura:facturaActual.id_factura, metodo_pago:metodoActual }. Si success: muestra toast de éxito con monto y método, cierra el modal y recarga ambas listas (pendientes e historial) con await Promise.all([cargarPendientes(), cargarHistorial()]).', { size: 20 }),
  space(),
  h3('Precarga desde parqueadero (sessionStorage)'),
  para('Segundo listener DOMContentLoaded: lee sessionStorage.getItem("facturaPrecargada"). Si existe, lo borra del storage con removeItem() y llama a abrirCobro(JSON.parse(precargada)) con setTimeout(..., 800) para esperar que cargarPendientes() termine primero.', { size: 20 }),
  space(),

  h2('8.2 Archivo: controllers/facturaapi.php'),
  h3('GET action=getPendientes'),
  para('JOIN entre factura, salida, entrada, tipo_servicio y modulo. WHERE f.estado_pago = "PENDIENTE". ORDER BY f.fecha_emision DESC. Para cada factura calcula tiempo_estancia con PHP: new DateTime(fecha_hora_entrada)->diff(new DateTime(fecha_hora_salida)) y construye el string "X días, Y horas, Z minutos". También calcula horas_cobradas = ceil(horas_totales).', { size: 20 }),
  space(),
  h3('GET action=getPagadas'),
  para('Recibe fecha en $_GET. Hace JOIN LEFT entre factura, salida, entrada, tipo_servicio. WHERE f.estado_pago = "PAGADA" AND DATE(f.fecha_emision) = ? Si no hay resultados con DATE(), reintenta con LIKE "$fecha%". Retorna el array más debug_info (útil para diagnosticar problemas de zona horaria).', { size: 20 }),
  space(),
  h3('POST action=procesarPago'),
  para('Lee id_factura (forzado a entero con (int)) y metodo_pago (validado contra ["EFECTIVO","TRANSFERENCIA","TARJETA","NEQUI"]). Ejecuta UPDATE factura SET estado_pago="PAGADA", metodo_pago=? WHERE id_factura=? AND estado_pago="PENDIENTE". Verifica rowCount() > 0. Si no cambió nada, busca la causa: ya estaba PAGADA, o la factura no existe. Retorna el mensaje específico.', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  9. SALIDAAPI — FLUJO CRÍTICO
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('9. CONTROLADOR DE SALIDAS — salidaapi.php (Flujo Crítico)'),
  para('Este controlador es el más importante del sistema porque ejecuta el cierre de una estancia y genera la factura. Recibe id_entrada y debe manejar múltiples casos límite.', { size: 20 }),
  space(),

  h2('9.1 POST action=release — Flujo Detallado'),
  flowStep(1, 'Buscar la entrada', 'SELECT e.id_entrada, e.id_modulo, e.placa, e.fecha_hora_entrada, e.estado, e.id_tipo_servicio, ts.tarifa, ts.nombre_tipo_servicio FROM entrada e INNER JOIN tipo_servicio ts ON e.id_tipo_servicio = ts.id_tipo_servicio WHERE e.id_entrada = ?. Si no existe: retorna error.'),
  space(1),
  flowStep(2, 'Verificar salida existente', 'SELECT id_salida, fecha_hora_salida FROM salida WHERE id_entrada = ?. Si ya hay salida Y la entrada está FINALIZADO es inconsistencia resuelta → solo limpia el módulo si quedó en OCUPADO por error.'),
  space(1),
  flowStep(3, 'Calcular tiempo de estancia', 'Si hay salida existente usa su fecha; si no, usa DateTime(). diff() entre fecha_entrada y fecha_salida. horas_totales = (days×24) + h + (i/60). horas_cobrar = max(1, ceil(horas_totales)) — mínimo 1 hora. monto_total = horas_cobrar × tarifa.'),
  space(1),
  flowStep(4, 'Transacción de cierre (si no hay salida previa)', 'INSERT INTO salida (id_entrada, fecha_hora_salida) VALUES (?, NOW()). INSERT INTO factura (id_salida, monto_total, metodo_pago, estado_pago) VALUES (?, ?, NULL, "PENDIENTE"). UPDATE entrada SET estado = "FINALIZADO" WHERE id_entrada = ?. UPDATE modulo SET estado = "DISPONIBLE" WHERE id_modulo = ?. COMMIT o ROLLBACK.'),
  space(1),
  flowStep(5, 'Retornar factura al frontend', 'Retorna objeto factura con todos los datos para mostrar la notificación en parqueadero.php: id_factura, monto_total, horas_cobradas, tarifa_hora, tiempo_estancia (string legible), nombre tipo servicio, placa y fecha_emision.'),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  10. REPORTES
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('10. MÓDULO REPORTES'),

  h2('10.1 Archivo: views/usuarios/reportes.php'),
  para('Genera estadísticas por rango de fechas configurable. Incluye cuatro gráficos/tablas: resumen KPI, ingresos por tipo de servicio (barras), distribución horaria y tabla detalle con paginación. Permite exportar a PDF con jsPDF.', { size: 20 }),
  space(),
  h3('Variables JavaScript globales'),
  twoColTable([
    ['cargandoReporte', 'Bandera booleana global. Protege contra bucles infinitos: si setPeriodoRapido() triggerea cargarReporte() que triggerea otros eventos, la bandera impide ejecuciones concurrentes.'],
    ['paginaActualDetalle', 'Página actual de la tabla de detalle. Inicia en 1, se resetea a 1 cada vez que llega nueva data.'],
    ['registrosPorPagina', 'Constante de 10 registros por página en la tabla de detalle.'],
    ['todosLosDetalles = []', 'Array con TODOS los registros de detalle recibidos de la API. La paginación se hace en memoria (JS).'],
    ['totalDetallesPages', 'ceil(totalDetallesItems / registrosPorPagina). Calculado en renderDetalle().'],
    ['datosResumen, datosTipo, datosDetalle', 'Variables globales que guardan la última respuesta de la API para usarlas al generar el PDF sin una segunda petición.'],
  ], 'Variable', 'Descripción'),
  space(),
  h3('Función: setPeriodoRapido()'),
  para('Listener del <select id="periodoRapido">. Calcula fechaInicio y fechaFin para: hoy (ambas = today), ayer (ambas = yesterday), semana (lunes al domingo de la semana actual), mes (primer y último día del mes), mes_anterior, trimestre (primer día del trimestre a último día) y año (1 enero al 31 diciembre). Formatea las fechas como YYYY-MM-DD con la hora LOCAL del navegador (evitando el problema de UTC). Establece los valores en #fechaInicio y #fechaFin. Limpia el select a valor vacío para evitar re-disparos. Llama a cargarReporte() con setTimeout(..., 100).', { size: 20 }),
  space(),
  h3('Función: cargarReporte() — async'),
  para('Primero verifica cargandoReporte (bandera anti-bucle). Lee fechaInicio y fechaFin de los inputs. Valida que ambas existan y que inicio ≤ fin. Actualiza #subtitulo y #rFechaLabel con getLabelRango(). Muestra "…" en las tarjetas KPI. Hace cuatro fetch en paralelo con Promise.all(): getResumen, getPorTipo, getPorHora y getDetalle — todos con los parámetros fecha_inicio y fecha_fin. Al resolver llama a renderResumen(), renderBarras(), renderHoras() y renderDetalle(). Guarda los datos en las variables globales para el PDF.', { size: 20 }),
  space(),
  h3('Función: renderResumen(result)'),
  para('Lee result.data con campos: ingresos (suma COP), vehiculos (int), tiempo_prom (string "Xh Ym"), mejor_servicio (string nombre). Escribe en #rIngresos, #rVehiculos, #rTiempo y #rMejor. Si result.success es false, pone "0" en todos.', { size: 20 }),
  space(),
  h3('Función: renderBarras(result)'),
  para('Para cada ítem en result.data crea un div.bar-row con: nombre del tipo + cantidad de vehículos entre paréntesis a la izquierda, ingresos formateados a la derecha, y una barra CSS con width=pct% (calculado por el servidor: cantidad/máxCantidad×100). Si no hay datos muestra empty state "Sin entradas registradas".', { size: 20 }),
  space(),
  h3('Función: renderHoras(result)'),
  para('El servidor retorna 9 franjas de 2 horas (06-08, 08-10, ..., 22-24). Para cada franja crea un div.hora-cell. Si cantidad > 0 agrega la clase "activa" (borde y texto dorado). Muestra el rango de hora en la parte superior y la cantidad de vehículos en grande.', { size: 20 }),
  space(),
  h3('Función: renderDetalle(result)'),
  para('Guarda result.data en todosLosDetalles. Resetea paginaActualDetalle a 1. Calcula totalDetallesPages. Actualiza el contador #totalDetalle. Llama a renderPaginaDetalle() y renderPagination().', { size: 20 }),
  space(),
  h3('Función: renderPaginaDetalle()'),
  para('Calcula slice: inicio=(pagina-1)×10, fin=min(inicio+10, total). Crea filas en #repTable con: hora de entrada, placa en monoespaciado, tipo de servicio, módulo con fondo gris, duración en texto, monto total en gold (o "—" si null) y badge de estado (Pagado en verde, Pendiente en amarillo, En curso en azul).', { size: 20 }),
  space(),
  h3('Función: descargarReporte()'),
  para('Llama a capturarContenidoVista() que lee los textos actuales del DOM (estadísticas ya renderizadas, filas de la tabla actual, texto de las barras). Luego llama a generarPDFDescargable(contenido) con jsPDF. Genera un PDF de página A4 con: encabezado PARKINGSURE con acento dorado, período del reporte, tarjetas KPI, sección de análisis por tipo de servicio y tabla de transacciones con paginación. Descarga el archivo como "reporte_parkingsure_INICIO_FIN.pdf".', { size: 20 }),
  space(),

  h2('10.2 Archivo: controllers/reportesapi.php'),
  h3('GET action=getResumen'),
  para('SQL 1 (ingresos): SELECT COALESCE(SUM(f.monto_total), 0) AS ingresos FROM factura f WHERE f.estado_pago = "PAGADA" AND DATE(f.fecha_emision) BETWEEN :fecha_inicio AND :fecha_fin. SQL 2 (vehículos): COUNT(*) FROM entrada WHERE DATE(fecha_hora_entrada) BETWEEN ... SQL 3 (tiempo promedio): AVG(TIMESTAMPDIFF(MINUTE, e.fecha_hora_entrada, s.fecha_hora_salida)) — convierte minutos a "Xh Ym". SQL 4 (mejor servicio): GROUP BY nombre_tipo_servicio ORDER BY COUNT DESC LIMIT 1.', { size: 20 }),
  space(),
  h3('GET action=getPorTipo'),
  para('JOIN entre entrada, tipo_servicio, salida y factura. LEFT JOIN para incluir entradas sin factura. WHERE en fecha_hora_entrada entre el rango. GROUP BY id_tipo_servicio. Calcula pct = cantidad/maxCantidad×100. Los datos se usan para las barras horizontales.', { size: 20 }),
  space(),
  h3('GET action=getPorHora'),
  para('SELECT HOUR(fecha_hora_entrada) AS hora, COUNT(*) AS cantidad FROM entrada GROUP BY HOUR. PHP construye las 9 franjas de 2 horas sumando los vehículos de cada hora dentro del rango de la franja.', { size: 20 }),
  space(),
  h3('GET action=getDetalle'),
  para('JOIN entre entrada, tipo_servicio, modulo, salida y factura. LEFT JOIN en salida y factura para incluir entradas activas. Calcula minutos_estancia con TIMESTAMPDIFF(MINUTE, e.fecha_hora_entrada, COALESCE(s.fecha_hora_salida, NOW())). PHP convierte a string "Xh Ym".', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  11. USUARIOS
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('11. MÓDULO USUARIOS'),

  h2('11.1 Archivo: views/usuarios/usuarios.php'),
  para('Solo accesible para rol ADMINISTRADOR (verificado en PHP: if($_SESSION["rol"] !== "ADMINISTRADOR") redirige a dashboard.php). Carga los usuarios desde PHP en el momento de renderizar la página (no via AJAX) y los inyecta como JSON en el HTML. El grid se construye con JavaScript sobre esos datos.', { size: 20 }),
  space(),
  h3('Carga de datos en PHP (tiempo de renderizado)'),
  para('El bloque PHP hace: require_once database.php, conectar(), prepare + execute del JOIN entre personal, rol y users, fetchAll(PDO::FETCH_ASSOC) y json_encode($usuarios, JSON_UNESCAPED_UNICODE). El resultado se inyecta como const phpUsuarios = <?= $usersJson ?? "[]" ?>.', { size: 20 }),
  space(),
  h3('Tarjetas de estadísticas (PHP inline)'),
  para('Las tarjetas de "Total Usuarios", "Administradores" y "Operadores" usan COUNT(*) con subconsultas PHP directas (no AJAX). Los valores se renderizan en el HTML inicial.', { size: 20 }),
  space(),
  h3('Variable JavaScript: phpUsuarios'),
  para('Array de objetos con: id_personal, usuario, rol (nombre_rol), nombre, telefono, correo. Esta variable alimenta toda la lógica de filtrado y renderizado del grid.', { size: 20 }),
  space(),
  h3('Función: renderGrid(lista)'),
  para('Crea el div.usr-grid. Para cada usuario calcula initials (primeras letras de cada palabra del nombre, máximo 2). isAdmin = rol==="ADMINISTRADOR". Crea tarjetas con: avatar circular con iniciales (gold para admin, gris para operador), nombre y rol, filas de detalle (ID, usuario, teléfono, correo) y badge de rol. El onclick de cada tarjeta llama a abrirDet(u).', { size: 20 }),
  space(),
  h3('Función: filtrarUsuarios()'),
  para('Lee el texto de #buscarUsr y el valor de #filtroRol. Filtra phpUsuarios por: que nombre, correo o usuario incluyan q (en minúsculas) Y que rol sea el seleccionado o "todos". Llama a renderGrid(lista filtrada).', { size: 20 }),
  space(),
  h3('Modal de nuevo usuario — 4 pasos'),
  twoColTable([
    ['Step 1 (nuStep1)', 'Datos personales: nombre, teléfono, cédula, correo. Validación: nombre, correo y cédula son obligatorios. Si pasa: showNuStep(2).'],
    ['Step 2 (nuStep2)', 'Credenciales: rol (select), usuario, contraseña. Validación: usuario y contraseña no vacíos, contraseña ≥ 6 chars. Si pasa: rellena la pantalla de confirmación y showNuStep(3).'],
    ['Step 3 (nuStep3)', 'Confirmación: muestra todos los datos en formato legible (cfNombre, cfCedula, cfCorreo, cfTel, cfUsuario, cfRol). Botón "Crear Usuario" llama a submitNuevo().'],
    ['Step 4 (nuStep4)', 'Éxito: muestra el mensaje y tras 1.4 segundos envía el formulario POST a ../../controllers/controllerusuario.php.'],
  ], 'Paso', 'Descripción'),
  space(),
  h3('Función: submitNuevo()'),
  para('Crea un <form> dinámico con method="POST" y action="../../controllers/controllerusuario.php". Agrega campos ocultos para nombre, telefono, cedula, correo, rol, usuario y password. Muestra el paso de éxito. Tras 1400ms hace document.body.appendChild(form) y form.submit().', { size: 20 }),
  space(),
  h3('Función: abrirDet(u)'),
  para('Rellena el modal de detalle con los datos del usuario seleccionado: iniciales en el avatar, nombre y rol en el header, y una lista de info-row con todos los campos. Abre el overlay #detOverlay.', { size: 20 }),
  space(),

  h2('11.2 Módulo Servicios — views/usuarios/servicios.php'),
  para('Solo para ADMINISTRADOR. Muestra el catálogo de tipos de servicio cargados vía AJAX desde dashboardapi.php?action=getServicios.', { size: 20 }),
  space(),
  h3('Función: cargarServicios() — async'),
  para('Fetch a dashboardapi.php?action=getServicios. Popula el array servicios con los tipos de servicio de la BD. Llama a renderCatalogo() y updateStats().', { size: 20 }),
  space(),
  h3('Función: renderCatalogo()'),
  para('Filtra según filtroActual ("todos" o "activos"). Crea tarjetas svc-card con clase activo o inactivo según estado. Muestra nombre, tarifa formateada, estado badge y botones "Editar" y "Desactivar/Activar". El onclick de la tarjeta abre el modal de edición.', { size: 20 }),
  space(),
  h3('Función: pedirToggle(i) y ejecutarToggle()'),
  para('pedirToggle: guarda el índice en pendingToggleIdx, rellena el modal de confirmación con el nombre del servicio y el texto de la acción. ejecutarToggle: cambia el estado en memoria (ACTIVO↔INACTIVO), cierra el modal y recarga el catálogo. (Nota: en la versión actual los cambios son solo en memoria y se pierden al recargar.)'),
  space(),
  h3('Función: updateStats()'),
  para('Calcula desde el array servicios filtrado por ACTIVO: count de activos, Math.min de tarifas y Math.max de tarifas. Actualiza #stActivos, #stMin y #stMax.', { size: 20 }),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  12. APIs REFERENCIA COMPLETA
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('12. CONTROLADORES API — REFERENCIA COMPLETA'),

  h2('12.1 tiposervicioapi.php'),
  twoColTable([
    ['GET action=getAll', 'SELECT id_tipo_servicio, nombre_tipo_servicio, tarifa, estado FROM tipo_servicio WHERE estado="ACTIVO" ORDER BY nombre_tipo_servicio. Usado por parqueadero.php al abrir el modal de asignación.'],
    ['GET action=getById', 'Recibe id_tipo_servicio en $_GET. Retorna un solo tipo. Raramente usado directamente.'],
    ['POST action=create', 'Recibe nombre_tipo_servicio, tarifa y estado. Verifica que el nombre no exista. INSERT INTO tipo_servicio.'],
  ], 'Endpoint', 'Descripción'),
  space(),

  h2('12.2 salidaapi.php — Acciones GET'),
  twoColTable([
    ['GET action=getAll', 'JOIN entre salida, entrada, modulo y vehiculo. Retorna todas las salidas ordenadas por fecha_hora_salida DESC.'],
    ['GET action=getById', 'Recibe id en $_GET. Retorna una salida específica con todos sus JOINs.'],
    ['GET action=getActive', 'Retorna entradas SIN salida (vehículos dentro). Estado ACTIVO. JOIN con modulo y vehiculo.'],
  ], 'Endpoint', 'Descripción'),
  space(),

  h2('12.3 Resumen de todos los endpoints'),
  twoColTable([
    ['dashboardapi.php?action=getStats', 'Total/disponibles/ocupados de módulos + ingresos del día.'],
    ['dashboardapi.php?action=getServicios', 'Todos los tipos de servicio de la BD.'],
    ['dashboardapi.php?action=getRecentEvents&page=N', 'UNION de entradas y salidas, paginado de 10 en 10.'],
    ['dashboardapi.php?action=getOccupancyByType', 'Entradas activas agrupadas por tipo de servicio.'],
    ['moduloapi.php?action=getAll', 'Módulos con auto-limpieza de inconsistencias.'],
    ['moduloapi.php?action=getStats', 'Conteos totales/disponibles/ocupados/mantenimiento.'],
    ['moduloapi.php?action=getById', 'Un módulo por id.'],
    ['moduloapi.php POST action=create', 'Crear nuevo módulo.'],
    ['moduloapi.php POST action=changeState', 'Cambiar estado de un módulo.'],
    ['moduloapi.php PUT', 'Actualizar ubicación y estado de un módulo.'],
    ['moduloapi.php DELETE', 'Eliminar módulo sin entradas activas.'],
    ['entradaapi.php?action=getActive', 'Entradas con estado ACTIVO.'],
    ['entradaapi.php?action=getAll', 'Todas las entradas.'],
    ['entradaapi.php POST action=assign', 'Registrar nueva entrada (asignar vehículo a módulo).'],
    ['salidaapi.php POST action=release', 'Registrar salida + factura PENDIENTE.'],
    ['vehiculosapi.php?action=getAll', 'Todos los vehículos con datos de cliente.'],
    ['vehiculosapi.php?action=getById', 'Vehículo por placa. Usado para validación en tiempo real.'],
    ['vehiculosapi.php?action=getClientes', 'Clientes para el select de registro de vehículo.'],
    ['vehiculosapi.php POST action=create', 'Registrar nuevo vehículo.'],
    ['facturaapi.php?action=getPendientes', 'Facturas PENDIENTES con tiempo de estancia calculado.'],
    ['facturaapi.php?action=getPagadas&fecha=...', 'Facturas PAGADAS de una fecha específica.'],
    ['facturaapi.php POST action=procesarPago', 'Cambiar factura de PENDIENTE a PAGADA.'],
    ['tiposervicioapi.php?action=getAll', 'Tipos de servicio ACTIVOS.'],
    ['reportesapi.php?action=getResumen', 'KPIs del período: ingresos, vehículos, tiempo promedio, mejor servicio.'],
    ['reportesapi.php?action=getPorTipo', 'Ingresos y cantidad por tipo de servicio.'],
    ['reportesapi.php?action=getPorHora', 'Distribución de vehículos por franja horaria de 2h.'],
    ['reportesapi.php?action=getDetalle', 'Todas las transacciones del período (detalle completo).'],
    ['loginController.php POST', 'Autenticar usuario y crear sesión.'],
    ['logout.php GET', 'Destruir sesión y redirigir al login.'],
  ], 'Endpoint', 'Descripción', 3600),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  13. FLUJOS END-TO-END
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('13. FLUJOS DE NEGOCIO END-TO-END'),

  h2('13.1 Flujo Completo: Ingreso de Vehículo'),
  infoBox('Precondición', [
    'El vehículo está registrado en la tabla vehiculo.',
    'El módulo que se quiere asignar tiene estado DISPONIBLE.',
    'El tipo de servicio elegido tiene estado ACTIVO.',
    'El operador tiene sesión iniciada ($_SESSION["user"] existe).',
  ], C.greenLight, C.green),
  space(1),
  numbered('Operador abre parqueadero.php → DOMContentLoaded llama cargarModulos().'),
  numbered('cargarModulos() hace GET a moduloapi.php?action=getAll. El controlador auto-corrige inconsistencias y retorna módulos con estado real.'),
  numbered('renderLot() dibuja las celdas de colores. El módulo destino aparece en verde.'),
  numbered('Operador hace clic en una celda verde → openAsignarModal(i).'),
  numbered('Modal se abre. cargarTiposServicio() carga el select. Operador escribe la placa.'),
  numbered('Listener debounced valida la placa en vehiculosapi.php?action=getById&placa=X → feedback visual verde.'),
  numbered('Operador selecciona tipo de servicio y hace clic en "Asignar Vehículo".'),
  numbered('confirmarAsignacion() deshabilita el botón, verifica placa en BD, hace POST a entradaapi.php action=assign.'),
  numbered('entradaapi.php valida los 4 requisitos, abre transacción, INSERT entrada, UPDATE modulo a OCUPADO, commit.'),
  numbered('Respuesta { success:true } → toast de éxito → closeAsignarModal() → cargarModulos() refresca la vista.'),
  numbered('La celda del módulo cambia a rojo con la placa visible. Las estadísticas se actualizan.'),
  space(),

  h2('13.2 Flujo Completo: Salida y Cobro Inmediato'),
  numbered('Operador hace clic en celda roja → openLiberarModal(i).'),
  numbered('Modal calcula tiempo transcurrido con GET entradaapi.php?action=getActive → filtra por id_modulo.'),
  numbered('Operador hace clic en "Liberar Módulo".'),
  numbered('confirmarLiberacion() hace GET getActive para obtener id_entrada, luego POST salidaapi.php action=release.'),
  numbered('salidaapi.php calcula horas, crea salida en BD, crea factura PENDIENTE, actualiza entrada a FINALIZADO y módulo a DISPONIBLE.'),
  numbered('Respuesta incluye objeto factura → mostrarNotificacionFactura() muestra el overlay con el resumen.'),
  numbered('Operador hace clic en "Ir a Pagos 💳" → abrirPagosConFactura() guarda en sessionStorage → redirige a pagos.php.'),
  numbered('pagos.php carga → DOMContentLoaded lee sessionStorage → abrirCobro(factura) abre el modal de cobro automáticamente.'),
  numbered('Operador selecciona método (EFECTIVO por defecto) y hace clic en "Confirmar Pago".'),
  numbered('confirmarPago() hace POST a facturaapi.php action=procesarPago → UPDATE factura SET estado_pago="PAGADA", metodo_pago=?'),
  numbered('Respuesta exitosa → toast → cerrar modal → recarga pendientes e historial → la factura aparece en el historial.'),
  space(),

  h2('13.3 Flujo Completo: Generación de Reporte'),
  numbered('Operador abre reportes.php → DOMContentLoaded establece fecha de hoy en ambos inputs (usando hora local, NO UTC).'),
  numbered('Carga inicial: setTimeout(cargarReporte, 500) para cargar el reporte del día actual.'),
  numbered('Operador puede cambiar fechas manualmente o usar el select de períodos rápidos.'),
  numbered('setPeriodoRapido() calcula las fechas con JavaScript local, las escribe en los inputs, limpia el select y llama cargarReporte().'),
  numbered('cargarReporte() verifica la bandera cargandoReporte. Lee fechas. Ejecuta 4 fetch en paralelo: getResumen, getPorTipo, getPorHora y getDetalle.'),
  numbered('Cada función render* actualiza su sección de la UI.'),
  numbered('La tabla de detalle muestra los primeros 10 registros. Los botones de paginación permiten navegar el resto (paginación en memoria).'),
  numbered('Operador hace clic en "Descargar PDF" → capturarContenidoVista() lee el DOM actual → generarPDFDescargable() crea el PDF con jsPDF → descarga automática.'),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  14. VARIABLES GLOBALES
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('14. VARIABLES GLOBALES Y ESTADO DE LA APLICACIÓN'),

  h2('14.1 Variables de Sesión PHP (compartidas entre todas las vistas)'),
  twoColTable([
    ['$_SESSION["user"]', 'id_personal del empleado logueado. Verificado con isset() en cada vista para proteger el acceso.'],
    ['$_SESSION["rol"]', '"ADMINISTRADOR" u "OPERADOR". Controla visibilidad de menús (Usuarios, Servicios) y acceso a ciertas vistas.'],
    ['$_SESSION["nombre"]', 'Nombre completo del empleado. Mostrado en el avatar y nombre en el topbar de todas las vistas.'],
    ['$_SESSION["usuario"]', 'Nombre de usuario (para referencia interna).'],
    ['$_SESSION["id_personal"]', 'Usado en entradaapi.php para el campo id_personal de la tabla entrada (quién registró).'],
    ['$_SESSION["correo"]', 'Para futura implementación de recuperación de contraseña.'],
    ['$_SESSION["flash_error"]', 'Mensaje de error temporal entre redirecciones. Se lee y borra en la vista de destino.'],
    ['$_SESSION["flash_success"]', 'Mensaje de éxito temporal entre redirecciones.'],
  ], 'Variable', 'Descripción y uso'),
  space(),

  h2('14.2 Variables de Almacenamiento del Navegador'),
  twoColTable([
    ['sessionStorage "facturaPrecargada"', 'JSON de la factura generada al liberar un módulo. Escrito por abrirPagosConFactura() en parqueadero.php. Leído y borrado por pagos.php al cargar.'],
  ], 'Clave', 'Descripción y ciclo de vida'),
  space(),

  h2('14.3 Constantes y Configuración Global'),
  twoColTable([
    ['Zona horaria PHP', 'date_default_timezone_set("America/Bogota") en database.php. Afecta a date() y DateTime().'],
    ['Zona horaria MySQL', 'SET time_zone = \'-05:00\' ejecutado tras cada conexión. Afecta a NOW() y CURDATE().'],
    ['Zona horaria JS', 'Calculada con new Date() usando la hora local del navegador. No se usa toISOString() para evitar conversión UTC.'],
    ['Puerto MySQL', '3307 (no el estándar 3306). Configurado en el DSN de database.php.'],
    ['Mínimo horas cobradas', '1 hora. max(1, ceil(horas_totales)) en salidaapi.php.'],
    ['Métodos de pago válidos', '"EFECTIVO", "TRANSFERENCIA", "TARJETA", "NEQUI". Validados en facturaapi.php.'],
    ['Imagen de hash', 'bcrypt con cost factor por defecto (10) vía PASSWORD_DEFAULT en PHP.'],
  ], 'Constante', 'Descripción'),
  pageBreak(),
);

// ══════════════════════════════════════════════════════════════════════════════
//  15. SEGURIDAD
// ══════════════════════════════════════════════════════════════════════════════
children.push(
  h1('15. SEGURIDAD Y CONSIDERACIONES TÉCNICAS'),

  h2('15.1 Seguridad Implementada'),
  twoColTable([
    ['SQL Injection', 'Todas las consultas usan PDO::prepare() con parámetros nombrados (:param) o posicionales (?). Nunca se concatena directamente entrada del usuario en SQL.'],
    ['XSS', 'Las vistas PHP usan htmlspecialchars() en todos los datos de sesión renderizados en el HTML. Los datos en JavaScript se inyectan via json_encode() que escapa caracteres peligrosos.'],
    ['Autenticación', 'password_hash() con PASSWORD_DEFAULT (bcrypt). password_verify() para comparar sin exponer el hash.'],
    ['Control de acceso', 'Cada vista verifica $_SESSION["user"] al inicio. Vistas de admin verifican $_SESSION["rol"] === "ADMINISTRADOR" y redirigen si no cumple.'],
    ['Doble envío', 'Los botones de acción se deshabilitan (btn.disabled=true) durante el fetch y se vuelven a habilitar en el bloque finally.'],
    ['Transacciones ACID', 'Las operaciones que afectan múltiples tablas (asignar entrada, registrar salida+factura) usan beginTransaction() + commit() + rollback() en caso de excepción.'],
    ['CORS', 'header("Access-Control-Allow-Origin: *") en todos los controladores API. Aceptable para desarrollo local; en producción debería restringirse al dominio propio.'],
  ], 'Aspecto', 'Implementación'),
  space(),

  h2('15.2 Limitaciones Conocidas'),
  twoColTable([
    ['Sin CSRF tokens', 'Los formularios POST no incluyen tokens anti-CSRF. Un atacante con sesión activa podría enviar peticiones falsas.'],
    ['Contraseña en texto plano en tránsito', 'Sin HTTPS en local, la contraseña viaja sin cifrar en el POST del login.'],
    ['Edición de vehículos en memoria', 'Los cambios de edición en vehiculos.php (editar, toggle estado, eliminar) solo afectan el array local JavaScript y se pierden al recargar. Requieren implementar los endpoints PUT/DELETE en vehiculosapi.php.'],
    ['Servicios sin persistencia', 'Los cambios de estado (activar/desactivar) en servicios.php son solo en memoria. Los endpoints POST/PUT del dashboard API para servicios no están completamente implementados.'],
    ['Sin paginación server-side en reportes', 'Los reportes cargan todos los registros del período en memoria del navegador. Para períodos largos con muchos datos esto puede ser lento.'],
    ['Menú hamburger duplicado', 'Cada vista tiene su propio <script> para el hamburger menu. Sería mejor tenerlo en un archivo compartido.'],
  ], 'Limitación', 'Descripción'),
  space(),

  h2('15.3 Topbar y Navegación'),
  para('El topbar está duplicado en cada vista PHP (no hay componente compartido). La navegación activa se marca añadiendo la clase "active" al enlace correspondiente en el HTML estático de cada página. El menú hamburger para móvil se inicializa en un <script> al final de cada vista que añade listeners de click al botón #hamburger-btn y al array .nav-links.', { size: 20 }),
  space(),

  h2('15.4 Sistema de Diseño (ps-core.css)'),
  para('El archivo views/usuarios/style/ps-core.css define el sistema de diseño completo con variables CSS en :root. Incluye: tokens de color (gold, surface-0 a 3, borders, text primary/secondary/muted), sistema de grillas responsive (stats-grid-4, stats-grid-3), componentes reutilizables (card, stat-card, badge, btn, form-input, modal, overlay, toast, pagination) y breakpoints para tablet (max 1024px) y móvil (max 768px, max 480px). Carga las fuentes Syne y Outfit desde Google Fonts.', { size: 20 }),
  space(),

  h2('15.5 Modelo de Datos del Frontend vs Backend'),
  para('Los controladores retornan nombres de campos en snake_case de MySQL (nombre_tipo_servicio, fecha_hora_entrada). El JavaScript los consume directamente con notación de punto. No hay una capa de transformación o ORM. Esto significa que si se renombra una columna en la BD hay que actualizar todas las referencias en el frontend.', { size: 20 }),
  space(),

  divider(),
  space(2),
  new Paragraph({
    children: [new TextRun({ text: 'Fin de la Documentación Técnica de ParkingSure v3.0', font: 'Arial', size: 22, italic: true, color: C.textMuted })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 200, after: 80 },
  }),
  new Paragraph({
    children: [new TextRun({ text: 'Mayo 2026 · Sistema de Gestión de Parqueaderos', font: 'Arial', size: 20, color: C.gold, bold: true })],
    alignment: AlignmentType.CENTER,
    spacing: { before: 0, after: 0 },
  }),
);

// ══════════════════════════════════════════════════════════════════════════════
//  BUILD DOCUMENT
// ══════════════════════════════════════════════════════════════════════════════
const doc = new Document({
  numbering: {
    config: [
      {
        reference: 'bullets',
        levels: [{
          level: 0, format: LevelFormat.BULLET, text: '•',
          alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 540, hanging: 260 } } }
        }, {
          level: 1, format: LevelFormat.BULLET, text: '◦',
          alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 900, hanging: 260 } } }
        }]
      },
      {
        reference: 'numbers',
        levels: [{
          level: 0, format: LevelFormat.DECIMAL, text: '%1.',
          alignment: AlignmentType.LEFT,
          style: { paragraph: { indent: { left: 540, hanging: 260 } } }
        }]
      },
    ]
  },
  styles: {
    default: {
      document: { run: { font: 'Arial', size: 20, color: C.text } }
    },
    paragraphStyles: [
      { id: 'Heading1', name: 'Heading 1', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 36, bold: true, font: 'Arial', color: C.darkBg },
        paragraph: { spacing: { before: 400, after: 160 }, outlineLevel: 0 } },
      { id: 'Heading2', name: 'Heading 2', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 28, bold: true, font: 'Arial', color: C.blue },
        paragraph: { spacing: { before: 320, after: 120 }, outlineLevel: 1 } },
      { id: 'Heading3', name: 'Heading 3', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 24, bold: true, font: 'Arial', color: C.gray },
        paragraph: { spacing: { before: 240, after: 80 }, outlineLevel: 2 } },
      { id: 'Heading4', name: 'Heading 4', basedOn: 'Normal', next: 'Normal', quickFormat: true,
        run: { size: 22, bold: true, font: 'Arial', color: C.textMid },
        paragraph: { spacing: { before: 160, after: 60 }, outlineLevel: 3 } },
    ]
  },
  sections: [{
    properties: {
      page: {
        size: { width: 12240, height: 15840 },
        margin: { top: 1080, right: 1080, bottom: 1080, left: 1080 }
      }
    },
    headers: {
      default: new Header({
        children: [new Paragraph({
          children: [
            new TextRun({ text: 'PARKINGSURE', font: 'Arial', size: 18, bold: true, color: C.gold }),
            new TextRun({ text: '  ·  Documentación Técnica Completa del Sistema', font: 'Arial', size: 18, color: C.textMuted }),
          ],
          border: { bottom: { style: BorderStyle.SINGLE, size: 4, color: C.grayMid } },
          spacing: { after: 0 },
        })]
      })
    },
    footers: {
      default: new Footer({
        children: [new Paragraph({
          children: [
            new TextRun({ text: 'ParkingSure v3.0  ·  Mayo 2026  ·  Pág. ', font: 'Arial', size: 16, color: C.textMuted }),
            new TextRun({ children: [PageNumber.CURRENT], font: 'Arial', size: 16, color: C.gold }),
            new TextRun({ text: ' de ', font: 'Arial', size: 16, color: C.textMuted }),
            new TextRun({ children: [PageNumber.TOTAL_PAGES], font: 'Arial', size: 16, color: C.gold }),
          ],
          alignment: AlignmentType.CENTER,
          border: { top: { style: BorderStyle.SINGLE, size: 4, color: C.grayMid } },
          spacing: { before: 0 },
        })]
      })
    },
    children,
  }]
});

Packer.toBuffer(doc).then(buffer => {
  fs.writeFileSync('/mnt/user-data/outputs/DOCUMENTACION_PARKINGSURE_COMPLETA.docx', buffer);
  console.log('✅ Documento generado correctamente.');
}).catch(err => {
  console.error('Error:', err);
  process.exit(1);
});