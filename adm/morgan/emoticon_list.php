<?php
/**
 * Morgan Edition - 이모티콘 관리
 */

$sub_menu = "800950";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 조건
$status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : 'all';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$rows = 20;
$offset = ($page - 1) * $rows;

// 상태 필터
$valid_statuses = array('all', 'draft', 'pending', 'approved', 'rejected');
if (!in_array($status, $valid_statuses)) $status = 'all';

// 쿼리
$where = "WHERE 1=1";
if ($status !== 'all') {
    $where .= " AND es.es_status = '".sql_real_escape_string($status)."'";
}
if ($stx) {
    $stx_esc = sql_real_escape_string($stx);
    $where .= " AND (es.es_name LIKE '%{$stx_esc}%' OR es.es_creator_id LIKE '%{$stx_esc}%')";
}

// 전체 개수
$sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_emoticon_set_table']} es {$where}";
$row = sql_fetch($sql);
$total_count = (int)$row['cnt'];
$total_page = ceil($total_count / $rows);

// 상태별 통계
$stat_sql = "SELECT es_status, COUNT(*) as cnt FROM {$g5['mg_emoticon_set_table']} GROUP BY es_status";
$stat_result = sql_query($stat_sql);
$status_counts = array('draft' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0);
while ($srow = sql_fetch_array($stat_result)) {
    $status_counts[$srow['es_status']] = (int)$srow['cnt'];
}
$total_all = array_sum($status_counts);

// 목록
$sql = "SELECT es.*,
               (SELECT COUNT(*) FROM {$g5['mg_emoticon_table']} WHERE es_id = es.es_id) as em_count
        FROM {$g5['mg_emoticon_set_table']} es
        {$where}
        ORDER BY es.es_id DESC
        LIMIT {$offset}, {$rows}";
$result = sql_query($sql);

$status_labels = array(
    'draft' => array('label' => '작성중', 'class' => 'mg-badge-secondary'),
    'pending' => array('label' => '심사대기', 'class' => 'mg-badge-warning'),
    'approved' => array('label' => '승인', 'class' => 'mg-badge-success'),
    'rejected' => array('label' => '반려', 'class' => 'mg-badge-error'),
);

$g5['title'] = '이모티콘 관리';
require_once __DIR__.'/_head.php';
?>

<style>
.mg-badge-secondary { background: rgba(148,155,164,0.2); color: #949ba4; }
.mg-badge-info { background: rgba(59,130,246,0.2); color: #60a5fa; }
.mg-emoticon-preview {
    width: 48px; height: 48px; object-fit: contain; border-radius: 4px;
    background: var(--mg-bg-tertiary);
}
@media (max-width: 768px) {
    .mg-hide-mobile { display: none !important; }
    #fsearch_emo { flex-direction: column; }
    #fsearch_emo .mg-form-select,
    #fsearch_emo .mg-form-input,
    #fsearch_emo .mg-btn { width: 100% !important; max-width: none !important; }
    #fsearch_emo .mg-btn-secondary { margin-left: 0 !important; }
    .mg-emoticon-preview { width: 40px; height: 40px; }
}
</style>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체</div>
        <div class="mg-stat-value"><?php echo number_format($total_all); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">심사 대기</div>
        <div class="mg-stat-value" style="color:var(--mg-warning);"><?php echo number_format($status_counts['pending']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">승인</div>
        <div class="mg-stat-value" style="color:var(--mg-success);"><?php echo number_format($status_counts['approved']); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">반려</div>
        <div class="mg-stat-value" style="color:var(--mg-error);"><?php echo number_format($status_counts['rejected']); ?></div>
    </div>
</div>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch_emo" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <select name="status" class="mg-form-select" style="width:auto;">
                <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>전체 상태</option>
                <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>심사대기</option>
                <option value="approved" <?php echo $status == 'approved' ? 'selected' : ''; ?>>승인</option>
                <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>반려</option>
                <option value="draft" <?php echo $status == 'draft' ? 'selected' : ''; ?>>작성중</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;flex:1;max-width:300px;" placeholder="셋 이름 또는 제작자 ID">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <a href="./emoticon_form.php" class="mg-btn mg-btn-secondary" style="margin-left:auto;">이모티콘 셋 등록</a>
        </form>
    </div>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <form name="femoticonlist" id="femoticonlist" method="post" action="./emoticon_form_update.php">
            <input type="hidden" name="action" value="bulk_delete">

            <table class="mg-table" style="min-width:640px;">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" onclick="checkAll(this);"></th>
                        <th style="width:60px;">미리보기</th>
                        <th>셋 이름</th>
                        <th style="width:80px;">제작자</th>
                        <th style="width:70px;">상태</th>
                        <th style="width:50px;">개수</th>
                        <th style="width:70px;">가격</th>
                        <th class="mg-hide-mobile" style="width:50px;">판매</th>
                        <th class="mg-hide-mobile" style="width:50px;">사용</th>
                        <th class="mg-hide-mobile" style="width:120px;">등록일</th>
                        <th style="width:100px;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = sql_fetch_array($result)) {
                        $st = $status_labels[$row['es_status']] ?? array('label' => $row['es_status'], 'class' => '');
                        $creator = $row['es_creator_id'] ? htmlspecialchars($row['es_creator_id']) : '<span style="color:var(--mg-accent);">관리자</span>';
                    ?>
                    <tr>
                        <td><input type="checkbox" name="chk[]" value="<?php echo $row['es_id']; ?>"></td>
                        <td>
                            <?php if ($row['es_preview']) { ?>
                            <img src="<?php echo htmlspecialchars($row['es_preview']); ?>" alt="" class="mg-emoticon-preview">
                            <?php } else { ?>
                            <div class="mg-emoticon-preview" style="display:flex;align-items:center;justify-content:center;color:var(--mg-text-muted);font-size:0.7rem;">-</div>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="./emoticon_form.php?es_id=<?php echo $row['es_id']; ?>" style="color:var(--mg-text-primary);">
                                <?php echo htmlspecialchars($row['es_name']); ?>
                            </a>
                        </td>
                        <td><?php echo $creator; ?></td>
                        <td><span class="mg-badge <?php echo $st['class']; ?>"><?php echo $st['label']; ?></span></td>
                        <td style="text-align:center;"><?php echo (int)$row['em_count']; ?></td>
                        <td style="text-align:right;"><?php echo number_format((int)$row['es_price']); ?>P</td>
                        <td class="mg-hide-mobile" style="text-align:center;"><?php echo number_format((int)$row['es_sales_count']); ?></td>
                        <td class="mg-hide-mobile" style="text-align:center;">
                            <?php if ($row['es_use']) { ?>
                            <span style="color:var(--mg-success);">O</span>
                            <?php } else { ?>
                            <span style="color:var(--mg-text-muted);">X</span>
                            <?php } ?>
                        </td>
                        <td class="mg-hide-mobile" style="font-size:0.8rem;"><?php echo substr($row['es_datetime'], 0, 16); ?></td>
                        <td>
                            <div style="display:flex;gap:0.25rem;flex-wrap:nowrap;">
                                <a href="./emoticon_form.php?es_id=<?php echo $row['es_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm" style="white-space:nowrap;">수정</a>
                                <?php if ($row['es_status'] === 'pending') { ?>
                                <button type="button" class="mg-btn mg-btn-sm" style="background:var(--mg-success);color:#fff;white-space:nowrap;" onclick="approveSet(<?php echo $row['es_id']; ?>)">승인</button>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if ($total_count == 0) { ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                            등록된 이모티콘 셋이 없습니다.
                            <br><br>
                            <a href="./emoticon_form.php" class="mg-btn mg-btn-primary">이모티콘 셋 등록하기</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($total_count > 0) { ?>
            <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 이모티콘 셋을 삭제하시겠습니까? 이미지 파일도 함께 삭제됩니다.');">선택 삭제</button>
            </div>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $query_string = 'status='.$status.'&stx='.urlencode($stx);
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

<!-- 승인/반려 모달 -->
<div id="approveModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;z-index:1000;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);border-radius:0.5rem;padding:1.5rem;max-width:400px;width:90%;">
        <h3 style="margin-bottom:1rem;">이모티콘 셋 승인/반려</h3>
        <form id="approveForm" method="post" action="./emoticon_approve.php">
            <input type="hidden" name="es_id" id="approve_es_id" value="">
            <div class="mg-form-group">
                <label class="mg-form-label">처리</label>
                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="radio" name="approve_action" value="approve" checked onchange="toggleRejectReason()">
                        <span style="color:var(--mg-success);">승인</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="radio" name="approve_action" value="reject" onchange="toggleRejectReason()">
                        <span style="color:var(--mg-error);">반려</span>
                    </label>
                </div>
            </div>
            <div class="mg-form-group" id="rejectReasonGroup" style="display:none;">
                <label class="mg-form-label" for="reject_reason">반려 사유</label>
                <textarea name="reject_reason" id="reject_reason" class="mg-form-textarea" rows="3" placeholder="반려 사유를 입력해주세요."></textarea>
            </div>
            <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeApproveModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">처리</button>
            </div>
        </form>
    </div>
</div>

<script>
function checkAll(el) {
    var chks = document.querySelectorAll('input[name="chk[]"]');
    chks.forEach(function(chk) { chk.checked = el.checked; });
}

function approveSet(esId) {
    document.getElementById('approve_es_id').value = esId;
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

function toggleRejectReason() {
    var isReject = document.querySelector('input[name="approve_action"]:checked').value === 'reject';
    document.getElementById('rejectReasonGroup').style.display = isReject ? 'block' : 'none';
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
