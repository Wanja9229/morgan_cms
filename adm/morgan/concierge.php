<?php
/**
 * Morgan Edition - 의뢰 관리
 */

$sub_menu = "801800";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 검색 파라미터
$search_mb_id = isset($_GET['mb_id']) ? clean_xss_tags($_GET['mb_id']) : '';
$search_status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : '';
$search_type = isset($_GET['type']) ? clean_xss_tags($_GET['type']) : '';

// 페이지네이션
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 30;
$offset = ($page - 1) * $per_page;

// 만료 자동 처리
$now = date('Y-m-d H:i:s');
sql_query("UPDATE {$g5['mg_concierge_table']}
           SET cc_status = 'expired'
           WHERE cc_status = 'recruiting' AND cc_deadline < '{$now}'");

// WHERE 조건
$where = "1=1";
if ($search_mb_id) {
    $esc = sql_real_escape_string($search_mb_id);
    $where .= " AND (cc.mb_id LIKE '%{$esc}%' OR m.mb_nick LIKE '%{$esc}%')";
}
if ($search_status) {
    $where .= " AND cc.cc_status = '" . sql_real_escape_string($search_status) . "'";
}
if ($search_type) {
    $where .= " AND cc.cc_type = '" . sql_real_escape_string($search_type) . "'";
}

// 전체 수
$total_row = sql_fetch("SELECT COUNT(*) as cnt
                        FROM {$g5['mg_concierge_table']} cc
                        LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
                        WHERE {$where}");
$total_count = (int)$total_row['cnt'];
$total_pages = max(1, ceil($total_count / $per_page));

// 목록
$sql = "SELECT cc.*, m.mb_nick, ch.ch_name,
               (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id) as apply_count,
               (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id AND ca_status = 'selected') as selected_count
        FROM {$g5['mg_concierge_table']} cc
        LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
        LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
        WHERE {$where}
        ORDER BY cc.cc_id DESC
        LIMIT {$offset}, {$per_page}";
$result = sql_query($sql);
$items = array();
while ($row = sql_fetch_array($result)) {
    $items[] = $row;
}

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
$status_badges = array(
    'recruiting' => '<span class="mg-badge mg-badge-primary">모집중</span>',
    'matched' => '<span class="mg-badge mg-badge-warning">매칭완료</span>',
    'completed' => '<span class="mg-badge mg-badge-success">완료</span>',
    'expired' => '<span class="mg-badge">만료</span>',
    'cancelled' => '<span class="mg-badge">취소</span>',
);

$g5['title'] = '의뢰 관리';
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
                    <option value="recruiting" <?php echo $search_status === 'recruiting' ? 'selected' : ''; ?>>모집중</option>
                    <option value="matched" <?php echo $search_status === 'matched' ? 'selected' : ''; ?>>매칭완료</option>
                    <option value="completed" <?php echo $search_status === 'completed' ? 'selected' : ''; ?>>완료</option>
                    <option value="expired" <?php echo $search_status === 'expired' ? 'selected' : ''; ?>>만료</option>
                    <option value="cancelled" <?php echo $search_status === 'cancelled' ? 'selected' : ''; ?>>취소</option>
                </select>
            </div>
            <div class="mg-form-group" style="margin-bottom:0;">
                <label class="mg-form-label" style="font-size:0.75rem;">유형</label>
                <select name="type" class="mg-form-input" style="width:120px;">
                    <option value="">전체</option>
                    <option value="collaboration" <?php echo $search_type === 'collaboration' ? 'selected' : ''; ?>>합작</option>
                    <option value="illustration" <?php echo $search_type === 'illustration' ? 'selected' : ''; ?>>일러스트</option>
                    <option value="novel" <?php echo $search_type === 'novel' ? 'selected' : ''; ?>>소설</option>
                    <option value="other" <?php echo $search_type === 'other' ? 'selected' : ''; ?>>기타</option>
                </select>
            </div>
            <button type="submit" class="mg-btn mg-btn-primary mg-btn-sm">검색</button>
            <a href="<?php echo G5_ADMIN_URL; ?>/morgan/concierge.php" class="mg-btn mg-btn-secondary mg-btn-sm">초기화</a>
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
        <table class="mg-table" style="min-width:900px;">
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>의뢰자</th>
                    <th>캐릭터</th>
                    <th>제목</th>
                    <th style="width:70px;">유형</th>
                    <th style="width:60px;">티어</th>
                    <th style="width:70px;">지원/모집</th>
                    <th style="width:80px;">매칭</th>
                    <th>마감일</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:80px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item) {
                    $badge = isset($status_badges[$item['cc_status']]) ? $status_badges[$item['cc_status']] : $item['cc_status'];
                    $type_label = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];
                    $tier_label = $item['cc_tier'] === 'urgent' ? '<span style="color:#f59e0b;font-weight:bold;">긴급</span>' : '일반';
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $item['cc_id']; ?></td>
                    <td><?php echo htmlspecialchars($item['mb_nick'] ?: $item['mb_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['ch_name'] ?: '-'); ?></td>
                    <td>
                        <?php echo htmlspecialchars(mb_substr($item['cc_title'], 0, 30)); ?>
                        <?php if (mb_strlen($item['cc_title']) > 30) echo '...'; ?>
                    </td>
                    <td style="text-align:center;"><?php echo $type_label; ?></td>
                    <td style="text-align:center;"><?php echo $tier_label; ?></td>
                    <td style="text-align:center;"><?php echo $item['apply_count']; ?>/<?php echo $item['cc_max_members']; ?></td>
                    <td style="text-align:center;"><?php echo $item['cc_match_mode'] === 'lottery' ? '추첨' : '직접'; ?></td>
                    <td style="font-size:0.8rem;"><?php echo substr($item['cc_deadline'], 0, 16); ?></td>
                    <td style="text-align:center;"><?php echo $badge; ?></td>
                    <td style="text-align:center;">
                        <?php if (in_array($item['cc_status'], array('recruiting', 'matched'))) { ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="cancelConcierge(<?php echo $item['cc_id']; ?>)">취소</button>
                        <?php } else { echo '-'; } ?>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($items)) { ?>
                <tr><td colspan="11" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">등록된 의뢰가 없습니다.</td></tr>
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
    if ($search_type) $query_params[] = 'type=' . urlencode($search_type);
    $base_url = G5_ADMIN_URL . '/morgan/concierge.php?' . implode('&', $query_params);
    if (!empty($query_params)) $base_url .= '&';

    for ($p = max(1, $page - 4); $p <= min($total_pages, $page + 4); $p++) {
        $active = ($p === $page) ? 'mg-btn-primary' : 'mg-btn-secondary';
        echo '<a href="' . $base_url . 'page=' . $p . '" class="mg-btn ' . $active . ' mg-btn-sm" style="margin:2px;">' . $p . '</a>';
    }
    ?>
</div>
<?php } ?>

<script>
function cancelConcierge(cc_id) {
    if (!confirm('이 의뢰를 관리자 권한으로 취소하시겠습니까?')) return;

    // 관리자 강제 취소
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/concierge_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="cancel"><input type="hidden" name="cc_id" value="' + cc_id + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
