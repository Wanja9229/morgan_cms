<?php
/**
 * Morgan Edition - 미션 관리
 */

$sub_menu = "801500";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$mode = isset($_GET['mode']) ? $_GET['mode'] : 'list';
$pm_id = isset($_GET['pm_id']) ? (int)$_GET['pm_id'] : 0;

// ==========================================
// AJAX: 미션 데이터 반환 (편집용)
// ==========================================
if (isset($_GET['ajax_prompt_data'])) {
    header('Content-Type: application/json; charset=utf-8');
    $ajax_id = (int)$_GET['pm_id'];
    $pm = sql_fetch("SELECT * FROM {$g5['mg_prompt_table']} WHERE pm_id = {$ajax_id}");
    echo json_encode($pm ?: null);
    exit;
}

// ==========================================
// AJAX: 개별 엔트리 승인/반려
// ==========================================
if (isset($_GET['ajax_entry_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    auth_check_menu($auth, $sub_menu, 'w');

    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $pe_id = (int)($_POST['pe_id'] ?? 0);

    if (!$pe_id) {
        echo json_encode(array('success' => false, 'message' => '잘못된 요청입니다.'));
        exit;
    }

    if ($action == 'approve') {
        $result = mg_prompt_approve($pe_id, $member['mb_id']);
        echo json_encode(array('success' => $result, 'message' => $result ? '승인되었습니다.' : '승인 실패 (이미 처리되었거나 상태가 다릅니다)'));
    } elseif ($action == 'reject') {
        $memo = isset($_POST['memo']) ? trim($_POST['memo']) : '';
        $result = mg_prompt_reject($pe_id, $member['mb_id'], $memo);
        echo json_encode(array('success' => $result, 'message' => $result ? '반려되었습니다.' : '반려 실패 (이미 처리되었거나 상태가 다릅니다)'));
    } else {
        echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
    }
    exit;
}

// ==========================================
// 공통 데이터 로드
// ==========================================

// 게시판 목록
$boards = array();
$board_result = sql_query("SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY gr_id, bo_order, bo_table");
while ($row = sql_fetch_array($board_result)) {
    $boards[] = $row;
}

// 재료 목록
$material_types = mg_get_material_types();

// ==========================================
// 모드별 데이터 로드
// ==========================================

// --- LIST 모드 ---
$prompts = array();
$total_count = 0;
$total_page = 1;
$page = 1;

if ($mode == 'list') {
    // 만료 자동 종료
    mg_prompt_check_expired();

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $rows = 20;
    $offset = ($page - 1) * $rows;

    // 필터
    $f_status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $f_cycle = isset($_GET['cycle']) ? trim($_GET['cycle']) : '';
    $f_board = isset($_GET['bo_table']) ? trim($_GET['bo_table']) : '';

    $where = '1';
    if ($f_status && in_array($f_status, array('draft', 'active', 'closed', 'archived'))) {
        $where .= " AND pm_status = '".sql_real_escape_string($f_status)."'";
    }
    if ($f_cycle && in_array($f_cycle, array('weekly', 'monthly', 'event'))) {
        $where .= " AND pm_cycle = '".sql_real_escape_string($f_cycle)."'";
    }
    if ($f_board) {
        $where .= " AND bo_table = '".sql_real_escape_string($f_board)."'";
    }

    $total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_prompt_table']} WHERE {$where}");
    $total_count = (int)$total_row['cnt'];
    $total_page = $total_count > 0 ? ceil($total_count / $rows) : 1;

    $sql = "SELECT p.*,
            (SELECT COUNT(*) FROM {$g5['mg_prompt_entry_table']} WHERE pm_id = p.pm_id) as entry_count
        FROM {$g5['mg_prompt_table']} p
        WHERE {$where}
        ORDER BY p.pm_created DESC
        LIMIT {$offset}, {$rows}";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $prompts[] = $row;
    }

    // 게시판 이름 매핑
    $board_names = array();
    foreach ($boards as $b) {
        $board_names[$b['bo_table']] = $b['bo_subject'];
    }
}

// --- EDIT 모드 ---
$edit_pm = null;
if ($mode == 'edit') {
    $clone_id = isset($_GET['clone_id']) ? (int)$_GET['clone_id'] : 0;
    if ($pm_id > 0) {
        $edit_pm = sql_fetch("SELECT * FROM {$g5['mg_prompt_table']} WHERE pm_id = {$pm_id}");
        if (!$edit_pm || !$edit_pm['pm_id']) {
            alert('미션을 찾을 수 없습니다.', './prompt.php');
        }
    } elseif ($clone_id > 0) {
        // 복제: 기존 미션 데이터로 새 미션 폼 프리필
        $edit_pm = mg_get_prompt($clone_id);
        if ($edit_pm) {
            $edit_pm['pm_id'] = 0;
            $edit_pm['pm_start_date'] = '';
            $edit_pm['pm_end_date'] = '';
            $edit_pm['pm_status'] = 'draft';
            $edit_pm['pm_banner'] = '';
        }
    }
}

// --- REVIEW 모드 ---
$review_pm = null;
$entries = array();
$entry_total = 0;
$entry_total_page = 1;
$entry_page = 1;

if ($mode == 'review' && $pm_id > 0) {
    $review_pm = sql_fetch("SELECT * FROM {$g5['mg_prompt_table']} WHERE pm_id = {$pm_id}");
    if (!$review_pm || !$review_pm['pm_id']) {
        alert('미션을 찾을 수 없습니다.', './prompt.php');
    }

    // 상태 필터
    $e_status = isset($_GET['e_status']) ? trim($_GET['e_status']) : '';
    $e_where = "e.pm_id = {$pm_id}";
    if ($e_status && in_array($e_status, array('submitted', 'approved', 'rejected', 'rewarded'))) {
        $e_where .= " AND e.pe_status = '".sql_real_escape_string($e_status)."'";
    }

    // 페이징
    $entry_page = isset($_GET['epage']) ? max(1, (int)$_GET['epage']) : 1;
    $entry_rows = 30;
    $entry_offset = ($entry_page - 1) * $entry_rows;

    $total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_prompt_entry_table']} e WHERE {$e_where}");
    $entry_total = (int)$total_row['cnt'];
    $entry_total_page = $entry_total > 0 ? ceil($entry_total / $entry_rows) : 1;

    $sql = "SELECT e.*, m.mb_nick
        FROM {$g5['mg_prompt_entry_table']} e
        LEFT JOIN {$g5['member_table']} m ON e.mb_id = m.mb_id
        WHERE {$e_where}
        ORDER BY e.pe_datetime DESC
        LIMIT {$entry_offset}, {$entry_rows}";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        // 게시글 정보 로드
        if ((int)$row['wr_id'] > 0 && $row['bo_table']) {
            $write_table = $g5['write_prefix'] . $row['bo_table'];
            $wr = sql_fetch("SELECT wr_subject, wr_content FROM {$write_table} WHERE wr_id = ".(int)$row['wr_id']);
            $row['wr_subject'] = $wr ? $wr['wr_subject'] : '(삭제된 글)';
            $row['wr_char_count'] = $wr ? mb_strlen(strip_tags($wr['wr_content'])) : 0;
        } else {
            $row['wr_subject'] = '(연결 없음)';
            $row['wr_char_count'] = 0;
        }
        // 추천수 (좋아요 수)
        $row['like_count'] = 0;
        if (isset($g5['mg_like_log_table']) && (int)$row['wr_id'] > 0) {
            $lk = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_like_log_table']} WHERE bo_table = '".sql_real_escape_string($row['bo_table'])."' AND wr_id = ".(int)$row['wr_id']);
            $row['like_count'] = (int)$lk['cnt'];
        }
        $entries[] = $row;
    }

    // 통계 요약
    $stat_submitted = mg_get_prompt_entry_count($pm_id, 'submitted');
    $stat_approved = mg_get_prompt_entry_count($pm_id, 'approved');
    $stat_rejected = mg_get_prompt_entry_count($pm_id, 'rejected');
    $stat_rewarded = mg_get_prompt_entry_count($pm_id, 'rewarded');
    $stat_total = mg_get_prompt_entry_count($pm_id);

    // 확장 통계
    $chars_sum = 0;
    $chars_count = 0;
    foreach ($entries as $e) {
        if ($e['wr_char_count'] > 0) {
            $chars_sum += $e['wr_char_count'];
            $chars_count++;
        }
    }
    $stat_avg_chars = $chars_count > 0 ? round($chars_sum / $chars_count) : 0;

    $stat_total_reward = (int)sql_fetch("SELECT COALESCE(SUM(pe_point), 0) as total
        FROM {$g5['mg_prompt_entry_table']} WHERE pm_id = {$pm_id} AND pe_status = 'rewarded'")['total'];

    $stat_unique_members = (int)sql_fetch("SELECT COUNT(DISTINCT mb_id) as cnt
        FROM {$g5['mg_prompt_entry_table']} WHERE pm_id = {$pm_id}")['cnt'];
}

// 페이지 타이틀
$g5['title'] = '미션 관리';
require_once __DIR__.'/_head.php';

$update_url = G5_ADMIN_URL . '/morgan/prompt_update.php';
$self_url = G5_ADMIN_URL . '/morgan/prompt.php';

// 상태 배지 색상
$status_badges = array(
    'draft'    => array('label' => '초안', 'class' => ''),
    'active'   => array('label' => '진행중', 'class' => 'mg-badge-success'),
    'closed'   => array('label' => '종료', 'class' => 'mg-badge-warning'),
    'archived' => array('label' => '보관', 'class' => 'mg-badge-error'),
);

// 주기 배지
$cycle_badges = array(
    'weekly'  => array('label' => '주간', 'color' => '#3b82f6'),
    'monthly' => array('label' => '월간', 'color' => '#a855f7'),
    'event'   => array('label' => '이벤트', 'color' => '#f97316'),
);

// 모드 라벨
$mode_labels = array(
    'auto'   => '자동',
    'review' => '검수',
    'vote'   => '투표',
);

// 엔트리 상태
$entry_status_labels = array(
    'submitted' => array('label' => '대기', 'class' => ''),
    'approved'  => array('label' => '승인', 'class' => 'mg-badge-primary'),
    'rejected'  => array('label' => '반려', 'class' => 'mg-badge-error'),
    'rewarded'  => array('label' => '보상완료', 'class' => 'mg-badge-success'),
);
?>

<?php if ($mode == 'list') { ?>
<!-- ================================ -->
<!-- 미션 목록 -->
<!-- ================================ -->

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.5rem;">
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
        <!-- 상태 필터 -->
        <a href="?mode=list" class="mg-btn <?php echo !$f_status ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm">전체</a>
        <?php foreach ($status_badges as $sk => $sv) { ?>
        <a href="?mode=list&status=<?php echo $sk; ?>&cycle=<?php echo $f_cycle; ?>&bo_table=<?php echo urlencode($f_board); ?>"
           class="mg-btn <?php echo $f_status == $sk ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"><?php echo $sv['label']; ?></a>
        <?php } ?>

        <!-- 주기 필터 -->
        <span style="color:var(--mg-text-muted);margin:0 0.25rem;">|</span>
        <?php foreach ($cycle_badges as $ck => $cv) { ?>
        <a href="?mode=list&status=<?php echo $f_status; ?>&cycle=<?php echo $ck; ?>&bo_table=<?php echo urlencode($f_board); ?>"
           class="mg-btn <?php echo $f_cycle == $ck ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"><?php echo $cv['label']; ?></a>
        <?php } ?>

        <!-- 게시판 필터 -->
        <select onchange="location.href='?mode=list&status=<?php echo $f_status; ?>&cycle=<?php echo $f_cycle; ?>&bo_table='+this.value"
                class="mg-form-input" style="width:auto;padding:0.375rem 0.5rem;font-size:0.75rem;">
            <option value="">게시판 전체</option>
            <?php foreach ($boards as $b) { ?>
            <option value="<?php echo $b['bo_table']; ?>" <?php echo $f_board == $b['bo_table'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bo_subject']); ?></option>
            <?php } ?>
        </select>
    </div>
    <a href="?mode=edit&pm_id=0" class="mg-btn mg-btn-primary">+ 미션 등록</a>
</div>

<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:1100px;">
            <thead>
                <tr>
                    <th style="width:40px;">ID</th>
                    <th style="width:100px;">게시판</th>
                    <th style="min-width:160px;">제목</th>
                    <th style="width:60px;text-align:center;">주기</th>
                    <th style="width:50px;text-align:center;">모드</th>
                    <th style="width:70px;text-align:center;">상태</th>
                    <th style="width:200px;">기간</th>
                    <th style="width:55px;text-align:center;">제출</th>
                    <th style="width:90px;">작성일</th>
                    <th style="width:200px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($prompts)) { ?>
                <tr><td colspan="10" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">등록된 미션이 없습니다.</td></tr>
                <?php } else { foreach ($prompts as $pm) {
                    $sb = $status_badges[$pm['pm_status']] ?? array('label' => $pm['pm_status'], 'class' => '');
                    $cb = $cycle_badges[$pm['pm_cycle']] ?? array('label' => $pm['pm_cycle'], 'color' => '#949ba4');
                    $ml = $mode_labels[$pm['pm_mode']] ?? $pm['pm_mode'];
                    $bn = isset($board_names[$pm['bo_table']]) ? $board_names[$pm['bo_table']] : $pm['bo_table'];
                ?>
                <tr>
                    <td style="color:var(--mg-text-muted);"><?php echo $pm['pm_id']; ?></td>
                    <td><span class="mg-badge" style="font-size:0.7rem;"><?php echo htmlspecialchars($bn); ?></span></td>
                    <td>
                        <strong><?php echo htmlspecialchars($pm['pm_title']); ?></strong>
                        <?php if ($pm['pm_tags']) { ?>
                        <br><small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars($pm['pm_tags']); ?></small>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <span class="mg-badge" style="background:<?php echo $cb['color']; ?>20;color:<?php echo $cb['color']; ?>;"><?php echo $cb['label']; ?></span>
                    </td>
                    <td style="text-align:center;">
                        <span style="font-size:0.8rem;color:var(--mg-text-secondary);"><?php echo $ml; ?></span>
                    </td>
                    <td style="text-align:center;">
                        <span class="mg-badge <?php echo $sb['class']; ?>"><?php echo $sb['label']; ?></span>
                    </td>
                    <td>
                        <small>
                            <?php
                            if ($pm['pm_start_date']) echo date('y.m.d H:i', strtotime($pm['pm_start_date']));
                            else echo '-';
                            echo ' ~ ';
                            if ($pm['pm_end_date']) echo date('y.m.d H:i', strtotime($pm['pm_end_date']));
                            else echo '-';
                            ?>
                        </small>
                    </td>
                    <td style="text-align:center;">
                        <a href="?mode=review&pm_id=<?php echo $pm['pm_id']; ?>" style="color:var(--mg-accent);">
                            <?php echo (int)$pm['entry_count']; ?>건
                        </a>
                    </td>
                    <td><small style="color:var(--mg-text-muted);"><?php echo $pm['pm_created']; ?></small></td>
                    <td style="text-align:center;">
                        <a href="?mode=edit&pm_id=<?php echo $pm['pm_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">편집</a>
                        <a href="?mode=review&pm_id=<?php echo $pm['pm_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">검수</a>
                        <a href="?mode=edit&clone_id=<?php echo $pm['pm_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">복제</a>
                        <?php if ($pm['pm_status'] == 'active') { ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="closePrompt(<?php echo $pm['pm_id']; ?>, '<?php echo addslashes($pm['pm_title']); ?>')">종료</button>
                        <?php } ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deletePrompt(<?php echo $pm['pm_id']; ?>, '<?php echo addslashes($pm['pm_title']); ?>')">삭제</button>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 페이징 -->
<?php if ($total_page > 1) { ?>
<div style="text-align:center;padding:1rem 0;">
    <?php for ($i = 1; $i <= $total_page; $i++) { ?>
    <a href="?mode=list&status=<?php echo $f_status; ?>&cycle=<?php echo $f_cycle; ?>&bo_table=<?php echo urlencode($f_board); ?>&page=<?php echo $i; ?>"
       class="mg-btn <?php echo $i == $page ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"
       style="min-width:32px;"><?php echo $i; ?></a>
    <?php } ?>
</div>
<?php } ?>

<script>
function closePrompt(pmId, title) {
    if (!confirm('"' + title + '" 미션을 종료하시겠습니까?')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo $update_url; ?>';
    form.innerHTML = '<input type="hidden" name="mode" value="close"><input type="hidden" name="pm_id" value="' + pmId + '">';
    document.body.appendChild(form);
    form.submit();
}

function deletePrompt(pmId, title) {
    if (!confirm('"' + title + '" 미션을 삭제하시겠습니까?\n모든 제출 기록도 함께 삭제됩니다.')) return;
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = '<?php echo $update_url; ?>';
    form.innerHTML = '<input type="hidden" name="mode" value="delete"><input type="hidden" name="pm_id" value="' + pmId + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php } elseif ($mode == 'edit') { ?>
<!-- ================================ -->
<!-- 미션 등록/편집 -->
<!-- ================================ -->

<div style="margin-bottom:1rem;">
    <a href="?mode=list" class="mg-btn mg-btn-secondary mg-btn-sm">&larr; 목록으로</a>
    <span style="margin-left:0.5rem;font-size:1.125rem;font-weight:600;">
        <?php echo $edit_pm ? '미션 편집: ' . htmlspecialchars($edit_pm['pm_title']) : '새 미션 등록'; ?>
    </span>
</div>

<form id="prompt-edit-form" method="post" action="<?php echo $update_url; ?>" enctype="multipart/form-data" onsubmit="if(window.pmEditor){document.getElementById('pm_content_textarea').value=window.pmEditor.getHTML();}">
    <input type="hidden" name="mode" value="<?php echo $edit_pm ? 'edit' : 'add'; ?>">
    <input type="hidden" name="pm_id" value="<?php echo $edit_pm ? $edit_pm['pm_id'] : 0; ?>">

    <div class="mg-card">
        <div class="mg-card-header"><h3>기본 정보</h3></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">대상 게시판 *</label>
                    <select name="bo_table" class="mg-form-input" required>
                        <option value="">선택하세요</option>
                        <?php foreach ($boards as $b) { ?>
                        <option value="<?php echo $b['bo_table']; ?>" <?php echo ($edit_pm && $edit_pm['bo_table'] == $b['bo_table']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bo_subject']); ?> (<?php echo $b['bo_table']; ?>)</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">상태</label>
                    <div style="display:flex;gap:1.5rem;padding-top:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="radio" name="pm_status" value="draft" <?php echo (!$edit_pm || $edit_pm['pm_status'] == 'draft') ? 'checked' : ''; ?>>
                            <span>초안</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="radio" name="pm_status" value="active" <?php echo ($edit_pm && $edit_pm['pm_status'] == 'active') ? 'checked' : ''; ?>>
                            <span>활성화</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">미션 제목 *</label>
                <input type="text" name="pm_title" class="mg-form-input" value="<?php echo $edit_pm ? htmlspecialchars($edit_pm['pm_title']) : ''; ?>" required placeholder="미션 제목을 입력하세요">
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">미션 설명</label>
                <textarea name="pm_content" id="pm_content_textarea" class="mg-form-input" rows="6" style="display:none;"><?php echo $edit_pm ? htmlspecialchars($edit_pm['pm_content']) : ''; ?></textarea>
                <div id="toast_pm_content" style="min-height:300px;"></div>
            </div>
        </div>
    </div>

    <div class="mg-card">
        <div class="mg-card-header"><h3>주기 / 모드 / 기간</h3></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">주기</label>
                    <div style="display:flex;gap:1.5rem;padding-top:0.5rem;">
                        <?php foreach ($cycle_badges as $ck => $cv) { ?>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="radio" name="pm_cycle" value="<?php echo $ck; ?>" <?php echo ($edit_pm && $edit_pm['pm_cycle'] == $ck) ? 'checked' : ((!$edit_pm && $ck == 'weekly') ? 'checked' : ''); ?>>
                            <span><?php echo $cv['label']; ?></span>
                        </label>
                        <?php } ?>
                    </div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">보상 모드</label>
                    <div style="display:flex;gap:1.5rem;padding-top:0.5rem;">
                        <?php foreach ($mode_labels as $mk => $mv) { ?>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="radio" name="pm_mode" value="<?php echo $mk; ?>" <?php echo ($edit_pm && $edit_pm['pm_mode'] == $mk) ? 'checked' : ((!$edit_pm && $mk == 'review') ? 'checked' : ''); ?>>
                            <span><?php echo $mv; ?></span>
                        </label>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">시작일</label>
                    <input type="datetime-local" name="pm_start_date" class="mg-form-input" value="<?php echo $edit_pm && $edit_pm['pm_start_date'] ? date('Y-m-d\TH:i', strtotime($edit_pm['pm_start_date'])) : ''; ?>">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">종료일</label>
                    <input type="datetime-local" name="pm_end_date" class="mg-form-input" value="<?php echo $edit_pm && $edit_pm['pm_end_date'] ? date('Y-m-d\TH:i', strtotime($edit_pm['pm_end_date'])) : ''; ?>">
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card">
        <div class="mg-card-header"><h3>보상 설정</h3></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">기본 보상 (포인트)</label>
                    <input type="number" name="pm_point" class="mg-form-input" value="<?php echo $edit_pm ? (int)$edit_pm['pm_point'] : 0; ?>" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">선정작 추가 보상</label>
                    <input type="number" name="pm_bonus_point" class="mg-form-input" value="<?php echo $edit_pm ? (int)$edit_pm['pm_bonus_point'] : 0; ?>" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">선정작 인원</label>
                    <input type="number" name="pm_bonus_count" class="mg-form-input" value="<?php echo $edit_pm ? (int)$edit_pm['pm_bonus_count'] : 0; ?>" min="0">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">재료 보상 (선택)</label>
                    <select name="pm_material_id" class="mg-form-input">
                        <option value="0">없음</option>
                        <?php foreach ($material_types as $mt) { ?>
                        <option value="<?php echo $mt['mt_id']; ?>" <?php echo ($edit_pm && (int)$edit_pm['pm_material_id'] == (int)$mt['mt_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mt['mt_name']); ?> (<?php echo $mt['mt_code']; ?>)</option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">재료 수량</label>
                    <input type="number" name="pm_material_qty" class="mg-form-input" value="<?php echo $edit_pm ? (int)$edit_pm['pm_material_qty'] : 0; ?>" min="0">
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card">
        <div class="mg-card-header"><h3>참여 조건 / 기타</h3></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">최소 글자수 (0=제한없음)</label>
                    <input type="number" name="pm_min_chars" class="mg-form-input" value="<?php echo $edit_pm ? (int)$edit_pm['pm_min_chars'] : 0; ?>" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">인당 최대 제출 수</label>
                    <input type="number" name="pm_max_entry" class="mg-form-input" value="<?php echo $edit_pm ? (int)$edit_pm['pm_max_entry'] : 1; ?>" min="1">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">배너 이미지</label>
                    <?php if ($edit_pm && $edit_pm['pm_banner']) { ?>
                    <div style="margin-bottom:0.5rem;">
                        <img src="<?php echo htmlspecialchars($edit_pm['pm_banner']); ?>" style="max-width:300px;max-height:100px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">
                        <div style="margin-top:0.25rem;">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.8rem;color:var(--mg-text-muted);">
                                <input type="checkbox" name="remove_banner" value="1">
                                <span>기존 배너 삭제</span>
                            </label>
                        </div>
                    </div>
                    <?php } ?>
                    <input type="file" name="pm_banner" class="mg-form-input" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small style="color:var(--mg-text-muted);">jpg, png, gif, webp (권장: 800x200)</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">태그 (쉼표 구분)</label>
                    <input type="text" name="pm_tags" class="mg-form-input" value="<?php echo $edit_pm ? htmlspecialchars($edit_pm['pm_tags']) : ''; ?>" placeholder="예: 세계관, 캐릭터, 일상">
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
        <a href="?mode=list" class="mg-btn mg-btn-secondary">취소</a>
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </div>
</form>

<!-- Toast UI Editor for mission description -->
<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/toastui-editor.min.css">
<link rel="stylesheet" href="https://uicdn.toast.com/editor/3.2.2/theme/toastui-editor-dark.min.css">
<link rel="stylesheet" href="<?php echo G5_PATH; ?>/plugin/editor/toastui/morgan-dark.css">
<script src="https://uicdn.toast.com/editor/3.2.2/toastui-editor-all.min.js"></script>
<script>
(function() {
    var el = document.getElementById('toast_pm_content');
    if (!el) return;

    var textarea = document.getElementById('pm_content_textarea');
    var initialValue = textarea ? textarea.value : '';

    window.pmEditor = new toastui.Editor({
        el: el,
        height: '300px',
        initialEditType: 'wysiwyg',
        initialValue: initialValue,
        theme: 'dark',
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol'],
            ['image', 'link'],
            ['code', 'codeblock']
        ],
        hooks: {
            addImageBlobHook: function(blob, callback) {
                var formData = new FormData();
                formData.append('image', blob);
                fetch('<?php echo G5_URL; ?>/plugin/editor/toastui/imageUpload/upload.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.url) {
                        callback(data.url, blob.name || 'image');
                    } else {
                        alert(data.error || '이미지 업로드 실패');
                    }
                })
                .catch(function() { alert('이미지 업로드 네트워크 오류'); });
                return false;
            }
        }
    });
})();
</script>

<?php } elseif ($mode == 'review' && $review_pm) { ?>
<!-- ================================ -->
<!-- 미션 검수 -->
<!-- ================================ -->

<div style="margin-bottom:1rem;">
    <a href="?mode=list" class="mg-btn mg-btn-secondary mg-btn-sm">&larr; 목록으로</a>
    <span style="margin-left:0.5rem;font-size:1.125rem;font-weight:600;">검수: <?php echo htmlspecialchars($review_pm['pm_title']); ?></span>
</div>

<!-- 미션 요약 카드 -->
<div class="mg-card" style="margin-bottom:1.5rem;">
    <div class="mg-card-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(150px, 1fr));gap:1rem;">
            <div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);text-transform:uppercase;">게시판</div>
                <div style="font-weight:600;"><?php echo htmlspecialchars($review_pm['bo_table']); ?></div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);text-transform:uppercase;">주기</div>
                <?php $cb = $cycle_badges[$review_pm['pm_cycle']] ?? array('label' => $review_pm['pm_cycle'], 'color' => '#949ba4'); ?>
                <span class="mg-badge" style="background:<?php echo $cb['color']; ?>20;color:<?php echo $cb['color']; ?>;"><?php echo $cb['label']; ?></span>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);text-transform:uppercase;">모드</div>
                <div style="font-weight:600;"><?php echo $mode_labels[$review_pm['pm_mode']] ?? $review_pm['pm_mode']; ?></div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);text-transform:uppercase;">상태</div>
                <?php $sb = $status_badges[$review_pm['pm_status']] ?? array('label' => $review_pm['pm_status'], 'class' => ''); ?>
                <span class="mg-badge <?php echo $sb['class']; ?>"><?php echo $sb['label']; ?></span>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);text-transform:uppercase;">기간</div>
                <div style="font-size:0.85rem;">
                    <?php
                    if ($review_pm['pm_start_date']) echo date('y.m.d', strtotime($review_pm['pm_start_date']));
                    else echo '-';
                    echo ' ~ ';
                    if ($review_pm['pm_end_date']) echo date('y.m.d', strtotime($review_pm['pm_end_date']));
                    else echo '-';
                    ?>
                </div>
            </div>
            <div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);text-transform:uppercase;">보상</div>
                <div style="font-size:0.85rem;">
                    +<?php echo number_format($review_pm['pm_point']); ?>P
                    <?php if ((int)$review_pm['pm_bonus_point'] > 0) { ?>
                    <span style="color:var(--mg-accent);">(선정작 +<?php echo number_format($review_pm['pm_bonus_point']); ?>)</span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 통계 -->
<div class="mg-stats-grid" style="margin-bottom:1rem;">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 제출</div>
        <div class="mg-stat-value"><?php echo number_format($stat_total); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">대기</div>
        <div class="mg-stat-value" style="color:var(--mg-text-secondary);"><?php echo number_format($stat_submitted); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">승인</div>
        <div class="mg-stat-value" style="color:var(--mg-accent);"><?php echo number_format($stat_approved); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">반려</div>
        <div class="mg-stat-value" style="color:var(--mg-error);"><?php echo number_format($stat_rejected); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">보상완료</div>
        <div class="mg-stat-value" style="color:var(--mg-success);"><?php echo number_format($stat_rewarded); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">평균 글자수</div>
        <div class="mg-stat-value"><?php echo number_format($stat_avg_chars); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">총 보상액</div>
        <div class="mg-stat-value"><?php echo number_format($stat_total_reward); ?>P</div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">참여자</div>
        <div class="mg-stat-value"><?php echo number_format($stat_unique_members); ?>명</div>
    </div>
</div>

<!-- 상태 필터 탭 -->
<div class="mg-tabs" style="margin-bottom:1rem;">
    <a href="?mode=review&pm_id=<?php echo $pm_id; ?>" class="mg-tab <?php echo !$e_status ? 'active' : ''; ?>">전체 (<?php echo $stat_total; ?>)</a>
    <a href="?mode=review&pm_id=<?php echo $pm_id; ?>&e_status=submitted" class="mg-tab <?php echo $e_status == 'submitted' ? 'active' : ''; ?>">대기 (<?php echo $stat_submitted; ?>)</a>
    <a href="?mode=review&pm_id=<?php echo $pm_id; ?>&e_status=approved" class="mg-tab <?php echo $e_status == 'approved' ? 'active' : ''; ?>">승인 (<?php echo $stat_approved; ?>)</a>
    <a href="?mode=review&pm_id=<?php echo $pm_id; ?>&e_status=rejected" class="mg-tab <?php echo $e_status == 'rejected' ? 'active' : ''; ?>">반려 (<?php echo $stat_rejected; ?>)</a>
    <a href="?mode=review&pm_id=<?php echo $pm_id; ?>&e_status=rewarded" class="mg-tab <?php echo $e_status == 'rewarded' ? 'active' : ''; ?>">보상완료 (<?php echo $stat_rewarded; ?>)</a>
</div>

<!-- 일괄 액션 폼 -->
<form id="bulk-form" method="post" action="<?php echo $update_url; ?>">
    <input type="hidden" name="pm_id" value="<?php echo $pm_id; ?>">
    <input type="hidden" name="mode" id="bulk-mode" value="">

    <div style="display:flex;gap:0.5rem;margin-bottom:1rem;flex-wrap:wrap;">
        <button type="button" class="mg-btn mg-btn-success mg-btn-sm" onclick="bulkAction('approve')">선택 일괄 승인</button>
        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="bulkAction('bonus')">선택 선정작 지정</button>
        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="bulkAction('reward_all')">보상 일괄 지급</button>
        <?php if ($review_pm['pm_mode'] === 'vote') { ?>
        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="bulkAction('vote_settle')" style="background:var(--mg-accent);color:var(--mg-bg-primary);">투표 정산</button>
        <span style="font-size:0.75rem;color:var(--mg-text-muted);align-self:center;">추천수 상위 <?php echo (int)$review_pm['pm_bonus_count']; ?>명 보너스 + 전원 기본 보상</span>
        <?php } ?>
    </div>

    <div class="mg-card">
        <div class="mg-card-body" style="padding:0;overflow-x:auto;">
            <table class="mg-table" style="min-width:900px;">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">
                            <input type="checkbox" id="chk-all" onclick="toggleAll(this)">
                        </th>
                        <th style="width:40px;">#</th>
                        <th>제목</th>
                        <th style="width:100px;">작성자</th>
                        <th style="width:70px;text-align:center;">글자수</th>
                        <th style="width:60px;text-align:center;">추천</th>
                        <th style="width:80px;text-align:center;">상태</th>
                        <th style="width:130px;">제출일</th>
                        <th style="width:160px;text-align:center;">관리</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)) { ?>
                    <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">제출된 항목이 없습니다.</td></tr>
                    <?php } else { foreach ($entries as $idx => $entry) {
                        $es = $entry_status_labels[$entry['pe_status']] ?? array('label' => $entry['pe_status'], 'class' => '');
                        $wr_url = G5_BBS_URL . '/board.php?bo_table=' . urlencode($entry['bo_table']) . '&wr_id=' . (int)$entry['wr_id'];
                    ?>
                    <tr>
                        <td style="text-align:center;">
                            <input type="checkbox" name="pe_id[]" value="<?php echo $entry['pe_id']; ?>" class="entry-chk">
                        </td>
                        <td style="color:var(--mg-text-muted);"><?php echo $entry['pe_id']; ?></td>
                        <td>
                            <?php if ((int)$entry['wr_id'] > 0) { ?>
                            <a href="<?php echo $wr_url; ?>" target="_blank" style="color:var(--mg-accent);"><?php echo htmlspecialchars($entry['wr_subject']); ?></a>
                            <?php } else { ?>
                            <span style="color:var(--mg-text-muted);"><?php echo htmlspecialchars($entry['wr_subject']); ?></span>
                            <?php } ?>
                            <?php if ($entry['pe_is_bonus']) { ?>
                            <span class="mg-badge mg-badge-warning" style="font-size:0.65rem;vertical-align:middle;margin-left:0.25rem;">선정작</span>
                            <?php } ?>
                            <?php if ($entry['pe_admin_memo']) { ?>
                            <br><small style="color:var(--mg-error);">반려 사유: <?php echo htmlspecialchars($entry['pe_admin_memo']); ?></small>
                            <?php } ?>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($entry['mb_nick'] ?: $entry['mb_id']); ?></strong>
                            <br><small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars($entry['mb_id']); ?></small>
                        </td>
                        <td style="text-align:center;"><?php echo number_format($entry['wr_char_count']); ?></td>
                        <td style="text-align:center;"><?php echo (int)$entry['like_count']; ?></td>
                        <td style="text-align:center;">
                            <span class="mg-badge <?php echo $es['class']; ?>"><?php echo $es['label']; ?></span>
                            <?php if ((int)$entry['pe_point'] > 0) { ?>
                            <br><small style="color:var(--mg-success);">+<?php echo number_format($entry['pe_point']); ?>P</small>
                            <?php } ?>
                        </td>
                        <td><small style="color:var(--mg-text-muted);"><?php echo $entry['pe_datetime']; ?></small></td>
                        <td style="text-align:center;">
                            <?php if ($entry['pe_status'] == 'submitted') { ?>
                            <button type="button" class="mg-btn mg-btn-success mg-btn-sm" onclick="approveEntry(<?php echo $entry['pe_id']; ?>)">승인</button>
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="rejectEntry(<?php echo $entry['pe_id']; ?>)">반려</button>
                            <?php } elseif ($entry['pe_status'] == 'approved') { ?>
                            <span style="font-size:0.75rem;color:var(--mg-text-muted);">승인됨</span>
                            <?php } elseif ($entry['pe_status'] == 'rewarded') { ?>
                            <span style="font-size:0.75rem;color:var(--mg-success);">보상완료</span>
                            <?php } else { ?>
                            <span style="font-size:0.75rem;color:var(--mg-error);">반려됨</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>
    </div>
</form>

<!-- 페이징 -->
<?php if ($entry_total_page > 1) { ?>
<div style="text-align:center;padding:1rem 0;">
    <?php for ($i = 1; $i <= $entry_total_page; $i++) { ?>
    <a href="?mode=review&pm_id=<?php echo $pm_id; ?>&e_status=<?php echo $e_status; ?>&epage=<?php echo $i; ?>"
       class="mg-btn <?php echo $i == $entry_page ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"
       style="min-width:32px;"><?php echo $i; ?></a>
    <?php } ?>
</div>
<?php } ?>

<!-- 반려 사유 모달 -->
<div id="reject-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:450px;max-height:90vh;overflow-y:auto;">
        <div class="mg-modal-header">
            <h3>반려 사유 입력</h3>
            <button type="button" class="mg-modal-close" onclick="closeRejectModal()">&times;</button>
        </div>
        <div class="mg-modal-body">
            <div class="mg-form-group">
                <label class="mg-form-label">반려 사유 (선택)</label>
                <textarea id="reject-memo" class="mg-form-input" rows="3" placeholder="반려 사유를 입력하세요. 유저에게 알림으로 전달됩니다."></textarea>
            </div>
        </div>
        <div class="mg-modal-footer">
            <button type="button" class="mg-btn mg-btn-secondary" onclick="closeRejectModal()">취소</button>
            <button type="button" class="mg-btn mg-btn-danger" onclick="submitReject()">반려 확인</button>
        </div>
    </div>
</div>

<script>
var _selfUrl = '<?php echo $self_url; ?>';
var _updateUrl = '<?php echo $update_url; ?>';
var _pmId = <?php echo $pm_id; ?>;
var _eStatus = '<?php echo $e_status; ?>';
var _rejectPeId = 0;

// 전체 선택
function toggleAll(el) {
    var chks = document.querySelectorAll('.entry-chk');
    for (var i = 0; i < chks.length; i++) {
        chks[i].checked = el.checked;
    }
}

// 일괄 액션
function bulkAction(action) {
    var checked = document.querySelectorAll('.entry-chk:checked');
    if (action !== 'reward_all' && action !== 'vote_settle' && checked.length === 0) {
        alert('항목을 선택해주세요.');
        return;
    }
    var msg = '';
    if (action === 'approve') msg = checked.length + '건을 일괄 승인하시겠습니까?';
    else if (action === 'bonus') msg = checked.length + '건을 선정작으로 지정하시겠습니까?';
    else if (action === 'reward_all') msg = '승인된 모든 엔트리에 보상을 일괄 지급하시겠습니까?';
    else if (action === 'vote_settle') msg = '추천수 기준 투표 정산을 진행하시겠습니까?\n상위 N명에게 보너스, 전원에게 기본 보상이 지급됩니다.';

    if (!confirm(msg)) return;
    document.getElementById('bulk-mode').value = action;
    document.getElementById('bulk-form').submit();
}

// 개별 승인 (AJAX)
function approveEntry(peId) {
    if (!confirm('이 제출을 승인하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('action', 'approve');
    fd.append('pe_id', peId);
    fetch(_selfUrl + '?ajax_entry_action=1', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
        else alert(data.message || '승인 실패');
    })
    .catch(function() { alert('요청 실패'); });
}

// 개별 반려 (모달)
function rejectEntry(peId) {
    _rejectPeId = peId;
    document.getElementById('reject-memo').value = '';
    document.getElementById('reject-modal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('reject-modal').style.display = 'none';
    _rejectPeId = 0;
}

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeRejectModal();
});

function submitReject() {
    if (!_rejectPeId) return;
    var memo = document.getElementById('reject-memo').value.trim();

    var fd = new FormData();
    fd.append('action', 'reject');
    fd.append('pe_id', _rejectPeId);
    fd.append('memo', memo);

    fetch(_selfUrl + '?ajax_entry_action=1', { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            closeRejectModal();
            location.reload();
        } else {
            alert(data.message || '반려 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}
</script>

<?php } ?>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php
require_once __DIR__.'/_tail.php';
?>
