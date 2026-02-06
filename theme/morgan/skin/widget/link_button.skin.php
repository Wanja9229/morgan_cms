<?php
/**
 * Morgan Edition - Link/Button Widget Skin
 */

if (!defined('_GNUBOARD_')) exit;

$align_styles = array(
    'left' => 'text-left',
    'center' => 'text-center',
    'right' => 'text-right',
    'full' => ''
);

$align_class = isset($align_styles[$config['align']]) ? $align_styles[$config['align']] : 'text-center';
$is_full = $config['align'] === 'full';

$link_url = $config['link_url'] ?: '#';
$target = $config['target'] ?: '_self';
?>
<div class="mg-widget mg-widget-link-button h-full flex items-center justify-center <?php echo $align_class; ?>">
    <?php if ($config['style'] === 'button'): ?>
        <a href="<?php echo htmlspecialchars($link_url); ?>"
           target="<?php echo $target; ?>"
           class="inline-flex items-center justify-center px-6 py-3 rounded-lg font-medium transition-opacity hover:opacity-80 <?php echo $is_full ? 'w-full' : ''; ?>"
           style="background-color:<?php echo htmlspecialchars($config['btn_color']); ?>;color:<?php echo htmlspecialchars($config['btn_text_color']); ?>;">
            <?php if ($config['content_type'] === 'image' && $config['image_url']): ?>
                <img src="<?php echo htmlspecialchars($config['image_url']); ?>" alt="" class="max-h-8">
            <?php else: ?>
                <?php echo htmlspecialchars($config['text']); ?>
            <?php endif; ?>
        </a>

    <?php elseif ($config['style'] === 'link'): ?>
        <a href="<?php echo htmlspecialchars($link_url); ?>"
           target="<?php echo $target; ?>"
           class="text-mg-accent hover:underline">
            <?php if ($config['content_type'] === 'image' && $config['image_url']): ?>
                <img src="<?php echo htmlspecialchars($config['image_url']); ?>" alt="" class="inline-block max-h-6">
            <?php else: ?>
                <?php echo htmlspecialchars($config['text']); ?>
            <?php endif; ?>
        </a>

    <?php elseif ($config['style'] === 'card'): ?>
        <a href="<?php echo htmlspecialchars($link_url); ?>"
           target="<?php echo $target; ?>"
           class="block card hover:border-mg-accent transition-colors <?php echo $is_full ? 'w-full' : 'inline-block'; ?>">
            <?php if ($config['content_type'] === 'image' && $config['image_url']): ?>
                <img src="<?php echo htmlspecialchars($config['image_url']); ?>" alt="" class="w-full rounded">
            <?php else: ?>
                <span class="text-mg-text"><?php echo htmlspecialchars($config['text']); ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
</div>
