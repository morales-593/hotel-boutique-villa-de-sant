    </div>

    <script>
        const sidebar = document.getElementById('adminSidebar');
        const toggle = document.getElementById('adminMobileToggle');

        if (toggle && sidebar) {
            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                sidebar.classList.toggle('active');
            });

            // Close when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== toggle) {
                    sidebar.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>
