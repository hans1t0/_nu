            </main>
        </div>
    </div>

    <!-- jQuery, Popper.js, y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <!-- Scripts adicionales que pueden ser incluidos por página -->
    <?php if (isset($customScripts)): ?>
        <?php echo $customScripts; ?>
    <?php endif; ?>

    <!-- Scripts generales -->
    <script>
        // Inicialización de tooltips de Bootstrap
        $(function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        // Función para confirmar acciones de eliminación
        function confirmarEliminar(mensaje) {
            return confirm(mensaje || "¿Está seguro de que desea eliminar este registro?");
        }
    </script>
</body>
</html>
