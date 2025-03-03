<?php
/**
 * Configuración específica para el módulo de Ludoteca
 */

// Configuración general
$config = [
    'module_name' => 'Ludoteca',
    'module_icon' => 'controller',
    'items_per_page' => 15,
];

// Rangos de edad para participantes
$edadRangos = [
    '3-5' => 'De 3 a 5 años',
    '6-8' => 'De 6 a 8 años',
    '9-12' => 'De 9 a 12 años',
    '13-17' => 'De 13 a 17 años',
    '18+' => 'Adultos (18 años o más)'
];

// Tipos de actividades
$tipoActividades = [
    'juego_mesa' => 'Juego de mesa',
    'juego_rol' => 'Juego de rol',
    'videojuego' => 'Videojuego',
    'actividad_grupal' => 'Actividad grupal',
    'taller' => 'Taller',
    'otro' => 'Otro'
];

// Niveles de dificultad
$nivelesDificultad = [
    'muy_facil' => 'Muy fácil',
    'facil' => 'Fácil',
    'medio' => 'Medio',
    'dificil' => 'Difícil',
    'muy_dificil' => 'Muy difícil'
];
?>
