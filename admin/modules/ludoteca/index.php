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
    
    // Obtener conexión para la base de datos 'ludoteca'
    $conn = DatabaseConnectors::getConnection('ludoteca');
    $dbConnected = true;
    
    // Verificar si la base de datos existe ejecutando una consulta simple
    try {
        DatabaseConnectors::executeQuery('ludoteca', 'SELECT 1');
    } catch (Exception $innerEx) {
        throw new Exception("La base de datos 'ludoteca' parece no existir o no contiene tablas necesarias: " . $innerEx->getMessage());
    }
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
    error_log('Error de conexión a BD: ' . $dbError);
}

// Obtener información del estado del sistema
$systemStatus = [
    'db_status' => $dbConnected,
    'server_time' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB', // En MB
    'session_active' => isset($_SESSION['user_authenticated']) && $_SESSION['user_authenticated'] === true
];

// Título de la página
$pageTitle = "Gestión de Ludoteca";

// Configurar migas de pan
$showBreadcrumb = true;
$breadcrumbItems = [
    ['text' => 'Ludoteca', 'url' => '', 'active' => true]
];

// Incluir el encabezado con ajuste de ruta
$basePath = '../../';
try {
    include_once __DIR__ . '/../../templates/module_header.php';
} catch (Exception $e) {
    error_log('Error al incluir header: ' . $e->getMessage());
    echo '<div class="alert alert-danger">Error al cargar la plantilla del encabezado: ' . $e->getMessage() . '</div>';
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="bi bi-controller text-primary"></i> Ludoteca</h1>
                <div>
                    <a href="../../" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Volver al panel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$dbConnected): ?>
    <div class="alert alert-danger">
        <h4 class="alert-heading">Error de conexión</h4>
        <p>No se pudo conectar a la base de datos de Ludoteca.</p>
        <hr>
        <p class="mb-0">Error: <?= $dbError ?></p>
        <p class="mt-3">
            <strong>Verificaciones sugeridas:</strong>
            <ul>
                <li>Asegurar que la base de datos 'ludoteca_db' existe en el servidor</li>
                <li>Verificar las credenciales de acceso en el archivo DatabaseConnectors.php</li>
                <li>Confirmar que el usuario tiene permisos suficientes</li>
            </ul>
        </p>
    </div>
    <?php else: ?>

    <!-- Tarjetas de acceso rápido - Sólo los módulos solicitados -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-people display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Alumnos</h5>
                    <p class="card-text">Gestión de alumnos inscritos en la ludoteca.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="alumnos.php" class="btn btn-primary stretched-link">Acceder</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-buildings display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Centros</h5>
                    <p class="card-text">Gestión de centros educativos.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="centros.php" class="btn btn-primary stretched-link">Acceder</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-person-badge display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Tutores</h5>
                    <p class="card-text">Gestión de tutores y contactos.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="tutores.php" class="btn btn-primary stretched-link">Acceder</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-calendar-check display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Asistencia</h5>
                    <p class="card-text">Control de asistencia diaria de los alumnos.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="asistencia.php" class="btn btn-primary stretched-link">Acceder</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="bi bi-file-earmark-bar-graph display-4 text-primary"></i>
                    <h5 class="card-title mt-3">Informes</h5>
                    <p class="card-text">Generación de informes y estadísticas.</p>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="informes.php" class="btn btn-primary stretched-link">Acceder</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de resumen -->
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Resumen de Ludoteca</h5>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        // Datos según la estructura de ludoteca_db.sql
                        $alumnos = 0;
                        $alumnosActivos = 0;
                        $tutores = 0;
                        $centros = 0;
                        
                        try {
                            $alumnosResult = DatabaseConnectors::executeQuery('ludoteca', "SELECT COUNT(*) as total FROM alumnos");
                            if (is_array($alumnosResult) && isset($alumnosResult[0]['total'])) {
                                $alumnos = $alumnosResult[0]['total'];
                            }
                        } catch (Exception $ex) {
                            error_log("Error consultando alumnos: " . $ex->getMessage());
                        }
                        
                        try {
                            $alumnosActivosResult = DatabaseConnectors::executeQuery('ludoteca', "SELECT COUNT(*) as total FROM alumnos WHERE activo = 1");
                            if (is_array($alumnosActivosResult) && isset($alumnosActivosResult[0]['total'])) {
                                $alumnosActivos = $alumnosActivosResult[0]['total'];
                            }
                        } catch (Exception $ex) {
                            error_log("Error consultando alumnos activos: " . $ex->getMessage());
                        }
                        
                        try {
                            $tutoresResult = DatabaseConnectors::executeQuery('ludoteca', "SELECT COUNT(*) as total FROM tutores");
                            if (is_array($tutoresResult) && isset($tutoresResult[0]['total'])) {
                                $tutores = $tutoresResult[0]['total'];
                            }
                        } catch (Exception $ex) {
                            error_log("Error consultando tutores: " . $ex->getMessage());
                        }
                        
                        try {
                            $centrosResult = DatabaseConnectors::executeQuery('ludoteca', "SELECT COUNT(*) as total FROM centros WHERE activo = 1");
                            if (is_array($centrosResult) && isset($centrosResult[0]['total'])) {
                                $centros = $centrosResult[0]['total'];
                            }
                        } catch (Exception $ex) {
                            error_log("Error consultando centros: " . $ex->getMessage());
                        }
                    } catch (Exception $e) {
                        error_log('Error en consultas de resumen: ' . $e->getMessage());
                        echo '<div class="alert alert-warning">Error al cargar estadísticas: ' . $e->getMessage() . '</div>';
                    }
                    ?>
                    
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="h2 text-primary"><?= $alumnos ?></div>
                            <div class="text-muted">Alumnos</div>
                        </div>
                        <div class="col-md-3">
                            <div class="h2 text-primary"><?= $alumnosActivos ?></div>
                            <div class="text-muted">Alumnos activos</div>
                        </div>
                        <div class="col-md-3">
                            <div class="h2 text-primary"><?= $tutores ?></div>
                            <div class="text-muted">Tutores</div>
                        </div>
                        <div class="col-md-3">
                            <div class="h2 text-primary"><?= $centros ?></div>
                            <div class="text-muted">Centros activos</div>
                        </div>
                    </div>

                    <!-- Resumen de asistencia reciente -->
                    <div class="mt-4">
                        <h6 class="border-bottom pb-2">Asistencia Reciente</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Alumno</th>
                                        <th>Hora Entrada</th>
                                        <th>Hora Salida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    try {
                                        $asistenciaReciente = DatabaseConnectors::executeQuery('ludoteca', 
                                            "SELECT a.fecha, a.hora_entrada, a.hora_salida, al.nombre, al.apellidos 
                                            FROM asistencia a 
                                            JOIN inscripciones i ON a.inscripcion_id = i.id 
                                            JOIN alumnos al ON i.alumno_id = al.id 
                                            ORDER BY a.fecha DESC, a.hora_entrada DESC 
                                            LIMIT 5");

                                        if (count($asistenciaReciente) > 0):
                                            foreach ($asistenciaReciente as $asistencia):
                                    ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($asistencia['fecha'])) ?></td>
                                                <td><?= htmlspecialchars($asistencia['nombre'] . ' ' . $asistencia['apellidos']) ?></td>
                                                <td><?= $asistencia['hora_entrada'] ? date('H:i', strtotime($asistencia['hora_entrada'])) : '-' ?></td>
                                                <td><?= $asistencia['hora_salida'] ? date('H:i', strtotime($asistencia['hora_salida'])) : '-' ?></td>
                                            </tr>
                                    <?php
                                            endforeach;
                                        else:
                                    ?>
                                            <tr>
                                                <td colspan="4" class="text-center">No hay registros de asistencia recientes</td>
                                            </tr>
                                    <?php
                                        endif;
                                    } catch (Exception $ex) {
                                        echo '<tr><td colspan="4" class="text-center text-danger">Error al cargar la asistencia: ' . $ex->getMessage() . '</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Acciones rápidas</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="alumnos.php?action=new" class="btn btn-outline-primary mb-2">
                            <i class="bi bi-person-plus"></i> Nuevo alumno
                        </a>
                        <a href="tutores.php?action=new" class="btn btn-outline-primary mb-2">
                            <i class="bi bi-person-add"></i> Nuevo tutor
                        </a>
                        <a href="asistencia.php?date=<?= date('Y-m-d') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-calendar-check"></i> Asistencia de hoy
                        </a>
                    </div>
                </div>
            </div>

            <!-- Estado del Sistema -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-gear"></i> Estado del Sistema</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Base de Datos
                            <?php if ($systemStatus['db_status']): ?>
                                <span class="badge bg-success rounded-pill">Conectada</span>
                            <?php else: ?>
                                <span class="badge bg-danger rounded-pill">Error</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tablas Requeridas
                            <?php 
                            $tablesOk = true;
                            try {
                                $requiredTables = ['alumnos', 'tutores', 'asistencia', 'centros'];
                                foreach ($requiredTables as $table) {
                                    // Intentamos realizar una consulta simple a cada tabla requerida
                                    DatabaseConnectors::executeQuery('ludoteca', "SELECT 1 FROM $table LIMIT 1");
                                }
                            } catch (Exception $e) {
                                $tablesOk = false;
                            }
                            ?>
                            <?php if ($tablesOk): ?>
                                <span class="badge bg-success rounded-pill">OK</span>
                            <?php else: ?>
                                <span class="badge bg-warning rounded-pill">Incompletas</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Hora del Servidor
                            <span class="text-muted"><?= $systemStatus['server_time'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Sesión
                            <?php if ($systemStatus['session_active']): ?>
                                <span class="badge bg-success rounded-pill">Activa</span>
                            <?php else: ?>
                                <span class="badge bg-warning rounded-pill">Inactiva</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Versión PHP
                            <span class="text-muted"><?= $systemStatus['php_version'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Memoria Utilizada
                            <span class="text-muted"><?= $systemStatus['memory_usage'] ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Incluir el pie de página
try {
    include_once __DIR__ . '/../../templates/footer.php';
} catch (Exception $e) {
    error_log('Error al incluir footer: ' . $e->getMessage());
    echo '<div class="alert alert-danger">Error al cargar la plantilla del pie de página: ' . $e->getMessage() . '</div>';
}
?>
