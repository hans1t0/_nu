<?php
require_once '../../database/DatabaseConnectors.php';
require_once 'classes/ExtraescolaresManager.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Comentado temporalmente para desarrollo
/*
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../../login.php');
    exit;
}
*/

$action = $_GET['action'] ?? 'listar';
$id = $_GET['id'] ?? null;
$mensaje = '';
$tipo_mensaje = '';

// Manejar acciones de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $extraManager = new ExtraescolaresManager();
    
    if (isset($_POST['guardar_actividad']) || isset($_POST['guardar_y_siguiente'])) {
        // Datos de la actividad
        $dias = isset($_POST['dias']) ? implode(', ', $_POST['dias']) : '';
        $horario = $_POST['horario'] ?? '17:00-18:00';
        
        // Convertir precio a formato correcto
        $precio = str_replace(',', '.', $_POST['precio']);
        $precio = number_format((float)$precio, 2, '.', '');
        
        $datos = [
            'actividad' => $_POST['actividad'] ?? '',
            'detalle_actividad' => $_POST['detalle_actividad'] ?? '',  // Cambiado desde descripcion
            'precio' => $precio,
            'max_alumnos' => $_POST['max_alumnos'] ?? 20,
            'horario' => $dias . ' ' . $horario,
            'activa' => isset($_POST['activa']) ? 1 : 0,
            'colegios' => $_POST['colegios'] ?? []
        ];
        
        if (isset($_POST['actividad_id']) && !empty($_POST['actividad_id'])) {
            // Actualizar actividad existente
            $datos['id'] = $_POST['actividad_id'];
            $resultado = $extraManager->actualizarActividad($datos);
            
            if ($resultado) {
                $mensaje = 'Actividad actualizada correctamente';
                $tipo_mensaje = 'success';
                
                // Modificar redirección para incluir colegio_id si existe
                if (isset($_POST['colegio_id']) && !empty($_POST['colegio_id'])) {
                    $colegio_id = intval($_POST['colegio_id']);
                    header("Location: actividades/colegio_{$colegio_id}/index.php?mensaje=$mensaje&tipo=$tipo_mensaje");
                    exit;
                }
                
                if (isset($_POST['guardar_y_siguiente'])) {
                    $siguiente_id = $_POST['guardar_y_siguiente'];
                    header("Location: actividades.php?action=editar&id={$siguiente_id}&mensaje={$mensaje}&tipo={$tipo_mensaje}");
                    exit;
                }
                
                header("Location: actividades.php?mensaje=$mensaje&tipo=$tipo_mensaje");
                exit;
            } else {
                $mensaje = 'Error al actualizar la actividad';
                $tipo_mensaje = 'danger';
            }
        } else {
            // Crear nueva actividad
            $resultado = $extraManager->crearActividad($datos);
            
            if ($resultado) {
                $mensaje = 'Actividad creada correctamente';
                $tipo_mensaje = 'success';
                header("Location: actividades.php?mensaje=$mensaje&tipo=$tipo_mensaje");
                exit;
            } else {
                $mensaje = 'Error al crear la actividad';
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // Eliminar actividad
    if (isset($_POST['eliminar_actividad'])) {
        $actividad_id = $_POST['actividad_id'] ?? 0;
        $resultado = $extraManager->eliminarActividad($actividad_id);
        
        if ($resultado) {
            $mensaje = 'Actividad eliminada correctamente';
            $tipo_mensaje = 'success';
            header("Location: actividades.php?mensaje=$mensaje&tipo=$tipo_mensaje");
            exit;
        } else {
            $mensaje = 'Error al eliminar la actividad';
            $tipo_mensaje = 'danger';
        }
    }
}

// Manejar notificaciones
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipo_mensaje = $_GET['tipo'] ?? 'info';
}

// Título de la página
$titulo_pagina = 'Gestión de Actividades';

// Incluir el header
include_once __DIR__ . '/../../includes/header.php';
?>

<!-- Estilos y fuentes -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: #f9fafb;
}
.table-container {
    border-radius: 10px;
    overflow: hidden;
}
.custom-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}
.badge-status-active {
    background-color: #d1e7dd;
    color: #0f5132;
}
.badge-status-inactive {
    background-color: #f8d7da;
    color: #842029;
}
.select2-container--default .select2-selection--multiple {
    border-color: #ced4da;
    padding: 0.25rem 0.5rem;
    min-height: 38px;
}
.table-avatar {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    font-weight: 600;
}
.activities-list .list-group-item {
    font-size: 0.875rem;
}
.bg-primary-subtle {
    background-color: #e9ecff;
}
.text-primary {
    color: #0d6efd!important;
}
.btn-action-group {
    display: flex;
    gap: 1rem;
    align-items: center;
}
.btn-action-group .btn {
    padding: 0.5rem 1.5rem;
    font-weight: 500;
}
.btn-next {
    margin-left: auto;
}
</style>

<div class="container-fluid px-4 py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="index.php">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Actividades</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?= $titulo_pagina ?></h1>
        <a href="actividades.php?action=nueva" class="btn btn-success px-4 rounded-pill">
            <i class="bi bi-plus-lg me-2"></i> Nueva Actividad
        </a>
    </div>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($action === 'listar'): ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <?php
        try {
            $colegios = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT c.*, 
                        COUNT(DISTINCT ca.actividad_id) as total_actividades,
                        GROUP_CONCAT(a.actividad SEPARATOR '|') as actividades_nombres
                 FROM colegios c
                 LEFT JOIN colegio_actividad ca ON c.id = ca.colegio_id AND ca.activo = 1
                 LEFT JOIN actividades a ON ca.actividad_id = a.id AND a.activa = 1
                 GROUP BY c.id 
                 ORDER BY c.nombre ASC"
            );
            
            if (count($colegios) > 0) {
                foreach ($colegios as $colegio) {
                    $actividades = $colegio['actividades_nombres'] ? explode('|', $colegio['actividades_nombres']) : [];
                    ?>
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="table-avatar bg-primary-subtle text-primary me-3">
                                        <?= strtoupper(substr($colegio['nombre'], 0, 2)) ?>
                                    </div>
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($colegio['nombre']) ?></h5>
                                </div>
                                
                                <div class="d-flex align-items-center mb-3">
                                    <div class="badge bg-success rounded-pill">
                                        <?= $colegio['total_actividades'] ?> Actividades
                                    </div>
                                </div>
                                
                                <?php if (!empty($actividades)): ?>
                                <div class="activities-list">
                                    <small class="text-muted d-block mb-2">Actividades asignadas:</small>
                                    <ul class="list-group list-group-flush">
                                        <?php 
                                        $maxActividades = 3;
                                        $totalRestantes = count($actividades) - $maxActividades;
                                        
                                        foreach (array_slice($actividades, 0, $maxActividades) as $actividad): ?>
                                            <li class="list-group-item px-0 py-1 border-0">
                                                <i class="bi bi-check2 text-success me-2"></i>
                                                <?= htmlspecialchars($actividad) ?>
                                            </li>
                                        <?php endforeach; 
                                        
                                        if ($totalRestantes > 0): ?>
                                            <li class="list-group-item px-0 py-1 border-0 text-muted">
                                                <i class="bi bi-three-dots me-2"></i>
                                                Y <?= $totalRestantes ?> más...
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <?php else: ?>
                                <p class="text-muted small mb-0">No hay actividades asignadas</p>
                                <?php endif; ?>

                                <div class="card-footer bg-transparent border-0 p-0 mt-3">
                                    <a href="gestionar-actividades.php?colegio_id=<?= $colegio['id'] ?>" 
                                       class="btn btn-outline-primary btn-sm w-100">
                                        <i class="bi bi-gear me-2"></i>
                                        Gestionar Actividades
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No hay colegios registrados</div></div>';
            }
        } catch (Exception $e) {
            echo '<div class="col-12"><div class="alert alert-danger">Error al cargar los colegios: ' . $e->getMessage() . '</div></div>';
        }
        ?>
    </div>
    <?php endif; ?>
    
    <?php if ($action === 'nueva' || $action === 'editar'): ?>
    <!-- Formulario para nueva/editar actividad -->
    <?php
    $actividad = [
        'id' => '',
        'actividad' => '',
        'detalle_actividad' => '',
        'precio' => '0.00',
        'max_alumnos' => '20',
        'horario' => '',
        'activa' => 1
    ];
    
    // Procesar horario actual si existe
    $diasSeleccionados = [];
    $horarioSeleccionado = '17:00-18:00';
    
    if ($action === 'editar' && $id && !empty($actividad['horario'])) {
        $partes = explode(' ', $actividad['horario']);
        if (count($partes) > 1) {
            // Extraer los días (pueden estar separados por ',' o ', ')
            $diasString = str_replace(' y ', ', ', $partes[0]); // Convertir "y" a coma
            $diasSeleccionados = array_map('trim', explode(',', $diasString));
            
            // Extraer el horario (último elemento)
            $horarioTemp = end($partes);
            if (preg_match('/\d{2}:\d{2}(-|a)\d{2}:\d{2}/', $horarioTemp)) {
                $horarioSeleccionado = $horarioTemp;
            }
        }
    }
    
    // Obtener colegios para selector múltiple
    $todosColegios = [];
    $colegiosAsignados = [];
    
    try {
        $todosColegios = DatabaseConnectors::executeQuery('extraescolares', 
            "SELECT id, nombre FROM colegios ORDER BY nombre"
        );
    } catch (Exception $e) {
        $mensaje = "Error al cargar los colegios: " . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
    
    if ($action === 'editar' && $id) {
        try {
            // Obtener la actividad actual
            $resultado = DatabaseConnectors::executeQuery('extraescolares',
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
            
            if (!empty($resultado)) {
                $actividad = $resultado[0];
                $actividad['precio'] = $actividad['precio_actual'] ?? 0;
                
                // Procesar días seleccionados
                $diasSeleccionados = !empty($actividad['dias_semana']) ? explode(',', $actividad['dias_semana']) : [];
                
                // Procesar horario
                $horarioSeleccionado = !empty($actividad['horario_formateado']) ? $actividad['horario_formateado'] : '17:00-18:00';
                
                // Obtener siguiente actividad del mismo colegio
                $siguiente = DatabaseConnectors::executeQuery('extraescolares',
                    "SELECT a2.id 
                     FROM actividades a2 
                     INNER JOIN colegio_actividad ca2 ON a2.id = ca2.actividad_id 
                     WHERE ca2.colegio_id = (
                         SELECT ca1.colegio_id 
                         FROM colegio_actividad ca1 
                         WHERE ca1.actividad_id = ? 
                         LIMIT 1
                     ) 
                     AND a2.id > ? 
                     ORDER BY a2.id ASC 
                     LIMIT 1",
                    [$id, $id]
                );
                
                $siguiente_id = !empty($siguiente) ? $siguiente[0]['id'] : null;
                
                // Obtener colegios asignados a esta actividad
                $asignaciones = DatabaseConnectors::executeQuery('extraescolares',
                    "SELECT colegio_id FROM colegio_actividad 
                     WHERE actividad_id = ? AND activo = 1",
                    [$id]
                );
                
                foreach ($asignaciones as $asignacion) {
                    $colegiosAsignados[] = $asignacion['colegio_id'];
                }
            }
        } catch (Exception $e) {
            $mensaje = 'Error al cargar los datos de la actividad: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
    ?>
    
    <div class="card shadow-sm border-0">
        <div class="custom-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold"><?= ($action === 'nueva') ? 'Nueva Actividad' : 'Editar Actividad' ?></h5>
            <?php 
            // Determinar la URL de retorno basada en colegio_id
            $return_url = isset($_GET['colegio_id']) ? 
                "gestionar-actividades.php?colegio_id=" . intval($_GET['colegio_id']) : 
                "actividades.php";
            ?>
            <a href="<?= $return_url ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Volver
            </a>
        </div>
        <div class="card-body">
            <form method="post" action="actividades.php">
                <?php if ($actividad['id']): ?>
                <input type="hidden" name="actividad_id" value="<?= htmlspecialchars($actividad['id']) ?>">
                <?php endif; ?>
                
                <?php if (isset($_GET['colegio_id'])): ?>
                <input type="hidden" name="colegio_id" value="<?= intval($_GET['colegio_id']) ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="actividad" class="form-label">Nombre de la Actividad <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="actividad" name="actividad" value="<?= htmlspecialchars($actividad['actividad']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label for="precio" class="form-label">Precio (€) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="precio" 
                                   name="precio" 
                                   step="0.01" 
                                   min="0" 
                                   value="<?= number_format((float)$actividad['precio'], 2, '.', '') ?>" 
                                   required>
                            <span class="input-group-text">€</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="max_alumnos" class="form-label">Plazas máximas <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="max_alumnos" name="max_alumnos" min="1" value="<?= htmlspecialchars($actividad['max_alumnos']) ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="detalle_actividad" class="form-label">Detalles de la Actividad</label>
                    <textarea class="form-control" id="detalle_actividad" name="detalle_actividad" rows="3"><?= htmlspecialchars($actividad['detalle_actividad'] ?? '') ?></textarea>
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
                
                <div class="mb-3">
                    <label for="colegios" class="form-label">Asignar a Colegios</label>
                    <select class="form-select select2-multiple" id="colegios" name="colegios[]" multiple="multiple" data-placeholder="Selecciona los colegios">
                        <?php foreach ($todosColegios as $colegio): ?>
                        <option value="<?= $colegio['id'] ?>" <?= in_array($colegio['id'], $colegiosAsignados) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($colegio['nombre']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">Selecciona los colegios donde se impartirá esta actividad</div>
                </div>
                
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="activa" name="activa" <?= $actividad['activa'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activa">Actividad activa</label>
                </div>
                
                <div class="mt-4 btn-action-group">
                    <div>
                        <button type="submit" name="guardar_actividad" class="btn btn-success">
                            <i class="bi bi-check-lg me-2"></i>Guardar
                        </button>
                        <a href="actividades.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                    </div>
                    <?php if (isset($siguiente_id)): ?>
                    <button type="submit" name="guardar_y_siguiente" class="btn btn-primary btn-next" value="<?= $siguiente_id ?>">
                        Guardar y Siguiente <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
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

<?php
// Incluir footer
include_once __DIR__ . '/../../includes/footer.php';
?>
