<?php
/**
 * Morgan Edition - Board View Skin (Renewed)
 *
 * view.html 디자인 기반 리뉴얼.
 * 색상은 모건 빌더 디자인 설정(mg_config)에서 로드된 CSS 변수를 사용.
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 글에 연결된 캐릭터 조회
$mg_view_char = mg_get_write_character($bo_table, $wr_id);

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

// 댓글 수
$_mg_comment_cnt = isset($view['wr_comment']) ? (int)$view['wr_comment'] : 0;
?>

<style>
/* 뷰 페이지 본문 리셋 — 에디터 산출물과 호환 */
.mg-view-body { line-height: 1.85; word-break: break-word; }
.mg-view-body p { margin-bottom: 1rem; }
.mg-view-body img { border-radius: 0.75rem; margin: 1.5rem 0; max-width: 100%; height: auto; }
.mg-view-body a { color: var(--mg-accent); text-decoration: underline; text-underline-offset: 2px; }
.mg-view-body a:hover { opacity: 0.8; }
.mg-view-body blockquote {
    border-left: 3px solid var(--mg-accent);
    padding: 0.75rem 1rem;
    margin: 1rem 0;
    background: var(--mg-bg-primary);
    border-radius: 0 0.5rem 0.5rem 0;
    color: var(--mg-text-secondary);
}
.mg-view-body h1,.mg-view-body h2,.mg-view-body h3,.mg-view-body h4 {
    color: var(--mg-text-primary);
    margin-top: 1.5rem; margin-bottom: 0.75rem; font-weight: 700;
}
.mg-view-body ul, .mg-view-body ol { padding-left: 1.5rem; margin-bottom: 1rem; }
.mg-view-body li { margin-bottom: 0.25rem; }
.mg-view-body table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
.mg-view-body th, .mg-view-body td {
    border: 1px solid var(--mg-bg-tertiary);
    padding: 0.5rem 0.75rem; text-align: left;
}
.mg-view-body th { background: var(--mg-bg-primary); font-weight: 600; }
.mg-view-body pre, .mg-view-body code {
    background: var(--mg-bg-primary);
    border-radius: 0.375rem;
    font-size: 0.875rem;
}
.mg-view-body pre { padding: 1rem; overflow-x: auto; margin: 1rem 0; }
.mg-view-body code { padding: 0.125rem 0.375rem; }
.mg-view-body hr {
    border: none; border-top: 1px solid var(--mg-bg-tertiary);
    margin: 1.5rem 0;
}
</style>

<div id="bo_view" class="mg-inner">

    <!-- 게시글 카드 -->
    <article class="rounded-2xl overflow-hidden mb-5" style="background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);">

        <div class="px-5 py-8 sm:px-8">

            <!-- 헤더 -->
            <header style="border-bottom:1px solid var(--mg-bg-tertiary);" class="pb-6 mb-7">
                <?php if ($view['ca_name']) { ?>
                <div class="mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background:color-mix(in srgb, var(--mg-accent) 15%, transparent);color:var(--mg-accent);">
                        <?php echo $view['ca_name']; ?>
                    </span>
                </div>
                <?php } ?>

                <h1 class="text-2xl sm:text-3xl font-bold mb-5" style="color:var(--mg-text-primary);letter-spacing:-0.02em;">
                    <?php echo $view['subject']; ?>
                </h1>

                <div class="flex flex-wrap items-center text-sm gap-y-3" style="color:var(--mg-text-muted);">
                    <!-- 작성자 -->
                    <div class="flex items-center mr-5">
                        <?php if ($mg_view_char && $mg_view_char['ch_id']) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $mg_view_char['ch_id']; ?>" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                            <?php if ($mg_view_char['ch_thumb']) { ?>
                            <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$mg_view_char['ch_thumb']; ?>" alt="" class="w-9 h-9 rounded-full object-cover" style="border:2px solid var(--mg-accent);">
                            <?php } else { ?>
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold" style="background:color-mix(in srgb, var(--mg-accent) 20%, transparent);color:var(--mg-accent);border:2px solid var(--mg-accent);">
                                <?php echo mb_substr($mg_view_char['ch_name'], 0, 1); ?>
                            </div>
                            <?php } ?>
                            <div>
                                <span class="font-medium" style="color:var(--mg-text-primary);"><?php echo htmlspecialchars($mg_view_char['ch_name']); ?></span>
                                <span class="text-xs block" style="color:var(--mg-text-muted);">@<?php echo mg_render_nickname($view['mb_id'], $view['wr_name'], $mg_view_char['ch_id']); ?></span>
                            </div>
                        </a>
                        <?php } else { ?>
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm" style="background:var(--mg-bg-tertiary);color:var(--mg-text-muted);border:1px solid var(--mg-bg-tertiary);">
                                <?php echo mb_substr(strip_tags($view['name']), 0, 1); ?>
                            </div>
                            <span class="font-medium" style="color:var(--mg-text-primary);"><?php echo $view['mb_id'] ? mg_render_nickname($view['mb_id'], $view['wr_name']) : htmlspecialchars($view['wr_name']); ?></span>
                        </div>
                        <?php } ?>
                    </div>

                    <!-- 메타 정보 -->
                    <div class="flex items-center gap-3">
                        <span><?php echo $view['datetime']; ?></span>
                        <span class="w-1 h-1 rounded-full" style="background:var(--mg-text-muted);opacity:0.5;"></span>
                        <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
                        <?php if ($_mg_comment_cnt > 0) { ?>
                        <span class="w-1 h-1 rounded-full" style="background:var(--mg-text-muted);opacity:0.5;"></span>
                        <span>댓글 <?php echo $_mg_comment_cnt; ?></span>
                        <?php } ?>
                    </div>
                </div>
            </header>

            <!-- 첨부파일 -->
            <?php
            $has_files = false;
            if (isset($view['file']['count']) && $view['file']['count']) {
                for ($fi = 0; $fi < count($view['file']); $fi++) {
                    if (!empty($view['file'][$fi]['source']) && !$view['file'][$fi]['view']) {
                        $has_files = true;
                        break;
                    }
                }
            }
            if ($has_files) { ?>
            <div class="rounded-xl p-4 mb-7 flex flex-col gap-2" style="background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);">
                <?php for ($i = 0; $i < count($view['file']); $i++) {
                    if (empty($view['file'][$i]['source'])) continue;
                    if ($view['file'][$i]['view']) continue;
                ?>
                <a href="<?php echo $view['file'][$i]['href']; ?>" class="flex items-center text-sm font-medium transition-colors" style="color:var(--mg-text-secondary);" onmouseover="this.style.color='var(--mg-accent)'" onmouseout="this.style.color='var(--mg-text-secondary)'">
                    <svg class="w-5 h-5 mr-2 flex-shrink-0" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <?php echo $view['file'][$i]['source']; ?>
                    <span class="ml-1 font-normal" style="color:var(--mg-text-muted);">(<?php echo $view['file'][$i]['size']; ?>)</span>
                </a>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 본문 -->
            <div class="mg-view-body text-base mb-10" style="color:var(--mg-text-secondary);">
                <?php echo mg_render_emoticons($view['content']); ?>
            </div>

            <!-- 인장 (시그니처 카드) -->
            <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($view['mb_id'], 'full'); } ?>

            <!-- 추천/비추천 -->
            <?php if ($is_good || $is_nogood) { ?>
            <div class="flex items-center justify-center gap-4 pt-6 mt-6" style="border-top:1px solid var(--mg-bg-tertiary);">
                <?php if ($is_good) { ?>
                <button type="button" onclick="good_choice(document.getElementById('good_form'), 'good');" class="flex items-center gap-2 px-5 py-2.5 rounded-xl transition-all" style="background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);" onmouseover="this.style.borderColor='var(--mg-success)'" onmouseout="this.style.borderColor='var(--mg-bg-tertiary)'">
                    <svg class="w-5 h-5" style="color:var(--mg-success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                    </svg>
                    <span class="font-medium" style="color:var(--mg-success);"><?php echo $view['wr_good']; ?></span>
                </button>
                <?php } ?>
                <?php if ($is_nogood) { ?>
                <button type="button" onclick="good_choice(document.getElementById('good_form'), 'nogood');" class="flex items-center gap-2 px-5 py-2.5 rounded-xl transition-all" style="background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);" onmouseover="this.style.borderColor='var(--mg-error)'" onmouseout="this.style.borderColor='var(--mg-bg-tertiary)'">
                    <svg class="w-5 h-5" style="color:var(--mg-error);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                    </svg>
                    <span class="font-medium" style="color:var(--mg-error);"><?php echo $view['wr_nogood']; ?></span>
                </button>
                <?php } ?>
            </div>
            <?php if ($is_good && $_mg_like_limit > 0 && $_mg_like_giver + $_mg_like_receiver > 0) { ?>
            <div id="like-reward-info" class="text-center text-xs mt-2 mb-2" style="color:var(--mg-text-muted);">
                좋아요: 나 +<?php echo $_mg_like_giver; ?>P, 작성자 +<?php echo $_mg_like_receiver; ?>P
                <span class="mx-1">|</span>
                남은 횟수: <span id="like-remaining" style="color:<?php echo $_mg_like_remaining <= 0 ? 'var(--mg-error)' : 'var(--mg-accent)'; ?>;"><?php echo $_mg_like_remaining; ?></span>/<?php echo $_mg_like_limit; ?>
            </div>
            <?php } ?>
            <form id="good_form">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
            </form>
            <?php } ?>

            <!-- 액션 버튼 -->
            <div class="flex items-center justify-between flex-wrap gap-3 pt-6 mt-6" style="border-top:1px solid var(--mg-bg-tertiary);">
                <div>
                    <a href="<?php echo $list_href; ?>" class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all" style="background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);color:var(--mg-text-secondary);" onmouseover="this.style.borderColor='var(--mg-accent)';this.style.color='var(--mg-text-primary)'" onmouseout="this.style.borderColor='var(--mg-bg-tertiary)';this.style.color='var(--mg-text-secondary)'">
                        <svg class="w-4 h-4 mr-2" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                        목록으로
                    </a>
                </div>
                <div class="flex gap-2">
                    <?php if ($update_href) { ?>
                    <a href="<?php echo $update_href; ?>" class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all" style="background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);color:var(--mg-text-secondary);" onmouseover="this.style.borderColor='var(--mg-accent)';this.style.color='var(--mg-text-primary)'" onmouseout="this.style.borderColor='var(--mg-bg-tertiary)';this.style.color='var(--mg-text-secondary)'">
                        수정
                    </a>
                    <?php } ?>
                    <?php if ($delete_href) { ?>
                    <a href="<?php echo $delete_href; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all" style="background:color-mix(in srgb, var(--mg-error) 10%, var(--mg-bg-primary));border:1px solid color-mix(in srgb, var(--mg-error) 30%, transparent);color:var(--mg-error);">
                        삭제
                    </a>
                    <?php } ?>
                    <?php if ($write_href) { ?>
                    <a href="<?php echo $write_href; ?>" class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-all" style="background:var(--mg-button);color:var(--mg-button-text);box-shadow:0 1px 3px rgba(0,0,0,0.2);" onmouseover="this.style.background='var(--mg-button-hover)'" onmouseout="this.style.background='var(--mg-button)'">
                        <?php echo isset($_mg_concierge_write_label) ? $_mg_concierge_write_label : '글쓰기'; ?>
                    </a>
                    <?php } ?>
                </div>
            </div>

        </div><!-- /px-5 py-8 -->
    </article>

    <!-- 이전글/다음글 -->
    <?php if ($prev_href || $next_href) { ?>
    <div class="rounded-2xl overflow-hidden mb-5" style="background:var(--mg-bg-secondary);border:1px solid var(--mg-bg-tertiary);">
        <?php if ($prev_href) { ?>
        <a href="<?php echo $prev_href; ?>" class="flex items-center gap-3 px-5 py-3.5 transition-colors" style="<?php echo $next_href ? 'border-bottom:1px solid var(--mg-bg-tertiary);' : ''; ?>" onmouseover="this.style.background='var(--mg-bg-primary)'" onmouseout="this.style.background='transparent'">
            <svg class="w-4 h-4 flex-shrink-0" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
            </svg>
            <span class="text-xs font-medium" style="color:var(--mg-text-muted);min-width:3rem;">이전글</span>
            <span class="text-sm flex-1 truncate" style="color:var(--mg-text-secondary);"><?php echo $prev['wr_subject']; ?></span>
        </a>
        <?php } ?>
        <?php if ($next_href) { ?>
        <a href="<?php echo $next_href; ?>" class="flex items-center gap-3 px-5 py-3.5 transition-colors" onmouseover="this.style.background='var(--mg-bg-primary)'" onmouseout="this.style.background='transparent'">
            <svg class="w-4 h-4 flex-shrink-0" style="color:var(--mg-text-muted);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
            <span class="text-xs font-medium" style="color:var(--mg-text-muted);min-width:3rem;">다음글</span>
            <span class="text-sm flex-1 truncate" style="color:var(--mg-text-secondary);"><?php echo $next['wr_subject']; ?></span>
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
            var btn = f.parentNode.querySelector(good === 'good' ? '[style*="mg-success"] + .font-medium, span[style*="mg-success"]' : 'span[style*="mg-error"]');
            // 더 안정적인 카운트 업데이트
            var btns = f.parentNode.parentNode.querySelectorAll('button');
            btns.forEach(function(b) {
                var span = b.querySelector('span.font-medium');
                if (!span) return;
                if (good === 'good' && b.querySelector('[style*="mg-success"]') && data.count) span.textContent = data.count;
                if (good === 'nogood' && b.querySelector('[style*="mg-error"]') && data.count) span.textContent = data.count;
            });

            if (data.like_reward && data.like_reward.success) {
                var r = data.like_reward;
                var remainEl = document.getElementById('like-remaining');
                if (remainEl) {
                    remainEl.textContent = r.remaining;
                    remainEl.style.color = r.remaining <= 0 ? 'var(--mg-error)' : 'var(--mg-accent)';
                }
            }
        })
        .catch(function() { location.reload(); });
}
</script>
