<?php
/**
 * Morgan Edition - 기본 설정
 */

$sub_menu = "800100";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 탭 라우팅
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'basic';
if (!in_array($tab, array('basic', 'member', 'content'))) $tab = 'basic';

// 설정 로드
$mg_configs = array();
$sql = "SELECT * FROM {$g5['mg_config_table']}";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $mg_configs[$row['cf_key']] = $row['cf_value'];
}

$g5['title'] = '기본 설정';
require_once __DIR__.'/_head.php';

// 헬퍼: 라디오 on/off
function _cfg_radio($name, $configs, $default = '1', $labels = array('사용', '사용안함')) {
    $val = isset($configs[$name]) ? $configs[$name] : $default;
    $html  = '<div style="display:flex;gap:1rem;margin-top:0.5rem;">';
    $html .= '<label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">';
    $html .= '<input type="radio" name="'.htmlspecialchars($name).'" value="1" '.($val == '1' ? 'checked' : '').'>';
    $html .= '<span>'.$labels[0].'</span></label>';
    $html .= '<label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">';
    $html .= '<input type="radio" name="'.htmlspecialchars($name).'" value="0" '.($val == '0' ? 'checked' : '').'>';
    $html .= '<span>'.$labels[1].'</span></label>';
    $html .= '</div>';
    return $html;
}
?>

<!-- 탭 바 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=basic" class="mg-tab <?php echo $tab == 'basic' ? 'active' : ''; ?>">기본 설정</a>
    <a href="?tab=member" class="mg-tab <?php echo $tab == 'member' ? 'active' : ''; ?>">회원 관리</a>
    <a href="?tab=content" class="mg-tab <?php echo $tab == 'content' ? 'active' : ''; ?>">컨텐츠 설정</a>
</div>

<?php if ($tab == 'basic') { ?>
<!-- ======================================== -->
<!-- 기본 설정 탭 -->
<!-- ======================================== -->
<form name="fconfig" method="post" action="./config_update.php" enctype="multipart/form-data">
    <input type="hidden" name="_redirect" value="config.php?tab=basic">

    <div class="mg-card">
        <div class="mg-card-header"><h3>기본 설정</h3></div>
        <div class="mg-card-body">

            <div class="mg-form-group">
                <label class="mg-form-label" for="site_name">사이트명</label>
                <input type="text" name="site_name" id="site_name" value="<?php echo isset($mg_configs['site_name']) ? $mg_configs['site_name'] : 'Morgan Edition'; ?>" class="mg-form-input" style="max-width:400px;">
            </div>

            <div class="mg-form-group" style="max-width:500px;">
                <label class="mg-form-label">사이트 로고</label>
                <input type="file" name="site_logo" id="site_logo" accept="image/*" class="mg-form-input" onchange="previewLogo(this)">
                <input type="hidden" name="site_logo_action" id="site_logo_action" value="">
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">헤더 좌측 상단에 표시 (권장: 높이 32px, PNG/SVG 투명 배경, 최대 2MB)</small>
                <div id="site_logo_preview" style="margin-top:0.75rem;">
                    <?php if (!empty($mg_configs['site_logo'])): ?>
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <div style="background:var(--mg-bg-tertiary);padding:8px 12px;border-radius:6px;display:inline-flex;align-items:center;">
                            <img src="<?php echo htmlspecialchars($mg_configs['site_logo']); ?>" alt="로고" style="max-height:32px;max-width:160px;">
                        </div>
                        <button type="button" class="mg-btn mg-btn-sm" style="background:var(--mg-error);color:#fff;" onclick="removeLogo()">삭제</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">포인트 / 캐릭터</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="login_point">로그인 포인트</label>
                    <input type="number" name="login_point" id="login_point" value="<?php echo isset($mg_configs['login_point']) ? $mg_configs['login_point'] : '10'; ?>" class="mg-form-input">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">회원 로그인 시 지급되는 포인트</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="attendance_point">출석 포인트</label>
                    <input type="number" name="attendance_point" id="attendance_point" value="<?php echo isset($mg_configs['attendance_point']) ? $mg_configs['attendance_point'] : '50'; ?>" class="mg-form-input">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">일일 출석체크 시 지급되는 포인트</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="character_create_point">캐릭터 생성 비용</label>
                    <input type="number" name="character_create_point" id="character_create_point" value="<?php echo isset($mg_configs['character_create_point']) ? $mg_configs['character_create_point'] : '100'; ?>" class="mg-form-input">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">캐릭터 생성 시 필요한 포인트 (0: 무료)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="max_characters">최대 캐릭터 수</label>
                    <input type="number" name="max_characters" id="max_characters" value="<?php echo isset($mg_configs['max_characters']) ? $mg_configs['max_characters'] : '10'; ?>" class="mg-form-input">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">회원당 보유 가능한 최대 캐릭터 수</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">접속자 표시</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">헤더 접속자 수 표시</label>
                    <?php echo _cfg_radio('show_connect_count', $mg_configs, '1'); ?>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">헤더 우측에 현재 접속자 수 배지 및 클릭 모달 표시</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">보안 설정 (Google reCAPTCHA v3)</h4>

            <div class="mg-alert mg-alert-info" style="margin-bottom:1.25rem;">
                봇의 자동 가입/글쓰기를 방지합니다. 사이트 키와 시크릿 키를 입력하면 선택한 항목에 자동 적용되며, 비워두면 캡챠가 비활성화됩니다.<br><br>
                <strong>키 발급 방법:</strong><br>
                1. <a href="https://www.google.com/recaptcha/admin" target="_blank" style="color:var(--mg-accent);">Google reCAPTCHA 관리 콘솔</a> 접속 (Google 계정 필요)<br>
                2. <code>+</code> 버튼 → 새 사이트 등록 → <strong>reCAPTCHA v3</strong> 유형 선택<br>
                3. 도메인 입력 (예: example.com) → 등록 후 사이트 키 + 비밀 키 복사
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; max-width:700px; margin-bottom:1.25rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="recaptcha_site_key">reCAPTCHA 사이트 키</label>
                    <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" value="<?php echo isset($mg_configs['recaptcha_site_key']) ? htmlspecialchars($mg_configs['recaptcha_site_key']) : ''; ?>" class="mg-form-input" placeholder="6Lc..." autocomplete="off" oninput="updateCaptchaToggles()">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="recaptcha_secret_key">reCAPTCHA 시크릿 키</label>
                    <input type="password" name="recaptcha_secret_key" id="recaptcha_secret_key" value="<?php echo isset($mg_configs['recaptcha_secret_key']) ? htmlspecialchars($mg_configs['recaptcha_secret_key']) : ''; ?>" class="mg-form-input" placeholder="6Lc..." autocomplete="off" oninput="updateCaptchaToggles()">
                </div>
            </div>

            <div id="captcha_toggles" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">회원가입</label>
                    <?php echo _cfg_radio('captcha_register', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">글쓰기</label>
                    <?php echo _cfg_radio('captcha_write', $mg_configs, '0'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">댓글</label>
                    <?php echo _cfg_radio('captcha_comment', $mg_configs, '0'); ?>
                </div>
            </div>

        </div>
    </div>

    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header"><h3>파일 업로드 제한</h3></div>
        <div class="mg-card-body">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="upload_max_file">일반 파일 최대 크기 (KB)</label>
                    <input type="number" name="upload_max_file" id="upload_max_file" value="<?php echo isset($mg_configs['upload_max_file']) ? $mg_configs['upload_max_file'] : '5120'; ?>" class="mg-form-input" min="512" max="51200">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">이미지, 배너, 배경, 에디터 첨부 등 일반 파일 업로드에 적용 (기본 5120KB = 5MB)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="upload_max_icon">아이콘 파일 최대 크기 (KB)</label>
                    <input type="number" name="upload_max_icon" id="upload_max_icon" value="<?php echo isset($mg_configs['upload_max_icon']) ? $mg_configs['upload_max_icon'] : '2048'; ?>" class="mg-form-input" min="128" max="10240">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">이모티콘, 뱃지 아이콘 등 소형 파일 업로드에 적용 (기본 2048KB = 2MB)</small>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
    </div>
</form>

<script>
function updateCaptchaToggles() {
    var siteKey = document.getElementById('recaptcha_site_key').value.trim();
    var secretKey = document.getElementById('recaptcha_secret_key').value.trim();
    var toggles = document.getElementById('captcha_toggles');
    if (!siteKey || !secretKey) {
        toggles.style.opacity = '0.4';
        toggles.style.pointerEvents = 'none';
    } else {
        toggles.style.opacity = '1';
        toggles.style.pointerEvents = '';
    }
}
updateCaptchaToggles();

function previewLogo(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        if (file.size > <?php echo mg_upload_max_file(); ?>) {
            alert('파일 크기가 너무 큽니다.');
            input.value = '';
            return;
        }
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('site_logo_preview').innerHTML =
                '<div style="display:flex;align-items:center;gap:1rem;">' +
                '<div style="background:var(--mg-bg-tertiary);padding:8px 12px;border-radius:6px;display:inline-flex;align-items:center;">' +
                '<img src="' + e.target.result + '" alt="미리보기" style="max-height:32px;max-width:160px;">' +
                '</div>' +
                '<span style="color:var(--mg-accent);font-size:0.8rem;">새 로고 선택됨</span>' +
                '</div>';
            document.getElementById('site_logo_action').value = '';
        };
        reader.readAsDataURL(file);
    }
}

function removeLogo() {
    document.getElementById('site_logo_action').value = '__DELETE__';
    document.getElementById('site_logo').value = '';
    document.getElementById('site_logo_preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.8rem;">로고가 삭제됩니다 (저장 시 적용)</span>';
}

</script>

<?php } elseif ($tab == 'member') { ?>
<!-- ======================================== -->
<!-- 회원 관리 탭 -->
<!-- ======================================== -->
<form method="post" action="./config_update.php">
    <input type="hidden" name="_redirect" value="config.php?tab=member">

    <div class="mg-card">
        <div class="mg-card-header"><h3>회원 관리</h3></div>
        <div class="mg-card-body">

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="cf_nick_modify">닉네임 변경 주기 (일)</label>
                    <input type="number" name="cf_nick_modify" id="cf_nick_modify" value="<?php echo (int)$config['cf_nick_modify']; ?>" class="mg-form-input" min="0" max="365">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">닉네임 변경 후 다시 변경 가능하기까지의 일수 (0: 제한없음)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="cf_register_level">가입 시 기본 레벨</label>
                    <input type="number" name="cf_register_level" id="cf_register_level" value="<?php echo (int)$config['cf_register_level']; ?>" class="mg-form-input" min="1" max="9">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">신규 가입 회원에게 부여되는 기본 레벨 (1~9)</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">기능별 최소 회원 레벨</h4>
            <p style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:1rem;">해당 레벨 미만 회원은 기능 이용(생성/참여)이 제한됩니다. 열람은 제한하지 않습니다.</p>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="rp_min_level">역극</label>
                    <input type="number" name="rp_min_level" id="rp_min_level" value="<?php echo isset($mg_configs['rp_min_level']) ? $mg_configs['rp_min_level'] : '2'; ?>" class="mg-form-input" min="1" max="9">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_min_level">의뢰</label>
                    <input type="number" name="concierge_min_level" id="concierge_min_level" value="<?php echo isset($mg_configs['concierge_min_level']) ? $mg_configs['concierge_min_level'] : '2'; ?>" class="mg-form-input" min="1" max="9">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="pioneer_min_level">개척</label>
                    <input type="number" name="pioneer_min_level" id="pioneer_min_level" value="<?php echo isset($mg_configs['pioneer_min_level']) ? $mg_configs['pioneer_min_level'] : '2'; ?>" class="mg-form-input" min="1" max="9">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="seal_min_level">인장</label>
                    <input type="number" name="seal_min_level" id="seal_min_level" value="<?php echo isset($mg_configs['seal_min_level']) ? $mg_configs['seal_min_level'] : '2'; ?>" class="mg-form-input" min="1" max="9">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="emoticon_min_level">이모티콘 제작</label>
                    <input type="number" name="emoticon_min_level" id="emoticon_min_level" value="<?php echo isset($mg_configs['emoticon_min_level']) ? $mg_configs['emoticon_min_level'] : '2'; ?>" class="mg-form-input" min="1" max="9">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="prompt_min_level">미션</label>
                    <input type="number" name="prompt_min_level" id="prompt_min_level" value="<?php echo isset($mg_configs['prompt_min_level']) ? $mg_configs['prompt_min_level'] : '2'; ?>" class="mg-form-input" min="1" max="9">
                </div>
            </div>

        </div>
    </div>

    <div style="margin-top:1.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
    </div>
</form>

<?php } elseif ($tab == 'content') { ?>
<!-- ======================================== -->
<!-- 컨텐츠 사용 설정 탭 -->
<!-- ======================================== -->
<form method="post" action="./config_update.php">
    <input type="hidden" name="_redirect" value="config.php?tab=content">

    <div class="mg-card">
        <div class="mg-card-header"><h3>컨텐츠 사용 설정</h3></div>
        <div class="mg-card-body">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">캐릭터 시스템</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">메인 캐릭터 표시</label>
                    <?php echo _cfg_radio('show_main_character', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">소속 시스템</label>
                    <?php echo _cfg_radio('use_side', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">유형 시스템</label>
                    <?php echo _cfg_radio('use_class', $mg_configs, '1'); ?>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">역극 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">역극 기능</label>
                    <?php echo _cfg_radio('rp_use', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="rp_require_reply">판 세우기 조건</label>
                    <input type="number" name="rp_require_reply" id="rp_require_reply" value="<?php echo isset($mg_configs['rp_require_reply']) ? $mg_configs['rp_require_reply'] : '0'; ?>" class="mg-form-input" min="0">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">다른 역극에 N회 이음 후 판 세우기 가능 (0: 제한없음)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="rp_max_member_default">기본 최대 참여자</label>
                    <input type="number" name="rp_max_member_default" id="rp_max_member_default" value="<?php echo isset($mg_configs['rp_max_member_default']) ? $mg_configs['rp_max_member_default'] : '0'; ?>" class="mg-form-input" min="0">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">새 역극 생성 시 기본 최대 참여자 수 (0: 무제한)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="rp_max_member_limit">참여자 상한선</label>
                    <input type="number" name="rp_max_member_limit" id="rp_max_member_limit" value="<?php echo isset($mg_configs['rp_max_member_limit']) ? $mg_configs['rp_max_member_limit'] : '20'; ?>" class="mg-form-input" min="0">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">유저가 설정 가능한 최대값</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="rp_content_min">최소 글자 수</label>
                    <input type="number" name="rp_content_min" id="rp_content_min" value="<?php echo isset($mg_configs['rp_content_min']) ? $mg_configs['rp_content_min'] : '0'; ?>" class="mg-form-input" min="0">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">이음 작성 시 최소 글자 수</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">이모티콘 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">이모티콘 기능</label>
                    <?php echo _cfg_radio('emoticon_use', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">유저 이모티콘 제작</label>
                    <?php echo _cfg_radio('emoticon_creator_use', $mg_configs, '1', array('허용', '비허용')); ?>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">유저가 등록권을 구매하여 이모티콘 셋을 제작/판매할 수 있음</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="emoticon_commission_rate">판매 수수료율 (%)</label>
                    <input type="number" name="emoticon_commission_rate" id="emoticon_commission_rate" value="<?php echo isset($mg_configs['emoticon_commission_rate']) ? $mg_configs['emoticon_commission_rate'] : '10'; ?>" class="mg-form-input" min="0" max="50">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">유저 이모티콘 판매 시 수수료 (소멸되는 재화)</small>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="emoticon_min_count">셋 당 최소 이모티콘</label>
                    <input type="number" name="emoticon_min_count" id="emoticon_min_count" value="<?php echo isset($mg_configs['emoticon_min_count']) ? $mg_configs['emoticon_min_count'] : '8'; ?>" class="mg-form-input" min="1">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="emoticon_max_count">셋 당 최대 이모티콘</label>
                    <input type="number" name="emoticon_max_count" id="emoticon_max_count" value="<?php echo isset($mg_configs['emoticon_max_count']) ? $mg_configs['emoticon_max_count'] : '30'; ?>" class="mg-form-input" min="1">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="emoticon_image_size">권장 이미지 크기 (px)</label>
                    <input type="number" name="emoticon_image_size" id="emoticon_image_size" value="<?php echo isset($mg_configs['emoticon_image_size']) ? $mg_configs['emoticon_image_size'] : '128'; ?>" class="mg-form-input" min="32">
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">인장 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">인장 시스템</label>
                    <?php echo _cfg_radio('seal_enable', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">이미지 업로드 허용</label>
                    <?php echo _cfg_radio('seal_image_upload', $mg_configs, '1', array('허용', '비허용')); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">외부 이미지 URL 허용</label>
                    <?php echo _cfg_radio('seal_image_url', $mg_configs, '1', array('허용', '비허용')); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">링크 허용</label>
                    <?php echo _cfg_radio('seal_link_allow', $mg_configs, '1', array('허용', '비허용')); ?>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="seal_tagline_max">한마디 최대 글자수</label>
                    <input type="number" name="seal_tagline_max" id="seal_tagline_max" value="<?php echo isset($mg_configs['seal_tagline_max']) ? $mg_configs['seal_tagline_max'] : '50'; ?>" class="mg-form-input" min="10" max="200">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="seal_content_max">자유 영역 최대 글자수</label>
                    <input type="number" name="seal_content_max" id="seal_content_max" value="<?php echo isset($mg_configs['seal_content_max']) ? $mg_configs['seal_content_max'] : '300'; ?>" class="mg-form-input" min="50" max="1000">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="seal_trophy_slots">트로피 슬롯 수</label>
                    <input type="number" name="seal_trophy_slots" id="seal_trophy_slots" value="<?php echo isset($mg_configs['seal_trophy_slots']) ? $mg_configs['seal_trophy_slots'] : '3'; ?>" class="mg-form-input" min="0" max="10">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">역극 이음에 표시</label>
                    <?php echo _cfg_radio('seal_show_in_rp', $mg_configs, '1', array('표시', '미표시')); ?>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">역극 이음에 compact 인장 표시</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">댓글에 표시</label>
                    <?php echo _cfg_radio('seal_show_in_comment', $mg_configs, '0', array('표시', '미표시')); ?>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">게시글 댓글에 인장 표시 (부하 주의)</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">개척 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">개척 시스템</label>
                    <?php echo _cfg_radio('pioneer_use', $mg_configs, '1'); ?>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">뷰 모드·거점 이미지는 <a href="<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility.php" style="color:var(--mg-accent);">시설 관리</a>에서 설정합니다.</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">위키 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">세계관 위키</label>
                    <?php echo _cfg_radio('lore_use', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="lore_articles_per_page">페이지당 문서 수</label>
                    <input type="number" name="lore_articles_per_page" id="lore_articles_per_page" value="<?php echo isset($mg_configs['lore_articles_per_page']) ? $mg_configs['lore_articles_per_page'] : '12'; ?>" class="mg-form-input" min="4" max="48">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group" style="grid-column:1/-1;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">연대기/맵 페이지 설명은 각각의 관리 페이지(타임라인, 지도)에서 설정합니다.</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">미션 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">미션 시스템</label>
                    <?php echo _cfg_radio('prompt_enable', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="prompt_show_closed">종료 미션 표시 수</label>
                    <input type="number" name="prompt_show_closed" id="prompt_show_closed" value="<?php echo isset($mg_configs['prompt_show_closed']) ? $mg_configs['prompt_show_closed'] : '3'; ?>" class="mg-form-input" min="0" max="20">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">제출 시 관리자 알림</label>
                    <?php echo _cfg_radio('prompt_notify_submit', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">승인 시 유저 알림</label>
                    <?php echo _cfg_radio('prompt_notify_approve', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">반려 시 유저 알림</label>
                    <?php echo _cfg_radio('prompt_notify_reject', $mg_configs, '1'); ?>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">의뢰 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">의뢰 시스템</label>
                    <?php echo _cfg_radio('concierge_use', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_max_slots">동시 등록 가능 의뢰 수</label>
                    <input type="number" name="concierge_max_slots" id="concierge_max_slots" value="<?php echo isset($mg_configs['concierge_max_slots']) ? $mg_configs['concierge_max_slots'] : '1'; ?>" class="mg-form-input" min="1" max="10">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_max_applies">동시 지원 가능 수</label>
                    <input type="number" name="concierge_max_applies" id="concierge_max_applies" value="<?php echo isset($mg_configs['concierge_max_applies']) ? $mg_configs['concierge_max_applies'] : '3'; ?>" class="mg-form-input" min="1" max="10">
                </div>
            </div>

            <h5 style="font-size:0.8rem;font-weight:600;margin:1.25rem 0 0.75rem;color:var(--mg-text-muted);">포인트</h5>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_point_min">보상 최솟값 (P)</label>
                    <input type="number" name="concierge_point_min" id="concierge_point_min" value="<?php echo isset($mg_configs['concierge_point_min']) ? $mg_configs['concierge_point_min'] : '0'; ?>" class="mg-form-input" min="0">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">0이면 무보수 의뢰 허용</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_point_max">보상 최댓값 (P)</label>
                    <input type="number" name="concierge_point_max" id="concierge_point_max" value="<?php echo isset($mg_configs['concierge_point_max']) ? $mg_configs['concierge_point_max'] : '1000'; ?>" class="mg-form-input" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_fee_rate">수수료율 (%)</label>
                    <input type="number" name="concierge_fee_rate" id="concierge_fee_rate" value="<?php echo isset($mg_configs['concierge_fee_rate']) ? $mg_configs['concierge_fee_rate'] : '0'; ?>" class="mg-form-input" min="0" max="50">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">의뢰 완료 시 보상에서 차감 후 소멸 (0=수수료 없음)</small>
                </div>
            </div>

            <h5 style="font-size:0.8rem;font-weight:600;margin:1.25rem 0 0.75rem;color:var(--mg-text-muted);">매칭</h5>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_match_mode_allowed">허용 매칭 방식</label>
                    <select name="concierge_match_mode_allowed" id="concierge_match_mode_allowed" class="mg-form-input">
                        <?php $_mma = isset($mg_configs['concierge_match_mode_allowed']) ? $mg_configs['concierge_match_mode_allowed'] : 'both'; ?>
                        <option value="both" <?php echo $_mma === 'both' ? 'selected' : ''; ?>>둘 다 허용</option>
                        <option value="direct_only" <?php echo $_mma === 'direct_only' ? 'selected' : ''; ?>>직접선택만</option>
                        <option value="lottery_only" <?php echo $_mma === 'lottery_only' ? 'selected' : ''; ?>>추첨만</option>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">신청자 익명</label>
                    <?php echo _cfg_radio('concierge_apply_anonymous', $mg_configs, '0'); ?>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">ON: 의뢰 작성자와 관리자만 신청자 정보 확인 가능</small>
                </div>
            </div>

            <h5 style="font-size:0.8rem;font-weight:600;margin:1.25rem 0 0.75rem;color:var(--mg-text-muted);">페널티</h5>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_penalty_count">미이행 제한 횟수</label>
                    <input type="number" name="concierge_penalty_count" id="concierge_penalty_count" value="<?php echo isset($mg_configs['concierge_penalty_count']) ? $mg_configs['concierge_penalty_count'] : '3'; ?>" class="mg-form-input" min="1" max="20">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">이 횟수 이상 미이행 시 의뢰 이용 제한</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_penalty_days">미이행 제한 기간 (일)</label>
                    <input type="number" name="concierge_penalty_days" id="concierge_penalty_days" value="<?php echo isset($mg_configs['concierge_penalty_days']) ? $mg_configs['concierge_penalty_days'] : '30'; ?>" class="mg-form-input" min="1" max="365">
                </div>
            </div>

        </div>
    </div>

    <div style="margin-top:1.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
    </div>
</form>

<?php } ?>

<?php
require_once __DIR__.'/_tail.php';
?>
