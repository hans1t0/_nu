<?php
session_start();
require_once 'includes/db_connect.php';

// Obtener fecha actual o la seleccionada
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$centro_id = isset($_GET['centro_id']) ? (int)$_GET['centro_id'] : 0;

// Obtener lista de centros activos
$centros = $pdo->query("SELECT id, nombre FROM centros WHERE activo = 1 ORDER BY nombre")->fetchAll();

// Construir consulta con filtros
$where = ['i.estado = "activa"'];
$params = ['fecha' => $fecha];

if ($centro_id > 0) {
    $where[] = "c.id = :centro_id";
    $params['centro_id'] = $centro_id;
}

$whereClause = 'WHERE ' . implode(' AND ', $where);

// Obtener lista de alumnos activos con sus inscripciones
$stmt = $pdo->prepare("
    SELECT 
        a.id as alumno_id,
        a.nombre,
        a.apellidos,
        c.nombre as centro,
        i.id as inscripcion_id,
        h.descripcion as horario,
        h.hora_fin,
        CASE WHEN ast.id IS NOT NULL THEN 1 ELSE 0 END as asistio
    FROM alumnos a
    JOIN inscripciones i ON a.id = i.alumno_id
    JOIN centros c ON a.centro_id = c.id
    JOIN horarios h ON i.horario_id = h.id
    LEFT JOIN asistencia ast ON i.id = ast.inscripcion_id AND ast.fecha = :fecha
    $whereClause
    ORDER BY c.nombre, h.hora_fin, a.apellidos, a.nombre
");
$stmt->execute($params);
$alumnos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia - Ludoteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
    </style>
</head>
<body>
    <main class="container py-4">
        <!-- Formulario de filtros separado -->
        <form id="filtrosForm" class="mb-3" method="GET">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h1 class="h4 mb-0">Control de Asistencia</h1>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <select name="centro_id" class="form-select" onchange="aplicarFiltros()">
                                <option value="">Todos los centros</option>
                                <?php foreach ($centros as $centro): ?>
                                    <option value="<?php echo $centro['id']; ?>" 
                                            <?php echo ($centro['id'] == $centro_id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($centro['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="date" class="form-control" id="fechaAsistencia" 
                                   name="fecha" value="<?php echo $fecha; ?>"
                                   onchange="aplicarFiltros()">
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Formulario de asistencia separado -->
        <form id="asistenciaForm" method="POST" action="procesar_asistencia.php">
            <div class="card shadow">
                <div class="card-body">
                    <?php if (isset($_SESSION['mensaje'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $_SESSION['mensaje']; unset($_SESSION['mensaje']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Asistencia</th>
                                    <th>Centro</th>
                                    <th>Alumno</th>
                                    <th>Horario</th>
                                    <th>Hora Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alumnos as $alumno): ?>
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input" 
                                                   name="asistencias[]" 
                                                   value="<?php echo $alumno['inscripcion_id']; ?>"
                                                   <?php echo $alumno['asistio'] ? 'checked' : ''; ?>>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($alumno['centro']); ?></td>
                                    <td><?php echo htmlspecialchars($alumno['apellidos'] . ', ' . $alumno['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($alumno['horario']); ?></td>
                                    <td><?php echo substr($alumno['hora_fin'], 0, 5); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <input type="hidden" name="fecha" value="<?php echo $fecha; ?>">
                    <input type="hidden" name="centro_id" value="<?php echo $centro_id; ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar Asistencias
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function aplicarFiltros() {
        document.getElementById('filtrosForm').submit();
    }
    </script>
</body>
</html>
