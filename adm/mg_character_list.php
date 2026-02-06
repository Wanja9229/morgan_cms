<?php
/**
 * Morgan Edition - 캐릭터 관리 목록
 */

$sub_menu = '400100';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '캐릭터 관리';
include_once './admin.head.php';

// 필터
$state_filter = isset($_GET['state']) ? $_GET['state'] : '';
$search_field = isset($_GET['sfl']) ? $_GET['sfl'] : '';
$search_text = isset($_GET['stx']) ? trim($_GET['stx']) : '';

// 페이지네이션
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = 20;
$offset = ($page - 1) * $rows;

// 쿼리 조건
$where = "c.ch_state != 'deleted'";
if ($state_filter) {
    $where .= " AND c.ch_state = '".sql_real_escape_string($state_filter)."'";
}
if ($search_text) {
    if ($search_field == 'ch_name') {
        $where .= " AND c.ch_name LIKE '%".sql_real_escape_string($search_text)."%'";
    } else if ($search_field == 'mb_id') {
        $where .= " AND c.mb_id LIKE '%".sql_real_escape_string($search_text)."%'";
    } else if ($search_field == 'mb_nick') {
        $where .= " AND m.mb_nick LIKE '%".sql_real_escape_string($search_text)."%'";
    }
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        WHERE {$where}";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 상태별 개수
$sql_pending = "SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE ch_state = 'pending'";
$pending_count = sql_fetch($sql_pending)['cnt'];

$sql_editing = "SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE ch_state = 'editing'";
$editing_count = sql_fetch($sql_editing)['cnt'];

$sql_approved = "SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE ch_state = 'approved'";
$approved_count = sql_fetch($sql_approved)['cnt'];

// 목록 조회
$sql = "SELECT c.*, m.mb_nick, s.side_name, cl.class_name
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        WHERE {$where}
        ORDER BY
            CASE c.ch_state
                WHEN 'pending' THEN 1
                WHEN 'editing' THEN 2
                WHEN 'approved' THEN 3
            END,
            c.ch_datetime DESC
        LIMIT {$offset}, {$rows}";
$result = sql_query($sql);

// 페이지네이션 계산
$total_page = ceil($total_count / $rows);

// 쿼리스트링
$qstr = http_build_query(array_filter([
    'state' => $state_filter,
    'sfl' => $search_field,
    'stx' => $search_text
]));
?>

<div class="local_desc01 local_desc">
    <p>캐릭터 승인 및 관리</p>
</div>

<!-- 상태 필터 탭 -->
<ul class="nav nav-tabs mb-3" style="margin-bottom: 15px; border-bottom: 1px solid #ddd;">
    <li style="display: inline-block; margin-right: 5px;">
        <a href="?state=" class="btn <?php echo !$state_filter ? 'btn-primary' : 'btn-default'; ?>">
            전체 (<?php echo number_format($total_count); ?>)
        </a>
    </li>
    <li style="display: inline-block; margin-right: 5px;">
        <a href="?state=pending" class="btn <?php echo $state_filter == 'pending' ? 'btn-warning' : 'btn-default'; ?>">
            승인대기 (<?php echo number_format($pending_count); ?>)
        </a>
    </li>
    <li style="display: inline-block; margin-right: 5px;">
        <a href="?state=editing" class="btn <?php echo $state_filter == 'editing' ? 'btn-info' : 'btn-default'; ?>">
            수정중 (<?php echo number_format($editing_count); ?>)
        </a>
    </li>
    <li style="display: inline-block; margin-right: 5px;">
        <a href="?state=approved" class="btn <?php echo $state_filter == 'approved' ? 'btn-success' : 'btn-default'; ?>">
            승인됨 (<?php echo number_format($approved_count); ?>)
        </a>
    </li>
</ul>

<!-- 검색 -->
<form method="get" class="form-inline" style="margin-bottom: 15px;">
    <input type="hidden" name="state" value="<?php echo $state_filter; ?>">
    <select name="sfl" class="form-control" style="width: auto; display: inline-block;">
        <option value="ch_name" <?php echo $search_field == 'ch_name' ? 'selected' : ''; ?>>캐릭터명</option>
        <option value="mb_id" <?php echo $search_field == 'mb_id' ? 'selected' : ''; ?>>회원ID</option>
        <option value="mb_nick" <?php echo $search_field == 'mb_nick' ? 'selected' : ''; ?>>회원닉네임</option>
    </select>
    <input type="text" name="stx" value="<?php echo htmlspecialchars($search_text); ?>" class="form-control" style="width: 200px; display: inline-block;">
    <button type="submit" class="btn btn-primary">검색</button>
</form>

<form name="fcharlist" method="post" action="./mg_character_list_update.php">
    <input type="hidden" name="token" value="<?php echo get_admin_token(); ?>">
    <input type="hidden" name="state" value="<?php echo $state_filter; ?>">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <caption>캐릭터 목록</caption>
            <thead>
                <tr>
                    <th scope="col"><input type="checkbox" id="chkall" onclick="check_all(this.form);"></th>
                    <th scope="col">ID</th>
                    <th scope="col">썸네일</th>
                    <th scope="col">캐릭터명</th>
                    <th scope="col">상태</th>
                    <th scope="col">소유자</th>
                    <th scope="col">세력/종족</th>
                    <th scope="col">등록일</th>
                    <th scope="col">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = $total_count - $offset;
                while ($row = sql_fetch_array($result)) {
                    $state_labels = array(
                        'editing' => '<span class="label label-default">수정중</span>',
                        'pending' => '<span class="label label-warning">승인대기</span>',
                        'approved' => '<span class="label label-success">승인됨</span>',
                    );
                    $state_label = $state_labels[$row['ch_state']] ?? '';
                ?>
                <tr>
                    <td class="td_chk">
                        <input type="checkbox" name="chk[]" value="<?php echo $row['ch_id']; ?>" id="chk_<?php echo $row['ch_id']; ?>">
                    </td>
                    <td class="td_num"><?php echo $row['ch_id']; ?></td>
                    <td class="td_img" style="text-align: center;">
                        <?php if ($row['ch_thumb']) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$row['ch_thumb']; ?>" alt="" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                        <?php } else { ?>
                        <span style="display: inline-block; width: 40px; height: 40px; background: #eee; border-radius: 4px; line-height: 40px; text-align: center;"><?php echo mb_substr($row['ch_name'], 0, 1); ?></span>
                        <?php } ?>
                    </td>
                    <td class="td_subject">
                        <a href="./mg_character_form.php?ch_id=<?php echo $row['ch_id']; ?>&amp;<?php echo $qstr; ?>">
                            <?php echo htmlspecialchars($row['ch_name']); ?>
                        </a>
                        <?php if ($row['ch_main']) { ?>
                        <span class="label label-primary">대표</span>
                        <?php } ?>
                    </td>
                    <td class="td_mng"><?php echo $state_label; ?></td>
                    <td class="td_mb_id">
                        <a href="./member_form.php?w=u&amp;mb_id=<?php echo $row['mb_id']; ?>">
                            <?php echo $row['mb_nick']; ?>
                        </a>
                        <br><small class="text-muted"><?php echo $row['mb_id']; ?></small>
                    </td>
                    <td class="td_left">
                        <?php
                        $info = array();
                        if ($row['side_name']) $info[] = $row['side_name'];
                        if ($row['class_name']) $info[] = $row['class_name'];
                        echo implode(' / ', $info) ?: '-';
                        ?>
                    </td>
                    <td class="td_datetime"><?php echo substr($row['ch_datetime'], 0, 10); ?></td>
                    <td class="td_mng">
                        <a href="./mg_character_form.php?ch_id=<?php echo $row['ch_id']; ?>&amp;<?php echo $qstr; ?>" class="btn btn-default btn-xs">상세</a>
                        <?php if ($row['ch_state'] == 'pending') { ?>
                        <a href="./mg_character_update.php?action=approve&amp;ch_id=<?php echo $row['ch_id']; ?>&amp;token=<?php echo get_admin_token(); ?>" onclick="return confirm('이 캐릭터를 승인하시겠습니까?');" class="btn btn-success btn-xs">승인</a>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                    $num--;
                }
                if ($total_count == 0) {
                ?>
                <tr>
                    <td colspan="9" class="td_empty">자료가 없습니다.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <select name="action" class="form-control" style="width: auto; display: inline-block;">
            <option value="">일괄 작업 선택</option>
            <option value="approve">선택 승인</option>
            <option value="reject">선택 반려</option>
            <option value="delete">선택 삭제</option>
        </select>
        <button type="submit" class="btn btn-default" onclick="return confirm('선택한 캐릭터에 대해 작업을 실행하시겠습니까?');">실행</button>
    </div>
</form>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<nav class="pg_wrap" style="text-align: center; margin-top: 20px;">
    <ul class="pagination">
        <?php
        $start_page = max(1, $page - 5);
        $end_page = min($total_page, $page + 5);

        if ($page > 1) {
            echo '<li><a href="?page=1&amp;'.$qstr.'">&laquo;</a></li>';
            echo '<li><a href="?page='.($page-1).'&amp;'.$qstr.'">&lsaquo;</a></li>';
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<li class="active"><a href="#">'.$i.'</a></li>';
            } else {
                echo '<li><a href="?page='.$i.'&amp;'.$qstr.'">'.$i.'</a></li>';
            }
        }

        if ($page < $total_page) {
            echo '<li><a href="?page='.($page+1).'&amp;'.$qstr.'">&rsaquo;</a></li>';
            echo '<li><a href="?page='.$total_page.'&amp;'.$qstr.'">&raquo;</a></li>';
        }
        ?>
    </ul>
</nav>
<?php } ?>

<script>
function check_all(f) {
    var chk = f.elements['chk[]'];
    if (!chk) return;
    if (!chk.length) {
        chk.checked = f.chkall.checked;
    } else {
        for (var i = 0; i < chk.length; i++) {
            chk[i].checked = f.chkall.checked;
        }
    }
}
</script>

<?php
include_once './admin.tail.php';
