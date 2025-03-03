<?php
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/Actividades.php';

// Definir el ID del colegio La Almadraba
const COLEGIO_ID = 6;

// Inicializar clases y obtener datos
$actividades = new Actividades();
$centro = $actividades->getCentroById(COLEGIO_ID);
$actividadesCentro = $actividades->getActividadesCentro(COLEGIO_ID);

// Después de obtener los datos del centro, agrega:
$nombreColegio = htmlspecialchars($centro['nombre']);

// Modificar la estructura para agrupar solo por nivel
$actividadesPorNivel = [
    'Infantil' => [], // Todas las actividades de 1º a 3º Infantil
    'Primaria' => []  // Todas las actividades de 1º a 6º Primaria
];

foreach ($actividadesCentro as $actividad) {
    if ($actividad['curso_maximo'] <= 3) {
        // Actividades de infantil (cursos 1-3)
        $actividadesPorNivel['Infantil'][] = $actividad;
    } else {
        // Actividades de primaria (cursos 4-9)
        $actividadesPorNivel['Primaria'][] = $actividad;
    }
}

// Ordenar actividades dentro de cada nivel por nombre
foreach ($actividadesPorNivel as &$actividades) {
    usort($actividades, function($a, $b) {
        return strcmp($a['actividad'], $b['actividad']);
    });
}
unset($actividades);

// Función auxiliar para obtener nombre de curso
function getNombreCurso($grado) {
    switch ($grado) {
        case 1: return "1º Infantil";
        case 2: return "2º Infantil";
        case 3: return "3º Infantil";
        case 4: return "1º Primaria";
        case 5: return "2º Primaria";
        case 6: return "3º Primaria";
        case 7: return "4º Primaria";
        case 8: return "5º Primaria";
        case 9: return "6º Primaria";
        default: return "$grado°";
    }
}

// Configuración de la página
$pagina = 'extraescolares';
$titulo = "CEIP $nombreColegio - Actividades Extraescolares " . date('Y') . '-' . (date('Y') + 1);
include '../includes/header.php';
include '../includes/nav.php';
?>

<div class="container-fluid bg-light py-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Inicio</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>extraescolares.php">Extraescolares</a></li>
                <li class="breadcrumb-item active"><?= $nombreColegio ?></li>
            </ol>
        </nav>

        <!-- Header -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">CEIP <?= $nombreColegio ?></h1>
                <p class="lead text-secondary">
                    Programa de actividades extraescolares para el curso <?= date('Y') ?>-<?= date('Y') + 1 ?>. 
                    Desarrollo educativo, deportivo y cultural para todos los niveles.
                </p>
                <div class="d-flex gap-3">
                    <a href="#actividades" class="btn btn-primary">Ver Actividades</a>
                    <a href="<?= BASE_URL ?>inscripcion.php" class="btn btn-outline-primary">Inscripción</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Información del Centro</h5>
                        <ul class="list-unstyled mb-0">
                            <?php if (!empty($centro['direccion'])): ?>
                                <li class="mb-2"><i class="bi bi-geo-alt me-2"></i><?= $centro['direccion'] ?></li>
                            <?php endif; ?>
                            <?php if (!empty($centro['telefono'])): ?>
                                <li class="mb-2"><i class="bi bi-telephone me-2"></i><?= $centro['telefono'] ?></li>
                            <?php endif; ?>
                            <?php if (!empty($centro['email'])): ?>
                                <li><i class="bi bi-envelope me-2"></i><?= $centro['email'] ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actividades -->
        <section id="actividades">
            <?php foreach ($actividadesPorNivel as $nivel => $actividades): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary bg-opacity-10">
                        <h2 class="h5 mb-0"><?= $nivel ?></h2>
                    </div>
                    <div class="card-body">
                        <!-- Vista móvil -->
                        <div class="d-md-none">
                            <?php foreach ($actividades as $actividad): ?>
                                <div class="p-3 border-bottom">
                                    <h4 class="h6 mb-2"><?= htmlspecialchars($actividad['actividad']) ?></h4>
                                    <div class="small text-secondary mb-1">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= htmlspecialchars($actividad['horarios']) ?>
                                    </div>
                                    <div class="small text-secondary mb-1">
                                        <i class="bi bi-people me-1"></i>
                                        <?= ($actividad['cupo_maximo'] - $actividad['cupo_actual']) ?>/<?= $actividad['cupo_maximo'] ?> plazas
                                    </div>
                                    <div class="small">
                                        <strong class="text-primary"><?= number_format($actividad['precio_actual'] ?? 0, 2) ?>€/mes</strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Vista desktop -->
                        <div class="table-responsive d-none d-md-block">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Actividad</th>
                                        <th>Horario</th>
                                        <th>Plazas</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($actividades as $actividad): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($actividad['actividad']) ?></td>
                                            <td><?= htmlspecialchars($actividad['horarios']) ?></td>
                                            <td><?= ($actividad['cupo_maximo'] - $actividad['cupo_actual']) ?>/<?= $actividad['cupo_maximo'] ?></td>
                                            <td><?= number_format($actividad['precio_actual'] ?? 0, 2) ?>€/mes</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
