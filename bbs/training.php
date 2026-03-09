<?php
/**
 * Morgan Edition - 수업 스케줄 (Training Schedule)
 *
 * 프린세스 메이커 스타일 주간 시간표
 * 토/일: 다음 주 스케줄 설정 (편집 모드)
 * 월~금: 현재 주 진행 상황 (읽기 전용)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');
include_once(G5_PATH.'/plugin/morgan/training.php');

if (mg_config('battle_use', '1') != '1' || mg_config('battle_training_use', '1') != '1') {
    alert_close('수업 스케줄 기능이 비활성화되어 있습니다.');
}
if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$mb_id = $member['mb_id'];
$my_characters = mg_get_usable_characters($mb_id);

if (empty($my_characters)) {
    alert_close('승인된 캐릭터가 없습니다.');
}

// 캐릭터 선택
$selected_ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;
$ch = null;
if ($selected_ch_id) {
    foreach ($my_characters as $c) {
        if ((int)$c['ch_id'] === $selected_ch_id) { $ch = $c; break; }
    }
}
if (!$ch) $ch = $my_characters[0];
$selected_ch_id = (int)$ch['ch_id'];

// 스탯 초기화
mg_battle_init_stat($selected_ch_id, $mb_id);

// 현재 주차 정보
$current = mg_training_get_current_week();
$cur_year = $current['year'];
$cur_week = $current['week'];
$day_of_week = (int)date('w'); // 0=Sun, 6=Sat

// 편집 가능 여부 (토/일)
$can_edit = mg_training_can_edit_schedule($cur_year, $cur_week);

// 편집 대상: 다음 주
if ($can_edit) {
    $edit_year = $cur_year;
    $edit_week = $cur_week + 1;
    if ($edit_week > (int)date('W', strtotime($cur_year . '-12-28'))) {
        $edit_year++;
        $edit_week = 1;
    }
} else {
    $edit_year = $cur_year;
    $edit_week = $cur_week;
}

// Lazy 정산 트리거 — 미정산 주가 있으면 자동 정산
mg_training_calc_progress($selected_ch_id, $cur_year, $cur_week);

// 스트레스
$stress = mg_training_get_stress($selected_ch_id);
$stress_max = (int)mg_config('training_stress_max', '100');
$stress_threshold = (int)mg_config('training_stress_threshold', '70');

// 수업 목록
$classes = mg_training_get_classes();
$class_map = array();
foreach ($classes as $cls) {
    $class_map[(int)$cls['tc_id']] = $cls;
}

// 이수 진행도
$progress_list = mg_training_get_progress($selected_ch_id);
$progress_map = array();
foreach ($progress_list as $p) {
    $progress_map[(int)$p['tc_id']] = $p;
}

// 현재 주 스케줄
$schedule = mg_training_get_schedule($selected_ch_id, $edit_year, $edit_week);
$slots = $schedule ? $schedule['ts_slots'] : array_fill(0, 15, 0);
if (is_string($slots)) $slots = json_decode($slots, true);
if (!is_array($slots) || count($slots) !== 15) $slots = array_fill(0, 15, 0);

// 주중 진행 표시용: 소화된 슬롯 수
$slots_consumed = 0;
if (!$can_edit && $schedule && (int)$schedule['ts_settled'] === 0) {
    $slot_times = array(9, 13, 16);
    $week_dates = mg_training_get_week_dates($cur_year, $cur_week);
    $now = time();
    for ($day = 0; $day < 5; $day++) {
        foreach ($slot_times as $si => $hour) {
            $slot_time = strtotime($week_dates[$day] . ' ' . $hour . ':00:00');
            if ($now >= $slot_time) $slots_consumed++;
        }
    }
}

$mb_point = (int)$member['mb_point'];

// 스탯 데이터
$stat = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$selected_ch_id}");

$day_names = array('월', '화', '수', '목', '금');
$slot_names = array('오전', '오후 1', '오후 2');

$g5['title'] = '수업 스케줄';
include_once(G5_THEME_PATH.'/head.php');

$api_url = G5_BBS_URL . '/training_api.php';
?>

<div id="training-app" class="mx-auto p-4" style="max-width:72rem;">

    <!-- 헤더 카드 -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-mg-bg-secondary p-5 rounded-2xl border border-mg-bg-tertiary mb-6 gap-4">
        <div class="flex items-center gap-4">
            <?php
            $ch_thumb_url = (!empty($ch['ch_thumb'])) ? MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb'] : '';
            ?>
            <?php if ($ch_thumb_url) { ?>
            <img src="<?php echo $ch_thumb_url; ?>" class="w-14 h-14 rounded-full object-cover border-2 border-mg-accent flex-shrink-0" alt="">
            <?php } else { ?>
            <div class="w-14 h-14 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-2xl border-2 border-mg-accent flex-shrink-0">📚</div>
            <?php } ?>
            <div>
                <h1 class="text-xl font-bold text-mg-text-primary">
                    <?php if ($can_edit) { ?>
                    다음 주 스케줄 설정
                    <?php } else { ?>
                    <?php echo $edit_year; ?>년 <?php echo $edit_week; ?>주차
                    <span class="text-sm font-normal text-mg-text-muted ml-1">(<?php echo $slots_consumed; ?>/15 슬롯 소화)</span>
                    <?php } ?>
                </h1>
                <p class="text-sm text-mg-text-muted mt-0.5">
                    <?php if ($can_edit) { ?>
                    이번 주말까지 스케줄을 확정해주세요.
                    <?php } else { ?>
                    현재 수업이 진행 중입니다.
                    <?php } ?>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-6">
            <!-- 캐릭터 선택 -->
            <?php if (count($my_characters) > 1) { ?>
            <select class="bg-mg-bg-tertiary text-mg-text-primary text-sm rounded-lg px-3 py-2 border border-mg-bg-tertiary" onchange="location.href='?ch_id='+this.value">
                <?php foreach ($my_characters as $c) { ?>
                <option value="<?php echo $c['ch_id']; ?>" <?php echo (int)$c['ch_id'] === $selected_ch_id ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['ch_name'] ?? ''); ?>
                </option>
                <?php } ?>
            </select>
            <?php } ?>
            <div class="text-right">
                <p class="text-xs text-mg-text-muted">보유 포인트</p>
                <p class="text-lg font-bold text-mg-accent"><?php echo number_format($mb_point); ?> P</p>
            </div>
            <div class="text-right">
                <p class="text-xs text-mg-text-muted">스트레스</p>
                <div class="flex items-center gap-2">
                    <p class="text-lg font-bold <?php echo $stress >= $stress_threshold ? 'text-red-400' : 'text-mg-text-primary'; ?>"><?php echo $stress; ?><span class="text-xs text-mg-text-muted">/<?php echo $stress_max; ?></span></p>
                    <div class="w-20 h-2 bg-mg-bg-tertiary rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all <?php echo $stress >= $stress_threshold ? 'bg-red-500' : ($stress >= $stress_threshold * 0.7 ? 'bg-yellow-500' : 'bg-green-500'); ?>"
                             style="width:<?php echo min(100, ($stress / $stress_max) * 100); ?>%"></div>
                    </div>
                </div>
            </div>
            <!-- 스탯 확인 버튼 -->
            <button type="button" onclick="document.getElementById('tr-stat-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent border border-mg-bg-tertiary hover:border-mg-accent/50 transition-colors">
                <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                스탯
            </button>
        </div>
    </div>

    <!-- 2컬럼 레이아웃 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- 좌측: 시간표 (2/3) -->
        <div class="lg:col-span-2 bg-mg-bg-secondary p-5 rounded-2xl border border-mg-bg-tertiary">
            <h2 class="text-base font-semibold text-mg-text-primary mb-4 flex items-center gap-2">
                <i data-lucide="calendar" class="w-5 h-5 text-mg-accent"></i>
                시간표 배분
            </h2>

            <!-- 테이블 헤더 (데스크톱) -->
            <div class="hidden md:grid grid-cols-4 gap-3 mb-2 text-center text-xs font-medium text-mg-text-muted">
                <div>요일</div>
                <div>오전 (09:00)</div>
                <div>오후 1 (13:00)</div>
                <div>오후 2 (16:00)</div>
            </div>

            <div class="space-y-3">
                <?php for ($day = 0; $day < 5; $day++) { ?>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-center p-3 rounded-xl border border-mg-bg-tertiary/50" style="background:rgba(15,23,42,0.3);">
                    <div class="text-center md:text-left font-bold text-mg-text-primary ml-2"><?php echo $day_names[$day]; ?>요일</div>
                    <?php for ($slot = 0; $slot < 3; $slot++) {
                        $idx = $day * 3 + $slot;
                        $tc_id = (int)$slots[$idx];
                        $cls_info = ($tc_id > 0 && isset($class_map[$tc_id])) ? $class_map[$tc_id] : null;
                        $consumed = (!$can_edit && $idx < $slots_consumed);
                        $is_current = (!$can_edit && $idx === $slots_consumed);
                    ?>
                    <?php if ($can_edit) { ?>
                    <select class="tr-slot-select w-full bg-mg-bg-tertiary text-mg-text-primary text-sm rounded-lg p-2.5 border border-transparent hover:border-mg-accent/50 transition-colors focus:border-mg-accent outline-none"
                            data-idx="<?php echo $idx; ?>"
                            onchange="trUpdateCost()">
                        <option value="0">☕ 자유 행동 (0P)</option>
                        <?php foreach ($classes as $c) {
                            $c_id = (int)$c['tc_id'];
                            $maxed = false;
                            if ((int)$c['tc_max_repeat'] > 0 && isset($progress_map[$c_id])) {
                                if ((int)$progress_map[$c_id]['tp_completed'] >= (int)$c['tc_max_repeat']) {
                                    $maxed = true;
                                }
                            }
                            $label = ($c['tc_icon'] ? $c['tc_icon'].' ' : '') . $c['tc_name'] . ' (' . (int)$c['tc_cost'] . 'P)';
                        ?>
                        <option value="<?php echo $c_id; ?>"
                                <?php echo $tc_id === $c_id ? 'selected' : ''; ?>
                                <?php echo $maxed ? 'disabled' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?><?php echo $maxed ? ' [완료]' : ''; ?>
                        </option>
                        <?php } ?>
                    </select>
                    <?php } else { ?>
                    <div class="flex items-center gap-2 text-sm px-2 py-1.5 rounded-lg <?php echo $consumed ? 'bg-green-500/5' : ($is_current ? 'bg-yellow-500/5' : ''); ?>">
                        <?php if ($consumed) { ?>
                            <span class="w-5 h-5 rounded-full bg-green-500/20 text-green-400 flex items-center justify-center text-xs">✓</span>
                        <?php } elseif ($is_current) { ?>
                            <span class="w-5 h-5 rounded-full bg-yellow-500/20 text-yellow-400 flex items-center justify-center text-xs">▶</span>
                        <?php } else { ?>
                            <span class="w-5 h-5 rounded-full bg-mg-bg-tertiary text-mg-text-muted flex items-center justify-center text-[10px]">—</span>
                        <?php } ?>
                        <span class="<?php echo $consumed ? 'text-mg-text-secondary' : 'text-mg-text-muted'; ?>">
                            <?php echo $cls_info ? htmlspecialchars(($cls_info['tc_icon'] ? $cls_info['tc_icon'].' ' : '') . $cls_info['tc_name']) : '자유행동'; ?>
                        </span>
                    </div>
                    <?php } ?>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- 우측: 확정 패널 + 진행도 (1/3) -->
        <div class="space-y-6">

            <?php if ($can_edit) { ?>
            <!-- 스케줄 확정 카드 -->
            <div class="bg-mg-bg-secondary p-5 rounded-2xl border border-mg-bg-tertiary relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 rounded-full blur-2xl" style="background:var(--mg-accent);opacity:0.07;"></div>

                <h2 class="text-base font-semibold text-mg-text-primary mb-5">스케줄 확정</h2>

                <!-- 수강료 명세 -->
                <div id="tr-cost-breakdown" class="space-y-3 mb-6">
                    <div class="text-sm text-mg-text-muted text-center py-2">수업을 선택해주세요</div>
                </div>

                <!-- 스트레스 예측 -->
                <div class="p-4 rounded-xl mb-5 border border-mg-bg-tertiary" style="background:rgba(15,23,42,0.5);">
                    <p class="text-xs text-mg-text-muted mb-2">금요일 예상 스트레스</p>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-mg-text-primary font-bold text-lg"><span id="tr-est-stress">—</span><span class="text-xs text-mg-text-muted">/<?php echo $stress_max; ?></span></span>
                        <span id="tr-stress-badge" class="text-xs font-semibold px-2 py-1 rounded hidden"></span>
                    </div>
                    <div class="w-full h-2 bg-mg-bg-tertiary rounded-full overflow-hidden">
                        <div id="tr-stress-bar" class="h-full rounded-full transition-all bg-green-500" style="width:<?php echo min(100, ($stress / $stress_max) * 100); ?>%"></div>
                    </div>
                    <p class="text-[10px] text-mg-text-muted mt-2">스트레스 <?php echo $stress_threshold; ?> 초과 시 수업 효율이 50%로 감소합니다.</p>
                </div>

                <button type="button" id="tr-submit-btn" onclick="trSubmit()"
                        class="w-full py-3 px-4 rounded-xl font-bold text-sm text-white transition-all hover:opacity-90" style="background:var(--mg-accent);">
                    포인트 차감하고 확정하기
                </button>
            </div>
            <?php } ?>

            <!-- 수업 진행도 -->
            <div class="bg-mg-bg-secondary p-5 rounded-2xl border border-mg-bg-tertiary">
                <h2 class="text-sm font-semibold text-mg-text-muted mb-4">누적 진행도</h2>
                <div class="space-y-4">
                    <?php
                    $has_progress = false;
                    $stat_colors = array('stat_hp'=>'#ef4444','stat_str'=>'#f59e0b','stat_dex'=>'#22c55e','stat_int'=>'#6366f1','stat_con'=>'#8b5cf6','stat_luk'=>'#ec4899');
                    $stat_labels = array('stat_hp'=>'HP','stat_str'=>'STR','stat_dex'=>'DEX','stat_int'=>'INT','stat_con'=>'CON','stat_luk'=>'LUK');
                    foreach ($classes as $c) {
                        $c_id = (int)$c['tc_id'];
                        if ($c['tc_stat'] === 'none') continue;
                        $p = isset($progress_map[$c_id]) ? $progress_map[$c_id] : null;
                        $cur_progress = $p ? (float)$p['tp_progress'] : 0;
                        $completed = $p ? (int)$p['tp_completed'] : 0;
                        $required = (int)$c['tc_required'];
                        if ($cur_progress <= 0 && $completed <= 0) continue;
                        $has_progress = true;
                        $pct = $required > 0 ? min(100, ($cur_progress / $required) * 100) : 0;
                        $max_repeat = (int)$c['tc_max_repeat'];
                        $bar_color = isset($stat_colors[$c['tc_stat']]) ? $stat_colors[$c['tc_stat']] : 'var(--mg-accent)';
                        $stat_label = isset($stat_labels[$c['tc_stat']]) ? $stat_labels[$c['tc_stat']] : '';
                    ?>
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="text-mg-text-primary flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo $bar_color; ?>"></span>
                                <?php echo htmlspecialchars($c['tc_name']); ?>
                                <?php if ($stat_label) { ?><span class="text-xs text-mg-text-muted">(<?php echo $stat_label; ?>)</span><?php } ?>
                            </span>
                            <?php if ($pct >= 100) { ?>
                            <span class="text-xs font-bold text-green-400">달성!</span>
                            <?php } else { ?>
                            <span class="text-xs text-mg-text-muted"><?php echo number_format($cur_progress, 1); ?> / <?php echo $required; ?></span>
                            <?php } ?>
                        </div>
                        <div class="w-full h-1.5 bg-mg-bg-tertiary rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all" style="width:<?php echo $pct; ?>%;background:<?php echo $pct >= 100 ? '#22c55e' : $bar_color; ?>;"></div>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if (!$has_progress) { ?>
                    <div class="text-sm text-mg-text-muted text-center py-4">아직 수강 기록이 없습니다.</div>
                    <?php } ?>
                </div>
            </div>

        </div>
    </div>

    <!-- 스탯 확인 모달 -->
    <div id="tr-stat-modal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.6);" onclick="if(event.target===this)this.classList.add('hidden')">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary w-full max-w-md overflow-hidden">
                <div class="px-5 py-4 border-b border-mg-bg-tertiary flex justify-between items-center">
                    <h3 class="font-bold text-mg-text-primary">현재 스탯</h3>
                    <button type="button" onclick="document.getElementById('tr-stat-modal').classList.add('hidden')" class="text-mg-text-muted hover:text-mg-text-primary text-xl leading-none">&times;</button>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <?php
                        $_stat_labels_modal = array(
                            'stat_hp'  => array('HP', '체력', '#ef4444'),
                            'stat_str' => array('STR', '힘', '#f59e0b'),
                            'stat_dex' => array('DEX', '민첩', '#22c55e'),
                            'stat_int' => array('INT', '지능', '#6366f1'),
                        );
                        foreach ($_stat_labels_modal as $skey => $slabel) {
                            $sval = (int)($stat[$skey] ?? 0);
                        ?>
                        <div class="text-center p-3 rounded-lg bg-mg-bg-primary/50">
                            <div class="text-xs font-bold" style="font-family:'Bebas Neue',monospace;letter-spacing:0.1em;color:<?php echo $slabel[2]; ?>;"><?php echo $slabel[0]; ?></div>
                            <div class="text-lg font-bold text-mg-text-primary my-1"><?php echo $sval; ?></div>
                            <div class="text-[10px] text-mg-text-muted"><?php echo $slabel[1]; ?></div>
                        </div>
                        <?php } ?>
                    </div>
                    <!-- 스트레스 -->
                    <div class="mt-4 flex items-center gap-3">
                        <span class="text-xs text-mg-text-muted" style="min-width:52px;">스트레스</span>
                        <div class="flex-1 h-2 rounded-full bg-mg-bg-tertiary overflow-hidden">
                            <div class="h-full rounded-full transition-all <?php echo $stress >= $stress_threshold ? 'bg-red-500' : ($stress >= $stress_threshold * 0.7 ? 'bg-yellow-500' : 'bg-green-500'); ?>"
                                 style="width:<?php echo min(100, ($stress / $stress_max) * 100); ?>%"></div>
                        </div>
                        <span class="text-xs font-bold <?php echo $stress >= $stress_threshold ? 'text-red-400' : 'text-mg-text-primary'; ?>" style="min-width:36px;text-align:right;"><?php echo $stress; ?>/<?php echo $stress_max; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div><!-- /training-app -->

<?php if ($can_edit) { ?>
<script>
(function() {
    var classData = <?php echo json_encode(array_values($classes)); ?>;
    var classMap = {};
    classData.forEach(function(c) { classMap[c.tc_id] = c; });

    var currentStress = <?php echo (int)$stress; ?>;
    var stressMax = <?php echo (int)$stress_max; ?>;
    var stressThreshold = <?php echo (int)$stress_threshold; ?>;
    var stressFree = <?php echo (int)mg_config('training_stress_free', '10'); ?>;
    var stressWeekend = <?php echo (int)mg_config('training_stress_weekend', '40'); ?>;
    var mbPoint = <?php echo (int)$mb_point; ?>;
    var apiUrl = '<?php echo $api_url; ?>';
    var chId = <?php echo $selected_ch_id; ?>;
    var editYear = <?php echo $edit_year; ?>;
    var editWeek = <?php echo $edit_week; ?>;

    function getSlots() {
        var selects = document.querySelectorAll('.tr-slot-select');
        var arr = [];
        selects.forEach(function(s) { arr.push(parseInt(s.value) || 0); });
        return arr;
    }

    window.trUpdateCost = function() {
        var slots = getSlots();
        var cost = 0;
        var stress = currentStress;
        var counts = {};

        for (var i = 0; i < 15; i++) {
            var tcId = slots[i];
            if (tcId > 0 && classMap[tcId]) {
                cost += parseInt(classMap[tcId].tc_cost) || 0;
                stress += parseInt(classMap[tcId].tc_stress) || 0;
                counts[tcId] = (counts[tcId] || 0) + 1;
            } else {
                stress -= stressFree;
            }
            if (stress > stressMax) stress = stressMax;
            if (stress < 0) stress = 0;
        }
        stress -= stressWeekend * 2;
        if (stress < 0) stress = 0;

        // 수강료 명세
        var bd = document.getElementById('tr-cost-breakdown');
        if (bd) {
            var h = '';
            var hasItems = false;
            for (var tid in counts) {
                hasItems = true;
                var c = classMap[tid];
                var itemCost = (parseInt(c.tc_cost) || 0) * counts[tid];
                h += '<div class="flex justify-between items-center text-sm">';
                h += '<span class="text-mg-text-muted">' + (c.tc_icon || '') + ' ' + c.tc_name + ' (x' + counts[tid] + ')</span>';
                h += '<span class="text-mg-text-primary">' + itemCost.toLocaleString() + ' P</span></div>';
            }
            if (hasItems) {
                h += '<hr style="border-color:var(--mg-bg-tertiary);">';
                h += '<div class="flex justify-between items-center"><span class="font-bold text-mg-text-primary">총 수강료</span>';
                h += '<span class="font-bold text-mg-accent text-lg">- ' + cost.toLocaleString() + ' P</span></div>';
            } else {
                h = '<div class="text-sm text-mg-text-muted text-center py-2">수업을 선택해주세요</div>';
            }
            bd.innerHTML = h;
        }

        // 스트레스 예측
        var estEl = document.getElementById('tr-est-stress');
        estEl.textContent = stress;

        var bar = document.getElementById('tr-stress-bar');
        if (bar) {
            bar.style.width = Math.min(100, (stress / stressMax) * 100) + '%';
            bar.className = 'h-full rounded-full transition-all ' + (stress >= stressThreshold ? 'bg-red-500' : (stress >= stressThreshold * 0.7 ? 'bg-yellow-500' : 'bg-green-500'));
        }

        var badge = document.getElementById('tr-stress-badge');
        if (badge) {
            if (stress >= 100) {
                badge.textContent = '강제 휴식!';
                badge.className = 'text-xs font-semibold px-2 py-1 rounded text-red-400';
                badge.style.background = 'rgba(239,68,68,0.1)';
            } else if (stress >= stressThreshold) {
                badge.textContent = '효율 저하 주의';
                badge.className = 'text-xs font-semibold px-2 py-1 rounded text-yellow-400';
                badge.style.background = 'rgba(234,179,8,0.1)';
            } else {
                badge.className = 'text-xs font-semibold px-2 py-1 rounded hidden';
            }
        }

        // 버튼 상태
        var btn = document.getElementById('tr-submit-btn');
        if (cost > mbPoint) {
            btn.disabled = true;
            btn.style.background = '#4b5563';
            btn.style.cursor = 'not-allowed';
            btn.className = 'w-full py-3 px-4 rounded-xl font-bold text-sm text-gray-400 transition-all';
            btn.title = '포인트가 부족합니다';
        } else {
            btn.disabled = false;
            btn.style.background = 'var(--mg-accent)';
            btn.style.cursor = 'pointer';
            btn.className = 'w-full py-3 px-4 rounded-xl font-bold text-sm text-white transition-all hover:opacity-90';
            btn.title = '';
        }
    };

    window.trSubmit = function() {
        var slots = getSlots();
        var costEl = document.getElementById('tr-total-cost');
        var cost = parseInt(costEl.textContent.replace(/[^0-9]/g, '')) || 0;

        if (cost > mbPoint) {
            alert('포인트가 부족합니다.');
            return;
        }

        if (!confirm('수강료 ' + cost.toLocaleString() + 'P가 차감됩니다. 확정하시겠습니까?')) return;

        var btn = document.getElementById('tr-submit-btn');
        btn.disabled = true;
        btn.textContent = '처리 중...';

        var fd = new FormData();
        fd.append('action', 'save_schedule');
        fd.append('ch_id', chId);
        fd.append('year', editYear);
        fd.append('week', editWeek);
        fd.append('slots', JSON.stringify(slots));

        fetch(apiUrl, { method: 'POST', body: fd })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    alert(data.message || '스케줄이 확정되었습니다.');
                    location.reload();
                } else {
                    alert(data.message || '오류가 발생했습니다.');
                    btn.disabled = false;
                    btn.textContent = '확정하기';
                }
            })
            .catch(function() {
                alert('네트워크 오류가 발생했습니다.');
                btn.disabled = false;
                btn.textContent = '확정하기';
            });
    };

    // 초기 비용 계산
    trUpdateCost();
})();
</script>
<?php } ?>

<?php
include_once(G5_THEME_PATH.'/tail.php');
