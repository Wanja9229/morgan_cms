<?php
/**
 * Morgan Edition - Link/Button Widget
 *
 * 링크 또는 버튼 위젯 (텍스트/이미지 선택 가능)
 */

if (!defined('_GNUBOARD_')) exit;

class MG_LinkButton_Widget extends MG_Widget_Base {
    protected $type = 'link_button';
    protected $name = '링크/버튼';
    protected $allowed_cols = array(2, 3, 4, 6, 8, 12);
    protected $default_config = array(
        'content_type' => 'text',  // text or image
        'text' => '버튼',
        'image_url' => '',
        'link_url' => '#',
        'target' => '_self',
        'style' => 'button',  // button, link, card
        'align' => 'center',
        'btn_color' => '#f59f0a',
        'btn_text_color' => '#000000'
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
            <label class="mg-form-label">콘텐츠 유형</label>
            <select name="widget_config[content_type]" id="link_content_type" class="mg-form-select" onchange="toggleLinkContentType()">
                <option value="text" <?php echo $config['content_type'] == 'text' ? 'selected' : ''; ?>>텍스트</option>
                <option value="image" <?php echo $config['content_type'] == 'image' ? 'selected' : ''; ?>>이미지</option>
            </select>
        </div>

        <div id="link_text_fields" style="<?php echo $config['content_type'] == 'image' ? 'display:none;' : ''; ?>">
            <div class="mg-form-group">
                <label class="mg-form-label">텍스트</label>
                <input type="text" name="widget_config[text]" value="<?php echo htmlspecialchars($config['text']); ?>" class="mg-form-input">
            </div>
        </div>

        <div id="link_image_fields" style="<?php echo $config['content_type'] == 'text' ? 'display:none;' : ''; ?>">
            <div class="mg-form-group">
                <label class="mg-form-label">이미지</label>
                <input type="file" id="link_image_file_upload" accept="image/*" class="mg-form-input" onchange="uploadLinkImage()">
                <div id="link_image_upload_status" style="margin-top:0.5rem;font-size:0.8rem;"></div>
                <input type="hidden" name="widget_config[image_url]" id="link_widget_image_url" value="<?php echo htmlspecialchars($config['image_url']); ?>">
                <div id="link_image_preview" style="margin-top:0.5rem;">
                    <?php if ($config['image_url']): ?>
                    <img src="<?php echo htmlspecialchars($config['image_url']); ?>" alt="미리보기" style="max-width:100%;max-height:150px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="mg-form-group">
            <label class="mg-form-label">링크 URL</label>
            <input type="text" name="widget_config[link_url]" value="<?php echo htmlspecialchars($config['link_url']); ?>" class="mg-form-input" placeholder="https://example.com">
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">열기 방식</label>
                <select name="widget_config[target]" class="mg-form-select">
                    <option value="_self" <?php echo $config['target'] == '_self' ? 'selected' : ''; ?>>현재 창</option>
                    <option value="_blank" <?php echo $config['target'] == '_blank' ? 'selected' : ''; ?>>새 창</option>
                </select>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">스타일</label>
                <select name="widget_config[style]" class="mg-form-select">
                    <option value="button" <?php echo $config['style'] == 'button' ? 'selected' : ''; ?>>버튼</option>
                    <option value="link" <?php echo $config['style'] == 'link' ? 'selected' : ''; ?>>텍스트 링크</option>
                    <option value="card" <?php echo $config['style'] == 'card' ? 'selected' : ''; ?>>카드</option>
                </select>
            </div>
        </div>

        <div class="mg-form-group">
            <label class="mg-form-label">정렬</label>
            <select name="widget_config[align]" class="mg-form-select">
                <option value="left" <?php echo $config['align'] == 'left' ? 'selected' : ''; ?>>왼쪽</option>
                <option value="center" <?php echo $config['align'] == 'center' ? 'selected' : ''; ?>>가운데</option>
                <option value="right" <?php echo $config['align'] == 'right' ? 'selected' : ''; ?>>오른쪽</option>
                <option value="full" <?php echo $config['align'] == 'full' ? 'selected' : ''; ?>>전체 너비</option>
            </select>
        </div>

        <div id="link_button_colors" style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
            <div class="mg-form-group">
                <label class="mg-form-label">버튼 색상</label>
                <input type="color" name="widget_config[btn_color]" value="<?php echo $config['btn_color']; ?>" class="mg-form-input" style="height:40px;padding:4px;">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">버튼 텍스트 색상</label>
                <input type="color" name="widget_config[btn_text_color]" value="<?php echo $config['btn_text_color']; ?>" class="mg-form-input" style="height:40px;padding:4px;">
            </div>
        </div>

        <?php
        return ob_get_clean();
    }
}

MG_Widget_Factory::register('link_button', 'MG_LinkButton_Widget');
