<?php
/**
 * Morgan Edition - 프로필 스킨: 전설의 카드
 * 다크 게임 테이블, 금장 프레임, Cinzel 판타지 폰트, 보석 스탯, 호버 리프트
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);

// 카드 서브타이틀
$card_subtitle_parts = array();
if ($ch_class && mg_config('use_class', '1') == '1') $card_subtitle_parts[] = $ch_class;
if ($ch_side && mg_config('use_side', '1') == '1') $card_subtitle_parts[] = $ch_side;
$card_subtitle = $card_subtitle_parts ? implode(' / ', $card_subtitle_parts) : '@'.$ch_owner;
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;700;900&family=Noto+Serif+KR:wght@400;700;900&display=swap');

.skin-lgcard { font-family: 'Noto Serif KR', serif; color: #e5e7eb; }
.skin-lgcard a { color: #fbbf24; text-decoration: none; }
.skin-lgcard a:hover { color: #fde68a; }
.skin-lgcard .lc-fantasy { font-family: 'Cinzel', serif; }

/* 카드 호버 리프트 */
.skin-lgcard .lc-card {
    transition: transform 0.3s, box-shadow 0.3s;
    cursor: default;
}
.skin-lgcard .lc-card:hover {
    transform: translateY(-8px) scale(1.01);
    box-shadow: 0 25px 50px -12px rgba(251, 191, 36, 0.25);
}

/* 금장 프레임 */
.skin-lgcard .lc-gold-frame {
    background: linear-gradient(135deg, #b48b3c 0%, #e2c174 25%, #8b6914 50%, #e2c174 75%, #b48b3c 100%);
    border-radius: 1rem;
    padding: 4px;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
    border: 2px solid #000000;
}

/* 스탯 보석 */
.skin-lgcard .lc-gem {
    box-shadow:
        inset -2px -2px 6px rgba(0,0,0,0.6),
        inset 2px 2px 6px rgba(255,255,255,0.4),
        0 4px 6px rgba(0,0,0,0.5);
    text-shadow: 1px 1px 2px black, -1px -1px 2px black, 1px -1px 2px black, -1px 1px 2px black;
    transition: all 0.25s;
}
.skin-lgcard .lc-gem:hover {
    transform: scale(1.1);
    box-shadow:
        inset -2px -2px 6px rgba(0,0,0,0.6),
        inset 2px 2px 6px rgba(255,255,255,0.4),
        0 0 15px rgba(251,191,36,0.5);
}

/* 관계 항목 */
.skin-lgcard .lc-rel-item { transition: all 0.2s; border-left: 2px solid transparent; padding-left: 0.5rem; }
.skin-lgcard .lc-rel-item:hover { background: rgba(251,191,36,0.08); border-left-color: #fbbf24; }

/* 뱃지 호버 */
.skin-lgcard .lc-badge { transition: all 0.2s; }
.skin-lgcard .lc-badge:hover { transform: scale(1.08); box-shadow: 0 0 10px rgba(251,191,36,0.4); }

/* 버튼 호버 */
.skin-lgcard button { transition: all 0.2s; }
.skin-lgcard button:hover { background: rgba(251,191,36,0.4) !important; transform: translateY(-1px); }

/* 카드 내부 구분선 */
.skin-lgcard .lc-divider {
    display: flex; align-items: center; justify-content: center; gap: 1rem;
    margin: 1.25rem 0; color: rgba(251,191,36,0.3); font-size: 0.75rem;
}
.skin-lgcard .lc-divider::before, .skin-lgcard .lc-divider::after {
    content: ''; flex: 1; height: 1px;
    background: linear-gradient(90deg, transparent, rgba(251,191,36,0.3), transparent);
}
</style>

<div class="mg-inner skin-lgcard" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#fbbf24;margin-bottom:1rem;">
        <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
        <span>뒤로</span>
    </a>

    <!-- 헤더 배너 -->
    <?php if ($char_header) { ?>
    <div class="lc-gold-frame" style="margin-bottom:1.5rem;">
        <div style="max-height:12rem;overflow:hidden;border-radius:0.75rem;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:brightness(70%) saturate(120%);">
        </div>
    </div>
    <?php } ?>

    <!-- 카드 -->
    <div class="lc-card" style="max-width:26rem;margin:0 auto;position:relative;padding-top:1.5rem;padding-bottom:2rem;">
        <!-- 레벨 보석 (좌상단) -->
        <div class="lc-gem" style="position:absolute;top:0;left:-0.5rem;width:3.5rem;height:3.5rem;border-radius:50%;background:#1d4ed8;border:4px solid #eab308;z-index:20;display:flex;align-items:center;justify-content:center;">
            <span class="lc-fantasy" style="font-size:1.25rem;font-weight:bold;color:#ffffff;line-height:1;">#<?php echo (int)$char['ch_id']; ?></span>
        </div>

        <div class="lc-gold-frame">
            <div style="width:100%;background:#27272a;border-radius:0.75rem;position:relative;display:flex;flex-direction:column;border:1px solid rgba(139,105,20,0.5);">

                <!-- 이름 -->
                <div style="text-align:center;margin-top:0.75rem;margin-bottom:0.5rem;">
                    <h2 class="lc-fantasy" style="font-size:1.5rem;font-weight:900;color:#fbbf24;letter-spacing:0.15em;margin:0;text-shadow:0 2px 4px rgba(0,0,0,0.5);">
                        <?php echo $ch_name; ?>
                    </h2>
                </div>

                <!-- 일러스트 영역 -->
                <div style="margin:0 0.75rem;height:14rem;background:#18181b;border:2px solid #92400e;overflow:hidden;position:relative;display:flex;align-items:center;justify-content:center;">
                    <?php if ($char_image) { ?>
                    <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;">
                    <?php } else { ?>
                    <span class="lc-fantasy" style="font-size:4rem;color:#44403c;"><?php echo $ch_initial; ?></span>
                    <?php } ?>
                    <div style="position:absolute;inset:0;background:linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 40%);"></div>
                </div>

                <!-- 서브타이틀 리본 -->
                <div style="position:relative;z-index:10;display:flex;justify-content:center;margin-top:-0.75rem;">
                    <div style="background:#78350f;border:1px solid #d97706;border-radius:0.25rem;padding:0.125rem 1rem;box-shadow:0 4px 6px rgba(0,0,0,0.3);">
                        <span style="font-size:0.75rem;color:#fef3c7;font-weight:bold;letter-spacing:0.1em;"><?php echo $card_subtitle; ?></span>
                    </div>
                </div>

                <!-- 플레이버 텍스트 영역 -->
                <div style="flex:1;margin:0.75rem;background:#e7e5e4;border-radius:0.25rem;color:#27272a;padding:1rem;border:2px solid rgba(139,105,20,0.4);position:relative;box-shadow:inset 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- 배경 워터마크 -->
                    <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;pointer-events:none;opacity:0.04;">
                        <span class="lc-fantasy" style="font-size:6rem;">M</span>
                    </div>

                    <!-- 업적 쇼케이스 -->
                    <?php if (!empty($achievement_showcase)) { ?>
                    <div style="margin-bottom:0.75rem;text-align:center;position:relative;z-index:1;">
                        <div style="display:flex;gap:0.375rem;flex-wrap:wrap;justify-content:center;">
                            <?php foreach ($achievement_showcase as $acd) {
                                $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                                $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                            ?>
                            <span class="lc-badge" style="display:inline-flex;align-items:center;gap:0.25rem;background:#44403c;color:#fef3c7;border-radius:9999px;padding:0.125rem 0.5rem;font-size:0.625rem;" title="<?php echo $a_name; ?>">
                                <?php if ($a_icon) { ?>
                                <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:12px;height:12px;object-fit:contain;">
                                <?php } ?>
                                <?php echo $a_name; ?>
                            </span>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>

                    <div style="text-align:center;position:relative;z-index:1;">
                        <p style="font-size:0.8125rem;line-height:1.5;font-weight:bold;">
                            <?php echo $ch_name; ?> &mdash; <?php echo $card_subtitle; ?>
                        </p>
                        <p style="font-size:0.75rem;font-style:italic;color:#57534e;margin-top:0.5rem;">
                            "@<?php echo $ch_owner; ?>" &middot; <?php echo $ch_date; ?>
                        </p>
                    </div>
                </div>

                <!-- 스탯 보석 (하단) -->
                <?php if ($_battle_use == '1' && $battle_stat) {
                    $_stat_base = (int)mg_config('battle_stat_base', '5');
                    $bs_hp = (int)($battle_stat['stat_hp'] ?? $_stat_base);
                    $bs_str = (int)($battle_stat['stat_str'] ?? $_stat_base);
                    $bs_dex = (int)($battle_stat['stat_dex'] ?? $_stat_base);
                    $bs_int = (int)($battle_stat['stat_int'] ?? $_stat_base);
                    $bs_stress = (int)($battle_stat['stat_stress'] ?? 0);
                ?>
                <div style="position:absolute;bottom:-1.5rem;left:0;right:0;display:flex;justify-content:space-between;padding:0 0.5rem;z-index:20;">
                    <!-- HP 보석 (큰 보석) -->
                    <div class="lc-gem" style="width:3.5rem;height:4rem;border-radius:50%;background:#dc2626;border:2px solid #fbbf24;display:flex;flex-direction:column;align-items:center;justify-content:center;padding-bottom:0.25rem;">
                        <span style="font-size:0.5rem;color:#fca5a5;font-weight:bold;line-height:1;margin-top:0.125rem;">HP</span>
                        <?php if ($battle_hp && $battle_hp['max_hp'] > 0) { ?>
                        <span class="lc-fantasy" style="font-size:0.875rem;color:#ffffff;font-weight:bold;line-height:1;margin-top:0.125rem;"><?php echo (int)$battle_hp['current_hp']; ?></span>
                        <span style="font-size:0.5rem;color:#fca5a5;line-height:1;">/<?php echo (int)$battle_hp['max_hp']; ?></span>
                        <?php } else { ?>
                        <span class="lc-fantasy" style="font-size:1.125rem;color:#ffffff;font-weight:bold;line-height:1;margin-top:0.125rem;"><?php echo $bs_hp; ?></span>
                        <?php } ?>
                    </div>

                    <!-- 중간 스탯 보석들 -->
                    <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                        <div class="lc-gem" style="width:3rem;height:3rem;border-radius:50%;background:#ea580c;border:2px solid #fbbf24;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <span style="font-size:0.5rem;color:#fed7aa;font-weight:bold;line-height:1;">STR</span>
                            <span class="lc-fantasy" style="font-size:1rem;color:#ffffff;font-weight:bold;line-height:1;margin-top:0.125rem;"><?php echo $bs_str; ?></span>
                        </div>
                        <div class="lc-gem" style="width:3rem;height:3rem;border-radius:50%;background:#16a34a;border:2px solid #fbbf24;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                            <span style="font-size:0.5rem;color:#bbf7d0;font-weight:bold;line-height:1;">DEX</span>
                            <span class="lc-fantasy" style="font-size:1rem;color:#ffffff;font-weight:bold;line-height:1;margin-top:0.125rem;"><?php echo $bs_dex; ?></span>
                        </div>
                    </div>

                    <!-- INT 보석 (큰 보석) -->
                    <div class="lc-gem" style="width:3.5rem;height:4rem;border-radius:50%;background:#2563eb;border:2px solid #fbbf24;display:flex;flex-direction:column;align-items:center;justify-content:center;padding-bottom:0.25rem;">
                        <span style="font-size:0.5rem;color:#bfdbfe;font-weight:bold;line-height:1;margin-top:0.125rem;">INT</span>
                        <span class="lc-fantasy" style="font-size:1.125rem;color:#ffffff;font-weight:bold;line-height:1;margin-top:0.125rem;"><?php echo $bs_int; ?></span>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 액션 버튼 -->
    <div style="display:flex;gap:0.5rem;justify-content:center;margin:2.5rem 0 1.5rem;">
        <?php if ($can_request_relation) { ?>
        <button type="button" onclick="openRelRequestModal()" class="lc-fantasy" style="font-size:0.75rem;background:rgba(251,191,36,0.2);border:1px solid #fbbf24;color:#fde68a;padding:0.375rem 1rem;border-radius:0.375rem;cursor:pointer;">관계 신청</button>
        <?php } ?>
        <?php if ($is_owner) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="lc-fantasy" style="font-size:0.75rem;background:rgba(100,116,139,0.2);border:1px solid #475569;color:#94a3b8;padding:0.375rem 1rem;border-radius:0.375rem;">수정</a>
        <?php } ?>
    </div>

    <!-- 프로필 필드 -->
    <?php if (count($grouped_fields) > 0) { ?>
    <?php foreach ($grouped_fields as $category => $fields) { ?>
    <div style="margin-bottom:1.5rem;background:rgba(39,39,42,0.6);border:1px solid rgba(251,191,36,0.15);border-radius:0.5rem;padding:1.25rem;">
        <h3 class="lc-fantasy" style="font-size:0.875rem;letter-spacing:0.15em;text-transform:uppercase;color:#fbbf24;margin:0 0 1rem;text-align:center;"><?php echo htmlspecialchars($category); ?></h3>
        <?php foreach ($fields as $field) { ?>
        <div style="margin-bottom:0.75rem;">
            <div style="font-size:0.6875rem;color:#92400e;text-transform:uppercase;letter-spacing:0.1em;font-weight:bold;" class="lc-fantasy"><?php echo htmlspecialchars($field['pf_name']); ?></div>
            <div style="font-size:0.9375rem;color:#e5e7eb;margin-top:0.125rem;line-height:1.6;">
                <?php echo mg_render_profile_value($field); ?>
            </div>
        </div>
        <?php } ?>
    </div>
    <div class="lc-divider"><span>&#10022;</span></div>
    <?php } ?>
    <?php } ?>

    <!-- 관계 -->
    <?php if (!empty($char_relations)) { ?>
    <div style="background:rgba(39,39,42,0.6);border:1px solid rgba(251,191,36,0.15);border-radius:0.5rem;padding:1.25rem;margin-bottom:1.5rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 class="lc-fantasy" style="font-size:0.875rem;letter-spacing:0.15em;text-transform:uppercase;color:#fbbf24;margin:0;">관계</h3>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <?php if ($is_owner) { ?>
                <button type="button" id="rel-graph-save" style="font-size:0.6875rem;color:#92400e;background:none;border:none;cursor:pointer;display:none;" class="lc-fantasy">배치 저장</button>
                <?php } ?>
                <button type="button" id="rel-graph-toggle" style="font-size:0.6875rem;color:#fbbf24;background:none;border:none;cursor:pointer;" class="lc-fantasy">관계도 보기</button>
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
        <div class="lc-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid rgba(251,191,36,0.1);font-size:0.875rem;">
            <?php if ($other_thumb) { ?>
            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid rgba(251,191,36,0.3);">
            <?php } else { ?>
            <div style="width:32px;height:32px;border-radius:50%;background:rgba(251,191,36,0.1);display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:bold;font-size:0.75rem;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
            <?php } ?>
            <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="color:#92400e;font-style:italic;"><?php echo $my_label; ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:600;"><?php echo $other_name; ?></a>
                </div>
                <?php if ($my_memo) { ?>
                <p style="font-size:0.75rem;color:#78350f;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($my_memo); ?></p>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <!-- 인라인 관계도 -->
        <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
            <div id="rel-graph-container" style="height:400px;background:#1a1a2e;border-radius:0.5rem;border:1px solid rgba(251,191,36,0.2);"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 푸터 -->
    <div style="text-align:center;font-size:0.6875rem;color:rgba(251,191,36,0.3);padding:0.5rem 0;" class="lc-fantasy">
        @<?php echo $ch_owner; ?> &middot; <?php echo $ch_date; ?>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
