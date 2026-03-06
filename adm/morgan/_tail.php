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

    // 관리자용 Toast (프론트 notification.js 미로드 대응)
    if (typeof mgToast === 'undefined') {
        window.mgToast = function(msg, type, duration) {
            type = type || 'info';
            duration = duration || 3000;
            var colors = {success:'#22c55e',error:'#ef4444',warning:'#f59e0b',info:'#3b82f6'};
            var toast = document.createElement('div');
            toast.style.cssText = 'position:fixed;top:1rem;left:50%;transform:translateX(-50%) translateY(-100%);z-index:99999;padding:12px 24px;border-radius:8px;color:#fff;font-size:14px;max-width:500px;text-align:center;opacity:0;transition:all .3s ease;background:' + (colors[type]||colors.info) + ';box-shadow:0 4px 12px rgba(0,0,0,.3);';
            toast.innerHTML = msg.replace(/\n/g, '<br>');
            document.body.appendChild(toast);
            requestAnimationFrame(function(){
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(-50%) translateY(0)';
            });
            setTimeout(function(){
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-100%)';
                setTimeout(function(){ toast.remove(); }, 300);
            }, duration);
        };
    }

    // 플래시 토스트 쿠키 체크
    (function() {
        var match = document.cookie.match(/mg_flash_toast=([^;]+)/);
        if (match) {
            try {
                var data = JSON.parse(decodeURIComponent(match[1]));
                if (data && data.msg) {
                    mgToast(data.msg.replace(/\\n/g, '\n'), data.type || 'info', 3000);
                }
            } catch(e) {}
            document.cookie = 'mg_flash_toast=; path=/; max-age=0';
        }
    })();

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
