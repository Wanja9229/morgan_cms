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
                                <i data-lucide="user" class="w-4 h-4 text-mg-text-muted"></i>
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

            <!-- 관계 로그 대상 선택 (rellog 게시판 + 신규 작성) -->
            <?php
            $_rellog_board = function_exists('mg_config') ? mg_config('relation_log_board', 'rellog') : 'rellog';
            if ($is_member && $bo_table === $_rellog_board && !$is_edit) {
                $_rellog_relations = array();
                if (count($mg_characters) > 0) {
                    $ch_ids = array();
                    foreach ($mg_characters as $ch) { $ch_ids[] = (int)$ch['ch_id']; }
                    $ch_ids_str = implode(',', $ch_ids);
                    $sql = "SELECT r.cr_id, r.ch_id_a, r.ch_id_b, r.ch_id_from, r.cr_label_a, r.cr_label_b, r.cr_wr_id_a, r.cr_wr_id_b,
                                   ca.ch_name AS name_a, cb.ch_name AS name_b
                            FROM {$g5['mg_relation_table']} r
                            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
                            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
                            WHERE r.cr_status = 'accepted'
                            AND (r.ch_id_a IN ({$ch_ids_str}) OR r.ch_id_b IN ({$ch_ids_str}))
                            ORDER BY r.cr_id DESC";
                    $result = sql_query($sql);
                    if ($result) {
                        while ($row = sql_fetch_array($result)) {
                            $_rellog_relations[] = $row;
                        }
                    }
                }
                if (!empty($_rellog_relations)) {
            ?>
            <div class="mb-4" id="mg-relation-selector">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">관계 로그 대상</label>
                <?php $_cr_id_preselect = isset($_GET['cr_id']) ? (int)$_GET['cr_id'] : 0; ?>
                <select name="mg_cr_id" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary">
                    <?php foreach ($_rellog_relations as $rel) {
                        // 내 캐릭터 쪽 판별
                        $my_side = in_array($rel['ch_id_a'], $ch_ids) ? 'a' : 'b';
                        $my_ch_id_rel = ($my_side === 'a') ? $rel['ch_id_a'] : $rel['ch_id_b'];
                        $my_name = ($my_side === 'a') ? $rel['name_a'] : $rel['name_b'];
                        $other_name = ($my_side === 'a') ? $rel['name_b'] : $rel['name_a'];
                        $my_label = ($my_side === 'a') ? $rel['cr_label_a'] : $rel['cr_label_b'];
                        $wr_col = ($my_side === 'a') ? 'cr_wr_id_a' : 'cr_wr_id_b';
                        $already = !empty($rel[$wr_col]);
                    ?>
                    <option value="<?php echo $rel['cr_id']; ?>" data-ch-id="<?php echo $my_ch_id_rel; ?>" <?php echo $already ? 'disabled' : ''; ?> <?php echo (!$already && $rel['cr_id'] == $_cr_id_preselect) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($my_name); ?> ↔ <?php echo htmlspecialchars($other_name); ?> (<?php echo htmlspecialchars($my_label); ?>)<?php echo $already ? ' — 제출 완료' : ''; ?>
                    </option>
                    <?php } ?>
                </select>
                <p class="text-xs text-mg-text-muted mt-1">로그를 제출할 관계를 선택하세요.</p>
            </div>
            <script>
            // 관계 선택 시 해당 캐릭터 자동 선택
            document.addEventListener('DOMContentLoaded', function() {
                var crSelect = document.querySelector('select[name="mg_cr_id"]');
                if (crSelect) {
                    function syncCharacter() {
                        var opt = crSelect.options[crSelect.selectedIndex];
                        if (opt && opt.dataset.chId) {
                            var radio = document.querySelector('input[name="mg_ch_id"][value="' + opt.dataset.chId + '"]');
                            if (radio) { radio.checked = true; radio.dispatchEvent(new Event('change', {bubbles:true})); }
                        }
                    }
                    crSelect.addEventListener('change', syncCharacter);
                    syncCharacter();
                }
            });
            </script>
            <?php
                }
            }
            ?>

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
        mgToast('제목을 입력해주세요.', 'warning');
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
            if (data.error) { mgToast(data.error, 'error'); return; }
            f.token.value = data.token;
            _fwrite_submitting = true;
            f.submit();
        } catch(e) { mgToast('토큰 발급 오류', 'error'); }
    };
    xhr.onerror = function() { mgToast('토큰 발급 네트워크 오류', 'error'); };
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
