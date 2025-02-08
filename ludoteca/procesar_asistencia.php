<?php
session_start();
require_once 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: asistencia.php');
    exit;
}

$fecha = $_POST['fecha'];
$centro_id = $_POST['centro_id'] ?? '';
$asistencias = $_POST['asistencias'] ?? [];

try {
    // Comenzar transacción
    $pdo->beginTransaction();

    // Si hay un centro_id, solo eliminar las asistencias de ese centro
    if (!empty($centro_id)) {
        $stmt = $pdo->prepare("
            DELETE a FROM asistencia a
            JOIN inscripciones i ON a.inscripcion_id = i.id
            JOIN alumnos al ON i.alumno_id = al.id
            WHERE a.fecha = ? AND al.centro_id = ?
        ");
        $stmt->execute([$fecha, $centro_id]);
    } else {
        // Eliminar todas las asistencias de la fecha
        $stmt = $pdo->prepare("DELETE FROM asistencia WHERE fecha = ?");
        $stmt->execute([$fecha]);
    }

    // Insertar nuevos registros de asistencia
    if (!empty($asistencias)) {
        // Primero obtener las horas de salida de los horarios
        $stmt = $pdo->prepare("
            SELECT i.id, h.hora_fin
            FROM inscripciones i
            JOIN horarios h ON i.horario_id = h.id
            WHERE i.id IN (" . str_repeat('?,', count($asistencias) - 1) . "?)
        ");
        $stmt->execute($asistencias);
        $horarios = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Insertar asistencias con hora de salida
        $stmt = $pdo->prepare("
            INSERT INTO asistencia (inscripcion_id, fecha, hora_salida) 
            VALUES (?, ?, ?)
        ");

        foreach ($asistencias as $inscripcion_id) {
            if (isset($horarios[$inscripcion_id])) {
                $stmt->execute([
                    $inscripcion_id,
                    $fecha,
                    $horarios[$inscripcion_id]
                ]);
            }
        }
    }

    // Confirmar cambios
    $pdo->commit();
    
    $_SESSION['mensaje'] = "Asistencias guardadas correctamente";
} catch (Exception $e) {
    // Revertir cambios si hay error
    $pdo->rollBack();
    error_log("Error en procesar_asistencia.php: " . $e->getMessage());
    $_SESSION['mensaje'] = "Error al guardar las asistencias: " . $e->getMessage();
}

// Redireccionar manteniendo los filtros
$redirect = 'asistencia.php?fecha=' . urlencode($fecha);
if (!empty($centro_id)) {
    $redirect .= '&centro_id=' . urlencode($centro_id);
}

header('Location: ' . $redirect);
exit;
