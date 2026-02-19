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

<form name="fconfig" id="fconfig" method="post" action="./config_update.php" enctype="multipart/form-data">
    <input type="hidden" name="token" value="">

    <!-- ======================================== -->
    <!-- 기본 설정 -->
    <!-- ======================================== -->
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

    <!-- ======================================== -->
    <!-- 회원 관리 -->
    <!-- ======================================== -->
    <div class="mg-card" style="margin-top:1.5rem;">
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

    <!-- ======================================== -->
    <!-- 컨텐츠 사용 설정 -->
    <!-- ======================================== -->
    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header"><h3>컨텐츠 사용 설정</h3></div>
        <div class="mg-card-body">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">캐릭터 시스템</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">메인 캐릭터 표시</label>
                    <?php echo _cfg_radio('show_main_character', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">진영 시스템</label>
                    <?php echo _cfg_radio('use_side', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">클래스 시스템</label>
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
                    <label class="mg-form-label" for="emoticon_image_max_size">이미지 최대 크기 (KB)</label>
                    <input type="number" name="emoticon_image_max_size" id="emoticon_image_max_size" value="<?php echo isset($mg_configs['emoticon_image_max_size']) ? $mg_configs['emoticon_image_max_size'] : '512'; ?>" class="mg-form-input" min="64">
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
                    <label class="mg-form-label" for="seal_image_max_size">이미지 최대 크기 (KB)</label>
                    <input type="number" name="seal_image_max_size" id="seal_image_max_size" value="<?php echo isset($mg_configs['seal_image_max_size']) ? $mg_configs['seal_image_max_size'] : '500'; ?>" class="mg-form-input" min="100" max="5000">
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

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">위키 설정</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">세계관 위키</label>
                    <?php echo _cfg_radio('lore_use', $mg_configs, '1'); ?>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="lore_image_max_size">이미지 최대 크기 (KB)</label>
                    <input type="number" name="lore_image_max_size" id="lore_image_max_size" value="<?php echo isset($mg_configs['lore_image_max_size']) ? $mg_configs['lore_image_max_size'] : '2048'; ?>" class="mg-form-input" min="100" max="10000">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="lore_thumbnail_max_size">썸네일 최대 크기 (KB)</label>
                    <input type="number" name="lore_thumbnail_max_size" id="lore_thumbnail_max_size" value="<?php echo isset($mg_configs['lore_thumbnail_max_size']) ? $mg_configs['lore_thumbnail_max_size'] : '500'; ?>" class="mg-form-input" min="100" max="5000">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="lore_articles_per_page">페이지당 문서 수</label>
                    <input type="number" name="lore_articles_per_page" id="lore_articles_per_page" value="<?php echo isset($mg_configs['lore_articles_per_page']) ? $mg_configs['lore_articles_per_page'] : '12'; ?>" class="mg-form-input" min="4" max="48">
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
                <div class="mg-form-group">
                    <label class="mg-form-label" for="prompt_banner_max_size">배너 이미지 최대 크기 (KB)</label>
                    <input type="number" name="prompt_banner_max_size" id="prompt_banner_max_size" value="<?php echo isset($mg_configs['prompt_banner_max_size']) ? $mg_configs['prompt_banner_max_size'] : '1024'; ?>" class="mg-form-input" min="100" max="5000">
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
                <div class="mg-form-group">
                    <label class="mg-form-label" for="concierge_reward">의뢰 수행 보상 (P)</label>
                    <input type="number" name="concierge_reward" id="concierge_reward" value="<?php echo isset($mg_configs['concierge_reward']) ? $mg_configs['concierge_reward'] : '50'; ?>" class="mg-form-input" min="0">
                </div>
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

    <!-- ======================================== -->
    <!-- 디자인 설정 -->
    <!-- ======================================== -->
    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header"><h3>디자인 설정</h3></div>
        <div class="mg-card-body">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">레이아웃</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="content_max_width">콘텐츠 최대 너비</label>
                    <input type="text" name="content_max_width" id="content_max_width" value="<?php echo isset($mg_configs['content_max_width']) ? htmlspecialchars($mg_configs['content_max_width']) : '72rem'; ?>" class="mg-form-input" placeholder="72rem">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">모든 페이지 콘텐츠 영역의 최대 너비 (예: 72rem, 1200px, 100%)</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">색상</h4>

            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_accent">메인 컬러 (Accent)</label>
                    <input type="color" name="color_accent" id="color_accent" value="<?php echo isset($mg_configs['color_accent']) ? $mg_configs['color_accent'] : '#f59f0a'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">강조 색상, 링크 등</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_button">버튼 색상</label>
                    <input type="color" name="color_button" id="color_button" value="<?php echo isset($mg_configs['color_button']) ? $mg_configs['color_button'] : '#f59f0a'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">기본 버튼 배경색</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_border">Border 색상</label>
                    <input type="color" name="color_border" id="color_border" value="<?php echo isset($mg_configs['color_border']) ? $mg_configs['color_border'] : '#313338'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">테두리, 구분선 색상</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_bg_primary">배경 색상 (Primary)</label>
                    <input type="color" name="color_bg_primary" id="color_bg_primary" value="<?php echo isset($mg_configs['color_bg_primary']) ? $mg_configs['color_bg_primary'] : '#1e1f22'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">메인 배경색</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="color_bg_secondary">배경 색상 (Secondary)</label>
                    <input type="color" name="color_bg_secondary" id="color_bg_secondary" value="<?php echo isset($mg_configs['color_bg_secondary']) ? $mg_configs['color_bg_secondary'] : '#2b2d31'; ?>" class="mg-form-input" style="height:44px;padding:4px;">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">카드, 섹션 배경색</small>
                </div>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:1rem;color:var(--mg-text-secondary);">배경 이미지</h4>

            <div class="mg-form-group" style="max-width:500px;">
                <label class="mg-form-label">배경 이미지</label>
                <input type="file" name="bg_image" id="bg_image" accept="image/*" class="mg-form-input" onchange="previewBgImage(this)">
                <input type="hidden" name="bg_image_url" id="bg_image_url" value="<?php echo isset($mg_configs['bg_image']) ? htmlspecialchars($mg_configs['bg_image']) : ''; ?>">
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">메인 콘텐츠 영역 배경 이미지 (최대 10MB, jpg/png/gif/webp)</small>
                <div id="bg_image_preview" style="margin-top:0.75rem;">
                    <?php if (!empty($mg_configs['bg_image'])): ?>
                    <div style="display:flex;align-items:center;gap:1rem;">
                        <img src="<?php echo htmlspecialchars($mg_configs['bg_image']); ?>" alt="배경 미리보기" style="max-width:200px;max-height:100px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">
                        <button type="button" class="mg-btn mg-btn-sm" style="background:var(--mg-error);color:#fff;" onclick="removeBgImage()">삭제</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mg-form-group" style="max-width:500px;margin-top:1rem;">
                <label class="mg-form-label" for="bg_opacity">배경 이미지 투명도</label>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <input type="range" name="bg_opacity" id="bg_opacity" min="0" max="100" value="<?php echo isset($mg_configs['bg_opacity']) ? $mg_configs['bg_opacity'] : '20'; ?>" style="flex:1;">
                    <span id="bg_opacity_value" style="min-width:40px;"><?php echo isset($mg_configs['bg_opacity']) ? $mg_configs['bg_opacity'] : '20'; ?>%</span>
                </div>
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">배경 이미지의 불투명도 (0: 투명, 100: 불투명)</small>
            </div>

        </div>
    </div>

    <!-- 저장 버튼 -->
    <div style="margin-top:1.5rem;display:flex;gap:1rem;align-items:center;">
        <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
        <button type="button" class="mg-btn mg-btn-secondary" onclick="resetColors()">색상 초기화</button>
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

document.getElementById('bg_opacity').addEventListener('input', function() {
    document.getElementById('bg_opacity_value').textContent = this.value + '%';
});

function previewBgImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bg_image_preview').innerHTML =
                '<div style="display:flex;align-items:center;gap:1rem;">' +
                '<img src="' + e.target.result + '" alt="미리보기" style="max-width:200px;max-height:100px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">' +
                '<span style="color:var(--mg-accent);font-size:0.8rem;">새 이미지 선택됨</span>' +
                '</div>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeBgImage() {
    document.getElementById('bg_image_url').value = '__DELETE__';
    document.getElementById('bg_image').value = '';
    document.getElementById('bg_image_preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.8rem;">이미지가 삭제됩니다 (저장 시 적용)</span>';
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        var file = input.files[0];
        if (file.size > 2 * 1024 * 1024) {
            alert('로고 파일은 2MB 이하만 가능합니다.');
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

function resetColors() {
    if (!confirm('모든 색상을 기본값으로 초기화하시겠습니까?')) return;
    document.getElementById('color_accent').value = '#f59f0a';
    document.getElementById('color_button').value = '#f59f0a';
    document.getElementById('color_border').value = '#313338';
    document.getElementById('color_bg_primary').value = '#1e1f22';
    document.getElementById('color_bg_secondary').value = '#2b2d31';
}

</script>

<?php
require_once __DIR__.'/_tail.php';
?>
