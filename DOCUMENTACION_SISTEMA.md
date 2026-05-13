# 📋 DOCUMENTACIÓN COMPLETA - SISTEMA PARKINGSURE

## 🏗️ ARQUITECTURA MVC DEL SISTEMA

### 📁 Estructura de Directorios
```
PARKINGSURE/
├── controllers/          # Controladores PHP (API endpoints)
├── views/usuarios/       # Vistas del sistema
├── sql/                 # Scripts de base de datos
├── img/                 # Imágenes y recursos estáticos
├── style/               # Archivos CSS
└── requirements.txt       # Dependencias Python (reportes)
```

### 🔄 PATRÓN DE DISEÑO MVC

#### 📄 **VISTAS (Views)**
**Responsabilidad**: Interfaz de usuario y presentación de datos
- **Ubicación**: `views/usuarios/`
- **Tecnología**: PHP + HTML + CSS + JavaScript
- **Funciones**:
  - Renderizar HTML estructurado
  - Manejar eventos del usuario (clicks, formularios)
  - Validar datos en el frontend
  - Comunicarse con controladores vía fetch API
  - Actualizar UI dinámicamente

**Ejemplo - `dashboard.php`**:
```php
// 1. Renderiza estructura HTML
<div class="page-header">
  <h1>Dashboard</h1>
</div>

// 2. Maneja eventos del usuario
<button onclick="cargarEstadisticas()">Actualizar</button>

// 3. Comunica con controlador
async function cargarEstadisticas() {
  const response = await fetch('../../controllers/dashboardapi.php?action=getStats');
  const result = await response.json();
  // Actualizar UI con datos recibidos
}
```

#### 🔧 **CONTROLADORES (Controllers)**
**Responsabilidad**: Lógica de negocio y API endpoints
- **Ubicación**: `controllers/`
- **Tecnología**: PHP con PDO
- **Funciones**:
  - Recibir peticiones HTTP (GET, POST, PUT, DELETE)
  - Validar y sanitizar datos de entrada
  - Ejecutar consultas SQL preparadas
  - Gestionar transacciones de base de datos
  - Responder en formato JSON

**Ejemplo - `dashboardapi.php`**:
```php
// 1. Recibe petición y valida
$action = $_GET['action'] ?? '';
if ($action === 'getStats') {
    // 2. Conecta a BD y ejecuta consulta
    $stmt = $conn->prepare("SELECT COUNT(*) FROM entrada WHERE fecha_hora_salida IS NULL");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // 3. Responde en formato JSON
    echo json_encode(['success' => true, 'data' => $stats]);
}
```

#### 🗄️ **MODELOS (Models)**
**Responsabilidad**: Abstracción de base de datos
- **Ubicación**: Implícito en controladores vía PDO
- **Tecnología**: SQL con MySQL/MariaDB
- **Funciones**:
  - Definir estructura de tablas
  - Relaciones entre tablas (FK, PK)
  - Consultas SQL complejas con JOINs
  - Integridad de datos

**Ejemplo - Modelo de Vehículo**:
```sql
-- Tabla principal
CREATE TABLE vehiculo (
  placa VARCHAR(8) PRIMARY KEY,
  id_cliente INT,
  marca VARCHAR(30),
  modelo VARCHAR(30),
  anio INT,
  color VARCHAR(20),
  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente)
);

-- Relación con cliente
CREATE TABLE cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  cedula_users VARCHAR(20),
  nombre VARCHAR(80),
  telefono VARCHAR(20),
  correo VARCHAR(80),
  FOREIGN KEY (cedula_users) REFERENCES users(cedula)
);
```

### 🔗 Conexión a Base de Datos
- **Motor**: MySQL/MariaDB
- **Conexión**: PDO en cada controlador
- **Configuración**: `config.php` (credenciales de BD)
- **Charset**: UTF-8
- **Timezone**: Configurado para hora local (America/Bogota)

---

## 🔐 MÓDULO DE AUTENTICACIÓN

### � **FLUJO COMPLETO DE AUTENTICACIÓN**
```
Usuario → login.php → loginController.php → Dashboard
```

### � **Vista: `login.php`**
**Responsabilidad**: Interfaz de autenticación
- **Tecnología**: PHP + HTML5 + CSS
- **Funciones**:
  1. Formulario de login (usuario, contraseña)
  2. Enlace de recuperación de contraseña
  3. Validación frontend básica
  4. Redirección automática según rol

### 🔧 **Controlador: `loginController.php`**
**Responsabilidad**: Lógica de autenticación
- **Tecnología**: PHP + PDO + Session Management
- **Proceso Completo**:
  ```php
  // 1. Recepción y validación
  $usuario = $_POST['usuario'] ?? '';
  $password = $_POST['password'] ?? '';
  
  // 2. Consulta a base de datos
  $stmt = $conn->prepare(
      "SELECT p.*, r.nombre_rol 
       FROM personal p 
       JOIN roles r ON p.id_rol = r.id_rol 
       WHERE p.usuario = ?"
  );
  
  // 3. Verificación de contraseña
  if ($stmt->execute([$usuario]) {
      $user = $stmt->fetch(PDO::FETCH_ASSOC);
      if (password_verify($password, $user['password_hash'])) {
          // 4. Creación de sesión segura
          session_regenerate_id(true);
          $_SESSION['id_usuario'] = $user['id_personal'];
          $_SESSION['nombre'] = $user['nombre'];
          $_SESSION['rol'] = $user['nombre_rol'];
          
          // 5. Redirección basada en rol
          header("Location: dashboard.php");
      }
  }
  ```

**Tablas Involucradas:**
- `personal`: Datos de usuarios del sistema
- `roles`: Definición de permisos y niveles de acceso

**Seguridad Implementada:**
- ✅ **Password Hashing**: bcrypt/password_hash
- ✅ **SQL Injection**: Prepared statements
- ✅ **Session Security**: Regeneración de ID
- ✅ **XSS Protection**: Escape de datos en vistas

---

## 📊 MÓDULO DASHBOARD (Panel Principal)

### 📄 Vista: `dashboard.php`
**Responsabilidad**: Panel principal con estadísticas en tiempo real
- **Tecnología**: PHP + JavaScript + Chart.js
- **Funciones**:
  - Renderizar tarjetas de información
  - Actualizar gráficos dinámicos
  - Mostrar estadísticas del día
  - Refrescar datos automáticamente

### 🔧 Controlador: `dashboardapi.php`
**Responsabilidad**: API para estadísticas del sistema
- **Endpoints Principales**:
```php
GET dashboardapi.php?action=getStats
- Consulta: SELECT COUNT(*) FROM entrada WHERE fecha_hora_salida IS NULL
- Tabla: entrada
- Retorna: Vehículos activos actualmente

GET dashboardapi.php?action=getIngresosHoy
- Consulta: SELECT COALESCE(SUM(monto_total), 0) FROM factura WHERE DATE(fecha_emision) = CURDATE()
- Tabla: factura
- Retorna: Ingresos del día actual

GET dashboardapi.php?action=getOcupacion
- Consulta: SELECT modulo, COUNT(*) FROM entrada WHERE fecha_hora_salida IS NULL GROUP BY modulo
- Tabla: entrada
- Retorna: Ocupación por módulo
```

**Flujo de Datos:**
```
Dashboard → dashboardapi.php → Base de Datos → Dashboard
```

**Actualización Automática:**
- **Cada 30 segundos**: Refresca estadísticas
- **Reinicio Diario**: A medianoche, reinicia contadores del día

---

## 🚗 MÓDULO PARQUEADERO

### � **FLUJO COMPLETO DE OPERACIONES**
```
Usuario → parqueadero.php → parqueaderoapi.php → Base de Datos → Parqueadero
```

### 📄 **Vista: `parqueadero.php`**
**Responsabilidad**: Interfaz de operaciones del parqueadero
- **Tecnología**: PHP + JavaScript + CSS Grid
- **Funciones**:
  - Registro de entrada de vehículos
  - Gestión de salidas y facturación
  - Visualización en tiempo real del estado
  - Búsqueda y filtros de vehículos

### 🔧 **Controlador: `parqueaderoapi.php`**
**Responsabilidad**: API para operaciones del parqueadero
- **Tecnología**: PHP + PDO + JSON
- **Endpoints Principales**:
```php
POST parqueaderoapi.php?action=registrarEntrada
- Recibe: placa, id_tipo_servicio, id_modulo
- Inserta: tabla entrada
- Actualiza: estado del módulo a OCUPADO
- Retorna: Confirmación con datos del vehículo

POST parqueaderoapi.php?action=registrarSalida
- Recibe: id_entrada, id_salida
- Calcula: tarifa según tiempo y tipo_servicio
- Inserta: tabla salida
- Inserta: tabla factura (estado PENDIENTE)
- Retorna: Factura generada pendiente de pago

GET parqueaderoapi.php?action=getEstadoActual
- Consulta: JOIN entrada ↔ salida ↔ tipo_servicio ↔ modulo
- Retorna: Estado completo del parqueadero
```

**Tablas Involucradas:**
- `entrada`: Registro de vehículos que ingresan
- `salida`: Registro de vehículos que salen
- `factura`: Generación de facturas
- `tipo_servicio`: Tipos de tarifa (HORA, DÍA, MES)
- `modulo`: Espacios físicos del parqueadero

**Lógica de Tarificación:**
1. **Tiempo Fraccionado**: Calcula por minutos/horas
2. **Tarifa Base**: Según tipo_servicio
3. **Recargos**: Tiempo adicional después del período base
4. **Redondeo**: A valores comerciales

---

## 🚗 MÓDULO VEHÍCULOS

### � **FLUJO COMPLETO DE GESTIÓN**
```
Usuario → vehículos.php → vehiculosapi.php → Base de Datos → Vehículos
```

### � **Vista: `vehiculos.php`**
**Responsabilidad**: Interfaz de gestión de vehículos
- **Tecnología**: PHP + HTML + CSS Grid + JavaScript
- **Funciones**:
  1. **Catálogo de vehículos**: Grid responsive con filtros
  2. **Registro de vehículos**: Formulario con cliente existente/nuevo
  3. **Búsqueda avanzada**: Por placa, modelo, propietario
  4. **Edición inline**: Modal con validación
  5. **Estadísticas**: Totales por estado

### 🔧 **Controlador: `vehiculosapi.php`**
**Responsabilidad**: API REST para gestión de vehículos
- **Tecnología**: PHP + PDO + JSON
- **Endpoints Principales**:
```php
GET vehiculosapi.php?action=getAll
- Consulta: SELECT v.*, u.nombre as nombre_cliente 
           FROM vehiculo v 
           LEFT JOIN cliente c ON v.id_cliente = c.id_cliente
           LEFT JOIN users u ON c.cedula_users = u.cedula
- Tablas: vehiculo, cliente, users
- Retorna: Lista completa de vehículos con datos de clientes

POST vehiculosapi.php?action=create
- Recibe: placa, id_cliente, marca, modelo, color
- Inserta: tabla vehiculo
- Retorna: Confirmación de registro

POST vehiculosapi.php?action=createWithClient
- Recibe: datos de cliente + datos de vehículo
- Proceso: 
  1. Verifica si cliente existe
  2. Si no existe, inserta en tabla cliente
  3. Inserta vehículo con ID del cliente
  4. Usa transacciones para integridad
- Retorna: Confirmación completa

GET vehiculosapi.php?action=getClientes
- Consulta: SELECT c.id_cliente, u.nombre, c.cedula_users
           FROM cliente c 
           LEFT JOIN users u ON c.cedula_users = u.cedula
- Tablas: cliente, users
- Retorna: Lista de clientes para el select
```

**Relaciones de Datos:**
- `vehiculo` ↔ `cliente` (id_cliente)
- `cliente` ↔ `users` (cedula_users)
- Integridad referencial completa con JOINs

**Validaciones Implementadas:**
- ✅ **Placa única**: Verificación de duplicados
- ✅ **Campos obligatorios**: Validación frontend y backend
- ✅ **Transacciones ACID**: Rollback automático en errores
- ✅ **Sanitización**: Escape de datos y prepared statements

---

## 💳 MÓDULO PAGOS Y FACTURACIÓN

### � **FLUJO COMPLETO DE FACTURACIÓN**
```
Vehículo sale → factura generada → pagos.php → Procesamiento → Estadísticas
```

### � **Vista: `pagos.php`**
**Responsabilidad**: Interfaz de facturación y pagos
- **Tecnología**: PHP + JavaScript MVC
- **Proceso de Pago:**
1. **Selección de Factura**: Usuario elige factura pendiente
2. **Método de Pago**: EFECTIVO, TRANSFERENCIA (únicos métodos disponibles)
3. **Confirmación**: Actualiza estado en BD con método seleccionado
4. **Estadísticas**: Actualiza contadores diarios por método de pago

### 🔧 **Controlador: `facturaapi.php`**
**Responsabilidad**: API REST para facturación
- **Tecnología**: PHP + PDO + JSON
- **Endpoints Principales**:
```php
GET facturaapi.php?action=getPendientes
- Consulta: JOIN factura ↔ salida ↔ entrada ↔ tipo_servicio
- WHERE: f.estado_pago = 'PENDIENTE'
- Tablas: factura, salida, entrada, tipo_servicio
- Retorna: Facturas pendientes con datos completos

GET facturaapi.php?action=getPagadas&fecha=YYYY-MM-DD
- Consulta: JOIN factura ↔ salida ↔ entrada ↔ tipo_servicio
- WHERE: f.estado_pago = 'PAGADA' AND DATE(fecha_emision) = ?
- Retorna: Pagos del día con método y montos

POST facturaapi.php?action=procesarPago
- Recibe: id_factura, metodo_pago
- Proceso: 
  1. Valida factura existente
  2. Actualiza estado_pago = 'PAGADA'
  3. Registra fecha_emision = NOW()
  4. Registra metodo_pago
- Retorna: Confirmación con datos actualizados
```

**Integridad de Datos:**
- ✅ **Transacciones ACID**: Todo o nada
- ✅ **Relaciones completas**: factura → salida → entrada
- ✅ **Consistencia**: Estados sincronizados
- ✅ **Validaciones**: Previene duplicados y errores

**Tablas Involucradas:**
- `factura`: Registro de facturas y pagos
- `salida`: Vinculación con entrada
- `entrada`: Datos del vehículo
- `tipo_servicio`: Información de tarifa

---

## 📋 MÓDULO REPORTES

### � **FLUJO COMPLETO DE REPORTES**
```
Usuario → reportes.php → reportesapi.php → Base de Datos → PDF
```

### � **Vista: `reportes.php`**
**Responsabilidad**: Interfaz de generación de reportes
- **Tecnología**: PHP + JavaScript + Chart.js + jsPDF
- **Funciones**:
  - Selección de períodos (hoy, semana, mes, personalizado)
  - Estadísticas visuales con gráficos dinámicos
  - Previsualización antes de generar PDF
  - Exportación múltiple (PDF, Excel)

### 🔧 **Controlador: `reportesapi.php`**
**Responsabilidad**: API REST para generación de reportes
- **Tecnología**: PHP + PDO + JSON
- **Endpoints Principales**:
```php
GET reportesapi.php?action=getResumen&fecha_inicio=X&fecha_fin=Y
- Consulta: 
  - Total ingresos: SUM(f.monto_total)
  - Total vehículos: COUNT(DISTINCT e.placa)
  - Tiempo promedio: AVG(TIMEDIFF(s.fecha_hora_salida, e.fecha_hora_entrada))
  - Mejor servicio: TOP 1 por ingresos
- Tablas: factura, entrada, salida, tipo_servicio
- Retorna: Resumen consolidado del período

GET reportesapi.php?action=getPorTipo&fecha_inicio=X&fecha_fin=Y
- Consulta: 
  - Agrupado por tipo de servicio
  - COUNT(*) como cantidad
  - SUM(f.monto_total) como ingresos
- Tablas: factura, entrada, salida, tipo_servicio
- Retorna: Desglose por tipo de servicio

GET reportesapi.php?action=getPorHora&fecha_inicio=X&fecha_fin=Y
- Consulta:
  - Agrupado por hora del día
  - COUNT(*) como cantidad por hora
- Tablas: entrada, salida
- Retorna: Distribución horaria de vehículos
```

### 🐍 **Módulo PDF (Python)**
**Responsabilidad**: Generación profesional de documentos PDF
- **Tecnología**: Python + fpdf2 + pandas
- **Archivo**: `reportes_pdf.py`
- **Funciones**:
  - Generación de ingresos (reporte financiero)
  - Generación de ocupación (estadísticas del parqueadero)
  - Generación de vehículos (catálogo completo)
  - Manejo interactivo de descargas
  - Diseño corporativo con branding PARKINGSURE

**Integración**:
- ✅ **Llamada desde PHP**: exec() para ejecutar script Python
- ✅ **Parámetros dinámicos**: Fechas y filtros pasados como argumentos
- ✅ **Retorno JSON**: Resultados de la consulta SQL
- ✅ **Manejo de errores**: Captura y logging de excepciones

---

## 👥 MÓDULO USUARIOS

### � **FLUJO COMPLETO DE GESTIÓN**
```
Administrador → usuarios.php → dashboardapi.php → Base de Datos → Usuarios
```

### 📄 **Vista: `usuarios.php`**
**Responsabilidad**: Interfaz de gestión de usuarios
- **Tecnología**: PHP + HTML + CSS Grid
- **Funciones**:
  1. **CRUD completo**: Crear, leer, actualizar, eliminar usuarios
  2. **Asignación de roles**: ADMINISTRADOR, OPERADOR
  3. **Validación**: Formularios con validación frontend y backend
  4. **Permisos**: Control de acceso por rol

### 🔧 **Controlador: `dashboardapi.php`**
**Responsabilidad**: API REST para gestión de usuarios
- **Tecnología**: PHP + PDO + JSON
- **Endpoints Principales**:
```php
GET dashboardapi.php?action=getUsuarios
- Consulta: JOIN personal ↔ roles
- Tablas: personal, roles
- Retorna: Lista completa con roles asignados

POST dashboardapi.php?action=guardarUsuario
- Proceso: 
  1. Validación de campos obligatorios
  2. Hash de contraseña con password_hash()
  3. Inserta en tabla personal
  4. Asigna rol existente
  5. Retorna confirmación

POST dashboardapi.php?action=actualizarUsuario
- Proceso:
  1. Verifica existencia del usuario
  2. Actualiza campos en tabla personal
  3. Maneja cambio de contraseña si se proporciona
  4. Mantiene integridad referencial
  5. Retorna confirmación

POST dashboardapi.php?action=eliminarUsuario
- Proceso:
  1. Verifica que no sea el usuario actual
  2. Soft delete (desactivación lógica)
  3. Mantiene integridad de datos relacionados
  4. Retorna confirmación
```

**Permisos por Rol:**
- **ADMINISTRADOR**: Acceso completo a todos los módulos
- **OPERADOR**: Acceso limitado a operaciones diarias

**Seguridad Implementada:**
- ✅ **Password Hashing**: bcrypt/password_hash
- ✅ **SQL Injection**: Prepared statements
- ✅ **Validación**: Frontend y backend completa
- ✅ **Integridad**: Soft delete y relaciones mantenidas

---

## ⚙️ MÓDULO SERVICIOS

### � **FLUJO COMPLETO DE GESTIÓN**
```
Administrador → servicios.php → dashboardapi.php → Base de Datos → Servicios
```

### �� **Vista: `servicios.php`**
**Responsabilidad**: Interfaz de gestión de servicios
- **Tecnología**: PHP + HTML + CSS Grid
- **Funciones**:
  1. **Catálogo de servicios**: Grid responsive con filtros
  2. **Gestión de precios**: Actualización de tarifas
  3. **Activación/Desactivación**: Control de servicios disponibles
  4. **Estadísticas de uso**: Frecuencia de cada servicio

### 🔧 **Controlador: `dashboardapi.php`**
**Responsabilidad**: API REST para gestión de servicios
- **Tecnología**: PHP + PDO + JSON
- **Endpoints Principales**:
```php
GET dashboardapi.php?action=getServicios
- Consulta: SELECT * FROM tipo_servicio ORDER BY nombre_tipo_servicio
- Tabla: tipo_servicio
- Retorna: Todos los servicios con estado

POST dashboardapi.php?action=guardarServicio
- Recibe: nombre_tipo_servicio, tarifa, descripcion
- Proceso: 
  1. Validación de campos obligatorios
  2. Inserta en tabla tipo_servicio
  3. Retorna confirmación

POST dashboardapi.php?action=actualizarServicio
- Recibe: id_tipo_servicio y campos actualizados
- Proceso:
  1. Verifica existencia del servicio
  2. Actualiza campos en tabla tipo_servicio
  3. Maneja cambios de tarifa
  4. Retorna confirmación

POST dashboardapi.php?action=toggleServicio
- Recibe: id_tipo_servicio
- Proceso:
  1. Actualiza estado (ACTIVO/INACTIVO)
  2. Retorna nuevo estado del servicio
```

**Tipos de Servicios:**
- **HORA**: Tarifa por hora (con fraccionamiento)
- **DÍA**: Tarifa diaria (sin importar hora)
- **MES**: Tarifa mensual (sin importar días)
- **ESTÁNDAR**: Tarifa base del sistema

**Validaciones Implementadas:**
- ✅ **Campos obligatorios**: Validación frontend y backend
- ✅ **Servicios únicos**: No duplicar nombres de servicio
- ✅ **Tarifas válidas**: Validación de valores numéricos
- ✅ **Transacciones**: Manejo con rollback automático

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Tablas Principales

#### `personal`
```sql
- id_personal (PK)
- nombre
- usuario
- password_hash
- correo
- id_rol (FK)
- estado
- fecha_creacion
```

#### `roles`
```sql
- id_rol (PK)
- nombre_rol
- descripcion
- permisos
```

#### `tipo_servicio`
```sql
- id_tipo_servicio (PK)
- nombre_tipo_servicio
- tarifa
- descripcion
- estado
```

#### `modulo`
```sql
- id_modulo (PK)
- nombre_modulo
- ubicacion
- capacidad
- estado
```

#### `entrada`
```sql
- id_entrada (PK)
- placa
- id_modulo (FK)
- id_personal (FK)
- id_tipo_servicio (FK)
- fecha_hora_entrada
- fecha_hora_salida (NULL si está activo)
```

#### `salida`
```sql
- id_salida (PK)
- id_entrada (FK)
- fecha_hora_salida
- duracion_minutos
- tarifa_calculada
```

#### `factura`
```sql
- id_factura (PK)
- id_salida (FK)
- fecha_emision
- monto_total
- metodo_pago
- estado_pago (PENDIENTE/PAGADA)
```

---

## 🔄 FLUJOS DE TRABAJO COMPLETOS

### 1. Flujo de Ingreso de Vehículo
```
Usuario → parqueadero.php → Selecciona módulo → Ingresa placa
↓
parqueaderoapi.php?action=registrarEntrada
↓
INSERT entrada (placa, id_modulo, id_tipo_servicio, fecha_hora_entrada)
↓
UPDATE modulo (estado = OCUPADO)
↓
Retorna: Confirmación con datos del vehículo
```

### 2. Flujo de Salida y Facturación
```
Usuario → parqueadero.php → Selecciona vehículo activo → "Registrar Salida"
↓
parqueaderoapi.php?action=registrarSalida
↓
Calcula tarifa según tiempo y tipo_servicio
↓
INSERT salida (id_entrada, fecha_hora_salida, duracion, tarifa_calculada)
↓
INSERT factura (id_salida, monto_total, estado_pago = PENDIENTE)
↓
Retorna: Factura generada pendiente de pago
```

### 3. Flujo de Procesamiento de Pago
```
Usuario → pagos.php → Selecciona factura pendiente → "Procesar Pago"
↓
facturaapi.php?action=procesarPago
↓
UPDATE factura (estado_pago = PAGADA, fecha_emision = NOW(), metodo_pago)
↓
Actualiza estadísticas diarias
↓
Retorna: Confirmación de pago procesado
```

### 4. Flujo de Generación de Reportes
```
Usuario → reportes.php → Selecciona rango fechas → "Generar Reporte"
↓
reportesapi.php (múltiples endpoints concurrentes)
↓
getResumen + getPorTipo + getPorHora + getDetalle
↓
Procesamiento de datos y generación de gráficos
↓
Usuario → "Descargar PDF"
↓
html2canvas + jsPDF → Generación y descarga
```

---

## 🔧 CONFIGURACIÓN Y MANTENIMIENTO

### Archivos de Configuración
- `config.php`: Credenciales de base de datos
- `.htaccess`: Configuración de Apache (URL amigables)
- `requirements.txt`: Dependencias Python para reportes

### Seguridad Implementada
- **Sesiones Seguras**: Regeneración de ID de sesión
- **Password Hashing**: bcrypt/password_hash
- **SQL Injection Prevention**: PDO con prepared statements
- **XSS Prevention**: Escape de datos en vistas
- **CSRF Protection**: Tokens en formularios

### Optimizaciones
- **Índices de BD**: En campos clave para consultas rápidas
- **Caching**: Estadísticas en memoria para respuestas rápidas
- **Lazy Loading**: Carga de datos bajo demanda
- **Responsive Design**: Adaptación a dispositivos móviles

### 🔧 Zona Horaria
- **Importante**: Todas las funciones usan `new Date()` para hora local
- **Evitar**: `toISOString()` que convierte a UTC
- **Formato**: YYYY-MM-DD consistente en todo el sistema
- **Ejemplo**: Mañana usará fecha local de mañana correctamente
- **Automático**: Detección automática de zona horaria sin ajustes manuales
- **Logging**: Sistema muestra offset y fecha local detectada
- **Consistencia**: Todas las vistas usan la misma lógica de fechas

### 📊 Sistema de Reportes
- **Períodos Rápidos**: hoy, ayer, semana, mes, mes_anterior, trimestre, año
- **Cálculo Automático**: Semana calculada de lunes a domingo
- **Fechas Locales**: Todas las fechas usan hora local del navegador
- **Sin Bucles**: Protección contra cargas repetitivas
- **Logging Completo**: Depuración detallada de cada consulta

---

## 🚀 DEPLOYMENT Y REQUISITOS

### Requisitos del Sistema
- **PHP**: 7.4+ con extensiones PDO, mbstring
- **MySQL**: 5.7+ o MariaDB 10.2+
- **Apache/Nginx**: Con mod_rewrite activo
- **Python**: 3.8+ (para módulo de reportes PDF)

### Variables de Entorno
- **Timezone**: America/Bogota
- **Charset**: UTF-8
- **Memory Limit**: 256M+ recomendado
- **Max Execution Time**: 300s para reportes grandes

---

## 📞 SOPORTE Y MANTENIMIENTO

### Logs del Sistema
- **PHP Errors**: `logs/php-error.log`
- **Access Logs**: `logs/access.log`
- **Application Logs**: `logs/app.log`

### Monitoreo
- **Estadísticas en tiempo real**: Dashboard actualizado cada 30s
- **Alertas automáticas**: Cuando hay módulos llenos
- **Backup diario**: Automático de base de datos

### Actualizaciones
- **Versionamiento**: Controlado por git
- **Migraciones**: Scripts para actualización de BD
- **Backward Compatibility**: Mantenida con versiones anteriores

---

## 📝 NOTAS ADICIONALES

### Mejoras Recientes (Mayo 2026)
1. **Sistema de Fechas Locales**: Detección automática sin ajustes manuales
2. **Períodos Rápidos Corregidos**: Cálculo correcto de semana y todos los períodos
3. **Reportes Optimizados**: Sin bucles infinitos y con logging detallado
4. **Zona Horaria Automática**: Funciona en cualquier zona horaria sin configuración

### Mejoras Futuras
1. **Notificaciones Push**: WebSocket para actualizaciones en tiempo real
2. **API REST**: Endpoints JSON para integración móvil
3. **Multi-tenant**: Soporte para múltiples parqueaderos
4. **Analytics Avanzado**: Predicciones y tendencias
5. **Integración Pagos**: Pasarelas de pago electrónicas

### Consideraciones Técnicas
1. **Escalabilidad**: Diseñado para +1000 vehículos simultáneos
2. **Performance**: Optimizado para consultas <100ms
3. **Disponibilidad**: 99.9% uptime requerido
4. **Respaldo**: Backups automáticos cada 6 horas

---

*Documentación actualizada: 10 de Mayo de 2026*
*Versión del sistema: v3.0*
*Estado: Producción estable*
