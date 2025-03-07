<?php
require_once '../../../database/DatabaseConnectors.php';
require_once '../classes/ExtraescolaresManager.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$colegio_id = $_GET['colegio_id'] ?? 0;
$mensaje = $_GET['mensaje'] ?? '';
$tipo_mensaje = $_GET['tipo'] ?? '';

// Obtener información del colegio
try {
    $colegio = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT * FROM colegios WHERE id = ?",
        [$colegio_id]
    );
    
    if (empty($colegio)) {
        header('Location: ../actividades.php');
        exit;
    }
    $colegio = $colegio[0];
    
    // Obtener actividades del colegio
    $actividades = DatabaseConnectors::executeQuery('extraescolares',
        "SELECT a.*, 
                (SELECT ap.precio 
                 FROM actividades_precio ap 
                 WHERE ap.id_actividad = a.id 
                 ORDER BY ap.fecha DESC 
                 LIMIT 1) as precio_actual,
                COUNT(i.id) as total_inscritos 
         FROM actividades a
         INNER JOIN colegio_actividad ca ON a.id = ca.actividad_id
         LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
         WHERE ca.colegio_id = ? AND ca.activo = 1
         GROUP BY a.id
         ORDER BY a.actividad",
        [$colegio_id]
    );
    
} catch (Exception $e) {
    header('Location: ../actividades.php');
    exit;
}

$titulo_pagina = 'Actividades - ' . $colegio['nombre'];
include_once '../../../includes/header.php';
?>

<!-- ...existing code for styles... -->

<div class="container-fluid px-4 py-4">
    <!-- ...existing code for breadcrumb... -->
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1"><?= htmlspecialchars($colegio['nombre']) ?></h1>
            <p class="text-muted mb-0">Gestión de actividades extraescolares</p>
        </div>
        <div>
            <a href="../nueva-actividad.php?colegio_id=<?= $colegio_id ?>" class="btn btn-success me-2">
                <i class="bi bi-plus-lg me-2"></i>Nueva Actividad
            </a>
            <a href="../actividades.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>
    
    <!-- ...existing code for alerts and activity cards... -->
</div>

<?php include_once '../../../includes/footer.php'; ?>
