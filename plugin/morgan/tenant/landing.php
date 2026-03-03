<?php
/**
 * Morgan CMS 랜딩 페이지
 *
 * bare domain 접근 시 표시. tenant_bootstrap.php에서 호출.
 * 외부 의존성 없는 인라인 CSS 기반 정적 마케팅 페이지.
 */

$base_domain = defined('MG_TENANT_BASE_DOMAIN') ? MG_TENANT_BASE_DOMAIN : '';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Morgan CMS — 역할극 커뮤니티 빌더</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            background: #1e1f22;
            color: #f2f3f5;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
        }

        /* Hero */
        .ld-hero {
            min-height: 80vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            background: radial-gradient(ellipse at center top, rgba(245,159,10,0.08) 0%, transparent 60%);
        }
        .ld-logo {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, #2b2d31, #1e1f22);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            border: 1px solid #313338;
        }
        .ld-logo svg { width: 40px; height: 40px; }
        .ld-hero h1 {
            font-size: clamp(2rem, 5vw, 3rem);
            margin: 0 0 0.5rem;
            background: linear-gradient(90deg, #f59f0a, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .ld-hero p {
            color: #b5bac1;
            font-size: clamp(1rem, 2vw, 1.2rem);
            max-width: 600px;
            margin: 0 0 2rem;
        }
        .ld-cta {
            display: inline-block;
            padding: 0.8rem 2rem;
            background: #f59f0a;
            color: #1e1f22;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.05rem;
            transition: background 0.2s, transform 0.1s;
        }
        .ld-cta:hover { background: #d97706; transform: translateY(-1px); }
        .ld-cta:active { transform: translateY(0); }

        /* Features */
        .ld-features {
            max-width: 960px;
            margin: 0 auto;
            padding: 4rem 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        .ld-card {
            background: #2b2d31;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid #313338;
            transition: border-color 0.2s;
        }
        .ld-card:hover { border-color: #f59f0a; }
        .ld-card-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }
        .ld-card h3 {
            margin: 0 0 0.5rem;
            font-size: 1.05rem;
            color: #f2f3f5;
        }
        .ld-card p {
            margin: 0;
            color: #949ba4;
            font-size: 0.9rem;
        }

        /* Footer */
        .ld-footer {
            text-align: center;
            padding: 2rem;
            color: #949ba4;
            font-size: 0.85rem;
            border-top: 1px solid #313338;
        }
        .ld-footer a {
            color: #f59f0a;
            text-decoration: none;
        }
        .ld-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<section class="ld-hero">
    <div class="ld-logo">
        <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M6 25V7h5l5 9 5-9h5v18h-4V13l-6 10-6-10v12H6z" fill="url(#mg)"/>
            <defs><linearGradient id="mg" x1="6" y1="7" x2="26" y2="25">
                <stop stop-color="#fbbf24"/><stop offset="1" stop-color="#d97706"/>
            </linearGradient></defs>
        </svg>
    </div>
    <h1>Morgan CMS</h1>
    <p>역할극, 세계관 설정, 캐릭터 관리까지<br>나만의 커뮤니티를 몇 분 만에 만들어 보세요.</p>
    <a href="/bbs/tenant_onboard.php" class="ld-cta">무료로 시작하기</a>
</section>

<section class="ld-features">
    <div class="ld-card">
        <div class="ld-card-icon">&#127918;</div>
        <h3>캐릭터 시스템</h3>
        <p>프로필, 세력/종족, 관계도 등 캐릭터 관리에 필요한 모든 것을 제공합니다.</p>
    </div>
    <div class="ld-card">
        <div class="ld-card-icon">&#128220;</div>
        <h3>역할극 (RP)</h3>
        <p>실시간 이음글 작성, RP 참여자 관리, 완결 시스템으로 몰입감 있는 역극을 즐기세요.</p>
    </div>
    <div class="ld-card">
        <div class="ld-card-icon">&#127758;</div>
        <h3>세계관 위키</h3>
        <p>타임라인, 지역, 세력, 사건 등 세계관을 체계적으로 기록하고 공유할 수 있습니다.</p>
    </div>
    <div class="ld-card">
        <div class="ld-card-icon">&#128722;</div>
        <h3>상점 & 꾸미기</h3>
        <p>인장(시그니처 카드), 이모티콘, 프로필 꾸미기 등 다양한 커스터마이징 요소.</p>
    </div>
</section>

<footer class="ld-footer">
    <p>&copy; <?php echo date('Y'); ?> Morgan CMS<?php if ($base_domain): ?> &middot; <?php echo htmlspecialchars($base_domain); ?><?php endif; ?></p>
    <?php if ($base_domain): ?>
    <p><a href="https://admin.<?php echo htmlspecialchars($base_domain); ?>/adm/super/">관리자 로그인</a></p>
    <?php endif; ?>
</footer>

</body>
</html>
