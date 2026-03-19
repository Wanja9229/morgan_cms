<?php
/**
 * Morgan Edition - 프로필 스킨: MS-DOS 터미널
 * 블랙 배경, 모노스페이스, 깜빡이는 커서, 부팅 시퀀스
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
?>

<div class="mg-inner skin-dos" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:12px;color:#808080;margin-bottom:8px;text-decoration:none;">
        <i data-lucide="chevron-left" style="width:14px;height:14px;"></i>
        <span>&lt; BACK</span>
    </a>

    <div class="dos-screen">
        <!-- 부팅 시퀀스 -->
        <div style="margin-bottom:16px;">
            <div>MS-DOS Version 6.22</div>
            <div style="margin-bottom:12px;">(C)Copyright Microsoft Corp 1981-1994.</div>
            <div>C:\MORGAN&gt; cd BUILDER</div>
            <div style="margin-bottom:8px;">C:\MORGAN\BUILDER&gt; profile.exe /user:<?php echo $ch_name; ?></div>
        </div>

        <div style="color:#ffffff;margin-bottom:16px;">
            Loading Morgan Builder Profile System...<br>
            [OK] User Data Loaded.<br>
            <?php if ($_battle_use == '1' && $battle_stat) { ?>
            [OK] Combat System Initialized.<br>
            <?php } ?>
            [OK] Profile Render Complete.
        </div>

        <!-- 헤더 배너 -->
        <?php if ($char_header) { ?>
        <div style="margin-bottom:12px;border:1px solid #333;max-height:140px;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;object-fit:cover;filter:contrast(110%) grayscale(30%);">
        </div>
        <?php } ?>

        <!-- 프로필 헤더 -->
        <div style="margin-bottom:4px;">
            <div class="dos-separator">=</div>
            <div style="text-align:center;font-size:16px;font-weight:bold;color:#ffffff;margin:4px 0;">[ USER PROFILE : <?php echo $ch_name; ?> ]</div>
            <div class="dos-separator">=</div>
        </div>

        <!-- 프로필 이미지 + 기본 정보 -->
        <div style="display:flex;gap:16px;margin:12px 0;">
            <?php if ($char_image) { ?>
            <div style="border:1px solid #333;flex-shrink:0;">
                <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:80px;height:80px;object-fit:cover;display:block;filter:contrast(120%);">
            </div>
            <?php } ?>
            <div style="flex:1;">
                <div style="margin-bottom:4px;">
                    <?php if ($char['ch_main']) { ?>
                    <span style="color:#ffff00;">[MAIN]</span>
                    <?php } ?>
                    <?php
                    $state_labels = array(
                        'editing' => array('EDITING', '#808080'),
                        'pending' => array('PENDING', '#ffff00'),
                        'approved' => array('APPROVED', '#00ff00'),
                    );
                    $state = $state_labels[$char['ch_state']] ?? array('', '');
                    if ($state[0]) { ?>
                    <span style="color:<?php echo $state[1]; ?>;">[<?php echo $state[0]; ?>]</span>
                    <?php } ?>
                </div>
                <p>NAME  : <span style="color:#ffffff;"><?php echo $ch_name; ?></span></p>
                <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                <p>CLASS : <span style="color:#ffffff;"><?php echo $ch_class; ?></span></p>
                <?php } ?>
                <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                <p>GUILD : <span style="color:#ffffff;"><?php echo $ch_side; ?></span></p>
                <?php } ?>
                <p>OWNER : <span style="color:#ffffff;">@<?php echo $ch_owner; ?></span></p>
                <p>DATE  : <span style="color:#ffffff;"><?php echo $ch_date; ?></span></p>
            </div>
        </div>

        <!-- 액션 -->
        <div style="margin-bottom:16px;">
            <?php if ($can_request_relation) { ?>
            <span>C:\&gt; </span><a href="javascript:void(0);" onclick="openRelRequestModal()" style="color:#00ff00;">relation_request.exe</a><br>
            <?php } ?>
            <?php if ($is_owner) { ?>
            <span>C:\&gt; </span><a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="color:#ffff00;">edit_profile.exe</a><br>
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
            $_stress_color = $_bs_stress >= 100 ? '#ff4444' : ($_bs_stress >= 70 ? '#ffff00' : '#00ff00');
        ?>
        <?php if ($battle_hp && $battle_hp['max_hp'] > 0) {
            $_hp_pct = round($battle_hp['current_hp'] / $battle_hp['max_hp'] * 100);
            $_hp_color = $_hp_pct > 60 ? '#00ff00' : ($_hp_pct > 30 ? '#ffff00' : '#ff4444');
        ?>
        <div style="margin-bottom:12px;">
            <div style="color:#ffffff;margin-bottom:4px;">--- HP STATUS ---</div>
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="color:<?php echo $_hp_color; ?>;width:28px;">[HP]</span>
                <div class="dos-bar">
                    <div class="dos-bar-fill" style="width:<?php echo $_hp_pct; ?>%;background:<?php echo $_hp_color; ?>;"></div>
                </div>
                <span style="color:<?php echo $_hp_color; ?>;"><?php echo $battle_hp['current_hp']; ?> / <?php echo $battle_hp['max_hp']; ?></span>
            </div>
        </div>
        <?php } ?>

        <div style="margin-bottom:16px;">
            <div style="color:#ffffff;margin-bottom:8px;">--- COMBAT STATS ---</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px 32px;">
                <div>[HP]  <span style="color:#ff6666;"><?php echo str_pad($_bs_hp, 3, '0', STR_PAD_LEFT); ?></span></div>
                <div>[STR] <span style="color:#ffff00;"><?php echo str_pad($_bs_str, 3, '0', STR_PAD_LEFT); ?></span></div>
                <div>[DEX] <span style="color:#00ff00;"><?php echo str_pad($_bs_dex, 3, '0', STR_PAD_LEFT); ?></span></div>
                <div>[INT] <span style="color:#6666ff;"><?php echo str_pad($_bs_int, 3, '0', STR_PAD_LEFT); ?></span></div>
            </div>
            <!-- 스트레스 -->
            <div style="margin-top:8px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <span style="color:<?php echo $_stress_color; ?>;width:36px;">[STS]</span>
                    <div class="dos-bar">
                        <div class="dos-bar-fill" style="width:<?php echo min(100, $_bs_stress); ?>%;background:<?php echo $_stress_color; ?>;"></div>
                    </div>
                    <span style="color:<?php echo $_stress_color; ?>;"><?php echo $_bs_stress; ?>/100</span>
                </div>
            </div>
        </div>
        <div class="dos-separator">=</div>
        <?php } ?>

        <!-- 업적 쇼케이스 -->
        <?php if (!empty($achievement_showcase)) {
            $ach_rarity_colors = array(
                'common' => '#808080', 'uncommon' => '#00ff00', 'rare' => '#6666ff',
                'epic' => '#ff00ff', 'legendary' => '#ffff00',
            );
        ?>
        <div style="margin:12px 0;">
            <div style="color:#ffffff;margin-bottom:8px;">--- ACHIEVEMENTS ---</div>
            <?php foreach ($achievement_showcase as $acd) {
                $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                $a_rarity = $acd['ac_rarity'] ?: 'common';
                $a_color = $ach_rarity_colors[$a_rarity] ?? '#808080';
            ?>
            <div style="margin-bottom:2px;">
                <span style="color:<?php echo $a_color; ?>;">[*]</span> <?php echo $a_name; ?>
            </div>
            <?php } ?>
            <div style="margin-top:4px;">
                <a href="<?php echo G5_BBS_URL; ?>/achievement.php" style="color:#00ff00;">&gt; VIEW ALL</a>
            </div>
        </div>
        <div class="dos-separator">=</div>
        <?php } ?>

        <!-- 프로필 필드 -->
        <?php if (count($grouped_fields) > 0) { ?>
        <?php foreach ($grouped_fields as $category => $fields) { ?>
        <div style="margin:12px 0;">
            <div style="color:#ffffff;margin-bottom:8px;">--- <?php echo strtoupper(htmlspecialchars($category)); ?> ---</div>
            <?php foreach ($fields as $field) { ?>
            <div style="margin-bottom:6px;">
                <div style="color:#00ffff;">&gt; <?php echo htmlspecialchars($field['pf_name']); ?></div>
                <div style="padding-left:16px;color:#c0c0c0;">
                    <?php echo mg_render_profile_value($field); ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <div class="dos-separator">=</div>
        <?php } ?>

        <!-- 캐릭터 관계 -->
        <?php if (!empty($char_relations)) { ?>
        <div style="margin:12px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                <span style="color:#ffffff;">--- RELATIONS ---</span>
                <div style="display:flex;gap:8px;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:12px;color:#808080;background:none;border:none;cursor:pointer;font-family:inherit;display:none;">SAVE LAYOUT</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:12px;color:#00ff00;background:none;border:none;cursor:pointer;font-family:inherit;">GRAPH &gt;</button>
                </div>
            </div>
            <?php foreach ($char_relations as $rel) {
                $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                $my_memo = $is_a ? ($rel['cr_memo_a'] ?? '') : ($rel['cr_memo_b'] ?? '');
                $rel_color = $rel['cr_color'] ?: '#95a5a6';
            ?>
            <div class="dos-rel-item" style="display:flex;align-items:center;gap:8px;padding:2px 0;border-bottom:1px solid #333;">
                <?php if ($other_thumb) { ?>
                <div style="border:1px solid #333;flex-shrink:0;">
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:20px;height:20px;object-fit:cover;display:block;filter:contrast(120%);">
                </div>
                <?php } else { ?>
                <div style="width:22px;height:22px;border:1px solid #333;display:flex;align-items:center;justify-content:center;color:#808080;font-size:11px;flex-shrink:0;">?</div>
                <?php } ?>
                <span style="color:<?php echo htmlspecialchars($rel_color); ?>;">&#9608;</span>
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="color:#808080;"><?php echo $my_label; ?></span>
                        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;color:#00ff00;"><?php echo $other_name; ?></a>
                    </div>
                    <?php if ($my_memo) { ?>
                    <p style="font-size:0.75rem;color:#555;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($my_memo); ?></p>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:8px;">
                <div id="rel-graph-container" style="height:400px;background:#000;border:1px solid #333;"></div>
            </div>
        </div>
        <?php } ?>

        <!-- 커서 -->
        <div style="margin-top:16px;display:flex;align-items:center;">
            <span>C:\MORGAN\BUILDER&gt; </span><span class="dos-cursor"></span>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@import url('https://cdn.jsdelivr.net/gh/neodgm/neodgm-webfont@latest/neodgm/style.css');

.skin-dos {
    font-family: 'NeoDunggeunmo', 'Courier New', monospace;
    font-size: 13px;
    color: #c0c0c0;
    line-height: 1.6;
}
.skin-dos a { color: #00ff00; text-decoration: none; }
.skin-dos a:hover { color: #ffffff; text-shadow: 0 0 8px #00ff00; }

.dos-screen {
    background: #000;
    padding: 20px;
    border: 2px solid #333;
    box-shadow: 0 0 20px rgba(0,0,0,0.8), inset 0 0 60px rgba(0,255,0,0.02);
}
.dos-separator {
    color: #ffffff;
    letter-spacing: 0.2em;
    overflow: hidden;
    white-space: nowrap;
}
.dos-separator::after {
    content: '====================================================================================================';
}
.dos-bar {
    flex: 1;
    height: 12px;
    background: #111;
    border: 1px solid #333;
    overflow: hidden;
}
.dos-bar-fill {
    height: 100%;
    transition: width 0.3s ease;
}
.dos-cursor {
    display: inline-block;
    width: 10px;
    height: 16px;
    background-color: #c0c0c0;
    animation: dos-blink 1s step-end infinite;
    vertical-align: bottom;
    margin-left: 4px;
}
@keyframes dos-blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0; }
}

/* 호버 효과 */
.dos-screen { transition: box-shadow 0.3s ease; }
.dos-screen:hover { box-shadow: 0 0 30px rgba(0,255,0,0.08), inset 0 0 60px rgba(0,255,0,0.03); }

.dos-rel-item { transition: background-color 0.2s ease, padding-left 0.2s ease; }
.dos-rel-item:hover { background-color: rgba(0,255,0,0.05); padding-left: 8px; }
</style>
