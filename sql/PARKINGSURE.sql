-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for parkingsure
-- Dumping structure for table parkingsure.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Dumping data for table parkingsure.users: ~7 rows (approximately)
INSERT INTO `users` (`cedula`, `nombre`, `telefono`, `correo`) VALUES
	('1080182082', 'Cristian Felipe Longas', '3023012409', 'cristianf.longas@gmail.com'),
	('12230492', 'Isabella longas', '323019384', 'isabellal@gmail.com'),
	('123456789', 'Juan Pérez', '3001234567', 'juan.perez@email.com'),
	('321654987', 'Pedro Gómez', '3005556666', 'pedro.gomez@email.com'),
	('456789123', 'Carlos Ruiz', '3201112233', 'carlos.ruiz@email.com'),
	('789123456', 'Laura Soto', '3154445566', 'laura.soto@email.com'),
	('987654321', 'María López', '3109876543', 'maria.lopez@email.com');
-- Dumping structure for table parkingsure.rol
DROP TABLE IF EXISTS `rol`;
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Dumping data for table parkingsure.rol: ~2 rows (approximately)
INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
	(1, 'ADMINISTRADOR'),
	(2, 'OPERADOR');
-- Dumping structure for table parkingsure.personal
DROP TABLE IF EXISTS `personal`;
CREATE TABLE IF NOT EXISTS `personal` (
  `id_personal` int NOT NULL AUTO_INCREMENT,
  `cedula_users` varchar(20) NOT NULL,
  `id_rol` int NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id_personal`),
  UNIQUE KEY `uq_usuario` (`usuario`),
  UNIQUE KEY `uq_cedula_pers` (`cedula_users`),
  KEY `fk_personal_rol` (`id_rol`),
  CONSTRAINT `fk_personal_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_personal_users` FOREIGN KEY (`cedula_users`) REFERENCES `users` (`cedula`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Dumping data for table parkingsure.personal: ~2 rows (approximately)
INSERT INTO `personal` (`id_personal`, `cedula_users`, `id_rol`, `usuario`, `password_hash`) VALUES
	(1, '1080182082', 1, 'cflg', '$2y$10$MSNe.zf5HvaAiT8o0dZtd.X9NtM5WU.H0k0cok0ZAeAVVPyL7J.ia'),
	(2, '12230492', 2, 'isabella', '$2y$10$MSNe.zf5HvaAiT8o0dZtd.X9NtM5WU.H0k0cok0ZAeAVVPyL7J.ia');
-- Dumping structure for table parkingsure.cliente
DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `cedula_users` varchar(20) NOT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `uq_cedula_cli` (`cedula_users`),
  CONSTRAINT `fk_cliente_users` FOREIGN KEY (`cedula_users`) REFERENCES `users` (`cedula`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table parkingsure.cliente: ~5 rows (approximately)
INSERT INTO `cliente` (`id_cliente`, `cedula_users`) VALUES
	(1, '123456789'),
	(5, '321654987'),
	(3, '456789123'),
	(4, '789123456'),
	(2, '987654321');

DROP TABLE IF EXISTS `modulo`;
CREATE TABLE IF NOT EXISTS `modulo` (
  `id_modulo` int NOT NULL AUTO_INCREMENT,
  `ubicacion` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'DISPONIBLE',
  PRIMARY KEY (`id_modulo`),
  CONSTRAINT `modulo_chk_1` CHECK ((`estado` in (_utf8mb4'DISPONIBLE',_utf8mb4'OCUPADO',_utf8mb4'MANTENIMIENTO')))
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Dumping data for table parkingsure.modulo: ~7 rows (approximately)
INSERT INTO `modulo` (`id_modulo`, `ubicacion`, `estado`) VALUES
	(1, 'M01', 'DISPONIBLE'),
	(2, 'M02', 'DISPONIBLE'),
	(3, 'M03', 'DISPONIBLE'),
	(4, 'M04', 'DISPONIBLE'),
	(5, 'M05', 'DISPONIBLE'),
	(6, 'M06', 'DISPONIBLE'),
	(7, 'M07', 'DISPONIBLE');
-- Dumping structure for table parkingsure.vehiculo
DROP TABLE IF EXISTS `vehiculo`;
CREATE TABLE IF NOT EXISTS `vehiculo` (
  `placa` varchar(8) NOT NULL,
  `id_cliente` int NOT NULL,
  `marca` varchar(30) DEFAULT NULL,
  `modelo` varchar(30) DEFAULT NULL,
  `anio` int DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`placa`),
  KEY `fk_vehiculo_cliente` (`id_cliente`),
  CONSTRAINT `fk_vehiculo_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `vehiculo` (`placa`, `id_cliente`, `marca`, `modelo`, `anio`, `color`) VALUES
	('ABC123', 1, 'Toyota', 'Corolla', 2020, 'Blanco'),
	('GHI654', 5, 'Renault', 'Sandero', 2019, 'Gris'),
	('JKL987', 1, 'Mazda', '3', 2023, 'Plateado'),
	('MNO321', 2, 'Yamaha', 'MT-03', 2023, 'Rojo'),
	('MOT001', 4, 'Honda', 'CB190R', 2022, 'Azul'),
	('PLT456', 3, 'Chevrolet', 'Spark GT', 2021, 'Rojo'),
	('PQR654', 3, 'Hyundai', 'Accent', 2020, 'Negro'),
	('XYZ789', 2, 'Bajaj', 'Pulsar 200', 2022, 'Negro');
-- Dumping structure for table parkingsure.tipo_servicio
DROP TABLE IF EXISTS `tipo_servicio`;
CREATE TABLE IF NOT EXISTS `tipo_servicio` (
  `id_tipo_servicio` int NOT NULL AUTO_INCREMENT,
  `nombre_tipo_servicio` varchar(100) NOT NULL,
  `tarifa` decimal(10,2) NOT NULL,
  `estado` varchar(20) DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_tipo_servicio`),
  CONSTRAINT `tipo_servicio_chk_1` CHECK ((`estado` in (_utf8mb4'ACTIVO',_utf8mb4'INACTIVO')))
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Dumping data for table parkingsure.tipo_servicio: ~5 rows (approximately)
INSERT INTO `tipo_servicio` (`id_tipo_servicio`, `nombre_tipo_servicio`, `tarifa`, `estado`) VALUES
	(1, 'Automóvil', 3000.00, 'ACTIVO'),
	(2, 'Motocicleta', 2000.00, 'ACTIVO'),
	(3, 'Camión', 6000.00, 'ACTIVO'),
	(4, 'Bus', 5000.00, 'INACTIVO'),
	(5, 'Van', 4000.00, 'ACTIVO');
-- Dumping structure for table parkingsure.entrada
DROP TABLE IF EXISTS `entrada`;
CREATE TABLE IF NOT EXISTS `entrada` (
  `id_entrada` int NOT NULL AUTO_INCREMENT,
  `placa` varchar(8) NOT NULL,
  `id_modulo` int NOT NULL,
  `id_personal` int NOT NULL,
  `id_tipo_servicio` int NOT NULL,
  `fecha_hora_entrada` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(20) DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_entrada`),
  KEY `fk_entrada_vehiculo` (`placa`),
  KEY `fk_entrada_modulo` (`id_modulo`),
  KEY `fk_entrada_personal` (`id_personal`),
  KEY `fk_entrada_tipo_servicio` (`id_tipo_servicio`),
  CONSTRAINT `fk_entrada_modulo` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_entrada_personal` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_entrada_tipo_servicio` FOREIGN KEY (`id_tipo_servicio`) REFERENCES `tipo_servicio` (`id_tipo_servicio`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `fk_entrada_vehiculo` FOREIGN KEY (`placa`) REFERENCES `vehiculo` (`placa`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `entrada_chk_1` CHECK ((`estado` in (_utf8mb4'ACTIVO',_utf8mb4'FINALIZADO')))
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table parkingsure.entrada: ~22 rows (approximately)
INSERT INTO `entrada` (`id_entrada`, `placa`, `id_modulo`, `id_personal`, `id_tipo_servicio`, `fecha_hora_entrada`, `estado`) VALUES
	(1, 'ABC123', 1, 1, 1, '2026-04-23 07:58:59', 'FINALIZADO'),
	(2, 'PLT456', 2, 1, 1, '2026-04-23 07:58:59', 'FINALIZADO'),
	(3, 'MOT001', 4, 1, 2, '2026-04-23 07:58:59', 'FINALIZADO'),
	(4, 'GHI654', 1, 1, 1, '2026-04-23 14:30:20', 'FINALIZADO'),
	(5, 'ABC123', 1, 1, 1, '2026-04-30 08:20:01', 'FINALIZADO'),
	(6, 'ABC123', 1, 1, 1, '2026-04-30 09:14:29', 'FINALIZADO'),
	(7, 'ABC123', 1, 1, 1, '2026-04-30 11:25:38', 'FINALIZADO'),
	(8, 'ABC123', 1, 1, 1, '2026-04-30 13:06:24', 'FINALIZADO'),
	(9, 'ABC123', 7, 1, 1, '2026-04-30 13:19:53', 'FINALIZADO'),
	(10, 'ABC123', 1, 1, 1, '2026-05-04 12:00:37', 'FINALIZADO'),
	(11, 'ABC123', 1, 1, 1, '2026-05-04 16:53:46', 'FINALIZADO'),
	(12, 'ABC123', 7, 1, 2, '2026-05-08 08:31:21', 'FINALIZADO'),
	(16, 'ABC123', 1, 1, 1, '2026-05-09 05:24:09', 'FINALIZADO'),
	(17, 'JKL987', 2, 1, 2, '2026-05-09 03:24:09', 'FINALIZADO'),
	(18, 'MNO321', 3, 1, 3, '2026-05-09 01:24:09', 'FINALIZADO'),
	(19, 'ABC123', 1, 1, 1, '2026-05-09 06:57:40', 'FINALIZADO'),
	(20, 'ABC123', 3, 1, 1, '2026-05-09 08:19:36', 'FINALIZADO'),
	(21, 'ABC123', 2, 1, 1, '2026-05-09 08:21:45', 'FINALIZADO'),
	(22, 'ABC123', 3, 1, 1, '2026-05-09 12:26:08', 'FINALIZADO'),
	(23, 'ABC123', 4, 1, 1, '2026-05-10 23:16:39', 'FINALIZADO'),
	(24, 'ABC123', 6, 1, 1, '2026-05-12 18:56:59', 'FINALIZADO'),
	(25, 'GHI654', 2, 1, 1, '2026-05-12 19:39:58', 'FINALIZADO');



-- Dumping structure for table parkingsure.salida
DROP TABLE IF EXISTS `salida`;
CREATE TABLE IF NOT EXISTS `salida` (
  `id_salida` int NOT NULL AUTO_INCREMENT,
  `id_entrada` int NOT NULL,
  `fecha_hora_salida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_salida`),
  UNIQUE KEY `uq_salida_entrada` (`id_entrada`),
  CONSTRAINT `fk_salida_entrada` FOREIGN KEY (`id_entrada`) REFERENCES `entrada` (`id_entrada`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table parkingsure.salida: ~22 rows (approximately)
INSERT INTO `salida` (`id_salida`, `id_entrada`, `fecha_hora_salida`) VALUES
	(1, 1, '2026-04-23 07:58:59'),
	(6, 4, '2026-04-23 19:01:48'),
	(9, 2, '2026-04-24 20:29:27'),
	(10, 3, '2026-04-24 20:30:01'),
	(11, 5, '2026-04-30 08:20:07'),
	(12, 6, '2026-04-30 09:14:36'),
	(13, 7, '2026-04-30 11:25:45'),
	(14, 8, '2026-04-30 13:06:41'),
	(15, 9, '2026-04-30 13:19:59'),
	(16, 10, '2026-05-04 12:00:45'),
	(17, 11, '2026-05-04 16:53:51'),
	(27, 12, '2026-05-08 11:51:50'),
	(31, 16, '2026-05-09 06:24:09'),
	(32, 17, '2026-05-09 04:24:09'),
	(33, 18, '2026-05-09 02:24:09'),
	(35, 19, '2026-05-09 06:57:45'),
	(36, 20, '2026-05-09 08:19:51'),
	(37, 21, '2026-05-09 08:23:29'),
	(38, 22, '2026-05-10 13:15:53'),
	(39, 23, '2026-05-10 23:16:42'),
	(40, 24, '2026-05-12 19:33:02'),
	(41, 25, '2026-05-12 19:40:14');

-- Dumping structure for table parkingsure.factura
DROP TABLE IF EXISTS `factura`;
CREATE TABLE IF NOT EXISTS `factura` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `id_salida` int DEFAULT NULL,
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `monto_total` decimal(10,2) DEFAULT '0.00',
  `metodo_pago` varchar(20) DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `uq_factura_salida` (`id_salida`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- Dumping data for table parkingsure.factura: ~22 rows (approximately)
INSERT INTO `factura` (`id_factura`, `id_salida`, `fecha_emision`, `monto_total`, `metodo_pago`, `estado_pago`) VALUES
	(2, 1, '2026-04-23 07:58:59', 3000.00, 'EFECTIVO', 'PAGADA'),
	(3, 6, '2026-04-23 19:01:48', 15000.00, 'TRANSFERENCIA', 'PAGADA'),
	(4, 9, '2026-04-24 20:29:27', 111000.00, 'EFECTIVO', 'PAGADA'),
	(5, 10, '2026-04-24 20:30:01', 74000.00, 'EFECTIVO', 'PAGADA'),
	(6, 11, '2026-04-30 08:20:07', 3000.00, 'EFECTIVO', 'PAGADA'),
	(7, 12, '2026-04-30 09:14:36', 3000.00, 'EFECTIVO', 'PAGADA'),
	(8, 13, '2026-04-30 11:25:45', 3000.00, 'TRANSFERENCIA', 'PAGADA'),
	(9, 14, '2026-04-30 13:06:41', 3000.00, 'TRANSFERENCIA', 'PAGADA'),
	(10, 15, '2026-04-30 13:19:59', 3000.00, 'TRANSFERENCIA', 'PAGADA'),
	(11, 16, '2026-05-04 12:00:45', 3000.00, 'EFECTIVO', 'PAGADA'),
	(12, 17, '2026-05-04 16:53:51', 3000.00, 'TRANSFERENCIA', 'PAGADA'),
	(13, 27, '2026-05-08 11:51:50', 8000.00, 'EFECTIVO', 'PAGADA'),
	(14, 31, '2026-05-09 06:24:09', 3000.00, 'TRANSFERENCIA', 'PAGADA'),
	(15, 32, '2026-05-09 04:24:09', 2000.00, 'EFECTIVO', 'PAGADA'),
	(16, 33, '2026-05-09 02:24:09', 6000.00, 'TRANSFERENCIA', 'PAGADA'),
	(17, 35, '2026-05-09 06:57:45', 3000.00, 'EFECTIVO', 'PAGADA'),
	(18, 36, '2026-05-09 08:19:51', 15000.00, 'EFECTIVO', 'PAGADA'),
	(19, 37, '2026-05-09 08:23:29', 18000.00, 'EFECTIVO', 'PAGADA'),
	(20, 38, '2026-05-10 13:15:53', 75000.00, 'EFECTIVO', 'PAGADA'),
	(21, 39, '2026-05-10 23:16:42', 3000.00, 'EFECTIVO', 'PAGADA'),
	(22, 40, '2026-05-12 19:33:02', 3000.00, 'EFECTIVO', 'PAGADA'),
	(23, 41, '2026-05-12 19:40:14', 3000.00, 'TRANSFERENCIA', 'PAGADA');








-- Dumping data for table parkingsure.vehiculo: ~8 rows (approximately)


/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
