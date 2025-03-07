<?php
require_once '../../database/DatabaseConnectors.php';
require_once 'classes/ExtraescolaresManager.php';

// Variables iniciales
$action = $_GET['action'] ?? 'listar';
$id = $_GET['id'] ?? null;
$mensaje = '';
$tipo_mensaje = '';

// Manejar acciones de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $extraManager = new ExtraescolaresManager();
    
    if (isset($_POST['guardar_colegio'])) {
        // Datos del colegio
        $datos = [
            'nombre' => $_POST['nombre'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? '',
            'contacto' => $_POST['contacto'] ?? ''
        ];
        
        if (isset($_POST['colegio_id']) && !empty($_POST['colegio_id'])) {
            // Actualizar colegio existente
            $datos['id'] = $_POST['colegio_id'];
            $resultado = $extraManager->actualizarColegio($datos);
            if ($resultado) {
                $mensaje = 'Colegio actualizado correctamente';
                $tipo_mensaje = 'success';
                header("Location: colegios.php?mensaje=$mensaje&tipo=$tipo_mensaje");
                exit;
            } else {
                $mensaje = 'Error al actualizar el colegio';
                $tipo_mensaje = 'danger';
            }
        } else {
            // Crear nuevo colegio
            $resultado = $extraManager->crearColegio($datos);
            if ($resultado) {
                $mensaje = 'Colegio creado correctamente';
                $tipo_mensaje = 'success';
                header("Location: colegios.php?mensaje=$mensaje&tipo=$tipo_mensaje");
                exit;
            } else {
                $mensaje = 'Error al crear el colegio';
                $tipo_mensaje = 'danger';
            }
        }
    }
    
    // Eliminar colegio
    if (isset($_POST['eliminar_colegio'])) {
        $colegio_id = $_POST['colegio_id'] ?? 0;
        $resultado = $extraManager->eliminarColegio($colegio_id);
        
        if ($resultado) {
            $mensaje = 'Colegio eliminado correctamente';
            $tipo_mensaje = 'success';
            header("Location: colegios.php?mensaje=$mensaje&tipo=$tipo_mensaje");
            exit;
        } else {
            $mensaje = 'Error al eliminar el colegio';
            $tipo_mensaje = 'danger';
        }
    }
}

// Manejar notificaciones
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipo_mensaje = $_GET['tipo'] ?? 'info';
}

// Título de la página
$titulo_pagina = 'Gestión de Colegios';

// Incluir el header
include_once __DIR__ . '/../../includes/header.php';
?>

<!-- Estilos y fuentes -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

<style>
body {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    background-color: #f9fafb;
}
.table-container {
    border-radius: 10px;
    overflow: hidden;
}
.custom-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.5rem;
}
</style>

<div class="container-fluid px-4 py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
            <li class="breadcrumb-item"><a href="index.php">Panel</a></li>
            <li class="breadcrumb-item active" aria-current="page">Colegios</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?= $titulo_pagina ?></h1>
        <a href="colegios.php?action=nuevo" class="btn btn-primary px-4 rounded-pill">
            <i class="bi bi-plus-lg me-2"></i> Nuevo Colegio
        </a>
    </div>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($action === 'listar'): ?>
    <div class="row g-3">
        <?php
        try {
            $colegios = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT 
                    c.*,
                    COUNT(DISTINCT ca.actividad_id) as total_actividades,
                    COUNT(DISTINCT i.id) as total_inscritos,
                    COUNT(DISTINCT i.hijo_id) as total_alumnos
                FROM colegios c
                LEFT JOIN colegio_actividad ca ON c.id = ca.colegio_id AND ca.activo = 1
                LEFT JOIN actividades a ON ca.actividad_id = a.id AND a.activa = 1
                LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
                GROUP BY c.id
                ORDER BY c.nombre ASC"
            );
            
            if (count($colegios) > 0) {
                foreach ($colegios as $colegio) {
                    ?>
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                        <div class="dashboard-card h-100">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between mb-3">
                                    <div class="stat-icon stat-primary" style="width: 35px; height: 35px; font-size: 1rem;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-link p-0" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="colegios/<?= preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', strtolower($colegio['nombre']))) ?>.php">
                                                    <i class="bi bi-eye me-2"></i> Ver detalles
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="?action=editar&id=<?= $colegio['id'] ?>">
                                                    <i class="bi bi-pencil me-2"></i> Editar
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger" data-bs-toggle="modal" 
                                                        data-bs-target="#eliminarModal" 
                                                        data-colegio-id="<?= $colegio['id'] ?>"
                                                        data-colegio-nombre="<?= htmlspecialchars($colegio['nombre']) ?>">
                                                    <i class="bi bi-trash me-2"></i> Eliminar
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <h6 class="mb-1 fw-semibold text-truncate"><?= htmlspecialchars($colegio['nombre']) ?></h6>
                                <div class="small text-muted mb-2 text-truncate">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= htmlspecialchars($colegio['direccion'] ?: 'Sin dirección') ?>
                                </div>

                                <div class="row g-2 mt-2 text-center">
                                    <div class="col-4">
                                        <div class="px-2 py-1 bg-light rounded-2">
                                            <div class="small text-muted">Activ.</div>
                                            <div class="fw-medium"><?= $colegio['total_actividades'] ?></div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="px-2 py-1 bg-light rounded-2">
                                            <div class="small text-muted">Alum.</div>
                                            <div class="fw-medium"><?= $colegio['total_alumnos'] ?></div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="px-2 py-1 bg-light rounded-2">
                                            <div class="small text-muted">Insc.</div>
                                            <div class="fw-medium"><?= $colegio['total_inscritos'] ?></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="colegios/<?= preg_replace('/[^a-z0-9-]/', '', str_replace(' ', '-', strtolower($colegio['nombre']))) ?>.php" 
                                       class="btn btn-sm btn-outline-primary w-100">
                                        Ver detalles <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert alert-info">No hay colegios registrados</div></div>';
            }
        } catch (Exception $e) {
            error_log("Error en colegios.php: " . $e->getMessage());
            echo '<div class="col-12"><div class="alert alert-danger">Error al cargar los colegios: ' . $e->getMessage() . '</div></div>';
        }
        ?>
    </div>

    <?php elseif ($action === 'ver'): ?>
    <?php
    try {
        // Obtener el nombre normalizado del colegio
        $colegio = DatabaseConnectors::executeQuery('extraescolares', 
            "SELECT id, nombre FROM colegios WHERE id = ?", 
            [$id]
        )[0] ?? null;

        if ($colegio) {
            $nombreArchivo = strtolower(preg_replace(
                '/[^a-z0-9-]/', 
                '', 
                str_replace(' ', '-', $colegio['nombre'])
            ));
            header("Location: colegios/{$nombreArchivo}.php");
            exit;
        } else {
            throw new Exception('Colegio no encontrado');
        }
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . $e->getMessage() . '</div>';
    }
    ?>

<?php else: ?>
    <!-- Formulario para nuevo/editar colegio -->
    <?php
    $colegio = [
        'id' => '',
        'nombre' => '',
        'direccion' => '',
        'telefono' => '',
        'email' => '',
        'contacto' => ''
    ];
    
    if ($action === 'editar' && $id) {
        try {
            $resultado = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT * FROM colegios WHERE id = ?",
                [$id]
            );
            
            if (!empty($resultado)) {
                $colegio = $resultado[0];
            }
        } catch (Exception $e) {
            $mensaje = 'Error al cargar los datos del colegio: ' . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    }
    ?>
    
    <div class="card shadow-sm border-0">
        <div class="custom-header">
            <h5 class="mb-0 fw-semibold"><?= ($action === 'nuevo') ? 'Nuevo Colegio' : 'Editar Colegio' ?></h5>
        </div>
        <div class="card-body">
            <form method="post" action="colegios.php">
                <?php if ($colegio['id']): ?>
                <input type="hidden" name="colegio_id" value="<?= htmlspecialchars($colegio['id']) ?>">
                <?php endif; ?>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label">Nombre del Centro <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?= htmlspecialchars($colegio['nombre']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telefono" name="telefono" value="<?= htmlspecialchars($colegio['telefono']) ?>" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="direccion" name="direccion" value="<?= htmlspecialchars($colegio['direccion']) ?>" required>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($colegio['email']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="contacto" class="form-label">Persona de Contacto</label>
                        <input type="text" class="form-control" id="contacto" name="contacto" value="<?= htmlspecialchars($colegio['contacto']) ?>">
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="guardar_colegio" class="btn btn-primary px-4">Guardar</button>
                    <a href="colegios.php" class="btn btn-outline-secondary ms-2">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Incluir footer
include_once __DIR__ . '/../../includes/footer.php';
?>
