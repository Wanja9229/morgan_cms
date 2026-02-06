<?php
/**
 * Morgan Edition - 시설 상세 스킨
 */

if (!defined('_GNUBOARD_')) exit;

$is_building = $facility['fc_status'] === 'building';
$is_complete = $facility['fc_status'] === 'complete';
?>

<div class="max-w-4xl mx-auto">
    <!-- 뒤로 가기 -->
    <div class="mb-4">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="inline-flex items-center text-mg-text-secondary hover:text-mg-accent">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            목록으로
        </a>
    </div>

    <!-- 시설 헤더 -->
    <div class="card mb-6">
        <div class="flex items-start gap-4">
            <?php if ($facility['fc_image']) { ?>
            <img src="<?php echo htmlspecialchars($facility['fc_image']); ?>" alt="" class="w-24 h-24 rounded-lg object-cover">
            <?php } else { ?>
            <div class="w-24 h-24 rounded-lg bg-mg-bg-tertiary flex items-center justify-center text-mg-text-primary">
                <?php echo mg_icon($facility['fc_icon'] ?: 'building-office', 'w-12 h-12'); ?>
            </div>
            <?php } ?>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                    <h1 class="text-2xl font-bold text-mg-text-primary"><?php echo htmlspecialchars($facility['fc_name']); ?></h1>
                    <?php if ($is_complete) { ?>
                    <span class="px-2 py-1 text-xs font-medium rounded bg-mg-success/10 text-mg-success">완공</span>
                    <?php } elseif ($is_building) { ?>
                    <span class="px-2 py-1 text-xs font-medium rounded bg-mg-accent/10 text-mg-accent">건설 중</span>
                    <?php } ?>
                </div>
                <p class="text-mg-text-secondary"><?php echo nl2br(htmlspecialchars($facility['fc_desc'])); ?></p>
                <?php if ($is_complete && $facility['fc_complete_date']) { ?>
                <p class="text-sm text-mg-text-muted mt-2"><?php echo date('Y년 n월 j일', strtotime($facility['fc_complete_date'])); ?> 완공</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <?php if ($is_building) { ?>
    <!-- 내 자원 + 투입 UI -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">자원 투입</h2>

        <!-- 내 노동력 -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-mg-accent"><?php echo mg_icon('bolt', 'w-6 h-6'); ?></span>
                    <span class="font-medium text-mg-text-primary">노동력</span>
                </div>
                <span class="text-mg-accent font-bold"><?php echo $my_stamina['current']; ?> / <?php echo $my_stamina['max']; ?></span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg">
                <div class="flex-1">
                    <div class="flex justify-between text-xs text-mg-text-muted mb-1">
                        <span>필요: <?php echo number_format($facility['fc_stamina_cost']); ?></span>
                        <span>현재: <?php echo number_format($facility['fc_stamina_current']); ?> (<?php echo round($facility['progress']['stamina'], 1); ?>%)</span>
                    </div>
                    <div class="h-2 bg-mg-bg-tertiary rounded-full overflow-hidden">
                        <div class="h-full bg-mg-accent" style="width: <?php echo $facility['progress']['stamina']; ?>%"></div>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <input type="number" id="stamina_amount" min="1" max="<?php echo $my_stamina['current']; ?>" value="1"
                           class="input w-16 text-center text-sm" <?php echo $my_stamina['current'] < 1 ? 'disabled' : ''; ?>>
                    <button type="button" onclick="contributeStamina()" class="btn btn-primary btn-sm" <?php echo $my_stamina['current'] < 1 ? 'disabled' : ''; ?>>
                        투입
                    </button>
                </div>
            </div>
        </div>

        <!-- 재료 -->
        <?php foreach ($facility['materials'] as $mat) {
            $my_count = 0;
            foreach ($my_materials as $mm) {
                if ($mm['mt_id'] == $mat['mt_id']) {
                    $my_count = $mm['um_count'];
                    break;
                }
            }
            $mat_progress = $mat['fmc_required'] > 0 ? ($mat['fmc_current'] / $mat['fmc_required']) * 100 : 100;
            $remaining = $mat['fmc_required'] - $mat['fmc_current'];
        ?>
        <div class="mb-4 last:mb-0">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                    <span class="text-mg-text-primary"><?php echo mg_icon($mat['mt_icon'], 'w-6 h-6'); ?></span>
                    <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($mat['mt_name']); ?></span>
                </div>
                <span class="text-mg-text-secondary">보유: <span class="font-bold"><?php echo number_format($my_count); ?></span></span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg">
                <div class="flex-1">
                    <div class="flex justify-between text-xs text-mg-text-muted mb-1">
                        <span>필요: <?php echo number_format($mat['fmc_required']); ?></span>
                        <span>현재: <?php echo number_format($mat['fmc_current']); ?> (<?php echo round($mat_progress, 1); ?>%)</span>
                    </div>
                    <div class="h-2 bg-mg-bg-tertiary rounded-full overflow-hidden">
                        <div class="h-full bg-mg-success" style="width: <?php echo $mat_progress; ?>%"></div>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <input type="number" id="mat_amount_<?php echo $mat['mt_id']; ?>" min="1" max="<?php echo min($my_count, $remaining); ?>" value="1"
                           class="input w-16 text-center text-sm" <?php echo $my_count < 1 || $remaining < 1 ? 'disabled' : ''; ?>>
                    <button type="button" onclick="contributeMaterial(<?php echo $mat['mt_id']; ?>)" class="btn btn-success btn-sm"
                            <?php echo $my_count < 1 || $remaining < 1 ? 'disabled' : ''; ?>>
                        투입
                    </button>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 총 진행률 -->
    <?php if ($is_building) { ?>
    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">총 진행률</h2>
        <div class="relative pt-1">
            <div class="flex justify-between text-sm text-mg-text-muted mb-2">
                <span>진행 중...</span>
                <span class="font-bold text-mg-accent"><?php echo round($facility['progress']['total'], 1); ?>%</span>
            </div>
            <div class="h-4 bg-mg-bg-tertiary rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-mg-accent to-mg-success transition-all" style="width: <?php echo $facility['progress']['total']; ?>%"></div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- 기여 랭킹 -->
    <div class="card">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">
            <?php echo $is_complete ? '명예의 전당' : '기여 랭킹'; ?>
        </h2>

        <!-- 탭 -->
        <div class="flex gap-2 mb-4 overflow-x-auto pb-2" id="ranking-tabs">
            <button type="button" class="ranking-tab px-3 py-1.5 text-sm rounded-full bg-mg-accent text-white inline-flex items-center gap-1" data-category="stamina">
                <?php echo mg_icon('bolt', 'w-4 h-4'); ?> 노동력
            </button>
            <?php foreach ($facility['materials'] as $mat) { ?>
            <button type="button" class="ranking-tab px-3 py-1.5 text-sm rounded-full bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-bg-primary inline-flex items-center gap-1" data-category="<?php echo $mat['mt_code']; ?>">
                <?php echo mg_icon($mat['mt_icon'], 'w-4 h-4'); ?> <?php echo htmlspecialchars($mat['mt_name']); ?>
            </button>
            <?php } ?>
        </div>

        <!-- 노동력 랭킹 -->
        <div class="ranking-panel" id="ranking-stamina">
            <?php if (empty($stamina_ranking)) { ?>
            <p class="text-center text-mg-text-muted py-4">아직 기여 기록이 없습니다.</p>
            <?php } else { ?>
            <div class="space-y-2">
                <?php foreach ($stamina_ranking as $rank) {
                    $rank_class = '';
                    if ($rank['rank'] == 1) $rank_class = 'bg-yellow-500/10 border-yellow-500/30';
                    elseif ($rank['rank'] == 2) $rank_class = 'bg-gray-300/10 border-gray-400/30';
                    elseif ($rank['rank'] == 3) $rank_class = 'bg-orange-500/10 border-orange-500/30';
                ?>
                <div class="flex items-center gap-3 p-3 rounded-lg border <?php echo $rank_class ?: 'border-mg-bg-tertiary'; ?>">
                    <span class="w-8 h-8 flex items-center justify-center rounded-full bg-mg-bg-tertiary font-bold text-sm">
                        <?php echo $rank['rank']; ?>
                    </span>
                    <span class="flex-1 font-medium text-mg-text-primary"><?php echo htmlspecialchars($rank['mb_nick'] ?: $rank['mb_name']); ?></span>
                    <span class="text-mg-accent font-bold"><?php echo number_format($rank['fh_amount']); ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <!-- 재료별 랭킹 -->
        <?php foreach ($facility['materials'] as $mat) {
            $rankings = $material_rankings[$mat['mt_code']] ?? [];
        ?>
        <div class="ranking-panel hidden" id="ranking-<?php echo $mat['mt_code']; ?>">
            <?php if (empty($rankings)) { ?>
            <p class="text-center text-mg-text-muted py-4">아직 기여 기록이 없습니다.</p>
            <?php } else { ?>
            <div class="space-y-2">
                <?php foreach ($rankings as $rank) {
                    $rank_class = '';
                    if ($rank['rank'] == 1) $rank_class = 'bg-yellow-500/10 border-yellow-500/30';
                    elseif ($rank['rank'] == 2) $rank_class = 'bg-gray-300/10 border-gray-400/30';
                    elseif ($rank['rank'] == 3) $rank_class = 'bg-orange-500/10 border-orange-500/30';
                ?>
                <div class="flex items-center gap-3 p-3 rounded-lg border <?php echo $rank_class ?: 'border-mg-bg-tertiary'; ?>">
                    <span class="w-8 h-8 flex items-center justify-center rounded-full bg-mg-bg-tertiary font-bold text-sm">
                        <?php echo $rank['rank']; ?>
                    </span>
                    <span class="flex-1 font-medium text-mg-text-primary"><?php echo htmlspecialchars($rank['mb_nick'] ?: $rank['mb_name']); ?></span>
                    <span class="text-mg-success font-bold"><?php echo number_format($rank['fh_amount']); ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>

<script>
// 탭 전환
document.querySelectorAll('.ranking-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        var category = this.dataset.category;

        // 탭 스타일
        document.querySelectorAll('.ranking-tab').forEach(function(t) {
            t.classList.remove('bg-mg-accent', 'text-white');
            t.classList.add('bg-mg-bg-tertiary', 'text-mg-text-secondary');
        });
        this.classList.remove('bg-mg-bg-tertiary', 'text-mg-text-secondary');
        this.classList.add('bg-mg-accent', 'text-white');

        // 패널 표시
        document.querySelectorAll('.ranking-panel').forEach(function(p) {
            p.classList.add('hidden');
        });
        document.getElementById('ranking-' + category).classList.remove('hidden');
    });
});

// 노동력 투입
function contributeStamina() {
    var amount = parseInt(document.getElementById('stamina_amount').value) || 0;
    if (amount < 1) {
        alert('투입량을 입력해주세요.');
        return;
    }

    var formData = new FormData();
    formData.append('fc_id', '<?php echo $fc_id; ?>');
    formData.append('type', 'stamina');
    formData.append('amount', amount);

    fetch('<?php echo G5_BBS_URL; ?>/pioneer_contribute.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            location.reload();
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(function(err) {
        alert('오류가 발생했습니다.');
    });
}

// 재료 투입
function contributeMaterial(mt_id) {
    var amount = parseInt(document.getElementById('mat_amount_' + mt_id).value) || 0;
    if (amount < 1) {
        alert('투입량을 입력해주세요.');
        return;
    }

    var formData = new FormData();
    formData.append('fc_id', '<?php echo $fc_id; ?>');
    formData.append('type', 'material');
    formData.append('mt_id', mt_id);
    formData.append('amount', amount);

    fetch('<?php echo G5_BBS_URL; ?>/pioneer_contribute.php', {
        method: 'POST',
        body: formData
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            if (typeof showToast === 'function') {
                showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            location.reload();
        } else {
            if (typeof showToast === 'function') {
                showToast(data.message, 'error');
            } else {
                alert(data.message);
            }
        }
    })
    .catch(function(err) {
        alert('오류가 발생했습니다.');
    });
}
</script>
