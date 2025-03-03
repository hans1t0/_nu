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
$id = $nombre = $codigo = $activo = '';
$action = isset($_GET['action']) ? $_GET['action'] : '';
$success_message = $error_message = '';

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
    $activo = isset($_POST['activo']) ? 1 : 0;

    // Validación básica
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre del centro es obligatorio";
    if (empty($codigo)) $errores[] = "El código del centro es obligatorio";

    if (empty($errores)) {
        try {
            if ($action == 'new') {
                // Verificar si ya existe un centro con el mismo código
                $existeCentro = DatabaseConnectors::executeQuery('ludoteca', 
                    "SELECT COUNT(*) as total FROM centros WHERE codigo = ?", 
                    [$codigo]
                );
                
                if ($existeCentro[0]['total'] > 0) {
                    $error_message = "Ya existe un centro con el código '$codigo'";
                } else {
                    // Insertar nuevo centro
                    $query = "INSERT INTO centros (nombre, codigo, activo) VALUES (?, ?, ?)";
                    $params = [$nombre, $codigo, $activo];
                    
                    DatabaseConnectors::executeNonQuery('ludoteca', $query, $params);
                    $success_message = "Centro creado correctamente.";
                    
                    // Redireccionar para evitar reenvío del formulario
                    header("Location: centros.php?success=" . urlencode($success_message));
                    exit();
                }
            } else {
                // Verificar si ya existe otro centro con el mismo código
                $existeCentro = DatabaseConnectors::executeQuery('ludoteca', 
                    "SELECT COUNT(*) as total FROM centros WHERE codigo = ? AND id != ?", 
                    [$codigo, $id]
                );
                
                if ($existeCentro[0]['total'] > 0) {
                    $error_message = "Ya existe otro centro con el código '$codigo'";
                } else {
                    // Actualizar centro existente
                    $query = "UPDATE centros SET nombre = ?, codigo = ?, activo = ? WHERE id = ?";
                    $params = [$nombre, $codigo, $activo, $id];
                    
                    DatabaseConnectors::executeNonQuery('ludoteca', $query, $params);
                    $success_message = "Centro actualizado correctamente.";
                    
                    // Redireccionar para evitar reenvío del formulario
                    header("Location: centros.php?success=" . urlencode($success_message));
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
        $centroId = $_GET['id'];
        
        // Verificar si hay alumnos asociados a este centro
        $alumnosAsociados = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT COUNT(*) as total FROM alumnos WHERE centro_id = ?", 
            [$centroId]
        );
        
        if ($alumnosAsociados[0]['total'] > 0) {
            // Tiene alumnos asociados, solo desactivar
            DatabaseConnectors::executeNonQuery('ludoteca', 
                "UPDATE centros SET activo = 0 WHERE id = ?", 
                [$centroId]
            );
            $success_message = "Centro desactivado correctamente. No se puede eliminar porque tiene alumnos asociados.";
        } else {
            // No tiene alumnos asociados, eliminar
            DatabaseConnectors::executeNonQuery('ludoteca', 
                "DELETE FROM centros WHERE id = ?", 
                [$centroId]
            );
            $success_message = "Centro eliminado correctamente.";
        }
        
        // Redireccionar
        header("Location: centros.php?success=" . urlencode($success_message));
        exit();
    } catch (Exception $e) {
        $error_message = "Error al eliminar el centro: " . $e->getMessage();
        error_log($error_message);
    }
}

// Cargar datos para edición
if ($action == 'edit' && isset($_GET['id'])) {
    try {
        // Obtener datos del centro
        $centroId = $_GET['id'];
        $centroData = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT * FROM centros WHERE id = ?", 
            [$centroId]
        );
        
        if (!empty($centroData)) {
            $id = $centroData[0]['id'];
            $nombre = $centroData[0]['nombre'];
            $codigo = $centroData[0]['codigo'];
            $activo = $centroData[0]['activo'];
        } else {
            $error_message = "No se encontró el centro solicitado.";
        }
    } catch (Exception $e) {
        $error_message = "Error al cargar los datos del centro: " . $e->getMessage();
        error_log($error_message);
    }
}

// Consultar lista de centros para mostrar
$centros = [];
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$soloActivos = isset($_GET['activos']) && $_GET['activos'] == '1';

if ($dbConnected) {
    try {
        $query = "SELECT c.*, 
                (SELECT COUNT(*) FROM alumnos a WHERE a.centro_id = c.id) AS num_alumnos 
                FROM centros c 
                WHERE (c.nombre LIKE ? OR c.codigo LIKE ?)";
                
        if ($soloActivos) {
            $query .= " AND c.activo = 1";
        }
        
        $query .= " ORDER BY c.nombre";
        
        $params = ["%$filtro%", "%$filtro%"];
        $centros = DatabaseConnectors::executeQuery('ludoteca', $query, $params);
    } catch (Exception $e) {
        $error_message = "Error al cargar la lista de centros: " . $e->getMessage();
        error_log($error_message);
    }
}

// Configurar título y migas de pan
$pageTitle = $action == 'new' ? "Nuevo Centro" : ($action == 'edit' ? "Editar Centro" : "Centros Educativos");
$showBreadcrumb = true;
$breadcrumbItems = [
    ['text' => 'Ludoteca', 'url' => 'index.php', 'active' => false],
    ['text' => 'Centros', 'url' => 'centros.php', 'active' => $action == ''],
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
                        <i class="bi bi-building-add text-primary"></i> Nuevo Centro
                    <?php elseif ($action == 'edit'): ?>
                        <i class="bi bi-pencil-square text-primary"></i> Editar Centro
                    <?php else: ?>
                        <i class="bi bi-buildings text-primary"></i> Centros Educativos
                    <?php endif; ?>
                </h1>
                <div>
                    <?php if ($action == 'new' || $action == 'edit'): ?>
                        <a href="centros.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Volver al listado
                        </a>
                    <?php else: ?>
                        <a href="index.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Volver al panel
                        </a>
                        <a href="centros.php?action=new" class="btn btn-primary">
                            <i class="bi bi-building-add"></i> Nuevo Centro
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
            <!-- Formulario para crear/editar centro -->
            <div class="card">
                <div class="card-body">
                    <form method="post" action="centros.php?action=<?= $action ?><?= $action == 'edit' ? "&id=$id" : "" ?>">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label for="nombre" class="form-label">Nombre del Centro <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="codigo" name="codigo" value="<?= htmlspecialchars($codigo) ?>" required>
                                <small class="form-text text-muted">Código abreviado para identificar el centro (ej: ALM, CBL)</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="activo">
                                        Centro activo
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <a href="centros.php" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $action == 'new' ? 'Crear Centro' : 'Guardar Cambios' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <!-- Listado de centros -->
            <div class="card">
                <div class="card-body">
                    <form method="get" action="centros.php" class="row mb-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Buscar por nombre o código..." name="filtro" value="<?= htmlspecialchars($filtro) ?>">
                                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch d-flex align-items-center justify-content-end">
                                <input class="form-check-input me-2" type="checkbox" id="activos" name="activos" value="1" <?= $soloActivos ? 'checked' : '' ?> onchange="this.form.submit()">
                                <label class="form-check-label" for="activos">Mostrar solo centros activos</label>
                            </div>
                        </div>
                    </form>
                    
                    <?php if (empty($centros)): ?>
                        <div class="alert alert-info">
                            No se encontraron centros<?= $filtro ? " con el filtro '$filtro'" : "" ?>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th class="text-center">Alumnos</th>
                                        <th>Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($centros as $centro): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($centro['nombre']) ?></td>
                                            <td><?= htmlspecialchars($centro['codigo']) ?></td>
                                            <td class="text-center"><?= $centro['num_alumnos'] ?></td>
                                            <td>
                                                <?php if ($centro['activo']): ?>
                                                    <span class="badge bg-success">Activo</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Inactivo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="centros.php?action=edit&id=<?= $centro['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmDelete(<?= $centro['id'] ?>, '<?= htmlspecialchars(addslashes($centro['nombre'])) ?>')" 
                                                            <?= $centro['num_alumnos'] > 0 ? 'data-bs-toggle="tooltip" data-bs-placement="top" title="Tiene alumnos asignados, solo se desactivará"' : '' ?>
                                                            title="Eliminar">
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
                            <p>Total: <?= count($centros) ?> centro(s)</p>
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
                ¿Está seguro de que desea eliminar el centro <span id="deleteCenterName"></span>?
                <p class="small text-muted mt-2">Nota: Si el centro tiene alumnos asociados, solo se desactivará.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <a href="#" id="confirmDelete" class="btn btn-danger">Eliminar</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Función para confirmar eliminación
        window.confirmDelete = function(id, nombre) {
            document.getElementById('deleteCenterName').textContent = nombre;
            document.getElementById('confirmDelete').href = 'centros.php?action=delete&id=' + id;
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        };
    });
</script>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>
