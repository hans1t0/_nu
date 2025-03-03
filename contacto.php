<?php 
require_once 'config.php';
$pagina = 'contacto';
$titulo = 'Contacto - ' . SITE_NAME;
include 'includes/header.php';
include 'includes/nav.php';
?>

<!-- Hero Section -->
<section class="hero-section d-flex align-items-center" style="
    background-image: url('<?php echo IMAGES_URL; ?>background/contacto-verano.jpg');
    min-height: 50vh;
">
    <div class="hero-overlay"></div>
    <div class="container position-relative">
        <div class="row">
            <div class="col-lg-8">
                <div class="p-4 rounded-3 blur-bg">
                    <h1 class="display-4 fw-bold text-white mb-3">
                        Contacta con tu Centro
                        <div class="h3 fw-light mt-2">Escuela de Verano 2025</div>
                    </h1>
                    <p class="lead text-white opacity-90">
                        Ponte en contacto con el coordinador de tu centro para resolver cualquier duda 
                        sobre la Escuela de Verano.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contactos Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- La Almadraba -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="h5 mb-0">La Almadraba</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bi bi-person h4 text-success mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Coordinadora</h4>
                                <p class="mb-0 text-success">Gema</p>
                            </div>
                        </div>
                        <a href="tel:647729651" class="btn btn-outline-success rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>647 729 651
                        </a>
                    </div>
                </div>
            </div>

            <!-- La Condomina -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="h5 mb-0">La Condomina</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bi bi-person h4 text-success mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Coordinador</h4>
                                <p class="mb-0 text-success">Ricardo</p>
                            </div>
                        </div>
                        <a href="tel:695385564" class="btn btn-outline-success rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>695 385 564
                        </a>
                    </div>
                </div>
            </div>

            <!-- Costa Blanca -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="h5 mb-0">Costa Blanca</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bi bi-person h4 text-success mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Coordinadora</h4>
                                <p class="mb-0 text-success">Ana Rosa</p>
                            </div>
                        </div>
                        <a href="tel:658988751" class="btn btn-outline-success rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>658 988 751
                        </a>
                    </div>
                </div>
            </div>

            <!-- El Faro -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="h5 mb-0">El Faro</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bi bi-person h4 text-success mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Coordinador</h4>
                                <p class="mb-0 text-success">Ricardo</p>
                            </div>
                        </div>
                        <a href="tel:695385564" class="btn btn-outline-success rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>695 385 564
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mediterráneo -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="h5 mb-0">Mediterráneo</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bi bi-person h4 text-success mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Coordinador</h4>
                                <p class="mb-0 text-success">Marcos</p>
                            </div>
                        </div>
                        <a href="tel:616447848" class="btn btn-outline-success rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>616 447 848
                        </a>
                    </div>
                </div>
            </div>

            <!-- Voramar -->
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100 hover-shadow">
                    <div class="card-header bg-success text-white py-3">
                        <h3 class="h5 mb-0">Voramar</h3>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                                <i class="bi bi-person h4 text-success mb-0"></i>
                            </div>
                            <div>
                                <h4 class="h6 mb-1">Coordinador</h4>
                                <p class="mb-0 text-success">Manolo</p>
                            </div>
                        </div>
                        <a href="tel:695385563" class="btn btn-outline-success rounded-pill w-100">
                            <i class="bi bi-telephone me-2"></i>695 385 563
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
