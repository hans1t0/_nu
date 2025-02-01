/**
 * Actualiza los números de identificación de cada hijo
 * y los muestra en una etiqueta sobre cada formulario
 */
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

/**
 * Agrega un nuevo formulario de hijo al contenedor
 * Clona los selectores y mantiene las opciones
 * Inicializa los tooltips en los nuevos elementos
 */
function agregarHijo() {
    const container = document.getElementById('hijos-container');
    const nuevo = document.createElement('div');
    nuevo.className = 'hijo-form row g-3 mb-3';

    // Plantilla HTML para el nuevo formulario de hijo
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

    // Agregar el nuevo formulario al contenedor
    container.appendChild(nuevo);
    actualizarNumerosHijos();

    // Inicializar tooltips en los elementos nuevos
    const tooltips = nuevo.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(el => new bootstrap.Tooltip(el));
}

/**
 * Maneja la eliminación de un hijo con confirmación
 * @param {HTMLElement} button - El botón de eliminar que fue clickeado
 */
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

// Cuando el DOM está completamente cargado
document.addEventListener('DOMContentLoaded', function () {
    // Inicialización inicial
    actualizarNumerosHijos();

    // Configurar el botón de agregar hijo
    document.getElementById('btnAgregarHijo').addEventListener('click', agregarHijo);

    // Inicializar todos los tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // Validación del formulario y envío AJAX
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (this.checkValidity()) {
                const formData = new FormData(this);

                // Proceso de dos pasos: validación y registro
                fetch('validar.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        // Si la validación es exitosa, proceder con el registro
                        if (data.valid) {
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
                        // Mostrar resumen si el registro fue exitoso
                        if (data.success) {
                            mostrarResumen(data);
                        } else {
                            throw new Error(data.message);
                        }
                    })
                    .catch(error => {
                        // Mostrar errores si algo falla
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

/**
 * Muestra un resumen del registro exitoso usando SweetAlert2
 * @param {Object} data - Datos del registro (padre e hijos)
 */
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
