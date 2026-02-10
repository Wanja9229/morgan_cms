<?php
/**
 * Morgan Edition - 인장 편집
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

if (!mg_config('seal_enable', 1)) {
    alert('인장 시스템이 비활성화되어 있습니다.');
}

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
    );
}

// 대표 캐릭터
$main_char = mg_get_main_character($mb_id);

// 활성 칭호
$title_items = mg_get_active_items($mb_id, 'title');
$active_title = !empty($title_items) ? $title_items[0] : null;

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

$g5['title'] = '내 인장 편집';
include_once(G5_THEME_PATH.'/head.php');

$seal_image_url = '';
if (!empty($seal['seal_image'])) {
    if (strpos($seal['seal_image'], 'http') === 0) {
        $seal_image_url = $seal['seal_image'];
    } else {
        $seal_image_url = MG_SEAL_IMAGE_URL . '/' . $seal['seal_image'];
    }
}
?>

<div class="max-w-3xl mx-auto">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>뒤로</span>
    </a>

    <h1 class="text-2xl font-bold text-mg-text-primary mb-6">내 인장 편집</h1>

    <!-- 편집 폼 -->
    <form id="seal-form" class="space-y-6">

        <!-- 인장 사용 토글 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
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

        <!-- 기본 정보 (자동) -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">기본 정보 (자동)</h2>
            </div>
            <div class="p-4 space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-lg bg-mg-bg-tertiary overflow-hidden flex items-center justify-center">
                        <?php if ($main_char && !empty($main_char['ch_thumb'])) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$main_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                        <?php } else { ?>
                        <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <?php } ?>
                    </div>
                    <div>
                        <p class="text-sm text-mg-text-primary font-medium"><?php echo htmlspecialchars($member['mb_nick']); ?></p>
                        <?php if ($active_title) {
                            $te = is_string($active_title['si_effect']) ? json_decode($active_title['si_effect'], true) : $active_title['si_effect'];
                        ?>
                        <p class="text-xs" style="<?php echo !empty($te['color']) ? 'color:'.$te['color'] : ''; ?>"><?php echo htmlspecialchars($te['text'] ?? $active_title['si_name']); ?></p>
                        <?php } ?>
                    </div>
                </div>
                <p class="text-xs text-mg-text-muted">대표 캐릭터는 <a href="<?php echo G5_BBS_URL; ?>/character.php" class="text-mg-accent hover:underline">캐릭터 관리</a>에서, 칭호는 <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="text-mg-accent hover:underline">인벤토리</a>에서 변경할 수 있습니다.</p>
            </div>
        </div>

        <!-- 한마디 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">한마디</h2>
            </div>
            <div class="p-4">
                <input type="text" name="seal_tagline" id="f_tagline" value="<?php echo htmlspecialchars($seal['seal_tagline']); ?>"
                       maxlength="<?php echo $tagline_max; ?>"
                       placeholder="나를 표현하는 한마디..."
                       class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                <p class="text-xs text-mg-text-muted mt-1 text-right"><span id="tagline-count"><?php echo mb_strlen($seal['seal_tagline']); ?></span>/<?php echo $tagline_max; ?>자</p>
            </div>
        </div>

        <!-- 자유 영역 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">자유 영역</h2>
            </div>
            <div class="p-4 space-y-4">
                <div>
                    <textarea name="seal_content" id="f_content" rows="4" maxlength="<?php echo $content_max; ?>"
                              placeholder="자유롭게 소개를 작성해보세요..."
                              class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none resize-none"><?php echo htmlspecialchars($seal['seal_content']); ?></textarea>
                    <p class="text-xs text-mg-text-muted mt-1 text-right"><span id="content-count"><?php echo mb_strlen($seal['seal_content']); ?></span>/<?php echo $content_max; ?>자</p>
                </div>

                <?php if ($image_upload || $image_url_allow) { ?>
                <!-- 이미지 -->
                <div>
                    <label class="text-xs font-medium text-mg-text-secondary mb-2 block">이미지 (최대 600x200, <?php echo mg_config('seal_image_max_size', 500); ?>KB)</label>
                    <div id="seal-image-preview" class="<?php echo $seal_image_url ? '' : 'hidden'; ?> mb-2">
                        <div class="relative inline-block">
                            <img id="seal-image-img" src="<?php echo htmlspecialchars($seal_image_url); ?>" alt="" class="max-w-full max-h-[100px] rounded border border-mg-bg-tertiary">
                            <button type="button" onclick="removeSealImage()" class="absolute -top-2 -right-2 w-5 h-5 bg-mg-error text-white rounded-full text-xs flex items-center justify-center">&times;</button>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <?php if ($image_upload) { ?>
                        <label class="inline-flex items-center gap-1 px-3 py-1.5 bg-mg-bg-tertiary hover:bg-mg-bg-primary text-mg-text-secondary text-xs rounded-lg cursor-pointer transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            파일 선택
                            <input type="file" accept="image/*" onchange="uploadSealImage(this)" class="hidden">
                        </label>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <?php if ($link_allow) { ?>
                <!-- 링크 -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-medium text-mg-text-secondary mb-1 block">링크 URL</label>
                        <input type="url" name="seal_link" id="f_link" value="<?php echo htmlspecialchars($seal['seal_link']); ?>"
                               placeholder="https://..."
                               class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-medium text-mg-text-secondary mb-1 block">링크 텍스트</label>
                        <input type="text" name="seal_link_text" id="f_link_text" value="<?php echo htmlspecialchars($seal['seal_link_text']); ?>"
                               placeholder="트위터, 블로그 등..."
                               maxlength="50"
                               class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary placeholder:text-mg-text-muted focus:border-mg-accent focus:outline-none">
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- 텍스트 색상 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">텍스트 색상</h2>
            </div>
            <div class="p-4">
                <div class="flex items-center gap-2 flex-wrap">
                    <?php
                    $colors = array('' => '기본', '#ffffff' => '흰색', '#b5bac1' => '회색', '#da7756' => '주황', '#a855f7' => '보라', '#60a5fa' => '하늘', '#4ade80' => '초록', '#facc15' => '노랑', '#fb7185' => '핑크');
                    foreach ($colors as $hex => $name) {
                        $active = ($seal['seal_text_color'] ?? '') === $hex ? 'ring-2 ring-mg-accent' : '';
                    ?>
                    <button type="button" onclick="setTextColor('<?php echo $hex; ?>')"
                            class="w-7 h-7 rounded-full border border-mg-bg-tertiary flex items-center justify-center <?php echo $active; ?>"
                            style="<?php echo $hex ? 'background:'.$hex : 'background:#2b2d31;'; ?>"
                            title="<?php echo $name; ?>">
                        <?php if (!$hex) { ?><span class="text-[10px] text-mg-text-muted">A</span><?php } ?>
                    </button>
                    <?php } ?>
                    <input type="hidden" name="seal_text_color" id="f_text_color" value="<?php echo htmlspecialchars($seal['seal_text_color'] ?? ''); ?>">
                </div>
            </div>
        </div>

        <!-- 트로피 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h2 class="font-medium text-mg-text-primary">트로피 (<?php echo $trophy_slots; ?>슬롯)</h2>
                <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-xs text-mg-accent hover:underline">업적 페이지에서 관리</a>
            </div>
            <div class="p-4">
                <?php if (!empty($trophy_display)) { ?>
                <div class="flex gap-2 flex-wrap">
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
                <?php } else { ?>
                <p class="text-xs text-mg-text-muted">업적을 달성하면 여기에 표시됩니다.</p>
                <?php } ?>
            </div>
        </div>

        <!-- 미리보기 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">미리보기</h2>
            </div>
            <div class="p-4">
                <div id="seal-preview">
                    <?php echo mg_render_seal($mb_id, 'full'); ?>
                    <?php if (!mg_get_seal($mb_id)) { ?>
                    <p class="text-sm text-mg-text-muted text-center py-4">저장 후 미리보기가 표시됩니다.</p>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- 저장 버튼 -->
        <div class="flex justify-end gap-3">
            <button type="submit" class="px-6 py-2.5 bg-mg-accent hover:bg-mg-accent-hover text-white font-medium rounded-lg transition-colors">
                저장
            </button>
        </div>
    </form>
</div>

<script>
var SEAL_UPDATE_URL = '<?php echo G5_BBS_URL; ?>/seal_edit_update.php';
var SEAL_IMAGE_URL = '<?php echo G5_BBS_URL; ?>/seal_image_upload.php';

// 글자수 카운터
document.getElementById('f_tagline').addEventListener('input', function() {
    document.getElementById('tagline-count').textContent = this.value.length;
});
document.getElementById('f_content').addEventListener('input', function() {
    document.getElementById('content-count').textContent = this.value.length;
});

// 텍스트 색상 선택
function setTextColor(hex) {
    document.getElementById('f_text_color').value = hex;
    document.querySelectorAll('[onclick^="setTextColor"]').forEach(function(btn) {
        btn.classList.remove('ring-2', 'ring-mg-accent');
    });
    event.target.closest('button').classList.add('ring-2', 'ring-mg-accent');
}

// 이미지 업로드
function uploadSealImage(input) {
    if (!input.files || !input.files[0]) return;
    var fd = new FormData();
    fd.append('seal_image', input.files[0]);
    fetch(SEAL_IMAGE_URL, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('seal-image-img').src = data.url;
                document.getElementById('seal-image-preview').classList.remove('hidden');
            } else {
                alert(data.message || '업로드 실패');
            }
        })
        .catch(function() { alert('업로드 중 오류가 발생했습니다.'); });
    input.value = '';
}

// 이미지 삭제
function removeSealImage() {
    var fd = new FormData();
    fd.append('remove_image', '1');
    fetch(SEAL_IMAGE_URL, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('seal-image-img').src = '';
                document.getElementById('seal-image-preview').classList.add('hidden');
            }
        });
}

// 폼 저장
document.getElementById('seal-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);
    fd.set('seal_use', document.getElementById('f_seal_use').checked ? '1' : '0');

    fetch(SEAL_UPDATE_URL, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || '저장 실패');
            }
        })
        .catch(function() { alert('저장 중 오류가 발생했습니다.'); });
});
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
