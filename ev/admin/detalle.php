<?php
session_start();
require_once '../config.php';
checkAdminAccess();

if (!isset($_GET['id'])) {
    header('Location: inscritos.php');
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener datos del participante
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            r.nombre as responsable_nombre,
            r.dni as responsable_dni,
            r.email as responsable_email,
            r.telefono as responsable_telefono,
            r.forma_pago,
            r.iban,
            r.observaciones as obs_responsable,
            GROUP_CONCAT(DISTINCT pi.semana ORDER BY pi.semana) as semanas,
            MAX(sc.socio_ampa) as socio_ampa,
            MAX(sc.guarderia_matinal) as guarderia_matinal,
            MAX(sc.comedor) as comedor
        FROM participantes p
        LEFT JOIN responsables r ON p.responsable_id = r.id
        LEFT JOIN periodos_inscritos pi ON p.id = pi.participante_id
        LEFT JOIN servicios_contratados sc ON p.id = sc.participante_id
        WHERE p.id = :id
        GROUP BY p.id, p.nombre, p.fecha_nacimiento, p.centro_actual, 
                 p.curso, p.alergias, p.created_at,
                 r.nombre, r.dni, r.email, r.telefono, r.forma_pago, r.iban, r.observaciones
    ");
    $stmt->execute(['id' => $_GET['id']]);
    $inscrito = $stmt->fetch();

    if (!$inscrito) {
        throw new Exception('Participante no encontrado');
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Inscripción - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="bg-light">
    <main class="container py-4">
        <!-- Cabecera -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Detalle de Inscripción</h1>
            <div class="btn-group">
                <a href="inscritos.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
                <button class="btn btn-outline-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>

        <div class="row g-4">
            <!-- Datos del Participante -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-person"></i> Datos del Participante
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong><br><?= htmlspecialchars($inscrito['nombre']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha de nacimiento:</strong><br><?= date('d/m/Y', strtotime($inscrito['fecha_nacimiento'])) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Centro:</strong><br><?= getNombreCentro($inscrito['centro_actual']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Curso:</strong><br><?= htmlspecialchars($inscrito['curso']) ?></p>
                            </div>
                            <?php if ($inscrito['alergias']): ?>
                            <div class="col-12">
                                <p><strong>Alergias/Observaciones médicas:</strong><br>
                                <?= nl2br(htmlspecialchars($inscrito['alergias'])) ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Datos del Responsable -->
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-person-badge"></i> Datos del Responsable
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p><strong>Nombre:</strong><br><?= htmlspecialchars($inscrito['responsable_nombre']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>DNI/NIE:</strong><br><?= htmlspecialchars($inscrito['responsable_dni']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Email:</strong><br>
                                <a href="mailto:<?= htmlspecialchars($inscrito['responsable_email']) ?>">
                                    <?= htmlspecialchars($inscrito['responsable_email']) ?>
                                </a></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono:</strong><br>
                                <a href="tel:<?= htmlspecialchars($inscrito['responsable_telefono']) ?>">
                                    <?= htmlspecialchars($inscrito['responsable_telefono']) ?>
                                </a></p>
                            </div>
                            <div class="col-12">
                                <p><strong>Forma de pago:</strong><br><?= htmlspecialchars($inscrito['forma_pago']) ?></p>
                                <?php if ($inscrito['iban']): ?>
                                    <p><strong>IBAN:</strong><br><?= htmlspecialchars($inscrito['iban']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Periodos y Servicios -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h2 class="h5 mb-0">
                            <i class="bi bi-calendar3"></i> Periodos y Servicios
                        </h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Semanas -->
                            <div class="col-md-6">
                                <h3 class="h6 mb-3">Semanas inscritas</h3>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php 
                                    $semanas = explode(',', $inscrito['semanas']);
                                    foreach ($semanas as $semana): 
                                    ?>
                                    <div class="badge bg-primary p-2">
                                        <?= getNombrePeriodo($semana) ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Servicios -->
                            <div class="col-md-6">
                                <h3 class="h6 mb-3">Servicios contratados</h3>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi <?= $inscrito['socio_ampa'] === 'SI' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?>"></i>
                                        Socio AMPA
                                    </li>
                                    <li class="mb-2">
                                        <?php if ($inscrito['guarderia_matinal']): ?>
                                            <i class="bi bi-check-circle-fill text-success"></i>
                                            Guardería matinal: <?= $inscrito['guarderia_matinal'] ?>h
                                        <?php else: ?>
                                            <i class="bi bi-x-circle-fill text-danger"></i>
                                            Sin guardería matinal
                                        <?php endif; ?>
                                    </li>
                                    <li>
                                        <i class="bi <?= $inscrito['comedor'] === 'SI' ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger' ?>"></i>
                                        Servicio de comedor
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
