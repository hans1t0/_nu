<?php
require_once 'config.php';

function getConnection() {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }
    return $pdo;
}

function calcularPrecio($hora_entrada, $desayuno, $codigo_colegio) {
    if ($codigo_colegio === 'ALMADRABA') {
        if ($hora_entrada === '07:30:00') {
            return $desayuno ? 55 : 45;
        } elseif ($hora_entrada === '08:00:00') {
            return $desayuno ? 45 : 35;
        } elseif ($hora_entrada === '08:30:00') {
            return 25;
        }
    } else {
        // Precios para el resto de colegios
        if ($hora_entrada === '07:30:00') {
            return 45;
        } elseif ($hora_entrada === '08:00:00') {
            return 35;
        } elseif ($hora_entrada === '08:30:00') {
            return 25;
        }
    }
    return 0;
}

function getInscripciones($filtros = []) {
    $pdo = getConnection();
    $sql = "SELECT r.id, r.nombre as responsable, r.dni, h.nombre as hijo, 
            c.nombre as colegio, c.codigo as codigo_colegio, h.curso, 
            h.hora_entrada, h.desayuno,
            (SELECT COUNT(*) FROM hijos WHERE responsable_id = r.id) as total_hijos
            FROM responsables r
            JOIN hijos h ON h.responsable_id = r.id
            JOIN colegios c ON h.colegio_id = c.id
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($filtros['colegio'])) {
        $sql .= " AND c.codigo = ?";
        $params[] = $filtros['colegio'];
    }
    
    if (!empty($filtros['curso'])) {
        $sql .= " AND h.curso = ?";
        $params[] = $filtros['curso'];
    }
    
    if (isset($filtros['desayuno']) && $filtros['desayuno'] !== '') {
        $sql .= " AND h.desayuno = ?";
        $params[] = $filtros['desayuno'];
    }
    
    $sql .= " ORDER BY r.nombre, h.nombre";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calcular precios para todos los colegios
    foreach ($inscripciones as &$i) {
        $i['precio'] = calcularPrecio($i['hora_entrada'], $i['desayuno'], $i['codigo_colegio']);
    }
    
    return $inscripciones;
}

function getEstadisticas() {
    $pdo = getConnection();
    return [
        'total_inscritos' => $pdo->query("SELECT COUNT(*) FROM hijos")->fetchColumn(),
        'total_desayunos' => $pdo->query("SELECT COUNT(*) FROM hijos WHERE desayuno = 1")->fetchColumn()
    ];
}

function getColegios() {
    $pdo = getConnection();
    return $pdo->query("SELECT * FROM colegios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
}

function getCursos() {
    return [
        '1INF' => '1º Infantil',
        '2INF' => '2º Infantil',
        '3INF' => '3º Infantil',
        '1PRIM' => '1º Primaria',
        '2PRIM' => '2º Primaria',
        '3PRIM' => '3º Primaria',
        '4PRIM' => '4º Primaria',
        '5PRIM' => '5º Primaria',
        '6PRIM' => '6º Primaria'
    ];
}

function contarInscripcionesPorColegio($codigo_colegio) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM hijos h 
        JOIN colegios c ON h.colegio_id = c.id 
        WHERE c.codigo = ?
    ");
    $stmt->execute([$codigo_colegio]);
    return $stmt->fetchColumn();
}

function contarHijosPorPadre($id_responsable) {
    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM hijos 
        WHERE responsable_id = ?
    ");
    $stmt->execute([$id_responsable]);
    return $stmt->fetchColumn();
}
