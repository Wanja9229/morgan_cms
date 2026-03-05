<?php
/**
 * Morgan Edition - 라디오 관리
 */
$sub_menu = '801400';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'config';
if (!in_array($tab, array('config','playlist','ments'))) $tab = 'config';

$token = get_admin_token();

// 설정 로드
$cfg = sql_fetch("SELECT * FROM {$g5['mg_radio_config_table']} WHERE config_id = 1");

// 플레이리스트
$tracks = array();
$result = sql_query("SELECT * FROM {$g5['mg_radio_playlist_table']} ORDER BY sort_order ASC, track_id ASC");
if ($result) while ($row = sql_fetch_array($result)) $tracks[] = $row;

// 멘트
$ments = array();
$result = sql_query("SELECT * FROM {$g5['mg_radio_ments_table']} ORDER BY sort_order ASC, ment_id ASC");
if ($result) while ($row = sql_fetch_array($result)) $ments[] = $row;

// 신청 대기
$pending_songs = array();
$result = sql_query("SELECT r.*, m.mb_nick FROM {$g5['mg_radio_requests_table']} r
                     LEFT JOIN {$g5['member_table']} m ON r.mb_id = m.mb_id
                     WHERE r.rr_type = 'song' AND r.rr_status = 'pending'
                     ORDER BY r.rr_created_at ASC");
if ($result) while ($row = sql_fetch_array($result)) $pending_songs[] = $row;

$pending_ments = array();
$result = sql_query("SELECT r.*, m.mb_nick FROM {$g5['mg_radio_requests_table']} r
                     LEFT JOIN {$g5['member_table']} m ON r.mb_id = m.mb_id
                     WHERE r.rr_type = 'ment' AND r.rr_status = 'pending'
                     ORDER BY r.rr_created_at ASC");
if ($result) while ($row = sql_fetch_array($result)) $pending_ments[] = $row;

// OpenWeatherMap 국가별 수도 목록
$weather_cities = array(
    '동아시아' => array(
        'Seoul,KR' => '서울 (한국)',
        'Tokyo,JP' => '도쿄 (일본)',
        'Beijing,CN' => '베이징 (중국)',
        'Taipei,TW' => '타이베이 (대만)',
        'Ulaanbaatar,MN' => '울란바토르 (몽골)',
    ),
    '동남아시아' => array(
        'Bangkok,TH' => '방콕 (태국)',
        'Hanoi,VN' => '하노이 (베트남)',
        'Singapore,SG' => '싱가포르',
        'Jakarta,ID' => '자카르타 (인도네시아)',
        'Manila,PH' => '마닐라 (필리핀)',
        'Kuala Lumpur,MY' => '쿠알라룸푸르 (말레이시아)',
        'Phnom Penh,KH' => '프놈펜 (캄보디아)',
        'Naypyidaw,MM' => '네피도 (미얀마)',
        'Vientiane,LA' => '비엔티안 (라오스)',
    ),
    '남아시아 / 중동' => array(
        'New Delhi,IN' => '뉴델리 (인도)',
        'Kathmandu,NP' => '카트만두 (네팔)',
        'Dhaka,BD' => '다카 (방글라데시)',
        'Colombo,LK' => '콜롬보 (스리랑카)',
        'Ankara,TR' => '앙카라 (튀르키예)',
        'Tehran,IR' => '테헤란 (이란)',
        'Riyadh,SA' => '리야드 (사우디)',
        'Abu Dhabi,AE' => '아부다비 (UAE)',
        'Doha,QA' => '도하 (카타르)',
        'Tel Aviv,IL' => '텔아비브 (이스라엘)',
    ),
    '유럽' => array(
        'London,GB' => '런던 (영국)',
        'Paris,FR' => '파리 (프랑스)',
        'Berlin,DE' => '베를린 (독일)',
        'Rome,IT' => '로마 (이탈리아)',
        'Madrid,ES' => '마드리드 (스페인)',
        'Amsterdam,NL' => '암스테르담 (네덜란드)',
        'Brussels,BE' => '브뤼셀 (벨기에)',
        'Vienna,AT' => '비엔나 (오스트리아)',
        'Bern,CH' => '베른 (스위스)',
        'Stockholm,SE' => '스톡홀름 (스웨덴)',
        'Oslo,NO' => '오슬로 (노르웨이)',
        'Copenhagen,DK' => '코펜하겐 (덴마크)',
        'Helsinki,FI' => '헬싱키 (핀란드)',
        'Warsaw,PL' => '바르샤바 (폴란드)',
        'Prague,CZ' => '프라하 (체코)',
        'Budapest,HU' => '부다페스트 (헝가리)',
        'Athens,GR' => '아테네 (그리스)',
        'Lisbon,PT' => '리스본 (포르투갈)',
        'Dublin,IE' => '더블린 (아일랜드)',
        'Moscow,RU' => '모스크바 (러시아)',
    ),
    '아메리카' => array(
        'Washington,US' => '워싱턴 (미국)',
        'Ottawa,CA' => '오타와 (캐나다)',
        'Mexico City,MX' => '멕시코시티 (멕시코)',
        'Brasilia,BR' => '브라질리아 (브라질)',
        'Buenos Aires,AR' => '부에노스아이레스 (아르헨티나)',
        'Santiago,CL' => '산티아고 (칠레)',
        'Lima,PE' => '리마 (페루)',
        'Bogota,CO' => '보고타 (콜롬비아)',
    ),
    '오세아니아 / 아프리카' => array(
        'Canberra,AU' => '캔버라 (호주)',
        'Wellington,NZ' => '웰링턴 (뉴질랜드)',
        'Cairo,EG' => '카이로 (이집트)',
        'Nairobi,KE' => '나이로비 (케냐)',
        'Pretoria,ZA' => '프리토리아 (남아공)',
        'Rabat,MA' => '라바트 (모로코)',
    ),
);

$weather_types = array(
    'sunny' => '맑음 ☀️',
    'partly_cloudy' => '구름조금 ⛅',
    'cloudy' => '흐림 ☁️',
    'rain' => '비 🌧️',
    'shower' => '소나기 🌦️',
    'snow' => '눈 ❄️',
    'fog' => '안개 🌫️',
    'thunderstorm' => '천둥번개 ⛈️',
);

$g5['title'] = '라디오 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 탭 바 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=config" class="mg-tab <?php echo $tab == 'config' ? 'active' : ''; ?>">설정</a>
    <a href="?tab=playlist" class="mg-tab <?php echo $tab == 'playlist' ? 'active' : ''; ?>">플레이리스트 (<?php echo count($tracks); ?>)<?php if (count($pending_songs)) { ?> <span style="background:var(--mg-error);color:#fff;font-size:0.7rem;padding:1px 6px;border-radius:9px;margin-left:4px;"><?php echo count($pending_songs); ?></span><?php } ?></a>
    <a href="?tab=ments" class="mg-tab <?php echo $tab == 'ments' ? 'active' : ''; ?>">멘트 (<?php echo count($ments); ?>)<?php if (count($pending_ments)) { ?> <span style="background:var(--mg-error);color:#fff;font-size:0.7rem;padding:1px 6px;border-radius:9px;margin-left:4px;"><?php echo count($pending_ments); ?></span><?php } ?></a>
</div>

<?php if ($tab == 'config') { ?>
<!-- ====== 설정 탭 ====== -->
<form method="post" action="./radio_update.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="action" value="save_config">

    <div class="mg-card">
        <div class="mg-card-header">기본 설정</div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label">라디오 위젯 사용</label>
                <label class="mg-switch">
                    <input type="checkbox" name="is_active" value="1" <?php echo $cfg['is_active'] ? 'checked' : ''; ?>>
                    <span class="mg-switch-slider"></span>
                </label>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">재생 모드</label>
                <select name="play_mode" class="mg-form-select">
                    <option value="sequential" <?php echo $cfg['play_mode'] == 'sequential' ? 'selected' : ''; ?>>순차 재생</option>
                    <option value="random" <?php echo $cfg['play_mode'] == 'random' ? 'selected' : ''; ?>>랜덤 재생</option>
                </select>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-top:1rem;">
        <div class="mg-card-header">날씨 설정</div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label">날씨 모드</label>
                <select name="weather_mode" class="mg-form-select" onchange="toggleWeatherMode(this.value)">
                    <option value="manual" <?php echo $cfg['weather_mode'] == 'manual' ? 'selected' : ''; ?>>수동 설정</option>
                    <option value="api" <?php echo $cfg['weather_mode'] == 'api' ? 'selected' : ''; ?>>API 자동 (OpenWeatherMap)</option>
                </select>
            </div>

            <div id="weather-manual" style="<?php echo $cfg['weather_mode'] != 'manual' ? 'display:none' : ''; ?>">
                <div class="mg-form-group">
                    <label class="mg-form-label">기온 (°C)</label>
                    <input type="number" name="manual_temp" class="mg-form-input" value="<?php echo (int)$cfg['manual_temp']; ?>" style="width:120px;">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">날씨 상태</label>
                    <select name="manual_weather" class="mg-form-select">
                        <?php foreach ($weather_types as $wk => $wv) { ?>
                        <option value="<?php echo $wk; ?>" <?php echo $cfg['manual_weather'] == $wk ? 'selected' : ''; ?>><?php echo $wv; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div id="weather-api" style="<?php echo $cfg['weather_mode'] != 'api' ? 'display:none' : ''; ?>">
                <div class="mg-form-group">
                    <label class="mg-form-label">도시 선택</label>
                    <div style="position:relative;" id="city-select-wrap">
                        <input type="text" id="city-search" class="mg-form-input" placeholder="도시 검색..." autocomplete="off" style="margin-bottom:0;">
                        <input type="hidden" name="weather_city" id="weather-city-val" value="<?php echo htmlspecialchars($cfg['weather_city'] ?? 'Seoul'); ?>">
                        <div id="city-dropdown" style="display:none;position:absolute;z-index:100;left:0;right:0;top:100%;max-height:240px;overflow-y:auto;background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);border-radius:0 0 6px 6px;box-shadow:0 4px 12px rgba(0,0,0,0.3);">
                            <?php foreach ($weather_cities as $region => $cities) { ?>
                            <div class="city-group" data-region="<?php echo $region; ?>">
                                <div style="padding:4px 10px;font-size:0.7rem;color:var(--mg-text-muted);background:var(--mg-bg-tertiary);font-weight:600;"><?php echo $region; ?></div>
                                <?php foreach ($cities as $eng => $kor) { ?>
                                <div class="city-option" data-value="<?php echo $eng; ?>" data-search="<?php echo $kor . ' ' . $eng; ?>" style="padding:6px 12px;cursor:pointer;font-size:0.85rem;color:var(--mg-text-primary);" onmouseover="this.style.background='var(--mg-bg-tertiary)'" onmouseout="this.style.background=''"><?php echo $kor; ?> <span style="color:var(--mg-text-muted);font-size:0.75rem;">(<?php echo $eng; ?>)</span></div>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <p class="mg-form-help" id="city-current">현재: <?php
                        $cur_city = $cfg['weather_city'] ?? 'Seoul';
                        $cur_label = $cur_city;
                        foreach ($weather_cities as $cities) {
                            if (isset($cities[$cur_city])) { $cur_label = $cities[$cur_city] . ' (' . $cur_city . ')'; break; }
                        }
                        echo htmlspecialchars($cur_label);
                    ?></p>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">API 키</label>
                    <input type="text" name="weather_api_key" class="mg-form-input" value="<?php echo htmlspecialchars($cfg['weather_api_key'] ?? ''); ?>" placeholder="OpenWeatherMap API Key">
                    <p class="mg-form-help"><a href="https://openweathermap.org/api" target="_blank" rel="noopener" style="color:var(--mg-accent);">openweathermap.org</a>에서 무료 API 키를 발급받으세요.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-top:1rem;">
        <div class="mg-card-header">멘트 설정</div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label">멘트 모드</label>
                <select name="ment_mode" class="mg-form-select">
                    <option value="sequential" <?php echo $cfg['ment_mode'] == 'sequential' ? 'selected' : ''; ?>>순차</option>
                    <option value="random" <?php echo $cfg['ment_mode'] == 'random' ? 'selected' : ''; ?>>랜덤</option>
                </select>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">교체 간격 (초)</label>
                <input type="number" name="ment_interval" class="mg-form-input" value="<?php echo (int)$cfg['ment_interval']; ?>" min="3" max="120" style="width:120px;">
            </div>
        </div>
    </div>

    <div style="margin-top:1rem;">
        <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
    </div>
</form>

<script>
function toggleWeatherMode(mode) {
    document.getElementById('weather-manual').style.display = mode === 'manual' ? '' : 'none';
    document.getElementById('weather-api').style.display = mode === 'api' ? '' : 'none';
}

// 도시 검색 드롭다운
(function(){
    var search = document.getElementById('city-search');
    var dropdown = document.getElementById('city-dropdown');
    var hiddenVal = document.getElementById('weather-city-val');
    var currentLabel = document.getElementById('city-current');
    if (!search || !dropdown) return;

    search.addEventListener('focus', function(){ dropdown.style.display = ''; });
    document.addEventListener('click', function(e){
        if (!document.getElementById('city-select-wrap').contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    search.addEventListener('input', function(){
        var q = this.value.toLowerCase();
        var groups = dropdown.querySelectorAll('.city-group');
        groups.forEach(function(g){
            var opts = g.querySelectorAll('.city-option');
            var anyVisible = false;
            opts.forEach(function(o){
                var match = !q || o.getAttribute('data-search').toLowerCase().indexOf(q) !== -1;
                o.style.display = match ? '' : 'none';
                if (match) anyVisible = true;
            });
            g.style.display = anyVisible ? '' : 'none';
        });
    });

    dropdown.addEventListener('click', function(e){
        var opt = e.target.closest('.city-option');
        if (!opt) return;
        var val = opt.getAttribute('data-value');
        hiddenVal.value = val;
        search.value = '';
        currentLabel.textContent = '현재: ' + opt.textContent.trim();
        dropdown.style.display = 'none';
        // 검색 리셋
        dropdown.querySelectorAll('.city-group, .city-option').forEach(function(el){ el.style.display = ''; });
    });
})();
</script>

<?php } elseif ($tab == 'playlist') { ?>
<!-- ====== 플레이리스트 탭 ====== -->

<?php if (!empty($pending_songs)) { ?>
<div class="mg-card" style="margin-bottom:1rem;border:1px solid var(--mg-warning);">
    <div class="mg-card-header" style="background:var(--mg-warning);color:#000;">🔔 신청곡 대기 (<?php echo count($pending_songs); ?>건)</div>
    <div class="mg-card-body" style="padding:0;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:100px;">신청자</th>
                    <th>제목</th>
                    <th style="width:110px;">Video ID</th>
                    <th style="width:100px;">신청일</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_songs as $ps) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($ps['mb_nick'] ?? $ps['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($ps['rr_title']); ?></td>
                    <td><code style="font-size:0.75rem;"><?php echo htmlspecialchars($ps['rr_youtube_vid']); ?></code></td>
                    <td style="font-size:0.8rem;"><?php echo substr($ps['rr_created_at'], 5, 11); ?></td>
                    <td>
                        <form method="post" action="./radio_update.php" style="display:inline;">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="approve_song_request">
                            <input type="hidden" name="rr_id" value="<?php echo $ps['rr_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-primary" title="승인 → 플레이리스트에 추가">✅</button>
                        </form>
                        <form method="post" action="./radio_update.php" style="display:inline;" onsubmit="return confirm('이 신청을 거절하시겠습니까?');">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="reject_request">
                            <input type="hidden" name="rr_id" value="<?php echo $ps['rr_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-danger" title="거절">❌</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<div class="mg-card">
    <div class="mg-card-header">곡 추가</div>
    <div class="mg-card-body">
        <form method="post" action="./radio_update.php" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="hidden" name="action" value="add_track">
            <div class="mg-form-group" style="flex:2;min-width:200px;margin-bottom:0;">
                <label class="mg-form-label">유튜브 URL</label>
                <input type="text" name="youtube_url" class="mg-form-input" placeholder="https://www.youtube.com/watch?v=..." required>
            </div>
            <div class="mg-form-group" style="flex:1;min-width:150px;margin-bottom:0;">
                <label class="mg-form-label">제목</label>
                <input type="text" name="title" class="mg-form-input" placeholder="곡 제목" required>
            </div>
            <button type="submit" class="mg-btn mg-btn-primary" style="height:38px;">추가</button>
        </form>
    </div>
</div>

<div class="mg-card" style="margin-top:1rem;">
    <div class="mg-card-header">플레이리스트 (<?php echo count($tracks); ?>곡)</div>
    <div class="mg-card-body" style="padding:0;">
        <?php if (empty($tracks)) { ?>
        <div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">등록된 곡이 없습니다.</div>
        <?php } else { ?>
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:50px;">순서</th>
                    <th>제목</th>
                    <th style="width:120px;">Video ID</th>
                    <th style="width:100px;">만료</th>
                    <th style="width:60px;">상태</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tracks as $i => $t) { ?>
                <tr style="<?php echo !$t['is_active'] ? 'opacity:0.5;' : ''; ?>">
                    <td>
                        <input type="number" value="<?php echo (int)$t['sort_order']; ?>" min="0" style="width:50px;padding:2px 4px;background:var(--mg-bg-tertiary);border:1px solid var(--mg-bg-tertiary);border-radius:4px;color:var(--mg-text-primary);text-align:center;" data-track-id="<?php echo $t['track_id']; ?>" class="sort-input">
                    </td>
                    <td><?php echo htmlspecialchars($t['title']); ?></td>
                    <td><code style="font-size:0.75rem;"><?php echo htmlspecialchars($t['youtube_vid']); ?></code></td>
                    <td style="font-size:0.75rem;">
                        <?php if (empty($t['expires_at'])) { ?>
                            <span style="color:var(--mg-text-muted);">무기한</span>
                        <?php } elseif (strtotime($t['expires_at']) > time()) { ?>
                            <span style="color:var(--mg-text-secondary);"><?php echo date('m-d H:i', strtotime($t['expires_at'])); ?></span>
                        <?php } else { ?>
                            <span style="color:var(--mg-error);text-decoration:line-through;">만료됨</span>
                        <?php } ?>
                    </td>
                    <td>
                        <form method="post" action="./radio_update.php" style="display:inline;">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="toggle_track">
                            <input type="hidden" name="track_id" value="<?php echo $t['track_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm <?php echo $t['is_active'] ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>"><?php echo $t['is_active'] ? 'ON' : 'OFF'; ?></button>
                        </form>
                    </td>
                    <td>
                        <form method="post" action="./radio_update.php" style="display:inline;" onsubmit="return confirm('이 곡을 삭제하시겠습니까?');">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="delete_track">
                            <input type="hidden" name="track_id" value="<?php echo $t['track_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-danger">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <div style="padding:0.75rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;justify-content:flex-end;">
            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="saveSortTracks()">순서 저장</button>
        </div>
        <?php } ?>
    </div>
</div>

<script>
function saveSortTracks() {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = './radio_update.php';
    form.innerHTML = '<input type="hidden" name="token" value="<?php echo $token; ?>"><input type="hidden" name="action" value="sort_tracks">';
    document.querySelectorAll('.sort-input[data-track-id]').forEach(function(el) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'orders[' + el.dataset.trackId + ']';
        inp.value = el.value;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php } elseif ($tab == 'ments') { ?>
<!-- ====== 멘트 탭 ====== -->

<?php if (!empty($pending_ments)) { ?>
<div class="mg-card" style="margin-bottom:1rem;border:1px solid var(--mg-warning);">
    <div class="mg-card-header" style="background:var(--mg-warning);color:#000;">🔔 신청 멘트 대기 (<?php echo count($pending_ments); ?>건)</div>
    <div class="mg-card-body" style="padding:0;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:100px;">신청자</th>
                    <th>멘트 내용</th>
                    <th style="width:100px;">신청일</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending_ments as $pm) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($pm['mb_nick'] ?? $pm['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($pm['rr_content']); ?></td>
                    <td style="font-size:0.8rem;"><?php echo substr($pm['rr_created_at'], 5, 11); ?></td>
                    <td>
                        <form method="post" action="./radio_update.php" style="display:inline;">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="approve_ment_request">
                            <input type="hidden" name="rr_id" value="<?php echo $pm['rr_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-primary" title="승인 → 멘트 목록에 추가">✅</button>
                        </form>
                        <form method="post" action="./radio_update.php" style="display:inline;" onsubmit="return confirm('이 신청을 거절하시겠습니까?');">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="reject_request">
                            <input type="hidden" name="rr_id" value="<?php echo $pm['rr_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-danger" title="거절">❌</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<div class="mg-card">
    <div class="mg-card-header">멘트 추가</div>
    <div class="mg-card-body">
        <form method="post" action="./radio_update.php" style="display:flex;gap:0.5rem;align-items:flex-end;">
            <input type="hidden" name="token" value="<?php echo $token; ?>">
            <input type="hidden" name="action" value="add_ment">
            <div class="mg-form-group" style="flex:1;margin-bottom:0;">
                <label class="mg-form-label">멘트 내용 (200자 이내)</label>
                <input type="text" name="content" class="mg-form-input" maxlength="200" placeholder="오늘도 좋은 하루 되세요!" required>
            </div>
            <button type="submit" class="mg-btn mg-btn-primary" style="height:38px;">추가</button>
        </form>
    </div>
</div>

<div class="mg-card" style="margin-top:1rem;">
    <div class="mg-card-header">멘트 목록 (<?php echo count($ments); ?>개)</div>
    <div class="mg-card-body" style="padding:0;">
        <?php if (empty($ments)) { ?>
        <div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">등록된 멘트가 없습니다.</div>
        <?php } else { ?>
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:50px;">순서</th>
                    <th>내용</th>
                    <th style="width:100px;">만료</th>
                    <th style="width:60px;">상태</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ments as $m) { ?>
                <tr style="<?php echo !$m['is_active'] ? 'opacity:0.5;' : ''; ?>">
                    <td>
                        <input type="number" value="<?php echo (int)$m['sort_order']; ?>" min="0" style="width:50px;padding:2px 4px;background:var(--mg-bg-tertiary);border:1px solid var(--mg-bg-tertiary);border-radius:4px;color:var(--mg-text-primary);text-align:center;" data-ment-id="<?php echo $m['ment_id']; ?>" class="sort-ment-input">
                    </td>
                    <td><?php echo htmlspecialchars($m['content']); ?></td>
                    <td style="font-size:0.75rem;">
                        <?php if (empty($m['expires_at'])) { ?>
                            <span style="color:var(--mg-text-muted);">무기한</span>
                        <?php } elseif (strtotime($m['expires_at']) > time()) { ?>
                            <span style="color:var(--mg-text-secondary);"><?php echo date('m-d H:i', strtotime($m['expires_at'])); ?></span>
                        <?php } else { ?>
                            <span style="color:var(--mg-error);text-decoration:line-through;">만료됨</span>
                        <?php } ?>
                    </td>
                    <td>
                        <form method="post" action="./radio_update.php" style="display:inline;">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="toggle_ment">
                            <input type="hidden" name="ment_id" value="<?php echo $m['ment_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm <?php echo $m['is_active'] ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>"><?php echo $m['is_active'] ? 'ON' : 'OFF'; ?></button>
                        </form>
                    </td>
                    <td>
                        <form method="post" action="./radio_update.php" style="display:inline;" onsubmit="return confirm('이 멘트를 삭제하시겠습니까?');">
                            <input type="hidden" name="token" value="<?php echo $token; ?>">
                            <input type="hidden" name="action" value="delete_ment">
                            <input type="hidden" name="ment_id" value="<?php echo $m['ment_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-danger">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <div style="padding:0.75rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;justify-content:flex-end;">
            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="saveSortMents()">순서 저장</button>
        </div>
        <?php } ?>
    </div>
</div>

<script>
function saveSortMents() {
    var form = document.createElement('form');
    form.method = 'post';
    form.action = './radio_update.php';
    form.innerHTML = '<input type="hidden" name="token" value="<?php echo $token; ?>"><input type="hidden" name="action" value="sort_ments">';
    document.querySelectorAll('.sort-ment-input[data-ment-id]').forEach(function(el) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'orders[' + el.dataset.mentId + ']';
        inp.value = el.value;
        form.appendChild(inp);
    });
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php } ?>

<?php include_once('./_tail.php'); ?>
