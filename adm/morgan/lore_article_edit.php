<?php
/**
 * Morgan Edition - 위키 문서 작성/수정
 */

$sub_menu = "801410";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 카테고리 목록
$categories = array();
$cat_result = sql_query("SELECT * FROM {$g5['mg_lore_category_table']} ORDER BY lc_order, lc_id");
while ($row = sql_fetch_array($cat_result)) {
    $categories[] = $row;
}

// 수정 모드
$la_id = isset($_GET['la_id']) ? (int)$_GET['la_id'] : 0;
$article = null;
$sections = array();
$is_edit = false;

if ($la_id > 0) {
    $article = sql_fetch("SELECT * FROM {$g5['mg_lore_article_table']} WHERE la_id = {$la_id}");
    if ($article['la_id']) {
        $is_edit = true;
        // 섹션 로드
        $sec_result = sql_query("SELECT * FROM {$g5['mg_lore_section_table']} WHERE la_id = {$la_id} ORDER BY ls_order, ls_id");
        while ($row = sql_fetch_array($sec_result)) {
            $sections[] = $row;
        }
    }
}

$g5['title'] = $is_edit ? '문서 수정: ' . htmlspecialchars($article['la_title']) : '새 문서 작성';
require_once __DIR__.'/_head.php';

$upload_url = G5_ADMIN_URL . '/morgan/lore_image_upload.php';
$update_url = G5_ADMIN_URL . '/morgan/lore_article_update.php';
?>

<div style="margin-bottom:1rem;">
    <a href="./lore_article.php" class="mg-btn mg-btn-secondary mg-btn-sm">&larr; 문서 목록</a>
    <span style="margin-left:0.5rem;font-size:1.125rem;font-weight:600;"><?php echo $is_edit ? '문서 수정' : '새 문서 작성'; ?></span>
</div>

<form id="article-form" method="post" action="<?php echo $update_url; ?>" enctype="multipart/form-data">
    <?php if ($is_edit) { ?>
    <input type="hidden" name="la_id" value="<?php echo $article['la_id']; ?>">
    <?php } ?>

    <!-- 기본 정보 -->
    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">기본 정보</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">카테고리 *</label>
                    <select name="lc_id" class="mg-form-input" required>
                        <option value="">선택하세요</option>
                        <?php foreach ($categories as $cat) { ?>
                        <option value="<?php echo $cat['lc_id']; ?>" <?php echo ($is_edit && $article['lc_id'] == $cat['lc_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['lc_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">정렬 순서</label>
                    <input type="number" name="la_order" value="<?php echo $is_edit ? $article['la_order'] : 0; ?>" class="mg-form-input" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">공개 여부</label>
                    <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                            <input type="radio" name="la_use" value="1" <?php echo (!$is_edit || $article['la_use']) ? 'checked' : ''; ?>>
                            ON (공개)
                        </label>
                        <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                            <input type="radio" name="la_use" value="0" <?php echo ($is_edit && !$article['la_use']) ? 'checked' : ''; ?>>
                            OFF (비공개)
                        </label>
                    </div>
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">제목 *</label>
                <input type="text" name="la_title" value="<?php echo $is_edit ? htmlspecialchars($article['la_title']) : ''; ?>" class="mg-form-input" placeholder="문서 제목" required>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">부제목</label>
                <input type="text" name="la_subtitle" value="<?php echo $is_edit ? htmlspecialchars($article['la_subtitle']) : ''; ?>" class="mg-form-input" placeholder="부제목 (선택)">
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">한줄 요약</label>
                <textarea name="la_summary" class="mg-form-input" rows="2" placeholder="문서의 간략한 요약 (목록에서 표시)"><?php echo $is_edit ? htmlspecialchars($article['la_summary']) : ''; ?></textarea>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">썸네일 이미지</label>
                <div style="display:flex;gap:1rem;align-items:flex-start;">
                    <div id="thumb-preview" style="width:120px;height:120px;border:1px dashed var(--mg-bg-tertiary);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                        <?php if ($is_edit && $article['la_thumbnail']) { ?>
                        <img src="<?php echo htmlspecialchars($article['la_thumbnail']); ?>" style="width:100%;height:100%;object-fit:cover;">
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);font-size:0.75rem;text-align:center;">미리보기</span>
                        <?php } ?>
                    </div>
                    <div style="flex:1;">
                        <input type="hidden" name="la_thumbnail" id="la_thumbnail" value="<?php echo $is_edit ? htmlspecialchars($article['la_thumbnail']) : ''; ?>">
                        <input type="file" id="thumb-file" accept="image/*" class="mg-form-input" style="margin-bottom:0.5rem;" onchange="uploadThumbnail(this)">
                        <div style="font-size:0.75rem;color:var(--mg-text-muted);">권장: 정사각형, 최소 200x200px (jpg, png, webp)</div>
                        <?php if ($is_edit && $article['la_thumbnail']) { ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" style="margin-top:0.5rem;" onclick="removeThumbnail()">썸네일 제거</button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 섹션 영역 -->
    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span>섹션 (<span id="section-count"><?php echo count($sections); ?></span>개)</span>
            <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="addSection()">+ 섹션 추가</button>
        </div>
        <div class="mg-card-body" id="sections-container">
            <?php if (empty($sections) && !$is_edit) { ?>
            <div id="no-sections-msg" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">
                아직 섹션이 없습니다. [+ 섹션 추가] 버튼을 눌러 내용을 추가하세요.
            </div>
            <?php } ?>

            <?php foreach ($sections as $idx => $sec) { ?>
            <div class="section-block" data-index="<?php echo $idx; ?>" style="border:1px solid var(--mg-bg-tertiary);border-radius:8px;padding:1rem;margin-bottom:1rem;background:var(--mg-bg-primary);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <strong style="color:var(--mg-accent);font-size:0.875rem;">섹션 #<span class="section-num"><?php echo $idx + 1; ?></span></strong>
                    <div style="display:flex;gap:0.25rem;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="moveSectionUp(this)" title="위로">&uarr;</button>
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="moveSectionDown(this)" title="아래로">&darr;</button>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="removeSection(this)">삭제</button>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr auto;gap:1rem;margin-bottom:1rem;">
                    <div class="mg-form-group" style="margin-bottom:0;">
                        <label class="mg-form-label">섹션명</label>
                        <input type="text" name="sections[<?php echo $idx; ?>][name]" value="<?php echo htmlspecialchars($sec['ls_name']); ?>" class="mg-form-input section-name" placeholder="섹션 제목">
                    </div>
                    <div class="mg-form-group" style="margin-bottom:0;">
                        <label class="mg-form-label">타입</label>
                        <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                            <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                                <input type="radio" name="sections[<?php echo $idx; ?>][type]" value="text" class="section-type-radio" <?php echo $sec['ls_type'] == 'text' ? 'checked' : ''; ?> onchange="toggleSectionType(this)">
                                텍스트
                            </label>
                            <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                                <input type="radio" name="sections[<?php echo $idx; ?>][type]" value="image" class="section-type-radio" <?php echo $sec['ls_type'] == 'image' ? 'checked' : ''; ?> onchange="toggleSectionType(this)">
                                이미지
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 텍스트 영역 -->
                <div class="section-text-area" style="<?php echo $sec['ls_type'] == 'image' ? 'display:none;' : ''; ?>">
                    <div class="mg-form-group" style="margin-bottom:0;">
                        <label class="mg-form-label">내용</label>
                        <textarea name="sections[<?php echo $idx; ?>][content]" class="mg-form-input section-content" rows="6" placeholder="섹션 내용을 입력하세요."><?php echo htmlspecialchars($sec['ls_content']); ?></textarea>
                    </div>
                </div>

                <!-- 이미지 영역 -->
                <div class="section-image-area" style="<?php echo $sec['ls_type'] == 'text' ? 'display:none;' : ''; ?>">
                    <div class="mg-form-group" style="margin-bottom:0.5rem;">
                        <label class="mg-form-label">이미지</label>
                        <input type="hidden" name="sections[<?php echo $idx; ?>][image]" class="section-image-url" value="<?php echo htmlspecialchars($sec['ls_image']); ?>">
                        <input type="hidden" name="sections[<?php echo $idx; ?>][existing_image]" class="section-existing-image" value="<?php echo htmlspecialchars($sec['ls_image']); ?>">
                        <div style="display:flex;gap:1rem;align-items:flex-start;">
                            <div class="section-image-preview" style="width:160px;height:100px;border:1px dashed var(--mg-bg-tertiary);border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                <?php if ($sec['ls_image']) { ?>
                                <img src="<?php echo htmlspecialchars($sec['ls_image']); ?>" style="width:100%;height:100%;object-fit:cover;">
                                <?php } else { ?>
                                <span style="color:var(--mg-text-muted);font-size:0.7rem;">미리보기</span>
                                <?php } ?>
                            </div>
                            <div style="flex:1;">
                                <input type="file" accept="image/*" class="mg-form-input section-image-file" onchange="uploadSectionImage(this)" style="margin-bottom:0.5rem;">
                                <div style="font-size:0.75rem;color:var(--mg-text-muted);">jpg, png, gif, webp (최대 2MB)</div>
                            </div>
                        </div>
                    </div>
                    <div class="mg-form-group" style="margin-bottom:0;">
                        <label class="mg-form-label">이미지 캡션</label>
                        <input type="text" name="sections[<?php echo $idx; ?>][image_caption]" class="mg-form-input section-caption" value="<?php echo htmlspecialchars($sec['ls_image_caption']); ?>" placeholder="이미지 설명 (선택)">
                    </div>
                </div>

                <input type="hidden" name="sections[<?php echo $idx; ?>][order]" class="section-order" value="<?php echo $idx; ?>">
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 하단 버튼 -->
    <div style="display:flex;gap:0.5rem;justify-content:space-between;">
        <div style="display:flex;gap:0.5rem;">
            <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '문서 수정' : '문서 등록'; ?></button>
            <a href="./lore_article.php" class="mg-btn mg-btn-secondary">취소</a>
        </div>
        <?php if ($is_edit) { ?>
        <div style="font-size:0.8rem;color:var(--mg-text-muted);display:flex;align-items:center;">
            작성일: <?php echo $article['la_created']; ?> | 수정일: <?php echo $article['la_updated']; ?> | 조회: <?php echo number_format($article['la_hit']); ?>
        </div>
        <?php } ?>
    </div>
</form>

<!-- 섹션 템플릿 (JS에서 복제) -->
<template id="section-template">
    <div class="section-block" style="border:1px solid var(--mg-bg-tertiary);border-radius:8px;padding:1rem;margin-bottom:1rem;background:var(--mg-bg-primary);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <strong style="color:var(--mg-accent);font-size:0.875rem;">섹션 #<span class="section-num">0</span></strong>
            <div style="display:flex;gap:0.25rem;">
                <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="moveSectionUp(this)" title="위로">&uarr;</button>
                <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="moveSectionDown(this)" title="아래로">&darr;</button>
                <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="removeSection(this)">삭제</button>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr auto;gap:1rem;margin-bottom:1rem;">
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">섹션명</label>
                <input type="text" class="mg-form-input section-name" placeholder="섹션 제목">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">타입</label>
                <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                    <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                        <input type="radio" value="text" class="section-type-radio" checked onchange="toggleSectionType(this)">
                        텍스트
                    </label>
                    <label style="display:flex;align-items:center;gap:0.375rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                        <input type="radio" value="image" class="section-type-radio" onchange="toggleSectionType(this)">
                        이미지
                    </label>
                </div>
            </div>
        </div>

        <!-- 텍스트 영역 -->
        <div class="section-text-area">
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">내용</label>
                <textarea class="mg-form-input section-content" rows="6" placeholder="섹션 내용을 입력하세요."></textarea>
            </div>
        </div>

        <!-- 이미지 영역 -->
        <div class="section-image-area" style="display:none;">
            <div class="mg-form-group" style="margin-bottom:0.5rem;">
                <label class="mg-form-label">이미지</label>
                <input type="hidden" class="section-image-url" value="">
                <input type="hidden" class="section-existing-image" value="">
                <div style="display:flex;gap:1rem;align-items:flex-start;">
                    <div class="section-image-preview" style="width:160px;height:100px;border:1px dashed var(--mg-bg-tertiary);border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                        <span style="color:var(--mg-text-muted);font-size:0.7rem;">미리보기</span>
                    </div>
                    <div style="flex:1;">
                        <input type="file" accept="image/*" class="mg-form-input section-image-file" onchange="uploadSectionImage(this)" style="margin-bottom:0.5rem;">
                        <div style="font-size:0.75rem;color:var(--mg-text-muted);">jpg, png, gif, webp (최대 2MB)</div>
                    </div>
                </div>
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">이미지 캡션</label>
                <input type="text" class="mg-form-input section-caption" placeholder="이미지 설명 (선택)">
            </div>
        </div>

        <input type="hidden" class="section-order" value="0">
    </div>
</template>

<script>
var _uploadUrl = '<?php echo $upload_url; ?>';
var _sectionIdx = <?php echo count($sections); ?>;

// === 썸네일 업로드 ===
function uploadThumbnail(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    var fd = new FormData();
    fd.append('file', file);
    fd.append('type', 'article_thumb');

    fetch(_uploadUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('la_thumbnail').value = data.url;
            document.getElementById('thumb-preview').innerHTML = '<img src="' + data.url + '" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            alert(data.message || '업로드 실패');
        }
    })
    .catch(function() { alert('업로드 중 오류가 발생했습니다.'); });
}

function removeThumbnail() {
    document.getElementById('la_thumbnail').value = '';
    document.getElementById('thumb-preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.75rem;text-align:center;">미리보기</span>';
}

// === 섹션 이미지 업로드 ===
function uploadSectionImage(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    var block = input.closest('.section-block');
    var fd = new FormData();
    fd.append('file', file);
    fd.append('type', 'section');

    fetch(_uploadUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            block.querySelector('.section-image-url').value = data.url;
            block.querySelector('.section-image-preview').innerHTML = '<img src="' + data.url + '" style="width:100%;height:100%;object-fit:cover;">';
        } else {
            alert(data.message || '업로드 실패');
        }
    })
    .catch(function() { alert('업로드 중 오류가 발생했습니다.'); });
}

// === 섹션 타입 토글 ===
function toggleSectionType(radio) {
    var block = radio.closest('.section-block');
    var type = radio.value;
    var textArea = block.querySelector('.section-text-area');
    var imageArea = block.querySelector('.section-image-area');
    if (type === 'text') {
        textArea.style.display = '';
        imageArea.style.display = 'none';
    } else {
        textArea.style.display = 'none';
        imageArea.style.display = '';
    }
}

// === 섹션 추가 ===
function addSection() {
    var container = document.getElementById('sections-container');
    var noMsg = document.getElementById('no-sections-msg');
    if (noMsg) noMsg.remove();

    var template = document.getElementById('section-template');
    var clone = template.content.cloneNode(true);
    var block = clone.querySelector('.section-block');
    block.setAttribute('data-index', _sectionIdx);

    // name 속성 설정
    var nameInput = block.querySelector('.section-name');
    nameInput.name = 'sections[' + _sectionIdx + '][name]';

    var typeRadios = block.querySelectorAll('.section-type-radio');
    typeRadios[0].name = 'sections[' + _sectionIdx + '][type]';
    typeRadios[1].name = 'sections[' + _sectionIdx + '][type]';

    var contentTa = block.querySelector('.section-content');
    contentTa.name = 'sections[' + _sectionIdx + '][content]';

    var imageUrl = block.querySelector('.section-image-url');
    imageUrl.name = 'sections[' + _sectionIdx + '][image]';

    var existingImage = block.querySelector('.section-existing-image');
    existingImage.name = 'sections[' + _sectionIdx + '][existing_image]';

    var caption = block.querySelector('.section-caption');
    caption.name = 'sections[' + _sectionIdx + '][image_caption]';

    var order = block.querySelector('.section-order');
    order.name = 'sections[' + _sectionIdx + '][order]';
    order.value = _sectionIdx;

    container.appendChild(block);
    _sectionIdx++;
    updateSectionNumbers();
}

// === 섹션 삭제 ===
function removeSection(btn) {
    if (!confirm('이 섹션을 삭제하시겠습니까?')) return;
    var block = btn.closest('.section-block');
    block.remove();
    updateSectionNumbers();
}

// === 섹션 이동 ===
function moveSectionUp(btn) {
    var block = btn.closest('.section-block');
    var prev = block.previousElementSibling;
    if (prev && prev.classList.contains('section-block')) {
        block.parentNode.insertBefore(block, prev);
        updateSectionNumbers();
    }
}

function moveSectionDown(btn) {
    var block = btn.closest('.section-block');
    var next = block.nextElementSibling;
    if (next && next.classList.contains('section-block')) {
        block.parentNode.insertBefore(next, block);
        updateSectionNumbers();
    }
}

// === 섹션 번호/순서 업데이트 ===
function updateSectionNumbers() {
    var blocks = document.querySelectorAll('#sections-container .section-block');
    document.getElementById('section-count').textContent = blocks.length;

    blocks.forEach(function(block, idx) {
        block.querySelector('.section-num').textContent = idx + 1;
        block.querySelector('.section-order').value = idx;

        // name 속성 인덱스 재할당
        var nameInput = block.querySelector('.section-name');
        if (nameInput) nameInput.name = 'sections[' + idx + '][name]';

        var typeRadios = block.querySelectorAll('.section-type-radio');
        if (typeRadios.length >= 2) {
            typeRadios[0].name = 'sections[' + idx + '][type]';
            typeRadios[1].name = 'sections[' + idx + '][type]';
        }

        var contentTa = block.querySelector('.section-content');
        if (contentTa) contentTa.name = 'sections[' + idx + '][content]';

        var imageUrl = block.querySelector('.section-image-url');
        if (imageUrl) imageUrl.name = 'sections[' + idx + '][image]';

        var existingImage = block.querySelector('.section-existing-image');
        if (existingImage) existingImage.name = 'sections[' + idx + '][existing_image]';

        var caption = block.querySelector('.section-caption');
        if (caption) caption.name = 'sections[' + idx + '][image_caption]';

        var order = block.querySelector('.section-order');
        if (order) order.name = 'sections[' + idx + '][order]';
    });

    // _sectionIdx를 블록 수로 리셋 (새 추가 시 인덱스)
    _sectionIdx = blocks.length;
}

// === 폼 제출 전 검증 ===
document.getElementById('article-form').addEventListener('submit', function(e) {
    var title = this.querySelector('input[name="la_title"]').value.trim();
    var category = this.querySelector('select[name="lc_id"]').value;
    if (!title) {
        e.preventDefault();
        alert('제목을 입력해주세요.');
        return;
    }
    if (!category) {
        e.preventDefault();
        alert('카테고리를 선택해주세요.');
        return;
    }

    // 제출 전 섹션 인덱스 정리
    updateSectionNumbers();
});
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
