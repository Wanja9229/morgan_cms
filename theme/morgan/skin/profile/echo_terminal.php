<?php
/**
 * Morgan Edition - 프로필 스킨: ECHO-4 택티컬 터미널
 * SF 다크 테마, 네온 시안/옐로우, 셀셰이딩 테두리, 스캔라인
 *
 * 사용 가능한 변수:
 * $char, $grouped_fields, $achievement_showcase, $char_relations,
 * $is_owner, $can_request_relation, $my_approved_characters, $profile_skin_id
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_name_upper = strtoupper($ch_name);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);
?>

<div class="mg-inner skin-echo" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:14px;color:#8c9eff;margin-bottom:8px;text-decoration:none;">
        <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
        <span>&lt; BACK</span>
    </a>

    <!-- 메인 프레임 -->
    <div class="echo-frame">
        <!-- 헤더 바 -->
        <div class="echo-header">
            <h1 class="echo-neon" style="font-size:24px;font-weight:700;margin:0;line-height:1;">ECHO-4 TERMINAL</h1>
            <span style="font-size:16px;color:#ffca28;">v.4.0.1</span>
        </div>

        <!-- 스크린 영역 -->
        <div class="echo-screen">
            <div class="echo-scanlines"></div>

            <div class="echo-content">
                <!-- 헤더 배너 -->
                <?php if ($char_header) { ?>
                <div style="margin:-20px -20px 16px;max-height:160px;overflow:hidden;border-bottom:2px solid #3949ab;">
                    <img src="<?php echo $char_header; ?>" alt="" style="width:100%;object-fit:cover;filter:contrast(110%) saturate(120%);">
                </div>
                <?php } ?>

                <!-- 프로필 헤더 -->
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:16px;border-bottom:2px solid #3949ab;padding-bottom:12px;">
                    <div style="flex:1;">
                        <!-- 배지 -->
                        <div style="margin-bottom:4px;">
                            <?php if ($char['ch_main']) { ?>
                            <span style="color:#ffca28;font-size:14px;">[MAIN]</span>
                            <?php } ?>
                            <?php
                            $state_labels = array(
                                'editing' => array('EDITING', '#8c9eff'),
                                'pending' => array('PENDING', '#ffca28'),
                                'approved' => array('ONLINE', '#69f0ae'),
                            );
                            $state = $state_labels[$char['ch_state']] ?? array('', '');
                            if ($state[0]) { ?>
                            <span style="color:<?php echo $state[1]; ?>;font-size:14px;">[<?php echo $state[0]; ?>]</span>
                            <?php } ?>
                        </div>
                        <p style="color:#00ffff;font-size:18px;margin:0;line-height:1;">ID: <?php echo $ch_owner; ?></p>
                        <h2 style="font-size:42px;font-weight:700;color:#fff;margin:4px 0;line-height:1;letter-spacing:0.05em;"><?php echo $ch_name; ?></h2>
                        <p style="color:#ffca28;font-size:20px;margin:0;">
                            <?php
                            $parts = array();
                            if ($ch_class && mg_config('use_class', '1') == '1') $parts[] = $ch_class;
                            if ($ch_side && mg_config('use_side', '1') == '1') $parts[] = $ch_side;
                            echo $parts ? implode(' | ', $parts) : 'REGISTERED: '.$ch_date;
                            ?>
                        </p>
                    </div>
                    <!-- 프로필 이미지 -->
                    <div style="width:64px;height:64px;background:#000;border:2px solid #00ffff;display:flex;align-items:center;justify-content:center;flex-shrink:0;transform:rotate(3deg);overflow:hidden;">
                        <?php if ($char_image) { ?>
                        <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php } else { ?>
                        <span style="color:#00ffff;font-size:28px;font-weight:700;"><?php echo $ch_initial; ?></span>
                        <?php } ?>
                    </div>
                </div>

                <!-- 시스템 정보 -->
                <div style="display:flex;justify-content:space-between;color:#8c9eff;font-size:16px;margin-bottom:16px;">
                    <span>SYS: MORGAN_CMS</span>
                    <span>DATE: <?php echo $ch_date; ?></span>
                </div>

                <!-- 액션 -->
                <div style="display:flex;gap:8px;margin-bottom:16px;">
                    <?php if ($can_request_relation) { ?>
                    <button type="button" onclick="openRelRequestModal()" class="echo-btn-primary">
                        <i data-lucide="link" style="width:14px;height:14px;"></i>
                        LINK REQUEST
                    </button>
                    <?php } ?>
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="echo-btn-secondary">
                        <i data-lucide="pencil" style="width:14px;height:14px;"></i>
                        EDIT
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
                    $_stress_color = $_bs_stress >= 100 ? '#ff5252' : ($_bs_stress >= 70 ? '#ffca28' : '#69f0ae');
                    $_stat_max = max($_bs_hp, $_bs_str, $_bs_dex, $_bs_int, 1);

                    $echo_stats = array(
                        array('label' => 'HP', 'val' => $_bs_hp, 'color' => '#ff5252'),
                        array('label' => 'STR', 'val' => $_bs_str, 'color' => '#ff9100'),
                        array('label' => 'DEX', 'val' => $_bs_dex, 'color' => '#69f0ae'),
                        array('label' => 'INT', 'val' => $_bs_int, 'color' => '#00ffff'),
                    );
                ?>
                <?php if ($battle_hp && $battle_hp['max_hp'] > 0) {
                    $_hp_pct = round($battle_hp['current_hp'] / $battle_hp['max_hp'] * 100);
                    $_hp_color = $_hp_pct > 60 ? '#69f0ae' : ($_hp_pct > 30 ? '#ffca28' : '#ff5252');
                ?>
                <div style="margin-bottom:12px;">
                    <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:700;color:#fff;margin-bottom:4px;">
                        <span>HP</span>
                        <span style="color:<?php echo $_hp_color; ?>;"><?php echo $battle_hp['current_hp']; ?> / <?php echo $battle_hp['max_hp']; ?></span>
                    </div>
                    <div class="echo-bar">
                        <div class="echo-bar-fill" style="width:<?php echo $_hp_pct; ?>%;background:<?php echo $_hp_color; ?>;"></div>
                    </div>
                </div>
                <?php } ?>

                <div style="margin-bottom:16px;">
                    <?php foreach ($echo_stats as $es) {
                        $bar_pct = round(($es['val'] / $_stat_max) * 100);
                    ?>
                    <div style="margin-bottom:8px;">
                        <div style="display:flex;justify-content:space-between;font-size:20px;font-weight:700;color:#fff;margin-bottom:4px;">
                            <span><?php echo $es['label']; ?></span>
                            <span style="color:<?php echo $es['color']; ?>;"><?php echo str_pad($es['val'], 3, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        <div class="echo-bar">
                            <div class="echo-bar-fill" style="width:<?php echo $bar_pct; ?>%;background:<?php echo $es['color']; ?>;"></div>
                        </div>
                    </div>
                    <?php } ?>
                    <!-- 스트레스 -->
                    <div style="margin-top:12px;">
                        <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:700;color:#fff;margin-bottom:4px;">
                            <span>STRESS</span>
                            <span style="color:<?php echo $_stress_color; ?>;"><?php echo $_bs_stress; ?>/100</span>
                        </div>
                        <div class="echo-bar">
                            <div class="echo-bar-fill" style="width:<?php echo min(100, $_bs_stress); ?>%;background:<?php echo $_stress_color; ?>;"></div>
                        </div>
                    </div>
                </div>
                <?php } ?>

                <!-- 업적 쇼케이스 -->
                <?php if (!empty($achievement_showcase)) {
                    $ach_rarity_colors = array(
                        'common' => '#8c9eff', 'uncommon' => '#69f0ae', 'rare' => '#00ffff',
                        'epic' => '#ff5252', 'legendary' => '#ffca28',
                    );
                ?>
                <div style="border-top:2px solid #3949ab;padding-top:12px;margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="color:#ffca28;font-size:18px;font-weight:700;">ACHIEVEMENTS</span>
                        <a href="<?php echo G5_BBS_URL; ?>/achievement.php" style="color:#00ffff;font-size:14px;">VIEW ALL &gt;</a>
                    </div>
                    <div style="display:flex;gap:8px;flex-wrap:wrap;">
                        <?php foreach ($achievement_showcase as $acd) {
                            $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                            $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                            $a_rarity = $acd['ac_rarity'] ?: 'common';
                            $a_color = $ach_rarity_colors[$a_rarity] ?? '#8c9eff';
                        ?>
                        <div class="echo-badge" style="border-color:<?php echo $a_color; ?>;" title="<?php echo $a_name; ?>">
                            <?php if ($a_icon) { ?>
                            <img src="<?php echo htmlspecialchars($a_icon); ?>" alt="<?php echo $a_name; ?>" style="width:20px;height:20px;object-fit:contain;">
                            <?php } else { ?>
                            <span style="color:<?php echo $a_color; ?>;">&#9733;</span>
                            <?php } ?>
                            <span style="font-size:12px;color:#fff;max-width:60px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo $a_name; ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <!-- 프로필 필드 -->
                <?php if (count($grouped_fields) > 0) { ?>
                <?php foreach ($grouped_fields as $category => $fields) { ?>
                <div style="border-top:2px solid #3949ab;padding-top:12px;margin-bottom:16px;">
                    <div style="color:#00ffff;font-size:18px;font-weight:700;margin-bottom:8px;">[<?php echo strtoupper(htmlspecialchars($category)); ?>]</div>
                    <?php foreach ($fields as $field) { ?>
                    <div style="margin-bottom:8px;">
                        <div style="color:#ffca28;font-size:14px;font-weight:500;margin-bottom:2px;">&gt; <?php echo htmlspecialchars($field['pf_name']); ?></div>
                        <div style="color:#e0e0e0;font-size:14px;padding-left:12px;">
                            <?php echo mg_render_profile_value($field); ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php } ?>

                <!-- 캐릭터 관계 -->
                <?php if (!empty($char_relations)) { ?>
                <div style="border-top:2px solid #3949ab;padding-top:12px;margin-bottom:16px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                        <span style="color:#ff5252;font-size:18px;font-weight:700;">LINKED UNITS</span>
                        <div style="display:flex;gap:8px;align-items:center;">
                            <?php if ($is_owner) { ?>
                            <button type="button" id="rel-graph-save" style="font-size:12px;color:#8c9eff;background:none;border:none;cursor:pointer;font-family:inherit;display:none;">SAVE LAYOUT</button>
                            <?php } ?>
                            <button type="button" id="rel-graph-toggle" style="font-size:12px;color:#00ffff;background:none;border:none;cursor:pointer;font-family:inherit;font-weight:500;">GRAPH &gt;</button>
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
                    <div class="echo-rel-item" style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #3949ab44;">
                        <?php if ($other_thumb) { ?>
                        <div style="border:1px solid #00ffff;flex-shrink:0;overflow:hidden;">
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:24px;height:24px;object-fit:cover;display:block;">
                        </div>
                        <?php } else { ?>
                        <div style="width:26px;height:26px;border:1px solid #3949ab;display:flex;align-items:center;justify-content:center;color:#8c9eff;font-size:12px;flex-shrink:0;">?</div>
                        <?php } ?>
                        <span style="width:8px;height:8px;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;box-shadow:0 0 4px <?php echo htmlspecialchars($rel_color); ?>;"></span>
                        <span style="color:#8c9eff;font-size:14px;"><?php echo $my_label; ?></span>
                        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;color:#00ffff;font-size:14px;font-weight:500;"><?php echo $other_name; ?></a>
                    </div>
                    <?php } ?>
                    <!-- 인라인 관계도 -->
                    <div id="rel-graph-wrap" class="hidden" style="margin-top:8px;">
                        <div id="rel-graph-container" style="height:400px;background:#0a0a2e;border:2px solid #3949ab;"></div>
                    </div>
                </div>
                <?php } ?>

                <!-- 시스템 온라인 -->
                <div style="text-align:center;color:#ffca28;font-size:18px;margin-top:16px;animation:echo-pulse 2s ease-in-out infinite;">
                    &gt;&gt; SYSTEM ONLINE &lt;&lt;
                </div>
            </div>
        </div>

        <!-- 바코드 푸터 -->
        <div style="margin-top:8px;display:flex;justify-content:space-between;align-items:center;padding:0 4px;">
            <div style="display:flex;gap:2px;height:20px;align-items:flex-end;">
                <div style="width:2px;height:100%;background:#000;"></div>
                <div style="width:4px;height:100%;background:#000;"></div>
                <div style="width:2px;height:100%;background:#000;"></div>
                <div style="width:6px;height:100%;background:#000;"></div>
                <div style="width:2px;height:100%;background:#000;"></div>
                <div style="width:4px;height:100%;background:#000;"></div>
            </div>
            <span style="font-weight:700;font-size:18px;letter-spacing:0.1em;color:#000;">SN: <?php echo str_pad($char['ch_id'], 4, '0', STR_PAD_LEFT); ?>-V</span>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Teko:wght@400;500;700&display=swap');

.skin-echo {
    font-family: 'Teko', sans-serif;
    font-size: 16px;
    color: #e0e0e0;
    letter-spacing: 0.05em;
}
.skin-echo a { color: #00ffff; text-decoration: none; }
.skin-echo a:hover { color: #fff; text-shadow: 0 0 8px #00ffff; }

.echo-frame {
    background: #ffca28;
    border: 4px solid #000;
    box-shadow: 8px 8px 0px #000;
    padding: 8px;
    position: relative;
    clip-path: polygon(0 0, 100% 0, 100% calc(100% - 30px), calc(100% - 30px) 100%, 0 100%);
    transition: transform 0.3s ease;
}
.echo-frame:hover {
    transform: rotate(0deg);
}
.echo-header {
    background: #000;
    color: #00ffff;
    padding: 8px 16px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-bottom: 4px solid #000;
    margin-bottom: 8px;
}
.echo-neon {
    text-shadow: 0 0 5px rgba(0, 255, 255, 0.8);
}
.echo-screen {
    background: #1a237e;
    border: 4px solid #000;
    position: relative;
    overflow: hidden;
}
.echo-scanlines {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(255,255,255,0),
        rgba(255,255,255,0) 50%,
        rgba(0,0,0,0.15) 50%,
        rgba(0,0,0,0.15)
    );
    background-size: 100% 4px;
    pointer-events: none;
    z-index: 10;
}
.echo-content {
    padding: 20px;
    position: relative;
    z-index: 0;
}
.echo-bar {
    width: 100%;
    height: 22px;
    background: #000;
    border: 2px solid #000;
    overflow: hidden;
}
.echo-bar-fill {
    height: 100%;
    transition: width 0.5s ease;
}
.echo-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #00ffff;
    color: #000;
    border: 2px solid #000;
    padding: 4px 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    letter-spacing: 0.05em;
    text-decoration: none;
    transition: background 0.2s, box-shadow 0.2s;
}
.echo-btn-primary:hover { background: #69f0ae; box-shadow: 0 0 10px rgba(0,255,255,0.5); color: #000; text-decoration: none; }
.echo-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    color: #8c9eff;
    border: 2px solid #3949ab;
    padding: 4px 12px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    font-family: inherit;
    letter-spacing: 0.05em;
    text-decoration: none;
    transition: background 0.2s, color 0.2s;
}
.echo-btn-secondary:hover { background: #3949ab; color: #fff; text-decoration: none; }
.echo-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: 1px solid #3949ab;
    background: rgba(0,0,0,0.3);
    padding: 3px 8px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.echo-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 0 8px rgba(0,255,255,0.3);
}

/* 호버 효과 */
.echo-screen { transition: box-shadow 0.3s ease; }
.echo-screen:hover { box-shadow: inset 0 0 30px rgba(0,255,255,0.05); }

.echo-rel-item { transition: background-color 0.2s ease, padding-left 0.2s ease; }
.echo-rel-item:hover { background-color: rgba(0,255,255,0.05); padding-left: 8px; }

@keyframes echo-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
</style>
