const MAX_HIJOS = 5;

function validateDNI(dni) {
    const dniRegex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
    const nieRegex = /^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
    return dniRegex.test(dni) || nieRegex.test(dni);
}

function agregarHijo() {
    const container = document.getElementById('hijos-container');
    if (container.children.length >= MAX_HIJOS) {
        alert('No se pueden añadir más de ' + MAX_HIJOS + ' hijos');
        return;
    }
    const hijoIndex = container.children.length;

    const hijoHtml = `
        <div class="hijo-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nombre del hijo</label>
                    <input type="text" name="hijo[${hijoIndex}][nombre]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fecha nacimiento</label>
                    <input type="date" name="hijo[${hijoIndex}][fecha_nacimiento]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Colegio</label>
                    <select name="hijo[${hijoIndex}][colegio]" class="form-select" required onchange="toggleDesayuno(this, ${hijoIndex})">
                        <option value="">Seleccione colegio</option>
                        <option value="ALMADRABA">CEIP Almadraba</option>
                        <option value="COSTA">CEIP Costa Blanca</option>
                        <option value="FARO">CEIP Faro</option>
                        <option value="VORAMAR">CEIP Voramar</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Curso</label>
                    <select name="hijo[${hijoIndex}][curso]" class="form-select" required>
                        <option value="">Seleccione curso</option>
                        <option value="1INF">1º Infantil</option>
                        <option value="2INF">2º Infantil</option>
                        <option value="3INF">3º Infantil</option>
                        <option value="1PRIM">1º Primaria</option>
                        <option value="2PRIM">2º Primaria</option>
                        <option value="3PRIM">3º Primaria</option>
                        <option value="4PRIM">4º Primaria</option>
                        <option value="5PRIM">5º Primaria</option>
                        <option value="6PRIM">6º Primaria</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Hora entrada</label>
                    <select name="hijo[${hijoIndex}][hora_entrada]" class="form-select" required>
                        <option value="">Seleccione hora</option>
                        <option value="7:30">7:30</option>
                        <option value="8:00">8:00</option>
                        <option value="8:30">8:30</option>
                    </select>
                </div>
                <div class="col-md-4 desayuno-option-${hijoIndex}" style="display: none;">
                    <label class="form-label">Desayuno</label>
                    <select name="hijo[${hijoIndex}][desayuno]" class="form-select">
                        <option value="">Seleccione opción</option>
                        <option value="con">Con desayuno</option>
                        <option value="sin">Sin desayuno</option>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-eliminar" onclick="eliminarHijo(this)">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>`;

    container.insertAdjacentHTML('beforeend', hijoHtml);
}

function toggleDesayuno(selectElement, index) {
    const desayunoDiv = document.querySelector(`.desayuno-option-${index}`);
    const desayunoSelect = desayunoDiv.querySelector('select');

    if (selectElement.value === 'ALMADRABA') {
        desayunoDiv.style.display = 'block';
        desayunoSelect.required = true;
    } else {
        desayunoDiv.style.display = 'none';
        desayunoSelect.required = false;
        desayunoSelect.value = '';
    }
}

function eliminarHijo(button) {
    if (confirm('¿Está seguro de eliminar este hijo?')) {
        button.closest('.hijo-form').remove();
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function () {
    // Agregar primer hijo
    agregarHijo();

    // Validación DNI
    document.querySelector('input[name="dni"]').addEventListener('input', function (e) {
        const isValid = validateDNI(this.value);
        this.classList.toggle('is-valid', isValid);
        this.classList.toggle('is-invalid', !isValid);
        if (!isValid) {
            if (!this.nextElementSibling?.classList.contains('invalid-feedback')) {
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = 'DNI/NIE no válido';
                this.after(feedback);
            }
        }
    });

    // Submit form
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault();
        const submitBtn = this.querySelector('#submitBtn');

        if (this.checkValidity()) {
            if (confirm('¿Está seguro de enviar el formulario?')) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Enviando...';

                fetch('validar.php', {
                    method: 'POST',
                    body: new FormData(this)
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Inscripción realizada correctamente');
                            window.location.reload();
                        } else {
                            alert(data.error);
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = '<i class="bi bi-check2-circle"></i> Enviar Solicitud';
                        }
                    })
                    .catch(error => {
                        alert('Error al procesar la solicitud');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-check2-circle"></i> Enviar Solicitud';
                    });
            }
        } else {
            this.classList.add('was-validated');
        }
    });

    // Validación en tiempo real
    document.addEventListener('input', function (e) {
        if (e.target.matches('.form-control, .form-select')) {
            e.target.classList.remove('is-valid', 'is-invalid');
            if (e.target.checkValidity()) {
                e.target.classList.add('is-valid');
            } else {
                e.target.classList.add('is-invalid');
            }
        }
    }, false);
});
