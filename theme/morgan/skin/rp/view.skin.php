<?php
/**
 * Morgan Edition - RP (역극) View Skin
 * Messenger / Chat-style UI
 *
 * Variables:
 *   $thread         - thread data (with mb_nick, ch_name, ch_thumb)
 *   $replies        - array of replies (each with mb_nick, ch_name, ch_thumb)
 *   $members        - array of participants
 *   $my_characters  - current user's usable characters
 *   $join_check     - can join check result ['can_join' => bool, 'message' => string]
 *   $is_owner       - boolean (current user is the thread owner)
 *   $member         - current member info
 *   $is_member      - boolean
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$is_open   = ($thread['rt_status'] == 'open');
$is_closed = ($thread['rt_status'] == 'closed');

/**
 * 상대 시간 표시 헬퍼
 */
function rp_view_time_ago($datetime) {
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
        return date('Y.m.d H:i', $time);
    }
}

/**
 * 메시지 정렬 판별 - 판장이면 오른쪽
 */
$owner_mb_id = $thread['mb_id'];
?>

<div id="rp_view" class="max-w-4xl mx-auto flex flex-col" style="min-height: calc(100vh - 200px);">

    <!-- 스레드 헤더 -->
    <div class="card mb-0 rounded-b-none border-b border-mg-bg-tertiary">
        <!-- 상단: 제목 + 상태 + 판장 -->
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-lg font-bold text-mg-text-primary"><?php echo htmlspecialchars($thread['rt_title']); ?></h1>
                    <?php if ($is_open) { ?>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-500/20 text-green-400">open</span>
                    <?php } else { ?>
                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-500/20 text-red-400">closed</span>
                    <?php } ?>
                </div>
                <div class="flex items-center gap-2 mt-1 text-sm text-mg-text-muted">
                    <span>판장:</span>
                    <?php if ($thread['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$thread['ch_thumb']; ?>" alt="" class="w-5 h-5 rounded-full object-cover">
                    <?php } ?>
                    <span class="text-mg-text-secondary font-medium"><?php echo htmlspecialchars($thread['ch_name'] ?: $thread['mb_nick']); ?></span>
                    <span class="text-mg-text-muted">(@<?php echo htmlspecialchars($thread['mb_nick']); ?>)</span>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-shrink-0">
                <?php if ($is_owner && $is_open) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/rp_close.php?rt_id=<?php echo $thread['rt_id']; ?>"
                   onclick="return confirm('이 역극을 완결 처리하시겠습니까?');"
                   class="btn btn-secondary text-sm">완결</a>
                <?php } ?>
                <a href="<?php echo G5_BBS_URL; ?>/rp_list.php" class="btn btn-secondary text-sm">목록</a>
            </div>
        </div>

        <!-- 참여자 바 -->
        <div class="flex items-center gap-2 pt-3 border-t border-mg-bg-tertiary">
            <span class="text-xs text-mg-text-muted flex-shrink-0">참여자 (<?php echo count($members); ?>명):</span>
            <div class="flex items-center gap-1 overflow-x-auto">
                <?php foreach ($members as $mem) { ?>
                <div class="flex-shrink-0 relative group" title="<?php echo htmlspecialchars($mem['ch_name'] ?: $mem['mb_nick']); ?> (@<?php echo htmlspecialchars($mem['mb_nick']); ?>)">
                    <?php if ($mem['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$mem['ch_thumb']; ?>" alt=""
                         class="w-7 h-7 rounded-full object-cover border-2 <?php echo ($mem['mb_id'] == $owner_mb_id) ? 'border-mg-accent' : 'border-mg-bg-tertiary'; ?>">
                    <?php } else { ?>
                    <div class="w-7 h-7 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-xs font-bold text-mg-accent border-2 <?php echo ($mem['mb_id'] == $owner_mb_id) ? 'border-mg-accent' : 'border-mg-bg-tertiary'; ?>">
                        <?php echo mb_substr($mem['ch_name'] ?: $mem['mb_nick'], 0, 1); ?>
                    </div>
                    <?php } ?>
                    <?php if ($mem['mb_id'] == $owner_mb_id) { ?>
                    <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-mg-accent rounded-full flex items-center justify-center" title="판장">
                        <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </span>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 채팅 메시지 영역 -->
    <div id="rp_messages" class="flex-1 bg-mg-bg-primary border-x border-mg-bg-tertiary overflow-y-auto" style="min-height: 400px;">
        <div class="p-4 space-y-4">

            <!-- 판장의 시작글 (첫 번째 메시지 / 항상 오른쪽) -->
            <div class="flex justify-end">
                <div class="flex items-end gap-2 max-w-[80%]">
                    <!-- 시간 (좌측) -->
                    <span class="text-xs text-mg-text-muted flex-shrink-0 mb-1"><?php echo rp_view_time_ago($thread['rt_datetime']); ?></span>
                    <!-- 메시지 버블 -->
                    <div class="min-w-0">
                        <!-- 캐릭터 이름 (우측 정렬) - 클릭 시 해당 판장의 역극 목록 -->
                        <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?owner=<?php echo urlencode($thread['mb_id']); ?>" class="flex items-center justify-end gap-1.5 mb-1 hover:opacity-80 transition-opacity" title="<?php echo htmlspecialchars($thread['ch_name'] ?: $thread['mb_nick']); ?>님의 역극 보기">
                            <span class="text-xs font-medium text-mg-accent"><?php echo htmlspecialchars($thread['ch_name'] ?: $thread['mb_nick']); ?></span>
                            <?php if ($thread['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$thread['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-mg-accent/30">
                            <?php } else { ?>
                            <div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center text-xs font-bold text-mg-accent">
                                <?php echo mb_substr($thread['ch_name'] ?: $thread['mb_nick'], 0, 1); ?>
                            </div>
                            <?php } ?>
                        </a>
                        <div id="rp_content_thread" class="rp-bubble bg-mg-accent/10 rounded-2xl rounded-tr-sm px-4 py-3 text-mg-text-secondary leading-relaxed cursor-pointer line-clamp-4"
                             onclick="toggleReply('thread')">
                            <?php echo mg_render_emoticons(nl2br(htmlspecialchars($thread['rt_content']))); ?>
                        </div>
                        <?php if ($thread['rt_image']) { ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($thread['rt_image']); ?>" alt="" class="max-w-full rounded-lg max-h-64 object-cover">
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php if (count($replies) > 0) { ?>
            <?php foreach ($replies as $idx => $reply) {
                $is_owner_reply = ($reply['mb_id'] == $owner_mb_id);
            ?>

            <?php if ($is_owner_reply) { ?>
            <!-- 판장 메시지 (오른쪽 정렬) -->
            <div class="flex justify-end">
                <div class="flex items-end gap-2 max-w-[80%]">
                    <span class="text-xs text-mg-text-muted flex-shrink-0 mb-1"><?php echo rp_view_time_ago($reply['rr_datetime']); ?></span>
                    <div class="min-w-0">
                        <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?owner=<?php echo urlencode($reply['mb_id']); ?>" class="flex items-center justify-end gap-1.5 mb-1 hover:opacity-80 transition-opacity" title="<?php echo htmlspecialchars($reply['ch_name'] ?: $reply['mb_nick']); ?>님의 역극 보기">
                            <span class="text-xs font-medium text-mg-accent"><?php echo htmlspecialchars($reply['ch_name'] ?: $reply['mb_nick']); ?></span>
                            <?php if ($reply['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$reply['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-mg-accent/30">
                            <?php } else { ?>
                            <div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center text-xs font-bold text-mg-accent">
                                <?php echo mb_substr($reply['ch_name'] ?: $reply['mb_nick'], 0, 1); ?>
                            </div>
                            <?php } ?>
                        </a>
                        <div id="rp_content_<?php echo $reply['rr_id']; ?>" class="rp-bubble bg-mg-accent/10 rounded-2xl rounded-tr-sm px-4 py-3 text-mg-text-secondary leading-relaxed cursor-pointer line-clamp-4"
                             onclick="toggleReply('<?php echo $reply['rr_id']; ?>')">
                            <?php echo mg_render_emoticons(nl2br(htmlspecialchars($reply['rr_content']))); ?>
                        </div>
                        <?php if ($reply['rr_image']) { ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($reply['rr_image']); ?>" alt="" class="max-w-full rounded-lg max-h-64 object-cover">
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <?php } else { ?>
            <!-- 참여자 메시지 (왼쪽 정렬) -->
            <div class="flex justify-start">
                <div class="flex items-end gap-2 max-w-[80%]">
                    <div class="min-w-0">
                        <!-- 캐릭터 이름 (좌측 정렬) - 클릭 시 해당 회원의 역극 목록 -->
                        <a href="<?php echo G5_BBS_URL; ?>/rp_list.php?owner=<?php echo urlencode($reply['mb_id']); ?>" class="flex items-center gap-1.5 mb-1 hover:opacity-80 transition-opacity" title="<?php echo htmlspecialchars($reply['ch_name'] ?: $reply['mb_nick']); ?>님의 역극 보기">
                            <?php if ($reply['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$reply['ch_thumb']; ?>" alt="" class="w-8 h-8 rounded-full object-cover border border-mg-bg-tertiary">
                            <?php } else { ?>
                            <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-xs font-bold text-mg-text-secondary">
                                <?php echo mb_substr($reply['ch_name'] ?: $reply['mb_nick'], 0, 1); ?>
                            </div>
                            <?php } ?>
                            <span class="text-xs font-medium text-mg-text-primary"><?php echo htmlspecialchars($reply['ch_name'] ?: $reply['mb_nick']); ?></span>
                            <span class="text-xs text-mg-text-muted">@<?php echo htmlspecialchars($reply['mb_nick']); ?></span>
                        </a>
                        <div id="rp_content_<?php echo $reply['rr_id']; ?>" class="rp-bubble bg-mg-bg-tertiary rounded-2xl rounded-tl-sm px-4 py-3 text-mg-text-secondary leading-relaxed cursor-pointer line-clamp-4"
                             onclick="toggleReply('<?php echo $reply['rr_id']; ?>')">
                            <?php echo mg_render_emoticons(nl2br(htmlspecialchars($reply['rr_content']))); ?>
                        </div>
                        <?php if ($reply['rr_image']) { ?>
                        <div class="mt-2">
                            <img src="<?php echo htmlspecialchars($reply['rr_image']); ?>" alt="" class="max-w-full rounded-lg max-h-64 object-cover">
                        </div>
                        <?php } ?>
                    </div>
                    <span class="text-xs text-mg-text-muted flex-shrink-0 mb-1"><?php echo rp_view_time_ago($reply['rr_datetime']); ?></span>
                </div>
            </div>
            <?php } ?>

            <?php } ?>
            <?php } ?>

        </div>
    </div>

    <!-- 하단 입력 바 / 상태 메시지 -->
    <?php if ($is_closed) { ?>
    <!-- 완결 상태 -->
    <div class="card rounded-t-none border-t border-mg-bg-tertiary">
        <div class="flex items-center justify-center gap-2 py-4 text-mg-text-muted">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>이 역극은 완결되었습니다.</span>
        </div>
    </div>

    <?php } elseif (!$is_member) { ?>
    <!-- 비로그인 -->
    <div class="card rounded-t-none border-t border-mg-bg-tertiary">
        <div class="flex items-center justify-center gap-2 py-4 text-mg-text-muted">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span>역극에 참여하려면 <a href="<?php echo G5_BBS_URL; ?>/../bbs/login.php" class="text-mg-accent hover:underline">로그인</a>이 필요합니다.</span>
        </div>
    </div>

    <?php } elseif (!$join_check['can_join'] && empty($join_check['already_joined'])) { ?>
    <!-- 참여 불가 -->
    <div class="card rounded-t-none border-t border-mg-bg-tertiary">
        <div class="flex items-center justify-center gap-2 py-4 text-mg-text-muted">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
            </svg>
            <span><?php echo htmlspecialchars($join_check['message']); ?></span>
        </div>
    </div>

    <?php } elseif (empty($my_characters) || count($my_characters) == 0) { ?>
    <!-- 캐릭터 없음 -->
    <div class="card rounded-t-none border-t border-mg-bg-tertiary">
        <div class="flex items-center justify-center gap-2 py-4 text-mg-text-muted">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <span>사용 가능한 캐릭터가 없습니다. <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="text-mg-accent hover:underline">캐릭터를 등록</a>해주세요.</span>
        </div>
    </div>

    <?php } else { ?>
    <!-- 이음 입력 바 -->
    <div class="card rounded-t-none border-t border-mg-bg-tertiary sticky bottom-0 z-10">
        <form id="rp_reply_form" onsubmit="event.preventDefault(); submitReply();">
            <input type="hidden" name="rt_id" value="<?php echo $thread['rt_id']; ?>">
            <div class="flex items-end gap-3">
                <!-- 캐릭터 선택 -->
                <div class="flex-shrink-0">
                    <label class="text-xs text-mg-text-muted block mb-1">캐릭터</label>
                    <select name="ch_id" id="rp_ch_id" class="input py-2 pr-8 text-sm" style="min-width: 140px;">
                        <?php foreach ($my_characters as $ch) { ?>
                        <option value="<?php echo $ch['ch_id']; ?>">
                            <?php echo htmlspecialchars($ch['ch_name']); ?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- 내용 입력 -->
                <div class="flex-1 relative">
                    <textarea name="rr_content" id="rp_content_input" class="input w-full resize-none text-sm" rows="2"
                              placeholder="이음할 내용을 입력하세요..."
                              onkeydown="if(event.ctrlKey && event.key === 'Enter') submitReply();"></textarea>
                    <?php
                    $picker_id = 'rp_reply';
                    $picker_target = 'rp_content_input';
                    include(G5_THEME_PATH.'/skin/emoticon/picker.skin.php');
                    ?>
                </div>

                <!-- 이음 버튼 -->
                <div class="flex-shrink-0">
                    <button type="submit" id="rp_submit_btn" class="btn btn-primary h-full px-5 py-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        이음
                    </button>
                </div>
            </div>
            <p class="text-xs text-mg-text-muted mt-2">Ctrl + Enter로 빠른 전송</p>
        </form>
    </div>
    <?php } ?>

</div>

<style>
/* line-clamp (4줄 제한) */
.line-clamp-4 {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* 확장된 메시지 (클릭시 line-clamp 해제) */
.rp-bubble {
    word-break: break-word;
    transition: all 0.2s ease;
}

.rp-bubble:not(.line-clamp-4) {
    max-height: none;
}

/* 채팅 영역 스크롤바 커스텀 */
#rp_messages::-webkit-scrollbar {
    width: 6px;
}
#rp_messages::-webkit-scrollbar-track {
    background: transparent;
}
#rp_messages::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
}
#rp_messages::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.2);
}

/* 입력 영역 sticky */
@supports (position: sticky) {
    .sticky {
        position: sticky;
    }
}
</style>

<script>
/**
 * 메시지 펼치기/접기 토글
 */
function toggleReply(id) {
    var el = document.getElementById('rp_content_' + id);
    if (el) {
        el.classList.toggle('line-clamp-4');
    }
}

/**
 * 이음 AJAX 전송
 */
function submitReply() {
    var form = document.getElementById('rp_reply_form');
    var textarea = form.querySelector('textarea');
    var submitBtn = document.getElementById('rp_submit_btn');
    var content = textarea.value.trim();

    if (!content) {
        textarea.focus();
        return;
    }

    // 중복 전송 방지
    submitBtn.disabled = true;
    submitBtn.classList.add('opacity-50');

    var formData = new FormData(form);

    fetch('<?php echo G5_BBS_URL; ?>/rp_reply.php', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            // 새 메시지 DOM에 추가
            appendReply(data.reply);
            textarea.value = '';
            // 메시지 영역 하단으로 스크롤
            scrollToBottom();
        } else {
            alert(data.message || '오류가 발생했습니다.');
        }
    })
    .catch(function(error) {
        console.error('RP reply error:', error);
        alert('전송 중 오류가 발생했습니다.');
    })
    .finally(function() {
        submitBtn.disabled = false;
        submitBtn.classList.remove('opacity-50');
        textarea.focus();
    });
}

/**
 * 새 이음 메시지를 DOM에 추가
 */
function appendReply(reply) {
    var container = document.querySelector('#rp_messages > div');
    if (!container) return;

    var isOwner = (reply.mb_id === '<?php echo addslashes($owner_mb_id); ?>');
    var charName = reply.ch_name || reply.mb_nick || '';
    var charInitial = charName.charAt(0);
    var thumbUrl = reply.ch_thumb ? '<?php echo MG_CHAR_IMAGE_URL; ?>/' + reply.ch_thumb : '';
    var mbNick = reply.mb_nick || '';

    var html = '';

    if (isOwner) {
        // 판장 메시지 (오른쪽)
        html += '<div class="flex justify-end">';
        html += '<div class="flex items-end gap-2 max-w-[80%]">';
        html += '<span class="text-xs text-mg-text-muted flex-shrink-0 mb-1">방금 전</span>';
        html += '<div class="min-w-0">';
        html += '<div class="flex items-center justify-end gap-1.5 mb-1">';
        html += '<span class="text-xs font-medium text-mg-accent">' + escapeHtml(charName) + '</span>';
        if (thumbUrl) {
            html += '<img src="' + escapeHtml(thumbUrl) + '" alt="" class="w-8 h-8 rounded-full object-cover border border-mg-accent/30">';
        } else {
            html += '<div class="w-8 h-8 rounded-full bg-mg-accent/20 flex items-center justify-center text-xs font-bold text-mg-accent">' + escapeHtml(charInitial) + '</div>';
        }
        html += '</div>';
        html += '<div id="rp_content_' + reply.rr_id + '" class="rp-bubble bg-mg-accent/10 rounded-2xl rounded-tr-sm px-4 py-3 text-mg-text-secondary leading-relaxed cursor-pointer line-clamp-4" onclick="toggleReply(\'' + reply.rr_id + '\')">';
        html += nl2br(escapeHtml(reply.rr_content));
        html += '</div>';
        if (reply.rr_image) {
            html += '<div class="mt-2"><img src="' + escapeHtml(reply.rr_image) + '" alt="" class="max-w-full rounded-lg max-h-64 object-cover"></div>';
        }
        html += '</div></div></div>';
    } else {
        // 참여자 메시지 (왼쪽)
        html += '<div class="flex justify-start">';
        html += '<div class="flex items-end gap-2 max-w-[80%]">';
        html += '<div class="min-w-0">';
        html += '<div class="flex items-center gap-1.5 mb-1">';
        if (thumbUrl) {
            html += '<img src="' + escapeHtml(thumbUrl) + '" alt="" class="w-8 h-8 rounded-full object-cover border border-mg-bg-tertiary">';
        } else {
            html += '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-xs font-bold text-mg-text-secondary">' + escapeHtml(charInitial) + '</div>';
        }
        html += '<span class="text-xs font-medium text-mg-text-primary">' + escapeHtml(charName) + '</span>';
        html += '<span class="text-xs text-mg-text-muted">@' + escapeHtml(mbNick) + '</span>';
        html += '</div>';
        html += '<div id="rp_content_' + reply.rr_id + '" class="rp-bubble bg-mg-bg-tertiary rounded-2xl rounded-tl-sm px-4 py-3 text-mg-text-secondary leading-relaxed cursor-pointer line-clamp-4" onclick="toggleReply(\'' + reply.rr_id + '\')">';
        html += nl2br(escapeHtml(reply.rr_content));
        html += '</div>';
        if (reply.rr_image) {
            html += '<div class="mt-2"><img src="' + escapeHtml(reply.rr_image) + '" alt="" class="max-w-full rounded-lg max-h-64 object-cover"></div>';
        }
        html += '</div>';
        html += '<span class="text-xs text-mg-text-muted flex-shrink-0 mb-1">방금 전</span>';
        html += '</div></div>';
    }

    container.insertAdjacentHTML('beforeend', html);
}

/**
 * 메시지 영역 하단으로 스크롤
 */
function scrollToBottom() {
    var el = document.getElementById('rp_messages');
    if (el) {
        el.scrollTop = el.scrollHeight;
    }
}

/**
 * HTML 이스케이프
 */
function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

/**
 * 줄바꿈을 <br>로 변환
 */
function nl2br(str) {
    if (!str) return '';
    return str.replace(/\n/g, '<br>');
}

// 페이지 로드 시 메시지 영역 하단으로 스크롤
document.addEventListener('DOMContentLoaded', function() {
    scrollToBottom();
});
</script>
