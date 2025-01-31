-- Crear base de datos
CREATE DATABASE IF NOT EXISTS registro_familiar;
USE registro_familiar;

-- Crear tabla padres
CREATE TABLE padres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    dni CHAR(9) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    telefono CHAR(9) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dni (dni),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Crear tabla hijos
CREATE TABLE hijos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_padre INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_colegio INT NOT NULL,
    id_curso INT NOT NULL,
    FOREIGN KEY (id_padre) REFERENCES padres(id) ON DELETE CASCADE,
    FOREIGN KEY (id_colegio) REFERENCES colegios(id),
    FOREIGN KEY (id_curso) REFERENCES cursos(id),
    INDEX idx_id_padre (id_padre),
    INDEX idx_colegio_curso (id_colegio, id_curso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de colegios
CREATE TABLE colegios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    direccion VARCHAR(255),
    telefono CHAR(9),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de cursos
CREATE TABLE cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    nivel ENUM('Primaria', 'Secundaria') NOT NULL,
    grado TINYINT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nivel_grado (nivel, grado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modificar tabla cursos para incluir Infantil
ALTER TABLE cursos 
MODIFY COLUMN nivel ENUM('Infantil', 'Primaria') NOT NULL;

-- Deshabilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 0;

-- Limpiar datos existentes de cursos
TRUNCATE TABLE cursos;

-- Habilitar verificación de claves foráneas
SET FOREIGN_KEY_CHECKS = 1;

-- Insertar datos actualizados de cursos
INSERT INTO cursos (nombre, nivel, grado) VALUES
-- Infantil
('1° Infantil', 'Infantil', 1),
('2° Infantil', 'Infantil', 2),
('3° Infantil', 'Infantil', 3),
-- Primaria
('1° Primaria', 'Primaria', 1),
('2° Primaria', 'Primaria', 2),
('3° Primaria', 'Primaria', 3),
('4° Primaria', 'Primaria', 4),
('5° Primaria', 'Primaria', 5),
('6° Primaria', 'Primaria', 6);

-- Insertar datos base de colegios
INSERT INTO colegios (nombre) VALUES
('Albufereta'),
('Almadraba'),
('Condomina'),
('Costa Blanca'),
('Faro'),
('Mediterraneo'),
('Voramar');
