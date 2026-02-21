<?php
/**
 * Morgan Edition - 타임라인 관리 (시대 + 이벤트)
 */

$sub_menu = "800175";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// === 위키 문서 목록 (이벤트 연결용) ===
$lore_articles = array();
$art_result = sql_query("SELECT la_id, la_title FROM {$g5['mg_lore_article_table']} WHERE la_use = 1 ORDER BY la_title");
while ($row = sql_fetch_array($art_result)) {
    $lore_articles[] = $row;
}

// === 시대 목록 + 각 시대의 이벤트 ===
$eras = array();
$era_result = sql_query("SELECT * FROM {$g5['mg_lore_era_table']} ORDER BY le_order, le_id");
while ($row = sql_fetch_array($era_result)) {
    $row['events'] = array();
    $eras[$row['le_id']] = $row;
}

// 이벤트 로드
if (!empty($eras)) {
    $era_ids = implode(',', array_keys($eras));
    $ev_result = sql_query("SELECT * FROM {$g5['mg_lore_event_table']} WHERE le_id IN ({$era_ids}) ORDER BY lv_order, lv_id");
    while ($ev = sql_fetch_array($ev_result)) {
        if (isset($eras[$ev['le_id']])) {
            $eras[$ev['le_id']]['events'][] = $ev;
        }
    }
}

$g5['title'] = '타임라인 관리';
require_once __DIR__.'/_head.php';

$update_url = G5_ADMIN_URL . '/morgan/lore_timeline_update.php';
$upload_url = G5_ADMIN_URL . '/morgan/lore_image_upload.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <div>
        <span style="font-size:1.125rem;font-weight:600;">타임라인 관리</span>
        <span style="font-size:0.875rem;color:var(--mg-text-muted);margin-left:0.5rem;">시대(Era)와 시대별 이벤트를 관리합니다.</span>
    </div>
    <button type="button" class="mg-btn mg-btn-primary" onclick="openEraModal()">+ 시대 추가</button>
</div>

<!-- 페이지 설명 설정 -->
<div class="mg-card" style="margin-bottom:1.5rem;">
    <div class="mg-card-body" style="padding:0.75rem 1rem;display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
        <label style="font-size:0.8rem;color:var(--mg-text-muted);white-space:nowrap;">프론트 페이지 설명:</label>
        <input type="text" id="timeline-desc-input" value="<?php echo htmlspecialchars(mg_config('lore_timeline_desc', '이 세계의 역사를 시간순으로 살펴보세요')); ?>" style="flex:1;min-width:200px;background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);color:var(--mg-text-primary);padding:4px 8px;border-radius:6px;font-size:0.85rem;" maxlength="100">
        <button type="button" class="mg-btn mg-btn-sm mg-btn-primary" onclick="saveTimelineDesc()">저장</button>
        <span id="timeline-desc-msg" style="font-size:0.75rem;color:var(--mg-accent);display:none;">저장됨</span>
    </div>
</div>
<script>
function saveTimelineDesc() {
    var val = document.getElementById('timeline-desc-input').value;
    var fd = new FormData();
    fd.append('mode', 'update_desc');
    fd.append('lore_timeline_desc', val);
    fetch('<?php echo $update_url; ?>', { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var msg = document.getElementById('timeline-desc-msg');
                msg.style.display = 'inline';
                setTimeout(function() { msg.style.display = 'none'; }, 2000);
            }
        });
}
</script>

<?php if (empty($eras)) { ?>
<div class="mg-card">
    <div class="mg-card-body" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
        등록된 시대가 없습니다. [+ 시대 추가] 버튼을 눌러 첫 시대를 추가하세요.
    </div>
</div>
<?php } else { ?>
<div id="era-sortable">
<?php foreach ($eras as $era) { ?>
<div class="mg-card era-sortable-item" data-era-id="<?php echo $era['le_id']; ?>" style="margin-bottom:1rem;">
    <!-- 시대 헤더 -->
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
        <div style="display:flex;align-items:center;gap:0.5rem 1rem;flex-wrap:wrap;min-width:0;">
            <span class="era-drag-handle" title="드래그하여 순서 변경" style="cursor:grab;color:var(--mg-text-muted);font-size:1.2rem;padding:0 0.25rem;user-select:none;">&#9776;</span>
            <span id="era-arrow-<?php echo $era['le_id']; ?>" style="transition:transform 0.2s;display:inline-block;cursor:pointer;" onclick="toggleEra(<?php echo $era['le_id']; ?>)">&#9660;</span>
            <div>
                <strong style="font-size:1rem;"><?php echo htmlspecialchars($era['le_name']); ?></strong>
                <?php if ($era['le_period']) { ?>
                <span style="color:var(--mg-text-muted);font-size:0.85rem;margin-left:0.5rem;">(<?php echo htmlspecialchars($era['le_period']); ?>)</span>
                <?php } ?>
            </div>
            <span class="mg-badge" style="font-size:0.7rem;">순서: <?php echo $era['le_order']; ?></span>
            <?php if ($era['le_use']) { ?>
            <span class="mg-badge mg-badge-success" style="font-size:0.7rem;">사용</span>
            <?php } else { ?>
            <span class="mg-badge mg-badge-error" style="font-size:0.7rem;">미사용</span>
            <?php } ?>
            <span class="era-event-count" style="color:var(--mg-text-muted);font-size:0.8rem;">이벤트 <?php echo count($era['events']); ?>개</span>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;" onclick="event.stopPropagation();">
            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="openEraModal(<?php echo $era['le_id']; ?>)">수정</button>
            <?php if (empty($era['events'])) { ?>
            <form method="post" action="<?php echo $update_url; ?>" style="display:inline;">
                <input type="hidden" name="mode" value="era_delete">
                <input type="hidden" name="le_id" value="<?php echo $era['le_id']; ?>">
                <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('이 시대를 삭제하시겠습니까?');">삭제</button>
            </form>
            <?php } else { ?>
            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" disabled style="opacity:0.4;cursor:not-allowed;" title="이벤트가 있어 삭제 불가">삭제</button>
            <?php } ?>
            <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="openEventModal(<?php echo $era['le_id']; ?>)">+ 이벤트</button>
        </div>
    </div>

    <!-- 이벤트 목록 (펼침/접힘) -->
    <div id="era-body-<?php echo $era['le_id']; ?>" class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:36px;"></th>
                    <th style="width:50px;text-align:center;">ID</th>
                    <th style="width:100px;text-align:center;">연도</th>
                    <th>제목</th>
                    <th style="width:80px;text-align:center;">이미지</th>
                    <th style="width:60px;text-align:center;">주요</th>
                    <th style="width:60px;text-align:center;">순서</th>
                    <th style="width:60px;text-align:center;">사용</th>
                    <th style="width:140px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody class="event-sortable-tbody" data-era-id="<?php echo $era['le_id']; ?>">
                <?php if (empty($era['events'])) { ?>
                <tr class="era-no-events"><td colspan="9" style="text-align:center;padding:1.5rem;color:var(--mg-text-muted);font-size:0.875rem;">이 시대에 등록된 이벤트가 없습니다.</td></tr>
                <?php } else { foreach ($era['events'] as $ev) { ?>
                <tr data-event-id="<?php echo $ev['lv_id']; ?>">
                    <td style="text-align:center;"><span class="event-drag-handle" style="cursor:grab;color:var(--mg-text-muted);font-size:1.1rem;user-select:none;" title="드래그하여 순서 변경">&#9776;</span></td>
                    <td style="text-align:center;color:var(--mg-text-muted);font-size:0.8rem;"><?php echo $ev['lv_id']; ?></td>
                    <td style="text-align:center;">
                        <span style="color:var(--mg-accent);font-weight:600;font-size:0.85rem;"><?php echo htmlspecialchars($ev['lv_year']); ?></span>
                    </td>
                    <td>
                        <strong style="font-size:0.875rem;"><?php echo htmlspecialchars($ev['lv_title']); ?></strong>
                        <?php if ($ev['lv_content']) { ?>
                        <br><small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars(mb_substr($ev['lv_content'], 0, 50)); ?><?php echo mb_strlen($ev['lv_content']) > 50 ? '...' : ''; ?></small>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($ev['lv_image']) { ?>
                        <img src="<?php echo htmlspecialchars($ev['lv_image']); ?>" style="width:32px;height:32px;object-fit:cover;border-radius:4px;">
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($ev['lv_is_major']) { ?>
                        <span style="color:var(--mg-accent);">&starf;</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;"><?php echo $ev['lv_order']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($ev['lv_use']) { ?>
                        <span style="color:var(--mg-success);">&check;</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">&cross;</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="openEventModal(<?php echo $era['le_id']; ?>, <?php echo $ev['lv_id']; ?>)">수정</button>
                        <form method="post" action="<?php echo $update_url; ?>" style="display:inline;">
                            <input type="hidden" name="mode" value="event_delete">
                            <input type="hidden" name="lv_id" value="<?php echo $ev['lv_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('이 이벤트를 삭제하시겠습니까?');">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>
</div>
<?php } ?>

<!-- ==================== -->
<!-- 시대 추가/수정 모달 -->
<!-- ==================== -->
<div id="era-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:500px;">
        <div class="mg-modal-header">
            <h3 id="era-modal-title">시대 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeEraModal()">&times;</button>
        </div>
        <form method="post" action="<?php echo $update_url; ?>">
            <input type="hidden" name="mode" id="era-mode" value="era_add">
            <input type="hidden" name="le_id" id="era-le-id" value="0">
            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">시대명 *</label>
                    <input type="text" name="le_name" id="era-name" class="mg-form-input" placeholder="예: 태초의 시대" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">기간 표기</label>
                    <input type="text" name="le_period" id="era-period" class="mg-form-input" placeholder="예: 0년 ~ 300년">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">시대 설명</label>
                    <textarea name="le_desc" id="era-desc" class="mg-form-input" rows="2" placeholder="시대에 대한 간략한 설명 (선택)"></textarea>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="le_order" id="era-order" class="mg-form-input" value="0" min="0">
                    </div>
                    <div class="mg-form-group" style="display:flex;align-items:flex-end;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                            <input type="checkbox" name="le_use" id="era-use" value="1" checked>
                            사용
                        </label>
                    </div>
                </div>
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeEraModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<!-- ==================== -->
<!-- 이벤트 추가/수정 모달 -->
<!-- ==================== -->
<div id="event-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:600px;">
        <div class="mg-modal-header">
            <h3 id="event-modal-title">이벤트 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeEventModal()">&times;</button>
        </div>
        <form method="post" action="<?php echo $update_url; ?>" enctype="multipart/form-data">
            <input type="hidden" name="mode" id="event-mode" value="event_add">
            <input type="hidden" name="lv_id" id="event-lv-id" value="0">
            <input type="hidden" name="le_id" id="event-le-id" value="0">
            <input type="hidden" name="lv_image_url" id="event-image-url" value="">
            <div class="mg-modal-body">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">연도 표기</label>
                        <input type="text" name="lv_year" id="event-year" class="mg-form-input" placeholder="예: 128년, 5세기 초">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="lv_order" id="event-order" class="mg-form-input" value="0" min="0">
                    </div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">제목 *</label>
                    <input type="text" name="lv_title" id="event-title" class="mg-form-input" placeholder="이벤트 제목" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">내용</label>
                    <textarea name="lv_content" id="event-content" class="mg-form-input" rows="4" placeholder="이벤트 설명"></textarea>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">연결 문서</label>
                    <select name="la_id" id="event-la-id" class="mg-form-input">
                        <option value="0">없음</option>
                        <?php foreach ($lore_articles as $art) { ?>
                        <option value="<?php echo $art['la_id']; ?>"><?php echo htmlspecialchars($art['la_title']); ?></option>
                        <?php } ?>
                    </select>
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">이벤트와 관련된 위키 문서를 연결합니다.</div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">이미지 (선택)</label>
                    <div style="display:flex;gap:1rem;align-items:flex-start;">
                        <div id="event-image-preview" style="width:100px;height:100px;border:1px dashed var(--mg-bg-tertiary);border-radius:4px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                            <span style="color:var(--mg-text-muted);font-size:0.7rem;">미리보기</span>
                        </div>
                        <div style="flex:1;">
                            <input type="file" name="lv_image" id="event-image-file" accept="image/*" class="mg-form-input" onchange="previewEventImage(this)" style="margin-bottom:0.5rem;">
                            <div style="font-size:0.75rem;color:var(--mg-text-muted);">jpg, png, gif, webp (최대 2MB)</div>
                            <button type="button" id="event-image-remove" class="mg-btn mg-btn-danger mg-btn-sm" style="margin-top:0.25rem;display:none;" onclick="removeEventImage()">이미지 제거</button>
                        </div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                    <div class="mg-form-group" style="display:flex;align-items:flex-end;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                            <input type="checkbox" name="lv_is_major" id="event-is-major" value="1">
                            주요 이벤트
                        </label>
                    </div>
                    <div class="mg-form-group" style="display:flex;align-items:flex-end;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.875rem;color:var(--mg-text-secondary);">
                            <input type="checkbox" name="lv_use" id="event-use" value="1" checked>
                            사용
                        </label>
                    </div>
                </div>
            </div>
            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeEventModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
// 시대/이벤트 데이터 (편집용)
var _eras = <?php echo json_encode(array_values($eras)); ?>;

// === 시대 펼침/접힘 ===
function toggleEra(leId) {
    var body = document.getElementById('era-body-' + leId);
    var arrow = document.getElementById('era-arrow-' + leId);
    if (body.style.display === 'none') {
        body.style.display = '';
        arrow.style.transform = 'rotate(0deg)';
    } else {
        body.style.display = 'none';
        arrow.style.transform = 'rotate(-90deg)';
    }
}

// === 시대 모달 ===
function openEraModal(leId) {
    var modal = document.getElementById('era-modal');
    if (leId) {
        // 편집 모드
        var era = _eras.find(function(e) { return e.le_id == leId; });
        if (!era) return;
        document.getElementById('era-modal-title').textContent = '시대 수정';
        document.getElementById('era-mode').value = 'era_edit';
        document.getElementById('era-le-id').value = era.le_id;
        document.getElementById('era-name').value = era.le_name || '';
        document.getElementById('era-period').value = era.le_period || '';
        document.getElementById('era-desc').value = era.le_desc || '';
        document.getElementById('era-order').value = era.le_order || 0;
        document.getElementById('era-use').checked = era.le_use == 1;
    } else {
        // 추가 모드
        document.getElementById('era-modal-title').textContent = '시대 추가';
        document.getElementById('era-mode').value = 'era_add';
        document.getElementById('era-le-id').value = '0';
        document.getElementById('era-name').value = '';
        document.getElementById('era-period').value = '';
        document.getElementById('era-desc').value = '';
        document.getElementById('era-order').value = '0';
        document.getElementById('era-use').checked = true;
    }
    modal.style.display = 'flex';
}

function closeEraModal() {
    document.getElementById('era-modal').style.display = 'none';
}

document.getElementById('era-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEraModal();
});

// === 이벤트 모달 ===
function openEventModal(leId, lvId) {
    var modal = document.getElementById('event-modal');
    document.getElementById('event-le-id').value = leId;

    if (lvId) {
        // 편집 모드 - 시대에서 이벤트 찾기
        var era = _eras.find(function(e) { return e.le_id == leId; });
        var ev = null;
        if (era && era.events) {
            ev = era.events.find(function(v) { return v.lv_id == lvId; });
        }
        if (!ev) {
            alert('이벤트를 찾을 수 없습니다.');
            return;
        }

        document.getElementById('event-modal-title').textContent = '이벤트 수정';
        document.getElementById('event-mode').value = 'event_edit';
        document.getElementById('event-lv-id').value = ev.lv_id;
        document.getElementById('event-year').value = ev.lv_year || '';
        document.getElementById('event-title').value = ev.lv_title || '';
        document.getElementById('event-content').value = ev.lv_content || '';
        document.getElementById('event-order').value = ev.lv_order || 0;
        document.getElementById('event-la-id').value = ev.la_id || 0;
        document.getElementById('event-is-major').checked = ev.lv_is_major == 1;
        document.getElementById('event-use').checked = ev.lv_use == 1;
        document.getElementById('event-image-url').value = ev.lv_image || '';

        // 이미지 미리보기
        var preview = document.getElementById('event-image-preview');
        var removeBtn = document.getElementById('event-image-remove');
        if (ev.lv_image) {
            preview.innerHTML = '<img src="' + escHtml(ev.lv_image) + '" style="width:100%;height:100%;object-fit:cover;">';
            removeBtn.style.display = '';
        } else {
            preview.innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.7rem;">미리보기</span>';
            removeBtn.style.display = 'none';
        }

        // 파일 input 초기화
        document.getElementById('event-image-file').value = '';
    } else {
        // 추가 모드
        document.getElementById('event-modal-title').textContent = '이벤트 추가';
        document.getElementById('event-mode').value = 'event_add';
        document.getElementById('event-lv-id').value = '0';
        document.getElementById('event-year').value = '';
        document.getElementById('event-title').value = '';
        document.getElementById('event-content').value = '';
        document.getElementById('event-order').value = '0';
        document.getElementById('event-la-id').value = '0';
        document.getElementById('event-is-major').checked = false;
        document.getElementById('event-use').checked = true;
        document.getElementById('event-image-url').value = '';
        document.getElementById('event-image-preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.7rem;">미리보기</span>';
        document.getElementById('event-image-remove').style.display = 'none';
        document.getElementById('event-image-file').value = '';
    }

    modal.style.display = 'flex';
}

function closeEventModal() {
    document.getElementById('event-modal').style.display = 'none';
}

document.getElementById('event-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEventModal();
});

// === 이벤트 이미지 미리보기 ===
function previewEventImage(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('event-image-preview').innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;">';
        document.getElementById('event-image-remove').style.display = '';
    };
    reader.readAsDataURL(input.files[0]);
}

function removeEventImage() {
    document.getElementById('event-image-url').value = '';
    document.getElementById('event-image-file').value = '';
    document.getElementById('event-image-preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.7rem;">미리보기</span>';
    document.getElementById('event-image-remove').style.display = 'none';
}

function escHtml(str) {
    var d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}

// === 시대 드래그 정렬 ===
(function() {
    var container = document.getElementById('era-sortable');
    if (!container) return;

    var dragItem = null;
    var placeholder = document.createElement('div');
    placeholder.style.cssText = 'border:2px dashed var(--mg-accent);border-radius:8px;margin-bottom:1rem;min-height:48px;opacity:0.5;';

    container.querySelectorAll('.era-drag-handle').forEach(function(handle) {
        var card = handle.closest('.era-sortable-item');

        handle.addEventListener('mousedown', function() {
            card.draggable = true;
        });

        card.addEventListener('dragstart', function(e) {
            dragItem = card;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', '');
            setTimeout(function() { card.style.opacity = '0.4'; }, 0);
        });

        card.addEventListener('dragend', function() {
            card.draggable = false;
            card.style.opacity = '';
            dragItem = null;
            if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);
            saveEraOrder();
        });
    });

    container.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        var target = e.target.closest('.era-sortable-item');
        if (!target || target === dragItem) return;

        var rect = target.getBoundingClientRect();
        var mid = rect.top + rect.height / 2;
        if (e.clientY < mid) {
            container.insertBefore(placeholder, target);
        } else {
            container.insertBefore(placeholder, target.nextSibling);
        }
    });

    container.addEventListener('drop', function(e) {
        e.preventDefault();
        if (dragItem && placeholder.parentNode) {
            container.insertBefore(dragItem, placeholder);
            placeholder.parentNode.removeChild(placeholder);
        }
    });

    function saveEraOrder() {
        var items = container.querySelectorAll('.era-sortable-item');
        var order = [];
        items.forEach(function(item, i) {
            order.push(item.getAttribute('data-era-id'));
            var badge = item.querySelector('.mg-badge');
            if (badge && badge.textContent.indexOf('순서') !== -1) {
                badge.textContent = '순서: ' + i;
            }
        });

        var formData = new FormData();
        formData.append('mode', 'era_reorder');
        order.forEach(function(id) { formData.append('order[]', id); });

        fetch('<?php echo $update_url; ?>', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) alert('순서 저장 실패: ' + (data.message || ''));
            })
            .catch(function() { alert('순서 저장 중 오류가 발생했습니다.'); });
    }
})();

// === 이벤트 드래그 정렬 (시대 간 이동 지원) ===
(function() {
    var allTbodies = document.querySelectorAll('.event-sortable-tbody');
    if (!allTbodies.length) return;

    var dragRow = null;
    var sourceTbody = null;
    var placeholder = document.createElement('tr');
    placeholder.innerHTML = '<td colspan="9" style="border:2px dashed var(--mg-accent);height:40px;opacity:0.5;"></td>';

    allTbodies.forEach(function(tbody) {
        // 핸들 이벤트 등록
        tbody.querySelectorAll('.event-drag-handle').forEach(function(handle) {
            var row = handle.closest('tr');
            handle.addEventListener('mousedown', function() { row.draggable = true; });

            row.addEventListener('dragstart', function(e) {
                dragRow = row;
                sourceTbody = tbody;
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', '');
                setTimeout(function() { row.style.opacity = '0.4'; }, 0);
            });

            row.addEventListener('dragend', function() {
                row.draggable = false;
                row.style.opacity = '';
                if (placeholder.parentNode) placeholder.parentNode.removeChild(placeholder);

                var targetTbody = dragRow ? dragRow.closest('tbody') : null;
                var isCrossEra = sourceTbody && targetTbody && sourceTbody !== targetTbody;
                dragRow = null;

                if (targetTbody) {
                    saveEventOrder(targetTbody, isCrossEra);
                    updateEraUI();
                }
                sourceTbody = null;
            });
        });

        // 모든 tbody에서 dragover/drop 허용 (시대 간 이동)
        tbody.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            var target = e.target.closest('tr');
            if (target && target === dragRow) return;

            // 빈 시대이거나, 이벤트 행이 없으면 끝에 추가
            if (!target || target.classList.contains('era-no-events')) {
                if (placeholder.parentNode !== tbody) {
                    tbody.appendChild(placeholder);
                }
                return;
            }
            if (!target.hasAttribute('data-event-id')) return;

            var rect = target.getBoundingClientRect();
            var mid = rect.top + rect.height / 2;
            if (e.clientY < mid) {
                tbody.insertBefore(placeholder, target);
            } else {
                tbody.insertBefore(placeholder, target.nextSibling);
            }
        });

        tbody.addEventListener('drop', function(e) {
            e.preventDefault();
            if (dragRow && placeholder.parentNode) {
                // 빈 시대 메시지 제거
                var emptyRow = tbody.querySelector('.era-no-events');
                if (emptyRow) emptyRow.remove();

                tbody.insertBefore(dragRow, placeholder);
                placeholder.parentNode.removeChild(placeholder);
            }
        });
    });

    function saveEventOrder(tbody, isCrossEra) {
        var eraId = tbody.getAttribute('data-era-id');
        var rows = tbody.querySelectorAll('tr[data-event-id]');
        var formData = new FormData();
        formData.append('mode', 'event_reorder');
        formData.append('le_id', eraId);
        rows.forEach(function(row, i) {
            formData.append('order[]', row.getAttribute('data-event-id'));
            var cells = row.querySelectorAll('td');
            if (cells.length >= 7) cells[6].textContent = i;
        });

        fetch('<?php echo $update_url; ?>', { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.success) {
                    alert('순서 저장 실패: ' + (data.message || ''));
                } else if (isCrossEra) {
                    // 시대 간 이동 시 페이지 새로고침 (모달 데이터 동기화)
                    location.reload();
                }
            })
            .catch(function() { alert('순서 저장 중 오류가 발생했습니다.'); });
    }

    function updateEraUI() {
        allTbodies.forEach(function(tbody) {
            var eraItem = tbody.closest('.era-sortable-item');
            if (!eraItem) return;
            var eventRows = tbody.querySelectorAll('tr[data-event-id]');
            var count = eventRows.length;

            // 이벤트 수 갱신
            var countSpan = eraItem.querySelector('.era-event-count');
            if (countSpan) countSpan.textContent = '이벤트 ' + count + '개';

            // 빈 시대 메시지 토글
            var emptyRow = tbody.querySelector('.era-no-events');
            if (count === 0 && !emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.className = 'era-no-events';
                emptyRow.innerHTML = '<td colspan="9" style="text-align:center;padding:1.5rem;color:var(--mg-text-muted);font-size:0.875rem;">이 시대에 등록된 이벤트가 없습니다.</td>';
                tbody.appendChild(emptyRow);
            } else if (count > 0 && emptyRow) {
                emptyRow.remove();
            }
        });
    }
})();
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
