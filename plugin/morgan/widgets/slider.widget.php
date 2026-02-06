<?php
/**
 * Morgan Edition - Slider Widget
 *
 * 이미지 슬라이더/캐러셀
 */

if (!defined('_GNUBOARD_')) exit;

class MG_Slider_Widget extends MG_Widget_Base {
    protected $type = 'slider';
    protected $name = '슬라이더';
    protected $allowed_cols = array(6, 8, 12);
    protected $default_config = array(
        'slides' => array(),
        'autoplay' => true,
        'interval' => 5000,
        'show_arrows' => true,
        'show_dots' => true,
        'height' => 300
    );

    public function render($config) {
        $config = array_merge($this->default_config, (array)$config);

        // slides가 JSON 문자열인 경우 배열로 변환
        if (is_string($config['slides'])) {
            $config['slides'] = json_decode($config['slides'], true) ?: array();
        }

        return $this->renderSkin(array(
            'config' => $config,
            'slides' => $config['slides'],
            'autoplay' => $config['autoplay'],
            'interval' => $config['interval'],
            'show_arrows' => $config['show_arrows'],
            'show_dots' => $config['show_dots'],
            'height' => $config['height']
        ));
    }

    public function renderConfigForm($config) {
        $config = array_merge($this->default_config, (array)$config);

        // slides가 JSON 문자열인 경우 배열로 변환
        if (is_string($config['slides'])) {
            $config['slides'] = json_decode($config['slides'], true) ?: array();
        }

        $row_height = defined('MG_WIDGET_ROW_HEIGHT') ? MG_WIDGET_ROW_HEIGHT : 300;
        $grid_width = defined('MG_WIDGET_GRID_WIDTH') ? MG_WIDGET_GRID_WIDTH : 1200;
        ob_start();
        ?>
        <div class="mg-size-guide" style="background:var(--mg-bg-tertiary);padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1rem;">
            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:0.25rem;">📐 권장 슬라이드 이미지 사이즈</div>
            <div id="slider_size_guide" style="font-size:0.9rem;color:var(--mg-accent);font-weight:600;">
                컬럼 너비 선택 시 가이드가 표시됩니다
            </div>
        </div>
        <script>
        (function() {
            var ROW_HEIGHT = <?php echo $row_height; ?>;
            var GRID_WIDTH = <?php echo $grid_width; ?>;
            var GAP = 16;
            function updateSliderGuide() {
                var colSelect = document.querySelector('[name="widget_cols"]');
                var guide = document.getElementById('slider_size_guide');
                if (!colSelect || !guide) return;
                var cols = parseInt(colSelect.value) || 12;
                var width = Math.round((GRID_WIDTH / 12) * cols - GAP);
                guide.innerHTML = width + ' x ' + ROW_HEIGHT + ' px';
            }
            setTimeout(updateSliderGuide, 100);
            var colSelect = document.querySelector('[name="widget_cols"]');
            if (colSelect) colSelect.addEventListener('change', updateSliderGuide);
        })();
        </script>
        <div class="mg-form-group">
            <label class="mg-form-label">자동 재생</label>
            <select name="widget_config[autoplay]" class="mg-form-select">
                <option value="1" <?php echo $config['autoplay'] ? 'selected' : ''; ?>>사용</option>
                <option value="0" <?php echo !$config['autoplay'] ? 'selected' : ''; ?>>사용안함</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">전환 간격 (ms)</label>
            <input type="number" name="widget_config[interval]" value="<?php echo (int)$config['interval']; ?>" min="1000" max="10000" step="500" class="mg-form-input" style="width:120px;">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">화살표 표시</label>
            <select name="widget_config[show_arrows]" class="mg-form-select">
                <option value="1" <?php echo $config['show_arrows'] ? 'selected' : ''; ?>>표시</option>
                <option value="0" <?php echo !$config['show_arrows'] ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">인디케이터 표시</label>
            <select name="widget_config[show_dots]" class="mg-form-select">
                <option value="1" <?php echo $config['show_dots'] ? 'selected' : ''; ?>>표시</option>
                <option value="0" <?php echo !$config['show_dots'] ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">슬라이드 목록</label>
            <div id="slider_slides_container">
                <?php if (empty($config['slides'])): ?>
                <p class="text-mg-text-muted text-sm" id="no_slides_msg">등록된 슬라이드가 없습니다.</p>
                <?php else: ?>
                <?php foreach ($config['slides'] as $idx => $slide): ?>
                <div class="slider-slide-item" style="margin-bottom:0.75rem;padding:0.75rem;background:var(--mg-bg-secondary);border-radius:4px;">
                    <div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem;">
                        <input type="file" accept="image/*" class="mg-form-input" style="flex:1;" onchange="uploadSlideImage(this, <?php echo $idx; ?>)">
                        <button type="button" class="mg-btn mg-btn-sm mg-btn-danger" onclick="this.closest('.slider-slide-item').remove()">삭제</button>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">
                        <input type="text" name="widget_config[slides][<?php echo $idx; ?>][image]" id="slide_image_<?php echo $idx; ?>" value="<?php echo htmlspecialchars($slide['image'] ?? ''); ?>" placeholder="이미지 URL" class="mg-form-input">
                        <input type="text" name="widget_config[slides][<?php echo $idx; ?>][link]" value="<?php echo htmlspecialchars($slide['link'] ?? ''); ?>" placeholder="링크 URL (선택)" class="mg-form-input">
                    </div>
                    <input type="text" name="widget_config[slides][<?php echo $idx; ?>][title]" value="<?php echo htmlspecialchars($slide['title'] ?? ''); ?>" placeholder="제목 (선택)" class="mg-form-input" style="margin-top:0.5rem;">
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="mg-btn mg-btn-sm mg-btn-secondary" onclick="addSlide()">+ 슬라이드 추가</button>
        </div>
        <script>
        var slideIndex = <?php echo count($config['slides']); ?>;

        function addSlide() {
            var msg = document.getElementById('no_slides_msg');
            if (msg) msg.remove();

            var html = '<div class="slider-slide-item" style="margin-bottom:0.75rem;padding:0.75rem;background:var(--mg-bg-secondary);border-radius:4px;">' +
                '<div style="display:flex;gap:0.5rem;align-items:center;margin-bottom:0.5rem;">' +
                '<input type="file" accept="image/*" class="mg-form-input" style="flex:1;" onchange="uploadSlideImage(this, ' + slideIndex + ')">' +
                '<button type="button" class="mg-btn mg-btn-sm mg-btn-danger" onclick="this.closest(\'.slider-slide-item\').remove()">삭제</button>' +
                '</div>' +
                '<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;">' +
                '<input type="text" name="widget_config[slides][' + slideIndex + '][image]" id="slide_image_' + slideIndex + '" placeholder="이미지 URL" class="mg-form-input">' +
                '<input type="text" name="widget_config[slides][' + slideIndex + '][link]" placeholder="링크 URL (선택)" class="mg-form-input">' +
                '</div>' +
                '<input type="text" name="widget_config[slides][' + slideIndex + '][title]" placeholder="제목 (선택)" class="mg-form-input" style="margin-top:0.5rem;">' +
                '</div>';
            document.getElementById('slider_slides_container').insertAdjacentHTML('beforeend', html);
            slideIndex++;
        }
        </script>
        <?php
        return ob_get_clean();
    }
}

// 팩토리에 등록
MG_Widget_Factory::register('slider', 'MG_Slider_Widget');
