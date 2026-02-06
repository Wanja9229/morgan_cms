<?php
/**
 * Morgan Edition - Editor Widget Skin
 *
 * 사용 가능한 변수:
 * $config - 위젯 설정
 * $title - 제목
 * $content - HTML 콘텐츠
 * $show_title - 제목 표시 여부
 */

if (!defined('_GNUBOARD_')) exit;
?>
<div class="card mg-widget mg-widget-editor">
    <?php if ($show_title && $title): ?>
    <h2 class="card-header">
        <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
        <?php echo htmlspecialchars($title); ?>
    </h2>
    <?php endif; ?>
    <div class="widget-content">
        <?php echo $content; ?>
    </div>
</div>
