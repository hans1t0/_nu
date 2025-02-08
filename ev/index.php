<?php
session_start();
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="es" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Escuela de Verano 2024</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Formulario de inscripción para la Escuela de Verano 2024. Actividades recreativas, deportivas y educativas para niños durante el verano.">
    <meta name="keywords" content="escuela de verano, actividades verano, campamento urbano, inscripción verano, actividades niños, CEIP Almadraba, CEIP Costa Blanca, CEIP Faro, CEIP Voramar">
    <meta name="author" content="Ayuntamiento">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ejemplo.com/escuela-verano">
    <meta property="og:title" content="Inscripción Escuela de Verano 2024">
    <meta property="og:description" content="Actividades de verano para niños. Deportes, talleres, juegos y mucho más.">
    <meta property="og:image" content="https://ejemplo.com/img/verano-social.jpg">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Inscripción Escuela de Verano 2024">
    <meta name="twitter:description" content="Actividades de verano para niños. Deportes, talleres, juegos y mucho más.">
    <meta name="twitter:image" content="https://ejemplo.com/img/verano-social.jpg">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://ejemplo.com/escuela-verano">
    
    <!-- Bootstrap y estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/form_ev.css">
</head>
<body>
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body"></div>
                <h1 class="card-title text-center mb-4">
                    <i class="bi bi-sun text-warning me-2" aria-hidden="true"></i>
                    <span>Escuela de Verano 2024</span>
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
                                <span>Datos de los Participantes</span>
                            </h2>
                        </div>
                        <div class="card-body">
                            <div id="hijos-container">
                                <!-- Los formularios de participantes se agregarán aquí -->
                            </div>
                            <button type="button" class="btn btn-outline-primary" onclick="agregarParticipante()">
                                <i class="bi bi-plus-circle"></i> Agregar Participante
                            </button>
                        </div>
                    </section>

                    <!-- Periodos y Servicios -->
                    <section class="card mb-4">
                        <div class="card-header">
                            <h2 class="text-white">
                                <i class="bi bi-calendar3 me-2"></i>
                                Periodos y Servicios
                            </h2>
                        </div>
                        <div class="card-body">
                            <!-- Periodos -->
                            <div class="periodo-section">
                                <h3 class="section-title">
                                    <i class="bi bi-calendar-week"></i>
                                    Seleccione las Semanas
                                </h3>
                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="periodos[]" value="julio1" id="periodo-julio1">
                                            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="periodo-julio1">
                                                <span>1-6 Julio</span>
                                                <span class="badge bg-primary">Semana 1</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="periodos[]" value="julio2" id="periodo-julio2">
                                            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="periodo-julio2">
                                                <span>7-13 Julio</span>
                                                <span class="badge bg-primary">Semana 2</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="periodos[]" value="julio3" id="periodo-julio3">
                                            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="periodo-julio3">
                                                <span>14-20 Julio</span>
                                                <span class="badge bg-primary">Semana 3</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="periodos[]" value="julio4" id="periodo-julio4">
                                            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="periodo-julio4">
                                                <span>21-27 Julio</span>
                                                <span class="badge bg-primary">Semana 4</span>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="periodos[]" value="julio5" id="periodo-julio5">
                                            <label class="form-check-label d-flex justify-content-between align-items-center w-100" for="periodo-julio5">
                                                <span>28-31 Julio</span>
                                                <span class="badge bg-primary">Semana 5</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Servicios -->
                            <div class="servicios-section">
                                <h3 class="section-title">
                                    <i class="bi bi-gear"></i>
                                    Servicios Adicionales
                                </h3>
                                <div class="row g-4">
                                    <!-- AMPA -->
                                    <div class="col-md-4">
                                        <label class="form-label">¿Es Socio del AMPA?</label>
                                        <div class="radio-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="socio_ampa" value="SI" id="ampa-si" required>
                                                <label class="form-check-label w-100" for="ampa-si">
                                                    <i class="bi bi-check-circle text-success"></i>
                                                    <div>Sí</div>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="socio_ampa" value="NO" id="ampa-no" required>
                                                <label class="form-check-label w-100" for="ampa-no">
                                                    <i class="bi bi-x-circle text-danger"></i>
                                                    <div>No</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Guardería -->
                                    <div class="col-md-4">
                                        <label class="form-label">Horario de Entrada</label>
                                        <select name="guarderia_matinal" class="form-select">
                                            <option value="">Horario normal (9:00h)</option>
                                            <option value="7:30">Matinal - 7:30h</option>
                                            <option value="8:00">Matinal - 8:00h</option>
                                            <option value="8:30">Matinal - 8:30h</option>
                                        </select>
                                    </div>

                                    <!-- Comedor -->
                                    <div class="col-md-4">
                                        <label class="form-label">¿Necesita Comedor?</label>
                                        <div class="radio-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="comedor" value="SI" id="comedor-si" required>
                                                <label class="form-check-label w-100" for="comedor-si">
                                                    <i class="bi bi-check-circle text-success"></i>
                                                    <div>Sí</div>
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="comedor" value="NO" id="comedor-no" required>
                                                <label class="form-check-label w-100" for="comedor-no">
                                                    <i class="bi bi-x-circle text-danger"></i>
                                                    <div>No</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
        "name": "Escuela de Verano 2024",
        "description": "Programa de actividades de verano para niños incluyendo deportes, talleres y actividades educativas",
        "provider": {
            "@type": "Organization",
            "name": "Ayuntamiento"
        },
        "areaServed": {
            "@type": "City",
            "name": "Localidad"
        },
        "serviceType": "Actividades de Verano",
        "offers": {
            "@type": "Offer",
            "availability": "https://schema.org/InStock",
            "availabilityStarts": "2024-06-24",
            "availabilityEnds": "2024-08-31"
        }
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/form_ev.js"></script>
    <script>
    document.getElementById('forma_pago').addEventListener('change', function() {
        document.querySelectorAll('.payment-info').forEach(div => div.style.display = 'none');
        if (this.value) {
            document.getElementById(this.value.toLowerCase() + '-info').style.display = 'block';
        }
    });
    </script>
</body>
</html>