function verDetalles(id) {
    fetch(`get_familia.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            let hijosHtml = '';
            data.hijos.forEach(hijo => {
                hijosHtml += `
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>${hijo.nombre}</h6>
                            <p class="mb-1"><strong>Colegio:</strong> ${hijo.colegio}</p>
                            <p class="mb-1"><strong>Curso:</strong> ${hijo.curso}</p>
                            <p class="mb-0"><strong>Fecha Nacimiento:</strong> ${hijo.fecha_nacimiento}</p>
                        </div>
                    </div>
                `;
            });

            Swal.fire({
                title: 'Detalles de la Familia',
                html: `
                    <div class="text-start">
                        <h5>Padre/Madre</h5>
                        <p><strong>Nombre:</strong> ${data.padre.nombre}</p>
                        <p><strong>DNI:</strong> ${data.padre.dni}</p>
                        <p><strong>Email:</strong> ${data.padre.email}</p>
                        <p><strong>Teléfono:</strong> ${data.padre.telefono}</p>
                        
                        <h5 class="mt-3">Hijos</h5>
                        ${hijosHtml}
                    </div>
                `,
                width: '600px'
            });
        });
}

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
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
        }
    });
}

// Búsqueda en tiempo real
document.getElementById('buscarFamilia').addEventListener('keyup', function () {
    const busqueda = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(tr => {
        const texto = tr.textContent.toLowerCase();
        tr.style.display = texto.includes(busqueda) ? '' : 'none';
    });
});
