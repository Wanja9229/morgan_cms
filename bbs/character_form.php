<?php
/**
 * Morgan Edition - 캐릭터 생성/수정 폼
 */

include_once('./_common.php');

// 로그인 체크
if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;
$is_edit = $ch_id > 0;

// 신규 생성 시 신청 가능 여부 체크
if (!$is_edit) {
    if (mg_config('char_reg_stop', '0') == '1') {
        alert('현재 캐릭터 모집이 중단되었습니다.', G5_BBS_URL.'/character_list.php');
    }
    if (mg_config('char_reg_period_use', '0') == '1') {
        $_char_reg_start = mg_config('char_reg_start', '');
        $_char_reg_end = mg_config('char_reg_end', '');
        if ($_char_reg_start && $_char_reg_end) {
            $now = date('Y-m-d\TH:i');
            if ($now < $_char_reg_start || $now > $_char_reg_end) {
                alert('지금은 캐릭터 신청 기간이 아닙니다. (신청 기간: ' . str_replace('T', ' ', $_char_reg_start) . ' ~ ' . str_replace('T', ' ', $_char_reg_end) . ')', G5_BBS_URL.'/character_list.php');
            }
        }
    }
}

// 수정 모드일 경우 캐릭터 정보 조회
$char = array();
$profile_values = array();

if ($is_edit) {
    $sql = "SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$member['mb_id']}'";
    $char = sql_fetch($sql);

    if (!$char['ch_id']) {
        alert('존재하지 않거나 권한이 없는 캐릭터입니다.');
    }

    // 프로필 값 조회
    $sql = "SELECT pf_id, pv_value FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id}";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $profile_values[$row['pf_id']] = $row['pv_value'];
    }
} else {
    // 생성 모드: 최대 캐릭터 수 체크
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']}
            WHERE mb_id = '{$member['mb_id']}' AND ch_state != 'deleted'";
    $row = sql_fetch($sql);
    $max_characters = mg_get_max_characters($member['mb_id']);

    if ($row['cnt'] >= $max_characters) {
        alert('최대 캐릭터 수('.$max_characters.'개)에 도달하여 더 이상 생성할 수 없습니다. 상점에서 추가 슬롯을 구매할 수 있습니다.');
    }
}

// 승인된 캐릭터: 기본정보/이미지/프로필 필드 수정 불가
$_is_approved = $is_edit && ($char['ch_state'] ?? '') === 'approved';

// 세력/종족 목록
$_use_side = mg_config('use_side', '1') == '1';
$_use_class = mg_config('use_class', '1') == '1';

$sides = array();
if ($_use_side) {
    $result = sql_query("SELECT * FROM {$g5['mg_side_table']} WHERE side_use = 1 ORDER BY side_order, side_id");
    while ($row = sql_fetch_array($result)) {
        $sides[] = $row;
    }
}

$classes = array();
if ($_use_class) {
    $result = sql_query("SELECT * FROM {$g5['mg_class_table']} WHERE class_use = 1 ORDER BY class_order, class_id");
    while ($row = sql_fetch_array($result)) {
        $classes[] = $row;
    }
}

// 프로필 필드 목록
$profile_fields = array();
$result = sql_query("SELECT * FROM {$g5['mg_profile_field_table']} WHERE pf_use = 1 ORDER BY pf_order, pf_id");
while ($row = sql_fetch_array($result)) {
    $row['value'] = $profile_values[$row['pf_id']] ?? '';
    $profile_fields[] = $row;
}

// 카테고리별 그룹핑
$grouped_fields = array();
foreach ($profile_fields as $field) {
    $category = $field['pf_category'] ?: '기본정보';
    $grouped_fields[$category][] = $field;
}

// 관계 데이터 (수정 모드일 때만)
$my_relations = array();
$received_pending = array();
$sent_pending = array();
$relation_icons = array();
$pending_count = 0;

if ($is_edit) {
    // 내 활성 관계
    $my_relations = mg_get_relations($ch_id, 'active');

    // 받은 대기 신청 (이 캐릭터가 대상인 pending)
    $sql = "SELECT r.*,
                   ca.ch_name AS name_a, ca.ch_thumb AS thumb_a,
                   cb.ch_name AS name_b, cb.ch_thumb AS thumb_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'pending'
            AND ((r.ch_id_a = {$ch_id} AND r.ch_id_from != {$ch_id}) OR (r.ch_id_b = {$ch_id} AND r.ch_id_from != {$ch_id}))
            ORDER BY r.cr_id DESC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $received_pending[] = $row;
    }
    $pending_count = count($received_pending);

    // 보낸 대기 신청 (이 캐릭터가 신청자인 pending)
    $sql = "SELECT r.*,
                   ca.ch_name AS name_a, ca.ch_thumb AS thumb_a,
                   cb.ch_name AS name_b, cb.ch_thumb AS thumb_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'pending' AND r.ch_id_from = {$ch_id}
            ORDER BY r.cr_id DESC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $sent_pending[] = $row;
    }

    // (관계 아이콘 프리셋 제거됨 — 유저가 직접 설정)
}

// URL 탭 파라미터
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'relation' && $is_edit ? 'relation' : 'info';

$g5['title'] = $is_edit ? '캐릭터 수정' : '캐릭터 생성';

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 페이지 헤더 -->
    <div class="mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/character.php" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-2">
            <i data-lucide="chevron-left" class="w-4 h-4"></i>
            <span>내 캐릭터</span>
        </a>
        <h1 class="text-2xl font-bold text-mg-text-primary"><?php echo $is_edit ? '캐릭터 수정' : '새 캐릭터 만들기'; ?></h1>
    </div>

    <?php
    // 반려 알림 표시
    if ($is_edit && $char['ch_state'] == 'editing') {
        $reject_log = sql_fetch("SELECT log_memo, log_datetime FROM {$g5['mg_character_log_table']}
            WHERE ch_id = {$ch_id} AND log_action = 'reject' ORDER BY log_id DESC LIMIT 1");
        if ($reject_log['log_datetime'] ?? '') {
    ?>
    <div class="rounded-lg p-4 mb-4" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);">
        <div class="flex items-start gap-3">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5"></i>
            <div>
                <p class="font-medium text-red-400">캐릭터가 반려되었습니다</p>
                <?php if ($reject_log['log_memo']) { ?>
                <p class="text-sm text-mg-text-secondary mt-1"><?php echo nl2br(htmlspecialchars($reject_log['log_memo'])); ?></p>
                <?php } ?>
                <p class="text-xs text-mg-text-muted mt-2">수정 후 다시 제출해주세요. (<?php echo substr($reject_log['log_datetime'], 0, 16); ?>)</p>
            </div>
        </div>
    </div>
    <?php } } ?>

    <?php if ($is_edit) { ?>
    <!-- 탭 메뉴 -->
    <div class="flex gap-2 mb-6 border-b border-mg-bg-tertiary">
        <button type="button" class="cf-tab px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo $active_tab === 'info' ? 'border-mg-accent text-mg-accent' : 'border-transparent text-mg-text-muted hover:text-mg-text-primary'; ?>" data-tab="info">
            기본 정보
        </button>
        <button type="button" class="cf-tab px-4 py-2 text-sm font-medium border-b-2 transition-colors <?php echo $active_tab === 'relation' ? 'border-mg-accent text-mg-accent' : 'border-transparent text-mg-text-muted hover:text-mg-text-primary'; ?>" data-tab="relation">
            관계
            <?php if ($pending_count > 0) { ?>
            <span class="ml-1 bg-mg-accent text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $pending_count; ?></span>
            <?php } ?>
        </button>
    </div>
    <?php } ?>

    <!-- 기본 정보 탭 -->
    <div id="tab-info" class="cf-tab-content" style="<?php echo $active_tab !== 'info' ? 'display:none' : ''; ?>">
        <!-- 폼 -->
        <form name="fcharform" id="fcharform" method="post" action="<?php echo G5_BBS_URL; ?>/character_form_update.php" enctype="multipart/form-data" class="space-y-6">
            <input type="hidden" name="ch_id" value="<?php echo $ch_id; ?>">
            <input type="hidden" name="token" value="">

            <!-- 기본 정보 카드 -->
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">기본 정보</h2>
                </div>
                <div class="p-4 space-y-4">
                    <!-- 캐릭터명 -->
                    <div>
                        <label for="ch_name" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                            캐릭터명 <?php if (!$_is_approved) { ?><span class="text-red-400">*</span><?php } ?>
                        </label>
                        <?php if ($_is_approved) { ?>
                        <input type="hidden" name="ch_name" value="<?php echo htmlspecialchars($char['ch_name']); ?>">
                        <p class="text-mg-text-primary px-4 py-2.5"><?php echo htmlspecialchars($char['ch_name']); ?></p>
                        <?php } else { ?>
                        <input type="text" name="ch_name" id="ch_name" value="<?php echo $char['ch_name'] ?? ''; ?>" required
                               class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors"
                               placeholder="캐릭터 이름을 입력하세요">
                        <?php } ?>
                    </div>

                    <!-- 세력/종족 -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php if (count($sides) > 0) { ?>
                        <div>
                            <label for="side_id" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                                <?php echo mg_config('side_title', '소속'); ?>
                            </label>
                            <?php if ($_is_approved) {
                                $cur_side_name = '';
                                foreach ($sides as $side) { if ($side['side_id'] == ($char['side_id'] ?? '')) $cur_side_name = $side['side_name']; }
                            ?>
                            <input type="hidden" name="side_id" value="<?php echo (int)($char['side_id'] ?? 0); ?>">
                            <p class="text-mg-text-primary px-4 py-2.5"><?php echo $cur_side_name ?: '-'; ?></p>
                            <?php } else { ?>
                            <select name="side_id" id="side_id" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary focus:outline-none focus:border-mg-accent transition-colors">
                                <option value="">선택안함</option>
                                <?php foreach ($sides as $side) { ?>
                                <option value="<?php echo $side['side_id']; ?>" <?php echo ($char['side_id'] ?? '') == $side['side_id'] ? 'selected' : ''; ?>><?php echo $side['side_name']; ?></option>
                                <?php } ?>
                            </select>
                            <?php } ?>
                        </div>
                        <?php } ?>

                        <?php if (count($classes) > 0) { ?>
                        <div>
                            <label for="class_id" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                                <?php echo mg_config('class_title', '유형'); ?>
                            </label>
                            <?php if ($_is_approved) {
                                $cur_class_name = '';
                                foreach ($classes as $class) { if ($class['class_id'] == ($char['class_id'] ?? '')) $cur_class_name = $class['class_name']; }
                            ?>
                            <input type="hidden" name="class_id" value="<?php echo (int)($char['class_id'] ?? 0); ?>">
                            <p class="text-mg-text-primary px-4 py-2.5"><?php echo $cur_class_name ?: '-'; ?></p>
                            <?php } else { ?>
                            <select name="class_id" id="class_id" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary focus:outline-none focus:border-mg-accent transition-colors">
                                <option value="">선택안함</option>
                                <?php foreach ($classes as $class) { ?>
                                <option value="<?php echo $class['class_id']; ?>" data-side-id="<?php echo (int)($class['side_id'] ?? 0); ?>" <?php echo ($char['class_id'] ?? '') == $class['class_id'] ? 'selected' : ''; ?>><?php echo $class['class_name']; ?></option>
                                <?php } ?>
                            </select>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- 대표 캐릭터 -->
                    <div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="ch_main" value="1" <?php echo ($char['ch_main'] ?? 0) ? 'checked' : ''; ?>
                                   class="w-5 h-5 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent focus:ring-offset-0">
                            <span class="text-sm text-mg-text-secondary">대표 캐릭터로 설정</span>
                        </label>
                        <p class="text-xs text-mg-text-muted mt-1 ml-8">대표 캐릭터는 게시글 작성 시 기본으로 선택됩니다.</p>
                    </div>

                    <?php if ($is_edit) {
                        $my_titles_for_char = mg_get_member_titles($member['mb_id']);
                        $ch_prefix_titles = array_filter($my_titles_for_char, function($t) { return $t['tp_type'] === 'prefix'; });
                        $ch_suffix_titles = array_filter($my_titles_for_char, function($t) { return $t['tp_type'] === 'suffix'; });
                    ?>
                    <!-- 칭호 설정 -->
                    <div class="border-t border-mg-bg-tertiary pt-4">
                        <label class="block text-sm font-medium text-mg-text-secondary mb-2">칭호 설정</label>
                        <?php if (!empty($ch_prefix_titles) || !empty($ch_suffix_titles)) { ?>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1">
                                <label class="text-xs text-mg-text-muted block mb-1">접두칭호</label>
                                <select id="chTitlePrefix" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-mg-text-primary text-sm focus:border-mg-accent focus:outline-none">
                                    <option value="">없음</option>
                                    <?php foreach ($ch_prefix_titles as $pt) { ?>
                                    <option value="<?php echo $pt['tp_id']; ?>" <?php echo ($char['ch_title_prefix_id'] ?? '') == $pt['tp_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pt['tp_name']); ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="flex-1">
                                <label class="text-xs text-mg-text-muted block mb-1">접미칭호</label>
                                <select id="chTitleSuffix" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-mg-text-primary text-sm focus:border-mg-accent focus:outline-none">
                                    <option value="">없음</option>
                                    <?php foreach ($ch_suffix_titles as $st) { ?>
                                    <option value="<?php echo $st['tp_id']; ?>" <?php echo ($char['ch_title_suffix_id'] ?? '') == $st['tp_id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['tp_name']); ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="button" onclick="saveCharTitle()" class="btn btn-primary text-sm whitespace-nowrap">칭호 저장</button>
                            </div>
                        </div>
                        <p class="text-xs text-mg-text-muted mt-2">이 캐릭터로 글을 쓸 때 「접두 접미」 닉네임 형태로 표시됩니다.</p>
                        <?php } else { ?>
                        <p class="text-xs text-mg-text-muted">보유한 칭호가 없습니다. <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=decor&type=title" class="text-mg-accent hover:underline">상점</a>에서 뽑기를 통해 획득하세요.</p>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <?php
            // 전투 스탯 분배 UI (전투 기능 활성화 시)
            $_battle_use = function_exists('mg_config') ? mg_config('battle_use', '1') : '0';
            if ($_battle_use == '1') {
                $_stat_base = (int)mg_config('battle_stat_base', '5');
                $_stat_bonus = (int)mg_config('battle_stat_bonus_points', '15');

                // 수정 모드: 기존 스탯 로드
                $_stat_values = array('stat_hp' => $_stat_base, 'stat_str' => $_stat_base, 'stat_dex' => $_stat_base, 'stat_int' => $_stat_base);
                $_stat_used = 0;
                $_stat_locked = 0;
                if ($is_edit) {
                    $bs_row = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
                    if ($bs_row) {
                        $_stat_locked = (int)($bs_row['stat_locked'] ?? 0);
                        foreach ($_stat_values as $k => $v) {
                            $_stat_values[$k] = (int)($bs_row[$k] ?? $_stat_base);
                            $_stat_used += max(0, $_stat_values[$k] - $_stat_base);
                        }
                    }
                }
                $_stat_remaining = $_stat_bonus - $_stat_used;

                $_stat_labels = array(
                    'stat_hp' => array('HP', '체력', '최대 HP에 영향'),
                    'stat_str' => array('STR', '힘', '물리 공격력에 영향'),
                    'stat_dex' => array('DEX', '민첩', '명중/회피에 영향'),
                    'stat_int' => array('INT', '지능', '마법 공격력에 영향'),
                );
            ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary flex items-center gap-2">
                        전투 스탯
                        <?php if ($_stat_locked) { ?>
                        <span class="text-xs text-mg-text-muted flex items-center gap-1">
                            <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                            확정됨
                        </span>
                        <?php } else { ?>
                        <span id="stat-remaining" class="text-sm font-bold text-mg-accent">(잔여 포인트: <span id="stat-remaining-val"><?php echo $_stat_remaining; ?></span>)</span>
                        <?php } ?>
                    </h2>
                </div>
                <div class="p-4">
                    <?php if ($_stat_locked) { ?>
                    <p class="text-xs text-mg-text-muted mb-4">스탯이 확정되었습니다. 스탯 초기화 아이템을 사용하면 재분배할 수 있습니다.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <?php foreach ($_stat_labels as $skey => $slabel) { ?>
                        <div class="text-center p-2 rounded-lg bg-mg-bg-primary/50">
                            <div class="text-xs font-bold text-mg-accent" style="font-family:'Bebas Neue',monospace;letter-spacing:0.1em;"><?php echo $slabel[0]; ?></div>
                            <div class="text-lg font-bold text-mg-text-primary"><?php echo $_stat_values[$skey]; ?></div>
                            <div class="text-[10px] text-mg-text-muted"><?php echo $slabel[1]; ?></div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                    <p class="text-xs text-mg-text-muted mb-4">
                        기본값 <?php echo $_stat_base; ?> / 자유 분배 포인트 <?php echo $_stat_bonus; ?>. 기본값 이하로는 낮출 수 없습니다.<br>
                        <strong class="text-mg-accent">저장하면 스탯이 확정됩니다.</strong> 이후 변경은 초기화 아이템이 필요합니다.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <?php foreach ($_stat_labels as $skey => $slabel) { ?>
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-mg-bg-primary/50">
                            <div style="min-width:56px;">
                                <span class="text-xs font-bold text-mg-accent" style="font-family:'Bebas Neue',monospace; letter-spacing:0.1em;"><?php echo $slabel[0]; ?></span>
                                <span class="text-xs text-mg-text-muted ml-1"><?php echo $slabel[1]; ?></span>
                            </div>
                            <div class="flex items-center gap-1.5 ml-auto">
                                <button type="button" onclick="statAdjust('<?php echo $skey; ?>', -1)"
                                        class="stat-btn-minus w-7 h-7 flex items-center justify-center rounded bg-mg-bg-tertiary text-mg-text-muted hover:bg-red-500/20 hover:text-red-400 transition-colors text-sm font-bold">-</button>
                                <input type="number" name="battle_stat[<?php echo $skey; ?>]" id="bs_<?php echo $skey; ?>"
                                       value="<?php echo $_stat_values[$skey]; ?>" readonly
                                       class="w-12 text-center bg-mg-bg-primary border border-mg-bg-tertiary rounded text-sm font-bold text-mg-text-primary py-1"
                                       data-base="<?php echo $_stat_base; ?>">
                                <button type="button" onclick="statAdjust('<?php echo $skey; ?>', 1)"
                                        class="stat-btn-plus w-7 h-7 flex items-center justify-center rounded bg-mg-bg-tertiary text-mg-text-muted hover:bg-mg-accent/20 hover:text-mg-accent transition-colors text-sm font-bold">+</button>
                            </div>
                            <span class="text-[10px] text-mg-text-muted" style="min-width:90px;"><?php echo $slabel[2]; ?></span>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } // end battle_use ?>

            <!-- 캐릭터 이미지 카드 -->
            <?php if ($_is_approved) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary flex items-center gap-2">캐릭터 이미지 <span class="text-xs text-mg-text-muted flex items-center gap-1"><i data-lucide="lock" class="w-3.5 h-3.5"></i>승인됨</span></h2>
                </div>
                <div class="p-4">
                    <div class="flex items-start gap-4 flex-wrap">
                        <?php if ($char['ch_thumb']) { ?>
                        <div>
                            <p class="text-xs text-mg-text-muted mb-1.5">두상</p>
                            <div class="w-28 h-28 bg-mg-bg-tertiary rounded-lg overflow-hidden">
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($char['ch_image'] ?? '') { ?>
                        <div>
                            <p class="text-xs text-mg-text-muted mb-1.5">전신</p>
                            <div class="w-28 h-36 bg-mg-bg-tertiary rounded-lg overflow-hidden">
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_image']; ?>" alt="" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ($char['ch_header'] ?? '') { ?>
                        <div class="w-full">
                            <p class="text-xs text-mg-text-muted mb-1.5">헤더</p>
                            <div class="h-32 bg-mg-bg-tertiary rounded-lg overflow-hidden" style="max-width:20rem;">
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_header']; ?>" alt="" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <?php } ?>
                        <?php if (!$char['ch_thumb'] && !($char['ch_image'] ?? '') && !($char['ch_header'] ?? '')) { ?>
                        <p class="text-sm text-mg-text-muted">등록된 이미지가 없습니다.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } else { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">캐릭터 이미지</h2>
                </div>
                <div class="p-4 space-y-6">
                    <!-- 두상 이미지 -->
                    <div>
                        <h3 class="text-sm font-medium text-mg-text-secondary mb-3">두상 이미지</h3>
                        <div class="flex items-start gap-4">
                            <div class="w-28 h-28 bg-mg-bg-tertiary rounded-lg overflow-hidden flex-shrink-0">
                                <?php if ($is_edit && $char['ch_thumb']) { ?>
                                <img id="thumb-preview" src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                                <?php } else { ?>
                                <div id="thumb-preview" class="w-full h-full flex items-center justify-center text-mg-text-muted">
                                    <i data-lucide="image" class="w-10 h-10"></i>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="ch_thumb" id="ch_thumb" accept="image/*"
                                       class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-mg-accent file:text-white hover:file:bg-mg-accent-hover file:cursor-pointer cursor-pointer">
                                <p class="text-xs text-mg-text-muted mt-2">프로필, 댓글 등에 표시됩니다. (정사각형 권장)</p>
                                <?php if ($is_edit && $char['ch_thumb']) { ?>
                                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                    <input type="checkbox" name="del_thumb" value="1" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-red-500 focus:ring-red-500 focus:ring-offset-0">
                                    <span class="text-sm text-red-400">삭제</span>
                                </label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- 전신 이미지 -->
                    <div>
                        <h3 class="text-sm font-medium text-mg-text-secondary mb-3">전신 이미지 <span class="text-mg-text-muted font-normal">(선택)</span></h3>
                        <div class="flex items-start gap-4">
                            <div class="w-28 h-36 bg-mg-bg-tertiary rounded-lg overflow-hidden flex-shrink-0">
                                <?php if ($is_edit && ($char['ch_image'] ?? '')) { ?>
                                <img id="body-preview" src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_image']; ?>" alt="" class="w-full h-full object-cover">
                                <?php } else { ?>
                                <div id="body-preview" class="w-full h-full flex items-center justify-center text-mg-text-muted">
                                    <i data-lucide="image" class="w-10 h-10"></i>
                                </div>
                                <?php } ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="ch_image" id="ch_image" accept="image/*"
                                       class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-mg-accent file:text-white hover:file:bg-mg-accent-hover file:cursor-pointer cursor-pointer">
                                <p class="text-xs text-mg-text-muted mt-2">캐릭터 상세 페이지에 표시됩니다.</p>
                                <?php if ($is_edit && ($char['ch_image'] ?? '')) { ?>
                                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                    <input type="checkbox" name="del_image" value="1" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-red-500 focus:ring-red-500 focus:ring-offset-0">
                                    <span class="text-sm text-red-400">삭제</span>
                                </label>
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <!-- 헤더/배너 이미지 -->
                    <div>
                        <h3 class="text-sm font-medium text-mg-text-secondary mb-3">헤더/배너 이미지 <span class="text-mg-text-muted font-normal">(선택)</span></h3>
                        <div class="flex items-start gap-4">
                            <div class="w-full h-32 bg-mg-bg-tertiary rounded-lg overflow-hidden flex-shrink-0" style="max-width:20rem;">
                                <?php if ($is_edit && ($char['ch_header'] ?? '')) { ?>
                                <img id="header-preview" src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_header']; ?>" alt="" class="w-full h-full object-cover">
                                <?php } else { ?>
                                <div id="header-preview" class="w-full h-full flex items-center justify-center text-mg-text-muted">
                                    <i data-lucide="image" class="w-10 h-10"></i>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="mt-2">
                            <input type="file" name="ch_header" id="ch_header" accept="image/*"
                                   class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-mg-accent file:text-white hover:file:bg-mg-accent-hover file:cursor-pointer cursor-pointer">
                            <p class="text-xs text-mg-text-muted mt-2">프로필 상단에 표시되는 가로형 배너 이미지 (권장: 1200x400)</p>
                            <?php if ($is_edit && ($char['ch_header'] ?? '')) { ?>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" name="del_header" value="1" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-red-500 focus:ring-red-500 focus:ring-offset-0">
                                <span class="text-sm text-red-400">삭제</span>
                            </label>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- 프로필 스킨/배경 선택 -->
            <?php
            // 보유한 스킨/배경 아이템 조회
            $owned_skins = array();
            $owned_bgs = array();
            if ($member['mb_id']) {
                $inv_sql = "SELECT i.si_id, i.si_name, i.si_type, i.si_effect
                            FROM {$g5['mg_inventory_table']} iv
                            JOIN {$g5['mg_shop_item_table']} i ON iv.si_id = i.si_id
                            WHERE iv.mb_id = '{$member['mb_id']}' AND iv.iv_count > 0
                            AND i.si_type IN ('profile_skin', 'profile_effect')
                            ORDER BY i.si_order, i.si_id";
                $inv_result = sql_query($inv_sql);
                while ($inv_row = sql_fetch_array($inv_result)) {
                    $eff = json_decode($inv_row['si_effect'], true);
                    if ($inv_row['si_type'] === 'profile_skin' && !empty($eff['skin_id'])) {
                        $owned_skins[$eff['skin_id']] = $inv_row['si_name'];
                    } elseif ($inv_row['si_type'] === 'profile_effect' && !empty($eff['bg_id'])) {
                        $owned_bgs[$eff['bg_id']] = $inv_row['si_name'];
                    }
                }
            }
            $cur_skin = $is_edit ? ($char['ch_profile_skin'] ?? '') : '';
            $cur_bg = $is_edit ? ($char['ch_profile_bg'] ?? '') : '';
            // 배경색은 아이템 구매제로 전환 (character_form에서 직접 설정 불가)

            // 인벤토리 활성 아이템 (회원 전체 기본값)
            $active_skin_id = mg_get_profile_skin_id($member['mb_id']);
            $active_bg_id = mg_get_profile_effect_id($member['mb_id']);

            // 기본 옵션 라벨 (인벤토리 기본값 표시)
            $all_skin_names = mg_get_profile_skin_list();
            $all_bg_names = mg_get_profile_effect_list();
            $skin_default_label = $active_skin_id ? '인벤토리 기본값 (' . ($all_skin_names[$active_skin_id] ?? $active_skin_id) . ')' : '기본 스킨';
            $bg_default_label = $active_bg_id ? '인벤토리 기본값 (' . ($all_bg_names[$active_bg_id] ?? $active_bg_id) . ')' : '없음';
            ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">프로필 꾸미기</h2>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <label for="ch_profile_skin" class="block text-sm font-medium text-mg-text-secondary mb-1.5">프로필 스킨</label>
                        <select name="ch_profile_skin" id="ch_profile_skin" class="w-full bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-mg-accent focus:border-transparent">
                            <option value="" <?php echo !$cur_skin ? 'selected' : ''; ?>><?php echo htmlspecialchars($skin_default_label); ?></option>
                            <option value="default" <?php echo $cur_skin === 'default' ? 'selected' : ''; ?>>기본 스킨</option>
                            <?php foreach ($owned_skins as $sk_id => $sk_name) { ?>
                            <option value="<?php echo htmlspecialchars($sk_id); ?>" <?php echo $cur_skin === $sk_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($sk_name); ?></option>
                            <?php } ?>
                        </select>
                        <p class="text-xs text-mg-text-muted mt-1">인벤토리에서 장착한 스킨이 기본 적용됩니다. 이 캐릭터만 다르게 하려면 선택하세요.</p>
                    </div>
                    <?php if (!empty($owned_bgs)) { ?>
                    <div>
                        <label for="ch_profile_bg" class="block text-sm font-medium text-mg-text-secondary mb-1.5">배경 이펙트</label>
                        <select name="ch_profile_bg" id="ch_profile_bg" class="w-full bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-mg-accent focus:border-transparent">
                            <option value="" <?php echo !$cur_bg ? 'selected' : ''; ?>><?php echo htmlspecialchars($bg_default_label); ?></option>
                            <option value="none" <?php echo $cur_bg === 'none' ? 'selected' : ''; ?>>없음 (이펙트 해제)</option>
                            <?php foreach ($owned_bgs as $bg_id => $bg_name) { ?>
                            <option value="<?php echo htmlspecialchars($bg_id); ?>" <?php echo $cur_bg === $bg_id ? 'selected' : ''; ?>><?php echo htmlspecialchars($bg_name); ?></option>
                            <?php } ?>
                        </select>
                        <p class="text-xs text-mg-text-muted mt-1">인벤토리에서 장착한 이펙트가 기본 적용됩니다. 이 캐릭터만 다르게 하려면 선택하세요.</p>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- 프로필 정보 (동적 필드) -->
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary flex items-center gap-2"><?php echo $category; ?><?php if ($_is_approved) { ?> <span class="text-xs text-mg-text-muted flex items-center gap-1"><i data-lucide="lock" class="w-3.5 h-3.5"></i>승인됨</span><?php } ?></h2>
                </div>
                <div class="p-4 space-y-4">
                    <?php foreach ($fields as $field) { ?>
                    <div>
                        <label class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                            <?php echo $field['pf_name']; ?>
                            <?php if (!$_is_approved && $field['pf_required']) { ?><span class="text-red-400">*</span><?php } ?>
                        </label>

                        <?php if ($_is_approved) { ?>
                            <?php // 승인된 캐릭터: 읽기전용 텍스트 표시 ?>
                            <?php if ($field['pf_type'] == 'image' && $field['value']) { ?>
                            <div class="rounded-lg overflow-hidden border border-mg-bg-tertiary" style="max-width:16rem;max-height:12rem;">
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($field['value']); ?>" class="w-full h-full object-contain" alt="">
                            </div>
                            <?php } elseif ($field['pf_type'] == 'multiselect') {
                                $ms_selected = $field['value'] ? json_decode($field['value'], true) : array();
                                if (!is_array($ms_selected)) $ms_selected = array();
                            ?>
                            <p class="text-mg-text-primary px-4 py-2.5"><?php echo $ms_selected ? htmlspecialchars(implode(', ', $ms_selected)) : '<span class="text-mg-text-muted">-</span>'; ?></p>
                            <?php } elseif ($field['pf_type'] == 'url' && $field['value']) { ?>
                            <a href="<?php echo htmlspecialchars($field['value']); ?>" target="_blank" rel="noopener" class="text-mg-accent hover:underline px-4 py-2.5 block"><?php echo htmlspecialchars($field['value']); ?></a>
                            <?php } elseif ($field['pf_type'] == 'textarea') { ?>
                            <div class="text-mg-text-primary px-4 py-2.5 whitespace-pre-wrap"><?php echo $field['value'] ? htmlspecialchars($field['value']) : '<span class="text-mg-text-muted">-</span>'; ?></div>
                            <?php } else { ?>
                            <p class="text-mg-text-primary px-4 py-2.5"><?php echo $field['value'] ? htmlspecialchars($field['value']) : '<span class="text-mg-text-muted">-</span>'; ?></p>
                            <?php } ?>
                        <?php } else { ?>
                            <?php // 미승인 캐릭터: 입력 폼 ?>
                            <?php if ($field['pf_type'] == 'text') { ?>
                        <input type="text" name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>"
                               value="<?php echo htmlspecialchars($field['value']); ?>"
                               placeholder="<?php echo $field['pf_placeholder']; ?>"
                               <?php echo $field['pf_required'] ? 'required' : ''; ?>
                               class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors">

                        <?php } elseif ($field['pf_type'] == 'textarea') { ?>
                        <textarea name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" rows="4"
                                  placeholder="<?php echo $field['pf_placeholder']; ?>"
                                  <?php echo $field['pf_required'] ? 'required' : ''; ?>
                                  class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors resize-none"><?php echo htmlspecialchars($field['value']); ?></textarea>

                        <?php } elseif ($field['pf_type'] == 'select') {
                            $options = json_decode($field['pf_options'], true) ?: array();
                        ?>
                        <select name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>"
                                <?php echo $field['pf_required'] ? 'required' : ''; ?>
                                class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary focus:outline-none focus:border-mg-accent transition-colors">
                            <option value="">선택</option>
                            <?php foreach ($options as $opt) { ?>
                            <option value="<?php echo $opt; ?>" <?php echo $field['value'] == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                            <?php } ?>
                        </select>

                        <?php } elseif ($field['pf_type'] == 'multiselect') {
                            $ms_options = json_decode($field['pf_options'], true) ?: array();
                            $ms_selected = $field['value'] ? json_decode($field['value'], true) : array();
                            if (!is_array($ms_selected)) $ms_selected = array();
                        ?>
                        <div class="space-y-1.5">
                            <?php foreach ($ms_options as $opt) { ?>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="profile[<?php echo $field['pf_id']; ?>][]" value="<?php echo htmlspecialchars($opt); ?>"
                                       <?php echo in_array($opt, $ms_selected) ? 'checked' : ''; ?>
                                       class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent focus:ring-offset-0">
                                <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($opt); ?></span>
                            </label>
                            <?php } ?>
                        </div>

                        <?php } elseif ($field['pf_type'] == 'url') { ?>
                        <input type="url" name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>"
                               value="<?php echo htmlspecialchars($field['value']); ?>"
                               placeholder="<?php echo $field['pf_placeholder'] ?: 'https://'; ?>"
                               <?php echo $field['pf_required'] ? 'required' : ''; ?>
                               class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors">

                        <?php } elseif ($field['pf_type'] == 'image') { ?>
                        <?php if ($field['value']) { ?>
                        <div class="mb-2 rounded-lg overflow-hidden border border-mg-bg-tertiary" style="max-width:16rem;max-height:12rem;">
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($field['value']); ?>" class="w-full h-full object-contain" alt="">
                        </div>
                        <input type="hidden" name="profile[<?php echo $field['pf_id']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>">
                        <label class="flex items-center gap-2 mb-2 cursor-pointer">
                            <input type="checkbox" name="del_profile_image[<?php echo $field['pf_id']; ?>]" value="1"
                                   class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-red-500 focus:ring-red-500 focus:ring-offset-0">
                            <span class="text-sm text-red-400">이미지 삭제</span>
                        </label>
                        <?php } ?>
                        <input type="file" name="profile_image[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" accept="image/*"
                               class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-mg-bg-tertiary file:text-mg-text-primary hover:file:bg-mg-bg-primary file:cursor-pointer cursor-pointer">

                        <?php } ?>

                            <?php if ($field['pf_help']) { ?>
                        <p class="text-xs text-mg-text-muted mt-1"><?php echo $field['pf_help']; ?></p>
                            <?php } ?>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <?php if ($is_edit) { ?>
            <script>
            function saveCharTitle() {
                var prefix = document.getElementById('chTitlePrefix').value;
                var suffix = document.getElementById('chTitleSuffix').value;
                fetch('<?php echo G5_BBS_URL; ?>/title_api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=set_character&ch_id=<?php echo $ch_id; ?>&prefix_tp_id=' + prefix + '&suffix_tp_id=' + suffix
                })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    mgToast(data.success ? '칭호가 저장되었습니다.' : (data.message || '저장에 실패했습니다.'), data.success ? 'success' : 'error');
                });
            }
            </script>
            <?php } ?>

            <!-- 버튼 -->
            <div class="flex items-center gap-3">
                <button type="submit" name="btn_save" value="save" class="flex-1 btn-primary font-medium py-3 rounded-lg transition-colors">
                    <?php echo $is_edit ? '저장하기' : '캐릭터 만들기'; ?>
                </button>
                <?php if (!$is_edit || ($char['ch_state'] ?? '') == 'editing') { ?>
                <button type="submit" name="btn_submit" value="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition-colors">
                    승인 신청
                </button>
                <?php } ?>
            </div>

            <?php if ($is_edit) { ?>
            <!-- 삭제 버튼 -->
            <div class="pt-4 border-t border-mg-bg-tertiary">
                <button type="button" onclick="deleteCharacter()" class="text-sm text-red-400 hover:text-red-300 transition-colors">
                    캐릭터 삭제
                </button>
            </div>
            <?php } ?>
        </form>
    </div>

    <?php if ($is_edit) { ?>
    <!-- 관계 탭 -->
    <div id="tab-relation" class="cf-tab-content" style="<?php echo $active_tab !== 'relation' ? 'display:none' : ''; ?>">

        <?php if (function_exists('mg_is_unlocked') && !mg_is_unlocked('relation')) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-8 text-center">
            <div style="font-size:2rem;margin-bottom:0.5rem;">🔒</div>
            <p class="text-mg-text-muted text-sm">관계 기능이 아직 해금되지 않았습니다.</p>
            <p class="text-mg-text-muted text-xs mt-1">개척 시설을 완공하면 이용할 수 있습니다.</p>
        </div>
        <?php } else { ?>

        <!-- 받은 신청 -->
        <div class="mb-6">
            <h2 class="text-lg font-bold text-mg-text-primary mb-3">
                받은 신청
                <?php if ($pending_count > 0) { ?>
                <span class="text-sm bg-mg-accent text-white px-2 py-0.5 rounded-full ml-1"><?php echo $pending_count; ?></span>
                <?php } ?>
            </h2>
            <?php if (empty($received_pending)) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 text-center text-mg-text-muted text-sm">
                받은 관계 신청이 없습니다.
            </div>
            <?php } else { ?>
            <div class="space-y-3">
                <?php foreach ($received_pending as $rel) {
                    $from_is_a = ($rel['ch_id_from'] == $rel['ch_id_a']);
                    $from_name = $from_is_a ? $rel['name_a'] : $rel['name_b'];
                    $from_thumb = $from_is_a ? $rel['thumb_a'] : $rel['thumb_b'];
                    $from_label = $from_is_a ? $rel['cr_label_a'] : $rel['cr_label_b'];
                    $from_memo = $from_is_a ? $rel['cr_memo_a'] : $rel['cr_memo_b'];
                ?>
                <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
                    <div class="flex items-start gap-3">
                        <?php if ($from_thumb) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$from_thumb; ?>" class="w-11 h-11 rounded-full object-cover flex-shrink-0" alt="">
                        <?php } else { ?>
                        <div class="w-11 h-11 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                        <?php } ?>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($from_name); ?></span>
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($rel['cr_color'] ?: '#95a5a6'); ?>"></span>
                                <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($from_label); ?></span>
                            </div>
                            <?php if ($from_memo) { ?>
                            <p class="text-xs text-mg-text-muted mt-1"><?php echo htmlspecialchars($from_memo); ?></p>
                            <?php } ?>
                            <p class="text-xs text-mg-text-muted mt-1"><?php echo date('Y.m.d H:i', strtotime($rel['cr_datetime'])); ?></p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-3 ml-14">
                        <button type="button" onclick="openCfAcceptModal(<?php echo $rel['cr_id']; ?>)" class="btn btn-primary text-sm" style="padding:0.375rem 1rem;">승인</button>
                        <button type="button" onclick="cfRejectRelation(<?php echo $rel['cr_id']; ?>)" class="bg-mg-bg-tertiary hover:bg-red-500/20 text-mg-text-secondary hover:text-red-400 text-sm px-4 py-1.5 rounded-lg transition-colors">거절</button>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <!-- 내 관계 -->
        <div class="mb-6">
            <?php
            $rel_count = mg_get_relation_count($ch_id);
            $rel_max = mg_get_max_relations($ch_id);
            ?>
            <h2 class="text-lg font-bold text-mg-text-primary mb-3">내 관계 <span class="text-sm text-mg-text-muted font-normal">(<?php echo $rel_count; ?>/<?php echo $rel_max; ?>)</span></h2>
            <?php if ($rel_count >= $rel_max) { ?>
            <div style="padding:0.5rem 0.75rem;background:var(--mg-bg-primary);border-radius:0.5rem;margin-bottom:0.75rem;font-size:0.8rem;color:var(--mg-text-muted);border-left:3px solid var(--mg-accent);">
                관계 슬롯이 가득 찼습니다. 상점에서 관계 슬롯 확장권을 구매하여 추가할 수 있습니다.
            </div>
            <?php } ?>
            <?php if (empty($my_relations)) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 text-center text-mg-text-muted text-sm">
                맺어진 관계가 없습니다.
            </div>
            <?php } else { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="divide-y divide-mg-bg-tertiary">
                    <?php foreach ($my_relations as $rel) {
                        $is_a = ($ch_id == $rel['ch_id_a']);
                        $other_name = $is_a ? $rel['name_b'] : $rel['name_a'];
                        $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                        $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                        $my_label = $is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']);
                        $my_memo = $is_a ? $rel['cr_memo_a'] : $rel['cr_memo_b'];
                        $rel_color = $rel['cr_color'] ?: '#95a5a6';
                    ?>
                    <div class="px-4 py-3 flex items-center gap-3 hover:bg-mg-bg-tertiary/30 transition-colors">
                        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" class="flex-shrink-0">
                            <?php if ($other_thumb) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" class="w-10 h-10 rounded-full object-cover" alt="">
                            <?php } else { ?>
                            <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>
                            <?php } ?>
                        </a>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($rel_color); ?>"></span>
                                <span class="text-sm font-medium text-mg-text-primary truncate"><?php echo htmlspecialchars($my_label); ?></span>
                                <span class="text-xs text-mg-text-muted">→</span>
                                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" class="text-sm text-mg-accent hover:underline truncate"><?php echo htmlspecialchars($other_name); ?></a>
                            </div>
                            <?php if ($my_memo) { ?>
                            <p class="text-xs text-mg-text-muted mt-0.5 truncate"><?php echo htmlspecialchars($my_memo); ?></p>
                            <?php } ?>
                        </div>
                        <div class="flex-shrink-0 flex gap-1">
                            <button type="button" onclick="openCfEditModal(<?php echo $rel['cr_id']; ?>, <?php echo $ch_id; ?>, <?php echo htmlspecialchars(json_encode($my_label), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($my_memo ?: ''), ENT_QUOTES); ?>, <?php echo htmlspecialchars(json_encode($rel['cr_color'] ?: '#95a5a6'), ENT_QUOTES); ?>)" class="text-xs text-mg-text-muted hover:text-mg-text-primary px-2 py-1 rounded hover:bg-mg-bg-tertiary transition-colors" title="수정">
                                <i data-lucide="pencil" class="w-4 h-4"></i>
                            </button>
                            <button type="button" onclick="cfDeleteRelation(<?php echo $rel['cr_id']; ?>, <?php echo $ch_id; ?>)" class="text-xs text-red-400 hover:text-red-300 px-2 py-1 rounded hover:bg-mg-bg-tertiary transition-colors" title="해제">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 보낸 신청 -->
        <div>
            <h2 class="text-lg font-bold text-mg-text-primary mb-3">보낸 신청</h2>
            <?php if (empty($sent_pending)) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 text-center text-mg-text-muted text-sm">
                보낸 관계 신청이 없습니다.
            </div>
            <?php } else { ?>
            <div class="space-y-3">
                <?php foreach ($sent_pending as $rel) {
                    $from_is_a = ($rel['ch_id_from'] == $rel['ch_id_a']);
                    $to_name = $from_is_a ? $rel['name_b'] : $rel['name_a'];
                    $to_thumb = $from_is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                    $from_label = $from_is_a ? $rel['cr_label_a'] : $rel['cr_label_b'];
                ?>
                <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 flex items-center gap-3">
                    <?php if ($to_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$to_thumb; ?>" class="w-10 h-10 rounded-full object-cover" alt="">
                    <?php } else { ?>
                    <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>
                    <?php } ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($rel['cr_color'] ?: '#95a5a6'); ?>"></span>
                            <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($from_label); ?></span>
                            <span class="text-xs text-mg-text-muted">→</span>
                            <span class="text-sm font-medium text-mg-text-primary"><?php echo htmlspecialchars($to_name); ?></span>
                        </div>
                    </div>
                    <span class="text-xs text-yellow-400 bg-yellow-500/10 px-2 py-1 rounded">대기중</span>
                    <button type="button" onclick="cfCancelRequest(<?php echo $rel['cr_id']; ?>, <?php echo $ch_id; ?>)" class="text-xs text-red-400 hover:text-red-300 px-2 py-1 rounded hover:bg-mg-bg-tertiary transition-colors" title="취소">취소</button>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 승인 모달 -->
    <div id="cf-accept-modal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.6)">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary w-full max-w-md">
                <div class="px-5 py-4 border-b border-mg-bg-tertiary flex justify-between items-center">
                    <h3 class="font-bold text-mg-text-primary">관계 승인</h3>
                    <button type="button" onclick="closeCfModal('cf-accept-modal')" class="text-mg-text-muted hover:text-mg-text-primary text-xl leading-none">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <input type="hidden" id="cf-accept-cr-id">
                    <p class="text-sm text-mg-text-secondary">내 쪽 설정을 입력해주세요. 비워두면 상대와 같은 값이 적용됩니다.</p>
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">내 관계명 <span class="text-mg-text-muted">(선택)</span></label>
                        <input type="text" id="cf-accept-label" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" placeholder="예: 귀찮은 소꿉친구..." maxlength="50">
                    </div>
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">관계선 색상 <span class="text-mg-text-muted">(선택)</span></label>
                        <input type="color" id="cf-accept-color" value="#95a5a6" class="w-10 h-10 rounded border border-mg-bg-tertiary cursor-pointer" style="padding:2px;">
                    </div>
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">한줄 메모 <span class="text-mg-text-muted">(선택)</span></label>
                        <input type="text" id="cf-accept-memo" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" maxlength="200">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-mg-bg-tertiary flex justify-end gap-2">
                    <button type="button" onclick="closeCfModal('cf-accept-modal')" class="px-4 py-2 text-sm text-mg-text-secondary rounded-lg hover:bg-mg-bg-tertiary transition-colors">취소</button>
                    <button type="button" onclick="cfSubmitAccept()" class="btn btn-primary text-sm">승인</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 수정 모달 -->
    <div id="cf-edit-modal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.6)">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary w-full max-w-md">
                <div class="px-5 py-4 border-b border-mg-bg-tertiary flex justify-between items-center">
                    <h3 class="font-bold text-mg-text-primary">관계 수정</h3>
                    <button type="button" onclick="closeCfModal('cf-edit-modal')" class="text-mg-text-muted hover:text-mg-text-primary text-xl leading-none">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <input type="hidden" id="cf-edit-cr-id">
                    <input type="hidden" id="cf-edit-ch-id">
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">관계명</label>
                        <input type="text" id="cf-edit-label" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" maxlength="50">
                    </div>
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">관계선 색상</label>
                        <input type="color" id="cf-edit-color" value="#95a5a6" class="w-10 h-10 rounded border border-mg-bg-tertiary cursor-pointer" style="padding:2px;">
                    </div>
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">한줄 메모</label>
                        <input type="text" id="cf-edit-memo" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" maxlength="200">
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-mg-bg-tertiary flex justify-end gap-2">
                    <button type="button" onclick="closeCfModal('cf-edit-modal')" class="px-4 py-2 text-sm text-mg-text-secondary rounded-lg hover:bg-mg-bg-tertiary transition-colors">취소</button>
                    <button type="button" onclick="cfSubmitEdit()" class="btn btn-primary text-sm">저장</button>
                </div>
            </div>
        </div>
    <?php } ?>
    </div>
    <?php } ?>
</div>

<script>
// 이미지 미리보기 함수
function setupImagePreview(inputId, previewId) {
    document.getElementById(inputId)?.addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var preview = document.getElementById(previewId);
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// 두상/전신 이미지 미리보기
setupImagePreview('ch_thumb', 'thumb-preview');
setupImagePreview('ch_image', 'body-preview');
setupImagePreview('ch_header', 'header-preview');
// 캐릭터 삭제
function deleteCharacter() {
    mgConfirm('정말 이 캐릭터를 삭제하시겠습니까?\n삭제된 캐릭터는 복구할 수 없습니다.', function() {
        var form = document.getElementById('fcharform');
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'btn_delete';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    });
}

<?php if ($is_edit) { ?>
// 탭 전환
document.querySelectorAll('.cf-tab').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.cf-tab').forEach(function(b) {
            b.classList.remove('border-mg-accent', 'text-mg-accent');
            b.classList.add('border-transparent', 'text-mg-text-muted');
        });
        this.classList.remove('border-transparent', 'text-mg-text-muted');
        this.classList.add('border-mg-accent', 'text-mg-accent');
        document.querySelectorAll('.cf-tab-content').forEach(function(c) { c.style.display = 'none'; });
        document.getElementById('tab-' + this.dataset.tab).style.display = '';
    });
});

// 관계 관리 JS
var CF_REL_API = '<?php echo G5_BBS_URL; ?>/relation_api.php';

function closeCfModal(id) { document.getElementById(id).classList.add('hidden'); }

// 승인 모달
window.openCfAcceptModal = function(crId) {
    document.getElementById('cf-accept-cr-id').value = crId;
    document.getElementById('cf-accept-label').value = '';
    document.getElementById('cf-accept-color').value = '#95a5a6';
    document.getElementById('cf-accept-memo').value = '';
    document.getElementById('cf-accept-modal').classList.remove('hidden');
};

window.cfSubmitAccept = function() {
    var data = new FormData();
    data.append('action', 'accept');
    data.append('cr_id', document.getElementById('cf-accept-cr-id').value);
    data.append('label_b', document.getElementById('cf-accept-label').value);
    data.append('color', document.getElementById('cf-accept-color').value);
    data.append('memo_b', document.getElementById('cf-accept-memo').value);
    fetch(CF_REL_API, { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            mgToast(res.message, res.success ? 'success' : 'error');
            if (res.success) location.reload();
        });
};

// 거절
window.cfRejectRelation = function(crId) {
    mgConfirm('이 관계 신청을 거절하시겠습니까?', function() {
        var data = new FormData();
        data.append('action', 'reject');
        data.append('cr_id', crId);
        fetch(CF_REL_API, { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                mgToast(res.message, res.success ? 'success' : 'error');
                if (res.success) location.reload();
            });
    });
};

// 수정 모달
window.openCfEditModal = function(crId, chId, label, memo, color) {
    document.getElementById('cf-edit-cr-id').value = crId;
    document.getElementById('cf-edit-ch-id').value = chId;
    document.getElementById('cf-edit-label').value = label;
    document.getElementById('cf-edit-color').value = color || '#95a5a6';
    document.getElementById('cf-edit-memo').value = memo;
    document.getElementById('cf-edit-modal').classList.remove('hidden');
};

window.cfSubmitEdit = function() {
    var data = new FormData();
    data.append('action', 'update');
    data.append('cr_id', document.getElementById('cf-edit-cr-id').value);
    data.append('my_ch_id', document.getElementById('cf-edit-ch-id').value);
    data.append('label', document.getElementById('cf-edit-label').value);
    data.append('color', document.getElementById('cf-edit-color').value);
    data.append('memo', document.getElementById('cf-edit-memo').value);
    fetch(CF_REL_API, { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            mgToast(res.message, res.success ? 'success' : 'error');
            if (res.success) location.reload();
        });
};

// 해제
window.cfCancelRequest = function(crId, myChId) {
    mgConfirm('보낸 신청을 취소하시겠습니까?', function() {
        var data = new FormData();
        data.append('action', 'delete');
        data.append('cr_id', crId);
        data.append('my_ch_id', myChId);
        fetch(CF_REL_API, { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                mgToast(res.message, res.success ? 'success' : 'error');
                if (res.success) location.reload();
            });
    });
};

window.cfDeleteRelation = function(crId, myChId) {
    mgConfirm('이 관계를 해제하시겠습니까?', function() {
        var data = new FormData();
        data.append('action', 'delete');
        data.append('cr_id', crId);
        data.append('my_ch_id', myChId);
        fetch(CF_REL_API, { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                mgToast(res.message, res.success ? 'success' : 'error');
                if (res.success) location.reload();
            });
    });
};

// 모달 외부 클릭 닫기
['cf-accept-modal', 'cf-edit-modal'].forEach(function(id) {
    document.getElementById(id)?.addEventListener('click', function(e) {
        if (e.target === this && document._mgMdTarget === this) closeCfModal(id);
    });
});
<?php } ?>

// 진영 선택 시 클래스 필터링
(function() {
    var sideSelect = document.getElementById('side_id');
    var classSelect = document.getElementById('class_id');
    if (!sideSelect || !classSelect) return;

    function filterClasses() {
        var selectedSide = parseInt(sideSelect.value) || 0;
        var currentClass = classSelect.value;
        var hasSelected = false;

        var options = classSelect.querySelectorAll('option[data-side-id]');
        options.forEach(function(opt) {
            var optSide = parseInt(opt.getAttribute('data-side-id')) || 0;
            // 공용(0) 또는 선택한 진영과 일치하면 표시
            var show = (optSide === 0 || selectedSide === 0 || optSide === selectedSide);
            opt.style.display = show ? '' : 'none';
            opt.disabled = !show;
            if (opt.value === currentClass && show) hasSelected = true;
        });

        // 현재 선택이 숨겨진 경우 초기화
        if (!hasSelected && currentClass) {
            classSelect.value = '';
        }
    }

    sideSelect.addEventListener('change', filterClasses);
    // 페이지 로드 시 초기 필터링
    filterClasses();
})();

// ── 전투 스탯 분배 ──
(function() {
    var remainEl = document.getElementById('stat-remaining-val');
    if (!remainEl) return; // 전투 비활성화 시 요소 없음

    var statKeys = ['stat_hp', 'stat_str', 'stat_dex', 'stat_int'];
    var totalBonus = <?php echo isset($_stat_bonus) ? $_stat_bonus : 0; ?>;

    function getUsed() {
        var used = 0;
        statKeys.forEach(function(key) {
            var input = document.getElementById('bs_' + key);
            if (input) {
                var base = parseInt(input.getAttribute('data-base')) || 0;
                var val = parseInt(input.value) || 0;
                used += Math.max(0, val - base);
            }
        });
        return used;
    }

    function updateRemaining() {
        var remaining = totalBonus - getUsed();
        remainEl.textContent = remaining;
        remainEl.style.color = remaining === 0 ? 'var(--mg-success)' : (remaining < 0 ? 'var(--mg-error)' : '');
    }

    window.statAdjust = function(key, delta) {
        var input = document.getElementById('bs_' + key);
        if (!input) return;
        var base = parseInt(input.getAttribute('data-base')) || 0;
        var val = parseInt(input.value) || 0;
        var newVal = val + delta;

        // 기본값 이하로 못 낮춤
        if (newVal < base) return;

        // 올릴 때: 잔여 포인트 체크
        if (delta > 0) {
            var remaining = totalBonus - getUsed();
            if (remaining <= 0) return;
        }

        input.value = newVal;
        updateRemaining();
    };

    updateRemaining();
})();
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
