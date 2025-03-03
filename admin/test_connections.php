<?php
// Iniciar sesión para gestión de usuarios
session_start();

// Cargar configuración de base de datos
require_once __DIR__ . '/database/DatabaseConnectors.php';

// Título de la página
$pageTitle = "Prueba de Conexiones";

// Definir servicios disponibles
$servicios = [
    'matinera' => [
        'nombre' => 'Guardería Matinal',
        'icono' => 'sunrise',
        'color' => 'success',
        'dbname' => 'guarderia_matinal'
    ],
    'ludoteca' => [
        'nombre' => 'Ludoteca',
        'icono' => 'controller',
        'color' => 'primary',
        'dbname' => 'ludoteca_db'
    ],
    'escuelaVerano' => [
        'nombre' => 'Escuela de Verano',
        'icono' => 'sun',
        'color' => 'warning',
        'dbname' => 'escuela_verano'
    ],
    'extraescolares' => [
        'nombre' => 'Act. Extraescolares',
        'icono' => 'stopwatch',
        'color' => 'purple',
        'dbname' => 'actividades_escolares'
    ]
];

// Variables para almacenar resultados de pruebas
$connectionResults = [];
$tableResults = [];
$tablesCount = [];
$recordsCount = [];

// Probar conexiones y contar tablas
foreach ($servicios as $servicio => $detalles) {
    try {
        // Probar conexión
        $conn = DatabaseConnectors::getConnection($servicio);
        $connectionResults[$servicio] = [
            'success' => true,
            'message' => 'Conexión establecida correctamente'
        ];
        
        // Contar tablas en la base de datos
        $tables = DatabaseConnectors::executeQuery($servicio, "SHOW TABLES");
        $tablesCount[$servicio] = count($tables);
        
        // Contar registros en las tablas principales
        $tableResults[$servicio] = [];
        $totalRecords = 0;
        
        foreach ($tables as $table) {
            $tableName = reset($table); // Obtener el nombre de la tabla
            
            $countQuery = "SELECT COUNT(*) as total FROM `$tableName`";
            $result = DatabaseConnectors::executeQuery($servicio, $countQuery);
            $count = $result[0]['total'];
            $totalRecords += $count;
            
            $tableResults[$servicio][] = [
                'name' => $tableName,
                'count' => $count
            ];
        }
        
        $recordsCount[$servicio] = $totalRecords;
        
    } catch (Exception $e) {
        $connectionResults[$servicio] = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ];
        
        // Inicializar contadores en 0 si hay error
        $tablesCount[$servicio] = 0;
        $recordsCount[$servicio] = 0;
        $tableResults[$servicio] = [];
    }
}

// Incluir el encabezado
include_once __DIR__ . '/templates/header.php';
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col">
            <h1>
                <i class="bi bi-database-check"></i> 
                Prueba de Conexiones a Bases de Datos
            </h1>
            <p class="lead">
                Esta página muestra el estado de las conexiones a las diferentes bases de datos del sistema.
            </p>
        </div>
    </div>

    <!-- Resumen de conexiones -->
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
                                    <th>Tablas</th>
                                    <th>Registros</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($servicios as $servicio => $detalles): ?>
                                    <?php $result = $connectionResults[$servicio]; ?>
                                    <tr>
                                        <td>
                                            <i class="bi bi-<?= $detalles['icono'] ?> text-<?= $detalles['color'] ?>"></i>
                                            <?= $detalles['nombre'] ?>
                                        </td>
                                        <td>
                                            <?php if ($result['success']): ?>
                                                <span class="badge bg-success">Conectado</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Error</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $detalles['dbname'] ?></td>
                                        <td><?= $tablesCount[$servicio] ?></td>
                                        <td><?= $recordsCount[$servicio] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles de cada servicio -->
    <div class="row">
        <?php foreach ($servicios as $servicio => $detalles): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-<?= $detalles['color'] ?> text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-<?= $detalles['icono'] ?>"></i>
                            <?= $detalles['nombre'] ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$connectionResults[$servicio]['success']): ?>
                            <div class="alert alert-danger"></div></div>
                                <?= $connectionResults[$servicio]['message'] ?>
                            </div>
                            <a href="database/setup.php" class="btn btn-outline-primary"></a></a>
                                <i class="bi bi-wrench"></i> Configurar bases de datos
                            </a>
                        <?php else: ?>
                            <p>
                                <strong>Base de datos:</strong> <?= $detalles['dbname'] ?><br>
                                <strong>Tablas:</strong> <?= $tablesCount[$servicio] ?><br>
                                <strong>Registros totales:</strong> <?= $recordsCount[$servicio] ?>
                            </p>
                            
                            <?php if (!empty($tableResults[$servicio])): ?>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr></tr></tr>
                                                <th>Tabla</th>
                                                <th>Registros</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody></tbody>
                                            <?php foreach ($tableResults[$servicio] as $table): ?>
                                                <tr></tr></tr>
                                                    <td><?= $table['name'] ?></td>
                                                    <td><?= $table['count'] ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-3"></div></div>
                                <a href="modules/<?= $servicio ?>" class="btn btn-<?= $detalles['color'] ?>">
                                    <i class="bi bi-box-arrow-right"></i> Ir al módulo
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card"></div></div>
                <div class="card-header">
                    <h5 class="mb-0">Opciones de configuración</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"></div></div>
                            <h6>Configuración de bases de datos</h6>
                            <p>Si alguna conexión presenta errores, puede ejecutar el script de configuración para crear las bases de datos y tablas necesarias.</p>
                            <a href="database/setup.php" class="btn btn-primary">
                                <i class="bi bi-database-gear"></i> Ejecutar script de configuración
                            </a>
                        </div>
                        <div class="col-md-6"></div></div>
                            <h6>Volver al panel principal</h6>
                            <p>Regrese al panel de administración para gestionar los diferentes servicios del sistema.</p>
                            <a href="index.php" class="btn btn-success"></a></a>
                                <i class="bi bi-speedometer2"></i> Ir al panel principal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Incluir el pie de página
include_once __DIR__ . '/templates/footer.php';
?>
