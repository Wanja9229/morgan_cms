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
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 30;
$offset = ($page - 1) * $per_page;

$where = "1";
if ($status_filter) {
    $where .= " AND r.cr_status = '".sql_real_escape_string($status_filter)."'";
}
if ($category_filter) {
    $where .= " AND ri.ri_category = '".sql_real_escape_string($category_filter)."'";
}
if ($search) {
    $s = sql_real_escape_string($search);
    $where .= " AND (ca.ch_name LIKE '%{$s}%' OR cb.ch_name LIKE '%{$s}%' OR r.cr_label_a LIKE '%{$s}%' OR r.cr_label_b LIKE '%{$s}%')";
}

// 총 수
$cnt_sql = "SELECT COUNT(*) AS cnt
    FROM {$g5['mg_relation_table']} r
    JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
    JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
    JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
    WHERE {$where}";
$total = sql_fetch($cnt_sql);
$total_count = $total['cnt'];
$total_pages = ceil($total_count / $per_page);

// 목록
$sql = "SELECT r.*, ri.ri_icon, ri.ri_label, ri.ri_color, ri.ri_category,
               ca.ch_name AS name_a, cb.ch_name AS name_b
        FROM {$g5['mg_relation_table']} r
        JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
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

// 통계
$stats = array();
$stat_result = sql_query("SELECT ri.ri_category, COUNT(*) AS cnt
    FROM {$g5['mg_relation_table']} r
    JOIN {$g5['mg_relation_icon_table']} ri ON r.ri_id = ri.ri_id
    WHERE r.cr_status = 'active'
    GROUP BY ri.ri_category ORDER BY cnt DESC");
while ($row = sql_fetch_array($stat_result)) {
    $stats[$row['ri_category']] = $row['cnt'];
}

include_once('./_head.php');
?>

<div class="local_desc01 local_desc">
    <span class="title01">관계 관리</span>
    <span class="title02">캐릭터 간 관계를 관리합니다. 총 <?php echo $total_count; ?>건</span>
</div>

<style>
.ri-table { width:100%; border-collapse:collapse; }
.ri-table th, .ri-table td { padding:8px 12px; border:1px solid #444; text-align:left; font-size:13px; }
.ri-table th { background:#2a2a2a; color:#ccc; }
.ri-table td { background:#1a1a1a; color:#e0e0e0; }
.ri-table tr:hover td { background:#222; }
.ri-btn { padding:4px 10px; border-radius:4px; font-size:12px; cursor:pointer; border:none; }
.ri-btn-primary { background:#8a0000; color:white; }
.ri-btn-danger { background:#444; color:#e74c3c; }
.ri-input { background:#111; border:1px solid #444; color:#e0e0e0; padding:4px 8px; border-radius:4px; font-size:13px; }
.stat-box { display:inline-block; padding:8px 16px; background:#222; border:1px solid #444; border-radius:6px; margin:0 8px 8px 0; font-size:13px; color:#ccc; }
.stat-box strong { color:#e0e0e0; }
.status-badge { padding:2px 8px; border-radius:10px; font-size:11px; }
.status-pending { background:#f39c12; color:#000; }
.status-active { background:#27ae60; color:#fff; }
.status-rejected { background:#e74c3c; color:#fff; }
.pager { margin:20px 0; text-align:center; }
.pager a, .pager span { display:inline-block; padding:4px 10px; margin:0 2px; border:1px solid #444; border-radius:4px; color:#ccc; text-decoration:none; font-size:13px; }
.pager span { background:#8a0000; color:#fff; border-color:#8a0000; }
.pager a:hover { background:#333; }
</style>

<!-- 통계 -->
<div style="margin:16px 0;">
    <?php
    $cat_labels = array('love' => '애정', 'friendship' => '우정', 'family' => '가족', 'rival' => '적대', 'mentor' => '사제', 'etc' => '기타');
    foreach ($stats as $cat => $cnt) {
        $label = $cat_labels[$cat] ?? $cat;
    ?>
    <div class="stat-box"><strong><?php echo $label; ?></strong>: <?php echo $cnt; ?>건</div>
    <?php } ?>
</div>

<!-- 필터 -->
<form method="get" style="margin:16px 0; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
    <select name="status" class="ri-input">
        <option value="">전체 상태</option>
        <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>대기중</option>
        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>활성</option>
        <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>거절</option>
    </select>
    <select name="category" class="ri-input">
        <option value="">전체 카테고리</option>
        <?php foreach ($cat_labels as $cat => $label) { ?>
        <option value="<?php echo $cat; ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>><?php echo $label; ?></option>
        <?php } ?>
    </select>
    <input type="text" name="q" class="ri-input" value="<?php echo htmlspecialchars($search); ?>" placeholder="캐릭터명/관계명 검색" style="width:200px;">
    <button type="submit" class="ri-btn ri-btn-primary">검색</button>
    <?php if ($status_filter || $category_filter || $search) { ?>
    <a href="?" style="color:#999; font-size:12px;">초기화</a>
    <?php } ?>
</form>

<table class="ri-table">
    <thead>
        <tr>
            <th style="width:50px">ID</th>
            <th>캐릭터 A</th>
            <th style="width:50px; text-align:center;"></th>
            <th>캐릭터 B</th>
            <th>아이콘</th>
            <th>관계명 A</th>
            <th>관계명 B</th>
            <th style="width:80px">상태</th>
            <th style="width:120px">신청일</th>
            <th style="width:120px">관리</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($relations)) { ?>
        <tr><td colspan="10" style="text-align:center; color:#666; padding:30px;">관계가 없습니다.</td></tr>
        <?php } ?>
        <?php foreach ($relations as $rel) { ?>
        <tr id="rel-<?php echo $rel['cr_id']; ?>">
            <td><?php echo $rel['cr_id']; ?></td>
            <td><?php echo htmlspecialchars($rel['name_a']); ?></td>
            <td style="text-align:center; font-size:18px;"><?php echo $rel['ri_icon']; ?></td>
            <td><?php echo htmlspecialchars($rel['name_b']); ?></td>
            <td><span style="color:<?php echo $rel['ri_color']; ?>"><?php echo htmlspecialchars($rel['ri_label']); ?></span></td>
            <td><?php echo htmlspecialchars($rel['cr_label_a']); ?></td>
            <td><?php echo htmlspecialchars($rel['cr_label_b'] ?: '-'); ?></td>
            <td>
                <?php
                $status_class = 'status-' . $rel['cr_status'];
                $status_text = array('pending' => '대기', 'active' => '활성', 'rejected' => '거절');
                ?>
                <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text[$rel['cr_status']] ?? $rel['cr_status']; ?></span>
            </td>
            <td><?php echo date('Y-m-d H:i', strtotime($rel['cr_datetime'])); ?></td>
            <td>
                <?php if ($rel['cr_status'] === 'pending') { ?>
                <button class="ri-btn ri-btn-primary" onclick="adminAction(<?php echo $rel['cr_id']; ?>, 'force_approve')">승인</button>
                <?php } ?>
                <button class="ri-btn ri-btn-danger" onclick="adminAction(<?php echo $rel['cr_id']; ?>, 'force_delete')">삭제</button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- 페이지네이션 -->
<?php if ($total_pages > 1) { ?>
<div class="pager">
    <?php
    $base_url = '?' . http_build_query(array_filter(array('status' => $status_filter, 'category' => $category_filter, 'q' => $search)));
    $base_url .= ($status_filter || $category_filter || $search) ? '&' : '';
    for ($i = 1; $i <= $total_pages; $i++) {
        if ($i == $page) {
            echo '<span>'.$i.'</span>';
        } else {
            echo '<a href="'.$base_url.'page='.$i.'">'.$i.'</a>';
        }
    }
    ?>
</div>
<?php } ?>

<script>
function adminAction(crId, action) {
    const msg = action === 'force_delete' ? '이 관계를 삭제하시겠습니까?' : '이 관계를 강제 승인하시겠습니까?';
    if (!confirm(msg)) return;

    const data = new FormData();
    data.append('rel_action', action);
    data.append('cr_id', crId);

    fetch(location.href, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.success) location.reload();
        });
}
</script>

<?php
include_once('./_tail.php');
?>
