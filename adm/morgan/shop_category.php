<?php
/**
 * Morgan Edition - 상점 카테고리 관리
 */

$sub_menu = "800800";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$token = get_admin_token();

// 카테고리 목록
$categories = array();
$result = sql_query("SELECT * FROM {$g5['mg_shop_category_table']} ORDER BY sc_order, sc_id");
while ($row = sql_fetch_array($result)) {
    // 해당 카테고리의 상품 수
    $cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_shop_item_table']} WHERE sc_id = {$row['sc_id']}");
    $row['item_count'] = (int)$cnt['cnt'];
    $categories[] = $row;
}

$g5['title'] = '상점 카테고리 관리';
require_once __DIR__.'/_head.php';
?>

<div class="mg-card">
    <div class="mg-card-header">
        상점 카테고리 관리
        <span style="font-weight:normal;font-size:0.875rem;color:var(--mg-text-muted);margin-left:1rem;">
            상점에서 상품을 분류할 카테고리를 관리합니다.
        </span>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <form name="fcategorylist" id="fcategorylist" method="post" action="./shop_category_update.php" enctype="multipart/form-data">
            <input type="hidden" name="token" value="<?php echo $token; ?>">

            <table class="mg-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" onclick="checkAll(this);"></th>
                        <th style="width:60px;">순서</th>
                        <th>카테고리명</th>
                        <th style="width:200px;">설명</th>
                        <th style="width:100px;">아이콘</th>
                        <th style="width:70px;">사용</th>
                        <th style="width:80px;">상품수</th>
                        <th style="width:50px;">ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat) { ?>
                    <tr>
                        <td><input type="checkbox" name="chk[]" value="<?php echo $cat['sc_id']; ?>"></td>
                        <td>
                            <input type="hidden" name="sc_id[]" value="<?php echo $cat['sc_id']; ?>">
                            <input type="text" name="sc_order[]" value="<?php echo $cat['sc_order']; ?>" class="mg-form-input" style="width:50px;text-align:center;">
                        </td>
                        <td>
                            <input type="text" name="sc_name[]" value="<?php echo htmlspecialchars($cat['sc_name']); ?>" class="mg-form-input" required>
                        </td>
                        <td>
                            <input type="text" name="sc_desc[]" value="<?php echo htmlspecialchars($cat['sc_desc']); ?>" class="mg-form-input" placeholder="설명">
                        </td>
                        <td>
                            <?php
                            $icon_val = $cat['sc_icon'];
                            $is_image = $icon_val && (strpos($icon_val, '/') !== false || strpos($icon_val, 'http') === 0);
                            ?>
                            <input type="hidden" name="sc_icon[]" value="<?php echo htmlspecialchars($icon_val); ?>">
                            <?php if ($is_image) { ?>
                            <div style="display:flex;align-items:center;gap:0.5rem;">
                                <img src="<?php echo $icon_val; ?>" style="width:24px;height:24px;object-fit:contain;">
                                <label style="color:var(--mg-error);font-size:0.75rem;cursor:pointer;">
                                    <input type="checkbox" name="del_icon[<?php echo $cat['sc_id']; ?>]" value="1"> 삭제
                                </label>
                            </div>
                            <?php } else { ?>
                            <span style="color:var(--mg-text-muted);font-size:0.875rem;"><?php echo htmlspecialchars($icon_val ?: '-'); ?></span>
                            <?php } ?>
                        </td>
                        <td style="text-align:center;">
                            <input type="checkbox" name="sc_use[<?php echo $cat['sc_id']; ?>]" value="1" <?php echo $cat['sc_use'] ? 'checked' : ''; ?>>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($cat['item_count'] > 0) { ?>
                            <a href="./shop_item_list.php?sc_id=<?php echo $cat['sc_id']; ?>" class="text-mg-accent"><?php echo $cat['item_count']; ?></a>
                            <?php } else { ?>
                            <span style="color:var(--mg-text-muted);">0</span>
                            <?php } ?>
                        </td>
                        <td style="text-align:center;color:var(--mg-text-muted);"><?php echo $cat['sc_id']; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
                <tfoot style="background:var(--mg-bg-primary);">
                    <tr>
                        <td></td>
                        <td><input type="text" name="new_sc_order" value="0" class="mg-form-input" style="width:50px;text-align:center;"></td>
                        <td><input type="text" name="new_sc_name" value="" class="mg-form-input" placeholder="새 카테고리명"></td>
                        <td><input type="text" name="new_sc_desc" value="" class="mg-form-input" placeholder="설명"></td>
                        <td>
                            <?php mg_icon_input('new_sc_icon', '', array('text_name' => 'new_sc_icon', 'file_name' => 'new_sc_icon_file', 'show_preview' => false, 'show_delete' => false, 'compact' => true)); ?>
                        </td>
                        <td style="text-align:center;"><input type="checkbox" name="new_sc_use" value="1" checked></td>
                        <td></td>
                        <td style="text-align:center;color:var(--mg-accent);">NEW</td>
                    </tr>
                </tfoot>
            </table>

            <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;justify-content:space-between;">
                <div style="display:flex;gap:0.5rem;">
                    <button type="submit" name="btn_submit" class="mg-btn mg-btn-primary">저장</button>
                    <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger" onclick="return confirm('선택한 카테고리를 삭제하시겠습니까?\n\n※ 상품이 있는 카테고리는 삭제되지 않습니다.');">선택 삭제</button>
                </div>
                <a href="./shop_item_list.php" class="mg-btn mg-btn-secondary">상품 관리 →</a>
            </div>
        </form>
    </div>
</div>

<script>
function checkAll(el) {
    var chks = document.querySelectorAll('input[name="chk[]"]');
    chks.forEach(function(chk) { chk.checked = el.checked; });
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
