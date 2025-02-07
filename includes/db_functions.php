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

function getInscripciones($filtros = []) {
    $pdo = getConnection();
    $sql = "SELECT r.id, r.nombre as responsable, r.dni, h.nombre as hijo, 
            c.nombre as colegio, h.curso, h.hora_entrada, h.desayuno
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
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
