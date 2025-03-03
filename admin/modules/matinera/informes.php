<?php
// Incluir el archivo de conexión a la base de datos
require_once '../../../admin/database/DatabaseConnectors.php';

// Inicializar variables
$error_message = null;
$success_message = null;
$tipo_informe = isset($_GET['tipo']) ? $_GET['tipo'] : 'asistencia_diaria';
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
$centro_id = isset($_GET['centro']) ? $_GET['centro'] : '';
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'html';

// Validar formatos de fecha
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    $mes = date('Y-m');
}

// Lista de informes disponibles
$informes_disponibles = [
    'asistencia_diaria' => 'Informe de asistencia diaria',
    'asistencia_mensual' => 'Informe de asistencia mensual',
    'asistencia_centro' => 'Informe de asistencia por centro',
    'listado_alumnos' => 'Listado de alumnos',
    'facturacion' => 'Informe de facturación'
];

// Intentar establecer la conexión usando DatabaseConnectors
try {
    // Obtener la conexión 'matinera'
    $conn = DatabaseConnectors::getConnection('matinera');
    
    // Cargar lista de centros para el filtro
    $stmt = $conn->query("SELECT id, nombre FROM colegios ORDER BY nombre");
    $colegios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Generar datos del informe según el tipo seleccionado
    switch ($tipo_informe) {
        case 'asistencia_diaria':
            $titulo_informe = "Informe de Asistencia Diaria - " . formatFecha($fecha);
            
            $query = "
                SELECT h.nombre as alumno, r.nombre as responsable, r.telefono, c.nombre as centro,
                       a.asistio, a.desayuno, a.hora_entrada, a.observaciones
                FROM hijos h
                LEFT JOIN asistencias a ON h.id = a.hijo_id AND a.fecha = :fecha
                JOIN responsables r ON h.responsable_id = r.id
                JOIN colegios c ON h.colegio_id = c.id
            ";
            
            // Aplicar filtro de centro si está seleccionado
            $params = [':fecha' => $fecha];
            if (!empty($centro_id)) {
                $query .= " WHERE h.colegio_id = :centro_id";
                $params[':centro_id'] = $centro_id;
            }
            
            $query .= " ORDER BY c.nombre, h.nombre";
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $datos_informe = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agrupar por centro
            $datos_agrupados = [];
            foreach ($datos_informe as $row) {
                $centro = $row['centro'];
                if (!isset($datos_agrupados[$centro])) {
                    $datos_agrupados[$centro] = [];
                }
                $datos_agrupados[$centro][] = $row;
            }
            
            // Estadísticas
            $stmt = $conn->prepare("
                SELECT 
                    COUNT(*) as total_alumnos,
                    SUM(CASE WHEN asistio = 1 THEN 1 ELSE 0 END) as total_asistencias,
                    SUM(CASE WHEN desayuno = 1 THEN 1 ELSE 0 END) as total_desayunos
                FROM asistencias
                WHERE fecha = :fecha
            ");
            $stmt->execute([':fecha' => $fecha]);
            $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si no hay datos, inicializar con ceros
            if (!$estadisticas) {
                $estadisticas = [
                    'total_alumnos' => 0,
                    'total_asistencias' => 0,
                    'total_desayunos' => 0
                ];
            }
            break;
            
        case 'asistencia_mensual':
            $titulo_informe = "Informe de Asistencia Mensual - " . formatMes($mes);
            
            // Obtener todos los días del mes
            $primer_dia = $mes . '-01';
            $ultimo_dia = date('Y-m-t', strtotime($primer_dia));
            $dias = [];
            $fecha_actual = $primer_dia;
            
            while ($fecha_actual <= $ultimo_dia) {
                $dias[] = $fecha_actual;
                $fecha_actual = date('Y-m-d', strtotime($fecha_actual . ' +1 day'));
            }
            
            // Obtener todos los alumnos
            $query = "SELECT h.id, h.nombre, c.nombre as centro
                      FROM hijos h
                      JOIN colegios c ON h.colegio_id = c.id";
            
            // Aplicar filtro de centro si está seleccionado
            $params = [];
            if (!empty($centro_id)) {
                $query .= " WHERE h.colegio_id = :centro_id";
                $params[':centro_id'] = $centro_id;
            }
            
            $query .= " ORDER BY c.nombre, h.nombre";
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada alumno, obtener sus asistencias del mes
            $datos_informe = [];
            foreach ($alumnos as $alumno) {
                $alumno_asistencias = [];
                $alumno_asistencias['alumno'] = $alumno['nombre'];
                $alumno_asistencias['centro'] = $alumno['centro'];
                $alumno_asistencias['total_asistencias'] = 0;
                $alumno_asistencias['total_desayunos'] = 0;
                $alumno_asistencias['dias'] = [];
                
                // Consultar asistencias para cada día
                $stmt = $conn->prepare("
                    SELECT fecha, asistio, desayuno
                    FROM asistencias
                    WHERE hijo_id = :hijo_id AND fecha >= :fecha_inicial AND fecha <= :fecha_final
                ");
                $stmt->execute([
                    ':hijo_id' => $alumno['id'],
                    ':fecha_inicial' => $primer_dia,
                    ':fecha_final' => $ultimo_dia
                ]);
                
                $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $asistencias_por_fecha = [];
                
                foreach ($asistencias as $asistencia) {
                    $asistencias_por_fecha[$asistencia['fecha']] = [
                        'asistio' => $asistencia['asistio'],
                        'desayuno' => $asistencia['desayuno']
                    ];
                    
                    if ($asistencia['asistio']) {
                        $alumno_asistencias['total_asistencias']++;
                    }
                    if ($asistencia['desayuno']) {
                        $alumno_asistencias['total_desayunos']++;
                    }
                }
                
                // Crear registro para cada día del mes
                foreach ($dias as $dia) {
                    if (isset($asistencias_por_fecha[$dia])) {
                        $alumno_asistencias['dias'][$dia] = $asistencias_por_fecha[$dia];
                    } else {
                        $alumno_asistencias['dias'][$dia] = [
                            'asistio' => 0,
                            'desayuno' => 0
                        ];
                    }
                }
                
                $datos_informe[] = $alumno_asistencias;
            }
            
            // Agrupar por centro
            $datos_agrupados = [];
            foreach ($datos_informe as $row) {
                $centro = $row['centro'];
                if (!isset($datos_agrupados[$centro])) {
                    $datos_agrupados[$centro] = [];
                }
                $datos_agrupados[$centro][] = $row;
            }
            break;
            
        case 'asistencia_centro':
            if (empty($centro_id)) {
                $error_message = "Debe seleccionar un centro para generar este informe";
                break;
            }
            
            // Obtener nombre del centro
            $stmt = $conn->prepare("SELECT nombre FROM colegios WHERE id = :id");
            $stmt->execute([':id' => $centro_id]);
            $centro = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$centro) {
                $error_message = "Centro no encontrado";
                break;
            }
            
            $titulo_informe = "Informe de Asistencia - " . $centro['nombre'] . " - " . formatFecha($fecha);
            
            // Obtener alumnos del centro y sus asistencias para la fecha seleccionada
            $stmt = $conn->prepare("
                SELECT h.nombre as alumno, h.curso, r.nombre as responsable, r.telefono,
                       a.asistio, a.desayuno, a.hora_entrada, a.observaciones
                FROM hijos h
                LEFT JOIN asistencias a ON h.id = a.hijo_id AND a.fecha = :fecha
                JOIN responsables r ON h.responsable_id = r.id
                WHERE h.colegio_id = :centro_id
                ORDER BY h.curso, h.nombre
            ");
            $stmt->execute([':fecha' => $fecha, ':centro_id' => $centro_id]);
            $datos_informe = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agrupar por curso
            $datos_agrupados = [];
            $cursos_nombres = [
                '1INF' => '1º Infantil', '2INF' => '2º Infantil', '3INF' => '3º Infantil',
                '1PRIM' => '1º Primaria', '2PRIM' => '2º Primaria', '3PRIM' => '3º Primaria',
                '4PRIM' => '4º Primaria', '5PRIM' => '5º Primaria', '6PRIM' => '6º Primaria'
            ];
            
            foreach ($datos_informe as $row) {
                $curso = $row['curso'];
                $curso_nombre = $cursos_nombres[$curso] ?? $curso;
                if (!isset($datos_agrupados[$curso_nombre])) {
                    $datos_agrupados[$curso_nombre] = [];
                }
                $datos_agrupados[$curso_nombre][] = $row;
            }
            
            // Estadísticas por curso
            $estadisticas_cursos = [];
            foreach ($datos_agrupados as $curso => $alumnos) {
                $total_alumnos = count($alumnos);
                $asistencias = 0;
                $desayunos = 0;
                
                foreach ($alumnos as $alumno) {
                    if (isset($alumno['asistio']) && $alumno['asistio']) {
                        $asistencias++;
                    }
                    if (isset($alumno['desayuno']) && $alumno['desayuno']) {
                        $desayunos++;
                    }
                }
                
                $estadisticas_cursos[$curso] = [
                    'total_alumnos' => $total_alumnos,
                    'asistencias' => $asistencias,
                    'desayunos' => $desayunos
                ];
            }
            break;
            
        case 'listado_alumnos':
            $titulo_informe = "Listado de Alumnos";
            
            $query = "
                SELECT h.nombre as alumno, h.fecha_nacimiento, h.curso, c.nombre as centro,
                       r.nombre as responsable, r.dni, r.telefono, r.email
                FROM hijos h
                JOIN responsables r ON h.responsable_id = r.id
                JOIN colegios c ON h.colegio_id = c.id
            ";
            
            // Aplicar filtro de centro si está seleccionado
            $params = [];
            if (!empty($centro_id)) {
                $query .= " WHERE h.colegio_id = :centro_id";
                $params[':centro_id'] = $centro_id;
            }
            
            $query .= " ORDER BY c.nombre, h.curso, h.nombre";
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $datos_informe = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Agrupar por centro
            $datos_agrupados = [];
            foreach ($datos_informe as $row) {
                $centro = $row['centro'];
                if (!isset($datos_agrupados[$centro])) {
                    $datos_agrupados[$centro] = [];
                }
                $datos_agrupados[$centro][] = $row;
            }
            break;
            
        case 'facturacion':
            $titulo_informe = "Informe de Facturación - " . formatMes($mes);
            
            // Obtener todos los días del mes
            $primer_dia = $mes . '-01';
            $ultimo_dia = date('Y-m-t', strtotime($primer_dia));
            
            // Obtener responsables y sus hijos
            $query = "
                SELECT r.id as responsable_id, r.nombre as responsable, r.dni, r.forma_pago, r.iban,
                       COUNT(h.id) as num_hijos
                FROM responsables r
                JOIN hijos h ON r.id = h.responsable_id
            ";
            
            // Aplicar filtro de centro si está seleccionado
            $params = [];
            if (!empty($centro_id)) {
                $query .= " WHERE h.colegio_id = :centro_id";
                $params[':centro_id'] = $centro_id;
            }
            
            $query .= " GROUP BY r.id ORDER BY r.nombre";
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $responsables = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calcular asistencias y facturación para cada responsable
            $datos_informe = [];
            foreach ($responsables as $responsable) {
                // Obtener asistencias de todos los hijos del responsable en el mes
                $stmt = $conn->prepare("
                    SELECT a.hijo_id, COUNT(*) as dias_asistencia, 
                           SUM(CASE WHEN a.desayuno = 1 THEN 1 ELSE 0 END) as dias_desayuno
                    FROM asistencias a
                    JOIN hijos h ON a.hijo_id = h.id
                    WHERE h.responsable_id = :responsable_id
                    AND a.fecha BETWEEN :fecha_inicial AND :fecha_final
                    AND a.asistio = 1
                    GROUP BY a.hijo_id
                ");
                $stmt->execute([
                    ':responsable_id' => $responsable['responsable_id'],
                    ':fecha_inicial' => $primer_dia,
                    ':fecha_final' => $ultimo_dia
                ]);
                
                $asistencias_hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Calcular totales
                $total_dias = 0;
                $total_desayunos = 0;
                foreach ($asistencias_hijos as $asistencias) {
                    $total_dias += $asistencias['dias_asistencia'];
                    $total_desayunos += $asistencias['dias_desayuno'];
                }
                
                // Calcular importes (ejemplo: 3€ por día, 2€ adicionales por desayuno)
                $precio_dia = 3;
                $precio_desayuno = 2;
                $importe_dias = $total_dias * $precio_dia;
                $importe_desayunos = $total_desayunos * $precio_desayuno;
                $importe_total = $importe_dias + $importe_desayunos;
                
                // Añadir a los datos del informe
                $responsable['total_dias'] = $total_dias;
                $responsable['total_desayunos'] = $total_desayunos;
                $responsable['importe_dias'] = $importe_dias;
                $responsable['importe_desayunos'] = $importe_desayunos;
                $responsable['importe_total'] = $importe_total;
                
                $datos_informe[] = $responsable;
            }
            
            // Calcular totales generales
            $total_global_dias = array_sum(array_column($datos_informe, 'total_dias'));
            $total_global_desayunos = array_sum(array_column($datos_informe, 'total_desayunos'));
            $total_global_importe = array_sum(array_column($datos_informe, 'importe_total'));
            
            $totales_globales = [
                'total_dias' => $total_global_dias,
                'total_desayunos' => $total_global_desayunos,
                'total_importe' => $total_global_importe
            ];
            break;
            
        default:
            $error_message = "Tipo de informe no válido";
            break;
    }
    
    // Si se solicita exportación a CSV
    if ($formato === 'csv' && isset($datos_informe)) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $tipo_informe . '_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // CSV en UTF-8 con BOM
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Encabezados según el tipo de informe
        switch ($tipo_informe) {
            case 'asistencia_diaria':
                fputcsv($output, ['Centro', 'Alumno', 'Responsable', 'Teléfono', 'Asistió', 'Desayuno', 'Hora Entrada', 'Observaciones']);
                
                foreach ($datos_informe as $fila) {
                    fputcsv($output, [
                        $fila['centro'],
                        $fila['alumno'],
                        $fila['responsable'],
                        $fila['telefono'],
                        $fila['asistio'] ? 'Sí' : 'No',
                        $fila['desayuno'] ? 'Sí' : 'No',
                        $fila['hora_entrada'],
                        $fila['observaciones']
                    ]);
                }
                break;
                
            case 'listado_alumnos':
                fputcsv($output, ['Centro', 'Alumno', 'Fecha Nacimiento', 'Curso', 'Responsable', 'DNI', 'Teléfono', 'Email']);
                
                foreach ($datos_informe as $fila) {
                    fputcsv($output, [
                        $fila['centro'],
                        $fila['alumno'],
                        $fila['fecha_nacimiento'],
                        $fila['curso'],
                        $fila['responsable'],
                        $fila['dni'],
                        $fila['telefono'],
                        $fila['email']
                    ]);
                }
                break;
                
            case 'facturacion':
                fputcsv($output, ['Responsable', 'DNI', 'Forma Pago', 'IBAN', 'Nº Hijos', 'Total Días', 'Total Desayunos', 'Importe Días', 'Importe Desayunos', 'Importe Total']);
                
                foreach ($datos_informe as $fila) {
                    fputcsv($output, [
                        $fila['responsable'],
                        $fila['dni'],
                        $fila['forma_pago'],
                        $fila['iban'],
                        $fila['num_hijos'],
                        $fila['total_dias'],
                        $fila['total_desayunos'],
                        $fila['importe_dias'] . ' €',
                        $fila['importe_desayunos'] . ' €',
                        $fila['importe_total'] . ' €'
                    ]);
                }
                break;
        }
        
        fclose($output);
        exit;
    }
    
} catch (Exception $e) {
    $error_message = "Error al generar el informe: " . $e->getMessage();
}

// Funciones auxiliares
function formatFecha($fecha) {
    $timestamp = strtotime($fecha);
    return date('d/m/Y', $timestamp);
}

function formatMes($mes) {
    $timestamp = strtotime($mes . '-01');
    $meses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];
    $mes_numero = (int)date('n', $timestamp) - 1;
    $ano = date('Y', $timestamp);
    
    return $meses[$mes_numero] . ' ' . $ano;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes - Matinera</title>
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
        .informe-container {
            background-color: white;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            margin-top: 20px;
        }
        .informe-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dee2e6;
        }
        .informe-fecha {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .tabla-informe {
            width: 100%;
        }
        .grupo-header {
            background-color: #e9ecef;
            padding: 8px 15px;
            margin-top: 15px;
            margin-bottom: 10px;
            border-radius: 0.25rem;
            font-weight: 500;
        }
        .badge-asistencia {
            padding: 0.25em 0.6em;
            border-radius: 0.25rem;
            font-size: 75%;
        }
        .badge-si {
            background-color: #28a745;
            color: white;
        }
        .badge-no {
            background-color: #dc3545;
            color: white;
        }
        .calendario-mes {
            font-size: 0.8rem;
        }
        .calendario-dia {
            width: 25px;
            height: 25px;
            padding: 0;
            text-align: center;
            line-height: 25px;
        }
        .calendario-asistio {
            background-color: #28a745;
            color: white;
            border-radius: 50%;
        }
        .calendario-desayuno {
            position: relative;
        }
        .calendario-desayuno::after {
            content: "•";
            position: absolute;
            bottom: -8px;
            left: 50%;
            transform: translateX(-50%);
            color: #ffc107;
            font-size: 16px;
        }
        .border-bottom-dotted {
            border-bottom: 1px dotted #dee2e6;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .filtros-container {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 0.25rem;
            margin-bottom: 20px;
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
        
        <h1 class="mb-4">Informes</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Tipos de Informes</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php foreach ($informes_disponibles as $key => $nombre): ?>
                                <a href="?tipo=<?php echo $key; ?>" class="list-group-item list-group-item-action <?php echo ($tipo_informe === $key) ? 'active' : ''; ?>">
                                    <?php echo $nombre; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header">
                        <h5>Filtros y Opciones</h5>
                    </div>
                    <div class="card-body">
                        <form method="get" action="informes.php">
                            <input type="hidden" name="tipo" value="<?php echo $tipo_informe; ?>">
                            
                            <?php if (in_array($tipo_informe, ['asistencia_diaria', 'asistencia_centro'])): ?>
                                <div class="mb-3">
                                    <label for="fecha" class="form-label">Fecha</label>
                                    <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>">
                                </div>
                            <?php endif; ?>
                            
                            <?php if (in_array($tipo_informe, ['asistencia_mensual', 'facturacion'])): ?>
                                <div class="mb-3">
                                    <label for="mes" class="form-label">Mes</label>
                                    <input type="month" class="form-control" id="mes" name="mes" value="<?php echo $mes; ?>">
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($tipo_informe !== 'asistencia_centro'): ?>
                                <div class="mb-3">
                                    <label for="centro" class="form-label">Centro</label>
                                    <select class="form-select" id="centro" name="centro">
                                        <option value="">Todos los centros</option>
                                        <?php foreach ($colegios as $colegio): ?>
                                            <option value="<?php echo $colegio['id']; ?>" 
                                                <?php echo ($centro_id == $colegio['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($colegio['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <label for="centro" class="form-label">Centro</label>
                                    <select class="form-select" id="centro" name="centro" required>
                                        <?php foreach ($colegios as $colegio): ?>
                                            <option value="<?php echo $colegio['id']; ?>" 
                                                <?php echo ($centro_id == $colegio['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($colegio['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Aplicar Filtros
                                </button>
                            </div>
                        </form>
                        
                        <?php if (isset($datos_informe) && !empty($datos_informe)): ?>
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="?tipo=<?php echo $tipo_informe; ?>&fecha=<?php echo $fecha; ?>&mes=<?php echo $mes; ?>&centro=<?php echo $centro_id; ?>&formato=csv" class="btn btn-success">
                                    <i class="fas fa-file-csv"></i> Exportar a CSV
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-9">
                <?php if (isset($datos_informe) && !empty($datos_informe)): ?>
                    <div class="informe-container">
                        <div class="informe-header">
                            <h2><?php echo $titulo_informe; ?></h2>
                            <?php if (in_array($tipo_informe, ['asistencia_diaria', 'asistencia_centro'])): ?>
                                <p class="informe-fecha"><?php echo formatFecha($fecha); ?></p>
                            <?php elseif (in_array($tipo_informe, ['asistencia_mensual', 'facturacion'])): ?>
                                <p class="informe-fecha"><?php echo formatMes($mes); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($tipo_informe === 'asistencia_diaria'): ?>
                            <?php foreach ($datos_agrupados as $centro => $alumnos): ?>
                                <div class="grupo-header"><?php echo htmlspecialchars($centro); ?></div>
                                <table class="table tabla-informe">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Responsable</th>
                                            <th>Teléfono</th>
                                            <th>Asistió</th>
                                            <th>Desayuno</th>
                                            <th>Hora Entrada</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alumnos as $alumno): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($alumno['alumno']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['responsable']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['telefono']); ?></td>
                                                <td>
                                                    <span class="badge badge-asistencia <?php echo $alumno['asistio'] ? 'badge-si' : 'badge-no'; ?>">
                                                        <?php echo $alumno['asistio'] ? 'Sí' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-asistencia <?php echo $alumno['desayuno'] ? 'badge-si' : 'badge-no'; ?>">
                                                        <?php echo $alumno['desayuno'] ? 'Sí' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($alumno['hora_entrada']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['observaciones']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                            
                            <div class="border-bottom-dotted"></div>
                            <h4>Estadísticas</h4>
                            <p>Total Alumnos: <?php echo $estadisticas['total_alumnos']; ?></p>
                            <p>Total Asistencias: <?php echo $estadisticas['total_asistencias']; ?></p>
                            <p>Total Desayunos: <?php echo $estadisticas['total_desayunos']; ?></p>
                            
                        <?php elseif ($tipo_informe === 'asistencia_mensual'): ?>
                            <?php foreach ($datos_agrupados as $centro => $alumnos): ?>
                                <div class="grupo-header"><?php echo htmlspecialchars($centro); ?></div>
                                <table class="table tabla-informe">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Asistencias</th>
                                            <th>Desayunos</th>
                                            <th>Calendario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alumnos as $alumno): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($alumno['alumno']); ?></td>
                                                <td><?php echo $alumno['total_asistencias']; ?></td>
                                                <td><?php echo $alumno['total_desayunos']; ?></td>
                                                <td class="calendario-mes">
                                                    <?php foreach ($alumno['dias'] as $dia => $asistencia): ?>
                                                        <span class="calendario-dia <?php echo $asistencia['asistio'] ? 'calendario-asistio' : ''; ?> <?php echo $asistencia['desayuno'] ? 'calendario-desayuno' : ''; ?>">
                                                            <?php echo date('d', strtotime($dia)); ?>
                                                        </span>
                                                    <?php endforeach; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                            
                        <?php elseif ($tipo_informe === 'asistencia_centro'): ?>
                            <?php foreach ($datos_agrupados as $curso => $alumnos): ?>
                                <div class="grupo-header"><?php echo htmlspecialchars($curso); ?></div>
                                <table class="table tabla-informe">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Responsable</th>
                                            <th>Teléfono</th>
                                            <th>Asistió</th>
                                            <th>Desayuno</th>
                                            <th>Hora Entrada</th>
                                            <th>Observaciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alumnos as $alumno): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($alumno['alumno']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['responsable']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['telefono']); ?></td>
                                                <td>
                                                    <span class="badge badge-asistencia <?php echo $alumno['asistio'] ? 'badge-si' : 'badge-no'; ?>">
                                                        <?php echo $alumno['asistio'] ? 'Sí' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-asistencia <?php echo $alumno['desayuno'] ? 'badge-si' : 'badge-no'; ?>">
                                                        <?php echo $alumno['desayuno'] ? 'Sí' : 'No'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($alumno['hora_entrada']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['observaciones']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                            
                            <div class="border-bottom-dotted"></div>
                            <h4>Estadísticas por Curso</h4>
                            <?php foreach ($estadisticas_cursos as $curso => $estadisticas): ?>
                                <p><strong><?php echo htmlspecialchars($curso); ?></strong></p>
                                <p>Total Alumnos: <?php echo $estadisticas['total_alumnos']; ?></p>
                                <p>Total Asistencias: <?php echo $estadisticas['asistencias']; ?></p>
                                <p>Total Desayunos: <?php echo $estadisticas['desayunos']; ?></p>
                                <div class="border-bottom-dotted"></div>
                            <?php endforeach; ?>
                            
                        <?php elseif ($tipo_informe === 'listado_alumnos'): ?>
                            <?php foreach ($datos_agrupados as $centro => $alumnos): ?>
                                <div class="grupo-header"><?php echo htmlspecialchars($centro); ?></div>
                                <table class="table tabla-informe">
                                    <thead>
                                        <tr>
                                            <th>Alumno</th>
                                            <th>Fecha Nacimiento</th>
                                            <th>Curso</th>
                                            <th>Responsable</th>
                                            <th>DNI</th>
                                            <th>Teléfono</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alumnos as $alumno): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($alumno['alumno']); ?></td>
                                                <td><?php echo formatFecha($alumno['fecha_nacimiento']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['curso']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['responsable']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['dni']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['telefono']); ?></td>
                                                <td><?php echo htmlspecialchars($alumno['email']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endforeach; ?>
                            
                        <?php elseif ($tipo_informe === 'facturacion'): ?>
                            <table class="table tabla-informe">
                                <thead>
                                    <tr>
                                        <th>Responsable</th>
                                        <th>DNI</th>
                                        <th>Forma Pago</th>
                                        <th>IBAN</th>
                                        <th>Nº Hijos</th>
                                        <th>Total Días</th>
                                        <th>Total Desayunos</th>
                                        <th>Importe Días</th>
                                        <th>Importe Desayunos</th>
                                        <th>Importe Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datos_informe as $fila): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fila['responsable']); ?></td>
                                            <td><?php echo htmlspecialchars($fila['dni']); ?></td>
                                            <td><?php echo htmlspecialchars($fila['forma_pago']); ?></td>
                                            <td><?php echo htmlspecialchars($fila['iban']); ?></td>
                                            <td><?php echo $fila['num_hijos']; ?></td>
                                            <td><?php echo $fila['total_dias']; ?></td>
                                            <td><?php echo $fila['total_desayunos']; ?></td>
                                            <td><?php echo number_format($fila['importe_dias'], 2); ?> €</td>
                                            <td><?php echo number_format($fila['importe_desayunos'], 2); ?> €</td>
                                            <td><?php echo number_format($fila['importe_total'], 2); ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div class="border-bottom-dotted"></div>
                            <h4>Totales Generales</h4>
                            <p>Total Días: <?php echo $totales_globales['total_dias']; ?></p>
                            <p>Total Desayunos: <?php echo $totales_globales['total_desayunos']; ?></p>
                            <p>Total Importe: <?php echo number_format($totales_globales['total_importe'], 2); ?> €</p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" role="alert">
                        Seleccione un tipo de informe y aplique los filtros para generar el informe.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"></script>
</body>
</html>