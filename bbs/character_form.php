<?php
/**
 * Morgan Edition - 캐릭터 생성/수정 폼
 */

include_once('./_common.php');

// 로그인 체크
if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;
$is_edit = $ch_id > 0;

// 수정 모드일 경우 캐릭터 정보 조회
$char = array();
$profile_values = array();

if ($is_edit) {
    $sql = "SELECT * FROM {$g5['mg_character_table']} WHERE ch_id = {$ch_id} AND mb_id = '{$member['mb_id']}'";
    $char = sql_fetch($sql);

    if (!$char['ch_id']) {
        alert('존재하지 않거나 권한이 없는 캐릭터입니다.');
    }

    // 프로필 값 조회
    $sql = "SELECT pf_id, pv_value FROM {$g5['mg_profile_value_table']} WHERE ch_id = {$ch_id}";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $profile_values[$row['pf_id']] = $row['pv_value'];
    }
} else {
    // 생성 모드: 최대 캐릭터 수 체크
    $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']}
            WHERE mb_id = '{$member['mb_id']}' AND ch_state != 'deleted'";
    $row = sql_fetch($sql);
    $max_characters = (int)mg_config('max_characters', 10);

    if ($row['cnt'] >= $max_characters) {
        alert('최대 캐릭터 수('.$max_characters.'개)에 도달하여 더 이상 생성할 수 없습니다.');
    }
}

// 세력/종족 목록
$sides = array();
$result = sql_query("SELECT * FROM {$g5['mg_side_table']} WHERE side_use = 1 ORDER BY side_order, side_id");
while ($row = sql_fetch_array($result)) {
    $sides[] = $row;
}

$classes = array();
$result = sql_query("SELECT * FROM {$g5['mg_class_table']} WHERE class_use = 1 ORDER BY class_order, class_id");
while ($row = sql_fetch_array($result)) {
    $classes[] = $row;
}

// 프로필 필드 목록
$profile_fields = array();
$result = sql_query("SELECT * FROM {$g5['mg_profile_field_table']} WHERE pf_use = 1 ORDER BY pf_order, pf_id");
while ($row = sql_fetch_array($result)) {
    $row['value'] = $profile_values[$row['pf_id']] ?? '';
    $profile_fields[] = $row;
}

// 카테고리별 그룹핑
$grouped_fields = array();
foreach ($profile_fields as $field) {
    $category = $field['pf_category'] ?: '기본정보';
    $grouped_fields[$category][] = $field;
}

$g5['title'] = $is_edit ? '캐릭터 수정' : '캐릭터 생성';

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 페이지 헤더 -->
    <div class="mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/character.php" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            <span>내 캐릭터</span>
        </a>
        <h1 class="text-2xl font-bold text-mg-text-primary"><?php echo $is_edit ? '캐릭터 수정' : '새 캐릭터 만들기'; ?></h1>
    </div>

    <!-- 폼 -->
    <form name="fcharform" id="fcharform" method="post" action="<?php echo G5_BBS_URL; ?>/character_form_update.php" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="ch_id" value="<?php echo $ch_id; ?>">
        <input type="hidden" name="token" value="">

        <!-- 기본 정보 카드 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">기본 정보</h2>
            </div>
            <div class="p-4 space-y-4">
                <!-- 캐릭터명 -->
                <div>
                    <label for="ch_name" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                        캐릭터명 <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="ch_name" id="ch_name" value="<?php echo $char['ch_name'] ?? ''; ?>" required
                           class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors"
                           placeholder="캐릭터 이름을 입력하세요">
                </div>

                <!-- 세력/종족 -->
                <div class="grid grid-cols-2 gap-4">
                    <?php if (count($sides) > 0) { ?>
                    <div>
                        <label for="side_id" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                            <?php echo mg_config('side_title', '세력'); ?>
                        </label>
                        <select name="side_id" id="side_id" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary focus:outline-none focus:border-mg-accent transition-colors">
                            <option value="">선택안함</option>
                            <?php foreach ($sides as $side) { ?>
                            <option value="<?php echo $side['side_id']; ?>" <?php echo ($char['side_id'] ?? '') == $side['side_id'] ? 'selected' : ''; ?>><?php echo $side['side_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php } ?>

                    <?php if (count($classes) > 0) { ?>
                    <div>
                        <label for="class_id" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                            <?php echo mg_config('class_title', '종족'); ?>
                        </label>
                        <select name="class_id" id="class_id" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary focus:outline-none focus:border-mg-accent transition-colors">
                            <option value="">선택안함</option>
                            <?php foreach ($classes as $class) { ?>
                            <option value="<?php echo $class['class_id']; ?>" <?php echo ($char['class_id'] ?? '') == $class['class_id'] ? 'selected' : ''; ?>><?php echo $class['class_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php } ?>
                </div>

                <!-- 대표 캐릭터 -->
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" name="ch_main" value="1" <?php echo ($char['ch_main'] ?? 0) ? 'checked' : ''; ?>
                               class="w-5 h-5 rounded border-mg-bg-tertiary bg-mg-bg-primary text-mg-accent focus:ring-mg-accent focus:ring-offset-0">
                        <span class="text-sm text-mg-text-secondary">대표 캐릭터로 설정</span>
                    </label>
                    <p class="text-xs text-mg-text-muted mt-1 ml-8">대표 캐릭터는 게시글 작성 시 기본으로 선택됩니다.</p>
                </div>
            </div>
        </div>

        <!-- 캐릭터 이미지 카드 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary">캐릭터 이미지</h2>
            </div>
            <div class="p-4 space-y-6">
                <!-- 두상 이미지 -->
                <div>
                    <h3 class="text-sm font-medium text-mg-text-secondary mb-3">두상 이미지</h3>
                    <div class="flex items-start gap-4">
                        <div class="w-28 h-28 bg-mg-bg-tertiary rounded-lg overflow-hidden flex-shrink-0">
                            <?php if ($is_edit && $char['ch_thumb']) { ?>
                            <img id="thumb-preview" src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                            <?php } else { ?>
                            <div id="thumb-preview" class="w-full h-full flex items-center justify-center text-mg-text-muted">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="ch_thumb" id="ch_thumb" accept="image/*"
                                   class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-mg-accent file:text-white hover:file:bg-mg-accent-hover file:cursor-pointer cursor-pointer">
                            <p class="text-xs text-mg-text-muted mt-2">프로필, 댓글 등에 표시됩니다. (정사각형 권장)</p>
                            <?php if ($is_edit && $char['ch_thumb']) { ?>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" name="del_thumb" value="1" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-red-500 focus:ring-red-500 focus:ring-offset-0">
                                <span class="text-sm text-red-400">삭제</span>
                            </label>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- 전신 이미지 -->
                <div>
                    <h3 class="text-sm font-medium text-mg-text-secondary mb-3">전신 이미지 <span class="text-mg-text-muted font-normal">(선택)</span></h3>
                    <div class="flex items-start gap-4">
                        <div class="w-28 h-36 bg-mg-bg-tertiary rounded-lg overflow-hidden flex-shrink-0">
                            <?php if ($is_edit && ($char['ch_image'] ?? '')) { ?>
                            <img id="body-preview" src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_image']; ?>" alt="" class="w-full h-full object-cover">
                            <?php } else { ?>
                            <div id="body-preview" class="w-full h-full flex items-center justify-center text-mg-text-muted">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="flex-1">
                            <input type="file" name="ch_image" id="ch_image" accept="image/*"
                                   class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-mg-accent file:text-white hover:file:bg-mg-accent-hover file:cursor-pointer cursor-pointer">
                            <p class="text-xs text-mg-text-muted mt-2">캐릭터 상세 페이지에 표시됩니다.</p>
                            <?php if ($is_edit && ($char['ch_image'] ?? '')) { ?>
                            <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                <input type="checkbox" name="del_image" value="1" class="w-4 h-4 rounded border-mg-bg-tertiary bg-mg-bg-primary text-red-500 focus:ring-red-500 focus:ring-offset-0">
                                <span class="text-sm text-red-400">삭제</span>
                            </label>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 프로필 정보 (동적 필드) -->
        <?php foreach ($grouped_fields as $category => $fields) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary"><?php echo $category; ?></h2>
            </div>
            <div class="p-4 space-y-4">
                <?php foreach ($fields as $field) { ?>
                <div>
                    <label for="pf_<?php echo $field['pf_id']; ?>" class="block text-sm font-medium text-mg-text-secondary mb-1.5">
                        <?php echo $field['pf_name']; ?>
                        <?php if ($field['pf_required']) { ?><span class="text-red-400">*</span><?php } ?>
                    </label>

                    <?php if ($field['pf_type'] == 'text') { ?>
                    <input type="text" name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>"
                           value="<?php echo htmlspecialchars($field['value']); ?>"
                           placeholder="<?php echo $field['pf_placeholder']; ?>"
                           <?php echo $field['pf_required'] ? 'required' : ''; ?>
                           class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors">

                    <?php } elseif ($field['pf_type'] == 'textarea') { ?>
                    <textarea name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>" rows="4"
                              placeholder="<?php echo $field['pf_placeholder']; ?>"
                              <?php echo $field['pf_required'] ? 'required' : ''; ?>
                              class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors resize-none"><?php echo htmlspecialchars($field['value']); ?></textarea>

                    <?php } elseif ($field['pf_type'] == 'select') {
                        $options = json_decode($field['pf_options'], true) ?: array();
                    ?>
                    <select name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>"
                            <?php echo $field['pf_required'] ? 'required' : ''; ?>
                            class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary focus:outline-none focus:border-mg-accent transition-colors">
                        <option value="">선택</option>
                        <?php foreach ($options as $opt) { ?>
                        <option value="<?php echo $opt; ?>" <?php echo $field['value'] == $opt ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                        <?php } ?>
                    </select>

                    <?php } elseif ($field['pf_type'] == 'url') { ?>
                    <input type="url" name="profile[<?php echo $field['pf_id']; ?>]" id="pf_<?php echo $field['pf_id']; ?>"
                           value="<?php echo htmlspecialchars($field['value']); ?>"
                           placeholder="<?php echo $field['pf_placeholder'] ?: 'https://'; ?>"
                           <?php echo $field['pf_required'] ? 'required' : ''; ?>
                           class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2.5 text-mg-text-primary placeholder-mg-text-muted focus:outline-none focus:border-mg-accent transition-colors">

                    <?php } ?>

                    <?php if ($field['pf_help']) { ?>
                    <p class="text-xs text-mg-text-muted mt-1"><?php echo $field['pf_help']; ?></p>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <!-- 버튼 -->
        <div class="flex items-center gap-3">
            <button type="submit" name="btn_save" value="save" class="flex-1 bg-mg-accent hover:bg-mg-accent-hover text-white font-medium py-3 rounded-lg transition-colors">
                <?php echo $is_edit ? '저장하기' : '캐릭터 만들기'; ?>
            </button>
            <?php if (!$is_edit || ($char['ch_state'] ?? '') == 'editing') { ?>
            <button type="submit" name="btn_submit" value="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition-colors">
                승인 신청
            </button>
            <?php } ?>
        </div>

        <?php if ($is_edit) { ?>
        <!-- 삭제 버튼 -->
        <div class="pt-4 border-t border-mg-bg-tertiary">
            <button type="button" onclick="deleteCharacter()" class="text-sm text-red-400 hover:text-red-300 transition-colors">
                캐릭터 삭제
            </button>
        </div>
        <?php } ?>
    </form>
</div>

<script>
// 이미지 미리보기 함수
function setupImagePreview(inputId, previewId) {
    document.getElementById(inputId)?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById(previewId);
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// 두상/전신 이미지 미리보기
setupImagePreview('ch_thumb', 'thumb-preview');
setupImagePreview('ch_image', 'body-preview');

// 캐릭터 삭제
function deleteCharacter() {
    if (confirm('정말 이 캐릭터를 삭제하시겠습니까?\n삭제된 캐릭터는 복구할 수 없습니다.')) {
        const form = document.getElementById('fcharform');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'btn_delete';
        input.value = '1';
        form.appendChild(input);
        form.submit();
    }
}
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
