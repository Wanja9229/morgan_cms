<?php
/**
 * Morgan Edition - 프로필 스킨: Windows 98 클래식
 * Win98 UI 스타일 (베벨 테두리, 회색 패널, 메뉴바)
 *
 * 사용 가능한 변수:
 * $char, $grouped_fields, $achievement_showcase, $char_relations,
 * $is_owner, $can_request_relation, $my_approved_characters, $profile_skin_id
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);
?>

<div class="mg-inner skin-win98" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#000080;margin-bottom:8px;text-decoration:none;">
        <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
        <span>&lt; 뒤로</span>
    </a>

    <!-- 메인 윈도우 -->
    <div class="w98-window">
        <!-- 타이틀바 -->
        <div class="w98-titlebar">
            <div style="display:flex;align-items:center;gap:6px;">
                <div class="w98-icon">M</div>
                <span>사용자 정보 - <?php echo $ch_name; ?></span>
            </div>
            <div style="display:flex;gap:2px;">
                <button class="w98-btn" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;padding-bottom:2px;">_</button>
                <button class="w98-btn" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;">&#9633;</button>
                <button class="w98-btn" style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:bold;">X</button>
            </div>
        </div>

        <div style="padding:0 8px 8px;">
            <!-- 메뉴바 -->
            <div style="display:flex;gap:12px;font-size:12px;color:#000;border-bottom:1px solid #808080;padding-bottom:4px;margin-bottom:8px;">
                <span>파일(<u>F</u>)</span>
                <span>편집(<u>E</u>)</span>
                <span>보기(<u>V</u>)</span>
                <span>도움말(<u>H</u>)</span>
            </div>

            <!-- 헤더 배너 -->
            <?php if ($char_header) { ?>
            <div class="w98-inset" style="margin-bottom:8px;max-height:160px;overflow:hidden;">
                <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <?php } ?>

            <!-- 프로필 정보 -->
            <div class="w98-inset" style="background:#fff;padding:12px;">
                <div style="display:flex;align-items:flex-start;gap:16px;margin-bottom:12px;">
                    <!-- 프로필 이미지 -->
                    <div class="w98-inset" style="background:#c0c0c0;width:100px;height:100px;flex-shrink:0;">
                        <?php if ($char_image) { ?>
                        <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;display:block;">
                        <?php } else { ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#808080;font-size:11px;text-align:center;">사진<br>없음</div>
                        <?php } ?>
                    </div>
                    <div style="flex:1;">
                        <!-- 배지 -->
                        <div style="margin-bottom:4px;">
                            <?php if ($char['ch_main']) { ?>
                            <span style="background:#000080;color:#fff;font-size:10px;padding:1px 6px;">대표</span>
                            <?php } ?>
                            <?php
                            $state_labels = array(
                                'editing' => array('수정중', '#808080'),
                                'pending' => array('승인대기', '#808000'),
                                'approved' => array('승인됨', '#008000'),
                            );
                            $state = $state_labels[$char['ch_state']] ?? array('', '');
                            if ($state[0]) { ?>
                            <span style="background:<?php echo $state[1]; ?>;color:#fff;font-size:10px;padding:1px 6px;"><?php echo $state[0]; ?></span>
                            <?php } ?>
                        </div>
                        <h2 style="font-size:20px;font-weight:bold;margin:0 0 4px;letter-spacing:0.05em;"><?php echo $ch_name; ?></h2>
                        <div style="font-size:12px;color:#333;">
                            <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                            <span><?php echo $ch_side; ?></span>
                            <?php } ?>
                            <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                            <?php if ($ch_side && mg_config('use_side', '1') == '1') echo ' | '; ?>
                            <span><?php echo $ch_class; ?></span>
                            <?php } ?>
                        </div>
                        <div style="font-size:11px;color:#0000ff;margin-top:4px;">[상태: <?php echo $state[0] ?: '활성'; ?>]</div>
                        <div style="font-size:11px;color:#808080;margin-top:6px;">
                            @<?php echo $ch_owner; ?> &middot; <?php echo $ch_date; ?> 등록
                        </div>
                    </div>
                </div>

                <!-- 액션 버튼 -->
                <div style="display:flex;gap:4px;">
                    <?php if ($can_request_relation) { ?>
                    <button type="button" onclick="openRelRequestModal()" class="w98-btn" style="padding:3px 12px;font-size:12px;cursor:pointer;font-family:inherit;">관계 신청</button>
                    <?php } ?>
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="w98-btn" style="padding:3px 12px;font-size:12px;text-decoration:none;color:#000;display:inline-block;">수정</a>
                    <?php } ?>
                </div>
            </div>

            <!-- 전투 능력치 -->
            <?php if ($_battle_use == '1' && $battle_stat) {
                $_stat_base = (int)mg_config('battle_stat_base', '5');
                $_bs_hp = (int)($battle_stat['stat_hp'] ?? $_stat_base);
                $_bs_str = (int)($battle_stat['stat_str'] ?? $_stat_base);
                $_bs_dex = (int)($battle_stat['stat_dex'] ?? $_stat_base);
                $_bs_int = (int)($battle_stat['stat_int'] ?? $_stat_base);
                $_bs_stress = (int)($battle_stat['stat_stress'] ?? 0);
                $_stress_color = $_bs_stress >= 100 ? '#ff0000' : ($_bs_stress >= 70 ? '#808000' : '#008000');
            ?>
            <?php if ($battle_hp && $battle_hp['max_hp'] > 0) {
                $_hp_pct = round($battle_hp['current_hp'] / $battle_hp['max_hp'] * 100);
                $_hp_color = $_hp_pct > 60 ? '#008000' : ($_hp_pct > 30 ? '#808000' : '#ff0000');
            ?>
            <div class="w98-inset" style="background:#fff;padding:8px;margin-top:8px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                    <span style="font-weight:bold;color:<?php echo $_hp_color; ?>;">HP</span>
                    <span style="color:<?php echo $_hp_color; ?>;"><?php echo $battle_hp['current_hp']; ?> / <?php echo $battle_hp['max_hp']; ?></span>
                </div>
                <div class="w98-inset" style="height:16px;background:#fff;">
                    <div style="height:100%;background:<?php echo $_hp_color; ?>;width:<?php echo $_hp_pct; ?>%;"></div>
                </div>
            </div>
            <?php } ?>

            <div class="w98-inset" style="background:#fff;padding:8px;margin-top:8px;">
                <div class="w98-section-header">캐릭터 스탯</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
                    <div class="w98-inset" style="background:#c0c0c0;padding:4px 8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#800000;font-weight:bold;">HP</span>
                        <span class="w98-inset" style="background:#fff;padding:1px 8px;min-width:48px;text-align:right;display:inline-block;"><?php echo $_bs_hp; ?></span>
                    </div>
                    <div class="w98-inset" style="background:#c0c0c0;padding:4px 8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#804000;font-weight:bold;">STR</span>
                        <span class="w98-inset" style="background:#fff;padding:1px 8px;min-width:48px;text-align:right;display:inline-block;"><?php echo $_bs_str; ?></span>
                    </div>
                    <div class="w98-inset" style="background:#c0c0c0;padding:4px 8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#006400;font-weight:bold;">DEX</span>
                        <span class="w98-inset" style="background:#fff;padding:1px 8px;min-width:48px;text-align:right;display:inline-block;"><?php echo $_bs_dex; ?></span>
                    </div>
                    <div class="w98-inset" style="background:#c0c0c0;padding:4px 8px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="color:#000080;font-weight:bold;">INT</span>
                        <span class="w98-inset" style="background:#fff;padding:1px 8px;min-width:48px;text-align:right;display:inline-block;"><?php echo $_bs_int; ?></span>
                    </div>
                </div>
                <!-- 스트레스 -->
                <div style="margin-top:8px;">
                    <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
                        <span style="color:#808080;">스트레스</span>
                        <span style="font-weight:bold;color:<?php echo $_stress_color; ?>;"><?php echo $_bs_stress; ?>/100</span>
                    </div>
                    <div class="w98-inset" style="height:12px;background:#fff;">
                        <div style="height:100%;background:<?php echo $_stress_color; ?>;width:<?php echo min(100, $_bs_stress); ?>%;"></div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- 업적 쇼케이스 -->
            <?php if (!empty($achievement_showcase)) {
                $ach_rarity_colors = array(
                    'common' => '#808080', 'uncommon' => '#008000', 'rare' => '#0000ff',
                    'epic' => '#800080', 'legendary' => '#808000',
                );
                $ach_rarity_labels = array(
                    'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare',
                    'epic' => 'Epic', 'legendary' => 'Legendary',
                );
            ?>
            <div class="w98-inset" style="background:#fff;padding:8px;margin-top:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div class="w98-section-header" style="margin-bottom:0;">업적 쇼케이스</div>
                    <a href="<?php echo G5_BBS_URL; ?>/achievement.php" style="font-size:11px;color:#0000ff;">전체보기</a>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;">
                    <?php foreach ($achievement_showcase as $acd) {
                        $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                        $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                        $a_rarity = $acd['ac_rarity'] ?: 'common';
                        $a_color = $ach_rarity_colors[$a_rarity] ?? '#808080';
                    ?>
                    <div class="w98-outset" style="padding:4px 8px;display:flex;flex-direction:column;align-items:center;min-width:70px;" title="<?php echo $a_name; ?>">
                        <?php if ($a_icon) { ?>
                        <img src="<?php echo htmlspecialchars($a_icon); ?>" alt="<?php echo $a_name; ?>" style="width:28px;height:28px;object-fit:contain;">
                        <?php } else { ?>
                        <span style="font-size:20px;">&#9733;</span>
                        <?php } ?>
                        <span style="font-size:10px;color:#333;text-align:center;max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;margin-top:2px;"><?php echo $a_name; ?></span>
                        <span style="font-size:9px;color:<?php echo $a_color; ?>;"><?php echo $ach_rarity_labels[$a_rarity] ?? ''; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 프로필 필드 -->
            <?php if (count($grouped_fields) > 0) { ?>
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div class="w98-inset" style="background:#fff;padding:8px;margin-top:8px;">
                <div class="w98-section-header"><?php echo htmlspecialchars($category); ?></div>
                <?php foreach ($fields as $field) { ?>
                <div style="margin-bottom:8px;">
                    <div style="font-size:11px;color:#808080;font-weight:bold;margin-bottom:2px;"><?php echo htmlspecialchars($field['pf_name']); ?></div>
                    <div style="font-size:12px;color:#000;padding-left:8px;">
                        <?php echo mg_render_profile_value($field); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php } ?>

            <!-- 캐릭터 관계 -->
            <?php if (!empty($char_relations)) { ?>
            <div class="w98-inset" style="background:#fff;padding:8px;margin-top:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <div class="w98-section-header" style="margin-bottom:0;">관계</div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <?php if ($is_owner) { ?>
                        <button type="button" id="rel-graph-save" style="font-size:11px;color:#808080;background:none;border:none;cursor:pointer;font-family:inherit;display:none;">배치 저장</button>
                        <?php } ?>
                        <button type="button" id="rel-graph-toggle" class="w98-btn" style="font-size:11px;padding:2px 8px;cursor:pointer;font-family:inherit;">관계도 보기</button>
                    </div>
                </div>
                <?php foreach ($char_relations as $rel) {
                    $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                    $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                    $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                    $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                    $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                    $rel_color = $rel['cr_color'] ?: '#95a5a6';
                ?>
                <div class="w98-rel-item" style="display:flex;align-items:center;gap:8px;padding:4px 0;border-bottom:1px solid #c0c0c0;">
                    <?php if ($other_thumb) { ?>
                    <div class="w98-inset" style="padding:1px;flex-shrink:0;">
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:24px;height:24px;object-fit:cover;display:block;">
                    </div>
                    <?php } else { ?>
                    <div class="w98-inset" style="width:28px;height:28px;display:flex;align-items:center;justify-content:center;color:#808080;font-size:11px;flex-shrink:0;background:#c0c0c0;">?</div>
                    <?php } ?>
                    <span style="width:8px;height:8px;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;border:1px solid #000;"></span>
                    <span style="color:#808080;font-size:12px;"><?php echo $my_label; ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;color:#0000ff;font-size:12px;"><?php echo $other_name; ?></a>
                </div>
                <?php } ?>
                <!-- 인라인 관계도 -->
                <div id="rel-graph-wrap" class="hidden" style="margin-top:8px;">
                    <div id="rel-graph-container" class="w98-inset" style="height:400px;background:#fff;"></div>
                </div>
            </div>
            <?php } ?>

            <!-- 상태바 -->
            <div class="w98-inset" style="background:#c0c0c0;padding:2px 8px;margin-top:8px;display:flex;justify-content:space-between;font-size:11px;color:#000;">
                <span>1개 개체 선택됨</span>
                <span>내 컴퓨터</span>
            </div>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@import url('https://cdn.jsdelivr.net/gh/neodgm/neodgm-webfont@latest/neodgm/style.css');

.skin-win98 {
    font-family: 'NeoDunggeunmo', 'Gulim', sans-serif;
    font-size: 12px;
    color: #000;
}
.skin-win98 a { color: #0000ff; text-decoration: none; }
.skin-win98 a:hover { text-decoration: underline; }

.w98-window {
    background: #c0c0c0;
    border-top: 2px solid #ffffff;
    border-left: 2px solid #ffffff;
    border-bottom: 2px solid #000000;
    border-right: 2px solid #000000;
    box-shadow: inset -1px -1px #808080, inset 1px 1px #dfdfdf;
    padding: 2px;
}
.w98-titlebar {
    background: linear-gradient(90deg, #000080, #1084d0);
    padding: 4px 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #fff;
    font-weight: bold;
    font-size: 12px;
    margin-bottom: 8px;
}
.w98-icon {
    width: 16px;
    height: 16px;
    background: #fff;
    border-top: 2px solid #ffffff;
    border-left: 2px solid #ffffff;
    border-bottom: 2px solid #000000;
    border-right: 2px solid #000000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 8px;
    color: #000;
}
.w98-btn {
    background: #c0c0c0;
    border-top: 2px solid #ffffff;
    border-left: 2px solid #ffffff;
    border-bottom: 2px solid #000000;
    border-right: 2px solid #000000;
    font-family: inherit;
}
.w98-btn:active {
    border-top: 2px solid #000000;
    border-left: 2px solid #000000;
    border-bottom: 2px solid #ffffff;
    border-right: 2px solid #ffffff;
    padding-top: 2px;
    padding-left: 2px;
}
.w98-inset {
    border-top: 2px solid #808080;
    border-left: 2px solid #808080;
    border-bottom: 2px solid #ffffff;
    border-right: 2px solid #ffffff;
    box-shadow: inset -1px -1px #dfdfdf, inset 1px 1px #000000;
}
.w98-outset {
    border-top: 2px solid #ffffff;
    border-left: 2px solid #ffffff;
    border-bottom: 2px solid #000000;
    border-right: 2px solid #000000;
}
.w98-section-header {
    font-weight: bold;
    font-size: 13px;
    margin-bottom: 8px;
    border-bottom: 1px dashed #808080;
    padding-bottom: 4px;
}

/* 호버 효과 */
.w98-window { transition: box-shadow 0.3s ease; }
.w98-window:hover { box-shadow: inset -1px -1px #808080, inset 1px 1px #dfdfdf, 0 4px 12px rgba(0,0,0,0.2); }

.w98-rel-item { transition: background-color 0.2s ease; }
.w98-rel-item:hover { background-color: #000080; }
.w98-rel-item:hover span, .w98-rel-item:hover a { color: #fff !important; }
</style>
