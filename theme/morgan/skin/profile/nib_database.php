<?php
/**
 * Morgan Edition - 프로필 스킨: NIB 수사 데이터베이스
 * 다크 네이비, 시안 악센트, 그리드 배경, 수사 기록
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y-m-d', strtotime($char['ch_datetime']));
?>

<style>
.skin-nib {
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    color: #cbd5e1;
}
.skin-nib .nib-data { font-family: 'Courier New', Courier, monospace; }
.skin-nib .nib-accent { color: #00d2ff; }
.skin-nib .nib-grid-bg {
    background-image: linear-gradient(to right, rgba(0,210,255,0.05) 1px, transparent 1px),
                      linear-gradient(to bottom, rgba(0,210,255,0.05) 1px, transparent 1px);
    background-size: 20px 20px;
}
.skin-nib a { color: #00d2ff; text-decoration: none; }
.skin-nib a:hover { text-decoration: underline; }

/* 호버 효과 */
.skin-nib .nib-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-nib .nib-rel-item:hover { background: rgba(0,210,255,0.05); border-left-color: #00d2ff; }

.skin-nib .nib-field-row { transition: all 0.25s ease; border-left: 2px solid transparent; padding-left: 0.25rem; }
.skin-nib .nib-field-row:hover { background: rgba(0,210,255,0.05); border-left-color: #00d2ff; }

.skin-nib .nib-chip { transition: all 0.25s ease; }
.skin-nib .nib-chip:hover { transform: scale(1.05); box-shadow: 0 0 10px rgba(0,210,255,0.3); border-color: #00d2ff; }

/* 초상화 호버 */
.skin-nib .nib-photo img { transition: all 0.4s ease; }
.skin-nib .nib-photo:hover img { filter: brightness(1.2); }
.skin-nib .nib-photo { transition: box-shadow 0.4s ease; }
.skin-nib .nib-photo:hover { box-shadow: 0 0 20px rgba(0,210,255,0.3); }

/* 그리드 배경 미세 이동 */
@keyframes nib-grid-drift { 0% { background-position: 0 0; } 100% { background-position: 20px 20px; } }
.skin-nib .nib-grid-bg { animation: nib-grid-drift 8s linear infinite; }

/* 헤더 배너 */
.skin-nib [style*="border-bottom:2px solid #00d2ff"] img { transition: transform 0.6s ease; }
.skin-nib [style*="border-bottom:2px solid #00d2ff"]:hover img { transform: scale(1.03); }
</style>

<div class="mg-inner skin-nib nib-grid-bg">
    <!-- NIB 헤더 -->
    <header style="max-width:72rem;margin:0 auto 1.5rem;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;border-bottom:2px solid rgba(0,210,255,0.5);padding-bottom:1rem;gap:1rem;">
        <div style="display:flex;align-items:center;gap:1rem;">
            <div style="width:3rem;height:3rem;background:#00d2ff;display:flex;align-items:center;justify-content:center;border-radius:2px;">
                <span style="color:#0a0f1a;font-weight:900;font-size:1.25rem;">NIB</span>
            </div>
            <div>
                <h1 style="font-size:1.25rem;font-weight:bold;letter-spacing:-0.05em;" class="nib-accent">NATIONAL INVESTIGATION BUREAU</h1>
                <p style="font-size:0.75rem;opacity:0.7;" class="nib-data">CENTRAL INTELLIGENCE DATABASE v.2.6.02</p>
            </div>
        </div>
        <div style="text-align:right;font-size:0.75rem;" class="nib-data nib-accent">
            <p>ACCESS LEVEL: CLASS-4 (RESTRICTED)</p>
            <p>SESSION: <?php echo strtoupper(substr(md5($char['ch_id'] . date('Ymd')), 0, 8)); ?></p>
        </div>
    </header>

    <?php if ($char_header) { ?>
    <div style="position:relative;max-height:12rem;overflow:hidden;border-bottom:2px solid #00d2ff;max-width:72rem;margin:0 auto;">
        <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:brightness(60%) saturate(120%);">
        <div style="position:absolute;bottom:0;left:0;right:0;height:3rem;background:linear-gradient(transparent,#0a0f1a);"></div>
    </div>
    <?php } ?>

    <main style="max-width:72rem;margin:0 auto;display:grid;grid-template-columns:1fr;gap:1.5rem;" class="nib-main-grid">
        <!-- 좌측: 사진 + 바이오 -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <!-- 사진 -->
            <div class="nib-photo" style="background:#151c2c;border:1px solid #2d3a54;padding:0.5rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25);">
                <div style="aspect-ratio:1;background:#1e293b;position:relative;overflow:hidden;display:flex;align-items:flex-end;justify-content:center;">
                    <?php if ($char_image) { ?>
                    <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="width:100%;height:100%;object-fit:cover;filter:grayscale(100%) contrast(125%) brightness(90%);position:relative;z-index:10;">
                    <?php } else { ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#2d3a54;font-size:3rem;font-weight:bold;" class="nib-data">NO PHOTO</div>
                    <?php } ?>
                    <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(255,77,77,0.8);color:#fff;text-align:center;padding:0.25rem;z-index:20;font-weight:bold;letter-spacing:0.2em;text-transform:uppercase;font-size:0.75rem;" class="nib-data">
                        <?php echo $char['ch_state'] == 'approved' ? 'SUBJECT REGISTERED' : 'UNDER REVIEW'; ?>
                    </div>
                </div>
            </div>

            <!-- 바이오메트릭 데이터: 기본 필드 중 첫 카테고리 -->
            <?php
            $first_category = null;
            $remaining_fields = $grouped_fields;
            if (!empty($grouped_fields)) {
                $keys = array_keys($grouped_fields);
                $first_category = $keys[0];
                $first_fields = $grouped_fields[$first_category];
                unset($remaining_fields[$first_category]);
            }
            ?>
            <?php if ($first_category && !empty($first_fields)) { ?>
            <div style="background:#151c2c;border:1px solid #2d3a54;padding:1rem;font-size:0.875rem;" class="nib-data">
                <h3 style="border-bottom:1px solid #2d3a54;padding-bottom:0.5rem;margin-bottom:0.75rem;font-size:0.75rem;font-weight:bold;text-transform:uppercase;letter-spacing:0.2em;" class="nib-accent"><?php echo htmlspecialchars($first_category); ?></h3>
                <div style="display:flex;flex-direction:column;gap:0.5rem;">
                    <?php foreach ($first_fields as $field) { ?>
                    <div class="nib-field-row" style="display:flex;justify-content:space-between;">
                        <span style="text-transform:uppercase;"><?php echo htmlspecialchars($field['pf_name']); ?>:</span>
                        <span style="color:#f1f5f9;">
                            <?php
                            if ($field['pf_type'] == 'url') {
                                echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank">LINK</a>';
                            } else {
                                echo htmlspecialchars(mb_substr($field['pv_value'], 0, 30));
                                if (mb_strlen($field['pv_value']) > 30) echo '...';
                            }
                            ?>
                        </span>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 우측: 메인 정보 -->
        <div style="display:flex;flex-direction:column;gap:1.5rem;">
            <!-- 이름 카드 -->
            <div style="background:#151c2c;border-top:2px solid #00d2ff;padding:1.5rem;box-shadow:0 10px 15px -3px rgba(0,0,0,0.3);">
                <div style="display:grid;grid-template-columns:1fr;gap:1rem;" class="nib-name-grid">
                    <div>
                        <label style="font-size:0.7rem;opacity:0.7;" class="nib-data nib-accent">SUBJECT NAME (FULL)</label>
                        <p style="font-size:1.875rem;font-weight:bold;color:#f1f5f9;letter-spacing:-0.05em;"><?php echo $ch_name; ?> <span style="font-size:1rem;font-weight:normal;color:#64748b;">(@<?php echo $ch_owner; ?>)</span></p>
                    </div>
                    <div>
                        <label style="font-size:0.7rem;opacity:0.7;" class="nib-data nib-accent">SUBJECT ID</label>
                        <p style="font-size:1.5rem;color:#f1f5f9;" class="nib-data">#CH-<?php echo str_pad($char['ch_id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                </div>
                <?php if ($ch_side || $ch_class) { ?>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:1rem;margin-top:1rem;">
                    <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                    <div style="background:#0a0f1a;padding:0.5rem;border:1px solid #2d3a54;border-radius:0.25rem;">
                        <label style="display:block;font-size:0.625rem;color:#64748b;font-weight:bold;text-transform:uppercase;">세력</label>
                        <span style="font-size:0.875rem;"><?php echo $ch_side; ?></span>
                    </div>
                    <?php } ?>
                    <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                    <div style="background:#0a0f1a;padding:0.5rem;border:1px solid #2d3a54;border-radius:0.25rem;">
                        <label style="display:block;font-size:0.625rem;color:#64748b;font-weight:bold;text-transform:uppercase;">직업</label>
                        <span style="font-size:0.875rem;"><?php echo $ch_class; ?></span>
                    </div>
                    <?php } ?>
                    <div style="background:#0a0f1a;padding:0.5rem;border:1px solid #2d3a54;border-radius:0.25rem;">
                        <label style="display:block;font-size:0.625rem;color:#64748b;font-weight:bold;text-transform:uppercase;">등록일</label>
                        <span style="font-size:0.875rem;"><?php echo $ch_date; ?></span>
                    </div>
                </div>
                <?php } ?>

                <!-- 액션 버튼 -->
                <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                    <?php if ($can_request_relation) { ?>
                    <button type="button" onclick="openRelRequestModal()" style="font-size:0.75rem;background:rgba(0,210,255,0.15);border:1px solid #00d2ff;color:#00d2ff;padding:0.375rem 0.75rem;border-radius:2px;cursor:pointer;" class="nib-data">LINK REQUEST</button>
                    <?php } ?>
                    <?php if ($is_owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.75rem;background:rgba(45,58,84,0.5);border:1px solid #2d3a54;color:#94a3b8;padding:0.375rem 0.75rem;border-radius:2px;" class="nib-data">EDIT RECORD</a>
                    <?php } ?>
                </div>
            </div>

            <!-- 수사 기록 (나머지 프로필 필드) -->
            <?php if (!empty($remaining_fields)) { ?>
            <?php foreach ($remaining_fields as $category => $fields) { ?>
            <div style="background:#151c2c;border:1px solid #2d3a54;display:flex;flex-direction:column;max-height:20rem;">
                <div style="background:rgba(45,58,84,0.5);padding:0.5rem 1rem;display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:0.75rem;font-weight:bold;color:#f1f5f9;letter-spacing:0.2em;"><?php echo htmlspecialchars(mb_strtoupper($category)); ?></span>
                    <span style="font-size:0.625rem;" class="nib-data nib-accent">LOG_ID: <?php echo strtoupper(substr(md5($category), 0, 8)); ?></span>
                </div>
                <div style="padding:1rem;font-size:0.875rem;overflow-y:auto;line-height:1.6;color:#94a3b8;" class="nib-data">
                    <?php foreach ($fields as $field) { ?>
                    <div class="nib-field-row" style="margin-bottom:0.75rem;">
                        <span class="nib-accent" style="font-weight:bold;">[<?php echo htmlspecialchars($field['pf_name']); ?>]</span>
                        <span style="margin-left:0.5rem;">
                            <?php
                            if ($field['pf_type'] == 'url') {
                                echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank">'.htmlspecialchars($field['pv_value']).'</a>';
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
            </div>
            <?php } ?>
            <?php } ?>

            <!-- 업적 태그 -->
            <?php if (!empty($achievement_showcase)) { ?>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                ?>
                <span style="background:rgba(0,210,255,0.1);border:1px solid #00d2ff;color:#00d2ff;padding:0.25rem 0.75rem;font-size:0.75rem;border-radius:9999px;" class="nib-data nib-chip">#<?php echo $a_name; ?></span>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </main>

    <!-- 관계 섹션 -->
    <?php if (!empty($char_relations)) { ?>
    <div style="max-width:72rem;margin:1.5rem auto 0;background:#151c2c;border:1px solid #2d3a54;">
        <div style="background:rgba(45,58,84,0.5);padding:0.5rem 1rem;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:0.75rem;font-weight:bold;color:#f1f5f9;letter-spacing:0.2em;">KNOWN ASSOCIATES</span>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <?php if ($is_owner) { ?>
                <button type="button" id="rel-graph-save" style="font-size:0.625rem;color:#64748b;background:none;border:none;cursor:pointer;display:none;" class="nib-data">배치 저장</button>
                <?php } ?>
                <button type="button" id="rel-graph-toggle" style="font-size:0.75rem;color:#00d2ff;background:none;border:none;cursor:pointer;" class="nib-data">관계도 보기</button>
            </div>
        </div>
        <div style="padding:1rem;">
            <?php foreach ($char_relations as $rel) {
                $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                $rel_color = $rel['cr_color'] ?: '#95a5a6';
            ?>
            <div style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid #2d3a54;font-size:0.875rem;" class="nib-data nib-rel-item">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;filter:grayscale(100%);">
                <?php } else { ?>
                <div style="width:32px;height:32px;border-radius:50%;background:#0a0f1a;display:flex;align-items:center;justify-content:center;color:#2d3a54;font-weight:bold;">?</div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#64748b;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
        </div>
        <!-- 인라인 관계도 -->
        <div id="rel-graph-wrap" class="hidden" style="border-top:1px solid #2d3a54;">
            <div id="rel-graph-container" style="height:400px;background:#0a0f1a;"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 푸터 -->
    <footer style="max-width:72rem;margin:1.5rem auto 0;font-size:0.625rem;color:#475569;display:flex;justify-content:space-between;flex-wrap:wrap;gap:0.5rem;" class="nib-data">
        <span>ENCRYPTION: AES-256-GCM ACTIVE</span>
        <span>CONFIDENTIAL - FOR INTERNAL USE ONLY</span>
        <span>TERMINAL: <?php echo strtoupper(substr(md5($char['mb_id']), 0, 12)); ?></span>
    </footer>

    <!-- 소유자 인장 -->
    <div style="max-width:72rem;margin:1rem auto 0;">
        <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
    </div>
</div>

<style>
@media (min-width: 768px) {
    .skin-nib .nib-main-grid { grid-template-columns: 1fr 2fr !important; }
    .skin-nib .nib-name-grid { grid-template-columns: 1fr 1fr !important; }
}
</style>
