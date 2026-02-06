<?php
/**
 * Morgan Edition - 메인 페이지 빌더 업데이트 처리
 */

require_once __DIR__.'/../_common.php';

if ($is_admin != 'super') {
    die(json_encode(['success' => false, 'message' => '권한이 없습니다.']));
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json');

// JSON body 처리
$input = file_get_contents('php://input');
$json_data = json_decode($input, true);

$action = isset($json_data['action']) ? $json_data['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

// 기본 row 확보 (row 없으면 생성)
function ensure_default_row() {
    global $g5;
    $row = sql_fetch("SELECT row_id FROM {$g5['mg_main_row_table']} LIMIT 1");
    if (!$row) {
        sql_query("INSERT INTO {$g5['mg_main_row_table']} (row_order, row_use) VALUES (0, 1)");
        return sql_insert_id();
    }
    return $row['row_id'];
}

switch ($action) {
    case 'add_widget':
        $widget_type = preg_replace('/[^a-z0-9_]/i', '', $_POST['widget_type']);
        $widget_cols = (int)$_POST['widget_cols'];

        // 유효성 검사
        $widget_types = mg_get_widget_types();
        if (!isset($widget_types[$widget_type])) {
            echo json_encode(['success' => false, 'message' => '잘못된 위젯 타입입니다.']);
            break;
        }

        if (!in_array($widget_cols, $widget_types[$widget_type]['allowed_cols'])) {
            $widget_cols = $widget_types[$widget_type]['allowed_cols'][0];
        }

        // 기본 row 확보
        $row_id = ensure_default_row();

        // 최대 순서 가져오기
        $max_order = sql_fetch("SELECT MAX(widget_order) as max_order FROM {$g5['mg_main_widget_table']}");
        $new_order = ($max_order['max_order'] ?? 0) + 1;

        sql_query("INSERT INTO {$g5['mg_main_widget_table']}
                   (row_id, widget_type, widget_order, widget_cols, widget_config, widget_use)
                   VALUES ({$row_id}, '{$widget_type}', {$new_order}, {$widget_cols}, '{}', 1)");
        $widget_id = sql_insert_id();

        echo json_encode(['success' => true, 'widget_id' => $widget_id]);
        break;

    case 'delete_widget':
        $widget_id = (int)$_POST['widget_id'];
        if ($widget_id > 0) {
            sql_query("DELETE FROM {$g5['mg_main_widget_table']} WHERE widget_id = {$widget_id}");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        }
        break;

    case 'update_widget_config':
        $widget_id = (int)$_POST['widget_id'];
        $widget_config = isset($_POST['widget_config']) ? $_POST['widget_config'] : array();
        $widget_cols = isset($_POST['widget_cols']) ? (int)$_POST['widget_cols'] : 0;

        if ($widget_id <= 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            break;
        }

        // 기존 위젯 정보 가져오기
        $widget = sql_fetch("SELECT * FROM {$g5['mg_main_widget_table']} WHERE widget_id = {$widget_id}");
        if (!$widget) {
            echo json_encode(['success' => false, 'message' => '존재하지 않는 위젯입니다.']);
            break;
        }

        // 컬럼 너비 유효성 검사
        if ($widget_cols > 0) {
            $widget_types = mg_get_widget_types();
            if (isset($widget_types[$widget['widget_type']]) &&
                in_array($widget_cols, $widget_types[$widget['widget_type']]['allowed_cols'])) {
                sql_query("UPDATE {$g5['mg_main_widget_table']} SET widget_cols = {$widget_cols} WHERE widget_id = {$widget_id}");
            }
        }

        // 설정 저장
        $config_json = sql_real_escape_string(json_encode($widget_config, JSON_UNESCAPED_UNICODE));
        sql_query("UPDATE {$g5['mg_main_widget_table']} SET widget_config = '{$config_json}' WHERE widget_id = {$widget_id}");

        echo json_encode(['success' => true]);
        break;

    case 'save_order':
        $widgets = $json_data['widgets'];

        if (is_array($widgets)) {
            foreach ($widgets as $widget_data) {
                $widget_id = (int)$widget_data['id'];
                $widget_order = (int)$widget_data['order'];
                if ($widget_id > 0) {
                    sql_query("UPDATE {$g5['mg_main_widget_table']} SET widget_order = {$widget_order} WHERE widget_id = {$widget_id}");
                }
            }
        }

        echo json_encode(['success' => true]);
        break;

    case 'save_row_height':
        $row_height = (int)$_POST['row_height'];

        // 유효성 검사 (100~800px)
        if ($row_height < 100 || $row_height > 800) {
            echo json_encode(['success' => false, 'message' => '행 높이는 100~800px 사이로 입력해주세요.']);
            break;
        }

        mg_config_set('widget_row_height', $row_height, '메인 위젯 행 높이(px)');
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
}
