<?php
/**
 * Morgan Edition - 위키 문서 목록
 */

$sub_menu = "801410";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// === POST: 삭제 처리 ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && $_POST['mode'] == 'delete') {
    if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');
    auth_check_menu($auth, $sub_menu, 'w');

    $la_id = (int)$_POST['la_id'];
    if ($la_id) {
        // 섹션 이미지 삭제
        $sec_result = sql_query("SELECT ls_image FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id} AND ls_image != '' AND ls_image IS NOT NULL");
        while ($sec = sql_fetch_array($sec_result)) {
            if ($sec['ls_image']) {
                $img_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $sec['ls_image']);
                if (file_exists($img_path)) @unlink($img_path);
            }
        }
        // 썸네일 삭제
        $art = sql_fetch("SELECT la_thumbnail FROM {$g5['mg_lore_article_table']} WHERE la_id = {$la_id}");
        if ($art['la_thumbnail']) {
            $thumb_path = str_replace(G5_DATA_URL, G5_DATA_PATH, $art['la_thumbnail']);
            if (file_exists($thumb_path)) @unlink($thumb_path);
        }
        // 섹션 삭제
        sql_query("DELETE FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id}");
        // 문서 삭제
        sql_query("DELETE FROM {$g5['mg_lore_article_table']} WHERE la_id = {$la_id}");
    }
    goto_url('./lore_article.php');
}

// === 카테고리 목록 ===
$categories = array();
$cat_result = sql_query("SELECT * FROM {$g5['mg_lore_category_table']} ORDER BY lc_order, lc_id");
while ($row = sql_fetch_array($cat_result)) {
    $categories[$row['lc_id']] = $row;
}

// 카테고리 필터
$filter_lc_id = isset($_GET['lc_id']) ? (int)$_GET['lc_id'] : 0;

// === 문서 목록 ===
$where = "1=1";
if ($filter_lc_id > 0) {
    $where .= " AND a.lc_id = {$filter_lc_id}";
}

$articles = array();
$sql = "SELECT a.*, c.lc_name
        FROM {$g5['mg_lore_article_table']} a
        LEFT JOIN {$g5['mg_lore_category_table']} c ON a.lc_id = c.lc_id
        WHERE {$where}
        ORDER BY a.la_order, a.la_id DESC";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $articles[] = $row;
}

$g5['title'] = '위키 문서 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 카테고리 필터 탭 -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="?lc_id=0" class="mg-btn <?php echo !$filter_lc_id ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm">전체</a>
        <?php foreach ($categories as $cat) { ?>
        <a href="?lc_id=<?php echo $cat['lc_id']; ?>" class="mg-btn <?php echo $filter_lc_id == $cat['lc_id'] ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"><?php echo htmlspecialchars($cat['lc_name']); ?></a>
        <?php } ?>
    </div>
    <a href="./lore_article_edit.php" class="mg-btn mg-btn-primary">+ 문서 작성</a>
</div>

<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:900px;">
            <thead>
                <tr>
                    <th style="width:50px;text-align:center;">ID</th>
                    <th style="width:60px;text-align:center;">썸네일</th>
                    <th>제목</th>
                    <th style="width:100px;text-align:center;">카테고리</th>
                    <th style="width:70px;text-align:center;">순서</th>
                    <th style="width:60px;text-align:center;">공개</th>
                    <th style="width:80px;text-align:center;">조회수</th>
                    <th style="width:130px;text-align:center;">수정일</th>
                    <th style="width:140px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)) { ?>
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
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
                    <td style="text-align:center;"><?php echo $art['la_order']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($art['la_use']) { ?>
                        <span style="color:var(--mg-success);">&check;</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">&cross;</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;"><?php echo number_format($art['la_hit']); ?></td>
                    <td style="text-align:center;font-size:0.8rem;color:var(--mg-text-muted);">
                        <?php echo substr($art['la_updated'], 0, 16); ?>
                    </td>
                    <td style="text-align:center;">
                        <a href="./lore_article_edit.php?la_id=<?php echo $art['la_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">수정</a>
                        <form method="post" action="" style="display:inline;">
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

<?php
require_once __DIR__.'/_tail.php';
?>
