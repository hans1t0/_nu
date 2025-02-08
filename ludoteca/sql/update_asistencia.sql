-- Modificar la columna hora_salida
ALTER TABLE asistencia
MODIFY COLUMN hora_salida TIME NOT NULL;

-- Agregar el índice por separado
ALTER TABLE asistencia
ADD INDEX idx_fecha_inscripcion (fecha, inscripcion_id);

-- Asegurarnos de que la columna fecha tiene el tipo correcto
ALTER TABLE asistencia
MODIFY COLUMN fecha DATE NOT NULL;

-- Verificar y agregar la columna hora_salida si no existe
ALTER TABLE asistencia
ADD COLUMN IF NOT EXISTS hora_salida TIME NOT NULL 
AFTER fecha;

-- Verificar y eliminar la columna hora_entrada si existe y no se usa
-- ALTER TABLE asistencia
-- DROP COLUMN IF EXISTS hora_entrada;
