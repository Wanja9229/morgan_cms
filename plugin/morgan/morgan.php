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
// 업적 시스템
$g5['mg_achievement_table'] = 'mg_achievement';
$g5['mg_achievement_tier_table'] = 'mg_achievement_tier';
$g5['mg_user_achievement_table'] = 'mg_user_achievement';
$g5['mg_user_achievement_display_table'] = 'mg_user_achievement_display';
// 인장 시스템
$g5['mg_seal_table'] = 'mg_seal';
// 세계관 위키
$g5['mg_lore_category_table'] = 'mg_lore_category';
$g5['mg_lore_article_table'] = 'mg_lore_article';
$g5['mg_lore_section_table'] = 'mg_lore_section';
$g5['mg_lore_era_table'] = 'mg_lore_era';
$g5['mg_lore_event_table'] = 'mg_lore_event';
// 프롬프트 미션
$g5['mg_prompt_table'] = 'mg_prompt';
$g5['mg_prompt_entry_table'] = 'mg_prompt_entry';
// 보상 시스템
$g5['mg_board_reward_table'] = 'mg_board_reward';
$g5['mg_rp_completion_table'] = 'mg_rp_completion';
$g5['mg_rp_reply_reward_log_table'] = 'mg_rp_reply_reward_log';
$g5['mg_like_log_table'] = 'mg_like_log';
$g5['mg_like_daily_table'] = 'mg_like_daily';
$g5['mg_reward_type_table'] = 'mg_reward_type';
$g5['mg_reward_queue_table'] = 'mg_reward_queue';
// 관계 시스템
$g5['mg_relation_table'] = 'mg_relation';
$g5['mg_relation_icon_table'] = 'mg_relation_icon';
// 탐색 파견 시스템
$g5['mg_expedition_area_table'] = 'mg_expedition_area';
$g5['mg_expedition_drop_table'] = 'mg_expedition_drop';
$g5['mg_expedition_log_table'] = 'mg_expedition_log';
$g5['mg_concierge_table'] = 'mg_concierge';
$g5['mg_concierge_apply_table'] = 'mg_concierge_apply';
$g5['mg_concierge_result_table'] = 'mg_concierge_result';

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
// 업적 시스템
$mg['achievement_table'] = $g5['mg_achievement_table'];
$mg['achievement_tier_table'] = $g5['mg_achievement_tier_table'];
$mg['user_achievement_table'] = $g5['mg_user_achievement_table'];
$mg['user_achievement_display_table'] = $g5['mg_user_achievement_display_table'];
// 인장 시스템
$mg['seal_table'] = $g5['mg_seal_table'];
// 세계관 위키
$mg['lore_category_table'] = $g5['mg_lore_category_table'];
$mg['lore_article_table'] = $g5['mg_lore_article_table'];
$mg['lore_section_table'] = $g5['mg_lore_section_table'];
$mg['lore_era_table'] = $g5['mg_lore_era_table'];
$mg['lore_event_table'] = $g5['mg_lore_event_table'];
// 프롬프트 미션
$mg['prompt_table'] = $g5['mg_prompt_table'];
$mg['prompt_entry_table'] = $g5['mg_prompt_entry_table'];
// 보상 시스템
$mg['rp_completion_table'] = $g5['mg_rp_completion_table'];
$mg['rp_reply_reward_log_table'] = $g5['mg_rp_reply_reward_log_table'];
$mg['like_log_table'] = $g5['mg_like_log_table'];
$mg['like_daily_table'] = $g5['mg_like_daily_table'];
$mg['reward_type_table'] = $g5['mg_reward_type_table'];
$mg['reward_queue_table'] = $g5['mg_reward_queue_table'];
// 관계 시스템
$mg['relation_table'] = $g5['mg_relation_table'];
$mg['relation_icon_table'] = $g5['mg_relation_icon_table'];
// 탐색 파견 시스템
$mg['expedition_area_table'] = $g5['mg_expedition_area_table'];
$mg['expedition_drop_table'] = $g5['mg_expedition_drop_table'];
$mg['expedition_log_table'] = $g5['mg_expedition_log_table'];
// 의뢰 매칭 시스템
$mg['concierge_table'] = $g5['mg_concierge_table'];
$mg['concierge_apply_table'] = $g5['mg_concierge_apply_table'];
$mg['concierge_result_table'] = $g5['mg_concierge_result_table'];

// 상점 이미지 저장 경로
define('MG_SHOP_IMAGE_PATH', G5_DATA_PATH.'/shop');
define('MG_SHOP_IMAGE_URL', G5_DATA_URL.'/shop');

// 이모티콘 이미지 저장 경로
define('MG_EMOTICON_PATH', G5_DATA_PATH.'/emoticon');
define('MG_EMOTICON_URL', G5_DATA_URL.'/emoticon');

// 인장 이미지 저장 경로
define('MG_SEAL_IMAGE_PATH', G5_DATA_PATH.'/seal');
define('MG_SEAL_IMAGE_URL', G5_DATA_URL.'/seal');

// 위키 이미지 저장 경로
define('MG_LORE_IMAGE_PATH', G5_DATA_PATH.'/lore');
define('MG_LORE_IMAGE_URL', G5_DATA_URL.'/lore');

// 프롬프트 배너 이미지 저장 경로
define('MG_PROMPT_IMAGE_PATH', G5_DATA_PATH.'/prompt');
define('MG_PROMPT_IMAGE_URL', G5_DATA_URL.'/prompt');

// 썸네일 사이즈
define('MG_THUMB_SIZE', 200);

/**
 * Gnuboard에 누락된 sql_affected_rows() 래퍼
 * UPDATE/DELETE 후 영향 받은 행 수 반환
 */
if (!function_exists('sql_affected_rows')) {
    function sql_affected_rows($link = null) {
        if ($link === null) {
            $link = $GLOBALS['connect_db'];
        }
        return mysqli_affected_rows($link);
    }
}

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
    $exclusive_types = array('title', 'nick_color', 'nick_effect', 'seal_bg', 'seal_frame');
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
 * 캐릭터별 역극 이음 조회
 *
 * @param int $rt_id 역극 ID
 * @param int $ch_id 캐릭터 ID
 * @return array 이음 배열
 */
function mg_get_rp_replies_by_character($rt_id, $ch_id) {
    global $mg, $g5;

    $rt_id = (int)$rt_id;
    $ch_id = (int)$ch_id;

    $sql = "SELECT r.*, m.mb_nick, c.ch_name, c.ch_thumb
            FROM {$mg['rp_reply_table']} r
            LEFT JOIN {$g5['member_table']} m ON r.mb_id = m.mb_id
            LEFT JOIN {$mg['character_table']} c ON r.ch_id = c.ch_id
            WHERE r.rt_id = {$rt_id} AND ((r.ch_id = {$ch_id} AND r.rr_context_ch_id = 0) OR r.rr_context_ch_id = {$ch_id})
            ORDER BY r.rr_id ASC";
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
    $context_ch_id = isset($data['rr_context_ch_id']) ? (int)$data['rr_context_ch_id'] : 0;
    $content = sql_real_escape_string($data['rr_content']);
    $image = isset($data['rr_image']) ? sql_real_escape_string($data['rr_image']) : '';

    // 역극 존재/상태 체크
    $thread = sql_fetch("SELECT * FROM {$mg['rp_thread_table']} WHERE rt_id = {$rt_id}");
    if (!$thread || $thread['rt_status'] != 'open') {
        return array('success' => false, 'message' => '이음할 수 없는 역극입니다.');
    }

    // 최소 글자 수 체크 (이미지 첨부 시 내용 생략 가능)
    $min_len = (int)mg_config('rp_content_min', 20);
    $content_len = mb_strlen(strip_tags($data['rr_content']));
    if ($content_len > 0 && $content_len < $min_len && !$image) {
        return array('success' => false, 'message' => "내용을 {$min_len}자 이상 입력해주세요.");
    }

    // 대화 맥락: context_ch_id가 작성자 ch_id와 다르면 설정
    if (!$context_ch_id || $context_ch_id == $ch_id) {
        $context_ch_id = 0;
    }

    // 이음 삽입
    $sql = "INSERT INTO {$mg['rp_reply_table']}
            (rt_id, rr_content, rr_image, mb_id, ch_id, rr_context_ch_id)
            VALUES ({$rt_id}, '{$content}', '{$image}', '{$mb_id}', {$ch_id}, {$context_ch_id})";
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
// 역극 재화 시스템
// ======================================

/**
 * 역극 판 세우기 비용 차감
 *
 * @param string $mb_id 회원 ID
 * @return array ['success' => bool, 'message' => string]
 */
function mg_rp_deduct_create_cost($mb_id) {
    global $g5;

    $cost = (int)mg_config('rp_create_cost', 500);
    if ($cost <= 0) {
        return array('success' => true);
    }

    // 현재 포인트 조회
    $mb = sql_fetch("SELECT mb_point FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
    if ((int)$mb['mb_point'] < $cost) {
        return array('success' => false, 'message' => "포인트가 부족합니다. (필요: {$cost}P, 보유: {$mb['mb_point']}P)");
    }

    insert_point($mb_id, -$cost, '역극 판 세우기 비용', 'mg_rp_thread', 0, '차감');
    return array('success' => true);
}

/**
 * 잇기 누적 보상 체크 + 지급
 *
 * 스레드 전체 이음 수 기준으로 N개당 참여자 전원에게 보상
 *
 * @param int $rt_id 역극 스레드 ID
 */
function mg_rp_check_reply_reward($rt_id) {
    global $g5, $mg;

    $batch_count = (int)mg_config('rp_reply_batch_count', 10);
    $batch_point = (int)mg_config('rp_reply_batch_point', 30);

    if ($batch_point <= 0 || $batch_count <= 0) return;

    $rt_id = (int)$rt_id;

    // 현재 이음 수
    $thread = sql_fetch("SELECT rt_reply_count, rt_title FROM {$mg['rp_thread_table']} WHERE rt_id = {$rt_id}");
    if (!$thread) return;

    $current_count = (int)$thread['rt_reply_count'];

    // 마지막 보상 시점
    $last = sql_fetch("SELECT MAX(rrl_reply_count) as last_count FROM {$g5['mg_rp_reply_reward_log_table']} WHERE rt_id = {$rt_id}");
    $last_rewarded = (int)($last['last_count'] ?? 0);

    // 다음 보상 기준
    $next_threshold = $last_rewarded + $batch_count;

    if ($current_count < $next_threshold) return;

    // 참여자 전원 조회
    $members = mg_get_rp_members($rt_id);
    if (!$members) return;

    $title_short = mb_substr(strip_tags($thread['rt_title']), 0, 20);

    foreach ($members as $mem) {
        if (!$mem['mb_id']) continue;
        insert_point($mem['mb_id'], $batch_point, "역극 \"{$title_short}\" 이음 {$next_threshold}회 보상", 'mg_rp_thread', $rt_id, '이음보상');

        // 알림
        if (function_exists('mg_notify')) {
            mg_notify(
                $mem['mb_id'],
                'reward',
                "역극 \"{$title_short}\" 이음 {$next_threshold}회 달성! +{$batch_point}P",
                '',
                G5_BBS_URL . '/rp_list.php#rp-thread-' . $rt_id
            );
        }
    }

    // 보상 로그 기록
    sql_query("INSERT INTO {$g5['mg_rp_reply_reward_log_table']}
               (rt_id, rrl_reply_count, rrl_point)
               VALUES ({$rt_id}, {$next_threshold}, {$batch_point})");
}

/**
 * 캐릭터별 완결 처리
 *
 * @param int $rt_id 역극 스레드 ID
 * @param int $ch_id 완결할 캐릭터 ID
 * @param string $type 'manual' 또는 'auto'
 * @param string|null $by_mb_id 처리자 (수동시 판장 mb_id)
 * @param bool $force 조건 미충족 시에도 강제 완결
 * @return array ['success' => bool, 'message' => string, ...]
 */
function mg_rp_complete_character($rt_id, $ch_id, $type = 'manual', $by_mb_id = null, $force = false) {
    global $g5, $mg;

    $rt_id = (int)$rt_id;
    $ch_id = (int)$ch_id;

    // 이미 완결인지 확인
    $existing = sql_fetch("SELECT rc_id FROM {$g5['mg_rp_completion_table']} WHERE rt_id = {$rt_id} AND ch_id = {$ch_id}");
    if ($existing['rc_id']) {
        return array('success' => false, 'message' => '이미 완결 처리된 캐릭터입니다.');
    }

    // 스레드 조회
    $thread = sql_fetch("SELECT * FROM {$mg['rp_thread_table']} WHERE rt_id = {$rt_id}");
    if (!$thread) {
        return array('success' => false, 'message' => '존재하지 않는 역극입니다.');
    }

    $owner_ch_id = (int)$thread['ch_id'];
    $owner_mb_id = $thread['mb_id'];

    // 참여자 정보 조회
    $participant = sql_fetch("SELECT * FROM {$mg['rp_member_table']} WHERE rt_id = {$rt_id} AND ch_id = {$ch_id}");
    if (!$participant) {
        return array('success' => false, 'message' => '해당 캐릭터는 이 역극의 참여자가 아닙니다.');
    }

    $target_mb_id = $participant['mb_id'];
    $total_replies = (int)$participant['rm_reply_count'];

    // 상호 이음 수 계산
    // 판장 → 해당 캐릭터 방향
    $owner_to_char = sql_fetch("SELECT COUNT(*) as cnt FROM {$mg['rp_reply_table']}
        WHERE rt_id = {$rt_id} AND ch_id = {$owner_ch_id} AND rr_context_ch_id = {$ch_id}");
    $count_owner_to_char = (int)$owner_to_char['cnt'];

    // 해당 캐릭터 → 판장 방향 (context가 판장이거나 기본 대화)
    $char_to_owner = sql_fetch("SELECT COUNT(*) as cnt FROM {$mg['rp_reply_table']}
        WHERE rt_id = {$rt_id} AND ch_id = {$ch_id}
        AND (rr_context_ch_id = {$owner_ch_id} OR rr_context_ch_id = 0)");
    $count_char_to_owner = (int)$char_to_owner['cnt'];

    $mutual_count = min($count_owner_to_char, $count_char_to_owner);

    // 완결 조건 체크
    $min_mutual = (int)mg_config('rp_complete_min_mutual', 5);
    $complete_point = (int)mg_config('rp_complete_point', 200);
    $condition_met = ($mutual_count >= $min_mutual);

    // 조건 미충족이고 강제도 아니면 확인 필요
    if (!$condition_met && !$force && $type === 'manual') {
        return array(
            'success' => false,
            'need_confirm' => true,
            'mutual_count' => $mutual_count,
            'min_mutual' => $min_mutual,
            'message' => "상호 이음 조건을 충족하지 않았습니다. ({$mutual_count}/{$min_mutual}회)"
        );
    }

    // 보상 결정
    $rewarded = ($condition_met && $complete_point > 0) ? 1 : 0;
    $point_given = $rewarded ? $complete_point : 0;

    // 포인트 지급
    if ($rewarded) {
        $title_short = mb_substr(strip_tags($thread['rt_title']), 0, 20);
        insert_point($target_mb_id, $complete_point, "역극 \"{$title_short}\" 완결 보상", 'mg_rp_completion', $rt_id, '완결');
    }

    // 완결 기록
    $by_esc = $by_mb_id ? "'".sql_real_escape_string($by_mb_id)."'" : 'NULL';
    sql_query("INSERT INTO {$g5['mg_rp_completion_table']}
        (rt_id, ch_id, mb_id, rc_mutual_count, rc_total_replies, rc_rewarded, rc_point, rc_type, rc_by)
        VALUES ({$rt_id}, {$ch_id}, '".sql_real_escape_string($target_mb_id)."',
                {$mutual_count}, {$total_replies}, {$rewarded}, {$point_given}, '{$type}', {$by_esc})");

    // 알림
    if (function_exists('mg_notify')) {
        $title_short = mb_substr(strip_tags($thread['rt_title']), 0, 20);
        $noti_msg = "역극 \"{$title_short}\"에서 완결 처리되었습니다.";
        if ($rewarded) {
            $noti_msg .= " (+{$point_given}P)";
        }
        mg_notify(
            $target_mb_id,
            'reward',
            $noti_msg,
            '',
            G5_BBS_URL . '/rp_list.php#rp-thread-' . $rt_id
        );
    }

    // 모든 참여자(판장 제외)가 완결되었으면 스레드도 closed
    $uncompleted = sql_fetch("SELECT COUNT(*) as cnt FROM {$mg['rp_member_table']} rm
        WHERE rm.rt_id = {$rt_id} AND rm.ch_id != {$owner_ch_id}
        AND NOT EXISTS (SELECT 1 FROM {$g5['mg_rp_completion_table']} rc WHERE rc.rt_id = rm.rt_id AND rc.ch_id = rm.ch_id)");
    if ((int)$uncompleted['cnt'] === 0) {
        sql_query("UPDATE {$mg['rp_thread_table']} SET rt_status = 'closed' WHERE rt_id = {$rt_id}");
    }

    return array(
        'success' => true,
        'message' => '완결 처리되었습니다.' . ($rewarded ? " (+{$point_given}P)" : ' (보상 없음)'),
        'rewarded' => $rewarded,
        'point' => $point_given,
        'mutual_count' => $mutual_count,
        'thread_closed' => ((int)$uncompleted['cnt'] === 0)
    );
}

/**
 * 자동 완결 체크 (패시브)
 *
 * @param int $rt_id 역극 스레드 ID
 */
function mg_rp_auto_complete_check($rt_id) {
    global $g5, $mg;

    $rt_id = (int)$rt_id;
    $auto_days = (int)mg_config('rp_auto_complete_days', 7);
    if ($auto_days <= 0) return;

    $thread = sql_fetch("SELECT * FROM {$mg['rp_thread_table']} WHERE rt_id = {$rt_id} AND rt_status = 'open'");
    if (!$thread) return;

    // 기한 초과 확인
    $deadline = date('Y-m-d H:i:s', strtotime("-{$auto_days} days"));
    if ($thread['rt_update'] > $deadline) return;

    $owner_ch_id = (int)$thread['ch_id'];

    // 미완결 참여자 (판장 제외) 순회
    $result = sql_query("SELECT rm.* FROM {$mg['rp_member_table']} rm
        WHERE rm.rt_id = {$rt_id} AND rm.ch_id != {$owner_ch_id}
        AND NOT EXISTS (SELECT 1 FROM {$g5['mg_rp_completion_table']} rc WHERE rc.rt_id = rm.rt_id AND rc.ch_id = rm.ch_id)");

    while ($mem = sql_fetch_array($result)) {
        mg_rp_complete_character($rt_id, (int)$mem['ch_id'], 'auto', null, true);
    }

    // 스레드 closed (mg_rp_complete_character에서 처리되지만 안전장치)
    sql_query("UPDATE {$mg['rp_thread_table']} SET rt_status = 'closed' WHERE rt_id = {$rt_id} AND rt_status = 'open'");
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

    // 업적 트리거 (개척 노동력)
    mg_trigger_achievement($mb_id, 'pioneer_stamina_total', $actual_amount);

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

    // 업적 트리거 (개척 재료 투입)
    mg_trigger_achievement($mb_id, 'pioneer_material_total', $actual_amount);

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

    // 업적 트리거: 시설 참여자 전원에게 시설 참여 업적
    $contribs = sql_query("SELECT DISTINCT mb_id FROM {$mg['facility_contribution_table']} WHERE fc_id = {$fc_id}");
    while ($c = sql_fetch_array($contribs)) {
        mg_trigger_achievement($c['mb_id'], 'pioneer_facility_count');
    }

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

// ======================================
// 탐색 파견 시스템
// ======================================

/**
 * 파견지 목록
 */
function mg_get_expedition_areas($status = null, $mb_id = null) {
    global $g5;

    $where = '';
    if ($status) {
        $where = " WHERE ea_status = '" . sql_real_escape_string($status) . "'";
    }

    $sql = "SELECT * FROM {$g5['mg_expedition_area_table']}{$where} ORDER BY ea_order, ea_id";
    $result = sql_query($sql);
    $areas = array();

    while ($row = sql_fetch_array($result)) {
        // 드롭 테이블 함께 로드
        $drop_sql = "SELECT ed.*, mt.mt_name, mt.mt_icon, mt.mt_code
                     FROM {$g5['mg_expedition_drop_table']} ed
                     LEFT JOIN {$g5['mg_material_type_table']} mt ON ed.mt_id = mt.mt_id
                     WHERE ed.ea_id = {$row['ea_id']}
                     ORDER BY ed.ed_is_rare, ed.ed_chance DESC";
        $drop_result = sql_query($drop_sql);
        $row['drops'] = array();
        while ($drop = sql_fetch_array($drop_result)) {
            $row['drops'][] = $drop;
        }

        // 해금 조건 체크
        $row['is_unlocked'] = true;
        if ($row['ea_unlock_facility'] && $mb_id) {
            $fc = sql_fetch("SELECT fc_id, fc_name, fc_status FROM {$g5['mg_facility_table']}
                            WHERE fc_id = " . (int)$row['ea_unlock_facility']);
            if (!$fc || $fc['fc_status'] !== 'complete') {
                $row['is_unlocked'] = false;
            }
            $row['unlock_facility_name'] = $fc ? $fc['fc_name'] : '';
        }

        $areas[] = $row;
    }

    return $areas;
}

/**
 * 진행 중인 파견 목록
 */
function mg_get_active_expeditions($mb_id) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $now = date('Y-m-d H:i:s');

    // active 상태 중 완료 시간 지난 것 → complete로 전환
    sql_query("UPDATE {$g5['mg_expedition_log_table']}
               SET el_status = 'complete'
               WHERE mb_id = '{$mb_id_esc}' AND el_status = 'active' AND el_end <= '{$now}'");

    $sql = "SELECT el.*, ea.ea_name, ea.ea_icon, ea.ea_image, ea.ea_partner_point,
                   ch.ch_name, ch.ch_thumb,
                   pch.ch_name as partner_ch_name, pch.ch_thumb as partner_ch_thumb,
                   pm.mb_nick as partner_nick
            FROM {$g5['mg_expedition_log_table']} el
            LEFT JOIN {$g5['mg_expedition_area_table']} ea ON el.ea_id = ea.ea_id
            LEFT JOIN {$g5['mg_character_table']} ch ON el.ch_id = ch.ch_id
            LEFT JOIN {$g5['mg_character_table']} pch ON el.partner_ch_id = pch.ch_id
            LEFT JOIN {$g5['member_table']} pm ON el.partner_mb_id = pm.mb_id
            WHERE el.mb_id = '{$mb_id_esc}' AND el.el_status IN ('active', 'complete')
            ORDER BY el.el_start DESC";
    $result = sql_query($sql);
    $expeditions = array();
    while ($row = sql_fetch_array($result)) {
        $row['is_complete'] = ($row['el_status'] === 'complete');
        $row['remaining_seconds'] = max(0, strtotime($row['el_end']) - time());
        $row['total_seconds'] = strtotime($row['el_end']) - strtotime($row['el_start']);
        $row['progress'] = $row['total_seconds'] > 0
            ? min(100, (($row['total_seconds'] - $row['remaining_seconds']) / $row['total_seconds']) * 100)
            : 100;
        $expeditions[] = $row;
    }

    return $expeditions;
}

/**
 * 파견 시작
 */
function mg_start_expedition($mb_id, $ch_id, $ea_id, $partner_ch_id = null) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $ch_id = (int)$ch_id;
    $ea_id = (int)$ea_id;

    // 1. 파견지 확인
    $area = sql_fetch("SELECT * FROM {$g5['mg_expedition_area_table']} WHERE ea_id = {$ea_id} AND ea_status = 'active'");
    if (!$area) {
        return array('success' => false, 'message' => '파견지를 찾을 수 없습니다.');
    }

    // 2. 해금 조건
    if ($area['ea_unlock_facility']) {
        $fc = sql_fetch("SELECT fc_status FROM {$g5['mg_facility_table']} WHERE fc_id = " . (int)$area['ea_unlock_facility']);
        if (!$fc || $fc['fc_status'] !== 'complete') {
            return array('success' => false, 'message' => '아직 해금되지 않은 파견지입니다.');
        }
    }

    // 3. 동시 파견 수 제한
    $max_slots = (int)mg_config('expedition_max_slots', 1);
    $active_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_expedition_log_table']}
                             WHERE mb_id = '{$mb_id_esc}' AND el_status IN ('active', 'complete')");
    $active_count = (int)$active_row['cnt'];
    if ($active_count >= $max_slots) {
        return array('success' => false, 'message' => "동시 파견 수({$max_slots}회)를 초과했습니다.");
    }

    // 4. 캐릭터 소유 확인
    $character = sql_fetch("SELECT ch_id, ch_name FROM {$g5['mg_character_table']}
                            WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id_esc}' AND ch_state = 'approved'");
    if (!$character) {
        return array('success' => false, 'message' => '사용할 수 없는 캐릭터입니다.');
    }

    // 5. 동일 캐릭터 파견 중 확인
    $char_active = sql_fetch("SELECT el_id FROM {$g5['mg_expedition_log_table']}
                              WHERE ch_id = {$ch_id} AND el_status IN ('active', 'complete')");
    if ($char_active && $char_active['el_id']) {
        return array('success' => false, 'message' => '이미 파견 중인 캐릭터입니다.');
    }

    // 6. 파트너 검증 (관계 기반)
    $partner_mb_id = null;
    if ($partner_ch_id) {
        $partner_ch_id = (int)$partner_ch_id;

        // 관계 확인
        $rel = sql_fetch("SELECT cr_id FROM {$g5['mg_relation_table']}
                          WHERE ((ch_id_a = {$ch_id} AND ch_id_b = {$partner_ch_id})
                              OR (ch_id_a = {$partner_ch_id} AND ch_id_b = {$ch_id}))
                          AND cr_status = 'active'");
        if (!$rel || !$rel['cr_id']) {
            return array('success' => false, 'message' => '관계가 맺어진 캐릭터만 파트너로 선택할 수 있습니다.');
        }

        // 파트너 캐릭터 소유자 확인
        $partner_char = sql_fetch("SELECT ch_id, mb_id FROM {$g5['mg_character_table']}
                                   WHERE ch_id = {$partner_ch_id} AND ch_state = 'approved'");
        if (!$partner_char || !$partner_char['mb_id']) {
            return array('success' => false, 'message' => '존재하지 않는 파트너 캐릭터입니다.');
        }
        $partner_mb_id = $partner_char['mb_id'];

        // 자기 자신 불가
        if ($partner_mb_id === $mb_id) {
            return array('success' => false, 'message' => '자기 자신의 캐릭터를 파트너로 선택할 수 없습니다.');
        }

        // 1일 1회 동일 파트너 제한
        $today = date('Y-m-d');
        $today_partner = sql_fetch("SELECT el_id FROM {$g5['mg_expedition_log_table']}
                                    WHERE mb_id = '{$mb_id_esc}' AND partner_ch_id = {$partner_ch_id}
                                    AND DATE(el_start) = '{$today}'");
        if ($today_partner && $today_partner['el_id']) {
            return array('success' => false, 'message' => '오늘 이미 같은 파트너와 파견을 보냈습니다.');
        }
    }

    // 7. 스태미나 차감
    if (!mg_use_stamina($mb_id, $area['ea_stamina_cost'])) {
        return array('success' => false, 'message' => '스태미나가 부족합니다. (필요: ' . $area['ea_stamina_cost'] . ')');
    }

    // 8. 파견 기록 생성
    $now = date('Y-m-d H:i:s');
    $end_time = date('Y-m-d H:i:s', time() + ($area['ea_duration'] * 60));
    $partner_mb_col = $partner_mb_id ? "'" . sql_real_escape_string($partner_mb_id) . "'" : 'NULL';
    $partner_ch_col = $partner_ch_id ? (int)$partner_ch_id : 'NULL';

    sql_query("INSERT INTO {$g5['mg_expedition_log_table']}
               (mb_id, ch_id, partner_mb_id, partner_ch_id, ea_id, el_stamina_used, el_start, el_end, el_status)
               VALUES ('{$mb_id_esc}', {$ch_id}, {$partner_mb_col}, {$partner_ch_col}, {$ea_id},
                       {$area['ea_stamina_cost']}, '{$now}', '{$end_time}', 'active')");

    $el_id = sql_insert_id();

    // 9. 업적 트리거
    if (function_exists('mg_trigger_achievement')) {
        mg_trigger_achievement($mb_id, 'expedition_start_count');
    }

    return array('success' => true, 'message' => '파견을 보냈습니다!', 'el_id' => $el_id, 'end_time' => $end_time);
}

/**
 * 드롭 결과 계산
 */
function mg_calculate_drops($ea_id, $has_partner = false) {
    global $g5;

    $ea_id = (int)$ea_id;
    $drops = array();
    $has_rare = false;

    $result = sql_query("SELECT ed.*, mt.mt_name, mt.mt_icon, mt.mt_code
                         FROM {$g5['mg_expedition_drop_table']} ed
                         LEFT JOIN {$g5['mg_material_type_table']} mt ON ed.mt_id = mt.mt_id
                         WHERE ed.ea_id = {$ea_id}");

    while ($row = sql_fetch_array($result)) {
        $roll = mt_rand(1, 100);
        if ($roll <= (int)$row['ed_chance']) {
            $amount = mt_rand((int)$row['ed_min'], max((int)$row['ed_min'], (int)$row['ed_max']));
            // 파트너 보너스 +20%
            if ($has_partner && $amount > 0) {
                $amount = (int)ceil($amount * 1.2);
            }
            if ($amount > 0) {
                $drops[] = array(
                    'mt_id'   => (int)$row['mt_id'],
                    'mt_name' => $row['mt_name'],
                    'mt_icon' => $row['mt_icon'],
                    'mt_code' => $row['mt_code'],
                    'amount'  => $amount,
                    'is_rare' => (bool)$row['ed_is_rare'],
                );
                if ($row['ed_is_rare']) {
                    $has_rare = true;
                }
            }
        }
    }

    return array('items' => $drops, 'has_rare' => $has_rare);
}

/**
 * 파견 보상 수령
 */
function mg_claim_expedition($mb_id, $el_id) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $el_id = (int)$el_id;

    $log = sql_fetch("SELECT * FROM {$g5['mg_expedition_log_table']}
                      WHERE el_id = {$el_id} AND mb_id = '{$mb_id_esc}'");

    if (!$log) {
        return array('success' => false, 'message' => '파견 기록을 찾을 수 없습니다.');
    }

    // active인데 시간 지났으면 complete로
    if ($log['el_status'] === 'active') {
        if (strtotime($log['el_end']) <= time()) {
            sql_query("UPDATE {$g5['mg_expedition_log_table']}
                       SET el_status = 'complete' WHERE el_id = {$el_id}");
            $log['el_status'] = 'complete';
        } else {
            return array('success' => false, 'message' => '아직 파견이 진행 중입니다.');
        }
    }

    if ($log['el_status'] !== 'complete') {
        return array('success' => false, 'message' => '수령할 수 없는 상태입니다.');
    }

    // 드롭 계산 (파트너 보너스 반영)
    $has_partner = !empty($log['partner_ch_id']);
    $drop_result = mg_calculate_drops((int)$log['ea_id'], $has_partner);

    // 재료 지급
    foreach ($drop_result['items'] as $item) {
        mg_add_material($mb_id, $item['mt_id'], $item['amount']);
    }

    // 상태 업데이트 + 보상 기록
    $rewards_json = sql_real_escape_string(json_encode($drop_result, JSON_UNESCAPED_UNICODE));
    sql_query("UPDATE {$g5['mg_expedition_log_table']}
               SET el_status = 'claimed', el_rewards = '{$rewards_json}'
               WHERE el_id = {$el_id}");

    // 파트너 보상
    if ($log['partner_mb_id']) {
        $area = sql_fetch("SELECT ea_partner_point, ea_name FROM {$g5['mg_expedition_area_table']}
                          WHERE ea_id = " . (int)$log['ea_id']);
        $partner_point = $area ? (int)$area['ea_partner_point'] : 10;
        if ($partner_point > 0) {
            insert_point($log['partner_mb_id'], $partner_point,
                        '파견 파트너 보상 (' . ($area ? $area['ea_name'] : '') . ')',
                        'mg_expedition_log', $el_id, '파트너');

            $my_info = get_member($mb_id, 'mb_nick');
            $my_nick = $my_info['mb_nick'] ?? $mb_id;
            mg_notify($log['partner_mb_id'], 'expedition',
                     '파견 파트너 보상',
                     "{$my_nick}님의 파견이 완료되어 {$partner_point}P를 받았습니다.",
                     G5_BBS_URL . '/pioneer.php?view=expedition');
        }
    }

    // 업적 트리거
    if (function_exists('mg_trigger_achievement')) {
        mg_trigger_achievement($mb_id, 'expedition_claim_count');
        if ($drop_result['has_rare']) {
            mg_trigger_achievement($mb_id, 'expedition_rare_drop_count');
        }
    }

    return array(
        'success' => true,
        'message' => '보상을 수령했습니다!',
        'rewards' => $drop_result
    );
}

/**
 * 파견 취소 (스태미나 미반환)
 */
function mg_cancel_expedition($mb_id, $el_id) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $el_id = (int)$el_id;

    $log = sql_fetch("SELECT * FROM {$g5['mg_expedition_log_table']}
                      WHERE el_id = {$el_id} AND mb_id = '{$mb_id_esc}' AND el_status = 'active'");

    if (!$log || !$log['el_id']) {
        return array('success' => false, 'message' => '취소할 수 있는 파견이 없습니다.');
    }

    sql_query("UPDATE {$g5['mg_expedition_log_table']}
               SET el_status = 'cancelled' WHERE el_id = {$el_id}");

    return array('success' => true, 'message' => '파견이 취소되었습니다. (스태미나는 반환되지 않습니다)');
}

/**
 * 파견 이력 (최근 N건)
 */
function mg_get_expedition_history($mb_id, $limit = 10) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $limit = (int)$limit;

    $sql = "SELECT el.*, ea.ea_name, ea.ea_icon, ch.ch_name,
                   pch.ch_name as partner_ch_name, pm.mb_nick as partner_nick
            FROM {$g5['mg_expedition_log_table']} el
            LEFT JOIN {$g5['mg_expedition_area_table']} ea ON el.ea_id = ea.ea_id
            LEFT JOIN {$g5['mg_character_table']} ch ON el.ch_id = ch.ch_id
            LEFT JOIN {$g5['mg_character_table']} pch ON el.partner_ch_id = pch.ch_id
            LEFT JOIN {$g5['member_table']} pm ON el.partner_mb_id = pm.mb_id
            WHERE el.mb_id = '{$mb_id_esc}' AND el.el_status IN ('claimed', 'cancelled')
            ORDER BY el.el_start DESC
            LIMIT {$limit}";
    $result = sql_query($sql);
    $history = array();
    while ($row = sql_fetch_array($result)) {
        if ($row['el_rewards']) {
            $row['el_rewards_parsed'] = json_decode($row['el_rewards'], true);
        }
        $history[] = $row;
    }
    return $history;
}

/**
 * 캐릭터의 관계 목록에서 파트너 후보 조회
 */
function mg_get_expedition_partner_candidates($ch_id) {
    global $g5;

    $ch_id = (int)$ch_id;
    $relations = mg_get_relations($ch_id, 'active');
    $candidates = array();

    foreach ($relations as $rel) {
        // 상대 캐릭터 정보 추출
        if ((int)$rel['ch_id_a'] === $ch_id) {
            $partner = array(
                'ch_id' => (int)$rel['ch_id_b'],
                'ch_name' => $rel['name_b'],
                'ch_thumb' => $rel['thumb_b'],
                'mb_id' => $rel['mb_id_b'],
                'relation_label' => $rel['ri_label'],
                'relation_icon' => $rel['ri_icon'],
            );
        } else {
            $partner = array(
                'ch_id' => (int)$rel['ch_id_a'],
                'ch_name' => $rel['name_a'],
                'ch_thumb' => $rel['thumb_a'],
                'mb_id' => $rel['mb_id_a'],
                'relation_label' => $rel['ri_label'],
                'relation_icon' => $rel['ri_icon'],
            );
        }

        // 자기 자신 캐릭터 제외
        if ($partner['mb_id'] === sql_fetch("SELECT mb_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id}")['mb_id']) {
            continue;
        }

        $candidates[] = $partner;
    }

    return $candidates;
}

/**
 * 나를 파트너로 선택한 파견 목록
 */
function mg_get_expedition_partner_history($mb_id, $limit = 10) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $limit = (int)$limit;

    $sql = "SELECT el.el_id, el.el_start, el.el_status, el.ea_id,
                   ea.ea_name, ea.ea_partner_point,
                   m.mb_nick, m.mb_id,
                   ch.ch_name, pch.ch_name as my_ch_name
            FROM {$g5['mg_expedition_log_table']} el
            LEFT JOIN {$g5['mg_expedition_area_table']} ea ON el.ea_id = ea.ea_id
            LEFT JOIN {$g5['member_table']} m ON el.mb_id = m.mb_id
            LEFT JOIN {$g5['mg_character_table']} ch ON el.ch_id = ch.ch_id
            LEFT JOIN {$g5['mg_character_table']} pch ON el.partner_ch_id = pch.ch_id
            WHERE el.partner_mb_id = '{$mb_id_esc}'
            ORDER BY el.el_start DESC
            LIMIT {$limit}";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

// ======================================
// 의뢰 매칭 시스템 (Concierge)
// ======================================

/**
 * 의뢰 목록 조회
 */
function mg_get_concierge_list($status = null, $type = null, $page = 1, $per_page = 20) {
    global $g5;

    $where = "1=1";
    if ($status) {
        $where .= " AND cc.cc_status = '" . sql_real_escape_string($status) . "'";
    }
    if ($type) {
        $where .= " AND cc.cc_type = '" . sql_real_escape_string($type) . "'";
    }

    // 만료 자동 처리
    $now = date('Y-m-d H:i:s');
    sql_query("UPDATE {$g5['mg_concierge_table']}
               SET cc_status = 'expired'
               WHERE cc_status = 'recruiting' AND cc_deadline < '{$now}'");

    $offset = ($page - 1) * $per_page;

    $total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_concierge_table']} cc WHERE {$where}");
    $total_count = (int)$total_row['cnt'];

    $sql = "SELECT cc.*, m.mb_nick, ch.ch_name, ch.ch_thumb,
                   (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id) as apply_count
            FROM {$g5['mg_concierge_table']} cc
            LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
            LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
            WHERE {$where}
            ORDER BY cc.cc_highlight DESC, cc.cc_tier = 'urgent' DESC, cc.cc_datetime DESC
            LIMIT {$offset}, {$per_page}";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }

    return array('items' => $list, 'total' => $total_count, 'page' => $page, 'total_pages' => max(1, ceil($total_count / $per_page)));
}

/**
 * 의뢰 상세 조회
 */
function mg_get_concierge($cc_id) {
    global $g5;

    $cc_id = (int)$cc_id;
    $row = sql_fetch("SELECT cc.*, m.mb_nick, ch.ch_name, ch.ch_thumb
                      FROM {$g5['mg_concierge_table']} cc
                      LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
                      LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
                      WHERE cc.cc_id = {$cc_id}");
    if (!$row || !$row['cc_id']) return null;

    // 지원자 목록
    $apply_sql = "SELECT ca.*, m.mb_nick, ch.ch_name, ch.ch_thumb
                  FROM {$g5['mg_concierge_apply_table']} ca
                  LEFT JOIN {$g5['member_table']} m ON ca.mb_id = m.mb_id
                  LEFT JOIN {$g5['mg_character_table']} ch ON ca.ch_id = ch.ch_id
                  WHERE ca.cc_id = {$cc_id}
                  ORDER BY ca.ca_datetime ASC";
    $apply_result = sql_query($apply_sql);
    $row['applies'] = array();
    while ($a = sql_fetch_array($apply_result)) {
        $row['applies'][] = $a;
    }

    // 결과물 목록
    $result_sql = "SELECT cr.*, ca.mb_id as performer_mb_id, m.mb_nick as performer_nick
                   FROM {$g5['mg_concierge_result_table']} cr
                   LEFT JOIN {$g5['mg_concierge_apply_table']} ca ON cr.ca_id = ca.ca_id
                   LEFT JOIN {$g5['member_table']} m ON ca.mb_id = m.mb_id
                   WHERE cr.cc_id = {$cc_id}";
    $res_result = sql_query($result_sql);
    $row['results'] = array();
    while ($r = sql_fetch_array($res_result)) {
        $row['results'][] = $r;
    }

    return $row;
}

/**
 * 의뢰 등록
 */
function mg_create_concierge($mb_id, $data) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $ch_id = (int)$data['ch_id'];

    // 캐릭터 소유 확인
    $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']}
                     WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id_esc}' AND ch_state = 'approved'");
    if (!$ch) {
        return array('success' => false, 'message' => '사용할 수 없는 캐릭터입니다.');
    }

    // 동시 등록 제한
    $max_slots = (int)mg_config('concierge_max_slots', 2);
    $active_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_concierge_table']}
                              WHERE mb_id = '{$mb_id_esc}' AND cc_status IN ('recruiting', 'matched')");
    if ((int)$active_row['cnt'] >= $max_slots) {
        return array('success' => false, 'message' => "동시 등록 가능한 의뢰 수({$max_slots}개)를 초과했습니다.");
    }

    $title = sql_real_escape_string(clean_xss_tags($data['cc_title']));
    $content = sql_real_escape_string(clean_xss_tags($data['cc_content'], 0, 0, 0, 0));
    $type = in_array($data['cc_type'], array('collaboration', 'illustration', 'novel', 'other')) ? $data['cc_type'] : 'collaboration';
    $max_members = max(1, min(5, (int)$data['cc_max_members']));
    $tier = in_array($data['cc_tier'], array('normal', 'urgent')) ? $data['cc_tier'] : 'normal';
    $match_mode = in_array($data['cc_match_mode'], array('direct', 'lottery')) ? $data['cc_match_mode'] : 'direct';
    $deadline = $data['cc_deadline'];

    if (empty($title)) {
        return array('success' => false, 'message' => '의뢰 제목을 입력해주세요.');
    }
    if (strtotime($deadline) <= time()) {
        return array('success' => false, 'message' => '마감일은 현재 시각 이후여야 합니다.');
    }

    // 긴급 의뢰: 포인트 선불 차감
    if ($tier === 'urgent') {
        $cost = (int)mg_config('concierge_reward_urgent', 100);
        $mb_point = (int)sql_fetch("SELECT mb_point FROM {$g5['member_table']} WHERE mb_id = '{$mb_id_esc}'")['mb_point'];
        if ($mb_point < $cost) {
            return array('success' => false, 'message' => "긴급 의뢰 등록에 {$cost}P가 필요합니다. (보유: {$mb_point}P)");
        }
        insert_point($mb_id, -$cost, '긴급 의뢰 등록 (선불)', 'mg_concierge', 0, '긴급등록');
    }

    $deadline_esc = sql_real_escape_string($deadline);
    sql_query("INSERT INTO {$g5['mg_concierge_table']}
               (mb_id, ch_id, cc_title, cc_content, cc_type, cc_max_members, cc_tier, cc_match_mode, cc_deadline)
               VALUES ('{$mb_id_esc}', {$ch_id}, '{$title}', '{$content}', '{$type}', {$max_members},
                       '{$tier}', '{$match_mode}', '{$deadline_esc}')");
    $cc_id = sql_insert_id();

    // 긴급 의뢰 포인트 rel_id 업데이트
    if ($tier === 'urgent') {
        sql_query("UPDATE {$g5['point_table']} SET po_rel_id = '{$cc_id}'
                   WHERE mb_id = '{$mb_id_esc}' AND po_rel_table = 'mg_concierge' AND po_rel_id = '0' AND po_rel_action = '긴급등록'
                   ORDER BY po_id DESC LIMIT 1");
    }

    return array('success' => true, 'message' => '의뢰가 등록되었습니다.', 'cc_id' => $cc_id);
}

/**
 * 의뢰 지원
 */
function mg_apply_concierge($mb_id, $cc_id, $ch_id, $message = '') {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $cc_id = (int)$cc_id;
    $ch_id = (int)$ch_id;

    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if (!$cc || $cc['cc_status'] !== 'recruiting') {
        return array('success' => false, 'message' => '모집 중인 의뢰가 아닙니다.');
    }

    // 본인 의뢰 지원 불가
    if ($cc['mb_id'] === $mb_id) {
        return array('success' => false, 'message' => '본인이 등록한 의뢰에는 지원할 수 없습니다.');
    }

    // 중복 지원 방지
    $existing = sql_fetch("SELECT ca_id FROM {$g5['mg_concierge_apply_table']}
                           WHERE cc_id = {$cc_id} AND mb_id = '{$mb_id_esc}'");
    if ($existing && $existing['ca_id']) {
        return array('success' => false, 'message' => '이미 지원한 의뢰입니다.');
    }

    // 캐릭터 확인
    $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']}
                     WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id_esc}' AND ch_state = 'approved'");
    if (!$ch) {
        return array('success' => false, 'message' => '사용할 수 없는 캐릭터입니다.');
    }

    $message_esc = sql_real_escape_string(clean_xss_tags($message, 0, 0, 0, 0));
    sql_query("INSERT INTO {$g5['mg_concierge_apply_table']}
               (cc_id, mb_id, ch_id, ca_message)
               VALUES ({$cc_id}, '{$mb_id_esc}', {$ch_id}, '{$message_esc}')");

    // 의뢰자에게 알림
    $my_nick = sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '{$mb_id_esc}'");
    mg_notify($cc['mb_id'], 'concierge_apply',
             '의뢰 지원 알림',
             ($my_nick['mb_nick'] ?? $mb_id) . '님이 "' . $cc['cc_title'] . '" 의뢰에 지원했습니다.',
             G5_BBS_URL . '/concierge_view.php?cc_id=' . $cc_id);

    return array('success' => true, 'message' => '지원이 완료되었습니다.');
}

/**
 * 매칭 (의뢰자가 지원자 선택)
 */
function mg_match_concierge($mb_id, $cc_id, $selected_ca_ids) {
    global $g5;

    $cc_id = (int)$cc_id;
    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if (!$cc || $cc['mb_id'] !== $mb_id) {
        return array('success' => false, 'message' => '권한이 없습니다.');
    }
    if ($cc['cc_status'] !== 'recruiting') {
        return array('success' => false, 'message' => '모집 중인 의뢰만 매칭할 수 있습니다.');
    }

    if (!is_array($selected_ca_ids) || count($selected_ca_ids) < 1) {
        return array('success' => false, 'message' => '최소 1명 이상 선택해주세요.');
    }
    if (count($selected_ca_ids) > (int)$cc['cc_max_members']) {
        return array('success' => false, 'message' => '모집 인원을 초과했습니다.');
    }

    // 선택된 지원자 승인
    foreach ($selected_ca_ids as $ca_id) {
        $ca_id = (int)$ca_id;
        sql_query("UPDATE {$g5['mg_concierge_apply_table']}
                   SET ca_status = 'selected' WHERE ca_id = {$ca_id} AND cc_id = {$cc_id}");

        $applicant = sql_fetch("SELECT mb_id FROM {$g5['mg_concierge_apply_table']} WHERE ca_id = {$ca_id}");
        if ($applicant && $applicant['mb_id']) {
            mg_notify($applicant['mb_id'], 'concierge_match',
                     '의뢰 매칭 완료',
                     '"' . $cc['cc_title'] . '" 의뢰에 선정되었습니다!',
                     G5_BBS_URL . '/concierge_view.php?cc_id=' . $cc_id);
        }
    }

    // 미선택 지원자 거절
    $selected_str = implode(',', array_map('intval', $selected_ca_ids));
    $rejected = sql_query("SELECT ca_id, mb_id FROM {$g5['mg_concierge_apply_table']}
                           WHERE cc_id = {$cc_id} AND ca_id NOT IN ({$selected_str}) AND ca_status = 'pending'");
    while ($rej = sql_fetch_array($rejected)) {
        sql_query("UPDATE {$g5['mg_concierge_apply_table']}
                   SET ca_status = 'rejected' WHERE ca_id = " . (int)$rej['ca_id']);
        mg_notify($rej['mb_id'], 'concierge_match',
                 '의뢰 매칭 결과',
                 '"' . $cc['cc_title'] . '" 의뢰에 아쉽게도 선정되지 않았습니다.',
                 G5_BBS_URL . '/concierge_view.php?cc_id=' . $cc_id);
    }

    // 의뢰 상태 변경
    sql_query("UPDATE {$g5['mg_concierge_table']} SET cc_status = 'matched' WHERE cc_id = {$cc_id}");

    return array('success' => true, 'message' => '매칭이 완료되었습니다.');
}

/**
 * 추첨 매칭
 */
function mg_lottery_concierge($mb_id, $cc_id) {
    global $g5;

    $cc_id = (int)$cc_id;
    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if (!$cc || $cc['mb_id'] !== $mb_id) {
        return array('success' => false, 'message' => '권한이 없습니다.');
    }
    if ($cc['cc_match_mode'] !== 'lottery') {
        return array('success' => false, 'message' => '추첨 모드 의뢰만 추첨할 수 있습니다.');
    }
    if ($cc['cc_status'] !== 'recruiting') {
        return array('success' => false, 'message' => '모집 중인 의뢰만 추첨할 수 있습니다.');
    }

    // 지원자 풀
    $applicants = array();
    $result = sql_query("SELECT ca_id, mb_id, ca_has_boost FROM {$g5['mg_concierge_apply_table']}
                         WHERE cc_id = {$cc_id} AND ca_status = 'pending'");
    while ($row = sql_fetch_array($result)) {
        $applicants[] = $row;
    }

    if (count($applicants) === 0) {
        return array('success' => false, 'message' => '지원자가 없습니다.');
    }

    // 가중치 풀 구성 (boost: x2)
    $pool = array();
    foreach ($applicants as $a) {
        $weight = $a['ca_has_boost'] ? 2 : 1;
        for ($i = 0; $i < $weight; $i++) {
            $pool[] = $a['ca_id'];
        }
    }

    // 추첨
    $max_members = (int)$cc['cc_max_members'];
    $selected = array();
    shuffle($pool);
    foreach ($pool as $ca_id) {
        if (!in_array($ca_id, $selected)) {
            $selected[] = $ca_id;
            if (count($selected) >= $max_members) break;
        }
    }

    return mg_match_concierge($mb_id, $cc_id, $selected);
}

/**
 * 의뢰 완료 처리 (게시판 write hook에서 호출)
 */
function mg_complete_concierge($mb_id, $cc_id, $bo_table, $wr_id) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $cc_id = (int)$cc_id;
    $bo_table_esc = sql_real_escape_string($bo_table);
    $wr_id = (int)$wr_id;

    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if (!$cc || $cc['cc_status'] !== 'matched') {
        return array('success' => false, 'message' => '매칭 완료 상태의 의뢰만 결과를 등록할 수 있습니다.');
    }

    // 수행자 확인
    $apply = sql_fetch("SELECT ca_id FROM {$g5['mg_concierge_apply_table']}
                        WHERE cc_id = {$cc_id} AND mb_id = '{$mb_id_esc}' AND ca_status = 'selected'");
    if (!$apply || !$apply['ca_id']) {
        // 의뢰자 본인이 수동 완료하는 경우
        if ($cc['mb_id'] !== $mb_id) {
            return array('success' => false, 'message' => '이 의뢰의 수행자가 아닙니다.');
        }
    }

    $ca_id = $apply ? (int)$apply['ca_id'] : 0;

    // 중복 결과 방지
    if ($ca_id > 0) {
        $existing = sql_fetch("SELECT cr_id FROM {$g5['mg_concierge_result_table']}
                               WHERE cc_id = {$cc_id} AND ca_id = {$ca_id}");
        if ($existing && $existing['cr_id']) {
            return array('success' => false, 'message' => '이미 결과를 등록한 의뢰입니다.');
        }
    }

    // 결과 등록
    sql_query("INSERT INTO {$g5['mg_concierge_result_table']}
               (cc_id, ca_id, bo_table, wr_id)
               VALUES ({$cc_id}, {$ca_id}, '{$bo_table_esc}', {$wr_id})");

    // 수행자 보상 지급
    if ($ca_id > 0) {
        $reward_key = $cc['cc_tier'] === 'urgent' ? 'concierge_reward_urgent' : 'concierge_reward_normal';
        $reward = (int)mg_config($reward_key, $cc['cc_tier'] === 'urgent' ? 100 : 50);
        if ($reward > 0) {
            insert_point($mb_id, $reward, '의뢰 수행 완료 보상 (' . $cc['cc_title'] . ')',
                        'mg_concierge', $cc_id, '수행완료');
            mg_notify($mb_id, 'concierge_reward',
                     '의뢰 보상 지급',
                     '"' . $cc['cc_title'] . '" 의뢰 수행 보상 ' . $reward . 'P가 지급되었습니다.',
                     G5_BBS_URL . '/concierge_view.php?cc_id=' . $cc_id);
        }
    }

    // 모든 수행자 결과 등록 확인 → 의뢰 완료
    $selected_count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_concierge_apply_table']}
                                 WHERE cc_id = {$cc_id} AND ca_status = 'selected'");
    $result_count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_concierge_result_table']}
                               WHERE cc_id = {$cc_id}");

    // 의뢰자 본인 완료이거나 모든 수행자 결과 등록 시
    if ($cc['mb_id'] === $mb_id || (int)$result_count['cnt'] >= (int)$selected_count['cnt']) {
        sql_query("UPDATE {$g5['mg_concierge_table']} SET cc_status = 'completed' WHERE cc_id = {$cc_id}");

        mg_notify($cc['mb_id'], 'concierge_complete',
                 '의뢰 완료',
                 '"' . $cc['cc_title'] . '" 의뢰가 완료되었습니다.',
                 G5_BBS_URL . '/concierge_view.php?cc_id=' . $cc_id);
    }

    if (function_exists('mg_trigger_achievement')) {
        mg_trigger_achievement($mb_id, 'concierge_complete_count');
    }

    return array('success' => true, 'message' => '의뢰 결과가 등록되었습니다.');
}

/**
 * 의뢰 취소
 */
function mg_cancel_concierge($mb_id, $cc_id) {
    global $g5;

    $cc_id = (int)$cc_id;
    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if (!$cc || $cc['mb_id'] !== $mb_id) {
        return array('success' => false, 'message' => '권한이 없습니다.');
    }
    if (!in_array($cc['cc_status'], array('recruiting', 'matched'))) {
        return array('success' => false, 'message' => '취소할 수 없는 상태입니다.');
    }

    sql_query("UPDATE {$g5['mg_concierge_table']} SET cc_status = 'cancelled' WHERE cc_id = {$cc_id}");

    // 긴급 의뢰 환불
    if ($cc['cc_tier'] === 'urgent') {
        $cost = (int)mg_config('concierge_reward_urgent', 100);
        insert_point($mb_id, $cost, '긴급 의뢰 취소 환불 (' . $cc['cc_title'] . ')',
                    'mg_concierge', $cc_id, '환불');
    }

    return array('success' => true, 'message' => '의뢰가 취소되었습니다.' . ($cc['cc_tier'] === 'urgent' ? ' 선불 포인트가 환불됩니다.' : ''));
}

/**
 * 현재 유저의 수행 중인(matched) 의뢰 목록 (write hook용)
 */
function mg_get_my_matched_concierges($mb_id) {
    global $g5;

    $mb_id_esc = sql_real_escape_string($mb_id);
    $sql = "SELECT cc.cc_id, cc.cc_title, cc.cc_type
            FROM {$g5['mg_concierge_apply_table']} ca
            JOIN {$g5['mg_concierge_table']} cc ON ca.cc_id = cc.cc_id
            WHERE ca.mb_id = '{$mb_id_esc}' AND ca.ca_status = 'selected' AND cc.cc_status = 'matched'";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        // 이미 결과 등록한 의뢰 제외
        $existing = sql_fetch("SELECT cr_id FROM {$g5['mg_concierge_result_table']}
                               WHERE cc_id = {$row['cc_id']} AND ca_id = (SELECT ca_id FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = {$row['cc_id']} AND mb_id = '{$mb_id_esc}' AND ca_status = 'selected' LIMIT 1)");
        if (!$existing || !$existing['cr_id']) {
            $list[] = $row;
        }
    }

    // 의뢰자 본인이 수동 완료 가능한 의뢰도 포함
    $own_sql = "SELECT cc_id, cc_title, cc_type FROM {$g5['mg_concierge_table']}
                WHERE mb_id = '{$mb_id_esc}' AND cc_status = 'matched'";
    $own_result = sql_query($own_sql);
    while ($row = sql_fetch_array($own_result)) {
        $found = false;
        foreach ($list as $l) {
            if ((int)$l['cc_id'] === (int)$row['cc_id']) { $found = true; break; }
        }
        if (!$found) {
            $row['is_owner'] = true;
            $list[] = $row;
        }
    }

    return $list;
}

// ======================================
// 보상 시스템
// ======================================

/**
 * 게시판별 보상 설정 조회
 */
function mg_get_board_reward($bo_table) {
    global $g5;
    static $cache = array();

    if (!isset($cache[$bo_table])) {
        $bo_table_esc = sql_real_escape_string($bo_table);
        $cache[$bo_table] = sql_fetch("SELECT * FROM {$g5['mg_board_reward_table']} WHERE bo_table = '{$bo_table_esc}'");
    }

    return $cache[$bo_table] ?: null;
}

/**
 * 게시판별 보상 적용 (Auto 모드)
 *
 * @param string $mb_id 회원 ID
 * @param string $bo_table 게시판
 * @param int $content_len 글자수 (strip_tags 후)
 * @param bool $has_image 이미지 첨부 여부
 * @param int $wr_id 게시글 ID
 * @return bool 보상 적용 여부
 */
function mg_apply_board_reward($mb_id, $bo_table, $content_len, $has_image, $wr_id) {
    global $g5;

    $br = mg_get_board_reward($bo_table);
    if (!$br || $br['br_mode'] !== 'auto') return false;

    // 일일 제한 체크
    if ($br['br_daily_limit'] > 0) {
        $today = date('Y-m-d');
        $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['point_table']}
            WHERE mb_id = '".sql_real_escape_string($mb_id)."'
            AND po_rel_table = '".sql_real_escape_string($bo_table)."'
            AND po_rel_action = '쓰기'
            AND po_datetime >= '{$today} 00:00:00'");
        if (($cnt['cnt'] ?? 0) >= $br['br_daily_limit']) return true; // 적용됨 (but 제한 도달)
    }

    // 포인트 계산
    $point = (int)$br['br_point'];
    if ($content_len >= 1000 && (int)$br['br_bonus_1000'] > 0) {
        $point += (int)$br['br_bonus_1000'];
    } elseif ($content_len >= 500 && (int)$br['br_bonus_500'] > 0) {
        $point += (int)$br['br_bonus_500'];
    }
    if ($has_image && (int)$br['br_bonus_image'] > 0) {
        $point += (int)$br['br_bonus_image'];
    }

    // 포인트 지급
    if ($point > 0) {
        $board = sql_fetch("SELECT bo_subject FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
        $bo_subject = $board['bo_subject'] ?? $bo_table;
        insert_point($mb_id, $point, "{$bo_subject} {$wr_id} 글쓰기", $bo_table, $wr_id, '쓰기');
    }

    // 재료 드롭
    if ($br['br_material_use'] && (int)$br['br_material_chance'] > 0) {
        if (mt_rand(1, 100) <= (int)$br['br_material_chance']) {
            $mat_list = $br['br_material_list'] ? json_decode($br['br_material_list'], true) : array();
            if (!empty($mat_list)) {
                $code = $mat_list[array_rand($mat_list)];
                $mt = mg_get_material_type_by_code($code);
                if ($mt) {
                    mg_add_material($mb_id, $mt['mt_id'], 1);
                }
            }
        }
    }

    return true;
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
    if (!$facility || !$facility['fc_id']) {
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

    if (!$facility || !$facility['fc_id']) {
        return null;
    }

    // 진행도 계산
    $facility['progress'] = mg_get_facility_progress($facility);

    return $facility;
}

// ======================================
// 좋아요 보상 함수
// ======================================

/**
 * 일일 좋아요 현황 조회
 * @param string $mb_id
 * @return array ['count' => int, 'targets' => array]
 */
function mg_like_get_daily($mb_id) {
    global $g5;

    $today = date('Y-m-d');
    $row = sql_fetch("SELECT * FROM {$g5['mg_like_daily_table']}
        WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND ld_date = '{$today}'");

    if (!$row['ld_id']) {
        return array('count' => 0, 'targets' => array());
    }

    $targets = json_decode($row['ld_targets'], true);
    if (!is_array($targets)) $targets = array();

    return array('count' => (int)$row['ld_count'], 'targets' => $targets);
}

/**
 * 좋아요 보상 처리
 * @param string $mb_id 좋아요 누른 회원
 * @param string $target_mb_id 좋아요 받은 회원
 * @param string $bo_table 게시판
 * @param int $wr_id 게시글 ID
 * @return array
 */
function mg_like_apply_reward($mb_id, $target_mb_id, $bo_table, $wr_id) {
    global $g5;

    // 게시판별 좋아요 보상 비활성화 체크
    $_br = sql_fetch("SELECT br_like_use FROM {$g5['mg_board_reward_table']}
        WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
    if ($_br && !$_br['br_like_use']) {
        return array('success' => false, 'message' => '이 게시판은 좋아요 보상이 비활성화되어 있습니다.', 'remaining' => 0);
    }

    $limit = (int)mg_config('like_daily_limit', 5);
    $giver_point = (int)mg_config('like_giver_point', 10);
    $receiver_point = (int)mg_config('like_receiver_point', 30);

    // 보상 비활성화 (한도 0 또는 양쪽 포인트 모두 0)
    if ($limit <= 0 || ($giver_point <= 0 && $receiver_point <= 0)) {
        return array('success' => false, 'message' => '좋아요 보상이 비활성화되어 있습니다.', 'remaining' => 0);
    }

    // 자기 글 좋아요 보상 없음
    if ($mb_id === $target_mb_id) {
        return array('success' => false, 'message' => '자기 글에는 보상이 지급되지 않습니다.', 'remaining' => 0);
    }

    $daily = mg_like_get_daily($mb_id);
    $remaining = $limit - $daily['count'];

    // 일일 횟수 소진
    if ($remaining <= 0) {
        return array('success' => false, 'message' => '일일 좋아요 보상 횟수를 모두 사용했습니다.', 'remaining' => 0);
    }

    // 동일 대상 1일 1회 (보상만 skip, 횟수 미소모)
    if (in_array($target_mb_id, $daily['targets'])) {
        return array('success' => false, 'message' => '같은 회원에게는 하루 1회만 보상이 지급됩니다.', 'remaining' => $remaining, 'already_target' => true);
    }

    // 포인트 지급
    if ($giver_point > 0) {
        insert_point($mb_id, $giver_point, '좋아요 보상 (누른 사람)', 'mg_like_log', $wr_id, '좋아요');
    }
    if ($receiver_point > 0) {
        insert_point($target_mb_id, $receiver_point, '좋아요 보상 (받은 사람)', 'mg_like_log', $wr_id, '좋아요');
    }

    // 로그 기록
    $mb_esc = sql_real_escape_string($mb_id);
    $target_esc = sql_real_escape_string($target_mb_id);
    $bo_esc = sql_real_escape_string($bo_table);
    sql_query("INSERT INTO {$g5['mg_like_log_table']}
        (mb_id, target_mb_id, bo_table, wr_id, ll_giver_point, ll_receiver_point)
        VALUES ('{$mb_esc}', '{$target_esc}', '{$bo_esc}', {$wr_id}, {$giver_point}, {$receiver_point})");

    // 일일 카운터 업데이트
    $daily['targets'][] = $target_mb_id;
    $targets_json = sql_real_escape_string(json_encode($daily['targets']));
    $new_count = $daily['count'] + 1;
    $today = date('Y-m-d');
    sql_query("INSERT INTO {$g5['mg_like_daily_table']} (mb_id, ld_date, ld_count, ld_targets)
        VALUES ('{$mb_esc}', '{$today}', {$new_count}, '{$targets_json}')
        ON DUPLICATE KEY UPDATE ld_count = {$new_count}, ld_targets = '{$targets_json}'");

    // 업적 트리거 (좋아요)
    mg_trigger_achievement($mb_id, 'like_count');

    $remaining = $limit - $new_count;

    return array(
        'success' => true,
        'giver_point' => $giver_point,
        'receiver_point' => $receiver_point,
        'remaining' => $remaining
    );
}

// ======================================
// 정산 시스템 (Request 모드) 함수
// ======================================

/**
 * 게시판용 보상 유형 목록 조회
 * @param string $bo_table 게시판 (해당 게시판 + 공통 유형)
 * @return array
 */
function mg_get_reward_types($bo_table) {
    global $g5;

    $bo_esc = sql_real_escape_string($bo_table);
    $result = sql_query("SELECT * FROM {$g5['mg_reward_type_table']}
        WHERE (bo_table = '{$bo_esc}' OR bo_table IS NULL) AND rwt_use = 1
        ORDER BY rwt_order, rwt_id");

    $types = array();
    if ($result) {
        while ($row = sql_fetch_array($result)) {
            $types[] = $row;
        }
    }
    return $types;
}

/**
 * 정산 큐에 보상 요청 등록
 * @param string $mb_id
 * @param string $bo_table
 * @param int $wr_id
 * @param int $rwt_id
 * @return array
 */
function mg_add_reward_queue($mb_id, $bo_table, $wr_id, $rwt_id) {
    global $g5;

    // 유형 유효성 체크
    $rwt = sql_fetch("SELECT * FROM {$g5['mg_reward_type_table']} WHERE rwt_id = {$rwt_id} AND rwt_use = 1");
    if (!$rwt['rwt_id']) {
        return array('success' => false, 'message' => '유효하지 않은 보상 유형입니다.');
    }

    $mb_esc = sql_real_escape_string($mb_id);
    $bo_esc = sql_real_escape_string($bo_table);
    sql_query("INSERT INTO {$g5['mg_reward_queue_table']}
        (mb_id, bo_table, wr_id, rwt_id)
        VALUES ('{$mb_esc}', '{$bo_esc}', {$wr_id}, {$rwt_id})");

    return array('success' => true);
}

/**
 * 보상 요청 승인
 * @param int $rq_id
 * @param string $admin_mb_id
 * @return array
 */
function mg_approve_reward($rq_id, $admin_mb_id) {
    global $g5;

    $rq = sql_fetch("SELECT * FROM {$g5['mg_reward_queue_table']} WHERE rq_id = {$rq_id}");
    if (!$rq['rq_id']) {
        return array('success' => false, 'message' => '요청을 찾을 수 없습니다.');
    }
    if ($rq['rq_status'] !== 'pending') {
        return array('success' => false, 'message' => '이미 처리된 요청입니다.');
    }

    // 보상 유형 조회
    $rwt = sql_fetch("SELECT * FROM {$g5['mg_reward_type_table']} WHERE rwt_id = {$rq['rwt_id']}");
    if (!$rwt['rwt_id']) {
        return array('success' => false, 'message' => '보상 유형을 찾을 수 없습니다.');
    }

    $point = (int)$rwt['rwt_point'];

    // 포인트 지급
    if ($point > 0) {
        $board = sql_fetch("SELECT bo_subject FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($rq['bo_table'])."'");
        $bo_subject = $board['bo_subject'] ?: $rq['bo_table'];
        insert_point($rq['mb_id'], $point, "{$bo_subject} {$rq['wr_id']} 보상 승인 ({$rwt['rwt_name']})", $rq['bo_table'], $rq['wr_id'], '보상');
    }

    // 재료 지급
    if ($rwt['rwt_material']) {
        $materials = json_decode($rwt['rwt_material'], true);
        if (is_array($materials) && function_exists('mg_add_material')) {
            foreach ($materials as $mat) {
                $mt_code = isset($mat['mt_code']) ? $mat['mt_code'] : '';
                $amount = isset($mat['amount']) ? (int)$mat['amount'] : 0;
                if ($mt_code && $amount > 0) {
                    $mt = mg_get_material_type_by_code($mt_code);
                    if ($mt) {
                        mg_add_material($rq['mb_id'], $mt['mt_id'], $amount);
                    }
                }
            }
        }
    }

    // 상태 업데이트
    $admin_esc = sql_real_escape_string($admin_mb_id);
    sql_query("UPDATE {$g5['mg_reward_queue_table']}
        SET rq_status = 'approved', rq_process_datetime = NOW(), rq_process_mb_id = '{$admin_esc}'
        WHERE rq_id = {$rq_id}");

    // 알림
    if (function_exists('mg_notify')) {
        $url = get_pretty_url($rq['bo_table'], $rq['wr_id']);
        $extra = $rwt['rwt_name'];
        if ($point > 0) $extra .= " (+{$point}P)";
        mg_notify($rq['mb_id'], 'reward', '보상이 승인되었습니다', $extra, $url);
    }

    return array('success' => true, 'point' => $point, 'message' => "승인 완료 (+{$point}P)");
}

/**
 * 보상 요청 반려
 * @param int $rq_id
 * @param string $admin_mb_id
 * @param string $reason
 * @return array
 */
function mg_reject_reward($rq_id, $admin_mb_id, $reason = '') {
    global $g5;

    $rq = sql_fetch("SELECT * FROM {$g5['mg_reward_queue_table']} WHERE rq_id = {$rq_id}");
    if (!$rq['rq_id']) {
        return array('success' => false, 'message' => '요청을 찾을 수 없습니다.');
    }
    if ($rq['rq_status'] !== 'pending') {
        return array('success' => false, 'message' => '이미 처리된 요청입니다.');
    }

    $admin_esc = sql_real_escape_string($admin_mb_id);
    $reason_esc = sql_real_escape_string($reason);
    sql_query("UPDATE {$g5['mg_reward_queue_table']}
        SET rq_status = 'rejected', rq_process_datetime = NOW(),
            rq_process_mb_id = '{$admin_esc}', rq_reject_reason = '{$reason_esc}'
        WHERE rq_id = {$rq_id}");

    // 알림
    if (function_exists('mg_notify')) {
        $url = get_pretty_url($rq['bo_table'], $rq['wr_id']);
        $extra = $reason ? '사유: '.$reason : '';
        mg_notify($rq['mb_id'], 'reward', '보상 요청이 반려되었습니다', $extra, $url);
    }

    return array('success' => true, 'message' => '반려 처리되었습니다.');
}

// ======================================
// 업적 시스템 (Achievement System)
// ======================================

/**
 * 업적 목록 조회 (카테고리 필터, 사용 중인 것만)
 */
function mg_get_achievements($category = '', $include_disabled = false)
{
    global $g5;
    $where = $include_disabled ? '1' : 'ac_use = 1';
    if ($category) {
        $where .= " AND ac_category = '".sql_real_escape_string($category)."'";
    }
    $sql = "SELECT * FROM {$g5['mg_achievement_table']} WHERE {$where} ORDER BY ac_category, ac_order, ac_id";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

/**
 * 업적 단계 목록 조회
 */
function mg_get_achievement_tiers($ac_id)
{
    global $g5;
    $ac_id = (int)$ac_id;
    $sql = "SELECT * FROM {$g5['mg_achievement_tier_table']} WHERE ac_id = {$ac_id} ORDER BY at_level";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

/**
 * 유저의 전체 업적 달성 현황 조회
 */
function mg_get_user_achievements($mb_id)
{
    global $g5;
    $mb_esc = sql_real_escape_string($mb_id);
    $sql = "SELECT a.*, ua.ua_progress, ua.ua_tier, ua.ua_completed, ua.ua_granted_by, ua.ua_grant_memo, ua.ua_datetime AS ua_datetime
            FROM {$g5['mg_achievement_table']} a
            LEFT JOIN {$g5['mg_user_achievement_table']} ua ON a.ac_id = ua.ac_id AND ua.mb_id = '{$mb_esc}'
            WHERE a.ac_use = 1
            ORDER BY a.ac_category, a.ac_order, a.ac_id";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $row['ua_progress'] = (int)($row['ua_progress'] ?? 0);
        $row['ua_tier'] = (int)($row['ua_tier'] ?? 0);
        $row['ua_completed'] = (int)($row['ua_completed'] ?? 0);
        $list[] = $row;
    }
    return $list;
}

/**
 * 유저의 프로필 쇼케이스 조회
 */
function mg_get_achievement_display($mb_id)
{
    global $g5;
    $mb_esc = sql_real_escape_string($mb_id);
    $row = sql_fetch("SELECT * FROM {$g5['mg_user_achievement_display_table']} WHERE mb_id = '{$mb_esc}'");
    if (!$row['mb_id']) return array();

    $slots = array();
    for ($i = 1; $i <= 5; $i++) {
        $ac_id = (int)($row['slot_'.$i] ?? 0);
        if ($ac_id) {
            $ac = sql_fetch("SELECT a.*, at2.at_name AS tier_name, at2.at_icon AS tier_icon
                FROM {$g5['mg_achievement_table']} a
                LEFT JOIN {$g5['mg_user_achievement_table']} ua ON a.ac_id = ua.ac_id AND ua.mb_id = '{$mb_esc}'
                LEFT JOIN {$g5['mg_achievement_tier_table']} at2 ON a.ac_id = at2.ac_id AND at2.at_level = ua.ua_tier
                WHERE a.ac_id = {$ac_id}");
            if ($ac['ac_id']) {
                $slots[] = $ac;
            }
        }
    }
    return $slots;
}

/**
 * 프로필 쇼케이스 저장
 */
function mg_save_achievement_display($mb_id, $slot_ids)
{
    global $g5;
    $mb_esc = sql_real_escape_string($mb_id);
    $slots = array();
    for ($i = 0; $i < 5; $i++) {
        $v = isset($slot_ids[$i]) ? (int)$slot_ids[$i] : 0;
        $slots[] = $v ? $v : 'NULL';
    }
    sql_query("INSERT INTO {$g5['mg_user_achievement_display_table']}
        (mb_id, slot_1, slot_2, slot_3, slot_4, slot_5)
        VALUES ('{$mb_esc}', {$slots[0]}, {$slots[1]}, {$slots[2]}, {$slots[3]}, {$slots[4]})
        ON DUPLICATE KEY UPDATE
        slot_1 = {$slots[0]}, slot_2 = {$slots[1]}, slot_3 = {$slots[2]},
        slot_4 = {$slots[3]}, slot_5 = {$slots[4]}");
}

/**
 * 업적 쇼케이스 렌더링 HTML
 */
function mg_render_achievement_showcase($mb_id)
{
    $slots = mg_get_achievement_display($mb_id);
    if (empty($slots)) return '';

    $html = '<div class="mg-achievement-showcase flex gap-2 mt-2">';
    foreach ($slots as $ac) {
        $icon = $ac['tier_icon'] ?: ($ac['ac_icon'] ?: '');
        $name = $ac['tier_name'] ?: $ac['ac_name'];
        $rarity = $ac['ac_rarity'] ?: 'common';
        $html .= '<div class="mg-trophy mg-trophy-'.$rarity.'" title="'.htmlspecialchars($name).'">';
        if ($icon) {
            $html .= '<img src="'.htmlspecialchars($icon).'" alt="'.htmlspecialchars($name).'" class="w-8 h-8">';
        } else {
            $html .= '<span class="text-xl">🏆</span>';
        }
        $html .= '<span class="text-xs truncate block text-center mt-1">'.htmlspecialchars($name).'</span>';
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

/**
 * 업적 트리거 - 이벤트 발생 시 관련 업적 진행도 갱신 + 달성 판정
 *
 * @param string $mb_id 회원 ID
 * @param string $event_type 이벤트 타입 (write_count, comment_count, rp_reply_count, ...)
 * @param int $increment 증가량 (기본 1)
 * @param array $context 추가 컨텍스트 (board, facility_id 등)
 */
function mg_trigger_achievement($mb_id, $event_type, $increment = 1, $context = array())
{
    global $g5;
    if (!$mb_id) return;

    $mb_esc = sql_real_escape_string($mb_id);

    // 해당 event_type에 관련된 활성 업적 목록 조회
    $sql = "SELECT * FROM {$g5['mg_achievement_table']}
            WHERE ac_use = 1 AND ac_condition LIKE '%\"type\":\"{$event_type}\"%'";
    $result = sql_query($sql);

    while ($ac = sql_fetch_array($result)) {
        $condition = json_decode($ac['ac_condition'], true);
        if (!$condition || ($condition['type'] ?? '') !== $event_type) continue;

        // 게시판 한정 체크
        if (!empty($condition['board']) && (!isset($context['board']) || $context['board'] !== $condition['board'])) {
            continue;
        }

        // 현재 유저 진행 상태 조회/생성
        $ua = sql_fetch("SELECT * FROM {$g5['mg_user_achievement_table']}
            WHERE mb_id = '{$mb_esc}' AND ac_id = {$ac['ac_id']}");

        $progress = (int)($ua['ua_progress'] ?? 0);
        $current_tier = (int)($ua['ua_tier'] ?? 0);
        $completed = (int)($ua['ua_completed'] ?? 0);

        // 이미 완전 달성 → 스킵
        if ($completed) continue;

        // 진행도 갱신
        $new_progress = $progress + $increment;

        if ($ac['ac_type'] === 'onetime') {
            // 일회성: target 도달 시 달성
            $target = (int)($condition['target'] ?? 1);
            $newly_completed = ($new_progress >= $target) ? 1 : 0;

            if ($ua['ua_id']) {
                sql_query("UPDATE {$g5['mg_user_achievement_table']}
                    SET ua_progress = {$new_progress}, ua_completed = {$newly_completed},
                        ua_tier = ".($newly_completed ? 1 : 0).", ua_datetime = NOW()
                    WHERE ua_id = {$ua['ua_id']}");
            } else {
                sql_query("INSERT INTO {$g5['mg_user_achievement_table']}
                    (mb_id, ac_id, ua_progress, ua_tier, ua_completed, ua_datetime)
                    VALUES ('{$mb_esc}', {$ac['ac_id']}, {$new_progress}, ".($newly_completed ? 1 : 0).", {$newly_completed}, NOW())");
            }

            if ($newly_completed) {
                mg_achievement_give_reward($mb_id, $ac, null);
                mg_achievement_notify($mb_id, $ac, null);
            }

        } else {
            // 단계형: 다음 단계 도달 여부 체크
            $tiers = mg_get_achievement_tiers($ac['ac_id']);
            $new_tier = $current_tier;
            $newly_completed = 0;
            $reached_tier = null;

            foreach ($tiers as $tier) {
                if ((int)$tier['at_level'] <= $current_tier) continue;
                if ($new_progress >= (int)$tier['at_target']) {
                    $new_tier = (int)$tier['at_level'];
                    $reached_tier = $tier;
                } else {
                    break;
                }
            }

            // 최종 단계 달성 여부
            if ($reached_tier && !empty($tiers)) {
                $last_tier = end($tiers);
                if ($new_tier >= (int)$last_tier['at_level']) {
                    $newly_completed = 1;
                }
            }

            if ($ua['ua_id']) {
                sql_query("UPDATE {$g5['mg_user_achievement_table']}
                    SET ua_progress = {$new_progress}, ua_tier = {$new_tier},
                        ua_completed = {$newly_completed}, ua_datetime = NOW()
                    WHERE ua_id = {$ua['ua_id']}");
            } else {
                sql_query("INSERT INTO {$g5['mg_user_achievement_table']}
                    (mb_id, ac_id, ua_progress, ua_tier, ua_completed, ua_datetime)
                    VALUES ('{$mb_esc}', {$ac['ac_id']}, {$new_progress}, {$new_tier}, {$newly_completed}, NOW())");
            }

            // 새 단계 달성 시 보상 + 알림 (여러 단계 한번에 넘을 수 있으므로 차이만큼)
            if ($reached_tier && $new_tier > $current_tier) {
                foreach ($tiers as $tier) {
                    if ((int)$tier['at_level'] > $current_tier && (int)$tier['at_level'] <= $new_tier) {
                        mg_achievement_give_reward($mb_id, $ac, $tier);
                        mg_achievement_notify($mb_id, $ac, $tier);
                    }
                }
            }
        }
    }
}

/**
 * 업적 보상 지급
 */
function mg_achievement_give_reward($mb_id, $ac, $tier = null)
{
    global $g5;
    $reward_json = $tier ? ($tier['at_reward'] ?? '') : ($ac['ac_reward'] ?? '');
    if (!$reward_json) return;

    $rewards = json_decode($reward_json, true);
    if (!$rewards) return;

    // 단일 보상이면 배열로 감싸기
    if (isset($rewards['type'])) {
        $rewards = array($rewards);
    }

    $name = $tier ? $tier['at_name'] : $ac['ac_name'];

    foreach ($rewards as $r) {
        $type = $r['type'] ?? '';
        switch ($type) {
            case 'point':
                $amount = (int)($r['amount'] ?? 0);
                if ($amount > 0) {
                    insert_point($mb_id, $amount, "업적 달성: {$name}", 'mg_achievement', $ac['ac_id'], 'achievement');
                }
                break;

            case 'material':
                $mt_code = $r['mt_code'] ?? '';
                $amount = (int)($r['amount'] ?? 1);
                if ($mt_code && $amount > 0 && function_exists('mg_add_material')) {
                    mg_add_material($mb_id, $mt_code, $amount);
                }
                break;

            case 'item':
                $si_id = (int)($r['si_id'] ?? 0);
                if ($si_id > 0) {
                    $mb_esc = sql_real_escape_string($mb_id);
                    $existing = sql_fetch("SELECT iv_id, iv_count FROM {$g5['mg_inventory_table']}
                        WHERE mb_id = '{$mb_esc}' AND si_id = {$si_id}");
                    if ($existing['iv_id']) {
                        sql_query("UPDATE {$g5['mg_inventory_table']} SET iv_count = iv_count + 1 WHERE iv_id = {$existing['iv_id']}");
                    } else {
                        sql_query("INSERT INTO {$g5['mg_inventory_table']} (mb_id, si_id, iv_count, iv_datetime)
                            VALUES ('{$mb_esc}', {$si_id}, 1, NOW())");
                    }
                }
                break;
        }
    }
}

/**
 * 업적 달성 알림 발송
 */
function mg_achievement_notify($mb_id, $ac, $tier = null)
{
    if (!function_exists('mg_notify')) return;
    $name = $tier ? $tier['at_name'] : $ac['ac_name'];
    $desc = $ac['ac_desc'] ?: '';
    mg_notify($mb_id, 'achievement', '업적 달성: '.$name, $desc, G5_BBS_URL.'/achievement.php');

    // 토스트 알림용 세션 저장 (다음 페이지 로드 시 표시)
    if (isset($_SESSION) && isset($_SESSION['ss_mb_id']) && $_SESSION['ss_mb_id'] === $mb_id) {
        $reward_desc = '';
        $reward_json = $tier ? ($tier['at_reward'] ?? '{}') : ($ac['ac_reward'] ?? '{}');
        $reward = json_decode($reward_json, true);
        if (!empty($reward['type'])) {
            if ($reward['type'] === 'point') $reward_desc = '+' . number_format($reward['amount'] ?? 0) . 'P';
            elseif ($reward['type'] === 'material') $reward_desc = ($reward['mt_code'] ?? '') . ' x' . ($reward['amount'] ?? 0);
        }
        $_SESSION['mg_achievement_toast'] = array(
            'name' => $name,
            'desc' => $desc,
            'icon' => ($tier && !empty($tier['at_icon'])) ? $tier['at_icon'] : ($ac['ac_icon'] ?? ''),
            'rarity' => $ac['ac_rarity'] ?? 'common',
            'reward' => $reward_desc,
        );
    }
}

/**
 * 관리자 수동 업적 부여
 *
 * @param string|array $mb_ids 회원 ID 또는 배열
 * @param int $ac_id 업적 ID
 * @param string $admin_mb_id 부여자
 * @param string $memo 부여 사유
 * @param bool $give_reward 보상 지급 여부
 * @return array ['success', 'message', 'granted', 'skipped']
 */
function mg_grant_achievement($mb_ids, $ac_id, $admin_mb_id, $memo = '', $give_reward = true)
{
    global $g5;
    $ac_id = (int)$ac_id;
    $ac = sql_fetch("SELECT * FROM {$g5['mg_achievement_table']} WHERE ac_id = {$ac_id}");
    if (!$ac['ac_id']) {
        return array('success' => false, 'message' => '업적을 찾을 수 없습니다.');
    }

    if (!is_array($mb_ids)) $mb_ids = array($mb_ids);

    $admin_esc = sql_real_escape_string($admin_mb_id);
    $memo_esc = sql_real_escape_string($memo);
    $granted = 0;
    $skipped = 0;

    foreach ($mb_ids as $mb_id) {
        $mb_esc = sql_real_escape_string($mb_id);

        // 이미 완전 달성 여부 확인
        $ua = sql_fetch("SELECT * FROM {$g5['mg_user_achievement_table']}
            WHERE mb_id = '{$mb_esc}' AND ac_id = {$ac_id}");

        if ($ua['ua_id'] && (int)$ua['ua_completed']) {
            $skipped++;
            continue;
        }

        // 단계형이면 최종 단계로, 일회성이면 바로 완료
        $target_tier = 0;
        $target_progress = 0;
        if ($ac['ac_type'] === 'progressive') {
            $tiers = mg_get_achievement_tiers($ac_id);
            if (!empty($tiers)) {
                $last = end($tiers);
                $target_tier = (int)$last['at_level'];
                $target_progress = (int)$last['at_target'];
            }
        } else {
            $cond = json_decode($ac['ac_condition'], true);
            $target_tier = 1;
            $target_progress = (int)($cond['target'] ?? 1);
        }

        if ($ua['ua_id']) {
            sql_query("UPDATE {$g5['mg_user_achievement_table']}
                SET ua_progress = {$target_progress}, ua_tier = {$target_tier}, ua_completed = 1,
                    ua_granted_by = '{$admin_esc}', ua_grant_memo = '{$memo_esc}', ua_datetime = NOW()
                WHERE ua_id = {$ua['ua_id']}");
        } else {
            sql_query("INSERT INTO {$g5['mg_user_achievement_table']}
                (mb_id, ac_id, ua_progress, ua_tier, ua_completed, ua_granted_by, ua_grant_memo, ua_datetime)
                VALUES ('{$mb_esc}', {$ac_id}, {$target_progress}, {$target_tier}, 1,
                        '{$admin_esc}', '{$memo_esc}', NOW())");
        }

        // 보상 지급
        if ($give_reward) {
            if ($ac['ac_type'] === 'progressive') {
                $tiers = mg_get_achievement_tiers($ac_id);
                $old_tier = (int)($ua['ua_tier'] ?? 0);
                foreach ($tiers as $tier) {
                    if ((int)$tier['at_level'] > $old_tier) {
                        mg_achievement_give_reward($mb_id, $ac, $tier);
                    }
                }
            } else {
                mg_achievement_give_reward($mb_id, $ac, null);
            }
        }

        // 알림
        mg_achievement_notify($mb_id, $ac, null);
        $granted++;
    }

    return array('success' => true, 'message' => "{$granted}명 부여, {$skipped}명 건너뜀", 'granted' => $granted, 'skipped' => $skipped);
}

/**
 * 관리자 업적 회수
 */
function mg_revoke_achievement($mb_id, $ac_id, $admin_mb_id)
{
    global $g5;
    $ac_id = (int)$ac_id;
    $mb_esc = sql_real_escape_string($mb_id);

    $ua = sql_fetch("SELECT * FROM {$g5['mg_user_achievement_table']}
        WHERE mb_id = '{$mb_esc}' AND ac_id = {$ac_id}");
    if (!$ua['ua_id']) {
        return array('success' => false, 'message' => '달성 기록이 없습니다.');
    }

    // 진행도 초기화
    sql_query("UPDATE {$g5['mg_user_achievement_table']}
        SET ua_progress = 0, ua_tier = 0, ua_completed = 0,
            ua_granted_by = NULL, ua_grant_memo = NULL, ua_datetime = NOW()
        WHERE ua_id = {$ua['ua_id']}");

    // 쇼케이스에서 제거
    sql_query("UPDATE {$g5['mg_user_achievement_display_table']}
        SET slot_1 = IF(slot_1={$ac_id}, NULL, slot_1),
            slot_2 = IF(slot_2={$ac_id}, NULL, slot_2),
            slot_3 = IF(slot_3={$ac_id}, NULL, slot_3),
            slot_4 = IF(slot_4={$ac_id}, NULL, slot_4),
            slot_5 = IF(slot_5={$ac_id}, NULL, slot_5)
        WHERE mb_id = '{$mb_esc}'");

    return array('success' => true, 'message' => '업적이 회수되었습니다.');
}

/**
 * 업적 카테고리 목록
 */
function mg_achievement_categories()
{
    return array(
        'activity'   => '활동',
        'rp'         => '역극',
        'pioneer'    => '개척',
        'social'     => '소셜',
        'collection' => '수집',
        'special'    => '특수',
    );
}

/**
 * 업적 통계 (전체 유저 대비 달성률)
 */
function mg_get_achievement_stats($ac_id)
{
    global $g5;
    $ac_id = (int)$ac_id;
    $total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['member_table']} WHERE mb_level >= 2");
    $achieved = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_user_achievement_table']}
        WHERE ac_id = {$ac_id} AND (ua_completed = 1 OR ua_tier > 0)");
    $total_cnt = max(1, (int)$total['cnt']);
    $achieved_cnt = (int)$achieved['cnt'];
    return array(
        'total' => $total_cnt,
        'achieved' => $achieved_cnt,
        'rate' => round($achieved_cnt / $total_cnt * 100, 1)
    );
}

// ======================================
// 인장 시스템 (Seal / Signature Card)
// ======================================

/**
 * 인장 데이터 조회 (렌더링에 필요한 모든 데이터)
 */
function mg_get_seal($mb_id)
{
    global $g5, $mg;

    if (!$mb_id) return null;
    if (!mg_config('seal_enable', 1)) return null;

    $mb_esc = sql_real_escape_string($mb_id);

    // 인장 데이터
    $seal = sql_fetch("SELECT * FROM {$g5['mg_seal_table']} WHERE mb_id = '{$mb_esc}'");
    if (!$seal || !$seal['seal_use']) return null;

    // 회원 닉네임
    $member = sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '{$mb_esc}'");
    $seal['mb_nick'] = $member['mb_nick'] ?? $mb_id;

    // 대표 캐릭터
    $seal['main_char'] = mg_get_main_character($mb_id);

    // 활성 칭호
    $title_items = mg_get_active_items($mb_id, 'title');
    $seal['title_item'] = !empty($title_items) ? $title_items[0] : null;

    // 배경/프레임 스킨
    $seal_bg = mg_get_active_items($mb_id, 'seal_bg');
    $seal['bg_item'] = !empty($seal_bg) ? $seal_bg[0] : null;
    $seal_frame = mg_get_active_items($mb_id, 'seal_frame');
    $seal['frame_item'] = !empty($seal_frame) ? $seal_frame[0] : null;

    // 트로피 (업적 쇼케이스)
    $seal['trophies'] = array();
    if (function_exists('mg_get_achievement_display')) {
        $seal['trophies'] = mg_get_achievement_display($mb_id);
    }

    return $seal;
}

/**
 * 인장 렌더링
 *
 * @param string $mb_id 회원 ID
 * @param string $mode 'full' 또는 'compact'
 * @return string HTML
 */
function mg_render_seal($mb_id, $mode = 'full')
{
    // 캐싱 (같은 페이지 내 동일 유저)
    static $cache = array();
    $cache_key = $mb_id . '_' . $mode;
    if (isset($cache[$cache_key])) return $cache[$cache_key];

    $seal = mg_get_seal($mb_id);
    if (!$seal) {
        $cache[$cache_key] = '';
        return '';
    }

    // 배경/프레임 스타일
    $bg_style = 'background:#2b2d31;';
    $border_style = 'border:1px solid #3f4147;';
    if ($seal['bg_item']) {
        $effect = is_string($seal['bg_item']['si_effect']) ? json_decode($seal['bg_item']['si_effect'], true) : $seal['bg_item']['si_effect'];
        if (!empty($effect['bg_image'])) {
            $bg_style = "background:url('" . htmlspecialchars($effect['bg_image']) . "') center/cover no-repeat;";
        } elseif (!empty($effect['bg_color'])) {
            $bg_style = "background:" . htmlspecialchars($effect['bg_color']) . ";";
        }
    }
    if ($seal['frame_item']) {
        $effect = is_string($seal['frame_item']['si_effect']) ? json_decode($seal['frame_item']['si_effect'], true) : $seal['frame_item']['si_effect'];
        if (!empty($effect['border_color'])) {
            $border_style = "border:2px solid " . htmlspecialchars($effect['border_color']) . ";";
        }
    }

    // 칭호 렌더링
    $title_html = '';
    if ($seal['title_item']) {
        $te = is_string($seal['title_item']['si_effect']) ? json_decode($seal['title_item']['si_effect'], true) : $seal['title_item']['si_effect'];
        $tc = !empty($te['color']) ? ' style="color:' . htmlspecialchars($te['color']) . '"' : '';
        $title_html = '<span class="mg-seal-title"' . $tc . '>' . htmlspecialchars($te['text'] ?? $seal['title_item']['si_name']) . '</span>';
    }

    // 텍스트 색상
    $text_color = '';
    if (!empty($seal['seal_text_color'])) {
        $text_color = ' style="color:' . htmlspecialchars($seal['seal_text_color']) . '"';
    }

    // 캐릭터 썸네일
    $char_thumb = '';
    if ($seal['main_char'] && !empty($seal['main_char']['ch_thumb'])) {
        $char_thumb = '<img src="' . MG_CHAR_IMAGE_URL . '/' . htmlspecialchars($seal['main_char']['ch_thumb']) . '" alt="" class="w-full h-full object-cover">';
    } else {
        $char_thumb = '<svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>';
    }

    // 트로피 HTML
    $trophy_html = '';
    $rarity_colors = array('common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6', 'epic' => '#a855f7', 'legendary' => '#f59e0b');
    $trophy_slots = (int)mg_config('seal_trophy_slots', 3);
    $shown = 0;
    if (!empty($seal['trophies'])) {
        foreach ($seal['trophies'] as $tr) {
            if (!$tr || $shown >= $trophy_slots) break;
            $t_name = $tr['tier_name'] ?: $tr['ac_name'];
            $t_icon = $tr['tier_icon'] ?: ($tr['ac_icon'] ?: '');
            $t_rarity = $tr['ac_rarity'] ?? 'common';
            $t_color = $rarity_colors[$t_rarity] ?? '#949ba4';
            $icon_html = $t_icon
                ? '<img src="' . htmlspecialchars($t_icon) . '" alt="" class="w-6 h-6 object-contain">'
                : '<span class="text-sm">&#127942;</span>';
            $trophy_html .= '<div class="flex flex-col items-center" title="' . htmlspecialchars($t_name) . '" style="border:1.5px solid ' . $t_color . ';border-radius:6px;padding:3px 4px;min-width:40px;">'
                . $icon_html
                . '<span class="text-[9px] leading-tight text-center truncate max-w-[50px]" style="color:' . $t_color . ';">' . htmlspecialchars(mb_strimwidth($t_name, 0, 12, '..')) . '</span>'
                . '</div>';
            $shown++;
        }
    }

    $html = '';

    if ($mode === 'compact') {
        // === COMPACT 모드 ===
        $html .= '<div class="mg-seal mg-seal-compact flex items-center gap-2 px-3 py-1.5 rounded-lg mt-1" style="' . $bg_style . $border_style . 'max-width:100%;">';
        $html .= '<div class="w-7 h-7 rounded-full overflow-hidden flex-shrink-0 bg-mg-bg-tertiary flex items-center justify-center">' . $char_thumb . '</div>';
        $html .= '<span class="text-xs font-medium text-mg-text-primary truncate">' . htmlspecialchars($seal['mb_nick']) . '</span>';
        if ($title_html) $html .= '<span class="text-[10px]">' . $title_html . '</span>';
        if (!empty($seal['seal_tagline'])) {
            $html .= '<span class="text-[10px] text-mg-text-muted truncate"' . $text_color . '>"' . htmlspecialchars(mb_strimwidth($seal['seal_tagline'], 0, 30, '..')) . '"</span>';
        }
        $html .= '</div>';
    } else {
        // === FULL 모드 ===
        $html .= '<div class="mg-seal mg-seal-full rounded-xl overflow-hidden mt-4" style="' . $bg_style . $border_style . '">';
        $html .= '<div class="flex gap-4 p-4">';

        // 좌측: 캐릭터 썸네일
        $html .= '<div class="flex-shrink-0">';
        $html .= '<div class="w-16 h-16 rounded-lg overflow-hidden bg-mg-bg-tertiary flex items-center justify-center">' . $char_thumb . '</div>';
        $html .= '</div>';

        // 중앙: 정보
        $html .= '<div class="flex-1 min-w-0"' . $text_color . '>';
        $html .= '<div class="flex items-center gap-2 flex-wrap">';
        $html .= '<span class="font-semibold text-sm text-mg-text-primary">' . htmlspecialchars($seal['mb_nick']) . '</span>';
        if ($title_html) $html .= $title_html;
        $html .= '</div>';

        if (!empty($seal['seal_tagline'])) {
            $html .= '<p class="text-xs text-mg-text-secondary mt-0.5">"' . htmlspecialchars($seal['seal_tagline']) . '"</p>';
        }

        // 자유 영역
        if (!empty($seal['seal_content'])) {
            $html .= '<div class="text-xs text-mg-text-muted mt-2 leading-relaxed">' . nl2br(htmlspecialchars(mb_strimwidth($seal['seal_content'], 0, 300, '...'))) . '</div>';
        }

        // 이미지
        if (!empty($seal['seal_image'])) {
            $img_url = $seal['seal_image'];
            if (strpos($img_url, 'http') !== 0) {
                $img_url = MG_SEAL_IMAGE_URL . '/' . $seal['seal_image'];
            }
            $html .= '<div class="mt-2"><img src="' . htmlspecialchars($img_url) . '" alt="" class="max-w-full max-h-[100px] rounded object-contain" loading="lazy"></div>';
        }

        // 링크
        if (!empty($seal['seal_link']) && mg_config('seal_link_allow', 1)) {
            $link_text = !empty($seal['seal_link_text']) ? htmlspecialchars($seal['seal_link_text']) : htmlspecialchars(mb_strimwidth($seal['seal_link'], 0, 40, '...'));
            $html .= '<div class="mt-1"><a href="' . htmlspecialchars($seal['seal_link']) . '" target="_blank" rel="noopener" class="text-[11px] text-mg-accent hover:underline inline-flex items-center gap-1">';
            $html .= '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>';
            $html .= $link_text . '</a></div>';
        }

        $html .= '</div>'; // 중앙 끝

        // 우측: 트로피
        if ($trophy_html) {
            $html .= '<div class="flex-shrink-0 flex flex-col gap-1.5">' . $trophy_html . '</div>';
        }

        $html .= '</div>'; // flex 끝
        $html .= '</div>'; // seal 끝
    }

    $cache[$cache_key] = $html;
    return $html;
}

/**
 * 인장 이미지 업로드
 */
function mg_upload_seal_image($file, $mb_id)
{
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $max_size = (int)mg_config('seal_image_max_size', 500) * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => '파일 업로드 오류입니다.');
    }
    if ($file['size'] > $max_size) {
        return array('success' => false, 'message' => '파일 크기가 제한(' . round($max_size/1024) . 'KB)을 초과했습니다.');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        return array('success' => false, 'message' => '허용되지 않는 파일 형식입니다.');
    }

    $dir = MG_SEAL_IMAGE_PATH . '/' . $mb_id;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }

    $filename = 'seal_' . uniqid() . '.' . $ext;
    $filepath = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return array('success' => false, 'message' => '파일 저장에 실패했습니다.');
    }

    // 리사이즈 (600x200 초과 시)
    $img_info = @getimagesize($filepath);
    if ($img_info && ($img_info[0] > 600 || $img_info[1] > 200)) {
        mg_resize_image($filepath, $filepath, 600, 200);
    }

    return array('success' => true, 'filename' => $mb_id . '/' . $filename);
}

/**
 * 이미지 리사이즈 (비율 유지)
 */
function mg_resize_image($source, $dest, $max_w, $max_h)
{
    $info = @getimagesize($source);
    if (!$info) return false;

    $src_w = $info[0];
    $src_h = $info[1];
    $ratio = min($max_w / $src_w, $max_h / $src_h);
    if ($ratio >= 1) return true;

    $new_w = (int)($src_w * $ratio);
    $new_h = (int)($src_h * $ratio);

    switch ($info[2]) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($source); break;
        case IMAGETYPE_PNG:  $src = @imagecreatefrompng($source); break;
        case IMAGETYPE_GIF:  $src = @imagecreatefromgif($source); break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($source); break;
        default: return false;
    }
    if (!$src) return false;

    $dst = imagecreatetruecolor($new_w, $new_h);
    if ($info[2] == IMAGETYPE_PNG || $info[2] == IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_w, $new_h, $src_w, $src_h);

    switch ($info[2]) {
        case IMAGETYPE_JPEG: imagejpeg($dst, $dest, 85); break;
        case IMAGETYPE_PNG:  imagepng($dst, $dest, 8); break;
        case IMAGETYPE_GIF:  imagegif($dst, $dest); break;
        case IMAGETYPE_WEBP: imagewebp($dst, $dest, 85); break;
    }

    imagedestroy($src);
    imagedestroy($dst);
    return true;
}

/**
 * 인장 텍스트 새니타이징
 */
function mg_sanitize_seal_text($text, $max_len = 300)
{
    $text = strip_tags($text);
    $text = trim($text);
    if (mb_strlen($text) > $max_len) {
        $text = mb_substr($text, 0, $max_len);
    }
    return $text;
}

// ======================================
// 세계관 위키 (Lore Wiki) 함수
// ======================================

/**
 * 활성 카테고리 목록 조회
 */
function mg_get_lore_categories()
{
    global $g5;
    $sql = "SELECT * FROM {$g5['mg_lore_category_table']} WHERE lc_use = 1 ORDER BY lc_order, lc_id";
    $result = sql_query($sql);
    $categories = array();
    while ($row = sql_fetch_array($result)) {
        $categories[] = $row;
    }
    return $categories;
}

/**
 * 카테고리별 문서 목록 조회
 * @param int $lc_id 카테고리 ID (0=전체)
 * @param int $page 페이지 번호
 * @param int $per_page 페이지당 문서 수
 * @return array ['articles' => [], 'total' => int]
 */
function mg_get_lore_articles($lc_id = 0, $page = 1, $per_page = 12)
{
    global $g5;

    $where = "la_use = 1";
    if ($lc_id > 0) {
        $lc_id = (int)$lc_id;
        $where .= " AND lc_id = {$lc_id}";
    }

    // 총 개수
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_lore_article_table']} WHERE {$where}";
    $row = sql_fetch($sql);
    $total = (int)$row['cnt'];

    // 문서 목록
    $offset = ($page - 1) * $per_page;
    $sql = "SELECT a.*, c.lc_name
            FROM {$g5['mg_lore_article_table']} a
            LEFT JOIN {$g5['mg_lore_category_table']} c ON a.lc_id = c.lc_id
            WHERE a.la_use = 1
            " . ($lc_id > 0 ? "AND a.lc_id = {$lc_id}" : "") . "
            ORDER BY a.la_order, a.la_id DESC
            LIMIT {$offset}, {$per_page}";
    $result = sql_query($sql);
    $articles = array();
    while ($row = sql_fetch_array($result)) {
        $articles[] = $row;
    }

    return array('articles' => $articles, 'total' => $total);
}

/**
 * 문서 + 섹션 전체 조회
 * @param int $la_id 문서 ID
 * @return array|null
 */
function mg_get_lore_article($la_id)
{
    global $g5;
    $la_id = (int)$la_id;

    $sql = "SELECT a.*, c.lc_name
            FROM {$g5['mg_lore_article_table']} a
            LEFT JOIN {$g5['mg_lore_category_table']} c ON a.lc_id = c.lc_id
            WHERE a.la_id = {$la_id}";
    $article = sql_fetch($sql);
    if (!$article || !$article['la_id']) return null;

    // 섹션
    $sql = "SELECT * FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id} ORDER BY ls_order, ls_id";
    $result = sql_query($sql);
    $sections = array();
    while ($row = sql_fetch_array($result)) {
        $sections[] = $row;
    }
    $article['sections'] = $sections;

    return $article;
}

/**
 * 타임라인 전체 조회 (시대 + 이벤트 2중 배열)
 * @return array
 */
function mg_get_lore_timeline()
{
    global $g5;

    $sql = "SELECT * FROM {$g5['mg_lore_era_table']} WHERE le_use = 1 ORDER BY le_order, le_id";
    $result = sql_query($sql);
    $eras = array();
    while ($row = sql_fetch_array($result)) {
        $row['events'] = array();
        $eras[$row['le_id']] = $row;
    }

    if (!empty($eras)) {
        $era_ids = implode(',', array_keys($eras));
        $sql = "SELECT * FROM {$g5['mg_lore_event_table']}
                WHERE le_id IN ({$era_ids}) AND lv_use = 1
                ORDER BY lv_order, lv_id";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            if (isset($eras[$row['le_id']])) {
                $eras[$row['le_id']]['events'][] = $row;
            }
        }
    }

    return array_values($eras);
}

/**
 * 위키 이미지 업로드
 * @param array $file $_FILES 배열 요소
 * @param string $type 'article', 'section', 'event'
 * @param int $id 해당 ID
 * @return array ['success' => bool, 'url' => string, 'filename' => string]
 */
function mg_upload_lore_image($file, $type = 'article', $id = 0)
{
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $max_size = (int)mg_config('lore_image_max_size', 2048) * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => '파일 업로드 오류');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        return array('success' => false, 'message' => '허용되지 않는 파일 형식');
    }

    if ($file['size'] > $max_size) {
        return array('success' => false, 'message' => '파일 크기 초과');
    }

    // 저장 디렉토리
    $dir = MG_LORE_IMAGE_PATH . '/' . $type;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
        @chmod($dir, 0755);
    }

    $filename = $type . '_' . $id . '_' . date('YmdHis') . '_' . mt_rand(100, 999) . '.' . $ext;
    $filepath = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return array('success' => false, 'message' => '파일 저장 실패');
    }

    @chmod($filepath, 0644);
    $url = MG_LORE_IMAGE_URL . '/' . $type . '/' . $filename;

    return array('success' => true, 'url' => $url, 'filename' => $filename);
}

// ======================================
// 프롬프트 미션 함수
// ======================================

/**
 * 기한 만료 프롬프트 자동 종료 (패시브 체크)
 */
function mg_prompt_check_expired()
{
    global $g5;
    sql_query("UPDATE {$g5['mg_prompt_table']}
        SET pm_status = 'closed'
        WHERE pm_status = 'active'
        AND pm_end_date IS NOT NULL
        AND pm_end_date < NOW()");
}

/**
 * 게시판의 활성 프롬프트 목록
 */
function mg_get_active_prompts($bo_table)
{
    global $g5;
    mg_prompt_check_expired();

    $sql = "SELECT * FROM {$g5['mg_prompt_table']}
        WHERE bo_table = '".sql_real_escape_string($bo_table)."'
        AND pm_status = 'active'
        ORDER BY pm_end_date ASC";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

/**
 * 프롬프트 단건 조회
 */
function mg_get_prompt($pm_id)
{
    global $g5;
    $pm_id = (int)$pm_id;
    return sql_fetch("SELECT * FROM {$g5['mg_prompt_table']} WHERE pm_id = {$pm_id}");
}

/**
 * 프롬프트 제출 수
 */
function mg_get_prompt_entry_count($pm_id, $status = '')
{
    global $g5;
    $pm_id = (int)$pm_id;
    $where = "pm_id = {$pm_id}";
    if ($status) {
        $where .= " AND pe_status = '".sql_real_escape_string($status)."'";
    }
    $row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_prompt_entry_table']} WHERE {$where}");
    return (int)$row['cnt'];
}

/**
 * 내 제출 내역
 */
function mg_get_my_entries($pm_id, $mb_id)
{
    global $g5;
    $pm_id = (int)$pm_id;
    $mb_id = sql_real_escape_string($mb_id);
    $sql = "SELECT * FROM {$g5['mg_prompt_entry_table']}
        WHERE pm_id = {$pm_id} AND mb_id = '{$mb_id}'
        ORDER BY pe_datetime DESC";
    $result = sql_query($sql);
    $list = array();
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

/**
 * 게시글 ID로 엔트리 조회
 */
function mg_get_entry_by_write($bo_table, $wr_id)
{
    global $g5;
    $bo_table = sql_real_escape_string($bo_table);
    $wr_id = (int)$wr_id;
    return sql_fetch("SELECT e.*, p.pm_title, p.pm_mode, p.pm_point, p.pm_bonus_point
        FROM {$g5['mg_prompt_entry_table']} e
        JOIN {$g5['mg_prompt_table']} p ON e.pm_id = p.pm_id
        WHERE e.bo_table = '{$bo_table}' AND e.wr_id = {$wr_id}");
}

/**
 * 글 저장 후 프롬프트 엔트리 생성 훅
 */
function mg_prompt_after_write($bo_table, $wr_id, $mb_id, $wr_content = '')
{
    global $g5;

    $pm_id = (int)(isset($_POST['pm_id']) ? $_POST['pm_id'] : 0);
    if (!$pm_id) return false;

    $prompt = mg_get_prompt($pm_id);
    if (!$prompt || !$prompt['pm_id']) return false;
    if ($prompt['pm_status'] != 'active') return false;
    if ($prompt['bo_table'] != $bo_table) return false;

    $mb_id = sql_real_escape_string($mb_id);

    // 중복 제출 체크
    $existing = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_prompt_entry_table']}
        WHERE pm_id = {$pm_id} AND mb_id = '{$mb_id}'");
    if ((int)$existing['cnt'] >= (int)$prompt['pm_max_entry']) return false;

    // 글자수 체크
    if ($prompt['pm_min_chars'] > 0) {
        $content_len = mb_strlen(strip_tags($wr_content));
        if ($content_len < $prompt['pm_min_chars']) return false;
    }

    // 엔트리 생성
    $status = ($prompt['pm_mode'] == 'auto') ? 'approved' : 'submitted';
    sql_query("INSERT INTO {$g5['mg_prompt_entry_table']}
        (pm_id, mb_id, wr_id, bo_table, pe_status, pe_datetime)
        VALUES ({$pm_id}, '{$mb_id}', {$wr_id}, '".sql_real_escape_string($bo_table)."', '{$status}', NOW())");

    $pe_id = sql_insert_id();

    // auto 모드면 즉시 보상
    if ($prompt['pm_mode'] == 'auto' && $pe_id) {
        mg_prompt_give_reward($pm_id, $pe_id);
    }

    // 관리자 알림
    if (mg_config('prompt_notify_submit', '1') == '1') {
        if (function_exists('mg_notify')) {
            mg_notify('admin', 'prompt_submit', "프롬프트 \"{$prompt['pm_title']}\"에 새 제출이 있습니다.", '', G5_ADMIN_URL.'/morgan/prompt.php?mode=review&pm_id='.$pm_id);
        }
    }

    return $pe_id;
}

/**
 * 보상 지급
 */
function mg_prompt_give_reward($pm_id, $pe_id, $is_bonus = false)
{
    global $g5;

    $prompt = mg_get_prompt($pm_id);
    if (!$prompt || !$prompt['pm_id']) return false;

    $entry = sql_fetch("SELECT * FROM {$g5['mg_prompt_entry_table']} WHERE pe_id = ".(int)$pe_id);
    if (!$entry || !$entry['pe_id']) return false;
    if ($entry['pe_status'] == 'rewarded') return false;

    $point = (int)$prompt['pm_point'];
    if ($is_bonus) {
        $point += (int)$prompt['pm_bonus_point'];
        sql_query("UPDATE {$g5['mg_prompt_entry_table']} SET pe_is_bonus = 1 WHERE pe_id = {$pe_id}");
    }

    // 포인트 지급
    if ($point > 0 && function_exists('insert_point')) {
        $desc = "[미션] {$prompt['pm_title']}" . ($is_bonus ? ' (우수작)' : '');
        insert_point($entry['mb_id'], $point, $desc, 'prompt', $pm_id, 'reward');
    }

    // 재료 보상
    if ((int)$prompt['pm_material_id'] > 0 && (int)$prompt['pm_material_qty'] > 0 && function_exists('mg_add_user_material')) {
        mg_add_user_material($entry['mb_id'], $prompt['pm_material_id'], $prompt['pm_material_qty']);
    }

    // 상태 업데이트
    sql_query("UPDATE {$g5['mg_prompt_entry_table']}
        SET pe_status = 'rewarded', pe_point = {$point}, pe_review_date = NOW()
        WHERE pe_id = {$pe_id}");

    // 유저 알림
    if (mg_config('prompt_notify_approve', '1') == '1' && function_exists('mg_notify')) {
        $msg = "프롬프트 \"{$prompt['pm_title']}\" 보상이 지급되었습니다. (+{$point}P)";
        mg_notify($entry['mb_id'], 'prompt_reward', $msg);
    }

    return true;
}

/**
 * 제출 승인
 */
function mg_prompt_approve($pe_id, $admin_id)
{
    global $g5;
    $pe_id = (int)$pe_id;
    $admin_id = sql_real_escape_string($admin_id);

    $entry = sql_fetch("SELECT * FROM {$g5['mg_prompt_entry_table']} WHERE pe_id = {$pe_id}");
    if (!$entry || !$entry['pe_id']) return false;
    if ($entry['pe_status'] != 'submitted') return false;

    sql_query("UPDATE {$g5['mg_prompt_entry_table']}
        SET pe_status = 'approved', pe_admin_id = '{$admin_id}', pe_review_date = NOW()
        WHERE pe_id = {$pe_id}");

    return true;
}

/**
 * 제출 반려
 */
function mg_prompt_reject($pe_id, $admin_id, $memo = '')
{
    global $g5;
    $pe_id = (int)$pe_id;
    $admin_id = sql_real_escape_string($admin_id);
    $memo = sql_real_escape_string($memo);

    $entry = sql_fetch("SELECT * FROM {$g5['mg_prompt_entry_table']} WHERE pe_id = {$pe_id}");
    if (!$entry || !$entry['pe_id']) return false;
    if ($entry['pe_status'] != 'submitted') return false;

    sql_query("UPDATE {$g5['mg_prompt_entry_table']}
        SET pe_status = 'rejected', pe_admin_id = '{$admin_id}', pe_admin_memo = '{$memo}', pe_review_date = NOW()
        WHERE pe_id = {$pe_id}");

    // 반려 알림
    if (mg_config('prompt_notify_reject', '1') == '1' && function_exists('mg_notify')) {
        $prompt = mg_get_prompt($entry['pm_id']);
        $msg = "프롬프트 \"{$prompt['pm_title']}\" 제출이 반려되었습니다.";
        if ($memo) $msg .= " 사유: {$memo}";
        mg_notify($entry['mb_id'], 'prompt_reject', $msg);
    }

    return true;
}

/**
 * 프롬프트 종료
 */
function mg_prompt_close($pm_id)
{
    global $g5;
    $pm_id = (int)$pm_id;
    sql_query("UPDATE {$g5['mg_prompt_table']} SET pm_status = 'closed' WHERE pm_id = {$pm_id}");
    return true;
}

/**
 * 프롬프트 배너 이미지 업로드
 */
function mg_upload_prompt_banner($file, $pm_id = 0)
{
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $max_size = (int)mg_config('prompt_banner_max_size', 1024) * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return array('success' => false, 'message' => '파일 업로드 오류');
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_ext)) {
        return array('success' => false, 'message' => '허용되지 않는 파일 형식');
    }

    if ($file['size'] > $max_size) {
        return array('success' => false, 'message' => '파일 크기 초과');
    }

    $dir = MG_PROMPT_IMAGE_PATH;
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
        @chmod($dir, 0755);
    }

    $filename = 'banner_' . $pm_id . '_' . date('YmdHis') . '_' . mt_rand(100, 999) . '.' . $ext;
    $filepath = $dir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return array('success' => false, 'message' => '파일 저장 실패');
    }

    @chmod($filepath, 0644);
    $url = MG_PROMPT_IMAGE_URL . '/' . $filename;

    return array('success' => true, 'url' => $url, 'filename' => $filename);
}

// ======================================
// 캐릭터 관계 함수
// ======================================

/**
 * 관계 아이콘 목록
 */
function mg_get_relation_icons($active_only = true)
{
    global $g5;
    $where = $active_only ? "WHERE ri_active = 1" : "";
    $sql = "SELECT * FROM {$g5['mg_relation_icon_table']} {$where} ORDER BY ri_order, ri_id";
    $result = sql_query($sql);
    $icons = array();
    while ($row = sql_fetch_array($result)) {
        $icons[] = $row;
    }
    return $icons;
}

/**
 * 관계 아이콘 단건
 */
function mg_get_relation_icon($ri_id)
{
    global $g5;
    $ri_id = (int)$ri_id;
    return sql_fetch("SELECT * FROM {$g5['mg_relation_icon_table']} WHERE ri_id = {$ri_id}");
}

/**
 * 관계 단건 (아이콘+캐릭터 조인)
 */
function mg_get_relation($cr_id)
{
    global $g5;
    $cr_id = (int)$cr_id;
    $sql = "SELECT r.*, ri.ri_icon, ri.ri_label, ri.ri_color, ri.ri_width, ri.ri_category,
                   ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, ca.mb_id AS mb_id_a,
                   cb.ch_name AS name_b, cb.ch_thumb AS thumb_b, cb.mb_id AS mb_id_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_id = {$cr_id}";
    return sql_fetch($sql);
}

/**
 * 특정 캐릭터의 관계 목록
 */
function mg_get_relations($ch_id, $status = 'active')
{
    global $g5;
    $ch_id = (int)$ch_id;
    $status_cond = $status ? "AND r.cr_status = '".sql_real_escape_string($status)."'" : "";
    $sql = "SELECT r.*, ri.ri_icon, ri.ri_label, ri.ri_color, ri.ri_width, ri.ri_category,
                   ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, ca.mb_id AS mb_id_a,
                   cb.ch_name AS name_b, cb.ch_thumb AS thumb_b, cb.mb_id AS mb_id_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE (r.ch_id_a = {$ch_id} OR r.ch_id_b = {$ch_id}) {$status_cond}
            ORDER BY r.cr_datetime DESC";
    $result = sql_query($sql);
    $relations = array();
    while ($row = sql_fetch_array($result)) {
        $relations[] = $row;
    }
    return $relations;
}

/**
 * 캐릭터 쌍으로 관계 조회
 */
function mg_get_relation_by_pair($ch_id_1, $ch_id_2)
{
    global $g5;
    $a = min((int)$ch_id_1, (int)$ch_id_2);
    $b = max((int)$ch_id_1, (int)$ch_id_2);
    return sql_fetch("SELECT * FROM {$g5['mg_relation_table']} WHERE ch_id_a = {$a} AND ch_id_b = {$b}");
}

/**
 * 관계 신청
 */
function mg_request_relation($from_ch_id, $to_ch_id, $ri_id, $label, $memo = '')
{
    global $g5;

    $from_ch_id = (int)$from_ch_id;
    $to_ch_id = (int)$to_ch_id;
    $ri_id = (int)$ri_id;

    if ($from_ch_id == $to_ch_id) {
        return array('success' => false, 'message' => '자기 자신에게는 신청할 수 없습니다.');
    }

    // 정규화: 작은쪽 = A, 큰쪽 = B
    $a = min($from_ch_id, $to_ch_id);
    $b = max($from_ch_id, $to_ch_id);

    // 중복 체크
    $exists = mg_get_relation_by_pair($a, $b);
    if ($exists) {
        return array('success' => false, 'message' => '이미 관계가 존재합니다.');
    }

    // 아이콘 존재 확인
    $icon = mg_get_relation_icon($ri_id);
    if (!$icon || !$icon['ri_active']) {
        return array('success' => false, 'message' => '유효하지 않은 아이콘입니다.');
    }

    // 신청자가 A인지 B인지에 따라 라벨 위치 결정
    $label = sql_real_escape_string($label);
    $memo = sql_real_escape_string($memo);

    if ($from_ch_id == $a) {
        $sql = "INSERT INTO {$g5['mg_relation_table']}
                (ch_id_a, ch_id_b, ch_id_from, ri_id, cr_label_a, cr_memo_a, cr_status, cr_datetime)
                VALUES ({$a}, {$b}, {$from_ch_id}, {$ri_id}, '{$label}', '{$memo}', 'pending', NOW())";
    } else {
        $sql = "INSERT INTO {$g5['mg_relation_table']}
                (ch_id_a, ch_id_b, ch_id_from, ri_id, cr_label_b, cr_memo_b, cr_status, cr_datetime)
                VALUES ({$a}, {$b}, {$from_ch_id}, {$ri_id}, '{$label}', '{$memo}', 'pending', NOW())";
    }
    sql_query($sql);
    $cr_id = sql_insert_id();

    // 대상 캐릭터 소유자에게 알림
    $from_char = mg_get_character($from_ch_id);
    $to_char = mg_get_character($to_ch_id);
    if ($to_char && $to_char['mb_id']) {
        $noti_title = $from_char['ch_name'] . '이(가) ' . $to_char['ch_name'] . '에게 관계를 신청했습니다.';
        $noti_content = $icon['ri_icon'] . ' ' . $label;
        mg_notify($to_char['mb_id'], 'relation_request', $noti_title, $noti_content,
            G5_BBS_URL . '/relation.php?tab=pending');
    }

    return array('success' => true, 'cr_id' => $cr_id, 'message' => '관계를 신청했습니다.');
}

/**
 * 관계 승인
 */
function mg_accept_relation($cr_id, $label_b = '', $memo_b = '', $icon_b = '')
{
    global $g5;
    $cr_id = (int)$cr_id;

    $rel = mg_get_relation($cr_id);
    if (!$rel || $rel['cr_status'] !== 'pending') {
        return array('success' => false, 'message' => '유효하지 않은 관계입니다.');
    }

    // 승인자는 신청자의 반대쪽
    // 승인자 쪽 라벨 저장
    $sets = array("cr_status = 'active'", "cr_accept_datetime = NOW()");
    $approver_ch_id = ($rel['ch_id_from'] == $rel['ch_id_a']) ? $rel['ch_id_b'] : $rel['ch_id_a'];

    if ($label_b) {
        $label_b_esc = sql_real_escape_string($label_b);
        if ($approver_ch_id == $rel['ch_id_a']) {
            $sets[] = "cr_label_a = '{$label_b_esc}'";
        } else {
            $sets[] = "cr_label_b = '{$label_b_esc}'";
        }
    }
    if ($memo_b) {
        $memo_b_esc = sql_real_escape_string($memo_b);
        if ($approver_ch_id == $rel['ch_id_a']) {
            $sets[] = "cr_memo_a = '{$memo_b_esc}'";
        } else {
            $sets[] = "cr_memo_b = '{$memo_b_esc}'";
        }
    }
    if ($icon_b) {
        $icon_b_esc = sql_real_escape_string($icon_b);
        if ($approver_ch_id == $rel['ch_id_a']) {
            $sets[] = "cr_icon_a = '{$icon_b_esc}'";
        } else {
            $sets[] = "cr_icon_b = '{$icon_b_esc}'";
        }
    }

    sql_query("UPDATE {$g5['mg_relation_table']} SET " . implode(', ', $sets) . " WHERE cr_id = {$cr_id}");

    // 신청자에게 승인 알림
    $from_char = mg_get_character($rel['ch_id_from']);
    $approver_char = mg_get_character($approver_ch_id);
    if ($from_char && $from_char['mb_id']) {
        $noti_title = $approver_char['ch_name'] . '이(가) 관계를 승인했습니다.';
        $noti_content = $rel['ri_icon'] . ' ' . ($label_b ?: $rel['cr_label_a'] ?: $rel['cr_label_b']);
        mg_notify($from_char['mb_id'], 'relation_accepted', $noti_title, $noti_content,
            G5_BBS_URL . '/relation.php');
    }

    return array('success' => true, 'message' => '관계를 승인했습니다.');
}

/**
 * 관계 거절 (삭제)
 */
function mg_reject_relation($cr_id)
{
    global $g5;
    $cr_id = (int)$cr_id;

    $rel = mg_get_relation($cr_id);
    if (!$rel || $rel['cr_status'] !== 'pending') {
        return array('success' => false, 'message' => '유효하지 않은 관계입니다.');
    }

    sql_query("DELETE FROM {$g5['mg_relation_table']} WHERE cr_id = {$cr_id}");

    // 신청자에게 거절 알림
    $from_char = mg_get_character($rel['ch_id_from']);
    $rejector_ch_id = ($rel['ch_id_from'] == $rel['ch_id_a']) ? $rel['ch_id_b'] : $rel['ch_id_a'];
    $rejector_char = mg_get_character($rejector_ch_id);
    if ($from_char && $from_char['mb_id']) {
        mg_notify($from_char['mb_id'], 'relation_rejected',
            $rejector_char['ch_name'] . '이(가) 관계 신청을 거절했습니다.', '', G5_BBS_URL . '/relation.php');
    }

    return array('success' => true, 'message' => '관계를 거절했습니다.');
}

/**
 * 관계 해제 (어느 쪽이든 가능)
 */
function mg_delete_relation($cr_id, $by_ch_id = 0)
{
    global $g5;
    $cr_id = (int)$cr_id;

    $rel = mg_get_relation($cr_id);
    if (!$rel) {
        return array('success' => false, 'message' => '존재하지 않는 관계입니다.');
    }

    sql_query("DELETE FROM {$g5['mg_relation_table']} WHERE cr_id = {$cr_id}");

    // 상대에게 해제 알림
    if ($by_ch_id) {
        $other_ch_id = ($by_ch_id == $rel['ch_id_a']) ? $rel['ch_id_b'] : $rel['ch_id_a'];
        $by_char = mg_get_character($by_ch_id);
        $other_char = mg_get_character($other_ch_id);
        if ($other_char && $other_char['mb_id']) {
            mg_notify($other_char['mb_id'], 'relation_deleted',
                $by_char['ch_name'] . '이(가) 관계를 해제했습니다.', '', G5_BBS_URL . '/relation.php');
        }
    }

    return array('success' => true, 'message' => '관계를 해제했습니다.');
}

/**
 * 자기 쪽 관계 정보 수정
 */
function mg_update_relation_side($cr_id, $ch_id, $label = '', $memo = '', $icon = '')
{
    global $g5;
    $cr_id = (int)$cr_id;
    $ch_id = (int)$ch_id;

    $rel = sql_fetch("SELECT * FROM {$g5['mg_relation_table']} WHERE cr_id = {$cr_id}");
    if (!$rel) {
        return array('success' => false, 'message' => '존재하지 않는 관계입니다.');
    }

    // ch_id가 A인지 B인지 판별
    $side = '';
    if ($ch_id == $rel['ch_id_a']) $side = 'a';
    elseif ($ch_id == $rel['ch_id_b']) $side = 'b';
    else return array('success' => false, 'message' => '해당 관계의 당사자가 아닙니다.');

    $sets = array();
    if ($label) $sets[] = "cr_label_{$side} = '".sql_real_escape_string($label)."'";
    if ($memo !== '') $sets[] = "cr_memo_{$side} = '".sql_real_escape_string($memo)."'";
    if ($icon) $sets[] = "cr_icon_{$side} = '".sql_real_escape_string($icon)."'";

    if (empty($sets)) {
        return array('success' => false, 'message' => '변경 사항이 없습니다.');
    }

    sql_query("UPDATE {$g5['mg_relation_table']} SET ".implode(', ', $sets)." WHERE cr_id = {$cr_id}");

    return array('success' => true, 'message' => '관계 정보를 수정했습니다.');
}

/**
 * vis.js 관계도 데이터 (nodes + edges)
 *
 * @param int $ch_id 중심 캐릭터 (0이면 전체)
 * @param int $depth 탐색 깊이 (1~3)
 * @param string $category 카테고리 필터 (콤마 구분)
 * @param int $faction_id 세력 필터
 * @return array ['nodes' => [...], 'edges' => [...]]
 */
function mg_get_relation_graph($ch_id = 0, $depth = 2, $category = '', $faction_id = 0)
{
    global $g5;
    $ch_id = (int)$ch_id;
    $depth = max(1, min(3, (int)$depth));

    // 전체 관계도 or 특정 캐릭터 중심
    if ($ch_id > 0) {
        // BFS로 depth만큼 관계 탐색
        $visited = array($ch_id);
        $current_ids = array($ch_id);
        $all_relations = array();

        for ($d = 0; $d < $depth; $d++) {
            if (empty($current_ids)) break;
            $id_list = implode(',', array_map('intval', $current_ids));

            $cat_cond = '';
            if ($category) {
                $cats = array_map(function($c) { return "'".sql_real_escape_string(trim($c))."'"; }, explode(',', $category));
                $cat_cond = "AND ri.ri_category IN (" . implode(',', $cats) . ")";
            }

            $sql = "SELECT r.*, ri.ri_icon, ri.ri_label, ri.ri_color, ri.ri_width, ri.ri_category
                    FROM {$g5['mg_relation_table']} r
                    JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
                    WHERE r.cr_status = 'active'
                    AND (r.ch_id_a IN ({$id_list}) OR r.ch_id_b IN ({$id_list}))
                    {$cat_cond}";
            $result = sql_query($sql);

            $next_ids = array();
            while ($row = sql_fetch_array($result)) {
                $all_relations[$row['cr_id']] = $row;
                foreach (array('ch_id_a', 'ch_id_b') as $col) {
                    if (!in_array($row[$col], $visited)) {
                        $visited[] = $row[$col];
                        $next_ids[] = $row[$col];
                    }
                }
            }
            $current_ids = $next_ids;
        }

        $node_ids = $visited;
        $edges = array_values($all_relations);
    } else {
        // 전체 관계도
        $cat_cond = '';
        if ($category) {
            $cats = array_map(function($c) { return "'".sql_real_escape_string(trim($c))."'"; }, explode(',', $category));
            $cat_cond = "AND ri.ri_category IN (" . implode(',', $cats) . ")";
        }

        $sql = "SELECT r.*, ri.ri_icon, ri.ri_label, ri.ri_color, ri.ri_width, ri.ri_category
                FROM {$g5['mg_relation_table']} r
                JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
                WHERE r.cr_status = 'active' {$cat_cond}
                LIMIT 500";
        $result = sql_query($sql);
        $edges = array();
        $node_ids = array();
        while ($row = sql_fetch_array($result)) {
            $edges[] = $row;
            if (!in_array($row['ch_id_a'], $node_ids)) $node_ids[] = $row['ch_id_a'];
            if (!in_array($row['ch_id_b'], $node_ids)) $node_ids[] = $row['ch_id_b'];
        }
    }

    // 노드 정보 조회
    $nodes = array();
    if (!empty($node_ids)) {
        $id_list = implode(',', array_map('intval', $node_ids));
        $faction_cond = $faction_id ? "AND c.side_id = ".(int)$faction_id : "";
        $sql = "SELECT c.ch_id, c.ch_name, c.ch_thumb, c.side_id, c.mb_id,
                       s.side_name, s.side_image
                FROM {$g5['mg_character_table']} c
                LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
                WHERE c.ch_id IN ({$id_list}) AND c.ch_state = 'approved' {$faction_cond}";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $nodes[] = array(
                'ch_id' => (int)$row['ch_id'],
                'ch_name' => $row['ch_name'],
                'ch_thumb' => $row['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$row['ch_thumb'] : '',
                'side_id' => (int)$row['side_id'],
                'faction_name' => $row['side_name'] ?: '',
                'faction_color' => '',
            );
        }
    }

    // 엣지 포맷
    $formatted_edges = array();
    foreach ($edges as $e) {
        $label_a = $e['cr_label_a'] ?: '';
        $label_b = $e['cr_label_b'] ?: '';
        $icon_a = $e['cr_icon_a'] ?: $e['ri_icon'];
        $icon_b = $e['cr_icon_b'] ?: $e['ri_icon'];

        if ($label_a && $label_b && $label_a !== $label_b) {
            $label_display = $label_a . ' / ' . $label_b;
        } else {
            $label_display = $label_a ?: $label_b;
        }

        $formatted_edges[] = array(
            'cr_id' => (int)$e['cr_id'],
            'ch_id_a' => (int)$e['ch_id_a'],
            'ch_id_b' => (int)$e['ch_id_b'],
            'icon' => $icon_a,
            'icon_a' => $icon_a,
            'icon_b' => $icon_b,
            'label_a' => $label_a,
            'label_b' => $label_b,
            'label_display' => $label_display,
            'edge_color' => $e['cr_color'] ?: $e['ri_color'],
            'edge_width' => (int)$e['ri_width'],
            'category' => $e['ri_category'],
            'memo_a' => $e['cr_memo_a'] ?: '',
            'memo_b' => $e['cr_memo_b'] ?: '',
        );
    }

    return array('nodes' => $nodes, 'edges' => $formatted_edges);
}

/**
 * 회원이 받은 대기중 관계 신청 목록
 */
function mg_get_pending_relations($mb_id)
{
    global $g5;
    $mb_id = sql_real_escape_string($mb_id);

    // 내 캐릭터 ID 목록
    $chars = mg_get_characters($mb_id);
    if (empty($chars)) return array();

    $my_ch_ids = array_map(function($c) { return (int)$c['ch_id']; }, $chars);
    $id_list = implode(',', $my_ch_ids);

    // 내 캐릭터가 대상(B)이면서 신청자(from)가 아닌 pending 관계
    $sql = "SELECT r.*, ri.ri_icon, ri.ri_label, ri.ri_color, ri.ri_category,
                   ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, ca.mb_id AS mb_id_a,
                   cb.ch_name AS name_b, cb.ch_thumb AS thumb_b, cb.mb_id AS mb_id_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'pending'
            AND ((r.ch_id_a IN ({$id_list}) OR r.ch_id_b IN ({$id_list}))
                 AND r.ch_id_from NOT IN ({$id_list}))
            ORDER BY r.cr_datetime DESC";
    $result = sql_query($sql);
    $relations = array();
    while ($row = sql_fetch_array($result)) {
        $relations[] = $row;
    }
    return $relations;
}
