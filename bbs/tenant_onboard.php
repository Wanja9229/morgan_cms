<?php
/**
 * Morgan Onboarding Wizard — 셀프서비스 테넌트 생성
 *
 * bare domain에서 접근 가능. common.php 미로드 상태.
 * tenant_bootstrap.php에서 라우팅.
 */

if (!defined('_GNUBOARD_')) {
    // 직접 접근 시 config.php 로드
    $g5_path = [];
    $g5_path['path'] = realpath(__DIR__ . '/../');
    if ($g5_path['path'] && file_exists($g5_path['path'] . '/config.php')) {
        include_once($g5_path['path'] . '/config.php');
    } else {
        die('Config not found');
    }
}

// POST 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'check_subdomain')) {
    require_once(G5_PLUGIN_PATH . '/morgan/tenant/onboard_process.php');
    exit;
}

// CSRF 토큰 생성
session_name('MG_ONBOARD');
session_start();
if (empty($_SESSION['ob_csrf_token'])) {
    $_SESSION['ob_csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['ob_csrf_token'];

$base_domain = defined('MG_TENANT_BASE_DOMAIN') ? MG_TENANT_BASE_DOMAIN : 'example.com';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>커뮤니티 만들기 — Morgan CMS</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            background: #1e1f22;
            color: #f2f3f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            min-height: 100vh;
        }

        /* Header */
        .ob-header {
            text-align: center;
            padding: 2rem 1rem 1rem;
        }
        .ob-header h1 {
            font-size: 1.5rem;
            color: #f59f0a;
            margin: 0 0 0.25rem;
        }
        .ob-header p {
            color: #949ba4;
            font-size: 0.9rem;
            margin: 0;
        }

        /* Container */
        .ob-container {
            max-width: 520px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* Progress */
        .ob-progress {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
        .ob-step {
            width: 2.5rem;
            height: 4px;
            border-radius: 2px;
            background: #313338;
            transition: background 0.3s;
        }
        .ob-step.active { background: #f59f0a; }
        .ob-step.done { background: #22c55e; }

        /* Form */
        .ob-panel {
            background: #2b2d31;
            border-radius: 12px;
            padding: 1.5rem;
            display: none;
        }
        .ob-panel.active { display: block; }
        .ob-panel h2 {
            font-size: 1.1rem;
            margin: 0 0 1rem;
            color: #f2f3f5;
        }

        .ob-field {
            margin-bottom: 1rem;
        }
        .ob-field label {
            display: block;
            font-size: 0.8rem;
            color: #b5bac1;
            margin-bottom: 0.25rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .ob-field input {
            width: 100%;
            padding: 0.6rem 0.75rem;
            background: #1e1f22;
            border: 1px solid #313338;
            border-radius: 6px;
            color: #f2f3f5;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.2s;
        }
        .ob-field input:focus {
            border-color: #f59f0a;
        }
        .ob-field input.error {
            border-color: #ef4444;
        }
        .ob-field input.success {
            border-color: #22c55e;
        }
        .ob-field .hint {
            font-size: 0.75rem;
            color: #949ba4;
            margin-top: 0.25rem;
        }
        .ob-field .hint.error { color: #ef4444; }
        .ob-field .hint.success { color: #22c55e; }

        /* Subdomain preview */
        .ob-subdomain-preview {
            display: flex;
            align-items: center;
            gap: 0;
            background: #1e1f22;
            border: 1px solid #313338;
            border-radius: 6px;
            overflow: hidden;
        }
        .ob-subdomain-preview input {
            border: none;
            border-radius: 0;
            flex: 1;
            text-align: right;
            padding-right: 0.25rem;
        }
        .ob-subdomain-preview .ob-domain-suffix {
            color: #949ba4;
            font-size: 0.9rem;
            padding: 0.6rem 0.75rem 0.6rem 0.25rem;
            white-space: nowrap;
        }

        /* Buttons */
        .ob-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        .ob-btn {
            flex: 1;
            padding: 0.65rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, opacity 0.2s;
        }
        .ob-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .ob-btn-primary {
            background: #f59f0a;
            color: #1e1f22;
        }
        .ob-btn-primary:hover:not(:disabled) {
            background: #d97706;
        }
        .ob-btn-secondary {
            background: #313338;
            color: #f2f3f5;
        }
        .ob-btn-secondary:hover:not(:disabled) {
            background: #3f4147;
        }

        /* Loading */
        .ob-loading {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .ob-loading.active { display: block; }
        .ob-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #313338;
            border-top-color: #f59f0a;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Result */
        .ob-result {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        .ob-result.active { display: block; }
        .ob-result .ob-check {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #22c55e;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }
        .ob-result h2 {
            color: #f2f3f5;
            margin: 0 0 0.5rem;
        }
        .ob-result p {
            color: #949ba4;
            margin: 0.25rem 0;
        }
        .ob-result .ob-url {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.6rem 1.5rem;
            background: #f59f0a;
            color: #1e1f22;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .ob-result .ob-url:hover { background: #d97706; }

        /* Error banner */
        .ob-error {
            display: none;
            background: #3c1420;
            border: 1px solid #ef4444;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
            color: #fca5a5;
            font-size: 0.85rem;
            white-space: pre-line;
        }
        .ob-error.active { display: block; }

        /* Honeypot */
        .ob-hp { position: absolute; left: -9999px; }

        /* Back link */
        .ob-back {
            text-align: center;
            margin-top: 1.5rem;
        }
        .ob-back a {
            color: #949ba4;
            font-size: 0.85rem;
            text-decoration: none;
        }
        .ob-back a:hover { color: #f2f3f5; }

        /* Plan cards */
        .ob-plan-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        .ob-plan-card {
            padding: 1rem;
            background: #1e1f22;
            border: 2px solid #313338;
            border-radius: 8px;
            cursor: pointer;
            transition: border-color 0.2s;
        }
        .ob-plan-card.selected {
            border-color: #f59f0a;
        }
        .ob-plan-card h3 {
            margin: 0 0 0.25rem;
            font-size: 1rem;
        }
        .ob-plan-card p {
            margin: 0;
            color: #949ba4;
            font-size: 0.85rem;
        }
        .ob-plan-card .ob-plan-price {
            color: #f59f0a;
            font-weight: 700;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>

<div class="ob-header">
    <h1>Morgan CMS</h1>
    <p>나만의 커뮤니티를 만들어 보세요</p>
</div>

<div class="ob-container">
    <div class="ob-progress">
        <div class="ob-step active" data-step="1"></div>
        <div class="ob-step" data-step="2"></div>
        <div class="ob-step" data-step="3"></div>
    </div>

    <div id="ob-error" class="ob-error"></div>

    <!-- Step 1: 커뮤니티 정보 -->
    <div class="ob-panel active" id="step1">
        <h2>커뮤니티 정보</h2>
        <div class="ob-field">
            <label>서브도메인</label>
            <div class="ob-subdomain-preview">
                <input type="text" id="subdomain" placeholder="my-community" maxlength="63" autocomplete="off">
                <span class="ob-domain-suffix">.<?php echo htmlspecialchars($base_domain); ?></span>
            </div>
            <div class="hint" id="subdomain-hint">영소문자, 숫자, 하이픈만 사용 가능 (2~63자)</div>
        </div>
        <div class="ob-field">
            <label>커뮤니티 이름</label>
            <input type="text" id="name" placeholder="나의 커뮤니티" maxlength="100">
        </div>
        <div class="ob-actions">
            <button class="ob-btn ob-btn-primary" onclick="goStep(2)" id="btn-next1">다음</button>
        </div>
    </div>

    <!-- Step 2: 관리자 정보 -->
    <div class="ob-panel" id="step2">
        <h2>관리자 계정</h2>
        <div class="ob-field">
            <label>관리자 ID</label>
            <input type="text" id="admin_id" placeholder="admin" maxlength="20" autocomplete="off">
            <div class="hint">영소문자로 시작, 3~20자 (영문/숫자/밑줄)</div>
        </div>
        <div class="ob-field">
            <label>이메일</label>
            <input type="email" id="admin_email" placeholder="admin@example.com">
        </div>
        <div class="ob-field">
            <label>비밀번호</label>
            <input type="password" id="admin_pass" placeholder="8자 이상" minlength="8">
        </div>
        <div class="ob-field">
            <label>비밀번호 확인</label>
            <input type="password" id="admin_pass2" placeholder="비밀번호 재입력">
        </div>
        <div class="ob-actions">
            <button class="ob-btn ob-btn-secondary" onclick="goStep(1)">이전</button>
            <button class="ob-btn ob-btn-primary" onclick="goStep(3)">다음</button>
        </div>
    </div>

    <!-- Step 3: 확인 -->
    <div class="ob-panel" id="step3">
        <h2>확인</h2>

        <div class="ob-plan-cards" style="margin-bottom:1rem;">
            <div class="ob-plan-card selected" onclick="selectPlan(this, 'free')">
                <h3>Free</h3>
                <p class="ob-plan-price">무료</p>
                <p>저장용량 1GB, 회원 100명</p>
            </div>
        </div>

        <div style="background:#1e1f22;border-radius:8px;padding:1rem;margin-bottom:1rem;">
            <table style="width:100%;font-size:0.9rem;">
                <tr><td style="color:#949ba4;padding:0.25rem 0;">서브도메인</td><td id="confirm-subdomain" style="text-align:right;"></td></tr>
                <tr><td style="color:#949ba4;padding:0.25rem 0;">이름</td><td id="confirm-name" style="text-align:right;"></td></tr>
                <tr><td style="color:#949ba4;padding:0.25rem 0;">관리자 ID</td><td id="confirm-admin" style="text-align:right;"></td></tr>
                <tr><td style="color:#949ba4;padding:0.25rem 0;">이메일</td><td id="confirm-email" style="text-align:right;"></td></tr>
            </table>
        </div>

        <!-- Honeypot -->
        <div class="ob-hp">
            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="ob-actions">
            <button class="ob-btn ob-btn-secondary" onclick="goStep(2)">이전</button>
            <button class="ob-btn ob-btn-primary" onclick="submitOnboard()" id="btn-submit">커뮤니티 생성</button>
        </div>
    </div>

    <!-- Loading -->
    <div class="ob-loading" id="loading">
        <div class="ob-spinner"></div>
        <p style="color:#949ba4;">커뮤니티를 생성하고 있습니다...</p>
        <p style="color:#949ba4;font-size:0.8rem;">최대 30초 정도 소요될 수 있습니다.</p>
    </div>

    <!-- Result -->
    <div class="ob-result" id="result">
        <div class="ob-check">&#10003;</div>
        <h2>커뮤니티가 생성되었습니다!</h2>
        <p id="result-msg"></p>
        <a href="#" id="result-url" class="ob-url">커뮤니티로 이동</a>
    </div>

    <div class="ob-back">
        <a href="/">&#8592; 메인으로 돌아가기</a>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?php echo $csrf_token; ?>';
const BASE_DOMAIN = '<?php echo htmlspecialchars($base_domain, ENT_QUOTES); ?>';
let currentStep = 1;
let subdomainTimer = null;
let subdomainValid = false;

// Subdomain 실시간 체크
document.getElementById('subdomain').addEventListener('input', function() {
    const val = this.value.toLowerCase().replace(/[^a-z0-9-]/g, '');
    this.value = val;
    subdomainValid = false;
    const hint = document.getElementById('subdomain-hint');

    if (val.length < 2) {
        hint.textContent = '2자 이상 입력해주세요.';
        hint.className = 'hint';
        this.className = '';
        return;
    }

    hint.textContent = '확인 중...';
    hint.className = 'hint';

    clearTimeout(subdomainTimer);
    subdomainTimer = setTimeout(() => {
        fetch('?action=check_subdomain&subdomain=' + encodeURIComponent(val))
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    hint.textContent = val + '.' + BASE_DOMAIN + ' — ' + data.message;
                    hint.className = 'hint success';
                    document.getElementById('subdomain').className = 'success';
                    subdomainValid = true;
                } else {
                    hint.textContent = data.message;
                    hint.className = 'hint error';
                    document.getElementById('subdomain').className = 'error';
                    subdomainValid = false;
                }
            })
            .catch(() => {
                hint.textContent = '확인 중 오류 발생';
                hint.className = 'hint error';
            });
    }, 400);
});

function goStep(step) {
    const err = document.getElementById('ob-error');
    err.className = 'ob-error';
    err.textContent = '';

    // Validation
    if (step > currentStep) {
        if (currentStep === 1) {
            if (!subdomainValid) {
                showError('사용 가능한 서브도메인을 입력해주세요.');
                return;
            }
            if (document.getElementById('name').value.trim().length < 2) {
                showError('커뮤니티 이름을 2자 이상 입력해주세요.');
                return;
            }
        }
        if (currentStep === 2) {
            const id = document.getElementById('admin_id').value.trim();
            const email = document.getElementById('admin_email').value.trim();
            const pass = document.getElementById('admin_pass').value;
            const pass2 = document.getElementById('admin_pass2').value;

            if (!/^[a-z][a-z0-9_]{2,19}$/.test(id)) {
                showError('관리자 ID: 영소문자로 시작, 3~20자(영문/숫자/밑줄)');
                return;
            }
            if (!email || !email.includes('@')) {
                showError('올바른 이메일을 입력해주세요.');
                return;
            }
            if (pass.length < 8) {
                showError('비밀번호는 8자 이상이어야 합니다.');
                return;
            }
            if (pass !== pass2) {
                showError('비밀번호가 일치하지 않습니다.');
                return;
            }
        }
    }

    // Update confirmation panel
    if (step === 3) {
        document.getElementById('confirm-subdomain').textContent =
            document.getElementById('subdomain').value + '.' + BASE_DOMAIN;
        document.getElementById('confirm-name').textContent =
            document.getElementById('name').value;
        document.getElementById('confirm-admin').textContent =
            document.getElementById('admin_id').value;
        document.getElementById('confirm-email').textContent =
            document.getElementById('admin_email').value;
    }

    // Switch panels
    currentStep = step;
    document.querySelectorAll('.ob-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('step' + step).classList.add('active');

    // Update progress
    document.querySelectorAll('.ob-step').forEach(s => {
        const n = parseInt(s.dataset.step);
        s.className = 'ob-step';
        if (n < step) s.classList.add('done');
        if (n === step) s.classList.add('active');
    });
}

function selectPlan(el, plan) {
    document.querySelectorAll('.ob-plan-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}

function showError(msg) {
    const err = document.getElementById('ob-error');
    err.textContent = msg;
    err.className = 'ob-error active';
}

function submitOnboard() {
    const btn = document.getElementById('btn-submit');
    btn.disabled = true;

    // Hide all panels, show loading
    document.querySelectorAll('.ob-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('loading').classList.add('active');
    document.getElementById('ob-error').className = 'ob-error';

    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('_token', CSRF_TOKEN);
    formData.append('subdomain', document.getElementById('subdomain').value.trim());
    formData.append('name', document.getElementById('name').value.trim());
    formData.append('admin_id', document.getElementById('admin_id').value.trim());
    formData.append('admin_email', document.getElementById('admin_email').value.trim());
    formData.append('admin_pass', document.getElementById('admin_pass').value);
    formData.append('website', document.getElementById('website').value); // honeypot

    fetch(location.pathname, {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading').classList.remove('active');

        if (data.success) {
            document.getElementById('result-msg').textContent = data.admin_id + ' 계정으로 로그인하세요.';
            document.getElementById('result-url').href = data.tenant_url;
            document.getElementById('result-url').textContent = data.tenant_url + ' 으로 이동';
            document.getElementById('result').classList.add('active');
            document.querySelector('.ob-progress').style.display = 'none';
        } else {
            showError(data.error || '알 수 없는 오류가 발생했습니다.');
            document.getElementById('step3').classList.add('active');
            btn.disabled = false;
        }
    })
    .catch(err => {
        document.getElementById('loading').classList.remove('active');
        showError('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
        document.getElementById('step3').classList.add('active');
        btn.disabled = false;
    });
}
</script>

</body>
</html>
