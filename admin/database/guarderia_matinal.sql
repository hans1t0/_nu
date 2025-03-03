# ************************************************************
# Sequel Ace SQL dump
# Versión 20087
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Equipo: localhost (MySQL 5.7.39)
# Base de datos: guarderia_matinal
# Tiempo de generación: 2025-03-03 18:29:26 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE='NO_AUTO_VALUE_ON_ZERO', SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Volcado de tabla asistencias
# ------------------------------------------------------------

DROP TABLE IF EXISTS `asistencias`;

CREATE TABLE `asistencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hijo_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `asistio` tinyint(1) DEFAULT '0',
  `desayuno` tinyint(1) DEFAULT '0',
  `hora_entrada` time DEFAULT NULL,
  `observaciones` text,
  `creado_por` varchar(50) DEFAULT NULL,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_asistencia` (`hijo_id`,`fecha`),
  KEY `idx_fecha` (`fecha`),
  CONSTRAINT `asistencias_ibfk_1` FOREIGN KEY (`hijo_id`) REFERENCES `hijos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

LOCK TABLES `asistencias` WRITE;
/*!40000 ALTER TABLE `asistencias` DISABLE KEYS */;

INSERT INTO `asistencias` (`id`, `hijo_id`, `fecha`, `asistio`, `desayuno`, `hora_entrada`, `observaciones`, `creado_por`, `fecha_registro`)
VALUES
	(1,1,'2025-02-07',1,1,NULL,NULL,'sistema','2025-02-07 23:54:20'),
	(2,4,'2025-02-07',1,1,NULL,NULL,'sistema','2025-02-07 23:54:20'),
	(3,2,'2025-02-07',1,0,'07:30:00','','sistema','2025-02-07 23:56:47'),
	(4,3,'2025-02-07',1,0,'07:30:00','','sistema','2025-02-07 23:56:47'),
	(7,4,'2025-02-06',1,0,NULL,NULL,'sistema','2025-02-08 00:04:45'),
	(8,1,'2025-02-06',1,1,NULL,NULL,'sistema','2025-02-08 00:04:45'),
	(11,4,'2025-02-08',1,0,NULL,NULL,'sistema','2025-02-08 01:14:11'),
	(12,1,'2025-02-08',1,0,NULL,NULL,'sistema','2025-02-08 01:14:11'),
	(13,4,'2025-02-15',1,0,NULL,NULL,'sistema','2025-02-15 11:21:33'),
	(14,1,'2025-02-15',1,1,NULL,NULL,'sistema','2025-02-15 11:21:33');

/*!40000 ALTER TABLE `asistencias` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla colegios
# ------------------------------------------------------------

DROP TABLE IF EXISTS `colegios`;

CREATE TABLE `colegios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(10) NOT NULL,
  `tiene_desayuno` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_codigo` (`codigo`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

LOCK TABLES `colegios` WRITE;
/*!40000 ALTER TABLE `colegios` DISABLE KEYS */;

INSERT INTO `colegios` (`id`, `nombre`, `codigo`, `tiene_desayuno`)
VALUES
	(1,'CEIP Almadraba','ALMADRABA',1),
	(2,'CEIP Costa Blanca','COSTA',0),
	(3,'CEIP Faro','FARO',0),
	(4,'CEIP Voramar','VORAMAR',0);

/*!40000 ALTER TABLE `colegios` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla hijos
# ------------------------------------------------------------

DROP TABLE IF EXISTS `hijos`;

CREATE TABLE `hijos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `responsable_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `colegio_id` int(11) NOT NULL,
  `curso` enum('1INF','2INF','3INF','1PRIM','2PRIM','3PRIM','4PRIM','5PRIM','6PRIM') NOT NULL,
  `hora_entrada` time NOT NULL,
  `desayuno` tinyint(1) DEFAULT '0',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_responsable` (`responsable_id`),
  KEY `idx_colegio` (`colegio_id`),
  CONSTRAINT `hijos_ibfk_1` FOREIGN KEY (`responsable_id`) REFERENCES `responsables` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hijos_ibfk_2` FOREIGN KEY (`colegio_id`) REFERENCES `colegios` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

LOCK TABLES `hijos` WRITE;
/*!40000 ALTER TABLE `hijos` DISABLE KEYS */;

INSERT INTO `hijos` (`id`, `responsable_id`, `nombre`, `fecha_nacimiento`, `colegio_id`, `curso`, `hora_entrada`, `desayuno`, `fecha_registro`)
VALUES
	(1,1,'lucas cok','2015-01-16',1,'4PRIM','08:30:00',1,'2025-02-07 12:00:56'),
	(2,3,'Jorge Pig','2022-12-12',2,'1INF','07:30:00',0,'2025-02-07 12:06:05'),
	(3,3,'Pepa Pig','2011-11-11',2,'1INF','07:30:00',0,'2025-02-07 12:06:05'),
	(4,5,'mia cok','1212-12-12',1,'1INF','07:30:00',0,'2025-02-07 23:05:43');

/*!40000 ALTER TABLE `hijos` ENABLE KEYS */;
UNLOCK TABLES;


# Volcado de tabla responsables
# ------------------------------------------------------------

DROP TABLE IF EXISTS `responsables`;

CREATE TABLE `responsables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `observaciones` text,
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `forma_pago` enum('DOMICILIACION','TRANSFERENCIA','COORDINADOR') NOT NULL,
  `iban` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`),
  KEY `idx_dni` (`dni`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

LOCK TABLES `responsables` WRITE;
/*!40000 ALTER TABLE `responsables` DISABLE KEYS */;

INSERT INTO `responsables` (`id`, `nombre`, `dni`, `email`, `telefono`, `observaciones`, `fecha_registro`, `forma_pago`, `iban`)
VALUES
	(1,'hans','x7242535L','hans1to@me.com','677283758','llega siempre tarde','2025-02-07 12:00:56','DOMICILIACION',NULL),
	(3,'Papa Pig','x7242535z','hans_cok@hotmail.com','677283758','comen como cerdos','2025-02-07 12:06:05','DOMICILIACION',NULL),
	(5,'Eva','x7242535c','amethyst.gema@gmail.com','677283758','nada','2025-02-07 23:05:43','TRANSFERENCIA',NULL);

/*!40000 ALTER TABLE `responsables` ENABLE KEYS */;
UNLOCK TABLES;




# Volcado de vista v_inscripciones
# ------------------------------------------------------------

DROP TABLE IF EXISTS `v_inscripciones`; DROP VIEW IF EXISTS `v_inscripciones`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_inscripciones`
AS SELECT
   `r`.`nombre` AS `responsable`,
   `r`.`dni` AS `dni`,
   `r`.`telefono` AS `telefono`,
   `h`.`nombre` AS `hijo`,
   `h`.`fecha_nacimiento` AS `fecha_nacimiento`,
   `c`.`nombre` AS `colegio`,
   `h`.`curso` AS `curso`,
   `h`.`hora_entrada` AS `hora_entrada`,
   `h`.`desayuno` AS `desayuno`
FROM ((`responsables` `r` join `hijos` `h` on((`h`.`responsable_id` = `r`.`id`))) join `colegios` `c` on((`h`.`colegio_id` = `c`.`id`)));


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
