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
        <i data-lucide="file-text" class="w-5 h-5 text-mg-accent"></i>
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
