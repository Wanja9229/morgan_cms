<?php
/**
 * Morgan Edition - 전투 API (AJAX)
 *
 * GET:  list, poll, encounter_detail
 * POST: join, battle_action, allocate_stat, reset_stats, equip_skill
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

header('Content-Type: application/json; charset=utf-8');

if (mg_config('battle_use', '1') != '1') {
    echo json_encode(array('success' => false, 'message' => '전투 기능이 비활성화되어 있습니다.'));
    exit;
}
if (!$is_member) {
    echo json_encode(array('success' => false, 'message' => '로그인이 필요합니다.'));
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');
$mb_id = $member['mb_id'];

switch ($action) {

    // ═══ 활성 전투 목록 ═══
    case 'list':
        $encounters = array();
        $res = sql_query("SELECT e.*, m.bm_name, m.bm_image
                          FROM {$g5['mg_battle_encounter_table']} e
                          LEFT JOIN {$g5['mg_battle_monster_table']} m ON e.bm_id = m.bm_id
                          WHERE e.be_status IN ('discovered', 'active')
                          ORDER BY e.be_discovered_at DESC
                          LIMIT 20");
        while ($row = sql_fetch_array($res)) {
            $monsters = json_decode($row['be_monsters'] ?? '[]', true);
            $total_hp = 0; $total_max = 0;
            if (is_array($monsters)) {
                foreach ($monsters as $m) {
                    $total_hp += (int)($m['hp'] ?? 0);
                    $total_max += (int)($m['max_hp'] ?? 1);
                }
            }

            // 참여자 수
            $slot_count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_battle_slot_table']} WHERE be_id = " . (int)$row['be_id']);

            // 남은 시간
            $time_remaining = '';
            if ($row['be_started_at']) {
                $remaining = (int)$row['be_time_limit'] - (time() - strtotime($row['be_started_at']));
                if ($remaining > 0) {
                    $time_remaining = sprintf('%02d:%02d:%02d', floor($remaining/3600), floor(($remaining%3600)/60), $remaining%60);
                } else {
                    $time_remaining = '시간 초과';
                }
            } else {
                $time_remaining = '대기 중';
            }

            // 파견지 좌표
            $ea_data = null;
            if ((int)$row['ea_id'] > 0) {
                $ea_data = sql_fetch("SELECT ea_name, ea_map_x, ea_map_y FROM {$g5['mg_expedition_area_table']} WHERE ea_id = " . (int)$row['ea_id']);
            }

            $encounters[] = array(
                'be_id' => (int)$row['be_id'],
                'be_status' => $row['be_status'],
                'be_type' => $row['be_type'],
                'monster_name' => $row['bm_name'] ?? '알 수 없음',
                'monster_image' => $row['bm_image'] ?? '',
                'ea_id' => (int)($row['ea_id'] ?? 0),
                'ea_name' => $ea_data ? ($ea_data['ea_name'] ?? '') : '',
                'ea_map_x' => $ea_data ? $ea_data['ea_map_x'] : null,
                'ea_map_y' => $ea_data ? $ea_data['ea_map_y'] : null,
                'total_hp' => $total_hp,
                'total_max_hp' => $total_max,
                'slot_count' => (int)($slot_count['cnt'] ?? 0),
                'time_remaining' => $time_remaining,
                'discovered_at' => $row['be_discovered_at'],
            );
        }
        echo json_encode(array('success' => true, 'data' => $encounters));
        break;

    // ═══ 폴링 (30초 간격) ═══
    case 'poll':
        $be_id = isset($_GET['be_id']) ? (int)$_GET['be_id'] : 0;
        if (!$be_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
            exit;
        }

        $enc = sql_fetch("SELECT * FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
        if (!$enc) {
            echo json_encode(array('success' => false, 'message' => '전투를 찾을 수 없습니다.'));
            exit;
        }

        // 만료 체크
        if ($enc['be_status'] === 'active' && $enc['be_started_at']) {
            $elapsed = time() - strtotime($enc['be_started_at']);
            if ($elapsed >= (int)$enc['be_time_limit']) {
                sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'failed', be_ended_at = NOW() WHERE be_id = {$be_id} AND be_status = 'active'");
                $enc['be_status'] = 'failed';
            }
        }
        // 미시작 소멸 체크
        if ($enc['be_status'] === 'discovered') {
            $expire_sec = (int)mg_battle_config('battle_expire_no_start', 7200);
            $since = time() - strtotime($enc['be_discovered_at']);
            if ($since >= $expire_sec) {
                sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'expired', be_ended_at = NOW() WHERE be_id = {$be_id} AND be_status = 'discovered'");
                $enc['be_status'] = 'expired';
            }
        }

        // 몬스터 HP
        $monsters = json_decode($enc['be_monsters'] ?? '[]', true);
        $boss_hp = 0; $boss_max = 0;
        if (is_array($monsters)) {
            foreach ($monsters as $m) { $boss_hp += (int)($m['hp'] ?? 0); $boss_max += (int)($m['max_hp'] ?? 1); }
        }

        // 남은 시간
        $time_remaining = 0;
        if ($enc['be_started_at']) {
            $time_remaining = max(0, (int)$enc['be_time_limit'] - (time() - strtotime($enc['be_started_at'])));
        }

        // 참여자
        $slots = array();
        $s_res = sql_query("SELECT bs.ch_id, bs.current_hp, bs.max_hp, bs.total_damage, bs.slot_role
                            FROM {$g5['mg_battle_slot_table']} bs
                            WHERE bs.be_id = {$be_id}
                            ORDER BY bs.total_damage DESC");
        while ($s = sql_fetch_array($s_res)) {
            $ch = sql_fetch("SELECT ch_name, ch_thumb FROM {$g5['mg_character_table']} WHERE ch_id = " . (int)$s['ch_id']);
            $slots[] = array(
                'ch_id' => (int)$s['ch_id'],
                'ch_name' => $ch ? $ch['ch_name'] : '',
                'ch_thumb' => $ch ? $ch['ch_thumb'] : '',
                'hp' => (int)$s['current_hp'],
                'max_hp' => (int)$s['max_hp'],
                'contribution' => (int)$s['total_damage'],
                'role' => $s['slot_role'],
            );
        }

        // 최근 로그 5건
        $logs = array();
        $l_res = sql_query("SELECT * FROM {$g5['mg_battle_log_table']} WHERE be_id = {$be_id} ORDER BY bl_id DESC LIMIT 5");
        while ($l = sql_fetch_array($l_res)) {
            $actor = sql_fetch("SELECT ch_name FROM {$g5['mg_character_table']} WHERE ch_id = " . (int)$l['ch_id']);
            $logs[] = array(
                'time' => date('H:i', strtotime($l['bl_datetime'])),
                'actor' => $actor ? $actor['ch_name'] : '',
                'action' => $l['bl_action'],
                'value' => (int)$l['bl_value'],
                'detail' => $l['bl_detail'] ?? '',
            );
        }

        echo json_encode(array('success' => true, 'data' => array(
            'status' => $enc['be_status'],
            'boss_hp' => $boss_hp,
            'boss_max_hp' => $boss_max,
            'time_remaining' => $time_remaining,
            'slots' => $slots,
            'logs' => $logs,
            'taunt_queue' => json_decode($enc['taunt_queue'] ?? '[]', true),
            'debuffs' => json_decode($enc['be_debuffs'] ?? '[]', true),
        )));
        break;

    // ═══ 전투 참여 ═══
    case 'join':
        $be_id = (int)($_POST['be_id'] ?? 0);
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        if (!$be_id || !$ch_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
            exit;
        }

        // 캐릭터 소유 확인
        $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id}' AND ch_status = 'approved'");
        if (!$ch) {
            echo json_encode(array('success' => false, 'message' => '사용할 수 없는 캐릭터입니다.'));
            exit;
        }

        // 전투 상태 확인
        $enc = sql_fetch("SELECT * FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
        if (!$enc || !in_array($enc['be_status'], array('discovered', 'active'))) {
            echo json_encode(array('success' => false, 'message' => '참여할 수 없는 전투입니다.'));
            exit;
        }

        // 중복 참여 확인
        $exists = sql_fetch("SELECT bsl_id FROM {$g5['mg_battle_slot_table']} WHERE be_id = {$be_id} AND mb_id = '{$mb_id}'");
        if ($exists) {
            echo json_encode(array('success' => false, 'message' => '이미 참여 중입니다.'));
            exit;
        }

        // 스탯 초기화
        mg_battle_init_stat($ch_id, $mb_id);
        mg_battle_init_energy($ch_id, $mb_id);

        // 참여
        $result = mg_battle_join($be_id, $ch_id, $mb_id, 'participant');
        if ($result) {
            echo json_encode(array('success' => true, 'message' => '전투에 참여했습니다.'));
        } else {
            echo json_encode(array('success' => false, 'message' => '참여에 실패했습니다.'));
        }
        break;

    // ═══ 전투 행동 ═══
    case 'battle_action':
        $be_id = (int)($_POST['be_id'] ?? 0);
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        $type  = isset($_POST['type']) ? clean_xss_tags($_POST['type']) : '';
        $target_ch_id = (int)($_POST['target_ch_id'] ?? 0);

        if (!$be_id || !$ch_id || !$type) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
            exit;
        }

        // 전투 상태 확인
        $enc = sql_fetch("SELECT * FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
        if (!$enc || !in_array($enc['be_status'], array('discovered', 'active'))) {
            echo json_encode(array('success' => false, 'message' => '행동할 수 없는 전투입니다.'));
            exit;
        }

        // 내 슬롯 확인
        $my_slot = sql_fetch("SELECT * FROM {$g5['mg_battle_slot_table']} WHERE be_id = {$be_id} AND ch_id = {$ch_id} AND mb_id = '{$mb_id}'");
        if (!$my_slot) {
            echo json_encode(array('success' => false, 'message' => '전투에 참여하지 않았습니다.'));
            exit;
        }
        if ((int)$my_slot['current_hp'] <= 0) {
            echo json_encode(array('success' => false, 'message' => '전사 상태에서는 행동할 수 없습니다.'));
            exit;
        }

        // 기력 확인
        $energy = mg_battle_get_energy($ch_id);
        $en_cost = ($type === 'attack') ? 1 : 2;
        if ($energy['current'] < $en_cost) {
            echo json_encode(array('success' => false, 'message' => '기력이 부족합니다. (필요: ' . $en_cost . ', 보유: ' . $energy['current'] . ')'));
            exit;
        }

        // 첫 행동이면 전투 활성화
        if ($enc['be_status'] === 'discovered') {
            sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'active', be_started_at = NOW() WHERE be_id = {$be_id} AND be_status = 'discovered'");
        }

        // 파생 수치 계산
        $derived = mg_battle_calc_derived($ch_id);

        // 기력 소모
        mg_battle_use_energy($ch_id, $en_cost);

        // 행동 처리
        $result_data = array('damage' => 0, 'heal' => 0, 'message' => '');
        $monsters = json_decode($enc['be_monsters'] ?? '[]', true);
        if (!is_array($monsters)) $monsters = array();

        switch ($type) {
            case 'attack':
                // 일반공격: 물리 데미지
                $base_dmg = max(1, $derived['atk']);
                $variance = mt_rand(90, 110) / 100;
                $damage = max(1, round($base_dmg * $variance));

                // 크리티컬
                $is_crit = (mt_rand(1, 100) <= $derived['crit_rate']);
                if ($is_crit) {
                    $damage = round($damage * $derived['crit_mult'] / 100);
                }

                // 몬스터에 데미지 적용 (첫 번째 생존 몬스터)
                foreach ($monsters as &$m) {
                    if ((int)$m['hp'] > 0) {
                        $m['hp'] = max(0, (int)$m['hp'] - $damage);
                        break;
                    }
                }
                unset($m);

                $result_data['damage'] = -$damage;
                $result_data['is_crit'] = $is_crit;
                $result_data['message'] = '일반공격 ' . ($is_crit ? '(크리티컬!) ' : '') . '-' . $damage;

                // 기여도 증가
                sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_damage = total_damage + {$damage}, bs_action_count = bs_action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);

                // 반격 처리 (도발 큐 기반)
                $taunt_queue = json_decode($enc['taunt_queue'] ?? '[]', true);
                $counter_target_ch = $ch_id; // 기본: 행동자에게 반격

                if (!empty($taunt_queue)) {
                    $counter_target_ch = (int)$taunt_queue[0]['ch_id'];
                    $taunt_queue[0]['remaining'] = (int)$taunt_queue[0]['remaining'] - 1;
                    if ($taunt_queue[0]['remaining'] <= 0) {
                        array_shift($taunt_queue);
                    }
                    sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET taunt_queue = '" . sql_real_escape_string(json_encode($taunt_queue)) . "' WHERE be_id = {$be_id}");
                }

                // 반격 데미지 (몬스터 ATK 기반)
                $mon_template = sql_fetch("SELECT bm_atk FROM {$g5['mg_battle_monster_table']} WHERE bm_id = " . (int)$enc['bm_id']);
                if ($mon_template) {
                    $counter_dmg = max(1, round((int)$mon_template['bm_atk'] * mt_rand(80, 120) / 100));

                    // 도발 대상의 방어력 적용
                    $target_derived = mg_battle_calc_derived($counter_target_ch);
                    $counter_dmg = max(1, $counter_dmg - (int)($target_derived['def'] ?? 0));

                    // 도발 대상 HP 감소
                    sql_query("UPDATE {$g5['mg_battle_slot_table']} SET current_hp = GREATEST(0, current_hp - {$counter_dmg}) WHERE be_id = {$be_id} AND ch_id = {$counter_target_ch}");

                    // 반격 로그
                    $counter_ch_name = sql_fetch("SELECT ch_name FROM {$g5['mg_character_table']} WHERE ch_id = {$counter_target_ch}");
                    sql_query("INSERT INTO {$g5['mg_battle_log_table']} (be_id, mb_id, ch_id, bl_action, bl_target_type, bl_target_id, bl_damage, bl_counter, bl_counter_target_ch, bl_detail, bl_datetime)
                               VALUES ({$be_id}, '', 0, 'counter', 'player', {$counter_target_ch}, {$counter_dmg}, {$counter_dmg}, {$counter_target_ch}, '보스 반격" . ($counter_target_ch !== $ch_id ? ' (도발 흡수)' : '') . "', NOW())");
                }
                break;

            case 'skill':
                // TODO: 스킬 선택 UI → 스킬 ID로 처리
                // 임시: 기본 스킬 공격 (ATK * 1.5)
                $base_dmg = max(1, round($derived['atk'] * 1.5));
                $damage = max(1, round($base_dmg * mt_rand(90, 110) / 100));

                foreach ($monsters as &$m) {
                    if ((int)$m['hp'] > 0) {
                        $m['hp'] = max(0, (int)$m['hp'] - $damage);
                        break;
                    }
                }
                unset($m);

                $result_data['damage'] = -$damage;
                $result_data['message'] = '스킬 공격 -' . $damage;
                sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_damage = total_damage + {$damage}, bs_action_count = bs_action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);
                break;

            case 'item':
                // TODO: 아이템 사용 UI
                $result_data['message'] = '아이템 기능 준비 중';
                break;
        }

        // 몬스터 HP 업데이트
        $monsters_json = sql_real_escape_string(json_encode($monsters, JSON_UNESCAPED_UNICODE));
        sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_monsters = '{$monsters_json}' WHERE be_id = {$be_id}");

        // 행동 로그
        $log_dmg = abs((int)$result_data['damage']);
        $log_heal = (int)($result_data['heal'] ?? 0);
        $log_is_crit = !empty($result_data['is_crit']) ? 1 : 0;
        sql_query("INSERT INTO {$g5['mg_battle_log_table']} (be_id, mb_id, ch_id, bl_action, bl_target_type, bl_target_id, bl_damage, bl_heal, bl_is_crit, bl_detail, bl_datetime)
                   VALUES ({$be_id}, '{$mb_id}', {$ch_id}, '" . sql_real_escape_string($type) . "', 'monster', 0, {$log_dmg}, {$log_heal}, {$log_is_crit}, '" . sql_real_escape_string($result_data['message']) . "', NOW())");

        // 몬스터 전멸 체크
        $all_dead = true;
        foreach ($monsters as $m) {
            if ((int)$m['hp'] > 0) { $all_dead = false; break; }
        }
        if ($all_dead) {
            sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'cleared', be_ended_at = NOW() WHERE be_id = {$be_id}");

            // 보상 분배
            mg_battle_distribute_rewards($be_id);

            $result_data['cleared'] = true;
            $result_data['message'] .= ' — 전투 승리!';
        }

        echo json_encode(array('success' => true, 'data' => $result_data));
        break;

    // ═══ 스탯 일괄 확정 ═══
    case 'save_stats':
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        if (!$ch_id) { echo json_encode(array('success' => false, 'message' => '잘못된 요청')); exit; }

        $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id}'");
        if (!$ch) { echo json_encode(array('success' => false, 'message' => '권한이 없습니다.')); exit; }

        $bs = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
        if ($bs && (int)($bs['stat_locked'] ?? 0)) {
            echo json_encode(array('success' => false, 'message' => '이미 스탯이 확정되었습니다.'));
            exit;
        }

        $_stat_base = (int)mg_config('battle_stat_base', '5');
        $_stat_bonus = (int)mg_config('battle_stat_bonus_points', '15');
        $stat_keys = array('stat_hp', 'stat_str', 'stat_dex', 'stat_int');
        $vals = array();
        $total_used = 0;
        foreach ($stat_keys as $sk) {
            $v = isset($_POST[$sk]) ? (int)$_POST[$sk] : $_stat_base;
            if ($v < $_stat_base) $v = $_stat_base;
            $vals[$sk] = $v;
            $total_used += ($v - $_stat_base);
        }
        if ($total_used > $_stat_bonus) {
            echo json_encode(array('success' => false, 'message' => '포인트를 초과 분배할 수 없습니다.'));
            exit;
        }
        $remaining = $_stat_bonus - $total_used;

        if ($bs && $bs['bs_id']) {
            sql_query("UPDATE {$g5['mg_battle_stat_table']} SET
                stat_hp = {$vals['stat_hp']}, stat_str = {$vals['stat_str']}, stat_dex = {$vals['stat_dex']},
                stat_int = {$vals['stat_int']},
                stat_points = {$remaining}, stat_locked = 1 WHERE ch_id = {$ch_id}");
        } else {
            sql_query("INSERT INTO {$g5['mg_battle_stat_table']}
                (ch_id, mb_id, stat_hp, stat_str, stat_dex, stat_int, stat_points, stat_locked)
                VALUES ({$ch_id}, '{$mb_id}',
                {$vals['stat_hp']}, {$vals['stat_str']}, {$vals['stat_dex']},
                {$vals['stat_int']}, {$remaining}, 1)");
        }
        echo json_encode(array('success' => true, 'message' => '스탯이 확정되었습니다.'));
        break;

    // ═══ 스탯 배분 (레거시) ═══
    case 'allocate_stat':
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        $stat_key = isset($_POST['stat']) ? clean_xss_tags($_POST['stat']) : '';
        $amount = max(1, (int)($_POST['amount'] ?? 1));

        if (!$ch_id || !$stat_key) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
            exit;
        }

        // 소유 확인
        $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id}'");
        if (!$ch) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }

        // 확정된 스탯은 수정 불가
        $_bs_check = sql_fetch("SELECT stat_locked FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
        if ($_bs_check && (int)($_bs_check['stat_locked'] ?? 0)) {
            echo json_encode(array('success' => false, 'message' => '스탯이 확정되어 수정할 수 없습니다.'));
            exit;
        }

        $valid_stats = array('stat_str', 'stat_dex', 'stat_int');
        if (!in_array($stat_key, $valid_stats)) {
            echo json_encode(array('success' => false, 'message' => '유효하지 않은 스탯'));
            exit;
        }

        $short_key = str_replace('stat_', '', $stat_key);
        $alloc = array($short_key => $amount);
        $result = mg_battle_allocate_stats($ch_id, $alloc);
        echo json_encode($result);
        break;

    // ═══ 스탯 초기화 ═══
    case 'reset_stats':
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        if (!$ch_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
            exit;
        }

        $ch = sql_fetch("SELECT ch_id FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$mb_id}'");
        if (!$ch) {
            echo json_encode(array('success' => false, 'message' => '권한이 없습니다.'));
            exit;
        }

        // 확정된 스탯은 초기화 불가
        $_bs_check = sql_fetch("SELECT stat_locked FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
        if ($_bs_check && (int)($_bs_check['stat_locked'] ?? 0)) {
            echo json_encode(array('success' => false, 'message' => '스탯이 확정되어 초기화할 수 없습니다. 초기화 아이템을 사용해주세요.'));
            exit;
        }

        $result = mg_battle_reset_stats($ch_id);
        echo json_encode($result);
        break;

    default:
        echo json_encode(array('success' => false, 'message' => '알 수 없는 액션'));
}
