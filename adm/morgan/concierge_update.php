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

// 관리자 강제 취소 (모집중/매칭완료)
if ($w === 'cancel' && $cc_id > 0) {
    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if ($cc && in_array($cc['cc_status'], array('recruiting', 'matched'))) {
        sql_query("UPDATE {$g5['mg_concierge_table']} SET cc_status = 'cancelled' WHERE cc_id = {$cc_id}");

        // 전액 환불
        if ((int)$cc['cc_point_total'] > 0) {
            insert_point($cc['mb_id'], (int)$cc['cc_point_total'],
                '의뢰 관리자 취소 환불: ' . strip_tags($cc['cc_title']),
                'mg_concierge', $cc_id, '관리자취소환불');
        }

        alert('의뢰가 취소되었습니다. 포인트가 전액 환불되었습니다.', $redirect_url);
    } else {
        alert('취소할 수 없는 상태입니다.', $redirect_url);
    }
}

// 관리자 강제 완료 (매칭완료 → 정산 + 완료)
if ($w === 'force_complete' && $cc_id > 0) {
    $result = mg_settle_concierge('admin', $cc_id, true);
    if ($result['success']) {
        alert('의뢰가 강제 완료되었습니다. 제출 수행자에게 보상이 지급되고, 미제출분은 의뢰자에게 환불되었습니다.', $redirect_url);
    } else {
        alert($result['message'], $redirect_url);
    }
}

// 관리자 강제 종료 (매칭완료 → 미이행 + 페널티)
if ($w === 'force_close' && $cc_id > 0) {
    $cc = sql_fetch("SELECT * FROM {$g5['mg_concierge_table']} WHERE cc_id = {$cc_id}");
    if ($cc && $cc['cc_status'] === 'matched') {
        // 이미 결과 제출한 수행자는 제외, 미제출자만 force_closed
        $submitted_ids = array();
        $sub_r = sql_query("SELECT ca_id FROM {$g5['mg_concierge_result_table']} WHERE cc_id = {$cc_id}");
        if ($sub_r) { while ($sr = sql_fetch_array($sub_r)) $submitted_ids[] = (int)$sr['ca_id']; }

        $selected = sql_query("SELECT ca_id, mb_id FROM {$g5['mg_concierge_apply_table']}
                               WHERE cc_id = {$cc_id} AND ca_status = 'selected'");
        $actual_selected = 0;
        while ($row = sql_fetch_array($selected)) {
            $actual_selected++;
            if (!in_array((int)$row['ca_id'], $submitted_ids)) {
                sql_query("UPDATE {$g5['mg_concierge_apply_table']}
                           SET ca_status = 'force_closed' WHERE ca_id = " . (int)$row['ca_id']);
                mg_notify($row['mb_id'], 'concierge_force_close',
                         '의뢰 관리자 강제 종료',
                         '"' . $cc['cc_title'] . '" 의뢰가 관리자에 의해 강제 종료되었습니다.',
                         G5_BBS_URL . '/concierge.php?tab=market&cc_id=' . $cc_id);
            }
        }

        // 미제출자 분만 환불
        if ((int)$cc['cc_point_total'] > 0) {
            $submitted_count = count($submitted_ids);
            if ($actual_selected === 0) $actual_selected = max(1, (int)$cc['cc_max_members']);
            $unpaid_count = $actual_selected - $submitted_count;
            if ($unpaid_count > 0) {
                $per_person = (int)floor((int)$cc['cc_point_total'] / $actual_selected);
                $refund = $per_person * $unpaid_count;
                if ($refund > 0) {
                    insert_point($cc['mb_id'], $refund,
                        '의뢰 관리자 강제종료 환불: ' . strip_tags($cc['cc_title']) . " ({$unpaid_count}명분)",
                        'mg_concierge', $cc_id, '관리자강제종료환불');
                }
            }
        }

        sql_query("UPDATE {$g5['mg_concierge_table']} SET cc_status = 'force_closed' WHERE cc_id = {$cc_id}");

        alert('의뢰가 강제 종료되었습니다. 미제출 수행자에게 페널티가 부여되고, 미제출분 포인트가 환불되었습니다.', $redirect_url);
    } else {
        alert('강제 종료할 수 없는 상태입니다.', $redirect_url);
    }
}

alert('잘못된 요청입니다.', $redirect_url);
