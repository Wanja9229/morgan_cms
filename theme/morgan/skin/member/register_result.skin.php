<?php
/**
 * Morgan Edition - Register Result Skin
 *
 * 회원가입 완료 화면
 */

if (!defined('_GNUBOARD_')) exit;
?>

<div class="max-w-md mx-auto">
    <div class="card text-center">
        <!-- 아이콘 -->
        <div class="w-20 h-20 rounded-full bg-mg-success/20 mx-auto mb-4 flex items-center justify-center">
            <svg class="w-10 h-10 text-mg-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <h2 class="text-xl font-bold text-mg-text-primary mb-2">가입을 환영합니다!</h2>
        <p class="text-mg-text-secondary mb-6">
            <strong class="text-mg-accent"><?php echo $mb_nick; ?></strong>님, 회원가입이 완료되었습니다.
        </p>

        <!-- 안내 메시지 -->
        <?php if ($config['cf_use_email_certify']) { ?>
        <div class="bg-mg-warning/10 border border-mg-warning/30 rounded-lg p-4 mb-6 text-left">
            <p class="text-sm text-mg-warning">
                <strong>이메일 인증이 필요합니다.</strong><br>
                가입 시 입력한 이메일로 인증 메일이 발송되었습니다.<br>
                메일 내 링크를 클릭하여 인증을 완료해주세요.
            </p>
        </div>
        <?php } ?>

        <!-- 버튼 -->
        <div class="flex flex-col gap-2">
            <a href="<?php echo G5_URL; ?>" class="btn btn-primary">
                메인으로
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/login.php" class="btn btn-secondary">
                로그인
            </a>
        </div>
    </div>
</div>
