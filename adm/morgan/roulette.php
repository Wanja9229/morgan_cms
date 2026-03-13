<?php
/**
 * Morgan Edition - 관리자 룰렛 관리
 */
$sub_menu = '800960';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r');
include_once(G5_PATH.'/plugin/morgan/morgan.php');
include_once(G5_PATH.'/plugin/morgan/roulette.php');

$token = get_token();
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'list';
$rp_id = isset($_GET['rp_id']) ? (int)$_GET['rp_id'] : 0;

// ── POST 처리 ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_token();

    $action = $_POST['action'] ?? '';

    // 설정 저장
    if ($action === 'config') {
        $config_keys = array('roulette_use', 'roulette_cost', 'roulette_daily_limit', 'roulette_cooldown',
            'roulette_board', 'roulette_transfer_reveal', 'roulette_pending_hours');
        foreach ($config_keys as $key) {
            if (isset($_POST[$key])) {
                mg_set_config($key, $_POST[$key]);
            }
        }
        if (isset($_POST['reset_pool']) && $_POST['reset_pool'] == '1') {
            mg_set_config('roulette_jackpot_pool', '0');
        }
        goto_url('./roulette.php?mode=config&saved=1');
    }

    // 항목 저장
    if ($action === 'save') {
        auth_check_menu($auth, $sub_menu, 'w');
        $rp_id = (int)($_POST['rp_id'] ?? 0);
        $rp_name = trim($_POST['rp_name'] ?? '');
        $rp_desc = trim($_POST['rp_desc'] ?? '');
        $rp_type = $_POST['rp_type'] ?? 'blank';
        $rp_icon = trim($_POST['rp_icon'] ?? '');
        $rp_color = $_POST['rp_color'] ?? '#6b7280';
        $rp_reward_type = $_POST['rp_reward_type'] ?? 'none';
        $rp_reward_value = trim($_POST['rp_reward_value'] ?? '{}');
        $rp_duration_hours = (int)($_POST['rp_duration_hours'] ?? 0);
        $rp_require_log = isset($_POST['rp_require_log']) ? 1 : 0;
        $rp_weight = max(1, (int)($_POST['rp_weight'] ?? 10));
        $rp_order = (int)($_POST['rp_order'] ?? 0);
        $rp_use = isset($_POST['rp_use']) ? 1 : 0;

        if (!$rp_name) {
            alert('이름을 입력해주세요.');
        }

        // 이미지 업로드 (벌칙 프사)
        $rp_image = '';
        if ($rp_id) {
            $old = sql_fetch("SELECT rp_image FROM {$g5['mg_roulette_prize_table']} WHERE rp_id = {$rp_id}");
            $rp_image = $old ? ($old['rp_image'] ?? '') : '';
        }
        if (isset($_FILES['rp_image_file']) && $_FILES['rp_image_file']['error'] === 0) {
            $upload_dir = G5_DATA_PATH . '/morgan/roulette';
            if (!is_dir($upload_dir)) @mkdir($upload_dir, 0755, true);
            $ext = strtolower(pathinfo($_FILES['rp_image_file']['name'], PATHINFO_EXTENSION));
            $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
            if (in_array($ext, $allowed)) {
                $fname = 'penalty_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                if (move_uploaded_file($_FILES['rp_image_file']['tmp_name'], $upload_dir . '/' . $fname)) {
                    if ($rp_image && file_exists(G5_DATA_PATH . '/' . $rp_image)) {
                        @unlink(G5_DATA_PATH . '/' . $rp_image);
                    }
                    $rp_image = 'morgan/roulette/' . $fname;
                }
            }
        }

        $name_esc = sql_real_escape_string($rp_name);
        $desc_esc = sql_real_escape_string($rp_desc);
        $icon_esc = sql_real_escape_string($rp_icon);
        $color_esc = sql_real_escape_string($rp_color);
        $rv_esc = sql_real_escape_string($rp_reward_value);
        $img_esc = sql_real_escape_string($rp_image);

        if ($rp_id) {
            sql_query("UPDATE {$g5['mg_roulette_prize_table']} SET
                rp_name = '{$name_esc}', rp_desc = '{$desc_esc}', rp_type = '{$rp_type}',
                rp_icon = '{$icon_esc}', rp_image = '{$img_esc}', rp_color = '{$color_esc}',
                rp_reward_type = '{$rp_reward_type}', rp_reward_value = '{$rv_esc}',
                rp_duration_hours = {$rp_duration_hours}, rp_require_log = {$rp_require_log},
                rp_weight = {$rp_weight}, rp_order = {$rp_order}, rp_use = {$rp_use}
                WHERE rp_id = {$rp_id}");
        } else {
            sql_query("INSERT INTO {$g5['mg_roulette_prize_table']}
                (rp_name, rp_desc, rp_type, rp_icon, rp_image, rp_color, rp_reward_type, rp_reward_value,
                 rp_duration_hours, rp_require_log, rp_weight, rp_order, rp_use)
                VALUES ('{$name_esc}', '{$desc_esc}', '{$rp_type}', '{$icon_esc}', '{$img_esc}', '{$color_esc}',
                        '{$rp_reward_type}', '{$rv_esc}', {$rp_duration_hours}, {$rp_require_log},
                        {$rp_weight}, {$rp_order}, {$rp_use})");
        }
        goto_url('./roulette.php?saved=1');
    }

    // 삭제
    if ($action === 'delete') {
        auth_check_menu($auth, $sub_menu, 'd');
        $ids = $_POST['chk'] ?? array();
        foreach ($ids as $id) {
            $id = (int)$id;
            if ($id) sql_query("DELETE FROM {$g5['mg_roulette_prize_table']} WHERE rp_id = {$id}");
        }
        goto_url('./roulette.php?deleted=1');
    }
}

include_once('./_head.php');

if (isset($_GET['saved'])) echo '<script>if(typeof mgToast==="function") mgToast("저장되었습니다.","success");</script>';
if (isset($_GET['deleted'])) echo '<script>if(typeof mgToast==="function") mgToast("삭제되었습니다.","success");</script>';

$type_labels = array('reward' => '보상', 'penalty' => '벌칙', 'blank' => '꽝', 'jackpot' => '잭팟');
$reward_type_labels = array('point' => '포인트', 'material' => '재료', 'item' => '아이템', 'title' => '칭호',
    'nickname' => '닉변', 'profile_image' => '프사 변경', 'log' => '로그 제출',
    'log_nickname' => '닉변+로그', 'log_image' => '프사+로그', 'none' => '없음');
?>

<div class="mg-card">
    <div class="mg-card-header">
        <h2 class="mg-card-title">룰렛 관리</h2>
        <div class="flex gap-2">
            <a href="./roulette.php" class="mg-btn <?php echo $mode === 'list' ? 'mg-btn-primary' : ''; ?>">항목 목록</a>
            <a href="./roulette.php?mode=form" class="mg-btn <?php echo $mode === 'form' ? 'mg-btn-primary' : ''; ?>">항목 등록</a>
            <a href="./roulette.php?mode=config" class="mg-btn <?php echo $mode === 'config' ? 'mg-btn-primary' : ''; ?>">설정</a>
        </div>
    </div>

    <div class="mg-card-body">

    <?php if ($mode === 'config') { ?>
    <!-- 설정 -->
    <form method="post" action="./roulette.php">
        <input type="hidden" name="action" value="config">
        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <div class="mg-form-group">
            <label class="mg-form-label">룰렛 활성화</label>
            <select name="roulette_use" class="mg-form-select">
                <option value="0" <?php echo mg_config('roulette_use', '0') == '0' ? 'selected' : ''; ?>>비활성</option>
                <option value="1" <?php echo mg_config('roulette_use', '0') == '1' ? 'selected' : ''; ?>>활성</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">1회 비용 (포인트)</label>
            <input type="number" name="roulette_cost" value="<?php echo mg_config('roulette_cost', '100'); ?>" class="mg-form-input" min="0">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">1일 제한 횟수 (0=무제한)</label>
            <input type="number" name="roulette_daily_limit" value="<?php echo mg_config('roulette_daily_limit', '3'); ?>" class="mg-form-input" min="0">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">쿨다운 (분, 0=없음)</label>
            <input type="number" name="roulette_cooldown" value="<?php echo mg_config('roulette_cooldown', '0'); ?>" class="mg-form-input" min="0">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">벌칙 로그 게시판 (bo_table)</label>
            <input type="text" name="roulette_board" value="<?php echo htmlspecialchars(mg_config('roulette_board', 'roulette')); ?>" class="mg-form-input">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">떠넘기기 보낸 사람 공개</label>
            <select name="roulette_transfer_reveal" class="mg-form-select">
                <option value="0" <?php echo mg_config('roulette_transfer_reveal', '0') == '0' ? 'selected' : ''; ?>>익명</option>
                <option value="1" <?php echo mg_config('roulette_transfer_reveal', '0') == '1' ? 'selected' : ''; ?>>공개</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">미확인 벌칙 자동 확정 (시간)</label>
            <input type="number" name="roulette_pending_hours" value="<?php echo mg_config('roulette_pending_hours', '24'); ?>" class="mg-form-input" min="1">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">현재 잭팟 풀</label>
            <p class="mg-form-input" style="background:transparent;"><?php echo number_format((int)mg_config('roulette_jackpot_pool', '0')); ?> P</p>
            <label class="mt-2"><input type="checkbox" name="reset_pool" value="1"> 잭팟 풀 리셋 (0으로 초기화)</label>
        </div>

        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </form>

    <?php } elseif ($mode === 'form') { ?>
    <!-- 등록/수정 폼 -->
    <?php
    $item = array('rp_id' => 0, 'rp_name' => '', 'rp_desc' => '', 'rp_type' => 'blank', 'rp_icon' => '',
        'rp_image' => '', 'rp_color' => '#6b7280', 'rp_reward_type' => 'none', 'rp_reward_value' => '{}',
        'rp_duration_hours' => 0, 'rp_require_log' => 0, 'rp_weight' => 10, 'rp_order' => 0, 'rp_use' => 1);
    if ($rp_id) {
        $row = sql_fetch("SELECT * FROM {$g5['mg_roulette_prize_table']} WHERE rp_id = {$rp_id}");
        if ($row) $item = $row;
    }
    ?>
    <form method="post" action="./roulette.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="rp_id" value="<?php echo $item['rp_id']; ?>">
        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <div class="mg-form-group">
            <label class="mg-form-label">이름 *</label>
            <input type="text" name="rp_name" value="<?php echo htmlspecialchars($item['rp_name'] ?? ''); ?>" class="mg-form-input" required>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">설명</label>
            <textarea name="rp_desc" class="mg-form-input" rows="3"><?php echo htmlspecialchars($item['rp_desc'] ?? ''); ?></textarea>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">유형</label>
            <select name="rp_type" class="mg-form-select">
                <?php foreach ($type_labels as $k => $v) { ?>
                <option value="<?php echo $k; ?>" <?php echo ($item['rp_type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">아이콘 (Lucide 아이콘명)</label>
            <div style="display:flex;gap:8px;align-items:center;">
                <input type="text" name="rp_icon" value="<?php echo htmlspecialchars($item['rp_icon'] ?? ''); ?>" class="mg-form-input" style="flex:1;" placeholder="예: coins, gem, star, flame" id="rp_icon_input">
                <span id="rp_icon_preview" style="font-size:1.2rem;width:32px;text-align:center;"><i data-lucide="<?php echo htmlspecialchars($item['rp_icon'] ?? 'help-circle'); ?>" style="width:24px;height:24px;"></i></span>
            </div>
            <p class="text-xs text-mg-text-muted mt-1">
                <a href="https://lucide.dev/icons/" target="_blank" style="color:var(--mg-accent);">아이콘 목록 보기 &rarr;</a>
            </p>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">룰렛 칸 색상</label>
            <input type="color" name="rp_color" value="<?php echo htmlspecialchars($item['rp_color'] ?? '#6b7280'); ?>" class="mg-form-input" style="width:80px;height:36px;padding:2px;">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">보상/벌칙 유형</label>
            <select name="rp_reward_type" class="mg-form-select">
                <?php foreach ($reward_type_labels as $k => $v) { ?>
                <option value="<?php echo $k; ?>" <?php echo ($item['rp_reward_type'] ?? '') === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">보상 값 (JSON)</label>
            <textarea name="rp_reward_value" class="mg-form-input" rows="2" placeholder='{"amount":100} / {"nickname":"이름 모를 고양이"}'><?php echo htmlspecialchars($item['rp_reward_value'] ?? '{}'); ?></textarea>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">벌칙 프사 이미지</label>
            <?php if (!empty($item['rp_image'])) { ?>
            <div class="mb-2"><img src="<?php echo G5_DATA_URL . '/' . $item['rp_image']; ?>" style="max-width:100px;max-height:100px;border-radius:8px;"></div>
            <?php } ?>
            <input type="file" name="rp_image_file" accept="image/*" class="mg-form-input">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">지속 시간 (시간, 0=없음)</label>
            <input type="number" name="rp_duration_hours" value="<?php echo (int)($item['rp_duration_hours'] ?? 0); ?>" class="mg-form-input" min="0">
        </div>
        <div class="mg-form-group">
            <label><input type="checkbox" name="rp_require_log" value="1" <?php echo ($item['rp_require_log'] ?? 0) ? 'checked' : ''; ?>> 벌칙 로그 제출 필요</label>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">가중치 (확률)</label>
            <input type="number" name="rp_weight" value="<?php echo (int)($item['rp_weight'] ?? 10); ?>" class="mg-form-input" min="1">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">정렬 순서</label>
            <input type="number" name="rp_order" value="<?php echo (int)($item['rp_order'] ?? 0); ?>" class="mg-form-input">
        </div>
        <div class="mg-form-group">
            <label><input type="checkbox" name="rp_use" value="1" <?php echo ($item['rp_use'] ?? 1) ? 'checked' : ''; ?>> 사용</label>
        </div>

        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $item['rp_id'] ? '수정' : '등록'; ?></button>
        <?php if ($item['rp_id']) { ?>
        <a href="./roulette.php" class="mg-btn">목록으로</a>
        <?php } ?>
    </form>

    <?php } else { ?>
    <!-- 목록 -->
    <?php
    $result = sql_query("SELECT * FROM {$g5['mg_roulette_prize_table']} ORDER BY rp_order, rp_id");
    $items = array();
    if ($result !== false) {
        while ($row = sql_fetch_array($result)) $items[] = $row;
    }
    $total_weight = 0;
    foreach ($items as $it) $total_weight += (int)$it['rp_weight'];
    ?>
    <form method="post" action="./roulette.php">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="token" value="<?php echo $token; ?>">

        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:30px"><input type="checkbox" onclick="var c=this.checked;document.querySelectorAll('input[name=\\'chk[]\\']').forEach(function(e){e.checked=c;})"></th>
                    <th>ID</th>
                    <th>아이콘</th>
                    <th>이름</th>
                    <th>유형</th>
                    <th>보상</th>
                    <th>가중치</th>
                    <th>확률</th>
                    <th>사용</th>
                    <th>관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)) { ?>
                <tr><td colspan="10" class="text-center" style="padding:2rem;">등록된 항목이 없습니다.</td></tr>
                <?php } ?>
                <?php foreach ($items as $it) {
                    $pct = $total_weight > 0 ? round((int)$it['rp_weight'] / $total_weight * 100, 2) : 0;
                ?>
                <tr>
                    <td><input type="checkbox" name="chk[]" value="<?php echo $it['rp_id']; ?>"></td>
                    <td><?php echo $it['rp_id']; ?></td>
                    <td style="text-align:center;"><i data-lucide="<?php echo htmlspecialchars($it['rp_icon'] ?? 'help-circle'); ?>" style="width:20px;height:20px;color:<?php echo htmlspecialchars($it['rp_color'] ?? '#6b7280'); ?>;"></i></td>
                    <td><?php echo htmlspecialchars($it['rp_name'] ?? ''); ?></td>
                    <td><?php echo $type_labels[$it['rp_type']] ?? $it['rp_type']; ?></td>
                    <td><?php echo $reward_type_labels[$it['rp_reward_type']] ?? $it['rp_reward_type']; ?></td>
                    <td><?php echo $it['rp_weight']; ?></td>
                    <td><?php echo $pct; ?>%</td>
                    <td><?php echo $it['rp_use'] ? '<span style="color:#22c55e;">ON</span>' : '<span style="color:#ef4444;">OFF</span>'; ?></td>
                    <td><a href="./roulette.php?mode=form&rp_id=<?php echo $it['rp_id']; ?>" class="mg-btn mg-btn-sm">수정</a></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (!empty($items)) { ?>
        <div class="mt-3">
            <button type="submit" class="mg-btn mg-btn-danger" onclick="return confirm('선택한 항목을 삭제하시겠습니까?');">선택 삭제</button>
        </div>
        <?php } ?>
    </form>
    <?php } ?>

    </div>
</div>

<?php
include_once('./_tail.php');
