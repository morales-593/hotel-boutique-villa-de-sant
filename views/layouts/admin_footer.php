        </div> <!-- /.admin-view-container -->
    </div> <!-- /.main-content -->

    <script>
        const sidebar = document.getElementById('adminSidebar');
        const toggle = document.getElementById('adminMobileToggle');
        const overlay = document.getElementById('adminSidebarOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        if (toggle && sidebar && overlay) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleSidebar();
            });

            overlay.addEventListener('click', () => {
                if (sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });

            // Close with escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                    toggleSidebar();
                }
            });
        }
    </script>
</body>
</html>
