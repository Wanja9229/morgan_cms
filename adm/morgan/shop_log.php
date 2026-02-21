<?php
/**
 * Morgan Edition - 구매/선물 내역
 */

$sub_menu = "800900";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$sl_type = isset($_GET['sl_type']) ? clean_xss_tags($_GET['sl_type']) : '';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 30;
$offset = ($page - 1) * $rows;

// 검색 쿼리
$where = "WHERE 1=1";
if ($sl_type) {
    $sl_type_escaped = sql_real_escape_string($sl_type);
    $where .= " AND l.sl_type = '{$sl_type_escaped}'";
}
if ($sfl && $stx) {
    $stx_escaped = sql_real_escape_string($stx);
    if ($sfl == 'mb_id') {
        $where .= " AND l.mb_id LIKE '%{$stx_escaped}%'";
    } else if ($sfl == 'si_name') {
        $where .= " AND i.si_name LIKE '%{$stx_escaped}%'";
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt
        FROM {$g5['mg_shop_log_table']} l
        LEFT JOIN {$g5['mg_shop_item_table']} i ON l.si_id = i.si_id
        $where";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$total_page = ceil($total_count / $rows);

// 목록 조회
$sql = "SELECT l.*, i.si_name, i.si_type, m.mb_nick
        FROM {$g5['mg_shop_log_table']} l
        LEFT JOIN {$g5['mg_shop_item_table']} i ON l.si_id = i.si_id
        LEFT JOIN {$g5['member_table']} m ON l.mb_id = m.mb_id
        $where
        ORDER BY l.sl_id DESC
        LIMIT $offset, $rows";
$result = sql_query($sql);

// 통계
$stat_purchase = sql_fetch("SELECT COUNT(*) as cnt, COALESCE(SUM(sl_price),0) as total FROM {$g5['mg_shop_log_table']} WHERE sl_type = 'purchase'");
$stat_gift = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_shop_log_table']} WHERE sl_type = 'gift_send'");

// 타입명
$type_names = array(
    'purchase' => '구매',
    'gift_send' => '선물 발송',
    'gift_receive' => '선물 수령'
);

$item_types = array(
    'title' => '칭호',
    'badge' => '뱃지',
    'nick_color' => '닉네임 색상',
    'nick_effect' => '닉네임 효과',
    'profile_border' => '프로필 테두리',
    'equip' => '장비',
    'emoticon_set' => '이모티콘',
    'furniture' => '가구',
    'etc' => '기타'
);

$g5['title'] = '구매/선물 내역';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">총 구매 건수</div>
        <div class="mg-stat-value"><?php echo number_format($stat_purchase['cnt']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">총 구매 포인트</div>
        <div class="mg-stat-value"><?php echo number_format($stat_purchase['total']); ?>P</div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">선물 발송 건수</div>
        <div class="mg-stat-value"><?php echo number_format($stat_gift['cnt']); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <select name="sl_type" class="mg-form-select" style="width:auto;">
                <option value="">전체 유형</option>
                <option value="purchase" <?php echo $sl_type == 'purchase' ? 'selected' : ''; ?>>구매</option>
                <option value="gift_send" <?php echo $sl_type == 'gift_send' ? 'selected' : ''; ?>>선물 발송</option>
                <option value="gift_receive" <?php echo $sl_type == 'gift_receive' ? 'selected' : ''; ?>>선물 수령</option>
            </select>
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="mb_id" <?php echo $sfl == 'mb_id' ? 'selected' : ''; ?>>회원ID</option>
                <option value="si_name" <?php echo $sfl == 'si_name' ? 'selected' : ''; ?>>상품명</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="검색어 입력">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <?php if ($sl_type || $stx) { ?>
            <a href="./shop_log.php" class="mg-btn mg-btn-secondary">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:800px;">
            <thead>
                <tr>
                    <th style="width:60px;">번호</th>
                    <th style="width:100px;">유형</th>
                    <th>회원</th>
                    <th>상품명</th>
                    <th style="width:80px;">타입</th>
                    <th style="width:100px;">가격</th>
                    <th style="width:150px;">일시</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = $total_count - $offset;
                while ($row = sql_fetch_array($result)) {
                    $type_badge = '';
                    switch ($row['sl_type']) {
                        case 'purchase':
                            $type_badge = '<span class="mg-badge mg-badge-primary">구매</span>';
                            break;
                        case 'gift_send':
                            $type_badge = '<span class="mg-badge mg-badge-warning">선물발송</span>';
                            break;
                        case 'gift_receive':
                            $type_badge = '<span class="mg-badge mg-badge-success">선물수령</span>';
                            break;
                    }
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $num--; ?></td>
                    <td style="text-align:center;"><?php echo $type_badge; ?></td>
                    <td>
                        <span style="color:var(--mg-accent);"><?php echo $row['mb_id']; ?></span>
                        <?php if ($row['mb_nick']) { ?>
                        <span style="color:var(--mg-text-muted);">(<?php echo $row['mb_nick']; ?>)</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($row['si_name']) { ?>
                        <a href="./shop_item_form.php?si_id=<?php echo $row['si_id']; ?>" style="color:var(--mg-text-primary);">
                            <?php echo htmlspecialchars($row['si_name']); ?>
                        </a>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">(삭제된 상품)</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php echo $item_types[$row['si_type']] ?? '-'; ?>
                    </td>
                    <td style="text-align:right;">
                        <?php echo number_format($row['sl_price']); ?>P
                    </td>
                    <td style="text-align:center;color:var(--mg-text-muted);">
                        <?php echo $row['sl_datetime']; ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($total_count == 0) { ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        내역이 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $query_string = 'sl_type=' . urlencode($sl_type) . '&sfl=' . $sfl . '&stx=' . urlencode($stx);
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

<?php
require_once __DIR__.'/_tail.php';
?>
