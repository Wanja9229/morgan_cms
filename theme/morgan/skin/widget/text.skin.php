<?php
/**
 * Morgan Edition - Text Widget Skin
 */

if (!defined('_GNUBOARD_')) exit;

$font_sizes = array(
    'sm' => 'text-sm',
    'base' => 'text-base',
    'lg' => 'text-lg',
    'xl' => 'text-xl',
    '2xl' => 'text-2xl'
);

$font_weights = array(
    'normal' => 'font-normal',
    'medium' => 'font-medium',
    'semibold' => 'font-semibold',
    'bold' => 'font-bold'
);

$paddings = array(
    'none' => '',
    'small' => 'p-2',
    'normal' => 'p-4',
    'large' => 'p-6'
);

$size_class = isset($font_sizes[$config['font_size']]) ? $font_sizes[$config['font_size']] : 'text-base';
$weight_class = isset($font_weights[$config['font_weight']]) ? $font_weights[$config['font_weight']] : '';
$padding_class = isset($paddings[$config['padding']]) ? $paddings[$config['padding']] : 'p-4';
$align_class = 'text-' . ($config['text_align'] ?: 'left');

$style = '';
if ($config['text_color']) {
    $style .= 'color:' . htmlspecialchars($config['text_color']) . ';';
}
if ($config['bg_color']) {
    $style .= 'background-color:' . htmlspecialchars($config['bg_color']) . ';';
}
?>
<div class="mg-widget mg-widget-text h-full overflow-auto <?php echo "{$size_class} {$weight_class} {$padding_class} {$align_class}"; ?> rounded-lg" style="<?php echo $style; ?>">
    <?php echo $config['content']; ?>
</div>
