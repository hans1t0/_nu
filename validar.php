<?php
include('conexion.php');

header('Content-Type: application/json');

$errors = [];
$data = [];

function validarDocumentoIdentidad($documento) {
    // Limpiamos el documento
    $documento = strtoupper(trim($documento));
    
    // Expresiones regulares solo para DNI y NIE
    $patronDNI = "/^[0-9]{8}[A-Z]$/";
    $patronNIE = "/^[XYZ][0-9]{7}[A-Z]$/";
    
    // Letras válidas para DNI/NIE
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    
    if (preg_match($patronDNI, $documento)) {
        // Validar DNI
        $numero = substr($documento, 0, 8);
        $letra = substr($documento, 8, 1);
        return ($letras[$numero % 23] == $letra);
    } 
    elseif (preg_match($patronNIE, $documento)) {
        // Convertir primera letra a número: X=0, Y=1, Z=2
        $primera = str_replace(['X','Y','Z'], [0,1,2], $documento[0]);
        $numero = $primera . substr($documento, 1, 7);
        $letra = substr($documento, 8, 1);
        return ($letras[$numero % 23] == $letra);
    }
    
    return false;
}

function validarNombre($nombre) {
    return preg_match('/^[A-Za-záéíóúüñÁÉÍÓÚÜÑ\s]{2,100}$/', $nombre);
}

function validarFechaNacimiento($fecha) {
    $fecha = strtotime($fecha);
    $minDate = strtotime('-18 years'); // Para padres
    $maxDate = strtotime('-2 years');  // Para niños
    return ($fecha && $fecha <= $maxDate && $fecha >= $minDate);
}

function sanitizarEmail($email) {
    $email = eliminarAcentos($email);
    $email = preg_replace('/[^a-zA-Z0-9@._-]/', '', $email); // Solo permitir caracteres válidos para email
    return filter_var(strtolower(trim($email)), FILTER_SANITIZE_EMAIL);
}

function eliminarAcentos($cadena) {
    $originales = 'áéíóúüñÁÉÍÓÚÜÑ';
    $modificadas = 'aeiouunAEIOUUN';
    return strtr($cadena, $originales, $modificadas);
}

// Validar padre
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

// Validar hijos
if (!empty($_POST['nombre_hijo'])) {
    foreach ($_POST['nombre_hijo'] as $i => $nombre) {
        if (!empty($nombre)) {
            if (!validarNombre($nombre)) {
                $errors[] = "Nombre de hijo inválido: solo letras y espacios";
            }
            
            if (empty($_POST['fecha_nacimiento'][$i]) || 
                !validarFechaNacimiento($_POST['fecha_nacimiento'][$i])) {
                $errors[] = "Fecha de nacimiento inválida para " . $nombre;
            }
            
            // Validar que el colegio exista
            if (!empty($_POST['colegio'][$i])) {
                $stmt = $conexion->prepare("SELECT id FROM colegios WHERE id = ?");
                $stmt->execute([$_POST['colegio'][$i]]);
                if (!$stmt->fetch()) {
                    $errors[] = "Colegio inválido para " . $nombre;
                }
            }
            
            // Validar que el curso exista
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

echo json_encode([
    'valid' => empty($errors),
    'errors' => $errors
]);
