<?php
/**
 * Morgan Edition - 프로필 스킨: VS Code IDE
 * 다크 IDE 배경(#1e1e1e), 모노스페이스, JSON 구문 강조, 파일 탐색기+에디터 2패널
 */
if (!defined('_GNUBOARD_')) exit;

$char_image = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : '';
$ch_name = htmlspecialchars($char['ch_name']);
$ch_side = htmlspecialchars($char['side_name'] ?? '');
$ch_class = htmlspecialchars($char['class_name'] ?? '');
$ch_owner = htmlspecialchars($char['mb_nick']);
$ch_date = date('Y-m-d H:i:s', strtotime($char['ch_datetime']));
$ch_initial = mb_substr($char['ch_name'], 0, 1);
?>

<style>
.skin-vscode { font-family: 'Consolas', 'Courier New', ui-monospace, monospace; color: #d4d4d4; }
.skin-vscode a { color: #9cdcfe; text-decoration: none; }
.skin-vscode a:hover { color: #4fc1ff; text-decoration: underline; }
.skin-vscode .vs-key { color: #9cdcfe; }
.skin-vscode .vs-string { color: #ce9178; }
.skin-vscode .vs-number { color: #b5cea8; }
.skin-vscode .vs-bracket { color: #ffd700; }
.skin-vscode .vs-comment { color: #6a9955; }
.skin-vscode .vs-bool { color: #569cd6; }
.skin-vscode .vs-null { color: #569cd6; }
.skin-vscode .vs-line-num { color: #858585; user-select: none; text-align: right; padding-right: 1rem; min-width: 2rem; display: inline-block; }
.skin-vscode .vs-editor-frame {
    background: #1e1e1e;
    border: 1px solid #333333;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
}
.skin-vscode .vs-titlebar {
    background: #323233;
    color: #cccccc;
    font-size: 0.75rem;
    padding: 0.5rem 1rem;
    text-align: center;
    border-bottom: 1px solid #1e1e1e;
}
.skin-vscode .vs-sidebar {
    width: 12rem;
    background: #252526;
    border-right: 1px solid #1e1e1e;
    flex-shrink: 0;
}
.skin-vscode .vs-sidebar-item {
    font-size: 0.8125rem;
    padding: 0.25rem 0.5rem;
    color: #cccccc;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background 0.15s;
}
.skin-vscode .vs-sidebar-item:hover { background: #2a2d2e; }
.skin-vscode .vs-sidebar-item.active { background: #37373d; color: #ffffff; }
.skin-vscode .vs-tab {
    padding: 0.5rem 1rem;
    background: #2d2d2d;
    color: #969696;
    font-size: 0.8125rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-right: 1px solid #252526;
    cursor: pointer;
}
.skin-vscode .vs-tab.active {
    background: #1e1e1e;
    color: #ffffff;
    border-top: 2px solid #007acc;
}
.skin-vscode .vs-statusbar {
    background: #007acc;
    color: #ffffff;
    font-size: 0.6875rem;
    padding: 0.25rem 1rem;
    display: flex;
    justify-content: space-between;
}
.skin-vscode .vs-code-line { white-space: pre-wrap; line-height: 1.6; font-size: 0.8125rem; }
.skin-vscode .vs-code-line:hover { background: rgba(255,255,255,0.04); }

/* 관계 항목 호버 */
.skin-vscode .vs-rel-item { transition: background 0.15s; padding: 0.25rem 0; }
.skin-vscode .vs-rel-item:hover { background: rgba(255,255,255,0.04); }

/* 뱃지 호버 */
.skin-vscode .vs-badge { transition: all 0.2s; }
.skin-vscode .vs-badge:hover { transform: scale(1.05); background: rgba(156,220,254,0.15); }

/* 버튼 호버 */
.skin-vscode button { transition: all 0.2s; }
.skin-vscode button:hover { background: rgba(0,122,204,0.6) !important; }

/* 모바일: 사이드바 숨김 */
@media (max-width: 640px) {
    .skin-vscode .vs-sidebar { display: none; }
    .skin-vscode .vs-statusbar .vs-status-right { display: none; }
}
</style>

<div class="mg-inner skin-vscode" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" style="display:inline-flex;align-items:center;gap:4px;font-size:0.875rem;color:#9cdcfe;margin-bottom:1rem;">
        <i data-lucide="chevron-left" style="width:16px;height:16px;"></i>
        <span>뒤로</span>
    </a>

    <div class="vs-editor-frame">
        <!-- 타이틀바 -->
        <div class="vs-titlebar">
            profile.json - <?php echo $ch_name; ?> - Visual Studio Code
        </div>

        <!-- 헤더 배너 (에디터 상단 배너처럼) -->
        <?php if ($char_header) { ?>
        <div style="max-height:8rem;overflow:hidden;border-bottom:1px solid #333333;position:relative;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;filter:brightness(60%) saturate(80%);">
            <div style="position:absolute;inset:0;background:linear-gradient(to right, rgba(30,30,30,0.7), transparent 50%, rgba(30,30,30,0.7));"></div>
        </div>
        <?php } ?>

        <div style="display:flex;min-height:400px;">
            <!-- 사이드바 (파일 탐색기) -->
            <div class="vs-sidebar" style="display:flex;flex-direction:column;">
                <div style="padding:0.5rem 1rem;font-size:0.6875rem;font-weight:bold;letter-spacing:0.15em;color:#cccccc;text-transform:uppercase;">
                    Explorer
                </div>
                <div class="vs-sidebar-item" style="font-weight:bold;">
                    <span style="font-size:0.6875rem;">&#9660;</span> MORGAN-BUILDER
                </div>
                <div class="vs-sidebar-item" style="padding-left:1.5rem;">
                    <span style="color:#dcad5a;font-size:0.6875rem;">&#128194;</span> src
                </div>
                <div class="vs-sidebar-item" style="padding-left:1.5rem;">
                    <span style="color:#75beff;font-size:0.6875rem;">&#128194;</span> config
                </div>
                <div class="vs-sidebar-item active" style="padding-left:2.5rem;">
                    <span style="color:#ffd700;font-size:0.6875rem;">{}</span> profile.json
                </div>
                <div class="vs-sidebar-item" style="padding-left:1.5rem;">
                    <span style="color:#6a9955;font-size:0.6875rem;">#</span> stats.md
                </div>
                <?php if (!empty($grouped_fields)) { ?>
                <div class="vs-sidebar-item" style="padding-left:1.5rem;">
                    <span style="color:#75beff;font-size:0.6875rem;">&#128194;</span> fields
                </div>
                <?php foreach ($grouped_fields as $category => $fields) { ?>
                <div class="vs-sidebar-item" style="padding-left:2.5rem;">
                    <span style="color:#ce9178;font-size:0.6875rem;">&#9776;</span> <?php echo htmlspecialchars($category); ?>
                </div>
                <?php } ?>
                <?php } ?>
                <?php if (!empty($char_relations)) { ?>
                <div class="vs-sidebar-item" style="padding-left:1.5rem;">
                    <span style="color:#c586c0;font-size:0.6875rem;">&#128279;</span> relations.json
                </div>
                <?php } ?>
            </div>

            <!-- 에디터 영역 -->
            <div style="flex:1;display:flex;flex-direction:column;min-width:0;">
                <!-- 탭 바 -->
                <div style="background:#252526;display:flex;flex-shrink:0;">
                    <div class="vs-tab active">
                        <span style="color:#ffd700;font-size:0.6875rem;">{}</span> profile.json
                        <span style="color:#666;font-size:0.75rem;margin-left:0.5rem;">&#215;</span>
                    </div>
                </div>

                <!-- 코드 영역 -->
                <div style="flex:1;padding:1rem;overflow:auto;">
                    <?php
                    $line = 1;
                    $json_lines = array();

                    // 빌드 JSON 라인
                    $json_lines[] = '<span class="vs-bracket">{</span>';
                    $json_lines[] = '  <span class="vs-comment">// Morgan Builder Character Profile</span>';
                    $json_lines[] = '  <span class="vs-key">"characterId"</span>: <span class="vs-number">' . (int)$char['ch_id'] . '</span>,';
                    $json_lines[] = '  <span class="vs-key">"name"</span>: <span class="vs-string">"' . $ch_name . '"</span>,';
                    $json_lines[] = '  <span class="vs-key">"owner"</span>: <span class="vs-string">"' . $ch_owner . '"</span>,';
                    if ($ch_class && mg_config('use_class', '1') == '1') {
                        $json_lines[] = '  <span class="vs-key">"class"</span>: <span class="vs-string">"' . $ch_class . '"</span>,';
                    }
                    if ($ch_side && mg_config('use_side', '1') == '1') {
                        $json_lines[] = '  <span class="vs-key">"faction"</span>: <span class="vs-string">"' . $ch_side . '"</span>,';
                    }
                    $json_lines[] = '  <span class="vs-key">"status"</span>: <span class="vs-string">"' . htmlspecialchars($char['ch_state'] == 'approved' ? 'Active' : $char['ch_state']) . '"</span>,';
                    $json_lines[] = '  <span class="vs-key">"createdAt"</span>: <span class="vs-string">"' . $ch_date . '"</span>,';

                    // 전투 스탯
                    if ($_battle_use == '1' && $battle_stat) {
                        $_stat_base = (int)mg_config('battle_stat_base', '5');
                        $bs_hp = (int)($battle_stat['stat_hp'] ?? $_stat_base);
                        $bs_str = (int)($battle_stat['stat_str'] ?? $_stat_base);
                        $bs_dex = (int)($battle_stat['stat_dex'] ?? $_stat_base);
                        $bs_int = (int)($battle_stat['stat_int'] ?? $_stat_base);
                        $bs_stress = (int)($battle_stat['stat_stress'] ?? 0);

                        $json_lines[] = '';
                        $json_lines[] = '  <span class="vs-comment">// Combat Statistics</span>';
                        $json_lines[] = '  <span class="vs-key">"combatStats"</span>: <span class="vs-bracket">{</span>';

                        if ($battle_hp && $battle_hp['max_hp'] > 0) {
                            $json_lines[] = '    <span class="vs-key">"currentHp"</span>: <span class="vs-number">' . (int)$battle_hp['current_hp'] . '</span>,';
                            $json_lines[] = '    <span class="vs-key">"maxHp"</span>: <span class="vs-number">' . (int)$battle_hp['max_hp'] . '</span>,';
                        }

                        $json_lines[] = '    <span class="vs-key">"hp"</span>: <span class="vs-number">' . $bs_hp . '</span>,';
                        $json_lines[] = '    <span class="vs-key">"str"</span>: <span class="vs-number">' . $bs_str . '</span>,';
                        $json_lines[] = '    <span class="vs-key">"dex"</span>: <span class="vs-number">' . $bs_dex . '</span>,';
                        $json_lines[] = '    <span class="vs-key">"int"</span>: <span class="vs-number">' . $bs_int . '</span>,';
                        $json_lines[] = '    <span class="vs-key">"stress"</span>: <span class="vs-number">' . $bs_stress . '</span>';
                        $json_lines[] = '  <span class="vs-bracket">}</span>,';
                    }

                    // 업적
                    if (!empty($achievement_showcase)) {
                        $json_lines[] = '';
                        $json_lines[] = '  <span class="vs-comment">// Achievements</span>';
                        $json_lines[] = '  <span class="vs-key">"achievements"</span>: <span class="vs-bracket">[</span>';
                        $ach_count = count($achievement_showcase);
                        $ach_i = 0;
                        foreach ($achievement_showcase as $acd) {
                            $a_name = htmlspecialchars($acd['tier_name'] ?: $acd['ac_name']);
                            $ach_i++;
                            $comma = $ach_i < $ach_count ? ',' : '';
                            $json_lines[] = '    <span class="vs-string">"' . $a_name . '"</span>' . $comma;
                        }
                        $json_lines[] = '  <span class="vs-bracket">]</span>,';
                    }

                    // 프로필 필드
                    if (count($grouped_fields) > 0) {
                        $json_lines[] = '';
                        $json_lines[] = '  <span class="vs-comment">// Profile Fields</span>';
                        $json_lines[] = '  <span class="vs-key">"profile"</span>: <span class="vs-bracket">{</span>';
                        $gf_count = count($grouped_fields);
                        $gf_i = 0;
                        foreach ($grouped_fields as $category => $fields) {
                            $gf_i++;
                            $cat_key = htmlspecialchars($category);
                            $json_lines[] = '    <span class="vs-key">"' . $cat_key . '"</span>: <span class="vs-bracket">{</span>';
                            $f_count = count($fields);
                            $f_i = 0;
                            foreach ($fields as $field) {
                                $f_i++;
                                $f_name = htmlspecialchars($field['pf_name']);
                                $f_val = strip_tags(mg_render_profile_value($field));
                                $f_val = htmlspecialchars($f_val);
                                $comma = $f_i < $f_count ? ',' : '';
                                $json_lines[] = '      <span class="vs-key">"' . $f_name . '"</span>: <span class="vs-string">"' . $f_val . '"</span>' . $comma;
                            }
                            $cat_comma = $gf_i < $gf_count ? ',' : '';
                            $json_lines[] = '    <span class="vs-bracket">}</span>' . $cat_comma;
                        }
                        $json_lines[] = '  <span class="vs-bracket">}</span>';
                    }

                    $json_lines[] = '<span class="vs-bracket">}</span>';

                    // 렌더링
                    foreach ($json_lines as $jl) {
                        echo '<div class="vs-code-line"><span class="vs-line-num">' . $line . '</span>' . $jl . '</div>';
                        $line++;
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- 스테이터스바 -->
        <div class="vs-statusbar">
            <div style="display:flex;gap:1rem;">
                <span>main*</span>
                <span>&oslash; 0 &#9888; 0</span>
            </div>
            <div style="display:flex;gap:1rem;" class="vs-status-right">
                <span>Ln <?php echo $line - 1; ?>, Col 2</span>
                <span>Spaces: 2</span>
                <span>UTF-8</span>
                <span>JSON</span>
            </div>
        </div>
    </div>

    <!-- 이미지 프리뷰 (사이드바 아래) -->
    <?php if ($char_image) { ?>
    <div style="margin-top:1rem;background:#252526;border:1px solid #333333;border-radius:0.5rem;overflow:hidden;padding:1rem;">
        <div style="font-size:0.6875rem;color:#858585;margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.1em;">&#9656; Image Preview — <?php echo $ch_name; ?></div>
        <div style="text-align:center;">
            <img src="<?php echo $char_image; ?>" alt="<?php echo $ch_name; ?>" style="max-height:200px;border-radius:0.25rem;border:1px solid #333;">
        </div>
    </div>
    <?php } ?>

    <!-- 액션 버튼 -->
    <div style="margin-top:1rem;display:flex;gap:0.5rem;">
        <?php if ($can_request_relation) { ?>
        <button type="button" onclick="openRelRequestModal()" style="font-size:0.75rem;background:#007acc;border:none;color:#fff;padding:0.375rem 1rem;border-radius:0.25rem;cursor:pointer;">&#128279; 관계 신청</button>
        <?php } ?>
        <?php if ($is_owner) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" style="font-size:0.75rem;background:#333;border:1px solid #555;color:#ccc;padding:0.375rem 1rem;border-radius:0.25rem;text-decoration:none;">&#9998; 수정</a>
        <?php } ?>
    </div>

    <!-- 관계 섹션 -->
    <?php if (!empty($char_relations)) { ?>
    <div style="margin-top:1rem;background:#1e1e1e;border:1px solid #333;border-radius:0.5rem;overflow:hidden;">
        <div style="background:#252526;padding:0.5rem 1rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #333;">
            <span style="font-size:0.75rem;color:#cccccc;text-transform:uppercase;letter-spacing:0.1em;">&#128279; Relations</span>
            <div style="display:flex;gap:0.5rem;align-items:center;">
                <?php if ($is_owner) { ?>
                <button type="button" id="rel-graph-save" style="font-size:0.6875rem;color:#858585;background:none;border:none;cursor:pointer;display:none;">배치 저장</button>
                <?php } ?>
                <button type="button" id="rel-graph-toggle" style="font-size:0.6875rem;color:#007acc;background:none;border:none;cursor:pointer;">관계도 보기</button>
            </div>
        </div>
        <div style="padding:0.75rem 1rem;">
            <?php foreach ($char_relations as $rel) {
                $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                $other_name = htmlspecialchars($is_a ? $rel['name_b'] : $rel['name_a']);
                $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                $my_label = htmlspecialchars($is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']));
                $rel_color = $rel['cr_color'] ?: '#95a5a6';
            ?>
            <div class="vs-rel-item" style="display:flex;align-items:center;gap:0.75rem;font-size:0.8125rem;border-bottom:1px solid #2a2d2e;padding:0.5rem 0;">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" style="width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid #555;">
                <?php } else { ?>
                <div style="width:28px;height:28px;border-radius:50%;background:#333;display:flex;align-items:center;justify-content:center;color:#858585;font-size:0.6875rem;"><?php echo mb_substr($is_a ? $rel['name_b'] : $rel['name_a'], 0, 1); ?></div>
                <?php } ?>
                <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;background:<?php echo htmlspecialchars($rel_color); ?>;"></span>
                <span style="color:#6a9955;font-style:italic;"><?php echo $my_label; ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" style="margin-left:auto;"><?php echo $other_name; ?></a>
            </div>
            <?php } ?>
        </div>
        <!-- 인라인 관계도 -->
        <div id="rel-graph-wrap" class="hidden" style="padding:0.75rem;">
            <div id="rel-graph-container" style="height:400px;background:#1a1a1a;border-radius:0.25rem;border:1px solid #333;"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>
