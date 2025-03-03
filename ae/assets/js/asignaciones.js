function cargarCursos(select) {
    // ... existing cargarCursos code ...
}

function actualizarNivelGrado(select) {
    // ... existing actualizarNivelGrado code ...
}

function agregarAsignacion() {
    // ... existing agregarAsignacion code ...
}

function eliminarAsignacion(btn) {
    // ... existing eliminarAsignacion code ...
}

function toggleEstado(btn) {
    // ... existing toggleEstado code ...
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function () {
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
});
