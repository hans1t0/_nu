-- Crear tabla de administradores
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertar admin por defecto
-- Contraseña: Nu3Ludoteca2024!
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$XkL3FHn.L9LfFBgPZ8XB5.uPpI.JV9AY9JEcr8pYl3x7Szm3V2jLS');
