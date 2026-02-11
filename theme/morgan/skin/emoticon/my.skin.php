<?php
if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-mg-text-primary">내 이모티콘</h2>
        <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=emoticon" class="btn btn-secondary text-sm">이모티콘 상점</a>
    </div>

    <!-- 보유 이모티콘 -->
    <div class="card mb-6">
        <div class="card-header">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            보유 이모티콘 셋 <span class="text-mg-accent ml-1"><?php echo count($my_sets); ?></span>
        </div>

        <?php if (!empty($my_sets)) { ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4">
            <?php foreach ($my_sets as $set) { ?>
            <div class="flex items-center gap-3 p-3 bg-mg-bg-primary rounded-lg">
                <?php if ($set['es_preview']) { ?>
                <img src="<?php echo htmlspecialchars($set['es_preview']); ?>" alt="" class="w-12 h-12 object-contain flex-shrink-0">
                <?php } else { ?>
                <div class="w-12 h-12 bg-mg-bg-tertiary rounded flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                <?php } ?>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($set['es_name']); ?></p>
                    <p class="text-xs text-mg-text-muted"><?php echo (int)$set['em_count']; ?>개</p>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="p-8 text-center text-mg-text-muted">
            <p class="mb-2">보유한 이모티콘 셋이 없습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=emoticon" class="text-mg-accent hover:underline text-sm">이모티콘 상점에서 구매하기</a>
        </div>
        <?php } ?>
    </div>

    <!-- 크리에이터 섹션 -->
    <?php if ($creator_enabled) { ?>
    <div class="card">
        <div class="card-header">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            내가 만든 이모티콘
        </div>

        <div class="p-4">
            <!-- 등록권 상태 -->
            <div class="flex items-center justify-between mb-4 p-3 bg-mg-bg-primary rounded-lg">
                <div>
                    <span class="text-sm text-mg-text-secondary">이모티콘 등록권</span>
                    <span class="ml-2 font-bold text-mg-accent"><?php echo $reg_check['count']; ?>개</span>
                </div>
                <?php if ($reg_check['can']) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/emoticon_create.php" class="btn btn-primary text-sm">새 이모티콘 만들기</a>
                <?php } else { ?>
                <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="btn btn-secondary text-sm">등록권 구매하기</a>
                <?php } ?>
            </div>

            <!-- 제작 목록 -->
            <?php if (!empty($creator_sets)) { ?>
            <?php
            $status_labels = array(
                'draft' => array('text' => '작성중', 'class' => 'bg-mg-bg-tertiary text-mg-text-muted'),
                'pending' => array('text' => '심사중', 'class' => 'bg-mg-warning/20 text-mg-warning'),
                'approved' => array('text' => '승인', 'class' => 'bg-mg-success/20 text-mg-success'),
                'rejected' => array('text' => '반려', 'class' => 'bg-mg-error/20 text-mg-error'),
            );
            ?>
            <div class="space-y-2">
                <?php foreach ($creator_sets as $cset) {
                    $st = $status_labels[$cset['es_status']] ?? array('text' => $cset['es_status'], 'class' => '');
                ?>
                <div class="flex items-center justify-between p-3 bg-mg-bg-primary rounded-lg">
                    <div class="flex items-center gap-3 min-w-0">
                        <?php if ($cset['es_preview']) { ?>
                        <img src="<?php echo htmlspecialchars($cset['es_preview']); ?>" alt="" class="w-10 h-10 object-contain flex-shrink-0">
                        <?php } else { ?>
                        <div class="w-10 h-10 bg-mg-bg-tertiary rounded flex items-center justify-center text-mg-text-muted flex-shrink-0 text-sm">?</div>
                        <?php } ?>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($cset['es_name']); ?></p>
                            <p class="text-xs text-mg-text-muted">
                                <?php echo (int)$cset['em_count']; ?>개 |
                                <?php echo number_format((int)$cset['es_price']); ?>P |
                                판매 <?php echo (int)$cset['es_sales_count']; ?>개
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo $st['class']; ?>"><?php echo $st['text']; ?></span>
                        <?php if (in_array($cset['es_status'], array('draft', 'rejected'))) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/emoticon_create.php?es_id=<?php echo $cset['es_id']; ?>" class="text-xs text-mg-accent hover:underline">수정</a>
                        <?php } ?>
                    </div>
                </div>
                <?php if ($cset['es_status'] === 'rejected' && $cset['es_reject_reason']) { ?>
                <div class="ml-13 px-3 py-2 bg-mg-error/10 rounded text-xs text-mg-error">
                    반려 사유: <?php echo htmlspecialchars($cset['es_reject_reason']); ?>
                </div>
                <?php } ?>
                <?php } ?>
            </div>
            <?php } else { ?>
            <p class="text-center text-mg-text-muted text-sm py-4">아직 만든 이모티콘 셋이 없습니다.</p>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
