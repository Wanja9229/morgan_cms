<?php
/**
 * Morgan Edition - 개척 현황 목록 스킨
 */

if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <!-- 탭 네비게이션 -->
    <div class="flex gap-2 mb-6 border-b border-mg-bg-tertiary pb-3">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="px-4 py-2 text-sm font-medium text-mg-accent bg-mg-accent/10 rounded-lg">시설 건설</a>
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php?view=expedition" class="px-4 py-2 text-sm font-medium text-mg-text-secondary hover:text-mg-text-primary rounded-lg transition-colors">탐색 파견</a>
    </div>

    <!-- 헤더 -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-mg-text-primary mb-2">개척 현황</h1>
        <p class="text-mg-text-secondary">커뮤니티와 함께 시설을 건설하고 새로운 기능을 해금하세요!</p>
    </div>

    <!-- 내 자원 현황 -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">내 자원</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            <!-- 노동력 -->
            <div class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg">
                <span class="text-mg-accent"><?php echo mg_icon('bolt', 'w-6 h-6'); ?></span>
                <div>
                    <div class="text-xs text-mg-text-muted">노동력</div>
                    <div class="font-bold text-mg-accent"><?php echo $my_stamina['current']; ?> / <?php echo $my_stamina['max']; ?></div>
                </div>
            </div>
            <!-- 재료 -->
            <?php foreach ($my_materials as $mat) { ?>
            <div class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg">
                <span class="text-mg-text-primary"><?php echo mg_icon($mat['mt_icon'], 'w-6 h-6'); ?></span>
                <div>
                    <div class="text-xs text-mg-text-muted"><?php echo htmlspecialchars($mat['mt_name']); ?></div>
                    <div class="font-bold text-mg-text-primary"><?php echo number_format($mat['um_count']); ?></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 통계 -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="card text-center">
            <div class="text-3xl font-bold text-mg-accent"><?php echo $building_count; ?></div>
            <div class="text-sm text-mg-text-muted">건설 중</div>
        </div>
        <div class="card text-center">
            <div class="text-3xl font-bold text-mg-success"><?php echo $complete_count; ?></div>
            <div class="text-sm text-mg-text-muted">완공</div>
        </div>
        <div class="card text-center">
            <div class="text-3xl font-bold text-mg-text-secondary"><?php echo count($facilities) - $complete_count - $building_count; ?></div>
            <div class="text-sm text-mg-text-muted">잠김</div>
        </div>
    </div>

    <!-- 시설 목록 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php foreach ($facilities as $facility) {
            $status_class = '';
            $status_label = '';
            $status_bg = '';

            switch ($facility['fc_status']) {
                case 'complete':
                    $status_class = 'border-mg-success';
                    $status_label = '완공';
                    $status_bg = 'bg-mg-success/10';
                    break;
                case 'building':
                    $status_class = 'border-mg-accent';
                    $status_label = '건설 중';
                    $status_bg = 'bg-mg-accent/10';
                    break;
                default:
                    $status_class = 'border-mg-bg-tertiary opacity-60';
                    $status_label = '잠김';
                    $status_bg = 'bg-mg-bg-tertiary';
            }

            $is_clickable = $facility['fc_status'] !== 'locked';
        ?>
        <div class="card border-2 <?php echo $status_class; ?> <?php echo $is_clickable ? 'cursor-pointer hover:shadow-lg transition-shadow' : ''; ?>"
             <?php if ($is_clickable) { ?>onclick="location.href='<?php echo G5_BBS_URL; ?>/pioneer.php?view=detail&fc_id=<?php echo $facility['fc_id']; ?>'"<?php } ?>>
            <!-- 상태 배지 -->
            <div class="flex justify-between items-start mb-3">
                <span class="text-mg-text-primary"><?php echo mg_icon($facility['fc_icon'] ?: 'building-office', 'w-8 h-8'); ?></span>
                <span class="px-2 py-1 text-xs font-medium rounded <?php echo $status_bg; ?> <?php echo $facility['fc_status'] === 'complete' ? 'text-mg-success' : ($facility['fc_status'] === 'building' ? 'text-mg-accent' : 'text-mg-text-muted'); ?>">
                    <?php echo $status_label; ?>
                </span>
            </div>

            <!-- 시설 정보 -->
            <h3 class="text-lg font-bold text-mg-text-primary mb-1"><?php echo htmlspecialchars($facility['fc_name']); ?></h3>
            <p class="text-sm text-mg-text-secondary mb-3 line-clamp-2"><?php echo htmlspecialchars($facility['fc_desc']); ?></p>

            <?php if ($facility['fc_status'] === 'building') { ?>
            <!-- 진행률 바 -->
            <div class="mb-3">
                <div class="flex justify-between text-xs text-mg-text-muted mb-1">
                    <span>총 진행률</span>
                    <span><?php echo round($facility['progress']['total'], 1); ?>%</span>
                </div>
                <div class="h-2 bg-mg-bg-tertiary rounded-full overflow-hidden">
                    <div class="h-full bg-mg-accent transition-all" style="width: <?php echo $facility['progress']['total']; ?>%"></div>
                </div>
            </div>

            <!-- 필요 자원 미리보기 -->
            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-1 bg-mg-bg-primary rounded inline-flex items-center gap-1">
                    <?php echo mg_icon('bolt', 'w-3 h-3'); ?> <?php echo number_format($facility['fc_stamina_current']); ?>/<?php echo number_format($facility['fc_stamina_cost']); ?>
                </span>
                <?php foreach (array_slice($facility['materials'], 0, 2) as $mat) { ?>
                <span class="px-2 py-1 bg-mg-bg-primary rounded inline-flex items-center gap-1">
                    <?php echo mg_icon($mat['mt_icon'], 'w-3 h-3'); ?> <?php echo number_format($mat['fmc_current']); ?>/<?php echo number_format($mat['fmc_required']); ?>
                </span>
                <?php } ?>
                <?php if (count($facility['materials']) > 2) { ?>
                <span class="px-2 py-1 bg-mg-bg-primary rounded text-mg-text-muted">+<?php echo count($facility['materials']) - 2; ?></span>
                <?php } ?>
            </div>
            <?php } elseif ($facility['fc_status'] === 'complete' && $facility['fc_complete_date']) { ?>
            <div class="text-xs text-mg-text-muted">
                <?php echo date('Y-m-d', strtotime($facility['fc_complete_date'])); ?> 완공
            </div>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (empty($facilities)) { ?>
        <div class="col-span-full card text-center py-12">
            <div class="mb-4"><svg class="w-12 h-12 mx-auto" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
            <p class="text-mg-text-muted">등록된 시설이 없습니다.</p>
        </div>
        <?php } ?>
    </div>
</div>
