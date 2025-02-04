<?php
include('conexion.php');

$mensaje = '';
$error = '';

// Obtener actividad si se especifica ID
if (isset($_GET['actividad_id'])) {
    $stmt = $conexion->prepare("SELECT * FROM actividades WHERE id = ?");
    $stmt->execute([$_GET['actividad_id']]);
    $actividad = $stmt->fetch();

    if ($actividad) {
        // Obtener asignaciones existentes
        $stmt = $conexion->prepare("
            SELECT ca.*, c.nombre as colegio_nombre, 
                   (SELECT COUNT(*) FROM inscripciones_actividad 
                    WHERE id_colegio = ca.id_colegio 
                    AND id_actividad = ca.id_actividad) as inscritos
            FROM colegio_actividad ca 
            JOIN colegios c ON ca.id_colegio = c.id
            WHERE ca.id_actividad = ?
            ORDER BY c.nombre, ca.horario
        ");
        $stmt->execute([$_GET['actividad_id']]);
        $asignaciones = $stmt->fetchAll();
    }
}

// Obtener lista de actividades para el selector
$actividades = $conexion->query("
    SELECT id, nombre, nivel_requerido 
    FROM actividades 
    ORDER BY nivel_requerido, nombre
")->fetchAll();

// Obtener colegios y cursos
$colegios = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre")->fetchAll();
$cursos = $conexion->query("SELECT id, nombre, nivel, grado FROM cursos ORDER BY nivel, grado")->fetchAll();

// Manejar actualizaciones de asignaciones
if (isset($_POST['actualizar_asignaciones'])) {
    try {
        $conexion->beginTransaction();
        
        // Eliminar asignaciones existentes
        $stmt = $conexion->prepare("DELETE FROM colegio_actividad WHERE id_actividad = ?");
        $stmt->execute([$_POST['actividad_id']]);
        
        // Insertar nuevas asignaciones
        $stmt = $conexion->prepare("
            INSERT INTO colegio_actividad (
                id_colegio, id_actividad, nivel, grado_minimo, 
                grado_maximo, horario, precio, activa
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['colegios'] as $i => $colegio_id) {
            if (!empty($colegio_id) && !empty($_POST['horarios'][$i])) {
                $stmt->execute([
                    $colegio_id,
                    $_POST['actividad_id'],
                    $_POST['niveles'][$i],
                    $_POST['grados_min'][$i],
                    $_POST['grados_max'][$i],
                    $_POST['horarios'][$i],
                    $_POST['precios'][$i],
                    isset($_POST['activa'][$i]) ? 1 : 0
                ]);
            }
        }
        
        $conexion->commit();
        $mensaje = "Asignaciones actualizadas correctamente";
    } catch (Exception $e) {
        $conexion->rollBack();
        $error = "Error al actualizar asignaciones: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Asignaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Mismo menú que actividades.php pero con asignaciones activo -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check"></i> 
                Gestión de Actividades
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="actividades.php">
                            <i class="bi bi-list-check"></i> Actividades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="asignaciones.php">
                            <i class="bi bi-building"></i> Asignaciones
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Selector de actividad -->
        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="bi bi-search"></i> Seleccionar Actividad</h5>
            </div>
            <div class="card-body">
                <select class="form-select" onchange="location = '?actividad_id=' + this.value">
                    <option value="">Seleccione una actividad...</option>
                    <?php foreach ($actividades as $act): ?>
                        <option value="<?= $act['id'] ?>" 
                            <?= isset($_GET['actividad_id']) && $_GET['actividad_id'] == $act['id'] ? 'selected' : '' ?>>
                            <?= $act['nombre'] ?> (<?= $act['nivel_requerido'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (isset($actividad)): ?>
            <!-- Formulario de asignaciones -->
            <div class="card">
                <div class="card-header">
                    <h5>
                        <i class="bi bi-building"></i> 
                        Asignaciones: <?= htmlspecialchars($actividad['nombre']) ?>
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Resto del contenido del formulario de asignaciones -->
                    <?php include('templates/asignacion_form.php'); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Incluir el JavaScript necesario para las asignaciones -->
    <script src="assets/js/asignaciones.js"></script>
</body>
</html>
