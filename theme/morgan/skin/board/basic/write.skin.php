<?php
/**
 * Morgan Edition - Board Write Skin (Basic)
 *
 * 변수는 bbs/write.php에서 준비됨:
 * $w, $wr_id, $bo_table, $subject, $content, $html_editor, $editor_js
 * $is_notice, $is_html, $is_secret, $is_mail, $is_name, $is_password, $is_email
 * $is_category, $category_option, $is_link, $is_file, $file_count, $file
 * $html_editor_head_script, $html_editor_tail_script, $action_url, $list_href
 * $mg_characters, $mg_selected_ch_id, $_mg_br_mode, $_mg_reward_types, $_mg_matched_concierges
 */

if (!defined('_GNUBOARD_')) exit;

$is_edit = $w === 'u';
$form_title = $is_edit ? '글 수정' : '글쓰기';
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

            <!-- 카테고리 -->
            <?php if ($is_category) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">카테고리</label>
                <select name="ca_name" class="input">
                    <?php echo $category_option; ?>
                </select>
            </div>
            <?php } ?>

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

            <!-- 이메일 -->
            <?php if ($is_email) { ?>
            <div class="mb-4">
                <label for="wr_email" class="block text-sm font-medium text-mg-text-secondary mb-2">이메일</label>
                <input type="email" name="wr_email" id="wr_email" value="<?php echo $email; ?>" class="input">
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
                <p class="text-xs text-mg-text-muted mt-1">이 게시물을 작성할 캐릭터를 선택하세요.</p>
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
                <?php if ($is_member) {
                    $picker_id = 'write';
                    $picker_target = 'wr_content';
                    include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
                } ?>
            </div>

            <!-- 파일첨부 -->
            <?php if ($is_file) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">파일첨부</label>
                <?php for ($i = 0; $i < $file_count; $i++) { ?>
                <div class="mb-2">
                    <?php if ($is_edit && isset($file[$i]['source'])) { ?>
                    <div class="flex items-center gap-2 mb-1 text-sm text-mg-text-muted">
                        <span><?php echo $file[$i]['source']; ?></span>
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="checkbox" name="bf_file_del<?php echo $i; ?>" value="1" class="w-4 h-4">
                            <span class="text-mg-error">삭제</span>
                        </label>
                    </div>
                    <?php } ?>
                    <input type="file" name="bf_file[]" class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-mg-bg-tertiary file:text-mg-text-primary hover:file:bg-mg-accent/20">
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 비밀글 -->
            <?php if ($is_secret) { ?>
            <div class="mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="secret" value="secret" <?php echo $secret_checked; ?> class="w-4 h-4 rounded">
                    <span class="text-sm text-mg-text-secondary">비밀글</span>
                </label>
            </div>
            <?php } ?>

            <!-- 의뢰 연결 -->
            <?php if (!$is_edit && !empty($_mg_matched_concierges)) { ?>
            <div class="mb-4 p-3 bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary">
                <label class="block text-sm font-medium text-mg-text-primary mb-1">의뢰 연결 (선택)</label>
                <select name="mg_concierge_id" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                    <option value="0">연결하지 않음</option>
                    <?php foreach ($_mg_matched_concierges as $_mc) {
                        $type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
                        $_mc_type = isset($type_labels[$_mc['cc_type']]) ? $type_labels[$_mc['cc_type']] : '';
                    ?>
                    <option value="<?php echo $_mc['cc_id']; ?>">[<?php echo $_mc_type; ?>] <?php echo htmlspecialchars($_mc['cc_title']); ?><?php if (isset($_mc['is_owner'])) echo ' (내 의뢰)'; ?></option>
                    <?php } ?>
                </select>
                <p class="text-xs text-mg-text-muted mt-1">이 글을 의뢰 결과물로 연결하면 의뢰가 완료 처리됩니다.</p>
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

    // 토큰 발급 후 submit
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
