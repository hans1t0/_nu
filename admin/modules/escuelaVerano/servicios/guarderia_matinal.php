<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Servicio de Guardería Matinal - Escuela de Verano";
$currentSection = "guarderia";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';

// Fechas para filtrar
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Procesamos las acciones
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$participanteId = isset($_GET['participante_id']) ? (int)$_GET['participante_id'] : 0;

// Si se está marcando la asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['marcar_asistencia'])) {
            // Obtener los datos del formulario
            $fecha = $_POST['fecha'];
            $asistencias = isset($_POST['asistencia']) ? $_POST['asistencia'] : [];
            $hora_entrada = isset($_POST['hora_entrada']) ? $_POST['hora_entrada'] : [];
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : [];
            
            // Primero eliminamos las asistencias existentes para esa fecha para evitar duplicados
            $deleteQuery = "DELETE FROM guarderia_asistencia WHERE fecha = :fecha";
            DatabaseConnectors::executeNonQuery('escuelaVerano', $deleteQuery, [':fecha' => $fecha]);
            
            // Insertamos las nuevas asistencias
            if (!empty($asistencias)) {
                foreach ($asistencias as $participanteId) {
                    $hora = isset($hora_entrada[$participanteId]) ? $hora_entrada[$participanteId] : '';
                    $obs = isset($observaciones[$participanteId]) ? $observaciones[$participanteId] : '';
                    
                    $insertQuery = "INSERT INTO guarderia_asistencia (participante_id, fecha, asistio, hora_entrada, observaciones) 
                                    VALUES (:participante_id, :fecha, 1, :hora_entrada, :observaciones)";
                                    
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $insertQuery, [
                        ':participante_id' => $participanteId,
                        ':fecha' => $fecha,
                        ':hora_entrada' => $hora,
                        ':observaciones' => $obs
                    ]);
                }
            }
            
            $mensaje = "Se ha registrado la asistencia a guardería matinal para el día $fecha";
            $tipoMensaje = "success";
        }
        // Otros posibles formularios se procesarían aquí
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

// Verificamos si existe la tabla de asistencia, si no, la creamos
try {
    $checkTableQuery = "SHOW TABLES LIKE 'guarderia_asistencia'";
    $tableExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkTableQuery);
    
    if (empty($tableExists)) {
        $createTableQuery = "
            CREATE TABLE guarderia_asistencia (
                id INT AUTO_INCREMENT PRIMARY KEY,
                participante_id INT NOT NULL,
                fecha DATE NOT NULL,
                asistio TINYINT(1) DEFAULT 0,
                hora_entrada TIME,
                observaciones TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_participante_fecha` (`participante_id`, `fecha`),
                FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        DatabaseConnectors::executeNonQuery('escuelaVerano', $createTableQuery);
    }
} catch (Exception $e) {
    $mensaje = "Error al verificar/crear tablas: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Obtenemos la lista de participantes con servicio de guardería matinal
$participantesGuarderia = [];
try {
    $query = "SELECT p.id, p.nombre, p.fecha_nacimiento, p.alergias, r.nombre as responsable_nombre, s.guarderia_matinal
              FROM participantes p 
              JOIN servicios_contratados s ON p.id = s.participante_id 
              JOIN responsables r ON p.responsable_id = r.id 
              WHERE s.guarderia_matinal IS NOT NULL
              ORDER BY p.nombre";
    $participantesGuarderia = DatabaseConnectors::executeQuery('escuelaVerano', $query);
} catch (Exception $e) {
    $mensaje = "Error al cargar participantes con guardería: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Obtenemos las asistencias registradas para la fecha seleccionada
$asistencias = [];
try {
    $query = "SELECT participante_id, hora_entrada, observaciones FROM guarderia_asistencia WHERE fecha = :fecha AND asistio = 1";
    $result = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':fecha' => $fechaSeleccionada]);
    
    // Convertimos el resultado a un array asociativo para facilitar la comprobación
    foreach ($result as $row) {
        $asistencias[$row['participante_id']] = [
            'hora_entrada' => $row['hora_entrada'],
            'observaciones' => $row['observaciones']
        ];
    }
} catch (Exception $e) {
    $mensaje .= " Error al cargar asistencias: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Calculamos estadísticas
$estadisticas = [
    'total_guarderia' => count($participantesGuarderia),
    'asistentes_hoy' => count($asistencias),
    'horarios' => [
        '7:30' => 0,
        '8:00' => 0,
        '8:30' => 0
    ]
];

// Contamos cuántos tienen contratado cada horario
foreach ($participantesGuarderia as $p) {
    if (isset($estadisticas['horarios'][$p['guarderia_matinal']])) {
        $estadisticas['horarios'][$p['guarderia_matinal']]++;
    }
}

// Preparamos el array con los próximos 14 días para el selector de fechas
$proximosDias = [];
for ($i = -7; $i <= 7; $i++) {
    $fecha = date('Y-m-d', strtotime("$i days"));
    $proximosDias[$fecha] = date('d/m/Y (l)', strtotime($fecha));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <style>
        .nav-item.active {
            background-color: rgba(0,0,0,0.1);
        }
        .alergia-badge {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table-fixed {
            table-layout: fixed;
        }
        .horario-730 {
            background-color: rgba(255, 193, 7, 0.2);
        }
        .horario-800 {
            background-color: rgba(23, 162, 184, 0.2);
        }
        .horario-830 {
            background-color: rgba(40, 167, 69, 0.2);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Menú lateral -->
            <nav class="col-md-2 d-none d-md-block bg-light sidebar py-5">
                <div class="sidebar-sticky">
                    <h5 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Escuela de Verano</span>
                    </h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="../index.php">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../responsables.php">
                                <i class="fas fa-users"></i> Responsables
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../participantes.php">
                                <i class="fas fa-child"></i> Participantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../periodos.php">
                                <i class="fas fa-calendar-alt"></i> Periodos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../servicios.php">
                                <i class="fas fa-concierge-bell"></i> Servicios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../reportes.php">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                    </ul>
                    
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Servicios específicos</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="comedor.php">
                                <i class="fas fa-utensils"></i> Comedor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="guarderia_matinal.php">
                                <i class="fas fa-sun"></i> Guardería Matinal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="talleres.php">
                                <i class="fas fa-paint-brush"></i> Talleres
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-5">
                        <a href="../servicios.php" class="btn btn-primary btn-sm btn-block mb-2">
                            <i class="fas fa-arrow-left"></i> Volver a Servicios
                        </a>
                        <a href="../../../index.php" class="btn btn-secondary btn-sm btn-block">
                            <i class="fas fa-home"></i> Panel principal
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main role="main" class="col-md-10 ml-sm-auto px-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1><i class="fas fa-sun mr-2"></i> Servicio de Guardería Matinal</h1>
                    
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <form class="form-inline" action="" method="get">
                            <label class="mr-2">Fecha:</label>
                            <select name="fecha" class="form-control mr-2" onchange="this.form.submit()">
                                <?php foreach($proximosDias as $fecha => $fechaTexto): ?>
                                    <option value="<?php echo $fecha; ?>" <?php echo $fecha === $fechaSeleccionada ? 'selected' : ''; ?>>
                                        <?php echo $fechaTexto; ?>
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
                
                <!-- Tarjetas de resumen -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Guardería</h6>
                                        <h2><?php echo $estadisticas['total_guarderia']; ?></h2>
                                    </div>
                                    <i class="fas fa-sun fa-3x"></i>
                                </div>
                                <small>Participantes con servicio de guardería contratado</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Asistentes Hoy</h6>
                                        <h2><?php echo $estadisticas['asistentes_hoy']; ?></h2>
                                    </div>
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                                <small>Participantes que han asistido a guardería hoy</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card bg-warning text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">7:30</h6>
                                        <h2><?php echo $estadisticas['horarios']['7:30']; ?></h2>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <small>Horario con desayuno</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">8:00</h6>
                                        <h2><?php echo $estadisticas['horarios']['8:00']; ?></h2>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <small>Horario con desayuno</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mb-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">8:30</h6>
                                        <h2><?php echo $estadisticas['horarios']['8:30']; ?></h2>
                                    </div>
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                                <small>Horario sin desayuno</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Información sobre el servicio -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle mr-2"></i> Información del Servicio</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Horarios y Servicios</h5>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>7:30</strong> - Entrada con desayuno completo
                                            <span class="badge badge-warning">Con desayuno</span>
                                        </div>
                                        <span class="badge badge-primary badge-pill"><?php echo $estadisticas['horarios']['7:30']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>8:00</strong> - Entrada con desayuno ligero
                                            <span class="badge badge-warning">Con desayuno</span>
                                        </div>
                                        <span class="badge badge-primary badge-pill"><?php echo $estadisticas['horarios']['8:00']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>8:30</strong> - Entrada sin desayuno
                                        </div>
                                        <span class="badge badge-primary badge-pill"><?php echo $estadisticas['horarios']['8:30']; ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>9:00</strong> - Entrada regular (sin coste adicional)
                                        </div>
                                        <span class="badge badge-secondary badge-pill">-</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>Notas</h5>
                                <div class="alert alert-info">
                                    <p><i class="fas fa-utensils"></i> <strong>Desayuno completo:</strong> Incluye leche/zumo, tostadas/galletas y fruta.</p>
                                    <p><i class="fas fa-utensils"></i> <strong>Desayuno ligero:</strong> Incluye leche/zumo y galletas.</p>
                                    <p><i class="fas fa-info-circle"></i> El servicio de guardería matinal está disponible todos los días del programa.</p>
                                    <p><i class="fas fa-allergies"></i> <strong>Alergias:</strong> Se respetan todas las alergias e intolerancias alimentarias.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Participantes con servicio de guardería matinal -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users mr-2"></i> Control de Asistencia - <?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($participantesGuarderia)): ?>
                            <div class="alert alert-info">
                                No hay participantes con el servicio de guardería matinal contratado.
                            </div>
                        <?php else: ?>
                            <form action="" method="post">
                                <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-fixed">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th style="width: 50px">Asistió</th>
                                                <th>Nombre</th>
                                                <th>Edad</th>
                                                <th>Hora Contratada</th>
                                                <th>Hora Entrada Real</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($participantesGuarderia as $p): ?>
                                                <?php 
                                                // Calculamos la edad
                                                $fechaNac = new DateTime($p['fecha_nacimiento']);
                                                $hoy = new DateTime();
                                                $edad = $fechaNac->diff($hoy)->y;
                                                
                                                // Verificamos si asistió
                                                $asistio = isset($asistencias[$p['id']]);
                                                $horaEntrada = $asistio ? $asistencias[$p['id']]['hora_entrada'] : '';
                                                $observacion = $asistio ? $asistencias[$p['id']]['observaciones'] : '';
                                                
                                                // Clase CSS según horario
                                                $claseHorario = '';
                                                if ($p['guarderia_matinal'] === '7:30') {
                                                    $claseHorario = 'horario-730';
                                                } elseif ($p['guarderia_matinal'] === '8:00') {
                                                    $claseHorario = 'horario-800';
                                                } elseif ($p['guarderia_matinal'] === '8:30') {
                                                    $claseHorario = 'horario-830';
                                                }
                                                ?>
                                                <tr class="<?php echo $claseHorario; ?>">
                                                    <td class="text-center">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" 
                                                                id="asistencia-<?php echo $p['id']; ?>" 
                                                                name="asistencia[]" 
                                                                value="<?php echo $p['id']; ?>"
                                                                <?php echo $asistio ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label" for="asistencia-<?php echo $p['id']; ?>"></label>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php echo $p['nombre']; ?>
                                                        <small class="d-block text-muted">Resp: <?php echo $p['responsable_nombre']; ?></small>
                                                    </td>
                                                    <td><?php echo $edad; ?> años</td>
                                                    <td>
                                                        <span class="badge badge-info"><?php echo $p['guarderia_matinal']; ?></span>
                                                        <?php if ($p['guarderia_matinal'] === '7:30' || $p['guarderia_matinal'] === '8:00'): ?>
                                                            <small class="d-block">Con desayuno</small>
                                                        <?php else: ?>
                                                            <small class="d-block">Sin desayuno</small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <input type="time" class="form-control" 
                                                            name="hora_entrada[<?php echo $p['id']; ?>]" 
                                                            value="<?php echo $horaEntrada; ?>"
                                                            placeholder="HH:MM">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" 
                                                            name="observaciones[<?php echo $p['id']; ?>]" 
                                                            value="<?php echo $observacion; ?>"
                                                            placeholder="Observaciones">
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" name="marcar_asistencia" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar asistencia
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
