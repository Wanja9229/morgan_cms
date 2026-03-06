<?php
/**
 * Morgan Edition - 히든 이벤트 관리
 */
$sub_menu = '801410';
require_once __DIR__.'/../_common.php';
auth_check_menu($auth, $sub_menu, 'r');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'list';
if (!in_array($tab, array('list','edit','log'))) $tab = 'list';

// 재료 목록 (추가/수정 폼용)
$materials = array();
$result = sql_query("SELECT mt_id, mt_name FROM {$g5['mg_material_type_table']} ORDER BY mt_name ASC");
if ($result) while ($row = sql_fetch_array($result)) $materials[] = $row;

$g5['title'] = '히든 이벤트 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 탭 바 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=list" class="mg-tab <?php echo $tab == 'list' ? 'active' : ''; ?>">이벤트 목록</a>
    <a href="?tab=edit" class="mg-tab <?php echo $tab == 'edit' ? 'active' : ''; ?>">이벤트 <?php echo isset($_GET['event_id']) ? '수정' : '추가'; ?></a>
    <a href="?tab=log" class="mg-tab <?php echo $tab == 'log' ? 'active' : ''; ?>">수령 로그</a>
</div>

<?php if ($tab == 'list') { ?>
<!-- ====== 이벤트 목록 ====== -->
<?php
$events = array();
$result = sql_query("SELECT * FROM {$g5['mg_hidden_event_table']} ORDER BY event_id DESC");
if ($result) while ($row = sql_fetch_array($result)) $events[] = $row;
?>

<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <span>이벤트 목록 (<?php echo count($events); ?>개)</span>
        <a href="?tab=edit" class="mg-btn mg-btn-primary mg-btn-sm">이벤트 추가</a>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <?php if (empty($events)) { ?>
        <div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">등록된 이벤트가 없습니다.</div>
        <?php } else { ?>
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:60px;">이미지</th>
                    <th>제목</th>
                    <th style="width:100px;">보상</th>
                    <th style="width:70px;">확률</th>
                    <th style="width:100px;">일일한도</th>
                    <th style="width:140px;">기간</th>
                    <th style="width:60px;">상태</th>
                    <th style="width:140px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $ev) {
                    if ($ev['reward_type'] === 'point') $reward_label = $ev['reward_amount'] . 'P';
                    elseif ($ev['reward_type'] === 'stamina') $reward_label = $ev['reward_amount'] . ' 스태미나';
                    else $reward_label = $ev['reward_amount'] . '개 (재료)';
                ?>
                <tr style="<?php echo !$ev['is_active'] ? 'opacity:0.5;' : ''; ?>">
                    <td>
                        <?php if ($ev['image_path']) { ?>
                        <img src="<?php echo $ev['image_path']; ?>" style="width:48px;height:48px;object-fit:contain;border-radius:4px;background:var(--mg-bg-tertiary);">
                        <?php } ?>
                    </td>
                    <td><a href="?tab=edit&event_id=<?php echo $ev['event_id']; ?>" style="color:var(--mg-accent);"><?php echo htmlspecialchars($ev['title']); ?></a></td>
                    <td><?php echo $reward_label; ?></td>
                    <td><?php echo $ev['probability']; ?>%</td>
                    <td><?php echo $ev['daily_limit']; ?>/<?php echo $ev['daily_total']; ?></td>
                    <td style="font-size:0.75rem;">
                        <?php
                        if ($ev['active_from'] || $ev['active_until']) {
                            echo ($ev['active_from'] ? substr($ev['active_from'], 5, 11) : '~') . '<br>' . ($ev['active_until'] ? '~ ' . substr($ev['active_until'], 5, 11) : '');
                        } else {
                            echo '<span style="color:var(--mg-text-muted);">무제한</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <form method="post" action="./hidden_event_update.php" style="display:inline;">

                            <input type="hidden" name="action" value="toggle_event">
                            <input type="hidden" name="event_id" value="<?php echo $ev['event_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm <?php echo $ev['is_active'] ? 'mg-btn-primary' : ''; ?>" style="<?php echo !$ev['is_active'] ? 'background:var(--mg-bg-tertiary);color:var(--mg-text-muted);border:1px solid var(--mg-text-muted);' : ''; ?>"><?php echo $ev['is_active'] ? 'ON' : 'OFF'; ?></button>
                        </form>
                    </td>
                    <td>
                        <a href="?tab=edit&event_id=<?php echo $ev['event_id']; ?>" class="mg-btn mg-btn-sm mg-btn-secondary">수정</a>
                        <form method="post" action="./hidden_event_update.php" style="display:inline;" onsubmit="return confirm('삭제하시겠습니까?');">

                            <input type="hidden" name="action" value="delete_event">
                            <input type="hidden" name="event_id" value="<?php echo $ev['event_id']; ?>">
                            <button type="submit" class="mg-btn mg-btn-sm mg-btn-danger">삭제</button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
</div>

<?php } elseif ($tab == 'edit') { ?>
<!-- ====== 이벤트 추가/수정 ====== -->
<?php
$event_id = (int)($_GET['event_id'] ?? 0);
$ev = null;
if ($event_id > 0) {
    $ev = sql_fetch("SELECT * FROM {$g5['mg_hidden_event_table']} WHERE event_id = {$event_id}");
}
$is_edit = $ev ? true : false;
?>

<form method="post" action="./hidden_event_update.php" enctype="multipart/form-data">
    <input type="hidden" name="action" value="<?php echo $is_edit ? 'edit_event' : 'add_event'; ?>">
    <?php if ($is_edit) { ?>
    <input type="hidden" name="event_id" value="<?php echo $ev['event_id']; ?>">
    <?php } ?>

    <div class="mg-card">
        <div class="mg-card-header"><?php echo $is_edit ? '이벤트 수정' : '이벤트 추가'; ?></div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label">제목</label>
                <input type="text" name="title" class="mg-form-input" value="<?php echo htmlspecialchars($ev['title'] ?? ''); ?>" required>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">이미지<?php echo $is_edit ? ' (변경 시에만 업로드)' : ''; ?></label>
                <?php if ($is_edit && $ev['image_path']) { ?>
                <div style="margin-bottom:8px;">
                    <img src="<?php echo $ev['image_path']; ?>" style="width:80px;height:80px;object-fit:contain;border-radius:6px;background:var(--mg-bg-tertiary);padding:4px;">
                </div>
                <?php } ?>
                <input type="file" name="event_image" class="mg-form-input" accept="image/*" <?php echo $is_edit ? '' : 'required'; ?>>
                <p class="mg-form-help">투명 배경 권장 (PNG/GIF)</p>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">보상 타입</label>
                <select name="reward_type" class="mg-form-select" onchange="toggleRewardType(this.value)">
                    <option value="point" <?php echo ($ev['reward_type'] ?? 'point') == 'point' ? 'selected' : ''; ?>>포인트</option>
                    <option value="material" <?php echo ($ev['reward_type'] ?? '') == 'material' ? 'selected' : ''; ?>>재료</option>
                    <option value="stamina" <?php echo ($ev['reward_type'] ?? '') == 'stamina' ? 'selected' : ''; ?>>스태미나</option>
                </select>
            </div>

            <div id="reward-material" style="<?php echo ($ev['reward_type'] ?? 'point') != 'material' ? 'display:none;' : ''; ?>">
                <div class="mg-form-group">
                    <label class="mg-form-label">재료 종류</label>
                    <select name="reward_id" class="mg-form-select">
                        <?php foreach ($materials as $mt) { ?>
                        <option value="<?php echo $mt['mt_id']; ?>" <?php echo ($ev['reward_id'] ?? 0) == $mt['mt_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($mt['mt_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">보상량</label>
                <input type="number" name="reward_amount" class="mg-form-input" value="<?php echo (int)($ev['reward_amount'] ?? 100); ?>" min="1" style="width:150px;">
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">출현 확률 (%)</label>
                <input type="number" name="probability" class="mg-form-input" value="<?php echo $ev['probability'] ?? '5.00'; ?>" min="0.01" max="100" step="0.01" style="width:150px;">
                <p class="mg-form-help">페이지 전환 시 이 확률로 이벤트가 출현합니다.</p>
            </div>

            <div style="display:flex;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">이벤트별 일일 한도</label>
                    <input type="number" name="daily_limit" class="mg-form-input" value="<?php echo (int)($ev['daily_limit'] ?? 1); ?>" min="1" style="width:100px;">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">전체 일일 한도</label>
                    <input type="number" name="daily_total" class="mg-form-input" value="<?php echo (int)($ev['daily_total'] ?? 5); ?>" min="1" style="width:100px;">
                    <p class="mg-form-help">유저당 하루 최대 수령 횟수</p>
                </div>
            </div>

            <div style="display:flex;gap:1rem;">
                <div class="mg-form-group" style="flex:1;">
                    <label class="mg-form-label">시작일 (비워두면 즉시)</label>
                    <input type="datetime-local" name="active_from" class="mg-form-input" value="<?php echo $ev['active_from'] ? date('Y-m-d\TH:i', strtotime($ev['active_from'])) : ''; ?>">
                </div>
                <div class="mg-form-group" style="flex:1;">
                    <label class="mg-form-label">종료일 (비워두면 무제한)</label>
                    <input type="datetime-local" name="active_until" class="mg-form-input" value="<?php echo $ev['active_until'] ? date('Y-m-d\TH:i', strtotime($ev['active_until'])) : ''; ?>">
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">활성 상태</label>
                <label class="mg-switch">
                    <input type="checkbox" name="is_active" value="1" <?php echo (!$is_edit || $ev['is_active']) ? 'checked' : ''; ?>>
                    <span class="mg-switch-slider"></span>
                </label>
            </div>
        </div>
    </div>

    <div style="margin-top:1rem;display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary"><?php echo $is_edit ? '수정 저장' : '이벤트 추가'; ?></button>
        <a href="?tab=list" class="mg-btn mg-btn-secondary">취소</a>
    </div>
</form>

<script>
function toggleRewardType(type) {
    document.getElementById('reward-material').style.display = type === 'material' ? '' : 'none';
}
</script>

<?php } elseif ($tab == 'log') { ?>
<!-- ====== 수령 로그 ====== -->
<?php
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'today';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 30;

switch ($filter) {
    case '7d': $where_date = "AND c.claimed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"; break;
    case '30d': $where_date = "AND c.claimed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"; break;
    case 'all': $where_date = ""; break;
    default: $filter = 'today'; $where_date = "AND DATE(c.claimed_at) = CURDATE()"; break;
}

$total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_event_claim_table']} c WHERE 1 {$where_date}");
$total = (int)$total_row['cnt'];
$total_pages = max(1, ceil($total / $per_page));
$offset = ($page - 1) * $per_page;

$logs = array();
$result = sql_query("SELECT c.*, e.title as event_title
    FROM {$g5['mg_event_claim_table']} c
    LEFT JOIN {$g5['mg_hidden_event_table']} e ON c.event_id = e.event_id
    WHERE 1 {$where_date}
    ORDER BY c.claim_id DESC
    LIMIT {$offset}, {$per_page}");
if ($result) while ($row = sql_fetch_array($result)) $logs[] = $row;
?>

<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <span>수령 로그 (<?php echo $total; ?>건)</span>
        <div style="display:flex;gap:4px;">
            <?php foreach (array('today'=>'오늘', '7d'=>'7일', '30d'=>'30일', 'all'=>'전체') as $fk => $fl) { ?>
            <a href="?tab=log&filter=<?php echo $fk; ?>" class="mg-btn mg-btn-sm <?php echo $filter == $fk ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>"><?php echo $fl; ?></a>
            <?php } ?>
        </div>
    </div>
    <div class="mg-card-body" style="padding:0;">
        <?php if (empty($logs)) { ?>
        <div style="padding:2rem;text-align:center;color:var(--mg-text-muted);">수령 기록이 없습니다.</div>
        <?php } else { ?>
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:140px;">일시</th>
                    <th style="width:120px;">유저</th>
                    <th>이벤트</th>
                    <th style="width:80px;">타입</th>
                    <th style="width:80px;">보상량</th>
                    <th style="width:120px;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) { ?>
                <tr>
                    <td style="font-size:0.8rem;"><?php echo substr($log['claimed_at'], 0, 16); ?></td>
                    <td><?php echo htmlspecialchars($log['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['event_title'] ?? '(삭제됨)'); ?></td>
                    <td><?php echo $log['reward_type'] === 'point' ? '포인트' : ($log['reward_type'] === 'stamina' ? '스태미나' : '재료'); ?></td>
                    <td><?php echo number_format($log['reward_amount']); ?></td>
                    <td style="font-size:0.75rem;color:var(--mg-text-muted);"><?php echo htmlspecialchars($log['ip_address'] ?? ''); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php if ($total_pages > 1) { ?>
        <div style="padding:0.75rem;border-top:1px solid var(--mg-bg-tertiary);display:flex;justify-content:center;gap:4px;">
            <?php for ($p = 1; $p <= $total_pages; $p++) { ?>
            <a href="?tab=log&filter=<?php echo $filter; ?>&page=<?php echo $p; ?>" class="mg-btn mg-btn-sm <?php echo $p == $page ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>"><?php echo $p; ?></a>
            <?php } ?>
        </div>
        <?php } ?>
        <?php } ?>
    </div>
</div>

<?php } ?>

<?php include_once('./_tail.php'); ?>
