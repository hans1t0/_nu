CREATE TABLE centros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(255),
    telefono VARCHAR(20),
    email VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE actividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    icono VARCHAR(50),
    categoria ENUM('culturales', 'deportivas') NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

CREATE TABLE centro_actividades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    centro_id INT,
    actividad_id INT,
    dias VARCHAR(100),
    horario VARCHAR(50),
    edades VARCHAR(50),
    FOREIGN KEY (centro_id) REFERENCES centros(id),
    FOREIGN KEY (actividad_id) REFERENCES actividades(id)
);

-- Datos de ejemplo
INSERT INTO centros (codigo, nombre, direccion, telefono, email) VALUES
('ALMADRABA', 'CEIP Almadraba', 'C/ Almadraba, 15', '966 123 456', 'extraescolares@almadraba.edu.es'),
('COSTA_BLANCA', 'CEIP Costa Blanca', 'Avda. Costa Blanca, 20', '966 234 567', 'extraescolares@costablanca.edu.es'),
('FARO', 'CEIP Faro', 'C/ Del Faro, 8', '966 345 678', 'extraescolares@faro.edu.es'),
('VORAMAR', 'CEIP Voramar', 'C/ Voramar, 30', '966 456 789', 'extraescolares@voramar.edu.es');

INSERT INTO actividades (nombre, descripcion, icono, categoria) VALUES
('Música', 'Jardín Musical, Guitarra y Piano', 'bi-music-note-beamed', 'culturales'),
('Teatro', 'Expresión y creatividad a través del juego dramático', 'bi-mask', 'culturales'),
('Inglés', 'Baby English y Ludoteca Inglesa', 'bi-translate', 'culturales'),
('Arte', 'Dibujo y Pintura', 'bi-palette2', 'culturales'),
('Fútbol', 'Infantil y Primaria', 'bi-trophy', 'deportivas'),
('Deportes de Equipo', 'Baloncesto y Voleibol', 'bi-people-fill', 'deportivas');
