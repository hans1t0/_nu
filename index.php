<?php
session_start();
// Generar token CSRF
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
include('conexion.php');
if(isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Deportiva Escolar 2024 | Registro Familiar</title>
    
    <!-- Meta SEO -->
    <meta name="description" content="Inscripción deportiva escolar 2024. Registra a tus hijos en actividades deportivas escolares. Colegios participantes: Albufereta, Almadraba, Condomina y más.">
    <meta name="keywords" content="inscripción deportiva, deporte escolar, actividades deportivas, colegios alicante, albufereta, almadraba, condomina">
    <meta name="author" content="Deportes Escolares">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://tudominio.com/inscripcion-deportiva">
    <meta property="og:title" content="Inscripción Deportiva Escolar 2024">
    <meta property="og:description" content="Registra a tus hijos en actividades deportivas escolares. Proceso simple y rápido.">
    <meta property="og:image" content="https://tudominio.com/assets/img/deportes-escolares.jpg">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Inscripción Deportiva Escolar 2024">
    <meta name="twitter:description" content="Registra a tus hijos en actividades deportivas escolares. Proceso simple y rápido.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="apple-touch-icon" href="assets/img/apple-touch-icon.png">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="card-title text-center mb-2">
                    <i class="bi bi-trophy-fill text-primary me-2"></i>
                    Inscripción Deportiva Escolar 2024
                </h1>
                <p class="text-center text-muted mb-4">
                    <i class="bi bi-star-fill text-warning me-1"></i>
                    Registro de actividades deportivas para el curso escolar
                    <i class="bi bi-star-fill text-warning ms-1"></i>
                </p>
                <?php if(isset($mensaje)): ?>
                    <div class="alert <?php echo strpos($mensaje, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show">
                        <?php echo $mensaje; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="process.php" class="needs-validation" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-vcard-fill section-icon"></i>
                                Datos del Padre/Madre
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-person-fill"></i>
                                        Nombre Completo:
                                    </label>
                                    <input type="text" name="nombre_completo" class="form-control" required>
                                    <div class="invalid-feedback">Ingrese el nombre completo</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-card-text"></i>
                                        DNI/NIE:
                                    </label>
                                    <input type="text" name="dni" class="form-control" 
                                        pattern="^[0-9]{8}[A-Z]$|^[XYZ][0-9]{7}[A-Z]$" 
                                        maxlength="9" 
                                        required
                                        oninput="this.value = this.value.toUpperCase()"
                                        placeholder="Ej: 12345678A o X1234567L">
                                    <div class="invalid-feedback">
                                        Ingrese un documento válido:<br>
                                        - DNI: 8 números + letra<br>
                                        - NIE: X/Y/Z + 7 números + letra
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-envelope-fill"></i>
                                        Email:
                                    </label>
                                    <input type="email" name="email" class="form-control" required>
                                    <div class="invalid-feedback">Ingrese un email válido</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        <i class="bi bi-telephone-fill"></i>
                                        Teléfono:
                                    </label>
                                    <input type="tel" name="telefono" class="form-control" pattern="[0-9]{9}" maxlength="9" required>
                                    <div class="invalid-feedback">Ingrese un teléfono válido de 9 dígitos</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-people-fill section-icon"></i>
                                Datos de los Hijos
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="hijos-container">
                                <div class="hijo-form row g-3 mb-3" data-hijo-index="0">
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            <i class="bi bi-person-badge"></i>
                                            Nombre:
                                        </label>
                                        <input type="text" name="nombre_hijo[]" class="form-control" required>
                                        <div class="invalid-feedback">Ingrese nombre</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            <i class="bi bi-building"></i>
                                            Colegio:
                                        </label>
                                        <select name="colegio[]" class="form-select" required>
                                            <option value="">Seleccione colegio</option>
                                            <?php
                                            try {
                                                $stmt = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre");
                                                while($colegio = $stmt->fetch()) {
                                                    echo "<option value='{$colegio['id']}'>{$colegio['nombre']}</option>";
                                                }
                                            } catch(PDOException $e) {
                                                echo "<option value=''>Error cargando colegios</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">Seleccione colegio</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            <i class="bi bi-calendar-event"></i>
                                            Fecha Nacimiento:
                                        </label>
                                        <input type="date" name="fecha_nacimiento[]" class="form-control" required>
                                        <div class="invalid-feedback">Ingrese fecha</div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">
                                            <i class="bi bi-mortarboard-fill"></i>
                                            Curso:
                                        </label>
                                        <select name="curso[]" class="form-select" required>
                                            <option value="">Seleccione curso</option>
                                            <?php
                                            try {
                                                $stmt = $conexion->query("SELECT id, nombre, nivel FROM cursos ORDER BY FIELD(nivel, 'Infantil', 'Primaria'), grado");
                                                $nivel_actual = '';
                                                while($curso = $stmt->fetch()) {
                                                    if ($nivel_actual != $curso['nivel']) {
                                                        if ($nivel_actual != '') echo '</optgroup>';
                                                        echo "<optgroup label='{$curso['nivel']}'>";
                                                        $nivel_actual = $curso['nivel'];
                                                    }
                                                    echo "<option value='{$curso['id']}'>{$curso['nombre']}</option>";
                                                }
                                                if ($nivel_actual != '') echo '</optgroup>';
                                            } catch(PDOException $e) {
                                                echo "<option value=''>Error cargando cursos</option>";
                                            }
                                            ?>
                                        </select>
                                        <div class="invalid-feedback">Seleccione curso</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="actividades-container mt-3" style="display:none;">
                                            <hr>
                                            <h6 class="mb-3">
                                                <i class="bi bi-award"></i>
                                                Actividades disponibles:
                                            </h6>
                                            <div class="actividades-lista row g-3">
                                                <!-- Las actividades se cargarán dinámicamente -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="button" class="btn btn-outline-primary d-flex align-items-center gap-2 mx-auto" 
                                        id="btnAgregarHijo" data-bs-toggle="tooltip" data-bs-title="Agregar nuevo hijo">
                                    <i class="bi bi-person-plus-fill"></i>
                                    Agregar otro hijo
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5 d-flex align-items-center gap-2 mx-auto">
                            <i class="bi bi-check2-circle"></i>
                            Registrar Familia
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/script.js" defer></script>
</body>
</html>