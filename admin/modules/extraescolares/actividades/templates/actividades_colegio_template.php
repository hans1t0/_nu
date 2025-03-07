<?php
require_once dirname(__FILE__) . '/../../../../database/DatabaseConnectors.php';

if (!defined('COLEGIO_ID')) {
    die('Acceso no permitido');
}

try {
    // Obtener información del colegio 
    $colegio = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT c.* FROM colegios c WHERE c.id = ?",
        [COLEGIO_ID]
    )[0] ?? null;

    if (!$colegio) {
        throw new Exception('Colegio no encontrado');
    }

    // Obtener actividades del colegio
    $actividades = DatabaseConnectors::executeQuery('extraescolares',
        "SELECT DISTINCT
            a.*, 
            GROUP_CONCAT(
                CONCAT(ah.dia_semana, ' de ', 
                TIME_FORMAT(ah.hora_inicio, '%H:%i'), ' a ', 
                TIME_FORMAT(ah.hora_fin, '%H:%i'))
                ORDER BY FIELD(ah.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')
                SEPARATOR ' y '
            ) as horarios,
            (SELECT COUNT(*) FROM inscripciones i WHERE i.actividad_id = a.id AND i.estado = 'confirmada') as total_inscritos
        FROM actividades a
        INNER JOIN colegio_actividad ca ON a.id = ca.actividad_id AND ca.colegio_id = ? AND ca.activo = 1
        LEFT JOIN actividad_horarios ah ON a.id = ah.actividad_id
        WHERE a.activa = 1
        GROUP BY a.id
        ORDER BY a.actividad ASC",
        [COLEGIO_ID]
    );

    // Incluir header
    $titulo_pagina = "Actividades - " . $colegio['nombre'];
    require_once dirname(__FILE__) . '/../../../../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Botón volver -->
    <div class="mb-3">
        <a href="../../colegios.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Volver a Colegios
        </a>
    </div>

    <!-- Lista de actividades -->
    <div class="dashboard-card">
        <div class="card-header bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Actividades de <?= htmlspecialchars($colegio['nombre']) ?></h5>
            <a href="../nueva.php?colegio=<?= COLEGIO_ID ?>" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg me-1"></i>Nueva Actividad
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table align-middle table-hover mb-0">
                <!-- ... resto del código de la tabla ... -->
            </table>
        </div>
    </div>
</div>

<?php
    require_once dirname(__FILE__) . '/../../../../includes/footer.php';
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
