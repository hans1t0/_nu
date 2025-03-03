ALTER TABLE colegios
ADD COLUMN email varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER telefono;
