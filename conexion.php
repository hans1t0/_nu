<?php
try {
    $host = 'localhost';
    $dbname = 'registro_familiar';
    $username = 'root';
    $password = 'hans';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $conexion = new PDO($dsn, $username, $password, $options);
} catch(PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

function limpiarDatos($datos) {
    return htmlspecialchars(strip_tags(trim($datos)));
}
?>
