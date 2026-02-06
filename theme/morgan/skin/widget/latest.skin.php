<?php
/**
 * Morgan Edition - Latest Posts Widget Skin
 *
 * 사용 가능한 변수:
 * $config - 위젯 설정
 * $title - 제목
 * $posts - 게시글 배열
 * $bo_table - 게시판 테이블명
 * $show_date - 날짜 표시 여부
 * $show_writer - 작성자 표시 여부
 */

if (!defined('_GNUBOARD_')) exit;
?>
<div class="card mg-widget mg-widget-latest h-full flex flex-col">
    <h2 class="card-header">
        <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <?php echo htmlspecialchars($title); ?>
    </h2>
    <ul class="space-y-2 text-sm flex-1 overflow-auto">
        <?php if (empty($posts)): ?>
        <li class="text-mg-text-muted">게시글이 없습니다.</li>
        <?php else: ?>
        <?php foreach ($posts as $post): ?>
        <li class="flex items-center justify-between gap-2 py-1 border-b border-mg-bg-tertiary last:border-0">
            <a href="<?php echo $post['href']; ?>" class="text-mg-text hover:text-mg-accent truncate flex-1" title="<?php echo htmlspecialchars($post['wr_subject']); ?>">
                <?php echo htmlspecialchars($post['wr_subject']); ?>
            </a>
            <span class="flex items-center gap-2 text-mg-text-muted text-xs whitespace-nowrap">
                <?php if ($show_writer): ?>
                <span><?php echo htmlspecialchars($post['wr_name']); ?></span>
                <?php endif; ?>
                <?php if ($show_date): ?>
                <span><?php echo date('m.d', strtotime($post['wr_datetime'])); ?></span>
                <?php endif; ?>
            </span>
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
