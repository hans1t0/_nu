CREATE DATABASE IF NOT EXISTS escuela_verano DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE escuela_verano;

CREATE TABLE responsables (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    dni VARCHAR(15) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    forma_pago VARCHAR(20) NOT NULL,
    iban VARCHAR(34),
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE participantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    responsable_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    centro_actual VARCHAR(100) NOT NULL,
    curso VARCHAR(10) NOT NULL,
    alergias TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (responsable_id) REFERENCES responsables(id) ON DELETE CASCADE
);

CREATE TABLE periodos_inscritos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participante_id INT NOT NULL,
    semana VARCHAR(20) NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

CREATE TABLE servicios_contratados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participante_id INT NOT NULL,
    socio_ampa ENUM('SI', 'NO') NOT NULL,
    guarderia_matinal VARCHAR(10),
    comedor ENUM('SI', 'NO') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
);

CREATE INDEX idx_responsable_dni ON responsables(dni);
CREATE INDEX idx_participante_responsable ON participantes(responsable_id);
CREATE INDEX idx_periodos_participante ON periodos_inscritos(participante_id);
CREATE INDEX idx_servicios_participante ON servicios_contratados(participante_id);
