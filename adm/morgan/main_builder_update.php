<?php
/**
 * Morgan Edition - 메인 페이지 빌더 업데이트 처리
 * GridStack 2D 그리드 캔버스 기반
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

// 기본 row 확보 (하위 호환)
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
    // ==========================================
    // 위젯 추가
    // ==========================================
    case 'add_widget':
        $widget_type = preg_replace('/[^a-z0-9_]/i', '', $_POST['widget_type'] ?? '');
        $grid_cols = (int)mg_config('grid_columns', 12);
        if ($grid_cols < 1) $grid_cols = 12;
        $widget_w = max(1, min($grid_cols, (int)($_POST['widget_w'] ?? 6)));
        $widget_h = max(1, (int)($_POST['widget_h'] ?? 2));

        // 유효성 검사
        $widget_types = mg_get_widget_types();
        if (!isset($widget_types[$widget_type])) {
            echo json_encode(['success' => false, 'message' => '잘못된 위젯 타입입니다.']);
            break;
        }

        // 기본 row 확보 (FK 호환)
        $row_id = ensure_default_row();

        // x,y = 0,0 (GridStack이 빈 자리에 배치)
        sql_query("INSERT INTO {$g5['mg_main_widget_table']}
                   (row_id, widget_type, widget_order, widget_cols, widget_x, widget_y, widget_w, widget_h, widget_config, widget_use)
                   VALUES ({$row_id}, '{$widget_type}', 0, {$widget_w}, 0, 0, {$widget_w}, {$widget_h}, '{}', 1)");
        $widget_id = sql_insert_id();

        echo json_encode(['success' => true, 'widget_id' => $widget_id]);
        break;

    // ==========================================
    // 위젯 삭제
    // ==========================================
    case 'delete_widget':
        $widget_id = isset($json_data['widget_id']) ? (int)$json_data['widget_id'] : (int)($_POST['widget_id'] ?? 0);
        if ($widget_id > 0) {
            sql_query("DELETE FROM {$g5['mg_main_widget_table']} WHERE widget_id = {$widget_id}");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
        }
        break;

    // ==========================================
    // 위젯 설정 저장
    // ==========================================
    case 'update_widget_config':
        $widget_id = (int)($_POST['widget_id'] ?? 0);
        $widget_config = isset($_POST['widget_config']) ? $_POST['widget_config'] : array();
        $widget_w = isset($_POST['widget_w']) ? (int)$_POST['widget_w'] : 0;
        $widget_h = isset($_POST['widget_h']) ? (int)$_POST['widget_h'] : 0;

        if ($widget_id <= 0) {
            echo json_encode(['success' => false, 'message' => '잘못된 요청입니다.']);
            break;
        }

        $widget = sql_fetch("SELECT * FROM {$g5['mg_main_widget_table']} WHERE widget_id = {$widget_id}");
        if (!$widget) {
            echo json_encode(['success' => false, 'message' => '존재하지 않는 위젯입니다.']);
            break;
        }

        // 크기 업데이트
        $grid_cols = (int)mg_config('grid_columns', 12);
        if ($grid_cols < 1) $grid_cols = 12;
        $size_sql = '';
        if ($widget_w > 0 && $widget_w <= $grid_cols) {
            $size_sql .= ", widget_w = {$widget_w}, widget_cols = {$widget_w}";
        }
        if ($widget_h > 0) {
            $size_sql .= ", widget_h = {$widget_h}";
        }

        // 설정 저장
        $config_json = sql_real_escape_string(json_encode($widget_config, JSON_UNESCAPED_UNICODE));
        sql_query("UPDATE {$g5['mg_main_widget_table']} SET widget_config = '{$config_json}'{$size_sql} WHERE widget_id = {$widget_id}");

        echo json_encode(['success' => true]);
        break;

    // ==========================================
    // 레이아웃 저장 (GridStack 2D 좌표)
    // ==========================================
    case 'save_layout':
        $items = $json_data['widgets'] ?? array();
        $grid_cols = (int)mg_config('grid_columns', 12);
        if ($grid_cols < 1) $grid_cols = 12;
        if (is_array($items)) {
            foreach ($items as $item) {
                $id = (int)($item['id'] ?? 0);
                $x = max(0, min($grid_cols - 1, (int)($item['x'] ?? 0)));
                $y = max(0, (int)($item['y'] ?? 0));
                $w = max(1, min($grid_cols, (int)($item['w'] ?? 6)));
                $h = max(1, (int)($item['h'] ?? 2));
                if ($id > 0) {
                    sql_query("UPDATE {$g5['mg_main_widget_table']}
                        SET widget_x={$x}, widget_y={$y}, widget_w={$w}, widget_h={$h}, widget_cols={$w}
                        WHERE widget_id={$id}");
                }
            }
        }
        echo json_encode(['success' => true]);
        break;

    // ==========================================
    // 순서 저장 (하위 호환)
    // ==========================================
    case 'save_order':
        $widgets = $json_data['widgets'] ?? array();
        if (is_array($widgets)) {
            foreach ($widgets as $widget_data) {
                $widget_id = (int)($widget_data['id'] ?? 0);
                $widget_order = (int)($widget_data['order'] ?? 0);
                if ($widget_id > 0) {
                    sql_query("UPDATE {$g5['mg_main_widget_table']} SET widget_order = {$widget_order} WHERE widget_id = {$widget_id}");
                }
            }
        }
        echo json_encode(['success' => true]);
        break;

    // ==========================================
    // 그리드 설정 저장
    // ==========================================
    case 'save_grid_settings':
        $grid_rows = max(4, min(100, (int)($json_data['grid_rows'] ?? $_POST['grid_rows'] ?? 40)));
        $new_columns = max(12, min(48, (int)($json_data['grid_columns'] ?? $_POST['grid_columns'] ?? 12)));

        // 기존 칸 수 확인 (위젯 좌표 변환용)
        $old_columns = (int)mg_config('grid_columns', 12);
        if ($old_columns < 1) $old_columns = 12;

        // 가로 칸 수가 변경되면 모든 위젯의 x, w를 비례 변환
        if ($new_columns != $old_columns) {
            $ratio = $new_columns / $old_columns;
            $result = sql_query("SELECT widget_id, widget_x, widget_w FROM {$g5['mg_main_widget_table']} WHERE widget_use = 1");
            while ($row = sql_fetch_array($result)) {
                $new_x = (int)round($row['widget_x'] * $ratio);
                $new_w = max(1, (int)round($row['widget_w'] * $ratio));
                // 오버플로 방지
                if ($new_x + $new_w > $new_columns) {
                    $new_x = max(0, $new_columns - $new_w);
                }
                if ($new_w > $new_columns) $new_w = $new_columns;
                sql_query("UPDATE {$g5['mg_main_widget_table']}
                    SET widget_x = {$new_x}, widget_w = {$new_w}, widget_cols = {$new_w}
                    WHERE widget_id = {$row['widget_id']}");
            }
        }

        mg_config_set('grid_columns', $new_columns, '그리드 가로 칸 수');
        mg_config_set('grid_rows', $grid_rows, '그리드 세로 칸 수');

        echo json_encode(['success' => true]);
        break;

    // ==========================================
    // 행 높이 저장 (하위 호환)
    // ==========================================
    case 'save_row_height':
        $row_height = (int)($_POST['row_height'] ?? 0);
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
