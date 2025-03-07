<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Reportes - Escuela de Verano";
$currentSection = "reportes";

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';
$tipoReporte = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$semana = isset($_GET['semana']) ? $_GET['semana'] : '';
$periodosFechas = [];
$reporteGenerado = false;
$exportarPDF = isset($_GET['pdf']) && $_GET['pdf'] == '1';
$exportarExcel = isset($_GET['excel']) && $_GET['excel'] == '1';

// Recopilamos todas las semanas disponibles
try {
    // Modificamos la consulta para evitar el error con GROUP BY
    $query = "SELECT semana, MIN(fecha_inicio) as fecha_inicio, MAX(fecha_fin) as fecha_fin 
              FROM periodos_inscritos 
              GROUP BY semana 
              ORDER BY MIN(fecha_inicio)";
    $periodos = DatabaseConnectors::executeQuery('escuelaVerano', $query);
    
    foreach ($periodos as $periodo) {
        $nombre = "";
        switch ($periodo['semana']) {
            case 'julio1':
                $nombre = "Primera semana de julio";
                break;
            case 'julio2':
                $nombre = "Segunda semana de julio";
                break;
            case 'julio3':
                $nombre = "Tercera semana de julio";
                break;
            case 'julio4':
                $nombre = "Cuarta semana de julio";
                break;
            case 'julio5':
                $nombre = "Quinta semana de julio";
                break;
            case 'agosto1':
                $nombre = "Primera semana de agosto";
                break;
            case 'agosto2':
                $nombre = "Segunda semana de agosto";
                break;
            case 'agosto3':
                $nombre = "Tercera semana de agosto";
                break;
            case 'agosto4':
                $nombre = "Cuarta semana de agosto";
                break;
            case 'agosto5':
                $nombre = "Quinta semana de agosto";
                break;
            default:
                $nombre = $periodo['semana'];
        }
        
        $periodosFechas[$periodo['semana']] = [
            'nombre' => $nombre,
            'fecha_inicio' => $periodo['fecha_inicio'],
            'fecha_fin' => $periodo['fecha_fin']
        ];
    }
} catch (Exception $e) {
    $mensaje = "Error al cargar periodos: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Procesamos la generación de reportes
$datosReporte = [];
$tituloReporte = "";
$encabezadosReporte = [];

if (!empty($tipoReporte)) {
    try {
        switch ($tipoReporte) {
            case 'asistencia_semana':
                if (!empty($semana)) {
                    $tituloReporte = "Asistencia Semanal: " . ($periodosFechas[$semana]['nombre'] ?? $semana);
                    $encabezadosReporte = ['Nombre', 'Edad', 'Centro', 'Responsable', 'Teléfono', 'Alergias', 'Guardería', 'Comedor'];
                    
                    $query = "SELECT p.nombre, p.fecha_nacimiento, p.centro_actual, p.alergias,
                                r.nombre as responsable, r.telefono,
                                sc.guarderia_matinal, sc.comedor
                              FROM participantes p
                              JOIN periodos_inscritos pi ON p.id = pi.participante_id
                              JOIN responsables r ON p.responsable_id = r.id
                              LEFT JOIN servicios_contratados sc ON p.id = sc.participante_id
                              WHERE pi.semana = :semana
                              ORDER BY p.nombre";
                    $datosReporte = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':semana' => $semana]);
                    
                    // Calcular edad para cada participante
                    foreach ($datosReporte as &$participante) {
                        $fechaNac = new DateTime($participante['fecha_nacimiento']);
                        $hoy = new DateTime();
                        $participante['edad'] = $fechaNac->diff($hoy)->y . " años";
                        
                        // Formatear servicios
                        $participante['guarderia_matinal'] = $participante['guarderia_matinal'] ?: 'NO';
                        $participante['comedor'] = $participante['comedor'] ?: 'NO';
                    }
                    
                    $reporteGenerado = true;
                } else {
                    $mensaje = "Debe seleccionar una semana para generar el reporte";
                    $tipoMensaje = "warning";
                }
                break;
                
            case 'servicios':
                $tituloReporte = "Servicios Contratados";
                $encabezadosReporte = ['Nombre', 'Edad', 'Responsable', 'Socio AMPA', 'Guardería Matinal', 'Comedor'];
                
                $query = "SELECT p.nombre, p.fecha_nacimiento, r.nombre as responsable, 
                            sc.socio_ampa, sc.guarderia_matinal, sc.comedor
                          FROM participantes p
                          JOIN responsables r ON p.responsable_id = r.id
                          JOIN servicios_contratados sc ON p.id = sc.participante_id
                          ORDER BY p.nombre";
                $datosReporte = DatabaseConnectors::executeQuery('escuelaVerano', $query);
                
                // Calcular edad para cada participante
                foreach ($datosReporte as &$participante) {
                    $fechaNac = new DateTime($participante['fecha_nacimiento']);
                    $hoy = new DateTime();
                    $participante['edad'] = $fechaNac->diff($hoy)->y . " años";
                    
                    // Formatear servicios
                    $participante['guarderia_matinal'] = $participante['guarderia_matinal'] ?: 'NO';
                }
                
                $reporteGenerado = true;
                break;
                
            case 'guarderia':
                $tituloReporte = "Servicio de Guardería Matinal";
                $encabezadosReporte = ['Nombre', 'Edad', 'Semanas', 'Hora Entrada', 'Responsable', 'Teléfono', 'Alergias'];
                
                // Modificamos la consulta para manejar correctamente GROUP BY
                $query = "SELECT p.nombre, p.fecha_nacimiento, p.alergias, 
                            GROUP_CONCAT(DISTINCT pi.semana ORDER BY pi.semana SEPARATOR ', ') as semanas,
                            sc.guarderia_matinal, r.nombre as responsable, r.telefono
                          FROM participantes p
                          JOIN responsables r ON p.responsable_id = r.id
                          JOIN periodos_inscritos pi ON p.id = pi.participante_id
                          JOIN servicios_contratados sc ON p.id = sc.participante_id
                          WHERE sc.guarderia_matinal IS NOT NULL AND sc.guarderia_matinal != 'NO'
                          GROUP BY p.id, p.nombre, p.fecha_nacimiento, p.alergias, sc.guarderia_matinal, r.nombre, r.telefono
                          ORDER BY p.nombre";
                $datosReporte = DatabaseConnectors::executeQuery('escuelaVerano', $query);
                
                // Calcular edad y formatear semanas para cada participante
                foreach ($datosReporte as &$participante) {
                    $fechaNac = new DateTime($participante['fecha_nacimiento']);
                    $hoy = new DateTime();
                    $participante['edad'] = $fechaNac->diff($hoy)->y . " años";
                    
                    // Formatear las semanas
                    $semanasArray = explode(', ', $participante['semanas']);
                    $semanasFormateadas = [];
                    foreach ($semanasArray as $sem) {
                        if (isset($periodosFechas[$sem])) {
                            $semanasFormateadas[] = $periodosFechas[$sem]['nombre'];
                        } else {
                            $semanasFormateadas[] = $sem;
                        }
                    }
                    $participante['semanas'] = implode(', ', $semanasFormateadas);
                }
                
                $reporteGenerado = true;
                break;
                
            case 'comedor':
                $tituloReporte = "Servicio de Comedor";
                $encabezadosReporte = ['Nombre', 'Edad', 'Semanas', 'Alergias', 'Responsable', 'Teléfono'];
                
                // Modificamos la consulta para manejar correctamente GROUP BY
                $query = "SELECT p.nombre, p.fecha_nacimiento, p.alergias, 
                            GROUP_CONCAT(DISTINCT pi.semana ORDER BY pi.semana SEPARATOR ', ') as semanas,
                            r.nombre as responsable, r.telefono
                          FROM participantes p
                          JOIN responsables r ON p.responsable_id = r.id
                          JOIN periodos_inscritos pi ON p.id = pi.participante_id
                          JOIN servicios_contratados sc ON p.id = sc.participante_id
                          WHERE sc.comedor = 'SI'
                          GROUP BY p.id, p.nombre, p.fecha_nacimiento, p.alergias, r.nombre, r.telefono
                          ORDER BY p.nombre";
                $datosReporte = DatabaseConnectors::executeQuery('escuelaVerano', $query);
                
                // Calcular edad y formatear semanas para cada participante
                foreach ($datosReporte as &$participante) {
                    $fechaNac = new DateTime($participante['fecha_nacimiento']);
                    $hoy = new DateTime();
                    $participante['edad'] = $fechaNac->diff($hoy)->y . " años";
                    
                    // Formatear las semanas
                    $semanasArray = explode(', ', $participante['semanas']);
                    $semanasFormateadas = [];
                    foreach ($semanasArray as $sem) {
                        if (isset($periodosFechas[$sem])) {
                            $semanasFormateadas[] = $periodosFechas[$sem]['nombre'];
                        } else {
                            $semanasFormateadas[] = $sem;
                        }
                    }
                    $participante['semanas'] = implode(', ', $semanasFormateadas);
                }
                
                $reporteGenerado = true;
                break;
                
            case 'resumen':
                $tituloReporte = "Resumen General";
                $encabezadosReporte = ['Concepto', 'Cantidad'];
                
                // Total de participantes
                $queryParticipantes = "SELECT COUNT(*) as total FROM participantes";
                $totalParticipantes = DatabaseConnectors::executeQuery('escuelaVerano', $queryParticipantes)[0]['total'];
                
                // Total por semanas - Modificamos la consulta para agrupar correctamente
                $queryPorSemana = "SELECT semana, COUNT(DISTINCT participante_id) as total 
                                   FROM periodos_inscritos 
                                   GROUP BY semana
                                   ORDER BY MIN(fecha_inicio)";
                $totalesPorSemana = DatabaseConnectors::executeQuery('escuelaVerano', $queryPorSemana);
                
                // Servicios
                $queryServicios = "SELECT 
                                    SUM(CASE WHEN socio_ampa = 'SI' THEN 1 ELSE 0 END) as total_ampa,
                                    SUM(CASE WHEN guarderia_matinal IS NOT NULL AND guarderia_matinal != 'NO' THEN 1 ELSE 0 END) as total_guarderia,
                                    SUM(CASE WHEN comedor = 'SI' THEN 1 ELSE 0 END) as total_comedor
                                  FROM servicios_contratados";
                $servicios = DatabaseConnectors::executeQuery('escuelaVerano', $queryServicios)[0];
                
                // Preparar los datos del reporte
                $datosReporte = [
                    ['concepto' => 'Total participantes', 'cantidad' => $totalParticipantes],
                    ['concepto' => 'Socios AMPA', 'cantidad' => $servicios['total_ampa']],
                    ['concepto' => 'Guardería matinal', 'cantidad' => $servicios['total_guarderia']],
                    ['concepto' => 'Comedor', 'cantidad' => $servicios['total_comedor']]
                ];
                
                // Añadir totales por semana
                foreach ($totalesPorSemana as $total) {
                    $nombreSemana = isset($periodosFechas[$total['semana']]) ? $periodosFechas[$total['semana']]['nombre'] : $total['semana'];
                    $datosReporte[] = ['concepto' => $nombreSemana, 'cantidad' => $total['total']];
                }
                
                $reporteGenerado = true;
                break;
                
            default:
                $mensaje = "Tipo de reporte no válido.";
                $tipoMensaje = "warning";
                break;
        }
    } catch (Exception $e) {
        $mensaje = "Error al generar el reporte: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Si se solicita un PDF, generamos y enviamos el PDF
if ($exportarPDF && $reporteGenerado) {
    // Aquí iría la generación del PDF usando una librería como TCPDF o FPDF
    // Por ahora, simplemente redireccionar con un mensaje
    $mensaje = "La exportación a PDF estará disponible próximamente.";
    $tipoMensaje = "info";
}

// Si se solicita un Excel, generamos y enviamos el Excel
if ($exportarExcel && $reporteGenerado) {
    // Aquí iría la generación del Excel usando una librería como PhpSpreadsheet
    // Por ahora, simplemente redireccionar con un mensaje
    $mensaje = "La exportación a Excel estará disponible próximamente.";
    $tipoMensaje = "info";
}

// Incluimos el header
include('includes/header.php');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1><i class="fas fa-chart-bar mr-2"></i> Reportes y Estadísticas</h1>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-3">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Reportes disponibles</h5>
            </div>
            <div class="card-body">
                <div class="nav flex-column nav-pills">
                    <a href="?tipo=resumen" class="nav-link <?php echo ($tipoReporte === 'resumen') ? 'active' : ''; ?>">
                        <i class="fas fa-chart-pie mr-2"></i> Resumen general
                    </a>
                    <a href="?tipo=asistencia_semana" class="nav-link <?php echo ($tipoReporte === 'asistencia_semana') ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-check mr-2"></i> Asistencia por semana
                    </a>
                    <a href="?tipo=servicios" class="nav-link <?php echo ($tipoReporte === 'servicios') ? 'active' : ''; ?>">
                        <i class="fas fa-concierge-bell mr-2"></i> Servicios contratados
                    </a>
                    <a href="?tipo=guarderia" class="nav-link <?php echo ($tipoReporte === 'guarderia') ? 'active' : ''; ?>">
                        <i class="fas fa-sun mr-2"></i> Guardería matinal
                    </a>
                    <a href="?tipo=comedor" class="nav-link <?php echo ($tipoReporte === 'comedor') ? 'active' : ''; ?>">
                        <i class="fas fa-utensils mr-2"></i> Comedor
                    </a>
                </div>
            </div>
        </div>
        
        <?php if ($tipoReporte === 'asistencia_semana'): ?>
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Seleccionar semana</h5>
                </div>
                <div class="card-body">
                    <form action="" method="get">
                        <input type="hidden" name="tipo" value="asistencia_semana">
                        <div class="form-group">
                            <label for="semana">Semana:</label>
                            <select name="semana" id="semana" class="form-control" required onchange="this.form.submit()">
                                <option value="">Seleccione una semana</option>
                                <?php foreach ($periodosFechas as $codigo => $periodo): ?>
                                    <option value="<?php echo $codigo; ?>" <?php echo ($semana === $codigo) ? 'selected' : ''; ?>>
                                        <?php echo $periodo['nombre']; ?>
                                        (<?php echo date('d/m/Y', strtotime($periodo['fecha_inicio'])); ?> - 
                                        <?php echo date('d/m/Y', strtotime($periodo['fecha_fin'])); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="col-md-9">
        <?php if ($reporteGenerado): ?>
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><?php echo $tituloReporte; ?></h5>
                    <div>
                        <a href="?tipo=<?php echo $tipoReporte; ?>&semana=<?php echo $semana; ?>&pdf=1" class="btn btn-sm btn-light">
                            <i class="fas fa-file-pdf"></i> Exportar PDF
                        </a>
                        <a href="?tipo=<?php echo $tipoReporte; ?>&semana=<?php echo $semana; ?>&excel=1" class="btn btn-sm btn-light ml-2">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($datosReporte)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <?php foreach ($encabezadosReporte as $encabezado): ?>
                                            <th><?php echo $encabezado; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($datosReporte as $fila): ?>
                                        <tr>
                                            <?php foreach ($encabezadosReporte as $key => $encabezado): ?>
                                                <?php 
                                                // Convertir el encabezado a clave para acceder a los datos
                                                $clave = strtolower(str_replace(' ', '_', $encabezado));
                                                if ($tipoReporte === 'resumen') {
                                                    $clave = ($key === 0) ? 'concepto' : 'cantidad';
                                                }
                                                $valorCelda = isset($fila[$clave]) ? $fila[$clave] : '';
                                                
                                                // Resaltar alergias
                                                $claseEspecial = '';
                                                if ($encabezado === 'Alergias' && !empty($valorCelda) && $valorCelda != '-') {
                                                    $claseEspecial = 'bg-light text-danger font-weight-bold';
                                                }
                                                ?>
                                                <td class="<?php echo $claseEspecial; ?>">
                                                    <?php echo $valorCelda; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">Total registros: <?php echo count($datosReporte); ?></small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No hay datos disponibles para este reporte.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif (empty($tipoReporte)): ?>
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-line fa-5x text-primary mb-4"></i>
                    <h4>Seleccione un reporte</h4>
                    <p class="text-muted">Elija el tipo de reporte que desea generar en el panel izquierdo.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Incluimos el footer
include('includes/footer.php');
?>
