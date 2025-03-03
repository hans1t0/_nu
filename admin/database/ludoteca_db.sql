# ************************************************************
# Sequel Ace SQL dump
# Versión 20087
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Equipo: localhost (MySQL 5.7.39)
# Base de datos: ludoteca_db
# Tiempo de generación: 2025-03-03 20:22:30 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla admins
# ------------------------------------------------------------

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`)
VALUES
	(1,'admin','$2y$10$XkL3FHn.L9LfFBgPZ8XB5.uPpI.JV9AY9JEcr8pYl3x7Szm3V2jLS','2025-02-07 22:19:29');

/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla alumno_tutor
# ------------------------------------------------------------

DROP TABLE IF EXISTS `alumno_tutor`;

CREATE TABLE `alumno_tutor` (
  `alumno_id` int(11) NOT NULL,
  `tutor_id` int(11) NOT NULL,
  `relacion` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`alumno_id`,`tutor_id`),
  KEY `tutor_id` (`tutor_id`),
  CONSTRAINT `alumno_tutor_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `alumno_tutor_ibfk_2` FOREIGN KEY (`tutor_id`) REFERENCES `tutores` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `alumno_tutor` WRITE;
/*!40000 ALTER TABLE `alumno_tutor` DISABLE KEYS */;

INSERT INTO `alumno_tutor` (`alumno_id`, `tutor_id`, `relacion`)
VALUES
	(1,1,''),
	(2,2,''),
	(3,2,''),
	(4,4,'tutor'),
	(5,4,'tutor'),
	(6,5,'tutor');

/*!40000 ALTER TABLE `alumno_tutor` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla alumnos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `alumnos`;

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `centro_id` int(11) DEFAULT NULL,
  `curso` varchar(20) DEFAULT NULL,
  `alergias` text,
  `medicacion` text,
  `observaciones` text,
  `fecha_alta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `centro_id` (`centro_id`),
  CONSTRAINT `alumnos_ibfk_1` FOREIGN KEY (`centro_id`) REFERENCES `centros` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

LOCK TABLES `alumnos` WRITE;
/*!40000 ALTER TABLE `alumnos` DISABLE KEYS */;

INSERT INTO `alumnos` (`id`, `nombre`, `apellidos`, `fecha_nacimiento`, `centro_id`, `curso`, `alergias`, `medicacion`, `observaciones`, `fecha_alta`, `activo`)
VALUES
	(1,'lucas','cok','2015-01-16',1,'4PRI','','','','2025-02-07 21:40:09',1),
	(2,'jorge','pig','2011-11-11',2,'1INF','','','','2025-02-07 21:44:03',1),
	(3,'pepa','pig','2012-12-12',1,'2INF','','','','2025-02-07 21:44:03',1),
	(4,'cain','dios','2011-11-11',2,'1INF','','','','2025-02-07 21:52:47',1),
	(5,'abel','dios','0001-01-01',1,'1INF','','','','2025-02-07 21:52:47',1),
	(6,'Mia','cok','2015-01-16',2,'1PRI','','','','2025-02-07 22:00:31',1);

/*!40000 ALTER TABLE `alumnos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla asistencia
# ------------------------------------------------------------

DROP TABLE IF EXISTS `asistencia`;

CREATE TABLE `asistencia` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inscripcion_id` int(11) DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time NOT NULL,
  `observaciones` text,
  PRIMARY KEY (`id`),
  KEY `inscripcion_id` (`inscripcion_id`),
  KEY `idx_fecha_inscripcion` (`fecha`,`inscripcion_id`),
  CONSTRAINT `asistencia_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

LOCK TABLES `asistencia` WRITE;
/*!40000 ALTER TABLE `asistencia` DISABLE KEYS */;

INSERT INTO `asistencia` (`id`, `inscripcion_id`, `fecha`, `hora_entrada`, `hora_salida`, `observaciones`)
VALUES
	(19,1,'2025-02-07',NULL,'17:00:00',NULL),
	(20,5,'2025-02-07',NULL,'17:00:00',NULL),
	(21,3,'2025-02-07',NULL,'17:00:00',NULL),
	(22,6,'2025-02-07',NULL,'16:00:00',NULL),
	(23,5,'2025-07-01','15:00:00','17:00:00',NULL),
	(24,5,'2025-07-02','15:00:00','17:00:00',NULL),
	(25,3,'2025-07-02','15:00:00','17:00:00',NULL),
	(26,1,'2025-07-02','15:00:00','17:00:00',NULL);

/*!40000 ALTER TABLE `asistencia` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla centros
# ------------------------------------------------------------

DROP TABLE IF EXISTS `centros`;

CREATE TABLE `centros` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

LOCK TABLES `centros` WRITE;
/*!40000 ALTER TABLE `centros` DISABLE KEYS */;

INSERT INTO `centros` (`id`, `nombre`, `codigo`, `activo`)
VALUES
	(1,'CEIP Almadraba','ALM',1),
	(2,'CEIP Costa Blanca','CBL',1),
	(3,'CEIP Faro','FAR',1),
	(4,'CEIP Voramar','VOR',1);

/*!40000 ALTER TABLE `centros` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla horarios
# ------------------------------------------------------------

DROP TABLE IF EXISTS `horarios`;

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

LOCK TABLES `horarios` WRITE;
/*!40000 ALTER TABLE `horarios` DISABLE KEYS */;

INSERT INTO `horarios` (`id`, `hora_inicio`, `hora_fin`, `precio`, `descripcion`)
VALUES
	(1,'15:00:00','16:00:00',25.00,'Horario hasta las 16h'),
	(2,'15:00:00','17:00:00',35.00,'Horario hasta las 17h');

/*!40000 ALTER TABLE `horarios` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla inscripciones
# ------------------------------------------------------------

DROP TABLE IF EXISTS `inscripciones`;

CREATE TABLE `inscripciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alumno_id` int(11) DEFAULT NULL,
  `horario_id` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('activa','cancelada','finalizada') DEFAULT 'activa',
  PRIMARY KEY (`id`),
  KEY `alumno_id` (`alumno_id`),
  KEY `horario_id` (`horario_id`),
  CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`),
  CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`horario_id`) REFERENCES `horarios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

LOCK TABLES `inscripciones` WRITE;
/*!40000 ALTER TABLE `inscripciones` DISABLE KEYS */;

INSERT INTO `inscripciones` (`id`, `alumno_id`, `horario_id`, `fecha_inicio`, `fecha_fin`, `estado`)
VALUES
	(1,1,2,'2025-02-07','2026-02-07','activa'),
	(2,2,2,'2025-02-07','2026-02-07','activa'),
	(3,3,2,'2025-02-07','2026-02-07','activa'),
	(4,4,2,'2025-02-07','2026-02-07','activa'),
	(5,5,2,'2025-02-07','2026-02-07','activa'),
	(6,6,1,'2025-02-07','2026-02-07','activa');

/*!40000 ALTER TABLE `inscripciones` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla pagos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `pagos`;

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `inscripcion_id` int(11) DEFAULT NULL,
  `fecha_pago` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `monto` decimal(6,2) NOT NULL,
  `metodo_pago` enum('efectivo','transferencia','tarjeta') DEFAULT NULL,
  `estado` enum('pendiente','pagado','cancelado') DEFAULT 'pendiente',
  PRIMARY KEY (`id`),
  KEY `inscripcion_id` (`inscripcion_id`),
  CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`inscripcion_id`) REFERENCES `inscripciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;

INSERT INTO `pagos` (`id`, `inscripcion_id`, `fecha_pago`, `monto`, `metodo_pago`, `estado`)
VALUES
	(1,6,'2025-02-07 22:00:31',25.00,'transferencia','pendiente');

/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla tutores
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tutores`;

CREATE TABLE `tutores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `telefono2` varchar(15) DEFAULT NULL,
  `iban` varchar(24) DEFAULT NULL,
  `forma_pago` enum('domiciliacion','transferencia','coordinador') NOT NULL,
  `metodo_pago` varchar(20) DEFAULT NULL,
  `observaciones` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

LOCK TABLES `tutores` WRITE;
/*!40000 ALTER TABLE `tutores` DISABLE KEYS */;

INSERT INTO `tutores` (`id`, `nombre`, `dni`, `email`, `telefono`, `telefono2`, `iban`, `forma_pago`, `metodo_pago`, `observaciones`, `fecha_registro`)
VALUES
	(1,'hans cok','x7242535L','hans1to@me.com','677283758','',NULL,'coordinador',NULL,'','2025-02-07 21:40:09'),
	(2,'Papa Pig','x7242535p','hans_cok@hotmail.com','677283758','',NULL,'transferencia',NULL,'','2025-02-07 21:44:03'),
	(4,'Eva','12345678e','hcdesign14@gmail.com','677283758','',NULL,'transferencia',NULL,'','2025-02-07 21:52:47'),
	(5,'gema maria','x7242535c','amethyst.gema@gmail.com','677283758','',NULL,'transferencia',NULL,'','2025-02-07 22:00:31');

/*!40000 ALTER TABLE `tutores` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
