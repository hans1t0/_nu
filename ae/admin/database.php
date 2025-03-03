<?php
try {
    $host = 'localhost';
    $dbname = 'actividades_escolares';
    $username = 'root';
    $password = 'hans';
    
    $conexion = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}
