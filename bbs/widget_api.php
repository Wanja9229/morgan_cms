<?php
/**
 * Morgan Edition - 위젯 API
 * 유저별 사이드바 위젯 순서/표시 저장/조회
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false, 'error' => '로그인이 필요합니다.'));
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
$mb_id = $member['mb_id'];
$mb_id_esc = sql_real_escape_string($mb_id);

// 전체 위젯 목록
$valid_widgets = array('member_card', 'inventory', 'gift', 'achievement', 'notification', 'pioneer', 'expedition', 'radio');
// 토글 가능 위젯 (고정 제외)
$toggleable_widgets = array('inventory', 'gift', 'achievement', 'notification', 'pioneer', 'expedition');
$max_optional = 3;

switch ($action) {
    case 'save_order':
        $order = isset($_POST['order']) ? $_POST['order'] : array();
        if (!is_array($order) || count($order) === 0) {
            echo json_encode(array('success' => false, 'error' => '순서 데이터가 없습니다.'));
            exit;
        }

        $order = array_values(array_filter($order, function($w) use ($valid_widgets) {
            return in_array($w, $valid_widgets);
        }));

        foreach ($order as $idx => $widget_name) {
            sql_query("INSERT INTO {$g5['mg_user_widget_table']}
                (mb_id, widget_name, widget_order) VALUES ('{$mb_id_esc}', '{$widget_name}', {$idx})
                ON DUPLICATE KEY UPDATE widget_order = {$idx}, updated_at = NOW()");
        }

        echo json_encode(array('success' => true));
        break;

    case 'toggle_widget':
        $widget_name = isset($_POST['widget_name']) ? preg_replace('/[^a-z_]/', '', $_POST['widget_name']) : '';
        $visible = isset($_POST['visible']) ? (int)$_POST['visible'] : 0;
        $visible = $visible ? 1 : 0;

        if (!in_array($widget_name, $toggleable_widgets)) {
            echo json_encode(array('success' => false, 'error' => '토글할 수 없는 위젯입니다.'));
            exit;
        }

        // ON으로 변경 시 3개 제한 체크
        if ($visible === 1) {
            // 현재 ON인 토글 가능 위젯 수 (DB에 있는 것 + 기본 ON인 것)
            $on_result = sql_query("SELECT widget_name FROM {$g5['mg_user_widget_table']}
                WHERE mb_id = '{$mb_id_esc}' AND widget_visible = 1
                AND widget_name IN ('".implode("','", $toggleable_widgets)."')");
            $on_names = array();
            if ($on_result) {
                while ($r = sql_fetch_array($on_result)) {
                    $on_names[] = $r['widget_name'];
                }
            }
            // DB에 행이 없는 기본 ON 위젯 (inventory, gift)
            $default_on = array('inventory', 'gift');
            foreach ($default_on as $dw) {
                if (!in_array($dw, $on_names)) {
                    // DB에 행 자체가 없으면 기본 ON 상태
                    $chk = sql_fetch("SELECT widget_visible FROM {$g5['mg_user_widget_table']}
                        WHERE mb_id = '{$mb_id_esc}' AND widget_name = '{$dw}'");
                    if (!$chk) {
                        $on_names[] = $dw;
                    }
                }
            }
            // 현재 위젯이 이미 ON이 아닌 경우에만 카운트 추가
            if (!in_array($widget_name, $on_names)) {
                if (count($on_names) >= $max_optional) {
                    echo json_encode(array('success' => false, 'error' => '최대 '.$max_optional.'개까지 선택할 수 있습니다.', 'active_count' => count($on_names)));
                    exit;
                }
            }
        }

        sql_query("INSERT INTO {$g5['mg_user_widget_table']}
            (mb_id, widget_name, widget_order, widget_visible) VALUES ('{$mb_id_esc}', '{$widget_name}', 99, {$visible})
            ON DUPLICATE KEY UPDATE widget_visible = {$visible}, updated_at = NOW()");

        // 현재 active count 반환
        $cnt_result = sql_query("SELECT COUNT(*) as cnt FROM {$g5['mg_user_widget_table']}
            WHERE mb_id = '{$mb_id_esc}' AND widget_visible = 1
            AND widget_name IN ('".implode("','", $toggleable_widgets)."')");
        $cnt_row = sql_fetch_array($cnt_result);
        $active_count = $cnt_row ? (int)$cnt_row['cnt'] : 0;
        // 기본 ON이지만 DB에 아직 없는 것도 카운트
        foreach (array('inventory', 'gift') as $dw) {
            $dchk = sql_fetch("SELECT uw_id FROM {$g5['mg_user_widget_table']} WHERE mb_id = '{$mb_id_esc}' AND widget_name = '{$dw}'");
            if (!$dchk) $active_count++;
        }

        echo json_encode(array('success' => true, 'active_count' => $active_count));
        break;

    case 'save_toggles':
        $on_widgets = isset($_POST['on']) ? $_POST['on'] : array();
        if (!is_array($on_widgets)) $on_widgets = array();
        // 유효한 토글 가능 위젯만 필터
        $on_widgets = array_values(array_filter($on_widgets, function($w) use ($toggleable_widgets) {
            return in_array($w, $toggleable_widgets);
        }));
        if (count($on_widgets) > $max_optional) {
            echo json_encode(array('success' => false, 'error' => '최대 '.$max_optional.'개까지 선택할 수 있습니다.'));
            exit;
        }
        // 전체 토글 가능 위젯을 OFF로 설정 후, 선택된 것만 ON
        foreach ($toggleable_widgets as $tw) {
            $tw_esc = sql_real_escape_string($tw);
            $vis = in_array($tw, $on_widgets) ? 1 : 0;
            sql_query("INSERT INTO {$g5['mg_user_widget_table']}
                (mb_id, widget_name, widget_order, widget_visible) VALUES ('{$mb_id_esc}', '{$tw_esc}', 99, {$vis})
                ON DUPLICATE KEY UPDATE widget_visible = {$vis}, updated_at = NOW()");
        }
        echo json_encode(array('success' => true, 'active_count' => count($on_widgets)));
        break;

    case 'get_order':
        $result = sql_query("SELECT widget_name, widget_order, widget_visible
            FROM {$g5['mg_user_widget_table']}
            WHERE mb_id = '{$mb_id_esc}' ORDER BY widget_order");

        $widgets = array();
        if ($result) {
            while ($row = sql_fetch_array($result)) {
                $widgets[] = $row;
            }
        }

        echo json_encode(array('success' => true, 'widgets' => $widgets));
        break;

    default:
        echo json_encode(array('success' => false, 'error' => '알 수 없는 액션'));
}
