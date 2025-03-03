<?php
require_once dirname(__FILE__) . '/../database.php';

$actividad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$colegio_id = isset($_GET['colegio']) ? (int)$_GET['colegio'] : 0;

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Iniciar transacción
        $conexion->beginTransaction();

        // Actualizar tabla actividades
        $stmt = $conexion->prepare("
            UPDATE actividades 
            SET actividad = ?, 
                desde = ?,
                hasta = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $_POST['nombre'],
            $_POST['nivel_min'],
            $_POST['nivel_max'],
            $actividad_id
        ]);

        // Actualizar cupos en colegio_actividad
        $stmt = $conexion->prepare("
            UPDATE colegio_actividad 
            SET cupo_maximo = ?
            WHERE actividad_id = ? AND colegio_id = ?
        ");
        $stmt->execute([
            $_POST['cupo'],
            $actividad_id,
            $colegio_id
        ]);

        // Actualizar precio si ha cambiado
        $stmt = $conexion->prepare("
            INSERT INTO actividades_precio (id_actividad, precio, fecha) 
            VALUES (?, ?, CURRENT_DATE)
        ");
        $stmt->execute([
            $actividad_id,
            $_POST['precio']
        ]);

        // Eliminar horarios existentes
        $stmt = $conexion->prepare("DELETE FROM actividad_horarios WHERE actividad_id = ?");
        $stmt->execute([$actividad_id]);

        // Insertar nuevos horarios
        $stmt = $conexion->prepare("
            INSERT INTO actividad_horarios (actividad_id, dia_semana, hora_inicio, hora_fin)
            VALUES (?, ?, ?, ?)
        ");
        
        foreach ($_POST['horarios'] as $horario) {
            if (!empty($horario['dia']) && !empty($horario['inicio']) && !empty($horario['fin'])) {
                $stmt->execute([
                    $actividad_id,
                    $horario['dia'],
                    $horario['inicio'],
                    $horario['fin']
                ]);
            }
        }

        $conexion->commit();
        header("Location: ?page=actividades&colegio=" . $colegio_id);
        exit;

    } catch (PDOException $e) {
        $conexion->rollBack();
        $error = "Error al actualizar: " . $e->getMessage();
    }
}

// Obtener datos actuales de la actividad
try {
    // Consulta principal
    $stmt = $conexion->prepare("
        SELECT a.*, 
               ca.cupo_maximo,
               (SELECT precio 
                FROM actividades_precio 
                WHERE id_actividad = a.id 
                ORDER BY fecha DESC LIMIT 1) as precio_actual
        FROM actividades a
        JOIN colegio_actividad ca ON a.id = ca.actividad_id
        WHERE a.id = ? AND ca.colegio_id = ?
    ");
    $stmt->execute([$actividad_id, $colegio_id]);
    $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener horarios
    $stmt = $conexion->prepare("
        SELECT * FROM actividad_horarios 
        WHERE actividad_id = ?
        ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')
    ");
    $stmt->execute([$actividad_id]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Modificar la consulta de cursos para ordenar por nombre en lugar de orden
    $stmt = $conexion->prepare("SELECT id, nombre FROM cursos ORDER BY nombre");
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar los datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

if (!$actividad) {
    echo "<div class='alert alert-danger'>Actividad no encontrada</div>";
    exit;
}
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">
        Editar Actividad
        <a href="?page=actividades&colegio=<?= $colegio_id ?>" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <!-- Datos básicos -->
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Actividad</label>
                        <input type="text" class="form-control" name="nombre" 
                               value="<?= htmlspecialchars($actividad['actividad']) ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Precio (€)</label>
                            <input type="number" class="form-control" name="precio" 
                                   value="<?= $actividad['precio_actual'] ?>" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cupo Máximo</label>
                            <input type="number" class="form-control" name="cupo" 
                                   value="<?= $actividad['cupo_maximo'] ?>" min="1" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nivel Mínimo</label>
                            <select class="form-select" name="nivel_min" required>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id'] ?>" 
                                            <?= $curso['id'] == $actividad['desde'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($curso['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nivel Máximo</label>
                            <select class="form-select" name="nivel_max" required>
                                <?php foreach ($cursos as $curso): ?>
                                    <option value="<?= $curso['id'] ?>"
                                            <?= $curso['id'] == $actividad['hasta'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($curso['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Horarios -->
                    <div id="horarios-container">
                        <label class="form-label">Horarios</label>
                        <?php foreach ($horarios as $index => $horario): ?>
                            <div class="row mb-2 horario-row">
                                <div class="col-md-4">
                                    <select class="form-select" name="horarios[<?= $index ?>][dia]" required>
                                        <option value="">Seleccione día</option>
                                        <option value="Lunes" <?= $horario['dia_semana'] == 'Lunes' ? 'selected' : '' ?>>Lunes</option>
                                        <option value="Martes" <?= $horario['dia_semana'] == 'Martes' ? 'selected' : '' ?>>Martes</option>
                                        <option value="Miércoles" <?= $horario['dia_semana'] == 'Miércoles' ? 'selected' : '' ?>>Miércoles</option>
                                        <option value="Jueves" <?= $horario['dia_semana'] == 'Jueves' ? 'selected' : '' ?>>Jueves</option>
                                        <option value="Viernes" <?= $horario['dia_semana'] == 'Viernes' ? 'selected' : '' ?>>Viernes</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="time" class="form-select" name="horarios[<?= $index ?>][inicio]"
                                           value="<?= date('H:i', strtotime($horario['hora_inicio'])) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <input type="time" class="form-select" name="horarios[<?= $index ?>][fin]"
                                           value="<?= date('H:i', strtotime($horario['hora_fin'])) ?>" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-horario">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" id="add-horario" class="btn btn-outline-primary btn-sm mt-2">
                        <i class="bi bi-plus-lg"></i> Agregar Horario
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Guardar Cambios
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('add-horario').addEventListener('click', function() {
    const container = document.getElementById('horarios-container');
    const index = container.getElementsByClassName('horario-row').length;
    
    const template = `
        <div class="row mb-2 horario-row">
            <div class="col-md-4">
                <select class="form-select" name="horarios[${index}][dia]" required>
                    <option value="">Seleccione día</option>
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="time" class="form-select" name="horarios[${index}][inicio]" required>
            </div>
            <div class="col-md-3">
                <input type="time" class="form-select" name="horarios[${index}][fin]" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-horario">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', template);
});

document.addEventListener('click', function(e) {
    if (e.target.matches('.remove-horario') || e.target.closest('.remove-horario')) {
        e.target.closest('.horario-row').remove();
    }
});
</script>
