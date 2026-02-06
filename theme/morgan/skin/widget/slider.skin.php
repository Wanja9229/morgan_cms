<?php
/**
 * Morgan Edition - Slider Widget Skin
 *
 * 사용 가능한 변수:
 * $config - 위젯 설정
 * $slides - 슬라이드 배열 [image, link, title]
 * $autoplay - 자동 재생 여부
 * $interval - 전환 간격 (ms)
 * $show_arrows - 화살표 표시 여부
 * $show_dots - 인디케이터 표시 여부
 * $height - 슬라이더 높이
 */

if (!defined('_GNUBOARD_')) exit;

$slider_id = 'mg_slider_'.uniqid();
?>
<div class="mg-widget mg-widget-slider h-full">
    <?php if (empty($slides)): ?>
    <div class="card h-full flex items-center justify-center">
        <p class="text-mg-text-muted text-center">등록된 슬라이드가 없습니다.</p>
    </div>
    <?php else: ?>
    <div id="<?php echo $slider_id; ?>" class="mg-slider relative overflow-hidden rounded-lg h-full">
        <!-- Slides -->
        <div class="mg-slider-track flex transition-transform duration-500" style="height:100%;">
            <?php foreach ($slides as $idx => $slide): ?>
            <div class="mg-slider-slide flex-shrink-0 w-full h-full relative">
                <?php if (!empty($slide['link'])): ?>
                <a href="<?php echo htmlspecialchars($slide['link']); ?>" class="block w-full h-full">
                <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($slide['image']); ?>" alt="<?php echo htmlspecialchars($slide['title'] ?? ''); ?>" class="w-full h-full object-cover">
                    <?php if (!empty($slide['title'])): ?>
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                        <p class="text-white text-lg font-semibold"><?php echo htmlspecialchars($slide['title']); ?></p>
                    </div>
                    <?php endif; ?>
                <?php if (!empty($slide['link'])): ?>
                </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($show_arrows && count($slides) > 1): ?>
        <!-- Arrows -->
        <button type="button" class="mg-slider-prev absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition-colors" onclick="mgSliderPrev('<?php echo $slider_id; ?>')">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <button type="button" class="mg-slider-next absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/70 transition-colors" onclick="mgSliderNext('<?php echo $slider_id; ?>')">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </button>
        <?php endif; ?>

        <?php if ($show_dots && count($slides) > 1): ?>
        <!-- Dots -->
        <div class="mg-slider-dots absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
            <?php foreach ($slides as $idx => $slide): ?>
            <button type="button" class="w-2 h-2 rounded-full bg-white/50 hover:bg-white transition-colors <?php echo $idx === 0 ? 'bg-white' : ''; ?>" onclick="mgSliderGoto('<?php echo $slider_id; ?>', <?php echo $idx; ?>)" data-index="<?php echo $idx; ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <script>
    (function() {
        var slider = document.getElementById('<?php echo $slider_id; ?>');
        var track = slider.querySelector('.mg-slider-track');
        var slideCount = <?php echo count($slides); ?>;
        var currentIndex = 0;
        var autoplay = <?php echo $autoplay ? 'true' : 'false'; ?>;
        var interval = <?php echo (int)$interval; ?>;
        var timer = null;

        window.mgSliderGoto = window.mgSliderGoto || function(id, index) {
            var s = document.getElementById(id);
            var t = s.querySelector('.mg-slider-track');
            var dots = s.querySelectorAll('.mg-slider-dots button');
            t.style.transform = 'translateX(-' + (index * 100) + '%)';
            dots.forEach(function(d, i) {
                d.classList.toggle('bg-white', i === index);
                d.classList.toggle('bg-white/50', i !== index);
            });
            s.dataset.currentIndex = index;
        };

        window.mgSliderPrev = window.mgSliderPrev || function(id) {
            var s = document.getElementById(id);
            var count = s.querySelectorAll('.mg-slider-slide').length;
            var idx = parseInt(s.dataset.currentIndex || 0);
            idx = (idx - 1 + count) % count;
            mgSliderGoto(id, idx);
        };

        window.mgSliderNext = window.mgSliderNext || function(id) {
            var s = document.getElementById(id);
            var count = s.querySelectorAll('.mg-slider-slide').length;
            var idx = parseInt(s.dataset.currentIndex || 0);
            idx = (idx + 1) % count;
            mgSliderGoto(id, idx);
        };

        slider.dataset.currentIndex = 0;

        if (autoplay && slideCount > 1) {
            timer = setInterval(function() {
                mgSliderNext('<?php echo $slider_id; ?>');
            }, interval);

            slider.addEventListener('mouseenter', function() { clearInterval(timer); });
            slider.addEventListener('mouseleave', function() {
                timer = setInterval(function() {
                    mgSliderNext('<?php echo $slider_id; ?>');
                }, interval);
            });
        }
    })();
    </script>
    <?php endif; ?>
</div>
