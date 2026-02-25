<?php
/**
 * Morgan Edition - 상점 페이지
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

// 개척 시스템 해금 체크
if (!mg_is_shop_unlocked()) {
    $unlock_info = mg_get_unlock_info('shop', '');
    $facility_name = $unlock_info ? $unlock_info['fc_name'] : '상점';
    alert("상점은 아직 개척되지 않았습니다.\\n\\n'{$facility_name}' 개척에 참여해주세요!", G5_BBS_URL.'/pioneer.php');
}

// 상점 사용 여부 체크
$shop_use = mg_get_config('shop_use', '1');
if ($shop_use != '1') {
    alert('상점 기능이 비활성화되어 있습니다.');
}

$g5['title'] = '상점';
include_once(G5_THEME_PATH.'/head.php');

// 타입 그룹 (카테고리 대체)
$type_groups = $mg['shop_type_groups'];
$type_labels = $mg['shop_type_labels'];

// 현재 탭 (타입 그룹 키 또는 emoticon)
$tab = isset($_GET['tab']) ? clean_xss_tags($_GET['tab']) : '';
$sub_type = isset($_GET['type']) ? clean_xss_tags($_GET['type']) : '';

// 페이지네이션
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 12;

// 특수 탭 여부
$is_emoticon_tab = ($tab === 'emoticon');
$is_title_tab = false;

// 현재 선택된 타입들
$current_types = array();
if ($tab && isset($type_groups[$tab])) {
    $group_def = $type_groups[$tab];
    $group_types = $group_def['types'];
    $sub_groups = isset($group_def['sub_groups']) ? $group_def['sub_groups'] : array();

    if ($sub_type) {
        // sub_group 키 체크 (예: 'title' → ['title_prefix','title_suffix'])
        if (isset($sub_groups[$sub_type])) {
            $current_types = $sub_groups[$sub_type]['types'];
        } elseif (in_array($sub_type, $group_types)) {
            $current_types = array($sub_type);
        } else {
            $current_types = $group_types;
            $sub_type = '';
        }
    } else {
        $current_types = $group_types;
    }
}

if ($is_emoticon_tab) {
    // 이모티콘 탭: 승인된 이모티콘 셋 목록
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';
    $emoticon_data = mg_get_emoticon_sets('approved', $page, $rows, $sort);
    $emoticon_sets = $emoticon_data['items'];
    $total_count = $emoticon_data['total'];
    $total_page = $emoticon_data['total_page'];
    $items = array();
} else {
    // 일반 상점 탭 - 타입 필터링
    $items_data = mg_get_shop_items_by_type($current_types, $page, $rows);
    $items = $items_data['items'];
    $total_count = $items_data['total'];
    $total_page = ceil($total_count / $rows);
    $emoticon_sets = array();
    $sort = '';
}

// 회원 포인트
$my_point = $member['mb_point'];

// 이모티콘 기능 사용 여부
$emoticon_use = mg_config('emoticon_use', '1');

// 스킨 파일 경로
$skin_path = G5_THEME_PATH.'/skin/shop';
if (!is_dir($skin_path)) {
    mkdir($skin_path, 0755, true);
}

include_once($skin_path.'/shop.skin.php');

include_once(G5_THEME_PATH.'/tail.php');
