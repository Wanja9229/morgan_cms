<?php
/**
 * Morgan Edition - 상품 등록/수정
 */

$sub_menu = "800700";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$si_id = isset($_GET['si_id']) ? (int)$_GET['si_id'] : 0;
$is_edit = $si_id > 0;

// 상품 타입 목록 (morgan.php 단일 소스)
$item_types = mg_get_item_types();

// 타입 그룹 (optgroup용)
$type_groups_form = $mg['shop_type_groups'];

// 기본 상품 정보
$item = array(
    'si_id' => 0,
    'sc_id' => 0,
    'si_name' => '',
    'si_desc' => '',
    'si_image' => '',
    'si_price' => 0,
    'si_type' => 'etc',
    'si_effect' => array(),
    'si_stock' => -1,
    'si_stock_sold' => 0,
    'si_limit_per_user' => 0,
    'si_sale_start' => '',
    'si_sale_end' => '',
    'si_consumable' => 0,
    'si_display' => 1,
    'si_use' => 1,
    'si_order' => 0
);

if ($is_edit) {
    $loaded = mg_get_shop_item($si_id);
    if (!$loaded || !is_array($loaded)) {
        alert('존재하지 않는 상품입니다.', './shop_item_list.php');
        exit;
    }
    // 기본값과 병합 (로드된 값 우선)
    foreach ($loaded as $key => $value) {
        if (array_key_exists($key, $item)) {
            $item[$key] = $value;
        }
    }
}

// 효과 데이터
$effect = array();
if (isset($item['si_effect']) && is_array($item['si_effect'])) {
    $effect = $item['si_effect'];
}

// 안전한 값 접근을 위한 헬퍼
$si_name = isset($item['si_name']) ? $item['si_name'] : '';
$si_type = isset($item['si_type']) ? $item['si_type'] : 'etc';
$si_desc = isset($item['si_desc']) ? $item['si_desc'] : '';
$si_image = isset($item['si_image']) ? $item['si_image'] : '';
$si_price = isset($item['si_price']) ? $item['si_price'] : 0;
$si_stock = isset($item['si_stock']) ? $item['si_stock'] : -1;
$si_stock_sold = isset($item['si_stock_sold']) ? $item['si_stock_sold'] : 0;
$si_limit_per_user = isset($item['si_limit_per_user']) ? $item['si_limit_per_user'] : 0;
$si_sale_start = isset($item['si_sale_start']) ? $item['si_sale_start'] : '';
$si_sale_end = isset($item['si_sale_end']) ? $item['si_sale_end'] : '';
$si_consumable = isset($item['si_consumable']) ? $item['si_consumable'] : 0;
$si_display = isset($item['si_display']) ? $item['si_display'] : 1;
$si_use = isset($item['si_use']) ? $item['si_use'] : 1;
$si_order = isset($item['si_order']) ? $item['si_order'] : 0;
$sc_id = isset($item['sc_id']) ? (int)$item['sc_id'] : 0;

// 타입 설명
$type_desc = isset($item_types[$si_type]) ? $item_types[$si_type]['desc'] : '';

$g5['title'] = $is_edit ? '상품 수정' : '상품 등록';
require_once __DIR__.'/_head.php';
?>

<form name="fitemform" id="fitemform" method="post" action="./shop_item_update.php" enctype="multipart/form-data">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="si_id" value="<?php echo $si_id; ?>">
    <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
        <!-- 기본 정보 -->
        <div>
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">기본 정보</div>
                <div class="mg-card-body">
                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_name">상품명 <span style="color:var(--mg-error);">*</span></label>
                        <input type="text" name="si_name" id="si_name" value="<?php echo htmlspecialchars($si_name); ?>" class="mg-form-input" required>
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_type">아이템 타입 <span style="color:var(--mg-error);">*</span></label>
                        <?php if ($is_edit && in_array($si_type, array('profile_skin', 'profile_bg'))) { ?>
                        <input type="hidden" name="si_type" value="<?php echo $si_type; ?>">
                        <div class="mg-form-input" style="background:var(--mg-bg-primary);cursor:not-allowed;opacity:0.7;"><?php echo $item_types[$si_type]['name'] ?? $si_type; ?></div>
                        <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">이 타입은 변경할 수 없습니다. 개발자가 코드 패치로 추가합니다.</div>
                        <?php } else { ?>
                        <select name="si_type" id="si_type" class="mg-form-select" required onchange="toggleEffectFields();">
                            <?php
                            // 신규 등록 시 제외할 타입
                            $hidden_types = $is_edit ? array() : array('profile_skin', 'profile_bg');
                            foreach ($mg['shop_type_groups'] as $grp_key => $grp) {
                                echo '<optgroup label="' . htmlspecialchars($grp['label']) . '">';
                                foreach ($grp['types'] as $t_key) {
                                    if (in_array($t_key, $hidden_types)) continue;
                                    if (!isset($item_types[$t_key])) continue;
                                    $sel = ($si_type == $t_key) ? ' selected' : '';
                                    echo '<option value="' . $t_key . '"' . $sel . '>' . htmlspecialchars($item_types[$t_key]['name']) . '</option>';
                                }
                                echo '</optgroup>';
                            }
                            ?>
                        </select>
                        <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;" id="type_desc">
                            <?php echo $type_desc; ?>
                        </div>
                        <?php } ?>
                    </div>

                    <input type="hidden" name="sc_id" value="0">

                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_desc">상품 설명</label>
                        <textarea name="si_desc" id="si_desc" class="mg-form-textarea" rows="4"><?php echo htmlspecialchars($si_desc); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- 효과 설정 -->
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">효과 설정</div>
                <div class="mg-card-body">
                    <!-- 칭호 -->
                    <div class="effect-field" data-type="title" style="<?php echo $si_type != 'title' ? 'display:none;' : ''; ?>">
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_title">칭호 텍스트</label>
                            <input type="text" name="effect[title]" id="effect_title" value="<?php echo htmlspecialchars(isset($effect['title']) ? $effect['title'] : ''); ?>" class="mg-form-input" placeholder="예: 초보 모험가">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_title_color">칭호 색상</label>
                            <input type="color" name="effect[title_color]" id="effect_title_color" value="<?php echo isset($effect['title_color']) ? $effect['title_color'] : '#ffffff'; ?>" style="width:100px;height:38px;">
                        </div>
                    </div>

                    <!-- 닉네임 색상 -->
                    <div class="effect-field" data-type="nick_color" style="<?php echo $si_type != 'nick_color' ? 'display:none;' : ''; ?>">
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_nick_color">닉네임 색상</label>
                            <input type="color" name="effect[nick_color]" id="effect_nick_color" value="<?php echo isset($effect['nick_color']) ? $effect['nick_color'] : '#ffffff'; ?>" style="width:100px;height:38px;">
                        </div>
                    </div>

                    <!-- 닉네임 효과 -->
                    <div class="effect-field" data-type="nick_effect" style="<?php echo $si_type != 'nick_effect' ? 'display:none;' : ''; ?>">
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_nick_effect">효과 종류</label>
                            <?php $eff_nick = isset($effect['nick_effect']) ? $effect['nick_effect'] : ''; ?>
                            <select name="effect[nick_effect]" id="effect_nick_effect" class="mg-form-select">
                                <option value="glow" <?php echo $eff_nick == 'glow' ? 'selected' : ''; ?>>글로우</option>
                                <option value="rainbow" <?php echo $eff_nick == 'rainbow' ? 'selected' : ''; ?>>무지개</option>
                                <option value="shake" <?php echo $eff_nick == 'shake' ? 'selected' : ''; ?>>흔들림</option>
                                <option value="gradient" <?php echo $eff_nick == 'gradient' ? 'selected' : ''; ?>>그라데이션</option>
                            </select>
                        </div>
                    </div>

                    <!-- 프로필 테두리 -->
                    <div class="effect-field" data-type="profile_border" style="<?php echo $si_type != 'profile_border' ? 'display:none;' : ''; ?>">
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_border_color">테두리 색상</label>
                            <input type="color" name="effect[border_color]" id="effect_border_color" value="<?php echo isset($effect['border_color']) ? $effect['border_color'] : '#5865f2'; ?>" style="width:100px;height:38px;">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_border_style">테두리 스타일</label>
                            <?php $eff_border = isset($effect['border_style']) ? $effect['border_style'] : ''; ?>
                            <select name="effect[border_style]" id="effect_border_style" class="mg-form-select">
                                <option value="solid" <?php echo $eff_border == 'solid' ? 'selected' : ''; ?>>실선</option>
                                <option value="double" <?php echo $eff_border == 'double' ? 'selected' : ''; ?>>이중선</option>
                                <option value="dashed" <?php echo $eff_border == 'dashed' ? 'selected' : ''; ?>>점선</option>
                                <option value="gradient" <?php echo $eff_border == 'gradient' ? 'selected' : ''; ?>>그라데이션</option>
                            </select>
                        </div>
                    </div>

                    <!-- 뱃지 -->
                    <div class="effect-field" data-type="badge" style="<?php echo $si_type != 'badge' ? 'display:none;' : ''; ?>">
                        <?php
                        $badge_icon = isset($effect['badge_icon']) ? $effect['badge_icon'] : '';
                        $badge_is_image = $badge_icon && (strpos($badge_icon, '/') !== false || strpos($badge_icon, 'http') === 0);
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">뱃지 아이콘</label>
                            <div style="display:flex;gap:1rem;margin-bottom:0.5rem;">
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="radio" name="badge_icon_type" value="text" <?php echo !$badge_is_image ? 'checked' : ''; ?> onchange="toggleBadgeIconInput();">
                                    <span>Heroicons 이름</span>
                                </label>
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="radio" name="badge_icon_type" value="file" <?php echo $badge_is_image ? 'checked' : ''; ?> onchange="toggleBadgeIconInput();">
                                    <span>이미지 업로드</span>
                                </label>
                            </div>
                            <div id="badge_icon_text" style="<?php echo $badge_is_image ? 'display:none;' : ''; ?>">
                                <input type="text" name="effect[badge_icon]" id="effect_badge_icon" value="<?php echo htmlspecialchars(!$badge_is_image ? $badge_icon : ''); ?>" class="mg-form-input" placeholder="예: star, heart, shield">
                                <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">Heroicons 아이콘명 입력</div>
                            </div>
                            <div id="badge_icon_file" style="<?php echo !$badge_is_image ? 'display:none;' : ''; ?>">
                                <?php if ($badge_is_image) { ?>
                                <div style="margin-bottom:0.5rem;display:flex;align-items:center;gap:1rem;">
                                    <img src="<?php echo $badge_icon; ?>" style="width:48px;height:48px;object-fit:contain;background:var(--mg-bg-tertiary);border-radius:0.25rem;">
                                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;color:var(--mg-error);font-size:0.875rem;">
                                        <input type="checkbox" name="del_badge_icon" value="1">
                                        <span>삭제</span>
                                    </label>
                                    <input type="hidden" name="effect[badge_icon_current]" value="<?php echo htmlspecialchars($badge_icon); ?>">
                                </div>
                                <?php } ?>
                                <input type="file" name="badge_icon_file" accept="image/*" class="mg-form-input" style="padding:0.5rem;">
                                <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">권장: 48x48px, PNG/SVG</div>
                            </div>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_badge_color">뱃지 배경색</label>
                            <input type="color" name="effect[badge_color]" id="effect_badge_color" value="<?php echo isset($effect['badge_color']) ? $effect['badge_color'] : '#fbbf24'; ?>" style="width:100px;height:38px;">
                        </div>
                    </div>

                    <!-- 프로필 스킨 -->
                    <div class="effect-field" data-type="profile_skin" style="<?php echo $si_type != 'profile_skin' ? 'display:none;' : ''; ?>">
                        <?php
                        $profile_skins = mg_get_profile_skin_list();
                        $eff_skin_id = isset($effect['skin_id']) ? $effect['skin_id'] : '';
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">스킨</label>
                            <input type="hidden" name="effect[skin_id]" value="<?php echo htmlspecialchars($eff_skin_id); ?>">
                            <div class="mg-form-input" style="background:var(--mg-bg-primary);cursor:not-allowed;opacity:0.7;">
                                <?php echo htmlspecialchars(isset($profile_skins[$eff_skin_id]) ? $profile_skins[$eff_skin_id] : $eff_skin_id); ?>
                            </div>
                            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">스킨 종류는 개발자가 코드 패치로 관리합니다.</div>
                        </div>
                    </div>

                    <!-- 프로필 배경 (색상 또는 이미지) -->
                    <div class="effect-field" data-type="profile_bg" style="<?php echo $si_type != 'profile_bg' ? 'display:none;' : ''; ?>">
                        <?php
                        $eff_pbg_mode = !empty($effect['image']) ? 'image' : 'color';
                        $eff_pbg_color = $effect['color'] ?? '#1e1f22';
                        $eff_pbg_image = $effect['image'] ?? '';
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">배경 유형</label>
                            <select name="effect[bg_mode]" id="profile_bg_mode" class="mg-form-select" onchange="toggleBgMode('profile_bg', this.value)">
                                <option value="color" <?php echo $eff_pbg_mode === 'color' ? 'selected' : ''; ?>>색상</option>
                                <option value="image" <?php echo $eff_pbg_mode === 'image' ? 'selected' : ''; ?>>이미지</option>
                            </select>
                        </div>
                        <div class="mg-form-group profile_bg-color-field" style="<?php echo $eff_pbg_mode !== 'color' ? 'display:none;' : ''; ?>">
                            <label class="mg-form-label">배경 색상</label>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <input type="color" name="effect[color]" id="eff_profile_bg_color" value="<?php echo htmlspecialchars($eff_pbg_color); ?>" style="width:100px;height:38px;">
                                <span id="eff_profile_bg_color_hex" style="font-size:0.8rem;color:var(--mg-text-muted);"><?php echo htmlspecialchars($eff_pbg_color); ?></span>
                            </div>
                        </div>
                        <div class="mg-form-group profile_bg-image-field" style="<?php echo $eff_pbg_mode !== 'image' ? 'display:none;' : ''; ?>">
                            <label class="mg-form-label">배경 이미지</label>
                            <?php if ($eff_pbg_image) { ?>
                            <div style="margin-bottom:0.5rem;">
                                <img src="<?php echo htmlspecialchars($eff_pbg_image); ?>" style="max-width:200px;max-height:80px;border-radius:6px;border:1px solid var(--mg-bg-tertiary);">
                                <input type="hidden" name="effect[image_current]" value="<?php echo htmlspecialchars($eff_pbg_image); ?>">
                            </div>
                            <?php } ?>
                            <input type="file" name="bg_image_file" accept="image/*" class="mg-form-input">
                            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">권장: 800x200 이하, 500KB 이하. 투명 PNG 가능.</div>
                        </div>
                    </div>

                    <!-- 프로필 이펙트 (Vanta.js 효과) -->
                    <div class="effect-field" data-type="profile_effect" style="<?php echo $si_type != 'profile_effect' ? 'display:none;' : ''; ?>">
                        <?php
                        $profile_effects = mg_get_profile_effect_list();
                        $eff_pe_id = $effect['bg_id'] ?? '';
                        $eff_pe_color = $effect['bg_color'] ?? '#f59f0a';
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">이펙트 효과</label>
                            <select name="effect[bg_id]" class="mg-form-select">
                                <option value="">선택</option>
                                <?php foreach ($profile_effects as $pe_key => $pe_name) { ?>
                                <option value="<?php echo $pe_key; ?>" <?php echo $eff_pe_id == $pe_key ? 'selected' : ''; ?>><?php echo htmlspecialchars($pe_name); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">효과 기본 색상</label>
                            <input type="color" name="effect[bg_color]" value="<?php echo htmlspecialchars($eff_pe_color); ?>" style="width:100px;height:38px;">
                        </div>
                    </div>

                    <!-- 인장 이펙트 (CSS 애니메이션) -->
                    <div class="effect-field" data-type="seal_effect" style="<?php echo $si_type != 'seal_effect' ? 'display:none;' : ''; ?>">
                        <?php
                        $seal_effects = mg_get_seal_effect_list();
                        $eff_se_id = $effect['bg_id'] ?? '';
                        $eff_se_color = $effect['bg_color'] ?? '#f59f0a';
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">이펙트 효과</label>
                            <select name="effect[bg_id]" class="mg-form-select">
                                <option value="">선택</option>
                                <?php foreach ($seal_effects as $se_key => $se_name) { ?>
                                <option value="<?php echo $se_key; ?>" <?php echo $eff_se_id == $se_key ? 'selected' : ''; ?>><?php echo htmlspecialchars($se_name); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">효과 기본 색상</label>
                            <input type="color" name="effect[bg_color]" value="<?php echo htmlspecialchars($eff_se_color); ?>" style="width:100px;height:38px;">
                        </div>
                    </div>

                    <!-- 재료 타입 -->
                    <div class="effect-field" data-type="material" style="<?php echo $si_type != 'material' ? 'display:none;' : ''; ?>">
                        <?php
                        $material_types = mg_get_material_types();
                        $eff_material_id = isset($effect['material_id']) ? $effect['material_id'] : '';
                        $eff_material_amount = isset($effect['material_amount']) ? $effect['material_amount'] : 1;
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_material_id">지급 재료</label>
                            <select name="effect[material_id]" id="effect_material_id" class="mg-form-select">
                                <option value="">선택</option>
                                <?php foreach ($material_types as $mt) { ?>
                                <option value="<?php echo $mt['mt_id']; ?>" <?php echo $eff_material_id == $mt['mt_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($mt['mt_name']); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_material_amount">지급 수량</label>
                            <input type="number" name="effect[material_amount]" id="effect_material_amount" value="<?php echo $eff_material_amount; ?>" class="mg-form-input" min="1">
                        </div>
                    </div>

                    <!-- 인장 프레임 -->
                    <div class="effect-field" data-type="seal_frame" style="<?php echo $si_type != 'seal_frame' ? 'display:none;' : ''; ?>">
                        <div class="seal-frame-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="mg-form-group">
                                <label class="mg-form-label">테두리 색상</label>
                                <input type="color" name="effect[border_color]" id="effect_seal_border_color" value="<?php echo isset($effect['border_color']) ? $effect['border_color'] : '#d4a843'; ?>" style="width:100px;height:38px;">
                            </div>
                            <div class="mg-form-group">
                                <label class="mg-form-label">테두리 스타일</label>
                                <?php $eff_sf_style = isset($effect['border_style']) ? $effect['border_style'] : 'solid'; ?>
                                <select name="effect[border_style]" id="effect_seal_border_style" class="mg-form-select">
                                    <option value="solid" <?php echo $eff_sf_style == 'solid' ? 'selected' : ''; ?>>실선 (solid)</option>
                                    <option value="double" <?php echo $eff_sf_style == 'double' ? 'selected' : ''; ?>>이중선 (double)</option>
                                    <option value="dashed" <?php echo $eff_sf_style == 'dashed' ? 'selected' : ''; ?>>점선 (dashed)</option>
                                    <option value="dotted" <?php echo $eff_sf_style == 'dotted' ? 'selected' : ''; ?>>도트 (dotted)</option>
                                    <option value="ridge" <?php echo $eff_sf_style == 'ridge' ? 'selected' : ''; ?>>릿지 (ridge)</option>
                                    <option value="groove" <?php echo $eff_sf_style == 'groove' ? 'selected' : ''; ?>>그루브 (groove)</option>
                                </select>
                            </div>
                            <div class="mg-form-group">
                                <label class="mg-form-label">테두리 굵기</label>
                                <?php $eff_sf_width = isset($effect['border_width']) ? $effect['border_width'] : '2px'; ?>
                                <select name="effect[border_width]" id="effect_seal_border_width" class="mg-form-select">
                                    <option value="1px" <?php echo $eff_sf_width == '1px' ? 'selected' : ''; ?>>1px (얇은)</option>
                                    <option value="2px" <?php echo $eff_sf_width == '2px' ? 'selected' : ''; ?>>2px (보통)</option>
                                    <option value="3px" <?php echo $eff_sf_width == '3px' ? 'selected' : ''; ?>>3px (굵은)</option>
                                    <option value="4px" <?php echo $eff_sf_width == '4px' ? 'selected' : ''; ?>>4px (아주 굵은)</option>
                                </select>
                            </div>
                            <div class="mg-form-group">
                                <label class="mg-form-label">둥글기</label>
                                <?php $eff_sf_radius = isset($effect['border_radius']) ? $effect['border_radius'] : '12px'; ?>
                                <select name="effect[border_radius]" id="effect_seal_border_radius" class="mg-form-select">
                                    <option value="0" <?php echo $eff_sf_radius == '0' ? 'selected' : ''; ?>>없음 (0)</option>
                                    <option value="6px" <?php echo $eff_sf_radius == '6px' ? 'selected' : ''; ?>>약간 (6px)</option>
                                    <option value="12px" <?php echo $eff_sf_radius == '12px' ? 'selected' : ''; ?>>보통 (12px)</option>
                                    <option value="16px" <?php echo $eff_sf_radius == '16px' ? 'selected' : ''; ?>>많이 (16px)</option>
                                    <option value="24px" <?php echo $eff_sf_radius == '24px' ? 'selected' : ''; ?>>크게 (24px)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mg-form-group" style="margin-top:0.75rem;">
                            <label class="mg-form-label">그림자 효과</label>
                            <?php $eff_sf_shadow = isset($effect['box_shadow']) ? $effect['box_shadow'] : ''; ?>
                            <select name="effect[box_shadow]" id="effect_seal_box_shadow" class="mg-form-select">
                                <option value="" <?php echo !$eff_sf_shadow ? 'selected' : ''; ?>>없음</option>
                                <option value="0 2px 8px rgba(0,0,0,0.3)" <?php echo $eff_sf_shadow == '0 2px 8px rgba(0,0,0,0.3)' ? 'selected' : ''; ?>>약한 그림자</option>
                                <option value="0 4px 20px rgba(0,0,0,0.4)" <?php echo $eff_sf_shadow == '0 4px 20px rgba(0,0,0,0.4)' ? 'selected' : ''; ?>>보통 그림자</option>
                                <option value="0 8px 32px rgba(0,0,0,0.5)" <?php echo $eff_sf_shadow == '0 8px 32px rgba(0,0,0,0.5)' ? 'selected' : ''; ?>>강한 그림자</option>
                                <option value="0 0 12px rgba(245,159,10,0.4)" <?php echo strpos($eff_sf_shadow, '245,159,10') !== false ? 'selected' : ''; ?>>앰버 글로우</option>
                                <option value="0 0 12px rgba(59,130,246,0.4)" <?php echo strpos($eff_sf_shadow, '59,130,246') !== false ? 'selected' : ''; ?>>블루 글로우</option>
                            </select>
                        </div>
                    </div>

                    <!-- 인장 배경 (색상 또는 이미지) -->
                    <div class="effect-field" data-type="seal_bg" style="<?php echo $si_type != 'seal_bg' ? 'display:none;' : ''; ?>">
                        <?php
                        $eff_sbg_mode = !empty($effect['image']) ? 'image' : 'color';
                        $eff_sbg_color = $effect['color'] ?? '#2b2d31';
                        $eff_sbg_image = $effect['image'] ?? '';
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">배경 유형</label>
                            <select name="effect[bg_mode]" id="seal_bg_mode" class="mg-form-select" onchange="toggleBgMode('seal_bg', this.value)">
                                <option value="color" <?php echo $eff_sbg_mode === 'color' ? 'selected' : ''; ?>>색상</option>
                                <option value="image" <?php echo $eff_sbg_mode === 'image' ? 'selected' : ''; ?>>이미지</option>
                            </select>
                        </div>
                        <div class="mg-form-group seal_bg-color-field" style="<?php echo $eff_sbg_mode !== 'color' ? 'display:none;' : ''; ?>">
                            <label class="mg-form-label">배경 색상</label>
                            <div style="display:flex;align-items:center;gap:0.75rem;">
                                <input type="color" name="effect[color]" id="eff_seal_bg_color" value="<?php echo htmlspecialchars($eff_sbg_color); ?>" style="width:100px;height:38px;">
                                <span id="eff_seal_bg_color_hex" style="font-size:0.8rem;color:var(--mg-text-muted);"><?php echo htmlspecialchars($eff_sbg_color); ?></span>
                            </div>
                        </div>
                        <div class="mg-form-group seal_bg-image-field" style="<?php echo $eff_sbg_mode !== 'image' ? 'display:none;' : ''; ?>">
                            <label class="mg-form-label">배경 이미지</label>
                            <?php if ($eff_sbg_image) { ?>
                            <div style="margin-bottom:0.5rem;">
                                <img src="<?php echo htmlspecialchars($eff_sbg_image); ?>" style="max-width:200px;max-height:80px;border-radius:6px;border:1px solid var(--mg-bg-tertiary);">
                                <input type="hidden" name="effect[image_current]" value="<?php echo htmlspecialchars($eff_sbg_image); ?>">
                            </div>
                            <?php } ?>
                            <input type="file" name="bg_image_file" accept="image/*" class="mg-form-input">
                            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">권장: 800x200 이하, 500KB 이하. 투명 PNG 가능.</div>
                        </div>
                    </div>

                    <!-- 인장 호버 -->
                    <div class="effect-field" data-type="seal_hover" style="<?php echo $si_type != 'seal_hover' ? 'display:none;' : ''; ?>">
                        <?php
                        $hover_presets = mg_get_seal_hover_presets();
                        $eff_hover_id = isset($effect['hover_id']) ? $effect['hover_id'] : '';
                        ?>
                        <div class="mg-form-group">
                            <label class="mg-form-label">호버 효과</label>
                            <select name="effect[hover_id]" class="mg-form-select">
                                <option value="">선택</option>
                                <?php foreach ($hover_presets as $hk => $hv) { ?>
                                <option value="<?php echo $hk; ?>" <?php echo $eff_hover_id == $hk ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($hv['name']); ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">효과 미리보기</label>
                            <div style="padding:0.5rem;background:var(--mg-bg-primary);border-radius:6px;font-size:0.8rem;color:var(--mg-text-muted);font-family:monospace;" id="seal_hover_preview">
                                <?php echo $eff_hover_id && isset($hover_presets[$eff_hover_id]) ? htmlspecialchars($hover_presets[$eff_hover_id]['css']) : '효과를 선택하면 CSS가 표시됩니다'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- 기타 타입 -->
                    <div class="effect-field" data-type="equip,furniture,etc" style="<?php echo !in_array($si_type, array('equip','furniture','etc')) ? 'display:none;' : ''; ?>">
                        <div class="mg-form-group">
                            <label class="mg-form-label" for="effect_custom">커스텀 데이터 (JSON)</label>
                            <textarea name="effect[custom]" id="effect_custom" class="mg-form-textarea" rows="3" placeholder='{"key": "value"}'><?php echo htmlspecialchars(isset($effect['custom']) ? $effect['custom'] : ''); ?></textarea>
                            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">추가 효과 데이터를 JSON 형식으로 입력합니다.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 우측 패널 -->
        <div>
            <!-- 이미지 -->
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">상품 이미지</div>
                <div class="mg-card-body">
                    <?php if ($si_image) { ?>
                    <div style="margin-bottom:1rem;text-align:center;">
                        <img src="<?php echo $si_image; ?>" style="max-width:100%;max-height:200px;border-radius:0.5rem;">
                        <label style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-top:0.5rem;cursor:pointer;color:var(--mg-error);">
                            <input type="checkbox" name="del_image" value="1">
                            <span>이미지 삭제</span>
                        </label>
                    </div>
                    <?php } ?>
                    <input type="file" name="si_image" id="si_image" accept="image/*" class="mg-form-input" style="padding:0.5rem;">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">권장: 200x200px, PNG/JPG</div>
                </div>
            </div>

            <!-- 가격/재고 -->
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">가격 및 재고</div>
                <div class="mg-card-body">
                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_price">가격 (포인트) <span style="color:var(--mg-error);">*</span></label>
                        <input type="number" name="si_price" id="si_price" value="<?php echo $si_price; ?>" class="mg-form-input" min="0" required>
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_stock">재고</label>
                        <input type="number" name="si_stock" id="si_stock" value="<?php echo $si_stock; ?>" class="mg-form-input" min="-1">
                        <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">-1 = 무제한</div>
                    </div>

                    <?php if ($is_edit && $si_stock_sold > 0) { ?>
                    <div class="mg-form-group">
                        <label class="mg-form-label">판매 수량</label>
                        <div style="padding:0.625rem;background:var(--mg-bg-primary);border-radius:0.375rem;color:var(--mg-accent);">
                            <?php echo number_format($si_stock_sold); ?>개
                        </div>
                    </div>
                    <?php } ?>

                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_limit_per_user">1인당 구매 제한</label>
                        <input type="number" name="si_limit_per_user" id="si_limit_per_user" value="<?php echo $si_limit_per_user; ?>" class="mg-form-input" min="0">
                        <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">0 = 무제한</div>
                    </div>
                </div>
            </div>

            <!-- 판매 설정 -->
            <div class="mg-card" style="margin-bottom:1.5rem;">
                <div class="mg-card-header">판매 설정</div>
                <div class="mg-card-body">
                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_sale_start">판매 시작일</label>
                        <input type="datetime-local" name="si_sale_start" id="si_sale_start" value="<?php echo $si_sale_start ? date('Y-m-d\TH:i', strtotime($si_sale_start)) : ''; ?>" class="mg-form-input">
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label" for="si_sale_end">판매 종료일</label>
                        <input type="datetime-local" name="si_sale_end" id="si_sale_end" value="<?php echo $si_sale_end ? date('Y-m-d\TH:i', strtotime($si_sale_end)) : ''; ?>" class="mg-form-input">
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:1rem;margin-top:1rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="si_consumable" value="1" <?php echo $si_consumable ? 'checked' : ''; ?>>
                            <span>소모품</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="si_display" value="1" <?php echo $si_display ? 'checked' : ''; ?>>
                            <span>노출</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="si_use" value="1" <?php echo $si_use ? 'checked' : ''; ?>>
                            <span>사용 가능</span>
                        </label>
                    </div>

                    <div class="mg-form-group" style="margin-top:1rem;">
                        <label class="mg-form-label" for="si_order">정렬 순서</label>
                        <input type="number" name="si_order" id="si_order" value="<?php echo $si_order; ?>" class="mg-form-input" min="0">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
        <a href="./shop_item_list.php" class="mg-btn mg-btn-secondary">목록</a>
        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '수정' : '등록'; ?></button>
    </div>
</form>

<script>
var itemTypeDescs = <?php echo json_encode(array_map(function($t) { return $t['desc']; }, $item_types)); ?>;

function toggleEffectFields() {
    var type = document.getElementById('si_type').value;
    document.getElementById('type_desc').textContent = itemTypeDescs[type] || '';

    document.querySelectorAll('.effect-field').forEach(function(el) {
        var types = el.dataset.type.split(',');
        el.style.display = types.includes(type) ? '' : 'none';
    });
}

function toggleBadgeIconInput() {
    var type = document.querySelector('input[name="badge_icon_type"]:checked').value;
    document.getElementById('badge_icon_text').style.display = type === 'text' ? '' : 'none';
    document.getElementById('badge_icon_file').style.display = type === 'file' ? '' : 'none';
}

// 배경 유형 (색상/이미지) 토글
function toggleBgMode(prefix, mode) {
    var colorFields = document.querySelectorAll('.' + prefix + '-color-field');
    var imageFields = document.querySelectorAll('.' + prefix + '-image-field');
    colorFields.forEach(function(el) { el.style.display = mode === 'color' ? '' : 'none'; });
    imageFields.forEach(function(el) { el.style.display = mode === 'image' ? '' : 'none'; });
}

// 인장 호버 미리보기
var _hoverPresets = <?php echo json_encode(array_map(function($v) { return $v['css']; }, mg_get_seal_hover_presets())); ?>;
document.addEventListener('change', function(e) {
    if (e.target.name === 'effect[hover_id]') {
        var preview = document.getElementById('seal_hover_preview');
        preview.textContent = _hoverPresets[e.target.value] || '효과를 선택하면 CSS가 표시됩니다';
    }
});
</script>

<style>
@media (max-width: 768px) {
    form > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
    .seal-frame-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php
require_once __DIR__.'/_tail.php';
?>
