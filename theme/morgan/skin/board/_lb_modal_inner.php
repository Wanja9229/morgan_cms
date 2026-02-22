<?php
/**
 * 로드비 스킨 공통 모달 내부 (캐릭터 선택 + 에디터)
 *
 * 필요 변수: $bo_table, $sca, $sfl, $stx, $spt, $page,
 *            $lb_characters, $lb_main_ch_id, $is_member
 */
if (!defined('_GNUBOARD_')) exit;
?>
<!-- 캐릭터 선택 -->
<?php if ($is_member && count($lb_characters) > 0) { ?>
<div class="lb-char-selector">
    <label class="lb-char-label">캐릭터</label>
    <div class="lb-char-list">
        <label class="lb-char-option">
            <input type="radio" name="mg_ch_id" value="0" <?php echo $lb_main_ch_id == 0 ? 'checked' : ''; ?>>
            <span class="lb-char-badge">
                <span class="lb-char-icon">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </span>
                <span>선택 안함</span>
            </span>
        </label>
        <?php foreach ($lb_characters as $ch) { ?>
        <label class="lb-char-option">
            <input type="radio" name="mg_ch_id" value="<?php echo $ch['ch_id']; ?>" <?php echo $lb_main_ch_id == $ch['ch_id'] ? 'checked' : ''; ?>>
            <span class="lb-char-badge">
                <?php if ($ch['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb']; ?>" alt="" class="lb-char-thumb">
                <?php } else { ?>
                <span class="lb-char-initial"><?php echo mb_substr($ch['ch_name'], 0, 1); ?></span>
                <?php } ?>
                <span><?php echo htmlspecialchars($ch['ch_name']); ?></span>
                <?php if ($ch['ch_main']) { ?><span class="lb-char-main">대표</span><?php } ?>
            </span>
        </label>
        <?php } ?>
    </div>
</div>
<?php } elseif ($is_member) { ?>
<input type="hidden" name="mg_ch_id" value="0">
<?php } ?>

<!-- 제목 -->
<div class="lb-field">
    <label class="lb-field-label" for="lb_wr_subject">제목</label>
    <input type="text" name="wr_subject" id="lb_wr_subject" placeholder="제목을 입력하세요" required>
</div>

<!-- 에디터 -->
<div class="lb-field">
    <label class="lb-field-label">내용</label>
    <div id="lb_editor_wrap"></div>
    <textarea name="wr_content" id="lb_wr_content" style="display:none"></textarea>
</div>
