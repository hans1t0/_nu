<?php
session_start();
require_once '../../config/db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    die('Acceso no autorizado');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Método no permitido');
}

$id = $_POST['id'] ?? null;
$estado = $_POST['estado'] ?? null;

if (!$id || !$estado) {
    http_response_code(400);
    die('Datos incompletos');
}

try {
    $stmt = $pdo->prepare("UPDATE inscripciones_tardes SET estado = ? WHERE id = ?");
    $stmt->execute([$estado, $id]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    die('Error al actualizar el estado');
}
