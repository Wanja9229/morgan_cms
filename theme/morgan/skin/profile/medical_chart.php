<?php
/**
 * Morgan Edition - 프로필 스킨: 의료 차트
 * 클린 화이트, 민트 악센트, 클립보드 레이아웃
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
.skin-med { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #1e293b; }
.skin-med a { color: #0d9488; }
.skin-med a:hover { text-decoration: underline; }
.skin-med .med-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
.skin-med .med-clip {
    background: #78350f; width: 80px; height: 24px; border-radius: 4px 4px 0 0;
    margin: 0 auto; position: relative; z-index: 2;
}
.skin-med .med-clip-inner {
    background: #92400e; width: 40px; height: 12px; border-radius: 0 0 8px 8px;
    margin: 0 auto; position: relative; top: -1px;
}
.skin-med .med-row { display: flex; border-bottom: 1px solid #e2e8f0; }
.skin-med .med-label {
    width: 140px; flex-shrink: 0; padding: 0.5rem 0.75rem; background: #f1f5f9;
    font-size: 0.8125rem; font-weight: 600; color: #64748b; border-right: 1px solid #e2e8f0;
}
.skin-med .med-value { flex: 1; padding: 0.5rem 0.75rem; font-size: 0.875rem; }
.skin-med .med-vitals {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.75rem; margin: 1rem 0;
}
.skin-med .med-vital-box {
    background: #f0fdfa; border: 1px solid #99f6e4; border-radius: 0.5rem;
    padding: 0.75rem; text-align: center;
}
.skin-med .med-vital-num { font-size: 1.5rem; font-weight: 800; color: #0d9488; }
.skin-med .med-vital-label { font-size: 0.6875rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }

/* 호버 효과 */
.skin-med .med-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-med .med-rel-item:hover { background: #f0fdfa; border-left-color: #0d9488; }

.skin-med .med-row { transition: background 0.25s ease; }
.skin-med .med-row:hover { background: #f0fdfa; }

.skin-med .med-badge { transition: all 0.25s ease; }
.skin-med .med-badge:hover { transform: scale(1.05); box-shadow: 0 0 10px rgba(13,148,136,0.2); }

/* 초상화 호버 */
.skin-med .med-photo { transition: all 0.4s ease; overflow: hidden; }
.skin-med .med-photo img { transition: transform 0.4s ease; }
.skin-med .med-photo:hover img { transform: scale(1.05); }
.skin-med .med-photo:hover { border-color: #0d9488 !important; }

/* 바이탈 사인 펄스 애니메이션 */
@keyframes med-pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }
.skin-med .med-clip { animation: med-pulse 3s ease-in-out infinite; }

/* 버튼 호버 */
.skin-med button { transition: all 0.25s ease; }
.skin-med button:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(13,148,136,0.3); }

/* 헤더 배너 */
.skin-med [style*="REFERENCE IMAGE"] { transition: opacity 0.3s ease; }
</style>

<div class="mg-inner skin-med" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#64748b;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <!-- 클립보드 -->
    <div style="margin:0 auto;">
        <div class="med-clip"></div>
        <div class="med-clip-inner"></div>

        <div style="background:#ffffff;border:2px solid #cbd5e1;border-radius:0.5rem;box-shadow:0 4px 24px rgba(0,0,0,0.08);overflow:hidden;position:relative;margin-top:-4px;">
            <!-- 헤더 -->
            <div style="background:linear-gradient(135deg,#f0fdfa,#ecfdf5);border-bottom:2px solid #0d9488;padding:1.25rem 1.5rem;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="font-size:0.6875rem;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;" class="med-mono">PATIENT RECORD</div>
                        <h1 style="font-size:1.75rem;font-weight:800;margin:0.25rem 0;"><?php echo $ch_name; ?></h1>
                        <div style="font-size:0.8125rem;color:#64748b;">담당: @<?php echo $ch_owner; ?> | ID: #<?php echo str_pad($char['ch_id'], 6, '0', STR_PAD_LEFT); ?></div>
                    </div>
                    <div style="display:flex;gap:0.5rem;">
                        <?php if ($can_request_relation) { ?>
                        <button type="button" onclick="openRelRequestModal()" style="background:#0d9488;color:#fff;border:none;border-radius:0.375rem;padding:0.375rem 1rem;font-size:0.8125rem;cursor:pointer;">관계 신청</button>
                        <?php } ?>
                        <?php if ($is_owner) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="border:1px solid #cbd5e1;border-radius:0.375rem;padding:0.375rem 1rem;font-size:0.8125rem;color:#64748b;text-decoration:none;">수정</a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php if ($char_header) { ?>
            <div style="margin:0 1rem 1rem;border:1px solid #e2e8f0;overflow:hidden;max-height:12rem;position:relative;">
                <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;bottom:0;left:0;background:#f0fdfa;padding:0.125rem 0.5rem;font-size:0.6875rem;color:#0d9488;border-top:1px solid #e2e8f0;border-right:1px solid #e2e8f0;">REFERENCE IMAGE</div>
            </div>
            <?php } ?>

            <div style="display:grid;grid-template-columns:1fr;gap:0;" class="med-grid">
                <!-- 좌측: 사진 + 기본정보 -->
                <div style="padding:1.5rem;border-bottom:1px solid #e2e8f0;">
                    <div style="display:flex;gap:1.5rem;align-items:flex-start;" class="med-top">
                        <?php if ($char_image) { ?>
                        <div class="med-photo" style="width:120px;height:160px;flex-shrink:0;border:2px solid #e2e8f0;border-radius:0.375rem;overflow:hidden;">
                            <img src="<?php echo $char_image; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                        </div>
                        <?php } ?>

                        <div style="flex:1;">
                            <!-- 바이탈 사인 (장식) -->
                            <div class="med-vitals">
                                <div class="med-vital-box">
                                    <div class="med-vital-num"><?php echo count($char_relations); ?></div>
                                    <div class="med-vital-label">관계</div>
                                </div>
                                <div class="med-vital-box">
                                    <div class="med-vital-num"><?php echo count($achievement_showcase); ?></div>
                                    <div class="med-vital-label">업적</div>
                                </div>
                                <div class="med-vital-box">
                                    <div class="med-vital-num"><?php echo $ch_date; ?></div>
                                    <div class="med-vital-label">등록일</div>
                                </div>
                            </div>

                            <!-- 기본 필드 테이블 -->
                            <div style="border:1px solid #e2e8f0;border-radius:0.375rem;overflow:hidden;">
                                <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                                <div class="med-row">
                                    <div class="med-label"><?php echo htmlspecialchars(mg_config('side_title', '소속')); ?></div>
                                    <div class="med-value"><?php echo $ch_side; ?></div>
                                </div>
                                <?php } ?>
                                <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                                <div class="med-row">
                                    <div class="med-label"><?php echo htmlspecialchars(mg_config('class_title', '유형')); ?></div>
                                    <div class="med-value"><?php echo $ch_class; ?></div>
                                </div>
                                <?php } ?>
                                <div class="med-row" style="border-bottom:none;">
                                    <div class="med-label">상태</div>
                                    <div class="med-value">
                                        <span style="display:inline-flex;align-items:center;gap:0.375rem;">
                                            <span style="width:8px;height:8px;border-radius:50%;background:<?php echo $char['ch_state'] == 'approved' ? '#22c55e' : '#eab308'; ?>;"></span>
                                            <?php echo $char['ch_state'] == 'approved' ? 'Active' : 'Under Review'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 업적 -->
                <?php if (!empty($achievement_showcase)) { ?>
                <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;">
                    <div style="font-size:0.6875rem;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;font-weight:600;" class="med-mono">ACHIEVEMENTS</div>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                        <?php foreach ($achievement_showcase as $acd) {
                            $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                            $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                        ?>
                        <span class="med-badge" style="display:inline-flex;align-items:center;gap:0.375rem;background:#f0fdfa;border:1px solid #99f6e4;border-radius:0.25rem;padding:0.25rem 0.625rem;font-size:0.75rem;color:#0d9488;" title="<?php echo $a_name; ?>">
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
                <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;">
                    <div style="font-size:0.6875rem;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:0.75rem;font-weight:600;" class="med-mono"><?php echo htmlspecialchars($category); ?></div>
                    <div style="border:1px solid #e2e8f0;border-radius:0.375rem;overflow:hidden;">
                        <?php foreach ($fields as $i => $field) { ?>
                        <div class="med-row" <?php echo $i === count($fields) - 1 ? 'style="border-bottom:none;"' : ''; ?>>
                            <div class="med-label"><?php echo htmlspecialchars($field['pf_name']); ?></div>
                            <div class="med-value">
                                <?php echo mg_render_profile_value($field); ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php } ?>

                <!-- 관계 -->
                <?php if (!empty($char_relations)) { ?>
                <div style="padding:1rem 1.5rem;border-bottom:1px solid #e2e8f0;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
                        <div style="font-size:0.6875rem;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;font-weight:600;" class="med-mono">RELATIONS</div>
                        <div style="display:flex;gap:0.5rem;align-items:center;">
                            <?php if ($is_owner) { ?>
                            <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#64748b;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                            <?php } ?>
                            <button type="button" id="rel-graph-toggle" style="font-size:0.8125rem;color:#0d9488;background:none;border:none;cursor:pointer;">관계도 보기</button>
                        </div>
                    </div>
                    <div style="border:1px solid #e2e8f0;border-radius:0.375rem;overflow:hidden;">
                    <?php foreach ($char_relations as $i => $rel) {
                        $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                        $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                        $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                        $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                        $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                        $rel_color = $rel['cr_color'] ?: '#95a5a6';
                    ?>
                    <div class="med-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0.75rem;<?php echo $i < count($char_relations) - 1 ? 'border-bottom:1px solid #e2e8f0;' : ''; ?>">
                        <?php if ($other_thumb) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                        <?php } else { ?>
                        <div style="width:32px;height:32px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;color:#64748b;font-weight:700;font-size:0.75rem;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                        <?php } ?>
                        <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                        <span style="font-size:0.8125rem;color:#64748b;"><?php echo $my_label; ?></span>
                        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:600;font-size:0.875rem;"><?php echo $other_name; ?></a>
                    </div>
                    <?php } ?>
                    </div>
                    <!-- 인라인 관계도 -->
                    <div id="rel-graph-wrap" class="hidden" style="margin-top:0.75rem;">
                        <div id="rel-graph-container" style="height:400px;background:#1e293b;border-radius:0.375rem;"></div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- 푸터 -->
            <div style="padding:0.75rem 1.5rem;background:#f8fafc;text-align:center;font-size:0.6875rem;color:#94a3b8;" class="med-mono">
                CHART #<?php echo str_pad($char['ch_id'], 6, '0', STR_PAD_LEFT); ?> | LAST UPDATED: <?php echo $ch_date; ?>
            </div>
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (min-width: 768px) {
    .skin-med .med-grid { grid-template-columns: 1fr; }
    .skin-med .med-top { flex-direction: row; }
}
@media (max-width: 640px) {
    .skin-med .med-top { flex-direction: column; align-items: center; text-align: center; }
    .skin-med .med-vitals { grid-template-columns: 1fr !important; }
    .skin-med .med-label { width: 100px; font-size: 0.75rem; }
}
</style>
