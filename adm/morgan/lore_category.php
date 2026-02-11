<?php
/**
 * Morgan Edition - 위키 카테고리 관리
 */

$sub_menu = "801400";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// === POST 처리 ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');
    auth_check_menu($auth, $sub_menu, 'w');

    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

    // 카테고리 추가
    if ($mode == 'add') {
        $lc_name = sql_real_escape_string(trim($_POST['lc_name']));
        $lc_order = (int)$_POST['lc_order'];
        $lc_use = isset($_POST['lc_use']) ? 1 : 0;

        if (!$lc_name) {
            alert('카테고리명을 입력해주세요.');
        }

        // 중복 체크
        $exists = sql_fetch("SELECT lc_id FROM {$g5['mg_lore_category_table']} WHERE lc_name = '{$lc_name}'");
        if ($exists['lc_id']) {
            alert('이미 존재하는 카테고리명입니다.');
        }

        sql_query("INSERT INTO {$g5['mg_lore_category_table']} (lc_name, lc_order, lc_use) VALUES ('{$lc_name}', {$lc_order}, {$lc_use})");
        goto_url('./lore_category.php');
    }

    // 카테고리 수정
    if ($mode == 'edit') {
        $lc_id = (int)$_POST['lc_id'];
        $lc_name = sql_real_escape_string(trim($_POST['lc_name']));
        $lc_order = (int)$_POST['lc_order'];
        $lc_use = isset($_POST['lc_use']) ? 1 : 0;

        if (!$lc_name) {
            alert('카테고리명을 입력해주세요.');
        }
        if (!$lc_id) {
            alert('잘못된 요청입니다.');
        }

        // 중복 체크 (자기 자신 제외)
        $exists = sql_fetch("SELECT lc_id FROM {$g5['mg_lore_category_table']} WHERE lc_name = '{$lc_name}' AND lc_id != {$lc_id}");
        if ($exists['lc_id']) {
            alert('이미 존재하는 카테고리명입니다.');
        }

        sql_query("UPDATE {$g5['mg_lore_category_table']} SET lc_name = '{$lc_name}', lc_order = {$lc_order}, lc_use = {$lc_use} WHERE lc_id = {$lc_id}");
        goto_url('./lore_category.php');
    }

    // 카테고리 삭제
    if ($mode == 'delete') {
        $lc_id = (int)$_POST['lc_id'];
        if (!$lc_id) {
            alert('잘못된 요청입니다.');
        }

        // 문서가 있는지 확인
        $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_lore_article_table']} WHERE lc_id = {$lc_id}");
        if ((int)$cnt['cnt'] > 0) {
            alert('해당 카테고리에 문서가 존재하여 삭제할 수 없습니다. 문서를 먼저 삭제하거나 다른 카테고리로 이동해주세요.');
        }

        sql_query("DELETE FROM {$g5['mg_lore_category_table']} WHERE lc_id = {$lc_id}");
        goto_url('./lore_category.php');
    }

    goto_url('./lore_category.php');
}

// === 카테고리 목록 ===
$categories = array();
$result = sql_query("SELECT * FROM {$g5['mg_lore_category_table']} ORDER BY lc_order, lc_id");
while ($row = sql_fetch_array($result)) {
    $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_lore_article_table']} WHERE lc_id = {$row['lc_id']}");
    $row['article_count'] = (int)$cnt['cnt'];
    $categories[] = $row;
}

$g5['title'] = '위키 카테고리 관리';
require_once __DIR__.'/_head.php';
?>

<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            위키 카테고리 관리
            <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:1rem;">
                세계관 위키 문서를 분류할 카테고리를 관리합니다.
            </span>
        </div>
        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="showAddForm()">+ 카테고리 추가</button>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <!-- 추가 폼 (기본 숨김) -->
        <div id="add-form" style="display:none;padding:1rem;border-bottom:1px solid var(--mg-bg-tertiary);background:var(--mg-bg-primary);">
            <form method="post" action="">
                <input type="hidden" name="mode" value="add">
                <div style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;">
                    <div class="mg-form-group" style="margin-bottom:0;flex:1;min-width:200px;">
                        <label class="mg-form-label">카테고리명 *</label>
                        <input type="text" name="lc_name" class="mg-form-input" placeholder="카테고리명 입력" required>
                    </div>
                    <div class="mg-form-group" style="margin-bottom:0;width:100px;">
                        <label class="mg-form-label">정렬순서</label>
                        <input type="number" name="lc_order" value="0" class="mg-form-input" style="text-align:center;">
                    </div>
                    <div class="mg-form-group" style="margin-bottom:0;display:flex;align-items:center;gap:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                            <input type="checkbox" name="lc_use" value="1" checked>
                            사용
                        </label>
                    </div>
                    <div style="display:flex;gap:0.5rem;">
                        <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">추가</button>
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="hideAddForm()">취소</button>
                    </div>
                </div>
            </form>
        </div>

        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:60px;text-align:center;">ID</th>
                    <th>카테고리명</th>
                    <th style="width:100px;text-align:center;">정렬순서</th>
                    <th style="width:80px;text-align:center;">사용여부</th>
                    <th style="width:80px;text-align:center;">문서수</th>
                    <th style="width:200px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)) { ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">등록된 카테고리가 없습니다.</td>
                </tr>
                <?php } else { foreach ($categories as $cat) { ?>
                <tr id="row-<?php echo $cat['lc_id']; ?>">
                    <!-- 보기 모드 -->
                    <td class="view-mode" style="text-align:center;color:var(--mg-text-muted);"><?php echo $cat['lc_id']; ?></td>
                    <td class="view-mode"><strong><?php echo htmlspecialchars($cat['lc_name']); ?></strong></td>
                    <td class="view-mode" style="text-align:center;"><?php echo $cat['lc_order']; ?></td>
                    <td class="view-mode" style="text-align:center;">
                        <?php if ($cat['lc_use']) { ?>
                        <span style="color:var(--mg-success);">&check; 사용</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">&cross; 미사용</span>
                        <?php } ?>
                    </td>
                    <td class="view-mode" style="text-align:center;">
                        <?php if ($cat['article_count'] > 0) { ?>
                        <a href="./lore_article.php?lc_id=<?php echo $cat['lc_id']; ?>" style="color:var(--mg-accent);"><?php echo $cat['article_count']; ?></a>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">0</span>
                        <?php } ?>
                    </td>
                    <td class="view-mode" style="text-align:center;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="showEditForm(<?php echo $cat['lc_id']; ?>)">수정</button>
                        <?php if ($cat['article_count'] == 0) { ?>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="mode" value="delete">
                            <input type="hidden" name="lc_id" value="<?php echo $cat['lc_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('카테고리를 삭제하시겠습니까?');">삭제</button>
                        </form>
                        <?php } else { ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" disabled style="opacity:0.4;cursor:not-allowed;" title="문서가 있어 삭제 불가">삭제</button>
                        <?php } ?>
                    </td>

                    <!-- 수정 모드 (기본 숨김) -->
                    <td class="edit-mode" colspan="6" style="display:none;">
                        <form method="post" action="" style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                            <input type="hidden" name="mode" value="edit">
                            <input type="hidden" name="lc_id" value="<?php echo $cat['lc_id']; ?>">
                            <span style="color:var(--mg-text-muted);font-size:0.875rem;min-width:40px;">ID: <?php echo $cat['lc_id']; ?></span>
                            <input type="text" name="lc_name" value="<?php echo htmlspecialchars($cat['lc_name']); ?>" class="mg-form-input" style="flex:1;min-width:180px;" required>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <label class="mg-form-label" style="margin-bottom:0;white-space:nowrap;">순서</label>
                                <input type="number" name="lc_order" value="<?php echo $cat['lc_order']; ?>" class="mg-form-input" style="width:80px;text-align:center;">
                            </div>
                            <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);white-space:nowrap;">
                                <input type="checkbox" name="lc_use" value="1" <?php echo $cat['lc_use'] ? 'checked' : ''; ?>>
                                사용
                            </label>
                            <div style="display:flex;gap:0.5rem;">
                                <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">저장</button>
                                <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="hideEditForm(<?php echo $cat['lc_id']; ?>)">취소</button>
                            </div>
                        </form>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showAddForm() {
    document.getElementById('add-form').style.display = '';
}
function hideAddForm() {
    document.getElementById('add-form').style.display = 'none';
}

function showEditForm(lcId) {
    var row = document.getElementById('row-' + lcId);
    if (!row) return;
    var viewCells = row.querySelectorAll('.view-mode');
    var editCells = row.querySelectorAll('.edit-mode');
    viewCells.forEach(function(el) { el.style.display = 'none'; });
    editCells.forEach(function(el) { el.style.display = ''; });
}

function hideEditForm(lcId) {
    var row = document.getElementById('row-' + lcId);
    if (!row) return;
    var viewCells = row.querySelectorAll('.view-mode');
    var editCells = row.querySelectorAll('.edit-mode');
    viewCells.forEach(function(el) { el.style.display = ''; });
    editCells.forEach(function(el) { el.style.display = 'none'; });
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
