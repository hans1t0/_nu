<!-- Sección de Colegios -->
<div class="sb-sidenav-menu-heading">Colegios</div>
<a class="nav-link" href="?page=colegios">
    <div class="sb-nav-link-icon"><i class="bi bi-building"></i></div>
    Gestión de Colegios
</a>

<?php
// Obtener lista de colegios
$colegios = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre")->fetchAll();
foreach ($colegios as $colegio):
?>
    <a class="nav-link" href="?page=colegios/<?= strtolower($colegio['nombre']) ?>">
        <div class="sb-nav-link-icon"><i class="bi bi-building"></i></div>
        <?= htmlspecialchars($colegio['nombre']) ?>
    </a>
<?php endforeach; ?>

<div class="sb-sidenav-menu-heading">Gestión</div>
<a class="nav-link" href="?page=colegios">
    <div class="sb-nav-link-icon"><i class="bi bi-building"></i></div>
    Colegios
</a>
<a class="nav-link" href="?page=actividades">
    <div class="sb-nav-link-icon"><i class="bi bi-calendar-check"></i></div>
    Actividades
</a>
<a class="nav-link" href="?page=inscritos">
    <div class="sb-nav-link-icon"><i class="bi bi-people"></i></div>
    Inscripciones
</a>
