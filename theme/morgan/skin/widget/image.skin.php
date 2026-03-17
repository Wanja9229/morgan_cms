<?php
/**
 * Morgan Edition - Image Widget Skin
 */

if (!defined('_GNUBOARD_')) exit;

$radius_map = array(
    'none' => '',
    'sm' => 'rounded',
    'md' => 'rounded-lg',
    'lg' => 'rounded-xl',
    'full' => 'rounded-full'
);

$radius_class = isset($radius_map[$config['border_radius']]) ? $radius_map[$config['border_radius']] : '';

$fit_map = array(
    'cover' => 'object-cover',
    'contain' => 'object-contain',
    'fill' => 'object-fill',
    'none' => 'object-none'
);
$fit_class = isset($fit_map[$config['object_fit'] ?? 'cover']) ? $fit_map[$config['object_fit'] ?? 'cover'] : 'object-cover';
?>
<div class="mg-widget mg-widget-image h-full">
    <?php if ($config['image_url']): ?>
    <img src="<?php echo htmlspecialchars($config['image_url']); ?>"
         alt="<?php echo htmlspecialchars($config['alt_text']); ?>"
         class="w-full h-full <?php echo $fit_class; ?> <?php echo $radius_class; ?>">
    <?php else: ?>
    <div class="bg-mg-bg-tertiary rounded-lg p-8 text-center text-mg-text-muted h-full flex items-center justify-center">
        이미지를 설정해주세요
    </div>
    <?php endif; ?>
</div>
