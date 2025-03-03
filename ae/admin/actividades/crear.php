<?php
require_once '../conexion.php';

$colegio_id = isset($_GET['colegio']) ? (int)$_GET['colegio'] : 0;

// Obtener lista de cursos para el select
try {
    $stmt = $conexion->prepare("SELECT id, nombre FROM cursos ORDER BY id");
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener nombre del colegio
    $stmt = $conexion->prepare("SELECT nombre FROM colegios WHERE id = ?");
    $stmt->execute([$colegio_id]);
    $colegio = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

// Días de la semana para el select
$dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conexion->beginTransaction();

        // Insertar actividad principal
        $stmt = $conexion->prepare("INSERT INTO actividades 
            (actividad, detalle_actividad, desde, hasta, max_alumnos, activa, link) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $_POST['actividad'],
            $_POST['detalle_actividad'],
            $_POST['desde'],
            $_POST['hasta'],
            $_POST['max_alumnos'],
            isset($_POST['activa']) ? 1 : 0,
            $_POST['link']
        ]);

        $actividad_id = $conexion->lastInsertId();

        // Insertar relación colegio-actividad
        $stmt = $conexion->prepare("INSERT INTO colegio_actividad 
            (colegio_id, actividad_id, cupo_maximo, cupo_actual) 
            VALUES (?, ?, ?, 0)");
        
        $stmt->execute([
            $colegio_id,
            $actividad_id,
            $_POST['max_alumnos']
        ]);

        // Insertar horarios
        $stmt = $conexion->prepare("INSERT INTO actividad_horarios 
            (actividad_id, dia_semana, hora_inicio, hora_fin) 
            VALUES (?, ?, ?, ?)");

        for($i = 0; $i < count($_POST['dia_semana']); $i++) {
            if(!empty($_POST['dia_semana'][$i]) && !empty($_POST['hora_inicio'][$i])) {
                $stmt->execute([
                    $actividad_id,
                    $_POST['dia_semana'][$i],
                    $_POST['hora_inicio'][$i],
                    $_POST['hora_fin'][$i]
                ]);
            }
        }

        // Insertar precio inicial
        if(!empty($_POST['precio'])) {
            $stmt = $conexion->prepare("INSERT INTO actividades_precio 
                (id_actividad, precio, fecha) 
                VALUES (?, ?, NOW())");
            $stmt->execute([$actividad_id, $_POST['precio']]);
        }

        $conexion->commit();
        header("Location: ?page=actividades&colegio=" . $colegio_id);
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        echo "<div class='alert alert-danger'>Error al crear actividad: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">
        Nueva Actividad en <?= htmlspecialchars($colegio['nombre']) ?>
        <a href="?page=actividades&colegio=<?= $colegio_id ?>" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </h2>

    <form method="POST" class="row g-3">
        <!-- Información básica -->
        <div class="col-md-6">
            <label class="form-label">Nombre de la Actividad</label>
            <input type="text" class="form-control" name="actividad" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Desde Curso</label>
            <select class="form-select" name="desde" required>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['id'] ?>">
                        <?= htmlspecialchars($curso['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Hasta Curso</label>
            <select class="form-select" name="hasta" required>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['id'] ?>">
                        <?= htmlspecialchars($curso['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Descripción</label>
            <textarea class="form-control" name="detalle_actividad" rows="3"></textarea>
        </div>

        <div class="col-md-3">
            <label class="form-label">Cupo Máximo</label>
            <input type="number" class="form-control" name="max_alumnos" required>
        </div>

        <div class="col-md-3">
            <label class="form-label">Precio (€)</label>
            <input type="number" step="0.01" class="form-control" name="precio" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Link Externo</label>
            <input type="url" class="form-control" name="link">
        </div>

        <!-- Horarios -->
        <div class="col-12">
            <h4>Horarios</h4>
            <div id="horarios-container">
                <div class="row g-2 mb-2 horario-row">
                    <div class="col-md-4">
                        <select class="form-select" name="dia_semana[]" required>
                            <?php foreach ($dias_semana as $dia): ?>
                                <option value="<?= $dia ?>"><?= $dia ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="time" class="form-control" name="hora_inicio[]" required>
                    </div>
                    <div class="col-md-3">
                        <input type="time" class="form-control" name="hora_fin[]" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger" onclick="eliminarHorario(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary mt-2" onclick="agregarHorario()">
                <i class="bi bi-plus-lg"></i> Agregar Horario
            </button>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="activa" checked>
                <label class="form-check-label">
                    Actividad Activa
                </label>
            </div>
        </div>

        <div class="col-12">
            <hr>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Crear Actividad
            </button>
        </div>
    </form>
</div>

<script>
function agregarHorario() {
    const template = `
        <div class="row g-2 mb-2 horario-row">
            <div class="col-md-4">
                <select class="form-select" name="dia_semana[]" required>
                    <?php foreach ($dias_semana as $dia): ?>
                        <option value="<?= $dia ?>"><?= $dia ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="time" class="form-control" name="hora_inicio[]" required>
            </div>
            <div class="col-md-3">
                <input type="time" class="form-control" name="hora_fin[]" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger" onclick="eliminarHorario(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('horarios-container').insertAdjacentHTML('beforeend', template);
}

function eliminarHorario(button) {
    if (document.querySelectorAll('.horario-row').length > 1) {
        button.closest('.horario-row').remove();
    } else {
        alert('Debe haber al menos un horario');
    }
}
</script>
