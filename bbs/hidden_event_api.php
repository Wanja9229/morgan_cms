<?php
/**
 * Morgan Edition - 히든 이벤트 API
 * GET  ?action=check → 확률 판정 + 토큰 발급
 * POST ?action=claim → 토큰 검증 + 보상 지급
 */
include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (!$is_member) {
    echo json_encode(array('success' => false));
    exit;
}

$mb_id = $member['mb_id'];
$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    // ─── 이벤트 출현 확인 ───
    case 'check':
        // 요청 간격 체크 (1초 미만 → 빈 응답)
        if (isset($_SESSION['mg_he_last_check'])) {
            if ((microtime(true) - $_SESSION['mg_he_last_check']) < 1.0) {
                echo json_encode(array('success' => true, 'event' => null));
                exit;
            }
        }
        $_SESSION['mg_he_last_check'] = microtime(true);

        // 오늘 전체 수령 횟수
        $today_total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_event_claim_table']}
            WHERE mb_id = '{$mb_id}' AND DATE(claimed_at) = CURDATE()");
        $today_count = (int)$today_total['cnt'];

        // 활성 이벤트 목록
        $now = date('Y-m-d H:i:s');
        $events = array();
        $result = sql_query("SELECT * FROM {$g5['mg_hidden_event_table']}
            WHERE is_active = 1
            AND (active_from IS NULL OR active_from <= '{$now}')
            AND (active_until IS NULL OR active_until >= '{$now}')
            ORDER BY event_id ASC");
        if ($result) while ($row = sql_fetch_array($result)) $events[] = $row;

        $triggered = null;
        foreach ($events as $ev) {
            // 전체 일일 한도 체크
            if ($today_count >= (int)$ev['daily_total']) break;

            // 이벤트별 일일 수령 체크
            $ev_today = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_event_claim_table']}
                WHERE mb_id = '{$mb_id}' AND event_id = {$ev['event_id']} AND DATE(claimed_at) = CURDATE()");
            if ((int)$ev_today['cnt'] >= (int)$ev['daily_limit']) continue;

            // 확률 판정
            $roll = mt_rand(1, 10000);
            $threshold = (int)($ev['probability'] * 100);
            if ($roll <= $threshold) {
                $triggered = $ev;
                break;
            }
        }

        if ($triggered) {
            // 토큰 생성
            $token_id = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 300); // 5분
            $mb_id_esc = sql_real_escape_string($mb_id);

            sql_query("INSERT INTO {$g5['mg_event_token_table']}
                (token_id, event_id, mb_id, expires_at)
                VALUES ('{$token_id}', {$triggered['event_id']}, '{$mb_id_esc}', '{$expires}')");

            // 만료 토큰 정리 (10% 확률로 실행, 부하 분산)
            if (mt_rand(1, 10) === 1) {
                sql_query("DELETE FROM {$g5['mg_event_token_table']}
                    WHERE claimed = 0 AND expires_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)");
            }

            echo json_encode(array(
                'success' => true,
                'event' => array(
                    'token' => $token_id,
                    'image_url' => $triggered['image_path'],
                    'title' => $triggered['title'],
                ),
            ), JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(array('success' => true, 'event' => null));
        }
        break;

    // ─── 보상 수령 ───
    case 'claim':
        $token_id = isset($_POST['token']) ? trim($_POST['token']) : '';
        if (!$token_id || strlen($token_id) !== 64) {
            echo json_encode(array('success' => false));
            exit;
        }

        $token_esc = $token_id;
        $mb_id_esc = sql_real_escape_string($mb_id);

        // 토큰 조회 + 검증
        $tk = sql_fetch("SELECT t.*, e.reward_type, e.reward_id, e.reward_amount, e.daily_limit, e.daily_total, e.is_active as event_active, e.title as event_title
            FROM {$g5['mg_event_token_table']} t
            JOIN {$g5['mg_hidden_event_table']} e ON t.event_id = e.event_id
            WHERE t.token_id = '{$token_esc}'");

        // 검증 실패 시 조용히 거부
        if (!$tk) {
            _he_suspicious($mb_id, 'invalid_token', $token_id);
            echo json_encode(array('success' => false));
            exit;
        }
        if ($tk['mb_id'] !== $mb_id) {
            _he_suspicious($mb_id, 'token_owner_mismatch', $token_id);
            echo json_encode(array('success' => false));
            exit;
        }
        if ($tk['claimed']) {
            echo json_encode(array('success' => false));
            exit;
        }
        if (strtotime($tk['expires_at']) < time()) {
            _he_suspicious($mb_id, 'expired_token', $token_id);
            echo json_encode(array('success' => false));
            exit;
        }
        if (!$tk['event_active']) {
            echo json_encode(array('success' => false));
            exit;
        }

        // 발급~수령 간격 체크 (0.5초 미만 → 봇 의심)
        $elapsed = time() - strtotime($tk['issued_at']);
        if ($elapsed < 1) {
            _he_suspicious($mb_id, 'too_fast_claim', "elapsed={$elapsed}s token={$token_id}");
            echo json_encode(array('success' => false));
            exit;
        }

        // 일일 한도 재확인
        $today_total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_event_claim_table']}
            WHERE mb_id = '{$mb_id_esc}' AND DATE(claimed_at) = CURDATE()");
        if ((int)$today_total['cnt'] >= (int)$tk['daily_total']) {
            echo json_encode(array('success' => false));
            exit;
        }
        $ev_today = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_event_claim_table']}
            WHERE mb_id = '{$mb_id_esc}' AND event_id = {$tk['event_id']} AND DATE(claimed_at) = CURDATE()");
        if ((int)$ev_today['cnt'] >= (int)$tk['daily_limit']) {
            echo json_encode(array('success' => false));
            exit;
        }

        // 보상 지급
        $reward_name = '';
        if ($tk['reward_type'] === 'point') {
            insert_point($mb_id, $tk['reward_amount'], '히든 이벤트: ' . $tk['event_title'], 'mg_hidden_event', $tk['event_id'], 'claim_' . $tk['token_id']);
            $reward_name = $tk['reward_amount'] . ' 포인트';
        } elseif ($tk['reward_type'] === 'stamina') {
            if (function_exists('mg_recover_stamina')) {
                $stam_result = mg_recover_stamina($mb_id, (int)$tk['reward_amount'], true);
                $reward_name = ($stam_result['recovered'] ?? $tk['reward_amount']) . ' 스태미나';
            }
        } else {
            if (function_exists('mg_add_material') && $tk['reward_id'] > 0) {
                mg_add_material($mb_id, $tk['reward_id'], $tk['reward_amount']);
                $mt_info = sql_fetch("SELECT mt_name FROM {$g5['mg_material_type_table']} WHERE mt_id = {$tk['reward_id']}");
                $reward_name = ($mt_info ? $mt_info['mt_name'] : '재료') . ' x' . $tk['reward_amount'];
            }
        }

        // 토큰 소비
        sql_query("UPDATE {$g5['mg_event_token_table']} SET claimed = 1, claimed_at = NOW() WHERE token_id = '{$token_esc}'");

        // 수령 로그
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $ip_esc = sql_real_escape_string($ip);
        sql_query("INSERT INTO {$g5['mg_event_claim_table']}
            (mb_id, event_id, reward_type, reward_amount, ip_address)
            VALUES ('{$mb_id_esc}', {$tk['event_id']}, '{$tk['reward_type']}', {$tk['reward_amount']}, '{$ip_esc}')");

        echo json_encode(array(
            'success' => true,
            'reward_type' => $tk['reward_type'],
            'reward_amount' => (int)$tk['reward_amount'],
            'reward_name' => $reward_name,
        ), JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(array('success' => false));
}

function _he_suspicious($mb_id, $reason, $details = '') {
    global $g5;
    $mb_esc = sql_real_escape_string($mb_id);
    $reason_esc = sql_real_escape_string($reason);
    $details_esc = sql_real_escape_string($details);
    sql_query("INSERT INTO {$g5['mg_event_suspicious_table']}
        (mb_id, reason, details) VALUES ('{$mb_esc}', '{$reason_esc}', '{$details_esc}')");
}
