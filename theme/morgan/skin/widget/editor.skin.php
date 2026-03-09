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
        <i data-lucide="pencil" class="w-5 h-5 text-mg-accent"></i>
        <?php echo htmlspecialchars($title); ?>
    </h2>
    <?php endif; ?>
    <div class="widget-content">
        <?php echo $content; ?>
    </div>
</div>
