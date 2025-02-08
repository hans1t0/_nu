-- Crear base de datos
CREATE DATABASE IF NOT EXISTS ludoteca_db;
USE ludoteca_db;

-- Crear tabla centros
CREATE TABLE centros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE
);

-- Tabla de alumnos
CREATE TABLE alumnos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    centro_id INT,
    curso VARCHAR(20),
    alergias TEXT,
    medicacion TEXT,
    observaciones TEXT,
    fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Actualizar tabla tutores
DROP TABLE IF EXISTS tutores;
CREATE TABLE tutores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(9) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    telefono2 VARCHAR(15),
    iban VARCHAR(24),
    forma_pago ENUM('domiciliacion', 'transferencia', 'coordinador') NOT NULL,
    metodo_pago VARCHAR(20),
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de relación alumnos-tutores
CREATE TABLE alumno_tutor (
    alumno_id INT,
    tutor_id INT,
    relacion VARCHAR(20), -- (padre, madre, tutor legal, etc)
    PRIMARY KEY (alumno_id, tutor_id),
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id),
    FOREIGN KEY (tutor_id) REFERENCES tutores(id)
);

-- Tabla de horarios disponibles
CREATE TABLE horarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    precio DECIMAL(6,2) NOT NULL,
    descripcion VARCHAR(100)
);

-- Tabla de inscripciones
CREATE TABLE inscripciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumno_id INT,
    horario_id INT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('activa', 'cancelada', 'finalizada') DEFAULT 'activa',
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id),
    FOREIGN KEY (horario_id) REFERENCES horarios(id)
);

-- Tabla de pagos
CREATE TABLE pagos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscripcion_id INT,
    fecha_pago TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    monto DECIMAL(6,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'transferencia', 'tarjeta'),
    estado ENUM('pendiente', 'pagado', 'cancelado') DEFAULT 'pendiente',
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id)
);

-- Tabla de asistencia
CREATE TABLE asistencia (
    id INT PRIMARY KEY AUTO_INCREMENT,
    inscripcion_id INT,
    fecha DATE NOT NULL,
    hora_entrada TIME,
    hora_salida TIME,
    observaciones TEXT,
    FOREIGN KEY (inscripcion_id) REFERENCES inscripciones(id)
);

-- Insertar centros de ejemplo
INSERT INTO centros (nombre, codigo) VALUES 
('CEIP Almadraba', 'ALM'),
('CEIP Costa Blanca', 'CBL'),
('CEIP Faro', 'FAR'),
('CEIP Voramar', 'VOR');

-- Insertar datos de ejemplo para horarios
INSERT INTO horarios (hora_inicio, hora_fin, precio, descripcion) VALUES 
('15:00', '16:00', 25.00, 'Horario hasta las 16h'),
('15:00', '17:00', 35.00, 'Horario hasta las 17h');
