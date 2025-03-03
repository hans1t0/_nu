<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'hans');
define('DB_NAME', 'escuela_verano');

// Configuración de la aplicación
define('APP_NAME', 'Escuela de Verano 2024');
define('APP_URL', 'http://localhost/_nu3/ev');

// Configuración de fechas
define('YEAR', '2024');
define('DATE_FORMAT', 'd/m/Y');
define('TIME_FORMAT', 'H:i');

// Configuración de periodos
define('PERIODOS', [
    'julio1' => ['nombre' => 'Semana 1', 'inicio' => '2024-07-01', 'fin' => '2024-07-06'],
    'julio2' => ['nombre' => 'Semana 2', 'inicio' => '2024-07-07', 'fin' => '2024-07-13'],
    'julio3' => ['nombre' => 'Semana 3', 'inicio' => '2024-07-14', 'fin' => '2024-07-20'],
    'julio4' => ['nombre' => 'Semana 4', 'inicio' => '2024-07-21', 'fin' => '2024-07-27'],
    'julio5' => ['nombre' => 'Semana 5', 'inicio' => '2024-07-28', 'fin' => '2024-07-31']
]);

// Configuración de centros
define('CENTROS', [
    'ALMADRABA' => 'CEIP Almadraba',
    'COSTA_BLANCA' => 'CEIP Costa Blanca',
    'FARO' => 'CEIP Faro',
    'VORAMAR' => 'CEIP Voramar'
]);

// Configuración de horarios
define('HORARIOS_ENTRADA', [
    '7:30' => 'Matinal - 7:30h',
    '8:00' => 'Matinal - 8:00h',
    '8:30' => 'Matinal - 8:30h',
    '9:00' => 'Normal - 9:00h'
]);

// Configuración de errores
ini_set('display_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Madrid');

// Funciones helper
function formatDate($date) {
    return date(DATE_FORMAT, strtotime($date));
}

function formatTime($time) {
    return date(TIME_FORMAT, strtotime($time));
}

function getNombreCentro($codigo) {
    return CENTROS[$codigo] ?? $codigo;
}

function getNombrePeriodo($codigo) {
    return PERIODOS[$codigo]['nombre'] ?? $codigo;
}

function getNombrePeriodoCorto($codigo) {
    if (isset(PERIODOS[$codigo])) {
        // Extraer solo el número de la semana (ejemplo: de "julio1" extrae "1")
        preg_match('/(\d+)/', $codigo, $matches);
        return 'S' . $matches[1];
    }
    return '';
}

// Función para validar acceso a admin
function checkAdminAccess() {
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ' . APP_URL . '/admin/login.php');
        exit;
    }
}
