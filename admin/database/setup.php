<?php
// Script para configurar las bases de datos

// Datos de conexión
$host = 'localhost';
$user = 'root';
$password = ''; // Ajusta esto según tu configuración

// Función para ejecutar el script SQL
function executeSqlScript($pdo, $sqlFile) {
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception("Error al leer el archivo SQL: $sqlFile");
    }
    
    // Separar consultas por punto y coma
    $queries = explode(';', $sql);
    
    // Ejecutar cada consulta
    $count = 0;
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                $count++;
            } catch (PDOException $e) {
                // Ignorar errores por crear base de datos ya existente
                if (!strpos($e->getMessage(), '1007') && !strpos($query, 'CREATE DATABASE')) {
                    echo "<div class='alert alert-warning'>Error en la consulta: " . htmlspecialchars($query) . "<br>Mensaje: " . $e->getMessage() . "</div>";
                }
            }
        }
    }
    return $count;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Bases de Datos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Configuración de Bases de Datos</h1>
        
        <?php
        $executed = false;
        $message = '';
        $success = false;
        $queriesExecuted = 0;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
            $executed = true;
            
            try {
                // Conectar a MySQL sin seleccionar una base de datos
                $pdo = new PDO("mysql:host=$host", $user, $password);
                
                // Configurar el modo de error para lanzar excepciones
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Ruta al archivo SQL
                $sqlFile = __DIR__ . '/schema.sql';
                
                // Ejecutar el script SQL
                $queriesExecuted = executeSqlScript($pdo, $sqlFile);
                
                $message = "¡Configuración completada correctamente! Se ejecutaron $queriesExecuted consultas.";
                $success = true;
            } catch (PDOException $e) {
                $message = "Error de conexión a la base de datos: " . $e->getMessage();
                $success = false;
            } catch (Exception $e) {
                $message = $e->getMessage();
                $success = false;
            }
        }
        ?>
        
        <?php if ($executed): ?>
            <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?> mb-4">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Crear bases de datos y tablas</h5>
                <p class="card-text">Este script creará las bases de datos necesarias y las tablas para el funcionamiento del sistema.</p>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="dbhost" class="form-label">Host de base de datos</label>
                        <input type="text" class="form-control" id="dbhost" name="dbhost" value="localhost" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dbuser" class="form-label">Usuario de base de datos</label>
                        <input type="text" class="form-control" id="dbuser" name="dbuser" value="root" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dbpass" class="form-label">Contraseña de base de datos</label>
                        <input type="password" class="form-control" id="dbpass" name="dbpass">
                    </div>
                    
                    <div class="alert alert-warning">
                        <h4 class="alert-heading">¡Advertencia!</h4>
                        <p>Este script creará o modificará las siguientes bases de datos:</p>
                        <ul>
                            <li><strong>guarderia_matinal</strong>: Para el módulo de Guardería Matinal</li>
                            <li><strong>ludoteca_db</strong>: Para el módulo de Ludoteca</li>
                            <li><strong>escuela_verano</strong>: Para el módulo de Escuela de Verano</li>
                            <li><strong>actividades_escolares</strong>: Para el módulo de Actividades Extraescolares</li>
                        </ul>
                        <p>Si estas bases de datos ya existen, se añadirán las tablas necesarias. Si las tablas ya existen, se conservarán sus datos.</p>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="confirmSetup" name="confirmSetup" required>
                        <label class="form-check-label" for="confirmSetup">
                            Entiendo que este script modificará las bases de datos mencionadas
                        </label>
                    </div>
                    
                    <button type="submit" name="setup" class="btn btn-primary">Configurar bases de datos</button>
                </form>
            </div>
        </div>
        
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Probar conexiones</h5>
                <p class="card-text">Prueba si las conexiones a las bases de datos funcionan correctamente.</p>
                
                <div class="row">
                    <div class="col-md-6">
                        <h6>Estado de conexiones:</h6>
                        <ul class="list-group">
                            <?php
                            // Probar conexiones a las bases de datos
                            $databases = [
                                'guarderia_matinal' => 'Guardería Matinal',
                                'ludoteca_db' => 'Ludoteca',
                                'escuela_verano' => 'Escuela de Verano',
                                'actividades_escolares' => 'Actividades Extraescolares'
                            ];
                            
                            foreach ($databases as $db => $label) {
                                try {
                                    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
                                    $dbh = new PDO($dsn, $user, $password);
                                    echo "<li class='list-group-item list-group-item-success'>
                                            <i class='bi bi-check-circle-fill'></i> $label: Conectado
                                          </li>";
                                } catch (PDOException $e) {
                                    echo "<li class='list-group-item list-group-item-danger'>
                                            <i class='bi bi-exclamation-triangle-fill'></i> $label: Error de conexión
                                          </li>";
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    
                    <div class="col-md-6">
                        <h6>Acciones:</h6>
                        <a href="../index.php" class="btn btn-success mb-2 d-block">Ir al panel de administración</a>
                        <a href="../../index.php" class="btn btn-outline-primary d-block">Ir a la página principal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</body>
</html>