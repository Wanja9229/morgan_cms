<?php
/**
 * Morgan Edition - 회원가입 약관 스킨
 */

if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6 text-center">회원가입</h2>

        <form name="fregisterform" action="<?php echo $register_action_url; ?>" method="post">

            <!-- 이용약관 -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-mg-text-primary mb-3">이용약관</h3>
                <div class="bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg p-4 h-48 overflow-y-auto text-sm text-mg-text-secondary">
                    <?php echo $config['cf_stipulation']; ?>
                </div>
                <label class="flex items-center gap-2 mt-3 cursor-pointer">
                    <input type="checkbox" name="agree" value="1" id="agree1" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent">
                    <span class="text-sm text-mg-text-secondary">이용약관에 동의합니다.</span>
                </label>
            </div>

            <!-- 개인정보 처리방침 -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-mg-text-primary mb-3">개인정보 처리방침</h3>
                <div class="bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg p-4 h-48 overflow-y-auto text-sm text-mg-text-secondary">
                    <?php echo $config['cf_privacy']; ?>
                </div>
                <label class="flex items-center gap-2 mt-3 cursor-pointer">
                    <input type="checkbox" name="agree2" value="1" id="agree2" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent">
                    <span class="text-sm text-mg-text-secondary">개인정보 처리방침에 동의합니다.</span>
                </label>
            </div>

            <!-- 버튼 -->
            <div class="flex gap-3">
                <a href="<?php echo G5_URL; ?>" class="btn flex-1 text-center bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-bg-primary">
                    취소
                </a>
                <button type="submit" class="btn btn-primary flex-1" onclick="return fregisterform_check(this.form);">
                    회원가입
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function fregisterform_check(f) {
    if (!f.agree.checked) {
        alert("이용약관에 동의해 주세요.");
        f.agree.focus();
        return false;
    }
    if (!f.agree2.checked) {
        alert("개인정보 처리방침에 동의해 주세요.");
        f.agree2.focus();
        return false;
    }
    return true;
}
</script>
