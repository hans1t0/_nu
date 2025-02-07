<?php
// Prevenir acceso directo al archivo
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'guarderia_matinal');
define('DB_USER', 'root');
define('DB_PASS', 'hans');

// Configuración de la aplicación
define('APP_NAME', 'Guardería Matinal 2024');
define('APP_URL', 'http://localhost/_nu3');
define('ADMIN_EMAIL', 'admin@ejemplo.com');

// Configuración de sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Configuración de errores en desarrollo
if ($_SERVER['SERVER_NAME'] === 'localhost') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Funciones de utilidad
function sanitize_output($buffer) {
    return htmlspecialchars($buffer, ENT_QUOTES, 'UTF-8');
}

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Configuración de la guardería
define('MAX_HIJOS_POR_RESPONSABLE', 5);
define('HORARIO_INICIO', '7:30');
define('HORARIO_FIN', '8:30');

// Configuración de seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_SALT', 'tu_salt_secreto_aqui');

// Headers de seguridad
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
if (isset($_SERVER['HTTPS'])) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}
