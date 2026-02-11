<?php
if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-mg-text-primary"><?php echo $g5['title']; ?></h2>
        <a href="<?php echo G5_BBS_URL; ?>/inventory.php?tab=emoticon" class="btn btn-secondary text-sm">돌아가기</a>
    </div>

    <?php if ($is_edit && $set['es_status'] === 'rejected' && $set['es_reject_reason']) { ?>
    <div class="bg-mg-error/10 border border-mg-error/30 rounded-lg p-4 mb-4 text-sm text-mg-error">
        <strong>반려 사유:</strong> <?php echo htmlspecialchars($set['es_reject_reason']); ?>
        <p class="mt-1 text-xs text-mg-text-muted">수정 후 다시 심사 요청할 수 있습니다. (추가 등록권 불요)</p>
    </div>
    <?php } ?>

    <form name="femoticoncreate" id="femoticoncreate" method="post" action="<?php echo G5_BBS_URL; ?>/emoticon_create_update.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'insert'; ?>">
        <input type="hidden" name="es_id" value="<?php echo $is_edit ? $set['es_id'] : 0; ?>">

        <!-- 기본 정보 -->
        <div class="card mb-4">
            <div class="card-header">기본 정보</div>
            <div class="p-4 space-y-4">
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1" for="es_name">셋 이름 <span class="text-mg-error">*</span></label>
                    <input type="text" name="es_name" id="es_name" value="<?php echo $is_edit ? htmlspecialchars($set['es_name']) : ''; ?>" class="input" maxlength="100" required placeholder="이모티콘 셋 이름">
                </div>
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1" for="es_desc">설명</label>
                    <textarea name="es_desc" id="es_desc" class="input resize-none" rows="2" placeholder="이모티콘 셋에 대한 설명"><?php echo $is_edit ? htmlspecialchars($set['es_desc']) : ''; ?></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1" for="es_price">판매 가격 (포인트)</label>
                        <input type="number" name="es_price" id="es_price" value="<?php echo $is_edit ? (int)$set['es_price'] : 0; ?>" class="input" min="0">
                        <p class="text-xs text-mg-text-muted mt-1">0이면 무료 배포 | 판매 수수료 <?php echo (int)mg_config('emoticon_commission_rate', 10); ?>%</p>
                    </div>
                    <div>
                        <label class="block text-sm text-mg-text-secondary mb-1">미리보기 이미지</label>
                        <input type="file" name="es_preview_file" accept="image/*" class="input text-sm" onchange="previewThumb(this)">
                        <div id="thumbPreview" class="mt-2">
                            <?php if ($is_edit && $set['es_preview']) { ?>
                            <img src="<?php echo htmlspecialchars($set['es_preview']); ?>" alt="" class="w-16 h-16 object-contain rounded">
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 이모티콘 이미지 -->
        <div class="card mb-4">
            <div class="card-header">
                이모티콘 이미지
                <span class="text-mg-text-muted font-normal text-sm ml-2" id="emoticonCount">
                    (<?php echo count($emoticons); ?> / <?php echo $min_count; ?>~<?php echo $max_count; ?>개)
                </span>
            </div>
            <div class="p-4">
                <div class="bg-mg-bg-primary border-2 border-dashed border-mg-bg-tertiary rounded-lg p-6 text-center cursor-pointer hover:border-mg-accent transition-colors"
                     id="uploadZone" onclick="document.getElementById('emoticonFiles').click();">
                    <svg class="w-8 h-8 mx-auto mb-2 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-mg-text-muted">이미지를 드래그하거나 클릭하여 업로드</p>
                    <p class="text-xs text-mg-text-muted mt-1">PNG, GIF, WebP | 최대 <?php echo $max_size; ?>KB | 권장 <?php echo $rec_size; ?>x<?php echo $rec_size; ?>px</p>
                </div>
                <input type="file" id="emoticonFiles" name="emoticon_files[]" multiple accept="image/*" style="display:none;" onchange="handleNewFiles(this.files)">

                <!-- 새 이모티콘 -->
                <div id="newEmoticons" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-3"></div>

                <!-- 기존 이모티콘 -->
                <?php if (!empty($emoticons)) { ?>
                <h4 class="text-sm font-medium text-mg-text-secondary mt-4 mb-2">등록된 이모티콘</h4>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    <?php foreach ($emoticons as $em) { ?>
                    <div class="bg-mg-bg-primary rounded-lg p-2 text-center relative group" id="em_<?php echo $em['em_id']; ?>">
                        <button type="button" class="absolute top-1 right-1 w-5 h-5 bg-mg-error text-white rounded-full text-xs hidden group-hover:flex items-center justify-center"
                                onclick="markDelete(<?php echo $em['em_id']; ?>)">&times;</button>
                        <img src="<?php echo htmlspecialchars($em['em_image']); ?>" alt="" class="w-10 h-10 mx-auto object-contain mb-1">
                        <input type="text" name="em_codes[<?php echo $em['em_id']; ?>]" value="<?php echo htmlspecialchars($em['em_code']); ?>"
                               class="w-full bg-transparent text-xs text-mg-text-muted text-center border-0 outline-none focus:text-mg-accent p-0">
                        <input type="hidden" name="em_ids[]" value="<?php echo $em['em_id']; ?>">
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>

                <input type="hidden" name="delete_em_ids" id="deleteEmIds" value="">
            </div>
        </div>

        <!-- 액션 버튼 -->
        <div class="flex gap-3">
            <button type="submit" class="btn btn-secondary">저장</button>
            <?php if ($is_edit && in_array($set['es_status'], array('draft', 'rejected'))) { ?>
            <button type="button" onclick="submitForReview()" class="btn btn-primary">심사 요청</button>
            <?php } ?>
        </div>
    </form>
</div>

<script>
var deleteEmIds = [];

// 드래그앤드롭
var zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('border-mg-accent'); });
zone.addEventListener('dragleave', function() { this.classList.remove('border-mg-accent'); });
zone.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('border-mg-accent');
    handleNewFiles(e.dataTransfer.files);
});

function handleNewFiles(files) {
    var container = document.getElementById('newEmoticons');
    for (var i = 0; i < files.length; i++) {
        var file = files[i];
        if (!file.type.startsWith('image/')) continue;

        var reader = new FileReader();
        reader.onload = (function(f) {
            return function(e) {
                var baseName = f.name.replace(/\.[^.]+$/, '').replace(/[^a-zA-Z0-9_]/g, '_').toLowerCase();
                var code = ':' + baseName + ':';

                var div = document.createElement('div');
                div.className = 'bg-mg-bg-primary rounded-lg p-2 text-center relative group';
                div.innerHTML =
                    '<button type="button" class="absolute top-1 right-1 w-5 h-5 bg-mg-error text-white rounded-full text-xs hidden group-hover:flex items-center justify-center" onclick="this.parentNode.remove();">&times;</button>' +
                    '<img src="' + e.target.result + '" alt="" class="w-10 h-10 mx-auto object-contain mb-1">' +
                    '<input type="text" name="new_em_codes[]" value="' + code + '" class="w-full bg-transparent text-xs text-mg-text-muted text-center border-0 outline-none focus:text-mg-accent p-0">';
                container.appendChild(div);
            };
        })(file);
        reader.readAsDataURL(file);
    }
}

function markDelete(emId) {
    if (!confirm('이 이모티콘을 삭제하시겠습니까?')) return;
    deleteEmIds.push(emId);
    document.getElementById('deleteEmIds').value = deleteEmIds.join(',');
    var el = document.getElementById('em_' + emId);
    if (el) el.style.display = 'none';
}

function submitForReview() {
    if (!confirm('심사를 요청하시겠습니까?\n<?php if (!$is_edit || $set['es_status'] === 'draft') echo '이모티콘 등록권 1개가 소모됩니다.'; ?>')) return;

    var form = document.getElementById('femoticoncreate');
    // 먼저 저장한 후 심사 요청
    var actionInput = form.querySelector('input[name="action"]');
    actionInput.value = 'submit';
    form.submit();
}

function previewThumb(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('thumbPreview').innerHTML = '<img src="' + e.target.result + '" alt="" class="w-16 h-16 object-contain rounded">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
