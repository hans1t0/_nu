// Búsqueda en tiempo real
document.getElementById('buscarFamilia').addEventListener('input', function (e) {
    const busqueda = e.target.value.toLowerCase();
    const tabla = document.getElementById('tablaFamilias');
    const filas = tabla.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    Array.from(filas).forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(busqueda) ? '' : 'none';
    });
});

// Ver detalles de familia
function verDetalles(id) {
    fetch(`get_familia.php?id=${id}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error al obtener los datos');
            }
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }

            const familia = data.familia;
            const padre = familia.padre;
            const hijos = familia.hijos;

            let detallesHtml = `
                <div class="text-start">
                    <h5 class="mb-3">Datos del Padre/Madre</h5>
                    <ul class="list-unstyled">
                        <li><strong>Nombre:</strong> ${padre.nombre_completo}</li>
                        <li><strong>DNI:</strong> ${padre.dni}</li>
                        <li><strong>Email:</strong> ${padre.email}</li>
                        <li><strong>Teléfono:</strong> ${padre.telefono}</li>
                        <li><strong>Fecha registro:</strong> ${new Date(padre.created_at).toLocaleString()}</li>
                    </ul>

                    <h5 class="mt-4 mb-3">Hijos</h5>
                    ${hijos.map(hijo => `
                        <div class="card mb-2">
                            <div class="card-body">
                                <h6 class="card-title">${hijo.nombre} (${hijo.edad} años)</h6>
                                <p class="mb-1"><strong>Colegio:</strong> ${hijo.colegio}</p>
                                <p class="mb-1"><strong>Curso:</strong> ${hijo.curso}</p>
                                <p class="mb-0"><strong>Actividades:</strong> 
                                    ${hijo.actividades.length ?
                    `<ul class="mb-0">
                                            ${hijo.actividades.map(act => `<li>${act}</li>`).join('')}
                                        </ul>` :
                    'No inscrito en actividades'}
                                </p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;

            Swal.fire({
                title: 'Detalles de la Familia',
                html: detallesHtml,
                width: '600px',
                confirmButtonText: 'Cerrar'
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message
            });
        });
}

// Eliminar familia
function eliminarFamilia(id) {
    Swal.fire({
        title: '¿Eliminar familia?',
        text: "Esta acción no se puede deshacer",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('eliminar_familia.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: id })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: data.message
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.message);
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: error.message
                    });
                });
        }
    });
}

// Exportar datos
function exportarDatos() {
    window.location.href = 'exportar.php';
}
