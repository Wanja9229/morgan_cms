<?php
/**
 * Morgan Edition - 캐릭터 수정 (관리자)
 */

$sub_menu = "800200";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

if (!$ch_id) {
    alert('잘못된 접근입니다.');
}

// 캐릭터 정보 조회
$sql = "SELECT c.*, m.mb_nick
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        WHERE c.ch_id = {$ch_id}";
$char = sql_fetch($sql);

if (!$char['ch_id']) {
    alert('존재하지 않는 캐릭터입니다.');
}

// 진영 목록
$sides = array();
$result = sql_query("SELECT * FROM {$g5['mg_side_table']} WHERE side_use = 1 ORDER BY side_order, side_id");
while ($row = sql_fetch_array($result)) {
    $sides[] = $row;
}

// 클래스 목록
$classes = array();
$result = sql_query("SELECT * FROM {$g5['mg_class_table']} WHERE class_use = 1 ORDER BY class_order, class_id");
while ($row = sql_fetch_array($result)) {
    $classes[] = $row;
}

// 프로필 필드 (섹션별 그룹화)
$profile_fields = array();
$result = sql_query("SELECT * FROM {$g5['mg_profile_field_table']} WHERE pf_use = 1 ORDER BY pf_category, pf_order, pf_id");
while ($row = sql_fetch_array($result)) {
    // 값 조회
    $value = sql_fetch("SELECT pv_value FROM {$g5['mg_profile_value_table']}
                        WHERE ch_id = {$ch_id} AND pf_id = {$row['pf_id']}");
    $row['value'] = $value['pv_value'] ?? '';

    // 옵션 변환 (JSON → 배열)
    if ($row['pf_options']) {
        $opts = json_decode($row['pf_options'], true);
        $row['options_arr'] = is_array($opts) ? $opts : array();
    } else {
        $row['options_arr'] = array();
    }

    $cat = $row['pf_category'] ?: '기본정보';
    if (!isset($profile_fields[$cat])) {
        $profile_fields[$cat] = array();
    }
    $profile_fields[$cat][] = $row;
}

// 상태 옵션
$state_options = array(
    'editing' => '작성중',
    'pending' => '승인 대기',
    'approved' => '승인됨'
);

$g5['title'] = '캐릭터 수정';
require_once __DIR__.'/_head.php';
?>

<form name="fcharform" id="fcharform" method="post" action="./character_form_update.php" enctype="multipart/form-data">
    <input type="hidden" name="ch_id" value="<?php echo $ch_id; ?>">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        <!-- 기본 정보 -->
        <div class="mg-card">
            <div class="mg-card-header">기본 정보</div>
            <div class="mg-card-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">회원</label>
                    <div style="padding:0.625rem;background:var(--mg-bg-tertiary);border-radius:0.375rem;">
                        <strong style="color:var(--mg-accent);"><?php echo $char['mb_id']; ?></strong>
                        <?php if ($char['mb_nick']) { ?>(<?php echo $char['mb_nick']; ?>)<?php } ?>
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label" for="ch_name">캐릭터명 <span style="color:var(--mg-error);">*</span></label>
                    <input type="text" name="ch_name" id="ch_name" value="<?php echo htmlspecialchars($char['ch_name']); ?>" class="mg-form-input" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label" for="side_id">진영</label>
                        <select name="side_id" id="side_id" class="mg-form-select">
                            <option value="">선택안함</option>
                            <?php foreach ($sides as $side) { ?>
                            <option value="<?php echo $side['side_id']; ?>" <?php echo $char['side_id'] == $side['side_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($side['side_name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label" for="class_id">클래스</label>
                        <select name="class_id" id="class_id" class="mg-form-select">
                            <option value="">선택안함</option>
                            <?php foreach ($classes as $class) { ?>
                            <option value="<?php echo $class['class_id']; ?>" <?php echo $char['class_id'] == $class['class_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($class['class_name']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label" for="ch_state">상태</label>
                        <select name="ch_state" id="ch_state" class="mg-form-select">
                            <?php foreach ($state_options as $val => $label) { ?>
                            <option value="<?php echo $val; ?>" <?php echo $char['ch_state'] == $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label">대표 캐릭터</label>
                        <div style="display:flex;gap:1rem;padding-top:0.5rem;">
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="radio" name="ch_main" value="1" <?php echo $char['ch_main'] ? 'checked' : ''; ?>>
                                <span>예</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                <input type="radio" name="ch_main" value="0" <?php echo !$char['ch_main'] ? 'checked' : ''; ?>>
                                <span>아니오</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">등록일</label>
                    <div style="padding:0.625rem;background:var(--mg-bg-tertiary);border-radius:0.375rem;color:var(--mg-text-muted);">
                        <?php echo $char['ch_datetime']; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- 이미지 -->
        <div>
            <!-- 두상 이미지 -->
            <div class="mg-card" style="margin-bottom:1rem;">
                <div class="mg-card-header">두상 이미지</div>
                <div class="mg-card-body">
                    <?php $thumb_url = $char['ch_thumb'] ? MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb'] : ''; ?>
                    <?php if ($thumb_url) { ?>
                    <div style="margin-bottom:1rem;text-align:center;">
                        <img src="<?php echo $thumb_url; ?>" style="max-width:150px;max-height:150px;border-radius:0.5rem;background:var(--mg-bg-tertiary);">
                        <label style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-top:0.5rem;cursor:pointer;color:var(--mg-error);font-size:0.85rem;">
                            <input type="checkbox" name="del_thumb" value="1">
                            <span>삭제</span>
                        </label>
                    </div>
                    <?php } else { ?>
                    <div style="margin-bottom:1rem;text-align:center;padding:1.5rem;background:var(--mg-bg-tertiary);border-radius:0.5rem;color:var(--mg-text-muted);font-size:0.85rem;">
                        등록된 이미지 없음
                    </div>
                    <?php } ?>
                    <input type="file" name="ch_thumb" accept="image/*" class="mg-form-input" style="padding:0.5rem;">
                </div>
            </div>

            <!-- 전신 이미지 -->
            <div class="mg-card">
                <div class="mg-card-header">전신 이미지</div>
                <div class="mg-card-body">
                    <?php $body_url = ($char['ch_image'] ?? '') ? MG_CHAR_IMAGE_URL.'/'.$char['ch_image'] : ''; ?>
                    <?php if ($body_url) { ?>
                    <div style="margin-bottom:1rem;text-align:center;">
                        <img src="<?php echo $body_url; ?>" style="max-width:150px;max-height:200px;border-radius:0.5rem;background:var(--mg-bg-tertiary);">
                        <label style="display:flex;align-items:center;justify-content:center;gap:0.5rem;margin-top:0.5rem;cursor:pointer;color:var(--mg-error);font-size:0.85rem;">
                            <input type="checkbox" name="del_image" value="1">
                            <span>삭제</span>
                        </label>
                    </div>
                    <?php } else { ?>
                    <div style="margin-bottom:1rem;text-align:center;padding:1.5rem;background:var(--mg-bg-tertiary);border-radius:0.5rem;color:var(--mg-text-muted);font-size:0.85rem;">
                        등록된 이미지 없음
                    </div>
                    <?php } ?>
                    <input type="file" name="ch_image" accept="image/*" class="mg-form-input" style="padding:0.5rem;">
                </div>
            </div>
        </div>
    </div>

    <!-- 프로필 필드 -->
    <?php if (!empty($profile_fields)) { ?>
    <div class="mg-card" style="margin-top:1.5rem;">
        <div class="mg-card-header">프로필 정보</div>
        <div class="mg-card-body">
            <?php foreach ($profile_fields as $category => $fields) { ?>
            <div style="margin-bottom:1.5rem;">
                <h4 style="margin:0 0 1rem 0;padding-bottom:0.5rem;border-bottom:1px solid var(--mg-bg-tertiary);color:var(--mg-text-secondary);"><?php echo htmlspecialchars($category); ?></h4>
                <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:1rem;">
                    <?php foreach ($fields as $field) { ?>
                    <div class="mg-form-group" style="margin-bottom:0;">
                        <label class="mg-form-label" for="pf_<?php echo $field['pf_id']; ?>">
                            <?php echo htmlspecialchars($field['pf_name']); ?>
                            <?php if ($field['pf_required']) { ?><span style="color:var(--mg-error);">*</span><?php } ?>
                        </label>
                        <?php if ($field['pf_type'] == 'text') { ?>
                        <input type="text" name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" value="<?php echo htmlspecialchars($field['value']); ?>" class="mg-form-input" placeholder="<?php echo htmlspecialchars($field['pf_placeholder']); ?>">
                        <?php } else if ($field['pf_type'] == 'textarea') { ?>
                        <textarea name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" class="mg-form-input" rows="3" placeholder="<?php echo htmlspecialchars($field['pf_placeholder']); ?>"><?php echo htmlspecialchars($field['value']); ?></textarea>
                        <?php } else if ($field['pf_type'] == 'select') { ?>
                        <select name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" class="mg-form-select">
                            <option value="">선택</option>
                            <?php foreach ($field['options_arr'] as $opt) { ?>
                            <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo $field['value'] == $opt ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                            <?php } ?>
                        </select>
                        <?php } else if ($field['pf_type'] == 'multiselect') {
                            $selected = $field['value'] ? json_decode($field['value'], true) : array();
                            if (!is_array($selected)) $selected = array();
                        ?>
                        <select name="profile[<?php echo $field['pf_id']; ?>][]" id="pf_<?php echo $field['pf_id']; ?>" class="mg-form-select" multiple style="min-height:100px;">
                            <?php foreach ($field['options_arr'] as $opt) { ?>
                            <option value="<?php echo htmlspecialchars($opt); ?>" <?php echo in_array($opt, $selected) ? 'selected' : ''; ?>><?php echo htmlspecialchars($opt); ?></option>
                            <?php } ?>
                        </select>
                        <?php } else if ($field['pf_type'] == 'url') { ?>
                        <input type="url" name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" value="<?php echo htmlspecialchars($field['value']); ?>" class="mg-form-input" placeholder="https://">
                        <?php } ?>
                        <?php if ($field['pf_help']) { ?>
                        <small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars($field['pf_help']); ?></small>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
        <a href="./character_list.php" class="mg-btn mg-btn-secondary">목록</a>
        <button type="submit" name="btn_save" class="mg-btn mg-btn-primary">저장</button>
        <?php if ($char['ch_state'] == 'pending') { ?>
        <button type="submit" name="btn_approve" class="mg-btn mg-btn-success">승인</button>
        <?php } ?>
    </div>
</form>

<style>
@media (max-width: 900px) {
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php
require_once __DIR__.'/_tail.php';
?>
