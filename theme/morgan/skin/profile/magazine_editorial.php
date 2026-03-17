<?php
/**
 * Morgan Edition - 프로필 스킨: 매거진 에디토리얼
 * 라이트/크림 배경, Playfair Display 세리프, 겹침 레이아웃, mix-blend-mode
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);

// 이름 영문 변환 (대문자)
$ch_name_upper = strtoupper($ch_name);

// 클래스/세력 표시 텍스트
$subtitle_parts = array();
if ($ch_class && mg_config('use_class', '1') == '1') $subtitle_parts[] = $ch_class;
if ($ch_side && mg_config('use_side', '1') == '1') $subtitle_parts[] = $ch_side;
$subtitle = $subtitle_parts ? implode(' / ', $subtitle_parts) : '@'.$ch_owner;
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,400&family=Noto+Sans+KR:wght@300;400;700;900&display=swap');

.skin-magazine { font-family: 'Noto Sans KR', sans-serif; color: #27272a; }
.skin-magazine a { color: #71717a; text-decoration: none; }
.skin-magazine a:hover { color: #18181b; }
.skin-magazine .mg-editorial { font-family: 'Playfair Display', Georgia, serif; }

.skin-magazine .mg-spread {
    background: #f4f4f0;
    position: relative;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
    min-height: 550px;
}

/* 메인 초상화 */
.skin-magazine .mg-portrait-wrap {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    width: 350px;
    height: 450px;
    background: #3f3f46;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
    z-index: 10;
    overflow: hidden;
}
.skin-magazine .mg-portrait-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(100%);
    transition: filter 0.7s, transform 0.7s;
}
.skin-magazine .mg-portrait-wrap:hover img {
    filter: grayscale(0%);
    transform: scale(1.05);
}

/* mix-blend 텍스트 */
.skin-magazine .mg-blend {
    mix-blend-mode: difference;
    color: white;
    pointer-events: none;
}

/* 세로쓰기 */
.skin-magazine .mg-vertical {
    writing-mode: vertical-rl;
    text-orientation: mixed;
}

/* 아래쪽 콘텐츠 영역 */
.skin-magazine .mg-content-area {
    background: #f4f4f0;
    padding: 2rem;
}

/* 관계 항목 */
.skin-magazine .mg-rel-item { transition: all 0.25s; border-left: 2px solid transparent; padding-left: 0.5rem; }
.skin-magazine .mg-rel-item:hover { background: rgba(0,0,0,0.03); border-left-color: #18181b; }

/* 뱃지 호버 */
.skin-magazine .mg-badge { transition: all 0.2s; }
.skin-magazine .mg-badge:hover { transform: scale(1.05); }

/* 버튼 호버 */
.skin-magazine button { transition: all 0.2s; }
.skin-magazine button:hover { background: #18181b !important; color: #f4f4f0 !important; }

/* 반응형: 모바일 */
@media (max-width: 768px) {
    .skin-magazine .mg-spread { min-height: 400px; }
    .skin-magazine .mg-portrait-wrap { width: 220px; height: 300px; }
    .skin-magazine .mg-hero-name { font-size: 3rem !important; }
    .skin-magazine .mg-watermark { font-size: 6rem !important; }
    .skin-magazine .mg-vertical-bar { display: none; }
    .skin-magazine .mg-right-quote { display: none; }
    .skin-magazine .mg-stat-spread { flex-direction: column; gap: 1rem; }
}
</style>

<div class="mg-inner skin-magazine" style="max-width:1000px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#71717a;margin-bottom:1rem;">
        <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
        <span>뒤로</span>
    </a>

    <!-- 헤더 배너 -->
    <?php if ($char_header) { ?>
    <div style="margin-bottom:0;overflow:hidden;position:relative;">
        <div style="max-height:15rem;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:grayscale(30%) contrast(110%);">
        </div>
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom, transparent 50%, #f4f4f0 100%);"></div>
    </div>
    <?php } ?>

    <!-- 매거진 표지 스프레드 -->
    <div class="mg-spread" style="margin-bottom:0;">
        <!-- 워터마크 (배경 대형 텍스트) -->
        <div class="mg-watermark mg-editorial" style="position:absolute;top:-1.5rem;left:-0.5rem;font-size:12rem;font-weight:900;color:rgba(0,0,0,0.06);z-index:0;line-height:1;user-select:none;">
            <?php echo $ch_name_upper; ?>
        </div>

        <!-- 초상화 -->
        <div class="mg-portrait-wrap">
            <?php if ($char_image) { ?>
            <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>">
            <?php } else { ?>
            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;">
                <span class="mg-editorial" style="font-size:6rem;color:#71717a;font-style:italic;"><?php echo $ch_initial; ?></span>
            </div>
            <?php } ?>
            <div style="position:absolute;inset:0;background:rgba(0,0,0,0.1);mix-blend-mode:overlay;"></div>
        </div>

        <!-- 이름 (좌상단, blend) -->
        <div class="mg-blend" style="position:absolute;top:2.5rem;left:2rem;z-index:20;">
            <h1 class="mg-editorial mg-hero-name" style="font-size:5rem;font-weight:900;letter-spacing:-0.05em;line-height:1;margin:0 0 0.5rem;">
                <?php echo $ch_name_upper; ?>
            </h1>
            <p style="font-size:1rem;font-weight:900;letter-spacing:0.3em;margin-left:0.125rem;"><?php echo strtoupper($subtitle); ?></p>
            <p style="font-size:0.75rem;letter-spacing:0.2em;margin-left:0.125rem;margin-top:0.5rem;font-weight:300;">
                MORGAN EDITION / <?php echo date('Y'); ?>
            </p>
        </div>

        <!-- 세로 텍스트 (우측) -->
        <div class="mg-vertical-bar" style="position:absolute;top:0;bottom:0;right:1rem;display:flex;align-items:center;z-index:20;pointer-events:none;">
            <p class="mg-vertical mg-editorial" style="font-size:0.6875rem;letter-spacing:0.5em;color:#a1a1aa;">
                EXCLUSIVE CHARACTER PROFILE
            </p>
        </div>

        <!-- 우상단 인용문 -->
        <div class="mg-right-quote" style="position:absolute;top:3rem;right:3rem;z-index:20;width:14rem;text-align:right;">
            <p style="font-size:0.6875rem;color:#71717a;font-weight:300;line-height:1.6;text-align:justify;">
                <?php echo $ch_name; ?> &mdash; <?php echo $subtitle; ?>. <?php echo $ch_date; ?>에 등록됨.
            </p>
        </div>

        <!-- 스탯 영역 (하단 좌측, blend) -->
        <?php if ($_battle_use == '1' && $battle_stat) {
            $_stat_base = (int)mg_config('battle_stat_base', '5');
            $bs_hp = (int)($battle_stat['stat_hp'] ?? $_stat_base);
            $bs_str = (int)($battle_stat['stat_str'] ?? $_stat_base);
            $bs_dex = (int)($battle_stat['stat_dex'] ?? $_stat_base);
            $bs_int = (int)($battle_stat['stat_int'] ?? $_stat_base);
            $bs_stress = (int)($battle_stat['stat_stress'] ?? 0);
        ?>
        <div class="mg-blend mg-stat-spread" style="position:absolute;bottom:2rem;left:2rem;z-index:20;display:flex;gap:2rem;align-items:flex-end;">
            <?php if ($battle_hp && $battle_hp['max_hp'] > 0) { ?>
            <div>
                <p class="mg-editorial" style="font-size:0.75rem;font-style:italic;margin-bottom:-0.25rem;">Health Points</p>
                <p style="font-size:3.5rem;font-weight:900;letter-spacing:-0.05em;line-height:1;">
                    <?php echo (int)$battle_hp['current_hp']; ?><span style="font-size:1rem;font-weight:normal;color:rgba(255,255,255,0.6);">/ <?php echo (int)$battle_hp['max_hp']; ?></span>
                </p>
            </div>
            <?php } ?>

            <div style="display:flex;flex-direction:column;gap:0.25rem;padding-bottom:0.25rem;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size:0.6875rem;font-weight:bold;letter-spacing:0.2em;width:2rem;">HP</span>
                    <div style="height:1px;width:3rem;background:white;"></div>
                    <span class="mg-editorial" style="font-size:1.125rem;font-weight:bold;"><?php echo str_pad($bs_hp, 3, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size:0.6875rem;font-weight:bold;letter-spacing:0.2em;width:2rem;">STR</span>
                    <div style="height:1px;width:3rem;background:white;"></div>
                    <span class="mg-editorial" style="font-size:1.125rem;font-weight:bold;"><?php echo str_pad($bs_str, 3, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size:0.6875rem;font-weight:bold;letter-spacing:0.2em;width:2rem;">DEX</span>
                    <div style="height:1px;width:3rem;background:white;"></div>
                    <span class="mg-editorial" style="font-size:1.125rem;font-weight:bold;"><?php echo str_pad($bs_dex, 3, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="font-size:0.6875rem;font-weight:bold;letter-spacing:0.2em;width:2rem;">INT</span>
                    <div style="height:1px;width:3rem;background:white;"></div>
                    <span class="mg-editorial" style="font-size:1.125rem;font-weight:bold;"><?php echo str_pad($bs_int, 3, '0', STR_PAD_LEFT); ?></span>
                </div>
            </div>
        </div>

        <!-- 스트레스 (우하단) -->
        <div style="position:absolute;bottom:2rem;right:3rem;z-index:20;text-align:right;">
            <p style="font-size:0.625rem;font-weight:bold;letter-spacing:0.2em;color:#3f3f46;">STRESS</p>
            <p class="mg-editorial" style="font-size:1.5rem;font-weight:900;color:<?php echo $bs_stress >= 70 ? '#dc2626' : '#18181b'; ?>;line-height:1;">
                <?php echo $bs_stress; ?>%
            </p>
        </div>
        <?php } ?>

        <!-- 바코드 장식 (우하단) -->
        <div style="position:absolute;bottom:1.5rem;right:3rem;z-index:20;display:flex;height:2rem;align-items:flex-end;gap:1px;">
            <div style="width:2px;height:100%;background:#3f3f46;"></div>
            <div style="width:1px;height:100%;background:#3f3f46;"></div>
            <div style="width:4px;height:100%;background:#3f3f46;"></div>
            <div style="width:1px;height:100%;background:#3f3f46;"></div>
            <div style="width:2px;height:40%;background:#3f3f46;"></div>
            <div style="width:4px;height:100%;background:#3f3f46;"></div>
            <div style="width:1px;height:100%;background:#3f3f46;"></div>
            <div style="width:2px;height:100%;background:#3f3f46;"></div>
        </div>
    </div>

    <!-- 콘텐츠 영역 (하단 카드) -->
    <div class="mg-content-area">
        <!-- 액션 버튼 -->
        <div style="display:flex;gap:0.5rem;margin-bottom:2rem;">
            <?php if ($can_request_relation) { ?>
            <button type="button" onclick="openRelRequestModal()" style="font-size:0.75rem;background:#27272a;color:#f4f4f0;border:none;padding:0.5rem 1.25rem;cursor:pointer;letter-spacing:0.1em;">관계 신청</button>
            <?php } ?>
            <?php if ($is_owner) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.75rem;background:transparent;color:#27272a;border:1px solid #27272a;padding:0.5rem 1.25rem;letter-spacing:0.1em;">수정</a>
            <?php } ?>
        </div>

        <!-- 업적 쇼케이스 -->
        <?php if (!empty($achievement_showcase)) { ?>
        <div style="margin-bottom:2rem;">
            <h3 class="mg-editorial" style="font-size:1.25rem;font-weight:700;margin-bottom:1rem;border-bottom:2px solid #18181b;padding-bottom:0.5rem;">Achievements</h3>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                ?>
                <span class="mg-badge" style="display:inline-flex;align-items:center;gap:0.375rem;background:#27272a;color:#f4f4f0;padding:0.25rem 0.75rem;font-size:0.6875rem;letter-spacing:0.05em;" title="<?php echo $a_name; ?>">
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
        <div style="margin-bottom:2rem;">
            <h3 class="mg-editorial" style="font-size:1.25rem;font-weight:700;margin-bottom:1rem;border-bottom:2px solid #18181b;padding-bottom:0.5rem;"><?php echo htmlspecialchars($category); ?></h3>
            <?php foreach ($fields as $field) { ?>
            <div style="margin-bottom:1rem;padding-left:0.5rem;">
                <div style="font-size:0.6875rem;font-weight:bold;color:#71717a;text-transform:uppercase;letter-spacing:0.15em;margin-bottom:0.25rem;"><?php echo htmlspecialchars($field['pf_name']); ?></div>
                <div style="font-size:0.9375rem;color:#27272a;line-height:1.7;">
                    <?php echo mg_render_profile_value($field); ?>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        <?php } ?>

        <!-- 관계 -->
        <?php if (!empty($char_relations)) { ?>
        <div style="margin-bottom:2rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;border-bottom:2px solid #18181b;padding-bottom:0.5rem;">
                <h3 class="mg-editorial" style="font-size:1.25rem;font-weight:700;margin:0;">Relations</h3>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.6875rem;color:#71717a;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.6875rem;color:#18181b;background:none;border:none;cursor:pointer;letter-spacing:0.1em;text-decoration:underline;">관계도 보기</button>
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
            <div class="mg-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid #d4d4d8;font-size:0.875rem;">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;filter:grayscale(50%);border:1px solid #d4d4d8;">
                <?php } else { ?>
                <div style="width:36px;height:36px;border-radius:50%;background:#e4e4e7;display:flex;align-items:center;justify-content:center;color:#71717a;font-weight:bold;font-size:0.75rem;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#71717a;font-style:italic;font-size:0.8125rem;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:600;color:#18181b;"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
                <div id="rel-graph-container" style="height:400px;background:#fafaf9;border:1px solid #d4d4d8;border-radius:0.25rem;"></div>
            </div>
        </div>
        <?php } ?>

        <!-- 푸터 -->
        <div style="text-align:center;font-size:0.6875rem;color:#a1a1aa;letter-spacing:0.2em;padding:1rem 0;" class="mg-editorial">
            @<?php echo $ch_owner; ?> &middot; <?php echo $ch_date; ?>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
