<?php
/**
 * Morgan Edition - 알림 AJAX API
 *
 * action:
 *   count      - 미읽은 알림 수 (GET)
 *   list       - 알림 목록 (GET)
 *   read       - 개별 읽음 처리 (POST)
 *   read_all   - 전체 읽음 (POST)
 *   delete     - 개별 삭제 (POST)
 *   delete_read - 읽은 알림 전체 삭제 (POST)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

// 로그인 필수
if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');
$mb_id = $member['mb_id'];

switch ($action) {
    // 미읽은 알림 수
    case 'count':
        $count = mg_get_unread_notification_count($mb_id);
        echo json_encode(array('success' => true, 'data' => array('count' => $count)));
        break;

    // 알림 목록
    case 'list':
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $rows = isset($_GET['rows']) ? min(50, max(1, (int)$_GET['rows'])) : 10;
        $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] == '1';

        $result = mg_get_notifications($mb_id, $page, $rows, $unread_only);

        // 시간 포맷 변환
        $items = array();
        foreach ($result['items'] as $item) {
            $items[] = array(
                'noti_id'      => (int)$item['noti_id'],
                'noti_type'    => $item['noti_type'],
                'noti_title'   => $item['noti_title'],
                'noti_content' => $item['noti_content'],
                'noti_url'     => $item['noti_url'],
                'noti_read'    => (int)$item['noti_read'],
                'noti_datetime' => $item['noti_datetime'],
                'time_ago'     => mg_time_ago($item['noti_datetime']),
            );
        }

        echo json_encode(array(
            'success' => true,
            'data' => array(
                'items' => $items,
                'total' => $result['total'],
                'total_page' => $result['total_page'],
                'page' => $page,
            )
        ));
        break;

    // 개별 읽음 처리
    case 'read':
        $noti_id = isset($_POST['noti_id']) ? (int)$_POST['noti_id'] : 0;
        if ($noti_id <= 0) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            break;
        }
        mg_mark_notification_read($noti_id, $mb_id);
        echo json_encode(array('success' => true));
        break;

    // 전체 읽음
    case 'read_all':
        mg_mark_all_notifications_read($mb_id);
        echo json_encode(array('success' => true, 'message' => '모든 알림을 읽음 처리했습니다.'));
        break;

    // 개별 삭제
    case 'delete':
        $noti_id = isset($_POST['noti_id']) ? (int)$_POST['noti_id'] : 0;
        if ($noti_id <= 0) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
            break;
        }
        mg_delete_notification($noti_id, $mb_id);
        echo json_encode(array('success' => true));
        break;

    // 읽은 알림 전체 삭제
    case 'delete_read':
        mg_delete_all_read_notifications($mb_id);
        echo json_encode(array('success' => true, 'message' => '읽은 알림을 모두 삭제했습니다.'));
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 action입니다.'));
        break;
}

/**
 * 상대 시간 표시
 */
function mg_time_ago($datetime) {
    $now = time();
    $time = strtotime($datetime);
    $diff = $now - $time;

    if ($diff < 60) return '방금 전';
    if ($diff < 3600) return floor($diff / 60) . '분 전';
    if ($diff < 86400) return floor($diff / 3600) . '시간 전';
    if ($diff < 604800) return floor($diff / 86400) . '일 전';
    return date('Y-m-d', $time);
}
