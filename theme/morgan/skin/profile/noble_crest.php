<?php
/**
 * Morgan Edition - 프로필 스킨: 귀족 문장
 * 다크 네이비, 금색 악센트, Heraldry, 귀족 가문 공식 문서
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_initial = mb_substr($char['ch_name'], 0, 1);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
?>

<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=EB+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&display=swap" rel="stylesheet">

<style>
.skin-noble {
    font-family: 'EB Garamond', Georgia, 'Noto Serif KR', serif;
    color: #f5f0e1;
    line-height: 1.7;
}
.skin-noble .nb-title { font-family: 'Cinzel', serif; }
.skin-noble a { color: #d4a745; }
.skin-noble a:hover { color: #f0d078; text-decoration: none; }

/* 메인 컨테이너 */
.skin-noble .nb-frame {
    max-width: 52rem;
    margin: 0 auto;
    background: linear-gradient(180deg, #0d1117 0%, #111827 30%, #0d1117 100%);
    border: 1px solid #d4a745;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 0 1px rgba(212,167,69,0.3), 0 8px 30px rgba(0,0,0,0.5);
}
.skin-noble .nb-frame::before {
    content: '';
    position: absolute; inset: 6px;
    border: 1px solid rgba(212,167,69,0.2);
    pointer-events: none; z-index: 0;
}

/* 상단 금색 바 */
.skin-noble .nb-gold-bar {
    height: 3px;
    background: linear-gradient(90deg, transparent, #d4a745 20%, #f0d078 50%, #d4a745 80%, transparent);
}

/* 문장 방패 */
.skin-noble .nb-shield {
    width: 100px; height: 120px;
    position: relative;
    margin: 0 auto;
}
.skin-noble .nb-shield-bg {
    width: 100%; height: 100%;
    background: linear-gradient(180deg, #1a1f2e, #0d1117);
    border: 2px solid #d4a745;
    border-radius: 0 0 50% 50% / 0 0 40% 40%;
    display: flex; align-items: center; justify-content: center;
    position: relative;
    box-shadow: 0 4px 15px rgba(0,0,0,0.4), inset 0 0 20px rgba(212,167,69,0.05);
}
.skin-noble .nb-shield-bg::before {
    content: '';
    position: absolute; inset: 4px;
    border: 1px solid rgba(212,167,69,0.25);
    border-radius: 0 0 50% 50% / 0 0 40% 40%;
}

/* 리본 */
.skin-noble .nb-ribbon {
    position: relative;
    background: linear-gradient(180deg, #722f37, #5a242b);
    color: #f5f0e1;
    padding: 0.375rem 2rem;
    text-align: center;
    font-size: 0.6875rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    margin: -0.75rem auto 0;
    max-width: 16rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.3);
}
.skin-noble .nb-ribbon::before, .skin-noble .nb-ribbon::after {
    content: '';
    position: absolute; top: 0; bottom: 0; width: 1rem;
    background: linear-gradient(180deg, #722f37, #5a242b);
}
.skin-noble .nb-ribbon::before {
    left: -0.75rem;
    clip-path: polygon(100% 0, 100% 100%, 0 50%);
}
.skin-noble .nb-ribbon::after {
    right: -0.75rem;
    clip-path: polygon(0 0, 0 100%, 100% 50%);
}

/* 구분선 */
.skin-noble .nb-rule {
    display: flex; align-items: center; justify-content: center;
    margin: 1.75rem 0; gap: 0.75rem; padding: 0;
}
.skin-noble .nb-rule::before, .skin-noble .nb-rule::after {
    content: ''; flex: 1; height: 0;
    border-top: 1px solid rgba(212,167,69,0.3);
}
.skin-noble .nb-rule-icon { color: #d4a745; font-size: 0.875rem; }

/* 초상화 */
.skin-noble .nb-portrait-frame {
    position: relative;
    display: inline-block;
    padding: 4px;
    background: linear-gradient(135deg, #d4a745, #b8962e, #d4a745);
    border-radius: 50%;
    box-shadow: 0 0 20px rgba(212,167,69,0.2), 0 4px 15px rgba(0,0,0,0.4);
}
.skin-noble .nb-portrait-inner {
    width: 160px; height: 160px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #0d1117;
}
.skin-noble .nb-portrait-inner img {
    width: 100%; height: 100%; object-fit: cover;
}

/* 섹션 헤더 */
.skin-noble .nb-section-title {
    font-size: 0.75rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #d4a745;
    text-align: center;
    margin-bottom: 1rem;
}

/* 필드 테이블 */
.skin-noble .nb-table {
    width: 100%;
    border-collapse: collapse;
}
.skin-noble .nb-table td {
    padding: 0.5rem 0.75rem;
    border-bottom: 1px solid rgba(212,167,69,0.1);
    vertical-align: top;
}
.skin-noble .nb-table tr:last-child td { border-bottom: none; }
.skin-noble .nb-table-label {
    width: 120px;
    color: #d4a745;
    font-size: 0.8125rem;
    font-weight: 600;
}
.skin-noble .nb-table-value {
    color: #e2dcc8;
    font-size: 1rem;
}

/* 업적 */
.skin-noble .nb-badge {
    display: inline-flex; align-items: center; gap: 0.25rem;
    border: 1px solid rgba(212,167,69,0.3);
    background: rgba(212,167,69,0.06);
    border-radius: 9999px;
    padding: 0.25rem 0.625rem;
    font-size: 0.75rem;
    color: #d4a745;
}

/* 관계 */
.skin-noble .nb-rel-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(212,167,69,0.1);
}
.skin-noble .nb-rel-item:last-child { border-bottom: none; }

/* 호버 효과 */
.skin-noble .nb-shield-bg { transition: all 0.4s ease; }
.skin-noble .nb-shield:hover .nb-shield-bg { box-shadow: 0 0 30px rgba(212,167,69,0.4), 0 4px 15px rgba(0,0,0,0.4); transform: scale(1.05); }

.skin-noble .nb-ribbon { transition: all 0.3s ease; }
.skin-noble .nb-ribbon:hover { transform: scaleX(1.05); }

.skin-noble .nb-portrait-frame { transition: all 0.4s ease; }
.skin-noble .nb-portrait-frame:hover { box-shadow: 0 0 30px rgba(212,167,69,0.35), 0 4px 15px rgba(0,0,0,0.4); transform: scale(1.05); }

.skin-noble .nb-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-noble .nb-rel-item:hover { background: rgba(212,167,69,0.06); border-left-color: #d4a745; }

.skin-noble .nb-table tr { transition: background 0.25s ease; }
.skin-noble .nb-table tr:hover { background: rgba(212,167,69,0.04); }

.skin-noble .nb-badge { transition: all 0.25s ease; }
.skin-noble .nb-badge:hover { transform: scale(1.08); box-shadow: 0 0 12px rgba(212,167,69,0.3); }

/* 금색 바 shimmer */
@keyframes nb-shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
.skin-noble .nb-gold-bar {
    background: linear-gradient(90deg, transparent, #d4a745 20%, #f0d078 50%, #d4a745 80%, transparent);
    background-size: 200% 100%;
    animation: nb-shimmer 4s linear infinite;
}

/* 버튼 호버 */
.skin-noble button { transition: all 0.25s ease; }
.skin-noble button:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(114,47,55,0.4); }
</style>

<div class="mg-inner skin-noble">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#d4a745;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div class="nb-frame">
        <div class="nb-gold-bar"></div>

        <!-- 헤더 배너 -->
        <?php if ($char_header) { ?>
        <div style="position:relative;max-height:12rem;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:brightness(50%) saturate(80%);">
            <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(13,17,23,0.3),rgba(13,17,23,0.8));"></div>
        </div>
        <?php } ?>

        <div style="padding:2rem 2rem;position:relative;z-index:1;">
            <!-- 문장 방패 -->
            <div style="text-align:center;margin-bottom:0.5rem;">
                <div class="nb-shield">
                    <div class="nb-shield-bg">
                        <span class="nb-title" style="font-size:2.5rem;font-weight:900;color:#d4a745;text-shadow:0 0 10px rgba(212,167,69,0.3);"><?php echo $ch_initial; ?></span>
                    </div>
                </div>
            </div>

            <!-- 리본 -->
            <div class="nb-ribbon nb-title">
                <?php echo $ch_side ?: 'Noble House'; ?>
            </div>

            <!-- 이름 -->
            <div style="text-align:center;margin-top:1.75rem;">
                <h1 class="nb-title" style="font-size:2rem;font-weight:900;letter-spacing:0.05em;margin:0;color:#f5f0e1;text-shadow:0 2px 4px rgba(0,0,0,0.5);">
                    <?php echo $ch_name; ?>
                </h1>
                <div style="font-size:1rem;color:#a0977d;font-style:italic;margin-top:0.25rem;">
                    <?php
                    $parts = array();
                    if ($ch_class && mg_config('use_class', '1') == '1') $parts[] = $ch_class;
                    echo $parts ? implode(' &bull; ', $parts) : 'of the Realm';
                    ?>
                </div>

                <!-- 액션 버튼 -->
                <div style="display:flex;gap:0.5rem;justify-content:center;margin-top:1.25rem;">
                    <?php if ($can_request_relation) { ?>
                    <button type="button" onclick="openRelRequestModal()" style="font-size:0.8125rem;background:linear-gradient(180deg,#722f37,#5a242b);color:#f5f0e1;border:1px solid #8b3a42;padding:0.375rem 1.25rem;border-radius:2px;cursor:pointer;box-shadow:0 2px 4px rgba(0,0,0,0.3);" class="nb-title">관계 신청</button>
                    <?php } ?>
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.8125rem;border:1px solid rgba(212,167,69,0.4);color:#d4a745;padding:0.375rem 1.25rem;border-radius:2px;text-decoration:none;background:rgba(212,167,69,0.05);" class="nb-title">수정</a>
                    <?php } ?>
                </div>
            </div>

            <div class="nb-rule"><span class="nb-rule-icon">&#9830;</span></div>

            <!-- 초상화 + 기본정보 -->
            <div style="display:flex;gap:2rem;align-items:flex-start;flex-wrap:wrap;justify-content:center;" class="nb-main-area">
                <!-- 초상화 -->
                <div style="text-align:center;flex-shrink:0;">
                    <?php if ($char_image) { ?>
                    <div class="nb-portrait-frame">
                        <div class="nb-portrait-inner">
                            <img src="<?php echo $char_image; ?>" alt="">
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="nb-portrait-frame">
                        <div class="nb-portrait-inner" style="display:flex;align-items:center;justify-content:center;background:#1a1f2e;">
                            <span class="nb-title" style="font-size:3rem;color:#d4a745;"><?php echo $ch_initial; ?></span>
                        </div>
                    </div>
                    <?php } ?>
                    <div style="margin-top:0.75rem;font-size:0.75rem;color:#a0977d;" class="nb-title">
                        @<?php echo $ch_owner; ?> &mdash; <?php echo $ch_date; ?>
                    </div>
                </div>

                <!-- 기본정보 -->
                <div style="flex:1;min-width:240px;">
                    <div class="nb-section-title nb-title">Royal Registry</div>
                    <table class="nb-table">
                        <tbody>
                            <tr>
                                <td class="nb-table-label nb-title">성명</td>
                                <td class="nb-table-value" style="font-weight:600;"><?php echo $ch_name; ?></td>
                            </tr>
                            <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                            <tr>
                                <td class="nb-table-label nb-title"><?php echo htmlspecialchars(mg_config('class_title', '유형')); ?></td>
                                <td class="nb-table-value"><?php echo $ch_class; ?></td>
                            </tr>
                            <?php } ?>
                            <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                            <tr>
                                <td class="nb-table-label nb-title"><?php echo htmlspecialchars(mg_config('side_title', '소속')); ?></td>
                                <td class="nb-table-value"><?php echo $ch_side; ?></td>
                            </tr>
                            <?php } ?>
                            <tr>
                                <td class="nb-table-label nb-title">등록일</td>
                                <td class="nb-table-value"><?php echo $ch_date; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 업적 -->
            <?php if (!empty($achievement_showcase)) { ?>
            <div class="nb-rule"><span class="nb-rule-icon">&#9733;</span></div>
            <div class="nb-section-title nb-title">Decorations</div>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;margin-bottom:0.5rem;">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                ?>
                <span class="nb-badge" title="<?php echo $a_name; ?>">
                    <?php if ($a_icon) { ?>
                    <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:14px;height:14px;object-fit:contain;">
                    <?php } ?>
                    <?php echo $a_name; ?>
                </span>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 프로필 필드 -->
            <?php if (count($grouped_fields) > 0) { ?>
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div class="nb-rule"><span class="nb-rule-icon">&#10070;</span></div>
            <div class="nb-section-title nb-title"><?php echo htmlspecialchars($category); ?></div>
            <table class="nb-table">
                <tbody>
                    <?php foreach ($fields as $field) { ?>
                    <tr>
                        <td class="nb-table-label nb-title"><?php echo htmlspecialchars($field['pf_name']); ?></td>
                        <td class="nb-table-value">
                            <?php echo mg_render_profile_value($field); ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php } ?>
            <?php } ?>

            <!-- 관계 -->
            <?php if (!empty($char_relations)) { ?>
            <div class="nb-rule"><span class="nb-rule-icon">&#9830;</span></div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div class="nb-section-title nb-title" style="margin-bottom:0;">궁정 인물록</div>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#a0977d;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.75rem;color:#d4a745;background:none;border:none;cursor:pointer;" class="nb-title">관계도 보기</button>
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
            <div class="nb-rel-item">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid rgba(212,167,69,0.4);">
                <?php } else { ?>
                <div style="width:34px;height:34px;border-radius:50%;background:rgba(212,167,69,0.08);display:flex;align-items:center;justify-content:center;color:#d4a745;font-weight:bold;border:2px solid rgba(212,167,69,0.3);font-size:0.75rem;" class="nb-title"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#a0977d;font-style:italic;font-size:0.9375rem;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:600;" class="nb-title"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
                <div id="rel-graph-container" style="height:400px;background:#080b11;border:1px solid rgba(212,167,69,0.2);"></div>
            </div>
            <?php } ?>
        </div>

        <div class="nb-gold-bar"></div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (min-width: 640px) {
    .skin-noble .nb-main-area { flex-wrap: nowrap !important; }
    .skin-noble .nb-table-label { width: 140px; }
}
@media (max-width: 639px) {
    .skin-noble .nb-shield { width: 80px; height: 96px; }
    .skin-noble .nb-shield-bg span { font-size: 2rem !important; }
    .skin-noble .nb-portrait-inner { width: 130px; height: 130px; }
    .skin-noble .nb-table,
    .skin-noble .nb-table tbody,
    .skin-noble .nb-table tr,
    .skin-noble .nb-table td { display: block; width: 100%; }
    .skin-noble .nb-table-label { width: auto; border-bottom: none; padding: 0.375rem 0.5rem 0.125rem; font-size: 0.75rem; opacity: 0.7; }
    .skin-noble .nb-table-value { padding: 0.125rem 0.5rem 0.5rem; border-bottom: 1px solid rgba(212,167,69,0.08); }
}
</style>
