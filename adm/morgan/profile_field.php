<?php
/**
 * Morgan Edition - 프로필 필드 관리
 * 섹션별로 필드를 구조화하여 관리
 */

$sub_menu = "800300";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 섹션(카테고리) 목록 조회 — 최소 pf_order 기준 정렬
$categories = array();
$cat_result = sql_query("SELECT pf_category, MIN(pf_order) as min_order
    FROM {$g5['mg_profile_field_table']}
    GROUP BY pf_category
    ORDER BY min_order, pf_category");
while ($cat = sql_fetch_array($cat_result)) {
    if ($cat['pf_category']) {
        $categories[] = $cat['pf_category'];
    }
}
if (empty($categories)) {
    $categories = array('기본정보');
}

// 필드 목록 조회 (섹션별로 그룹화)
$sql = "SELECT * FROM {$g5['mg_profile_field_table']} ORDER BY pf_order, pf_id";
$result = sql_query($sql);

$fields_by_category = array();
// 카테고리 순서 초기화
foreach ($categories as $cat) {
    $fields_by_category[$cat] = array();
}
while ($row = sql_fetch_array($result)) {
    $cat = $row['pf_category'] ?: '기본정보';
    if (!isset($fields_by_category[$cat])) {
        $fields_by_category[$cat] = array();
    }
    // JSON 옵션을 콤마 구분 텍스트로 변환
    if ($row['pf_options']) {
        $opts = json_decode($row['pf_options'], true);
        if (is_array($opts)) {
            $row['pf_options_text'] = implode(', ', $opts);
        } else {
            $row['pf_options_text'] = $row['pf_options'];
        }
    } else {
        $row['pf_options_text'] = '';
    }
    $fields_by_category[$cat][] = $row;
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

$g5['title'] = '프로필 필드 관리';
require_once __DIR__.'/_head.php';
?>

<style>
.pf-section {
    margin-bottom: 1.5rem;
}
.pf-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    background: var(--mg-bg-tertiary);
    border-radius: 0.5rem 0.5rem 0 0;
    cursor: pointer;
}
.pf-section-header:hover {
    background: var(--mg-bg-primary);
}
.pf-section-title {
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.pf-section-count {
    font-size: 0.75rem;
    color: var(--mg-text-muted);
    background: var(--mg-bg-secondary);
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
}
.pf-section-body {
    border: 1px solid var(--mg-bg-tertiary);
    border-top: none;
    border-radius: 0 0 0.5rem 0.5rem;
    overflow-x: auto;
}
.pf-field-row {
    display: grid;
    grid-template-columns: 32px 40px 140px 110px 1fr 60px 60px 70px;
    min-width: 700px;
    gap: 0.5rem;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}
.pf-field-row:last-child {
    border-bottom: none;
}
.pf-field-row.header {
    background: var(--mg-bg-secondary);
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--mg-text-muted);
}
.pf-field-row input[type="text"],
.pf-field-row select {
    width: 100%;
}
/* 드래그 핸들 */
.drag-handle {
    cursor: grab;
    color: var(--mg-text-muted);
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    min-height: 44px;
    user-select: none;
    -webkit-user-select: none;
}
.drag-handle:active { cursor: grabbing; }
.section-drag-handle {
    cursor: grab;
    color: var(--mg-text-muted);
    display: flex;
    align-items: center;
    min-width: 32px;
    min-height: 44px;
    user-select: none;
    -webkit-user-select: none;
}
.section-drag-handle:active { cursor: grabbing; }
</style>

<div class="mg-alert mg-alert-info" style="display:flex;align-items:center;gap:1rem;">
    <div style="flex:1;">
        캐릭터 프로필에 표시될 필드를 관리합니다.<br>
        <small style="color:var(--mg-text-muted);">캐릭터 이름과 이미지는 기본 필드로 별도 관리됩니다.</small>
    </div>
    <button type="button" class="mg-btn mg-btn-primary" onclick="openAddSectionModal()">새 섹션 추가</button>
</div>

<form name="ffieldlist" id="ffieldlist" method="post" action="./profile_field_update.php">

<div id="pf-mobile-notice" style="display:none;padding:1rem;margin-bottom:1rem;background:var(--mg-bg-tertiary);border-radius:0.5rem;border-left:3px solid var(--mg-accent);">
    <strong style="font-size:0.85rem;color:var(--mg-text-primary);">PC 환경 권장</strong>
    <p style="font-size:0.8rem;color:var(--mg-text-muted);margin-top:0.25rem;">섹션/필드 드래그 정렬은 PC에서 최적화되어 있습니다. 모바일에서는 가로 스크롤 후 드래그하세요.</p>
</div>
<script>if(window.innerWidth<768)document.getElementById('pf-mobile-notice').style.display='block';</script>

<div id="section-sortable">
<?php foreach ($fields_by_category as $category => $fields): ?>
<div class="pf-section" data-category="<?php echo htmlspecialchars($category); ?>">
    <div class="pf-section-header">
        <div class="pf-section-title">
            <span class="section-drag-handle" onclick="event.stopPropagation();" title="드래그하여 섹션 순서 변경">
                <svg style="width:16px;height:16px;" viewBox="0 0 20 20" fill="currentColor"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
            </span>
            <span style="cursor:pointer;" onclick="toggleSection(this.closest('.pf-section-header'))">
                <svg class="section-arrow" style="width:16px;height:16px;transition:transform 0.2s;" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </span>
            <span style="cursor:pointer;" onclick="toggleSection(this.closest('.pf-section-header'))"><?php echo htmlspecialchars($category); ?></span>
            <span class="pf-section-count"><?php echo count($fields); ?>개</span>
        </div>
        <div style="display:flex;gap:0.5rem;">
            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="openAddFieldModal('<?php echo htmlspecialchars($category); ?>')">필드 추가</button>
            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="openRenameSectionModal('<?php echo htmlspecialchars($category); ?>')">이름 변경</button>
        </div>
    </div>
    <div class="pf-section-body">
        <div class="pf-field-row header">
            <div></div>
            <div>선택</div>
            <div>필드명</div>
            <div>타입</div>
            <div>선택 옵션</div>
            <div style="text-align:center;">필수</div>
            <div style="text-align:center;">사용</div>
            <div></div>
        </div>
        <?php foreach ($fields as $field): ?>
        <div class="pf-field-row" data-field-id="<?php echo $field['pf_id']; ?>">
            <div>
                <span class="drag-handle" title="드래그하여 순서 변경">
                    <svg style="width:14px;height:14px;" viewBox="0 0 20 20" fill="currentColor"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
                </span>
            </div>
            <div>
                <input type="hidden" name="pf_id[]" value="<?php echo $field['pf_id']; ?>">
                <input type="checkbox" name="chk[]" value="<?php echo $field['pf_id']; ?>">
            </div>
            <div>
                <input type="text" name="pf_name[]" value="<?php echo htmlspecialchars($field['pf_name']); ?>" class="mg-form-input" placeholder="필드명" style="padding:0.35rem 0.5rem;">
            </div>
            <div>
                <select name="pf_type[]" class="mg-form-select" style="padding:0.35rem 0.25rem;font-size:0.8rem;">
                    <?php foreach ($field_types as $type => $label): ?>
                    <option value="<?php echo $type; ?>" <?php echo $field['pf_type'] == $type ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="text" name="pf_options[]" value="<?php echo htmlspecialchars($field['pf_options_text']); ?>" class="mg-form-input" placeholder="옵션1, 옵션2, 옵션3" style="padding:0.35rem 0.5rem;font-size:0.85rem;">
            </div>
            <div style="text-align:center;">
                <input type="checkbox" name="pf_required[<?php echo $field['pf_id']; ?>]" value="1" <?php echo $field['pf_required'] ? 'checked' : ''; ?>>
            </div>
            <div style="text-align:center;">
                <input type="checkbox" name="pf_use[<?php echo $field['pf_id']; ?>]" value="1" <?php echo $field['pf_use'] ? 'checked' : ''; ?>>
            </div>
            <div>
                <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="openEditFieldModal(<?php echo $field['pf_id']; ?>)">상세</button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($fields)): ?>
        <div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">
            이 섹션에 필드가 없습니다.
            <br><br>
            <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="openAddFieldModal('<?php echo htmlspecialchars($category); ?>')">필드 추가</button>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div><!-- /section-sortable -->

<?php if (empty($fields_by_category)): ?>
<div class="mg-card">
    <div class="mg-card-body" style="padding:3rem;text-align:center;color:var(--mg-text-muted);">
        등록된 프로필 필드가 없습니다.
        <br><br>
        <button type="button" class="mg-btn mg-btn-primary" onclick="openAddSectionModal()">첫 섹션 만들기</button>
    </div>
</div>
<?php endif; ?>

<div style="margin-top:1rem;display:flex;gap:0.5rem;">
    <button type="submit" name="btn_save" class="mg-btn mg-btn-primary">변경사항 저장</button>
    <button type="submit" name="btn_delete" class="mg-btn mg-btn-danger" onclick="return confirm('선택한 필드를 삭제하시겠습니까?');">선택 삭제</button>
</div>

</form>

<!-- 새 섹션 추가 모달 -->
<div class="mg-modal" id="addSectionModal" style="display:none;">
    <div class="mg-modal-content" style="max-width:400px;">
        <div class="mg-modal-header">
            <span class="mg-modal-title">새 섹션 추가</span>
            <button type="button" class="mg-modal-close" onclick="closeModal('addSectionModal')">&times;</button>
        </div>
        <form method="post" action="./profile_field_update.php">
            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">섹션명</label>
                    <input type="text" name="new_category" class="mg-form-input" placeholder="예: 기본정보, 외형, 성격" required>
                    <small style="color:var(--mg-text-muted);">캐릭터 프로필에서 필드들을 묶어 보여줄 섹션명입니다.</small>
                </div>
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal('addSectionModal')">취소</button>
                <button type="submit" name="btn_add_section" class="mg-btn mg-btn-primary">추가</button>
            </div>
        </form>
    </div>
</div>

<!-- 섹션 이름 변경 모달 -->
<div class="mg-modal" id="renameSectionModal" style="display:none;">
    <div class="mg-modal-content" style="max-width:400px;">
        <div class="mg-modal-header">
            <span class="mg-modal-title">섹션 이름 변경</span>
            <button type="button" class="mg-modal-close" onclick="closeModal('renameSectionModal')">&times;</button>
        </div>
        <form method="post" action="./profile_field_update.php">
            <input type="hidden" name="old_category" id="rename_old_category">
            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">새 섹션명</label>
                    <input type="text" name="new_category_name" id="rename_new_category" class="mg-form-input" required>
                </div>
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal('renameSectionModal')">취소</button>
                <button type="submit" name="btn_rename_section" class="mg-btn mg-btn-primary">변경</button>
            </div>
        </form>
    </div>
</div>

<!-- 필드 추가 모달 -->
<div class="mg-modal" id="addFieldModal" style="display:none;">
    <div class="mg-modal-content" style="max-width:480px;">
        <div class="mg-modal-header">
            <span class="mg-modal-title">필드 추가</span>
            <button type="button" class="mg-modal-close" onclick="closeModal('addFieldModal')">&times;</button>
        </div>
        <form method="post" action="./profile_field_update.php">
            <input type="hidden" name="field_category" id="add_field_category">
            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">필드명 <span style="color:var(--mg-error);">*</span></label>
                    <input type="text" name="new_pf_name" class="mg-form-input" placeholder="예: 나이, 키, 성격" required>
                    <small style="color:var(--mg-text-muted);">캐릭터 프로필에 표시될 항목명입니다.</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">타입</label>
                    <select name="new_pf_type" class="mg-form-select" onchange="toggleOptionsField(this)">
                        <?php foreach ($field_types as $type => $label): ?>
                        <option value="<?php echo $type; ?>"><?php echo $label; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--mg-text-muted);">
                        한줄 텍스트: 짧은 답변 / 여러줄 텍스트: 긴 설명<br>
                        선택: 미리 정한 옵션 중 선택
                    </small>
                </div>
                <div class="mg-form-group" id="optionsGroup" style="display:none;">
                    <label class="mg-form-label">선택 옵션</label>
                    <input type="text" name="new_pf_options" class="mg-form-input" placeholder="옵션1, 옵션2, 옵션3">
                    <small style="color:var(--mg-text-muted);">쉼표(,)로 구분하여 입력하세요.</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">힌트 텍스트</label>
                    <input type="text" name="new_pf_placeholder" class="mg-form-input" placeholder="예: 25세, 불명">
                    <small style="color:var(--mg-text-muted);">입력란에 미리 보여줄 안내 문구입니다.</small>
                </div>
                <div style="display:flex;gap:1.5rem;margin-top:1rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="new_pf_required" value="1"> 필수 입력
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="new_pf_use" value="1" checked> 사용
                    </label>
                </div>
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal('addFieldModal')">취소</button>
                <button type="submit" name="btn_add_field" class="mg-btn mg-btn-primary">추가</button>
            </div>
        </form>
    </div>
</div>

<!-- 필드 상세 편집 모달 -->
<div class="mg-modal" id="editFieldModal" style="display:none;">
    <div class="mg-modal-content" style="max-width:480px;">
        <div class="mg-modal-header">
            <span class="mg-modal-title">필드 상세 편집</span>
            <button type="button" class="mg-modal-close" onclick="closeModal('editFieldModal')">&times;</button>
        </div>
        <form method="post" action="./profile_field_update.php">
            <input type="hidden" name="edit_pf_id" id="edit_pf_id">
            <div class="mg-modal-body" id="editFieldBody">
                <!-- AJAX로 로드 -->
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal('editFieldModal')">취소</button>
                <button type="submit" name="btn_edit_field" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
var updateUrl = './profile_field_update.php';

function toggleSection(header) {
    var body = header.nextElementSibling;
    var arrow = header.querySelector('.section-arrow');
    if (body.style.display === 'none') {
        body.style.display = '';
        arrow.style.transform = '';
    } else {
        body.style.display = 'none';
        arrow.style.transform = 'rotate(-90deg)';
    }
}

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openAddSectionModal() {
    openModal('addSectionModal');
}

function openRenameSectionModal(category) {
    document.getElementById('rename_old_category').value = category;
    document.getElementById('rename_new_category').value = category;
    openModal('renameSectionModal');
}

function openAddFieldModal(category) {
    document.getElementById('add_field_category').value = category;
    openModal('addFieldModal');
}

function toggleOptionsField(select) {
    var group = document.getElementById('optionsGroup');
    if (select.value === 'select' || select.value === 'multiselect') {
        group.style.display = '';
    } else {
        group.style.display = 'none';
    }
}

function openEditFieldModal(pfId) {
    document.getElementById('edit_pf_id').value = pfId;
    document.getElementById('editFieldBody').innerHTML = '<div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">로딩중...</div>';
    openModal('editFieldModal');

    // AJAX로 필드 정보 로드
    fetch('./profile_field_ajax.php?action=get&pf_id=' + pfId)
        .then(r => r.text())
        .then(html => {
            document.getElementById('editFieldBody').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('editFieldBody').innerHTML = '<div style="padding:2rem;text-align:center;color:var(--mg-error);">로드 실패</div>';
        });
}

// 모달 외부 클릭 시 닫기
document.querySelectorAll('.mg-modal').forEach(function(modal) {
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// ===========================================================
// 섹션 드래그 정렬 (마우스 + 터치)
// ===========================================================
(function() {
    var ct = document.getElementById('section-sortable');
    if (!ct) return;
    var drag = null, ph = document.createElement('div');
    ph.className = 'pf-section';
    ph.style.cssText = 'border:2px dashed var(--mg-accent);border-radius:8px;min-height:48px;opacity:0.5;margin-bottom:1.5rem;';

    ct.querySelectorAll('.section-drag-handle').forEach(function(h) {
        var item = h.closest('.pf-section');
        h.addEventListener('mousedown', function() { item.draggable = true; });
        item.addEventListener('dragstart', function(e) {
            drag = item; e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', 'section');
            setTimeout(function() { item.style.opacity = '0.4'; }, 0);
        });
        item.addEventListener('dragend', function() {
            item.draggable = false; item.style.opacity = '';
            if (ph.parentNode && drag) { ct.insertBefore(drag, ph); ph.remove(); }
            if (drag) saveSectionOrder(); drag = null;
        });
    });
    ct.addEventListener('dragover', function(e) {
        if (!drag) return; e.preventDefault(); e.dataTransfer.dropEffect = 'move';
        var t = e.target.closest('.pf-section');
        if (!t || t === drag || t === ph) return;
        var r = t.getBoundingClientRect();
        if (e.clientY < r.top + r.height / 2) ct.insertBefore(ph, t);
        else ct.insertBefore(ph, t.nextSibling);
    });
    ct.addEventListener('drop', function(e) { e.preventDefault(); });

    // 터치
    var ti = null, tc = null, tm = false;
    ct.querySelectorAll('.section-drag-handle').forEach(function(h) {
        h.addEventListener('touchstart', function() {
            var item = h.closest('.pf-section'); if (!item) return;
            ti = item; tm = false;
            tc = item.cloneNode(true);
            tc.style.cssText = 'position:fixed;left:0;right:0;z-index:9999;opacity:0.85;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,0.4);';
            var r = item.getBoundingClientRect(); tc.style.top = r.top+'px'; tc.style.width = r.width+'px';
            document.body.appendChild(tc); item.style.opacity = '0.3';
        }, {passive:true});
    });
    ct.addEventListener('touchmove', function(e) {
        if (!ti) return; e.preventDefault(); tm = true;
        var touch = e.touches[0]; if (tc) tc.style.top = touch.clientY - 24 + 'px';
        var el = document.elementFromPoint(touch.clientX, touch.clientY); if (!el) return;
        var t = el.closest('.pf-section');
        if (!t || t === ti || t === ph || !ct.contains(t)) return;
        var r = t.getBoundingClientRect();
        if (touch.clientY < r.top + r.height / 2) ct.insertBefore(ph, t);
        else ct.insertBefore(ph, t.nextSibling);
    }, {passive:false});
    ct.addEventListener('touchend', function() {
        if (!ti) return;
        if (tm && ph.parentNode) { ct.insertBefore(ti, ph); ph.remove(); }
        ti.style.opacity = ''; if (tc) { tc.remove(); tc = null; }
        if (tm) saveSectionOrder(); ti = null; tm = false;
    });
    ct.addEventListener('touchcancel', function() {
        if (ti) ti.style.opacity = ''; if (tc) { tc.remove(); tc = null; }
        if (ph.parentNode) ph.remove(); ti = null; tm = false;
    });

    function saveSectionOrder() {
        var fd = new FormData(); fd.append('mode', 'section_reorder');
        ct.querySelectorAll('.pf-section').forEach(function(s) {
            fd.append('sections[]', s.getAttribute('data-category'));
        });
        fetch(updateUrl, {method:'POST', body:fd})
            .then(function(r){return r.json();})
            .then(function(d){if(!d.success) alert('섹션 순서 저장 실패');})
            .catch(function(){alert('섹션 순서 저장 중 오류');});
    }
})();

// ===========================================================
// 필드 드래그 정렬 — 카테고리 간 이동 지원 (마우스 + 터치)
// ===========================================================
(function() {
    var drag = null;
    var ph = document.createElement('div');
    ph.className = 'pf-field-row';
    ph.style.cssText = 'border:2px dashed var(--mg-accent);min-height:44px;opacity:0.5;display:grid;padding:0;';
    var allBodies = document.querySelectorAll('.pf-section-body');

    // 마우스 드래그
    document.querySelectorAll('.pf-section-body .drag-handle').forEach(function(h) {
        var item = h.closest('.pf-field-row');
        h.addEventListener('mousedown', function(e) { item.draggable = true; e.stopPropagation(); });
        item.addEventListener('dragstart', function(e) {
            drag = item;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', 'field');
            e.stopPropagation();
            setTimeout(function() { item.style.opacity = '0.4'; }, 0);
        });
        item.addEventListener('dragend', function() {
            item.draggable = false; item.style.opacity = '';
            if (ph.parentNode && drag) { ph.parentNode.insertBefore(drag, ph); ph.remove(); }
            if (drag) saveFieldOrder(); drag = null;
        });
    });

    allBodies.forEach(function(body) {
        body.addEventListener('dragover', function(e) {
            if (!drag) return;
            e.preventDefault(); e.dataTransfer.dropEffect = 'move';
            e.stopPropagation(); // 섹션 dragover 차단

            var t = e.target.closest('.pf-field-row:not(.header)');
            if (t && t !== drag && t !== ph) {
                var r = t.getBoundingClientRect();
                if (e.clientY < r.top + r.height / 2) body.insertBefore(ph, t);
                else body.insertBefore(ph, t.nextSibling);
            } else if (!t) {
                // 빈 섹션이나 헤더 아래 영역 — 필드가 없으면 끝에 추가
                var rows = body.querySelectorAll('.pf-field-row:not(.header)');
                if (rows.length === 0 || (rows.length === 1 && rows[0] === drag)) {
                    var header = body.querySelector('.pf-field-row.header');
                    if (header && ph.parentNode !== body) {
                        body.insertBefore(ph, header.nextSibling);
                    }
                }
            }
        });
        body.addEventListener('drop', function(e) { e.preventDefault(); });
    });

    // 터치 드래그
    var ti = null, tc = null, tm = false;
    document.querySelectorAll('.pf-section-body .drag-handle').forEach(function(h) {
        h.addEventListener('touchstart', function(e) {
            var item = h.closest('.pf-field-row'); if (!item) return;
            ti = item; tm = false;
            tc = item.cloneNode(true);
            tc.style.cssText = 'position:fixed;left:0;right:0;z-index:9999;opacity:0.85;pointer-events:none;box-shadow:0 4px 16px rgba(0,0,0,0.4);';
            var r = item.getBoundingClientRect();
            tc.style.top = r.top+'px'; tc.style.width = r.width+'px';
            document.body.appendChild(tc); item.style.opacity = '0.3';
        }, {passive:true});
    });

    document.addEventListener('touchmove', function(e) {
        if (!ti) return; e.preventDefault(); tm = true;
        var touch = e.touches[0];
        if (tc) tc.style.top = touch.clientY - 24 + 'px';
        var el = document.elementFromPoint(touch.clientX, touch.clientY); if (!el) return;
        var t = el.closest('.pf-field-row:not(.header)');
        var body = el.closest('.pf-section-body');
        if (t && t !== ti && t !== ph && body) {
            var r = t.getBoundingClientRect();
            if (touch.clientY < r.top + r.height / 2) body.insertBefore(ph, t);
            else body.insertBefore(ph, t.nextSibling);
        } else if (body && !t) {
            // 빈 섹션 영역
            var rows = body.querySelectorAll('.pf-field-row:not(.header)');
            if (rows.length === 0 || (rows.length === 1 && rows[0] === ti)) {
                var header = body.querySelector('.pf-field-row.header');
                if (header) body.insertBefore(ph, header.nextSibling);
            }
        }
    }, {passive:false});

    document.addEventListener('touchend', function() {
        if (!ti) return;
        if (tm && ph.parentNode) { ph.parentNode.insertBefore(ti, ph); ph.remove(); }
        ti.style.opacity = ''; if (tc) { tc.remove(); tc = null; }
        if (tm) saveFieldOrder(); ti = null; tm = false;
    });
    document.addEventListener('touchcancel', function() {
        if (ti) ti.style.opacity = ''; if (tc) { tc.remove(); tc = null; }
        if (ph.parentNode) ph.remove(); ti = null; tm = false;
    });

    function saveFieldOrder() {
        var fd = new FormData();
        fd.append('mode', 'field_reorder_cross');
        document.querySelectorAll('.pf-section').forEach(function(section) {
            var cat = section.getAttribute('data-category');
            section.querySelectorAll('.pf-field-row:not(.header)').forEach(function(row) {
                var fid = row.getAttribute('data-field-id');
                if (fid) {
                    fd.append('fields[]', fid);
                    fd.append('field_categories[]', cat);
                }
            });
        });
        fetch(updateUrl, {method:'POST', body:fd})
            .then(function(r){return r.json();})
            .then(function(d){
                if (d.success) updateCounts();
                else alert('필드 순서 저장 실패');
            })
            .catch(function(){alert('필드 순서 저장 중 오류');});
    }

    function updateCounts() {
        document.querySelectorAll('.pf-section').forEach(function(s) {
            var cnt = s.querySelectorAll('.pf-field-row:not(.header)').length;
            var badge = s.querySelector('.pf-section-count');
            if (badge) badge.textContent = cnt + '개';
        });
    }
})();
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
