<?php
/**
 * Morgan Edition - Postit Board List Skin
 *
 * 일반: lino.it 스타일 포스트잇 그리드
 * 익명(코르크 보드): 알림판 스타일, 드래그 이동, 판 시스템
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// list.php 컨텍스트에서 누락되는 변수 기본값
if (!isset($is_name)) $is_name = (!$is_member);
if (!isset($is_password)) $is_password = (!$is_member);
if (!isset($is_secret)) $is_secret = ($board['bo_use_secret'] ?? 0);

// 익명 게시판 판별
$is_anonymous_board = (($board['bo_1'] ?? '') === 'anonymous');

// ── 색상 팔레트 ──
// 일반 그리드용 (다크)
$postit_colors = array(
    'bg-amber-900/30', 'bg-rose-900/30', 'bg-blue-900/30',
    'bg-emerald-900/30', 'bg-violet-900/30', 'bg-cyan-900/30', 'bg-orange-900/30',
);
$postit_accents = array(
    'bg-amber-500', 'bg-rose-500', 'bg-blue-500',
    'bg-emerald-500', 'bg-violet-500', 'bg-cyan-500', 'bg-orange-500',
);

// 코르크 보드용 (밝은 파스텔)
$cork_card_colors = array(
    array('bg' => '#fff59d', 'shadow' => 'rgba(250,204,21,0.3)'),
    array('bg' => '#f8bbd0', 'shadow' => 'rgba(244,114,182,0.3)'),
    array('bg' => '#b3e5fc', 'shadow' => 'rgba(96,165,250,0.3)'),
    array('bg' => '#c8e6c9', 'shadow' => 'rgba(74,222,128,0.3)'),
    array('bg' => '#e1bee7', 'shadow' => 'rgba(167,139,250,0.3)'),
    array('bg' => '#ffe0b2', 'shadow' => 'rgba(251,146,60,0.3)'),
    array('bg' => '#b2ebf2', 'shadow' => 'rgba(56,189,248,0.3)'),
);
$pin_colors = array('#ef4444', '#3b82f6', '#22c55e', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4');

// ── 판(Panel) 시스템 ──
$current_panel = '';
$panels = array();
if ($is_anonymous_board && ($board['bo_use_category'] ?? 0)) {
    $raw_cats = $board['bo_category_list'] ?? '';
    $panels = array_values(array_filter(array_map('trim', explode('|', $raw_cats))));
    if (!empty($panels)) {
        if ($sca) {
            $current_panel = $sca;
        } else {
            $current_panel = end($panels);
            echo '<script>location.replace("' . G5_BBS_URL . '/board.php?bo_table=' . $bo_table . '&sca=' . urlencode($current_panel) . '");</script>';
            return;
        }
    }
}
$is_latest_panel = empty($panels) || $current_panel === end($panels);

// ── 코르크 보드: 카드 위치 계산 ──
if ($is_anonymous_board && count($list) > 0) {
    $auto_cols = 5;
    $col_width = 19;
    $row_height = 280;
    foreach ($list as $i => &$_row) {
        $has_pos = (isset($_row['wr_2']) && $_row['wr_2'] !== '' && isset($_row['wr_3']) && $_row['wr_3'] !== '');
        if ($has_pos) {
            $_row['_pos_x'] = (float)$_row['wr_2'];
            $_row['_pos_y'] = (float)$_row['wr_3'];
        } else {
            $col = $i % $auto_cols;
            $r = floor($i / $auto_cols);
            $_row['_pos_x'] = max(0, min(95, 1 + ($col * $col_width) + (($_row['wr_id'] % 3) - 1) * 1.5));
            $_row['_pos_y'] = max(0, 10 + ($r * $row_height) + (($_row['wr_id'] % 7) * 5));
        }
    }
    unset($_row);
}


/* ================================================================
 *  코르크 보드 레이아웃 (익명 게시판)
 * ================================================================ */
if ($is_anonymous_board) { ?>

<style>
/* 코르크 보드 */
.cork-board {
    background-color: #b8935a;
    background-image:
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='0.15'/%3E%3C/svg%3E"),
        radial-gradient(ellipse at 20% 30%, rgba(210,170,100,0.5) 0%, transparent 50%),
        radial-gradient(ellipse at 75% 70%, rgba(160,120,60,0.4) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 10%, rgba(220,185,120,0.3) 0%, transparent 40%);
    border: 5px solid #6d4c1a;
    border-image: linear-gradient(135deg, #8b6914 0%, #5c3d12 30%, #a07830 50%, #5c3d12 70%, #8b6914 100%) 1;
    border-radius: 4px;
    box-shadow:
        inset 0 2px 15px rgba(0,0,0,0.25),
        inset 0 -2px 10px rgba(0,0,0,0.15),
        0 4px 20px rgba(0,0,0,0.35);
    position: relative;
    padding: 2rem 1.5rem;
    overflow-x: hidden;
    overflow-y: auto;
    height: calc(80vh - 48px);
}
.cork-card {
    position: absolute;
    width: 180px;
    padding: 1.25rem 0.75rem 0.625rem;
    border-radius: 3px;
    box-shadow: 2px 3px 8px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: box-shadow 0.2s;
    z-index: 1;
    user-select: none;
}
.cork-card:hover { box-shadow: 3px 5px 15px rgba(0,0,0,0.25); z-index: 5; }
.cork-card[data-mine="1"] {
    cursor: grab;
    border: 2px solid rgba(70,100,200,0.7);
    box-shadow: 2px 3px 8px rgba(0,0,0,0.15), 0 0 6px rgba(70,100,200,0.3);
}
.cork-card[data-mine="1"]:hover {
    border-color: rgba(70,100,200,0.9);
    box-shadow: 3px 5px 15px rgba(0,0,0,0.25), 0 0 10px rgba(70,100,200,0.4);
}
.cork-card.dragging {
    cursor: grabbing !important;
    z-index: 100 !important;
    opacity: 0.92;
    box-shadow: 5px 8px 20px rgba(0,0,0,0.3);
}
.cork-pin {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    pointer-events: none;
}
.cork-card-text {
    color: #37474f;
    font-size: 0.75rem;
    line-height: 1.5;
    word-break: break-word;
    overflow: hidden;
    max-height: 180px;
}
.cork-card-meta {
    color: #78909c;
    font-size: 0.625rem;
    margin-top: 0.5rem;
    padding-top: 0.375rem;
    border-top: 1px dashed rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
/* 코르크 보드 작성 모달 — 콘텐츠 영역 기준 */
.cork-write-overlay {
    position: absolute;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(3px);
}
.cork-write-overlay.hidden { display: none; }
/* 열람 모달 — 콘텐츠 영역 기준 */
.cork-view-overlay {
    position: absolute;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(3px);
}
.cork-view-overlay.hidden { display: none; }
.cork-write-card {
    background: #fff59d;
    border-radius: 4px;
    padding: 1.75rem 1.25rem 1.25rem;
    width: 100%;
    max-width: 380px;
    box-shadow: 4px 6px 24px rgba(0,0,0,0.3);
    position: relative;
    transform: rotate(-1deg);
}
.cork-write-card textarea {
    background: transparent;
    border: none;
    border-bottom: 1px dashed rgba(0,0,0,0.12);
    width: 100%;
    resize: none;
    color: #37474f;
    font-size: 0.875rem;
    line-height: 1.6;
    padding: 0;
}
.cork-write-card textarea:focus { outline: none; border-bottom-color: rgba(0,0,0,0.3); }
.cork-write-card textarea::placeholder { color: #a0855c; }
/* 모바일 대응: 코르크 보드 카드 스택 */
@media (max-width: 640px) {
    .cork-board { padding: 0.75rem; }
    .cork-board-inner {
        display: flex !important;
        flex-direction: column;
        gap: 1rem;
        position: static !important;
        min-height: auto !important;
    }
    .cork-card {
        position: static !important;
        width: 100% !important;
        transform: none !important;
    }
    .cork-card[data-mine="1"] { cursor: pointer; }
}
</style>

<div id="bo_list" style="position: relative;">

    <!-- 헤더: 판 탭 + 버튼 -->
    <div class="flex items-center justify-between flex-wrap gap-3 mb-3 px-1">
        <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $board['bo_subject']; ?></h1>
            <?php if ($current_panel) { ?>
            <span class="text-sm text-mg-text-muted">&mdash; <?php echo htmlspecialchars($current_panel); ?>판</span>
            <?php } ?>
            <span class="text-sm text-mg-text-muted">(<?php echo number_format($total_count); ?>)</span>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <?php if (!empty($panels)) {
                foreach ($panels as $p) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $bo_table; ?>&amp;sca=<?php echo urlencode($p); ?>"
                   class="px-3 py-1 rounded text-sm font-medium transition-colors <?php echo $p === $current_panel ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-bg-secondary'; ?>">
                    <?php echo htmlspecialchars($p); ?>판
                </a>
            <?php }
            } ?>
            <?php if ($is_admin) { ?>
            <button type="button" onclick="createNewPanel()" class="px-3 py-1 rounded text-sm bg-mg-bg-tertiary text-mg-text-muted hover:bg-mg-bg-secondary transition-colors" title="새 판 열기">+ 새 판</button>
            <?php } ?>
            <?php if ($admin_href) { ?>
            <a href="<?php echo $admin_href; ?>" class="btn btn-ghost p-2" title="관리자">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </a>
            <?php } ?>
            <?php if ($write_href && $is_latest_panel) { ?>
            <button type="button" onclick="togglePostitWrite()" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                새 포스트잇
            </button>
            <?php } ?>
        </div>
    </div>

    <!-- 작성 모달 (최신 판에서만) -->
    <?php if ($write_href && $is_latest_panel) { ?>
    <div id="postit_write_form" class="cork-write-overlay hidden" onclick="togglePostitWrite()">
        <div class="cork-write-card" onclick="event.stopPropagation()">
            <!-- 압정 -->
            <div class="cork-pin">
                <svg viewBox="0 0 24 24" width="22" height="22"><circle cx="12" cy="12" r="7" fill="#ef4444" stroke="rgba(0,0,0,0.15)" stroke-width="0.5"/><ellipse cx="9.5" cy="9" rx="2" ry="1.5" fill="rgba(255,255,255,0.35)" transform="rotate(-20 9.5 9)"/></svg>
            </div>
            <form name="fpostitwrite" id="fpostitwrite" action="<?php echo $action_url ?? G5_BBS_URL.'/write_update.php'; ?>" method="post" onsubmit="return fpostit_submit(this);" autocomplete="off">
                <input type="hidden" name="w" value="">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <input type="hidden" name="wr_id" value="0">
                <input type="hidden" name="sca" value="<?php echo $sca; ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
                <input type="hidden" name="stx" value="<?php echo $stx; ?>">
                <input type="hidden" name="spt" value="<?php echo $spt; ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="token" value="">
                <input type="hidden" name="wr_subject" id="postit_wr_subject" value="<?php echo date('Y-m-d H:i'); ?> 포스트잇">
                <input type="hidden" name="ca_name" value="<?php echo htmlspecialchars($current_panel); ?>">
                <input type="hidden" name="wr_4" value="<?php echo rand(-15, 15); ?>">

                <?php if ($is_name) { ?>
                <div class="mb-2">
                    <input type="text" name="wr_name" value="<?php echo $name ?? ''; ?>" style="background:transparent; border:none; border-bottom:1px dashed rgba(0,0,0,0.12); width:100%; color:#37474f; font-size:0.8rem; padding:0.25rem 0;" placeholder="이름" required>
                </div>
                <?php } ?>
                <?php if ($is_password) { ?>
                <div class="mb-2">
                    <input type="password" name="wr_password" style="background:transparent; border:none; border-bottom:1px dashed rgba(0,0,0,0.12); width:100%; color:#37474f; font-size:0.8rem; padding:0.25rem 0;" placeholder="비밀번호" required>
                </div>
                <?php } ?>

                <div class="mb-3">
                    <textarea name="wr_content" id="postit_wr_content" rows="6" placeholder="마음속 이야기를 적어주세요..." required></textarea>
                </div>

                <!-- 색상 선택 -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex gap-1.5">
                        <?php foreach ($cork_card_colors as $ci => $cc) { ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="wr_1" value="<?php echo $ci; ?>" <?php echo $ci === 0 ? 'checked' : ''; ?> class="sr-only peer">
                            <span class="block w-6 h-6 rounded-full border-2 border-transparent peer-checked:border-mg-accent peer-checked:ring-2 peer-checked:ring-mg-accent/30 transition-all" style="background: <?php echo $cc['bg']; ?>;"></span>
                        </label>
                        <?php } ?>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="togglePostitWrite()" style="color:#78909c; font-size:0.8rem; background:none; border:none; cursor:pointer;">취소</button>
                        <button type="submit" style="background:#8b6914; color:#fff; padding:0.375rem 1rem; border-radius:3px; font-size:0.8rem; border:none; cursor:pointer;">붙이기</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <!-- 코르크 보드 -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <?php if (count($list) > 0) { ?>
        <div class="cork-board">
            <div class="cork-board-inner" style="position: relative; min-height: 100%;">
                <?php foreach ($list as $i => $row) {
                    $color_index = (isset($row['wr_1']) && $row['wr_1'] !== '') ? ((int)$row['wr_1'] % 7) : ($row['wr_id'] % 7);
                    $card_color = $cork_card_colors[$color_index];
                    $pin_color = $pin_colors[$color_index];
                    $rotation = (isset($row['wr_4']) && $row['wr_4'] !== '') ? (int)$row['wr_4'] : 0;
                    $pos_x = $row['_pos_x'] ?? 0;
                    $pos_y = $row['_pos_y'] ?? 0;

                    // 작성자 표시
                    $is_mine = ($is_member && $member['mb_id'] === $row['mb_id']);
                    $raw_name = $row['wr_name'] ?? strip_tags($row['name'] ?? '');
                    $can_edit = $is_latest_panel && ($is_admin || $is_mine);
                    if ($is_admin) {
                        $display_name = '익명 (' . htmlspecialchars($raw_name ?: '비회원') . ')';
                    } elseif ($is_mine) {
                        $display_name = '익명 (나)';
                    } else {
                        $display_name = '';
                    }

                    // 전체 내용
                    $raw_content = strip_tags($row['content'] ?? $row['wr_content'] ?? '');
                ?>
                <div class="cork-card"
                     style="left:<?php echo $pos_x; ?>%; top:<?php echo $pos_y; ?>px; background:<?php echo $card_color['bg']; ?>; transform:rotate(<?php echo $rotation; ?>deg); box-shadow:2px 3px 8px <?php echo $card_color['shadow']; ?>;"
                     data-wr-id="<?php echo $row['wr_id']; ?>"
                     data-mine="<?php echo (($is_mine || $is_admin) && $is_latest_panel) ? '1' : '0'; ?>"
                     <?php if ($can_edit) { ?>data-edit="<?php echo htmlspecialchars(json_encode(array(
                         'wr_id' => $row['wr_id'],
                         'subject' => $row['wr_subject'] ?? '',
                         'content' => $row['wr_content'] ?? '',
                         'wr_1' => $row['wr_1'] ?? '0',
                         'wr_2' => $row['wr_2'] ?? '',
                         'wr_3' => $row['wr_3'] ?? '',
                         'wr_4' => $row['wr_4'] ?? '0',
                         'ca_name' => $row['ca_name'] ?? '',
                     ), JSON_UNESCAPED_UNICODE), ENT_QUOTES); ?>"<?php } ?>
                     onclick="onCorkCardClick(event, <?php echo $row['wr_id']; ?>)">

                    <?php if ($is_checkbox) { ?>
                    <div style="position:absolute; top:2px; left:4px; z-index:3;">
                        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>"
                               onclick="event.stopPropagation();" style="width:14px; height:14px; opacity:0.6;">
                    </div>
                    <?php } ?>

                    <!-- 압정 -->
                    <div class="cork-pin">
                        <svg viewBox="0 0 24 24" width="20" height="20"><circle cx="12" cy="12" r="7" fill="<?php echo $pin_color; ?>" stroke="rgba(0,0,0,0.15)" stroke-width="0.5"/><ellipse cx="9.5" cy="9" rx="2" ry="1.5" fill="rgba(255,255,255,0.35)" transform="rotate(-20 9.5 9)"/></svg>
                    </div>

                    <!-- 본문 -->
                    <div class="cork-card-text"><?php echo nl2br(htmlspecialchars($raw_content)); ?></div>

                    <!-- 메타 -->
                    <div class="cork-card-meta">
                        <?php if ($display_name) { ?><span><?php echo $display_name; ?></span><?php } ?>
                        <span><?php echo $row['datetime2']; ?></span>
                    </div>
                </div>

                <!-- 모달 -->
                <div id="postit_modal_<?php echo $row['wr_id']; ?>" class="cork-view-overlay hidden" onclick="closePostit(<?php echo $row['wr_id']; ?>)">
                    <div class="relative w-full max-w-lg max-h-[70vh] flex flex-col rounded-lg shadow-2xl overflow-hidden" style="background:<?php echo $card_color['bg']; ?>;" onclick="event.stopPropagation()">
                        <!-- 압정 -->
                        <div style="position:absolute; top:-4px; left:50%; transform:translateX(-50%); z-index:2;">
                            <svg viewBox="0 0 24 24" width="28" height="28"><circle cx="12" cy="12" r="7" fill="<?php echo $pin_color; ?>" stroke="rgba(0,0,0,0.15)" stroke-width="0.5"/><ellipse cx="9.5" cy="9" rx="2" ry="1.5" fill="rgba(255,255,255,0.35)" transform="rotate(-20 9.5 9)"/></svg>
                        </div>
                        <!-- 헤더 -->
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:1rem 1rem 0.5rem; color:#78909c; font-size:0.8rem;">
                            <div style="display:flex; align-items:center; gap:0.5rem;">
                                <?php if ($display_name) { ?>
                                <span style="color:#37474f; font-weight:500;"><?php echo $display_name; ?></span>
                                <span>&middot;</span>
                                <?php } ?>
                                <span><?php echo $row['datetime2']; ?></span>
                                <span>&middot;</span>
                                <span>조회 <?php echo $row['wr_hit']; ?></span>
                            </div>
                            <button type="button" onclick="closePostit(<?php echo $row['wr_id']; ?>)" style="background:none; border:none; cursor:pointer; color:#78909c; padding:4px;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <!-- 본문 -->
                        <div style="padding:0.75rem 1rem; overflow-y:auto; flex:1; color:#37474f; font-size:0.875rem; line-height:1.7;">
                            <?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?>
                        </div>
                        <!-- 하단 버튼 -->
                        <div style="display:flex; align-items:center; justify-content:flex-end; gap:0.5rem; padding:0.75rem 1rem; border-top:1px dashed rgba(0,0,0,0.1);">
                            <?php if ($can_edit) { ?>
                            <button type="button" onclick="event.stopPropagation(); openEditPostit(<?php echo $row['wr_id']; ?>);"
                               style="background:#8b6914; color:#fff; padding:0.25rem 0.75rem; border-radius:3px; font-size:0.75rem; border:none; cursor:pointer;">수정</button>
                            <a href="<?php echo G5_BBS_URL; ?>/delete.php?bo_table=<?php echo $bo_table; ?>&amp;wr_id=<?php echo $row['wr_id']; ?>"
                               onclick="event.stopPropagation(); return confirm('이 포스트잇을 삭제하시겠습니까?');"
                               style="background:#d32f2f; color:#fff; padding:0.25rem 0.75rem; border-radius:3px; font-size:0.75rem; text-decoration:none;">삭제</a>
                            <?php } ?>
                            <button type="button" onclick="closePostit(<?php echo $row['wr_id']; ?>)" style="background:rgba(0,0,0,0.08); color:#37474f; padding:0.25rem 0.75rem; border-radius:3px; font-size:0.75rem; border:none; cursor:pointer;">닫기</button>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } else { ?>
        <div class="cork-board" style="display:flex; align-items:center; justify-content:center;">
            <div style="text-align:center; color:#8b6914; opacity:0.6;">
                <svg style="width:48px; height:48px; margin:0 auto 0.5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <p style="font-size:1rem; font-weight:500;">아직 포스트잇이 없습니다</p>
                <p style="font-size:0.8rem;">첫 번째 포스트잇을 붙여보세요!</p>
            </div>
        </div>
        <?php } ?>

        <?php if ($is_checkbox) { ?>
        <div class="mt-4 flex gap-2">
            <button type="submit" name="btn_submit" value="선택삭제" class="btn btn-secondary text-sm">삭제</button>
            <button type="submit" name="btn_submit" value="선택복사" class="btn btn-secondary text-sm">복사</button>
            <button type="submit" name="btn_submit" value="선택이동" class="btn btn-secondary text-sm">이동</button>
        </div>
        <?php } ?>
    </form>

    <?php if ($total_page > 1) { ?>
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center gap-1"><?php echo $write_pages; ?></nav>
    </div>
    <?php } ?>

    <!-- 수정 모달 (공유) -->
    <div id="postit_edit_modal" class="cork-write-overlay hidden" onclick="closeEditPostit()">
        <div class="cork-write-card" onclick="event.stopPropagation()" style="max-width:400px;">
            <div class="cork-pin">
                <svg viewBox="0 0 24 24" width="22" height="22"><circle cx="12" cy="12" r="7" fill="#3b82f6" stroke="rgba(0,0,0,0.15)" stroke-width="0.5"/><ellipse cx="9.5" cy="9" rx="2" ry="1.5" fill="rgba(255,255,255,0.35)" transform="rotate(-20 9.5 9)"/></svg>
            </div>
            <form id="fpostitedit" action="<?php echo G5_BBS_URL; ?>/write_update.php" method="post" onsubmit="return fpostit_edit_submit(this);" autocomplete="off">
                <input type="hidden" name="w" value="u">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <input type="hidden" name="wr_id" id="edit_wr_id" value="">
                <input type="hidden" name="sca" value="<?php echo $sca; ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
                <input type="hidden" name="stx" value="<?php echo $stx; ?>">
                <input type="hidden" name="spt" value="<?php echo $spt; ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="token" value="">
                <input type="hidden" name="wr_subject" id="edit_wr_subject" value="">
                <input type="hidden" name="ca_name" id="edit_ca_name" value="">
                <input type="hidden" name="wr_2" id="edit_wr_2" value="">
                <input type="hidden" name="wr_3" id="edit_wr_3" value="">
                <input type="hidden" name="wr_4" id="edit_wr_4" value="">
                <input type="hidden" name="html" value="html1">

                <div class="mb-3">
                    <textarea name="wr_content" id="edit_wr_content" rows="6" placeholder="내용을 수정하세요..." required></textarea>
                </div>

                <div class="flex items-center justify-between gap-2">
                    <div class="flex gap-1.5">
                        <?php foreach ($cork_card_colors as $ci => $cc) { ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="wr_1" value="<?php echo $ci; ?>" class="sr-only peer edit-color-radio">
                            <span class="block w-6 h-6 rounded-full border-2 border-transparent peer-checked:border-mg-accent peer-checked:ring-2 peer-checked:ring-mg-accent/30 transition-all" style="background: <?php echo $cc['bg']; ?>;"></span>
                        </label>
                        <?php } ?>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeEditPostit()" style="color:#78909c; font-size:0.8rem; background:none; border:none; cursor:pointer;">취소</button>
                        <button type="submit" style="background:#8b6914; color:#fff; padding:0.375rem 1rem; border-radius:3px; font-size:0.8rem; border:none; cursor:pointer;">수정</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
/* ── 코르크 보드 JS ── */
var _cork_bo_table = '<?php echo $bo_table; ?>';
var _cork_api_url  = '<?php echo G5_BBS_URL; ?>/postit_api.php';
var _cork_is_mobile = window.innerWidth <= 640;
var _cork_is_latest = <?php echo $is_latest_panel ? 'true' : 'false'; ?>;

// 드래그 상태
var _drag = { active: false, el: null, wrId: 0, startX: 0, startY: 0, elStartX: 0, elStartY: 0, moved: false };

// 카드 클릭 (모달 열기 or 드래그 시작 분기)
function onCorkCardClick(e, wrId) {
    // 드래그 중이었으면 무시 (mouseup에서 처리)
    if (_drag.moved) return;
    // 체크박스 클릭은 무시
    if (e.target.type === 'checkbox') return;
    openPostit(wrId);
}

// 드래그 시작 (mousedown/touchstart)
document.addEventListener('DOMContentLoaded', function() {
    if (_cork_is_mobile || !_cork_is_latest) return; // 모바일 또는 이전 판에서는 드래그 비활성

    var cards = document.querySelectorAll('.cork-card[data-mine="1"]');
    cards.forEach(function(card) {
        card.addEventListener('mousedown', onDragStart);
        card.addEventListener('touchstart', onDragStart, { passive: false });
    });

    document.addEventListener('mousemove', onDragMove);
    document.addEventListener('mouseup', onDragEnd);
    document.addEventListener('touchmove', onDragMove, { passive: false });
    document.addEventListener('touchend', onDragEnd);
});

function getPointerPos(e) {
    if (e.touches && e.touches.length) return { x: e.touches[0].clientX, y: e.touches[0].clientY };
    return { x: e.clientX, y: e.clientY };
}

function onDragStart(e) {
    var card = e.currentTarget;
    if (e.target.type === 'checkbox') return;

    var pos = getPointerPos(e);
    _drag.active = true;
    _drag.el = card;
    _drag.wrId = parseInt(card.dataset.wrId);
    _drag.startX = pos.x;
    _drag.startY = pos.y;
    _drag.elStartX = parseFloat(card.style.left) || 0;
    _drag.elStartY = parseFloat(card.style.top) || 0;
    _drag.origTransform = card.style.transform || '';
    _drag.moved = false;

    // 컨테이너 정보 캐시
    var container = card.closest('.cork-board-inner');
    _drag.containerRect = container.getBoundingClientRect();
    _drag.containerW = container.offsetWidth;

    if (e.type === 'touchstart') e.preventDefault();
}

function onDragMove(e) {
    if (!_drag.active) return;

    var pos = getPointerPos(e);
    var dx = pos.x - _drag.startX;
    var dy = pos.y - _drag.startY;

    // 5px 이상 이동해야 드래그 시작
    if (!_drag.moved && Math.abs(dx) < 5 && Math.abs(dy) < 5) return;

    if (!_drag.moved) {
        _drag.moved = true;
        _drag.el.classList.add('dragging');
        _drag.el.style.transition = 'none';
    }

    // % 기반 X 이동
    var pxPerPercent = _drag.containerW / 100;
    var newX = _drag.elStartX + (dx / pxPerPercent);
    var newY = _drag.elStartY + dy;

    newX = Math.max(0, Math.min(95, newX));
    newY = Math.max(0, newY);

    _drag.el.style.left = newX + '%';
    _drag.el.style.top = newY + 'px';
    _drag.el.style.transform = 'rotate(0deg)'; // 드래그 중 수평으로

    if (e.type === 'touchmove') e.preventDefault();
}

function onDragEnd(e) {
    if (!_drag.active) return;
    _drag.active = false;

    if (_drag.moved) {
        _drag.el.classList.remove('dragging');
        _drag.el.style.transition = '';
        _drag.el.style.transform = _drag.origTransform; // 원래 회전 복원

        // 위치 저장
        var newX = parseFloat(_drag.el.style.left);
        var newY = parseFloat(_drag.el.style.top);
        saveCorkPosition(_drag.wrId, newX, newY);
    }

    // moved가 false이면 click 이벤트가 자연스럽게 발생 → openPostit
    setTimeout(function() { _drag.moved = false; }, 50);
}

function saveCorkPosition(wrId, posX, posY) {
    var fd = new FormData();
    fd.append('action', 'save_position');
    fd.append('bo_table', _cork_bo_table);
    fd.append('wr_id', wrId);
    fd.append('pos_x', posX.toFixed(2));
    fd.append('pos_y', posY.toFixed(1));

    fetch(_cork_api_url, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.error) console.warn('위치 저장 실패:', d.error);
        })
        .catch(function(err) { console.warn('위치 저장 오류:', err); });
}

// 새 판 열기
function createNewPanel() {
    if (!confirm('새 판을 열겠습니까? 현재 판은 유지됩니다.')) return;

    var fd = new FormData();
    fd.append('action', 'new_panel');
    fd.append('bo_table', _cork_bo_table);

    fetch(_cork_api_url, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.error) { alert(d.error); return; }
            // 새 판으로 이동
            location.href = '<?php echo G5_BBS_URL; ?>/board.php?bo_table=' + _cork_bo_table + '&sca=' + encodeURIComponent(d.panel);
        })
        .catch(function() { alert('네트워크 오류'); });
}

// 모달 열기/닫기
function openPostit(id) {
    document.getElementById('postit_modal_' + id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closePostit(id) {
    document.getElementById('postit_modal_' + id).classList.add('hidden');
    document.body.style.overflow = '';
}

// 인라인 작성 폼 토글
function togglePostitWrite() {
    var form = document.getElementById('postit_write_form');
    if (form) {
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            var now = new Date();
            var y = now.getFullYear();
            var m = String(now.getMonth() + 1).padStart(2, '0');
            var d = String(now.getDate()).padStart(2, '0');
            var h = String(now.getHours()).padStart(2, '0');
            var mi = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('postit_wr_subject').value = y + '-' + m + '-' + d + ' ' + h + ':' + mi + ' 포스트잇';
            document.getElementById('postit_wr_content').focus();
        }
    }
}

// 인라인 작성 폼 제출
var _fpostit_submitting = false;
function fpostit_submit(f) {
    if (_fpostit_submitting) return true;
    if (!f.wr_content.value.trim()) {
        alert('내용을 입력해주세요.');
        f.wr_content.focus();
        return false;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/write_token.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.error) { alert(data.error); return; }
            f.token.value = data.token;
            _fpostit_submitting = true;
            f.submit();
        } catch(e) { alert('토큰 발급 오류'); }
    };
    xhr.onerror = function() { alert('토큰 발급 네트워크 오류'); };
    xhr.send('bo_table=<?php echo $bo_table; ?>');
    return false;
}

// 수정 모달 열기
function openEditPostit(wrId) {
    // 보기 모달 닫기
    closePostit(wrId);

    // 카드에서 data-edit JSON 읽기
    var card = document.querySelector('.cork-card[data-wr-id="' + wrId + '"]');
    if (!card || !card.dataset.edit) { alert('수정 데이터를 찾을 수 없습니다.'); return; }

    var d;
    try { d = JSON.parse(card.dataset.edit); } catch(e) { alert('수정 데이터 파싱 오류'); return; }

    // 폼 필드 채우기
    document.getElementById('edit_wr_id').value = d.wr_id;
    document.getElementById('edit_wr_content').value = d.content;
    document.getElementById('edit_wr_subject').value = d.subject || (d.wr_id + ' 포스트잇');
    document.getElementById('edit_ca_name').value = d.ca_name || '';
    document.getElementById('edit_wr_2').value = d.wr_2 || '';
    document.getElementById('edit_wr_3').value = d.wr_3 || '';
    document.getElementById('edit_wr_4').value = d.wr_4 || '0';

    // 색상 라디오 체크
    var radios = document.querySelectorAll('#fpostitedit input[name="wr_1"]');
    radios.forEach(function(r) { r.checked = (r.value === String(d.wr_1 || '0')); });

    // 모달 표시
    document.getElementById('postit_edit_modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    setTimeout(function() { document.getElementById('edit_wr_content').focus(); }, 100);
}

// 수정 모달 닫기
function closeEditPostit() {
    document.getElementById('postit_edit_modal').classList.add('hidden');
    document.body.style.overflow = '';
}

// 수정 폼 제출
var _fpostit_edit_submitting = false;
function fpostit_edit_submit(f) {
    if (_fpostit_edit_submitting) return true;
    if (!f.wr_content.value.trim()) {
        alert('내용을 입력해주세요.');
        f.wr_content.focus();
        return false;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/write_token.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.error) { alert(data.error); return; }
            f.token.value = data.token;
            _fpostit_edit_submitting = true;
            f.submit();
        } catch(e) { alert('토큰 발급 오류'); }
    };
    xhr.onerror = function() { alert('토큰 발급 네트워크 오류'); };
    xhr.send('bo_table=<?php echo $bo_table; ?>');
    return false;
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // 수정 모달
        var editModal = document.getElementById('postit_edit_modal');
        if (editModal && !editModal.classList.contains('hidden')) {
            closeEditPostit();
            return;
        }
        // 보기 모달
        var modals = document.querySelectorAll('[id^="postit_modal_"]:not(.hidden)');
        modals.forEach(function(modal) { modal.classList.add('hidden'); });
        document.body.style.overflow = '';
    }
});
</script>

<?php } else {
/* ================================================================
 *  일반 포스트잇 그리드 레이아웃 (비익명 게시판)
 * ================================================================ */ ?>

<div id="bo_list" class="mg-inner">

    <!-- 게시판 헤더 -->
    <div class="card mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $board['bo_subject']; ?></h1>
                <p class="text-sm text-mg-text-muted">총 <?php echo number_format($total_count); ?>개의 포스트잇</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($admin_href) { ?>
                <a href="<?php echo $admin_href; ?>" class="btn btn-ghost" title="관리자">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
                <?php } ?>
                <?php if ($write_href) { ?>
                <button type="button" onclick="togglePostitWrite()" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    새 포스트잇
                </button>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 인라인 작성 폼 (토글) -->
    <?php if ($write_href) { ?>
    <div id="postit_write_form" class="hidden mb-4">
        <div class="card border border-mg-accent/30">
            <h3 class="text-lg font-bold text-mg-text-primary mb-4">
                <svg class="w-5 h-5 inline-block mr-1 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                새 포스트잇 붙이기
            </h3>
            <form name="fpostitwrite" id="fpostitwrite" action="<?php echo $action_url ?? G5_BBS_URL.'/write_update.php'; ?>" method="post" onsubmit="return fpostit_submit(this);" autocomplete="off">
                <input type="hidden" name="w" value="">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <input type="hidden" name="wr_id" value="0">
                <input type="hidden" name="sca" value="<?php echo $sca; ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
                <input type="hidden" name="stx" value="<?php echo $stx; ?>">
                <input type="hidden" name="spt" value="<?php echo $spt; ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="token" value="">
                <input type="hidden" name="wr_subject" id="postit_wr_subject" value="<?php echo date('Y-m-d H:i'); ?> 포스트잇">
                <input type="hidden" name="wr_4" value="<?php echo rand(-15, 15); ?>">

                <?php if ($is_name) { ?>
                <div class="mb-3">
                    <input type="text" name="wr_name" value="<?php echo $name ?? ''; ?>" class="input" placeholder="이름" required>
                </div>
                <?php } ?>
                <?php if ($is_password) { ?>
                <div class="mb-3">
                    <input type="password" name="wr_password" class="input" placeholder="비밀번호" required>
                </div>
                <?php } ?>

                <div class="mb-3">
                    <textarea name="wr_content" id="postit_wr_content" rows="4" class="input w-full resize-none" placeholder="마음속 이야기를 적어주세요..." required></textarea>
                </div>

                <!-- 색상 선택 -->
                <div class="mb-3">
                    <label class="block text-xs text-mg-text-muted mb-1.5">색상</label>
                    <div class="flex gap-2">
                        <?php foreach ($postit_colors as $ci => $pc) { ?>
                        <label class="cursor-pointer">
                            <input type="radio" name="wr_1" value="<?php echo $ci; ?>" <?php echo $ci === 0 ? 'checked' : ''; ?> class="sr-only peer">
                            <span class="block w-7 h-7 rounded-full <?php echo $pc; ?> border-2 border-transparent peer-checked:border-mg-accent peer-checked:ring-2 peer-checked:ring-mg-accent/30 transition-all"></span>
                        </label>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($is_secret) { ?>
                <div class="mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="secret" value="secret" class="w-4 h-4 rounded">
                        <span class="text-sm text-mg-text-secondary">비밀글</span>
                    </label>
                </div>
                <?php } ?>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="togglePostitWrite()" class="btn btn-secondary">취소</button>
                    <button type="submit" class="btn btn-primary">붙이기</button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <!-- 포스트잇 그리드 -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <?php if (count($list) > 0) { ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
            <?php foreach ($list as $i => $row) {
                $color_index = (isset($row['wr_1']) && $row['wr_1'] !== '') ? ((int)$row['wr_1'] % 7) : ($row['wr_id'] % 7);
                $postit_bg = $postit_colors[$color_index];
                $postit_accent = $postit_accents[$color_index];

                $raw_content = strip_tags($row['content'] ?? $row['wr_content'] ?? '');
                $short_content = mb_strlen($raw_content) > 100 ? mb_substr($raw_content, 0, 100) . '...' : $raw_content;

                $display_name = $row['name'] ?: '익명';
            ?>
            <div class="group relative">
                <?php if ($is_checkbox) { ?>
                <div class="absolute top-2 left-2 z-10">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>"
                           class="w-4 h-4 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
                <?php } ?>

                <div class="<?php echo $postit_bg; ?> rounded-lg shadow-lg cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-200 overflow-hidden"
                     onclick="openPostit(<?php echo $row['wr_id']; ?>)">
                    <div class="<?php echo $postit_accent; ?> h-1"></div>
                    <div class="p-4">
                        <p class="text-sm text-mg-text-secondary leading-relaxed mb-3 break-words min-h-[3rem]"><?php echo nl2br(htmlspecialchars($short_content)); ?></p>
                        <div class="flex items-center justify-between text-xs text-mg-text-muted mt-auto pt-2 border-t border-white/5">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <?php echo $display_name; ?>
                            </span>
                            <span><?php echo $row['datetime2']; ?></span>
                        </div>
                        <?php if ($row['comment_cnt']) { ?>
                        <div class="mt-2 text-xs text-mg-accent">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <?php echo $row['comment_cnt']; ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- 모달 -->
                <div id="postit_modal_<?php echo $row['wr_id']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" onclick="closePostit(<?php echo $row['wr_id']; ?>)">
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
                    <div class="relative w-full max-w-2xl max-h-[80vh] flex flex-col <?php echo $postit_bg; ?> rounded-lg shadow-lg overflow-hidden" onclick="event.stopPropagation()">
                        <div class="<?php echo $postit_accent; ?> h-1.5 flex-shrink-0"></div>
                        <div class="flex items-center justify-between p-4 border-b border-white/10 flex-shrink-0">
                            <div class="flex items-center gap-2 text-sm text-mg-text-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-mg-text-secondary font-medium"><?php echo $display_name; ?></span>
                                <span>&middot;</span>
                                <span><?php echo $row['datetime2']; ?></span>
                            </div>
                            <button type="button" onclick="closePostit(<?php echo $row['wr_id']; ?>)" class="text-mg-text-muted hover:text-mg-text-primary transition-colors p-1 -mr-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        <div class="p-4 overflow-y-auto flex-1">
                            <div class="prose prose-invert max-w-none text-mg-text-secondary leading-relaxed text-sm">
                                <?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?>
                            </div>
                        </div>
                        <div class="flex items-center justify-between p-4 border-t border-white/10 flex-shrink-0">
                            <div class="flex items-center gap-2 text-xs text-mg-text-muted">
                                <span>조회 <?php echo $row['wr_hit']; ?></span>
                                <?php if ($row['comment_cnt']) { ?>
                                <span>&middot;</span>
                                <span>댓글 <?php echo $row['comment_cnt']; ?></span>
                                <?php } ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php if ($row['href']) { ?>
                                <a href="<?php echo $row['href']; ?>" class="btn btn-secondary text-xs py-1 px-3" onclick="event.stopPropagation()">상세보기</a>
                                <?php } ?>
                                <?php
                                $can_edit = ($is_admin || ($is_member && $member['mb_id'] == $row['mb_id']));
                                if ($can_edit) { ?>
                                <a href="<?php echo G5_BBS_URL; ?>/write.php?w=u&amp;bo_table=<?php echo $bo_table; ?>&amp;wr_id=<?php echo $row['wr_id']; ?>"
                                   onclick="event.stopPropagation();"
                                   class="btn btn-secondary text-xs py-1 px-3">수정</a>
                                <a href="<?php echo G5_BBS_URL; ?>/delete.php?bo_table=<?php echo $bo_table; ?>&amp;wr_id=<?php echo $row['wr_id']; ?>"
                                   onclick="event.stopPropagation(); return confirm('이 포스트잇을 삭제하시겠습니까?');"
                                   class="btn btn-secondary text-xs py-1 px-3 text-mg-error hover:bg-mg-error/20">삭제</a>
                                <?php } ?>
                                <button type="button" onclick="closePostit(<?php echo $row['wr_id']; ?>)" class="btn btn-secondary text-xs py-1 px-3">닫기</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-mg-text-muted/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <p class="text-mg-text-muted text-lg mb-2">아직 포스트잇이 없습니다</p>
            <p class="text-mg-text-muted text-sm">첫 번째 포스트잇을 붙여보세요!</p>
        </div>
        <?php } ?>

        <?php if ($is_checkbox) { ?>
        <div class="mt-4 flex gap-2">
            <button type="submit" name="btn_submit" value="선택삭제" class="btn btn-secondary text-sm">삭제</button>
            <button type="submit" name="btn_submit" value="선택복사" class="btn btn-secondary text-sm">복사</button>
            <button type="submit" name="btn_submit" value="선택이동" class="btn btn-secondary text-sm">이동</button>
        </div>
        <?php } ?>
    </form>

    <?php if ($total_page > 1) { ?>
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center gap-1"><?php echo $write_pages; ?></nav>
    </div>
    <?php } ?>

</div>

<script>
// 모달 열기/닫기
function openPostit(id) {
    document.getElementById('postit_modal_' + id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closePostit(id) {
    document.getElementById('postit_modal_' + id).classList.add('hidden');
    document.body.style.overflow = '';
}

// 인라인 작성 폼 토글
function togglePostitWrite() {
    var form = document.getElementById('postit_write_form');
    if (form) {
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            var now = new Date();
            var y = now.getFullYear();
            var m = String(now.getMonth() + 1).padStart(2, '0');
            var d = String(now.getDate()).padStart(2, '0');
            var h = String(now.getHours()).padStart(2, '0');
            var mi = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('postit_wr_subject').value = y + '-' + m + '-' + d + ' ' + h + ':' + mi + ' 포스트잇';
            document.getElementById('postit_wr_content').focus();
        }
    }
}

// 인라인 작성 폼 제출
var _fpostit_submitting = false;
function fpostit_submit(f) {
    if (_fpostit_submitting) return true;
    if (!f.wr_content.value.trim()) {
        alert('내용을 입력해주세요.');
        f.wr_content.focus();
        return false;
    }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/write_token.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            if (data.error) { alert(data.error); return; }
            f.token.value = data.token;
            _fpostit_submitting = true;
            f.submit();
        } catch(e) { alert('토큰 발급 오류'); }
    };
    xhr.onerror = function() { alert('토큰 발급 네트워크 오류'); };
    xhr.send('bo_table=<?php echo $bo_table; ?>');
    return false;
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modals = document.querySelectorAll('[id^="postit_modal_"]:not(.hidden)');
        modals.forEach(function(modal) { modal.classList.add('hidden'); });
        document.body.style.overflow = '';
    }
});
</script>

<?php } ?>
