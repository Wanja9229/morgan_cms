<?php
/**
 * Morgan Edition - 이모티콘 셋 등록/수정 (관리자)
 */

$sub_menu = "800950";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$es_id = isset($_GET['es_id']) ? (int)$_GET['es_id'] : 0;
$is_edit = $es_id > 0;

// 기본값
$set = array(
    'es_id' => 0,
    'es_name' => '',
    'es_desc' => '',
    'es_preview' => '',
    'es_price' => 0,
    'es_order' => 0,
    'es_use' => 1,
    'es_creator_id' => null,
    'es_status' => 'approved',
    'es_reject_reason' => '',
    'es_sales_count' => 0,
    'es_total_revenue' => 0,
);

$emoticons = array();

if ($is_edit) {
    $loaded = mg_get_emoticon_set($es_id);
    if (!$loaded) {
        alert('존재하지 않는 이모티콘 셋입니다.', './emoticon_list.php');
        exit;
    }
    foreach ($loaded as $key => $value) {
        if (array_key_exists($key, $set)) {
            $set[$key] = $value;
        }
    }
    $emoticons = mg_get_emoticons($es_id);
}

$g5['title'] = $is_edit ? '이모티콘 셋 수정' : '이모티콘 셋 등록';
require_once __DIR__.'/_head.php';
?>

<style>
.mg-emoticon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 0.75rem;
    margin-top: 1rem;
}
.mg-emoticon-item {
    background: var(--mg-bg-primary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 0.5rem;
    padding: 0.75rem;
    text-align: center;
    position: relative;
}
.mg-emoticon-item img {
    width: 64px; height: 64px;
    object-fit: contain;
    margin-bottom: 0.5rem;
}
.mg-emoticon-item .em-code {
    font-size: 0.75rem;
    color: var(--mg-text-muted);
    word-break: break-all;
}
.mg-emoticon-item .em-delete {
    position: absolute; top: 4px; right: 4px;
    background: var(--mg-error); color: #fff;
    border: none; border-radius: 50%;
    width: 20px; height: 20px;
    cursor: pointer; font-size: 0.7rem;
    display: flex; align-items: center; justify-content: center;
}
.mg-upload-zone {
    border: 2px dashed var(--mg-bg-tertiary);
    border-radius: 0.5rem;
    padding: 2rem;
    text-align: center;
    color: var(--mg-text-muted);
    cursor: pointer;
    transition: border-color 0.2s;
}
.mg-upload-zone:hover,
.mg-upload-zone.dragover {
    border-color: var(--mg-accent);
    color: var(--mg-accent);
}
</style>

<form name="femoticon" id="femoticon" method="post" action="./emoticon_form_update.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'insert'; ?>">
    <input type="hidden" name="es_id" value="<?php echo $es_id; ?>">

    <div class="mg-card">
        <div class="mg-card-header">
            <?php echo $g5['title']; ?>
            <?php if ($is_edit && $set['es_creator_id']) { ?>
            <span style="font-weight:normal;font-size:0.85rem;color:var(--mg-text-muted);margin-left:0.5rem;">
                (제작자: <?php echo htmlspecialchars($set['es_creator_id']); ?>)
            </span>
            <?php } ?>
        </div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="es_name">셋 이름 <span style="color:var(--mg-error);">*</span></label>
                    <input type="text" name="es_name" id="es_name" value="<?php echo htmlspecialchars($set['es_name']); ?>" class="mg-form-input" required>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label" for="es_price">가격 (포인트)</label>
                    <input type="number" name="es_price" id="es_price" value="<?php echo (int)$set['es_price']; ?>" class="mg-form-input" min="0">
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">0이면 무료</small>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label" for="es_order">정렬 순서</label>
                    <input type="number" name="es_order" id="es_order" value="<?php echo (int)$set['es_order']; ?>" class="mg-form-input" min="0">
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">사용 여부</label>
                    <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="radio" name="es_use" value="1" <?php echo $set['es_use'] ? 'checked' : ''; ?>>
                            <span>사용</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="radio" name="es_use" value="0" <?php echo !$set['es_use'] ? 'checked' : ''; ?>>
                            <span>사용안함</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label" for="es_desc">설명</label>
                <textarea name="es_desc" id="es_desc" class="mg-form-textarea" rows="3"><?php echo htmlspecialchars($set['es_desc']); ?></textarea>
            </div>

            <div class="mg-form-group" style="max-width:400px;">
                <label class="mg-form-label">미리보기 이미지</label>
                <input type="file" name="es_preview_file" accept="image/*" class="mg-form-input" onchange="previewImage(this, 'previewImg')">
                <div id="previewImg" style="margin-top:0.5rem;">
                    <?php if ($set['es_preview']) { ?>
                    <img src="<?php echo htmlspecialchars($set['es_preview']); ?>" alt="미리보기" style="max-width:128px;max-height:128px;border-radius:8px;">
                    <?php } ?>
                </div>
            </div>

            <?php if ($is_edit && $set['es_status'] === 'rejected' && $set['es_reject_reason']) { ?>
            <div class="mg-alert mg-alert-error" style="margin-top:1rem;">
                <strong>반려 사유:</strong> <?php echo htmlspecialchars($set['es_reject_reason']); ?>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 이모티콘 이미지 관리 -->
    <div class="mg-card" style="margin-top:1rem;">
        <div class="mg-card-header">
            이모티콘 이미지
            <span style="font-weight:normal;font-size:0.85rem;color:var(--mg-text-muted);">
                (<?php echo count($emoticons); ?>개)
            </span>
        </div>
        <div class="mg-card-body">
            <!-- 업로드 영역 -->
            <div class="mg-upload-zone" id="uploadZone" onclick="document.getElementById('emoticonFiles').click();">
                이미지 파일을 드래그하거나 클릭하여 업로드<br>
                <small>PNG, GIF, WebP 권장 | 최대 <?php echo mg_config('emoticon_image_max_size', 512); ?>KB</small>
            </div>
            <input type="file" id="emoticonFiles" name="emoticon_files[]" multiple accept="image/*" style="display:none;" onchange="handleFiles(this.files)">

            <!-- 새로 추가할 이모티콘 -->
            <div id="newEmoticons" class="mg-emoticon-grid"></div>

            <!-- 기존 이모티콘 -->
            <?php if (!empty($emoticons)) { ?>
            <h4 style="margin-top:1.5rem;margin-bottom:0.5rem;font-size:0.9rem;color:var(--mg-text-secondary);">등록된 이모티콘</h4>
            <div class="mg-emoticon-grid">
                <?php foreach ($emoticons as $em) { ?>
                <div class="mg-emoticon-item" id="em_<?php echo $em['em_id']; ?>">
                    <button type="button" class="em-delete" onclick="deleteEmoticon(<?php echo $em['em_id']; ?>)" title="삭제">&times;</button>
                    <img src="<?php echo htmlspecialchars($em['em_image']); ?>" alt="">
                    <input type="text" name="em_codes[<?php echo $em['em_id']; ?>]" value="<?php echo htmlspecialchars($em['em_code']); ?>"
                           class="mg-form-input" style="font-size:0.75rem;padding:0.25rem 0.5rem;margin-top:0.25rem;">
                    <input type="hidden" name="em_ids[]" value="<?php echo $em['em_id']; ?>">
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <input type="hidden" name="delete_em_ids" id="deleteEmIds" value="">
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '수정' : '등록'; ?></button>
        <a href="./emoticon_list.php" class="mg-btn mg-btn-secondary">목록으로</a>
    </div>
</form>

<script>
var newFileIndex = 0;
var deleteEmIds = [];

// 드래그앤드롭
var zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});
zone.addEventListener('dragleave', function() {
    this.classList.remove('dragover');
});
zone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    handleFiles(e.dataTransfer.files);
});

function handleFiles(files) {
    var container = document.getElementById('newEmoticons');
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        if (!file.type.startsWith('image/')) continue;

        var idx = newFileIndex++;
        var reader = new FileReader();
        reader.onload = (function(f, idx) {
            return function(e) {
                var baseName = f.name.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_]/g, '_').toLowerCase();
                var code = ':' + baseName + ':';

                var div = document.createElement('div');
                div.className = 'mg-emoticon-item';
                div.innerHTML =
                    '<button type="button" class="em-delete" onclick="this.parentNode.remove();">&times;</button>' +
                    '<img src="' + e.target.result + '" alt="">' +
                    '<input type="text" name="new_em_codes[]" value="' + code + '" class="mg-form-input" style="font-size:0.75rem;padding:0.25rem 0.5rem;margin-top:0.25rem;">';
                container.appendChild(div);
            };
        })(file, idx);
        reader.readAsDataURL(file);
    }

    // DataTransfer로 파일 input에 추가 (브라우저 제약으로 별도 hidden input 사용)
    var input = document.getElementById('emoticonFiles');
    // 폼 제출 시 파일이 포함되도록 clone 방식 사용
    var clone = input.cloneNode(true);
    clone.style.display = 'none';
    clone.removeAttribute('id');
    clone.name = 'emoticon_files[]';
    document.getElementById('femoticon').appendChild(clone);
}

function deleteEmoticon(emId) {
    if (!confirm('이 이모티콘을 삭제하시겠습니까?')) return;
    deleteEmIds.push(emId);
    document.getElementById('deleteEmIds').value = deleteEmIds.join(',');
    var el = document.getElementById('em_' + emId);
    if (el) el.style.display = 'none';
}

function previewImage(input, targetId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(targetId).innerHTML =
                '<img src="' + e.target.result + '" alt="미리보기" style="max-width:128px;max-height:128px;border-radius:8px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
