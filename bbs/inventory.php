<?php
/**
 * Morgan Edition - 인벤토리 페이지
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

$g5['title'] = '인벤토리';
include_once(G5_THEME_PATH.'/head.php');

// 타입 그룹 (상점과 동일 구조)
$type_groups = $mg['shop_type_groups'];

// 현재 탭
$tab = isset($_GET['tab']) ? $_GET['tab'] : '';
$is_emoticon_tab = ($tab === 'emoticon');
$is_material_tab = ($tab === 'material');

// 타입 그룹 기반 필터링
$filter_types = array();
if (!$is_emoticon_tab && !$is_material_tab && $tab && isset($type_groups[$tab])) {
    $filter_types = $type_groups[$tab]['types'];
}

if ($is_emoticon_tab) {
    // 이모티콘 탭: 보유 셋 + 크리에이터 정보
    $my_emoticon_sets = mg_get_my_emoticon_sets($member['mb_id']);
    $creator_enabled = mg_config('emoticon_creator_use', '1') == '1';
    $creator_sets = $creator_enabled ? mg_get_creator_sets($member['mb_id']) : array();
    $reg_check = $creator_enabled ? mg_can_create_emoticon($member['mb_id']) : array('can' => false, 'count' => 0);
    $inventory = array();
    $active_items = array();
    $active_si_ids = array();
    $my_materials = array();
} elseif ($is_material_tab) {
    // 재료 탭: mg_user_material + mg_material_type
    $my_materials = mg_get_materials($member['mb_id']);
    $inventory = array();
    $active_items = array();
    $active_si_ids = array();
    $my_emoticon_sets = array();
    $creator_sets = array();
    $creator_enabled = false;
    $reg_check = array('can' => false, 'count' => 0);
} else {
    // 일반 인벤토리 (타입 그룹 필터)
    $inv_data = mg_get_inventory($member['mb_id'], 0, 0, 0, $filter_types);
    $inventory = $inv_data['items'];
    $active_items = mg_get_active_items($member['mb_id']);
    $active_si_ids = array_column($active_items, 'si_id');
    $my_emoticon_sets = array();
    $creator_sets = array();
    $creator_enabled = false;
    $reg_check = array('can' => false, 'count' => 0);
    $my_materials = array();
}

// 이모티콘 기능 사용 여부
$emoticon_use = mg_config('emoticon_use', '1');

// 스킨 파일 경로
$skin_path = G5_THEME_PATH.'/skin/shop';
if (!is_dir($skin_path)) {
    mkdir($skin_path, 0755, true);
}

include_once($skin_path.'/inventory.skin.php');

include_once(G5_THEME_PATH.'/tail.php');
