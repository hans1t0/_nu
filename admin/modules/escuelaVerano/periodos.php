<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Periodos - Escuela de Verano";
$currentSection = "periodos";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';
$accion = isset($_GET['action']) ? $_GET['action'] : 'list';
$periodoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$participanteId = isset($_GET['participante_id']) ? (int)$_GET['participante_id'] : 0;
$periodo = null;

// Inicializamos un array con los periodos disponibles para la escuela de verano
$periodos_disponibles = [
    'julio1' => [
        'nombre' => 'Primera semana de julio',
        'fecha_inicio' => '2024-07-01',
        'fecha_fin' => '2024-07-06'
    ],
    'julio2' => [
        'nombre' => 'Segunda semana de julio',
        'fecha_inicio' => '2024-07-07',
        'fecha_fin' => '2024-07-13'
    ],
    'julio3' => [
        'nombre' => 'Tercera semana de julio',
        'fecha_inicio' => '2024-07-14',
        'fecha_fin' => '2024-07-20'
    ],
    'julio4' => [
        'nombre' => 'Cuarta semana de julio',
        'fecha_inicio' => '2024-07-21',
        'fecha_fin' => '2024-07-27'
    ],
    'julio5' => [
        'nombre' => 'Quinta semana de julio',
        'fecha_inicio' => '2024-07-28',
        'fecha_fin' => '2024-07-31'
    ],
    'agosto1' => [
        'nombre' => 'Primera semana de agosto',
        'fecha_inicio' => '2024-08-01',
        'fecha_fin' => '2024-08-03'
    ],
    'agosto2' => [
        'nombre' => 'Segunda semana de agosto',
        'fecha_inicio' => '2024-08-04',
        'fecha_fin' => '2024-08-10'
    ],
    'agosto3' => [
        'nombre' => 'Tercera semana de agosto',
        'fecha_inicio' => '2024-08-11',
        'fecha_fin' => '2024-08-17'
    ],
    'agosto4' => [
        'nombre' => 'Cuarta semana de agosto',
        'fecha_inicio' => '2024-08-18',
        'fecha_fin' => '2024-08-24'
    ],
    'agosto5' => [
        'nombre' => 'Quinta semana de agosto',
        'fecha_inicio' => '2024-08-25',
        'fecha_fin' => '2024-08-31'
    ]
];

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Si se está creando o actualizando un periodo
        if (isset($_POST['guardar_periodo'])) {
            $participante_id = $_POST['participante_id'];
            $semanas = $_POST['semanas'] ?? [];
            
            // Verificamos que el participante existe
            $checkQuery = "SELECT * FROM participantes WHERE id = :id";
            $resultado = DatabaseConnectors::executeQuery('escuelaVerano', $checkQuery, [':id' => $participante_id]);
            
            if (empty($resultado)) {
                $mensaje = "Error: Participante no encontrado.";
                $tipoMensaje = "warning";
            } else {
                // Si es una edición, primero eliminamos los periodos actuales
                if ($periodoId > 0) {
                    $deleteQuery = "DELETE FROM periodos_inscritos WHERE id = :id";
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $deleteQuery, [':id' => $periodoId]);
                    $mensaje = "Periodo actualizado correctamente.";
                } else {
                    // Si son periodos nuevos, verificamos si ya existen algunos periodos para este participante
                    $mensaje = "Periodos registrados correctamente.";
                }
                
                $tipoMensaje = "success";
                $countInsertados = 0;
                
                // Insertamos los nuevos periodos seleccionados
                if (!empty($semanas)) {
                    foreach ($semanas as $semana) {
                        if (isset($periodos_disponibles[$semana])) {
                            $periodo_info = $periodos_disponibles[$semana];
                            
                            // Primero verificamos si ya existe este periodo para este participante
                            $checkPeriodoQuery = "SELECT COUNT(*) as total FROM periodos_inscritos 
                                                  WHERE participante_id = :participante_id AND semana = :semana";
                            $resultCheck = DatabaseConnectors::executeQuery('escuelaVerano', $checkPeriodoQuery, [
                                ':participante_id' => $participante_id,
                                ':semana' => $semana
                            ]);
                            
                            // Si no existe, lo insertamos
                            if ($resultCheck[0]['total'] == 0) {
                                $insertQuery = "INSERT INTO periodos_inscritos 
                                               (participante_id, semana, fecha_inicio, fecha_fin) 
                                               VALUES 
                                               (:participante_id, :semana, :fecha_inicio, :fecha_fin)";
                                               
                                DatabaseConnectors::executeNonQuery('escuelaVerano', $insertQuery, [
                                    ':participante_id' => $participante_id,
                                    ':semana' => $semana,
                                    ':fecha_inicio' => $periodo_info['fecha_inicio'],
                                    ':fecha_fin' => $periodo_info['fecha_fin']
                                ]);
                                
                                $countInsertados++;
                            }
                        }
                    }
                }
                
                if ($countInsertados === 0 && !empty($semanas)) {
                    $mensaje = "No se añadieron nuevos periodos. Es posible que ya estuvieran registrados.";
                    $tipoMensaje = "info";
                } elseif ($countInsertados > 0) {
                    $mensaje = "Se añadieron $countInsertados periodos correctamente.";
                    $tipoMensaje = "success";
                }
                
                // Redirigimos a la lista de periodos del participante
                header("Location: participantes.php?action=edit&id=$participante_id&msg=" . urlencode($mensaje) . "&tipo=$tipoMensaje#periodos");
                exit;
            }
        }
        
        // Si se está eliminando un periodo
        if (isset($_POST['eliminar_periodo'])) {
            $id = $_POST['id'];
            $participanteId = $_POST['participante_id']; // Para redirigir después
            
            $query = "DELETE FROM periodos_inscritos WHERE id = :id";
            DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [':id' => $id]);
            
            $mensaje = "Periodo eliminado correctamente.";
            $tipoMensaje = "success";
            
            // Redirigimos a la página de edición del participante
            header("Location: participantes.php?action=edit&id=$participanteId&msg=" . urlencode($mensaje) . "&tipo=$tipoMensaje#periodos");
            exit;
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

// Si estamos editando, cargamos los datos del periodo
if ($accion === 'edit' && $periodoId > 0) {
    try {
        $query = "SELECT * FROM periodos_inscritos WHERE id = :id";
        $resultado = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $periodoId]);
        
        if (!empty($resultado)) {
            $periodo = $resultado[0];
            // Si no se especificó participante_id en la URL, lo tomamos del periodo
            if ($participanteId == 0) {
                $participanteId = $periodo['participante_id'];
            }
        } else {
            $mensaje = "Periodo no encontrado.";
            $tipoMensaje = "warning";
            $accion = 'list';
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar el periodo: " . $e->getMessage();
        $tipoMensaje = "danger";
        $accion = 'list';
    }
}

// Cargamos los datos del participante si tenemos su ID
$participante = null;
if ($participanteId > 0) {
    try {
        $query = "SELECT p.*, r.nombre as responsable_nombre 
                  FROM participantes p 
                  LEFT JOIN responsables r ON p.responsable_id = r.id
                  WHERE p.id = :id";
        $resultado = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $participanteId]);
        
        if (!empty($resultado)) {
            $participante = $resultado[0];
        } else {
            $mensaje = "Participante no encontrado.";
            $tipoMensaje = "warning";
            // Si estamos en modo add, redirigimos a la lista de participantes
            if ($accion === 'add') {
                header("Location: participantes.php?msg=" . urlencode($mensaje) . "&tipo=$tipoMensaje");
                exit;
            }
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar datos del participante: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Cargamos los periodos ya inscritos para este participante
$periodos_inscritos = [];
$periodos_inscritos_keys = [];
if ($participanteId > 0) {
    try {
        $query = "SELECT * FROM periodos_inscritos WHERE participante_id = :participante_id";
        $periodos_inscritos = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':participante_id' => $participanteId]);
        
        // Creamos un array con las claves de los periodos inscritos para facilitar la comprobación
        foreach ($periodos_inscritos as $p) {
            $periodos_inscritos_keys[] = $p['semana'];
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar periodos inscritos: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Para el listado general de periodos
$periodos = [];
$totalParticipantesPorPeriodo = [];
if ($accion === 'list' && $participanteId == 0) {
    try {
        // Obtenemos todos los periodos agrupados por semana
        $query = "SELECT semana, fecha_inicio, fecha_fin, COUNT(*) as total_participantes
                  FROM periodos_inscritos
                  GROUP BY semana, fecha_inicio, fecha_fin
                  ORDER BY fecha_inicio ASC";
        $periodos = DatabaseConnectors::executeQuery('escuelaVerano', $query);
        
        // Calculamos el porcentaje de ocupación (asumiendo un máximo de 50 niños por semana)
        $maxParticipantes = 50;
        foreach ($periodos as &$p) {
            $p['porcentaje'] = round(($p['total_participantes'] / $maxParticipantes) * 100);
            $p['nombre'] = isset($periodos_disponibles[$p['semana']]) ? $periodos_disponibles[$p['semana']]['nombre'] : $p['semana'];
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar la lista de periodos: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
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
        .periodo-card {
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .periodo-card.selected {
            border-color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }
        .periodo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .progress-thin {
            height: 8px;
            margin-bottom: 0;
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
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="responsables.php">
                                <i class="fas fa-users"></i> Responsables
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="participantes.php">
                                <i class="fas fa-child"></i> Participantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="periodos.php">
                                <i class="fas fa-calendar-alt"></i> Periodos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="servicios.php">
                                <i class="fas fa-concierge-bell"></i> Servicios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reportes.php">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                    </ul>
                    
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Servicios específicos</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="servicios/comedor.php">
                                <i class="fas fa-utensils"></i> Comedor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="servicios/guarderia_matinal.php">
                                <i class="fas fa-sun"></i> Guardería Matinal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="servicios/talleres.php">
                                <i class="fas fa-paint-brush"></i> Talleres
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-5">
                        <a href="../../index.php" class="btn btn-secondary btn-sm btn-block">
                            <i class="fas fa-arrow-left"></i> Volver al panel principal
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main role="main" class="col-md-10 ml-sm-auto px-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>
                        <?php if ($accion === 'add' && $participanteId > 0): ?>
                            Inscribir Periodos para <?php echo $participante ? $participante['nombre'] : ''; ?>
                        <?php elseif ($accion === 'edit'): ?>
                            Editar Periodo
                        <?php else: ?>
                            Gestión de Periodos
                        <?php endif; ?>
                    </h1>
                </div>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($accion === 'list' && $participanteId === 0): ?>
                    <!-- Vista de lista general de periodos -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Resumen de Periodos</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php if (empty($periodos)): ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">No hay periodos registrados.</div>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($periodos as $p): ?>
                                        <div class="col-md-6 col-lg-4 mb-4">
                                            <div class="card h-100">
                                                <div class="card-header">
                                                    <h5 class="card-title mb-0">
                                                        <i class="fas fa-calendar-week mr-2"></i> <?php echo $p['nombre']; ?>
                                                    </h5>
                                                </div>
                                                <div class="card-body">
                                                    <p><strong>Periodo:</strong> 
                                                        <?php echo date('d/m/Y', strtotime($p['fecha_inicio'])); ?> - 
                                                        <?php echo date('d/m/Y', strtotime($p['fecha_fin'])); ?>
                                                    </p>
                                                    <p><strong>Participantes:</strong> <?php echo $p['total_participantes']; ?></p>
                                                    
                                                    <div class="progress progress-thin mt-2">
                                                        <?php 
                                                        $barClass = 'bg-success';
                                                        if ($p['porcentaje'] > 70) $barClass = 'bg-warning';
                                                        if ($p['porcentaje'] > 90) $barClass = 'bg-danger';
                                                        ?>
                                                        <div class="progress-bar <?php echo $barClass; ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $p['porcentaje']; ?>%" 
                                                             aria-valuenow="<?php echo $p['porcentaje']; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo $p['porcentaje']; ?>%
                                                        </div>
                                                    </div>
                                                    <small class="text-muted">
                                                        Ocupación: <?php echo $p['total_participantes']; ?>/50
                                                    </small>
                                                </div>
                                                <div class="card-footer">
                                                    <a href="reportes.php?semana=<?php echo $p['semana']; ?>" class="btn btn-sm btn-info">
                                                        <i class="fas fa-list"></i> Ver participantes
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                
                <?php elseif ($accion === 'add' || $accion === 'edit'): ?>
                    <!-- Formulario de creación/edición de períodos -->
                    <?php if ($participante): ?>
                        <div class="alert alert-info mb-4">
                            <h5><i class="fas fa-info-circle"></i> Información del participante</h5>
                            <p class="mb-1"><strong>Nombre:</strong> <?php echo $participante['nombre']; ?></p>
                            <p class="mb-1"><strong>Fecha de nacimiento:</strong> <?php echo date('d/m/Y', strtotime($participante['fecha_nacimiento'])); ?></p>
                            <p class="mb-0"><strong>Responsable:</strong> <?php echo $participante['responsable_nombre']; ?></p>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Selección de Periodos</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post" id="formPeriodos">
                                    <input type="hidden" name="participante_id" value="<?php echo $participanteId; ?>">
                                    
                                    <div class="alert alert-secondary">
                                        Seleccione los periodos a los que desea inscribir al participante. 
                                        Los periodos ya inscritos se muestran con fondo verde.
                                    </div>
                                    
                                    <div class="row">
                                        <?php foreach ($periodos_disponibles as $codigo => $p): ?>
                                            <?php 
                                            $isSelected = in_array($codigo, $periodos_inscritos_keys);
                                            $cardClass = $isSelected ? 'selected' : '';
                                            ?>
                                            <div class="col-md-6 col-lg-4 mb-4">
                                                <div class="card periodo-card <?php echo $cardClass; ?>" data-periodo="<?php echo $codigo; ?>">
                                                    <div class="card-body">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" 
                                                                id="check-<?php echo $codigo; ?>" 
                                                                name="semanas[]" 
                                                                value="<?php echo $codigo; ?>"
                                                                <?php echo $isSelected ? 'checked' : ''; ?>>
                                                            <label class="custom-control-label" for="check-<?php echo $codigo; ?>">
                                                                <h5 class="card-title"><?php echo $p['nombre']; ?></h5>
                                                            </label>
                                                        </div>
                                                        
                                                        <p class="card-text">
                                                            <i class="fas fa-calendar-day"></i> 
                                                            <?php echo date('d/m/Y', strtotime($p['fecha_inicio'])); ?> - 
                                                            <?php echo date('d/m/Y', strtotime($p['fecha_fin'])); ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    
                                    <div class="form-group mt-4">
                                        <button type="submit" name="guardar_periodo" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar periodos
                                        </button>
                                        <a href="participantes.php?action=edit&id=<?php echo $participanteId; ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Debe seleccionar un participante para inscribir periodos.
                            <a href="participantes.php" class="btn btn-primary mt-2">
                                <i class="fas fa-child"></i> Seleccionar Participante
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Script para manejar la selección de períodos -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const periodoCards = document.querySelectorAll('.periodo-card');
            
            periodoCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Evitar que se propague el evento al hacer clic en el checkbox
                    if (e.target.type !== 'checkbox') {
                        const checkbox = this.querySelector('input[type="checkbox"]');
                        checkbox.checked = !checkbox.checked;
                        
                        // Actualizar la clase de la tarjeta
                        if (checkbox.checked) {
                            this.classList.add('selected');
                        } else {
                            this.classList.remove('selected');
                        }
                    }
                });
            });
            
            // Cuando se hace clic en el checkbox, actualizar también la clase de la tarjeta
            document.querySelectorAll('.periodo-card input[type="checkbox"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const card = this.closest('.periodo-card');
                    if (this.checked) {
                        card.classList.add('selected');
                    } else {
                        card.classList.remove('selected');
                    }
                });
            });
        });
    </script>
</body>
</html>
