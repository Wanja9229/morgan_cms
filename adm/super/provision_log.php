<?php
/**
 * Morgan Super Admin — 프로비저닝 로그
 */

include_once('./_common.php');
sa_check_auth();

$sa_page_title = '프로비저닝 로그';

// 필터
$tenant_filter = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : 0;
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 30;
$offset = ($page - 1) * $per_page;

// WHERE 조건
$where = '1=1';
if ($tenant_filter > 0) {
    $where .= " AND pl.tenant_id = {$tenant_filter}";
}

// 총 수
$count_result = sa_query("SELECT COUNT(*) AS cnt FROM provision_log pl WHERE {$where}");
$total = (int)sa_fetch($count_result)['cnt'];
$total_pages = max(1, ceil($total / $per_page));

// 로그 목록
$logs = sa_fetch_all(sa_query(
    "SELECT pl.*, t.subdomain, t.name AS tenant_name, sa.username AS admin_name
     FROM provision_log pl
     LEFT JOIN tenants t ON pl.tenant_id = t.id
     LEFT JOIN super_admins sa ON pl.admin_id = sa.id
     WHERE {$where}
     ORDER BY pl.id DESC
     LIMIT {$per_page} OFFSET {$offset}"
));

// 테넌트 필터용 목록
$tenant_list = sa_fetch_all(sa_query("SELECT id, subdomain FROM tenants ORDER BY subdomain"));

include_once('./_head.php');
?>

<!-- 필터 -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:center">
    <form method="get" style="display:flex;gap:0.5rem;align-items:center">
        <select name="tenant_id" class="sa-form-select" style="width:auto;min-width:160px" onchange="this.form.submit()">
            <option value="0">전체 테넌트</option>
            <?php foreach ($tenant_list as $tl): ?>
            <option value="<?php echo $tl['id']; ?>" <?php echo $tenant_filter == $tl['id'] ? 'selected' : ''; ?>><?php echo sa_h($tl['subdomain']); ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="sa-card">
    <div class="sa-card-body" style="padding:0">
        <?php if ($logs): ?>
        <table class="sa-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>시간</th>
                    <th>테넌트</th>
                    <th>액션</th>
                    <th>상세</th>
                    <th>관리자</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo $log['id']; ?></td>
                    <td style="white-space:nowrap;font-size:0.8125rem"><?php echo sa_h(substr($log['created_at'], 0, 16)); ?></td>
                    <td>
                        <?php if ($log['subdomain']): ?>
                        <a href="?tenant_id=<?php echo $log['tenant_id']; ?>"><?php echo sa_h($log['subdomain']); ?></a>
                        <?php else: ?>
                        #<?php echo $log['tenant_id']; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $action_styles = array(
                            'create'   => 'sa-badge-success',
                            'activate' => 'sa-badge-success',
                            'suspend'  => 'sa-badge-warning',
                            'delete'   => 'sa-badge-error',
                            'backup'   => 'sa-badge-info',
                            'restore'  => 'sa-badge-info',
                        );
                        $action_labels = array(
                            'create'   => '생성',
                            'activate' => '활성화',
                            'suspend'  => '정지',
                            'delete'   => '삭제',
                            'backup'   => '백업',
                            'restore'  => '복원',
                        );
                        $cls = $action_styles[$log['action']] ?? 'sa-badge-info';
                        $lbl = $action_labels[$log['action']] ?? $log['action'];
                        ?>
                        <span class="sa-badge <?php echo $cls; ?>"><?php echo sa_h($lbl); ?></span>
                    </td>
                    <td style="max-width:250px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.8125rem">
                        <?php echo sa_h($log['detail'] ?? ''); ?>
                    </td>
                    <td><?php echo sa_h($log['admin_name'] ?? '-'); ?></td>
                    <td style="font-size:0.75rem;color:var(--mg-text-muted)"><?php echo sa_h($log['ip_address'] ?? ''); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total_pages > 1): ?>
        <div class="sa-pagination">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <?php if ($p == $page): ?>
                    <span class="active"><?php echo $p; ?></span>
                <?php else: ?>
                    <a href="?tenant_id=<?php echo $tenant_filter; ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <p style="padding:2rem;text-align:center;color:var(--mg-text-muted)">로그가 없습니다.</p>
        <?php endif; ?>
    </div>
</div>

<?php include_once('./_tail.php'); ?>
