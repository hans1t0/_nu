<?php
require_once '../includes/db_functions.php';
session_start();

// Obtener filtros
$filtros = [
    'colegio' => $_GET['colegio'] ?? '',
    'curso' => $_GET['curso'] ?? '',
    'desayuno' => $_GET['desayuno'] ?? ''
];

$colegios = getColegios();
$inscripciones = getInscripciones($filtros);
$estadisticas = getEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Guardería Matinal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading px-3 mt-4 mb-1 text-muted">
                        <span>Resumen</span>
                    </h6>
                    <div class="stats p-3">
                        <div class="stat-item">
                            <i class="bi bi-people"></i>
                            <span>Total Inscritos: <?php echo $estadisticas['total_inscritos']; ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="bi bi-cup-hot"></i>
                            <span>Con Desayuno: <?php echo $estadisticas['total_desayunos']; ?></span>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Inscripciones Guardería Matinal</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportarExcel()">
                            <i class="bi bi-file-excel"></i> Exportar Excel
                        </button>
                    </div>
                </div>

                <!-- Pestañas de colegios -->
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item">
                        <a class="nav-link <?php echo empty($filtros['colegio']) ? 'active' : ''; ?>" 
                           href="?colegio=">Todos</a>
                    </li>
                    <?php foreach ($colegios as $colegio): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($filtros['colegio'] == $colegio['codigo']) ? 'active' : ''; ?>" 
                           href="?colegio=<?php echo $colegio['codigo']; ?>">
                            <?php echo $colegio['nombre']; ?>
                            <span class="badge bg-secondary">
                                <?php echo contarInscripcionesPorColegio($colegio['codigo']); ?>
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <!-- Filtros secundarios -->
                <form class="row g-3 mb-4">
                    <?php if ($filtros['colegio']): ?>
                        <input type="hidden" name="colegio" value="<?php echo $filtros['colegio']; ?>">
                    <?php endif; ?>
                    <div class="col-md-3">
                        <select name="curso" class="form-select" onchange="this.form.submit()">
                            <option value="">Todos los cursos</option>
                            <?php foreach (getCursos() as $codigo => $nombre): ?>
                                <option value="<?php echo $codigo; ?>"
                                    <?php echo ($filtros['curso'] == $codigo) ? 'selected' : ''; ?>>
                                    <?php echo $nombre; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="desayuno" class="form-select" onchange="this.form.submit()">
                            <option value="">Desayuno (Todos)</option>
                            <option value="1" <?php echo ($filtros['desayuno'] === '1') ? 'selected' : ''; ?>>Con desayuno</option>
                            <option value="0" <?php echo ($filtros['desayuno'] === '0') ? 'selected' : ''; ?>>Sin desayuno</option>
                        </select>
                    </div>
                </form>

                <!-- Tabla de inscripciones -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Responsable</th>
                                <th>DNI</th>
                                <th>Total Hijos</th>
                                <th>Hijo</th>
                                <th>Colegio</th>
                                <th>Curso</th>
                                <th>Hora</th>
                                <?php if ($filtros['colegio'] === 'ALMADRABA'): ?>
                                    <th>Desayuno</th>
                                <?php endif; ?>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $i): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($i['responsable']); ?></td>
                                <td><?php echo htmlspecialchars($i['dni']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <i class="bi bi-people"></i>
                                        <?php echo $i['total_hijos']; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($i['hijo']); ?></td>
                                <td><?php echo htmlspecialchars($i['colegio']); ?></td>
                                <td><?php echo htmlspecialchars($i['curso']); ?></td>
                                <td><?php echo htmlspecialchars($i['hora_entrada']); ?></td>
                                <?php if ($filtros['colegio'] === 'ALMADRABA'): ?>
                                    <td>
                                        <?php if ($i['desayuno']): ?>
                                            <span class="badge bg-success rounded-circle p-1" 
                                                  title="Con desayuno - <?= htmlspecialchars($i['hora_entrada']) ?>"
                                                  data-bs-toggle="tooltip">
                                                <i class="bi bi-cup-hot-fill"></i>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary rounded-circle p-1"
                                                  title="Sin desayuno"
                                                  data-bs-toggle="tooltip">
                                                <i class="bi bi-x"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $i['precio']; ?>€
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verDetalles(<?php echo $i['id']; ?>)">
                                        <i class="bi bi-eye"></i>
                                    </button>
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
    <script src="../assets/js/admin.js"></script>
    <script>
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    </script>
</body>
</html>
