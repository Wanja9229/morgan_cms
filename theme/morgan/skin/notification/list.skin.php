<?php
if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner">
    <!-- 헤더 -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-mg-text-primary">알림</h2>
            <?php if ($unread_count > 0) { ?>
            <p class="text-sm text-mg-text-muted mt-0.5">읽지 않은 알림 <span class="text-mg-accent font-medium"><?php echo $unread_count; ?></span>개</p>
            <?php } ?>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($unread_count > 0) { ?>
            <button type="button" onclick="notiPageAction('read_all')" class="btn btn-secondary text-sm">전체 읽음</button>
            <?php } ?>
            <button type="button" onclick="notiPageAction('delete_read')" class="btn btn-secondary text-sm">읽은 알림 삭제</button>
        </div>
    </div>

    <!-- 필터 탭 -->
    <div class="flex gap-2 mb-4">
        <a href="?filter=all" class="px-3 py-1.5 rounded text-sm <?php echo $filter === 'all' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">전체</a>
        <a href="?filter=unread" class="px-3 py-1.5 rounded text-sm <?php echo $filter === 'unread' ? 'bg-mg-accent text-white' : 'bg-mg-bg-tertiary text-mg-text-secondary hover:text-mg-text-primary'; ?>">미읽음</a>
    </div>

    <!-- 알림 목록 -->
    <?php if (!empty($notifications['items'])) { ?>
    <div class="card divide-y divide-mg-bg-tertiary">
        <?php foreach ($notifications['items'] as $noti) {
            $type_label = isset($noti_type_labels[$noti['noti_type']]) ? $noti_type_labels[$noti['noti_type']] : $noti['noti_type'];
            $is_unread = !(int)$noti['noti_read'];
            $url = $noti['noti_url'] ?: 'javascript:void(0)';

            // 타입별 아이콘 색상
            $icon_colors = array(
                'comment' => 'bg-blue-500/20 text-blue-400',
                'reply' => 'bg-cyan-500/20 text-cyan-400',
                'like' => 'bg-rose-500/20 text-rose-400',
                'character_approved' => 'bg-emerald-500/20 text-emerald-400',
                'character_rejected' => 'bg-red-500/20 text-red-400',
                'character_unapproved' => 'bg-orange-500/20 text-orange-400',
                'character_deleted' => 'bg-red-500/20 text-red-400',
                'gift_received' => 'bg-amber-500/20 text-amber-400',
                'gift_accepted' => 'bg-emerald-500/20 text-emerald-400',
                'gift_rejected' => 'bg-red-500/20 text-red-400',
                'emoticon' => 'bg-violet-500/20 text-violet-400',
                'rp_reply' => 'bg-indigo-500/20 text-indigo-400',
                'system' => 'bg-mg-bg-tertiary text-mg-text-muted',
            );
            $icon_class = isset($icon_colors[$noti['noti_type']]) ? $icon_colors[$noti['noti_type']] : 'bg-mg-bg-tertiary text-mg-text-muted';

            // 상대 시간
            $time_diff = time() - strtotime($noti['noti_datetime']);
            if ($time_diff < 60) $time_ago = '방금 전';
            elseif ($time_diff < 3600) $time_ago = floor($time_diff / 60) . '분 전';
            elseif ($time_diff < 86400) $time_ago = floor($time_diff / 3600) . '시간 전';
            elseif ($time_diff < 604800) $time_ago = floor($time_diff / 86400) . '일 전';
            else $time_ago = date('Y-m-d', strtotime($noti['noti_datetime']));
        ?>
        <div class="flex items-start gap-3 p-4 <?php echo $is_unread ? 'bg-mg-accent/5' : ''; ?>" id="noti_<?php echo $noti['noti_id']; ?>">
            <!-- 아이콘 -->
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 <?php echo $icon_class; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>

            <!-- 내용 -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <span class="text-xs px-1.5 py-0.5 rounded bg-mg-bg-tertiary text-mg-text-muted"><?php echo htmlspecialchars($type_label); ?></span>
                    <?php if ($is_unread) { ?>
                    <span class="w-2 h-2 rounded-full bg-mg-accent"></span>
                    <?php } ?>
                </div>
                <?php if ($noti['noti_url']) { ?>
                <a href="<?php echo htmlspecialchars($url); ?>" class="text-sm text-mg-text-primary hover:text-mg-accent transition-colors" onclick="notiMarkRead(<?php echo $noti['noti_id']; ?>)">
                    <?php echo htmlspecialchars($noti['noti_title']); ?>
                </a>
                <?php } else { ?>
                <p class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($noti['noti_title']); ?></p>
                <?php } ?>
                <?php if ($noti['noti_content']) { ?>
                <p class="text-xs text-mg-text-muted mt-0.5"><?php echo htmlspecialchars($noti['noti_content']); ?></p>
                <?php } ?>
                <span class="text-xs text-mg-text-muted mt-1 block"><?php echo $time_ago; ?></span>
            </div>

            <!-- 삭제 버튼 -->
            <button type="button" onclick="notiDelete(<?php echo $noti['noti_id']; ?>)" class="text-mg-text-muted hover:text-mg-error transition-colors p-1 flex-shrink-0" title="삭제">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($notifications['total_page'] > 1) { ?>
    <div class="flex justify-center gap-1 mt-6">
        <?php
        $qs = 'filter=' . urlencode($filter);
        $start_p = max(1, $page - 2);
        $end_p = min($notifications['total_page'], $page + 2);
        if ($page > 1) echo '<a href="?'.$qs.'&page='.($page-1).'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm">&lsaquo;</a>';
        for ($i = $start_p; $i <= $end_p; $i++) {
            if ($i == $page) echo '<span class="px-3 py-1.5 rounded bg-mg-accent text-white text-sm">'.$i.'</span>';
            else echo '<a href="?'.$qs.'&page='.$i.'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm hover:text-mg-text-primary">'.$i.'</a>';
        }
        if ($page < $notifications['total_page']) echo '<a href="?'.$qs.'&page='.($page+1).'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm">&rsaquo;</a>';
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <div class="card text-center py-16">
        <svg class="w-16 h-16 mx-auto mb-4 text-mg-text-muted/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <p class="text-mg-text-muted text-lg mb-1"><?php echo $filter === 'unread' ? '읽지 않은 알림이 없습니다.' : '알림이 없습니다.'; ?></p>
    </div>
    <?php } ?>
</div>

<script>
var mgBbs = document.querySelector('meta[name="mg-bbs-url"]');
var bbsUrl = mgBbs ? mgBbs.content : '/bbs';

function notiMarkRead(notiId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', bbsUrl + '/notification_api.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send('action=read&noti_id=' + notiId);
}

function notiDelete(notiId) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', bbsUrl + '/notification_api.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        var el = document.getElementById('noti_' + notiId);
        if (el) {
            el.style.transition = 'opacity 0.2s';
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 200);
        }
    };
    xhr.send('action=delete&noti_id=' + notiId);
}

function notiPageAction(action) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', bbsUrl + '/notification_api.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        location.reload();
    };
    xhr.send('action=' + action);
}
</script>
