<?php
/**
 * Morgan Edition - 프로필 스킨: 요원 인사기록
 * 다크 슬레이트, 블루 악센트, 모노스페이스, 스캔라인
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_date = date('Y.m.d H:i:s', strtotime($char['ch_datetime']));
$ch_owner = htmlspecialchars($char['mb_nick']);
?>

<style>
.skin-spy { font-family: ui-sans-serif, system-ui, -apple-system, sans-serif; }
.skin-spy .spy-accent { color: #3b82f6; }
.skin-spy .spy-scanline {
    background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0) 50%, rgba(0,0,0,0.2) 50%, rgba(0,0,0,0.2));
    background-size: 100% 4px;
    pointer-events: none;
}
.skin-spy .spy-mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
.skin-spy .spy-img-wrapper img { filter: grayscale(100%); transition: filter 0.5s; }
.skin-spy .spy-img-wrapper:hover img { filter: grayscale(0%); }
.skin-spy .spy-bar { height: 12px; border-radius: 2px; overflow: hidden; background: #334155; }
.skin-spy .spy-bar-fill { height: 100%; background: #3b82f6; }

/* 호버 효과 */
.skin-spy .spy-img-wrapper { overflow: hidden; }
.skin-spy .spy-img-wrapper img { transition: filter 0.5s, transform 0.5s; }
.skin-spy .spy-img-wrapper:hover img { filter: grayscale(0%); transform: scale(1.05); }

/* 관계 항목 호버 */
.skin-spy .spy-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-spy .spy-rel-item:hover { background: rgba(51,65,85,0.3); border-left-color: #3b82f6; }

/* 프로필 필드 행 호버 */
.skin-spy .spy-field-row { transition: all 0.25s ease; border-left: 2px solid transparent; }
.skin-spy .spy-field-row:hover { background: rgba(51,65,85,0.2); border-left-color: #3b82f6; }

/* 버튼 호버 */
.skin-spy button, .skin-spy a[style*="border"] { transition: all 0.25s ease; }
.skin-spy button:hover, .skin-spy a[style*="border"]:hover {
    transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59,130,246,0.3);
}

/* 업적 칩 호버 */
.skin-spy .spy-badge { transition: all 0.25s ease; }
.skin-spy .spy-badge:hover { transform: scale(1.05); box-shadow: 0 0 10px rgba(59,130,246,0.3); }

/* 헤더 배너 호버 */
.skin-spy [style*="max-height:12rem"] img { transition: transform 0.6s ease; }
.skin-spy [style*="max-height:12rem"]:hover img { transform: scale(1.03); }
</style>

<div class="mg-inner skin-spy">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#94a3b8;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div style="max-width:64rem;margin:0 auto;background:rgba(15,23,42,0.8);border:1px solid #1e293b;border-radius:0.5rem;overflow:hidden;position:relative;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
        <!-- 스캔라인 오버레이 -->
        <div class="spy-scanline" style="position:absolute;inset:0;opacity:0.1;z-index:0;"></div>

        <!-- CLASSIFIED 헤더 -->
        <div style="background:rgba(127,29,29,0.3);border-bottom:1px solid rgba(153,27,27,0.5);padding:0.5rem 1rem;display:flex;justify-content:space-between;align-items:center;color:#f87171;font-size:0.75rem;letter-spacing:0.2em;text-transform:uppercase;position:relative;z-index:10;" class="spy-mono">
            <span>/// CLASSIFIED DOCUMENT ///</span>
            <span style="display:none;" class="sm-show">EYES ONLY</span>
        </div>

        <?php if ($char_header) { ?>
        <div style="position:relative;max-height:12rem;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:grayscale(60%);">
            <div class="spy-scanline" style="position:absolute;inset:0;"></div>
        </div>
        <?php } ?>

        <div style="display:grid;grid-template-columns:1fr;gap:1.5rem;padding:1.5rem;position:relative;z-index:10;" class="spy-grid">
            <!-- 좌측: 사진 + ID -->
            <div style="display:flex;flex-direction:column;gap:1.5rem;">
                <?php if ($char_image) { ?>
                <div class="spy-img-wrapper" style="position:relative;">
                    <div style="aspect-ratio:3/4;background:#1e293b;border:2px solid #334155;overflow:hidden;border-radius:0.25rem;position:relative;">
                        <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;">
                        <div style="position:absolute;inset:0;background:linear-gradient(to top,rgba(15,23,42,0.8),transparent);"></div>
                        <div style="position:absolute;bottom:8px;left:8px;right:8px;display:flex;justify-content:space-between;font-size:0.75rem;" class="spy-mono spy-accent">
                            <span>[IMG_ID: <?php echo strtoupper(substr(md5($char['ch_id']), 0, 6)); ?>]</span>
                        </div>
                    </div>
                    <?php if ($char['ch_state'] == 'approved') { ?>
                    <div style="position:absolute;top:1rem;right:1rem;background:rgba(34,197,94,0.2);color:#4ade80;border:1px solid rgba(34,197,94,0.5);padding:0.25rem 0.75rem;font-size:0.75rem;font-weight:bold;letter-spacing:0.1em;border-radius:2px;backdrop-filter:blur(12px);" class="spy-mono">ACTIVE STATUS</div>
                    <?php } ?>
                </div>
                <?php } ?>

                <div style="background:rgba(30,41,59,0.5);border:1px solid #334155;padding:1rem;border-radius:0.25rem;font-size:0.875rem;" class="spy-mono">
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #334155;padding-bottom:0.5rem;margin-bottom:0.5rem;">
                        <span style="color:#64748b;">ID NUMBER</span>
                        <span class="spy-accent">#CH-<?php echo str_pad($char['ch_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #334155;padding-bottom:0.5rem;margin-bottom:0.5rem;">
                        <span style="color:#64748b;">CODENAME</span>
                        <span style="font-weight:bold;letter-spacing:0.1em;">"<?php echo $ch_name; ?>"</span>
                    </div>
                    <?php if ($ch_side) { ?>
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #334155;padding-bottom:0.5rem;margin-bottom:0.5rem;">
                        <span style="color:#64748b;">AFFILIATION</span>
                        <span><?php echo $ch_side; ?></span>
                    </div>
                    <?php } ?>
                    <?php if ($ch_class) { ?>
                    <div style="display:flex;justify-content:space-between;">
                        <span style="color:#64748b;">RANK/CLASS</span>
                        <span><?php echo $ch_class; ?></span>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- 우측: 이름 + 프로필 -->
            <div style="display:flex;flex-direction:column;gap:2rem;">
                <div>
                    <h1 style="font-size:2rem;font-weight:800;color:#f1f5f9;text-transform:uppercase;letter-spacing:-0.025em;">
                        <?php echo $ch_name; ?>
                        <span style="display:block;font-size:1rem;font-weight:normal;margin-top:0.25rem;letter-spacing:0.1em;" class="spy-mono spy-accent">// <?php echo $ch_owner; ?></span>
                    </h1>
                    <div style="height:4px;width:6rem;background:#3b82f6;margin-top:1rem;"></div>

                    <!-- 액션 버튼 -->
                    <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                        <?php if ($can_request_relation) { ?>
                        <button type="button" onclick="openRelRequestModal()" style="font-size:0.75rem;background:rgba(59,130,246,0.2);border:1px solid rgba(59,130,246,0.5);color:#3b82f6;padding:0.25rem 0.75rem;border-radius:2px;cursor:pointer;" class="spy-mono">LINK REQUEST</button>
                        <?php } ?>
                        <?php if ($is_owner) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.75rem;background:rgba(100,116,139,0.2);border:1px solid #475569;color:#94a3b8;padding:0.25rem 0.75rem;border-radius:2px;text-decoration:none;" class="spy-mono">EDIT RECORD</a>
                        <?php } ?>
                    </div>
                </div>

                <!-- PERSONAL DATA -->
                <?php if (count($grouped_fields) > 0) { ?>
                <?php foreach ($grouped_fields as $category => $fields) { ?>
                <section>
                    <h3 style="font-size:1.125rem;font-weight:bold;color:#f1f5f9;margin-bottom:1rem;display:flex;align-items:center;">
                        <span style="width:8px;height:24px;background:#3b82f6;margin-right:0.75rem;display:inline-block;"></span>
                        <span style="letter-spacing:0.1em;" class="spy-mono"><?php echo strtoupper(htmlspecialchars($category)); ?></span>
                    </h3>
                    <div style="background:rgba(30,41,59,0.3);border-left:2px solid #334155;padding:1rem;border-radius:0 0.25rem 0.25rem 0;display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1rem;font-size:0.875rem;" class="spy-mono">
                        <?php foreach ($fields as $field) { ?>
                        <div class="spy-field-row">
                            <span style="color:#64748b;display:block;margin-bottom:0.25rem;font-size:0.75rem;text-transform:uppercase;"><?php echo htmlspecialchars($field['pf_name']); ?>:</span>
                            <span style="color:#cbd5e1;">
                                <?php
                                if ($field['pf_type'] == 'url') {
                                    echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank" style="color:#3b82f6;">'.htmlspecialchars($field['pv_value']).'</a>';
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
                </section>
                <?php } ?>
                <?php } ?>

                <!-- 업적 쇼케이스 -->
                <?php if (!empty($achievement_showcase)) { ?>
                <section>
                    <h3 style="font-size:1.125rem;font-weight:bold;color:#f1f5f9;margin-bottom:1rem;display:flex;align-items:center;">
                        <span style="width:8px;height:24px;background:#3b82f6;margin-right:0.75rem;display:inline-block;"></span>
                        <span style="letter-spacing:0.1em;" class="spy-mono">COMMENDATIONS</span>
                    </h3>
                    <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                        <?php foreach ($achievement_showcase as $acd) {
                            $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                            $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                        ?>
                        <div style="background:rgba(30,41,59,0.5);border:1px solid #334155;padding:0.5rem 0.75rem;border-radius:2px;display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;" class="spy-mono spy-badge" title="<?php echo $a_name; ?>">
                            <?php if ($a_icon) { ?>
                            <img src="<?php echo htmlspecialchars($a_icon); ?>" style="width:20px;height:20px;object-fit:contain;">
                            <?php } ?>
                            <span style="color:#94a3b8;"><?php echo $a_name; ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </section>
                <?php } ?>
            </div>
        </div>

        <!-- 관계 섹션 -->
        <?php if (!empty($char_relations)) { ?>
        <div style="border-top:1px solid #1e293b;padding:1.5rem;position:relative;z-index:10;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 style="font-size:1rem;font-weight:bold;color:#f1f5f9;display:flex;align-items:center;">
                    <span style="width:8px;height:24px;background:#3b82f6;margin-right:0.75rem;display:inline-block;"></span>
                    <span class="spy-mono" style="letter-spacing:0.1em;">ASSOCIATED AGENTS</span>
                </h3>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#64748b;background:none;border:none;cursor:pointer;display:none;" class="spy-mono">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.75rem;color:#3b82f6;background:none;border:none;cursor:pointer;" class="spy-mono">관계도 보기</button>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:0;">
                <?php foreach ($char_relations as $rel) {
                    $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                    $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                    $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                    $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                    $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                    $rel_color = $rel['cr_color'] ?: '#95a5a6';
                ?>
                <div style="display:flex;align-items:center;gap:0.75rem;padding:0.625rem 0;border-bottom:1px solid #1e293b;font-size:0.875rem;" class="spy-mono spy-rel-item">
                    <?php if ($other_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;filter:grayscale(50%);">
                    <?php } else { ?>
                    <div style="width:32px;height:32px;border-radius:50%;background:#1e293b;display:flex;align-items:center;justify-content:center;color:#64748b;font-size:0.75rem;">?</div>
                    <?php } ?>
                    <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                    <span style="color:#64748b;"><?php echo $my_label; ?></span>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="color:#3b82f6;text-decoration:none;margin-left:auto;"><?php echo $other_name; ?></a>
                </div>
                <?php } ?>
            </div>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;border-top:1px solid #1e293b;">
                <div id="rel-graph-container" style="height:400px;background:#0a0f1a;"></div>
            </div>
        </div>
        <?php } ?>

        <!-- 푸터 -->
        <div style="background:#0f172a;border-top:1px solid #1e293b;padding:1rem;text-align:center;font-size:0.75rem;color:#475569;position:relative;z-index:10;" class="spy-mono">
            /// END OF RECORD - DATABASE TIMESTAMP: <?php echo $ch_date; ?> ///
        </div>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
@media (min-width: 768px) {
    .skin-spy .spy-grid { grid-template-columns: 1fr 2fr !important; }
    .skin-spy .sm-show { display: inline !important; }
}
</style>
