<?php
/**
 * Morgan Edition - 세계관 맵 페이지
 * 파견 시스템 맵 이미지 + 지역 마커 (읽기 전용)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 세계관 위키 활성화 확인
if (mg_config('lore_use', '1') == '0') {
    alert('세계관 위키가 비활성화되어 있습니다.', G5_BBS_URL);
}

$map_image = mg_config('expedition_map_image', '');
$marker_style = mg_config('map_marker_style', 'pin');
if (!$map_image) {
    alert('세계관 맵이 설정되지 않았습니다.', G5_BBS_URL . '/lore.php');
}

// 맵 좌표가 있는 파견지 목록
$areas = mg_get_expedition_areas();
$map_areas = array();
foreach ($areas as $area) {
    if ($area['ea_map_x'] !== null && $area['ea_map_y'] !== null && $area['ea_status'] !== 'hidden') {
        $map_areas[] = array(
            'ea_id'    => $area['ea_id'],
            'ea_name'  => $area['ea_name'],
            'ea_desc'  => $area['ea_desc'],
            'ea_image' => $area['ea_image'] ?? '',
            'ea_icon'  => $area['ea_icon'] ?? '',
            'ea_map_x' => (float)$area['ea_map_x'],
            'ea_map_y' => (float)$area['ea_map_y'],
            'ea_status' => $area['ea_status'],
        );
    }
}

$g5['title'] = '세계관 맵 - 세계관 위키';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 카테고리 탭 (세계관 위키와 통일) -->
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/lore.php" class="px-4 py-2 rounded-full text-sm font-medium transition-colors bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary">전체</a>
        <a href="<?php echo G5_BBS_URL; ?>/lore_timeline.php" class="px-4 py-2 rounded-full text-sm font-medium transition-colors bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            타임라인
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/lore_map.php" class="px-4 py-2 rounded-full text-sm font-medium transition-colors bg-mg-accent text-white flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
            세계관 맵
        </a>
    </div>

    <!-- 맵 영역 -->
    <div id="lore-map-container" style="position:relative;overflow:auto;max-height:75vh;border-radius:12px;border:1px solid var(--mg-bg-tertiary);background:var(--mg-bg-secondary);">
        <img src="<?php echo htmlspecialchars($map_image); ?>" id="lore-map-image" style="display:block;width:100%;min-width:600px;" alt="세계관 맵" draggable="false">
        <div id="lore-map-markers"></div>
        <div id="lore-map-popup" style="display:none;position:absolute;z-index:20;"></div>
    </div>
</div>

<style>
.lore-marker { position:absolute; width:40px; height:40px; margin-left:-20px; margin-top:-40px; cursor:pointer; transition:transform 0.15s; z-index:5; user-select:none; }
.lore-marker:hover { transform:scale(1.2); z-index:10; }
.lore-marker svg { width:100%; height:100%; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4)); }
.lore-marker.is-locked { opacity:0.4; cursor:default; }
.lore-popup { width:280px; background:var(--mg-bg-secondary); border:1px solid var(--mg-bg-tertiary); border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
.lore-popup-img { width:100%; height:120px; object-fit:cover; }
.lore-popup-body { padding:12px; }
.lore-popup-name { font-weight:600; color:var(--mg-text-primary); font-size:0.95rem; }
.lore-popup-desc { font-size:0.75rem; color:var(--mg-text-muted); margin-top:4px; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:3; overflow:hidden; }
</style>

<script>
(function() {
    var areas = <?php echo json_encode($map_areas); ?>;
    var MARKER_STYLE = '<?php echo $marker_style; ?>';
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

    areas.forEach(function(area) {
        var locked = area.ea_status === 'locked';

        var marker = document.createElement('div');
        marker.className = 'lore-marker' + (locked ? ' is-locked' : '');
        marker.style.left = area.ea_map_x + '%';
        marker.style.top = area.ea_map_y + '%';
        marker.title = area.ea_name;

        var pinColor = locked ? '#6b7280' : 'var(--mg-accent)';
        var pinInner = locked ? '#4b5563' : 'var(--mg-bg-primary)';
        marker.innerHTML = getMarkerSVG(MARKER_STYLE, pinColor, pinInner);

        if (!locked) {
            marker.onclick = function(e) {
                e.stopPropagation();
                showPopup(area, marker);
            };
        }

        markersEl.appendChild(marker);
    });

    container.addEventListener('click', function(e) {
        if (!e.target.closest('.lore-marker') && !e.target.closest('.lore-popup')) {
            popupEl.style.display = 'none';
        }
    });

    function showPopup(area, markerEl) {
        var imgHtml = area.ea_image
            ? '<img class="lore-popup-img" src="' + escHtml(area.ea_image) + '" alt="' + escHtml(area.ea_name) + '">'
            : '';

        popupEl.innerHTML = '<div class="lore-popup">' +
            imgHtml +
            '<div class="lore-popup-body">' +
                '<div class="lore-popup-name">' + escHtml(area.ea_name) + '</div>' +
                (area.ea_desc ? '<div class="lore-popup-desc">' + escHtml(area.ea_desc) + '</div>' : '') +
                (area.ea_status === 'locked' ? '<div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:8px;">잠금된 지역</div>' : '') +
            '</div></div>';

        var mapRect = container.getBoundingClientRect();
        var markerRect = markerEl.getBoundingClientRect();

        var popupLeft = markerRect.left - mapRect.left + container.scrollLeft - 120;
        var popupTop = markerRect.top - mapRect.top + container.scrollTop - popupEl.firstChild.offsetHeight - 10;

        if (popupLeft < 8) popupLeft = 8;
        var mapWidth = container.scrollWidth;
        if (popupLeft + 280 > mapWidth - 8) popupLeft = mapWidth - 288;
        if (popupTop < 8) {
            popupTop = markerRect.top - mapRect.top + container.scrollTop + 10;
        }

        popupEl.style.left = popupLeft + 'px';
        popupEl.style.top = popupTop + 'px';
        popupEl.style.display = 'block';
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
