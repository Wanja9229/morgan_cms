<?php
/**
 * Morgan Edition - Text Widget
 *
 * 텍스트 콘텐츠 위젯
 */

if (!defined('_GNUBOARD_')) exit;

class MG_Text_Widget extends MG_Widget_Base {
    protected $type = 'text';
    protected $name = '텍스트';
    protected $allowed_cols = array(2, 3, 4, 6, 8, 12);
    protected $default_config = array(
        'content' => '',
        'font_size' => 'base',
        'font_weight' => 'normal',
        'text_align' => 'left',
        'text_color' => '',
        'bg_color' => '',
        'padding' => 'normal'
    );

    public function render($config) {
        $config = array_merge($this->default_config, (array)$config);
        return $this->renderSkin(array('config' => $config));
    }

    public function renderConfigForm($config) {
        $config = array_merge($this->default_config, (array)$config);
        ob_start();
        ?>
        <div class="mg-form-group">
            <label class="mg-form-label">텍스트 내용</label>
            <textarea name="widget_config[content]" rows="5" class="mg-form-textarea"><?php echo htmlspecialchars($config['content']); ?></textarea>
            <small class="text-mg-text-muted">HTML 태그 사용 가능</small>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">글자 크기</label>
                <select name="widget_config[font_size]" class="mg-form-select">
                    <option value="sm" <?php echo $config['font_size'] == 'sm' ? 'selected' : ''; ?>>작게</option>
                    <option value="base" <?php echo $config['font_size'] == 'base' ? 'selected' : ''; ?>>보통</option>
                    <option value="lg" <?php echo $config['font_size'] == 'lg' ? 'selected' : ''; ?>>크게</option>
                    <option value="xl" <?php echo $config['font_size'] == 'xl' ? 'selected' : ''; ?>>매우 크게</option>
                    <option value="2xl" <?php echo $config['font_size'] == '2xl' ? 'selected' : ''; ?>>제목</option>
                </select>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">글자 굵기</label>
                <select name="widget_config[font_weight]" class="mg-form-select">
                    <option value="normal" <?php echo $config['font_weight'] == 'normal' ? 'selected' : ''; ?>>보통</option>
                    <option value="medium" <?php echo $config['font_weight'] == 'medium' ? 'selected' : ''; ?>>중간</option>
                    <option value="semibold" <?php echo $config['font_weight'] == 'semibold' ? 'selected' : ''; ?>>약간 굵게</option>
                    <option value="bold" <?php echo $config['font_weight'] == 'bold' ? 'selected' : ''; ?>>굵게</option>
                </select>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">정렬</label>
                <select name="widget_config[text_align]" class="mg-form-select">
                    <option value="left" <?php echo $config['text_align'] == 'left' ? 'selected' : ''; ?>>왼쪽</option>
                    <option value="center" <?php echo $config['text_align'] == 'center' ? 'selected' : ''; ?>>가운데</option>
                    <option value="right" <?php echo $config['text_align'] == 'right' ? 'selected' : ''; ?>>오른쪽</option>
                </select>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">여백</label>
                <select name="widget_config[padding]" class="mg-form-select">
                    <option value="none" <?php echo $config['padding'] == 'none' ? 'selected' : ''; ?>>없음</option>
                    <option value="small" <?php echo $config['padding'] == 'small' ? 'selected' : ''; ?>>작게</option>
                    <option value="normal" <?php echo $config['padding'] == 'normal' ? 'selected' : ''; ?>>보통</option>
                    <option value="large" <?php echo $config['padding'] == 'large' ? 'selected' : ''; ?>>크게</option>
                </select>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">글자 색상</label>
                <input type="color" name="widget_config[text_color]" value="<?php echo $config['text_color'] ?: '#f2f3f5'; ?>" class="mg-form-input" style="height:40px;padding:4px;">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">배경 색상</label>
                <input type="color" name="widget_config[bg_color]" value="<?php echo $config['bg_color'] ?: '#2b2d31'; ?>" class="mg-form-input" style="height:40px;padding:4px;">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

MG_Widget_Factory::register('text', 'MG_Text_Widget');
