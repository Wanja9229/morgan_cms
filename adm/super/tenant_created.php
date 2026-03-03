<?php
/**
 * Morgan Super Admin — 테넌트 생성 완료
 */

include_once('./_common.php');
sa_check_auth();

$sa_page_title = '테넌트 생성 완료';

$result = $_SESSION['sa_provision_result'] ?? null;
$log = $_SESSION['sa_provision_log'] ?? array();

// 세션에서 제거 (새로고침 시 재표시 방지)
unset($_SESSION['sa_provision_result'], $_SESSION['sa_provision_log']);

if (!$result) {
    header('Location: ' . sa_url('tenants.php'));
    exit;
}

include_once('./_head.php');
?>

<div class="sa-card">
    <div class="sa-card-header" style="color:var(--mg-success)">테넌트 프로비저닝 완료</div>
    <div class="sa-card-body">
        <div class="sa-alert sa-alert-success">
            테넌트가 성공적으로 생성되었습니다.
        </div>

        <?php
        $tenant_url = '';
        if (!empty($result['subdomain']) && defined('MG_TENANT_BASE_DOMAIN')) {
            $tenant_url = 'https://' . $result['subdomain'] . '.' . MG_TENANT_BASE_DOMAIN;
        }
        ?>
        <table class="sa-table" style="max-width:500px">
            <tbody>
                <tr>
                    <td style="font-weight:500;width:140px">테넌트 ID</td>
                    <td><?php echo (int)$result['tenant_id']; ?></td>
                </tr>
                <?php if ($tenant_url): ?>
                <tr>
                    <td style="font-weight:500">사이트 URL</td>
                    <td>
                        <a href="<?php echo sa_h($tenant_url); ?>" target="_blank" style="color:var(--mg-accent);word-break:break-all"><?php echo sa_h($tenant_url); ?></a>
                        <button type="button" onclick="navigator.clipboard.writeText('<?php echo sa_h($tenant_url); ?>');this.textContent='복사됨!';setTimeout(()=>this.textContent='복사',1500)" style="margin-left:0.5rem;padding:0.125rem 0.5rem;background:var(--mg-bg-tertiary);border:1px solid #4a4d55;border-radius:0.25rem;color:var(--mg-text-secondary);font-size:0.75rem;cursor:pointer">복사</button>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="font-weight:500">DB</td>
                    <td><code style="background:var(--mg-bg-tertiary);padding:0.125rem 0.375rem;border-radius:0.25rem"><?php echo sa_h($result['db_name']); ?></code></td>
                </tr>
                <tr>
                    <td style="font-weight:500">관리자 아이디</td>
                    <td><strong><?php echo sa_h($result['admin_id']); ?></strong></td>
                </tr>
                <tr>
                    <td style="font-weight:500">관리자 비밀번호</td>
                    <td>
                        <code style="background:rgba(245,159,10,0.15);color:var(--mg-accent);padding:0.25rem 0.5rem;border-radius:0.25rem;font-size:1rem;letter-spacing:0.05em">
                            <?php echo sa_h($result['admin_password']); ?>
                        </code>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="sa-alert sa-alert-info" style="margin-top:1.5rem">
            이 비밀번호를 안전한 곳에 기록해 주세요. 이 페이지를 떠나면 다시 확인할 수 없습니다.
        </div>

        <?php if ($log): ?>
        <details style="margin-top:1.5rem">
            <summary style="cursor:pointer;color:var(--mg-text-muted);font-size:0.8125rem">프로비저닝 로그 (<?php echo count($log); ?>단계)</summary>
            <div style="margin-top:0.5rem;padding:0.75rem;background:var(--mg-bg-primary);border-radius:0.375rem;font-size:0.75rem;font-family:monospace;color:var(--mg-text-secondary);max-height:300px;overflow-y:auto">
                <?php foreach ($log as $line): ?>
                <div><?php echo sa_h($line); ?></div>
                <?php endforeach; ?>
            </div>
        </details>
        <?php endif; ?>

        <div style="margin-top:1.5rem;display:flex;gap:0.5rem">
            <a href="<?php echo sa_url('tenants.php'); ?>" class="sa-btn sa-btn-primary">테넌트 목록</a>
            <a href="<?php echo sa_url('tenant_form.php'); ?>" class="sa-btn sa-btn-secondary">+ 추가 생성</a>
        </div>
    </div>
</div>

<?php include_once('./_tail.php'); ?>
