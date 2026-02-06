<?php
/**
 * Morgan Edition - 이모티콘 셋 제작
 */

include_once('./_common.php');

if ($is_guest) {
    alert('회원만 이용하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');

if (!mg_config('emoticon_use', '1') || !mg_config('emoticon_creator_use', '1')) {
    alert('이모티콘 제작 기능이 비활성화되어 있습니다.');
}

// 수정 모드
$es_id = isset($_GET['es_id']) ? (int)$_GET['es_id'] : 0;
$is_edit = false;
$set = null;
$emoticons = array();

if ($es_id > 0) {
    $set = mg_get_emoticon_set($es_id);
    if (!$set || $set['es_creator_id'] !== $member['mb_id']) {
        alert('접근 권한이 없습니다.', G5_BBS_URL.'/inventory.php?tab=emoticon');
    }
    // draft 또는 rejected 상태만 수정 가능
    if (!in_array($set['es_status'], array('draft', 'rejected'))) {
        alert('수정할 수 없는 상태입니다.', G5_BBS_URL.'/inventory.php?tab=emoticon');
    }
    $is_edit = true;
    $emoticons = mg_get_emoticons($es_id);
} else {
    // 등록권 확인
    $reg_check = mg_can_create_emoticon($member['mb_id']);
    if (!$reg_check['can']) {
        alert('이모티콘 등록권이 필요합니다. 상점에서 구매해주세요.', G5_BBS_URL.'/shop.php');
    }
}

$g5['title'] = $is_edit ? '이모티콘 셋 수정' : '이모티콘 셋 제작';
include_once(G5_THEME_PATH.'/head.php');

$min_count = (int)mg_config('emoticon_min_count', 8);
$max_count = (int)mg_config('emoticon_max_count', 30);
$max_size = (int)mg_config('emoticon_image_max_size', 512);
$rec_size = (int)mg_config('emoticon_image_size', 128);

include_once(G5_THEME_PATH.'/skin/emoticon/create.skin.php');

include_once(G5_THEME_PATH.'/tail.php');
