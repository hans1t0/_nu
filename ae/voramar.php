<?php 
require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/Actividades.php';

$pagina = 'extraescolares';
$actividades = new Actividades();
$centro = $actividades->getCentro('VORAMAR');
$actividadesCentro = $actividades->getActividadesCentro($centro['id']);

$titulo = $centro['nombre'] . ' - Actividades Extraescolares - ' . SITE_NAME;
include '../includes/header.php';
include '../includes/nav.php';
?>

// ...rest of the code same as almadraba.php but with the specific image...
