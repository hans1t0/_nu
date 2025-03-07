<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Servicios - Escuela de Verano";
$currentSection = "servicios";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';
$accion = isset($_GET['action']) ? $_GET['action'] : 'list';
$servicioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$participanteId = isset($_GET['participante_id']) ? (int)$_GET['participante_id'] : 0;
$servicio = null;

// Opciones para servicios
$opcionesGuarderia = ['7:30', '8:00', '8:30', '9:00', 'NO'];
$opcionesSiNo = ['SI', 'NO'];

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Si se está creando o actualizando un servicio
        if (isset($_POST['guardar_servicio'])) {
            $participante_id = $_POST['participante_id'];
            $socio_ampa = $_POST['socio_ampa'];
            $guarderia_matinal = $_POST['guarderia_matinal'];
            $comedor = $_POST['comedor'];
            
            // Verificamos que el participante existe
            $checkQuery = "SELECT * FROM participantes WHERE id = :id";
            $resultado = DatabaseConnectors::executeQuery('escuelaVerano', $checkQuery, [':id' => $participante_id]);
            
            if (empty($resultado)) {
                $mensaje = "Error: Participante no encontrado.";
                $tipoMensaje = "warning";
            } else {
                // Comprobar si ya existe un registro para este participante
                $checkServicioQuery = "SELECT id FROM servicios_contratados WHERE participante_id = :participante_id";
                $servicioExistente = DatabaseConnectors::executeQuery('escuelaVerano', $checkServicioQuery, [
                    ':participante_id' => $participante_id
                ]);
                
                if (!empty($servicioExistente)) {
                    // Si ya existe, actualizamos
                    $servicioId = $servicioExistente[0]['id'];
                    $query = "UPDATE servicios_contratados SET 
                                socio_ampa = :socio_ampa, 
                                guarderia_matinal = :guarderia_matinal, 
                                comedor = :comedor
                              WHERE id = :id";
                              
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [
                        ':socio_ampa' => $socio_ampa,
                        ':guarderia_matinal' => ($guarderia_matinal === 'NO' ? NULL : $guarderia_matinal),
                        ':comedor' => $comedor,
                        ':id' => $servicioId
                    ]);
                    
                    $mensaje = "Servicios actualizados correctamente.";
                } else {
                    // Si no existe, insertamos
                    $query = "INSERT INTO servicios_contratados (participante_id, socio_ampa, guarderia_matinal, comedor) 
                              VALUES (:participante_id, :socio_ampa, :guarderia_matinal, :comedor)";
                              
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [
                        ':participante_id' => $participante_id,
                        ':socio_ampa' => $socio_ampa,
                        ':guarderia_matinal' => ($guarderia_matinal === 'NO' ? NULL : $guarderia_matinal),
                        ':comedor' => $comedor
                    ]);
                    
                    $mensaje = "Servicios registrados correctamente.";
                }
                $tipoMensaje = "success";
                
                // Redirigimos a la página del participante
                header("Location: participantes.php?action=edit&id=$participante_id&msg=" . urlencode($mensaje) . "&tipo=$tipoMensaje#servicios");
                exit;
            }
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
            
            // Verificamos si ya tiene servicios contratados
            $servicioQuery = "SELECT * FROM servicios_contratados WHERE participante_id = :participante_id";
            $servicioResultado = DatabaseConnectors::executeQuery('escuelaVerano', $servicioQuery, [
                ':participante_id' => $participanteId
            ]);
            
            if (!empty($servicioResultado)) {
                $servicio = $servicioResultado[0];
                $servicioId = $servicio['id'];
                $accion = 'edit';
            }
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

// Cargamos el listado de servicios para la vista de lista
$serviciosListado = [];
if ($accion === 'list' && $participanteId === 0) {
    try {
        $query = "SELECT s.*, p.nombre as participante_nombre, 
                  r.nombre as responsable_nombre
                  FROM servicios_contratados s
                  LEFT JOIN participantes p ON s.participante_id = p.id
                  LEFT JOIN responsables r ON p.responsable_id = r.id
                  ORDER BY p.nombre";
        $serviciosListado = DatabaseConnectors::executeQuery('escuelaVerano', $query);
    } catch (Exception $e) {
        $mensaje = "Error al cargar la lista de servicios: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Cargamos resúmenes de servicios para el panel principal
$resumenServicios = [
    'total' => 0,
    'ampa' => 0,
    'guarderia' => 0,
    'comedor' => 0
];

if ($accion === 'list') {
    try {
        // Total de servicios
        $queryTotal = "SELECT COUNT(*) as total FROM servicios_contratados";
        $resultTotal = DatabaseConnectors::executeQuery('escuelaVerano', $queryTotal);
        $resumenServicios['total'] = $resultTotal[0]['total'] ?? 0;
        
        // Total socios AMPA
        $queryAmpa = "SELECT COUNT(*) as total FROM servicios_contratados WHERE socio_ampa = 'SI'";
        $resultAmpa = DatabaseConnectors::executeQuery('escuelaVerano', $queryAmpa);
        $resumenServicios['ampa'] = $resultAmpa[0]['total'] ?? 0;
        
        // Total guardería matinal
        $queryGuarderia = "SELECT COUNT(*) as total FROM servicios_contratados WHERE guarderia_matinal IS NOT NULL";
        $resultGuarderia = DatabaseConnectors::executeQuery('escuelaVerano', $queryGuarderia);
        $resumenServicios['guarderia'] = $resultGuarderia[0]['total'] ?? 0;
        
        // Total comedor
        $queryComedor = "SELECT COUNT(*) as total FROM servicios_contratados WHERE comedor = 'SI'";
        $resultComedor = DatabaseConnectors::executeQuery('escuelaVerano', $queryComedor);
        $resumenServicios['comedor'] = $resultComedor[0]['total'] ?? 0;
    } catch (Exception $e) {
        $mensaje = "Error al cargar resumen de servicios: " . $e->getMessage();
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
        .servicio-card {
            transition: transform 0.3s;
        }
        .servicio-card:hover {
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
                            <a class="nav-link" href="periodos.php">
                                <i class="fas fa-calendar-alt"></i> Periodos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="servicios.php">
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
                            Registrar Servicios para <?php echo $participante ? $participante['nombre'] : ''; ?>
                        <?php elseif ($accion === 'edit' && $participanteId > 0): ?>
                            Editar Servicios para <?php echo $participante ? $participante['nombre'] : ''; ?>
                        <?php else: ?>
                            Gestión de Servicios
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
                    <!-- Resumen de servicios -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card servicio-card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><?php echo $resumenServicios['total']; ?></h1>
                                    <p class="lead">Total servicios</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card servicio-card bg-success text-white">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><?php echo $resumenServicios['ampa']; ?></h1>
                                    <p class="lead">Socios AMPA</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card servicio-card bg-info text-white">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><?php echo $resumenServicios['guarderia']; ?></h1>
                                    <p class="lead">Guardería Matinal</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card servicio-card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h1 class="display-4"><?php echo $resumenServicios['comedor']; ?></h1>
                                    <p class="lead">Comedor</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Enlaces a servicios específicos -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">Servicios Disponibles</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <a href="servicios/comedor.php" class="card h-100 text-decoration-none servicio-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-utensils fa-4x text-info mb-3"></i>
                                            <h4>Comedor</h4>
                                            <p class="text-muted">Gestione el servicio de comedor escolar</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="servicios/guarderia_matinal.php" class="card h-100 text-decoration-none servicio-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-sun fa-4x text-warning mb-3"></i>
                                            <h4>Guardería Matinal</h4>
                                            <p class="text-muted">Gestione el servicio de guardería matinal</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <a href="servicios/talleres.php" class="card h-100 text-decoration-none servicio-card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-paint-brush fa-4x text-success mb-3"></i>
                                            <h4>Talleres</h4>
                                            <p class="text-muted">Gestione los talleres y actividades programadas</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Lista de servicios contratados -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Servicios Contratados</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Participante</th>
                                            <th>Responsable</th>
                                            <th>Socio AMPA</th>
                                            <th>Guardería Matinal</th>
                                            <th>Comedor</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($serviciosListado)): ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No hay servicios contratados.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($serviciosListado as $serv): ?>
                                                <tr>
                                                    <td><?php echo $serv['id']; ?></td>
                                                    <td><?php echo $serv['participante_nombre']; ?></td>
                                                    <td><?php echo $serv['responsable_nombre']; ?></td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $serv['socio_ampa'] === 'SI' ? 'success' : 'secondary'; ?>">
                                                            <?php echo $serv['socio_ampa']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($serv['guarderia_matinal']): ?>
                                                            <span class="badge badge-info"><?php echo $serv['guarderia_matinal']; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">No</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-<?php echo $serv['comedor'] === 'SI' ? 'warning' : 'secondary'; ?>">
                                                            <?php echo $serv['comedor']; ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="?action=edit&participante_id=<?php echo $serv['participante_id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="participantes.php?action=edit&id=<?php echo $serv['participante_id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-user"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                
                <?php elseif (($accion === 'add' || $accion === 'edit') && $participanteId > 0): ?>
                    <!-- Formulario de creación/edición de servicios -->
                    <?php if ($participante): ?>
                        <div class="alert alert-info mb-4">
                            <h5><i class="fas fa-info-circle"></i> Información del participante</h5>
                            <p class="mb-1"><strong>Nombre:</strong> <?php echo $participante['nombre']; ?></p>
                            <p class="mb-1"><strong>Fecha de nacimiento:</strong> <?php echo date('d/m/Y', strtotime($participante['fecha_nacimiento'])); ?></p>
                            <p class="mb-0"><strong>Responsable:</strong> <?php echo $participante['responsable_nombre']; ?></p>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Servicios Contratados</h5>
                            </div>
                            <div class="card-body">
                                <form action="" method="post" id="formServicios">
                                    <input type="hidden" name="participante_id" value="<?php echo $participanteId; ?>">
                                    
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label for="socio_ampa">¿Socio AMPA?</label>
                                            <select class="form-control" id="socio_ampa" name="socio_ampa" required>
                                                <?php foreach($opcionesSiNo as $opcion): ?>
                                                    <option value="<?php echo $opcion; ?>" 
                                                        <?php echo (isset($servicio) && $servicio['socio_ampa'] == $opcion) ? 'selected' : ''; ?>>
                                                        <?php echo $opcion; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label for="guarderia_matinal">Guardería matinal</label>
                                            <select class="form-control" id="guarderia_matinal" name="guarderia_matinal" required>
                                                <?php foreach($opcionesGuarderia as $opcion): ?>
                                                    <option value="<?php echo $opcion; ?>" 
                                                        <?php 
                                                        if (isset($servicio)) {
                                                            if (($servicio['guarderia_matinal'] === null && $opcion === 'NO') || 
                                                                $servicio['guarderia_matinal'] === $opcion) {
                                                                echo 'selected';
                                                            }
                                                        }
                                                        ?>>
                                                        <?php echo $opcion; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="form-text text-muted">
                                                Seleccione la hora de entrada o "NO" si no requiere el servicio.
                                            </small>
                                        </div>
                                        
                                        <div class="form-group col-md-4">
                                            <label for="comedor">¿Servicio de comedor?</label>
                                            <select class="form-control" id="comedor" name="comedor" required>
                                                <?php foreach($opcionesSiNo as $opcion): ?>
                                                    <option value="<?php echo $opcion; ?>" 
                                                        <?php echo (isset($servicio) && $servicio['comedor'] == $opcion) ? 'selected' : ''; ?>>
                                                        <?php echo $opcion; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mt-4">
                                        <button type="submit" name="guardar_servicio" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Guardar servicios
                                        </button>
                                        <a href="participantes.php?action=edit&id=<?php echo $participanteId; ?>" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Información adicional sobre servicios -->
                        <div class="row mt-4">
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Socio AMPA</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Ser socio del AMPA proporciona descuentos en las actividades y servicios de la escuela de verano.</p>
                                        <p><strong>Ventajas:</strong></p>
                                        <ul>
                                            <li>Descuento del 10% en la inscripción</li>
                                            <li>Prioridad en la selección de actividades</li>
                                            <li>Descuentos en material escolar</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">Guardería Matinal</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>Servicio para familias que necesitan dejar a sus hijos antes del inicio regular de actividades.</p>
                                        <p><strong>Horarios disponibles:</strong></p>
                                        <ul>
                                            <li>7:30 - Desayuno incluido</li>
                                            <li>8:00 - Desayuno incluido</li>
                                            <li>8:30 - Sin desayuno</li>
                                            <li>9:00 - Entrada regular sin coste adicional</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-header bg-warning text-white">
                                        <h5 class="mb-0">Comedor</h5>
                                    </div>
                                    <div class="card-body">
                                        <p>El servicio de comedor ofrece menús equilibrados y supervisados por nutricionistas.</p>
                                        <p><strong>Características:</strong></p>
                                        <ul>
                                            <li>Menús adaptados a alergias e intolerancias</li>
                                            <li>Comida casera y saludable</li>
                                            <li>Supervisión durante la comida</li>
                                            <li>Horario: 14:00 - 15:30</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Debe seleccionar un participante para gestionar sus servicios.
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
</body>
</html>
