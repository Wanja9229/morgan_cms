<?php
/**
 * Morgan Edition - 알림 처리
 */

$sub_menu = "800600";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

// 삭제 처리
if (isset($_POST['btn_delete']) && isset($_POST['chk'])) {
    foreach ($_POST['chk'] as $noti_id) {
        $noti_id = (int)$noti_id;
        sql_query("DELETE FROM {$g5['mg_notification_table']} WHERE noti_id = $noti_id");
    }
}

// 읽음 처리
if (isset($_POST['btn_read']) && isset($_POST['chk'])) {
    foreach ($_POST['chk'] as $noti_id) {
        $noti_id = (int)$noti_id;
        sql_query("UPDATE {$g5['mg_notification_table']} SET noti_read = 1 WHERE noti_id = $noti_id");
    }
}

goto_url('./notification.php?page='.$page);
