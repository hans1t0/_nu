// Función para exportar a Excel
async function exportarExcel() {
    try {
        const response = await fetch('../includes/export_excel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                colegio: new URLSearchParams(window.location.search).get('colegio') || ''
            })
        });

        if (!response.ok) throw new Error('Error en la exportación');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'inscripciones.xlsx';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error al exportar:', error);
        alert('Error al exportar los datos');
    }
}

// Función para ver detalles
function verDetalles(id) {
    const width = 1024;
    const height = 800;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;


    window.open(
        `../includes/ver_detalles.php?id=${id}`,
        `detalles_${id}`,
        `width=${width},height=${height},top=${top},left=${left},scrollbars=yes`
    );
}

/* Opción 2: Si necesitas la versión JSON para alguna funcionalidad
async function verDetallesJson(id) {
    try {
        const response = await fetch(`../includes/get_detalles.php?id=${id}&format=json`);
        if (!response.ok) throw new Error('Error al obtener detalles');
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cargar los detalles');
    }
}
*/

// Evitar múltiples envíos de formularios
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
        if (form.submitInProgress) {
            e.preventDefault();
            return false;
        }
        form.submitInProgress = true;
        setTimeout(() => form.submitInProgress = false, 1000);
    });
});

// Inicializaciones cuando el documento está listo
document.addEventListener('DOMContentLoaded', function () {
    // Aquí puedes añadir más inicializaciones si son necesarias
});
