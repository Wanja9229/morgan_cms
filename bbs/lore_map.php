<?php
/**
 * Morgan Edition - 세계관 지도 페이지
 * 맵 지역(mg_map_region) 기반 마커 표시
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 세계관 위키 활성화 확인
if (mg_config('lore_use', '1') == '0') {
    alert('세계관 위키가 비활성화되어 있습니다.', G5_BBS_URL);
}

$map_image = mg_config('expedition_map_image', '');
if (!$map_image) {
    alert('세계관 지도가 설정되지 않았습니다.', G5_BBS_URL . '/lore.php');
}

// 맵 지역 목록 (사용중 + 좌표 있는 것만)
$regions = mg_get_map_regions(true);
$map_regions = array();
foreach ($regions as $r) {
    $map_regions[] = array(
        'mr_id'    => (int)$r['mr_id'],
        'mr_name'  => $r['mr_name'],
        'mr_desc'  => $r['mr_desc'] ?? '',
        'mr_image' => $r['mr_image'] ?? '',
        'mr_map_x' => (float)$r['mr_map_x'],
        'mr_map_y' => (float)$r['mr_map_y'],
        'mr_marker_style' => $r['mr_marker_style'] ?? 'pin',
    );
}

$g5['title'] = '지도 - 세계관 위키';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner px-4 py-6">
    <!-- 페이지 헤더 -->
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-lg bg-mg-accent/20 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-mg-text-primary">지도</h1>
            <p class="text-sm text-mg-text-muted"><?php echo htmlspecialchars(mg_config('lore_map_desc', '이 세계의 지도를 살펴보세요')); ?></p>
        </div>
    </div>

    <!-- 서브 탭 (2뎁스) -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/lore.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-bg-secondary border border-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent hover:border-mg-accent/30">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            위키
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/lore_timeline.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-bg-secondary border border-mg-bg-tertiary text-mg-text-secondary hover:text-mg-accent hover:border-mg-accent/30">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            연대기
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/lore_map.php" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors bg-mg-accent/10 border border-mg-accent/30 text-mg-accent">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            지도
        </a>
    </div>

    <!-- 맵 영역 -->
    <div id="lore-map-container" style="position:relative;overflow:auto;max-height:75vh;border-radius:12px;border:1px solid var(--mg-bg-tertiary);background:var(--mg-bg-secondary);">
        <img src="<?php echo htmlspecialchars($map_image); ?>" id="lore-map-image" style="display:block;width:100%;" alt="세계관 지도" draggable="false">
        <div id="lore-map-markers"></div>
        <div id="lore-map-popup" style="display:none;position:absolute;z-index:20;"></div>
    </div>
</div>

<style>
.lore-marker { position:absolute; width:44px; height:44px; margin-left:-22px; margin-top:-44px; cursor:pointer; transition:transform 0.15s; z-index:5; user-select:none; display:flex; align-items:center; justify-content:center; }
.lore-marker:hover { transform:scale(1.2); z-index:10; }
.lore-marker svg { width:100%; height:100%; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4)); }
.lore-popup { width:280px; max-width:calc(100vw - 3rem); background:var(--mg-bg-secondary); border:1px solid var(--mg-bg-tertiary); border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,0.4); position:relative; }
.lore-popup-close { position:absolute; top:6px; right:6px; width:28px; height:28px; border-radius:50%; background:rgba(0,0,0,0.5); color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:2; font-size:14px; line-height:1; }
.lore-popup-close:hover { background:rgba(0,0,0,0.7); }
.lore-popup-img { width:100%; height:120px; object-fit:cover; }
.lore-popup-body { padding:12px; }
.lore-popup-name { font-weight:600; color:var(--mg-text-primary); font-size:0.95rem; }
.lore-popup-desc { font-size:0.75rem; color:var(--mg-text-muted); margin-top:4px; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:3; overflow:hidden; }
</style>

<script>
(function() {
    var regions = <?php echo json_encode($map_regions); ?>;
    var markersEl = document.getElementById('lore-map-markers');
    var popupEl = document.getElementById('lore-map-popup');
    var container = document.getElementById('lore-map-container');

    function getMarkerSVG(style, color, inner) {
        switch (style) {
            case 'circle':
                return '<svg viewBox="0 0 28 28" width="40" height="40"><circle cx="14" cy="14" r="12" fill="'+color+'" stroke="'+inner+'" stroke-width="2.5"/><circle cx="14" cy="14" r="4" fill="'+inner+'"/></svg>';
            case 'diamond':
                return '<svg viewBox="0 0 24 32" width="30" height="40"><path d="M12 1 L23 16 L12 31 L1 16 Z" fill="'+color+'" stroke="'+inner+'" stroke-width="1.5"/><circle cx="12" cy="16" r="3.5" fill="'+inner+'"/></svg>';
            case 'flag':
                return '<svg viewBox="0 0 24 36" width="27" height="40"><rect x="10" y="6" width="2.5" height="26" rx="1" fill="'+color+'"/><path d="M12.5 6 L23 11 L12.5 16 Z" fill="'+color+'"/><circle cx="11.25" cy="4.5" r="2.5" fill="'+color+'"/></svg>';
            default:
                return '<svg viewBox="0 0 24 36" width="27" height="40"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z" fill="'+color+'"/><circle cx="12" cy="12" r="5" fill="'+inner+'"/></svg>';
        }
    }

    regions.forEach(function(region) {
        var marker = document.createElement('div');
        marker.className = 'lore-marker';
        marker.style.left = region.mr_map_x + '%';
        marker.style.top = region.mr_map_y + '%';
        marker.title = region.mr_name;
        marker.innerHTML = getMarkerSVG(region.mr_marker_style || 'pin', 'var(--mg-accent)', 'var(--mg-bg-primary)');

        marker.onclick = function(e) {
            e.stopPropagation();
            showPopup(region, marker);
        };

        markersEl.appendChild(marker);
    });

    container.addEventListener('click', function(e) {
        if (!e.target.closest('.lore-marker') && !e.target.closest('.lore-popup')) {
            popupEl.style.display = 'none';
        }
    });

    function showPopup(region, markerEl) {
        var imgHtml = region.mr_image
            ? '<img class="lore-popup-img" src="' + escHtml(region.mr_image) + '" alt="' + escHtml(region.mr_name) + '">'
            : '';

        popupEl.innerHTML = '<div class="lore-popup">' +
            '<button class="lore-popup-close" onclick="this.closest(\'#lore-map-popup\').style.display=\'none\'" type="button">&times;</button>' +
            imgHtml +
            '<div class="lore-popup-body">' +
                '<div class="lore-popup-name">' + escHtml(region.mr_name) + '</div>' +
                (region.mr_desc ? '<div class="lore-popup-desc">' + escHtml(region.mr_desc) + '</div>' : '') +
            '</div></div>';

        popupEl.style.display = 'block';

        var mapRect = container.getBoundingClientRect();
        var markerRect = markerEl.getBoundingClientRect();
        var popupW = popupEl.firstChild.offsetWidth || 280;
        var popupH = popupEl.firstChild.offsetHeight;

        // 마커 중앙 기준 위치 계산
        var markerCX = markerRect.left - mapRect.left + container.scrollLeft + markerRect.width / 2;
        var markerTopY = markerRect.top - mapRect.top + container.scrollTop;

        var popupLeft = markerCX - popupW / 2;
        var popupTop = markerTopY - popupH - 10;

        // 뷰포트 기준 클램핑 (현재 보이는 영역 내)
        var visibleLeft = container.scrollLeft + 8;
        var visibleRight = container.scrollLeft + container.clientWidth - 8;
        if (popupLeft < visibleLeft) popupLeft = visibleLeft;
        if (popupLeft + popupW > visibleRight) popupLeft = visibleRight - popupW;

        // 상단 넘침 → 마커 아래로
        if (popupTop < container.scrollTop + 8) {
            popupTop = markerTopY + markerRect.height + 10;
        }

        popupEl.style.left = popupLeft + 'px';
        popupEl.style.top = popupTop + 'px';
    }

    function escHtml(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
})();
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
