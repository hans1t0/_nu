<?php
// Iniciar sesión para gestión de usuarios (podrá expandirse más adelante)
session_start();

// Cargar configuración de base de datos
require_once __DIR__ . '/database/DatabaseConnectors.php';

// Comprobar si existe la estructura de directorios necesaria
$requiredDirs = [
    'database', 'modules', 'templates', 'assets'
];

foreach ($requiredDirs as $dir) {
    if (!file_exists(__DIR__ . '/' . $dir)) {
        mkdir(__DIR__ . '/' . $dir, 0755, true);
    }
}

// Definir servicios disponibles
$servicios = [
    'matinera' => [
        'nombre' => 'Guardería Matinal',
        'icono' => 'sunrise',
        'color' => 'success'
    ],
    'ludoteca' => [
        'nombre' => 'Ludoteca',
        'icono' => 'controller',
        'color' => 'primary'
    ],
    'escuelaVerano' => [
        'nombre' => 'Escuela de Verano',
        'icono' => 'sun',
        'color' => 'warning'
    ],
    'extraescolares' => [
        'nombre' => 'Act. Extraescolares',
        'icono' => 'stopwatch',
        'color' => 'purple'
    ]
];

// Título de la página
$pageTitle = "Panel de Administración";

// Incluir el encabezado (lo crearemos en el siguiente paso)
include_once __DIR__ . '/templates/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h1 class="display-5">Panel de Administración</h1>
                    <p class="lead">Bienvenido al sistema centralizado de gestión escolar</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Estado de las conexiones</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Base de datos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio => $detalles): ?>
                                    <?php
                                    // Intentar verificar la conexión
                                    $estado = true;
                                    $mensaje = "Conectado";
                                    
                                    try {
                                        DatabaseConnectors::getConnection($servicio);
                                    } catch (Exception $e) {
                                        $estado = false;
                                        $mensaje = "Error: " . $e->getMessage();
                                    }
                                    ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-<?= $detalles['icono'] ?> text-<?= $detalles['color'] ?>"></i>
                                            <?= $detalles['nombre'] ?>
                                        </td>
                                        <td>
                                            <?php if ($estado): ?>
                                                <span class="badge bg-success">Conectado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Error</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $dbname = "";
                                            switch ($servicio) {
                                                case 'matinera': $dbname = 'guarderia_matinal'; break;
                                                case 'ludoteca': $dbname = 'ludoteca_db'; break;
                                                case 'escuelaVerano': $dbname = 'escuela_verano'; break;
                                                case 'extraescolares': $dbname = 'actividades_escolares'; break;
                                            }
                                            echo $dbname;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="modules/<?= $servicio ?>" class="btn btn-sm btn-outline-<?= $detalles['color'] ?>" <?= !$estado ? 'disabled' : '' ?>>
                                                Acceder
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php foreach ($servicios as $servicio => $detalles): ?>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-<?= $detalles['color'] ?> text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-<?= $detalles['icono'] ?>"></i>
                            <?= $detalles['nombre'] ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <p>Acceda al módulo de <?= $detalles['nombre'] ?> para gestionar sus funcionalidades.</p>
                        
                        <?php
                        // Intentamos mostrar algunos datos básicos
                        try {
                            switch ($servicio) {
                                case 'matinera':
                                    $count = DatabaseConnectors::executeQuery('matinera', "SELECT COUNT(*) as total FROM alumnos")[0]['total'] ?? 0;
                                    echo "<p class='mb-0'><strong>Alumnos registrados:</strong> $count</p>";
                                    break;
                                    
                                case 'ludoteca':
                                    $count = DatabaseConnectors::executeQuery('ludoteca', "SELECT COUNT(*) as total FROM actividades")[0]['total'] ?? 0;
                                    echo "<p class='mb-0'><strong>Actividades:</strong> $count</p>";
                                    break;
                                    
                                case 'escuelaVerano':
                                    $count = DatabaseConnectors::executeQuery('escuelaVerano', "SELECT COUNT(*) as total FROM inscripciones")[0]['total'] ?? 0;
                                    echo "<p class='mb-0'><strong>Inscripciones:</strong> $count</p>";
                                    break;
                                    
                                case 'extraescolares':
                                    $count = DatabaseConnectors::executeQuery('extraescolares', "SELECT COUNT(*) as total FROM estudiantes")[0]['total'] ?? 0;
                                    echo "<p class='mb-0'><strong>Estudiantes:</strong> $count</p>";
                                    break;
                            }
                        } catch (Exception $e) {
                            echo '<p class="text-muted mb-0"><em>Información no disponible</em></p>';
                        }
                        ?>
                    </div>
                    <div class="card-footer">
                        <a href="modules/<?= $servicio ?>" class="btn btn-sm btn-outline-<?= $detalles['color'] ?> w-100">
                            Acceder al módulo
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Incluir el pie de página (lo crearemos en el siguiente paso)
include_once __DIR__ . '/templates/footer.php';
?>
