<?php
/**
 * Morgan Edition - 스토리지 설정
 */

$sub_menu = "800102";
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

// 현재 드라이버
$current_driver = isset($mg_configs['mg_storage_driver']) ? $mg_configs['mg_storage_driver'] : 'local';

$g5['title'] = '스토리지 설정';
require_once __DIR__.'/_head.php';

// 헬퍼
function _sc($key, $configs, $default = '') {
    return htmlspecialchars(isset($configs[$key]) ? $configs[$key] : $default);
}
?>

<form id="fstorageconfig" method="post" action="./storage_config_update.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">

    <!-- 드라이버 선택 -->
    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">
            <h3>스토리지 드라이버</h3>
        </div>
        <div class="mg-card-body">
            <div style="margin-bottom:1rem;">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0.5rem;">
                    <input type="radio" name="mg_storage_driver" value="local"
                        <?php echo $current_driver === 'local' ? 'checked' : ''; ?>
                        onchange="toggleR2Fields()">
                    <strong>로컬 파일시스템</strong>
                    <span style="color:var(--mg-text-muted);font-size:0.85rem;">(기본값)</span>
                </label>
                <p style="color:var(--mg-text-secondary);font-size:0.85rem;margin:0 0 0 1.5rem;">
                    서버 로컬 디스크에 파일을 저장합니다. 별도 설정 없이 바로 사용 가능합니다.
                </p>
            </div>
            <div>
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-bottom:0.5rem;">
                    <input type="radio" name="mg_storage_driver" value="r2"
                        <?php echo $current_driver === 'r2' ? 'checked' : ''; ?>
                        onchange="toggleR2Fields()">
                    <strong>Cloudflare R2</strong>
                    <span style="color:var(--mg-text-muted);font-size:0.85rem;">(S3 호환 오브젝트 스토리지)</span>
                </label>
                <p style="color:var(--mg-text-secondary);font-size:0.85rem;margin:0 0 0 1.5rem;">
                    Cloudflare R2에 파일을 저장합니다. 별도 설정이 필요합니다.
                </p>
            </div>
        </div>
    </div>

    <!-- R2 설정 -->
    <div id="r2-settings" class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">
            <h3>Cloudflare R2 설정</h3>
        </div>
        <div class="mg-card-body">
            <table class="mg-table-form">
                <tr>
                    <th style="width:180px;">Account ID</th>
                    <td>
                        <input type="text" name="mg_r2_account_id" class="mg-input" style="width:400px;"
                            value="<?php echo _sc('mg_r2_account_id', $mg_configs); ?>"
                            placeholder="Cloudflare 대시보드에서 확인">
                        <p class="mg-help">Cloudflare 대시보드 우측 하단의 Account ID</p>
                    </td>
                </tr>
                <tr>
                    <th>Access Key ID</th>
                    <td>
                        <input type="text" name="mg_r2_access_key_id" class="mg-input" style="width:400px;"
                            value="<?php echo _sc('mg_r2_access_key_id', $mg_configs); ?>"
                            placeholder="R2 API 토큰의 Access Key ID">
                    </td>
                </tr>
                <tr>
                    <th>Secret Access Key</th>
                    <td>
                        <input type="password" name="mg_r2_secret_access_key" class="mg-input" style="width:400px;"
                            value="<?php echo _sc('mg_r2_secret_access_key', $mg_configs); ?>"
                            placeholder="R2 API 토큰의 Secret Access Key">
                        <p class="mg-help">보안을 위해 마스킹 처리됩니다</p>
                    </td>
                </tr>
                <tr>
                    <th>Bucket Name</th>
                    <td>
                        <input type="text" name="mg_r2_bucket_name" class="mg-input" style="width:300px;"
                            value="<?php echo _sc('mg_r2_bucket_name', $mg_configs); ?>"
                            placeholder="예: morgan-storage">
                    </td>
                </tr>
                <tr>
                    <th>Endpoint URL</th>
                    <td>
                        <input type="text" name="mg_r2_endpoint" class="mg-input" style="width:500px;"
                            value="<?php echo _sc('mg_r2_endpoint', $mg_configs); ?>"
                            placeholder="비워두면 Account ID로 자동 생성">
                        <p class="mg-help">비워두면 <code>https://{Account ID}.r2.cloudflarestorage.com</code> 사용</p>
                    </td>
                </tr>
                <tr>
                    <th>Public URL</th>
                    <td>
                        <input type="text" name="mg_r2_public_url" class="mg-input" style="width:500px;"
                            value="<?php echo _sc('mg_r2_public_url', $mg_configs); ?>"
                            placeholder="예: https://cdn.example.com">
                        <p class="mg-help">R2 버킷의 퍼블릭 접근 URL 또는 커스텀 도메인</p>
                    </td>
                </tr>
            </table>

            <!-- 연결 테스트 -->
            <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid var(--mg-bg-tertiary);">
                <button type="button" id="btn-test-connection" class="mg-btn mg-btn-secondary" onclick="testConnection()">
                    연결 테스트
                </button>
                <span id="test-result" style="margin-left:1rem;font-size:0.9rem;"></span>
            </div>
        </div>
    </div>

    <!-- 저장 -->
    <div style="text-align:center;margin-top:2rem;">
        <button type="submit" class="mg-btn mg-btn-primary" style="padding:0.75rem 3rem;font-size:1rem;">
            설정 저장
        </button>
    </div>
</form>

<script>
function toggleR2Fields() {
    var driver = document.querySelector('input[name="mg_storage_driver"]:checked').value;
    var r2Section = document.getElementById('r2-settings');
    var inputs = r2Section.querySelectorAll('input[type="text"], input[type="password"]');

    if (driver === 'r2') {
        r2Section.style.opacity = '1';
        r2Section.style.pointerEvents = 'auto';
        inputs.forEach(function(inp) { inp.disabled = false; });
    } else {
        r2Section.style.opacity = '0.5';
        r2Section.style.pointerEvents = 'none';
        inputs.forEach(function(inp) { inp.disabled = true; });
    }
}

function testConnection() {
    var btn = document.getElementById('btn-test-connection');
    var result = document.getElementById('test-result');

    btn.disabled = true;
    btn.textContent = '테스트 중...';
    result.textContent = '';
    result.style.color = '';

    var formData = new FormData(document.getElementById('fstorageconfig'));

    fetch('./storage_config_test.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            result.textContent = data.message;
            result.style.color = '#22c55e';
        } else {
            result.textContent = data.message;
            result.style.color = '#ef4444';
        }
    })
    .catch(function(e) {
        result.textContent = '요청 실패: ' + e.message;
        result.style.color = '#ef4444';
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = '연결 테스트';
    });
}

// 초기 상태
toggleR2Fields();
</script>

<?php
require_once __DIR__.'/_tail.php';
