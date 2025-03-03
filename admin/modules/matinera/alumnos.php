<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../../admin/database/DatabaseConnectors.php';

// Inicializar variables
$error_message = null;
$success_message = null;
$alumnos = [];
$alumno = [
    'id' => '',
    'nombre' => '',
    'responsable_id' => '',
    'fecha_nacimiento' => '',
    'colegio_id' => '',
    'curso' => '',
    'hora_entrada' => '07:30:00',
    'desayuno' => 0
    // Eliminamos 'observaciones' ya que no existe en la tabla
];
$responsables = [];
$colegios = [];
$cursos = [
    '1INF' => '1º Infantil',
    '2INF' => '2º Infantil',
    '3INF' => '3º Infantil',
    '1PRIM' => '1º Primaria',
    '2PRIM' => '2º Primaria',
    '3PRIM' => '3º Primaria',
    '4PRIM' => '4º Primaria',
    '5PRIM' => '5º Primaria',
    '6PRIM' => '6º Primaria'
];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$filtro_centro = isset($_GET['centro']) ? $_GET['centro'] : '';

// Intentar establecer la conexión usando DatabaseConnectors
try {
    // Obtener la conexión 'matinera'
    $conn = DatabaseConnectors::getConnection('matinera');
    
    // Cargar lista de responsables y colegios para los selectores
    $stmt = $conn->query("SELECT id, nombre, dni, email, telefono, observaciones FROM responsables ORDER BY nombre");
    $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt = $conn->query("SELECT id, nombre FROM colegios ORDER BY nombre");
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar acciones CRUD
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    // Validar datos
                    if (empty($_POST['nombre']) || empty($_POST['responsable_id']) || 
                        empty($_POST['fecha_nacimiento']) || empty($_POST['colegio_id']) || 
                        empty($_POST['curso'])) {
                        $error_message = "Todos los campos marcados con * son obligatorios";
                    } else {
                        try {
                            $stmt = $conn->prepare(
                                "INSERT INTO hijos (nombre, responsable_id, fecha_nacimiento, colegio_id, 
                                curso, hora_entrada, desayuno) 
                                VALUES (:nombre, :responsable_id, :fecha_nacimiento, :colegio_id, 
                                :curso, :hora_entrada, :desayuno)"
                            );
                            $stmt->execute([
                                ':nombre' => $_POST['nombre'],
                                ':responsable_id' => $_POST['responsable_id'],
                                ':fecha_nacimiento' => $_POST['fecha_nacimiento'],
                                ':colegio_id' => $_POST['colegio_id'],
                                ':curso' => $_POST['curso'],
                                ':hora_entrada' => $_POST['hora_entrada'],
                                ':desayuno' => isset($_POST['desayuno']) ? 1 : 0
                            ]);
                            
                            // Si hay observaciones, las guardamos en la tabla de responsables como información adicional
                            if (!empty($_POST['observaciones'])) {
                                $hijo_id = $conn->lastInsertId();
                                $responsable_id = $_POST['responsable_id'];
                                
                                // Obtener observaciones actuales del responsable
                                $stmt = $conn->prepare("SELECT observaciones FROM responsables WHERE id = :id");
                                $stmt->execute([':id' => $responsable_id]);
                                $row = $stmt->fetch();
                                $observaciones_actuales = $row['observaciones'] ?? '';
                                
                                // Añadir las nuevas observaciones
                                $nuevas_observaciones = $observaciones_actuales;
                                if (!empty($nuevas_observaciones)) {
                                    $nuevas_observaciones .= "\n\n";
                                }
                                $nuevas_observaciones .= "Observaciones para " . $_POST['nombre'] . ": " . $_POST['observaciones'];
                                
                                // Actualizar las observaciones del responsable
                                $stmt = $conn->prepare("UPDATE responsables SET observaciones = :observaciones WHERE id = :id");
                                $stmt->execute([
                                    ':observaciones' => $nuevas_observaciones,
                                    ':id' => $responsable_id
                                ]);
                            }
                            
                            $success_message = "Alumno añadido correctamente";
                            $action = 'list';
                        } catch (PDOException $e) {
                            $error_message = "Error al crear el alumno: " . $e->getMessage();
                        }
                    }
                    break;
                    
                case 'update':
                    // Validar datos
                    if (empty($_POST['nombre']) || empty($_POST['responsable_id']) || 
                        empty($_POST['fecha_nacimiento']) || empty($_POST['colegio_id']) || 
                        empty($_POST['curso']) || empty($_POST['id'])) {
                        $error_message = "Todos los campos marcados con * son obligatorios";
                    } else {
                        try {
                            $stmt = $conn->prepare(
                                "UPDATE hijos SET nombre = :nombre, responsable_id = :responsable_id, 
                                fecha_nacimiento = :fecha_nacimiento, colegio_id = :colegio_id,
                                curso = :curso, hora_entrada = :hora_entrada, desayuno = :desayuno
                                WHERE id = :id"
                            );
                            $stmt->execute([
                                ':nombre' => $_POST['nombre'],
                                ':responsable_id' => $_POST['responsable_id'],
                                ':fecha_nacimiento' => $_POST['fecha_nacimiento'],
                                ':colegio_id' => $_POST['colegio_id'],
                                ':curso' => $_POST['curso'],
                                ':hora_entrada' => $_POST['hora_entrada'],
                                ':desayuno' => isset($_POST['desayuno']) ? 1 : 0,
                                ':id' => $_POST['id']
                            ]);
                            
                            // Gestionar observaciones en la tabla de responsables
                            if (isset($_POST['observaciones'])) {
                                $responsable_id = $_POST['responsable_id'];
                                
                                // Añadimos la nota en las observaciones del responsable
                                if (!empty($_POST['observaciones'])) {
                                    // Obtener observaciones actuales del responsable
                                    $stmt = $conn->prepare("SELECT observaciones FROM responsables WHERE id = :id");
                                    $stmt->execute([':id' => $responsable_id]);
                                    $row = $stmt->fetch();
                                    $observaciones_actuales = $row['observaciones'] ?? '';
                                    
                                    // Crear nueva observación
                                    $nombre_alumno = $_POST['nombre'];
                                    $nuevas_observaciones = $observaciones_actuales;
                                    
                                    // Verificar si ya existe una observación para este alumno
                                    $patron = "/Observaciones para " . preg_quote($nombre_alumno, "/") . ": .+?(?=\n\nObservaciones para|$)/s";
                                    
                                    if (preg_match($patron, $nuevas_observaciones)) {
                                        // Reemplazar las observaciones existentes
                                        $nuevas_observaciones = preg_replace(
                                            $patron, 
                                            "Observaciones para " . $nombre_alumno . ": " . $_POST['observaciones'], 
                                            $nuevas_observaciones
                                        );
                                    } else {
                                        // Añadir nuevas observaciones
                                        if (!empty($nuevas_observaciones)) {
                                            $nuevas_observaciones .= "\n\n";
                                        }
                                        $nuevas_observaciones .= "Observaciones para " . $nombre_alumno . ": " . $_POST['observaciones'];
                                    }
                                    
                                    // Actualizar las observaciones del responsable
                                    $stmt = $conn->prepare("UPDATE responsables SET observaciones = :observaciones WHERE id = :id");
                                    $stmt->execute([
                                        ':observaciones' => $nuevas_observaciones,
                                        ':id' => $responsable_id
                                    ]);
                                }
                            }
                            
                            $success_message = "Alumno actualizado correctamente";
                            $action = 'list';
                        } catch (PDOException $e) {
                            $error_message = "Error al actualizar el alumno: " . $e->getMessage();
                        }
                    }
                    break;
                    
                case 'delete':
                    if (!empty($_POST['id'])) {
                        try {
                            // Verificar si tiene asistencias registradas
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM asistencias WHERE hijo_id = :id");
                            $stmt->execute([':id' => $_POST['id']]);
                            $asistencias = $stmt->fetchColumn();
                            
                            if ($asistencias > 0) {
                                // Eliminar primero las asistencias asociadas
                                $stmt = $conn->prepare("DELETE FROM asistencias WHERE hijo_id = :id");
                                $stmt->execute([':id' => $_POST['id']]);
                            }
                            
                            // Ahora eliminar al alumno
                            $stmt = $conn->prepare("DELETE FROM hijos WHERE id = :id");
                            $stmt->execute([':id' => $_POST['id']]);
                            
                            $success_message = "Alumno eliminado correctamente" . 
                                ($asistencias > 0 ? " (incluyendo $asistencias registros de asistencia)" : "");
                        } catch (PDOException $e) {
                            $error_message = "Error al eliminar el alumno: " . $e->getMessage();
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
                $stmt = $conn->prepare("SELECT h.*, r.observaciones as responsable_observaciones 
                                        FROM hijos h
                                        LEFT JOIN responsables r ON h.responsable_id = r.id
                                        WHERE h.id = :id");
                $stmt->execute([':id' => $_GET['id']]);
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $alumno = $row;
                    
                    // Extraer observaciones específicas del alumno desde las observaciones del responsable
                    $observaciones_responsable = $row['responsable_observaciones'] ?? '';
                    $patron = "/Observaciones para " . preg_quote($row['nombre'], "/") . ": (.*?)(?=\n\nObservaciones para|$)/s";
                    if (preg_match($patron, $observaciones_responsable, $matches)) {
                        $alumno['observaciones_personales'] = trim($matches[1]);
                    } else {
                        $alumno['observaciones_personales'] = '';
                    }
                } else {
                    $error_message = "Alumno no encontrado";
                    $action = 'list';
                }
            }
            break;
            
        case 'new':
            // Inicializar alumno vacío ya está hecho arriba
            $alumno['observaciones_personales'] = '';
            break;
            
        default: // list
            $query = "
                SELECT h.*, 
                       r.nombre as responsable_nombre, 
                       r.dni as responsable_dni,
                       r.email as responsable_email,
                       r.telefono as responsable_telefono,
                       r.observaciones as responsable_observaciones,
                       c.nombre as colegio_nombre
                FROM hijos h
                JOIN responsables r ON h.responsable_id = r.id
                JOIN colegios c ON h.colegio_id = c.id
            ";
            
            // Aplicar filtro de centro si está seleccionado
            $params = [];
            if (!empty($filtro_centro)) {
                $query .= " WHERE h.colegio_id = :centro_id";
                $params[':centro_id'] = $filtro_centro;
            }
            
            $query .= " ORDER BY h.nombre";
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Extraer observaciones específicas para cada alumno
            foreach ($alumnos as $key => $alumno_item) {
                $observaciones_responsable = $alumno_item['responsable_observaciones'] ?? '';
                $patron = "/Observaciones para " . preg_quote($alumno_item['nombre'], "/") . ": (.*?)(?=\n\nObservaciones para|$)/s";
                if (preg_match($patron, $observaciones_responsable, $matches)) {
                    $alumnos[$key]['observaciones_personales'] = trim($matches[1]);
                } else {
                    $alumnos[$key]['observaciones_personales'] = '';
                }
            }
            
            break;
    }
    
} catch (Exception $e) {
    $error_message = "Error de conexión: " . $e->getMessage();
}

// Función para calcular la edad a partir de la fecha de nacimiento
function calcularEdad($fechaNacimiento) {
    $hoy = new DateTime();
    $cumpleanos = new DateTime($fechaNacimiento);
    $edad = $hoy->diff($cumpleanos);
    return $edad->y;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - Matinera</title>
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
        .badge-desayuno {
            background-color: #28a745;
            color: white;
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            font-size: 75%;
        }
        .alumno-row:hover {
            background-color: #f8f9fa;
        }
        .required-field::after {
            content: " *";
            color: red;
        }
        .responsable-info {
            margin-bottom: 0.5rem;
        }
        .responsable-info i {
            width: 16px;
            margin-right: 5px;
            color: #6c757d;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            font-size: 75%;
        }
        .tooltip-inner {
            max-width: 300px;
        }
        .observacion-text {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
        }
        .observacion-completa {
            font-style: italic;
            color: #6c757d;
            background-color: #f8f9fa;
            padding: 5px 8px;
            border-radius: 4px;
            border-left: 3px solid #ffc107;
        }
        .observacion-alerta {
            color: #721c24;
            background-color: #f8d7da;
            padding: 2px 5px;
            border-radius: 4px;
            border-left: 3px solid #dc3545;
            display: inline-block;
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
                Nuevo Alumno
            <?php elseif ($action === 'edit'): ?>
                Editar Alumno
            <?php else: ?>
                Gestión de Alumnos
            <?php endif; ?>
        </h1>
        
        <?php if ($action === 'list'): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Listado de Alumnos</h5>
                    <div>
                        <a href="?action=new" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Nuevo Alumno
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filtros -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <form method="get" action="alumnos.php" class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <select name="centro" class="form-select" onchange="this.form.submit()">
                                        <option value="">Todos los centros</option>
                                        <?php foreach ($colegios as $colegio): ?>
                                            <option value="<?php echo $colegio['id']; ?>" 
                                                <?php echo ($filtro_centro == $colegio['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($colegio['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if (!empty($filtro_centro)): ?>
                                    <div>
                                        <a href="alumnos.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times"></i> Limpiar
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <span class="text-muted">
                                Mostrando <?php echo count($alumnos); ?> alumnos
                                <?php if (!empty($filtro_centro)): 
                                    $nombre_centro = "";
                                    foreach ($colegios as $colegio) {
                                        if ($colegio['id'] == $filtro_centro) {
                                            $nombre_centro = $colegio['nombre'];
                                            break;
                                        }
                                    }
                                ?>
                                    del centro: <?php echo htmlspecialchars($nombre_centro); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (empty($alumnos)): ?>
                        <div class="alert alert-info">
                            No hay alumnos registrados
                            <?php if (!empty($filtro_centro)): ?>
                                para el centro seleccionado
                            <?php endif; ?>. 
                            <a href="?action=new" class="alert-link">Crear nuevo alumno</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Edad</th>
                                        <th>Curso</th>
                                        <th>Centro</th>
                                        <th>Responsable</th>
                                        <th>Entrada</th>
                                        <th>Desayuno</th>
                                        <th>Observaciones</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($alumnos as $alumno): ?>
                                        <tr class="alumno-row">
                                            <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                                            <td>
                                                <?php 
                                                    try {
                                                        echo calcularEdad($alumno['fecha_nacimiento']) . " años";
                                                    } catch (Exception $e) {
                                                        echo "N/A";
                                                    }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($cursos[$alumno['curso']] ?? $alumno['curso']); ?></td>
                                            <td><?php echo htmlspecialchars($alumno['colegio_nombre']); ?></td>
                                            <td>
                                                <div class="responsable-info">
                                                    <strong><?php echo htmlspecialchars($alumno['responsable_nombre']); ?></strong>
                                                    <div class="small text-muted">DNI: <?php echo htmlspecialchars($alumno['responsable_dni']); ?></div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($alumno['responsable_telefono']); ?>
                                                    </div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($alumno['responsable_email']); ?>
                                                    </div>
                                                    <?php if (!empty($alumno['responsable_observaciones']) && $alumno['responsable_observaciones'] != $alumno['observaciones_personales']): ?>
                                                        <div>
                                                            <button class="btn btn-sm btn-link p-0" data-bs-toggle="tooltip" 
                                                                    data-bs-html="true" title="<?php echo htmlspecialchars($alumno['responsable_observaciones']); ?>">
                                                                <i class="fas fa-info-circle"></i> Ver observaciones resp.
                                                            </button>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?php echo substr($alumno['hora_entrada'], 0, 5); ?></td>
                                            <td>
                                                <?php if ($alumno['desayuno']): ?>
                                                    <span class="badge-desayuno">
                                                        <i class="fas fa-check"></i> Sí
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">No</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($alumno['observaciones_personales'])): 
                                                    $observacionText = htmlspecialchars($alumno['observaciones_personales']);
                                                    $esAlergia = stripos($observacionText, 'alerg') !== false;
                                                ?>
                                                    <div class="<?php echo $esAlergia ? 'observacion-alerta' : 'observacion-completa'; ?>"
                                                         <?php if (strlen($observacionText) > 100): ?>
                                                            data-bs-toggle="tooltip" title="<?php echo $observacionText; ?>"
                                                         <?php endif; ?>>
                                                        <?php 
                                                            if ($esAlergia) {
                                                                echo '<i class="fas fa-exclamation-triangle"></i> ';
                                                            }
                                                            echo (strlen($observacionText) > 100) ? 
                                                                substr($observacionText, 0, 97) . '...' : 
                                                                $observacionText; 
                                                        ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="?action=edit&id=<?php echo $alumno['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="confirmDelete(<?php echo $alumno['id']; ?>, '<?php echo htmlspecialchars($alumno['nombre']); ?>')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                    <h5><?php echo ($action === 'new') ? 'Nuevo Alumno' : 'Editar Alumno'; ?></h5>
                </div>
                <div class="card-body">
                    <?php if (empty($responsables)): ?>
                        <div class="alert alert-warning">
                            No hay responsables registrados. 
                            <a href="responsables.php?action=new" class="alert-link">Debe crear al menos un responsable</a> antes de añadir alumnos.
                        </div>
                    <?php elseif (empty($colegios)): ?>
                        <div class="alert alert-warning">
                            No hay centros educativos registrados. 
                            <a href="centros.php?action=new" class="alert-link">Debe crear al menos un centro</a> antes de añadir alumnos.
                        </div>
                    <?php else: ?>
                        <form method="post" action="alumnos.php">
                            <input type="hidden" name="action" value="<?php echo ($action === 'new') ? 'create' : 'update'; ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="id" value="<?php echo $alumno['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre" class="form-label required-field">Nombre del alumno</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                        value="<?php echo htmlspecialchars($alumno['nombre']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="fecha_nacimiento" class="form-label required-field">Fecha de nacimiento</label>
                                    <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" 
                                        value="<?php echo $alumno['fecha_nacimiento']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="colegio_id" class="form-label required-field">Centro educativo</label>
                                    <select class="form-select" id="colegio_id" name="colegio_id" required>
                                        <option value="">Seleccione un centro</option>
                                        <?php foreach ($colegios as $colegio): ?>
                                            <option value="<?php echo $colegio['id']; ?>" 
                                                <?php echo ($alumno['colegio_id'] == $colegio['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($colegio['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="responsable_id" class="form-label required-field">Responsable</label>
                                    <select class="form-select" id="responsable_id" name="responsable_id" required>
                                        <option value="">Seleccione un responsable</option>
                                        <?php foreach ($responsables as $responsable): ?>
                                            <option value="<?php echo $responsable['id']; ?>" 
                                                <?php echo ($alumno['responsable_id'] == $responsable['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($responsable['nombre']) . ' (' . $responsable['dni'] . ' - ' . $responsable['telefono'] . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="curso" class="form-label required-field">Curso</label>
                                    <select class="form-select" id="curso" name="curso" required>
                                        <option value="">Seleccione un curso</option>
                                        <?php foreach ($cursos as $key => $value): ?>
                                            <option value="<?php echo $key; ?>" 
                                                <?php echo ($alumno['curso'] == $key) ? 'selected' : ''; ?>>
                                                <?php echo $value; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="hora_entrada" class="form-label required-field">Hora de entrada</label>
                                    <input type="time" class="form-control" id="hora_entrada" name="hora_entrada" 
                                        value="<?php echo $alumno['hora_entrada']; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <div class="mt-4 pt-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="desayuno" name="desayuno" 
                                                <?php echo $alumno['desayuno'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="desayuno">
                                                ¿Incluye servicio de desayuno?
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="observaciones" class="form-label">Observaciones (alergias, información importante)</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?php echo htmlspecialchars($alumno['observaciones_personales'] ?? ''); ?></textarea>
                                <div class="form-text">Indique aquí si el alumno tiene alergias o cualquier otra información relevante. Esta información se guardará en las observaciones del responsable.</div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar
                                </button>
                                <a href="alumnos.php<?php echo (!empty($filtro_centro) ? '?centro=' . $filtro_centro : ''); ?>" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    <?php endif; ?>
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
                    ¿Está seguro de que desea eliminar al alumno <strong id="deleteModalAlumno"></strong>?
                    <div class="alert alert-warning mt-2">
                        <i class="fas fa-exclamation-triangle"></i> Esta acción también eliminará todos los registros de asistencia asociados.
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="post" action="alumnos.php<?php echo (!empty($filtro_centro) ? '?centro=' . $filtro_centro : ''); ?>">
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
            document.getElementById('deleteModalAlumno').textContent = nombre;
            document.getElementById('deleteModalId').value = id;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        }
        
        // Inicializar tooltips
        document.addEventListener('DOMContentLoaded', function() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
        });
    </script>
</body>
</html>