<?php
require_once '../../../config/config.php';
require_once '../../../config/functions.php';
require_once '../../../admin/database/DatabaseConnectors.php';

// Verificar la sesión del usuario
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../login.php");
    exit();
}

// Inicializar variables
$message = '';
$messageType = '';

try {
    $conn = DatabaseConnectors::getConnection('matinera');
    
    // Procesar formulario de pago
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    // Agregar nuevo pago
                    $stmt = $conn->prepare("INSERT INTO matinera_pagos (alumno_id, fecha_pago, monto, concepto) 
                                VALUES (:alumno_id, :fecha_pago, :monto, :concepto)");
                    
                    $stmt->execute([
                        ':alumno_id' => $_POST['alumno_id'],
                        ':fecha_pago' => $_POST['fecha_pago'],
                        ':monto' => $_POST['monto'],
                        ':concepto' => $_POST['concepto']
                    ]);
                    
                    if ($stmt->rowCount() > 0) {
                        $message = "Pago registrado correctamente";
                        $messageType = "success";
                    } else {
                        $message = "Error al registrar el pago";
                        $messageType = "danger";
                    }
                    break;
                    
                case 'delete':
                    // Eliminar pago
                    $stmt = $conn->prepare("DELETE FROM matinera_pagos WHERE id = :pago_id");
                    $stmt->execute([':pago_id' => $_POST['pago_id']]);
                    
                    if ($stmt->rowCount() > 0) {
                        $message = "Pago eliminado correctamente";
                        $messageType = "success";
                    } else {
                        $message = "Error al eliminar el pago";
                        $messageType = "danger";
                    }
                    break;
            }
        }
    }

    // Obtener lista de pagos
    $pagos = [];
    $stmt = $conn->prepare("SELECT mp.*, ma.nombre, ma.apellidos 
              FROM matinera_pagos mp 
              JOIN matinera_alumnos ma ON mp.alumno_id = ma.id 
              ORDER BY mp.fecha_pago DESC");
    $stmt->execute();
    $pagos = $stmt->fetchAll();

    // Obtener lista de alumnos para el formulario
    $stmt = $conn->prepare("SELECT id, nombre, apellidos FROM matinera_alumnos ORDER BY apellidos");
    $stmt->execute();
    $alumnos = $stmt->fetchAll();

} catch (Exception $e) {
    $message = "Error de conexión: " . $e->getMessage();
    $messageType = "danger";
    $pagos = [];
    $alumnos = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - Matinera</title>
    <link rel="stylesheet" href="../../../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../../assets/css/styles.css">
</head>
<body>
    <?php include '../../../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h1>Gestión de Pagos - Matinera</h1>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5>Registrar Nuevo Pago</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="alumno_id" class="form-label">Alumno</label>
                            <select class="form-control" id="alumno_id" name="alumno_id" required>
                                <option value="">Seleccione un alumno</option>
                                <?php foreach ($alumnos as $alumno): ?>
                                <option value="<?php echo $alumno['id']; ?>">
                                    <?php echo $alumno['apellidos'] . ', ' . $alumno['nombre']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="fecha_pago" class="form-label">Fecha de Pago</label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="monto" class="form-label">Monto (€)</label>
                            <input type="number" step="0.01" class="form-control" id="monto" name="monto" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="concepto" class="form-label">Concepto</label>
                            <input type="text" class="form-control" id="concepto" name="concepto" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                </form>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Listado de Pagos</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Alumno</th>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pagos)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay pagos registrados</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($pagos as $pago): ?>
                            <tr>
                                <td><?php echo $pago['id']; ?></td>
                                <td><?php echo $pago['apellidos'] . ', ' . $pago['nombre']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></td>
                                <td><?php echo $pago['concepto']; ?></td>
                                <td><?php echo number_format($pago['monto'], 2, ',', '.') . ' €'; ?></td>
                                <td>
                                    <form method="post" action="" onsubmit="return confirm('¿Está seguro de eliminar este pago?');" style="display:inline;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="pago_id" value="<?php echo $pago['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <?php include '../../../includes/footer.php'; ?>
    <script src="../../../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
