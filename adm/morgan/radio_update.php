<?php
/**
 * Morgan Edition - 라디오 관리 처리
 */
$sub_menu = '801400';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'w');
include_once(G5_PATH.'/plugin/morgan/morgan.php');
check_admin_token();

$action = isset($_POST['action']) ? $_POST['action'] : '';
$redirect = isset($_POST['_redirect']) ? $_POST['_redirect'] : './radio.php';

switch ($action) {
    // ─── 설정 저장 ───
    case 'save_config':
        $is_active      = isset($_POST['is_active']) ? 1 : 0;
        $play_mode      = in_array($_POST['play_mode'] ?? '', array('sequential','random')) ? $_POST['play_mode'] : 'sequential';
        $weather_mode   = in_array($_POST['weather_mode'] ?? '', array('api','manual')) ? $_POST['weather_mode'] : 'manual';
        $weather_city   = sql_real_escape_string(trim($_POST['weather_city'] ?? ''));
        $weather_api_key = sql_real_escape_string(trim($_POST['weather_api_key'] ?? ''));
        $manual_temp    = (int)($_POST['manual_temp'] ?? 0);
        $manual_weather = sql_real_escape_string(trim($_POST['manual_weather'] ?? ''));
        $ment_mode      = in_array($_POST['ment_mode'] ?? '', array('sequential','random')) ? $_POST['ment_mode'] : 'sequential';
        $ment_interval  = max(3, min(120, (int)($_POST['ment_interval'] ?? 12)));

        sql_query("UPDATE {$g5['mg_radio_config_table']} SET
            is_active = {$is_active},
            play_mode = '{$play_mode}',
            weather_mode = '{$weather_mode}',
            weather_city = '{$weather_city}',
            weather_api_key = '{$weather_api_key}',
            manual_temp = {$manual_temp},
            manual_weather = '{$manual_weather}',
            ment_mode = '{$ment_mode}',
            ment_interval = {$ment_interval}
            WHERE config_id = 1");

        $redirect = './radio.php?tab=config';
        break;

    // ─── 플레이리스트 ───
    case 'add_track':
        $youtube_url = trim($_POST['youtube_url'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $vid = _parse_youtube_vid($youtube_url);

        if (!$vid) {
            alert('유효한 유튜브 URL이 아닙니다.');
        }
        if (!$title) $title = '제목 없음';

        $url_esc = sql_real_escape_string($youtube_url);
        $vid_esc = sql_real_escape_string($vid);
        $title_esc = sql_real_escape_string($title);

        $max = sql_fetch("SELECT COALESCE(MAX(sort_order),0)+1 as next_order FROM {$g5['mg_radio_playlist_table']}");
        $next_order = (int)$max['next_order'];

        sql_query("INSERT INTO {$g5['mg_radio_playlist_table']} (youtube_url, youtube_vid, title, sort_order) VALUES ('{$url_esc}', '{$vid_esc}', '{$title_esc}', {$next_order})");
        $redirect = './radio.php?tab=playlist';
        break;

    case 'delete_track':
        $track_id = (int)($_POST['track_id'] ?? 0);
        if ($track_id > 0) {
            sql_query("DELETE FROM {$g5['mg_radio_playlist_table']} WHERE track_id = {$track_id}");
        }
        $redirect = './radio.php?tab=playlist';
        break;

    case 'toggle_track':
        $track_id = (int)($_POST['track_id'] ?? 0);
        if ($track_id > 0) {
            sql_query("UPDATE {$g5['mg_radio_playlist_table']} SET is_active = 1 - is_active WHERE track_id = {$track_id}");
        }
        $redirect = './radio.php?tab=playlist';
        break;

    case 'sort_tracks':
        $orders = isset($_POST['orders']) ? $_POST['orders'] : array();
        foreach ($orders as $track_id => $order) {
            $track_id = (int)$track_id;
            $order = (int)$order;
            sql_query("UPDATE {$g5['mg_radio_playlist_table']} SET sort_order = {$order} WHERE track_id = {$track_id}");
        }
        $redirect = './radio.php?tab=playlist';
        break;

    // ─── 멘트 ───
    case 'add_ment':
        $content = trim($_POST['content'] ?? '');
        if (!$content) {
            alert('멘트 내용을 입력하세요.');
        }
        $content_esc = sql_real_escape_string($content);
        $max = sql_fetch("SELECT COALESCE(MAX(sort_order),0)+1 as next_order FROM {$g5['mg_radio_ments_table']}");
        $next_order = (int)$max['next_order'];

        sql_query("INSERT INTO {$g5['mg_radio_ments_table']} (content, sort_order) VALUES ('{$content_esc}', {$next_order})");
        $redirect = './radio.php?tab=ments';
        break;

    case 'delete_ment':
        $ment_id = (int)($_POST['ment_id'] ?? 0);
        if ($ment_id > 0) {
            sql_query("DELETE FROM {$g5['mg_radio_ments_table']} WHERE ment_id = {$ment_id}");
        }
        $redirect = './radio.php?tab=ments';
        break;

    case 'toggle_ment':
        $ment_id = (int)($_POST['ment_id'] ?? 0);
        if ($ment_id > 0) {
            sql_query("UPDATE {$g5['mg_radio_ments_table']} SET is_active = 1 - is_active WHERE ment_id = {$ment_id}");
        }
        $redirect = './radio.php?tab=ments';
        break;

    case 'sort_ments':
        $orders = isset($_POST['orders']) ? $_POST['orders'] : array();
        foreach ($orders as $ment_id => $order) {
            $ment_id = (int)$ment_id;
            $order = (int)$order;
            sql_query("UPDATE {$g5['mg_radio_ments_table']} SET sort_order = {$order} WHERE ment_id = {$ment_id}");
        }
        $redirect = './radio.php?tab=ments';
        break;

    default:
        alert('잘못된 요청입니다.');
}

goto_url($redirect);

// ─── 헬퍼 ───
function _parse_youtube_vid($url) {
    // youtu.be/VIDEO_ID
    if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $url, $m)) return $m[1];
    // youtube.com/watch?v=VIDEO_ID
    if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $url, $m)) return $m[1];
    // youtube.com/embed/VIDEO_ID
    if (preg_match('#/embed/([a-zA-Z0-9_-]{11})#', $url, $m)) return $m[1];
    // youtube.com/shorts/VIDEO_ID
    if (preg_match('#/shorts/([a-zA-Z0-9_-]{11})#', $url, $m)) return $m[1];
    return '';
}
