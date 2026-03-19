<?php
/**
 * Morgan Edition - 프로필 스킨: 클래식 JRPG
 * 검정 배경, 파란 그라데이션 박스, NeoDunggeunmo 픽셀 폰트, 깜빡이는 커서
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);
?>

<style>
@import url('https://cdn.jsdelivr.net/gh/neodgm/neodgm-webfont@latest/neodgm/style.css');

.skin-jrpg { font-family: 'NeoDunggeunmo', 'DungGeunMo', monospace; color: #ffffff; }
.skin-jrpg a { color: #fde68a; text-decoration: none; }
.skin-jrpg a:hover { color: #fbbf24; }
.skin-jrpg .jrpg-box {
    background: linear-gradient(to bottom, #0000aa 0%, #000055 100%);
    border: 4px solid #ffffff;
    border-radius: 8px;
    box-shadow:
        inset 2px 2px 0px rgba(255,255,255,0.3),
        inset -2px -2px 0px rgba(0,0,0,0.5),
        4px 4px 0px #222222;
}
.skin-jrpg .jrpg-cursor {
    display: inline-block;
    width: 0; height: 0;
    border-top: 8px solid transparent;
    border-bottom: 8px solid transparent;
    border-left: 12px solid #ffffff;
    animation: jrpg-blink 1s step-end infinite;
    margin-right: 8px;
    flex-shrink: 0;
}
@keyframes jrpg-blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
.skin-jrpg .jrpg-hp-bar {
    width: 100%;
    height: 12px;
    background: #000000;
    border: 2px solid #888888;
    padding: 1px;
    border-radius: 0;
}
.skin-jrpg .jrpg-hp-fill {
    height: 100%;
    transition: width 0.3s;
}
.skin-jrpg .jrpg-divider {
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    margin: 0.5rem 0;
}

/* 메뉴 항목 호버 */
.skin-jrpg .jrpg-menu-item { transition: color 0.15s; cursor: pointer; }
.skin-jrpg .jrpg-menu-item:hover { color: #fde68a; }

/* 관계 항목 호버 */
.skin-jrpg .jrpg-rel-item { transition: background 0.15s; padding: 0.375rem 0.5rem; border-left: 3px solid transparent; }
.skin-jrpg .jrpg-rel-item:hover { background: rgba(255,255,255,0.08); border-left-color: #fde68a; }

/* 뱃지 호버 */
.skin-jrpg .jrpg-badge { transition: all 0.2s; }
.skin-jrpg .jrpg-badge:hover { transform: scale(1.08); box-shadow: 0 0 8px rgba(253,224,71,0.4); }

/* 버튼 호버 */
.skin-jrpg button, .skin-jrpg a.jrpg-btn { transition: all 0.2s; }
.skin-jrpg button:hover, .skin-jrpg a.jrpg-btn:hover {
    background: rgba(255,255,255,0.15) !important;
    transform: translateY(-1px);
}
</style>

<div class="mg-inner skin-jrpg" style="max-width:800px;background:#000000;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:1rem;color:#fde68a;margin-bottom:1rem;">
        <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
        <span>뒤로</span>
    </a>

    <!-- 헤더 배너 -->
    <?php if ($char_header) { ?>
    <div class="jrpg-box" style="padding:0;margin-bottom:1rem;overflow:hidden;">
        <div style="max-height:10rem;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:contrast(120%) brightness(80%);">
        </div>
    </div>
    <?php } ?>

    <!-- 캐릭터 정보 박스 (상단) -->
    <div class="jrpg-box" style="padding:1.25rem;display:flex;align-items:flex-start;gap:1.5rem;margin-bottom:1rem;">
        <!-- 초상화 -->
        <div style="width:96px;height:96px;border:2px solid #ffffff;background:#000000;flex-shrink:0;overflow:hidden;display:flex;align-items:center;justify-content:center;">
            <?php if ($char_image) { ?>
            <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;">
            <?php } else { ?>
            <span style="font-size:2rem;color:#fde68a;"><?php echo $ch_initial; ?></span>
            <?php } ?>
        </div>

        <!-- 이름/직업/상태 -->
        <div style="flex:1;min-width:0;">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;border-bottom:2px solid rgba(255,255,255,0.3);padding-bottom:0.5rem;margin-bottom:0.5rem;">
                <h2 style="font-size:1.75rem;letter-spacing:0.15em;color:#fde68a;margin:0;"><?php echo $ch_name; ?></h2>
                <span style="font-size:1.125rem;color:#ffffff;">@<?php echo $ch_owner; ?></span>
            </div>
            <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
            <p style="font-size:1.125rem;color:#d1d5db;letter-spacing:0.1em;margin:0.25rem 0;">직업 : <?php echo $ch_class; ?></p>
            <?php } ?>
            <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
            <p style="font-size:1.125rem;color:#d1d5db;letter-spacing:0.1em;margin:0.25rem 0;">세력 : <?php echo $ch_side; ?></p>
            <?php } ?>
            <p style="font-size:1.125rem;color:#d1d5db;letter-spacing:0.1em;margin:0.25rem 0;">
                상태 : <span style="color:<?php echo $char['ch_state'] == 'approved' ? '#4ade80' : '#f59e0b'; ?>;"><?php echo $char['ch_state'] == 'approved' ? '활동중' : htmlspecialchars($char['ch_state']); ?></span>
            </p>
        </div>
    </div>

    <!-- 2단 레이아웃: 메뉴 + 스탯 -->
    <div style="display:flex;gap:1rem;margin-bottom:1rem;">
        <!-- 메뉴 패널 -->
        <div class="jrpg-box" style="padding:1rem;width:33%;display:flex;flex-direction:column;gap:0.75rem;font-size:1.125rem;letter-spacing:0.1em;">
            <div class="jrpg-menu-item" style="display:flex;align-items:center;"><span class="jrpg-cursor"></span>상태</div>
            <?php if ($can_request_relation) { ?>
            <div class="jrpg-menu-item" style="display:flex;align-items:center;padding-left:1.25rem;color:#d1d5db;cursor:pointer;" onclick="openRelRequestModal()">관계 신청</div>
            <?php } ?>
            <?php if ($is_owner) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="jrpg-menu-item jrpg-btn" style="display:flex;align-items:center;padding-left:1.25rem;color:#d1d5db;">수정</a>
            <?php } ?>
            <div class="jrpg-menu-item" style="display:flex;align-items:center;padding-left:1.25rem;color:#6b7280;">장비</div>
            <div class="jrpg-menu-item" style="display:flex;align-items:center;padding-left:1.25rem;color:#6b7280;">소지품</div>
        </div>

        <!-- 스탯 패널 -->
        <div class="jrpg-box" style="padding:1.25rem;width:67%;">
            <?php if ($_battle_use == '1' && $battle_stat) {
                $_stat_base = (int)mg_config('battle_stat_base', '5');
                $bs_hp = (int)($battle_stat['stat_hp'] ?? $_stat_base);
                $bs_str = (int)($battle_stat['stat_str'] ?? $_stat_base);
                $bs_dex = (int)($battle_stat['stat_dex'] ?? $_stat_base);
                $bs_int = (int)($battle_stat['stat_int'] ?? $_stat_base);
                $bs_stress = (int)($battle_stat['stat_stress'] ?? 0);
                $stress_color = $bs_stress >= 100 ? '#ef4444' : ($bs_stress >= 70 ? '#f59e0b' : '#22c55e');
            ?>
            <h3 style="font-size:1.125rem;color:#fde68a;margin:0 0 1rem;letter-spacing:0.15em;">&#9654; 전투 능력치</h3>

            <?php if ($battle_hp && $battle_hp['max_hp'] > 0) {
                $_hp_pct = round($battle_hp['current_hp'] / $battle_hp['max_hp'] * 100);
                $_hp_color = $_hp_pct > 60 ? '#22c55e' : ($_hp_pct > 30 ? '#f59e0b' : '#ef4444');
            ?>
            <div style="margin-bottom:1rem;">
                <div style="display:flex;justify-content:space-between;margin-bottom:0.25rem;font-size:1.125rem;letter-spacing:0.1em;">
                    <span>HP</span>
                    <span style="color:<?php echo $_hp_color; ?>;"><?php echo (int)$battle_hp['current_hp']; ?> / <?php echo (int)$battle_hp['max_hp']; ?></span>
                </div>
                <div class="jrpg-hp-bar">
                    <div class="jrpg-hp-fill" style="width:<?php echo $_hp_pct; ?>%;background:<?php echo $_hp_color; ?>;"></div>
                </div>
            </div>
            <?php } ?>

            <div class="jrpg-divider"></div>

            <div style="font-size:1.125rem;letter-spacing:0.1em;">
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.375rem 0;">
                    <span>HP (체력)</span>
                    <span style="color:#fde68a;"><?php echo $bs_hp; ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.375rem 0;">
                    <span style="color:#fdba74;">STR (힘)</span>
                    <span><?php echo $bs_str; ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.375rem 0;">
                    <span style="color:#86efac;">DEX (민첩)</span>
                    <span><?php echo $bs_dex; ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.375rem 0;">
                    <span style="color:#93c5fd;">INT (지능)</span>
                    <span><?php echo $bs_int; ?></span>
                </div>
            </div>

            <div class="jrpg-divider"></div>

            <div style="display:flex;justify-content:space-between;align-items:center;padding:0.375rem 0;font-size:1rem;">
                <span style="color:#d1d5db;">스트레스</span>
                <span style="color:<?php echo $stress_color; ?>;"><?php echo $bs_stress; ?>%</span>
            </div>
            <div class="jrpg-hp-bar">
                <div class="jrpg-hp-fill" style="width:<?php echo min($bs_stress, 100); ?>%;background:<?php echo $stress_color; ?>;"></div>
            </div>

            <?php } else { ?>
            <h3 style="font-size:1.125rem;color:#fde68a;margin:0 0 1rem;letter-spacing:0.15em;">&#9654; 캐릭터 정보</h3>
            <p style="color:#9ca3af;font-size:1rem;">전투 데이터 없음</p>
            <?php } ?>
        </div>
    </div>

    <!-- 업적 쇼케이스 -->
    <?php if (!empty($achievement_showcase)) { ?>
    <div class="jrpg-box" style="padding:1rem;margin-bottom:1rem;">
        <h3 style="font-size:1.125rem;color:#fde68a;margin:0 0 0.75rem;letter-spacing:0.15em;">&#9654; 업적</h3>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <?php foreach ($achievement_showcase as $acd) {
                $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
            ?>
            <span class="jrpg-badge" style="display:inline-flex;align-items:center;gap:0.375rem;background:rgba(0,0,0,0.4);border:2px solid rgba(255,255,255,0.3);border-radius:4px;padding:0.25rem 0.75rem;font-size:0.875rem;color:#fde68a;" title="<?php echo $a_name; ?>">
                <?php if ($a_icon) { ?>
                <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:16px;height:16px;object-fit:contain;">
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
    <div class="jrpg-box" style="padding:1rem;margin-bottom:1rem;">
        <h3 style="font-size:1.125rem;color:#fde68a;margin:0 0 0.75rem;letter-spacing:0.15em;">&#9654; <?php echo htmlspecialchars($category); ?></h3>
        <?php foreach ($fields as $field) { ?>
        <div style="margin-bottom:0.75rem;">
            <div style="font-size:0.875rem;color:#93c5fd;letter-spacing:0.05em;margin-bottom:0.125rem;"><?php echo htmlspecialchars($field['pf_name']); ?></div>
            <div style="font-size:1rem;color:#e5e7eb;line-height:1.6;">
                <?php echo mg_render_profile_value($field); ?>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
    <?php } ?>

    <!-- 관계 -->
    <?php if (!empty($char_relations)) { ?>
    <div class="jrpg-box" style="padding:1rem;margin-bottom:1rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
            <h3 style="font-size:1.125rem;color:#fde68a;margin:0;letter-spacing:0.15em;">&#9654; 관계</h3>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <?php if ($is_owner) { ?>
                <button type="button" id="rel-graph-save" style="font-size:0.875rem;color:#9ca3af;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                <?php } ?>
                <button type="button" id="rel-graph-toggle" style="font-size:0.875rem;color:#fde68a;background:none;border:none;cursor:pointer;">관계도 보기</button>
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
        <div class="jrpg-rel-item" style="display:flex;align-items:center;gap:0.75rem;border-bottom:2px solid rgba(255,255,255,0.1);font-size:1rem;">
            <?php if ($other_thumb) { ?>
            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border:2px solid #fff;object-fit:cover;">
            <?php } else { ?>
            <div style="width:32px;height:32px;background:#000;border:2px solid #fff;display:flex;align-items:center;justify-content:center;color:#fde68a;font-size:0.875rem;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
            <?php } ?>
            <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="color:#93c5fd;"><?php echo $my_label; ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;"><?php echo $other_name; ?></a>
                </div>
                <?php if ($my_memo) { ?>
                <p style="font-size:0.75rem;color:#60a5fa;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($my_memo); ?></p>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
        <!-- 인라인 관계도 -->
        <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;">
            <div id="rel-graph-container" style="height:400px;background:#000022;border:4px solid #ffffff;border-radius:8px;"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 푸터 -->
    <div style="text-align:center;font-size:0.875rem;color:#6b7280;padding:0.5rem 0;">
        @<?php echo $ch_owner; ?> &middot; <?php echo $ch_date; ?>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
