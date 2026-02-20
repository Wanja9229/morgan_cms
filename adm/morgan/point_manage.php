<?php
/**
 * Morgan Edition - 포인트 관리
 */

$sub_menu = "800550";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 탭
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';

// 재화 설정값
$point_name = mg_config('point_name', 'G');
$point_unit = mg_config('point_unit', '');

// 검색
$sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
$stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// 회원 목록 (지급 탭)
if ($tab == 'give') {
    $rows = 20;
    $offset = ($page - 1) * $rows;

    // 검색 조건
    $where = "1";
    if ($stx) {
        $stx_escaped = sql_real_escape_string($stx);
        $where .= " AND (mb_id LIKE '%$stx_escaped%' OR mb_nick LIKE '%$stx_escaped%')";
    }

    // 전체 개수
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['member_table']} WHERE $where";
    $cnt_row = sql_fetch($sql);
    $member_total_count = isset($cnt_row['cnt']) ? (int)$cnt_row['cnt'] : 0;
    $member_total_page = ceil($member_total_count / $rows);

    // 회원 목록
    $sql = "SELECT mb_id, mb_nick, mb_point, mb_datetime
            FROM {$g5['member_table']}
            WHERE $where
            ORDER BY mb_datetime DESC
            LIMIT $offset, $rows";
    $member_result = sql_query($sql);
}

// 포인트 내역 (history 탭)
if ($tab == 'history') {
    $rows = 30;
    $offset = ($page - 1) * $rows;

    // 검색 조건
    $where = "1";
    if ($stx) {
        $stx_escaped = sql_real_escape_string($stx);
        if ($sfl == 'mb_id') {
            $where .= " AND p.mb_id = '$stx_escaped'";
        } elseif ($sfl == 'content') {
            $where .= " AND p.po_content LIKE '%$stx_escaped%'";
        } else {
            $where .= " AND (p.mb_id = '$stx_escaped' OR p.po_content LIKE '%$stx_escaped%')";
        }
    }

    // 전체 개수
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['point_table']} p WHERE $where";
    $cnt_row = sql_fetch($sql);
    $total_count = isset($cnt_row['cnt']) ? (int)$cnt_row['cnt'] : 0;
    $total_page = ceil($total_count / $rows);

    // 목록
    $sql = "SELECT p.*, m.mb_nick
            FROM {$g5['point_table']} p
            LEFT JOIN {$g5['member_table']} m ON p.mb_id = m.mb_id
            WHERE $where
            ORDER BY p.po_id DESC
            LIMIT $offset, $rows";
    $history_result = sql_query($sql);
}

$g5['title'] = '포인트 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 탭 -->
<div style="margin-bottom:1rem;display:flex;gap:0.5rem;">
    <a href="?tab=settings" class="mg-btn <?php echo $tab == 'settings' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">재화 설정</a>
    <a href="?tab=give" class="mg-btn <?php echo $tab == 'give' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">포인트 지급</a>
    <a href="?tab=history" class="mg-btn <?php echo $tab == 'history' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">포인트 내역</a>
</div>

<?php if ($tab == 'settings') { ?>
<!-- 재화 설정 탭 -->
<form name="fsettings" id="fsettings" method="post" action="./point_manage_update.php">
    <input type="hidden" name="mode" value="settings">

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">재화 설정</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="point_name">재화 명칭</label>
                    <input type="text" name="point_name" id="point_name" value="<?php echo htmlspecialchars($point_name); ?>" class="mg-form-input" placeholder="G">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">
                        예: G, 골드, 포인트, 젬 등
                    </div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="point_unit">재화 단위</label>
                    <input type="text" name="point_unit" id="point_unit" value="<?php echo htmlspecialchars($point_unit); ?>" class="mg-form-input" placeholder="(선택사항)">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">
                        명칭 뒤에 붙는 단위 (예: 원, 개)
                    </div>
                </div>
            </div>

            <div class="mg-alert mg-alert-info" style="margin-top:1rem;">
                <strong>표시 예시:</strong>
                <span id="point_preview" style="margin-left:0.5rem;font-weight:bold;color:var(--mg-accent);">1,000 <?php echo htmlspecialchars($point_name . $point_unit); ?></span>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var nameInput = document.getElementById('point_name');
    var unitInput = document.getElementById('point_unit');
    var preview = document.getElementById('point_preview');

    function updatePreview() {
        var name = nameInput.value || 'G';
        var unit = unitInput.value || '';
        preview.textContent = '1,000 ' + name + unit;
    }

    nameInput.addEventListener('input', updatePreview);
    unitInput.addEventListener('input', updatePreview);
});
</script>

<?php } elseif ($tab == 'give') { ?>
<!-- 포인트 지급 탭 -->

<!-- 회원 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="tab" value="give">
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:250px;" placeholder="회원ID 또는 닉네임 검색">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <?php if ($stx) { ?>
            <a href="?tab=give" class="mg-btn mg-btn-secondary">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 회원 목록 -->
<div class="mg-card">
    <div class="mg-card-header">
        회원 목록
        <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:1rem;">
            총 <?php echo number_format($member_total_count); ?>명
        </span>
    </div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:120px;">회원ID</th>
                    <th style="width:120px;">닉네임</th>
                    <th style="width:120px;text-align:right;">보유 포인트</th>
                    <th style="width:150px;">가입일</th>
                    <th style="width:200px;">포인트 지급</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($mb = sql_fetch_array($member_result)) { ?>
                <tr id="row_<?php echo $mb['mb_id']; ?>">
                    <td><?php echo htmlspecialchars($mb['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($mb['mb_nick']); ?></td>
                    <td style="text-align:right;">
                        <span id="point_<?php echo $mb['mb_id']; ?>" style="color:var(--mg-accent);font-weight:500;">
                            <?php echo number_format($mb['mb_point']); ?>
                        </span> <?php echo htmlspecialchars($point_name . $point_unit); ?>
                    </td>
                    <td style="color:var(--mg-text-muted);"><?php echo substr($mb['mb_datetime'], 0, 10); ?></td>
                    <td>
                        <form class="point-form" style="display:flex;gap:0.25rem;" onsubmit="return givePoint(this);">
                            <input type="hidden" name="mb_id" value="<?php echo htmlspecialchars($mb['mb_id']); ?>">
                            <input type="number" name="po_point" class="mg-form-input" style="width:80px;padding:0.25rem 0.5rem;" placeholder="포인트" required>
                            <input type="text" name="po_content" class="mg-form-input" style="width:100px;padding:0.25rem 0.5rem;" placeholder="사유" value="관리자 지급">
                            <button type="submit" class="mg-btn mg-btn-primary" style="padding:0.25rem 0.5rem;font-size:0.75rem;">지급</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($member_total_count == 0) { ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">회원이 없습니다.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($member_total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $start_page_num = max(1, $page - 2);
    $end_page_num = min($member_total_page, $page + 2);
    $base_url = '?tab=give&stx='.urlencode($stx).'&page=';

    if ($page > 1) {
        echo '<a href="'.$base_url.'1">&laquo;</a>';
        echo '<a href="'.$base_url.($page-1).'">&lsaquo;</a>';
    }

    for ($i = $start_page_num; $i <= $end_page_num; $i++) {
        if ($i == $page) {
            echo '<span class="active">'.$i.'</span>';
        } else {
            echo '<a href="'.$base_url.$i.'">'.$i.'</a>';
        }
    }

    if ($page < $member_total_page) {
        echo '<a href="'.$base_url.($page+1).'">&rsaquo;</a>';
        echo '<a href="'.$base_url.$member_total_page.'">&raquo;</a>';
    }
    ?>
</div>
<?php } ?>

<script>
function givePoint(form) {
    var mb_id = form.mb_id.value;
    var po_point = form.po_point.value;
    var po_content = form.po_content.value;

    if (!po_point || po_point == 0) {
        alert('포인트를 입력하세요.');
        return false;
    }

    var action = parseInt(po_point) > 0 ? '지급' : '차감';
    if (!confirm(mb_id + ' 회원에게 ' + po_point + ' 포인트를 ' + action + '하시겠습니까?\n사유: ' + po_content)) {
        return false;
    }

    // AJAX 전송
    var formData = new FormData();
    formData.append('mode', 'give_ajax');
    formData.append('mb_id', mb_id);
    formData.append('po_point', po_point);
    formData.append('po_content', po_content);

    fetch('./point_manage_update.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            // 포인트 표시 업데이트
            document.getElementById('point_' + mb_id).textContent = data.new_point;
            form.po_point.value = '';
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(error => {
        alert('오류가 발생했습니다.');
        console.error(error);
    });

    return false;
}
</script>

<?php } else { ?>
<!-- 포인트 내역 탭 -->

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="tab" value="history">
            <select name="sfl" class="mg-form-input" style="width:auto;">
                <option value="">전체</option>
                <option value="mb_id" <?php echo $sfl == 'mb_id' ? 'selected' : ''; ?>>회원ID</option>
                <option value="content" <?php echo $sfl == 'content' ? 'selected' : ''; ?>>내용</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="mg-form-input" style="width:200px;" placeholder="검색어">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <?php if ($stx) { ?>
            <a href="?tab=history" class="mg-btn mg-btn-secondary">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 포인트 내역 테이블 -->
<div class="mg-card">
    <div class="mg-card-header">
        포인트 내역
        <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:1rem;">
            총 <?php echo number_format($total_count); ?>건
        </span>
    </div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:50px;">번호</th>
                    <th style="width:100px;">회원ID</th>
                    <th style="width:100px;">닉네임</th>
                    <th>내용</th>
                    <th style="width:100px;text-align:right;">포인트</th>
                    <th style="width:150px;">일시</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $num = $total_count - $offset;
                while ($row = sql_fetch_array($history_result)) {
                    $point_class = $row['po_point'] >= 0 ? 'color:var(--mg-success);' : 'color:var(--mg-error);';
                    $point_prefix = $row['po_point'] >= 0 ? '+' : '';
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $num--; ?></td>
                    <td><?php echo htmlspecialchars($row['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['mb_nick']); ?></td>
                    <td><?php echo htmlspecialchars($row['po_content']); ?></td>
                    <td style="text-align:right;<?php echo $point_class; ?>"><?php echo $point_prefix . number_format($row['po_point']); ?>P</td>
                    <td style="color:var(--mg-text-muted);"><?php echo $row['po_datetime']; ?></td>
                </tr>
                <?php } ?>
                <?php if ($total_count == 0) { ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">포인트 내역이 없습니다.</td>
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
    $start_page_num = max(1, $page - 2);
    $end_page_num = min($total_page, $page + 2);
    $base_url = '?tab=history&sfl='.$sfl.'&stx='.urlencode($stx).'&page=';

    if ($page > 1) {
        echo '<a href="'.$base_url.'1">&laquo;</a>';
        echo '<a href="'.$base_url.($page-1).'">&lsaquo;</a>';
    }

    for ($i = $start_page_num; $i <= $end_page_num; $i++) {
        if ($i == $page) {
            echo '<span class="active">'.$i.'</span>';
        } else {
            echo '<a href="'.$base_url.$i.'">'.$i.'</a>';
        }
    }

    if ($page < $total_page) {
        echo '<a href="'.$base_url.($page+1).'">&rsaquo;</a>';
        echo '<a href="'.$base_url.$total_page.'">&raquo;</a>';
    }
    ?>
</div>
<?php } ?>

<?php } ?>

<?php
require_once __DIR__.'/_tail.php';
?>
