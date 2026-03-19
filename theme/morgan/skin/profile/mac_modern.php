<?php
/**
 * Morgan Edition - 프로필 스킨: macOS 모던
 * 클린 화이트 테마, 트래픽 라이트 버튼, 부드러운 그림자
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

<div class="mg-inner skin-mac" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:13px;color:#999;margin-bottom:12px;text-decoration:none;">
        <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
        <span>뒤로</span>
    </a>

    <!-- 메인 윈도우 -->
    <div class="mac-window">
        <!-- 타이틀바 -->
        <div class="mac-titlebar">
            <div style="display:flex;gap:8px;width:25%;">
                <div style="width:12px;height:12px;border-radius:50%;background:#ff5f56;border:1px solid #e0443e;"></div>
                <div style="width:12px;height:12px;border-radius:50%;background:#ffbd2e;border:1px solid #dea123;"></div>
                <div style="width:12px;height:12px;border-radius:50%;background:#27c93f;border:1px solid #1aab29;"></div>
            </div>
            <div style="width:50%;display:flex;justify-content:center;">
                <div style="background:#fff;padding:3px 16px;border-radius:6px;font-size:11px;color:#999;font-weight:500;letter-spacing:0.02em;box-shadow:0 1px 2px rgba(0,0,0,0.06);display:flex;align-items:center;gap:4px;">
                    &#128274; morgan-builder.com/<?php echo $ch_owner; ?>
                </div>
            </div>
            <div style="width:25%;"></div>
        </div>

        <!-- 헤더 배너 -->
        <?php if ($char_header) { ?>
        <div style="max-height:180px;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;object-fit:cover;">
        </div>
        <?php } ?>

        <!-- 콘텐츠 영역 -->
        <div style="padding:32px;">
            <!-- 프로필 헤더 -->
            <div style="display:flex;align-items:center;gap:24px;margin-bottom:32px;">
                <div class="mac-avatar">
                    <?php if ($char_image) { ?>
                    <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                    <?php } else { ?>
                    <span style="font-size:24px;font-weight:600;color:#fff;"><?php echo $ch_initial; ?></span>
                    <?php } ?>
                </div>
                <div style="flex:1;">
                    <!-- 배지 -->
                    <div style="margin-bottom:4px;">
                        <?php if ($char['ch_main']) { ?>
                        <span style="display:inline-block;background:#f0f0ff;color:#5856d6;font-size:11px;padding:2px 10px;border-radius:12px;font-weight:600;border:1px solid #e0e0ff;">대표</span>
                        <?php } ?>
                        <?php
                        $state_labels = array(
                            'editing' => array('수정중', '#8e8e93', '#f2f2f7'),
                            'pending' => array('승인대기', '#ff9500', '#fff8f0'),
                            'approved' => array('승인됨', '#34c759', '#f0fff4'),
                        );
                        $state = $state_labels[$char['ch_state']] ?? array('', '', '');
                        if ($state[0]) { ?>
                        <span style="display:inline-block;background:<?php echo $state[2]; ?>;color:<?php echo $state[1]; ?>;font-size:11px;padding:2px 10px;border-radius:12px;font-weight:600;border:1px solid <?php echo $state[1]; ?>22;"><?php echo $state[0]; ?></span>
                        <?php } ?>
                    </div>
                    <h2 style="font-size:28px;font-weight:800;color:#1d1d1f;letter-spacing:-0.02em;margin:0 0 4px;"><?php echo $ch_name; ?></h2>
                    <div style="font-size:14px;color:#86868b;font-weight:500;margin-bottom:6px;">
                        <?php
                        $parts = array();
                        if ($ch_class && mg_config('use_class', '1') == '1') $parts[] = $ch_class;
                        if ($ch_side && mg_config('use_side', '1') == '1') $parts[] = $ch_side;
                        echo $parts ? implode(' &middot; ', $parts) : '';
                        ?>
                    </div>
                    <span style="display:inline-block;background:#f5f5f7;color:#5856d6;font-size:12px;padding:4px 12px;border-radius:12px;font-weight:700;border:1px solid #e8e8ed;">
                        @<?php echo $ch_owner; ?> &middot; <?php echo $ch_date; ?>
                    </span>
                </div>
            </div>

            <!-- 액션 버튼 -->
            <div style="display:flex;gap:8px;margin-bottom:24px;">
                <?php if ($can_request_relation) { ?>
                <button type="button" onclick="openRelRequestModal()" class="mac-btn-primary">
                    <i data-lucide="link" style="width:14px;height:14px;"></i>
                    관계 신청
                </button>
                <?php } ?>
                <?php if ($is_owner) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="mac-btn-secondary">
                    <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                    수정
                </a>
                <?php } ?>
            </div>

            <!-- 전투 능력치 -->
            <?php if ($_battle_use == '1' && $battle_stat) {
                $_stat_base = (int)mg_config('battle_stat_base', '5');
                $_bs_hp = (int)($battle_stat['stat_hp'] ?? $_stat_base);
                $_bs_str = (int)($battle_stat['stat_str'] ?? $_stat_base);
                $_bs_dex = (int)($battle_stat['stat_dex'] ?? $_stat_base);
                $_bs_int = (int)($battle_stat['stat_int'] ?? $_stat_base);
                $_bs_stress = (int)($battle_stat['stat_stress'] ?? 0);
                $_stress_color = $_bs_stress >= 100 ? '#ff3b30' : ($_bs_stress >= 70 ? '#ff9500' : '#34c759');
                $_stat_max = max($_bs_hp, $_bs_str, $_bs_dex, $_bs_int, 1);
            ?>
            <?php if ($battle_hp && $battle_hp['max_hp'] > 0) {
                $_hp_pct = round($battle_hp['current_hp'] / $battle_hp['max_hp'] * 100);
                $_hp_color = $_hp_pct > 60 ? '#34c759' : ($_hp_pct > 30 ? '#ff9500' : '#ff3b30');
            ?>
            <div class="mac-card" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;color:#1d1d1f;margin-bottom:6px;">
                    <span>HP</span>
                    <span style="color:<?php echo $_hp_color; ?>;"><?php echo $battle_hp['current_hp']; ?> / <?php echo $battle_hp['max_hp']; ?></span>
                </div>
                <div class="mac-bar">
                    <div class="mac-bar-fill" style="width:<?php echo $_hp_pct; ?>%;background:<?php echo $_hp_color; ?>;"></div>
                </div>
            </div>
            <?php } ?>

            <div class="mac-card" style="margin-bottom:16px;">
                <div class="mac-card-title">Combat Stats</div>
                <div style="display:flex;flex-direction:column;gap:16px;">
                    <?php
                    $mac_stats = array(
                        array('label' => 'HP / 체력', 'val' => $_bs_hp, 'color' => '#ff3b30'),
                        array('label' => 'STR / 힘', 'val' => $_bs_str, 'color' => '#ff9500'),
                        array('label' => 'DEX / 민첩', 'val' => $_bs_dex, 'color' => '#34c759'),
                        array('label' => 'INT / 지능', 'val' => $_bs_int, 'color' => '#007aff'),
                    );
                    foreach ($mac_stats as $ms) {
                        $bar_pct = round(($ms['val'] / $_stat_max) * 100);
                    ?>
                    <div>
                        <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:700;color:#1d1d1f;margin-bottom:6px;">
                            <span><?php echo $ms['label']; ?></span>
                            <span style="color:<?php echo $ms['color']; ?>;"><?php echo $ms['val']; ?></span>
                        </div>
                        <div class="mac-bar">
                            <div class="mac-bar-fill" style="width:<?php echo $bar_pct; ?>%;background:<?php echo $ms['color']; ?>;"></div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <!-- 스트레스 -->
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f2f2f7;">
                    <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:600;color:#1d1d1f;margin-bottom:6px;">
                        <span>스트레스</span>
                        <span style="color:<?php echo $_stress_color; ?>;"><?php echo $_bs_stress; ?>/100</span>
                    </div>
                    <div class="mac-bar">
                        <div class="mac-bar-fill" style="width:<?php echo min(100, $_bs_stress); ?>%;background:<?php echo $_stress_color; ?>;"></div>
                    </div>
                </div>
            </div>
            <?php } ?>

            <!-- 업적 쇼케이스 -->
            <?php if (!empty($achievement_showcase)) {
                $ach_rarity_colors = array(
                    'common' => '#8e8e93', 'uncommon' => '#34c759', 'rare' => '#007aff',
                    'epic' => '#af52de', 'legendary' => '#ff9500',
                );
                $ach_rarity_labels = array(
                    'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare',
                    'epic' => 'Epic', 'legendary' => 'Legendary',
                );
            ?>
            <div class="mac-card" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div class="mac-card-title" style="margin-bottom:0;">업적 쇼케이스</div>
                    <a href="<?php echo G5_BBS_URL; ?>/achievement.php" style="font-size:12px;color:#007aff;font-weight:500;">전체보기</a>
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
                    <?php foreach ($achievement_showcase as $acd) {
                        $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                        $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                        $a_rarity = $acd['ac_rarity'] ?: 'common';
                        $a_color = $ach_rarity_colors[$a_rarity] ?? '#8e8e93';
                    ?>
                    <div class="mac-badge" style="border-color:<?php echo $a_color; ?>33;" title="<?php echo $a_name; ?>">
                        <?php if ($a_icon) { ?>
                        <img src="<?php echo htmlspecialchars($a_icon); ?>" alt="<?php echo $a_name; ?>" style="width:32px;height:32px;object-fit:contain;">
                        <?php } else { ?>
                        <span style="font-size:24px;">&#9733;</span>
                        <?php } ?>
                        <span style="font-size:11px;color:#1d1d1f;text-align:center;max-width:64px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;display:block;font-weight:500;"><?php echo $a_name; ?></span>
                        <span style="font-size:10px;color:<?php echo $a_color; ?>;font-weight:600;"><?php echo $ach_rarity_labels[$a_rarity] ?? ''; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 프로필 필드 -->
            <?php if (count($grouped_fields) > 0) { ?>
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div class="mac-card" style="margin-bottom:16px;">
                <div class="mac-card-title"><?php echo htmlspecialchars($category); ?></div>
                <?php foreach ($fields as $idx => $field) { ?>
                <div style="<?php if ($idx > 0) echo 'padding-top:12px;margin-top:12px;border-top:1px solid #f2f2f7;'; ?>">
                    <div style="font-size:12px;font-weight:600;color:#86868b;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.03em;"><?php echo htmlspecialchars($field['pf_name']); ?></div>
                    <div style="font-size:14px;color:#1d1d1f;">
                        <?php echo mg_render_profile_value($field); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php } ?>

            <!-- 캐릭터 관계 -->
            <?php if (!empty($char_relations)) { ?>
            <div class="mac-card" style="margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <div class="mac-card-title" style="margin-bottom:0;">관계</div>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <?php if ($is_owner) { ?>
                        <button type="button" id="rel-graph-save" style="font-size:12px;color:#86868b;background:none;border:none;cursor:pointer;font-family:inherit;display:none;">배치 저장</button>
                        <?php } ?>
                        <button type="button" id="rel-graph-toggle" style="font-size:12px;color:#007aff;background:none;border:none;cursor:pointer;font-family:inherit;font-weight:500;">관계도 보기</button>
                    </div>
                </div>
                <?php foreach ($char_relations as $idx => $rel) {
                    $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                    $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                    $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                    $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                    $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                    $my_memo = $is_a ? ($rel['cr_memo_a'] ?? '') : ($rel['cr_memo_b'] ?? '');
                    $rel_color = $rel['cr_color'] ?: '#95a5a6';
                ?>
                <div class="mac-rel-item" style="display:flex;align-items:center;gap:10px;padding:8px 0;<?php if ($idx > 0) echo 'border-top:1px solid #f2f2f7;'; ?>">
                    <?php if ($other_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                    <?php } else { ?>
                    <div style="width:32px;height:32px;border-radius:50%;background:#f2f2f7;display:flex;align-items:center;justify-content:center;color:#86868b;font-size:12px;flex-shrink:0;">?</div>
                    <?php } ?>
                    <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:13px;color:#86868b;"><?php echo $my_label; ?></span>
                            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-size:14px;font-weight:600;color:#007aff;"><?php echo $other_name; ?></a>
                        </div>
                        <?php if ($my_memo) { ?>
                        <p style="font-size:11px;color:#aeaeb2;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($my_memo); ?></p>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <!-- 인라인 관계도 -->
                <div id="rel-graph-wrap" class="hidden" style="margin-top:12px;">
                    <div id="rel-graph-container" style="height:400px;background:#fafafa;border-radius:8px;border:1px solid #e8e8ed;"></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
.skin-mac {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    font-size: 14px;
    color: #1d1d1f;
}
.skin-mac a { color: #007aff; text-decoration: none; }
.skin-mac a:hover { text-decoration: underline; }

.mac-window {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.08);
}
.mac-titlebar {
    background: rgba(246,246,246,0.8);
    padding: 12px 16px;
    display: flex;
    align-items: center;
    border-bottom: 1px solid #e8e8ed;
}
.mac-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5856d6, #af52de);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(88,86,214,0.3);
}
.mac-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #007aff;
    color: #fff;
    border: none;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.2s, transform 0.2s;
    text-decoration: none;
}
.mac-btn-primary:hover { background: #0066d6; transform: translateY(-1px); color: #fff; text-decoration: none; }
.mac-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f2f2f7;
    color: #1d1d1f;
    border: 1px solid #e8e8ed;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: background 0.2s, transform 0.2s;
    text-decoration: none;
}
.mac-btn-secondary:hover { background: #e8e8ed; transform: translateY(-1px); color: #1d1d1f; text-decoration: none; }
.mac-card {
    background: #fff;
    border: 1px solid #e8e8ed;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.mac-card-title {
    font-size: 11px;
    font-weight: 700;
    color: #86868b;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f2f2f7;
}
.mac-bar {
    width: 100%;
    height: 8px;
    background: #f2f2f7;
    border-radius: 4px;
    overflow: hidden;
}
.mac-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}
.mac-badge {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid #e8e8ed;
    min-width: 72px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.mac-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

/* 호버 효과 */
.mac-window { transition: box-shadow 0.3s ease; }
.mac-window:hover { box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.3); }

.mac-card { transition: border-color 0.3s ease, box-shadow 0.3s ease; }
.mac-card:hover { border-color: #007aff33; box-shadow: 0 2px 8px rgba(0,122,255,0.06); }

.mac-rel-item { transition: background-color 0.2s ease; border-radius: 8px; padding-left: 4px; padding-right: 4px; }
.mac-rel-item:hover { background-color: #f5f5f7; }
</style>
