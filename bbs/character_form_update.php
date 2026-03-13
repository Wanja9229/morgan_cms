<?php
/**
 * Morgan Edition - 캐릭터 폼 처리
 */

include_once('./_common.php');

// 로그인 체크
if (!$is_member) {
    alert('로그인이 필요합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// [DEBUG] 업로드 디버깅 — 원인 파악 후 제거
error_log('[MG CharForm DEBUG] FILES=' . json_encode(array_map(function($f) {
    return is_array($f['name']) ? array('names' => $f['name'], 'errors' => $f['error'], 'sizes' => $f['size'])
        : array('name' => $f['name'], 'error' => $f['error'], 'size' => $f['size']);
}, $_FILES ?: [])));
error_log('[MG CharForm DEBUG] POST_keys=' . implode(',', array_keys($_POST ?: [])));
error_log('[MG CharForm DEBUG] CONTENT_LENGTH=' . ($_SERVER['CONTENT_LENGTH'] ?? 'N/A') . ' REQUEST_METHOD=' . ($_SERVER['REQUEST_METHOD'] ?? 'N/A'));

// 입력값
$ch_id = isset($_POST['ch_id']) ? (int)$_POST['ch_id'] : 0;
$ch_name = isset($_POST['ch_name']) ? clean_xss_tags(trim($_POST['ch_name'])) : '';
$side_id = isset($_POST['side_id']) ? (int)$_POST['side_id'] : 0;
$class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
$ch_main = isset($_POST['ch_main']) ? 1 : 0;
if ($is_admin === 'super') {
    $ch_is_npc = isset($_POST['ch_is_npc']) ? 1 : 0;
} else {
    $ch_is_npc = $is_edit ? (int)($char['ch_is_npc'] ?? 0) : 0;
}
$profile_raw = isset($_POST['profile']) ? $_POST['profile'] : array();
$profile = array();
foreach ($profile_raw as $k => $v) {
    if (is_array($v)) {
        $profile[$k] = array_map('stripslashes', $v);
    } else {
        $profile[$k] = stripslashes($v);
    }
}

$btn_save = isset($_POST['btn_save']);
$btn_submit = isset($_POST['btn_submit']);
$btn_delete = isset($_POST['btn_delete']);

$is_edit = $ch_id > 0;

// 기본 검증
if (empty($ch_name)) {
    alert('캐릭터명을 입력해주세요.');
}

if (mb_strlen($ch_name) > 100) {
    alert('캐릭터명은 100자를 초과할 수 없습니다.');
}

// 수정 모드: 권한 체크
if ($is_edit) {
    $char = sql_fetch("SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$member['mb_id']}'");
    if (!$char['ch_id']) {
        alert('존재하지 않거나 권한이 없는 캐릭터입니다.');
    }
}

$_is_approved = $is_edit && ($char['ch_state'] ?? '') === 'approved';

// 신청 기간 중인지 확인 (승인 캐릭터도 수정 가능)
$_is_edit_period = false;
if ($_is_approved) {
    $_char_reg_start = mg_config('char_reg_start', '');
    $_char_reg_end = mg_config('char_reg_end', '');
    $_char_reg_stop = mg_config('char_reg_stop', '0');
    if ($_char_reg_start && $_char_reg_end && $_char_reg_stop !== '1') {
        $now = date('Y-m-d\TH:i');
        $_is_edit_period = ($now >= $_char_reg_start && $now <= $_char_reg_end);
    }
}

// 실제 잠금 여부
$_is_locked = $_is_approved && !$_is_edit_period;

// 승인된 캐릭터: 기본정보 변경 차단 (칭호/프로필꾸미기/대표캐릭터만 허용)
if ($_is_locked) {
    $ch_name = $char['ch_name'];
    $side_id = (int)($char['side_id'] ?? 0);
    $class_id = (int)($char['class_id'] ?? 0);
    $profile_raw = array(); // 프로필 필드 변경 차단
    $profile = array();
}

// 삭제 처리
if ($btn_delete && $is_edit) {
    // 두상 이미지 삭제
    if ($char['ch_thumb']) {
        $thumb_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_thumb'];
        if (file_exists($thumb_path)) {
            @unlink($thumb_path);
        }
        // 썸네일도 삭제
        $dir = dirname($char['ch_thumb']);
        $base = basename($char['ch_thumb']);
        $th_path = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
        if (file_exists($th_path)) {
            @unlink($th_path);
        }
    }

    // 전신 이미지 삭제
    if ($char['ch_image'] ?? '') {
        $img_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_image'];
        if (file_exists($img_path)) {
            @unlink($img_path);
        }
    }

    // 헤더 이미지 삭제
    if ($char['ch_header'] ?? '') {
        $hdr_path = MG_CHAR_IMAGE_PATH.'/'.$char['ch_header'];
        if (file_exists($hdr_path)) {
            @unlink($hdr_path);
        }
    }

    // 소프트 삭제
    sql_query("UPDATE {$g5['mg_character_table']} SET ch_state = 'deleted', ch_update = NOW() WHERE ch_id = {$ch_id}");

    // 프로필 값 삭제
    sql_query("DELETE FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id}");

    // 로그 기록
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action, log_memo) VALUES ({$ch_id}, 'edit', '캐릭터 삭제')");

    goto_url(G5_BBS_URL.'/character.php');
}

// 승인된 캐릭터: 이미지 변경 차단 (수정 기간이 아닌 경우만)
if ($_is_locked) {
    $ch_thumb = $char['ch_thumb'] ?? '';
    $ch_image = $char['ch_image'] ?? '';
    $ch_header = $char['ch_header'] ?? '';
    $ch_profile_bg_image = $char['ch_profile_bg_image'] ?? '';
    goto skip_image_processing;
}

// 두상 이미지 처리
$ch_thumb = $is_edit ? ($char['ch_thumb'] ?? '') : '';

// 두상 이미지 삭제 요청
if (isset($_POST['del_thumb']) && $is_edit && $ch_thumb) {
    $thumb_path = MG_CHAR_IMAGE_PATH.'/'.$ch_thumb;
    if (file_exists($thumb_path)) {
        @unlink($thumb_path);
    }
    $dir = dirname($ch_thumb);
    $base = basename($ch_thumb);
    $th_path = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
    if (file_exists($th_path)) {
        @unlink($th_path);
    }
    $ch_thumb = '';
}

// 새 두상 이미지 업로드
if (isset($_FILES['ch_thumb']) && $_FILES['ch_thumb']['error'] == UPLOAD_ERR_OK) {
    // 기존 이미지 삭제
    if ($ch_thumb) {
        $old_path = MG_CHAR_IMAGE_PATH.'/'.$ch_thumb;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
        $dir = dirname($ch_thumb);
        $base = basename($ch_thumb);
        $old_th = MG_CHAR_IMAGE_PATH.'/'.$dir.'/th_'.$base;
        if (file_exists($old_th)) {
            @unlink($old_th);
        }
    }

    $upload = mg_upload_character_image($_FILES['ch_thumb'], $member['mb_id'], 'thumb');
    if ($upload['success']) {
        $ch_thumb = $upload['filename'];
    } elseif (!empty($upload['error'])) {
        error_log('[MG CharForm] thumb upload failed: ' . $upload['error']);
    }
}

// 전신 이미지 처리
$ch_image = $is_edit ? ($char['ch_image'] ?? '') : '';

// 전신 이미지 삭제 요청
if (isset($_POST['del_image']) && $is_edit && $ch_image) {
    $img_path = MG_CHAR_IMAGE_PATH.'/'.$ch_image;
    if (file_exists($img_path)) {
        @unlink($img_path);
    }
    $ch_image = '';
}

// 새 전신 이미지 업로드
if (isset($_FILES['ch_image']) && $_FILES['ch_image']['error'] == UPLOAD_ERR_OK) {
    if ($ch_image) {
        $old_path = MG_CHAR_IMAGE_PATH.'/'.$ch_image;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }

    $upload = mg_upload_character_image($_FILES['ch_image'], $member['mb_id'], 'image');
    if ($upload['success']) {
        $ch_image = $upload['filename'];
    } elseif (!empty($upload['error'])) {
        error_log('[MG CharForm] image upload failed: ' . $upload['error']);
    }
}

// 헤더/배너 이미지 처리
$ch_header = $is_edit ? ($char['ch_header'] ?? '') : '';

// 헤더 이미지 삭제 요청
if (isset($_POST['del_header']) && $is_edit && $ch_header) {
    $hdr_path = MG_CHAR_IMAGE_PATH.'/'.$ch_header;
    if (file_exists($hdr_path)) {
        @unlink($hdr_path);
    }
    $ch_header = '';
}

// 새 헤더 이미지 업로드
if (isset($_FILES['ch_header']) && $_FILES['ch_header']['error'] == UPLOAD_ERR_OK) {
    if ($ch_header) {
        $old_path = MG_CHAR_IMAGE_PATH.'/'.$ch_header;
        if (file_exists($old_path)) {
            @unlink($old_path);
        }
    }

    $upload = mg_upload_character_image($_FILES['ch_header'], $member['mb_id'], 'header');
    if ($upload['success']) {
        $ch_header = $upload['filename'];
    } elseif (!empty($upload['error'])) {
        error_log('[MG CharForm] header upload failed: ' . $upload['error']);
    }
}

// 배경 이미지 처리 (커스텀 권한 보유 시만)
$ch_profile_bg_image = $is_edit ? ($char['ch_profile_bg_image'] ?? '') : '';

if (mg_has_bg_custom_perm($member['mb_id'])) {
    // 삭제 요청
    if (isset($_POST['del_bg_image']) && $is_edit && $ch_profile_bg_image) {
        $bg_path = MG_CHAR_IMAGE_PATH.'/'.$ch_profile_bg_image;
        if (file_exists($bg_path)) {
            @unlink($bg_path);
        }
        $ch_profile_bg_image = '';
    }

    // 새 배경 이미지 업로드
    if (isset($_FILES['ch_profile_bg_image']) && $_FILES['ch_profile_bg_image']['error'] == UPLOAD_ERR_OK) {
        if ($ch_profile_bg_image) {
            $old_path = MG_CHAR_IMAGE_PATH.'/'.$ch_profile_bg_image;
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $upload = mg_upload_character_image($_FILES['ch_profile_bg_image'], $member['mb_id'], 'bg');
        if ($upload['success']) {
            $ch_profile_bg_image = $upload['filename'];
        } elseif (!empty($upload['error'])) {
            error_log('[MG CharForm] bg upload failed: ' . $upload['error']);
        }
    }
}

skip_image_processing:

// 프로필 스킨/배경 선택 처리 (보유 여부 검증)
$ch_profile_skin = isset($_POST['ch_profile_skin']) ? trim($_POST['ch_profile_skin']) : ($is_edit ? ($char['ch_profile_skin'] ?? '') : '');
$ch_profile_bg = isset($_POST['ch_profile_bg']) ? trim($_POST['ch_profile_bg']) : ($is_edit ? ($char['ch_profile_bg'] ?? '') : '');
$ch_profile_bg_color = isset($_POST['ch_profile_bg_color']) ? trim($_POST['ch_profile_bg_color']) : ($is_edit ? ($char['ch_profile_bg_color'] ?? '#f59f0a') : '#f59f0a');
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $ch_profile_bg_color)) $ch_profile_bg_color = '#f59f0a';

if ($ch_profile_skin && $ch_profile_skin !== 'default') {
    $valid_skins = mg_get_profile_skin_list();
    if (!isset($valid_skins[$ch_profile_skin])) {
        $ch_profile_skin = '';
    } else {
        // 보유 여부 확인
        $own_check = sql_fetch("SELECT iv.iv_id FROM {$g5['mg_inventory_table']} iv
            JOIN {$g5['mg_shop_item_table']} si ON iv.si_id = si.si_id
            WHERE iv.mb_id = '{$member['mb_id']}' AND iv.iv_count > 0
            AND si.si_type = 'profile_skin' AND si.si_effect LIKE '%\"{$ch_profile_skin}\"%'");
        if (!$own_check['iv_id']) $ch_profile_skin = '';
    }
}
if ($ch_profile_bg && $ch_profile_bg !== 'none') {
    $valid_bgs = mg_get_profile_bg_list();
    if (!isset($valid_bgs[$ch_profile_bg])) {
        $ch_profile_bg = '';
    } else {
        // 보유 여부 확인
        $own_check = sql_fetch("SELECT iv.iv_id FROM {$g5['mg_inventory_table']} iv
            JOIN {$g5['mg_shop_item_table']} si ON iv.si_id = si.si_id
            WHERE iv.mb_id = '{$member['mb_id']}' AND iv.iv_count > 0
            AND si.si_type = 'profile_effect' AND si.si_effect LIKE '%\"{$ch_profile_bg}\"%'");
        if (!$own_check['iv_id']) $ch_profile_bg = '';
    }
}

// 대표 캐릭터 처리 (하나만 허용)
if ($ch_main) {
    sql_query("UPDATE {$g5['mg_character_table']} SET ch_main = 0 WHERE mb_id = '{$member['mb_id']}'");
}

// 상태 결정
if ($btn_submit) {
    $ch_state = 'pending';
} elseif ($is_edit && $char['ch_state'] == 'approved') {
    $ch_state = 'approved'; // 승인된 캐릭터는 수정해도 승인 유지
} else {
    $ch_state = 'editing';
}

if ($is_edit) {
    // 수정
    $sql = "UPDATE {$g5['mg_character_table']} SET
            ch_name = '{$ch_name}',
            side_id = ".($side_id ?: 'NULL').",
            class_id = ".($class_id ?: 'NULL').",
            ch_main = {$ch_main},
            ch_is_npc = {$ch_is_npc},
            ch_thumb = '".sql_real_escape_string($ch_thumb)."',
            ch_image = '".sql_real_escape_string($ch_image)."',
            ch_header = '".sql_real_escape_string($ch_header)."',
            ch_profile_skin = '{$ch_profile_skin}',
            ch_profile_bg = '{$ch_profile_bg}',
            ch_profile_bg_color = '{$ch_profile_bg_color}',
            ch_profile_bg_image = '".sql_real_escape_string($ch_profile_bg_image)."',
            ch_state = '{$ch_state}',
            ch_update = NOW()
            WHERE ch_id = {$ch_id}";
    sql_query($sql);

    // 로그
    $log_action = $btn_submit ? 'submit' : 'edit';
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action) VALUES ({$ch_id}, '{$log_action}')");

    // 수정 기간 중 승인 캐릭터 수정 이력 기록
    if ($_is_approved && $_is_edit_period) {
        $edit_fields = array(
            'ch_name' => array('old' => $char['ch_name'], 'new' => $ch_name),
            'side_id' => array('old' => (string)($char['side_id'] ?? ''), 'new' => (string)$side_id),
            'class_id' => array('old' => (string)($char['class_id'] ?? ''), 'new' => (string)$class_id),
            'ch_thumb' => array('old' => $char['ch_thumb'] ?? '', 'new' => $ch_thumb),
            'ch_image' => array('old' => $char['ch_image'] ?? '', 'new' => $ch_image),
            'ch_header' => array('old' => $char['ch_header'] ?? '', 'new' => $ch_header),
        );
        foreach ($edit_fields as $field_key => $f) {
            if ((string)$f['old'] !== (string)$f['new']) {
                $old_esc = sql_real_escape_string($f['old'] ?? '');
                $new_esc = sql_real_escape_string($f['new'] ?? '');
                sql_query("INSERT INTO {$g5['mg_character_edit_log_table']}
                    (ch_id, mb_id, cel_field, cel_old_value, cel_new_value)
                    VALUES ({$ch_id}, '{$member['mb_id']}', '{$field_key}', '{$old_esc}', '{$new_esc}')");
            }
        }
    }
} else {
    // 신규 생성: 슬롯 제한 검증
    $cnt_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']}
                          WHERE mb_id = '{$member['mb_id']}' AND ch_state != 'deleted'");
    $max_chars = mg_get_max_characters($member['mb_id']);
    if ((int)$cnt_row['cnt'] >= $max_chars) {
        alert("최대 캐릭터 수({$max_chars}개)에 도달하여 더 이상 생성할 수 없습니다.");
    }

    $sql = "INSERT INTO {$g5['mg_character_table']} SET
            mb_id = '{$member['mb_id']}',
            ch_name = '{$ch_name}',
            side_id = ".($side_id ?: 'NULL').",
            class_id = ".($class_id ?: 'NULL').",
            ch_main = {$ch_main},
            ch_is_npc = {$ch_is_npc},
            ch_thumb = '".sql_real_escape_string($ch_thumb)."',
            ch_image = '".sql_real_escape_string($ch_image)."',
            ch_header = '".sql_real_escape_string($ch_header)."',
            ch_profile_skin = '{$ch_profile_skin}',
            ch_profile_bg = '{$ch_profile_bg}',
            ch_profile_bg_color = '{$ch_profile_bg_color}',
            ch_profile_bg_image = '".sql_real_escape_string($ch_profile_bg_image)."',
            ch_state = '{$ch_state}',
            ch_datetime = NOW()";
    sql_query($sql);
    $ch_id = sql_insert_id();

    // 로그
    $log_action = $btn_submit ? 'submit' : 'edit';
    sql_query("INSERT INTO {$g5['mg_character_log_table']} (ch_id, log_action) VALUES ({$ch_id}, '{$log_action}')");
}

// 프로필 이미지 필드 처리
$del_profile_image = isset($_POST['del_profile_image']) ? $_POST['del_profile_image'] : array();
if (isset($_FILES['profile_image']) && is_array($_FILES['profile_image']['name'])) {
    foreach ($_FILES['profile_image']['name'] as $pf_id => $name) {
        $pf_id = (int)$pf_id;
        if (!$pf_id) continue;

        // 삭제 요청 처리
        if (isset($del_profile_image[$pf_id])) {
            $old_val = sql_fetch("SELECT pv_value FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id} AND pf_id = {$pf_id}");
            if ($old_val['pv_value']) {
                $old_path = MG_CHAR_IMAGE_PATH.'/'.$old_val['pv_value'];
                if (file_exists($old_path)) @unlink($old_path);
            }
            // profile[] 에도 빈값 세팅
            $profile[$pf_id] = '';
        }

        // 새 이미지 업로드
        if ($_FILES['profile_image']['error'][$pf_id] == UPLOAD_ERR_OK) {
            // 기존 이미지 삭제
            $old_val = sql_fetch("SELECT pv_value FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id} AND pf_id = {$pf_id}");
            if ($old_val['pv_value']) {
                $old_path = MG_CHAR_IMAGE_PATH.'/'.$old_val['pv_value'];
                if (file_exists($old_path)) @unlink($old_path);
            }

            $file_arr = array(
                'name' => $_FILES['profile_image']['name'][$pf_id],
                'type' => $_FILES['profile_image']['type'][$pf_id],
                'tmp_name' => $_FILES['profile_image']['tmp_name'][$pf_id],
                'error' => $_FILES['profile_image']['error'][$pf_id],
                'size' => $_FILES['profile_image']['size'][$pf_id],
            );
            $upload = mg_upload_character_image($file_arr, $member['mb_id'], 'profile');
            if ($upload['success']) {
                $profile[$pf_id] = $upload['filename'];
            } elseif (!empty($upload['error'])) {
                error_log('[MG CharForm] profile_image[' . $pf_id . '] upload failed: ' . $upload['error']);
            }
        }
    }
} else {
    // 파일 업로드 없이 삭제만 요청한 경우
    foreach ($del_profile_image as $pf_id => $v) {
        $pf_id = (int)$pf_id;
        if (!$pf_id) continue;
        $old_val = sql_fetch("SELECT pv_value FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id} AND pf_id = {$pf_id}");
        if ($old_val['pv_value']) {
            $old_path = MG_CHAR_IMAGE_PATH.'/'.$old_val['pv_value'];
            if (file_exists($old_path)) @unlink($old_path);
        }
        $profile[$pf_id] = '';
    }
}

// 프로필 값 저장
// 수정 기간 중이면 이전 프로필 값을 기록용으로 보존
$_prev_profile_values = array();
if ($_is_approved && $_is_edit_period && $is_edit) {
    $pv_result = sql_query("SELECT pf_id, pv_value FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id}");
    if ($pv_result) {
        while ($pv_row = sql_fetch_array($pv_result)) {
            $_prev_profile_values[(int)$pv_row['pf_id']] = $pv_row['pv_value'] ?? '';
        }
    }
}

foreach ($profile as $pf_id => $pv_value) {
    $pf_id = (int)$pf_id;

    // 다중선택 처리
    if (is_array($pv_value)) {
        $pv_value = json_encode($pv_value, JSON_UNESCAPED_UNICODE);
    } else {
        $pv_value = trim($pv_value);
    }

    // 필드 존재 여부 확인
    $field = sql_fetch("SELECT pf_id, pf_name FROM {$g5['mg_profile_field_table']} WHERE pf_id = {$pf_id} AND pf_use = 1");
    if (!$field['pf_id']) continue;

    // 수정 기간 중 프로필 필드 변경 이력 기록
    if ($_is_approved && $_is_edit_period) {
        $prev_val = $_prev_profile_values[$pf_id] ?? '';
        if ((string)$prev_val !== (string)$pv_value) {
            $log_field_name = 'profile_' . $pf_id;
            $old_esc = sql_real_escape_string($prev_val);
            $new_esc = sql_real_escape_string($pv_value);
            sql_query("INSERT INTO {$g5['mg_character_edit_log_table']}
                (ch_id, mb_id, cel_field, cel_old_value, cel_new_value)
                VALUES ({$ch_id}, '{$member['mb_id']}', '{$log_field_name}', '{$old_esc}', '{$new_esc}')");
        }
    }

    // UPSERT
    $exists = sql_fetch("SELECT pv_id FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id} AND pf_id = {$pf_id}");

    if (isset($exists['pv_id']) && $exists['pv_id']) {
        sql_query("UPDATE {$g5['mg_profile_value_table']} SET pv_value = '".sql_real_escape_string($pv_value)."' WHERE pv_id = {$exists['pv_id']}");
    } else {
        sql_query("INSERT INTO {$g5['mg_profile_value_table']} (ch_id, pf_id, pv_value) VALUES ({$ch_id}, {$pf_id}, '".sql_real_escape_string($pv_value)."')");
    }
}

// 전투 스탯 저장
$_battle_use = function_exists('mg_config') ? mg_config('battle_use', '1') : '0';
if ($_battle_use == '1' && isset($_POST['battle_stat']) && is_array($_POST['battle_stat'])) {
    $_stat_base = (int)mg_config('battle_stat_base', '5');
    $_stat_bonus = (int)mg_config('battle_stat_bonus_points', '15');

    // NPC 여부 확인
    $_is_npc_char = (int)($ch_is_npc ?? 0);

    // 이미 확정된 스탯은 수정 불가 (NPC는 예외)
    $existing_stat = sql_fetch("SELECT bs_id, stat_locked FROM {$g5['mg_battle_stat_table']} WHERE ch_id = {$ch_id}");
    if ($_is_npc_char || !$existing_stat || !(int)($existing_stat['stat_locked'] ?? 0)) {
        $stat_keys = array('stat_hp', 'stat_str', 'stat_dex', 'stat_int');
        $stat_vals = array();
        $total_used = 0;

        foreach ($stat_keys as $sk) {
            $v = isset($_POST['battle_stat'][$sk]) ? (int)$_POST['battle_stat'][$sk] : $_stat_base;
            if (!$_is_npc_char && $v < $_stat_base) $v = $_stat_base;
            if ($v < 0) $v = 0;
            $stat_vals[$sk] = $v;
            $total_used += max(0, $v - $_stat_base);
        }

        // 분배 초과 방지 (NPC는 제한 없음)
        if (!$_is_npc_char && $total_used > $_stat_bonus) {
            $over = $total_used - $_stat_bonus;
            foreach (array_reverse($stat_keys) as $sk) {
                $excess = $stat_vals[$sk] - $_stat_base;
                if ($excess > 0 && $over > 0) {
                    $cut = min($excess, $over);
                    $stat_vals[$sk] -= $cut;
                    $over -= $cut;
                }
            }
            $total_used = $_stat_bonus;
        }

        $remaining = $_is_npc_char ? 0 : max(0, $_stat_bonus - $total_used);

        // 스탯 확정 (locked = 1, NPC도 locked로 저장)
        if ($existing_stat && $existing_stat['bs_id']) {
            sql_query("UPDATE {$g5['mg_battle_stat_table']} SET
                stat_hp = {$stat_vals['stat_hp']},
                stat_str = {$stat_vals['stat_str']},
                stat_dex = {$stat_vals['stat_dex']},
                stat_int = {$stat_vals['stat_int']},
                stat_points = {$remaining},
                stat_locked = 1
                WHERE ch_id = {$ch_id}");
        } else {
            sql_query("INSERT INTO {$g5['mg_battle_stat_table']}
                (ch_id, mb_id, stat_hp, stat_str, stat_dex, stat_int, stat_points, stat_locked)
                VALUES ({$ch_id}, '{$member['mb_id']}',
                {$stat_vals['stat_hp']}, {$stat_vals['stat_str']}, {$stat_vals['stat_dex']},
                {$stat_vals['stat_int']}, {$remaining}, 1)");

            // 기력/HP 레코드도 생성 (없으면)
            if (function_exists('mg_battle_init_energy')) {
                mg_battle_init_energy($ch_id, $member['mb_id']);
            }
        }
    }
}

// 완료 메시지
$msg = $is_edit ? '캐릭터가 수정되었습니다.' : '캐릭터가 생성되었습니다.';
if ($btn_submit) {
    $msg .= ' 승인 심사 후 활동이 가능합니다.';
}

alert($msg, G5_BBS_URL.'/character.php');
