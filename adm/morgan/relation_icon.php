<?php
/**
 * Morgan Edition - 관리자 관계 아이콘 관리
 */
$sub_menu = '801600';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// AJAX: 아이콘 저장 (추가/수정)
if (isset($_POST['icon_action'])) {
    header('Content-Type: application/json; charset=utf-8');
    auth_check_menu($auth, $sub_menu, 'w');

    $action = $_POST['icon_action'];
    $ri_id = (int)($_POST['ri_id'] ?? 0);
    $ri_category = sql_real_escape_string(trim($_POST['ri_category'] ?? ''));
    $ri_icon = sql_real_escape_string(trim($_POST['ri_icon'] ?? ''));
    $ri_label = sql_real_escape_string(trim($_POST['ri_label'] ?? ''));
    $ri_color = sql_real_escape_string(trim($_POST['ri_color'] ?? '#95a5a6'));
    $ri_width = max(1, min(5, (int)($_POST['ri_width'] ?? 2)));
    $ri_order = (int)($_POST['ri_order'] ?? 0);
    $ri_active = (int)($_POST['ri_active'] ?? 1);

    if (!$ri_category || !$ri_icon || !$ri_label) {
        echo json_encode(array('success' => false, 'message' => '필수 항목을 입력해주세요.'));
        exit;
    }

    if ($action === 'add') {
        sql_query("INSERT INTO {$g5['mg_relation_icon_table']}
            (ri_category, ri_icon, ri_label, ri_color, ri_width, ri_order, ri_active)
            VALUES ('{$ri_category}', '{$ri_icon}', '{$ri_label}', '{$ri_color}', {$ri_width}, {$ri_order}, {$ri_active})");
        echo json_encode(array('success' => true, 'message' => '아이콘을 추가했습니다.'));
    } elseif ($action === 'update' && $ri_id) {
        sql_query("UPDATE {$g5['mg_relation_icon_table']} SET
            ri_category = '{$ri_category}', ri_icon = '{$ri_icon}', ri_label = '{$ri_label}',
            ri_color = '{$ri_color}', ri_width = {$ri_width}, ri_order = {$ri_order}, ri_active = {$ri_active}
            WHERE ri_id = {$ri_id}");
        echo json_encode(array('success' => true, 'message' => '아이콘을 수정했습니다.'));
    } elseif ($action === 'delete' && $ri_id) {
        // 사용 중인 관계가 있는지 확인
        $in_use = sql_fetch("SELECT COUNT(*) AS cnt FROM {$g5['mg_relation_table']} WHERE ri_id = {$ri_id}");
        if ($in_use['cnt'] > 0) {
            echo json_encode(array('success' => false, 'message' => '이 아이콘을 사용 중인 관계가 '.$in_use['cnt'].'개 있어 삭제할 수 없습니다. 비활성화를 사용해주세요.'));
        } else {
            sql_query("DELETE FROM {$g5['mg_relation_icon_table']} WHERE ri_id = {$ri_id}");
            echo json_encode(array('success' => true, 'message' => '아이콘을 삭제했습니다.'));
        }
    } else {
        echo json_encode(array('success' => false, 'message' => '알 수 없는 요청입니다.'));
    }
    exit;
}

// 목록 조회
$icons = mg_get_relation_icons(false);

include_once('./_head.php');
?>

<div class="local_desc01 local_desc">
    <span class="title01">관계 아이콘 관리</span>
    <span class="title02">캐릭터 관계에 사용되는 아이콘을 관리합니다.</span>
</div>

<style>
.ri-table { width:100%; border-collapse:collapse; }
.ri-table th, .ri-table td { padding:8px 12px; border:1px solid #444; text-align:left; font-size:14px; }
.ri-table th { background:#2a2a2a; color:#ccc; }
.ri-table td { background:#1a1a1a; color:#e0e0e0; }
.ri-table tr:hover td { background:#222; }
.ri-btn { padding:4px 10px; border-radius:4px; font-size:12px; cursor:pointer; border:none; }
.ri-btn-primary { background:#8a0000; color:white; }
.ri-btn-primary:hover { background:#a00; }
.ri-btn-danger { background:#444; color:#e74c3c; }
.ri-btn-danger:hover { background:#555; }
.ri-input { background:#111; border:1px solid #444; color:#e0e0e0; padding:4px 8px; border-radius:4px; font-size:13px; }
.ri-input:focus { border-color:#8a0000; outline:none; }
.color-preview { display:inline-block; width:16px; height:16px; border-radius:50%; vertical-align:middle; margin-right:4px; }
</style>

<div style="margin:20px 0;">
    <button class="ri-btn ri-btn-primary" onclick="openAddModal()">+ 아이콘 추가</button>
</div>

<table class="ri-table">
    <thead>
        <tr>
            <th style="width:50px">ID</th>
            <th style="width:80px">아이콘</th>
            <th style="width:100px">카테고리</th>
            <th>표시명</th>
            <th style="width:80px">색상</th>
            <th style="width:60px">굵기</th>
            <th style="width:60px">정렬</th>
            <th style="width:60px">상태</th>
            <th style="width:120px">관리</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($icons as $icon) { ?>
        <tr id="row-<?php echo $icon['ri_id']; ?>" style="<?php echo $icon['ri_active'] ? '' : 'opacity:0.5'; ?>">
            <td><?php echo $icon['ri_id']; ?></td>
            <td style="font-size:20px; text-align:center;"><?php echo $icon['ri_icon']; ?></td>
            <td><?php echo htmlspecialchars($icon['ri_category']); ?></td>
            <td><?php echo htmlspecialchars($icon['ri_label']); ?></td>
            <td><span class="color-preview" style="background:<?php echo $icon['ri_color']; ?>"></span><?php echo $icon['ri_color']; ?></td>
            <td><?php echo $icon['ri_width']; ?>px</td>
            <td><?php echo $icon['ri_order']; ?></td>
            <td><?php echo $icon['ri_active'] ? '<span style="color:#27ae60">활성</span>' : '<span style="color:#e74c3c">비활성</span>'; ?></td>
            <td>
                <button class="ri-btn ri-btn-primary" onclick='openEditModal(<?php echo json_encode($icon); ?>)'>수정</button>
                <button class="ri-btn ri-btn-danger" onclick="deleteIcon(<?php echo $icon['ri_id']; ?>)">삭제</button>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<!-- 추가/수정 모달 -->
<div id="icon-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:1000;">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:#2a2a2a; border:1px solid #555; border-radius:8px; padding:24px; width:400px;">
        <h3 id="modal-title" style="color:#e0e0e0; margin:0 0 16px;">아이콘 추가</h3>
        <input type="hidden" id="m-ri-id">
        <input type="hidden" id="m-action">
        <div style="margin-bottom:12px;">
            <label style="display:block; color:#999; font-size:13px; margin-bottom:4px;">카테고리</label>
            <select id="m-category" class="ri-input" style="width:100%">
                <option value="love">love (애정)</option>
                <option value="friendship">friendship (우정)</option>
                <option value="family">family (가족)</option>
                <option value="rival">rival (적대)</option>
                <option value="mentor">mentor (사제)</option>
                <option value="etc">etc (기타)</option>
            </select>
        </div>
        <div style="margin-bottom:12px;">
            <label style="display:block; color:#999; font-size:13px; margin-bottom:4px;">아이콘 (이모지)</label>
            <input type="text" id="m-icon" class="ri-input" style="width:100%; font-size:18px;" maxlength="20">
        </div>
        <div style="margin-bottom:12px;">
            <label style="display:block; color:#999; font-size:13px; margin-bottom:4px;">표시명</label>
            <input type="text" id="m-label" class="ri-input" style="width:100%;" maxlength="50">
        </div>
        <div style="display:flex; gap:12px; margin-bottom:12px;">
            <div style="flex:1">
                <label style="display:block; color:#999; font-size:13px; margin-bottom:4px;">색상</label>
                <input type="color" id="m-color" value="#95a5a6" style="width:100%; height:32px; border:1px solid #444; background:none; cursor:pointer;">
            </div>
            <div style="flex:1">
                <label style="display:block; color:#999; font-size:13px; margin-bottom:4px;">굵기 (1~5)</label>
                <input type="number" id="m-width" class="ri-input" style="width:100%;" min="1" max="5" value="2">
            </div>
            <div style="flex:1">
                <label style="display:block; color:#999; font-size:13px; margin-bottom:4px;">정렬순</label>
                <input type="number" id="m-order" class="ri-input" style="width:100%;" value="0">
            </div>
        </div>
        <div style="margin-bottom:16px;">
            <label style="color:#999; font-size:13px;">
                <input type="checkbox" id="m-active" checked> 활성
            </label>
        </div>
        <div style="display:flex; justify-content:flex-end; gap:8px;">
            <button class="ri-btn" style="background:#444; color:#ccc;" onclick="closeIconModal()">취소</button>
            <button class="ri-btn ri-btn-primary" onclick="submitIcon()">저장</button>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modal-title').textContent = '아이콘 추가';
    document.getElementById('m-action').value = 'add';
    document.getElementById('m-ri-id').value = '';
    document.getElementById('m-category').value = 'etc';
    document.getElementById('m-icon').value = '';
    document.getElementById('m-label').value = '';
    document.getElementById('m-color').value = '#95a5a6';
    document.getElementById('m-width').value = '2';
    document.getElementById('m-order').value = '0';
    document.getElementById('m-active').checked = true;
    document.getElementById('icon-modal').style.display = '';
}

function openEditModal(icon) {
    document.getElementById('modal-title').textContent = '아이콘 수정';
    document.getElementById('m-action').value = 'update';
    document.getElementById('m-ri-id').value = icon.ri_id;
    document.getElementById('m-category').value = icon.ri_category;
    document.getElementById('m-icon').value = icon.ri_icon;
    document.getElementById('m-label').value = icon.ri_label;
    document.getElementById('m-color').value = icon.ri_color;
    document.getElementById('m-width').value = icon.ri_width;
    document.getElementById('m-order').value = icon.ri_order;
    document.getElementById('m-active').checked = (icon.ri_active == 1);
    document.getElementById('icon-modal').style.display = '';
}

function closeIconModal() {
    document.getElementById('icon-modal').style.display = 'none';
}

function submitIcon() {
    const data = new FormData();
    data.append('icon_action', document.getElementById('m-action').value);
    data.append('ri_id', document.getElementById('m-ri-id').value);
    data.append('ri_category', document.getElementById('m-category').value);
    data.append('ri_icon', document.getElementById('m-icon').value);
    data.append('ri_label', document.getElementById('m-label').value);
    data.append('ri_color', document.getElementById('m-color').value);
    data.append('ri_width', document.getElementById('m-width').value);
    data.append('ri_order', document.getElementById('m-order').value);
    data.append('ri_active', document.getElementById('m-active').checked ? 1 : 0);

    fetch(location.href, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.success) location.reload();
        });
}

function deleteIcon(riId) {
    if (!confirm('이 아이콘을 삭제하시겠습니까?')) return;
    const data = new FormData();
    data.append('icon_action', 'delete');
    data.append('ri_id', riId);
    fetch(location.href, { method: 'POST', body: data })
        .then(r => r.json())
        .then(res => {
            alert(res.message);
            if (res.success) location.reload();
        });
}
</script>

<?php
include_once('./_tail.php');
?>
