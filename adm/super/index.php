<?php
/**
 * Morgan Super Admin — 대시보드
 */

include_once('./_common.php');
sa_check_auth();

$sa_page_title = '대시보드';

// 통계 쿼리
$stats = array('total' => 0, 'active' => 0, 'suspended' => 0);
$result = sa_query("SELECT status, COUNT(*) AS cnt FROM tenants WHERE status != 'deleted' GROUP BY status");
while ($row = sa_fetch($result)) {
    $stats[$row['status']] = (int)$row['cnt'];
    $stats['total'] += (int)$row['cnt'];
}

// 최근 생성 테넌트 5개
$recent_tenants = sa_fetch_all(sa_query(
    "SELECT id, subdomain, name, status, plan, created_at FROM tenants WHERE status != 'deleted' ORDER BY id DESC LIMIT 5"
));

// 최근 프로비저닝 로그 10개
$recent_logs = sa_fetch_all(sa_query(
    "SELECT pl.*, t.subdomain, sa.username AS admin_name
     FROM provision_log pl
     LEFT JOIN tenants t ON pl.tenant_id = t.id
     LEFT JOIN super_admins sa ON pl.admin_id = sa.id
     ORDER BY pl.id DESC LIMIT 10"
));

include_once('./_head.php');
?>

<div class="sa-stats-grid">
    <div class="sa-stat-card">
        <div class="sa-stat-label">전체 테넌트</div>
        <div class="sa-stat-value"><?php echo $stats['total']; ?></div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-label">활성</div>
        <div class="sa-stat-value" style="color:var(--mg-success)"><?php echo $stats['active']; ?></div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-label">정지</div>
        <div class="sa-stat-value" style="color:var(--mg-warning)"><?php echo $stats['suspended']; ?></div>
    </div>
    <div class="sa-stat-card">
        <div class="sa-stat-label">슈퍼 관리자</div>
        <div class="sa-stat-value"><?php echo sa_h($_sa_admin['username']); ?></div>
    </div>
</div>

<!-- 최근 테넌트 -->
<div class="sa-card">
    <div class="sa-card-header" style="display:flex;justify-content:space-between;align-items:center">
        최근 생성 테넌트
        <a href="<?php echo sa_url('tenants.php'); ?>" class="sa-btn sa-btn-sm sa-btn-secondary">전체 보기</a>
    </div>
    <div class="sa-card-body" style="padding:0">
        <?php if ($recent_tenants): ?>
        <table class="sa-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>서브도메인</th>
                    <th>이름</th>
                    <th>상태</th>
                    <th>플랜</th>
                    <th>생성일</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_tenants as $t): ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td><a href="<?php echo sa_url('tenant_form.php?id=' . $t['id']); ?>"><?php echo sa_h($t['subdomain']); ?></a></td>
                    <td><?php echo sa_h($t['name']); ?></td>
                    <td>
                        <?php
                        $badge = $t['status'] === 'active' ? 'success' : ($t['status'] === 'suspended' ? 'warning' : 'error');
                        $label = $t['status'] === 'active' ? '활성' : ($t['status'] === 'suspended' ? '정지' : '삭제');
                        ?>
                        <span class="sa-badge sa-badge-<?php echo $badge; ?>"><?php echo $label; ?></span>
                    </td>
                    <td><?php echo sa_h($t['plan']); ?></td>
                    <td><?php echo sa_h(substr($t['created_at'], 0, 10)); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="padding:2rem;text-align:center;color:var(--mg-text-muted)">
            아직 생성된 테넌트가 없습니다.
            <a href="<?php echo sa_url('tenant_form.php'); ?>">첫 번째 테넌트 생성하기</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<!-- 최근 로그 -->
<div class="sa-card">
    <div class="sa-card-header" style="display:flex;justify-content:space-between;align-items:center">
        최근 프로비저닝 로그
        <a href="<?php echo sa_url('provision_log.php'); ?>" class="sa-btn sa-btn-sm sa-btn-secondary">전체 보기</a>
    </div>
    <div class="sa-card-body" style="padding:0">
        <?php if ($recent_logs): ?>
        <table class="sa-table">
            <thead>
                <tr>
                    <th>시간</th>
                    <th>테넌트</th>
                    <th>액션</th>
                    <th>상세</th>
                    <th>관리자</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_logs as $log): ?>
                <tr>
                    <td style="white-space:nowrap"><?php echo sa_h(substr($log['created_at'], 0, 16)); ?></td>
                    <td><?php echo sa_h($log['subdomain'] ?? '-'); ?></td>
                    <td>
                        <?php
                        $action_map = array('create'=>'생성','suspend'=>'정지','activate'=>'활성화','delete'=>'삭제','backup'=>'백업','restore'=>'복원');
                        echo sa_h($action_map[$log['action']] ?? $log['action']);
                        ?>
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo sa_h($log['detail'] ?? ''); ?></td>
                    <td><?php echo sa_h($log['admin_name'] ?? '-'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="padding:2rem;text-align:center;color:var(--mg-text-muted)">로그가 없습니다.</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once('./_tail.php'); ?>
