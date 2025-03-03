<?php 
require_once 'config.php';
$pagina = 'extraescolares';
$titulo = 'Actividades Extraescolares - ' . SITE_NAME;
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="background-image: url('<?php echo IMAGES_URL; ?>background/extraescolares.jpg');">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="col-lg-6">
            <div class="p-4 rounded-3 blur-bg">
                <span class="badge bg-info mb-3 px-3 py-2 rounded-pill fs-6">
                    <i class="bi bi-calendar2-check me-2"></i>Curso 2024-2025
                </span>
                <h1 class="display-4 fw-bold text-white mb-4">
                    Actividades Extraescolares
                    <div class="h3 fw-light mt-2">Desarrollo y Diversión</div>
                </h1>
                <p class="lead text-white opacity-90">
                    Actividades deportivas, artísticas y educativas para complementar 
                    la formación de tus hijos fuera del horario escolar.
                </p>
                <div class="d-flex gap-3">
                    <a href="#actividades" class="btn btn-info btn-lg rounded-pill px-4">
                        Ver Actividades <i class="bi bi-arrow-down-circle ms-2"></i>
                    </a>
                    <a href="#info" class="btn btn-outline-light btn-lg rounded-pill px-4">
                        Más Información
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Actividades Section -->
<section id="actividades" class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Nuestras Actividades</h2>
            <p class="lead text-muted">Descubre todas las actividades disponibles</p>
        </div>

        <div class="row g-4">
            <!-- Culturales y Artísticas -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-info text-white py-3">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-palette me-2"></i>
                            Actividades Culturales y Artísticas
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <i class="bi bi-mask text-info me-2"></i>
                                        <strong>Teatro:</strong><br>
                                        <small class="text-muted">Expresión y creatividad a través del juego dramático</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-book text-info me-2"></i>
                                        <strong>Cuentoterapia:</strong><br>
                                        <small class="text-muted">Desarrollo de valores mediante la narración</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-puzzle text-info me-2"></i>
                                        <strong>Multiactividad:</strong><br>
                                        <small class="text-muted">Juegos, manualidades y psicomotricidad</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-dice-6 text-info me-2"></i>
                                        <strong>Ajedrez:</strong><br>
                                        <small class="text-muted">Desarrollo cognitivo y estratégico</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-palette2 text-info me-2"></i>
                                        <strong>Dibujo y Pintura:</strong><br>
                                        <small class="text-muted">Estimulación de la creatividad artística</small>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <i class="bi bi-music-note-beamed text-info me-2"></i>
                                        <strong>Música:</strong><br>
                                        <small class="text-muted">Jardín Musical, Guitarra y Piano</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-robot text-info me-2"></i>
                                        <strong>Tecnología:</strong><br>
                                        <small class="text-muted">Taller de Drones y Robótica</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-translate text-info me-2"></i>
                                        <strong>Idiomas:</strong><br>
                                        <small class="text-muted">Baby English y Ludoteca Inglesa/Francesa</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-pencil-square text-info me-2"></i>
                                        <strong>Atención Educativa:</strong><br>
                                        <small class="text-muted">Apoyo y refuerzo escolar</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deportivas -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-info text-white py-3">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-trophy me-2"></i>
                            Actividades Deportivas
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <i class="bi bi-dribbble text-info me-2"></i>
                                        <strong>Predeporte:</strong><br>
                                        <small class="text-muted">Introducción al movimiento y juego en equipo</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-circle text-info me-2"></i>
                                        <strong>Mini-Tenis:</strong><br>
                                        <small class="text-muted">Desarrollo de reflejos y coordinación</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-trophy text-info me-2"></i>
                                        <strong>Fútbol:</strong><br>
                                        <small class="text-muted">Infantil y Primaria</small>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-3">
                                        <i class="bi bi-shield text-info me-2"></i>
                                        <strong>Artes Marciales:</strong><br>
                                        <small class="text-muted">Judo y Karate</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-people-fill text-info me-2"></i>
                                        <strong>Deportes de Equipo:</strong><br>
                                        <small class="text-muted">Baloncesto y Voleibol</small>
                                    </li>
                                    <li class="mb-3">
                                        <i class="bi bi-lightning text-info me-2"></i>
                                        <strong>Patinaje y Hockey:</strong><br>
                                        <small class="text-muted">Equilibrio y coordinación</small>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Colegios Section -->
<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-3">Nuestros Centros</h2>
            <p class="lead text-muted">Consulta las actividades disponibles en cada centro</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 g-4 justify-content-center">
            <div class="col">
                <a href="<?php echo BASE_URL; ?>ae/almadraba.php" class="card text-decoration-none border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-building text-info fs-2 me-3"></i>
                            <h3 class="h5 mb-0">CEIP Almadraba</h3>
                        </div>
                        <p class="text-muted mb-0">Ver actividades específicas y horarios del centro</p>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="<?php echo BASE_URL; ?>ae/costa-blanca.php" class="card text-decoration-none border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-building text-info fs-2 me-3"></i>
                            <h3 class="h5 mb-0">CEIP Costa Blanca</h3>
                        </div>
                        <p class="text-muted mb-0">Ver actividades específicas y horarios del centro</p>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="<?php echo BASE_URL; ?>ae/faro.php" class="card text-decoration-none border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-building text-info fs-2 me-3"></i>
                            <h3 class="h5 mb-0">CEIP Faro</h3>
                        </div>
                        <p class="text-muted mb-0">Ver actividades específicas y horarios del centro</p>
                    </div>
                </a>
            </div>

            <div class="col">
                <a href="<?php echo BASE_URL; ?>ae/voramar.php" class="card text-decoration-none border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-building text-info fs-2 me-3"></i>
                            <h3 class="h5 mb-0">CEIP Voramar</h3>
                        </div>
                        <p class="text-muted mb-0">Ver actividades específicas y horarios del centro</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Información y Precios -->
<section class="py-5" id="info">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="mb-4">Sobre las Actividades</h2>
                        <p class="lead text-muted">
                            Nuestras actividades extraescolares están diseñadas para complementar 
                            la formación académica y fomentar el desarrollo integral de los alumnos.
                        </p>
                        
                        <div class="row g-4 mt-4">
                            <div class="col-md-6">
                                <h4 class="h5"><i class="bi bi-clock-history text-info me-2"></i>Horarios</h4>
                                <p class="text-muted">De lunes a viernes, en horario de tarde después de las clases.</p>
                            </div>
                            <div class="col-md-6">
                                <h4 class="h5"><i class="bi bi-calendar-check text-info me-2"></i>Duración</h4>
                                <p class="text-muted">De octubre a mayo, siguiendo el calendario escolar.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 2rem;">
                    <div class="card-header bg-info text-white py-3">
                        <h3 class="h5 mb-0">Información de Contacto</h3>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted mb-4">Para más información sobre horarios y precios, contacta con el coordinador de tu centro.</p>
                        <a href="<?php echo BASE_URL; ?>contacto.php" class="btn btn-info rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>Ver Contactos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
