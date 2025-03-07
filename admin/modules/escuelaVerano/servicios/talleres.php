<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Asistencia a Talleres - Escuela de Verano";
$currentSection = "talleres";
$isServicePage = true;
$baseUrl = "../";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$tallerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verificamos si existe la tabla de talleres y la columna asistio
$talleresExisten = false;
$columnaAsistioExiste = false;
try {
    // Verificar si existe la tabla talleres
    $checkTableQuery = "SHOW TABLES LIKE 'talleres'";
    $tableExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkTableQuery);
    
    if (!empty($tableExists)) {
        $talleresExisten = true;
        
        // Verificar si existe la tabla taller_participantes
        $checkParticipantesTableQuery = "SHOW TABLES LIKE 'taller_participantes'";
        $participantesTableExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkParticipantesTableQuery);
        
        if (empty($participantesTableExists)) {
            // Crear tabla taller_participantes si no existe
            $createParticipantesTableQuery = "
                CREATE TABLE taller_participantes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    taller_id INT NOT NULL,
                    participante_id INT NOT NULL,
                    asistio BOOLEAN DEFAULT FALSE,
                    comentario TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (taller_id) REFERENCES talleres(id) ON DELETE CASCADE,
                    FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE,
                    UNIQUE KEY `idx_taller_participante` (`taller_id`, `participante_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
            ";
            DatabaseConnectors::executeNonQuery('escuelaVerano', $createParticipantesTableQuery);
            $mensaje = "Se ha creado la tabla de participantes en talleres. Ahora puede gestionar las asistencias.";
            $tipoMensaje = "info";
        } else {
            // Verificar si la columna asistio existe
            $checkColumnQuery = "SHOW COLUMNS FROM taller_participantes LIKE 'asistio'";
            $columnExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkColumnQuery);
            
            if (empty($columnExists)) {
                // Agregar la columna asistio si no existe
                $addColumnQuery = "ALTER TABLE taller_participantes ADD asistio BOOLEAN DEFAULT FALSE";
                DatabaseConnectors::executeNonQuery('escuelaVerano', $addColumnQuery);
                
                // Agregar la columna comentario si no existe
                $checkCommentColumnQuery = "SHOW COLUMNS FROM taller_participantes LIKE 'comentario'";
                $commentColumnExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkCommentColumnQuery);
                
                if (empty($commentColumnExists)) {
                    $addCommentColumnQuery = "ALTER TABLE taller_participantes ADD comentario TEXT";
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $addCommentColumnQuery);
                }
                
                $mensaje = "Se han actualizado las tablas para gestionar asistencias.";
                $tipoMensaje = "info";
            }
            
            $columnaAsistioExiste = true;
        }
    }
} catch (Exception $e) {
    $mensaje = "Error al verificar tablas: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Marcar asistencia
        if (isset($_POST['marcar_asistencia'])) {
            $taller_id = $_POST['taller_id'];
            $asistencias = isset($_POST['asistencia']) ? $_POST['asistencia'] : [];
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : [];
            
            // Primero reseteamos todas las asistencias para este taller
            $resetQuery = "UPDATE taller_participantes SET asistio = 0 WHERE taller_id = :taller_id";
            DatabaseConnectors::executeNonQuery('escuelaVerano', $resetQuery, [':taller_id' => $taller_id]);
            
            // Marcamos las asistencias seleccionadas
            if (!empty($asistencias)) {
                foreach ($asistencias as $participante_id) {
                    $observacion = isset($observaciones[$participante_id]) ? $observaciones[$participante_id] : '';
                    
                    $updateQuery = "UPDATE taller_participantes 
                                   SET asistio = 1, 
                                       comentario = :comentario 
                                   WHERE taller_id = :taller_id 
                                   AND participante_id = :participante_id";
                                   
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $updateQuery, [
                        ':taller_id' => $taller_id,
                        ':participante_id' => $participante_id,
                        ':comentario' => $observacion
                    ]);
                }
            }
            
            $mensaje = "Asistencia registrada correctamente.";
            $tipoMensaje = "success";
        }
        
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Si hay mensaje en la URL, lo recogemos
if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

// Obtenemos la lista de talleres por fecha
$talleres = [];
if ($talleresExisten) {
    try {
        // Consulta modificada para evitar el error de la columna 'asistio'
        if ($columnaAsistioExiste) {
            $query = "SELECT t.*, 
                     (SELECT COUNT(*) FROM taller_participantes WHERE taller_id = t.id) as total_participantes,
                     (SELECT COUNT(*) FROM taller_participantes WHERE taller_id = t.id AND asistio = 1) as asistentes
                     FROM talleres t 
                     WHERE t.fecha = :fecha 
                     ORDER BY t.hora_inicio";
        } else {
            // Consulta alternativa si no existe la columna asistio
            $query = "SELECT t.*, 
                     (SELECT COUNT(*) FROM taller_participantes WHERE taller_id = t.id) as total_participantes,
                     0 as asistentes
                     FROM talleres t 
                     WHERE t.fecha = :fecha 
                     ORDER BY t.hora_inicio";
        }
        
        $talleres = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':fecha' => $fechaSeleccionada]);
    } catch (Exception $e) {
        $mensaje = "Error al cargar talleres: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Si se seleccionó un taller específico, cargamos sus datos y participantes
$taller = null;
$participantesTaller = [];
if ($tallerId > 0 && $talleresExisten) {
    try {
        // Información del taller
        $query = "SELECT * FROM talleres WHERE id = :id";
        $result = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $tallerId]);
        if (!empty($result)) {
            $taller = $result[0];
            
            // Lista de participantes inscritos con su estado de asistencia
            if ($columnaAsistioExiste) {
                $queryParticipantes = "SELECT tp.id as inscripcion_id, tp.participante_id, tp.asistio, tp.comentario, 
                                     p.nombre, p.fecha_nacimiento, r.nombre as responsable_nombre
                                     FROM taller_participantes tp
                                     JOIN participantes p ON tp.participante_id = p.id
                                     JOIN responsables r ON p.responsable_id = r.id
                                     WHERE tp.taller_id = :taller_id
                                     ORDER BY p.nombre";
            } else {
                // Consulta alternativa si no existe la columna asistio
                $queryParticipantes = "SELECT tp.id as inscripcion_id, tp.participante_id, 0 as asistio, '' as comentario, 
                                     p.nombre, p.fecha_nacimiento, r.nombre as responsable_nombre
                                     FROM taller_participantes tp
                                     JOIN participantes p ON tp.participante_id = p.id
                                     JOIN responsables r ON p.responsable_id = r.id
                                     WHERE tp.taller_id = :taller_id
                                     ORDER BY p.nombre";
            }
            
            $participantesTaller = DatabaseConnectors::executeQuery('escuelaVerano', $queryParticipantes, [':taller_id' => $tallerId]);
        } else {
            $mensaje = "Taller no encontrado.";
            $tipoMensaje = "warning";
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar datos del taller: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Preparamos un array con las próximas fechas
$proximosDias = [];
$fechaActual = new DateTime();
$fechaInicio = clone $fechaActual;
$fechaInicio->modify('-7 days');

for ($i = 0; $i < 15; $i++) {
    $fecha = clone $fechaInicio;
    $fecha->modify("+$i days");
    $fechaStr = $fecha->format('Y-m-d');
    $proximosDias[$fechaStr] = $fecha->format('d/m/Y (D)');
}

// Incluimos el header
include('../includes/header.php');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1>
        <i class="fas fa-paint-brush mr-2"></i> Asistencia a Talleres
        <?php if ($tallerId > 0 && $taller): ?>
            <small class="text-muted"> - <?php echo $taller['nombre']; ?></small>
        <?php endif; ?>
    </h1>
    
    <div class="btn-toolbar mb-2 mb-md-0">
        <form class="form-inline" action="" method="get">
            <label class="mr-2">Fecha:</label>
            <select name="fecha" class="form-control mr-2" onchange="this.form.submit()">
                <?php foreach($proximosDias as $fecha => $label): ?>
                    <option value="<?php echo $fecha; ?>" <?php echo $fecha === $fechaSeleccionada ? 'selected' : ''; ?>>
                        <?php echo $label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (!$talleresExisten): ?>
    <div class="alert alert-info">
        <h4><i class="fas fa-info-circle mr-2"></i> No hay talleres registrados</h4>
        <p>No se ha encontrado la tabla de talleres en la base de datos. Para gestionar talleres, primero deben ser creados por un administrador.</p>
    </div>
<?php elseif ($tallerId > 0 && $taller): ?>
    <!-- Vista de asistencia para un taller específico -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Control de Asistencia</h5>
            <a href="?fecha=<?php echo $fechaSeleccionada; ?>" class="btn btn-light btn-sm">
                <i class="fas fa-arrow-left"></i> Volver a la lista de talleres
            </a>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5><?php echo $taller['nombre']; ?></h5>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($taller['fecha'])); ?></p>
                    <p><strong>Horario:</strong> <?php echo date('H:i', strtotime($taller['hora_inicio'])) . ' - ' . date('H:i', strtotime($taller['hora_fin'])); ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Lugar:</strong> <?php echo $taller['lugar'] ?? 'No especificado'; ?></p>
                    <p><strong>Responsable:</strong> <?php echo $taller['responsable'] ?? 'No especificado'; ?></p>
                    <p><strong>Participantes inscritos:</strong> <?php echo count($participantesTaller); ?> / <?php echo $taller['capacidad']; ?></p>
                </div>
            </div>
            
            <?php if (empty($participantesTaller)): ?>
                <div class="alert alert-warning">No hay participantes inscritos en este taller.</div>
            <?php else: ?>
                <form action="" method="post">
                    <input type="hidden" name="taller_id" value="<?php echo $tallerId; ?>">
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 60px">Asistió</th>
                                    <th>Nombre</th>
                                    <th>Responsable</th>
                                    <th>Edad</th>
                                    <th>Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($participantesTaller as $p): 
                                    // Calculamos la edad
                                    $fechaNac = new DateTime($p['fecha_nacimiento']);
                                    $hoy = new DateTime();
                                    $edad = $fechaNac->diff($hoy)->y;
                                ?>
                                    <tr class="<?php echo $p['asistio'] ? 'table-success' : ''; ?>">
                                        <td class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" 
                                                    id="asistencia-<?php echo $p['participante_id']; ?>" 
                                                    name="asistencia[]" 
                                                    value="<?php echo $p['participante_id']; ?>" 
                                                    <?php echo $p['asistio'] ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="asistencia-<?php echo $p['participante_id']; ?>"></label>
                                            </div>
                                        </td>
                                        <td><?php echo $p['nombre']; ?></td>
                                        <td><?php echo $p['responsable_nombre']; ?></td>
                                        <td><?php echo $edad; ?> años</td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm" 
                                                name="observaciones[<?php echo $p['participante_id']; ?>]" 
                                                value="<?php echo htmlspecialchars($p['comentario'] ?? ''); ?>" 
                                                placeholder="Observaciones">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="form-group mt-4">
                        <button type="submit" name="marcar_asistencia" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Asistencia
                        </button>
                        <a href="?fecha=<?php echo $fechaSeleccionada; ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Lista de talleres del día seleccionado -->
    <?php if (empty($talleres)): ?>
        <div class="alert alert-info">
            <h4><i class="fas fa-info-circle mr-2"></i> No hay talleres programados</h4>
            <p>No hay talleres programados para la fecha seleccionada: <?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($talleres as $t): 
                // Calcular porcentaje de asistencia
                $porcentajeAsistencia = ($t['total_participantes'] > 0) ? round(($t['asistentes'] / $t['total_participantes']) * 100) : 0;
                
                // Determinar color según el tipo de taller
                $tiposTaller = [
                    'arte' => 'primary', 'deporte' => 'success', 'musica' => 'info',
                    'teatro' => 'warning', 'ciencia' => 'danger'
                ];
                $cardColor = isset($t['tipo']) && isset($tiposTaller[$t['tipo']]) ? $tiposTaller[$t['tipo']] : 'primary';
            ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-<?php echo $cardColor; ?> text-white">
                            <h5 class="card-title mb-0"><?php echo $t['nombre']; ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <p class="mb-1"><i class="far fa-clock mr-2"></i> <strong>Horario:</strong> 
                                    <?php echo date('H:i', strtotime($t['hora_inicio'])); ?> - 
                                    <?php echo date('H:i', strtotime($t['hora_fin'])); ?>
                                </p>
                                <p class="mb-1"><i class="fas fa-map-marker-alt mr-2"></i> <strong>Lugar:</strong> 
                                    <?php echo $t['lugar'] ?? 'No especificado'; ?>
                                </p>
                                <p class="mb-1"><i class="fas fa-user mr-2"></i> <strong>Responsable:</strong> 
                                    <?php echo $t['responsable'] ?? 'No especificado'; ?>
                                </p>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong>Asistencia:</strong>
                                </div>
                                <div>
                                    <span class="badge badge-<?php echo $porcentajeAsistencia > 0 ? 'success' : 'secondary'; ?>">
                                        <?php echo $t['asistentes']; ?>/<?php echo $t['total_participantes']; ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: <?php echo $porcentajeAsistencia; ?>%;" 
                                     aria-valuenow="<?php echo $porcentajeAsistencia; ?>" 
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="?id=<?php echo $t['id']; ?>&fecha=<?php echo $fechaSeleccionada; ?>" class="btn btn-primary">
                                <i class="fas fa-clipboard-check"></i> Controlar Asistencia
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php
// Incluimos el footer
include('../includes/footer.php');
?>

