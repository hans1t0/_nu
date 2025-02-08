<?php
require_once '../includes/config.php';
require_once '../includes/db_functions.php';
session_start();

// Establecer conexión con la base de datos
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener fecha (hoy por defecto)
$fecha = $_GET['fecha'] ?? date('Y-m-d');
$colegio = $_GET['colegio'] ?? '';

// Si se envía el formulario de asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        foreach ($_POST['asistencia'] as $hijoId => $datos) {
            $stmt = $pdo->prepare("
                INSERT INTO asistencias (hijo_id, fecha, asistio, desayuno, hora_entrada, observaciones, creado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                asistio = VALUES(asistio),
                desayuno = VALUES(desayuno),
                hora_entrada = VALUES(hora_entrada),
                observaciones = VALUES(observaciones)
            ");
            
            $stmt->execute([
                $hijoId,
                $fecha,
                isset($datos['asistio']) ? 1 : 0,
                isset($datos['desayuno']) ? 1 : 0,
                $datos['hora_entrada'] ?? null,
                $datos['observaciones'] ?? null,
                $_SESSION['usuario'] ?? 'sistema'
            ]);
        }
        
        $pdo->commit();
        $mensaje = "Asistencias guardadas correctamente";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error al guardar asistencias: " . $e->getMessage();
    }
}

// Obtener lista de alumnos por colegio
$alumnos = $pdo->prepare("
    SELECT 
        h.id,
        h.nombre as alumno,
        h.curso,
        h.hora_entrada as hora_habitual,
        h.desayuno as desayuno_habitual,
        r.nombre as responsable,
        r.telefono,
        a.asistio,
        a.desayuno,
        a.hora_entrada,
        a.observaciones
    FROM hijos h
    JOIN responsables r ON h.responsable_id = r.id
    JOIN colegios c ON h.colegio_id = c.id
    LEFT JOIN asistencias a ON h.id = a.hijo_id AND a.fecha = ?
    WHERE c.codigo = ?
    ORDER BY h.curso, h.nombre
");

$alumnos->execute([$fecha, $colegio]);
$listaAlumnos = $alumnos->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - Guardería Matinal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .table-fixed { table-layout: fixed; }
        .table-fixed td { vertical-align: middle; }
        .attendance-row:hover { background-color: #f8f9fa; }
        .curso-header {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        /* Estilos para móviles */
        @media (max-width: 768px) {
            .mobile-hide {
                display: none !important;
            }
            .table-fixed td {
                padding: 0.5rem;
            }
            .table > :not(caption) > * > * {
                padding: 0.5rem;
            }
            .badge {
                font-size: 0.7rem;
            }
            .form-check-input {
                width: 1.5em;
                height: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <form class="d-flex gap-3">
                    <input type="date" name="fecha" value="<?= $fecha ?>" class="form-control w-auto">
                    <select name="colegio" class="form-control w-auto" required>
                        <option value="">Seleccione colegio</option>
                        <?php foreach (getColegios() as $col): ?>
                        <option value="<?= $col['codigo'] ?>" <?= $colegio === $col['codigo'] ? 'selected' : '' ?>>
                            <?= $col['nombre'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </form>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group">
                    <a href="?fecha=<?= date('Y-m-d', strtotime($fecha . ' -1 day')) ?>&colegio=<?= $colegio ?>" 
                       class="btn btn-outline-secondary">
                        <i class="bi bi-chevron-left"></i> Día anterior
                    </a>
                    <a href="?fecha=<?= date('Y-m-d', strtotime($fecha . ' +1 day')) ?>&colegio=<?= $colegio ?>" 
                       class="btn btn-outline-secondary">
                        Día siguiente <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php if ($colegio && $listaAlumnos): ?>
        <form method="post">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Asistencia <?= date('d/m/Y', strtotime($fecha)) ?></h5>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar Asistencias
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-fixed">
                            <thead>
                                <tr>
                                    <th style="width: 50%">Alumno</th>
                                    <th style="width: 25%">Asistencia</th>
                                    <th style="width: 25%">Desayuno</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $cursoActual = '';
                                foreach ($listaAlumnos as $alumno):
                                    if ($alumno['curso'] !== $cursoActual):
                                        $cursoActual = $alumno['curso'];
                                ?>
                                <tr class="curso-header">
                                    <td colspan="3"><?= $alumno['curso'] ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr class="attendance-row">
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?= htmlspecialchars($alumno['alumno']) ?></strong>
                                                <?php if ($alumno['desayuno_habitual']): ?>
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-cup-hot"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('H:i', strtotime($alumno['hora_habitual'])) ?>h
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input type="checkbox" 
                                                   class="form-check-input"
                                                   name="asistencia[<?= $alumno['id'] ?>][asistio]"
                                                   <?= $alumno['asistio'] ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check d-inline-block">
                                            <input type="checkbox" 
                                                   class="form-check-input"
                                                   name="asistencia[<?= $alumno['id'] ?>][desayuno]"
                                                   <?= $alumno['desayuno'] ? 'checked' : '' ?>
                                                   <?= !$alumno['desayuno_habitual'] ? 'disabled' : '' ?>>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
        <?php else: ?>
        <div class="alert alert-info">
            Seleccione un colegio para ver la lista de alumnos.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
