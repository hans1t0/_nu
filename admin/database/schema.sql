
-- Crear bases de datos si no existen
CREATE DATABASE IF NOT EXISTS guarderia_matinal;
CREATE DATABASE IF NOT EXISTS ludoteca_db;
CREATE DATABASE IF NOT EXISTS escuela_verano;
CREATE DATABASE IF NOT EXISTS actividades_escolares;

-- Seleccionar la base de datos guarderia_matinal
USE guarderia_matinal;

-- Tabla de alumnos para guardería matinal
CREATE TABLE IF NOT EXISTS alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    curso VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de asistencia para guardería matinal
CREATE TABLE IF NOT EXISTS asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_entrada TIME,
    hora_salida TIME,
    observaciones TEXT,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE,
    UNIQUE KEY (alumno_id, fecha)
);

-- Tabla de pagos para guardería matinal
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    alumno_id INT NOT NULL,
    concepto VARCHAR(100) NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    fecha_pago DATE NOT NULL,
    estado ENUM('pendiente', 'pagado', 'cancelado') DEFAULT 'pendiente',
    metodo_pago VARCHAR(50),
    observaciones TEXT,
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id) ON DELETE CASCADE
);

-- Seleccionar la base de datos ludoteca_db
USE ludoteca_db;

-- Tabla de participantes para ludoteca
CREATE TABLE IF NOT EXISTS participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    edad INT,
    telefono VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de actividades para ludoteca
CREATE TABLE IF NOT EXISTS actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    edad_minima INT,
    edad_maxima INT,
    duracion INT COMMENT 'Duración en minutos',
    capacidad INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de monitores para ludoteca
CREATE TABLE IF NOT EXISTS monitores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de horarios para ludoteca
CREATE TABLE IF NOT EXISTS horarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actividad_id INT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    monitor_id INT,
    max_participantes INT DEFAULT 15,
    observaciones TEXT,
    FOREIGN KEY (actividad_id) REFERENCES actividades(id) ON DELETE CASCADE,
    FOREIGN KEY (monitor_id) REFERENCES monitores(id) ON DELETE SET NULL
);

-- Tabla de inscripciones para ludoteca
CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    horario_id INT NOT NULL,
    participante_id INT NOT NULL,
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('confirmada', 'en_espera', 'cancelada') DEFAULT 'confirmada',
    observaciones TEXT,
    FOREIGN KEY (horario_id) REFERENCES horarios(id) ON DELETE CASCADE,
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
    UNIQUE KEY (horario_id, participante_id)
);

-- Seleccionar la base de datos escuela_verano
USE escuela_verano;

-- Tabla de inscripciones para escuela de verano
CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    tutor_nombre VARCHAR(100) NOT NULL,
    tutor_telefono VARCHAR(20) NOT NULL,
    tutor_email VARCHAR(100),
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    jornada ENUM('completa', 'mañana', 'tarde') DEFAULT 'completa',
    comedor BOOLEAN DEFAULT FALSE,
    alergias TEXT,
    observaciones TEXT,
    estado ENUM('pendiente', 'confirmada', 'cancelada') DEFAULT 'pendiente',
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de sesiones para escuela de verano
CREATE TABLE IF NOT EXISTS sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    edad_minima INT,
    edad_maxima INT,
    monitor_id INT,
    capacidad INT DEFAULT 20,
    lugar VARCHAR(100)
);

-- Tabla de monitores para escuela de verano
CREATE TABLE IF NOT EXISTS monitores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    especialidad VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de asistencia para escuela de verano
CREATE TABLE IF NOT EXISTS asistencia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    fecha DATE NOT NULL,
    asistencia BOOLEAN DEFAULT TRUE,
    observaciones TEXT,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE,
    UNIQUE KEY (inscripcion_id, fecha)
);

-- Seleccionar la base de datos actividades_escolares
USE actividades_escolares;

-- Tabla de estudiantes para actividades extraescolares
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    curso VARCHAR(50),
    tutor_nombre VARCHAR(100),
    tutor_telefono VARCHAR(20),
    tutor_email VARCHAR(100),
    observaciones TEXT,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de actividades para actividades extraescolares
CREATE TABLE IF NOT EXISTS actividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    dia_semana ENUM('lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'),
    hora_inicio TIME,
    hora_fin TIME,
    edad_minima INT,
    edad_maxima INT,
    precio_mensual DECIMAL(10,2),
    profesor_id INT,
    aula VARCHAR(50),
    capacidad INT DEFAULT 15,
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de profesores para actividades extraescolares
CREATE TABLE IF NOT EXISTS profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    especialidad VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de inscripciones para actividades extraescolares
CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    actividad_id INT NOT NULL,
    fecha_inscripcion DATE NOT NULL,
    fecha_baja DATE,
    observaciones TEXT,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (actividad_id) REFERENCES actividades(id) ON DELETE CASCADE
);

-- Tabla de pagos para actividades extraescolares
CREATE TABLE IF NOT EXISTS pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inscripcion_id INT NOT NULL,
    mes VARCHAR(20) NOT NULL,
    año INT NOT NULL,
    cantidad DECIMAL(10,2) NOT NULL,
    fecha_pago DATE,
    estado ENUM('pendiente', 'pagado', 'cancelado') DEFAULT 'pendiente',
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id) ON DELETE CASCADE
);

-- Insertar algunos datos de ejemplo en las tablas para pruebas

-- Guardería Matinal
USE guarderia_matinal;

INSERT INTO alumnos (nombre, apellidos, fecha_nacimiento, curso, activo, observaciones) VALUES 
('Ana', 'García Pérez', '2018-05-10', 'Infantil 3', 1, 'Alergia a frutos secos'),
('Carlos', 'Martínez López', '2017-03-22', 'Infantil 4', 1, 'Necesita medicación a las 11:00'),
('Laura', 'Sánchez Rodríguez', '2016-09-15', 'Infantil 5', 1, NULL),
('Miguel', 'Fernández González', '2017-11-30', 'Infantil 4', 1, NULL),
('Lucía', 'López Martín', '2018-02-05', 'Infantil 3', 0, 'Baja temporal por traslado');

-- Ludoteca
USE ludoteca_db;

INSERT INTO participantes (nombre, apellidos, edad, telefono, activo) VALUES 
('Javier', 'Ramírez Silva', 7, '666111222', 1),
('Elena', 'Gómez Torres', 5, '666333444', 1),
('Pablo', 'Muñoz Serrano', 9, '666555666', 1),
('Marta', 'Díaz Álvarez', 6, '666777888', 1),
('Daniel', 'Romero Castro', 8, '666999000', 0);

INSERT INTO actividades (nombre, descripcion, edad_minima, edad_maxima, duracion, capacidad, activo) VALUES 
('Juegos de mesa cooperativos', 'Actividad para fomentar el trabajo en equipo', 5, 12, 60, 15, 1),
('Manualidades con reciclaje', 'Taller para crear juguetes con materiales reciclados', 4, 10, 90, 12, 1),
('Cuentacuentos interactivos', 'Narración de cuentos con participación de los niños', 3, 8, 45, 20, 1),
('Juegos deportivos', 'Actividades deportivas para desarrollar habilidades motrices', 6, 12, 60, 15, 1),
('Taller de teatro', 'Expresión corporal y representación de pequeñas obras', 5, 12, 90, 10, 0);

INSERT INTO monitores (nombre, apellidos, telefono, email, activo) VALUES 
('Raquel', 'Moreno Jiménez', '677111222', 'raquelm@mail.com', 1),
('Alejandro', 'Torres Vega', '677333444', 'alejandrot@mail.com', 1),
('Cristina', 'Blanco Ruiz', '677555666', 'cristinab@mail.com', 1);

-- Escuela de Verano
USE escuela_verano;

INSERT INTO inscripciones (nombre, apellidos, fecha_nacimiento, tutor_nombre, tutor_telefono, tutor_email, fecha_inicio, fecha_fin, jornada, comedor) VALUES 
('Sara', 'Martín Sanz', '2016-07-12', 'María Sanz', '688111222', 'maria@mail.com', '2023-07-01', '2023-07-31', 'completa', 1),
('Luis', 'Pérez García', '2015-04-23', 'Pedro Pérez', '688333444', 'pedro@mail.com', '2023-07-01', '2023-07-15', 'mañana', 0),
('Isabel', 'González Ruiz', '2017-03-10', 'Carmen Ruiz', '688555666', 'carmen@mail.com', '2023-07-15', '2023-08-15', 'tarde', 0);

-- Actividades Extraescolares
USE actividades_escolares;

INSERT INTO estudiantes (nombre, apellidos, fecha_nacimiento, curso, tutor_nombre, tutor_telefono, activo) VALUES 
('Gabriel', 'Sánchez Moreno', '2012-09-18', 'Quinto', 'Ana Moreno', '699111222', 1),
('Sofía', 'Hernández Gil', '2014-05-25', 'Tercero', 'José Hernández', '699333444', 1),
('Diego', 'Flores Vega', '2013-11-30', 'Cuarto', 'Laura Vega', '699555666', 1),
('Valentina', 'Castro Rivera', '2011-02-14', 'Sexto', 'Carlos Castro', '699777888', 1);

INSERT INTO profesores (nombre, apellidos, telefono, email, especialidad, activo) VALUES 
('Manuel', 'Jiménez Torres', '633111222', 'manuelj@mail.com', 'Música', 1),
('Lucia', 'Ortega Sanz', '633333444', 'luciao@mail.com', 'Idiomas', 1),
('Ricardo', 'Vargas Martín', '633555666', 'ricardov@mail.com', 'Deportes', 1);

INSERT INTO actividades (nombre, descripcion, dia_semana, hora_inicio, hora_fin, edad_minima, edad_maxima, precio_mensual, capacidad, activo) VALUES 
('Inglés Divertido', 'Aprendizaje de inglés a través de juegos', 'lunes', '16:00:00', '17:30:00', 6, 12, 35.00, 12, 1),
('Fútbol Sala', 'Entrenamiento y técnicas básicas de fútbol sala', 'martes', '17:00:00', '18:30:00', 8, 14, 30.00, 15, 1),
('Taller de Música', 'Iniciación a la música y aprendizaje de instrumentos', 'miercoles', '16:30:00', '18:00:00', 7, 12, 40.00, 10, 1),
('Programación para niños', 'Introducción a la programación con Scratch', 'jueves', '17:00:00', '18:30:00', 9, 14, 45.00, 12, 1);
