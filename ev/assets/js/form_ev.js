function validateDNI(dni) {
    const dniRegex = /^[0-9]{8}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
    const nieRegex = /^[XYZ][0-9]{7}[TRWAGMYFPDXBNJZSQVHLCKE]$/i;
    return dniRegex.test(dni) || nieRegex.test(dni);
}

let participanteCount = 0;

function agregarParticipante() {
    participanteCount++;
    const container = document.getElementById('hijos-container');

    const participanteHtml = `
        <div class="participante-form mb-4 p-3 border rounded">
            <h3 class="h6 mb-3">Participante ${participanteCount}</h3>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="participante[${participanteCount}][nombre]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de nacimiento</label>
                    <input type="date" name="participante[${participanteCount}][fecha_nacimiento]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Centro escolar actual</label>
                    <select name="participante[${participanteCount}][centro_actual]" class="form-control" required>
                        <option value="">Seleccione centro</option>
                        <option value="ALMADRABA">CEIP Almadraba</option>
                        <option value="COSTA_BLANCA">CEIP Costa Blanca</option>
                        <option value="FARO">CEIP Faro</option>
                        <option value="VORAMAR">CEIP Voramar</option>
                        <option value="OTRO">Otro centro</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Curso actual</label>
                    <select name="participante[${participanteCount}][curso]" class="form-control" required>
                        <option value="">Seleccione curso</option>
                        <optgroup label="Infantil">
                            <option value="1INF">1º Infantil (3 años)</option>
                            <option value="2INF">2º Infantil (4 años)</option>
                            <option value="3INF">3º Infantil (5 años)</option>
                        </optgroup>
                        <optgroup label="Primaria">
                            <option value="1PRIM">1º Primaria</option>
                            <option value="2PRIM">2º Primaria</option>
                            <option value="3PRIM">3º Primaria</option>
                            <option value="4PRIM">4º Primaria</option>
                            <option value="5PRIM">5º Primaria</option>
                            <option value="6PRIM">6º Primaria</option>
                        </optgroup>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Alergias/Observaciones médicas</label>
                    <input type="text" name="participante[${participanteCount}][alergias]" class="form-control">
                </div>
                <div class="col-12">
                    <button type="button" class="btn btn-danger btn-sm" onclick="eliminarParticipante(this)">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', participanteHtml);
}

function eliminarParticipante(button) {
    button.closest('.participante-form').remove();
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function () {
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
});
