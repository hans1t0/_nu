<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../../admin/database/DatabaseConnectors.php';

// Inicializar variables para estadísticas
$total_alumnos = 0;
$total_centros = 0;
$asistencias_hoy = 0;
$pagos_mes = 0;
$monto_mes = 0;
$error_message = null;
$version_sistema = '1.2.0'; // Versión del sistema
$fecha_actualizacion = '2024-03-15'; // Última actualización del sistema

// Intentar establecer la conexión usando DatabaseConnectors
try {
    // Obtener la conexión 'matinera'
    $conn = DatabaseConnectors::getConnection('matinera');
    $connection_status = true;
    
    // Información sobre la base de datos
    $stmt = $conn->query("SELECT VERSION() as version, DATABASE() as db_name");
    $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verificar estado del servidor MySQL
    $stmt = $conn->query("SHOW STATUS LIKE 'Uptime'");
    $uptime = $stmt->fetch(PDO::FETCH_ASSOC);
    $server_uptime = floor($uptime['Value'] / 86400) . " días " . 
                    floor(($uptime['Value'] % 86400) / 3600) . " horas";
    
    // Total de alumnos
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hijos");
        $stmt->execute();
        $row = $stmt->fetch();
        $total_alumnos = $row['total'] ?? 0;
    } catch (Exception $e) {
        // Silenciar error si la tabla no existe
    }
    
    // Total de centros
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM colegios");
        $stmt->execute();
        $row = $stmt->fetch();
        $total_centros = $row['total'] ?? 0;
    } catch (Exception $e) {
        // Silenciar error si la tabla no existe
    }
    
    // Asistencias de hoy
    try {
        $hoy = date('Y-m-d');
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM asistencias WHERE fecha = :fecha AND asistio = 1");
        $stmt->execute([':fecha' => $hoy]);
        $row = $stmt->fetch();
        $asistencias_hoy = $row['total'] ?? 0;
    } catch (Exception $e) {
        // Silenciar error si la tabla no existe
    }
    
    // Total de responsables
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM responsables");
        $stmt->execute();
        $row = $stmt->fetch();
        $total_responsables = $row['total'] ?? 0;
    } catch (Exception $e) {
        $total_responsables = 0;
    }
    
    // Pagos del mes actual - No existe tabla específica de pagos en la estructura actual
    // Por lo tanto, esta sección se mantendrá en cero o se podría adaptar más adelante
    $pagos_mes = 0;
    $monto_mes = 0;
    
    // Verificar tablas existentes y su estructura
    $required_tables = [
        'hijos' => ['id', 'nombre', 'responsable_id', 'fecha_nacimiento', 'colegio_id', 'curso', 'hora_entrada', 'desayuno'],
        'colegios' => ['id', 'nombre', 'codigo', 'tiene_desayuno'],
        'asistencias' => ['id', 'hijo_id', 'fecha', 'asistio', 'desayuno', 'hora_entrada', 'observaciones'],
        'responsables' => ['id', 'nombre', 'dni', 'email', 'telefono', 'observaciones', 'forma_pago', 'iban']
    ];
    
    $missing_tables = [];
    $incomplete_tables = [];
    
    foreach ($required_tables as $table => $required_fields) {
        try {
            $stmt = $conn->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() == 0) {
                $missing_tables[] = $table;
                continue;
            }
            
            // Verificar estructura de la tabla
            $stmt = $conn->query("SHOW COLUMNS FROM $table");
            $existing_fields = [];
            while ($field = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $existing_fields[] = $field['Field'];
            }
            
            $missing_fields = array_diff($required_fields, $existing_fields);
            if (!empty($missing_fields)) {
                $incomplete_tables[$table] = $missing_fields;
            }
            
        } catch (Exception $e) {
            $missing_tables[] = $table;
        }
    }
    
    $tables_exist = empty($missing_tables) && empty($incomplete_tables);
    
    // Verificar permisos de escritura
    try {
        $write_permission = true;
        if (!empty($conn->getAttribute(PDO::ATTR_DRIVER_NAME))) {
            $temp_table = 'temp_' . rand(1000, 9999);
            $conn->exec("CREATE TEMPORARY TABLE $temp_table (id INT)");
            $conn->exec("DROP TEMPORARY TABLE IF EXISTS $temp_table");
        }
    } catch (Exception $e) {
        $write_permission = false;
    }
    
    // Estadísticas adicionales
    $stats = [
        'asistencias_mes' => 0,
        'desayunos_mes' => 0
    ];
    
    try {
        $mes_actual = date('Y-m');
        $stmt = $conn->prepare("SELECT 
                              COUNT(*) as total, 
                              SUM(CASE WHEN desayuno = 1 THEN 1 ELSE 0 END) as desayunos
                          FROM asistencias 
                          WHERE fecha LIKE :mes AND asistio = 1");
        $stmt->execute([':mes' => $mes_actual.'%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $stats['asistencias_mes'] = $row['total'] ?? 0;
            $stats['desayunos_mes'] = $row['desayunos'] ?? 0;
        }
    } catch (Exception $e) {
        // Silenciar error
    }
    
} catch (Exception $e) {
    $error_message = "Error de conexión: " . $e->getMessage();
    $connection_status = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Matinera</title>
    <!-- Incluir Bootstrap directamente desde CDN para evitar dependencias de archivos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Estilos básicos */
        body {
            padding-top: 70px; /* Aumentado para dejar espacio al menú fijo */
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }
        .card-header {
            background-color: #f1f1f1;
        }
        footer {
            margin-top: 30px;
            padding: 20px 0;
            text-align: center;
            background-color: #f1f1f1;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .navbar-nav .nav-link.active {
            font-weight: bold;
            color: #0d6efd;
        }
        .system-status .list-group-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .badge-status {
            font-size: 85%;
            padding: 0.35em 0.65em;
        }
        .progress-bar {
            transition: width 1.5s ease;
        }
    </style>
</head>
<body>
    <!-- Menú de navegación principal -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-child"></i> Matinera
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                    aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">
                            <i class="fas fa-home"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alumnos.php">
                            <i class="fas fa-user-graduate"></i> Alumnos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="responsables.php">
                            <i class="fas fa-users"></i> Responsables
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="asistencia.php">
                            <i class="fas fa-calendar-check"></i> Asistencia
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="centros.php">
                            <i class="fas fa-school"></i> Centros
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="informes.php">
                            <i class="fas fa-chart-bar"></i> Informes
                        </a>
                    </li>
                </ul>
                <div class="d-flex">
                    <a href="../../../admin/index.php" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Volver al Panel Admin
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <h1 class="mb-4 text-center">Gestión de Matinera (Guardería Matinal)</h1>
        
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total Alumnos</h5>
                                <h2 class="mb-0"><?php echo $total_alumnos; ?></h2>
                            </div>
                            <i class="fas fa-user-graduate fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Asistencias Hoy</h5>
                                <h2 class="mb-0"><?php echo $asistencias_hoy; ?></h2>
                            </div>
                            <i class="fas fa-calendar-check fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Responsables</h5>
                                <h2 class="mb-0"><?php echo $total_responsables ?? 0; ?></h2>
                            </div>
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title">Total Centros</h5>
                                <h2 class="mb-0"><?php echo $total_centros; ?></h2>
                            </div>
                            <i class="fas fa-school fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Módulos de Gestión</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-user-graduate me-2"></i> Alumnos</h5>
                                        <p class="card-text">Gestión de alumnos inscritos en la matinera.</p>
                                        <a href="alumnos.php" class="btn btn-primary">Administrar Alumnos</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-users me-2"></i> Responsables</h5>
                                        <p class="card-text">Gestión de padres/madres y tutores.</p>
                                        <a href="responsables.php" class="btn btn-primary">Gestionar Responsables</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-calendar-check me-2"></i> Asistencia</h5>
                                        <p class="card-text">Control de asistencia diaria de alumnos.</p>
                                        <a href="asistencia.php" class="btn btn-primary">Controlar Asistencia</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-school me-2"></i> Centros</h5>
                                        <p class="card-text">Administración de centros educativos.</p>
                                        <a href="centros.php" class="btn btn-primary">Administrar Centros</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="fas fa-chart-bar me-2"></i> Informes</h5>
                                        <p class="card-text">Generación de informes y estadísticas.</p>
                                        <a href="informes.php" class="btn btn-primary">Ver Informes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Resumen de Asistencias</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h4 class="alert-heading">Asistencias del mes</h4>
                            <h2><?php echo $stats['asistencias_mes']; ?></h2>
                            <hr>
                            <p class="mb-1">Con desayuno: <?php echo $stats['desayunos_mes']; ?></p>
                            <div class="progress mt-2" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: 0%" 
                                     aria-valuenow="<?php echo ($stats['asistencias_mes'] > 0) ? ($stats['desayunos_mes'] / $stats['asistencias_mes']) * 100 : 0; ?>" 
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small class="text-muted"><?php echo ($stats['asistencias_mes'] > 0) ? round(($stats['desayunos_mes'] / $stats['asistencias_mes']) * 100) : 0; ?>% incluyen desayuno</small>
                        </div>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="informes.php?tipo=asistencia_mensual&mes=<?php echo date('Y-m'); ?>" class="btn btn-outline-primary">Ver detalles de asistencias</a>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Estado del Sistema</h5>
                        <span class="badge <?php echo $connection_status ? 'bg-success' : 'bg-danger'; ?>">
                            <?php echo $connection_status ? 'Operativo' : 'Error'; ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <ul class="list-group system-status">
                            <li class="list-group-item">
                                Base de datos
                                <span class="badge <?php echo $connection_status ? 'bg-success' : 'bg-danger'; ?> badge-status">
                                    <?php echo $connection_status ? 'Conectado' : 'Error'; ?>
                                </span>
                            </li>
                            <li class="list-group-item">
                                Tablas del sistema
                                <?php if (empty($missing_tables) && empty($incomplete_tables)): ?>
                                    <span class="badge bg-success badge-status">Completas</span>
                                <?php elseif (empty($missing_tables) && !empty($incomplete_tables)): ?>
                                    <span class="badge bg-warning badge-status">Incompletas</span>
                                <?php else: ?>
                                    <span class="badge bg-danger badge-status">Faltan tablas</span>
                                <?php endif; ?>
                            </li>
                            <?php if ($connection_status): ?>
                                <li class="list-group-item">
                                    Permisos de escritura
                                    <span class="badge <?php echo $write_permission ? 'bg-success' : 'bg-warning'; ?> badge-status">
                                        <?php echo $write_permission ? 'OK' : 'Solo lectura'; ?>
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    Versión MySQL
                                    <span class="text-muted"><?php echo isset($db_info['version']) ? $db_info['version'] : 'N/A'; ?></span>
                                </li>
                                <li class="list-group-item">
                                    Uptime servidor
                                    <span class="text-muted"><?php echo isset($server_uptime) ? $server_uptime : 'N/A'; ?></span>
                                </li>
                            <?php endif; ?>
                            <li class="list-group-item">
                                Versión del sistema
                                <span class="badge bg-info badge-status"><?php echo $version_sistema; ?></span>
                            </li>
                            <li class="list-group-item">
                                Última actualización
                                <span class="text-muted"><?php echo date('d/m/Y', strtotime($fecha_actualizacion)); ?></span>
                            </li>
                        </ul>
                        
                        <?php if (!empty($missing_tables)): ?>
                            <div class="alert alert-danger mt-3">
                                <h6 class="alert-heading">Tablas faltantes:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($missing_tables as $table): ?>
                                        <li><?php echo $table; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($incomplete_tables)): ?>
                            <div class="alert alert-warning mt-3">
                                <h6 class="alert-heading">Tablas incompletas:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($incomplete_tables as $table => $fields): ?>
                                        <li><?php echo $table; ?> (faltan campos: <?php echo implode(', ', $fields); ?>)</li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!$connection_status || !empty($missing_tables)): ?>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle"></i> Para configurar la base de datos correctamente, ejecute el script de instalación:
                                <code class="d-block mt-2">mysql -u [usuario] -p [base_de_datos] < guarderia_matinal.sql</code>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <footer>
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Matinera</p>
            </div>
        </footer>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animar barra de progreso al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const progressBars = document.querySelectorAll('.progress-bar');
                progressBars.forEach(function(bar) {
                    const value = bar.getAttribute('aria-valuenow');
                    bar.style.width = value + '%';
                });
            }, 200);
        });
    </script>
</body>
</html>
