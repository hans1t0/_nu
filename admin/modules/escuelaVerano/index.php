<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Escuela de Verano - Administración";
$currentSection = "escuelaVerano";

// Intentamos conectarnos a la base de datos
try {
    // Obtenemos la conexión a la base de datos de escuelaVerano
    $db = DatabaseConnectors::getConnection('escuelaVerano');
    $connectionStatus = "Conexión establecida correctamente a la base de datos de Escuela de Verano.";
    $connectionClass = "success";
    
    // Consulta para obtener conteos rápidos
    $totalResponsablesQuery = "SELECT COUNT(*) as total FROM responsables";
    $totalParticipantesQuery = "SELECT COUNT(*) as total FROM participantes";
    $totalPeriodosQuery = "SELECT COUNT(*) as total FROM periodos_inscritos";
    $totalServiciosQuery = "SELECT COUNT(*) as total FROM servicios_contratados";
    
    $totalResponsables = DatabaseConnectors::executeQuery('escuelaVerano', $totalResponsablesQuery)[0]['total'];
    $totalParticipantes = DatabaseConnectors::executeQuery('escuelaVerano', $totalParticipantesQuery)[0]['total'];
    $totalPeriodos = DatabaseConnectors::executeQuery('escuelaVerano', $totalPeriodosQuery)[0]['total'];
    $totalServicios = DatabaseConnectors::executeQuery('escuelaVerano', $totalServiciosQuery)[0]['total'];
    
} catch (Exception $e) {
    // Si hay un error en la conexión, lo capturamos
    $connectionStatus = "Error de conexión: " . $e->getMessage();
    $connectionClass = "danger";
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
        .dashboard-card {
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
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
                            <a class="nav-link active" href="index.php">
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
                            <a class="nav-link" href="periodos.php">
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
                    <h1>Panel de Administración - Escuela de Verano</h1>
                </div>
                
                <?php if (isset($connectionClass) && $connectionClass !== "success"): ?>
                <div class="alert alert-<?php echo $connectionClass; ?>">
                    <?php echo $connectionStatus; ?>
                </div>
                <?php endif; ?>
                
                <div class="row mt-4">
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Responsables</h5>
                                        <h2><?php echo isset($totalResponsables) ? $totalResponsables : '0'; ?></h2>
                                    </div>
                                    <i class="fas fa-users fa-3x"></i>
                                </div>
                                <a href="responsables.php" class="text-white">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Participantes</h5>
                                        <h2><?php echo isset($totalParticipantes) ? $totalParticipantes : '0'; ?></h2>
                                    </div>
                                    <i class="fas fa-child fa-3x"></i>
                                </div>
                                <a href="participantes.php" class="text-white">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Periodos</h5>
                                        <h2><?php echo isset($totalPeriodos) ? $totalPeriodos : '0'; ?></h2>
                                    </div>
                                    <i class="fas fa-calendar-alt fa-3x"></i>
                                </div>
                                <a href="periodos.php" class="text-white">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Servicios</h5>
                                        <h2><?php echo isset($totalServicios) ? $totalServicios : '0'; ?></h2>
                                    </div>
                                    <i class="fas fa-concierge-bell fa-3x"></i>
                                </div>
                                <a href="servicios.php" class="text-white">Ver detalles <i class="fas fa-arrow-right"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Servicios Disponibles</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="servicios/comedor.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-utensils mr-2"></i> Comedor
                                        </div>
                                        <span class="badge badge-primary badge-pill">Ver</span>
                                    </a>
                                    <a href="servicios/guarderia_matinal.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-sun mr-2"></i> Guardería Matinal
                                        </div>
                                        <span class="badge badge-primary badge-pill">Ver</span>
                                    </a>
                                    <a href="servicios/talleres.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-paint-brush mr-2"></i> Talleres
                                        </div>
                                        <span class="badge badge-primary badge-pill">Ver</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="participantes.php?action=add" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-plus-circle mr-2"></i> Registrar nuevo participante
                                        </div>
                                        <span class="badge badge-success badge-pill">Nuevo</span>
                                    </a>
                                    <a href="responsables.php?action=add" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-plus-circle mr-2"></i> Registrar nuevo responsable
                                        </div>
                                        <span class="badge badge-success badge-pill">Nuevo</span>
                                    </a>
                                    <a href="reportes.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-file-export mr-2"></i> Generar reportes
                                        </div>
                                        <span class="badge badge-info badge-pill">Ver</span>
                                    </a>
                                </div>
                            </div>
                        </div>
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
