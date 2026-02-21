<?php
/**
 * Morgan Edition - 프로필 스킨: 길드 모험가 프로필
 * 양피지 세피아, Cinzel + Crimson Text, 왁스 씰, 이중 테두리
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_initial = mb_substr($char['ch_name'], 0, 1);
?>

<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Crimson+Text:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">

<style>
.skin-parchment {
    font-family: 'Crimson Text', Georgia, serif;
    color: #3e2723;
}
.skin-parchment .fp-cinzel { font-family: 'Cinzel Decorative', serif; }
.skin-parchment .fp-ink-bleed { text-shadow: 0 0 2px rgba(62, 39, 35, 0.2); }
.skin-parchment .fp-divider {
    display: flex; align-items: center; justify-content: center; margin: 1.5rem 0;
}
.skin-parchment .fp-divider::before, .skin-parchment .fp-divider::after {
    content: ''; flex: 1; height: 1px; background: rgba(93,64,55,0.3); margin: 0 1rem;
}
.skin-parchment a { color: #c62828; }
.skin-parchment a:hover { text-decoration: underline; }
.skin-parchment img.fp-portrait {
    filter: sepia(30%) contrast(125%);
}
</style>

<div class="mg-inner skin-parchment">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#8d6e63;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div style="max-width:56rem;margin:0 auto;background:radial-gradient(ellipse at center,#fdf6e3 0%,#f0e6d2 100%);border:12px double #8d6e63;border-radius:0.5rem;box-shadow:0 10px 30px -5px rgba(62,39,35,0.5),inset 0 0 100px -20px rgba(141,110,99,0.3);padding:2rem 2rem;position:relative;overflow:hidden;">

        <!-- 왁스 씰 -->
        <div style="position:absolute;top:0;left:50%;transform:translateX(-50%) translateY(-50%);">
            <div style="position:relative;">
                <div style="width:5rem;height:5rem;background:#c62828;border-radius:50%;border:4px solid #a52222;box-shadow:0 4px 8px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;position:relative;top:3rem;z-index:10;">
                    <span class="fp-cinzel fp-ink-bleed" style="color:#fdf6e3;font-size:1.5rem;font-weight:900;"><?php echo $ch_initial; ?></span>
                </div>
            </div>
        </div>

        <!-- 헤더 -->
        <header style="text-align:center;margin-top:4rem;margin-bottom:2.5rem;">
            <h1 class="fp-cinzel fp-ink-bleed" style="font-size:2.5rem;font-weight:900;text-transform:uppercase;margin-bottom:0.5rem;">
                <?php echo $ch_name; ?>
            </h1>
            <h2 class="fp-cinzel" style="font-size:1.125rem;color:#5d4037;font-style:italic;">
                <?php
                $subtitle_parts = array();
                if ($ch_class) $subtitle_parts[] = $ch_class;
                if ($ch_side) $subtitle_parts[] = $ch_side;
                echo implode(' &middot; ', $subtitle_parts) ?: '@'.$ch_owner;
                ?>
            </h2>

            <!-- 액션 버튼 -->
            <div style="display:flex;gap:0.5rem;justify-content:center;margin-top:1rem;">
                <?php if ($can_request_relation) { ?>
                <button type="button" onclick="openRelRequestModal()" style="font-size:0.875rem;background:#c62828;color:#fdf6e3;border:none;padding:0.375rem 1rem;border-radius:0.25rem;cursor:pointer;" class="fp-cinzel">관계 신청</button>
                <?php } ?>
                <?php if ($is_owner) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.875rem;background:rgba(62,39,35,0.1);border:1px solid #8d6e63;color:#5d4037;padding:0.375rem 1rem;border-radius:0.25rem;text-decoration:none;" class="fp-cinzel">수정</a>
                <?php } ?>
            </div>
        </header>

        <div style="display:grid;grid-template-columns:1fr;gap:2rem;" class="fp-grid">
            <!-- 좌측: 초상화 + 기본정보 -->
            <div style="display:flex;flex-direction:column;gap:1.5rem;text-align:center;">
                <?php if ($char_image) { ?>
                <div style="position:relative;width:80%;max-width:20rem;aspect-ratio:3/4;margin:0 auto;padding:0.75rem;border:4px solid rgba(93,64,55,0.5);background:rgba(62,39,35,0.1);box-shadow:inset 0 2px 4px rgba(0,0,0,0.1);border-radius:2px;">
                    <div style="width:100%;height:100%;border:2px solid rgba(62,39,35,0.8);overflow:hidden;border-radius:2px;position:relative;">
                        <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" class="fp-portrait" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                </div>
                <?php } ?>

                <div style="background:rgba(62,39,35,0.05);padding:1rem;border:1px solid rgba(62,39,35,0.2);border-radius:0.25rem;font-size:1.125rem;">
                    <table style="width:100%;">
                        <tbody>
                            <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                            <tr style="border-bottom:1px solid rgba(62,39,35,0.1);">
                                <th style="padding:0.5rem;text-align:left;color:#5d4037;" class="fp-cinzel">세력</th>
                                <td style="padding:0.5rem;text-align:right;font-weight:bold;"><?php echo $ch_side; ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                            <tr style="border-bottom:1px solid rgba(62,39,35,0.1);">
                                <th style="padding:0.5rem;text-align:left;color:#5d4037;" class="fp-cinzel">직업</th>
                                <td style="padding:0.5rem;text-align:right;font-weight:bold;"><?php echo $ch_class; ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <th style="padding:0.5rem;text-align:left;color:#5d4037;" class="fp-cinzel">작성자</th>
                                <td style="padding:0.5rem;text-align:right;">@<?php echo $ch_owner; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 우측: 프로필 필드 -->
            <div>
                <!-- 업적 쇼케이스 -->
                <?php if (!empty($achievement_showcase)) { ?>
                <div class="fp-divider"><span style="color:#bf9b30;font-size:1.5rem;">&#9876;</span></div>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;justify-content:center;margin-bottom:1.5rem;">
                    <?php foreach ($achievement_showcase as $acd) {
                        $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                        $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                    ?>
                    <div style="display:flex;flex-direction:column;align-items:center;padding:0.5rem;" title="<?php echo $a_name; ?>">
                        <?php if ($a_icon) { ?>
                        <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:40px;height:40px;object-fit:contain;">
                        <?php } ?>
                        <span style="font-size:0.75rem;color:#5d4037;margin-top:0.25rem;" class="fp-cinzel"><?php echo $a_name; ?></span>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <!-- 프로필 필드 -->
                <?php if (count($grouped_fields) > 0) { ?>
                <?php foreach ($grouped_fields as $category => $fields) { ?>
                <div class="fp-divider"><span style="color:#bf9b30;font-size:1.5rem;">&#9758;</span></div>
                <h3 class="fp-cinzel" style="font-size:1.5rem;font-weight:bold;text-align:center;margin-bottom:1rem;"><?php echo htmlspecialchars($category); ?></h3>
                <div style="font-size:1.125rem;line-height:1.8;">
                    <?php foreach ($fields as $i => $field) { ?>
                    <div style="margin-bottom:1rem;<?php echo $i === 0 ? '' : ''; ?>">
                        <strong style="color:#5d4037;" class="fp-cinzel"><?php echo htmlspecialchars($field['pf_name']); ?>:</strong>
                        <span style="margin-left:0.5rem;">
                            <?php
                            if ($field['pf_type'] == 'url') {
                                echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank">'.htmlspecialchars($field['pv_value']).'</a>';
                            } elseif ($field['pf_type'] == 'textarea') {
                                echo nl2br(htmlspecialchars($field['pv_value']));
                            } else {
                                echo htmlspecialchars($field['pv_value']);
                            }
                            ?>
                        </span>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
                <?php } ?>
            </div>
        </div>

        <!-- 관계 섹션 -->
        <?php if (!empty($char_relations)) { ?>
        <div class="fp-divider"><span style="color:#bf9b30;font-size:1.5rem;">&#9876;</span></div>
        <div style="margin-bottom:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 class="fp-cinzel" style="font-size:1.5rem;font-weight:bold;text-align:center;flex:1;">관계</h3>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#8d6e63;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.875rem;color:#c62828;background:none;border:none;cursor:pointer;text-decoration:underline;">관계도 보기</button>
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
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid rgba(62,39,35,0.1);font-size:1rem;">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #8d6e63;">
                <?php } else { ?>
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(62,39,35,0.1);display:flex;align-items:center;justify-content:center;color:#8d6e63;font-weight:bold;border:2px solid #8d6e63;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#5d4037;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:bold;"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;border-top:2px solid rgba(62,39,35,0.2);">
                <div id="rel-graph-container" style="height:400px;background:#2c1e16;border-radius:0.25rem;"></div>
            </div>
        </div>
        <?php } ?>

        <!-- 푸터 -->
        <footer style="margin-top:2rem;padding-top:2rem;border-top:2px solid rgba(62,39,35,0.2);text-align:center;position:relative;">
            <div style="color:#bf9b30;font-size:1.5rem;position:absolute;left:50%;transform:translateX(-50%);top:-0.75rem;background:#f0e6d2;padding:0 1rem;">&#9876;</div>
            <div class="fp-cinzel" style="color:#5d4037;font-size:1rem;font-style:italic;margin-bottom:0.5rem;">
                Registered at <?php echo date('Y.m.d', strtotime($char['ch_datetime'])); ?>
            </div>
        </footer>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (min-width: 768px) {
    .skin-parchment .fp-grid { grid-template-columns: 2fr 3fr !important; }
    .skin-parchment .fp-grid > div:first-child { text-align: left !important; }
}
</style>
