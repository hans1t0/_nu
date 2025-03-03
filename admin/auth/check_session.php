<?php
/**
 * Verificación de sesión de usuario
 * Este archivo comprueba si el usuario tiene una sesión activa válida
 */

// Asegurarnos de que la sesión está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Comprobar si el usuario tiene una sesión activa
function checkUserSession() {
    // Si no existe la variable de sesión del usuario o no está autenticado
    if (!isset($_SESSION['user_authenticated']) || $_SESSION['user_authenticated'] !== true) {
        // Guardar la URL actual para redirigir después del login
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        
        // Redirigir al login
        header('Location: /admin/login.php');
        exit;
    }
    
    // Verificar tiempo de inactividad (opcional)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        // Si pasó más de 1 hora, cerrar sesión
        session_unset();     // Eliminar todas las variables de sesión
        session_destroy();   // Destruir la sesión
        
        // Redirigir al login
        header('Location: /admin/login.php?timeout=1');
        exit;
    }
    
    // Actualizar tiempo de última actividad
    $_SESSION['last_activity'] = time();
    
    return true;
}

// Para propósitos de depuración, temporalmente desactivamos la verificación
// para permitir el desarrollo sin login constante
// IMPORTANTE: Activar esta funcionalidad en producción
$_SESSION['user_authenticated'] = true;  // Solo para desarrollo
$_SESSION['user_name'] = 'Usuario de Prueba';  // Solo para desarrollo
$_SESSION['user_role'] = 'admin';  // Solo para desarrollo

// Comentar esta línea durante desarrollo y descomentar en producción
// checkUserSession();
