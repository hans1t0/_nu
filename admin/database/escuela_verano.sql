# ************************************************************
# Sequel Ace SQL dump
# Versión 20087
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Equipo: localhost (MySQL 5.7.39)
# Base de datos: escuela_verano
# Tiempo de generación: 2025-03-04 08:51:34 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla participantes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `participantes`;

CREATE TABLE `participantes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `responsable_id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `centro_actual` varchar(100) NOT NULL,
  `curso` varchar(10) NOT NULL,
  `alergias` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_participante_responsable` (`responsable_id`),
  CONSTRAINT `participantes_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `responsables` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `participantes` WRITE;
/*!40000 ALTER TABLE `participantes` DISABLE KEYS */;

INSERT INTO `participantes` (`id`, `responsable_id`, `nombre`, `fecha_nacimiento`, `centro_actual`, `curso`, `alergias`, `created_at`)
VALUES
	(1,1,'lucas cok','2015-01-16','ALMADRABA','4PRIM','','2025-02-08 09:27:06');

/*!40000 ALTER TABLE `participantes` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla periodos_inscritos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `periodos_inscritos`;

CREATE TABLE `periodos_inscritos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `participante_id` int(11) NOT NULL,
  `semana` varchar(20) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_periodos_participante` (`participante_id`),
  CONSTRAINT `periodos_inscritos_ibfk_1` FOREIGN KEY (`participante_id`) REFERENCES `participantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

LOCK TABLES `periodos_inscritos` WRITE;
/*!40000 ALTER TABLE `periodos_inscritos` DISABLE KEYS */;

INSERT INTO `periodos_inscritos` (`id`, `participante_id`, `semana`, `fecha_inicio`, `fecha_fin`, `created_at`)
VALUES
	(1,1,'julio1','2024-07-01','2024-07-06','2025-02-08 09:27:06'),
	(2,1,'julio2','2024-07-07','2024-07-13','2025-02-08 09:27:06'),
	(3,1,'julio3','2024-07-14','2024-07-20','2025-02-08 09:27:06'),
	(4,1,'julio4','2024-07-21','2024-07-27','2025-02-08 09:27:06'),
	(5,1,'julio5','2024-07-28','2024-07-31','2025-02-08 09:27:06');

/*!40000 ALTER TABLE `periodos_inscritos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla responsables
# ------------------------------------------------------------

DROP TABLE IF EXISTS `responsables`;

CREATE TABLE `responsables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `forma_pago` varchar(20) NOT NULL,
  `iban` varchar(34) DEFAULT NULL,
  `observaciones` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_responsable_dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `responsables` WRITE;
/*!40000 ALTER TABLE `responsables` DISABLE KEYS */;

INSERT INTO `responsables` (`id`, `nombre`, `dni`, `email`, `telefono`, `forma_pago`, `iban`, `observaciones`, `created_at`)
VALUES
	(1,'hans cok','X7242535L','hans1to@me.com','677283758','TRANSFERENCIA','','','2025-02-08 09:27:06');

/*!40000 ALTER TABLE `responsables` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla servicios_contratados
# ------------------------------------------------------------

DROP TABLE IF EXISTS `servicios_contratados`;

CREATE TABLE `servicios_contratados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `participante_id` int(11) NOT NULL,
  `socio_ampa` enum('SI','NO') NOT NULL,
  `guarderia_matinal` varchar(10) DEFAULT NULL,
  `comedor` enum('SI','NO') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_servicios_participante` (`participante_id`),
  CONSTRAINT `servicios_contratados_ibfk_1` FOREIGN KEY (`participante_id`) REFERENCES `participantes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `servicios_contratados` WRITE;
/*!40000 ALTER TABLE `servicios_contratados` DISABLE KEYS */;

INSERT INTO `servicios_contratados` (`id`, `participante_id`, `socio_ampa`, `guarderia_matinal`, `comedor`, `created_at`)
VALUES
	(1,1,'SI','7:30','SI','2025-02-08 09:27:06');

/*!40000 ALTER TABLE `servicios_contratados` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
