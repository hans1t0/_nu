<?php
require_once '../../auth/check_session.php';
require_once '../../database/DatabaseConnectors.php';
require_once './config.php';
require_once './includes/functions.php';
$pageTitle = "Panel de Ludoteca";

try {
    // Obtener estadísticas principales
    $db = DatabaseConnectors::getConnection(MODULE_DB);
    
    // Total de alumnos activos
    $stmt = $db->query("SELECT COUNT(*) as total FROM alumnos WHERE activo = 1");
    $totalAlumnos = $stmt->fetch()['total'];
    
    // Total de inscripciones activas
    $stmt = $db->query("SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'activa'");
    $totalInscripciones = $stmt->fetch()['total'];
    
    // Asistencias del día actual
    $fechaHoy = date('Y-m-d');
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM asistencia WHERE fecha = ?");
    $stmt->execute([$fechaHoy]);
    $asistenciasHoy = $stmt->fetch()['total'];
    
    // Obtener los centros y contar alumnos por cada uno
    $stmt = $db->query("
        SELECT c.nombre, COUNT(a.id) as total 
        FROM centros c 
        LEFT JOIN alumnos a ON c.id = a.centro_id 
        WHERE a.activo = 1 
        GROUP BY c.id
        ORDER BY total DESC
    ");
    $alumnosPorCentro = $stmt->fetchAll();
    
} catch (Exception $e) {
    $error = "Error al obtener datos: " . $e->getMessage();
}

include '../../templates/header.php';
?>

<div class="container mt-4">
    <h1>Panel de Control - <?php echo MODULE_NAME; ?></h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row mt-4">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Alumnos Activos</h5>
                    <p class="card-text display-4"><?php echo $totalAlumnos; ?></p>
                    <a href="./alumnos/index.php" class="btn btn-primary">Ver Alumnos</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Inscripciones Activas</h5>
                    <p class="card-text display-4"><?php echo $totalInscripciones; ?></p>
                    <a href="./inscripciones/index.php" class="btn btn-primary">Ver Inscripciones</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Asistencias Hoy</h5>
                    <p class="card-text display-4"><?php echo $asistenciasHoy; ?></p>
                    <a href="./asistencias/index.php" class="btn btn-primary">Registrar Asistencia</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Alumnos por Centro
                </div>
                <div class="card-body">
                    <?php if (!empty($alumnosPorCentro)): ?>
                        <canvas id="centrosChart"></canvas>
                    <?php else: ?>
                        <p>No hay datos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    Acciones Rápidas
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="./alumnos/nuevo.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-plus"></i> Nuevo Alumno
                        </a>
                        <a href="./inscripciones/nueva.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard-list"></i> Nueva Inscripción
                        </a>
                        <a href="./asistencias/registro_diario.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard-check"></i> Registro de Asistencia Diaria
                        </a>
                        <a href="./reportes/index.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-bar"></i> Generar Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($alumnosPorCentro)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Datos para el gráfico de centros
    const centrosData = {
        labels: [<?php echo implode(',', array_map(function($item) { return '"' . $item['nombre'] . '"'; }, $alumnosPorCentro)); ?>],
        datasets: [{
            label: 'Número de Alumnos',
            data: [<?php echo implode(',', array_map(function($item) { return $item['total']; }, $alumnosPorCentro)); ?>],
            backgroundColor: [
                'rgba(255, 99, 132, 0.7)',
                'rgba(54, 162, 235, 0.7)',
                'rgba(255, 206, 86, 0.7)',
                'rgba(75, 192, 192, 0.7)',
                'rgba(153, 102, 255, 0.7)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)'
            ],
            borderWidth: 1
        }]
    };
    
    // Configurar el gráfico
    const centrosChart = new Chart(
        document.getElementById('centrosChart'),
        {
            type: 'bar',
            data: centrosData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        }
    );
});
</script>
<?php endif; ?>

<?php
include '../../templates/footer.php';
?>
