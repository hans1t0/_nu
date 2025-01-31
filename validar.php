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

// Validar padre
if (empty($_POST['nombre_completo'])) {
    $errors[] = "El nombre es requerido";
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

if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email inválido";
} else {
    // Verificar email único
    $stmt = $conexion->prepare("SELECT id FROM padres WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    if ($stmt->fetch()) {
        $errors[] = "El email ya está registrado";
    }
}

// Validar hijos
if (!empty($_POST['nombre_hijo'])) {
    foreach ($_POST['nombre_hijo'] as $i => $nombre) {
        if (!empty($nombre)) {
            if (empty($_POST['fecha_nacimiento'][$i])) {
                $errors[] = "Fecha de nacimiento requerida para " . $nombre;
            }
            if (empty($_POST['curso'][$i])) {
                $errors[] = "Curso requerido para " . $nombre;
            }
            if (empty($_POST['colegio'][$i])) {
                $errors[] = "Colegio requerido para " . $nombre;
            }
        }
    }
}

echo json_encode([
    'valid' => empty($errors),
    'errors' => $errors
]);
