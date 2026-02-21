<?php
/**
 * Morgan Edition - 세계관 지도 관리 (2탭: 지도 설정 + 지역 관리)
 */

$sub_menu = '800178';
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

if ($is_admin != 'super') alert('최고관리자만 접근 가능합니다.');

// 탭 라우팅
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';
if (!in_array($tab, array('settings', 'regions'))) $tab = 'settings';

// 공통 데이터
$map_image = mg_config('expedition_map_image', '');
$map_desc = mg_config('lore_map_desc', '이 세계의 지도를 살펴보세요');

// 지역 데이터
$regions = mg_get_map_regions();

$g5['title'] = '지도 관리';
require_once __DIR__.'/_head.php';

$update_url = G5_ADMIN_URL . '/morgan/lore_map_update.php';
?>

<!-- 탭 바 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=settings" class="mg-tab <?php echo $tab == 'settings' ? 'active' : ''; ?>">지도 설정</a>
    <a href="?tab=regions" class="mg-tab <?php echo $tab == 'regions' ? 'active' : ''; ?>">지역 관리</a>
</div>

<?php if ($tab == 'settings') { ?>
<!-- ======================================== -->
<!-- 지도 설정 탭 -->
<!-- ======================================== -->
<form method="post" action="<?php echo $update_url; ?>" enctype="multipart/form-data">
<input type="hidden" name="mode" value="settings">

<div class="mg-card">
    <div class="mg-card-header"><h3>지도 설정</h3></div>
    <div class="mg-card-body">

        <!-- 지도 이미지 -->
        <div class="mg-form-group" style="max-width:600px;">
            <label class="mg-form-label">지도 이미지</label>
            <?php if ($map_image) { ?>
            <div id="map-current" style="margin-bottom:1rem;">
                <img src="<?php echo htmlspecialchars($map_image); ?>" alt="현재 지도" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
                <div style="margin-top:0.5rem;">
                    <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteMapImage()">이미지 삭제</button>
                </div>
            </div>
            <?php } ?>
            <input type="file" name="map_image_file" id="map_image_file" accept="image/*" class="mg-form-input" onchange="previewNewMap(this)">
            <div id="map-preview" style="display:none;margin-top:0.75rem;">
                <img id="map-preview-img" src="" style="max-width:100%;max-height:300px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
                <div style="margin-top:4px;font-size:0.8rem;color:var(--mg-accent);">새 이미지 선택됨</div>
            </div>
            <input type="hidden" name="map_image_action" id="map_image_action" value="">
            <p style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.5rem;">JPG, PNG, GIF, WebP / 최대 10MB / 권장: 1920x1080px 이상</p>
        </div>

        <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

        <!-- 페이지 설명 -->
        <div class="mg-form-group" style="max-width:500px;">
            <label class="mg-form-label" for="lore_map_desc">페이지 설명</label>
            <input type="text" name="lore_map_desc" id="lore_map_desc" value="<?php echo htmlspecialchars($map_desc); ?>" class="mg-form-input" maxlength="100">
            <small style="color:var(--mg-text-muted);font-size:0.75rem;">프론트 세계관 맵 페이지 상단에 표시되는 소개 문구</small>
        </div>

    </div>
</div>

<div style="margin-top:1.5rem;text-align:center;">
    <button type="submit" class="mg-btn mg-btn-primary">설정 저장</button>
</div>

</form>

<script>
function previewNewMap(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('map-preview-img').src = e.target.result;
            document.getElementById('map-preview').style.display = 'block';
            document.getElementById('map_image_action').value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function deleteMapImage() {
    document.getElementById('map_image_action').value = '__DELETE__';
    var cur = document.getElementById('map-current');
    if (cur) cur.innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.85rem;">이미지가 삭제됩니다 (저장 시 적용)</span>';
}
</script>

<?php } elseif ($tab == 'regions') { ?>
<!-- ======================================== -->
<!-- 지역 관리 탭 -->
<!-- ======================================== -->

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <span style="font-size:0.875rem;color:var(--mg-text-muted);">총 <?php echo count($regions); ?>개 지역</span>
    <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="openRegionModal()">+ 지역 추가</button>
</div>

<!-- 지역 테이블 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:50px;">순서</th>
                    <th style="width:60px;">이미지</th>
                    <th>지역명</th>
                    <th>설명</th>
                    <th style="width:80px;">마커</th>
                    <th style="width:100px;">좌표</th>
                    <th style="width:60px;">사용</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($regions)) { ?>
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">등록된 지역이 없습니다.</td></tr>
                <?php } ?>
                <?php foreach ($regions as $i => $r) { ?>
                <tr>
                    <td style="text-align:center;"><?php echo $i + 1; ?></td>
                    <td>
                        <?php if (!empty($r['mr_image'])) { ?>
                        <img src="<?php echo htmlspecialchars($r['mr_image']); ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                        <?php } else { ?>
                        <div style="width:40px;height:40px;background:var(--mg-bg-tertiary);border-radius:4px;display:flex;align-items:center;justify-content:center;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--mg-text-muted)" stroke-width="1.5"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                        <?php } ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($r['mr_name']); ?></strong></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:0.85rem;color:var(--mg-text-muted);"><?php echo htmlspecialchars(mb_substr($r['mr_desc'] ?? '', 0, 50, 'UTF-8')); ?><?php echo mb_strlen($r['mr_desc'] ?? '', 'UTF-8') > 50 ? '...' : ''; ?></td>
                    <td style="text-align:center;">
                        <?php if ($r['mr_map_x'] !== null && $r['mr_map_y'] !== null) {
                            $ms = $r['mr_marker_style'] ?? 'pin';
                            $style_labels = array('pin' => '드롭핀', 'circle' => '원형', 'diamond' => '다이아몬드', 'flag' => '깃발');
                        ?>
                        <span style="font-size:0.75rem;color:var(--mg-text-secondary);"><?php echo $style_labels[$ms] ?? $ms; ?></span>
                        <?php } else { ?>
                        <span style="font-size:0.75rem;opacity:0.4;">-</span>
                        <?php } ?>
                    </td>
                    <td style="font-size:0.8rem;color:var(--mg-text-muted);">
                        <?php if ($r['mr_map_x'] !== null && $r['mr_map_y'] !== null) { ?>
                        <span style="color:var(--mg-accent);"><?php echo round($r['mr_map_x'], 1); ?>%, <?php echo round($r['mr_map_y'], 1); ?>%</span>
                        <?php } else { ?>
                        <span style="opacity:0.5;">미배치</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($r['mr_use']) { ?>
                        <span style="color:var(--mg-accent);">ON</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">OFF</span>
                        <?php } ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="mg-btn mg-btn-sm" onclick="editRegion(<?php echo $r['mr_id']; ?>)" title="수정">수정</button>
                            <button type="button" class="mg-btn mg-btn-sm mg-btn-danger" onclick="deleteRegion(<?php echo $r['mr_id']; ?>, '<?php echo htmlspecialchars(addslashes($r['mr_name'])); ?>')" title="삭제">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($map_image) { ?>
<!-- 맵 에디터 -->
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-header">
        <h3>마커 배치</h3>
        <span style="font-size:0.8rem;color:var(--mg-text-muted);">지도를 클릭하여 마커를 배치하고, 마커를 클릭하여 지역을 연결하세요.</span>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <div id="map-editor" style="position:relative;cursor:crosshair;">
            <img src="<?php echo htmlspecialchars($map_image); ?>" id="editor-map-img" style="display:block;width:100%;" alt="지도" draggable="false">
            <div id="editor-markers"></div>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-body" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">
        마커를 배치하려면 <a href="?tab=settings" style="color:var(--mg-accent);">지도 설정</a>에서 먼저 지도 이미지를 등록하세요.
    </div>
</div>
<?php } ?>

<!-- 지역 추가/수정 모달 -->
<div id="region-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;overflow-y:auto;padding:0 1rem;">
    <div style="max-width:500px;margin:5vh auto;background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);border-radius:12px;padding:1.5rem;">
        <h3 id="region-modal-title" style="margin-bottom:1rem;font-size:1.1rem;">지역 추가</h3>
        <form method="post" action="<?php echo $update_url; ?>" enctype="multipart/form-data" id="region-form">
            <input type="hidden" name="mode" id="region-mode" value="region_add">
            <input type="hidden" name="mr_id" id="region-mr-id" value="0">

            <div class="mg-form-group">
                <label class="mg-form-label" for="region-name">지역명 *</label>
                <input type="text" name="mr_name" id="region-name" class="mg-form-input" required maxlength="100">
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label" for="region-desc">설명</label>
                <textarea name="mr_desc" id="region-desc" class="mg-form-input" rows="4" style="resize:vertical;"></textarea>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">지역 이미지</label>
                <input type="file" name="mr_image_file" id="region-image-file" accept="image/*" class="mg-form-input">
                <small style="color:var(--mg-text-muted);font-size:0.75rem;">JPG, PNG, GIF, WebP / 최대 2MB</small>
                <div id="region-image-preview" style="margin-top:0.5rem;"></div>
                <input type="hidden" name="mr_image_action" id="region-image-action" value="">
            </div>

            <div class="mg-form-group">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                    <input type="checkbox" name="mr_use" id="region-use" value="1" checked>
                    <span class="mg-form-label" style="margin:0;">사용</span>
                </label>
            </div>

            <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="mg-btn" onclick="closeRegionModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<!-- 마커-지역 연결 모달 -->
<div id="link-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;overflow-y:auto;padding:0 1rem;">
    <div style="max-width:400px;margin:10vh auto;background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);border-radius:12px;padding:1.5rem;">
        <h3 style="margin-bottom:1rem;font-size:1.1rem;">지역 연결</h3>

        <!-- 마커 스타일 선택 -->
        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.8rem;color:var(--mg-text-muted);margin-bottom:0.5rem;">마커 스타일</label>
            <div id="link-style-picker" style="display:flex;gap:0.5rem;flex-wrap:wrap;"></div>
        </div>

        <p style="font-size:0.85rem;color:var(--mg-text-muted);margin-bottom:0.75rem;">연결할 지역을 선택하세요.</p>
        <div id="link-region-list" style="max-height:260px;overflow-y:auto;"></div>
        <div style="display:flex;gap:0.5rem;justify-content:flex-end;margin-top:1rem;">
            <button type="button" class="mg-btn mg-btn-sm" style="color:var(--mg-error);" id="link-remove-btn" onclick="removeLinkCoords()">마커 제거</button>
            <button type="button" class="mg-btn" onclick="closeLinkModal()">닫기</button>
            <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" id="link-save-btn" onclick="saveLinkStyle()">저장</button>
        </div>
    </div>
</div>

<!-- 삭제 폼 -->
<form id="delete-form" method="post" action="<?php echo $update_url; ?>" style="display:none;">
    <input type="hidden" name="mode" value="region_delete">
    <input type="hidden" name="mr_id" id="delete-mr-id" value="0">
</form>

<script>
// === 데이터 ===
var regionsData = <?php echo json_encode(array_map(function($r) {
    return array(
        'mr_id' => (int)$r['mr_id'],
        'mr_name' => $r['mr_name'],
        'mr_desc' => $r['mr_desc'] ?? '',
        'mr_image' => $r['mr_image'] ?? '',
        'mr_map_x' => $r['mr_map_x'] !== null ? (float)$r['mr_map_x'] : null,
        'mr_map_y' => $r['mr_map_y'] !== null ? (float)$r['mr_map_y'] : null,
        'mr_marker_style' => $r['mr_marker_style'] ?? 'pin',
        'mr_use' => (int)$r['mr_use']
    );
}, $regions)); ?>;

var UPDATE_URL = '<?php echo $update_url; ?>';
var pendingCoords = null; // 클릭으로 배치 대기 중인 좌표

// === 지역 CRUD 모달 ===
function openRegionModal(data) {
    document.getElementById('region-modal').style.display = 'block';
    if (data) {
        document.getElementById('region-modal-title').textContent = '지역 수정';
        document.getElementById('region-mode').value = 'region_edit';
        document.getElementById('region-mr-id').value = data.mr_id;
        document.getElementById('region-name').value = data.mr_name;
        document.getElementById('region-desc').value = data.mr_desc;
        document.getElementById('region-use').checked = !!data.mr_use;
        document.getElementById('region-image-action').value = '';
        var preview = document.getElementById('region-image-preview');
        if (data.mr_image) {
            preview.innerHTML = '<div style="display:flex;align-items:center;gap:0.5rem;"><img src="'+escHtml(data.mr_image)+'" style="width:60px;height:60px;object-fit:cover;border-radius:4px;"><button type="button" class="mg-btn mg-btn-sm mg-btn-danger" onclick="removeRegionImage()">삭제</button></div>';
        } else {
            preview.innerHTML = '';
        }
    } else {
        document.getElementById('region-modal-title').textContent = '지역 추가';
        document.getElementById('region-mode').value = 'region_add';
        document.getElementById('region-mr-id').value = '0';
        document.getElementById('region-name').value = '';
        document.getElementById('region-desc').value = '';
        document.getElementById('region-use').checked = true;
        document.getElementById('region-image-action').value = '';
        document.getElementById('region-image-preview').innerHTML = '';
    }
    document.getElementById('region-image-file').value = '';
}
function closeRegionModal() { document.getElementById('region-modal').style.display = 'none'; }
function removeRegionImage() {
    document.getElementById('region-image-action').value = '__DELETE__';
    document.getElementById('region-image-preview').innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.8rem;">이미지가 삭제됩니다 (저장 시 적용)</span>';
}
function editRegion(mr_id) {
    var data = regionsData.find(function(r) { return r.mr_id === mr_id; });
    if (data) openRegionModal(data);
}
function deleteRegion(mr_id, name) {
    if (!confirm('"' + name + '" 지역을 삭제하시겠습니까?')) return;
    document.getElementById('delete-mr-id').value = mr_id;
    document.getElementById('delete-form').submit();
}

// === 마커-지역 연결 모달 ===
var linkTargetMrId = null; // 현재 연결 모달의 대상 mr_id (기존 마커 클릭 시)
var selectedStyle = 'pin'; // 현재 선택된 마커 스타일
var STYLE_OPTIONS = [
    { key: 'pin', label: '드롭핀' },
    { key: 'circle', label: '원형' },
    { key: 'diamond', label: '다이아몬드' },
    { key: 'flag', label: '깃발' }
];

function renderStylePicker(currentStyle) {
    selectedStyle = currentStyle || 'pin';
    var picker = document.getElementById('link-style-picker');
    picker.innerHTML = '';

    STYLE_OPTIONS.forEach(function(opt) {
        var btn = document.createElement('div');
        var isActive = opt.key === selectedStyle;
        btn.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:2px;cursor:pointer;padding:6px 10px;border-radius:8px;border:2px solid '+(isActive ? 'var(--mg-accent)' : 'var(--mg-bg-tertiary)')+';background:var(--mg-bg-primary);min-width:60px;transition:border-color 0.15s;';
        btn.innerHTML = getMarkerSVG(opt.key, 'var(--mg-accent)', 'var(--mg-bg-primary)') +
            '<span style="font-size:0.65rem;color:var(--mg-text-secondary);">'+opt.label+'</span>';
        btn.onclick = function() {
            selectedStyle = opt.key;
            renderStylePicker(opt.key);
        };
        picker.appendChild(btn);
    });
}

function openLinkModal(mr_id_or_null) {
    // mr_id_or_null: 기존 마커 클릭 시 해당 mr_id, 신규 배치 시 null
    linkTargetMrId = mr_id_or_null;

    // 스타일 선택기 렌더링 (기존 마커면 해당 스타일, 신규면 pin)
    var currentStyle = 'pin';
    if (mr_id_or_null) {
        var target = regionsData.find(function(r) { return r.mr_id === mr_id_or_null; });
        if (target) currentStyle = target.mr_marker_style || 'pin';
    }
    renderStylePicker(currentStyle);

    var listEl = document.getElementById('link-region-list');
    listEl.innerHTML = '';

    // 이미 배치된 mr_id 목록
    var placedIds = {};
    regionsData.forEach(function(r) {
        if (r.mr_map_x !== null && r.mr_map_y !== null) placedIds[r.mr_id] = true;
    });

    // 지역 목록 렌더링
    var hasItems = false;
    regionsData.forEach(function(r) {
        // 이미 다른 곳에 배치된 지역은 제외 (단, 자기 자신은 표시)
        if (placedIds[r.mr_id] && r.mr_id !== mr_id_or_null) return;

        hasItems = true;
        var item = document.createElement('div');
        var isLinked = r.mr_id === mr_id_or_null;
        item.style.cssText = 'display:flex;align-items:center;gap:0.75rem;padding:0.5rem 0.75rem;border-radius:8px;cursor:pointer;border:1px solid '+(isLinked ? 'var(--mg-accent)' : 'var(--mg-bg-tertiary)')+';margin-bottom:0.5rem;background:'+(isLinked ? 'rgba(245,159,10,0.1)' : 'var(--mg-bg-primary)')+';';
        item.onmouseover = function() { if (!isLinked) this.style.borderColor = 'var(--mg-accent)'; };
        item.onmouseout = function() { if (!isLinked) this.style.borderColor = 'var(--mg-bg-tertiary)'; };

        var thumb = r.mr_image
            ? '<img src="'+escHtml(r.mr_image)+'" style="width:32px;height:32px;object-fit:cover;border-radius:4px;flex-shrink:0;">'
            : '<div style="width:32px;height:32px;background:var(--mg-bg-tertiary);border-radius:4px;flex-shrink:0;"></div>';

        item.innerHTML = thumb +
            '<div style="flex:1;min-width:0;"><div style="font-size:0.9rem;font-weight:500;color:var(--mg-text-primary);">'+escHtml(r.mr_name)+'</div>' +
            (r.mr_desc ? '<div style="font-size:0.75rem;color:var(--mg-text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'+escHtml(r.mr_desc.substring(0, 40))+'</div>' : '') +
            '</div>' +
            (isLinked ? '<span style="font-size:0.75rem;color:var(--mg-accent);">연결됨</span>' : '');

        item.onclick = function() { linkRegion(r.mr_id); };
        listEl.appendChild(item);
    });

    if (!hasItems) {
        listEl.innerHTML = '<div style="text-align:center;padding:1rem;color:var(--mg-text-muted);font-size:0.85rem;">연결 가능한 지역이 없습니다.<br>먼저 지역을 추가하세요.</div>';
    }

    // 마커 제거/저장 버튼은 기존 마커 클릭 시에만
    document.getElementById('link-remove-btn').style.display = mr_id_or_null ? '' : 'none';
    document.getElementById('link-save-btn').style.display = mr_id_or_null ? '' : 'none';
    document.getElementById('link-modal').style.display = 'block';
}

function closeLinkModal() {
    document.getElementById('link-modal').style.display = 'none';
    pendingCoords = null;
}

function linkRegion(mr_id) {
    if (!pendingCoords) {
        closeLinkModal();
        return;
    }

    // 기존 마커를 다른 지역으로 교체하는 경우: 이전 것의 좌표 제거
    if (linkTargetMrId && linkTargetMrId !== mr_id) {
        var oldRegion = regionsData.find(function(r) { return r.mr_id === linkTargetMrId; });
        if (oldRegion) {
            var fd0 = new FormData();
            fd0.append('mode', 'region_remove_coords');
            fd0.append('mr_id', linkTargetMrId);
            fetch(UPDATE_URL, { method: 'POST', body: fd0 });
            oldRegion.mr_map_x = null;
            oldRegion.mr_map_y = null;
            oldRegion.mr_marker_style = 'pin';
        }
    }

    // 새 좌표 + 스타일 저장
    saveCoords(mr_id, pendingCoords.x, pendingCoords.y, selectedStyle, function() {
        closeLinkModal();
    });
}

function saveLinkStyle() {
    if (!linkTargetMrId) { closeLinkModal(); return; }
    var region = regionsData.find(function(r) { return r.mr_id === linkTargetMrId; });
    if (!region || region.mr_map_x === null) { closeLinkModal(); return; }
    saveCoords(linkTargetMrId, region.mr_map_x, region.mr_map_y, selectedStyle, function() {
        closeLinkModal();
    });
}

function removeLinkCoords() {
    if (!linkTargetMrId) { closeLinkModal(); return; }
    var fd = new FormData();
    fd.append('mode', 'region_remove_coords');
    fd.append('mr_id', linkTargetMrId);

    fetch(UPDATE_URL, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var region = regionsData.find(function(r) { return r.mr_id === linkTargetMrId; });
                if (region) { region.mr_map_x = null; region.mr_map_y = null; }
                renderEditorMarkers();
            }
            closeLinkModal();
        });
}

// === 맵 에디터 ===
var editorMarkers = document.getElementById('editor-markers');
var mapEditor = document.getElementById('map-editor');

if (mapEditor) {
    renderEditorMarkers();

    // 맵 클릭 → 마커 배치 → 연결 모달
    mapEditor.addEventListener('click', function(e) {
        if (e.target.closest('.editor-marker')) return;

        var img = document.getElementById('editor-map-img');
        var rect = img.getBoundingClientRect();
        var x = ((e.clientX - rect.left) / rect.width) * 100;
        var y = ((e.clientY - rect.top) / rect.height) * 100;

        // 범위 제한
        x = Math.max(0, Math.min(100, x));
        y = Math.max(0, Math.min(100, y));

        pendingCoords = { x: x, y: y };
        openLinkModal(null);
    });
}

function renderEditorMarkers() {
    if (!editorMarkers) return;
    editorMarkers.innerHTML = '';

    regionsData.forEach(function(r) {
        if (r.mr_map_x === null || r.mr_map_y === null) return;

        var marker = document.createElement('div');
        marker.className = 'editor-marker';
        marker.style.cssText = 'position:absolute;left:'+r.mr_map_x+'%;top:'+r.mr_map_y+'%;width:44px;height:44px;margin-left:-22px;margin-top:-40px;cursor:grab;z-index:5;user-select:none;display:flex;align-items:center;justify-content:center;';
        marker.dataset.mrId = r.mr_id;
        marker.title = r.mr_name;
        marker.innerHTML = getMarkerSVG(r.mr_marker_style || 'pin', 'var(--mg-accent)', 'var(--mg-bg-primary)');

        // 라벨
        var label = document.createElement('div');
        label.style.cssText = 'position:absolute;top:100%;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:0.7rem;color:var(--mg-text-primary);background:var(--mg-bg-secondary);padding:1px 4px;border-radius:3px;border:1px solid var(--mg-bg-tertiary);margin-top:2px;pointer-events:none;';
        label.textContent = r.mr_name;
        marker.appendChild(label);

        // 드래그 + 클릭 구분
        setupMarkerInteraction(marker, r);

        editorMarkers.appendChild(marker);
    });
}

function setupMarkerInteraction(marker, region) {
    var isDragging = false;
    var hasMoved = false;
    var startX, startY, origLeft, origTop;

    function startDrag(cx, cy, e) {
        e.preventDefault();
        e.stopPropagation();
        isDragging = true;
        hasMoved = false;
        marker.style.zIndex = '20';
        startX = cx;
        startY = cy;
        origLeft = parseFloat(marker.style.left);
        origTop = parseFloat(marker.style.top);
    }

    function moveDrag(cx, cy) {
        if (!isDragging) return;
        var img = document.getElementById('editor-map-img');
        var rect = img.getBoundingClientRect();
        var dx = (cx - startX) / rect.width * 100;
        var dy = (cy - startY) / rect.height * 100;

        if (Math.abs(cx - startX) > 3 || Math.abs(cy - startY) > 3) {
            hasMoved = true;
        }

        marker.style.left = Math.max(0, Math.min(100, origLeft + dx)) + '%';
        marker.style.top = Math.max(0, Math.min(100, origTop + dy)) + '%';
    }

    function endDrag() {
        isDragging = false;
        marker.style.cursor = 'grab';
        marker.style.zIndex = '5';

        if (hasMoved) {
            var newX = parseFloat(marker.style.left);
            var newY = parseFloat(marker.style.top);
            saveCoords(region.mr_id, newX, newY, region.mr_marker_style || 'pin');
        } else {
            pendingCoords = { x: region.mr_map_x, y: region.mr_map_y };
            openLinkModal(region.mr_id);
        }
    }

    // Mouse events
    marker.addEventListener('mousedown', function(e) {
        if (e.button !== 0) return;
        startDrag(e.clientX, e.clientY, e);
        marker.style.cursor = 'grabbing';

        function onMove(ev) { moveDrag(ev.clientX, ev.clientY); }
        function onUp() {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            endDrag();
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    // Touch events
    marker.addEventListener('touchstart', function(e) {
        var t = e.touches[0];
        startDrag(t.clientX, t.clientY, e);
    }, { passive: false });

    marker.addEventListener('touchmove', function(e) {
        if (!isDragging) return;
        e.preventDefault();
        var t = e.touches[0];
        moveDrag(t.clientX, t.clientY);
    }, { passive: false });

    marker.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        endDrag();
    });
}

function saveCoords(mr_id, x, y, style, callback) {
    var fd = new FormData();
    fd.append('mode', 'region_set_coords');
    fd.append('mr_id', mr_id);
    fd.append('mr_map_x', x.toFixed(2));
    fd.append('mr_map_y', y.toFixed(2));
    fd.append('mr_marker_style', style || 'pin');

    fetch(UPDATE_URL, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var region = regionsData.find(function(r) { return r.mr_id === mr_id; });
                if (region) { region.mr_map_x = x; region.mr_map_y = y; region.mr_marker_style = style || 'pin'; }
                renderEditorMarkers();
            }
            if (callback) callback();
        });
}

function getMarkerSVG(style, color, inner) {
    switch (style) {
        case 'circle':
            return '<svg viewBox="0 0 28 28" width="30" height="30"><circle cx="14" cy="14" r="12" fill="'+color+'" stroke="'+inner+'" stroke-width="2.5"/><circle cx="14" cy="14" r="4" fill="'+inner+'"/></svg>';
        case 'diamond':
            return '<svg viewBox="0 0 24 32" width="24" height="32"><path d="M12 1 L23 16 L12 31 L1 16 Z" fill="'+color+'" stroke="'+inner+'" stroke-width="1.5"/><circle cx="12" cy="16" r="3.5" fill="'+inner+'"/></svg>';
        case 'flag':
            return '<svg viewBox="0 0 24 36" width="22" height="32"><rect x="10" y="6" width="2.5" height="26" rx="1" fill="'+color+'"/><path d="M12.5 6 L23 11 L12.5 16 Z" fill="'+color+'"/><circle cx="11.25" cy="4.5" r="2.5" fill="'+color+'"/></svg>';
        default:
            return '<svg viewBox="0 0 24 36" width="22" height="32"><path d="M12 0C5.4 0 0 5.4 0 12c0 9 12 24 12 24s12-15 12-24C24 5.4 18.6 0 12 0z" fill="'+color+'"/><circle cx="12" cy="12" r="5" fill="'+inner+'"/></svg>';
    }
}

function escHtml(str) {
    if (!str) return '';
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>

<?php } ?>

<?php
require_once __DIR__.'/_tail.php';
?>
