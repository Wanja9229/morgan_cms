<?php
/**
 * Morgan Edition - 프로필 스킨: WANTED 수배전단
 * 양피지 텍스처, 웨스턴 폰트, 현상금, 단일 컬럼
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
?>

<link href="https://fonts.googleapis.com/css2?family=Rye&family=Special+Elite&display=swap" rel="stylesheet">

<style>
.skin-wanted {
    font-family: 'Special Elite', serif;
    color: #3a2a1a;
}
.skin-wanted .wt-western { font-family: 'Rye', cursive; }
.skin-wanted .wt-rough {
    box-shadow: 0 0 0 4px #e2cfb6, 0 0 0 8px #3a2a1a, 5px 5px 15px rgba(0,0,0,0.5);
}
.skin-wanted a { color: #8b0000; }
.skin-wanted a:hover { text-decoration: underline; }
.skin-wanted img.wt-photo {
    filter: grayscale(100%) contrast(150%) brightness(75%);
}

/* 호버 효과 */
.skin-wanted img.wt-photo { transition: filter 0.5s ease; }
.skin-wanted img.wt-photo:hover { filter: grayscale(30%) contrast(120%) brightness(90%); }

.skin-wanted .wt-rel-item { transition: all 0.25s ease; border-left: 3px solid transparent; padding-left: 0.5rem; }
.skin-wanted .wt-rel-item:hover { background: rgba(139,0,0,0.08); border-left-color: #8b0000; }

.skin-wanted .wt-field-row { transition: all 0.25s ease; }
.skin-wanted .wt-field-row:hover { background: rgba(58,42,26,0.06); }

/* WANTED 텍스트 hover shake */
@keyframes wt-shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-2px); } 75% { transform: translateX(2px); } }
.skin-wanted .wt-western:hover { animation: wt-shake 0.3s ease 2; }

/* 컨테이너 hover */
.skin-wanted .wt-rough { transition: box-shadow 0.4s ease; }
.skin-wanted .wt-rough:hover { box-shadow: 0 0 0 4px #e2cfb6, 0 0 0 8px #3a2a1a, 8px 8px 25px rgba(0,0,0,0.6); }

/* 버튼 호버 */
.skin-wanted button { transition: all 0.25s ease; }
.skin-wanted button:hover { background: #6d0000 !important; transform: translateY(-1px); }
</style>

<div class="mg-inner skin-wanted">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#8d6e63;margin-bottom:1rem;text-decoration:none;">
        <svg style="width:16px;height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <div class="wt-rough" style="max-width:40rem;margin:0 auto;background:#e2cfb6;padding:2rem 2rem;position:relative;overflow:hidden;border:4px solid rgba(58,42,26,0.2);">

        <!-- WANTED 헤더 -->
        <header style="text-align:center;margin-bottom:2rem;border-bottom:4px solid #3a2a1a;padding-bottom:1rem;">
            <h1 class="wt-western" style="font-size:4rem;letter-spacing:-0.05em;text-transform:uppercase;line-height:1;">
                WANTED
            </h1>
            <p style="font-size:1.5rem;font-weight:bold;letter-spacing:0.3em;margin-top:0.5rem;">
                DEAD OR ALIVE
            </p>
        </header>

        <?php if ($char_header) { ?>
        <div style="margin:1rem 0;max-height:10rem;overflow:hidden;border:3px solid #3a2a1a;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:sepia(40%) contrast(120%) brightness(90%);">
        </div>
        <?php } ?>

        <!-- 사진 -->
        <div style="position:relative;margin-bottom:2rem;">
            <div style="aspect-ratio:1;background:rgba(255,255,255,0.5);border:8px solid #3a2a1a;padding:0.5rem;box-shadow:inset 0 2px 4px rgba(0,0,0,0.2);">
                <?php if ($char_image) { ?>
                <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" class="wt-photo" style="width:100%;height:100%;object-fit:cover;">
                <?php } else { ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:#8d6e63;font-size:2rem;" class="wt-western">NO PHOTO</div>
                <?php } ?>
            </div>
            <!-- 스탬프 -->
            <div style="position:absolute;bottom:-1rem;right:-1rem;width:8rem;height:8rem;border:4px solid rgba(139,0,0,0.6);border-radius:50%;display:flex;align-items:center;justify-content:center;transform:rotate(12deg);background:transparent;">
                <span style="color:rgba(139,0,0,0.6);font-weight:bold;text-align:center;line-height:1.1;font-size:1rem;text-transform:uppercase;">
                    <?php echo $char['ch_state'] == 'approved' ? 'REGISTERED<br>SUBJECT' : 'UNDER<br>REVIEW'; ?>
                </span>
            </div>
        </div>

        <!-- 이름 -->
        <div style="text-align:center;margin-bottom:1rem;">
            <h2 class="wt-western" style="font-size:2.5rem;text-transform:uppercase;border-bottom:2px solid rgba(58,42,26,0.3);display:inline-block;padding:0 1.5rem;">
                <?php echo $ch_name; ?>
            </h2>
            <div style="font-size:0.875rem;color:#5d4037;margin-top:0.5rem;">@<?php echo $ch_owner; ?></div>

            <!-- 포인트 (장식용) -->
            <div style="padding:1rem 0;">
                <p style="font-size:1.125rem;font-weight:bold;">REWARD</p>
                <p class="wt-western" style="font-size:3rem;color:#8b0000;">
                    <?php echo number_format($char['ch_id'] * 1000); ?>P
                </p>
                <p style="font-size:0.875rem;margin-top:0.25rem;">GOLD COINS</p>
            </div>

            <!-- 액션 버튼 -->
            <div style="display:flex;gap:0.5rem;justify-content:center;">
                <?php if ($can_request_relation) { ?>
                <button type="button" onclick="openRelRequestModal()" style="font-size:0.875rem;background:#8b0000;color:#e2cfb6;border:none;padding:0.375rem 1rem;cursor:pointer;">관계 신청</button>
                <?php } ?>
                <?php if ($is_owner) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.875rem;background:rgba(58,42,26,0.1);border:2px solid #3a2a1a;color:#3a2a1a;padding:0.375rem 1rem;text-decoration:none;">수정</a>
                <?php } ?>
            </div>
        </div>

        <!-- 프로필 필드 -->
        <div style="margin-top:2rem;border-top:4px solid #3a2a1a;padding-top:1.5rem;">
            <?php if ($ch_side || $ch_class) { ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                <?php if ($ch_side && mg_config('use_side', '1') == '1') { ?>
                <p><strong>세력:</strong> <?php echo $ch_side; ?></p>
                <?php } ?>
                <?php if ($ch_class && mg_config('use_class', '1') == '1') { ?>
                <p><strong>직업:</strong> <?php echo $ch_class; ?></p>
                <?php } ?>
            </div>
            <?php } ?>

            <?php if (count($grouped_fields) > 0) { ?>
            <?php foreach ($grouped_fields as $category => $fields) { ?>
            <div class="wt-field-row" style="background:rgba(0,0,0,0.05);padding:1rem;border-radius:0.25rem;margin-bottom:1rem;">
                <p style="font-weight:bold;text-decoration:underline;margin-bottom:0.5rem;"><?php echo htmlspecialchars($category); ?>:</p>
                <ul style="list-style:disc;list-style-position:inside;">
                    <?php foreach ($fields as $field) { ?>
                    <li style="margin-bottom:0.25rem;">
                        <strong><?php echo htmlspecialchars($field['pf_name']); ?></strong> —
                        <?php
                        if ($field['pf_type'] == 'url') {
                            echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank">'.htmlspecialchars($field['pv_value']).'</a>';
                        } elseif ($field['pf_type'] == 'textarea') {
                            echo nl2br(htmlspecialchars($field['pv_value']));
                        } else {
                            echo htmlspecialchars($field['pv_value']);
                        }
                        ?>
                    </li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
            <?php } ?>

            <!-- 업적 -->
            <?php if (!empty($achievement_showcase)) { ?>
            <div style="background:rgba(0,0,0,0.05);padding:1rem;border-radius:0.25rem;margin-bottom:1rem;">
                <p style="font-weight:bold;text-decoration:underline;margin-bottom:0.5rem;">NOTABLE ACHIEVEMENTS:</p>
                <ul style="list-style:disc;list-style-position:inside;">
                    <?php foreach ($achievement_showcase as $acd) {
                        $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                    ?>
                    <li><?php echo $a_name; ?></li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>

        <!-- 관계 -->
        <?php if (!empty($char_relations)) { ?>
        <div style="margin-top:1.5rem;border-top:2px solid rgba(58,42,26,0.3);padding-top:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <p style="font-weight:bold;text-decoration:underline;">KNOWN ASSOCIATES:</p>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <?php if ($is_owner) { ?>
                    <button type="button" id="rel-graph-save" style="font-size:0.75rem;color:#8d6e63;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                    <?php } ?>
                    <button type="button" id="rel-graph-toggle" style="font-size:0.875rem;color:#8b0000;background:none;border:none;cursor:pointer;text-decoration:underline;">관계도 보기</button>
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
            <div class="wt-rel-item" style="display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid rgba(58,42,26,0.15);font-size:0.875rem;">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #3a2a1a;filter:grayscale(100%);">
                <?php } else { ?>
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(58,42,26,0.1);display:flex;align-items:center;justify-content:center;border:2px solid #3a2a1a;font-weight:bold;">?</div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#5d4037;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;font-weight:bold;"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
            <!-- 인라인 관계도 -->
            <div id="rel-graph-wrap" class="hidden" style="margin-top:1rem;border-top:2px solid rgba(58,42,26,0.3);">
                <div id="rel-graph-container" style="height:400px;background:#1a120b;border-radius:0.25rem;"></div>
            </div>
        </div>
        <?php } ?>

        <!-- 푸터 -->
        <footer style="margin-top:2.5rem;text-align:center;font-size:0.75rem;opacity:0.8;">
            <p>CONTACT THE NEAREST MARSHAL'S OFFICE IMMEDIATELY.</p>
            <p style="margin-top:0.25rem;">AUTHORITY OF THE MORGAN EDITION MANAGEMENT SYSTEM</p>
        </footer>
    </div>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
