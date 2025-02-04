function actualizarDatosCurso(select) {
    const option = select.options[select.selectedIndex];
    if (option.value) {
        document.getElementById('nivel_hidden').value = option.dataset.nivel;
        document.getElementById('grado_min_hidden').value = option.dataset.grado;
        document.getElementById('grado_max_hidden').value = option.dataset.grado;
    }
}

const HORA_MIN = '16:00';
const HORA_MAX = '18:30';
const DURACION_MIN = 30; // minutos

function validarHorario(horaInicio, horaFin) {
    const inicio = new Date(`2000-01-01T${horaInicio}`);
    const fin = new Date(`2000-01-01T${horaFin}`);
    const min = new Date(`2000-01-01T${HORA_MIN}`);
    const max = new Date(`2000-01-01T${HORA_MAX}`);

    // Validar rango permitido
    if (inicio < min || fin > max) {
        return {
            valido: false,
            mensaje: `El horario debe estar entre ${HORA_MIN} y ${HORA_MAX}`
        };
    }

    // Validar duración mínima
    const duracion = (fin - inicio) / 1000 / 60; // duración en minutos
    if (duracion < DURACION_MIN) {
        return {
            valido: false,
            mensaje: `La duración mínima debe ser ${DURACION_MIN} minutos`
        };
    }

    return { valido: true };
}

document.addEventListener('DOMContentLoaded', function () {
    // Validación del formulario
    const form = document.querySelector('.needs-validation');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            // Verificar que al menos un colegio esté seleccionado
            const colegiosSeleccionados = document.querySelectorAll('input[name="colegios[]"]:checked');
            if (colegiosSeleccionados.length === 0) {
                event.preventDefault();
                alert('Debe seleccionar al menos un colegio');
                return;
            }

            form.classList.add('was-validated');
        });
    }

    // Configurar restricciones de inputs time
    const horaInicio = document.querySelector('input[name="hora_inicio"]');
    const horaFin = document.querySelector('input[name="hora_fin"]');

    if (horaInicio && horaFin) {
        horaInicio.min = HORA_MIN;
        horaInicio.max = HORA_MAX;
        horaFin.min = HORA_MIN;
        horaFin.max = HORA_MAX;

        // Validar al cambiar
        [horaInicio, horaFin].forEach(input => {
            input.addEventListener('change', () => {
                if (horaInicio.value && horaFin.value) {
                    const validacion = validarHorario(horaInicio.value, horaFin.value);
                    if (!validacion.valido) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error en horario',
                            text: validacion.mensaje
                        });
                        input.value = '';
                    }
                }
            });
        });
    }

    // Animación de cards de colegios
    const colegioCards = document.querySelectorAll('#colegios-container .card');
    colegioCards.forEach(card => {
        card.addEventListener('mouseenter', function () {
            this.classList.add('shadow');
        });
        card.addEventListener('mouseleave', function () {
            this.classList.remove('shadow');
        });
    });
});
