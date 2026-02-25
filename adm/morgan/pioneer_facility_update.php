<?php
/**
 * Morgan Edition - 시설 관리 처리
 */

$sub_menu = "801000";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$w = isset($_POST['w']) ? $_POST['w'] : '';
$fc_id = isset($_POST['fc_id']) ? (int)$_POST['fc_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

$redirect_url = G5_ADMIN_URL.'/morgan/pioneer_facility.php';

// UI 모드 AJAX 전환
if ($action === 'set_view_mode') {
    header('Content-Type: application/json; charset=utf-8');
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'card';
    if (!in_array($mode, array('card', 'base'))) $mode = 'card';
    mg_set_config('pioneer_view_mode', $mode);
    echo json_encode(array('success' => true, 'mode' => $mode));
    exit;
}

// 거점 이미지 삭제 (AJAX)
if ($action === 'delete_base_image') {
    header('Content-Type: application/json; charset=utf-8');
    $old_map = mg_config('pioneer_map_image', '');
    if ($old_map) {
        $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_map);
        if (file_exists($old_file)) @unlink($old_file);
    }
    mg_set_config('pioneer_map_image', '');
    mg_set_config('pioneer_view_mode', 'card');
    echo json_encode(array('success' => true));
    exit;
}

// 뷰 설정 저장
if ($w === 'config') {
    // 뷰 모드
    $view_mode = isset($_POST['pioneer_view_mode']) ? $_POST['pioneer_view_mode'] : 'card';
    if (!in_array($view_mode, array('card', 'base'))) $view_mode = 'card';
    mg_set_config('pioneer_view_mode', $view_mode);

    // 거점 이미지 처리
    $upload_dir = G5_DATA_PATH.'/morgan';
    $upload_url = G5_DATA_URL.'/morgan';
    if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);

    $old_map = mg_config('pioneer_map_image', '');

    if (isset($_POST['pioneer_map_action']) && $_POST['pioneer_map_action'] === '__DELETE__') {
        if ($old_map) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_map);
            if (file_exists($old_file)) @unlink($old_file);
        }
        mg_set_config('pioneer_map_image', '');
    }
    elseif (isset($_FILES['pioneer_map_image_file']) && $_FILES['pioneer_map_image_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['pioneer_map_image_file'];
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            alert('허용되지 않는 파일 형식입니다. (jpg, png, gif, webp만 가능)');
        }

        // 맵 이미지는 일반 업로드 제한보다 큰 20MB까지 허용
        $max_map_size = 20 * 1024 * 1024;
        if ($file['size'] > $max_map_size) {
            alert('파일 크기가 20MB를 초과합니다.');
        }

        if ($old_map) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_map);
            if (file_exists($old_file)) @unlink($old_file);
        }

        $new_filename = 'pioneer_map_' . date('Ymd_His') . '.' . $ext;
        $target_path = $upload_dir . '/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            @chmod($target_path, 0644);
            mg_set_config('pioneer_map_image', $upload_url . '/' . $new_filename);
        } else {
            alert('파일 업로드에 실패했습니다. 디렉토리 권한을 확인해주세요.');
        }
    }
    elseif (isset($_FILES['pioneer_map_image_file']) && $_FILES['pioneer_map_image_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $err_code = $_FILES['pioneer_map_image_file']['error'];
        $err_msgs = array(
            UPLOAD_ERR_INI_SIZE   => 'PHP upload_max_filesize 초과',
            UPLOAD_ERR_FORM_SIZE  => '폼 MAX_FILE_SIZE 초과',
            UPLOAD_ERR_PARTIAL    => '파일이 부분만 업로드됨',
            UPLOAD_ERR_NO_TMP_DIR => '임시 폴더 없음',
            UPLOAD_ERR_CANT_WRITE => '디스크 쓰기 실패',
        );
        $err_msg = isset($err_msgs[$err_code]) ? $err_msgs[$err_code] : '알 수 없는 오류('.$err_code.')';
        alert('이미지 업로드 실패: ' . $err_msg);
    }

    alert('뷰 설정이 저장되었습니다.', $redirect_url);
}

// 마커 좌표 저장 (AJAX)
if ($w === 'marker' && $fc_id > 0) {
    global $mg;

    $fc_map_x = isset($_POST['fc_map_x']) && $_POST['fc_map_x'] !== '' ? max(0, min(100, (float)$_POST['fc_map_x'])) : null;
    $fc_map_y = isset($_POST['fc_map_y']) && $_POST['fc_map_y'] !== '' ? max(0, min(100, (float)$_POST['fc_map_y'])) : null;

    $map_x_sql = $fc_map_x !== null ? $fc_map_x : 'NULL';
    $map_y_sql = $fc_map_y !== null ? $fc_map_y : 'NULL';

    sql_query("UPDATE {$mg['facility_table']} SET fc_map_x = {$map_x_sql}, fc_map_y = {$map_y_sql} WHERE fc_id = {$fc_id}");

    echo 'OK';
    exit;
}

// 삭제
if ($w === 'd' && $fc_id > 0) {
    global $mg;

    // 기여 기록 삭제
    sql_query("DELETE FROM {$mg['facility_contribution_table']} WHERE fc_id = {$fc_id}");
    // 명예의 전당 삭제
    sql_query("DELETE FROM {$mg['facility_honor_table']} WHERE fc_id = {$fc_id}");
    // 재료 비용 삭제
    sql_query("DELETE FROM {$mg['facility_material_cost_table']} WHERE fc_id = {$fc_id}");
    // 시설 삭제
    sql_query("DELETE FROM {$mg['facility_table']} WHERE fc_id = {$fc_id}");

    alert('삭제되었습니다.', $redirect_url);
}

// 건설 시작
if ($w === 'start' && $fc_id > 0) {
    global $mg;

    sql_query("UPDATE {$mg['facility_table']} SET fc_status = 'building' WHERE fc_id = {$fc_id} AND fc_status = 'locked'");

    alert('건설이 시작되었습니다.', $redirect_url);
}

// 강제 완공
if ($w === 'complete' && $fc_id > 0) {
    global $mg;

    $facility = mg_get_facility($fc_id);
    if (!$facility) {
        alert('시설을 찾을 수 없습니다.');
    }

    // 자원 채우기
    sql_query("UPDATE {$mg['facility_table']}
               SET fc_stamina_current = fc_stamina_cost
               WHERE fc_id = {$fc_id}");

    sql_query("UPDATE {$mg['facility_material_cost_table']}
               SET fmc_current = fmc_required
               WHERE fc_id = {$fc_id}");

    // 완공 처리
    $now = date('Y-m-d H:i:s');
    sql_query("UPDATE {$mg['facility_table']}
               SET fc_status = 'complete', fc_complete_date = '{$now}'
               WHERE fc_id = {$fc_id}");

    // 명예의 전당 기록 (기여 기록이 있는 경우)
    mg_record_facility_honor($fc_id);

    alert('시설이 완공되었습니다.', $redirect_url);
}

// 추가/수정
$fc_name = isset($_POST['fc_name']) ? clean_xss_tags($_POST['fc_name']) : '';
$fc_icon = isset($_POST['fc_icon']) ? clean_xss_tags($_POST['fc_icon']) : '';
$fc_desc = isset($_POST['fc_desc']) ? clean_xss_tags($_POST['fc_desc']) : '';
$fc_order = isset($_POST['fc_order']) ? (int)$_POST['fc_order'] : 0;
$fc_stamina_cost = isset($_POST['fc_stamina_cost']) ? (int)$_POST['fc_stamina_cost'] : 0;
$fc_unlock_type = isset($_POST['fc_unlock_type']) ? clean_xss_tags($_POST['fc_unlock_type']) : '';
$fc_unlock_target = isset($_POST['fc_unlock_target']) ? clean_xss_tags($_POST['fc_unlock_target']) : '';
$fc_map_x = isset($_POST['fc_map_x']) && $_POST['fc_map_x'] !== '' ? max(0, min(100, (float)$_POST['fc_map_x'])) : null;
$fc_map_y = isset($_POST['fc_map_y']) && $_POST['fc_map_y'] !== '' ? max(0, min(100, (float)$_POST['fc_map_y'])) : null;
$mat_costs = isset($_POST['mat_cost']) ? $_POST['mat_cost'] : array();
$del_icon = isset($_POST['del_icon']) ? true : false;

if (empty($fc_name)) {
    alert('시설명을 입력해주세요.');
}

global $mg;

// 아이콘 이미지 업로드 처리
$icon_upload_path = G5_DATA_PATH.'/morgan/facility';
$icon_upload_url = G5_DATA_URL.'/morgan/facility';

// 업로드 디렉토리 생성
if (!is_dir($icon_upload_path)) {
    @mkdir($icon_upload_path, 0755, true);
}

// 기존 아이콘 값 가져오기 (수정 시)
$old_icon = '';
if ($w === 'u' && $fc_id > 0) {
    $old_facility = sql_fetch("SELECT fc_icon FROM {$mg['facility_table']} WHERE fc_id = {$fc_id}");
    $old_icon = $old_facility['fc_icon'] ?? '';
}

// 아이콘 삭제 체크
if ($del_icon && $old_icon && strpos($old_icon, '/') !== false) {
    // 기존 이미지 파일 삭제
    $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_icon);
    if (file_exists($old_file)) {
        @unlink($old_file);
    }
    $fc_icon = '';
}

// 새 이미지 업로드
if (isset($_FILES['fc_icon_file']) && $_FILES['fc_icon_file']['tmp_name']) {
    $file = $_FILES['fc_icon_file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'svg', 'webp');

    if (in_array($ext, $allowed)) {
        // 기존 이미지 삭제
        if ($old_icon && strpos($old_icon, '/') !== false) {
            $old_file = str_replace(G5_DATA_URL, G5_DATA_PATH, $old_icon);
            if (file_exists($old_file)) {
                @unlink($old_file);
            }
        }

        $new_filename = 'fc_'.($fc_id ?: time()).'_'.time().'.'.$ext;
        $new_filepath = $icon_upload_path.'/'.$new_filename;

        if (move_uploaded_file($file['tmp_name'], $new_filepath)) {
            $fc_icon = $icon_upload_url.'/'.$new_filename;
        }
    }
} elseif (!$del_icon && empty($fc_icon) && $old_icon) {
    // 새 업로드 없고 삭제도 안 했으면 기존 값 유지
    $fc_icon = $old_icon;
}

$fc_name_esc = sql_real_escape_string($fc_name);
$fc_icon_esc = sql_real_escape_string($fc_icon);
$fc_desc_esc = sql_real_escape_string($fc_desc);
$fc_unlock_type_esc = sql_real_escape_string($fc_unlock_type);
$fc_unlock_target_esc = sql_real_escape_string($fc_unlock_target);

if ($w === 'u' && $fc_id > 0) {
    // 수정
    $map_x_sql = $fc_map_x !== null ? $fc_map_x : 'NULL';
    $map_y_sql = $fc_map_y !== null ? $fc_map_y : 'NULL';

    sql_query("UPDATE {$mg['facility_table']} SET
               fc_name = '{$fc_name_esc}',
               fc_icon = '{$fc_icon_esc}',
               fc_desc = '{$fc_desc_esc}',
               fc_order = {$fc_order},
               fc_stamina_cost = {$fc_stamina_cost},
               fc_unlock_type = '{$fc_unlock_type_esc}',
               fc_unlock_target = '{$fc_unlock_target_esc}',
               fc_map_x = {$map_x_sql},
               fc_map_y = {$map_y_sql}
               WHERE fc_id = {$fc_id}");

    // 재료 비용 업데이트 (기존 투입량 보존)
    $old_mats = array();
    $old_result = sql_query("SELECT mt_id, fmc_current FROM {$mg['facility_material_cost_table']} WHERE fc_id = {$fc_id}");
    while ($om = sql_fetch_array($old_result)) {
        $old_mats[(int)$om['mt_id']] = (int)$om['fmc_current'];
    }

    sql_query("DELETE FROM {$mg['facility_material_cost_table']} WHERE fc_id = {$fc_id}");

    foreach ($mat_costs as $mt_id => $required) {
        $mt_id = (int)$mt_id;
        $required = (int)$required;
        if ($required > 0) {
            $current = isset($old_mats[$mt_id]) ? min($old_mats[$mt_id], $required) : 0;
            sql_query("INSERT INTO {$mg['facility_material_cost_table']}
                       (fc_id, mt_id, fmc_required, fmc_current)
                       VALUES ({$fc_id}, {$mt_id}, {$required}, {$current})");
        }
    }

    alert('수정되었습니다.', $redirect_url);

} else {
    // 추가
    $map_x_sql = $fc_map_x !== null ? $fc_map_x : 'NULL';
    $map_y_sql = $fc_map_y !== null ? $fc_map_y : 'NULL';

    sql_query("INSERT INTO {$mg['facility_table']}
               (fc_name, fc_icon, fc_desc, fc_order, fc_status, fc_stamina_cost, fc_stamina_current, fc_unlock_type, fc_unlock_target, fc_map_x, fc_map_y)
               VALUES ('{$fc_name_esc}', '{$fc_icon_esc}', '{$fc_desc_esc}', {$fc_order}, 'locked', {$fc_stamina_cost}, 0, '{$fc_unlock_type_esc}', '{$fc_unlock_target_esc}', {$map_x_sql}, {$map_y_sql})");

    $fc_id = sql_insert_id();

    // 재료 비용 입력
    foreach ($mat_costs as $mt_id => $required) {
        $mt_id = (int)$mt_id;
        $required = (int)$required;
        if ($required > 0) {
            sql_query("INSERT INTO {$mg['facility_material_cost_table']}
                       (fc_id, mt_id, fmc_required, fmc_current)
                       VALUES ({$fc_id}, {$mt_id}, {$required}, 0)");
        }
    }

    alert('등록되었습니다.', $redirect_url);
}
