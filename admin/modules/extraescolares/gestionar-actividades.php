<?php
require_once '../../database/DatabaseConnectors.php';
require_once 'classes/ExtraescolaresManager.php';

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
        header('Location: actividades.php');
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
                COUNT(i.id) as total_inscritos,
                GROUP_CONCAT(DISTINCT ah.dia_semana ORDER BY FIELD(ah.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')) as dias,
                MIN(ah.hora_inicio) as hora_inicio,
                MAX(ah.hora_fin) as hora_fin
         FROM actividades a
         INNER JOIN colegio_actividad ca ON a.id = ca.actividad_id
         LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
         LEFT JOIN actividad_horarios ah ON a.id = ah.actividad_id
         WHERE ca.colegio_id = ? AND ca.activo = 1
         GROUP BY a.id
         ORDER BY a.actividad",
        [$colegio_id]
    );
    
} catch (Exception $e) {
    header('Location: actividades.php');
    exit;
}

$titulo_pagina = 'Actividades - ' . $colegio['nombre'];
include_once '../../includes/header.php';
?>

<div class="container-fluid px-4 py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../../index.php">Panel</a></li>
            <li class="breadcrumb-item"><a href="actividades.php">Actividades</a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($colegio['nombre']) ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1"><?= htmlspecialchars($colegio['nombre']) ?></h1>
            <p class="text-muted mb-0">Gestión de actividades extraescolares</p>
        </div>
        <div>
            <a href="nueva-actividad.php?colegio_id=<?= $colegio_id ?>" class="btn btn-success">
                <i class="bi bi-plus-lg me-2"></i>Nueva Actividad
            </a>
            <a href="actividades.php" class="btn btn-outline-secondary ms-2">
                <i class="bi bi-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="bg-light">
                        <tr>
                            <th>Actividad</th>
                            <th>Horario</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Inscritos</th>
                            <th class="text-center">Estado</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades as $actividad): 
                            $porcentaje = ($actividad['total_inscritos'] / $actividad['max_alumnos']) * 100;
                            $tipo_badge = $porcentaje >= 90 ? 'danger' : 
                                        ($porcentaje >= 75 ? 'warning' : 'success');
                            
                            $horario = '';
                            if ($actividad['dias'] && $actividad['hora_inicio'] && $actividad['hora_fin']) {
                                $horario = $actividad['dias'] . ' de ' . 
                                         substr($actividad['hora_inicio'], 0, 5) . ' a ' .
                                         substr($actividad['hora_fin'], 0, 5);
                            }
                        ?>
                        <tr>
                            <td>
                                <h6 class="mb-0"><?= htmlspecialchars($actividad['actividad']) ?></h6>
                                <?php if ($actividad['detalle_actividad']): ?>
                                <small class="text-muted d-block">
                                    <?= htmlspecialchars(substr($actividad['detalle_actividad'], 0, 60)) ?>...
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= htmlspecialchars($horario) ?>
                                </small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success rounded-pill px-3">
                                    <?= number_format($actividad['precio_actual'], 2, ',', '.') ?> €
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="badge bg-<?= $tipo_badge ?> rounded-pill">
                                        <?= $actividad['total_inscritos'] ?>/<?= $actividad['max_alumnos'] ?>
                                    </span>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($actividad['activa']): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="editar-actividad.php?id=<?= $actividad['id'] ?>&colegio_id=<?= $colegio_id ?>" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil me-1"></i>Editar
                                    </a>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="confirmarEliminar(<?= $actividad['id'] ?>)">
                                        <i class="bi bi-trash me-1"></i>Eliminar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($actividades)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No hay actividades registradas
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminar(id) {
    if (confirm('¿Estás seguro de que deseas eliminar esta actividad?')) {
        window.location.href = `eliminar-actividad.php?id=${id}&colegio_id=<?= $colegio_id ?>`;
    }
}
</script>

<?php include_once '../../includes/footer.php'; ?>
