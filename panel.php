<?php
session_start();
include('conexion.php');

// Comentado temporalmente para testing
/*if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}*/

// Testing: Simular sesión de administrador
$_SESSION['admin'] = true;

// Obtener estadísticas
$stats = [
    'total_familias' => $conexion->query("SELECT COUNT(*) FROM padres")->fetchColumn(),
    'total_hijos' => $conexion->query("SELECT COUNT(*) FROM hijos")->fetchColumn(),
    'colegios_populares' => $conexion->query("
        SELECT c.nombre, COUNT(*) as total 
        FROM hijos h 
        JOIN colegios c ON h.id_colegio = c.id 
        GROUP BY c.id 
        ORDER BY total DESC 
        LIMIT 5
    ")->fetchAll(),
    'cursos_populares' => $conexion->query("
        SELECT cu.nombre, COUNT(*) as total 
        FROM hijos h 
        JOIN cursos cu ON h.id_curso = cu.id 
        GROUP BY cu.id 
        ORDER BY total DESC 
        LIMIT 5
    ")->fetchAll()
];

// Obtener listado de familias
$familias = $conexion->query("
    SELECT p.*, 
           COUNT(h.id) as num_hijos,
           GROUP_CONCAT(DISTINCT c.nombre) as colegios
    FROM padres p
    LEFT JOIN hijos h ON p.id = h.id_padre
    LEFT JOIN colegios c ON h.id_colegio = c.id
    GROUP BY p.id
    ORDER BY p.fecha_registro DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head></head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inscripciones Deportivas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light"></body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-speedometer2"></i> Panel de Control
            </a>
            <button class="btn btn-outline-light ms-auto">
                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
            </button>
        </div>
    </nav>

    <div class="container py-4">
        <!-- Estadísticas -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-people-fill display-4 text-primary"></i>
                        <h5 class="card-title mt-2">Total Familias</h5>
                        <h2 class="mb-0"><?php echo $stats['total_familias']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-person-lines-fill display-4 text-success"></i>
                        <h5 class="card-title mt-2">Total Hijos</h5>
                        <h2 class="mb-0"><?php echo $stats['total_hijos']; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-building"></i> Colegios Populares
                        </h5>
                        <ul class="list-unstyled">
                            <?php foreach($stats['colegios_populares'] as $colegio): ?>
                            <li class="mb-2">
                                <?php echo $colegio['nombre']; ?>
                                <span class="badge bg-primary float-end"><?php echo $colegio['total']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-mortarboard-fill"></i> Cursos Populares
                        </h5>
                        <ul class="list-unstyled">
                            <?php foreach($stats['cursos_populares'] as $curso): ?>
                            <li class="mb-2">
                                <?php echo $curso['nombre']; ?>
                                <span class="badge bg-primary float-end"><?php echo $curso['total']; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Listado de Familias -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> Familias Registradas</h5>
                <div class="input-group w-auto">
                    <input type="text" id="buscarFamilia" class="form-control" placeholder="Buscar...">
                    <button class="btn btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>DNI</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Hijos</th>
                                <th>Colegios</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($familias as $familia): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($familia['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($familia['dni']); ?></td>
                                <td><?php echo htmlspecialchars($familia['email']); ?></td>
                                <td><?php echo htmlspecialchars($familia['telefono']); ?></td>
                                <td><span class="badge bg-primary"><?php echo $familia['num_hijos']; ?></span></td>
                                <td><?php echo htmlspecialchars($familia['colegios']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($familia['fecha_registro'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $familia['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarFamilia(<?php echo $familia['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/panel.js"></script>
</body>
</html>
