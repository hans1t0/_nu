<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function logDbError($message, $context = []) {
    $logFile = __DIR__ . '/../logs/db_error.log';
    $errorLog = sprintf(
        "[%s] %s\nContext: %s\n",
        date('Y-m-d H:i:s'),
        $message,
        json_encode($context, JSON_PRETTY_PRINT)
    );
    error_log($errorLog, 3, $logFile);
}

try {
    $host = 'localhost';
    $db   = 'ludoteca_db';
    $user = 'root';
    $pass = 'hans';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verificar conexión y permisos
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar que las tablas existen y tienen los campos correctos
    $tables = ['tutores', 'alumnos', 'centros', 'horarios', 'inscripciones'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("DESCRIBE $table");
            if (!$stmt) {
                throw new PDOException("La tabla $table no existe");
            }
            logDbError("DEBUG - Tabla verificada", ['table' => $table]);
        } catch (PDOException $e) {
            throw new PDOException("Error al verificar tabla $table: " . $e->getMessage());
        }
    }

} catch (PDOException $e) {
    logDbError("Error de conexión", [
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    die('Error de conexión a la base de datos. Por favor, contacte al administrador.');
}
