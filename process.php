<?php
include('conexion.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $conexion->beginTransaction();
        
        // Insertar padre
        $sql_padre = "INSERT INTO padres (nombre, email, dni, telefono) 
                      VALUES (:nombre, :email, :dni, :telefono)";
        $stmt = $conexion->prepare($sql_padre);
        $stmt->execute([
            ':nombre' => limpiarDatos($_POST['nombre_completo']),
            ':email' => limpiarDatos($_POST['email']),
            ':dni' => limpiarDatos($_POST['dni']),
            ':telefono' => limpiarDatos($_POST['telefono'])
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
                    ':nombre' => limpiarDatos($nombre),
                    ':id_colegio' => limpiarDatos($_POST['colegio'][$i]),
                    ':id_curso' => limpiarDatos($_POST['curso'][$i]),
                    ':fecha' => limpiarDatos($_POST['fecha_nacimiento'][$i])
                ]);
                
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
    }
}

function limpiarDatos($dato) {
    return htmlspecialchars(trim($dato));
}
