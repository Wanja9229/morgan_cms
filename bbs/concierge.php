<?php
/**
 * Morgan Edition - 의뢰 목록
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (mg_config('concierge_use', '1') != '1') {
    alert_close('의뢰 기능이 비활성화되어 있습니다.');
}

if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$search_status = isset($_GET['status']) ? clean_xss_tags($_GET['status']) : '';
$search_type = isset($_GET['type']) ? clean_xss_tags($_GET['type']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$result = mg_get_concierge_list(
    $search_status ?: null,
    $search_type ?: null,
    $page,
    20
);

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');

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

    <!-- 필터 -->
    <div class="card mb-4">
        <form method="get" class="flex flex-wrap gap-3 items-end">
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
        $query_params = array();
        if ($search_status) $query_params[] = 'status=' . urlencode($search_status);
        if ($search_type) $query_params[] = 'type=' . urlencode($search_type);
        $base_url = G5_BBS_URL . '/concierge.php?' . implode('&', $query_params);
        if (!empty($query_params)) $base_url .= '&';

        for ($p = max(1, $page - 4); $p <= min($result['total_pages'], $page + 4); $p++) {
            $active = ($p === $page) ? 'bg-mg-accent text-mg-bg-primary' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:bg-mg-accent/20';
        ?>
        <a href="<?php echo $base_url . 'page=' . $p; ?>" class="px-3 py-1.5 rounded text-sm <?php echo $active; ?>"><?php echo $p; ?></a>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
