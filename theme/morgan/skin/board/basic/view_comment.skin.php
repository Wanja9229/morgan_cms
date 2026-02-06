<?php
/**
 * Morgan Edition - Comment Skin
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 댓글에 연결된 캐릭터 정보 미리 로드
$mg_cmt_chars = array();
if (!empty($list)) {
    $cmt_wr_ids = array();
    foreach ($list as $row) {
        $cmt_wr_ids[] = (int)$row['wr_id'];
    }
    if (count($cmt_wr_ids) > 0) {
        global $g5;
        $sql = "SELECT wc.wr_id, c.ch_id, c.ch_name, c.ch_thumb
                FROM {$g5['mg_write_character_table']} wc
                JOIN {$g5['mg_character_table']} c ON wc.ch_id = c.ch_id
                WHERE wc.bo_table = '".sql_real_escape_string($bo_table)."'
                AND wc.wr_id IN (".implode(',', $cmt_wr_ids).")";
        $result = sql_query($sql);
        while ($row_c = sql_fetch_array($result)) {
            $mg_cmt_chars[$row_c['wr_id']] = $row_c;
        }
    }
}

// 로그인 회원의 사용 가능한 캐릭터 목록
$mg_cmt_my_chars = array();
$mg_cmt_default_ch_id = 0;
if ($is_member) {
    $mg_cmt_my_chars = mg_get_usable_characters($member['mb_id']);
    // 대표 캐릭터를 기본 선택
    foreach ($mg_cmt_my_chars as $ch) {
        if ($ch['ch_main']) {
            $mg_cmt_default_ch_id = $ch['ch_id'];
            break;
        }
    }
}
?>

<section id="bo_vc" class="card">
    <h2 class="text-lg font-semibold text-mg-text-primary mb-4">
        댓글 <span class="text-mg-accent"><?php echo $comment_count; ?></span>
    </h2>

    <!-- 댓글 목록 -->
    <?php if (!empty($list)) { ?>
    <div id="cmt_list" class="divide-y divide-mg-bg-tertiary mb-6">
        <?php foreach ($list as $i => $row) { ?>
        <div id="<?php echo $comment_id; ?>_<?php echo $row['wr_id']; ?>" class="py-4 <?php echo $row['is_reply'] ? 'pl-8' : ''; ?>">
            <!-- 댓글 헤더 -->
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <?php if ($row['is_reply']) { ?>
                    <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                    <?php } ?>
                    <?php
                    $cmt_char = isset($mg_cmt_chars[$row['wr_id']]) ? $mg_cmt_chars[$row['wr_id']] : null;
                    if ($cmt_char) {
                    ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $cmt_char['ch_id']; ?>" class="flex items-center gap-1.5 hover:opacity-80 transition-opacity">
                        <?php if ($cmt_char['ch_thumb']) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$cmt_char['ch_thumb']; ?>" alt="" class="w-6 h-6 rounded-full object-cover border border-mg-accent">
                        <?php } else { ?>
                        <span class="w-6 h-6 rounded-full bg-mg-accent/20 flex items-center justify-center text-xs font-bold text-mg-accent"><?php echo mb_substr($cmt_char['ch_name'], 0, 1); ?></span>
                        <?php } ?>
                        <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($cmt_char['ch_name']); ?></span>
                    </a>
                    <span class="text-xs text-mg-text-muted">@<?php echo $row['name']; ?></span>
                    <?php } else { ?>
                    <span class="font-medium text-mg-text-primary"><?php echo $row['name']; ?></span>
                    <?php } ?>
                    <span class="text-xs text-mg-text-muted"><?php echo $row['datetime2']; ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($row['is_reply_write']) { ?>
                    <button type="button" onclick="comment_reply('<?php echo $row['wr_id']; ?>');" class="text-xs text-mg-text-muted hover:text-mg-text-primary">답글</button>
                    <?php } ?>
                    <?php if ($row['is_edit']) { ?>
                    <button type="button" onclick="comment_edit('<?php echo $row['wr_id']; ?>');" class="text-xs text-mg-text-muted hover:text-mg-text-primary">수정</button>
                    <?php } ?>
                    <?php if ($row['is_del']) { ?>
                    <a href="<?php echo $row['del_href']; ?>" onclick="return confirm('댓글을 삭제하시겠습니까?');" class="text-xs text-mg-text-muted hover:text-mg-error">삭제</a>
                    <?php } ?>
                </div>
            </div>

            <!-- 댓글 내용 -->
            <div id="cmt_txt_<?php echo $row['wr_id']; ?>" class="text-sm text-mg-text-secondary">
                <?php if ($row['is_secret']) { ?>
                <span class="text-mg-warning">비밀 댓글입니다.</span>
                <?php } else { ?>
                <?php echo mg_render_emoticons($row['content']); ?>
                <?php } ?>
            </div>

            <!-- 수정/답글 폼 영역 -->
            <div id="cmt_form_<?php echo $row['wr_id']; ?>"></div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 댓글 작성 폼 -->
    <?php if ($is_comment_write) { ?>
    <form name="fcomment" id="fcomment" action="<?php echo $comment_action_url; ?>" method="post" onsubmit="return fcomment_submit(this);">
        <input type="hidden" name="w" value="">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
        <input type="hidden" name="comment_id" value="">
        <input type="hidden" name="token" value="">

        <?php if (!$is_member) { ?>
        <div class="flex gap-2 mb-3">
            <input type="text" name="wr_name" value="<?php echo $name; ?>" class="input w-32" placeholder="이름" required>
            <input type="password" name="wr_password" class="input w-32" placeholder="비밀번호" required>
        </div>
        <?php } elseif (count($mg_cmt_my_chars) > 0) { ?>
        <!-- 캐릭터 선택 (드롭다운) -->
        <div class="mb-3">
            <select name="mg_ch_id" class="input w-auto text-sm">
                <option value="0">캐릭터 없이 작성</option>
                <?php foreach ($mg_cmt_my_chars as $ch) { ?>
                <option value="<?php echo $ch['ch_id']; ?>" <?php echo $ch['ch_id'] == $mg_cmt_default_ch_id ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ch['ch_name']); ?><?php echo $ch['ch_main'] ? ' (대표)' : ''; ?>
                </option>
                <?php } ?>
            </select>
        </div>
        <?php } ?>

        <div class="flex gap-2">
            <div class="flex-1 relative">
                <textarea name="wr_content" id="wr_content" rows="3" class="input w-full resize-none" placeholder="댓글을 입력하세요" required></textarea>
                <?php if ($is_member) {
                    $picker_id = 'comment';
                    $picker_target = 'wr_content';
                    include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
                } ?>
            </div>
            <button type="submit" class="btn btn-primary self-end">등록</button>
        </div>

        <?php if ($is_secret) { ?>
        <label class="flex items-center gap-2 mt-2 cursor-pointer">
            <input type="checkbox" name="wr_secret" value="secret" class="w-4 h-4 rounded">
            <span class="text-sm text-mg-text-secondary">비밀 댓글</span>
        </label>
        <?php } ?>
    </form>
    <?php } else { ?>
    <p class="text-center text-mg-text-muted py-4">댓글을 작성하려면 로그인이 필요합니다.</p>
    <?php } ?>
</section>

<script>
function fcomment_submit(f) {
    if (!f.wr_content.value.trim()) {
        alert('댓글 내용을 입력해주세요.');
        f.wr_content.focus();
        return false;
    }
    return true;
}

function comment_reply(cmt_id) {
    var el = document.getElementById('cmt_form_' + cmt_id);
    if (el.innerHTML) {
        el.innerHTML = '';
        return;
    }

    var f = document.fcomment;
    var html = '<div class="mt-3 p-3 bg-mg-bg-primary rounded">';
    html += '<form name="fcommentreply" action="' + f.action + '" method="post" onsubmit="return fcomment_submit(this);">';
    html += '<input type="hidden" name="w" value="">';
    html += '<input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">';
    html += '<input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">';
    html += '<input type="hidden" name="comment_id" value="' + cmt_id + '">';
    html += '<input type="hidden" name="token" value="">';
    <?php if (!$is_member) { ?>
    html += '<div class="flex gap-2 mb-2">';
    html += '<input type="text" name="wr_name" class="input w-24 text-sm" placeholder="이름" required>';
    html += '<input type="password" name="wr_password" class="input w-24 text-sm" placeholder="비밀번호" required>';
    html += '</div>';
    <?php } elseif (count($mg_cmt_my_chars) > 0) { ?>
    html += '<div class="mb-2">';
    html += '<select name="mg_ch_id" class="input w-auto text-sm">';
    html += '<option value="0">캐릭터 없이 작성</option>';
    <?php foreach ($mg_cmt_my_chars as $ch) { ?>
    html += '<option value="<?php echo $ch['ch_id']; ?>"<?php echo $ch['ch_id'] == $mg_cmt_default_ch_id ? ' selected' : ''; ?>><?php echo addslashes(htmlspecialchars($ch['ch_name'])); ?><?php echo $ch['ch_main'] ? ' (대표)' : ''; ?></option>';
    <?php } ?>
    html += '</select>';
    html += '</div>';
    <?php } ?>
    html += '<div class="flex gap-2">';
    html += '<textarea name="wr_content" rows="2" class="input flex-1 text-sm resize-none" placeholder="답글을 입력하세요" required></textarea>';
    html += '<button type="submit" class="btn btn-primary text-sm self-end">등록</button>';
    html += '</div></form></div>';

    el.innerHTML = html;
    el.querySelector('textarea').focus();
}

function comment_edit(cmt_id) {
    // TODO: AJAX로 댓글 수정 폼 로드
    alert('수정 기능은 개발 중입니다.');
}
</script>
