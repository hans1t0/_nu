<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Filtros mejorados
$centro_id = isset($_GET['centro_id']) ? (int)$_GET['centro_id'] : 0;
$where = [];
$params = [];

// Siempre mostrar solo inscripciones activas
$where[] = "i.estado = 'activa'";

if ($centro_id > 0) {
    $where[] = "c.id = :centro_id";
    $params[':centro_id'] = $centro_id;
}

// Construir WHERE
$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Consulta principal mejorada
$sql = "SELECT 
    i.id,
    i.fecha_inicio,
    i.estado,
    a.nombre as nombre_alumno,
    a.apellidos as apellidos_alumno,
    a.curso,
    c.id as centro_id,
    c.nombre as centro_nombre,
    t.nombre as tutor_nombre,
    t.email,
    t.telefono,
    t.forma_pago,
    h.descripcion as horario
FROM inscripciones i
JOIN alumnos a ON i.alumno_id = a.id
JOIN centros c ON a.centro_id = c.id
JOIN alumno_tutor at ON a.id = at.alumno_id
JOIN tutores t ON at.tutor_id = t.id
JOIN horarios h ON i.horario_id = h.id
$whereClause
ORDER BY c.nombre, a.apellidos, a.nombre";

// Debug
// error_log("SQL: " . $sql);
// error_log("Params: " . print_r($params, true));

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de centros para el filtro (solo centros activos)
$centros = $pdo->query("SELECT id, nombre FROM centros WHERE activo = 1 ORDER BY nombre")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inscripciones - Ludoteca Tardes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar mejorado -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="inscripciones.php">
                                <i class="bi bi-list-check"></i> Inscripciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../asistencia.php" target="_blank">
                                <i class="bi bi-calendar-check"></i> Control Asistencia
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="exportar.php">
                                <i class="bi bi-download"></i> Exportar Datos
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-white" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Contenido principal -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Gestión de Inscripciones</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="exportar.php<?php echo $centro_id ? '?centro_id='.$centro_id : ''; ?>" 
                           class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download"></i> Exportar Filtrados
                        </a>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <form class="d-flex">
                            <select name="centro_id" class="form-select me-2">
                                <option value="">Todos los centros</option>
                                <?php foreach ($centros as $centro): ?>
                                    <option value="<?php echo $centro['id']; ?>" 
                                            <?php echo ($centro['id'] == $centro_id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($centro['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary">Filtrar</button>
                        </form>
                    </div>
                </div>

                <!-- Tabla de inscripciones -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Centro</th>
                                <th>Curso</th>
                                <th>Tutor</th>
                                <th>Contacto</th>
                                <th>Horario</th>
                                <th>Forma Pago</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $i): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($i['nombre_alumno'] . ' ' . $i['apellidos_alumno']); ?></td>
                                <td><?php echo htmlspecialchars($i['centro_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($i['curso']); ?></td>
                                <td><?php echo htmlspecialchars($i['tutor_nombre']); ?></td>
                                <td>
                                    <small>
                                        <?php echo htmlspecialchars($i['email']); ?><br>
                                        <?php echo htmlspecialchars($i['telefono']); ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($i['horario']); ?></td>
                                <td><?php echo htmlspecialchars($i['forma_pago']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $i['estado'] === 'activa' ? 'success' : 
                                            ($i['estado'] === 'cancelada' ? 'danger' : 'warning'); 
                                    ?>">
                                        <?php echo htmlspecialchars($i['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info" 
                                                onclick="verDetalles(<?php echo $i['id']; ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($i['estado'] === 'activa'): ?>
                                        <button type="button" class="btn btn-danger" 
                                                onclick="cambiarEstado(<?php echo $i['id']; ?>, 'cancelada')">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal de detalles -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de la Inscripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function verDetalles(id) {
        fetch(`ajax/ver_detalles.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                document.querySelector('#detallesModal .modal-body').innerHTML = html;
                new bootstrap.Modal(document.getElementById('detallesModal')).show();
            });
    }

    function cambiarEstado(id, nuevoEstado) {
        if (!confirm('¿Estás seguro de que deseas cambiar el estado de esta inscripción?')) return;
        
        fetch('ajax/cambiar_estado.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error al cambiar el estado');
            }
        });
    }
    </script>
</body>
</html>
