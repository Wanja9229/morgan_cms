<?php
/**
 * Morgan Edition - Core Plugin
 *
 * 커스텀 테이블 및 핵심 기능 정의
 */

if (!defined('_GNUBOARD_')) exit;

// 버전
define('MG_VERSION', '1.0.0');

// 경로
define('MG_PLUGIN_PATH', G5_PLUGIN_PATH.'/morgan');
define('MG_PLUGIN_URL', G5_PLUGIN_URL.'/morgan');

// ======================================
// 테이블명 정의 ($g5 전역 배열에 추가)
// ======================================
global $g5;

// Morgan 테이블은 mg_ 프리픽스 사용 (install.sql과 일치)
$g5['mg_config_table'] = 'mg_config';
$g5['mg_character_table'] = 'mg_character';
$g5['mg_character_log_table'] = 'mg_character_log';
$g5['mg_profile_field_table'] = 'mg_profile_field';
$g5['mg_profile_value_table'] = 'mg_profile_value';
$g5['mg_side_table'] = 'mg_side';
$g5['mg_class_table'] = 'mg_class';
$g5['mg_attendance_table'] = 'mg_attendance';
$g5['mg_notification_table'] = 'mg_notification';
$g5['mg_write_character_table'] = 'mg_write_character';
$g5['mg_main_row_table'] = 'mg_main_row';
$g5['mg_main_widget_table'] = 'mg_main_widget';
$g5['mg_shop_category_table'] = 'mg_shop_category';
$g5['mg_shop_item_table'] = 'mg_shop_item';
$g5['mg_shop_log_table'] = 'mg_shop_log';
$g5['mg_inventory_table'] = 'mg_inventory';
$g5['mg_item_active_table'] = 'mg_item_active';
$g5['mg_gift_table'] = 'mg_gift';
$g5['mg_rp_thread_table'] = 'mg_rp_thread';
$g5['mg_rp_reply_table'] = 'mg_rp_reply';
$g5['mg_rp_member_table'] = 'mg_rp_member';
$g5['mg_emoticon_set_table'] = 'mg_emoticon_set';
$g5['mg_emoticon_table'] = 'mg_emoticon';
$g5['mg_emoticon_own_table'] = 'mg_emoticon_own';
// 개척 시스템
$g5['mg_material_type_table'] = 'mg_material_type';
$g5['mg_user_material_table'] = 'mg_user_material';
$g5['mg_user_stamina_table'] = 'mg_user_stamina';
$g5['mg_facility_table'] = 'mg_facility';
$g5['mg_facility_material_cost_table'] = 'mg_facility_material_cost';
$g5['mg_facility_contribution_table'] = 'mg_facility_contribution';
$g5['mg_facility_honor_table'] = 'mg_facility_honor';

// 캐릭터 이미지 저장 경로
define('MG_CHAR_IMAGE_PATH', G5_DATA_PATH.'/character');
define('MG_CHAR_IMAGE_URL', G5_DATA_URL.'/character');

// UTF-8mb4 설정 (이모지 지원)
sql_query("SET NAMES utf8mb4");

// 메인 빌더 위젯 설정 (설정에서 가져오거나 기본값 사용)
// mg_config가 아직 로드 안 됐으면 기본값 사용
if (!defined('MG_WIDGET_ROW_HEIGHT')) {
    define('MG_WIDGET_ROW_HEIGHT', 300);
}
if (!defined('MG_WIDGET_GRID_WIDTH')) {
    define('MG_WIDGET_GRID_WIDTH', 1200);
}

// 이전 버전 호환용 $mg 배열
$mg = array();
$mg['character_table'] = $g5['mg_character_table'];
$mg['character_log_table'] = $g5['mg_character_log_table'];
$mg['profile_field_table'] = $g5['mg_profile_field_table'];
$mg['profile_value_table'] = $g5['mg_profile_value_table'];
$mg['side_table'] = $g5['mg_side_table'];
$mg['class_table'] = $g5['mg_class_table'];
$mg['config_table'] = $g5['mg_config_table'];
$mg['attendance_table'] = $g5['mg_attendance_table'];
$mg['notification_table'] = $g5['mg_notification_table'];
$mg['write_character_table'] = $g5['mg_write_character_table'];
$mg['main_row_table'] = $g5['mg_main_row_table'];
$mg['main_widget_table'] = $g5['mg_main_widget_table'];
$mg['shop_category_table'] = $g5['mg_shop_category_table'];
$mg['shop_item_table'] = $g5['mg_shop_item_table'];
$mg['shop_log_table'] = $g5['mg_shop_log_table'];
$mg['inventory_table'] = $g5['mg_inventory_table'];
$mg['item_active_table'] = $g5['mg_item_active_table'];
$mg['gift_table'] = $g5['mg_gift_table'];
$mg['rp_thread_table'] = $g5['mg_rp_thread_table'];
$mg['rp_reply_table'] = $g5['mg_rp_reply_table'];
$mg['rp_member_table'] = $g5['mg_rp_member_table'];
$mg['emoticon_set_table'] = $g5['mg_emoticon_set_table'];
$mg['emoticon_table'] = $g5['mg_emoticon_table'];
$mg['emoticon_own_table'] = $g5['mg_emoticon_own_table'];
// 개척 시스템
$mg['material_type_table'] = $g5['mg_material_type_table'];
$mg['user_material_table'] = $g5['mg_user_material_table'];
$mg['user_stamina_table'] = $g5['mg_user_stamina_table'];
$mg['facility_table'] = $g5['mg_facility_table'];
$mg['facility_material_cost_table'] = $g5['mg_facility_material_cost_table'];
$mg['facility_contribution_table'] = $g5['mg_facility_contribution_table'];
$mg['facility_honor_table'] = $g5['mg_facility_honor_table'];

// 상점 이미지 저장 경로
define('MG_SHOP_IMAGE_PATH', G5_DATA_PATH.'/shop');
define('MG_SHOP_IMAGE_URL', G5_DATA_URL.'/shop');

// 이모티콘 이미지 저장 경로
define('MG_EMOTICON_PATH', G5_DATA_PATH.'/emoticon');
define('MG_EMOTICON_URL', G5_DATA_URL.'/emoticon');

// 썸네일 사이즈
define('MG_THUMB_SIZE', 200);

/**
 * 이미지 썸네일 생성
 *
 * @param string $source_path 원본 이미지 경로
 * @param string $dest_path 썸네일 저장 경로
 * @param int $max_size 최대 크기 (기본 200px)
 * @return bool 성공 여부
 */
function mg_create_thumbnail($source_path, $dest_path, $max_size = 200) {
    if (!file_exists($source_path)) {
        return false;
    }

    $info = getimagesize($source_path);
    if (!$info) {
        return false;
    }

    $mime = $info['mime'];
    $width = $info[0];
    $height = $info[1];

    // 이미 작은 이미지면 복사만
    if ($width <= $max_size && $height <= $max_size) {
        return copy($source_path, $dest_path);
    }

    // 비율 계산
    $ratio = min($max_size / $width, $max_size / $height);
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);

    // 원본 이미지 로드
    switch ($mime) {
        case 'image/jpeg':
            $source = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $source = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $source = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            $source = imagecreatefromwebp($source_path);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    // 새 이미지 생성
    $thumb = imagecreatetruecolor($new_width, $new_height);

    // PNG/GIF 투명도 유지
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
        $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
        imagefill($thumb, 0, 0, $transparent);
    }

    // 리사이즈
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // 저장
    $ext = strtolower(pathinfo($dest_path, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $result = imagejpeg($thumb, $dest_path, 85);
            break;
        case 'png':
            $result = imagepng($thumb, $dest_path, 8);
            break;
        case 'gif':
            $result = imagegif($thumb, $dest_path);
            break;
        case 'webp':
            $result = imagewebp($thumb, $dest_path, 85);
            break;
        default:
            $result = imagejpeg($thumb, $dest_path, 85);
    }

    imagedestroy($source);
    imagedestroy($thumb);

    return $result;
}

/**
 * 캐릭터 이미지 업로드 처리 (원본 + 썸네일)
 *
 * @param array $file $_FILES 배열 요소
 * @param string $mb_id 회원 ID
 * @param string $type 'thumb' 또는 'image'
 * @return array ['success' => bool, 'filename' => string, 'thumb' => string]
 */
function mg_upload_character_image($file, $mb_id, $type = 'thumb') {
    $result = array('success' => false, 'filename' => '', 'thumb' => '');

    if ($file['error'] != UPLOAD_ERR_OK) {
        return $result;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

    if (!in_array($ext, $allowed)) {
        return $result;
    }

    // 파일 크기 체크 (5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return $result;
    }

    // 저장 디렉토리
    $upload_dir = MG_CHAR_IMAGE_PATH.'/'.$mb_id;
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // 파일명 생성
    $prefix = ($type == 'thumb') ? 'head_' : 'body_';
    $basename = $prefix . uniqid() . '.' . $ext;
    $filename = $mb_id . '/' . $basename;
    $full_path = MG_CHAR_IMAGE_PATH . '/' . $filename;

    // 원본 저장
    if (!move_uploaded_file($file['tmp_name'], $full_path)) {
        return $result;
    }

    $result['success'] = true;
    $result['filename'] = $filename;

    // 썸네일 생성 (두상 이미지인 경우)
    if ($type == 'thumb') {
        $thumb_basename = 'th_' . $basename;
        $thumb_filename = $mb_id . '/' . $thumb_basename;
        $thumb_path = MG_CHAR_IMAGE_PATH . '/' . $thumb_filename;

        if (mg_create_thumbnail($full_path, $thumb_path, MG_THUMB_SIZE)) {
            $result['thumb'] = $thumb_filename;
        }
    }

    return $result;
}

// 상점 아이템 타입 그룹 (카테고리 대체)
$mg['shop_type_groups'] = array(
    'decor' => array(
        'label' => '꾸미기',
        'icon' => 'sparkles',
        'types' => array('title', 'badge', 'nick_color', 'nick_effect')
    ),
    'border' => array(
        'label' => '테두리',
        'icon' => 'square',
        'types' => array('profile_border')
    ),
    'equip' => array(
        'label' => '장비',
        'icon' => 'shield',
        'types' => array('equip')
    ),
    'material' => array(
        'label' => '재료',
        'icon' => 'cube',
        'types' => array('material')
    ),
    'furniture' => array(
        'label' => '가구',
        'icon' => 'home',
        'types' => array('furniture')
    ),
    'etc' => array(
        'label' => '기타',
        'icon' => 'gift',
        'types' => array('etc')
    )
);

// 타입별 라벨
$mg['shop_type_labels'] = array(
    'title' => '칭호',
    'badge' => '뱃지',
    'nick_color' => '닉네임 색상',
    'nick_effect' => '닉네임 효과',
    'profile_border' => '프로필 테두리',
    'equip' => '장비',
    'material' => '재료',
    'furniture' => '가구',
    'etc' => '기타'
);

// ======================================
// 설정 로드 함수
// ======================================

/**
 * Morgan Edition 설정값 가져오기
 *
 * @param string $key 설정 키
 * @param mixed $default 기본값
 * @return mixed
 */
function mg_config($key, $default = null) {
    global $g5;
    static $config_cache = array();

    if (empty($config_cache)) {
        $sql = "SELECT cf_key, cf_value FROM {$g5['mg_config_table']}";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $config_cache[$row['cf_key']] = $row['cf_value'];
        }
    }

    return isset($config_cache[$key]) ? $config_cache[$key] : $default;
}

/**
 * Morgan Edition 설정값 저장
 *
 * @param string $key 설정 키
 * @param mixed $value 값
 * @param string $desc 설명 (신규 생성 시)
 */
function mg_config_set($key, $value, $desc = '') {
    global $g5;
    $key = sql_real_escape_string($key);
    $value = sql_real_escape_string($value);
    $desc = sql_real_escape_string($desc);

    $sql = "INSERT INTO {$g5['mg_config_table']} (cf_key, cf_value, cf_desc)
            VALUES ('{$key}', '{$value}', '{$desc}')
            ON DUPLICATE KEY UPDATE cf_value = '{$value}'";
    sql_query($sql);
}

// ======================================
// 캐릭터 함수
// ======================================

/**
 * 회원의 캐릭터 목록 가져오기
 *
 * @param string $mb_id 회원 ID
 * @param string $state 상태 필터 (null=전체)
 * @return array
 */
function mg_get_characters($mb_id, $state = null) {
    global $mg;

    $where = "mb_id = '".sql_real_escape_string($mb_id)."'";
    if ($state) {
        $where .= " AND ch_state = '".sql_real_escape_string($state)."'";
    } else {
        $where .= " AND ch_state != 'deleted'";
    }

    $sql = "SELECT * FROM {$mg['character_table']} WHERE {$where} ORDER BY ch_main DESC, ch_datetime ASC";
    $result = sql_query($sql);

    $characters = array();
    while ($row = sql_fetch_array($result)) {
        $characters[] = $row;
    }
    return $characters;
}

/**
 * 캐릭터 정보 가져오기
 *
 * @param int $ch_id 캐릭터 ID
 * @return array|false
 */
function mg_get_character($ch_id) {
    global $mg;

    $ch_id = (int)$ch_id;
    $sql = "SELECT * FROM {$mg['character_table']} WHERE ch_id = {$ch_id}";
    return sql_fetch($sql);
}

/**
 * 회원의 대표 캐릭터 가져오기
 *
 * @param string $mb_id 회원 ID
 * @return array|false
 */
function mg_get_main_character($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $sql = "SELECT * FROM {$mg['character_table']}
            WHERE mb_id = '{$mb_id}' AND ch_main = 1 AND ch_state = 'approved'
            LIMIT 1";
    return sql_fetch($sql);
}

/**
 * 캐릭터의 프로필 값 가져오기
 *
 * @param int $ch_id 캐릭터 ID
 * @return array [pf_code => value]
 */
function mg_get_character_profile($ch_id) {
    global $mg;

    $ch_id = (int)$ch_id;
    $sql = "SELECT pf.pf_code, pv.pv_value
            FROM {$mg['profile_value_table']} pv
            JOIN {$mg['profile_field_table']} pf ON pv.pf_id = pf.pf_id
            WHERE pv.ch_id = {$ch_id}";
    $result = sql_query($sql);

    $profile = array();
    while ($row = sql_fetch_array($result)) {
        $profile[$row['pf_code']] = $row['pv_value'];
    }
    return $profile;
}

/**
 * 프로필 양식 목록 가져오기
 *
 * @param bool $only_active 활성화된 것만
 * @return array
 */
function mg_get_profile_fields($only_active = true) {
    global $mg;

    $where = $only_active ? "WHERE pf_use = 1" : "";
    $sql = "SELECT * FROM {$mg['profile_field_table']} {$where} ORDER BY pf_order ASC";
    $result = sql_query($sql);

    $fields = array();
    while ($row = sql_fetch_array($result)) {
        if ($row['pf_options']) {
            $row['pf_options'] = json_decode($row['pf_options'], true);
        }
        $fields[] = $row;
    }
    return $fields;
}

// ======================================
// 출석 함수
// ======================================

/**
 * 오늘 출석 체크
 *
 * @param string $mb_id 회원 ID
 * @return array ['success' => bool, 'point' => int, 'message' => string]
 */
function mg_attendance_check($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $today = date('Y-m-d');

    // 이미 출석했는지 확인
    $sql = "SELECT * FROM {$mg['attendance_table']}
            WHERE mb_id = '{$mb_id}' AND at_date = '{$today}'";
    $exists = sql_fetch($sql);

    if ($exists) {
        return ['success' => false, 'point' => 0, 'message' => '이미 출석하셨습니다.'];
    }

    // 연속 출석 계산
    $sql = "SELECT COUNT(*) as cnt FROM {$mg['attendance_table']}
            WHERE mb_id = '{$mb_id}'
            AND at_date >= DATE_SUB('{$today}', INTERVAL 7 DAY)
            AND at_date < '{$today}'";
    $streak = sql_fetch($sql);
    $streak_count = (int)$streak['cnt'];

    // 포인트 계산
    $base_point = (int)mg_config('attendance_point', 100);
    $bonus_point = ($streak_count >= 6) ? (int)mg_config('attendance_bonus', 500) : 0;
    $total_point = $base_point + $bonus_point;

    // 출석 기록
    $sql = "INSERT INTO {$mg['attendance_table']} (mb_id, at_date, at_point)
            VALUES ('{$mb_id}', '{$today}', {$total_point})";
    sql_query($sql);

    // 포인트 지급
    insert_point($mb_id, $total_point, '출석 체크'.($bonus_point > 0 ? ' (7일 연속 보너스)' : ''));

    $message = $total_point.'P 지급되었습니다.';
    if ($bonus_point > 0) {
        $message .= ' (연속 출석 보너스 포함)';
    }

    return ['success' => true, 'point' => $total_point, 'message' => $message];
}

// ======================================
// 알림 함수
// ======================================

/**
 * 알림 생성
 *
 * @param string $mb_id 수신자
 * @param string $type 유형
 * @param string $title 제목
 * @param string $content 내용
 * @param string $url 링크
 */
function mg_notify($mb_id, $type, $title, $content = '', $url = '') {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $type = sql_real_escape_string($type);
    $title = sql_real_escape_string($title);
    $content = sql_real_escape_string($content);
    $url = sql_real_escape_string($url);

    $sql = "INSERT INTO {$mg['notification_table']}
            (mb_id, noti_type, noti_title, noti_content, noti_url)
            VALUES ('{$mb_id}', '{$type}', '{$title}', '{$content}', '{$url}')";
    sql_query($sql);
}

/**
 * 읽지 않은 알림 수
 *
 * @param string $mb_id 회원 ID
 * @return int
 */
function mg_get_unread_notification_count($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $sql = "SELECT COUNT(*) as cnt FROM {$mg['notification_table']}
            WHERE mb_id = '{$mb_id}' AND noti_read = 0";
    $row = sql_fetch($sql);
    return (int)$row['cnt'];
}

/**
 * 알림 목록 조회
 *
 * @param string $mb_id 회원 ID
 * @param int $page 페이지
 * @param int $rows 한 페이지 수
 * @param bool $unread_only 미읽음만
 * @return array ['items' => array, 'total' => int, 'total_page' => int]
 */
function mg_get_notifications($mb_id, $page = 1, $rows = 20, $unread_only = false) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $where = "WHERE mb_id = '{$mb_id}'";
    if ($unread_only) {
        $where .= " AND noti_read = 0";
    }

    $sql = "SELECT COUNT(*) as cnt FROM {$mg['notification_table']} {$where}";
    $row = sql_fetch($sql);
    $total = (int)$row['cnt'];
    $total_page = $rows > 0 ? ceil($total / $rows) : 1;
    $offset = ($page - 1) * $rows;

    $sql = "SELECT * FROM {$mg['notification_table']} {$where}
            ORDER BY noti_id DESC
            LIMIT {$offset}, {$rows}";
    $result = sql_query($sql);

    $items = array();
    while ($r = sql_fetch_array($result)) {
        $items[] = $r;
    }

    return array('items' => $items, 'total' => $total, 'total_page' => $total_page);
}

/**
 * 개별 알림 읽음 처리
 *
 * @param int $noti_id 알림 ID
 * @param string $mb_id 회원 ID (소유 확인)
 * @return bool
 */
function mg_mark_notification_read($noti_id, $mb_id) {
    global $mg;

    $noti_id = (int)$noti_id;
    $mb_id = sql_real_escape_string($mb_id);
    sql_query("UPDATE {$mg['notification_table']} SET noti_read = 1
               WHERE noti_id = {$noti_id} AND mb_id = '{$mb_id}'");
    return true;
}

/**
 * 전체 알림 읽음 처리
 *
 * @param string $mb_id 회원 ID
 * @return bool
 */
function mg_mark_all_notifications_read($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    sql_query("UPDATE {$mg['notification_table']} SET noti_read = 1
               WHERE mb_id = '{$mb_id}' AND noti_read = 0");
    return true;
}

/**
 * 개별 알림 삭제
 *
 * @param int $noti_id 알림 ID
 * @param string $mb_id 회원 ID (소유 확인)
 * @return bool
 */
function mg_delete_notification($noti_id, $mb_id) {
    global $mg;

    $noti_id = (int)$noti_id;
    $mb_id = sql_real_escape_string($mb_id);
    sql_query("DELETE FROM {$mg['notification_table']}
               WHERE noti_id = {$noti_id} AND mb_id = '{$mb_id}'");
    return true;
}

/**
 * 읽은 알림 전체 삭제
 *
 * @param string $mb_id 회원 ID
 * @return int 삭제 수
 */
function mg_delete_all_read_notifications($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    sql_query("DELETE FROM {$mg['notification_table']}
               WHERE mb_id = '{$mb_id}' AND noti_read = 1");
    return sql_affected_rows();
}

// ======================================
// 글-캐릭터 연결 함수
// ======================================

/**
 * 글에 연결된 캐릭터 가져오기
 *
 * @param string $bo_table 게시판 테이블명
 * @param int $wr_id 글 ID
 * @return array|false 캐릭터 정보 또는 false
 */
function mg_get_write_character($bo_table, $wr_id) {
    global $mg;

    $bo_table = sql_real_escape_string($bo_table);
    $wr_id = (int)$wr_id;

    $sql = "SELECT c.*
            FROM {$mg['write_character_table']} wc
            JOIN {$mg['character_table']} c ON wc.ch_id = c.ch_id
            WHERE wc.bo_table = '{$bo_table}' AND wc.wr_id = {$wr_id}";
    return sql_fetch($sql);
}

/**
 * 글에 캐릭터 연결 설정
 *
 * @param string $bo_table 게시판 테이블명
 * @param int $wr_id 글 ID
 * @param int $ch_id 캐릭터 ID (0이면 연결 삭제)
 * @return bool
 */
function mg_set_write_character($bo_table, $wr_id, $ch_id) {
    global $mg;

    $bo_table = sql_real_escape_string($bo_table);
    $wr_id = (int)$wr_id;
    $ch_id = (int)$ch_id;

    // 기존 연결 삭제
    sql_query("DELETE FROM {$mg['write_character_table']}
               WHERE bo_table = '{$bo_table}' AND wr_id = {$wr_id}");

    // 새 연결 (ch_id가 0이면 연결 없음)
    if ($ch_id > 0) {
        sql_query("INSERT INTO {$mg['write_character_table']} (bo_table, wr_id, ch_id)
                   VALUES ('{$bo_table}', {$wr_id}, {$ch_id})");
    }

    return true;
}

/**
 * 회원의 사용 가능한 캐릭터 목록 (승인된 것만)
 *
 * @param string $mb_id 회원 ID
 * @return array
 */
function mg_get_usable_characters($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $sql = "SELECT ch_id, ch_name, ch_thumb, ch_main
            FROM {$mg['character_table']}
            WHERE mb_id = '{$mb_id}' AND ch_state = 'approved'
            ORDER BY ch_main DESC, ch_name ASC";
    $result = sql_query($sql);

    $characters = array();
    while ($row = sql_fetch_array($result)) {
        $characters[] = $row;
    }
    return $characters;
}

// ======================================
// 메인 페이지 빌더 함수
// ======================================

/**
 * 메인 페이지 위젯 목록 가져오기
 *
 * @return array
 */
function mg_get_main_widgets() {
    global $mg;

    $widgets = array();

    $sql = "SELECT * FROM {$mg['main_widget_table']}
            WHERE widget_use = 1
            ORDER BY widget_order ASC";
    $result = sql_query($sql);
    while ($widget = sql_fetch_array($result)) {
        $widget['widget_config'] = $widget['widget_config'] ? json_decode($widget['widget_config'], true) : array();
        $widgets[] = $widget;
    }

    return $widgets;
}

/**
 * 메인 페이지 렌더링
 *
 * Tailwind Safelist (동적 클래스 생성용):
 * col-span-2 col-span-3 col-span-4 col-span-6 col-span-8 col-span-12
 * md:col-span-2 md:col-span-3 md:col-span-4 md:col-span-6 md:col-span-8 md:col-span-12
 *
 * @return string HTML
 */
function mg_render_main() {
    $widgets = mg_get_main_widgets();

    if (empty($widgets)) {
        return mg_render_default_main();
    }

    // 위젯 팩토리 로드
    require_once(MG_PLUGIN_PATH.'/widgets/widget.factory.php');

    $row_height = (int)mg_config('widget_row_height', 300);
    $grid_width = (int)mg_config('widget_grid_width', 1200);
    $html = '<div class="mg-main-builder grid grid-cols-12 gap-4">';

    foreach ($widgets as $widget) {
        $cols = (int)$widget['widget_cols'];
        // 컬럼별 너비 계산 및 aspect-ratio 산출
        $col_width = ($grid_width / 12) * $cols;
        $aspect_ratio = round($col_width / $row_height, 3);
        $html .= '<div class="col-span-12 md:col-span-'.$cols.'" style="aspect-ratio:'.$aspect_ratio.';overflow:hidden;">';

        // 위젯 렌더링
        $widget_instance = MG_Widget_Factory::create($widget['widget_type']);
        if ($widget_instance) {
            $html .= $widget_instance->render($widget['widget_config']);
        }

        $html .= '</div>';
    }

    $html .= '</div>';

    return $html;
}

/**
 * 기본 메인 페이지 렌더링 (위젯 없을 때)
 *
 * @return string HTML
 */
function mg_render_default_main() {
    global $config;

    ob_start();
    ?>
    <section class="card mb-6">
        <h1 class="text-2xl font-bold text-mg-accent mb-2">
            <?php echo $config['cf_title']; ?>에 오신 것을 환영합니다
        </h1>
        <p class="text-mg-text-secondary">자캐 커뮤니티를 위한 특화형 CMS - Morgan Edition</p>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card">
            <h2 class="card-header">최근 게시글</h2>
            <p class="text-sm text-mg-text-muted">게시글이 없습니다.</p>
        </div>
        <div class="card">
            <h2 class="card-header">인기 캐릭터</h2>
            <p class="text-sm text-mg-text-muted">등록된 캐릭터가 없습니다.</p>
        </div>
        <div class="card">
            <h2 class="card-header">공지사항</h2>
            <p class="text-sm text-mg-text-muted">등록된 공지사항이 없습니다.</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * 등록된 위젯 타입 목록 가져오기
 *
 * @return array
 */
function mg_get_widget_types() {
    return array(
        'text' => array(
            'name' => '텍스트',
            'desc' => '텍스트 콘텐츠 추가',
            'allowed_cols' => array(2, 3, 4, 6, 8, 12),
            'icon' => 'text'
        ),
        'image' => array(
            'name' => '이미지',
            'desc' => '이미지 추가',
            'allowed_cols' => array(2, 3, 4, 6, 8, 12),
            'icon' => 'image'
        ),
        'link_button' => array(
            'name' => '링크/버튼',
            'desc' => '클릭 가능한 링크 또는 버튼',
            'allowed_cols' => array(2, 3, 4, 6, 8, 12),
            'icon' => 'link'
        ),
        'latest' => array(
            'name' => '최신글',
            'desc' => '게시판의 최신 글 목록 표시',
            'allowed_cols' => array(3, 4, 6, 8, 12),
            'icon' => 'list'
        ),
        'notice' => array(
            'name' => '공지사항',
            'desc' => '공지 게시판 글 목록 표시',
            'allowed_cols' => array(3, 4, 6, 8, 12),
            'icon' => 'bell'
        ),
        'slider' => array(
            'name' => '슬라이더',
            'desc' => '이미지 슬라이더/캐러셀',
            'allowed_cols' => array(6, 8, 12),
            'icon' => 'image'
        )
    );
}

// ======================================
// 상점 함수
// ======================================

/**
 * 상점 카테고리 목록 가져오기
 *
 * @param bool $use_only 사용중인 것만
 * @return array
 */
function mg_get_shop_categories($use_only = true) {
    global $mg;

    $where = $use_only ? "WHERE sc_use = 1" : "";
    $sql = "SELECT * FROM {$mg['shop_category_table']} {$where} ORDER BY sc_order ASC, sc_id ASC";
    $result = sql_query($sql);

    $categories = array();
    while ($row = sql_fetch_array($result)) {
        $categories[] = $row;
    }
    return $categories;
}

/**
 * 상점 상품 목록 가져오기 (페이지네이션)
 *
 * @param int $sc_id 카테고리 ID (0=전체)
 * @param int $page 페이지
 * @param int $rows 페이지당 개수
 * @return array ['items' => array, 'total' => int, 'total_page' => int]
 */
function mg_get_shop_items($sc_id = 0, $page = 1, $rows = 12) {
    global $mg;

    $sc_id = (int)$sc_id;
    $page = max(1, (int)$page);
    $offset = ($page - 1) * $rows;

    $where = "WHERE si_display = 1 AND si_use = 1";
    if ($sc_id > 0) {
        $where .= " AND sc_id = {$sc_id}";
    }

    // 총 개수
    $sql = "SELECT COUNT(*) as cnt FROM {$mg['shop_item_table']} {$where}";
    $cnt_row = sql_fetch($sql);
    $total = (int)$cnt_row['cnt'];
    $total_page = ceil($total / $rows);

    // 목록
    $sql = "SELECT i.*, c.sc_name
            FROM {$mg['shop_item_table']} i
            LEFT JOIN {$mg['shop_category_table']} c ON i.sc_id = c.sc_id
            {$where}
            ORDER BY i.si_order ASC, i.si_id DESC
            LIMIT {$offset}, {$rows}";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $row['si_effect'] = $row['si_effect'] ? json_decode($row['si_effect'], true) : array();
        $row['status'] = mg_get_item_status($row);
        $items[] = $row;
    }

    return array('items' => $items, 'total' => $total, 'total_page' => $total_page);
}

/**
 * 타입 기반 상점 아이템 목록 조회 (카테고리 대체)
 *
 * @param array $types 타입 배열 (비어있으면 전체)
 * @param int $page 페이지
 * @param int $rows 개수
 * @return array ['items' => [], 'total' => int, 'total_page' => int]
 */
function mg_get_shop_items_by_type($types = array(), $page = 1, $rows = 12) {
    global $mg;

    $page = max(1, (int)$page);
    $offset = ($page - 1) * $rows;

    $where = "WHERE si_display = 1 AND si_use = 1";
    if (!empty($types) && is_array($types)) {
        $type_list = array_map(function($t) { return "'" . sql_real_escape_string($t) . "'"; }, $types);
        $where .= " AND si_type IN (" . implode(',', $type_list) . ")";
    }

    // 총 개수
    $sql = "SELECT COUNT(*) as cnt FROM {$mg['shop_item_table']} {$where}";
    $cnt_row = sql_fetch($sql);
    $total = (int)($cnt_row['cnt'] ?? 0);
    $total_page = ceil($total / $rows);

    // 목록
    $sql = "SELECT *
            FROM {$mg['shop_item_table']}
            {$where}
            ORDER BY si_order ASC, si_id DESC
            LIMIT {$offset}, {$rows}";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $row['si_effect'] = $row['si_effect'] ? json_decode($row['si_effect'], true) : array();
        $row['status'] = mg_get_item_status($row);
        $items[] = $row;
    }

    return array('items' => $items, 'total' => $total, 'total_page' => $total_page);
}

/**
 * 상품 상태 확인
 *
 * @param array $item 상품 정보
 * @return string selling, coming_soon, sold_out, ended
 */
function mg_get_item_status($item) {
    $now = date('Y-m-d H:i:s');

    // 판매 시작 전
    if ($item['si_sale_start'] && $item['si_sale_start'] > $now) {
        return 'coming_soon';
    }

    // 판매 종료
    if ($item['si_sale_end'] && $item['si_sale_end'] < $now) {
        return 'ended';
    }

    // 재고 소진
    if ($item['si_stock'] != -1 && $item['si_stock_sold'] >= $item['si_stock']) {
        return 'sold_out';
    }

    return 'selling';
}

/**
 * 상품 상세 가져오기
 *
 * @param int $si_id 상품 ID
 * @return array|false
 */
function mg_get_shop_item($si_id) {
    global $mg;

    $si_id = (int)$si_id;
    $sql = "SELECT i.*, c.sc_name
            FROM {$mg['shop_item_table']} i
            LEFT JOIN {$mg['shop_category_table']} c ON i.sc_id = c.sc_id
            WHERE i.si_id = {$si_id}";
    $item = sql_fetch($sql);

    if ($item) {
        $item['si_effect'] = $item['si_effect'] ? json_decode($item['si_effect'], true) : array();
        $item['status'] = mg_get_item_status($item);
    }

    return $item;
}

/**
 * 상품 구매 가능 여부 체크
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @return array ['can_buy' => bool, 'message' => string]
 */
function mg_can_buy_item($mb_id, $si_id) {
    global $mg, $member;

    $item = mg_get_shop_item($si_id);
    if (!$item) {
        return array('can_buy' => false, 'message' => '존재하지 않는 상품입니다.');
    }

    // 상태 체크
    if ($item['status'] == 'coming_soon') {
        return array('can_buy' => false, 'message' => '아직 판매 시작 전입니다.');
    }
    if ($item['status'] == 'sold_out' || $item['status'] == 'ended') {
        return array('can_buy' => false, 'message' => '품절된 상품입니다.');
    }

    // 포인트 체크
    $mb_point = (int)$member['mb_point'];
    if ($mb_point < $item['si_price']) {
        return array('can_buy' => false, 'message' => '포인트가 부족합니다.');
    }

    // 1인당 제한 체크
    if ($item['si_limit_per_user'] > 0) {
        $owned = mg_get_inventory_count($mb_id, $si_id);
        if ($owned >= $item['si_limit_per_user']) {
            return array('can_buy' => false, 'message' => '이미 최대 보유 수량에 도달했습니다.');
        }
    }

    return array('can_buy' => true, 'message' => '', 'item' => $item);
}

/**
 * 상품 구매 처리
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_buy_item($mb_id, $si_id) {
    global $mg;

    // 구매 가능 체크
    $check = mg_can_buy_item($mb_id, $si_id);
    if (!$check['can_buy']) {
        return array('success' => false, 'message' => $check['message']);
    }

    $item = $check['item'];
    $mb_id = sql_real_escape_string($mb_id);

    // 포인트 차감
    insert_point($mb_id, -$item['si_price'], '상점 구매: '.$item['si_name']);

    // 판매 수량 증가
    sql_query("UPDATE {$mg['shop_item_table']} SET si_stock_sold = si_stock_sold + 1 WHERE si_id = {$si_id}");

    // 재료 타입이면 재료 직접 지급, 아니면 인벤토리에 추가
    if ($item['si_type'] === 'material' && !empty($item['si_effect']['material_id'])) {
        $mt_id = (int)$item['si_effect']['material_id'];
        $amount = (int)($item['si_effect']['material_amount'] ?? 1);
        mg_add_material($mb_id, $mt_id, $amount);
    } else {
        mg_add_to_inventory($mb_id, $si_id);
    }

    // 구매 로그
    sql_query("INSERT INTO {$mg['shop_log_table']} (mb_id, si_id, sl_price, sl_type)
               VALUES ('{$mb_id}', {$si_id}, {$item['si_price']}, 'purchase')");

    return array('success' => true, 'message' => '구매가 완료되었습니다.', 'item' => $item);
}

/**
 * 인벤토리에 아이템 추가
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @param int $count 수량
 */
function mg_add_to_inventory($mb_id, $si_id, $count = 1) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $si_id = (int)$si_id;
    $count = (int)$count;

    $sql = "INSERT INTO {$mg['inventory_table']} (mb_id, si_id, iv_count)
            VALUES ('{$mb_id}', {$si_id}, {$count})
            ON DUPLICATE KEY UPDATE iv_count = iv_count + {$count}";
    sql_query($sql);
}

/**
 * 인벤토리 아이템 보유 수량
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @return int
 */
function mg_get_inventory_count($mb_id, $si_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $si_id = (int)$si_id;

    $sql = "SELECT iv_count FROM {$mg['inventory_table']}
            WHERE mb_id = '{$mb_id}' AND si_id = {$si_id}";
    $row = sql_fetch($sql);

    return $row ? (int)$row['iv_count'] : 0;
}

/**
 * 인벤토리 조회
 *
 * @param string $mb_id 회원 ID
 * @param int $sc_id 카테고리 ID (0=전체)
 * @return array
 */
function mg_get_inventory($mb_id, $sc_id = 0, $page = 0, $limit = 0) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $sc_id = (int)$sc_id;
    $page = (int)$page;
    $limit = (int)$limit;

    $where = "WHERE iv.mb_id = '{$mb_id}'";
    if ($sc_id > 0) {
        $where .= " AND i.sc_id = {$sc_id}";
    }

    // 페이지네이션 지원 시 총 개수 반환
    if ($page > 0 && $limit > 0) {
        $cnt_sql = "SELECT COUNT(*) as cnt
                    FROM {$mg['inventory_table']} iv
                    JOIN {$mg['shop_item_table']} i ON iv.si_id = i.si_id
                    {$where}";
        $cnt_row = sql_fetch($cnt_sql);
        $total = isset($cnt_row['cnt']) ? (int)$cnt_row['cnt'] : 0;

        $offset = ($page - 1) * $limit;
        $limit_clause = "LIMIT {$offset}, {$limit}";
    } elseif ($limit > 0) {
        // 단순 limit만
        $total = 0;
        $limit_clause = "LIMIT {$limit}";
    } else {
        $total = 0;
        $limit_clause = "";
    }

    $sql = "SELECT iv.*, i.si_name, i.si_image, i.si_type, i.si_effect, i.si_consumable, c.sc_name
            FROM {$mg['inventory_table']} iv
            JOIN {$mg['shop_item_table']} i ON iv.si_id = i.si_id
            LEFT JOIN {$mg['shop_category_table']} c ON i.sc_id = c.sc_id
            {$where}
            ORDER BY iv.iv_datetime DESC
            {$limit_clause}";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $row['si_effect'] = $row['si_effect'] ? json_decode($row['si_effect'], true) : array();
        // 사용 중인지 체크
        $row['is_active'] = mg_is_item_active($mb_id, $row['si_id']);
        $items[] = $row;
    }

    // 페이지네이션 사용 시 배열로 반환
    if ($page > 0 && $limit > 0) {
        return array('items' => $items, 'total' => $total);
    }

    return $items;
}

/**
 * 아이템 사용 중인지 확인
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @return bool
 */
function mg_is_item_active($mb_id, $si_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $si_id = (int)$si_id;

    $sql = "SELECT ia_id FROM {$mg['item_active_table']}
            WHERE mb_id = '{$mb_id}' AND si_id = {$si_id}
            LIMIT 1";
    $row = sql_fetch($sql);

    return !empty($row['ia_id']);
}

/**
 * 아이템 사용 (장착)
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @param int $ch_id 캐릭터 ID (캐릭터별 적용 시)
 * @return array ['success' => bool, 'message' => string]
 */
function mg_use_item($mb_id, $si_id, $ch_id = null) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $si_id = (int)$si_id;

    // 보유 확인
    $owned = mg_get_inventory_count($mb_id, $si_id);
    if ($owned <= 0) {
        return array('success' => false, 'message' => '보유하지 않은 아이템입니다.');
    }

    // 상품 정보
    $item = mg_get_shop_item($si_id);
    if (!$item) {
        return array('success' => false, 'message' => '존재하지 않는 상품입니다.');
    }

    // 이미 사용중인지 확인
    if (mg_is_item_active($mb_id, $si_id)) {
        return array('success' => false, 'message' => '이미 사용 중인 아이템입니다.');
    }

    // 같은 타입의 다른 아이템 해제 (칭호, 닉네임색상 등은 하나만)
    $exclusive_types = array('title', 'nick_color', 'nick_effect');
    if (in_array($item['si_type'], $exclusive_types)) {
        sql_query("DELETE FROM {$mg['item_active_table']}
                   WHERE mb_id = '{$mb_id}' AND ia_type = '{$item['si_type']}'");
    }

    // 아이템 적용
    $ch_id_sql = $ch_id ? (int)$ch_id : 'NULL';
    sql_query("INSERT INTO {$mg['item_active_table']} (mb_id, si_id, ia_type, ch_id)
               VALUES ('{$mb_id}', {$si_id}, '{$item['si_type']}', {$ch_id_sql})");

    // 소모품이면 수량 감소
    if ($item['si_consumable']) {
        sql_query("UPDATE {$mg['inventory_table']}
                   SET iv_count = iv_count - 1
                   WHERE mb_id = '{$mb_id}' AND si_id = {$si_id}");
        // 수량 0이면 삭제
        sql_query("DELETE FROM {$mg['inventory_table']}
                   WHERE mb_id = '{$mb_id}' AND si_id = {$si_id} AND iv_count <= 0");
    }

    return array('success' => true, 'message' => '아이템을 사용했습니다.');
}

/**
 * 아이템 해제
 *
 * @param string $mb_id 회원 ID
 * @param int $si_id 상품 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_unuse_item($mb_id, $si_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $si_id = (int)$si_id;

    sql_query("DELETE FROM {$mg['item_active_table']}
               WHERE mb_id = '{$mb_id}' AND si_id = {$si_id}");

    return array('success' => true, 'message' => '아이템 사용을 해제했습니다.');
}

/**
 * 사용 중인 아이템 조회
 *
 * @param string $mb_id 회원 ID
 * @param string $type 타입 필터 (null=전체)
 * @return array
 */
function mg_get_active_items($mb_id, $type = null) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);

    $where = "WHERE ia.mb_id = '{$mb_id}'";
    if ($type) {
        $type = sql_real_escape_string($type);
        $where .= " AND ia.ia_type = '{$type}'";
    }

    $sql = "SELECT ia.*, i.si_name, i.si_image, i.si_type, i.si_effect
            FROM {$mg['item_active_table']} ia
            JOIN {$mg['shop_item_table']} i ON ia.si_id = i.si_id
            {$where}";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $row['si_effect'] = $row['si_effect'] ? json_decode($row['si_effect'], true) : array();
        $items[] = $row;
    }

    return $items;
}

/**
 * 선물 보내기
 *
 * @param string $mb_id_from 보내는 사람
 * @param string $mb_id_to 받는 사람
 * @param int $si_id 상품 ID
 * @param string $message 메시지
 * @return array ['success' => bool, 'message' => string]
 */
function mg_send_gift($mb_id_from, $mb_id_to, $si_id, $message = '') {
    global $mg, $member;

    // 자기 자신에게 선물 불가
    if ($mb_id_from == $mb_id_to) {
        return array('success' => false, 'message' => '자기 자신에게는 선물할 수 없습니다.');
    }

    // 받는 사람 확인
    $to_member = get_member($mb_id_to);
    if (!$to_member['mb_id']) {
        return array('success' => false, 'message' => '받는 사람을 찾을 수 없습니다.');
    }

    // 구매 가능 체크 (포인트, 재고 등)
    $check = mg_can_buy_item($mb_id_from, $si_id);
    if (!$check['can_buy']) {
        return array('success' => false, 'message' => $check['message']);
    }

    $item = $check['item'];
    $mb_id_from = sql_real_escape_string($mb_id_from);
    $mb_id_to = sql_real_escape_string($mb_id_to);
    $message = sql_real_escape_string(mb_substr($message, 0, 200));

    // 포인트 차감
    insert_point($mb_id_from, -$item['si_price'], '선물 보내기: '.$item['si_name'].' → '.$mb_id_to);

    // 판매 수량 증가
    sql_query("UPDATE {$mg['shop_item_table']} SET si_stock_sold = si_stock_sold + 1 WHERE si_id = {$si_id}");

    // 선물 레코드 생성
    sql_query("INSERT INTO {$mg['gift_table']} (mb_id_from, mb_id_to, si_id, gf_message, gf_status)
               VALUES ('{$mb_id_from}', '{$mb_id_to}', {$si_id}, '{$message}', 'pending')");

    // 구매 로그
    sql_query("INSERT INTO {$mg['shop_log_table']} (mb_id, si_id, sl_price, sl_type)
               VALUES ('{$mb_id_from}', {$si_id}, {$item['si_price']}, 'gift_send')");

    // 알림 전송
    mg_notify($mb_id_to, 'gift_received', '선물이 도착했습니다',
              $mb_id_from.'님이 '.$item['si_name'].'을(를) 선물로 보냈습니다.',
              G5_BBS_URL.'/gift.php');

    return array('success' => true, 'message' => '선물을 보냈습니다.');
}

/**
 * 선물 수락
 *
 * @param int $gf_id 선물 ID
 * @param string $mb_id 수락자 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_accept_gift($gf_id, $mb_id) {
    global $mg;

    $gf_id = (int)$gf_id;
    $mb_id = sql_real_escape_string($mb_id);

    // 선물 조회
    $sql = "SELECT g.*, i.si_name FROM {$mg['gift_table']} g
            JOIN {$mg['shop_item_table']} i ON g.si_id = i.si_id
            WHERE g.gf_id = {$gf_id} AND g.mb_id_to = '{$mb_id}' AND g.gf_status = 'pending'";
    $gift = sql_fetch($sql);

    if (!$gift) {
        return array('success' => false, 'message' => '선물을 찾을 수 없습니다.');
    }

    // 상태 변경
    sql_query("UPDATE {$mg['gift_table']} SET gf_status = 'accepted' WHERE gf_id = {$gf_id}");

    // 인벤토리에 추가
    mg_add_to_inventory($mb_id, $gift['si_id']);

    // 로그
    sql_query("INSERT INTO {$mg['shop_log_table']} (mb_id, si_id, sl_price, sl_type)
               VALUES ('{$mb_id}', {$gift['si_id']}, 0, 'gift_receive')");

    return array('success' => true, 'message' => '선물을 수락했습니다.');
}

/**
 * 선물 거절
 *
 * @param int $gf_id 선물 ID
 * @param string $mb_id 거절자 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_reject_gift($gf_id, $mb_id) {
    global $mg;

    $gf_id = (int)$gf_id;
    $mb_id = sql_real_escape_string($mb_id);

    // 선물 조회
    $sql = "SELECT g.*, i.si_name, i.si_price FROM {$mg['gift_table']} g
            JOIN {$mg['shop_item_table']} i ON g.si_id = i.si_id
            WHERE g.gf_id = {$gf_id} AND g.mb_id_to = '{$mb_id}' AND g.gf_status = 'pending'";
    $gift = sql_fetch($sql);

    if (!$gift) {
        return array('success' => false, 'message' => '선물을 찾을 수 없습니다.');
    }

    // 상태 변경
    sql_query("UPDATE {$mg['gift_table']} SET gf_status = 'rejected' WHERE gf_id = {$gf_id}");

    // 포인트 환불
    insert_point($gift['mb_id_from'], $gift['si_price'], '선물 거절 환불: '.$gift['si_name']);

    // 판매 수량 감소
    sql_query("UPDATE {$mg['shop_item_table']} SET si_stock_sold = si_stock_sold - 1 WHERE si_id = {$gift['si_id']}");

    return array('success' => true, 'message' => '선물을 거절했습니다.');
}

/**
 * 받은 선물 목록 (대기 중)
 *
 * @param string $mb_id 회원 ID
 * @return array
 */
function mg_get_pending_gifts($mb_id, $limit = 0) {
    global $mg, $g5;

    $mb_id = sql_real_escape_string($mb_id);
    $limit = (int)$limit;

    $limit_clause = $limit > 0 ? "LIMIT {$limit}" : "";

    $sql = "SELECT g.*, i.si_name, i.si_image, i.si_type, m.mb_nick as from_nick
            FROM {$mg['gift_table']} g
            LEFT JOIN {$mg['shop_item_table']} i ON g.si_id = i.si_id
            LEFT JOIN {$g5['member_table']} m ON g.mb_id_from = m.mb_id
            WHERE g.mb_id_to = '{$mb_id}' AND g.gf_status = 'pending'
            ORDER BY g.gf_datetime DESC
            {$limit_clause}";
    $result = sql_query($sql);

    $gifts = array();
    while ($row = sql_fetch_array($result)) {
        $gifts[] = $row;
    }

    return $gifts;
}

/**
 * 닉네임 렌더링 (칭호, 색상, 효과 적용)
 *
 * @param string $mb_id 회원 ID
 * @param string $mb_nick 닉네임
 * @return string HTML
 */
function mg_render_nickname($mb_id, $mb_nick) {
    static $cache = array();

    if (isset($cache[$mb_id])) {
        $active = $cache[$mb_id];
    } else {
        $active = mg_get_active_items($mb_id);
        $cache[$mb_id] = $active;
    }

    $prefix = '';
    $suffix = '';
    $style = '';
    $class = '';

    foreach ($active as $item) {
        $effect = $item['si_effect'];

        switch ($item['si_type']) {
            case 'title':
                if (isset($effect['position']) && $effect['position'] == 'suffix') {
                    $color = isset($effect['color']) ? $effect['color'] : '#FFD700';
                    $suffix = ' <span style="color:'.$color.'">'.$effect['text'].'</span>';
                } else {
                    $color = isset($effect['color']) ? $effect['color'] : '#FFD700';
                    $prefix = '<span style="color:'.$color.'">'.$effect['text'].'</span> ';
                }
                break;

            case 'nick_color':
                if (isset($effect['color'])) {
                    $style .= 'color:'.$effect['color'].';';
                }
                break;

            case 'nick_effect':
                if (isset($effect['effect'])) {
                    switch ($effect['effect']) {
                        case 'bold':
                            $style .= 'font-weight:bold;';
                            break;
                        case 'shadow':
                            $color = isset($effect['value']) ? $effect['value'] : '#000000';
                            $style .= 'text-shadow:1px 1px 2px '.$color.';';
                            break;
                        case 'glow':
                            $color = isset($effect['value']) ? $effect['value'] : '#f59f0a';
                            $style .= 'text-shadow:0 0 5px '.$color.';';
                            break;
                    }
                }
                break;
        }
    }

    $nick_html = htmlspecialchars($mb_nick);
    if ($style) {
        $nick_html = '<span style="'.$style.'">'.$nick_html.'</span>';
    }

    return $prefix . $nick_html . $suffix;
}

/**
 * 설정값 가져오기 (mg_config 별칭)
 *
 * @param string $key 설정 키
 * @param mixed $default 기본값
 * @return mixed
 */
function mg_get_config($key, $default = null) {
    return mg_config($key, $default);
}

/**
 * 설정값 저장 (mg_config_set 별칭)
 *
 * @param string $key 설정 키
 * @param mixed $value 값
 */
function mg_set_config($key, $value) {
    return mg_config_set($key, $value);
}

/**
 * 아이콘 렌더링 (Heroicons 이름 또는 이미지 URL)
 *
 * @param string $icon 아이콘명 또는 이미지 URL
 * @param string $class CSS 클래스
 * @param string $alt 대체 텍스트 (이미지용)
 * @return string HTML
 */
function mg_icon($icon, $class = 'w-5 h-5', $alt = '') {
    if (!$icon) {
        return '';
    }

    // 이미지 URL인 경우
    if (strpos($icon, '/') !== false || strpos($icon, 'http') === 0) {
        return '<img src="' . htmlspecialchars($icon) . '" alt="' . htmlspecialchars($alt) . '" class="' . $class . ' inline-block object-contain">';
    }

    // Heroicons (outline) - 자주 사용되는 아이콘들
    $heroicons = array(
        // 기본 아이콘
        'star' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
        'heart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
        'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        'gift' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>',
        'fire' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/>',
        'bolt' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
        'crown' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5l3 3 6-6 6 6 3-3v12H3V5z"/>',
        'badge' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'user' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>',
        'trophy' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>',
        'cube' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'face-smile' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'square' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4h16v16H4z"/>',
        // 상점/시설 관련
        'shopping-cart' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'shopping-bag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>',
        'building-office' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>',
        'building-storefront' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/>',
        'home' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'home-modern' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205l3 1m1.5.5l-1.5-.5M6.75 7.364V3h-3v18m3-13.636l10.5-3.819"/>',
        // 엔터테인먼트
        'film' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>',
        'musical-note' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>',
        'ticket' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>',
        // 커뮤니케이션
        'chat-bubble-left' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 12.76c0 1.6 1.123 2.994 2.707 3.227 1.068.157 2.148.279 3.238.364.466.037.893.281 1.153.671L12 21l2.652-3.978c.26-.39.687-.634 1.153-.67 1.09-.086 2.17-.208 3.238-.365 1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>',
        'envelope' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>',
        // 문서/교육
        'book-open' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>',
        'academic-cap' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>',
        'clipboard-document-list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>',
        // 도구/설정
        'cog-6-tooth' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'wrench-screwdriver' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z"/>',
        // 자연/환경
        'sun' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>',
        'moon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>',
        // 기타
        'puzzle-piece' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z"/>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>',
        'map' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 6.75V15m0-8.25L3 3v12l6 3.75m0-14.25l6-3.75M9 15l6 3.75m0-14.25v13.5m0-13.5L21 3v12l-6 3.75"/>',
        'flag' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3v1.5M3 21v-6m0 0l2.77-.693a9 9 0 016.208.682l.108.054a9 9 0 006.086.71l3.114-.732a48.524 48.524 0 01-.005-10.499l-3.11.732a9 9 0 01-6.085-.711l-.108-.054a9 9 0 00-6.208-.682L3 4.5M3 15V4.5"/>',
        'beaker' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>',
        'key' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/>',
        'lock-closed' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>',
        'lock-open' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>',
        // 재료 아이콘
        'squares-2x2' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
        'rectangle-stack' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 6.878V6a2.25 2.25 0 012.25-2.25h7.5A2.25 2.25 0 0118 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 004.5 9v.878m13.5-3A2.25 2.25 0 0119.5 9v.878m0 0a2.246 2.246 0 00-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0121 12v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6c0-.98.626-1.813 1.5-2.122"/>',
        'archive-box' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
        'swatch' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z"/>',
        'paint-brush' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>',
        'scissors' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.848 8.25l1.536.887M7.848 8.25a3 3 0 11-5.196-3 3 3 0 015.196 3zm1.536.887a2.165 2.165 0 011.083 1.839c.005.351.054.695.14 1.024M9.384 9.137l2.077 1.199M7.848 15.75l1.536-.887m-1.536.887a3 3 0 11-5.196 3 3 3 0 015.196-3zm1.536-.887a2.165 2.165 0 001.083-1.838c.005-.352.054-.695.14-1.025m-1.223 2.863l2.077-1.199m0-3.328a4.323 4.323 0 012.068-1.379l5.325-1.628a4.5 4.5 0 012.48-.044l.803.215-7.794 4.5m-2.882-1.664A4.331 4.331 0 0010.607 12m3.736 0l7.794 4.5-.802.215a4.5 4.5 0 01-2.48-.043l-5.326-1.629a4.324 4.324 0 01-2.068-1.379M14.343 12l-2.882 1.664"/>',
        // 위젯 빌더용 아이콘
        'photo' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>',
        'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>',
        'link' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>',
        'queue-list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/>',
        'bell' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>',
        'pencil-square' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>',
        'arrows-up-down' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5"/>',
    );

    // 등록된 아이콘이면 SVG 반환
    if (isset($heroicons[$icon])) {
        return '<svg class="' . $class . '" fill="none" stroke="currentColor" viewBox="0 0 24 24">' . $heroicons[$icon] . '</svg>';
    }

    // 알 수 없는 아이콘은 기본 아이콘 또는 텍스트 반환
    return '<span class="text-xs">' . htmlspecialchars($icon) . '</span>';
}

/**
 * 아이콘 렌더링 (mg_icon 별칭, heroicons 호환)
 */
function mg_heroicon($icon, $class = 'w-5 h-5') {
    return mg_icon($icon, $class);
}

/**
 * 포인트 포맷팅 (재화 명칭 적용)
 * @param int $point 포인트
 * @param bool $with_sign 부호 표시 여부
 * @return string 포맷된 문자열 (예: "1,000 G", "+100 골드")
 */
function mg_point_format($point, $with_sign = false) {
    $point_name = mg_config('point_name', 'G');
    $point_unit = mg_config('point_unit', '');

    $formatted = number_format($point);
    if ($with_sign && $point > 0) {
        $formatted = '+' . $formatted;
    }

    return $formatted . ' ' . $point_name . $point_unit;
}

/**
 * 재화 명칭만 반환
 * @return string 재화 명칭 (예: "G", "골드")
 */
function mg_point_name() {
    $point_name = mg_config('point_name', 'G');
    $point_unit = mg_config('point_unit', '');
    return $point_name . $point_unit;
}

// ======================================
// 역극(RP) 함수
// ======================================

/**
 * 역극 스레드 조회
 *
 * @param int $rt_id 역극 ID
 * @return array|false
 */
function mg_get_rp_thread($rt_id) {
    global $mg, $g5;

    $rt_id = (int)$rt_id;
    $sql = "SELECT t.*, m.mb_nick, c.ch_name, c.ch_thumb
            FROM {$mg['rp_thread_table']} t
            LEFT JOIN {$g5['member_table']} m ON t.mb_id = m.mb_id
            LEFT JOIN {$mg['character_table']} c ON t.ch_id = c.ch_id
            WHERE t.rt_id = {$rt_id}";
    return sql_fetch($sql);
}

/**
 * 역극 목록 조회 (페이지네이션)
 *
 * @param string $status 상태 필터 (open/closed/all)
 * @param string $mb_id 회원 ID 필터 (내 역극)
 * @param int $page 페이지
 * @param int $rows 페이지당 개수
 * @return array ['threads' => array, 'total' => int, 'total_page' => int]
 */
function mg_get_rp_threads($status = 'all', $mb_id = '', $page = 1, $rows = 20) {
    global $mg, $g5;

    $page = max(1, (int)$page);
    $offset = ($page - 1) * $rows;

    $where = "WHERE t.rt_status != 'deleted'";
    if ($status == 'open') {
        $where .= " AND t.rt_status = 'open'";
    } elseif ($status == 'closed') {
        $where .= " AND t.rt_status = 'closed'";
    }

    if ($mb_id) {
        $mb_id_esc = sql_real_escape_string($mb_id);
        $where .= " AND (t.mb_id = '{$mb_id_esc}' OR t.rt_id IN (
            SELECT rt_id FROM {$mg['rp_member_table']} WHERE mb_id = '{$mb_id_esc}'
        ))";
    }

    // 총 개수
    $sql = "SELECT COUNT(*) as cnt FROM {$mg['rp_thread_table']} t {$where}";
    $cnt_row = sql_fetch($sql);
    $total = (int)$cnt_row['cnt'];
    $total_page = ceil($total / $rows);

    // 목록
    $sql = "SELECT t.*, m.mb_nick, c.ch_name, c.ch_thumb,
                   (SELECT COUNT(*) FROM {$mg['rp_member_table']} WHERE rt_id = t.rt_id) as member_count
            FROM {$mg['rp_thread_table']} t
            LEFT JOIN {$g5['member_table']} m ON t.mb_id = m.mb_id
            LEFT JOIN {$mg['character_table']} c ON t.ch_id = c.ch_id
            {$where}
            ORDER BY t.rt_update DESC
            LIMIT {$offset}, {$rows}";
    $result = sql_query($sql);

    $threads = array();
    while ($row = sql_fetch_array($result)) {
        $threads[] = $row;
    }

    return array('threads' => $threads, 'total' => $total, 'total_page' => $total_page);
}

/**
 * 역극 생성
 *
 * @param array $data [rt_title, rt_content, rt_image, mb_id, ch_id, rt_max_member]
 * @return array ['success' => bool, 'message' => string, 'rt_id' => int]
 */
function mg_create_rp_thread($data) {
    global $mg;

    $mb_id = sql_real_escape_string($data['mb_id']);
    $ch_id = (int)$data['ch_id'];
    $title = sql_real_escape_string($data['rt_title']);
    $content = sql_real_escape_string($data['rt_content']);
    $image = isset($data['rt_image']) ? sql_real_escape_string($data['rt_image']) : '';
    $max_member = isset($data['rt_max_member']) ? (int)$data['rt_max_member'] : 0;

    // 최소 글자 수 체크
    $min_len = (int)mg_config('rp_content_min', 20);
    if (mb_strlen(strip_tags($data['rt_content'])) < $min_len) {
        return array('success' => false, 'message' => "내용을 {$min_len}자 이상 입력해주세요.");
    }

    // 참여자 상한 체크
    $max_limit = (int)mg_config('rp_max_member_limit', 20);
    if ($max_member > $max_limit) {
        $max_member = $max_limit;
    }

    $sql = "INSERT INTO {$mg['rp_thread_table']}
            (rt_title, rt_content, rt_image, mb_id, ch_id, rt_max_member)
            VALUES ('{$title}', '{$content}', '{$image}', '{$mb_id}', {$ch_id}, {$max_member})";
    sql_query($sql);
    $rt_id = sql_insert_id();

    // 판장을 참여자로 추가
    sql_query("INSERT INTO {$mg['rp_member_table']} (rt_id, mb_id, ch_id)
               VALUES ({$rt_id}, '{$mb_id}', {$ch_id})");

    return array('success' => true, 'message' => '역극이 생성되었습니다.', 'rt_id' => $rt_id);
}

/**
 * 역극 이음 목록 조회
 *
 * @param int $rt_id 역극 ID
 * @param int $page 페이지 (0=전체)
 * @param int $rows 페이지당 개수
 * @return array
 */
function mg_get_rp_replies($rt_id, $page = 0, $rows = 50) {
    global $mg, $g5;

    $rt_id = (int)$rt_id;

    $limit_clause = '';
    if ($page > 0 && $rows > 0) {
        $offset = ($page - 1) * $rows;
        $limit_clause = "LIMIT {$offset}, {$rows}";
    }

    $sql = "SELECT r.*, m.mb_nick, c.ch_name, c.ch_thumb
            FROM {$mg['rp_reply_table']} r
            LEFT JOIN {$g5['member_table']} m ON r.mb_id = m.mb_id
            LEFT JOIN {$mg['character_table']} c ON r.ch_id = c.ch_id
            WHERE r.rt_id = {$rt_id}
            ORDER BY r.rr_id ASC
            {$limit_clause}";
    $result = sql_query($sql);

    $replies = array();
    while ($row = sql_fetch_array($result)) {
        $replies[] = $row;
    }

    return $replies;
}

/**
 * 역극 이음 작성
 *
 * @param array $data [rt_id, rr_content, rr_image, mb_id, ch_id]
 * @return array ['success' => bool, 'message' => string, 'reply' => array]
 */
function mg_create_rp_reply($data) {
    global $mg, $g5;

    $rt_id = (int)$data['rt_id'];
    $mb_id = sql_real_escape_string($data['mb_id']);
    $ch_id = (int)$data['ch_id'];
    $content = sql_real_escape_string($data['rr_content']);
    $image = isset($data['rr_image']) ? sql_real_escape_string($data['rr_image']) : '';

    // 역극 존재/상태 체크
    $thread = sql_fetch("SELECT * FROM {$mg['rp_thread_table']} WHERE rt_id = {$rt_id}");
    if (!$thread || $thread['rt_status'] != 'open') {
        return array('success' => false, 'message' => '이음할 수 없는 역극입니다.');
    }

    // 최소 글자 수 체크
    $min_len = (int)mg_config('rp_content_min', 20);
    if (mb_strlen(strip_tags($data['rr_content'])) < $min_len) {
        return array('success' => false, 'message' => "내용을 {$min_len}자 이상 입력해주세요.");
    }

    // 이음 삽입
    $sql = "INSERT INTO {$mg['rp_reply_table']}
            (rt_id, rr_content, rr_image, mb_id, ch_id)
            VALUES ({$rt_id}, '{$content}', '{$image}', '{$mb_id}', {$ch_id})";
    sql_query($sql);
    $rr_id = sql_insert_id();

    // 역극 업데이트 (이음수, 최근활동일)
    sql_query("UPDATE {$mg['rp_thread_table']}
               SET rt_reply_count = rt_reply_count + 1, rt_update = NOW()
               WHERE rt_id = {$rt_id}");

    // 참여자 등록/업데이트
    sql_query("INSERT INTO {$mg['rp_member_table']} (rt_id, mb_id, ch_id, rm_reply_count)
               VALUES ({$rt_id}, '{$mb_id}', {$ch_id}, 1)
               ON DUPLICATE KEY UPDATE rm_reply_count = rm_reply_count + 1, ch_id = {$ch_id}");

    // 이음 정보 반환용 조회
    $reply = sql_fetch("SELECT r.*, m.mb_nick, c.ch_name, c.ch_thumb
                         FROM {$mg['rp_reply_table']} r
                         LEFT JOIN {$g5['member_table']} m ON r.mb_id = m.mb_id
                         LEFT JOIN {$mg['character_table']} c ON r.ch_id = c.ch_id
                         WHERE r.rr_id = {$rr_id}");

    return array('success' => true, 'message' => '이음이 작성되었습니다.', 'reply' => $reply);
}

/**
 * 역극 참여자 목록 조회
 *
 * @param int $rt_id 역극 ID
 * @return array
 */
function mg_get_rp_members($rt_id) {
    global $mg, $g5;

    $rt_id = (int)$rt_id;
    $sql = "SELECT rm.*, m.mb_nick, c.ch_name, c.ch_thumb
            FROM {$mg['rp_member_table']} rm
            LEFT JOIN {$g5['member_table']} m ON rm.mb_id = m.mb_id
            LEFT JOIN {$mg['character_table']} c ON rm.ch_id = c.ch_id
            WHERE rm.rt_id = {$rt_id}
            ORDER BY rm.rm_datetime ASC";
    $result = sql_query($sql);

    $members = array();
    while ($row = sql_fetch_array($result)) {
        $members[] = $row;
    }

    return $members;
}

/**
 * 역극 참여 가능 여부
 *
 * @param int $rt_id 역극 ID
 * @param string $mb_id 회원 ID
 * @return array ['can_join' => bool, 'message' => string]
 */
function mg_can_join_rp($rt_id, $mb_id) {
    global $mg;

    $rt_id = (int)$rt_id;
    $mb_id_esc = sql_real_escape_string($mb_id);

    $thread = sql_fetch("SELECT * FROM {$mg['rp_thread_table']} WHERE rt_id = {$rt_id}");
    if (!$thread || $thread['rt_status'] != 'open') {
        return array('can_join' => false, 'message' => '참여할 수 없는 역극입니다.');
    }

    // 이미 참여 중인지
    $exists = sql_fetch("SELECT rm_id FROM {$mg['rp_member_table']}
                          WHERE rt_id = {$rt_id} AND mb_id = '{$mb_id_esc}'");
    if ($exists['rm_id']) {
        return array('can_join' => true, 'message' => '이미 참여 중입니다.', 'already_joined' => true);
    }

    // 최대 참여자 체크
    if ($thread['rt_max_member'] > 0) {
        $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$mg['rp_member_table']} WHERE rt_id = {$rt_id}");
        if ((int)$cnt['cnt'] >= $thread['rt_max_member']) {
            return array('can_join' => false, 'message' => '참여자 수가 초과되었습니다.');
        }
    }

    return array('can_join' => true, 'message' => '');
}

/**
 * 판 세우기 가능 여부
 *
 * @param string $mb_id 회원 ID
 * @return array ['can_create' => bool, 'message' => string]
 */
function mg_can_create_rp($mb_id) {
    global $mg;

    // 역극 기능 사용 여부
    if (!mg_config('rp_use', '1')) {
        return array('can_create' => false, 'message' => '역극 기능이 비활성화되어 있습니다.');
    }

    // 이전 이음 수 조건
    $require = (int)mg_config('rp_require_reply', 0);
    if ($require > 0) {
        $mb_id_esc = sql_real_escape_string($mb_id);
        $reply_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$mg['rp_reply_table']} WHERE mb_id = '{$mb_id_esc}'");
        if ((int)$reply_cnt['cnt'] < $require) {
            return array('can_create' => false, 'message' => "판을 세우려면 다른 역극에 {$require}회 이상 이어야 합니다.");
        }
    }

    return array('can_create' => true, 'message' => '');
}

// ======================================
// 이모티콘 시스템
// ======================================

/**
 * 이모티콘 셋 목록 조회
 *
 * @param string $status 상태 필터 (all, draft, pending, approved, rejected)
 * @param int $page 페이지
 * @param int $rows 페이지당 수
 * @param string $creator_id 제작자 필터 (빈 문자열=전체)
 * @return array ['items' => array, 'total' => int, 'total_page' => int]
 */
function mg_get_emoticon_sets($status = 'approved', $page = 1, $rows = 12, $sort = 'latest', $creator_id = '') {
    global $mg;

    $page = max(1, (int)$page);
    $offset = ($page - 1) * $rows;

    $where = "WHERE 1";
    if ($status !== 'all') {
        $where .= " AND es_status = '".sql_real_escape_string($status)."'";
    }
    if ($creator_id !== '') {
        $where .= " AND es_creator_id = '".sql_real_escape_string($creator_id)."'";
    }
    if ($sort === 'free') {
        $where .= " AND es_price = 0";
    }
    // 판매중인 셋만 (status=approved 이고 es_use=1)
    if ($status === 'approved') {
        $where .= " AND es_use = 1";
    }

    $sql = "SELECT COUNT(*) as cnt FROM {$mg['emoticon_set_table']} {$where}";
    $cnt_row = sql_fetch($sql);
    $total = (int)$cnt_row['cnt'];
    $total_page = $rows > 0 ? ceil($total / $rows) : 1;

    // 정렬
    $order_by = "es.es_order ASC, es.es_id DESC";
    if ($sort === 'popular') {
        $order_by = "es.es_sales_count DESC, es.es_id DESC";
    }

    $sql = "SELECT es.*, (SELECT COUNT(*) FROM {$mg['emoticon_table']} WHERE es_id = es.es_id) as em_count
            FROM {$mg['emoticon_set_table']} es
            {$where}
            ORDER BY {$order_by}
            LIMIT {$offset}, {$rows}";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
    }

    return array('items' => $items, 'total' => $total, 'total_page' => $total_page);
}

/**
 * 이모티콘 셋 상세 조회
 *
 * @param int $es_id 셋 ID
 * @return array|false
 */
function mg_get_emoticon_set($es_id) {
    global $mg;

    $es_id = (int)$es_id;
    $sql = "SELECT es.*, (SELECT COUNT(*) FROM {$mg['emoticon_table']} WHERE es_id = es.es_id) as em_count
            FROM {$mg['emoticon_set_table']} es
            WHERE es.es_id = {$es_id}";
    $row = sql_fetch($sql);

    return $row && $row['es_id'] ? $row : false;
}

/**
 * 셋 내 이모티콘 목록
 *
 * @param int $es_id 셋 ID
 * @return array
 */
function mg_get_emoticons($es_id) {
    global $mg;

    $es_id = (int)$es_id;
    $sql = "SELECT * FROM {$mg['emoticon_table']}
            WHERE es_id = {$es_id}
            ORDER BY em_order ASC, em_id ASC";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
    }
    return $items;
}

/**
 * 내가 보유한 이모티콘 셋 목록
 *
 * @param string $mb_id 회원 ID
 * @return array
 */
function mg_get_my_emoticon_sets($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $sql = "SELECT es.*, eo.eo_datetime as own_datetime,
                   (SELECT COUNT(*) FROM {$mg['emoticon_table']} WHERE es_id = es.es_id) as em_count
            FROM {$mg['emoticon_own_table']} eo
            JOIN {$mg['emoticon_set_table']} es ON eo.es_id = es.es_id
            WHERE eo.mb_id = '{$mb_id}' AND es.es_use = 1
            ORDER BY eo.eo_datetime DESC";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
    }
    return $items;
}

/**
 * 보유한 셋의 전체 이모티콘 (피커용)
 *
 * @param string $mb_id 회원 ID
 * @return array [es_id => ['set' => ..., 'emoticons' => [...]]]
 */
function mg_get_my_emoticons($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);

    // 보유 셋 조회
    $sql = "SELECT es.*
            FROM {$mg['emoticon_own_table']} eo
            JOIN {$mg['emoticon_set_table']} es ON eo.es_id = es.es_id
            WHERE eo.mb_id = '{$mb_id}' AND es.es_use = 1
            ORDER BY eo.eo_datetime ASC";
    $result = sql_query($sql);

    $sets = array();
    while ($row = sql_fetch_array($result)) {
        $es_id = $row['es_id'];
        $sets[$es_id] = array(
            'set' => $row,
            'emoticons' => mg_get_emoticons($es_id)
        );
    }

    return $sets;
}

/**
 * 이모티콘 셋 보유 여부 확인
 *
 * @param string $mb_id 회원 ID
 * @param int $es_id 셋 ID
 * @return bool
 */
function mg_owns_emoticon_set($mb_id, $es_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $es_id = (int)$es_id;

    $sql = "SELECT eo_id FROM {$mg['emoticon_own_table']}
            WHERE mb_id = '{$mb_id}' AND es_id = {$es_id} LIMIT 1";
    $row = sql_fetch($sql);

    return !empty($row['eo_id']);
}

/**
 * 이모티콘 셋 구매 (수수료 처리 포함)
 *
 * @param string $mb_id 구매자 회원 ID
 * @param int $es_id 셋 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_buy_emoticon_set($mb_id, $es_id) {
    global $mg, $member;

    $es_id = (int)$es_id;
    $set = mg_get_emoticon_set($es_id);

    if (!$set) {
        return array('success' => false, 'message' => '존재하지 않는 이모티콘 셋입니다.');
    }

    if ($set['es_status'] !== 'approved' || !$set['es_use']) {
        return array('success' => false, 'message' => '구매할 수 없는 이모티콘 셋입니다.');
    }

    // 이미 보유
    if (mg_owns_emoticon_set($mb_id, $es_id)) {
        return array('success' => false, 'message' => '이미 보유한 이모티콘 셋입니다.');
    }

    // 포인트 확인
    $price = (int)$set['es_price'];
    if ($price > 0) {
        $mb_point = (int)$member['mb_point'];
        if ($mb_point < $price) {
            return array('success' => false, 'message' => '포인트가 부족합니다.');
        }

        // 구매자 포인트 차감
        insert_point($mb_id, -$price, '이모티콘 구매: '.$set['es_name']);

        // 크리에이터가 있으면 수수료 처리
        if ($set['es_creator_id']) {
            $commission_rate = (int)mg_config('emoticon_commission_rate', 10);
            $commission = (int)floor($price * $commission_rate / 100);
            $creator_revenue = $price - $commission;

            if ($creator_revenue > 0) {
                insert_point($set['es_creator_id'], $creator_revenue, '이모티콘 판매 수익: '.$set['es_name']);
            }
        }
    }

    // 보유 추가
    $mb_id_esc = sql_real_escape_string($mb_id);
    sql_query("INSERT INTO {$mg['emoticon_own_table']} (mb_id, es_id) VALUES ('{$mb_id_esc}', {$es_id})");

    // 판매 통계 업데이트
    sql_query("UPDATE {$mg['emoticon_set_table']}
               SET es_sales_count = es_sales_count + 1,
                   es_total_revenue = es_total_revenue + {$price}
               WHERE es_id = {$es_id}");

    return array('success' => true, 'message' => '이모티콘 셋을 구매했습니다.');
}

/**
 * 콘텐츠 내 이모티콘 코드를 이미지로 변환
 *
 * @param string $content 원본 콘텐츠
 * @return string 변환된 콘텐츠
 */
function mg_render_emoticons($content) {
    global $mg;
    static $emoticon_map = null;

    if ($emoticon_map === null) {
        $emoticon_map = mg_get_all_emoticon_codes();
    }

    if (empty($emoticon_map)) {
        return $content;
    }

    return preg_replace_callback('/:([a-zA-Z0-9_]+):/', function($m) use ($emoticon_map) {
        $code = ':' . $m[1] . ':';
        if (isset($emoticon_map[$code])) {
            $img = htmlspecialchars($emoticon_map[$code], ENT_QUOTES, 'UTF-8');
            $alt = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            return '<img src="'.$img.'" alt="'.$alt.'" class="mg-emoticon" loading="lazy">';
        }
        return $m[0];
    }, $content);
}

/**
 * 전체 이모티콘 코드-이미지 맵 (렌더링용 캐시)
 *
 * @return array [':code:' => 'image_url', ...]
 */
function mg_get_all_emoticon_codes() {
    global $mg;

    $sql = "SELECT e.em_code, e.em_image
            FROM {$mg['emoticon_table']} e
            JOIN {$mg['emoticon_set_table']} es ON e.es_id = es.es_id
            WHERE es.es_use = 1 AND es.es_status = 'approved'";
    $result = sql_query($sql);

    $map = array();
    while ($row = sql_fetch_array($result)) {
        $map[$row['em_code']] = $row['em_image'];
    }
    return $map;
}

/**
 * 내가 만든 이모티콘 셋 목록 (크리에이터)
 *
 * @param string $mb_id 회원 ID
 * @return array
 */
function mg_get_creator_sets($mb_id) {
    global $mg;

    $mb_id = sql_real_escape_string($mb_id);
    $sql = "SELECT es.*, (SELECT COUNT(*) FROM {$mg['emoticon_table']} WHERE es_id = es.es_id) as em_count
            FROM {$mg['emoticon_set_table']} es
            WHERE es.es_creator_id = '{$mb_id}'
            ORDER BY es.es_id DESC";
    $result = sql_query($sql);

    $items = array();
    while ($row = sql_fetch_array($result)) {
        $items[] = $row;
    }
    return $items;
}

/**
 * 이모티콘 등록권 보유 확인
 *
 * @param string $mb_id 회원 ID
 * @return array ['can' => bool, 'count' => int, 'si_id' => int]
 */
function mg_can_create_emoticon($mb_id) {
    global $mg;

    // 이모티콘 기능 및 크리에이터 기능 확인
    if (!mg_config('emoticon_use', '1') || !mg_config('emoticon_creator_use', '1')) {
        return array('can' => false, 'count' => 0, 'si_id' => 0);
    }

    // emoticon_reg 타입 아이템 보유 확인
    $mb_id_esc = sql_real_escape_string($mb_id);
    $sql = "SELECT iv.si_id, iv.iv_count
            FROM {$mg['inventory_table']} iv
            JOIN {$mg['shop_item_table']} si ON iv.si_id = si.si_id
            WHERE iv.mb_id = '{$mb_id_esc}' AND si.si_type = 'emoticon_reg' AND iv.iv_count > 0
            LIMIT 1";
    $row = sql_fetch($sql);

    if ($row && (int)$row['iv_count'] > 0) {
        return array('can' => true, 'count' => (int)$row['iv_count'], 'si_id' => (int)$row['si_id']);
    }

    return array('can' => false, 'count' => 0, 'si_id' => 0);
}

/**
 * 이모티콘 셋 생성 (draft 상태)
 *
 * @param string $mb_id 제작자 ID (NULL이면 관리자)
 * @param array $data ['es_name', 'es_desc', 'es_price', 'es_preview']
 * @return int|false 생성된 es_id 또는 실패
 */
function mg_create_emoticon_set($mb_id, $data) {
    global $mg;

    $es_name = sql_real_escape_string(trim($data['es_name']));
    $es_desc = sql_real_escape_string(trim($data['es_desc'] ?? ''));
    $es_price = (int)($data['es_price'] ?? 0);
    $es_preview = sql_real_escape_string(trim($data['es_preview'] ?? ''));

    if ($mb_id) {
        $creator = "'" . sql_real_escape_string($mb_id) . "'";
        $status = 'draft';
    } else {
        $creator = 'NULL';
        $status = 'approved';
    }

    $sql = "INSERT INTO {$mg['emoticon_set_table']}
            (es_name, es_desc, es_preview, es_price, es_creator_id, es_status)
            VALUES ('{$es_name}', '{$es_desc}', '{$es_preview}', {$es_price}, {$creator}, '{$status}')";
    sql_query($sql);

    $es_id = sql_insert_id();
    return $es_id > 0 ? $es_id : false;
}

/**
 * 이모티콘 셋 심사 요청 (draft → pending)
 *
 * @param int $es_id 셋 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_submit_emoticon_set($es_id) {
    global $mg;

    $es_id = (int)$es_id;
    $set = mg_get_emoticon_set($es_id);

    if (!$set) {
        return array('success' => false, 'message' => '존재하지 않는 이모티콘 셋입니다.');
    }

    if ($set['es_status'] !== 'draft' && $set['es_status'] !== 'rejected') {
        return array('success' => false, 'message' => '심사 요청할 수 없는 상태입니다.');
    }

    // 최소 이모티콘 수 확인
    $min_count = (int)mg_config('emoticon_min_count', 8);
    if ((int)$set['em_count'] < $min_count) {
        return array('success' => false, 'message' => "이모티콘이 최소 {$min_count}개 이상 필요합니다.");
    }

    sql_query("UPDATE {$mg['emoticon_set_table']} SET es_status = 'pending' WHERE es_id = {$es_id}");

    return array('success' => true, 'message' => '심사 요청이 완료되었습니다.');
}

/**
 * 이모티콘 셋 승인 (pending → approved)
 *
 * @param int $es_id 셋 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_approve_emoticon_set($es_id) {
    global $mg;

    $es_id = (int)$es_id;
    $set = mg_get_emoticon_set($es_id);

    if (!$set) {
        return array('success' => false, 'message' => '존재하지 않는 이모티콘 셋입니다.');
    }

    if ($set['es_status'] !== 'pending') {
        return array('success' => false, 'message' => '승인 대기 상태가 아닙니다.');
    }

    sql_query("UPDATE {$mg['emoticon_set_table']} SET es_status = 'approved', es_use = 1 WHERE es_id = {$es_id}");

    // 알림 발송 (제작자가 있는 경우)
    if ($set['es_creator_id']) {
        mg_notify(
            $set['es_creator_id'],
            'emoticon',
            '이모티콘 셋 승인',
            '이모티콘 셋 "'.$set['es_name'].'"이(가) 승인되었습니다.',
            G5_BBS_URL.'/inventory.php?tab=emoticon'
        );
    }

    return array('success' => true, 'message' => '승인 처리되었습니다.');
}

/**
 * 이모티콘 셋 반려 (pending → rejected)
 *
 * @param int $es_id 셋 ID
 * @param string $reason 반려 사유
 * @return array ['success' => bool, 'message' => string]
 */
function mg_reject_emoticon_set($es_id, $reason = '') {
    global $mg;

    $es_id = (int)$es_id;
    $set = mg_get_emoticon_set($es_id);

    if (!$set) {
        return array('success' => false, 'message' => '존재하지 않는 이모티콘 셋입니다.');
    }

    if ($set['es_status'] !== 'pending') {
        return array('success' => false, 'message' => '승인 대기 상태가 아닙니다.');
    }

    $reason_esc = sql_real_escape_string($reason);
    sql_query("UPDATE {$mg['emoticon_set_table']}
               SET es_status = 'rejected', es_reject_reason = '{$reason_esc}'
               WHERE es_id = {$es_id}");

    // 알림 발송
    if ($set['es_creator_id']) {
        $msg = '이모티콘 셋 "'.$set['es_name'].'"이(가) 반려되었습니다.';
        if ($reason) {
            $msg .= ' 사유: '.$reason;
        }
        mg_notify(
            $set['es_creator_id'],
            'emoticon',
            '이모티콘 셋 반려',
            $msg,
            G5_BBS_URL.'/inventory.php?tab=emoticon'
        );
    }

    return array('success' => true, 'message' => '반려 처리되었습니다.');
}

/**
 * 이모티콘 셋 삭제 (이미지 파일 포함)
 *
 * @param int $es_id 셋 ID
 * @return bool
 */
function mg_delete_emoticon_set($es_id) {
    global $mg;

    $es_id = (int)$es_id;
    $set = mg_get_emoticon_set($es_id);
    if (!$set) return false;

    // 이모티콘 이미지 파일 삭제
    $emoticons = mg_get_emoticons($es_id);
    foreach ($emoticons as $em) {
        if ($em['em_image']) {
            $file = str_replace(MG_EMOTICON_URL, MG_EMOTICON_PATH, $em['em_image']);
            if (file_exists($file)) @unlink($file);
        }
    }

    // 미리보기 이미지 삭제
    if ($set['es_preview']) {
        $file = str_replace(MG_EMOTICON_URL, MG_EMOTICON_PATH, $set['es_preview']);
        if (file_exists($file)) @unlink($file);
    }

    // 셋 디렉토리 삭제
    $set_dir = MG_EMOTICON_PATH . '/' . $es_id;
    if (is_dir($set_dir)) @rmdir($set_dir);

    // DB 삭제
    sql_query("DELETE FROM {$mg['emoticon_table']} WHERE es_id = {$es_id}");
    sql_query("DELETE FROM {$mg['emoticon_own_table']} WHERE es_id = {$es_id}");
    sql_query("DELETE FROM {$mg['emoticon_set_table']} WHERE es_id = {$es_id}");

    return true;
}

/**
 * 이모티콘 개별 추가
 *
 * @param int $es_id 셋 ID
 * @param string $code 코드 (:code:)
 * @param string $image 이미지 경로
 * @param int $order 정렬 순서
 * @return int|false em_id 또는 실패
 */
function mg_add_emoticon($es_id, $code, $image, $order = 0) {
    global $mg;

    $es_id = (int)$es_id;
    $code = sql_real_escape_string($code);
    $image = sql_real_escape_string($image);
    $order = (int)$order;

    $sql = "INSERT INTO {$mg['emoticon_table']} (es_id, em_code, em_image, em_order)
            VALUES ({$es_id}, '{$code}', '{$image}', {$order})";
    sql_query($sql);

    $em_id = sql_insert_id();
    return $em_id > 0 ? $em_id : false;
}

/**
 * 이모티콘 코드 중복 확인
 *
 * @param string $code 코드
 * @param int $exclude_em_id 제외할 em_id (수정 시)
 * @return bool 중복 여부
 */
function mg_emoticon_code_exists($code, $exclude_em_id = 0) {
    global $mg;

    $code = sql_real_escape_string($code);
    $exclude = (int)$exclude_em_id;

    $sql = "SELECT em_id FROM {$mg['emoticon_table']} WHERE em_code = '{$code}'";
    if ($exclude > 0) {
        $sql .= " AND em_id != {$exclude}";
    }
    $sql .= " LIMIT 1";
    $row = sql_fetch($sql);

    return !empty($row['em_id']);
}

/**
 * 인벤토리에서 이모티콘 등록권 1개 소모
 *
 * @param string $mb_id 회원 ID
 * @return bool 성공 여부
 */
function mg_consume_emoticon_reg($mb_id) {
    global $mg;

    $check = mg_can_create_emoticon($mb_id);
    if (!$check['can']) return false;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $si_id = (int)$check['si_id'];

    // 수량 1 차감
    sql_query("UPDATE {$mg['inventory_table']}
               SET iv_count = iv_count - 1
               WHERE mb_id = '{$mb_id_esc}' AND si_id = {$si_id} AND iv_count > 0");

    return sql_affected_rows() > 0;
}

// ======================================
// 개척 시스템 (Pioneer System) 함수
// ======================================

/**
 * 유저 노동력 가져오기 (패시브 리셋 포함)
 *
 * @param string $mb_id 회원 ID
 * @return array ['current' => int, 'max' => int]
 */
function mg_get_stamina($mb_id) {
    global $mg;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $today = date('Y-m-d');
    $default_max = (int)mg_config('pioneer_stamina_default', 10);

    $row = sql_fetch("SELECT * FROM {$mg['user_stamina_table']} WHERE mb_id = '{$mb_id_esc}'");

    if (!$row) {
        // 신규 유저: 레코드 생성
        sql_query("INSERT INTO {$mg['user_stamina_table']} (mb_id, us_current, us_max, us_last_reset)
                   VALUES ('{$mb_id_esc}', {$default_max}, {$default_max}, '{$today}')");
        return ['current' => $default_max, 'max' => $default_max];
    }

    // 패시브 리셋: 날짜가 지났으면 리셋
    if ($row['us_last_reset'] < $today) {
        sql_query("UPDATE {$mg['user_stamina_table']}
                   SET us_current = us_max, us_last_reset = '{$today}'
                   WHERE mb_id = '{$mb_id_esc}'");
        return ['current' => (int)$row['us_max'], 'max' => (int)$row['us_max']];
    }

    return ['current' => (int)$row['us_current'], 'max' => (int)$row['us_max']];
}

/**
 * 유저 노동력 차감
 *
 * @param string $mb_id 회원 ID
 * @param int $amount 차감량
 * @return bool 성공 여부
 */
function mg_use_stamina($mb_id, $amount) {
    global $mg;

    $amount = (int)$amount;
    if ($amount <= 0) return false;

    // 현재 노동력 확인 (패시브 리셋 트리거)
    $stamina = mg_get_stamina($mb_id);
    if ($stamina['current'] < $amount) return false;

    $mb_id_esc = sql_real_escape_string($mb_id);
    sql_query("UPDATE {$mg['user_stamina_table']}
               SET us_current = us_current - {$amount}
               WHERE mb_id = '{$mb_id_esc}' AND us_current >= {$amount}");

    return sql_affected_rows() > 0;
}

/**
 * 유저 재료 보유량 가져오기
 *
 * @param string $mb_id 회원 ID
 * @param int|null $mt_id 특정 재료만 (null이면 전체)
 * @return array 재료 목록 또는 단일 수량
 */
function mg_get_materials($mb_id, $mt_id = null) {
    global $mg;

    $mb_id_esc = sql_real_escape_string($mb_id);

    if ($mt_id !== null) {
        $mt_id = (int)$mt_id;
        $row = sql_fetch("SELECT um_count FROM {$mg['user_material_table']}
                          WHERE mb_id = '{$mb_id_esc}' AND mt_id = {$mt_id}");
        return (int)($row['um_count'] ?? 0);
    }

    // 전체 재료 (재료 종류 정보 포함)
    $result = sql_query("SELECT mt.*, COALESCE(um.um_count, 0) as um_count
                         FROM {$mg['material_type_table']} mt
                         LEFT JOIN {$mg['user_material_table']} um
                             ON um.mt_id = mt.mt_id AND um.mb_id = '{$mb_id_esc}'
                         ORDER BY mt.mt_order, mt.mt_id");
    $materials = [];
    while ($row = sql_fetch_array($result)) {
        $materials[] = $row;
    }
    return $materials;
}

/**
 * 유저 재료 추가
 *
 * @param string $mb_id 회원 ID
 * @param int $mt_id 재료 종류
 * @param int $amount 수량
 * @return bool 성공 여부
 */
function mg_add_material($mb_id, $mt_id, $amount) {
    global $mg;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $mt_id = (int)$mt_id;
    $amount = (int)$amount;
    if ($amount <= 0) return false;

    // UPSERT: 없으면 INSERT, 있으면 UPDATE
    sql_query("INSERT INTO {$mg['user_material_table']} (mb_id, mt_id, um_count)
               VALUES ('{$mb_id_esc}', {$mt_id}, {$amount})
               ON DUPLICATE KEY UPDATE um_count = um_count + {$amount}");

    return true;
}

/**
 * 유저 재료 차감
 *
 * @param string $mb_id 회원 ID
 * @param int $mt_id 재료 종류
 * @param int $amount 수량
 * @return bool 성공 여부
 */
function mg_use_material($mb_id, $mt_id, $amount) {
    global $mg;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $mt_id = (int)$mt_id;
    $amount = (int)$amount;
    if ($amount <= 0) return false;

    // 보유량 확인
    $current = mg_get_materials($mb_id, $mt_id);
    if ($current < $amount) return false;

    sql_query("UPDATE {$mg['user_material_table']}
               SET um_count = um_count - {$amount}
               WHERE mb_id = '{$mb_id_esc}' AND mt_id = {$mt_id} AND um_count >= {$amount}");

    return sql_affected_rows() > 0;
}

/**
 * 모든 재료 종류 가져오기
 *
 * @return array 재료 종류 목록
 */
function mg_get_material_types() {
    global $mg;

    $result = sql_query("SELECT * FROM {$mg['material_type_table']} ORDER BY mt_order, mt_id");
    $types = [];
    while ($row = sql_fetch_array($result)) {
        $types[] = $row;
    }
    return $types;
}

/**
 * 재료 종류 코드로 가져오기
 *
 * @param string $code 재료 코드 (wood, stone 등)
 * @return array|null 재료 정보
 */
function mg_get_material_type_by_code($code) {
    global $mg;

    $code = sql_real_escape_string($code);
    return sql_fetch("SELECT * FROM {$mg['material_type_table']} WHERE mt_code = '{$code}'");
}

/**
 * 시설 목록 가져오기
 *
 * @param string|null $status 상태 필터 (null이면 전체)
 * @return array 시설 목록
 */
function mg_get_facilities($status = null) {
    global $mg;

    $where = "";
    if ($status) {
        $status_esc = sql_real_escape_string($status);
        $where = "WHERE fc_status = '{$status_esc}'";
    }

    $result = sql_query("SELECT * FROM {$mg['facility_table']} {$where} ORDER BY fc_order, fc_id");
    $facilities = [];
    while ($row = sql_fetch_array($result)) {
        // 필요 재료 정보 추가
        $row['materials'] = mg_get_facility_materials($row['fc_id']);
        $row['progress'] = mg_get_facility_progress($row);
        $facilities[] = $row;
    }
    return $facilities;
}

/**
 * 시설 단건 가져오기
 *
 * @param int $fc_id 시설 ID
 * @return array|null 시설 정보
 */
function mg_get_facility($fc_id) {
    global $mg;

    $fc_id = (int)$fc_id;
    $row = sql_fetch("SELECT * FROM {$mg['facility_table']} WHERE fc_id = {$fc_id}");

    if ($row) {
        $row['materials'] = mg_get_facility_materials($fc_id);
        $row['progress'] = mg_get_facility_progress($row);
    }
    return $row ?: null;
}

/**
 * 시설 필요 재료 가져오기
 *
 * @param int $fc_id 시설 ID
 * @return array 재료 목록
 */
function mg_get_facility_materials($fc_id) {
    global $mg;

    $fc_id = (int)$fc_id;
    $result = sql_query("SELECT fmc.*, mt.mt_name, mt.mt_code, mt.mt_icon
                         FROM {$mg['facility_material_cost_table']} fmc
                         JOIN {$mg['material_type_table']} mt ON mt.mt_id = fmc.mt_id
                         WHERE fmc.fc_id = {$fc_id}
                         ORDER BY mt.mt_order");
    $materials = [];
    while ($row = sql_fetch_array($result)) {
        $materials[] = $row;
    }
    return $materials;
}

/**
 * 시설 진행률 계산
 *
 * @param array $facility 시설 정보
 * @return array ['stamina' => float, 'total' => float, 'materials' => [...]]
 */
function mg_get_facility_progress($facility) {
    $progress = ['stamina' => 0, 'total' => 0, 'materials' => []];

    // 노동력 진행률
    if ($facility['fc_stamina_cost'] > 0) {
        $progress['stamina'] = min(100, ($facility['fc_stamina_current'] / $facility['fc_stamina_cost']) * 100);
    } else {
        $progress['stamina'] = 100;
    }

    // 재료별 진행률
    $materials = isset($facility['materials']) ? $facility['materials'] : mg_get_facility_materials($facility['fc_id']);
    $total_required = $facility['fc_stamina_cost'];
    $total_current = $facility['fc_stamina_current'];

    foreach ($materials as $mat) {
        $mat_progress = $mat['fmc_required'] > 0
            ? min(100, ($mat['fmc_current'] / $mat['fmc_required']) * 100)
            : 100;
        $progress['materials'][$mat['mt_code']] = $mat_progress;

        $total_required += $mat['fmc_required'];
        $total_current += $mat['fmc_current'];
    }

    // 총 진행률
    $progress['total'] = $total_required > 0
        ? min(100, ($total_current / $total_required) * 100)
        : 100;

    return $progress;
}

/**
 * 시설에 노동력 투입
 *
 * @param int $fc_id 시설 ID
 * @param string $mb_id 회원 ID
 * @param int $amount 투입량
 * @return array ['success' => bool, 'message' => string]
 */
function mg_contribute_stamina($fc_id, $mb_id, $amount) {
    global $mg;

    $fc_id = (int)$fc_id;
    $amount = (int)$amount;
    $mb_id_esc = sql_real_escape_string($mb_id);

    if ($amount <= 0) {
        return ['success' => false, 'message' => '투입량은 1 이상이어야 합니다.'];
    }

    // 시설 확인
    $facility = mg_get_facility($fc_id);
    if (!$facility) {
        return ['success' => false, 'message' => '시설을 찾을 수 없습니다.'];
    }
    if ($facility['fc_status'] !== 'building') {
        return ['success' => false, 'message' => '건설 중인 시설에만 투입할 수 있습니다.'];
    }

    // 필요한 남은 노동력
    $remaining = $facility['fc_stamina_cost'] - $facility['fc_stamina_current'];
    if ($remaining <= 0) {
        return ['success' => false, 'message' => '이 시설은 더 이상 노동력이 필요하지 않습니다.'];
    }

    // 실제 투입량 조정
    $actual_amount = min($amount, $remaining);

    // 노동력 차감
    if (!mg_use_stamina($mb_id, $actual_amount)) {
        return ['success' => false, 'message' => '노동력이 부족합니다.'];
    }

    // 시설에 투입
    sql_query("UPDATE {$mg['facility_table']}
               SET fc_stamina_current = fc_stamina_current + {$actual_amount}
               WHERE fc_id = {$fc_id}");

    // 기여 기록
    $now = date('Y-m-d H:i:s');
    sql_query("INSERT INTO {$mg['facility_contribution_table']}
               (fc_id, mb_id, fcn_type, fcn_amount, fcn_datetime)
               VALUES ({$fc_id}, '{$mb_id_esc}', 'stamina', {$actual_amount}, '{$now}')");

    // 완공 체크
    mg_check_facility_complete($fc_id);

    return ['success' => true, 'message' => "노동력 {$actual_amount}을(를) 투입했습니다.", 'amount' => $actual_amount];
}

/**
 * 시설에 재료 투입
 *
 * @param int $fc_id 시설 ID
 * @param string $mb_id 회원 ID
 * @param int $mt_id 재료 종류
 * @param int $amount 투입량
 * @return array ['success' => bool, 'message' => string]
 */
function mg_contribute_material($fc_id, $mb_id, $mt_id, $amount) {
    global $mg;

    $fc_id = (int)$fc_id;
    $mt_id = (int)$mt_id;
    $amount = (int)$amount;
    $mb_id_esc = sql_real_escape_string($mb_id);

    if ($amount <= 0) {
        return ['success' => false, 'message' => '투입량은 1 이상이어야 합니다.'];
    }

    // 시설 확인
    $facility = mg_get_facility($fc_id);
    if (!$facility) {
        return ['success' => false, 'message' => '시설을 찾을 수 없습니다.'];
    }
    if ($facility['fc_status'] !== 'building') {
        return ['success' => false, 'message' => '건설 중인 시설에만 투입할 수 있습니다.'];
    }

    // 해당 재료가 필요한지 확인
    $mat_cost = sql_fetch("SELECT * FROM {$mg['facility_material_cost_table']}
                           WHERE fc_id = {$fc_id} AND mt_id = {$mt_id}");
    if (!$mat_cost) {
        return ['success' => false, 'message' => '이 시설에 해당 재료는 필요하지 않습니다.'];
    }

    // 남은 필요량
    $remaining = $mat_cost['fmc_required'] - $mat_cost['fmc_current'];
    if ($remaining <= 0) {
        return ['success' => false, 'message' => '이 재료는 이미 충분히 모였습니다.'];
    }

    // 실제 투입량 조정
    $actual_amount = min($amount, $remaining);

    // 재료 차감
    if (!mg_use_material($mb_id, $mt_id, $actual_amount)) {
        return ['success' => false, 'message' => '재료가 부족합니다.'];
    }

    // 시설에 투입
    sql_query("UPDATE {$mg['facility_material_cost_table']}
               SET fmc_current = fmc_current + {$actual_amount}
               WHERE fc_id = {$fc_id} AND mt_id = {$mt_id}");

    // 기여 기록
    $now = date('Y-m-d H:i:s');
    sql_query("INSERT INTO {$mg['facility_contribution_table']}
               (fc_id, mb_id, fcn_type, mt_id, fcn_amount, fcn_datetime)
               VALUES ({$fc_id}, '{$mb_id_esc}', 'material', {$mt_id}, {$actual_amount}, '{$now}')");

    // 완공 체크
    mg_check_facility_complete($fc_id);

    // 재료 이름 가져오기
    $mat_type = sql_fetch("SELECT mt_name FROM {$mg['material_type_table']} WHERE mt_id = {$mt_id}");
    $mat_name = $mat_type['mt_name'] ?? '재료';

    return ['success' => true, 'message' => "{$mat_name} {$actual_amount}개를 투입했습니다.", 'amount' => $actual_amount];
}

/**
 * 시설 완공 체크 및 처리
 *
 * @param int $fc_id 시설 ID
 * @return bool 완공 여부
 */
function mg_check_facility_complete($fc_id) {
    global $mg;

    $fc_id = (int)$fc_id;
    $facility = mg_get_facility($fc_id);

    if (!$facility || $facility['fc_status'] !== 'building') {
        return false;
    }

    // 노동력 체크
    if ($facility['fc_stamina_current'] < $facility['fc_stamina_cost']) {
        return false;
    }

    // 재료 체크
    foreach ($facility['materials'] as $mat) {
        if ($mat['fmc_current'] < $mat['fmc_required']) {
            return false;
        }
    }

    // 완공 처리
    $now = date('Y-m-d H:i:s');
    sql_query("UPDATE {$mg['facility_table']}
               SET fc_status = 'complete', fc_complete_date = '{$now}'
               WHERE fc_id = {$fc_id}");

    // 해금 기능 적용
    if ($facility['fc_unlock_key'] && $facility['fc_unlock_value']) {
        mg_config_set($facility['fc_unlock_key'], $facility['fc_unlock_value']);
    }

    // 명예의 전당 기록
    mg_record_facility_honor($fc_id);

    return true;
}

/**
 * 시설 완공 시 명예의 전당 기록
 *
 * @param int $fc_id 시설 ID
 */
function mg_record_facility_honor($fc_id) {
    global $mg;

    $fc_id = (int)$fc_id;

    // 노동력 기여 TOP 3
    $stamina_result = sql_query("SELECT mb_id, SUM(fcn_amount) as total
                                  FROM {$mg['facility_contribution_table']}
                                  WHERE fc_id = {$fc_id} AND fcn_type = 'stamina'
                                  GROUP BY mb_id
                                  ORDER BY total DESC
                                  LIMIT 3");
    $rank = 1;
    while ($row = sql_fetch_array($stamina_result)) {
        $mb_id_esc = sql_real_escape_string($row['mb_id']);
        sql_query("INSERT INTO {$mg['facility_honor_table']}
                   (fc_id, fh_rank, fh_category, mb_id, fh_amount)
                   VALUES ({$fc_id}, {$rank}, 'stamina', '{$mb_id_esc}', {$row['total']})");
        $rank++;
    }

    // 재료별 기여 TOP 3
    $mat_types = sql_query("SELECT DISTINCT mt.mt_id, mt.mt_code
                            FROM {$mg['facility_material_cost_table']} fmc
                            JOIN {$mg['material_type_table']} mt ON mt.mt_id = fmc.mt_id
                            WHERE fmc.fc_id = {$fc_id}");
    while ($mt = sql_fetch_array($mat_types)) {
        $mt_id = (int)$mt['mt_id'];
        $mt_code = sql_real_escape_string($mt['mt_code']);

        $mat_result = sql_query("SELECT mb_id, SUM(fcn_amount) as total
                                  FROM {$mg['facility_contribution_table']}
                                  WHERE fc_id = {$fc_id} AND fcn_type = 'material' AND mt_id = {$mt_id}
                                  GROUP BY mb_id
                                  ORDER BY total DESC
                                  LIMIT 3");
        $rank = 1;
        while ($row = sql_fetch_array($mat_result)) {
            $mb_id_esc = sql_real_escape_string($row['mb_id']);
            sql_query("INSERT INTO {$mg['facility_honor_table']}
                       (fc_id, fh_rank, fh_category, mb_id, fh_amount)
                       VALUES ({$fc_id}, {$rank}, '{$mt_code}', '{$mb_id_esc}', {$row['total']})");
            $rank++;
        }
    }
}

/**
 * 시설 기여 랭킹 가져오기
 *
 * @param int $fc_id 시설 ID
 * @param string $category 카테고리 (stamina, wood 등)
 * @param int $limit 개수
 * @return array 랭킹 목록
 */
function mg_get_facility_ranking($fc_id, $category = 'stamina', $limit = 10) {
    global $mg;

    $fc_id = (int)$fc_id;
    $category = sql_real_escape_string($category);
    $limit = (int)$limit;

    // 완공된 시설은 명예의 전당에서
    $facility = mg_get_facility($fc_id);
    if ($facility && $facility['fc_status'] === 'complete') {
        $result = sql_query("SELECT fh.*, m.mb_nick, m.mb_name
                             FROM {$mg['facility_honor_table']} fh
                             JOIN {$GLOBALS['g5']['member_table']} m ON m.mb_id = fh.mb_id
                             WHERE fh.fc_id = {$fc_id} AND fh.fh_category = '{$category}'
                             ORDER BY fh.fh_rank
                             LIMIT {$limit}");
    } else {
        // 진행 중 시설은 실시간 계산
        $type_where = "fcn_type = 'stamina'";
        if ($category !== 'stamina') {
            $mt = mg_get_material_type_by_code($category);
            if ($mt) {
                $type_where = "fcn_type = 'material' AND mt_id = {$mt['mt_id']}";
            }
        }

        $result = sql_query("SELECT fcn.mb_id, SUM(fcn.fcn_amount) as fh_amount, m.mb_nick, m.mb_name
                             FROM {$mg['facility_contribution_table']} fcn
                             JOIN {$GLOBALS['g5']['member_table']} m ON m.mb_id = fcn.mb_id
                             WHERE fcn.fc_id = {$fc_id} AND {$type_where}
                             GROUP BY fcn.mb_id
                             ORDER BY fh_amount DESC
                             LIMIT {$limit}");
    }

    $ranking = [];
    $rank = 1;
    while ($row = sql_fetch_array($result)) {
        $row['rank'] = $rank++;
        $ranking[] = $row;
    }
    return $ranking;
}

/**
 * 활동 보상 재료 지급
 *
 * @param string $mb_id 회원 ID
 * @param string $activity 활동 유형 (write, comment, rp, attendance)
 * @return array|null ['mt_name' => string, 'amount' => int] 또는 null (미지급)
 */
function mg_reward_material($mb_id, $activity) {
    $config_key = 'pioneer_' . $activity . '_reward';
    $reward_str = mg_config($config_key, '');

    if (empty($reward_str)) return null;

    // 보상 형식 파싱: "wood:1" 또는 "random:1:30" (30% 확률로 랜덤 재료 1개)
    $parts = explode(':', $reward_str);

    if ($parts[0] === 'random') {
        $amount = (int)($parts[1] ?? 1);
        $chance = (int)($parts[2] ?? 100);

        // 확률 체크
        if (mt_rand(1, 100) > $chance) return null;

        // 랜덤 재료 선택
        $types = mg_get_material_types();
        if (empty($types)) return null;

        $mt = $types[array_rand($types)];
        mg_add_material($mb_id, $mt['mt_id'], $amount);

        return ['mt_name' => $mt['mt_name'], 'mt_icon' => $mt['mt_icon'], 'amount' => $amount];
    } else {
        // 지정 재료: "wood:1"
        $mt_code = $parts[0];
        $amount = (int)($parts[1] ?? 1);

        $mt = mg_get_material_type_by_code($mt_code);
        if (!$mt) return null;

        mg_add_material($mb_id, $mt['mt_id'], $amount);

        return ['mt_name' => $mt['mt_name'], 'mt_icon' => $mt['mt_icon'], 'amount' => $amount];
    }
}

/**
 * 개척 시스템 활성화 여부
 *
 * @return bool
 */
function mg_pioneer_enabled() {
    return mg_config('pioneer_enabled', '1') === '1';
}

/**
 * 컨텐츠 해금 여부 체크
 *
 * @param string $type 해금 대상 타입 (board, shop, gift, achievement, history, fountain)
 * @param string $target 해금 대상 ID (게시판이면 bo_table)
 * @return bool 해금되었으면 true
 */
function mg_is_unlocked($type, $target = '') {
    global $g5;

    // 개척 시스템 비활성화면 모두 해금
    if (!mg_pioneer_enabled()) {
        return true;
    }

    // 해당 타입+타겟에 연결된 시설 찾기
    $sql = "SELECT fc_id, fc_status FROM {$g5['mg_facility_table']}
            WHERE fc_unlock_type = '".sql_real_escape_string($type)."'";

    if ($target) {
        $sql .= " AND fc_unlock_target = '".sql_real_escape_string($target)."'";
    } else {
        $sql .= " AND (fc_unlock_target = '' OR fc_unlock_target IS NULL)";
    }

    $facility = sql_fetch($sql);

    // 연결된 시설이 없으면 해금된 것으로 처리
    if (!$facility['fc_id']) {
        return true;
    }

    // 시설이 완공(complete) 상태면 해금
    return $facility['fc_status'] === 'complete';
}

/**
 * 게시판 해금 여부 체크
 *
 * @param string $bo_table 게시판 테이블명
 * @return bool
 */
function mg_is_board_unlocked($bo_table) {
    return mg_is_unlocked('board', $bo_table);
}

/**
 * 상점 해금 여부 체크
 *
 * @return bool
 */
function mg_is_shop_unlocked() {
    return mg_is_unlocked('shop', '');
}

/**
 * 선물 시스템 해금 여부 체크
 *
 * @return bool
 */
function mg_is_gift_unlocked() {
    return mg_is_unlocked('gift', '');
}

/**
 * 해금되지 않은 컨텐츠 접근 시 안내 메시지 반환
 *
 * @param string $type
 * @param string $target
 * @return array|null 시설 정보 (fc_name, fc_status, progress 등) 또는 null
 */
function mg_get_unlock_info($type, $target = '') {
    global $g5;

    $sql = "SELECT * FROM {$g5['mg_facility_table']}
            WHERE fc_unlock_type = '".sql_real_escape_string($type)."'";

    if ($target) {
        $sql .= " AND fc_unlock_target = '".sql_real_escape_string($target)."'";
    } else {
        $sql .= " AND (fc_unlock_target = '' OR fc_unlock_target IS NULL)";
    }

    $facility = sql_fetch($sql);

    if (!$facility['fc_id']) {
        return null;
    }

    // 진행도 계산
    $facility['progress'] = mg_get_facility_progress($facility);

    return $facility;
}
