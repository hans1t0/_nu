<?php
require_once 'config.php';
$pagina = 'inicio';
$titulo = 'Servicios Educativos - ' . SITE_NAME;
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section Mejorado -->
<section class="hero-section d-flex align-items-center" style="
    background-image: url('<?php echo IMAGES_URL; ?>background/background-img-1.jpg');
    min-height: 100vh;
">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="display-3 fw-bold text-white mb-4 animation-fade-in">
                    Educación y Cuidado<br>
                    <span class="text-warning">para tus hijos</span>
                </h1>
                <p class="lead text-white opacity-90 mb-5">
                    Servicios educativos diseñados para facilitar la conciliación familiar,
                    ofreciendo un entorno seguro y enriquecedor para el desarrollo de tus hijos.
                </p>
                <div class="d-flex gap-3">
                    <a href="#servicios" class="btn btn-warning btn-lg rounded-pill px-4 py-3">
                        Explorar Servicios <i class="bi bi-arrow-down-circle ms-2"></i>
                    </a>
                    <a href="#contacto" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                        Contáctanos <i class="bi bi-envelope ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Scroll indicator -->
    <a href="#servicios" class="position-absolute bottom-0 start-50 translate-middle-x mb-4 text-white animation-bounce">
        <i class="bi bi-chevron-double-down h2 mb-0"></i>
    </a>
</section>

<!-- Servicios Section -->
<section id="servicios" class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Nuestros Servicios</h2>
            <p class="lead text-muted">Descubre todas las opciones que tenemos para tu familia</p>
        </div>
        
        <div class="row g-4">
            <!-- Guardería Matinal -->
            <div class="col-md-6 col-lg-3">
                <div class="card service-card h-100">
                    <div class="position-relative card-img-wrapper">
                        <img src="<?php echo IMAGES_URL; ?>matinera.jpg" class="card-img-top service-img" alt="Guardería Matinal">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-primary rounded-pill px-3 py-2">
                                7:30 - 9:00
                            </span>
                        </div>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-sunrise h3 text-primary"></i>
                        </div>
                        <h3 class="h4 mb-3">Guardería Matinal</h3>
                        <p class="text-muted mb-4">Servicio de atención temprana con desayuno incluido.</p>
                        <a href="<?php echo BASE_URL; ?>matinera.php" class="btn btn-primary rounded-pill px-4">
                            Más información <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Ludoteca Tardes -->
            <div class="col-md-6 col-lg-3">
                <div class="card service-card h-100">
                    <div class="position-relative card-img-wrapper">
                        <img src="<?php echo IMAGES_URL; ?>tardes.jpg" class="card-img-top service-img" alt="Ludoteca Tardes">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning rounded-pill px-3 py-2">
                                15:00 - 17:00
                            </span>
                        </div>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-backpack h3 text-warning"></i>
                        </div>
                        <h3 class="h4 mb-3">Ludoteca Tardes</h3>
                        <p class="text-muted mb-4">Actividades lúdicas y educativas supervisadas.</p>
                        <a href="<?php echo BASE_URL; ?>ludoteca.php" class="btn btn-warning rounded-pill px-4">
                            Más información <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Escuela de Verano -->
            <div class="col-md-6 col-lg-3">
                <div class="card service-card h-100">
                    <div class="position-relative card-img-wrapper">
                        <img src="<?php echo IMAGES_URL; ?>verano.jpg" class="card-img-top service-img" alt="Escuela de Verano">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-success rounded-pill px-3 py-2">
                                9:00 - 14:00
                            </span>
                        </div>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-sun h3 text-success"></i>
                        </div>
                        <h3 class="h4 mb-3">Escuela de Verano</h3>
                        <p class="text-muted mb-4">Programa completo de actividades estivales.</p>
                        <a href="<?php echo BASE_URL; ?>verano.php" class="btn btn-success rounded-pill px-4">
                            Más información <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Actividades Extraescolares -->
            <div class="col-md-6 col-lg-3">
                <div class="card service-card h-100">
                    <div class="position-relative card-img-wrapper">
                        <img src="<?php echo IMAGES_URL; ?>extraescolares.jpg" class="card-img-top service-img" alt="Actividades Extraescolares">
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-info rounded-pill px-3 py-2">
                                Horario Tarde
                            </span>
                        </div>
                    </div>
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-journal-check h3 text-info"></i>
                        </div>
                        <h3 class="h4 mb-3">Extraescolares</h3>
                        <p class="text-muted mb-4">Actividades deportivas, artísticas y educativas.</p>
                        <a href="<?php echo BASE_URL; ?>extraescolares.php" class="btn btn-info rounded-pill px-4">
                            Más información <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Características Mejoradas -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-4 mb-lg-0">
                <h2 class="display-6 fw-bold mb-4">¿Por qué elegir nuestros servicios?</h2>
                <p class="lead text-muted mb-4">
                    Más de 10 años de experiencia nos avalan en el cuidado y educación de niños,
                    proporcionando un entorno seguro y estimulante para su desarrollo.
                </p>
                <a href="#contacto" class="btn btn-primary rounded-pill px-4">
                    Solicitar información <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="col-lg-7">
                <div class="row g-4">
                    <!-- Tarjeta 1 -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-shadow">
                            <div class="card-body p-4">
                                <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-shield-check h3 text-primary mb-0"></i>
                                </div>
                                <h4 class="h5">Personal Cualificado</h4>
                                <p class="text-muted mb-0">Equipo profesional con amplia experiencia en educación infantil.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Tarjeta 2 -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-shadow">
                            <div class="card-body p-4">
                                <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-clock h3 text-warning mb-0"></i>
                                </div>
                                <h4 class="h5">Horarios Flexibles</h4>
                                <p class="text-muted mb-0">Adaptados a las necesidades de cada familia.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Tarjeta 3 -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-shadow">
                            <div class="card-body p-4">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-heart h3 text-success mb-0"></i>
                                </div>
                                <h4 class="h5">Atención Personalizada</h4>
                                <p class="text-muted mb-0">Cuidado individualizado para cada niño.</p>
                            </div>
                        </div>
                    </div>
                    <!-- Tarjeta 4 -->
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm hover-shadow">
                            <div class="card-body p-4">
                                <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-block mb-3">
                                    <i class="bi bi-puzzle h3 text-info mb-0"></i>
                                </div>
                                <h4 class="h5">Actividades Educativas</h4>
                                <p class="text-muted mb-0">Aprendizaje mediante el juego y actividades dirigidas.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-primary text-white">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="display-6 fw-bold mb-3">¿Necesitas más información?</h2>
                <p class="lead opacity-90 mb-0">
                    Contacta con nosotros y te ayudaremos a encontrar el mejor servicio para tu familia.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="#contacto" class="btn btn-outline-light btn-lg rounded-pill px-4">
                    Contactar ahora <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>

<style>
.animation-bounce {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0) translateX(-50%);
    }
    40% {
        transform: translateY(-30px) translateX(-50%);
    }
    60% {
        transform: translateY(-15px) translateX(-50%);
    }
}

.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}

.card-img-wrapper {
    height: 200px;
    overflow: hidden;
    border-radius: 15px 15px 0 0;
}

.service-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    transition: transform 0.3s ease;
}

.service-card:hover .service-img {
    transform: scale(1.05);
}
</style>
