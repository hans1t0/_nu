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
    const hijoIndex = container.querySelectorAll('.hijo-form').length;
    const nuevo = document.createElement('div');
    nuevo.className = 'hijo-form row g-3 mb-3';
    nuevo.dataset.hijoIndex = hijoIndex;

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
        <div class="col-12">
            <div class="actividades-container mt-3" style="display:none;">
                <hr>
                <h6 class="mb-3">
                    <i class="bi bi-award"></i>
                    Actividades disponibles:
                </h6>
                <div class="actividades-lista row g-3">
                    <!-- Las actividades se cargarán dinámicamente -->
                </div>
            </div>
        </div>
    `;

    // Agregar el nuevo formulario al contenedor
    container.appendChild(nuevo);
    actualizarNumerosHijos();

    // Añadir listener para el selector de colegio
    const colegioSelect = nuevo.querySelector('select[name="colegio[]"]');
    colegioSelect.addEventListener('change', () => cargarActividades(colegioSelect));

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

    // Manejar cambios en selector de colegio
    document.body.addEventListener('change', function (e) {
        if (e.target.matches('select[name="colegio[]"]') ||
            e.target.matches('select[name="curso[]"]')) {
            const hijoForm = e.target.closest('.hijo-form');
            const colegioSelect = hijoForm.querySelector('select[name="colegio[]"]');
            if (colegioSelect.value) {
                cargarActividades(colegioSelect);
            }
        }
    });

    // Añadir listener para el primer selector de colegio
    const primerSelector = document.querySelector('select[name="colegio[]"]');
    if (primerSelector) {
        primerSelector.addEventListener('change', () => cargarActividades(primerSelector));
    }
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

/**
 * Carga las actividades disponibles para un colegio
 * @param {HTMLSelectElement} selector - El selector de colegio que cambió
 */
function cargarActividades(selector) {
    const hijoForm = selector.closest('.hijo-form');
    const actividadesContainer = hijoForm.querySelector('.actividades-container');
    const actividadesLista = hijoForm.querySelector('.actividades-lista');
    const colegioId = selector.value;
    const cursoSelect = hijoForm.querySelector('select[name="curso[]"]');
    const cursoId = cursoSelect.value;

    // Ocultar container si no hay colegio o curso seleccionado
    if (!colegioId || !cursoId) {
        actividadesContainer.style.display = 'none';
        return;
    }

    // Mostrar indicador de carga
    actividadesLista.innerHTML = '<div class="col-12 text-center"><div class="spinner-border text-primary" role="status"></div></div>';
    actividadesContainer.style.display = 'block';

    fetch(`get_actividades.php?colegio_id=${colegioId}&curso_id=${cursoId}`)
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                throw new Error(result.error || 'Error al cargar actividades');
            }

            const actividades = result.data;

            if (!actividades || actividades.length === 0) {
                actividadesLista.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-info">
                            No hay actividades disponibles para ${result.nivel}
                        </div>
                    </div>`;
                return;
            }

            actividadesLista.innerHTML = actividades.map(act => `
                <div class="col-md-6 mb-2">
                    <div class="card h-100 ${act.cupo_lleno ? 'bg-light' : ''}">
                        <div class="card-body">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="actividades[${hijoForm.dataset.hijoIndex}][]" 
                                       value="${act.id}" 
                                       id="act-${act.id}-${hijoForm.dataset.hijoIndex}"
                                       ${act.cupo_lleno ? 'disabled' : ''}>
                                <label class="form-check-label ${act.cupo_lleno ? 'text-muted' : ''}" 
                                       for="act-${act.id}-${hijoForm.dataset.hijoIndex}">
                                    <strong>${act.nombre}</strong>
                                    <div class="text-muted small">
                                        <div>${act.horario}</div>
                                        <div>Precio: ${act.precio}€</div>
                                        <div>Cupos: ${act.cupo_actual}/${act.cupo_maximo} 
                                             ${act.cupo_lleno ? '(COMPLETO)' : ''}</div>
                                        <div class="text-info">${act.duracion}</div>
                                        <div class="badge bg-secondary mt-1">${act.rango_grados}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error:', error);
            actividadesLista.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-danger">
                        Error al cargar actividades: ${error.message}
                    </div>
                </div>`;
        });
}
