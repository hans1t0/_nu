<?php
include('conexion.php');

// Arrays ampliados para generar datos aleatorios
$nombres = ['Juan', 'María', 'José', 'Ana', 'Pedro', 'Laura', 'Carlos', 'Sofia', 'Miguel', 'Isabel', 
           'Antonio', 'Carmen', 'David', 'Paula', 'Francisco', 'Lucía', 'Manuel', 'Elena', 'Javier', 'Raquel',
           'Alberto', 'Rosa', 'Diego', 'Julia', 'Andrés', 'Beatriz', 'Fernando', 'Cristina', 'Luis', 'Victoria'];

$apellidos = ['García', 'Martínez', 'López', 'González', 'Rodríguez', 'Fernández', 'Sánchez', 'Pérez', 'Gómez', 'Torres',
              'Ruiz', 'Díaz', 'Serrano', 'Hernández', 'Muñoz', 'Sáez', 'Romero', 'Navarro', 'Jiménez', 'Moreno',
              'Álvarez', 'Alonso', 'Gutiérrez', 'Santos', 'Gil', 'Ramos', 'Blanco', 'Suárez', 'Molina', 'Ortiz'];

$dominios = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'icloud.com', 'protonmail.com'];

function generarDNI() {
    // 20% de probabilidad de generar NIE, 80% DNI
    if (rand(1, 100) <= 20) {
        $letras_nie = ['X', 'Y', 'Z'];
        $primera = $letras_nie[array_rand($letras_nie)];
        $numero = str_pad(rand(1, 9999999), 7, '0', STR_PAD_LEFT);
    } else {
        $primera = '';
        $numero = str_pad(rand(1, 99999999), 8, '0', STR_PAD_LEFT);
    }
    
    // Convertir letra NIE a número para cálculo
    $num_calculo = $numero;
    if ($primera !== '') {
        $num_calculo = strtr($primera, ['X'=>0, 'Y'=>1, 'Z'=>2]) . $numero;
    }
    
    $letras = "TRWAGMYFPDXBNJZSQVHLCKE";
    return $primera . $numero . $letras[$num_calculo % 23];
}

function generarTelefono() {
    return '6' . str_pad(rand(0, 99999999), 8, '0', STR_PAD_LEFT);
}

function generarFechaNacimiento($tipo = 'hijo') {
    if ($tipo == 'hijo') {
        // Entre 3 y 12 años
        return date('Y-m-d', strtotime('-' . rand(3, 12) . ' years -' . rand(0, 365) . ' days'));
    } else {
        // Entre 25 y 50 años
        return date('Y-m-d', strtotime('-' . rand(25, 50) . ' years -' . rand(0, 365) . ' days'));
    }
}

function eliminarAcentos($cadena) {
    $originales = 'áéíóúüñÁÉÍÓÚÜÑ';
    $modificadas = 'aeiouunAEIOUUN';
    return strtr($cadena, $originales, $modificadas);
}

function generarEmailUnico($nombre, $conexion) {
    $nombre = eliminarAcentos($nombre); // Eliminar acentos
    $baseEmail = strtolower(
        preg_replace('/[^a-zA-Z0-9.]/', '.', // Solo permitir letras, números y puntos
        trim(
            preg_replace('/\.+/', '.', // Eliminar puntos consecutivos
                str_replace(' ', '.', $nombre)
            )
        )
    ));
    
    $email = $baseEmail . '@' . $GLOBALS['dominios'][array_rand($GLOBALS['dominios'])];
    $contador = 1;
    
    // Verificar si el email existe y generar uno nuevo si es necesario
    $stmt = $conexion->prepare("SELECT id FROM padres WHERE email = ?");
    while (true) {
        $stmt->execute([$email]);
        if (!$stmt->fetch()) {
            break;
        }
        $email = $baseEmail . $contador . '@' . $GLOBALS['dominios'][array_rand($GLOBALS['dominios'])];
        $contador++;
    }
    return $email;
}

function generarDNIUnico($conexion) {
    $stmt = $conexion->prepare("SELECT id FROM padres WHERE dni = ?");
    while (true) {
        $dni = generarDNI();
        $stmt->execute([$dni]);
        if (!$stmt->fetch()) {
            return $dni;
        }
    }
}

try {
    $conexion->beginTransaction();

    // Obtener IDs de colegios y cursos
    $colegios = $conexion->query("SELECT id FROM colegios")->fetchAll(PDO::FETCH_COLUMN);
    $cursos = $conexion->query("SELECT id FROM cursos")->fetchAll(PDO::FETCH_COLUMN);

    // Generar 100 padres (aumentado de 50 a 100)
    for ($i = 0; $i < 100; $i++) {
        $nombre = $nombres[array_rand($nombres)] . ' ' . 
                 $apellidos[array_rand($apellidos)] . ' ' . 
                 $apellidos[array_rand($apellidos)];
        
        $stmt = $conexion->prepare("INSERT INTO padres (nombre, dni, email, telefono) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $nombre,
            generarDNIUnico($conexion),
            generarEmailUnico($nombre, $conexion),
            generarTelefono()
        ]);
        
        $id_padre = $conexion->lastInsertId();
        
        // Generar entre 1 y 3 hijos por padre
        $num_hijos = rand(1, 3);
        for ($j = 0; $j < $num_hijos; $j++) {
            $nombre_hijo = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
            
            $stmt = $conexion->prepare("INSERT INTO hijos (id_padre, nombre, id_colegio, id_curso, fecha_nacimiento) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $id_padre,
                $nombre_hijo,
                $colegios[array_rand($colegios)],
                $cursos[array_rand($cursos)],
                generarFechaNacimiento('hijo')
            ]);
        }
    }

    $conexion->commit();
    echo "Datos generados exitosamente: 100 padres con sus respectivos hijos.\n";

} catch (Exception $e) {
    $conexion->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
