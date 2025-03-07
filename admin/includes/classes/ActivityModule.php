<?php
class ActivityModule extends BaseModule {
    protected function initializeStats() {
        // Información de actividades
        $activities = $this->getTableInfo('actividades');
        $this->stats['actividades'] = [
            'total' => $activities['count'],
            'activas' => $this->getActiveRecords('actividades'),
            'icon' => 'fa-calendar-alt',
            'color' => 'primary'
        ];
        
        // Información de inscripciones
        $inscriptions = $this->getTableInfo('inscripciones');
        $this->stats['inscripciones'] = [
            'total' => $inscriptions['count'],
            'icon' => 'fa-user-plus',
            'color' => 'success'
        ];
        
        // Información de alumnos
        $students = $this->getTableInfo('alumnos');
        $this->stats['alumnos'] = [
            'total' => $students['count'],
            'icon' => 'fa-users',
            'color' => 'info'
        ];
    }
    
    public function getQuickActions() {
        return [
            [
                'title' => 'Nueva Actividad',
                'description' => 'Crear actividad',
                'icon' => 'fa-plus-circle',
                'color' => 'primary',
                'url' => 'crear_actividad.php'
            ],
            [
                'title' => 'Nueva Inscripción',
                'description' => 'Inscribir alumno',
                'icon' => 'fa-user-plus',
                'color' => 'success',
                'url' => 'inscripciones.php?action=nueva'
            ],
            // ... más acciones rápidas
        ];
    }
    
    public function getRecentActivities() {
        return $this->getRecentRecords('actividades');
    }
    
    public function getRecentInscriptions() {
        try {
            return DatabaseConnectors::executeQuery(
                $this->dbName,
                'SELECT i.*, a.nombre as alumno_nombre, a.apellidos as alumno_apellidos, 
                 act.nombre as actividad_nombre 
                 FROM inscripciones i 
                 JOIN alumnos a ON i.alumno_id = a.id 
                 JOIN actividades act ON i.actividad_id = act.id 
                 ORDER BY i.id DESC LIMIT 5'
            );
        } catch (Exception $e) {
            $this->error = "Error al obtener inscripciones recientes: " . $e->getMessage();
            return [];
        }
    }
}
