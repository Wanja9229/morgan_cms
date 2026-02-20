<?php
/**
 * Morgan Edition - 탐색 파견 스킨
 * 리스트뷰(팰월드) + 맵뷰(원신) 지원
 */

if (!defined('_GNUBOARD_')) exit;

$expedition_api = G5_BBS_URL . '/expedition_api.php';
$ui_mode = mg_config('expedition_ui_mode', 'list');
$map_image = mg_config('expedition_map_image', '');
$marker_style = mg_config('map_marker_style', 'pin');
$relation_url = G5_BBS_URL . '/relation.php';
?>

<div class="mg-inner" id="expedition-app">
    <!-- 탭 네비게이션 -->
    <div class="flex gap-2 mb-6 border-b border-mg-bg-tertiary pb-3">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="px-4 py-2 text-sm font-medium text-mg-text-secondary hover:text-mg-text-primary rounded-lg transition-colors">시설 건설</a>
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php?view=expedition" class="px-4 py-2 text-sm font-medium text-mg-accent bg-mg-accent/10 rounded-lg">탐색 파견</a>
    </div>

    <!-- 상단: 스태미나 + 슬롯 -->
    <div class="card mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-mg-accent"><?php echo mg_icon('bolt', 'w-6 h-6'); ?></span>
                <div>
                    <div class="text-xs text-mg-text-muted">노동력</div>
                    <div class="font-bold text-mg-accent" id="stamina-display"><?php echo $my_stamina['current']; ?> / <?php echo $my_stamina['max']; ?></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-mg-text-secondary"><?php echo mg_icon('map', 'w-6 h-6'); ?></span>
                <div>
                    <div class="text-xs text-mg-text-muted">파견 슬롯</div>
                    <div class="font-bold text-mg-text-primary" id="slot-display">- / -</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 진행 중인 파견 -->
    <div id="active-section" style="display:none;" class="mb-6">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">진행 중인 파견</h2>
        <div id="active-list" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
    </div>

    <!-- 파견지 선택 영역 -->
    <div class="mb-6" id="area-section">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-mg-text-primary">파견지 선택</h2>
            <div id="slot-full-notice" style="display:none;" class="text-sm text-mg-text-muted">파견 슬롯이 모두 사용 중입니다.</div>
        </div>

        <!-- 리스트뷰 -->
        <div id="area-list-view" style="display:<?php echo ($ui_mode !== 'map' || !$map_image) ? 'block' : 'none'; ?>;">
            <div id="area-list" class="space-y-4">
                <div class="text-sm text-mg-text-muted text-center py-8">불러오는 중...</div>
            </div>
        </div>

        <!-- 맵뷰 -->
        <?php if ($ui_mode === 'map' && $map_image) { ?>
        <div id="area-map-view" style="display:block;">
            <div id="map-container" style="position:relative;overflow:auto;max-height:70vh;border-radius:12px;border:1px solid var(--mg-bg-tertiary);">
                <img src="<?php echo $map_image; ?>" id="map-image" style="display:block;width:100%;min-width:600px;" alt="세계관 맵" draggable="false">
                <div id="map-markers"></div>
                <div id="map-popup" style="display:none;position:absolute;z-index:20;"></div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 나를 파트너로 선택한 기록 -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">나를 파트너로 선택한 파견</h2>
        <div id="partner-history-list">
            <div class="text-sm text-mg-text-muted text-center py-4">불러오는 중...</div>
        </div>
    </div>

    <!-- 파견 이력 -->
    <div class="card">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">최근 파견 이력</h2>
        <div id="history-list">
            <div class="text-sm text-mg-text-muted text-center py-4">불러오는 중...</div>
        </div>
    </div>
</div>

<!-- 파견 모달 (파견지 클릭 시) -->
<div id="dispatch-modal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-mg-bg-secondary rounded-xl w-full" style="max-width:520px;max-height:90vh;overflow-y:auto;">
        <!-- 모달 헤더: 파견지 정보 -->
        <div id="dm-header" style="position:relative;overflow:hidden;border-radius:12px 12px 0 0;">
            <div id="dm-image-wrap" style="height:180px;background:linear-gradient(135deg,#2d3748,#1a202c);position:relative;">
                <img id="dm-area-img" src="" style="width:100%;height:100%;object-fit:cover;display:none;" alt="">
                <div style="position:absolute;inset:0;background:linear-gradient(transparent 40%,rgba(0,0,0,0.7));" id="dm-gradient"></div>
                <div style="position:absolute;bottom:12px;left:16px;right:16px;">
                    <h3 id="dm-area-name" class="text-lg font-bold text-white" style="text-shadow:0 1px 3px rgba(0,0,0,0.5);"></h3>
                    <p id="dm-area-desc" class="text-xs text-gray-300 mt-1" style="text-shadow:0 1px 2px rgba(0,0,0,0.5);"></p>
                </div>
            </div>
            <button onclick="closeDispatchModal()" style="position:absolute;top:8px;right:8px;width:32px;height:32px;border-radius:50%;background:rgba(0,0,0,0.5);color:white;border:none;font-size:18px;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
        </div>

        <!-- 파견지 상세 -->
        <div style="padding:16px;">
            <div class="flex flex-wrap gap-3 text-sm mb-4" id="dm-stats">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg" style="background:var(--mg-bg-primary);"><svg class="w-4 h-4" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> <span id="dm-stamina">0</span></span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg" style="background:var(--mg-bg-primary);"><svg class="w-4 h-4" style="color:var(--mg-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> <span id="dm-duration">0분</span></span>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg" style="background:var(--mg-bg-primary);"><svg class="w-4 h-4" style="color:var(--mg-text-secondary);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> +<span id="dm-partner-pt">0</span>P</span>
            </div>
            <div id="dm-drops" class="flex flex-wrap gap-2 mb-4"></div>

            <!-- Step 1: 캐릭터 선택 -->
            <div class="mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-mg-accent text-mg-bg-primary text-xs font-bold">1</span>
                    <span class="text-sm font-medium text-mg-text-primary">캐릭터 선택</span>
                </div>
                <div id="dm-character-list" class="flex flex-wrap gap-2">
                    <div class="text-sm text-mg-text-muted p-2">불러오는 중...</div>
                </div>
            </div>

            <!-- Step 2: 파트너 선택 -->
            <div id="dm-step-partner" class="mb-4" style="display:none;">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-mg-accent text-mg-bg-primary text-xs font-bold">2</span>
                    <span class="text-sm font-medium text-mg-text-primary">파트너 선택 <span class="text-mg-text-muted font-normal">(선택, +20% 보너스)</span></span>
                </div>
                <div id="dm-partner-list" class="flex flex-wrap gap-2"></div>
            </div>

            <!-- 파견 버튼 -->
            <button id="dm-dispatch-btn" onclick="submitDispatch()" class="w-full px-4 py-3 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors" disabled>캐릭터를 선택해주세요</button>
        </div>
    </div>
</div>

<!-- 보상 수령 모달 -->
<div id="reward-modal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-mg-bg-secondary rounded-xl max-w-sm w-full p-6">
        <div class="text-center mb-4">
            <div class="mb-2"><svg class="w-8 h-8 mx-auto" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg></div>
            <h3 class="text-lg font-bold text-mg-text-primary">파견 완료!</h3>
        </div>
        <div id="reward-items" class="space-y-2 mb-4"></div>
        <button onclick="closeRewardModal()" class="w-full px-4 py-3 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors">확인</button>
    </div>
</div>

<style>
.exp-card { position:relative; border-radius:12px; overflow:hidden; background:var(--mg-bg-secondary); border:1px solid var(--mg-bg-tertiary); transition:border-color 0.2s, transform 0.2s; cursor:pointer; }
.exp-card:hover { border-color:var(--mg-accent); transform:translateY(-2px); }
.exp-card.is-locked { opacity:0.6; cursor:default; }
.exp-card.is-locked:hover { border-color:var(--mg-bg-tertiary); transform:none; }
.exp-card-img { position:relative; width:100%; padding-top:30%; background:linear-gradient(135deg,#2d3748,#1a202c); overflow:hidden; }
.exp-card-img img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.exp-card-img .stamp-badge { position:absolute; top:8px; right:8px; padding:2px 8px; border-radius:6px; background:rgba(0,0,0,0.6); font-size:12px; color:var(--mg-accent); display:flex; align-items:center; gap:4px; backdrop-filter:blur(4px); }
.exp-card-img .lock-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; font-size:2rem; }
.exp-card-body { padding:12px 16px 16px; }
.exp-card-name { font-weight:600; color:var(--mg-text-primary); font-size:1rem; margin-bottom:2px; }
.exp-card-desc { font-size:0.75rem; color:var(--mg-text-muted); display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; margin-bottom:8px; min-height:2.25em; }
.exp-card-meta { display:flex; flex-wrap:wrap; gap:8px; font-size:0.75rem; color:var(--mg-text-secondary); margin-bottom:8px; }
.exp-card-drops { display:flex; flex-wrap:wrap; gap:4px; }
.exp-card-drops .drop-tag { font-size:0.7rem; padding:1px 6px; border-radius:4px; background:var(--mg-bg-primary); color:var(--mg-text-secondary); }
.exp-card-drops .drop-tag.rare { background:rgba(167,139,250,0.15); color:#a78bfa; font-weight:600; }
.map-marker { position:absolute; width:40px; height:40px; margin-left:-20px; margin-top:-40px; cursor:pointer; transition:transform 0.15s; z-index:5; user-select:none; }
.map-marker:hover { transform:scale(1.2); z-index:10; }
.map-marker svg { width:100%; height:100%; filter:drop-shadow(0 2px 4px rgba(0,0,0,0.4)); }
.map-marker.is-locked { opacity:0.4; cursor:default; }
.map-marker.is-locked:hover { transform:none; }
.map-popup { width:280px; background:var(--mg-bg-secondary); border:1px solid var(--mg-bg-tertiary); border-radius:12px; overflow:hidden; box-shadow:0 8px 24px rgba(0,0,0,0.4); }
.map-popup-img { width:100%; height:120px; object-fit:cover; }
.map-popup-body { padding:12px; }
.map-popup-name { font-weight:600; color:var(--mg-text-primary); font-size:0.95rem; }
.map-popup-desc { font-size:0.75rem; color:var(--mg-text-muted); margin-top:2px; display:-webkit-box; -webkit-box-orient:vertical; -webkit-line-clamp:2; overflow:hidden; }
.map-popup-meta { display:flex; gap:8px; font-size:0.75rem; color:var(--mg-text-secondary); margin-top:8px; }
.map-popup-btn { display:block; width:100%; margin-top:10px; padding:8px; border:none; border-radius:8px; background:var(--mg-accent); color:var(--mg-bg-primary); font-weight:500; font-size:0.85rem; cursor:pointer; text-align:center; }
.map-popup-btn:hover { background:var(--mg-accent-hover); }
</style>

<script>
(function() {
    var API = '<?php echo $expedition_api; ?>';
    var UI_MODE = '<?php echo $ui_mode; ?>';
    var MAP_IMAGE = '<?php echo $map_image; ?>';
    var MARKER_STYLE = '<?php echo $marker_style; ?>';
    var RELATION_URL = '<?php echo $relation_url; ?>';

    function getMarkerSVG(style, color, inner) {
        color = color || 'var(--mg-accent)';
        inner = inner || 'var(--mg-bg-primary)';
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
    var selected = { ch_id: 0, partner_ch_id: 0, ea_id: 0 };
    var timerIntervals = [];
    var cachedAreas = null;
    var cachedCharacters = null;

    // === 초기 로드 ===
    loadStatus();
    loadAreas();
    loadPartnerHistory();
    loadHistory();

    // === API 호출 ===
    function api(action, params, method) {
        method = method || 'GET';
        var url = API + '?action=' + action;
        var opts = { method: method, credentials: 'same-origin' };

        if (method === 'POST') {
            var fd = new FormData();
            fd.append('action', action);
            if (params) Object.keys(params).forEach(function(k) { fd.append(k, params[k]); });
            opts.body = fd;
        } else {
            if (params) Object.keys(params).forEach(function(k) { url += '&' + k + '=' + encodeURIComponent(params[k]); });
        }

        return fetch(url, opts).then(function(r) { return r.json(); });
    }

    // === 상태 로드 ===
    function loadStatus() {
        api('status').then(function(data) {
            if (!data.success) return;
            document.getElementById('stamina-display').textContent = data.stamina.current + ' / ' + data.stamina.max;
            document.getElementById('slot-display').textContent = data.used_slots + ' / ' + data.max_slots;

            renderActive(data.active);

            var notice = document.getElementById('slot-full-notice');
            if (data.used_slots >= data.max_slots) {
                notice.style.display = 'block';
            } else {
                notice.style.display = 'none';
            }
        });
    }

    // === 진행 중 파견 렌더 ===
    function renderActive(list) {
        var section = document.getElementById('active-section');
        var container = document.getElementById('active-list');

        timerIntervals.forEach(clearInterval);
        timerIntervals = [];

        if (!list || list.length === 0) {
            section.style.display = 'none';
            return;
        }
        section.style.display = 'block';
        container.innerHTML = '';

        list.forEach(function(exp) {
            var card = document.createElement('div');
            card.className = 'card border-2 ' + (exp.is_complete ? 'border-mg-accent' : 'border-mg-bg-tertiary');

            var partnerHtml = '';
            if (exp.partner_ch_name) {
                partnerHtml = '<div class="text-xs text-mg-text-muted mt-1">파트너: ' + escHtml(exp.partner_ch_name) +
                    ' (' + escHtml(exp.partner_nick || '') + ')</div>';
            }

            var actionHtml = '';
            if (exp.is_complete) {
                actionHtml = '<button class="w-full px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors" onclick="claimExpedition(' + exp.el_id + ')">보상 수령</button>';
            } else {
                actionHtml = '<div class="mb-2"><div class="flex justify-between text-xs text-mg-text-muted mb-1"><span>진행 중</span><span id="timer-' + exp.el_id + '">' + formatTime(exp.remaining_seconds) + '</span></div>' +
                    '<div class="h-2 bg-mg-bg-primary rounded-full overflow-hidden"><div class="h-full bg-mg-accent transition-all" id="bar-' + exp.el_id + '" style="width:' + exp.progress + '%"></div></div></div>' +
                    '<button class="w-full px-3 py-1.5 text-sm border border-mg-bg-tertiary text-mg-text-secondary rounded-lg hover:bg-mg-bg-tertiary transition-colors" onclick="cancelExpedition(' + exp.el_id + ')">취소</button>';

                (function(id, remaining, total) {
                    var iv = setInterval(function() {
                        remaining--;
                        if (remaining <= 0) {
                            clearInterval(iv);
                            loadStatus();
                            return;
                        }
                        var tEl = document.getElementById('timer-' + id);
                        var bEl = document.getElementById('bar-' + id);
                        if (tEl) tEl.textContent = formatTime(remaining);
                        if (bEl) bEl.style.width = Math.min(100, ((total - remaining) / total) * 100) + '%';
                    }, 1000);
                    timerIntervals.push(iv);
                })(exp.el_id, exp.remaining_seconds, exp.total_seconds);
            }

            card.innerHTML =
                '<div class="flex items-center gap-3 mb-3">' +
                    '<div class="text-2xl">' + (exp.ea_icon ? '' : '<svg class="w-6 h-6" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>') + '</div>' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="font-semibold text-mg-text-primary truncate">' + escHtml(exp.ea_name || '파견지') + '</div>' +
                        '<div class="text-xs text-mg-text-muted">' + escHtml(exp.ch_name || '') + '</div>' +
                        partnerHtml +
                    '</div>' +
                '</div>' + actionHtml;

            container.appendChild(card);
        });
    }

    // === 파견지 목록 (리스트뷰 + 맵뷰) ===
    function loadAreas() {
        api('areas').then(function(data) {
            if (!data.success) return;
            cachedAreas = data.areas || [];

            if (UI_MODE === 'map' && MAP_IMAGE) {
                renderMapView(cachedAreas);
            } else {
                renderListView(cachedAreas);
            }
        });
    }

    // === 리스트뷰 렌더 ===
    function renderListView(areas) {
        var container = document.getElementById('area-list');
        container.innerHTML = '';

        if (!areas || areas.length === 0) {
            container.innerHTML = '<div class="text-sm text-mg-text-muted text-center py-8">등록된 파견지가 없습니다.</div>';
            return;
        }

        areas.forEach(function(area) {
            var locked = !area.is_unlocked;
            var card = document.createElement('div');
            card.className = 'exp-card' + (locked ? ' is-locked' : '');

            var imgHtml = '<div class="exp-card-img">';
            if (area.ea_image) {
                imgHtml += '<img src="' + escHtml(area.ea_image) + '" alt="' + escHtml(area.ea_name) + '" loading="lazy">';
            }
            imgHtml += '<div class="stamp-badge"><svg style="display:inline-block;width:12px;height:12px;vertical-align:middle;color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> ' + area.ea_stamina_cost + '</div>';
            if (locked) {
                imgHtml += '<div class="lock-overlay"><svg style="display:inline-block;width:24px;height:24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></div>';
            }
            imgHtml += '</div>';

            var durH = Math.floor(area.ea_duration / 60);
            var durM = area.ea_duration % 60;
            var durText = (durH > 0 ? durH + '시간 ' : '') + (durM > 0 ? durM + '분' : '');

            var dropsHtml = '';
            if (area.drops && area.drops.length > 0) {
                area.drops.forEach(function(d) {
                    var cls = d.ed_is_rare == 1 ? 'drop-tag rare' : 'drop-tag';
                    dropsHtml += '<span class="' + cls + '">' + escHtml(d.mt_name) + ' ' + d.ed_chance + '%' + (d.ed_is_rare == 1 ? ' RARE' : '') + '</span>';
                });
            }

            var bodyHtml = '<div class="exp-card-body">' +
                '<div class="exp-card-name">' + escHtml(area.ea_name) + '</div>' +
                '<div class="exp-card-desc">' + escHtml(area.ea_desc || '') + '</div>' +
                '<div class="exp-card-meta">' +
                    '<span>' + durText.trim() + '</span>' +
                    '<span>+' + area.ea_partner_point + 'P</span>' +
                '</div>' +
                (dropsHtml ? '<div class="exp-card-drops">' + dropsHtml + '</div>' : '') +
                (locked ? '<div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:6px;">' + escHtml(area.unlock_facility_name || '시설') + ' 건설 필요</div>' : '') +
                '</div>';

            card.innerHTML = imgHtml + bodyHtml;

            if (!locked) {
                card.onclick = function() { openDispatchModal(area); };
            }

            container.appendChild(card);
        });
    }

    // === 맵뷰 렌더 ===
    function renderMapView(areas) {
        var markersEl = document.getElementById('map-markers');
        if (!markersEl) return;
        markersEl.innerHTML = '';

        var popupEl = document.getElementById('map-popup');

        areas.forEach(function(area) {
            if (area.ea_map_x == null || area.ea_map_y == null) return;
            var locked = !area.is_unlocked;

            var marker = document.createElement('div');
            marker.className = 'map-marker' + (locked ? ' is-locked' : '');
            marker.style.left = area.ea_map_x + '%';
            marker.style.top = area.ea_map_y + '%';
            marker.title = area.ea_name;

            var pinColor = locked ? '#6b7280' : 'var(--mg-accent)';
            var pinInner = locked ? '#4b5563' : 'var(--mg-bg-primary)';
            marker.innerHTML = getMarkerSVG(MARKER_STYLE, pinColor, pinInner);

            if (!locked) {
                marker.onclick = function(e) {
                    e.stopPropagation();
                    showMapPopup(area, marker, popupEl);
                };
            }

            markersEl.appendChild(marker);
        });

        // 맵 클릭 시 팝업 닫기
        var mapContainer = document.getElementById('map-container');
        mapContainer.addEventListener('click', function(e) {
            if (!e.target.closest('.map-marker') && !e.target.closest('.map-popup')) {
                popupEl.style.display = 'none';
            }
        });
    }

    function showMapPopup(area, markerEl, popupEl) {
        var durH = Math.floor(area.ea_duration / 60);
        var durM = area.ea_duration % 60;
        var durText = (durH > 0 ? durH + '시간 ' : '') + (durM > 0 ? durM + '분' : '');

        var imgHtml = area.ea_image
            ? '<img class="map-popup-img" src="' + escHtml(area.ea_image) + '" alt="' + escHtml(area.ea_name) + '">'
            : '';

        popupEl.innerHTML = '<div class="map-popup">' +
            imgHtml +
            '<div class="map-popup-body">' +
                '<div class="map-popup-name">' + escHtml(area.ea_name) + '</div>' +
                (area.ea_desc ? '<div class="map-popup-desc">' + escHtml(area.ea_desc) + '</div>' : '') +
                '<div class="map-popup-meta">' +
                    '<span>' + area.ea_stamina_cost + '</span>' +
                    '<span>' + durText.trim() + '</span>' +
                    '<span>+' + area.ea_partner_point + 'P</span>' +
                '</div>' +
                '<button class="map-popup-btn" onclick="openDispatchModalById(' + area.ea_id + ')">파견 보내기</button>' +
            '</div></div>';

        // 마커 위에 팝업 위치
        var mapContainer = document.getElementById('map-container');
        var mapRect = mapContainer.getBoundingClientRect();
        var markerRect = markerEl.getBoundingClientRect();

        var popupLeft = markerRect.left - mapRect.left + mapContainer.scrollLeft - 120;
        var popupTop = markerRect.top - mapRect.top + mapContainer.scrollTop - popupEl.firstChild.offsetHeight - 10;

        // 좌우 경계 보정
        if (popupLeft < 8) popupLeft = 8;
        var mapWidth = mapContainer.scrollWidth;
        if (popupLeft + 280 > mapWidth - 8) popupLeft = mapWidth - 288;

        // 위로 공간이 부족하면 아래에 표시
        if (popupTop < 8) {
            popupTop = markerRect.top - mapRect.top + mapContainer.scrollTop + 10;
        }

        popupEl.style.left = popupLeft + 'px';
        popupEl.style.top = popupTop + 'px';
        popupEl.style.display = 'block';
    }

    window.openDispatchModalById = function(ea_id) {
        if (!cachedAreas) return;
        var area = cachedAreas.find(function(a) { return a.ea_id == ea_id; });
        if (area) openDispatchModal(area);
    };

    // === 디스패치 모달 열기 ===
    function openDispatchModal(area) {
        selected.ea_id = area.ea_id;
        selected.ch_id = 0;
        selected.partner_ch_id = 0;

        // 헤더 정보 채우기
        var imgEl = document.getElementById('dm-area-img');
        if (area.ea_image) {
            imgEl.src = area.ea_image;
            imgEl.style.display = 'block';
        } else {
            imgEl.style.display = 'none';
        }
        document.getElementById('dm-area-name').textContent = area.ea_name;
        document.getElementById('dm-area-desc').textContent = area.ea_desc || '';

        document.getElementById('dm-stamina').textContent = area.ea_stamina_cost;

        var durH = Math.floor(area.ea_duration / 60);
        var durM = area.ea_duration % 60;
        var durText = (durH > 0 ? durH + '시간 ' : '') + (durM > 0 ? durM + '분' : '');
        document.getElementById('dm-duration').textContent = durText.trim();
        document.getElementById('dm-partner-pt').textContent = area.ea_partner_point;

        // 드롭 테이블
        var dropsEl = document.getElementById('dm-drops');
        dropsEl.innerHTML = '';
        if (area.drops && area.drops.length > 0) {
            area.drops.forEach(function(d) {
                var cls = d.ed_is_rare == 1 ? 'drop-tag rare' : 'drop-tag';
                dropsEl.innerHTML += '<span class="' + cls + '">' + escHtml(d.mt_name) + ' ' + d.ed_min + '~' + d.ed_max + '개 (' + d.ed_chance + '%)' + (d.ed_is_rare == 1 ? ' RARE' : '') + '</span>';
            });
        }

        // 버튼 초기화
        var btn = document.getElementById('dm-dispatch-btn');
        btn.disabled = true;
        btn.textContent = '캐릭터를 선택해주세요';

        // 파트너 섹션 숨기기
        document.getElementById('dm-step-partner').style.display = 'none';
        document.getElementById('dm-partner-list').innerHTML = '';

        // 캐릭터 로드
        loadModalCharacters();

        // 맵 팝업 닫기
        var mapPopup = document.getElementById('map-popup');
        if (mapPopup) mapPopup.style.display = 'none';

        document.getElementById('dispatch-modal').style.display = 'flex';
    }

    window.closeDispatchModal = function() {
        document.getElementById('dispatch-modal').style.display = 'none';
    };

    document.getElementById('dispatch-modal').addEventListener('click', function(e) {
        if (e.target === this) closeDispatchModal();
    });

    // === 모달 내 캐릭터 로드 ===
    function loadModalCharacters() {
        var container = document.getElementById('dm-character-list');

        if (cachedCharacters) {
            renderModalCharacters(cachedCharacters, container);
            return;
        }

        api('my_characters').then(function(data) {
            if (!data.success || !data.characters) {
                container.innerHTML = '<div class="text-sm text-mg-text-muted p-2">사용 가능한 캐릭터가 없습니다.</div>';
                return;
            }
            cachedCharacters = data.characters;
            renderModalCharacters(cachedCharacters, container);
        });
    }

    function renderModalCharacters(characters, container) {
        container.innerHTML = '';
        if (!characters || characters.length === 0) {
            container.innerHTML = '<div class="text-sm text-mg-text-muted p-2">사용 가능한 캐릭터가 없습니다.</div>';
            return;
        }

        characters.forEach(function(ch) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'flex items-center gap-2 px-3 py-2 bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg hover:border-mg-accent transition-colors text-left';
            btn.setAttribute('data-ch-id', ch.ch_id);
            btn.innerHTML =
                (ch.ch_thumb ? '<img src="' + escHtml(ch.ch_thumb) + '" class="w-8 h-8 rounded-full object-cover">' : '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>') +
                '<span class="text-sm text-mg-text-primary">' + escHtml(ch.ch_name) + '</span>';
            btn.onclick = function() { selectModalCharacter(ch.ch_id, this); };
            container.appendChild(btn);
        });
    }

    function selectModalCharacter(ch_id, el) {
        selected.ch_id = ch_id;
        selected.partner_ch_id = 0;

        // UI 선택 표시
        document.querySelectorAll('#dm-character-list button').forEach(function(b) {
            b.classList.remove('border-mg-accent', 'ring-1', 'ring-mg-accent');
            b.classList.add('border-mg-bg-tertiary');
        });
        el.classList.remove('border-mg-bg-tertiary');
        el.classList.add('border-mg-accent', 'ring-1', 'ring-mg-accent');

        // 버튼 활성화
        var btn = document.getElementById('dm-dispatch-btn');
        btn.disabled = false;
        btn.textContent = '파견 보내기';

        // 파트너 로드
        loadModalPartners(ch_id);
    }

    // === 모달 내 파트너 로드 ===
    function loadModalPartners(ch_id) {
        var section = document.getElementById('dm-step-partner');
        var container = document.getElementById('dm-partner-list');

        api('partner_candidates', { ch_id: ch_id }).then(function(data) {
            if (!data.success || !data.candidates || data.candidates.length === 0) {
                section.style.display = 'block';
                container.innerHTML = '<div class="text-sm text-mg-text-muted p-2">관계가 맺어진 캐릭터가 없습니다. <a href="' + RELATION_URL + '" class="text-mg-accent hover:underline">관계 맺기</a></div>';
                return;
            }

            section.style.display = 'block';
            container.innerHTML = '';

            // 혼자 보내기 버튼
            var skipBtn = document.createElement('button');
            skipBtn.type = 'button';
            skipBtn.className = 'flex items-center gap-2 px-3 py-2 bg-mg-bg-primary border border-mg-accent ring-1 ring-mg-accent rounded-lg text-left';
            skipBtn.innerHTML = '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">-</div><span class="text-sm text-mg-text-primary">혼자 보내기</span>';
            skipBtn.onclick = function() { selectModalPartner(0, this); };
            container.appendChild(skipBtn);

            data.candidates.forEach(function(p) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'flex items-center gap-2 px-3 py-2 bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg hover:border-mg-accent transition-colors text-left';
                btn.innerHTML =
                    (p.ch_thumb ? '<img src="' + escHtml(p.ch_thumb) + '" class="w-8 h-8 rounded-full object-cover">' : '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>') +
                    '<div><div class="text-sm text-mg-text-primary">' + escHtml(p.ch_name) + '</div>' +
                    '<div class="text-xs text-mg-text-muted">' + escHtml(p.relation_label || '') + '</div></div>';
                btn.onclick = function() { selectModalPartner(p.ch_id, this); };
                container.appendChild(btn);
            });
        });
    }

    function selectModalPartner(ch_id, el) {
        selected.partner_ch_id = ch_id;

        document.querySelectorAll('#dm-partner-list button').forEach(function(b) {
            b.classList.remove('border-mg-accent', 'ring-1', 'ring-mg-accent');
            b.classList.add('border-mg-bg-tertiary');
        });
        el.classList.remove('border-mg-bg-tertiary');
        el.classList.add('border-mg-accent', 'ring-1', 'ring-mg-accent');
    }

    // === 파견 실행 ===
    window.submitDispatch = function() {
        if (!selected.ch_id || !selected.ea_id) return;

        var area = cachedAreas ? cachedAreas.find(function(a) { return a.ea_id == selected.ea_id; }) : null;
        var areaName = area ? area.ea_name : '파견지';
        var cost = area ? area.ea_stamina_cost : 0;

        if (!confirm(areaName + ' 파견을 보내시겠습니까?\n(노동력 ' + cost + ' 소모)')) return;

        api('start', {
            ch_id: selected.ch_id,
            ea_id: selected.ea_id,
            partner_ch_id: selected.partner_ch_id || ''
        }, 'POST').then(function(data) {
            if (data.success) {
                closeDispatchModal();
                selected.ch_id = 0;
                selected.partner_ch_id = 0;
                selected.ea_id = 0;
                cachedCharacters = null;
                loadStatus();
                loadAreas();
                loadHistory();
            }
            alert(data.message);
        });
    };

    // === 보상 수령 ===
    window.claimExpedition = function(el_id) {
        api('claim', { el_id: el_id }, 'POST').then(function(data) {
            if (data.success) {
                showRewardModal(data.rewards);
                loadStatus();
                loadHistory();
            } else {
                alert(data.message);
            }
        });
    };

    // === 파견 취소 ===
    window.cancelExpedition = function(el_id) {
        if (!confirm('파견을 취소하시겠습니까?\n노동력은 반환되지 않습니다.')) return;

        api('cancel', { el_id: el_id }, 'POST').then(function(data) {
            alert(data.message);
            if (data.success) {
                loadStatus();
                loadHistory();
            }
        });
    };

    // === 보상 모달 ===
    function showRewardModal(rewards) {
        var container = document.getElementById('reward-items');
        container.innerHTML = '';

        if (rewards && rewards.items && rewards.items.length > 0) {
            rewards.items.forEach(function(item) {
                var cls = item.is_rare ? 'border-purple-500 bg-purple-500/10' : 'border-mg-bg-tertiary bg-mg-bg-primary';
                var nameClass = item.is_rare ? 'text-purple-400 font-semibold' : 'text-mg-text-primary';
                container.innerHTML +=
                    '<div class="flex items-center justify-between p-3 rounded-lg border ' + cls + '">' +
                        '<span class="' + nameClass + '">' + escHtml(item.mt_name) + (item.is_rare ? ' RARE' : '') + '</span>' +
                        '<span class="font-bold text-mg-text-primary">x' + item.amount + '</span>' +
                    '</div>';
            });
        } else {
            container.innerHTML = '<div class="text-center text-mg-text-muted py-2">획득한 재료가 없습니다.</div>';
        }

        document.getElementById('reward-modal').style.display = 'flex';
    }

    window.closeRewardModal = function() {
        document.getElementById('reward-modal').style.display = 'none';
    };

    document.getElementById('reward-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRewardModal();
    });

    // === 파트너 이력 ===
    function loadPartnerHistory() {
        api('partner_history', { limit: 10 }).then(function(data) {
            var container = document.getElementById('partner-history-list');
            if (!data.success || !data.partner_history || data.partner_history.length === 0) {
                container.innerHTML = '<div class="text-sm text-mg-text-muted text-center py-4">아직 나를 파트너로 선택한 파견이 없습니다.</div>';
                return;
            }

            var html = '<div class="space-y-2">';
            data.partner_history.forEach(function(h) {
                var statusBadge = '';
                if (h.el_status === 'active') statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-accent/20 text-mg-accent">진행중</span>';
                else if (h.el_status === 'complete') statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">미수령</span>';
                else if (h.el_status === 'claimed') statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-success/20 text-mg-success">완료</span>';
                else if (h.el_status === 'cancelled') statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-muted">취소</span>';

                var dateText = (h.el_start || '').substring(5, 16);

                html += '<div class="flex items-center gap-3 p-2 bg-mg-bg-primary rounded-lg text-sm">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2">' +
                            '<span class="text-mg-text-primary font-medium">' + escHtml(h.mb_nick || h.mb_id) + '</span>' +
                            '<span class="text-mg-text-muted">→</span>' +
                            '<span class="text-mg-accent">' + escHtml(h.ea_name || '') + '</span>' +
                            statusBadge +
                        '</div>' +
                        '<div class="text-xs text-mg-text-muted mt-0.5">' +
                            escHtml(h.ch_name || '') + ' + ' + escHtml(h.my_ch_name || '내 캐릭터') +
                            ' · ' + dateText +
                            (h.ea_partner_point ? ' · +' + h.ea_partner_point + 'P' : '') +
                        '</div>' +
                    '</div></div>';
            });
            html += '</div>';
            container.innerHTML = html;
        });
    }

    // === 이력 ===
    function loadHistory() {
        api('history', { limit: 10 }).then(function(data) {
            var container = document.getElementById('history-list');
            if (!data.success || !data.history || data.history.length === 0) {
                container.innerHTML = '<div class="text-sm text-mg-text-muted text-center py-4">파견 이력이 없습니다.</div>';
                return;
            }

            var html = '<div class="space-y-2">';
            data.history.forEach(function(h) {
                var statusBadge = '';
                if (h.el_status === 'claimed') {
                    statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-success/20 text-mg-success">수령완료</span>';
                } else if (h.el_status === 'cancelled') {
                    statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-muted">취소</span>';
                }

                var rewardsText = '';
                if (h.el_rewards_parsed && h.el_rewards_parsed.items && h.el_rewards_parsed.items.length > 0) {
                    var parts = [];
                    h.el_rewards_parsed.items.forEach(function(item) {
                        parts.push(item.mt_name + ' x' + item.amount + (item.is_rare ? 'RARE' : ''));
                    });
                    rewardsText = parts.join(', ');
                } else if (h.el_status === 'claimed') {
                    rewardsText = '(드롭 없음)';
                }

                var dateText = (h.el_start || '').substring(5, 16);

                html += '<div class="flex items-center gap-3 p-2 bg-mg-bg-primary rounded-lg text-sm">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><span class="text-mg-text-primary font-medium">' + escHtml(h.ea_name || '') + '</span>' + statusBadge + '</div>' +
                        '<div class="text-xs text-mg-text-muted mt-0.5">' + escHtml(h.ch_name || '') +
                        (h.partner_ch_name ? ' + ' + escHtml(h.partner_ch_name) : '') +
                        ' · ' + dateText + '</div>' +
                        (rewardsText ? '<div class="text-xs text-mg-text-secondary mt-0.5">' + escHtml(rewardsText) + '</div>' : '') +
                    '</div></div>';
            });
            html += '</div>';
            container.innerHTML = html;
        });
    }

    // === 유틸 ===
    function formatTime(seconds) {
        if (seconds <= 0) return '완료';
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        if (h > 0) return h + '시간 ' + (m < 10 ? '0' : '') + m + '분';
        return m + '분 ' + (s < 10 ? '0' : '') + s + '초';
    }

    function escHtml(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
})();
</script>
