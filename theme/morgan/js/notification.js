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
            xhr.open('GET', this.bbs + '/notification_api.php?action=list&rows=10', true);
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
                this.listEl.innerHTML = '<div class="py-8 text-center text-mg-text-muted text-sm">알림이 없습니다.</div>';
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
        },

        // 알림 타입별 아이콘
        getTypeIcon: function(type) {
            var icons = {
                'comment': { bg: 'bg-blue-500/20', svg: '<svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>' },
                'reply': { bg: 'bg-cyan-500/20', svg: '<svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>' },
                'like': { bg: 'bg-rose-500/20', svg: '<svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>' },
                'character_approved': { bg: 'bg-emerald-500/20', svg: '<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                'character_rejected': { bg: 'bg-red-500/20', svg: '<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>' },
                'gift_received': { bg: 'bg-amber-500/20', svg: '<svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>' },
                'emoticon': { bg: 'bg-violet-500/20', svg: '<svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M8 14s1.5 2 4 2 4-2 4-2"/></svg>' },
                'rp_reply': { bg: 'bg-indigo-500/20', svg: '<svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>' },
            };
            return icons[type] || { bg: 'bg-mg-bg-tertiary', svg: '<svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>' };
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
                    window.location.href = url;
                }
            };
            xhr.onerror = function() {
                if (url && url !== '#') {
                    window.location.href = url;
                }
            };
            xhr.send('action=read&noti_id=' + notiId);

            // 뱃지 즉시 업데이트
            if (this.lastCount > 0) {
                this.updateBadge(this.lastCount - 1);
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
            this.toastContainer = document.getElementById('mg-toast-container');
            if (!this.toastContainer) {
                this.toastContainer = document.createElement('div');
                this.toastContainer.id = 'mg-toast-container';
                this.toastContainer.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column-reverse;gap:8px;pointer-events:none;';
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
            if (!this.toastContainer) return;

            var icon = this.getTypeIcon(item.noti_type);
            var url = item.noti_url || '';
            var notiId = item.noti_id;

            var toast = document.createElement('div');
            toast.style.cssText = 'pointer-events:auto;max-width:360px;width:100%;opacity:0;transform:translateX(100%);transition:all 0.3s ease;';
            toast.innerHTML =
                '<div style="background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);border-radius:12px;padding:14px 16px;box-shadow:0 8px 24px rgba(0,0,0,0.3);display:flex;align-items:flex-start;gap:12px;cursor:' + (url ? 'pointer' : 'default') + ';">' +
                    '<div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;" class="' + icon.bg + '">' + icon.svg + '</div>' +
                    '<div style="flex:1;min-width:0;">' +
                        '<p style="font-size:13px;color:var(--mg-text-primary);margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + this.escHtml(item.noti_title) + '</p>' +
                        (item.noti_content ? '<p style="font-size:12px;color:var(--mg-text-muted);margin:3px 0 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + this.escHtml(item.noti_content) + '</p>' : '') +
                    '</div>' +
                    '<button type="button" style="flex-shrink:0;padding:2px;color:var(--mg-text-muted);background:none;border:none;cursor:pointer;line-height:1;" data-toast-close="1">' +
                        '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                    '</button>' +
                '</div>';

            // 클릭 시 이동
            if (url) {
                toast.addEventListener('click', function(e) {
                    if (e.target.closest('[data-toast-close]')) return;
                    MgNoti.markAndGo(e, notiId, url);
                    dismissToast();
                });
            }

            // 닫기 버튼
            toast.querySelector('[data-toast-close]').addEventListener('click', function(e) {
                e.stopPropagation();
                dismissToast();
            });

            this.toastContainer.appendChild(toast);

            // 등장 애니메이션
            requestAnimationFrame(function() {
                toast.style.opacity = '1';
                toast.style.transform = 'translateX(0)';
            });

            // 5초 후 자동 퇴장
            var autoTimer = setTimeout(function() { dismissToast(); }, 5000);

            function dismissToast() {
                clearTimeout(autoTimer);
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(function() {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
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
})();
