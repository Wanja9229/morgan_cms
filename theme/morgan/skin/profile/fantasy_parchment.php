<?php
/**
 * Morgan Edition - 프로필 스킨: 길드 모험가 프로필
 * 양피지 질감, Cinzel Decorative + Crimson Pro, 금색 장식, 중세 고급 디자인
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_initial = mb_substr($char['ch_name'], 0, 1);
$ch_date_roman = date('d.m.Y', strtotime($char['ch_datetime']));
?>

<link href="https://fonts.googleapis.com/css2?family=Cinzel+Decorative:wght@400;700;900&family=Cinzel:wght@400;600;700;900&family=Crimson+Pro:ital,wght@0,400;0,600;0,700;1,400;1,600&display=swap" rel="stylesheet">

<style>
.skin-parchment {
    font-family: 'Crimson Pro', Georgia, 'Noto Serif KR', serif;
    color: #3d2b1f;
    line-height: 1.7;
}
.skin-parchment .fp-display { font-family: 'Cinzel Decorative', serif; }
.skin-parchment .fp-heading { font-family: 'Cinzel', serif; }
.skin-parchment a { color: #8b1a1a; }
.skin-parchment a:hover { color: #5c0e0e; text-decoration: underline; }

/* 양피지 컨테이너 */
.skin-parchment .fp-scroll {
    max-width: 52rem;
    margin: 0 auto;
    background:
        radial-gradient(ellipse at 20% 50%, rgba(244,228,193,0.8), transparent 70%),
        radial-gradient(ellipse at 80% 50%, rgba(232,214,180,0.6), transparent 70%),
        radial-gradient(ellipse at 50% 0%, rgba(210,185,140,0.3), transparent 60%),
        linear-gradient(180deg, #efe0c6 0%, #f4e4c1 15%, #f7ecd5 50%, #f4e4c1 85%, #e8d6b4 100%);
    position: relative;
    border-radius: 4px;
    box-shadow:
        0 0 0 1px #c5a55a,
        0 0 0 4px #f4e4c1,
        0 0 0 5px #c5a55a,
        0 4px 20px rgba(61,43,31,0.3),
        inset 0 0 60px rgba(139,110,70,0.08);
}

/* 코너 장식 */
.skin-parchment .fp-corner {
    position: absolute; width: 3rem; height: 3rem; z-index: 2;
    color: #c5a55a; font-size: 1.75rem; line-height: 1;
    display: flex; align-items: center; justify-content: center;
    opacity: 0.7;
}
.skin-parchment .fp-corner-tl { top: 0.5rem; left: 0.75rem; }
.skin-parchment .fp-corner-tr { top: 0.5rem; right: 0.75rem; }
.skin-parchment .fp-corner-bl { bottom: 0.5rem; left: 0.75rem; }
.skin-parchment .fp-corner-br { bottom: 0.5rem; right: 0.75rem; }

/* 금색 구분선 */
.skin-parchment .fp-rule {
    display: flex; align-items: center; justify-content: center;
    margin: 1.75rem 0; gap: 0.75rem;
}
.skin-parchment .fp-rule::before, .skin-parchment .fp-rule::after {
    content: ''; flex: 1; height: 0;
    border-top: 1px solid #c5a55a;
    border-bottom: 1px solid rgba(197,165,90,0.3);
}
.skin-parchment .fp-rule-icon {
    color: #c5a55a; font-size: 1.25rem; flex-shrink: 0;
}

/* 초상화 프레임 */
.skin-parchment .fp-portrait-frame {
    position: relative;
    display: inline-block;
    padding: 6px;
    background: linear-gradient(135deg, #d4af37, #c5a55a, #b8962e, #c5a55a, #d4af37);
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(61,43,31,0.3), inset 0 1px 2px rgba(255,255,255,0.3);
}
.skin-parchment .fp-portrait-frame::before {
    content: '';
    position: absolute; inset: 3px;
    border: 1px solid rgba(197,165,90,0.6);
    border-radius: 50%;
    pointer-events: none;
}
.skin-parchment .fp-portrait-inner {
    width: 180px; height: 180px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #f4e4c1;
}
.skin-parchment .fp-portrait-inner img {
    width: 100%; height: 100%; object-fit: cover;
    filter: sepia(15%) contrast(105%);
}

/* 왁스 씰 — 하단 우측, 불규칙 인장 */
.skin-parchment .fp-wax-wrap {
    position: absolute;
    bottom: 1.25rem;
    right: 2.5rem;
    z-index: 5;
    transform: rotate(-8deg);
}
.skin-parchment .fp-wax {
    width: 5.5rem; height: 5.5rem;
    background:
        radial-gradient(ellipse at 30% 25%, rgba(255,120,120,0.25), transparent 50%),
        radial-gradient(ellipse at 70% 65%, rgba(100,20,20,0.3), transparent 50%),
        radial-gradient(circle at 40% 40%, #d63031, #c0392b 35%, #a93226 60%, #7b241c 85%, #641e16);
    border-radius: 47% 53% 49% 51% / 52% 48% 54% 46%;
    display: flex; align-items: center; justify-content: center;
    box-shadow:
        0 4px 14px rgba(0,0,0,0.45),
        0 1px 3px rgba(0,0,0,0.3),
        inset 0 -3px 6px rgba(0,0,0,0.3),
        inset 0 3px 6px rgba(255,200,200,0.12),
        inset 2px 0 4px rgba(0,0,0,0.15),
        inset -2px 0 4px rgba(0,0,0,0.1);
    position: relative;
    cursor: default;
}
/* 외곽 불규칙 테두리 */
.skin-parchment .fp-wax::before {
    content: ''; position: absolute;
    inset: 3px 4px 5px 3px;
    border: 1.5px solid rgba(255,255,255,0.12);
    border-radius: 50% 46% 52% 48% / 48% 52% 46% 50%;
}
/* 눌린 자국 (내부 원) */
.skin-parchment .fp-wax::after {
    content: ''; position: absolute;
    inset: 10px 11px 12px 10px;
    border: 1px solid rgba(0,0,0,0.15);
    border-radius: 52% 48% 50% 50% / 46% 54% 48% 52%;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.2), inset 0 -1px 2px rgba(255,200,200,0.08);
}
.skin-parchment .fp-wax-logo {
    width: 2.25rem; height: 2.25rem;
    object-fit: contain;
    filter: brightness(1.5) contrast(0.7) sepia(0.3);
    opacity: 0.55;
    mix-blend-mode: overlay;
    position: relative;
    z-index: 1;
}
/* 로고 없을 때 이니셜 대체 */
.skin-parchment .fp-wax-initial {
    color: rgba(253,228,228,0.45);
    font-size: 1.5rem;
    font-weight: 900;
    text-shadow: 0 1px 2px rgba(0,0,0,0.35), 0 -1px 1px rgba(255,200,200,0.1);
    position: relative;
    z-index: 1;
}
/* 왁스 방울 장식 (불규칙 흘러내림) */
.skin-parchment .fp-wax-drip {
    position: absolute;
    background: radial-gradient(ellipse at 50% 30%, #c0392b, #a93226 60%, #7b241c);
    border-radius: 40% 60% 55% 45% / 50% 50% 60% 40%;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.skin-parchment .fp-wax-drip-1 {
    width: 14px; height: 18px;
    bottom: -8px; left: 12px;
    transform: rotate(15deg);
}
.skin-parchment .fp-wax-drip-2 {
    width: 10px; height: 12px;
    bottom: -5px; right: 16px;
    transform: rotate(-10deg);
}
.skin-parchment .fp-wax-drip-3 {
    width: 8px; height: 10px;
    top: -4px; right: 8px;
    transform: rotate(25deg);
}

/* 필드 테이블 */
.skin-parchment .fp-field-table {
    width: 100%;
    border-collapse: collapse;
}
.skin-parchment .fp-field-table td {
    padding: 0.625rem 0.75rem;
    border-bottom: 1px solid rgba(197,165,90,0.25);
    vertical-align: top;
}
.skin-parchment .fp-field-table tr:last-child td { border-bottom: none; }
.skin-parchment .fp-field-label {
    width: 120px; font-weight: 600;
    color: #8b6914;
    font-size: 0.875rem;
    letter-spacing: 0.02em;
}
.skin-parchment .fp-field-value {
    font-size: 1rem;
    color: #3d2b1f;
}

/* 관계 카드 */
.skin-parchment .fp-rel-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.625rem 0;
    border-bottom: 1px solid rgba(197,165,90,0.2);
}
.skin-parchment .fp-rel-item:last-child { border-bottom: none; }

/* 업적 배지 */
.skin-parchment .fp-badge {
    display: inline-flex; align-items: center; gap: 0.375rem;
    background: rgba(197,165,90,0.12);
    border: 1px solid rgba(197,165,90,0.35);
    border-radius: 9999px;
    padding: 0.25rem 0.75rem;
    font-size: 0.8125rem;
    color: #8b6914;
}

/* 호버 효과 */
.skin-parchment .fp-portrait-frame { transition: all 0.4s ease; }
.skin-parchment .fp-portrait-frame:hover { transform: scale(1.03); box-shadow: 0 0 25px rgba(197,165,90,0.4), 0 4px 15px rgba(61,43,31,0.3); }

.skin-parchment .fp-wax-wrap { transition: transform 0.5s ease; }
.skin-parchment .fp-wax-wrap:hover { transform: rotate(-2deg) scale(1.08); }
.skin-parchment .fp-wax { transition: box-shadow 0.4s ease; }
.skin-parchment .fp-wax-wrap:hover .fp-wax { box-shadow: 0 6px 20px rgba(0,0,0,0.5), 0 2px 6px rgba(0,0,0,0.3), inset 0 -3px 6px rgba(0,0,0,0.3), inset 0 3px 6px rgba(255,200,200,0.15), inset 2px 0 4px rgba(0,0,0,0.15), inset -2px 0 4px rgba(0,0,0,0.1); }

.skin-parchment .fp-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-parchment .fp-rel-item:hover { background: rgba(197,165,90,0.08); border-left-color: #c5a55a; }

.skin-parchment .fp-field-table tr { transition: background 0.25s ease; }
.skin-parchment .fp-field-table tr:hover { background: rgba(197,165,90,0.06); }

.skin-parchment .fp-badge { transition: all 0.25s ease; }
.skin-parchment .fp-badge:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgba(197,165,90,0.3); }

.skin-parchment .fp-corner { transition: transform 2s ease; }
@keyframes fp-corner-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
.skin-parchment .fp-corner { animation: fp-corner-spin 20s linear infinite; }

/* 헤더 배너 */
.skin-parchment [style*="border:3px double #c5a55a"] img { transition: transform 0.6s ease; }
.skin-parchment [style*="border:3px double #c5a55a"]:hover img { transform: scale(1.03); }

/* 구분선 glow */
.skin-parchment .fp-rule-icon { transition: all 0.3s ease; }
.skin-parchment .fp-rule:hover .fp-rule-icon { text-shadow: 0 0 10px rgba(197,165,90,0.6); transform: scale(1.2); }
</style>

<div class="mg-inner skin-parchment">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#8b6914;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div class="fp-scroll">
        <!-- 코너 장식 -->
        <div class="fp-corner fp-corner-tl">&#10050;</div>
        <div class="fp-corner fp-corner-tr">&#10050;</div>
        <div class="fp-corner fp-corner-bl">&#10050;</div>
        <div class="fp-corner fp-corner-br">&#10050;</div>

        <!-- 헤더 배너 -->
        <?php if ($char_header) { ?>
        <div style="margin:1.5rem 2rem 0;border:3px double #c5a55a;overflow:hidden;max-height:12rem;border-radius:2px;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:sepia(25%) contrast(105%);">
        </div>
        <?php } ?>

        <!-- 상단 장식 라인 -->
        <div style="padding:1.5rem 2rem 0;">
            <div style="border-top:2px solid #c5a55a;border-bottom:1px solid rgba(197,165,90,0.4);height:4px;"></div>
        </div>

        <!-- 타이틀 영역 -->
        <div style="text-align:center;padding:1.5rem 2rem 0;">
            <div class="fp-display" style="font-size:0.75rem;letter-spacing:0.3em;color:#c5a55a;text-transform:uppercase;margin-bottom:0.5rem;">~ Guild Registry ~</div>
            <h1 class="fp-display" style="font-size:2.25rem;font-weight:900;margin:0;color:#3d2b1f;text-shadow:1px 1px 0 rgba(197,165,90,0.3);">
                <?php echo $ch_name; ?>
            </h1>
            <div style="font-size:1.0625rem;color:#6d4c2a;font-style:italic;margin-top:0.375rem;">
                <?php
                $subtitle_parts = array();
                if ($ch_class && mg_config('use_class', '1') == '1') $subtitle_parts[] = $ch_class;
                if ($ch_side && mg_config('use_side', '1') == '1') $subtitle_parts[] = $ch_side;
                echo $subtitle_parts ? implode(' &bull; ', $subtitle_parts) : 'Adventurer';
                ?>
            </div>

            <!-- 액션 버튼 -->
            <div style="display:flex;gap:0.5rem;justify-content:center;margin-top:1rem;">
                <?php if ($can_request_relation) { ?>
                <button type="button" onclick="openRelRequestModal()" style="font-size:0.8125rem;background:linear-gradient(180deg,#c0392b,#a93226);color:#fdf6e3;border:1px solid #922b21;padding:0.375rem 1.25rem;border-radius:2px;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.2);" class="fp-heading">관계 신청</button>
                <?php } ?>
                <?php if ($is_owner) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.8125rem;background:rgba(197,165,90,0.15);border:1px solid #c5a55a;color:#8b6914;padding:0.375rem 1.25rem;border-radius:2px;text-decoration:none;" class="fp-heading">수정</a>
                <?php } ?>
            </div>
        </div>

        <div class="fp-rule" style="padding:0 2rem;"><span class="fp-rule-icon">&#9876;</span></div>

        <!-- 초상화 + 기본정보 -->
        <div style="padding:0 2rem;">
            <div style="display:flex;gap:2rem;align-items:flex-start;flex-wrap:wrap;justify-content:center;" class="fp-main-area">
                <!-- 초상화 -->
                <div style="text-align:center;flex-shrink:0;">
                    <?php if ($char_image) { ?>
                    <div class="fp-portrait-frame">
                        <div class="fp-portrait-inner">
                            <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>">
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="fp-portrait-frame">
                        <div class="fp-portrait-inner" style="display:flex;align-items:center;justify-content:center;background:rgba(197,165,90,0.1);">
                            <span class="fp-display" style="font-size:3.5rem;color:#c5a55a;"><?php echo $ch_initial; ?></span>
                        </div>
                    </div>
                    <?php } ?>

                    <div style="margin-top:1rem;font-size:0.8125rem;color:#8b6914;" class="fp-heading">
                        @<?php echo $ch_owner; ?>
                    </div>
                </div>

                <!-- 기본정보 테이블 -->
                <div style="flex:1;min-width:240px;">
                    <h3 class="fp-heading" style="font-size:0.8125rem;letter-spacing:0.15em;text-transform:uppercase;color:#c5a55a;margin-bottom:0.75rem;border-bottom:1px solid rgba(197,165,90,0.3);padding-bottom:0.375rem;">Record</h3>
                    <table class="fp-field-table">
                        <tbody>
                            <tr>
                                <td class="fp-field-label fp-heading">이름</td>
                                <td class="fp-field-value" style="font-weight:700;"><?php echo $ch_name; ?></td>
                            </tr>
                            <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                            <tr>
                                <td class="fp-field-label fp-heading"><?php echo htmlspecialchars(mg_config('class_title', '종족')); ?></td>
                                <td class="fp-field-value"><?php echo $ch_class; ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                            <tr>
                                <td class="fp-field-label fp-heading"><?php echo htmlspecialchars(mg_config('side_title', '세력')); ?></td>
                                <td class="fp-field-value"><?php echo $ch_side; ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td class="fp-field-label fp-heading">등록일</td>
                                <td class="fp-field-value"><?php echo $ch_date_roman; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 업적 -->
        <?php if (!empty($achievement_showcase)) { ?>
        <div class="fp-rule" style="padding:0 2rem;"><span class="fp-rule-icon">&#9733;</span></div>
        <div style="padding:0 2rem;">
            <h3 class="fp-heading" style="text-align:center;font-size:0.8125rem;letter-spacing:0.15em;text-transform:uppercase;color:#c5a55a;margin-bottom:1rem;">Honors &amp; Achievements</h3>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                ?>
                <span class="fp-badge" title="<?php echo $a_name; ?>">
                    <?php if ($a_icon) { ?>
                    <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:16px;height:16px;object-fit:contain;">
                    <?php } else { ?>
                    <span style="color:#c5a55a;">&#9733;</span>
                    <?php } ?>
                    <?php echo $a_name; ?>
                </span>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <!-- 프로필 필드 -->
        <?php if (count($grouped_fields) > 0) { ?>
        <?php foreach ($grouped_fields as $category => $fields) { ?>
        <div class="fp-rule" style="padding:0 2rem;"><span class="fp-rule-icon">&#10087;</span></div>
        <div style="padding:0 2rem;">
            <h3 class="fp-heading" style="text-align:center;font-size:1.125rem;font-weight:700;color:#3d2b1f;margin-bottom:1rem;"><?php echo htmlspecialchars($category); ?></h3>
            <table class="fp-field-table">
                <tbody>
                    <?php foreach ($fields as $field) { ?>
                    <tr>
                        <td class="fp-field-label fp-heading"><?php echo htmlspecialchars($field['pf_name']); ?></td>
                        <td class="fp-field-value">
                            <?php
                            if ($field['pf_type'] == 'url') {
                                echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank">'.htmlspecialchars($field['pv_value']).'</a>';
                            } elseif ($field['pf_type'] == 'textarea') {
                                echo nl2br(htmlspecialchars($field['pv_value']));
                            } else {
                                echo htmlspecialchars($field['pv_value']);
                            }
                            ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
        <?php } ?>

        <!-- 관계 -->
        <?php if (!empty($char_relations)) { ?>
        <div class="fp-rule" style="padding:0 2rem;"><span class="fp-rule-icon">&#9876;</span></div>
        <div style="padding:0 2rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 class="fp-heading" style="font-size:1.125rem;font-weight:700;color:#3d2b1f;">관계</h3>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#8b6914;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.8125rem;color:#8b1a1a;background:none;border:none;cursor:pointer;text-decoration:underline;" class="fp-heading">관계도 보기</button>
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
            <div class="fp-rel-item">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #c5a55a;filter:sepia(10%);">
                <?php } else { ?>
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(197,165,90,0.12);display:flex;align-items:center;justify-content:center;color:#c5a55a;font-weight:bold;border:2px solid #c5a55a;font-size:0.8125rem;" class="fp-heading"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#6d4c2a;font-style:italic;font-size:0.9375rem;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:700;" class="fp-heading"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
                <div id="rel-graph-container" style="height:400px;background:#2c1e16;border:1px solid rgba(197,165,90,0.3);border-radius:2px;"></div>
            </div>
        </div>
        <?php } ?>

        <!-- 푸터 -->
        <div style="padding:1.5rem 2rem 0;">
            <div style="border-top:2px solid #c5a55a;border-bottom:1px solid rgba(197,165,90,0.4);height:4px;"></div>
        </div>
        <div style="text-align:center;padding:1rem 2rem 2.5rem;position:relative;">
            <div class="fp-heading" style="font-size:0.75rem;letter-spacing:0.2em;color:#c5a55a;text-transform:uppercase;">
                Sealed &amp; Recorded &mdash; <?php echo $ch_date_roman; ?>
            </div>
        </div>

        <!-- 왁스 실링 인장 -->
        <?php $seal_logo = function_exists('mg_config') ? mg_config('site_logo') : ''; ?>
        <div class="fp-wax-wrap">
            <div class="fp-wax">
                <?php if ($seal_logo) { ?>
                <img src="<?php echo htmlspecialchars($seal_logo); ?>" alt="" class="fp-wax-logo">
                <?php } else { ?>
                <span class="fp-display fp-wax-initial"><?php echo $ch_initial; ?></span>
                <?php } ?>
                <div class="fp-wax-drip fp-wax-drip-1"></div>
                <div class="fp-wax-drip fp-wax-drip-2"></div>
                <div class="fp-wax-drip fp-wax-drip-3"></div>
            </div>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (min-width: 640px) {
    .skin-parchment .fp-main-area { flex-wrap: nowrap !important; }
    .skin-parchment .fp-field-label { width: 140px; }
}
@media (max-width: 639px) {
    .skin-parchment .fp-scroll { margin: 0 -0.5rem; }
    .skin-parchment .fp-portrait-inner { width: 140px; height: 140px; }
    .skin-parchment .fp-corner { display: none; }
    .skin-parchment .fp-wax-wrap { right: 1.25rem; bottom: 0.75rem; }
    .skin-parchment .fp-wax { width: 4.5rem; height: 4.5rem; }
    .skin-parchment .fp-wax-logo { width: 1.75rem; height: 1.75rem; }
}
</style>
