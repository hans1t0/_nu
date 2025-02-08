<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Ludoteca Tardes 2024</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Formulario de inscripción para la Ludoteca Tardes 2024. Servicio de atención vespertina para alumnos de infantil y primaria. Horarios flexibles y actividades lúdicas disponibles.">
    <meta name="keywords" content="ludoteca tardes, atención vespertina, conciliación familiar, inscripción ludoteca, actividades extraescolares, CEIP Almadraba, CEIP Costa Blanca, CEIP Faro, CEIP Voramar">
    <meta name="author" content="Ayuntamiento">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ejemplo.com/ludoteca-tardes">
    <meta property="og:title" content="Inscripción Ludoteca Tardes 2024">
    <meta property="og:description" content="Inscribe a tus hijos en el servicio de ludoteca de tardes. Horarios hasta las 17:30h con actividades lúdicas.">
    <meta property="og:image" content="https://ejemplo.com/img/guarderia-social.jpg">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Inscripción Ludoteca Tardes 2024">
    <meta name="twitter:description" content="Inscribe a tus hijos en el servicio de ludoteca de tardes. Horarios hasta las 17:00h con actividades lúdicas.">
    <meta name="twitter:image" content="https://ejemplo.com/img/guarderia-social.jpg">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://ejemplo.com/ludoteca-tardes">
    
    <!-- Bootstrap y estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css"> 
</head>
<body>
    <main class="container py-5">
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php 
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']); 
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']); 
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">
                    <i class="bi bi-moon-fill text-primary me-2" aria-hidden="true"></i>
                    <span>Ludoteca Tardes 2024</span>
                </h1>

                <form method="POST" action="validar.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <!-- Datos del Responsable -->
                    <section class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-person-circle" aria-hidden="true"></i>
                                <span>Datos del Responsable</span>
                            </h2>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre completo</label>
                                    <input type="text" name="nombre" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">DNI/NIE</label>
                                    <input type="text" name="dni" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Teléfono principal</label>
                                    <input type="tel" name="telefono" class="form-control" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Teléfono secundario</label>
                                    <input type="tel" name="telefono2" class="form-control">
                                </div>
                                
                                <!-- Forma de pago con información dinámica -->
                                <div class="col-md-12">
                                    <label class="form-label">Forma de Pago</label>
                                    <select name="forma_pago" class="form-select" id="formaPago" required>
                                        <option value="">Seleccione forma de pago</option>
                                        <option value="domiciliacion">Domiciliación bancaria</option>
                                        <option value="transferencia">Transferencia bancaria</option>
                                        <option value="coordinador">Pago al coordinador</option>
                                    </select>
                                </div>

                                <!-- Campo IBAN (visible solo para domiciliación) -->
                                <div class="col-12" id="campoDomiciliacion" style="display: none;">
                                    <label class="form-label">Número de cuenta (IBAN)</label>
                                    <input type="text" name="iban" id="ibanInput" class="form-control" 
                                           pattern="ES\d{22}" 
                                           placeholder="ES0000000000000000000000"
                                           title="Formato IBAN: ES + 22 números">
                                    <div class="form-text">Formato: ES + 22 números (sin espacios)</div>
                                </div>

                                <!-- Información condicional de pago -->
                                <div class="col-12" id="infoPagoTransferencia" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Modalidad de pago por transferencia</h5>
                                        <hr>
                                        <p class="mb-1"><strong>Envío de justificante:</strong> inscripciones@educap.es</p>
                                        <p class="mb-1"><strong>Titular:</strong> EDUCAP Serveis d'Oci S.L.</p>
                                        <p class="mb-1"><strong>IBAN:</strong> ES30 3058 2519 4927 2000 6473</p>
                                        <p class="mb-1"><strong>Concepto:</strong> LT+Nombre alumno+Colegio</p>
                                    </div>
                                </div>

                                <div class="col-12" id="infoPagoCoordinador" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5 class="alert-heading"><i class="bi bi-info-circle"></i> Pago al Coordinador</h5>
                                        <hr>
                                        <p>El coordinador se pondrá en contacto para gestionar el pago.</p>
                                        <p class="mb-1"><strong>Teléfono:</strong> 666 777 888</p>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="3" placeholder="Indique cualquier información adicional que considere relevante"></textarea>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Datos de los Hijos -->
                    <section class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h2 class="h5 mb-0">
                                <i class="bi bi-people-fill" aria-hidden="true"></i>
                                <span>Datos de los Hijos</span>
                            </h2>
                        </div>
                        <div class="card-body">
                            <div id="hijos-container">
                                <!-- Aquí se agregarán los formularios de hijos -->
                            </div>
                            <button type="button" class="btn btn-outline-primary" onclick="agregarHijo()">
                                <i class="bi bi-plus-circle"></i> Agregar Hijo
                            </button>
                        </div>
                    </section>

                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        <span>Enviar Solicitud</span>
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Schema.org markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Ludoteca Tardes 2024",
        "description": "Servicio de atención vespertina para alumnos de infantil y primaria con actividades lúdicas",
        "provider": {
            "@type": "Organization",
            "name": "Ayuntamiento"
        },
        "areaServed": {
            "@type": "City",
            "name": "Localidad"
        },
        "serviceType": "Atención Vespertina Escolar",
        "offers": {
            "@type": "Offer",
            "availability": "https://schema.org/InStock",
            "availabilityStarts": "2024-01-01",
            "availabilityEnds": "2024-12-31"
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/form_tardes.js"></script>
    <script>
    document.getElementById('formaPago').addEventListener('change', function() {
        const infoTransferencia = document.getElementById('infoPagoTransferencia');
        const infoCoordinador = document.getElementById('infoPagoCoordinador');
        const campoDomiciliacion = document.getElementById('campoDomiciliacion');
        const ibanInput = document.getElementById('ibanInput');
        
        // Ocultar todos los elementos primero
        infoTransferencia.style.display = 'none';
        infoCoordinador.style.display = 'none';
        campoDomiciliacion.style.display = 'none';
        ibanInput.required = false;
        
        // Mostrar según selección
        switch(this.value) {
            case 'transferencia':
                infoTransferencia.style.display = 'block';
                break;
            case 'coordinador':
                infoCoordinador.style.display = 'block';
                break;
            case 'domiciliacion':
                campoDomiciliacion.style.display = 'block';
                ibanInput.required = true;
                break;
        }
    });
    </script>
</body>
</html>