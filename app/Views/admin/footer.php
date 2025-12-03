        </main>
    </div>

    <footer class="admin-footer">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> - Panel de administracion</p>
    </footer>

    <!-- Scripts de transición de página -->
    <script>
        // Mostrar barra de progreso
        const progressBar = document.getElementById('progressBar');
        
        // Capturar clics en enlaces
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (link && link.href && !link.target && link.href.indexOf(window.location.origin) === 0) {
                if (link.href !== window.location.href && !link.getAttribute('data-no-transition')) {
                    e.preventDefault();
                    document.body.classList.add('fade-out');
                    if (progressBar) {
                        progressBar.style.display = 'block';
                    }
                    setTimeout(() => {
                        window.location.href = link.href;
                    }, 300);
                }
            }
        });

        // Ocultar barra de progreso al cargar
        window.addEventListener('load', function() {
            if (progressBar) {
                progressBar.style.display = 'none';
            }
        });
    </script>
</body>
</html>
