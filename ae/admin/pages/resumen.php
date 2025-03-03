<?php
$stats = [];
try {
    $query = "SELECT 
        (SELECT COUNT(*) FROM colegios) as total_colegios,
        (SELECT COUNT(*) FROM actividades) as total_actividades,
        (SELECT COUNT(*) FROM hijos) as total_alumnos,
        (SELECT COUNT(*) FROM padres) as total_padres,
        (SELECT COUNT(*) FROM inscripciones WHERE estado = 'confirmada') as inscripciones_confirmadas,
        (SELECT COUNT(*) FROM inscripciones WHERE estado = 'pendiente') as inscripciones_pendientes";
    $stats = $conexion->query($query)->fetch();
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<div class="row g-4">
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-building text-primary"></i></h5>
                <h6>Colegios</h6>
                <h2 class="mb-0"><?= $stats['total_colegios'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-event text-success"></i></h5>
                <h6>Actividades</h6>
                <h2 class="mb-0"><?= $stats['total_actividades'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-mortarboard text-info"></i></h5>
                <h6>Alumnos</h6>
                <h2 class="mb-0"><?= $stats['total_alumnos'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-lg-2">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people text-warning"></i></h5>
                <h6>Padres</h6>
                <h2 class="mb-0"><?= $stats['total_padres'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-8 col-lg-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-person-check text-success"></i></h5>
                <h6>Inscripciones</h6>
                <h2 class="mb-0"><?= $stats['inscripciones_confirmadas'] ?? 0 ?></h2>
                <small class="text-muted"><?= $stats['inscripciones_pendientes'] ?? 0 ?> pendientes</small>
            </div>
        </div>
    </div>
</div>
