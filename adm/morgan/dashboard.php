<?php
/**
 * Morgan Edition - 관리자 대시보드
 */

$sub_menu = "800050";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 상대시간 헬퍼
function mg_time_ago($datetime) {
    if (!$datetime || $datetime == '0000-00-00 00:00:00') return '-';
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return '방금 전';
    if ($diff < 3600) return floor($diff/60).'분 전';
    if ($diff < 86400) return floor($diff/3600).'시간 전';
    if ($diff < 604800) return floor($diff/86400).'일 전';
    return date('m.d', strtotime($datetime));
}

// ─── 통계 데이터 ───
$stat_members = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['member_table']}")['cnt'];
$stat_pending = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE ch_state = 'pending'")['cnt'];
$stat_rp_open = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_rp_thread_table']} WHERE rt_status = 'open'")['cnt'];
$stat_point_today = sql_fetch("SELECT COALESCE(SUM(po_point),0) as total FROM {$g5['point_table']} WHERE po_point > 0 AND DATE(po_datetime) = CURDATE()")['total'];
$stat_reward_pending = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_reward_queue_table']} WHERE rq_status = 'pending'")['cnt'];
$stat_like_today = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_like_log_table']} WHERE DATE(ll_datetime) = CURDATE()")['cnt'];

// ─── 승인 요청 캐릭터 ───
$pending_chars = array();
$result = sql_query("SELECT c.ch_id, c.ch_name, c.ch_thumb, c.ch_datetime, c.mb_id, m.mb_nick
    FROM {$g5['mg_character_table']} c
    LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
    WHERE c.ch_state = 'pending'
    ORDER BY c.ch_datetime DESC LIMIT 5");
while ($row = sql_fetch_array($result)) {
    $pending_chars[] = $row;
}

// ─── 최신 게시글 ───
$recent_posts = array();
$result = sql_query("SELECT bn.bo_table, bn.wr_id, bn.bn_datetime, b.bo_subject
    FROM {$g5['board_new_table']} bn
    LEFT JOIN {$g5['board_table']} b ON bn.bo_table = b.bo_table
    WHERE bn.wr_parent = bn.wr_id
    ORDER BY bn.bn_datetime DESC LIMIT 8");
while ($row = sql_fetch_array($result)) {
    $write_table = $g5['write_prefix'] . $row['bo_table'];
    $write = sql_fetch("SELECT wr_subject, mb_id FROM {$write_table} WHERE wr_id = {$row['wr_id']}");
    if (isset($write['wr_subject']) && $write['wr_subject']) {
        $row['wr_subject'] = $write['wr_subject'];
        $row['mb_id'] = $write['mb_id'];
        $nick_row = sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '{$write['mb_id']}'");
        $row['mb_nick'] = isset($nick_row['mb_nick']) ? $nick_row['mb_nick'] : $write['mb_id'];
        $recent_posts[] = $row;
    }
}

// ─── 최신 역극 ───
$recent_rps = array();
$result = sql_query("SELECT t.rt_id, t.rt_title, t.rt_status, t.rt_reply_count, t.rt_update, t.mb_id, m.mb_nick
    FROM {$g5['mg_rp_thread_table']} t
    LEFT JOIN {$g5['member_table']} m ON t.mb_id = m.mb_id
    WHERE t.rt_status != 'deleted'
    ORDER BY t.rt_update DESC LIMIT 5");
while ($row = sql_fetch_array($result)) {
    $recent_rps[] = $row;
}

// ─── 최근 포인트 발급 ───
$recent_points = array();
$result = sql_query("SELECT p.po_point, p.po_content, p.po_datetime, p.mb_id, m.mb_nick
    FROM {$g5['point_table']} p
    LEFT JOIN {$g5['member_table']} m ON p.mb_id = m.mb_id
    WHERE p.po_point > 0
    ORDER BY p.po_id DESC LIMIT 8");
while ($row = sql_fetch_array($result)) {
    $recent_points[] = $row;
}

// ─── 최근 구매 내역 ───
$recent_purchases = array();
$result = sql_query("SELECT sl.sl_price, sl.sl_datetime, sl.mb_id, m.mb_nick, i.si_name
    FROM {$g5['mg_shop_log_table']} sl
    LEFT JOIN {$g5['member_table']} m ON sl.mb_id = m.mb_id
    LEFT JOIN {$g5['mg_shop_item_table']} i ON sl.si_id = i.si_id
    WHERE sl.sl_type = 'purchase'
    ORDER BY sl.sl_id DESC LIMIT 5");
while ($row = sql_fetch_array($result)) {
    $recent_purchases[] = $row;
}

// ─── 업적 통계 ───
$stat_achievement_total = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_achievement_table']} WHERE ac_use = 1")['cnt'];
$stat_achievement_today = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_user_achievement_table']} WHERE ua_completed = 1 AND DATE(ua_datetime) = CURDATE()")['cnt'];

// ─── 최근 업적 달성 ───
$recent_achievements = array();
$result = sql_query("SELECT ua.ua_id, ua.mb_id, ua.ua_tier, ua.ua_granted_by, ua.ua_datetime,
        a.ac_name, a.ac_category, a.ac_type, a.ac_rarity, m.mb_nick
    FROM {$g5['mg_user_achievement_table']} ua
    LEFT JOIN {$g5['mg_achievement_table']} a ON ua.ac_id = a.ac_id
    LEFT JOIN {$g5['member_table']} m ON ua.mb_id = m.mb_id
    WHERE ua.ua_completed = 1
    ORDER BY ua.ua_datetime DESC LIMIT 5");
while ($row = sql_fetch_array($result)) {
    // 단계형이면 단계 이름 조회
    if ($row['ac_type'] == 'progressive' && $row['ua_tier'] > 0) {
        $tier = sql_fetch("SELECT at_name FROM {$g5['mg_achievement_tier_table']}
            WHERE ac_id = (SELECT ac_id FROM {$g5['mg_user_achievement_table']} WHERE ua_id = {$row['ua_id']})
            AND at_level = {$row['ua_tier']}");
        if ($tier) $row['tier_name'] = $tier['at_name'];
    }
    $recent_achievements[] = $row;
}

// ─── 정산 대기열 (pending) ───
$pending_rewards = array();
$result = sql_query("SELECT rq.rq_id, rq.mb_id, rq.bo_table, rq.rq_datetime, rt.rwt_name, rt.rwt_point, m.mb_nick
    FROM {$g5['mg_reward_queue_table']} rq
    LEFT JOIN {$g5['mg_reward_type_table']} rt ON rq.rwt_id = rt.rwt_id
    LEFT JOIN {$g5['member_table']} m ON rq.mb_id = m.mb_id
    WHERE rq.rq_status = 'pending'
    ORDER BY rq.rq_datetime DESC LIMIT 5");
while ($row = sql_fetch_array($result)) {
    $pending_rewards[] = $row;
}

// ─── 최근 역극 완결 ───
$recent_completions = array();
$result = sql_query("SELECT rc.rc_id, rc.rc_point, rc.rc_rewarded, rc.rc_type, rc.rc_datetime,
        t.rt_id, t.rt_title, c.ch_name
    FROM {$g5['mg_rp_completion_table']} rc
    LEFT JOIN {$g5['mg_rp_thread_table']} t ON rc.rt_id = t.rt_id
    LEFT JOIN {$g5['mg_character_table']} c ON rc.ch_id = c.ch_id
    ORDER BY rc.rc_datetime DESC LIMIT 5");
while ($row = sql_fetch_array($result)) {
    $recent_completions[] = $row;
}

$g5['title'] = '대시보드';
require_once __DIR__.'/_head.php';
?>

<style>
.mg-dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
    margin-top: 1.25rem;
}
@media (max-width: 900px) {
    .mg-dashboard-grid { grid-template-columns: 1fr; }
}
.mg-widget { min-width: 0; }
.mg-widget .mg-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mg-widget .mg-card-header a {
    font-size: 0.75rem;
    color: var(--mg-text-muted);
    text-decoration: none;
}
.mg-widget .mg-card-header a:hover { color: var(--mg-accent); }
.mg-widget .mg-card-body { padding: 0; }
.mg-widget-list {
    list-style: none;
    margin: 0;
    padding: 0;
}
.mg-widget-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.625rem 1rem;
    border-bottom: 1px solid var(--mg-bg-tertiary);
    font-size: 0.8125rem;
}
.mg-widget-list li:last-child { border-bottom: none; }
.mg-widget-list .wl-main {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--mg-text-primary);
}
.mg-widget-list .wl-main a {
    color: var(--mg-text-primary);
    text-decoration: none;
}
.mg-widget-list .wl-main a:hover { color: var(--mg-accent); }
.mg-widget-list .wl-sub {
    color: var(--mg-text-muted);
    font-size: 0.75rem;
    flex-shrink: 0;
}
.mg-widget-list .wl-nick {
    color: var(--mg-text-secondary);
    font-size: 0.75rem;
    flex-shrink: 0;
    max-width: 80px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.mg-widget-list .wl-badge {
    display: inline-block;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.6875rem;
    font-weight: 600;
    flex-shrink: 0;
}
.mg-widget-list .wl-board {
    display: inline-block;
    padding: 0.125rem 0.375rem;
    background: var(--mg-bg-tertiary);
    border-radius: 0.25rem;
    color: var(--mg-text-muted);
    font-size: 0.6875rem;
    flex-shrink: 0;
    margin-right: 0.25rem;
}
.mg-widget-list .wl-point {
    font-weight: 600;
    color: var(--mg-success);
    flex-shrink: 0;
}
.mg-widget-list .wl-price {
    font-weight: 600;
    color: var(--mg-accent);
    flex-shrink: 0;
}
.mg-widget-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--mg-text-muted);
    font-size: 0.8125rem;
}
.mg-stat-highlight {
    color: var(--mg-warning) !important;
    font-weight: 700;
}
</style>

<!-- 통계 카드 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 회원</div>
        <div class="mg-stat-value"><?php echo number_format($stat_members); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">승인 대기</div>
        <div class="mg-stat-value <?php echo $stat_pending > 0 ? 'mg-stat-highlight' : ''; ?>" style="<?php echo $stat_pending == 0 ? 'color:var(--mg-text-muted);' : ''; ?>"><?php echo number_format($stat_pending); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">진행 중 역극</div>
        <div class="mg-stat-value" style="color:var(--mg-success);"><?php echo number_format($stat_rp_open); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">오늘 발급 포인트</div>
        <div class="mg-stat-value"><?php echo number_format($stat_point_today); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">정산 대기</div>
        <div class="mg-stat-value <?php echo $stat_reward_pending > 0 ? 'mg-stat-highlight' : ''; ?>" style="<?php echo $stat_reward_pending == 0 ? 'color:var(--mg-text-muted);' : ''; ?>"><?php echo number_format($stat_reward_pending); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">오늘 좋아요</div>
        <div class="mg-stat-value"><?php echo number_format($stat_like_today); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">등록 업적</div>
        <div class="mg-stat-value"><?php echo number_format($stat_achievement_total); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">오늘 달성</div>
        <div class="mg-stat-value" style="<?php echo $stat_achievement_today == 0 ? 'color:var(--mg-text-muted);' : ''; ?>"><?php echo number_format($stat_achievement_today); ?></div>
    </div>
</div>

<!-- 위젯 그리드 -->
<div class="mg-dashboard-grid">
    <!-- 좌측 컬럼 -->
    <div class="mg-widget-col" style="display:flex;flex-direction:column;gap:1.25rem;">

        <!-- 승인 요청 캐릭터 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>승인 요청 캐릭터</h3>
                <a href="./character_list.php?state=pending">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($pending_chars)) { ?>
                <div class="mg-widget-empty">승인 대기 중인 캐릭터가 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($pending_chars as $pc) { ?>
                    <li>
                        <span class="wl-main">
                            <a href="./character_form.php?ch_id=<?php echo $pc['ch_id']; ?>"><?php echo htmlspecialchars($pc['ch_name']); ?></a>
                        </span>
                        <span class="wl-nick"><?php echo htmlspecialchars($pc['mb_nick'] ?: $pc['mb_id']); ?></span>
                        <span class="wl-sub"><?php echo mg_time_ago($pc['ch_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>

        <!-- 최근 포인트 발급 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>최근 포인트 발급</h3>
                <a href="./point_manage.php">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($recent_points)) { ?>
                <div class="mg-widget-empty">포인트 발급 내역이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($recent_points as $pt) { ?>
                    <li>
                        <span class="wl-nick"><?php echo htmlspecialchars($pt['mb_nick'] ?: $pt['mb_id']); ?></span>
                        <span class="wl-point">+<?php echo number_format($pt['po_point']); ?></span>
                        <span class="wl-main"><?php echo htmlspecialchars(mb_strimwidth($pt['po_content'], 0, 30, '...')); ?></span>
                        <span class="wl-sub"><?php echo mg_time_ago($pt['po_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>

        <!-- 최근 구매 내역 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>최근 구매 내역</h3>
                <a href="./shop_log.php">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($recent_purchases)) { ?>
                <div class="mg-widget-empty">구매 내역이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($recent_purchases as $pu) { ?>
                    <li>
                        <span class="wl-nick"><?php echo htmlspecialchars($pu['mb_nick'] ?: $pu['mb_id']); ?></span>
                        <span class="wl-main"><?php echo htmlspecialchars($pu['si_name'] ?: '(삭제된 상품)'); ?></span>
                        <span class="wl-price"><?php echo number_format($pu['sl_price']); ?>P</span>
                        <span class="wl-sub"><?php echo mg_time_ago($pu['sl_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 우측 컬럼 -->
    <div class="mg-widget-col" style="display:flex;flex-direction:column;gap:1.25rem;">

        <!-- 최신 게시글 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>최신 게시글</h3>
                <a href="./board_list.php">게시판 관리 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($recent_posts)) { ?>
                <div class="mg-widget-empty">게시글이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($recent_posts as $post) { ?>
                    <li>
                        <span class="wl-board"><?php echo htmlspecialchars($post['bo_subject'] ?: $post['bo_table']); ?></span>
                        <span class="wl-main">
                            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $post['bo_table']; ?>&wr_id=<?php echo $post['wr_id']; ?>" target="_blank"><?php echo htmlspecialchars(mb_strimwidth($post['wr_subject'], 0, 40, '...')); ?></a>
                        </span>
                        <span class="wl-nick"><?php echo htmlspecialchars($post['mb_nick']); ?></span>
                        <span class="wl-sub"><?php echo mg_time_ago($post['bn_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>

        <!-- 최신 역극 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>최신 역극</h3>
                <a href="./rp_list.php">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($recent_rps)) { ?>
                <div class="mg-widget-empty">역극이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($recent_rps as $rp) { ?>
                    <li>
                        <?php if ($rp['rt_status'] == 'open') { ?>
                        <span class="wl-badge" style="background:rgba(34,197,94,0.15);color:var(--mg-success);">진행</span>
                        <?php } else { ?>
                        <span class="wl-badge" style="background:var(--mg-bg-tertiary);color:var(--mg-text-muted);">완결</span>
                        <?php } ?>
                        <span class="wl-main">
                            <a href="<?php echo G5_BBS_URL; ?>/rp_list.php#rp-thread-<?php echo $rp['rt_id']; ?>" target="_blank"><?php echo htmlspecialchars(mb_strimwidth($rp['rt_title'], 0, 30, '...')); ?></a>
                        </span>
                        <span class="wl-nick"><?php echo htmlspecialchars($rp['mb_nick'] ?: $rp['mb_id']); ?></span>
                        <span class="wl-sub" title="이음 <?php echo $rp['rt_reply_count']; ?>개"><?php echo $rp['rt_reply_count']; ?>이음</span>
                        <span class="wl-sub"><?php echo mg_time_ago($rp['rt_update']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>

        <!-- 정산 대기열 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>정산 대기열<?php if ($stat_reward_pending > 0) echo ' <span style="color:var(--mg-warning);font-size:0.8rem;">('.$stat_reward_pending.')</span>'; ?></h3>
                <a href="./reward.php?tab=settlement">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($pending_rewards)) { ?>
                <div class="mg-widget-empty">대기 중인 정산 요청이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($pending_rewards as $rq) { ?>
                    <li>
                        <span class="wl-badge" style="background:rgba(234,179,8,0.15);color:var(--mg-warning);">대기</span>
                        <span class="wl-nick"><?php echo htmlspecialchars($rq['mb_nick'] ?: $rq['mb_id']); ?></span>
                        <span class="wl-main"><?php echo htmlspecialchars($rq['rwt_name'] ?: '(삭제됨)'); ?></span>
                        <span class="wl-point">+<?php echo number_format($rq['rwt_point']); ?>P</span>
                        <span class="wl-sub"><?php echo mg_time_ago($rq['rq_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>

        <!-- 최근 역극 완결 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>최근 역극 완결</h3>
                <a href="./reward.php?tab=rp">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($recent_completions)) { ?>
                <div class="mg-widget-empty">완결 기록이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($recent_completions as $rc) { ?>
                    <li>
                        <?php if ($rc['rc_type'] == 'auto') { ?>
                        <span class="wl-badge" style="background:var(--mg-bg-tertiary);color:var(--mg-text-muted);">자동</span>
                        <?php } else { ?>
                        <span class="wl-badge" style="background:rgba(99,102,241,0.15);color:var(--mg-info);">수동</span>
                        <?php } ?>
                        <span class="wl-main">
                            <a href="<?php echo G5_BBS_URL; ?>/rp_list.php#rp-thread-<?php echo $rc['rt_id']; ?>" target="_blank"><?php echo htmlspecialchars(mb_strimwidth($rc['rt_title'] ?: '(삭제됨)', 0, 25, '...')); ?></a>
                        </span>
                        <span class="wl-nick"><?php echo htmlspecialchars($rc['ch_name'] ?: '-'); ?></span>
                        <?php if ($rc['rc_rewarded']) { ?>
                        <span class="wl-point">+<?php echo number_format($rc['rc_point']); ?>P</span>
                        <?php } else { ?>
                        <span class="wl-sub">보상 없음</span>
                        <?php } ?>
                        <span class="wl-sub"><?php echo mg_time_ago($rc['rc_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>

        <!-- 최근 업적 달성 -->
        <div class="mg-card mg-widget">
            <div class="mg-card-header">
                <h3>최근 업적 달성</h3>
                <a href="./achievement.php">전체보기 &rarr;</a>
            </div>
            <div class="mg-card-body">
                <?php if (empty($recent_achievements)) { ?>
                <div class="mg-widget-empty">업적 달성 기록이 없습니다.</div>
                <?php } else { ?>
                <ul class="mg-widget-list">
                    <?php foreach ($recent_achievements as $ra) {
                        $ach_name = isset($ra['tier_name']) ? $ra['tier_name'] : $ra['ac_name'];
                        $ach_rarity_css = array(
                            'common' => 'color:var(--mg-text-muted)',
                            'uncommon' => 'color:#22c55e',
                            'rare' => 'color:#3b82f6',
                            'epic' => 'color:#a855f7',
                            'legendary' => 'color:#f59e0b',
                        );
                        $r_style = $ach_rarity_css[$ra['ac_rarity']] ?? 'color:var(--mg-text-muted)';
                    ?>
                    <li>
                        <?php if ($ra['ua_granted_by']) { ?>
                        <span class="wl-badge" style="background:rgba(245,159,10,0.15);color:var(--mg-accent);">수동</span>
                        <?php } else { ?>
                        <span class="wl-badge" style="background:var(--mg-bg-tertiary);color:var(--mg-text-muted);">자동</span>
                        <?php } ?>
                        <span class="wl-nick"><?php echo htmlspecialchars($ra['mb_nick'] ?: $ra['mb_id']); ?></span>
                        <span class="wl-main" style="<?php echo $r_style; ?>;font-weight:600;"><?php echo htmlspecialchars($ach_name); ?></span>
                        <span class="wl-sub"><?php echo mg_time_ago($ra['ua_datetime']); ?></span>
                    </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__.'/_tail.php';
?>
