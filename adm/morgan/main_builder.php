<?php
/**
 * Morgan Edition - 메인 페이지 빌더
 */

$sub_menu = "800150"; // 메인 페이지 빌더
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');
include_once(MG_PLUGIN_PATH.'/widgets/widget.factory.php');

// 위젯 목록 로드
$widgets = array();
$sql = "SELECT * FROM {$g5['mg_main_widget_table']} ORDER BY widget_order ASC";
$result = sql_query($sql);
while ($widget = sql_fetch_array($result)) {
    $widget['widget_config'] = $widget['widget_config'] ? json_decode($widget['widget_config'], true) : array();
    $widgets[] = $widget;
}

// 위젯 타입 목록
$widget_types = mg_get_widget_types();

// 행 높이 설정
$widget_row_height = (int)mg_config('widget_row_height', 300);
$widget_grid_width = (int)mg_config('widget_grid_width', 1200);

$g5['title'] = '메인 페이지 빌더';
require_once __DIR__.'/_head.php';
?>

<style>
.mg-builder-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.5rem;
}

.mg-builder-actions {
    display: flex;
    gap: 0.5rem;
}

.mg-widget-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 1rem;
    min-height: 200px;
    padding: 1rem;
    background: var(--mg-bg-tertiary);
    border-radius: 0.5rem;
    border: 2px dashed var(--mg-bg-secondary);
}

.mg-widget-item {
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-primary);
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: grab;
    transition: all 0.2s;
    position: relative;
}

.mg-widget-item:hover {
    border-color: var(--mg-accent);
}

.mg-widget-item.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.mg-widget-item.drag-over {
    border-color: var(--mg-accent);
    box-shadow: 0 0 0 2px rgba(245, 159, 10, 0.3);
}

.mg-widget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.mg-widget-type {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--mg-accent);
}

.mg-widget-cols {
    font-size: 0.625rem;
    color: var(--mg-text-muted);
    background: var(--mg-bg-primary);
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
}

.mg-widget-preview {
    font-size: 0.8rem;
    color: var(--mg-text-secondary);
    margin-bottom: 0.75rem;
    max-height: 80px;
    overflow: hidden;
}

.mg-widget-preview img {
    max-width: 100%;
    max-height: 60px;
    border-radius: 4px;
    object-fit: cover;
    border: 1px solid var(--mg-bg-tertiary);
}

.mg-widget-actions {
    display: flex;
    gap: 0.25rem;
}

.mg-widget-btn {
    background: var(--mg-bg-tertiary);
    border: none;
    color: var(--mg-text-muted);
    cursor: pointer;
    padding: 0.375rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    transition: all 0.2s;
}

.mg-widget-btn:hover {
    background: var(--mg-bg-primary);
    color: var(--mg-text-primary);
}

.mg-widget-btn.danger:hover {
    background: var(--mg-error);
    color: #fff;
}

.mg-empty-state {
    grid-column: span 12;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    color: var(--mg-text-muted);
}

/* Builder Modal (unique prefix to avoid conflict with _head.php) */
.mgb-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s;
}

.mgb-modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.mgb-modal {
    background: var(--mg-bg-secondary);
    border-radius: 0.5rem;
    width: 100%;
    max-width: 500px;
    max-height: 80vh;
    overflow: hidden;
    transform: scale(0.9);
    transition: transform 0.2s;
    border: 1px solid var(--mg-bg-tertiary);
}

.mgb-modal-overlay.show .mgb-modal {
    transform: scale(1);
}

.mgb-modal.lg {
    max-width: 700px;
}

.mgb-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}

.mgb-modal-title {
    font-weight: 600;
}

.mgb-modal-close {
    background: none;
    border: none;
    color: var(--mg-text-muted);
    cursor: pointer;
    padding: 0.25rem;
    font-size: 1.5rem;
    line-height: 1;
}

.mgb-modal-close:hover {
    color: var(--mg-text-primary);
}

.mgb-modal-body {
    padding: 1.25rem;
    max-height: 60vh;
    overflow-y: auto;
}

.mgb-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--mg-bg-tertiary);
}

/* Widget Type Grid */
.mg-type-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
}

.mg-type-item {
    background: var(--mg-bg-tertiary);
    border: 2px solid transparent;
    border-radius: 0.5rem;
    padding: 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.mg-type-item:hover {
    border-color: var(--mg-accent);
}

.mg-type-item.selected {
    border-color: var(--mg-accent);
    background: rgba(245, 159, 10, 0.1);
}

.mg-type-icon {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.mg-type-name {
    font-size: 0.875rem;
    font-weight: 500;
}

.mg-type-desc {
    font-size: 0.7rem;
    color: var(--mg-text-muted);
    margin-top: 0.25rem;
}
</style>

<div class="mg-card">
    <div class="mg-card-header">
        <div class="mg-builder-header" style="margin-bottom:0;">
            <span>메인 페이지 빌더</span>
            <div class="mg-builder-actions">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="openAddModal()">+ 위젯 추가</button>
                <a href="<?php echo G5_URL; ?>/" target="_blank" class="mg-btn mg-btn-secondary">미리보기</a>
                <button type="button" class="mg-btn mg-btn-primary" onclick="saveOrder()">순서 저장</button>
            </div>
        </div>
    </div>
    <div class="mg-card-body">
        <div style="display:flex;gap:1rem;margin-bottom:1rem;flex-wrap:wrap;align-items:stretch;">
            <div style="background:var(--mg-bg-tertiary);padding:0.75rem 1rem;border-radius:0.5rem;min-width:200px;">
                <div style="font-size:0.75rem;color:var(--mg-text-muted);display:flex;align-items:center;gap:0.25rem;margin-bottom:0.5rem;">
                    <?php echo mg_icon('arrows-up-down', 'w-4 h-4'); ?> 데스크탑 행 높이
                </div>
                <div style="display:flex;gap:0.5rem;align-items:center;">
                    <input type="number" id="rowHeightInput" value="<?php echo $widget_row_height; ?>" min="100" max="800" step="10" class="mg-form-input" style="width:80px;padding:0.375rem 0.5rem;">
                    <span style="color:var(--mg-text-muted);">px</span>
                    <button type="button" class="mg-btn mg-btn-sm mg-btn-primary" onclick="saveRowHeight()">저장</button>
                </div>
                <div style="font-size:0.7rem;color:var(--mg-text-muted);margin-top:0.5rem;">모바일은 비율 유지하며 자동 조절</div>
            </div>
            <div style="background:var(--mg-bg-tertiary);padding:0.75rem 1rem;border-radius:0.5rem;flex:1;min-width:300px;">
                <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:0.25rem;display:flex;align-items:center;gap:0.25rem;">
                    <?php echo mg_icon('photo', 'w-4 h-4'); ?> 컬럼별 권장 이미지 사이즈 (비율)
                </div>
                <div id="imageSizeGuide" style="font-size:0.8rem;color:var(--mg-text-secondary);display:flex;gap:1rem;flex-wrap:wrap;">
                    <?php
                    $col_widths = array(3 => 300, 4 => 400, 6 => 600, 12 => 1200);
                    foreach ($col_widths as $col => $w):
                        $ratio = round($w / $widget_row_height, 2);
                    ?>
                    <span><strong><?php echo $col; ?>칸:</strong> <?php echo $w; ?>x<?php echo $widget_row_height; ?> <small style="color:var(--mg-text-muted);">(<?php echo $ratio; ?>:1)</small></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <p class="text-sm text-mg-text-muted" style="margin-bottom:1rem;">위젯을 드래그하여 순서를 변경할 수 있습니다. 12칸을 초과하면 자동으로 다음 줄로 넘어갑니다. <strong>반응형:</strong> 모바일에서는 모든 위젯이 전체 너비로 표시되며, 비율을 유지합니다.</p>

        <div class="mg-widget-grid" id="widgetGrid">
            <?php if (empty($widgets)): ?>
            <div class="mg-empty-state" id="emptyState">
                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin-bottom:1rem;opacity:0.5;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <p>위젯이 없습니다. '위젯 추가' 버튼을 클릭하세요.</p>
            </div>
            <?php else: ?>
            <?php foreach ($widgets as $widget): ?>
            <div class="mg-widget-item"
                 style="grid-column: span <?php echo $widget['widget_cols']; ?>;"
                 data-widget-id="<?php echo $widget['widget_id']; ?>"
                 data-widget-cols="<?php echo $widget['widget_cols']; ?>"
                 draggable="true">
                <div class="mg-widget-header">
                    <span class="mg-widget-type">
                        <?php
                        $type_icons = array('text' => 'document-text', 'image' => 'photo', 'link_button' => 'link', 'latest' => 'queue-list', 'notice' => 'bell', 'slider' => 'squares-2x2', 'editor' => 'pencil-square');
                        $icon_name = isset($type_icons[$widget['widget_type']]) ? $type_icons[$widget['widget_type']] : 'cube';
                        echo mg_icon($icon_name, 'w-4 h-4');
                        echo ' ';
                        echo isset($widget_types[$widget['widget_type']]) ? $widget_types[$widget['widget_type']]['name'] : $widget['widget_type'];
                        ?>
                    </span>
                    <span class="mg-widget-cols"><?php echo $widget['widget_cols']; ?>칸</span>
                </div>
                <div class="mg-widget-preview">
                    <?php
                    $preview = '';
                    $cfg = $widget['widget_config'] ?: array();
                    switch ($widget['widget_type']) {
                        case 'text':
                            $preview = strip_tags($cfg['content'] ?? '') ?: '(내용 없음)';
                            break;
                        case 'image':
                            if (!empty($cfg['image_url'])) {
                                echo '<img src="' . htmlspecialchars($cfg['image_url']) . '" alt="미리보기">';
                                $preview = '';
                            } else {
                                $preview = '(이미지 없음)';
                            }
                            break;
                        case 'link_button':
                            if (!empty($cfg['content_type']) && $cfg['content_type'] == 'image' && !empty($cfg['image_url'])) {
                                echo '<img src="' . htmlspecialchars($cfg['image_url']) . '" alt="미리보기" style="max-height:40px;">';
                                echo '<br><small>' . htmlspecialchars($cfg['link_url'] ?? '#') . '</small>';
                                $preview = '';
                            } else {
                                $type_label = ($cfg['text'] ?? '버튼');
                                $preview = $type_label . ' → ' . ($cfg['link_url'] ?? '#');
                            }
                            break;
                        case 'latest':
                        case 'notice':
                            $preview = $cfg['title'] ?? (!empty($cfg['bo_table']) ? $cfg['bo_table'] : '(게시판 미선택)');
                            break;
                        case 'slider':
                            $slides = $cfg['slides'] ?? array();
                            if (is_string($slides)) $slides = json_decode($slides, true) ?: array();
                            if (!empty($slides) && !empty($slides[0]['image'])) {
                                echo '<img src="' . htmlspecialchars($slides[0]['image']) . '" alt="미리보기" style="max-height:40px;">';
                                echo '<br><small>' . count($slides) . '개 슬라이드</small>';
                                $preview = '';
                            } else {
                                $preview = count($slides) . '개 슬라이드';
                            }
                            break;
                        case 'editor':
                            $preview = $cfg['title'] ?? strip_tags($cfg['content'] ?? '') ?: '(내용 없음)';
                            break;
                        default:
                            $preview = '(설정 필요)';
                    }
                    echo htmlspecialchars(mb_substr($preview, 0, 50));
                    ?>
                </div>
                <div class="mg-widget-actions">
                    <button type="button" class="mg-widget-btn" onclick="editWidget(<?php echo $widget['widget_id']; ?>)">설정</button>
                    <button type="button" class="mg-widget-btn danger" onclick="deleteWidget(<?php echo $widget['widget_id']; ?>)">삭제</button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 위젯 추가 모달 -->
<div class="mgb-modal-overlay" id="addModal">
    <div class="mgb-modal">
        <div class="mgb-modal-header">
            <span class="mgb-modal-title">위젯 추가</span>
            <button type="button" class="mgb-modal-close" onclick="closeAddModal()">&times;</button>
        </div>
        <div class="mgb-modal-body">
            <div class="mg-form-group">
                <label class="mg-form-label">위젯 타입 선택</label>
                <div class="mg-type-grid">
                    <?php
                    $type_icons = array('text' => 'document-text', 'image' => 'photo', 'link_button' => 'link', 'latest' => 'queue-list', 'notice' => 'bell', 'slider' => 'squares-2x2', 'editor' => 'pencil-square');
                    foreach ($widget_types as $type => $info):
                        $icon_name = isset($type_icons[$type]) ? $type_icons[$type] : 'cube';
                    ?>
                    <div class="mg-type-item" data-type="<?php echo $type; ?>" onclick="selectType('<?php echo $type; ?>')">
                        <div class="mg-type-icon">
                            <?php echo mg_icon($icon_name, 'w-6 h-6'); ?>
                        </div>
                        <div class="mg-type-name"><?php echo $info['name']; ?></div>
                        <div class="mg-type-desc"><?php echo $info['desc']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mg-form-group" style="margin-top:1rem;">
                <label class="mg-form-label">컬럼 너비</label>
                <select id="addWidgetCols" class="mg-form-select">
                    <option value="12">12칸 (전체)</option>
                    <option value="6">6칸 (절반)</option>
                    <option value="4">4칸 (1/3)</option>
                    <option value="3">3칸 (1/4)</option>
                </select>
            </div>
        </div>
        <div class="mgb-modal-footer">
            <button type="button" class="mg-btn mg-btn-secondary" onclick="closeAddModal()">취소</button>
            <button type="button" class="mg-btn mg-btn-primary" onclick="addWidget()">추가</button>
        </div>
    </div>
</div>

<!-- 위젯 설정 모달 -->
<div class="mgb-modal-overlay" id="editModal">
    <div class="mgb-modal lg">
        <div class="mgb-modal-header">
            <span class="mgb-modal-title">위젯 설정</span>
            <button type="button" class="mgb-modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="widgetConfigForm" onsubmit="saveWidgetConfig(event)">
            <div class="mgb-modal-body" id="editModalBody">
                <!-- AJAX로 로드 -->
            </div>
            <div class="mgb-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeEditModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
var selectedType = null;
var currentWidgetId = null;

// 타입 선택
function selectType(type) {
    selectedType = type;
    document.querySelectorAll('.mg-type-item').forEach(function(el) {
        el.classList.toggle('selected', el.dataset.type === type);
    });

    // 허용된 컬럼 옵션 업데이트
    var types = <?php echo json_encode($widget_types); ?>;
    var cols = types[type]?.allowed_cols || [12];
    var select = document.getElementById('addWidgetCols');
    var colLabels = {12: '전체', 8: '2/3', 6: '절반', 4: '1/3', 3: '1/4', 2: '1/6'};
    select.innerHTML = '';
    [12, 8, 6, 4, 3, 2].forEach(function(c) {
        if (cols.includes(c)) {
            var opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c + '칸 (' + colLabels[c] + ')';
            select.appendChild(opt);
        }
    });
}

// 모달 열기/닫기
function openAddModal() {
    selectedType = null;
    document.querySelectorAll('.mg-type-item').forEach(el => el.classList.remove('selected'));
    document.getElementById('addModal').classList.add('show');
}

function closeAddModal() {
    document.getElementById('addModal').classList.remove('show');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    currentWidgetId = null;
}

// 위젯 추가
function addWidget() {
    if (!selectedType) {
        alert('위젯 타입을 선택하세요.');
        return;
    }

    var cols = document.getElementById('addWidgetCols').value;

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add_widget&widget_type=' + selectedType + '&widget_cols=' + cols
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    });
}

// 위젯 설정
function editWidget(widgetId) {
    currentWidgetId = widgetId;
    document.getElementById('editModalBody').innerHTML = '<p style="text-align:center;color:var(--mg-text-muted);">로딩 중...</p>';
    document.getElementById('editModal').classList.add('show');

    fetch('./main_widget_config.php?widget_id=' + widgetId)
    .then(r => r.text())
    .then(html => {
        var container = document.getElementById('editModalBody');
        container.innerHTML = html;

        // AJAX로 로드된 스크립트 실행
        var scripts = container.querySelectorAll('script');
        scripts.forEach(function(script) {
            var newScript = document.createElement('script');
            newScript.textContent = script.textContent;
            document.body.appendChild(newScript);
            document.body.removeChild(newScript);
        });
    });
}

function saveWidgetConfig(e) {
    e.preventDefault();

    var formData = new FormData(document.getElementById('widgetConfigForm'));
    formData.append('action', 'update_widget_config');
    formData.append('widget_id', currentWidgetId);

    fetch('./main_builder_update.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    });
}

// 위젯 삭제
function deleteWidget(widgetId) {
    if (!confirm('이 위젯을 삭제하시겠습니까?')) return;

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=delete_widget&widget_id=' + widgetId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    });
}

// 순서 저장
function saveOrder() {
    var widgets = [];
    document.querySelectorAll('.mg-widget-item').forEach(function(el, idx) {
        if (el.dataset.widgetId) {
            widgets.push({id: el.dataset.widgetId, order: idx});
        }
    });

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'save_order', widgets: widgets})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('순서가 저장되었습니다.');
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    });
}

// 드래그앤드롭
var draggedWidget = null;

document.querySelectorAll('.mg-widget-item').forEach(function(widget) {
    widget.addEventListener('dragstart', function(e) {
        draggedWidget = this;
        this.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    widget.addEventListener('dragend', function() {
        this.classList.remove('dragging');
        document.querySelectorAll('.mg-widget-item').forEach(w => w.classList.remove('drag-over'));
        draggedWidget = null;
    });

    widget.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (draggedWidget && draggedWidget !== this) {
            this.classList.add('drag-over');
        }
    });

    widget.addEventListener('dragleave', function() {
        this.classList.remove('drag-over');
    });

    widget.addEventListener('drop', function(e) {
        e.preventDefault();
        if (draggedWidget && draggedWidget !== this) {
            var grid = document.getElementById('widgetGrid');
            var items = Array.from(grid.querySelectorAll('.mg-widget-item'));
            var fromIdx = items.indexOf(draggedWidget);
            var toIdx = items.indexOf(this);

            if (fromIdx < toIdx) {
                this.parentNode.insertBefore(draggedWidget, this.nextSibling);
            } else {
                this.parentNode.insertBefore(draggedWidget, this);
            }
        }
        this.classList.remove('drag-over');
    });
});

// ESC로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

// ====================================
// 위젯 이미지 업로드 함수들 (전역)
// ====================================

// 이미지 위젯용 업로드
function uploadWidgetImage() {
    console.log('uploadWidgetImage called');
    var fileInput = document.getElementById('image_file_upload');
    var status = document.getElementById('image_upload_status');

    console.log('fileInput:', fileInput);
    console.log('files:', fileInput ? fileInput.files : null);

    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
        if (status) status.innerHTML = '<span style="color:var(--mg-error);">파일을 선택해주세요.</span>';
        return;
    }

    var formData = new FormData();
    formData.append('image', fileInput.files[0]);

    if (status) status.innerHTML = '<span style="color:var(--mg-accent);">업로드 중...</span>';

    fetch('./main_widget_upload.php', {
        method: 'POST',
        body: formData
    })
    .then(r => {
        console.log('Response status:', r.status);
        return r.text();
    })
    .then(text => {
        console.log('Response text:', text);
        try {
            var data = JSON.parse(text);
            if (data.success) {
                var urlInput = document.getElementById('widget_image_url');
                if (urlInput) urlInput.value = data.url;
                // 미리보기 업데이트
                var preview = document.getElementById('image_preview');
                if (preview) preview.innerHTML = '<img src="' + data.url + '" alt="미리보기" style="max-width:100%;max-height:200px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">';
                if (status) status.innerHTML = '<span style="color:var(--mg-success);">업로드 완료!</span>';
            } else {
                if (status) status.innerHTML = '<span style="color:var(--mg-error);">' + (data.message || '업로드 실패') + '</span>';
            }
        } catch(e) {
            console.error('JSON parse error:', e);
            if (status) status.innerHTML = '<span style="color:var(--mg-error);">서버 응답 오류</span>';
        }
    })
    .catch(err => {
        console.error('Fetch error:', err);
        if (status) status.innerHTML = '<span style="color:var(--mg-error);">업로드 오류: ' + err.message + '</span>';
    });
}

// 링크/버튼 위젯용 업로드
function uploadLinkImage() {
    var fileInput = document.getElementById('link_image_file_upload');
    var status = document.getElementById('link_image_upload_status');

    if (!fileInput || !fileInput.files || !fileInput.files[0]) {
        if (status) status.innerHTML = '<span style="color:var(--mg-error);">파일을 선택해주세요.</span>';
        return;
    }

    var formData = new FormData();
    formData.append('image', fileInput.files[0]);

    if (status) status.innerHTML = '<span style="color:var(--mg-accent);">업로드 중...</span>';

    fetch('./main_widget_upload.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(text => {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                var urlInput = document.getElementById('link_widget_image_url');
                if (urlInput) urlInput.value = data.url;
                // 미리보기 업데이트
                var preview = document.getElementById('link_image_preview');
                if (preview) preview.innerHTML = '<img src="' + data.url + '" alt="미리보기" style="max-width:100%;max-height:150px;border-radius:4px;border:1px solid var(--mg-bg-tertiary);">';
                if (status) status.innerHTML = '<span style="color:var(--mg-success);">업로드 완료!</span>';
            } else {
                if (status) status.innerHTML = '<span style="color:var(--mg-error);">' + (data.message || '업로드 실패') + '</span>';
            }
        } catch(e) {
            console.error('JSON parse error:', e);
            if (status) status.innerHTML = '<span style="color:var(--mg-error);">서버 응답 오류</span>';
        }
    })
    .catch(err => {
        console.error(err);
        if (status) status.innerHTML = '<span style="color:var(--mg-error);">업로드 오류</span>';
    });
}

// 슬라이더 위젯용 업로드
function uploadSlideImage(input, idx) {
    if (!input.files || !input.files[0]) return;

    var formData = new FormData();
    formData.append('image', input.files[0]);

    // 로딩 표시
    var slideItem = input.closest('.slider-slide-item');
    var statusEl = slideItem ? slideItem.querySelector('.slide-upload-status') : null;
    if (!statusEl && slideItem) {
        statusEl = document.createElement('div');
        statusEl.className = 'slide-upload-status';
        statusEl.style.cssText = 'font-size:0.8rem;margin-top:0.25rem;';
        input.parentNode.appendChild(statusEl);
    }
    if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-accent);">업로드 중...</span>';

    fetch('./main_widget_upload.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.text())
    .then(text => {
        try {
            var data = JSON.parse(text);
            if (data.success) {
                var urlInput = document.getElementById('slide_image_' + idx);
                if (urlInput) urlInput.value = data.url;
                if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-success);">업로드 완료!</span>';
            } else {
                if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-error);">' + (data.message || '업로드 실패') + '</span>';
            }
        } catch(e) {
            console.error('JSON parse error:', e);
            if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-error);">서버 응답 오류</span>';
        }
    })
    .catch(err => {
        console.error(err);
        if (statusEl) statusEl.innerHTML = '<span style="color:var(--mg-error);">업로드 오류</span>';
    });
}

// 링크/버튼 콘텐츠 타입 토글
function toggleLinkContentType() {
    var type = document.getElementById('link_content_type');
    if (!type) return;
    var textFields = document.getElementById('link_text_fields');
    var imageFields = document.getElementById('link_image_fields');
    if (textFields) textFields.style.display = type.value === 'text' ? '' : 'none';
    if (imageFields) imageFields.style.display = type.value === 'image' ? '' : 'none';
}

// 행 높이 저장
function saveRowHeight() {
    var input = document.getElementById('rowHeightInput');
    if (!input) return;

    var value = parseInt(input.value, 10);
    if (isNaN(value) || value < 100 || value > 800) {
        alert('행 높이는 100~800px 사이로 입력해주세요.');
        return;
    }

    fetch('./main_builder_update.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=save_row_height&row_height=' + value
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // 이미지 가이드 업데이트
            updateImageSizeGuide(value);
            alert('행 높이가 저장되었습니다.');
        } else {
            alert(data.message || '저장 실패');
        }
    })
    .catch(err => {
        console.error(err);
        alert('저장 중 오류가 발생했습니다.');
    });
}

// 이미지 사이즈 가이드 업데이트
function updateImageSizeGuide(height) {
    var guide = document.getElementById('imageSizeGuide');
    if (!guide) return;

    var colWidths = {3: 300, 4: 400, 6: 600, 12: 1200};
    var html = '';
    for (var col in colWidths) {
        var w = colWidths[col];
        var ratio = (w / height).toFixed(2);
        html += '<span><strong>' + col + '칸:</strong> ' + w + 'x' + height + ' <small style="color:var(--mg-text-muted);">(' + ratio + ':1)</small></span>';
    }
    guide.innerHTML = html;
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
