<?php
/**
 * Morgan Edition - 알림 관리
 */

$sub_menu = "800600";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows = 30;
$offset = ($page - 1) * $rows;

// 검색 쿼리
$where = "";
if ($sfl && $stx) {
    if ($sfl == 'mb_id') {
        $where = " WHERE n.mb_id LIKE '%$stx%'";
    } else if ($sfl == 'noti_type') {
        $where = " WHERE n.noti_type = '$stx'";
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_notification_table']} n $where";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
$total_page = ceil($total_count / $rows);

// 목록 조회
$sql = "SELECT n.*, m.mb_nick
        FROM {$g5['mg_notification_table']} n
        LEFT JOIN {$g5['member_table']} m ON n.mb_id = m.mb_id
        $where
        ORDER BY n.noti_id DESC
        LIMIT $offset, $rows";
$result = sql_query($sql);

// 알림 타입 목록
$noti_types = array(
    'comment' => '댓글',
    'reply' => '답글',
    'like' => '좋아요',
    'character_approved' => '캐릭터 승인',
    'character_rejected' => '캐릭터 반려',
    'character_unapproved' => '캐릭터 승인취소',
    'character_deleted' => '캐릭터 삭제',
    'gift_received' => '선물 수신',
    'gift_accepted' => '선물 수락',
    'gift_rejected' => '선물 거절',
    'emoticon' => '이모티콘',
    'rp_reply' => 'RP 이음',
    'system' => '시스템'
);

$g5['title'] = '알림 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 알림</div>
        <div class="mg-stat-value"><?php echo number_format($total_count); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="mb_id" <?php echo $sfl == 'mb_id' ? 'selected' : ''; ?>>회원ID</option>
                <option value="noti_type" <?php echo $sfl == 'noti_type' ? 'selected' : ''; ?>>알림타입</option>
            </select>
            <input type="text" name="stx" value="<?php echo $stx; ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="검색어 입력">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <form name="fnotilist" id="fnotilist" method="post" action="./notification_update.php">
            <input type="hidden" name="token" value="">
            <input type="hidden" name="page" value="<?php echo $page; ?>">

            <table class="mg-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" onclick="checkAll(this);"></th>
                        <th style="width:60px;">번호</th>
                        <th style="width:100px;">회원ID</th>
                        <th style="width:100px;">닉네임</th>
                        <th style="width:80px;">타입</th>
                        <th>내용</th>
                        <th style="width:60px;">상태</th>
                        <th style="width:150px;">일시</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $num = $total_count - $offset;
                    while ($row = sql_fetch_array($result)) {
                        $type_name = isset($noti_types[$row['noti_type']]) ? $noti_types[$row['noti_type']] : $row['noti_type'];
                    ?>
                    <tr style="<?php echo $row['noti_read'] ? '' : 'background:rgba(245,159,10,0.05);'; ?>">
                        <td><input type="checkbox" name="chk[]" value="<?php echo $row['noti_id']; ?>"></td>
                        <td><?php echo $num--; ?></td>
                        <td><?php echo $row['mb_id']; ?></td>
                        <td><?php echo $row['mb_nick']; ?></td>
                        <td><span class="mg-badge mg-badge-warning"><?php echo $type_name; ?></span></td>
                        <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($row['noti_content']); ?></td>
                        <td>
                            <?php if ($row['noti_read']) { ?>
                            <span class="mg-badge mg-badge-success">읽음</span>
                            <?php } else { ?>
                            <span class="mg-badge mg-badge-error">미읽음</span>
                            <?php } ?>
                        </td>
                        <td style="color:var(--mg-text-muted);"><?php echo $row['noti_datetime']; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if ($total_count == 0) { ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">알림 내역이 없습니다.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($total_count > 0) { ?>
            <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 알림을 삭제하시겠습니까?');">선택 삭제</button>
                <button type="submit" name="btn_read" class="mg-btn mg-btn-secondary mg-btn-sm">읽음 처리</button>
            </div>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $start_page = max(1, $page - 2);
    $end_page = min($total_page, $page + 2);

    if ($page > 1) {
        echo '<a href="?sfl='.$sfl.'&stx='.$stx.'&page=1">&laquo;</a>';
        echo '<a href="?sfl='.$sfl.'&stx='.$stx.'&page='.($page-1).'">&lsaquo;</a>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $page) {
            echo '<span class="active">'.$i.'</span>';
        } else {
            echo '<a href="?sfl='.$sfl.'&stx='.$stx.'&page='.$i.'">'.$i.'</a>';
        }
    }

    if ($page < $total_page) {
        echo '<a href="?sfl='.$sfl.'&stx='.$stx.'&page='.($page+1).'">&rsaquo;</a>';
        echo '<a href="?sfl='.$sfl.'&stx='.$stx.'&page='.$total_page.'">&raquo;</a>';
    }
    ?>
</div>
<?php } ?>

<script>
function checkAll(el) {
    var chks = document.querySelectorAll('input[name="chk[]"]');
    chks.forEach(function(chk) { chk.checked = el.checked; });
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
