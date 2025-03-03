<?php
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
$colegio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch($accion) {
    case 'ver':
        include dirname(__DIR__) . '/colegios/ver.php';
        break;
    case 'editar':
        include dirname(__DIR__) . '/colegios/editar.php';
        break;
    case 'crear':
        include dirname(__DIR__) . '/colegios/crear.php';
        break;
    default:
        include dirname(__DIR__) . '/colegios/lista.php';
}
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">Colegios</h2>

    <div class="table-responsive">
        <table id="tablaColegios" class="table table-striped table-hover datatable">
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
