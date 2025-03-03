<?php

class DatabaseConnectors {
    private static $configs = [
        'matinera' => [
            'host' => null,
            'user' => null,
            'password' => null,
            'dbname' => null
        ],
        'ludoteca' => [
            'host' => null,
            'user' => null,
            'password' => null,
            'dbname' => null
        ],
        'escuelaVerano' => [
            'host' => null,
            'user' => null,
            'password' => null,
            'dbname' => null
        ],
        'extraescolares' => [
            'host' => null,
            'user' => null,
            'password' => null,
            'dbname' => null
        ]
    ];

    private static $connections = [];

    /**
     * Inicializa las configuraciones de las bases de datos
     */
    public static function initialize() {
        // Configuración para Matinera
        self::$configs['matinera'] = [
            'host' => getenv('MATINERA_DB_HOST') ?: 'localhost',
            'user' => getenv('MATINERA_DB_USER') ?: 'root',
            'password' => getenv('MATINERA_DB_PASSWORD') ?: 'hans',
            'dbname' => getenv('MATINERA_DB_NAME') ?: 'guarderia_matinal'
        ];

        // Configuración para Ludoteca
        self::$configs['ludoteca'] = [
            'host' => getenv('LUDOTECA_DB_HOST') ?: 'localhost',
            'user' => getenv('LUDOTECA_DB_USER') ?: 'root',
            'password' => getenv('LUDOTECA_DB_PASSWORD') ?: 'hans',
            'dbname' => getenv('LUDOTECA_DB_NAME') ?: 'ludoteca_db'
        ];

        // Configuración para Escuela de Verano
        self::$configs['escuelaVerano'] = [
            'host' => getenv('ESCUELA_VERANO_DB_HOST') ?: 'localhost',
            'user' => getenv('ESCUELA_VERANO_DB_USER') ?: 'root',
            'password' => getenv('ESCUELA_VERANO_DB_PASSWORD') ?: 'hans',
            'dbname' => getenv('ESCUELA_VERANO_DB_NAME') ?: 'escuela_verano'
        ];

        // Configuración para Extraescolares
        self::$configs['extraescolares'] = [
            'host' => getenv('EXTRAESCOLARES_DB_HOST') ?: 'localhost',
            'user' => getenv('EXTRAESCOLARES_DB_USER') ?: 'root',
            'password' => getenv('EXTRAESCOLARES_DB_PASSWORD') ?: 'hans',
            'dbname' => getenv('EXTRAESCOLARES_DB_NAME') ?: 'actividades_escolares'
        ];
    }

    /**
     * Obtiene una conexión a la base de datos especificada
     * 
     * @param string $dbName Nombre de la base de datos (matinera, ludoteca, escuelaVerano, extraescolares)
     * @return PDO Conexión a la base de datos
     * @throws Exception Si la base de datos no está configurada
     */
    public static function getConnection($dbName) {
        if (!isset(self::$configs[$dbName])) {
            throw new Exception("Base de datos '$dbName' no está configurada");
        }

        if (!isset(self::$connections[$dbName]) || self::$connections[$dbName] === null) {
            $config = self::$configs[$dbName];
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            
            try {
                $pdo = new PDO(
                    $dsn, 
                    $config['user'], 
                    $config['password'], 
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
                self::$connections[$dbName] = $pdo;
            } catch (PDOException $e) {
                throw new Exception("Error al conectar a la base de datos $dbName: " . $e->getMessage());
            }
        }

        return self::$connections[$dbName];
    }

    /**
     * Ejecuta una consulta en la base de datos especificada
     * 
     * @param string $dbName Nombre de la base de datos
     * @param string $query Consulta SQL
     * @param array $params Parámetros para la consulta (opcional)
     * @return array Resultados de la consulta
     */
    public static function executeQuery($dbName, $query, $params = []) {
        $conn = self::getConnection($dbName);
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error ejecutando consulta en $dbName: " . $e->getMessage());
        }
    }

    /**
     * Ejecuta una consulta que no devuelve resultados (INSERT, UPDATE, DELETE)
     * 
     * @param string $dbName Nombre de la base de datos
     * @param string $query Consulta SQL
     * @param array $params Parámetros para la consulta (opcional)
     * @return int Número de filas afectadas
     */
    public static function executeNonQuery($dbName, $query, $params = []) {
        $conn = self::getConnection($dbName);
        try {
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            throw new Exception("Error ejecutando consulta en $dbName: " . $e->getMessage());
        }
    }

    /**
     * Inicia una transacción en la base de datos especificada
     * 
     * @param string $dbName Nombre de la base de datos
     */
    public static function beginTransaction($dbName) {
        $conn = self::getConnection($dbName);
        $conn->beginTransaction();
    }

    /**
     * Confirma una transacción en la base de datos especificada
     * 
     * @param string $dbName Nombre de la base de datos
     */
    public static function commitTransaction($dbName) {
        $conn = self::getConnection($dbName);
        $conn->commit();
    }

    /**
     * Revierte una transacción en la base de datos especificada
     * 
     * @param string $dbName Nombre de la base de datos
     */
    public static function rollbackTransaction($dbName) {
        $conn = self::getConnection($dbName);
        $conn->rollback();
    }

    /**
     * Cierra todas las conexiones a las bases de datos
     */
    public static function closeAllConnections() {
        foreach (self::$connections as $key => $connection) {
            self::$connections[$key] = null;
        }
    }
}

// Inicializamos las configuraciones al cargar la clase
DatabaseConnectors::initialize();
