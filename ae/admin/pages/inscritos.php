<?php
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Procesar acciones POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch($_POST['accion']) {
        case 'actualizar_estado':
            $sql = "UPDATE inscripciones SET estado = :estado WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                'estado' => $_POST['estado'],
                'id' => $_POST['id']
            ]);
            header('Location: ?page=inscritos');
            break;
            
        case 'eliminar':
            $stmt = $conexion->prepare("DELETE FROM inscripciones WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            header('Location: ?page=inscritos');
            break;
    }
}

// Filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$filtro_colegio = isset($_GET['colegio_id']) ? (int)$_GET['colegio_id'] : 0;
$filtro_actividad = isset($_GET['actividad_id']) ? (int)$_GET['actividad_id'] : 0;

// Consulta base - Corregida para usar el esquema correcto
$query = "SELECT i.*, 
          h.nombre as alumno_nombre,
          p.nombre_completo as padre_nombre,
          p.telefono as padre_telefono,
          p.email as padre_email,
          a.actividad as actividad_nombre,
          COALESCE((
            SELECT ap.precio 
            FROM actividades_precio ap 
            WHERE ap.id_actividad = a.id 
            ORDER BY ap.fecha DESC 
            LIMIT 1
          ), 0) as actividad_precio,
          c.nombre as colegio_nombre
          FROM inscripciones i
          JOIN hijos h ON i.hijo_id = h.id
          JOIN padres p ON h.padre_id = p.id
          JOIN actividades a ON i.actividad_id = a.id
          JOIN colegios c ON h.colegio_id = c.id
          WHERE 1=1";

// Aplicar filtros
$params = [];
if ($filtro_estado) {
    $query .= " AND i.estado = ?";
    $params[] = $filtro_estado;
}
if ($filtro_colegio) {
    $query .= " AND h.colegio_id = ?";
    $params[] = $filtro_colegio;
}
if ($filtro_actividad) {
    $query .= " AND i.actividad_id = ?";
    $params[] = $filtro_actividad;
}

$query .= " ORDER BY i.fecha_inscripcion DESC";

// Ejecutar consulta
$stmt = $conexion->prepare($query);
$stmt->execute($params);
$inscripciones = $stmt->fetchAll();

// Obtener listas para filtros - Corregida la consulta de actividades
$colegios = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre")->fetchAll();
$actividades = $conexion->query("SELECT id, actividad as nombre FROM actividades WHERE activa = 1 ORDER BY actividad")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Inscripciones</h2>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <input type="hidden" name="page" value="inscritos">
            
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="confirmada" <?= $filtro_estado == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                    <option value="cancelada" <?= $filtro_estado == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Colegio</label>
                <select name="colegio_id" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($colegios as $colegio): ?>
                        <option value="<?= $colegio['id'] ?>" <?= $filtro_colegio == $colegio['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($colegio['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Actividad</label>
                <select name="actividad_id" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($actividades as $actividad): ?>
                        <option value="<?= $actividad['id'] ?>" <?= $filtro_actividad == $actividad['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($actividad['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary d-block">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Inscripciones -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Alumno</th>
                <th>Padre/Tutor</th>
                <th>Contacto</th>
                <th>Actividad</th>
                <th>Colegio</th>
                <th>Precio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inscripciones as $ins): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($ins['fecha_inscripcion'])) ?></td>
                <td><?= htmlspecialchars($ins['alumno_nombre']) ?></td>
                <td><?= htmlspecialchars($ins['padre_nombre']) ?></td>
                <td>
                    <small>
                        <?= htmlspecialchars($ins['padre_email']) ?><br>
                        <?= htmlspecialchars($ins['padre_telefono']) ?>
                    </small>
                </td>
                <td><?= htmlspecialchars($ins['actividad_nombre']) ?></td>
                <td><?= htmlspecialchars($ins['colegio_nombre']) ?></td>
                <td><?= number_format($ins['actividad_precio'], 2) ?>€</td>
                <td>
                    <select class="form-select form-select-sm estado-select" 
                            data-id="<?= $ins['id'] ?>" 
                            style="width: 100px;">
                        <option value="pendiente" <?= $ins['estado'] == 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="confirmada" <?= $ins['estado'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                        <option value="cancelada" <?= $ins['estado'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                    </select>
                </td>
                <td>
                    <button onclick="eliminarInscripcion(<?= $ins['id'] ?>)" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
// Actualizar estado
document.querySelectorAll('.estado-select').forEach(select => {
    select.addEventListener('change', function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="accion" value="actualizar_estado">
            <input type="hidden" name="id" value="${this.dataset.id}">
            <input type="hidden" name="estado" value="${this.value}">
        `;
        document.body.appendChild(form);
        form.submit();
    });
});

function eliminarInscripcion(id) {
    if (confirm('¿Estás seguro de eliminar esta inscripción?')) {
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
</script>
