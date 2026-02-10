<?php
/**
 * Morgan Edition - 상품 상세 페이지
 */

include_once('./_common.php');

// 로그인 체크
if ($is_guest) {
    alert('회원만 이용하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

// 레벨 3 이상만 상점 이용 가능
if ($member['mb_level'] < 3) {
    alert('캐릭터 승인 후 이용하실 수 있습니다.', G5_BBS_URL.'/character.php');
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

// 상점 사용 여부 체크
$shop_use = mg_get_config('shop_use', '1');
if ($shop_use != '1') {
    alert('상점 기능이 비활성화되어 있습니다.');
}

// 상품 ID
$si_id = isset($_GET['si_id']) ? (int)$_GET['si_id'] : 0;
if (!$si_id) {
    alert('잘못된 접근입니다.', G5_BBS_URL.'/shop.php');
}

// 상품 정보
$item = mg_get_shop_item($si_id);
if (!$item || !$item['si_display']) {
    alert('존재하지 않거나 비공개된 상품입니다.', G5_BBS_URL.'/shop.php');
}

// 카테고리 정보
$category = sql_fetch("SELECT * FROM {$g5['mg_shop_category_table']} WHERE sc_id = {$item['sc_id']}");

// 상품 상태
$status = mg_get_item_status($item);

// 구매 가능 여부
$_can_buy = mg_can_buy_item($member['mb_id'], $si_id);
$can_buy = $_can_buy['can_buy'] ? true : $_can_buy['message'];

// 보유 수량
$my_count = mg_get_inventory_count($member['mb_id'], $si_id);

// 회원 포인트
$my_point = $member['mb_point'];

// 선물 기능 사용 여부
$gift_use = mg_get_config('shop_gift_use', '1');

$g5['title'] = htmlspecialchars($item['si_name']) . ' - 상점';
include_once(G5_THEME_PATH.'/head.php');

// 스킨 파일 경로
$skin_path = G5_THEME_PATH.'/skin/shop';
if (!is_dir($skin_path)) {
    $skin_path = G5_THEME_PATH.'/skin/shop';
    if (!is_dir($skin_path)) {
        mkdir($skin_path, 0755, true);
    }
}

include_once($skin_path.'/view.skin.php');

include_once(G5_THEME_PATH.'/tail.php');
