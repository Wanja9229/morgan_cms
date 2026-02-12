/**
 * Morgan Edition - Main JavaScript
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Morgan Edition initialized');

        // Initialize modules
        MG.init();
    });

    // Morgan Edition Global Object
    window.MG = {
        // 초기화
        init: function() {
            this.initSidebar();
            this.initSearch();
            this.initMobileMenu();
            this.router.init();
        },

        // 사이드바 토글 (모바일)
        // 사이드바 브레이크포인트 (lg = 1024px)
        SIDEBAR_BP: 1024,

        initSidebar: function() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');
            const backdrop = document.getElementById('sidebar-backdrop');

            if (!toggleBtn || !sidebar) return;

            this._closeMobileSidebar = function() {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('flex');
                if (backdrop) backdrop.classList.add('hidden');
                if (window.MG_BoardPanel) window.MG_BoardPanel.close();
                if (window.MG_LorePanel) window.MG_LorePanel.close();
            };

            // 우측 위젯 사이드바 닫기
            this._closeWidgetSidebar = function() {
                var ws = document.getElementById('widget-sidebar');
                if (ws) ws.classList.add('hidden');
                if (backdrop) backdrop.classList.add('hidden');
            };

            var closeMobile = this._closeMobileSidebar;
            var closeWidget = this._closeWidgetSidebar;

            toggleBtn.addEventListener('click', function() {
                // 우측 사이드바가 열려있으면 먼저 닫기
                closeWidget();
                var isHidden = sidebar.classList.contains('hidden');
                if (isHidden) {
                    sidebar.classList.remove('hidden');
                    sidebar.classList.add('flex');
                    if (backdrop) backdrop.classList.remove('hidden');
                } else {
                    closeMobile();
                }
            });

            // 우측 위젯 사이드바 토글
            var widgetToggle = document.getElementById('widget-toggle');
            var widgetSidebar = document.getElementById('widget-sidebar');
            if (widgetToggle && widgetSidebar) {
                widgetToggle.addEventListener('click', function() {
                    // 좌측 사이드바가 열려있으면 먼저 닫기
                    closeMobile();
                    var isHidden = widgetSidebar.classList.contains('hidden');
                    if (isHidden) {
                        widgetSidebar.classList.remove('hidden');
                        if (backdrop) backdrop.classList.remove('hidden');
                    } else {
                        closeWidget();
                    }
                });
            }
        },

        // 검색 기능
        initSearch: function() {
            const searchInput = document.getElementById('header-search');

            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const query = this.value.trim();
                        if (query) {
                            // TODO: 검색 페이지로 이동 또는 fetch 검색
                            console.log('Search:', query);
                        }
                    }
                });
            }
        },

        // 모바일 메뉴
        initMobileMenu: function() {
            var self = this;
            var backdrop = document.getElementById('sidebar-backdrop');

            // 백드롭 클릭 시 전체 닫기
            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    if (self._closeMobileSidebar) self._closeMobileSidebar();
                    if (self._closeWidgetSidebar) self._closeWidgetSidebar();
                });
            }

            // 사이드바 직접 링크(a 태그) 클릭 시 사이드바 닫기 (< lg 브레이크포인트)
            var sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.addEventListener('click', function(e) {
                    if (window.innerWidth >= MG.SIDEBAR_BP) return;
                    var link = e.target.closest('a');
                    if (link && self._closeMobileSidebar) {
                        self._closeMobileSidebar();
                    }
                });
            }
        },

        // API 요청 헬퍼
        api: {
            baseUrl: '/api',

            async request(endpoint, options = {}) {
                const url = this.baseUrl + '/' + endpoint;

                const defaultOptions = {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                };

                try {
                    const response = await fetch(url, { ...defaultOptions, ...options });
                    const data = await response.json();
                    return data;
                } catch (error) {
                    console.error('API Error:', error);
                    return { success: false, error: { message: error.message } };
                }
            },

            get(endpoint) {
                return this.request(endpoint, { method: 'GET' });
            },

            post(endpoint, data) {
                return this.request(endpoint, {
                    method: 'POST',
                    body: JSON.stringify(data)
                });
            },

            put(endpoint, data) {
                return this.request(endpoint, {
                    method: 'PUT',
                    body: JSON.stringify(data)
                });
            },

            delete(endpoint) {
                return this.request(endpoint, { method: 'DELETE' });
            }
        },

        // 유틸리티
        util: {
            // 디바운스
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            },

            // 토스트 메시지
            toast(message, type = 'info') {
                // TODO: 토스트 UI 구현
                console.log(`[${type}] ${message}`);
            }
        },

        // SPA-like 라우터
        router: {
            mainContent: null,
            isNavigating: false,

            init: function() {
                this.mainContent = document.getElementById('main-content');
                if (!this.mainContent) {
                    console.warn('SPA Router: #main-content not found');
                    return;
                }

                // 링크 클릭 이벤트 위임
                document.addEventListener('click', this.handleClick.bind(this));

                // 브라우저 뒤로/앞으로 버튼 처리
                window.addEventListener('popstate', this.handlePopState.bind(this));

                console.log('SPA Router initialized');
            },

            // 내부 링크인지 확인
            isInternalLink: function(url) {
                try {
                    const linkUrl = new URL(url, window.location.origin);
                    const currentUrl = new URL(window.location.href);

                    // 같은 도메인이고, 해시만 다른 게 아닌 경우
                    if (linkUrl.origin !== currentUrl.origin) return false;

                    // 관리자 페이지는 제외
                    if (linkUrl.pathname.includes('/adm/')) return false;

                    // 파일 다운로드, 로그아웃 등 제외
                    const excludePatterns = [
                        '/logout.php',
                        '/download.php',
                        '/formmail.php',
                        '/rp_api.php'
                    ];
                    for (const pattern of excludePatterns) {
                        if (linkUrl.pathname.includes(pattern)) return false;
                    }

                    return true;
                } catch (e) {
                    return false;
                }
            },

            // 클릭 이벤트 핸들러
            handleClick: function(e) {
                // 링크 요소 찾기 (버블링 고려)
                const link = e.target.closest('a');
                if (!link) return;

                const href = link.getAttribute('href');
                if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;

                // 새 탭에서 열기 (Ctrl/Cmd + 클릭 또는 target="_blank")
                if (e.ctrlKey || e.metaKey || link.target === '_blank') return;

                // form submit 버튼 등 무시
                if (link.hasAttribute('data-no-spa')) return;

                // 내부 링크가 아니면 무시
                if (!this.isInternalLink(href)) return;

                // 기본 동작 중단하고 SPA 네비게이션
                e.preventDefault();
                this.navigate(href);
            },

            // 브라우저 뒤로/앞으로 버튼
            handlePopState: function(e) {
                if (e.state && e.state.spa) {
                    this.loadPage(window.location.href, false);
                }
            },

            // 페이지 네비게이션
            navigate: function(url) {
                if (this.isNavigating) return;

                // 현재 URL과 같으면 무시
                if (url === window.location.href) return;

                this.loadPage(url, true);
            },

            // 페이지 로드
            loadPage: async function(url, pushState = true) {
                if (this.isNavigating) return;
                this.isNavigating = true;

                // 로딩 상태 표시
                this.mainContent.style.opacity = '0.5';
                this.mainContent.style.pointerEvents = 'none';

                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}`);
                    }

                    const html = await response.text();

                    // 페이지 타이틀 업데이트
                    const pageTitle = response.headers.get('X-Page-Title');
                    if (pageTitle) {
                        document.title = decodeURIComponent(pageTitle);
                    }

                    // 콘텐츠 추출 및 삽입
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('ajax-content');

                    if (newContent) {
                        this.mainContent.innerHTML = newContent.innerHTML;
                    } else {
                        // #ajax-content가 없으면 전체 HTML 사용 (fallback)
                        this.mainContent.innerHTML = html;
                    }

                    // URL 업데이트
                    if (pushState) {
                        history.pushState({ spa: true }, '', url);
                    }

                    // 스크롤 맨 위로
                    this.mainContent.scrollTo(0, 0);

                    // 사이드바 활성 상태 업데이트
                    this.updateSidebar(url);

                    // 새 콘텐츠의 스크립트 실행
                    this.executeScripts();

                    console.log('SPA: Loaded', url);

                } catch (error) {
                    console.error('SPA Navigation Error:', error);
                    // 에러 시 전통적인 네비게이션
                    window.location.href = url;
                } finally {
                    this.mainContent.style.opacity = '';
                    this.mainContent.style.pointerEvents = '';
                    this.isNavigating = false;
                }
            },

            // 새로 삽입된 콘텐츠의 스크립트 실행
            executeScripts: function() {
                const scripts = this.mainContent.querySelectorAll('script');
                scripts.forEach(oldScript => {
                    const newScript = document.createElement('script');
                    Array.from(oldScript.attributes).forEach(attr => {
                        newScript.setAttribute(attr.name, attr.value);
                    });
                    newScript.textContent = oldScript.textContent;
                    oldScript.parentNode.replaceChild(newScript, oldScript);
                });
            },

            // 사이드바 활성 상태 업데이트
            updateSidebar: function(url) {
                try {
                    var parsed = new URL(url, window.location.origin);
                    var pathname = parsed.pathname;
                    var script = pathname.split('/').pop() || 'index.php';
                    var params = parsed.searchParams;
                    var boTable = params.get('bo_table') || '';

                    // SPA 네비게이션 시 모바일 사이드바/위젯/패널 닫기
                    if (window.innerWidth < MG.SIDEBAR_BP) {
                        var sb = document.getElementById('sidebar');
                        if (sb) { sb.classList.add('hidden'); sb.classList.remove('flex'); }
                        var bd = document.getElementById('sidebar-backdrop');
                        if (bd) bd.classList.add('hidden');
                        var ws = document.getElementById('widget-sidebar');
                        if (ws) ws.classList.add('hidden');
                        if (window.MG_BoardPanel) window.MG_BoardPanel.close();
                        if (window.MG_LorePanel) window.MG_LorePanel.close();
                    }

                    // 페이지별 사이드바 ID 매핑
                    var activeId = '';
                    var isCommunity = false;

                    if (script === 'index.php' || script === '' || pathname === '/') {
                        activeId = 'home';
                    } else if (script === 'board.php' || script === 'write.php' || script === 'view.php') {
                        activeId = 'board';
                        isCommunity = true;
                    } else if (script === 'rp_list.php') {
                        activeId = 'board';
                        isCommunity = true;
                    } else if (script === 'character.php' || script === 'character_edit.php' || script === 'character_form.php') {
                        activeId = 'character';
                    } else if (script === 'character_view.php') {
                        activeId = (params.get('from') === 'list') ? 'character_list' : 'character';
                    } else if (script === 'character_list.php') {
                        activeId = 'character_list';
                    } else if (script === 'shop.php') {
                        activeId = 'shop';
                    } else if (script === 'inventory.php') {
                        activeId = 'inventory';
                    } else if (script === 'new.php') {
                        activeId = 'new';
                    } else if (script === 'lore.php' || script === 'lore_view.php' || script === 'lore_timeline.php') {
                        activeId = 'lore';
                    } else if (script === 'pioneer.php') {
                        activeId = 'pioneer';
                    }

                    var isLore = (activeId === 'lore');

                    // 모든 사이드바 아이콘에서 활성 클래스 제거
                    var icons = document.querySelectorAll('#sidebar [data-sidebar-id]');
                    var activeClasses = ['!bg-mg-accent', '!text-white', '!rounded-xl'];
                    icons.forEach(function(icon) {
                        if (icon.id === 'sidebar-board-toggle' || icon.id === 'sidebar-lore-toggle') return;
                        activeClasses.forEach(function(cls) {
                            icon.classList.remove(cls);
                        });
                    });

                    // 새 활성 아이콘에 클래스 추가
                    if (activeId && activeId !== 'board') {
                        var activeIcon = document.querySelector('#sidebar [data-sidebar-id="' + activeId + '"]');
                        if (activeIcon) {
                            activeClasses.forEach(function(cls) {
                                activeIcon.classList.add(cls);
                            });
                        }
                    }

                    // 보드 토글 + 패널 상태 업데이트
                    var boardToggle = document.getElementById('sidebar-board-toggle');
                    if (boardToggle && window.MG_BoardPanel) {
                        window.MG_BoardPanel.setCommunityPage(isCommunity);
                        if (isCommunity) {
                            boardToggle.classList.add('!bg-mg-accent', '!text-white', '!rounded-xl');
                            // 데스크톱에서만 패널 자동 열기 (모바일은 이미 사이드바가 닫혀있음)
                            if (window.innerWidth >= MG.SIDEBAR_BP) {
                                window.MG_BoardPanel.open();
                            }
                        } else {
                            boardToggle.classList.remove('!bg-mg-accent', '!text-white', '!rounded-xl');
                            window.MG_BoardPanel.close();
                        }
                    }

                    // 보드 서브메뉴 내 개별 항목 활성 상태 업데이트
                    // isCommunity일 때만 처리 (RP 페이지에서도 게시판 하이라이트 제거 필요)
                    if (isCommunity) {
                        var boardLinks = document.querySelectorAll('#sidebar-board-panel nav a[href*="bo_table"]');
                        boardLinks.forEach(function(link) {
                            var linkHref = link.getAttribute('href') || '';
                            // boTable이 있을 때만 해당 게시판 활성화, 없으면 모두 비활성화
                            var isActive = boTable && linkHref.indexOf('bo_table=' + boTable) !== -1;
                            var hashSpan = link.querySelector('span:first-child');
                            var dotSpan = link.querySelector('.ml-auto');

                            if (isActive) {
                                link.className = link.className
                                    .replace(/text-mg-text-secondary/g, 'text-mg-text-primary')
                                    .replace(/hover:bg-mg-bg-tertiary\/50/g, '')
                                    .replace(/hover:text-mg-text-primary/g, '');
                                if (link.className.indexOf('bg-mg-accent/15') === -1) {
                                    link.classList.add('bg-mg-accent/15', 'font-medium');
                                }
                                if (hashSpan) {
                                    hashSpan.className = hashSpan.className.replace('text-mg-text-muted', 'text-mg-accent');
                                }
                                if (!dotSpan) {
                                    link.insertAdjacentHTML('beforeend', '<span class="ml-auto w-1 h-1 rounded-full bg-mg-accent"></span>');
                                }
                            } else {
                                link.classList.remove('bg-mg-accent/15', 'font-medium');
                                if (link.className.indexOf('text-mg-text-secondary') === -1) {
                                    link.classList.add('text-mg-text-secondary');
                                }
                                if (link.className.indexOf('hover:bg-mg-bg-tertiary/50') === -1) {
                                    link.classList.add('hover:bg-mg-bg-tertiary/50', 'hover:text-mg-text-primary');
                                }
                                if (hashSpan) {
                                    hashSpan.className = hashSpan.className.replace('text-mg-accent', 'text-mg-text-muted');
                                }
                                if (dotSpan) {
                                    dotSpan.remove();
                                }
                            }
                        });
                    }

                    // 세계관 토글 + 패널 상태 업데이트
                    if (window.MG_LorePanel) {
                        window.MG_LorePanel.setLorePage(isLore);
                        window.MG_LorePanel.updateFocus();
                        if (isLore) {
                            // 데스크톱에서만 패널 자동 열기
                            if (window.innerWidth >= MG.SIDEBAR_BP) {
                                window.MG_LorePanel.open();
                            }
                        } else {
                            window.MG_LorePanel.close();
                        }
                    }

                    // RP 링크 활성 상태
                    var isRp = (script === 'rp_list.php');
                    var rpLinks = document.querySelectorAll('#sidebar-board-panel nav a[href*="rp_list"]');
                    rpLinks.forEach(function(link) {
                        var rpIcon = link.querySelector('svg');
                        if (isRp) {
                            link.classList.add('bg-mg-accent/15', 'font-medium');
                            link.classList.remove('text-mg-text-secondary');
                            link.classList.add('text-mg-text-primary');
                            if (rpIcon) rpIcon.className = rpIcon.className.replace('text-mg-text-muted', 'text-mg-accent');
                        } else {
                            link.classList.remove('bg-mg-accent/15', 'font-medium');
                            link.classList.add('text-mg-text-secondary');
                            link.classList.remove('text-mg-text-primary');
                            if (rpIcon) rpIcon.className = rpIcon.className.replace('text-mg-accent', 'text-mg-text-muted');
                        }
                    });

                } catch (e) {
                    console.warn('SPA: sidebar update error', e);
                }
            },

            // 프로그래매틱 네비게이션 (외부에서 호출용)
            go: function(url) {
                this.navigate(url);
            }
        }
    };

})();
