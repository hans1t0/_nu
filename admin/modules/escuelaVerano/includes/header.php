<?php
// Verificamos si se ha establecido el título de la página
if (!isset($pageTitle)) {
    $pageTitle = "Escuela de Verano";
}

// Verificamos si se ha establecido la sección actual
if (!isset($currentSection)) {
    $currentSection = "";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> | AMPA</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css">
    <!-- Estilos personalizados -->
    <style>
        .nav-item.active {
            background-color: rgba(0,0,0,0.1);
        }
        .sidebar {
            min-height: 100vh;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .dashboard-card {
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .periodo-card {
            transition: all 0.3s;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .periodo-card.selected {
            border-color: #28a745;
            background-color: rgba(40, 167, 69, 0.1);
        }
        .periodo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .alergia-badge {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .table-fixed {
            table-layout: fixed;
        }
        .horario-730 {
            background-color: rgba(255, 193, 7, 0.2);
        }
        .horario-800 {
            background-color: rgba(23, 162, 184, 0.2);
        }
        .horario-830 {
            background-color: rgba(40, 167, 69, 0.2);
        }
        .servicio-card {
            transition: transform 0.3s;
        }
        .servicio-card:hover {
            transform: translateY(-5px);
        }
        .progress-thin {
            height: 8px;
            margin-bottom: 0;
        }
    </style>
    <!-- Permitir estilos personalizados adicionales por página -->
    <?php if (isset($customStyles)): ?>
        <style>
            <?php echo $customStyles; ?>
        </style>
    <?php endif; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Menú lateral -->
            <nav class="col-md-2 d-none d-md-block bg-light sidebar py-5">
                <div class="sidebar-sticky">
                    <h5 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Escuela de Verano</span>
                    </h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'inicio') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>index.php">
                                <i class="fas fa-home"></i> Inicio
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'responsables') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>responsables.php">
                                <i class="fas fa-users"></i> Responsables
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'participantes') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>participantes.php">
                                <i class="fas fa-child"></i> Participantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'periodos') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>periodos.php">
                                <i class="fas fa-calendar-alt"></i> Periodos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'servicios') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>servicios.php">
                                <i class="fas fa-concierge-bell"></i> Servicios
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'reportes') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>reportes.php">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                    </ul>
                    
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Servicios específicos</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'comedor') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>servicios/comedor.php">
                                <i class="fas fa-utensils"></i> Comedor
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'guarderia') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>servicios/guarderia_matinal.php">
                                <i class="fas fa-sun"></i> Guardería Matinal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($currentSection === 'talleres') ? 'active' : ''; ?>" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>servicios/talleres.php">
                                <i class="fas fa-paint-brush"></i> Talleres
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-5">
                        <?php if (isset($isServicePage) && $isServicePage): ?>
                            <a href="<?php echo isset($baseUrl) ? $baseUrl : '../'; ?>servicios.php" class="btn btn-primary btn-sm btn-block mb-2">
                                <i class="fas fa-arrow-left"></i> Volver a Servicios
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>../../index.php" class="btn btn-secondary btn-sm btn-block">
                            <i class="fas fa-home"></i> Panel principal
                        </a>
                    </div>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main role="main" class="col-md-10 ml-sm-auto px-4 py-4">
