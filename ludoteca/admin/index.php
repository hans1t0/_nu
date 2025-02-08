<?php
session_start();
require_once '../includes/db_connect.php';

// Verificar autenticación
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Obtener estadísticas generales
$stmt = $pdo->query("SELECT 
    COUNT(DISTINCT i.id) as total_inscripciones,
    COUNT(DISTINCT t.id) as total_familias,
    SUM(CASE WHEN t.forma_pago = 'domiciliacion' THEN 1 ELSE 0 END) as domiciliaciones,
    SUM(CASE WHEN t.forma_pago = 'transferencia' THEN 1 ELSE 0 END) as transferencias,
    SUM(CASE WHEN t.forma_pago = 'coordinador' THEN 1 ELSE 0 END) as pagos_coordinador
FROM inscripciones i
JOIN alumno_tutor at ON i.alumno_id = at.alumno_id
JOIN tutores t ON at.tutor_id = t.id
WHERE i.estado = 'activa'");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener inscripciones por centro
$stmt = $pdo->query("SELECT 
    c.nombre as centro,
    c.id as id,
    COUNT(DISTINCT i.id) as total,
    COUNT(DISTINCT CASE WHEN a.curso LIKE '%infantil%' THEN i.id END) as infantil,
    COUNT(DISTINCT CASE WHEN a.curso LIKE '%primaria%' THEN i.id END) as primaria
FROM centros c
LEFT JOIN alumnos a ON c.id = a.centro_id
LEFT JOIN inscripciones i ON a.id = i.alumno_id
WHERE i.estado = 'activa'
GROUP BY c.id, c.nombre
ORDER BY c.nombre");
$centros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Ludoteca Tardes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar actualizado -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="#">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inscripciones.php">
                                <i class="bi bi-list-check"></i> Inscripciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../asistencia.php" target="_blank">
                                <i class="bi bi-calendar-check"></i> Control Asistencia
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="exportar.php">
                                <i class="bi bi-download"></i> Exportar Datos
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Dashboard Ludoteca Tardes</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="exportar.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i> Exportar Excel
                        </a>
                    </div>
                </div>

                <!-- Resumen general -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <h5 class="card-title">Total Inscripciones</h5>
                                <p class="card-text h2"><?php echo $stats['total_inscripciones']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <h5 class="card-title">Total Familias</h5>
                                <p class="card-text h2"><?php echo $stats['total_familias']; ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <h5 class="card-title">Formas de Pago</h5>
                                <p class="card-text">
                                    Domiciliación: <?php echo $stats['domiciliaciones']; ?><br>
                                    Transferencia: <?php echo $stats['transferencias']; ?><br>
                                    Coordinador: <?php echo $stats['pagos_coordinador']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de centros -->
                <h2>Desglose por Centros</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Centro</th>
                                <th>Total</th>
                                <th>Infantil</th>
                                <th>Primaria</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($centros as $centro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($centro['centro']); ?></td>
                                <td><?php echo $centro['total'] ?: '0'; ?></td>
                                <td><?php echo $centro['infantil'] ?: '0'; ?></td>
                                <td><?php echo $centro['primaria'] ?: '0'; ?></td>
                                <td>
                                    <a href="inscripciones.php?centro_id=<?php echo urlencode($centro['id']); ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
