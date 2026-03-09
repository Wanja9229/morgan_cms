<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * Morgan 전투 시스템 — 핵심 헬퍼 함수
 *
 * 기력 계산, HP 회복, 스탯 파생값, 장비 보정, 스킬 조회 등
 */

// ============================================================
// 1. 설정 헬퍼
// ============================================================

/**
 * 전투 설정값 조회 (mg_config 래퍼)
 */
function mg_battle_config($key, $default = '') {
    return mg_config('battle_' . $key, $default);
}

// ============================================================
// 2. 스탯 & 기력 초기화
// ============================================================

/**
 * 캐릭터 전투 스탯 레코드 생성 (없으면)
 * @return array 생성/기존 스탯 row
 */
function mg_battle_init_stat($ch_id, $mb_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $mb_id_esc = sql_real_escape_string($mb_id);

    $row = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    if ($row && $row['bs_id']) return $row;

    $initial_points = (int)mg_battle_config('stat_points', '20');
    sql_query("INSERT IGNORE INTO {$g5['mg_battle_stat_table']}
        (ch_id, mb_id, stat_hp, stat_str, stat_dex, stat_int, stat_points)
        VALUES ({$ch_id}, '{$mb_id_esc}', 0, 0, 0, 0, {$initial_points})");

    return sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
}

/**
 * 캐릭터 기력 레코드 생성 (없으면)
 * @return array 생성/기존 기력 row
 */
function mg_battle_init_energy($ch_id, $mb_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $mb_id_esc = sql_real_escape_string($mb_id);

    $row = sql_fetch("SELECT * FROM {$g5['mg_battle_energy_table']} WHERE ch_id = {$ch_id}");
    if ($row && $row['ben_id']) return $row;

    $initial = (int)mg_battle_config('energy_initial', '5');
    $max = (int)mg_battle_config('energy_max', '10');
    sql_query("INSERT IGNORE INTO {$g5['mg_battle_energy_table']}
        (ch_id, mb_id, current_energy, max_energy)
        VALUES ({$ch_id}, '{$mb_id_esc}', {$initial}, {$max})");

    return sql_fetch("SELECT * FROM {$g5['mg_battle_energy_table']} WHERE ch_id = {$ch_id}");
}

// ============================================================
// 3. 기력 계산
// ============================================================

/**
 * 현재 기력 계산 (시간 기반 충전 반영)
 * DB 저장값 + 경과 시간 충전분을 합산하여 반환
 * @return array [current, max, next_charge_sec, raw_row]
 */
function mg_battle_get_energy($ch_id) {
    global $g5;
    $ch_id = (int)$ch_id;

    $row = sql_fetch("SELECT * FROM {$g5['mg_battle_energy_table']} WHERE ch_id = {$ch_id}");
    if (!$row || !$row['ben_id']) {
        return array('current' => 0, 'max' => 0, 'next_charge_sec' => 0, 'raw' => null);
    }

    $interval = (int)mg_battle_config('energy_interval', '1800');
    $max = (int)$row['max_energy'];
    $saved = (int)$row['current_energy'];
    $last = strtotime($row['last_charge_at']);
    $now = time();
    $elapsed = $now - $last;

    $charged = $interval > 0 ? (int)floor($elapsed / $interval) : 0;
    $current = min($saved + $charged, $max);

    // 다음 충전까지 남은 시간
    $next_charge_sec = 0;
    if ($current < $max && $interval > 0) {
        $next_charge_sec = $interval - ($elapsed % $interval);
    }

    return array(
        'current' => $current,
        'max' => $max,
        'next_charge_sec' => $next_charge_sec,
        'raw' => $row
    );
}

/**
 * 기력 소모 (행동 시)
 * 현재 기력 재계산 → 차감 → DB 업데이트
 * @return bool 성공 여부
 */
function mg_battle_use_energy($ch_id, $amount) {
    global $g5;
    $ch_id = (int)$ch_id;
    $amount = (int)$amount;

    $energy = mg_battle_get_energy($ch_id);
    if (!$energy['raw'] || $energy['current'] < $amount) return false;

    $interval = (int)mg_battle_config('energy_interval', '1800');
    $new_current = $energy['current'] - $amount;
    $now = date('Y-m-d H:i:s');

    // 충전분 반영 후 저장: current를 확정값으로, last_charge_at를 현재 시각으로
    // (충전분이 이미 반영되었으므로 타이머를 리셋하면 안 됨)
    // last_charge_at = 마지막 충전이 일어난 시각으로 조정
    $last = strtotime($energy['raw']['last_charge_at']);
    $elapsed = time() - $last;
    $charged_count = $interval > 0 ? (int)floor($elapsed / $interval) : 0;
    $new_last_charge = date('Y-m-d H:i:s', $last + ($charged_count * $interval));

    sql_query("UPDATE {$g5['mg_battle_energy_table']}
        SET current_energy = {$new_current},
            last_charge_at = '{$new_last_charge}'
        WHERE ch_id = {$ch_id}");

    return true;
}

/**
 * 기력 즉시 충전 (아이템 사용)
 */
function mg_battle_charge_energy($ch_id, $amount) {
    global $g5;
    $ch_id = (int)$ch_id;

    $energy = mg_battle_get_energy($ch_id);
    if (!$energy['raw']) return false;

    $new_current = min($energy['current'] + (int)$amount, $energy['max']);

    // last_charge_at도 현재 충전분 반영 후 갱신
    $interval = (int)mg_battle_config('energy_interval', '1800');
    $last = strtotime($energy['raw']['last_charge_at']);
    $elapsed = time() - $last;
    $charged_count = $interval > 0 ? (int)floor($elapsed / $interval) : 0;
    $new_last_charge = date('Y-m-d H:i:s', $last + ($charged_count * $interval));

    sql_query("UPDATE {$g5['mg_battle_energy_table']}
        SET current_energy = {$new_current},
            last_charge_at = '{$new_last_charge}'
        WHERE ch_id = {$ch_id}");

    return true;
}

// ============================================================
// 4. 장비 보정값
// ============================================================

/**
 * 장착 장비에서 보정값 합산
 * @return array [atk, satk, def, hp, crit_rate, crit_mult, evasion, support_power]
 */
function mg_battle_get_equip_bonuses($ch_id) {
    global $g5;
    $ch_id = (int)$ch_id;

    $stat = sql_fetch("SELECT equip_weapon, equip_armor, equip_accessory
                       FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");

    $bonuses = array(
        'atk' => 0, 'satk' => 0, 'def' => 0, 'hp' => 0,
        'crit_rate' => 0, 'crit_mult' => 0, 'evasion' => 0,
        'support_power' => 0
    );

    if (!$stat) return $bonuses;

    $equip_ids = array_filter(array(
        (int)$stat['equip_weapon'],
        (int)$stat['equip_armor'],
        (int)$stat['equip_accessory']
    ));

    if (empty($equip_ids)) return $bonuses;

    $ids_str = implode(',', $equip_ids);
    $result = sql_query("SELECT si_effect FROM {$g5['mg_shop_item_table']}
                         WHERE si_id IN ({$ids_str})");

    while ($item = sql_fetch_array($result)) {
        $effect = json_decode($item['si_effect'], true);
        if (!is_array($effect)) continue;

        foreach ($bonuses as $key => $val) {
            if (isset($effect[$key])) {
                $bonuses[$key] += (int)$effect[$key];
            }
        }
    }

    return $bonuses;
}

// ============================================================
// 5. 파생 수치 계산
// ============================================================

/**
 * 캐릭터의 전투 파생 수치 전체 계산
 * @return array|null [max_hp, atk, satk, support, def, crit_rate, crit_mult, evasion, stat_*, equip_*]
 */
function mg_battle_calc_derived($ch_id) {
    global $g5;
    $ch_id = (int)$ch_id;

    $stat = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    if (!$stat) return null;

    $equip = mg_battle_get_equip_bonuses($ch_id);
    $base_hp = (int)mg_battle_config('base_hp', '100');
    $base_crit_rate = (int)mg_battle_config('base_crit_rate', '5');
    $base_crit_mult = (int)mg_battle_config('base_crit_mult', '150');

    $hp  = (int)$stat['stat_hp'];
    $str = (int)$stat['stat_str'];
    $dex = (int)$stat['stat_dex'];
    $int_stat = (int)$stat['stat_int'];

    return array(
        // 파생 수치
        'max_hp'     => $base_hp + ($hp * 10) + $equip['hp'],
        'atk'        => ($str * 2) + $equip['atk'],
        'satk'       => ($dex * 2) + $equip['satk'],
        'support'    => $int_stat * 2 + $equip['support_power'],
        'def'        => (int)floor(($hp + $str) / 4) + $equip['def'],
        'crit_rate'  => $base_crit_rate + $equip['crit_rate'],
        'crit_mult'  => $base_crit_mult + $equip['crit_mult'],
        'evasion'    => $equip['evasion'],
        // 원본 스탯
        'stat_hp'    => $hp,
        'stat_str'   => $str,
        'stat_dex'   => $dex,
        'stat_int'   => $int_stat,
        'stat_points'=> (int)$stat['stat_points'],
        // 장비 ID
        'equip_weapon'    => (int)$stat['equip_weapon'],
        'equip_armor'     => (int)$stat['equip_armor'],
        'equip_accessory' => (int)$stat['equip_accessory'],
        // 스킬 슬롯
        'skill_slot_1' => (int)$stat['skill_slot_1'],
        'skill_slot_2' => (int)$stat['skill_slot_2'],
        'skill_slot_3' => (int)$stat['skill_slot_3'],
    );
}

// ============================================================
// 6. HP 자동회복 (lazy 계산)
// ============================================================

/**
 * 행동 시점에 lazy HP 회복 적용
 * 전투 슬롯(be_id + ch_id)의 current_hp를 시간 경과분만큼 회복
 * @return int 회복된 HP량
 */
function mg_battle_apply_hp_regen($ch_id, $be_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $be_id = (int)$be_id;

    $energy_row = sql_fetch("SELECT last_hp_regen_at FROM {$g5['mg_battle_energy_table']} WHERE ch_id = {$ch_id}");
    if (!$energy_row) return 0;

    $slot = sql_fetch("SELECT current_hp, max_hp, slot_status FROM {$g5['mg_battle_slot_table']}
                       WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
    if (!$slot || $slot['slot_status'] === 'dead') return 0;

    $interval = (int)mg_battle_config('energy_interval', '1800');
    $regen_pct = (int)mg_battle_config('hp_regen_pct', '5');
    if ($interval <= 0 || $regen_pct <= 0) return 0;

    $last_regen = strtotime($energy_row['last_hp_regen_at']);
    $now = time();
    $elapsed = $now - $last_regen;
    $charge_count = (int)floor($elapsed / $interval);

    if ($charge_count <= 0) return 0;

    $max_hp = (int)$slot['max_hp'];
    $current_hp = (int)$slot['current_hp'];
    $heal_per_tick = (int)floor($max_hp * $regen_pct / 100);
    $total_heal = $heal_per_tick * $charge_count;
    $new_hp = min($current_hp + $total_heal, $max_hp);
    $actual_heal = $new_hp - $current_hp;

    if ($actual_heal > 0) {
        sql_query("UPDATE {$g5['mg_battle_slot_table']}
            SET current_hp = {$new_hp}
            WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
    }

    // last_hp_regen_at 갱신
    $new_regen_at = date('Y-m-d H:i:s', $last_regen + ($charge_count * $interval));
    sql_query("UPDATE {$g5['mg_battle_energy_table']}
        SET last_hp_regen_at = '{$new_regen_at}'
        WHERE ch_id = {$ch_id}");

    return $actual_heal;
}

// ============================================================
// 7. 스탯 포인트 배분
// ============================================================

/**
 * 스탯 포인트 배분
 * @param array $alloc [hp => N, str => N, dex => N, int => N]
 * @return array [success, message]
 */
function mg_battle_allocate_stats($ch_id, $alloc) {
    global $g5;
    $ch_id = (int)$ch_id;

    $stat = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    if (!$stat) return array('success' => false, 'message' => '스탯 정보가 없습니다.');

    $hp  = max(0, (int)($alloc['hp'] ?? 0));
    $str = max(0, (int)($alloc['str'] ?? 0));
    $dex = max(0, (int)($alloc['dex'] ?? 0));
    $int_val = max(0, (int)($alloc['int'] ?? 0));
    $total = $hp + $str + $dex + $int_val;

    if ($total <= 0) return array('success' => false, 'message' => '배분할 포인트를 입력해주세요.');
    if ($total > (int)$stat['stat_points']) {
        return array('success' => false, 'message' => '포인트가 부족합니다. (보유: ' . $stat['stat_points'] . ')');
    }

    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET
        stat_hp = stat_hp + {$hp},
        stat_str = stat_str + {$str},
        stat_dex = stat_dex + {$dex},
        stat_int = stat_int + {$int_val},
        stat_points = stat_points - {$total}
        WHERE ch_id = {$ch_id}");

    return array('success' => true, 'message' => '스탯이 배분되었습니다.');
}

/**
 * 스탯 초기화 (전부 회수)
 * @return array [success, message, refunded_points]
 */
function mg_battle_reset_stats($ch_id) {
    global $g5;
    $ch_id = (int)$ch_id;

    $stat = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    if (!$stat) return array('success' => false, 'message' => '스탯 정보가 없습니다.');

    $used = (int)$stat['stat_hp'] + (int)$stat['stat_str'] + (int)$stat['stat_dex'] + (int)$stat['stat_int'];
    $refund = $used + (int)$stat['stat_points'];

    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET
        stat_hp = 0, stat_str = 0, stat_dex = 0, stat_int = 0,
        stat_points = {$refund}
        WHERE ch_id = {$ch_id}");

    return array('success' => true, 'message' => '스탯이 초기화되었습니다.', 'refunded_points' => $refund);
}

// ============================================================
// 8. 장비 장착/해제
// ============================================================

/**
 * 장비 장착
 * @param string $slot weapon|armor|accessory
 * @return array [success, message]
 */
function mg_battle_equip($ch_id, $mb_id, $slot, $si_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $si_id = (int)$si_id;
    $mb_id_esc = sql_real_escape_string($mb_id);

    $valid_slots = array('weapon' => 'equip_weapon', 'armor' => 'equip_armor', 'accessory' => 'equip_accessory');
    if (!isset($valid_slots[$slot])) {
        return array('success' => false, 'message' => '잘못된 장비 슬롯입니다.');
    }
    $col = $valid_slots[$slot];

    // 아이템 보유 확인
    $inv = sql_fetch("SELECT iv_id FROM {$g5['mg_inventory_table']}
                      WHERE mb_id = '{$mb_id_esc}' AND si_id = {$si_id} AND iv_count > 0");
    if (!$inv) return array('success' => false, 'message' => '해당 아이템을 보유하고 있지 않습니다.');

    // 아이템 타입 확인
    $slot_type_map = array('weapon' => 'battle_weapon', 'armor' => 'battle_armor', 'accessory' => 'battle_accessory');
    $item = sql_fetch("SELECT si_id, si_type, si_effect FROM {$g5['mg_shop_item_table']} WHERE si_id = {$si_id}");
    if (!$item || $item['si_type'] !== $slot_type_map[$slot]) {
        return array('success' => false, 'message' => '해당 슬롯에 장착할 수 없는 아이템입니다.');
    }

    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET {$col} = {$si_id} WHERE ch_id = {$ch_id}");

    return array('success' => true, 'message' => '장비를 장착했습니다.');
}

/**
 * 장비 해제
 */
function mg_battle_unequip($ch_id, $slot) {
    global $g5;
    $ch_id = (int)$ch_id;

    $valid_slots = array('weapon' => 'equip_weapon', 'armor' => 'equip_armor', 'accessory' => 'equip_accessory');
    if (!isset($valid_slots[$slot])) {
        return array('success' => false, 'message' => '잘못된 장비 슬롯입니다.');
    }
    $col = $valid_slots[$slot];

    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET {$col} = 0 WHERE ch_id = {$ch_id}");

    return array('success' => true, 'message' => '장비를 해제했습니다.');
}

// ============================================================
// 9. 스킬 관련
// ============================================================

/**
 * 스킬 장착
 * @param int $slot_num 1|2|3
 */
function mg_battle_equip_skill($ch_id, $mb_id, $slot_num, $sk_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $sk_id = (int)$sk_id;
    $slot_num = (int)$slot_num;

    if ($slot_num < 1 || $slot_num > 3) {
        return array('success' => false, 'message' => '잘못된 스킬 슬롯입니다.');
    }

    // 스킬 존재 & 활성 확인
    $skill = sql_fetch("SELECT * FROM {$g5['mg_battle_skill_table']} WHERE sk_id = {$sk_id} AND sk_use = 1");
    if (!$skill) return array('success' => false, 'message' => '존재하지 않는 스킬입니다.');

    // 해금 확인 (default는 무조건 사용 가능)
    if ($skill['sk_unlock_type'] !== 'default') {
        if ($skill['sk_unlock_type'] === 'shop') {
            // 스킬북 보유 확인
            $mb_id_esc = sql_real_escape_string($mb_id);
            $book = sql_fetch("SELECT iv_id FROM {$g5['mg_inventory_table']}
                               WHERE mb_id = '{$mb_id_esc}'
                               AND si_id IN (SELECT si_id FROM {$g5['mg_shop_item_table']}
                                             WHERE si_type = 'battle_skill_book'
                                             AND JSON_EXTRACT(si_effect, '$.skill_code') = '{$skill['sk_code']}')
                               AND iv_count > 0");
            if (!$book) return array('success' => false, 'message' => '해당 스킬이 해금되지 않았습니다.');
        }
    }

    // 스탯 요구 조건 확인
    if (!empty($skill['sk_stat_req'])) {
        $parts = explode(':', $skill['sk_stat_req']);
        if (count($parts) === 2) {
            $req_stat = $parts[0];
            $req_val = (int)$parts[1];
            $stat = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
            $stat_col = 'stat_' . $req_stat;
            if ($stat && isset($stat[$stat_col]) && (int)$stat[$stat_col] < $req_val) {
                return array('success' => false, 'message' => strtoupper($req_stat) . ' ' . $req_val . ' 이상 필요합니다.');
            }
        }
    }

    // 다른 슬롯에 같은 스킬이 있으면 제거
    $stat = sql_fetch("SELECT * FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    for ($i = 1; $i <= 3; $i++) {
        if ($i !== $slot_num && (int)$stat['skill_slot_' . $i] === $sk_id) {
            sql_query("UPDATE {$g5['mg_battle_stat_table']} SET skill_slot_{$i} = 0 WHERE ch_id = {$ch_id}");
        }
    }

    $col = 'skill_slot_' . $slot_num;
    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET {$col} = {$sk_id} WHERE ch_id = {$ch_id}");

    return array('success' => true, 'message' => '스킬을 장착했습니다.');
}

/**
 * 캐릭터가 사용 가능한 스킬 목록 조회
 * (기본 스킬 + 해금된 스킬)
 */
function mg_battle_get_available_skills($ch_id, $mb_id) {
    global $g5;
    $mb_id_esc = sql_real_escape_string($mb_id);

    // 기본 스킬
    $skills = array();
    $result = sql_query("SELECT * FROM {$g5['mg_battle_skill_table']} WHERE sk_use = 1 ORDER BY sk_order");
    while ($row = sql_fetch_array($result)) {
        $row['unlocked'] = ($row['sk_unlock_type'] === 'default');

        // 상점 해금 체크
        if (!$row['unlocked'] && $row['sk_unlock_type'] === 'shop') {
            $book = sql_fetch("SELECT iv_id FROM {$g5['mg_inventory_table']}
                               WHERE mb_id = '{$mb_id_esc}'
                               AND si_id IN (SELECT si_id FROM {$g5['mg_shop_item_table']}
                                             WHERE si_type = 'battle_skill_book'
                                             AND JSON_EXTRACT(si_effect, '$.skill_code') = '{$row['sk_code']}')
                               AND iv_count > 0");
            if ($book) $row['unlocked'] = true;
        }

        // 스탯 요구 조건 체크
        $row['stat_met'] = true;
        if (!empty($row['sk_stat_req'])) {
            $parts = explode(':', $row['sk_stat_req']);
            if (count($parts) === 2) {
                $stat = sql_fetch("SELECT stat_{$parts[0]} as val FROM {$g5['mg_battle_stat_table']} WHERE ch_id = " . (int)$ch_id);
                if (!$stat || (int)$stat['val'] < (int)$parts[1]) {
                    $row['stat_met'] = false;
                }
            }
        }

        $skills[] = $row;
    }

    return $skills;
}

/**
 * 캐릭터가 현재 장착한 스킬 3개 조회
 * @return array [slot_1 => skill_row|null, slot_2 => ..., slot_3 => ...]
 */
function mg_battle_get_equipped_skills($ch_id) {
    global $g5;
    $ch_id = (int)$ch_id;

    $stat = sql_fetch("SELECT skill_slot_1, skill_slot_2, skill_slot_3
                       FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    if (!$stat) return array('slot_1' => null, 'slot_2' => null, 'slot_3' => null);

    $result = array();
    for ($i = 1; $i <= 3; $i++) {
        $sk_id = (int)$stat['skill_slot_' . $i];
        if ($sk_id > 0) {
            $result['slot_' . $i] = sql_fetch("SELECT * FROM {$g5['mg_battle_skill_table']} WHERE sk_id = {$sk_id}");
        } else {
            $result['slot_' . $i] = null;
        }
    }

    return $result;
}

// ============================================================
// 10. 유틸
// ============================================================

/**
 * 캐릭터의 전투 프로필 전체 조회 (스탯 + 기력 + 장비 + 스킬)
 * 세팅 페이지 / 전투 참여 시 종합 정보
 */
function mg_battle_get_profile($ch_id, $mb_id) {
    $stat = mg_battle_init_stat((int)$ch_id, $mb_id);
    $energy_row = mg_battle_init_energy((int)$ch_id, $mb_id);
    $derived = mg_battle_calc_derived((int)$ch_id);
    $energy = mg_battle_get_energy((int)$ch_id);
    $equipped_skills = mg_battle_get_equipped_skills((int)$ch_id);

    return array(
        'stat' => $stat,
        'derived' => $derived,
        'energy' => $energy,
        'equipped_skills' => $equipped_skills,
    );
}

/**
 * 전투 참여 시 슬롯 생성
 * @return array [success, message, bsl_id]
 */
function mg_battle_join($be_id, $ch_id, $mb_id, $role = 'participant') {
    global $g5;
    $be_id = (int)$be_id;
    $ch_id = (int)$ch_id;
    $mb_id_esc = sql_real_escape_string($mb_id);

    // 인카운터 상태 확인
    $enc = sql_fetch("SELECT be_status FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
    if (!$enc || !in_array($enc['be_status'], array('discovered', 'active'))) {
        return array('success' => false, 'message' => '참여할 수 없는 전투입니다.');
    }

    // 이미 참여 중인지 (mb_id 기준)
    $exists = sql_fetch("SELECT bsl_id FROM {$g5['mg_battle_slot_table']}
                         WHERE be_id = {$be_id} AND mb_id = '{$mb_id_esc}'");
    if ($exists && $exists['bsl_id']) {
        return array('success' => false, 'message' => '이미 이 전투에 참여 중입니다.');
    }

    // 파생 수치 계산
    $derived = mg_battle_calc_derived($ch_id);
    if (!$derived) return array('success' => false, 'message' => '전투 스탯이 설정되지 않았습니다.');

    $max_hp = $derived['max_hp'];
    $role_esc = sql_real_escape_string($role);

    sql_query("INSERT INTO {$g5['mg_battle_slot_table']}
        (be_id, mb_id, ch_id, slot_role, slot_status, current_hp, max_hp)
        VALUES ({$be_id}, '{$mb_id_esc}', {$ch_id}, '{$role_esc}', 'active', {$max_hp}, {$max_hp})");

    $bsl_id = sql_insert_id();

    return array('success' => true, 'message' => '전투에 참여했습니다.', 'bsl_id' => $bsl_id);
}
