<?php
/**
 * Morgan Edition - 시설 관리
 */

$sub_menu = "801000";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 재료 종류 목록
$material_types = mg_get_material_types();

// 시설 목록
$facilities = mg_get_facilities();

// 게시판 목록 (해금 대상 선택용)
$boards = array();
$sql = "SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_order, gr_id, bo_table";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $boards[] = $row;
}

$g5['title'] = '시설 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 안내 -->
<div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
    시설은 유저들이 노동력과 재료를 기여하여 건설합니다. <strong>노동력과 재료가 모두 충족되면 자동으로 완공</strong>됩니다.<br>
    <span style="font-size:0.85rem;color:var(--mg-text-muted);">강제완공 버튼은 테스트나 긴급 상황에서만 사용하세요.</span>
</div>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 시설</div>
        <div class="mg-stat-value"><?php echo count($facilities); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">건설 중</div>
        <div class="mg-stat-value"><?php
            echo count(array_filter($facilities, function($f) { return $f['fc_status'] === 'building'; }));
        ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">완공</div>
        <div class="mg-stat-value"><?php
            echo count(array_filter($facilities, function($f) { return $f['fc_status'] === 'complete'; }));
        ?></div>
    </div>
</div>

<!-- 시설 추가 버튼 -->
<div style="margin-bottom:1rem;text-align:right;">
    <button type="button" class="mg-btn mg-btn-primary" onclick="openFacilityModal()">시설 추가</button>
</div>

<!-- 시설 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:1000px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:55px;">순서</th>
                    <th style="width:70px;">아이콘</th>
                    <th style="width:140px;">시설명</th>
                    <th style="width:75px;">상태</th>
                    <th style="width:120px;">노동력</th>
                    <th style="width:180px;">재료</th>
                    <th style="width:110px;">해금</th>
                    <th style="width:210px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($facilities as $fc) {
                    $status_badge = '';
                    switch ($fc['fc_status']) {
                        case 'complete':
                            $status_badge = '<span class="mg-badge mg-badge-success">완공</span>';
                            break;
                        case 'building':
                            $status_badge = '<span class="mg-badge mg-badge-primary">건설중</span>';
                            break;
                        default:
                            $status_badge = '<span class="mg-badge">잠김</span>';
                    }
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $fc['fc_order']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($fc['fc_icon']) {
                            echo mg_icon($fc['fc_icon'], 'w-6 h-6');
                        } else {
                            echo '<svg class="w-6 h-6" style="display:inline-block;color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>';
                        } ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($fc['fc_name']); ?></strong>
                        <?php if ($fc['fc_desc']) { ?>
                        <br><span style="font-size:0.8rem;color:var(--mg-text-muted);"><?php echo mb_substr($fc['fc_desc'], 0, 50); ?><?php echo mb_strlen($fc['fc_desc']) > 50 ? '...' : ''; ?></span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;"><?php echo $status_badge; ?></td>
                    <td>
                        <?php echo number_format($fc['fc_stamina_current']); ?> / <?php echo number_format($fc['fc_stamina_cost']); ?>
                        <div class="mg-progress" style="margin-top:4px;">
                            <div class="mg-progress-bar" style="width:<?php echo $fc['progress']['stamina']; ?>%"></div>
                        </div>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php foreach ($fc['materials'] as $mat) {
                            $p = $mat['fmc_required'] > 0 ? round(($mat['fmc_current'] / $mat['fmc_required']) * 100) : 100;
                        ?>
                        <span style="margin-right:8px;display:inline-flex;align-items:center;gap:2px;" title="<?php echo htmlspecialchars($mat['mt_name']); ?>">
                            <?php echo mg_icon($mat['mt_icon'], 'w-4 h-4'); ?> <?php echo $mat['fmc_current']; ?>/<?php echo $mat['fmc_required']; ?>
                        </span>
                        <?php } ?>
                        <?php if (empty($fc['materials'])) { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="font-size:0.8rem;">
                        <?php if ($fc['fc_unlock_type']) {
                            $unlock_labels = array(
                                'board' => '게시판',
                                'shop' => '상점',
                                'gift' => '선물함',
                                'achievement' => '업적',
                                'history' => '연혁',
                                'fountain' => '분수대'
                            );
                            $label = isset($unlock_labels[$fc['fc_unlock_type']]) ? $unlock_labels[$fc['fc_unlock_type']] : $fc['fc_unlock_type'];
                        ?>
                        <span class="mg-badge"><?php echo $label; ?></span>
                        <?php if ($fc['fc_unlock_target']) { ?>
                        <br><span class="mg-badge" style="margin-top:4px;"><?php echo htmlspecialchars($fc['fc_unlock_target']); ?></span>
                        <?php } ?>
                        <?php } else { ?>
                        -
                        <?php } ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <div style="display:flex;gap:4px;flex-wrap:nowrap;">
                            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" style="white-space:nowrap;" onclick="editFacility(<?php echo $fc['fc_id']; ?>)">수정</button>
                            <?php if ($fc['fc_status'] === 'locked') { ?>
                            <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" style="white-space:nowrap;" onclick="startBuilding(<?php echo $fc['fc_id']; ?>)">시작</button>
                            <?php } elseif ($fc['fc_status'] === 'building') { ?>
                            <button type="button" class="mg-btn mg-btn-success mg-btn-sm" style="white-space:nowrap;" onclick="forceComplete(<?php echo $fc['fc_id']; ?>)" title="강제 완공 (테스트용)">강제완공</button>
                            <?php } ?>
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" style="white-space:nowrap;" onclick="deleteFacility(<?php echo $fc['fc_id']; ?>)">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($facilities)) { ?>
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 시설이 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 시설 모달 -->
<div id="facility-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:700px;">
        <div class="mg-modal-header">
            <h3 id="modal-title">시설 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="facility-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php" enctype="multipart/form-data">
            <input type="hidden" name="w" id="form_w" value="">
            <input type="hidden" name="fc_id" id="form_fc_id" value="">

            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">시설명 *</label>
                    <input type="text" name="fc_name" id="fc_name" class="mg-form-input" required>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">아이콘</label>
                        <div id="current-icon-preview" style="display:none;margin-bottom:8px;">
                            <img id="icon-preview-img" src="" style="width:32px;height:32px;object-fit:contain;background:var(--mg-bg-tertiary);border-radius:4px;padding:4px;">
                            <label style="margin-left:8px;color:var(--mg-error);font-size:0.75rem;cursor:pointer;">
                                <input type="checkbox" name="del_icon" value="1" onchange="toggleIconDelete(this)"> 삭제
                            </label>
                        </div>
                        <div style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                            <label style="font-size:0.75rem;display:flex;align-items:center;gap:0.25rem;cursor:pointer;">
                                <input type="radio" name="icon_type" value="text" checked onchange="toggleIconInput()">
                                <span>Heroicons</span>
                            </label>
                            <label style="font-size:0.75rem;display:flex;align-items:center;gap:0.25rem;cursor:pointer;">
                                <input type="radio" name="icon_type" value="file" onchange="toggleIconInput()">
                                <span>이미지 업로드</span>
                            </label>
                        </div>
                        <div id="icon-text-input">
                            <input type="text" name="fc_icon" id="fc_icon" class="mg-form-input" placeholder="heart, gift, shopping-cart 등">
                        </div>
                        <div id="icon-file-input" style="display:none;">
                            <input type="file" name="fc_icon_file" accept="image/*" class="mg-form-input" style="padding:0.25rem;">
                        </div>
                        <p style="font-size:0.7rem;color:var(--mg-text-muted);margin-top:4px;">
                            <a href="https://heroicons.com/" target="_blank" style="color:var(--mg-accent);">Heroicons</a> 아이콘명 또는 이미지 파일 (권장: 24x24px)
                        </p>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="fc_order" id="fc_order" class="mg-form-input" value="0">
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">설명</label>
                    <textarea name="fc_desc" id="fc_desc" class="mg-form-textarea" rows="2"></textarea>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">필요 노동력 *</label>
                    <input type="number" name="fc_stamina_cost" id="fc_stamina_cost" class="mg-form-input" min="0" value="100" required>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">필요 재료</label>
                    <div id="material-costs">
                        <?php foreach ($material_types as $mt) { ?>
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                            <span style="width:100px;display:flex;align-items:center;gap:4px;"><?php echo mg_icon($mt['mt_icon'], 'w-4 h-4'); ?> <?php echo htmlspecialchars($mt['mt_name']); ?></span>
                            <input type="number" name="mat_cost[<?php echo $mt['mt_id']; ?>]" class="mg-form-input" style="width:100px;" min="0" value="0" placeholder="0">
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">해금 대상 타입</label>
                        <select name="fc_unlock_type" id="fc_unlock_type" class="mg-form-input" onchange="toggleUnlockTarget()">
                            <option value="">선택 안함</option>
                            <option value="board">게시판</option>
                            <option value="shop">상점</option>
                            <option value="gift">선물함</option>
                            <option value="achievement">업적</option>
                            <option value="history">연혁</option>
                            <option value="fountain">분수대</option>
                        </select>
                    </div>
                    <div class="mg-form-group" id="unlock_target_group">
                        <label class="mg-form-label">해금 대상</label>
                        <!-- 게시판 선택 드롭다운 -->
                        <select name="fc_unlock_target_board" id="fc_unlock_target_board" class="mg-form-input" style="display:none;">
                            <option value="">게시판 선택</option>
                            <?php foreach ($boards as $board) { ?>
                            <option value="<?php echo $board['bo_table']; ?>"><?php echo htmlspecialchars($board['bo_subject']); ?> (<?php echo $board['bo_table']; ?>)</option>
                            <?php } ?>
                        </select>
                        <!-- 기타 타입용 텍스트 입력 -->
                        <input type="text" name="fc_unlock_target_text" id="fc_unlock_target_text" class="mg-form-input" style="display:none;" placeholder="대상 ID">
                        <!-- 실제 전송될 hidden -->
                        <input type="hidden" name="fc_unlock_target" id="fc_unlock_target" value="">
                        <p id="unlock_target_help" style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:4px;"></p>
                    </div>
                </div>
            </div>

            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<style>
.mg-progress {
    height: 6px;
    background: var(--mg-bg-tertiary);
    border-radius: 3px;
    overflow: hidden;
}
.mg-progress-bar {
    height: 100%;
    background: var(--mg-accent);
    transition: width 0.3s;
}
</style>

<script>
var facilities = <?php echo json_encode($facilities); ?>;

function toggleUnlockTarget() {
    var type = document.getElementById('fc_unlock_type').value;
    var boardSelect = document.getElementById('fc_unlock_target_board');
    var textInput = document.getElementById('fc_unlock_target_text');
    var targetGroup = document.getElementById('unlock_target_group');
    var helpText = document.getElementById('unlock_target_help');

    // 모두 숨기기
    boardSelect.style.display = 'none';
    textInput.style.display = 'none';
    targetGroup.style.display = 'block';

    if (type === 'board') {
        boardSelect.style.display = 'block';
        helpText.textContent = '해금할 게시판을 선택하세요';
    } else if (type === 'shop' || type === 'gift' || type === 'achievement' || type === 'fountain') {
        // 대상 ID 불필요
        targetGroup.style.display = 'none';
        helpText.textContent = '';
    } else if (type === 'history') {
        textInput.style.display = 'block';
        helpText.textContent = '연혁 ID를 입력하세요 (선택)';
    } else {
        targetGroup.style.display = 'none';
        helpText.textContent = '';
    }

    syncUnlockTarget();
}

function syncUnlockTarget() {
    var type = document.getElementById('fc_unlock_type').value;
    var hidden = document.getElementById('fc_unlock_target');

    if (type === 'board') {
        hidden.value = document.getElementById('fc_unlock_target_board').value;
    } else if (type === 'history') {
        hidden.value = document.getElementById('fc_unlock_target_text').value;
    } else {
        hidden.value = '';
    }
}

function openFacilityModal() {
    document.getElementById('modal-title').textContent = '시설 추가';
    document.getElementById('form_w').value = '';
    document.getElementById('form_fc_id').value = '';
    document.getElementById('facility-form').reset();
    document.getElementById('fc_unlock_target').value = '';
    document.getElementById('fc_unlock_target_board').value = '';
    document.getElementById('fc_unlock_target_text').value = '';
    document.getElementById('current-icon-preview').style.display = 'none';
    document.querySelector('input[name="icon_type"][value="text"]').checked = true;
    toggleIconInput();
    toggleUnlockTarget();
    document.getElementById('facility-modal').style.display = 'flex';
}

function editFacility(fc_id) {
    var fc = facilities.find(function(f) { return f.fc_id == fc_id; });
    if (!fc) return;

    document.getElementById('modal-title').textContent = '시설 수정';
    document.getElementById('form_w').value = 'u';
    document.getElementById('form_fc_id').value = fc_id;
    document.getElementById('fc_name').value = fc.fc_name;
    document.getElementById('fc_order').value = fc.fc_order;
    document.getElementById('fc_desc').value = fc.fc_desc;
    document.getElementById('fc_stamina_cost').value = fc.fc_stamina_cost;
    document.getElementById('fc_unlock_type').value = fc.fc_unlock_type || '';
    document.getElementById('fc_unlock_target').value = fc.fc_unlock_target || '';

    // 아이콘 설정
    var iconVal = fc.fc_icon || '';
    var isImage = iconVal && (iconVal.indexOf('/') !== -1 || iconVal.indexOf('http') === 0);
    if (isImage) {
        document.getElementById('fc_icon').value = '';
        showIconPreview(iconVal);
    } else {
        document.getElementById('fc_icon').value = iconVal;
        document.getElementById('current-icon-preview').style.display = 'none';
    }
    document.querySelector('input[name="icon_type"][value="text"]').checked = true;
    toggleIconInput();

    // 해금 대상 필드 설정
    if (fc.fc_unlock_type === 'board') {
        document.getElementById('fc_unlock_target_board').value = fc.fc_unlock_target || '';
    } else {
        document.getElementById('fc_unlock_target_text').value = fc.fc_unlock_target || '';
    }
    toggleUnlockTarget();

    // 재료 비용 설정
    document.querySelectorAll('[name^="mat_cost"]').forEach(function(input) {
        input.value = 0;
    });
    fc.materials.forEach(function(mat) {
        var input = document.querySelector('[name="mat_cost[' + mat.mt_id + ']"]');
        if (input) input.value = mat.fmc_required;
    });

    document.getElementById('facility-modal').style.display = 'flex';
}

// 드롭다운/텍스트 변경 시 hidden 값 동기화
document.getElementById('fc_unlock_target_board').addEventListener('change', syncUnlockTarget);
document.getElementById('fc_unlock_target_text').addEventListener('input', syncUnlockTarget);

function closeModal() {
    document.getElementById('facility-modal').style.display = 'none';
}

function toggleIconInput() {
    var type = document.querySelector('input[name="icon_type"]:checked').value;
    document.getElementById('icon-text-input').style.display = type === 'text' ? '' : 'none';
    document.getElementById('icon-file-input').style.display = type === 'file' ? '' : 'none';
}

function toggleIconDelete(checkbox) {
    var preview = document.getElementById('current-icon-preview');
    if (checkbox.checked) {
        preview.style.opacity = '0.5';
    } else {
        preview.style.opacity = '1';
    }
}

function showIconPreview(iconValue) {
    var preview = document.getElementById('current-icon-preview');
    var img = document.getElementById('icon-preview-img');

    if (iconValue && (iconValue.indexOf('/') !== -1 || iconValue.indexOf('http') === 0)) {
        // 이미지 경로인 경우
        img.src = iconValue;
        preview.style.display = 'flex';
        preview.style.alignItems = 'center';
    } else {
        preview.style.display = 'none';
    }
}

function startBuilding(fc_id) {
    if (!confirm('이 시설의 건설을 시작하시겠습니까?')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="start"><input type="hidden" name="fc_id" value="' + fc_id + '">';
    document.body.appendChild(form);
    form.submit();
}

function forceComplete(fc_id) {
    if (!confirm('이 시설을 강제 완공하시겠습니까?\n(기여 기록이 있는 경우 명예의 전당에 기록됩니다)')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="complete"><input type="hidden" name="fc_id" value="' + fc_id + '">';
    document.body.appendChild(form);
    form.submit();
}

function deleteFacility(fc_id) {
    if (!confirm('이 시설을 삭제하시겠습니까?\n모든 기여 기록도 함께 삭제됩니다.')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="fc_id" value="' + fc_id + '">';
    document.body.appendChild(form);
    form.submit();
}

// 모달 외부 클릭 시 닫기
document.getElementById('facility-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
