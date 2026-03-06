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

// 라디오 해금 체크
if (!mg_is_radio_unlocked()) {
    echo json_encode(array('success' => false, 'message' => '라디오 시설이 아직 건설되지 않았습니다.', 'not_unlocked' => true));
    exit;
}

switch ($action) {
    case 'status':
        $cfg = sql_fetch("SELECT * FROM {$g5['mg_radio_config_table']} WHERE config_id = 1");
        if (!$cfg || !$cfg['is_active']) {
            echo json_encode(array('success' => false, 'message' => '라디오 비활성'));
            exit;
        }

        // 플레이리스트
        $tracks = array();
        $result = sql_query("SELECT track_id, youtube_vid, title FROM {$g5['mg_radio_playlist_table']} WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY sort_order ASC, track_id ASC");
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
        $result = sql_query("SELECT content FROM {$g5['mg_radio_ments_table']} WHERE is_active = 1 AND (expires_at IS NULL OR expires_at > NOW()) ORDER BY sort_order ASC, ment_id ASC");
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

    // ─── 노래 신청 ───
    case 'request_song':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'POST only'));
            exit;
        }
        if (!$is_member) {
            echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
            exit;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $youtube_url = trim($input['youtube_url'] ?? '');
        $title       = trim($input['title'] ?? '');
        if (!$youtube_url || !$title) {
            echo json_encode(array('success' => false, 'message' => 'YouTube URL과 곡 제목을 입력해주세요.'));
            exit;
        }
        if (mb_strlen($title) > 200) {
            echo json_encode(array('success' => false, 'message' => '곡 제목은 200자 이내로 입력해주세요.'));
            exit;
        }
        $vid = mg_parse_youtube_vid($youtube_url);
        if (!$vid) {
            echo json_encode(array('success' => false, 'message' => '유효한 YouTube URL을 입력해주세요.'));
            exit;
        }
        // 인벤토리 확인 + 소비
        $si_id = mg_consume_radio_ticket($member['mb_id'], 'radio_song');
        if (!$si_id) {
            echo json_encode(array('success' => false, 'message' => '노래 신청권이 없습니다.'));
            exit;
        }
        // 상품 effect에서 노출 기간 읽기
        $_item = sql_fetch("SELECT si_effect FROM {$g5['mg_shop_item_table']} WHERE si_id = {$si_id}");
        $_eff  = $_item ? json_decode($_item['si_effect'], true) : array();
        $duration_hours = isset($_eff['duration_hours']) ? (int)$_eff['duration_hours'] : 72;

        $title_esc = sql_real_escape_string($title);
        $url_esc   = sql_real_escape_string($youtube_url);
        $vid_esc   = sql_real_escape_string($vid);
        $mb_esc    = sql_real_escape_string($member['mb_id']);
        sql_query("INSERT INTO {$g5['mg_radio_requests_table']}
                   (rr_type, mb_id, rr_title, rr_youtube_url, rr_youtube_vid, rr_status, rr_duration_hours)
                   VALUES ('song', '{$mb_esc}', '{$title_esc}', '{$url_esc}', '{$vid_esc}', 'pending', {$duration_hours})");
        echo json_encode(array('success' => true, 'message' => '노래 신청이 완료되었습니다. 관리자 검수 후 반영됩니다.'));
        break;

    // ─── 멘트 신청 ───
    case 'request_ment':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(array('success' => false, 'message' => 'POST only'));
            exit;
        }
        if (!$is_member) {
            echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
            exit;
        }
        $input   = json_decode(file_get_contents('php://input'), true);
        $content = trim($input['content'] ?? '');
        if (!$content) {
            echo json_encode(array('success' => false, 'message' => '멘트 내용을 입력해주세요.'));
            exit;
        }
        if (mb_strlen($content) > 200) {
            echo json_encode(array('success' => false, 'message' => '멘트는 200자 이내로 입력해주세요.'));
            exit;
        }
        // 인벤토리 확인 + 소비
        $si_id = mg_consume_radio_ticket($member['mb_id'], 'radio_ment');
        if (!$si_id) {
            echo json_encode(array('success' => false, 'message' => '라디오 멘트권이 없습니다.'));
            exit;
        }
        // 상품 effect에서 노출 기간 읽기
        $_item = sql_fetch("SELECT si_effect FROM {$g5['mg_shop_item_table']} WHERE si_id = {$si_id}");
        $_eff  = $_item ? json_decode($_item['si_effect'], true) : array();
        $duration_hours = isset($_eff['duration_hours']) ? (int)$_eff['duration_hours'] : 24;

        $content_esc = sql_real_escape_string($content);
        $mb_esc      = sql_real_escape_string($member['mb_id']);
        sql_query("INSERT INTO {$g5['mg_radio_requests_table']}
                   (rr_type, mb_id, rr_content, rr_status, rr_duration_hours)
                   VALUES ('ment', '{$mb_esc}', '{$content_esc}', 'pending', {$duration_hours})");
        echo json_encode(array('success' => true, 'message' => '멘트 신청이 완료되었습니다. 관리자 검수 후 반영됩니다.'));
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
