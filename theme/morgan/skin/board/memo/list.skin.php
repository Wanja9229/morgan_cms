<?php
/**
 * Morgan Edition - Memo Board List Skin (Accordion)
 *
 * 아코디언 스타일 메모 목록 - 제목 클릭 시 내용이 인라인으로 펼쳐짐
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$colspan = 4;
if ($is_checkbox) $colspan++;

// 목록의 글에 연결된 캐릭터 정보 미리 로드
$mg_list_chars = array();
if (count($list) > 0) {
    $wr_ids = array();
    foreach ($list as $row) {
        $wr_ids[] = (int)$row['wr_id'];
    }
    if (count($wr_ids) > 0) {
        global $g5;
        $sql = "SELECT wc.wr_id, c.ch_id, c.ch_name, c.ch_thumb
                FROM {$g5['mg_write_character_table']} wc
                JOIN {$g5['mg_character_table']} c ON wc.ch_id = c.ch_id
                WHERE wc.bo_table = '".sql_real_escape_string($bo_table)."'
                AND wc.wr_id IN (".implode(',', $wr_ids).")";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $mg_list_chars[$row['wr_id']] = $row;
        }
    }
}

// 주사위 설정
$mg_dice_enabled = false;
$mg_dice_max = 100;
if (function_exists('mg_get_board_reward')) {
    $mg_br = mg_get_board_reward($bo_table);
    if ($mg_br && $mg_br['br_dice_use']) {
        $mg_dice_enabled = true;
        $mg_dice_max = (int)$mg_br['br_dice_max'] ?: 100;
    }
}
?>

<div id="bo_list" class="mg-inner">

    <!-- 게시판 헤더 -->
    <div class="card mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $board['bo_subject']; ?></h1>
                <p class="text-sm text-mg-text-muted">총 <?php echo number_format($total_count); ?>개의 메모</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($admin_href) { ?>
                <a href="<?php echo $admin_href; ?>" class="btn btn-ghost" title="관리자">
                    <i data-lucide="settings" class="w-5 h-5"></i>
                </a>
                <?php } ?>
                <?php if ($write_href) { ?>
                <a href="<?php echo $write_href; ?>" class="btn btn-primary">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                    메모 쓰기
                </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 카테고리 -->
    <?php if ($is_category) { ?>
    <div class="mb-4 flex flex-wrap gap-2">
        <?php echo $category_option; ?>
    </div>
    <?php } ?>

    <!-- 메모 목록 (아코디언) -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <div class="space-y-2">
            <?php if (count($list) > 0) { ?>
                <?php foreach ($list as $i => $row) { ?>
                <div class="card p-0 overflow-hidden <?php echo $row['is_notice'] ? 'border border-mg-accent/30' : ''; ?>">
                    <!-- 아코디언 헤더 (클릭 영역) -->
                    <div class="flex items-center gap-3 p-4 cursor-pointer hover:bg-mg-bg-tertiary/30 transition-colors select-none" onclick="toggleMemo(<?php echo $row['wr_id']; ?>)">
                        <?php if ($is_checkbox) { ?>
                        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="flex-shrink-0" onclick="event.stopPropagation();">
                        <?php } ?>

                        <!-- 펼침/접힘 아이콘 -->
                        <i id="memo_icon_<?php echo $row['wr_id']; ?>" data-lucide="chevron-right" class="w-4 h-4 text-mg-text-muted flex-shrink-0 transition-transform duration-200"></i>

                        <div class="flex-1 min-w-0">
                            <!-- 제목 줄 -->
                            <div class="flex items-center gap-2">
                                <?php if ($row['is_notice']) { ?>
                                <span class="badge badge-accent flex-shrink-0">공지</span>
                                <?php } ?>
                                <span class="text-mg-text-primary font-medium truncate">
                                    <?php echo $row['subject']; ?>
                                </span>
                                <?php if ($row['wr_option'] && strpos($row['wr_option'], 'secret') !== false) { ?>
                                <i data-lucide="lock" class="w-4 h-4 text-mg-warning flex-shrink-0"></i>
                                <?php } ?>
                                <?php if ($row['comment_cnt']) { ?>
                                <span class="text-xs text-mg-accent flex-shrink-0">[<?php echo $row['comment_cnt']; ?>]</span>
                                <?php } ?>
                            </div>

                            <!-- 메타 정보 -->
                            <div class="flex items-center gap-3 text-xs text-mg-text-muted mt-1">
                                <?php
                                $row_char = isset($mg_list_chars[$row['wr_id']]) ? $mg_list_chars[$row['wr_id']] : null;
                                if ($row_char) {
                                ?>
                                <span class="flex items-center gap-1">
                                    <?php if ($row_char['ch_thumb']) { ?>
                                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$row_char['ch_thumb']; ?>" alt="" class="w-4 h-4 rounded-full object-cover">
                                    <?php } ?>
                                    <span class="text-mg-text-secondary"><?php echo mg_render_title($row['mb_id'], $row_char['ch_id']); ?><?php echo htmlspecialchars($row_char['ch_name']); ?></span>
                                </span>
                                <span class="text-mg-text-muted">@<?php echo mg_render_nickname($row['mb_id'], $row['wr_name'], $row_char['ch_id'], false); ?></span>
                                <?php } else { ?>
                                <span><?php echo $row['mb_id'] ? mg_render_nickname($row['mb_id'], $row['wr_name']) : htmlspecialchars($row['wr_name']); ?></span>
                                <?php } ?>
                                <span><?php echo $row['datetime2']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 아코디언 내용 (숨김 상태) -->
                    <div id="memo_content_<?php echo $row['wr_id']; ?>" class="hidden">
                        <div class="border-t border-mg-bg-tertiary px-4 py-4 bg-mg-bg-primary/50">
                            <?php if ($row['wr_option'] && strpos($row['wr_option'], 'secret') !== false) { ?>
                                <?php
                                // 비밀글 권한 체크: 본인 또는 관리자만 볼 수 있음
                                $is_owner = ($is_member && $member['mb_id'] === $row['mb_id']);
                                $is_admin_user = ($is_admin === 'super' || $is_admin === 'group' || $is_admin === 'board');
                                if ($is_owner || $is_admin_user) {
                                ?>
                                <div class="prose prose-invert max-w-none text-mg-text-secondary text-sm leading-relaxed">
                                    <?php echo $row['content'] ?? conv_content($row['wr_content'] ?? '', 1); ?>
                                </div>
                                <?php } else { ?>
                                <div class="flex items-center gap-2 text-mg-warning text-sm">
                                    <i data-lucide="lock" class="w-4 h-4"></i>
                                    비밀글입니다.
                                </div>
                                <?php } ?>
                            <?php } else { ?>
                            <div class="prose prose-invert max-w-none text-mg-text-secondary text-sm leading-relaxed">
                                <?php echo $row['content'] ?? conv_content($row['wr_content'] ?? '', 1); ?>
                            </div>
                            <?php } ?>

                            <!-- 펼침 영역 하단 버튼 -->
                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-mg-bg-tertiary">
                                <div class="flex items-center gap-2 text-xs text-mg-text-muted">
                                    <span>조회 <?php echo $row['wr_hit']; ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo $row['href']; ?>" class="btn btn-secondary text-xs px-3 py-1">
                                        상세보기
                                    </a>
                                    <?php if (!empty($row['reply_href'])) { ?>
                                    <a href="<?php echo $row['reply_href']; ?>" class="btn btn-secondary text-xs px-3 py-1">
                                        답변
                                    </a>
                                    <?php } ?>
                                    <?php if (!empty($row['edit_href'])) { ?>
                                    <a href="<?php echo $row['edit_href']; ?>" class="btn btn-secondary text-xs px-3 py-1">
                                        수정
                                    </a>
                                    <?php } ?>
                                    <?php if (!empty($row['delete_href'])) { ?>
                                    <a href="javascript:void(0)" onclick="mgConfirm('정말 삭제하시겠습니까?', function(){ location.href='<?php echo $row['delete_href']; ?>'; });" class="btn btn-secondary text-xs px-3 py-1 text-red-400 hover:text-red-300">
                                        삭제
                                    </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <!-- 인라인 댓글 -->
                        <div class="border-t border-mg-bg-tertiary">
                            <div class="px-4 py-3">
                                <div id="mc_list_<?php echo $row['wr_id']; ?>"></div>
                                <div id="mc_form_<?php echo $row['wr_id']; ?>" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            <?php } else { ?>
            <div class="card">
                <div class="p-8 text-center text-mg-text-muted">
                    등록된 메모가 없습니다.
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 관리자 버튼 -->
        <?php if ($is_checkbox) { ?>
        <div class="mt-4 flex gap-2">
            <button type="submit" name="btn_submit" value="선택삭제" class="btn btn-secondary text-sm">삭제</button>
            <button type="submit" name="btn_submit" value="선택복사" class="btn btn-secondary text-sm">복사</button>
            <button type="submit" name="btn_submit" value="선택이동" class="btn btn-secondary text-sm">이동</button>
        </div>
        <?php } ?>
    </form>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center gap-1">
            <?php echo $write_pages; ?>
        </nav>
    </div>
    <?php } ?>

    <!-- 검색 -->
    <div class="mt-6">
        <form class="card" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <div class="flex gap-2">
                <select name="sfl" class="input w-auto">
                    <option value="wr_subject" <?php echo $sfl == 'wr_subject' ? 'selected' : ''; ?>>제목</option>
                    <option value="wr_content" <?php echo $sfl == 'wr_content' ? 'selected' : ''; ?>>내용</option>
                    <option value="wr_subject||wr_content" <?php echo $sfl == 'wr_subject||wr_content' ? 'selected' : ''; ?>>제목+내용</option>
                    <option value="mb_id,1" <?php echo $sfl == 'mb_id,1' ? 'selected' : ''; ?>>회원ID</option>
                    <option value="wr_name,1" <?php echo $sfl == 'wr_name,1' ? 'selected' : ''; ?>>글쓴이</option>
                </select>
                <input type="text" name="stx" value="<?php echo $stx; ?>" class="input flex-1" placeholder="검색어를 입력하세요">
                <button type="submit" class="btn btn-primary">검색</button>
            </div>
        </form>
    </div>
</div>

<script>
var MC = {
    api: '<?php echo G5_BBS_URL; ?>/comment_api.php',
    bo: '<?php echo $bo_table; ?>',
    diceEnabled: <?php echo $mg_dice_enabled ? 'true' : 'false'; ?>,
    diceUrl: '<?php echo G5_BBS_URL; ?>/comment_dice.php',
    charImg: '<?php echo defined("MG_CHAR_IMAGE_URL") ? MG_CHAR_IMAGE_URL : ""; ?>',
    loaded: {},
    tokens: {},
    delTokens: {}
};

function toggleMemo(id) {
    var el = document.getElementById('memo_content_' + id);
    var icon = document.getElementById('memo_icon_' + id);
    el.classList.toggle('hidden');
    if (el.classList.contains('hidden')) {
        icon.style.transform = 'rotate(0deg)';
    } else {
        icon.style.transform = 'rotate(90deg)';
        if (!MC.loaded[id]) {
            MC.loaded[id] = true;
            mcLoad(id);
        }
    }
}

// 댓글 목록 AJAX 로드
function mcLoad(wrId) {
    var listEl = document.getElementById('mc_list_' + wrId);
    var formEl = document.getElementById('mc_form_' + wrId);
    listEl.innerHTML = '<div class="text-center text-xs text-mg-text-muted py-2">댓글 로딩중...</div>';

    fetch(MC.api + '?action=list&bo_table=' + encodeURIComponent(MC.bo) + '&wr_id=' + wrId)
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            listEl.innerHTML = '<div class="text-xs text-red-400 py-2">' + (data.message || '로드 실패') + '</div>';
            return;
        }
        MC.tokens[wrId] = data.token;
        mcRenderList(wrId, data);
        mcRenderForm(wrId, data);
    })
    .catch(function() {
        listEl.innerHTML = '<div class="text-xs text-red-400 py-2">댓글 로드 실패</div>';
    });
}

// 댓글 목록 렌더링
function mcRenderList(wrId, data) {
    var listEl = document.getElementById('mc_list_' + wrId);
    var cmts = data.comments;

    if (!cmts.length) {
        listEl.innerHTML = '';
        return;
    }

    var html = '<div class="text-xs text-mg-text-muted mb-2">댓글 ' + data.total + '</div>';

    for (var i = 0; i < cmts.length; i++) {
        var c = cmts[i];
        if (c.del_token) MC.delTokens[c.wr_id] = c.del_token;

        var indent = c.depth > 0 ? ' style="margin-left:' + (Math.min(c.depth, 3) * 24) + 'px"' : '';
        html += '<div class="py-2' + (i > 0 ? ' border-t border-mg-bg-tertiary/50' : '') + '"' + indent + ' id="mc_c_' + c.wr_id + '">';

        if (c.is_dice) {
            html += '<div class="flex items-center flex-wrap gap-1 text-xs">';
            html += '<span class="text-mg-accent font-bold">[주사위]</span>';
            html += '<span class="' + (c.is_best ? 'text-yellow-400 font-bold' : 'text-mg-text-secondary') + '">' + c.dice_value + '</span>';
            html += '<span class="text-mg-text-muted">(0~' + data.dice_max + ')</span>';
            if (c.is_best) html += '<span class="badge badge-accent text-xs">BEST</span>';
            html += '<span class="text-mg-text-muted">- ' + c.name_html + '</span>';
            html += '<span class="text-mg-text-muted">' + c.datetime + '</span>';
            html += '</div>';
        } else {
            html += '<div class="flex items-center flex-wrap gap-1.5 mb-1">';
            if (c.char && c.char.ch_thumb) {
                html += '<img src="' + MC.charImg + '/' + c.char.ch_thumb + '" class="w-5 h-5 rounded-full object-cover">';
            }
            html += '<span class="text-xs text-mg-text-secondary">' + c.name_html + '</span>';
            if (c.char && c.nick_html) {
                html += '<span class="text-xs text-mg-text-muted">@' + c.nick_html + '</span>';
            }
            html += '<span class="text-xs text-mg-text-muted">' + c.datetime + '</span>';
            if (c.is_secret) html += '<span class="text-xs text-mg-warning">비밀</span>';
            html += '</div>';
            html += '<div class="text-sm text-mg-text-secondary leading-relaxed">' + c.content_html + '</div>';
        }

        // 원본 내용 (수정용)
        if (c.can_edit && c.content_raw) {
            html += '<input type="hidden" id="mc_raw_' + c.wr_id + '" value="' + _mcEsc(c.content_raw) + '">';
        }

        // 액션 버튼
        var hasActions = c.can_reply || (c.can_edit && !c.is_dice) || c.can_delete;
        if (hasActions) {
            html += '<div class="flex items-center gap-2 mt-1">';
            if (c.can_reply) html += '<button type="button" onclick="mcReply(' + wrId + ',' + c.wr_id + ')" class="text-xs text-mg-text-muted hover:text-mg-accent">답글</button>';
            if (c.can_edit && !c.is_dice) html += '<button type="button" onclick="mcEdit(' + wrId + ',' + c.wr_id + ')" class="text-xs text-mg-text-muted hover:text-mg-accent">수정</button>';
            if (c.can_delete) html += '<button type="button" onclick="mcDel(' + wrId + ',' + c.wr_id + ')" class="text-xs text-mg-text-muted hover:text-red-400">삭제</button>';
            html += '</div>';
        }

        html += '<div id="mc_sub_' + c.wr_id + '"></div>';
        html += '</div>';
    }

    listEl.innerHTML = html;
}

// 댓글 입력 폼 렌더링
function mcRenderForm(wrId, data) {
    var formEl = document.getElementById('mc_form_' + wrId);
    if (!data.can_write) { formEl.innerHTML = ''; return; }

    var html = '<div class="border-t border-mg-bg-tertiary pt-3 mt-2">';

    // 캐릭터 선택 + 비밀 옵션
    var hasOpt = (data.my_chars && data.my_chars.length > 0) || data.use_secret;
    if (hasOpt) {
        html += '<div class="flex items-center flex-wrap gap-2 mb-2">';
        if (data.my_chars && data.my_chars.length > 0) {
            html += '<select id="mc_ch_' + wrId + '" class="input text-xs w-auto">';
            for (var j = 0; j < data.my_chars.length; j++) {
                var ch = data.my_chars[j];
                html += '<option value="' + ch.ch_id + '"' + (ch.ch_id === data.default_ch_id ? ' selected' : '') + '>' + _mcEsc(ch.ch_name) + '</option>';
            }
            html += '</select>';
        }
        if (data.use_secret) {
            html += '<label class="flex items-center gap-1 text-xs text-mg-text-muted cursor-pointer">';
            html += '<input type="checkbox" id="mc_secret_' + wrId + '" class="w-3.5 h-3.5"> 비밀';
            html += '</label>';
        }
        html += '</div>';
    }

    html += '<textarea id="mc_ta_' + wrId + '" class="input text-sm w-full" rows="2" placeholder="댓글을 입력하세요"></textarea>';
    html += '<div class="flex items-center justify-between mt-2">';
    html += '<div class="flex items-center gap-1">';
    html += '<button type="button" class="mg-emoticon-btn" onclick="MgEmoticonPicker.toggleInToolbar(\'mc_ta_' + wrId + '\',this)" title="이모티콘">';
    html += '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    html += '</button>';
    if (MC.diceEnabled) {
        html += '<button type="button" onclick="mcDice(' + wrId + ')" class="mg-emoticon-btn" title="주사위 굴리기">';
        html += '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><circle cx="9" cy="9" r="1" fill="currentColor"/><circle cx="15" cy="9" r="1" fill="currentColor"/><circle cx="9" cy="15" r="1" fill="currentColor"/><circle cx="15" cy="15" r="1" fill="currentColor"/><circle cx="12" cy="12" r="1" fill="currentColor"/></svg>';
        html += '</button>';
    }
    html += '</div>';
    html += '<button type="button" onclick="mcSubmit(' + wrId + ')" class="btn btn-primary text-xs px-3 py-1.5">등록</button>';
    html += '</div></div>';
    formEl.innerHTML = html;
}

// 댓글 등록
function mcSubmit(wrId) {
    var ta = document.getElementById('mc_ta_' + wrId);
    var content = ta.value.trim();
    if (!content) { mgToast('댓글 내용을 입력해주세요.', 'warning'); ta.focus(); return; }

    var fd = new FormData();
    fd.append('action', 'write');
    fd.append('bo_table', MC.bo);
    fd.append('wr_id', wrId);
    fd.append('wr_content', content);
    fd.append('token', MC.tokens[wrId] || '');

    var chSel = document.getElementById('mc_ch_' + wrId);
    if (chSel) fd.append('mg_ch_id', chSel.value);

    var secretCb = document.getElementById('mc_secret_' + wrId);
    if (secretCb && secretCb.checked) fd.append('wr_secret', '1');

    fetch(MC.api, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            ta.value = '';
            mcLoad(wrId);
            _mcUpdateCnt(wrId, 1);
        } else {
            mgToast(data.message || '댓글 등록 실패', 'error');
            if (data.message && data.message.indexOf('토큰') >= 0) mcLoad(wrId);
        }
    })
    .catch(function() { mgToast('요청 실패', 'error'); });
}

// 답글 폼 토글
function mcReply(wrId, cmtId) {
    var subEl = document.getElementById('mc_sub_' + cmtId);
    if (subEl.querySelector('.mc-reply-form')) { subEl.innerHTML = ''; return; }
    _mcCloseSubForms();

    var html = '<div class="mc-reply-form mt-2 p-2 rounded" style="background:rgba(49,51,56,0.5)">';
    html += '<textarea id="mc_ta_reply_' + cmtId + '" class="input text-sm w-full" rows="2" placeholder="답글을 입력하세요"></textarea>';
    html += '<div class="flex items-center justify-between mt-1">';
    html += '<button type="button" class="mg-emoticon-btn" onclick="MgEmoticonPicker.toggleInToolbar(\'mc_ta_reply_' + cmtId + '\',this)" title="이모티콘">';
    html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    html += '</button>';
    html += '<div class="flex gap-1">';
    html += '<button type="button" onclick="mcSubmitReply(' + wrId + ',' + cmtId + ')" class="btn btn-primary text-xs px-2 py-1">등록</button>';
    html += '<button type="button" onclick="document.getElementById(\'mc_sub_' + cmtId + '\').innerHTML=\'\'" class="btn btn-secondary text-xs px-2 py-1">취소</button>';
    html += '</div></div></div>';

    subEl.innerHTML = html;
    document.getElementById('mc_ta_reply_' + cmtId).focus();
}

// 답글 등록
function mcSubmitReply(wrId, cmtId) {
    var ta = document.getElementById('mc_ta_reply_' + cmtId);
    var content = ta.value.trim();
    if (!content) { mgToast('답글 내용을 입력해주세요.', 'warning'); ta.focus(); return; }

    var fd = new FormData();
    fd.append('action', 'write');
    fd.append('bo_table', MC.bo);
    fd.append('wr_id', wrId);
    fd.append('wr_content', content);
    fd.append('token', MC.tokens[wrId] || '');
    fd.append('comment_id', cmtId);

    var chSel = document.getElementById('mc_ch_' + wrId);
    if (chSel) fd.append('mg_ch_id', chSel.value);

    fetch(MC.api, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mcLoad(wrId);
            _mcUpdateCnt(wrId, 1);
        } else {
            mgToast(data.message || '답글 등록 실패', 'error');
            if (data.message && data.message.indexOf('토큰') >= 0) mcLoad(wrId);
        }
    })
    .catch(function() { mgToast('요청 실패', 'error'); });
}

// 수정 폼 토글
function mcEdit(wrId, cmtId) {
    var subEl = document.getElementById('mc_sub_' + cmtId);
    if (subEl.querySelector('.mc-edit-form')) { subEl.innerHTML = ''; return; }
    _mcCloseSubForms();

    var rawEl = document.getElementById('mc_raw_' + cmtId);
    var raw = rawEl ? rawEl.value : '';

    var html = '<div class="mc-edit-form mt-2 p-2 rounded" style="background:rgba(49,51,56,0.5)">';
    html += '<textarea id="mc_ta_edit_' + cmtId + '" class="input text-sm w-full" rows="3">' + _mcEsc(raw) + '</textarea>';
    html += '<div class="flex items-center justify-between mt-1">';
    html += '<button type="button" class="mg-emoticon-btn" onclick="MgEmoticonPicker.toggleInToolbar(\'mc_ta_edit_' + cmtId + '\',this)" title="이모티콘">';
    html += '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
    html += '</button>';
    html += '<div class="flex gap-1">';
    html += '<button type="button" onclick="mcSaveEdit(' + wrId + ',' + cmtId + ')" class="btn btn-primary text-xs px-2 py-1">저장</button>';
    html += '<button type="button" onclick="document.getElementById(\'mc_sub_' + cmtId + '\').innerHTML=\'\'" class="btn btn-secondary text-xs px-2 py-1">취소</button>';
    html += '</div></div></div>';

    subEl.innerHTML = html;
    document.getElementById('mc_ta_edit_' + cmtId).focus();
}

// 수정 저장
function mcSaveEdit(wrId, cmtId) {
    var ta = document.getElementById('mc_ta_edit_' + cmtId);
    var content = ta.value.trim();
    if (!content) { mgToast('내용을 입력해주세요.', 'warning'); ta.focus(); return; }

    var fd = new FormData();
    fd.append('action', 'edit');
    fd.append('bo_table', MC.bo);
    fd.append('wr_id', wrId);
    fd.append('comment_id', cmtId);
    fd.append('wr_content', content);
    fd.append('token', MC.tokens[wrId] || '');

    fetch(MC.api, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mcLoad(wrId);
        } else {
            mgToast(data.message || '수정 실패', 'error');
            if (data.message && data.message.indexOf('토큰') >= 0) mcLoad(wrId);
        }
    })
    .catch(function() { mgToast('요청 실패', 'error'); });
}

// 댓글 삭제
function mcDel(wrId, cmtId) {
    mgConfirm('댓글을 삭제하시겠습니까?', function() {
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('bo_table', MC.bo);
        fd.append('comment_id', cmtId);
        fd.append('token', MC.delTokens[cmtId] || '');

        fetch(MC.api, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                mcLoad(wrId);
                _mcUpdateCnt(wrId, -1);
            } else {
                mgToast(data.message || '삭제 실패', 'error');
                if (data.message && data.message.indexOf('토큰') >= 0) mcLoad(wrId);
            }
        })
        .catch(function() { mgToast('요청 실패', 'error'); });
    });
}

// 주사위 굴리기
function mcDice(wrId) {
    mgConfirm('주사위를 굴리시겠습니까?', function() {
        fetch(MC.diceUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'bo_table=' + encodeURIComponent(MC.bo) + '&wr_id=' + wrId
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mgToast('[주사위] ' + data.dice_value + ' (0~' + data.dice_max + ')', 'info');
            mcLoad(wrId);
            _mcUpdateCnt(wrId, 1);
        } else {
            mgToast(data.message || '주사위 굴리기 실패', 'error');
        }
    })
    .catch(function() { mgToast('요청 실패', 'error'); });
    });
}

// 헤더 댓글 수 배지 갱신
function _mcUpdateCnt(wrId, delta) {
    var contentEl = document.getElementById('memo_content_' + wrId);
    if (!contentEl) return;
    var header = contentEl.previousElementSibling;
    if (!header) return;
    var badge = header.querySelector('.text-xs.text-mg-accent');
    if (badge) {
        var m = badge.textContent.match(/\[(\d+)\]/);
        if (m) {
            var n = Math.max(0, parseInt(m[1]) + delta);
            if (n > 0) badge.textContent = '[' + n + ']';
            else badge.remove();
        }
    } else if (delta > 0) {
        var titleRow = header.querySelector('.flex-1 > .flex');
        if (titleRow) {
            var b = document.createElement('span');
            b.className = 'text-xs text-mg-accent flex-shrink-0';
            b.textContent = '[' + delta + ']';
            titleRow.appendChild(b);
        }
    }
}

// 열린 서브폼 닫기
function _mcCloseSubForms() {
    document.querySelectorAll('.mc-reply-form, .mc-edit-form').forEach(function(el) { el.remove(); });
}

// HTML 이스케이프
function _mcEsc(str) {
    return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>
