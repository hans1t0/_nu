<style>
    /* Estilos base del menú */
    .navbar {
        padding: 1rem 0;
        background: rgba(255, 255, 255, 0.98) !important;
        backdrop-filter: blur(10px);
    }

    /* Efectos de navegación unificados */
    .navbar-nav .nav-link,
    .dropdown-item {
        position: relative;
        transition: all 0.25s ease;
        padding: 0.75rem 1.25rem;
        color: var(--bs-gray-700);
    }

    /* Hover sutil para todos los elementos */
    .navbar-nav .nav-link:hover,
    .dropdown-item:hover {
        color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.05);
        transform: translateY(-1px);
    }

    /* Elemento activo */
    .navbar-nav .nav-link.active {
        color: var(--bs-primary);
        font-weight: 500;
    }

    /* Dropdown menus */
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
        border-radius: 0.75rem;
        padding: 0.5rem;
        margin-top: 0.5rem;
        transition: all 0.25s ease;
    }

    .dropdown-item {
        border-radius: 0.5rem;
        margin: 0.1rem 0;
    }

    /* Desktop específico */
    @media (min-width: 992px) {
        .dropdown-menu {
            display: block;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
        }

        .dropdown:hover > .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-toggle::after {
            transition: transform 0.25s ease;
        }

        .dropdown:hover .dropdown-toggle::after {
            transform: rotate(180deg);
        }
    }

    /* Móvil específico */
    @media (max-width: 991.98px) {
        .navbar-collapse {
            background: white;
            padding: 1rem;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.1);
            margin-top: 1rem;
        }

        .dropdown-menu {
            background: rgba(var(--bs-primary-rgb), 0.03);
            margin: 0.5rem 0;
        }

        .dropdown-item {
            padding-left: 2.5rem;
        }

        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 1.25rem;
            top: 50%;
            width: 0.5rem;
            height: 1px;
            background: currentColor;
            opacity: 0.25;
        }
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo IMAGES_URL; ?>logo_ev.png" alt="Educap" height="40">
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $pagina === 'inicio' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>">
                       <i class="bi bi-house-fill me-1"></i>Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pagina === 'matinera' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>matinera.php">
                       <i class="bi bi-sunrise-fill me-1"></i>Matinera
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pagina === 'ludoteca' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>ludoteca.php">
                       <i class="bi bi-backpack me-1"></i>Ludoteca
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pagina === 'verano' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>verano.php">
                       <i class="bi bi-sun-fill me-1"></i>Verano
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pagina === 'extraescolares' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>extraescolares.php">
                       <i class="bi bi-journal-check me-1"></i>Extraescolares
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $pagina === 'contacto' ? 'active' : ''; ?>" 
                       href="<?php echo BASE_URL; ?>contacto.php">
                       <i class="bi bi-telephone-fill me-1"></i>Contacto
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar todos los dropdowns de Bootstrap
    var dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    var dropdownList = [...dropdownElementList].map(dropdownToggleEl => new bootstrap.Dropdown(dropdownToggleEl));

    const isMobile = () => window.innerWidth < 992;
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const bsCollapse = new bootstrap.Collapse(navbarCollapse, { toggle: false });

    // Gestión del botón toggle
    document.querySelector('.navbar-toggler').addEventListener('click', function() {
        bsCollapse.toggle();
    });

    // Gestión de dropdowns en móvil
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        toggle.addEventListener('click', function(e) {
            if (isMobile()) {
                e.stopPropagation(); // Prevenir cierre automático
                
                // Cerrar otros dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                    if (otherMenu !== menu && otherMenu.classList.contains('show')) {
                        otherMenu.classList.remove('show');
                    }
                });

                // Toggle del menú actual
                menu.classList.toggle('show');
            }
        });
    });

    // Cerrar menú al seleccionar una opción
    document.querySelectorAll('.dropdown-menu a').forEach(item => {
        item.addEventListener('click', () => {
            if (isMobile() && navbarCollapse.classList.contains('show')) {
                bsCollapse.hide();
            }
        });
    });

    // Cerrar dropdowns al hacer click fuera
    document.addEventListener('click', function(e) {
        if (isMobile() && !e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });

    // Limpiar estados al cambiar tamaño de ventana
    window.addEventListener('resize', function() {
        if (!isMobile()) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
            if (navbarCollapse.classList.contains('show')) {
                bsCollapse.hide();
            }
        }
    });

    // Mejorar manejo de dropdowns en táctil
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');

        // Manejar eventos táctiles
        toggle.addEventListener('touchstart', function(e) {
            if (isMobile()) {
                e.preventDefault();
                e.stopPropagation();

                // Cerrar otros dropdowns
                document.querySelectorAll('.dropdown-menu.show').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                        otherMenu.previousElementSibling.setAttribute('aria-expanded', 'false');
                    }
                });

                // Toggle actual dropdown
                const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', !isExpanded);
                menu.classList.toggle('show');
            }
        });

        // Evitar que los clicks en el menú lo cierren
        menu.addEventListener('click', e => e.stopPropagation());
    });

    // Cerrar menú al seleccionar opción
    document.querySelectorAll('.navbar-nav a:not(.dropdown-toggle)').forEach(link => {
        link.addEventListener('click', () => {
            if (isMobile() && navbarCollapse.classList.contains('show')) {
                bsCollapse.hide();
            }
        });
    });

    // Cerrar al tocar fuera
    document.addEventListener('touchstart', function(e) {
        if (isMobile() && !e.target.closest('.navbar-collapse')) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
                menu.previousElementSibling.setAttribute('aria-expanded', 'false');
            });
            if (navbarCollapse.classList.contains('show')) {
                bsCollapse.hide();
            }
        }
    });

    // Manejar cambios de orientación
    window.addEventListener('orientationchange', function() {
        if (isMobile()) {
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
            if (navbarCollapse.classList.contains('show')) {
                bsCollapse.hide();
            }
        }
    });
});
</script>
