<?php
/**
 * Morgan Edition - Google reCAPTCHA v3 Captcha Plugin
 *
 * 그누보드5 captcha 표준 인터페이스 (drop-in replacement)
 * - captcha_html()  : 폼에 삽입할 reCAPTCHA HTML 반환
 * - chk_captcha()   : 서버사이드 토큰 검증
 * - chk_captcha_js(): JS 검증 코드 반환
 *
 * Morgan 설정(mg_config)에서 키를 읽으며, 키가 없으면 캡챠 비활성화.
 * jQuery 의존 없음 (vanilla JS).
 */

if (!defined('_GNUBOARD_')) exit;

/**
 * Morgan 플러그인 로드 보장
 */
function _mg_captcha_ensure_loaded()
{
    if (!function_exists('mg_config')) {
        if (defined('G5_PLUGIN_PATH') && file_exists(G5_PLUGIN_PATH.'/morgan/morgan.php')) {
            include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');
        }
    }
}

/**
 * reCAPTCHA가 현재 컨텍스트에서 활성화되어 있는지 확인
 *
 * @return bool
 */
function _mg_captcha_is_enabled()
{
    _mg_captcha_ensure_loaded();

    if (!function_exists('mg_config')) {
        return false;
    }

    $site_key = trim(mg_config('recaptcha_site_key', ''));
    $secret_key = trim(mg_config('recaptcha_secret_key', ''));

    if (!$site_key || !$secret_key) {
        return false;
    }

    // 컨텍스트별 토글 확인
    $context = isset($GLOBALS['mg_captcha_context']) ? $GLOBALS['mg_captcha_context'] : '';
    if ($context) {
        $toggle = mg_config('captcha_' . $context, '0');
        if ($toggle !== '1') {
            return false;
        }
    }

    return true;
}

/**
 * 캡챠 HTML 코드 출력
 *
 * reCAPTCHA v3: 사용자에게 보이지 않는 백그라운드 검증
 * - Google reCAPTCHA JS 로드
 * - hidden input으로 토큰 전달
 * - form submit 시 자동으로 토큰 취득
 *
 * @param string $class CSS 클래스 (호환용, 실제 미사용)
 * @return string
 */
function captcha_html($class = 'captcha')
{
    if (!_mg_captcha_is_enabled()) {
        return '';
    }

    _mg_captcha_ensure_loaded();
    $site_key = trim(mg_config('recaptcha_site_key', ''));
    $context = isset($GLOBALS['mg_captcha_context']) ? $GLOBALS['mg_captcha_context'] : 'submit';

    $html = '';

    // reCAPTCHA v3 API 스크립트 (한 번만 로드)
    $html .= '<script src="https://www.google.com/recaptcha/api.js?render=' . htmlspecialchars($site_key) . '"></script>';

    // hidden input
    $html .= '<input type="hidden" name="g-recaptcha-response" id="mg-recaptcha-response" value="">';

    // form submit 인터셉트 (vanilla JS)
    $html .= '<script>
(function() {
    var MG_RECAPTCHA_SITE_KEY = ' . json_encode($site_key) . ';
    var MG_RECAPTCHA_ACTION = ' . json_encode($context) . ';
    var mgCaptchaDone = false;

    function mgCaptchaInit() {
        // 가장 가까운 form 찾기
        var tokenInput = document.getElementById("mg-recaptcha-response");
        if (!tokenInput) return;

        var form = tokenInput.closest("form");
        if (!form) return;

        form.addEventListener("submit", function(e) {
            // 이미 토큰이 세팅되어 있으면 통과
            if (mgCaptchaDone && tokenInput.value) return;

            e.preventDefault();

            if (typeof grecaptcha === "undefined") {
                // reCAPTCHA 로드 실패 시 그냥 제출
                form.submit();
                return;
            }

            grecaptcha.ready(function() {
                grecaptcha.execute(MG_RECAPTCHA_SITE_KEY, { action: MG_RECAPTCHA_ACTION })
                    .then(function(token) {
                        tokenInput.value = token;
                        mgCaptchaDone = true;
                        form.submit();
                    })
                    .catch(function() {
                        // 실패 시 그냥 제출 (서버에서 검증 실패 처리)
                        form.submit();
                    });
            });
        });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", mgCaptchaInit);
    } else {
        mgCaptchaInit();
    }
})();
</script>';

    return $html;
}

/**
 * 캡챠 검증 JS 반환 (호환용)
 *
 * reCAPTCHA v3는 form submit 인터셉트로 처리하므로
 * 별도 JS 검증 코드가 필요 없음
 *
 * @return string
 */
function chk_captcha_js()
{
    return '';
}

/**
 * 서버사이드 캡챠 검증
 *
 * Google reCAPTCHA API로 토큰을 검증하고 score를 확인
 *
 * @return bool true = 통과, false = 실패
 */
function chk_captcha()
{
    if (!_mg_captcha_is_enabled()) {
        return true; // 비활성화 시 무조건 통과
    }

    _mg_captcha_ensure_loaded();
    $secret_key = trim(mg_config('recaptcha_secret_key', ''));

    $response = isset($_POST['g-recaptcha-response']) ? trim($_POST['g-recaptcha-response']) : '';

    if (!$response) {
        return false;
    }

    // Google API로 검증
    $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret'   => $secret_key,
        'response' => $response,
        'remoteip' => $_SERVER['REMOTE_ADDR']
    );

    $result = _mg_captcha_verify_request($verify_url, $data);

    if (!$result) {
        return false;
    }

    $json = json_decode($result, true);

    if (!$json || !isset($json['success'])) {
        return false;
    }

    // reCAPTCHA v3: success + score >= 0.5
    if ($json['success'] === true) {
        $score = isset($json['score']) ? (float)$json['score'] : 0;
        return $score >= 0.5;
    }

    return false;
}

/**
 * Google API 검증 요청 (curl 또는 file_get_contents)
 *
 * @param string $url
 * @param array $data
 * @return string|false
 */
function _mg_captcha_verify_request($url, $data)
{
    // curl 사용 (우선)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    // file_get_contents fallback
    $options = array(
        'http' => array(
            'method'  => 'POST',
            'header'  => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($data),
            'timeout' => 10
        )
    );
    $context = stream_context_create($options);
    return @file_get_contents($url, false, $context);
}
