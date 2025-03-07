<?php
// Incluimos el archivo de conexión a la base de datos
require_once '../../../database/DatabaseConnectors.php';

// Establecemos el título de la página y la sección actual
$pageTitle = "Servicio de Comedor - Escuela de Verano";
$currentSection = "comedor";
$isServicePage = true;
$baseUrl = "../"; // Ruta base para los enlaces del menú

// Inicializamos variables
$mensaje = '';
$tipoMensaje = '';

// Fechas para filtrar
$fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

// Procesamos las acciones
$accion = isset($_GET['accion']) ? $_GET['accion'] : '';
$participanteId = isset($_GET['participante_id']) ? (int)$_GET['participante_id'] : 0;

// Si se está marcando la asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['marcar_asistencia'])) {
            // Obtener los datos del formulario
            $fecha = $_POST['fecha'];
            $asistencias = isset($_POST['asistencia']) ? $_POST['asistencia'] : [];
            $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : [];
            
            // Primero eliminamos las asistencias existentes para esa fecha para evitar duplicados
            $deleteQuery = "DELETE FROM comedor_asistencia WHERE fecha = :fecha";
            DatabaseConnectors::executeNonQuery('escuelaVerano', $deleteQuery, [':fecha' => $fecha]);
            
            // Insertamos las nuevas asistencias
            if (!empty($asistencias)) {
                foreach ($asistencias as $participanteId) {
                    $obs = isset($observaciones[$participanteId]) ? $observaciones[$participanteId] : '';
                    
                    $insertQuery = "INSERT INTO comedor_asistencia (participante_id, fecha, asistio, observaciones) 
                                    VALUES (:participante_id, :fecha, 1, :observaciones)";
                                    
                    DatabaseConnectors::executeNonQuery('escuelaVerano', $insertQuery, [
                        ':participante_id' => $participanteId,
                        ':fecha' => $fecha,
                        ':observaciones' => $obs
                    ]);
                }
            }
            
            $mensaje = "Se ha registrado la asistencia al comedor para el día $fecha";
            $tipoMensaje = "success";
            
        } elseif (isset($_POST['registrar_menu'])) {
            // Registrar el menú del día
            $fecha = $_POST['fecha'];
            $primer_plato = $_POST['primer_plato'];
            $segundo_plato = $_POST['segundo_plato'];
            $postre = $_POST['postre'];
            $menu_especial = $_POST['menu_especial'] ?? '';
            
            // Verificar si ya existe un menú para esta fecha
            $checkQuery = "SELECT COUNT(*) as total FROM comedor_menu WHERE fecha = :fecha";
            $result = DatabaseConnectors::executeQuery('escuelaVerano', $checkQuery, [':fecha' => $fecha]);
            
            if ($result[0]['total'] > 0) {
                // Actualizar el menú existente
                $updateQuery = "UPDATE comedor_menu SET 
                                primer_plato = :primer_plato, 
                                segundo_plato = :segundo_plato, 
                                postre = :postre, 
                                menu_especial = :menu_especial 
                                WHERE fecha = :fecha";
                                
                DatabaseConnectors::executeNonQuery('escuelaVerano', $updateQuery, [
                    ':primer_plato' => $primer_plato,
                    ':segundo_plato' => $segundo_plato,
                    ':postre' => $postre,
                    ':menu_especial' => $menu_especial,
                    ':fecha' => $fecha
                ]);
                
                $mensaje = "Menú actualizado correctamente para el día $fecha";
            } else {
                // Insertar nuevo menú
                $insertQuery = "INSERT INTO comedor_menu (fecha, primer_plato, segundo_plato, postre, menu_especial) 
                                VALUES (:fecha, :primer_plato, :segundo_plato, :postre, :menu_especial)";
                                
                DatabaseConnectors::executeNonQuery('escuelaVerano', $insertQuery, [
                    ':fecha' => $fecha,
                    ':primer_plato' => $primer_plato,
                    ':segundo_plato' => $segundo_plato,
                    ':postre' => $postre,
                    ':menu_especial' => $menu_especial
                ]);
                
                $mensaje = "Menú registrado correctamente para el día $fecha";
            }
            
            $tipoMensaje = "success";
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Si hay mensaje en la URL, lo recogemos
if (isset($_GET['msg'])) {
    $mensaje = $_GET['msg'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}

// Verificamos si existe la tabla de asistencia, si no, la creamos
try {
    $checkTableQuery = "SHOW TABLES LIKE 'comedor_asistencia'";
    $tableExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkTableQuery);
    
    if (empty($tableExists)) {
        $createTableQuery = "
            CREATE TABLE comedor_asistencia (
                id INT AUTO_INCREMENT PRIMARY KEY,
                participante_id INT NOT NULL,
                fecha DATE NOT NULL,
                asistio TINYINT(1) DEFAULT 0,
                observaciones TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_participante_fecha` (`participante_id`, `fecha`),
                FOREIGN KEY (participante_id) REFERENCES participantes(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        DatabaseConnectors::executeNonQuery('escuelaVerano', $createTableQuery);
    }
    
    // Verificamos si existe la tabla de menú, si no, la creamos
    $checkMenuTableQuery = "SHOW TABLES LIKE 'comedor_menu'";
    $menuTableExists = DatabaseConnectors::executeQuery('escuelaVerano', $checkMenuTableQuery);
    
    if (empty($menuTableExists)) {
        $createMenuTableQuery = "
            CREATE TABLE comedor_menu (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fecha DATE NOT NULL,
                primer_plato VARCHAR(255) NOT NULL,
                segundo_plato VARCHAR(255) NOT NULL,
                postre VARCHAR(255) NOT NULL,
                menu_especial TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY `idx_fecha` (`fecha`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
        ";
        DatabaseConnectors::executeNonQuery('escuelaVerano', $createMenuTableQuery);
    }
} catch (Exception $e) {
    $mensaje = "Error al verificar/crear tablas: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Obtenemos la lista de participantes con servicio de comedor
$participantesComedor = [];
try {
    $query = "SELECT p.id, p.nombre, p.fecha_nacimiento, p.alergias, r.nombre as responsable_nombre 
              FROM participantes p 
              JOIN servicios_contratados s ON p.id = s.participante_id 
              JOIN responsables r ON p.responsable_id = r.id 
              WHERE s.comedor = 'SI' 
              ORDER BY p.nombre";
    $participantesComedor = DatabaseConnectors::executeQuery('escuelaVerano', $query);
} catch (Exception $e) {
    $mensaje = "Error al cargar participantes con comedor: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Obtenemos las asistencias registradas para la fecha seleccionada
$asistencias = [];
try {
    $query = "SELECT participante_id, observaciones FROM comedor_asistencia WHERE fecha = :fecha AND asistio = 1";
    $result = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':fecha' => $fechaSeleccionada]);
    
    // Convertimos el resultado a un array asociativo para facilitar la comprobación
    foreach ($result as $row) {
        $asistencias[$row['participante_id']] = $row['observaciones'];
    }
} catch (Exception $e) {
    $mensaje .= " Error al cargar asistencias: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Obtenemos el menú para la fecha seleccionada
$menuDelDia = null;
try {
    $query = "SELECT * FROM comedor_menu WHERE fecha = :fecha";
    $result = DatabaseConnectors::executeQuery('escuelaVerano', $query, [':fecha' => $fechaSeleccionada]);
    
    if (!empty($result)) {
        $menuDelDia = $result[0];
    }
} catch (Exception $e) {
    $mensaje .= " Error al cargar menú: " . $e->getMessage();
    $tipoMensaje = "danger";
}

// Calculamos estadísticas
$estadisticas = [
    'total_comedor' => count($participantesComedor),
    'asistentes_hoy' => count($asistencias),
    'alergias' => 0
];

// Contamos cuántos tienen alergias alimentarias
foreach ($participantesComedor as $p) {
    if (!empty($p['alergias']) && 
        (
            stripos($p['alergias'], 'alergia') !== false || 
            stripos($p['alergias'], 'intoleran') !== false ||
            stripos($p['alergias'], 'celiac') !== false ||
            stripos($p['alergias'], 'lactosa') !== false
        )
    ) {
        $estadisticas['alergias']++;
    }
}

// Preparamos el array con los próximos 14 días para el selector de fechas
$proximosDias = [];
for ($i = -7; $i <= 7; $i++) {
    $fecha = date('Y-m-d', strtotime("$i days"));
    $proximosDias[$fecha] = date('d/m/Y (l)', strtotime($fecha));
}

// Incluimos el header
include('../includes/header.php');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1><i class="fas fa-utensils mr-2"></i> Servicio de Comedor</h1>
    
    <div class="btn-toolbar mb-2 mb-md-0">
        <form class="form-inline" action="" method="get">
            <label class="mr-2">Fecha:</label>
            <select name="fecha" class="form-control mr-2" onchange="this.form.submit()">
                <?php foreach($proximosDias as $fecha => $fechaTexto): ?>
                    <option value="<?php echo $fecha; ?>" <?php echo $fecha === $fechaSeleccionada ? 'selected' : ''; ?>>
                        <?php echo $fechaTexto; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
        <?php echo $mensaje; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Tarjetas de resumen -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Total Comedor</h6>
                        <h2><?php echo $estadisticas['total_comedor']; ?></h2>
                    </div>
                    <i class="fas fa-utensils fa-3x"></i>
                </div>
                <small>Participantes con servicio de comedor contratado</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Asistentes Hoy</h6>
                        <h2><?php echo $estadisticas['asistentes_hoy']; ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x"></i>
                </div>
                <small>Participantes que han asistido al comedor hoy</small>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title">Alergias Alimentarias</h6>
                        <h2><?php echo $estadisticas['alergias']; ?></h2>
                    </div>
                    <i class="fas fa-allergies fa-3x"></i>
                </div>
                <small>Participantes con alergias o intolerancias alimentarias</small>
            </div>
        </div>
    </div>
</div>

<!-- Menú del día -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-clipboard-list mr-2"></i> Menú del día <?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></h5>
    </div>
    <div class="card-body">
        <?php if ($menuDelDia): ?>
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">Primer Plato</div>
                        <div class="card-body">
                            <p class="card-text"><?php echo $menuDelDia['primer_plato']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">Segundo Plato</div>
                        <div class="card-body">
                            <p class="card-text"><?php echo $menuDelDia['segundo_plato']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-header">Postre</div>
                        <div class="card-body">
                            <p class="card-text"><?php echo $menuDelDia['postre']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($menuDelDia['menu_especial'])): ?>
                <div class="alert alert-info mt-2">
                    <strong>Menú especial / Adaptaciones:</strong> <?php echo $menuDelDia['menu_especial']; ?>
                </div>
            <?php endif; ?>
            <div class="mt-3">
                <button class="btn btn-primary" data-toggle="modal" data-target="#menuModal">
                    <i class="fas fa-edit"></i> Modificar menú
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                No hay un menú registrado para esta fecha.
            </div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#menuModal">
                <i class="fas fa-plus-circle"></i> Registrar menú
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Participantes con servicio de comedor -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-users mr-2"></i> Control de Asistencia - <?php echo date('d/m/Y', strtotime($fechaSeleccionada)); ?></h5>
    </div>
    <div class="card-body">
        <?php if (empty($participantesComedor)): ?>
            <div class="alert alert-info">
                No hay participantes con el servicio de comedor contratado.
            </div>
        <?php else: ?>
            <form action="" method="post">
                <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-fixed">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px">Asistió</th>
                                <th>Nombre</th>
                                <th>Edad</th>
                                <th>Alergias/Información</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($participantesComedor as $p): ?>
                                <?php 
                                // Calculamos la edad
                                $fechaNac = new DateTime($p['fecha_nacimiento']);
                                $hoy = new DateTime();
                                $edad = $fechaNac->diff($hoy)->y;
                                
                                // Verificamos si asistió
                                $asistio = isset($asistencias[$p['id']]);
                                $observacion = $asistio ? $asistencias[$p['id']] : '';
                                
                                // CSS para alergias
                                $tieneAlergia = !empty($p['alergias']);
                                $claseAlergia = $tieneAlergia ? 'alergia-badge' : '';
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" 
                                                id="asistencia-<?php echo $p['id']; ?>" 
                                                name="asistencia[]" 
                                                value="<?php echo $p['id']; ?>"
                                                <?php echo $asistio ? 'checked' : ''; ?>>
                                            <label class="custom-control-label" for="asistencia-<?php echo $p['id']; ?>"></label>
                                        </div>
                                    </td>
                                    <td>
                                        <?php echo $p['nombre']; ?>
                                        <small class="d-block text-muted">Resp: <?php echo $p['responsable_nombre']; ?></small>
                                    </td>
                                    <td><?php echo $edad; ?> años</td>
                                    <td class="<?php echo $claseAlergia; ?>">
                                        <?php echo $tieneAlergia ? $p['alergias'] : 'Sin alergias registradas'; ?>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" 
                                            name="observaciones[<?php echo $p['id']; ?>]" 
                                            value="<?php echo $observacion; ?>"
                                            placeholder="Observaciones">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="marcar_asistencia" class="btn btn-success">
                    <i class="fas fa-save"></i> Guardar asistencia
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- Modal para el menú del día -->
<div class="modal fade" id="menuModal" tabindex="-1" role="dialog" aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form action="" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalLabel">
                        <?php echo $menuDelDia ? 'Modificar' : 'Registrar'; ?> Menú del Día
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="fecha" value="<?php echo $fechaSeleccionada; ?>">
                    
                    <div class="form-group">
                        <label for="primer_plato">Primer plato:</label>
                        <input type="text" class="form-control" id="primer_plato" name="primer_plato" 
                               value="<?php echo $menuDelDia ? $menuDelDia['primer_plato'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="segundo_plato">Segundo plato:</label>
                        <input type="text" class="form-control" id="segundo_plato" name="segundo_plato"
                               value="<?php echo $menuDelDia ? $menuDelDia['segundo_plato'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="postre">Postre:</label>
                        <input type="text" class="form-control" id="postre" name="postre"
                               value="<?php echo $menuDelDia ? $menuDelDia['postre'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="menu_especial">Menú especial / Adaptaciones:</label>
                        <textarea class="form-control" id="menu_especial" name="menu_especial" rows="3"
                                  placeholder="Información sobre menús adaptados para alergias o intolerancias"><?php echo $menuDelDia ? $menuDelDia['menu_especial'] : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" name="registrar_menu" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar menú
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Scripts personalizados para esta página (opcional)
$customScripts = '
<script>
    // Código JavaScript específico para esta página
    console.log("Página de comedor cargada");
</script>
';

// Incluimos el footer
include('../includes/footer.php');
?>
