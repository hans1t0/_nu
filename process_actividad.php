<?php
include('conexion.php');

// Constantes de horarios
define('HORA_MIN', '16:00');
define('HORA_MAX', '18:30');
define('DURACION_MIN', 30); // minutos

function validarHorario($inicio, $fin) {
    $horaInicio = strtotime($inicio);
    $horaFin = strtotime($fin);
    $horaMin = strtotime(HORA_MIN);
    $horaMax = strtotime(HORA_MAX);
    
    // Validar rango permitido
    if ($horaInicio < $horaMin || $horaFin > $horaMax) {
        throw new Exception('El horario debe estar entre ' . HORA_MIN . ' y ' . HORA_MAX);
    }
    
    // Validar duración mínima
    $duracion = ($horaFin - $horaInicio) / 60; // duración en minutos
    if ($duracion < DURACION_MIN) {
        throw new Exception('La duración mínima debe ser ' . DURACION_MIN . ' minutos');
    }
    
    return true;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar horarios antes de procesar
        $horaInicio = $_POST['hora_inicio'];
        $horaFin = $_POST['hora_fin'];
        
        validarHorario($horaInicio, $horaFin);
        
        $conexion->beginTransaction();

        // Insertar actividad principal
        $stmt = $conexion->prepare("
            INSERT INTO actividades (
                nombre, nivel_requerido, grado_minimo, 
                grado_maximo, descripcion, precio, 
                duracion, cupo_maximo
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['nivel'],
            $_POST['grado_minimo'],
            $_POST['grado_maximo'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion'],
            $_POST['cupo_maximo']
        ]);
        
        $actividad_id = $conexion->lastInsertId();

        // Insertar días disponibles para la actividad
        if (!empty($_POST['dias'])) {
            $stmt = $conexion->prepare("
                INSERT INTO dias_actividad (
                    id_actividad, dia
                ) VALUES (?, ?)
            ");
            
            foreach ($_POST['dias'] as $dia) {
                $stmt->execute([$actividad_id, $dia]);
            }
        }

        // Insertar asignación a colegio
        $stmt = $conexion->prepare("
            INSERT INTO colegio_actividad (
                id_colegio, id_actividad, nivel,
                grado_minimo, grado_maximo,
                precio, activa
            ) VALUES (?, ?, ?, ?, ?, ?, true)
        ");
        
        $stmt->execute([
            $_POST['colegio'],
            $actividad_id,
            $_POST['nivel'],
            $_POST['grado_min'],
            $_POST['grado_max'],
            $_POST['precio']
        ]);
        
        $colegio_actividad_id = $conexion->lastInsertId();

        // Insertar días y horarios
        if (!empty($_POST['dias'])) {
            $stmt = $conexion->prepare("
                INSERT INTO actividad_dias (
                    id_colegio_actividad, dia, 
                    hora_inicio, hora_fin
                ) VALUES (?, ?, ?, ?)
            ");
            
            foreach ($_POST['dias'] as $dia) {
                $stmt->execute([
                    $colegio_actividad_id,
                    $dia,
                    $_POST['hora_inicio'],
                    $_POST['hora_fin']
                ]);
            }
        }

        $conexion->commit();
        header('Location: actividades.php?mensaje=Actividad creada correctamente');
        exit;
    }
} catch (Exception $e) {
    $conexion->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}
