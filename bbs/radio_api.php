<?php
/**
 * Morgan Edition - 라디오 위젯 API
 * GET ?action=status  → 전체 상태 (설정+플레이리스트+멘트+날씨)
 * GET ?action=weather → 날씨만 (API 모드 시 캐시/갱신)
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'status':
        $cfg = sql_fetch("SELECT * FROM {$g5['mg_radio_config_table']} WHERE config_id = 1");
        if (!$cfg || !$cfg['is_active']) {
            echo json_encode(array('success' => false, 'message' => '라디오 비활성'));
            exit;
        }

        // 플레이리스트
        $tracks = array();
        $result = sql_query("SELECT track_id, youtube_vid, title FROM {$g5['mg_radio_playlist_table']} WHERE is_active = 1 ORDER BY sort_order ASC, track_id ASC");
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $tracks[] = array(
                    'id' => (int)$row['track_id'],
                    'vid' => $row['youtube_vid'],
                    'title' => $row['title'],
                );
            }
        }

        // 멘트
        $ments = array();
        $result = sql_query("SELECT content FROM {$g5['mg_radio_ments_table']} WHERE is_active = 1 ORDER BY sort_order ASC, ment_id ASC");
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $ments[] = $row['content'];
            }
        }

        // 날씨
        $weather = _get_weather($cfg);

        echo json_encode(array(
            'success' => true,
            'data' => array(
                'play_mode' => $cfg['play_mode'],
                'ment_mode' => $cfg['ment_mode'],
                'ment_interval' => (int)$cfg['ment_interval'],
                'tracks' => $tracks,
                'ments' => $ments,
                'weather' => $weather,
                'weather_mode' => $cfg['weather_mode'],
            ),
        ), JSON_UNESCAPED_UNICODE);
        break;

    case 'weather':
        $cfg = sql_fetch("SELECT * FROM {$g5['mg_radio_config_table']} WHERE config_id = 1");
        $weather = $cfg ? _get_weather($cfg) : null;
        echo json_encode(array('success' => true, 'weather' => $weather), JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(array('success' => false, 'message' => 'Unknown action'));
}

function _get_weather($cfg) {
    if ($cfg['weather_mode'] === 'manual') {
        return array(
            'temp' => (int)$cfg['manual_temp'],
            'type' => $cfg['manual_weather'] ?: 'sunny',
        );
    }

    // API 모드: 캐시 확인
    if ($cfg['weather_cache'] && $cfg['weather_cached_at']) {
        $cached_at = strtotime($cfg['weather_cached_at']);
        if ($cached_at && (time() - $cached_at) < 3600) {
            $cache = json_decode($cfg['weather_cache'], true);
            if ($cache) return $cache;
        }
    }

    // API 호출
    $city = $cfg['weather_city'] ?: 'Seoul';
    $api_key = $cfg['weather_api_key'];
    if (!$api_key) {
        return array('temp' => 0, 'type' => 'sunny');
    }

    $url = 'https://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . '&appid=' . urlencode($api_key) . '&units=metric&lang=kr';
    $ctx = stream_context_create(array('http' => array('timeout' => 5)));
    $resp = @file_get_contents($url, false, $ctx);
    if (!$resp) {
        return array('temp' => 0, 'type' => 'sunny');
    }

    $data = json_decode($resp, true);
    if (!$data || !isset($data['main'])) {
        return array('temp' => 0, 'type' => 'sunny');
    }

    $weather_main = strtolower($data['weather'][0]['main'] ?? 'clear');
    $type_map = array(
        'clear' => 'sunny',
        'clouds' => 'cloudy',
        'rain' => 'rain',
        'drizzle' => 'rain',
        'thunderstorm' => 'thunderstorm',
        'snow' => 'snow',
        'mist' => 'fog',
        'fog' => 'fog',
        'haze' => 'fog',
    );
    $type = isset($type_map[$weather_main]) ? $type_map[$weather_main] : 'sunny';

    // 구름 세분화
    if ($weather_main === 'clouds') {
        $clouds_pct = $data['clouds']['all'] ?? 50;
        $type = $clouds_pct < 50 ? 'partly_cloudy' : 'cloudy';
    }

    $result = array(
        'temp' => round($data['main']['temp']),
        'type' => $type,
    );

    // 캐시 저장
    global $g5;
    $cache_json = sql_real_escape_string(json_encode($result));
    sql_query("UPDATE {$g5['mg_radio_config_table']} SET weather_cache = '{$cache_json}', weather_cached_at = NOW() WHERE config_id = 1");

    return $result;
}
