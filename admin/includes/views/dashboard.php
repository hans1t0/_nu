<div class="container-fluid py-4">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?php echo $this->pageTitle; ?></h1>
            <p class="text-muted">Panel de gestión</p>
        </div>
        <a href="../index.php" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
        <?php foreach ($this->stats as $key => $stat): ?>
        <div class="col-xl-3 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="h4 mb-0"><?php echo $stat['total']; ?></div>
                            <div class="text-muted"><?php echo ucfirst($key); ?></div>
                            <?php if (isset($stat['activas'])): ?>
                            <small class="text-success">
                                <i class="fas fa-check-circle"></i>
                                <?php echo $stat['activas']; ?> activas
                            </small>
                            <?php endif; ?>
                        </div>
                        <div class="bg-<?php echo $stat['color']; ?> bg-opacity-10 p-3 rounded">
                            <i class="fas <?php echo $stat['icon']; ?> text-<?php echo $stat['color']; ?>"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Acciones Rápidas -->
    <?php if (method_exists($this, 'getQuickActions')): ?>
    <div class="row g-3 mb-4">
        <div class="col-12">
            <h6 class="fw-bold mb-3">ACCIONES RÁPIDAS</h6>
            <div class="row g-3">
                <?php foreach ($this->getQuickActions() as $action): ?>
                <div class="col-xl-3 col-md-6">
                    <a href="<?php echo $action['url']; ?>" class="card h-100 hover-shadow text-decoration-none">
                        <div class="card-body text-center">
                            <div class="bg-<?php echo $action['color']; ?> bg-opacity-10 rounded-circle p-3 mx-auto mb-3" style="width: fit-content;">
                                <i class="fas <?php echo $action['icon']; ?> text-<?php echo $action['color']; ?>"></i>
                            </div>
                            <h6 class="mb-1"><?php echo $action['title']; ?></h6>
                            <p class="text-muted small mb-0"><?php echo $action['description']; ?></p>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contenido específico del módulo -->
    <?php if (method_exists($this, 'renderModuleContent')): ?>
        <?php $this->renderModuleContent(); ?>
    <?php endif; ?>
</div>
