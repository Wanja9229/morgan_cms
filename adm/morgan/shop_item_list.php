<?php
/**
 * Morgan Edition - 상점 상품 관리
 */

$sub_menu = "800700";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 타입 그룹
$type_groups = $mg['shop_type_groups'];
$type_labels = $mg['shop_type_labels'];

// 검색 조건
$type_group = isset($_GET['type_group']) ? clean_xss_tags($_GET['type_group']) : '';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 20;
$offset = ($page - 1) * $rows;

// 현재 선택된 타입들
$current_types = array();
if ($type_group && isset($type_groups[$type_group])) {
    $current_types = $type_groups[$type_group]['types'];
}

// 검색 쿼리
$where = "WHERE 1=1";
if (!empty($current_types)) {
    $types_escaped = array_map('sql_real_escape_string', $current_types);
    $where .= " AND i.si_type IN ('" . implode("','", $types_escaped) . "')";
}
if ($sfl && $stx) {
    $stx_escaped = sql_real_escape_string($stx);
    if ($sfl == 'si_name') {
        $where .= " AND i.si_name LIKE '%{$stx_escaped}%'";
    } else if ($sfl == 'si_type') {
        $where .= " AND i.si_type = '{$stx_escaped}'";
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_shop_item_table']} i $where";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$total_page = ceil($total_count / $rows);

// 목록 조회
$sql = "SELECT i.*
        FROM {$g5['mg_shop_item_table']} i
        $where
        ORDER BY i.si_order, i.si_id DESC
        LIMIT $offset, $rows";
$result = sql_query($sql);

$g5['title'] = '상점 상품 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 상품</div>
        <div class="mg-stat-value"><?php echo number_format($total_count); ?></div>
    </div>
</div>

<!-- 타입 탭 -->
<div class="mg-tabs" style="margin-bottom:1rem;">
    <a href="./shop_item_list.php" class="mg-tab <?php echo !$type_group ? 'active' : ''; ?>">전체</a>
    <?php foreach ($type_groups as $key => $group) { ?>
    <a href="./shop_item_list.php?type_group=<?php echo $key; ?>" class="mg-tab <?php echo $type_group == $key ? 'active' : ''; ?>">
        <?php echo mg_icon($group['icon'], 'w-4 h-4'); ?>
        <?php echo $group['label']; ?>
    </a>
    <?php } ?>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="type_group" value="<?php echo htmlspecialchars($type_group); ?>">
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="si_name" <?php echo $sfl == 'si_name' ? 'selected' : ''; ?>>상품명</option>
                <option value="si_type" <?php echo $sfl == 'si_type' ? 'selected' : ''; ?>>타입</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="검색어 입력">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <a href="./shop_item_form.php" class="mg-btn mg-btn-success" style="margin-left:auto;">상품 등록</a>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <form name="fitemlist" id="fitemlist" method="post" action="./shop_item_update.php">
            <input type="hidden" name="token" value="">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="type_group" value="<?php echo htmlspecialchars($type_group); ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
            <input type="hidden" name="stx" value="<?php echo htmlspecialchars($stx); ?>">

            <table class="mg-table" style="min-width:900px;">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" onclick="checkAll(this);"></th>
                        <th style="width:60px;">이미지</th>
                        <th>상품명</th>
                        <th style="width:120px;">타입</th>
                        <th style="width:100px;">가격</th>
                        <th style="width:80px;">재고</th>
                        <th style="width:60px;">노출</th>
                        <th style="width:60px;">사용</th>
                        <th style="width:80px;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = sql_fetch_array($result)) {
                        $status = mg_get_item_status($row);
                        $status_badge = '';
                        switch ($status) {
                            case 'coming_soon':
                                $status_badge = '<span class="mg-badge mg-badge-info">예정</span>';
                                break;
                            case 'sold_out':
                                $status_badge = '<span class="mg-badge mg-badge-danger">품절</span>';
                                break;
                            case 'ended':
                                $status_badge = '<span class="mg-badge mg-badge-secondary">종료</span>';
                                break;
                        }

                        $stock_display = $row['si_stock'] == -1 ? '무제한' : $row['si_stock'] - $row['si_stock_sold'];
                    ?>
                    <tr>
                        <td><input type="checkbox" name="chk[]" value="<?php echo $row['si_id']; ?>"></td>
                        <td>
                            <?php if ($row['si_image']) { ?>
                            <img src="<?php echo $row['si_image']; ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                            <?php } else { ?>
                            <div style="width:40px;height:40px;background:var(--mg-bg-tertiary);border-radius:4px;"></div>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="./shop_item_form.php?si_id=<?php echo $row['si_id']; ?>" style="color:var(--mg-text-primary);">
                                <?php echo htmlspecialchars($row['si_name']); ?>
                            </a>
                            <?php echo $status_badge; ?>
                        </td>
                        <td>
                            <span class="mg-badge"><?php echo $type_labels[$row['si_type']] ?? $row['si_type']; ?></span>
                        </td>
                        <td style="text-align:right;"><?php echo number_format($row['si_price']); ?>P</td>
                        <td style="text-align:center;">
                            <?php echo $stock_display; ?>
                            <?php if ($row['si_stock'] > 0) { ?>
                            <span style="color:var(--mg-text-muted);font-size:0.75rem;">(<?php echo $row['si_stock_sold']; ?>)</span>
                            <?php } ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($row['si_display']) { ?>
                            <span style="color:var(--mg-success);">O</span>
                            <?php } else { ?>
                            <span style="color:var(--mg-text-muted);">X</span>
                            <?php } ?>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($row['si_use']) { ?>
                            <span style="color:var(--mg-success);">O</span>
                            <?php } else { ?>
                            <span style="color:var(--mg-text-muted);">X</span>
                            <?php } ?>
                        </td>
                        <td style="text-align:center;">
                            <a href="./shop_item_form.php?si_id=<?php echo $row['si_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm" style="white-space:nowrap;">수정</a>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if ($total_count == 0) { ?>
                    <tr>
                        <td colspan="9" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                            등록된 상품이 없습니다.
                            <br><br>
                            <a href="./shop_item_form.php" class="mg-btn mg-btn-primary">상품 등록하기</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($total_count > 0) { ?>
            <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 상품을 삭제하시겠습니까?');">선택 삭제</button>
            </div>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $query_string = 'type_group='.urlencode($type_group).'&sfl='.$sfl.'&stx='.urlencode($stx);
    $start_page = max(1, $page - 2);
    $end_page = min($total_page, $page + 2);

    if ($page > 1) {
        echo '<a href="?'.$query_string.'&page=1">&laquo;</a>';
        echo '<a href="?'.$query_string.'&page='.($page-1).'">&lsaquo;</a>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $page) {
            echo '<span class="active">'.$i.'</span>';
        } else {
            echo '<a href="?'.$query_string.'&page='.$i.'">'.$i.'</a>';
        }
    }

    if ($page < $total_page) {
        echo '<a href="?'.$query_string.'&page='.($page+1).'">&rsaquo;</a>';
        echo '<a href="?'.$query_string.'&page='.$total_page.'">&raquo;</a>';
    }
    ?>
</div>
<?php } ?>

<script>
function checkAll(el) {
    var chks = document.querySelectorAll('input[name="chk[]"]');
    chks.forEach(function(chk) {
        chk.checked = el.checked;
    });
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
