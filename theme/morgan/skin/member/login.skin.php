<?php
/**
 * Morgan Edition - Login Skin
 */

if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6 text-center">로그인</h2>

        <form name="flogin" action="<?php echo $login_action_url; ?>" method="post" autocomplete="off">
            <input type="hidden" name="url" value="<?php echo $url; ?>">

            <!-- 아이디 -->
            <div class="mb-4">
                <label for="login_id" class="block text-sm font-medium text-mg-text-secondary mb-2">아이디</label>
                <input type="text"
                       name="mb_id"
                       id="login_id"
                       class="input"
                       placeholder="아이디를 입력하세요"
                       required>
            </div>

            <!-- 비밀번호 -->
            <div class="mb-4">
                <label for="login_pw" class="block text-sm font-medium text-mg-text-secondary mb-2">비밀번호</label>
                <input type="password"
                       name="mb_password"
                       id="login_pw"
                       class="input"
                       placeholder="비밀번호를 입력하세요"
                       required>
            </div>

            <!-- 자동 로그인 -->
            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="auto_login"
                           value="1"
                           class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent">
                    <span class="text-sm text-mg-text-secondary">자동 로그인</span>
                </label>
            </div>

            <!-- 로그인 버튼 -->
            <button type="submit" class="btn btn-primary w-full mb-4">
                로그인
            </button>

            <!-- 링크 -->
            <div class="flex items-center justify-center gap-4 text-sm">
                <a href="<?php echo G5_BBS_URL; ?>/register.php" class="text-mg-text-muted hover:text-mg-text-primary transition-colors">
                    회원가입
                </a>
                <span class="text-mg-bg-tertiary">|</span>
                <a href="<?php echo G5_BBS_URL; ?>/password_lost.php" class="text-mg-text-muted hover:text-mg-text-primary transition-colors">
                    비밀번호 찾기
                </a>
            </div>
        </form>
    </div>
</div>
