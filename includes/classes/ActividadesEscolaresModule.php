<?php

class ActividadesEscolaresModule extends BaseModule {
    
    public function renderDashboard() {
        echo '<div class="container mt-4">';
        echo '<h2>Gestión de Actividades Extraescolares</h2>';
        
        // Mostrar mensajes de error/éxito si existen
        $this->showMessages();
        
        // Mostrar tabla de actividades
        $this->showActivitiesTable();
        
        echo '</div>';
    }

    private function showActivitiesTable() {
        $query = "SELECT * FROM actividades_extraescolares ORDER BY nombre";
        $result = $this->db->query($query);

        echo '<div class="table-responsive">';
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Nombre</th>';
        echo '<th>Descripción</th>';
        echo '<th>Horario</th>';
        echo '<th>Capacidad</th>';
        echo '<th>Acciones</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
                echo '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
                echo '<td>' . htmlspecialchars($row['horario']) . '</td>';
                echo '<td>' . htmlspecialchars($row['capacidad']) . '</td>';
                echo '<td>';
                echo '<a href="edit.php?id=' . $row['id'] . '" class="btn btn-sm btn-primary">Editar</a> ';
                echo '<a href="delete.php?id=' . $row['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'¿Está seguro?\')">Eliminar</a>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="5">No hay actividades registradas</td></tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
        
        // Botón para agregar nueva actividad
        echo '<a href="add.php" class="btn btn-success">Agregar Nueva Actividad</a>';
    }
}
