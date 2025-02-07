<?php
session_start();
header('Content-Type: application/json');

// Configuración BD
define('DB_HOST', 'localhost');
define('DB_NAME', 'guarderia_matinal');
define('DB_USER', 'root');
define('DB_PASS', 'hans');

// Función para sanitizar inputs
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Validar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['error' => 'Token CSRF inválido']));
}

// Validar campos obligatorios del responsable
$required = ['nombre', 'dni', 'email', 'telefono'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        die(json_encode(['error' => "El campo $field es obligatorio"]));
    }
}

// Sanitizar datos del responsable
$responsable = [
    'nombre' => sanitize($_POST['nombre']),
    'dni' => sanitize($_POST['dni']),
    'email' => sanitize($_POST['email']),
    'telefono' => sanitize($_POST['telefono']),
    'observaciones' => isset($_POST['observaciones']) ? sanitize($_POST['observaciones']) : null
];

// Validar formato DNI/NIE
if (!preg_match('/^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i', $responsable['dni']) &&
    !preg_match('/^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/i', $responsable['dni'])) {
    die(json_encode(['error' => 'Formato de DNI/NIE inválido']));
}

// Validar email
if (!filter_var($responsable['email'], FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['error' => 'Email inválido']));
}

// Validar teléfono
if (!preg_match('/^[0-9]{9}$/', $responsable['telefono'])) {
    die(json_encode(['error' => 'Formato de teléfono inválido']));
}

// Validar que hay al menos un hijo
if (!isset($_POST['hijo']) || !is_array($_POST['hijo'])) {
    die(json_encode(['error' => 'Debe añadir al menos un hijo']));
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Iniciar transacción
    $pdo->beginTransaction();

    // Insertar responsable
    $stmt = $pdo->prepare("
        INSERT INTO responsables (nombre, dni, email, telefono, observaciones)
        VALUES (:nombre, :dni, :email, :telefono, :observaciones)
    ");
    $stmt->execute($responsable);
    $responsableId = $pdo->lastInsertId();

    // Procesar cada hijo
    foreach ($_POST['hijo'] as $hijo) {
        // Validar campos obligatorios del hijo
        if (empty($hijo['nombre']) || empty($hijo['fecha_nacimiento']) || 
            empty($hijo['colegio']) || empty($hijo['curso']) || 
            empty($hijo['hora_entrada'])) {
            throw new Exception('Todos los campos del hijo son obligatorios');
        }

        // Obtener ID del colegio
        $stmtColegio = $pdo->prepare("SELECT id FROM colegios WHERE codigo = ?");
        $stmtColegio->execute([$hijo['colegio']]);
        $colegioId = $stmtColegio->fetchColumn();

        if (!$colegioId) {
            throw new Exception('Colegio no válido');
        }

        // Validar y convertir hora
        if (!preg_match('/^([0-9]{1,2}):([0-9]{2})$/', $hijo['hora_entrada'])) {
            throw new Exception('Formato de hora inválido');
        }

        // Insertar hijo
        $stmt = $pdo->prepare("
            INSERT INTO hijos (
                responsable_id, nombre, fecha_nacimiento, colegio_id,
                curso, hora_entrada, desayuno
            ) VALUES (
                :responsable_id, :nombre, :fecha_nacimiento, :colegio_id,
                :curso, :hora_entrada, :desayuno
            )
        ");

        $stmt->execute([
            'responsable_id' => $responsableId,
            'nombre' => sanitize($hijo['nombre']),
            'fecha_nacimiento' => sanitize($hijo['fecha_nacimiento']),
            'colegio_id' => $colegioId,
            'curso' => sanitize($hijo['curso']),
            'hora_entrada' => sanitize($hijo['hora_entrada']),
            'desayuno' => ($hijo['colegio'] === 'ALMADRABA' && 
                          isset($hijo['desayuno']) && 
                          $hijo['desayuno'] === 'con') ? 1 : 0
        ]);
    }

    // Confirmar transacción
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Inscripción realizada correctamente',
        'id' => $responsableId
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode([
        'error' => 'Error al procesar la inscripción: ' . $e->getMessage()
    ]);
}
