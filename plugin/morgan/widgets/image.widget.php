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
                컬럼 너비 선택 시 가이드가 표시됩니다
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
            var ROW_HEIGHT = <?php echo $row_height; ?>;
            var GRID_WIDTH = <?php echo $grid_width; ?>;
            var GAP = 16; // gap-4 = 1rem = 16px

            function updateSizeGuide() {
                var colSelect = document.querySelector('[name="widget_cols"]');
                var guide = document.getElementById('image_size_guide');
                if (!colSelect || !guide) return;

                var cols = parseInt(colSelect.value) || 12;
                var width = Math.round((GRID_WIDTH / 12) * cols);
                var ratio = (width / ROW_HEIGHT).toFixed(1);
                guide.innerHTML = width + ' x ' + ROW_HEIGHT + ' px <span style="font-weight:normal;color:var(--mg-text-muted);">(비율 ' + ratio + ':1, 모바일 자동 조절)</span>';
            }

            // 초기 실행
            setTimeout(updateSizeGuide, 100);

            // 컬럼 변경 시 업데이트
            var colSelect = document.querySelector('[name="widget_cols"]');
            if (colSelect) {
                colSelect.addEventListener('change', updateSizeGuide);
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}

MG_Widget_Factory::register('image', 'MG_Image_Widget');
