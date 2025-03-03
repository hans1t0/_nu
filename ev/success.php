<?php
session_start();

// Verificar que venimos de una inscripción válida
if (!isset($_GET['id']) || !isset($_SESSION['inscripcion_completada'])) {
    header('Location: index.php');
    exit;
}

require_once 'config.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener datos del responsable
    $stmt = $pdo->prepare("
        SELECT * FROM responsables WHERE id = :id
    ");
    $stmt->execute(['id' => $_GET['id']]);
    $responsable = $stmt->fetch();

    // Obtener participantes y sus servicios
    $stmt = $pdo->prepare("
        SELECT 
            p.*,
            GROUP_CONCAT(DISTINCT pi.semana) as semanas,
            sc.socio_ampa,
            sc.guarderia_matinal,
            sc.comedor
        FROM participantes p
        LEFT JOIN periodos_inscritos pi ON p.id = pi.participante_id
        LEFT JOIN servicios_contratados sc ON p.id = sc.participante_id
        WHERE p.responsable_id = :responsable_id
        GROUP BY p.id
    ");
    $stmt->execute(['responsable_id' => $_GET['id']]);
    $participantes = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Completada - Escuela de Verano 2024</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/form_ev.css">
</head>
<body>
    <main class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="text-center mb-4">
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                    <h1 class="h3 mt-3">¡Inscripción Completada!</h1>
                    <p class="text-muted">La inscripción se ha realizado correctamente</p>
                </div>

                <!-- Resumen de la Inscripción -->
                <div class="row g-4">
                    <!-- Datos del Responsable -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h2 class="h5 mb-0 text-white">
                                    <i class="bi bi-person-circle me-2"></i>
                                    Datos del Responsable
                                </h2>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Nombre:</strong> <?= htmlspecialchars($responsable['nombre']) ?></p>
                                        <p><strong>DNI/NIE:</strong> <?= htmlspecialchars($responsable['dni']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Email:</strong> <?= htmlspecialchars($responsable['email']) ?></p>
                                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($responsable['telefono']) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Participantes -->
                    <?php foreach ($participantes as $participante): ?>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-success">
                                <h3 class="h5 mb-0 text-white">
                                    <i class="bi bi-person me-2"></i>
                                    <?= htmlspecialchars($participante['nombre']) ?>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h4 class="h6">Datos Personales</h4>
                                        <p><strong>Fecha de nacimiento:</strong> <?= date('d/m/Y', strtotime($participante['fecha_nacimiento'])) ?></p>
                                        <p><strong>Centro:</strong> <?= htmlspecialchars($participante['centro_actual']) ?></p>
                                        <p><strong>Curso:</strong> <?= htmlspecialchars($participante['curso']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="h6">Servicios Contratados</h4>
                                        <p><strong>Socio AMPA:</strong> <?= $participante['socio_ampa'] ?></p>
                                        <p><strong>Guardería Matinal:</strong> <?= $participante['guarderia_matinal'] ?: 'No' ?></p>
                                        <p><strong>Comedor:</strong> <?= $participante['comedor'] ?></p>
                                    </div>
                                    <div class="col-12">
                                        <h4 class="h6">Semanas</h4>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php 
                                            $semanas = explode(',', $participante['semanas']);
                                            foreach ($semanas as $semana): 
                                                $numSemana = substr($semana, -1);
                                            ?>
                                            <span class="badge bg-primary">Semana <?= $numSemana ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Botones de Acción -->
                <div class="d-flex justify-content-center gap-3 mt-4">
                    <a href="javascript:window.print()" class="btn btn-outline-primary">
                        <i class="bi bi-printer"></i> Imprimir
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="bi bi-house"></i> Volver al Inicio
                    </a>
                </div>
            </div>
        </div>
    </main>

    <!-- Estilos específicos para impresión -->
    <style media="print">
        .btn { display: none !important; }
        .card { border: 1px solid #ddd !important; }
        .card-header { background-color: #f8f9fa !important; color: #000 !important; }
        .text-white { color: #000 !important; }
    </style>
</body>
</html>
