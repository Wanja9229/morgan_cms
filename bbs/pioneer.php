<?php
/**
 * Morgan Edition - 개척 현황 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

// 개척 시스템 활성화 확인
if (!mg_pioneer_enabled()) {
    alert_close('개척 시스템이 비활성화되어 있습니다.');
}

// 파라미터
$fc_id = isset($_GET['fc_id']) ? (int)$_GET['fc_id'] : 0;
$view = isset($_GET['view']) ? clean_xss_tags($_GET['view']) : 'list';

// 유저 정보
$my_stamina = mg_get_stamina($member['mb_id']);
$my_materials = mg_get_materials($member['mb_id']);

// 시설 목록 또는 상세
if ($fc_id > 0 && $view === 'detail') {
    // 시설 상세
    $facility = mg_get_facility($fc_id);
    if (!$facility) {
        alert_close('시설을 찾을 수 없습니다.');
    }

    // 기여 랭킹
    $stamina_ranking = mg_get_facility_ranking($fc_id, 'stamina', 10);
    $material_rankings = [];
    foreach ($facility['materials'] as $mat) {
        $material_rankings[$mat['mt_code']] = mg_get_facility_ranking($fc_id, $mat['mt_code'], 10);
    }

    $g5['title'] = $facility['fc_name'] . ' - 개척';
    $skin_file = 'detail.skin.php';
} else {
    // 시설 목록
    $facilities = mg_get_facilities();
    $complete_count = 0;
    $building_count = 0;
    foreach ($facilities as $f) {
        if ($f['fc_status'] === 'complete') $complete_count++;
        if ($f['fc_status'] === 'building') $building_count++;
    }

    $g5['title'] = '개척 현황';
    $skin_file = 'list.skin.php';
}

include_once(G5_THEME_PATH.'/head.php');
include_once(G5_THEME_PATH.'/skin/pioneer/'.$skin_file);
include_once(G5_THEME_PATH.'/tail.php');
