<?php
/**
 * Morgan Edition - 재료 관리
 */

$sub_menu = "801100";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 재료 종류 목록
$material_types = mg_get_material_types();

$g5['title'] = '재료 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 안내 -->
<div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
    재료는 유저들이 활동(글쓰기, 댓글, 출석 등)을 통해 획득하고 시설 건설에 기여할 때 사용합니다.
</div>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 재료 종류</div>
        <div class="mg-stat-value"><?php echo count($material_types); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">총 유저 보유량</div>
        <div class="mg-stat-value"><?php
            global $mg;
            $total = sql_fetch("SELECT COALESCE(SUM(um_amount), 0) as total FROM {$mg['user_material_table']}");
            echo number_format($total['total'] ?? 0);
        ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">재료 보유 유저</div>
        <div class="mg-stat-value"><?php
            $users = sql_fetch("SELECT COUNT(DISTINCT mb_id) as cnt FROM {$mg['user_material_table']} WHERE um_amount > 0");
            echo number_format($users['cnt'] ?? 0);
        ?></div>
    </div>
</div>

<!-- 재료 추가 버튼 -->
<div style="margin-bottom:1rem;text-align:right;">
    <button type="button" class="mg-btn mg-btn-primary" onclick="openMaterialModal()">재료 추가</button>
</div>

<!-- 재료 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:850px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:55px;">순서</th>
                    <th style="width:70px;">아이콘</th>
                    <th style="width:110px;">코드</th>
                    <th style="width:100px;">이름</th>
                    <th style="width:200px;">설명</th>
                    <th style="width:120px;">총 보유량</th>
                    <th style="width:150px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($material_types as $mt) {
                    $mat_total = sql_fetch("SELECT COALESCE(SUM(um_amount), 0) as total FROM {$mg['user_material_table']} WHERE mt_id = {$mt['mt_id']}");
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $mt['mt_order']; ?></td>
                    <td style="text-align:center;">
                        <?php if ($mt['mt_icon']) {
                            echo mg_icon($mt['mt_icon'], 'w-6 h-6');
                        } else {
                            echo '<span style="color:var(--mg-text-muted);">-</span>';
                        } ?>
                    </td>
                    <td><code><?php echo htmlspecialchars($mt['mt_code']); ?></code></td>
                    <td><strong><?php echo htmlspecialchars($mt['mt_name']); ?></strong></td>
                    <td style="color:var(--mg-text-muted);font-size:0.9rem;"><?php echo htmlspecialchars($mt['mt_desc']); ?></td>
                    <td style="text-align:center;">
                        <span class="mg-badge"><?php echo number_format($mat_total['total'] ?? 0); ?></span>
                    </td>
                    <td style="white-space:nowrap;">
                        <div style="display:flex;gap:4px;flex-wrap:nowrap;">
                            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editMaterial(<?php echo $mt['mt_id']; ?>)">수정</button>
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteMaterial(<?php echo $mt['mt_id']; ?>)">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($material_types)) { ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 재료가 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 유저 재료 지급 -->
<div class="mg-card" style="margin-top:2rem;">
    <div class="mg-card-header">
        <h3>유저 재료 수동 지급</h3>
    </div>
    <div class="mg-card-body">
        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_material_update.php" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="w" value="give">
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">회원 ID</label>
                <input type="text" name="mb_id" class="mg-form-input" required style="width:150px;">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">재료</label>
                <select name="mt_id" class="mg-form-select" required style="width:150px;">
                    <?php foreach ($material_types as $mt) { ?>
                    <option value="<?php echo $mt['mt_id']; ?>"><?php echo htmlspecialchars($mt['mt_name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">수량</label>
                <input type="number" name="amount" class="mg-form-input" required min="1" value="1" style="width:100px;">
            </div>
            <button type="submit" class="mg-btn mg-btn-success">지급</button>
        </form>
    </div>
</div>

<!-- 유저 노동력 지급 -->
<div class="mg-card" style="margin-top:1rem;">
    <div class="mg-card-header">
        <h3>유저 노동력 수동 지급</h3>
    </div>
    <div class="mg-card-body">
        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_material_update.php" style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
            <input type="hidden" name="w" value="stamina">
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">회원 ID</label>
                <input type="text" name="mb_id" class="mg-form-input" required style="width:150px;">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label">추가 노동력</label>
                <input type="number" name="amount" class="mg-form-input" required min="1" value="10" style="width:100px;">
            </div>
            <button type="submit" class="mg-btn mg-btn-primary">지급</button>
        </form>
        <p style="margin-top:0.5rem;font-size:0.85rem;color:var(--mg-text-muted);">
            * 현재 노동력에 추가됩니다 (일일 상한 초과 가능)
        </p>
    </div>
</div>

<!-- 재료 모달 -->
<div id="material-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:500px;">
        <div class="mg-modal-header">
            <h3 id="modal-title">재료 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="material-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_material_update.php" enctype="multipart/form-data">
            <input type="hidden" name="w" id="form_w" value="">
            <input type="hidden" name="mt_id" id="form_mt_id" value="">

            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">재료 코드 * (영문 소문자, 언더스코어만)</label>
                    <input type="text" name="mt_code" id="mt_code" class="mg-form-input" required pattern="[a-z_]+" placeholder="예: wood">
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">이름 *</label>
                    <input type="text" name="mt_name" id="mt_name" class="mg-form-input" required placeholder="예: 목재">
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
                                <span>이미지</span>
                            </label>
                        </div>
                        <div id="icon-text-input">
                            <input type="text" name="mt_icon" id="mt_icon" class="mg-form-input" placeholder="cube, sparkles 등">
                        </div>
                        <div id="icon-file-input" style="display:none;">
                            <input type="file" name="mt_icon_file" accept="image/*" class="mg-form-input" style="padding:0.25rem;">
                        </div>
                        <p style="font-size:0.7rem;color:var(--mg-text-muted);margin-top:4px;">
                            <a href="https://heroicons.com/" target="_blank" style="color:var(--mg-accent);">Heroicons</a> 아이콘명 또는 이미지 파일
                        </p>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="mt_order" id="mt_order" class="mg-form-input" value="0">
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">설명</label>
                    <input type="text" name="mt_desc" id="mt_desc" class="mg-form-input" placeholder="재료에 대한 간단한 설명">
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
var materials = <?php echo json_encode($material_types); ?>;

function openMaterialModal() {
    document.getElementById('modal-title').textContent = '재료 추가';
    document.getElementById('form_w').value = '';
    document.getElementById('form_mt_id').value = '';
    document.getElementById('material-form').reset();
    document.getElementById('mt_code').readOnly = false;
    document.getElementById('current-icon-preview').style.display = 'none';
    document.querySelector('input[name="icon_type"][value="text"]').checked = true;
    toggleIconInput();
    document.getElementById('material-modal').style.display = 'flex';
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
        img.src = iconValue;
        preview.style.display = 'flex';
        preview.style.alignItems = 'center';
    } else {
        preview.style.display = 'none';
    }
}

function editMaterial(mt_id) {
    var mt = materials.find(function(m) { return m.mt_id == mt_id; });
    if (!mt) return;

    document.getElementById('modal-title').textContent = '재료 수정';
    document.getElementById('form_w').value = 'u';
    document.getElementById('form_mt_id').value = mt_id;
    document.getElementById('mt_code').value = mt.mt_code;
    document.getElementById('mt_code').readOnly = true; // 코드는 수정 불가
    document.getElementById('mt_name').value = mt.mt_name;
    document.getElementById('mt_order').value = mt.mt_order;
    document.getElementById('mt_desc').value = mt.mt_desc;

    // 아이콘 설정
    var iconVal = mt.mt_icon || '';
    var isImage = iconVal && (iconVal.indexOf('/') !== -1 || iconVal.indexOf('http') === 0);
    if (isImage) {
        document.getElementById('mt_icon').value = '';
        showIconPreview(iconVal);
    } else {
        document.getElementById('mt_icon').value = iconVal;
        document.getElementById('current-icon-preview').style.display = 'none';
    }
    document.querySelector('input[name="icon_type"][value="text"]').checked = true;
    toggleIconInput();

    document.getElementById('material-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('material-modal').style.display = 'none';
}

function deleteMaterial(mt_id) {
    if (!confirm('이 재료를 삭제하시겠습니까?\n이 재료를 사용하는 시설 비용 및 유저 보유량이 함께 삭제됩니다.')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/pioneer_material_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="mt_id" value="' + mt_id + '">';
    document.body.appendChild(form);
    form.submit();
}

// 모달 외부 클릭 시 닫기
document.getElementById('material-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
