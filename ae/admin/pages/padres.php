<?php
$accion = isset($_GET['accion']) ? $_GET['accion'] : 'listar';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    switch($_POST['accion']) {
        case 'crear':
            $sql = "INSERT INTO padres (nombre_completo, dni, email, telefono) 
                   VALUES (:nombre_completo, :dni, :email, :telefono)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($_POST);
            header('Location: ?page=padres');
            break;
            
        case 'actualizar':
            $sql = "UPDATE padres SET 
                   nombre_completo = :nombre_completo,
                   dni = :dni,
                   email = :email,
                   telefono = :telefono
                   WHERE id = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($_POST);
            header('Location: ?page=padres');
            break;
            
        case 'eliminar':
            $stmt = $conexion->prepare("DELETE FROM padres WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            header('Location: ?page=padres');
            break;
    }
}

// Obtener datos para edición
$padre = null;
if ($accion == 'editar' && $id) {
    $stmt = $conexion->prepare("SELECT * FROM padres WHERE id = ?");
    $stmt->execute([$id]);
    $padre = $stmt->fetch();
}

// Obtener lista de padres con estadísticas
$query = "SELECT p.*, 
          COUNT(DISTINCT h.id) as total_hijos,
          COUNT(DISTINCT i.id) as total_inscripciones
          FROM padres p
          LEFT JOIN hijos h ON p.id = h.padre_id
          LEFT JOIN inscripciones i ON h.id = i.hijo_id
          GROUP BY p.id
          ORDER BY p.nombre_completo";
$padres = $conexion->query($query)->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Padres/Tutores</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#padreModal">
        <i class="bi bi-plus-circle"></i> Nuevo Padre/Tutor
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre Completo</th>
                <th>DNI</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Hijos</th>
                <th>Inscripciones</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($padres as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['nombre_completo']) ?></td>
                <td><?= htmlspecialchars($p['dni']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['telefono']) ?></td>
                <td><?= $p['total_hijos'] ?></td>
                <td><?= $p['total_inscripciones'] ?></td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button onclick="editarPadre(<?= $p['id'] ?>)" class="btn btn-outline-primary">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="eliminarPadre(<?= $p['id'] ?>)" class="btn btn-outline-danger">
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
<div class="modal fade" id="padreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><?= $padre ? 'Editar' : 'Nuevo' ?> Padre/Tutor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="<?= $padre ? 'actualizar' : 'crear' ?>">
                    <input type="hidden" name="id" value="<?= $padre['id'] ?? '' ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" name="nombre_completo" class="form-control" required 
                               value="<?= $padre['nombre_completo'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">DNI</label>
                        <input type="text" name="dni" class="form-control" required 
                               value="<?= $padre['dni'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required 
                               value="<?= $padre['email'] ?? '' ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" name="telefono" class="form-control" required 
                               value="<?= $padre['telefono'] ?? '' ?>">
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
function editarPadre(id) {
    window.location.href = `?page=padres&accion=editar&id=${id}`;
}

function eliminarPadre(id) {
    if (confirm('¿Estás seguro de eliminar este padre/tutor? Se eliminarán también todos sus hijos asociados.')) {
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

<?php if ($padre): ?>
window.addEventListener('DOMContentLoaded', () => {
    new bootstrap.Modal(document.getElementById('padreModal')).show();
});
<?php endif; ?>
</script>
