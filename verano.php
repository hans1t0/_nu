<?php 
require_once 'config.php';
$pagina = 'verano';
$titulo = 'Escuela de Verano - ' . SITE_NAME;
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="background-image: url('<?php echo IMAGES_URL; ?>background/img-2.jpg');">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-6">
                <div class="p-4 rounded-3 blur-bg">
                    <span class="badge bg-success mb-3 px-3 py-2 rounded-pill fs-6">
                        <i class="bi bi-clock me-2"></i>9:00 - 14:00
                    </span>
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Escuela de Verano
                        <div class="h3 fw-light mt-2">Verano <?php echo date('Y'); ?></div>
                    </h1>
                    <p class="lead text-white opacity-90">
                        Programa completo de actividades educativas y recreativas. 
                        Diversión y aprendizaje durante todo el verano.
                    </p>
                    <div class="d-flex gap-3">
                        <a href="#inscripcion" class="btn btn-success btn-lg rounded-pill px-4">
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

<!-- Actividades Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-6 fw-bold mb-3">Nuestras Actividades</h2>
            <p class="lead text-muted">Un verano lleno de diversión y aprendizaje</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4 text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-palette h3 text-success"></i>
                        </div>
                        <h4 class="h5 mb-3">Talleres Creativos</h4>
                        <p class="text-muted mb-0">Arte, manualidades y expresión creativa</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4 text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-water h3 text-success"></i>
                        </div>
                        <h4 class="h5 mb-3">Juegos de Agua</h4>
                        <p class="text-muted mb-0">Actividades refrescantes y divertidas</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4 text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-trophy h3 text-success"></i>
                        </div>
                        <h4 class="h5 mb-3">Deportes</h4>
                        <p class="text-muted mb-0">Actividades físicas y juegos en equipo</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-shadow h-100">
                    <div class="card-body p-4 text-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                            <i class="bi bi-book h3 text-success"></i>
                        </div>
                        <h4 class="h5 mb-3">Talleres Educativos</h4>
                        <p class="text-muted mb-0">Aprendizaje divertido y dinámico</p>
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
            <!-- Columna 1: Periodos y Lugar -->
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white p-4">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-calendar-week me-2"></i>
                            Periodos y Ubicación
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-4">
                            <li class="mb-4">
                                <div>
                                    <strong class="d-block mb-2 text-success">
                                        <i class="bi bi-calendar3-week me-2"></i>Semanas Disponibles
                                    </strong>
                                    <div class="ps-3 border-start border-success">
                                        <p class="mb-2"><small class="text-muted">1ª Semana: del 1 al 6 de Julio</small></p>
                                        <p class="mb-2"><small class="text-muted">2ª Semana: del 7 al 13 de Julio</small></p>
                                        <p class="mb-2"><small class="text-muted">3ª Semana: del 14 al 20 de Julio</small></p>
                                        <p class="mb-2"><small class="text-muted">4ª Semana: del 21 al 27 de Julio</small></p>
                                        <p class="mb-2"><small class="text-muted">5ª Semana: del 28 al 31 de Julio</small></p>
                                    </div>
                                </div>
                            </li>
                            
                            <li>
                                <strong class="d-block mb-2 text-success">
                                    <i class="bi bi-geo-alt me-2"></i>Información del Centro
                                </strong>
                                <div class="ps-3 border-start border-success">
                                    <p class="mb-2"><strong>Ubicación:</strong><br>
                                    <span class="text-muted">Colegios zona Playa San Juan</span></p>
                                    <p class="mb-0"><strong>Edades:</strong><br>
                                    <span class="text-muted">de 3 a 12 años</span></p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Columna 2: Horarios -->
            <div class="col-lg-6">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white p-4">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-clock me-2"></i>
                            Horarios Detallados
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="bi bi-sunrise h4 text-success"></i>
                                </div>
                                <div>
                                    <h4 class="h6 mb-1">Guardería Matinal</h4>
                                    <p class="text-muted small mb-0">Sin servicio de desayuno</p>
                                </div>
                            </div>
                            <div class="ps-5">
                                <p class="mb-1"><small>Opción 1: 7:30 - 9:00</small></p>
                                <p class="mb-1"><small>Opción 2: 8:00 - 9:00</small></p>
                                <p class="mb-3"><small>Opción 3: 8:30 - 9:00</small></p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="bi bi-sun h4 text-success"></i>
                                </div>
                                <div>
                                    <h4 class="h6 mb-1">Talleres</h4>
                                    <p class="text-muted small mb-0">Actividades principales</p>
                                </div>
                            </div>
                            <p class="ps-5 mb-0">9:00 - 14:00</p>
                        </div>

                        <div>
                            <div class="d-flex align-items-center mb-3">
                                <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                    <i class="bi bi-cup-hot h4 text-success"></i>
                                </div>
                                <div>
                                    <h4 class="h6 mb-1">Comedor</h4>
                                    <p class="text-muted small mb-0">Servicio opcional</p>
                                </div>
                            </div>
                            <p class="ps-5 mb-0">14:00 - 15:30</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Columna 3: Precios y Descuentos -->
            <div class="col-lg-12"> <!-- Cambiado a col-lg-12 para tabla completa -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white p-4">
                        <h3 class="h5 mb-0">
                            <i class="bi bi-cash-coin me-2"></i>
                            Tarifas Escuela de Verano <?php echo date('Y'); ?>
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <!-- Tabla de Precios -->
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Servicio</th>
                                        <th scope="col" colspan="2">UNA SEMANA</th>
                                        <th scope="col" colspan="2">DOS SEMANAS</th>
                                        <th scope="col" colspan="2">TRES SEMANAS</th>
                                        <th scope="col" colspan="2">CUATRO SEMANAS</th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th>SOCIOS AMPA</th>
                                        <th>NO SOCIOS</th>
                                        <th>SOCIOS AMPA</th>
                                        <th>NO SOCIOS</th>
                                        <th>SOCIOS AMPA</th>
                                        <th>NO SOCIOS</th>
                                        <th>SOCIOS AMPA</th>
                                        <th>NO SOCIOS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Guardería (7:30 - 9:00)</strong></td>
                                        <td>15€</td>
                                        <td>20€</td>
                                        <td>25€</td>
                                        <td>30€</td>
                                        <td>35€</td>
                                        <td>40€</td>
                                        <td>45€</td>
                                        <td>50€</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Guardería (8:00 - 9:00)</strong></td>
                                        <td>10€</td>
                                        <td>15€</td>
                                        <td>20€</td>
                                        <td>25€</td>
                                        <td>30€</td>
                                        <td>35€</td>
                                        <td>40€</td>
                                        <td>45€</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Guardería (8:30 - 9:00)</strong></td>
                                        <td>10€</td>
                                        <td>15€</td>
                                        <td>15€</td>
                                        <td>20€</td>
                                        <td>20€</td>
                                        <td>25€</td>
                                        <td>25€</td>
                                        <td>30€</td>
                                    </tr>
                                    <tr class="table-success">
                                        <td><strong>Talleres (9:00 - 14:00)</strong></td>
                                        <td>120€</td>
                                        <td>125€</td>
                                        <td>200€</td>
                                        <td>210€</td>
                                        <td>255€</td>
                                        <td>270€</td>
                                        <td>300€</td>
                                        <td>320€</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Comedor (14:00 - 15:30)</strong></td>
                                        <td>40€</td>
                                        <td>45€</td>
                                        <td>80€</td>
                                        <td>85€</td>
                                        <td>120€</td>
                                        <td>125€</td>
                                        <td>160€</td>
                                        <td>165€</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Días Sueltos -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-white">
                                        <h5 class="mb-0">Días Sueltos</h5>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Servicio</th>
                                                    <th>AMPA</th>
                                                    <th>NO AMPA</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Guardería (7:30 - 9:00)</td>
                                                    <td>5€</td>
                                                    <td>7€</td>
                                                </tr>
                                                <tr>
                                                    <td>Comedor (14:00 - 15:30)</td>
                                                    <td>8€</td>
                                                    <td>10€</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">Descuentos por Herman@s</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                2do/a hij@
                                                <span class="badge bg-success rounded-pill">5€ por semana</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                3er/a hij@
                                                <span class="badge bg-success rounded-pill">10€ por semana</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                4to/a hij@
                                                <span class="badge bg-success rounded-pill">40€ por semana</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notas Importantes -->
                        <div class="alert alert-info mt-4">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            Los descuentos por herman@s se aplican solo en el servicio de talleres.
                        </div>
                    </div>
                </div>

                <!-- Botón de Inscripción -->
                <div class="text-center">
                    <a href="/inscripciones/verano" class="btn btn-success btn-lg rounded-pill px-4">
                        <i class="bi bi-pencil-square me-2"></i>
                        Inscripción Online
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
</body>
</html>
