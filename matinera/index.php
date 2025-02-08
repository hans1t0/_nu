<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Guardería Matinal 2024 - Servicio de Atención Temprana</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Formulario de inscripción para la Guardería Matinal 2024. Servicio de atención temprana para alumnos de infantil y primaria. Horarios flexibles y servicio de desayuno disponible.">
    <meta name="keywords" content="guardería matinal, atención temprana, conciliación familiar, inscripción guardería, desayuno escolar, CEIP Almadraba, CEIP Costa Blanca, CEIP Faro, CEIP Voramar">
    <meta name="author" content="Ayuntamiento">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ejemplo.com/guarderia-matinal">
    <meta property="og:title" content="Inscripción Guardería Matinal 2024">
    <meta property="og:description" content="Inscribe a tus hijos en el servicio de guardería matinal. Horarios desde las 7:30h con opción de desayuno.">
    <meta property="og:image" content="https://ejemplo.com/img/guarderia-social.jpg">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Inscripción Guardería Matinal 2024">
    <meta name="twitter:description" content="Inscribe a tus hijos en el servicio de guardería matinal. Horarios desde las 7:30h con opción de desayuno.">
    <meta name="twitter:image" content="https://ejemplo.com/img/guarderia-social.jpg">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://ejemplo.com/guarderia-matinal">
    
    <!-- Bootstrap y estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css"> 
</head>
<body>
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">
                    <i class="bi bi-sun-fill text-warning me-2" aria-hidden="true"></i>
                    <span>Guardería Matinal 2024</span>
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
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" name="telefono" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Forma de Pago</label>
                                    <select name="forma_pago" class="form-control" id="forma_pago" required>
                                        <option value="">Seleccione forma de pago</option>
                                        <option value="DOMICILIACION">Domiciliación bancaria</option>
                                        <option value="TRANSFERENCIA">Transferencia bancaria</option>
                                        <option value="COORDINADOR">Pago al coordinador</option>
                                    </select>
                                </div>

                                <!-- Campos para Domiciliación -->
                                <div class="col-12 payment-info" id="domiciliacion-info" style="display:none;">
                                    <div class="alert alert-info">
                                        <label class="form-label">Número de cuenta (IBAN)</label>
                                        <input type="text" name="iban" class="form-control" pattern="ES[0-9]{2}[0-9]{20}" placeholder="ES91 2100 0418 4502 0005 1332">
                                    </div>
                                </div>

                                <!-- Información para Transferencia -->
                                <div class="col-12 payment-info" id="transferencia-info" style="display:none;">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Modalidad de pago por transferencia</h6>
                                        <p>Envío de justificante: <strong>inscripciones@educap.es</strong></p>
                                        <hr>
                                        <p class="mb-0">Titular: EDUCAP Serveis d'Oci S.L.</p>
                                        <p class="mb-0">IBAN: ES30 3058 2519 4927 2000 6473</p>
                                        <p class="mb-0">Concepto: GM + Nombre alumno + Colegio</p>
                                    </div>
                                </div>

                                <!-- Información para Coordinador -->
                                <div class="col-12 payment-info" id="coordinador-info" style="display:none;">
                                    <div class="alert alert-info">
                                        <h6 class="alert-heading">Pago al Coordinador</h6>
                                        <p>El coordinador se pondrá en contacto para gestionar el pago.</p>
                                        <p class="mb-0">Teléfono: 666 777 888</p>
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
        "name": "Guardería Matinal 2024",
        "description": "Servicio de atención temprana para alumnos de infantil y primaria con opción de desayuno",
        "provider": {
            "@type": "Organization",
            "name": "Ayuntamiento"
        },
        "areaServed": {
            "@type": "City",
            "name": "Localidad"
        },
        "serviceType": "Atención Temprana Escolar",
        "offers": {
            "@type": "Offer",
            "availability": "https://schema.org/InStock",
            "availabilityStarts": "2024-01-01",
            "availabilityEnds": "2024-12-31"
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/form.js"></script>
    <script>
    document.getElementById('forma_pago').addEventListener('change', function() {
        // Ocultar todos los divs de información de pago
        document.querySelectorAll('.payment-info').forEach(div => div.style.display = 'none');
        
        // Mostrar el div correspondiente según la selección
        if (this.value) {
            document.getElementById(this.value.toLowerCase() + '-info').style.display = 'block';
        }
    });
    </script>
</body>
</html>