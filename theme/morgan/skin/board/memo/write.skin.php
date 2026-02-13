<?php
/**
 * Morgan Edition - Memo Board Write Skin
 *
 * 간소화된 메모 작성 폼 (캐릭터 선택, 제목, 내용, 비밀글)
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 변수 기본값 설정
$w = isset($w) ? $w : '';
$wr_id = isset($wr_id) ? $wr_id : 0;
$sca = isset($sca) ? $sca : '';
$sfl = isset($sfl) ? $sfl : '';
$stx = isset($stx) ? $stx : '';
$spt = isset($spt) ? $spt : '';
$page = isset($page) ? $page : '';
$name = isset($name) ? $name : '';
$subject = isset($subject) ? $subject : '';
$secret_checked = isset($secret_checked) ? $secret_checked : '';
$html_editor_head_script = isset($html_editor_head_script) ? $html_editor_head_script : '';
$html_editor = isset($html_editor) ? $html_editor : '';
$html_editor_tail_script = isset($html_editor_tail_script) ? $html_editor_tail_script : '';
$editor_js = isset($editor_js) ? $editor_js : '';
$is_name = isset($is_name) ? $is_name : false;
$is_password = isset($is_password) ? $is_password : false;
$is_secret = isset($is_secret) ? $is_secret : false;
$action_url = isset($action_url) ? $action_url : '';
$list_href = isset($list_href) ? $list_href : '';

$is_edit = $w === 'u';
$form_title = $is_edit ? '메모 수정' : '메모 쓰기';

// 보상 유형 (request 모드)
$_mg_br_mode = '';
$_mg_reward_types = array();
if ($is_member && !$is_edit && function_exists('mg_get_board_reward')) {
    $_mg_br = mg_get_board_reward($bo_table);
    if ($_mg_br && $_mg_br['br_mode'] === 'request') {
        $_mg_br_mode = 'request';
        $_mg_reward_types = mg_get_reward_types($bo_table);
    }
}

// 캐릭터 선택기 준비 (로그인 회원만)
$mg_characters = array();
$mg_selected_ch_id = 0;

if ($is_member) {
    $mg_characters = mg_get_usable_characters($member['mb_id']);

    // 수정 시 기존 선택된 캐릭터
    if ($is_edit && $wr_id) {
        $mg_write_char = mg_get_write_character($bo_table, $wr_id);
        if ($mg_write_char) {
            $mg_selected_ch_id = $mg_write_char['ch_id'];
        }
    } else {
        // 신규 작성 시 대표 캐릭터 기본 선택
        foreach ($mg_characters as $ch) {
            if ($ch['ch_main']) {
                $mg_selected_ch_id = $ch['ch_id'];
                break;
            }
        }
    }
}
?>

<div id="bo_write" class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6"><?php echo $form_title; ?></h2>

        <form name="fwrite" id="fwrite" action="<?php echo $action_url; ?>" method="post" enctype="multipart/form-data" onsubmit="return fwrite_submit(this);" autocomplete="off">
            <input type="hidden" name="w" value="<?php echo $w; ?>">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
            <input type="hidden" name="sca" value="<?php echo $sca; ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
            <input type="hidden" name="stx" value="<?php echo $stx; ?>">
            <input type="hidden" name="spt" value="<?php echo $spt; ?>">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="token" value="">
            <?php echo $html_editor_head_script; ?>

            <!-- 이름 (비회원) -->
            <?php if ($is_name) { ?>
            <div class="mb-4">
                <label for="wr_name" class="block text-sm font-medium text-mg-text-secondary mb-2">이름 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_name" id="wr_name" value="<?php echo $name; ?>" class="input" required>
            </div>
            <?php } ?>

            <!-- 비밀번호 (비회원) -->
            <?php if ($is_password) { ?>
            <div class="mb-4">
                <label for="wr_password" class="block text-sm font-medium text-mg-text-secondary mb-2">비밀번호 <span class="text-mg-error">*</span></label>
                <input type="password" name="wr_password" id="wr_password" class="input" <?php echo $is_edit ? '' : 'required'; ?>>
            </div>
            <?php } ?>

            <!-- 캐릭터 선택 (회원 전용) -->
            <?php if ($is_member && count($mg_characters) > 0) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">캐릭터 선택</label>
                <div class="flex flex-wrap gap-2" id="mg-character-selector">
                    <!-- 캐릭터 없음 옵션 -->
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="mg_ch_id" value="0" <?php echo $mg_selected_ch_id == 0 ? 'checked' : ''; ?> class="hidden">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg border border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent transition-colors character-badge">
                            <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
                                <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-sm text-mg-text-secondary">선택 안함</span>
                        </div>
                    </label>
                    <?php foreach ($mg_characters as $ch) { ?>
                    <label class="character-option cursor-pointer">
                        <input type="radio" name="mg_ch_id" value="<?php echo $ch['ch_id']; ?>" <?php echo $mg_selected_ch_id == $ch['ch_id'] ? 'checked' : ''; ?> class="hidden">
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
                <p class="text-xs text-mg-text-muted mt-1">이 메모를 작성할 캐릭터를 선택하세요.</p>
            </div>
            <?php } elseif ($is_member) { ?>
            <input type="hidden" name="mg_ch_id" value="0">
            <?php } ?>

            <!-- 보상 유형 (request 모드) -->
            <?php if ($_mg_br_mode === 'request' && !empty($_mg_reward_types)) { ?>
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">보상 요청</label>
                <select name="reward_type" class="input">
                    <option value="">보상 요청 안 함</option>
                    <?php foreach ($_mg_reward_types as $rwt) { ?>
                    <option value="<?php echo $rwt['rwt_id']; ?>">
                        <?php echo htmlspecialchars($rwt['rwt_name']); ?> - <?php echo number_format($rwt['rwt_point']); ?>P
                        <?php if ($rwt['rwt_desc']) echo '(' . htmlspecialchars($rwt['rwt_desc']) . ')'; ?>
                    </option>
                    <?php } ?>
                </select>
                <p class="text-xs text-mg-text-muted mt-1">보상을 요청하면 관리자 검토 후 지급됩니다.</p>
            </div>
            <?php } ?>

            <!-- 제목 -->
            <div class="mb-4">
                <label for="wr_subject" class="block text-sm font-medium text-mg-text-secondary mb-2">제목 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_subject" id="wr_subject" value="<?php echo $subject; ?>" class="input" required>
            </div>

            <!-- 내용 -->
            <div class="mb-4">
                <label for="wr_content" class="block text-sm font-medium text-mg-text-secondary mb-2">내용 <span class="text-mg-error">*</span></label>
                <?php echo $html_editor; ?>
                <?php if ($is_member) {
                    $picker_id = 'write';
                    $picker_target = 'wr_content';
                    include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
                } ?>
            </div>

            <!-- 옵션 (비밀글만) -->
            <?php if ($is_secret) { ?>
            <div class="mb-6 p-4 bg-mg-bg-primary rounded-lg">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="secret" value="secret" <?php echo $secret_checked; ?> class="w-4 h-4 rounded">
                    <span class="text-sm text-mg-text-secondary">비밀글</span>
                </label>
            </div>
            <?php } ?>

            <!-- 버튼 -->
            <div class="flex items-center justify-between">
                <a href="<?php echo $list_href; ?>" class="btn btn-secondary">취소</a>
                <button type="submit" id="btn_submit" class="btn btn-primary">
                    <?php echo $is_edit ? '수정하기' : '작성하기'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $html_editor_tail_script; ?>

<script>
function fwrite_submit(f) {
    if (!f.wr_subject.value.trim()) {
        alert('제목을 입력해주세요.');
        f.wr_subject.focus();
        return false;
    }

    <?php echo $editor_js; ?>

    return true;
}

// 캐릭터 선택기 UI 업데이트
document.querySelectorAll('.character-option input[type="radio"]').forEach(function(radio) {
    radio.addEventListener('change', function() {
        // 모든 배지 기본 스타일로
        document.querySelectorAll('.character-badge').forEach(function(badge) {
            badge.classList.remove('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
            badge.classList.add('border-mg-bg-tertiary');
        });
        // 선택된 항목 강조
        if (this.checked) {
            var badge = this.parentElement.querySelector('.character-badge');
            badge.classList.remove('border-mg-bg-tertiary');
            badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
        }
    });
    // 초기 상태 설정
    if (radio.checked) {
        var badge = radio.parentElement.querySelector('.character-badge');
        badge.classList.remove('border-mg-bg-tertiary');
        badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
    }
});
</script>
