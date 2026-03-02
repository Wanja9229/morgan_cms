<?php
/**
 * Morgan Super Admin — 테넌트 목록
 */

include_once('./_common.php');
sa_check_auth();

$sa_page_title = '테넌트 관리';

// 필터
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

// WHERE 조건
$where = "status != 'deleted'";
if ($status_filter === 'active') $where = "status = 'active'";
elseif ($status_filter === 'suspended') $where = "status = 'suspended'";

// 총 수
$count_result = sa_query("SELECT COUNT(*) AS cnt FROM tenants WHERE {$where}");
$total = (int)sa_fetch($count_result)['cnt'];
$total_pages = max(1, ceil($total / $per_page));

// 목록
$tenants = sa_fetch_all(sa_query(
    "SELECT * FROM tenants WHERE {$where} ORDER BY id DESC LIMIT {$per_page} OFFSET {$offset}"
));

include_once('./_head.php');
?>

<!-- 필터 탭 -->
<div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;flex-wrap:wrap;align-items:center">
    <a href="?status=all" class="sa-btn sa-btn-sm <?php echo $status_filter === 'all' ? 'sa-btn-primary' : 'sa-btn-secondary'; ?>">전체 (<?php
        $r = sa_fetch(sa_query("SELECT COUNT(*) AS c FROM tenants WHERE status != 'deleted'")); echo $r['c'];
    ?>)</a>
    <a href="?status=active" class="sa-btn sa-btn-sm <?php echo $status_filter === 'active' ? 'sa-btn-primary' : 'sa-btn-secondary'; ?>">활성</a>
    <a href="?status=suspended" class="sa-btn sa-btn-sm <?php echo $status_filter === 'suspended' ? 'sa-btn-primary' : 'sa-btn-secondary'; ?>">정지</a>

    <div style="flex:1"></div>
    <a href="<?php echo sa_url('tenant_form.php'); ?>" class="sa-btn sa-btn-primary sa-btn-sm">+ 테넌트 생성</a>
</div>

<div class="sa-card">
    <div class="sa-card-body" style="padding:0">
        <?php if ($tenants): ?>
        <table class="sa-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>서브도메인</th>
                    <th>이름</th>
                    <th>상태</th>
                    <th>플랜</th>
                    <th>DB</th>
                    <th>생성일</th>
                    <th>액션</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenants as $t): ?>
                <tr>
                    <td><?php echo $t['id']; ?></td>
                    <td>
                        <a href="<?php echo sa_url('tenant_form.php?id=' . $t['id']); ?>" style="font-weight:500">
                            <?php echo sa_h($t['subdomain']); ?>
                        </a>
                    </td>
                    <td><?php echo sa_h($t['name']); ?></td>
                    <td>
                        <?php
                        $badge = $t['status'] === 'active' ? 'success' : ($t['status'] === 'suspended' ? 'warning' : 'error');
                        $label = $t['status'] === 'active' ? '활성' : ($t['status'] === 'suspended' ? '정지' : '삭제');
                        ?>
                        <span class="sa-badge sa-badge-<?php echo $badge; ?>"><?php echo $label; ?></span>
                    </td>
                    <td><?php echo sa_h($t['plan']); ?></td>
                    <td style="font-size:0.75rem;color:var(--mg-text-muted)"><?php echo sa_h($t['db_name']); ?></td>
                    <td><?php echo sa_h(substr($t['created_at'], 0, 10)); ?></td>
                    <td>
                        <a href="<?php echo sa_url('tenant_form.php?id=' . $t['id']); ?>" class="sa-btn sa-btn-sm sa-btn-secondary">편집</a>
                        <?php if ($t['status'] === 'active'): ?>
                        <form method="post" action="<?php echo sa_url('tenant_update.php'); ?>" style="display:inline" onsubmit="return confirm('정말 정지하시겠습니까?')">
                            <?php echo sa_token_field(); ?>
                            <input type="hidden" name="action" value="suspend">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-danger">정지</button>
                        </form>
                        <?php elseif ($t['status'] === 'suspended'): ?>
                        <form method="post" action="<?php echo sa_url('tenant_update.php'); ?>" style="display:inline">
                            <?php echo sa_token_field(); ?>
                            <input type="hidden" name="action" value="activate">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <button type="submit" class="sa-btn sa-btn-sm sa-btn-success">활성화</button>
                        </form>
                        <?php endif; ?>
                    </td>
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
                    <a href="?status=<?php echo sa_h($status_filter); ?>&page=<?php echo $p; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <p style="padding:2rem;text-align:center;color:var(--mg-text-muted)">
            테넌트가 없습니다.
            <a href="<?php echo sa_url('tenant_form.php'); ?>">첫 번째 테넌트 생성하기</a>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php include_once('./_tail.php'); ?>
