<?php
session_start();

// Redirigir si no hay datos de inscripción
if (!isset($_SESSION['inscripcion'])) {
    header('Location: tardes.php');
    exit;
}

$inscripcion = $_SESSION['inscripcion'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Inscripción - Ludoteca Tardes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-check-circle-fill text-success display-1"></i>
                    <h1 class="h3 mt-3 mb-4">¡Inscripción Realizada con Éxito!</h1>
                    <p class="text-muted">Recibirás un email de confirmación en breve</p>
                </div>

                <!-- Desglose de la inscripción -->
                <div class="row g-4">
                    <!-- Datos del Responsable -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-primary text-white">
                                <h2 class="h5 mb-0"><i class="bi bi-person-circle me-2"></i>Datos del Responsable</h2>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-2"><strong>Nombre:</strong> <?php echo htmlspecialchars($inscripcion['tutor']['nombre']); ?></li>
                                    <li class="mb-2"><strong>DNI:</strong> <?php echo htmlspecialchars($inscripcion['tutor']['dni']); ?></li>
                                    <li class="mb-2"><strong>Email:</strong> <?php echo htmlspecialchars($inscripcion['tutor']['email']); ?></li>
                                    <li class="mb-2"><strong>Teléfono:</strong> <?php echo htmlspecialchars($inscripcion['tutor']['telefono']); ?></li>
                                    <li class="mb-2"><strong>Forma de Pago:</strong> <?php echo ucfirst(htmlspecialchars($inscripcion['tutor']['forma_pago'])); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Pago -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-success text-white">
                                <h2 class="h5 mb-0"><i class="bi bi-credit-card me-2"></i>Información de Pago</h2>
                            </div>
                            <div class="card-body">
                                <?php if ($inscripcion['tutor']['forma_pago'] === 'transferencia'): ?>
                                    <div class="alert alert-info">
                                        <p class="mb-1"><strong>Titular:</strong> EDUCAP Serveis d'Oci S.L.</p>
                                        <p class="mb-1"><strong>IBAN:</strong> ES30 3058 2519 4927 2000 6473</p>
                                        <p class="mb-1"><strong>Concepto:</strong> LT+<?php echo htmlspecialchars($inscripcion['alumnos'][0]['nombre']); ?>+<?php echo htmlspecialchars($inscripcion['alumnos'][0]['centro']); ?></p>
                                        <p class="mb-0"><strong>Enviar justificante a:</strong> inscripciones@educap.es</p>
                                    </div>
                                <?php elseif ($inscripcion['tutor']['forma_pago'] === 'domiciliacion'): ?>
                                    <div class="alert alert-info">
                                        <p class="mb-1">El cobro se realizará mediante domiciliación bancaria.</p>
                                        <p class="mb-0">IBAN: <?php echo substr($inscripcion['tutor']['iban'], 0, 4) . '****'; ?></p>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <p class="mb-0">El coordinador se pondrá en contacto para gestionar el pago.</p>
                                    </div>
                                <?php endif; ?>
                                
                                <h3 class="h6 mt-3">Total a Pagar: <?php echo number_format($inscripcion['total'], 2); ?>€</h3>
                                <p class="text-muted small">Estado: Pendiente de pago</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alumnos Inscritos -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h2 class="h5 mb-0"><i class="bi bi-people-fill me-2"></i>Alumnos Inscritos</h2>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Centro</th>
                                                <th>Curso</th>
                                                <th>Horario</th>
                                                <th>Precio</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($inscripcion['alumnos'] as $alumno): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellidos']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['centro']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['curso']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['horario']); ?></td>
                                                <td><?php echo number_format($alumno['precio'], 2); ?>€</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="text-center mt-4">
                    <a href="tardes.php" class="btn btn-primary me-2">
                        <i class="bi bi-plus-circle me-2"></i>Volver a inicio
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-2"></i>Imprimir
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
