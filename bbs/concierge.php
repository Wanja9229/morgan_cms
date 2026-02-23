<?php
/**
 * Morgan Edition - 의뢰 게시판 (리디자인)
 *
 * 3탭 구조:
 *   market  — 의뢰 목록 (인라인 카드, 지원/매칭)
 *   my      — 내 의뢰 대시보드 (등록한/지원한)
 *   results — 의뢰 수행 (concierge_result 게시판)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (mg_config('concierge_use', '1') != '1') {
    alert_close('의뢰 기능이 비활성화되어 있습니다.');
}
if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$tab = isset($_GET['tab']) ? clean_xss_tags($_GET['tab']) : 'market';
if (!in_array($tab, array('market', 'my', 'results'))) $tab = 'market';

$open_cc_id = isset($_GET['cc_id']) ? (int)$_GET['cc_id'] : 0;
$open_write = !empty($_GET['write']);

// Config
$cfg_match_mode = mg_config('concierge_match_mode_allowed', 'both');
$cfg_anonymous  = mg_config('concierge_apply_anonymous', '0') === '1';
$cfg_point_min  = (int)mg_config('concierge_point_min', 0);
$cfg_point_max  = (int)mg_config('concierge_point_max', 1000);
$cfg_fee_rate   = (int)mg_config('concierge_fee_rate', 0);

$mb_id    = $member['mb_id'];
$mb_point = (int)$member['mb_point'];
$my_characters = mg_get_usable_characters($mb_id);

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');

// Helper
function cc_status_badge($status) {
    $map = array(
        'recruiting'   => array('bg-orange-500/10 text-orange-400 border-orange-500/20', '모집중'),
        'matched'      => array('bg-blue-500/10 text-blue-400 border-blue-500/20', '진행중'),
        'completed'    => array('bg-green-500/10 text-green-400 border-green-500/20', '완료'),
        'expired'      => array('bg-gray-500/10 text-gray-500 border-gray-500/20', '만료'),
        'cancelled'    => array('bg-gray-500/10 text-gray-500 border-gray-500/20', '취소'),
        'force_closed' => array('bg-red-500/10 text-red-400 border-red-500/20', '미이행'),
    );
    return isset($map[$status]) ? $map[$status] : array('', $status);
}

function cc_deadline_label($deadline, $status = 'recruiting') {
    if (empty($deadline)) return array('', '');
    $diff = strtotime($deadline) - time();
    if ($status === 'recruiting') {
        if ($diff <= 0) return array('text-red-400 bg-red-950/50', '마감');
        if ($diff < 86400) return array('text-red-400 bg-red-950/50', floor($diff / 3600) . 'h');
        if ($diff < 86400 * 3) return array('text-red-400 bg-red-950/50', 'D-' . ceil($diff / 86400));
        return array('text-gray-400 bg-mg-bg-primary', 'D-' . ceil($diff / 86400));
    }
    if ($status === 'matched' && $deadline) {
        if ($diff <= 0) return array('text-red-400 bg-red-950/50', '수행마감');
        if ($diff < 86400 * 3) return array('text-red-400 bg-red-950/50', 'D-' . ceil($diff / 86400));
        return array('text-gray-400 bg-mg-bg-primary', 'D-' . ceil($diff / 86400));
    }
    return array('', '');
}

function cc_is_urgent($deadline) {
    if (empty($deadline)) return false;
    $diff = strtotime($deadline) - time();
    return $diff > 0 && $diff < 86400;
}

// ── Tab data ──
if ($tab === 'market') {
    $search_type   = isset($_GET['type'])   ? clean_xss_tags($_GET['type'])   : '';
    $search_status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $result = mg_get_concierge_list($search_status ?: null, $search_type ?: null, $page, 20);

} elseif ($tab === 'my') {
    $mb_id_esc = sql_real_escape_string($mb_id);

    $my_owned = array();
    $sql = "SELECT cc.*, m.mb_nick, ch.ch_name, ch.ch_thumb,
                   (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id) as apply_count
            FROM {$g5['mg_concierge_table']} cc
            LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
            LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
            WHERE cc.mb_id = '{$mb_id_esc}'
            ORDER BY FIELD(cc.cc_status, 'recruiting', 'matched', 'completed', 'expired', 'cancelled', 'force_closed'), cc.cc_datetime DESC";
    $r = sql_query($sql);
    if ($r) { while ($row = sql_fetch_array($r)) $my_owned[] = $row; }

    $my_applied = array();
    $sql = "SELECT cc.*, m.mb_nick, ch.ch_name, ch.ch_thumb,
                   ca.ca_status, ca.ca_id, ca.ca_datetime as my_apply_datetime,
                   (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id) as apply_count
            FROM {$g5['mg_concierge_apply_table']} ca
            JOIN {$g5['mg_concierge_table']} cc ON ca.cc_id = cc.cc_id
            LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
            LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
            WHERE ca.mb_id = '{$mb_id_esc}'
            ORDER BY FIELD(ca.ca_status, 'selected', 'pending', 'rejected', 'force_closed'), ca.ca_datetime DESC";
    $r = sql_query($sql);
    if ($r) {
        while ($row = sql_fetch_array($r)) {
            $has_result = sql_fetch("SELECT cr_id FROM {$g5['mg_concierge_result_table']}
                                     WHERE cc_id = {$row['cc_id']} AND ca_id = {$row['ca_id']}");
            $row['has_result'] = ($has_result && $has_result['cr_id']) ? true : false;
            $my_applied[] = $row;
        }
    }

} elseif ($tab === 'results') {
    // 의뢰 수행 (concierge_result 게시판 인라인 표시)
    $_rt_board = function_exists('get_board_db') ? get_board_db('concierge_result', true) : null;
    $_rt_list = array();
    $_rt_cc_map = array();
    $_rt_ch_map = array();
    $_rt_total_count = 0;
    $_rt_total_pages = 1;
    $_rt_page = isset($_GET['rpage']) ? max(1, (int)$_GET['rpage']) : 1;

    if ($_rt_board) {
        $_rt_write_table = $g5['write_prefix'] . 'concierge_result';
        $_rt_per_page = 20;
        $_rt_offset = ($_rt_page - 1) * $_rt_per_page;

        $_rt_total_row = sql_fetch("SELECT COUNT(*) as cnt FROM {$_rt_write_table} WHERE wr_is_comment = 0");
        $_rt_total_count = (int)$_rt_total_row['cnt'];
        $_rt_total_pages = max(1, ceil($_rt_total_count / $_rt_per_page));

        $_rt_r = sql_query("SELECT wr_id, wr_subject, wr_name, wr_datetime, wr_hit, wr_comment
                            FROM {$_rt_write_table}
                            WHERE wr_is_comment = 0
                            ORDER BY wr_num, wr_reply
                            LIMIT {$_rt_offset}, {$_rt_per_page}");
        if ($_rt_r) { while ($row = sql_fetch_array($_rt_r)) $_rt_list[] = $row; }

        if ($_rt_list) {
            $wr_ids_str = implode(',', array_map(function($r) { return (int)$r['wr_id']; }, $_rt_list));

            $cr_r = sql_query("SELECT cr.wr_id, cr.cc_id, cc.cc_title, cc.cc_type
                               FROM {$g5['mg_concierge_result_table']} cr
                               JOIN {$g5['mg_concierge_table']} cc ON cr.cc_id = cc.cc_id
                               WHERE cr.bo_table = 'concierge_result' AND cr.wr_id IN ({$wr_ids_str})");
            if ($cr_r) { while ($row = sql_fetch_array($cr_r)) $_rt_cc_map[(int)$row['wr_id']] = $row; }

            $ch_r = sql_query("SELECT wc.wr_id, c.ch_name, c.ch_thumb
                               FROM {$g5['mg_write_character_table']} wc
                               JOIN {$g5['mg_character_table']} c ON wc.ch_id = c.ch_id
                               WHERE wc.bo_table = 'concierge_result' AND wc.wr_id IN ({$wr_ids_str})");
            if ($ch_r) { while ($row = sql_fetch_array($ch_r)) $_rt_ch_map[(int)$row['wr_id']] = $row; }
        }
    }
}

$g5['title'] = '의뢰 게시판';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 헤더 -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-mg-text-primary">의뢰 게시판</h1>
            <p class="text-sm text-mg-text-secondary mt-1">창작 협업 의뢰를 등록하고 지원하세요</p>
        </div>
        <button type="button" onclick="ccOpenModal()" class="px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors">
            의뢰 등록
        </button>
    </div>

    <!-- 탭 -->
    <div class="flex gap-1 mb-5 border-b border-mg-bg-tertiary">
        <?php
        $tabs = array('market' => '의뢰 목록', 'my' => '내 의뢰', 'results' => '의뢰 수행');
        foreach ($tabs as $tid => $tname) {
            $active = ($tab === $tid) ? 'border-mg-accent text-mg-accent' : 'border-transparent text-mg-text-muted hover:text-mg-text-secondary';
            echo '<a href="'.G5_BBS_URL.'/concierge.php?tab='.$tid.'" class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors '.$active.'">'.$tname.'</a>';
        }
        ?>
    </div>

<?php if ($tab === 'market') { ?>
    <!-- ═══ 의뢰 목록 탭 ═══ -->

    <!-- 필터 -->
    <div class="card mb-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="tab" value="market">
            <div>
                <label class="block text-xs text-mg-text-muted mb-1">상태</label>
                <select name="status" class="px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                    <option value="">전체</option>
                    <option value="recruiting" <?php echo $search_status === 'recruiting' ? 'selected' : ''; ?>>모집중</option>
                    <option value="matched" <?php echo $search_status === 'matched' ? 'selected' : ''; ?>>진행중</option>
                    <option value="completed" <?php echo $search_status === 'completed' ? 'selected' : ''; ?>>완료</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-mg-text-muted mb-1">유형</label>
                <select name="type" class="px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                    <option value="">전체</option>
                    <?php foreach ($type_labels as $k => $v) { ?>
                    <option value="<?php echo $k; ?>" <?php echo $search_type === $k ? 'selected' : ''; ?>><?php echo $v; ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm hover:bg-mg-accent/20 transition-colors">검색</button>
        </form>
    </div>

    <!-- 카드 그리드 -->
    <?php if (empty($result['items'])) { ?>
    <div class="card text-center py-12">
        <p class="text-mg-text-muted mb-3">등록된 의뢰가 없습니다.</p>
        <button type="button" onclick="ccOpenModal()" class="inline-block px-4 py-2 bg-mg-accent text-mg-bg-primary rounded-lg text-sm">첫 의뢰 등록하기</button>
    </div>
    <?php } else { ?>
    <div class="grid grid-cols-1 gap-5" style="grid-template-columns:repeat(1,minmax(0,1fr))" id="cc-grid">
        <?php foreach ($result['items'] as $item) {
            $sb = cc_status_badge($item['cc_status']);
            $dl = cc_deadline_label($item['cc_deadline'], $item['cc_status']);
            $tl = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];
            $is_hl = $item['cc_highlight'] && strtotime($item['cc_highlight']) > time();
            $urgent = cc_is_urgent($item['cc_deadline']) && $item['cc_status'] === 'recruiting';
            $done = in_array($item['cc_status'], array('completed', 'expired', 'cancelled', 'force_closed'));

            $hover_border = $urgent ? 'hover:border-red-500' : 'hover:border-mg-accent';
            $title_hover = $urgent ? 'group-hover:text-red-400' : 'group-hover:text-mg-accent';
        ?>
        <div class="bg-mg-bg-secondary border border-mg-bg-tertiary rounded-xl p-5 flex flex-col gap-3 group <?php echo $hover_border; ?> transition-all duration-300 cursor-pointer <?php if ($done) echo 'opacity-60'; ?> <?php if ($is_hl) echo 'ring-1 ring-mg-accent'; ?>"
             style="hover:transform:translateY(-4px)" onclick="ccOpenDetail(<?php echo $item['cc_id']; ?>)">
            <!-- 상태 + D-day -->
            <div class="flex justify-between items-start">
                <span class="px-2.5 py-1 text-xs font-bold rounded-md border <?php echo $sb[0]; ?> <?php if ($urgent) echo 'animate-pulse'; ?>">
                    <?php echo $sb[1]; ?>
                </span>
                <?php if ($dl[1]) { ?>
                <span class="text-xs font-medium px-2 py-1 rounded-md <?php echo $dl[0]; ?>"><?php echo $dl[1]; ?></span>
                <?php } ?>
            </div>
            <!-- 제목 + 설명 -->
            <div>
                <h3 class="text-lg font-bold text-mg-text-primary <?php echo $title_hover; ?> transition-colors" style="display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden"><?php echo htmlspecialchars($item['cc_title'] ?? ''); ?></h3>
                <p class="text-sm text-mg-text-secondary mt-1.5 leading-relaxed" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden"><?php echo htmlspecialchars(mb_substr($item['cc_content'] ?? '', 0, 100)); ?></p>
            </div>
            <!-- 캐릭터 아바타 -->
            <div class="flex items-center gap-3 mt-auto pt-1">
                <?php if ($item['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($item['ch_thumb']); ?>" class="w-8 h-8 rounded-full object-cover border border-mg-bg-tertiary" alt="">
                <?php } else { ?>
                <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs">?</div>
                <?php } ?>
                <div class="flex flex-col">
                    <span class="text-sm font-bold text-mg-text-primary"><?php echo htmlspecialchars($item['ch_name'] ?: ($item['mb_nick'] ?? '')); ?></span>
                    <span class="text-xs text-mg-text-muted"><?php echo $tl; ?> · 선정 <?php echo $item['cc_max_members']; ?>명 · 지원 <?php echo $item['apply_count']; ?>명</span>
                </div>
            </div>
            <hr class="border-mg-bg-tertiary">
            <!-- REWARD -->
            <div class="flex flex-col gap-2">
                <span class="text-xs font-bold text-mg-text-muted">REWARD</span>
                <div class="flex flex-wrap gap-2">
                    <?php if ((int)$item['cc_point_total'] > 0) { ?>
                    <span class="flex items-center gap-1.5 bg-mg-bg-primary border border-mg-bg-tertiary px-2.5 py-1.5 rounded-md text-xs font-bold text-yellow-400"><?php echo number_format($item['cc_point_total']); ?> P</span>
                    <?php } ?>
                    <?php if (!empty($item['cc_reward_memo'])) { ?>
                    <span class="flex items-center gap-1.5 bg-orange-950/30 border border-orange-900/50 px-2.5 py-1.5 rounded-md text-xs font-bold text-orange-300"><?php echo htmlspecialchars(mb_substr($item['cc_reward_memo'] ?? '', 0, 20)); ?></span>
                    <?php } ?>
                    <?php if ((int)$item['cc_point_total'] === 0 && empty($item['cc_reward_memo'])) { ?>
                    <span class="text-xs text-mg-text-muted italic">-</span>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 페이지네이션 -->
    <?php if ($result['total_pages'] > 1) { ?>
    <div class="flex justify-center gap-1 mt-6">
        <?php
        $qp = array('tab=market');
        if ($search_status) $qp[] = 'status=' . urlencode($search_status);
        if ($search_type) $qp[] = 'type=' . urlencode($search_type);
        $base_url = G5_BBS_URL . '/concierge.php?' . implode('&', $qp) . '&';
        for ($p = max(1, $page - 4); $p <= min($result['total_pages'], $page + 4); $p++) {
            $ac = ($p === $page) ? 'bg-mg-accent text-mg-bg-primary' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-accent/20';
        ?>
        <a href="<?php echo $base_url . 'page=' . $p; ?>" class="px-3 py-1.5 rounded text-sm <?php echo $ac; ?>"><?php echo $p; ?></a>
        <?php } ?>
    </div>
    <?php } ?>

<?php } elseif ($tab === 'my') { ?>
    <!-- ═══ 내 의뢰 탭 ═══ -->

    <!-- 내가 등록한 의뢰 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">내가 등록한 의뢰 <span class="text-sm font-normal text-mg-text-muted">(<?php echo count($my_owned); ?>)</span></h2>
        <?php if (empty($my_owned)) { ?>
        <div class="card text-center py-8">
            <p class="text-sm text-mg-text-muted">등록한 의뢰가 없습니다.</p>
            <button type="button" onclick="ccOpenModal()" class="inline-block mt-2 text-sm text-mg-accent hover:underline">의뢰 등록하기</button>
        </div>
        <?php } else { ?>
        <div class="grid grid-cols-1 gap-5" style="grid-template-columns:repeat(1,minmax(0,1fr))" id="cc-grid-owned">
            <?php foreach ($my_owned as $item) {
                $sb = cc_status_badge($item['cc_status']);
                $tl = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];
                $dl = cc_deadline_label($item['cc_deadline'], $item['cc_status']);
                $done = in_array($item['cc_status'], array('completed', 'expired', 'cancelled', 'force_closed'));
            ?>
            <div class="bg-mg-bg-secondary border border-mg-bg-tertiary rounded-xl p-5 flex flex-col gap-3 group hover:border-mg-accent transition-all duration-300 cursor-pointer <?php if ($done) echo 'opacity-60'; ?>"
                 onclick="ccOpenDetail(<?php echo $item['cc_id']; ?>)">
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-md border <?php echo $sb[0]; ?>"><?php echo $sb[1]; ?></span>
                    <?php if ($dl[1]) { ?>
                    <span class="text-xs font-medium px-2 py-1 rounded-md <?php echo $dl[0]; ?>"><?php echo $dl[1]; ?></span>
                    <?php } ?>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-mg-text-primary group-hover:text-mg-accent transition-colors" style="display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden"><?php echo htmlspecialchars($item['cc_title'] ?? ''); ?></h3>
                </div>
                <div class="flex items-center gap-3 mt-auto pt-1">
                    <?php if ($item['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($item['ch_thumb']); ?>" class="w-8 h-8 rounded-full object-cover border border-mg-bg-tertiary" alt="">
                    <?php } else { ?>
                    <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs">?</div>
                    <?php } ?>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-mg-text-primary"><?php echo htmlspecialchars($item['ch_name'] ?: ($item['mb_nick'] ?? '')); ?></span>
                        <span class="text-xs text-mg-text-muted"><?php echo $tl; ?> · 지원 <?php echo $item['apply_count']; ?>명</span>
                    </div>
                </div>
                <?php if ((int)$item['cc_point_total'] > 0 || !empty($item['cc_reward_memo'])) { ?>
                <hr class="border-mg-bg-tertiary">
                <div class="flex flex-col gap-2">
                    <span class="text-xs font-bold text-mg-text-muted">REWARD</span>
                    <div class="flex flex-wrap gap-2">
                        <?php if ((int)$item['cc_point_total'] > 0) { ?>
                        <span class="flex items-center gap-1.5 bg-mg-bg-primary border border-mg-bg-tertiary px-2.5 py-1.5 rounded-md text-xs font-bold text-yellow-400"><?php echo number_format($item['cc_point_total']); ?> P</span>
                        <?php } ?>
                        <?php if (!empty($item['cc_reward_memo'])) { ?>
                        <span class="flex items-center gap-1.5 bg-orange-950/30 border border-orange-900/50 px-2.5 py-1.5 rounded-md text-xs font-bold text-orange-300"><?php echo htmlspecialchars(mb_substr($item['cc_reward_memo'] ?? '', 0, 20)); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 내가 지원한 의뢰 -->
    <div>
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">내가 지원한 의뢰 <span class="text-sm font-normal text-mg-text-muted">(<?php echo count($my_applied); ?>)</span></h2>
        <?php if (empty($my_applied)) { ?>
        <div class="card text-center py-8">
            <p class="text-sm text-mg-text-muted">지원한 의뢰가 없습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/concierge.php?tab=market" class="inline-block mt-2 text-sm text-mg-accent hover:underline">의뢰 둘러보기</a>
        </div>
        <?php } else { ?>
        <div class="grid grid-cols-1 gap-5" style="grid-template-columns:repeat(1,minmax(0,1fr))" id="cc-grid-applied">
            <?php foreach ($my_applied as $item) {
                $sb = cc_status_badge($item['cc_status']);
                $tl = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];
                $done = in_array($item['cc_status'], array('completed', 'expired', 'cancelled', 'force_closed'));
                $my_st = array('pending' => array('text-mg-accent', '대기중'), 'selected' => array('text-green-400', '선정'), 'rejected' => array('text-mg-text-muted', '미선정'), 'force_closed' => array('text-red-400', '미이행'));
                $ms = isset($my_st[$item['ca_status']]) ? $my_st[$item['ca_status']] : array('', $item['ca_status']);
            ?>
            <div class="bg-mg-bg-secondary border border-mg-bg-tertiary rounded-xl p-5 flex flex-col gap-3 group hover:border-mg-accent transition-all duration-300 cursor-pointer <?php if ($done) echo 'opacity-60'; ?>"
                 onclick="ccOpenDetail(<?php echo $item['cc_id']; ?>)">
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-md border <?php echo $sb[0]; ?>"><?php echo $sb[1]; ?></span>
                    <span class="px-2 py-1 text-xs font-bold rounded-md <?php echo $ms[0]; ?> bg-mg-bg-primary"><?php echo $ms[1]; ?><?php if ($item['has_result']) echo ' ✓'; ?></span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-mg-text-primary group-hover:text-mg-accent transition-colors" style="display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden"><?php echo htmlspecialchars($item['cc_title'] ?? ''); ?></h3>
                </div>
                <div class="flex items-center gap-3 mt-auto pt-1">
                    <?php if ($item['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($item['ch_thumb']); ?>" class="w-8 h-8 rounded-full object-cover border border-mg-bg-tertiary" alt="">
                    <?php } else { ?>
                    <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs">?</div>
                    <?php } ?>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-mg-text-primary"><?php echo htmlspecialchars($item['ch_name'] ?: ($item['mb_nick'] ?? '')); ?></span>
                        <span class="text-xs text-mg-text-muted"><?php echo $tl; ?> · 지원 <?php echo $item['apply_count']; ?>명</span>
                    </div>
                </div>
                <?php if ((int)$item['cc_point_total'] > 0) { ?>
                <hr class="border-mg-bg-tertiary">
                <div class="flex flex-col gap-2">
                    <span class="text-xs font-bold text-mg-text-muted">REWARD</span>
                    <div class="flex flex-wrap gap-2">
                        <span class="flex items-center gap-1.5 bg-mg-bg-primary border border-mg-bg-tertiary px-2.5 py-1.5 rounded-md text-xs font-bold text-yellow-400"><?php echo number_format($item['cc_point_total']); ?> P</span>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

<?php } elseif ($tab === 'results') { ?>
    <!-- ═══ 의뢰 수행 탭 ═══ -->

    <?php if (!$_rt_board) { ?>
    <div class="card text-center py-12">
        <p class="text-mg-text-muted">의뢰 수행 게시판이 아직 생성되지 않았습니다.</p>
        <p class="text-xs text-mg-text-muted mt-1">관리자가 'concierge_result' 게시판을 먼저 생성해주세요.</p>
    </div>
    <?php } else { ?>

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="text-sm text-mg-text-muted">의뢰 수행 결과물 <?php echo number_format($_rt_total_count); ?>건</p>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=concierge_result&w=w" class="px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg text-sm hover:bg-mg-accent-hover transition-colors">결과물 등록</a>
    </div>

    <div class="card">
        <div class="divide-y divide-mg-bg-tertiary">
            <?php foreach ($_rt_list as $row) {
                $ch = isset($_rt_ch_map[(int)$row['wr_id']]) ? $_rt_ch_map[(int)$row['wr_id']] : null;
                $cc = isset($_rt_cc_map[(int)$row['wr_id']]) ? $_rt_cc_map[(int)$row['wr_id']] : null;
                $cc_tl = ($cc && isset($type_labels[$cc['cc_type']])) ? $type_labels[$cc['cc_type']] : '';
            ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=concierge_result&wr_id=<?php echo $row['wr_id']; ?>" class="flex items-center gap-3 py-3 px-3 hover:bg-mg-bg-primary transition-colors block">
                <?php if ($ch && $ch['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($ch['ch_thumb']); ?>" class="w-8 h-8 rounded-full object-cover flex-shrink-0 border border-mg-bg-tertiary" alt="">
                <?php } else { ?>
                <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs flex-shrink-0">?</div>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <?php if ($cc_tl) { ?>
                        <span class="px-1.5 py-0.5 text-xs rounded bg-mg-accent/15 text-mg-accent"><?php echo $cc_tl; ?></span>
                        <?php } ?>
                        <span class="text-sm font-medium text-mg-text-primary truncate"><?php echo htmlspecialchars($row['wr_subject'] ?? ''); ?></span>
                        <?php if ($row['wr_comment']) { ?>
                        <span class="text-xs text-mg-accent">[<?php echo $row['wr_comment']; ?>]</span>
                        <?php } ?>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-mg-text-muted">
                        <span><?php echo ($ch ? htmlspecialchars($ch['ch_name']) : htmlspecialchars($row['wr_name'] ?? '')); ?></span>
                        <span><?php echo substr($row['wr_datetime'], 0, 10); ?></span>
                        <span>조회 <?php echo $row['wr_hit']; ?></span>
                        <?php if ($cc) { ?>
                        <span class="text-mg-text-secondary">&larr; <?php echo htmlspecialchars(mb_substr($cc['cc_title'], 0, 20)); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </a>
            <?php } ?>

            <?php if (empty($_rt_list)) { ?>
            <div class="py-12 text-center text-mg-text-muted">등록된 결과물이 없습니다.</div>
            <?php } ?>
        </div>
    </div>

    <?php if ($_rt_total_pages > 1) { ?>
    <div class="flex justify-center gap-1 mt-4">
        <?php for ($p = max(1, $_rt_page - 4); $p <= min($_rt_total_pages, $_rt_page + 4); $p++) {
            $ac = ($p === $_rt_page) ? 'bg-mg-accent text-mg-bg-primary' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-accent/20';
        ?>
        <a href="<?php echo G5_BBS_URL; ?>/concierge.php?tab=results&rpage=<?php echo $p; ?>" class="px-3 py-1.5 rounded text-sm <?php echo $ac; ?>"><?php echo $p; ?></a>
        <?php } ?>
    </div>
    <?php } ?>

    <?php } ?>

<?php } ?>
</div>

<!-- ═══ 상세 모달 ═══ -->
<div id="cc-detail-overlay" class="fixed inset-0 bg-black/60 z-50 hidden" onclick="ccCloseDetail()"></div>
<div id="cc-detail-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="pointer-events:none">
    <div class="w-full bg-mg-bg-secondary rounded-xl shadow-2xl border border-mg-bg-tertiary" style="pointer-events:auto;max-height:90vh;overflow-y:auto;max-width:640px">
        <div class="sticky top-0 bg-mg-bg-secondary border-b border-mg-bg-tertiary px-6 py-4 flex items-center justify-between z-10">
            <h3 class="text-lg font-bold text-mg-text-primary truncate" id="cc-detail-title">의뢰 상세</h3>
            <button type="button" onclick="ccCloseDetail()" class="w-8 h-8 flex items-center justify-center text-mg-text-muted hover:text-mg-text-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors text-xl">&times;</button>
        </div>
        <div id="cc-detail-body" class="px-6 py-4">
            <div class="text-center py-8 text-mg-text-muted">로딩중...</div>
        </div>
    </div>
</div>

<!-- ═══ 의뢰 등록/수정 모달 ═══ -->
<div id="cc-modal-overlay" class="fixed inset-0 bg-black/60 z-50 hidden" onclick="ccCloseModal()"></div>
<div id="cc-modal" class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden" style="pointer-events:none">
    <div class="w-full max-w-lg bg-mg-bg-secondary rounded-xl shadow-2xl border border-mg-bg-tertiary" style="pointer-events:auto;max-height:90vh;overflow-y:auto" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-mg-bg-secondary border-b border-mg-bg-tertiary px-6 py-4 flex items-center justify-between z-10">
            <h3 class="text-lg font-bold text-mg-text-primary" id="cc-modal-title">새 의뢰 등록</h3>
            <button type="button" onclick="ccCloseModal()" class="w-8 h-8 flex items-center justify-center text-mg-text-muted hover:text-mg-text-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors text-xl">&times;</button>
        </div>

        <div class="px-6 py-4 space-y-4">
            <input type="hidden" id="cc-edit-id" value="0">

            <div>
                <label class="block text-xs text-mg-text-muted mb-1">제목 *</label>
                <input type="text" id="cc-f-title" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm focus:border-mg-accent outline-none" maxlength="100">
            </div>

            <div>
                <label class="block text-xs text-mg-text-muted mb-1">내용</label>
                <textarea id="cc-f-content" rows="4" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm resize-y focus:border-mg-accent outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">캐릭터 *</label>
                    <select id="cc-f-ch" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                        <?php foreach ($my_characters as $ch) { ?>
                        <option value="<?php echo $ch['ch_id']; ?>"><?php echo htmlspecialchars($ch['ch_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">유형</label>
                    <select id="cc-f-type" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                        <?php foreach ($type_labels as $k => $v) { ?>
                        <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">매칭 방식</label>
                    <select id="cc-f-match" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm"
                            <?php echo $cfg_match_mode !== 'both' ? 'disabled' : ''; ?>>
                        <option value="direct" <?php echo $cfg_match_mode === 'lottery_only' ? 'style="display:none"' : ''; ?>>직접선택</option>
                        <option value="lottery" <?php echo $cfg_match_mode === 'direct_only' ? 'style="display:none"' : ''; ?>>추첨</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">선정 인원 (1~5)</label>
                    <input type="number" id="cc-f-members" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm" min="1" max="5" value="1" onchange="ccCalcPoint()">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">지원 상한 (0=무제한)</label>
                    <input type="number" id="cc-f-applicants" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm" min="0" max="100" value="0">
                </div>
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">보상 포인트<?php if ($cfg_point_min > 0) echo " (최소 {$cfg_point_min})"; ?></label>
                    <input type="number" id="cc-f-point" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm" min="0" max="<?php echo $cfg_point_max; ?>" value="0" oninput="ccCalcPoint()">
                </div>
            </div>

            <div id="cc-point-preview" class="text-xs text-mg-text-muted bg-mg-bg-primary rounded-lg px-3 py-2 hidden">
                1인당: <b id="cc-pp-per">0</b>P
                <span id="cc-pp-fee-wrap" class="hidden"> (수수료 <span id="cc-pp-fee">0</span>P 차감 &rarr; 실지급 <b id="cc-pp-actual">0</b>P)</span>
                <br>총 차감: <b id="cc-pp-deduct">0</b>P (보유: <?php echo number_format($mb_point); ?>P)
            </div>

            <div>
                <label class="block text-xs text-mg-text-muted mb-1">추가 보상 (선택)</label>
                <input type="text" id="cc-f-reward-memo" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm focus:border-mg-accent outline-none" maxlength="200" placeholder="예: 캐릭터 관계 등록, 그림 1장 등 자유롭게">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">모집 마감 *</label>
                    <input type="datetime-local" id="cc-f-deadline" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs text-mg-text-muted mb-1">수행 마감 (선택)</label>
                    <input type="datetime-local" id="cc-f-complete-dl" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 bg-mg-bg-secondary border-t border-mg-bg-tertiary px-6 py-4 flex justify-end gap-2">
            <button type="button" onclick="ccCloseModal()" class="px-4 py-2 bg-mg-bg-tertiary text-mg-text-secondary rounded-lg text-sm hover:bg-mg-bg-primary transition-colors">취소</button>
            <button type="button" onclick="ccSubmitModal()" id="cc-submit-btn" class="px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg text-sm hover:bg-mg-accent-hover transition-colors">등록하기</button>
        </div>
    </div>
</div>

<!-- ═══ JavaScript ═══ -->
<script>
var CC = {
    api: '<?php echo G5_BBS_URL; ?>/concierge_api.php',
    bbs: '<?php echo G5_BBS_URL; ?>',
    charImg: '<?php echo defined("MG_CHAR_IMAGE_URL") ? MG_CHAR_IMAGE_URL : G5_DATA_URL."/character"; ?>',
    matchMode: '<?php echo $cfg_match_mode; ?>',
    anon: <?php echo $cfg_anonymous ? 'true' : 'false'; ?>,
    feeRate: <?php echo $cfg_fee_rate; ?>,
    pointMin: <?php echo $cfg_point_min; ?>,
    pointMax: <?php echo $cfg_point_max; ?>,
    mbPoint: <?php echo $mb_point; ?>,
    mbId: '<?php echo addslashes($mb_id); ?>',
    isAdmin: <?php echo $is_admin === 'super' ? 'true' : 'false'; ?>,
    chars: <?php echo json_encode($my_characters); ?>,
    types: <?php echo json_encode($type_labels); ?>,
    cache: {}
};

function _e(s) { return s ? String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;') : ''; }

function _post(data) {
    var fd = new FormData();
    for (var k in data) fd.append(k, data[k]);
    return fetch(CC.api, { method: 'POST', body: fd }).then(function(r) { return r.json(); });
}

// ── 상세 모달 ──
function ccOpenDetail(id) {
    document.getElementById('cc-detail-overlay').classList.remove('hidden');
    document.getElementById('cc-detail-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    if (CC.cache[id]) {
        ccRenderDetail(id, CC.cache[id]);
    } else {
        document.getElementById('cc-detail-body').innerHTML = '<div class="text-center py-8 text-mg-text-muted">로딩중...</div>';
        document.getElementById('cc-detail-title').textContent = '의뢰 상세';
        fetch(CC.api + '?action=detail&cc_id=' + id)
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (!d.success) {
                    document.getElementById('cc-detail-body').innerHTML = '<div class="p-4 text-sm text-red-400">' + _e(d.message) + '</div>';
                    return;
                }
                CC.cache[id] = d.data;
                ccRenderDetail(id, d.data);
            })
            .catch(function() {
                document.getElementById('cc-detail-body').innerHTML = '<div class="p-4 text-sm text-red-400">로딩 실패</div>';
            });
    }
}

function ccCloseDetail() {
    document.getElementById('cc-detail-overlay').classList.add('hidden');
    document.getElementById('cc-detail-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function _statusBadge(st) {
    var map = {
        'recruiting': ['bg-orange-500/10 text-orange-400 border-orange-500/20', '모집중'],
        'matched': ['bg-blue-500/10 text-blue-400 border-blue-500/20', '진행중'],
        'completed': ['bg-green-500/10 text-green-400 border-green-500/20', '완료'],
        'expired': ['bg-gray-500/10 text-gray-500 border-gray-500/20', '만료'],
        'cancelled': ['bg-gray-500/10 text-gray-500 border-gray-500/20', '취소'],
        'force_closed': ['bg-red-500/10 text-red-400 border-red-500/20', '미이행']
    };
    return map[st] || ['', st];
}

// ── 상세 렌더링 (모달) ──
function ccRenderDetail(id, d) {
    var el = document.getElementById('cc-detail-body');
    document.getElementById('cc-detail-title').textContent = d.cc_title || '의뢰 상세';
    var own = (d.mb_id === CC.mbId);
    var sb = _statusBadge(d.cc_status);
    var h = '';

    // 상태 배지
    h += '<div class="flex flex-wrap gap-2 mb-4">';
    h += '<span class="px-2.5 py-1 text-xs font-bold rounded-md border ' + sb[0] + '">' + sb[1] + '</span>';
    h += '<span class="px-2 py-0.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-secondary">' + _e(CC.types[d.cc_type] || d.cc_type) + '</span>';
    h += '</div>';

    // 본문
    h += '<div class="text-sm text-mg-text-secondary mb-4 leading-relaxed whitespace-pre-wrap">' + (d.cc_content ? _e(d.cc_content) : '<span class="text-mg-text-muted italic">내용 없음</span>') + '</div>';

    // 정보 그리드
    h += '<div class="grid grid-cols-2 gap-3 mb-4 text-xs">';
    if (parseInt(d.cc_point_total) > 0) {
        var pp = Math.floor(d.cc_point_total / d.cc_max_members);
        var fee = Math.floor(pp * CC.feeRate / 100);
        var act = pp - fee;
        h += '<div class="bg-mg-bg-primary rounded-lg px-3 py-2"><span class="text-mg-text-muted">총 보상</span><br><b class="text-yellow-400">' + Number(d.cc_point_total).toLocaleString() + 'P</b> <span class="text-mg-text-muted">(1인당 ' + act + 'P)</span></div>';
    }
    if (d.cc_reward_memo) {
        h += '<div class="bg-mg-bg-primary rounded-lg px-3 py-2"><span class="text-mg-text-muted">추가 보상</span><br><b class="text-orange-300">' + _e(d.cc_reward_memo) + '</b></div>';
    }
    h += '<div class="bg-mg-bg-primary rounded-lg px-3 py-2"><span class="text-mg-text-muted">모집 마감</span><br>' + (d.cc_deadline ? d.cc_deadline.substring(0, 16) : '-') + '</div>';
    h += '<div class="bg-mg-bg-primary rounded-lg px-3 py-2"><span class="text-mg-text-muted">수행 마감</span><br>' + (d.cc_complete_deadline ? d.cc_complete_deadline.substring(0, 16) : '-') + '</div>';
    h += '<div class="bg-mg-bg-primary rounded-lg px-3 py-2"><span class="text-mg-text-muted">매칭 방식</span><br>' + (d.cc_match_mode === 'lottery' ? '추첨' : '직접선택') + '</div>';
    h += '<div class="bg-mg-bg-primary rounded-lg px-3 py-2"><span class="text-mg-text-muted">선정 인원</span><br>' + d.cc_max_members + '명</div>';
    h += '</div>';

    // 지원자 목록
    h += '<div class="mb-4">';
    h += '<h4 class="text-xs font-bold text-mg-text-muted mb-3 uppercase tracking-wider">지원자 (' + d.applies.length + '명)</h4>';
    if (d.applies.length === 0) {
        h += '<p class="text-xs text-mg-text-muted italic">지원자가 없습니다.</p>';
    } else {
        var showCb = own && d.cc_status === 'recruiting' && d.cc_match_mode === 'direct';
        h += '<div class="space-y-2">';
        for (var i = 0; i < d.applies.length; i++) {
            var a = d.applies[i];
            var canSee = own || CC.isAdmin || a.mb_id === CC.mbId || (a.ca_status === 'selected');
            var masked = CC.anon && !canSee;
            var hasResult = false;
            if (d.results) {
                for (var ri = 0; ri < d.results.length; ri++) {
                    if (d.results[ri].ca_id == a.ca_id) { hasResult = true; break; }
                }
            }
            var ast = '', acls = '';
            if (a.ca_status === 'selected' && hasResult) { ast = '제출완료'; acls = 'text-green-400'; }
            else if (a.ca_status === 'selected') { ast = '선정'; acls = 'text-yellow-400'; }
            else if (a.ca_status === 'pending') { ast = '대기'; acls = 'text-mg-accent'; }
            else if (a.ca_status === 'rejected') { ast = '미선정'; acls = 'text-mg-text-muted'; }
            else if (a.ca_status === 'force_closed') { ast = '미이행'; acls = 'text-red-400'; }

            h += '<div class="flex items-center gap-2 text-sm">';
            if (showCb && a.ca_status === 'pending') {
                h += '<input type="checkbox" class="cc-sel-chk" value="' + a.ca_id + '" style="accent-color:var(--mg-accent)">';
            }
            if (masked) {
                h += '<div class="w-7 h-7 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs">?</div>';
                h += '<span class="text-mg-text-secondary">익명 지원자 ' + (i + 1) + '</span>';
            } else {
                if (a.ch_thumb) {
                    h += '<img src="' + _e(CC.charImg + '/' + a.ch_thumb) + '" class="w-7 h-7 rounded-full object-cover">';
                } else {
                    h += '<div class="w-7 h-7 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs">?</div>';
                }
                h += '<span class="text-mg-text-primary">' + _e(a.ch_name || a.mb_nick) + '</span>';
            }
            if (ast) h += '<span class="text-xs ' + acls + '">' + ast + '</span>';
            if (!masked && a.ca_message) h += '<span class="text-xs text-mg-text-muted truncate" style="max-width:200px">"' + _e(a.ca_message) + '"</span>';
            h += '</div>';
        }
        h += '</div>';
    }
    h += '</div>';

    // 액션
    h += '<div class="flex flex-wrap gap-2 pt-4 border-t border-mg-bg-tertiary">';

    if (d.cc_status === 'recruiting' && !own) {
        var applied = false;
        for (var j = 0; j < d.applies.length; j++) { if (d.applies[j].mb_id === CC.mbId) { applied = true; break; } }
        var maxApp = d.cc_max_applicants ? parseInt(d.cc_max_applicants) : 0;
        var full = maxApp > 0 && d.applies.length >= maxApp;

        if (applied) {
            h += '<span class="text-xs text-mg-text-muted py-1">이미 지원한 의뢰입니다.</span>';
        } else if (full) {
            h += '<span class="text-xs text-mg-text-muted py-1">지원 인원이 마감되었습니다.</span>';
        } else if (CC.chars.length > 0) {
            h += '<div class="w-full flex flex-wrap gap-2 items-end">';
            h += '<select id="cc-apc-' + id + '" class="px-3 py-1.5 bg-mg-bg-tertiary text-mg-text-primary rounded text-sm border border-mg-bg-tertiary">';
            for (var k = 0; k < CC.chars.length; k++) h += '<option value="' + CC.chars[k].ch_id + '">' + _e(CC.chars[k].ch_name) + '</option>';
            h += '</select>';
            h += '<input type="text" id="cc-apm-' + id + '" placeholder="지원 메시지" class="flex-1 px-3 py-1.5 bg-mg-bg-tertiary text-mg-text-primary rounded text-sm border border-mg-bg-tertiary" maxlength="200" style="min-width:120px">';
            h += '<button type="button" onclick="ccApply(' + id + ')" class="px-3 py-1.5 bg-mg-accent text-mg-bg-primary rounded text-sm font-medium hover:bg-mg-accent-hover transition-colors">지원</button>';
            h += '</div>';
        } else {
            h += '<span class="text-xs text-mg-text-muted py-1">승인된 캐릭터가 없어 지원할 수 없습니다.</span>';
        }
    }

    if (own && d.cc_status === 'recruiting') {
        if (d.cc_match_mode === 'direct' && d.applies.length > 0)
            h += '<button type="button" onclick="ccMatch(' + id + ')" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm font-medium hover:opacity-80 transition-opacity">매칭 실행</button>';
        if (d.cc_match_mode === 'lottery' && d.applies.length >= parseInt(d.cc_max_members))
            h += '<button type="button" onclick="ccLottery(' + id + ')" class="px-3 py-1.5 bg-blue-500 text-white rounded text-sm font-medium hover:opacity-80 transition-opacity">추첨 실행</button>';
        h += '<button type="button" onclick="ccCloseDetail();ccEdit(' + id + ')" class="px-3 py-1.5 bg-mg-bg-tertiary text-mg-text-secondary rounded text-sm hover:bg-mg-bg-primary transition-colors">수정</button>';
        h += '<button type="button" onclick="ccCancel(' + id + ')" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded text-sm hover:bg-red-500/30 transition-colors">취소</button>';
    }

    if (own && d.cc_status === 'matched') {
        var resultCount = d.results ? d.results.length : 0;
        if (resultCount > 0)
            h += '<button type="button" onclick="ccSettle(' + id + ',false)" class="px-3 py-1.5 bg-green-600 text-white rounded text-sm font-medium hover:opacity-80 transition-opacity">전체 완료</button>';
        h += '<button type="button" onclick="ccSettle(' + id + ',true)" class="px-3 py-1.5 bg-yellow-500/20 text-yellow-400 rounded text-sm hover:bg-yellow-500/30 transition-colors">강제 완료</button>';
        h += '<button type="button" onclick="ccForceClose(' + id + ')" class="px-3 py-1.5 bg-red-500/20 text-red-400 rounded text-sm hover:bg-red-500/30 transition-colors">미이행 종료</button>';
    }

    if (d.cc_status === 'matched') {
        for (var m = 0; m < d.applies.length; m++) {
            if (d.applies[m].mb_id === CC.mbId && d.applies[m].ca_status === 'selected') {
                var myResult = false;
                if (d.results) {
                    for (var mr = 0; mr < d.results.length; mr++) {
                        if (d.results[mr].ca_id == d.applies[m].ca_id) { myResult = true; break; }
                    }
                }
                if (!myResult) {
                    h += '<a href="' + CC.bbs + '/board.php?bo_table=concierge_result&w=w&mg_concierge_id=' + id + '" class="px-3 py-1.5 bg-mg-accent text-mg-bg-primary rounded text-sm font-medium hover:bg-mg-accent-hover transition-colors inline-flex items-center gap-1">결과물 등록</a>';
                } else {
                    h += '<span class="text-xs text-green-400 py-1">결과물 등록 완료</span>';
                    h += '<button type="button" onclick="ccSettle(' + id + ',true)" class="px-3 py-1.5 bg-yellow-500/20 text-yellow-400 rounded text-sm hover:bg-yellow-500/30 transition-colors">강제 완료</button>';
                }
                break;
            }
        }
    }

    h += '</div>';
    el.innerHTML = h;
}

// ── 지원 ──
function ccApply(id) {
    var ch = document.getElementById('cc-apc-' + id);
    var msg = document.getElementById('cc-apm-' + id);
    if (!ch) return;
    _post({ action: 'apply', cc_id: id, ch_id: ch.value, ca_message: msg ? msg.value : '' })
        .then(function(d) { alert(d.message); if (d.success) location.reload(); });
}

// ── 매칭 (직접선택) ──
function ccMatch(id) {
    var cbs = document.querySelectorAll('#cc-detail-' + id + ' .cc-sel-chk:checked');
    if (cbs.length === 0) { alert('선정할 지원자를 선택해주세요.'); return; }
    var ids = []; cbs.forEach(function(c) { ids.push(parseInt(c.value)); });
    if (!confirm(ids.length + '명을 선정하시겠습니까?')) return;
    _post({ action: 'match', cc_id: id, selected_ca_ids: JSON.stringify(ids) })
        .then(function(d) { alert(d.message); if (d.success) location.reload(); });
}

// ── 추첨 ──
function ccLottery(id) {
    if (!confirm('추첨을 실행하시겠습니까?')) return;
    _post({ action: 'lottery', cc_id: id })
        .then(function(d) { alert(d.message); if (d.success) location.reload(); });
}

// ── 정산 완료 ──
function ccSettle(id, force) {
    var msg = force
        ? '강제 완료하시겠습니까?\n미제출 수행자의 보상은 의뢰자에게 환불됩니다.\n(관리자에게 기록됩니다)'
        : '의뢰를 완료하시겠습니까?\n미제출 수행자의 보상은 환불됩니다.';
    if (!confirm(msg)) return;
    _post({ action: 'settle', cc_id: id, force: force ? '1' : '0' })
        .then(function(d) { alert(d.message); if (d.success) location.reload(); });
}

// ── 취소 ──
function ccCancel(id) {
    if (!confirm('의뢰를 취소하시겠습니까?\n보상 포인트가 환불됩니다.')) return;
    _post({ action: 'cancel', cc_id: id })
        .then(function(d) { alert(d.message); if (d.success) location.reload(); });
}

// ── 미이행 종료 ──
function ccForceClose(id) {
    if (!confirm('미이행으로 강제 종료하시겠습니까?\n수행자에게 페널티가 부여되고 포인트가 환불됩니다.')) return;
    _post({ action: 'force_close', cc_id: id })
        .then(function(d) { alert(d.message); if (d.success) location.reload(); });
}

// ── 모달 ──
function ccOpenModal(editId) {
    document.getElementById('cc-modal-overlay').classList.remove('hidden');
    document.getElementById('cc-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    var isEdit = editId && editId > 0;
    document.getElementById('cc-modal-title').textContent = isEdit ? '의뢰 수정' : '새 의뢰 등록';
    document.getElementById('cc-submit-btn').textContent = isEdit ? '수정하기' : '등록하기';
    document.getElementById('cc-edit-id').value = isEdit ? editId : 0;

    // 매칭 방식 제한
    var mm = document.getElementById('cc-f-match');
    if (CC.matchMode === 'direct_only') mm.value = 'direct';
    else if (CC.matchMode === 'lottery_only') mm.value = 'lottery';

    // 지원자 유무에 따라 잠금 필드 결정
    var lockedFields = ['cc-f-type', 'cc-f-match', 'cc-f-members', 'cc-f-applicants', 'cc-f-point'];
    var hasApplicants = isEdit && CC.cache[editId] && CC.cache[editId].applies && CC.cache[editId].applies.length > 0;

    lockedFields.forEach(function(fid) {
        var el = document.getElementById(fid);
        if (el) { el.disabled = hasApplicants; el.style.opacity = hasApplicants ? '0.5' : '1'; }
    });

    if (isEdit && CC.cache[editId]) {
        var d = CC.cache[editId];
        document.getElementById('cc-f-title').value = d.cc_title || '';
        document.getElementById('cc-f-content').value = d.cc_content || '';
        document.getElementById('cc-f-ch').value = d.ch_id || '';
        document.getElementById('cc-f-type').value = d.cc_type || 'collaboration';
        if (CC.matchMode === 'both') mm.value = d.cc_match_mode || 'direct';
        document.getElementById('cc-f-members').value = d.cc_max_members || 1;
        document.getElementById('cc-f-applicants').value = d.cc_max_applicants || 0;
        document.getElementById('cc-f-point').value = d.cc_point_total || 0;
        document.getElementById('cc-f-deadline').value = (d.cc_deadline || '').replace(' ', 'T').substring(0, 16);
        document.getElementById('cc-f-complete-dl').value = d.cc_complete_deadline ? d.cc_complete_deadline.replace(' ', 'T').substring(0, 16) : '';
        document.getElementById('cc-f-reward-memo').value = d.cc_reward_memo || '';
        ccCalcPoint();
    } else {
        document.getElementById('cc-f-title').value = '';
        document.getElementById('cc-f-content').value = '';
        document.getElementById('cc-f-type').value = 'collaboration';
        if (CC.matchMode === 'both') mm.value = 'direct';
        document.getElementById('cc-f-members').value = 1;
        document.getElementById('cc-f-applicants').value = 0;
        document.getElementById('cc-f-point').value = 0;
        document.getElementById('cc-f-deadline').value = '';
        document.getElementById('cc-f-complete-dl').value = '';
        document.getElementById('cc-f-reward-memo').value = '';
        document.getElementById('cc-point-preview').classList.add('hidden');
    }
}

function ccCloseModal() {
    document.getElementById('cc-modal-overlay').classList.add('hidden');
    document.getElementById('cc-modal').classList.add('hidden');
    document.body.style.overflow = '';
}

function ccCalcPoint() {
    var total = parseInt(document.getElementById('cc-f-point').value) || 0;
    var members = parseInt(document.getElementById('cc-f-members').value) || 1;
    var prev = document.getElementById('cc-point-preview');
    if (total <= 0) { prev.classList.add('hidden'); return; }
    prev.classList.remove('hidden');
    var pp = Math.floor(total / members);
    var fee = Math.floor(pp * CC.feeRate / 100);
    var act = pp - fee;
    document.getElementById('cc-pp-per').textContent = pp;
    document.getElementById('cc-pp-deduct').textContent = total;
    if (CC.feeRate > 0) {
        document.getElementById('cc-pp-fee-wrap').classList.remove('hidden');
        document.getElementById('cc-pp-fee').textContent = fee;
        document.getElementById('cc-pp-actual').textContent = act;
    } else {
        document.getElementById('cc-pp-fee-wrap').classList.add('hidden');
    }
}

function ccSubmitModal() {
    var editId = parseInt(document.getElementById('cc-edit-id').value);
    var data = {
        action: editId > 0 ? 'update' : 'create',
        cc_title: document.getElementById('cc-f-title').value,
        cc_content: document.getElementById('cc-f-content').value,
        ch_id: document.getElementById('cc-f-ch').value,
        cc_type: document.getElementById('cc-f-type').value,
        cc_match_mode: document.getElementById('cc-f-match').value,
        cc_max_members: document.getElementById('cc-f-members').value,
        cc_max_applicants: document.getElementById('cc-f-applicants').value,
        cc_point_total: document.getElementById('cc-f-point').value,
        cc_deadline: (document.getElementById('cc-f-deadline').value || '').replace('T', ' '),
        cc_complete_deadline: (document.getElementById('cc-f-complete-dl').value || '').replace('T', ' '),
        cc_reward_memo: document.getElementById('cc-f-reward-memo').value
    };
    if (editId > 0) data.cc_id = editId;
    _post(data).then(function(d) {
        if (d.success) { ccCloseModal(); location.reload(); }
        else alert(d.message);
    }).catch(function() { alert('네트워크 오류'); });
}

function ccEdit(id) { ccOpenModal(id); }

// ── 자동 열기 ──
(function() {
    <?php if ($open_cc_id) { ?>
    ccOpenDetail(<?php echo $open_cc_id; ?>);
    <?php } ?>
    <?php if ($open_write) { ?>
    ccOpenModal();
    <?php } ?>
})();

// ── 그리드 반응형 (Tailwind pre-built에 누락될 수 있어 JS로 처리) ──
function ccApplyGrid() {
    var grids = document.querySelectorAll('#cc-grid, #cc-grid-owned, #cc-grid-applied');
    var w = window.innerWidth;
    var cols = w >= 1024 ? 3 : (w >= 768 ? 2 : 1);
    grids.forEach(function(g) { g.style.gridTemplateColumns = 'repeat(' + cols + ',minmax(0,1fr))'; });
}
ccApplyGrid();
window.addEventListener('resize', ccApplyGrid);

document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { ccCloseModal(); ccCloseDetail(); } });
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
