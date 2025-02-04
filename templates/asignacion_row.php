<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">
            <i class="bi bi-building"></i> Colegio:
        </label>
        <select name="colegios[]" class="form-select" required onchange="cargarCursos(this)">
            <option value="">Seleccione colegio</option>
            <?php foreach ($colegios as $col): ?>
                <option value="<?= $col['id'] ?>" 
                    <?= isset($asig) && $asig['id_colegio'] == $col['id'] ? 'selected' : '' ?>>
                    <?= $col['nombre'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="col-md-3">
        <label class="form-label">
            <i class="bi bi-mortarboard-fill"></i> Curso:
        </label>
        <select name="cursos[]" class="form-select" required onchange="actualizarNivelGrado(this)">
            <option value="">Seleccione curso</option>
            <?php if (isset($asig)): ?>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?= $curso['id'] ?>"
                            data-nivel="<?= $curso['nivel'] ?>"
                            data-grado="<?= $curso['grado'] ?>"
                            <?= isset($asig) && $asig['nivel'] == $curso['nivel'] && $asig['grado_minimo'] == $curso['grado'] ? 'selected' : '' ?>>
                        <?= $curso['nombre'] ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    
    <div class="col-md-3">
        <label class="form-label">
            <i class="bi bi-clock"></i> Horario:
        </label>
        <input type="text" name="horarios[]" class="form-control" required
               placeholder="Ej: Lunes y Miércoles 16:00-17:00"
               value="<?= isset($asig) ? $asig['horario'] : '' ?>">
    </div>

    <div class="col-md-2">
        <label class="form-label">
            <i class="bi bi-currency-euro"></i> Precio:
        </label>
        <input type="number" name="precios[]" class="form-control" step="0.01" min="0" required
               value="<?= isset($asig) ? $asig['precio'] : $actividad['precio'] ?>">
    </div>

    <input type="hidden" name="niveles[]" value="<?= isset($asig) ? $asig['nivel'] : '' ?>">
    <input type="hidden" name="grados_min[]" value="<?= isset($asig) ? $asig['grado_minimo'] : '' ?>">
    <input type="hidden" name="grados_max[]" value="<?= isset($asig) ? $asig['grado_maximo'] : '' ?>">
    
    <div class="col-md-1 d-flex align-items-end">
        <div class="btn-group">
            <button type="button" class="btn btn-outline-success btn-sm" onclick="toggleEstado(this)">
                <i class="bi bi-toggle-on"></i>
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="eliminarAsignacion(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>

    <div class="col-12">
        <div class="form-text text-muted">
            <span class="badge bg-secondary">
                <i class="bi bi-people"></i> 
                Cupo: <span class="cupo-actual"><?= isset($asig) ? $asig['cupo_actual'] : '0' ?></span>
                / <?= $actividad['cupo_maximo'] ?>
            </span>
            <span class="badge <?= isset($asig) && $asig['activa'] ? 'bg-success' : 'bg-danger' ?>">
                <?= isset($asig) && $asig['activa'] ? 'Activa' : 'Deshabilitada' ?>
            </span>
        </div>
    </div>
</div>
