<?php
/**
 * Morgan Edition - 판 세우기 (역극 생성) 페이지
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) { alert_close('로그인이 필요합니다.'); }

// Can create check
$can_create = mg_can_create_rp($member['mb_id']);
if (!$can_create['can_create']) { alert_close($can_create['message']); }

$my_characters = mg_get_usable_characters($member['mb_id']);
if (empty($my_characters)) { alert_close('승인된 캐릭터가 없습니다. 캐릭터를 먼저 등록해주세요.'); }

$max_member_default = (int)mg_config('rp_max_member_default', 0);
$max_member_limit = (int)mg_config('rp_max_member_limit', 20);

$g5['title'] = '판 세우기 - 역극';
include_once(G5_THEME_PATH.'/head.php');
?>

<div id="rp_write" class="max-w-4xl mx-auto">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6">판 세우기</h2>

        <form name="frp_write" id="frp_write" action="<?php echo G5_BBS_URL; ?>/rp_write_update.php" method="post" enctype="multipart/form-data" onsubmit="return frp_write_submit(this);" autocomplete="off">
            <input type="hidden" name="token" value="<?php echo isset($_SESSION['ss_token']) ? $_SESSION['ss_token'] : ''; ?>">

            <!-- 캐릭터 선택 -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">캐릭터 선택 <span class="text-mg-error">*</span></label>
                <div class="flex flex-wrap gap-2" id="mg-character-selector">
                    <?php
                    $default_ch_id = 0;
                    foreach ($my_characters as $ch) {
                        if ($ch['ch_main']) { $default_ch_id = $ch['ch_id']; break; }
                    }
                    if (!$default_ch_id && count($my_characters) > 0) {
                        $default_ch_id = $my_characters[0]['ch_id'];
                    }
                    ?>
                    <?php foreach ($my_characters as $ch) { ?>
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="ch_id" value="<?php echo $ch['ch_id']; ?>" <?php echo $default_ch_id == $ch['ch_id'] ? 'checked' : ''; ?> class="hidden">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent transition-colors character-badge">
                            <?php if ($ch['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                            <?php } else { ?>
                            <div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center">
                                <span class="text-xs font-bold text-mg-accent"><?php echo mb_substr($ch['ch_name'], 0, 1); ?></span>
                            </div>
                            <?php } ?>
                            <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($ch['ch_name']); ?></span>
                            <?php if ($ch['ch_main']) { ?>
                            <span class="text-xs bg-mg-accent text-white px-1.5 py-0.5 rounded">대표</span>
                            <?php } ?>
                        </div>
                    </label>
                    <?php } ?>
                </div>
                <p class="text-xs text-mg-text-muted mt-1">이 역극에서 사용할 캐릭터를 선택하세요.</p>
            </div>

            <!-- 제목 -->
            <div class="mb-4">
                <label for="rt_title" class="block text-sm font-medium text-mg-text-secondary mb-2">제목 <span class="text-mg-error">*</span></label>
                <input type="text" name="rt_title" id="rt_title" class="input" maxlength="200" required placeholder="역극 제목을 입력하세요">
            </div>

            <!-- 내용 -->
            <div class="mb-4">
                <label for="rt_content" class="block text-sm font-medium text-mg-text-secondary mb-2">내용 <span class="text-mg-error">*</span></label>
                <textarea name="rt_content" id="rt_content" rows="10" class="input resize-y" required placeholder="역극의 시작 내용을 작성하세요. 배경 설정, 상황 묘사 등을 포함해주세요."></textarea>
            </div>

            <!-- 이미지 업로드 -->
            <div class="mb-4">
                <label for="rt_image" class="block text-sm font-medium text-mg-text-secondary mb-2">대표 이미지 <span class="text-mg-text-muted font-normal">(선택)</span></label>
                <input type="file" name="rt_image" id="rt_image" accept="image/jpeg,image/png,image/gif,image/webp" class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-mg-bg-tertiary file:text-mg-text-primary hover:file:bg-mg-accent/20">
                <p class="text-xs text-mg-text-muted mt-1">JPG, PNG, GIF, WebP 형식 (선택사항)</p>
            </div>

            <!-- 최대 참여자 수 -->
            <div class="mb-6">
                <label for="rt_max_member" class="block text-sm font-medium text-mg-text-secondary mb-2">최대 참여자 수</label>
                <input type="number" name="rt_max_member" id="rt_max_member" value="<?php echo $max_member_default; ?>" min="0" max="<?php echo $max_member_limit; ?>" class="input w-32" placeholder="0">
                <p class="text-xs text-mg-text-muted mt-1">0 = 제한 없음 (최대 <?php echo $max_member_limit; ?>명)</p>
            </div>

            <!-- 버튼 -->
            <div class="flex items-center justify-between">
                <a href="<?php echo G5_BBS_URL; ?>/rp_list.php" class="btn btn-secondary">취소</a>
                <button type="submit" id="btn_submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    판 세우기
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function frp_write_submit(f) {
    if (!f.rt_title.value.trim()) {
        alert('제목을 입력해주세요.');
        f.rt_title.focus();
        return false;
    }
    if (!f.rt_content.value.trim()) {
        alert('내용을 입력해주세요.');
        f.rt_content.focus();
        return false;
    }
    var ch_selected = f.querySelector('input[name="ch_id"]:checked');
    if (!ch_selected) {
        alert('캐릭터를 선택해주세요.');
        return false;
    }
    return true;
}

// 캐릭터 선택기 UI 업데이트
document.querySelectorAll('.character-option input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.character-badge').forEach(function(badge) {
            badge.classList.remove('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
            badge.classList.add('border-mg-bg-tertiary');
        });
        if (this.checked) {
            var badge = this.parentElement.querySelector('.character-badge');
            badge.classList.remove('border-mg-bg-tertiary');
            badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
        }
    });
    if (radio.checked) {
        var badge = radio.parentElement.querySelector('.character-badge');
        badge.classList.remove('border-mg-bg-tertiary');
        badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
    }
});
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
