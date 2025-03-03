<?php 
$pagina = 'ludoteca';
$titulo = 'Ludoteca Tardes - Educap';
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="background-image: url('<?php echo $baseUrl; ?>assets/img/tardes.jpg');">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-6">
                <div class="p-4 rounded-3 blur-bg">
                    <span class="badge bg-warning mb-3 px-3 py-2 rounded-pill fs-6">
                        <i class="bi bi-clock me-2"></i>15:00 - 17:00
                    </span>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Ludoteca Tardes
                        <div class="h3 fw-light mt-2">Curso <?php echo date('Y'); ?></div>
                    </h1>
                    <p class="lead text-white opacity-90">
                        Servicio de atención por las tardes con actividades lúdicas y educativas.
                        Un espacio seguro y divertido para el desarrollo de tus hijos.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="ludoteca/tardes.php" class="btn btn-warning btn-lg rounded-pill px-4">
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
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <!-- Card de Información -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-white p-4">
                            <h3 class="h5 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                Información General
                            </h3>
                        </div>
                        <div class="card-body p-4">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="bi bi-clock text-warning h5 mb-0 me-3"></i>
                                    <div>
                                        <strong class="d-block">Horarios</strong>
                                        <span class="text-muted">15:00 - 16:00</span><br>
                                        <span class="text-muted">15:00 - 17:00</span>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="bi bi-calendar text-warning h5 mb-0 me-3"></i>
                                    <div>
                                        <strong class="d-block">Periodo</strong>
                                        <span class="text-muted">Septiembre y Junio</span>
                                    </div>
                                </li>
                                <li class="mb-3 d-flex align-items-center">
                                    <i class="bi bi-cash text-warning h5 mb-0 me-3"></i>
                                    <div>
                                        <strong class="d-block">Precios</strong>
                                        <span class="text-muted">Hasta 16:00 - 25€/mes</span><br>
                                        <span class="text-muted">Hasta 17:00 - 35€/mes</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Card de Contacto -->
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="h5 mb-3">¿Necesitas más información?</h4>
                            <p class="text-muted mb-4">Contacta con el coordinador de tu centro para resolver cualquier duda.</p>
                            <a href="#contacto" class="btn btn-outline-warning rounded-pill w-100">
                                <i class="bi bi-envelope me-2"></i>Contactar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Contenido Principal -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="mb-4">Sobre el Servicio</h2>
                        <p class="lead text-muted">La Ludoteca de Tardes es un servicio diseñado para las familias que necesitan ampliar el horario escolar hasta las 17:00h, ofreciendo un espacio seguro y educativo para los niños.</p>
                        
                        <!-- Características -->
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                            <i class="bi bi-backpack text-warning h4 mb-0"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="h5 mb-2">Actividades Lúdicas</h4>
                                        <p class="text-muted mb-0">Juegos educativos y actividades recreativas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                                            <i class="bi bi-people text-warning h4 mb-0"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h4 class="h5 mb-2">Personal Cualificado</h4>
                                        <p class="text-muted mb-0">Monitores con experiencia</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA para Inscripción -->
                        <div class="text-center mt-5">
                            <h3 class="h4 mb-4">¿Listo para inscribir a tu hijo?</h3>
                            <a href="ludoteca/tardes.php" class="btn btn-warning btn-lg rounded-pill px-5 py-3">
                                <i class="bi bi-pencil-square me-2"></i>
                                Ir al formulario de inscripción
                            </a>
                            <p class="text-muted mt-3">
                                <small>
                                    <i class="bi bi-info-circle me-1"></i>
                                    Serás redirigido a nuestro sistema de inscripciones
                                </small>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
