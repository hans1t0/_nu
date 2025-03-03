<?php
require_once 'database.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'resumen';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="css/dashboard.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Panel Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'resumen' ? 'active' : '' ?>" href="?page=resumen">
                            <i class="bi bi-house"></i> Resumen
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'inscritos' ? 'active' : '' ?>" href="?page=inscritos">
                            <i class="bi bi-person-check"></i> Inscritos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'actividades' ? 'active' : '' ?>" href="?page=actividades">
                            <i class="bi bi-calendar-event"></i> Actividades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'colegios' ? 'active' : '' ?>" href="?page=colegios">
                            <i class="bi bi-building"></i> Colegios
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'alumnos' ? 'active' : '' ?>" href="?page=alumnos">
                            <i class="bi bi-mortarboard"></i> Alumnos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $page == 'padres' ? 'active' : '' ?>" href="?page=padres">
                            <i class="bi bi-people"></i> Padres
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <?php
        switch($page) {
            case 'resumen':
                include 'pages/resumen.php';
                break;
            case 'inscritos':
                include 'pages/inscritos.php';
                break;
            case 'actividades':
                include 'pages/actividades.php';
                break;
            case 'colegios':
                include 'pages/colegios.php';
                break;
            case 'alumnos':
                include 'pages/alumnos.php';
                break;
            case 'padres':
                include 'pages/padres.php';
                break;
            default:
                include 'pages/resumen.php';
        }
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar DataTables solo en tablas con la clase .datatable que no estén ya inicializadas
            $('table.datatable').not('.dataTable').each(function() {
                $(this).DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 10,
                    responsive: true,
                    order: [[0, 'asc']]
                });
            });
        });
    </script>
</body>
</html>
