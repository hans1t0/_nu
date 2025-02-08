<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Las credenciales introducidas no son correctas. Por favor, verifica tu usuario y contraseña.";
        // Registrar intento fallido
        error_log("Intento de acceso fallido - Usuario: $username - IP: " . $_SERVER['REMOTE_ADDR']);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administración - Ludoteca</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .alert-danger {
            border-left: 5px solid #842029;
            background-color: #f8d7da;
            color: #842029;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">Acceso Administración</h2>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <?php echo $error; ?>
                                <div class="mt-2 small">
                                    <ul class="mb-0">
                                        <li>Usuario por defecto: admin</li>
                                        <li>Verifica que la tecla mayúsculas no esté activada</li>
                                        <li>Asegúrate de no tener espacios en blanco</li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                        <form method="POST"></form>
                            <div class="mb-3">
                                <label for="username" class="form-label">Usuario</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Iniciar sesión</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
