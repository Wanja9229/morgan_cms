/**
 * Morgan Edition - Emoticon Picker
 */
var MgEmoticonPicker = (function() {
    var _cache = null;
    var _loaded = false;
    var _loading = false;
    var _currentTarget = null;

    function _getApiUrl() {
        var meta = document.querySelector('meta[name="mg-bbs-url"]');
        return meta ? meta.content : '/bbs';
    }

    function _loadEmoticons(callback) {
        if (_loaded && _cache) {
            callback(_cache);
            return;
        }

        if (_loading) return;
        _loading = true;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', _getApiUrl() + '/emoticon_api.php?action=my_emoticons', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    _cache = JSON.parse(xhr.responseText);
                    _loaded = true;
                } catch(e) {
                    _cache = { sets: [] };
                }
            } else {
                _cache = { sets: [] };
            }
            _loading = false;
            callback(_cache);
        };
        xhr.onerror = function() {
            _cache = { sets: [] };
            _loading = false;
            callback(_cache);
        };
        xhr.send();
    }

    function _renderTabs(pickerId, data) {
        var tabsEl = document.getElementById('mgEmoticonTabs_' + pickerId);
        if (!tabsEl) return;

        var html = '';
        if (data.sets && data.sets.length > 0) {
            data.sets.forEach(function(set, idx) {
                var activeClass = idx === 0 ? ' active' : '';
                var preview = set.preview || '';
                html += '<button type="button" class="mg-emoticon-tab' + activeClass + '" ' +
                         'onclick="MgEmoticonPicker.switchTab(\'' + pickerId + '\', ' + idx + ')" ' +
                         'title="' + _escapeHtml(set.name) + '">';
                if (preview) {
                    html += '<img src="' + _escapeHtml(preview) + '" alt="" width="20" height="20">';
                } else {
                    html += '<span>' + _escapeHtml(set.name.substring(0, 2)) + '</span>';
                }
                html += '</button>';
            });
        }
        tabsEl.innerHTML = html;
    }

    function _renderGrid(pickerId, data, tabIndex) {
        var gridEl = document.getElementById('mgEmoticonGrid_' + pickerId);
        if (!gridEl) return;

        if (!data.sets || data.sets.length === 0) {
            gridEl.innerHTML = '<div class="mg-emoticon-popup-empty">보유한 이모티콘이 없습니다.<br><a href="' + _getApiUrl() + '/shop.php?tab=emoticon" style="color:var(--mg-accent);">이모티콘 상점 가기</a></div>';
            return;
        }

        var set = data.sets[tabIndex || 0];
        if (!set || !set.emoticons) return;

        var html = '';
        set.emoticons.forEach(function(em) {
            html += '<button type="button" class="mg-emoticon-grid-item" ' +
                     'onclick="MgEmoticonPicker.insert(\'' + _escapeHtml(em.code) + '\',\'' + _escapeHtml(em.image) + '\')" ' +
                     'title="' + _escapeHtml(em.code) + '">' +
                     '<img src="' + _escapeHtml(em.image) + '" alt="' + _escapeHtml(em.code) + '" loading="lazy">' +
                     '</button>';
        });
        gridEl.innerHTML = html || '<div class="mg-emoticon-popup-empty">이모티콘이 없습니다.</div>';
    }

    function _escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // Close picker when clicking outside
    document.addEventListener('click', function(e) {
        document.querySelectorAll('.mg-emoticon-popup').forEach(function(popup) {
            if (popup.style.display !== 'none' && !popup.contains(e.target)) {
                var wrap = popup.parentElement;
                if (wrap && wrap.classList && wrap.classList.contains('mg-emoticon-picker-wrap') && wrap.contains(e.target)) {
                    return;
                }
                popup.style.display = 'none';
            }
        });
    });

    return {
        toggle: function(pickerId, targetId) {
            var popup = document.getElementById('mgEmoticonPopup_' + pickerId);
            if (!popup) return;

            _currentTarget = targetId;

            if (popup.style.display !== 'none') {
                popup.style.display = 'none';
                return;
            }

            popup.style.display = 'flex';

            // position: fixed 기반 위치 계산
            var btn = popup.parentElement.querySelector('.mg-emoticon-btn');
            if (btn) {
                var rect = btn.getBoundingClientRect();
                var popH = 360;
                var spaceAbove = rect.top;
                var spaceBelow = window.innerHeight - rect.bottom;

                if (spaceAbove >= popH || spaceAbove > spaceBelow) {
                    // 위로 열기
                    popup.style.bottom = (window.innerHeight - rect.top + 4) + 'px';
                    popup.style.top = 'auto';
                } else {
                    // 아래로 열기
                    popup.style.top = (rect.bottom + 4) + 'px';
                    popup.style.bottom = 'auto';
                }

                // 오른쪽 정렬 (화면 밖으로 안 나가게)
                var rightPos = window.innerWidth - rect.right;
                if (rightPos < 0) rightPos = 8;
                popup.style.right = rightPos + 'px';
                popup.style.left = 'auto';
            }

            _loadEmoticons(function(data) {
                _renderTabs(pickerId, data);
                _renderGrid(pickerId, data, 0);
            });
        },

        close: function(pickerId) {
            var popup = document.getElementById('mgEmoticonPopup_' + pickerId);
            if (popup) popup.style.display = 'none';
        },

        switchTab: function(pickerId, tabIndex) {
            // Update tab active state
            var tabs = document.querySelectorAll('#mgEmoticonTabs_' + pickerId + ' .mg-emoticon-tab');
            tabs.forEach(function(tab, idx) {
                tab.classList.toggle('active', idx === tabIndex);
            });

            _renderGrid(pickerId, _cache, tabIndex);
        },

        insert: function(code, imageUrl) {
            if (!_currentTarget) return;

            // Toast UI Editor 지원 — 이미지로 직접 삽입
            if (typeof toastEditors !== 'undefined' && toastEditors[_currentTarget]) {
                var editor = toastEditors[_currentTarget];
                if (imageUrl) {
                    editor.exec('addImage', { imageUrl: imageUrl, altText: code });
                } else {
                    editor.insertText(' ' + code + ' ');
                }
                document.querySelectorAll('.mg-emoticon-popup').forEach(function(p) {
                    p.style.display = 'none';
                });
                return;
            }

            var textarea = document.getElementById(_currentTarget) ||
                           document.querySelector('[name="' + _currentTarget + '"]');

            // SmartEditor2 iframe 지원: textarea가 없거나 hidden이면 SE2 iframe에 삽입
            if (!textarea || (textarea.offsetParent === null && textarea.tagName === 'TEXTAREA')) {
                var iframe = document.querySelector('#' + _currentTarget + '_ifr, .se2_inputarea iframe');
                if (iframe && iframe.contentDocument) {
                    var doc = iframe.contentDocument;
                    var sel = doc.getSelection ? doc.getSelection() : null;
                    var textNode = doc.createTextNode(' ' + code + ' ');
                    if (sel && sel.rangeCount > 0) {
                        var range = sel.getRangeAt(0);
                        range.deleteContents();
                        range.insertNode(textNode);
                        range.setStartAfter(textNode);
                        range.collapse(true);
                        sel.removeAllRanges();
                        sel.addRange(range);
                    } else {
                        doc.body.appendChild(textNode);
                    }
                    // Close all popups
                    document.querySelectorAll('.mg-emoticon-popup').forEach(function(p) {
                        p.style.display = 'none';
                    });
                    return;
                }
            }

            if (!textarea) return;

            var start = textarea.selectionStart;
            var end = textarea.selectionEnd;
            var text = textarea.value;

            // 앞뒤에 공백 추가 (필요시)
            var before = text.substring(0, start);
            var after = text.substring(end);
            var insertText = code;
            if (before.length > 0 && before.slice(-1) !== ' ' && before.slice(-1) !== '\n') {
                insertText = ' ' + insertText;
            }
            if (after.length > 0 && after[0] !== ' ' && after[0] !== '\n') {
                insertText = insertText + ' ';
            }

            textarea.value = before + insertText + after;
            textarea.focus();
            textarea.selectionStart = textarea.selectionEnd = start + insertText.length;

            // Close all popups
            document.querySelectorAll('.mg-emoticon-popup').forEach(function(p) {
                p.style.display = 'none';
            });
        },

        // Toast UI Editor 툴바 내 이모티콘 피커
        toggleInToolbar: function(editorId, anchorBtn) {
            _currentTarget = editorId;

            var popupId = 'mgEmoticonToolbar_' + editorId;
            var tbPickerId = 'tb_' + editorId;
            var popup = document.getElementById(popupId);

            if (!popup) {
                popup = document.createElement('div');
                popup.id = popupId;
                popup.className = 'mg-emoticon-popup';
                popup.style.display = 'none';
                popup.innerHTML =
                    '<div class="mg-emoticon-popup-header">' +
                        '<span>이모티콘</span>' +
                        '<button type="button" class="mg-emoticon-popup-close">&times;</button>' +
                    '</div>' +
                    '<div class="mg-emoticon-popup-tabs" id="mgEmoticonTabs_' + tbPickerId + '"></div>' +
                    '<div class="mg-emoticon-popup-grid" id="mgEmoticonGrid_' + tbPickerId + '">' +
                        '<div class="mg-emoticon-popup-empty">보유한 이모티콘이 없습니다.</div>' +
                    '</div>' +
                    '<div class="mg-emoticon-popup-footer">' +
                        '<a href="' + _getApiUrl() + '/shop.php?tab=emoticon">이모티콘 상점</a>' +
                    '</div>';
                document.body.appendChild(popup);

                popup.querySelector('.mg-emoticon-popup-close').addEventListener('click', function() {
                    popup.style.display = 'none';
                });
                popup.addEventListener('click', function(e) { e.stopPropagation(); });
            }

            if (popup.style.display === 'flex') {
                popup.style.display = 'none';
                return;
            }

            // Close other open popups
            document.querySelectorAll('.mg-emoticon-popup').forEach(function(p) {
                p.style.display = 'none';
            });

            popup.style.display = 'flex';

            // Position below the toolbar button
            var rect = anchorBtn.getBoundingClientRect();
            var popW = 320;
            popup.style.top = (rect.bottom + 4) + 'px';
            popup.style.bottom = 'auto';

            if (rect.right - popW < 0) {
                popup.style.left = '8px';
                popup.style.right = 'auto';
            } else {
                popup.style.right = (window.innerWidth - rect.right) + 'px';
                popup.style.left = 'auto';
            }

            _loadEmoticons(function(data) {
                _renderTabs(tbPickerId, data);
                _renderGrid(tbPickerId, data, 0);
            });
        },

        // Invalidate cache (e.g., after purchasing)
        clearCache: function() {
            _cache = null;
            _loaded = false;
        }
    };
})();
