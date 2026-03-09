<?php
if (!defined("_GNUBOARD_")) exit;
?>

<div class="mg-inner">
    <!-- 헤더 -->
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold text-mg-text-primary">새글</h2>
    </div>

    <!-- 검색 필터 -->
    <div class="card mb-6">
        <form name="fnew" method="get" class="flex flex-wrap items-center gap-3">
            <select name="gr_id" id="gr_id" class="input w-auto min-w-[120px]">
                <option value="">전체 그룹</option>
                <?php
                $sql = "SELECT gr_id, gr_subject FROM {$g5['group_table']} ORDER BY gr_id";
                $result = sql_query($sql);
                while ($row = sql_fetch_array($result)) {
                    $selected = ($gr_id == $row['gr_id']) ? 'selected' : '';
                    echo '<option value="'.$row['gr_id'].'" '.$selected.'>'.$row['gr_subject'].'</option>';
                }
                ?>
            </select>
            <select name="view" id="view" class="input w-auto min-w-[120px]">
                <option value="" <?php echo $view == '' ? 'selected' : ''; ?>>전체</option>
                <option value="w" <?php echo $view == 'w' ? 'selected' : ''; ?>>원글만</option>
                <option value="c" <?php echo $view == 'c' ? 'selected' : ''; ?>>댓글만</option>
            </select>
            <input type="text" name="mb_id" value="<?php echo $mb_id; ?>" class="input flex-1 min-w-[200px]" placeholder="회원 아이디 검색">
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4"></i>
                검색
            </button>
        </form>
    </div>

    <!-- 새글 목록 -->
    <?php if (count($list) > 0) { ?>
    <div class="card divide-y divide-mg-bg-tertiary">
        <?php for ($i = 0; $i < count($list); $i++) {
            $wr_subject = get_text(cut_str($list[$i]['wr_subject'], 80));
            $is_comment = !empty($list[$i]['comment']);
        ?>
        <div class="p-4 hover:bg-mg-bg-tertiary/30 transition-colors">
            <div class="flex items-start gap-3">
                <!-- 댓글 표시 -->
                <?php if ($is_comment) { ?>
                <span class="px-2 py-0.5 text-xs rounded bg-cyan-500/20 text-cyan-400 flex-shrink-0 mt-0.5">댓글</span>
                <?php } ?>

                <div class="flex-1 min-w-0">
                    <!-- 제목 -->
                    <a href="<?php echo $list[$i]['href']; ?>" class="text-mg-text-primary hover:text-mg-accent transition-colors line-clamp-1 block">
                        <?php echo $wr_subject; ?>
                    </a>

                    <!-- 메타 정보 -->
                    <div class="flex items-center gap-2 mt-1.5 text-xs text-mg-text-muted">
                        <a href="./new.php?gr_id=<?php echo $list[$i]['gr_id']; ?>" class="hover:text-mg-text-secondary">
                            <?php echo cut_str($list[$i]['gr_subject'], 15); ?>
                        </a>
                        <span class="text-mg-bg-tertiary">·</span>
                        <a href="<?php echo get_pretty_url($list[$i]['bo_table']); ?>" class="hover:text-mg-text-secondary">
                            <?php echo cut_str($list[$i]['bo_subject'], 15); ?>
                        </a>
                        <span class="text-mg-bg-tertiary">·</span>
                        <span><?php echo $list[$i]['name']; ?></span>
                        <span class="text-mg-bg-tertiary">·</span>
                        <span><?php echo $list[$i]['datetime2']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="flex justify-center gap-1 mt-6">
        <?php
        $qs = "gr_id={$gr_id}&view={$view}&mb_id={$mb_id}";
        $start_p = max(1, $page - 2);
        $end_p = min($total_page, $page + 2);

        if ($page > 1) {
            echo '<a href="?'.$qs.'&page='.($page-1).'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm hover:text-mg-text-primary">&lsaquo;</a>';
        }

        for ($p = $start_p; $p <= $end_p; $p++) {
            if ($p == $page) {
                echo '<span class="px-3 py-1.5 rounded bg-mg-accent text-white text-sm">'.$p.'</span>';
            } else {
                echo '<a href="?'.$qs.'&page='.$p.'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm hover:text-mg-text-primary">'.$p.'</a>';
            }
        }

        if ($page < $total_page) {
            echo '<a href="?'.$qs.'&page='.($page+1).'" class="px-3 py-1.5 rounded bg-mg-bg-tertiary text-mg-text-secondary text-sm hover:text-mg-text-primary">&rsaquo;</a>';
        }
        ?>
    </div>
    <?php } ?>

    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="card text-center py-16">
        <i data-lucide="newspaper" class="w-16 h-16 mx-auto mb-4 text-mg-text-muted/30"></i>
        <p class="text-mg-text-muted text-lg">새글이 없습니다.</p>
    </div>
    <?php } ?>
</div>
