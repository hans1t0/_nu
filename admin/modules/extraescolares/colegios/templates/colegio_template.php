<?php
require_once dirname(__FILE__) . '/../../../../database/DatabaseConnectors.php';

if (!defined('COLEGIO_ID')) {
    die('Acceso no permitido');
}

try {
    // Obtener información del colegio con estadísticas
    $colegio = DatabaseConnectors::executeQuery('extraescolares', 
        "SELECT 
            c.*,
            COUNT(DISTINCT ca.actividad_id) as total_actividades,
            COUNT(DISTINCT i.id) as total_inscritos,
            COUNT(DISTINCT i.hijo_id) as total_alumnos
        FROM colegios c
        LEFT JOIN colegio_actividad ca ON c.id = ca.colegio_id AND ca.activo = 1
        LEFT JOIN actividades a ON ca.actividad_id = a.id AND a.activa = 1
        LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
        WHERE c.id = ?
        GROUP BY c.id",
        [COLEGIO_ID]
    )[0] ?? null;

    if (!$colegio) {
        throw new Exception('Colegio no encontrado');
    }

    // Obtener actividades del colegio - Consulta corregida con filtro por colegio_id
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
            (
                SELECT COUNT(*) 
                FROM inscripciones i 
                WHERE i.actividad_id = a.id 
                AND i.estado = 'confirmada'
            ) as total_inscritos
        FROM actividades a
        INNER JOIN colegio_actividad ca ON a.id = ca.actividad_id AND ca.colegio_id = ? AND ca.activo = 1
        LEFT JOIN actividad_horarios ah ON a.id = ah.actividad_id
        WHERE a.activa = 1
        GROUP BY a.id
        ORDER BY a.actividad ASC",
        [COLEGIO_ID]
    );

    // Incluir header
    $titulo_pagina = $colegio['nombre'];
    require_once dirname(__FILE__) . '/../../../../includes/header.php';
?>



<div class="content-wrapper">
    <div class="container-fluid px-4 py-4">
        <!-- Botón volver -->
        <div class="mb-3">
            <a href="../colegios.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>Volver a Colegios
            </a>
        </div>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="../../index.php">Panel</a></li>
                <li class="breadcrumb-item"><a href="../colegios.php">Colegios</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($colegio['nombre']) ?></li>
            </ol>
        </nav>

        <!-- Cabecera del colegio -->
        <div class="dashboard-card p-4 mb-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h2 class="mb-3"><?= htmlspecialchars($colegio['nombre']) ?></h2>
                    <div class="mb-3">
                        <?php if ($colegio['direccion']): ?>
                            <p class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= htmlspecialchars($colegio['direccion']) ?></p>
                        <?php endif; ?>
                        <?php if ($colegio['telefono']): ?>
                            <p class="mb-2"><i class="bi bi-telephone me-2"></i><?= htmlspecialchars($colegio['telefono']) ?></p>
                        <?php endif; ?>
                        <?php if ($colegio['email']): ?>
                            <p class="mb-2"><i class="bi bi-envelope me-2"></i><?= htmlspecialchars($colegio['email']) ?></p>
                        <?php endif; ?>
                        <?php if ($colegio['contacto']): ?>
                            <p class="mb-0"><i class="bi bi-person me-2"></i><?= htmlspecialchars($colegio['contacto']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="px-3 py-2 bg-light rounded text-center">
                                <div class="h4 mb-1"><?= $colegio['total_actividades'] ?></div>
                                <div class="small text-muted">Actividades</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="px-3 py-2 bg-light rounded text-center">
                                <div class="h4 mb-1"><?= $colegio['total_alumnos'] ?></div>
                                <div class="small text-muted">Alumnos</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="px-3 py-2 bg-light rounded text-center">
                                <div class="h4 mb-1"><?= $colegio['total_inscritos'] ?></div>
                                <div class="small text-muted">Inscripciones</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de actividades -->
        <div class="dashboard-card">
            <div class="card-header bg-light p-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Actividades Disponibles</h5>
                <div>
                    <a href="../actividades.php?action=nueva&colegio=<?= COLEGIO_ID ?>" class="btn btn-sm btn-success me-2">
                        <i class="bi bi-plus-lg"></i> Nueva Actividad
                    </a>
                    <a href="../colegios.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i>
                    </a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Actividad</th>
                            <th>Horario</th>
                            <th class="text-center" style="width: 100px">Inscritos</th>
                            <th class="text-end" style="width: 100px">Precio</th>
                            <th class="text-center" style="width: 100px">Estado</th>
                            <th class="text-end" style="width: 100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($actividades as $actividad): ?>
                        <tr>
                            <td>
                                <div class="fw-medium"><?= htmlspecialchars($actividad['actividad']) ?></div>
                                <?php if ($actividad['detalle_actividad']): ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars(strip_tags($actividad['detalle_actividad'])) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($actividad['horarios'])): ?>
                                    <small><?= $actividad['horarios'] ?></small>
                                <?php else: ?>
                                    <small class="text-muted">Sin horario definido</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="small fw-medium"><?= $actividad['total_inscritos'] ?>/<?= $actividad['max_alumnos'] ?></div>
                            </td>
                            <td class="text-end">
                                <?php 
                                $precio = DatabaseConnectors::executeQuery('extraescolares',
                                    "SELECT precio FROM actividades_precio 
                                     WHERE id_actividad = ? AND activo = 1 
                                     ORDER BY fecha DESC LIMIT 1",
                                    [$actividad['id']]
                                );
                                $precio_actual = $precio[0]['precio'] ?? $actividad['precio'] ?? 0;
                                ?>
                                <span class="fw-medium"><?= number_format($precio_actual, 2, ',', '.') ?> €</span>
                            </td>
                            <td class="text-center">
                                <?php if ($actividad['total_inscritos'] >= $actividad['max_alumnos']): ?>
                                    <span class="badge bg-danger rounded-pill px-2">Completo</span>
                                <?php else: ?>
                                    <span class="badge bg-success rounded-pill px-2">Disponible</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="../actividades.php?action=editar&id=<?= $actividad['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($actividades)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">No hay actividades disponibles</div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
    require_once dirname(__FILE__) . '/../../../../includes/footer.php';
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
