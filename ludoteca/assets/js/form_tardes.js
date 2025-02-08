document.getElementById('formaPago').addEventListener('change', function () {
    const infoTransferencia = document.getElementById('infoPagoTransferencia');
    const infoCoordinador = document.getElementById('infoPagoCoordinador');
    const campoDomiciliacion = document.getElementById('campoDomiciliacion');
    const ibanInput = document.getElementById('ibanInput');

    // Ocultar todos los elementos primero
    infoTransferencia.style.display = 'none';
    infoCoordinador.style.display = 'none';
    campoDomiciliacion.style.display = 'none';
    ibanInput.required = false;

    // Mostrar según selección
    switch (this.value) {
        case 'transferencia':
            infoTransferencia.style.display = 'block';
            break;
        case 'coordinador':
            infoCoordinador.style.display = 'block';
            break;
        case 'domiciliacion':
            campoDomiciliacion.style.display = 'block';
            ibanInput.required = true;
            break;
    }
});

function agregarHijo() {
    const container = document.getElementById('hijos-container');
    const index = container.children.length;

    const hijoHTML = `
        <div class="card mb-3">
            <div class="card-body">
                <button type="button" class="btn-close float-end" onclick="this.closest('.card').remove()"></button>
                <h3 class="h6 mb-3">Hijo/a ${index + 1}</h3>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" name="hijos[${index}][nombre]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Apellidos</label>
                        <input type="text" name="hijos[${index}][apellidos]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de Nacimiento</label>
                        <input type="date" name="hijos[${index}][fecha_nacimiento]" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Centro</label>
                        <select name="hijos[${index}][centro]" class="form-select" required>
                            <option value="">Seleccione centro</option>
                            <option value="1">CEIP Almadraba</option>
                            <option value="2">CEIP Costa Blanca</option>
                            <option value="3">CEIP Faro</option>
                            <option value="4">CEIP Voramar</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Curso</label>
                        <select name="hijos[${index}][curso]" class="form-select" required>
                            <option value="">Seleccione curso</option>
                            <option value="1INF">1º Infantil</option>
                            <option value="2INF">2º Infantil</option>
                            <option value="3INF">3º Infantil</option>
                            <option value="1PRI">1º Primaria</option>
                            <option value="2PRI">2º Primaria</option>
                            <option value="3PRI">3º Primaria</option>
                            <option value="4PRI">4º Primaria</option>
                            <option value="5PRI">5º Primaria</option>
                            <option value="6PRI">6º Primaria</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Horario</label>
                        <select name="hijos[${index}][horario]" class="form-select" required>
                            <option value="">Seleccione horario</option>
                            <option value="1">Hasta las 16:00 (25€)</option>
                            <option value="2">Hasta las 17:00 (35€)</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Alergias/Intolerancias</label>
                        <textarea name="hijos[${index}][alergias]" class="form-control" rows="2"></textarea>
                    </div>              
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', hijoHTML);
}

// Agregar el primer hijo automáticamente al cargar la página
document.addEventListener('DOMContentLoaded', function () {
    agregarHijo();
});

// Validación del formulario antes de enviar
document.querySelector('form').addEventListener('submit', function (e) {
    const hijos = document.getElementById('hijos-container').children;
    if (hijos.length === 0) {
        e.preventDefault();
        alert('Debe agregar al menos un hijo');
    }
});
