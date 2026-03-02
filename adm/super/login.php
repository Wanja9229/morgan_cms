<?php
/**
 * Morgan Super Admin — 로그인
 */

$g5_path['path'] = realpath(__DIR__ . '/../../');
include_once($g5_path['path'] . '/config.php');

// 마스터 DB 설정 로드 (공통 진입점의 경량 버전)
$_master_cfg = G5_DATA_PATH . '/dbconfig_master.php';
if (!file_exists($_master_cfg)) {
    die('dbconfig_master.php 없음');
}
include_once($_master_cfg);

$_SA_LINK = @mysqli_connect(MG_MASTER_DB_HOST, MG_MASTER_DB_USER, MG_MASTER_DB_PASS, MG_MASTER_DB_NAME);
if (!$_SA_LINK) {
    die('마스터 DB 연결 실패');
}
mysqli_set_charset($_SA_LINK, 'utf8mb4');

define('G5_IS_SUPER_ADMIN', true);

session_name('MG_SUPER_ADMIN');
session_start();

// 이미 로그인 상태면 대시보드로
if (!empty($_SESSION['sa_id'])) {
    header('Location: ./index.php');
    exit;
}

$error = '';

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $esc_user = mysqli_real_escape_string($_SA_LINK, $username);
        $result = mysqli_query($_SA_LINK,
            "SELECT * FROM super_admins WHERE username = '{$esc_user}' AND is_active = 1 LIMIT 1"
        );

        if ($result && mysqli_num_rows($result) > 0) {
            $admin = mysqli_fetch_assoc($result);
            if (password_verify($password, $admin['password_hash'])) {
                // 로그인 성공
                $_SESSION['sa_id']       = (int)$admin['id'];
                $_SESSION['sa_username'] = $admin['username'];
                $_SESSION['sa_email']    = $admin['email'];
                session_regenerate_id(true);

                // last_login 갱신
                $ip = mysqli_real_escape_string($_SA_LINK, $_SERVER['REMOTE_ADDR'] ?? '');
                mysqli_query($_SA_LINK,
                    "UPDATE super_admins SET last_login_at = NOW(), last_login_ip = '{$ip}' WHERE id = " . (int)$admin['id']
                );

                header('Location: ./index.php');
                exit;
            }
        }

        // 로그인 실패 — brute force 방지
        sleep(2);
        $error = '아이디 또는 비밀번호가 올바르지 않습니다.';
    } else {
        $error = '아이디와 비밀번호를 입력해 주세요.';
    }
}

mysqli_close($_SA_LINK);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Morgan Super Admin</title>
    <style>
        :root {
            --mg-bg-primary: #1e1f22;
            --mg-bg-secondary: #2b2d31;
            --mg-bg-tertiary: #313338;
            --mg-text-primary: #f2f3f5;
            --mg-text-secondary: #b5bac1;
            --mg-text-muted: #949ba4;
            --mg-accent: #f59f0a;
            --mg-accent-hover: #d97706;
            --mg-error: #ef4444;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--mg-bg-primary);
            color: var(--mg-text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-box {
            background: var(--mg-bg-secondary);
            border: 1px solid var(--mg-bg-tertiary);
            border-radius: 0.75rem;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 400px;
            margin: 1rem;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-logo h1 {
            color: var(--mg-accent);
            font-size: 1.5rem;
        }

        .login-logo p {
            color: var(--mg-text-muted);
            font-size: 0.8125rem;
            margin-top: 0.25rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--mg-text-secondary);
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--mg-bg-primary);
            border: 1px solid var(--mg-bg-tertiary);
            border-radius: 0.375rem;
            color: var(--mg-text-primary);
            font-size: 0.9375rem;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--mg-accent);
        }

        .form-input::placeholder { color: var(--mg-text-muted); }

        .btn-login {
            width: 100%;
            padding: 0.75rem;
            background: var(--mg-accent);
            color: #fff;
            border: none;
            border-radius: 0.375rem;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-login:hover {
            background: var(--mg-accent-hover);
        }

        .error-msg {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--mg-error);
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.8125rem;
            margin-bottom: 1.25rem;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-logo">
            <h1>Morgan CMS</h1>
            <p>Super Admin Panel</p>
        </div>

        <?php if ($error): ?>
        <div class="error-msg"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label class="form-label" for="username">아이디</label>
                <input type="text" id="username" name="username" class="form-input"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                       placeholder="admin" autocomplete="username" autofocus required>
            </div>
            <div class="form-group">
                <label class="form-label" for="password">비밀번호</label>
                <input type="password" id="password" name="password" class="form-input"
                       placeholder="••••••••" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-login">로그인</button>
        </form>
    </div>
</body>
</html>
