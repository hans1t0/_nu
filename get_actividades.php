<?php
include('conexion.php');
header('Content-Type: application/json');

if (!isset($_GET['colegio_id']) || !isset($_GET['curso_id'])) {
    echo json_encode(['error' => 'Faltan parámetros requeridos']);
    exit;
}

try {
    // Obtener información del curso
    $stmt = $conexion->prepare("
        SELECT nivel, grado 
        FROM cursos 
        WHERE id = ?
    ");
    $stmt->execute([$_GET['curso_id']]);
    $curso = $stmt->fetch();
    
    if (!$curso) {
        throw new Exception('Curso no encontrado');
    }

    // Corregir la consulta para evitar el uso de 'add' como alias
    $sql = "
        SELECT 
            a.id, 
            a.nombre, 
            a.descripcion,
            ca.nivel as nivel_requerido,
            FORMAT(ca.precio, 2) as precio,
            a.duracion,
            a.cupo_maximo, 
            ca.cupo_actual,
            (ca.cupo_actual >= a.cupo_maximo) as cupo_lleno,
            GROUP_CONCAT(DISTINCT da.dia) as dias_disponibles,
            GROUP_CONCAT(
                DISTINCT CONCAT(
                    ah.id_dia, ' ',
                    TIME_FORMAT(h.hora_inicio, '%H:%i'),
                    '-',
                    TIME_FORMAT(h.hora_fin, '%H:%i')
                )
            ) as horarios
        FROM actividades a
        JOIN colegio_actividad ca ON a.id = ca.id_actividad
        LEFT JOIN dias_actividad da ON a.id = da.id_actividad
        LEFT JOIN actividad_horarios ah ON ca.id = ah.id_colegio_actividad
        LEFT JOIN horarios_disponibles h ON ah.id_horario = h.id
        WHERE ca.id_colegio = ? 
        AND ca.nivel = ?
        AND ? BETWEEN ca.grado_minimo AND ca.grado_maximo
        GROUP BY a.id, ca.id
        ORDER BY a.nombre
    ";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        $_GET['colegio_id'], 
        $curso['nivel'],
        $curso['grado']
    ]);
    $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $actividades,
        'nivel' => $curso['nivel'],
        'grado' => $curso['grado'],
        'count' => count($actividades)
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
