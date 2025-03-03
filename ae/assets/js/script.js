console.log('script.js cargado');

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM cargado');

    // Añadir un event listener a los selectores de colegio
    const colegioSelects = document.querySelectorAll('select[name="colegio[]"]');
    colegioSelects.forEach(select => {
        select.addEventListener('change', function () {
            const colegioId = this.value;
            const hijoForm = this.closest('.hijo-form');
            const cursoSelect = hijoForm.querySelector('select[name="curso[]"]');
            const cursoId = cursoSelect.value;

            console.log('Colegio seleccionado:', colegioId, 'Curso seleccionado:', cursoId);

            if (colegioId && cursoId) {
                cargarActividades(colegioId, cursoId, hijoForm);
            } else {
                console.log('Faltan datos para cargar actividades');
            }
        });
    });

    // Añadir un event listener a los selectores de curso
    const cursoSelects = document.querySelectorAll('select[name="curso[]"]');
    cursoSelects.forEach(select => {
        select.addEventListener('change', function () {
            const cursoId = this.value;
            const hijoForm = this.closest('.hijo-form');
            const colegioSelect = hijoForm.querySelector('select[name="colegio[]"]');
            const colegioId = colegioSelect.value;

            console.log('Colegio seleccionado:', colegioId, 'Curso seleccionado:', cursoId);

            if (colegioId && cursoId) {
                cargarActividades(colegioId, cursoId, hijoForm);
            } else {
                console.log('Faltan datos para cargar actividades');
            }
        });
    });
});

async function cargarActividades(colegioId, cursoId, hijoForm) {
    console.log('Cargando actividades para:', colegioId, cursoId);

    const actividadesLista = hijoForm.querySelector('.actividades-lista');
    actividadesLista.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

    try {
        // Agregar timestamp para evitar caché
        const timestamp = new Date().getTime();
        const response = await fetch(`get_actividades.php?colegio_id=${colegioId}&curso_id=${cursoId}&t=${timestamp}`);
        const text = await response.text();
        console.log('Respuesta raw:', text); // Debug

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Error parseando JSON:', e);
            throw new Error('Respuesta inválida del servidor');
        }

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        if (data.success) {
            mostrarActividadesEnLista(data.data, hijoForm);
        } else {
            throw new Error(data.error || 'Error al cargar actividades');
        }
    } catch (error) {
        console.error('Error al cargar actividades:', error);
        actividadesLista.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
    }
}

function mostrarActividadesEnLista(actividades, hijoForm) {
    if (!hijoForm || !actividades) {
        console.error('Parámetros inválidos:', { actividades, hijoForm });
        return;
    }

    console.log('Iniciando mostrarActividadesEnLista');
    console.log('hijoForm:', hijoForm);
    console.log('actividades:', actividades);

    const actividadesLista = hijoForm.querySelector('.actividades-lista');
    if (!actividadesLista) {
        console.error('No se encontró el contenedor .actividades-lista');
        return;
    }

    // Limpiar el contenedor
    actividadesLista.innerHTML = '';
    console.log('Contenedor limpiado');

    // Verificar si hay actividades
    if (!Array.isArray(actividades) || actividades.length === 0) {
        console.log('No hay actividades para mostrar');
        actividadesLista.innerHTML = '<div class="alert alert-info">No hay actividades disponibles</div>';
        return;
    }

    try {
        // Crear la tabla con nuevo orden de columnas
        const tableHTML = `
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        id="checkAll_${hijoForm.id}" 
                                        onchange="toggleAllActivities(this, '${hijoForm.id}')">
                                    <label class="form-check-label" for="checkAll_${hijoForm.id}">Todas</label>
                                </div>
                            </th>
                            <th>Actividad</th>
                            <th class="text-center">Horario</th>
                            <th class="text-center">Cupo</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${actividades.map(act => {
            const isWaitingList = act.cupo_actual >= act.cupo_maximo;
            const badgeClass = getBadgeColor(act.cupo_actual, act.cupo_maximo);
            let estadoText = 'Disponible';
            let estadoBadgeClass = 'bg-success';

            if (isWaitingList) {
                estadoText = 'Lista de espera';
                estadoBadgeClass = 'bg-warning text-dark';
            } else if (act.cupo_actual >= 7) {
                estadoText = 'Actividad sale';
                estadoBadgeClass = 'bg-warning text-dark';
            }

            // Aquí agregamos la validación del rango de curso
            const cursoSelect = hijoForm.querySelector('select[name="curso[]"]');
            const cursoSeleccionado = parseInt(cursoSelect.value);

            // Verificar si el curso seleccionado está dentro del rango permitido
            const estaDentroRango = cursoSeleccionado >= act.desde &&
                cursoSeleccionado <= act.hasta;

            // Si no está en el rango, deshabilitar el checkbox
            const checkboxDisabled = !estaDentroRango ? 'disabled' : '';
            const rowClass = !estaDentroRango ? 'table-secondary' : '';

            return `
                <tr class="${rowClass}">
                    <td>
                        <div class="form-check">
                            <input class="form-check-input actividad-check" 
                                name="${isWaitingList ? 'lista_espera[]' : 'actividades[]'}"
                                type="checkbox" 
                                value="${act.id}" 
                                id="check_${act.id}_${hijoForm.id}"
                                data-nombre="${act.nombre.replace(/"/g, '&quot;')}"
                                data-precio="${act.precio || 0}"
                                data-lista-espera="${isWaitingList}"
                                ${checkboxDisabled}>
                        </div>
                    </td>
                    <td>
                        <div class="fw-bold">${act.nombre}</div>
                    </td>
                    <td class="text-center">${act.horario || 'No especificado'}</td>
                    <td class="text-center">
                        <span class="badge ${badgeClass}">
                            ${act.cupo_actual}/${act.cupo_maximo}
                        </span>
                    </td>
                    <td class="text-center">${act.precio ? act.precio + '€' : 'N/A'}</td>
                    <td class="text-center">
                        <span class="badge ${estadoBadgeClass}">${estadoText}</span>
                    </td>
                </tr>
            `;
        }).join('')}
                    </tbody>
                </table>
            </div>
        `;

        // Insertar la tabla en el contenedor
        console.log('Insertando tabla HTML');
        actividadesLista.innerHTML = tableHTML;

        // Verificar que la tabla se insertó correctamente
        const tablaInsertada = actividadesLista.querySelector('table');
        if (tablaInsertada) {
            console.log('Tabla insertada correctamente');
        } else {
            console.error('La tabla no se insertó correctamente');
        }

    } catch (error) {
        console.error('Error al generar la tabla:', error);
        actividadesLista.innerHTML = '<div class="alert alert-danger">Error al mostrar las actividades</div>';
    }
}

function toggleAllActivities(checkbox, formId) {
    const form = document.getElementById(formId);
    if (!form) {
        console.error('No se encontró el formulario con ID:', formId);
        return;
    }
    const checks = form.querySelectorAll('.actividad-check:not(:disabled)');
    checks.forEach(check => check.checked = checkbox.checked);
}

// Hacer la función agregarHijo global
window.agregarHijo = function () {
    const hijoTemplate = document.querySelector('.hijo-form').cloneNode(true);
    const container = document.getElementById('hijos-container');
    const numHijos = container.children.length;

    // Limpiar valores del template clonado
    hijoTemplate.querySelectorAll('input, select').forEach(input => {
        input.value = '';
        if (input.type === 'checkbox') {
            input.checked = false;
        }
    });

    // Actualizar IDs y nombres únicos
    hijoTemplate.setAttribute('data-hijo-index', numHijos + 1);
    hijoTemplate.querySelector('.actividades-lista').innerHTML = '';

    // Agregar event listeners a los nuevos selectores
    const colegioSelect = hijoTemplate.querySelector('select[name="colegio[]"]');
    const cursoSelect = hijoTemplate.querySelector('select[name="curso[]"]');

    // Agregar los mismos event listeners que el primer hijo
    colegioSelect.addEventListener('change', function () {
        const colegioId = this.value;
        const hijoForm = this.closest('.hijo-form');
        const cursoId = hijoForm.querySelector('select[name="curso[]"]').value;

        if (colegioId && cursoId) {
            cargarActividades(colegioId, cursoId, hijoForm);
        }
    });

    cursoSelect.addEventListener('change', function () {
        const cursoId = this.value;
        const hijoForm = this.closest('.hijo-form');
        const colegioId = hijoForm.querySelector('select[name="colegio[]"]').value;

        if (colegioId && cursoId) {
            cargarActividades(colegioId, cursoId, hijoForm);
        }
    });

    // Agregar el nuevo formulario al contenedor
    container.appendChild(hijoTemplate);
}

function getBadgeColor(cupoActual, cupoMaximo) {
    if (cupoActual >= cupoMaximo) {
        return 'bg-danger';    // Rojo cuando está lleno
    } else if (cupoActual >= cupoMaximo - 3) {
        return 'bg-warning';   // Naranja cuando quedan 3 o menos plazas
    } else if (cupoActual >= 7) {
        return 'bg-info text-dark';   // Naranja cuando hay 7 o más inscritos
    }
    return 'bg-success';      // Verde en otros casos
}
