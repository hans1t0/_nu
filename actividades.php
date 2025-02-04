<?php
include('conexion.php');

// Inicializar variables
$mensaje = '';
$error = '';
$actividad = null;

// Procesar formulario de creación/edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre = trim($_POST['nombre']);
        $nivel = $_POST['nivel'];
        $grado_min = (int)$_POST['grado_minimo'];
        $grado_max = (int)$_POST['grado_maximo'];
        $descripcion = trim($_POST['descripcion']);
        $precio = (float)$_POST['precio'];
        $duracion = trim($_POST['duracion']);
        $cupo_maximo = (int)$_POST['cupo_maximo'];

        // Validaciones
        if (empty($nombre)) throw new Exception('El nombre es requerido');
        if ($grado_min > $grado_max) throw new Exception('El grado mínimo no puede ser mayor al máximo');
        if ($precio <= 0) throw new Exception('El precio debe ser mayor a 0');

        if (isset($_POST['id'])) {
            // Actualizar actividad existente
            $sql = "UPDATE actividades SET 
                    nombre = ?, nivel_requerido = ?, grado_minimo = ?, 
                    grado_maximo = ?, descripcion = ?, precio = ?, 
                    duracion = ?, cupo_maximo = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $nivel, $grado_min, $grado_max, 
                          $descripcion, $precio, $duracion, $cupo_maximo, $_POST['id']]);
            $mensaje = "Actividad actualizada correctamente";
        } else {
            // Crear nueva actividad
            $sql = "INSERT INTO actividades (nombre, nivel_requerido, grado_minimo, 
                    grado_maximo, descripcion, precio, duracion, cupo_maximo) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([$nombre, $nivel, $grado_min, $grado_max, 
                          $descripcion, $precio, $duracion, $cupo_maximo]);
            $mensaje = "Actividad creada correctamente";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Procesar asignación a colegio
if (isset($_POST['asignar_colegio'])) {
    try {
        $stmt = $conexion->prepare("
            INSERT INTO colegio_actividad (
                id_colegio, id_actividad, nivel, grado_minimo, 
                grado_maximo, horario, cupo_maximo, precio
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $_POST['colegio_id'],
            $_POST['actividad_id'],
            $_POST['nivel'],
            $_POST['grado_minimo'],
            $_POST['grado_maximo'],
            $_POST['horario'],
            $_POST['cupo'],
            $_POST['precio']
        ]);
        
        $mensaje = "Actividad asignada al colegio correctamente";
    } catch (Exception $e) {
        $error = "Error al asignar la actividad: " . $e->getMessage();
    }
}

// Eliminar actividad
if (isset($_GET['eliminar'])) {
    try {
        $stmt = $conexion->prepare("DELETE FROM actividades WHERE id = ?");
        $stmt->execute([$_GET['eliminar']]);
        $mensaje = "Actividad eliminada correctamente";
    } catch (Exception $e) {
        $error = "No se pudo eliminar la actividad";
    }
}

// Cargar actividad para editar
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM actividades WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $actividad = $stmt->fetch();

    // Obtener asignaciones existentes
    if ($actividad) {
        $stmt = $conexion->prepare("
            SELECT ca.*, c.nombre as colegio_nombre, 
                   (SELECT COUNT(*) FROM inscripciones_actividad 
                    WHERE id_colegio = ca.id_colegio 
                    AND id_actividad = ca.id_actividad) as inscritos
            FROM colegio_actividad ca 
            JOIN colegios c ON ca.id_colegio = c.id
            WHERE ca.id_actividad = ?
            ORDER BY c.nombre, ca.horario
        ");
        $stmt->execute([$_GET['editar']]);
        $asignaciones = $stmt->fetchAll();
    }
}

// Obtener todas las actividades
$actividades = $conexion->query("
    SELECT * FROM actividades 
    ORDER BY nivel_requerido, nombre")->fetchAll();

// Obtener colegios para el formulario
$colegios = $conexion->query("SELECT id, nombre FROM colegios ORDER BY nombre")->fetchAll();

// Agregar después de obtener colegios
$cursos = $conexion->query("SELECT id, nombre, nivel, grado FROM cursos ORDER BY nivel, grado")->fetchAll();

// Agregar manejo de asignaciones múltiples
if (isset($_POST['actualizar_asignaciones']) && isset($_POST['id'])) {
    try {
        $conexion->beginTransaction();
        
        // Eliminar asignaciones existentes
        $stmt = $conexion->prepare("DELETE FROM colegio_actividad WHERE id_actividad = ?");
        $stmt->execute([$_POST['id']]);
        
        // Insertar nuevas asignaciones
        $stmt = $conexion->prepare("
            INSERT INTO colegio_actividad (
                id_colegio, id_actividad, nivel, grado_minimo, 
                grado_maximo, horario, precio
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($_POST['colegios'] as $i => $colegio_id) {
            if (!empty($colegio_id) && !empty($_POST['horarios'][$i])) {
                $stmt->execute([
                    $colegio_id,
                    $_POST['id'],
                    $_POST['niveles'][$i],
                    $_POST['grados_min'][$i],
                    $_POST['grados_max'][$i],
                    $_POST['horarios'][$i],
                    $_POST['precios'][$i]
                ]);
            }
        }
        
        $conexion->commit();
        $mensaje = "Asignaciones actualizadas correctamente";
    } catch (Exception $e) {
        $conexion->rollBack();
        $error = "Error al actualizar asignaciones: " . $e->getMessage();
    }
}

// Agregar endpoint para toggle estado
if (isset($_POST['toggle_estado'])) {
    try {
        $stmt = $conexion->prepare("
            UPDATE colegio_actividad 
            SET activa = NOT activa 
            WHERE id_colegio = ? AND id_actividad = ? AND horario = ?
        ");
        $stmt->execute([
            $_POST['colegio_id'],
            $_POST['actividad_id'],
            $_POST['horario']
        ]);
        echo json_encode(['success' => true]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Actividades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Agregar menú de navegación -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-calendar-check"></i> 
                Gestión de Actividades
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="actividades.php">
                            <i class="bi bi-list-check"></i> Actividades
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="asignaciones.php">
                            <i class="bi bi-building"></i> Asignaciones
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row">
            <!-- Formulario -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="bi bi-award"></i>
                            <?= $actividad ? 'Editar' : 'Nueva' ?> Actividad
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-success"><?= $mensaje ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <?php if ($actividad): ?>
                                <input type="hidden" name="id" value="<?= $actividad['id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Nombre:</label>
                                <input type="text" name="nombre" class="form-control" required
                                    value="<?= $actividad ? $actividad['nombre'] : '' ?>">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-mortarboard-fill"></i> 
                                        Curso:
                                    </label>
                                    <select name="curso" class="form-select" required onchange="actualizarNivelGrado(this)">
                                        <option value="">Seleccione curso</option>
                                        <?php foreach ($cursos as $curso): ?>
                                            <option value="<?= $curso['id'] ?>"
                                                    data-nivel="<?= $curso['nivel'] ?>"
                                                    data-grado="<?= $curso['grado'] ?>"
                                                    <?= ($actividad && $actividad['nivel_requerido'] == $curso['nivel'] && 
                                                        $actividad['grado_minimo'] == $curso['grado']) ? 'selected' : '' ?>>
                                                <?= $curso['nombre'] ?> (<?= $curso['nivel'] ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-building"></i>
                                        Colegio:
                                    </label>
                                    <select name="colegio" class="form-select" required>
                                        <option value="">Seleccione colegio</option>
                                        <?php foreach ($colegios as $colegio): ?>
                                            <option value="<?= $colegio['id'] ?>">
                                                <?= $colegio['nombre'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="nivel" id="nivel_hidden">
                            <input type="hidden" name="grado_minimo" id="grado_min_hidden">
                            <input type="hidden" name="grado_maximo" id="grado_max_hidden">

                            <div class="mb-3">
                                <label class="form-label">Descripción:</label>
                                <textarea name="descripcion" class="form-control" rows="3"><?= $actividad ? $actividad['descripcion'] : '' ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Precio (€):</label>
                                <input type="number" name="precio" class="form-control" step="0.01" min="0" required
                                    value="<?= $actividad ? $actividad['precio'] : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Duración:</label>
                                <input type="text" name="duracion" class="form-control" required
                                    value="<?= $actividad ? $actividad['duracion'] : '' ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Cupo máximo:</label>
                                <input type="number" name="cupo_maximo" class="form-control" 
                                       min="1" required value="<?= $actividad ? $actividad['cupo_maximo'] : '20' ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <?= $actividad ? 'Actualizar' : 'Crear' ?> Actividad
                                </button>
                                <?php if ($actividad): ?>
                                    <a href="actividades.php" class="btn btn-outline-secondary">Cancelar</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lista de actividades -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>
                            <i class="bi bi-list-check"></i>
                            Actividades Registradas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Nivel</th>
                                        <th>Grados</th>
                                        <th>Precio</th>
                                        <th>Duración</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($actividades as $act): ?>
                                        <tr>
                                            <td>
                                                <?= htmlspecialchars($act['nombre']) ?>
                                                <?php if ($act['descripcion']): ?>
                                                    <i class="bi bi-info-circle text-primary" 
                                                       data-bs-toggle="tooltip" 
                                                       title="<?= htmlspecialchars($act['descripcion']) ?>">
                                                    </i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= $act['nivel_requerido'] ?></td>
                                            <td><?= $act['grado_minimo'] ?>° a <?= $act['grado_maximo'] ?>°</td>
                                            <td><?= number_format($act['precio'], 2) ?>€</td>
                                            <td><?= $act['duracion'] ?></td>
                                            <td>
                                                <a href="?editar=<?= $act['id'] ?>" 
                                                   class="btn btn-sm btn-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="Editar">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="#" 
                                                   onclick="confirmarEliminacion(<?= $act['id'] ?>)"
                                                   class="btn btn-sm btn-danger"
                                                   data-bs-toggle="tooltip"
                                                   title="Eliminar">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                                <a href="#" 
                                                   onclick="asignarAColegio(<?= $act['id'] ?>, <?= $act['precio'] ?>)"
                                                   class="btn btn-sm btn-success"
                                                   data-bs-toggle="tooltip"
                                                   title="Asignar a Colegio">
                                                    <i class="bi bi-building"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAsignarColegio" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Actividad a Colegio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="modal-body">
                        <input type="hidden" name="asignar_colegio" value="1">
                        <input type="hidden" name="actividad_id" id="actividad_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Colegio:</label>
                            <select name="colegio_id" class="form-select" required>
                                <option value="">Seleccione colegio</option>
                                <?php foreach ($colegios as $colegio): ?>
                                    <option value="<?= $colegio['id'] ?>"><?= $colegio['nombre'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nivel:</label>
                            <select name="nivel" class="form-select" required>
                                <option value="Infantil">Infantil</option>
                                <option value="Primaria">Primaria</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label">Grado mínimo:</label>
                                <input type="number" name="grado_minimo" class="form-control" 
                                       min="1" max="6" required value="1">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Grado máximo:</label>
                                <input type="number" name="grado_maximo" class="form-control" 
                                       min="1" max="6" required value="6">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Horario:</label>
                            <input type="text" name="horario" class="form-control" required
                                   placeholder="Ej: Lunes y Miércoles 16:00-17:00">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cupo actual / máximo:</label>
                            <input type="text" class="form-control" disabled
                                   value="0 / <?= $actividad['cupo_maximo'] ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio (€):</label>
                            <input type="number" name="precio" class="form-control" 
                                   step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Inicializar tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el))

        // Confirmar eliminación
        function confirmarEliminacion(id) {
            Swal.fire({
                title: '¿Eliminar actividad?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?eliminar=${id}`;
                }
            })
        }

        // Asignar a colegio
        function asignarAColegio(id, precio) {
            document.getElementById('actividad_id').value = id;
            document.querySelector('input[name="precio"]').value = precio;
            new bootstrap.Modal(document.getElementById('modalAsignarColegio')).show();
        }

        // Validación de formulario
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        });

        function agregarAsignacion() {
            const container = document.getElementById('asignaciones-container');
            const template = document.getElementById('asignacion-template');
            const clone = template.content.cloneNode(true);
            
            // Actualizar índices
            const index = container.children.length;
            clone.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace('[]', `[${index}]`);
            });
            
            const row = document.createElement('div');
            row.className = 'asignacion-row border-bottom pb-3 mb-3';
            row.appendChild(clone);
            container.appendChild(row);
        }

        function eliminarAsignacion(btn) {
            const row = btn.closest('.asignacion-row');
            row.remove();
        }

        function cargarCursos(select) {
            const row = select.closest('.row');
            const cursosSelect = row.querySelector('select[name="cursos[]"]');
            const nivel = row.querySelector('input[name="niveles[]"]');
            const gradoMin = row.querySelector('input[name="grados_min[]"]');
            const gradoMax = row.querySelector('input[name="grados_max[]"]');
            
            // Limpiar valores anteriores
            nivel.value = '';
            gradoMin.value = '';
            gradoMax.value = '';
            
            // Resetear select de cursos
            cursosSelect.innerHTML = '<option value="">Seleccione curso</option>';
            
            if (select.value) {
                const cursos = <?= json_encode($cursos) ?>;
                cursos.forEach(curso => {
                    const option = new Option(curso.nombre, curso.id);
                    option.dataset.nivel = curso.nivel;
                    option.dataset.grado = curso.grado;
                    cursosSelect.add(option);
                });
            }
        }

        function actualizarNivelGrado(select) {
            const row = select.closest('.row');
            const selectedOption = select.options[select.selectedIndex];
            
            if (selectedOption.value) {
                row.querySelector('input[name="niveles[]"]').value = selectedOption.dataset.nivel;
                row.querySelector('input[name="grados_min[]"]').value = selectedOption.dataset.grado;
                row.querySelector('input[name="grados_max[]"]').value = selectedOption.dataset.grado;
            }
        }

        function toggleEstado(btn) {
            const row = btn.closest('.row');
            const colegioId = row.querySelector('select[name="colegios[]"]').value;
            const horario = row.querySelector('input[name="horarios[]"]').value;
            const actividadId = document.querySelector('input[name="id"]').value;
            
            fetch('actividades.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `toggle_estado=1&colegio_id=${colegioId}&actividad_id=${actividadId}&horario=${encodeURIComponent(horario)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = row.querySelector('.badge:last-child');
                    const icon = btn.querySelector('i');
                    if (badge.classList.contains('bg-success')) {
                        badge.classList.replace('bg-success', 'bg-danger');
                        badge.textContent = 'Deshabilitada';
                        icon.classList.replace('bi-toggle-on', 'bi-toggle-off');
                    } else {
                        badge.classList.replace('bg-danger', 'bg-success');
                        badge.textContent = 'Activa';
                        icon.classList.replace('bi-toggle-off', 'bi-toggle-on');
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function actualizarNivelGrado(select) {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption.value) {
                document.getElementById('nivel_hidden').value = selectedOption.dataset.nivel;
                document.getElementById('grado_min_hidden').value = selectedOption.dataset.grado;
                document.getElementById('grado_max_hidden').value = selectedOption.dataset.grado;
            }
        }

        // Inicializar valores si hay curso seleccionado
        document.addEventListener('DOMContentLoaded', function() {
            const cursoSelect = document.querySelector('select[name="curso"]');
            if (cursoSelect) {
                actualizarNivelGrado(cursoSelect);
            }
        });
    </script>
</body>
</html>
