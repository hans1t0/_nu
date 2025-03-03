<?php
// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión para gestión de usuarios
session_start();

// Configurar log de errores personalizado
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Cargar configuración de base de datos
try {
    require_once __DIR__ . '/../../database/DatabaseConnectors.php';
    require_once __DIR__ . '/../../auth/check_session.php';
    
    if (file_exists('./config.php')) {
        require_once './config.php';
    }
} catch (Exception $e) {
    error_log('Error en la carga de archivos: ' . $e->getMessage());
    echo '<div class="alert alert-danger">Error al cargar archivos de configuración: ' . $e->getMessage() . '</div>';
}

// Verificar conexión a la base de datos
try {
    // Asegurar que la clase DatabaseConnectors está inicializada
    if (method_exists('DatabaseConnectors', 'initialize')) {
        DatabaseConnectors::initialize();
    }
    
    $conn = DatabaseConnectors::getConnection('ludoteca');
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
    error_log('Error de conexión a BD: ' . $dbError);
}

// Definir variables
$fecha = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$centro_id = isset($_GET['centro_id']) ? $_GET['centro_id'] : '';
$success_message = $error_message = '';

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}

// Procesar registro de asistencia si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Inicio de transacción
        DatabaseConnectors::beginTransaction('ludoteca');
        
        // Si se recibió el arreglo de inscripciones activas
        if (isset($_POST['inscripciones']) && is_array($_POST['inscripciones'])) {
            // Primero eliminar los registros de asistencia para la fecha seleccionada
            // que estén en el listado de inscripciones enviadas
            $inscripciones = array_keys($_POST['inscripciones']);
            
            // Si hay inscripciones seleccionadas, eliminar sus registros previos
            if (!empty($inscripciones)) {
                $placeholders = implode(',', array_fill(0, count($inscripciones), '?'));
                $params = array_merge([$fecha], $inscripciones);
                
                DatabaseConnectors::executeNonQuery('ludoteca',
                    "DELETE FROM asistencia WHERE fecha = ? AND inscripcion_id IN ($placeholders)",
                    $params
                );
                
                // Insertar nuevos registros de asistencia
                foreach ($inscripciones as $inscripcion_id) {
                    // Verificar si el checkbox de presente está marcado
                    if (isset($_POST['presente'][$inscripcion_id]) && $_POST['presente'][$inscripcion_id] == '1') {
                        $hora_entrada = isset($_POST['hora_entrada'][$inscripcion_id]) && !empty($_POST['hora_entrada'][$inscripcion_id]) 
                                      ? $_POST['hora_entrada'][$inscripcion_id] 
                                      : null;
                        
                        $hora_salida = isset($_POST['hora_salida'][$inscripcion_id]) && !empty($_POST['hora_salida'][$inscripcion_id])
                                     ? $_POST['hora_salida'][$inscripcion_id] 
                                     : null;
                        
                        $observaciones = isset($_POST['observaciones'][$inscripcion_id])
                                      ? trim($_POST['observaciones'][$inscripcion_id])
                                      : null;
                        
                        // Verificar que al menos uno de los campos esté completo
                        if ($hora_entrada !== null || $hora_salida !== null) {
                            DatabaseConnectors::executeNonQuery('ludoteca',
                                "INSERT INTO asistencia (inscripcion_id, fecha, hora_entrada, hora_salida, observaciones) 
                                 VALUES (?, ?, ?, ?, ?)",
                                [$inscripcion_id, $fecha, $hora_entrada, $hora_salida, $observaciones]
                            );
                        }
                    }
                }
            }
            
            // Confirmar transacción
            DatabaseConnectors::commitTransaction('ludoteca');
            $success_message = "Registro de asistencia guardado correctamente.";
            
            // Redireccionar para evitar reenvío de formulario
            header("Location: asistencia.php?date=$fecha&centro_id=$centro_id&success=" . urlencode($success_message));
            exit();
        }
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        DatabaseConnectors::rollbackTransaction('ludoteca');
        $error_message = "Error al guardar el registro de asistencia: " . $e->getMessage();
        error_log($error_message);
    }
}

// Cargar lista de centros para el filtro
$centros = [];
if ($dbConnected) {
    try {
        $centros = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT id, nombre FROM centros WHERE activo = 1 ORDER BY nombre"
        );
    } catch (Exception $e) {
        error_log("Error al cargar la lista de centros: " . $e->getMessage());
    }
}

// Cargar alumnos inscritos para la fecha seleccionada
$inscripciones = [];
if ($dbConnected) {
    try {
        $query = "
            SELECT i.id, a.id as alumno_id, a.nombre, a.apellidos, a.alergias, 
                   c.nombre as centro_nombre, a.curso, h.hora_inicio, h.hora_fin,
                   (SELECT asi.hora_entrada FROM asistencia asi WHERE asi.inscripcion_id = i.id AND asi.fecha = ?) as hora_entrada,
                   (SELECT asi.hora_salida FROM asistencia asi WHERE asi.inscripcion_id = i.id AND asi.fecha = ?) as hora_salida,
                   (SELECT asi.observaciones FROM asistencia asi WHERE asi.inscripcion_id = i.id AND asi.fecha = ?) as observaciones
            FROM inscripciones i
            JOIN alumnos a ON i.alumno_id = a.id
            JOIN horarios h ON i.horario_id = h.id
            LEFT JOIN centros c ON a.centro_id = c.id
            WHERE i.estado = 'activa'
            AND ? BETWEEN i.fecha_inicio AND i.fecha_fin";
        
        $params = [$fecha, $fecha, $fecha, $fecha];
        
        // Añadir filtro por centro si se especificó
        if (!empty($centro_id)) {
            $query .= " AND a.centro_id = ?";
            $params[] = $centro_id;
        }
        
        // Ordenar por centro, apellidos y nombre
        $query .= " ORDER BY c.nombre, a.apellidos, a.nombre";
        
        $inscripciones = DatabaseConnectors::executeQuery('ludoteca', $query, $params);
    } catch (Exception $e) {
        $error_message = "Error al cargar la lista de alumnos: " . $e->getMessage();
        error_log($error_message);
    }
}

// Agrupar inscripciones por centro para visualización
$inscripcionesPorCentro = [];
foreach ($inscripciones as $inscripcion) {
    $centroNombre = $inscripcion['centro_nombre'] ?: 'Sin centro asignado';
    
    if (!isset($inscripcionesPorCentro[$centroNombre])) {
        $inscripcionesPorCentro[$centroNombre] = [];
    }
    
    $inscripcionesPorCentro[$centroNombre][] = $inscripcion;
}

// Calcular fechas para navegación
$fechaObj = new DateTime($fecha);
$fechaAnterior = clone $fechaObj;
$fechaAnterior->modify('-1 day');
$fechaSiguiente = clone $fechaObj;
$fechaSiguiente->modify('+1 day');

// Configurar título y migas de pan
$diaSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fechaFormateada = $diaSemana[$fechaObj->format('w')] . ', ' . $fechaObj->format('j') . ' de ' . $meses[$fechaObj->format('n')-1] . ' de ' . $fechaObj->format('Y');

$pageTitle = "Asistencia: $fechaFormateada";
$showBreadcrumb = true;
$breadcrumbItems = [
    ['text' => 'Ludoteca', 'url' => 'index.php', 'active' => false],
    ['text' => 'Asistencia', 'url' => 'asistencia.php', 'active' => false],
    ['text' => $fechaFormateada, 'url' => '', 'active' => true]
];

// Incluir plantilla de encabezado
$basePath = '../../';
include_once __DIR__ . '/../../templates/module_header.php';

// Verificar si hay mensajes de éxito en la URL
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1>
                    <i class="bi bi-calendar-check text-primary"></i> Control de Asistencia
                </h1>
                <div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al panel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$dbConnected): ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">Error de conexión</h4>
            <p>No se pudo conectar a la base de datos.</p>
            <hr>
            <p class="mb-0">Error: <?= $dbError ?></p>
        </div>
    <?php else: ?>
    
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>
        
        <!-- Selector de fecha y filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <h3 class="mb-0"><?= $fechaFormateada ?></h3>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end">
                            <a href="?date=<?= $fechaAnterior->format('Y-m-d') ?>&centro_id=<?= $centro_id ?>" class="btn btn-outline-primary me-2">
                                <i class="bi bi-arrow-left"></i> Día anterior
                            </a>
                            <button type="button" class="btn btn-outline-primary me-2" id="datePickerBtn">
                                <i class="bi bi-calendar3"></i> Cambiar fecha
                            </button>
                            <a href="?date=<?= $fechaSiguiente->format('Y-m-d') ?>&centro_id=<?= $centro_id ?>" class="btn btn-outline-primary">
                                Día siguiente <i class="bi bi-arrow-right"></i>
                            </a>
                            <input type="date" id="datePicker" class="d-none" value="<?= $fecha ?>">
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <form method="get" action="asistencia.php" id="filtro-form">
                            <input type="hidden" name="date" value="<?= $fecha ?>">
                            <div class="input-group">
                                <label class="input-group-text" for="centro_id">Filtrar por centro:</label>
                                <select class="form-select" id="centro_id" name="centro_id" onchange="document.getElementById('filtro-form').submit()">
                                    <option value="">Todos los centros</option>
                                    <?php foreach ($centros as $centro): ?>
                                        <option value="<?= $centro['id'] ?>" <?= $centro_id == $centro['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($centro['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de asistencia -->
        <form method="post" action="asistencia.php?date=<?= $fecha ?>&centro_id=<?= $centro_id ?>">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Registro de Asistencia</h5>
                            <div>
                                <button type="submit" class="btn btn-light btn-sm">
                                    <i class="bi bi-save"></i> Guardar Asistencia
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($inscripciones)): ?>
                                <div class="alert alert-info m-3">
                                    No hay alumnos inscritos para la fecha seleccionada<?= $centro_id ? " en el centro seleccionado" : "" ?>.
                                </div>
                            <?php else: ?>
                                <?php foreach ($inscripcionesPorCentro as $centroNombre => $inscripcionesCentro): ?>
                                    <div class="mb-4">
                                        <h5 class="border-bottom pb-2 pt-3 px-3">
                                            <i class="bi bi-building"></i> <?= htmlspecialchars($centroNombre) ?>
                                            <span class="badge bg-primary rounded-pill ms-2"><?= count($inscripcionesCentro) ?></span>
                                        </h5>
                                        
                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Alumno</th>
                                                        <th>Curso</th>
                                                        <th class="text-center">Presente</th>
                                                        <th>Hora Entrada</th>
                                                        <th>Hora Salida</th>
                                                        <th>Observaciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($inscripcionesCentro as $inscripcion): ?>
                                                        <?php
                                                        $presente = !empty($inscripcion['hora_entrada']) || !empty($inscripcion['hora_salida']);
                                                        $horaEntrada = $inscripcion['hora_entrada'] ? $inscripcion['hora_entrada'] : $inscripcion['hora_inicio'];
                                                        $horaSalida = $inscripcion['hora_salida'] ? $inscripcion['hora_salida'] : $inscripcion['hora_fin'];
                                                        ?>
                                                        <tr class="<?= $presente ? 'table-success' : '' ?>">
                                                            <td>
                                                                <input type="hidden" name="inscripciones[<?= $inscripcion['id'] ?>]" value="1">
                                                                <div class="fw-bold"><?= htmlspecialchars($inscripcion['apellidos'] . ', ' . $inscripcion['nombre']) ?></div>
                                                                <?php if (!empty($inscripcion['alergias'])): ?>
                                                                    <div class="text-danger small">
                                                                        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($inscripcion['alergias']) ?>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?= htmlspecialchars($inscripcion['curso']) ?></td>
                                                            <td class="text-center">
                                                                <div class="form-check form-check-inline">
                                                                    <input class="form-check-input present-checkbox" type="checkbox" 
                                                                          name="presente[<?= $inscripcion['id'] ?>]" 
                                                                          value="1" 
                                                                          id="presente_<?= $inscripcion['id'] ?>" 
                                                                          <?= $presente ? 'checked' : '' ?>>
                                                                    <label class="form-check-label" for="presente_<?= $inscripcion['id'] ?>"></label>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <input type="time" class="form-control form-control-sm time-input" 
                                                                       name="hora_entrada[<?= $inscripcion['id'] ?>]" 
                                                                       value="<?= substr($horaEntrada, 0, 5) ?>"
                                                                       <?= !$presente ? 'disabled' : '' ?>>
                                                            </td>
                                                            <td>
                                                                <input type="time" class="form-control form-control-sm time-input" 
                                                                       name="hora_salida[<?= $inscripcion['id'] ?>]" 
                                                                       value="<?= substr($horaSalida, 0, 5) ?>"
                                                                       <?= !$presente ? 'disabled' : '' ?>>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control form-control-sm"
                                                                       name="observaciones[<?= $inscripcion['id'] ?>]"
                                                                       value="<?= htmlspecialchars($inscripcion['observaciones'] ?? '') ?>"
                                                                       placeholder="Observaciones"
                                                                       <?= !$presente ? 'disabled' : '' ?>>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-save"></i> Guardar Asistencia
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Selector de fecha
        const datePicker = document.getElementById('datePicker');
        const datePickerBtn = document.getElementById('datePickerBtn');
        
        datePickerBtn.addEventListener('click', function() {
            datePicker.showPicker();
        });
        
        datePicker.addEventListener('change', function() {
            window.location.href = 'asistencia.php?date=' + this.value + '&centro_id=<?= $centro_id ?>';
        });
        
        // Manejo de checkbox de asistencia
        const checkboxes = document.querySelectorAll('.present-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('tr');
                const timeInputs = row.querySelectorAll('.time-input');
                const observacionesInput = row.querySelector('input[placeholder="Observaciones"]');
                
                if (this.checked) {
                    row.classList.add('table-success');
                    timeInputs.forEach(input => input.removeAttribute('disabled'));
                    if (observacionesInput) observacionesInput.removeAttribute('disabled');
                } else {
                    row.classList.remove('table-success');
                    timeInputs.forEach(input => input.setAttribute('disabled', 'disabled'));
                    if (observacionesInput) observacionesInput.setAttribute('disabled', 'disabled');
                }
            });
        });
    });
</script>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>
