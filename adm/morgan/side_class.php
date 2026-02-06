<?php
/**
 * Morgan Edition - 진영/클래스 관리
 */

$sub_menu = "800400";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 진영 목록
$sides = array();
$result = sql_query("SELECT * FROM {$g5['mg_side_table']} ORDER BY sort_order, side_id");
while ($row = sql_fetch_array($result)) {
    $sides[] = $row;
}

// 클래스 목록
$classes = array();
$result = sql_query("SELECT * FROM {$g5['mg_class_table']} ORDER BY sort_order, class_id");
while ($row = sql_fetch_array($result)) {
    $classes[] = $row;
}

$g5['title'] = '진영/클래스 관리';
require_once __DIR__.'/_head.php';
?>

<div class="mg-alert mg-alert-info">
    캐릭터가 속할 수 있는 진영과 클래스를 관리합니다.
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(400px, 1fr));gap:1.5rem;">
    <!-- 진영 관리 -->
    <div class="mg-card">
        <div class="mg-card-header">진영 관리</div>
        <div class="mg-card-body" style="padding:0;">
            <form name="fsidelist" id="fsidelist" method="post" action="./side_class_update.php">
                <input type="hidden" name="token" value="">
                <input type="hidden" name="type" value="side">

                <table class="mg-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" onclick="checkAll(this, 'fsidelist');"></th>
                            <th style="width:60px;">순서</th>
                            <th>진영명</th>
                            <th style="width:70px;">색상</th>
                            <th style="width:50px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sides as $side) { ?>
                        <tr>
                            <td><input type="checkbox" name="chk[]" value="<?php echo $side['side_id']; ?>"></td>
                            <td>
                                <input type="hidden" name="item_id[]" value="<?php echo $side['side_id']; ?>">
                                <input type="text" name="sort_order[]" value="<?php echo $side['sort_order']; ?>" class="mg-form-input" style="width:50px;text-align:center;">
                            </td>
                            <td>
                                <input type="text" name="item_name[]" value="<?php echo $side['side_name']; ?>" class="mg-form-input">
                            </td>
                            <td>
                                <input type="color" name="item_color[]" value="<?php echo $side['side_color'] ?: '#ffffff'; ?>" style="width:50px;height:32px;border:none;background:none;cursor:pointer;">
                            </td>
                            <td style="text-align:center;color:var(--mg-text-muted);"><?php echo $side['side_id']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot style="background:var(--mg-bg-primary);">
                        <tr>
                            <td></td>
                            <td><input type="text" name="new_sort_order" value="0" class="mg-form-input" style="width:50px;text-align:center;"></td>
                            <td><input type="text" name="new_item_name" value="" class="mg-form-input" placeholder="새 진영명"></td>
                            <td><input type="color" name="new_item_color" value="#ffffff" style="width:50px;height:32px;border:none;background:none;cursor:pointer;"></td>
                            <td style="text-align:center;color:var(--mg-accent);">NEW</td>
                        </tr>
                    </tfoot>
                </table>

                <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                    <button type="submit" name="btn_submit" class="mg-btn mg-btn-primary mg-btn-sm">저장</button>
                    <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('삭제하시겠습니까?');">삭제</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 클래스 관리 -->
    <div class="mg-card">
        <div class="mg-card-header">클래스 관리</div>
        <div class="mg-card-body" style="padding:0;">
            <form name="fclasslist" id="fclasslist" method="post" action="./side_class_update.php">
                <input type="hidden" name="token" value="">
                <input type="hidden" name="type" value="class">

                <table class="mg-table">
                    <thead>
                        <tr>
                            <th style="width:40px;"><input type="checkbox" onclick="checkAll(this, 'fclasslist');"></th>
                            <th style="width:60px;">순서</th>
                            <th>클래스명</th>
                            <th style="width:80px;">아이콘</th>
                            <th style="width:50px;">ID</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($classes as $class) { ?>
                        <tr>
                            <td><input type="checkbox" name="chk[]" value="<?php echo $class['class_id']; ?>"></td>
                            <td>
                                <input type="hidden" name="item_id[]" value="<?php echo $class['class_id']; ?>">
                                <input type="text" name="sort_order[]" value="<?php echo $class['sort_order']; ?>" class="mg-form-input" style="width:50px;text-align:center;">
                            </td>
                            <td>
                                <input type="text" name="item_name[]" value="<?php echo $class['class_name']; ?>" class="mg-form-input">
                            </td>
                            <td>
                                <input type="text" name="item_icon[]" value="<?php echo $class['class_icon']; ?>" class="mg-form-input" placeholder="아이콘">
                            </td>
                            <td style="text-align:center;color:var(--mg-text-muted);"><?php echo $class['class_id']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot style="background:var(--mg-bg-primary);">
                        <tr>
                            <td></td>
                            <td><input type="text" name="new_sort_order" value="0" class="mg-form-input" style="width:50px;text-align:center;"></td>
                            <td><input type="text" name="new_item_name" value="" class="mg-form-input" placeholder="새 클래스명"></td>
                            <td><input type="text" name="new_item_icon" value="" class="mg-form-input" placeholder="아이콘"></td>
                            <td style="text-align:center;color:var(--mg-accent);">NEW</td>
                        </tr>
                    </tfoot>
                </table>

                <div style="padding:1rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;gap:0.5rem;">
                    <button type="submit" name="btn_submit" class="mg-btn mg-btn-primary mg-btn-sm">저장</button>
                    <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('삭제하시겠습니까?');">삭제</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function checkAll(el, formId) {
    var form = document.getElementById(formId);
    var chks = form.querySelectorAll('input[name="chk[]"]');
    chks.forEach(function(chk) { chk.checked = el.checked; });
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
