<?php
/**
 * Morgan Edition - 프로필 스킨: 신문 기사
 * 세피아/모노, 올드 프레스, 컬럼 레이아웃
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y년 m월 d일', strtotime($char['ch_datetime']));
$ch_date_en = date('F j, Y', strtotime($char['ch_datetime']));
$edition = 'Vol. ' . ceil($char['ch_id'] / 10) . ', No. ' . $char['ch_id'];
?>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Lora:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

<style>
.skin-news { font-family: 'Lora', Georgia, serif; color: #2c1810; }
.skin-news a { color: #8b4513; }
.skin-news a:hover { text-decoration: underline; }
.skin-news .nw-playfair { font-family: 'Playfair Display', Georgia, serif; }
.skin-news .nw-paper {
    background: #faf6f0;
    border: 1px solid #d4c5a9;
    box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
    position: relative;
}
.skin-news .nw-rule { border: none; border-top: 1px solid #2c1810; margin: 0; }
.skin-news .nw-rule-thick { border: none; border-top: 3px double #2c1810; margin: 0; }
.skin-news .nw-rule-thin { border: none; border-top: 1px solid #d4c5a9; margin: 0.75rem 0; }
.skin-news .nw-cols {
    column-count: 1; column-gap: 1.5rem; column-rule: 1px solid #d4c5a9;
}
.skin-news .nw-dropcap::first-letter {
    float: left; font-size: 3.5rem; line-height: 0.8; padding-right: 0.25rem;
    font-weight: 900; color: #2c1810; font-family: 'Playfair Display', serif;
}

/* 호버 효과 */
.skin-news .nw-paper { transition: box-shadow 0.4s ease; }
.skin-news .nw-paper:hover { box-shadow: 4px 4px 16px rgba(0,0,0,0.15); }

.skin-news .nw-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-news .nw-rel-item:hover { background: #f5f0e6; border-left-color: #2c1810; }

/* 사진 sepia 해제 호버 */
.skin-news img[style*="sepia"] { transition: filter 0.5s ease; }
.skin-news img[style*="sepia"]:hover { filter: sepia(0%) contrast(100%) !important; }

/* 드롭캡 호버 */
.skin-news .nw-dropcap:hover::first-letter { color: #8b4513; }

/* 헤드라인 호버 */
.skin-news .nw-playfair { transition: letter-spacing 0.3s ease; }

/* 버튼 호버 */
.skin-news button { transition: all 0.25s ease; }
.skin-news button:hover { opacity: 0.8; transform: translateY(-1px); }
</style>

<div class="mg-inner skin-news" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#8b7355;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div class="nw-paper" style="max-width:52rem;margin:0 auto;padding:0;">
        <!-- 마스트헤드 -->
        <div style="text-align:center;padding:1.25rem 2rem 0.75rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.6875rem;color:#8b7355;">
                <span><?php echo $edition; ?></span>
                <span><?php echo $ch_date; ?></span>
            </div>
            <hr class="nw-rule" style="margin:0.5rem 0;">
            <h1 class="nw-playfair" style="font-size:2.5rem;font-weight:900;letter-spacing:-0.02em;margin:0.5rem 0;line-height:1;">
                THE MORGAN CHRONICLE
            </h1>
            <div style="font-size:0.6875rem;color:#8b7355;font-style:italic;margin-bottom:0.25rem;">
                "ALL THE NEWS THAT'S FIT TO PRINT"
            </div>
            <hr class="nw-rule-thick">
        </div>

        <?php if ($char_header) { ?>
        <div style="padding:0 2rem;">
            <div style="max-height:14rem;overflow:hidden;border:1px solid #d4c5a9;">
                <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:sepia(20%) contrast(110%);">
            </div>
            <p style="font-size:0.6875rem;color:#8b7355;font-style:italic;text-align:center;margin:0.25rem 0 0;">사진 제공 — The Morgan Chronicle</p>
        </div>
        <?php } ?>

        <!-- 메인 콘텐츠 -->
        <div style="padding:1rem 2rem 2rem;">
            <!-- 헤드라인 -->
            <div style="margin-bottom:1.5rem;">
                <h2 class="nw-playfair" style="font-size:2rem;font-weight:900;line-height:1.15;margin:0 0 0.5rem;">
                    <?php echo $ch_name; ?><?php
                    $sub_parts = array();
                    if ($ch_class && mg_config('use_class', '1') == '1') $sub_parts[] = $ch_class;
                    if ($ch_side && mg_config('use_side', '1') == '1') $sub_parts[] = $ch_side;
                    if ($sub_parts) echo ', ' . implode(' &middot; ', $sub_parts);
                    ?>
                </h2>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                    <div style="display:flex;gap:0.5rem;">
                        <?php if ($can_request_relation) { ?>
                        <button type="button" onclick="openRelRequestModal()" style="background:#2c1810;color:#faf6f0;border:none;padding:0.25rem 0.75rem;font-size:0.75rem;cursor:pointer;font-family:inherit;">관계 신청</button>
                        <?php } ?>
                        <?php if ($is_owner) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="border:1px solid #2c1810;padding:0.25rem 0.75rem;font-size:0.75rem;color:#2c1810;text-decoration:none;">수정</a>
                        <?php } ?>
                    </div>
                </div>
                <div style="font-size:0.75rem;color:#8b7355;margin-top:0.375rem;">
                    BY <?php echo strtoupper($ch_owner); ?> | <?php echo strtoupper($ch_date_en); ?>
                </div>
            </div>

            <hr class="nw-rule">

            <!-- 사진 + 본문 -->
            <div style="margin-top:1rem;">
                <?php if ($char_image) { ?>
                <div style="float:left;width:200px;margin:0.25rem 1.5rem 1rem 0;">
                    <img src="<?php echo $char_image; ?>" alt="" style="width:100%;border:1px solid #d4c5a9;filter:sepia(20%) contrast(110%);">
                    <p style="font-size:0.6875rem;color:#8b7355;font-style:italic;margin-top:0.25rem;text-align:center;">
                        <?php echo $ch_name; ?> — 본지 제공
                    </p>
                </div>
                <?php } ?>

                <!-- 업적 (인라인) -->
                <?php if (!empty($achievement_showcase)) { ?>
                <div style="margin-bottom:1rem;">
                    <span style="font-weight:700;">주요 업적 —</span>
                    <?php
                    $a_names = array();
                    foreach ($achievement_showcase as $acd) {
                        $a_names[] = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    }
                    echo implode(', ', $a_names);
                    ?>
                </div>
                <?php } ?>

                <!-- 프로필 필드 (신문 본문 스타일) -->
                <?php if (count($grouped_fields) > 0) { ?>
                <div class="nw-cols">
                <?php $first = true; ?>
                <?php foreach ($grouped_fields as $category => $fields) { ?>
                    <?php if (!$first) { ?>
                    <hr class="nw-rule-thin">
                    <?php } ?>
                    <h3 class="nw-playfair" style="font-size:1.125rem;font-weight:700;margin:0 0 0.5rem;break-after:avoid;"><?php echo htmlspecialchars($category); ?></h3>
                    <?php foreach ($fields as $i => $field) { ?>
                    <div style="margin-bottom:0.75rem;break-inside:avoid;<?php echo ($first && $i === 0) ? '' : ''; ?>" <?php echo ($first && $i === 0) ? 'class="nw-dropcap"' : ''; ?>>
                        <span style="font-weight:700;"><?php echo htmlspecialchars($field['pf_name']); ?>.</span>
                        <?php echo mg_render_profile_value($field); ?>
                    </div>
                    <?php } ?>
                    <?php $first = false; ?>
                <?php } ?>
                </div>
                <?php } ?>

                <div style="clear:both;"></div>
            </div>

            <!-- 관계 -->
            <?php if (!empty($char_relations)) { ?>
            <hr class="nw-rule" style="margin-top:1.5rem;">
            <div style="margin-top:1rem;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                    <h3 class="nw-playfair" style="font-size:1.25rem;font-weight:700;margin:0;">관련 인물</h3>
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <?php if ($is_owner) { ?>
                        <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#8b7355;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                        <?php } ?>
                        <button type="button" id="rel-graph-toggle" style="font-size:0.75rem;color:#8b4513;background:none;border:none;cursor:pointer;font-family:inherit;text-decoration:underline;">관계도 보기</button>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr;gap:0;" class="nw-rel-grid">
                <?php foreach ($char_relations as $rel) {
                    $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                    $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                    $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                    $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                    $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                    $rel_color = $rel['cr_color'] ?: '#95a5a6';
                ?>
                <div class="nw-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid #d4c5a9;">
                    <?php if ($other_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:1px solid #d4c5a9;filter:sepia(30%);">
                    <?php } else { ?>
                    <div style="width:36px;height:36px;border-radius:50%;background:#f0e6d2;display:flex;align-items:center;justify-content:center;color:#8b7355;font-weight:700;font-size:0.75rem;border:1px solid #d4c5a9;" class="nw-playfair"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                    <?php } ?>
                    <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                    <span style="color:#8b7355;font-style:italic;font-size:0.875rem;"><?php echo $my_label; ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:700;font-size:0.9375rem;" class="nw-playfair"><?php echo $other_name; ?></a>
                </div>
                <?php } ?>
                </div>
                <!-- 인라인 관계도 -->
                <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
                    <div id="rel-graph-container" style="height:400px;background:#1a120b;border:1px solid #d4c5a9;"></div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 푸터 -->
        <div style="border-top:3px double #2c1810;padding:0.75rem 2rem;display:flex;justify-content:space-between;font-size:0.6875rem;color:#8b7355;">
            <span>THE MORGAN CHRONICLE &copy; Morgan Edition</span>
            <span>Printed on <?php echo $ch_date; ?></span>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (min-width: 640px) {
    .skin-news .nw-cols { column-count: 2; }
    .skin-news .nw-rel-grid { grid-template-columns: 1fr 1fr !important; column-gap: 1.5rem; }
}
@media (max-width: 639px) {
    .skin-news .nw-paper { padding: 0; }
    .skin-news .nw-paper > div { padding-left: 1rem !important; padding-right: 1rem !important; }
    .skin-news .nw-playfair h1 { font-size: 1.75rem !important; }
}
</style>
