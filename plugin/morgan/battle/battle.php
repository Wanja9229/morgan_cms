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
    $con = max(0, (int)($alloc['con'] ?? 0));
    $luk = max(0, (int)($alloc['luk'] ?? 0));
    $total = $hp + $str + $dex + $int_val + $con + $luk;

    if ($total <= 0) return array('success' => false, 'message' => '배분할 포인트를 입력해주세요.');
    if ($total > (int)$stat['stat_points']) {
        return array('success' => false, 'message' => '포인트가 부족합니다. (보유: ' . $stat['stat_points'] . ')');
    }

    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET
        stat_hp = stat_hp + {$hp},
        stat_str = stat_str + {$str},
        stat_dex = stat_dex + {$dex},
        stat_int = stat_int + {$int_val},
        stat_con = stat_con + {$con},
        stat_luk = stat_luk + {$luk},
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

    $base = (int)mg_config('battle_stat_base', '5');
    $used = ((int)$stat['stat_hp'] - $base) + ((int)$stat['stat_str'] - $base) + ((int)$stat['stat_dex'] - $base) + ((int)$stat['stat_int'] - $base) + ((int)$stat['stat_con'] - $base) + ((int)$stat['stat_luk'] - $base);
    $refund = max(0, $used) + (int)$stat['stat_points'];

    sql_query("UPDATE {$g5['mg_battle_stat_table']} SET
        stat_hp = {$base}, stat_str = {$base}, stat_dex = {$base}, stat_int = {$base}, stat_con = {$base}, stat_luk = {$base},
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

// ============================================================
// 11. 보상 분배
// ============================================================

/**
 * 전투 승리 시 보상 분배
 * 기본 보상: 균등 + 기여도 상위 3명 추가 보상 + 발견자 보너스
 */
function mg_battle_distribute_rewards($be_id) {
    global $g5;
    $be_id = (int)$be_id;

    $enc = sql_fetch("SELECT * FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
    if (!$enc) return;

    $base_point = (int)$enc['be_reward_point'];
    $death_penalty_pct = (int)mg_battle_config('battle_death_penalty', 50);

    // 참여자 (행동 1회 이상)
    $slots = array();
    $res = sql_query("SELECT * FROM {$g5['mg_battle_slot_table']}
                      WHERE be_id = {$be_id} AND action_count > 0
                      ORDER BY (total_damage + total_heal + buff_count * 50 + debuff_count * 50 + taunt_absorb * 80) DESC");
    while ($s = sql_fetch_array($res)) {
        $s['contribution'] = (int)$s['total_damage'] + (int)$s['total_heal']
                           + (int)$s['buff_count'] * 50 + (int)$s['debuff_count'] * 50
                           + (int)$s['taunt_absorb'] * 80;
        $slots[] = $s;
    }

    if (empty($slots)) return;

    // 보상 계산
    $bonus_rates = array(50, 30, 15); // 1,2,3위
    $discoverer_bonus = 20;

    foreach ($slots as $i => $slot) {
        $reward = $base_point;

        // 상위 3명 추가 보상
        if ($i < 3) {
            $reward += round($base_point * $bonus_rates[$i] / 100);
        }

        // 발견자 보너스
        if ($slot['mb_id'] === $enc['discoverer_mb_id']) {
            $reward += round($base_point * $discoverer_bonus / 100);
        }

        // 전사 패널티
        if ($slot['slot_status'] === 'dead') {
            $reward = round($reward * (100 - $death_penalty_pct) / 100);
        }

        // 포인트 지급
        if ($reward > 0 && function_exists('insert_point')) {
            insert_point($slot['mb_id'], $reward, '전투 보상 (기여 ' . ($i+1) . '위)', 'mg_battle', $be_id, 'reward');
        }

        // 알림
        if (function_exists('mg_notify')) {
            $mon = sql_fetch("SELECT bm_name FROM {$g5['mg_battle_monster_table']} WHERE bm_id = " . (int)$enc['bm_id']);
            mg_notify($slot['mb_id'], 'battle', '전투 승리!',
                      htmlspecialchars($mon['bm_name'] ?? '') . ' 격퇴 — 보상 ' . number_format($reward) . ' 포인트',
                      G5_BBS_URL . '/battle.php?mode=list');
        }
    }

    // 아이템 드랍 (기여율 비례)
    $drops = json_decode($enc['be_reward_drops'] ?? '[]', true);
    if (is_array($drops) && !empty($drops)) {
        $total_contribution = array_sum(array_column($slots, 'contribution'));
        if ($total_contribution <= 0) $total_contribution = 1;

        foreach ($drops as $drop) {
            $drop_rate = (int)($drop['rate'] ?? 0);
            if (mt_rand(1, 100) > $drop_rate) continue;

            // 기여율 비례 대상 선택
            $roll = mt_rand(1, $total_contribution);
            $cumulative = 0;
            foreach ($slots as $slot) {
                $cumulative += $slot['contribution'];
                if ($roll <= $cumulative) {
                    // 아이템 지급 (인벤토리)
                    $si_id = (int)($drop['si_id'] ?? 0);
                    if ($si_id > 0) {
                        $mb_esc = sql_real_escape_string($slot['mb_id']);
                        $existing = sql_fetch("SELECT iv_id, iv_count FROM {$g5['mg_inventory_table']}
                                               WHERE mb_id = '{$mb_esc}' AND si_id = {$si_id}");
                        if ($existing) {
                            sql_query("UPDATE {$g5['mg_inventory_table']} SET iv_count = iv_count + 1 WHERE iv_id = " . (int)$existing['iv_id']);
                        } else {
                            sql_query("INSERT INTO {$g5['mg_inventory_table']} (mb_id, si_id, iv_count, iv_datetime)
                                       VALUES ('{$mb_esc}', {$si_id}, 1, NOW())");
                        }
                    }
                    break;
                }
            }
        }
    }
}

// ============================================================
// 12. 주사위 시스템
// ============================================================

/**
 * 주사위 배율표 조회
 * 기본: 1d20 (20단계)
 * @return array [1 => 0.3, 2 => 0.5, ..., 20 => 1.8]
 */
function mg_battle_dice_multipliers() {
    $default = '0.3,0.5,0.6,0.65,0.7,0.75,0.8,0.85,0.9,0.95,1.0,1.05,1.1,1.15,1.2,1.25,1.3,1.4,1.5,1.8';
    $cfg = mg_config('battle_dice_multipliers', $default);
    $parts = explode(',', $cfg);
    $result = array();
    for ($i = 0; $i < count($parts); $i++) {
        $result[$i + 1] = (float)trim($parts[$i]);
    }
    return $result;
}

/**
 * 주사위 면 수 (배율표 크기로 자동 결정)
 * @return int 6 또는 20 등
 */
function mg_battle_dice_sides() {
    $mults = mg_battle_dice_multipliers();
    return count($mults);
}

/**
 * 1d6 주사위 굴림 (아이템 효과 적용)
 * @param int $ch_id 행동 캐릭터
 * @param int $be_id 인카운터 ID
 * @return array [roll => 1~6, multiplier => float, item_used => string|null]
 */
function mg_battle_roll_dice($ch_id, $be_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $be_id = (int)$be_id;

    // 주사위 사용 여부 확인
    if (mg_config('battle_dice_use', '1') != '1') {
        return array('roll' => 4, 'multiplier' => 1.0, 'item_used' => null);
    }

    $multipliers = mg_battle_dice_multipliers();
    $sides = count($multipliers); // 6 또는 20

    // 슬롯의 주사위 효과 확인
    $slot = sql_fetch("SELECT dice_effects FROM {$g5['mg_battle_slot_table']}
                       WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
    $effects = json_decode($slot['dice_effects'] ?? '[]', true);
    if (!is_array($effects)) $effects = array();

    $item_used = null;
    $roll = mt_rand(1, $sides);

    // 효과 처리 (우선순위: dice_lock > dice_bless)
    foreach ($effects as $idx => $ef) {
        if ($ef['type'] === 'dice_lock' && (int)$ef['uses'] > 0) {
            $roll = (int)($ef['value'] ?? 6);
            $effects[$idx]['uses'] = (int)$ef['uses'] - 1;
            $item_used = 'dice_lock';
            break;
        }
    }

    if (!$item_used) {
        foreach ($effects as $idx => $ef) {
            if ($ef['type'] === 'dice_bless' && (int)$ef['uses'] > 0) {
                $min_val = (int)($ef['min_value'] ?? 3);
                if ($roll < $min_val) $roll = $min_val;
                $effects[$idx]['uses'] = (int)$ef['uses'] - 1;
                $item_used = 'dice_bless';
                break;
            }
        }
    }

    // 사용 완료된 효과 제거
    $effects = array_values(array_filter($effects, function($e) {
        return (int)$e['uses'] > 0;
    }));

    // DB 업데이트
    $effects_json = sql_real_escape_string(json_encode($effects));
    sql_query("UPDATE {$g5['mg_battle_slot_table']}
               SET dice_effects = '{$effects_json}'
               WHERE be_id = {$be_id} AND ch_id = {$ch_id}");

    return array(
        'roll' => $roll,
        'multiplier' => isset($multipliers[$roll]) ? $multipliers[$roll] : 1.0,
        'item_used' => $item_used,
    );
}

/**
 * 주사위 아이템 효과 추가 (전투 중 아이템 사용 시)
 */
function mg_battle_add_dice_effect($ch_id, $be_id, $effect) {
    global $g5;
    $ch_id = (int)$ch_id;
    $be_id = (int)$be_id;

    $slot = sql_fetch("SELECT dice_effects FROM {$g5['mg_battle_slot_table']}
                       WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
    $effects = json_decode($slot['dice_effects'] ?? '[]', true);
    if (!is_array($effects)) $effects = array();

    $effects[] = $effect;

    $effects_json = sql_real_escape_string(json_encode($effects));
    sql_query("UPDATE {$g5['mg_battle_slot_table']}
               SET dice_effects = '{$effects_json}'
               WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
}

// ============================================================
// 13. 버프/디버프 처리
// ============================================================

/**
 * 파생 수치에 활성 버프 보정 적용
 * @param array $derived mg_battle_calc_derived() 결과
 * @param array $buffs_active JSON 디코딩된 버프 배열
 * @return array 보정된 derived
 */
function mg_battle_apply_buffs($derived, $buffs_active) {
    if (!is_array($buffs_active) || empty($buffs_active)) return $derived;

    foreach ($buffs_active as $buff) {
        $stat = $buff['stat'] ?? '';
        $value = (int)($buff['value'] ?? 0);
        if (!$stat || !$value) continue;

        if (isset($derived[$stat])) {
            // 퍼센트 증감
            $derived[$stat] = (int)round($derived[$stat] * (100 + $value) / 100);
        }
    }

    return $derived;
}

/**
 * 행동 후 버프 잔여 횟수 차감
 * @return array 갱신된 buffs 배열
 */
function mg_battle_consume_buff_turn($ch_id, $be_id) {
    global $g5;
    $ch_id = (int)$ch_id;
    $be_id = (int)$be_id;

    $slot = sql_fetch("SELECT buffs_active FROM {$g5['mg_battle_slot_table']}
                       WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
    $buffs = json_decode($slot['buffs_active'] ?? '[]', true);
    if (!is_array($buffs) || empty($buffs)) return array();

    foreach ($buffs as &$b) {
        $b['remaining'] = (int)($b['remaining'] ?? 0) - 1;
    }
    unset($b);

    // 만료된 버프 제거
    $buffs = array_values(array_filter($buffs, function($b) {
        return (int)$b['remaining'] > 0;
    }));

    $buffs_json = sql_real_escape_string(json_encode($buffs));
    sql_query("UPDATE {$g5['mg_battle_slot_table']}
               SET buffs_active = '{$buffs_json}'
               WHERE be_id = {$be_id} AND ch_id = {$ch_id}");

    return $buffs;
}

/**
 * 대상에게 버프 추가
 */
function mg_battle_add_buff($target_ch_id, $be_id, $stat, $value, $turns) {
    global $g5;
    $target_ch_id = (int)$target_ch_id;
    $be_id = (int)$be_id;

    $slot = sql_fetch("SELECT buffs_active FROM {$g5['mg_battle_slot_table']}
                       WHERE be_id = {$be_id} AND ch_id = {$target_ch_id}");
    $buffs = json_decode($slot['buffs_active'] ?? '[]', true);
    if (!is_array($buffs)) $buffs = array();

    // 같은 스탯 버프는 갱신 (중첩 불가)
    $found = false;
    foreach ($buffs as &$b) {
        if (($b['stat'] ?? '') === $stat) {
            $b['value'] = (int)$value;
            $b['remaining'] = (int)$turns;
            $found = true;
            break;
        }
    }
    unset($b);

    if (!$found) {
        $buffs[] = array('stat' => $stat, 'value' => (int)$value, 'remaining' => (int)$turns);
    }

    $buffs_json = sql_real_escape_string(json_encode($buffs));
    sql_query("UPDATE {$g5['mg_battle_slot_table']}
               SET buffs_active = '{$buffs_json}'
               WHERE be_id = {$be_id} AND ch_id = {$target_ch_id}");
}

/**
 * 인카운터 디버프 만료 정리 (시간 기반)
 * @return array 활성 디버프 목록
 */
function mg_battle_clean_debuffs($be_id) {
    global $g5;
    $be_id = (int)$be_id;

    $enc = sql_fetch("SELECT be_debuffs FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
    $debuffs = json_decode($enc['be_debuffs'] ?? '[]', true);
    if (!is_array($debuffs)) return array();

    $now = time();
    $debuffs = array_values(array_filter($debuffs, function($d) use ($now) {
        return isset($d['expires_at']) && strtotime($d['expires_at']) > $now;
    }));

    $json = sql_real_escape_string(json_encode($debuffs));
    sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_debuffs = '{$json}' WHERE be_id = {$be_id}");

    return $debuffs;
}

/**
 * 몬스터에 디버프 적용 (수치 보정 반환)
 * @return array [def_mod => int, atk_mod => int] 퍼센트 감소량
 */
function mg_battle_get_debuff_mods($be_id) {
    $debuffs = mg_battle_clean_debuffs($be_id);
    $mods = array('def' => 0, 'atk' => 0);

    foreach ($debuffs as $d) {
        $stat = $d['stat'] ?? '';
        $value = (int)($d['value'] ?? 0);
        if (isset($mods[$stat])) {
            $mods[$stat] += $value; // 퍼센트 누적
        }
    }

    return $mods;
}

// ============================================================
// 14. 전투 조우 생성 (파견 연동)
// ============================================================

/**
 * 전투 조우 생성 (파견 이벤트에서 호출)
 */
function mg_battle_create_encounter($bm_id, $discoverer_mb_id, $discoverer_ch_id, $ea_id = 0, $el_id = 0) {
    global $g5;
    $bm_id = (int)$bm_id;

    $monster = sql_fetch("SELECT * FROM {$g5['mg_battle_monster_table']} WHERE bm_id = {$bm_id} AND bm_use = 1");
    if (!$monster) return false;

    // 몬스터 인스턴스 생성
    $instances = array();
    $count = ($monster['bm_type'] === 'mob_group') ? max(1, (int)$monster['bm_mob_count']) : 1;
    for ($i = 0; $i < $count; $i++) {
        $instances[] = array(
            'idx' => $i,
            'name' => $monster['bm_name'],
            'hp' => (int)$monster['bm_hp'],
            'max_hp' => (int)$monster['bm_hp'],
            'atk' => (int)$monster['bm_atk'],
            'def' => (int)$monster['bm_def'],
        );
    }

    $monsters_json = sql_real_escape_string(json_encode($instances, JSON_UNESCAPED_UNICODE));
    $drops_json = $monster['bm_reward_drops'] ? sql_real_escape_string($monster['bm_reward_drops']) : '[]';
    $mb_esc = sql_real_escape_string($discoverer_mb_id);
    $ch_id = (int)$discoverer_ch_id;

    sql_query("INSERT INTO {$g5['mg_battle_encounter_table']}
               (bm_id, be_type, be_status, be_monsters, be_time_limit, be_reward_point,
                be_reward_drops, discoverer_mb_id, discoverer_ch_id, ea_id, el_id, be_discovered_at)
               VALUES ({$bm_id}, '{$monster['bm_type']}', 'discovered',
                       '{$monsters_json}', " . (int)$monster['bm_time_limit'] . ", " . (int)$monster['bm_reward_point'] . ",
                       '{$drops_json}', '{$mb_esc}', {$ch_id}, " . (int)$ea_id . ", " . (int)$el_id . ", NOW())");

    $be_id = sql_insert_id();
    if (!$be_id) return false;

    // 발견자 자동 참여
    mg_battle_init_stat($ch_id, $discoverer_mb_id);
    mg_battle_init_energy($ch_id, $discoverer_mb_id);
    mg_battle_join($be_id, $ch_id, $discoverer_mb_id, 'discoverer');

    // 전체 알림
    if (function_exists('mg_notify')) {
        // 로그인 중인 회원에게 알림 (최근 24시간 접속자)
        $res = sql_query("SELECT mb_id FROM {$g5['member_table']}
                          WHERE mb_datetime > DATE_SUB(NOW(), INTERVAL 24 HOUR)
                          AND mb_id != '{$mb_esc}'
                          LIMIT 50");
        while ($row = sql_fetch_array($res)) {
            mg_notify($row['mb_id'], 'battle', '몬스터 출현!',
                      htmlspecialchars($monster['bm_name']) . '이(가) 발견되었습니다!',
                      G5_BBS_URL . '/battle.php?mode=view&be_id=' . $be_id);
        }
    }

    return $be_id;
}
