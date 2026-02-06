<?php
/**
 * Morgan Edition - 프로필 필드 AJAX
 */

$sub_menu = "800300";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// 필드 상세 정보 조회
if ($action == 'get') {
    $pf_id = (int)$_GET['pf_id'];

    $field = sql_fetch("SELECT * FROM {$g5['mg_profile_field_table']} WHERE pf_id = {$pf_id}");

    if (!$field['pf_id']) {
        echo '<div style="padding:2rem;text-align:center;color:var(--mg-error);">필드를 찾을 수 없습니다.</div>';
        exit;
    }

    // JSON 옵션을 콤마 구분 텍스트로 변환
    $options_text = '';
    if ($field['pf_options']) {
        $opts = json_decode($field['pf_options'], true);
        if (is_array($opts)) {
            $options_text = implode(', ', $opts);
        } else {
            $options_text = $field['pf_options'];
        }
    }

    // 섹션 목록
    $categories = array();
    $cat_result = sql_query("SELECT DISTINCT pf_category FROM {$g5['mg_profile_field_table']} ORDER BY pf_category");
    while ($cat = sql_fetch_array($cat_result)) {
        if ($cat['pf_category']) {
            $categories[] = $cat['pf_category'];
        }
    }

    // 필드 타입 옵션
    $field_types = array(
        'text' => '한줄 텍스트',
        'textarea' => '여러줄 텍스트',
        'select' => '선택 (단일)',
        'multiselect' => '선택 (다중)',
        'url' => 'URL 링크',
        'image' => '이미지'
    );
    ?>
    <div class="mg-form-group">
        <label class="mg-form-label">필드명 <span style="color:var(--mg-error);">*</span></label>
        <input type="text" name="edit_pf_name" class="mg-form-input" value="<?php echo htmlspecialchars($field['pf_name']); ?>" required>
    </div>

    <div class="mg-form-group">
        <label class="mg-form-label">섹션</label>
        <select name="edit_pf_category" class="mg-form-select">
            <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $field['pf_category'] == $cat ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        <small style="color:var(--mg-text-muted);">이 필드가 속할 섹션을 선택합니다.</small>
    </div>

    <div class="mg-form-group">
        <label class="mg-form-label">타입</label>
        <select name="edit_pf_type" class="mg-form-select" onchange="toggleEditOptions(this)">
            <?php foreach ($field_types as $type => $label): ?>
            <option value="<?php echo $type; ?>" <?php echo $field['pf_type'] == $type ? 'selected' : ''; ?>><?php echo $label; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mg-form-group" id="editOptionsGroup" style="<?php echo in_array($field['pf_type'], ['select', 'multiselect']) ? '' : 'display:none;'; ?>">
        <label class="mg-form-label">선택 옵션</label>
        <input type="text" name="edit_pf_options" class="mg-form-input" value="<?php echo htmlspecialchars($options_text); ?>" placeholder="옵션1, 옵션2, 옵션3">
        <small style="color:var(--mg-text-muted);">쉼표(,)로 구분하여 입력하세요.</small>
    </div>

    <div class="mg-form-group">
        <label class="mg-form-label">힌트 텍스트</label>
        <input type="text" name="edit_pf_placeholder" class="mg-form-input" value="<?php echo htmlspecialchars($field['pf_placeholder']); ?>" placeholder="입력란에 보여줄 안내 문구">
    </div>

    <div class="mg-form-group">
        <label class="mg-form-label">도움말</label>
        <textarea name="edit_pf_help" class="mg-form-input" rows="2" placeholder="필드 아래에 표시될 추가 설명"><?php echo htmlspecialchars($field['pf_help']); ?></textarea>
    </div>

    <div style="display:flex;gap:1.5rem;margin-top:1rem;">
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input type="checkbox" name="edit_pf_required" value="1" <?php echo $field['pf_required'] ? 'checked' : ''; ?>> 필수 입력
        </label>
        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
            <input type="checkbox" name="edit_pf_use" value="1" <?php echo $field['pf_use'] ? 'checked' : ''; ?>> 사용
        </label>
    </div>

    <script>
    function toggleEditOptions(select) {
        var group = document.getElementById('editOptionsGroup');
        if (select.value === 'select' || select.value === 'multiselect') {
            group.style.display = '';
        } else {
            group.style.display = 'none';
        }
    }
    </script>
    <?php
    exit;
}

echo '잘못된 요청입니다.';
