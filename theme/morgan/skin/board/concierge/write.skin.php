<?php
/**
 * Morgan Edition - Board Write Skin (Concierge Result)
 * 의뢰 수행 결과물 전용 게시판 글쓰기
 */

if (!defined('_GNUBOARD_')) exit;

$is_edit = $w === 'u';
$form_title = $is_edit ? '결과물 수정' : '결과물 등록';

// 의뢰 pre-select
$_preselect_cc_id = isset($_GET['mg_concierge_id']) ? (int)$_GET['mg_concierge_id'] : 0;
?>

<div id="bo_write" class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6"><?php echo $form_title; ?></h2>

        <?php if (!$is_edit && empty($_mg_matched_concierges)) { ?>
        <div class="text-center py-8">
            <p class="text-mg-text-muted mb-3">연결할 수 있는 의뢰가 없습니다.</p>
            <p class="text-sm text-mg-text-muted">매칭 완료된 의뢰가 있어야 결과물을 등록할 수 있습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/concierge.php" class="inline-block mt-4 px-4 py-2 bg-mg-bg-tertiary text-mg-text-secondary rounded-lg text-sm hover:bg-mg-accent/20 transition-colors">의뢰 목록으로</a>
        </div>
        <?php } else { ?>

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

            <!-- 의뢰 선택 (필수) -->
            <?php if (!$is_edit) { ?>
            <div class="mb-4 p-3 bg-mg-bg-primary rounded-lg border border-mg-accent/30">
                <label class="block text-sm font-medium text-mg-accent mb-1">의뢰 연결 <span class="text-mg-error">*</span></label>
                <select name="mg_concierge_id" id="mg_concierge_id" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm" required>
                    <option value="">의뢰를 선택하세요</option>
                    <?php foreach ($_mg_matched_concierges as $_mc) {
                        $type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
                        $_mc_type = isset($type_labels[$_mc['cc_type']]) ? $type_labels[$_mc['cc_type']] : '';
                        $_selected = ($_preselect_cc_id == $_mc['cc_id']) ? ' selected' : '';
                    ?>
                    <option value="<?php echo $_mc['cc_id']; ?>"<?php echo $_selected; ?>>[<?php echo $_mc_type; ?>] <?php echo htmlspecialchars($_mc['cc_title']); ?><?php if (isset($_mc['is_owner'])) echo ' (내 의뢰)'; ?></option>
                    <?php } ?>
                </select>
                <p class="text-xs text-mg-text-muted mt-1">이 글이 연결될 의뢰를 선택하세요. 등록 시 의뢰가 완료 처리됩니다.</p>
            </div>
            <?php } ?>

            <!-- 캐릭터 선택 -->
            <?php if ($is_member && count($mg_characters) > 0) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">캐릭터 선택</label>
                <div class="flex flex-wrap gap-2" id="mg-character-selector">
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
                        </div>
                    </label>
                    <?php } ?>
                </div>
            </div>
            <?php } elseif ($is_member) { ?>
            <input type="hidden" name="mg_ch_id" value="0">
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

            <!-- 버튼 -->
            <div class="flex items-center justify-between">
                <a href="<?php echo $list_href; ?>" class="btn btn-secondary">취소</a>
                <button type="submit" id="btn_submit" class="btn btn-primary">
                    <?php echo $is_edit ? '수정하기' : '등록하기'; ?>
                </button>
            </div>
        </form>
        <?php } ?>
    </div>
</div>

<?php echo $html_editor_tail_script; ?>

<script>
var _fwrite_submitting = false;
function fwrite_submit(f) {
    if (_fwrite_submitting) return true;

    <?php if (!$is_edit) { ?>
    var cc_sel = document.getElementById('mg_concierge_id');
    if (cc_sel && !cc_sel.value) {
        alert('연결할 의뢰를 선택해주세요.');
        cc_sel.focus();
        return false;
    }
    <?php } ?>

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
