<?php
/**
 * 로드비 스킨 공통 에디터 CDN + 캐릭터 선택 CSS + JS
 *
 * list.skin.php에서 </div> 닫기 전에 include
 * 필요 변수: $lb_upload_url, $lb_ed_nonce
 */
if (!defined('_GNUBOARD_')) exit;
?>

<!-- Toast UI Editor CDN -->
<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css">
<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/theme/toastui-editor-dark.min.css">
<link rel="stylesheet" href="<?php echo G5_EDITOR_URL.'/'.$config['cf_editor']; ?>/morgan-dark.css">
<script src="https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js"></script>
<script>if(typeof toastEditors==='undefined') var toastEditors = {}; if(typeof ed_nonce==='undefined') var ed_nonce = '<?php echo $lb_ed_nonce; ?>';</script>

<style>
/* ── 로드비 공통: 캐릭터 선택 + 에디터 모달 ── */
.lb-char-selector { margin-bottom: 14px; }
.lb-char-label, .lb-field-label {
    display: block; font-size: 0.75rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
    color: var(--mg-text-muted, #949ba4);
}
.lb-char-list {
    display: flex; flex-wrap: wrap; gap: 6px;
}
.lb-char-option input { display: none; }
.lb-char-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 10px; border-radius: 6px; cursor: pointer;
    border: 1px solid var(--mg-bg-tertiary, #313338);
    background: var(--mg-bg-primary, #1e1f22);
    font-size: 0.8rem; color: var(--mg-text-secondary, #b5bac1);
    transition: border-color 0.15s;
}
.lb-char-option input:checked + .lb-char-badge {
    border-color: var(--mg-accent, #f59f0a);
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--mg-accent, #f59f0a) 25%, transparent);
}
.lb-char-badge:hover { border-color: var(--mg-accent, #f59f0a); }
.lb-char-icon {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--mg-bg-tertiary, #313338);
    display: flex; align-items: center; justify-content: center;
    color: var(--mg-text-muted, #949ba4);
}
.lb-char-thumb {
    width: 22px; height: 22px; border-radius: 50%; object-fit: cover;
}
.lb-char-initial {
    width: 22px; height: 22px; border-radius: 50%;
    background: color-mix(in srgb, var(--mg-accent, #f59f0a) 20%, transparent);
    display: flex; align-items: center; justify-content: center;
    font-size: 0.7rem; font-weight: 700; color: var(--mg-accent, #f59f0a);
}
.lb-char-main {
    font-size: 0.65rem; background: var(--mg-accent, #f59f0a); color: #000;
    padding: 1px 5px; border-radius: 3px; font-weight: 700;
}

.lb-field { margin-bottom: 14px; }
.lb-field input[type="text"] {
    width: 100%; padding: 8px 12px; box-sizing: border-box;
    background: var(--mg-bg-tertiary, #313338);
    border: 1px solid transparent; border-radius: 4px;
    color: var(--mg-text-primary, #f2f3f5);
    font-size: 0.9rem; outline: none;
    transition: border-color 0.15s;
}
.lb-field input[type="text"]:focus { border-color: var(--mg-accent, #f59f0a); }

/* 에디터 컨테이너 높이 */
#lb_editor_wrap { min-height: 300px; }
#lb_editor_wrap .toastui-editor-defaultUI { border-radius: 4px; }

/* 캐릭터 모바일 */
@media screen and (max-width: 768px) {
    .lb-char-list { gap: 4px; }
    .lb-char-badge { font-size: 0.75rem; padding: 4px 8px; }
    .lb-char-thumb, .lb-char-icon, .lb-char-initial { width: 20px; height: 20px; }
}
</style>

<script>
var _lb_bo_table = '<?php echo $bo_table; ?>';
var _lb_api_url = '<?php echo G5_BBS_URL; ?>/lordby_api.php';
var _lb_upload_url = '<?php echo $lb_upload_url; ?>';
var _lb_editor = null;

// 에디터 초기화 (모달 열릴 때 1회)
function lbInitEditor() {
    if (_lb_editor) return;
    var container = document.getElementById('lb_editor_wrap');
    if (!container) return;

    var emotiBtn = document.createElement('button');
    emotiBtn.type = 'button';
    emotiBtn.className = 'toastui-editor-toolbar-icons';
    emotiBtn.style.cssText = 'background-image:none;display:inline-flex;align-items:center;justify-content:center;';
    emotiBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/></svg>';
    emotiBtn.addEventListener('click', function(e) { e.stopPropagation(); if(typeof MgEmoticonPicker!=='undefined') MgEmoticonPicker.toggleInToolbar('lb_wr_content', this); });

    _lb_editor = new toastui.Editor({
        el: container,
        height: '300px',
        initialEditType: 'wysiwyg',
        initialValue: '',
        theme: 'dark',
        usageStatistics: false,
        hideModeSwitch: true,
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol'],
            ['table', 'image', 'link'],
            ['code', 'codeblock', {el: emotiBtn, tooltip: '이모티콘', name: 'emoticon'}]
        ],
        hooks: {
            addImageBlobHook: function(blob, callback) {
                var fd = new FormData();
                fd.append('file', blob);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', _lb_upload_url);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try { var res = JSON.parse(xhr.responseText); if (res.url) callback(res.url, blob.name || 'image'); else alert('이미지 업로드 실패'); }
                        catch(e) { alert('이미지 업로드 오류'); }
                    } else { alert('이미지 업로드 실패 ('+xhr.status+')'); }
                };
                xhr.onerror = function() { alert('네트워크 오류'); };
                xhr.send(fd);
            }
        }
    });
    toastEditors['lb_wr_content'] = _lb_editor;
}

// 모달 열기
function lbOpenWrite() {
    document.getElementById('lb_modal_overlay').classList.remove('hidden');
    document.getElementById('lb_write_modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // 에디터 지연 초기화 (DOM visible 후)
    setTimeout(function() {
        lbInitEditor();
        document.getElementById('lb_wr_subject').focus();
    }, 50);
}

// 모달 닫기
function lbCloseWrite() {
    document.getElementById('lb_modal_overlay').classList.add('hidden');
    document.getElementById('lb_write_modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// 글 등록 (에디터 → textarea 동기화 → 토큰 → submit)
var _lb_submitting = false;
function lbSubmitPost(f) {
    if (_lb_submitting) return true;

    if (!f.wr_subject.value.trim()) { alert('제목을 입력해주세요.'); f.wr_subject.focus(); return false; }

    // 에디터 내용 동기화
    if (_lb_editor) {
        var html = _lb_editor.getHTML();
        if (!html || (html.replace(/<[^>]*>/g,'').trim() === '' && html.indexOf('<img') === -1)) {
            alert('내용을 입력해주세요.');
            _lb_editor.focus();
            return false;
        }
        f.wr_content.value = html;
    } else if (!f.wr_content.value.trim()) {
        alert('내용을 입력해주세요.'); return false;
    }

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/write_token.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.error) { alert(data.error); return; }
            f.token.value = data.token;
            _lb_submitting = true;
            f.submit();
        } catch(e) { alert('토큰 발급 오류'); }
    };
    xhr.onerror = function() { alert('네트워크 오류'); };
    xhr.send('bo_table=' + _lb_bo_table);
    return false;
}

// ESC 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') lbCloseWrite();
});
</script>
