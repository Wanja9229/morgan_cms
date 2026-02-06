<?php
/**
 * Morgan Edition - Board View Skin
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 글에 연결된 캐릭터 조회
$mg_view_char = mg_get_write_character($bo_table, $wr_id);
?>

<div id="bo_view" class="max-w-4xl mx-auto">

    <!-- 게시글 카드 -->
    <article class="card mb-4">
        <!-- 헤더 -->
        <header class="border-b border-mg-bg-tertiary pb-4 mb-4">
            <?php if ($view['ca_name']) { ?>
            <span class="text-sm text-mg-accent mb-2 inline-block"><?php echo $view['ca_name']; ?></span>
            <?php } ?>
            <h1 class="text-xl font-bold text-mg-text-primary mb-3"><?php echo $view['subject']; ?></h1>

            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <!-- 작성자/캐릭터 -->
                    <?php if ($mg_view_char && $mg_view_char['ch_id']) { ?>
                    <!-- 캐릭터로 작성된 글 -->
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $mg_view_char['ch_id']; ?>" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <?php if ($mg_view_char['ch_thumb']) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$mg_view_char['ch_thumb']; ?>" alt="" class="w-10 h-10 rounded-full object-cover border-2 border-mg-accent">
                        <?php } else { ?>
                        <div class="w-10 h-10 rounded-full bg-mg-accent/20 flex items-center justify-center text-mg-accent font-bold border-2 border-mg-accent">
                            <?php echo mb_substr($mg_view_char['ch_name'], 0, 1); ?>
                        </div>
                        <?php } ?>
                        <div>
                            <span class="text-mg-text-primary font-medium"><?php echo htmlspecialchars($mg_view_char['ch_name']); ?></span>
                            <span class="text-xs text-mg-text-muted block">@<?php echo $view['name']; ?></span>
                        </div>
                    </a>
                    <?php } else { ?>
                    <!-- 일반 글 -->
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold">
                            <?php echo mb_substr(strip_tags($view['name']), 0, 1); ?>
                        </div>
                        <span class="text-mg-text-primary"><?php echo $view['name']; ?></span>
                    </div>
                    <?php } ?>
                </div>
                <div class="flex items-center gap-3 text-sm text-mg-text-muted">
                    <span><?php echo $view['datetime']; ?></span>
                    <span>조회 <?php echo number_format($view['wr_hit']); ?></span>
                </div>
            </div>
        </header>

        <!-- 본문 -->
        <div class="prose prose-invert max-w-none text-mg-text-secondary leading-relaxed mb-6">
            <?php echo mg_render_emoticons($view['content']); ?>
        </div>

        <!-- 첨부파일 -->
        <?php if ($view['file']) { ?>
        <div class="border-t border-mg-bg-tertiary pt-4 mt-4">
            <h3 class="text-sm font-medium text-mg-text-muted mb-2">첨부파일</h3>
            <?php echo $view['file']; ?>
        </div>
        <?php } ?>

        <!-- 추천/비추천 -->
        <?php if ($is_good || $is_nogood) { ?>
        <div class="flex items-center justify-center gap-4 border-t border-mg-bg-tertiary pt-4 mt-4">
            <?php if ($is_good) { ?>
            <button type="button" onclick="good_choice(document.getElementById('good_form'), 'good');" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-mg-bg-tertiary hover:bg-mg-success/20 transition-colors">
                <svg class="w-5 h-5 text-mg-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                </svg>
                <span class="text-mg-success font-medium"><?php echo $view['wr_good']; ?></span>
            </button>
            <?php } ?>
            <?php if ($is_nogood) { ?>
            <button type="button" onclick="good_choice(document.getElementById('good_form'), 'nogood');" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-mg-bg-tertiary hover:bg-mg-error/20 transition-colors">
                <svg class="w-5 h-5 text-mg-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/>
                </svg>
                <span class="text-mg-error font-medium"><?php echo $view['wr_nogood']; ?></span>
            </button>
            <?php } ?>
        </div>
        <form id="good_form">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <input type="hidden" name="wr_id" value="<?php echo $wr_id; ?>">
        </form>
        <?php } ?>

        <!-- 버튼 -->
        <div class="flex items-center justify-between flex-wrap gap-2 border-t border-mg-bg-tertiary pt-4 mt-4">
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
    <?php echo $view['comment']; ?>

</div>

<script>
function good_choice(f, good) {
    var href = '<?php echo G5_BBS_URL; ?>/good.php?bo_table=' + f.bo_table.value + '&wr_id=' + f.wr_id.value + '&good=' + good;
    fetch(href)
        .then(res => res.text())
        .then(data => {
            if (data) alert(data);
            else location.reload();
        });
}
</script>
