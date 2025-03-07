<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titulo_pagina ?? 'Panel de Administración' ?></title>
    
    <!-- Estilos comunes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
            --primary-color: #2563eb;
        }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #f9fafb;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: #fff;
            border-right: 1px solid #e5e7eb;
            z-index: 1000;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
        }
        
        .topbar {
            height: var(--topbar-height);
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .nav-link {
            color: #4b5563;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
        }
        
        .nav-link:hover {
            background-color: #f3f4f6;
            color: var(--primary-color);
        }
        
        .nav-link.active {
            background-color: #e5e7eb;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .nav-link i {
            width: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar">
    <div class="d-flex flex-column h-100">
        <div class="p-3 border-bottom">
            <h5 class="mb-0">NU3 Admin</h5>
        </div>
        
        <div class="p-3">
            <div class="nav flex-column">
                <a href="/admin/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'index.php') !== false ? 'active' : '' ?>">
                    <i class="bi bi-house-door"></i> Dashboard
                </a>
                <a href="/admin/modules/extraescolares/index.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'extraescolares') !== false ? 'active' : '' ?>">
                    <i class="bi bi-calendar-check"></i> Extraescolares
                </a>
                <a href="/admin/modules/extraescolares/colegios.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'colegios.php') !== false ? 'active' : '' ?>">
                    <i class="bi bi-building"></i> Colegios
                </a>
                <a href="/admin/modules/extraescolares/actividades.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'actividades.php') !== false ? 'active' : '' ?>">
                    <i class="bi bi-list-task"></i> Actividades
                </a>
                <a href="/admin/modules/extraescolares/reportes.php" class="nav-link <?= strpos($_SERVER['PHP_SELF'], 'reportes.php') !== false ? 'active' : '' ?>">
                    <i class="bi bi-graph-up"></i> Reportes
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="main-content">
    <div class="topbar px-4 d-flex align-items-center justify-content-between">
        <button class="btn d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-link text-dark dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="/admin/perfil.php">Mi Perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/admin/logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
