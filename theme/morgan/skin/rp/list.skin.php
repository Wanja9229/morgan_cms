<?php
/**
 * Morgan Edition - RP (역극) List Skin (View-in-List)
 *
 * Variables:
 *   $result          - array with 'threads', 'total', 'total_page'
 *   $status          - current status filter (all/open/closed)
 *   $my              - whether filtering by current user
 *   $page            - current page number
 *   $member          - current member info
 *   $is_member       - boolean
 *   $my_characters   - current user's usable characters
 *   $can_create      - array with 'can_create' key
 *   $max_member_default - default max members
 *   $max_member_limit   - max members limit
 */

if (!defined('_GNUBOARD_')) exit;

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$threads    = $result['threads'];
$total      = $result['total'];
$total_page = $result['total_page'];

// owner 필터
$owner = isset($_GET['owner']) ? clean_xss_tags($_GET['owner']) : '';
$owner_nick = '';
if ($owner) {
    $owner_row = sql_fetch("SELECT mb_nick FROM {$GLOBALS['g5']['member_table']} WHERE mb_id = '".sql_real_escape_string($owner)."'");
    $owner_nick = $owner_row['mb_nick'] ?? $owner;
}

// 기본 캐릭터 ID
$default_ch_id = 0;
foreach ($my_characters as $ch) {
    if ($ch['ch_main']) { $default_ch_id = $ch['ch_id']; break; }
}
if (!$default_ch_id && count($my_characters) > 0) {
    $default_ch_id = $my_characters[0]['ch_id'];
}

function rp_time_ago($datetime) {
    $now  = time();
    $time = strtotime($datetime);
    $diff = $now - $time;
    if ($diff < 60) return '방금 전';
    elseif ($diff < 3600) return floor($diff / 60) . '분 전';
    elseif ($diff < 86400) return floor($diff / 3600) . '시간 전';
    elseif ($diff < 604800) return floor($diff / 86400) . '일 전';
    else return date('Y.m.d', $time);
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
            <?php if ($is_member && $can_create['can_create']) { ?>
            <button type="button" onclick="openWriteModal()" class="btn btn-primary flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                새 글
            </button>
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
    <div class="space-y-4">
        <?php foreach ($threads as $thread) {
            $is_owner = ($is_member && $thread['mb_id'] == $member['mb_id']);
            $is_open = ($thread['rt_status'] == 'open');
            $thread_members = isset($thread['members']) ? $thread['members'] : array();
            // 참여 여부 / 정원 초과 체크
            $is_already_joined = false;
            if ($is_member) {
                foreach ($thread_members as $_m) {
                    if ($_m['mb_id'] == $member['mb_id']) { $is_already_joined = true; break; }
                }
            }
            $is_full = ($thread['rt_max_member'] > 0 && count($thread_members) >= $thread['rt_max_member']);
        ?>
        <div class="card" id="rp-thread-<?php echo $thread['rt_id']; ?>" data-rt-id="<?php echo $thread['rt_id']; ?>" data-status="<?php echo $is_open ? 'open' : 'closed'; ?>" data-owner="<?php echo htmlspecialchars($thread['mb_id']); ?>" data-owner-ch="<?php echo (int)$thread['ch_id']; ?>">

            <!-- 스레드 헤더 -->
            <div class="flex items-start justify-between gap-3 mb-3">
                <div class="flex items-start gap-3 min-w-0">
                    <!-- 작성자 캐릭터 썸네일 -->
                    <div class="flex-shrink-0">
                        <?php if ($thread['ch_thumb']) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$thread['ch_thumb']; ?>" alt=""
                             class="w-10 h-10 rounded-full object-cover border-2 border-mg-accent/30">
                        <?php } else { ?>
                        <div class="w-10 h-10 rounded-full bg-mg-accent/20 flex items-center justify-center border-2 border-mg-accent/30">
                            <span class="text-sm font-bold text-mg-accent"><?php echo mb_substr($thread['ch_name'] ?: $thread['mb_nick'], 0, 1); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <div class="min-w-0">
                        <!-- 제목 + 상태 -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-lg font-bold text-mg-text-primary"><?php echo htmlspecialchars($thread['rt_title']); ?></h3>
                            <?php if ($is_open) { ?>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-500/20 text-green-400">진행중</span>
                            <?php } else { ?>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-500/20 text-red-400">완결</span>
                            <?php } ?>
                        </div>
                        <!-- 작성자 정보 -->
                        <p class="text-xs text-mg-text-muted mt-0.5">
                            <?php echo htmlspecialchars($thread['ch_name'] ?: $thread['mb_nick']); ?>
                            <span class="text-mg-text-muted/50">@<?php echo htmlspecialchars($thread['mb_nick']); ?></span>
                            &middot; <?php echo rp_time_ago($thread['rt_datetime']); ?>
                            &middot; 댓글 <?php echo number_format($thread['rt_reply_count']); ?>개
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <!-- 완결 버튼 (작성자 + open일 때만) -->
                    <?php if ($is_owner && $is_open) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/rp_close.php?rt_id=<?php echo $thread['rt_id']; ?>"
                       onclick="return confirm('이 역극을 완결하시겠습니까?');"
                       class="text-xs px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-muted hover:bg-red-500/20 hover:text-red-400 transition-colors"
                       data-no-spa>
                        완결
                    </a>
                    <?php } ?>
                    <!-- 관리자: 판 삭제 버튼 -->
                    <?php if ($is_admin === 'super') { ?>
                    <button type="button" onclick="deleteThread(<?php echo $thread['rt_id']; ?>)"
                            class="text-xs px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-muted hover:bg-red-500/20 hover:text-red-400 transition-colors" title="관리자 삭제">
                        삭제
                    </button>
                    <?php } ?>
                </div>
            </div>

            <!-- 본문 전체 표시 -->
            <div class="text-sm text-mg-text-secondary leading-relaxed mb-4 whitespace-pre-line">
                <?php
                $content_html = htmlspecialchars($thread['rt_content']);
                if (function_exists('mg_render_emoticons')) {
                    $content_html = mg_render_emoticons($content_html);
                }
                echo nl2br($content_html);
                ?>
            </div>

            <!-- 첨부 이미지 -->
            <?php if (!empty($thread['rt_image'])) { ?>
            <div class="mb-4">
                <img src="<?php echo htmlspecialchars($thread['rt_image']); ?>" alt="" class="rounded-lg max-h-64 object-cover cursor-pointer hover:opacity-80 transition-opacity" onclick="openImageModal(this.src)">
            </div>
            <?php } ?>

            <!-- 참여자 섹션 -->
            <div class="border-t border-mg-bg-tertiary pt-3">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-medium text-mg-text-muted">참여자</span>
                    <span class="text-xs text-mg-text-muted/70">(<?php echo count($thread_members); ?>명<?php if ($thread['rt_max_member'] > 0) echo ' / 최대 '.$thread['rt_max_member']; ?>)</span>
                </div>

                <div class="rp-participants space-y-1" id="rp-participants-<?php echo $thread['rt_id']; ?>">
                    <?php if (count($thread_members) > 0) { ?>
                    <?php foreach ($thread_members as $mem) { ?>
                    <div class="rp-participant" data-rt-id="<?php echo $thread['rt_id']; ?>" data-ch-id="<?php echo $mem['ch_id']; ?>">
                        <!-- 참여자 행 (클릭 가능) -->
                        <button type="button" onclick="toggleMessenger(this)"
                                class="w-full flex items-center gap-2 py-2 px-3 rounded-lg hover:bg-mg-bg-tertiary/50 transition-colors text-left">
                            <?php if (!empty($mem['ch_thumb'])) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$mem['ch_thumb']; ?>" alt=""
                                 class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                            <?php } else { ?>
                            <div class="w-7 h-7 rounded-full bg-mg-bg-tertiary flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-bold text-mg-accent"><?php echo mb_substr($mem['ch_name'] ?: $mem['mb_nick'], 0, 1); ?></span>
                            </div>
                            <?php } ?>
                            <span class="text-sm font-medium text-mg-text-primary"><?php echo htmlspecialchars($mem['ch_name'] ?: $mem['mb_nick']); ?></span>
                            <span class="text-xs text-mg-text-muted rp-reply-count">(댓글 <?php echo (int)$mem['rm_reply_count']; ?>개)</span>
                            <!-- 토글 화살표 -->
                            <svg class="w-4 h-4 ml-auto text-mg-text-muted rp-toggle-arrow transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>

                        <!-- 메신저 컨테이너 (숨김, AJAX로 로딩) -->
                        <div class="rp-messenger hidden ml-9 mt-1 mb-2 bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary overflow-hidden"
                             data-loaded="false">
                            <div class="rp-messenger-content p-3 max-h-120 overflow-y-auto">
                                <div class="text-center text-mg-text-muted text-xs py-4">불러오는 중...</div>
                            </div>
                            <?php
                            // 메신저 내 댓글 폼: 해당 캐릭터 소유자 OR 원글 작성자
                            $show_messenger_form = ($is_open && $is_member && count($my_characters) > 0
                                && ($mem['mb_id'] == $member['mb_id'] || $is_owner));
                            if ($show_messenger_form) { ?>
                            <div class="border-t border-mg-bg-tertiary p-2">
                                <form class="rp-reply-form flex items-start gap-2" data-rt-id="<?php echo $thread['rt_id']; ?>">
                                    <input type="hidden" name="context_ch_id" value="<?php echo $mem['ch_id']; ?>">
                                    <select name="ch_id" class="bg-mg-bg-tertiary text-mg-text-primary text-xs rounded-lg px-2 py-1.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none h-[32px]" style="min-width:100px;">
                                        <?php foreach ($my_characters as $ch) { ?>
                                        <option value="<?php echo $ch['ch_id']; ?>" <?php echo $default_ch_id == $ch['ch_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ch['ch_name']); ?><?php if ($ch['ch_main']) echo ' ★'; ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                    <div class="flex-1">
                                        <textarea name="rr_content" rows="3" class="w-full bg-mg-bg-tertiary/50 text-mg-text-primary text-xs rounded-lg px-2 py-1.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none resize-none"
                                                  placeholder="댓글을 입력하세요..."></textarea>
                                        <div class="rp-image-preview hidden mt-1"></div>
                                    </div>
                                    <div class="flex flex-col gap-1">
                                        <label class="bg-mg-bg-tertiary hover:bg-mg-bg-tertiary/80 text-mg-text-muted rounded-lg px-2 py-1.5 flex-shrink-0 h-[32px] transition-colors cursor-pointer flex items-center" title="이미지 첨부">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <input type="file" name="rr_image" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden rp-image-input">
                                        </label>
                                        <button type="submit" class="bg-mg-accent hover:bg-mg-accent-hover text-white rounded-lg px-2 py-1.5 flex-shrink-0 h-[32px] transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                    <?php } else { ?>
                    <p class="text-sm text-mg-text-muted py-2 px-3">아직 참여자가 없습니다.</p>
                    <?php } ?>
                </div>
            </div>

            <!-- 기본 댓글 작성 폼 / 상태 메시지 -->
            <?php if (!$is_open) { ?>
            <div class="border-t border-mg-bg-tertiary pt-3 mt-3">
                <p class="text-center text-mg-text-muted text-sm py-1 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    완결된 역극입니다
                </p>
            </div>
            <?php } elseif (!$is_member) { ?>
            <?php } elseif (count($my_characters) == 0) { ?>
            <div class="border-t border-mg-bg-tertiary pt-3 mt-3">
                <p class="text-center text-sm py-1">
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="text-mg-accent hover:underline">캐릭터를 등록</a>
                    <span class="text-mg-text-muted">하면 댓글을 작성할 수 있습니다.</span>
                </p>
            </div>
            <?php } elseif ($is_already_joined) { ?>
            <!-- 이미 참여 중 - 메신저 탭에서 댓글 작성 -->
            <?php } elseif ($is_full) { ?>
            <div class="border-t border-mg-bg-tertiary pt-3 mt-3">
                <p class="text-center text-mg-text-muted text-sm py-1 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    참여 인원이 가득 찼습니다 (<?php echo count($thread_members); ?>/<?php echo $thread['rt_max_member']; ?>)
                </p>
            </div>
            <?php } else { ?>
            <div class="border-t border-mg-bg-tertiary pt-3 mt-3">
                <form class="rp-reply-form flex items-start gap-2" data-rt-id="<?php echo $thread['rt_id']; ?>">
                    <select name="ch_id" class="bg-mg-bg-tertiary text-mg-text-primary text-sm rounded-lg px-3 py-2 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none h-[38px]" style="min-width:120px;">
                        <?php foreach ($my_characters as $ch) { ?>
                        <option value="<?php echo $ch['ch_id']; ?>" <?php echo $default_ch_id == $ch['ch_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ch['ch_name']); ?><?php if ($ch['ch_main']) echo ' ★'; ?>
                        </option>
                        <?php } ?>
                    </select>
                    <div class="flex-1">
                        <textarea name="rr_content" rows="2" class="w-full bg-mg-bg-primary text-mg-text-primary text-sm rounded-lg px-3 py-2 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none resize-none"
                                  placeholder="댓글을 입력하세요..."></textarea>
                        <div class="rp-image-preview hidden mt-1"></div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="bg-mg-bg-tertiary hover:bg-mg-bg-tertiary/80 text-mg-text-muted rounded-lg px-3 py-2 flex-shrink-0 h-[38px] transition-colors cursor-pointer flex items-center" title="이미지 첨부">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <input type="file" name="rr_image" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden rp-image-input">
                        </label>
                        <button type="submit" class="bg-mg-accent hover:bg-mg-accent-hover text-white rounded-lg px-4 py-2 flex-shrink-0 h-[38px] transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
            <?php } ?>

        </div>
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
            첫 번째 역극을 시작해보세요!
            <?php } ?>
        </p>
        <?php if ($is_member && $can_create['can_create']) { ?>
        <button type="button" onclick="openWriteModal()" class="btn btn-primary inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            새 글
        </button>
        <?php } ?>
    </div>
    <?php } ?>

</div>

<!-- 글쓰기 모달 -->
<?php if ($is_member && $can_create['can_create'] && count($my_characters) > 0) { ?>
<style>
#rp-write-modal { top:3rem; left:3.5rem; right:0; bottom:0; }
@media (min-width:1024px) { #rp-write-modal { right:18rem; } }
</style>
<div id="rp-write-modal" class="fixed z-50 hidden flex items-center justify-center p-4 md:p-6">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" onclick="closeWriteModal()"></div>
    <div class="relative z-10 w-full max-w-2xl bg-mg-bg-secondary rounded-xl shadow-2xl overflow-y-auto max-h-[80vh]">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-mg-text-primary">새 역극</h2>
                <button type="button" onclick="closeWriteModal()" class="text-mg-text-muted hover:text-mg-text-primary transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form name="frp_write" id="frp_write" action="<?php echo G5_BBS_URL; ?>/rp_write_update.php" method="post" enctype="multipart/form-data" onsubmit="return frp_write_submit(this);" autocomplete="off">
                <input type="hidden" name="token" value="<?php echo isset($_SESSION['ss_token']) ? $_SESSION['ss_token'] : ''; ?>">

                <!-- 캐릭터 선택 -->
                <div class="mb-5">
                    <label class="block text-sm font-medium text-mg-text-secondary mb-2">캐릭터 선택 <span class="text-mg-error">*</span></label>
                    <div class="flex flex-wrap gap-2" id="mg-character-selector">
                        <?php foreach ($my_characters as $ch) { ?>
                        <label class="character-option cursor-pointer">
                            <input type="radio" name="ch_id" value="<?php echo $ch['ch_id']; ?>" <?php echo $default_ch_id == $ch['ch_id'] ? 'checked' : ''; ?> class="hidden">
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg border transition-colors character-badge <?php echo $default_ch_id == $ch['ch_id'] ? 'border-mg-accent ring-2 ring-mg-accent/30' : 'border-mg-bg-tertiary bg-mg-bg-primary hover:border-mg-accent'; ?>">
                                <?php if ($ch['ch_thumb']) { ?>
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$ch['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover">
                                <?php } else { ?>
                                <div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center">
                                    <span class="text-xs font-bold text-mg-accent"><?php echo mb_substr($ch['ch_name'], 0, 1); ?></span>
                                </div>
                                <?php } ?>
                                <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($ch['ch_name']); ?></span>
                                <?php if ($ch['ch_main']) { ?>
                                <span class="text-xs bg-mg-accent text-white px-1.5 py-0.5 rounded">대표</span>
                                <?php } ?>
                            </div>
                        </label>
                        <?php } ?>
                    </div>
                </div>

                <!-- 제목 -->
                <div class="mb-4">
                    <label for="rt_title" class="block text-sm font-medium text-mg-text-secondary mb-2">제목 <span class="text-mg-error">*</span></label>
                    <input type="text" name="rt_title" id="rt_title" class="w-full bg-mg-bg-primary text-mg-text-primary rounded-lg px-4 py-2.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none" required placeholder="역극 제목을 입력하세요">
                </div>

                <!-- 내용 -->
                <div class="mb-4">
                    <label for="rt_content" class="block text-sm font-medium text-mg-text-secondary mb-2">내용 <span class="text-mg-error">*</span></label>
                    <textarea name="rt_content" id="rt_content" rows="8" class="w-full bg-mg-bg-primary text-mg-text-primary rounded-lg px-4 py-2.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none resize-y" required placeholder="역극의 시작 내용을 작성하세요."></textarea>
                </div>

                <!-- 이미지 업로드 -->
                <div class="mb-4">
                    <label for="rt_image" class="block text-sm font-medium text-mg-text-secondary mb-2">대표 이미지 <span class="text-mg-text-muted font-normal">(선택)</span></label>
                    <input type="file" name="rt_image" id="rt_image" accept="image/jpeg,image/png,image/gif,image/webp" class="block w-full text-sm text-mg-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:bg-mg-bg-tertiary file:text-mg-text-primary hover:file:bg-mg-accent/20">
                </div>

                <!-- 최대 참여자 수 -->
                <div class="mb-6">
                    <label for="rt_max_member" class="block text-sm font-medium text-mg-text-secondary mb-2">최대 참여자 수</label>
                    <input type="number" name="rt_max_member" id="rt_max_member" value="<?php echo $max_member_default; ?>" min="0" max="<?php echo $max_member_limit; ?>" class="w-28 bg-mg-bg-primary text-mg-text-primary rounded-lg px-4 py-2.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none">
                    <p class="text-xs text-mg-text-muted mt-1">0 = 제한 없음 (최대 <?php echo $max_member_limit; ?>명)</p>
                </div>

                <!-- 버튼 -->
                <div class="flex items-center justify-end gap-3">
                    <button type="button" onclick="closeWriteModal()" class="btn btn-secondary">취소</button>
                    <button type="submit" class="btn btn-primary">글쓰기</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php } ?>

<script>
(function() {
    var RP_API_URL = '<?php echo G5_BBS_URL; ?>/rp_api.php';
    var RP_REPLY_URL = '<?php echo G5_BBS_URL; ?>/rp_reply.php';
    var CHAR_IMAGE_URL = '<?php echo MG_CHAR_IMAGE_URL; ?>';
    var MY_CHARACTERS = <?php echo json_encode(array_map(function($ch) { return array('ch_id' => $ch['ch_id'], 'ch_name' => $ch['ch_name'], 'ch_main' => $ch['ch_main']); }, $my_characters)); ?>;
    var IS_MEMBER = <?php echo $is_member ? 'true' : 'false'; ?>;
    var IS_ADMIN = <?php echo ($is_admin === 'super') ? 'true' : 'false'; ?>;
    var CURRENT_MB_ID = '<?php echo $is_member ? addslashes($member['mb_id']) : ''; ?>';
    var SERVER_UTC_OFFSET = <?php echo (int)date('Z'); ?>; // 서버 타임존 오프셋 (초)

    // === 메신저 토글 ===
    window.toggleMessenger = function(btn) {
        var participant = btn.closest('.rp-participant');
        var card = participant.closest('.card');
        var messenger = participant.querySelector('.rp-messenger');
        var arrow = btn.querySelector('.rp-toggle-arrow');

        if (messenger.classList.contains('hidden')) {
            // 같은 스레드의 다른 메신저 닫기
            card.querySelectorAll('.rp-participant').forEach(function(p) {
                if (p !== participant) {
                    var m = p.querySelector('.rp-messenger');
                    var a = p.querySelector('.rp-toggle-arrow');
                    if (m && !m.classList.contains('hidden')) {
                        m.classList.add('hidden');
                        if (a) a.style.transform = '';
                    }
                }
            });
            messenger.classList.remove('hidden');
            if (arrow) arrow.style.transform = 'rotate(90deg)';
            // 전체 댓글 로드 (항상 새로 로드하여 최신 상태 유지)
            loadReplies(participant);
        } else {
            messenger.classList.add('hidden');
            if (arrow) arrow.style.transform = '';
        }
    };

    function loadReplies(participant) {
        var rtId = participant.dataset.rtId;
        var chId = participant.dataset.chId;
        var card = participant.closest('.card');
        var ownerMbId = card ? (card.dataset.owner || '') : '';
        var container = participant.querySelector('.rp-messenger-content');

        fetch(RP_API_URL + '?action=replies&rt_id=' + rtId + '&ch_id=' + chId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.replies.length > 0) {
                    container.innerHTML = data.replies.map(function(r, i, a) { return renderReply(r, i, a, ownerMbId); }).join('');
                    container.scrollTop = container.scrollHeight;
                } else if (data.success) {
                    container.innerHTML = '<div class="text-center text-mg-text-muted text-xs py-4">댓글이 없습니다.</div>';
                } else {
                    container.innerHTML = '<div class="text-center text-mg-error text-xs py-4">불러오기 실패</div>';
                }
                participant.querySelector('.rp-messenger').dataset.loaded = 'true';
            })
            .catch(function() {
                container.innerHTML = '<div class="text-center text-mg-error text-xs py-4">오류가 발생했습니다.</div>';
            });
    }

    function renderReply(reply, index, allReplies, ownerMbId, forceFirst) {
        var time = timeAgo(reply.rr_datetime);
        var content = reply.rr_content_html || nl2br(escapeHtml(reply.rr_content));
        var imageHtml = '';
        if (reply.rr_image) {
            imageHtml = '<div class="mt-1.5"><img src="' + escapeHtml(reply.rr_image) + '" class="rounded-lg max-h-40 object-cover cursor-pointer hover:opacity-80 transition-opacity" onclick="openImageModal(this.src)" alt=""></div>';
        }

        var isOwner = reply.mb_id === ownerMbId;
        var canEdit = IS_MEMBER && reply.mb_id === CURRENT_MB_ID;
        var canDelete = canEdit || IS_ADMIN;

        // 그루핑: 같은 캐릭터의 연속 메시지 판별
        var isFirstInGroup;
        if (forceFirst !== undefined) {
            isFirstInGroup = forceFirst;
        } else {
            var prev = (allReplies && index > 0) ? allReplies[index - 1] : null;
            isFirstInGroup = !prev || String(prev.ch_id) !== String(reply.ch_id);
        }
        var needsGap = isFirstInGroup && ((allReplies && index > 0) || forceFirst === true);
        var gapStyle = needsGap ? 'margin-top:0.625rem;' : '';

        var charName = escapeHtml(reply.ch_name || reply.mb_nick || '');
        var avatarHtml = reply.ch_thumb
            ? '<img src="' + CHAR_IMAGE_URL + '/' + escapeHtml(reply.ch_thumb) + '" class="w-5 h-5 rounded-full object-cover flex-shrink-0" alt="">'
            : '<div class="w-5 h-5 rounded-full bg-mg-accent/20 flex items-center justify-center flex-shrink-0"><span class="text-[9px] font-bold text-mg-accent">' + charName.charAt(0) + '</span></div>';

        var clockIcon = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

        var actionsHtml = '';
        if (canEdit || canDelete) {
            var editBtn = canEdit
                ? '<button type="button" onclick="editReply(this,' + reply.rr_id + ')" class="p-0.5 text-mg-text-muted hover:text-mg-accent transition-colors" title="수정">' +
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>' +
                  '</button>'
                : '';
            var deleteBtn = canDelete
                ? '<button type="button" onclick="deleteReply(this,' + reply.rr_id + ')" class="p-0.5 text-mg-text-muted hover:text-red-400 transition-colors" title="삭제">' +
                    '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>' +
                  '</button>'
                : '';
            actionsHtml = '<span class="rp-reply-actions flex items-center gap-1">' + editBtn + deleteBtn + '</span>';
        }

        var tailClass = isFirstInGroup ? (isOwner ? ' rounded-tr-sm' : ' rounded-tl-sm') : '';

        if (isOwner) {
            // 오른쪽 (원글 작성자)
            var headerHtml = isFirstInGroup
                ? '<div class="flex items-center gap-1.5 mb-1" style="justify-content:flex-end;">' + '<span class="text-xs font-medium text-mg-text-primary">' + charName + '</span>' + avatarHtml + '</div>'
                : '';
            return '<div class="rp-reply-item" data-rr-id="' + reply.rr_id + '" data-ch-id="' + reply.ch_id + '" style="margin-bottom:0.125rem;' + gapStyle + '">' +
                headerHtml +
                '<div class="flex items-end gap-1.5" style="margin-right:1.625rem;justify-content:flex-end;">' +
                    actionsHtml +
                    '<span class="text-[10px] text-mg-text-muted/50 flex items-center gap-0.5 flex-shrink-0 pb-0.5">' + clockIcon + time + '</span>' +
                    '<div class="bg-mg-accent/20 rounded-2xl' + tailClass + ' px-3 py-2" style="max-width:80%;">' +
                        '<div class="rp-reply-content text-sm text-mg-text-secondary leading-relaxed">' + content + '</div>' +
                        imageHtml +
                    '</div>' +
                '</div>' +
            '</div>';
        } else {
            // 왼쪽 (댓글 작성자)
            var headerHtml = isFirstInGroup
                ? '<div class="flex items-center gap-1.5 mb-1">' + avatarHtml + '<span class="text-xs font-medium text-mg-text-primary">' + charName + '</span></div>'
                : '';
            return '<div class="rp-reply-item" data-rr-id="' + reply.rr_id + '" data-ch-id="' + reply.ch_id + '" style="margin-bottom:0.125rem;' + gapStyle + '">' +
                headerHtml +
                '<div class="flex items-end gap-1.5" style="margin-left:1.625rem;">' +
                    '<div class="bg-mg-bg-tertiary rounded-2xl' + tailClass + ' px-3 py-2" style="max-width:80%;">' +
                        '<div class="rp-reply-content text-sm text-mg-text-secondary leading-relaxed">' + content + '</div>' +
                        imageHtml +
                    '</div>' +
                    '<span class="text-[10px] text-mg-text-muted/50 flex items-center gap-0.5 flex-shrink-0 pb-0.5">' + clockIcon + time + '</span>' +
                    actionsHtml +
                '</div>' +
            '</div>';
        }
    }

    // === 댓글 전송 ===
    document.addEventListener('submit', function(e) {
        if (!e.target.classList.contains('rp-reply-form')) return;
        e.preventDefault();

        var form = e.target;
        var submitBtn = form.querySelector('button[type="submit"]');
        var textarea = form.querySelector('textarea');
        var content = textarea.value.trim();
        var rtId = form.dataset.rtId;
        var fileInput = form.querySelector('input[name="rr_image"]');
        var hasImage = fileInput && fileInput.files && fileInput.files[0];

        if (!content && !hasImage) { textarea.focus(); return; }

        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-50');

        var formData = new FormData();
        formData.append('rt_id', rtId);
        var chInput = form.querySelector('select[name="ch_id"]') || form.querySelector('input[name="ch_id"]');
        formData.append('ch_id', chInput.value);
        formData.append('rr_content', content);
        var contextInput = form.querySelector('input[name="context_ch_id"]');
        formData.append('context_ch_id', contextInput ? contextInput.value : 0);
        if (hasImage) {
            formData.append('rr_image', fileInput.files[0]);
        }

        fetch(RP_REPLY_URL, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    textarea.value = '';
                    // 이미지 입력 초기화
                    if (fileInput) {
                        fileInput.value = '';
                        var preview = form.querySelector('.rp-image-preview');
                        if (preview) { preview.innerHTML = ''; preview.classList.add('hidden'); }
                    }
                    appendReplyToMessenger(rtId, data.reply, form);
                    updateParticipantCount(rtId, data.reply.ch_id);
                } else {
                    alert(data.message || '오류가 발생했습니다.');
                }
            })
            .catch(function() {
                alert('전송 중 오류가 발생했습니다.');
            })
            .finally(function() {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50');
                textarea.focus();
            });
    });

    function appendReplyToContainer(container, reply, ownerMbId) {
        var lastItem = container.querySelector('.rp-reply-item:last-child');
        if (!lastItem) container.innerHTML = '';
        var isFirst = !lastItem || lastItem.dataset.chId !== String(reply.ch_id);
        container.insertAdjacentHTML('beforeend', renderReply(reply, undefined, undefined, ownerMbId, isFirst));
        container.scrollTop = container.scrollHeight;
    }

    function appendReplyToMessenger(rtId, reply, form) {
        var card = document.getElementById('rp-thread-' + rtId);
        if (!card) return;
        var ownerMbId = card.dataset.owner || '';

        // 폼이 메신저 안에 있으면 해당 메신저에 추가
        var messenger = form ? form.closest('.rp-messenger') : null;
        if (messenger && messenger.dataset.loaded === 'true') {
            var container = messenger.querySelector('.rp-messenger-content');
            if (!reply.rr_content_html) {
                reply.rr_content_html = nl2br(escapeHtml(reply.rr_content));
                reply.rr_datetime = new Date().toISOString().slice(0, 19).replace('T', ' ');
            }
            appendReplyToContainer(container, reply, ownerMbId);
        } else {
            // 하단 폼에서 작성한 경우: 해당 캐릭터 메신저가 열려있으면 추가
            var participant = card.querySelector('.rp-participant[data-ch-id="' + reply.ch_id + '"]');
            if (participant) {
                var pMessenger = participant.querySelector('.rp-messenger');
                if (pMessenger && !pMessenger.classList.contains('hidden') && pMessenger.dataset.loaded === 'true') {
                    var container = pMessenger.querySelector('.rp-messenger-content');
                    if (!reply.rr_content_html) {
                        reply.rr_content_html = nl2br(escapeHtml(reply.rr_content));
                        reply.rr_datetime = new Date().toISOString().slice(0, 19).replace('T', ' ');
                    }
                    appendReplyToContainer(container, reply, ownerMbId);
                }
            }
        }

        // 참여자 목록에 없으면 갱신
        var existingParticipant = card.querySelector('.rp-participant[data-ch-id="' + reply.ch_id + '"]');
        if (!existingParticipant) {
            refreshMembers(rtId);
        }
    }

    function updateParticipantCount(rtId, chId) {
        var card = document.getElementById('rp-thread-' + rtId);
        if (!card) return;
        var participant = card.querySelector('.rp-participant[data-ch-id="' + chId + '"]');
        if (!participant) return;
        var countEl = participant.querySelector('.rp-reply-count');
        if (countEl) {
            var text = countEl.textContent;
            var num = parseInt(text.replace(/[^0-9]/g, '')) || 0;
            countEl.textContent = '(댓글 ' + (num + 1) + '개)';
        }
    }

    function refreshMembers(rtId) {
        fetch(RP_API_URL + '?action=members&rt_id=' + rtId)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    rebuildParticipants(rtId, data.members);
                }
            })
            .catch(function(err) {
                console.error('Refresh members error:', err);
            });
    }

    function buildReplyFormHtml(rtId, contextChId) {
        if (!IS_MEMBER || MY_CHARACTERS.length === 0) return '';

        var opts = '';
        MY_CHARACTERS.forEach(function(ch) {
            opts += '<option value="' + ch.ch_id + '">' + escapeHtml(ch.ch_name) + (ch.ch_main ? ' ★' : '') + '</option>';
        });
        var charHtml = '<select name="ch_id" class="bg-mg-bg-tertiary text-mg-text-primary text-xs rounded-lg px-2 py-1.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none h-[32px]" style="min-width:100px;">' + opts + '</select>';
        var contextHtml = '<input type="hidden" name="context_ch_id" value="' + (contextChId || 0) + '">';

        return '<div class="border-t border-mg-bg-tertiary p-2">' +
            '<form class="rp-reply-form flex items-start gap-2" data-rt-id="' + rtId + '">' +
                contextHtml +
                charHtml +
                '<div class="flex-1"><textarea name="rr_content" rows="3" class="w-full bg-mg-bg-tertiary/50 text-mg-text-primary text-xs rounded-lg px-2 py-1.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none resize-none" placeholder="댓글을 입력하세요..."></textarea><div class="rp-image-preview hidden mt-1"></div></div>' +
                '<div class="flex flex-col gap-1">' +
                    '<label class="bg-mg-bg-tertiary hover:bg-mg-bg-tertiary/80 text-mg-text-muted rounded-lg px-2 py-1.5 flex-shrink-0 h-[32px] transition-colors cursor-pointer flex items-center" title="이미지 첨부">' +
                        '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>' +
                        '<input type="file" name="rr_image" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden rp-image-input">' +
                    '</label>' +
                    '<button type="submit" class="bg-mg-accent hover:bg-mg-accent-hover text-white rounded-lg px-2 py-1.5 flex-shrink-0 h-[32px] transition-colors"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></button>' +
                '</div>' +
            '</form>' +
        '</div>';
    }

    function rebuildParticipants(rtId, members) {
        var container = document.getElementById('rp-participants-' + rtId);
        if (!container) return;

        if (members.length === 0) {
            container.innerHTML = '<p class="text-sm text-mg-text-muted py-2 px-3">아직 참여자가 없습니다.</p>';
            return;
        }

        // 해당 스레드가 open 상태인지 확인
        var card = document.getElementById('rp-thread-' + rtId);
        var isOpen = card ? card.dataset.status === 'open' : false;
        var ownerMbId = card ? card.dataset.owner : '';

        var html = '';
        members.forEach(function(mem) {
            var name = escapeHtml(mem.ch_name || mem.mb_nick || '');
            var initial = name.charAt(0);
            var avatarHtml = mem.ch_thumb
                ? '<img src="' + CHAR_IMAGE_URL + '/' + escapeHtml(mem.ch_thumb) + '" class="w-7 h-7 rounded-full object-cover flex-shrink-0" alt="">'
                : '<div class="w-7 h-7 rounded-full bg-mg-bg-tertiary flex items-center justify-center flex-shrink-0"><span class="text-xs font-bold text-mg-accent">' + initial + '</span></div>';

            // 메신저 내 댓글 폼: 해당 캐릭터 소유자 OR 원글 작성자
            var showForm = isOpen && (mem.mb_id === CURRENT_MB_ID || CURRENT_MB_ID === ownerMbId);
            var formHtml = showForm ? buildReplyFormHtml(rtId, mem.ch_id) : '';

            html += '<div class="rp-participant" data-rt-id="' + rtId + '" data-ch-id="' + mem.ch_id + '">' +
                '<button type="button" onclick="toggleMessenger(this)" class="w-full flex items-center gap-2 py-2 px-3 rounded-lg hover:bg-mg-bg-tertiary/50 transition-colors text-left">' +
                    avatarHtml +
                    '<span class="text-sm font-medium text-mg-text-primary">' + name + '</span>' +
                    '<span class="text-xs text-mg-text-muted rp-reply-count">(댓글 ' + (parseInt(mem.rm_reply_count) || 0) + '개)</span>' +
                    '<svg class="w-4 h-4 ml-auto text-mg-text-muted rp-toggle-arrow transition-transform duration-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>' +
                '</button>' +
                '<div class="rp-messenger hidden ml-9 mt-1 mb-2 bg-mg-bg-primary rounded-lg border border-mg-bg-tertiary overflow-hidden" data-loaded="false">' +
                    '<div class="rp-messenger-content p-3 max-h-120 overflow-y-auto">' +
                        '<div class="text-center text-mg-text-muted text-xs py-4">불러오는 중...</div>' +
                    '</div>' +
                    formHtml +
                '</div>' +
            '</div>';
        });

        container.innerHTML = html;
    }

    // === 댓글 수정 ===
    window.editReply = function(btn, rrId) {
        var item = btn.closest('.rp-reply-item');
        if (!item) return;
        var contentEl = item.querySelector('.rp-reply-content');
        var actionsEl = item.querySelector('.rp-reply-actions');

        // 현재 텍스트 (HTML → plain text)
        var temp = document.createElement('div');
        temp.innerHTML = contentEl.innerHTML;
        // br → newline
        temp.querySelectorAll('br').forEach(function(br) { br.replaceWith('\n'); });
        var currentText = temp.textContent.trim();

        // 기존 내용을 textarea로 교체
        var origHtml = contentEl.innerHTML;
        contentEl.innerHTML = '<textarea class="w-full bg-mg-bg-primary text-mg-text-primary text-sm rounded-lg px-2 py-1.5 border border-mg-bg-tertiary focus:border-mg-accent focus:outline-none resize-none" rows="3">' + escapeHtml(currentText) + '</textarea>' +
            '<div class="flex gap-2 mt-1">' +
                '<button type="button" class="rp-edit-save text-xs bg-mg-accent hover:bg-mg-accent-hover text-white px-2 py-1 rounded transition-colors">저장</button>' +
                '<button type="button" class="rp-edit-cancel text-xs bg-mg-bg-primary text-mg-text-muted px-2 py-1 rounded hover:bg-mg-bg-tertiary transition-colors">취소</button>' +
            '</div>';
        if (actionsEl) actionsEl.style.display = 'none';

        var textarea = contentEl.querySelector('textarea');
        textarea.focus();
        textarea.setSelectionRange(textarea.value.length, textarea.value.length);

        // 취소
        contentEl.querySelector('.rp-edit-cancel').addEventListener('click', function() {
            contentEl.innerHTML = origHtml;
            if (actionsEl) actionsEl.style.display = '';
        });

        // 저장
        contentEl.querySelector('.rp-edit-save').addEventListener('click', function() {
            var newContent = textarea.value.trim();
            if (!newContent) { textarea.focus(); return; }

            var formData = new FormData();
            formData.append('action', 'edit_reply');
            formData.append('rr_id', rrId);
            formData.append('rr_content', newContent);

            fetch(RP_API_URL, { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        contentEl.innerHTML = data.rr_content_html;
                        if (actionsEl) actionsEl.style.display = '';
                    } else {
                        alert(data.message || '수정 실패');
                    }
                })
                .catch(function() { alert('수정 중 오류가 발생했습니다.'); });
        });
    };

    // === 댓글 삭제 ===
    window.deleteReply = function(btn, rrId) {
        if (!confirm('이 댓글을 삭제하시겠습니까?')) return;

        var item = btn.closest('.rp-reply-item');
        var formData = new FormData();
        formData.append('action', 'delete_reply');
        formData.append('rr_id', rrId);

        var card = item.closest('.card');
        fetch(RP_API_URL, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    item.remove();
                    // 참여자 댓글 수 감소
                    if (data.ch_id && card) {
                        var participant = card.querySelector('.rp-participant[data-ch-id="' + data.ch_id + '"]');
                        if (participant) {
                            var countEl = participant.querySelector('.rp-reply-count');
                            if (countEl) {
                                var num = parseInt(countEl.textContent.replace(/[^0-9]/g, '')) || 0;
                                countEl.textContent = '(댓글 ' + Math.max(0, num - 1) + '개)';
                            }
                        }
                    }
                } else {
                    alert(data.message || '삭제 실패');
                }
            })
            .catch(function() { alert('삭제 중 오류가 발생했습니다.'); });
    };

    // === 판 삭제 (관리자) ===
    window.deleteThread = function(rtId) {
        if (!confirm('이 역극을 완전히 삭제하시겠습니까?\n(모든 댓글과 참여 기록이 삭제됩니다)')) return;

        var formData = new FormData();
        formData.append('action', 'delete_thread');
        formData.append('rt_id', rtId);

        fetch(RP_API_URL, { method: 'POST', body: formData })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    var card = document.getElementById('rp-thread-' + rtId);
                    if (card) card.remove();
                } else {
                    alert(data.message || '삭제 실패');
                }
            })
            .catch(function() { alert('삭제 중 오류가 발생했습니다.'); });
    };

    // === 글쓰기 모달 ===
    window.openWriteModal = function() {
        var modal = document.getElementById('rp-write-modal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    };

    window.closeWriteModal = function() {
        var modal = document.getElementById('rp-write-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    };

    // ESC로 모달 닫기
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeWriteModal();
    });

    // 글쓰기 폼 유효성 검사
    window.frp_write_submit = function(f) {
        if (!f.rt_title.value.trim()) { alert('제목을 입력해주세요.'); f.rt_title.focus(); return false; }
        if (!f.rt_content.value.trim()) { alert('내용을 입력해주세요.'); f.rt_content.focus(); return false; }
        var ch = f.querySelector('input[name="ch_id"]:checked');
        if (!ch) { alert('캐릭터를 선택해주세요.'); return false; }
        return true;
    };

    // 캐릭터 선택기 UI
    document.querySelectorAll('#mg-character-selector .character-option input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('#mg-character-selector .character-badge').forEach(function(badge) {
                badge.classList.remove('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
                badge.classList.add('border-mg-bg-tertiary');
            });
            if (this.checked) {
                var badge = this.parentElement.querySelector('.character-badge');
                badge.classList.remove('border-mg-bg-tertiary');
                badge.classList.add('border-mg-accent', 'ring-2', 'ring-mg-accent/30');
            }
        });
    });

    // === 유틸리티 ===
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    function nl2br(str) {
        if (!str) return '';
        return str.replace(/\n/g, '<br>');
    }

    function timeAgo(datetime) {
        if (!datetime) return '';
        // MySQL datetime을 UTC로 파싱 후 서버 타임존 오프셋 보정
        var isoStr = datetime.replace(' ', 'T') + 'Z';
        var utcMs = new Date(isoStr).getTime();
        if (isNaN(utcMs)) return datetime;
        // 서버 타임존 보정: datetime은 서버 로컬 시간이므로 UTC로 변환
        var time = Math.floor(utcMs / 1000) - SERVER_UTC_OFFSET;
        var now = Math.floor(Date.now() / 1000);
        var diff = now - time;
        if (diff < 0) diff = 0;
        if (diff < 60) return '방금 전';
        if (diff < 3600) return Math.floor(diff / 60) + '분 전';
        if (diff < 86400) return Math.floor(diff / 3600) + '시간 전';
        if (diff < 604800) return Math.floor(diff / 86400) + '일 전';
        // 서버 시간 기준으로 날짜 표시
        var d = new Date((time + SERVER_UTC_OFFSET) * 1000);
        return d.getUTCFullYear() + '.' + String(d.getUTCMonth() + 1).padStart(2, '0') + '.' + String(d.getUTCDate()).padStart(2, '0');
    }

    // === 이미지 첨부 미리보기 ===
    document.addEventListener('change', function(e) {
        if (!e.target.classList.contains('rp-image-input')) return;
        var input = e.target;
        var form = input.closest('form') || input.closest('.border-t');
        var preview = form ? form.querySelector('.rp-image-preview') : null;
        if (!preview) return;

        if (input.files && input.files[0]) {
            var file = input.files[0];
            if (file.size > 5 * 1024 * 1024) {
                alert('이미지 파일 크기는 5MB 이하만 가능합니다.');
                input.value = '';
                preview.innerHTML = '';
                preview.classList.add('hidden');
                return;
            }
            var reader = new FileReader();
            reader.onload = function(ev) {
                preview.innerHTML = '<div class="relative inline-block">' +
                    '<img src="' + ev.target.result + '" class="rounded max-h-20 object-cover">' +
                    '<button type="button" class="rp-image-remove absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white rounded-full flex items-center justify-center text-xs leading-none hover:bg-red-600">&times;</button>' +
                '</div>';
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
            preview.classList.add('hidden');
        }
    });

    // 이미지 미리보기 제거 버튼
    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('rp-image-remove')) return;
        var form = e.target.closest('form') || e.target.closest('.border-t');
        if (!form) return;
        var input = form.querySelector('input[name="rr_image"]');
        var preview = form.querySelector('.rp-image-preview');
        if (input) input.value = '';
        if (preview) { preview.innerHTML = ''; preview.classList.add('hidden'); }
    });

    // === 이미지 원본 보기 모달 ===
    window.openImageModal = function(src) {
        var modal = document.getElementById('rp-image-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'rp-image-modal';
            modal.className = 'fixed inset-0 z-[60] hidden items-center justify-center p-4';
            modal.innerHTML = '<div class="absolute inset-0 bg-black/80 backdrop-blur-sm" onclick="closeImageModal()"></div>' +
                '<button type="button" onclick="closeImageModal()" class="absolute top-4 right-4 z-20 w-9 h-9 bg-mg-bg-secondary/90 text-mg-text-primary rounded-full flex items-center justify-center shadow-lg hover:bg-mg-bg-tertiary transition-colors">' +
                    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>' +
                '</button>' +
                '<div class="relative z-10 max-w-[90vw] max-h-[90vh]">' +
                    '<img id="rp-image-modal-img" src="" class="max-w-[90vw] max-h-[90vh] object-contain rounded-lg shadow-2xl">' +
                '</div>';
            document.body.appendChild(modal);
        }
        document.getElementById('rp-image-modal-img').src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    };

    window.closeImageModal = function() {
        var modal = document.getElementById('rp-image-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeImageModal();
    });

    // URL hash로 스크롤
    if (window.location.hash) {
        var target = document.querySelector(window.location.hash);
        if (target) {
            setTimeout(function() { target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 300);
        }
    }
})();
</script>
