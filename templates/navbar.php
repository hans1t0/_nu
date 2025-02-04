<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="actividades.php">
            <i class="bi bi-calendar-check"></i> 
            Gestión de Actividades
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'actividades.php' ? 'active' : '' ?>" 
                       href="actividades.php">
                        <i class="bi bi-list-check"></i> Actividades
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'asignaciones.php' ? 'active' : '' ?>" 
                       href="asignaciones.php">
                        <i class="bi bi-building"></i> Asignaciones
                    </a>
                </li>
            </ul>
            <div class="d-flex">
                <a href="add_actividades.php" class="btn btn-light">
                    <i class="bi bi-plus-circle"></i>
                    Nueva Actividad
                </a>
            </div>
        </div>
    </div>
</nav>
