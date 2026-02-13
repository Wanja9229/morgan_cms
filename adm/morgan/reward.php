<?php
/**
 * Morgan Edition - 보상 관리
 */

$sub_menu = "800570";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 탭
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'board';

// AJAX: 보상 유형 목록 반환
if (isset($_GET['ajax_types'])) {
    header('Content-Type: application/json');
    $bo_table = isset($_GET['bo_table']) ? trim($_GET['bo_table']) : '';
    $bo_esc = sql_real_escape_string($bo_table);
    $types = array();
    $sql = "SELECT rwt_id, bo_table, rwt_name, rwt_point, rwt_desc
            FROM {$g5['mg_reward_type_table']}
            WHERE rwt_use = 1 AND (bo_table = '{$bo_esc}' OR bo_table IS NULL)
            ORDER BY rwt_order";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $types[] = $row;
    }
    echo json_encode($types);
    exit;
}

// 게시판 목록 + 보상 설정 조인
$boards = array();
$sql = "SELECT b.bo_table, b.bo_subject, b.bo_write_point, b.bo_comment_point,
               r.br_id, r.br_mode, r.br_point, r.br_bonus_500, r.br_bonus_1000,
               r.br_bonus_image, r.br_material_use, r.br_material_chance,
               r.br_material_list, r.br_daily_limit, r.br_like_use,
               r.br_dice_use, r.br_dice_once, r.br_dice_max
        FROM {$g5['board_table']} b
        LEFT JOIN {$g5['mg_board_reward_table']} r ON b.bo_table = r.bo_table
        ORDER BY b.gr_id, b.bo_order, b.bo_table";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $boards[] = $row;
}

// 재료 목록 (모달용)
$material_types = mg_get_material_types();

// 활동 보상 설정값 (activity 탭용)
$activity_configs = array(
    'rp_create_cost' => mg_get_config('rp_create_cost', '500'),
    'rp_reply_batch_count' => mg_get_config('rp_reply_batch_count', '10'),
    'rp_reply_batch_point' => mg_get_config('rp_reply_batch_point', '30'),
    'rp_complete_point' => mg_get_config('rp_complete_point', '200'),
    'rp_complete_min_mutual' => mg_get_config('rp_complete_min_mutual', '5'),
    'rp_auto_complete_days' => mg_get_config('rp_auto_complete_days', '7'),
    'pioneer_write_reward' => mg_get_config('pioneer_write_reward', ''),
    'pioneer_comment_reward' => mg_get_config('pioneer_comment_reward', ''),
    'pioneer_rp_reward' => mg_get_config('pioneer_rp_reward', ''),
    'pioneer_attendance_reward' => mg_get_config('pioneer_attendance_reward', ''),
    'like_daily_limit' => mg_get_config('like_daily_limit', '5'),
    'like_giver_point' => mg_get_config('like_giver_point', '10'),
    'like_receiver_point' => mg_get_config('like_receiver_point', '30'),
);

// RP 보상 데이터 (rp 탭용)
$rp_completions = array();
$rp_reply_logs = array();
if ($tab == 'rp') {
    // 자동 완결 패시브 체크
    if (function_exists('mg_rp_auto_complete_check')) {
        $auto_days = (int)mg_config('rp_auto_complete_days', 7);
        if ($auto_days > 0) {
            $stale = sql_query("SELECT rt_id FROM {$g5['mg_rp_thread_table']}
                WHERE rt_status = 'open' AND rt_update < DATE_SUB(NOW(), INTERVAL {$auto_days} DAY) LIMIT 5");
            if ($stale) {
                while ($row = sql_fetch_array($stale)) {
                    mg_rp_auto_complete_check((int)$row['rt_id']);
                }
            }
        }
    }

    // 기간 필터
    $rp_period = isset($_GET['period']) ? $_GET['period'] : 'all';
    $rp_where = '';
    switch ($rp_period) {
        case 'today': $rp_where = "AND rc.rc_datetime >= CURDATE()"; break;
        case 'week': $rp_where = "AND rc.rc_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
        case 'month': $rp_where = "AND rc.rc_datetime >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
    }

    // 페이징
    $rp_page = isset($_GET['rp_page']) ? max(1, (int)$_GET['rp_page']) : 1;
    $rp_rows = 30;
    $rp_offset = ($rp_page - 1) * $rp_rows;

    $total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_rp_completion_table']} rc WHERE 1 {$rp_where}");
    $rp_total = (int)$total_row['cnt'];
    $rp_total_page = $rp_total > 0 ? ceil($rp_total / $rp_rows) : 1;

    $comp_sql = "SELECT rc.*, t.rt_title, c.ch_name, m.mb_nick
        FROM {$g5['mg_rp_completion_table']} rc
        LEFT JOIN {$g5['mg_rp_thread_table']} t ON rc.rt_id = t.rt_id
        LEFT JOIN {$g5['mg_character_table']} c ON rc.ch_id = c.ch_id
        LEFT JOIN {$g5['member_table']} m ON rc.mb_id = m.mb_id
        WHERE 1 {$rp_where}
        ORDER BY rc.rc_datetime DESC
        LIMIT {$rp_offset}, {$rp_rows}";
    $comp_result = sql_query($comp_sql);
    while ($row = sql_fetch_array($comp_result)) {
        $rp_completions[] = $row;
    }

    // 잇기 보상 로그 (최근 20건)
    $log_sql = "SELECT rl.*, t.rt_title
        FROM {$g5['mg_rp_reply_reward_log_table']} rl
        LEFT JOIN {$g5['mg_rp_thread_table']} t ON rl.rt_id = t.rt_id
        ORDER BY rl.rrl_datetime DESC LIMIT 20";
    $log_result = sql_query($log_sql);
    while ($row = sql_fetch_array($log_result)) {
        $rp_reply_logs[] = $row;
    }
}

// 좋아요 보상 데이터 (like 탭용)
$like_logs = array();
if ($tab == 'like') {
    $like_period = isset($_GET['period']) ? $_GET['period'] : 'all';
    $like_where = '';
    switch ($like_period) {
        case 'today': $like_where = "AND ll.ll_datetime >= CURDATE()"; break;
        case 'week': $like_where = "AND ll.ll_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
        case 'month': $like_where = "AND ll.ll_datetime >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
    }

    $like_page = isset($_GET['like_page']) ? max(1, (int)$_GET['like_page']) : 1;
    $like_rows = 30;
    $like_offset = ($like_page - 1) * $like_rows;

    $total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_like_log_table']} ll WHERE 1 {$like_where}");
    $like_total = (int)$total_row['cnt'];
    $like_total_page = $like_total > 0 ? ceil($like_total / $like_rows) : 1;

    $like_sql = "SELECT ll.*, m1.mb_nick as giver_nick, m2.mb_nick as receiver_nick
        FROM {$g5['mg_like_log_table']} ll
        LEFT JOIN {$g5['member_table']} m1 ON ll.mb_id = m1.mb_id
        LEFT JOIN {$g5['member_table']} m2 ON ll.target_mb_id = m2.mb_id
        WHERE 1 {$like_where}
        ORDER BY ll.ll_datetime DESC
        LIMIT {$like_offset}, {$like_rows}";
    $like_result = sql_query($like_sql);
    while ($row = sql_fetch_array($like_result)) {
        $like_logs[] = $row;
    }
}

// 정산 대기열 데이터 (settlement 탭용)
$stl_queue = array();
if ($tab == 'settlement') {
    $stl_status = isset($_GET['status']) ? $_GET['status'] : 'all';
    $stl_bo = isset($_GET['bo']) ? trim($_GET['bo']) : '';
    $stl_period = isset($_GET['period']) ? $_GET['period'] : 'all';

    $stl_where = '';
    if ($stl_status && $stl_status !== 'all') {
        $stl_where .= " AND rq.rq_status = '".sql_real_escape_string($stl_status)."'";
    }
    if ($stl_bo) {
        $stl_where .= " AND rq.bo_table = '".sql_real_escape_string($stl_bo)."'";
    }
    switch ($stl_period) {
        case 'today': $stl_where .= " AND rq.rq_datetime >= CURDATE()"; break;
        case 'week': $stl_where .= " AND rq.rq_datetime >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)"; break;
        case 'month': $stl_where .= " AND rq.rq_datetime >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; break;
    }

    $stl_page = isset($_GET['stl_page']) ? max(1, (int)$_GET['stl_page']) : 1;
    $stl_rows = 30;
    $stl_offset = ($stl_page - 1) * $stl_rows;

    $total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_reward_queue_table']} rq WHERE 1 {$stl_where}");
    $stl_total = (int)$total_row['cnt'];
    $stl_total_page = $stl_total > 0 ? ceil($stl_total / $stl_rows) : 1;

    $stl_sql = "SELECT rq.*, rt.rwt_name, rt.rwt_point, m.mb_nick
                FROM {$g5['mg_reward_queue_table']} rq
                LEFT JOIN {$g5['mg_reward_type_table']} rt ON rq.rwt_id = rt.rwt_id
                LEFT JOIN {$g5['member_table']} m ON rq.mb_id = m.mb_id
                WHERE 1 {$stl_where}
                ORDER BY rq.rq_datetime DESC
                LIMIT {$stl_offset}, {$stl_rows}";

    $stl_result = sql_query($stl_sql);
    while ($row = sql_fetch_array($stl_result)) {
        // 글 제목 개별 조회
        if ($row['bo_table'] && $row['wr_id']) {
            $wr = sql_fetch("SELECT wr_subject FROM write_{$row['bo_table']} WHERE wr_id = ".(int)$row['wr_id']);
            $row['wr_subject'] = $wr['wr_subject'] ?: '(삭제됨)';
        } else {
            $row['wr_subject'] = '(알 수 없음)';
        }
        $stl_queue[] = $row;
    }

}

// pending 건수 (탭 배지용, 항상 조회)
$stl_pending_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_reward_queue_table']} WHERE rq_status = 'pending'");
$stl_pending_count = (int)$stl_pending_row['cnt'];

$g5['title'] = '보상 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 탭 네비게이션 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=board" class="mg-tab <?php echo $tab == 'board' ? 'active' : ''; ?>">게시판별 보상</a>
    <a href="?tab=activity" class="mg-tab <?php echo $tab == 'activity' ? 'active' : ''; ?>">활동 보상 설정</a>
    <a href="?tab=rp" class="mg-tab <?php echo $tab == 'rp' ? 'active' : ''; ?>">역극 보상</a>
    <a href="?tab=like" class="mg-tab <?php echo $tab == 'like' ? 'active' : ''; ?>">좋아요 보상</a>
    <a href="?tab=settlement" class="mg-tab <?php echo $tab == 'settlement' ? 'active' : ''; ?>">정산 대기열<?php if (isset($stl_pending_count) && $stl_pending_count > 0) echo ' <span class="mg-badge mg-badge--warning">'.$stl_pending_count.'</span>'; ?></a>
</div>

<?php if ($tab == 'board') { ?>
<!-- ================================ -->
<!-- 게시판별 보상 설정 -->
<!-- ================================ -->

<div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
    게시판별로 글 작성 시 보상을 설정합니다. 설정하지 않은 게시판은 기존 그누보드 포인트(bo_write_point)가 적용됩니다.
</div>

<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:900px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:100px;">게시판</th>
                    <th style="width:70px;">모드</th>
                    <th style="width:70px;">기본P</th>
                    <th style="width:70px;">500자+</th>
                    <th style="width:75px;">1000자+</th>
                    <th style="width:70px;">이미지</th>
                    <th style="width:70px;">재료</th>
                    <th style="width:70px;">일일제한</th>
                    <th style="width:55px;">좋아요</th>
                    <th style="width:50px;">🎲</th>
                    <th style="width:60px;">기존P</th>
                    <th style="width:65px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($boards as $b) {
                    $mode = $b['br_mode'] ?: 'off';
                    $mode_label = array('auto' => '<span class="mg-badge mg-badge--success">Auto</span>', 'request' => '<span class="mg-badge mg-badge--warning">Request</span>', 'off' => '<span style="color:var(--mg-text-muted);">-</span>');
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($b['bo_subject']); ?></strong><br><small style="color:var(--mg-text-muted);"><?php echo $b['bo_table']; ?></small></td>
                    <td style="text-align:center;"><?php echo $mode_label[$mode]; ?></td>
                    <td style="text-align:center;"><?php echo $mode == 'auto' ? $b['br_point'] : '-'; ?></td>
                    <td style="text-align:center;"><?php echo ($mode == 'auto' && $b['br_bonus_500']) ? '+'.$b['br_bonus_500'] : '-'; ?></td>
                    <td style="text-align:center;"><?php echo ($mode == 'auto' && $b['br_bonus_1000']) ? '+'.$b['br_bonus_1000'] : '-'; ?></td>
                    <td style="text-align:center;"><?php echo ($mode == 'auto' && $b['br_bonus_image']) ? '+'.$b['br_bonus_image'] : '-'; ?></td>
                    <td style="text-align:center;"><?php
                        if ($mode == 'auto' && $b['br_material_use']) {
                            echo '<span class="mg-badge">'.$b['br_material_chance'].'%</span>';
                        } else {
                            echo '-';
                        }
                    ?></td>
                    <td style="text-align:center;"><?php echo ($mode == 'auto' && $b['br_daily_limit']) ? $b['br_daily_limit'].'회' : ($mode == 'auto' ? '무제한' : '-'); ?></td>
                    <td style="text-align:center;"><?php
                        $like_on = ($b['br_like_use'] === null || $b['br_like_use'] == 1);
                        echo $like_on ? '<span style="color:var(--mg-success);">&check;</span>' : '<span style="color:var(--mg-text-muted);">&cross;</span>';
                    ?></td>
                    <td style="text-align:center;"><?php
                        echo $b['br_dice_use'] ? '<span style="color:var(--mg-success);">&check;</span>' : '<span style="color:var(--mg-text-muted);">&cross;</span>';
                    ?></td>
                    <td style="text-align:center;color:var(--mg-text-muted);"><?php echo $b['bo_write_point']; ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editReward('<?php echo $b['bo_table']; ?>')">편집</button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 편집 모달 -->
<div id="reward-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:550px;">
        <div class="mg-modal-header">
            <h3 id="modal-title">보상 설정</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="reward-form">
            <input type="hidden" name="mode" value="board_reward">
            <input type="hidden" name="bo_table" id="f_bo_table" value="">

            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">보상 모드</label>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:0.25rem;cursor:pointer;">
                            <input type="radio" name="br_mode" value="auto" onchange="toggleMode()"> Auto (자동 지급)
                        </label>
                        <label style="display:flex;align-items:center;gap:0.25rem;cursor:pointer;">
                            <input type="radio" name="br_mode" value="request" onchange="toggleMode()"> Request (요청 후 승인)
                        </label>
                        <label style="display:flex;align-items:center;gap:0.25rem;cursor:pointer;">
                            <input type="radio" name="br_mode" value="off" onchange="toggleMode()"> Off (미사용)
                        </label>
                    </div>
                    <small style="color:var(--mg-text-muted);">Off: 기존 그누보드 bo_write_point 사용</small>
                </div>

                <div class="mg-form-group" style="border-top:1px solid var(--mg-border);padding-top:0.75rem;margin-top:0.25rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="br_like_use" id="f_br_like_use" value="1">
                        <span class="mg-form-label" style="margin:0;">좋아요 보상 활성화</span>
                    </label>
                    <small style="color:var(--mg-text-muted);">체크 해제 시 이 게시판에서 좋아요 보상이 지급되지 않습니다. (글 작성 보상 모드와 무관)</small>
                </div>

                <div class="mg-form-group" style="border-top:1px solid var(--mg-border);padding-top:0.75rem;margin-top:0.25rem;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="br_dice_use" id="f_br_dice_use" value="1" onchange="toggleDice()">
                        <span class="mg-form-label" style="margin:0;">🎲 댓글 주사위 활성화</span>
                    </label>
                    <small style="color:var(--mg-text-muted);">활성화 시 댓글 영역에 주사위 버튼이 표시됩니다.</small>
                    <div id="dice-settings" style="display:none;margin-top:0.75rem;">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="mg-form-group">
                                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                                    <input type="checkbox" name="br_dice_once" id="f_br_dice_once" value="1">
                                    <span class="mg-form-label" style="margin:0;">1인 1회 제한</span>
                                </label>
                                <small style="color:var(--mg-text-muted);">한 글에서 한 번만 굴릴 수 있습니다.</small>
                            </div>
                            <div class="mg-form-group">
                                <label class="mg-form-label">최대값</label>
                                <input type="number" name="br_dice_max" id="f_br_dice_max" class="mg-form-input" value="100" min="1" max="9999">
                                <small style="color:var(--mg-text-muted);">0 ~ 최대값 범위의 랜덤 숫자</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="auto-settings" style="display:none;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">기본 포인트</label>
                            <input type="number" name="br_point" id="f_br_point" class="mg-form-input" value="0" min="0">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">일일 제한 (0=무제한)</label>
                            <input type="number" name="br_daily_limit" id="f_br_daily_limit" class="mg-form-input" value="0" min="0">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">500자+ 보너스</label>
                            <input type="number" name="br_bonus_500" id="f_br_bonus_500" class="mg-form-input" value="0" min="0">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">1000자+ 보너스</label>
                            <input type="number" name="br_bonus_1000" id="f_br_bonus_1000" class="mg-form-input" value="0" min="0">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">이미지 보너스</label>
                            <input type="number" name="br_bonus_image" id="f_br_bonus_image" class="mg-form-input" value="0" min="0">
                        </div>
                    </div>

                    <div class="mg-form-group" style="border-top:1px solid var(--mg-border);padding-top:1rem;margin-top:0.5rem;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="br_material_use" id="f_br_material_use" value="1" onchange="toggleMaterial()">
                            <span class="mg-form-label" style="margin:0;">재료 드롭 사용</span>
                        </label>
                    </div>

                    <div id="material-settings" style="display:none;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">드롭 확률 (%)</label>
                            <input type="number" name="br_material_chance" id="f_br_material_chance" class="mg-form-input" value="30" min="1" max="100" style="width:100px;">
                        </div>
                        <div class="mg-form-group">
                            <label class="mg-form-label">드롭 대상 재료</label>
                            <div style="display:flex;flex-wrap:wrap;gap:0.75rem;">
                                <?php foreach ($material_types as $mt) { ?>
                                <label style="display:flex;align-items:center;gap:0.25rem;cursor:pointer;">
                                    <input type="checkbox" name="br_materials[]" value="<?php echo htmlspecialchars($mt['mt_code']); ?>" class="mat-check">
                                    <?php if ($mt['mt_icon']) echo mg_icon($mt['mt_icon'], 'w-4 h-4'); ?>
                                    <span style="font-size:0.9rem;"><?php echo htmlspecialchars($mt['mt_name']); ?></span>
                                </label>
                                <?php } ?>
                                <?php if (empty($material_types)) { ?>
                                <span style="color:var(--mg-text-muted);font-size:0.85rem;">등록된 재료가 없습니다. 재료 관리에서 먼저 추가하세요.</span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="request-settings" style="display:none;">
                    <div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
                        유저가 글 작성 시 보상 유형을 선택해 요청하면, 관리자가 정산 대기열에서 승인/반려합니다.
                    </div>

                    <div class="mg-form-group">
                        <label class="mg-form-label">보상 유형 목록</label>
                        <div id="reward-type-list" style="margin-bottom:0.75rem;">
                            <!-- JS로 렌더링 -->
                        </div>
                    </div>

                    <div style="border:1px dashed var(--mg-border);border-radius:8px;padding:1rem;background:var(--mg-bg-secondary);">
                        <div style="font-weight:600;margin-bottom:0.5rem;font-size:0.9rem;">새 유형 추가</div>
                        <div style="display:grid;grid-template-columns:1fr 80px;gap:0.5rem;">
                            <input type="text" id="new_rwt_name" class="mg-form-input" placeholder="유형 이름" style="font-size:0.85rem;">
                            <input type="number" id="new_rwt_point" class="mg-form-input" placeholder="포인트" min="0" value="0" style="font-size:0.85rem;">
                        </div>
                        <input type="text" id="new_rwt_desc" class="mg-form-input" placeholder="설명 (선택)" style="font-size:0.85rem;margin-top:0.5rem;">
                        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="addRewardType()" style="margin-top:0.5rem;">추가</button>
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

<script>
var boardData = <?php echo json_encode($boards); ?>;

function editReward(bo_table) {
    var b = boardData.find(function(r) { return r.bo_table === bo_table; });
    if (!b) return;

    document.getElementById('modal-title').textContent = b.bo_subject + ' 보상 설정';
    document.getElementById('f_bo_table').value = bo_table;

    var mode = b.br_mode || 'off';
    document.querySelector('input[name="br_mode"][value="' + mode + '"]').checked = true;

    document.getElementById('f_br_point').value = b.br_point || 0;
    document.getElementById('f_br_bonus_500').value = b.br_bonus_500 || 0;
    document.getElementById('f_br_bonus_1000').value = b.br_bonus_1000 || 0;
    document.getElementById('f_br_bonus_image').value = b.br_bonus_image || 0;
    document.getElementById('f_br_daily_limit').value = b.br_daily_limit || 0;
    document.getElementById('f_br_material_use').checked = b.br_material_use == 1;
    document.getElementById('f_br_material_chance').value = b.br_material_chance || 30;
    document.getElementById('f_br_like_use').checked = (b.br_like_use == null || b.br_like_use == 1);

    // 주사위 설정
    document.getElementById('f_br_dice_use').checked = b.br_dice_use == 1;
    document.getElementById('f_br_dice_once').checked = (b.br_dice_once == null || b.br_dice_once == 1);
    document.getElementById('f_br_dice_max').value = b.br_dice_max || 100;
    toggleDice();

    // 재료 체크박스
    var matList = [];
    try { matList = b.br_material_list ? JSON.parse(b.br_material_list) : []; } catch(e) {}
    document.querySelectorAll('.mat-check').forEach(function(cb) {
        cb.checked = matList.indexOf(cb.value) !== -1;
    });

    toggleMode();
    toggleMaterial();
    document.getElementById('reward-modal').style.display = 'flex';
}

function toggleMode() {
    var mode = document.querySelector('input[name="br_mode"]:checked').value;
    document.getElementById('auto-settings').style.display = mode === 'auto' ? '' : 'none';
    document.getElementById('request-settings').style.display = mode === 'request' ? '' : 'none';
    if (mode === 'request') {
        loadRewardTypes(document.getElementById('f_bo_table').value);
    }
}

function toggleMaterial() {
    document.getElementById('material-settings').style.display =
        document.getElementById('f_br_material_use').checked ? '' : 'none';
}

function toggleDice() {
    document.getElementById('dice-settings').style.display =
        document.getElementById('f_br_dice_use').checked ? '' : 'none';
}

function closeModal() {
    document.getElementById('reward-modal').style.display = 'none';
}

document.getElementById('reward-modal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

document.getElementById('reward-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);

    // 재료 목록을 JSON으로
    var mats = [];
    document.querySelectorAll('.mat-check:checked').forEach(function(cb) { mats.push(cb.value); });
    fd.set('br_material_list', JSON.stringify(mats));
    fd.set('br_material_use', document.getElementById('f_br_material_use').checked ? '1' : '0');
    fd.set('br_like_use', document.getElementById('f_br_like_use').checked ? '1' : '0');
    fd.set('br_dice_use', document.getElementById('f_br_dice_use').checked ? '1' : '0');
    fd.set('br_dice_once', document.getElementById('f_br_dice_once').checked ? '1' : '0');
    fd.set('br_dice_max', document.getElementById('f_br_dice_max').value || '100');

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/reward_update.php', {
        method: 'POST',
        body: fd
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '저장 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
});

// --- 보상 유형 CRUD ---
var _rewardUpdateUrl = '<?php echo G5_ADMIN_URL; ?>/morgan/reward_update.php';

function loadRewardTypes(bo_table) {
    var container = document.getElementById('reward-type-list');
    container.innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.85rem;">로딩 중...</span>';

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/reward.php?tab=board&ajax_types=1&bo_table=' + encodeURIComponent(bo_table))
    .then(function(r) { return r.json(); })
    .then(function(types) {
        if (!types.length) {
            container.innerHTML = '<span style="color:var(--mg-text-muted);font-size:0.85rem;">등록된 보상 유형이 없습니다.</span>';
            return;
        }
        var html = '<table class="mg-table" style="font-size:0.85rem;"><thead><tr><th>유형</th><th style="width:70px;">포인트</th><th>설명</th><th style="width:50px;"></th></tr></thead><tbody>';
        types.forEach(function(t) {
            html += '<tr><td>' + escHtml(t.rwt_name) + '</td>';
            html += '<td style="text-align:center;">' + Number(t.rwt_point).toLocaleString() + 'P</td>';
            html += '<td style="color:var(--mg-text-muted);">' + escHtml(t.rwt_desc || '') + '</td>';
            html += '<td><button type="button" class="mg-btn mg-btn-danger mg-btn-sm" style="padding:2px 6px;font-size:0.75rem;" onclick="deleteRewardType(' + t.rwt_id + ')">삭제</button></td></tr>';
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    })
    .catch(function() {
        container.innerHTML = '<span style="color:var(--mg-error);">로드 실패</span>';
    });
}

function addRewardType() {
    var bo_table = document.getElementById('f_bo_table').value;
    var name = document.getElementById('new_rwt_name').value.trim();
    var point = document.getElementById('new_rwt_point').value;
    var desc = document.getElementById('new_rwt_desc').value.trim();

    if (!name) { alert('유형 이름을 입력하세요.'); return; }

    var fd = new FormData();
    fd.append('mode', 'reward_type_save');
    fd.append('bo_table', bo_table);
    fd.append('rwt_name', name);
    fd.append('rwt_point', point);
    fd.append('rwt_desc', desc);

    fetch(_rewardUpdateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            document.getElementById('new_rwt_name').value = '';
            document.getElementById('new_rwt_point').value = '0';
            document.getElementById('new_rwt_desc').value = '';
            loadRewardTypes(bo_table);
        } else {
            alert(data.message || '추가 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}

function deleteRewardType(rwt_id) {
    if (!confirm('이 보상 유형을 삭제하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('mode', 'reward_type_delete');
    fd.append('rwt_id', rwt_id);

    fetch(_rewardUpdateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            loadRewardTypes(document.getElementById('f_bo_table').value);
        } else {
            alert(data.message || '삭제 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}

function escHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>

<?php } elseif ($tab == 'activity') { ?>
<!-- ================================ -->
<!-- 활동 보상 설정 -->
<!-- ================================ -->

<form method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/reward_update.php">
    <input type="hidden" name="mode" value="activity">

    <!-- 역극 보상 -->
    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header"><h3>역극 재화 설정</h3></div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">판 세우기 비용</label>
                    <input type="number" name="rp_create_cost" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['rp_create_cost']); ?>" min="0">
                    <small style="color:var(--mg-text-muted);">0 = 무료</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">잇기 보상 단위</label>
                    <input type="number" name="rp_reply_batch_count" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['rp_reply_batch_count']); ?>" min="1">
                    <small style="color:var(--mg-text-muted);">N개당 보상</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">잇기 보상량</label>
                    <input type="number" name="rp_reply_batch_point" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['rp_reply_batch_point']); ?>" min="0">
                    <small style="color:var(--mg-text-muted);">포인트</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">완결 보상</label>
                    <input type="number" name="rp_complete_point" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['rp_complete_point']); ?>" min="0">
                    <small style="color:var(--mg-text-muted);">캐릭터별 포인트</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">완결 최소 상호 이음</label>
                    <input type="number" name="rp_complete_min_mutual" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['rp_complete_min_mutual']); ?>" min="1">
                    <small style="color:var(--mg-text-muted);">상호 N회 이상</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">자동 완결 기한 (일)</label>
                    <input type="number" name="rp_auto_complete_days" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['rp_auto_complete_days']); ?>" min="1">
                    <small style="color:var(--mg-text-muted);">무활동 N일 후 자동</small>
                </div>
            </div>
        </div>
    </div>

    <!-- 재료 보상 전역 설정 -->
    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header"><h3>재료 보상 전역 설정 (개척 시스템)</h3></div>
        <div class="mg-card-body">
            <div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
                형식: <code>wood:1</code> (목재 1개 확정) 또는 <code>random:1:30</code> (30% 확률로 랜덤 재료 1개). 비워두면 비활성.
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">글 작성 시</label>
                    <input type="text" name="pioneer_write_reward" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['pioneer_write_reward']); ?>" placeholder="wood:1">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">댓글 작성 시</label>
                    <input type="text" name="pioneer_comment_reward" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['pioneer_comment_reward']); ?>" placeholder="random:1:30">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">역극 이음 시</label>
                    <input type="text" name="pioneer_rp_reward" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['pioneer_rp_reward']); ?>" placeholder="stone:1">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">출석 시</label>
                    <input type="text" name="pioneer_attendance_reward" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['pioneer_attendance_reward']); ?>" placeholder="random:1:100">
                </div>
            </div>
        </div>
    </div>

    <!-- 좋아요 보상 설정 -->
    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header"><h3>좋아요 보상 설정</h3></div>
        <div class="mg-card-body">
            <div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
                좋아요(추천) 시 누른 사람과 받은 사람 모두에게 포인트를 지급합니다. 일일 횟수 제한을 초과하면 좋아요 자체는 가능하지만 보상만 미지급됩니다.
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">일일 횟수 제한</label>
                    <input type="number" name="like_daily_limit" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['like_daily_limit']); ?>" min="0">
                    <small style="color:var(--mg-text-muted);">0 = 보상 비활성</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">누른 사람 보상</label>
                    <input type="number" name="like_giver_point" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['like_giver_point']); ?>" min="0">
                    <small style="color:var(--mg-text-muted);">포인트</small>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">받은 사람 보상</label>
                    <input type="number" name="like_receiver_point" class="mg-form-input" value="<?php echo htmlspecialchars($activity_configs['like_receiver_point']); ?>" min="0">
                    <small style="color:var(--mg-text-muted);">포인트</small>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:1rem;">
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </div>
</form>

<?php } elseif ($tab == 'rp') { ?>
<!-- ================================ -->
<!-- 역극 보상 모니터링 -->
<!-- ================================ -->

<!-- 기간 필터 -->
<div style="display:flex;gap:0.5rem;margin-bottom:1rem;">
    <?php
    $periods = array('all' => '전체', 'today' => '오늘', 'week' => '이번주', 'month' => '이번달');
    foreach ($periods as $pk => $pv) {
        $active = ($rp_period == $pk) ? 'mg-btn-primary' : 'mg-btn-secondary';
        echo "<a href=\"?tab=rp&period={$pk}\" class=\"mg-btn {$active} mg-btn-sm\">{$pv}</a>";
    }
    ?>
</div>

<!-- 완결 기록 테이블 -->
<div class="mg-card" style="margin-bottom:1.5rem;">
    <div class="mg-card-header"><h3>완결 기록<?php if ($rp_total > 0) echo " ({$rp_total}건)"; ?></h3></div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <?php if (count($rp_completions) > 0) { ?>
        <table class="mg-table" style="min-width:800px;">
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>역극</th>
                    <th style="width:120px;">캐릭터</th>
                    <th style="width:100px;">소유자</th>
                    <th style="width:80px;">상호 이음</th>
                    <th style="width:80px;">보상</th>
                    <th style="width:70px;">방식</th>
                    <th style="width:70px;">상태</th>
                    <th style="width:130px;">완결일</th>
                    <th style="width:70px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rp_completions as $rc) {
                    $min_mutual = (int)mg_get_config('rp_complete_min_mutual', 5);
                    $mutual_ok = (int)$rc['rc_mutual_count'] >= $min_mutual;
                ?>
                <tr>
                    <td><?php echo $rc['rc_id']; ?></td>
                    <td>
                        <a href="<?php echo G5_BBS_URL; ?>/rp_list.php#rp-thread-<?php echo $rc['rt_id']; ?>" target="_blank" style="color:var(--mg-accent);">
                            <?php echo htmlspecialchars(mb_substr($rc['rt_title'] ?: '(삭제됨)', 0, 30)); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($rc['ch_name'] ?: '-'); ?></td>
                    <td>
                        <small><?php echo htmlspecialchars($rc['mb_nick'] ?: $rc['mb_id']); ?></small>
                    </td>
                    <td style="text-align:center;">
                        <span style="color:<?php echo $mutual_ok ? 'var(--mg-accent)' : 'var(--mg-text-muted)'; ?>;">
                            <?php echo (int)$rc['rc_mutual_count']; ?>회
                            <?php echo $mutual_ok ? '&check;' : '&cross;'; ?>
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($rc['rc_rewarded']) { ?>
                        <span style="color:var(--mg-accent);font-weight:600;">+<?php echo number_format($rc['rc_point']); ?>P</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($rc['rc_type'] == 'auto') { ?>
                        <span class="mg-badge">자동</span>
                        <?php } else { ?>
                        <span class="mg-badge mg-badge--info">수동</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($rc['rc_status'] == 'revoked') { ?>
                        <span class="mg-badge mg-badge--danger">회수</span>
                        <?php } else { ?>
                        <span class="mg-badge mg-badge--success">완료</span>
                        <?php } ?>
                    </td>
                    <td><small><?php echo $rc['rc_datetime']; ?></small></td>
                    <td style="text-align:center;">
                        <?php if ($rc['rc_status'] == 'completed' && $rc['rc_rewarded']) { ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="revokeReward(<?php echo $rc['rc_id']; ?>, <?php echo (int)$rc['rc_point']; ?>, '<?php echo sql_real_escape_string($rc['mb_id']); ?>')">회수</button>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 페이징 -->
        <?php if ($rp_total_page > 1) { ?>
        <div style="padding:1rem;text-align:center;">
            <?php for ($i = 1; $i <= $rp_total_page; $i++) { ?>
            <a href="?tab=rp&period=<?php echo $rp_period; ?>&rp_page=<?php echo $i; ?>"
               class="mg-btn <?php echo $i == $rp_page ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"
               style="min-width:32px;"><?php echo $i; ?></a>
            <?php } ?>
        </div>
        <?php } ?>

        <?php } else { ?>
        <div style="text-align:center;padding:3rem 2rem;color:var(--mg-text-muted);">
            완결 기록이 없습니다.
        </div>
        <?php } ?>
    </div>
</div>

<!-- 잇기 보상 로그 -->
<div class="mg-card">
    <div class="mg-card-header"><h3>잇기 보상 로그 (최근 20건)</h3></div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <?php if (count($rp_reply_logs) > 0) { ?>
        <table class="mg-table">
            <thead>
                <tr>
                    <th>역극</th>
                    <th style="width:100px;">달성 이음수</th>
                    <th style="width:100px;">인당 보상</th>
                    <th style="width:150px;">지급일</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rp_reply_logs as $rl) { ?>
                <tr>
                    <td>
                        <a href="<?php echo G5_BBS_URL; ?>/rp_list.php#rp-thread-<?php echo $rl['rt_id']; ?>" target="_blank" style="color:var(--mg-accent);">
                            <?php echo htmlspecialchars(mb_substr($rl['rt_title'] ?: '(삭제됨)', 0, 30)); ?>
                        </a>
                    </td>
                    <td style="text-align:center;"><?php echo number_format($rl['rrl_reply_count']); ?>개</td>
                    <td style="text-align:center;color:var(--mg-accent);">+<?php echo number_format($rl['rrl_point']); ?>P</td>
                    <td><small><?php echo $rl['rrl_datetime']; ?></small></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
        <div style="text-align:center;padding:2rem;color:var(--mg-text-muted);">
            잇기 보상 기록이 없습니다.
        </div>
        <?php } ?>
    </div>
</div>

<script>
function revokeReward(rcId, point, mbId) {
    if (!confirm('이 완결 보상 ' + point + 'P를 회수하시겠습니까?\n해당 회원에게서 포인트가 차감됩니다.')) return;

    var formData = new FormData();
    formData.append('mode', 'revoke_completion');
    formData.append('rc_id', rcId);

    fetch('<?php echo G5_ADMIN_URL; ?>/morgan/reward_update.php', {
        method: 'POST',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(function() { alert('요청 실패'); });
}
</script>

<?php } elseif ($tab == 'like') { ?>
<!-- ================================ -->
<!-- 좋아요 보상 로그 -->
<!-- ================================ -->

<!-- 기간 필터 -->
<div style="display:flex;gap:0.5rem;margin-bottom:1rem;">
    <?php
    $periods = array('all' => '전체', 'today' => '오늘', 'week' => '이번주', 'month' => '이번달');
    foreach ($periods as $pk => $pv) {
        $active = ($like_period == $pk) ? 'mg-btn-primary' : 'mg-btn-secondary';
        echo "<a href=\"?tab=like&period={$pk}\" class=\"mg-btn {$active} mg-btn-sm\">{$pv}</a>";
    }
    ?>
</div>

<div class="mg-card">
    <div class="mg-card-header"><h3>좋아요 보상 로그<?php if ($like_total > 0) echo " ({$like_total}건)"; ?></h3></div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <?php if (count($like_logs) > 0) { ?>
        <table class="mg-table" style="min-width:700px;">
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>누른 회원</th>
                    <th>받은 회원</th>
                    <th style="width:100px;">게시판</th>
                    <th style="width:80px;">글 ID</th>
                    <th style="width:100px;">누른 사람 보상</th>
                    <th style="width:100px;">받은 사람 보상</th>
                    <th style="width:150px;">일시</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($like_logs as $ll) { ?>
                <tr>
                    <td><?php echo $ll['ll_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($ll['giver_nick'] ?: $ll['mb_id']); ?></strong>
                        <br><small style="color:var(--mg-text-muted);"><?php echo $ll['mb_id']; ?></small>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($ll['receiver_nick'] ?: $ll['target_mb_id']); ?></strong>
                        <br><small style="color:var(--mg-text-muted);"><?php echo $ll['target_mb_id']; ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($ll['bo_table']); ?></td>
                    <td style="text-align:center;">
                        <a href="<?php echo get_pretty_url($ll['bo_table'], $ll['wr_id']); ?>" target="_blank" style="color:var(--mg-accent);"><?php echo $ll['wr_id']; ?></a>
                    </td>
                    <td style="text-align:center;color:var(--mg-accent);">+<?php echo number_format($ll['ll_giver_point']); ?>P</td>
                    <td style="text-align:center;color:var(--mg-accent);">+<?php echo number_format($ll['ll_receiver_point']); ?>P</td>
                    <td><small><?php echo $ll['ll_datetime']; ?></small></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 페이징 -->
        <?php if ($like_total_page > 1) { ?>
        <div style="padding:1rem;text-align:center;">
            <?php for ($i = 1; $i <= $like_total_page; $i++) { ?>
            <a href="?tab=like&period=<?php echo $like_period; ?>&like_page=<?php echo $i; ?>"
               class="mg-btn <?php echo $i == $like_page ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"
               style="min-width:32px;"><?php echo $i; ?></a>
            <?php } ?>
        </div>
        <?php } ?>

        <?php } else { ?>
        <div style="text-align:center;padding:3rem 2rem;color:var(--mg-text-muted);">
            좋아요 보상 기록이 없습니다.
        </div>
        <?php } ?>
    </div>
</div>

<?php } elseif ($tab == 'settlement') { ?>
<!-- ================================ -->
<!-- 정산 대기열 -->
<!-- ================================ -->

<!-- 필터 -->
<div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1rem;align-items:center;">
    <span style="font-weight:600;font-size:0.9rem;margin-right:0.25rem;">상태:</span>
    <?php
    $statuses = array('all' => '전체', 'pending' => '대기', 'approved' => '승인', 'rejected' => '반려');
    foreach ($statuses as $sk => $sv) {
        $active = ($stl_status == $sk) ? 'mg-btn-primary' : 'mg-btn-secondary';
        echo "<a href=\"?tab=settlement&status={$sk}&bo={$stl_bo}&period={$stl_period}\" class=\"mg-btn {$active} mg-btn-sm\">{$sv}</a>";
    }
    ?>
    <span style="margin-left:0.5rem;font-weight:600;font-size:0.9rem;">게시판:</span>
    <select id="stl_bo_filter" class="mg-form-input" style="width:140px;padding:4px 8px;font-size:0.85rem;" onchange="stlFilterChange()">
        <option value="">전체</option>
        <?php foreach ($boards as $b) { ?>
        <option value="<?php echo $b['bo_table']; ?>" <?php echo $stl_bo == $b['bo_table'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($b['bo_subject']); ?></option>
        <?php } ?>
    </select>
    <span style="margin-left:0.5rem;font-weight:600;font-size:0.9rem;">기간:</span>
    <?php
    $periods = array('all' => '전체', 'today' => '오늘', 'week' => '이번주', 'month' => '이번달');
    foreach ($periods as $pk => $pv) {
        $active = ($stl_period == $pk) ? 'mg-btn-primary' : 'mg-btn-secondary';
        echo "<a href=\"?tab=settlement&status={$stl_status}&bo={$stl_bo}&period={$pk}\" class=\"mg-btn {$active} mg-btn-sm\">{$pv}</a>";
    }
    ?>
</div>

<?php if ($stl_status == 'all' || $stl_status == 'pending') { ?>
<div style="margin-bottom:1rem;">
    <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="batchApprove()">선택 일괄 승인</button>
</div>
<?php } ?>

<div class="mg-card">
    <div class="mg-card-header"><h3>정산 대기열<?php if ($stl_total > 0) echo " ({$stl_total}건)"; ?></h3></div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <?php if (count($stl_queue) > 0) { ?>
        <table class="mg-table" style="min-width:900px;">
            <thead>
                <tr>
                    <th style="width:35px;"><input type="checkbox" id="stl_check_all" onchange="stlToggleAll(this)"></th>
                    <th style="width:50px;">ID</th>
                    <th style="width:120px;">요청자</th>
                    <th style="width:100px;">게시판</th>
                    <th>글 제목</th>
                    <th style="width:140px;">보상 유형</th>
                    <th style="width:130px;">요청일</th>
                    <th style="width:80px;">상태</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stl_queue as $rq) {
                    $status_badge = array(
                        'pending' => '<span class="mg-badge mg-badge--warning">대기</span>',
                        'approved' => '<span class="mg-badge mg-badge--success">승인</span>',
                        'rejected' => '<span class="mg-badge mg-badge--danger">반려</span>',
                    );
                ?>
                <tr>
                    <td style="text-align:center;">
                        <?php if ($rq['rq_status'] == 'pending') { ?>
                        <input type="checkbox" class="stl-check" value="<?php echo $rq['rq_id']; ?>">
                        <?php } ?>
                    </td>
                    <td><?php echo $rq['rq_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($rq['mb_nick'] ?: $rq['mb_id']); ?></strong>
                        <br><small style="color:var(--mg-text-muted);"><?php echo $rq['mb_id']; ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($rq['bo_table']); ?></td>
                    <td>
                        <a href="<?php echo get_pretty_url($rq['bo_table'], $rq['wr_id']); ?>" target="_blank" style="color:var(--mg-accent);">
                            <?php echo htmlspecialchars(mb_substr($rq['wr_subject'], 0, 30)); ?>
                        </a>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($rq['rwt_name'] ?: '(삭제됨)'); ?>
                        <?php if ($rq['rwt_point']) { ?>
                        <span style="color:var(--mg-accent);font-weight:600;">(+<?php echo number_format($rq['rwt_point']); ?>P)</span>
                        <?php } ?>
                    </td>
                    <td><small><?php echo $rq['rq_datetime']; ?></small></td>
                    <td style="text-align:center;">
                        <?php echo $status_badge[$rq['rq_status']] ?? $rq['rq_status']; ?>
                        <?php if ($rq['rq_status'] == 'rejected' && $rq['rq_reject_reason']) { ?>
                        <br><small style="color:var(--mg-text-muted);" title="<?php echo htmlspecialchars($rq['rq_reject_reason']); ?>">사유 있음</small>
                        <?php } ?>
                        <?php if ($rq['rq_process_datetime']) { ?>
                        <br><small style="color:var(--mg-text-muted);"><?php echo substr($rq['rq_process_datetime'], 5); ?></small>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($rq['rq_status'] == 'pending') { ?>
                        <button type="button" class="mg-btn mg-btn-primary mg-btn-sm" style="padding:2px 8px;font-size:0.8rem;" onclick="stlApprove(<?php echo $rq['rq_id']; ?>)">승인</button>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" style="padding:2px 8px;font-size:0.8rem;" onclick="stlReject(<?php echo $rq['rq_id']; ?>)">반려</button>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">-</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- 페이징 -->
        <?php if ($stl_total_page > 1) { ?>
        <div style="padding:1rem;text-align:center;">
            <?php for ($i = 1; $i <= $stl_total_page; $i++) { ?>
            <a href="?tab=settlement&status=<?php echo $stl_status; ?>&bo=<?php echo $stl_bo; ?>&period=<?php echo $stl_period; ?>&stl_page=<?php echo $i; ?>"
               class="mg-btn <?php echo $i == $stl_page ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"
               style="min-width:32px;"><?php echo $i; ?></a>
            <?php } ?>
        </div>
        <?php } ?>

        <?php } else { ?>
        <div style="text-align:center;padding:3rem 2rem;color:var(--mg-text-muted);">
            정산 대기 항목이 없습니다.
        </div>
        <?php } ?>
    </div>
</div>

<!-- 반려 사유 모달 -->
<div id="reject-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:400px;">
        <div class="mg-modal-header">
            <h3>보상 반려</h3>
            <button type="button" class="mg-modal-close" onclick="document.getElementById('reject-modal').style.display='none'">&times;</button>
        </div>
        <div class="mg-modal-body">
            <div class="mg-form-group">
                <label class="mg-form-label">반려 사유</label>
                <textarea id="reject_reason" class="mg-form-input" rows="3" placeholder="반려 사유를 입력하세요 (선택)"></textarea>
            </div>
        </div>
        <div class="mg-modal-footer">
            <button type="button" class="mg-btn mg-btn-secondary" onclick="document.getElementById('reject-modal').style.display='none'">취소</button>
            <button type="button" class="mg-btn mg-btn-danger" onclick="stlRejectSubmit()">반려 확인</button>
        </div>
    </div>
</div>

<script>
var _stlUpdateUrl = '<?php echo G5_ADMIN_URL; ?>/morgan/reward_update.php';
var _stlRejectId = 0;

function stlFilterChange() {
    var bo = document.getElementById('stl_bo_filter').value;
    location.href = '?tab=settlement&status=<?php echo $stl_status; ?>&bo=' + encodeURIComponent(bo) + '&period=<?php echo $stl_period; ?>';
}

function stlToggleAll(el) {
    document.querySelectorAll('.stl-check').forEach(function(cb) { cb.checked = el.checked; });
}

function stlApprove(rqId) {
    if (!confirm('이 요청을 승인하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('mode', 'approve_reward');
    fd.append('rq_id', rqId);

    fetch(_stlUpdateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message || '승인되었습니다.');
            location.reload();
        } else {
            alert(data.message || '승인 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}

function stlReject(rqId) {
    _stlRejectId = rqId;
    document.getElementById('reject_reason').value = '';
    document.getElementById('reject-modal').style.display = 'flex';
}

function stlRejectSubmit() {
    if (!_stlRejectId) return;

    var fd = new FormData();
    fd.append('mode', 'reject_reward');
    fd.append('rq_id', _stlRejectId);
    fd.append('reason', document.getElementById('reject_reason').value);

    fetch(_stlUpdateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        document.getElementById('reject-modal').style.display = 'none';
        if (data.success) {
            alert(data.message || '반려되었습니다.');
            location.reload();
        } else {
            alert(data.message || '반려 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}

function batchApprove() {
    var ids = [];
    document.querySelectorAll('.stl-check:checked').forEach(function(cb) { ids.push(cb.value); });
    if (!ids.length) { alert('선택된 항목이 없습니다.'); return; }
    if (!confirm(ids.length + '건을 일괄 승인하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('mode', 'batch_approve');
    ids.forEach(function(id) { fd.append('rq_ids[]', id); });

    fetch(_stlUpdateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || '일괄 승인 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}

document.getElementById('reject-modal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
<?php } ?>


<?php
require_once __DIR__.'/_tail.php';
?>
