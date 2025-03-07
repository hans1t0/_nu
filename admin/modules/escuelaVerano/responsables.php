<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Responsables - Escuela de Verano";
$currentSection = "responsables";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';
$accion = isset($_GET['action']) ? $_GET['action'] : 'list';
$responsableId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$responsable = null;

// Procesamiento de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Si se está creando o actualizando un responsable
        if (isset($_POST['guardar_responsable'])) {
            $nombre = $_POST['nombre'];
            $dni = $_POST['dni'];
            $email = $_POST['email'];
            $telefono = $_POST['telefono'];
            $forma_pago = $_POST['forma_pago'];
            $iban = $_POST['iban'] ?? '';
            $observaciones = $_POST['observaciones'] ?? '';
            
            // Si es una actualización
            if ($responsableId > 0) {
                $query = "UPDATE responsables SET 
                            nombre = :nombre, 
                            dni = :dni, 
                            email = :email, 
                            telefono = :telefono, 
                            forma_pago = :forma_pago, 
                            iban = :iban,
                            observaciones = :observaciones 
                          WHERE id = :id";
                          
                DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [
                    ':nombre' => $nombre,
                    ':dni' => $dni,
                    ':email' => $email,
                    ':telefono' => $telefono,
                    ':forma_pago' => $forma_pago,
                    ':iban' => $iban,
                    ':observaciones' => $observaciones,
                    ':id' => $responsableId
                ]);
                
                $mensaje = "Responsable actualizado correctamente.";
                $tipoMensaje = "success";
                
            } else { // Si es un nuevo registro
                // Verificamos si el DNI ya existe
                $checkQuery = "SELECT COUNT(*) AS total FROM responsables WHERE dni = :dni";
                $result = DatabaseConnectors::executeQuery('escuelaVerano', $checkQuery, [':dni' => $dni]);
                
                if ($result[0]['total'] > 0) {
                    $mensaje = "Error: Ya existe un responsable con el DNI $dni.";
                    $tipoMensaje = "warning";
                } else {
                    $query = "INSERT INTO responsables (nombre, dni, email, telefono, forma_pago, iban, observaciones) 
                              VALUES (:nombre, :dni, :email, :telefono, :forma_pago, :iban, :observaciones)";
                              
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [
                        ':nombre' => $nombre,
                        ':dni' => $dni,
                        ':email' => $email,
                        ':telefono' => $telefono,
                        ':forma_pago' => $forma_pago,
                        ':iban' => $iban,
                        ':observaciones' => $observaciones
                    ]);
                    
                    $mensaje = "Responsable registrado correctamente.";
                    $tipoMensaje = "success";
                }
            }
            
            // Redirigir a la lista después de guardar si no hay errores
            if ($tipoMensaje === "success") {
                header("Location: responsables.php?msg=" . urlencode($mensaje) . "&tipo=" . $tipoMensaje);
                exit;
            }
        }
        
        // Si se está eliminando un responsable
        if (isset($_POST['eliminar_responsable'])) {
            $id = $_POST['id'];
            
            // Verificamos si tiene participantes asociados
            $checkQuery = "SELECT COUNT(*) AS total FROM participantes WHERE responsable_id = :id";
            $result = DatabaseConnectors::executeQuery('escuelaVerano', $checkQuery, [':id' => $id]);
            
            if ($result[0]['total'] > 0) {
                $mensaje = "No se puede eliminar el responsable porque tiene participantes asociados.";
                $tipoMensaje = "warning";
            } else {
                $query = "DELETE FROM responsables WHERE id = :id";
                DatabaseConnectors::executeNonQuery('escuelaVerano', $query, [':id' => $id]);
                
                $mensaje = "Responsable eliminado correctamente.";
                $tipoMensaje = "success";
            }
            
            header("Location: responsables.php?msg=" . urlencode($mensaje) . "&tipo=" . $tipoMensaje);
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

// Si estamos editando, cargamos los datos del responsable
if ($accion === 'edit' && $responsableId > 0) {
    try {
        $query = "SELECT * FROM responsables WHERE id = :id";
        $resultado = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $responsableId]);
        
        if (!empty($resultado)) {
            $responsable = $resultado[0];
        } else {
            $mensaje = "Responsable no encontrado.";
            $tipoMensaje = "warning";
            $accion = 'list';
        }
    } catch (Exception $e) {
        $mensaje = "Error al cargar el responsable: " . $e->getMessage();
        $tipoMensaje = "danger";
        $accion = 'list';
    }
}

// Cargamos la lista de responsables si estamos en vista de lista
$responsables = [];
if ($accion === 'list') {
    try {
        $query = "SELECT r.*, (SELECT COUNT(*) FROM participantes WHERE responsable_id = r.id) AS num_participantes 
                  FROM responsables r 
                  ORDER BY r.nombre";
        $responsables = DatabaseConnectors::executeQuery('escuelaVerano', $query);
    } catch (Exception $e) {
        $mensaje = "Error al cargar la lista de responsables: " . $e->getMessage();
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
                            <a class="nav-link active" href="responsables.php">
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
                    <h1>
                        <?php if ($accion === 'add'): ?>
                            Registrar Nuevo Responsable
                        <?php elseif ($accion === 'edit'): ?>
                            Editar Responsable
                        <?php else: ?>
                            Gestión de Responsables
                        <?php endif; ?>
                    </h1>
                    
                    <?php if ($accion === 'list'): ?>
                        <div class="btn-toolbar mb-2 mb-md-0">
                            <a href="?action=add" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus"></i> Nuevo Responsable
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
                                    <th>DNI</th>
                                    <th>Email</th>
                                    <th>Teléfono</th>
                                    <th>Forma de Pago</th>
                                    <th>Participantes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($responsables)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No hay responsables registrados.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($responsables as $r): ?>
                                        <tr>
                                            <td><?php echo $r['id']; ?></td>
                                            <td><?php echo $r['nombre']; ?></td>
                                            <td><?php echo $r['dni']; ?></td>
                                            <td><?php echo $r['email']; ?></td>
                                            <td><?php echo $r['telefono']; ?></td>
                                            <td><?php echo $r['forma_pago']; ?></td>
                                            <td>
                                                <?php if ($r['num_participantes'] > 0): ?>
                                                    <span class="badge badge-info"><?php echo $r['num_participantes']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">0</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        data-toggle="modal" data-target="#deleteModal<?php echo $r['id']; ?>"
                                                        <?php echo ($r['num_participantes'] > 0) ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                
                                                <!-- Modal de confirmación de eliminación -->
                                                <div class="modal fade" id="deleteModal<?php echo $r['id']; ?>" tabindex="-1" role="dialog" 
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
                                                                ¿Está seguro de que desea eliminar al responsable <strong><?php echo $r['nombre']; ?></strong>?
                                                                Esta acción no se puede deshacer.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                                <form action="" method="post">
                                                                    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
                                                                    <button type="submit" name="eliminar_responsable" class="btn btn-danger">Eliminar</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Botón para añadir participante a este responsable -->
                                                <a href="participantes.php?action=add&responsable_id=<?php echo $r['id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="fas fa-child"></i>
                                                </a>
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
                                               value="<?php echo isset($responsable) ? $responsable['nombre'] : ''; ?>" required>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="dni">DNI/NIE:</label>
                                        <input type="text" class="form-control" id="dni" name="dni" 
                                               value="<?php echo isset($responsable) ? $responsable['dni'] : ''; ?>" 
                                               pattern="[0-9]{8}[A-Za-z]{1}|[XYZxyz][0-9]{7}[A-Za-z]{1}" required>
                                        <small class="form-text text-muted">Formato: 12345678A o X1234567A</small>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="email">Correo electrónico:</label>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="<?php echo isset($responsable) ? $responsable['email'] : ''; ?>" required>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="telefono">Teléfono:</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" 
                                               value="<?php echo isset($responsable) ? $responsable['telefono'] : ''; ?>" 
                                               pattern="[0-9]{9}" required>
                                        <small class="form-text text-muted">Formato: 9 dígitos sin espacios</small>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="forma_pago">Forma de pago:</label>
                                        <select class="form-control" id="forma_pago" name="forma_pago" required>
                                            <option value="" selected disabled>Seleccione forma de pago</option>
                                            <option value="TRANSFERENCIA" <?php echo (isset($responsable) && $responsable['forma_pago'] == 'TRANSFERENCIA') ? 'selected' : ''; ?>>Transferencia bancaria</option>
                                            <option value="EFECTIVO" <?php echo (isset($responsable) && $responsable['forma_pago'] == 'EFECTIVO') ? 'selected' : ''; ?>>Efectivo</option>
                                            <option value="DOMICILIACION" <?php echo (isset($responsable) && $responsable['forma_pago'] == 'DOMICILIACION') ? 'selected' : ''; ?>>Domiciliación bancaria</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group col-md-6">
                                        <label for="iban">IBAN (opcional):</label>
                                        <input type="text" class="form-control" id="iban" name="iban" 
                                               value="<?php echo isset($responsable) ? $responsable['iban'] : ''; ?>">
                                        <small class="form-text text-muted">Requerido solo para domiciliación bancaria</small>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label for="observaciones">Observaciones:</label>
                                        <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo isset($responsable) ? $responsable['observaciones'] : ''; ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" name="guardar_responsable" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar
                                    </button>
                                    <a href="responsables.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php if ($accion === 'edit' && $responsableId > 0): ?>
                    <!-- Listado de participantes asociados a este responsable -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Participantes a cargo</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Cargamos los participantes de este responsable
                            $participantes = [];
                            try {
                                $query = "SELECT * FROM participantes WHERE responsable_id = :id ORDER BY nombre";
                                $participantes = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':id' => $responsableId]);
                            } catch (Exception $e) {
                                echo '<div class="alert alert-danger">Error al cargar participantes: ' . $e->getMessage() . '</div>';
                            }
                            ?>
                            
                            <?php if (empty($participantes)): ?>
                                <div class="alert alert-info">
                                    Este responsable no tiene participantes asociados.
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nombre</th>
                                                <th>Fecha Nacimiento</th>
                                                <th>Centro</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($participantes as $p): ?>
                                                <tr>
                                                    <td><?php echo $p['id']; ?></td>
                                                    <td><?php echo $p['nombre']; ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($p['fecha_nacimiento'])); ?></td>
                                                    <td><?php echo $p['centro_actual']; ?></td>
                                                    <td>
                                                        <a href="participantes.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i> Editar
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <a href="participantes.php?action=add&responsable_id=<?php echo $responsableId; ?>" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Añadir participante
                                </a>
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
    
    <!-- Script para mostrar/ocultar campo IBAN según forma de pago -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formaPagoSelect = document.getElementById('forma_pago');
            const ibanField = document.getElementById('iban');
            
            if (formaPagoSelect && ibanField) {
                // Función para actualizar la visibilidad del campo IBAN
                function updateIbanVisibility() {
                    const ibanContainer = ibanField.closest('.form-group');
                    if (formaPagoSelect.value === 'DOMICILIACION') {
                        ibanContainer.classList.add('required');
                        ibanField.setAttribute('required', 'required');
                    } else {
                        ibanContainer.classList.remove('required');
                        ibanField.removeAttribute('required');
                    }
                }
                
                // Ejecutar al cargar y al cambiar la selección
                updateIbanVisibility();
                formaPagoSelect.addEventListener('change', updateIbanVisibility);
            }
        });
    </script>
</body>
</html>
