<?php
// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Manejar acciones POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'eliminar':
                if (isset($_POST['id'])) {
                    try {
                        $stmt = $conexion->prepare("DELETE FROM actividades WHERE id = ?");
                        $stmt->execute([$_POST['id']]);
                        header("Location: " . $_SERVER['HTTP_REFERER']);
                        exit;
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>Error al eliminar: " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                }
                break;
                
            case 'suspender':
                if (isset($_POST['id'], $_POST['estado'])) {
                    try {
                        $stmt = $conexion->prepare("UPDATE actividades SET activa = ? WHERE id = ?");
                        $stmt->execute([(int)$_POST['estado'], $_POST['id']]);
                        header("Location: " . $_SERVER['HTTP_REFERER']);
                        exit;
                    } catch (PDOException $e) {
                        echo "<div class='alert alert-danger'>Error al actualizar: " . htmlspecialchars($e->getMessage()) . "</div>";
                    }
                }
                break;
        }
    }
}

// Manejar acciones GET
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

switch ($accion) {
    case 'ver':
        include dirname(__DIR__) . '/actividades/ver.php';
        return;
    case 'editar': 
        include dirname(__DIR__) . '/actividades/editar.php';
        return;
    case 'crear':
        include dirname(__DIR__) . '/actividades/crear.php';
        return;
}

// Agregar referencias a jQuery y DataTables
?>
<!-- Referencias a jQuery y DataTables -->
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<?php
require_once dirname(__FILE__) . '/../database.php';

// Obtener ID del colegio si se especifica
$colegio_id = isset($_GET['colegio']) ? (int)$_GET['colegio'] : 0;

try {
    if ($colegio_id > 0) {
        // Consulta para un colegio específico
        $query = "SELECT a.*, 
                  COUNT(DISTINCT i.id) as total_inscritos,
                  cm.nombre as curso_min_nombre,
                  cx.nombre as curso_max_nombre,
                  ca.cupo_maximo,
                  ca.cupo_actual,
                  MAX(ap.precio) as precio,
                  /* Modificar esta parte para evitar duplicados */
                  (
                    SELECT GROUP_CONCAT(
                      CONCAT(dia_semana, ' ', 
                      TIME_FORMAT(hora_inicio, '%H:%i'), '-',
                      TIME_FORMAT(hora_fin, '%H:%i'))
                      ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes')
                      SEPARATOR '<br>'
                    )
                    FROM actividad_horarios
                    WHERE actividad_id = a.id
                  ) as horarios
                  FROM actividades a
                  LEFT JOIN inscripciones i ON a.id = i.actividad_id
                  LEFT JOIN cursos cm ON a.desde = cm.id
                  LEFT JOIN cursos cx ON a.hasta = cx.id
                  LEFT JOIN colegio_actividad ca ON a.id = ca.actividad_id
                  LEFT JOIN actividades_precio ap ON a.id = ap.id_actividad 
                  WHERE ca.colegio_id = :colegio_id
                  GROUP BY a.id, a.actividad, cm.nombre, cx.nombre, ca.cupo_maximo, ca.cupo_actual
                  ORDER BY a.actividad";
        
        $stmt = $conexion->prepare($query);
        $stmt->execute(['colegio_id' => $colegio_id]);
        $actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener nombre del colegio
        $stmt = $conexion->prepare("SELECT nombre FROM colegios WHERE id = ?");
        $stmt->execute([$colegio_id]);
        $colegio = $stmt->fetch(PDO::FETCH_ASSOC);

    } else {
        // Consulta para el resumen de todos los colegios
        $query = "SELECT c.id, c.nombre,
                  COUNT(DISTINCT a.id) as total_actividades,
                  COUNT(DISTINCT i.id) as total_inscritos,
                  SUM(ca.cupo_maximo) as cupos_totales,
                  SUM(ca.cupo_actual) as cupos_ocupados
                  FROM colegios c
                  LEFT JOIN colegio_actividad ca ON c.id = ca.colegio_id
                  LEFT JOIN actividades a ON ca.actividad_id = a.id
                  LEFT JOIN inscripciones i ON a.id = i.actividad_id
                  GROUP BY c.id
                  ORDER BY c.nombre";
        
        $stmt = $conexion->prepare($query);
        $stmt->execute();
        $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error en la consulta: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>

<div class="container-fluid px-4">
    <?php if ($colegio_id > 0 && isset($colegio)): ?>
        <!-- Vista de actividades para un colegio específico -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>
                Actividades en <?= htmlspecialchars($colegio['nombre']) ?>
                <a href="?page=actividades" class="btn btn-sm btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>
            </h2>
            <a href="?page=actividades&accion=crear&colegio=<?= $colegio_id ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nueva Actividad
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Actividad</th>
                        <th>Niveles</th>
                        <th>Horarios</th>
                        <th>Precio</th>
                        <th>Inscritos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($actividades as $act): ?>
                    <tr>
                        <td><?= htmlspecialchars($act['actividad']) ?></td>
                        <td>
                            <?= htmlspecialchars($act['curso_min_nombre']) ?> - 
                            <?= htmlspecialchars($act['curso_max_nombre']) ?>
                        </td>
                        <td><small><?= $act['horarios'] ?></small></td>
                        <td><?= number_format($act['precio'], 2) ?>€</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <?php 
                                $porcentaje = $act['cupo_maximo'] > 0 ? 
                                    ($act['total_inscritos'] / $act['cupo_maximo']) * 100 : 0;
                                $clase = $porcentaje >= 90 ? 'bg-danger' : 
                                        ($porcentaje >= 70 ? 'bg-warning' : 'bg-success');
                                ?>
                                <div class="progress-bar <?= $clase ?>" 
                                     style="width: <?= $porcentaje ?>%">
                                    <?= $act['total_inscritos'] ?>/<?= $act['cupo_maximo'] ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="?page=actividades&accion=ver&id=<?= $act['id'] ?>&colegio=<?= $colegio_id ?>" 
                                   class="btn btn-sm btn-outline-info" title="Ver detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="?page=actividades&accion=editar&id=<?= $act['id'] ?>&colegio=<?= $colegio_id ?>" 
                                   class="btn btn-sm btn-outline-primary" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button onclick="suspenderActividad(<?= $act['id'] ?>, <?= $act['activa'] ?>)"
                                        class="btn btn-sm btn-outline-warning" title="<?= $act['activa'] ? 'Suspender' : 'Reactivar' ?>">
                                    <i class="bi bi-<?= $act['activa'] ? 'pause' : 'play' ?>"></i>
                                </button>
                                <button onclick="eliminarActividad(<?= $act['id'] ?>)"
                                        class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Vista resumen de colegios -->
        <h2 class="mb-4">Resumen de Actividades por Colegio</h2>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
            <?php foreach ($colegios as $col): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($col['nombre']) ?></h5>
                        <div class="row g-3 mb-3">
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
                        <div class="d-grid">
                            <a href="?page=actividades&colegio=<?= $col['id'] ?>" 
                               class="btn btn-outline-primary">
                                Ver Actividades <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function eliminarActividad(id) {
    if (confirm('¿Estás seguro de eliminar esta actividad?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

$(document).ready(function() {
    $('.datatable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        pageLength: 10,
        order: [[0, 'asc']]
    });
});

function suspenderActividad(id, estadoActual) {
    const accion = estadoActual ? 'suspender' : 'reactivar';
    if (confirm(`¿Estás seguro de ${accion} esta actividad?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accion" value="suspender">
            <input type="hidden" name="id" value="${id}">
            <input type="hidden" name="estado" value="${!estadoActual}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
.progress {
    border-radius: 0.25rem;
}
.progress-bar {
    min-width: 2rem;
    font-size: 0.75rem;
    line-height: 20px;
}
.card {
    transition: transform 0.2s;
}
.card:hover {
    transform: translateY(-5px);
}
.btn-group .btn {
    padding: .25rem .5rem;
}
.btn-group .btn:not(:last-child) {
    margin-right: 2px;
}

/* Cards y Layout */
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,.05);
    transition: all 0.3s ease;
    border: 1px solid rgba(0,0,0,.1);
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,.1);
}
.card-body {
    padding: 1.5rem;
}

/* Tablas */
.table {
    margin-bottom: 0;
    font-size: 0.9rem;
}
.table th {
    font-weight: 600;
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}
.table td {
    vertical-align: middle;
}
.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.02);
}

/* Botones y badges */
.btn-group .btn {
    margin: 0 2px;
    padding: .25rem .5rem;
    font-size: 0.85rem;
}
.btn-group .btn i {
    margin-right: 0;
}
.badge {
    padding: 0.5em 0.8em;
    font-weight: 500;
}

/* Progress bars */
.progress {
    height: 20px;
    border-radius: 10px;
    background-color: #eee;
    margin: 5px 0;
}
.progress-bar {
    transition: width 0.3s ease;
    min-width: 2rem;
    border-radius: 10px;
    font-size: 0.75rem;
    line-height: 20px;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .btn-group .btn {
        width: 100%;
        margin: 0;
    }
    .table-responsive {
        border-radius: 0.5rem;
        border: 1px solid #dee2e6;
    }
}

/* Stats cards */
.stat-card {
    text-align: center;
    padding: 1rem;
    border-radius: 0.5rem;
    background: linear-gradient(145deg, #ffffff, #f5f5f5);
}
.stat-card .small {
    color: #6c757d;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.stat-card .h4 {
    color: #2c3e50;
    margin: 0.5rem 0;
    font-weight: 600;
}

/* Custom scrollbar */
.table-responsive::-webkit-scrollbar {
    height: 8px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
