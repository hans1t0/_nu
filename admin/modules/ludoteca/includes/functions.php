<?php
/**
 * Funciones auxiliares para el módulo de Ludoteca
 */

/**
 * Formatea una fecha al formato local (DD/MM/YYYY)
 */
function formatDateToLocal($date) {
    if (empty($date) || $date == '0000-00-00') return '';
    $dateObj = new DateTime($date);
    return $dateObj->format('d/m/Y');
}

/**
 * Formatea una fecha desde formato local (DD/MM/YYYY) a formato de base de datos (YYYY-MM-DD)
 */
function formatDateToDB($date) {
    if (empty($date)) return null;
    $dateObj = DateTime::createFromFormat('d/m/Y', $date);
    return $dateObj ? $dateObj->format('Y-m-d') : null;
}

/**
 * Obtener nombre completo del alumno por ID
 */
function getAlumnoNombre($db, $alumno_id) {
    $stmt = $db->prepare("SELECT CONCAT(nombre, ' ', apellidos) AS nombre_completo FROM alumnos WHERE id = ?");
    $stmt->execute([$alumno_id]);
    $result = $stmt->fetch();
    return $result ? $result['nombre_completo'] : 'Desconocido';
}

/**
 * Obtener nombre completo del tutor por ID
 */
function getTutorNombre($db, $tutor_id) {
    $stmt = $db->prepare("SELECT nombre FROM tutores WHERE id = ?");
    $stmt->execute([$tutor_id]);
    $result = $stmt->fetch();
    return $result ? $result['nombre'] : 'Desconocido';
}

/**
 * Obtener nombre del centro por ID
 */
function getCentroNombre($db, $centro_id) {
    $stmt = $db->prepare("SELECT nombre FROM centros WHERE id = ?");
    $stmt->execute([$centro_id]);
    $result = $stmt->fetch();
    return $result ? $result['nombre'] : 'Desconocido';
}

/**
 * Obtener descripción del horario por ID
 */
function getHorarioDescripcion($db, $horario_id) {
    $stmt = $db->prepare("SELECT CONCAT(hora_inicio, ' - ', hora_fin, ' (', precio, '€)') AS descripcion FROM horarios WHERE id = ?");
    $stmt->execute([$horario_id]);
    $result = $stmt->fetch();
    return $result ? $result['descripcion'] : 'Desconocido';
}

/**
 * Validar DNI español
 */
function validarDNI($dni) {
    $dni = strtoupper(trim($dni));
    $patron = '/^[0-9XYZ][0-9]{7}[A-Z]$/';
    
    if (!preg_match($patron, $dni)) {
        return false;
    }
    
    $letras = 'TRWAGMYFPDXBNJZSQVHLCKE';
    $numero = substr($dni, 0, 8);
    $letra = substr($dni, 8, 1);
    
    // Reemplazar la primera letra si es X, Y o Z
    $numero = str_replace(['X', 'Y', 'Z'], ['0', '1', '2'], $numero);
    
    $resto = $numero % 23;
    $letraCorrecta = $letras[$resto];
    
    return $letra === $letraCorrecta;
}

/**
 * Generar listado de alumnos con asistencias en un rango de fechas
 */
function getAsistenciasEnPeriodo($db, $fecha_inicio, $fecha_fin) {
    $query = "
        SELECT 
            a.fecha, 
            i.alumno_id, 
            CONCAT(al.nombre, ' ', al.apellidos) AS nombre_alumno,
            c.nombre AS centro,
            a.hora_entrada, 
            a.hora_salida
        FROM asistencia a
        INNER JOIN inscripciones i ON a.inscripcion_id = i.id
        INNER JOIN alumnos al ON i.alumno_id = al.id
        LEFT JOIN centros c ON al.centro_id = c.id
        WHERE a.fecha BETWEEN ? AND ?
        ORDER BY a.fecha DESC, al.apellidos, al.nombre
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$fecha_inicio, $fecha_fin]);
    return $stmt->fetchAll();
}

/**
 * Verificar si un alumno ya tiene registro de asistencia para una fecha específica
 */
function tieneAsistenciaRegistrada($db, $inscripcion_id, $fecha) {
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM asistencia WHERE inscripcion_id = ? AND fecha = ?");
    $stmt->execute([$inscripcion_id, $fecha]);
    $result = $stmt->fetch();
    return $result && $result['total'] > 0;
}
