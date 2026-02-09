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

    function toggleNavSection(titleEl) {
        var section = titleEl.parentElement;
        var items = section.querySelector('.mg-nav-items');
        if (!items) return;

        if (section.classList.contains('collapsed')) {
            // 열기
            section.classList.remove('collapsed');
            items.style.maxHeight = items.scrollHeight + 'px';
            // 트랜지션 후 maxHeight 제거 (내부 높이 변동 대응)
            items.addEventListener('transitionend', function handler() {
                items.style.maxHeight = '';
                items.removeEventListener('transitionend', handler);
            });
        } else {
            // 닫기: 현재 높이를 명시적으로 설정 후 0으로 전환
            items.style.maxHeight = items.scrollHeight + 'px';
            // 강제 reflow
            items.offsetHeight;
            section.classList.add('collapsed');
            items.style.maxHeight = '0';
        }

        // localStorage에 상태 저장
        saveNavState();
    }

    function saveNavState() {
        var sections = document.querySelectorAll('.mg-nav-section[data-section]');
        var state = {};
        sections.forEach(function(s) {
            state[s.getAttribute('data-section')] = s.classList.contains('collapsed');
        });
        try { localStorage.setItem('mg_nav_state', JSON.stringify(state)); } catch(e) {}
    }

    function restoreNavState() {
        try {
            var state = JSON.parse(localStorage.getItem('mg_nav_state'));
            if (!state) return;
            var sections = document.querySelectorAll('.mg-nav-section[data-section]');
            sections.forEach(function(s) {
                var key = s.getAttribute('data-section');
                // 활성 메뉴가 포함된 섹션은 항상 열기
                if (s.querySelector('.mg-nav-item.active')) {
                    s.classList.remove('collapsed');
                    var items = s.querySelector('.mg-nav-items');
                    if (items) items.style.maxHeight = '';
                    return;
                }
                if (state[key] === true) {
                    s.classList.add('collapsed');
                    var items = s.querySelector('.mg-nav-items');
                    if (items) items.style.maxHeight = '0';
                } else if (state[key] === false) {
                    s.classList.remove('collapsed');
                    var items = s.querySelector('.mg-nav-items');
                    if (items) items.style.maxHeight = '';
                }
            });
        } catch(e) {}
    }

    restoreNavState();

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
