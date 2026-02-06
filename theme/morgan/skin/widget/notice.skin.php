<?php
/**
 * Morgan Edition - Notice Widget Skin
 *
 * 사용 가능한 변수:
 * $config - 위젯 설정
 * $title - 제목
 * $notices - 공지 배열
 * $bo_table - 게시판 테이블명
 * $show_date - 날짜 표시 여부
 * $show_icon - 아이콘 표시 여부
 */

if (!defined('_GNUBOARD_')) exit;
?>
<div class="card mg-widget mg-widget-notice h-full flex flex-col">
    <h2 class="card-header">
        <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
        </svg>
        <?php echo htmlspecialchars($title); ?>
    </h2>
    <ul class="space-y-2 text-sm flex-1 overflow-auto">
        <?php if (empty($notices)): ?>
        <li class="text-mg-text-muted">공지사항이 없습니다.</li>
        <?php else: ?>
        <?php foreach ($notices as $notice): ?>
        <li class="flex items-center justify-between gap-2 py-1 border-b border-mg-bg-tertiary last:border-0">
            <a href="<?php echo $notice['href']; ?>" class="flex items-center gap-2 text-mg-text hover:text-mg-accent truncate flex-1" title="<?php echo htmlspecialchars($notice['wr_subject']); ?>">
                <?php if ($show_icon && !empty($notice['is_notice'])): ?>
                <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-mg-accent text-white text-xs font-bold flex-shrink-0">N</span>
                <?php endif; ?>
                <span class="truncate"><?php echo htmlspecialchars($notice['wr_subject']); ?></span>
            </a>
            <?php if ($show_date): ?>
            <span class="text-mg-text-muted text-xs whitespace-nowrap"><?php echo date('m.d', strtotime($notice['wr_datetime'])); ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <?php endif; ?>
    </ul>
    <?php if ($bo_table): ?>
    <div class="mt-3 pt-3 border-t border-mg-bg-tertiary">
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $bo_table; ?>" class="text-xs text-mg-accent hover:underline">더보기 &rarr;</a>
    </div>
    <?php endif; ?>
</div>
