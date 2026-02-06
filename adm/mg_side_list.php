<?php
/**
 * Morgan Edition - 세력/종족 관리
 */

$sub_menu = '400200';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '세력/종족 관리';
include_once './admin.head.php';

$token = get_admin_token();

// 세력 목록
$sql_sides = "SELECT * FROM {$g5['mg_side_table']} ORDER BY side_order, side_id";
$result_sides = sql_query($sql_sides);

// 종족 목록
$sql_classes = "SELECT * FROM {$g5['mg_class_table']} ORDER BY class_order, class_id";
$result_classes = sql_query($sql_classes);
?>

<div class="local_desc01 local_desc">
    <p>세력과 종족을 관리합니다. 캐릭터 등록 시 선택할 수 있습니다.</p>
</div>

<div class="row" style="display: flex; gap: 20px; flex-wrap: wrap;">
    <!-- 세력 관리 -->
    <div class="col" style="flex: 1; min-width: 400px;">
        <section id="anc_mg_side">
            <h2 class="h2_frm">세력 관리</h2>

            <form name="fside" method="post" action="./mg_side_update.php">
                <input type="hidden" name="token" value="<?php echo $token; ?>">

                <div class="tbl_head01 tbl_wrap">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">세력명</th>
                                <th scope="col">순서</th>
                                <th scope="col">사용</th>
                                <th scope="col">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = sql_fetch_array($result_sides)) { ?>
                            <tr>
                                <td class="td_num"><?php echo $row['side_id']; ?></td>
                                <td>
                                    <input type="text" name="side[<?php echo $row['side_id']; ?>][name]" value="<?php echo htmlspecialchars($row['side_name']); ?>" class="form-control" style="width: 150px;">
                                </td>
                                <td>
                                    <input type="number" name="side[<?php echo $row['side_id']; ?>][order]" value="<?php echo $row['side_order']; ?>" class="form-control" style="width: 60px;">
                                </td>
                                <td>
                                    <input type="checkbox" name="side[<?php echo $row['side_id']; ?>][use]" value="1" <?php echo $row['side_use'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <a href="./mg_side_update.php?action=delete&amp;side_id=<?php echo $row['side_id']; ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('삭제하시겠습니까?');" class="btn btn-danger btn-xs">삭제</a>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr class="bg-info">
                                <td class="td_num">신규</td>
                                <td><input type="text" name="new_side_name" class="form-control" style="width: 150px;" placeholder="새 세력명"></td>
                                <td><input type="number" name="new_side_order" value="0" class="form-control" style="width: 60px;"></td>
                                <td><input type="checkbox" name="new_side_use" value="1" checked></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="btn_confirm01 btn_confirm" style="margin-top: 10px;">
                    <button type="submit" name="action" value="save_sides" class="btn btn-primary">세력 저장</button>
                </div>
            </form>
        </section>
    </div>

    <!-- 종족 관리 -->
    <div class="col" style="flex: 1; min-width: 400px;">
        <section id="anc_mg_class">
            <h2 class="h2_frm">종족 관리</h2>

            <form name="fclass" method="post" action="./mg_side_update.php">
                <input type="hidden" name="token" value="<?php echo $token; ?>">

                <div class="tbl_head01 tbl_wrap">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th scope="col">종족명</th>
                                <th scope="col">순서</th>
                                <th scope="col">사용</th>
                                <th scope="col">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = sql_fetch_array($result_classes)) { ?>
                            <tr>
                                <td class="td_num"><?php echo $row['class_id']; ?></td>
                                <td>
                                    <input type="text" name="class[<?php echo $row['class_id']; ?>][name]" value="<?php echo htmlspecialchars($row['class_name']); ?>" class="form-control" style="width: 150px;">
                                </td>
                                <td>
                                    <input type="number" name="class[<?php echo $row['class_id']; ?>][order]" value="<?php echo $row['class_order']; ?>" class="form-control" style="width: 60px;">
                                </td>
                                <td>
                                    <input type="checkbox" name="class[<?php echo $row['class_id']; ?>][use]" value="1" <?php echo $row['class_use'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <a href="./mg_side_update.php?action=delete_class&amp;class_id=<?php echo $row['class_id']; ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('삭제하시겠습니까?');" class="btn btn-danger btn-xs">삭제</a>
                                </td>
                            </tr>
                            <?php } ?>
                            <tr class="bg-info">
                                <td class="td_num">신규</td>
                                <td><input type="text" name="new_class_name" class="form-control" style="width: 150px;" placeholder="새 종족명"></td>
                                <td><input type="number" name="new_class_order" value="0" class="form-control" style="width: 60px;"></td>
                                <td><input type="checkbox" name="new_class_use" value="1" checked></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="btn_confirm01 btn_confirm" style="margin-top: 10px;">
                    <button type="submit" name="action" value="save_classes" class="btn btn-primary">종족 저장</button>
                </div>
            </form>
        </section>
    </div>
</div>

<?php
include_once './admin.tail.php';
