<?php
/**
 * Morgan Edition - RP (역극) List Skin
 *
 * Variables:
 *   $result  - array with 'threads', 'total', 'total_page'
 *   $status  - current status filter (all/open/closed)
 *   $my      - whether filtering by current user
 *   $page    - current page number
 *   $member  - current member info
 *   $is_member - boolean
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$threads    = $result['threads'];
$total      = $result['total'];
$total_page = $result['total_page'];

// owner 필터 (특정 회원의 역극 목록)
$owner = isset($_GET['owner']) ? clean_xss_tags($_GET['owner']) : '';
$owner_nick = '';
if ($owner) {
    $owner_row = sql_fetch("SELECT mb_nick FROM {$GLOBALS['g5']['member_table']} WHERE mb_id = '".sql_real_escape_string($owner)."'");
    $owner_nick = $owner_row['mb_nick'] ?? $owner;
}

/**
 * 상대 시간 표시 헬퍼
 */
function rp_time_ago($datetime) {
    $now  = time();
    $time = strtotime($datetime);
    $diff = $now - $time;

    if ($diff < 60) {
        return '방금 전';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . '분 전';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . '시간 전';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . '일 전';
    } else {
        return date('Y.m.d', $time);
    }
}
?>

<div id="rp_list" class="max-w-4xl mx-auto">

    <!-- 헤더 -->
    <div class="card mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <?php if ($owner) { ?>
                    <?php echo htmlspecialchars($owner_nick); ?>님의 역극
                    <?php } else { ?>
                    역극
                    <?php } ?>
                </h1>
                <p class="text-sm text-mg-text-muted mt-1">
                    총 <?php echo number_format($total); ?>개의 역극
                    <?php if ($owner) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/rp_list.php" class="ml-2 text-mg-accent hover:underline">&larr; 전체 목록</a>
                    <?php } ?>
                </p>
            </div>
            <?php if ($is_member) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/rp_write.php" class="btn btn-primary flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                판 세우기
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <?php } ?>
        </div>
    </div>

    <!-- 필터 탭 -->
    <?php if (!$owner) { ?>
    <div class="mb-4 overflow-x-auto">
        <div class="flex gap-2 min-w-max">
            <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?status=open"
               class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo ($status == 'open' && !$my) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                진행중
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?status=closed"
               class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo ($status == 'closed' && !$my) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                완결
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?status=all"
               class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo ($status == 'all' && !$my) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                전체
            </a>
            <?php if ($is_member) { ?>
            <span class="border-l border-mg-bg-tertiary mx-1"></span>
            <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?my=1"
               class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo $my ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                내 역극
            </a>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- 스레드 목록 -->
    <?php if (count($threads) > 0) { ?>
    <div class="space-y-3">
        <?php foreach ($threads as $thread) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/rp_view.php?rt_id=<?php echo $thread['rt_id']; ?>" class="card block hover:ring-1 hover:ring-mg-accent/50 transition-all group">
            <div class="flex items-start gap-3">
                <!-- 캐릭터 썸네일 -->
                <div class="flex-shrink-0">
                    <?php if ($thread['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$thread['ch_thumb']; ?>" alt=""
                         class="w-10 h-10 rounded-full object-cover border-2 border-mg-bg-tertiary group-hover:border-mg-accent transition-colors">
                    <?php } else { ?>
                    <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold border-2 border-mg-bg-tertiary group-hover:border-mg-accent transition-colors">
                        <?php echo mb_substr($thread['ch_name'] ?: $thread['mb_nick'], 0, 1); ?>
                    </div>
                    <?php } ?>
                </div>

                <!-- 정보 -->
                <div class="flex-1 min-w-0">
                    <!-- 제목 + 상태 -->
                    <div class="flex items-center gap-2 mb-1">
                        <h3 class="text-mg-text-primary font-medium truncate group-hover:text-mg-accent transition-colors">
                            <?php echo htmlspecialchars($thread['rt_title']); ?>
                        </h3>
                        <!-- 상태 뱃지 -->
                        <?php if ($thread['rt_status'] == 'open') { ?>
                        <span class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded-full bg-green-500/20 text-green-400">
                            open
                        </span>
                        <?php } elseif ($thread['rt_status'] == 'closed') { ?>
                        <span class="flex-shrink-0 px-2 py-0.5 text-xs font-medium rounded-full bg-red-500/20 text-red-400">
                            closed
                        </span>
                        <?php } ?>
                    </div>

                    <!-- 본문 미리보기 -->
                    <p class="text-sm text-mg-text-secondary mb-2 line-clamp-2">
                        <?php echo htmlspecialchars(mb_substr(strip_tags($thread['rt_content']), 0, 150)); ?>
                    </p>

                    <!-- 판장 + 메타 정보 -->
                    <div class="flex items-center gap-3 text-xs text-mg-text-muted flex-wrap">
                        <span class="text-mg-text-secondary">
                            판장: <?php echo htmlspecialchars($thread['ch_name'] ?: $thread['mb_nick']); ?>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            이음 <?php echo number_format($thread['rt_reply_count']); ?>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <?php echo number_format($thread['member_count']); ?>명
                        </span>
                        <?php if ($thread['rt_max_member'] > 0) { ?>
                        <span class="text-mg-text-muted">(최대 <?php echo $thread['rt_max_member']; ?>)</span>
                        <?php } ?>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <?php echo rp_time_ago($thread['rt_update']); ?>
                        </span>
                    </div>
                </div>

                <!-- 우측 화살표 -->
                <div class="flex-shrink-0 self-center">
                    <svg class="w-5 h-5 text-mg-text-muted group-hover:text-mg-accent transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="mt-8 flex justify-center gap-1">
        <?php
        $query_params = array();
        if ($status && $status != 'all') $query_params[] = "status={$status}";
        if ($my) $query_params[] = "my=1";
        if ($owner) $query_params[] = "owner=".urlencode($owner);
        $query_string = count($query_params) > 0 ? implode('&', $query_params) . '&' : '';

        $start_page = max(1, $page - 2);
        $end_page   = min($total_page, $page + 2);

        if ($page > 1) {
            echo '<a href="?' . $query_string . 'page=1" class="px-3 py-2 bg-mg-bg-secondary text-mg-text-secondary rounded hover:bg-mg-bg-tertiary transition-colors">&laquo;</a>';
            echo '<a href="?' . $query_string . 'page=' . ($page - 1) . '" class="px-3 py-2 bg-mg-bg-secondary text-mg-text-secondary rounded hover:bg-mg-bg-tertiary transition-colors">&lsaquo;</a>';
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                echo '<span class="px-3 py-2 bg-mg-accent text-white rounded font-medium">' . $i . '</span>';
            } else {
                echo '<a href="?' . $query_string . 'page=' . $i . '" class="px-3 py-2 bg-mg-bg-secondary text-mg-text-secondary rounded hover:bg-mg-bg-tertiary transition-colors">' . $i . '</a>';
            }
        }

        if ($page < $total_page) {
            echo '<a href="?' . $query_string . 'page=' . ($page + 1) . '" class="px-3 py-2 bg-mg-bg-secondary text-mg-text-secondary rounded hover:bg-mg-bg-tertiary transition-colors">&rsaquo;</a>';
            echo '<a href="?' . $query_string . 'page=' . $total_page . '" class="px-3 py-2 bg-mg-bg-secondary text-mg-text-secondary rounded hover:bg-mg-bg-tertiary transition-colors">&raquo;</a>';
        }
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="card py-16 text-center">
        <svg class="w-16 h-16 mx-auto text-mg-text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <p class="text-mg-text-muted text-lg mb-2">역극이 없습니다</p>
        <p class="text-mg-text-muted text-sm mb-6">
            <?php if ($status == 'closed') { ?>
            아직 완결된 역극이 없습니다.
            <?php } elseif ($my) { ?>
            참여 중인 역극이 없습니다.
            <?php } else { ?>
            첫 번째 역극의 판을 세워보세요!
            <?php } ?>
        </p>
        <?php if ($is_member) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/rp_write.php" class="btn btn-primary inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            판 세우기
        </a>
        <?php } ?>
    </div>
    <?php } ?>

</div>
