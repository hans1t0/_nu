/**
 * Convierte texto de horario a array estructurado
 * Ejemplo: "LUN y VIE 17:00 a 18:00" ->
 * [
 *   ['dia' => 'Lunes', 'inicio' => '17:00', 'fin' => '18:00'],
 *   ['dia' => 'Viernes', 'inicio' => '17:00', 'fin' => '18:00']
 * ]
 */
function parseHorario($texto) {
    $horarios = [];
    
    // Normalizar abreviaturas
    $dias_map = [
        'LUN' => 'Lunes',
        'MAR' => 'Martes',
        'MIE' => 'Miércoles',
        'MIER' => 'Miércoles',
        'JUE' => 'Jueves',
        'JUEV' => 'Jueves',
        'VIE' => 'Viernes'
    ];

    // Limpiar texto
    $texto = trim(strtoupper($texto));
    
    // Extraer horas
    if (preg_match('/(\d{1,2}:\d{2})\s*A\s*(\d{1,2}:\d{2})/', $texto, $matches)) {
        $hora_inicio = $matches[1];
        $hora_fin = $matches[2];
        
        // Extraer días
        foreach ($dias_map as $abrev => $dia_completo) {
            if (strpos($texto, $abrev) !== false) {
                $horarios[] = [
                    'dia' => $dia_completo,
                    'inicio' => $hora_inicio,
                    'fin' => $hora_fin
                ];
            }
        }
    }
    
    return $horarios;
}
