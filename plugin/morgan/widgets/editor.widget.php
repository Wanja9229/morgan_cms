<?php
/**
 * Morgan Edition - Editor Widget
 *
 * HTML 에디터로 자유롭게 콘텐츠 작성
 */

if (!defined('_GNUBOARD_')) exit;

class MG_Editor_Widget extends MG_Widget_Base {
    protected $type = 'editor';
    protected $name = '에디터';
    protected $allowed_cols = array(2, 3, 4, 6, 8, 12);
    protected $default_config = array(
        'title' => '',
        'content' => '',
        'show_title' => true
    );

    public function render($config) {
        $config = array_merge($this->default_config, (array)$config);

        return $this->renderSkin(array(
            'config' => $config,
            'title' => $config['title'],
            'content' => $config['content'],
            'show_title' => $config['show_title']
        ));
    }

    public function renderConfigForm($config) {
        $config = array_merge($this->default_config, (array)$config);
        ob_start();
        ?>
        <div class="mg-form-group">
            <label class="mg-form-label">제목</label>
            <input type="text" name="widget_config[title]" value="<?php echo htmlspecialchars($config['title']); ?>" class="mg-form-input">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">제목 표시</label>
            <select name="widget_config[show_title]" class="mg-form-select">
                <option value="1" <?php echo $config['show_title'] ? 'selected' : ''; ?>>표시</option>
                <option value="0" <?php echo !$config['show_title'] ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">내용</label>
            <textarea name="widget_config[content]" id="widget_content_editor" rows="10" class="mg-form-textarea"><?php echo htmlspecialchars($config['content']); ?></textarea>
            <small class="text-mg-text-muted">HTML 태그 사용 가능</small>
        </div>
        <?php
        return ob_get_clean();
    }
}

// 팩토리에 등록
MG_Widget_Factory::register('editor', 'MG_Editor_Widget');
