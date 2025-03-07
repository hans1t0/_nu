<div class="row">
    <!-- Actividades Recientes -->
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Actividades Recientes</h6>
                <a href="actividades/index.php" class="btn btn-sm btn-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Actividad</th>
                                <th>Colegio</th>
                                <th>Cupos</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $actividad): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($actividad['actividad']); ?></td>
                                <td><?php echo htmlspecialchars($actividad['colegio']); ?></td>
                                <td><?php echo $actividad['cupo_actual'].'/'.$actividad['cupo_maximo']; ?></td>
                                <td><?php echo number_format($actividad['precio_actual'], 2).'€'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Inscripciones Recientes -->
    <div class="col-12 col-xl-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Inscripciones Recientes</h6>
                <a href="inscripciones/index.php" class="btn btn-sm btn-primary">Ver todas</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Alumno</th>
                                <th>Actividad</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscripciones as $inscripcion): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($inscripcion['hijo_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($inscripcion['nombre_actividad']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $inscripcion['estado'] === 'confirmada' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($inscripcion['estado']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($inscripcion['fecha_inscripcion'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
