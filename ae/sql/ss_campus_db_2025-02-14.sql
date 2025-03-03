# ************************************************************
# Sequel Ace SQL dump
# Versión 20080
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Equipo: localhost (MySQL 5.7.39)
# Base de datos: ss_campus_db
# Tiempo de generación: 2025-02-14 17:19:13 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla consentimientos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `consentimientos`;

CREATE TABLE `consentimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `padre_id` int(11) NOT NULL,
  `tipo` enum('datos','imagen') COLLATE utf8mb4_unicode_ci NOT NULL,
  `aceptado` tinyint(1) DEFAULT '1',
  `fecha` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `padre_id` (`padre_id`),
  CONSTRAINT `consentimientos_ibfk_1` FOREIGN KEY (`padre_id`) REFERENCES `padres` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `consentimientos` WRITE;
/*!40000 ALTER TABLE `consentimientos` DISABLE KEYS */;

INSERT INTO `consentimientos` (`id`, `padre_id`, `tipo`, `aceptado`, `fecha`)
VALUES
	(1,2,'datos',1,'2025-01-27 11:44:42'),
	(2,3,'datos',1,'2025-01-27 12:39:50'),
	(3,5,'datos',1,'2025-01-27 18:33:44'),
	(4,6,'datos',1,'2025-01-27 18:44:53'),
	(5,7,'datos',1,'2025-01-27 18:53:14'),
	(6,8,'datos',1,'2025-01-30 10:58:24'),
	(7,8,'imagen',1,'2025-01-30 10:58:24');

/*!40000 ALTER TABLE `consentimientos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla descuentos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `descuentos`;

CREATE TABLE `descuentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `jugador_id` int(11) NOT NULL,
  `descuento` decimal(5,2) NOT NULL,
  `tiene_hermanos` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `jugador_id` (`jugador_id`),
  KEY `idx_descuento_valor` (`descuento`),
  CONSTRAINT `descuentos_ibfk_1` FOREIGN KEY (`jugador_id`) REFERENCES `jugadores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `descuentos` WRITE;
/*!40000 ALTER TABLE `descuentos` DISABLE KEYS */;

INSERT INTO `descuentos` (`id`, `jugador_id`, `descuento`, `tiene_hermanos`)
VALUES
	(1,1,0.00,0),
	(2,2,5.00,1),
	(3,3,0.00,0),
	(4,5,0.00,0),
	(5,6,5.00,1),
	(6,7,10.00,1),
	(7,8,0.00,0),
	(8,9,0.00,0),
	(9,10,0.00,0);

/*!40000 ALTER TABLE `descuentos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla jugadores
# ------------------------------------------------------------

DROP TABLE IF EXISTS `jugadores`;

CREATE TABLE `jugadores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `padre_id` int(11) NOT NULL,
  `nombre_completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `sexo` enum('H','M') COLLATE utf8mb4_unicode_ci NOT NULL,
  `grupo` enum('Querubin','Prebenjamin','Benjamin','Alevin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `modalidad` enum('RPSJ','NO_RPSJ') COLLATE utf8mb4_unicode_ci NOT NULL,
  `demarcacion` enum('jugador','portero') COLLATE utf8mb4_unicode_ci NOT NULL,
  `lesiones` text COLLATE utf8mb4_unicode_ci,
  `jugador_numero` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `padre_id` (`padre_id`),
  KEY `idx_jugador_grupo` (`grupo`),
  KEY `idx_jugador_modalidad` (`modalidad`),
  KEY `idx_jugador_grupo_modalidad` (`grupo`,`modalidad`),
  KEY `idx_jugador_nombre` (`nombre_completo`),
  KEY `idx_jugador_fecha_nacimiento` (`fecha_nacimiento`),
  CONSTRAINT `jugadores_ibfk_1` FOREIGN KEY (`padre_id`) REFERENCES `padres` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `jugadores` WRITE;
/*!40000 ALTER TABLE `jugadores` DISABLE KEYS */;

INSERT INTO `jugadores` (`id`, `padre_id`, `nombre_completo`, `fecha_nacimiento`, `sexo`, `grupo`, `modalidad`, `demarcacion`, `lesiones`, `jugador_numero`)
VALUES
	(1,2,'Lucas Cok','2015-01-16','H','Benjamin','RPSJ','jugador','nada',1),
	(2,2,'Mia Cok','2019-12-12','M','Querubin','RPSJ','portero','naha',2),
	(3,3,'Luca Siro','2025-01-16','H','Alevin','RPSJ','jugador','',0),
	(5,5,'Abel','2024-12-30','H','Querubin','RPSJ','jugador','',1),
	(6,5,'Cain','2024-12-30','H','Querubin','RPSJ','jugador','',2),
	(7,5,'Zeus','2024-12-31','H','Querubin','RPSJ','jugador','',3),
	(8,6,'Mercurio','2025-01-06','H','Alevin','NO_RPSJ','portero','',1),
	(9,7,'Antonio Ruedas','2003-12-12','H','Alevin','NO_RPSJ','portero','',1),
	(10,8,'julito','2024-12-30','H','Querubin','RPSJ','jugador','',1),
	(11,13,'Luisito perrito','1201-12-12','H','Querubin','RPSJ','jugador',NULL,1),
	(12,14,'Luisito perrito','2012-12-12','H','Benjamin','RPSJ','jugador',NULL,1),
	(13,14,'Mias','2011-11-11','M','Querubin','RPSJ','jugador',NULL,2),
	(14,16,'Luisito perrito','2012-12-12','H','Benjamin','RPSJ','jugador',NULL,1),
	(15,16,'Mias','2011-11-11','M','Prebenjamin','RPSJ','jugador',NULL,2);

/*!40000 ALTER TABLE `jugadores` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla padres
# ------------------------------------------------------------

DROP TABLE IF EXISTS `padres`;

CREATE TABLE `padres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dni` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metodo_pago` enum('transferencia','coordinador') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cuenta_bancaria` varchar(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_padre_dni` (`dni`),
  KEY `idx_padre_email_dni` (`email`,`dni`),
  KEY `idx_padre_metodo_pago` (`metodo_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

LOCK TABLES `padres` WRITE;
/*!40000 ALTER TABLE `padres` DISABLE KEYS */;

INSERT INTO `padres` (`id`, `nombre`, `dni`, `telefono`, `email`, `metodo_pago`, `cuenta_bancaria`, `fecha_registro`)
VALUES
	(2,'Hans Cok','12345678Z','677283758','hans1to@me.com','coordinador',NULL,'2025-01-27 11:44:42'),
	(3,'Gema maria','12345678Z','647729651','gema@a.es','transferencia',NULL,'2025-01-27 12:39:50'),
	(5,'Pepe Pardo','12345678C','677283758','hans1to@me.com','transferencia',NULL,'2025-01-27 18:33:44'),
	(6,'Pepe Pardo2','12345678C','677283758','hans1to@me.com','transferencia',NULL,'2025-01-27 18:44:53'),
	(7,'Antonio Ruedas','12345678X','647729651','hans1to@me.com','coordinador',NULL,'2025-01-27 18:53:14'),
	(8,'julian muñoz','12345678H','677283758','h@julian.es','transferencia',NULL,'2025-01-30 10:58:24'),
	(9,'Enrico Peez','12345678Q','677283758','hans_cok@hotmail.com','transferencia',NULL,'2025-02-14 16:39:13'),
	(10,'Enrico Peez','12345678Q','677283758','hans_cok@hotmail.com','coordinador',NULL,'2025-02-14 16:42:30'),
	(11,'Enrico Peez','12345678Q','677283758','hans_cok@hotmail.com','transferencia',NULL,'2025-02-14 16:43:44'),
	(12,'Hans Cok','12345678Q','677283758','hans_cok@hotmail.com','transferencia',NULL,'2025-02-14 16:47:10'),
	(13,'Hans Cok','12345678Q','677123123','hans_cok@hotmail.com','transferencia',NULL,'2025-02-14 16:56:44'),
	(14,'Hans Cok','12345678Q','677123123','hans_cok@hotmail.com','transferencia',NULL,'2025-02-14 17:08:19'),
	(16,'Hans Cok','12345678Q','677123123','hans_cok@hotmail.com','transferencia',NULL,'2025-02-14 17:13:12');

/*!40000 ALTER TABLE `padres` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
