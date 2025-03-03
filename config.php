<?php
// Configuración global del sitio
define('BASE_URL', 'http://localhost/_nu3/');  // Ajusta esto según tu estructura
define('SITE_NAME', 'Extraescolares');
define('SITE_EMAIL', 'info@educap.es');

// Rutas de assets
define('ASSETS_URL', BASE_URL . 'assets/');
define('IMAGES_URL', BASE_URL . 'assets/img/');
define('CSS_URL', ASSETS_URL . 'css/');
define('JS_URL', ASSETS_URL . 'js/');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'actividades_escolares');
define('DB_USER', 'root');
define('DB_PASS', 'hans');

// Otros ajustes
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Europe/Madrid');

// Información de servicios
$servicios = [
    'matinera' => [
        'titulo' => 'Guardería Matinal',
        'horario' => '7:30 - 9:00',
        'color' => 'primary'
    ],
    'ludoteca' => [
        'titulo' => 'Ludoteca Tardes',
        'horario' => '15:00 - 17:00',
        'color' => 'warning'
    ],
    'verano' => [
        'titulo' => 'Escuela de Verano',
        'horario' => '9:00 - 14:00',
        'color' => 'success'
    ],
    'extraescolares' => [
        'titulo' => 'Actividades Extraescolares',
        'horario' => 'Horario de tarde',
        'color' => 'info'
    ]
];
