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

// 회원 레벨 체크
$_lv = mg_check_member_level('pioneer', $member['mb_level']);
if (!$_lv['allowed']) { alert_close("개척은 회원 레벨 {$_lv['required']} 이상부터 이용 가능합니다."); }

// 파라미터
$fc_id = isset($_GET['fc_id']) ? (int)$_GET['fc_id'] : 0;
$view = isset($_GET['view']) ? clean_xss_tags($_GET['view']) : 'list';

// 유저 정보
$my_stamina = mg_get_stamina($member['mb_id']);
$my_materials = mg_get_materials($member['mb_id']);

// AJAX: 시설 상세 데이터 (랭킹)
if ($view === 'facility_data' && $fc_id > 0) {
    header('Content-Type: application/json; charset=utf-8');

    $facility = mg_get_facility($fc_id);
    if (!$facility) {
        echo json_encode(['error' => 'not_found']);
        exit;
    }

    $stamina_ranking = mg_get_facility_ranking($fc_id, 'stamina', 10);
    $material_rankings = [];
    foreach ($facility['materials'] as $mat) {
        $material_rankings[$mat['mt_code']] = mg_get_facility_ranking($fc_id, $mat['mt_code'], 10);
    }

    echo json_encode([
        'facility' => $facility,
        'stamina_ranking' => $stamina_ranking,
        'material_rankings' => $material_rankings,
        'my_stamina' => $my_stamina,
        'my_materials' => $my_materials,
    ]);
    exit;
}

// 시설 목록 / 파견
if ($view === 'expedition') {
    // 탐색 파견
    $g5['title'] = '탐색 파견 - 개척';
    $skin_file = 'expedition.skin.php';
} else {
    // 시설 목록 (fc_id가 있으면 모달 자동 오픈)
    $auto_open_fc_id = $fc_id;
    $facilities = mg_get_facilities();
    $complete_count = 0;
    $building_count = 0;
    foreach ($facilities as $f) {
        if ($f['fc_status'] === 'complete') $complete_count++;
        if ($f['fc_status'] === 'building') $building_count++;
    }

    // 뷰 모드: card(기본) / base(거점맵)
    $pioneer_view_mode = mg_config('pioneer_view_mode', 'card');
    $pioneer_map_image = mg_config('pioneer_map_image', '');

    // 거점뷰인데 이미지가 없으면 카드뷰로 폴백
    if ($pioneer_view_mode === 'base' && !$pioneer_map_image) {
        $pioneer_view_mode = 'card';
    }

    $g5['title'] = '개척 현황';
    $skin_file = 'list.skin.php';
}

include_once(G5_THEME_PATH.'/head.php');
include_once(G5_THEME_PATH.'/skin/pioneer/'.$skin_file);
include_once(G5_THEME_PATH.'/tail.php');
