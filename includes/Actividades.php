<?php
class Actividades {
    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getCentro($codigo) {
        $stmt = $this->db->prepare("SELECT * FROM centros WHERE codigo = ?");
        $stmt->execute([$codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene la información de un centro educativo por su ID
     */
    public function getCentroById($id) {
        $sql = "SELECT id, nombre, direccion, telefono, email 
                FROM colegios 
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActividadesCentro($colegioId) {
        $sql = "SELECT 
            a.id,
            a.actividad,
            a.detalle_actividad,
            a.desde AS curso_minimo,
            a.hasta AS curso_maximo,
            COALESCE(ap.precio, 0) as precio_actual,
            c.id as colegio_id,
            c.nombre as colegio_nombre,
            ca.cupo_maximo,
            ca.cupo_actual,
            ca.activo,
            GROUP_CONCAT(
                CONCAT(
                    ah.dia_semana, 
                    ' de ', 
                    TIME_FORMAT(ah.hora_inicio, '%H:%i'),
                    ' a ',
                    TIME_FORMAT(ah.hora_fin, '%H:%i')
                )
                ORDER BY FIELD(ah.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')
                SEPARATOR ' y '
            ) as horarios
        FROM actividades a
        JOIN colegio_actividad ca ON a.id = ca.actividad_id
        JOIN colegios c ON ca.colegio_id = c.id
        JOIN actividad_horarios ah ON a.id = ah.actividad_id 
        LEFT JOIN (
            SELECT ap1.id_actividad, ap1.precio 
            FROM actividades_precio ap1
            WHERE ap1.fecha = (
                SELECT MAX(ap2.fecha)
                FROM actividades_precio ap2
                WHERE ap2.id_actividad = ap1.id_actividad
            )
        ) ap ON a.id = ap.id_actividad
        WHERE ca.activo = 1 
        AND ca.colegio_id = ?
        AND ca.cupo_actual < ca.cupo_maximo
        GROUP BY a.id, a.actividad, a.detalle_actividad, a.desde, a.hasta, 
                 ap.precio, c.id, c.nombre, ca.cupo_maximo, ca.cupo_actual, ca.activo";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$colegioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActividad($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, ca.cupo_actual, ca.cupo_maximo,
                   COALESCE(ap.precio, 0) as precio
            FROM actividades a
            JOIN colegio_actividad ca ON a.id = ca.actividad_id
            LEFT JOIN (
                SELECT ap1.id_actividad, ap1.precio
                FROM actividades_precio ap1
                WHERE ap1.fecha = (
                    SELECT MAX(ap2.fecha)
                    FROM actividades_precio ap2
                    WHERE ap2.id_actividad = ap1.id_actividad
                )
            ) ap ON a.id = ap.id_actividad
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function addActividad($datos) {
        $stmt = $this->db->prepare("
            INSERT INTO actividades (actividad, detalle_actividad, desde, hasta, horario, max_alumnos, id_colegio)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $datos['actividad'],
            $datos['detalle_actividad'],
            $datos['desde'],
            $datos['hasta'],
            $datos['horario'],
            $datos['max_alumnos'],
            $datos['id_colegio']
        ]);
    }

    public function getHorariosActividad($actividad_id) {
        $stmt = $this->db->prepare("
            SELECT dia_semana, hora_inicio, hora_fin 
            FROM actividad_horarios 
            WHERE actividad_id = ?
            ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')
        ");
        $stmt->execute([$actividad_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function verificarInscripcion($hijo_id, $actividad_id) {
        $stmt = $this->db->prepare("
            SELECT i.*, a.actividad
            FROM inscripciones i
            JOIN actividades a ON i.actividad_id = a.id
            WHERE i.hijo_id = ? AND i.actividad_id = ?
        ");
        $stmt->execute([$hijo_id, $actividad_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function inscribirActividad($hijo_id, $actividad_id) {
        $this->db->beginTransaction();
        try {
            // Verificar cupo disponible
            $stmt = $this->db->prepare("
                SELECT ca.* 
                FROM colegio_actividad ca
                JOIN actividades a ON ca.actividad_id = a.id
                WHERE a.id = ? AND ca.cupo_actual < ca.cupo_maximo
                FOR UPDATE
            ");
            $stmt->execute([$actividad_id]);
            $cupo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$cupo) {
                throw new Exception("No hay cupo disponible");
            }

            // Crear inscripción
            $stmt = $this->db->prepare("
                INSERT INTO inscripciones (hijo_id, actividad_id, estado)
                VALUES (?, ?, 'confirmada')
            ");
            $stmt->execute([$hijo_id, $actividad_id]);

            // Actualizar cupo
            $stmt = $this->db->prepare("
                UPDATE colegio_actividad 
                SET cupo_actual = cupo_actual + 1
                WHERE actividad_id = ?
            ");
            $stmt->execute([$actividad_id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
