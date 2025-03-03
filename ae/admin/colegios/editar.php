<?php
require_once '../conexion.php';

$colegio_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Obtener datos del colegio
try {
    $stmt = $conexion->prepare("SELECT * FROM colegios WHERE id = ?");
    $stmt->execute([$colegio_id]);
    $colegio = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error al cargar el colegio: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $conexion->prepare("UPDATE colegios SET 
            nombre = ?,
            direccion = ?,
            telefono = ?
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['nombre'],
            $_POST['direccion'],
            $_POST['telefono'],
            $colegio_id
        ]);

        header("Location: ?page=colegios");
        exit;
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error al actualizar: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<div class="container-fluid px-4">
    <h2 class="mb-4">
        Editar Colegio
        <a href="?page=colegios" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </h2>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Nombre del Colegio</label>
            <input type="text" class="form-control" name="nombre" 
                   value="<?= htmlspecialchars($colegio['nombre']) ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="text" class="form-control" name="telefono" 
                   value="<?= htmlspecialchars($colegio['telefono']) ?>">
        </div>

        <div class="col-12">
            <label class="form-label">Dirección</label>
            <input type="text" class="form-control" name="direccion" 
                   value="<?= htmlspecialchars($colegio['direccion']) ?>">
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </div>
    </form>
</div>
