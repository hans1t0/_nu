function cambiarEstado(id) {
    if (!confirm('¿Desea cambiar el estado de esta inscripción?')) {
        return;
    }

    fetch('ajax/cambiar_estado.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&estado=confirmado`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cambiar el estado');
        });
}

function verDetalles(id) {
    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));

    fetch(`ajax/get_detalles.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const modalBody = document.querySelector('#detallesModal .modal-body');
            modalBody.innerHTML = `
                <dl class="row">
                    <dt class="col-sm-4">Alumno:</dt>
                    <dd class="col-sm-8">${data.nombre_alumno}</dd>
                    
                    <dt class="col-sm-4">Responsable:</dt>
                    <dd class="col-sm-8">${data.responsable_nombre}</dd>
                    
                    <dt class="col-sm-4">Contacto:</dt>
                    <dd class="col-sm-8">${data.responsable_email}<br>${data.responsable_telefono}</dd>
                    
                    <dt class="col-sm-4">Forma de pago:</dt>
                    <dd class="col-sm-8">${data.forma_pago}</dd>
                    
                    <dt class="col-sm-4">Estado:</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-${data.estado === 'pendiente' ? 'warning' : 'success'}">
                            ${data.estado}
                        </span>
                    </dd>
                </dl>
            `;
            modal.show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar los detalles');
        });
}
