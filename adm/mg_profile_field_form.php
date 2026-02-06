<?php
/**
 * Morgan Edition - 프로필 양식 상세/추가
 */

$sub_menu = '400300';
include_once './_common.php';

// Morgan 플러그인 로드
include_once G5_PATH.'/plugin/morgan/morgan.php';

auth_check_menu($auth, $sub_menu, 'r');

$pf_id = isset($_GET['pf_id']) ? (int)$_GET['pf_id'] : 0;
$is_edit = $pf_id > 0;

if ($is_edit) {
    $field = sql_fetch("SELECT * FROM {$g5['mg_profile_field_table']} WHERE pf_id = {$pf_id}");
    if (!$field['pf_id']) {
        alert('존재하지 않는 항목입니다.');
    }
} else {
    $field = array(
        'pf_code' => '',
        'pf_name' => '',
        'pf_type' => 'text',
        'pf_options' => '',
        'pf_placeholder' => '',
        'pf_help' => '',
        'pf_required' => 0,
        'pf_order' => 0,
        'pf_category' => '기본정보',
        'pf_use' => 1,
    );
}

$g5['title'] = $is_edit ? '프로필 항목 수정' : '프로필 항목 추가';
include_once './admin.head.php';

$token = get_admin_token();

$field_types = array(
    'text' => '텍스트 (한 줄)',
    'textarea' => '여러 줄 텍스트',
    'select' => '선택 (단일)',
    'multiselect' => '선택 (복수)',
    'url' => 'URL',
    'image' => '이미지',
);
?>

<form name="fprofilefield" method="post" action="./mg_profile_field_update.php">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'insert'; ?>">
    <input type="hidden" name="pf_id" value="<?php echo $pf_id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <tbody>
                <tr>
                    <th scope="row"><label for="pf_code">항목 코드</label></th>
                    <td>
                        <input type="text" name="pf_code" id="pf_code" value="<?php echo htmlspecialchars($field['pf_code']); ?>" class="form-control" style="width: 200px;" required <?php echo $is_edit ? 'readonly' : ''; ?>>
                        <span class="help-block">영문, 숫자, 언더스코어만 사용 (예: birth_date, hobby)</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_name">표시명</label></th>
                    <td>
                        <input type="text" name="pf_name" id="pf_name" value="<?php echo htmlspecialchars($field['pf_name']); ?>" class="form-control" style="width: 200px;" required>
                        <span class="help-block">사용자에게 표시되는 항목 이름</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_type">입력 타입</label></th>
                    <td>
                        <select name="pf_type" id="pf_type" class="form-control" style="width: 200px;">
                            <?php foreach ($field_types as $type => $label) { ?>
                            <option value="<?php echo $type; ?>" <?php echo $field['pf_type'] == $type ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php } ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_options">선택지</label></th>
                    <td>
                        <textarea name="pf_options" id="pf_options" rows="4" class="form-control" style="width: 400px;"><?php echo htmlspecialchars($field['pf_options']); ?></textarea>
                        <span class="help-block">select/multiselect 타입일 때 사용. JSON 배열 형식 (예: ["남성","여성","기타"])</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_placeholder">힌트 텍스트</label></th>
                    <td>
                        <input type="text" name="pf_placeholder" id="pf_placeholder" value="<?php echo htmlspecialchars($field['pf_placeholder']); ?>" class="form-control" style="width: 400px;">
                        <span class="help-block">입력 필드에 표시되는 안내 문구</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_help">도움말</label></th>
                    <td>
                        <textarea name="pf_help" id="pf_help" rows="2" class="form-control" style="width: 400px;"><?php echo htmlspecialchars($field['pf_help']); ?></textarea>
                        <span class="help-block">입력 필드 아래에 표시되는 상세 설명</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_category">카테고리</label></th>
                    <td>
                        <input type="text" name="pf_category" id="pf_category" value="<?php echo htmlspecialchars($field['pf_category']); ?>" class="form-control" style="width: 200px;">
                        <span class="help-block">프로필에서 그룹화할 섹션 이름 (예: 기본정보, 외형, 성격)</span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pf_order">정렬 순서</label></th>
                    <td>
                        <input type="number" name="pf_order" id="pf_order" value="<?php echo $field['pf_order']; ?>" class="form-control" style="width: 100px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row">필수 여부</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="pf_required" value="1" <?php echo $field['pf_required'] ? 'checked' : ''; ?>>
                            필수 항목으로 지정
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row">사용 여부</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="pf_use" value="1" <?php echo $field['pf_use'] ? 'checked' : ''; ?>>
                            사용함
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm" style="margin-top: 10px;">
        <button type="submit" class="btn btn-primary"><?php echo $is_edit ? '수정' : '추가'; ?></button>
        <a href="./mg_profile_field_list.php" class="btn btn-default">목록</a>
    </div>
</form>

<?php
include_once './admin.tail.php';
