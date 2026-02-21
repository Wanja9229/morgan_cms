<?php
/**
 * Morgan Edition - 프로필 스킨: 아케이드 게임
 * 네온/레트로, 픽셀 느낌, HP바, 스탯 표시
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);
/* 장식용 랜덤 스탯 (미사용 — 주석 처리)
$stat_seed = crc32($char['ch_name'] . $char['ch_id']);
mt_srand($stat_seed);
$stats = array(
    'STR' => mt_rand(30, 99), 'DEX' => mt_rand(30, 99), 'INT' => mt_rand(30, 99),
    'WIS' => mt_rand(30, 99), 'CHA' => mt_rand(30, 99), 'LUK' => mt_rand(30, 99),
);
mt_srand();
*/
?>

<link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">

<style>
.skin-arcade { font-family: 'Press Start 2P', monospace; color: #e0e0e0; font-size: 0.625rem; line-height: 1.8; }
.skin-arcade a { color: #00ff88; text-decoration: none; }
.skin-arcade a:hover { color: #ffffff; text-shadow: 0 0 8px #00ff88; }
.skin-arcade .arc-frame {
    background: #0a0a0a;
    border: 4px solid #333;
    border-radius: 0.5rem;
    box-shadow: 0 0 0 2px #000, 0 0 30px rgba(0,255,136,0.1);
    image-rendering: pixelated;
}
.skin-arcade .arc-header {
    background: linear-gradient(180deg, #1a1a2e, #0a0a0a);
    border-bottom: 2px solid #333;
    padding: 1rem 1.5rem;
    text-align: center;
}
.skin-arcade .arc-neon { color: #00ff88; text-shadow: 0 0 10px #00ff88, 0 0 20px rgba(0,255,136,0.5); }
.skin-arcade .arc-neon-pink { color: #ff0080; text-shadow: 0 0 10px #ff0080, 0 0 20px rgba(255,0,128,0.5); }
.skin-arcade .arc-neon-cyan { color: #00e5ff; text-shadow: 0 0 10px #00e5ff; }
.skin-arcade .arc-neon-yellow { color: #ffe600; text-shadow: 0 0 10px #ffe600; }
.skin-arcade .arc-bar { height: 12px; background: #1a1a1a; border: 1px solid #333; border-radius: 2px; overflow: hidden; }
.skin-arcade .arc-bar-fill { height: 100%; transition: width 0.3s; }
.skin-arcade .arc-pixel-border { border: 2px solid #333; background: #111; }
.skin-arcade .arc-blink { animation: arc-blink 1s step-end infinite; }
@keyframes arc-blink { 50% { opacity: 0; } }

/* 호버 효과 */
.skin-arcade .arc-frame { transition: box-shadow 0.5s ease; }
.skin-arcade .arc-frame:hover { box-shadow: 0 0 0 2px #000, 0 0 50px rgba(0,255,136,0.2); }

.skin-arcade .arc-pixel-border { transition: all 0.3s ease; }
.skin-arcade .arc-pixel-border:hover { box-shadow: 0 0 20px rgba(0,255,136,0.4); transform: scale(1.03); }

.skin-arcade .arc-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; }
.skin-arcade .arc-rel-item:hover { background: rgba(0,255,136,0.05); border-left-color: #00ff88; }

.skin-arcade .arc-badge { transition: all 0.25s ease; }
.skin-arcade .arc-badge:hover { box-shadow: 0 0 10px rgba(255,230,0,0.4); transform: scale(1.05); border-color: #ffe600; }

/* 버튼 호버 강화 */
.skin-arcade button[style*="background:#00ff88"]:hover { box-shadow: 0 0 20px rgba(0,255,136,0.5); transform: translateY(-1px); }

/* GAME OVER 호버 */
.skin-arcade .arc-blink { transition: color 0.3s ease; }
</style>

<div class="mg-inner skin-arcade">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.625rem;color:#666;margin-bottom:1rem;">
        <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>&lt; BACK</span>
    </a>

    <div class="arc-frame" style="max-width:40rem;margin:0 auto;">
        <!-- 헤더 -->
        <div class="arc-header">
            <div style="font-size:0.5rem;color:#666;margin-bottom:0.5rem;">- INSERT COIN -</div>
            <h1 class="arc-neon" style="font-size:1rem;letter-spacing:0.1em;margin:0;"><?php echo $ch_name; ?></h1>
            <div style="color:#666;margin-top:0.5rem;">
                <?php
                $parts = array();
                if ($ch_class && mg_config('use_class', '1') == '1') $parts[] = $ch_class;
                if ($ch_side && mg_config('use_side', '1') == '1') $parts[] = $ch_side;
                echo $parts ? implode(' / ', $parts) : 'PLAYER: @'.$ch_owner;
                ?>
            </div>
        </div>

        <?php if ($char_header) { ?>
        <div style="max-height:10rem;overflow:hidden;border-bottom:2px solid #333;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:contrast(120%) saturate(130%);">
        </div>
        <?php } ?>

        <div style="padding:1.5rem;">
            <!-- 캐릭터 -->
            <div style="text-align:center;">
                <div class="arc-pixel-border" style="display:inline-block;padding:4px;">
                    <?php if ($char_image) { ?>
                    <img src="<?php echo $char_image; ?>" alt="" style="width:200px;height:260px;object-fit:cover;display:block;image-rendering:auto;">
                    <?php } else { ?>
                    <div style="width:200px;height:260px;background:#1a1a2e;display:flex;align-items:center;justify-content:center;font-size:3rem;" class="arc-neon"><?php echo $ch_initial; ?></div>
                    <?php } ?>
                </div>
                <div style="margin-top:0.75rem;color:#666;">LV.<?php echo min(99, $char['ch_id'] + 10); ?> | @<?php echo $ch_owner; ?></div>

                <!-- 액션 -->
                <div style="display:flex;gap:0.5rem;justify-content:center;margin-top:0.75rem;">
                    <?php if ($can_request_relation) { ?>
                    <button type="button" onclick="openRelRequestModal()" style="background:#00ff88;color:#0a0a0a;border:2px solid #00cc6a;padding:0.375rem 0.75rem;font-family:inherit;font-size:0.5rem;cursor:pointer;">&gt; 관계 신청</button>
                    <?php } ?>
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="border:2px solid #333;padding:0.375rem 0.75rem;font-size:0.5rem;color:#666;">&gt; 수정</a>
                    <?php } ?>
                </div>
            </div>

            <!-- 스탯 바 (미사용 — 주석 처리)
            <div>
                <div style="color:#666;margin-bottom:0.75rem;text-align:center;">== STATUS ==</div>
                <?php foreach ($stats as $label => $val) {
                    $colors = array('STR' => '#ff0080', 'DEX' => '#00ff88', 'INT' => '#00e5ff', 'WIS' => '#ffe600', 'CHA' => '#ff6b00', 'LUK' => '#bf00ff');
                    $color = $colors[$label];
                ?>
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.375rem;">
                    <span style="width:2.5rem;text-align:right;color:<?php echo $color; ?>;"><?php echo $label; ?></span>
                    <div class="arc-bar" style="flex:1;">
                        <div class="arc-bar-fill" style="width:<?php echo $val; ?>%;background:<?php echo $color; ?>;"></div>
                    </div>
                    <span style="width:1.5rem;color:<?php echo $color; ?>;"><?php echo $val; ?></span>
                </div>
                <?php } ?>
            </div>
            -->

            <!-- 업적 -->
            <?php if (!empty($achievement_showcase)) { ?>
            <div style="margin-top:1.5rem;border-top:2px solid #333;padding-top:1rem;">
                <div class="arc-neon-yellow" style="margin-bottom:0.75rem;text-align:center;">== ACHIEVEMENTS ==</div>
                <div style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
                    <?php foreach ($achievement_showcase as $acd) {
                        $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                        $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                    ?>
                    <span class="arc-badge" style="display:inline-flex;align-items:center;gap:0.25rem;border:1px solid #333;background:#111;padding:0.25rem 0.5rem;font-size:0.5rem;" title="<?php echo $a_name; ?>">
                        <?php if ($a_icon) { ?>
                        <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:14px;height:14px;object-fit:contain;">
                        <?php } else { ?>
                        <span class="arc-neon-yellow">&#9733;</span>
                        <?php } ?>
                        <span style="color:#ccc;"><?php echo $a_name; ?></span>
                    </span>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 프로필 필드 -->
            <?php if (count($grouped_fields) > 0) { ?>
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div style="margin-top:1.5rem;border-top:2px solid #333;padding-top:1rem;">
                <div class="arc-neon-cyan" style="margin-bottom:0.75rem;text-align:center;">== <?php echo strtoupper(htmlspecialchars($category)); ?> ==</div>
                <?php foreach ($fields as $field) { ?>
                <div style="margin-bottom:0.75rem;">
                    <div style="color:#00e5ff;margin-bottom:0.125rem;">&gt; <?php echo htmlspecialchars($field['pf_name']); ?></div>
                    <div style="color:#ccc;padding-left:1rem;">
                        <?php echo mg_render_profile_value($field); ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php } ?>

            <!-- 관계 -->
            <?php if (!empty($char_relations)) { ?>
            <div style="margin-top:1.5rem;border-top:2px solid #333;padding-top:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                    <span class="arc-neon-pink">== PARTY ==</span>
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <?php if ($is_owner) { ?>
                        <button type="button" id="rel-graph-save" style="font-size:0.5rem;color:#666;background:none;border:none;cursor:pointer;display:none;font-family:inherit;">배치 저장</button>
                        <?php } ?>
                        <button type="button" id="rel-graph-toggle" style="font-size:0.5rem;color:#ff0080;background:none;border:none;cursor:pointer;font-family:inherit;">관계도 &gt;</button>
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
                <div class="arc-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.375rem 0;border-bottom:1px solid #222;">
                    <?php if ($other_thumb) { ?>
                    <div class="arc-pixel-border" style="padding:1px;flex-shrink:0;">
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:24px;height:24px;object-fit:cover;display:block;">
                    </div>
                    <?php } else { ?>
                    <div style="width:28px;height:28px;background:#111;border:2px solid #333;display:flex;align-items:center;justify-content:center;color:#666;flex-shrink:0;">?</div>
                    <?php } ?>
                    <span style="width:8px;height:8px;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                    <span style="color:#666;"><?php echo $my_label; ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;"><?php echo $other_name; ?></a>
                </div>
                <?php } ?>
                <!-- 인라인 관계도 -->
                <div id="rel-graph-wrap" class="hidden" style="margin-top:0.75rem;">
                    <div id="rel-graph-container" style="height:400px;background:#050510;border:2px solid #333;"></div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 푸터 -->
        <div style="border-top:2px solid #333;padding:0.75rem;text-align:center;color:#333;">
            <span class="arc-blink">&#9608;</span> GAME OVER <span class="arc-blink">&#9608;</span>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

