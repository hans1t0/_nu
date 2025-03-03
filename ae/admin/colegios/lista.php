<?php
try {
    // Obtener lista de colegios con estadísticas
    $query = "SELECT c.*, 
              COUNT(DISTINCT a.id) as total_actividades,
              COUNT(DISTINCT i.id) as total_inscritos,
              SUM(ca.cupo_maximo) as cupos_totales,
              SUM(ca.cupo_actual) as cupos_ocupados
              FROM colegios c
              LEFT JOIN colegio_actividad ca ON c.id = ca.colegio_id
              LEFT JOIN actividades a ON ca.actividad_id = a.id AND a.activa = 1
              LEFT JOIN inscripciones i ON a.id = i.actividad_id
              GROUP BY c.id
              ORDER BY c.nombre";
    
    $stmt = $conexion->prepare($query);
    $stmt->execute();
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Colegios</h2>
        <a href="?page=colegios&accion=crear" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nuevo Colegio
        </a>
    </div>
    
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($colegios as $col): ?>
        <div class="col">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5 class="card-title"><?= htmlspecialchars($col['nombre']) ?></h5>
                        <div class="dropdown">
                            <button class="btn btn-link p-0" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="?page=colegios&accion=editar&id=<?= $col['id'] ?>">
                                        <i class="bi bi-pencil"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="?page=colegios&accion=ver&id=<?= $col['id'] ?>">
                                        <i class="bi bi-eye"></i> Ver detalles
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-muted mb-3">
                        <div><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($col['direccion'] ?? 'Sin dirección') ?></div>
                        <div><i class="bi bi-telephone"></i> <?= htmlspecialchars($col['telefono'] ?? 'Sin teléfono') ?></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-muted">Actividades</div>
                                <div class="h4 mb-0"><?= $col['total_actividades'] ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2 text-center">
                                <div class="small text-muted">Inscritos</div>
                                <div class="h4 mb-0"><?= $col['total_inscritos'] ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <a href="?page=actividades&colegio=<?= $col['id'] ?>" 
                           class="btn btn-outline-primary w-100">
                            Ver actividades <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,.1);
}
.dropdown-item i {
    width: 1.2em;
}
</style>
