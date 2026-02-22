<?php
/**
 * Morgan Edition - Postit Board View Skin
 *
 * 포스트잇 스타일 게시글 상세 보기
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 익명 게시판이면 목록으로 리다이렉트
if (($board['bo_1'] ?? '') === 'anonymous') {
    goto_url(get_pretty_url($bo_table));
    exit;
}

// 좋아요 보상 잔여 횟수
$_mg_like_daily = array('count' => 0, 'targets' => array());
$_mg_like_limit = (int)mg_config('like_daily_limit', 5);
$_mg_like_giver = (int)mg_config('like_giver_point', 10);
$_mg_like_receiver = (int)mg_config('like_receiver_point', 30);
$_mg_like_remaining = $_mg_like_limit;
if ($is_member && function_exists('mg_like_get_daily') && $_mg_like_limit > 0) {
    $_mg_like_daily = mg_like_get_daily($member['mb_id']);
    $_mg_like_remaining = max(0, $_mg_like_limit - $_mg_like_daily['count']);
}

// 포스트잇 배경색/악센트색 (list 스킨과 동일)
$postit_colors = array(
    'bg-amber-900/30',
    'bg-rose-900/30',
    'bg-blue-900/30',
    'bg-emerald-900/30',
    'bg-violet-900/30',
    'bg-cyan-900/30',
    'bg-orange-900/30',
);
$postit_accents = array(
    'bg-amber-500',
    'bg-rose-500',
    'bg-blue-500',
    'bg-emerald-500',
    'bg-violet-500',
    'bg-cyan-500',
    'bg-orange-500',
);
$color_index = $wr_id % 7;
$postit_bg = $postit_colors[$color_index];
$postit_accent = $postit_accents[$color_index];
?>

<div id="bo_view" class="mg-inner">

    <!-- 포스트잇 카드 -->
    <article class="<?php echo $postit_bg; ?> rounded-lg shadow-lg overflow-hidden mb-4">
        <!-- 상단 악센트 바 -->
        <div class="<?php echo $postit_accent; ?> h-1.5"></div>

        <!-- 헤더 -->
        <header class="border-b border-white/10 p-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-mg-text-primary font-medium"><?php echo $view['name']; ?></span>
                    </div>
                </div>
                <div class="flex items-center gap-3 text-sm text-mg-text-muted">
                    <span><?php echo $view['datetime']; ?></span>
                    <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
                </div>
            </div>
        </header>

        <!-- 본문 -->
        <div class="p-5">
            <div class="prose prose-invert max-w-none text-mg-text-secondary leading-relaxed">
                <?php echo mg_render_emoticons($view['content']); ?>
            </div>
        </div>

        <!-- 첨부파일 -->
        <?php if (isset($view['file']['count']) && $view['file']['count']) { ?>
        <div class="border-t border-white/10 p-4">
            <h3 class="text-sm font-medium text-mg-text-muted mb-2">첨부파일</h3>
            <ul class="space-y-1">
                <?php for ($i = 0; $i < count($view['file']); $i++) {
                    if (empty($view['file'][$i]['source'])) continue;
                    if ($view['file'][$i]['view']) continue;
                ?>
                <li><a href="<?php echo $view['file'][$i]['href']; ?>" class="text-sm text-mg-accent hover:underline"><?php echo $view['file'][$i]['source']; ?> <span class="text-xs text-mg-text-muted">(<?php echo $view['file'][$i]['size']; ?>)</span></a></li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>

        <!-- 인장 (시그니처 카드) -->
        <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($view['mb_id'], 'full'); } ?>

        <!-- 추천/비추천 -->
        <?php if ($is_good || $is_nogood) { ?>
        <div class="flex items-center justify-center gap-4 border-t border-white/10 p-4">
            <?php if ($is_good) { ?>
            <button type="button" onclick="good_choice(document.getElementById('good_form'), 'good');" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 hover:bg-mg-success/20 transition-colors">
                <svg class="w-5 h-5 text-mg-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                </svg>
                <span class="text-mg-success font-medium"><?php echo $view['wr_good']; ?></span>
            </button>
            <?php } ?>
            <?php if ($is_nogood) { ?>
            <button type="button" onclick="good_choice(document.getElementById('good_form'), 'nogood');" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-white/5 hover:bg-mg-error/20 transition-colors">
                <svg class="w-5 h-5 text-mg-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                </svg>
                <span class="text-mg-error font-medium"><?php echo $view['wr_nogood']; ?></span>
            </button>
            <?php } ?>
        </div>
        <?php if ($is_good && $_mg_like_limit > 0 && $_mg_like_giver + $_mg_like_receiver > 0) { ?>
        <div id="like-reward-info" class="text-center text-xs text-mg-text-muted mt-1 mb-2 px-4">
            좋아요: 나 +<?php echo $_mg_like_giver; ?>P, 작성자 +<?php echo $_mg_like_receiver; ?>P
            <span class="mx-1">|</span>
            남은 횟수: <span id="like-remaining" class="<?php echo $_mg_like_remaining <= 0 ? 'text-mg-error' : 'text-mg-accent'; ?>"><?php echo $_mg_like_remaining; ?></span>/<?php echo $_mg_like_limit; ?>
        </div>
        <?php } ?>
        <form id="good_form">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
        </form>
        <?php } ?>

        <!-- 버튼 -->
        <div class="flex items-center justify-between flex-wrap gap-2 border-t border-white/10 p-4">
            <div class="flex gap-2">
                <a href="<?php echo $list_href; ?>" class="btn btn-secondary">목록</a>
            </div>
            <div class="flex gap-2">
                <?php if ($update_href) { ?>
                <a href="<?php echo $update_href; ?>" class="btn btn-secondary">수정</a>
                <?php } ?>
                <?php if ($delete_href) { ?>
                <a href="<?php echo $delete_href; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" class="btn btn-secondary">삭제</a>
                <?php } ?>
                <?php if ($write_href) { ?>
                <a href="<?php echo $write_href; ?>" class="btn btn-primary">글쓰기</a>
                <?php } ?>
            </div>
        </div>
    </article>

    <!-- 이전글/다음글 -->
    <?php if ($prev_href || $next_href) { ?>
    <div class="card mb-4">
        <?php if ($prev_href) { ?>
        <a href="<?php echo $prev_href; ?>" class="flex items-center gap-2 p-3 hover:bg-mg-bg-tertiary/30 transition-colors <?php echo $next_href ? 'border-b border-mg-bg-tertiary' : ''; ?>">
            <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            <span class="text-sm text-mg-text-muted">이전글</span>
            <span class="text-sm text-mg-text-secondary flex-1 truncate"><?php echo $prev['wr_subject']; ?></span>
        </a>
        <?php } ?>
        <?php if ($next_href) { ?>
        <a href="<?php echo $next_href; ?>" class="flex items-center gap-2 p-3 hover:bg-mg-bg-tertiary/30 transition-colors">
            <svg class="w-4 h-4 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <span class="text-sm text-mg-text-muted">다음글</span>
            <span class="text-sm text-mg-text-secondary flex-1 truncate"><?php echo $next['wr_subject']; ?></span>
        </a>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 댓글 -->
    <?php include_once(G5_BBS_PATH.'/view_comment.php'); ?>

</div>

<script>
function good_choice(f, good) {
    var fd = new FormData();
    fd.append('bo_table', f.bo_table.value);
    fd.append('wr_id', f.wr_id.value);
    fd.append('good', good);
    fd.append('js', 'on');

    fetch('<?php echo G5_BBS_URL; ?>/good.php', { method: 'POST', body: fd })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.error) {
                alert(data.error);
                return;
            }
            var btn = f.parentNode.querySelector(good === 'good' ? '.text-mg-success.font-medium' : '.text-mg-error.font-medium');
            if (btn && data.count) btn.textContent = data.count;

            if (data.like_reward && data.like_reward.success) {
                var r = data.like_reward;
                var remainEl = document.getElementById('like-remaining');
                if (remainEl) {
                    remainEl.textContent = r.remaining;
                    remainEl.className = r.remaining <= 0 ? 'text-mg-error' : 'text-mg-accent';
                }
            }
        })
        .catch(function() { location.reload(); });
}
</script>
