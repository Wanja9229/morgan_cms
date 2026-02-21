<?php
/**
 * Morgan Edition - 프로필 스킨: SNS 프로필
 * 밝은 모던, 커버 배너 + 원형 아바타, 소셜 스타일
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y.m.d', strtotime($char['ch_datetime']));
$relation_count = count($char_relations);
$field_count = 0;
foreach ($grouped_fields as $fields) { $field_count += count($fields); }
?>

<style>
.skin-sns { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f1419; }
.skin-sns a { color: #1d9bf0; text-decoration: none; }
.skin-sns a:hover { text-decoration: underline; }
.skin-sns .sns-cover {
    height: 200px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    position: relative;
}
.skin-sns .sns-avatar {
    width: 134px; height: 134px; border-radius: 50%; border: 4px solid #ffffff;
    object-fit: cover; position: absolute; bottom: -67px; left: 1.5rem;
    background: #e1e8ed;
}
.skin-sns .sns-avatar-placeholder {
    width: 134px; height: 134px; border-radius: 50%; border: 4px solid #ffffff;
    position: absolute; bottom: -67px; left: 1.5rem;
    background: #cfd9de; display: flex; align-items: center; justify-content: center;
    font-size: 3rem; color: #536471; font-weight: bold;
}
.skin-sns .sns-stat { text-align: center; }
.skin-sns .sns-stat-num { font-size: 1.25rem; font-weight: 900; color: #0f1419; }
.skin-sns .sns-stat-label { font-size: 0.8125rem; color: #536471; }
.skin-sns .sns-field-card {
    padding: 0.75rem 1rem; border-bottom: 1px solid #eff3f4;
}
.skin-sns .sns-field-label { font-size: 0.8125rem; color: #536471; }
.skin-sns .sns-field-value { font-size: 0.9375rem; color: #0f1419; margin-top: 0.125rem; }

/* 호버 효과 */
.skin-sns .sns-avatar { transition: all 0.4s ease; }
.skin-sns .sns-avatar:hover { transform: scale(1.08) translateY(0); box-shadow: 0 4px 20px rgba(0,0,0,0.2); }

.skin-sns .sns-rel-item { transition: all 0.25s ease; border-radius: 0.5rem; }
.skin-sns .sns-rel-item:hover { background: #f7f9fa; }

.skin-sns .sns-field-row { transition: all 0.25s ease; border-radius: 0.375rem; }
.skin-sns .sns-field-row:hover { background: #f7f9fa; }

/* 버튼 호버 */
.skin-sns button, .skin-sns a[style*="border-radius"] { transition: all 0.25s ease; }
.skin-sns button:hover, .skin-sns a[style*="border-radius"]:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

/* 통계 카운터 호버 */
.skin-sns .sns-stat { transition: color 0.25s ease; cursor: default; }
.skin-sns .sns-stat:hover { color: #1d9bf0 !important; }

/* 업적 배지 */
.skin-sns .sns-badge { transition: all 0.25s ease; }
.skin-sns .sns-badge:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(29,155,240,0.2); }

/* 커버 이미지 */
.skin-sns .sns-cover img { transition: transform 0.6s ease; }
.skin-sns .sns-cover:hover img { transform: scale(1.03); }
</style>

<div class="mg-inner skin-sns">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#536471;margin-bottom:1rem;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:1rem;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.12);">
        <!-- 커버 배너 -->
        <div class="sns-cover" <?php if ($char_header) { ?>style="background:none;"<?php } ?>>
            <?php if ($char_header) { ?>
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <?php } ?>
            <?php if ($char_image) { ?>
            <img src="<?php echo $char_image; ?>" class="sns-avatar" alt="">
            <?php } else { ?>
            <div class="sns-avatar-placeholder"><?php echo mb_substr($char['ch_name'], 0, 1); ?></div>
            <?php } ?>
        </div>

        <!-- 프로필 헤더 -->
        <div style="padding:0 1.5rem;padding-top:76px;">
            <!-- 액션 버튼 (우측 정렬) -->
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;margin-top:-60px;margin-bottom:2rem;">
                <?php if ($can_request_relation) { ?>
                <button type="button" onclick="openRelRequestModal()" style="background:#0f1419;color:#fff;border:none;border-radius:9999px;padding:0.5rem 1.25rem;font-size:0.875rem;font-weight:700;cursor:pointer;">관계 신청</button>
                <?php } ?>
                <?php if ($is_owner) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="background:transparent;border:1px solid #cfd9de;border-radius:9999px;padding:0.5rem 1.25rem;font-size:0.875rem;font-weight:700;color:#0f1419;">수정</a>
                <?php } ?>
            </div>

            <!-- 이름 -->
            <h1 style="font-size:1.3125rem;font-weight:800;margin:0;"><?php echo $ch_name; ?></h1>
            <p style="color:#536471;font-size:0.9375rem;margin-top:0.125rem;">@<?php echo $ch_owner; ?></p>

            <!-- 부가 정보 -->
            <?php if ($ch_side || $ch_class) { ?>
            <p style="color:#536471;font-size:0.9375rem;margin-top:0.5rem;display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap;">
                <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                <span style="display:inline-flex;align-items:center;gap:0.25rem;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.193 23.193 0 0112 15c-3.183 0-6.22-.64-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <?php echo $ch_class; ?>
                </span>
                <?php } ?>
                <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                <span style="display:inline-flex;align-items:center;gap:0.25rem;">
                    <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                    <?php echo $ch_side; ?>
                </span>
                <?php } ?>
            </p>
            <?php } ?>

            <p style="color:#536471;font-size:0.9375rem;margin-top:0.25rem;display:flex;align-items:center;gap:0.25rem;">
                <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                가입일 <?php echo $ch_date; ?>
            </p>

            <!-- 통계 -->
            <div style="display:flex;gap:1.5rem;margin-top:1rem;padding-bottom:1rem;border-bottom:1px solid #eff3f4;">
                <div class="sns-stat">
                    <span class="sns-stat-num"><?php echo $relation_count; ?></span>
                    <span class="sns-stat-label"> 관계</span>
                </div>
                <div class="sns-stat">
                    <span class="sns-stat-num"><?php echo $field_count; ?></span>
                    <span class="sns-stat-label"> 프로필</span>
                </div>
                <div class="sns-stat">
                    <span class="sns-stat-num"><?php echo count($achievement_showcase); ?></span>
                    <span class="sns-stat-label"> 업적</span>
                </div>
            </div>
        </div>

        <!-- 업적 -->
        <?php if (!empty($achievement_showcase)) { ?>
        <div style="padding:1rem 1.5rem;border-bottom:1px solid #eff3f4;">
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                ?>
                <span class="sns-badge" style="display:inline-flex;align-items:center;gap:0.375rem;background:#eff3f4;border-radius:9999px;padding:0.375rem 0.75rem;font-size:0.8125rem;" title="<?php echo $a_name; ?>">
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
        <div style="padding:0.75rem 1.5rem;background:#f7f9f9;font-size:0.8125rem;font-weight:700;color:#536471;border-bottom:1px solid #eff3f4;">
            <?php echo htmlspecialchars($category); ?>
        </div>
        <?php foreach ($fields as $field) { ?>
        <div class="sns-field-card sns-field-row">
            <div class="sns-field-label"><?php echo htmlspecialchars($field['pf_name']); ?></div>
            <div class="sns-field-value">
                <?php echo mg_render_profile_value($field); ?>
            </div>
        </div>
        <?php } ?>
        <?php } ?>
        <?php } ?>

        <!-- 관계 -->
        <?php if (!empty($char_relations)) { ?>
        <div style="padding:0.75rem 1.5rem;background:#f7f9f9;font-size:0.8125rem;font-weight:700;color:#536471;border-bottom:1px solid #eff3f4;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span>관계</span>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#536471;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.8125rem;color:#1d9bf0;background:none;border:none;cursor:pointer;">관계도 보기</button>
                </div>
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
        <div class="sns-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1.5rem;border-bottom:1px solid #eff3f4;">
            <?php if ($other_thumb) { ?>
            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
            <?php } else { ?>
            <div style="width:40px;height:40px;border-radius:50%;background:#eff3f4;display:flex;align-items:center;justify-content:center;color:#536471;font-weight:700;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
            <?php } ?>
            <div style="flex:1;min-width:0;">
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="font-weight:700;color:#0f1419;font-size:0.9375rem;"><?php echo $other_name; ?></a>
                <div style="display:flex;align-items:center;gap:0.375rem;margin-top:0.125rem;">
                    <span style="width:8px;height:8px;border-radius:50%;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                    <span style="font-size:0.8125rem;color:#536471;"><?php echo $my_label; ?></span>
                </div>
            </div>
        </div>
        <?php } ?>
        <!-- 인라인 관계도 -->
        <div id="rel-graph-wrap" class="hidden" style="border-bottom:1px solid #eff3f4;">
            <div id="rel-graph-container" style="height:400px;background:#15202b;"></div>
        </div>
        <?php } ?>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
