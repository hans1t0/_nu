<?php
class ActividadesEscolaresModule extends BaseModule {
    protected function initializeStats() {
        // Información de actividades disponibles
        $actividades = $this->getTableInfo('horarios_disponibles');
        $this->stats['actividades'] = [
            'total' => $actividades['count'],
            'icon' => 'fa-calendar-alt',
            'color' => 'primary'
        ];
        
        // Información de inscripciones
        $inscripciones = $this->getTableInfo('inscripciones');
        $this->stats['inscripciones'] = [
            'total' => $inscripciones['count'],
            'icon' => 'fa-user-plus',
            'color' => 'success'
        ];
        
        // Información de hijos registrados
        $hijos = $this->getTableInfo('hijos');
        $this->stats['hijos'] = [
            'total' => $hijos['count'],
            'icon' => 'fa-users',
            'color' => 'info'
        ];

        // Información de colegios
        $colegios = $this->getTableInfo('colegios');
        $this->stats['colegios'] = [
            'total' => $colegios['count'],
            'icon' => 'fa-school',
            'color' => 'warning'
        ];
    }
    
    public function getQuickActions() {
        return [
            [
                'title' => 'Nueva Actividad',
                'description' => 'Crear actividad extraescolar',
                'icon' => 'fa-plus-circle',
                'color' => 'primary',
                'url' => 'actividades/crear.php'
            ],
            [
                'title' => 'Nueva Inscripción',
                'description' => 'Inscribir hijo en actividad',
                'icon' => 'fa-user-plus',
                'color' => 'success',
                'url' => 'inscripciones/crear.php'
            ],
            [
                'title' => 'Gestionar Horarios',
                'description' => 'Administrar horarios de actividades',
                'icon' => 'fa-clock',
                'color' => 'info',
                'url' => 'horarios/index.php'
            ],
            [
                'title' => 'Reportes',
                'description' => 'Ver estadísticas y reportes',
                'icon' => 'fa-chart-bar',
                'color' => 'warning',
                'url' => 'reportes/index.php'
            ]
        ];
    }

    public function renderModuleContent() {
        $actividades = $this->getActividadesRecientes();
        $inscripciones = $this->getInscripcionesRecientes();
        include __DIR__ . '/../views/actividades/dashboard_content.php';
    }

    protected function getActividadesRecientes() {
        return DatabaseConnectors::executeQuery(
            $this->dbName,
            "SELECT * FROM v_actividades_completas ORDER BY id DESC LIMIT 5"
        );
    }

    protected function getInscripcionesRecientes() {
        return DatabaseConnectors::executeQuery(
            $this->dbName,
            "SELECT i.*, h.nombre as hijo_nombre, a.actividad as nombre_actividad 
             FROM inscripciones i 
             JOIN hijos h ON i.hijo_id = h.id 
             JOIN actividades a ON i.actividad_id = a.id 
             ORDER BY fecha_inscripcion DESC LIMIT 5"
        );
    }
}
