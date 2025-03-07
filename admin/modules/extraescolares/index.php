<?php
require_once '../../database/DatabaseConnectors.php';
require_once 'classes/ExtraescolaresManager.php';

// Definir constantes del módulo
define('MODULE_NAME', 'Panel de Control - Extraescolares');
define('MODULE_DESCRIPTION', 'Gestión de actividades extraescolares y centros educativos');

// Obtener datos resumidos para el panel principal
try {
    // Contar colegios activos
    $totalColegios = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT COUNT(id) as total FROM colegios"
    );
    $numColegios = intval($totalColegios[0]['total'] ?? 0);
    
    // Contar actividades activas 
    $totalActividades = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT COUNT(DISTINCT a.id) as total 
         FROM actividades a 
         WHERE a.activa = 1"
    );
    $numActividades = intval($totalActividades[0]['total'] ?? 0);
    
    // Total inscripciones activas
    $totalInscripciones = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT COUNT(DISTINCT inscripciones.id) as total 
         FROM inscripciones 
         INNER JOIN actividades ON inscripciones.actividad_id = actividades.id 
         WHERE inscripciones.estado = 'confirmada' 
         AND actividades.activa = 1"
    );
    $numInscripciones = intval($totalInscripciones[0]['total'] ?? 0);
    
    // Obtener últimas inscripciones
    $ultimasInscripciones = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT 
            i.fecha_inscripcion,
            h.nombre as alumno,
            a.actividad,
            c.nombre as colegio,
            a.precio
        FROM inscripciones i
        INNER JOIN hijos h ON i.hijo_id = h.id
        INNER JOIN actividades a ON i.actividad_id = a.id
        INNER JOIN colegios c ON h.colegio_id = c.id
        WHERE i.estado = 'confirmada'
        ORDER BY i.fecha_inscripcion DESC
        LIMIT 5"
    );
    
    // Actividades más populares
    $actividadesPopulares = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT 
            a.actividad,
            COUNT(DISTINCT i.id) as total
        FROM actividades a
        LEFT JOIN inscripciones i ON a.id = i.actividad_id 
        WHERE a.activa = 1
        AND (i.estado = 'confirmada' OR i.estado IS NULL)
        GROUP BY a.id, a.actividad
        ORDER BY total DESC
        LIMIT 5"
    );

} catch (Exception $e) {
    error_log("Error en dashboard: " . $e->getMessage());
    $numColegios = 0;
    $numActividades = 0;
    $numInscripciones = 0;
    $ultimasInscripciones = [];
    $actividadesPopulares = [];
}

// Establecer título para el header
$titulo_pagina = MODULE_NAME;

// Incluir header común
require_once '../../includes/header.php';
?>

<!-- Contenido específico de la página -->
<div class="container-fluid py-4 px-4">
    <!-- Encabezado -->
    <header class="mb-4">
        <h1 class="h3 mb-2 fw-semibold"><?= MODULE_NAME ?></h1>
        <p class="text-muted mb-0"><?= MODULE_DESCRIPTION ?></p>
    </header>
    
    <!-- Estadísticas principales -->
    <div class="row g-4 mb-4">
        <!-- Colegios -->
        <div class="col-12 col-md-4">
            <div class="dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon stat-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="text-end">
                        <span class="d-block text-muted small">Total Colegios</span>
                        <h3 class="mb-0 fw-bold"><?= number_format($numColegios) ?></h3>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-primary" style="width: 75%"></div>
                </div>
                <div class="mt-3">
                    <a href="colegios.php" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-arrow-right me-1"></i> Gestionar
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Actividades -->
        <div class="col-12 col-md-4">
            <div class="dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon stat-success">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="text-end">
                        <span class="d-block text-muted small">Actividades Activas</span>
                        <h3 class="mb-0 fw-bold"><?= number_format($numActividades) ?></h3>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-success" style="width: 60%"></div>
                </div>
                <div class="mt-3">
                    <a href="actividades.php" class="btn btn-sm btn-outline-success rounded-pill px-3">
                        <i class="bi bi-arrow-right me-1"></i> Gestionar
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Inscripciones -->
        <div class="col-12 col-md-4">
            <div class="dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="stat-icon stat-warning">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="text-end">
                        <span class="d-block text-muted small">Últimas Inscripciones</span>
                        <h3 class="mb-0 fw-bold"><?= count($ultimasInscripciones) ?></h3>
                    </div>
                </div>
                <div class="progress" style="height: 6px;">
                    <div class="progress-bar bg-warning" style="width: 45%"></div>
                </div>
                <div class="mt-3">
                    <a href="inscripciones.php" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                        <i class="bi bi-arrow-right me-1"></i> Ver todas
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Acciones rápidas y gráfico -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="dashboard-card p-4">
                <h5 class="fw-semibold mb-4">Actividades más populares</h5>
                <div class="chart-container">
                    <canvas id="popularActivitiesChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-4">
            <div class="dashboard-card p-4">
                <h5 class="fw-semibold mb-4">Acciones rápidas</h5>
                <div class="d-grid gap-3">
                    <a href="colegios.php?action=nuevo" class="quick-action text-decoration-none text-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon stat-primary me-3" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Nuevo Colegio</h6>
                                <p class="mb-0 small text-muted">Añadir centro educativo</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="actividades.php?action=nueva" class="quick-action text-decoration-none text-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon stat-success me-3" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                <i class="bi bi-plus-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Nueva Actividad</h6>
                                <p class="mb-0 small text-muted">Crear actividad extraescolar</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="reportes.php" class="quick-action text-decoration-none text-body">
                        <div class="d-flex align-items-center">
                            <div class="stat-icon stat-warning me-3" style="width: 40px; height: 40px; font-size: 1.2rem;">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Ver Informes</h6>
                                <p class="mb-0 small text-muted">Estadísticas y reportes</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Últimas actividades -->
    <div class="row">
        <div class="col-12">
            <div class="dashboard-card p-4">
                <h5 class="fw-semibold mb-4">Últimas inscripciones</h5>
                <?php if (!empty($ultimasInscripciones)): ?>
                    <?php foreach ($ultimasInscripciones as $inscripcion): ?>
                    <div class="activity-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?= htmlspecialchars($inscripcion['alumno']) ?></h6>
                                <p class="mb-0 small text-muted">
                                    <?= htmlspecialchars($inscripcion['actividad']) ?> - 
                                    <?= htmlspecialchars($inscripcion['colegio']) ?>
                                </p>
                            </div>
                            <span class="badge bg-light text-dark">
                                <?= date('d/m/Y', strtotime($inscripcion['fecha_inscripcion'])) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="text-muted">
                            <i class="bi bi-calendar-x display-6"></i>
                            <p class="mt-2">No hay inscripciones recientes</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts específicos de la página -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar gráfico de actividades populares
    const ctx = document.getElementById('popularActivitiesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($actividadesPopulares, 'actividad')) ?>,
            datasets: [{
                label: 'Inscripciones',
                data: <?= json_encode(array_column($actividadesPopulares, 'total')) ?>,
                backgroundColor: [
                    'rgba(37, 99, 235, 0.8)',
                    'rgba(34, 197, 94, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(6, 182, 212, 0.8)',
                    'rgba(168, 85, 247, 0.8)'
                ],
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>

<?php
// Incluir footer común
require_once '../../includes/footer.php';
?>
