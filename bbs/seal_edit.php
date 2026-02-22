<?php
/**
 * Morgan Edition - 인장 편집 (단일 페이지 통합)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

if (!mg_config('seal_enable', 1)) {
    alert('인장 시스템이 비활성화되어 있습니다.');
}

$_lv = mg_check_member_level('seal', $member['mb_level']);
if (!$_lv['allowed']) { alert_close("인장은 회원 레벨 {$_lv['required']} 이상부터 이용 가능합니다."); }

$mb_id = $member['mb_id'];

// 인장 데이터 로드
$seal = sql_fetch("SELECT * FROM {$g5['mg_seal_table']} WHERE mb_id = '".sql_real_escape_string($mb_id)."'");
if (!$seal) {
    $seal = array(
        'seal_use' => 1,
        'seal_tagline' => '',
        'seal_content' => '',
        'seal_image' => '',
        'seal_link' => '',
        'seal_link_text' => '',
        'seal_text_color' => '',
        'seal_layout' => '',
    );
}

// 대표 캐릭터 + 캐릭터 목록
$main_char = mg_get_main_character($mb_id);
$my_characters = array();
$sql_ch = "SELECT ch_id, ch_name, ch_thumb, ch_main FROM {$g5['mg_character_table']}
           WHERE mb_id = '".sql_real_escape_string($mb_id)."' AND ch_state = 'approved'
           ORDER BY ch_main DESC, ch_name ASC";
$res_ch = sql_query($sql_ch);
while ($rch = sql_fetch_array($res_ch)) {
    $my_characters[] = $rch;
}

// 보유 인장 스킨 (인벤토리)
$seal_bg_items = array();
$seal_frame_items = array();
$inv_result = sql_query("SELECT i.si_id, i.si_name, i.si_image, i.si_type, i.si_effect
    FROM {$g5['mg_inventory_table']} v
    JOIN {$g5['mg_shop_item_table']} i ON v.si_id = i.si_id
    WHERE v.mb_id = '".sql_real_escape_string($mb_id)."'
    AND i.si_type IN ('seal_bg', 'seal_frame')");
while ($row = sql_fetch_array($inv_result)) {
    $row['si_effect'] = json_decode($row['si_effect'], true);
    if ($row['si_type'] === 'seal_bg') $seal_bg_items[] = $row;
    else $seal_frame_items[] = $row;
}

// 현재 적용 중인 스킨
$active_bg = mg_get_active_items($mb_id, 'seal_bg');
$active_bg = !empty($active_bg) ? $active_bg[0] : null;
$active_frame = mg_get_active_items($mb_id, 'seal_frame');
$active_frame = !empty($active_frame) ? $active_frame[0] : null;

// 트로피 쇼케이스
$trophy_display = array();
if (function_exists('mg_get_achievement_display')) {
    $trophy_display = mg_get_achievement_display($mb_id);
}

// 설정값
$tagline_max = (int)mg_config('seal_tagline_max', 50);
$content_max = (int)mg_config('seal_content_max', 300);
$image_upload = (int)mg_config('seal_image_upload', 1);
$image_url_allow = (int)mg_config('seal_image_url', 1);
$link_allow = (int)mg_config('seal_link_allow', 1);
$trophy_slots = (int)mg_config('seal_trophy_slots', 3);

// 기존 레이아웃 로드
$seal_layout_json = $seal['seal_layout'] ?? '';

// 미리보기용 배경/프레임 스타일
$preview_bg_style = 'background:#2b2d31;';
$preview_border_style = 'border:1px solid #3f4147;';
if ($active_bg) {
    $eff = is_string($active_bg['si_effect']) ? json_decode($active_bg['si_effect'], true) : ($active_bg['si_effect'] ?? array());
    if (!empty($eff['bg_image'])) {
        $preview_bg_style = "background:url('" . htmlspecialchars($eff['bg_image']) . "') center/cover no-repeat;";
    } elseif (!empty($eff['bg_color'])) {
        $preview_bg_style = "background:" . htmlspecialchars($eff['bg_color']) . ";";
    }
}
if ($active_frame) {
    $eff = is_string($active_frame['si_effect']) ? json_decode($active_frame['si_effect'], true) : ($active_frame['si_effect'] ?? array());
    if (!empty($eff['border_color'])) {
        $preview_border_style = "border:2px solid " . htmlspecialchars($eff['border_color']) . ";";
    }
}

// 트로피 데이터 for JS
$trophies_js = array();
foreach ($trophy_display as $tr) {
    if (!$tr) continue;
    $trophies_js[] = array(
        'name' => $tr['tier_name'] ?: ($tr['ac_name'] ?? ''),
        'icon' => $tr['tier_icon'] ?: ($tr['ac_icon'] ?? ''),
        'rarity' => $tr['ac_rarity'] ?? 'common',
    );
}

// 인장 이미지 URL
$seal_image_url = '';
if (!empty($seal['seal_image'])) {
    if (strpos($seal['seal_image'], 'http') === 0) {
        $seal_image_url = $seal['seal_image'];
    } else {
        $seal_image_url = MG_SEAL_IMAGE_URL . '/' . $seal['seal_image'];
    }
}

// JS용 데이터 객체
$js_seal_data = json_encode(array(
    'mb_nick' => $member['mb_nick'],
    'char_thumb' => ($main_char && !empty($main_char['ch_thumb'])) ? MG_CHAR_IMAGE_URL.'/'.$main_char['ch_thumb'] : '',
    'tagline' => $seal['seal_tagline'] ?? '',
    'content' => $seal['seal_content'] ?? '',
    'seal_image' => $seal_image_url,
    'link' => $seal['seal_link'] ?? '',
    'link_text' => $seal['seal_link_text'] ?? '',
    'text_color' => $seal['seal_text_color'] ?? '',
    'trophies' => $trophies_js,
), JSON_UNESCAPED_UNICODE);

$g5['title'] = '내 인장 편집';
include_once(G5_THEME_PATH.'/head.php');
?>

<!-- GridStack CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@12/dist/gridstack.min.css">
<script src="https://cdn.jsdelivr.net/npm/gridstack@12/dist/gridstack-all.js"></script>

<style>
/* 그리드 편집기 — 격자선 */
#seal-grid.grid-stack {
    --_col-w: calc(100% / 16);
    --_row-h: var(--gs-cell-height, 50px);
    min-height: 200px;
    background-color: #0d0e10;
    background-image:
        repeating-linear-gradient(to right, transparent, transparent calc(var(--_col-w) - 1px), #3b3f48 calc(var(--_col-w) - 1px), #3b3f48 var(--_col-w)),
        repeating-linear-gradient(to bottom, transparent, transparent calc(var(--_row-h) - 1px), #3b3f48 calc(var(--_row-h) - 1px), #3b3f48 var(--_row-h));
    border-radius: 0.5rem;
}
#seal-grid .grid-stack-item-content {
    background: var(--mg-bg-tertiary);
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.06);
    overflow: hidden;
}
#seal-grid .grid-stack-placeholder > .placeholder-content {
    border: 2px dashed var(--mg-accent) !important;
    background: rgba(245,159,10,0.08) !important;
    border-radius: 6px;
}
/* 선택된 위젯 강조 */
#seal-grid .grid-stack-item.gs-selected > .grid-stack-item-content {
    outline: 2px solid var(--mg-accent);
    outline-offset: -1px;
}
.seal-el { height:100%; display:flex; flex-direction:column; position:relative; padding:3px; }
.seal-el-label {
    font-size: 9px; color: var(--mg-text-muted); letter-spacing: 0.04em;
    line-height: 1; margin-bottom: 2px; flex-shrink: 0;
}
.seal-el-body {
    flex:1; overflow:hidden; display:flex; align-items:center; justify-content:center;
    font-size: 11px; color: var(--mg-text-secondary);
}
.seal-el-body img { width:100%; height:100%; object-fit:cover; border-radius:4px; }
.seal-el-del {
    position:absolute; top:1px; right:1px; width:16px; height:16px; border-radius:50%;
    background:rgba(239,68,68,0.85); color:#fff; font-size:10px; line-height:16px; text-align:center;
    cursor:pointer; border:none; display:none; z-index:5;
}
#seal-grid .grid-stack-item:hover .seal-el-del { display:block; }

/* 요소 팔레트 */
.seal-pal-btn {
    padding:5px 10px; background:var(--mg-bg-tertiary); border:1px solid rgba(255,255,255,0.06);
    border-radius:6px; color:var(--mg-text-secondary); font-size:12px; cursor:pointer;
    transition: background 0.15s, color 0.15s; white-space:nowrap;
}
.seal-pal-btn:hover:not(.is-off) { background:var(--mg-accent); color:#fff; }
.seal-pal-btn.is-off { opacity:0.35; cursor:not-allowed; }

/* CSS Grid 미리보기 */
.mg-seal-grid {
    display: grid;
    grid-template-columns: repeat(16, 1fr);
    grid-template-rows: repeat(6, 1fr);
    aspect-ratio: 16/6;
    border-radius: 12px;
    overflow: hidden;
    padding: 6px;
    gap: 3px;
}
.mg-seal-grid > div { overflow:hidden; display:flex; align-items:center; min-width:0; }

/* 속성 패널 트랜지션 */
#seal-prop-panel { transition: all 0.2s ease; }

/* 모바일 폴백 */
@media (max-width: 767px) {
    #layout-pc-editor { display: none !important; }
    #seal-prop-panel { display: none !important; }
    #mobile-editor { display: block !important; }
}
@media (min-width: 768px) {
    #mobile-editor { display: none !important; }
}
</style>

<div class="mg-inner">
    <a href="javascript:history.back();" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        <span>뒤로</span>
    </a>

    <h1 class="text-2xl font-bold text-mg-text-primary mb-6">내 인장 편집</h1>

    <form id="seal-form">
        <input type="hidden" name="seal_layout" id="f_seal_layout" value="<?php echo htmlspecialchars($seal_layout_json); ?>">
        <input type="hidden" name="seal_tagline" id="f_tagline" value="<?php echo htmlspecialchars($seal['seal_tagline']); ?>">
        <input type="hidden" name="seal_content" id="f_content" value="<?php echo htmlspecialchars($seal['seal_content']); ?>">
        <input type="hidden" name="seal_text_color" id="f_text_color" value="<?php echo htmlspecialchars($seal['seal_text_color'] ?? ''); ?>">
        <?php if ($link_allow) { ?>
        <input type="hidden" name="seal_link" id="f_link" value="<?php echo htmlspecialchars($seal['seal_link']); ?>">
        <input type="hidden" name="seal_link_text" id="f_link_text" value="<?php echo htmlspecialchars($seal['seal_link_text']); ?>">
        <?php } ?>

        <!-- 1. 인장 사용 토글 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 mb-4">
            <label class="flex items-center justify-between cursor-pointer">
                <div>
                    <span class="font-medium text-mg-text-primary">인장 사용</span>
                    <p class="text-xs text-mg-text-muted mt-0.5">OFF 시 모든 위치에서 인장이 숨겨집니다</p>
                </div>
                <div class="relative">
                    <input type="checkbox" name="seal_use" id="f_seal_use" value="1" <?php echo $seal['seal_use'] ? 'checked' : ''; ?> class="sr-only peer">
                    <div class="w-11 h-6 bg-mg-bg-tertiary rounded-full peer-checked:bg-mg-accent transition-colors"></div>
                    <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                </div>
            </label>
        </div>

        <!-- 2. 대표 캐릭터 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-4">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">대표 캐릭터</h2>
            </div>
            <div class="p-4">
                <?php if (!empty($my_characters)) { ?>
                <div class="space-y-1.5">
                    <?php foreach ($my_characters as $mc) {
                        $is_main = $mc['ch_main'] ? true : false;
                    ?>
                    <label class="flex items-center gap-3 p-2 rounded-lg cursor-pointer transition-colors hover:bg-mg-bg-tertiary/50 <?php echo $is_main ? 'bg-mg-accent/10 ring-1 ring-mg-accent/30' : ''; ?>">
                        <input type="radio" name="main_ch_id" value="<?php echo $mc['ch_id']; ?>" <?php echo $is_main ? 'checked' : ''; ?> class="sr-only peer">
                        <div class="w-10 h-10 rounded-lg bg-mg-bg-tertiary overflow-hidden flex-shrink-0 flex items-center justify-center peer-checked:ring-2 peer-checked:ring-mg-accent">
                            <?php if (!empty($mc['ch_thumb'])) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$mc['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                            <?php } else { ?>
                            <svg class="w-5 h-5 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <?php } ?>
                        </div>
                        <span class="text-sm text-mg-text-primary font-medium peer-checked:text-mg-accent"><?php echo htmlspecialchars($mc['ch_name']); ?></span>
                        <?php if ($is_main) { ?>
                        <span class="ml-auto text-[10px] text-mg-accent bg-mg-accent/15 px-1.5 py-0.5 rounded">현재</span>
                        <?php } ?>
                    </label>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <p class="text-xs text-mg-text-muted">승인된 캐릭터가 없습니다. <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="text-mg-accent hover:underline">캐릭터 만들기</a></p>
                <?php } ?>
            </div>
        </div>

        <!-- 3. PC 편집기: 요소 팔레트 + GridStack 캔버스 -->
        <div id="layout-pc-editor" class="space-y-4 mb-4">
            <!-- 요소 팔레트 -->
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
                <p class="text-xs text-mg-text-muted mb-3">요소를 추가한 뒤 캔버스에서 드래그하여 배치하세요. <strong>위젯을 클릭</strong>하면 내용을 편집할 수 있습니다.</p>
                <div class="flex flex-wrap gap-2" id="seal-palette">
                    <button type="button" class="seal-pal-btn" data-type="character">+ 캐릭터</button>
                    <button type="button" class="seal-pal-btn" data-type="nickname">+ 닉네임</button>
                    <button type="button" class="seal-pal-btn" data-type="tagline">+ 한마디</button>
                    <button type="button" class="seal-pal-btn" data-type="text">+ 텍스트</button>
                    <button type="button" class="seal-pal-btn" data-type="image">+ 이미지</button>
                    <?php if ($link_allow) { ?>
                    <button type="button" class="seal-pal-btn" data-type="link">+ 링크</button>
                    <?php } ?>
                    <?php for ($s = 1; $s <= $trophy_slots; $s++) { ?>
                    <button type="button" class="seal-pal-btn" data-type="trophy" data-slot="<?php echo $s; ?>">+ 트로피<?php echo $s; ?></button>
                    <?php } ?>
                    <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-[11px] text-mg-accent hover:underline self-center">업적 관리</a>
                    <span class="flex-1"></span>
                    <button type="button" class="seal-pal-btn" id="btn-reset-layout" style="border-color:var(--mg-accent);color:var(--mg-accent);">기본 레이아웃</button>
                </div>
            </div>

            <!-- GridStack 캔버스 -->
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
                <p class="text-xs text-mg-text-muted mb-2">16 &times; 6 격자 &mdash; 드래그로 이동, 모서리 드래그로 크기 조절, 클릭으로 편집</p>
                <div style="max-width:800px;margin:0 auto;">
                    <div class="grid-stack" id="seal-grid"></div>
                </div>
            </div>
        </div>

        <!-- 4. 속성 편집 패널 (위젯 클릭 시 표시) -->
        <div id="seal-prop-panel" class="hidden bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-4">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h3 class="font-medium text-mg-text-primary">
                    <span id="pp-name" class="text-mg-accent"></span> 속성
                </h3>
                <button type="button" id="pp-close" class="text-mg-text-muted hover:text-mg-text-primary text-lg leading-none">&times;</button>
            </div>
            <div class="p-4 space-y-4">
                <!-- 한마디 편집 -->
                <div id="pp-tagline" class="hidden">
                    <label class="text-xs font-medium text-mg-text-secondary mb-1 block">한마디</label>
                    <input type="text" id="pp-tagline-input" maxlength="<?php echo $tagline_max; ?>" placeholder="나를 표현하는 한마디..."
                           class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                    <p class="text-xs text-mg-text-muted mt-1 text-right"><span id="pp-tagline-count">0</span>/<?php echo $tagline_max; ?>자</p>
                </div>

                <!-- 텍스트 편집 -->
                <div id="pp-text" class="hidden">
                    <label class="text-xs font-medium text-mg-text-secondary mb-1 block">자유 텍스트</label>
                    <textarea id="pp-text-input" rows="4" maxlength="<?php echo $content_max; ?>"
                              placeholder="자유롭게 소개를 작성해보세요..."
                              class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none resize-none"></textarea>
                    <p class="text-xs text-mg-text-muted mt-1 text-right"><span id="pp-text-count">0</span>/<?php echo $content_max; ?>자</p>
                </div>

                <!-- 이미지 편집 -->
                <div id="pp-image" class="hidden">
                    <label class="text-xs font-medium text-mg-text-secondary mb-2 block">이미지 (최대 600x200, <?php echo mg_config('seal_image_max_size', 500); ?>KB)</label>
                    <div id="pp-image-preview" class="hidden mb-2">
                        <div class="relative inline-block">
                            <img id="pp-image-img" src="" alt="" class="max-w-full max-h-[100px] rounded border border-mg-bg-tertiary">
                            <button type="button" id="pp-image-remove" class="absolute -top-2 -right-2 w-5 h-5 bg-mg-error text-white rounded-full text-xs flex items-center justify-center">&times;</button>
                        </div>
                    </div>
                    <?php if ($image_upload) { ?>
                    <label class="inline-flex items-center gap-1 px-3 py-1.5 bg-mg-bg-tertiary hover:bg-mg-bg-primary text-mg-text-secondary text-xs rounded-lg cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        파일 선택
                        <input type="file" accept="image/*" id="pp-image-file" class="hidden">
                    </label>
                    <?php } ?>
                </div>

                <!-- 링크 편집 -->
                <?php if ($link_allow) { ?>
                <div id="pp-link" class="hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-mg-text-secondary mb-1 block">링크 URL</label>
                            <input type="url" id="pp-link-url" placeholder="https://..."
                                   class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-mg-text-secondary mb-1 block">링크 텍스트</label>
                            <input type="text" id="pp-link-text" placeholder="트위터, 블로그 등..." maxlength="50"
                                   class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                        </div>
                    </div>
                </div>
                <?php } ?>

                <!-- 트로피 안내 -->
                <div id="pp-trophy" class="hidden">
                    <p class="text-xs text-mg-text-muted">트로피는 <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-mg-accent hover:underline">업적 페이지</a>에서 관리할 수 있습니다.</p>
                    <?php if (!empty($trophy_display)) { ?>
                    <div class="flex gap-2 flex-wrap mt-2">
                        <?php
                        $rarity_colors = array('common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6', 'epic' => '#a855f7', 'legendary' => '#f59e0b');
                        $shown = 0;
                        foreach ($trophy_display as $tr) {
                            if (!$tr || $shown >= $trophy_slots) break;
                            $t_name = $tr['tier_name'] ?: $tr['ac_name'];
                            $t_rarity = $tr['ac_rarity'] ?? 'common';
                            $t_color = $rarity_colors[$t_rarity] ?? '#949ba4';
                            $t_icon = $tr['tier_icon'] ?: ($tr['ac_icon'] ?: '');
                        ?>
                        <div class="flex flex-col items-center p-2 rounded-lg" style="border:2px solid <?php echo $t_color; ?>;">
                            <?php if ($t_icon) { ?>
                            <img src="<?php echo htmlspecialchars($t_icon); ?>" alt="" class="w-8 h-8 object-contain">
                            <?php } else { ?>
                            <span class="text-lg">&#127942;</span>
                            <?php } ?>
                            <span class="text-[10px] text-center mt-0.5 max-w-[60px] truncate" style="color:<?php echo $t_color; ?>;"><?php echo htmlspecialchars($t_name); ?></span>
                        </div>
                        <?php $shown++; } ?>
                    </div>
                    <?php } ?>
                </div>

                <!-- 캐릭터/닉네임 안내 -->
                <div id="pp-info" class="hidden">
                    <p class="text-xs text-mg-text-muted" id="pp-info-text"></p>
                </div>

                <!-- 텍스트 정렬 (tagline, text, nickname, link) -->
                <div id="pp-align" class="hidden border-t border-mg-bg-tertiary pt-3">
                    <p class="text-xs font-medium text-mg-text-secondary mb-2">텍스트 정렬</p>
                    <div class="flex gap-1">
                        <button type="button" data-align="left" class="sp-align-btn flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-mg-bg-tertiary text-mg-text-secondary transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 6h18M3 12h12M3 18h16"/></svg>
                            좌측
                        </button>
                        <button type="button" data-align="center" class="sp-align-btn flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-mg-bg-tertiary text-mg-text-secondary transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 6h18M6 12h12M5 18h14"/></svg>
                            가운데
                        </button>
                        <button type="button" data-align="right" class="sp-align-btn flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs border border-mg-bg-tertiary text-mg-text-secondary transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="2" d="M3 6h18M9 12h12M5 18h16"/></svg>
                            우측
                        </button>
                    </div>
                </div>

                <!-- 요소별 스타일 (tagline, text, trophy) -->
                <div id="pp-style" class="hidden border-t border-mg-bg-tertiary pt-3">
                    <p class="text-xs font-medium text-mg-text-secondary mb-2">요소 스타일</p>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="sp-color-check" class="rounded border-mg-bg-tertiary">
                            <span class="text-xs text-mg-text-secondary">글자색</span>
                            <input type="color" id="sp-color" value="#ffffff" class="w-7 h-7 rounded border border-mg-bg-tertiary cursor-pointer" style="padding:1px;">
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="sp-bgcolor-check" class="rounded border-mg-bg-tertiary">
                            <span class="text-xs text-mg-text-secondary">배경색</span>
                            <input type="color" id="sp-bgcolor" value="#2b2d31" class="w-7 h-7 rounded border border-mg-bg-tertiary cursor-pointer" style="padding:1px;">
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" id="sp-bordercolor-check" class="rounded border-mg-bg-tertiary">
                            <span class="text-xs text-mg-text-secondary">테두리색</span>
                            <input type="color" id="sp-bordercolor" value="#3f4147" class="w-7 h-7 rounded border border-mg-bg-tertiary cursor-pointer" style="padding:1px;">
                        </label>
                    </div>
                    <p class="text-[11px] text-mg-text-muted mt-2">체크를 해제하면 기본 스타일로 돌아갑니다.</p>
                </div>
            </div>
        </div>

        <!-- 5. 모바일 폴백 -->
        <div id="mobile-editor" class="space-y-4 mb-4" style="display:none;">
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 text-center mb-4">
                <svg class="w-10 h-10 mx-auto mb-3 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <p class="text-sm text-mg-text-secondary font-medium">레이아웃 편집은 PC에서만 가능합니다</p>
                <p class="text-xs text-mg-text-muted mt-1">아래에서 텍스트와 이미지를 수정할 수 있습니다</p>
            </div>

            <!-- 모바일: 한마디 -->
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">한마디</h2>
                </div>
                <div class="p-4">
                    <input type="text" id="m-tagline" maxlength="<?php echo $tagline_max; ?>" placeholder="나를 표현하는 한마디..."
                           value="<?php echo htmlspecialchars($seal['seal_tagline']); ?>"
                           class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                </div>
            </div>

            <!-- 모바일: 자유 텍스트 -->
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">자유 텍스트</h2>
                </div>
                <div class="p-4">
                    <textarea id="m-content" rows="4" maxlength="<?php echo $content_max; ?>"
                              placeholder="자유롭게 소개를 작성해보세요..."
                              class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none resize-none"><?php echo htmlspecialchars($seal['seal_content']); ?></textarea>
                </div>
            </div>

            <!-- 모바일: 이미지 -->
            <?php if ($image_upload || $image_url_allow) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">이미지</h2>
                </div>
                <div class="p-4">
                    <div id="m-image-preview" class="<?php echo $seal_image_url ? '' : 'hidden'; ?> mb-2">
                        <div class="relative inline-block">
                            <img id="m-image-img" src="<?php echo htmlspecialchars($seal_image_url); ?>" alt="" class="max-w-full max-h-[100px] rounded border border-mg-bg-tertiary">
                            <button type="button" id="m-image-remove" class="absolute -top-2 -right-2 w-5 h-5 bg-mg-error text-white rounded-full text-xs flex items-center justify-center">&times;</button>
                        </div>
                    </div>
                    <?php if ($image_upload) { ?>
                    <label class="inline-flex items-center gap-1 px-3 py-1.5 bg-mg-bg-tertiary hover:bg-mg-bg-primary text-mg-text-secondary text-xs rounded-lg cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        파일 선택
                        <input type="file" accept="image/*" id="m-image-file" class="hidden">
                    </label>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>

            <!-- 모바일: 링크 -->
            <?php if ($link_allow) { ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
                <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                    <h2 class="font-medium text-mg-text-primary">링크</h2>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-medium text-mg-text-secondary mb-1 block">URL</label>
                            <input type="url" id="m-link-url" placeholder="https://..." value="<?php echo htmlspecialchars($seal['seal_link']); ?>"
                                   class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-medium text-mg-text-secondary mb-1 block">텍스트</label>
                            <input type="text" id="m-link-text" placeholder="트위터, 블로그 등..." maxlength="50" value="<?php echo htmlspecialchars($seal['seal_link_text']); ?>"
                                   class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- 6. 전역 텍스트 색상 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-4">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">전역 텍스트 색상</h2>
            </div>
            <div class="p-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php
                    $colors = array('' => '기본', '#ffffff' => '흰색', '#b5bac1' => '회색', '#da7756' => '주황', '#a855f7' => '보라', '#60a5fa' => '하늘', '#4ade80' => '초록', '#facc15' => '노랑', '#fb7185' => '핑크');
                    foreach ($colors as $hex => $name) {
                        $active = ($seal['seal_text_color'] ?? '') === $hex ? 'ring-2 ring-mg-accent' : '';
                    ?>
                    <button type="button" data-color="<?php echo $hex; ?>"
                            class="seal-color-btn w-7 h-7 rounded-full border border-mg-bg-tertiary flex items-center justify-center <?php echo $active; ?>"
                            style="<?php echo $hex ? 'background:'.$hex : 'background:var(--mg-bg-secondary,#2b2d31);'; ?>"
                            title="<?php echo $name; ?>">
                        <?php if (!$hex) { ?><span class="text-[10px] text-mg-text-muted">A</span><?php } ?>
                    </button>
                    <?php } ?>
                </div>
                <p class="text-[11px] text-mg-text-muted mt-2">요소별 개별 글자색이 설정된 경우 해당 설정이 우선 적용됩니다.</p>
            </div>
        </div>

        <!-- 7. 미리보기 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-4">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">미리보기</h2>
            </div>
            <div class="p-4">
                <div id="seal-preview" style="max-width:800px;margin:0 auto;">
                    <p class="text-sm text-mg-text-muted text-center py-4">미리보기 로딩 중...</p>
                </div>
            </div>
        </div>

        <!-- 8. 저장 -->
        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 bg-mg-accent hover:bg-mg-accent-hover text-white font-medium rounded-lg transition-colors">저장</button>
        </div>
    </form>
</div>

<script>
(function(){
// ============================
// Configuration
// ============================
var SEAL_UPDATE_URL = '<?php echo G5_BBS_URL; ?>/seal_edit_update.php';
var SEAL_IMAGE_URL  = '<?php echo G5_BBS_URL; ?>/seal_image_upload.php';
var SD = <?php echo $js_seal_data; ?>;
var SAVED_LAYOUT = <?php echo $seal_layout_json ? $seal_layout_json : 'null'; ?>;
var TROPHY_SLOTS = <?php echo $trophy_slots; ?>;
var BG_STYLE = <?php echo json_encode($preview_bg_style); ?>;
var BORDER_STYLE = <?php echo json_encode($preview_border_style); ?>;
var LINK_ALLOW = <?php echo $link_allow ? 'true' : 'false'; ?>;

var RARITY_COLORS = {common:'#949ba4',uncommon:'#22c55e',rare:'#3b82f6',epic:'#a855f7',legendary:'#f59e0b'};

var GRID_COLS = 16;
var GRID_ROWS = 6;

var DEFS = {
    character:{label:'캐릭터', unique:true, w:3, h:4, minW:2, minH:2, maxW:5, maxH:6},
    nickname: {label:'닉네임', unique:true, w:4, h:1, minW:2, minH:1, maxW:10, maxH:3},
    tagline:  {label:'한마디', unique:true, w:6, h:1, minW:2, minH:1, maxW:12, maxH:3},
    text:     {label:'텍스트', unique:true, w:6, h:2, minW:2, minH:1, maxW:12, maxH:4},
    image:    {label:'이미지', unique:true, w:4, h:2, minW:1, minH:1, maxW:8, maxH:4},
    link:     {label:'링크',   unique:true, w:3, h:1, minW:1, minH:1, maxW:6, maxH:1},
    trophy:   {label:'트로피', unique:false,w:1, h:1, minW:1, minH:1, maxW:2, maxH:2}
};

var DEFAULT_LAYOUT = [
    {type:'character', x:0, y:0, w:3, h:4},
    {type:'nickname',  x:3, y:0, w:5, h:1},
    {type:'tagline',   x:3, y:1, w:8, h:1},
    {type:'text',      x:3, y:2, w:8, h:2},
    {type:'trophy',    x:13, y:0, w:1, h:1, slot:1},
    {type:'trophy',    x:14, y:0, w:1, h:1, slot:2},
    {type:'trophy',    x:15, y:0, w:1, h:1, slot:3}
];

var grid = null;
var gridReady = false;
var isMobile = window.innerWidth < 768;

// ============================
// Helpers
// ============================
function escHtml(s) {
    if (!s) return '';
    var d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}
function $(id) { return document.getElementById(id); }

// ============================
// 요소별 스타일 저장
// ============================
var _elementStyles = {};
if (SAVED_LAYOUT && SAVED_LAYOUT.elements) {
    SAVED_LAYOUT.elements.forEach(function(el) {
        if (el.style) {
            var gid = (el.type === 'trophy') ? 'trophy-'+(el.slot||1) : el.type;
            _elementStyles[gid] = el.style;
        }
    });
}

var _selectedWidget = null;

// ============================
// 모바일 ↔ PC 데이터 동기화
// ============================
function syncToHidden() {
    if (isMobile) {
        $('f_tagline').value = $('m-tagline') ? $('m-tagline').value : '';
        $('f_content').value = $('m-content') ? $('m-content').value : '';
        if (LINK_ALLOW) {
            $('f_link').value = $('m-link-url') ? $('m-link-url').value : '';
            $('f_link_text').value = $('m-link-text') ? $('m-link-text').value : '';
        }
    }
}

// 모바일 입력 → hidden 동기화
if (isMobile) {
    var mTag = $('m-tagline');
    var mCon = $('m-content');
    if (mTag) mTag.addEventListener('input', function() { $('f_tagline').value = this.value; updatePreview(); });
    if (mCon) mCon.addEventListener('input', function() { $('f_content').value = this.value; updatePreview(); });
    if (LINK_ALLOW) {
        var mLu = $('m-link-url');
        var mLt = $('m-link-text');
        if (mLu) mLu.addEventListener('input', function() { $('f_link').value = this.value; updatePreview(); });
        if (mLt) mLt.addEventListener('input', function() { $('f_link_text').value = this.value; updatePreview(); });
    }

    // 모바일 이미지 업로드
    var mFile = $('m-image-file');
    if (mFile) mFile.addEventListener('change', function() { uploadSealImage(this, 'm-'); });
    var mRem = $('m-image-remove');
    if (mRem) mRem.addEventListener('click', function() { removeSealImage('m-'); });
}

// ============================
// 텍스트 색상 팔레트
// ============================
document.querySelectorAll('.seal-color-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var hex = this.dataset.color;
        $('f_text_color').value = hex;
        document.querySelectorAll('.seal-color-btn').forEach(function(b) { b.classList.remove('ring-2','ring-mg-accent'); });
        this.classList.add('ring-2','ring-mg-accent');
        updatePreview();
    });
});

// ============================
// 이미지 업로드/삭제
// ============================
function uploadSealImage(input, prefix) {
    prefix = prefix || 'pp-';
    if (!input.files || !input.files[0]) return;
    var fd = new FormData();
    fd.append('seal_image', input.files[0]);
    fetch(SEAL_IMAGE_URL, {method:'POST', body:fd})
        .then(function(r){return r.json();})
        .then(function(d){
            if(d.success){
                // 양쪽 프리뷰 갱신
                var ppImg = $('pp-image-img');
                var ppPrev = $('pp-image-preview');
                if (ppImg) ppImg.src = d.url;
                if (ppPrev) ppPrev.classList.remove('hidden');
                var mImg = $('m-image-img');
                var mPrev = $('m-image-preview');
                if (mImg) mImg.src = d.url;
                if (mPrev) mPrev.classList.remove('hidden');
                SD.seal_image = d.url;
                updatePreview();
                refreshWidget('image');
            } else alert(d.message||'업로드 실패');
        }).catch(function(){alert('업로드 중 오류가 발생했습니다.');});
    input.value = '';
}

function removeSealImage(prefix) {
    prefix = prefix || 'pp-';
    var fd = new FormData();
    fd.append('remove_image','1');
    fetch(SEAL_IMAGE_URL, {method:'POST', body:fd})
        .then(function(r){return r.json();})
        .then(function(d){
            if(d.success){
                var ppImg = $('pp-image-img');
                var ppPrev = $('pp-image-preview');
                if (ppImg) ppImg.src = '';
                if (ppPrev) ppPrev.classList.add('hidden');
                var mImg = $('m-image-img');
                var mPrev = $('m-image-preview');
                if (mImg) mImg.src = '';
                if (mPrev) mPrev.classList.add('hidden');
                SD.seal_image = '';
                updatePreview();
                refreshWidget('image');
            }
        });
}

// PC 속성 패널 내 이미지 이벤트
var ppFile = $('pp-image-file');
if (ppFile) ppFile.addEventListener('change', function() { uploadSealImage(this, 'pp-'); });
var ppRem = $('pp-image-remove');
if (ppRem) ppRem.addEventListener('click', function() { removeSealImage('pp-'); });

// ============================
// 속성 편집 패널
// ============================
var STYLEABLE_TYPES = ['tagline', 'text', 'trophy'];
var ALIGNABLE_TYPES = ['tagline', 'text', 'nickname', 'link'];
var TYPE_LABELS = {
    character: '캐릭터', nickname: '닉네임', tagline: '한마디',
    text: '텍스트', image: '이미지', link: '링크', trophy: '트로피'
};

function showPropPanel(widgetId) {
    _selectedWidget = widgetId;
    var panel = $('seal-prop-panel');
    if (!panel) return;

    var type = widgetId.split('-')[0];

    // 모든 서브패널 숨김
    ['pp-tagline','pp-text','pp-image','pp-link','pp-trophy','pp-info','pp-align','pp-style'].forEach(function(id) {
        var el = $(id);
        if (el) el.classList.add('hidden');
    });

    // 패널 이름
    var label = TYPE_LABELS[type] || widgetId;
    if (type === 'trophy') {
        var slot = widgetId.split('-')[1] || '1';
        label = '트로피 ' + slot;
    }
    $('pp-name').textContent = label;

    // 타입별 서브패널 표시 + 값 채우기
    switch (type) {
        case 'tagline':
            $('pp-tagline').classList.remove('hidden');
            $('pp-tagline-input').value = $('f_tagline').value;
            $('pp-tagline-count').textContent = $('f_tagline').value.length;
            $('pp-style').classList.remove('hidden');
            $('pp-align').classList.remove('hidden');
            break;
        case 'text':
            $('pp-text').classList.remove('hidden');
            $('pp-text-input').value = $('f_content').value;
            $('pp-text-count').textContent = $('f_content').value.length;
            $('pp-style').classList.remove('hidden');
            $('pp-align').classList.remove('hidden');
            break;
        case 'image':
            $('pp-image').classList.remove('hidden');
            if (SD.seal_image) {
                $('pp-image-img').src = SD.seal_image;
                $('pp-image-preview').classList.remove('hidden');
            } else {
                $('pp-image-preview').classList.add('hidden');
            }
            break;
        case 'link':
            if ($('pp-link')) {
                $('pp-link').classList.remove('hidden');
                $('pp-link-url').value = $('f_link') ? $('f_link').value : '';
                $('pp-link-text').value = $('f_link_text') ? $('f_link_text').value : '';
            }
            $('pp-align').classList.remove('hidden');
            break;
        case 'trophy':
            $('pp-trophy').classList.remove('hidden');
            $('pp-style').classList.remove('hidden');
            break;
        case 'character':
            $('pp-info').classList.remove('hidden');
            $('pp-info-text').textContent = '대표 캐릭터는 상단에서 변경할 수 있습니다.';
            break;
        case 'nickname':
            $('pp-info').classList.remove('hidden');
            $('pp-info-text').textContent = '닉네임은 회원 정보에서 변경할 수 있습니다.';
            $('pp-align').classList.remove('hidden');
            break;
    }

    // 스타일 값 채우기
    var es = _elementStyles[widgetId] || {};
    if (STYLEABLE_TYPES.indexOf(type) >= 0) {
        $('sp-color').value = es.color || '#ffffff';
        $('sp-bgcolor').value = es.bgColor || '#2b2d31';
        $('sp-bordercolor').value = es.borderColor || '#3f4147';
        $('sp-color-check').checked = !!es.color;
        $('sp-bgcolor-check').checked = !!es.bgColor;
        $('sp-bordercolor-check').checked = !!es.borderColor;
    }

    // 정렬 값 채우기
    if (ALIGNABLE_TYPES.indexOf(type) >= 0) {
        var curAlign = es.align || 'center';
        document.querySelectorAll('.sp-align-btn').forEach(function(b) {
            var active = b.dataset.align === curAlign;
            b.classList.toggle('bg-mg-accent', active);
            b.classList.toggle('text-white', active);
            b.classList.toggle('border-mg-accent', active);
        });
    }

    panel.classList.remove('hidden');

    // 그리드 위젯 선택 하이라이트
    document.querySelectorAll('#seal-grid .grid-stack-item').forEach(function(el) {
        el.classList.toggle('gs-selected', el.gridstackNode && el.gridstackNode.id === widgetId);
    });
}

function hidePropPanel() {
    _selectedWidget = null;
    var panel = $('seal-prop-panel');
    if (panel) panel.classList.add('hidden');
    document.querySelectorAll('#seal-grid .grid-stack-item').forEach(function(el) {
        el.classList.remove('gs-selected');
    });
}

// 닫기 버튼
$('pp-close').addEventListener('click', hidePropPanel);

// 속성 패널 내 입력 이벤트 → hidden 동기화 + 미리보기 갱신
var ppTagInput = $('pp-tagline-input');
if (ppTagInput) {
    ppTagInput.addEventListener('input', function() {
        $('f_tagline').value = this.value;
        $('pp-tagline-count').textContent = this.value.length;
        refreshWidget('tagline');
        updatePreview();
    });
}

var ppTextInput = $('pp-text-input');
if (ppTextInput) {
    ppTextInput.addEventListener('input', function() {
        $('f_content').value = this.value;
        $('pp-text-count').textContent = this.value.length;
        refreshWidget('text');
        updatePreview();
    });
}

var ppLinkUrl = $('pp-link-url');
var ppLinkText = $('pp-link-text');
if (ppLinkUrl) {
    ppLinkUrl.addEventListener('input', function() {
        if ($('f_link')) $('f_link').value = this.value;
        refreshWidget('link');
        updatePreview();
    });
}
if (ppLinkText) {
    ppLinkText.addEventListener('input', function() {
        if ($('f_link_text')) $('f_link_text').value = this.value;
        refreshWidget('link');
        updatePreview();
    });
}

// 정렬 버튼 클릭
document.querySelectorAll('.sp-align-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (!_selectedWidget) return;
        var align = this.dataset.align;
        document.querySelectorAll('.sp-align-btn').forEach(function(b) {
            var active = b.dataset.align === align;
            b.classList.toggle('bg-mg-accent', active);
            b.classList.toggle('text-white', active);
            b.classList.toggle('border-mg-accent', active);
        });
        if (!_elementStyles[_selectedWidget]) _elementStyles[_selectedWidget] = {};
        if (align === 'center') {
            delete _elementStyles[_selectedWidget].align;
        } else {
            _elementStyles[_selectedWidget].align = align;
        }
        // 빈 객체면 삭제
        if (Object.keys(_elementStyles[_selectedWidget]).length === 0) delete _elementStyles[_selectedWidget];
        updatePreview();
    });
});

// 스타일 체크박스/색상 변경
['sp-color-check','sp-bgcolor-check','sp-bordercolor-check'].forEach(function(id) {
    var el = $(id);
    if (el) el.addEventListener('change', applyStyleFromPanel);
});
['sp-color','sp-bgcolor','sp-bordercolor'].forEach(function(id) {
    var el = $(id);
    if (el) el.addEventListener('input', function() {
        var checkId = id + '-check';
        if ($(checkId) && $(checkId).checked) applyStyleFromPanel();
    });
});

function applyStyleFromPanel() {
    if (!_selectedWidget) return;
    var existing = _elementStyles[_selectedWidget] || {};
    var es = {};
    // 기존 align 보존
    if (existing.align) es.align = existing.align;
    if ($('sp-color-check').checked) es.color = $('sp-color').value;
    if ($('sp-bgcolor-check').checked) es.bgColor = $('sp-bgcolor').value;
    if ($('sp-bordercolor-check').checked) es.borderColor = $('sp-bordercolor').value;
    if (Object.keys(es).length) _elementStyles[_selectedWidget] = es;
    else delete _elementStyles[_selectedWidget];
    updatePreview();
}

// ============================
// GridStack Init — PC에서 즉시 실행
// ============================
function calcCellHeight() {
    var container = $('seal-grid');
    if (!container) return 50;
    return Math.floor(container.clientWidth / GRID_COLS);
}

function initGrid() {
    if (gridReady) return;
    gridReady = true;

    var cellH = calcCellHeight();

    grid = GridStack.init({
        column: GRID_COLS,
        minRow: GRID_ROWS,
        maxRow: GRID_ROWS,
        cellHeight: cellH,
        float: true,
        animate: true,
        margin: 2,
        disableOneColumnMode: true
    }, '#seal-grid');

    var gridEl = $('seal-grid');
    gridEl.style.setProperty('--gs-cell-height', cellH + 'px');

    var resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (grid) {
                var newH = calcCellHeight();
                grid.cellHeight(newH);
                gridEl.style.setProperty('--gs-cell-height', newH + 'px');
            }
        }, 200);
    });

    var layout = (SAVED_LAYOUT && SAVED_LAYOUT.elements) ? SAVED_LAYOUT.elements : DEFAULT_LAYOUT;
    layout.forEach(function(el) { addToGrid(el); });

    grid.on('change', function() { updatePreview(); });
    updatePalette();
    updatePreview();

    // 위젯 클릭 → 속성 패널 열기
    gridEl.addEventListener('click', function(e) {
        if (e.target.closest('.seal-el-del')) return;
        var item = e.target.closest('.grid-stack-item');
        if (!item || !item.gridstackNode) { hidePropPanel(); return; }
        showPropPanel(item.gridstackNode.id);
    });
}

// PC에서 즉시 초기화 (CDN 로딩 실패 방지)
if (!isMobile) {
    try { initGrid(); } catch(e) { console.warn('GridStack 초기화 실패:', e); }
}

// ============================
// Grid widget operations
// ============================
function addToGrid(el) {
    var def = DEFS[el.type];
    if (!def) return;

    var gid = def.unique ? el.type : el.type + '-' + (el.slot || 1);

    if (def.unique) {
        var exists = grid.getGridItems().some(function(item) {
            return item.gridstackNode && item.gridstackNode.id === gid;
        });
        if (exists) return;
    }

    var opts = {
        id: gid,
        w: el.w || def.w, h: el.h || def.h,
        minW: def.minW, minH: def.minH,
        maxW: def.maxW, maxH: def.maxH
    };
    if (el.autoPos) {
        opts.autoPosition = true;
    } else {
        opts.x = el.x || 0;
        opts.y = el.y || 0;
    }
    grid.addWidget(opts);

    var items = grid.getGridItems();
    for (var i = 0; i < items.length; i++) {
        var node = items[i].gridstackNode;
        if (node && node.id === gid) {
            var contentEl = items[i].querySelector('.grid-stack-item-content');
            if (contentEl) contentEl.innerHTML = widgetHtml(el);
            break;
        }
    }
}

function widgetHtml(el) {
    var def = DEFS[el.type];
    var body = '';

    switch(el.type) {
        case 'character':
            body = SD.char_thumb
                ? '<img src="'+escHtml(SD.char_thumb)+'" style="width:100%;height:100%;object-fit:cover;border-radius:4px;">'
                : '<span style="font-size:20px;opacity:0.4;">&#128100;</span>';
            break;
        case 'nickname':
            body = '<span style="font-size:11px;font-weight:600;color:var(--mg-text-primary);">'+escHtml(SD.mb_nick)+'</span>';
            break;
        case 'tagline':
            var tl = $('f_tagline').value || SD.tagline;
            body = '<span style="font-size:10px;font-style:italic;">"'+ escHtml(tl || '한마디...')+'"</span>';
            break;
        case 'text':
            var ct = $('f_content').value || SD.content;
            body = '<span style="font-size:9px;line-height:1.3;word-break:break-all;">'+escHtml((ct||'텍스트...').substring(0,80))+'</span>';
            break;
        case 'image':
            body = SD.seal_image
                ? '<img src="'+escHtml(SD.seal_image)+'" style="width:100%;height:100%;object-fit:contain;border-radius:3px;">'
                : '<span style="font-size:18px;opacity:0.4;">&#128444;</span>';
            break;
        case 'link':
            var lt = $('f_link_text') ? $('f_link_text').value : SD.link_text;
            body = '<span style="font-size:10px;color:var(--mg-accent);">&#128279; '+escHtml(lt||'링크')+'</span>';
            break;
        case 'trophy':
            var slot = el.slot || 1;
            var tr = SD.trophies[slot-1];
            if (tr && tr.icon) {
                body = '<img src="'+escHtml(tr.icon)+'" style="width:24px;height:24px;object-fit:contain;">';
            } else {
                body = '<span style="font-size:16px;">&#127942;</span>';
            }
            break;
    }

    return '<div class="seal-el">'
        + '<span class="seal-el-label">'+def.label+(el.slot ? el.slot : '')+'</span>'
        + '<div class="seal-el-body">'+body+'</div>'
        + '<button type="button" class="seal-el-del" onclick="window._sealRemove(this)">&times;</button>'
        + '</div>';
}

// 위젯 내용 새로고침 (편집 시)
function refreshWidget(type) {
    if (!grid) return;
    var items = grid.getGridItems();
    for (var i = 0; i < items.length; i++) {
        var node = items[i].gridstackNode;
        if (!node) continue;
        var wType = node.id.split('-')[0];
        if (wType === type) {
            var contentEl = items[i].querySelector('.grid-stack-item-content');
            var elObj = {type: type};
            if (type === 'trophy') elObj.slot = parseInt(node.id.split('-')[1]) || 1;
            if (contentEl) contentEl.innerHTML = widgetHtml(elObj);
        }
    }
}

// ============================
// Element remove
// ============================
window._sealRemove = function(btn) {
    var item = btn.closest('.grid-stack-item');
    if (item && grid) {
        var wid = item.gridstackNode ? item.gridstackNode.id : null;
        grid.removeWidget(item);
        if (wid && _selectedWidget === wid) hidePropPanel();
        updatePalette();
        updatePreview();
    }
};

// ============================
// Element palette
// ============================
document.querySelectorAll('#seal-palette .seal-pal-btn[data-type]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        if (this.classList.contains('is-off')) return;
        var type = this.dataset.type;
        var slot = this.dataset.slot ? parseInt(this.dataset.slot) : 0;
        var el = {type: type, autoPos: true};
        if (slot) el.slot = slot;
        addToGrid(el);
        updatePalette();
        updatePreview();
    });
});

var resetBtn = $('btn-reset-layout');
if (resetBtn) {
    resetBtn.addEventListener('click', function() {
        if (!confirm('기본 레이아웃으로 되돌리시겠습니까? 현재 배치가 초기화됩니다.')) return;
        grid.removeAll();
        _elementStyles = {};
        hidePropPanel();
        DEFAULT_LAYOUT.forEach(function(el) { addToGrid(el); });
        updatePalette();
        updatePreview();
    });
}

function updatePalette() {
    if (!grid) return;
    var placed = {};
    grid.getGridItems().forEach(function(item) {
        var n = item.gridstackNode;
        if (!n) return;
        placed[n.id] = true;
    });

    document.querySelectorAll('#seal-palette .seal-pal-btn[data-type]').forEach(function(btn) {
        var type = btn.dataset.type;
        var def = DEFS[type];
        if (!def) return;

        if (def.unique) {
            btn.classList.toggle('is-off', !!placed[type]);
        } else if (type === 'trophy') {
            var slot = btn.dataset.slot;
            btn.classList.toggle('is-off', !!placed['trophy-'+slot]);
        }
    });
}

// ============================
// Preview (CSS Grid)
// ============================
function updatePreview() {
    var el = $('seal-preview');
    var items = grid ? getLayout() : (SAVED_LAYOUT && SAVED_LAYOUT.elements ? SAVED_LAYOUT.elements : []);
    if (!items.length) {
        el.innerHTML = '<p style="text-align:center;padding:20px;color:var(--mg-text-muted);font-size:14px;">요소를 배치해주세요</p>';
        return;
    }

    var tc = $('f_text_color').value;
    var textStyle = tc ? 'color:'+tc+';' : '';

    var html = '<div class="mg-seal mg-seal-grid" style="display:grid;grid-template-columns:repeat('+GRID_COLS+',1fr);grid-template-rows:repeat('+GRID_ROWS+',1fr);aspect-ratio:'+GRID_COLS+'/'+GRID_ROWS+';'+BG_STYLE+BORDER_STYLE+textStyle+'border-radius:12px;overflow:hidden;padding:6px;gap:3px;">';
    items.forEach(function(it) {
        var gc = (it.x+1)+'/span '+it.w;
        var gr = (it.y+1)+'/span '+it.h;
        var gid = (it.type === 'trophy') ? 'trophy-'+(it.slot||1) : it.type;
        var es = it.style || _elementStyles[gid] || {};
        var cellExtra = '';
        // 텍스트 계열 요소에 패딩 추가
        var textPad = {nickname:1, tagline:1, text:1, link:1};
        if (textPad[it.type]) cellExtra += 'padding:2px 6px;';
        if (es.bgColor) cellExtra += 'background:'+es.bgColor+';';
        if (es.borderColor) cellExtra += 'border:1px solid '+es.borderColor+';border-radius:6px;';
        var jc = 'center';
        if (es.align === 'left') jc = 'flex-start';
        else if (es.align === 'right') jc = 'flex-end';
        var ta = es.align || 'center';
        html += '<div style="grid-column:'+gc+';grid-row:'+gr+';overflow:hidden;display:flex;align-items:center;justify-content:'+jc+';text-align:'+ta+';min-width:0;'+cellExtra+'">';
        html += previewContent(it, es);
        html += '</div>';
    });
    html += '</div>';
    el.innerHTML = html;
}

function previewContent(it, es) {
    var tc = $('f_text_color').value;
    var elColor = (es && es.color) ? es.color : '';

    switch(it.type) {
        case 'character':
            var thumb = SD.char_thumb;
            return thumb
                ? '<div style="width:100%;height:100%;border-radius:8px;overflow:hidden;display:flex;align-items:center;justify-content:center;background:var(--mg-bg-tertiary,#313338);"><img src="'+escHtml(thumb)+'" style="width:100%;height:100%;object-fit:cover;"></div>'
                : '<div style="width:100%;height:100%;border-radius:8px;display:flex;align-items:center;justify-content:center;background:var(--mg-bg-tertiary,#313338);"><svg style="width:24px;height:24px;color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>';
        case 'nickname':
            return '<span style="font-weight:600;font-size:14px;color:var(--mg-text-primary,#f2f3f5);">'+escHtml(SD.mb_nick)+'</span>';
        case 'tagline':
            var tl = $('f_tagline').value || SD.tagline;
            var tagColor = elColor || tc || 'var(--mg-text-secondary,#b5bac1)';
            return tl ? '<span style="font-size:12px;color:'+tagColor+';">&ldquo;'+escHtml(tl)+'&rdquo;</span>' : '';
        case 'text':
            var ct = $('f_content').value || SD.content;
            var txtColor = elColor || tc || 'var(--mg-text-muted,#949ba4)';
            return ct ? '<span style="font-size:11px;line-height:1.5;color:'+txtColor+';word-break:break-all;">'+escHtml(ct).replace(/\n/g,'<br>')+'</span>' : '';
        case 'image':
            return SD.seal_image
                ? '<img src="'+escHtml(SD.seal_image)+'" style="max-width:100%;max-height:100%;object-fit:contain;border-radius:4px;">' : '';
        case 'link':
            var lu = $('f_link') ? $('f_link').value : SD.link;
            var lt = $('f_link_text') ? $('f_link_text').value : SD.link_text;
            return lu ? '<span style="font-size:11px;color:var(--mg-accent,#f59f0a);display:inline-flex;align-items:center;gap:3px;">&#128279; '+escHtml(lt||lu)+'</span>' : '';
        case 'trophy':
            var slot = it.slot || 1;
            var tr = SD.trophies[slot-1];
            if (!tr) return '';
            var rc = RARITY_COLORS[tr.rarity||'common']||'#949ba4';
            var icon = tr.icon
                ? '<img src="'+escHtml(tr.icon)+'" style="width:22px;height:22px;object-fit:contain;">'
                : '<span style="font-size:14px;">&#127942;</span>';
            return '<div style="display:flex;flex-direction:column;align-items:center;border:1.5px solid '+rc+';border-radius:6px;padding:2px;width:100%;height:100%;justify-content:center;">'
                +icon+'<span style="font-size:8px;color:'+rc+';max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+escHtml((tr.name||'').substring(0,10))+'</span></div>';
    }
    return '';
}

function getLayout() {
    if (!grid) return [];
    var items = [];
    grid.getGridItems().forEach(function(el) {
        var n = el.gridstackNode;
        if (!n) return;
        var parts = n.id.split('-');
        var type = parts[0];
        var item = {type:type, x:n.x, y:n.y, w:n.w, h:n.h};
        if (type === 'trophy') item.slot = parseInt(parts[1]) || 1;
        var gid = n.id;
        if (_elementStyles[gid]) item.style = _elementStyles[gid];
        items.push(item);
    });
    return items;
}

// ============================
// 초기 미리보기
// ============================
updatePreview();

// ============================
// Form submit
// ============================
$('seal-form').addEventListener('submit', function(e) {
    e.preventDefault();

    // 모바일 입력값 동기화
    syncToHidden();

    // 레이아웃 JSON 생성
    var layoutJson;
    if (grid) {
        layoutJson = JSON.stringify({elements: getLayout()});
    } else {
        var fallback = (SAVED_LAYOUT && SAVED_LAYOUT.elements) ? SAVED_LAYOUT : {elements: DEFAULT_LAYOUT};
        layoutJson = JSON.stringify(fallback);
    }
    $('f_seal_layout').value = layoutJson;

    var fd = new FormData(this);
    // FormData가 hidden input을 놓칠 수 있으므로 명시적으로 설정
    fd.set('seal_layout', layoutJson);
    fd.set('seal_use', $('f_seal_use').checked ? '1' : '0');

    fetch(SEAL_UPDATE_URL, {method:'POST', body:fd})
        .then(function(r){return r.json();})
        .then(function(d){
            if(d.success) location.reload();
            else alert(d.message||'저장 실패');
        }).catch(function(){alert('저장 중 오류가 발생했습니다.');});
});

})();
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
