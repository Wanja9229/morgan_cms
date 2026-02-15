<?php
/**
 * Morgan Edition - 파견지 관리
 */

$sub_menu = "801110";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 파견지 목록
$areas = mg_get_expedition_areas();

// 재료 종류 목록 (드롭 테이블용)
$material_types = mg_get_material_types();

// 시설 목록 (해금 조건용)
$facility_list = array();
$fc_result = sql_query("SELECT fc_id, fc_name, fc_status FROM {$g5['mg_facility_table']} ORDER BY fc_order, fc_id");
while ($fc_row = sql_fetch_array($fc_result)) {
    $facility_list[] = $fc_row;
}

$g5['title'] = '파견지 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 파견지</div>
        <div class="mg-stat-value"><?php echo count($areas); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">활성</div>
        <div class="mg-stat-value"><?php echo count(array_filter($areas, function($a) { return $a['ea_status'] === 'active'; })); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">숨김</div>
        <div class="mg-stat-value"><?php echo count(array_filter($areas, function($a) { return $a['ea_status'] === 'hidden'; })); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">잠김</div>
        <div class="mg-stat-value"><?php echo count(array_filter($areas, function($a) { return $a['ea_status'] === 'locked'; })); ?></div>
    </div>
</div>

<!-- 추가 버튼 -->
<div style="margin-bottom:1rem;text-align:right;">
    <button type="button" class="mg-btn mg-btn-primary" onclick="openAreaModal()">파견지 추가</button>
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:900px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:50px;">순서</th>
                    <th style="width:60px;">아이콘</th>
                    <th style="width:140px;">파견지명</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:80px;">스태미나</th>
                    <th style="width:80px;">소요시간</th>
                    <th style="width:70px;">파트너PT</th>
                    <th style="width:180px;">드롭 아이템</th>
                    <th style="width:100px;">해금 조건</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($areas as $area) {
                    $status_badges = array(
                        'active' => '<span class="mg-badge mg-badge-success">활성</span>',
                        'hidden' => '<span class="mg-badge">숨김</span>',
                        'locked' => '<span class="mg-badge mg-badge-warning">잠김</span>',
                    );
                    $status_badge = isset($status_badges[$area['ea_status']]) ? $status_badges[$area['ea_status']] : '';

                    $duration_h = floor($area['ea_duration'] / 60);
                    $duration_m = $area['ea_duration'] % 60;
                    $duration_text = $duration_h > 0 ? $duration_h.'시간' : '';
                    $duration_text .= $duration_m > 0 ? ' '.$duration_m.'분' : '';
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $area['ea_order']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($area['ea_icon']) {
                            echo mg_icon($area['ea_icon'], 'w-6 h-6');
                        } else {
                            echo '<span style="font-size:1.5rem;">🗺️</span>';
                        } ?>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($area['ea_name']); ?></strong>
                        <?php if ($area['ea_desc']) { ?>
                        <br><span style="font-size:0.8rem;color:var(--mg-text-muted);"><?php echo mb_substr($area['ea_desc'], 0, 40); ?><?php echo mb_strlen($area['ea_desc']) > 40 ? '...' : ''; ?></span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;"><?php echo $status_badge; ?></td>
                    <td style="text-align:center;"><?php echo $area['ea_stamina_cost']; ?></td>
                    <td style="text-align:center;"><?php echo trim($duration_text); ?></td>
                    <td style="text-align:center;"><?php echo $area['ea_partner_point']; ?>P</td>
                    <td style="font-size:0.85rem;">
                        <?php foreach ($area['drops'] as $drop) {
                            $rare_style = $drop['ed_is_rare'] ? 'color:#a78bfa;font-weight:bold;' : '';
                        ?>
                        <span style="display:inline-flex;align-items:center;gap:2px;margin-right:6px;<?php echo $rare_style; ?>" title="<?php echo htmlspecialchars($drop['mt_name']); ?> (<?php echo $drop['ed_min']; ?>~<?php echo $drop['ed_max']; ?>개, <?php echo $drop['ed_chance']; ?>%)">
                            <?php echo mg_icon($drop['mt_icon'], 'w-4 h-4'); ?>
                            <?php echo $drop['ed_chance']; ?>%
                            <?php if ($drop['ed_is_rare']) echo '★'; ?>
                        </span>
                        <?php } ?>
                        <?php if (empty($area['drops'])) { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="font-size:0.85rem;">
                        <?php if ($area['ea_unlock_facility']) {
                            echo htmlspecialchars($area['unlock_facility_name'] ?: '시설 #'.$area['ea_unlock_facility']);
                        } else {
                            echo '-';
                        } ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editArea(<?php echo $area['ea_id']; ?>)">수정</button>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteArea(<?php echo $area['ea_id']; ?>)">삭제</button>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($areas)) { ?>
                <tr>
                    <td colspan="10" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 파견지가 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 파견지 모달 -->
<div id="area-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:700px;">
        <div class="mg-modal-header">
            <h3 id="modal-title">파견지 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="area-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/expedition_area_update.php">
            <input type="hidden" name="w" id="form_w" value="">
            <input type="hidden" name="ea_id" id="form_ea_id" value="">

            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">파견지명 *</label>
                    <input type="text" name="ea_name" id="ea_name" class="mg-form-input" required>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">설명</label>
                    <textarea name="ea_desc" id="ea_desc" class="mg-form-textarea" rows="2"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">아이콘 (Heroicons명)</label>
                        <input type="text" name="ea_icon" id="ea_icon" class="mg-form-input" placeholder="globe-americas, fire 등">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="ea_order" id="ea_order" class="mg-form-input" value="0">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">필요 스태미나 *</label>
                        <input type="number" name="ea_stamina_cost" id="ea_stamina_cost" class="mg-form-input" min="1" value="2" required>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">소요시간 (분) *</label>
                        <input type="number" name="ea_duration" id="ea_duration" class="mg-form-input" min="1" value="60" required>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">파트너 보상PT</label>
                        <input type="number" name="ea_partner_point" id="ea_partner_point" class="mg-form-input" min="0" value="10">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">상태</label>
                        <select name="ea_status" id="ea_status" class="mg-form-input">
                            <option value="active">활성</option>
                            <option value="hidden">숨김</option>
                            <option value="locked">잠김 (시설 해금 필요)</option>
                        </select>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">해금 조건 (시설)</label>
                        <select name="ea_unlock_facility" id="ea_unlock_facility" class="mg-form-input">
                            <option value="0">없음</option>
                            <?php foreach ($facility_list as $fc) { ?>
                            <option value="<?php echo $fc['fc_id']; ?>"><?php echo htmlspecialchars($fc['fc_name']); ?> (<?php echo $fc['fc_status']; ?>)</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <!-- 드롭 테이블 -->
                <div class="mg-form-group">
                    <label class="mg-form-label">드롭 테이블</label>
                    <div id="drop-table">
                        <!-- JS로 동적 추가 -->
                    </div>
                    <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="addDropRow()" style="margin-top:8px;">+ 드롭 추가</button>
                </div>
            </div>

            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
var areas = <?php echo json_encode($areas); ?>;
var materialTypes = <?php echo json_encode($material_types); ?>;

function addDropRow(data) {
    var container = document.getElementById('drop-table');
    var idx = container.children.length;
    var mt_options = '<option value="">재료 선택</option>';
    materialTypes.forEach(function(mt) {
        var selected = (data && data.mt_id == mt.mt_id) ? ' selected' : '';
        mt_options += '<option value="' + mt.mt_id + '"' + selected + '>' + mt.mt_name + '</option>';
    });

    var row = document.createElement('div');
    row.style.cssText = 'display:flex;gap:6px;align-items:center;margin-bottom:6px;flex-wrap:wrap;';
    row.innerHTML =
        '<select name="drop_mt_id[]" class="mg-form-input" style="width:120px;">' + mt_options + '</select>' +
        '<input type="number" name="drop_min[]" class="mg-form-input" style="width:60px;" min="0" value="' + (data ? data.ed_min : 1) + '" placeholder="최소">' +
        '<span style="color:var(--mg-text-muted);">~</span>' +
        '<input type="number" name="drop_max[]" class="mg-form-input" style="width:60px;" min="0" value="' + (data ? data.ed_max : 1) + '" placeholder="최대">' +
        '<input type="number" name="drop_chance[]" class="mg-form-input" style="width:65px;" min="1" max="100" value="' + (data ? data.ed_chance : 100) + '" placeholder="%">' +
        '<span style="font-size:0.75rem;color:var(--mg-text-muted);">%</span>' +
        '<label style="font-size:0.8rem;display:flex;align-items:center;gap:4px;cursor:pointer;"><input type="checkbox" name="drop_rare[' + idx + ']" value="1"' + (data && data.ed_is_rare == 1 ? ' checked' : '') + '> 레어</label>' +
        '<button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="this.parentElement.remove()" style="padding:2px 8px;">✕</button>';
    container.appendChild(row);
}

function openAreaModal() {
    document.getElementById('modal-title').textContent = '파견지 추가';
    document.getElementById('form_w').value = '';
    document.getElementById('form_ea_id').value = '';
    document.getElementById('area-form').reset();
    document.getElementById('drop-table').innerHTML = '';
    document.getElementById('area-modal').style.display = 'flex';
}

function editArea(ea_id) {
    var area = areas.find(function(a) { return a.ea_id == ea_id; });
    if (!area) return;

    document.getElementById('modal-title').textContent = '파견지 수정';
    document.getElementById('form_w').value = 'u';
    document.getElementById('form_ea_id').value = ea_id;
    document.getElementById('ea_name').value = area.ea_name;
    document.getElementById('ea_desc').value = area.ea_desc || '';
    document.getElementById('ea_icon').value = area.ea_icon || '';
    document.getElementById('ea_order').value = area.ea_order;
    document.getElementById('ea_stamina_cost').value = area.ea_stamina_cost;
    document.getElementById('ea_duration').value = area.ea_duration;
    document.getElementById('ea_partner_point').value = area.ea_partner_point;
    document.getElementById('ea_status').value = area.ea_status;
    document.getElementById('ea_unlock_facility').value = area.ea_unlock_facility || 0;

    // 드롭 테이블
    document.getElementById('drop-table').innerHTML = '';
    if (area.drops) {
        area.drops.forEach(function(drop) { addDropRow(drop); });
    }

    document.getElementById('area-modal').style.display = 'flex';
}

function deleteArea(ea_id) {
    if (!confirm('이 파견지를 삭제하시겠습니까?\n관련 드롭 테이블도 함께 삭제됩니다.')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/expedition_area_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="ea_id" value="' + ea_id + '">';
    document.body.appendChild(form);
    form.submit();
}

function closeModal() {
    document.getElementById('area-modal').style.display = 'none';
}

document.getElementById('area-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
