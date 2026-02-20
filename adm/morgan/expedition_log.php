<?php
/**
 * Morgan Edition - 파견 로그
 */

$sub_menu = "801120";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 파라미터
$search_mb_id = isset($_GET['mb_id']) ? clean_xss_tags($_GET['mb_id']) : '';
$search_status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : '';
$search_area = isset($_GET['ea_id']) ? (int)$_GET['ea_id'] : 0;

// 페이지네이션
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 30;
$offset = ($page - 1) * $per_page;

// WHERE 조건
$where = "1=1";
if ($search_mb_id) {
    $search_mb_id_esc = sql_real_escape_string($search_mb_id);
    $where .= " AND (el.mb_id LIKE '%{$search_mb_id_esc}%' OR m.mb_nick LIKE '%{$search_mb_id_esc}%')";
}
if ($search_status) {
    $search_status_esc = sql_real_escape_string($search_status);
    $where .= " AND el.el_status = '{$search_status_esc}'";
}
if ($search_area > 0) {
    $where .= " AND el.ea_id = {$search_area}";
}

// 전체 수
$total_row = sql_fetch("SELECT COUNT(*) as cnt
                        FROM {$g5['mg_expedition_log_table']} el
                        LEFT JOIN {$g5['member_table']} m ON el.mb_id = m.mb_id
                        WHERE {$where}");
$total_count = (int)$total_row['cnt'];
$total_pages = max(1, ceil($total_count / $per_page));

// 목록
$sql = "SELECT el.*, ea.ea_name, ch.ch_name,
               pch.ch_name as partner_ch_name, m.mb_nick, pm.mb_nick as partner_nick
        FROM {$g5['mg_expedition_log_table']} el
        LEFT JOIN {$g5['mg_expedition_area_table']} ea ON el.ea_id = ea.ea_id
        LEFT JOIN {$g5['mg_character_table']} ch ON el.ch_id = ch.ch_id
        LEFT JOIN {$g5['mg_character_table']} pch ON el.partner_ch_id = pch.ch_id
        LEFT JOIN {$g5['member_table']} m ON el.mb_id = m.mb_id
        LEFT JOIN {$g5['member_table']} pm ON el.partner_mb_id = pm.mb_id
        WHERE {$where}
        ORDER BY el.el_id DESC
        LIMIT {$offset}, {$per_page}";
$result = sql_query($sql);
$logs = array();
while ($row = sql_fetch_array($result)) {
    $logs[] = $row;
}

// 파견지 목록 (필터용)
$area_list = array();
$area_result = sql_query("SELECT ea_id, ea_name FROM {$g5['mg_expedition_area_table']} ORDER BY ea_order, ea_id");
while ($ar = sql_fetch_array($area_result)) {
    $area_list[] = $ar;
}

$g5['title'] = '파견 로그';
require_once __DIR__.'/_head.php';
?>

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body">
        <form method="get" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;">
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label" style="font-size:0.75rem;">회원ID/닉네임</label>
                <input type="text" name="mb_id" class="mg-form-input" style="width:140px;" value="<?php echo htmlspecialchars($search_mb_id); ?>">
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label" style="font-size:0.75rem;">상태</label>
                <select name="status" class="mg-form-input" style="width:120px;">
                    <option value="">전체</option>
                    <option value="active" <?php echo $search_status === 'active' ? 'selected' : ''; ?>>진행중</option>
                    <option value="complete" <?php echo $search_status === 'complete' ? 'selected' : ''; ?>>완료(미수령)</option>
                    <option value="claimed" <?php echo $search_status === 'claimed' ? 'selected' : ''; ?>>수령완료</option>
                    <option value="cancelled" <?php echo $search_status === 'cancelled' ? 'selected' : ''; ?>>취소</option>
                </select>
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label" style="font-size:0.75rem;">파견지</label>
                <select name="ea_id" class="mg-form-input" style="width:140px;">
                    <option value="0">전체</option>
                    <?php foreach ($area_list as $al) { ?>
                    <option value="<?php echo $al['ea_id']; ?>" <?php echo $search_area == $al['ea_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($al['ea_name']); ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">검색</button>
            <a href="<?php echo G5_ADMIN_URL; ?>/morgan/expedition_log.php" class="mg-btn mg-btn-secondary mg-btn-sm">초기화</a>
        </form>
    </div>
</div>

<!-- 통계 -->
<div style="margin-bottom:1rem;color:var(--mg-text-secondary);font-size:0.85rem;">
    총 <?php echo number_format($total_count); ?>건 (<?php echo $page; ?>/<?php echo $total_pages; ?>페이지)
</div>

<!-- 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:1000px;">
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>회원</th>
                    <th>캐릭터</th>
                    <th>파트너</th>
                    <th>파견지</th>
                    <th style="width:50px;">ST</th>
                    <th>시작</th>
                    <th>종료</th>
                    <th style="width:70px;">상태</th>
                    <th>보상</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log) {
                    $status_badges = array(
                        'active' => '<span class="mg-badge mg-badge-primary">진행중</span>',
                        'complete' => '<span class="mg-badge mg-badge-warning">미수령</span>',
                        'claimed' => '<span class="mg-badge mg-badge-success">수령</span>',
                        'cancelled' => '<span class="mg-badge">취소</span>',
                    );
                    $badge = isset($status_badges[$log['el_status']]) ? $status_badges[$log['el_status']] : $log['el_status'];

                    $rewards_text = '-';
                    if ($log['el_rewards']) {
                        $rwd = json_decode($log['el_rewards'], true);
                        if ($rwd && !empty($rwd['items'])) {
                            $parts = array();
                            foreach ($rwd['items'] as $item) {
                                $parts[] = $item['mt_name'] . ' x' . $item['amount'] . ($item['is_rare'] ? ' (희귀)' : '');
                            }
                            $rewards_text = implode(', ', $parts);
                        } else {
                            $rewards_text = '(드롭 없음)';
                        }
                    }
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $log['el_id']; ?></td>
                    <td><?php echo htmlspecialchars($log['mb_nick'] ?: $log['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['ch_name'] ?: '-'); ?></td>
                    <td>
                        <?php if ($log['partner_ch_name']) { ?>
                        <?php echo htmlspecialchars($log['partner_ch_name']); ?>
                        <span style="font-size:0.75rem;color:var(--mg-text-muted);">(<?php echo htmlspecialchars($log['partner_nick'] ?: $log['partner_mb_id']); ?>)</span>
                        <?php } else { echo '-'; } ?>
                    </td>
                    <td><?php echo htmlspecialchars($log['ea_name'] ?: '-'); ?></td>
                    <td style="text-align:center;"><?php echo $log['el_stamina_used']; ?></td>
                    <td style="font-size:0.8rem;"><?php echo substr($log['el_start'], 5, 11); ?></td>
                    <td style="font-size:0.8rem;"><?php echo substr($log['el_end'], 5, 11); ?></td>
                    <td style="text-align:center;"><?php echo $badge; ?></td>
                    <td style="font-size:0.8rem;"><?php echo htmlspecialchars($rewards_text); ?></td>
                </tr>
                <?php } ?>
                <?php if (empty($logs)) { ?>
                <tr><td colspan="10" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">파견 기록이 없습니다.</td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_pages > 1) { ?>
<div style="margin-top:1rem;text-align:center;">
    <?php
    $query_params = array();
    if ($search_mb_id) $query_params[] = 'mb_id=' . urlencode($search_mb_id);
    if ($search_status) $query_params[] = 'status=' . urlencode($search_status);
    if ($search_area) $query_params[] = 'ea_id=' . $search_area;
    $base_url = G5_ADMIN_URL . '/morgan/expedition_log.php?' . implode('&', $query_params);
    if (!empty($query_params)) $base_url .= '&';

    for ($p = max(1, $page - 4); $p <= min($total_pages, $page + 4); $p++) {
        $active = ($p === $page) ? 'mg-btn-primary' : 'mg-btn-secondary';
        echo '<a href="' . $base_url . 'page=' . $p . '" class="mg-btn ' . $active . ' mg-btn-sm" style="margin:2px;">' . $p . '</a>';
    }
    ?>
</div>
<?php } ?>

<?php
require_once __DIR__.'/_tail.php';
?>
