</main>
    <footer class="bg-light py-3 mt-5">
        <div class="container text-center">
            <p class="mb-1">&copy; <?= date('Y') ?> Sistema de Gestión Escolar</p>
            <p class="small text-muted mb-0">Desarrollado para Centros Educativos</p>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle con Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Script personalizado -->
    <script>
        // Destacar el enlace de navegación activo
        document.addEventListener('DOMContentLoaded', () => {
            const currentLocation = window.location.pathname;
            const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentLocation.includes(href) && href !== '/admin') {
                    link.classList.add('active');
                } else if (href === '/admin' && (currentLocation === '/admin/' || currentLocation === '/admin/index.php')) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>
