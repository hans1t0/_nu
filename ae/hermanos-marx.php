<?php 
require_once '../config.php';
$pagina = 'extraescolares';
$titulo = 'CEIP Hermanos Marx - Actividades Extraescolares - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/nav.php';
?>

<!-- Hero Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-3">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>extraescolares.php">Extraescolares</a></li>
                        <li class="breadcrumb-item active">CEIP Hermanos Marx</li>
                    </ol>
                </nav>
                <h1 class="display-5 fw-bold mb-4">CEIP Hermanos Marx</h1>
                <p class="lead text-muted">
                    Actividades extraescolares disponibles para el curso 2024-2025 en el CEIP Hermanos Marx.
                </p>
            </div>
            <div class="col-lg-6">
                <img src="<?php echo IMAGES_URL; ?>centros/hermanos-marx.jpg" alt="CEIP Hermanos Marx" class="img-fluid rounded-4 shadow">
            </div>
        </div>
    </div>
</section>

<!-- Horarios Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white py-3">
                        <h3 class="h5 mb-0"><i class="bi bi-calendar3 me-2"></i>Horarios por Actividad</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Actividad</th>
                                        <th>Días</th>
                                        <th>Horario</th>
                                        <th>Edades</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><i class="bi bi-mask text-info me-2"></i>Teatro</td>
                                        <td>Lunes y Miércoles</td>
                                        <td>16:30 - 17:30</td>
                                        <td>6-12 años</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-music-note-beamed text-info me-2"></i>Música</td>
                                        <td>Martes y Jueves</td>
                                        <td>16:30 - 17:30</td>
                                        <td>4-12 años</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-trophy text-info me-2"></i>Fútbol</td>
                                        <td>Lunes y Miércoles</td>
                                        <td>17:30 - 18:30</td>
                                        <td>6-12 años</td>
                                    </tr>
                                    <tr>
                                        <td><i class="bi bi-translate text-info me-2"></i>Inglés</td>
                                        <td>Martes y Jueves</td>
                                        <td>17:30 - 18:30</td>
                                        <td>3-12 años</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Info Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="h5 mb-3"><i class="bi bi-info-circle text-info me-2"></i>Información del Centro</h4>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <i class="bi bi-geo-alt text-info me-2"></i>
                                <strong>Dirección:</strong><br>
                                <small class="text-muted">C/ Cineasta Segundo de Chomón, s/n</small>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-telephone text-info me-2"></i>
                                <strong>Teléfono:</strong><br>
                                <small class="text-muted">976 513 402</small>
                            </li>
                            <li class="mb-3">
                                <i class="bi bi-envelope text-info me-2"></i>
                                <strong>Email:</strong><br>
                                <small class="text-muted">extraescolares@cpchzar.es</small>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Inscripción Card -->
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h4 class="h5 mb-3"><i class="bi bi-pencil-square text-info me-2"></i>Inscripción</h4>
                        <p class="text-muted mb-4">Las inscripciones para el curso 2024-2025 están abiertas.</p>
                        <a href="<?php echo BASE_URL; ?>inscripcion.php" class="btn btn-info rounded-pill w-100">
                            Inscribirse Ahora
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
