<?php
// Asegurarnos que no hay output antes de los headers
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'includes/db_connect.php';

// Mejorar función de log para incluir más detalles
function logError($mensaje, $datos = []) {
    $logFile = __DIR__ . '/logs/error.log';
    
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }
    
    $errorLog = sprintf(
        "[%s] %s\n%s\n%s\n",
        date('Y-m-d H:i:s'),
        $mensaje,
        str_repeat('-', 50),
        json_encode($datos, JSON_PRETTY_PRINT)
    );
    
    error_log($errorLog, 3, $logFile);
}

// Función para sanitizar strings
function sanitizeString($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

// Función para crear registro de pago
function crearRegistroPago($pdo, $inscripcionId, $monto, $formaPago) {
    $metodoPago = match($formaPago) {
        'transferencia' => 'transferencia',
        'coordinador' => 'efectivo',
        'domiciliacion' => 'transferencia',
        default => 'efectivo'
    };

    $stmt = $pdo->prepare("INSERT INTO pagos (inscripcion_id, monto, metodo_pago, estado) 
                          VALUES (:inscripcion_id, :monto, :metodo_pago, :estado)");
    return $stmt->execute([
        'inscripcion_id' => $inscripcionId,
        'monto' => $monto,
        'metodo_pago' => $metodoPago,
        'estado' => $formaPago === 'domiciliacion' ? 'pendiente' : 'pendiente'
    ]);
}

// Variable para controlar transacción activa
$transactionActive = false;

try {
    // Verificar conexión BD al inicio
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query('SELECT 1')->fetch();
    
    // Debug: Imprimir datos recibidos
    logError("DEBUG - Inicio de proceso", ['POST' => $_POST]);

    // Validar que existan datos POST
    if (empty($_POST)) {
        throw new Exception('No se recibieron datos del formulario');
    }

    // Validar que existan hijos y sea un array
    if (!isset($_POST['hijos']) || !is_array($_POST['hijos'])) {
        throw new Exception('No se recibieron datos de los alumnos');
    }

    // Validar que haya al menos un hijo
    if (count($_POST['hijos']) === 0) {
        throw new Exception('Debe agregar al menos un alumno');
    }

    // Debug: Imprimir datos de hijos
    logError("Datos de hijos recibidos", $_POST['hijos']);

    // Validar CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Error de validación del token CSRF');
    }

    // Validar campos requeridos del tutor
    $camposRequeridos = ['nombre', 'dni', 'email', 'telefono', 'forma_pago'];
    foreach ($camposRequeridos as $campo) {
        if (empty($_POST[$campo])) {
            throw new Exception("El campo {$campo} es requerido");
        }
    }

    $pdo->beginTransaction();
    $transactionActive = true;

    // Debug: Verificar conexión BD
    if (!$pdo) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Debug: Imprimir consultas SQL
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sanitizar y validar datos del tutor
    $tutor = [
        'nombre' => sanitizeString($_POST['nombre']),
        'dni' => sanitizeString($_POST['dni']),
        'email' => filter_var($_POST['email'], FILTER_SANITIZE_EMAIL),
        'telefono' => sanitizeString($_POST['telefono']),
        'telefono2' => sanitizeString($_POST['telefono2'] ?? ''),
        'forma_pago' => sanitizeString($_POST['forma_pago']),
        'observaciones' => sanitizeString($_POST['observaciones'] ?? '')
    ];

    // Validar email
    if (!filter_var($tutor['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    // Agregar IBAN solo si es domiciliación
    if ($_POST['forma_pago'] === 'domiciliacion') {
        if (empty($_POST['iban'])) {
            throw new Exception('El IBAN es requerido para domiciliación');
        }
        $tutor['iban'] = sanitizeString($_POST['iban']);
    } else {
        $tutor['iban'] = null;
    }

    // Preparar campos para la consulta
    $fields = array_keys($tutor);
    $fields_str = implode(', ', $fields);
    $values_str = ':' . implode(', :', $fields);

    // Debug: Log antes de insertar tutor
    logError("DEBUG - Datos del tutor a insertar", $tutor);
    
    // Insertar tutor con try/catch específico
    try {
        $stmt = $pdo->prepare("INSERT INTO tutores ($fields_str) VALUES ($values_str)");
        $stmt->execute($tutor);
        $tutorId = $pdo->lastInsertId();
        logError("DEBUG - Tutor insertado correctamente", ['tutor_id' => $tutorId]);
    } catch (PDOException $e) {
        throw new PDOException("Error al insertar tutor: " . $e->getMessage());
    }

    // Debug: Log antes de procesar hijos
    logError("DEBUG - Iniciando proceso de hijos", ['num_hijos' => count($_POST['hijos'])]);

    // Procesar datos de los hijos con validación
    foreach ($_POST['hijos'] as $index => $hijo) {
        try {
            // Validar campos requeridos del alumno
            $camposHijo = ['nombre', 'apellidos', 'fecha_nacimiento', 'centro', 'curso'];
            foreach ($camposHijo as $campo) {
                if (empty($hijo[$campo])) {
                    throw new Exception("Campo {$campo} requerido para el alumno " . ($index + 1));
                }
            }

            // Validar centro existe
            $stmt = $pdo->prepare("SELECT id FROM centros WHERE id = ?");
            $stmt->execute([$hijo['centro']]);
            if (!$stmt->fetch()) {
                throw new Exception("Centro escolar no válido para el alumno " . ($index + 1));
            }

            // Validar horario existe
            $stmt = $pdo->prepare("SELECT id FROM horarios WHERE id = ?");
            $stmt->execute([$hijo['horario']]);
            if (!$stmt->fetch()) {
                throw new Exception("Horario no válido para el alumno " . ($index + 1));
            }

            // Sanitizar datos del alumno
            $alumno = [
                'nombre' => sanitizeString($hijo['nombre']),
                'apellidos' => sanitizeString($hijo['apellidos']),
                'fecha_nacimiento' => sanitizeString($hijo['fecha_nacimiento']),
                'centro_id' => filter_var($hijo['centro'], FILTER_SANITIZE_NUMBER_INT),
                'curso' => sanitizeString($hijo['curso']),
                'alergias' => sanitizeString($hijo['alergias'] ?? ''),
                'medicacion' => sanitizeString($hijo['medicacion'] ?? ''),
                'observaciones' => sanitizeString($hijo['observaciones'] ?? '')
            ];

            logError("DEBUG - Datos del hijo a insertar", [
                'index' => $index,
                'datos' => $alumno
            ]);

            // Insertar alumno
            $stmt = $pdo->prepare("INSERT INTO alumnos (nombre, apellidos, fecha_nacimiento, centro_id, 
                                  curso, alergias, medicacion, observaciones) 
                                  VALUES (:nombre, :apellidos, :fecha_nacimiento, :centro_id, 
                                  :curso, :alergias, :medicacion, :observaciones)");
            $stmt->execute($alumno);
            $alumnoId = $pdo->lastInsertId();

            // Crear relación alumno-tutor
            $stmt = $pdo->prepare("INSERT INTO alumno_tutor (alumno_id, tutor_id, relacion) 
                                  VALUES (:alumno_id, :tutor_id, :relacion)");
            $stmt->execute([
                'alumno_id' => $alumnoId,
                'tutor_id' => $tutorId,
                'relacion' => sanitizeString($hijo['relacion'] ?? 'tutor')
            ]);

            // Crear inscripción
            $stmt = $pdo->prepare("INSERT INTO inscripciones (alumno_id, horario_id, fecha_inicio, fecha_fin) 
                                  VALUES (:alumno_id, :horario_id, :fecha_inicio, :fecha_fin)");
            $stmt->execute([
                'alumno_id' => $alumnoId,
                'horario_id' => filter_var($hijo['horario'], FILTER_SANITIZE_NUMBER_INT),
                'fecha_inicio' => date('Y-m-d'), // Ajustar según necesidades
                'fecha_fin' => date('Y-m-d', strtotime('+1 year')) // Ajustar según necesidades
            ]);
            $inscripcionId = $pdo->lastInsertId();

            // Obtener precio del horario
            $stmt = $pdo->prepare("SELECT precio FROM horarios WHERE id = ?");
            $stmt->execute([filter_var($hijo['horario'], FILTER_SANITIZE_NUMBER_INT)]);
            $precio = $stmt->fetch(PDO::FETCH_COLUMN);

            // Crear registro de pago
            crearRegistroPago($pdo, $inscripcionId, $precio, $tutor['forma_pago']);

            // Crear primer registro de asistencia (opcional)
            $stmt = $pdo->prepare("INSERT INTO asistencia (inscripcion_id, fecha) VALUES (?, ?)");
            $stmt->execute([$inscripcionId, date('Y-m-d')]);

        } catch (PDOException $e) {
            throw new PDOException("Error al procesar hijo {$index}: " . $e->getMessage());
        }
    }

    $pdo->commit();
    $transactionActive = false;
    
    // Preparar datos para la confirmación
    $_SESSION['inscripcion'] = [
        'tutor' => $tutor,
        'alumnos' => [],
        'total' => 0
    ];

    // Obtener datos de los alumnos para mostrar
    foreach ($_POST['hijos'] as $hijo) {
        $horario = $pdo->query("SELECT * FROM horarios WHERE id = " . filter_var($hijo['horario'], FILTER_SANITIZE_NUMBER_INT))->fetch();
        $centro = $pdo->query("SELECT * FROM centros WHERE id = " . filter_var($hijo['centro'], FILTER_SANITIZE_NUMBER_INT))->fetch();
        
        $_SESSION['inscripcion']['alumnos'][] = [
            'nombre' => sanitizeString($hijo['nombre']),
            'apellidos' => sanitizeString($hijo['apellidos']),
            'centro' => $centro['nombre'],
            'curso' => sanitizeString($hijo['curso']),
            'horario' => $horario['descripcion'],
            'precio' => $horario['precio']
        ];
        
        $_SESSION['inscripcion']['total'] += $horario['precio'];
    }

    // Guardar mensaje y redirigir
    $_SESSION['mensaje'] = "¡Inscripción realizada con éxito!";
    ob_end_clean(); // Limpiar cualquier output antes de redirect
    header('Location: confirmacion.php');
    exit;

} catch (PDOException $e) {
    // Hacer rollback solo si hay transacción activa
    if ($transactionActive && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logError("Error de base de datos", [
        'message' => $e->getMessage(),
        'code' => $e->getCode(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    $_SESSION['error'] = "Error en la base de datos: " . $e->getMessage();
    ob_end_clean(); // Limpiar cualquier output antes de redirect
    header('Location: tardes.php');
    exit;
} catch (Exception $e) {
    // Hacer rollback solo si hay transacción activa
    if ($transactionActive && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    logError("Error general: " . $e->getMessage(), [
        'POST' => $_POST,
        'Trace' => $e->getTraceAsString()
    ]);
    $_SESSION['error'] = "Error al procesar la inscripción: " . $e->getMessage();
    ob_end_clean(); // Limpiar cualquier output antes de redirect
    header('Location: tardes.php');
    exit;
}
