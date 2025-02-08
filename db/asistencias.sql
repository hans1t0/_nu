CREATE TABLE asistencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hijo_id INT NOT NULL,
    fecha DATE NOT NULL,
    asistio BOOLEAN DEFAULT false,
    desayuno BOOLEAN DEFAULT false,
    hora_entrada TIME,
    observaciones TEXT,
    creado_por VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hijo_id) REFERENCES hijos(id),
    UNIQUE KEY unique_asistencia (hijo_id, fecha),
    INDEX idx_fecha (fecha)
) ENGINE=InnoDB;
