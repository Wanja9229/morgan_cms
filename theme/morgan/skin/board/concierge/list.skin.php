<?php
/**
 * Morgan Edition - Board List Skin (Concierge Result)
 * 의뢰 수행 결과물 전용 게시판 목록
 */

if (!defined('_GNUBOARD_')) exit;

include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 의뢰 연결 정보 일괄 조회
$_mg_concierge_map = array();
if (count($list) > 0) {
    $wr_ids = array();
    foreach ($list as $row) {
        $wr_ids[] = (int)$row['wr_id'];
    }
    if ($wr_ids) {
        $wr_ids_str = implode(',', $wr_ids);
        $cr_sql = "SELECT cr.wr_id, cr.cc_id, cc.cc_title, cc.cc_type, cc.cc_status
                   FROM {$g5['mg_concierge_result_table']} cr
                   JOIN {$g5['mg_concierge_table']} cc ON cr.cc_id = cc.cc_id
                   WHERE cr.bo_table = '{$bo_table}' AND cr.wr_id IN ({$wr_ids_str})";
        $cr_result = sql_query($cr_sql);
        while ($cr_row = sql_fetch_array($cr_result)) {
            $_mg_concierge_map[(int)$cr_row['wr_id']] = $cr_row;
        }
    }
}

// 캐릭터 프리로드
$mg_list_chars = array();
if (count($list) > 0) {
    $wr_ids = array_column($list, 'wr_id');
    $wr_ids_str = implode(',', array_map('intval', $wr_ids));
    $ch_sql = "SELECT wc.wr_id, c.ch_id, c.ch_name, c.ch_thumb
               FROM {$g5['mg_write_character_table']} wc
               JOIN {$g5['mg_character_table']} c ON wc.ch_id = c.ch_id
               WHERE wc.bo_table = '{$bo_table}' AND wc.wr_id IN ({$wr_ids_str})";
    $ch_result = sql_query($ch_sql);
    while ($ch_row = sql_fetch_array($ch_result)) {
        $mg_list_chars[(int)$ch_row['wr_id']] = $ch_row;
    }
}

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
?>

<div id="bo_list" class="mg-inner">
    <!-- 헤더 -->
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $board['bo_subject']; ?></h1>
            <p class="text-sm text-mg-text-muted mt-0.5">의뢰 수행 결과물 <?php echo number_format($total_count); ?>건</p>
        </div>
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $bo_table; ?>&w=w" class="px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors text-sm">
            결과물 등록
        </a>
    </div>

    <!-- 목록 -->
    <div class="card">
        <div class="divide-y divide-mg-bg-tertiary">
            <?php foreach ($list as $i => $row) {
                $is_notice = isset($row['is_notice']) && $row['is_notice'];
                $ch = isset($mg_list_chars[(int)$row['wr_id']]) ? $mg_list_chars[(int)$row['wr_id']] : null;
                $cc = isset($_mg_concierge_map[(int)$row['wr_id']]) ? $_mg_concierge_map[(int)$row['wr_id']] : null;
                $cc_type_label = ($cc && isset($type_labels[$cc['cc_type']])) ? $type_labels[$cc['cc_type']] : '';
            ?>
            <div class="flex items-center gap-3 py-3 px-2 hover:bg-mg-bg-primary transition-colors">
                <!-- 캐릭터 아바타 -->
                <?php if ($ch && $ch['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.htmlspecialchars($ch['ch_thumb']); ?>" class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                <?php } else { ?>
                <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-xs flex-shrink-0">?</div>
                <?php } ?>

                <!-- 내용 -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <?php if ($cc_type_label) { ?>
                        <span class="px-1.5 py-0.5 text-xs rounded bg-mg-accent/15 text-mg-accent"><?php echo $cc_type_label; ?></span>
                        <?php } ?>
                        <a href="<?php echo get_pretty_url($bo_table, $row['wr_id']); ?>" class="text-sm font-medium text-mg-text-primary hover:text-mg-accent truncate transition-colors">
                            <?php echo $row['wr_subject']; ?>
                        </a>
                        <?php if ($row['wr_comment']) { ?>
                        <span class="text-xs text-mg-accent">[<?php echo $row['wr_comment']; ?>]</span>
                        <?php } ?>
                    </div>
                    <div class="flex items-center gap-2 mt-0.5 text-xs text-mg-text-muted">
                        <?php if ($ch) { ?>
                        <span><?php echo htmlspecialchars($ch['ch_name']); ?></span>
                        <?php } else { ?>
                        <span><?php echo $row['wr_name']; ?></span>
                        <?php } ?>
                        <span><?php echo substr($row['wr_datetime'], 0, 10); ?></span>
                        <span>조회 <?php echo $row['wr_hit']; ?></span>
                        <?php if ($cc) { ?>
                        <span class="text-mg-text-secondary">← <?php echo htmlspecialchars(mb_substr($cc['cc_title'], 0, 20)); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>

            <?php if (empty($list)) { ?>
            <div class="py-12 text-center text-mg-text-muted">
                <p>등록된 결과물이 없습니다.</p>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 페이지네이션 -->
    <?php if ($write_pages) { ?>
    <div class="flex justify-center mt-4">
        <?php echo $write_pages; ?>
    </div>
    <?php } ?>

    <!-- 검색 -->
    <div class="card mt-4">
        <form method="get" class="flex items-center gap-2">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <select name="sfl" class="px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm">
                <option value="wr_subject" <?php echo $sfl === 'wr_subject' ? 'selected' : ''; ?>>제목</option>
                <option value="wr_content" <?php echo $sfl === 'wr_content' ? 'selected' : ''; ?>>내용</option>
                <option value="wr_name" <?php echo $sfl === 'wr_name' ? 'selected' : ''; ?>>작성자</option>
            </select>
            <input type="text" name="stx" value="<?php echo htmlspecialchars($stx); ?>" class="flex-1 px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm" placeholder="검색어">
            <button type="submit" class="px-4 py-2 bg-mg-bg-tertiary text-mg-text-primary rounded-lg text-sm hover:bg-mg-accent/20 transition-colors">검색</button>
        </form>
    </div>
</div>
