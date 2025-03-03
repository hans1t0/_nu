<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../../admin/database/DatabaseConnectors.php';

// Inicializar variables
$error_message = null;
$success_message = null;
$centros = [];
$centro = [
    'id' => '',
    'nombre' => '',
    'codigo' => '',
    'tiene_desayuno' => 0
];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Intentar establecer la conexión usando DatabaseConnectors
try {
    // Obtener la conexión 'matinera'
    $conn = DatabaseConnectors::getConnection('matinera');
    
    // Procesar acciones CRUD
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    // Validar datos
                    if (empty($_POST['nombre']) || empty($_POST['codigo'])) {
                        $error_message = "El nombre y código del centro son obligatorios";
                    } else {
                        try {
                            // Comprobar si ya existe un centro con ese código
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM colegios WHERE codigo = :codigo");
                            $stmt->execute([':codigo' => $_POST['codigo']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error_message = "Ya existe un centro con ese código";
                            } else {
                                $stmt = $conn->prepare(
                                    "INSERT INTO colegios (nombre, codigo, tiene_desayuno) 
                                    VALUES (:nombre, :codigo, :tiene_desayuno)"
                                );
                                $stmt->execute([
                                    ':nombre' => $_POST['nombre'],
                                    ':codigo' => $_POST['codigo'],
                                    ':tiene_desayuno' => isset($_POST['tiene_desayuno']) ? 1 : 0
                                ]);
                                $success_message = "Centro educativo añadido correctamente";
                                $action = 'list';
                            }
                        } catch (PDOException $e) {
                            $error_message = "Error al crear el centro: " . $e->getMessage();
                        }
                    }
                    break;
                    
                case 'update':
                    // Validar datos
                    if (empty($_POST['nombre']) || empty($_POST['codigo']) || empty($_POST['id'])) {
                        $error_message = "Todos los campos son obligatorios";
                    } else {
                        try {
                            // Comprobar si ya existe un centro con ese código y no es el mismo
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM colegios WHERE codigo = :codigo AND id != :id");
                            $stmt->execute([':codigo' => $_POST['codigo'], ':id' => $_POST['id']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error_message = "Ya existe un centro con ese código";
                            } else {
                                $stmt = $conn->prepare(
                                    "UPDATE colegios SET nombre = :nombre, codigo = :codigo, tiene_desayuno = :tiene_desayuno 
                                    WHERE id = :id"
                                );
                                $stmt->execute([
                                    ':nombre' => $_POST['nombre'],
                                    ':codigo' => $_POST['codigo'],
                                    ':tiene_desayuno' => isset($_POST['tiene_desayuno']) ? 1 : 0,
                                    ':id' => $_POST['id']
                                ]);
                                $success_message = "Centro educativo actualizado correctamente";
                                $action = 'list';
                            }
                        } catch (PDOException $e) {
                            $error_message = "Error al actualizar el centro: " . $e->getMessage();
                        }
                    }
                    break;
                    
                case 'delete':
                    if (!empty($_POST['id'])) {
                        try {
                            // Comprobar si hay alumnos asociados al centro
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM hijos WHERE colegio_id = :id");
                            $stmt->execute([':id' => $_POST['id']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error_message = "No se puede eliminar el centro porque tiene alumnos asociados";
                            } else {
                                $stmt = $conn->prepare("DELETE FROM colegios WHERE id = :id");
                                $stmt->execute([':id' => $_POST['id']]);
                                $success_message = "Centro educativo eliminado correctamente";
                            }
                        } catch (PDOException $e) {
                            $error_message = "Error al eliminar el centro: " . $e->getMessage();
                        }
                    }
                    break;
            }
        }
    }
    
    // Cargar datos según la acción
    switch ($action) {
        case 'edit':
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("SELECT * FROM colegios WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $centro = $row;
                } else {
                    $error_message = "Centro no encontrado";
                    $action = 'list';
                }
            }
            break;
            
        case 'new':
            // Inicializar centro vacío ya está hecho arriba
            break;
            
        default: // list
            $stmt = $conn->prepare("SELECT c.*, 
                                  (SELECT COUNT(*) FROM hijos WHERE colegio_id = c.id) as num_alumnos 
                                  FROM colegios c ORDER BY nombre");
            $stmt->execute();
            $centros = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
    
} catch (Exception $e) {
    $error_message = "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Centros - Matinera</title>
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
        .badge-desayuno {
            background-color: #28a745;
            color: white;
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            font-size: 75%;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <h1 class="mb-4">
            <?php if ($action === 'new'): ?>
                Nuevo Centro Educativo
            <?php elseif ($action === 'edit'): ?>
                Editar Centro Educativo
            <?php else: ?>
                Gestión de Centros Educativos
            <?php endif; ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Listado de Centros</h5>
                    <a href="?action=new" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Centro
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($centros)): ?>
                        <div class="alert alert-info">
                            No hay centros educativos registrados. 
                            <a href="?action=new" class="alert-link">Crear nuevo centro</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Código</th>
                                        <th>Desayuno</th>
                                        <th>Alumnos</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($centros as $centro): ?>
                                        <tr>
                                            <td><?php echo $centro['id']; ?></td>
                                            <td><?php echo htmlspecialchars($centro['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($centro['codigo']); ?></td>
                                            <td>
                                                <?php if ($centro['tiene_desayuno']): ?>
                                                    <span class="badge-desayuno">
                                                        <i class="fas fa-check"></i> Sí
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $centro['num_alumnos']; ?></td>
                                            <td>
                                                <a href="?action=edit&id=<?php echo $centro['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <?php if ($centro['num_alumnos'] == 0): ?>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete(<?php echo $centro['id']; ?>, '<?php echo htmlspecialchars($centro['nombre']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-sm btn-danger" disabled title="Este centro tiene alumnos asociados">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        <?php elseif ($action === 'new' || $action === 'edit'): ?>
            <div class="card">
                <div class="card-header">
                    <h5><?php echo ($action === 'new') ? 'Nuevo Centro' : 'Editar Centro'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="post" action="centros.php">
                        <input type="hidden" name="action" value="<?php echo ($action === 'new') ? 'create' : 'update'; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $centro['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del Centro *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($centro['nombre']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código *</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" 
                                   value="<?php echo htmlspecialchars($centro['codigo']); ?>" 
                                   required maxlength="10">
                            <div class="form-text">Código único para identificar el centro (máximo 10 caracteres)</div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="tiene_desayuno" name="tiene_desayuno" 
                                   <?php echo $centro['tiene_desayuno'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="tiene_desayuno">Ofrece servicio de desayuno</label>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="centros.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
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
                    ¿Está seguro de que desea eliminar el centro <strong id="deleteModalCentro"></strong>?
                </div>
                <div class="modal-footer">
                    <form method="post" action="centros.php">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteModalId">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Matinera</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para confirmar eliminación
        function confirmDelete(id, nombre) {
            document.getElementById('deleteModalCentro').textContent = nombre;
            document.getElementById('deleteModalId').value = id;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
