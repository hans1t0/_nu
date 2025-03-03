<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../../admin/database/DatabaseConnectors.php';

// Inicializar variables
$error_message = null;
$success_message = null;
$alumnos = [];
$asistencias = [];
$colegios = [];
$fecha_actual = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$centro_id = isset($_GET['centro']) ? $_GET['centro'] : '';

// Validar formato de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_actual)) {
    $fecha_actual = date('Y-m-d'); // Si el formato es incorrecto, usar la fecha actual
}

// Intentar establecer la conexión usando DatabaseConnectors
try {
    // Obtener la conexión 'matinera'
    $conn = DatabaseConnectors::getConnection('matinera');
    
    // Cargar lista de centros para el filtro
    $stmt = $conn->query("SELECT id, nombre FROM colegios ORDER BY nombre");
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar acciones
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) { // Corregido: eliminar paréntesis extra
                case 'registrar_asistencia':
                    if (!empty($_POST['alumnos']) && isset($_POST['fecha'])) {
                        $fecha = $_POST['fecha'];
                        $registros_insertados = 0;
                        $registros_actualizados = 0;
                        
                        try {
                            $conn->beginTransaction();
                            
                            foreach ($_POST['alumnos'] as $alumno_id => $datos) {
                                // Preparar datos
                                $asistio = isset($datos['asistio']) ? 1 : 0;
                                $desayuno = isset($datos['desayuno']) ? 1 : 0;
                                $hora_entrada = !empty($datos['hora_entrada']) ? $datos['hora_entrada'] : null;
                                $observaciones = $datos['observaciones'] ?? null;
                                
                                // Verificar si ya existe un registro para este alumno en esta fecha
                                $stmt = $conn->prepare("SELECT id FROM asistencias WHERE hijo_id = :hijo_id AND fecha = :fecha");
                                $stmt->execute([':hijo_id' => $alumno_id, ':fecha' => $fecha]);
                                
                                if ($stmt->rowCount() > 0) {
                                    // Actualizar registro existente
                                    $registro_id = $stmt->fetchColumn();
                                    $stmt = $conn->prepare(
                                        "UPDATE asistencias SET 
                                        asistio = :asistio, 
                                        desayuno = :desayuno, 
                                        hora_entrada = :hora_entrada, 
                                        observaciones = :observaciones 
                                        WHERE id = :id"
                                    );
                                    $stmt->execute([
                                        ':asistio' => $asistio,
                                        ':desayuno' => $desayuno,
                                        ':hora_entrada' => $hora_entrada,
                                        ':observaciones' => $observaciones,
                                        ':id' => $registro_id
                                    ]);
                                    $registros_actualizados++;
                                } else {
                                    // Crear nuevo registro
                                    $stmt = $conn->prepare(
                                        "INSERT INTO asistencias 
                                        (hijo_id, fecha, asistio, desayuno, hora_entrada, observaciones, creado_por) 
                                        VALUES 
                                        (:hijo_id, :fecha, :asistio, :desayuno, :hora_entrada, :observaciones, :creado_por)"
                                    );
                                    $stmt->execute([
                                        ':hijo_id' => $alumno_id,
                                        ':fecha' => $fecha,
                                        ':asistio' => $asistio,
                                        ':desayuno' => $desayuno,
                                        ':hora_entrada' => $hora_entrada,
                                        ':observaciones' => $observaciones,
                                        ':creado_por' => 'sistema'
                                    ]);
                                    $registros_insertados++;
                                }
                            }
                            
                            $conn->commit();
                            
                            $mensaje = "Asistencia registrada: ";
                            if ($registros_insertados > 0) {
                                $mensaje .= "$registros_insertados nuevo(s) registro(s)";
                            }
                            if ($registros_actualizados > 0) {
                                $mensaje .= ($registros_insertados > 0 ? ", " : "") . "$registros_actualizados registro(s) actualizado(s)";
                            }
                            $success_message = $mensaje;
                            
                            // Actualizar fecha en la URL para reflejar la fecha registrada
                            $fecha_actual = $fecha;
                        } catch (PDOException $e) {
                            $conn->rollBack();
                            $error_message = "Error al registrar la asistencia: " . $e->getMessage();
                        }
                    } else {
                        $error_message = "No se recibieron datos de asistencia o fecha";
                    }
                    break;
            }
        }
    }
    
    // Cargar alumnos y sus asistencias para la fecha seleccionada
    $query = "
        SELECT h.id, h.nombre, h.desayuno as desayuno_habitual, h.hora_entrada as hora_habitual, 
               r.nombre as responsable_nombre, c.nombre as colegio_nombre, c.id as colegio_id,
               a.asistio, a.desayuno, a.hora_entrada, a.observaciones
        FROM hijos h
        JOIN responsables r ON h.responsable_id = r.id
        JOIN colegios c ON h.colegio_id = c.id
        LEFT JOIN asistencias a ON h.id = a.hijo_id AND a.fecha = :fecha
    ";
    
    // Aplicar filtro de centro si está seleccionado
    $params = [':fecha' => $fecha_actual];
    if (!empty($centro_id)) {
        $query .= " WHERE h.colegio_id = :centro_id";
        $params[':centro_id'] = $centro_id;
    }
    
    $query .= " ORDER BY c.nombre, h.nombre";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Agrupar alumnos por colegio para mejor visualización
    $alumnos_por_colegio = [];
    foreach ($alumnos as $alumno) {
        $colegio_id = $alumno['colegio_id'];
        $colegio_nombre = $alumno['colegio_nombre'];
        
        if (!isset($alumnos_por_colegio[$colegio_id])) {
            $alumnos_por_colegio[$colegio_id] = [
                'nombre' => $colegio_nombre,
                'alumnos' => []
            ];
        }
        
        $alumnos_por_colegio[$colegio_id]['alumnos'][] = $alumno;
    }
    
    // Obtener estadísticas para la fecha seleccionada
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_alumnos,
            SUM(CASE WHEN asistio = 1 THEN 1 ELSE 0 END) as total_asistencias,
            SUM(CASE WHEN desayuno = 1 THEN 1 ELSE 0 END) as total_desayunos
        FROM asistencias
        WHERE fecha = :fecha
    ");
    $stmt->execute([':fecha' => $fecha_actual]);
    $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Inicializar valores predeterminados para estadísticas si no hay registros
    if (!$estadisticas) {
        $estadisticas = [
            'total_alumnos' => 0,
            'total_asistencias' => 0,
            'total_desayunos' => 0
        ];
    }
    
} catch (Exception $e) {
    $error_message = "Error de conexión: " . $e->getMessage();
    // Inicializar arrays vacíos para evitar errores en la vista
    $alumnos_por_colegio = [];
    $estadisticas = [
        'total_alumnos' => 0,
        'total_asistencias' => 0,
        'total_desayunos' => 0
    ];
}

// Funciones auxiliares
function formatFecha($fecha) {
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

function diaSemana($fecha) {
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $timestamp = strtotime($fecha);
    return $dias[date('w', $timestamp)];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Asistencia - Matinera</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            padding-top: 70px;
            background-color: #f8f9fa;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }
        .card-header {
            background-color: #f1f1f1;
        }
        footer {
            margin-top: 30px;
            padding: 20px 0;
            text-align: center;
            background-color: #f1f1f1;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .fecha-navegacion {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .fecha-navegacion .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .fecha-actual {
            font-size: 1.2rem;
            font-weight: 500;
            margin: 0;
        }
        .dia-semana {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            min-width: 700px;
        }
        .form-check-input {
            width: 1.2em;
            height: 1.2em;
        }
        .btn-group-asistencia {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .estadisticas-asistencia {
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            padding: 10px;
        }
        .estadistica-item {
            text-align: center;
            padding: 10px;
            border-radius: 0.25rem;
        }
        .estadistica-valor {
            font-size: 1.8rem;
            font-weight: bold;
            display: block;
        }
        .estadistica-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .asistio-si {
            color: #28a745;
        }
        .asistio-no {
            color: #dc3545;
        }
        .colegio-header {
            background-color: #e9ecef;
            padding: 8px 15px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-radius: 0.25rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <h1 class="mb-3">Control de Asistencia</h1>
        
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                        <div class="fecha-navegacion">
                            <?php 
                                $fecha_anterior = date('Y-m-d', strtotime($fecha_actual . ' -1 day'));
                                $fecha_siguiente = date('Y-m-d', strtotime($fecha_actual . ' +1 day'));
                                $params = [];
                                if (!empty($centro_id)) {
                                    $params[] = "centro=" . $centro_id;
                                }
                                $query_string = !empty($params) ? '&' . implode('&', $params) : '';
                            ?>
                            <a href="?fecha=<?php echo $fecha_anterior . $query_string; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <div>
                                <div class="fecha-actual"><?php echo formatFecha($fecha_actual); ?></div>
                                <div class="dia-semana"><?php echo diaSemana($fecha_actual); ?></div>
                            </div>
                            <a href="?fecha=<?php echo $fecha_siguiente . $query_string; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                            <a href="?fecha=<?php echo date('Y-m-d') . $query_string; ?>" class="btn btn-outline-secondary ms-2">
                                Hoy
                            </a>
                        </div>
                        
                        <div class="mt-2 mt-md-0">
                            <form method="get" action="asistencia.php" class="d-flex gap-2">
                                <input type="hidden" name="fecha" value="<?php echo $fecha_actual; ?>">
                                <div class="flex-grow-1">
                                    <select name="centro" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="">Todos los centros</option>
                                        <?php foreach ($colegios as $colegio): ?>
                                            <option value="<?php echo $colegio['id']; ?>" 
                                                <?php echo ($centro_id == $colegio['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($colegio['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <form method="post" action="asistencia.php<?php echo (!empty($centro_id) ? "?centro=$centro_id&" : "?"); ?>fecha=<?php echo $fecha_actual; ?>">
                            <input type="hidden" name="action" value="registrar_asistencia">
                            <input type="hidden" name="fecha" value="<?php echo $fecha_actual; ?>">
                            
                            <?php if (empty($alumnos)): ?>
                                <div class="alert alert-info">
                                    No hay alumnos registrados
                                    <?php if (!empty($centro_id)): 
                                        $nombre_centro = "";
                                        foreach ($colegios as $colegio) {
                                            if ($colegio['id'] == $centro_id) {
                                                $nombre_centro = $colegio['nombre'];
                                                break;
                                            }
                                        }
                                    ?>
                                        para el centro: <?php echo htmlspecialchars($nombre_centro); ?>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="btn-group-asistencia mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="marcarTodos('asistencia', true)">
                                        <i class="fas fa-check"></i> Marcar todos asistencia
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="marcarTodos('asistencia', false)">
                                        <i class="fas fa-times"></i> Desmarcar todos
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info ms-2" onclick="marcarTodos('desayuno', true)">
                                        <i class="fas fa-coffee"></i> Marcar todos desayuno
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="marcarTodos('desayuno', false)">
                                        <i class="fas fa-times"></i> Desmarcar todos
                                    </button>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Alumno</th>
                                                <th class="text-center">Asistió</th>
                                                <th class="text-center">Desayuno</th>
                                                <th>Hora entrada</th>
                                                <th>Observaciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($alumnos_por_colegio as $colegio_id => $colegio_data): ?>
                                                <tr>
                                                    <td colspan="5" class="colegio-header">
                                                        <?php echo htmlspecialchars($colegio_data['nombre']); ?>
                                                    </td>
                                                </tr>
                                                <?php foreach ($colegio_data['alumnos'] as $alumno): ?>
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($alumno['nombre']); ?></strong>
                                                                <span class="text-muted d-block small">
                                                                    <?php echo htmlspecialchars($alumno['responsable_nombre']); ?>
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input check-asistencia" type="checkbox" name="alumnos[<?php echo $alumno['id']; ?>][asistio]" 
                                                                    <?php echo (isset($alumno['asistio']) && $alumno['asistio'] == 1) ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check d-flex justify-content-center">
                                                                <input class="form-check-input check-desayuno" type="checkbox" name="alumnos[<?php echo $alumno['id']; ?>][desayuno]" 
                                                                    <?php echo (isset($alumno['desayuno']) && $alumno['desayuno'] == 1) ? 'checked' : ''; ?> 
                                                                    <?php echo $alumno['desayuno_habitual'] ? 'data-habitual="1"' : ''; ?>>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="time" class="form-control form-control-sm" name="alumnos[<?php echo $alumno['id']; ?>][hora_entrada]" 
                                                                   value="<?php echo (!empty($alumno['hora_entrada'])) ? $alumno['hora_entrada'] : substr($alumno['hora_habitual'], 0, 5); ?>">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control form-control-sm" name="alumnos[<?php echo $alumno['id']; ?>][observaciones]" 
                                                                   value="<?php echo htmlspecialchars($alumno['observaciones'] ?? ''); ?>" placeholder="Observaciones">
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Asistencia
                                    </button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Estadísticas del día</h5>
                    </div>
                    <div class="card-body">
                        <div class="row estadisticas-asistencia g-3">
                            <div class="col-6">
                                <div class="estadistica-item bg-light">
                                    <span class="estadistica-valor">
                                        <?php echo (isset($estadisticas['total_asistencias'])) ? (int)$estadisticas['total_asistencias'] : 0; ?>
                                    </span>
                                    <span class="estadistica-label">
                                        Asistencias
                                    </span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="estadistica-item bg-light">
                                    <span class="estadistica-valor">
                                        <?php echo (isset($estadisticas['total_desayunos'])) ? (int)$estadisticas['total_desayunos'] : 0; ?>
                                    </span>
                                    <span class="estadistica-label">
                                        Desayunos
                                    </span>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="estadistica-item bg-light">
                                    <span class="estadistica-valor">
                                        <?php 
                                            $total_alumnos = count($alumnos);
                                            $total_asistencias = (isset($estadisticas['total_asistencias'])) ? (int)$estadisticas['total_asistencias'] : 0;
                                            $porcentaje = ($total_alumnos > 0) ? round(($total_asistencias / $total_alumnos) * 100) : 0;
                                            echo $porcentaje . '%';
                                        ?>
                                    </span>
                                    <span class="estadistica-label">
                                        Porcentaje de asistencia
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <h6>Informes</h6>
                            <div class="list-group">
                                <a href="informes.php?tipo=asistencia_diaria&fecha=<?php echo $fecha_actual; ?>" class="list-group-item list-group-item-action">
                                    <i class="fas fa-file-alt me-2"></i> Informe diario completo
                                </a>
                                <a href="informes.php?tipo=asistencia_mensual&mes=<?php echo substr($fecha_actual, 0, 7); ?>" class="list-group-item list-group-item-action">
                                    <i class="fas fa-calendar-alt me-2"></i> Informe mensual
                                </a>
                                <?php if (!empty($centro_id)): ?>
                                    <a href="informes.php?tipo=asistencia_centro&centro=<?php echo $centro_id; ?>&fecha=<?php echo $fecha_actual; ?>" class="list-group-item list-group-item-action">
                                        <i class="fas fa-school me-2"></i> Informe por centro
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Ayuda</h5>
                    </div>
                    <div class="card-body">
                        <p>Para registrar la asistencia:</p>
                        <ol>
                            <li>Marque la casilla <strong>Asistió</strong> para los alumnos presentes.</li>
                            <li>Marque la casilla <strong>Desayuno</strong> si tomaron desayuno.</li>
                            <li>Opcionalmente, ajuste la hora de entrada.</li>
                            <li>Puede agregar observaciones específicas.</li>
                            <li>Haga clic en <strong>Guardar Asistencia</strong> para registrar.</li>
                        </ol>
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle"></i> Los alumnos están agrupados por centro educativo para facilitar el registro.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión de Matinera</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Marcar o desmarcar todos los checkboxes
        function marcarTodos(tipo, valor) {
            let checkboxes;
            if (tipo === 'asistencia') {
                checkboxes = document.querySelectorAll('.check-asistencia');
            } else if (tipo === 'desayuno') {
                checkboxes = document.querySelectorAll('.check-desayuno');
            }
            
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = valor;
            });
        }
        
        // Marcar desayuno automáticamente cuando está configurado como habitual
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxesAsistencia = document.querySelectorAll('.check-asistencia');
            
            checkboxesAsistencia.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const alumnoId = this.name.match(/\[(\d+)\]/)[1];
                    const desayunoCheckbox = document.querySelector(`input[name="alumnos[${alumnoId}][desayuno]"]`);
                    
                    if (this.checked && desayunoCheckbox && desayunoCheckbox.getAttribute('data-habitual') === '1') {
                        desayunoCheckbox.checked = true;
                    } else if (!this.checked) {
                        desayunoCheckbox.checked = false;
                    }
                });
            });
        });
    </script>
</body>
</html>
