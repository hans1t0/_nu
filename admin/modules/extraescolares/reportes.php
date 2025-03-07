<?php
require_once '../../database/DatabaseConnectors.php';
require_once 'classes/ExtraescolaresManager.php';

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar acceso
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../../login.php');
    exit;
}

$action = $_GET['action'] ?? 'listar';
$id = $_GET['id'] ?? null;
$mensaje = '';
$tipo_mensaje = '';

// Manejar acciones de formularios
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $extraManager = new ExtraescolaresManager();
    
    if (isset($_POST['guardar_reporte'])) {
        // Datos del reporte
        $datos = [
            'tipo' => $_POST['tipo'] ?? '',
            'filtros' => $_POST['filtros'] ?? []
        ];
        
        // Generar reporte
        $reporte = $extraManager->generarReporte($datos['tipo'], $datos['filtros']);
        
        if ($reporte) {
            $mensaje = 'Reporte generado correctamente';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al generar el reporte';
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
$titulo_pagina = 'Generar Reportes';

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
            <li class="breadcrumb-item active" aria-current="page">Reportes</li>
        </ol>
    </nav>
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><?= $titulo_pagina ?></h1>
    </div>
    
    <?php if ($mensaje): ?>
    <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Formulario para generar reporte -->
    <div class="card shadow-sm border-0">
        <div class="custom-header">
            <h5 class="mb-0 fw-semibold">Generar Reporte</h5>
        </div>
        <div class="card-body">
            <form method="post" action="reportes.php">
                <div class="mb-3">
                    <label for="tipo" class="form-label">Tipo de Reporte <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo" name="tipo" required>
                        <option value="">Selecciona un tipo de reporte</option>
                        <option value="inscripciones_por_actividad">Inscripciones por Actividad</option>
                        <option value="ingresos_por_actividad">Ingresos por Actividad</option>
                        <option value="actividades_por_colegio">Actividades por Colegio</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="filtros" class="form-label">Filtros</label>
                    <input type="text" class="form-control" id="filtros" name="filtros" placeholder="Ej: fecha_inicio=2022-01-01&fecha_fin=2022-12-31">
                </div>
                
                <div class="mt-4">
                    <button type="submit" name="guardar_reporte" class="btn btn-primary px-4">Generar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Incluir footer
include_once __DIR__ . '/../../includes/footer.php';
?>