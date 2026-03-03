<?php
/**
 * Morgan Super Admin — 초기 계정 생성 (1회용)
 *
 * super_admins 테이블이 비어있을 때만 동작.
 * 초기 슈퍼 관리자 계정을 생성하고 비밀번호를 화면에 출력한다.
 * 생성 후 이 파일을 삭제할 것을 권고한다.
 */

$g5_path['path'] = realpath(__DIR__ . '/../../');
include_once($g5_path['path'] . '/config.php');

$_master_cfg = G5_DATA_PATH . '/dbconfig_master.php';
if (!file_exists($_master_cfg)) {
    die('dbconfig_master.php 없음');
}
include_once($_master_cfg);

$link = @mysqli_connect(MG_MASTER_DB_HOST, MG_MASTER_DB_USER, MG_MASTER_DB_PASS, MG_MASTER_DB_NAME);
if (!$link) {
    die('마스터 DB 연결 실패: ' . mysqli_connect_error());
}
mysqli_set_charset($link, 'utf8mb4');

// 마스터 스키마 자동 부트스트랩 — 테이블이 없으면 생성
$_master_sql = G5_PATH . '/db/migrations/20260301_220000_master_schema.sql';
if ($_master_sql && file_exists($_master_sql)) {
    $check = mysqli_query($link, "SHOW TABLES LIKE 'super_admins'");
    if (!$check || mysqli_num_rows($check) === 0) {
        $sql_content = file_get_contents($_master_sql);
        if ($sql_content) {
            mysqli_multi_query($link, $sql_content);
            // 모든 결과 세트 소비
            do {
                if ($r = mysqli_store_result($link)) mysqli_free_result($r);
            } while (mysqli_more_results($link) && mysqli_next_result($link));
        }
    }
}
unset($_master_sql);

// 이미 계정 존재 확인
$result = mysqli_query($link, "SELECT COUNT(*) AS cnt FROM super_admins");
if (!$result) {
    die('super_admins 테이블 조회 실패: ' . mysqli_error($link));
}
$row = mysqli_fetch_assoc($result);

if ((int)$row['cnt'] > 0) {
    mysqli_close($link);
    ?>
    <!DOCTYPE html>
    <html lang="ko"><head><meta charset="UTF-8"><title>Bootstrap</title>
    <style>body{background:#1e1f22;color:#f2f3f5;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0}.box{text-align:center;max-width:500px;padding:2rem}h1{color:#f59f0a}p{color:#949ba4;margin-top:1rem}a{color:#f59f0a}</style>
    </head><body><div class="box">
        <h1>Already Initialized</h1>
        <p>슈퍼 관리자 계정이 이미 존재합니다.<br>보안을 위해 이 파일을 삭제해 주세요.</p>
        <p><a href="./login.php">로그인 페이지로 이동</a></p>
    </div></body></html>
    <?php
    exit;
}

$created = false;
$gen_password = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? 'admin');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username) {
        $error = '아이디를 입력해 주세요.';
    } elseif (strlen($password) < 6) {
        $error = '비밀번호는 6자 이상이어야 합니다.';
    } else {
        if (!$password) {
            $password = bin2hex(random_bytes(8));
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $esc_user = mysqli_real_escape_string($link, $username);
        $esc_email = mysqli_real_escape_string($link, $email);
        $esc_hash = mysqli_real_escape_string($link, $hash);

        $sql = "INSERT INTO super_admins (username, password_hash, email, is_active, created_at)
                VALUES ('{$esc_user}', '{$esc_hash}', '{$esc_email}', 1, NOW())";

        if (mysqli_query($link, $sql)) {
            $created = true;
            $gen_password = $password;
        } else {
            $error = 'DB 오류: ' . mysqli_error($link);
        }
    }
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bootstrap | Morgan Super Admin</title>
    <style>
        body{background:#1e1f22;color:#f2f3f5;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
        .box{background:#2b2d31;border:1px solid #313338;border-radius:0.75rem;padding:2.5rem 2rem;width:100%;max-width:480px;margin:1rem}
        h1{color:#f59f0a;font-size:1.5rem;margin-bottom:0.5rem}
        p{color:#949ba4;font-size:0.875rem;margin-bottom:1.5rem}
        .fg{margin-bottom:1rem}
        label{display:block;margin-bottom:0.5rem;font-size:0.875rem;color:#b5bac1}
        input{width:100%;padding:0.625rem 0.875rem;background:#1e1f22;border:1px solid #313338;border-radius:0.375rem;color:#f2f3f5;font-size:0.875rem}
        input:focus{outline:none;border-color:#f59f0a}
        .btn{width:100%;padding:0.75rem;background:#f59f0a;color:#fff;border:none;border-radius:0.375rem;font-size:0.9375rem;font-weight:600;cursor:pointer;margin-top:0.5rem}
        .btn:hover{background:#d97706}
        .result{background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#22c55e;padding:1.25rem;border-radius:0.375rem;margin-bottom:1rem}
        .result strong{color:#f2f3f5;font-size:1.125rem}
        .error{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#ef4444;padding:0.75rem;border-radius:0.375rem;margin-bottom:1rem;font-size:0.875rem}
        .warn{background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);color:#f59e0b;padding:0.75rem;border-radius:0.375rem;margin-top:1rem;font-size:0.8125rem}
        a{color:#f59f0a}
    </style>
</head>
<body>
<div class="box">
    <?php if ($created): ?>
        <h1>계정 생성 완료</h1>
        <div class="result">
            <p style="color:#22c55e;margin-bottom:0.5rem">슈퍼 관리자 계정이 생성되었습니다.</p>
            <p style="margin-bottom:0.25rem">아이디: <strong><?php echo htmlspecialchars($_POST['username'] ?? 'admin'); ?></strong></p>
            <p style="margin-bottom:0">비밀번호: <strong><?php echo htmlspecialchars($gen_password); ?></strong></p>
        </div>
        <div class="warn">
            이 비밀번호를 안전한 곳에 기록해 주세요.<br>
            보안을 위해 <code>bootstrap.php</code> 파일을 삭제해 주세요.
        </div>
        <p style="margin-top:1.5rem"><a href="./login.php">로그인 페이지로 이동 &rarr;</a></p>
    <?php else: ?>
        <h1>초기 설정</h1>
        <p>첫 번째 슈퍼 관리자 계정을 생성합니다.</p>

        <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="fg">
                <label for="username">아이디</label>
                <input type="text" id="username" name="username" value="admin" required>
            </div>
            <div class="fg">
                <label for="email">이메일 (선택)</label>
                <input type="email" id="email" name="email" placeholder="admin@example.com">
            </div>
            <div class="fg">
                <label for="password">비밀번호</label>
                <input type="password" id="password" name="password" placeholder="6자 이상" required minlength="6">
            </div>
            <button type="submit" class="btn">계정 생성</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
