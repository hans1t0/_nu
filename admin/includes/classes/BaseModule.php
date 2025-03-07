<?php
abstract class BaseModule {
    protected $dbName;
    protected $moduleName;
    protected $pageTitle;
    protected $error = null;
    protected $stats = [];
    
    public function __construct($dbName, $moduleName, $pageTitle) {
        $this->dbName = $dbName;
        $this->moduleName = $moduleName;
        $this->pageTitle = $pageTitle;
        $this->initializeStats();
    }
    
    abstract protected function initializeStats();
    
    protected function getTableInfo($tableName) {
        try {
            $exists = !empty(DatabaseConnectors::executeQuery(
                $this->dbName,
                "SHOW TABLES LIKE ?",
                [$tableName]
            ));
            
            if ($exists) {
                // Modificamos para soportar vistas
                if (strpos($tableName, 'v_') === 0) {
                    $count = DatabaseConnectors::executeQuery(
                        $this->dbName,
                        "SELECT COUNT(*) as total FROM $tableName"
                    )[0]['total'] ?? 0;
                } else {
                    $count = DatabaseConnectors::executeQuery(
                        $this->dbName,
                        "SELECT COUNT(*) as total FROM $tableName"
                    )[0]['total'] ?? 0;
                }
                
                return [
                    'exists' => true,
                    'count' => $count
                ];
            }
            
            return ['exists' => false, 'count' => 0];
        } catch (Exception $e) {
            $this->error = "Error al verificar la tabla $tableName: " . $e->getMessage();
            return ['exists' => false, 'count' => 0];
        }
    }
    
    public function getActiveRecords($tableName, $stateColumn = 'estado', $activeValue = 'activa') {
        if (!$this->tableExists($tableName)) {
            return 0;
        }
        
        try {
            $result = DatabaseConnectors::executeQuery(
                $this->dbName,
                "SELECT COUNT(*) as total FROM $tableName WHERE $stateColumn = ?",
                [$activeValue]
            );
            return $result[0]['total'] ?? 0;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    public function getRecentRecords($tableName, $limit = 5, $orderBy = 'id DESC') {
        try {
            return DatabaseConnectors::executeQuery(
                $this->dbName,
                "SELECT * FROM $tableName ORDER BY $orderBy LIMIT ?",
                [$limit]
            );
        } catch (Exception $e) {
            $this->error = "Error al obtener registros recientes: " . $e->getMessage();
            return [];
        }
    }
    
    protected function tableExists($tableName) {
        try {
            $result = DatabaseConnectors::executeQuery(
                $this->dbName,
                "SHOW TABLES LIKE ?",
                [$tableName]
            );
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function getError() {
        return $this->error;
    }
    
    public function getStats() {
        return $this->stats;
    }
    
    public function renderDashboard() {
        include __DIR__ . '/../views/dashboard.php';
    }
}
