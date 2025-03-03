<?php
require_once '../../config.php'; // subir un nivel
require_once '../../includes/db.php'; // subir un nivel
require_once '../../includes/Actividades.php'; // subir un nivel

// Obtener ID del centro de la URL
$colegioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$colegioId) {
    header('Location: ../../extraescolares.php'); // ajustar ruta
    exit;
}

// Inicializar clases y obtener datos
$actividades = new Actividades();
$centro = $actividades->getCentroById($colegioId);

if (!$centro) {
    header('Location: ../../extraescolares.php'); // ajustar ruta
    exit;
}

$actividadesCentro = $actividades->getActividadesCentro($colegioId);

// Después de obtener los datos del centro, agrega:
$nombreColegio = htmlspecialchars($centro['nombre']);

// Agrupar actividades por nivel
$actividadesInfantil = array_filter($actividadesCentro, function($act) {
    return $act['curso_minimo'] <= 3;
});

$actividadesPrimaria = array_filter($actividadesCentro, function($act) {
    return $act['curso_minimo'] >= 4;
});

// Configuración de la página
$pagina = 'extraescolares';
$titulo = "{$centro['nombre']} - Actividades Extraescolares " . date('Y') . '-' . (date('Y') + 1);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Actividades extraescolares <?php echo htmlspecialchars($centro['nombre']); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="index, follow">
    
    <title><?php echo $titulo; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../../favicon.ico" type="image/x-icon">
    <link rel="icon" href="../../favicon.ico" type="image/x-icon">

    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/styles.css">

    <!-- Open Graph tags -->
    <meta property="og:title" content="<?php echo $titulo; ?>">
    <meta property="og:description" content="Actividades extraescolares <?php echo htmlspecialchars($centro['nombre']); ?>">
    <meta property="og:type" content="website">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="../../">Extraescolares</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="../../">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="../../centros.php">Centros</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="container mt-4">
        <h1><?= $nombreColegio ?></h1>
        
        <?php if($centro['direccion'] || $centro['telefono']): ?>
        <div class="mb-4">
            <?php if($centro['direccion']): ?>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($centro['direccion']); ?></p>
            <?php endif; ?>
            <?php if($centro['telefono']): ?>
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($centro['telefono']); ?></p>
            <?php endif; ?>
            <?php if($centro['email']): ?>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($centro['email']); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <?php if(!empty($actividadesInfantil)): ?>
            <div class="col-md-6">
                <h2>Actividades Infantil</h2>
                <div class="list-group">
                    <?php foreach($actividadesInfantil as $actividad): ?>
                    <div class="list-group-item">
                        <h5 class="mb-1"><?php echo htmlspecialchars($actividad['actividad']); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($actividad['horarios']); ?></p>
                        <p class="mb-1"><strong>Cursos:</strong> <?php echo getRangoCursos($actividad['curso_minimo'], $actividad['curso_maximo']); ?></p>
                        <?php if($actividad['precio_actual'] > 0): ?>
                            <p class="mb-1"><strong>Precio:</strong> <?php echo number_format($actividad['precio_actual'], 2); ?>€/mes</p>
                        <?php endif; ?>
                        <p class="mb-1"><strong>Plazas:</strong> <?php echo ($actividad['cupo_maximo'] - $actividad['cupo_actual']); ?> disponibles</p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if(!empty($actividadesPrimaria)): ?>
            <div class="col-md-6">
                <h2>Actividades Primaria</h2>
                <div class="list-group">
                    <?php foreach($actividadesPrimaria as $actividad): ?>
                    <div class="list-group-item">
                        <h5 class="mb-1"><?php echo htmlspecialchars($actividad['actividad']); ?></h5>
                        <p class="mb-1"><?php echo htmlspecialchars($actividad['horarios']); ?></p>
                        <p class="mb-1"><strong>Cursos:</strong> <?php echo getRangoCursos($actividad['curso_minimo'], $actividad['curso_maximo']); ?></p>
                        <?php if($actividad['precio_actual'] > 0): ?>
                            <p class="mb-1"><strong>Precio:</strong> <?php echo number_format($actividad['precio_actual'], 2); ?>€/mes</p>
                        <?php endif; ?>
                        <p class="mb-1"><strong>Plazas:</strong> <?php echo ($actividad['cupo_maximo'] - $actividad['cupo_actual']); ?> disponibles</p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="container mt-5 mb-3">
        <hr>
        <div class="row">
            <div class="col-md-6"></div>
                <p>&copy; <?php echo date('Y'); ?> Actividades Extraescolares</p>
            </div>
            <div class="col-md-6 text-md-right"></div>
                <p><a href="../../aviso-legal.php">Aviso Legal</a> | 
                   <a href="../../privacidad.php">Política de Privacidad</a></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
