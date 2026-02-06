<?php
/**
 * Morgan Edition - 선물함 페이지
 */

include_once('./_common.php');

// 로그인 체크
if ($is_guest) {
    alert('회원만 이용하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

// 레벨 3 이상만 이용 가능
if ($member['mb_level'] < 3) {
    alert('캐릭터 승인 후 이용하실 수 있습니다.', G5_BBS_URL.'/character.php');
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

// 개척 시스템 해금 체크
if (!mg_is_gift_unlocked()) {
    $unlock_info = mg_get_unlock_info('gift', '');
    $facility_name = $unlock_info ? $unlock_info['fc_name'] : '선물함';
    alert("선물함은 아직 개척되지 않았습니다.\\n\\n'{$facility_name}' 개척에 참여해주세요!", G5_BBS_URL.'/pioneer.php');
}

// 선물 기능 사용 여부 체크
$gift_use = mg_get_config('shop_gift_use', '1');
if ($gift_use != '1') {
    alert('선물 기능이 비활성화되어 있습니다.');
}

$g5['title'] = '선물함';
include_once(G5_THEME_PATH.'/head.php');

// 탭 (pending, sent, received)
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'pending';

// 대기 중인 선물 (받은)
$pending_gifts = array();
$sql = "SELECT g.*, i.si_name, i.si_image, i.si_type, i.si_price, m.mb_nick as from_nick
        FROM {$g5['mg_gift_table']} g
        LEFT JOIN {$g5['mg_shop_item_table']} i ON g.si_id = i.si_id
        LEFT JOIN {$g5['member_table']} m ON g.mb_id_from = m.mb_id
        WHERE g.mb_id_to = '{$member['mb_id']}' AND g.gf_status = 'pending'
        ORDER BY g.gf_id DESC";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $pending_gifts[] = $row;
}

// 보낸 선물 내역
$sent_gifts = array();
if ($tab == 'sent') {
    $sql = "SELECT g.*, i.si_name, i.si_image, i.si_type, i.si_price, m.mb_nick as to_nick
            FROM {$g5['mg_gift_table']} g
            LEFT JOIN {$g5['mg_shop_item_table']} i ON g.si_id = i.si_id
            LEFT JOIN {$g5['member_table']} m ON g.mb_id_to = m.mb_id
            WHERE g.mb_id_from = '{$member['mb_id']}'
            ORDER BY g.gf_id DESC
            LIMIT 50";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $sent_gifts[] = $row;
    }
}

// 받은 선물 내역 (수락한 것)
$received_gifts = array();
if ($tab == 'received') {
    $sql = "SELECT g.*, i.si_name, i.si_image, i.si_type, i.si_price, m.mb_nick as from_nick
            FROM {$g5['mg_gift_table']} g
            LEFT JOIN {$g5['mg_shop_item_table']} i ON g.si_id = i.si_id
            LEFT JOIN {$g5['member_table']} m ON g.mb_id_from = m.mb_id
            WHERE g.mb_id_to = '{$member['mb_id']}' AND g.gf_status = 'accepted'
            ORDER BY g.gf_id DESC
            LIMIT 50";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $received_gifts[] = $row;
    }
}

$pending_count = count($pending_gifts);

// 스킨 파일 경로
$skin_path = G5_THEME_PATH.'/skin/shop';
if (!is_dir($skin_path)) {
    mkdir($skin_path, 0755, true);
}

include_once($skin_path.'/gift.skin.php');

include_once(G5_THEME_PATH.'/tail.php');
