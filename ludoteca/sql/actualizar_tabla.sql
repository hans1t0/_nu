-- Script para actualizar la tabla existente

-- Verificar si la columna existe
SET @dbname = 'ludoteca_db';
SET @tablename = 'tutores';
SET @columnname = 'forma_pago';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 'Columna ya existe.'",
  CONCAT("ALTER TABLE ", @tablename, " ADD ", @columnname, " ENUM('domiciliacion', 'transferencia', 'coordinador') NOT NULL AFTER iban;")
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
