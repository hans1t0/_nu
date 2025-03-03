<?php
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch($_POST['accion']) {
        case 'crear':
            $sql = "INSERT INTO hijos (padre_id, nombre, fecha_nacimiento, colegio_id, curso_id) 
                   VALUES (:padre_id, :nombre, :fecha_nacimiento, :colegio_id, :curso_id)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($_POST);
            header('Location: ?page=alumnos');
            break;
            
        case 'actualizar':
            $sql = "UPDATE hijos SET 
                   padre_id = :padre_id,
                   nombre = :nombre,
                   fecha_nacimiento = :fecha_nacimiento,
                   colegio_id = :colegio_id,
                   curso_id = :curso_id
                   WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($_POST);
            header('Location: ?page=alumnos');
            break;
            
        case 'eliminar':
            $stmt = $conexion->prepare("DELETE FROM hijos WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            header('Location: ?page=alumnos');
            break;
    }
}

// Obtener listas necesarias
$padres = $conexion->query("SELECT id, nombre_completo FROM padres ORDER BY nombre_completo")->fetchAll();
$colegios = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre")->fetchAll();
$cursos = $conexion->query("SELECT id, nombre, nivel, grado FROM cursos ORDER BY nivel, grado")->fetchAll();

// Obtener datos para edición
$alumno = null;
if ($accion == 'editar' && $id) {
    $stmt = $conexion->prepare("SELECT * FROM hijos WHERE id = ?");
    $stmt->execute([$id]);
    $alumno = $stmt->fetch();
}

// Obtener lista de alumnos con datos relacionados
$query = "SELECT h.*, 
          p.nombre_completo as nombre_padre,
          c.nombre as nombre_colegio,
          cu.nombre as nombre_curso,
          COUNT(i.id) as total_actividades
          FROM hijos h
          JOIN padres p ON h.padre_id = p.id
          JOIN colegios c ON h.colegio_id = c.id
          JOIN cursos cu ON h.curso_id = cu.id
          LEFT JOIN inscripciones i ON h.id = i.hijo_id
          GROUP BY h.id
          ORDER BY h.nombre";
$alumnos = $conexion->query($query)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Alumnos</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#alumnoModal">
        <i class="bi bi-plus-circle"></i> Nuevo Alumno
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Padre/Tutor</th>
                <th>Fecha Nacimiento</th>
                <th>Colegio</th>
                <th>Curso</th>
                <th>Actividades</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($alumnos as $alu): ?>
            <tr>
                <td><?= htmlspecialchars($alu['nombre']) ?></td>
                <td><?= htmlspecialchars($alu['nombre_padre']) ?></td>
                <td><?= date('d/m/Y', strtotime($alu['fecha_nacimiento'])) ?></td>
                <td><?= htmlspecialchars($alu['nombre_colegio']) ?></td>
                <td><?= htmlspecialchars($alu['nombre_curso']) ?></td>
                <td><?= $alu['total_actividades'] ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button onclick="editarAlumno(<?= $alu['id'] ?>)" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="eliminarAlumno(<?= $alu['id'] ?>)" class="btn btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Crear/Editar -->
<div class="modal fade" id="alumnoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $alumno ? 'Editar' : 'Nuevo' ?> Alumno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="<?= $alumno ? 'actualizar' : 'crear' ?>">
                    <input type="hidden" name="id" value="<?= $alumno['id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="nombre" class="form-control" required 
                               value="<?= $alumno['nombre'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Padre/Tutor</label>
                        <select name="padre_id" class="form-select" required>
                            <?php foreach ($padres as $padre): ?>
                                <option value="<?= $padre['id'] ?>" <?= ($alumno['padre_id'] ?? '') == $padre['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($padre['nombre_completo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" class="form-control" required 
                               value="<?= $alumno['fecha_nacimiento'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Colegio</label>
                        <select name="colegio_id" class="form-select" required>
                            <?php foreach ($colegios as $colegio): ?>
                                <option value="<?= $colegio['id'] ?>" <?= ($alumno['colegio_id'] ?? '') == $colegio['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($colegio['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Curso</label>
                        <select name="curso_id" class="form-select" required>
                            <?php foreach ($cursos as $curso): ?>
                                <option value="<?= $curso['id'] ?>" <?= ($alumno['curso_id'] ?? '') == $curso['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($curso['nombre']) ?> (<?= $curso['nivel'] ?> - <?= $curso['grado'] ?>º)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarAlumno(id) {
    window.location.href = `?page=alumnos&accion=editar&id=${id}`;
}

function eliminarAlumno(id) {
    if (confirm('¿Estás seguro de eliminar este alumno?')) {
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

<?php if ($alumno): ?>
window.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('alumnoModal')).show();
});
<?php endif; ?>
</script>
