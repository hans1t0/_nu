function actualizarNumerosHijos() {
    document.querySelectorAll('.hijo-form').forEach((form, index) => {
        let numeroSpan = form.querySelector('.hijo-numero');
        if (!numeroSpan) {
            numeroSpan = document.createElement('span');
            numeroSpan.className = 'hijo-numero';
            form.appendChild(numeroSpan);
        }
        numeroSpan.textContent = `Hijo ${index + 1}`;
    });
}

function agregarHijo() {
    const container = document.getElementById('hijos-container');
    const nuevo = document.createElement('div');
    nuevo.className = 'hijo-form row g-3 mb-3';

    nuevo.innerHTML = `
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-person-badge"></i>
                Nombre:
            </label>
            <input type="text" name="nombre_hijo[]" class="form-control" required>
            <div class="invalid-feedback">Ingrese nombre</div>
        </div>
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-building"></i>
                Colegio:
            </label>
            <select name="colegio[]" class="form-select" required>
                <option value="">Seleccione colegio</option>
                ${document.querySelector('select[name="colegio[]"]').innerHTML.split('<option value="">Seleccione colegio</option>')[1]}
            </select>
            <div class="invalid-feedback">Seleccione colegio</div>
        </div>
        <div class="col-md-3">
            <label class="form-label">
                <i class="bi bi-calendar-event"></i>
                Fecha Nacimiento:
            </label>
            <input type="date" name="fecha_nacimiento[]" class="form-control" required>
            <div class="invalid-feedback">Ingrese fecha</div>
        </div>
        <div class="col-md-2">
            <label class="form-label">
                <i class="bi bi-mortarboard-fill"></i>
                Curso:
            </label>
            <select name="curso[]" class="form-select" required>
                <option value="">Seleccione curso</option>
                ${document.querySelector('select[name="curso[]"]').innerHTML.split('<option value="">Seleccione curso</option>')[1]}
            </select>
            <div class="invalid-feedback">Seleccione curso</div>
        </div>
        <div class="col-md-1">
            <label class="form-label text-white">.</label>
            <button type="button" class="btn btn-eliminar" onclick="eliminarHijo(this)" data-bs-toggle="tooltip" data-bs-title="Eliminar hijo">
                <i class="bi bi-trash"></i>
                <span class="d-none d-sm-inline">Eliminar</span>
            </button>
        </div>
    `;

    container.appendChild(nuevo);
    actualizarNumerosHijos();

    // Inicializar tooltips en los nuevos elementos
    const tooltips = nuevo.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
}

function eliminarHijo(button) {
    Swal.fire({
        title: '¿Eliminar hijo?',
        text: "¿Estás seguro de eliminar este registro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            button.closest('.hijo-form').remove();
            actualizarNumerosHijos();
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    actualizarNumerosHijos();
    // Agregar el evento click al botón
    document.getElementById('btnAgregarHijo').addEventListener('click', agregarHijo);

    // Inicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // Validación del formulario
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (this.checkValidity()) {
                const formData = new FormData(this);

                // Validar primero
                fetch('validar.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.valid) {
                            // Si es válido, proceder con el registro
                            return fetch('process.php', {
                                method: 'POST',
                                body: formData
                            });
                        } else {
                            throw new Error(data.errors.join('\n'));
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            mostrarResumen(data);
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
            this.classList.add('was-validated');
        });
    });
});

// Mantener la función mostrarResumen fuera para acceso global
function mostrarResumen(data) {
    let resumenHtml = `
        <h4>Padre/Madre</h4>
        <p><strong>Nombre:</strong> ${data.padre.nombre}</p>
        <p><strong>DNI:</strong> ${data.padre.dni}</p>
        <p><strong>Email:</strong> ${data.padre.email}</p>
        
        <h4>Hijos Registrados</h4>
        <ul class="list-group">`;

    data.hijos.forEach(hijo => {
        resumenHtml += `
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">${hijo.nombre}</h5>
                        <p class="mb-1"><strong>Colegio:</strong> ${hijo.colegio}</p>
                        <p class="mb-1"><strong>Curso:</strong> ${hijo.curso}</p>
                    </div>
                </div>
            </li>`;
    });

    resumenHtml += `</ul>`;

    Swal.fire({
        icon: 'success',
        title: '¡Registro Exitoso!',
        html: resumenHtml,
        confirmButtonText: 'Aceptar',
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php';
        }
    });
}
