<?php
session_start();
header('Content-Type: application/json');

// Configuración BD
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'hans');
define('DB_NAME', 'escuela_verano');

// Validar CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die(json_encode(['success' => false, 'error' => 'Token inválido']));
}

// Funciones de sanitización y validación
function sanitizeString($str) {
    return filter_var(trim($str), FILTER_SANITIZE_STRING);
}

function validateDNINIE($value) {
    $value = strtoupper($value);
    
    // Validar formato
    if (!preg_match('/^[XYZ0-9][0-9]{7}[A-Z]$/', $value)) {
        return false;
    }
    
    // Reemplazar letras iniciales de NIE
    $numero = str_replace(['X', 'Y', 'Z'], [0, 1, 2], substr($value, 0, -1));
    $letra = substr($value, -1);
    
    // Letras válidas en orden
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    
    // Calcular letra esperada
    $letraEsperada = substr($letras, $numero % 23, 1);
    
    return $letra === $letraEsperada;
}

function sanitizeDNI($dni) {
    $dni = preg_replace('/[^A-Z0-9]/i', '', strtoupper($dni));
    if (!validateDNINIE($dni)) {
        throw new Exception('DNI/NIE no válido');
    }
    return $dni;
}

function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitizePhone($phone) {
    return preg_replace('/[^0-9+]/', '', $phone);
}

function sanitizeIBAN($iban) {
    return preg_replace('/[^A-Z0-9]/i', '', $iban);
}

try {
    // Conexión a la BD
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Iniciar transacción
    $pdo->beginTransaction();

    // Validar y sanitizar datos del responsable
    $responsable = [
        'nombre' => sanitizeString($_POST['nombre']),
        'dni' => sanitizeDNI($_POST['dni']), // Ahora incluye validación
        'email' => sanitizeEmail($_POST['email']),
        'telefono' => sanitizePhone($_POST['telefono']),
        'forma_pago' => sanitizeString($_POST['forma_pago']),
        'iban' => isset($_POST['iban']) ? sanitizeIBAN($_POST['iban']) : null,
        'observaciones' => sanitizeString($_POST['observaciones'])
    ];

    // Validaciones básicas
    if (empty($responsable['nombre']) || empty($responsable['dni']) || empty($responsable['email'])) {
        throw new Exception('Faltan campos obligatorios del responsable');
    }

    if (!filter_var($responsable['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email inválido');
    }

    // Insertar responsable
    $stmt = $pdo->prepare("
        INSERT INTO responsables (nombre, dni, email, telefono, forma_pago, iban, observaciones)
        VALUES (:nombre, :dni, :email, :telefono, :forma_pago, :iban, :observaciones)
    ");
    $stmt->execute($responsable);
    $responsableId = $pdo->lastInsertId();

    // Procesar participantes
    if (!empty($_POST['participante']) && is_array($_POST['participante'])) {
        $stmt = $pdo->prepare("
            INSERT INTO participantes (
                responsable_id, nombre, fecha_nacimiento, centro_actual,
                curso, alergias
            ) VALUES (
                :responsable_id, :nombre, :fecha_nacimiento, :centro_actual,
                :curso, :alergias
            )
        ");

        foreach ($_POST['participante'] as $participante) {
            $participanteData = [
                'responsable_id' => $responsableId,
                'nombre' => sanitizeString($participante['nombre']),
                'fecha_nacimiento' => $participante['fecha_nacimiento'],
                'centro_actual' => sanitizeString($participante['centro_actual']),
                'curso' => sanitizeString($participante['curso']),
                'alergias' => sanitizeString($participante['alergias'])
            ];

            if (empty($participanteData['nombre']) || empty($participanteData['fecha_nacimiento'])) {
                throw new Exception('Faltan campos obligatorios del participante');
            }

            $stmt->execute($participanteData);
            $participanteId = $pdo->lastInsertId();

            // Definir fechas de las semanas
            $periodosFechas = [
                'julio1' => ['inicio' => '2024-07-01', 'fin' => '2024-07-06'],
                'julio2' => ['inicio' => '2024-07-07', 'fin' => '2024-07-13'],
                'julio3' => ['inicio' => '2024-07-14', 'fin' => '2024-07-20'],
                'julio4' => ['inicio' => '2024-07-21', 'fin' => '2024-07-27'],
                'julio5' => ['inicio' => '2024-07-28', 'fin' => '2024-07-31']
            ];

            // Guardar periodos seleccionados
            if (!empty($_POST['periodos']) && is_array($_POST['periodos'])) {
                $stmtPeriodos = $pdo->prepare("
                    INSERT INTO periodos_inscritos (
                        participante_id, semana, fecha_inicio, fecha_fin
                    ) VALUES (
                        :participante_id, :semana, :fecha_inicio, :fecha_fin
                    )
                ");

                foreach ($_POST['periodos'] as $periodo) {
                    if (isset($periodosFechas[$periodo])) {
                        $periodoData = [
                            'participante_id' => $participanteId,
                            'semana' => $periodo,
                            'fecha_inicio' => $periodosFechas[$periodo]['inicio'],
                            'fecha_fin' => $periodosFechas[$periodo]['fin']
                        ];
                        $stmtPeriodos->execute($periodoData);
                    }
                }
            }

            // Guardar servicios
            $stmtServicios = $pdo->prepare("
                INSERT INTO servicios_contratados (
                    participante_id, socio_ampa, guarderia_matinal, comedor
                ) VALUES (
                    :participante_id, :socio_ampa, :guarderia_matinal, :comedor
                )
            ");

            $serviciosData = [
                'participante_id' => $participanteId,
                'socio_ampa' => sanitizeString($_POST['socio_ampa']),
                'guarderia_matinal' => isset($_POST['guarderia_matinal']) ? 
                    sanitizeString($_POST['guarderia_matinal']) : null,
                'comedor' => sanitizeString($_POST['comedor'])
            ];
            $stmtServicios->execute($serviciosData);
        }
    }

    // Confirmar transacción
    $pdo->commit();
    $_SESSION['inscripcion_completada'] = true;
    echo json_encode([
        'success' => true,
        'redirect' => 'success.php?id=' . $responsableId
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
