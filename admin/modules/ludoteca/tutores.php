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
$id = $nombre = $dni = $email = $telefono = $telefono2 = $iban = $forma_pago = $metodo_pago = $observaciones = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = $error_message = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $dni = isset($_POST['dni']) ? trim($_POST['dni']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $telefono2 = isset($_POST['telefono2']) ? trim($_POST['telefono2']) : '';
    $iban = isset($_POST['iban']) ? trim($_POST['iban']) : '';
    $forma_pago = isset($_POST['forma_pago']) ? $_POST['forma_pago'] : '';
    $metodo_pago = isset($_POST['metodo_pago']) ? trim($_POST['metodo_pago']) : '';
    $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

    // Validación básica
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio";
    if (empty($dni)) $errores[] = "El DNI/NIE es obligatorio";
    if (empty($telefono)) $errores[] = "El teléfono es obligatorio";
    if (empty($email)) $errores[] = "El email es obligatorio";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "El email no tiene un formato válido";
    if (empty($forma_pago)) $errores[] = "La forma de pago es obligatoria";
    
    // Validación de IBAN para domiciliación
    if ($forma_pago == 'domiciliacion' && empty($iban)) {
        $errores[] = "El IBAN es obligatorio para domiciliación bancaria";
    }

    if (empty($errores)) {
        try {
            if ($action == 'new') {
                // Verificar si ya existe un tutor con el mismo DNI
                $existeTutor = DatabaseConnectors::executeQuery('ludoteca', 
                    "SELECT COUNT(*) as total FROM tutores WHERE dni = ?", 
                    [$dni]
                );
                
                if ($existeTutor[0]['total'] > 0) {
                    $error_message = "Ya existe un tutor con el DNI/NIE '$dni'";
                } else {
                    // Insertar nuevo tutor
                    $query = "INSERT INTO tutores (nombre, dni, email, telefono, telefono2, iban, forma_pago, metodo_pago, observaciones) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $params = [$nombre, $dni, $email, $telefono, $telefono2, $iban, $forma_pago, $metodo_pago, $observaciones];
                    
                    DatabaseConnectors::executeNonQuery('ludoteca', $query, $params);
                    $success_message = "Tutor creado correctamente.";
                    
                    // Redireccionar para evitar reenvío del formulario
                    header("Location: tutores.php?success=" . urlencode($success_message));
                    exit();
                }
            } else {
                // Verificar si ya existe otro tutor con el mismo DNI
                $existeTutor = DatabaseConnectors::executeQuery('ludoteca', 
                    "SELECT COUNT(*) as total FROM tutores WHERE dni = ? AND id != ?", 
                    [$dni, $id]
                );
                
                if ($existeTutor[0]['total'] > 0) {
                    $error_message = "Ya existe otro tutor con el DNI/NIE '$dni'";
                } else {
                    // Actualizar tutor existente
                    $query = "UPDATE tutores SET nombre = ?, dni = ?, email = ?, telefono = ?, telefono2 = ?, iban = ?, forma_pago = ?, metodo_pago = ?, observaciones = ? WHERE id = ?";
                    $params = [$nombre, $dni, $email, $telefono, $telefono2, $iban, $forma_pago, $metodo_pago, $observaciones, $id];
                    
                    DatabaseConnectors::executeNonQuery('ludoteca', $query, $params);
                    $success_message = "Tutor actualizado correctamente.";
                    
                    // Redireccionar para evitar reenvío del formulario
                    header("Location: tutores.php?success=" . urlencode($success_message));
                    exit();
                }
            }
        } catch (Exception $e) {
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
        $tutorId = $_GET['id'];
        
        // Verificar si hay alumnos asociados a este tutor
        $alumnosAsociados = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT COUNT(*) as total FROM alumno_tutor WHERE tutor_id = ?", 
            [$tutorId]
        );
        
        if ($alumnosAsociados[0]['total'] > 0) {
            $error_message = "No se puede eliminar el tutor porque tiene alumnos asociados. Por favor, elimine primero las relaciones con los alumnos.";
        } else {
            // No tiene alumnos asociados, se puede eliminar
            DatabaseConnectors::executeNonQuery('ludoteca', 
                "DELETE FROM tutores WHERE id = ?", 
                [$tutorId]
            );
            $success_message = "Tutor eliminado correctamente.";
        }
        
        // Redireccionar
        header("Location: tutores.php?success=" . urlencode($success_message));
        exit();
    } catch (Exception $e) {
        $error_message = "Error al eliminar el tutor: " . $e->getMessage();
        error_log($error_message);
    }
}

// Cargar datos para edición
if ($action == 'edit' && isset($_GET['id'])) {
    try {
        // Obtener datos del tutor
        $tutorId = $_GET['id'];
        $tutorData = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT * FROM tutores WHERE id = ?", 
            [$tutorId]
        );
        
        if (!empty($tutorData)) {
            $id = $tutorData[0]['id'];
            $nombre = $tutorData[0]['nombre'];
            $dni = $tutorData[0]['dni'];
            $email = $tutorData[0]['email'];
            $telefono = $tutorData[0]['telefono'];
            $telefono2 = $tutorData[0]['telefono2'];
            $iban = $tutorData[0]['iban'];
            $forma_pago = $tutorData[0]['forma_pago'];
            $metodo_pago = $tutorData[0]['metodo_pago'];
            $observaciones = $tutorData[0]['observaciones'];
        } else {
            $error_message = "No se encontró el tutor solicitado.";
        }
    } catch (Exception $e) {
        $error_message = "Error al cargar los datos del tutor: " . $e->getMessage();
        error_log($error_message);
    }
}

// Consultar lista de tutores para mostrar
$tutores = [];
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

if ($dbConnected) {
    try {
        $query = "SELECT t.*, 
                (SELECT COUNT(*) FROM alumno_tutor at WHERE at.tutor_id = t.id) AS num_alumnos 
                FROM tutores t 
                WHERE (t.nombre LIKE ? OR t.dni LIKE ? OR t.email LIKE ? OR t.telefono LIKE ?)
                ORDER BY t.nombre";
        
        $params = ["%$filtro%", "%$filtro%", "%$filtro%", "%$filtro%"];
        $tutores = DatabaseConnectors::executeQuery('ludoteca', $query, $params);
    } catch (Exception $e) {
        $error_message = "Error al cargar la lista de tutores: " . $e->getMessage();
        error_log($error_message);
    }
}

// Configurar título y migas de pan
$pageTitle = $action == 'new' ? "Nuevo Tutor" : ($action == 'edit' ? "Editar Tutor" : "Tutores");
$showBreadcrumb = true;
$breadcrumbItems = [
    ['text' => 'Ludoteca', 'url' => 'index.php', 'active' => false],
    ['text' => 'Tutores', 'url' => 'tutores.php', 'active' => $action == ''],
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
                        <i class="bi bi-person-add text-primary"></i> Nuevo Tutor
                    <?php elseif ($action == 'edit'): ?>
                        <i class="bi bi-pencil-square text-primary"></i> Editar Tutor
                    <?php else: ?>
                        <i class="bi bi-person-badge text-primary"></i> Tutores
                    <?php endif; ?>
                </h1>
                <div>
                    <?php if ($action == 'new' || $action == 'edit'): ?>
                        <a href="tutores.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al listado
                        </a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Volver al panel
                        </a>
                        <a href="tutores.php?action=new" class="btn btn-primary">
                            <i class="bi bi-person-add"></i> Nuevo Tutor
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
            <!-- Formulario para crear/editar tutor -->
            <div class="card">
                <div class="card-body">
                    <form method="post" action="tutores.php?action=<?= $action ?><?= $action == 'edit' ? "&id=$id" : "" ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">Nombre completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dni" class="form-label">DNI/NIE <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dni" name="dni" value="<?= htmlspecialchars($dni) ?>" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label for="telefono" class="form-label">Teléfono principal <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($telefono) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label for="telefono2" class="form-label">Teléfono alternativo</label>
                                <input type="tel" class="form-control" id="telefono2" name="telefono2" value="<?= htmlspecialchars($telefono2) ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="forma_pago" class="form-label">Forma de pago <span class="text-danger">*</span></label>
                                <select class="form-select" id="forma_pago" name="forma_pago" required onchange="mostrarIban()">
                                    <option value="">-- Seleccionar forma de pago --</option>
                                    <option value="domiciliacion" <?= $forma_pago == 'domiciliacion' ? 'selected' : '' ?>>Domiciliación bancaria</option>
                                    <option value="transferencia" <?= $forma_pago == 'transferencia' ? 'selected' : '' ?>>Transferencia</option>
                                    <option value="coordinador" <?= $forma_pago == 'coordinador' ? 'selected' : '' ?>>Pago a coordinador</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="iban_container" style="<?= $forma_pago != 'domiciliacion' ? 'display:none;' : '' ?>">
                                <label for="iban" class="form-label">IBAN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="iban" name="iban" value="<?= htmlspecialchars($iban) ?>" <?= $forma_pago == 'domiciliacion' ? 'required' : '' ?>>
                            </div>
                            <div class="col-md-4">
                                <label for="metodo_pago" class="form-label">Método de pago</label>
                                <input type="text" class="form-control" id="metodo_pago" name="metodo_pago" value="<?= htmlspecialchars($metodo_pago) ?>" placeholder="Ej: Efectivo, Bizum, etc.">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= htmlspecialchars($observaciones) ?></textarea>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="tutores.php" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $action == 'new' ? 'Crear Tutor' : 'Guardar Cambios' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Listado de tutores -->
            <div class="card">
                <div class="card-body">
                    <form method="get" action="tutores.php" class="mb-4">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Buscar por nombre, DNI, email o teléfono..." name="filtro" value="<?= htmlspecialchars($filtro) ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                        </div>
                    </form>
                    
                    <?php if (empty($tutores)): ?>
                        <div class="alert alert-info">
                            No se encontraron tutores<?= $filtro ? " con el filtro '$filtro'" : "" ?>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>DNI/NIE</th>
                                        <th>Contacto</th>
                                        <th>Forma de pago</th>
                                        <th class="text-center">Alumnos</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tutores as $tutor): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tutor['nombre']) ?></td>
                                            <td><?= htmlspecialchars($tutor['dni']) ?></td>
                                            <td>
                                                <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($tutor['telefono']) ?></div>
                                                <div><i class="bi bi-envelope"></i> <?= htmlspecialchars($tutor['email']) ?></div>
                                            </td>
                                            <td>
                                                <?php
                                                switch($tutor['forma_pago']) {
                                                    case 'domiciliacion':
                                                        echo '<span class="badge bg-info text-dark">Domiciliación</span>';
                                                        break;
                                                    case 'transferencia':
                                                        echo '<span class="badge bg-success">Transferencia</span>';
                                                        break;
                                                    case 'coordinador':
                                                        echo '<span class="badge bg-warning text-dark">Coordinador</span>';
                                                        break;
                                                    default:
                                                        echo '<span class="badge bg-secondary">No especificada</span>';
                                                }
                                                ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="alumnos.php?tutor_id=<?= $tutor['id'] ?>" class="btn btn-sm <?= $tutor['num_alumnos'] > 0 ? 'btn-outline-primary' : 'btn-outline-secondary' ?>">
                                                    <?= $tutor['num_alumnos'] ?>
                                                    <i class="bi bi-people"></i>
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="tutores.php?action=edit&id=<?= $tutor['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?= $tutor['id'] ?>, '<?= htmlspecialchars(addslashes($tutor['nombre'])) ?>', <?= $tutor['num_alumnos'] ?>)" 
                                                            title="Eliminar"
                                                            <?= $tutor['num_alumnos'] > 0 ? 'disabled' : '' ?>>
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <p>Total: <?= count($tutores) ?> tutor(es)</p>
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
                ¿Está seguro de que desea eliminar al tutor <span id="deleteTutorName"></span>?
                <div id="deleteWarning" class="alert alert-warning mt-3" style="display:none;">
                    <i class="bi bi-exclamation-triangle"></i> No se puede eliminar este tutor porque tiene alumnos asociados.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para mostrar/ocultar el campo IBAN según la forma de pago
    function mostrarIban() {
        const formaPago = document.getElementById('forma_pago').value;
        const ibanContainer = document.getElementById('iban_container');
        const ibanInput = document.getElementById('iban');
        
        if (formaPago === 'domiciliacion') {
            ibanContainer.style.display = 'block';
            ibanInput.setAttribute('required', 'required');
        } else {
            ibanContainer.style.display = 'none';
            ibanInput.removeAttribute('required');
        }
    }

    // Función para confirmar eliminación
    function confirmDelete(id, nombre, numAlumnos) {
        document.getElementById('deleteTutorName').textContent = nombre;
        
        const deleteWarning = document.getElementById('deleteWarning');
        const confirmButton = document.getElementById('confirmDelete');
        
        if (numAlumnos > 0) {
            deleteWarning.style.display = 'block';
            confirmButton.style.display = 'none';
        } else {
            deleteWarning.style.display = 'none';
            confirmButton.style.display = 'inline-block';
            confirmButton.href = 'tutores.php?action=delete&id=' + id;
        }
        
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
    
    // Inicializar eventos cuando el DOM esté cargado
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar/ocultar IBAN al cargar la página
        mostrarIban();
    });
</script>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>
