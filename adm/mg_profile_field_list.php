<?php
/**
 * Morgan Edition - 프로필 양식 관리
 */

$sub_menu = '400300';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '프로필 양식 관리';
include_once './admin.head.php';

$token = get_admin_token();

// 프로필 필드 목록
$sql = "SELECT * FROM {$g5['mg_profile_field_table']} ORDER BY pf_order, pf_id";
$result = sql_query($sql);

$field_types = array(
    'text' => '텍스트',
    'textarea' => '여러 줄 텍스트',
    'select' => '선택 (단일)',
    'multiselect' => '선택 (복수)',
    'url' => 'URL',
    'image' => '이미지',
);
?>

<div class="local_desc01 local_desc">
    <p>캐릭터 프로필에 표시되는 항목을 관리합니다.</p>
</div>

<form name="fprofile" method="post" action="./mg_profile_field_update.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="action" value="save">

    <div class="tbl_head01 tbl_wrap">
        <table>
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">코드</th>
                    <th scope="col">항목명</th>
                    <th scope="col">타입</th>
                    <th scope="col">카테고리</th>
                    <th scope="col">순서</th>
                    <th scope="col">필수</th>
                    <th scope="col">사용</th>
                    <th scope="col">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = sql_fetch_array($result)) { ?>
                <tr>
                    <td class="td_num"><?php echo $row['pf_id']; ?></td>
                    <td>
                        <input type="text" name="field[<?php echo $row['pf_id']; ?>][code]" value="<?php echo htmlspecialchars($row['pf_code']); ?>" class="form-control" style="width: 80px;" readonly>
                    </td>
                    <td>
                        <input type="text" name="field[<?php echo $row['pf_id']; ?>][name]" value="<?php echo htmlspecialchars($row['pf_name']); ?>" class="form-control" style="width: 100px;">
                    </td>
                    <td>
                        <select name="field[<?php echo $row['pf_id']; ?>][type]" class="form-control" style="width: 100px;">
                            <?php foreach ($field_types as $type => $label) { ?>
                            <option value="<?php echo $type; ?>" <?php echo $row['pf_type'] == $type ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <input type="text" name="field[<?php echo $row['pf_id']; ?>][category]" value="<?php echo htmlspecialchars($row['pf_category']); ?>" class="form-control" style="width: 80px;">
                    </td>
                    <td>
                        <input type="number" name="field[<?php echo $row['pf_id']; ?>][order]" value="<?php echo $row['pf_order']; ?>" class="form-control" style="width: 60px;">
                    </td>
                    <td class="td_chk">
                        <input type="checkbox" name="field[<?php echo $row['pf_id']; ?>][required]" value="1" <?php echo $row['pf_required'] ? 'checked' : ''; ?>>
                    </td>
                    <td class="td_chk">
                        <input type="checkbox" name="field[<?php echo $row['pf_id']; ?>][use]" value="1" <?php echo $row['pf_use'] ? 'checked' : ''; ?>>
                    </td>
                    <td class="td_mng">
                        <a href="./mg_profile_field_form.php?pf_id=<?php echo $row['pf_id']; ?>" class="btn btn-default btn-xs">상세</a>
                        <a href="./mg_profile_field_update.php?action=delete&amp;pf_id=<?php echo $row['pf_id']; ?>&amp;token=<?php echo $token; ?>" onclick="return confirm('삭제하시겠습니까?');" class="btn btn-danger btn-xs">삭제</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm" style="margin-top: 10px;">
        <button type="submit" class="btn btn-primary">저장</button>
        <a href="./mg_profile_field_form.php" class="btn btn-success">새 항목 추가</a>
    </div>
</form>

<?php
include_once './admin.tail.php';
