<?php
include('conexion.php');
header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('ID de familia no válido');
    }

    $id = (int)$_GET['id'];

    // Obtener datos del padre
    $stmt = $conexion->prepare("
        SELECT id, nombre, dni, email, telefono, fecha_registro 
        FROM padres 
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $padre = $stmt->fetch();

    if (!$padre) {
        throw new Exception('Familia no encontrada');
    }

    // Obtener datos de los hijos con sus colegios y cursos
    $stmt = $conexion->prepare("
        SELECT 
            h.id,
            h.nombre,
            h.fecha_nacimiento,
            c.nombre as colegio,
            cu.nombre as curso,
            cu.nivel
        FROM hijos h
        LEFT JOIN colegios c ON h.id_colegio = c.id
        LEFT JOIN cursos cu ON h.id_curso = cu.id
        WHERE h.id_padre = ?
        ORDER BY h.fecha_nacimiento ASC
    ");
    $stmt->execute([$id]);
    $hijos = $stmt->fetchAll();

    // Formatear fechas y datos para la respuesta
    foreach ($hijos as &$hijo) {
        $hijo['fecha_nacimiento'] = date('d/m/Y', strtotime($hijo['fecha_nacimiento']));
        $hijo['curso'] = $hijo['nivel'] . ' - ' . $hijo['curso'];
        unset($hijo['nivel']); // Removemos el campo nivel ya que va incluido en curso
    }

    echo json_encode([
        'success' => true,
        'padre' => [
            'nombre' => $padre['nombre'],
            'dni' => $padre['dni'],
            'email' => $padre['email'],
            'telefono' => $padre['telefono'],
            'fecha_registro' => date('d/m/Y', strtotime($padre['fecha_registro']))
        ],
        'hijos' => $hijos
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
