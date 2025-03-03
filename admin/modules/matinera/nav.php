<?php
/**
 * Componente de navegación para el sistema de Matinera (Guardería Matinal)
 * Este archivo debe incluirse en todas las páginas del sistema para mantener 
 * un menú de navegación consistente.
 * 
 * Uso: incluir este archivo después de <body> en cada página
 */

// Determinar qué página está activa actualmente
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Menú de navegación principal -->
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-child"></i> Matinera
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" 
                       <?php echo ($current_page == 'index.php') ? 'aria-current="page"' : ''; ?> href="index.php">
                        <i class="fas fa-home"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'alumnos.php') ? 'active' : ''; ?>" href="alumnos.php">
                        <i class="fas fa-user-graduate"></i> Alumnos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'responsables.php') ? 'active' : ''; ?>" href="responsables.php">
                        <i class="fas fa-users"></i> Responsables
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'asistencia.php') ? 'active' : ''; ?>" href="asistencia.php">
                        <i class="fas fa-calendar-check"></i> Asistencia
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'centros.php') ? 'active' : ''; ?>" href="centros.php">
                        <i class="fas fa-school"></i> Centros
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'informes.php') ? 'active' : ''; ?>" href="informes.php">
                        <i class="fas fa-chart-bar"></i> Informes
                    </a>
                </li>
            </ul>
            <div class="d-flex">
                <a href="../../../admin/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver al Panel Admin
                </a>
            </div>
        </div>
    </div>
</nav>
