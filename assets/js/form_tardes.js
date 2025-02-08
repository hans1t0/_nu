let contadorHijos = 0;

function agregarHijo() {
    const container = document.getElementById('hijos-container');
    contadorHijos++;

    const hijoHTML = `
        <div class="hijo-form mb-4" id="hijo-${contadorHijos}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="h6 mb-0">Hijo/a ${contadorHijos}</h3>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarHijo(${contadorHijos})">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre completo</label>
                    <input type="text" name="nombre_hijo[]" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fecha de nacimiento</label>
                    <input type="date" name="fecha_nacimiento[]" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Centro</label>
                    <select name="centro[]" class="form-select" required>
                        <option value="">Seleccione centro...</option>
                        <option value="ALMADRABA">CEIP La Almadraba</option>
                        <option value="COSTA BLANCA">CEIP Costa Blanca</option>
                        <option value="FARO">CEIP Faro</option>
                        <option value="VORAMAR">CEIP Voramar</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Curso</label>
                    <select name="curso[]" class="form-select" required>
                        <option value="">Seleccione curso...</option>
                        <option value="INF3">Infantil 3 años</option>
                        <option value="INF4">Infantil 4 años</option>
                        <option value="INF5">Infantil 5 años</option>
                        <option value="PRI1">1º Primaria</option>
                        <option value="PRI2">2º Primaria</option>
                        <option value="PRI3">3º Primaria</option>
                        <option value="PRI4">4º Primaria</option>
                        <option value="PRI5">5º Primaria</option>
                        <option value="PRI6">6º Primaria</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Horario de Salida</label>
                    <select name="horario[]" class="form-select" required>
                        <option value="">Seleccione horario...</option>
                        <option value="16:00">16:00</option>
                        <option value="17:00">17:00</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Alergias/Enfermedades/Intolerancias alimentarias</label>
                    <textarea name="alergias[]" class="form-control" rows="2" 
                        placeholder="Indique si el alumno tiene alguna alergia, enfermedad o intolerancia alimentaria que debamos conocer"></textarea>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', hijoHTML);
    actualizarBotones();
}

function eliminarHijo(id) {
    const hijoElement = document.getElementById(`hijo-${id}`);
    if (hijoElement) {
        hijoElement.remove();
        actualizarBotones();
    }
}

function actualizarBotones() {
    const submitBtn = document.getElementById('submitBtn');
    const hijos = document.querySelectorAll('.hijo-form');

    if (hijos.length === 0) {
        submitBtn.disabled = true;
    } else {
        submitBtn.disabled = false;
    }
}

// Inicialización del formulario
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    // Validación del formulario
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });

    // Deshabilitar botón de envío inicial
    document.getElementById('submitBtn').disabled = true;
});
