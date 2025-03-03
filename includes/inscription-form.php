<form action="procesar_inscripcion.php" method="POST" class="needs-validation" novalidate>
    <div class="row g-3">
        <!-- Datos del Alumno -->
        <div class="col-12">
            <h5 class="mb-3">Datos del Alumno</h5>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Apellidos</label>
            <input type="text" class="form-control" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Fecha de Nacimiento</label>
            <input type="date" class="form-control" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Curso</label>
            <select class="form-select" required>
                <option value="">Seleccionar...</option>
                <option>Infantil 3 años</option>
                <option>Infantil 4 años</option>
                <option>Infantil 5 años</option>
                <option>1º Primaria</option>
                <option>2º Primaria</option>
                <option>3º Primaria</option>
                <option>4º Primaria</option>
                <option>5º Primaria</option>
                <option>6º Primaria</option>
            </select>
        </div>

        <!-- Datos de Contacto -->
        <div class="col-12">
            <h5 class="mb-3 mt-4">Datos de Contacto</h5>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" class="form-control" required>
        </div>
        
        <div class="col-md-6">
            <label class="form-label">Teléfono</label>
            <input type="tel" class="form-control" required>
        </div>

        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" required>
                <label class="form-check-label">
                    Acepto la política de privacidad y el tratamiento de datos
                </label>
            </div>
        </div>

        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary rounded-pill px-4">
                Enviar Inscripción
                <i class="bi bi-arrow-right-circle ms-2"></i>
            </button>
        </div>
    </div>
</form>

<script>
// Validación del formulario
(function() {
    'use strict';
    var forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
