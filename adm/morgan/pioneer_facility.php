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

<!-- 통계 -->
<?php $_pvm = mg_config('pioneer_view_mode', 'card'); $_pmi = mg_config('pioneer_map_image', ''); ?>
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

<!-- UI 모드 토글 + 추가 버튼 -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <div style="display:flex;align-items:center;gap:0.75rem;">
        <span style="font-size:0.85rem;color:var(--mg-text-secondary);">유저 UI:</span>
        <div style="display:inline-flex;border-radius:8px;overflow:hidden;border:1px solid var(--mg-bg-tertiary);">
            <button type="button" id="btn-mode-card" onclick="setViewMode('card')" style="padding:6px 14px;font-size:0.8rem;border:none;cursor:pointer;transition:background 0.15s;<?php echo $_pvm !== 'base' ? 'background:var(--mg-accent);color:var(--mg-bg-primary);font-weight:600;' : 'background:var(--mg-bg-primary);color:var(--mg-text-secondary);'; ?>">카드뷰</button>
            <button type="button" id="btn-mode-base" onclick="setViewMode('base')" style="padding:6px 14px;font-size:0.8rem;border:none;cursor:pointer;transition:background 0.15s;<?php echo $_pvm === 'base' ? 'background:var(--mg-accent);color:var(--mg-bg-primary);font-weight:600;' : 'background:var(--mg-bg-primary);color:var(--mg-text-secondary);'; ?>"<?php echo !$_pmi ? ' disabled title="거점 이미지를 먼저 등록하세요"' : ''; ?>>거점뷰</button>
        </div>
    </div>
    <button type="button" class="mg-btn mg-btn-primary" onclick="openFacilityModal()">시설 추가</button>
</div>

<!-- 거점뷰 설정 (거점뷰일 때만 표시) -->
<div id="base-section" style="display:<?php echo $_pvm === 'base' ? 'block' : 'none'; ?>;">
    <!-- 거점 이미지 업로드 -->
    <div class="mg-card" style="margin-bottom:1rem;">
        <div class="mg-card-header">
            <h3>거점 이미지</h3>
            <span style="font-size:0.8rem;color:var(--mg-text-muted);">시설 배치용 거점 맵 이미지</span>
        </div>
        <div class="mg-card-body">
            <?php if ($_pmi) { ?>
            <div style="margin-bottom:12px;">
                <img src="<?php echo htmlspecialchars($_pmi); ?>" style="max-width:300px;max-height:150px;border-radius:8px;border:1px solid var(--mg-bg-tertiary);">
            </div>
            <?php } ?>
            <form id="view-config-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:end;flex-wrap:wrap;">
                <input type="hidden" name="w" value="config">
                <input type="hidden" name="pioneer_view_mode" value="base">
                <input type="hidden" name="pioneer_map_action" id="pioneer_map_action" value="">
                <div class="mg-form-group" style="margin-bottom:0;">
                    <label class="mg-form-label" style="font-size:0.75rem;">이미지 (JPG/PNG/WebP, 최대 20MB, 권장 1600px+)</label>
                    <input type="file" name="pioneer_map_image_file" accept="image/*" class="mg-form-input" style="width:280px;">
                </div>
                <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">업로드</button>
                <?php if ($_pmi) { ?>
                <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteBaseImage()">이미지 삭제</button>
                <?php } ?>
            </form>
        </div>
    </div>

    <?php if ($_pmi) { ?>
    <!-- 맵 마커 편집기 -->
    <div class="mg-card" style="margin-bottom:1rem;">
        <div class="mg-card-header">
            <h3>마커 배치</h3>
            <span style="font-size:0.8rem;color:var(--mg-text-muted);">시설 선택 후 맵 클릭으로 좌표 지정 · 기존 마커 클릭 시 수정</span>
        </div>
        <div class="mg-card-body">
            <div style="display:flex;gap:1rem;margin-bottom:0.75rem;flex-wrap:wrap;align-items:center;">
                <label style="font-size:0.85rem;color:var(--mg-text-secondary);">배치할 시설:</label>
                <select id="marker-target-fc" class="mg-form-input" style="width:auto;min-width:200px;">
                    <option value="">-- 시설 선택 --</option>
                    <?php foreach ($facilities as $fc): ?>
                    <option value="<?php echo $fc['fc_id']; ?>"
                            data-x="<?php echo $fc['fc_map_x'] ?? ''; ?>"
                            data-y="<?php echo $fc['fc_map_y'] ?? ''; ?>">
                        <?php echo htmlspecialchars($fc['fc_name']); ?>
                        <?php if ($fc['fc_map_x'] !== null): ?>(<?php echo round($fc['fc_map_x'],1); ?>%, <?php echo round($fc['fc_map_y'],1); ?>%)<?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="mg-btn mg-btn-sm mg-btn-secondary" onclick="removeMarkerCoord()" title="선택 시설의 좌표 제거">좌표 제거</button>
                <span id="marker-status" style="font-size:0.8rem;color:var(--mg-accent);"></span>
            </div>
            <div id="map-marker-container" style="position:relative;display:inline-block;max-width:100%;cursor:crosshair;border:2px solid var(--mg-bg-tertiary);border-radius:6px;overflow:hidden;">
                <img id="map-marker-img" src="<?php echo htmlspecialchars($_pmi); ?>" alt="거점 맵"
                     style="display:block;max-width:100%;height:auto;"
                     onclick="placeMarker(event)">
                <?php foreach ($facilities as $fc):
                    if ($fc['fc_map_x'] === null || $fc['fc_map_y'] === null) continue;
                ?>
                <div class="map-admin-marker"
                     data-fc-id="<?php echo $fc['fc_id']; ?>"
                     style="position:absolute;left:<?php echo $fc['fc_map_x']; ?>%;top:<?php echo $fc['fc_map_y']; ?>%;transform:translate(-50%,-50%);z-index:10;">
                    <div style="width:14px;height:14px;border-radius:50%;background:var(--mg-accent);border:2px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.5);"></div>
                    <div style="position:absolute;top:-22px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:0.7rem;color:#fff;background:rgba(0,0,0,0.75);padding:1px 6px;border-radius:3px;pointer-events:none;">
                        <?php echo htmlspecialchars($fc['fc_name']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<!-- 안내 -->
<div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
    시설은 유저들이 스테미나와 재료를 기여하여 건설합니다. <strong>스테미나와 재료가 모두 충족되면 자동으로 완공</strong>됩니다.<br>
    <span style="font-size:0.85rem;color:var(--mg-text-muted);">강제완공 버튼은 테스트나 긴급 상황에서만 사용하세요.</span>
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
                    <th style="width:120px;">스테미나</th>
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
                                'history' => '연대기',
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
                        <?php mg_icon_input('fc_icon', '', array('delete_name' => 'del_icon', 'placeholder' => 'heart, gift, shopping-cart 등')); ?>
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
                    <label class="mg-form-label">필요 스테미나 *</label>
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

                <div id="map-coord-section" class="mg-form-group" style="display:none;">
                    <label class="mg-form-label">맵 좌표 (거점뷰용)</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <label style="font-size:0.8rem;color:var(--mg-text-muted);">X%</label>
                        <input type="number" name="fc_map_x" id="fc_map_x" class="mg-form-input" style="width:100px;" min="0" max="100" step="0.1" placeholder="0.0">
                        <label style="font-size:0.8rem;color:var(--mg-text-muted);">Y%</label>
                        <input type="number" name="fc_map_y" id="fc_map_y" class="mg-form-input" style="width:100px;" min="0" max="100" step="0.1" placeholder="0.0">
                    </div>
                    <small style="color:var(--mg-text-muted);font-size:0.75rem;">이미지 좌상단 기준 백분율 좌표 (0~100)</small>
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
                            <option value="history">연대기</option>
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
var pioneerViewMode = '<?php echo mg_config('pioneer_view_mode', 'card'); ?>';

// UI 모드 전환 (AJAX)
function setViewMode(mode) {
    var formData = new FormData();
    formData.append('action', 'set_view_mode');
    formData.append('mode', mode);

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php', {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            pioneerViewMode = data.mode;
            var btnCard = document.getElementById('btn-mode-card');
            var btnBase = document.getElementById('btn-mode-base');
            if (data.mode === 'card') {
                btnCard.style.background = 'var(--mg-accent)';
                btnCard.style.color = 'var(--mg-bg-primary)';
                btnCard.style.fontWeight = '600';
                btnBase.style.background = 'var(--mg-bg-primary)';
                btnBase.style.color = 'var(--mg-text-secondary)';
                btnBase.style.fontWeight = '';
            } else {
                btnBase.style.background = 'var(--mg-accent)';
                btnBase.style.color = 'var(--mg-bg-primary)';
                btnBase.style.fontWeight = '600';
                btnCard.style.background = 'var(--mg-bg-primary)';
                btnCard.style.color = 'var(--mg-text-secondary)';
                btnCard.style.fontWeight = '';
            }
            document.getElementById('base-section').style.display = data.mode === 'base' ? 'block' : 'none';
            updateMapCoordSection();
        }
    });
}

// 거점 이미지 삭제 (AJAX)
function deleteBaseImage() {
    if (!confirm('거점 이미지를 삭제하시겠습니까?\n카드뷰로 자동 전환됩니다.')) return;

    var formData = new FormData();
    formData.append('action', 'delete_base_image');

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php', {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) location.reload();
    });
}

// 거점뷰일 때만 좌표 입력 표시
function updateMapCoordSection() {
    var section = document.getElementById('map-coord-section');
    if (section) section.style.display = pioneerViewMode === 'base' ? 'block' : 'none';
}
updateMapCoordSection();

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
        // 연혁은 기능 자체 해금이므로 대상 ID 불필요
        targetGroup.style.display = 'none';
        helpText.textContent = '';
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
    mgIconReset('fc_icon');
    document.getElementById('fc_map_x').value = '';
    document.getElementById('fc_map_y').value = '';
    toggleUnlockTarget();
    updateMapCoordSection();
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
    mgIconSet('fc_icon', fc.fc_icon || '');

    // 좌표 설정
    document.getElementById('fc_map_x').value = fc.fc_map_x || '';
    document.getElementById('fc_map_y').value = fc.fc_map_y || '';
    updateMapCoordSection();

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
    if (e.target === this && document._mgMdTarget === this) closeModal();
});

// === 맵 마커 배치 ===
function placeMarker(e) {
    var select = document.getElementById('marker-target-fc');
    if (!select) return;
    var fcId = select.value;
    if (!fcId) {
        document.getElementById('marker-status').textContent = '먼저 시설을 선택하세요';
        setTimeout(function(){ document.getElementById('marker-status').textContent = ''; }, 2000);
        return;
    }

    var img = document.getElementById('map-marker-img');
    var rect = img.getBoundingClientRect();
    var x = ((e.clientX - rect.left) / rect.width * 100).toFixed(2);
    var y = ((e.clientY - rect.top) / rect.height * 100).toFixed(2);

    // AJAX로 좌표 저장
    saveMarkerCoord(fcId, x, y);
}

function saveMarkerCoord(fcId, x, y) {
    var formData = new FormData();
    formData.append('w', 'marker');
    formData.append('fc_id', fcId);
    formData.append('fc_map_x', x);
    formData.append('fc_map_y', y);

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php', {
        method: 'POST',
        body: formData
    }).then(function(res){ return res.text(); }).then(function(html) {
        // 마커 갱신
        updateMarkerOnMap(fcId, x, y);
        updateSelectOption(fcId, x, y);
        document.getElementById('marker-status').textContent = '좌표 저장됨 (' + x + '%, ' + y + '%)';
        setTimeout(function(){ document.getElementById('marker-status').textContent = ''; }, 3000);
    }).catch(function(err) {
        alert('좌표 저장 실패: ' + err);
    });
}

function removeMarkerCoord() {
    var select = document.getElementById('marker-target-fc');
    if (!select || !select.value) return;
    var fcId = select.value;

    var formData = new FormData();
    formData.append('w', 'marker');
    formData.append('fc_id', fcId);
    formData.append('fc_map_x', '');
    formData.append('fc_map_y', '');

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_facility_update.php', {
        method: 'POST',
        body: formData
    }).then(function(res){ return res.text(); }).then(function() {
        // 맵에서 마커 제거
        var existing = document.querySelector('.map-admin-marker[data-fc-id="' + fcId + '"]');
        if (existing) existing.remove();
        updateSelectOption(fcId, '', '');
        document.getElementById('marker-status').textContent = '좌표가 제거되었습니다';
        setTimeout(function(){ document.getElementById('marker-status').textContent = ''; }, 3000);
    });
}

function updateMarkerOnMap(fcId, x, y) {
    var container = document.getElementById('map-marker-container');
    if (!container) return;

    // 기존 마커 제거
    var existing = container.querySelector('.map-admin-marker[data-fc-id="' + fcId + '"]');
    if (existing) existing.remove();

    // 시설명 가져오기
    var select = document.getElementById('marker-target-fc');
    var option = select.querySelector('option[value="' + fcId + '"]');
    var name = option ? option.textContent.split('(')[0].trim() : '';

    // 새 마커 추가
    var marker = document.createElement('div');
    marker.className = 'map-admin-marker';
    marker.setAttribute('data-fc-id', fcId);
    marker.style.cssText = 'position:absolute;left:' + x + '%;top:' + y + '%;transform:translate(-50%,-50%);z-index:10;';
    marker.innerHTML =
        '<div style="width:14px;height:14px;border-radius:50%;background:var(--mg-accent);border:2px solid #fff;box-shadow:0 0 6px rgba(0,0,0,0.5);"></div>' +
        '<div style="position:absolute;top:-22px;left:50%;transform:translateX(-50%);white-space:nowrap;font-size:0.7rem;color:#fff;background:rgba(0,0,0,0.75);padding:1px 6px;border-radius:3px;pointer-events:none;">' + name + '</div>';
    container.appendChild(marker);
}

function updateSelectOption(fcId, x, y) {
    var select = document.getElementById('marker-target-fc');
    var option = select.querySelector('option[value="' + fcId + '"]');
    if (!option) return;
    var name = option.textContent.split('(')[0].trim();
    if (x && y) {
        option.textContent = name + ' (' + parseFloat(x).toFixed(1) + '%, ' + parseFloat(y).toFixed(1) + '%)';
    } else {
        option.textContent = name;
    }
    option.setAttribute('data-x', x);
    option.setAttribute('data-y', y);
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
