<?php
/**
 * Morgan Edition - 몬스터 관리 (목록 + 폼)
 */

$sub_menu = "801910";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// ── POST 처리 ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    auth_check_menu($auth, $sub_menu, 'w');

    $mode = isset($_POST['mode']) ? $_POST['mode'] : '';
    $bm_id = isset($_POST['bm_id']) ? (int)$_POST['bm_id'] : 0;

    if ($mode === 'delete') {
        $ids = isset($_POST['chk']) ? $_POST['chk'] : array();
        if (!empty($ids)) {
            $ids_safe = implode(',', array_map('intval', $ids));
            sql_query("DELETE FROM {$g5['mg_battle_monster_table']} WHERE bm_id IN ({$ids_safe})");
        }
        $cookie_data = json_encode(array('msg' => count($ids) . '개 몬스터가 삭제되었습니다.', 'type' => 'success'));
        setcookie('mg_flash_toast', $cookie_data, time() + 5, '/');
        goto_url('./battle_monster.php');
        exit;
    }

    // add / edit
    $bm_name = isset($_POST['bm_name']) ? trim($_POST['bm_name']) : '';
    if (!$bm_name) { alert('몬스터 이름을 입력해주세요.'); }

    $bm_type = isset($_POST['bm_type']) ? $_POST['bm_type'] : 'mob';
    $bm_hp = max(1, (int)($_POST['bm_hp'] ?? 1000));
    $bm_atk = max(0, (int)($_POST['bm_atk'] ?? 50));
    $bm_def = max(0, (int)($_POST['bm_def'] ?? 10));
    $bm_time_limit = max(60, (int)($_POST['bm_time_limit'] ?? 7200));
    $bm_reward_point = max(0, (int)($_POST['bm_reward_point'] ?? 500));
    $bm_mob_count = max(1, min(5, (int)($_POST['bm_mob_count'] ?? 1)));
    $bm_story_regen_pct = max(0, min(100, (int)($_POST['bm_story_regen_pct'] ?? 5)));
    $bm_use = isset($_POST['bm_use']) ? 1 : 0;
    $bm_order = (int)($_POST['bm_order'] ?? 0);

    // 드랍 테이블 JSON
    $bm_reward_drops = isset($_POST['bm_reward_drops']) ? trim($_POST['bm_reward_drops']) : '[]';
    // 출현 지역 JSON
    $bm_areas = isset($_POST['bm_areas']) ? trim($_POST['bm_areas']) : '[]';

    // 이미지 업로드
    $bm_image = '';
    if ($bm_id > 0) {
        $old = sql_fetch("SELECT bm_image FROM {$g5['mg_battle_monster_table']} WHERE bm_id = {$bm_id}");
        $bm_image = $old ? $old['bm_image'] : '';
    }
    if (isset($_FILES['bm_image_file']) && $_FILES['bm_image_file']['error'] === 0) {
        $upload_dir = G5_DATA_PATH . '/morgan/battle';
        if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
        $ext = strtolower(pathinfo($_FILES['bm_image_file']['name'], PATHINFO_EXTENSION));
        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($ext, $allowed)) {
            $fname = 'monster_' . time() . '_' . mt_rand(1000,9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['bm_image_file']['tmp_name'], $upload_dir . '/' . $fname)) {
                // 이전 이미지 삭제
                if ($bm_image && file_exists(G5_DATA_PATH . '/' . $bm_image)) {
                    @unlink(G5_DATA_PATH . '/' . $bm_image);
                }
                $bm_image = 'morgan/battle/' . $fname;
            }
        }
    }

    $bm_name_esc = sql_real_escape_string($bm_name);
    $bm_type_esc = sql_real_escape_string($bm_type);
    $bm_image_esc = sql_real_escape_string($bm_image);
    $bm_reward_drops_esc = sql_real_escape_string($bm_reward_drops);
    $bm_areas_esc = sql_real_escape_string($bm_areas);

    if ($mode === 'add') {
        sql_query("INSERT INTO {$g5['mg_battle_monster_table']}
            (bm_name, bm_image, bm_type, bm_hp, bm_atk, bm_def, bm_time_limit,
             bm_reward_point, bm_reward_drops, bm_areas, bm_mob_count,
             bm_story_regen_pct, bm_use, bm_order)
            VALUES ('{$bm_name_esc}', '{$bm_image_esc}', '{$bm_type_esc}',
                    {$bm_hp}, {$bm_atk}, {$bm_def}, {$bm_time_limit},
                    {$bm_reward_point}, '{$bm_reward_drops_esc}', '{$bm_areas_esc}',
                    {$bm_mob_count}, {$bm_story_regen_pct}, {$bm_use}, {$bm_order})");
        $msg = '몬스터가 등록되었습니다.';
    } else {
        sql_query("UPDATE {$g5['mg_battle_monster_table']} SET
            bm_name = '{$bm_name_esc}', bm_image = '{$bm_image_esc}', bm_type = '{$bm_type_esc}',
            bm_hp = {$bm_hp}, bm_atk = {$bm_atk}, bm_def = {$bm_def},
            bm_time_limit = {$bm_time_limit}, bm_reward_point = {$bm_reward_point},
            bm_reward_drops = '{$bm_reward_drops_esc}', bm_areas = '{$bm_areas_esc}',
            bm_mob_count = {$bm_mob_count}, bm_story_regen_pct = {$bm_story_regen_pct},
            bm_use = {$bm_use}, bm_order = {$bm_order}
            WHERE bm_id = {$bm_id}");
        $msg = '몬스터가 수정되었습니다.';
    }

    $cookie_data = json_encode(array('msg' => $msg, 'type' => 'success'));
    setcookie('mg_flash_toast', $cookie_data, time() + 5, '/');
    goto_url('./battle_monster.php');
    exit;
}

// ── 탭: list / form ──
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'list';
$bm_id = isset($_GET['bm_id']) ? (int)$_GET['bm_id'] : 0;

$g5['title'] = '몬스터 관리';
include_once('./_head.php');

if ($tab === 'form') {
    // ── 등록/수정 폼 ──
    $item = array(
        'bm_id' => 0, 'bm_name' => '', 'bm_image' => '', 'bm_type' => 'mob',
        'bm_hp' => 1000, 'bm_atk' => 50, 'bm_def' => 10, 'bm_time_limit' => 7200,
        'bm_reward_point' => 500, 'bm_reward_drops' => '[]', 'bm_areas' => '[]',
        'bm_mob_count' => 1, 'bm_story_regen_pct' => 5, 'bm_use' => 1, 'bm_order' => 0
    );
    $is_edit = false;

    if ($bm_id > 0) {
        $loaded = sql_fetch("SELECT * FROM {$g5['mg_battle_monster_table']} WHERE bm_id = {$bm_id}");
        if ($loaded && $loaded['bm_id']) {
            $item = $loaded;
            $is_edit = true;
        }
    }
?>

<div style="margin-bottom:1rem;">
    <a href="./battle_monster.php" class="mg-btn mg-btn-secondary" style="font-size:0.85rem;">&larr; 목록으로</a>
</div>

<form method="post" action="./battle_monster.php" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?php echo $is_edit ? 'edit' : 'add'; ?>">
    <input type="hidden" name="bm_id" value="<?php echo (int)$item['bm_id']; ?>">

    <div class="mg-card">
        <div class="mg-card-header"><?php echo $is_edit ? '몬스터 수정' : '몬스터 등록'; ?></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">이름 *</label>
                    <input type="text" name="bm_name" value="<?php echo htmlspecialchars($item['bm_name'] ?? ''); ?>" class="mg-form-input" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">유형</label>
                    <select name="bm_type" class="mg-form-select" id="bm_type_select">
                        <option value="mob" <?php echo ($item['bm_type'] ?? '') === 'mob' ? 'selected' : ''; ?>>일반 몹 (mob)</option>
                        <option value="boss" <?php echo ($item['bm_type'] ?? '') === 'boss' ? 'selected' : ''; ?>>보스 (boss)</option>
                        <option value="story_boss" <?php echo ($item['bm_type'] ?? '') === 'story_boss' ? 'selected' : ''; ?>>스토리 보스 (story_boss)</option>
                    </select>
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">이미지</label>
                <?php if (!empty($item['bm_image'])) { ?>
                    <div style="margin-bottom:0.5rem;">
                        <img src="<?php echo G5_DATA_URL . '/' . $item['bm_image']; ?>" style="max-width:200px; max-height:200px; border-radius:8px;">
                    </div>
                <?php } ?>
                <input type="file" name="bm_image_file" accept="image/*" class="mg-form-input">
            </div>

            <h4 style="font-size:0.9rem;font-weight:600;margin:1.25rem 0 0.75rem;color:var(--mg-text-secondary);">전투 수치</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">HP</label>
                    <input type="number" name="bm_hp" value="<?php echo (int)($item['bm_hp'] ?? 1000); ?>" class="mg-form-input" min="1">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">ATK</label>
                    <input type="number" name="bm_atk" value="<?php echo (int)($item['bm_atk'] ?? 50); ?>" class="mg-form-input" min="0">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">DEF</label>
                    <input type="number" name="bm_def" value="<?php echo (int)($item['bm_def'] ?? 10); ?>" class="mg-form-input" min="0">
                </div>
            </div>

            <h4 style="font-size:0.9rem;font-weight:600;margin:1.25rem 0 0.75rem;color:var(--mg-text-secondary);">보상 & 규칙</h4>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">제한 시간 (초)</label>
                    <input type="number" name="bm_time_limit" value="<?php echo (int)($item['bm_time_limit'] ?? 7200); ?>" class="mg-form-input" min="60">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">기본 7200초 = 2시간</p>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">보상 포인트</label>
                    <input type="number" name="bm_reward_point" value="<?php echo (int)($item['bm_reward_point'] ?? 500); ?>" class="mg-form-input" min="0">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group" id="mob_count_group">
                    <label class="mg-form-label">등장 수 (mob 전용)</label>
                    <input type="number" name="bm_mob_count" value="<?php echo (int)($item['bm_mob_count'] ?? 1); ?>" class="mg-form-input" min="1" max="5">
                </div>
                <div class="mg-form-group" id="story_regen_group" style="display:none;">
                    <label class="mg-form-label">라운드 간 HP 회복률 (%)</label>
                    <input type="number" name="bm_story_regen_pct" value="<?php echo (int)($item['bm_story_regen_pct'] ?? 5); ?>" class="mg-form-input" min="0" max="100">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">잔여 HP의 N% 회복 (스토리 보스 전용)</p>
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">드랍 테이블 (JSON)</label>
                <textarea name="bm_reward_drops" class="mg-form-textarea" rows="3" style="font-family:monospace;font-size:0.85rem;"><?php echo htmlspecialchars($item['bm_reward_drops'] ?? '[]'); ?></textarea>
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">[{"item_id":101,"chance":30,"count":1}, ...]</p>
            </div>
            <div class="mg-form-group">
                <label class="mg-form-label">출현 지역 (JSON)</label>
                <textarea name="bm_areas" class="mg-form-textarea" rows="2" style="font-family:monospace;font-size:0.85rem;"><?php echo htmlspecialchars($item['bm_areas'] ?? '[]'); ?></textarea>
                <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">[ea_id, ea_id, ...]</p>
            </div>

            <hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1.5rem 0;">

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">정렬 순서</label>
                    <input type="number" name="bm_order" value="<?php echo (int)($item['bm_order'] ?? 0); ?>" class="mg-form-input">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">활성 상태</label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="bm_use" value="1" <?php echo ($item['bm_use'] ?? 1) ? 'checked' : ''; ?>>
                        사용
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '수정' : '등록'; ?></button>
        <a href="./battle_monster.php" class="mg-btn mg-btn-secondary">목록으로</a>
    </div>
</form>

<script>
(function() {
    var typeSelect = document.getElementById('bm_type_select');
    var mobGroup = document.getElementById('mob_count_group');
    var storyGroup = document.getElementById('story_regen_group');
    function toggleFields() {
        var v = typeSelect.value;
        mobGroup.style.display = v === 'mob' ? '' : 'none';
        storyGroup.style.display = v === 'story_boss' ? '' : 'none';
    }
    typeSelect.addEventListener('change', toggleFields);
    toggleFields();
})();
</script>

<?php
} else {
    // ── 목록 ──
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $rows = 20;
    $offset = ($page - 1) * $rows;

    $sfl = isset($_GET['sfl']) ? clean_xss_tags($_GET['sfl']) : '';
    $stx = isset($_GET['stx']) ? clean_xss_tags($_GET['stx']) : '';
    $type_filter = isset($_GET['bm_type']) ? clean_xss_tags($_GET['bm_type']) : '';

    $where = "WHERE 1=1";
    if ($type_filter) $where .= " AND bm_type = '" . sql_real_escape_string($type_filter) . "'";
    if ($stx) $where .= " AND bm_name LIKE '%" . sql_real_escape_string($stx) . "%'";

    $total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_battle_monster_table']} {$where}");
    $total_count = (int)$total['cnt'];
    $total_page = $total_count > 0 ? (int)ceil($total_count / $rows) : 1;

    $result = sql_query("SELECT * FROM {$g5['mg_battle_monster_table']} {$where}
                         ORDER BY bm_order DESC, bm_id DESC LIMIT {$offset}, {$rows}");
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; flex-wrap:wrap; gap:0.5rem;">
    <div style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
        <select onchange="location.href='./battle_monster.php?bm_type='+this.value" class="mg-form-select" style="width:150px;">
            <option value="">전체 유형</option>
            <option value="mob" <?php echo $type_filter === 'mob' ? 'selected' : ''; ?>>일반 몹</option>
            <option value="boss" <?php echo $type_filter === 'boss' ? 'selected' : ''; ?>>보스</option>
            <option value="story_boss" <?php echo $type_filter === 'story_boss' ? 'selected' : ''; ?>>스토리 보스</option>
        </select>
        <form method="get" action="./battle_monster.php" style="display:flex; gap:0.25rem;">
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" placeholder="이름 검색" class="mg-form-input" style="width:160px;">
            <button type="submit" class="mg-btn mg-btn-secondary" style="font-size:0.85rem;">검색</button>
        </form>
    </div>
    <a href="./battle_monster.php?tab=form" class="mg-btn mg-btn-primary" style="font-size:0.85rem;">+ 몬스터 등록</a>
</div>

<form method="post" action="./battle_monster.php" id="fmonsterlist">
    <input type="hidden" name="mode" value="delete">
    <div class="mg-card">
        <div class="mg-card-body" style="padding:0; overflow-x:auto;">
            <table class="mg-table">
                <thead>
                    <tr>
                        <th style="width:40px;"><input type="checkbox" onclick="var c=this.form.querySelectorAll('input[name=\'chk[]\']');for(var i=0;i<c.length;i++)c[i].checked=this.checked;"></th>
                        <th>ID</th>
                        <th>이름</th>
                        <th>유형</th>
                        <th>HP</th>
                        <th>ATK</th>
                        <th>DEF</th>
                        <th>보상</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $type_labels = array('mob' => '몹', 'boss' => '보스', 'story_boss' => '스토리');
                while ($row = sql_fetch_array($result)) {
                    $type_label = isset($type_labels[$row['bm_type']]) ? $type_labels[$row['bm_type']] : $row['bm_type'];
                ?>
                    <tr>
                        <td><input type="checkbox" name="chk[]" value="<?php echo $row['bm_id']; ?>"></td>
                        <td><?php echo $row['bm_id']; ?></td>
                        <td>
                            <?php if ($row['bm_image']) { ?>
                                <img src="<?php echo G5_DATA_URL . '/' . $row['bm_image']; ?>" style="width:32px;height:32px;object-fit:cover;border-radius:4px;vertical-align:middle;margin-right:0.5rem;">
                            <?php } ?>
                            <?php echo htmlspecialchars($row['bm_name']); ?>
                        </td>
                        <td><?php echo $type_label; ?></td>
                        <td><?php echo number_format($row['bm_hp']); ?></td>
                        <td><?php echo $row['bm_atk']; ?></td>
                        <td><?php echo $row['bm_def']; ?></td>
                        <td><?php echo number_format($row['bm_reward_point']); ?>P</td>
                        <td><?php echo $row['bm_use'] ? '<span style="color:#22c55e;">ON</span>' : '<span style="color:#ef4444;">OFF</span>'; ?></td>
                        <td><a href="./battle_monster.php?tab=form&bm_id=<?php echo $row['bm_id']; ?>" class="mg-btn mg-btn-sm mg-btn-secondary">수정</a></td>
                    </tr>
                <?php } ?>
                <?php if ($total_count === 0) { ?>
                    <tr><td colspan="10" style="text-align:center; padding:2rem; color:var(--mg-text-muted);">등록된 몬스터가 없습니다.</td></tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if ($total_count > 0) { ?>
    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:1rem;">
        <button type="submit" class="mg-btn mg-btn-danger mg-btn-sm" onclick="return confirm('선택한 몬스터를 삭제하시겠습니까?');">선택 삭제</button>
        <div style="display:flex; gap:0.25rem; align-items:center;">
            <?php if ($page > 1) { ?><a href="./battle_monster.php?page=<?php echo $page-1; ?>&bm_type=<?php echo $type_filter; ?>&stx=<?php echo urlencode($stx); ?>" class="mg-btn mg-btn-sm mg-btn-secondary">&laquo;</a><?php } ?>
            <span style="font-size:0.85rem; color:var(--mg-text-muted);"><?php echo $page; ?> / <?php echo $total_page; ?></span>
            <?php if ($page < $total_page) { ?><a href="./battle_monster.php?page=<?php echo $page+1; ?>&bm_type=<?php echo $type_filter; ?>&stx=<?php echo urlencode($stx); ?>" class="mg-btn mg-btn-sm mg-btn-secondary">&raquo;</a><?php } ?>
        </div>
    </div>
    <?php } ?>
</form>

<?php
}

include_once('./_tail.php');
