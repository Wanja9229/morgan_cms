<?php
/**
 * Morgan Edition - 캐릭터 관리
 */

$sub_menu = "800200";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$state = isset($_GET['state']) ? clean_xss_tags($_GET['state']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = 20;
$offset = ($page - 1) * $rows;

// 검색 쿼리
$where = "WHERE c.ch_state != 'deleted'";
if ($sfl && $stx) {
    $stx_esc = sql_real_escape_string($stx);
    if ($sfl == 'ch_name') {
        $where .= " AND c.ch_name LIKE '%{$stx_esc}%'";
    } else if ($sfl == 'mb_id') {
        $where .= " AND c.mb_id LIKE '%{$stx_esc}%'";
    }
}
if ($state) {
    $where .= " AND c.ch_state = '".sql_real_escape_string($state)."'";
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} c {$where}";
$row = sql_fetch($sql);
$total_count = (int)$row['cnt'];
$total_page = ceil($total_count / $rows);

// 상태별 통계
$stats = array('editing' => 0, 'pending' => 0, 'approved' => 0);
$stat_result = sql_query("SELECT ch_state, COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE ch_state != 'deleted' GROUP BY ch_state");
while ($stat = sql_fetch_array($stat_result)) {
    $stats[$stat['ch_state']] = (int)$stat['cnt'];
}

// 목록 조회
$sql = "SELECT c.*, m.mb_nick
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        {$where}
        ORDER BY c.ch_id DESC
        LIMIT {$offset}, {$rows}";
$result = sql_query($sql);

// 진영/클래스 목록 캐시
$sides = array();
$classes = array();
$side_result = sql_query("SELECT side_id, side_name FROM {$g5['mg_side_table']} WHERE side_use = 1");
while ($s = sql_fetch_array($side_result)) {
    $sides[$s['side_id']] = $s['side_name'];
}
$class_result = sql_query("SELECT class_id, class_name FROM {$g5['mg_class_table']} WHERE class_use = 1");
while ($c = sql_fetch_array($class_result)) {
    $classes[$c['class_id']] = $c['class_name'];
}

$g5['title'] = '캐릭터 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체</div>
        <div class="mg-stat-value"><?php echo number_format($total_count); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">승인 대기</div>
        <div class="mg-stat-value" style="color:var(--mg-warning);"><?php echo number_format($stats['pending']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">승인됨</div>
        <div class="mg-stat-value" style="color:var(--mg-success);"><?php echo number_format($stats['approved']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">작성중</div>
        <div class="mg-stat-value" style="color:var(--mg-text-muted);"><?php echo number_format($stats['editing']); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <select name="state" class="mg-form-select" style="width:auto;">
                <option value="">전체 상태</option>
                <option value="pending" <?php echo $state == 'pending' ? 'selected' : ''; ?>>승인 대기</option>
                <option value="approved" <?php echo $state == 'approved' ? 'selected' : ''; ?>>승인됨</option>
                <option value="editing" <?php echo $state == 'editing' ? 'selected' : ''; ?>>작성중</option>
            </select>
            <select name="sfl" class="mg-form-select" style="width:auto;">
                <option value="ch_name" <?php echo $sfl == 'ch_name' ? 'selected' : ''; ?>>캐릭터명</option>
                <option value="mb_id" <?php echo $sfl == 'mb_id' ? 'selected' : ''; ?>>회원ID</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="검색어 입력">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <?php if ($state || $stx) { ?>
            <a href="./character_list.php" class="mg-btn mg-btn-secondary">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <form name="fcharlist" id="fcharlist" method="post" action="./character_list_update.php">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="state" value="<?php echo $state; ?>">

            <table class="mg-table" style="width:100%;min-width:900px;">
                <thead>
                    <tr>
                        <th style="width:50px;"><input type="checkbox" onclick="checkAll(this);"></th>
                        <th style="width:60px;">번호</th>
                        <th style="width:80px;">이미지</th>
                        <th>캐릭터명</th>
                        <th style="width:100px;">회원</th>
                        <th style="width:90px;">진영</th>
                        <th style="width:90px;">클래스</th>
                        <th style="width:80px;">상태</th>
                        <th style="width:120px;">등록일</th>
                        <th style="width:120px;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $num = $total_count - $offset;
                    while ($row = sql_fetch_array($result)) {
                        $side_name = $row['side_id'] ? ($sides[$row['side_id']] ?? '-') : '-';
                        $class_name = $row['class_id'] ? ($classes[$row['class_id']] ?? '-') : '-';

                        $state_label = '';
                        $state_class = '';
                        switch ($row['ch_state']) {
                            case 'pending':
                                $state_label = '대기';
                                $state_class = 'mg-badge-warning';
                                break;
                            case 'approved':
                                $state_label = '승인';
                                $state_class = 'mg-badge-success';
                                break;
                            case 'editing':
                                $state_label = '작성중';
                                $state_class = '';
                                break;
                        }

                        // 썸네일 URL (th_ 접두사 버전 우선)
                        $thumb_url = G5_THEME_URL.'/img/no-image.png';
                        if ($row['ch_thumb']) {
                            $dir = dirname($row['ch_thumb']);
                            $base = basename($row['ch_thumb']);
                            $th_file = $dir.'/th_'.$base;
                            if (file_exists(MG_CHAR_IMAGE_PATH.'/'.$th_file)) {
                                $thumb_url = MG_CHAR_IMAGE_URL.'/'.$th_file;
                            } else {
                                $thumb_url = MG_CHAR_IMAGE_URL.'/'.$row['ch_thumb'];
                            }
                        }
                    ?>
                    <tr>
                        <td style="text-align:center;"><input type="checkbox" name="chk[]" value="<?php echo $row['ch_id']; ?>"></td>
                        <td style="text-align:center;color:var(--mg-text-muted);"><?php echo $num--; ?></td>
                        <td style="text-align:center;">
                            <img src="<?php echo $thumb_url; ?>" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;background:var(--mg-bg-tertiary);">
                        </td>
                        <td>
                            <a href="./character_form.php?ch_id=<?php echo $row['ch_id']; ?>" style="color:var(--mg-text-primary);">
                                <?php echo htmlspecialchars($row['ch_name']); ?>
                            </a>
                            <?php if ($row['ch_main']) { ?><span class="mg-badge mg-badge-primary" style="margin-left:0.5rem;">대표</span><?php } ?>
                        </td>
                        <td style="color:var(--mg-text-muted);"><?php echo $row['mb_id']; ?></td>
                        <td><?php echo htmlspecialchars($side_name); ?></td>
                        <td><?php echo htmlspecialchars($class_name); ?></td>
                        <td><span class="mg-badge <?php echo $state_class; ?>"><?php echo $state_label; ?></span></td>
                        <td style="color:var(--mg-text-muted);"><?php echo substr($row['ch_datetime'], 0, 10); ?></td>
                        <td>
                            <div style="display:flex;gap:0.5rem;">
                                <a href="./character_form.php?ch_id=<?php echo $row['ch_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">수정</a>
                                <?php if ($row['ch_state'] == 'pending') { ?>
                                <button type="submit" name="btn_approve" value="<?php echo $row['ch_id']; ?>" class="mg-btn mg-btn-success mg-btn-sm">승인</button>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if ($total_count == 0) { ?>
                    <tr>
                        <td colspan="10" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                            <?php echo $state == 'pending' ? '승인 대기 중인 캐릭터가 없습니다.' : '등록된 캐릭터가 없습니다.'; ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($total_count > 0) { ?>
            <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                <button type="submit" name="btn_approve_selected" class="mg-btn mg-btn-success mg-btn-sm">선택 승인</button>
                <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 캐릭터를 삭제하시겠습니까?');">선택 삭제</button>
            </div>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $query_string = 'state='.urlencode($state).'&sfl='.$sfl.'&stx='.urlencode($stx);
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
