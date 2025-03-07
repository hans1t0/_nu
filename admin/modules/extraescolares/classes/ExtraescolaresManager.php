<?php
/**
 * Clase para gestionar las operaciones del módulo de extraescolares
 */
class ExtraescolaresManager {
    
    /**
     * Crear un nuevo colegio
     * 
     * @param array $datos Datos del colegio
     * @return int|bool ID del colegio creado o false en caso de error
     */
    public function crearColegio(array $datos) {
        try {
            $query = "INSERT INTO colegios (nombre, direccion, telefono, email, contacto) VALUES (?, ?, ?, ?, ?)";
            $params = [
                $datos['nombre'],
                $datos['direccion'],
                $datos['telefono'],
                $datos['email'],
                $datos['contacto']
            ];
            
            $resultado = DatabaseConnectors::executeNonQuery('extraescolares', $query, $params);
            if ($resultado) {
                return DatabaseConnectors::getLastInsertId('extraescolares');
            }
            return false;
        } catch (Exception $e) {
            error_log("Error al crear colegio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un colegio existente
     * 
     * @param array $datos Datos del colegio con ID
     * @return bool Éxito de la operación
     */
    public function actualizarColegio(array $datos) {
        try {
            $query = "UPDATE colegios SET nombre = ?, direccion = ?, telefono = ?, email = ?, contacto = ? WHERE id = ?";
            $params = [
                $datos['nombre'],
                $datos['direccion'],
                $datos['telefono'],
                $datos['email'],
                $datos['contacto'],
                $datos['id']
            ];
            
            return DatabaseConnectors::executeNonQuery('extraescolares', $query, $params);
        } catch (Exception $e) {
            error_log("Error al actualizar colegio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un colegio
     * 
     * @param int $id ID del colegio
     * @return bool Éxito de la operación
     */
    public function eliminarColegio(int $id) {
        try {
            // Verificar si hay actividades asociadas
            $check = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT COUNT(*) as total FROM colegio_actividad WHERE colegio_id = ? AND activo = 1", 
                [$id]
            );
            
            if ($check[0]['total'] > 0) {
                // Desactivar las asignaciones de actividades
                DatabaseConnectors::executeNonQuery('extraescolares',
                    "UPDATE colegio_actividad SET activo = 0 WHERE colegio_id = ?",
                    [$id]
                );
            }
            
            // Eliminar el colegio (o marcar como inactivo si preferimos no eliminar)
            return DatabaseConnectors::executeNonQuery('extraescolares', 
                "DELETE FROM colegios WHERE id = ?", 
                [$id]
            );
        } catch (Exception $e) {
            error_log("Error al eliminar colegio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Crear una nueva actividad
     * 
     * @param array $datos Datos de la actividad
     * @return int|bool ID de la actividad creada o false en caso de error
     */
    public function crearActividad(array $datos) {
        try {
            $conexion = DatabaseConnectors::getConnection('extraescolares');
            $conexion->beginTransaction();
            
            // Insertar actividad sin precio
            $query = "INSERT INTO actividades (actividad, detalle_actividad, max_alumnos, horario, activa) 
                      VALUES (?, ?, ?, ?, ?)";
            $params = [
                $datos['actividad'],
                $datos['detalle_actividad'],
                $datos['max_alumnos'],
                $datos['horario'] ?? null,
                $datos['activa']
            ];
            
            $resultado = $conexion->prepare($query);
            $resultado->execute($params);
            $actividad_id = $conexion->lastInsertId();
            
            // Insertar precio con fecha actual
            if ($actividad_id) {
                $precio = number_format((float)$datos['precio'], 2, '.', '');
                $query = "INSERT INTO actividades_precio (id_actividad, precio, fecha) VALUES (?, ?, NOW())";
                $stmt = $conexion->prepare($query);
                $stmt->execute([$actividad_id, $precio]);
            }
            
            // Asignar colegios si existen
            if (!empty($datos['colegios']) && $actividad_id) {
                foreach ($datos['colegios'] as $colegio_id) {
                    $query = "INSERT INTO colegio_actividad (colegio_id, actividad_id, activo) VALUES (?, ?, 1)";
                    $stmt = $conexion->prepare($query);
                    $stmt->execute([$colegio_id, $actividad_id]);
                }
            }
            
            $conexion->commit();
            return $actividad_id;
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error al crear actividad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar una actividad existente
     * 
     * @param array $datos Datos de la actividad con ID
     * @return bool Éxito de la operación
     */
    public function actualizarActividad(array $datos) {
        try {
            $conexion = DatabaseConnectors::getConnection('extraescolares');
            $conexion->beginTransaction();
            
            // Actualizar actividad básica
            $query = "UPDATE actividades SET 
                      actividad = ?, 
                      detalle_actividad = ?, 
                      max_alumnos = ?, 
                      horario = ?,
                      activa = ? 
                      WHERE id = ?";
            
            $stmt = $conexion->prepare($query);
            $stmt->execute([
                $datos['actividad'],
                $datos['detalle_actividad'],
                $datos['max_alumnos'],
                $datos['horario'] ?? null,
                $datos['activa'],
                $datos['id']
            ]);
            
            // Obtener precio actual
            $precio_actual = DatabaseConnectors::executeQuery('extraescolares',
                "SELECT precio 
                 FROM actividades_precio 
                 WHERE id_actividad = ? 
                 ORDER BY fecha DESC LIMIT 1",
                [$datos['id']]
            );
            
            $precio_nuevo = number_format((float)$datos['precio'], 2, '.', '');
            $precio_actual = !empty($precio_actual) ? $precio_actual[0]['precio'] : null;
            
            // Insertar nuevo precio solo si ha cambiado
            if ($precio_actual === null || number_format((float)$precio_actual, 2, '.', '') !== $precio_nuevo) {
                $stmt = $conexion->prepare(
                    "INSERT INTO actividades_precio (id_actividad, precio, fecha) VALUES (?, ?, NOW())"
                );
                $stmt->execute([$datos['id'], $precio_nuevo]);
            }
            
            // Desactivar todas las asignaciones actuales
            $query = "UPDATE colegio_actividad SET activo = 0 WHERE actividad_id = ?";
            $stmt = $conexion->prepare($query);
            $stmt->execute([$datos['id']]);
            
            // Asignar colegios nuevamente
            if (!empty($datos['colegios'])) {
                foreach ($datos['colegios'] as $colegio_id) {
                    // Verificar si la asignación ya existe
                    $check = $conexion->prepare("SELECT id FROM colegio_actividad WHERE colegio_id = ? AND actividad_id = ?");
                    $check->execute([$colegio_id, $datos['id']]);
                    $asignacion = $check->fetch(PDO::FETCH_ASSOC);
                    
                    if ($asignacion) {
                        // Reactivar la asignación
                        $query = "UPDATE colegio_actividad SET activo = 1 WHERE id = ?";
                        $stmt = $conexion->prepare($query);
                        $stmt->execute([$asignacion['id']]);
                    } else {
                        // Crear nueva asignación
                        $query = "INSERT INTO colegio_actividad (colegio_id, actividad_id, activo) VALUES (?, ?, 1)";
                        $stmt = $conexion->prepare($query);
                        $stmt->execute([$colegio_id, $datos['id']]);
                    }
                }
            }
            
            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error al actualizar actividad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar una actividad
     * 
     * @param int $id ID de la actividad
     * @return bool Éxito de la operación
     */
    public function eliminarActividad(int $id) {
        try {
            $conexion = DatabaseConnectors::getConnection('extraescolares');
            $conexion->beginTransaction();
            
            // Eliminar inscripciones asociadas
            $stmt = $conexion->prepare("DELETE FROM inscripciones WHERE actividad_id = ?");
            $stmt->execute([$id]);
            
            // Eliminar relaciones con colegios
            $stmt = $conexion->prepare("DELETE FROM colegio_actividad WHERE actividad_id = ?");
            $stmt->execute([$id]);
            
            // Eliminar la actividad
            $stmt = $conexion->prepare("DELETE FROM actividades WHERE id = ?");
            $stmt->execute([$id]);
            
            $conexion->commit();
            return true;
        } catch (Exception $e) {
            if (isset($conexion)) {
                $conexion->rollBack();
            }
            error_log("Error al eliminar actividad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener los totales para el dashboard
     * 
     * @return array Array con datos estadísticos
     */
    public function obtenerEstadisticas() {
        try {
            $stats = [
                'total_colegios' => 0,
                'total_actividades' => 0,
                'total_inscripciones' => 0,
                'inscripciones_por_mes' => [],
                'actividades_por_categoria' => [],
                'ocupacion_por_actividad' => []
            ];
            
            // Total de colegios
            $result = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT COUNT(*) as total FROM colegios"
            );
            $stats['total_colegios'] = $result[0]['total'] ?? 0;
            
            // Total de actividades
            $result = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT COUNT(*) as total FROM actividades WHERE activa = 1"
            );
            $stats['total_actividades'] = $result[0]['total'] ?? 0;
            
            // Total de inscripciones
            $result = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT COUNT(*) as total FROM inscripciones WHERE estado = 'confirmada'"
            );
            $stats['total_inscripciones'] = $result[0]['total'] ?? 0;
            
            // Inscripciones por mes (últimos 6 meses)
            $stats['inscripciones_por_mes'] = DatabaseConnectors::executeQuery('extraescolares', 
                "SELECT DATE_FORMAT(fecha_inscripcion, '%Y-%m') as mes, COUNT(*) as total 
                FROM inscripciones 
                WHERE fecha_inscripcion >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) 
                GROUP BY DATE_FORMAT(fecha_inscripcion, '%Y-%m') 
                ORDER BY mes ASC"
            );
            
            // Ocupación por actividad
            $stats['ocupacion_por_actividad'] = DatabaseConnectors::executeQuery('extraescolares',
                "SELECT 
                    a.id, 
                    a.actividad, 
                    a.max_alumnos, 
                    COUNT(i.id) as inscritos,
                    ROUND((COUNT(i.id) / a.max_alumnos) * 100, 2) as porcentaje_ocupacion
                FROM actividades a
                LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
                WHERE a.activa = 1
                GROUP BY a.id
                ORDER BY porcentaje_ocupacion DESC
                LIMIT 10"
            );
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generar un reporte específico
     * 
     * @param string $tipo Tipo de reporte
     * @param array $filtros Filtros aplicados al reporte
     * @return array Datos del reporte
     */
    public function generarReporte($tipo, $filtros = []) {
        try {
            $resultado = [];
            
            switch ($tipo) {
                case 'inscripciones_por_actividad':
                    $query = "SELECT 
                        a.actividad, 
                        COUNT(i.id) as total_inscripciones 
                    FROM actividades a 
                    LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
                    GROUP BY a.id 
                    ORDER BY total_inscripciones DESC";
                    
                    $resultado = DatabaseConnectors::executeQuery('extraescolares', $query);
                    break;
                    
                case 'inscripciones_por_colegio':
                    $query = "SELECT 
                        c.nombre as colegio, 
                        COUNT(i.id) as total_inscripciones 
                    FROM colegios c 
                    JOIN colegio_actividad ca ON c.id = ca.colegio_id 
                    JOIN inscripciones i ON ca.actividad_id = i.actividad_id 
                    WHERE i.estado = 'confirmada' AND ca.activo = 1 
                    GROUP BY c.id 
                    ORDER BY total_inscripciones DESC";
                    
                    $resultado = DatabaseConnectors::executeQuery('extraescolares', $query);
                    break;
                    
                case 'actividades_por_colegio':
                    $colegio_id = $filtros['colegio_id'] ?? null;
                    
                    if ($colegio_id) {
                        $query = "SELECT 
                            a.actividad, 
                            a.max_alumnos,
                            COUNT(i.id) as total_inscritos,
                            a.precio
                        FROM actividades a 
                        JOIN colegio_actividad ca ON a.id = ca.actividad_id 
                        LEFT JOIN inscripciones i ON a.id = i.actividad_id AND i.estado = 'confirmada'
                        WHERE ca.colegio_id = ? AND ca.activo = 1 AND a.activa = 1
                        GROUP BY a.id
                        ORDER BY a.actividad";
                        
                        $resultado = DatabaseConnectors::executeQuery('extraescolares', $query, [$colegio_id]);
                    }
                    break;
                    
                case 'ingresos_por_actividad':
                    $fecha_inicio = $filtros['fecha_inicio'] ?? null;
                    $fecha_fin = $filtros['fecha_fin'] ?? null;
                    
                    $condiciones = [];
                    $params = [];
                    
                    if ($fecha_inicio) {
                        $condiciones[] = "i.fecha_inscripcion >= ?";
                        $params[] = $fecha_inicio;
                    }
                    
                    if ($fecha_fin) {
                        $condiciones[] = "i.fecha_inscripcion <= ?";
                        $params[] = $fecha_fin;
                    }
                    
                    $where = !empty($condiciones) ? "AND " . implode(' AND ', $condiciones) : "";
                    
                    $query = "SELECT 
                        a.actividad,
                        COUNT(i.id) as total_inscripciones,
                        a.precio,
                        (COUNT(i.id) * a.precio) as ingresos_totales
                    FROM actividades a
                    JOIN inscripciones i ON a.id = i.actividad_id
                    WHERE i.estado = 'confirmada' $where
                    GROUP BY a.id
                    ORDER BY ingresos_totales DESC";
                    
                    $resultado = DatabaseConnectors::executeQuery('extraescolares', $query, $params);
                    break;
            }
            
            return $resultado;
        } catch (Exception $e) {
            error_log("Error al generar reporte: " . $e->getMessage());
            return [];
        }
    }

    public function asignarActividadColegio($actividad_id, $colegio_id) {
        try {
            DatabaseConnectors::executeQuery('extraescolares',
                "INSERT INTO colegio_actividad (colegio_id, actividad_id, activo) 
                 VALUES (?, ?, 1)
                 ON DUPLICATE KEY UPDATE activo = 1",
                [$colegio_id, $actividad_id]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function desasignarActividadColegio($actividad_id, $colegio_id) {
        try {
            DatabaseConnectors::executeQuery('extraescolares',
                "UPDATE colegio_actividad 
                 SET activo = 0 
                 WHERE colegio_id = ? AND actividad_id = ?",
                [$colegio_id, $actividad_id]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
