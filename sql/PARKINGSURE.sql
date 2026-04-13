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
  `id_personal` int NOT NULL,
  PRIMARY KEY (`id_cliente`),
  KEY `id_personal` (`id_personal`),
  CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.cliente: ~0 rows (approximately)

-- Dumping structure for table parkingsure.factura
CREATE TABLE IF NOT EXISTS `factura` (
  `id_factura` int NOT NULL AUTO_INCREMENT,
  `id_servicio` int NOT NULL,
  `fecha_emision` datetime DEFAULT NULL,
  `monto_total` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(20) DEFAULT NULL,
  `estado_pago` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_factura`),
  KEY `id_servicio` (`id_servicio`),
  CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.factura: ~0 rows (approximately)

-- Dumping structure for table parkingsure.modulo
CREATE TABLE IF NOT EXISTS `modulo` (
  `id_modulo` int NOT NULL AUTO_INCREMENT,
  `ubicacion` varchar(50) DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_modulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.modulo: ~0 rows (approximately)

-- Dumping structure for table parkingsure.personal
CREATE TABLE IF NOT EXISTS `personal` (
  `id_personal` int NOT NULL AUTO_INCREMENT,
  `rol` varchar(30) NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `correo` varchar(80) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id_personal`),
  UNIQUE KEY `cedula` (`cedula`),
  UNIQUE KEY `usuario` (`usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.personal: ~1 rows (approximately)
INSERT INTO `personal` (`id_personal`, `rol`, `cedula`, `nombre`, `telefono`, `correo`, `usuario`, `password_hash`) VALUES
	(2, 'ADMINISTRADOR', '1080182082', 'Cristian Felipe Longas', '3023012409', 'cristianf.longas@gmail.com', 'cflg', '$2y$10$MSNe.zf5HvaAiT8o0dZtd.X9NtM5WU.H0k0cok0ZAeAVVPyL7J.ia');

-- Dumping structure for table parkingsure.servicio
CREATE TABLE IF NOT EXISTS `servicio` (
  `id_servicio` int NOT NULL AUTO_INCREMENT,
  `fecha_entrada` datetime NOT NULL,
  `fecha_salida` datetime DEFAULT NULL,
  `estado` varchar(20) DEFAULT NULL,
  `placa` varchar(8) NOT NULL,
  `id_tipo_servicio` int NOT NULL,
  `id_modulo` int NOT NULL,
  `id_personal` int NOT NULL,
  PRIMARY KEY (`id_servicio`),
  KEY `placa` (`placa`),
  KEY `id_tipo_servicio` (`id_tipo_servicio`),
  KEY `id_modulo` (`id_modulo`),
  KEY `id_personal` (`id_personal`),
  CONSTRAINT `servicio_ibfk_1` FOREIGN KEY (`placa`) REFERENCES `vehiculo` (`placa`),
  CONSTRAINT `servicio_ibfk_2` FOREIGN KEY (`id_tipo_servicio`) REFERENCES `tipo_servicio` (`id_tipo_servicio`),
  CONSTRAINT `servicio_ibfk_3` FOREIGN KEY (`id_modulo`) REFERENCES `modulo` (`id_modulo`),
  CONSTRAINT `servicio_ibfk_4` FOREIGN KEY (`id_personal`) REFERENCES `personal` (`id_personal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.servicio: ~0 rows (approximately)

-- Dumping structure for table parkingsure.tipo_servicio
CREATE TABLE IF NOT EXISTS `tipo_servicio` (
  `id_tipo_servicio` int NOT NULL AUTO_INCREMENT,
  `nombre_tipo_servicio` varchar(100) NOT NULL,
  `tarifa` decimal(10,2) NOT NULL,
  `estado` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_tipo_servicio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.tipo_servicio: ~0 rows (approximately)

-- Dumping structure for table parkingsure.vehiculo
CREATE TABLE IF NOT EXISTS `vehiculo` (
  `placa` varchar(8) NOT NULL,
  `id_cliente` int NOT NULL,
  `marca` varchar(30) DEFAULT NULL,
  `modelo` varchar(30) DEFAULT NULL,
  `anio` int DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`placa`),
  KEY `id_cliente` (`id_cliente`),
  CONSTRAINT `vehiculo_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table parkingsure.vehiculo: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
