<?php
/**
 * Morgan Edition - Board View Skin (Concierge Result)
 * 의뢰 수행 결과물 전용 게시판 상세
 */

if (!defined('_GNUBOARD_')) exit;

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 글에 연결된 캐릭터 조회
$mg_view_char = mg_get_write_character($bo_table, $wr_id);

// 연결된 의뢰 정보 조회
$_mg_cr = null;
$_mg_cc = null;
$cr_sql = "SELECT cr.*, cc.cc_id, cc.cc_title, cc.cc_type, cc.cc_status, cc.cc_content, cc.mb_id as cc_mb_id
           FROM {$g5['mg_concierge_result_table']} cr
           JOIN {$g5['mg_concierge_table']} cc ON cr.cc_id = cc.cc_id
           WHERE cr.bo_table = '{$bo_table}' AND cr.wr_id = '{$wr_id}'
           LIMIT 1";
$_mg_cr_result = sql_query($cr_sql);
if ($_mg_cr_result) {
    $_mg_cr = sql_fetch_array($_mg_cr_result);
}
if ($_mg_cr) {
    // 의뢰자 이름
    $_mg_cc_owner = sql_fetch("SELECT mb_nick FROM {$g5['member_table']} WHERE mb_id = '{$_mg_cr['cc_mb_id']}'");
    // 수행자(선정된 지원자) 목록
    $_mg_cc_performers = array();
    $perf_sql = "SELECT ca.mb_id, m.mb_nick
                 FROM {$g5['mg_concierge_apply_table']} ca
                 JOIN {$g5['member_table']} m ON ca.mb_id = m.mb_id
                 WHERE ca.cc_id = {$_mg_cr['cc_id']} AND ca.ca_status = 'selected'";
    $perf_result = sql_query($perf_sql);
    while ($perf_row = sql_fetch_array($perf_result)) {
        $_mg_cc_performers[] = $perf_row;
    }
}

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
$status_labels = array('recruiting' => '모집중', 'matched' => '수행중', 'completed' => '완료', 'expired' => '만료', 'cancelled' => '취소', 'force_closed' => '미이행종료');
$status_colors = array('recruiting' => 'text-mg-accent', 'matched' => 'text-blue-400', 'completed' => 'text-mg-success', 'expired' => 'text-mg-text-muted', 'cancelled' => 'text-mg-text-muted', 'force_closed' => 'text-mg-error');

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
?>

<div id="bo_view" class="mg-inner">

    <!-- 연결된 의뢰 카드 -->
    <?php if ($_mg_cr) {
        $cc_type_label = isset($type_labels[$_mg_cr['cc_type']]) ? $type_labels[$_mg_cr['cc_type']] : '';
        $cc_status_label = isset($status_labels[$_mg_cr['cc_status']]) ? $status_labels[$_mg_cr['cc_status']] : $_mg_cr['cc_status'];
        $cc_status_color = isset($status_colors[$_mg_cr['cc_status']]) ? $status_colors[$_mg_cr['cc_status']] : 'text-mg-text-muted';
    ?>
    <div class="card mb-4 border border-mg-accent/20">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-lg bg-mg-accent/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <span class="text-xs text-mg-text-muted">연결된 의뢰</span>
                    <?php if ($cc_type_label) { ?>
                    <span class="px-1.5 py-0.5 text-xs rounded bg-mg-accent/15 text-mg-accent"><?php echo $cc_type_label; ?></span>
                    <?php } ?>
                    <span class="<?php echo $cc_status_color; ?> text-xs font-medium"><?php echo $cc_status_label; ?></span>
                </div>
                <a href="<?php echo G5_BBS_URL; ?>/concierge_view.php?cc_id=<?php echo $_mg_cr['cc_id']; ?>" class="text-mg-text-primary font-medium hover:text-mg-accent transition-colors">
                    <?php echo htmlspecialchars($_mg_cr['cc_title']); ?>
                </a>
                <div class="flex items-center gap-3 mt-1 text-xs text-mg-text-muted">
                    <?php if ($_mg_cc_owner) { ?>
                    <span>의뢰자: <?php echo htmlspecialchars($_mg_cc_owner['mb_nick']); ?></span>
                    <?php } ?>
                    <?php if ($_mg_cc_performers) { ?>
                    <span>수행자: <?php echo htmlspecialchars(implode(', ', array_column($_mg_cc_performers, 'mb_nick'))); ?></span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <!-- 게시글 카드 -->
    <article class="card mb-4">
        <!-- 헤더 -->
        <header class="border-b border-mg-bg-tertiary pb-4 mb-4">
            <h1 class="text-xl font-bold text-mg-text-primary mb-3"><?php echo $view['subject']; ?></h1>

            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <?php if ($mg_view_char && $mg_view_char['ch_id']) { ?>
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
        <?php if (isset($view['file']['count']) && $view['file']['count']) { ?>
        <div class="border-t border-mg-bg-tertiary pt-4 mt-4">
            <h3 class="text-sm font-medium text-mg-text-muted mb-2">첨부파일</h3>
            <ul class="space-y-1">
                <?php for ($i = 0; $i < count($view['file']); $i++) {
                    if (empty($view['file'][$i]['source'])) continue;
                    if ($view['file'][$i]['view']) continue;
                ?>
                <li>
                    <a href="<?php echo $view['file'][$i]['href']; ?>" class="inline-flex items-center gap-2 text-sm text-mg-accent hover:underline py-1">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <?php echo $view['file'][$i]['source']; ?>
                        <span class="text-xs text-mg-text-muted">(<?php echo $view['file'][$i]['size']; ?>)</span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>

        <!-- 인장 -->
        <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($view['mb_id'], 'full'); } ?>

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
        <?php if ($is_good && $_mg_like_limit > 0 && $_mg_like_giver + $_mg_like_receiver > 0) { ?>
        <div id="like-reward-info" class="text-center text-xs text-mg-text-muted mt-1 mb-2">
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
                <a href="<?php echo $write_href; ?>" class="btn btn-primary">결과물 등록</a>
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
