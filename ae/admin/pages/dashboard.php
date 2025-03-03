<?php
$query = "SELECT c.*, 
          COUNT(DISTINCT ca.actividad_id) as total_actividades,
          COUNT(DISTINCT i.id) as total_inscritos
          FROM colegios c
          LEFT JOIN colegio_actividad ca ON c.id = ca.colegio_id
          LEFT JOIN inscripciones i ON ca.actividad_id = i.actividad_id
          GROUP BY c.id
          ORDER BY c.nombre";

$colegios = $conexion->query($query)->fetchAll();
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Colegios</h2>

    <div class="table-responsive">
        <table id="colegiosTable" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Colegio</th>
                    <th>Dirección</th>
                    <th>Teléfono</th>
                    <th>Actividades</th>
                    <th>Inscritos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($colegios as $colegio): ?>
                <tr>
                    <td><?= htmlspecialchars($colegio['nombre']) ?></td>
                    <td><?= htmlspecialchars($colegio['direccion'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($colegio['telefono'] ?? '-') ?></td>
                    <td>
                        <span class="badge bg-primary">
                            <?= $colegio['total_actividades'] ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-success">
                            <?= $colegio['total_inscritos'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="?page=actividades&colegio=<?= $colegio['id'] ?>" 
                           class="btn btn-sm btn-outline-primary">
                            Ver actividades <i class="bi bi-arrow-right"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Destroy existing DataTable if it exists
    if ($.fn.DataTable.isDataTable('#colegiosTable')) {
        $('#colegiosTable').DataTable().destroy();
    }
    
    // Initialize DataTable
    $('#colegiosTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        order: [[0, 'asc']], // Sort by first column (colegio name)
        pageLength: 10,
        responsive: true
    });
});
</script>
