<?php
/**
 * Validación de datos del formulario de registro familiar
 * Valida tanto los datos del padre/madre como de los hijos
 */

include('conexion.php');
header('Content-Type: application/json');

$errors = [];
$data = [];

/**
 * Valida un documento de identidad (DNI o NIE)
 * @param string $documento El documento a validar
 * @return boolean true si el documento es válido, false en caso contrario
 */
function validarDocumentoIdentidad($documento) {
    $documento = strtoupper(trim($documento));
    
    // Patrones de validación
    $patronDNI = "/^[0-9]{8}[A-Z]$/";
    $patronNIE = "/^[XYZ][0-9]{7}[A-Z]$/";
    
    // Secuencia de letras válidas para el cálculo
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    
    // Validación de DNI
    if (preg_match($patronDNI, $documento)) {
        $numero = substr($documento, 0, 8);
        $letra = substr($documento, 8, 1);
        return ($letras[$numero % 23] == $letra);
    } 
    // Validación de NIE
    elseif (preg_match($patronNIE, $documento)) {
        // Convertir primera letra a número: X=0, Y=1, Z=2
        $primera = str_replace(['X','Y','Z'], [0,1,2], $documento[0]);
        $numero = $primera . substr($documento, 1, 7);
        $letra = substr($documento, 8, 1);
        return ($letras[$numero % 23] == $letra);
    }
    
    return false;
}

/**
 * Valida que un nombre contenga solo caracteres permitidos
 * @param string $nombre El nombre a validar
 * @return boolean true si el nombre es válido
 */
function validarNombre($nombre) {
    return preg_match('/^[A-Za-záéíóúüñÁÉÍÓÚÜÑ\s]{2,100}$/', $nombre);
}

/**
 * Valida que una fecha de nacimiento esté en un rango aceptable
 * @param string $fecha La fecha a validar
 * @return boolean true si la fecha es válida
 */
function validarFechaNacimiento($fecha) {
    $fecha = strtotime($fecha);
    $minDate = strtotime('-18 years'); // Edad mínima para padres
    $maxDate = strtotime('-2 years');  // Edad mínima para niños
    return ($fecha && $fecha <= $maxDate && $fecha >= $minDate);
}

/**
 * Sanitiza y valida un email
 * @param string $email El email a sanitizar
 * @return string El email sanitizado
 */
function sanitizarEmail($email) {
    $email = eliminarAcentos($email);
    $email = preg_replace('/[^a-zA-Z0-9@._-]/', '', $email);
    return filter_var(strtolower(trim($email)), FILTER_SANITIZE_EMAIL);
}

/**
 * Elimina acentos y caracteres especiales
 * @param string $cadena La cadena a procesar
 * @return string La cadena sin acentos
 */
function eliminarAcentos($cadena) {
    $originales = 'áéíóúüñÁÉÍÓÚÜÑ';
    $modificadas = 'aeiouunAEIOUUN';
    return strtr($cadena, $originales, $modificadas);
}

// Validación de datos del padre/madre
if (empty($_POST['nombre_completo']) || !validarNombre($_POST['nombre_completo'])) {
    $errors[] = "El nombre solo debe contener letras y espacios (2-100 caracteres)";
}

if (empty($_POST['dni'])) {
    $errors[] = "Documento de identidad requerido";
} else {
    $documento = strtoupper(trim($_POST['dni']));
    if (!validarDocumentoIdentidad($documento)) {
        $errors[] = "Documento de identidad inválido";
    } else {
        // Verificar único
        $stmt = $conexion->prepare("SELECT id FROM padres WHERE dni = ?");
        $stmt->execute([$documento]);
        if ($stmt->fetch()) {
            $errors[] = "El documento de identidad ya está registrado";
        }
    }
}

if (empty($_POST['email'])) {
    $errors[] = "Email requerido";
} else {
    $email = sanitizarEmail($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    } else {
        // Verificar email único
        $stmt = $conexion->prepare("SELECT id FROM padres WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "El email ya está registrado";
        }
    }
}

// Validar teléfono
if (!preg_match('/^[6-9][0-9]{8}$/', $_POST['telefono'])) {
    $errors[] = "Teléfono inválido (debe empezar por 6-9 y tener 9 dígitos)";
}

// Validación de datos de los hijos y sus relaciones
if (!empty($_POST['nombre_hijo'])) {
    foreach ($_POST['nombre_hijo'] as $i => $nombre) {
        if (!empty($nombre)) {
            // Validar nombre del hijo
            if (!validarNombre($nombre)) {
                $errors[] = "Nombre de hijo inválido: solo letras y espacios";
            }
            
            // Validar fecha de nacimiento
            if (empty($_POST['fecha_nacimiento'][$i]) || 
                !validarFechaNacimiento($_POST['fecha_nacimiento'][$i])) {
                $errors[] = "Fecha de nacimiento inválida para " . $nombre;
            }
            
            // Validar existencia del colegio en la base de datos
            if (!empty($_POST['colegio'][$i])) {
                $stmt = $conexion->prepare("SELECT id FROM colegios WHERE id = ?");
                $stmt->execute([$_POST['colegio'][$i]]);
                if (!$stmt->fetch()) {
                    $errors[] = "Colegio inválido para " . $nombre;
                }
            }
            
            // Validar existencia del curso en la base de datos
            if (!empty($_POST['curso'][$i])) {
                $stmt = $conexion->prepare("SELECT id FROM cursos WHERE id = ?");
                $stmt->execute([$_POST['curso'][$i]]);
                if (!$stmt->fetch()) {
                    $errors[] = "Curso inválido para " . $nombre;
                }
            }
        }
    }
}

// Devolver resultado de la validación
echo json_encode([
    'valid' => empty($errors),
    'errors' => $errors
]);
