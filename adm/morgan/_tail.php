            </div><!-- // mg-admin-content -->

            <!-- Footer -->
            <footer style="padding:1rem 1.5rem; text-align:center; font-size:0.75rem; color:var(--mg-text-muted); border-top:1px solid var(--mg-bg-tertiary);">
                Morgan Edition Admin &copy; <?php echo date('Y'); ?>
            </footer>
        </main>
    </div>

    <script>
    function toggleSidebar() {
        var sidebar = document.getElementById('adminSidebar');
        var overlay = document.querySelector('.mg-sidebar-overlay');

        if (sidebar.classList.contains('show')) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        } else {
            sidebar.classList.add('show');
            overlay.classList.add('show');
        }
    }

    // ESC 키로 사이드바 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var sidebar = document.getElementById('adminSidebar');
            var overlay = document.querySelector('.mg-sidebar-overlay');
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
        }
    });
    </script>
</body>
</html>
