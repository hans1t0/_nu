<?php
session_start();
require_once '../config.php';
// checkAdminAccess();

// Configurar filtros
$filtros = [
    'centro' => $_GET['centro'] ?? '',
    'curso' => $_GET['curso'] ?? '',
    'semana' => $_GET['semana'] ?? '',
    'comedor' => $_GET['comedor'] ?? '',
    'guarderia' => $_GET['guarderia'] ?? ''
];

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Modificar consulta para obtener resultados agrupados por centro
    $sql = "
        SELECT 
            p.*,
            r.nombre as responsable_nombre,
            r.dni as responsable_dni,
            r.email as responsable_email,
            r.telefono as responsable_telefono,
            r.forma_pago,
            GROUP_CONCAT(DISTINCT pi.semana) as semanas,
            MAX(sc.socio_ampa) as socio_ampa,
            MAX(sc.guarderia_matinal) as guarderia_matinal,
            MAX(sc.comedor) as comedor
        FROM participantes p
        LEFT JOIN responsables r ON p.responsable_id = r.id
        LEFT JOIN periodos_inscritos pi ON p.id = pi.participante_id
        LEFT JOIN servicios_contratados sc ON p.id = sc.participante_id
        WHERE 1=1
    ";

    $params = [];
    
    if ($filtros['centro']) {
        $sql .= " AND p.centro_actual = :centro";
        $params['centro'] = $filtros['centro'];
    }
    if ($filtros['curso']) {
        $sql .= " AND p.curso = :curso";
        $params['curso'] = $filtros['curso'];
    }
    if ($filtros['semana']) {
        $sql .= " AND pi.semana = :semana";
        $params['semana'] = $filtros['semana'];
    }
    if ($filtros['comedor']) {
        $sql .= " AND sc.comedor = :comedor";
        $params['comedor'] = $filtros['comedor'];
    }
    if ($filtros['guarderia']) {
        $sql .= " AND sc.guarderia_matinal IS NOT NULL";
    }

    $sql .= " GROUP BY p.id, p.nombre, p.fecha_nacimiento, p.centro_actual, 
                       p.curso, p.alergias, p.responsable_id, p.created_at,
                       r.nombre, r.dni, r.email, r.telefono, r.forma_pago
              ORDER BY p.nombre";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $inscritos = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Inscritos - <?= APP_NAME ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- DataTables Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="bg-light">
    <main class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Listado de Inscritos</h1>
        </div>

        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <!-- Eliminar filtro de centro ya que usaremos pestañas -->
                    <div class="col-md-2">
                        <label class="form-label">Semana</label>
                        <select name="semana" class="form-select">
                            <option value="">Todas</option>
                            <?php foreach (PERIODOS as $codigo => $periodo): ?>
                            <option value="<?= $codigo ?>" <?= $filtros['semana'] === $codigo ? 'selected' : '' ?>>
                                <?= $periodo['nombre'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Comedor</label>
                        <select name="comedor" class="form-select">
                            <option value="">Todos</option>
                            <option value="SI" <?= $filtros['comedor'] === 'SI' ? 'selected' : '' ?>>Con comedor</option>
                            <option value="NO" <?= $filtros['comedor'] === 'NO' ? 'selected' : '' ?>>Sin comedor</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Guardería</label>
                        <select name="guarderia" class="form-select">
                            <option value="">Todos</option>
                            <option value="1" <?= $filtros['guarderia'] === '1' ? 'selected' : '' ?>>Con guardería</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sistema de pestañas -->
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="centrosTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#todos" type="button">
                            Todos
                        </button>
                    </li>
                    <?php foreach (CENTROS as $codigo => $nombre): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#centro<?= $codigo ?>" type="button">
                            <?= $nombre ?>
                        </button>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content" id="centrosContent">
                    <!-- Pestaña "Todos" -->
                    <div class="tab-pane fade show active" id="todos">
                        <div class="table-responsive pt-3">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Curso</th>
                                        <th>Centro</th>
                                        <th>Semanas</th>
                                        <th>Servicios</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscritos as $inscrito): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($inscrito['nombre']) ?></td>
                                        <td><?= htmlspecialchars($inscrito['curso']) ?></td>
                                        <td><?= getNombreCentro($inscrito['centro_actual']) ?></td>
                                        <td>
                                            <?php 
                                            $semanas = explode(',', $inscrito['semanas']);
                                            foreach ($semanas as $semana): 
                                                echo '<span class="badge bg-primary me-1">' . getNombrePeriodoCorto($semana) . '</span>';
                                            endforeach; 
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($inscrito['guarderia_matinal']): ?>
                                                <span class="badge bg-info">Guardería <?= $inscrito['guarderia_matinal'] ?></span>
                                            <?php endif; ?>
                                            <?php if ($inscrito['comedor'] === 'SI'): ?>
                                                <span class="badge bg-warning">Comedor</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?= htmlspecialchars($inscrito['responsable_nombre']) ?><br>
                                                <i class="bi bi-telephone"></i> <?= htmlspecialchars($inscrito['responsable_telefono']) ?><br>
                                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($inscrito['responsable_email']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    onclick="verDetalles(<?= $inscrito['id'] ?>)">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pestañas por centro -->
                    <?php foreach (CENTROS as $codigo => $nombre): ?>
                    <div class="tab-pane fade" id="centro<?= $codigo ?>">
                        <div class="table-responsive pt-3">
                            <table class="table table-hover datatable">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Curso</th>
                                        <th>Centro</th>
                                        <th>Semanas</th>
                                        <th>Servicios</th>
                                        <th>Contacto</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($inscritos as $inscrito): ?>
                                        <?php if ($inscrito['centro_actual'] === $codigo): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($inscrito['nombre']) ?></td>
                                            <td><?= htmlspecialchars($inscrito['curso']) ?></td>
                                            <td><?= getNombreCentro($inscrito['centro_actual']) ?></td>
                                            <td>
                                                <?php 
                                                $semanas = explode(',', $inscrito['semanas']);
                                                foreach ($semanas as $semana): 
                                                    echo '<span class="badge bg-primary me-1">' . getNombrePeriodoCorto($semana) . '</span>';
                                                endforeach; 
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($inscrito['guarderia_matinal']): ?>
                                                    <span class="badge bg-info">Guardería <?= $inscrito['guarderia_matinal'] ?></span>
                                                <?php endif; ?>
                                                <?php if ($inscrito['comedor'] === 'SI'): ?>
                                                    <span class="badge bg-warning">Comedor</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <small>
                                                    <?= htmlspecialchars($inscrito['responsable_nombre']) ?><br>
                                                    <i class="bi bi-telephone"></i> <?= htmlspecialchars($inscrito['responsable_telefono']) ?><br>
                                                    <i class="bi bi-envelope"></i> <?= htmlspecialchars($inscrito['responsable_email']) ?>
                                                </small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        onclick="verDetalles(<?= $inscrito['id'] ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Scripts al final del body -->
    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datatable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 25,
                order: [[0, 'asc']],
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="bi bi-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5],
                            format: {
                                body: function(data, row, column, node) {
                                    // Para la columna de servicios (índice 4)
                                    if (column === 4) {
                                        // Extraer texto de los badges
                                        let servicios = [];
                                        $(node).find('.badge').each(function() {
                                            servicios.push($(this).text().trim());
                                        });
                                        return servicios.join(', ');
                                    }
                                    // Para otras columnas, eliminar HTML
                                    return data.replace(/<[^>]+>/g, ' ').trim();
                                }
                            }
                        }
                    }
                ]
            });
        });

        function verDetalles(id) {
            window.location.href = `detalle.php?id=${id}`;
        }
    </script>
</body>
</html>
