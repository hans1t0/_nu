function exportarExcel() {
    const filtros = new URLSearchParams(window.location.search);
    window.location.href = 'export.php?' + filtros.toString();
}

function verDetalles(id) {
    fetch('detalles.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            // Aquí implementarías la lógica para mostrar un modal con los detalles
            console.log(data);
        })
        .catch(error => console.error('Error:', error));
}

// Inicializaciones cuando el documento está listo
document.addEventListener('DOMContentLoaded', function () {
    // Aquí puedes añadir más inicializaciones si son necesarias
});
