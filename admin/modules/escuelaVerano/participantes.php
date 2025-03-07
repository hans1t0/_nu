<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Participantes - Escuela de Verano";
$currentSection = "participantes";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';
$accion = isset($_GET['action']) ? $_GET['action'] : 'list';
$participanteId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$participante = null;

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Si se está creando o actualizando un participante
        if (isset($_POST['guardar_participante'])) {
            $responsableId = $_POST['responsable_id'];
            $nombre = $_POST['nombre'];
            $fechaNacimiento = $_POST['fecha_nacimiento'];
            $centroActual = $_POST['centro_actual'];
            $curso = $_POST['curso'];
            $alergias = $_POST['alergias'] ?? '';
            
            // Si es una actualización
            if ($participanteId > 0) {
                $query = "UPDATE participantes SET 
                            responsable_id = :responsable_id, 
                            nombre = :nombre, 
                            fecha_nacimiento = :fecha_nacimiento, 
                            centro_actual = :centro_actual, 
                            curso = :curso, 
                            alergias = :alergias 
                          WHERE id = :id";
                          
                DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [
                    ':responsable_id' => $responsableId,
                    ':nombre' => $nombre,
                    ':fecha_nacimiento' => $fechaNacimiento,
                    ':centro_actual' => $centroActual,
                    ':curso' => $curso,
                    ':alergias' => $alergias,
                    ':id' => $participanteId
                ]);
                
                $mensaje = "Participante actualizado correctamente.";
                $tipoMensaje = "success";
                
            } else { // Si es un nuevo registro
                $query = "INSERT INTO participantes (responsable_id, nombre, fecha_nacimiento, centro_actual, curso, alergias) 
                          VALUES (:responsable_id, :nombre, :fecha_nacimiento, :centro_actual, :curso, :alergias)";
                          
                DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [
                    ':responsable_id' => $responsableId,
                    ':nombre' => $nombre,
                    ':fecha_nacimiento' => $fechaNacimiento,
                    ':centro_actual' => $centroActual,
                    ':curso' => $curso,
                    ':alergias' => $alergias
                ]);
                
                $mensaje = "Participante registrado correctamente.";
                $tipoMensaje = "success";
            }
            
            // Redirigir a la lista después de guardar
            header("Location: participantes.php?msg=" . urlencode($mensaje) . "&tipo=" . $tipoMensaje);
            exit;
        }
        
        // Si se está eliminando un participante
        if (isset($_POST['eliminar_participante'])) {
            $id = $_POST['id'];
            $query = "DELETE FROM participantes WHERE id = :id";
            DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [':id' => $id]);
            
            $mensaje = "Participante eliminado correctamente.";
            $tipoMensaje = "success";
            
            header("Location: participantes.php?msg=" . urlencode($mensaje) . "&tipo=" . $tipoMensaje);
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

// Obtenemos la lista de responsables para el formulario
$responsables = [];
try {
    $query = "SELECT id, nombre, dni FROM responsables ORDER BY nombre";
    $responsables = DatabaseConnectors::executeQuery('escuelaVerano', $query);
} catch (Exception $e) {
    $mensaje = "Error al cargar responsables: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Si estamos editando, cargamos los datos del participante
if ($accion === 'edit' && $participanteId > 0) {
    try {
        $query = "SELECT * FROM participantes WHERE id = :id";
        $resultado = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $participanteId]);
        
        if (!empty($resultado)) {
            $participante = $resultado[0];
        } else {
            $mensaje = "Participante no encontrado.";
            $tipoMensaje = "warning";
            $accion = 'list';
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar el participante: " . $e->getMessage();
        $tipoMensaje = "danger";
        $accion = 'list';
    }
}

// Cargamos la lista de participantes si estamos en vista de lista
$participantes = [];
if ($accion === 'list') {
    try {
        $query = "SELECT p.*, r.nombre AS responsable_nombre 
                  FROM participantes p 
                  LEFT JOIN responsables r ON p.responsable_id = r.id 
                  ORDER BY p.nombre";
        $participantes = DatabaseConnectors::executeQuery('escuelaVerano', $query);
    } catch (Exception $e) {
        $mensaje = "Error al cargar la lista de participantes: " . $e->getMessage();
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
                            <a class="nav-link active" href="participantes.php">
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
                    <h1>
                        <?php if ($accion === 'add'): ?>
                            Registrar Nuevo Participante
                        <?php elseif ($accion === 'edit'): ?>
                            Editar Participante
                        <?php else: ?>
                            Gestión de Participantes
                        <?php endif; ?>
                    </h1>
                    
                    <?php if ($accion === 'list'): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="?action=add" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus"></i> Nuevo Participante
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensaje; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                
                <?php if ($accion === 'list'): ?>
                    <!-- Vista de lista -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Fecha Nacimiento</th>
                                    <th>Centro Actual</th>
                                    <th>Curso</th>
                                    <th>Responsable</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($participantes)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No hay participantes registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($participantes as $p): ?>
                                        <tr>
                                            <td><?php echo $p['id']; ?></td>
                                            <td><?php echo $p['nombre']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($p['fecha_nacimiento'])); ?></td>
                                            <td><?php echo $p['centro_actual']; ?></td>
                                            <td><?php echo $p['curso']; ?></td>
                                            <td><?php echo $p['responsable_nombre']; ?></td>
                                            <td>
                                                <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-toggle="modal" data-target="#deleteModal<?php echo $p['id']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Modal de confirmación de eliminación -->
                                                <div class="modal fade" id="deleteModal<?php echo $p['id']; ?>" tabindex="-1" role="dialog" 
                                                     aria-labelledby="deleteModalLabel" aria-hidden="true">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel">Confirmar eliminación</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                ¿Está seguro de que desea eliminar al participante <strong><?php echo $p['nombre']; ?></strong>?
                                                                Esta acción no se puede deshacer.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                <form action="" method="post">
                                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                                    <button type="submit" name="eliminar_participante" class="btn btn-danger">Eliminar</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                
                <?php else: ?>
                    <!-- Formulario de creación/edición -->
                    <div class="card">
                        <div class="card-body">
                            <form action="" method="post">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="nombre">Nombre completo:</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo isset($participante) ? $participante['nombre'] : ''; ?>" required>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="fecha_nacimiento">Fecha de nacimiento:</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                               value="<?php echo isset($participante) ? $participante['fecha_nacimiento'] : ''; ?>" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="centro_actual">Centro actual:</label>
                                        <input type="text" class="form-control" id="centro_actual" name="centro_actual"
                                               value="<?php echo isset($participante) ? $participante['centro_actual'] : ''; ?>" required>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="curso">Curso:</label>
                                        <select class="form-control" id="curso" name="curso" required>
                                            <option value="" selected disabled>Seleccione un curso</option>
                                            <option value="1INF" <?php echo (isset($participante) && $participante['curso'] == '1INF') ? 'selected' : ''; ?>>1º Infantil</option>
                                            <option value="2INF" <?php echo (isset($participante) && $participante['curso'] == '2INF') ? 'selected' : ''; ?>>2º Infantil</option>
                                            <option value="3INF" <?php echo (isset($participante) && $participante['curso'] == '3INF') ? 'selected' : ''; ?>>3º Infantil</option>
                                            <option value="1PRIM" <?php echo (isset($participante) && $participante['curso'] == '1PRIM') ? 'selected' : ''; ?>>1º Primaria</option>
                                            <option value="2PRIM" <?php echo (isset($participante) && $participante['curso'] == '2PRIM') ? 'selected' : ''; ?>>2º Primaria</option>
                                            <option value="3PRIM" <?php echo (isset($participante) && $participante['curso'] == '3PRIM') ? 'selected' : ''; ?>>3º Primaria</option>
                                            <option value="4PRIM" <?php echo (isset($participante) && $participante['curso'] == '4PRIM') ? 'selected' : ''; ?>>4º Primaria</option>
                                            <option value="5PRIM" <?php echo (isset($participante) && $participante['curso'] == '5PRIM') ? 'selected' : ''; ?>>5º Primaria</option>
                                            <option value="6PRIM" <?php echo (isset($participante) && $participante['curso'] == '6PRIM') ? 'selected' : ''; ?>>6º Primaria</option>
                                            <option value="1ESO" <?php echo (isset($participante) && $participante['curso'] == '1ESO') ? 'selected' : ''; ?>>1º ESO</option>
                                            <option value="2ESO" <?php echo (isset($participante) && $participante['curso'] == '2ESO') ? 'selected' : ''; ?>>2º ESO</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="alergias">Alergias o información médica relevante:</label>
                                        <textarea class="form-control" id="alergias" name="alergias" rows="3"><?php echo isset($participante) ? $participante['alergias'] : ''; ?></textarea>
                                        <small class="form-text text-muted">Indique alergias, medicación o cualquier otra información médica relevante.</small>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="responsable_id">Responsable:</label>
                                        <select class="form-control" id="responsable_id" name="responsable_id" required>
                                            <option value="" selected disabled>Seleccione un responsable</option>
                                            <?php foreach($responsables as $resp): ?>
                                                <option value="<?php echo $resp['id']; ?>" 
                                                    <?php echo (isset($participante) && $participante['responsable_id'] == $resp['id']) ? 'selected' : ''; ?>>
                                                    <?php echo $resp['nombre']; ?> (<?php echo $resp['dni']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="form-text text-muted">
                                            Si el responsable no está en la lista, debe <a href="responsables.php?action=add" target="_blank">registrarlo primero</a>.
                                        </small>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" name="guardar_participante" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                    <a href="participantes.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($accion === 'edit' && $participanteId > 0): ?>
                    <!-- Pestañas adicionales para un participante existente -->
                    <div class="mt-4">
                        <ul class="nav nav-tabs" id="participanteTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="periodos-tab" data-toggle="tab" href="#periodos" role="tab">
                                    <i class="fas fa-calendar-alt"></i> Periodos
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="servicios-tab" data-toggle="tab" href="#servicios" role="tab">
                                    <i class="fas fa-concierge-bell"></i> Servicios
                                </a>
                            </li>
                        </ul>
                        
                        <div class="tab-content border-left border-right border-bottom p-4" id="participanteTabsContent">
                            <!-- Tab de Periodos -->
                            <div class="tab-pane fade show active" id="periodos" role="tabpanel">
                                <?php
                                // Obtenemos los periodos del participante
                                $periodos = [];
                                try {
                                    $query = "SELECT * FROM periodos_inscritos WHERE participante_id = :id ORDER BY fecha_inicio";
                                    $periodos = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $participanteId]);
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Error al cargar periodos: ' . $e->getMessage() . '</div>';
                                }
                                ?>
                                
                                <h5>Periodos inscritos</h5>
                                
                                <div class="table-responsive mt-3">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Semana</th>
                                                <th>Fecha inicio</th>
                                                <th>Fecha fin</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($periodos)): ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No hay periodos inscritos.</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($periodos as $periodo): ?>
                                                    <tr>
                                                        <td><?php echo $periodo['semana']; ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($periodo['fecha_inicio'])); ?></td>
                                                        <td><?php echo date('d/m/Y', strtotime($periodo['fecha_fin'])); ?></td>
                                                        <td></td>
                                                            <a href="periodos.php?action=edit&id=<?php echo $periodo['id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3"></div>
                                    <a href="periodos.php?action=add&participante_id=<?php echo $participanteId; ?>" class="btn btn-sm btn-success"></a>
                                        <i class="fas fa-plus"></i> Añadir periodo
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Tab de Servicios -->
                            <div class="tab-pane fade" id="servicios" role="tabpanel">
                                <?php
                                // Obtenemos los servicios contratados por el participante
                                $servicios = null;
                                try {
                                    $query = "SELECT * FROM servicios_contratados WHERE participante_id = :id";
                                    $result = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $participanteId]);
                                    if (!empty($result)) {
                                        $servicios = $result[0];
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">Error al cargar servicios: ' . $e->getMessage() . '</div>';
                                }
                                ?>
                                
                                <h5>Servicios contratados</h5>
                                
                                <?php if ($servicios): ?>
                                    <div class="card mt-3">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4"></div>
                                                    <p><strong>Socio AMPA:</strong> 
                                                        <span class="badge badge-<?php echo $servicios['socio_ampa'] == 'SI' ? 'success' : 'secondary'; ?>"></span>
                                                            <?php echo $servicios['socio_ampa']; ?>
                                                        </span>
                                                    </p>
                                                </div>
                                                <div class="col-md-4"></div>
                                                    <p><strong>Guardería matinal:</strong> 
                                                        <?php if ($servicios['guarderia_matinal']): ?>
                                                            <span class="badge badge-info"><?php echo $servicios['guarderia_matinal']; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">No</span>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="col-md-4">
                                                    <p><strong>Comedor:</strong> 
                                                        <span class="badge badge-<?php echo $servicios['comedor'] == 'SI' ? 'info' : 'secondary'; ?>">
                                                            <?php echo $servicios['comedor']; ?>
                                                        </span>
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="mt-3"></div>
                                                <a href="servicios.php?action=edit&participante_id=<?php echo $participanteId; ?>" class="btn btn-sm btn-primary"></a>
                                                    <i class="fas fa-edit"></i> Modificar servicios
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mt-3"></div>
                                        No hay servicios contratados para este participante.
                                    </div>
                                    <div class="mt-3"></div>
                                        <a href="servicios.php?action=add&participante_id=<?php echo $participanteId; ?>" class="btn btn-sm btn-success"></a>
                                            <i class="fas fa-plus"></i> Añadir servicios
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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
