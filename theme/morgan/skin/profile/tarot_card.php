<?php
/**
 * Morgan Edition - 프로필 스킨: 타로 카드
 * 보라/금, 신비로운, 카드 프레임, 별자리 장식
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);
$ch_number = str_pad($char['ch_id'], 0, STR_PAD_LEFT);
?>

<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Cormorant+Garamond:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

<style>
.skin-tarot { font-family: 'Cormorant Garamond', Georgia, serif; color: #e2d9f3; }
.skin-tarot .tt-cinzel { font-family: 'Cinzel', serif; }
.skin-tarot a { color: #c084fc; }
.skin-tarot a:hover { color: #e9d5ff; }
.skin-tarot .tt-card {
    background: linear-gradient(180deg, #1a0533 0%, #0f0520 50%, #1a0533 100%);
    border: 3px solid #7c3aed;
    border-radius: 1rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0 40px rgba(124,58,237,0.3), inset 0 0 80px rgba(124,58,237,0.05);
}
.skin-tarot .tt-card::before {
    content: '';
    position: absolute; inset: 8px;
    border: 1px solid rgba(196,181,253,0.2);
    border-radius: 0.75rem;
    pointer-events: none; z-index: 0;
}
.skin-tarot .tt-star {
    position: absolute; width: 2px; height: 2px; background: #e9d5ff; border-radius: 50%;
    animation: tt-twinkle 3s ease-in-out infinite alternate;
}
@keyframes tt-twinkle { 0% { opacity: 0.2; } 100% { opacity: 1; } }
.skin-tarot .tt-divider {
    display: flex; align-items: center; justify-content: center; gap: 1rem;
    margin: 1.5rem 0; color: rgba(196,181,253,0.4); font-size: 0.875rem;
}
.skin-tarot .tt-divider::before, .skin-tarot .tt-divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(196,181,253,0.3), transparent);
}
.skin-tarot .tt-portrait {
    border: 3px solid rgba(196,181,253,0.4);
    box-shadow: 0 0 20px rgba(124,58,237,0.4);
}

/* 호버 효과 */
.skin-tarot .tt-card { transition: box-shadow 0.5s ease; }
.skin-tarot .tt-card:hover { box-shadow: 0 0 60px rgba(124,58,237,0.4), inset 0 0 80px rgba(124,58,237,0.08); }

.skin-tarot .tt-portrait { transition: all 0.4s ease; }
.skin-tarot .tt-portrait:hover { box-shadow: 0 0 30px rgba(124,58,237,0.6); transform: scale(1.03); }

.skin-tarot .tt-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-tarot .tt-rel-item:hover { background: rgba(124,58,237,0.1); border-left-color: #7c3aed; }

.skin-tarot .tt-badge { transition: all 0.25s ease; }
.skin-tarot .tt-badge:hover { transform: scale(1.08); box-shadow: 0 0 12px rgba(124,58,237,0.4); }

/* 구분선 glow pulse */
@keyframes tt-divider-glow { 0%,100% { opacity: 0.4; } 50% { opacity: 0.8; } }
.skin-tarot .tt-divider { animation: tt-divider-glow 4s ease-in-out infinite; }

/* 버튼 호버 */
.skin-tarot button { transition: all 0.25s ease; }
.skin-tarot button:hover { background: rgba(124,58,237,0.5) !important; transform: translateY(-1px); box-shadow: 0 0 15px rgba(124,58,237,0.4); }
</style>

<div class="mg-inner skin-tarot" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#a78bfa;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div class="tt-card" style="margin:0 auto;padding:2rem 2rem;">
        <!-- 장식 별 -->
        <div class="tt-star" style="top:12%;left:15%;animation-delay:0s;"></div>
        <div class="tt-star" style="top:8%;right:20%;animation-delay:0.7s;"></div>
        <div class="tt-star" style="top:25%;right:10%;animation-delay:1.4s;"></div>
        <div class="tt-star" style="bottom:30%;left:8%;animation-delay:2.1s;"></div>
        <div class="tt-star" style="bottom:15%;right:15%;animation-delay:0.3s;"></div>
        <div class="tt-star" style="top:50%;left:5%;animation-delay:1s;"></div>

        <?php if ($char_header) { ?>
        <div style="position:relative;z-index:1;max-height:10rem;overflow:hidden;margin-bottom:1rem;border-radius:0.5rem;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <div style="position:absolute;inset:0;background:linear-gradient(180deg,rgba(26,5,51,0.3),rgba(26,5,51,0.7));"></div>
        </div>
        <?php } ?>

        <!-- 번호 -->
        <div style="text-align:center;position:relative;z-index:1;">
            <span class="tt-cinzel" style="font-size:0.875rem;color:rgba(196,181,253,0.5);letter-spacing:0.3em;"><?php echo $ch_number; ?></span>
        </div>

        <!-- 초상화 -->
        <div style="text-align:center;margin:1.5rem 0;position:relative;z-index:1;">
            <?php if ($char_image) { ?>
            <div style="width:180px;height:280px;margin:0 auto;border-radius:0.5rem;overflow:hidden;" class="tt-portrait">
                <img src="<?php echo $char_image; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            </div>
            <?php } else { ?>
            <div style="width:180px;height:280px;margin:0 auto;border-radius:0.5rem;background:rgba(124,58,237,0.15);display:flex;align-items:center;justify-content:center;font-size:4rem;color:#7c3aed;" class="tt-cinzel tt-portrait"><?php echo $ch_initial; ?></div>
            <?php } ?>
        </div>

        <!-- 이름 -->
        <div style="text-align:center;position:relative;z-index:1;">
            <h1 class="tt-cinzel" style="font-size:1.75rem;font-weight:900;letter-spacing:0.05em;text-transform:uppercase;margin:0;background:linear-gradient(180deg,#f5f3ff,#c084fc);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                <?php echo $ch_name; ?>
            </h1>
            <p style="font-size:1rem;color:#a78bfa;font-style:italic;margin-top:0.25rem;">
                <?php
                $parts = array();
                if ($ch_class && mg_config('use_class', '1') == '1') $parts[] = $ch_class;
                if ($ch_side && mg_config('use_side', '1') == '1') $parts[] = $ch_side;
                echo $parts ? implode(' &middot; ', $parts) : '@'.$ch_owner;
                ?>
            </p>

            <!-- 액션 버튼 -->
            <div style="display:flex;gap:0.5rem;justify-content:center;margin-top:1rem;">
                <?php if ($can_request_relation) { ?>
                <button type="button" onclick="openRelRequestModal()" style="background:rgba(124,58,237,0.3);color:#e9d5ff;border:1px solid #7c3aed;border-radius:0.375rem;padding:0.375rem 1rem;font-size:0.8125rem;cursor:pointer;" class="tt-cinzel">관계 신청</button>
                <?php } ?>
                <?php if ($is_owner) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="background:rgba(124,58,237,0.1);color:#a78bfa;border:1px solid rgba(124,58,237,0.3);border-radius:0.375rem;padding:0.375rem 1rem;font-size:0.8125rem;text-decoration:none;" class="tt-cinzel">수정</a>
                <?php } ?>
            </div>
        </div>

        <div class="tt-divider"><span>&#10022;</span></div>

        <!-- 업적 -->
        <?php if (!empty($achievement_showcase)) { ?>
        <div style="text-align:center;position:relative;z-index:1;margin-bottom:1.5rem;">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;justify-content:center;">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                ?>
                <span class="tt-badge" style="display:inline-flex;align-items:center;gap:0.25rem;background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.3);border-radius:9999px;padding:0.25rem 0.75rem;font-size:0.75rem;color:#c4b5fd;" title="<?php echo $a_name; ?>">
                    <?php if ($a_icon) { ?>
                    <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:14px;height:14px;object-fit:contain;">
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
        <div style="position:relative;z-index:1;margin-bottom:1.5rem;">
            <h3 class="tt-cinzel" style="text-align:center;font-size:0.8125rem;letter-spacing:0.2em;text-transform:uppercase;color:#a78bfa;margin-bottom:1rem;"><?php echo htmlspecialchars($category); ?></h3>
            <?php foreach ($fields as $field) { ?>
            <div style="margin-bottom:0.75rem;padding:0 0.5rem;">
                <div style="font-size:0.75rem;color:#7c3aed;text-transform:uppercase;letter-spacing:0.1em;" class="tt-cinzel"><?php echo htmlspecialchars($field['pf_name']); ?></div>
                <div style="font-size:1rem;color:#e2d9f3;margin-top:0.125rem;line-height:1.6;font-style:italic;">
                    <?php echo mg_render_profile_value($field); ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php if (next($grouped_fields) !== false) { ?>
        <div class="tt-divider"><span>&#10022;</span></div>
        <?php } ?>
        <?php } ?>
        <?php } ?>

        <!-- 관계 -->
        <?php if (!empty($char_relations)) { ?>
        <div class="tt-divider"><span>&#9734;</span></div>
        <div style="position:relative;z-index:1;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 class="tt-cinzel" style="font-size:0.8125rem;letter-spacing:0.2em;text-transform:uppercase;color:#a78bfa;">관계</h3>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#7c3aed;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.75rem;color:#c084fc;background:none;border:none;cursor:pointer;" class="tt-cinzel">관계도 보기</button>
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
            <div class="tt-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid rgba(124,58,237,0.15);font-size:0.875rem;">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(124,58,237,0.3);">
                <?php } else { ?>
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(124,58,237,0.15);display:flex;align-items:center;justify-content:center;color:#a78bfa;font-weight:bold;font-size:0.75rem;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#7c3aed;font-style:italic;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:600;"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
                <div id="rel-graph-container" style="height:400px;background:#0a0020;border-radius:0.5rem;border:1px solid rgba(124,58,237,0.2);"></div>
            </div>
        </div>
        <?php } ?>

        <div class="tt-divider"><span>&#10022;</span></div>

        <!-- 푸터 -->
        <div style="text-align:center;font-size:0.75rem;color:rgba(196,181,253,0.4);position:relative;z-index:1;" class="tt-cinzel">
            @<?php echo $ch_owner; ?> &middot; <?php echo $ch_date; ?>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
