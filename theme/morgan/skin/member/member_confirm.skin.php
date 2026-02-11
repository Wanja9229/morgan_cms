<?php
/**
 * Morgan Edition - Member Confirm Skin
 *
 * 회원정보 수정 전 비밀번호 확인
 */

if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-2 text-center">본인 확인</h2>
        <p class="text-sm text-mg-text-muted mb-6 text-center">
            회원정보를 수정하려면 비밀번호를 입력해주세요.
        </p>

        <form name="fmember_confirm" action="<?php echo $confirm_action_url; ?>" method="post" onsubmit="return fmember_confirm_submit(this);">
            <input type="hidden" name="url" value="<?php echo $url; ?>">

            <!-- 비밀번호 -->
            <div class="mb-6">
                <label for="mb_password" class="block text-sm font-medium text-mg-text-secondary mb-2">비밀번호</label>
                <input type="password"
                       name="mb_password"
                       id="mb_password"
                       class="input"
                       placeholder="현재 비밀번호를 입력하세요"
                       required>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                확인
            </button>
        </form>
    </div>
</div>

<script>
function fmember_confirm_submit(f) {
    if (!f.mb_password.value.trim()) {
        alert('비밀번호를 입력해주세요.');
        f.mb_password.focus();
        return false;
    }
    return true;
}
</script>
