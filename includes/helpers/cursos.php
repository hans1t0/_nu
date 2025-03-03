<?php
function getNombreCurso($grado) {
    switch ($grado) {
        case 1: return "1º Infantil";
        case 2: return "2º Infantil";
        case 3: return "3º Infantil";
        case 4: return "1º Primaria";
        case 5: return "2º Primaria";
        case 6: return "3º Primaria";
        case 7: return "4º Primaria";
        case 8: return "5º Primaria";
        case 9: return "6º Primaria";
        default: return "$grado°";
    }
}

function getRangoCursos($min, $max) {
    if ($min === $max) {
        return getNombreCurso($min);
    }
    return getNombreCurso($min) . " a " . getNombreCurso($max);
}
