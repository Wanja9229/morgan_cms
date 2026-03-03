<?php
/**
 * Morgan Super Admin — 테넌트 생성/편집 폼
 */

include_once('./_common.php');
sa_check_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tenant = null;
$is_edit = false;

if ($id > 0) {
    $result = sa_query("SELECT * FROM tenants WHERE id = {$id}");
    $tenant = sa_fetch($result);
    if (!$tenant) {
        sa_alert('테넌트를 찾을 수 없습니다.', sa_url('tenants.php'));
    }
    $is_edit = true;
    $sa_page_title = '테넌트 편집: ' . $tenant['subdomain'];
} else {
    $sa_page_title = '테넌트 생성';
}

include_once('./_head.php');
?>

<div class="sa-card">
    <div class="sa-card-header"><?php echo $is_edit ? '테넌트 편집' : '새 테넌트 생성'; ?></div>
    <div class="sa-card-body">
        <form method="post" action="<?php echo sa_url('tenant_update.php'); ?>">
            <?php echo sa_token_field(); ?>
            <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
            <?php if ($is_edit): ?>
            <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
            <?php endif; ?>

            <?php if ($is_edit):
                $tenant_url = '';
                if (!empty($tenant['subdomain']) && defined('MG_TENANT_BASE_DOMAIN')) {
                    $tenant_url = 'https://' . $tenant['subdomain'] . '.' . MG_TENANT_BASE_DOMAIN;
                }
            ?>
            <!-- 편집 모드: 서브도메인 읽기 전용 -->
            <div class="sa-form-group">
                <label class="sa-form-label">서브도메인</label>
                <input type="text" class="sa-form-input" value="<?php echo sa_h($tenant['subdomain']); ?>" disabled style="opacity:0.6;max-width:300px">
                <?php if ($tenant_url): ?>
                <div style="margin-top:0.375rem;display:flex;align-items:center;gap:0.5rem">
                    <a href="<?php echo sa_h($tenant_url); ?>" target="_blank" style="color:var(--mg-accent);font-size:0.875rem"><?php echo sa_h($tenant_url); ?></a>
                    <button type="button" onclick="navigator.clipboard.writeText('<?php echo sa_h($tenant_url); ?>');this.textContent='복사됨!';setTimeout(()=>this.textContent='복사',1500)" style="padding:0.125rem 0.5rem;background:var(--mg-bg-tertiary);border:1px solid #4a4d55;border-radius:0.25rem;color:var(--mg-text-secondary);font-size:0.75rem;cursor:pointer">복사</button>
                </div>
                <?php endif; ?>
                <div class="sa-form-help">서브도메인은 변경할 수 없습니다.</div>
            </div>
            <?php else: ?>
            <!-- 생성 모드: 서브도메인 입력 -->
            <div class="sa-form-group">
                <label class="sa-form-label" for="subdomain">서브도메인 *</label>
                <input type="text" id="subdomain" name="subdomain" class="sa-form-input"
                       placeholder="alpha" pattern="[a-z0-9][a-z0-9-]{0,61}[a-z0-9]?" required
                       style="max-width:300px">
                <div class="sa-form-help">영소문자, 숫자, 하이픈만 사용 가능 (1~63자). 예: alpha, my-community</div>
            </div>
            <?php endif; ?>

            <div class="sa-form-group">
                <label class="sa-form-label" for="name">커뮤니티 이름 *</label>
                <input type="text" id="name" name="name" class="sa-form-input"
                       value="<?php echo sa_h($is_edit ? $tenant['name'] : ''); ?>"
                       placeholder="나의 커뮤니티" required style="max-width:400px">
            </div>

            <div class="sa-form-group">
                <label class="sa-form-label" for="admin_email">관리자 이메일 *</label>
                <input type="email" id="admin_email" name="admin_email" class="sa-form-input"
                       value="<?php echo sa_h($is_edit ? $tenant['admin_email'] : ''); ?>"
                       placeholder="admin@example.com" required style="max-width:400px">
            </div>

            <?php if (!$is_edit): ?>
            <div class="sa-form-group">
                <label class="sa-form-label" for="admin_id">관리자 아이디</label>
                <input type="text" id="admin_id" name="admin_id" class="sa-form-input"
                       value="admin" placeholder="admin" style="max-width:200px">
                <div class="sa-form-help">비밀번호는 자동 생성됩니다.</div>
            </div>
            <?php endif; ?>

            <div class="sa-form-group">
                <label class="sa-form-label" for="plan">플랜</label>
                <select id="plan" name="plan" class="sa-form-select" style="max-width:200px">
                    <?php
                    $current_plan = $is_edit ? $tenant['plan'] : 'free';
                    foreach (array('free' => 'Free', 'basic' => 'Basic', 'pro' => 'Pro') as $val => $label):
                    ?>
                    <option value="<?php echo $val; ?>" <?php echo $current_plan === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($is_edit): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;max-width:400px">
                <div class="sa-form-group">
                    <label class="sa-form-label" for="max_storage_mb">저장 용량 (MB)</label>
                    <input type="number" id="max_storage_mb" name="max_storage_mb" class="sa-form-input"
                           value="<?php echo (int)$tenant['max_storage_mb']; ?>" min="100">
                </div>
                <div class="sa-form-group">
                    <label class="sa-form-label" for="max_members">회원 수 상한</label>
                    <input type="number" id="max_members" name="max_members" class="sa-form-input"
                           value="<?php echo (int)$tenant['max_members']; ?>" min="10">
                </div>
            </div>

            <!-- DB 정보 (읽기 전용) -->
            <div class="sa-card" style="background:var(--mg-bg-primary);margin-top:1.5rem">
                <div class="sa-card-header" style="font-size:0.8125rem;color:var(--mg-text-muted)">DB 정보 (읽기 전용)</div>
                <div class="sa-card-body" style="font-size:0.8125rem;color:var(--mg-text-secondary)">
                    <div>DB: <?php echo sa_h($tenant['db_name']); ?></div>
                    <div>User: <?php echo sa_h($tenant['db_user']); ?></div>
                    <div>Host: <?php echo sa_h($tenant['db_host'] ?: '(마스터와 동일)'); ?></div>
                    <div style="margin-top:0.5rem">생성일: <?php echo sa_h($tenant['created_at']); ?></div>
                    <div>수정일: <?php echo sa_h($tenant['updated_at']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <div style="margin-top:1.5rem;display:flex;gap:0.5rem">
                <button type="submit" class="sa-btn sa-btn-primary">
                    <?php echo $is_edit ? '저장' : '테넌트 생성'; ?>
                </button>
                <a href="<?php echo sa_url('tenants.php'); ?>" class="sa-btn sa-btn-secondary">취소</a>

                <?php if ($is_edit && $tenant['status'] !== 'deleted'): ?>
                <div style="flex:1"></div>
                <form method="post" action="<?php echo sa_url('tenant_update.php'); ?>" style="display:inline"
                      onsubmit="return confirm('정말 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')">
                    <?php echo sa_token_field(); ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $tenant['id']; ?>">
                    <button type="submit" class="sa-btn sa-btn-danger">삭제</button>
                </form>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php include_once('./_tail.php'); ?>
