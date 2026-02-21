<?php
/**
 * Morgan Edition - 상품 저장 처리
 */

$sub_menu = "800700";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
$si_id = isset($_POST['si_id']) ? (int)$_POST['si_id'] : 0;

// 상품 등록/수정
if ($mode == 'add' || $mode == 'edit') {
    $si_name = sql_real_escape_string(trim($_POST['si_name']));
    $si_desc = sql_real_escape_string(trim($_POST['si_desc']));
    $si_price = (int)$_POST['si_price'];
    $si_type = sql_real_escape_string($_POST['si_type']);
    $si_stock = (int)$_POST['si_stock'];
    $si_limit_per_user = (int)$_POST['si_limit_per_user'];
    $si_sale_start = isset($_POST['si_sale_start']) && $_POST['si_sale_start'] ? "'" . sql_real_escape_string($_POST['si_sale_start']) . "'" : "NULL";
    $si_sale_end = isset($_POST['si_sale_end']) && $_POST['si_sale_end'] ? "'" . sql_real_escape_string($_POST['si_sale_end']) . "'" : "NULL";
    $si_consumable = isset($_POST['si_consumable']) ? 1 : 0;
    $si_display = isset($_POST['si_display']) ? 1 : 0;
    $si_use = isset($_POST['si_use']) ? 1 : 0;
    $si_order = (int)$_POST['si_order'];

    // 유효성 검사
    if (!$si_name || !$si_type) {
        alert('필수 항목을 입력해주세요.');
    }

    // 프로필 스킨은 신규 등록 불가 (개발자가 직접 DB에 등록)
    if ($mode == 'add' && $si_type == 'profile_skin') {
        alert('프로필 스킨은 관리자 페이지에서 신규 등록할 수 없습니다.');
    }

    // 효과 데이터 처리
    $effect = isset($_POST['effect']) ? $_POST['effect'] : array();
    $effect_data = array();

    switch ($si_type) {
        case 'title':
            if (!empty($effect['title'])) {
                $effect_data['title'] = $effect['title'];
                $effect_data['title_color'] = $effect['title_color'] ?? '#ffffff';
            }
            break;
        case 'nick_color':
            if (!empty($effect['nick_color'])) {
                $effect_data['nick_color'] = $effect['nick_color'];
            }
            break;
        case 'nick_effect':
            if (!empty($effect['nick_effect'])) {
                $effect_data['nick_effect'] = $effect['nick_effect'];
            }
            break;
        case 'profile_border':
            $effect_data['border_color'] = $effect['border_color'] ?? '#5865f2';
            $effect_data['border_style'] = $effect['border_style'] ?? 'solid';
            break;
        case 'material':
            if (!empty($effect['material_id'])) {
                $effect_data['material_id'] = (int)$effect['material_id'];
                $effect_data['material_amount'] = max(1, (int)($effect['material_amount'] ?? 1));
            }
            break;
        case 'profile_skin':
            $valid_skins = mg_get_profile_skin_list();
            $skin_id = $effect['skin_id'] ?? '';
            if (isset($valid_skins[$skin_id])) {
                $effect_data['skin_id'] = $skin_id;
            }
            break;
        case 'profile_bg':
            $valid_bgs = mg_get_profile_bg_list();
            $bg_id = $effect['bg_id'] ?? '';
            if (isset($valid_bgs[$bg_id])) {
                $effect_data['bg_id'] = $bg_id;
            }
            break;
        case 'badge':
            $badge_icon = '';
            $badge_icon_type = isset($_POST['badge_icon_type']) ? $_POST['badge_icon_type'] : 'text';

            // 기존 이미지 아이콘
            $current_badge_icon = isset($effect['badge_icon_current']) ? $effect['badge_icon_current'] : '';

            // 이미지 삭제 체크
            if (isset($_POST['del_badge_icon']) && $current_badge_icon) {
                $icon_path = G5_DATA_PATH . '/shop/icons/' . basename($current_badge_icon);
                if (file_exists($icon_path)) {
                    unlink($icon_path);
                }
                $current_badge_icon = '';
            }

            // 새 이미지 업로드
            if ($badge_icon_type === 'file' && isset($_FILES['badge_icon_file']) && $_FILES['badge_icon_file']['error'] == 0) {
                $upload_dir = G5_DATA_PATH . '/shop/icons';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $ext = pathinfo($_FILES['badge_icon_file']['name'], PATHINFO_EXTENSION);
                $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp', 'svg');
                if (in_array(strtolower($ext), $allowed_ext)) {
                    $new_filename = 'badge_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                    $upload_path = $upload_dir . '/' . $new_filename;

                    if (move_uploaded_file($_FILES['badge_icon_file']['tmp_name'], $upload_path)) {
                        // 기존 이미지 삭제
                        if ($current_badge_icon) {
                            $old_path = G5_DATA_PATH . '/shop/icons/' . basename($current_badge_icon);
                            if (file_exists($old_path)) {
                                unlink($old_path);
                            }
                        }
                        $badge_icon = G5_DATA_URL . '/shop/icons/' . $new_filename;
                    }
                }
            } elseif ($badge_icon_type === 'file' && $current_badge_icon) {
                // 파일 모드이고 새 업로드 없으면 기존 이미지 유지
                $badge_icon = $current_badge_icon;
            } elseif ($badge_icon_type === 'text' && !empty($effect['badge_icon'])) {
                // 텍스트 모드면 Heroicons 이름 사용
                $badge_icon = $effect['badge_icon'];
            }

            if ($badge_icon) {
                $effect_data['badge_icon'] = $badge_icon;
                $effect_data['badge_color'] = $effect['badge_color'] ?? '#fbbf24';
            }
            break;
        default:
            if (!empty($effect['custom'])) {
                $custom = json_decode($effect['custom'], true);
                if ($custom) {
                    $effect_data['custom'] = $custom;
                }
            }
    }

    $si_effect = sql_real_escape_string(json_encode($effect_data, JSON_UNESCAPED_UNICODE));

    // 이미지 처리
    $si_image = '';
    if ($mode == 'edit') {
        $old_item = mg_get_shop_item($si_id);
        $si_image = $old_item['si_image'];

        // 이미지 삭제
        if (isset($_POST['del_image']) && $si_image) {
            $image_path = G5_DATA_PATH . '/shop/' . basename($si_image);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
            $si_image = '';
        }
    }

    // 새 이미지 업로드
    if (isset($_FILES['si_image']) && $_FILES['si_image']['error'] == 0) {
        $upload_dir = G5_DATA_PATH . '/shop';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($_FILES['si_image']['name'], PATHINFO_EXTENSION);
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (!in_array(strtolower($ext), $allowed_ext)) {
            alert('허용되지 않는 파일 형식입니다.');
        }

        $new_filename = 'item_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
        $upload_path = $upload_dir . '/' . $new_filename;

        if (move_uploaded_file($_FILES['si_image']['tmp_name'], $upload_path)) {
            // 기존 이미지 삭제
            if ($si_image) {
                $old_path = G5_DATA_PATH . '/shop/' . basename($si_image);
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            $si_image = G5_DATA_URL . '/shop/' . $new_filename;
        }
    }

    $si_image_escaped = sql_real_escape_string($si_image);

    if ($mode == 'add') {
        sql_query("INSERT INTO {$g5['mg_shop_item_table']} SET
            si_name = '{$si_name}',
            si_desc = '{$si_desc}',
            si_image = '{$si_image_escaped}',
            si_price = {$si_price},
            si_type = '{$si_type}',
            si_effect = '{$si_effect}',
            si_stock = {$si_stock},
            si_limit_per_user = {$si_limit_per_user},
            si_sale_start = {$si_sale_start},
            si_sale_end = {$si_sale_end},
            si_consumable = {$si_consumable},
            si_display = {$si_display},
            si_use = {$si_use},
            si_order = {$si_order},
            si_datetime = NOW()");

        goto_url('./shop_item_list.php');
    } else {
        sql_query("UPDATE {$g5['mg_shop_item_table']} SET
            si_name = '{$si_name}',
            si_desc = '{$si_desc}',
            si_image = '{$si_image_escaped}',
            si_price = {$si_price},
            si_type = '{$si_type}',
            si_effect = '{$si_effect}',
            si_stock = {$si_stock},
            si_limit_per_user = {$si_limit_per_user},
            si_sale_start = {$si_sale_start},
            si_sale_end = {$si_sale_end},
            si_consumable = {$si_consumable},
            si_display = {$si_display},
            si_use = {$si_use},
            si_order = {$si_order}
            WHERE si_id = {$si_id}");

        goto_url('./shop_item_form.php?si_id=' . $si_id);
    }
}

// 선택 삭제
if (isset($_POST['btn_delete'])) {
    $chk = isset($_POST['chk']) ? $_POST['chk'] : array();

    foreach ($chk as $si_id) {
        $si_id = (int)$si_id;

        // 상품 정보 조회
        $item = mg_get_shop_item($si_id);
        if (!$item) continue;

        // 이미지 삭제
        if ($item['si_image']) {
            $image_path = G5_DATA_PATH . '/shop/' . basename($item['si_image']);
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // 상품 삭제
        sql_query("DELETE FROM {$g5['mg_shop_item_table']} WHERE si_id = {$si_id}");

        // 관련 인벤토리, 활성화 데이터도 삭제 (선택적)
        // sql_query("DELETE FROM {$g5['mg_inventory_table']} WHERE si_id = {$si_id}");
        // sql_query("DELETE FROM {$g5['mg_item_active_table']} WHERE si_id = {$si_id}");
    }

    $type_group = isset($_POST['type_group']) ? $_POST['type_group'] : '';
    $sfl = isset($_POST['sfl']) ? $_POST['sfl'] : '';
    $stx = isset($_POST['stx']) ? $_POST['stx'] : '';
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;

    goto_url('./shop_item_list.php?type_group=' . $type_group . '&sfl=' . $sfl . '&stx=' . urlencode($stx) . '&page=' . $page);
}

goto_url('./shop_item_list.php');
