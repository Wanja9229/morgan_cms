<?php
/**
 * Morgan Edition - 의뢰 게시판
 *
 * 등록 탭: 전체 의뢰 마켓플레이스 (모든 상태)
 * 진행 탭: 나의 의뢰 대시보드 (내가 등록한 + 내가 지원/수행 중인)
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (mg_config('concierge_use', '1') != '1') {
    alert_close('의뢰 기능이 비활성화되어 있습니다.');
}

if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$tab = isset($_GET['tab']) ? clean_xss_tags($_GET['tab']) : 'register';
if (!in_array($tab, array('register', 'progress'))) $tab = 'register';

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');

if ($tab === 'register') {
    // ── 등록 탭: 전체 의뢰 마켓플레이스 ──
    $search_type   = isset($_GET['type'])   ? clean_xss_tags($_GET['type'])   : '';
    $search_status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : '';
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

    $result = mg_get_concierge_list(
        $search_status ?: null,
        $search_type ?: null,
        $page,
        20
    );
} else {
    // ── 진행 탭: 나의 의뢰 대시보드 ──
    $mb_id_esc = sql_real_escape_string($member['mb_id']);

    // 1) 내가 등록한 의뢰
    $my_owned_sql = "SELECT cc.*, m.mb_nick, ch.ch_name, ch.ch_thumb,
                       (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id) as apply_count
                FROM {$g5['mg_concierge_table']} cc
                LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
                LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
                WHERE cc.mb_id = '{$mb_id_esc}'
                ORDER BY FIELD(cc.cc_status, 'recruiting', 'matched', 'completed', 'expired', 'cancelled', 'force_closed'), cc.cc_datetime DESC";
    $my_owned_result = sql_query($my_owned_sql);
    $my_owned = array();
    if ($my_owned_result !== false) {
        while ($row = sql_fetch_array($my_owned_result)) {
            $my_owned[] = $row;
        }
    }

    // 2) 내가 지원한 의뢰 (수행자 입장)
    $my_apply_sql = "SELECT cc.*, m.mb_nick, ch.ch_name, ch.ch_thumb,
                            ca.ca_status, ca.ca_id, ca.ca_datetime as my_apply_datetime,
                            (SELECT COUNT(*) FROM {$g5['mg_concierge_apply_table']} WHERE cc_id = cc.cc_id) as apply_count
                     FROM {$g5['mg_concierge_apply_table']} ca
                     JOIN {$g5['mg_concierge_table']} cc ON ca.cc_id = cc.cc_id
                     LEFT JOIN {$g5['member_table']} m ON cc.mb_id = m.mb_id
                     LEFT JOIN {$g5['mg_character_table']} ch ON cc.ch_id = ch.ch_id
                     WHERE ca.mb_id = '{$mb_id_esc}'
                     ORDER BY FIELD(ca.ca_status, 'selected', 'pending', 'rejected', 'force_closed'), ca.ca_datetime DESC";
    $my_apply_result = sql_query($my_apply_sql);
    $my_applied = array();
    if ($my_apply_result !== false) {
        while ($row = sql_fetch_array($my_apply_result)) {
            // 이미 결과물 등록 여부 확인
            $has_result = sql_fetch("SELECT cr_id FROM {$g5['mg_concierge_result_table']}
                                     WHERE cc_id = {$row['cc_id']} AND ca_id = {$row['ca_id']}");
            $row['has_result'] = ($has_result && $has_result['cr_id']) ? true : false;
            $my_applied[] = $row;
        }
    }
}

$g5['title'] = '의뢰 게시판';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-mg-text-primary">의뢰 게시판</h1>
            <p class="text-sm text-mg-text-secondary mt-1">창작 협업 의뢰를 등록하고 지원하세요</p>
        </div>
        <a href="<?php echo G5_BBS_URL; ?>/concierge_write.php" class="px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors">
            의뢰 등록
        </a>
    </div>

    <!-- 탭 -->
    <div class="flex gap-1 mb-4 border-b border-mg-bg-tertiary">
        <a href="<?php echo G5_BBS_URL; ?>/concierge.php?tab=register"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo $tab === 'register' ? 'border-mg-accent text-mg-accent' : 'border-transparent text-mg-text-muted hover:text-mg-text-secondary'; ?>">
            등록
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/concierge.php?tab=progress"
           class="px-4 py-2.5 text-sm font-medium border-b-2 transition-colors <?php echo $tab === 'progress' ? 'border-mg-accent text-mg-accent' : 'border-transparent text-mg-text-muted hover:text-mg-text-secondary'; ?>">
            진행
        </a>
    </div>

<?php if ($tab === 'register') { ?>
    <!-- ════════════════ 등록 탭: 의뢰 마켓플레이스 ════════════════ -->

    <!-- 필터 -->
    <div class="card mb-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
            <input type="hidden" name="tab" value="register">
            <div>
                <label class="block text-xs text-mg-text-muted mb-1">상태</label>
                <select name="status" class="px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                    <option value="">전체</option>
                    <option value="recruiting" <?php echo $search_status === 'recruiting' ? 'selected' : ''; ?>>모집중</option>
                    <option value="matched" <?php echo $search_status === 'matched' ? 'selected' : ''; ?>>진행중</option>
                    <option value="completed" <?php echo $search_status === 'completed' ? 'selected' : ''; ?>>완료</option>
                    <option value="expired" <?php echo $search_status === 'expired' ? 'selected' : ''; ?>>만료</option>
                    <option value="cancelled" <?php echo $search_status === 'cancelled' ? 'selected' : ''; ?>>취소</option>
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

    <!-- 목록 -->
    <div class="space-y-3">
        <?php foreach ($result['items'] as $item) {
            $type_label = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];
            $is_highlighted = $item['cc_highlight'] && strtotime($item['cc_highlight']) > time();

            $status_class = '';
            $status_text = '';
            switch ($item['cc_status']) {
                case 'recruiting': $status_class = 'bg-mg-accent/20 text-mg-accent'; $status_text = '모집중'; break;
                case 'matched': $status_class = 'bg-yellow-500/20 text-yellow-400'; $status_text = '진행중'; break;
                case 'completed': $status_class = 'bg-mg-success/20 text-mg-success'; $status_text = '완료'; break;
                case 'expired': $status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $status_text = '만료'; break;
                case 'cancelled': $status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $status_text = '취소'; break;
                case 'force_closed': $status_class = 'bg-red-500/20 text-red-400'; $status_text = '미이행'; break;
            }

            $deadline_diff = strtotime($item['cc_deadline']) - time();
            $deadline_text = '';
            if ($item['cc_status'] === 'recruiting' && $deadline_diff > 0) {
                if ($deadline_diff < 86400) {
                    $deadline_text = floor($deadline_diff / 3600) . '시간 남음';
                } else {
                    $deadline_text = floor($deadline_diff / 86400) . '일 남음';
                }
            }
        ?>
        <a href="<?php echo G5_BBS_URL; ?>/concierge_view.php?cc_id=<?php echo $item['cc_id']; ?>"
           class="card block hover:border-mg-accent border border-transparent transition-colors <?php if ($is_highlighted) echo 'ring-1 ring-mg-accent'; ?>">
            <div class="flex items-start gap-3">
                <?php if ($item['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($item['ch_thumb']); ?>" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                <?php } else { ?>
                <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($item['cc_title']); ?></span>
                        <span class="px-2 py-0.5 text-xs rounded <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                    </div>
                    <div class="flex items-center gap-3 mt-1 text-xs text-mg-text-muted">
                        <span><?php echo htmlspecialchars($item['ch_name'] ?: $item['mb_nick']); ?></span>
                        <span><?php echo $type_label; ?></span>
                        <span>지원 <?php echo $item['apply_count']; ?>/<?php echo $item['cc_max_members']; ?>명</span>
                        <?php if ($item['cc_match_mode'] === 'lottery') { ?><span>추첨</span><?php } ?>
                        <?php if ($deadline_text) { ?><span class="text-mg-accent"><?php echo $deadline_text; ?></span><?php } ?>
                    </div>
                </div>
            </div>
        </a>
        <?php } ?>

        <?php if (empty($result['items'])) { ?>
        <div class="card text-center py-12">
            <div class="mb-3"><svg class="w-12 h-12 mx-auto" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
            <p class="text-mg-text-muted">등록된 의뢰가 없습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/concierge_write.php" class="inline-block mt-3 px-4 py-2 bg-mg-accent text-mg-bg-primary rounded-lg text-sm">첫 의뢰 등록하기</a>
        </div>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($result['total_pages'] > 1) { ?>
    <div class="flex justify-center gap-1 mt-6">
        <?php
        $query_params = array('tab=register');
        if ($search_status) $query_params[] = 'status=' . urlencode($search_status);
        if ($search_type) $query_params[] = 'type=' . urlencode($search_type);
        $base_url = G5_BBS_URL . '/concierge.php?' . implode('&', $query_params) . '&';

        for ($p = max(1, $page - 4); $p <= min($result['total_pages'], $page + 4); $p++) {
            $active = ($p === $page) ? 'bg-mg-accent text-mg-bg-primary' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-accent/20';
        ?>
        <a href="<?php echo $base_url . 'page=' . $p; ?>" class="px-3 py-1.5 rounded text-sm <?php echo $active; ?>"><?php echo $p; ?></a>
        <?php } ?>
    </div>
    <?php } ?>

<?php } else { ?>
    <!-- ════════════════ 진행 탭: 나의 의뢰 대시보드 ════════════════ -->

    <!-- 내가 등록한 의뢰 -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            내가 등록한 의뢰
            <span class="text-sm font-normal text-mg-text-muted">(<?php echo count($my_owned); ?>)</span>
        </h2>

        <?php if (empty($my_owned)) { ?>
        <div class="card text-center py-8">
            <p class="text-sm text-mg-text-muted">등록한 의뢰가 없습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/concierge_write.php" class="inline-block mt-2 text-sm text-mg-accent hover:underline">의뢰 등록하기</a>
        </div>
        <?php } else { ?>
        <div class="space-y-3">
            <?php foreach ($my_owned as $item) {
                $type_label = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];

                $status_class = '';
                $status_text = '';
                switch ($item['cc_status']) {
                    case 'recruiting': $status_class = 'bg-mg-accent/20 text-mg-accent'; $status_text = '모집중'; break;
                    case 'matched': $status_class = 'bg-yellow-500/20 text-yellow-400'; $status_text = '진행중'; break;
                    case 'completed': $status_class = 'bg-mg-success/20 text-mg-success'; $status_text = '완료'; break;
                    case 'expired': $status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $status_text = '만료'; break;
                    case 'cancelled': $status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $status_text = '취소'; break;
                    case 'force_closed': $status_class = 'bg-red-500/20 text-red-400'; $status_text = '미이행'; break;
                }
            ?>
            <a href="<?php echo G5_BBS_URL; ?>/concierge_view.php?cc_id=<?php echo $item['cc_id']; ?>"
               class="card block hover:border-mg-accent border border-transparent transition-colors">
                <div class="flex items-start gap-3">
                    <?php if ($item['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($item['ch_thumb']); ?>" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    <?php } else { ?>
                    <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                    <?php } ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($item['cc_title']); ?></span>
                            <span class="px-2 py-0.5 text-xs rounded <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </div>
                        <div class="flex items-center gap-3 mt-1 text-xs text-mg-text-muted">
                            <span><?php echo $type_label; ?></span>
                            <span>지원 <?php echo $item['apply_count']; ?>/<?php echo $item['cc_max_members']; ?>명</span>
                            <span><?php echo substr($item['cc_datetime'], 0, 10); ?></span>
                            <?php if ($item['cc_status'] === 'recruiting') {
                                $d_diff = strtotime($item['cc_deadline']) - time();
                                if ($d_diff > 0) {
                                    $d_text = ($d_diff < 86400) ? floor($d_diff / 3600) . '시간 남음' : floor($d_diff / 86400) . '일 남음';
                                    echo '<span class="text-mg-accent">' . $d_text . '</span>';
                                }
                            } ?>
                        </div>
                    </div>
                </div>
            </a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 내가 지원한 의뢰 -->
    <div>
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            내가 지원한 의뢰
            <span class="text-sm font-normal text-mg-text-muted">(<?php echo count($my_applied); ?>)</span>
        </h2>

        <?php if (empty($my_applied)) { ?>
        <div class="card text-center py-8">
            <p class="text-sm text-mg-text-muted">지원한 의뢰가 없습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/concierge.php?tab=register" class="inline-block mt-2 text-sm text-mg-accent hover:underline">의뢰 둘러보기</a>
        </div>
        <?php } else { ?>
        <div class="space-y-3">
            <?php foreach ($my_applied as $item) {
                $type_label = isset($type_labels[$item['cc_type']]) ? $type_labels[$item['cc_type']] : $item['cc_type'];

                // 의뢰 상태
                $cc_status_class = '';
                $cc_status_text = '';
                switch ($item['cc_status']) {
                    case 'recruiting': $cc_status_class = 'bg-mg-accent/20 text-mg-accent'; $cc_status_text = '모집중'; break;
                    case 'matched': $cc_status_class = 'bg-yellow-500/20 text-yellow-400'; $cc_status_text = '진행중'; break;
                    case 'completed': $cc_status_class = 'bg-mg-success/20 text-mg-success'; $cc_status_text = '완료'; break;
                    case 'expired': $cc_status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $cc_status_text = '만료'; break;
                    case 'cancelled': $cc_status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $cc_status_text = '취소'; break;
                    case 'force_closed': $cc_status_class = 'bg-red-500/20 text-red-400'; $cc_status_text = '미이행'; break;
                }

                // 나의 지원 상태
                $my_status_class = '';
                $my_status_text = '';
                switch ($item['ca_status']) {
                    case 'pending': $my_status_class = 'text-mg-accent'; $my_status_text = '대기중'; break;
                    case 'selected': $my_status_class = 'text-mg-success'; $my_status_text = '선정됨'; break;
                    case 'rejected': $my_status_class = 'text-mg-text-muted'; $my_status_text = '미선정'; break;
                    case 'force_closed': $my_status_class = 'text-red-400'; $my_status_text = '미이행'; break;
                }
            ?>
            <div class="card border border-transparent hover:border-mg-accent transition-colors">
                <a href="<?php echo G5_BBS_URL; ?>/concierge_view.php?cc_id=<?php echo $item['cc_id']; ?>" class="flex items-start gap-3">
                    <?php if ($item['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($item['ch_thumb']); ?>" class="w-10 h-10 rounded-full object-cover flex-shrink-0">
                    <?php } else { ?>
                    <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                    <?php } ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($item['cc_title']); ?></span>
                            <span class="px-2 py-0.5 text-xs rounded <?php echo $cc_status_class; ?>"><?php echo $cc_status_text; ?></span>
                        </div>
                        <div class="flex items-center gap-3 mt-1 text-xs text-mg-text-muted">
                            <span>의뢰자: <?php echo htmlspecialchars($item['ch_name'] ?: $item['mb_nick']); ?></span>
                            <span><?php echo $type_label; ?></span>
                            <span>나의 상태: <span class="font-medium <?php echo $my_status_class; ?>"><?php echo $my_status_text; ?></span></span>
                            <?php if ($item['has_result']) { ?>
                            <span class="text-mg-success">결과물 제출 완료</span>
                            <?php } ?>
                        </div>
                    </div>
                </a>
                <?php if ($item['ca_status'] === 'selected' && $item['cc_status'] === 'matched' && !$item['has_result']) { ?>
                <div class="mt-3 pt-3 border-t border-mg-bg-tertiary">
                    <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=concierge_result&w=w&mg_concierge_id=<?php echo $item['cc_id']; ?>"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-mg-accent text-mg-bg-primary rounded-lg text-sm font-medium hover:bg-mg-accent-hover transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        결과물 등록
                    </a>
                </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

<?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
