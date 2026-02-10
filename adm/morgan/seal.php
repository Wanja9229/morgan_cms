<?php
/**
 * Morgan Edition - 관리자 인장 관리
 */
$sub_menu = '801300';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// AJAX: 인장 상세
if (isset($_GET['ajax_seal'])) {
    header('Content-Type: application/json; charset=utf-8');
    $mb_id = $_GET['mb_id'] ?? '';
    if (!$mb_id) { echo json_encode(array('success' => false)); exit; }
    $mb_esc = sql_real_escape_string($mb_id);
    $seal = sql_fetch("SELECT s.*, m.mb_nick FROM {$g5['mg_seal_table']} s
        JOIN {$g5['member_table']} m ON s.mb_id = m.mb_id
        WHERE s.mb_id = '{$mb_esc}'");
    if (!$seal) { echo json_encode(array('success' => false)); exit; }
    $seal['preview_html'] = mg_render_seal($mb_id, 'full');
    echo json_encode(array('success' => true, 'seal' => $seal));
    exit;
}

// AJAX: 인장 액션 (강제 OFF, 초기화, 삭제)
if (isset($_POST['seal_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    auth_check_menu($auth, $sub_menu, 'w');
    $action = $_POST['seal_action'];
    $mb_id = $_POST['mb_id'] ?? '';
    if (!$mb_id) { echo json_encode(array('success' => false, 'message' => '회원 ID가 없습니다.')); exit; }
    $mb_esc = sql_real_escape_string($mb_id);

    switch ($action) {
        case 'off':
            sql_query("UPDATE {$g5['mg_seal_table']} SET seal_use = 0, seal_update = NOW() WHERE mb_id = '{$mb_esc}'");
            echo json_encode(array('success' => true, 'message' => '인장을 비활성화했습니다.'));
            break;
        case 'on':
            sql_query("UPDATE {$g5['mg_seal_table']} SET seal_use = 1, seal_update = NOW() WHERE mb_id = '{$mb_esc}'");
            echo json_encode(array('success' => true, 'message' => '인장을 활성화했습니다.'));
            break;
        case 'reset':
            // 이미지 파일 삭제
            $seal = sql_fetch("SELECT seal_image FROM {$g5['mg_seal_table']} WHERE mb_id = '{$mb_esc}'");
            if ($seal && !empty($seal['seal_image']) && strpos($seal['seal_image'], 'http') !== 0) {
                @unlink(MG_SEAL_IMAGE_PATH . '/' . $seal['seal_image']);
            }
            sql_query("UPDATE {$g5['mg_seal_table']} SET
                seal_tagline = NULL, seal_content = NULL, seal_image = NULL,
                seal_link = NULL, seal_link_text = NULL, seal_text_color = NULL,
                seal_update = NOW()
                WHERE mb_id = '{$mb_esc}'");
            echo json_encode(array('success' => true, 'message' => '인장 내용을 초기화했습니다.'));
            break;
        case 'delete':
            $seal = sql_fetch("SELECT seal_image FROM {$g5['mg_seal_table']} WHERE mb_id = '{$mb_esc}'");
            if ($seal && !empty($seal['seal_image']) && strpos($seal['seal_image'], 'http') !== 0) {
                @unlink(MG_SEAL_IMAGE_PATH . '/' . $seal['seal_image']);
            }
            sql_query("DELETE FROM {$g5['mg_seal_table']} WHERE mb_id = '{$mb_esc}'");
            echo json_encode(array('success' => true, 'message' => '인장을 삭제했습니다.'));
            break;
        default:
            echo json_encode(array('success' => false, 'message' => '알 수 없는 액션입니다.'));
    }
    exit;
}

// 목록 조회
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where = "1";
if ($search) {
    $s = sql_real_escape_string($search);
    $where .= " AND (m.mb_id LIKE '%{$s}%' OR m.mb_nick LIKE '%{$s}%' OR s.seal_tagline LIKE '%{$s}%')";
}

$total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_seal_table']} s
    JOIN {$g5['member_table']} m ON s.mb_id = m.mb_id WHERE {$where}");
$total_count = (int)$total['cnt'];
$total_pages = max(1, ceil($total_count / $per_page));

$result = sql_query("SELECT s.*, m.mb_nick FROM {$g5['mg_seal_table']} s
    JOIN {$g5['member_table']} m ON s.mb_id = m.mb_id
    WHERE {$where}
    ORDER BY s.seal_update DESC
    LIMIT {$offset}, {$per_page}");

$seals = array();
while ($row = sql_fetch_array($result)) {
    $seals[] = $row;
}

$_update_url = G5_ADMIN_URL . '/morgan/seal.php';

include_once('./_head.php');
?>

<div class="mg-page-header">
    <h2 class="mg-page-title">인장 관리</h2>
    <p class="mg-page-desc">회원 인장(시그니처 카드) 목록 조회 및 관리</p>
</div>

<!-- 검색 -->
<div class="mg-card mb-4">
    <div class="mg-card-body">
        <form method="get" class="flex gap-2">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="회원ID, 닉네임, 한마디 검색..." class="mg-form-input" style="max-width:300px;">
            <button type="submit" class="mg-btn mg-btn-primary">검색</button>
            <?php if ($search) { ?>
            <a href="<?php echo $_update_url; ?>" class="mg-btn mg-btn-secondary">초기화</a>
            <?php } ?>
        </form>
    </div>
</div>

<!-- 인장 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>회원</th>
                    <th>한마디</th>
                    <th style="width:60px;">이미지</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:130px;">수정일</th>
                    <th style="width:160px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($seals) == 0) { ?>
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:#949ba4;">등록된 인장이 없습니다.</td></tr>
                <?php } ?>
                <?php foreach ($seals as $s) { ?>
                <tr>
                    <td><?php echo $s['seal_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($s['mb_nick']); ?></strong>
                        <span style="color:#949ba4;font-size:12px;">(<?php echo $s['mb_id']; ?>)</span>
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?php echo htmlspecialchars(mb_strimwidth($s['seal_tagline'] ?: '-', 0, 40, '...')); ?>
                    </td>
                    <td style="text-align:center;">
                        <?php echo $s['seal_image'] ? '<span style="color:#22c55e;">O</span>' : '<span style="color:#949ba4;">-</span>'; ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($s['seal_use']) { ?>
                        <span class="mg-badge mg-badge-success">ON</span>
                        <?php } else { ?>
                        <span class="mg-badge mg-badge-secondary">OFF</span>
                        <?php } ?>
                    </td>
                    <td><?php echo date('Y-m-d H:i', strtotime($s['seal_update'])); ?></td>
                    <td>
                        <button type="button" onclick="viewSeal('<?php echo $s['mb_id']; ?>')" class="mg-btn mg-btn-sm mg-btn-secondary">보기</button>
                        <?php if ($s['seal_use']) { ?>
                        <button type="button" onclick="sealAction('off','<?php echo $s['mb_id']; ?>')" class="mg-btn mg-btn-sm mg-btn-danger">OFF</button>
                        <?php } else { ?>
                        <button type="button" onclick="sealAction('on','<?php echo $s['mb_id']; ?>')" class="mg-btn mg-btn-sm mg-btn-primary">ON</button>
                        <?php } ?>
                        <button type="button" onclick="sealAction('reset','<?php echo $s['mb_id']; ?>')" class="mg-btn mg-btn-sm mg-btn-secondary" title="내용 초기화">초기화</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 페이징 -->
<?php if ($total_pages > 1) { ?>
<div class="mg-pagination" style="margin-top:1rem;">
    <?php for ($i = 1; $i <= $total_pages; $i++) {
        $active = $i == $page ? ' mg-page-active' : '';
        $qs = $search ? '&q='.urlencode($search) : '';
    ?>
    <a href="?page=<?php echo $i . $qs; ?>" class="mg-page-link<?php echo $active; ?>"><?php echo $i; ?></a>
    <?php } ?>
</div>
<?php } ?>

<!-- 인장 미리보기 모달 -->
<div id="seal-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:600px;">
        <div class="mg-modal-header">
            <h3 id="seal-modal-title">인장 미리보기</h3>
            <button type="button" onclick="closeSealModal()" class="mg-modal-close">&times;</button>
        </div>
        <div class="mg-modal-body">
            <div id="seal-preview-area" style="padding:1rem;background:#1e1f22;border-radius:8px;"></div>
            <div id="seal-detail-info" style="margin-top:1rem;font-size:13px;color:#949ba4;"></div>
        </div>
        <div class="mg-modal-footer">
            <button type="button" onclick="closeSealModal()" class="mg-btn mg-btn-secondary">닫기</button>
        </div>
    </div>
</div>

<script>
var _ajaxUrl = '<?php echo $_update_url; ?>';

function viewSeal(mbId) {
    fetch(_ajaxUrl + '?ajax_seal=1&mb_id=' + encodeURIComponent(mbId))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success) return alert('인장 데이터를 불러올 수 없습니다.');
            var s = data.seal;
            document.getElementById('seal-modal-title').textContent = s.mb_nick + '의 인장';
            document.getElementById('seal-preview-area').innerHTML = s.preview_html || '<p style="text-align:center;color:#949ba4;padding:1rem;">인장 내용이 없거나 비활성 상태입니다.</p>';
            var info = '<div>상태: ' + (s.seal_use == 1 ? '<strong style="color:#22c55e;">ON</strong>' : '<strong style="color:#949ba4;">OFF</strong>') + '</div>';
            info += '<div>수정일: ' + s.seal_update + '</div>';
            if (s.seal_image) info += '<div>이미지: ' + s.seal_image + '</div>';
            document.getElementById('seal-detail-info').innerHTML = info;
            document.getElementById('seal-modal').style.display = 'flex';
        });
}

function closeSealModal() {
    document.getElementById('seal-modal').style.display = 'none';
}

function sealAction(action, mbId) {
    var labels = { off: '비활성화', on: '활성화', reset: '내용 초기화', 'delete': '삭제' };
    if (!confirm('"' + mbId + '"의 인장을 ' + labels[action] + '하시겠습니까?')) return;

    var fd = new FormData();
    fd.set('seal_action', action);
    fd.set('mb_id', mbId);

    fetch(_ajaxUrl, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            alert(data.message);
            if (data.success) location.reload();
        });
}
</script>

<?php
include_once('./_tail.php');
?>
