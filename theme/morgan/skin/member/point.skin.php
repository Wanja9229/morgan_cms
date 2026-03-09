<?php
/**
 * Morgan Edition - Point Skin
 *
 * 포인트 내역
 */

if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <!-- 포인트 요약 -->
    <div class="card mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-mg-text-muted">보유 포인트</p>
                <p class="text-3xl font-bold text-mg-accent"><?php echo number_format($member['mb_point']); ?><span class="text-lg">P</span></p>
            </div>
            <div class="w-16 h-16 rounded-full bg-mg-accent/20 flex items-center justify-center">
                <i data-lucide="circle-dollar-sign" class="w-8 h-8 text-mg-accent"></i>
            </div>
        </div>
    </div>

    <!-- 포인트 내역 -->
    <div class="card">
        <h2 class="card-header mb-4">
            <i data-lucide="clipboard-list" class="w-5 h-5 text-mg-accent"></i>
            포인트 내역
        </h2>

        <?php if (empty($list)) { ?>
        <p class="text-mg-text-muted text-center py-8">포인트 내역이 없습니다.</p>
        <?php } else { ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-mg-bg-tertiary">
                        <th class="text-left py-3 px-2 text-mg-text-muted font-medium">날짜</th>
                        <th class="text-left py-3 px-2 text-mg-text-muted font-medium">내용</th>
                        <th class="text-right py-3 px-2 text-mg-text-muted font-medium">포인트</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $row) { ?>
                    <tr class="border-b border-mg-bg-tertiary/50 hover:bg-mg-bg-tertiary/30">
                        <td class="py-3 px-2 text-mg-text-secondary whitespace-nowrap">
                            <?php echo substr($row['po_datetime'], 0, 10); ?>
                        </td>
                        <td class="py-3 px-2 text-mg-text-primary">
                            <?php echo $row['po_content']; ?>
                        </td>
                        <td class="py-3 px-2 text-right whitespace-nowrap <?php echo $row['po_point'] >= 0 ? 'text-mg-success' : 'text-mg-error'; ?>">
                            <?php echo ($row['po_point'] >= 0 ? '+' : '') . number_format($row['po_point']); ?>P
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- 페이지네이션 -->
        <?php if ($total_page > 1) { ?>
        <div class="flex justify-center mt-6">
            <?php echo $paging; ?>
        </div>
        <?php } ?>
        <?php } ?>
    </div>
</div>
