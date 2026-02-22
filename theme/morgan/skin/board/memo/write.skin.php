<?php
/**
 * Morgan Edition - Memo Board Write Skin
 *
 * 간소화된 메모 작성 폼 (캐릭터 선택, 제목, 내용, 비밀글)
 * 변수는 bbs/write.php에서 준비됨
 */

if (!defined('_GNUBOARD_')) exit;

$is_edit = $w === 'u';
$form_title = $is_edit ? '메모 수정' : '메모 쓰기';
?>

<div id="bo_write" class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6"><?php echo $form_title; ?></h2>

        <form name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" method="post" enctype="multipart/form-data" onsubmit="return fwrite_submit(this);" autocomplete="off">
            <input type="hidden" name="w" value="<?php echo $w; ?>">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
            <input type="hidden" name="sca" value="<?php echo $sca; ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
            <input type="hidden" name="stx" value="<?php echo $stx; ?>">
            <input type="hidden" name="spt" value="<?php echo $spt; ?>">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="token" value="">
            <input type="hidden" name="html" value="html1">
            <?php echo $html_editor_head_script; ?>

            <!-- 이름 (비회원) -->
            <?php if ($is_name) { ?>
            <div class="mb-4">
                <label for="wr_name" class="block text-sm font-medium text-mg-text-secondary mb-2">이름 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_name" id="wr_name" value="<?php echo $name; ?>" class="input" required>
            </div>
            <?php } ?>

            <!-- 비밀번호 (비회원) -->
            <?php if ($is_password) { ?>
            <div class="mb-4">
                <label for="wr_password" class="block text-sm font-medium text-mg-text-secondary mb-2">비밀번호 <span class="text-mg-error">*</span></label>
                <input type="password" name="wr_password" id="wr_password" class="input" <?php echo $is_edit ? '' : 'required'; ?>>
            </div>
            <?php } ?>

            <!-- 캐릭터 선택 (회원 전용) -->
            <?php if ($is_member && count($mg_characters) > 0) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">캐릭터 선택</label>
                <div class="flex flex-wrap gap-2" id="mg-character-selector">
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="mg_ch_id" value="0" <?php echo $mg_selected_ch_id == 0 ? 'checked' : ''; ?> class="hidden">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent transition-colors character-badge">
                            <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
                                <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-mg-text-secondary">선택 안함</span>
                        </div>
                    </label>
                    <?php foreach ($mg_characters as $ch) { ?>
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="mg_ch_id" value="<?php echo $ch['ch_id']; ?>" <?php echo $mg_selected_ch_id == $ch['ch_id'] ? 'checked' : ''; ?> class="hidden">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent transition-colors character-badge">
                            <?php if ($ch['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                            <?php } else { ?>
                            <div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center">
                                <span class="text-xs font-bold text-mg-accent"><?php echo mb_substr($ch['ch_name'], 0, 1); ?></span>
                            </div>
                            <?php } ?>
                            <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($ch['ch_name']); ?></span>
                            <?php if ($ch['ch_main']) { ?>
                            <span class="text-xs bg-mg-accent text-white px-1.5 py-0.5 rounded">대표</span>
                            <?php } ?>
                        </div>
                    </label>
                    <?php } ?>
                </div>
                <p class="text-xs text-mg-text-muted mt-1">이 메모를 작성할 캐릭터를 선택하세요.</p>
            </div>
            <?php } elseif ($is_member) { ?>
            <input type="hidden" name="mg_ch_id" value="0">
            <?php } ?>

            <!-- 보상 유형 (request 모드) -->
            <?php if ($_mg_br_mode === 'request' && !empty($_mg_reward_types)) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">보상 요청</label>
                <select name="reward_type" class="input">
                    <option value="">보상 요청 안 함</option>
                    <?php foreach ($_mg_reward_types as $rwt) { ?>
                    <option value="<?php echo $rwt['rwt_id']; ?>">
                        <?php echo htmlspecialchars($rwt['rwt_name']); ?> - <?php echo number_format($rwt['rwt_point']); ?>P
                        <?php if ($rwt['rwt_desc']) echo '(' . htmlspecialchars($rwt['rwt_desc']) . ')'; ?>
                    </option>
                    <?php } ?>
                </select>
                <p class="text-xs text-mg-text-muted mt-1">보상을 요청하면 관리자 검토 후 지급됩니다.</p>
            </div>
            <?php } ?>

            <!-- 제목 -->
            <div class="mb-4">
                <label for="wr_subject" class="block text-sm font-medium text-mg-text-secondary mb-2">제목 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" class="input" required>
            </div>

            <!-- 내용 -->
            <div class="mb-4">
                <label for="wr_content" class="block text-sm font-medium text-mg-text-secondary mb-2">내용 <span class="text-mg-error">*</span></label>
                <?php echo $html_editor; ?>
            </div>

            <!-- 비밀글 -->
            <?php if ($is_secret) { ?>
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="secret" value="secret" <?php echo $secret_checked; ?> class="w-4 h-4 rounded">
                    <span class="text-sm text-mg-text-secondary">비밀글</span>
                </label>
            </div>
            <?php } ?>

            <!-- 버튼 -->
            <div class="flex items-center justify-between">
                <a href="<?php echo $list_href; ?>" class="btn btn-secondary">취소</a>
                <button type="submit" id="btn_submit" class="btn btn-primary">
                    <?php echo $is_edit ? '수정하기' : '작성하기'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $html_editor_tail_script; ?>

<script>
var _fwrite_submitting = false;
function fwrite_submit(f) {
    if (_fwrite_submitting) return true;

    if (!f.wr_subject.value.trim()) {
        alert('제목을 입력해주세요.');
        f.wr_subject.focus();
        return false;
    }

    <?php echo $editor_js; ?>

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/write_token.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.error) { alert(data.error); return; }
            f.token.value = data.token;
            _fwrite_submitting = true;
            f.submit();
        } catch(e) { alert('토큰 발급 오류'); }
    };
    xhr.onerror = function() { alert('토큰 발급 네트워크 오류'); };
    xhr.send('bo_table=<?php echo $bo_table; ?>');
    return false;
}

// 캐릭터 선택기 UI 업데이트
document.querySelectorAll('.character-option input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.character-badge').forEach(function(badge) {
            badge.classList.remove('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
            badge.classList.add('border-mg-bg-tertiary');
        });
        if (this.checked) {
            var badge = this.parentElement.querySelector('.character-badge');
            badge.classList.remove('border-mg-bg-tertiary');
            badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
        }
    });
    if (radio.checked) {
        var badge = radio.parentElement.querySelector('.character-badge');
        badge.classList.remove('border-mg-bg-tertiary');
        badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
    }
});
</script>
