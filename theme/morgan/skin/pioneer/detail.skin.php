<?php
/**
 * Morgan Edition - 시설 상세 스킨
 */

if (!defined('_GNUBOARD_')) exit;

$is_building = $facility['fc_status'] === 'building';
$is_complete = $facility['fc_status'] === 'complete';
$status_color = $is_complete ? 'var(--mg-success, #10b981)' : ($is_building ? 'var(--mg-accent)' : '#6b7280');
$status_label = $is_complete ? '완공' : ($is_building ? '건설 중' : '대기');
?>

<div class="mg-inner">
    <!-- 탭 네비게이션 -->
    <div class="flex gap-2 mb-6 border-b border-mg-bg-tertiary pb-3">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="px-4 py-2 text-sm font-medium text-mg-accent bg-mg-accent/10 rounded-lg">시설 건설</a>
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php?view=expedition" class="px-4 py-2 text-sm font-medium text-mg-text-secondary hover:text-mg-text-primary rounded-lg transition-colors">탐색 파견</a>
    </div>

    <!-- 뒤로 가기 -->
    <div class="mb-4">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="inline-flex items-center text-sm text-mg-text-secondary hover:text-mg-accent transition-colors">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            목록으로
        </a>
    </div>

    <!-- 시설 헤더 카드 -->
    <div class="pn-detail-header" style="border-top-color:<?php echo $status_color; ?>;">
        <?php if ($is_building) { ?>
        <div class="pn-card-stripes"></div>
        <?php } ?>

        <div class="pn-detail-header-inner">
            <div class="flex items-start gap-4">
                <div class="pn-detail-icon">
                    <?php echo mg_icon($facility['fc_icon'] ?: 'building-office', 'w-10 h-10'); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-2">
                        <h1 class="text-xl font-bold text-mg-text-primary"><?php echo htmlspecialchars($facility['fc_name']); ?></h1>
                        <span class="pn-card-badge" style="color:<?php echo $status_color; ?>;border-color:<?php echo $status_color; ?>30;background:<?php echo $status_color; ?>15;">
                            <?php echo $status_label; ?>
                        </span>
                    </div>
                    <p class="text-sm text-mg-text-secondary leading-relaxed"><?php echo nl2br(htmlspecialchars($facility['fc_desc'])); ?></p>
                    <?php if ($is_complete && $facility['fc_complete_date']) { ?>
                    <p class="text-sm text-mg-text-muted mt-2"><?php echo date('Y년 n월 j일', strtotime($facility['fc_complete_date'])); ?> 완공</p>
                    <?php } ?>
                </div>
            </div>

            <?php if ($is_building) { ?>
            <!-- 총 진행률 -->
            <div class="pn-detail-progress">
                <div class="pn-progress-header">
                    <span style="color:var(--mg-text-muted);font-size:0.75rem;">총 진행률</span>
                    <span class="pn-mono" style="font-size:1.1rem;font-weight:700;color:var(--mg-accent);"><?php echo round($facility['progress']['total'], 1); ?>%</span>
                </div>
                <div class="pn-progress-bar" style="height:8px;">
                    <div class="pn-progress-fill pn-progress-animated" style="width:<?php echo $facility['progress']['total']; ?>%;background:var(--mg-accent);"></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <?php if ($is_building) { ?>
    <!-- 자원 투입 -->
    <div class="pn-detail-card">
        <h2 class="pn-detail-section-title">자원 투입</h2>

        <!-- 스테미나 -->
        <div class="pn-contribute-row">
            <div class="pn-contribute-info">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-mg-accent"><?php echo mg_icon('bolt', 'w-5 h-5'); ?></span>
                    <span class="font-medium text-mg-text-primary">스테미나</span>
                    <span class="text-sm text-mg-accent font-bold" style="margin-left:auto;">보유 <?php echo $my_stamina['current']; ?></span>
                </div>
                <div class="flex justify-between text-xs text-mg-text-muted mb-1">
                    <span>필요: <?php echo number_format($facility['fc_stamina_cost']); ?></span>
                    <span>현재: <?php echo number_format($facility['fc_stamina_current']); ?> (<?php echo round($facility['progress']['stamina'], 1); ?>%)</span>
                </div>
                <div class="pn-progress-bar" style="height:5px;margin-bottom:0;">
                    <div class="pn-progress-fill" style="width:<?php echo $facility['progress']['stamina']; ?>%;background:var(--mg-accent);"></div>
                </div>
            </div>
            <div class="pn-contribute-action">
                <input type="number" id="stamina_amount" min="1" max="<?php echo $my_stamina['current']; ?>" value="1"
                       class="pn-input-sm pn-mono" <?php echo $my_stamina['current'] < 1 ? 'disabled' : ''; ?>>
                <button type="button" onclick="contributeStamina()" class="pn-btn-contribute pn-btn-accent" <?php echo $my_stamina['current'] < 1 ? 'disabled' : ''; ?>>투입</button>
            </div>
        </div>

        <!-- 재료 -->
        <?php foreach ($facility['materials'] as $mat) {
            $my_count = 0;
            foreach ($my_materials as $mm) {
                if ($mm['mt_id'] == $mat['mt_id']) { $my_count = $mm['um_count']; break; }
            }
            $mat_progress = $mat['fmc_required'] > 0 ? ($mat['fmc_current'] / $mat['fmc_required']) * 100 : 100;
            $remaining = $mat['fmc_required'] - $mat['fmc_current'];
        ?>
        <div class="pn-contribute-row">
            <div class="pn-contribute-info">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-mg-text-primary"><?php echo mg_icon($mat['mt_icon'], 'w-5 h-5'); ?></span>
                    <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($mat['mt_name']); ?></span>
                    <span class="text-sm text-mg-text-secondary font-bold" style="margin-left:auto;">보유 <?php echo number_format($my_count); ?></span>
                </div>
                <div class="flex justify-between text-xs text-mg-text-muted mb-1">
                    <span>필요: <?php echo number_format($mat['fmc_required']); ?></span>
                    <span>현재: <?php echo number_format($mat['fmc_current']); ?> (<?php echo round($mat_progress, 1); ?>%)</span>
                </div>
                <div class="pn-progress-bar" style="height:5px;margin-bottom:0;">
                    <div class="pn-progress-fill" style="width:<?php echo $mat_progress; ?>%;background:var(--mg-success, #10b981);"></div>
                </div>
            </div>
            <div class="pn-contribute-action">
                <input type="number" id="mat_amount_<?php echo $mat['mt_id']; ?>" min="1" max="<?php echo min($my_count, $remaining); ?>" value="1"
                       class="pn-input-sm pn-mono" <?php echo $my_count < 1 || $remaining < 1 ? 'disabled' : ''; ?>>
                <button type="button" onclick="contributeMaterial(<?php echo $mat['mt_id']; ?>)" class="pn-btn-contribute pn-btn-success"
                        <?php echo $my_count < 1 || $remaining < 1 ? 'disabled' : ''; ?>>투입</button>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 기여 랭킹 / 명예의 전당 -->
    <div class="pn-detail-card">
        <h2 class="pn-detail-section-title"><?php echo $is_complete ? '명예의 전당' : '기여 랭킹'; ?></h2>

        <!-- 탭 -->
        <div class="pn-ranking-tabs" id="ranking-tabs">
            <button type="button" class="ranking-tab active" data-category="stamina">
                <?php echo mg_icon('bolt', 'w-4 h-4'); ?> 스테미나
            </button>
            <?php foreach ($facility['materials'] as $mat) { ?>
            <button type="button" class="ranking-tab" data-category="<?php echo $mat['mt_code']; ?>">
                <?php echo mg_icon($mat['mt_icon'], 'w-4 h-4'); ?> <?php echo htmlspecialchars($mat['mt_name']); ?>
            </button>
            <?php } ?>
        </div>

        <!-- 스테미나 랭킹 -->
        <div class="ranking-panel" id="ranking-stamina">
            <?php if (empty($stamina_ranking)) { ?>
            <p class="text-center text-mg-text-muted py-6">아직 기여 기록이 없습니다.</p>
            <?php } else { ?>
            <div class="pn-ranking-list">
                <?php foreach ($stamina_ranking as $rank) {
                    $medal = '';
                    if ($rank['rank'] == 1) $medal = 'pn-rank-gold';
                    elseif ($rank['rank'] == 2) $medal = 'pn-rank-silver';
                    elseif ($rank['rank'] == 3) $medal = 'pn-rank-bronze';
                ?>
                <div class="pn-rank-item <?php echo $medal; ?>">
                    <span class="pn-rank-num"><?php echo $rank['rank']; ?></span>
                    <span class="pn-rank-name"><?php echo htmlspecialchars($rank['mb_nick'] ?: $rank['mb_name']); ?></span>
                    <span class="pn-rank-amount pn-mono"><?php echo number_format($rank['fh_amount']); ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <!-- 재료별 랭킹 -->
        <?php foreach ($facility['materials'] as $mat) {
            $rankings = $material_rankings[$mat['mt_code']] ?? [];
        ?>
        <div class="ranking-panel" id="ranking-<?php echo $mat['mt_code']; ?>" style="display:none;">
            <?php if (empty($rankings)) { ?>
            <p class="text-center text-mg-text-muted py-6">아직 기여 기록이 없습니다.</p>
            <?php } else { ?>
            <div class="pn-ranking-list">
                <?php foreach ($rankings as $rank) {
                    $medal = '';
                    if ($rank['rank'] == 1) $medal = 'pn-rank-gold';
                    elseif ($rank['rank'] == 2) $medal = 'pn-rank-silver';
                    elseif ($rank['rank'] == 3) $medal = 'pn-rank-bronze';
                ?>
                <div class="pn-rank-item <?php echo $medal; ?>">
                    <span class="pn-rank-num"><?php echo $rank['rank']; ?></span>
                    <span class="pn-rank-name"><?php echo htmlspecialchars($rank['mb_nick'] ?: $rank['mb_name']); ?></span>
                    <span class="pn-rank-amount pn-mono"><?php echo number_format($rank['fh_amount']); ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>

<style>
.pn-mono { font-family: 'Consolas', 'Monaco', monospace; }

/* 헤더 카드 */
.pn-detail-header {
    position: relative;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-top: 2px solid;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 1.25rem;
    clip-path: polygon(0 0, 100% 0, 100% calc(100% - 14px), calc(100% - 14px) 100%, 0 100%);
}
.pn-detail-header-inner {
    position: relative;
    z-index: 1;
    padding: 1.5rem;
}
.pn-detail-icon {
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    border-radius: 8px;
    background: var(--mg-bg-tertiary);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--mg-text-primary);
}
.pn-detail-progress {
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid var(--mg-bg-tertiary);
}
.pn-card-badge {
    flex-shrink: 0; font-size: 0.65rem; font-weight: 700;
    padding: 0.2rem 0.5rem; border: 1px solid; border-radius: 2px; white-space: nowrap;
}

/* 빗살무늬 */
.pn-card-stripes {
    position: absolute; inset: 0; pointer-events: none; z-index: 0;
    background-image: repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(245,159,10,0.04) 10px, rgba(245,159,10,0.04) 20px);
}

/* 프로그레스 */
.pn-progress-header { display: flex; justify-content: space-between; font-size: 0.7rem; margin-bottom: 0.35rem; }
.pn-progress-bar { width: 100%; height: 6px; background: rgba(0,0,0,0.5); border: 1px solid var(--mg-bg-tertiary); overflow: hidden; margin-bottom: 0.75rem; }
.pn-progress-fill { height: 100%; transition: width 0.5s; }
.pn-progress-animated {
    background-image: linear-gradient(45deg, rgba(255,255,255,.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,.15) 50%, rgba(255,255,255,.15) 75%, transparent 75%, transparent);
    background-size: 1rem 1rem;
    animation: pn-stripe-move 1s linear infinite;
}
@keyframes pn-stripe-move { 0% { background-position: 0 0; } 100% { background-position: 1rem 1rem; } }

/* 섹션 카드 */
.pn-detail-card {
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1.25rem;
}
.pn-detail-section-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--mg-text-primary);
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}

/* 자원 투입 행 */
.pn-contribute-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--mg-bg-primary);
    border-radius: 8px;
    margin-bottom: 0.75rem;
}
.pn-contribute-row:last-child { margin-bottom: 0; }
@media (min-width: 640px) {
    .pn-contribute-row {
        flex-direction: row;
        align-items: flex-end;
    }
}
.pn-contribute-info { flex: 1; min-width: 0; }
.pn-contribute-action {
    display: flex;
    gap: 0.35rem;
    flex-shrink: 0;
}
.pn-input-sm {
    width: 60px;
    padding: 0.4rem 0.5rem;
    text-align: center;
    font-size: 0.85rem;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    color: var(--mg-text-primary);
    border-radius: 4px;
    outline: none;
}
.pn-input-sm:focus { border-color: var(--mg-accent); }
.pn-input-sm:disabled { opacity: 0.4; }

.pn-btn-contribute {
    padding: 0.4rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: opacity 0.2s;
    white-space: nowrap;
}
.pn-btn-contribute:disabled { opacity: 0.35; cursor: default; }
.pn-btn-accent { background: var(--mg-accent); color: var(--mg-bg-primary); }
.pn-btn-accent:hover:not(:disabled) { opacity: 0.85; }
.pn-btn-success { background: var(--mg-success, #10b981); color: #fff; }
.pn-btn-success:hover:not(:disabled) { opacity: 0.85; }

/* 랭킹 탭 */
.pn-ranking-tabs {
    display: flex;
    gap: 0.35rem;
    margin-bottom: 1rem;
    overflow-x: auto;
    padding-bottom: 0.25rem;
}
.ranking-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 999px;
    border: none;
    cursor: pointer;
    white-space: nowrap;
    background: var(--mg-bg-tertiary);
    color: var(--mg-text-secondary);
    transition: all 0.2s;
}
.ranking-tab:hover { color: var(--mg-text-primary); }
.ranking-tab.active {
    background: var(--mg-accent);
    color: #fff;
}

/* 랭킹 리스트 */
.pn-ranking-list { display: flex; flex-direction: column; gap: 0.35rem; }
.pn-rank-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.75rem;
    border-radius: 8px;
    border: 1px solid var(--mg-bg-tertiary);
    background: var(--mg-bg-primary);
}
.pn-rank-num {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--mg-bg-tertiary);
    font-size: 0.8rem;
    font-weight: 700;
    color: var(--mg-text-secondary);
    flex-shrink: 0;
}
.pn-rank-name { flex: 1; font-weight: 500; color: var(--mg-text-primary); font-size: 0.9rem; }
.pn-rank-amount { font-weight: 700; color: var(--mg-accent); font-size: 0.9rem; }

/* 메달 색상 */
.pn-rank-gold { border-color: rgba(234,179,8,0.3); background: rgba(234,179,8,0.06); }
.pn-rank-gold .pn-rank-num { background: rgba(234,179,8,0.2); color: #eab308; }
.pn-rank-silver { border-color: rgba(156,163,175,0.3); background: rgba(156,163,175,0.06); }
.pn-rank-silver .pn-rank-num { background: rgba(156,163,175,0.2); color: #9ca3af; }
.pn-rank-bronze { border-color: rgba(249,115,22,0.3); background: rgba(249,115,22,0.06); }
.pn-rank-bronze .pn-rank-num { background: rgba(249,115,22,0.2); color: #f97316; }
</style>

<script>
// 탭 전환
document.querySelectorAll('.ranking-tab').forEach(function(tab) {
    tab.addEventListener('click', function() {
        var category = this.dataset.category;

        document.querySelectorAll('.ranking-tab').forEach(function(t) { t.classList.remove('active'); });
        this.classList.add('active');

        document.querySelectorAll('.ranking-panel').forEach(function(p) { p.style.display = 'none'; });
        document.getElementById('ranking-' + category).style.display = '';
    });
});

// 스테미나 투입
function contributeStamina() {
    var amount = parseInt(document.getElementById('stamina_amount').value) || 0;
    if (amount < 1) { alert('투입량을 입력해주세요.'); return; }

    var formData = new FormData();
    formData.append('fc_id', '<?php echo $fc_id; ?>');
    formData.append('type', 'stamina');
    formData.append('amount', amount);

    fetch('<?php echo G5_BBS_URL; ?>/pioneer_contribute.php', { method: 'POST', body: formData })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (typeof showToast === 'function') showToast(data.message, data.success ? 'success' : 'error');
        else alert(data.message);
        if (data.success) location.reload();
    })
    .catch(function() { alert('오류가 발생했습니다.'); });
}

// 재료 투입
function contributeMaterial(mt_id) {
    var amount = parseInt(document.getElementById('mat_amount_' + mt_id).value) || 0;
    if (amount < 1) { alert('투입량을 입력해주세요.'); return; }

    var formData = new FormData();
    formData.append('fc_id', '<?php echo $fc_id; ?>');
    formData.append('type', 'material');
    formData.append('mt_id', mt_id);
    formData.append('amount', amount);

    fetch('<?php echo G5_BBS_URL; ?>/pioneer_contribute.php', { method: 'POST', body: formData })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (typeof showToast === 'function') showToast(data.message, data.success ? 'success' : 'error');
        else alert(data.message);
        if (data.success) location.reload();
    })
    .catch(function() { alert('오류가 발생했습니다.'); });
}
</script>
