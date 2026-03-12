<?php
/**
 * Morgan Edition - Image Widget
 *
 * 이미지 위젯
 */

if (!defined('_GNUBOARD_')) exit;

class MG_Image_Widget extends MG_Widget_Base {
    protected $type = 'image';
    protected $name = '이미지';
    protected $allowed_cols = array(2, 3, 4, 6, 8, 12);
    protected $default_config = array(
        'image_url' => '',
        'alt_text' => '',
        'border_radius' => 'none'
    );

    public function render($config) {
        $config = array_merge($this->default_config, (array)$config);
        return $this->renderSkin(array('config' => $config));
    }

    public function renderConfigForm($config) {
        $config = array_merge($this->default_config, (array)$config);
        $row_height = defined('MG_WIDGET_ROW_HEIGHT') ? MG_WIDGET_ROW_HEIGHT : 300;
        $grid_width = defined('MG_WIDGET_GRID_WIDTH') ? MG_WIDGET_GRID_WIDTH : 1200;
        ob_start();
        ?>
        <div class="mg-size-guide" style="background:var(--mg-bg-tertiary);padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;">
            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:0.25rem;">권장 이미지 사이즈</div>
            <div id="image_size_guide" style="font-size:0.9rem;color:var(--mg-accent);font-weight:600;">
                계산 중...
            </div>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">이미지</label>
            <input type="file" id="image_file_upload" accept="image/*" class="mg-form-input" onchange="uploadWidgetImage()">
            <div id="image_upload_status" style="margin-top:0.5rem;font-size:0.8rem;"></div>
            <input type="hidden" name="widget_config[image_url]" id="widget_image_url" value="<?php echo htmlspecialchars($config['image_url']); ?>">
            <div id="image_preview" style="margin-top:0.5rem;">
                <?php if ($config['image_url']): ?>
                <img src="<?php echo htmlspecialchars($config['image_url']); ?>" alt="미리보기" style="max-width:100%;max-height:200px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">
                <?php endif; ?>
            </div>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">대체 텍스트 (alt)</label>
            <input type="text" name="widget_config[alt_text]" value="<?php echo htmlspecialchars($config['alt_text']); ?>" class="mg-form-input" placeholder="이미지 설명">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">모서리</label>
            <select name="widget_config[border_radius]" class="mg-form-select">
                <option value="none" <?php echo $config['border_radius'] == 'none' ? 'selected' : ''; ?>>없음</option>
                <option value="sm" <?php echo $config['border_radius'] == 'sm' ? 'selected' : ''; ?>>작게</option>
                <option value="md" <?php echo $config['border_radius'] == 'md' ? 'selected' : ''; ?>>보통</option>
                <option value="lg" <?php echo $config['border_radius'] == 'lg' ? 'selected' : ''; ?>>크게</option>
                <option value="full" <?php echo $config['border_radius'] == 'full' ? 'selected' : ''; ?>>원형</option>
            </select>
        </div>
        <script>
        (function() {
            // GridStack 기반: 현재 위젯의 w/h와 그리드 셀 크기로 권장 사이즈 계산
            var guide = document.getElementById('image_size_guide');
            if (!guide) return;

            var canvas = document.getElementById('gridCanvas');
            var cols = (typeof currentGridColumns !== 'undefined') ? currentGridColumns : 12;
            var cellW = canvas ? Math.round(canvas.clientWidth / cols) : 50;

            // 현재 편집 중인 위젯의 w/h 가져오기
            var ww = 6, wh = 2;
            if (typeof currentWidgetId !== 'undefined' && currentWidgetId && typeof grid !== 'undefined' && grid) {
                var items = grid.getGridItems();
                for (var i = 0; i < items.length; i++) {
                    var node = items[i].gridstackNode;
                    if (node && node.id == currentWidgetId) {
                        ww = node.w || 6;
                        wh = node.h || 2;
                        break;
                    }
                }
            }

            var pxW = cellW * ww;
            var pxH = cellW * wh;
            guide.innerHTML = pxW + ' x ' + pxH + ' px'
                + ' <span style="font-weight:normal;color:var(--mg-text-muted);">(' + ww + '×' + wh + '칸, 모바일 자동 조절)</span>';
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}

MG_Widget_Factory::register('image', 'MG_Image_Widget');
