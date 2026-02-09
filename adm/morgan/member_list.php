<?php
/**
 * Morgan Edition - 회원 관리
 */

$sub_menu = "800190";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = 20;
$offset = ($page - 1) * $rows;

// 검색 쿼리
$where = "WHERE 1";
if ($sfl && $stx) {
    $stx_esc = sql_real_escape_string($stx);
    switch ($sfl) {
        case 'mb_id':
            $where .= " AND m.mb_id LIKE '%{$stx_esc}%'";
            break;
        case 'mb_nick':
            $where .= " AND m.mb_nick LIKE '%{$stx_esc}%'";
            break;
        case 'mb_email':
            $where .= " AND m.mb_email LIKE '%{$stx_esc}%'";
            break;
        case 'mb_level':
            $where .= " AND m.mb_level = '{$stx_esc}'";
            break;
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt FROM {$g5['member_table']} m {$where}";
$row = sql_fetch($sql);
$total_count = (int)$row['cnt'];
$total_page = ceil($total_count / $rows);

// 캐릭터 수 서브쿼리 포함 조회
$sql = "SELECT m.*,
               (SELECT COUNT(*) FROM {$g5['mg_character_table']} WHERE mb_id = m.mb_id AND ch_state != 'deleted') as char_count
        FROM {$g5['member_table']} m
        {$where}
        ORDER BY m.mb_datetime DESC
        LIMIT {$offset}, {$rows}";
$result = sql_query($sql);

$members = array();
while ($row = sql_fetch_array($result)) {
    $members[] = $row;
}

$g5['title'] = '회원 관리';
include_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 회원</div>
        <div class="mg-stat-value"><?php echo number_format($total_count); ?></div>
    </div>
    <?php
    $active_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['member_table']} WHERE mb_leave_date = '' AND mb_intercept_date = ''");
    $leave_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['member_table']} WHERE mb_leave_date != ''");
    $block_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['member_table']} WHERE mb_intercept_date != ''");
    ?>
    <div class="mg-stat-card">
        <div class="mg-stat-label">활동 회원</div>
        <div class="mg-stat-value"><?php echo number_format($active_row['cnt']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">탈퇴</div>
        <div class="mg-stat-value"><?php echo number_format($leave_row['cnt']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">차단</div>
        <div class="mg-stat-value"><?php echo number_format($block_row['cnt']); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0.75rem 1.25rem;">
        <form method="get" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="mb_id" <?php echo $sfl=='mb_id'?'selected':''; ?>>아이디</option>
                <option value="mb_nick" <?php echo $sfl=='mb_nick'?'selected':''; ?>>닉네임</option>
                <option value="mb_email" <?php echo $sfl=='mb_email'?'selected':''; ?>>이메일</option>
                <option value="mb_level" <?php echo $sfl=='mb_level'?'selected':''; ?>>권한</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;" placeholder="검색어">
            <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">검색</button>
            <?php if ($stx) { ?>
            <a href="<?php echo $_SERVER['SCRIPT_NAME']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 회원 목록 -->
<div class="mg-card">
    <div class="mg-card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <span>회원 목록 (<?php echo number_format($total_count); ?>명)</span>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th>아이디</th>
                    <th>닉네임</th>
                    <th>포인트</th>
                    <th>권한</th>
                    <th>캐릭터</th>
                    <th>가입일</th>
                    <th>상태</th>
                    <th style="width:145px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($members) == 0) { ?>
                <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--mg-text-muted);">회원이 없습니다.</td></tr>
                <?php } ?>
                <?php foreach ($members as $mb) {
                    $mb_status = '';
                    $mb_badge = '';
                    if ($mb['mb_leave_date']) {
                        $mb_status = '탈퇴';
                        $mb_badge = 'mg-badge-error';
                    } elseif ($mb['mb_intercept_date']) {
                        $mb_status = '차단';
                        $mb_badge = 'mg-badge-warning';
                    } else {
                        $mb_status = '정상';
                        $mb_badge = 'mg-badge-success';
                    }
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($mb['mb_id']); ?></strong></td>
                    <td><?php echo htmlspecialchars($mb['mb_nick']); ?></td>
                    <td><?php echo number_format($mb['mb_point']); ?></td>
                    <td><?php echo $mb['mb_level']; ?></td>
                    <td><?php echo (int)$mb['char_count']; ?>개</td>
                    <td><?php echo substr($mb['mb_datetime'], 0, 10); ?></td>
                    <td><span class="mg-badge <?php echo $mb_badge; ?>"><?php echo $mb_status; ?></span></td>
                    <td>
                        <a href="<?php echo G5_ADMIN_URL; ?>/morgan/member_form.php?mb_id=<?php echo urlencode($mb['mb_id']); ?>" class="mg-btn mg-btn-secondary mg-btn-sm">수정</a>
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
    $query_base = $_SERVER['SCRIPT_NAME'] . '?' . ($sfl ? "sfl={$sfl}&stx=" . urlencode($stx) . '&' : '');
    if ($page > 1) echo '<a href="' . $query_base . 'page=' . ($page - 1) . '">&laquo;</a>';
    $start_page = max(1, $page - 4);
    $end_page = min($total_page, $page + 4);
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $page) {
            echo '<span class="active">' . $i . '</span>';
        } else {
            echo '<a href="' . $query_base . 'page=' . $i . '">' . $i . '</a>';
        }
    }
    if ($page < $total_page) echo '<a href="' . $query_base . 'page=' . ($page + 1) . '">&raquo;</a>';
    ?>
</div>
<?php } ?>

<?php include_once __DIR__.'/_tail.php'; ?>
