ALTER TABLE cursos 
MODIFY COLUMN nivel enum('INF','PRI') COLLATE utf8mb4_unicode_ci NOT NULL;

UPDATE cursos 
SET nivel = 'INF' 
WHERE nivel = 'Infantil';

UPDATE cursos 
SET nivel = 'PRI' 
WHERE nivel = 'Primaria';
