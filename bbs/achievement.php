<?php
/**
 * Morgan Edition - 업적 목록 (유저 페이지)
 */

include_once('./_common.php');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$mb_id = $member['mb_id'];

// AJAX: 쇼케이스 저장
if (isset($_POST['ajax_save_display'])) {
    header('Content-Type: application/json');
    $slots = isset($_POST['slots']) ? $_POST['slots'] : array();
    $slot_ids = array();
    foreach ($slots as $s) {
        $slot_ids[] = (int)$s;
    }
    $result = mg_save_achievement_display($mb_id, $slot_ids);
    echo json_encode(array('success' => true));
    exit;
}

// 카테고리
$categories = mg_achievement_categories();
$filter_cat = isset($_GET['category']) ? $_GET['category'] : '';

// 전체 업적 목록 + 유저 진행도
$achievements = mg_get_user_achievements($mb_id);

// 전체 통계
$total_count = 0;
$completed_count = 0;
foreach ($achievements as $ac) {
    if (!$ac['ac_hidden'] || $ac['ua_progress'] > 0) {
        $total_count++;
        if ($ac['ua_completed']) $completed_count++;
    }
}
$completion_pct = $total_count > 0 ? round(($completed_count / $total_count) * 100) : 0;

// 카테고리별 필터
$filtered = array();
foreach ($achievements as $ac) {
    // 숨김 업적: 진행이 없으면 표시 안함
    if ($ac['ac_hidden'] && !$ac['ua_progress'] && !$ac['ua_completed']) continue;
    if ($filter_cat && $ac['ac_category'] !== $filter_cat) continue;
    $filtered[] = $ac;
}

// 단계형 업적: 각 단계 정보 로드
$tier_cache = array();
foreach ($filtered as $ac) {
    if ($ac['ac_type'] === 'progressive' && !isset($tier_cache[$ac['ac_id']])) {
        $tier_cache[$ac['ac_id']] = mg_get_achievement_tiers($ac['ac_id']);
    }
}

// 쇼케이스 데이터
$display = mg_get_achievement_display($mb_id);
$display_ids = array();
foreach ($display as $d) {
    $display_ids[] = (int)$d['ac_id'];
}

// 쇼케이스에 넣을 수 있는 업적 (달성한 것만)
$displayable = array();
foreach ($achievements as $ac) {
    if ($ac['ua_completed'] || ($ac['ac_type'] === 'progressive' && $ac['ua_tier'] > 0)) {
        $displayable[] = $ac;
    }
}

$rarity_labels = array(
    'common' => 'Common',
    'uncommon' => 'Uncommon',
    'rare' => 'Rare',
    'epic' => 'Epic',
    'legendary' => 'Legendary',
);
$rarity_colors = array(
    'common' => '#949ba4',
    'uncommon' => '#22c55e',
    'rare' => '#3b82f6',
    'epic' => '#a855f7',
    'legendary' => '#f59e0b',
);

$g5['title'] = '업적';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 헤더: 전체 진행도 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h1 class="text-xl font-bold text-mg-text-primary">업적</h1>
            <span class="text-mg-text-secondary text-sm">달성: <?php echo $completed_count; ?> / <?php echo $total_count; ?></span>
        </div>
        <div class="w-full bg-mg-bg-tertiary rounded-full h-3 overflow-hidden">
            <div class="h-full rounded-full transition-all" style="width:<?php echo $completion_pct; ?>%;background:var(--mg-accent, #f59f0a);"></div>
        </div>
        <div class="text-right mt-1 text-xs text-mg-text-muted"><?php echo $completion_pct; ?>%</div>
    </div>

    <!-- 쇼케이스 관리 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-mg-text-primary">프로필 쇼케이스</h2>
            <button type="button" onclick="toggleShowcaseEdit()" class="text-sm text-mg-accent hover:underline" id="btn-showcase-toggle">편집</button>
        </div>
        <!-- 현재 쇼케이스 -->
        <div id="showcase-display" class="flex gap-3 flex-wrap">
            <?php if (empty($display)) { ?>
            <p class="text-sm text-mg-text-muted">프로필에 표시할 업적을 선택하세요.</p>
            <?php } else { foreach ($display as $d) {
                $icon = $d['tier_icon'] ?: ($d['ac_icon'] ?: '');
                $name = $d['tier_name'] ?: $d['ac_name'];
                $rarity = $d['ac_rarity'] ?: 'common';
                $r_color = $rarity_colors[$rarity] ?? '#949ba4';
            ?>
            <div class="flex flex-col items-center p-2 rounded-lg" style="border:2px solid <?php echo $r_color; ?>;min-width:70px;">
                <?php if ($icon) { ?>
                <?php echo mg_icon($icon, 'w-10 h-10'); ?>
                <?php } else { ?>
                <svg class="w-8 h-8" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3h14l-1.405 4.544A5.001 5.001 0 0112 13a5.001 5.001 0 01-5.595-5.456L5 3zm7 10v4m-4 4h8m-4-4v4"/></svg>
                <?php } ?>
                <span class="text-xs text-mg-text-secondary mt-1 text-center leading-tight"><?php echo htmlspecialchars($name); ?></span>
                <span class="text-[10px]" style="color:<?php echo $r_color; ?>;"><?php echo $rarity_labels[$rarity] ?? ''; ?></span>
            </div>
            <?php } } ?>
        </div>
        <!-- 편집 UI -->
        <div id="showcase-edit" style="display:none;" class="mt-4">
            <p class="text-sm text-mg-text-muted mb-2">프로필에 표시할 업적을 최대 5개 선택하세요.</p>
            <div class="flex flex-wrap gap-2 mb-3">
                <?php foreach ($displayable as $ac) {
                    $checked = in_array($ac['ac_id'], $display_ids) ? 'checked' : '';
                    $name = $ac['ac_name'];
                    if ($ac['ac_type'] === 'progressive' && isset($tier_cache[$ac['ac_id']])) {
                        foreach ($tier_cache[$ac['ac_id']] as $t) {
                            if ((int)$t['at_level'] == (int)$ac['ua_tier']) {
                                $name = $t['at_name'];
                                break;
                            }
                        }
                    }
                ?>
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-mg-bg-tertiary cursor-pointer text-sm hover:bg-mg-bg-primary transition-colors">
                    <input type="checkbox" class="showcase-check" value="<?php echo $ac['ac_id']; ?>" <?php echo $checked; ?> onchange="checkShowcaseLimit(this)">
                    <span class="text-mg-text-secondary"><?php echo htmlspecialchars($name); ?></span>
                </label>
                <?php } ?>
                <?php if (empty($displayable)) { ?>
                <p class="text-sm text-mg-text-muted">달성한 업적이 없습니다.</p>
                <?php } ?>
            </div>
            <button type="button" onclick="saveShowcase()" class="text-sm px-4 py-1.5 rounded-lg text-black font-medium" style="background:var(--mg-accent, #f59f0a);">저장</button>
        </div>
    </div>

    <!-- 카테고리 탭 -->
    <div class="flex gap-2 flex-wrap mb-4">
        <a href="?<?php echo $filter_cat ? '' : ''; ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors <?php echo !$filter_cat ? 'text-black' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>" <?php if (!$filter_cat) echo 'style="background:var(--mg-accent, #f59f0a);"'; ?>>전체</a>
        <?php foreach ($categories as $ck => $cv) { ?>
        <a href="?category=<?php echo $ck; ?>" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors <?php echo $filter_cat == $ck ? 'text-black' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>" <?php if ($filter_cat == $ck) echo 'style="background:var(--mg-accent, #f59f0a);"'; ?>><?php echo $cv; ?></a>
        <?php } ?>
    </div>

    <!-- 업적 목록 -->
    <div class="space-y-3">
        <?php if (empty($filtered)) { ?>
        <div class="text-center py-12 text-mg-text-muted">이 카테고리에 업적이 없습니다.</div>
        <?php } ?>

        <?php foreach ($filtered as $ac) {
            $rarity = $ac['ac_rarity'] ?: 'common';
            $r_color = $rarity_colors[$rarity] ?? '#949ba4';
            $progress = (int)($ac['ua_progress'] ?? 0);
            $current_tier = (int)($ac['ua_tier'] ?? 0);
            $completed = (int)($ac['ua_completed'] ?? 0);

            // 진행 바 계산
            $target = 0;
            $tier_name = '';
            $next_tier_info = '';

            if ($ac['ac_type'] === 'onetime') {
                $cond = json_decode($ac['ac_condition'], true);
                $target = (int)($cond['target'] ?? 1);
            } else {
                // 단계형: 현재/다음 단계 정보
                $tiers = $tier_cache[$ac['ac_id']] ?? array();
                $current_t = null;
                $next_t = null;
                foreach ($tiers as $t) {
                    if ((int)$t['at_level'] <= $current_tier) {
                        $current_t = $t;
                    }
                    if ((int)$t['at_level'] == $current_tier + 1) {
                        $next_t = $t;
                    }
                }
                if ($current_t) $tier_name = $current_t['at_name'];
                if ($next_t) {
                    $target = (int)$next_t['at_target'];
                    $next_tier_info = $next_t['at_name'] . ' (' . number_format($next_t['at_target']) . ')';
                } elseif (!empty($tiers)) {
                    // 아직 첫 단계도 안 됨
                    $first = $tiers[0];
                    $target = (int)$first['at_target'];
                    $next_tier_info = $first['at_name'] . ' (' . number_format($first['at_target']) . ')';
                }
                if ($completed) {
                    $target = $progress; // 이미 완료
                }
            }

            $pct = $target > 0 ? min(100, round(($progress / $target) * 100)) : 0;
            $icon = $ac['ac_icon'] ?: '';
            $display_name = $tier_name ?: $ac['ac_name'];

            // 상태
            $status = '미달성';
            $status_class = 'text-mg-text-muted';
            if ($completed) {
                $status = '달성';
                $status_class = 'text-green-400';
            } elseif ($progress > 0) {
                $status = '진행중';
                $status_class = 'text-mg-accent';
            }
        ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 flex gap-4 items-start">
            <!-- 아이콘 -->
            <div class="flex-shrink-0 w-14 h-14 rounded-lg flex items-center justify-center" style="border:2px solid <?php echo $r_color; ?>;background:rgba(0,0,0,0.2);">
                <?php if ($icon && ($completed || $progress > 0)) { ?>
                <?php echo mg_icon($icon, 'w-10 h-10'); ?>
                <?php } elseif ($completed || $progress > 0) { ?>
                <svg class="w-8 h-8" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3h14l-1.405 4.544A5.001 5.001 0 0112 13a5.001 5.001 0 01-5.595-5.456L5 3zm7 10v4m-4 4h8m-4-4v4"/></svg>
                <?php } else { ?>
                <span class="text-2xl opacity-30">?</span>
                <?php } ?>
            </div>

            <!-- 내용 -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-semibold text-mg-text-primary">
                            <?php if ($ac['ac_hidden'] && !$completed && !$progress) { ?>
                            ???
                            <?php } else { ?>
                            <?php echo htmlspecialchars($display_name); ?>
                            <?php } ?>
                        </h3>
                        <?php if ($ac['ac_type'] === 'progressive' && $current_tier > 0) { ?>
                        <span class="text-xs px-1.5 py-0.5 rounded" style="background:rgba(<?php echo $rarity == 'legendary' ? '245,158,11' : ($rarity == 'epic' ? '168,85,247' : ($rarity == 'rare' ? '59,130,246' : '148,155,164')); ?>,0.2);color:<?php echo $r_color; ?>;">Lv.<?php echo $current_tier; ?></span>
                        <?php } ?>
                    </div>
                    <span class="text-sm font-medium <?php echo $status_class; ?>"><?php echo $status; ?></span>
                </div>

                <p class="text-sm text-mg-text-muted mb-2">
                    <?php echo htmlspecialchars($ac['ac_hidden'] && !$completed && !$progress ? '???' : $ac['ac_desc']); ?>
                </p>

                <!-- 진행 바 -->
                <?php if (!$completed) { ?>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-mg-bg-tertiary rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full transition-all" style="width:<?php echo $pct; ?>%;background:<?php echo $r_color; ?>;"></div>
                    </div>
                    <span class="text-xs text-mg-text-muted flex-shrink-0"><?php echo number_format($progress); ?>/<?php echo number_format($target); ?></span>
                </div>
                <?php if ($next_tier_info) { ?>
                <p class="text-xs text-mg-text-muted mt-1">다음: <?php echo htmlspecialchars($next_tier_info); ?></p>
                <?php } ?>
                <?php } else { ?>
                <div class="flex items-center gap-2">
                    <div class="flex-1 bg-mg-bg-tertiary rounded-full h-2 overflow-hidden">
                        <div class="h-full rounded-full" style="width:100%;background:<?php echo $r_color; ?>;"></div>
                    </div>
                    <span class="text-xs flex-shrink-0" style="color:<?php echo $r_color; ?>;"><?php echo $rarity_labels[$rarity] ?? ''; ?></span>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<script>
function toggleShowcaseEdit() {
    var el = document.getElementById('showcase-edit');
    var btn = document.getElementById('btn-showcase-toggle');
    if (el.style.display === 'none') {
        el.style.display = '';
        btn.textContent = '닫기';
    } else {
        el.style.display = 'none';
        btn.textContent = '편집';
    }
}

function checkShowcaseLimit(cb) {
    var checked = document.querySelectorAll('.showcase-check:checked');
    if (checked.length > 5) {
        cb.checked = false;
        alert('최대 5개까지 선택할 수 있습니다.');
    }
}

function saveShowcase() {
    var checked = document.querySelectorAll('.showcase-check:checked');
    var fd = new FormData();
    fd.append('ajax_save_display', '1');
    checked.forEach(function(cb) { fd.append('slots[]', cb.value); });

    fetch('<?php echo G5_BBS_URL; ?>/achievement.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert('저장 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
