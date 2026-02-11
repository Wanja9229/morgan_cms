<?php
/**
 * Morgan Edition - Register Form Skin
 *
 * 간소화된 회원가입 폼
 * 필수: 아이디, 비밀번호, 닉네임
 */

if (!defined('_GNUBOARD_')) exit;

// 수정 모드인지 체크
$is_update = $w === 'u';
$form_title = $is_update ? '회원정보 수정' : '회원가입';
$submit_text = $is_update ? '정보 수정' : '가입하기';
?>

<div class="mg-inner">
    <div class="card">
        <h2 class="text-xl font-bold text-mg-text-primary mb-6 text-center"><?php echo $form_title; ?></h2>

        <form name="fregister" action="<?php echo $register_action_url; ?>" method="post" autocomplete="off" onsubmit="return fregister_submit(this);">
            <input type="hidden" name="w" value="<?php echo $w; ?>">
            <input type="hidden" name="url" value="<?php echo $urlencode; ?>">
            <?php echo $captcha_html ?? ''; ?>

            <?php if (!$is_update) { ?>
            <!-- 아이디 (신규 가입시만) -->
            <div class="mb-4">
                <label for="reg_mb_id" class="block text-sm font-medium text-mg-text-secondary mb-2">
                    아이디 <span class="text-mg-error">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text"
                           name="mb_id"
                           id="reg_mb_id"
                           class="input flex-1"
                           maxlength="20"
                           placeholder="영문, 숫자 4~20자"
                           required>
                    <button type="button" onclick="check_mb_id();" class="btn btn-secondary whitespace-nowrap">
                        중복확인
                    </button>
                </div>
                <p id="msg_mb_id" class="text-xs mt-1"></p>
            </div>
            <?php } else { ?>
            <!-- 아이디 표시 (수정시) -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-mg-text-secondary mb-2">아이디</label>
                <p class="text-mg-text-primary"><?php echo $member['mb_id']; ?></p>
            </div>
            <?php } ?>

            <!-- 비밀번호 -->
            <div class="mb-4">
                <label for="reg_mb_password" class="block text-sm font-medium text-mg-text-secondary mb-2">
                    비밀번호 <?php echo $is_update ? '' : '<span class="text-mg-error">*</span>'; ?>
                </label>
                <input type="password"
                       name="mb_password"
                       id="reg_mb_password"
                       class="input"
                       minlength="4"
                       placeholder="<?php echo $is_update ? '변경시에만 입력' : '4자 이상'; ?>"
                       <?php echo $is_update ? '' : 'required'; ?>>
            </div>

            <!-- 비밀번호 확인 -->
            <div class="mb-4">
                <label for="reg_mb_password_re" class="block text-sm font-medium text-mg-text-secondary mb-2">
                    비밀번호 확인 <?php echo $is_update ? '' : '<span class="text-mg-error">*</span>'; ?>
                </label>
                <input type="password"
                       name="mb_password_re"
                       id="reg_mb_password_re"
                       class="input"
                       placeholder="비밀번호를 한번 더 입력"
                       <?php echo $is_update ? '' : 'required'; ?>>
            </div>

            <!-- 닉네임 -->
            <div class="mb-4">
                <label for="reg_mb_nick" class="block text-sm font-medium text-mg-text-secondary mb-2">
                    닉네임 <span class="text-mg-error">*</span>
                </label>
                <div class="flex gap-2">
                    <input type="text"
                           name="mb_nick"
                           id="reg_mb_nick"
                           class="input flex-1"
                           maxlength="20"
                           value="<?php echo $is_update ? get_text($member['mb_nick']) : ''; ?>"
                           placeholder="2~20자"
                           required>
                    <button type="button" onclick="check_mb_nick();" class="btn btn-secondary whitespace-nowrap">
                        중복확인
                    </button>
                </div>
                <p id="msg_mb_nick" class="text-xs mt-1"></p>
                <?php if ($is_update && $member['mb_nick_date'] && $member['mb_nick_date'] >= date('Y-m-d', strtotime('-'.$config['cf_nick_modify'].' day'))) { ?>
                <p class="text-xs text-mg-warning mt-1">닉네임 변경 후 <?php echo $config['cf_nick_modify']; ?>일이 지나지 않아 변경할 수 없습니다.</p>
                <?php } ?>
            </div>

            <!-- 이메일 (선택) -->
            <?php if (!$config['cf_use_email_certify']) { ?>
            <div class="mb-4">
                <label for="reg_mb_email" class="block text-sm font-medium text-mg-text-secondary mb-2">
                    이메일 <span class="text-mg-text-muted">(선택)</span>
                </label>
                <input type="email"
                       name="mb_email"
                       id="reg_mb_email"
                       class="input"
                       value="<?php echo $is_update ? $member['mb_email'] : ''; ?>"
                       placeholder="비밀번호 찾기에 사용됩니다">
            </div>
            <?php } ?>

            <?php if (!$is_update) { ?>
            <!-- 약관 동의 -->
            <div class="mb-6 p-4 bg-mg-bg-primary rounded-lg">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox"
                           name="agree"
                           id="agree"
                           value="1"
                           class="mt-1 w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent"
                           required>
                    <span class="text-sm text-mg-text-secondary">
                        <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=provision" target="_blank" class="text-mg-accent hover:underline">이용약관</a> 및
                        <a href="<?php echo G5_BBS_URL; ?>/content.php?co_id=privacy" target="_blank" class="text-mg-accent hover:underline">개인정보처리방침</a>에 동의합니다.
                    </span>
                </label>
            </div>
            <?php } ?>

            <!-- 제출 버튼 -->
            <button type="submit" id="btn_submit" class="btn btn-primary w-full">
                <?php echo $submit_text; ?>
            </button>

            <?php if (!$is_update) { ?>
            <p class="text-center text-sm text-mg-text-muted mt-4">
                이미 계정이 있으신가요?
                <a href="<?php echo G5_BBS_URL; ?>/login.php" class="text-mg-accent hover:underline">로그인</a>
            </p>
            <?php } ?>
        </form>
    </div>
</div>

<script>
var g5_check_mb_id = false;
var g5_check_mb_nick = false;

function check_mb_id() {
    var mb_id = document.getElementById('reg_mb_id').value.trim();
    if (!mb_id) {
        alert('아이디를 입력해주세요.');
        return;
    }

    var msgEl = document.getElementById('msg_mb_id');
    msgEl.textContent = '확인 중...';
    msgEl.className = 'text-xs mt-1 text-mg-text-muted';

    fetch('<?php echo G5_BBS_URL; ?>/ajax.mb_id.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reg_mb_id=' + encodeURIComponent(mb_id)
    })
    .then(res => res.text())
    .then(data => {
        data = data.trim();
        if (data === '') {
            msgEl.textContent = '사용 가능한 아이디입니다.';
            msgEl.className = 'text-xs mt-1 text-mg-success';
            g5_check_mb_id = true;
        } else {
            msgEl.textContent = data;
            msgEl.className = 'text-xs mt-1 text-mg-error';
            g5_check_mb_id = false;
        }
    });
}

function check_mb_nick() {
    var mb_nick = document.getElementById('reg_mb_nick').value.trim();
    if (!mb_nick) {
        alert('닉네임을 입력해주세요.');
        return;
    }

    var msgEl = document.getElementById('msg_mb_nick');
    msgEl.textContent = '확인 중...';
    msgEl.className = 'text-xs mt-1 text-mg-text-muted';

    var mb_id = document.getElementById('reg_mb_id') ? document.getElementById('reg_mb_id').value.trim() : '';

    fetch('<?php echo G5_BBS_URL; ?>/ajax.mb_nick.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'reg_mb_nick=' + encodeURIComponent(mb_nick) + '&reg_mb_id=' + encodeURIComponent(mb_id)
    })
    .then(res => res.text())
    .then(data => {
        data = data.trim();
        if (data === '') {
            msgEl.textContent = '사용 가능한 닉네임입니다.';
            msgEl.className = 'text-xs mt-1 text-mg-success';
            g5_check_mb_nick = true;
        } else {
            msgEl.textContent = data;
            msgEl.className = 'text-xs mt-1 text-mg-error';
            g5_check_mb_nick = false;
        }
    });
}

function fregister_submit(f) {
    <?php if (!$is_update) { ?>
    if (!g5_check_mb_id) {
        alert('아이디 중복확인을 해주세요.');
        return false;
    }
    <?php } ?>

    if (!g5_check_mb_nick) {
        // 수정 모드에서 닉네임을 변경하지 않은 경우 패스
        <?php if ($is_update) { ?>
        if (f.mb_nick.value !== '<?php echo addslashes($member['mb_nick']); ?>') {
            alert('닉네임 중복확인을 해주세요.');
            return false;
        }
        <?php } else { ?>
        alert('닉네임 중복확인을 해주세요.');
        return false;
        <?php } ?>
    }

    var pw = f.mb_password.value;
    var pw_re = f.mb_password_re.value;

    if (pw && pw !== pw_re) {
        alert('비밀번호가 일치하지 않습니다.');
        f.mb_password_re.focus();
        return false;
    }

    return true;
}
</script>
