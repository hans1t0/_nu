<?php
require_once dirname(__FILE__) . '/../database.php';

$actividad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$colegio_id = isset($_GET['colegio']) ? (int)$_GET['colegio'] : 0;

try {
    // Obtener detalles de la actividad
    $stmt = $conexion->prepare("
        SELECT a.*, c.nombre as nombre_colegio,
               cm.nombre as curso_min,
               cx.nombre as curso_max,
               ca.cupo_maximo,
               ca.cupo_actual,
               (SELECT precio 
                FROM actividades_precio 
                WHERE id_actividad = a.id 
                ORDER BY fecha DESC LIMIT 1) as precio_actual
        FROM actividades a
        JOIN colegio_actividad ca ON a.id = ca.actividad_id
        JOIN colegios c ON ca.colegio_id = c.id
        JOIN cursos cm ON a.desde = cm.id
        JOIN cursos cx ON a.hasta = cx.id
        WHERE a.id = ?
    ");
    $stmt->execute([$actividad_id]);
    $actividad = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener horarios
    $stmt = $conexion->prepare("
        SELECT *
        FROM actividad_horarios 
        WHERE actividad_id = ?
        ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')
    ");
    $stmt->execute([$actividad_id]);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener inscritos
    $stmt = $conexion->prepare("
        SELECT h.nombre, h.fecha_nacimiento, 
               cu.nombre as curso,
               p.nombre_completo as padre,
               p.telefono,
               i.fecha_inscripcion,
               i.estado
        FROM inscripciones i
        JOIN hijos h ON i.hijo_id = h.id
        JOIN padres p ON h.padre_id = p.id
        JOIN cursos cu ON h.curso_id = cu.id
        WHERE i.actividad_id = ?
        ORDER BY h.nombre
    ");
    $stmt->execute([$actividad_id]);
    $inscritos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar los datos: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">
        <?= htmlspecialchars($actividad['actividad']) ?>
        <a href="?page=actividades&colegio=<?= $colegio_id ?>" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </h2>

    <div class="row g-4">
        <!-- Información General -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Información General</h5>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Colegio</dt>
                        <dd class="col-sm-8"><?= htmlspecialchars($actividad['nombre_colegio']) ?></dd>

                        <dt class="col-sm-4">Niveles</dt>
                        <dd class="col-sm-8">
                            <?= htmlspecialchars($actividad['curso_min']) ?> a 
                            <?= htmlspecialchars($actividad['curso_max']) ?>
                        </dd>

                        <dt class="col-sm-4">Precio</dt>
                        <dd class="col-sm-8"><?= number_format($actividad['precio_actual'], 2) ?>€</dd>

                        <dt class="col-sm-4">Cupo</dt>
                        <dd class="col-sm-8">
                            <?= $actividad['cupo_actual'] ?>/<?= $actividad['cupo_maximo'] ?>
                            <div class="progress mt-1" style="height: 5px;">
                                <?php 
                                $porcentaje = ($actividad['cupo_actual'] / $actividad['cupo_maximo']) * 100;
                                $clase = $porcentaje >= 90 ? 'bg-danger' : 
                                        ($porcentaje >= 70 ? 'bg-warning' : 'bg-success');
                                ?>
                                <div class="progress-bar <?= $clase ?>" style="width: <?= $porcentaje ?>%"></div>
                            </div>
                        </dd>

                        <dt class="col-sm-4">Estado</dt>
                        <dd class="col-sm-8">
                            <span class="badge <?= $actividad['activa'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $actividad['activa'] ? 'Activa' : 'Inactiva' ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Horarios -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Horarios</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Hora Inicio</th>
                                    <th>Hora Fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horarios as $horario): ?>
                                <tr>
                                    <td><?= htmlspecialchars($horario['dia_semana']) ?></td>
                                    <td><?= date('H:i', strtotime($horario['hora_inicio'])) ?></td>
                                    <td><?= date('H:i', strtotime($horario['hora_fin'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Inscritos -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Alumnos Inscritos</h5>
                    <span class="badge bg-primary"><?= count($inscritos) ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Alumno</th>
                                    <th>Curso</th>
                                    <th>Contacto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($inscritos as $inscrito): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($inscrito['nombre']) ?>
                                        <div class="small text-muted">
                                            Edad: <?= date_diff(
                                                date_create($inscrito['fecha_nacimiento']),
                                                date_create('today')
                                            )->y ?> años
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($inscrito['curso']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($inscrito['padre']) ?>
                                        <div class="small text-muted">
                                            Tel: <?= htmlspecialchars($inscrito['telefono']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars($inscrito['estado']) ?>
                                        </span>
                                        <div class="small text-muted">
                                            <?= date('d/m/Y', strtotime($inscrito['fecha_inscripcion'])) ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
