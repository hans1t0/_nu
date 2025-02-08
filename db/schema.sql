-- Crear base de datos
CREATE DATABASE IF NOT EXISTS guarderia_matinal DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE guarderia_matinal;

-- Tabla de responsables
CREATE TABLE responsables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    dni VARCHAR(9) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    forma_pago ENUM('DOMICILIACION', 'TRANSFERENCIA', 'COORDINADOR') NOT NULL,
    iban VARCHAR(24),
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_dni (dni),
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- Tabla de colegios
CREATE TABLE colegios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(10) NOT NULL UNIQUE,
    tiene_desayuno BOOLEAN DEFAULT false,
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB;

-- Tabla de hijos
CREATE TABLE hijos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    responsable_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    colegio_id INT NOT NULL,
    curso ENUM('1INF', '2INF', '3INF', '1PRIM', '2PRIM', '3PRIM', '4PRIM', '5PRIM', '6PRIM') NOT NULL,
    hora_entrada TIME NOT NULL,
    desayuno BOOLEAN DEFAULT false,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responsable_id) REFERENCES responsables(id) ON DELETE CASCADE,
    FOREIGN KEY (colegio_id) REFERENCES colegios(id),
    INDEX idx_responsable (responsable_id),
    INDEX idx_colegio (colegio_id)
) ENGINE=InnoDB;

-- Insertar colegios
INSERT INTO colegios (nombre, codigo, tiene_desayuno) VALUES
('CEIP Almadraba', 'ALMADRABA', true),
('CEIP Costa Blanca', 'COSTA', false),
('CEIP Faro', 'FARO', false),
('CEIP Voramar', 'VORAMAR', false);

-- Vista para consultas comunes
CREATE VIEW v_inscripciones AS
SELECT 
    r.nombre as responsable,
    r.dni,
    r.telefono,
    h.nombre as hijo,
    h.fecha_nacimiento,
    c.nombre as colegio,
    h.curso,
    h.hora_entrada,
    h.desayuno
FROM responsables r
JOIN hijos h ON h.responsable_id = r.id
JOIN colegios c ON h.colegio_id = c.id;
