<?php
// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y cargar configuraciones
session_start();
require_once __DIR__ . '/../../database/DatabaseConnectors.php';
require_once __DIR__ . '/../../auth/check_session.php';

// Definir variables iniciales
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');
$centro_id = isset($_GET['centro_id']) ? $_GET['centro_id'] : '';
$tipoInforme = isset($_GET['tipo']) ? $_GET['tipo'] : 'asistencia';

// Nombres de los meses para los encabezados
$meses = [
    '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
    '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
    '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
    '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
];

try {
    $conn = DatabaseConnectors::getConnection('ludoteca');
    $dbConnected = true;
    
    // Obtener nombre del centro si se ha especificado
    $nombreCentro = '';
    if (!empty($centro_id)) {
        $centroData = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT nombre FROM centros WHERE id = ?", 
            [$centro_id]
        );
        if (!empty($centroData)) {
            $nombreCentro = $centroData[0]['nombre'];
        }
    }
    
    // Configurar el título del informe
    switch ($tipoInforme) {
        case 'alumnos':
            $tituloInforme = "Informe de Alumnos";
            break;
        case 'centros':
            $tituloInforme = "Distribución por Centros";
            break;
        default:
            $tituloInforme = "Informe de Asistencia";
    }
    
    // Añadir mes, año y centro al título
    $tituloCompleto = $tituloInforme . " - " . $meses[$mes] . " " . $anio;
    if (!empty($nombreCentro)) {
        $tituloCompleto .= " - Centro: " . $nombreCentro;
    }
    
    // Configurar los encabezados para la descarga del archivo Excel
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $tituloInforme . '_' . $anio . $mes . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Emitir cabecera para Excel
    echo '
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <style>
            table {border-collapse: collapse; border: 1px solid black;}
            th, td {border: 1px solid black; padding: 5px;}
            th {background-color: #dddddd; font-weight: bold; text-align: center;}
            .header {font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 10px;}
            .subheader {font-size: 14px; font-weight: bold; text-align: left; margin-top: 15px; margin-bottom: 5px;}
        </style>
    </head>
    <body>
        <div class="header">' . $tituloCompleto . '</div>
    ';
    
    // Generar el informe según el tipo seleccionado
    switch ($tipoInforme) {
        case 'asistencia':
            generarInformeAsistencia($mes, $anio, $centro_id);
            break;
        case 'alumnos':
            generarInformeAlumnos($centro_id);
            break;
        case 'centros':
            generarInformeCentros();
            break;
        default:
            generarInformeAsistencia($mes, $anio, $centro_id);
    }
    
    echo '</body></html>';
    
} catch (Exception $e) {
    // En caso de error, mostrar mensaje y terminar ejecución
    header('Content-Type: text/html; charset=utf-8');
    echo '<div style="color:red; font-weight:bold;">';
    echo 'Error al generar el informe: ' . $e->getMessage();
    echo '</div>';
    exit;
}

/**
 * Genera el informe de asistencia
 */
function generarInformeAsistencia($mes, $anio, $centro_id) {
    // 1. Resumen estadístico
    echo '<div class="subheader">Resumen Estadístico</div>';
    
    // Obtener estadísticas
    $alumnosActivos = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT COUNT(*) as total FROM alumnos WHERE activo = 1" .
        ($centro_id ? " AND centro_id = ?" : ""),
        $centro_id ? [$centro_id] : []
    )[0]['total'];
    
    $asistenciaMedia = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT ROUND(AVG(asistencias_dia), 1) as media FROM (
            SELECT DATE(fecha) as dia, COUNT(*) as asistencias_dia 
            FROM asistencia a 
            JOIN inscripciones i ON a.inscripcion_id = i.id 
            JOIN alumnos al ON i.alumno_id = al.id
            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
            ($centro_id ? " AND al.centro_id = ?" : "") . "
            GROUP BY DATE(fecha)
        ) t",
        $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
    )[0]['media'] ?: 0;
    
    $diasActividad = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT COUNT(DISTINCT DATE(fecha)) as dias 
        FROM asistencia a 
        JOIN inscripciones i ON a.inscripcion_id = i.id 
        JOIN alumnos al ON i.alumno_id = al.id
        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
        ($centro_id ? " AND al.centro_id = ?" : ""),
        $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
    )[0]['dias'];
    
    $totalAsistencias = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT COUNT(*) as total 
        FROM asistencia a 
        JOIN inscripciones i ON a.inscripcion_id = i.id 
        JOIN alumnos al ON i.alumno_id = al.id
        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
        ($centro_id ? " AND al.centro_id = ?" : ""),
        $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
    )[0]['total'];
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Alumnos Activos</th>';
    echo '<th>Media Diaria</th>';
    echo '<th>Días de Actividad</th>';
    echo '<th>Total Asistencias</th>';
    echo '</tr>';
    echo '<tr>';
    echo '<td style="text-align:center;">' . $alumnosActivos . '</td>';
    echo '<td style="text-align:center;">' . $asistenciaMedia . '</td>';
    echo '<td style="text-align:center;">' . $diasActividad . '</td>';
    echo '<td style="text-align:center;">' . $totalAsistencias . '</td>';
    echo '</tr>';
    echo '</table>';
    
    // 2. Tabla de asistencia diaria
    echo '<div class="subheader">Asistencia Diaria</div>';
    
    $asistenciaDiaria = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT 
            DATE(fecha) as dia, 
            COUNT(*) as total,
            MIN(fecha) as fecha_orden
        FROM asistencia a 
        JOIN inscripciones i ON a.inscripcion_id = i.id 
        JOIN alumnos al ON i.alumno_id = al.id
        WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
        ($centro_id ? " AND al.centro_id = ?" : "") . "
        GROUP BY DATE(fecha)
        ORDER BY fecha_orden",
        $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Día</th>';
    echo '<th>Total Asistencias</th>';
    echo '</tr>';
    
    if (empty($asistenciaDiaria)) {
        echo '<tr><td colspan="2" style="text-align:center;">No hay datos de asistencia para este período</td></tr>';
    } else {
        foreach ($asistenciaDiaria as $asistencia) {
            $fechaFormateada = date('d/m/Y', strtotime($asistencia['dia']));
            echo '<tr>';
            echo '<td>' . $fechaFormateada . '</td>';
            echo '<td style="text-align:center;">' . $asistencia['total'] . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    
    // 3. Tabla detallada de asistencias
    echo '<div class="subheader">Detalle de Asistencias</div>';
    
    $asistencias = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT 
            a.fecha,
            al.nombre,
            al.apellidos,
            c.nombre as centro,
            a.hora_entrada,
            a.hora_salida
        FROM asistencia a
        JOIN inscripciones i ON a.inscripcion_id = i.id
        JOIN alumnos al ON i.alumno_id = al.id
        LEFT JOIN centros c ON al.centro_id = c.id
        WHERE MONTH(a.fecha) = ? AND YEAR(a.fecha) = ?" .
        ($centro_id ? " AND al.centro_id = ?" : "") . "
        ORDER BY a.fecha DESC, a.hora_entrada DESC
        LIMIT 1000",
        $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Fecha</th>';
    echo '<th>Alumno</th>';
    echo '<th>Centro</th>';
    echo '<th>Entrada</th>';
    echo '<th>Salida</th>';
    echo '</tr>';
    
    if (empty($asistencias)) {
        echo '<tr><td colspan="5" style="text-align:center;">No hay registros de asistencia para este período</td></tr>';
    } else {
        foreach ($asistencias as $asistencia) {
            $fechaFormateada = date('d/m/Y', strtotime($asistencia['fecha']));
            $horaEntrada = $asistencia['hora_entrada'] ? date('H:i', strtotime($asistencia['hora_entrada'])) : '-';
            $horaSalida = $asistencia['hora_salida'] ? date('H:i', strtotime($asistencia['hora_salida'])) : '-';
            
            echo '<tr>';
            echo '<td>' . $fechaFormateada . '</td>';
            echo '<td>' . $asistencia['apellidos'] . ', ' . $asistencia['nombre'] . '</td>';
            echo '<td>' . $asistencia['centro'] . '</td>';
            echo '<td>' . $horaEntrada . '</td>';
            echo '<td>' . $horaSalida . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
}

/**
 * Genera el informe de alumnos
 */
function generarInformeAlumnos($centro_id) {
    // 1. Alumnos por edad
    echo '<div class="subheader">Distribución de Alumnos por Edad</div>';
    
    $alumnosPorEdad = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT 
            TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad,
            COUNT(*) as total
        FROM alumnos
        WHERE activo = 1 " .
        ($centro_id ? " AND centro_id = ?" : "") . "
        GROUP BY edad
        ORDER BY edad",
        $centro_id ? [$centro_id] : []
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Edad</th>';
    echo '<th>Cantidad de Alumnos</th>';
    echo '</tr>';
    
    if (empty($alumnosPorEdad)) {
        echo '<tr><td colspan="2" style="text-align:center;">No hay datos de alumnos</td></tr>';
    } else {
        foreach ($alumnosPorEdad as $grupo) {
            echo '<tr>';
            echo '<td>' . $grupo['edad'] . ' años</td>';
            echo '<td style="text-align:center;">' . $grupo['total'] . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    
    // 2. Alumnos por curso
    echo '<div class="subheader">Distribución de Alumnos por Curso</div>';
    
    $alumnosPorCurso = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT 
            curso,
            COUNT(*) as total
        FROM alumnos
        WHERE activo = 1 " .
        ($centro_id ? " AND centro_id = ?" : "") . "
        GROUP BY curso
        ORDER BY curso",
        $centro_id ? [$centro_id] : []
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Curso</th>';
    echo '<th>Cantidad de Alumnos</th>';
    echo '</tr>';
    
    if (empty($alumnosPorCurso)) {
        echo '<tr><td colspan="2" style="text-align:center;">No hay datos de alumnos</td></tr>';
    } else {
        foreach ($alumnosPorCurso as $grupo) {
            echo '<tr>';
            echo '<td>' . $grupo['curso'] . '</td>';
            echo '<td style="text-align:center;">' . $grupo['total'] . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
    
    // 3. Listado completo de alumnos
    echo '<div class="subheader">Listado de Alumnos</div>';
    
    $alumnos = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT a.*, c.nombre as centro_nombre
         FROM alumnos a
         LEFT JOIN centros c ON a.centro_id = c.id
         WHERE a.activo = 1 " .
        ($centro_id ? " AND a.centro_id = ?" : "") . "
        ORDER BY a.apellidos, a.nombre",
        $centro_id ? [$centro_id] : []
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Apellidos</th>';
    echo '<th>Nombre</th>';
    echo '<th>Fecha Nacimiento</th>';
    echo '<th>Edad</th>';
    echo '<th>Centro</th>';
    echo '<th>Curso</th>';
    echo '<th>Alergias</th>';
    echo '</tr>';
    
    if (empty($alumnos)) {
        echo '<tr><td colspan="7" style="text-align:center;">No hay alumnos registrados</td></tr>';
    } else {
        foreach ($alumnos as $alumno) {
            // Calcular edad
            $nacimiento = new DateTime($alumno['fecha_nacimiento']);
            $hoy = new DateTime();
            $edad = $hoy->diff($nacimiento)->y;
            
            echo '<tr>';
            echo '<td>' . $alumno['apellidos'] . '</td>';
            echo '<td>' . $alumno['nombre'] . '</td>';
            echo '<td>' . date('d/m/Y', strtotime($alumno['fecha_nacimiento'])) . '</td>';
            echo '<td style="text-align:center;">' . $edad . ' años</td>';
            echo '<td>' . $alumno['centro_nombre'] . '</td>';
            echo '<td>' . $alumno['curso'] . '</td>';
            echo '<td>' . $alumno['alergias'] . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
}

/**
 * Genera el informe de centros
 */
function generarInformeCentros() {
    // 1. Distribución de alumnos por centro
    echo '<div class="subheader">Distribución de Alumnos por Centro</div>';
    
    $distribucionCentros = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT c.nombre, COUNT(DISTINCT a.id) as total 
        FROM centros c 
        JOIN alumnos a ON c.id = a.centro_id 
        WHERE a.activo = 1 
        GROUP BY c.id, c.nombre 
        ORDER BY total DESC"
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Centro</th>';
    echo '<th>Alumnos</th>';
    echo '</tr>';
    
    if (empty($distribucionCentros)) {
        echo '<tr><td colspan="2" style="text-align:center;">No hay datos disponibles</td></tr>';
    } else {
        $totalAlumnos = array_sum(array_column($distribucionCentros, 'total'));
        
        foreach ($distribucionCentros as $centro) {
            $porcentaje = ($totalAlumnos > 0) ? round(($centro['total'] / $totalAlumnos) * 100, 1) : 0;
            
            echo '<tr>';
            echo '<td>' . $centro['nombre'] . '</td>';
            echo '<td style="text-align:center;">' . $centro['total'] . ' (' . $porcentaje . '%)</td>';
            echo '</tr>';
        }
        
        echo '<tr style="font-weight:bold;">';
        echo '<td>Total</td>';
        echo '<td style="text-align:center;">' . $totalAlumnos . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // 2. Listado de centros con número de alumnos activos
    echo '<div class="subheader">Detalle de Centros</div>';
    
    $centros = DatabaseConnectors::executeQuery('ludoteca',
        "SELECT c.*, 
         (SELECT COUNT(*) FROM alumnos a WHERE a.centro_id = c.id AND a.activo = 1) as alumnos_activos,
         (SELECT COUNT(*) FROM alumnos a WHERE a.centro_id = c.id) as total_alumnos
         FROM centros c
         ORDER BY c.nombre"
    );
    
    echo '<table>';
    echo '<tr>';
    echo '<th>Nombre del Centro</th>';
    echo '<th>Código</th>';
    echo '<th>Estado</th>';
    echo '<th>Alumnos Activos</th>';
    echo '<th>Total Alumnos</th>';
    echo '</tr>';
    
    if (empty($centros)) {
        echo '<tr><td colspan="5" style="text-align:center;">No hay centros registrados</td></tr>';
    } else {
        foreach ($centros as $centro) {
            echo '<tr>';
            echo '<td>' . $centro['nombre'] . '</td>';
            echo '<td style="text-align:center;">' . $centro['codigo'] . '</td>';
            echo '<td style="text-align:center;">' . ($centro['activo'] ? 'Activo' : 'Inactivo') . '</td>';
            echo '<td style="text-align:center;">' . $centro['alumnos_activos'] . '</td>';
            echo '<td style="text-align:center;">' . $centro['total_alumnos'] . '</td>';
            echo '</tr>';
        }
    }
    
    echo '</table>';
}