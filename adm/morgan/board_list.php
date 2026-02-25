<?php
/**
 * Morgan Edition - 게시판 관리
 */

$sub_menu = "800180";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = 20;
$offset = ($page - 1) * $rows;

// 검색 쿼리
$where = "WHERE 1=1";
if ($sfl && $stx) {
    $stx_esc = sql_real_escape_string($stx);
    if ($sfl == 'bo_table') {
        $where .= " AND a.bo_table LIKE '{$stx_esc}%'";
    } else {
        $where .= " AND a.{$sfl} LIKE '%{$stx_esc}%'";
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt FROM {$g5['board_table']} a {$where}";
$row = sql_fetch($sql);
$total_count = (int)$row['cnt'];
$total_page = ceil($total_count / $rows);

// 게시판 목록
$sql = "SELECT a.*,
               (SELECT COUNT(*) FROM {$g5['write_prefix']}{$write_table} WHERE 1) as cnt
        FROM {$g5['board_table']} a
        {$where}
        ORDER BY a.bo_order, a.bo_table
        LIMIT {$offset}, {$rows}";

// 게시판별 글 수 계산은 복잡하므로 목록에서 별도로
$sql = "SELECT a.*
        FROM {$g5['board_table']} a
        {$where}
        ORDER BY a.bo_order, a.bo_table
        LIMIT {$offset}, {$rows}";
$result = sql_query($sql);

// 스킨 목록
$skins = array();
$skin_dirs = glob(G5_SKIN_PATH.'/board/*', GLOB_ONLYDIR);
foreach ($skin_dirs as $dir) {
    $skin_name = basename($dir);
    $skins[] = $skin_name;
}

$g5['title'] = '게시판 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 게시판</div>
        <div class="mg-stat-value"><?php echo number_format($total_count); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="bo_table" <?php echo $sfl == 'bo_table' ? 'selected' : ''; ?>>TABLE</option>
                <option value="bo_subject" <?php echo $sfl == 'bo_subject' ? 'selected' : ''; ?>>제목</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="검색어 입력">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <a href="./board_form.php" class="mg-btn mg-btn-success" style="margin-left:auto;">게시판 추가</a>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:1000px;">
            <thead>
                <tr>
                    <th style="width:120px;">TABLE</th>
                    <th>제목</th>
                    <th style="width:120px;">스킨</th>
                    <th style="width:60px;">읽기Lv</th>
                    <th style="width:60px;">쓰기Lv</th>
                    <th style="width:60px;">순서</th>
                    <th style="width:60px;">검색</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = sql_fetch_array($result)) {
                ?>
                <tr>
                    <td>
                        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $row['bo_table']; ?>" target="_blank" style="color:var(--mg-text-primary);">
                            <?php echo $row['bo_table']; ?>
                        </a>
                    </td>
                    <td>
                        <a href="./board_form.php?w=u&bo_table=<?php echo $row['bo_table']; ?>" style="color:var(--mg-text-primary);">
                            <?php echo htmlspecialchars($row['bo_subject']); ?>
                        </a>
                    </td>
                    <td style="font-size:0.8rem;color:var(--mg-text-muted);">
                        <?php echo $row['bo_skin']; ?>
                    </td>
                    <td style="text-align:center;font-size:0.8rem;">
                        <?php echo $row['bo_read_level']; ?>
                    </td>
                    <td style="text-align:center;font-size:0.8rem;">
                        <?php echo $row['bo_write_level']; ?>
                    </td>
                    <td style="text-align:center;font-size:0.8rem;">
                        <?php echo $row['bo_order']; ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($row['bo_use_search']) { ?>
                        <span style="color:var(--mg-success);">O</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">X</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.25rem;flex-wrap:nowrap;">
                            <a href="./board_form.php?w=u&bo_table=<?php echo $row['bo_table']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm" style="white-space:nowrap;">수정</a>
                            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $row['bo_table']; ?>" target="_blank" class="mg-btn mg-btn-secondary mg-btn-sm" style="white-space:nowrap;">보기</a>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($total_count == 0) { ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 게시판이 없습니다.
                        <br><br>
                        <a href="./board_form.php" class="mg-btn mg-btn-primary">게시판 추가하기</a>
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
    $query_string = 'sfl='.$sfl.'&stx='.urlencode($stx);
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
