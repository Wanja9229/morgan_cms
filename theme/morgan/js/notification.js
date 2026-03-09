/**
 * Morgan Edition - Notification System
 */
(function() {
    'use strict';

    var MgNoti = {
        bbs: '',
        panel: null,
        badge: null,
        listEl: null,
        isOpen: false,
        pollTimer: null,
        lastCount: -1,
        toastContainer: null,

        init: function() {
            var meta = document.querySelector('meta[name="mg-bbs-url"]');
            this.bbs = meta ? meta.content : '/bbs';
            this.panel = document.getElementById('mg-noti-panel');
            this.badge = document.getElementById('mg-noti-badge');
            this.listEl = document.getElementById('mg-noti-list');

            if (!this.panel || !this.badge) return;

            // 토스트 컨테이너 생성
            this.createToastContainer();

            var toggleBtn = document.getElementById('mg-noti-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    MgNoti.toggle();
                });
            }

            // 외부 클릭 시 닫기
            document.addEventListener('click', function(e) {
                if (MgNoti.isOpen && MgNoti.panel && !MgNoti.panel.contains(e.target)) {
                    MgNoti.close();
                }
            });

            // ESC로 닫기
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && MgNoti.isOpen) {
                    MgNoti.close();
                }
            });

            // 최초 카운트 조회
            this.fetchCount();

            // 30초마다 polling
            this.pollTimer = setInterval(function() {
                MgNoti.fetchCount();
            }, 30000);
        },

        toggle: function() {
            this.isOpen ? this.close() : this.open();
        },

        open: function() {
            if (!this.panel) return;
            this.isOpen = true;
            this.panel.classList.remove('hidden');
            this.panel.classList.add('animate-fade-in');
            this.fetchList();
        },

        close: function() {
            if (!this.panel) return;
            this.isOpen = false;
            this.panel.classList.add('hidden');
            this.panel.classList.remove('animate-fade-in');
        },

        // 미읽은 알림 수 조회
        fetchCount: function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', this.bbs + '/notification_api.php?action=count', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            var newCount = res.data.count;
                            // 카운트가 증가했으면 토스트 표시
                            if (MgNoti.lastCount >= 0 && newCount > MgNoti.lastCount) {
                                MgNoti.fetchLatestForToast();
                            }
                            MgNoti.updateBadge(newCount);
                        }
                    } catch (e) {}
                }
            };
            xhr.send();
        },

        // 뱃지 업데이트
        updateBadge: function(count) {
            if (!this.badge) return;
            this.lastCount = count;
            if (count > 0) {
                this.badge.textContent = count > 99 ? '99+' : count;
                this.badge.classList.remove('hidden');
            } else {
                this.badge.classList.add('hidden');
            }
        },

        // 드롭다운 목록 로드
        fetchList: function() {
            if (!this.listEl) return;
            this.listEl.innerHTML = '<div class="py-6 text-center text-mg-text-muted text-sm">로딩중...</div>';

            var xhr = new XMLHttpRequest();
            xhr.open('GET', this.bbs + '/notification_api.php?action=list&rows=10&unread_only=1', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            MgNoti.renderList(res.data.items);
                        }
                    } catch (e) {
                        MgNoti.listEl.innerHTML = '<div class="py-6 text-center text-mg-error text-sm">오류가 발생했습니다.</div>';
                    }
                }
            };
            xhr.send();
        },

        // 드롭다운 목록 렌더링
        renderList: function(items) {
            if (!this.listEl) return;

            if (!items || items.length === 0) {
                this.listEl.innerHTML = '<div class="py-8 text-center text-mg-text-muted text-sm">새로운 알림이 없습니다.</div>';
                return;
            }

            var html = '';
            for (var i = 0; i < items.length; i++) {
                var item = items[i];
                var unreadClass = item.noti_read ? '' : 'bg-mg-accent/5';
                var dotHtml = item.noti_read ? '' : '<span class="w-2 h-2 rounded-full bg-mg-accent flex-shrink-0"></span>';
                var icon = this.getTypeIcon(item.noti_type);
                var url = item.noti_url || '#';

                html += '<a href="' + this.escHtml(url) + '" class="flex items-start gap-3 px-4 py-3 hover:bg-mg-bg-tertiary/50 transition-colors ' + unreadClass + '"'
                      + ' data-noti-id="' + item.noti_id + '"'
                      + ' onclick="MgNoti.markAndGo(event, ' + item.noti_id + ', \'' + this.escHtml(url) + '\')">';
                html += '<div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 ' + icon.bg + '">' + icon.svg + '</div>';
                html += '<div class="flex-1 min-w-0">';
                html += '<p class="text-sm text-mg-text-primary truncate">' + this.escHtml(item.noti_title) + '</p>';
                if (item.noti_content) {
                    html += '<p class="text-xs text-mg-text-muted truncate mt-0.5">' + this.escHtml(item.noti_content) + '</p>';
                }
                html += '<span class="text-xs text-mg-text-muted mt-1 block">' + this.escHtml(item.time_ago) + '</span>';
                html += '</div>';
                html += dotHtml;
                html += '</a>';
            }
            this.listEl.innerHTML = html;
            this._renderIcons();
        },

        // Lucide 아이콘 헬퍼
        _lucideIcon: function(name, cls) {
            return '<i data-lucide="' + name + '" class="' + (cls || 'w-4 h-4') + '"></i>';
        },

        // Lucide 아이콘 렌더링 (DOM 삽입 후 호출)
        _renderIcons: function() {
            if (typeof lucide !== 'undefined') lucide.createIcons();
        },

        // 알림 타입별 아이콘
        getTypeIcon: function(type) {
            var icons = {
                'comment': { bg: 'bg-blue-500/20', svg: this._lucideIcon('message-circle', 'w-4 h-4 text-blue-400') },
                'reply': { bg: 'bg-cyan-500/20', svg: this._lucideIcon('reply', 'w-4 h-4 text-cyan-400') },
                'like': { bg: 'bg-rose-500/20', svg: this._lucideIcon('heart', 'w-4 h-4 text-rose-400') },
                'character_approved': { bg: 'bg-emerald-500/20', svg: this._lucideIcon('check-circle', 'w-4 h-4 text-emerald-400') },
                'character_rejected': { bg: 'bg-red-500/20', svg: this._lucideIcon('x-circle', 'w-4 h-4 text-red-400') },
                'gift_received': { bg: 'bg-amber-500/20', svg: this._lucideIcon('gift', 'w-4 h-4 text-amber-400') },
                'emoticon': { bg: 'bg-violet-500/20', svg: this._lucideIcon('smile', 'w-4 h-4 text-violet-400') },
                'rp_reply': { bg: 'bg-indigo-500/20', svg: this._lucideIcon('message-square', 'w-4 h-4 text-indigo-400') },
            };
            return icons[type] || { bg: 'bg-mg-bg-tertiary', svg: this._lucideIcon('bell', 'w-4 h-4 text-mg-text-muted') };
        },

        // 읽음 처리 후 이동
        markAndGo: function(e, notiId, url) {
            e.preventDefault();

            var xhr = new XMLHttpRequest();
            xhr.open('POST', this.bbs + '/notification_api.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (url && url !== '#') {
                    MgNoti._spaNavigate(url);
                }
            };
            xhr.onerror = function() {
                if (url && url !== '#') {
                    MgNoti._spaNavigate(url);
                }
            };
            xhr.send('action=read&noti_id=' + notiId);

            // 드롭다운 닫기
            this.close();

            // 뱃지 즉시 업데이트
            if (this.lastCount > 0) {
                this.updateBadge(this.lastCount - 1);
            }
        },

        // SPA 라우터가 있으면 SPA 이동, 없으면 일반 이동
        _spaNavigate: function(url) {
            if (window.MG && MG.router && typeof MG.router.navigate === 'function' && MG.router.isInternalLink(url)) {
                MG.router.navigate(url);
            } else {
                window.location.href = url;
            }
        },

        // 전체 읽음
        readAll: function() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', this.bbs + '/notification_api.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                MgNoti.updateBadge(0);
                MgNoti.fetchList();
            };
            xhr.send('action=read_all');
        },

        // === 토스트 알림 ===

        createToastContainer: function() {
            this.toastContainer = document.getElementById('mg-noti-toast-container');
            if (!this.toastContainer) {
                this.toastContainer = document.createElement('div');
                this.toastContainer.id = 'mg-noti-toast-container';
                this.toastContainer.style.cssText = 'display:none;';
                document.body.appendChild(this.toastContainer);
            }
        },

        // 폴링에서 카운트 증가 감지 시 최신 알림 1개를 가져와 토스트로 표시
        fetchLatestForToast: function() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', this.bbs + '/notification_api.php?action=list&rows=1&unread_only=1', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success && res.data.items && res.data.items.length > 0) {
                            MgNoti.showToast(res.data.items[0]);
                        }
                    } catch (e) {}
                }
            };
            xhr.send();
        },

        showToast: function(item) {
            if (typeof mgToast !== 'function') return;

            var title = this.escHtml(item.noti_title);
            var content = item.noti_content ? this.escHtml(item.noti_content) : '';
            var msg = content ? title + '\n' + content : title;
            var url = item.noti_url || '';
            var notiId = item.noti_id;

            // mgToast로 통일 표시
            var t = mgToast(msg, 'info', 5000);

            // 알림 토스트는 클릭 시 URL 이동 + 읽음 처리
            if (url && t.dismiss) {
                var container = document.getElementById('mg-toast-container');
                if (container) {
                    var lastToast = container.lastElementChild;
                    if (lastToast) {
                        lastToast.style.cursor = 'pointer';
                        lastToast.addEventListener('click', function(e) {
                            if (e.target.closest('[data-mg-toast-close]')) return;
                            MgNoti.markAndGo(e, notiId, url);
                            t.dismiss();
                        });
                    }
                }
            }
        },

        escHtml: function(str) {
            if (!str) return '';
            var d = document.createElement('div');
            d.appendChild(document.createTextNode(str));
            return d.innerHTML;
        }
    };

    // 전역 노출
    window.MgNoti = MgNoti;

    // DOM Ready 시 초기화
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() { MgNoti.init(); });
    } else {
        MgNoti.init();
    }

    // 플래시 토스트: 쿠키에 저장된 메시지가 있으면 표시 (PHP alert() 리다이렉트 후)
    function checkFlashToast() {
        var match = document.cookie.match(/mg_flash_toast=([^;]+)/);
        if (!match) return;
        // 쿠키 즉시 삭제
        document.cookie = 'mg_flash_toast=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/';
        try {
            var data = JSON.parse(decodeURIComponent(match[1]));
            if (data.msg && typeof mgToast === 'function') {
                mgToast(data.msg.replace(/\\n/g, '\n'), data.type || 'info', 3000);
            }
        } catch(e) {}
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkFlashToast);
    } else {
        checkFlashToast();
    }
    // SPA 네비게이션 후에도 플래시 토스트 체크
    window.addEventListener('mg:pageLoaded', checkFlashToast);
})();

/**
 * mgToast — alert() 대체용 간단 토스트
 * @param {string} message
 * @param {string} type  'info' | 'success' | 'error' | 'warning'
 * @param {number} duration  자동 닫힘 ms (기본 3000)
 */
window.mgToast = function(message, type, duration) {
    type = type || 'info';
    duration = duration || 3000;
    var icons = {
        success: '<i data-lucide="check-circle" style="width:16px;height:16px;color:#22c55e;"></i>',
        error:   '<i data-lucide="x-circle" style="width:16px;height:16px;color:#ef4444;"></i>',
        warning: '<i data-lucide="alert-triangle" style="width:16px;height:16px;color:#eab308;"></i>',
        info:    '<i data-lucide="info" style="width:16px;height:16px;color:#60a5fa;"></i>'
    };
    var bgs = { success:'rgba(34,197,94,0.15)', error:'rgba(239,68,68,0.15)', warning:'rgba(234,179,8,0.15)', info:'rgba(96,165,250,0.15)' };
    var container = document.getElementById('mg-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'mg-toast-container';
        container.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;gap:8px;pointer-events:none;max-width:400px;width:calc(100% - 32px);';
        document.body.appendChild(container);
    }
    var toast = document.createElement('div');
    toast.style.cssText = 'pointer-events:auto;width:100%;opacity:0;transform:translateY(-20px);transition:all 0.3s ease;';
    toast.innerHTML =
        '<div style="background:var(--mg-bg-secondary,#2b2d31);border:1px solid var(--mg-bg-tertiary,#313338);border-radius:12px;padding:14px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.3);display:flex;align-items:center;gap:12px;">' +
            '<div style="width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:' + (bgs[type]||bgs.info) + ';">' + (icons[type]||icons.info) + '</div>' +
            '<p style="flex:1;font-size:13px;color:var(--mg-text-primary,#f2f3f5);margin:0;line-height:1.5;word-break:break-word;">' + String(message).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>') + '</p>' +
            '<button type="button" style="flex-shrink:0;padding:2px;color:var(--mg-text-muted,#949ba4);background:none;border:none;cursor:pointer;line-height:1;" data-mg-toast-close>' +
                '<i data-lucide="x" style="width:14px;height:14px;"></i>' +
            '</button>' +
        '</div>';
    toast.querySelector('[data-mg-toast-close]').addEventListener('click', function() { dismiss(); });
    container.appendChild(toast);
    if (typeof lucide !== 'undefined') lucide.createIcons();
    requestAnimationFrame(function() { toast.style.opacity='1'; toast.style.transform='translateY(0)'; });
    var timer = setTimeout(function() { dismiss(); }, duration);
    function dismiss() {
        clearTimeout(timer);
        toast.style.opacity='0'; toast.style.transform='translateY(-20px)';
        setTimeout(function() { if(toast.parentNode) toast.parentNode.removeChild(toast); }, 300);
    }
    return { dismiss: dismiss };
};

/**
 * mgConfirm — confirm() 대체용 모달
 * @param {string} message
 * @param {function} onOk
 * @param {function} [onCancel]
 */
window.mgConfirm = function(message, onOk, onCancel) {
    var overlay = document.createElement('div');
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity 0.2s ease;';
    overlay.innerHTML =
        '<div style="background:var(--mg-bg-secondary,#2b2d31);border:1px solid var(--mg-bg-tertiary,#313338);border-radius:12px;padding:24px;max-width:400px;width:calc(100% - 48px);box-shadow:0 16px 48px rgba(0,0,0,0.4);transform:scale(0.95);transition:transform 0.2s ease;">' +
            '<p style="color:var(--mg-text-primary,#f2f3f5);font-size:14px;margin:0 0 20px;line-height:1.5;word-break:break-word;">' + String(message).replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>') + '</p>' +
            '<div style="display:flex;gap:8px;justify-content:flex-end;">' +
                '<button type="button" data-action="cancel" style="padding:8px 16px;border-radius:8px;border:1px solid var(--mg-bg-tertiary,#313338);background:var(--mg-bg-tertiary,#313338);color:var(--mg-text-secondary,#b5bac1);cursor:pointer;font-size:13px;">취소</button>' +
                '<button type="button" data-action="ok" style="padding:8px 16px;border-radius:8px;border:none;background:var(--mg-button,#f59f0a);color:var(--mg-button-text,#fff);cursor:pointer;font-size:13px;">확인</button>' +
            '</div>' +
        '</div>';
    document.body.appendChild(overlay);
    requestAnimationFrame(function() {
        overlay.style.opacity='1';
        overlay.querySelector('div').style.transform='scale(1)';
    });
    function close() {
        overlay.style.opacity='0';
        setTimeout(function() { if(overlay.parentNode) overlay.parentNode.removeChild(overlay); }, 200);
    }
    overlay.querySelector('[data-action="ok"]').addEventListener('click', function() { close(); if(onOk) onOk(); });
    overlay.querySelector('[data-action="cancel"]').addEventListener('click', function() { close(); if(onCancel) onCancel(); });
    overlay.addEventListener('click', function(e) { if(e.target===overlay) { close(); if(onCancel) onCancel(); } });
};
