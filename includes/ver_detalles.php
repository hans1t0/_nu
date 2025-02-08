<?php
session_start();

// Configuración BD
define('DB_HOST', 'localhost');
define('DB_NAME', 'guarderia_matinal');
define('DB_USER', 'root');
define('DB_PASS', 'hans');

// Validar ID
if (!isset($_GET['id']) && !isset($_SESSION['inscripcion_id'])) {
    die('No hay inscripción especificada');
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['inscripcion_id'];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Obtener datos del responsable
    $stmt = $pdo->prepare("
        SELECT *
        FROM responsables
        WHERE id = ?
    ");
    $stmt->execute([$id]);
    $responsable = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$responsable) {
        die('Inscripción no encontrada');
    }

    // Obtener datos de los hijos
    $stmt = $pdo->prepare("
        SELECT 
            h.*,
            c.nombre as colegio,
            c.codigo as colegio_codigo
        FROM hijos h
        JOIN colegios c ON h.colegio_id = c.id
        WHERE h.responsable_id = ?
        ORDER BY h.nombre
    ");
    $stmt->execute([$id]);
    $hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular total mensual
    $total = 0;
    foreach ($hijos as &$hijo) {
        $hora = (int)date('H', strtotime($hijo['hora_entrada']));
        $hijo['cuota_base'] = $hora < 8 ? 40 : ($hora < 9 ? 35 : 30);
        $hijo['cuota_desayuno'] = ($hijo['colegio_codigo'] === 'ALMADRABA' && $hijo['desayuno']) ? 15 : 0;
        $hijo['total'] = $hijo['cuota_base'] + $hijo['cuota_desayuno'];
        $total += $hijo['total'];
    }

    // Determinar si es vista desde admin o inscripción nueva
    $esAdmin = isset($_GET['id']);
    $titulo = $esAdmin ? 'Detalles de Inscripción #' . $id : '¡Inscripción Realizada con Éxito!';
    $mensaje = $esAdmin ? '' : 'Su solicitud ha sido procesada correctamente.';

    ?>
    <!DOCTYPE html>
    <html lang="es"></button>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $titulo ?> - Guardería Matinal 2024</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
        <style>
            @media print {
                .no-print { display: none !important; }
                .card { border: none !important; }
                .card-header { 
                    background-color: #fff !important; 
                    color: #000 !important;
                    border-bottom: 2px solid #000 !important;
                }
                .alert { border: 1px solid #000 !important; }
            }

            /* Estilos generales */
            .bg-light { background-color: #f8f9fa !important; }
            
            /* Estilos de la tarjeta principal */
            .card {
                border-radius: 15px;
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
                overflow: hidden;
            }
            
            .card-header {
                border-bottom: 0;
                background: linear-gradient(45deg, #007bff, #0056b3);
                padding: 1.5rem;
            }

            /* Estilos de secciones */
            section {
                margin-bottom: 2rem;
                padding: 1rem;
                border-radius: 10px;
                background-color: #fff;
            }

            section h2, section h3 {
                color: #0056b3;
                margin-bottom: 1.5rem;
                padding-bottom: 0.5rem;
                border-bottom: 2px solid #e9ecef;
            }

            /* Estilos de la tabla */
            .table {
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 0 10px rgba(0,0,0,0.05);
            }

            .table thead th {
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
                color: #495057;
            }

            .table-primary {
                background-color: #cce5ff !important;
            }

            /* Estilos de alertas */
            .alert {
                border-radius: 10px;
                border-left: 5px solid;
            }

            .alert-success {
                border-left-color: #28a745;
                background-color: #d4edda;
            }

            .alert-info {
                border-left-color: #17a2b8;
                background-color: #d1ecf1;
            }

            .alert-warning {
                border-left-color: #ffc107;
                background-color: #fff3cd;
            }

            /* Badges y estados */
            .badge {
                padding: 0.5em 1em;
                border-radius: 30px;
                font-weight: 500;
            }

            /* Botones */
            .btn {
                border-radius: 30px;
                padding: 0.5rem 1.5rem;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            }

            /* Detalles y datos */
            .text-muted {
                color: #6c757d !important;
            }

            .detail-row {
                padding: 0.5rem;
                border-bottom: 1px solid #eee;
            }

            .detail-row:last-child {
                border-bottom: none;
            }

            /* Estilos específicos para móviles */
            @media (max-width: 768px) {
                .container { padding: 0.5rem; }
                .card { border-radius: 0; }
                .btn { width: 100%; margin-bottom: 0.5rem; }
            }

            /* Animaciones */
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .card {
                animation: fadeIn 0.5s ease-out;
            }

            .alert {
                animation: fadeIn 0.3s ease-out;
            }
        </style>
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <?php if (!$esAdmin): ?>
                    <div class="alert alert-success mb-4">
                        <h4 class="alert-heading"></h4>
                            <i class="bi bi-check-circle-fill"></i>
                            <?= $titulo ?>
                        </h4>
                        <p><?= $mensaje ?></p>
                        <hr>
                        <p class="mb-0">Por favor, guarde o imprima esta página para futura referencia.</p>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow-sm"></div>
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h1 class="h4 mb-0"></h1>
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Inscripción #<?= $id ?>
                            </h1>
                            <small>
                                <i class="bi bi-calendar-event me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($responsable['fecha_registro'])) ?>
                            </small>
                        </div>
                        <div class="card-body">
                            <!-- ... aquí todo el contenido existente ... -->

                            <!-- Añadir nueva sección de resumen de pagos -->
                            <section class="mt-4">
                                <h3 class="h5 border-bottom pb-2"></h3>
                                    <i class="bi bi-currency-euro me-2"></i>
                                    Resumen de Pagos
                                </h3>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr></tr>
                                                <th>Concepto</th>
                                                <th>Importe</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($hijos as $hijo): ?>
                                            <tr></tr>
                                                <td>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($hijo['nombre']) ?></strong>
                                                            <?php if ($hijo['desayuno']): ?>
                                                                <span class="badge bg-success rounded-circle p-1" 
                                                                      title="Con desayuno"
                                                                      data-bs-toggle="tooltip">
                                                                    <i class="bi bi-cup-hot-fill"></i>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?= date('H:i', strtotime($hijo['hora_entrada'])) ?>h
                                                        </small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?= number_format($hijo['total'], 2) ?>€
                                                    <br>
                                                    <small class="text-muted">
                                                        Base: <?= $hijo['cuota_base'] ?>€
                                                        <?= $hijo['cuota_desayuno'] ? "+ Desayuno: {$hijo['cuota_desayuno']}€" : '' ?>
                                                    </small>
                                                </td>
                                                <td></td>
                                                    <span class="badge bg-success">Al corriente</span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot class="table-primary">
                                            <tr>
                                                <th>Total Mensual</th>
                                                <th colspan="2"><?= number_format($total, 2) ?>€</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </section>

                            <!-- Información de pago específica -->
                            <section class="mt-4">
                                <div class="alert alert-info">
                                    <h4 class="alert-heading h5">
                                        <i class="bi bi-info-circle-fill me-2"></i>
                                        Información de Pago
                                    </h4>
                                    <?php if ($responsable['forma_pago'] === 'DOMICILIACION'): ?>
                                        <p>Los recibos se cargarán mensualmente en la cuenta:</p>
                                        <p class="mb-0"><strong>IBAN:</strong> <?= htmlspecialchars($responsable['iban']) ?></p>
                                    <?php elseif ($responsable['forma_pago'] === 'TRANSFERENCIA'): ?>
                                        <p>Realizar transferencia mensual a:</p>
                                        <p class="mb-0"><strong>Titular:</strong> EDUCAP Serveis d'Oci S.L.</p>
                                        <p class="mb-0"><strong>IBAN:</strong> ES30 3058 2519 4927 2000 6473</p>
                                        <p class="mb-0"><strong>Concepto:</strong> GM
                                            <?php
                                            // Organizar hijos por colegio correctamente
                                            $colegios = [];
                                            foreach ($hijos as $hijo) {
                                                $colegioNombre = $hijo['colegio'];
                                                if (!isset($colegios[$colegioNombre])) {
                                                    $colegios[$colegioNombre] = [];
                                                }
                                                $colegios[$colegioNombre][] = htmlspecialchars($hijo['nombre']);
                                            }

                                            $conceptosPago = [];
                                            foreach ($colegios as $colegio => $nombres) {
                                                $conceptosPago[] = implode('/', $nombres) . '+' . htmlspecialchars($colegio);
                                            }

                                            echo '+' . implode(' | ', $conceptosPago);
                                            ?>
                                        </p>
                                        <p class="mb-0">Enviar justificante a: <strong>inscripciones@educap.es</strong></p>
                                    <?php else: ?>
                                        <p class="mb-0">El coordinador se pondrá en contacto en el teléfono <?= htmlspecialchars($responsable['telefono']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </section>

                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="text-center mt-4">
                        <div class="btn-group no-print">
                            <button onclick="window.print()" class="btn btn-outline-primary">
                                <i class="bi bi-printer"></i> Imprimir
                            </button>
                            <?php if ($esAdmin): ?>
                                <a href="javascript:window.close()" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cerrar
                                </a>
                            <?php else: ?>
                                <a href="../matinera/" class="btn btn-success">
                                    <i class="bi bi-house"></i> Volver al Inicio
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        // Inicializar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        </script>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
