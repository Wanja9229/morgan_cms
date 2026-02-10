<?php
/**
 * Morgan Edition - Postit Board Write Skin
 *
 * 포스트잇 게시판 전용 미니멀 작성 폼
 * 제목 없이 내용만 작성 (제목은 날짜 자동 생성)
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
$form_title = $is_edit ? '포스트잇 수정' : '새 포스트잇';

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

// 자동 제목 생성 (날짜 기반)
$auto_subject = $is_edit ? $subject : date('Y-m-d H:i') . ' 포스트잇';
?>

<div id="bo_write" class="max-w-lg mx-auto">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6 flex items-center gap-2">
            <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <?php echo $form_title; ?>
        </h2>

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

            <!-- 제목: 숨김 (자동 생성) -->
            <input type="hidden" name="wr_subject" id="wr_subject" value="<?php echo htmlspecialchars($auto_subject); ?>">

            <?php echo $html_editor_head_script; ?>

            <!-- 이름 (비회원) -->
            <?php if ($is_name) { ?>
            <div class="mb-4">
                <label for="wr_name" class="block text-sm font-medium text-mg-text-secondary mb-2">이름 <span class="text-mg-error">*</span></label>
                <input type="text" name="wr_name" id="wr_name" value="<?php echo $name; ?>" class="input" required placeholder="이름을 입력하세요">
            </div>
            <?php } ?>

            <!-- 비밀번호 (비회원) -->
            <?php if ($is_password) { ?>
            <div class="mb-4">
                <label for="wr_password" class="block text-sm font-medium text-mg-text-secondary mb-2">비밀번호 <span class="text-mg-error">*</span></label>
                <input type="password" name="wr_password" id="wr_password" class="input" <?php echo $is_edit ? '' : 'required'; ?> placeholder="비밀번호를 입력하세요">
            </div>
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

            <!-- 내용 -->
            <div class="mb-4">
                <label for="wr_content" class="block text-sm font-medium text-mg-text-secondary mb-2">내용 <span class="text-mg-error">*</span></label>
                <?php if ($html_editor) { ?>
                    <?php echo $html_editor; ?>
                <?php } else { ?>
                    <textarea name="wr_content" id="wr_content" rows="8" class="input w-full resize-y" placeholder="마음속 이야기를 적어주세요..." required><?php echo $content; ?></textarea>
                <?php } ?>
            </div>

            <!-- 옵션: 비밀글만 표시 -->
            <?php if ($is_secret) { ?>
            <div class="mb-6 p-4 bg-mg-bg-primary rounded-lg">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="secret" value="secret" <?php echo $secret_checked; ?> class="w-4 h-4 rounded">
                    <span class="text-sm text-mg-text-secondary">비밀글로 작성</span>
                    <span class="text-xs text-mg-text-muted">(작성자와 관리자만 볼 수 있습니다)</span>
                </label>
            </div>
            <?php } ?>

            <!-- 버튼 -->
            <div class="flex items-center justify-between">
                <a href="<?php echo $list_href; ?>" class="btn btn-secondary">취소</a>
                <button type="submit" id="btn_submit" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <?php echo $is_edit ? '수정하기' : '붙이기'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php echo $html_editor_tail_script; ?>

<script>
function fwrite_submit(f) {
    // 제목이 비어있으면 자동 생성
    if (!f.wr_subject.value.trim()) {
        var now = new Date();
        var y = now.getFullYear();
        var m = String(now.getMonth() + 1).padStart(2, '0');
        var d = String(now.getDate()).padStart(2, '0');
        var h = String(now.getHours()).padStart(2, '0');
        var mi = String(now.getMinutes()).padStart(2, '0');
        f.wr_subject.value = y + '-' + m + '-' + d + ' ' + h + ':' + mi + ' 포스트잇';
    }

    <?php echo $editor_js; ?>

    // 내용 검증 (에디터가 없는 경우)
    var content = f.wr_content ? f.wr_content.value : '';
    if (typeof get_editor_content === 'function') {
        content = get_editor_content('wr_content');
    }
    if (!content.replace(/<[^>]*>/g, '').trim()) {
        alert('내용을 입력해주세요.');
        if (f.wr_content) f.wr_content.focus();
        return false;
    }

    return true;
}
</script>
