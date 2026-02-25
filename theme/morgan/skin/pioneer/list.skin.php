<?php
/**
 * Morgan Edition - 개척 현황 목록 스킨
 * 카드뷰 + 거점뷰 (맵 마커), 시설 상세 모달 통합
 */

if (!defined('_GNUBOARD_')) exit;
$auto_open_fc_id = isset($auto_open_fc_id) ? (int)$auto_open_fc_id : 0;

// JS 모달에서 사용할 아이콘 HTML을 시설 데이터에 추가
foreach ($facilities as &$_fc) {
    $_fc['fc_icon_html'] = $_fc['fc_icon'] ? mg_icon($_fc['fc_icon'], 'w-8 h-8') : '';
    foreach ($_fc['materials'] as &$_mat) {
        $_mat['mt_icon_html'] = $_mat['mt_icon'] ? mg_icon($_mat['mt_icon'], 'w-4 h-4') : '';
    }
    unset($_mat);
}
unset($_fc);
?>

<div class="mg-inner">
    <!-- 탭 네비게이션 -->
    <div class="flex gap-2 mb-6 border-b border-mg-bg-tertiary pb-3">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="px-4 py-2 text-sm font-medium text-mg-accent bg-mg-accent/10 rounded-lg">시설 건설</a>
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php?view=expedition" class="px-4 py-2 text-sm font-medium text-mg-text-secondary hover:text-mg-text-primary rounded-lg transition-colors">탐색 파견</a>
    </div>

    <!-- 헤더 -->
    <div class="pn-header">
        <h1 class="pn-title">개척 현황</h1>
        <p class="pn-subtitle">커뮤니티와 함께 시설을 건설하고 새로운 기능을 해금하세요</p>
    </div>

    <!-- 자원 + 통계 패널 -->
    <div class="pn-resource-panel">
        <div class="pn-resource-main">
            <h3 class="pn-resource-title">보유 자원</h3>
            <div class="pn-resource-grid">
                <div class="pn-resource-item pn-resource-stamina">
                    <span class="text-mg-accent"><?php echo mg_icon('bolt', 'w-5 h-5'); ?></span>
                    <span class="pn-resource-label">스테미나</span>
                    <span class="pn-resource-value pn-mono"><?php echo $my_stamina['current']; ?>/<?php echo $my_stamina['max']; ?></span>
                </div>
                <?php foreach ($my_materials as $mat) { ?>
                <div class="pn-resource-item">
                    <span class="text-mg-text-primary"><?php echo mg_icon($mat['mt_icon'], 'w-5 h-5'); ?></span>
                    <span class="pn-resource-label"><?php echo htmlspecialchars($mat['mt_name']); ?></span>
                    <span class="pn-resource-value pn-mono"><?php echo number_format($mat['um_count']); ?></span>
                </div>
                <?php } ?>
            </div>
        </div>

        <div class="pn-stats-col">
            <div class="pn-stat-bar pn-stat-building">
                <span class="pn-stat-label">건설 중</span>
                <span class="pn-stat-value pn-mono"><?php echo $building_count; ?></span>
            </div>
            <div class="pn-stat-bar pn-stat-complete">
                <span class="pn-stat-label">완공</span>
                <span class="pn-stat-value pn-mono"><?php echo $complete_count; ?></span>
            </div>
            <div class="pn-stat-bar pn-stat-locked">
                <span class="pn-stat-label">대기</span>
                <span class="pn-stat-value pn-mono"><?php echo count($facilities) - $complete_count - $building_count; ?></span>
            </div>
        </div>
    </div>

    <?php if ($pioneer_view_mode === 'base') { ?>
    <!-- =============================== -->
    <!-- 거점뷰 (맵 이미지 + 마커) -->
    <!-- =============================== -->
    <div id="pioneer-base-view">
        <div id="pn-map-container" class="pn-map-container">
            <img src="<?php echo htmlspecialchars($pioneer_map_image); ?>" id="pn-map-image" class="pn-map-image" alt="거점 이미지" draggable="false">
            <div id="pn-map-markers"></div>
        </div>
    </div>

    <?php } else { ?>
    <!-- =============================== -->
    <!-- 카드뷰 -->
    <!-- =============================== -->
    <div class="pn-card-grid">
        <?php
        foreach ($facilities as $facility) {
            $status_color = '';
            $status_label = '';
            $border_color = '';

            switch ($facility['fc_status']) {
                case 'complete':
                    $status_color = 'var(--mg-success, #10b981)';
                    $status_label = '완공';
                    $border_color = 'var(--mg-success, #10b981)';
                    break;
                case 'building':
                    $status_color = 'var(--mg-accent)';
                    $status_label = '건설 중';
                    $border_color = 'var(--mg-accent)';
                    break;
                default:
                    $status_color = '#6b7280';
                    $status_label = '대기';
                    $border_color = 'var(--mg-bg-tertiary)';
            }

            $is_clickable = $facility['fc_status'] !== 'locked';
        ?>
        <div class="pn-card <?php echo $facility['fc_status'] === 'building' ? 'pn-card-building' : ''; ?> <?php echo $facility['fc_status'] === 'locked' ? 'pn-card-locked' : ''; ?>"
             style="border-top-color:<?php echo $border_color; ?>;"
             <?php if ($is_clickable) { ?>onclick="openFacilityModal(<?php echo $facility['fc_id']; ?>)"<?php } ?>>
            <?php if ($facility['fc_status'] === 'building') { ?>
            <div class="pn-card-stripes"></div>
            <?php } ?>

            <div class="pn-card-inner">
                <div class="pn-card-header">
                    <div class="pn-card-name-row">
                        <?php if ($facility['fc_icon']) { ?>
                        <span class="pn-card-icon"><?php echo mg_icon($facility['fc_icon'], 'w-6 h-6'); ?></span>
                        <?php } ?>
                        <h3 class="pn-card-name"><?php echo htmlspecialchars($facility['fc_name']); ?></h3>
                    </div>
                    <span class="pn-card-badge" style="color:<?php echo $status_color; ?>;border-color:<?php echo $status_color; ?>30;background:<?php echo $status_color; ?>15;">
                        <?php echo $status_label; ?>
                    </span>
                </div>

                <p class="pn-card-desc"><?php echo htmlspecialchars($facility['fc_desc']); ?></p>

                <div class="pn-card-footer">
                    <?php if ($facility['fc_status'] === 'building') { ?>
                    <div class="pn-progress-section">
                        <div class="pn-progress-header">
                            <span style="color:var(--mg-text-muted);font-size:0.75rem;">진행률</span>
                            <span class="pn-mono" style="color:var(--mg-text-primary);"><?php echo round($facility['progress']['total'], 1); ?>%</span>
                        </div>
                        <div class="pn-progress-bar">
                            <div class="pn-progress-fill <?php echo $facility['progress']['total'] < 100 ? 'pn-progress-animated' : ''; ?>" style="width:<?php echo $facility['progress']['total']; ?>%;background:<?php echo $status_color; ?>;"></div>
                        </div>

                        <div class="pn-resource-tags">
                            <span class="pn-resource-tag">
                                <?php echo mg_icon('bolt', 'w-3 h-3'); ?>
                                <span class="pn-mono"><?php echo number_format($facility['fc_stamina_current']); ?>/<?php echo number_format($facility['fc_stamina_cost']); ?></span>
                            </span>
                            <?php foreach (array_slice($facility['materials'], 0, 3) as $mat) { ?>
                            <span class="pn-resource-tag">
                                <?php echo mg_icon($mat['mt_icon'], 'w-3 h-3'); ?>
                                <span class="pn-mono"><?php echo number_format($mat['fmc_current']); ?>/<?php echo number_format($mat['fmc_required']); ?></span>
                            </span>
                            <?php } ?>
                            <?php if (count($facility['materials']) > 3) { ?>
                            <span class="pn-resource-tag pn-mono" style="color:var(--mg-text-muted);">+<?php echo count($facility['materials']) - 3; ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <?php } elseif ($facility['fc_status'] === 'complete') { ?>
                    <div class="pn-complete-footer">
                        <span class="pn-complete-date"><?php echo $facility['fc_complete_date'] ? date('Y-m-d', strtotime($facility['fc_complete_date'])) . ' 완공' : '완공'; ?></span>
                        <?php if ($facility['fc_unlock_type'] === 'board' && $facility['fc_unlock_target']) { ?>
                        <a href="<?php echo G5_BBS_URL.'/board.php?bo_table='.$facility['fc_unlock_target']; ?>" class="pn-link-btn" onclick="event.stopPropagation();">이동 &rarr;</a>
                        <?php } ?>
                    </div>

                    <?php } else { ?>
                    <div class="pn-locked-footer">
                        <?php if ($facility['fc_unlock_type']) {
                            $unlock_labels = array('board'=>'게시판','shop'=>'상점','gift'=>'선물함','achievement'=>'업적','history'=>'연대기','fountain'=>'분수대');
                            $ul = isset($unlock_labels[$facility['fc_unlock_type']]) ? $unlock_labels[$facility['fc_unlock_type']] : $facility['fc_unlock_type'];
                        ?>
                        <span style="color:var(--mg-text-muted);font-size:0.75rem;">해금: <?php echo $ul; ?></span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>

        <?php if (empty($facilities)) { ?>
        <div class="pn-empty">
            <svg class="w-12 h-12" style="color:var(--mg-text-muted);margin:0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            <p class="text-mg-text-muted">등록된 시설이 없습니다.</p>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<!-- =============================== -->
<!-- 시설 상세 모달 -->
<!-- =============================== -->
<div id="pn-modal-overlay" class="pn-modal-overlay" style="display:none;" onclick="if(event.target===this)closeFacilityModal()">
    <div class="pn-modal-panel">
        <button class="pn-modal-close" onclick="closeFacilityModal()" type="button">&times;</button>
        <div id="pn-modal-content">
            <div class="pn-modal-loading">불러오는 중...</div>
        </div>
    </div>
</div>

<style>
/* ============================== */
/* 개척 현황 - 공통 */
/* ============================== */
.pn-mono { font-family: 'Consolas', 'Monaco', monospace; }

.pn-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}
.pn-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--mg-text-primary);
    letter-spacing: -0.02em;
}
.pn-subtitle {
    font-size: 0.85rem;
    color: var(--mg-text-muted);
    margin-top: 0.25rem;
}

/* ============================== */
/* 자원 + 통계 패널 */
/* ============================== */
.pn-resource-panel {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
@media (min-width: 768px) {
    .pn-resource-panel { flex-direction: row; }
}
.pn-resource-main {
    flex: 1;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 12px;
    padding: 1.25rem;
    overflow: hidden;
}
.pn-resource-title {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--mg-text-muted);
    letter-spacing: 0.05em;
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}
.pn-resource-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}
@media (min-width: 640px) { .pn-resource-grid { grid-template-columns: repeat(4, 1fr); } }
@media (min-width: 1024px) { .pn-resource-grid { grid-template-columns: repeat(7, 1fr); } }

.pn-resource-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    padding: 0.5rem 0.25rem;
    background: rgba(0,0,0,0.3);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 4px;
    transition: border-color 0.2s;
}
.pn-resource-item:hover { border-color: var(--mg-text-muted); }
.pn-resource-item.pn-resource-stamina:hover { border-color: var(--mg-accent); }
.pn-resource-label { font-size: 0.6rem; color: var(--mg-text-muted); }
.pn-resource-value { font-size: 0.85rem; font-weight: 700; color: var(--mg-text-primary); }

.pn-stats-col { display: flex; flex-direction: row; gap: 0.5rem; }
@media (min-width: 768px) { .pn-stats-col { flex-direction: column; width: 180px; } }

.pn-stat-bar {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    background: rgba(0,0,0,0.3);
    border-left: 4px solid;
    border-radius: 0 4px 4px 0;
}
.pn-stat-building { border-left-color: var(--mg-accent); }
.pn-stat-complete { border-left-color: var(--mg-success, #10b981); }
.pn-stat-locked { border-left-color: #6b7280; }
.pn-stat-label { font-size: 0.75rem; color: var(--mg-text-muted); }
.pn-stat-value { font-size: 1.25rem; font-weight: 700; }
.pn-stat-building .pn-stat-value { color: var(--mg-accent); }
.pn-stat-complete .pn-stat-value { color: var(--mg-success, #10b981); }
.pn-stat-locked .pn-stat-value { color: #6b7280; }

/* ============================== */
/* 카드뷰 */
/* ============================== */
.pn-card-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 768px) { .pn-card-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .pn-card-grid { grid-template-columns: repeat(3, 1fr); } }

.pn-card {
    position: relative;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-top: 2px solid;
    border-radius: 2px;
    overflow: hidden;
    transition: border-color 0.2s, transform 0.15s;
    cursor: pointer;
    clip-path: polygon(0 0, 100% 0, 100% calc(100% - 14px), calc(100% - 14px) 100%, 0 100%);
}
.pn-card:hover { transform: translateY(-2px); border-color: var(--mg-text-muted); }
.pn-card.pn-card-locked { opacity: 0.5; cursor: default; }
.pn-card.pn-card-locked:hover { transform: none; border-color: var(--mg-bg-tertiary); }

.pn-card-stripes {
    position: absolute; inset: 0; pointer-events: none; z-index: 0;
    background-image: repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(245,159,10,0.04) 10px, rgba(245,159,10,0.04) 20px);
}

.pn-card-inner {
    position: relative; z-index: 1;
    padding: 1.25rem;
    display: flex; flex-direction: column; gap: 0.75rem;
    min-height: 200px;
}

.pn-card-header { display: flex; justify-content: space-between; align-items: flex-start; }
.pn-card-name-row { display: flex; align-items: center; gap: 0.5rem; }
.pn-card-icon { flex-shrink: 0; }
.pn-card-name { font-size: 1.15rem; font-weight: 700; color: var(--mg-text-primary); }
.pn-card-badge {
    flex-shrink: 0; font-size: 0.65rem; font-weight: 700;
    padding: 0.2rem 0.5rem; border: 1px solid; border-radius: 2px; white-space: nowrap;
}

.pn-card-desc {
    font-size: 0.8rem; color: var(--mg-text-muted); line-height: 1.5;
    display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden;
    min-height: 2.4em;
}

.pn-card-footer { margin-top: auto; padding-top: 0.75rem; border-top: 1px solid var(--mg-bg-tertiary); }

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

.pn-resource-tags { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.pn-resource-tag {
    display: inline-flex; align-items: center; gap: 0.25rem;
    font-size: 0.7rem; padding: 0.15rem 0.5rem;
    background: rgba(0,0,0,0.4); border: 1px solid var(--mg-bg-tertiary);
    color: var(--mg-text-secondary); border-radius: 2px;
}

/* 완공 */
.pn-complete-footer { display: flex; justify-content: space-between; align-items: center; }
.pn-complete-date { font-size: 0.8rem; color: var(--mg-text-secondary); }
.pn-link-btn { font-size: 0.85rem; font-weight: 700; color: var(--mg-success, #10b981); text-decoration: none; transition: color 0.2s; }
.pn-link-btn:hover { color: var(--mg-text-primary); }

/* 잠김 */
.pn-locked-footer { text-align: center; }

/* 빈 상태 */
.pn-empty {
    grid-column: 1 / -1; text-align: center; padding: 4rem 2rem;
    background: var(--mg-bg-secondary); border: 1px solid var(--mg-bg-tertiary); border-radius: 12px;
}

/* ============================== */
/* 거점뷰 (맵) */
/* ============================== */
.pn-map-container { position: relative; overflow: auto; max-height: 75vh; border-radius: 12px; border: 1px solid var(--mg-bg-tertiary); background: var(--mg-bg-secondary); }
.pn-map-image { display: block; width: 100%; min-width: 600px; user-select: none; }

.pn-marker {
    position: absolute; width: 44px; height: 44px; margin-left: -22px; margin-top: -44px;
    cursor: pointer; transition: transform 0.15s; z-index: 5; user-select: none;
    display: flex; align-items: center; justify-content: center;
}
.pn-marker:hover { transform: scale(1.2); z-index: 10; }
.pn-marker svg { width: 100%; height: 100%; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.4)); }
.pn-marker.is-locked { opacity: 0.35; cursor: default; }
.pn-marker.is-locked:hover { transform: none; }

/* ============================== */
/* 시설 상세 모달 */
/* ============================== */
.pn-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow-y: auto;
}
.pn-modal-panel {
    position: relative;
    width: 100%;
    max-width: 580px;
    max-height: 85vh;
    overflow-y: auto;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
}
.pn-modal-close {
    position: sticky;
    top: 0;
    float: right;
    z-index: 10;
    width: 32px;
    height: 32px;
    margin: 0.75rem 0.75rem 0 0;
    border-radius: 50%;
    background: var(--mg-bg-tertiary);
    color: var(--mg-text-secondary);
    border: none;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.pn-modal-close:hover { background: var(--mg-text-muted); color: #fff; }

.pn-modal-loading {
    text-align: center;
    padding: 3rem;
    color: var(--mg-text-muted);
}

/* 모달 - 헤더 */
.pn-m-header {
    position: relative;
    padding: 1.25rem 1.5rem 1rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
    overflow: hidden;
}
.pn-m-header-stripes {
    position: absolute; inset: 0; pointer-events: none;
    background-image: repeating-linear-gradient(-45deg, transparent, transparent 10px, rgba(245,159,10,0.04) 10px, rgba(245,159,10,0.04) 20px);
}
.pn-m-header-inner { position: relative; z-index: 1; }
.pn-m-icon {
    width: 48px; height: 48px; flex-shrink: 0;
    border-radius: 8px; background: var(--mg-bg-tertiary);
    display: flex; align-items: center; justify-content: center;
    color: var(--mg-text-primary);
}
.pn-m-name { font-size: 1.15rem; font-weight: 700; color: var(--mg-text-primary); }
.pn-m-desc { font-size: 0.8rem; color: var(--mg-text-muted); line-height: 1.5; margin-top: 0.5rem; }

/* 모달 - 진행률 */
.pn-m-progress {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
    background: rgba(0,0,0,0.15);
}

/* 모달 - 투입 섹션 */
.pn-m-contribute { padding: 1rem 1.5rem; border-bottom: 1px solid var(--mg-bg-tertiary); }
.pn-m-section-label {
    font-size: 0.7rem; font-weight: 600; color: var(--mg-text-muted);
    letter-spacing: 0.05em; margin-bottom: 0.75rem;
}
.pn-m-row {
    display: flex; flex-direction: column; gap: 0.5rem;
    padding: 0.75rem; margin-bottom: 0.5rem;
    background: var(--mg-bg-primary); border-radius: 8px;
}
.pn-m-row:last-child { margin-bottom: 0; }
@media (min-width: 480px) {
    .pn-m-row { flex-direction: row; align-items: flex-end; }
}
.pn-m-row-info { flex: 1; min-width: 0; }
.pn-m-row-label {
    display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.3rem;
    font-size: 0.85rem; font-weight: 500; color: var(--mg-text-primary);
}
.pn-m-row-label .pn-m-own {
    margin-left: auto; font-size: 0.75rem; font-weight: 700;
}
.pn-m-row-sub {
    display: flex; justify-content: space-between;
    font-size: 0.7rem; color: var(--mg-text-muted); margin-bottom: 0.3rem;
}
.pn-m-row-action {
    display: flex; gap: 0.3rem; flex-shrink: 0;
}
.pn-m-input {
    width: 56px; padding: 0.35rem 0.4rem; text-align: center;
    font-size: 0.8rem; font-family: 'Consolas', monospace;
    background: var(--mg-bg-secondary); border: 1px solid var(--mg-bg-tertiary);
    color: var(--mg-text-primary); border-radius: 4px; outline: none;
}
.pn-m-input:focus { border-color: var(--mg-accent); }
.pn-m-input:disabled { opacity: 0.35; }
.pn-m-btn {
    padding: 0.35rem 0.7rem; font-size: 0.75rem; font-weight: 600;
    border: none; border-radius: 4px; cursor: pointer;
    transition: opacity 0.2s; white-space: nowrap;
}
.pn-m-btn:disabled { opacity: 0.3; cursor: default; }
.pn-m-btn-amber { background: var(--mg-accent); color: var(--mg-bg-primary); }
.pn-m-btn-amber:hover:not(:disabled) { opacity: 0.85; }
.pn-m-btn-green { background: var(--mg-success, #10b981); color: #fff; }
.pn-m-btn-green:hover:not(:disabled) { opacity: 0.85; }

/* 모달 - 완공 정보 */
.pn-m-complete {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
    display: flex; justify-content: space-between; align-items: center;
}
.pn-m-complete-date { font-size: 0.85rem; color: var(--mg-text-secondary); }
.pn-m-complete-link {
    font-size: 0.85rem; font-weight: 700; color: var(--mg-success, #10b981);
    text-decoration: none; transition: color 0.2s;
}
.pn-m-complete-link:hover { color: var(--mg-text-primary); }

/* 모달 - 랭킹 */
.pn-m-ranking { padding: 1rem 1.5rem; }
.pn-m-rank-tabs {
    display: flex; gap: 0.3rem; margin-bottom: 0.75rem;
    overflow-x: auto; padding-bottom: 0.2rem;
}
.pn-m-rank-tab {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.3rem 0.6rem; font-size: 0.75rem; font-weight: 500;
    border-radius: 999px; border: none; cursor: pointer; white-space: nowrap;
    background: var(--mg-bg-tertiary); color: var(--mg-text-secondary);
    transition: all 0.2s;
}
.pn-m-rank-tab:hover { color: var(--mg-text-primary); }
.pn-m-rank-tab.active { background: var(--mg-accent); color: #fff; }

.pn-m-rank-list { display: flex; flex-direction: column; gap: 0.3rem; }
.pn-m-rank-item {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.5rem 0.6rem; border-radius: 6px;
    border: 1px solid var(--mg-bg-tertiary); background: var(--mg-bg-primary);
}
.pn-m-rank-num {
    width: 24px; height: 24px; display: flex; align-items: center; justify-content: center;
    border-radius: 50%; background: var(--mg-bg-tertiary);
    font-size: 0.7rem; font-weight: 700; color: var(--mg-text-secondary); flex-shrink: 0;
}
.pn-m-rank-name { flex: 1; font-weight: 500; color: var(--mg-text-primary); font-size: 0.85rem; }
.pn-m-rank-amount { font-weight: 700; color: var(--mg-accent); font-size: 0.85rem; font-family: 'Consolas', monospace; }
.pn-m-rank-gold { border-color: rgba(234,179,8,0.3); background: rgba(234,179,8,0.06); }
.pn-m-rank-gold .pn-m-rank-num { background: rgba(234,179,8,0.2); color: #eab308; }
.pn-m-rank-silver { border-color: rgba(156,163,175,0.3); background: rgba(156,163,175,0.06); }
.pn-m-rank-silver .pn-m-rank-num { background: rgba(156,163,175,0.2); color: #9ca3af; }
.pn-m-rank-bronze { border-color: rgba(249,115,22,0.3); background: rgba(249,115,22,0.06); }
.pn-m-rank-bronze .pn-m-rank-num { background: rgba(249,115,22,0.2); color: #f97316; }
.pn-m-rank-empty { text-align: center; padding: 1.5rem; color: var(--mg-text-muted); font-size: 0.85rem; }
</style>

<script>
(function() {
    var facilitiesData = <?php echo json_encode($facilities); ?>;
    var myStamina = <?php echo json_encode($my_stamina); ?>;
    var myMaterials = <?php echo json_encode($my_materials); ?>;
    var PIONEER_URL = '<?php echo G5_BBS_URL; ?>/pioneer.php';
    var CONTRIBUTE_URL = '<?php echo G5_BBS_URL; ?>/pioneer_contribute.php';
    var autoOpenFcId = <?php echo $auto_open_fc_id; ?>;

    function esc(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
    function fmt(n) { return Number(n).toLocaleString(); }

    // === 모달 열기/닫기 ===
    window.openFacilityModal = function(fcId) {
        var fc = facilitiesData.find(function(f){ return f.fc_id == fcId; });
        if (!fc) return;

        var content = document.getElementById('pn-modal-content');
        content.innerHTML = buildModalHTML(fc);
        document.getElementById('pn-modal-overlay').style.display = 'flex';
        document.body.style.overflow = 'hidden';

        // 랭킹 AJAX 로드
        fetchRankings(fcId, fc);
    };

    window.closeFacilityModal = function() {
        document.getElementById('pn-modal-overlay').style.display = 'none';
        document.body.style.overflow = '';
    };

    // ESC 키로 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('pn-modal-overlay').style.display === 'flex') {
            closeFacilityModal();
        }
    });

    // === 모달 HTML 빌드 ===
    function buildModalHTML(fc) {
        var isBuilding = fc.fc_status === 'building';
        var isComplete = fc.fc_status === 'complete';
        var statusColor = isComplete ? 'var(--mg-success, #10b981)' : (isBuilding ? 'var(--mg-accent)' : '#6b7280');
        var statusLabel = isComplete ? '완공' : (isBuilding ? '건설 중' : '대기');

        var html = '';

        // 헤더
        html += '<div class="pn-m-header" style="border-top:2px solid ' + statusColor + ';">';
        if (isBuilding) html += '<div class="pn-m-header-stripes"></div>';
        html += '<div class="pn-m-header-inner">';
        html += '<div style="display:flex;align-items:flex-start;gap:0.75rem;">';
        html += '<div class="pn-m-icon">' + (fc.fc_icon_html || '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>') + '</div>';
        html += '<div style="flex:1;min-width:0;">';
        html += '<div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">';
        html += '<span class="pn-m-name">' + esc(fc.fc_name) + '</span>';
        html += '<span class="pn-card-badge" style="color:' + statusColor + ';border-color:' + statusColor + '30;background:' + statusColor + '15;">' + statusLabel + '</span>';
        html += '</div>';
        if (fc.fc_desc) html += '<div class="pn-m-desc">' + esc(fc.fc_desc) + '</div>';
        html += '</div></div></div></div>';

        // 진행률 (건설 중)
        if (isBuilding && fc.progress) {
            html += '<div class="pn-m-progress">';
            html += '<div class="pn-progress-header"><span style="color:var(--mg-text-muted);font-size:0.75rem;">총 진행률</span>';
            html += '<span class="pn-mono" style="font-size:1rem;font-weight:700;color:var(--mg-accent);">' + Math.round(fc.progress.total * 10) / 10 + '%</span></div>';
            html += '<div class="pn-progress-bar" style="height:8px;margin-bottom:0;">';
            html += '<div class="pn-progress-fill ' + (fc.progress.total < 100 ? 'pn-progress-animated' : '') + '" style="width:' + fc.progress.total + '%;background:var(--mg-accent);"></div>';
            html += '</div></div>';
        }

        // 투입 섹션 (건설 중)
        if (isBuilding) {
            html += '<div class="pn-m-contribute">';
            html += '<div class="pn-m-section-label">자원 투입</div>';

            // 스테미나
            var stProg = fc.progress ? fc.progress.stamina : 0;
            var stRemain = fc.fc_stamina_cost - fc.fc_stamina_current;
            var stMax = Math.min(myStamina.current, stRemain);
            var stDisabled = myStamina.current < 1 || stRemain < 1;

            html += '<div class="pn-m-row">';
            html += '<div class="pn-m-row-info">';
            html += '<div class="pn-m-row-label"><span style="color:var(--mg-accent);">⚡</span> 스테미나 <span class="pn-m-own" style="color:var(--mg-accent);">보유 ' + myStamina.current + '</span></div>';
            html += '<div class="pn-m-row-sub"><span>필요: ' + fmt(fc.fc_stamina_cost) + '</span><span>현재: ' + fmt(fc.fc_stamina_current) + ' (' + Math.round(stProg * 10) / 10 + '%)</span></div>';
            html += '<div class="pn-progress-bar" style="height:4px;margin:0;"><div class="pn-progress-fill" style="width:' + stProg + '%;background:var(--mg-accent);"></div></div>';
            html += '</div>';
            html += '<div class="pn-m-row-action">';
            html += '<input type="number" id="pnm-stamina" min="1" max="' + stMax + '" value="1" class="pn-m-input" ' + (stDisabled ? 'disabled' : '') + '>';
            html += '<button type="button" class="pn-m-btn pn-m-btn-amber" onclick="pnContribute(' + fc.fc_id + ',\'stamina\',0)" ' + (stDisabled ? 'disabled' : '') + '>투입</button>';
            html += '</div></div>';

            // 재료
            (fc.materials || []).forEach(function(mat) {
                var myCount = 0;
                myMaterials.forEach(function(mm) { if (mm.mt_id == mat.mt_id) myCount = mm.um_count; });
                var matProg = mat.fmc_required > 0 ? (mat.fmc_current / mat.fmc_required * 100) : 100;
                var matRemain = mat.fmc_required - mat.fmc_current;
                var matMax = Math.min(myCount, matRemain);
                var matDisabled = myCount < 1 || matRemain < 1;

                html += '<div class="pn-m-row">';
                html += '<div class="pn-m-row-info">';
                html += '<div class="pn-m-row-label">' + (mat.mt_icon_html || '') + ' ' + esc(mat.mt_name) + ' <span class="pn-m-own" style="color:var(--mg-text-secondary);">보유 ' + fmt(myCount) + '</span></div>';
                html += '<div class="pn-m-row-sub"><span>필요: ' + fmt(mat.fmc_required) + '</span><span>현재: ' + fmt(mat.fmc_current) + ' (' + Math.round(matProg * 10) / 10 + '%)</span></div>';
                html += '<div class="pn-progress-bar" style="height:4px;margin:0;"><div class="pn-progress-fill" style="width:' + matProg + '%;background:var(--mg-success, #10b981);"></div></div>';
                html += '</div>';
                html += '<div class="pn-m-row-action">';
                html += '<input type="number" id="pnm-mat-' + mat.mt_id + '" min="1" max="' + matMax + '" value="1" class="pn-m-input" ' + (matDisabled ? 'disabled' : '') + '>';
                html += '<button type="button" class="pn-m-btn pn-m-btn-green" onclick="pnContribute(' + fc.fc_id + ',\'material\',' + mat.mt_id + ')" ' + (matDisabled ? 'disabled' : '') + '>투입</button>';
                html += '</div></div>';
            });

            html += '</div>';
        }

        // 완공 정보
        if (isComplete) {
            html += '<div class="pn-m-complete">';
            html += '<span class="pn-m-complete-date">' + (fc.fc_complete_date ? fc.fc_complete_date.substring(0, 10) + ' 완공' : '완공') + '</span>';
            if (fc.fc_unlock_type === 'board' && fc.fc_unlock_target) {
                html += '<a href="' + PIONEER_URL.replace('/pioneer.php', '/board.php?bo_table=' + fc.fc_unlock_target) + '" class="pn-m-complete-link">이동 &rarr;</a>';
            }
            html += '</div>';
        }

        // 랭킹 (로딩 상태)
        html += '<div class="pn-m-ranking">';
        html += '<div class="pn-m-section-label">' + (isComplete ? '명예의 전당' : '기여 랭킹') + '</div>';
        html += '<div id="pnm-ranking-content"><div class="pn-m-rank-empty">불러오는 중...</div></div>';
        html += '</div>';

        return html;
    }

    // === 랭킹 AJAX ===
    function fetchRankings(fcId, fc) {
        fetch(PIONEER_URL + '?view=facility_data&fc_id=' + fcId)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                document.getElementById('pnm-ranking-content').innerHTML = '<div class="pn-m-rank-empty">데이터를 불러올 수 없습니다.</div>';
                return;
            }
            renderRankings(data, fc);
        })
        .catch(function() {
            document.getElementById('pnm-ranking-content').innerHTML = '<div class="pn-m-rank-empty">네트워크 오류</div>';
        });
    }

    function renderRankings(data, fc) {
        var container = document.getElementById('pnm-ranking-content');
        if (!container) return;

        var html = '';

        // 탭
        html += '<div class="pn-m-rank-tabs">';
        html += '<button type="button" class="pn-m-rank-tab active" onclick="pnSwitchRankTab(this,\'stamina\')">⚡ 스테미나</button>';
        (fc.materials || []).forEach(function(mat) {
            html += '<button type="button" class="pn-m-rank-tab" onclick="pnSwitchRankTab(this,\'' + esc(mat.mt_code) + '\')">' + (mat.mt_icon_html || '') + ' ' + esc(mat.mt_name) + '</button>';
        });
        html += '</div>';

        // 스테미나 랭킹
        html += '<div class="pn-m-rank-panel" data-rank-cat="stamina">' + buildRankList(data.stamina_ranking) + '</div>';

        // 재료별 랭킹
        (fc.materials || []).forEach(function(mat) {
            var rankings = (data.material_rankings && data.material_rankings[mat.mt_code]) || [];
            html += '<div class="pn-m-rank-panel" data-rank-cat="' + esc(mat.mt_code) + '" style="display:none;">' + buildRankList(rankings) + '</div>';
        });

        container.innerHTML = html;
    }

    function buildRankList(rankings) {
        if (!rankings || rankings.length === 0) {
            return '<div class="pn-m-rank-empty">아직 기여 기록이 없습니다.</div>';
        }
        var html = '<div class="pn-m-rank-list">';
        rankings.forEach(function(r) {
            var medal = '';
            if (r.rank == 1) medal = ' pn-m-rank-gold';
            else if (r.rank == 2) medal = ' pn-m-rank-silver';
            else if (r.rank == 3) medal = ' pn-m-rank-bronze';

            html += '<div class="pn-m-rank-item' + medal + '">';
            html += '<span class="pn-m-rank-num">' + r.rank + '</span>';
            html += '<span class="pn-m-rank-name">' + esc(r.mb_nick || r.mb_name || r.mb_id) + '</span>';
            html += '<span class="pn-m-rank-amount">' + fmt(r.fh_amount) + '</span>';
            html += '</div>';
        });
        html += '</div>';
        return html;
    }

    // 랭킹 탭 전환
    window.pnSwitchRankTab = function(btn, cat) {
        btn.parentElement.querySelectorAll('.pn-m-rank-tab').forEach(function(t) { t.classList.remove('active'); });
        btn.classList.add('active');
        btn.closest('.pn-m-ranking').querySelectorAll('.pn-m-rank-panel').forEach(function(p) {
            p.style.display = p.dataset.rankCat === cat ? '' : 'none';
        });
    };

    // === 자원 투입 ===
    window.pnContribute = function(fcId, type, mtId) {
        var inputId = type === 'stamina' ? 'pnm-stamina' : 'pnm-mat-' + mtId;
        var input = document.getElementById(inputId);
        var amount = parseInt(input ? input.value : 0) || 0;
        if (amount < 1) { alert('투입량을 입력해주세요.'); return; }

        var formData = new FormData();
        formData.append('fc_id', fcId);
        formData.append('type', type);
        formData.append('amount', amount);
        if (type === 'material') formData.append('mt_id', mtId);

        fetch(CONTRIBUTE_URL, { method: 'POST', body: formData })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (typeof showToast === 'function') showToast(data.message, data.success ? 'success' : 'error');
            else alert(data.message);
            if (data.success) location.reload();
        })
        .catch(function() { alert('오류가 발생했습니다.'); });
    };

    // === 거점뷰 마커 ===
    <?php if ($pioneer_view_mode === 'base') { ?>
    (function() {
        var markersEl = document.getElementById('pn-map-markers');

        function getMarkerSVG(color, inner) {
            return '<svg viewBox="0 0 24 36" width="27" height="40"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z" fill="'+color+'"/><circle cx="12" cy="12" r="5" fill="'+inner+'"/></svg>';
        }

        facilitiesData.forEach(function(fc) {
            if (fc.fc_map_x == null || fc.fc_map_y == null) return;
            var locked = fc.fc_status === 'locked';
            var color = locked ? '#6b7280' : (fc.fc_status === 'complete' ? '#10b981' : '#f59f0a');
            var inner = locked ? '#4b5563' : '#1e1f22';

            var marker = document.createElement('div');
            marker.className = 'pn-marker' + (locked ? ' is-locked' : '');
            marker.style.left = fc.fc_map_x + '%';
            marker.style.top = fc.fc_map_y + '%';
            marker.title = fc.fc_name;
            marker.innerHTML = getMarkerSVG(color, inner);

            if (!locked) {
                marker.onclick = function(e) { e.stopPropagation(); openFacilityModal(fc.fc_id); };
            }
            markersEl.appendChild(marker);
        });
    })();
    <?php } ?>

    // === 자동 오픈 ===
    if (autoOpenFcId > 0) {
        openFacilityModal(autoOpenFcId);
    }
})();
</script>
