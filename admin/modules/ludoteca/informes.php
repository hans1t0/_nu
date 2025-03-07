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
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$error_message = '';

// Añadir tipo de informe
$tipoInforme = isset($_GET['tipo']) ? $_GET['tipo'] : 'asistencia';
$tiposDisponibles = [
    'asistencia' => 'Informe de Asistencia',
    'alumnos' => 'Informe de Alumnos',
    'centros' => 'Distribución por Centros'
];

try {
    $conn = DatabaseConnectors::getConnection('ludoteca');
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
    error_log('Error de conexión a BD: ' . $dbError);
}

// Cargar lista de centros para filtros
$centros = [];
if ($dbConnected) {
    try {
        $centros = DatabaseConnectors::executeQuery('ludoteca', 
            "SELECT id, nombre FROM centros WHERE activo = 1 ORDER BY nombre"
        );
    } catch (Exception $e) {
        error_log("Error al cargar centros: " . $e->getMessage());
    }
}

// Configurar título y migas de pan
$pageTitle = $tiposDisponibles[$tipoInforme] ?? "Informes y Estadísticas";
$showBreadcrumb = true;
$breadcrumbItems = [
    ['text' => 'Ludoteca', 'url' => 'index.php', 'active' => false],
    ['text' => 'Informes', 'url' => 'informes.php', 'active' => false],
    ['text' => $tiposDisponibles[$tipoInforme] ?? '', 'url' => '', 'active' => true]
];

$basePath = '../../';
include_once __DIR__ . '/../../templates/module_header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h1><i class="bi bi-file-earmark-bar-graph text-primary"></i> <?= $pageTitle ?></h1>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al panel
                </a>
            </div>
        </div>
    </div>

    <?php if (!$dbConnected): ?>
        <div class="alert alert-danger">
            <h4 class="alert-heading">Error de conexión</h4>
            <p>No se pudo conectar a la base de datos.</p>
            <hr>
            <p class="mb-0">Error: <?= $dbError ?></p>
        </div>
    <?php else: ?>
        <!-- Selector de tipo de informe -->
        <div class="card mb-4">
            <div class="card-body bg-light">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-pills nav-fill">
                            <?php foreach ($tiposDisponibles as $key => $label): ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $tipoInforme == $key ? 'active' : '' ?>" 
                                   href="?tipo=<?= $key ?>&mes=<?= $mes ?>&anio=<?= $anio ?>&centro_id=<?= $centro_id ?>">
                                    <?= $label ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <input type="hidden" name="tipo" value="<?= $tipoInforme ?>">
                    
                    <div class="col-md-3">
                        <label for="mes" class="form-label">Mes</label>
                        <select class="form-select" id="mes" name="mes" onchange="this.form.submit()">
                            <?php
                            $meses = [
                                '01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo',
                                '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio',
                                '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre',
                                '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'
                            ];
                            foreach ($meses as $num => $nombre):
                            ?>
                                <option value="<?= $num ?>" <?= $mes == $num ? 'selected' : '' ?>><?= $nombre ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="anio" class="form-label">Año</label>
                        <select class="form-select" id="anio" name="anio" onchange="this.form.submit()">
                            <?php
                            $anioActual = date('Y');
                            for ($i = $anioActual - 2; $i <= $anioActual + 1; $i++):
                            ?>
                                <option value="<?= $i ?>" <?= $anio == $i ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label for="centro_id" class="form-label">Centro</label>
                        <select class="form-select" id="centro_id" name="centro_id" onchange="this.form.submit()">
                            <option value="">Todos los centros</option>
                            <?php foreach ($centros as $centro): ?>
                                <option value="<?= $centro['id'] ?>" <?= $centro_id == $centro['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($centro['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-2">
                        <label for="exportar" class="form-label">&nbsp;</label>
                        <div class="d-grid">
                            <button type="button" class="btn btn-success" onclick="exportarInforme('<?= $tipoInforme ?>')">
                                <i class="bi bi-file-earmark-excel"></i> Exportar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resumen general - Tarjetas de métricas -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <?php
                        $alumnosActivos = DatabaseConnectors::executeQuery('ludoteca',
                            "SELECT COUNT(*) as total FROM alumnos WHERE activo = 1" .
                            ($centro_id ? " AND centro_id = ?" : ""),
                            $centro_id ? [$centro_id] : []
                        )[0]['total'];
                        ?>
                        <h1 class="display-4 text-primary"><?= $alumnosActivos ?></h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-people-fill me-1"></i> Alumnos Activos
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <?php
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
                        ?>
                        <h1 class="display-4 text-success"><?= $asistenciaMedia ?></h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-calculator me-1"></i> Media Diaria
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <?php
                        $diasActividad = DatabaseConnectors::executeQuery('ludoteca',
                            "SELECT COUNT(DISTINCT DATE(fecha)) as dias 
                            FROM asistencia a 
                            JOIN inscripciones i ON a.inscripcion_id = i.id 
                            JOIN alumnos al ON i.alumno_id = al.id
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
                            ($centro_id ? " AND al.centro_id = ?" : ""),
                            $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
                        )[0]['dias'];
                        ?>
                        <h1 class="display-4 text-info"><?= $diasActividad ?></h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-calendar-event me-1"></i> Días de Actividad
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <?php
                        $totalAsistencias = DatabaseConnectors::executeQuery('ludoteca',
                            "SELECT COUNT(*) as total 
                            FROM asistencia a 
                            JOIN inscripciones i ON a.inscripcion_id = i.id 
                            JOIN alumnos al ON i.alumno_id = al.id
                            WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
                            ($centro_id ? " AND al.centro_id = ?" : ""),
                            $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
                        )[0]['total'];
                        ?>
                        <h1 class="display-4 text-warning"><?= $totalAsistencias ?></h1>
                        <p class="text-muted mb-0">
                            <i class="bi bi-check2-circle me-1"></i> Total Asistencias
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($tipoInforme == 'asistencia'): ?>
            <!-- INFORME DE ASISTENCIA -->
            <div class="row">
                <!-- Asistencia diaria -->
                <div class="col-md-8">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Asistencia Diaria - <?= $meses[$mes] ?> <?= $anio ?></h5>
                        </div>
                        <div class="card-body">
                            <canvas id="asistenciaChart" height="300"></canvas>
                            <?php
                            // Consulta corregida para cumplir con ONLY_FULL_GROUP_BY
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
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Distribución por días de la semana -->
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Asistencia por Día</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $asistenciaPorDia = DatabaseConnectors::executeQuery('ludoteca',
                                "SELECT 
                                    DAYOFWEEK(fecha) as dia_semana,
                                    COUNT(*) as total
                                FROM asistencia a 
                                JOIN inscripciones i ON a.inscripcion_id = i.id 
                                JOIN alumnos al ON i.alumno_id = al.id
                                WHERE MONTH(fecha) = ? AND YEAR(fecha) = ?" .
                                ($centro_id ? " AND al.centro_id = ?" : "") . "
                                GROUP BY DAYOFWEEK(fecha)
                                ORDER BY DAYOFWEEK(fecha)",
                                $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
                            );
                            
                            $diasSemana = [
                                1 => 'Domingo', 2 => 'Lunes', 3 => 'Martes', 4 => 'Miércoles',
                                5 => 'Jueves', 6 => 'Viernes', 7 => 'Sábado'
                            ];
                            
                            // Transformar datos para el gráfico
                            $labelsAsistenciaDia = [];
                            $valuesAsistenciaDia = [];
                            
                            foreach ($asistenciaPorDia as $asistencia) {
                                $labelsAsistenciaDia[] = $diasSemana[$asistencia['dia_semana']];
                                $valuesAsistenciaDia[] = $asistencia['total'];
                            }
                            ?>
                            <canvas id="asistenciaPorDiaChart" height="260"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($tipoInforme == 'alumnos'): ?>
            <!-- INFORME DE ALUMNOS -->
            <div class="row">
                <!-- Alumnos por edad -->
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Alumnos por Edad</h5>
                        </div>
                        <div class="card-body">
                            <?php
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
                            ?>
                            <canvas id="alumnosPorEdadChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Alumnos por género -->
                <div class="col-md-6">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Alumnos por Centro</h5>
                        </div>
                        <div class="card-body">
                            <?php
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
                            ?>
                            <canvas id="alumnosPorCursoChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- INFORME POR CENTROS -->
            <div class="row">
                <!-- Distribución por centros -->
                <div class="col-md-7">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Distribución por Centros</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $distribucionCentros = DatabaseConnectors::executeQuery('ludoteca',
                                "SELECT c.nombre, COUNT(DISTINCT a.id) as total 
                                FROM centros c 
                                JOIN alumnos a ON c.id = a.centro_id 
                                WHERE a.activo = 1 
                                GROUP BY c.id, c.nombre 
                                ORDER BY total DESC"
                            );
                            ?>
                            <canvas id="centrosChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Tabla de desglose por centros -->
                <div class="col-md-5">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">Desglose por Centros</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Centro</th>
                                        <th>Alumnos</th>
                                        <th>% del Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $totalAlumnos = array_sum(array_column($distribucionCentros, 'total'));
                                    foreach ($distribucionCentros as $centro): 
                                        $porcentaje = ($totalAlumnos > 0) ? round(($centro['total'] / $totalAlumnos) * 100, 1) : 0;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($centro['nombre']) ?></td>
                                            <td><?= $centro['total'] ?></td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: <?= $porcentaje ?>%;" 
                                                         aria-valuenow="<?= $porcentaje ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?= $porcentaje ?>%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td>Total</td>
                                        <td><?= $totalAlumnos ?></td>
                                        <td>100%</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabla de detalle -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Detalle de Asistencias</h5>
                <button class="btn btn-sm btn-light" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTable" aria-expanded="true">
                    <i class="bi bi-chevron-down"></i>
                </button>
            </div>
            <div class="collapse show" id="collapseTable">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Alumno</th>
                                    <th>Centro</th>
                                    <th>Entrada</th>
                                    <th>Salida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
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
                                    LIMIT 100",
                                    $centro_id ? [$mes, $anio, $centro_id] : [$mes, $anio]
                                );

                                if (empty($asistencias)):
                                ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-3">No hay registros de asistencia para el periodo seleccionado</td>
                                    </tr>
                                <?php else:
                                    foreach ($asistencias as $asistencia):
                                ?>
                                    <tr>
                                        <td><?= date('d/m/Y', strtotime($asistencia['fecha'])) ?></td>
                                        <td><?= htmlspecialchars($asistencia['apellidos'] . ', ' . $asistencia['nombre']) ?></td>
                                        <td><?= htmlspecialchars($asistencia['centro']) ?></td>
                                        <td><?= $asistencia['hora_entrada'] ? date('H:i', strtotime($asistencia['hora_entrada'])) : '-' ?></td>
                                        <td><?= $asistencia['hora_salida'] ? date('H:i', strtotime($asistencia['hora_salida'])) : '-' ?></td>
                                    </tr>
                                <?php 
                                    endforeach;
                                endif;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de asistencia diaria
    <?php if ($tipoInforme == 'asistencia' && !empty($asistenciaDiaria)): ?>
    const asistenciaData = <?= json_encode($asistenciaDiaria) ?>;
    const diasLabels = asistenciaData.map(item => item.dia.split('-')[2]); // Solo el día
    const asistenciaValues = asistenciaData.map(item => item.total);
    
    new Chart(document.getElementById('asistenciaChart'), {
        type: 'bar',
        data: {
            labels: diasLabels,
            datasets: [{
                label: 'Asistencias',
                data: asistenciaValues,
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Número de asistencias por día'
                }
            }
        }
    });

    // Asistencia por día de la semana
    const diasLabelsAsistDia = <?= json_encode($labelsAsistenciaDia ?? []) ?>;
    const valuesAsistenciaDia = <?= json_encode($valuesAsistenciaDia ?? []) ?>;
    
    new Chart(document.getElementById('asistenciaPorDiaChart'), {
        type: 'doughnut',
        data: {
            labels: diasLabelsAsistDia,
            datasets: [{
                data: valuesAsistenciaDia,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(201, 203, 207, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    <?php endif; ?>
    
    // Gráfico de alumnos por edad
    <?php if ($tipoInforme == 'alumnos' && !empty($alumnosPorEdad)): ?>
    const edadLabels = <?= json_encode(array_column($alumnosPorEdad, 'edad')) ?>;
    const edadValues = <?= json_encode(array_column($alumnosPorEdad, 'total')) ?>;
    
    new Chart(document.getElementById('alumnosPorEdadChart'), {
        type: 'bar',
        data: {
            labels: edadLabels.map(edad => edad + ' años'),
            datasets: [{
                label: 'Alumnos',
                data: edadValues,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Gráfico de alumnos por curso
    const cursoLabels = <?= json_encode(array_column($alumnosPorCurso ?? [], 'curso')) ?>;
    const cursoValues = <?= json_encode(array_column($alumnosPorCurso ?? [], 'total')) ?>;
    
    new Chart(document.getElementById('alumnosPorCursoChart'), {
        type: 'pie',
        data: {
            labels: cursoLabels,
            datasets: [{
                data: cursoValues,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    <?php endif; ?>
    
    // Gráfico de distribución por centros
    <?php if ($tipoInforme == 'centros' && !empty($distribucionCentros)): ?>
    const centrosLabels = <?= json_encode(array_column($distribucionCentros, 'nombre')) ?>;
    const centrosValues = <?= json_encode(array_column($distribucionCentros, 'total')) ?>;
    
    new Chart(document.getElementById('centrosChart'), {
        type: 'polarArea',
        data: {
            labels: centrosLabels,
            datasets: [{
                data: centrosValues,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    <?php endif; ?>
});

function exportarInforme(tipo) {
    // Construir URL con los parámetros actuales
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'excel');
    params.set('tipo', tipo);
    
    // Redirigir a la versión de exportación
    window.location.href = 'exportar_informe.php?' + params.toString();
}
</script>

<?php include_once __DIR__ . '/../../templates/footer.php'; ?>
