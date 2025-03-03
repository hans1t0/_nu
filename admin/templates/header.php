<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Panel de Administración'; ?> - Sistema Escolar</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Estilos personalizados -->
    <style>
        :root {
            --color-matinera: #198754;
            --color-ludoteca: #0d6efd;
            --color-escuelaVerano: #fd7e14;
            --color-extraescolares: #6f42c1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .bg-purple { 
            background-color: var(--color-extraescolares);
        }
        
        .text-purple {
            color: var(--color-extraescolares);
        }
        
        .btn-outline-purple {
            color: var(--color-extraescolares);
            border-color: var(--color-extraescolares);
        }
        
        .btn-outline-purple:hover {
            color: #fff;
            background-color: var(--color-extraescolares);
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .nav-link {
            font-weight: 500;
        }
        
        .card {
            border-radius: 0.5rem;
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="/admin">
                    <i class="bi bi-mortarboard-fill me-2"></i>
                    Sistema Escolar
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarMain">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="/admin">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="serviciosDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-grid-3x3-gap"></i> Servicios
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="serviciosDropdown">
                                <li>
                                    <a class="dropdown-item" href="/admin/modules/matinera">
                                        <i class="bi bi-sunrise text-success"></i> Guardería Matinal
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/modules/ludoteca">
                                        <i class="bi bi-controller text-primary"></i> Ludoteca
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/modules/escuelaVerano">
                                        <i class="bi bi-sun text-warning"></i> Escuela de Verano
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/admin/modules/extraescolares">
                                        <i class="bi bi-stopwatch text-purple"></i> Act. Extraescolares
                                    </a>
                                </li>
                            </ul>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/test_connections.php">
                                <i class="bi bi-database-check"></i> Probar Conexiones
                            </a>
                        </li>
                    </ul>
                    
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> Admin
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="/admin/profile.php">Perfil</a></li>
                                <li><a class="dropdown-item" href="/admin/settings.php">Configuración</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/admin/logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
