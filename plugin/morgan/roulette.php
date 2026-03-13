<?php
/**
 * Morgan Edition - 룰렛 시스템 헬퍼
 */
if (!defined('_GNUBOARD_')) exit;

/**
 * 룰렛 활성화 여부
 */
function mg_roulette_enabled()
{
    return mg_config('roulette_use', '0') == '1';
}

/**
 * 스핀 가능 여부 체크
 * @return array ['ok' => bool, 'reason' => string]
 */
function mg_roulette_can_spin($mb_id)
{
    global $g5;

    if (!mg_roulette_enabled()) {
        return array('ok' => false, 'reason' => '룰렛이 비활성화 상태입니다.');
    }

    // 미완료 벌칙 체크
    if (mg_roulette_has_active_penalty($mb_id)) {
        return array('ok' => false, 'reason' => '수행 중인 벌칙을 완료해야 다음 룰렛을 돌릴 수 있습니다.');
    }

    // 포인트 체크
    $cost = (int)mg_config('roulette_cost', 100);
    $point = (int)sql_fetch("SELECT mb_point FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'")['mb_point'];
    if ($point < $cost) {
        return array('ok' => false, 'reason' => '포인트가 부족합니다. (필요: '.number_format($cost).'P)');
    }

    // 일일 한도
    $daily_limit = (int)mg_config('roulette_daily_limit', 3);
    if ($daily_limit > 0) {
        $today_count = (int)sql_fetch("SELECT COUNT(*) AS cnt FROM {$g5['mg_roulette_log_table']}
            WHERE mb_id = '".sql_real_escape_string($mb_id)."'
            AND rl_source = 'spin'
            AND DATE(rl_datetime) = CURDATE()")['cnt'];
        if ($today_count >= $daily_limit) {
            return array('ok' => false, 'reason' => '오늘 룰렛 사용 횟수를 초과했습니다. (제한: '.$daily_limit.'회)');
        }
    }

    // 쿨다운
    $cooldown = (int)mg_config('roulette_cooldown', 0);
    if ($cooldown > 0) {
        $last = sql_fetch("SELECT rl_datetime FROM {$g5['mg_roulette_log_table']}
            WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND rl_source = 'spin'
            ORDER BY rl_id DESC LIMIT 1");
        if ($last && $last['rl_datetime']) {
            $diff = time() - strtotime($last['rl_datetime']);
            $remain = ($cooldown * 60) - $diff;
            if ($remain > 0) {
                $min = ceil($remain / 60);
                return array('ok' => false, 'reason' => '쿨다운 중입니다. ('.$min.'분 후 가능)');
            }
        }
    }

    return array('ok' => true, 'reason' => '');
}

/**
 * 가중치 기반 랜덤 추첨
 */
function mg_roulette_pick_prize()
{
    global $g5;
    $result = sql_query("SELECT * FROM {$g5['mg_roulette_prize_table']} WHERE rp_use = 1 ORDER BY rp_order, rp_id");
    $prizes = array();
    $total_weight = 0;
    while ($row = sql_fetch_array($result)) {
        $prizes[] = $row;
        $total_weight += (int)$row['rp_weight'];
    }
    if (empty($prizes) || $total_weight <= 0) return null;

    $rand = mt_rand(1, $total_weight);
    $cumulative = 0;
    foreach ($prizes as $p) {
        $cumulative += (int)$p['rp_weight'];
        if ($rand <= $cumulative) return $p;
    }
    return end($prizes);
}

/**
 * 활성 항목 목록 (휠 렌더링용)
 */
function mg_roulette_get_prizes()
{
    global $g5;
    $result = sql_query("SELECT * FROM {$g5['mg_roulette_prize_table']} WHERE rp_use = 1 ORDER BY rp_order, rp_id");
    $prizes = array();
    while ($row = sql_fetch_array($result)) {
        $prizes[] = $row;
    }
    return $prizes;
}

/**
 * 보상 지급
 */
function mg_roulette_apply_reward($mb_id, $prize, $rl_id)
{
    global $g5;
    $val = json_decode($prize['rp_reward_value'] ?? '{}', true);
    if (!$val) $val = array();

    switch ($prize['rp_reward_type']) {
        case 'point':
            $amount = (int)($val['amount'] ?? 0);
            if ($amount > 0) {
                insert_point($mb_id, $amount, '룰렛 보상: '.$prize['rp_name'], 'roulette', $rl_id, 'reward');
            }
            break;

        case 'material':
            $mt_id = (int)($val['mt_id'] ?? 0);
            $count = (int)($val['count'] ?? 1);
            if ($mt_id > 0) {
                $exists = sql_fetch("SELECT um_id, um_count FROM {$g5['mg_user_material_table']}
                    WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND mt_id = {$mt_id}");
                if ($exists && $exists['um_id']) {
                    sql_query("UPDATE {$g5['mg_user_material_table']} SET um_count = um_count + {$count} WHERE um_id = {$exists['um_id']}");
                } else {
                    sql_query("INSERT INTO {$g5['mg_user_material_table']} (mb_id, mt_id, um_count) VALUES ('".sql_real_escape_string($mb_id)."', {$mt_id}, {$count})");
                }
            }
            break;

        case 'item':
            $si_id = (int)($val['si_id'] ?? 0);
            if ($si_id > 0) {
                $exists = sql_fetch("SELECT iv_id, iv_count FROM {$g5['mg_inventory_table']}
                    WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND si_id = {$si_id}");
                if ($exists && $exists['iv_id']) {
                    sql_query("UPDATE {$g5['mg_inventory_table']} SET iv_count = iv_count + 1 WHERE iv_id = {$exists['iv_id']}");
                } else {
                    sql_query("INSERT INTO {$g5['mg_inventory_table']} (mb_id, si_id, iv_count, iv_datetime) VALUES ('".sql_real_escape_string($mb_id)."', {$si_id}, 1, NOW())");
                }
            }
            break;

        case 'title':
            $tp_id = (int)($val['tp_id'] ?? 0);
            if ($tp_id > 0) {
                $exists = sql_fetch("SELECT mt_id FROM {$g5['mg_member_title_table']}
                    WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND tp_id = {$tp_id}");
                if (!$exists || !$exists['mt_id']) {
                    sql_query("INSERT INTO {$g5['mg_member_title_table']} (mb_id, tp_id, mt_datetime) VALUES ('".sql_real_escape_string($mb_id)."', {$tp_id}, NOW())");
                }
            }
            break;
    }
}

/**
 * 벌칙 적용 (시스템 강제)
 */
function mg_roulette_apply_penalty($mb_id, $prize, $rl_id)
{
    global $g5;
    $val = json_decode($prize['rp_reward_value'] ?? '{}', true);
    if (!$val) $val = array();
    $duration = (int)$prize['rp_duration_hours'];
    $expires = $duration > 0 ? date('Y-m-d H:i:s', time() + $duration * 3600) : null;

    $reward_type = $prize['rp_reward_type'];

    // 닉네임 강제 변경
    if (in_array($reward_type, array('nickname', 'log_nickname'))) {
        $new_nick = $val['nickname'] ?? '이름 모를 고양이';
        $old_nick = sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'")['mb_nick'];
        sql_query("UPDATE {$g5['member_table']} SET mb_nick = '".sql_real_escape_string($new_nick)."' WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
        sql_query("UPDATE {$g5['mg_roulette_log_table']} SET
            rl_original_nick = '".sql_real_escape_string($old_nick)."',
            rl_expires_at = ".($expires ? "'{$expires}'" : "NULL")."
            WHERE rl_id = {$rl_id}");
    }

    // 프로필 이미지 강제 변경
    if (in_array($reward_type, array('profile_image', 'log_image'))) {
        $penalty_img = $prize['rp_image'] ?? '';
        sql_query("UPDATE {$g5['mg_roulette_log_table']} SET
            rl_penalty_image = '".sql_real_escape_string($penalty_img)."',
            rl_expires_at = ".($expires ? "'{$expires}'" : "NULL")."
            WHERE rl_id = {$rl_id}");
    }
}

/**
 * 닉변/프사 만료 체크 및 복원 (세션당 1회)
 */
function mg_roulette_check_expiry($mb_id)
{
    global $g5;

    $result = sql_query("SELECT rl_id, rl_original_nick, rl_penalty_image, rp.rp_reward_type
        FROM {$g5['mg_roulette_log_table']} rl
        JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
        WHERE rl.mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl.rl_status = 'active'
        AND rl.rl_expires_at IS NOT NULL
        AND rl.rl_expires_at < NOW()");

    if ($result === false) return;

    while ($row = sql_fetch_array($result)) {
        // 닉네임 복원
        if ($row['rl_original_nick'] && in_array($row['rp_reward_type'], array('nickname', 'log_nickname'))) {
            sql_query("UPDATE {$g5['member_table']} SET mb_nick = '".sql_real_escape_string($row['rl_original_nick'])."'
                WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
        }

        // 시간제 벌칙 만료 → completed (로그 제출형은 로그 제출까지 유지)
        $rp_type = $row['rp_reward_type'];
        if (in_array($rp_type, array('nickname', 'profile_image'))) {
            // 순수 시간제 → 완료
            sql_query("UPDATE {$g5['mg_roulette_log_table']} SET rl_status = 'completed' WHERE rl_id = {$row['rl_id']}");
        } else {
            // log_nickname, log_image → 시간제 효과만 해제, 로그 제출 대기 유지
            sql_query("UPDATE {$g5['mg_roulette_log_table']} SET rl_expires_at = NULL WHERE rl_id = {$row['rl_id']}");
        }

        mg_notify($mb_id, 'roulette', '룰렛 벌칙이 만료되어 복원되었습니다.', '', G5_BBS_URL.'/roulette.php');
    }
}

/**
 * pending 상태 타임아웃 체크 (N시간 경과 시 자동 active)
 */
function mg_roulette_check_pending_timeout($mb_id)
{
    global $g5;
    $hours = (int)mg_config('roulette_pending_hours', 24);

    $result = sql_query("SELECT rl.rl_id, rl.rp_id, rp.rp_reward_type, rp.rp_type
        FROM {$g5['mg_roulette_log_table']} rl
        JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
        WHERE rl.mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl.rl_status = 'pending'
        AND rl.rl_datetime < DATE_SUB(NOW(), INTERVAL {$hours} HOUR)");

    if ($result === false) return;

    while ($row = sql_fetch_array($result)) {
        sql_query("UPDATE {$g5['mg_roulette_log_table']} SET rl_status = 'active' WHERE rl_id = {$row['rl_id']}");

        // 시스템 강제 벌칙 적용
        if ($row['rp_type'] === 'penalty') {
            $prize = sql_fetch("SELECT * FROM {$g5['mg_roulette_prize_table']} WHERE rp_id = {$row['rp_id']}");
            if ($prize) {
                mg_roulette_apply_penalty($mb_id, $prize, $row['rl_id']);
            }
        }
    }
}

/**
 * 미완료 벌칙 존재 여부 (다음 룰렛 차단 기준)
 */
function mg_roulette_has_active_penalty($mb_id)
{
    global $g5;
    $row = sql_fetch("SELECT rl_id FROM {$g5['mg_roulette_log_table']}
        WHERE mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl_status IN ('pending', 'active')
        AND rl_id IN (SELECT rl.rl_id FROM {$g5['mg_roulette_log_table']} rl
            JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
            WHERE rp.rp_type = 'penalty')
        LIMIT 1");
    return !empty($row['rl_id']);
}

/**
 * 현재 활성 벌칙 정보 (프론트 표시용)
 */
function mg_roulette_get_active_penalty($mb_id)
{
    global $g5;
    return sql_fetch("SELECT rl.*, rp.rp_name, rp.rp_desc, rp.rp_type, rp.rp_reward_type, rp.rp_require_log, rp.rp_image
        FROM {$g5['mg_roulette_log_table']} rl
        JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
        WHERE rl.mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl.rl_status IN ('pending', 'active')
        AND rp.rp_type = 'penalty'
        ORDER BY rl.rl_id DESC LIMIT 1");
}

/**
 * 무효화 처리
 */
function mg_roulette_nullify($rl_id, $mb_id)
{
    global $g5;
    $log = sql_fetch("SELECT rl.*, rp.rp_reward_type FROM {$g5['mg_roulette_log_table']} rl
        JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
        WHERE rl.rl_id = {$rl_id} AND rl.mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl.rl_status = 'pending'");
    if (!$log) return false;

    // 인벤토리 차감
    if (!_mg_roulette_consume_item($mb_id, 'roulette_nullify')) return false;

    sql_query("UPDATE {$g5['mg_roulette_log_table']} SET rl_status = 'nullified' WHERE rl_id = {$rl_id}");
    return true;
}

/**
 * 떠넘기기 처리
 */
function mg_roulette_transfer($rl_id, $from_mb_id, $to_mb_id, $type = 'transfer_random')
{
    global $g5;
    $log = sql_fetch("SELECT rl.*, rp.rp_name FROM {$g5['mg_roulette_log_table']} rl
        JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
        WHERE rl.rl_id = {$rl_id} AND rl.mb_id = '".sql_real_escape_string($from_mb_id)."'
        AND rl.rl_status = 'pending' AND rl.rl_transfer_count = 0");
    if (!$log) return false;

    $item_type = ($type === 'transfer_target') ? 'roulette_transfer_target' : 'roulette_transfer_random';
    if (!_mg_roulette_consume_item($from_mb_id, $item_type)) return false;

    // 원본 transferred
    sql_query("UPDATE {$g5['mg_roulette_log_table']} SET rl_status = 'transferred' WHERE rl_id = {$rl_id}");

    // 새 로그 생성
    $to_mb_esc = sql_real_escape_string($to_mb_id);
    $from_mb_esc = sql_real_escape_string($from_mb_id);
    sql_query("INSERT INTO {$g5['mg_roulette_log_table']}
        (mb_id, rp_id, rl_source, rl_from_mb_id, rl_status, rl_transfer_count, rl_cost, rl_datetime)
        VALUES ('{$to_mb_esc}', {$log['rp_id']}, '{$type}', '{$from_mb_esc}', 'pending', 1, 0, NOW())");

    // 알림
    $reveal = mg_config('roulette_transfer_reveal', '0') == '1';
    $from_name = $reveal ? get_text(sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '{$from_mb_esc}'")['mb_nick']) : '누군가';
    mg_notify($to_mb_id, 'roulette', $from_name.'가 벌칙을 떠넘겼습니다: '.$log['rp_name'], '', G5_BBS_URL.'/roulette.php');

    return true;
}

/**
 * 랜덤 대상 선택 (최근 7일 로그인, 본인 제외)
 */
function mg_roulette_random_target($exclude_mb_id)
{
    global $g5;
    $exc = sql_real_escape_string($exclude_mb_id);
    $result = sql_query("SELECT mb_id FROM {$g5['member_table']}
        WHERE mb_id != '{$exc}' AND mb_leave_dt = '' AND mb_intercept_date = ''
        AND mb_datetime > DATE_SUB(NOW(), INTERVAL 30 DAY)
        ORDER BY RAND() LIMIT 1");
    if ($result === false) return null;
    $row = sql_fetch_array($result);
    return $row ? $row['mb_id'] : null;
}

/**
 * 벌칙 로그 작성으로 완료 처리
 */
function mg_roulette_complete_log($rl_id, $mb_id, $bo_table, $wr_id)
{
    global $g5;
    $log = sql_fetch("SELECT rl_id, rl_status FROM {$g5['mg_roulette_log_table']}
        WHERE rl_id = {$rl_id} AND mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl_status = 'active'");
    if (!$log) return false;

    sql_query("UPDATE {$g5['mg_roulette_log_table']} SET
        rl_status = 'completed',
        rl_bo_table = '".sql_real_escape_string($bo_table)."',
        rl_wr_id = {$wr_id}
        WHERE rl_id = {$rl_id}");
    return true;
}

/**
 * 잭팟 처리
 */
function mg_roulette_jackpot($mb_id, $rl_id)
{
    global $g5;
    $pool = (int)mg_config('roulette_jackpot_pool', 0);
    if ($pool <= 0) return;

    insert_point($mb_id, $pool, '룰렛 잭팟 당첨!', 'roulette', $rl_id, 'jackpot');
    mg_set_config('roulette_jackpot_pool', '0');

    // 전체 알림
    $nick = get_text(sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'")['mb_nick']);
    $msg = $nick . '님이 잭팟 ' . number_format($pool) . ' 포인트를 획득했습니다!';

    // 최근 활성 회원들에게 알림
    $active = sql_query("SELECT mb_id FROM {$g5['member_table']}
        WHERE mb_id != '".sql_real_escape_string($mb_id)."'
        AND mb_leave_dt = '' AND mb_intercept_date = ''
        AND mb_today_login > DATE_SUB(NOW(), INTERVAL 7 DAY)
        LIMIT 100");
    if ($active !== false) {
        while ($r = sql_fetch_array($active)) {
            mg_notify($r['mb_id'], 'roulette', $msg, '', G5_BBS_URL.'/roulette.php');
        }
    }

    sql_query("UPDATE {$g5['mg_roulette_log_table']} SET rl_status = 'completed' WHERE rl_id = {$rl_id}");
}

/**
 * 활성 프사 벌칙 이미지 (게시판 렌더링용)
 */
function mg_roulette_get_active_penalty_image($mb_id)
{
    global $g5;
    static $cache = array();
    if (isset($cache[$mb_id])) return $cache[$mb_id];

    $row = sql_fetch("SELECT rl.rl_penalty_image
        FROM {$g5['mg_roulette_log_table']} rl
        JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
        WHERE rl.mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl.rl_status = 'active'
        AND rp.rp_reward_type IN ('profile_image', 'log_image')
        AND rl.rl_penalty_image != ''
        AND (rl.rl_expires_at IS NULL OR rl.rl_expires_at > NOW())
        LIMIT 1");

    $cache[$mb_id] = ($row && $row['rl_penalty_image']) ? $row['rl_penalty_image'] : '';
    return $cache[$mb_id];
}

/**
 * 오늘 스핀 횟수
 */
function mg_roulette_today_count($mb_id)
{
    global $g5;
    return (int)sql_fetch("SELECT COUNT(*) AS cnt FROM {$g5['mg_roulette_log_table']}
        WHERE mb_id = '".sql_real_escape_string($mb_id)."'
        AND rl_source = 'spin'
        AND DATE(rl_datetime) = CURDATE()")['cnt'];
}

/**
 * 내부: 상점 아이템 소모
 */
function _mg_roulette_consume_item($mb_id, $si_type)
{
    global $g5;
    $inv = sql_fetch("SELECT iv.iv_id, iv.iv_count FROM {$g5['mg_inventory_table']} iv
        JOIN {$g5['mg_shop_item_table']} si ON iv.si_id = si.si_id
        WHERE iv.mb_id = '".sql_real_escape_string($mb_id)."'
        AND si.si_type = '".sql_real_escape_string($si_type)."'
        AND iv.iv_count > 0
        LIMIT 1");
    if (!$inv || !$inv['iv_id']) return false;

    sql_query("UPDATE {$g5['mg_inventory_table']} SET iv_count = iv_count - 1 WHERE iv_id = {$inv['iv_id']}");
    return true;
}
