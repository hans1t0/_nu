<?php $pagina = 'ludoteca'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ludoteca tardes educap.es">
    <title>Ludoteca Tardes</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <link href="assets/css/styles.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link href="assets/img/favicon.png" rel="shortcut icon">

    <!-- Añadir al head los estilos CSS -->
    <style>
        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
        }
        .transition {
            transition: all .3s ease-in-out;
        }
        .bg-gradient {
            background-color: #4a90e2;
            color: white;
        }
        .opacity-90 {
            opacity: 0.9;
        }
    </style>
</head>

<body>
    <!-- Incluir el menú de navegación -->
    <?php include 'includes/nav.php'; ?>

    <!-- Hero Section con imagen de fondo -->
    <section class="position-relative vh-100 d-flex align-items-center" style="
        background: url('assets/img/hero-ludoteca.jpg') no-repeat center center;
        background-size: cover;
        margin-top: -76px; /* altura del navbar */
    ">
        <!-- Overlay oscuro -->
        <div class="position-absolute top-0 start-0 w-100 h-100" style="
            background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.4) 100%);
        "></div>

        <div class="container position-relative">
            <div class="row">
                <div class="col-lg-6">
                    <div class="p-4 rounded-3" style="backdrop-filter: blur(10px); background: rgba(255,255,255,0.1);">
                        <span class="badge bg-warning mb-3 px-3 py-2 rounded-pill fs-6">¡Inscripciones Abiertas!</span>
                        <h1 class="display-4 fw-bold text-white mb-4">
                            Ludoteca<br>Tardes
                            <div class="h3 fw-light mt-2"><?php echo date('Y')?> </div>
                        </h1>
                        <p class="lead mb-4 text-white opacity-90">
                            Actividades lúdicas y educativas para el desarrollo de tus hijos en un entorno seguro y divertido.
                        </p>
                        <div class="d-flex gap-3">
                            <a href="tardes.php" class="btn btn-warning btn-lg rounded-pill px-4 py-3">
                                Inscripción <i class="bi bi-arrow-right-circle ms-2"></i>
                            </a>
                            <a href="#info" class="btn btn-outline-light btn-lg rounded-pill px-4 py-3">
                                Más información <i class="bi bi-info-circle ms-2"></i>
                            </a>
                        </div>

                        <!-- Estadísticas/Features -->
                        <div class="row mt-5 g-4">
                            <div class="col-6">
                                <div class="d-flex align-items-center text-white">
                                    <div class="rounded-circle bg-warning p-3 me-3">
                                        <i class="bi bi-clock h4 mb-0"></i>
                                    </div>
                                    <div>
                                        <h4 class="h5 mb-0">Horario Flexible</h4>
                                        <small class="opacity-75">Hasta las 17:00</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center text-white">
                                    <div class="rounded-circle bg-success p-3 me-3">
                                        <i class="bi bi-shield-check h4 mb-0"></i>
                                    </div>
                                    <div>
                                        <h4 class="h5 mb-0">Personal Cualificado</h4>
                                        <small class="opacity-75">Supervisión constante</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <a href="#info" class="position-absolute bottom-0 start-50 translate-middle-x mb-4 text-white">
            <i class="bi bi-chevron-double-down h2 mb-0"></i>
        </a>
    </section>

    <!-- Características con iconos -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-white shadow-sm h-100 hover-shadow transition">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-clock h3 text-primary mb-0"></i>
                            </div>
                            <h3 class="h5 mb-3">Horario Flexible</h3>
                            <p class="text-muted mb-0">Adaptamos nuestros horarios a tus necesidades familiares</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-white shadow-sm h-100 hover-shadow transition">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-people h3 text-warning mb-0"></i>
                            </div>
                            <h3 class="h5 mb-3">Personal Cualificado</h3>
                            <p class="text-muted mb-0">Equipo profesional con amplia experiencia educativa</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-white shadow-sm h-100 hover-shadow transition">
                        <div class="card-body text-center p-4">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                                <i class="bi bi-heart h3 text-success mb-0"></i>
                            </div>
                            <h3 class="h5 mb-3">Actividades Divertidas</h3>
                            <p class="text-muted mb-0">Juegos y actividades educativas supervisadas</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Información del Servicio -->
    <section class="py-5" id="info">
        <div class="container">
            <div class="row">
                <!-- Sidebar - Información General -->
                <div class="col-lg-3">
                    <div class="card border-0 shadow-sm sticky-top" style="top: 2rem;">
                        <div class="card-header bg-primary text-white py-3">
                            <h3 class="h5 mb-0 text-center">
                                <i class="bi bi-info-circle me-2"></i>
                                Información General
                            </h3>
                        </div>
                        <div class="card-body py-4">
                            <ul class="list-unstyled">
                                <li class="mb-4 d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-calendar-check text-primary h5 mb-0"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Periodo</strong>
                                        <span class="text-muted">Junio - Septiembre</span>
                                    </div>
                                </li>
                                <li class="mb-4 d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-mortarboard text-primary h5 mb-0"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Edades</strong>
                                        <span class="text-muted">3 años - 6º EP</span>
                                    </div>
                                </li>
                                <li class="mb-4 d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-people text-primary h5 mb-0"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Plazas</strong>
                                        <span class="text-muted">Máximo permitido</span>
                                    </div>
                                </li>
                                <li class="mb-4 d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                                        <i class="bi bi-telephone text-primary h5 mb-0"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block mb-1">Contacto</strong>
                                        <span class="text-muted">Coordinador del centro</span>
                                    </div>
                                </li>
                            </ul>
                            <div class="text-center mt-4">
                                <a href="tardes.php" class="btn btn-primary btn-lg rounded-pill w-100">
                                    <i class="bi bi-pencil-square me-2"></i>
                                    Inscripción Online
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna Principal -->
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h2 class="text-primary mb-4">Sobre el Servicio</h2>
                            <p>El servicio de Ludoteca de Tardes lo ofertamos para los meses de septiembre y junio. Se trata de una ampliación horaria para el cuidado de alumn@s hasta las 16:00 ó 17:00 h, según necesitad familiar.</p>
                            <p>Este servicio complementario tiene como objetivo facilitar la conciliación familiar, lo consideramos como un complemento muy importante para el buen desarrollo del funcionamiento del centro.</p>
                            <p>Durante el periodo de la ludoteca l@s alumn@s realizarán actividades no dirigidas, pero supervisadas.Para cualquier consulta podéis contactar con el coordinador de Educap en tu centro.</p>
                            
                            <div class="row g-4 mt-4">
                                <div class="col-md-6">
                                    <div class="card h-100 border-warning">
                                        <div class="card-header bg-warning text-white">
                                            <h3 class="h5 mb-0">Horarios</h3>
                                        </div>
                                        <img src="assets/img/tardes.jpg" class="card-img-top" alt="Horarios Ludoteca">
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <i class="bi bi-clock text-warning me-2"></i>
                                                    15:00 - 16:00
                                                </li>
                                                <li>
                                                    <i class="bi bi-clock text-warning me-2"></i>
                                                    15:00 - 17:00
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card h-100 border-success">
                                        <div class="card-header bg-success text-white">
                                            <h3 class="h5 mb-0">Precios</h3>
                                        </div>
                                        <img src="assets/img/precio.jpg" class="card-img-top" alt="Precios Ludoteca">
                                        <div class="card-body">
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <i class="bi bi-tag text-success me-2"></i>
                                                    Hasta 16:00 - 25€/mes
                                                </li>
                                                <li>
                                                    <i class="bi bi-tag text-success me-2"></i>
                                                    Hasta 17:00 - 35€/mes
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">© 2024 Educap Servicios Educativos</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="https://www.facebook.com/EducapServeis/" class="text-white me-3">
                        <i class="bi bi-facebook"></i>
                    </a>
                    <a href="#" class="text-white">
                        <i class="bi bi-instagram"></i>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>