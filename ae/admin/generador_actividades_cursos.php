<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Primero incluimos config.php
require_once __DIR__ . '/../includes/config.php';

// Luego incluimos y verificamos la conexión
require_once __DIR__ . '/../includes/conexion.php';

// Verificar que tenemos la conexión antes de continuar
if (!isset($conexion)) {
    die("Error: No se pudo establecer la conexión con la base de datos");
}

// Definir actividades por nivel educativo
$actividades_por_nivel = [
    'Infantil' => [
        ['nombre' => 'Juegos Musicales', 'descripcion' => 'Actividades rítmicas y musicales', 'precio' => 30.00],
        ['nombre' => 'Arte y Creatividad', 'descripcion' => 'Talleres de expresión artística', 'precio' => 35.00],
        ['nombre' => 'Psicomotricidad', 'descripcion' => 'Desarrollo motor y coordinación', 'precio' => 32.00],
        ['nombre' => 'Cuentacuentos', 'descripcion' => 'Narración y dramatización', 'precio' => 28.00],
    ],
    'Primaria' => [
        ['nombre' => 'Robótica Educativa', 'descripcion' => 'Introducción a la programación', 'precio' => 45.00],
        ['nombre' => 'Deportes de Equipo', 'descripcion' => 'Actividades deportivas colectivas', 'precio' => 35.00],
        ['nombre' => 'Club de Ciencias', 'descripcion' => 'Experimentos y descubrimientos', 'precio' => 40.00],
        ['nombre' => 'Teatro y Drama', 'descripcion' => 'Expresión dramática y teatral', 'precio' => 38.00],
        ['nombre' => 'Idiomas Divertidos', 'descripcion' => 'Aprendizaje lúdico de idiomas', 'precio' => 42.00],
    ]
];

// Horarios disponibles por nivel
$horarios_por_nivel = [
    'Infantil' => [
        ['inicio' => '16:00', 'fin' => '17:00'],
        ['inicio' => '17:00', 'fin' => '18:00']
    ],
    'Primaria' => [
        ['inicio' => '16:00', 'fin' => '17:00'],
        ['inicio' => '17:00', 'fin' => '18:00'],
        ['inicio' => '18:00', 'fin' => '19:00']
    ]
];

$dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

try {
    if (!$conexion) {
        throw new Exception('No hay conexión a la base de datos');
    }

    $conexion->beginTransaction();

    // Obtener todos los colegios
    $stmt = $conexion->query("SELECT id, nombre FROM colegios");
    if (!$stmt) {
        throw new Exception('Error al consultar colegios: ' . implode(', ', $conexion->errorInfo()));
    }
    $colegios = $stmt->fetchAll();
    
    if (empty($colegios)) {
        throw new Exception('No hay colegios en la base de datos');
    }

    // Obtener todos los cursos agrupados por nivel
    $stmt = $conexion->query("SELECT id, nombre, nivel, grado FROM cursos ORDER BY nivel, grado");
    if (!$stmt) {
        throw new Exception('Error al consultar cursos: ' . implode(', ', $conexion->errorInfo()));
    }
    
    $cursos_por_nivel = [];
    while ($curso = $stmt->fetch()) {
        $cursos_por_nivel[$curso['nivel']][] = $curso;
    }

    if (empty($cursos_por_nivel)) {
        throw new Exception('No hay cursos en la base de datos');
    }

    $actividades_generadas = 0;

    // Generar actividades para cada nivel y curso
    foreach ($cursos_por_nivel as $nivel => $cursos) {
        if (!isset($actividades_por_nivel[$nivel])) {
            continue; // Saltamos niveles que no tienen actividades definidas
        }

        $actividades = $actividades_por_nivel[$nivel];
        $horarios = $horarios_por_nivel[$nivel];

        foreach ($actividades as $actividad) {
            // Determinar rango de cursos para la actividad
            $num_cursos = min(3, count($cursos));
            if ($num_cursos < 1) continue;

            // Selección segura de cursos
            $indices_cursos = range(0, count($cursos) - 1);
            shuffle($indices_cursos);
            $cursos_disponibles = array_slice($indices_cursos, 0, $num_cursos);

            $curso_min = min($cursos_disponibles);
            $curso_max = max($cursos_disponibles);

            // Crear actividad base con try/catch específico
            try {
                $stmt = $conexion->prepare("
                    INSERT INTO actividades (nombre, descripcion, nivel_minimo, curso_minimo, curso_maximo, precio) 
                    VALUES (:nombre, :descripcion, :nivel_minimo, :curso_minimo, :curso_maximo, :precio)
                ");

                $stmt->execute([
                    ':nombre' => $actividad['nombre'],
                    ':descripcion' => $actividad['descripcion'],
                    ':nivel_minimo' => $nivel,
                    ':curso_minimo' => $cursos[$curso_min]['id'],
                    ':curso_maximo' => $cursos[$curso_max]['id'],
                    ':precio' => $actividad['precio']
                ]);

                $actividad_id = $conexion->lastInsertId();

                // Asignar horarios aleatorios de forma segura
                $num_dias = rand(2, 3);
                $dias_indices = range(0, count($dias) - 1);
                shuffle($dias_indices);
                $dias_seleccionados = array_slice($dias_indices, 0, $num_dias);

                $horario = $horarios[array_rand($horarios)];

                foreach ($dias_seleccionados as $dia_index) {
                    $stmt = $conexion->prepare("
                        INSERT INTO actividad_horarios (actividad_id, dia_semana, hora_inicio, hora_fin) 
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $actividad_id,
                        $dias[$dia_index],
                        $horario['inicio'],
                        $horario['fin']
                    ]);
                }

                // Asignar a colegios de forma segura
                $num_colegios = rand(2, count($colegios));
                $indices_colegios = range(0, count($colegios) - 1);
                shuffle($indices_colegios);
                $colegios_seleccionados = array_slice($indices_colegios, 0, $num_colegios);

                foreach ($colegios_seleccionados as $colegio_index) {
                    $stmt = $conexion->prepare("
                        INSERT INTO colegio_actividad (actividad_id, colegio_id, cupo_maximo, activo) 
                        VALUES (?, ?, ?, 1)
                    ");
                    $stmt->execute([
                        $actividad_id,
                        $colegios[$colegio_index]['id'],
                        rand(15, 25)
                    ]);
                }

                $actividades_generadas++;
            } catch (PDOException $e) {
                throw new Exception('Error al insertar actividad: ' . $e->getMessage());
            }
        }
    }

    $conexion->commit();
    echo "<div style='font-family: Arial, sans-serif; margin: 20px; padding: 20px; background-color: #e8f5e9; border-radius: 5px;'>";
    echo "<h2>Generación Completada</h2>";
    echo "<p>Se han generado <strong>{$actividades_generadas}</strong> actividades exitosamente.</p>";
    echo "<p>Detalles:</p>";
    echo "<ul>";
    foreach ($actividades_por_nivel as $nivel => $acts) {
        echo "<li>{$nivel}: " . count($acts) . " tipos de actividades</li>";
    }
    echo "</ul>";
    echo "<p><a href='?page=actividades' style='color: #4CAF50; text-decoration: none;'>Ver todas las actividades</a></p>";
    echo "</div>";

} catch (Exception $e) {
    $conexion->rollBack();
    echo "<div style='font-family: Arial, sans-serif; margin: 20px; padding: 20px; background-color: #ffebee; border-radius: 5px;'>";
    echo "<h2>Error</h2>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    if (isset($e->getLine)) {
        echo "<p>Línea: " . $e->getLine() . "</p>";
    }
    echo "</div>";
}
?>
