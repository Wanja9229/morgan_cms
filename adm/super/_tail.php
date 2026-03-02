            </div><!-- // sa-content -->

            <footer style="padding:1rem 1.5rem; text-align:center; font-size:0.75rem; color:var(--mg-text-muted); border-top:1px solid var(--mg-bg-tertiary);">
                Morgan Super Admin &copy; <?php echo date('Y'); ?>
            </footer>
        </main>
    </div>

    <script>
    function toggleSidebar() {
        var sidebar = document.getElementById('saSidebar');
        var overlay = document.querySelector('.sa-sidebar-overlay');
        if (sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        } else {
            sidebar.classList.add('show');
            if (overlay) overlay.classList.add('show');
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var sidebar = document.getElementById('saSidebar');
            var overlay = document.querySelector('.sa-sidebar-overlay');
            sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
        }
    });
    </script>
</body>
</html>
