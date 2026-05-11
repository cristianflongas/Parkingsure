# 📋 DOCUMENTACIÓN COMPLETA - SISTEMA PARKINGSURE

## 🏗️ ARQUITECTURA GENERAL DEL SISTEMA

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

### 🔗 Conexión a Base de Datos
- **Motor**: MySQL/MariaDB
- **Conexión**: PDO en cada controlador
- **Configuración**: `config.php` (credenciales de BD)
- **Charset**: UTF-8
- **Timezone**: Configurado para hora local (America/Bogota)

---

## 🔐 MÓDULO DE AUTENTICACIÓN

### 📄 Vista: `login.php`
**Acciones Principales:**
1. **Inicio de Sesión**: Valida credenciales contra BD
2. **Recuperación Contraseña**: Envia correo de recuperación
3. **Redirección Automática**: Basada en rol de usuario

### 🔧 Controlador: `loginController.php`
**Proceso de Login:**
```php
1. Recibe usuario y password vía POST
2. Consulta BD con JOIN a tabla roles
3. Verifica contraseña con password_verify()
4. Crea variables de sesión:
   - $_SESSION['id_usuario'] = id_personal
   - $_SESSION['nombre'] = nombre
   - $_SESSION['usuario'] = usuario
   - $_SESSION['rol'] = nombre_rol
   - $_SESSION['correo'] = correo
5. Redirige al dashboard según rol
```

**Tablas Involucradas:**
- `personal` (usuarios del sistema)
- `roles` (roles de usuario: ADMINISTRADOR, OPERADOR)

**Flujo de Validación:**
1. Verificar campos vacíos
2. Sanitizar inputs
3. Consulta SQL preparada
4. Verificar hash de contraseña
5. Crear sesión segura
6. Redirección basada en rol

---

## 📊 MÓDULO DASHBOARD (Panel Principal)

### 📄 Vista: `dashboard.php`
**Funcionalidades:**
1. **Estadísticas en Tiempo Real**: Ingresos del día, vehículos activos
2. **Tarjetas de Información**: Estado del parqueadero
3. **Gráficos Dinámicos**: Ocupación por módulo
4. **Accesos Rápidos**: Atajos a otros módulos

### 🔧 Controlador: `dashboardapi.php`
**Endpoints Principales:**
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

**Actualización Automática:**
- **Cada 30 segundos**: Refresca estadísticas
- **Reinicio Diario**: A medianoche, reinicia contadores del día

---

## 🚗 MÓDULO PARQUEADERO

### 📄 Vista: `parqueadero.php`
**Funcionalidades:**
1. **Registro de Entrada**: Nuevo vehículo al parqueadero
2. **Gestión de Salidas**: Registrar salida y calcular tarifa
3. **Vista en Tiempo Real**: Estado actual de cada módulo
4. **Búsqueda de Vehículos**: Filtros por placa, estado

### 🔧 Controlador: `parqueaderoapi.php`
**Endpoints Principales:**
```php
POST parqueaderoapi.php?action=registrarEntrada
- Recibe: placa, id_tipo_servicio, id_modulo
- Inserta: tabla entrada
- Actualiza: estado del módulo a OCUPADO

POST parqueaderoapi.php?action=registrarSalida
- Recibe: id_entrada, id_salida
- Calcula: tarifa según tiempo y tipo de servicio
- Inserta: tabla salida
- Inserta: tabla factura (estado PENDIENTE)

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

### 📄 Vista: `vehiculos.php`
**Funcionalidades:**
1. **Listado Completo**: Todos los vehículos registrados
2. **Búsqueda Avanzada**: Por placa, tipo, estado
3. **Historial de Servicios**: Todos los usos del vehículo
4. **Estadísticas Individuales**: Frecuencia, ingresos totales

### 🔧 Controlador: `vehiculosapi.php`
**Endpoints Principales:**
```php
GET vehiculosapi.php?action=getVehiculos
- Consulta: SELECT * FROM entrada ORDER BY fecha_hora_entrada DESC
- Tabla: entrada
- Retorna: Lista de vehículos con estado actual

GET vehiculosapi.php?action=getHistorialVehiculo
- Recibe: placa
- Consulta: JOIN entrada ↔ salida ↔ factura ↔ tipo_servicio
- Retorna: Historial completo del vehículo
```

**Filtros Disponibles:**
- Por placa exacta
- Por tipo de servicio
- Por estado (activo/inactivo)
- Por rango de fechas

---

## 💳 MÓDULO PAGOS Y FACTURACIÓN

### 📄 Vista: `pagos.php`
**Funcionalidades:**
1. **Facturas Pendientes**: Lista de cobros pendientes
2. **Historial de Pagos**: Todos los pagos del día
3. **Procesamiento de Pagos**: Marcar facturas como pagadas
4. **Estadísticas Diarias**: Ingresos, transacciones, promedio

### 🔧 Controlador: `facturaapi.php`
**Endpoints Principales:**
```php
GET facturaapi.php?action=getPendientes
- Consulta: JOIN factura ↔ salida ↔ entrada ↔ tipo_servicio ↔ modulo
- WHERE: f.estado_pago = 'PENDIENTE'
- Retorna: Facturas pendientes de cobro

GET facturaapi.php?action=getPagadas&fecha=YYYY-MM-DD
- Consulta: JOIN factura ↔ salida ↔ entrada ↔ tipo_servicio
- WHERE: f.estado_pago = 'PAGADA' AND DATE(f.fecha_emision) = ?
- Retorna: Pagos del día especificado

POST facturaapi.php?action=procesarPago
- Recibe: id_factura, metodo_pago
- Actualiza: factura.estado_pago = 'PAGADA'
- Actualiza: factura.fecha_emision = NOW()
```

**Proceso de Pago:**
1. **Selección de Factura**: Usuario elige factura pendiente
2. **Método de Pago**: EFECTIVO, TARJETA, TRANSFERENCIA
3. **Confirmación**: Actualiza estado en BD
4. **Estadísticas**: Actualiza contadores diarios

**Tablas Involucradas:**
- `factura`: Registro de facturas y pagos
- `salida`: Vinculación con entrada
- `entrada`: Datos del vehículo
- `tipo_servicio`: Información de tarifa

---

## 📋 MÓDULO REPORTES

### 📄 Vista: `reportes.php`
**Funcionalidades:**
1. **Reportes por Rango**: Selección de fechas personalizadas
2. **Estadísticas Consolidadas**: Resumen del período
3. **Gráficos Dinámicos**: Por tipo de servicio, por hora
4. **Exportación PDF**: Generación de reportes imprimibles
5. **Períodos Rápidos**: Hoy, ayer, semana, mes, año

### 🔧 Controlador: `reportesapi.php`
**Endpoints Principales:**
```php
GET reportesapi.php?action=getResumen&fecha_inicio=X&fecha_fin=Y
- Consulta: 
  - Total ingresos: SUM(f.monto_total)
  - Total vehículos: COUNT(DISTINCT e.placa)
  - Tiempo promedio: AVG(TIMEDIFF(s.fecha_hora_salida, e.fecha_hora_entrada))
  - Mejor servicio: TOP 1 por ingresos
- Tablas: factura, entrada, salida, tipo_servicio

GET reportesapi.php?action=getPorTipo&fecha_inicio=X&fecha_fin=Y
- Consulta: 
  - Agrupado por tipo de servicio
  - COUNT(*) como cantidad
  - SUM(f.monto_total) como ingresos
- Tablas: factura, entrada, tipo_servicio

GET reportesapi.php?action=getPorHora&fecha_inicio=X&fecha_fin=Y
- Consulta:
  - Agrupado por hora del día
  - COUNT(*) como cantidad por hora
- Tablas: entrada, salida

GET reportesapi.php?action=getDetalle&fecha_inicio=X&fecha_fin=Y
- Consulta: JOIN completo de todas las tablas
- Retorna: Registro detallado de cada transacción
```

**Generación de PDF:**
1. **Captura de Vista**: Usa html2canvas para capturar DOM
2. **jsPDF**: Genera PDF profesional
3. **Diseño Corporativo**: Colores y formato PARKINGSURE
4. **Descarga Automática**: Opción de guardar en dispositivo

---

## 👥 MÓDULO USUARIOS

### 📄 Vista: `usuarios.php`
**Funcionalidades:**
1. **Gestión de Personal**: CRUD de usuarios
2. **Asignación de Roles**: ADMINISTRADOR, OPERADOR
3. **Control de Accesos**: Estado de cuentas
4. **Permisos por Rol**: Diferentes niveles de acceso

### 🔧 Controlador: `dashboardapi.php`
**Endpoints Principales:**
```php
GET dashboardapi.php?action=getUsuarios
- Consulta: JOIN personal ↔ roles
- Retorna: Lista de usuarios con rol asignado

POST dashboardapi.php?action=guardarUsuario
- Recibe: Todos los campos del formulario
- Inserta: tabla personal
- Hashea: contraseña con password_hash()

POST dashboardapi.php?action=actualizarUsuario
- Recibe: id_usuario y campos actualizados
- Actualiza: tabla personal
- Maneja: cambio de contraseña si se proporciona

POST dashboardapi.php?action=eliminarUsuario
- Recibe: id_usuario
- Verifica: que no sea el usuario actual
- Elimina: Lógico (soft delete recomendado)
```

**Permisos por Rol:**
- **ADMINISTRADOR**: Acceso completo a todos los módulos
- **OPERADOR**: Acceso limitado a operaciones diarias

---

## ⚙️ MÓDULO SERVICIOS

### 📄 Vista: `servicios.php`
**Funcionalidades:**
1. **Catálogo de Servicios**: Lista de tipos de tarifa
2. **Gestión de Precios**: Actualización de tarifas
3. **Activación/Desactivación**: Control de servicios disponibles
4. **Estadísticas de Uso**: Frecuencia de cada servicio

### 🔧 Controlador: `dashboardapi.php`
**Endpoints Principales:**
```php
GET dashboardapi.php?action=getServicios
- Consulta: SELECT * FROM tipo_servicio ORDER BY nombre_tipo_servicio
- Tabla: tipo_servicio
- Retorna: Todos los servicios con estado

POST dashboardapi.php?action=guardarServicio
- Recibe: nombre_tipo_servicio, tarifa, descripcion
- Inserta: tabla tipo_servicio
- Valida: campos obligatorios

POST dashboardapi.php?action=actualizarServicio
- Recibe: id_tipo_servicio y campos actualizados
- Actualiza: tabla tipo_servicio
- Maneja: cambios de tarifa

POST dashboardapi.php?action=toggleServicio
- Recibe: id_tipo_servicio
- Actualiza: estado (ACTIVO/INACTIVO)
- Retorna: Nuevo estado del servicio
```

**Tipos de Servicios:**
- **HORA**: Tarifa por hora (con fraccionamiento)
- **DÍA**: Tarifa diaria (sin importar hora)
- **MES**: Tarifa mensual (sin importar días)
- **ESTÁNDAR**: Tarifa base del sistema

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
