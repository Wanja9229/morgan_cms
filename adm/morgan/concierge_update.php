<?php
/**
 * Morgan Edition - 의뢰 관리 처리
 */

$sub_menu = "801800";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$w = isset($_POST['w']) ? $_POST['w'] : '';
$cc_id = isset($_POST['cc_id']) ? (int)$_POST['cc_id'] : 0;

$redirect_url = G5_ADMIN_URL.'/morgan/concierge.php';

if ($w === 'cancel' && $cc_id > 0) {
    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if ($cc && in_array($cc['cc_status'], array('recruiting', 'matched'))) {
        sql_query("UPDATE {$g5['mg_concierge_table']} SET cc_status = 'cancelled' WHERE cc_id = {$cc_id}");

        // 긴급 의뢰 환불
        if ($cc['cc_tier'] === 'urgent') {
            $cost = (int)mg_config('concierge_reward_urgent', 100);
            insert_point($cc['mb_id'], $cost, '긴급 의뢰 관리자 취소 환불',
                        'mg_concierge', $cc_id, '관리자환불');
        }

        alert('의뢰가 취소되었습니다.', $redirect_url);
    } else {
        alert('취소할 수 없는 상태입니다.', $redirect_url);
    }
}

alert('잘못된 요청입니다.', $redirect_url);
