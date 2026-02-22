<?php
/**
 * Morgan Edition - 프로필 배경 효과 (Vanta.js 기반)
 *
 * 사용 가능한 변수: $profile_bg_id, $profile_bg_color (hex #rrggbb)
 * character_view.php에서 include됨
 */
if (!defined('_GNUBOARD_')) exit;

$valid_effects = mg_get_profile_bg_list();
if (!isset($valid_effects[$profile_bg_id])) return;

// 유저 커스텀 색상 (기본값: 앰버)
$user_color = (!empty($profile_bg_color) && preg_match('/^#[0-9a-fA-F]{6}$/', $profile_bg_color))
    ? $profile_bg_color : '#f59f0a';

// hex → 0x 정수 (JS용)
$color_int = '0x' . substr($user_color, 1);

// 다크 배경색 (Morgan 테마)
$bg_int = '0x1e1f22';
$bg_sec  = '0x2b2d31';
$bg_ter  = '0x313338';

// p5.js 기반 이펙트 (three.js 대신 p5.js 필요)
$p5_effects = array('topology', 'trunk');
$is_p5 = in_array($profile_bg_id, $p5_effects);

// 이펙트별 Vanta 옵션 (JS로 출력)
$effect_configs = array(
    'birds' => "{
        backgroundColor: {$bg_int},
        color1: {$color_int},
        color2: {$bg_sec},
        colorMode: 'varianceGradient',
        birdSize: 1.2,
        wingSpan: 25,
        speedLimit: 4,
        separation: 30,
        alignment: 30,
        cohesion: 25,
        quantity: 4
    }",
    'fog' => "{
        highlightColor: {$color_int},
        midtoneColor: {$bg_ter},
        lowlightColor: {$bg_sec},
        baseColor: {$bg_int},
        blurFactor: 0.5,
        speed: 1.2,
        zoom: 0.8
    }",
    'waves' => "{
        color: {$color_int},
        shininess: 35,
        waveHeight: 15,
        waveSpeed: 0.8,
        zoom: 0.9
    }",
    'clouds' => "{
        backgroundColor: {$bg_int},
        skyColor: {$bg_sec},
        cloudColor: {$color_int},
        cloudShadowColor: {$bg_ter},
        sunColor: {$color_int},
        sunGlareColor: {$color_int},
        sunlightColor: {$color_int},
        speed: 0.8
    }",
    // clouds2 제거: CDN 로드 시 noise.png 텍스처 경로 깨짐
    'globe' => "{
        backgroundColor: {$bg_int},
        color: {$color_int},
        color2: {$bg_ter},
        size: 1.0,
        points: 8,
        maxDistance: 22
    }",
    'net' => "{
        backgroundColor: {$bg_int},
        color: {$color_int},
        points: 10,
        maxDistance: 22,
        spacing: 16,
        showDots: true
    }",
    'cells' => "{
        backgroundColor: {$bg_int},
        color1: {$color_int},
        color2: {$bg_ter},
        size: 1.5,
        speed: 1.0,
        scaleMobile: 5
    }",
    'trunk' => "{
        color: {$color_int},
        backgroundColor: {$bg_int},
        spacing: 0,
        chaos: 3.0
    }",
    'topology' => "{
        color: {$color_int},
        backgroundColor: {$bg_int}
    }",
    'dots' => "{
        backgroundColor: {$bg_int},
        color: {$color_int},
        color2: {$bg_ter},
        size: 3.0,
        spacing: 35,
        showLines: true
    }",
    'rings' => "{
        backgroundColor: {$bg_int},
        color: {$color_int}
    }",
    'ripple' => "{
        backgroundColor: {$bg_int},
        color: {$color_int},
        amplitudeFactor: 1.0,
        ringFactor: 1.0,
        rotationFactor: 1.0,
        speed: 1.0,
        scaleMobile: 3
    }",
    'halo' => "{
        backgroundColor: {$bg_int},
        baseColor: {$color_int},
        color2: {$bg_ter},
        size: 1.2,
        amplitudeFactor: 1.2,
        xOffset: 0.1,
        yOffset: 0.05,
        speed: 1.0
    }",
);

$js_config = isset($effect_configs[$profile_bg_id]) ? $effect_configs[$profile_bg_id] : null;
if (!$js_config) return;

$vanta_name = strtoupper($profile_bg_id);
?>

<style>
#vanta-bg {
    position: fixed;
    top: 48px;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: -1;
    pointer-events: none;
}
@media (min-width: 1024px) {
    #vanta-bg { left: 56px; }
}
#vanta-bg canvas {
    pointer-events: none !important;
}
<?php if ($profile_bg_id === 'waves'): ?>
#vanta-bg { opacity: 0.35; }
<?php elseif (!empty($profile_bg_image)): ?>
#vanta-bg { opacity: 0.5; }
<?php endif; ?>
</style>

<div id="vanta-bg"></div>

<script>
(function() {
    var el = document.getElementById('vanta-bg');
    if (!el) return;

    // 모바일 성능 최적화: 768px 미만 비활성화
    if (window.innerWidth < 768) return;

    var EFFECT_NAME = '<?php echo $vanta_name; ?>';
    var IS_P5 = <?php echo $is_p5 ? 'true' : 'false'; ?>;
    var depUrl = IS_P5
        ? 'https://cdnjs.cloudflare.com/ajax/libs/p5.js/1.9.0/p5.min.js'
        : 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js';
    var vantaUrl = 'https://cdnjs.cloudflare.com/ajax/libs/vanta/0.5.24/vanta.<?php echo $profile_bg_id; ?>.min.js';

    function loadScript(src, cb) {
        // 이미 로딩된 스크립트는 스킵
        if (document.querySelector('script[src="' + src + '"]')) { cb(); return; }
        var s = document.createElement('script');
        s.src = src;
        s.onload = cb;
        s.onerror = function() { console.warn('Vanta dep load fail:', src); };
        document.head.appendChild(s);
    }

    function initEffect() {
        if (typeof VANTA === 'undefined' || typeof VANTA[EFFECT_NAME] === 'undefined') return;

        var opts = Object.assign({
            el: el,
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200,
            minWidth: 200,
            scale: 1,
            scaleMobile: 2
        }, <?php echo $js_config; ?>);

        if (IS_P5 && typeof p5 !== 'undefined') { opts.p5 = p5; }

        var effect = VANTA[EFFECT_NAME](opts);

        // SPA 정리: #vanta-bg 제거 시 자동 destroy
        var observer = new MutationObserver(function() {
            if (!document.getElementById('vanta-bg')) {
                if (effect) { effect.destroy(); effect = null; }
                observer.disconnect();
            }
        });
        observer.observe(document.getElementById('main-content') || document.body, {
            childList: true, subtree: true
        });

        window.addEventListener('beforeunload', function() {
            if (effect) { effect.destroy(); effect = null; }
        });
    }

    // 이미 로딩 완료 상태면 바로 실행
    if (typeof VANTA !== 'undefined' && typeof VANTA[EFFECT_NAME] !== 'undefined') {
        initEffect();
        return;
    }

    // CDN 순서대로 로딩 → 이펙트 초기화
    loadScript(depUrl, function() {
        loadScript(vantaUrl, function() {
            initEffect();
        });
    });
})();
</script>
