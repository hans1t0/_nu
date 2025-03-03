<?php 
$pagina = 'matinera';
$titulo = 'Guardería Matinal - Educap';
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="background-image: url('<?php echo $baseUrl; ?>/assets/img/matinera.jpg');">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-6">
                <div class="p-4 rounded-3 blur-bg">
                    <span class="badge bg-primary mb-3 px-3 py-2 rounded-pill fs-6">
                        <i class="bi bi-clock me-2"></i>7:30 - 9:00
                    </span>
                    <h1 class="display-4 fw-bold text-white mb-4">Guardería Matinal</h1>
                    <p class="lead text-white opacity-90">
                        Servicio de atención temprana para facilitar la conciliación familiar.
                        Incluye desayuno y actividades supervisadas.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#inscripcion" class="btn btn-primary btn-lg rounded-pill px-4">
                            Inscríbete ahora <i class="bi bi-arrow-right-circle ms-2"></i>
                        </a>
                        <a href="#info" class="btn btn-outline-light btn-lg rounded-pill px-4">
                            Más información
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Información Principal -->
<section class="py-5" id="info">
    <div class="container">
        <div class="row g-4">
            <!-- Info Cards - 3 columnas -->
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white p-4">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Horarios
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                <i class="bi bi-alarm h4 text-primary mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Horario del Servicio</h4>
                                <p class="text-muted mb-0">7:30 - 9:00</p>
                            </div>
                        </div>
                        <div class="alert alert-info mb-0">
                            <small><i class="bi bi-info-circle me-2"></i>Servicio disponible durante todo el curso escolar</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white p-4">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-card-checklist me-2"></i>
                            Servicios Incluidos
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex">
                                <i class="bi bi-cup-hot text-primary h5 mb-0 me-3"></i>
                                <div>
                                    <strong class="d-block">Desayuno Equilibrado</strong>
                                    <small class="text-muted">Supervisado por monitores</small>
                                </div>
                            </li>
                            <li class="mb-3 d-flex">
                                <i class="bi bi-puzzle text-primary h5 mb-0 me-3"></i>
                                <div>
                                    <strong class="d-block">Actividades Lúdicas</strong>
                                    <small class="text-muted">Juegos y entretenimiento</small>
                                </div>
                            </li>
                            <li class="d-flex">
                                <i class="bi bi-shield-check text-primary h5 mb-0 me-3"></i>
                                <div>
                                    <strong class="d-block">Atención Personalizada</strong>
                                    <small class="text-muted">Personal cualificado</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white p-4">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-cash-coin me-2"></i>
                            Tarifas
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h4 class="h6 mb-3">Cuota Mensual</h4>
                            <div class="d-flex align-items-baseline">
                                <span class="display-6 fw-bold text-primary">35€</span>
                                <span class="text-muted ms-2">/mes</span>
                            </div>
                        </div>
                        <div class="alert alert-warning bg-warning bg-opacity-10 mb-0">
                            <small><i class="bi bi-exclamation-triangle me-2"></i>Consulta descuentos para hermanos</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal - Ancho completo -->
            <div class="col-12 mt-4">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-lg-8">
                                <h2 class="mb-4">Sobre el Servicio</h2>
                                <p class="lead text-muted mb-4">
                                    La guardería matinal es un servicio diseñado para facilitar la conciliación familiar, 
                                    permitiendo a los padres dejar a sus hijos antes del inicio de la jornada escolar en un 
                                    entorno seguro y supervisado.
                                </p>
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bi bi-people-fill h4 mb-0 me-2"></i>
                                    <span>Monitores cualificados con amplia experiencia</span>
                                </div>
                            </div>
                            <div class="col-lg-4 text-center mt-4 mt-lg-0">
                                <a href="/inscripciones/matinera" class="btn btn-primary btn-lg rounded-pill px-5 py-3">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    Inscripción Online
                                </a>
                                <p class="text-muted mt-3 mb-0">
                                    <small><i class="bi bi-info-circle me-1"></i>Sistema de inscripción seguro</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
