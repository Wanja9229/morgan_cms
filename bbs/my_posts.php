<?php
/**
 * Morgan Edition - 내 글 모아보기
 * 모든 게시판의 내 글 목록 + 활동 통계
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

$mb_id = $member['mb_id'];
$mb_id_esc = sql_real_escape_string($mb_id);

// ─── 게시판 목록 ───
$boards = array();
$board_result = sql_query("SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_order, bo_table");
if ($board_result) {
    while ($row = sql_fetch_array($board_result)) {
        $boards[] = $row;
    }
}

// ─── 게시판 필터 ───
$filter_bo = isset($_GET['bo_table']) ? preg_replace('/[^a-z0-9_]/i', '', $_GET['bo_table']) : '';
$page_num = max(1, (int)($_GET['page'] ?? 1));
$per_page = 20;

// ─── 활동 통계 (전체 게시판 순회) ───
$total_posts = 0;
$total_comments = 0;

foreach ($boards as $bd) {
    $wt = $g5['write_prefix'] . $bd['bo_table'];
    $chk = sql_query("SHOW TABLES LIKE '{$wt}'");
    if (!$chk || !sql_num_rows($chk)) continue;

    $cnt = sql_fetch("SELECT
        SUM(CASE WHEN wr_is_comment = 0 THEN 1 ELSE 0 END) as posts,
        SUM(CASE WHEN wr_is_comment = 1 THEN 1 ELSE 0 END) as comments
        FROM {$wt} WHERE mb_id = '{$mb_id_esc}'");
    $total_posts += (int)($cnt['posts'] ?? 0);
    $total_comments += (int)($cnt['comments'] ?? 0);
}

// 가입 경과일
$join_date = strtotime($member['mb_datetime']);
$days_since = max(1, (int)floor((time() - $join_date) / 86400));

// ─── 글 목록 조회 ───
$all_posts = array();
$target_boards = $filter_bo ? array_filter($boards, function($b) use ($filter_bo) { return $b['bo_table'] === $filter_bo; }) : $boards;

foreach ($target_boards as $bd) {
    $wt = $g5['write_prefix'] . $bd['bo_table'];
    $chk = sql_query("SHOW TABLES LIKE '{$wt}'");
    if (!$chk || !sql_num_rows($chk)) continue;

    $result = sql_query("SELECT wr_id, wr_subject, wr_datetime, wr_comment, wr_hit
        FROM {$wt}
        WHERE mb_id = '{$mb_id_esc}' AND wr_is_comment = 0
        ORDER BY wr_datetime DESC");
    if ($result) {
        while ($row = sql_fetch_array($result)) {
            $row['bo_table'] = $bd['bo_table'];
            $row['bo_subject'] = $bd['bo_subject'];
            $all_posts[] = $row;
        }
    }
}

// 날짜순 정렬
usort($all_posts, function($a, $b) {
    return strcmp($b['wr_datetime'], $a['wr_datetime']);
});

// 페이지네이션
$total_count = count($all_posts);
$total_pages = max(1, (int)ceil($total_count / $per_page));
if ($page_num > $total_pages) $page_num = $total_pages;
$offset = ($page_num - 1) * $per_page;
$paged_posts = array_slice($all_posts, $offset, $per_page);

$g5['title'] = '내 글 모아보기';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner px-4 py-6">
    <h1 class="text-xl font-bold text-mg-text-primary mb-6">내 글 모아보기</h1>

    <!-- 활동 통계 -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 text-center">
            <div class="text-2xl font-bold text-mg-accent"><?php echo number_format($total_posts); ?></div>
            <div class="text-xs text-mg-text-muted mt-1">작성한 글</div>
        </div>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 text-center">
            <div class="text-2xl font-bold text-mg-accent"><?php echo number_format($total_comments); ?></div>
            <div class="text-xs text-mg-text-muted mt-1">작성한 댓글</div>
        </div>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 text-center">
            <div class="text-2xl font-bold text-mg-text-primary"><?php echo number_format($days_since); ?><span class="text-sm font-normal text-mg-text-muted">일</span></div>
            <div class="text-xs text-mg-text-muted mt-1">가입 경과</div>
        </div>
    </div>

    <!-- 게시판 필터 -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <select onchange="location.href='?bo_table='+this.value" class="bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary text-sm rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-mg-accent">
                <option value="">전체 게시판</option>
                <?php foreach ($boards as $bd) { ?>
                <option value="<?php echo $bd['bo_table']; ?>" <?php echo $filter_bo === $bd['bo_table'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($bd['bo_subject']); ?></option>
                <?php } ?>
            </select>
        </div>
        <span class="text-sm text-mg-text-muted"><?php echo number_format($total_count); ?>개의 글</span>
    </div>

    <!-- 글 목록 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
        <?php if (count($paged_posts) > 0) { ?>
        <table class="w-full">
            <thead>
                <tr class="bg-mg-bg-tertiary/50 text-xs text-mg-text-muted">
                    <th class="px-4 py-3 text-left" style="width:150px;">게시판</th>
                    <th class="px-4 py-3 text-left">제목</th>
                    <th class="px-4 py-3 text-center" style="width:90px;">작성일</th>
                    <th class="px-4 py-3 text-center" style="width:65px;">댓글</th>
                    <th class="px-4 py-3 text-center" style="width:65px;">조회</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paged_posts as $post) { ?>
                <tr class="border-t border-mg-bg-tertiary hover:bg-mg-bg-tertiary/30 transition-colors">
                    <td class="px-4 py-3">
                        <span class="text-xs px-2 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted"><?php echo htmlspecialchars($post['bo_subject']); ?></span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $post['bo_table']; ?>&wr_id=<?php echo $post['wr_id']; ?>" class="text-sm text-mg-text-primary hover:text-mg-accent transition-colors">
                            <?php echo htmlspecialchars($post['wr_subject']); ?>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-xs text-mg-text-muted text-center"><?php echo substr($post['wr_datetime'], 2, 8); ?></td>
                    <td class="px-4 py-3 text-xs text-mg-text-muted text-center"><?php echo (int)$post['wr_comment']; ?></td>
                    <td class="px-4 py-3 text-xs text-mg-text-muted text-center"><?php echo (int)$post['wr_hit']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } else { ?>
        <div class="py-12 text-center">
            <svg class="w-12 h-12 text-mg-text-muted mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-mg-text-muted">작성한 글이 없습니다</p>
        </div>
        <?php } ?>
    </div>

    <?php if ($total_pages > 1) { ?>
    <!-- 페이지네이션 -->
    <div class="flex items-center justify-center gap-1 mt-6">
        <?php
        $query_base = $filter_bo ? "bo_table={$filter_bo}&" : '';
        $range = 2;
        $start_page = max(1, $page_num - $range);
        $end_page = min($total_pages, $page_num + $range);

        if ($page_num > 1) { ?>
        <a href="?<?php echo $query_base; ?>page=1" class="px-3 py-1.5 text-sm rounded bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-text-primary transition-colors">&laquo;</a>
        <a href="?<?php echo $query_base; ?>page=<?php echo $page_num - 1; ?>" class="px-3 py-1.5 text-sm rounded bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-text-primary transition-colors">&lsaquo;</a>
        <?php }

        for ($i = $start_page; $i <= $end_page; $i++) { ?>
        <a href="?<?php echo $query_base; ?>page=<?php echo $i; ?>" class="px-3 py-1.5 text-sm rounded <?php echo $i === $page_num ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-text-primary'; ?> transition-colors"><?php echo $i; ?></a>
        <?php }

        if ($page_num < $total_pages) { ?>
        <a href="?<?php echo $query_base; ?>page=<?php echo $page_num + 1; ?>" class="px-3 py-1.5 text-sm rounded bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-text-primary transition-colors">&rsaquo;</a>
        <a href="?<?php echo $query_base; ?>page=<?php echo $total_pages; ?>" class="px-3 py-1.5 text-sm rounded bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-text-primary transition-colors">&raquo;</a>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
