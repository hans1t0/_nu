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

// Definir variables para el formulario
$id = $nombre = $apellidos = $fecha_nacimiento = $centro_id = $curso = $alergias = $medicacion = $observaciones = $activo = '';
$tutores = [];
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = $error_message = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $apellidos = isset($_POST['apellidos']) ? trim($_POST['apellidos']) : '';
    $fecha_nacimiento = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '';
    $centro_id = isset($_POST['centro_id']) ? $_POST['centro_id'] : '';
    $curso = isset($_POST['curso']) ? trim($_POST['curso']) : '';
    $alergias = isset($_POST['alergias']) ? trim($_POST['alergias']) : '';
    $medicacion = isset($_POST['medicacion']) ? trim($_POST['medicacion']) : '';
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;
    $tutorIds = isset($_POST['tutores']) ? $_POST['tutores'] : [];
    $tutorRelaciones = isset($_POST['relaciones']) ? $_POST['relaciones'] : [];

    // Validación básica
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($apellidos)) $errores[] = "Los apellidos son obligatorios";
    if (empty($fecha_nacimiento)) $errores[] = "La fecha de nacimiento es obligatoria";
    if (empty($centro_id)) $errores[] = "El centro es obligatorio";
    if (empty($curso)) $errores[] = "El curso es obligatorio";

    if (empty($errores)) {
        try {
            // Iniciar transacción
            DatabaseConnectors::beginTransaction('ludoteca');
            
            if ($action == 'new') {
                // Insertar nuevo alumno
                $query = "INSERT INTO alumnos (nombre, apellidos, fecha_nacimiento, centro_id, curso, alergias, medicacion, observaciones, activo) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$nombre, $apellidos, $fecha_nacimiento, $centro_id, $curso, $alergias, $medicacion, $observaciones, $activo];
                
                DatabaseConnectors::executeNonQuery('ludoteca', $query, $params);
                $alumnoId = $conn->lastInsertId();
            } else {
                // Actualizar alumno existente
                $query = "UPDATE alumnos 
                          SET nombre = ?, apellidos = ?, fecha_nacimiento = ?, centro_id = ?, curso = ?, alergias = ?, medicacion = ?, observaciones = ?, activo = ? 
                          WHERE id = ?";
                $params = [$nombre, $apellidos, $fecha_nacimiento, $centro_id, $curso, $alergias, $medicacion, $observaciones, $activo, $id];
                
                DatabaseConnectors::executeNonQuery('ludoteca', $query, $params);
                $alumnoId = $id;
                
                // Eliminar relaciones existentes con tutores
                DatabaseConnectors::executeNonQuery('ludoteca', "DELETE FROM alumno_tutor WHERE alumno_id = ?", [$alumnoId]);
            }
            
            // Insertar relaciones con tutores
            if (!empty($tutorIds)) {
                foreach ($tutorIds as $index => $tutorId) {
                    if (!empty($tutorId)) {
                        $relacion = isset($tutorRelaciones[$index]) ? $tutorRelaciones[$index] : '';
                        DatabaseConnectors::executeNonQuery('ludoteca', 
                            "INSERT INTO alumno_tutor (alumno_id, tutor_id, relacion) VALUES (?, ?, ?)", 
                            [$alumnoId, $tutorId, $relacion]
                        );
                    }
                }
            }
            
            // Confirmar transacción
            DatabaseConnectors::commitTransaction('ludoteca');
            
            $success_message = $action == 'new' ? "Alumno creado correctamente." : "Alumno actualizado correctamente.";
            
            // Redireccionar para evitar reenvío del formulario
            header("Location: alumnos.php?success=" . urlencode($success_message));
            exit();
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            DatabaseConnectors::rollbackTransaction('ludoteca');
            $error_message = "Error al guardar los datos: " . $e->getMessage();
            error_log($error_message);
        }
    } else {
        $error_message = implode("<br>", $errores);
    }
}

// Procesar eliminación
if ($action == 'delete' && isset($_GET['id'])) {
    try {
        $alumnoId = $_GET['id'];
        
        // Verificar si tiene inscripciones
        $inscripciones = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT COUNT(*) as total FROM inscripciones WHERE alumno_id = ?", 
            [$alumnoId]
        );
        
        if ($inscripciones[0]['total'] > 0) {
            // Tiene inscripciones, solo desactivar
            DatabaseConnectors::executeNonQuery('ludoteca', 
                "UPDATE alumnos SET activo = 0 WHERE id = ?", 
                [$alumnoId]
            );
            $success_message = "Alumno desactivado correctamente. No se puede eliminar porque tiene inscripciones asociadas.";
        } else {
            // No tiene inscripciones, eliminar relaciones y alumno
            DatabaseConnectors::beginTransaction('ludoteca');
            
            // Eliminar relaciones con tutores
            DatabaseConnectors::executeNonQuery('ludoteca', 
                "DELETE FROM alumno_tutor WHERE alumno_id = ?", 
                [$alumnoId]
            );
            
            // Eliminar alumno
            DatabaseConnectors::executeNonQuery('ludoteca', 
                "DELETE FROM alumnos WHERE id = ?", 
                [$alumnoId]
            );
            
            DatabaseConnectors::commitTransaction('ludoteca');
            $success_message = "Alumno eliminado correctamente.";
        }
        
        // Redireccionar
        header("Location: alumnos.php?success=" . urlencode($success_message));
        exit();
    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            DatabaseConnectors::rollbackTransaction('ludoteca');
        }
        $error_message = "Error al eliminar el alumno: " . $e->getMessage();
        error_log($error_message);
    }
}

// Cargar datos para edición
if ($action == 'edit' && isset($_GET['id'])) {
    try {
        // Obtener datos del alumno
        $alumnoId = $_GET['id'];
        $alumnoData = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT * FROM alumnos WHERE id = ?", 
            [$alumnoId]
        );
        
        if (!empty($alumnoData)) {
            $id = $alumnoData[0]['id'];
            $nombre = $alumnoData[0]['nombre'];
            $apellidos = $alumnoData[0]['apellidos'];
            $fecha_nacimiento = $alumnoData[0]['fecha_nacimiento'];
            $centro_id = $alumnoData[0]['centro_id'];
            $curso = $alumnoData[0]['curso'];
            $alergias = $alumnoData[0]['alergias'];
            $medicacion = $alumnoData[0]['medicacion'];
            $observaciones = $alumnoData[0]['observaciones'];
            $activo = $alumnoData[0]['activo'];
            
            // Obtener tutores del alumno
            $tutores = DatabaseConnectors::executeQuery('ludoteca', 
                "SELECT t.*, at.relacion 
                FROM tutores t
                JOIN alumno_tutor at ON t.id = at.tutor_id
                WHERE at.alumno_id = ?",
                [$alumnoId]
            );
        } else {
            $error_message = "No se encontró el alumno solicitado.";
        }
    } catch (Exception $e) {
        $error_message = "Error al cargar los datos del alumno: " . $e->getMessage();
        error_log($error_message);
    }
}

// Consultar lista de alumnos para mostrar
$alumnos = [];
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$soloActivos = isset($_GET['activos']) && $_GET['activos'] == '1';
$centroPredeterminado = isset($_GET['centro_id']) ? $_GET['centro_id'] : '';

if ($dbConnected) {
    try {
        $query = "SELECT a.*, c.nombre as centro_nombre,
                 (SELECT h.hora_fin 
                  FROM inscripciones i 
                  JOIN horarios h ON i.horario_id = h.id 
                  WHERE i.alumno_id = a.id 
                  AND i.estado = 'activa' 
                  LIMIT 1) as hora_salida
                FROM alumnos a
                LEFT JOIN centros c ON a.centro_id = c.id
                WHERE 1=1";
        
        $params = [];
        
        // Filtrar por centro si se seleccionó uno
        if (!empty($centroPredeterminado)) {
            $query .= " AND a.centro_id = ?";
            $params[] = $centroPredeterminado;
        }
        
        // Filtrar por nombre o apellidos
        if (!empty($filtro)) {
            $query .= " AND (a.nombre LIKE ? OR a.apellidos LIKE ?)";
            $params[] = "%$filtro%";
            $params[] = "%$filtro%";
        }
                
        if ($soloActivos) {
            $query .= " AND a.activo = 1";
        }
        
        // Ordenar primero por centro, luego por apellidos y nombre
        $query .= " ORDER BY c.nombre, a.apellidos, a.nombre";
        
        $alumnos = DatabaseConnectors::executeQuery('ludoteca', $query, $params);
    } catch (Exception $e) {
        $error_message = "Error al cargar la lista de alumnos: " . $e->getMessage();
        error_log($error_message);
    }
}

// Cargar lista de centros para el formulario y filtros
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

// Cargar lista de tutores para el formulario
$todosTutores = [];
if ($dbConnected) {
    try {
        $todosTutores = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT id, nombre, dni, telefono FROM tutores ORDER BY nombre"
        );
    } catch (Exception $e) {
        error_log("Error al cargar la lista de tutores: " . $e->getMessage());
    }
}

// Configurar título y migas de pan
$pageTitle = $action == 'new' ? "Nuevo Alumno" : ($action == 'edit' ? "Editar Alumno" : "Alumnos");
$showBreadcrumb = true;
$breadcrumbItems = [
    ['text' => 'Ludoteca', 'url' => 'index.php', 'active' => false],
    ['text' => 'Alumnos', 'url' => 'alumnos.php', 'active' => $action == ''],
    ['text' => $action == 'new' ? 'Nuevo' : ($action == 'edit' ? 'Editar' : ''), 'url' => '', 'active' => true]
];

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
                    <?php if ($action == 'new'): ?>
                        <i class="bi bi-person-plus text-primary"></i> Nuevo Alumno
                    <?php elseif ($action == 'edit'): ?>
                        <i class="bi bi-pencil-square text-primary"></i> Editar Alumno
                    <?php else: ?>
                        <i class="bi bi-people text-primary"></i> Alumnos
                    <?php endif; ?>
                </h1>
                <div>
                    <?php if ($action == 'new' || $action == 'edit'): ?>
                        <a href="alumnos.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al listado
                        </a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Volver al panel
                        </a>
                        <a href="alumnos.php?action=new" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Nuevo Alumno
                        </a>
                    <?php endif; ?>
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

        <?php if ($action == 'new' || $action == 'edit'): ?>
            <!-- Formulario para crear/editar alumno -->
            <div class="card">
                <div class="card-body">
                    <form method="post" action="alumnos.php?action=<?= $action ?><?= $action == 'edit' ? "&id=$id" : "" ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" value="<?= htmlspecialchars($apellidos) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= $fecha_nacimiento ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="centro_id" class="form-label">Centro <span class="text-danger">*</span></label>
                                <select class="form-select" id="centro_id" name="centro_id" required>
                                    <option value="">-- Seleccionar centro --</option>
                                    <?php foreach ($centros as $centro): ?>
                                        <option value="<?= $centro['id'] ?>" <?= $centro_id == $centro['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($centro['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="curso" class="form-label">Curso <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="curso" name="curso" value="<?= htmlspecialchars($curso) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="alergias" class="form-label">Alergias</label>
                                <textarea class="form-control" id="alergias" name="alergias" rows="2"><?= htmlspecialchars($alergias) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="medicacion" class="form-label">Medicación</label>
                                <textarea class="form-control" id="medicacion" name="medicacion" rows="2"><?= htmlspecialchars($medicacion) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($observaciones) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="activo">
                                        Alumno activo
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <h4 class="mt-4 mb-3">Tutores</h4>
                        <div id="tutores-container">
                            <?php if (!empty($tutores)): ?>
                                <?php foreach ($tutores as $index => $tutor): ?>
                                    <div class="row mb-3 tutor-row">
                                        <div class="col-md-7">
                                            <label class="form-label">Tutor</label>
                                            <select class="form-select" name="tutores[]">
                                                <option value="">-- Seleccionar tutor --</option>
                                                <?php foreach ($todosTutores as $t): ?>
                                                    <option value="<?= $t['id'] ?>" <?= $t['id'] == $tutor['id'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($t['nombre']) ?> (<?= htmlspecialchars($t['dni']) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Relación</label>
                                            <input type="text" class="form-control" name="relaciones[]" placeholder="Ej: Padre, Madre, Tutor legal" value="<?= htmlspecialchars($tutor['relacion']) ?>">
                                        </div>
                                        <div class="col-md-1 d-flex align-items-end">
                                            <button type="button" class="btn btn-outline-danger mb-0 remove-tutor"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="row mb-3 tutor-row">
                                    <div class="col-md-7">
                                        <label class="form-label">Tutor</label>
                                        <select class="form-select" name="tutores[]">
                                            <option value="">-- Seleccionar tutor --</option>
                                            <?php foreach ($todosTutores as $t): ?>
                                                <option value="<?= $t['id'] ?>">
                                                    <?= htmlspecialchars($t['nombre']) ?> (<?= htmlspecialchars($t['dni']) ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Relación</label>
                                        <input type="text" class="form-control" name="relaciones[]" placeholder="Ej: Padre, Madre, Tutor legal">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger mb-0 remove-tutor"><i class="bi bi-trash"></i></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-4">
                            <button type="button" id="add-tutor" class="btn btn-outline-secondary">
                                <i class="bi bi-plus-circle"></i> Añadir otro tutor
                            </button>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="alumnos.php" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $action == 'new' ? 'Crear Alumno' : 'Guardar Cambios' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Listado de alumnos -->
            <div class="card">
                <div class="card-body">
                    <form method="get" action="alumnos.php" class="row mb-3">
                        <div class="col-md-3">
                            <label for="centro_id" class="form-label">Filtrar por Centro:</label>
                            <select class="form-select" id="centro_id" name="centro_id" onchange="this.form.submit()">
                                <option value="">Todos los centros</option>
                                <?php foreach ($centros as $centro): ?>
                                    <option value="<?= $centro['id'] ?>" <?= $centroPredeterminado == $centro['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($centro['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="filtro" class="form-label">Buscar:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Buscar por nombre o apellidos..." id="filtro" name="filtro" value="<?= htmlspecialchars($filtro) ?>">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" id="activos" name="activos" value="1" <?= $soloActivos ? 'checked' : '' ?> onchange="this.form.submit()">
                                <label class="form-check-label" for="activos">Mostrar solo alumnos activos</label>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Botones de acción rápida -->
                    <div class="d-flex justify-content-end mb-3">
                        <a href="alumnos.php?action=new" class="btn btn-success">
                            <i class="bi bi-person-plus"></i> Nuevo Alumno
                        </a>
                    </div>
                    
                    <?php if (empty($alumnos)): ?>
                        <div class="alert alert-info">
                            No se encontraron alumnos<?= $filtro ? " con el filtro '$filtro'" : "" ?><?= $centroPredeterminado ? " en el centro seleccionado" : "" ?>.
                        </div>
                    <?php else: ?>
                        <!-- Agrupación por centro -->
                        <?php
                        $alumnosPorCentro = [];
                        foreach ($alumnos as $alumno) {
                            $centroId = $alumno['centro_id'] ?: '0';
                            $centroNombre = $alumno['centro_nombre'] ?: 'Sin centro asignado';
                            
                            if (!isset($alumnosPorCentro[$centroId])) {
                                $alumnosPorCentro[$centroId] = [
                                    'nombre' => $centroNombre,
                                    'alumnos' => []
                                ];
                            }
                            
                            $alumnosPorCentro[$centroId]['alumnos'][] = $alumno;
                        }
                        ?>
                        
                        <?php foreach ($alumnosPorCentro as $centroId => $datosCentro): ?>
                            <div class="mb-4">
                                <h5 class="border-bottom pb-2">
                                    <i class="bi bi-building"></i> <?= htmlspecialchars($datosCentro['nombre']) ?>
                                    <span class="badge bg-primary rounded-pill ms-2"><?= count($datosCentro['alumnos']) ?></span>
                                </h5>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Apellidos</th>
                                                <th>Nombre</th>
                                                <th>F. Nacimiento</th>
                                                <th>Edad</th>
                                                <th>Curso</th>
                                                <th>Hora Salida</th>
                                                <th>Estado</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($datosCentro['alumnos'] as $alumno): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($alumno['apellidos']) ?></td>
                                                    <td><?= htmlspecialchars($alumno['nombre']) ?></td>
                                                    <td><?= date('d/m/Y', strtotime($alumno['fecha_nacimiento'])) ?></td>
                                                    <td>
                                                        <?php
                                                            $nacimiento = new DateTime($alumno['fecha_nacimiento']);
                                                            $hoy = new DateTime();
                                                            $edad = $hoy->diff($nacimiento);
                                                            echo $edad->y . " años";
                                                        ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($alumno['curso']) ?></td>
                                                    <td>
                                                        <?php if ($alumno['hora_salida']): ?>
                                                            <span class="badge bg-info text-dark">
                                                                <i class="bi bi-clock"></i> 
                                                                <?= date('H:i', strtotime($alumno['hora_salida'])) ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">No asignada</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($alumno['activo']): ?>
                                                            <span class="badge bg-success">Activo</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Inactivo</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="btn-group" role="group">
                                                            <a href="alumnos.php?action=edit&id=<?= $alumno['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="confirmDelete(<?= $alumno['id'] ?>, '<?= htmlspecialchars(addslashes($alumno['nombre'] . ' ' . $alumno['apellidos'])) ?>')" 
                                                                    title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="mt-3">
                            <p>Total: <?= count($alumnos) ?> alumno(s)</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar al alumno <span id="deleteStudentName"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Script para manejar los tutores en el formulario
    document.addEventListener('DOMContentLoaded', function() {
        // Añadir tutor
        document.getElementById('add-tutor').addEventListener('click', function() {
            const container = document.getElementById('tutores-container');
            const tutorRows = container.querySelectorAll('.tutor-row');
            const lastRow = tutorRows[tutorRows.length - 1];
            const newRow = lastRow.cloneNode(true);
            
            // Limpiar los valores
            const selectElement = newRow.querySelector('select');
            selectElement.value = '';
            
            const inputElement = newRow.querySelector('input[type="text"]');
            inputElement.value = '';
            
            // Añadir evento para eliminar
            newRow.querySelector('.remove-tutor').addEventListener('click', function() {
                container.removeChild(newRow);
            });
            
            container.appendChild(newRow);
        });
        
        //