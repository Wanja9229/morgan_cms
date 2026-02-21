<?php
/**
 * Morgan Edition - 프로필 스킨: 군 인사기록
 * 올리브/카키, 밀리터리, 도장, 기밀등급 바
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$serial = strtoupper(substr(md5($char['ch_id'] . $char['ch_name']), 0, 8));
?>

<style>
.skin-mil { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; color: #1c1917; }
.skin-mil a { color: #78350f; }
.skin-mil a:hover { text-decoration: underline; }
.skin-mil .mil-stamp {
    position: absolute; top: 1.5rem; right: 1.5rem;
    border: 3px solid rgba(22,101,52,0.6); border-radius: 50%;
    width: 5rem; height: 5rem; display: flex; align-items: center; justify-content: center;
    transform: rotate(-15deg); color: rgba(22,101,52,0.6);
    font-weight: 900; font-size: 0.625rem; text-align: center; line-height: 1.2;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.skin-mil .mil-bar {
    height: 6px; background: repeating-linear-gradient(90deg, #365314, #365314 20%, #84cc16 20%, #84cc16 21%);
}
.skin-mil .mil-section {
    border: 1px solid #d6d3d1; margin-bottom: 1rem; background: #fafaf9;
}
.skin-mil .mil-section-header {
    background: #44403c; color: #fafaf9; padding: 0.375rem 0.75rem;
    font-size: 0.6875rem; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 700;
}
.skin-mil .mil-row { display: flex; border-bottom: 1px solid #e7e5e4; }
.skin-mil .mil-row:last-child { border-bottom: none; }
.skin-mil .mil-label { width: 130px; flex-shrink: 0; padding: 0.375rem 0.75rem; background: #f5f5f4; color: #78716c; font-size: 0.75rem; font-weight: 600; border-right: 1px solid #e7e5e4; }
.skin-mil .mil-val { flex: 1; padding: 0.375rem 0.75rem; font-size: 0.8125rem; }

/* 호버 효과 */
.skin-mil .mil-stamp { transition: all 0.4s ease; }
.skin-mil .mil-stamp:hover { transform: rotate(-15deg) scale(1.15); color: rgba(22,101,52,0.8); border-color: rgba(22,101,52,0.8); }

.skin-mil .mil-row { transition: background 0.25s ease; }
.skin-mil .mil-row:hover { background: #f5f5f4; }

.skin-mil .mil-section { transition: border-color 0.3s ease; }
.skin-mil .mil-section:hover { border-color: #a8a29e; }

.skin-mil .mil-badge { transition: all 0.25s ease; }
.skin-mil .mil-badge:hover { transform: scale(1.05); background: #e7e5e4; }

/* 바코드 바 애니메이션 */
@keyframes mil-bar-shift { 0% { background-position: 0 0; } 100% { background-position: 100% 0; } }
.skin-mil .mil-bar { animation: mil-bar-shift 15s linear infinite; background-size: 200% 100%; }

/* 버튼 호버 */
.skin-mil button { transition: all 0.25s ease; }
.skin-mil button:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(54,83,20,0.3); }
</style>

<div class="mg-inner skin-mil">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#78716c;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div style="max-width:52rem;margin:0 auto;background:#fafaf9;border:2px solid #a8a29e;box-shadow:2px 2px 0 #78716c;">
        <!-- 기밀등급 바 -->
        <div class="mil-bar"></div>

        <!-- 헤더 -->
        <div style="background:#292524;color:#fafaf9;padding:0.75rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <span style="font-size:0.625rem;letter-spacing:0.2em;opacity:0.6;">MILITARY PERSONNEL FILE</span>
                <div style="font-size:1rem;font-weight:700;margin-top:0.125rem;">SERVICE RECORD — <?php echo $serial; ?></div>
            </div>
            <div style="text-align:right;font-size:0.625rem;opacity:0.6;">
                <div>CLASSIFICATION: RESTRICTED</div>
                <div>DATE: <?php echo $ch_date; ?></div>
            </div>
        </div>

        <?php if ($char_header) { ?>
        <div style="max-height:10rem;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:grayscale(70%) contrast(110%);">
        </div>
        <?php } ?>

        <div style="padding:1.5rem;position:relative;">
            <!-- 승인 도장 -->
            <div class="mil-stamp">
                <?php echo $char['ch_state'] == 'approved' ? 'CLEARED<br>FOR DUTY' : 'PENDING<br>REVIEW'; ?>
            </div>

            <!-- 신상정보 -->
            <div class="mil-section">
                <div class="mil-section-header">SECTION I — IDENTIFICATION</div>
                <div style="display:flex;gap:1.5rem;padding:1rem;" class="mil-id-area">
                    <?php if ($char_image) { ?>
                    <div style="width:100px;height:130px;flex-shrink:0;border:2px solid #a8a29e;background:#e7e5e4;">
                        <img src="<?php echo $char_image; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:contrast(110%);">
                    </div>
                    <?php } ?>
                    <div style="flex:1;">
                        <div class="mil-row">
                            <div class="mil-label">성명</div>
                            <div class="mil-val" style="font-weight:700;font-size:1rem;"><?php echo $ch_name; ?></div>
                        </div>
                        <div class="mil-row">
                            <div class="mil-label">군번</div>
                            <div class="mil-val"><?php echo $serial; ?>-<?php echo str_pad($char['ch_id'], 4, '0', STR_PAD_LEFT); ?></div>
                        </div>
                        <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                        <div class="mil-row">
                            <div class="mil-label">병과</div>
                            <div class="mil-val"><?php echo $ch_class; ?></div>
                        </div>
                        <?php } ?>
                        <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                        <div class="mil-row">
                            <div class="mil-label">소속</div>
                            <div class="mil-val"><?php echo $ch_side; ?></div>
                        </div>
                        <?php } ?>
                        <div class="mil-row">
                            <div class="mil-label">등록자</div>
                            <div class="mil-val">@<?php echo $ch_owner; ?></div>
                        </div>
                    </div>
                </div>
                <!-- 액션 -->
                <div style="padding:0.5rem 1rem;background:#f5f5f4;border-top:1px solid #e7e5e4;display:flex;gap:0.5rem;">
                    <?php if ($can_request_relation) { ?>
                    <button type="button" onclick="openRelRequestModal()" style="background:#365314;color:#fafaf9;border:none;padding:0.25rem 0.75rem;font-size:0.75rem;cursor:pointer;font-family:inherit;">관계 신청</button>
                    <?php } ?>
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="border:1px solid #a8a29e;padding:0.25rem 0.75rem;font-size:0.75rem;color:#78716c;text-decoration:none;font-family:inherit;">수정</a>
                    <?php } ?>
                </div>
            </div>

            <!-- 업적/훈장 -->
            <?php if (!empty($achievement_showcase)) { ?>
            <div class="mil-section">
                <div class="mil-section-header">SECTION II — DECORATIONS &amp; AWARDS</div>
                <div style="padding:0.75rem 1rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
                    <?php foreach ($achievement_showcase as $acd) {
                        $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                        $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                    ?>
                    <span class="mil-badge" style="display:inline-flex;align-items:center;gap:0.25rem;background:#f5f5f4;border:1px solid #d6d3d1;padding:0.25rem 0.5rem;font-size:0.6875rem;" title="<?php echo $a_name; ?>">
                        <?php if ($a_icon) { ?>
                        <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:16px;height:16px;object-fit:contain;">
                        <?php } else { ?>
                        <span style="color:#365314;">&#9733;</span>
                        <?php } ?>
                        <?php echo $a_name; ?>
                    </span>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 프로필 필드 -->
            <?php if (count($grouped_fields) > 0) { ?>
            <?php $section_num = 3; ?>
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div class="mil-section">
                <div class="mil-section-header">SECTION <?php echo $section_num > 9 ? $section_num : ('0'.$section_num); $section_num++; ?> — <?php echo strtoupper(htmlspecialchars($category)); ?></div>
                <?php foreach ($fields as $field) { ?>
                <div class="mil-row">
                    <div class="mil-label"><?php echo htmlspecialchars($field['pf_name']); ?></div>
                    <div class="mil-val">
                        <?php
                        if ($field['pf_type'] == 'url') {
                            echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank">'.htmlspecialchars($field['pv_value']).'</a>';
                        } elseif ($field['pf_type'] == 'textarea') {
                            echo nl2br(htmlspecialchars($field['pv_value']));
                        } else {
                            echo htmlspecialchars($field['pv_value']);
                        }
                        ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
            <?php } ?>

            <!-- 관계 -->
            <?php if (!empty($char_relations)) { ?>
            <div class="mil-section">
                <div class="mil-section-header" style="display:flex;justify-content:space-between;align-items:center;">
                    <span>KNOWN ASSOCIATES</span>
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                        <?php if ($is_owner) { ?>
                        <button type="button" id="rel-graph-save" style="font-size:0.625rem;color:#a8a29e;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                        <?php } ?>
                        <button type="button" id="rel-graph-toggle" style="font-size:0.625rem;color:#84cc16;background:none;border:none;cursor:pointer;">관계도 보기</button>
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
                <div class="mil-row" style="align-items:center;gap:0.75rem;">
                    <?php if ($other_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:28px;height:28px;border-radius:2px;object-fit:cover;margin-left:0.75rem;filter:grayscale(60%);">
                    <?php } else { ?>
                    <div style="width:28px;height:28px;background:#e7e5e4;display:flex;align-items:center;justify-content:center;font-size:0.625rem;color:#78716c;margin-left:0.75rem;">?</div>
                    <?php } ?>
                    <div style="flex:1;padding:0.375rem 0.5rem;display:flex;align-items:center;gap:0.5rem;">
                        <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                        <span style="color:#78716c;font-size:0.75rem;"><?php echo $my_label; ?></span>
                        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-size:0.8125rem;font-weight:600;"><?php echo $other_name; ?></a>
                    </div>
                </div>
                <?php } ?>
            </div>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:-1rem;margin-bottom:1rem;">
                <div id="rel-graph-container" style="height:400px;background:#1c1917;border:1px solid #a8a29e;"></div>
            </div>
            <?php } ?>
        </div>

        <!-- 푸터 -->
        <div style="background:#292524;color:#78716c;padding:0.5rem 1.5rem;font-size:0.625rem;display:flex;justify-content:space-between;">
            <span>MORGAN EDITION COMMAND — PERSONNEL DIVISION</span>
            <span>PAGE 1 OF 1</span>
        </div>
        <div class="mil-bar"></div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (max-width: 640px) {
    .skin-mil .mil-id-area { flex-direction: column; align-items: center; }
    .skin-mil .mil-label { width: 90px; font-size: 0.6875rem; }
    .skin-mil .mil-stamp { width: 3.5rem; height: 3.5rem; font-size: 0.5rem; top: 1rem; right: 1rem; }
}
</style>
