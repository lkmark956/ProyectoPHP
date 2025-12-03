    <!-- Footer -->
    <footer class="site-footer">
        <div class="footer-container">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- SCRIPT DE ANIMACIONES: Para eliminar, borra desde aquí hasta el cierre del script -->
    <script>
        // Transiciones de página suaves
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a:not([target="_blank"]):not([href^="#"])');
            const progressBar = document.getElementById('progressBar');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    
                    // Solo para links internos
                    if (href && !href.startsWith('javascript:') && !href.includes('logout')) {
                        e.preventDefault();
                        
                        // Mostrar barra de progreso
                        if (progressBar) {
                            progressBar.style.display = 'block';
                        }
                        
                        // Animar salida
                        document.body.classList.add('page-transition');
                        
                        // Navegar después de la animación
                        setTimeout(() => {
                            window.location.href = href;
                        }, 300);
                    }
                });
            });
            
            // Ocultar barra de progreso cuando carga la página
            window.addEventListener('load', function() {
                if (progressBar) {
                    setTimeout(() => {
                        progressBar.style.display = 'none';
                    }, 500);
                }
            });
        });
    </script>
    <!-- FIN SCRIPT DE ANIMACIONES -->
</body>
</html>
