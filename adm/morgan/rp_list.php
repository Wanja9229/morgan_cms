<?php
/**
 * Morgan Edition - 역극 관리
 */

$sub_menu = "800650";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : '';
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 30;
$offset = ($page - 1) * $rows;

// 검색 쿼리
$where = "WHERE t.rt_status != 'deleted'";
if ($status && in_array($status, array('open', 'closed'))) {
    $status_escaped = sql_real_escape_string($status);
    $where .= " AND t.rt_status = '{$status_escaped}'";
}
if ($sfl && $stx) {
    $stx_escaped = sql_real_escape_string($stx);
    if ($sfl == 'rt_title') {
        $where .= " AND t.rt_title LIKE '%{$stx_escaped}%'";
    } else if ($sfl == 'mb_id') {
        $where .= " AND (t.mb_id LIKE '%{$stx_escaped}%' OR m.mb_nick LIKE '%{$stx_escaped}%')";
    } else if ($sfl == 'ch_name') {
        $where .= " AND c.ch_name LIKE '%{$stx_escaped}%'";
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt
        FROM {$g5['mg_rp_thread_table']} t
        LEFT JOIN {$g5['member_table']} m ON t.mb_id = m.mb_id
        LEFT JOIN {$g5['mg_character_table']} c ON t.ch_id = c.ch_id
        $where";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$total_page = ceil($total_count / $rows);

// 목록 조회
$sql = "SELECT t.*, m.mb_nick, c.ch_name,
        (SELECT COUNT(*) FROM {$g5['mg_rp_member_table']} rm WHERE rm.rt_id = t.rt_id) as member_count
        FROM {$g5['mg_rp_thread_table']} t
        LEFT JOIN {$g5['member_table']} m ON t.mb_id = m.mb_id
        LEFT JOIN {$g5['mg_character_table']} c ON t.ch_id = c.ch_id
        $where
        ORDER BY t.rt_id DESC
        LIMIT $offset, $rows";
$result = sql_query($sql);

// 통계
$stat_total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_rp_thread_table']} WHERE rt_status != 'deleted'");
$stat_open = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_rp_thread_table']} WHERE rt_status = 'open'");
$stat_closed = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_rp_thread_table']} WHERE rt_status = 'closed'");
$stat_replies = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_rp_reply_table']}");

$g5['title'] = '역극 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 역극</div>
        <div class="mg-stat-value"><?php echo number_format($stat_total['cnt']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">진행중</div>
        <div class="mg-stat-value" style="color:var(--mg-success);"><?php echo number_format($stat_open['cnt']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">완결</div>
        <div class="mg-stat-value" style="color:var(--mg-error);"><?php echo number_format($stat_closed['cnt']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">총 이음 수</div>
        <div class="mg-stat-value"><?php echo number_format($stat_replies['cnt']); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <select name="status" class="mg-form-select" style="width:auto;">
                <option value="">전체 상태</option>
                <option value="open" <?php echo $status == 'open' ? 'selected' : ''; ?>>진행중</option>
                <option value="closed" <?php echo $status == 'closed' ? 'selected' : ''; ?>>완결</option>
            </select>
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="rt_title" <?php echo $sfl == 'rt_title' ? 'selected' : ''; ?>>제목</option>
                <option value="mb_id" <?php echo $sfl == 'mb_id' ? 'selected' : ''; ?>>회원</option>
                <option value="ch_name" <?php echo $sfl == 'ch_name' ? 'selected' : ''; ?>>캐릭터</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="검색어 입력">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <?php if ($status || $stx) { ?>
            <a href="./rp_list.php" class="mg-btn mg-btn-secondary">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:60px;">번호</th>
                    <th>제목</th>
                    <th style="width:240px;">작성자</th>
                    <th style="width:90px;">상태</th>
                    <th style="width:80px;">이음수</th>
                    <th style="width:80px;">참여자</th>
                    <th style="width:150px;">생성일</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = $total_count - $offset;
                while ($row = sql_fetch_array($result)) {
                    $status_badge = '';
                    switch ($row['rt_status']) {
                        case 'open':
                            $status_badge = '<span class="mg-badge mg-badge-success">진행중</span>';
                            break;
                        case 'closed':
                            $status_badge = '<span class="mg-badge mg-badge-error">완결</span>';
                            break;
                    }
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $num--; ?></td>
                    <td>
                        <a href="<?php echo G5_URL; ?>/plugin/morgan/rp/view.php?rt_id=<?php echo $row['rt_id']; ?>" target="_blank" style="color:var(--mg-text-primary);">
                            <?php echo htmlspecialchars($row['rt_title']); ?>
                        </a>
                    </td>
                    <td>
                        <?php if ($row['ch_name']) { ?>
                        <span style="color:var(--mg-accent);"><?php echo htmlspecialchars($row['ch_name']); ?></span>
                        <?php } ?>
                        <span style="color:var(--mg-text-muted);font-size:0.75rem;">
                            (<?php echo $row['mb_id']; ?><?php if ($row['mb_nick']) { ?>/<?php echo $row['mb_nick']; ?><?php } ?>)
                        </span>
                    </td>
                    <td style="text-align:center;"><?php echo $status_badge; ?></td>
                    <td style="text-align:center;"><?php echo number_format($row['rt_reply_count']); ?></td>
                    <td style="text-align:center;"><?php echo number_format($row['member_count']); ?></td>
                    <td style="text-align:center;color:var(--mg-text-muted);font-size:0.8rem;">
                        <?php echo $row['rt_datetime']; ?>
                    </td>
                    <td style="text-align:center;">
                        <form method="post" action="./rp_list_update.php" style="display:inline;" onsubmit="return confirm('상태를 변경하시겠습니까?');">
                            <input type="hidden" name="rt_id" value="<?php echo $row['rt_id']; ?>">
                            <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <select name="rt_status" onchange="this.form.submit();" class="mg-form-select" style="width:auto;font-size:0.75rem;padding:0.25rem 0.5rem;">
                                <option value="open" <?php echo $row['rt_status'] == 'open' ? 'selected' : ''; ?>>진행중</option>
                                <option value="closed" <?php echo $row['rt_status'] == 'closed' ? 'selected' : ''; ?>>완결</option>
                                <option value="deleted">삭제</option>
                            </select>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($total_count == 0) { ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        역극 내역이 없습니다.
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
    $query_string = 'status=' . urlencode($status) . '&sfl=' . $sfl . '&stx=' . urlencode($stx);
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
