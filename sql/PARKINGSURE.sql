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
CREATE DATABASE IF NOT EXISTS `parkingsure` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `parkingsure`;

-- Dumping structure for table parkingsure.cliente
CREATE TABLE IF NOT EXISTS `cliente` (
  `id_cliente` int NOT NULL AUTO_INCREMENT,
  `cedula_users` varchar(20) NOT NULL,
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `uq_cedula_cli` (`cedula_users`),
  CONSTRAINT `fk_cliente_users` FOREIGN KEY (`cedula_users`) REFERENCES `users` (`cedula`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.cliente: ~0 rows (approximately)

-- Dumping structure for table parkingsure.entrada
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.entrada: ~0 rows (approximately)

-- Dumping structure for table parkingsure.factura
CREATE TABLE IF NOT EXISTS `factura` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `id_salida` int NOT NULL,
  `fecha_emision` datetime DEFAULT CURRENT_TIMESTAMP,
  `monto_total` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(20) DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT 'PENDIENTE',
  PRIMARY KEY (`id_factura`),
  UNIQUE KEY `uq_factura_salida` (`id_salida`),
  CONSTRAINT `fk_factura_salida` FOREIGN KEY (`id_salida`) REFERENCES `salida` (`id_salida`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `factura_chk_1` CHECK ((`metodo_pago` in (_utf8mb4'EFECTIVO',_utf8mb4'TARJETA',_utf8mb4'TRANSFERENCIA',_utf8mb4'NEQUI',NULL))),
  CONSTRAINT `factura_chk_2` CHECK ((`estado_pago` in (_utf8mb4'PENDIENTE',_utf8mb4'PAGADA')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.factura: ~0 rows (approximately)

-- Dumping structure for table parkingsure.modulo
CREATE TABLE IF NOT EXISTS `modulo` (
  `id_modulo` int NOT NULL AUTO_INCREMENT,
  `ubicacion` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT 'DISPONIBLE',
  PRIMARY KEY (`id_modulo`),
  CONSTRAINT `modulo_chk_1` CHECK ((`estado` in (_utf8mb4'DISPONIBLE',_utf8mb4'OCUPADO',_utf8mb4'MANTENIMIENTO')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.modulo: ~0 rows (approximately)

-- Dumping structure for table parkingsure.personal
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.personal: ~0 rows (approximately)
INSERT INTO `personal` (`id_personal`, `cedula_users`, `id_rol`, `usuario`, `password_hash`) VALUES
	(1, '1080182082', 1, 'cflg', '$2y$10$MSNe.zf5HvaAiT8o0dZtd.X9NtM5WU.H0k0cok0ZAeAVVPyL7J.ia');

-- Dumping structure for table parkingsure.rol
CREATE TABLE IF NOT EXISTS `rol` (
  `id_rol` int NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `uq_nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.rol: ~2 rows (approximately)
INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
	(1, 'ADMINISTRADOR'),
	(2, 'OPERADOR');

-- Dumping structure for table parkingsure.salida
CREATE TABLE IF NOT EXISTS `salida` (
  `id_salida` int NOT NULL AUTO_INCREMENT,
  `id_entrada` int NOT NULL,
  `fecha_hora_salida` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_salida`),
  UNIQUE KEY `uq_salida_entrada` (`id_entrada`),
  CONSTRAINT `fk_salida_entrada` FOREIGN KEY (`id_entrada`) REFERENCES `entrada` (`id_entrada`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.salida: ~0 rows (approximately)

-- Dumping structure for table parkingsure.tipo_servicio
CREATE TABLE IF NOT EXISTS `tipo_servicio` (
  `id_tipo_servicio` int NOT NULL AUTO_INCREMENT,
  `nombre_tipo_servicio` varchar(100) NOT NULL,
  `tarifa` decimal(10,2) NOT NULL,
  `estado` varchar(20) DEFAULT 'ACTIVO',
  PRIMARY KEY (`id_tipo_servicio`),
  CONSTRAINT `tipo_servicio_chk_1` CHECK ((`estado` in (_utf8mb4'ACTIVO',_utf8mb4'INACTIVO')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.tipo_servicio: ~0 rows (approximately)

-- Dumping structure for table parkingsure.users
CREATE TABLE IF NOT EXISTS `users` (
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`cedula`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.users: ~0 rows (approximately)
INSERT INTO `users` (`cedula`, `nombre`, `telefono`, `correo`) VALUES
	('1080182082', 'Cristian Felipe Longas', '3023012409', 'cristianf.longas@gmail.com');

-- Dumping structure for table parkingsure.vehiculo
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.vehiculo: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
