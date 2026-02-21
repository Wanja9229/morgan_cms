<?php
/**
 * Morgan Edition - 위키 관리 (통합)
 * 탭: 카테고리 / 문서 / 순서 관리
 */

$sub_menu = "800160";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 탭 라우팅
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'categories';
if (!in_array($tab, array('categories', 'articles', 'ordering'))) $tab = 'categories';

// ============================================================
// POST 처리
// ============================================================

// --- 카테고리 탭 POST ---
if ($tab == 'categories' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');
    auth_check_menu($auth, $sub_menu, 'w');

    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

    if ($mode == 'add') {
        $lc_name = sql_real_escape_string(trim($_POST['lc_name']));
        $lc_order = (int)$_POST['lc_order'];
        $lc_use = isset($_POST['lc_use']) ? 1 : 0;

        if (!$lc_name) alert('카테고리명을 입력해주세요.');

        $exists = sql_fetch("SELECT lc_id FROM {$g5['mg_lore_category_table']} WHERE lc_name = '{$lc_name}'");
        if ($exists['lc_id']) alert('이미 존재하는 카테고리명입니다.');

        sql_query("INSERT INTO {$g5['mg_lore_category_table']} (lc_name, lc_order, lc_use) VALUES ('{$lc_name}', {$lc_order}, {$lc_use})");
        goto_url('./lore_wiki.php?tab=categories');
    }

    if ($mode == 'edit') {
        $lc_id = (int)$_POST['lc_id'];
        $lc_name = sql_real_escape_string(trim($_POST['lc_name']));
        $lc_order = (int)$_POST['lc_order'];
        $lc_use = isset($_POST['lc_use']) ? 1 : 0;

        if (!$lc_name) alert('카테고리명을 입력해주세요.');
        if (!$lc_id) alert('잘못된 요청입니다.');

        $exists = sql_fetch("SELECT lc_id FROM {$g5['mg_lore_category_table']} WHERE lc_name = '{$lc_name}' AND lc_id != {$lc_id}");
        if ($exists['lc_id']) alert('이미 존재하는 카테고리명입니다.');

        sql_query("UPDATE {$g5['mg_lore_category_table']} SET lc_name = '{$lc_name}', lc_order = {$lc_order}, lc_use = {$lc_use} WHERE lc_id = {$lc_id}");
        goto_url('./lore_wiki.php?tab=categories');
    }

    if ($mode == 'delete') {
        $lc_id = (int)$_POST['lc_id'];
        if (!$lc_id) alert('잘못된 요청입니다.');

        $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_lore_article_table']} WHERE lc_id = {$lc_id}");
        if ((int)$cnt['cnt'] > 0) {
            alert('해당 카테고리에 문서가 존재하여 삭제할 수 없습니다. 문서를 먼저 삭제하거나 다른 카테고리로 이동해주세요.');
        }

        sql_query("DELETE FROM {$g5['mg_lore_category_table']} WHERE lc_id = {$lc_id}");
        goto_url('./lore_wiki.php?tab=categories');
    }

    goto_url('./lore_wiki.php?tab=categories');
}

// --- 문서 탭 POST (삭제) ---
if ($tab == 'articles' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] == 'delete') {
    if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');
    auth_check_menu($auth, $sub_menu, 'w');

    $la_id = (int)$_POST['la_id'];
    if ($la_id) {
        $sec_result = sql_query("SELECT ls_image FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id} AND ls_image != '' AND ls_image IS NOT NULL");
        while ($sec = sql_fetch_array($sec_result)) {
            if ($sec['ls_image']) {
                $img_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $sec['ls_image']);
                if (file_exists($img_path)) @unlink($img_path);
            }
        }
        $art = sql_fetch("SELECT la_thumbnail FROM {$g5['mg_lore_article_table']} WHERE la_id = {$la_id}");
        if ($art['la_thumbnail']) {
            $thumb_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $art['la_thumbnail']);
            if (file_exists($thumb_path)) @unlink($thumb_path);
        }
        sql_query("DELETE FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id}");
        sql_query("DELETE FROM {$g5['mg_lore_article_table']} WHERE la_id = {$la_id}");
    }
    goto_url('./lore_wiki.php?tab=articles');
}

// --- 순서 관리 탭 AJAX ---
if ($tab == 'ordering' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($is_admin != 'super') {
        header('Content-Type: application/json');
        die(json_encode(array('success' => false, 'message' => '권한이 없습니다.')));
    }
    auth_check_menu($auth, $sub_menu, 'w');
    header('Content-Type: application/json; charset=utf-8');

    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';

    if ($mode == 'category_reorder') {
        $order = isset($_POST['order']) ? $_POST['order'] : array();
        if (!is_array($order) || empty($order)) {
            echo json_encode(array('success' => false, 'message' => '순서 데이터가 없습니다.'));
            exit;
        }
        foreach ($order as $i => $lc_id) {
            $lc_id = (int)$lc_id;
            if ($lc_id > 0) {
                sql_query("UPDATE {$g5['mg_lore_category_table']} SET lc_order = {$i} WHERE lc_id = {$lc_id}");
            }
        }
        echo json_encode(array('success' => true));
        exit;
    }

    if ($mode == 'article_reorder') {
        $order = isset($_POST['order']) ? $_POST['order'] : array();
        if (!is_array($order) || empty($order)) {
            echo json_encode(array('success' => false, 'message' => '순서 데이터가 없습니다.'));
            exit;
        }
        foreach ($order as $i => $la_id) {
            $la_id = (int)$la_id;
            if ($la_id > 0) {
                sql_query("UPDATE {$g5['mg_lore_article_table']} SET la_order = {$i} WHERE la_id = {$la_id}");
            }
        }
        echo json_encode(array('success' => true));
        exit;
    }

    echo json_encode(array('success' => false, 'message' => '알 수 없는 요청'));
    exit;
}

// --- 순서 관리 탭 AJAX: 카테고리별 문서 목록 ---
if ($tab == 'ordering' && isset($_GET['ajax_articles'])) {
    header('Content-Type: application/json; charset=utf-8');
    $lc_id = isset($_GET['lc_id']) ? (int)$_GET['lc_id'] : 0;
    $articles = array();
    if ($lc_id > 0) {
        $result = sql_query("SELECT la_id, la_title, la_thumbnail, la_order FROM {$g5['mg_lore_article_table']} WHERE lc_id = {$lc_id} ORDER BY la_order, la_id");
        while ($row = sql_fetch_array($result)) {
            $articles[] = $row;
        }
    }
    echo json_encode(array('success' => true, 'articles' => $articles));
    exit;
}

// ============================================================
// 공통 데이터
// ============================================================

$categories = array();
$result = sql_query("SELECT * FROM {$g5['mg_lore_category_table']} ORDER BY lc_order, lc_id");
while ($row = sql_fetch_array($result)) {
    $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_lore_article_table']} WHERE lc_id = {$row['lc_id']}");
    $row['article_count'] = (int)$cnt['cnt'];
    $categories[] = $row;
}

// 문서 탭 데이터
$articles = array();
if ($tab == 'articles') {
    $filter_lc_id = isset($_GET['lc_id']) ? (int)$_GET['lc_id'] : 0;
    $where = "1=1";
    if ($filter_lc_id > 0) {
        $where .= " AND a.lc_id = {$filter_lc_id}";
    }
    $sql = "SELECT a.*, c.lc_name
            FROM {$g5['mg_lore_article_table']} a
            LEFT JOIN {$g5['mg_lore_category_table']} c ON a.lc_id = c.lc_id
            WHERE {$where}
            ORDER BY a.la_order, a.la_id DESC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $articles[] = $row;
    }
}

$g5['title'] = '위키 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 탭 바 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=categories" class="mg-tab <?php echo $tab == 'categories' ? 'active' : ''; ?>">카테고리</a>
    <a href="?tab=articles" class="mg-tab <?php echo $tab == 'articles' ? 'active' : ''; ?>">문서</a>
    <a href="?tab=ordering" class="mg-tab <?php echo $tab == 'ordering' ? 'active' : ''; ?>">순서 관리</a>
</div>

<?php
// ============================================================
// 카테고리 탭
// ============================================================
if ($tab == 'categories') { ?>

<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
        <div>
            카테고리 관리
            <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:0.5rem;">세계관 위키 문서를 분류할 카테고리를 관리합니다.</span>
        </div>
        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="showAddForm()">+ 카테고리 추가</button>
    </div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <!-- 추가 폼 -->
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
                <tr><td colspan="6" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">등록된 카테고리가 없습니다.</td></tr>
                <?php } else { foreach ($categories as $cat) { ?>
                <tr id="row-<?php echo $cat['lc_id']; ?>">
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
                        <a href="?tab=articles&lc_id=<?php echo $cat['lc_id']; ?>" style="color:var(--mg-accent);"><?php echo $cat['article_count']; ?></a>
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
function showAddForm() { document.getElementById('add-form').style.display = ''; }
function hideAddForm() { document.getElementById('add-form').style.display = 'none'; }
function showEditForm(lcId) {
    var row = document.getElementById('row-' + lcId);
    if (!row) return;
    row.querySelectorAll('.view-mode').forEach(function(el) { el.style.display = 'none'; });
    row.querySelectorAll('.edit-mode').forEach(function(el) { el.style.display = ''; });
}
function hideEditForm(lcId) {
    var row = document.getElementById('row-' + lcId);
    if (!row) return;
    row.querySelectorAll('.view-mode').forEach(function(el) { el.style.display = ''; });
    row.querySelectorAll('.edit-mode').forEach(function(el) { el.style.display = 'none'; });
}
</script>

<?php }

// ============================================================
// 문서 탭
// ============================================================
if ($tab == 'articles') {
    $filter_lc_id = isset($_GET['lc_id']) ? (int)$_GET['lc_id'] : 0;
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="?tab=articles&lc_id=0" class="mg-btn <?php echo !$filter_lc_id ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm">전체</a>
        <?php foreach ($categories as $cat) { ?>
        <a href="?tab=articles&lc_id=<?php echo $cat['lc_id']; ?>" class="mg-btn <?php echo $filter_lc_id == $cat['lc_id'] ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"><?php echo htmlspecialchars($cat['lc_name']); ?></a>
        <?php } ?>
    </div>
    <a href="./lore_article_edit.php" class="mg-btn mg-btn-primary">+ 문서 작성</a>
</div>

<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:800px;">
            <thead>
                <tr>
                    <th style="width:50px;text-align:center;">ID</th>
                    <th style="width:60px;text-align:center;">썸네일</th>
                    <th>제목</th>
                    <th style="width:100px;text-align:center;">카테고리</th>
                    <th style="width:60px;text-align:center;">공개</th>
                    <th style="width:80px;text-align:center;">조회수</th>
                    <th style="width:130px;text-align:center;">수정일</th>
                    <th style="width:140px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)) { ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 문서가 없습니다.
                        <br><a href="./lore_article_edit.php" style="color:var(--mg-accent);font-size:0.875rem;">첫 문서를 작성해보세요 &rarr;</a>
                    </td>
                </tr>
                <?php } else { foreach ($articles as $art) { ?>
                <tr>
                    <td style="text-align:center;color:var(--mg-text-muted);"><?php echo $art['la_id']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($art['la_thumbnail']) { ?>
                        <img src="<?php echo htmlspecialchars($art['la_thumbnail']); ?>" style="width:40px;height:40px;object-fit:cover;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">
                        <?php } else { ?>
                        <div style="width:40px;height:40px;background:var(--mg-bg-tertiary);border-radius:4px;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                            <span style="color:var(--mg-text-muted);font-size:0.6rem;">No img</span>
                        </div>
                        <?php } ?>
                    </td>
                    <td>
                        <a href="./lore_article_edit.php?la_id=<?php echo $art['la_id']; ?>" style="font-weight:600;"><?php echo htmlspecialchars($art['la_title']); ?></a>
                        <?php if ($art['la_subtitle']) { ?>
                        <br><small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars(mb_substr($art['la_subtitle'], 0, 40)); ?></small>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <span class="mg-badge"><?php echo htmlspecialchars($art['lc_name'] ?: '미지정'); ?></span>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($art['la_use']) { ?>
                        <span style="color:var(--mg-success);">&check;</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">&cross;</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;"><?php echo number_format($art['la_hit']); ?></td>
                    <td style="text-align:center;font-size:0.8rem;color:var(--mg-text-muted);">
                        <?php echo substr($art['la_updated'] ?? '', 0, 16); ?>
                    </td>
                    <td style="text-align:center;">
                        <a href="./lore_article_edit.php?la_id=<?php echo $art['la_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">수정</a>
                        <form method="post" action="?tab=articles" style="display:inline;">
                            <input type="hidden" name="mode" value="delete">
                            <input type="hidden" name="la_id" value="<?php echo $art['la_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('이 문서와 모든 섹션을 삭제하시겠습니까?');">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>

<?php }

// ============================================================
// 순서 관리 탭
// ============================================================
if ($tab == 'ordering') { ?>

<!-- 카테고리 순서 -->
<div class="mg-card" style="margin-bottom:1.5rem;">
    <div class="mg-card-header">카테고리 순서</div>
    <div class="mg-card-body" style="padding:0;">
        <?php if (empty($categories)) { ?>
        <div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">등록된 카테고리가 없습니다.</div>
        <?php } else { ?>
        <div id="cat-sortable">
            <?php foreach ($categories as $cat) { ?>
            <div class="cat-sortable-item" data-cat-id="<?php echo $cat['lc_id']; ?>" style="display:flex;align-items:center;gap:0.5rem 1rem;padding:0.75rem 1rem;border-bottom:1px solid var(--mg-bg-tertiary);background:var(--mg-bg-secondary);flex-wrap:wrap;">
                <span class="cat-drag-handle" style="cursor:grab;color:var(--mg-text-muted);font-size:1.1rem;user-select:none;min-width:44px;min-height:44px;display:flex;align-items:center;justify-content:center;" title="드래그하여 순서 변경">&#9776;</span>
                <strong style="flex:1;min-width:100px;"><?php echo htmlspecialchars($cat['lc_name']); ?></strong>
                <span style="color:var(--mg-text-muted);font-size:0.8rem;">문서 <?php echo $cat['article_count']; ?>개</span>
                <?php if ($cat['lc_use']) { ?>
                <span class="mg-badge mg-badge-success" style="font-size:0.7rem;">사용</span>
                <?php } else { ?>
                <span class="mg-badge mg-badge-error" style="font-size:0.7rem;">미사용</span>
                <?php } ?>
                <span class="mg-badge cat-order-badge" style="font-size:0.7rem;">순서: <?php echo $cat['lc_order']; ?></span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>

<!-- 카테고리별 문서 순서 -->
<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
        <span>문서 순서</span>
        <select id="order-cat-select" class="mg-form-input" style="width:auto;min-width:160px;max-width:100%;" onchange="loadCategoryArticles(this.value)">
            <option value="">카테고리 선택</option>
            <?php foreach ($categories as $cat) { ?>
            <option value="<?php echo $cat['lc_id']; ?>"><?php echo htmlspecialchars($cat['lc_name']); ?> (<?php echo $cat['article_count']; ?>)</option>
            <?php } ?>
        </select>
    </div>
    <div class="mg-card-body" id="article-order-body" style="padding:0;">
        <div style="text-align:center;padding:2rem;color:var(--mg-text-muted);">
            위에서 카테고리를 선택하면 해당 카테고리의 문서를 정렬할 수 있습니다.
        </div>
    </div>
</div>

<script>
/**
 * 범용 정렬 초기화 (마우스 + 터치 지원)
 * @param {HTMLElement} container - 정렬 컨테이너
 * @param {string} handleSel - 드래그 핸들 CSS 선택자
 * @param {string} itemSel - 정렬 아이템 CSS 선택자
 * @param {Function} saveFn - 정렬 완료 후 호출할 저장 함수(container)
 */
function initSortable(container, handleSel, itemSel, saveFn) {
    if (!container) return;

    var dragItem = null;
    var placeholder = document.createElement('div');
    placeholder.style.cssText = 'border:2px dashed var(--mg-accent);border-radius:4px;margin:0;min-height:44px;opacity:0.5;';

    // --- 마우스 Drag & Drop ---
    container.querySelectorAll(handleSel).forEach(function(handle) {
        var item = handle.closest(itemSel);
        handle.addEventListener('mousedown', function() { item.draggable = true; });
        item.addEventListener('dragstart', function(e) {
            dragItem = item;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
            setTimeout(function() { item.style.opacity = '0.4'; }, 0);
        });
        item.addEventListener('dragend', function() {
            item.draggable = false;
            item.style.opacity = '';
            dragItem = null;
            if (placeholder.parentNode) placeholder.remove();
            saveFn(container);
        });
    });

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        var target = e.target.closest(itemSel);
        if (!target || target === dragItem) return;
        var rect = target.getBoundingClientRect();
        if (e.clientY < rect.top + rect.height / 2) {
            container.insertBefore(placeholder, target);
        } else {
            container.insertBefore(placeholder, target.nextSibling);
        }
    });

    container.addEventListener('drop', function(e) {
        e.preventDefault();
        if (dragItem && placeholder.parentNode) {
            container.insertBefore(dragItem, placeholder);
            placeholder.remove();
        }
    });

    // --- 터치 정렬 ---
    var touchItem = null;
    var touchClone = null;
    var touchStartY = 0;
    var touchMoved = false;

    container.querySelectorAll(handleSel).forEach(function(handle) {
        handle.addEventListener('touchstart', function(e) {
            var item = handle.closest(itemSel);
            if (!item) return;
            touchItem = item;
            touchMoved = false;
            touchStartY = e.touches[0].clientY;

            // 고스트 복제
            touchClone = item.cloneNode(true);
            touchClone.style.cssText = 'position:fixed;left:0;right:0;z-index:9999;opacity:0.85;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,0.4);';
            var rect = item.getBoundingClientRect();
            touchClone.style.top = rect.top + 'px';
            touchClone.style.width = rect.width + 'px';
            document.body.appendChild(touchClone);

            item.style.opacity = '0.3';
        }, {passive: true});
    });

    container.addEventListener('touchmove', function(e) {
        if (!touchItem) return;
        e.preventDefault();
        touchMoved = true;

        var touch = e.touches[0];
        if (touchClone) touchClone.style.top = touch.clientY - 22 + 'px';

        // placeholder 위치 결정
        var elUnder = document.elementFromPoint(touch.clientX, touch.clientY);
        if (!elUnder) return;
        var target = elUnder.closest(itemSel);
        if (!target || target === touchItem || target === placeholder) return;
        // 같은 컨테이너인지 확인
        if (!container.contains(target)) return;

        var rect = target.getBoundingClientRect();
        if (touch.clientY < rect.top + rect.height / 2) {
            container.insertBefore(placeholder, target);
        } else {
            container.insertBefore(placeholder, target.nextSibling);
        }
    }, {passive: false});

    container.addEventListener('touchend', function() {
        if (!touchItem) return;
        if (touchMoved && placeholder.parentNode) {
            container.insertBefore(touchItem, placeholder);
            placeholder.remove();
        }
        touchItem.style.opacity = '';
        if (touchClone) { touchClone.remove(); touchClone = null; }
        if (touchMoved) saveFn(container);
        touchItem = null;
        touchMoved = false;
    });

    container.addEventListener('touchcancel', function() {
        if (touchItem) touchItem.style.opacity = '';
        if (touchClone) { touchClone.remove(); touchClone = null; }
        if (placeholder.parentNode) placeholder.remove();
        touchItem = null;
        touchMoved = false;
    });
}

// === 카테고리 순서 저장 ===
function saveCategoryOrder(container) {
    var items = container.querySelectorAll('.cat-sortable-item');
    var formData = new FormData();
    formData.append('mode', 'category_reorder');
    items.forEach(function(item, i) {
        formData.append('order[]', item.getAttribute('data-cat-id'));
        var badge = item.querySelector('.cat-order-badge');
        if (badge) badge.textContent = '순서: ' + i;
    });
    fetch('?tab=ordering', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) { if (!data.success) alert('순서 저장 실패: ' + (data.message || '')); })
        .catch(function() { alert('순서 저장 중 오류가 발생했습니다.'); });
}

// === 문서 순서 저장 ===
function saveArticleOrder(container) {
    var items = container.querySelectorAll('.art-sortable-item');
    var formData = new FormData();
    formData.append('mode', 'article_reorder');
    items.forEach(function(item, i) {
        formData.append('order[]', item.getAttribute('data-art-id'));
        var badge = item.querySelector('.art-order-badge');
        if (badge) badge.textContent = '순서: ' + i;
    });
    fetch('?tab=ordering', { method: 'POST', body: formData })
        .then(function(r) { return r.json(); })
        .then(function(data) { if (!data.success) alert('순서 저장 실패: ' + (data.message || '')); })
        .catch(function() { alert('순서 저장 중 오류가 발생했습니다.'); });
}

// 카테고리 정렬 초기화
initSortable(
    document.getElementById('cat-sortable'),
    '.cat-drag-handle', '.cat-sortable-item',
    saveCategoryOrder
);

// === 카테고리별 문서 순서 ===
function loadCategoryArticles(lcId) {
    var body = document.getElementById('article-order-body');
    if (!lcId) {
        body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--mg-text-muted);">위에서 카테고리를 선택하면 해당 카테고리의 문서를 정렬할 수 있습니다.</div>';
        return;
    }
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--mg-text-muted);">로딩 중...</div>';

    fetch('?tab=ordering&ajax_articles=1&lc_id=' + lcId)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.articles || data.articles.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--mg-text-muted);">이 카테고리에 문서가 없습니다.</div>';
                return;
            }
            var html = '<div id="art-sortable">';
            data.articles.forEach(function(art) {
                var thumb = art.la_thumbnail
                    ? '<img src="' + escHtml(art.la_thumbnail) + '" style="width:32px;height:32px;object-fit:cover;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">'
                    : '<div style="width:32px;height:32px;background:var(--mg-bg-tertiary);border-radius:4px;"></div>';
                html += '<div class="art-sortable-item" data-art-id="' + art.la_id + '" style="display:flex;align-items:center;gap:0.5rem 1rem;padding:0.75rem 1rem;border-bottom:1px solid var(--mg-bg-tertiary);background:var(--mg-bg-secondary);flex-wrap:wrap;">';
                html += '<span class="art-drag-handle" style="cursor:grab;color:var(--mg-text-muted);font-size:1.1rem;user-select:none;min-width:44px;min-height:44px;display:flex;align-items:center;justify-content:center;" title="드래그하여 순서 변경">&#9776;</span>';
                html += thumb;
                html += '<strong style="flex:1;">' + escHtml(art.la_title) + '</strong>';
                html += '<span class="mg-badge art-order-badge" style="font-size:0.7rem;">순서: ' + art.la_order + '</span>';
                html += '</div>';
            });
            html += '</div>';
            body.innerHTML = html;
            // 문서 정렬 초기화 (터치 + 마우스)
            initSortable(
                document.getElementById('art-sortable'),
                '.art-drag-handle', '.art-sortable-item',
                saveArticleOrder
            );
        })
        .catch(function() {
            body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--mg-text-muted);">로드 중 오류가 발생했습니다.</div>';
        });
}

function escHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>

<?php } ?>

<?php
require_once __DIR__.'/_tail.php';
?>
