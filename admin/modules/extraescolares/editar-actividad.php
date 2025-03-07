<?php
require_once '../../database/DatabaseConnectors.php';
require_once 'classes/ExtraescolaresManager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Procesar formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_actividad'])) {
    $extraManager = new ExtraescolaresManager();
    
    // Preparar datos
    $dias = isset($_POST['dias']) ? implode(', ', $_POST['dias']) : '';
    $horario = $_POST['horario'] ?? '17:00-18:00';
    $precio = str_replace(',', '.', $_POST['precio']);
    $precio = number_format((float)$precio, 2, '.', '');
    
    $datos = [
        'id' => $_POST['actividad_id'],
        'actividad' => $_POST['actividad'] ?? '',
        'detalle_actividad' => $_POST['detalle_actividad'] ?? '',
        'precio' => $precio,
        'max_alumnos' => $_POST['max_alumnos'] ?? 20,
        'horario' => $dias . ' ' . $horario,
        'activa' => isset($_POST['activa']) ? 1 : 0,
        'colegios' => $_POST['colegios'] ?? []
    ];
    
    if ($extraManager->actualizarActividad($datos)) {
        $mensaje = 'Actividad actualizada correctamente';
        $tipo_mensaje = 'success';
        
        // Redirigir según el origen
        if (isset($_POST['colegio_id']) && !empty($_POST['colegio_id'])) {
            header("Location: gestionar-actividades.php?colegio_id=" . intval($_POST['colegio_id']) . "&mensaje=$mensaje&tipo=$tipo_mensaje");
        } else {
            header("Location: actividades.php?mensaje=$mensaje&tipo=$tipo_mensaje");
        }
        exit;
    } else {
        $mensaje = 'Error al actualizar la actividad';
        $tipo_mensaje = 'danger';
    }
}

$id = $_GET['id'] ?? 0;
$colegio_id = $_GET['colegio_id'] ?? null;
$mensaje = '';
$tipo_mensaje = '';

try {
    // Obtener la actividad actual con sus horarios
    $actividad = DatabaseConnectors::executeQuery('extraescolares',
        "SELECT a.*, 
                (SELECT ap.precio 
                 FROM actividades_precio ap 
                 WHERE ap.id_actividad = a.id 
                 ORDER BY ap.fecha DESC 
                 LIMIT 1) as precio_actual,
                (SELECT GROUP_CONCAT(ah.dia_semana ORDER BY FIELD(ah.dia_semana, 'Lunes','Martes','Miércoles','Jueves','Viernes')) 
                 FROM actividad_horarios ah 
                 WHERE ah.actividad_id = a.id) as dias_semana,
                (SELECT DISTINCT CONCAT(TIME_FORMAT(ah.hora_inicio, '%H:%i'),'-',TIME_FORMAT(ah.hora_fin, '%H:%i'))
                 FROM actividad_horarios ah 
                 WHERE ah.actividad_id = a.id 
                 LIMIT 1) as horario_formateado
         FROM actividades a 
         WHERE a.id = ?",
        [$id]
    );
    
    if (empty($actividad)) {
        header('Location: actividades.php');
        exit;
    }
    
    $actividad = $actividad[0];
    $actividad['precio'] = $actividad['precio_actual'] ?? 0;
    
    // Procesar días seleccionados
    $diasSeleccionados = !empty($actividad['dias_semana']) ? explode(',', $actividad['dias_semana']) : [];
    
    // Procesar horario
    $horarioSeleccionado = !empty($actividad['horario_formateado']) ? $actividad['horario_formateado'] : '17:00-18:00';
    
    // Obtener colegios asignados
    $colegiosAsignados = DatabaseConnectors::executeQuery('extraescolares',
        "SELECT colegio_id FROM colegio_actividad 
         WHERE actividad_id = ? AND activo = 1",
        [$id]
    );
    
    $colegiosAsignados = array_map(function($row) {
        return $row['colegio_id'];
    }, $colegiosAsignados);
    
    // Obtener todos los colegios para el selector
    $todosColegios = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT id, nombre FROM colegios ORDER BY nombre"
    );
    
} catch (Exception $e) {
    $mensaje = 'Error al cargar los datos: ' . $e->getMessage();
    $tipo_mensaje = 'danger';
}

$titulo_pagina = 'Editar Actividad';
include_once '../../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../../index.php">Panel</a></li>
            <li class="breadcrumb-item"><a href="actividades.php">Actividades</a></li>
            <?php if ($colegio_id): ?>
            <li class="breadcrumb-item">
                <a href="gestionar-actividades.php?colegio_id=<?= $colegio_id ?>">
                    Gestión
                </a>
            </li>
            <?php endif; ?>
            <li class="breadcrumb-item active">Editar</li>
        </ol>
    </nav>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold">
                <?= htmlspecialchars($actividad['actividad']) ?>
            </h5>
        </div>
        <div class="card-body">
            <form method="post" action="editar-actividad.php">
                <input type="hidden" name="actividad_id" value="<?= $actividad['id'] ?>">
                <?php if ($colegio_id): ?>
                <input type="hidden" name="colegio_id" value="<?= $colegio_id ?>">
                <?php endif; ?>

                <!-- Campos del formulario -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="actividad" class="form-label">Nombre de la Actividad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="actividad" name="actividad" 
                               value="<?= htmlspecialchars($actividad['actividad']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="precio" class="form-label">Precio (€) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="precio" name="precio" 
                                   step="0.01" min="0" 
                                   value="<?= number_format((float)$actividad['precio'], 2, '.', '') ?>" required>
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="max_alumnos" class="form-label">Plazas máximas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="max_alumnos" name="max_alumnos" 
                               min="1" value="<?= htmlspecialchars($actividad['max_alumnos']) ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="detalle_actividad" class="form-label">Detalles de la Actividad</label>
                    <textarea class="form-control" id="detalle_actividad" name="detalle_actividad" rows="3"><?= htmlspecialchars($actividad['detalle_actividad']) ?></textarea>
                    <div class="form-text">Información adicional sobre la actividad (opcional)</div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Días de la semana</label>
                        <div class="d-flex flex-wrap gap-3">
                            <?php
                            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
                            foreach ($dias as $dia): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="dias[]" 
                                       value="<?= $dia ?>" 
                                       id="dia_<?= $dia ?>"
                                       <?= in_array($dia, $diasSeleccionados) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="dia_<?= $dia ?>">
                                    <?= $dia ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Horario</label>
                        <select class="form-select" name="horario">
                            <option value="17:00-18:00" <?= $horarioSeleccionado === '17:00-18:00' ? 'selected' : '' ?>>
                                17:00 - 18:00
                            </option>
                            <option value="17:00-18:30" <?= $horarioSeleccionado === '17:00-18:30' ? 'selected' : '' ?>>
                                17:00 - 18:30
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 btn-action-group">
                    <div>
                        <button type="submit" name="guardar_actividad" class="btn btn-success">
                            <i class="bi bi-check-lg me-2"></i>Guardar Cambios
                        </button>
                        <a href="<?= $colegio_id ? "gestionar-actividades.php?colegio_id=$colegio_id" : 'actividades.php' ?>" 
                           class="btn btn-outline-secondary ms-2">
                            Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Select2 para el selector de colegios -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: $(this).data('placeholder'),
        allowClear: true
    });
});
</script>

<?php include_once '../../includes/footer.php'; ?>
