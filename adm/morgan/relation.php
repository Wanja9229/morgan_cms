<?php
/**
 * Morgan Edition - 관리자 관계 관리
 */
$sub_menu = '801700';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// AJAX: 관계 액션
if (isset($_POST['rel_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    auth_check_menu($auth, $sub_menu, 'w');

    $action = $_POST['rel_action'];
    $cr_id = (int)($_POST['cr_id'] ?? 0);

    if (!$cr_id) {
        echo json_encode(array('success' => false, 'message' => '관계 ID가 없습니다.'));
        exit;
    }

    switch ($action) {
        case 'force_approve':
            sql_query("UPDATE {$g5['mg_relation_table']} SET cr_status = 'active', cr_accept_datetime = NOW() WHERE cr_id = {$cr_id}");
            echo json_encode(array('success' => true, 'message' => '관계를 강제 승인했습니다.'));
            break;
        case 'force_delete':
            sql_query("DELETE FROM {$g5['mg_relation_table']} WHERE cr_id = {$cr_id}");
            echo json_encode(array('success' => true, 'message' => '관계를 삭제했습니다.'));
            break;
        default:
            echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
    }
    exit;
}

// 필터
$status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 30;
$offset = ($page - 1) * $per_page;

$where = "1";
if ($status_filter) {
    $where .= " AND r.cr_status = '".sql_real_escape_string($status_filter)."'";
}
if ($search) {
    $s = sql_real_escape_string($search);
    $where .= " AND (ca.ch_name LIKE '%{$s}%' OR cb.ch_name LIKE '%{$s}%' OR r.cr_label_a LIKE '%{$s}%' OR r.cr_label_b LIKE '%{$s}%')";
}

// 총 수
$cnt_sql = "SELECT COUNT(*) AS cnt
    FROM {$g5['mg_relation_table']} r
    JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
    JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
    WHERE {$where}";
$total = sql_fetch($cnt_sql);
$total_count = (int)$total['cnt'];
$total_pages = max(1, ceil($total_count / $per_page));

// 목록
$sql = "SELECT r.*,
               ca.ch_name AS name_a, cb.ch_name AS name_b
        FROM {$g5['mg_relation_table']} r
        JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
        JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
        WHERE {$where}
        ORDER BY r.cr_id DESC
        LIMIT {$offset}, {$per_page}";
$result = sql_query($sql);
$relations = array();
while ($row = sql_fetch_array($result)) {
    $relations[] = $row;
}

// 상태별 통계
$stat_pending = (int)sql_fetch("SELECT COUNT(*) AS cnt FROM {$g5['mg_relation_table']} WHERE cr_status = 'pending'")['cnt'];
$stat_active = (int)sql_fetch("SELECT COUNT(*) AS cnt FROM {$g5['mg_relation_table']} WHERE cr_status = 'active'")['cnt'];
$stat_rejected = (int)sql_fetch("SELECT COUNT(*) AS cnt FROM {$g5['mg_relation_table']} WHERE cr_status = 'rejected'")['cnt'];

include_once('./_head.php');
?>

<div class="mg-card">
    <div class="mg-card-header">
        <h2>관계 관리</h2>
        <span class="mg-card-desc">캐릭터 간 관계를 관리합니다. 총 <?php echo $total_count; ?>건</span>
    </div>
    <div class="mg-card-body">

        <!-- 통계 -->
        <div class="mg-stats-grid" style="margin-bottom:16px;">
            <div class="mg-stat-card">
                <div class="mg-stat-label">대기중</div>
                <div class="mg-stat-value"><?php echo $stat_pending; ?></div>
            </div>
            <div class="mg-stat-card">
                <div class="mg-stat-label">활성</div>
                <div class="mg-stat-value"><?php echo $stat_active; ?></div>
            </div>
            <div class="mg-stat-card">
                <div class="mg-stat-label">거절</div>
                <div class="mg-stat-value"><?php echo $stat_rejected; ?></div>
            </div>
        </div>

        <!-- 필터 -->
        <form method="get" style="margin-bottom:16px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <select name="status" class="mg-form-select">
                <option value="">전체 상태</option>
                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>대기중</option>
                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>활성</option>
                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>거절</option>
            </select>
            <input type="text" name="q" class="mg-form-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="캐릭터명/관계명 검색" style="width:200px;">
            <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">검색</button>
            <?php if ($status_filter || $search) { ?>
            <a href="?" class="mg-btn mg-btn-secondary mg-btn-sm">초기화</a>
            <?php } ?>
        </form>

        <!-- 테이블 -->
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:50px">ID</th>
                    <th>캐릭터 A</th>
                    <th style="width:60px">색상</th>
                    <th>캐릭터 B</th>
                    <th>관계명 A</th>
                    <th>관계명 B</th>
                    <th style="width:70px">상태</th>
                    <th style="width:110px">신청일</th>
                    <th style="width:120px">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($relations)) { ?>
                <tr><td colspan="9" style="text-align:center; padding:30px;" class="text-mg-text-muted">관계가 없습니다.</td></tr>
                <?php } ?>
                <?php foreach ($relations as $rel) {
                    $display_color = $rel['cr_color'] ?: '#95a5a6';

                    $badge_class = 'mg-badge-secondary';
                    $status_text = $rel['cr_status'];
                    if ($rel['cr_status'] === 'pending') { $badge_class = 'mg-badge-warning'; $status_text = '대기'; }
                    elseif ($rel['cr_status'] === 'active') { $badge_class = 'mg-badge-success'; $status_text = '활성'; }
                    elseif ($rel['cr_status'] === 'rejected') { $badge_class = 'mg-badge-danger'; $status_text = '거절'; }
                ?>
                <tr id="rel-<?php echo $rel['cr_id']; ?>">
                    <td><?php echo $rel['cr_id']; ?></td>
                    <td><?php echo htmlspecialchars($rel['name_a']); ?></td>
                    <td><span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:<?php echo htmlspecialchars($display_color); ?>;vertical-align:middle;"></span></td>
                    <td><?php echo htmlspecialchars($rel['name_b']); ?></td>
                    <td><?php echo htmlspecialchars($rel['cr_label_a'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($rel['cr_label_b'] ?: '-'); ?></td>
                    <td><span class="mg-badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span></td>
                    <td><?php echo date('Y-m-d H:i', strtotime($rel['cr_datetime'])); ?></td>
                    <td>
                        <?php if ($rel['cr_status'] === 'pending') { ?>
                        <button class="mg-btn mg-btn-primary mg-btn-sm" onclick="adminAction(<?php echo $rel['cr_id']; ?>, 'force_approve')">승인</button>
                        <?php } ?>
                        <button class="mg-btn mg-btn-danger mg-btn-sm" onclick="adminAction(<?php echo $rel['cr_id']; ?>, 'force_delete')">삭제</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 페이지네이션 -->
        <?php if ($total_pages > 1) { ?>
        <div class="mg-pagination">
            <?php
            $base_url = '?' . http_build_query(array_filter(array('status' => $status_filter, 'q' => $search)));
            $base_url .= ($status_filter || $search) ? '&' : '';
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo '<span class="active">'.$i.'</span>';
                } else {
                    echo '<a href="'.$base_url.'page='.$i.'">'.$i.'</a>';
                }
            }
            ?>
        </div>
        <?php } ?>

    </div>
</div>

<script>
function adminAction(crId, action) {
    var msg = action === 'force_delete' ? '이 관계를 삭제하시겠습니까?' : '이 관계를 강제 승인하시겠습니까?';
    if (!confirm(msg)) return;

    var data = new FormData();
    data.append('rel_action', action);
    data.append('cr_id', crId);

    fetch(location.href, { method: 'POST', body: data })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            alert(res.message);
            if (res.success) location.reload();
        });
}
</script>

<?php
include_once('./_tail.php');
?>
