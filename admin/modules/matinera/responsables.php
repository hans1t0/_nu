<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../../admin/database/DatabaseConnectors.php';

// Inicializar variables
$error_message = null;
$success_message = null;
$responsables = [];
$responsable = [
    'id' => '',
    'nombre' => '',
    'dni' => '',
    'email' => '',
    'telefono' => '',
    'observaciones' => '',
    'forma_pago' => 'TRANSFERENCIA',
    'iban' => ''
];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$formas_pago = ['DOMICILIACION', 'TRANSFERENCIA', 'COORDINADOR'];

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
                    if (empty($_POST['nombre']) || empty($_POST['dni']) || empty($_POST['telefono'])) {
                        $error_message = "Los campos Nombre, DNI y Teléfono son obligatorios";
                    } else {
                        try {
                            // Comprobar si ya existe un responsable con ese DNI
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM responsables WHERE dni = :dni");
                            $stmt->execute([':dni' => $_POST['dni']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error_message = "Ya existe un responsable con ese DNI";
                            } else {
                                $stmt = $conn->prepare(
                                    "INSERT INTO responsables (nombre, dni, email, telefono, observaciones, forma_pago, iban) 
                                    VALUES (:nombre, :dni, :email, :telefono, :observaciones, :forma_pago, :iban)"
                                );
                                $stmt->execute([
                                    ':nombre' => $_POST['nombre'],
                                    ':dni' => $_POST['dni'],
                                    ':email' => $_POST['email'] ?? '',
                                    ':telefono' => $_POST['telefono'],
                                    ':observaciones' => $_POST['observaciones'] ?? '',
                                    ':forma_pago' => $_POST['forma_pago'],
                                    ':iban' => $_POST['iban'] ?? null
                                ]);
                                $success_message = "Responsable añadido correctamente";
                                $action = 'list';
                            }
                        } catch (PDOException $e) {
                            $error_message = "Error al crear el responsable: " . $e->getMessage();
                        }
                    }
                    break;
                    
                case 'update':
                    // Validar datos
                    if (empty($_POST['nombre']) || empty($_POST['dni']) || empty($_POST['telefono']) || empty($_POST['id'])) {
                        $error_message = "Los campos Nombre, DNI y Teléfono son obligatorios";
                    } else {
                        try {
                            // Comprobar si ya existe un responsable con ese DNI y no es el mismo
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM responsables WHERE dni = :dni AND id != :id");
                            $stmt->execute([':dni' => $_POST['dni'], ':id' => $_POST['id']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error_message = "Ya existe un responsable con ese DNI";
                            } else {
                                $stmt = $conn->prepare(
                                    "UPDATE responsables SET nombre = :nombre, dni = :dni, email = :email, 
                                    telefono = :telefono, observaciones = :observaciones, forma_pago = :forma_pago,
                                    iban = :iban WHERE id = :id"
                                );
                                $stmt->execute([
                                    ':nombre' => $_POST['nombre'],
                                    ':dni' => $_POST['dni'],
                                    ':email' => $_POST['email'] ?? '',
                                    ':telefono' => $_POST['telefono'],
                                    ':observaciones' => $_POST['observaciones'] ?? '',
                                    ':forma_pago' => $_POST['forma_pago'],
                                    ':iban' => $_POST['iban'] ?? null,
                                    ':id' => $_POST['id']
                                ]);
                                $success_message = "Responsable actualizado correctamente";
                                $action = 'list';
                            }
                        } catch (PDOException $e) {
                            $error_message = "Error al actualizar el responsable: " . $e->getMessage();
                        }
                    }
                    break;
                    
                case 'delete':
                    if (!empty($_POST['id'])) {
                        try {
                            // Comprobar si hay alumnos asociados al responsable
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM hijos WHERE responsable_id = :id");
                            $stmt->execute([':id' => $_POST['id']]);
                            if ($stmt->fetchColumn() > 0) {
                                $error_message = "No se puede eliminar el responsable porque tiene alumnos asociados";
                            } else {
                                $stmt = $conn->prepare("DELETE FROM responsables WHERE id = :id");
                                $stmt->execute([':id' => $_POST['id']]);
                                $success_message = "Responsable eliminado correctamente";
                            }
                        } catch (PDOException $e) {
                            $error_message = "Error al eliminar el responsable: " . $e->getMessage();
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
                $stmt = $conn->prepare("SELECT * FROM responsables WHERE id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $responsable = $row;
                } else {
                    $error_message = "Responsable no encontrado";
                    $action = 'list';
                }
            }
            break;
            
        case 'new':
            // Inicializar responsable vacío ya está hecho arriba
            break;
            
        default: // list
            $stmt = $conn->prepare("SELECT r.*, 
                                 (SELECT COUNT(*) FROM hijos WHERE responsable_id = r.id) as num_hijos
                                 FROM responsables r ORDER BY nombre");
            $stmt->execute();
            $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Gestión de Responsables - Matinera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            padding-top: 70px;
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
        .required-field::after {
            content: " *";
            color: red;
        }
        .responsable-row:hover {
            background-color: #f8f9fa;
        }
        .text-truncate-container {
            max-width: 200px;
        }
        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .badge-pago {
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            font-size: 75%;
            font-weight: bold;
        }
        .badge-domiciliacion {
            background-color: #28a745;
            color: white;
        }
        .badge-transferencia {
            background-color: #007bff;
            color: white;
        }
        .badge-coordinador {
            background-color: #ffc107;
            color: #212529;
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
                Nuevo Responsable
            <?php elseif ($action === 'edit'): ?>
                Editar Responsable
            <?php else: ?>
                Gestión de Responsables
            <?php endif; ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Listado de Responsables</h5>
                    <div>
                        <a href="?action=new" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Responsable
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($responsables)): ?>
                        <div class="alert alert-info">
                            No hay responsables registrados. 
                            <a href="?action=new" class="alert-link">Crear nuevo responsable</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>DNI</th>
                                        <th>Contacto</th>
                                        <th>Forma de Pago</th>
                                        <th>Alumnos</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($responsables as $resp): ?>
                                        <tr class="responsable-row">
                                            <td><?php echo htmlspecialchars($resp['nombre']); ?></td>
                                            <td><?php echo htmlspecialchars($resp['dni']); ?></td>
                                            <td>
                                                <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($resp['telefono']); ?></div>
                                                <?php if (!empty($resp['email'])): ?>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($resp['email']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                    $badge_class = '';
                                                    switch ($resp['forma_pago']) {
                                                        case 'DOMICILIACION':
                                                            $badge_class = 'badge-domiciliacion';
                                                            break;
                                                        case 'TRANSFERENCIA':
                                                            $badge_class = 'badge-transferencia';
                                                            break;
                                                        case 'COORDINADOR':
                                                            $badge_class = 'badge-coordinador';
                                                            break;
                                                    }
                                                ?>
                                                <span class="badge-pago <?php echo $badge_class; ?>">
                                                    <?php echo $resp['forma_pago']; ?>
                                                </span>
                                                <?php if ($resp['forma_pago'] === 'DOMICILIACION' && !empty($resp['iban'])): ?>
                                                    <div class="small text-muted mt-1">
                                                        <?php echo substr($resp['iban'], 0, 4) . '...' . substr($resp['iban'], -4); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $resp['num_hijos']; ?>
                                                <?php if ($resp['num_hijos'] > 0): ?>
                                                    <a href="alumnos.php?responsable=<?php echo $resp['id']; ?>" class="btn btn-sm btn-outline-secondary ms-2">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-truncate-container">
                                                    <?php if (!empty($resp['observaciones'])): ?>
                                                        <span class="text-truncate-2" data-bs-toggle="tooltip" title="<?php echo htmlspecialchars($resp['observaciones']); ?>">
                                                            <?php echo htmlspecialchars($resp['observaciones']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?action=edit&id=<?php echo $resp['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($resp['num_hijos'] == 0): ?>
                                                        <button type="button" class="btn btn-sm btn-danger" 
                                                                onclick="confirmDelete(<?php echo $resp['id']; ?>, '<?php echo htmlspecialchars($resp['nombre']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-sm btn-danger" disabled 
                                                                title="No se puede eliminar: tiene alumnos asociados">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
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
                    <h5><?php echo ($action === 'new') ? 'Nuevo Responsable' : 'Editar Responsable'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="post" action="responsables.php">
                        <input type="hidden" name="action" value="<?php echo ($action === 'new') ? 'create' : 'update'; ?>">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $responsable['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label required-field">Nombre completo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($responsable['nombre']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dni" class="form-label required-field">DNI/NIE/Pasaporte</label>
                                <input type="text" class="form-control" id="dni" name="dni" 
                                       value="<?php echo htmlspecialchars($responsable['dni']); ?>" 
                                       required maxlength="9" pattern="[0-9a-zA-Z]{1,9}">
                                <div class="form-text">Formato: sin espacios ni guiones (máximo 9 caracteres)</div>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($responsable['email']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="telefono" class="form-label required-field">Teléfono de contacto</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" 
                                       value="<?php echo htmlspecialchars($responsable['telefono']); ?>" 
                                       required maxlength="15">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="forma_pago" class="form-label required-field">Forma de pago</label>
                                <select class="form-select" id="forma_pago" name="forma_pago" required onchange="toggleIban()">
                                    <?php foreach ($formas_pago as $forma): ?>
                                        <option value="<?php echo $forma; ?>" 
                                            <?php echo ($responsable['forma_pago'] == $forma) ? 'selected' : ''; ?>>
                                            <?php echo $forma; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6" id="iban_container">
                                <label for="iban" class="form-label">IBAN (Para domiciliación)</label>
                                <input type="text" class="form-control" id="iban" name="iban" 
                                       value="<?php echo htmlspecialchars($responsable['iban'] ?? ''); ?>" 
                                       maxlength="24" pattern="[a-zA-Z0-9]{4,24}">
                                <div class="form-text">Formato: sin espacios (ejemplo: ES6621000418401234567891)</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($responsable['observaciones']); ?></textarea>
                            <div class="form-text">Indique aquí cualquier información adicional importante sobre el responsable o sus hijos.</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                            <a href="responsables.php" class="btn btn-secondary">Cancelar</a>
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
                    ¿Está seguro de que desea eliminar al responsable <strong id="deleteModalResponsable"></strong>?
                </div>
                <div class="modal-footer">
                    <form method="post" action="responsables.php">
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
            document.getElementById('deleteModalResponsable').textContent = nombre;
            document.getElementById('deleteModalId').value = id;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
        
        // Función para mostrar/ocultar campo IBAN según forma de pago
        function toggleIban() {
            const formaPago = document.getElementById('forma_pago').value;
            const ibanContainer = document.getElementById('iban_container');
            const ibanInput = document.getElementById('iban');
            
            if (formaPago === 'DOMICILIACION') {
                ibanContainer.style.display = 'block';
            } else {
                ibanContainer.style.display = 'none';
                ibanInput.value = '';
            }
        }
        
        // Inicializar tooltips y campo IBAN
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Inicializar estado del campo IBAN
            toggleIban();
        });
    </script>
</body>
</html>
