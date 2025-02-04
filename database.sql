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

-- Tabla de actividades
CREATE TABLE actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(6,2) NOT NULL,
    duracion VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Agregar campo de nivel requerido a las actividades
ALTER TABLE actividades 
ADD COLUMN nivel_requerido ENUM('Infantil', 'Primaria') NOT NULL AFTER nombre;

-- Agregar columnas para grado mínimo y máximo en actividades
ALTER TABLE actividades 
ADD COLUMN grado_minimo TINYINT NOT NULL DEFAULT 1 AFTER nivel_requerido,
ADD COLUMN grado_maximo TINYINT NOT NULL DEFAULT 6 AFTER grado_minimo;

-- Actualizar las actividades existentes con sus niveles
UPDATE actividades SET nivel_requerido = 
    CASE nombre
        WHEN 'Fútbol' THEN 'Primaria'
        WHEN 'Baloncesto' THEN 'Primaria'
        WHEN 'Voleibol' THEN 'Primaria'
        WHEN 'Ajedrez' THEN 'Infantil'
        WHEN 'Atletismo' THEN 'Primaria'
    END;

-- Actualizar actividades con rangos de grado específicos
UPDATE actividades SET 
    grado_minimo = CASE nombre
        WHEN 'Fútbol' THEN 3         -- 3° a 6° Primaria
        WHEN 'Baloncesto' THEN 3     -- 3° a 6° Primaria
        WHEN 'Voleibol' THEN 4       -- 4° a 6° Primaria
        WHEN 'Ajedrez' THEN 2        -- 2° Infantil a 3° Infantil
        WHEN 'Atletismo' THEN 3      -- 3° a 6° Primaria
        WHEN 'Juegos Predeportivos' THEN 1  -- Todo Infantil
        WHEN 'Psicomotricidad' THEN 1       -- 1° y 2° Infantil
    END,
    grado_maximo = CASE nombre
        WHEN 'Fútbol' THEN 6
        WHEN 'Baloncesto' THEN 6
        WHEN 'Voleibol' THEN 6
        WHEN 'Ajedrez' THEN 3
        WHEN 'Atletismo' THEN 6
        WHEN 'Juegos Predeportivos' THEN 3
        WHEN 'Psicomotricidad' THEN 2
    END;

-- Tabla de relación entre colegios y actividades
CREATE TABLE colegio_actividad (
    id_colegio INT NOT NULL,
    id_actividad INT NOT NULL,
    horario VARCHAR(100) NOT NULL,
    cupo_maximo INT NOT NULL DEFAULT 20,
    cupo_actual INT NOT NULL DEFAULT 0,
    FOREIGN KEY (id_colegio) REFERENCES colegios(id),
    FOREIGN KEY (id_actividad) REFERENCES actividades(id),
    PRIMARY KEY (id_colegio, id_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de inscripciones a actividades
CREATE TABLE inscripciones_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_hijo INT NOT NULL,
    id_colegio INT NOT NULL,
    id_actividad INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_hijo) REFERENCES hijos(id),
    FOREIGN KEY (id_colegio, id_actividad) REFERENCES colegio_actividad(id_colegio, id_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Limpiar y reinsertar datos de ejemplo
TRUNCATE TABLE inscripciones_actividad;
TRUNCATE TABLE colegio_actividad;
TRUNCATE TABLE actividades;

-- Insertar actividades de ejemplo
INSERT INTO actividades (nombre, nivel_requerido, descripcion, precio, duracion) VALUES
('Fútbol', 'Primaria', 'Entrenamiento y práctica de fútbol', 30.00, '2 horas semanales'),
('Baloncesto', 'Primaria', 'Entrenamiento y práctica de baloncesto', 30.00, '2 horas semanales'),
('Voleibol', 'Primaria', 'Entrenamiento y práctica de voleibol', 30.00, '2 horas semanales'),
('Ajedrez', 'Infantil', 'Clases y práctica de ajedrez', 25.00, '1 hora semanal'),
('Atletismo', 'Primaria', 'Entrenamiento de atletismo', 30.00, '2 horas semanales'),
('Juegos Predeportivos', 'Infantil', 'Iniciación al deporte', 25.00, '2 horas semanales'),
('Psicomotricidad', 'Infantil', 'Desarrollo motor', 25.00, '1 hora semanal');

-- Asignar actividades a colegios con horarios específicos
INSERT INTO colegio_actividad (id_colegio, id_actividad, horario, cupo_maximo, cupo_actual) 
SELECT 
    c.id, 
    a.id,
    CONCAT(
        CASE a.id % 2 
            WHEN 0 THEN 'Martes y Jueves '
            ELSE 'Lunes y Miércoles '
        END,
        CASE 
            WHEN c.id % 2 = 0 THEN '16:00-17:00'
            ELSE '17:00-18:00'
        END
    ),
    20,
    0
FROM colegios c 
CROSS JOIN actividades a;
