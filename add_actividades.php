<?php
include('conexion.php');

$colegios = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre")->fetchAll();
$cursos = $conexion->query("SELECT id, nombre, nivel, grado FROM cursos ORDER BY nivel, grado")->fetchAll();

// Agrupar cursos por nivel para mejor organización
$cursos_por_nivel = [];
foreach ($cursos as $curso) {
    $cursos_por_nivel[$curso['nivel']][] = $curso;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion->beginTransaction();

        // Insertar la actividad
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
            $_POST['grado_min'],
            $_POST['grado_max'],
            $_POST['descripcion'],
            $_POST['precio'],
            $_POST['duracion'],
            $_POST['cupo_maximo']
        ]);

        $actividad_id = $conexion->lastInsertId();

        // Insertar la asignación al colegio si se seleccionó uno
        if (!empty($_POST['colegio'])) {
            $stmt = $conexion->prepare("
                INSERT INTO colegio_actividad (
                    id_colegio, id_actividad, nivel,
                    grado_minimo, grado_maximo, horario,
                    precio, activa
                ) VALUES (?, ?, ?, ?, ?, ?, ?, true)
            ");

            $stmt->execute([
                $_POST['colegio'],
                $actividad_id,
                $_POST['nivel'],
                $_POST['grado_min'],
                $_POST['grado_max'],
                $_POST['horario'],
                $_POST['precio']
            ]);
        }

        // Redirección después de éxito
        $conexion->commit();
        header('Location: actividades.php?mensaje=' . urlencode('Actividad creada correctamente'));
        exit;
    } catch (Exception $e) {
        $conexion->rollBack();
        $error = "Error al crear la actividad: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Actividad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include('templates/navbar.php'); ?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle-fill me-2"></i>
                            Nueva Actividad
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row g-4">
                                <!-- Información básica -->
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-pencil-fill text-primary"></i>
                                        Nombre de la actividad
                                    </label>
                                    <input type="text" name="nombre" class="form-control form-control-lg" required>
                                    <div class="form-text">Nombre descriptivo de la actividad</div>
                                </div>

                                <!-- Selector de curso con agrupación -->
                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-mortarboard-fill text-primary"></i>
                                        Rango de Cursos
                                    </label>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Nivel</label>
                                            <select name="nivel" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <option value="Infantil">Infantil</option>
                                                <option value="Primaria">Primaria</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Curso Mínimo</label>
                                            <select name="grado_min" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <!-- Se llenará dinámicamente -->
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Curso Máximo</label>
                                            <select name="grado_max" class="form-select" required>
                                                <option value="">Seleccione...</option>
                                                <!-- Se llenará dinámicamente -->
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selector de horarios -->
                                <div class="col-md-12 mb-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-clock-fill text-primary"></i>
                                        Horarios Disponibles
                                    </label>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Días de la semana</label>
                                            <div class="border rounded p-3">
                                                <?php
                                                $dias = [
                                                    'lunes' => 'Lunes',
                                                    'martes' => 'Martes',
                                                    'miercoles' => 'Miércoles',
                                                    'jueves' => 'Jueves',
                                                    'viernes' => 'Viernes'
                                                ];
                                                foreach ($dias as $valor => $dia): ?>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="dias[]" value="<?= $valor ?>" 
                                                               id="dia_<?= $valor ?>">
                                                        <label class="form-check-label" for="dia_<?= $valor ?>">
                                                            <?= $dia ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <label class="form-label">Hora inicio</label>
                                                    <div class="input-group">
                                                        <input type="time" name="hora_inicio" class="form-control" 
                                                               required min="16:00" max="18:30">
                                                        <span class="input-group-text">
                                                            <i class="bi bi-clock"></i>
                                                        </span>
                                                    </div>
                                                    <div class="form-text">Entre 16:00 y 18:30</div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Hora fin</label>
                                                    <div class="input-group">
                                                        <input type="time" name="hora_fin" class="form-control" 
                                                               required min="16:00" max="18:30">
                                                        <span class="input-group-text">
                                                            <i class="bi bi-clock"></i>
                                                        </span>
                                                    </div>
                                                    <div class="form-text">Mínimo 30 minutos</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Colegios disponibles -->
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-building-fill text-primary"></i>
                                        Colegios disponibles
                                    </label>
                                    <div class="row g-3" id="colegios-container">
                                        <?php foreach ($colegios as $colegio): ?>
                                            <div class="col-md-4">
                                                <div class="form-check card h-100">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" 
                                                               name="colegios[]" value="<?= $colegio['id'] ?>"
                                                               id="colegio_<?= $colegio['id'] ?>">
                                                        <label class="form-check-label" for="colegio_<?= $colegio['id'] ?>">
                                                            <?= $colegio['nombre'] ?>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Detalles de la actividad -->
                                <div class="col-md-12">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-card-text text-primary"></i>
                                        Descripción
                                    </label>
                                    <textarea name="descripcion" class="form-control" rows="3"></textarea>
                                </div>

                                <!-- Configuración -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-currency-euro text-primary"></i>
                                        Precio
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="precio" class="form-control" 
                                               step="0.01" min="0" required>
                                        <span class="input-group-text">€</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-people-fill text-primary"></i>
                                        Cupo máximo
                                    </label>
                                    <input type="number" name="cupo_maximo" class="form-control" 
                                           min="1" value="20" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-clock-fill text-primary"></i>
                                        Duración
                                    </label>
                                    <input type="text" name="duracion" class="form-control" 
                                           placeholder="Ej: 2 horas semanales" required>
                                </div>

                                <!-- Campos ocultos -->
                                <input type="hidden" name="nivel" id="nivel_hidden">
                                <input type="hidden" name="grado_min" id="grado_min_hidden">
                                <input type="hidden" name="grado_max" id="grado_max_hidden">

                                <!-- Botones de acción -->
                                <div class="col-12 text-end">
                                    <hr>
                                    <a href="actividades.php" class="btn btn-outline-secondary btn-lg">
                                        <i class="bi bi-x-circle"></i>
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary btn-lg ms-2"></button></button>
                                        <i class="bi bi-save"></i>
                                        Guardar Actividad
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/actividades.js"></script>
    <script>
    const CURSOS_INFANTIL = 3;
    const CURSOS_PRIMARIA = 6;

    function actualizarCursos() {
        const nivel = document.querySelector('select[name="nivel"]').value;
        const gradoMin = document.querySelector('select[name="grado_min"]');
        const gradoMax = document.querySelector('select[name="grado_max"]');
        
        // Limpiar selectores
        gradoMin.innerHTML = '<option value="">Seleccione...</option>';
        gradoMax.innerHTML = '<option value="">Seleccione...</option>';
        
        if (!nivel) return;
        
        const maxCursos = nivel === 'Infantil' ? CURSOS_INFANTIL : CURSOS_PRIMARIA;
        
        // Llenar opciones
        for (let i = 1; i <= maxCursos; i++) {
            gradoMin.add(new Option(`${i}° de ${nivel}`, i));
            gradoMax.add(new Option(`${i}° de ${nivel}`, i));
        }
    }

    function validarHorario() {
        const inicio = document.querySelector('input[name="hora_inicio"]');
        const fin = document.querySelector('input[name="hora_fin"]');
        
        if (inicio.value && fin.value) {
            if (inicio.value >= fin.value) {
                alert('La hora de fin debe ser posterior a la hora de inicio');
                fin.value = '';
            }
        }
    }

    function generarHorario() {
        const dias = Array.from(document.querySelectorAll('input[name="dias[]"]:checked'))
                        .map(cb => cb.nextElementSibling.textContent.trim());
        const inicio = document.querySelector('input[name="hora_inicio"]').value;
        const fin = document.querySelector('input[name="hora_fin"]').value;
        
        if (dias.length === 0 || !inicio || !fin) return '';
        
        return `${dias.join(' y ')} de ${inicio} a ${fin}`;
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Evento para actualizar cursos cuando cambia el nivel
        document.querySelector('select[name="nivel"]')
                .addEventListener('change', actualizarCursos);
        
        // Validar horario
        document.querySelector('input[name="hora_fin"]')
                .addEventListener('change', validarHorario);
        
        // Actualizar horario cuando cambian los valores
        const horariosInputs = document.querySelectorAll('input[name="dias[]"], input[name="hora_inicio"], input[name="hora_fin"]');
        horariosInputs.forEach(input => {
            input.addEventListener('change', () => {
                const horario = generarHorario();
                if (horario) {
                    document.querySelector('input[name="duracion"]').value = horario;
                }
            });
        });
    });
    </script>
</body>
</html>
