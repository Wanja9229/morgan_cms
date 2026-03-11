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
        $s_res = sql_query("SELECT bs.*
                            FROM {$g5['mg_battle_slot_table']} bs
                            WHERE bs.be_id = {$be_id}
                            ORDER BY (bs.total_damage + bs.total_heal + bs.buff_count * 50 + bs.debuff_count * 50 + bs.taunt_absorb * 80) DESC");
        while ($s = sql_fetch_array($s_res)) {
            $ch = sql_fetch("SELECT ch_name, ch_thumb FROM {$g5['mg_character_table']} WHERE ch_id = " . (int)$s['ch_id']);
            $contribution = (int)$s['total_damage'] + (int)$s['total_heal']
                          + (int)$s['buff_count'] * 50 + (int)$s['debuff_count'] * 50
                          + (int)$s['taunt_absorb'] * 80;
            // 글로벌 HP 사용
            $ghp = mg_battle_get_global_hp((int)$s['ch_id']);
            $slots[] = array(
                'ch_id' => (int)$s['ch_id'],
                'mb_id' => $s['mb_id'],
                'ch_name' => $ch ? $ch['ch_name'] : '',
                'ch_thumb' => $ch ? $ch['ch_thumb'] : '',
                'hp' => $ghp['current_hp'],
                'max_hp' => $ghp['max_hp'],
                'status' => $ghp['current_hp'] <= 0 ? 'dead' : $s['slot_status'],
                'contribution' => $contribution,
                'role' => $s['slot_role'],
                'buffs' => json_decode($s['buffs_active'] ?? '[]', true),
                'dice_effects' => json_decode($s['dice_effects'] ?? '[]', true),
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
                'damage' => (int)($l['bl_damage'] ?? 0),
                'heal' => (int)($l['bl_heal'] ?? 0),
                'dice' => (int)($l['bl_dice'] ?? 0),
                'is_crit' => (int)($l['bl_is_crit'] ?? 0),
                'detail' => $l['bl_detail'] ?? '',
            );
        }

        // 내 글로벌 HP (내 슬롯에서 ch_id 조회)
        $my_poll_slot = sql_fetch("SELECT ch_id FROM {$g5['mg_battle_slot_table']} WHERE be_id = {$be_id} AND mb_id = '{$mb_id}'");
        $my_ghp = $my_poll_slot ? mg_battle_get_global_hp((int)$my_poll_slot['ch_id']) : array('current_hp' => 0, 'max_hp' => 0);
        // 참여자 = slots (JS 호환)
        echo json_encode(array('success' => true, 'data' => array(
            'status' => $enc['be_status'],
            'boss_hp' => $boss_hp,
            'boss_max_hp' => $boss_max,
            'monsters' => $monsters,
            'time_remaining' => $time_remaining,
            'slots' => $slots,
            'participants' => $slots,
            'my_hp' => $my_ghp['current_hp'],
            'my_max_hp' => $my_ghp['max_hp'],
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

    // ═══ 전투 행동 (전체 스킬 타입 + 주사위) ═══
    case 'battle_action':
        $be_id = (int)($_POST['be_id'] ?? 0);
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        $type  = isset($_POST['type']) ? clean_xss_tags($_POST['type']) : '';
        $sk_id = (int)($_POST['sk_id'] ?? 0);
        $target_idx = (int)($_POST['target_idx'] ?? 0);  // 몬스터 인스턴스 인덱스
        $target_ch_ids_raw = isset($_POST['target_ch_ids']) ? $_POST['target_ch_ids'] : '';

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

        // 시간 초과 체크
        if ($enc['be_status'] === 'active' && $enc['be_started_at']) {
            $elapsed = time() - strtotime($enc['be_started_at']);
            if ($elapsed >= (int)$enc['be_time_limit']) {
                sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'failed', be_ended_at = NOW() WHERE be_id = {$be_id} AND be_status = 'active'");
                echo json_encode(array('success' => false, 'message' => '제한 시간이 초과되었습니다.'));
                exit;
            }
        }

        // 내 슬롯 확인
        $my_slot = sql_fetch("SELECT * FROM {$g5['mg_battle_slot_table']} WHERE be_id = {$be_id} AND ch_id = {$ch_id} AND mb_id = '{$mb_id}'");
        if (!$my_slot) {
            echo json_encode(array('success' => false, 'message' => '전투에 참여하지 않았습니다.'));
            exit;
        }
        // 글로벌 HP 체크
        $my_ghp = mg_battle_get_global_hp($ch_id);
        if ($my_slot['slot_status'] === 'dead' || $my_ghp['current_hp'] <= 0) {
            echo json_encode(array('success' => false, 'message' => '전사 상태에서는 행동할 수 없습니다.'));
            exit;
        }

        // 스킬 정보 로드 (type=skill 시)
        $skill = null;
        $en_cost = 1; // 기본공격 = 1

        if ($type === 'skill') {
            if (!$sk_id) {
                echo json_encode(array('success' => false, 'message' => '스킬을 선택해주세요.'));
                exit;
            }
            $skill = sql_fetch("SELECT * FROM {$g5['mg_battle_skill_table']} WHERE sk_id = {$sk_id} AND sk_use = 1");
            if (!$skill) {
                echo json_encode(array('success' => false, 'message' => '존재하지 않는 스킬입니다.'));
                exit;
            }
            $en_cost = (int)$skill['sk_stamina'];
        }

        // 기력 확인
        $energy = mg_battle_get_energy($ch_id);
        if ($energy['current'] < $en_cost) {
            echo json_encode(array('success' => false, 'message' => '기력이 부족합니다. (필요: ' . $en_cost . ', 보유: ' . $energy['current'] . ')'));
            exit;
        }

        // 첫 행동이면 전투 활성화
        if ($enc['be_status'] === 'discovered') {
            sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'active', be_started_at = NOW() WHERE be_id = {$be_id} AND be_status = 'discovered'");
        }

        // 파생 수치 계산 + 활성 버프 적용
        $derived_raw = mg_battle_calc_derived($ch_id);
        $my_buffs = json_decode($my_slot['buffs_active'] ?? '[]', true);
        $derived = mg_battle_apply_buffs($derived_raw, $my_buffs);

        // 기력 소모
        mg_battle_use_energy($ch_id, $en_cost);

        // 주사위 굴림
        $dice = mg_battle_roll_dice($ch_id, $be_id);

        // 몬스터 데이터
        $monsters = json_decode($enc['be_monsters'] ?? '[]', true);
        if (!is_array($monsters)) $monsters = array();

        // 몬스터 디버프 보정
        $debuff_mods = mg_battle_get_debuff_mods($be_id);

        // 행동 결과
        $result_data = array(
            'damage' => 0, 'heal' => 0, 'message' => '',
            'is_crit' => false, 'dice' => $dice['roll'],
            'dice_multiplier' => $dice['multiplier'],
            'dice_item' => $dice['item_used'],
        );

        $log_action = $type;
        $log_target_type = 'monster';
        $log_target_id = 0;
        $log_dmg = 0;
        $log_heal = 0;
        $do_counter = true; // 반격 발생 여부

        // ─── 일반공격 ───
        if ($type === 'attack') {
            $base_atk = max(1, $derived['atk']);
            $damage = max(1, round($base_atk * $dice['multiplier']));

            // 몬스터 DEF 적용 (디버프 감소 반영)
            $mon_def = 0;
            foreach ($monsters as $m) {
                if ((int)$m['hp'] > 0) {
                    $mon_def = (int)($m['def'] ?? 0);
                    break;
                }
            }
            $effective_def = max(0, round($mon_def * (100 - ($debuff_mods['def'] ?? 0)) / 100));
            $damage = max(1, $damage - $effective_def);

            // 크리티컬
            $is_crit = (mt_rand(1, 100) <= $derived['crit_rate']);
            if ($is_crit) {
                $damage = round($damage * $derived['crit_mult'] / 100);
            }

            // 타겟 몬스터에 적용
            $hit_monster = null;
            foreach ($monsters as &$m) {
                if ((int)$m['hp'] > 0 && (int)$m['idx'] === $target_idx) {
                    $m['hp'] = max(0, (int)$m['hp'] - $damage);
                    $hit_monster = $m;
                    break;
                }
            }
            // 지정 타겟이 없으면 첫 번째 생존 몬스터
            if (!$hit_monster) {
                foreach ($monsters as &$m) {
                    if ((int)$m['hp'] > 0) {
                        $m['hp'] = max(0, (int)$m['hp'] - $damage);
                        $hit_monster = $m;
                        break;
                    }
                }
            }
            unset($m);

            $result_data['damage'] = -$damage;
            $result_data['is_crit'] = $is_crit;
            $result_data['action_type'] = 'attack';
            $result_data['message'] = '일반공격 ' . ($is_crit ? '(크리티컬!) ' : '') . '-' . $damage . ' [🎲' . $dice['roll'] . ']';
            $log_dmg = $damage;
            $log_action = 'attack';

            sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_damage = total_damage + {$damage}, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);

        // ─── 스킬 행동 ───
        } elseif ($type === 'skill' && $skill) {
            $sk_type = $skill['sk_type'];
            $sk_target = $skill['sk_target'];
            $sk_base_stat = $skill['sk_base_stat'];
            $sk_mult = (float)$skill['sk_multiplier'];
            $log_action = $skill['sk_code'];

            // ── 데미지 스킬 ──
            if ($sk_type === 'damage') {
                // 기반 스탯에 따른 공격력 결정
                $base_val = $derived['atk']; // STR 기반
                if ($sk_base_stat === 'dex') $base_val = $derived['satk'];
                elseif ($sk_base_stat === 'int') $base_val = $derived['support'];

                $damage = max(1, round($base_val * $sk_mult * $dice['multiplier']));

                // 크리티컬
                $is_crit = (mt_rand(1, 100) <= $derived['crit_rate']);
                if ($is_crit) {
                    $damage = round($damage * $derived['crit_mult'] / 100);
                }

                // 대상: 적 1체 또는 전체
                if ($sk_target === 'enemy_all') {
                    // 전체공격: 모든 생존 몬스터에 데미지
                    $total_dealt = 0;
                    foreach ($monsters as &$m) {
                        if ((int)$m['hp'] > 0) {
                            $m_def = max(0, round((int)($m['def'] ?? 0) * (100 - ($debuff_mods['def'] ?? 0)) / 100));
                            $actual = max(1, $damage - $m_def);
                            $m['hp'] = max(0, (int)$m['hp'] - $actual);
                            $total_dealt += $actual;
                        }
                    }
                    unset($m);
                    $result_data['damage'] = -$total_dealt;
                    $result_data['is_crit'] = $is_crit;
                    $result_data['action_type'] = 'damage';
                    $result_data['message'] = $skill['sk_name'] . ' (전체) ' . ($is_crit ? '(크리티컬!) ' : '') . '-' . $total_dealt . ' [🎲' . $dice['roll'] . ']';
                    $log_dmg = $total_dealt;
                    sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_damage = total_damage + {$total_dealt}, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);
                } else {
                    // 단일 대상
                    $hit = false;
                    foreach ($monsters as &$m) {
                        if ((int)$m['hp'] > 0 && ((int)$m['idx'] === $target_idx || !$hit)) {
                            $m_def = max(0, round((int)($m['def'] ?? 0) * (100 - ($debuff_mods['def'] ?? 0)) / 100));
                            $damage = max(1, $damage - $m_def);
                            $m['hp'] = max(0, (int)$m['hp'] - $damage);
                            $hit = true;
                            break;
                        }
                    }
                    unset($m);
                    $result_data['damage'] = -$damage;
                    $result_data['is_crit'] = $is_crit;
                    $result_data['action_type'] = 'damage';
                    $result_data['message'] = $skill['sk_name'] . ' ' . ($is_crit ? '(크리티컬!) ' : '') . '-' . $damage . ' [🎲' . $dice['roll'] . ']';
                    $log_dmg = $damage;
                    sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_damage = total_damage + {$damage}, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);
                }

            // ── 회복 스킬 ──
            } elseif ($sk_type === 'heal') {
                $base_heal = max(1, $derived['support']);
                $heal_amount = max(1, round($base_heal * $sk_mult * $dice['multiplier']));
                $do_counter = false; // 힐은 반격 없음
                $log_target_type = 'player';

                // 부활 스킬 특수 처리
                if ($skill['sk_code'] === 'revive') {
                    $revive_hp = max(1, $derived_raw['stat_int'] * 3);
                    $revive_hp = round($revive_hp * $dice['multiplier']);
                    $target_ch_ids = array_map('intval', explode(',', $target_ch_ids_raw));
                    $revived = 0;

                    foreach ($target_ch_ids as $tch) {
                        if ($tch <= 0) continue;
                        $dead_slot = sql_fetch("SELECT bsl_id FROM {$g5['mg_battle_slot_table']}
                                                WHERE be_id = {$be_id} AND ch_id = {$tch} AND slot_status = 'dead'");
                        if ($dead_slot) {
                            // 글로벌 HP 부활
                            $revive_final = mg_battle_revive_global_hp($tch, $revive_hp);
                            sql_query("UPDATE {$g5['mg_battle_slot_table']} SET slot_status = 'active' WHERE bsl_id = " . (int)$dead_slot['bsl_id']);
                            $revived++;
                            break; // 부활은 1명만
                        }
                    }

                    $result_data['heal'] = $revive_hp;
                    $result_data['action_type'] = 'heal';
                    $result_data['message'] = $skill['sk_name'] . ' — 부활! HP ' . $revive_hp . ' [🎲' . $dice['roll'] . ']';
                    $log_heal = $revive_hp;
                    sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_heal = total_heal + {$revive_hp}, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);

                } else {
                    // 일반 힐 / 전체힐
                    $target_ch_ids = array();
                    $total_healed = 0;

                    if ($sk_target === 'ally_all') {
                        // 전체힐: 생존 아군 전체 (글로벌 HP)
                        $allies = sql_query("SELECT ch_id FROM {$g5['mg_battle_slot_table']}
                                             WHERE be_id = {$be_id} AND slot_status = 'active'");
                        while ($ally = sql_fetch_array($allies)) {
                            $actual_heal = mg_battle_heal_global_hp((int)$ally['ch_id'], $heal_amount);
                            $total_healed += $actual_heal;
                        }
                    } else {
                        // 아군 N명 선택 힐 (글로벌 HP)
                        $target_ch_ids = array_map('intval', explode(',', $target_ch_ids_raw));
                        $max_targets = (int)$skill['sk_target_count'];
                        $healed_count = 0;

                        foreach ($target_ch_ids as $tch) {
                            if ($tch <= 0 || $healed_count >= $max_targets) continue;
                            $ally = sql_fetch("SELECT slot_status FROM {$g5['mg_battle_slot_table']}
                                               WHERE be_id = {$be_id} AND ch_id = {$tch} AND slot_status = 'active'");
                            if ($ally) {
                                $actual_heal = mg_battle_heal_global_hp($tch, $heal_amount);
                                $total_healed += $actual_heal;
                                $healed_count++;
                            }
                        }
                    }

                    $result_data['heal'] = $total_healed;
                    $result_data['action_type'] = 'heal';
                    $result_data['message'] = $skill['sk_name'] . ' +' . $total_healed . ' HP [🎲' . $dice['roll'] . ']';
                    $log_heal = $total_healed;
                    sql_query("UPDATE {$g5['mg_battle_slot_table']} SET total_heal = total_heal + {$total_healed}, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);
                }

            // ── 버프 스킬 ──
            } elseif ($sk_type === 'buff') {
                $do_counter = false;
                $log_target_type = 'player';
                $buff_stat = $skill['sk_buff_stat'];
                $buff_value = (int)$skill['sk_buff_value'];
                $buff_turns = (int)$skill['sk_buff_turns'];

                // 주사위 17~20이면 +1턴 보너스 (d20 상위 20%)
                $dice_sides = mg_battle_dice_sides();
                if ($dice['roll'] >= ceil($dice_sides * 0.85)) $buff_turns += 1;

                $target_ch_ids = array_map('intval', explode(',', $target_ch_ids_raw));
                $max_targets = (int)$skill['sk_target_count'];
                $buffed = 0;

                foreach ($target_ch_ids as $tch) {
                    if ($tch <= 0 || $buffed >= $max_targets) continue;
                    $ally = sql_fetch("SELECT slot_status FROM {$g5['mg_battle_slot_table']}
                                       WHERE be_id = {$be_id} AND ch_id = {$tch} AND slot_status = 'active'");
                    if ($ally) {
                        mg_battle_add_buff($tch, $be_id, $buff_stat, $buff_value, $buff_turns);
                        $buffed++;
                    }
                }

                $result_data['action_type'] = 'buff';
                $result_data['buff_stat'] = strtoupper($buff_stat);
                $result_data['buff_value'] = $buff_value;
                $result_data['message'] = $skill['sk_name'] . ' — ' . strtoupper($buff_stat) . ' +' . $buff_value . '% (' . $buff_turns . '턴) [🎲' . $dice['roll'] . ']';
                sql_query("UPDATE {$g5['mg_battle_slot_table']} SET buff_count = buff_count + 1, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);

            // ── 디버프 스킬 ──
            } elseif ($sk_type === 'debuff') {
                $debuff_stat = $skill['sk_buff_stat'];
                $debuff_value = (int)$skill['sk_buff_value'];
                $debuff_duration_min = (int)$skill['sk_buff_turns']; // 분 단위로 사용
                if ($debuff_duration_min <= 0) $debuff_duration_min = 5;

                // 주사위 17~20이면 +1분 보너스
                if ($dice['roll'] >= ceil(mg_battle_dice_sides() * 0.85)) $debuff_duration_min += 1;

                $expires_at = date('Y-m-d H:i:s', time() + ($debuff_duration_min * 60));
                $log_target_type = 'monster';

                // 인카운터에 디버프 추가 (같은 스탯은 갱신)
                $debuffs = json_decode($enc['be_debuffs'] ?? '[]', true);
                if (!is_array($debuffs)) $debuffs = array();

                $found = false;
                foreach ($debuffs as &$d) {
                    if (($d['stat'] ?? '') === $debuff_stat) {
                        $d['value'] = $debuff_value;
                        $d['expires_at'] = $expires_at;
                        $d['caster_ch_id'] = $ch_id;
                        $found = true;
                        break;
                    }
                }
                unset($d);

                if (!$found) {
                    $debuffs[] = array('stat' => $debuff_stat, 'value' => $debuff_value, 'expires_at' => $expires_at, 'caster_ch_id' => $ch_id);
                }

                $debuffs_json = sql_real_escape_string(json_encode($debuffs));
                sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_debuffs = '{$debuffs_json}' WHERE be_id = {$be_id}");

                $result_data['action_type'] = 'debuff';
                $result_data['debuff_stat'] = strtoupper($debuff_stat);
                $result_data['debuff_value'] = $debuff_value;
                $result_data['message'] = $skill['sk_name'] . ' — 적 ' . strtoupper($debuff_stat) . ' -' . $debuff_value . '% (' . $debuff_duration_min . '분) [🎲' . $dice['roll'] . ']';
                sql_query("UPDATE {$g5['mg_battle_slot_table']} SET debuff_count = debuff_count + 1, action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);

            // ── 도발/수호 스킬 ──
            } elseif ($sk_type === 'taunt') {
                $do_counter = false; // 도발 자체는 반격 안 받음
                $log_target_type = 'self';
                $taunt_turns = (int)$skill['sk_buff_turns'];
                if ($taunt_turns <= 0) $taunt_turns = (int)mg_battle_config('taunt_turns', 5);

                // Natural 20이면 +1회 보너스
                if ($dice['roll'] >= mg_battle_dice_sides()) $taunt_turns += 1;

                $is_guard = ((int)$skill['sk_guard_reduction'] > 0);
                $guard_reduction = (int)$skill['sk_guard_reduction'];

                // 도발 큐에 추가
                $taunt_queue = json_decode($enc['taunt_queue'] ?? '[]', true);
                if (!is_array($taunt_queue)) $taunt_queue = array();

                $taunt_queue[] = array(
                    'ch_id' => $ch_id,
                    'remaining' => $taunt_turns,
                    'is_guard' => $is_guard,
                    'guard_reduction' => $guard_reduction,
                );

                $tq_json = sql_real_escape_string(json_encode($taunt_queue));
                sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET taunt_queue = '{$tq_json}' WHERE be_id = {$be_id}");

                $guard_label = $is_guard ? ' (수호: 피해 ' . $guard_reduction . '% 감소)' : '';
                $result_data['action_type'] = 'taunt';
                $result_data['message'] = $skill['sk_name'] . ' — ' . $taunt_turns . '회 반격 흡수' . $guard_label . ' [🎲' . $dice['roll'] . ']';
                sql_query("UPDATE {$g5['mg_battle_slot_table']} SET action_count = action_count + 1 WHERE bsl_id = " . (int)$my_slot['bsl_id']);
            }
        }

        // ─── 반격 처리 (공격 계열 행동 시에만) ───
        $counter_data = null;
        if ($do_counter) {
            $taunt_queue = json_decode($enc['taunt_queue'] ?? '[]', true);
            if (!is_array($taunt_queue)) $taunt_queue = array();

            // 도발 큐 리프레시 (수정된 큐가 있을 수 있으므로)
            if ($type === 'skill' && $skill && $skill['sk_type'] === 'taunt') {
                $taunt_queue = json_decode(sql_fetch("SELECT taunt_queue FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}")['taunt_queue'] ?? '[]', true);
            }

            $counter_target_ch = $ch_id; // 기본: 행동자
            $guard_reduction_pct = 0;

            if (!empty($taunt_queue)) {
                $counter_target_ch = (int)$taunt_queue[0]['ch_id'];
                $guard_reduction_pct = !empty($taunt_queue[0]['is_guard']) ? (int)$taunt_queue[0]['guard_reduction'] : 0;

                // 도발 횟수 차감
                $taunt_queue[0]['remaining'] = (int)$taunt_queue[0]['remaining'] - 1;
                if ($taunt_queue[0]['remaining'] <= 0) {
                    array_shift($taunt_queue);
                }

                $tq_json = sql_real_escape_string(json_encode(array_values($taunt_queue)));
                sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET taunt_queue = '{$tq_json}' WHERE be_id = {$be_id}");

                // 도발 흡수 카운트 증가
                if ($counter_target_ch !== $ch_id) {
                    sql_query("UPDATE {$g5['mg_battle_slot_table']} SET taunt_absorb = taunt_absorb + 1
                               WHERE be_id = {$be_id} AND ch_id = {$counter_target_ch}");
                }
            }

            // 반격 데미지 계산
            $mon_template = sql_fetch("SELECT bm_atk FROM {$g5['mg_battle_monster_table']} WHERE bm_id = " . (int)$enc['bm_id']);
            if ($mon_template) {
                $mon_atk = (int)$mon_template['bm_atk'];
                // 몬스터 ATK 디버프 적용
                $mon_atk = max(1, round($mon_atk * (100 - ($debuff_mods['atk'] ?? 0)) / 100));
                $counter_dmg = max(1, round($mon_atk * mt_rand(80, 120) / 100));

                // 대상 방어력 적용
                $target_derived = mg_battle_calc_derived($counter_target_ch);
                $target_buffs = json_decode(sql_fetch("SELECT buffs_active FROM {$g5['mg_battle_slot_table']} WHERE be_id = {$be_id} AND ch_id = {$counter_target_ch}")['buffs_active'] ?? '[]', true);
                $target_derived = mg_battle_apply_buffs($target_derived, $target_buffs);
                $counter_dmg = max(1, $counter_dmg - (int)($target_derived['def'] ?? 0));

                // 수호 감소율 적용
                if ($guard_reduction_pct > 0) {
                    $counter_dmg = max(1, round($counter_dmg * (100 - $guard_reduction_pct) / 100));
                }

                // 회피 판정
                $evaded = false;
                if ((int)($target_derived['evasion'] ?? 0) > 0 && mt_rand(1, 100) <= (int)$target_derived['evasion']) {
                    $evaded = true;
                    $counter_dmg = 0;
                }

                if (!$evaded) {
                    // 글로벌 HP에 데미지 적용
                    $counter_hp_result = mg_battle_damage_global_hp($counter_target_ch, $counter_dmg);

                    // 전사 체크
                    if ($counter_hp_result['is_dead']) {
                        sql_query("UPDATE {$g5['mg_battle_slot_table']} SET slot_status = 'dead'
                                   WHERE be_id = {$be_id} AND ch_id = {$counter_target_ch}");

                        // 도발 큐에서 전사자 제거
                        $tq_refresh = json_decode(sql_fetch("SELECT taunt_queue FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}")['taunt_queue'] ?? '[]', true);
                        if (is_array($tq_refresh)) {
                            $tq_refresh = array_values(array_filter($tq_refresh, function($t) use ($counter_target_ch) {
                                return (int)$t['ch_id'] !== $counter_target_ch;
                            }));
                            $tq_json = sql_real_escape_string(json_encode($tq_refresh));
                            sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET taunt_queue = '{$tq_json}' WHERE be_id = {$be_id}");
                        }
                    }
                }

                $counter_data = array(
                    'target_ch_id' => $counter_target_ch,
                    'damage' => $counter_dmg,
                    'evaded' => $evaded,
                    'guard_reduction' => $guard_reduction_pct,
                    'is_taunt' => ($counter_target_ch !== $ch_id),
                );

                $result_data['counter'] = $counter_data;

                // 반격 로그
                $counter_detail = $evaded ? '회피!' : ('보스 반격 -' . $counter_dmg . ($counter_target_ch !== $ch_id ? ' (도발 흡수)' : '') . ($guard_reduction_pct > 0 ? ' (수호 ' . $guard_reduction_pct . '% 감소)' : ''));
                sql_query("INSERT INTO {$g5['mg_battle_log_table']} (be_id, mb_id, ch_id, bl_action, bl_target_type, bl_target_id, bl_damage, bl_counter, bl_counter_target_ch, bl_is_evade, bl_detail, bl_datetime)
                           VALUES ({$be_id}, '', 0, 'counter', 'player', {$counter_target_ch}, {$counter_dmg}, {$counter_dmg}, {$counter_target_ch}, " . ($evaded ? 1 : 0) . ", '" . sql_real_escape_string($counter_detail) . "', NOW())");
            }
        }

        // ─── 행동자 버프 턴 소모 ───
        mg_battle_consume_buff_turn($ch_id, $be_id);

        // ─── 몬스터 HP 업데이트 ───
        $monsters_json = sql_real_escape_string(json_encode($monsters, JSON_UNESCAPED_UNICODE));
        sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_monsters = '{$monsters_json}' WHERE be_id = {$be_id}");

        // ─── 행동 로그 ───
        $log_is_crit = !empty($result_data['is_crit']) ? 1 : 0;
        sql_query("INSERT INTO {$g5['mg_battle_log_table']} (be_id, mb_id, ch_id, bl_action, bl_target_type, bl_target_id, bl_damage, bl_heal, bl_is_crit, bl_dice, bl_detail, bl_datetime)
                   VALUES ({$be_id}, '{$mb_id}', {$ch_id}, '" . sql_real_escape_string($log_action) . "', '" . sql_real_escape_string($log_target_type) . "', {$log_target_id}, {$log_dmg}, {$log_heal}, {$log_is_crit}, " . (int)$dice['roll'] . ", '" . sql_real_escape_string($result_data['message']) . "', NOW())");

        // ─── 몬스터 전멸 체크 ───
        $all_dead = true;
        foreach ($monsters as $m) {
            if ((int)$m['hp'] > 0) { $all_dead = false; break; }
        }
        if ($all_dead) {
            sql_query("UPDATE {$g5['mg_battle_encounter_table']} SET be_status = 'cleared', be_ended_at = NOW() WHERE be_id = {$be_id}");
            mg_battle_distribute_rewards($be_id);
            $result_data['cleared'] = true;
            $result_data['message'] .= ' — 전투 승리!';
        }

        echo json_encode(array('success' => true, 'data' => $result_data));
        break;

    // ═══ 아이템 사용 (전투 중) ═══
    case 'use_item':
        $be_id = (int)($_POST['be_id'] ?? 0);
        $ch_id = (int)($_POST['ch_id'] ?? 0);
        $si_id = (int)($_POST['si_id'] ?? 0);

        if (!$be_id || !$ch_id || !$si_id) {
            echo json_encode(array('success' => false, 'message' => '잘못된 요청'));
            exit;
        }

        // 전투/슬롯 확인
        $enc = sql_fetch("SELECT be_status FROM {$g5['mg_battle_encounter_table']} WHERE be_id = {$be_id}");
        if (!$enc || !in_array($enc['be_status'], array('discovered', 'active'))) {
            echo json_encode(array('success' => false, 'message' => '전투가 진행 중이 아닙니다.'));
            exit;
        }
        $my_slot = sql_fetch("SELECT * FROM {$g5['mg_battle_slot_table']} WHERE be_id = {$be_id} AND ch_id = {$ch_id} AND mb_id = '{$mb_id}'");
        if (!$my_slot) {
            echo json_encode(array('success' => false, 'message' => '전투에 참여하지 않았습니다.'));
            exit;
        }

        // 아이템 확인
        $item = sql_fetch("SELECT * FROM {$g5['mg_shop_item_table']} WHERE si_id = {$si_id} AND si_type = 'battle_consumable'");
        if (!$item) {
            echo json_encode(array('success' => false, 'message' => '전투 소모품이 아닙니다.'));
            exit;
        }

        // 인벤토리 보유 확인
        $inv = sql_fetch("SELECT iv_id, iv_count FROM {$g5['mg_inventory_table']}
                          WHERE mb_id = '{$mb_id}' AND si_id = {$si_id} AND iv_count > 0");
        if (!$inv) {
            echo json_encode(array('success' => false, 'message' => '아이템이 부족합니다.'));
            exit;
        }

        $effect = json_decode($item['si_effect'], true);
        if (!is_array($effect)) $effect = array();
        $effect_type = $effect['type'] ?? '';
        $item_msg = '';

        switch ($effect_type) {
            case 'heal':
                // HP 회복 포션 (글로벌 HP)
                $item_ghp = mg_battle_get_global_hp($ch_id);
                if ($item_ghp['current_hp'] <= 0) {
                    echo json_encode(array('success' => false, 'message' => '전사 상태에서는 사용할 수 없습니다.'));
                    exit;
                }
                $heal = (int)($effect['hp_amount'] ?? 50);
                $actual_heal = mg_battle_heal_global_hp($ch_id, $heal);
                $item_msg = 'HP +' . $actual_heal . ' 회복';
                break;

            case 'revive':
                // 자가 부활 (글로벌 HP)
                $item_ghp = mg_battle_get_global_hp($ch_id);
                if ($item_ghp['current_hp'] > 0) {
                    echo json_encode(array('success' => false, 'message' => '전사 상태가 아닙니다.'));
                    exit;
                }
                $hp_pct = (int)($effect['hp_percent'] ?? 50);
                $revive_hp = max(1, round($item_ghp['max_hp'] * $hp_pct / 100));
                $final_hp = mg_battle_revive_global_hp($ch_id, $revive_hp);
                // 슬롯 상태도 active로 복구
                sql_query("UPDATE {$g5['mg_battle_slot_table']} SET slot_status = 'active'
                           WHERE be_id = {$be_id} AND ch_id = {$ch_id}");
                $item_msg = '부활! HP ' . $final_hp;
                break;

            case 'stamina':
                // 기력 충전
                $amount = (int)($effect['amount'] ?? 3);
                mg_battle_charge_energy($ch_id, $amount);
                $item_msg = '기력 +' . $amount . ' 충전';
                break;

            case 'dice_lock':
            case 'dice_reroll':
            case 'dice_bless':
                // 주사위 아이템
                mg_battle_add_dice_effect($ch_id, $be_id, $effect);
                $labels = array('dice_lock' => '주사위 고정권', 'dice_reroll' => '재굴림권', 'dice_bless' => '축복 주사위');
                $item_msg = ($labels[$effect_type] ?? '주사위 아이템') . ' 활성화';
                break;

            default:
                echo json_encode(array('success' => false, 'message' => '알 수 없는 아이템 효과입니다.'));
                exit;
        }

        // 인벤토리 차감
        sql_query("UPDATE {$g5['mg_inventory_table']} SET iv_count = iv_count - 1 WHERE iv_id = " . (int)$inv['iv_id']);

        // 로그
        sql_query("INSERT INTO {$g5['mg_battle_log_table']} (be_id, mb_id, ch_id, bl_action, bl_target_type, bl_target_id, bl_detail, bl_datetime)
                   VALUES ({$be_id}, '{$mb_id}', {$ch_id}, 'item', 'self', 0, '" . sql_real_escape_string($item_msg) . "', NOW())");

        echo json_encode(array('success' => true, 'message' => $item_msg));
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
        // 글로벌 max_hp 동기화
        mg_battle_sync_max_hp($ch_id);
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
