<?php
include('conexion.php');

header('Content-Type: application/json');

function sanitizarDatos($datos) {
    if (is_array($datos)) {
        return array_map('sanitizarDatos', $datos);
    }
    $datos = trim($datos);
    $datos = stripslashes($datos);
    $datos = htmlspecialchars($datos, ENT_QUOTES, 'UTF-8');
    return $datos;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validar token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception('Error de validación de seguridad');
        }

        $conexion->beginTransaction();
        
        // Sanitizar datos antes de insertar
        $nombre = sanitizarDatos($_POST['nombre_completo']);
        $email = sanitizarEmail($_POST['email']);
        $dni = strtoupper(sanitizarDatos($_POST['dni']));
        $telefono = sanitizarDatos($_POST['telefono']);

        // Insertar padre con datos sanitizados
        $sql_padre = "INSERT INTO padres (nombre, email, dni, telefono) 
                      VALUES (:nombre, :email, :dni, :telefono)";
        $stmt = $conexion->prepare($sql_padre);
        $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':dni' => $dni,
            ':telefono' => $telefono
        ]);
        
        $id_padre = $conexion->lastInsertId();
        $hijos_data = [];
        
        // Insertar hijos
        $sql_hijo = "INSERT INTO hijos (id_padre, nombre, id_colegio, id_curso, fecha_nacimiento) 
                     VALUES (:id_padre, :nombre, :id_colegio, :id_curso, :fecha)";
        $stmt_hijo = $conexion->prepare($sql_hijo);
        
        foreach($_POST['nombre_hijo'] as $i => $nombre) {
            if(!empty($nombre)) {
                $stmt_hijo->execute([
                    ':id_padre' => $id_padre,
                    ':nombre' => sanitizarDatos($nombre),
                    ':id_colegio' => sanitizarDatos($_POST['colegio'][$i]),
                    ':id_curso' => sanitizarDatos($_POST['curso'][$i]),
                    ':fecha' => sanitizarDatos($_POST['fecha_nacimiento'][$i])
                ]);
                
                $id_hijo = $conexion->lastInsertId();
                
                // Procesar inscripciones a actividades
                if (isset($_POST['actividades'][$i]) && is_array($_POST['actividades'][$i])) {
                    $stmt_actividad = $conexion->prepare("
                        INSERT INTO inscripciones_actividad (id_hijo, id_colegio, id_actividad)
                        VALUES (:id_hijo, :id_colegio, :id_actividad)
                    ");
                    
                    foreach ($_POST['actividades'][$i] as $id_actividad) {
                        $stmt_actividad->execute([
                            ':id_hijo' => $id_hijo,
                            ':id_colegio' => $_POST['colegio'][$i],
                            ':id_actividad' => $id_actividad
                        ]);
                        
                        // Actualizar cupo actual
                        $conexion->prepare("
                            UPDATE colegio_actividad 
                            SET cupo_actual = cupo_actual + 1
                            WHERE id_colegio = ? AND id_actividad = ?
                        ")->execute([$_POST['colegio'][$i], $id_actividad]);
                    }
                }
                
                // Obtener datos para el resumen
                $stmt_datos = $conexion->query("
                    SELECT h.nombre, c.nombre as colegio, cu.nombre as curso
                    FROM hijos h
                    JOIN colegios c ON h.id_colegio = c.id
                    JOIN cursos cu ON h.id_curso = cu.id
                    WHERE h.id = " . $conexion->lastInsertId()
                );
                $hijos_data[] = $stmt_datos->fetch();
            }
        }
        
        $conexion->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Registro exitoso',
            'padre' => [
                'nombre' => $_POST['nombre_completo'],
                'dni' => $_POST['dni'],
                'email' => $_POST['email']
            ],
            'hijos' => $hijos_data
        ]);
        
    } catch(PDOException $e) {
        $conexion->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Error al registrar: ' . $e->getMessage()
        ]);
    } catch(Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

function limpiarDatos($dato) {
    return htmlspecialchars(trim($dato));
}
