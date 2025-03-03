<?php
// Iniciar sesión
session_start();

// Comprobar si ya hay una sesión activa
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

// Variables para mostrar mensajes
$error = '';
$success = '';

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validación básica (en un entorno real, esto usaría una base de datos y contraseñas hash)
    // Para fines demostrativos usamos credenciales hardcodeadas
    if ($username === 'admin' && $password === 'admin123') {
        // Crear sesión
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_name'] = 'Administrador';
        
        // Redirigir al panel
        header('Location: index.php');
        exit;
    } else {
        $error = 'Usuario o contraseña incorrectos';
    }
}

// Título de la página
$pageTitle = "Iniciar Sesión - Panel de Administración";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        
        .login-header {
            background-color: #343a40;
            color: white;
            border-radius: 0.5rem 0.5rem 0 0;
            padding: 1.5rem;
            text-align: center;
        }
        
        .login-body {
            background-color: #fff;
            border-radius: 0 0 0.5rem 0.5rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .login-footer {
            margin-top: 1rem;
            text-align: center;
            color: #6c757d;
            font-size: 0.875rem;
        }
        
        .login-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="bi bi-mortarboard-fill login-icon"></i>
            <h1 class="mb-0 fs-4">Sistema de Gestión Escolar</h1>
            <p class="mb-0 small">Panel de Administración</p>
        </div>
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="off">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Recordar sesión</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                </div>
            </form>
            
            <div class="mt-3 text-center">
                <a href="#" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
            </div>
        </div>
        <div class="login-footer">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Gestión Escolar</p>
        </div>
    </div>
    
    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle para mostrar/ocultar contraseña
        document.addEventListener('DOMContentLoaded', function() {
            const togglePasswordButton = document.querySelector('.toggle-password');
            const passwordInput = document.querySelector('#password');
            
            if (togglePasswordButton && passwordInput) {
                togglePasswordButton.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-eye');
                    icon.classList.toggle('bi-eye-slash');
                });
            }
        });
    </script>
</body>
</html>
