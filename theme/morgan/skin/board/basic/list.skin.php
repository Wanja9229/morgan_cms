<?php
/**
 * Morgan Edition - Board List Skin
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$colspan = 4;
if ($is_checkbox) $colspan++;

// 목록의 글에 연결된 캐릭터 정보 미리 로드
$mg_list_chars = array();
if (count($list) > 0) {
    $wr_ids = array();
    foreach ($list as $row) {
        $wr_ids[] = (int)$row['wr_id'];
    }
    if (count($wr_ids) > 0) {
        global $g5;
        $sql = "SELECT wc.wr_id, c.ch_id, c.ch_name, c.ch_thumb
                FROM {$g5['mg_write_character_table']} wc
                JOIN {$g5['mg_character_table']} c ON wc.ch_id = c.ch_id
                WHERE wc.bo_table = '".sql_real_escape_string($bo_table)."'
                AND wc.wr_id IN (".implode(',', $wr_ids).")";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $mg_list_chars[$row['wr_id']] = $row;
        }
    }
}
?>

<div id="bo_list" class="max-w-6xl mx-auto">

    <!-- 게시판 헤더 -->
    <div class="card mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $board['bo_subject']; ?></h1>
                <p class="text-sm text-mg-text-muted">총 <?php echo number_format($total_count); ?>개의 글</p>
            </div>
            <div class="flex items-center gap-2">
                <?php if ($admin_href) { ?>
                <a href="<?php echo $admin_href; ?>" class="btn btn-ghost" title="관리자">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
                <?php } ?>
                <?php if ($write_href) { ?>
                <a href="<?php echo $write_href; ?>" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    글쓰기
                </a>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 카테고리 -->
    <?php if ($is_category) { ?>
    <div class="mb-4 flex flex-wrap gap-2">
        <?php echo $category_option; ?>
    </div>
    <?php } ?>

    <!-- 게시글 목록 -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <div class="card overflow-hidden">
            <?php if (count($list) > 0) { ?>
            <div class="divide-y divide-mg-bg-tertiary">
                <?php foreach ($list as $i => $row) { ?>
                <div class="p-4 hover:bg-mg-bg-tertiary/30 transition-colors <?php echo $row['is_notice'] ? 'bg-mg-accent/5' : ''; ?>">
                    <div class="flex items-start gap-4">
                        <?php if ($is_checkbox) { ?>
                        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="mt-1">
                        <?php } ?>

                        <div class="flex-1 min-w-0">
                            <!-- 제목 -->
                            <div class="flex items-center gap-2 mb-1">
                                <?php if ($row['is_notice']) { ?>
                                <span class="badge badge-accent">공지</span>
                                <?php } ?>
                                <?php if ($row['ca_name']) { ?>
                                <span class="text-xs text-mg-text-muted">[<?php echo $row['ca_name']; ?>]</span>
                                <?php } ?>
                                <a href="<?php echo $row['href']; ?>" class="text-mg-text-primary hover:text-mg-accent font-medium truncate">
                                    <?php echo $row['subject']; ?>
                                </a>
                                <?php if ($row['comment_cnt']) { ?>
                                <span class="text-xs text-mg-accent">[<?php echo $row['comment_cnt']; ?>]</span>
                                <?php } ?>
                                <?php if ($row['wr_file']) { ?>
                                <svg class="w-4 h-4 text-mg-text-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <?php } ?>
                            </div>

                            <!-- 메타 정보 -->
                            <div class="flex items-center gap-3 text-xs text-mg-text-muted">
                                <?php
                                $row_char = isset($mg_list_chars[$row['wr_id']]) ? $mg_list_chars[$row['wr_id']] : null;
                                if ($row_char) {
                                ?>
                                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $row_char['ch_id']; ?>" class="flex items-center gap-1 hover:text-mg-accent transition-colors">
                                    <?php if ($row_char['ch_thumb']) { ?>
                                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$row_char['ch_thumb']; ?>" alt="" class="w-4 h-4 rounded-full object-cover">
                                    <?php } ?>
                                    <span class="text-mg-text-secondary"><?php echo htmlspecialchars($row_char['ch_name']); ?></span>
                                </a>
                                <span class="text-mg-text-muted">@<?php echo $row['name']; ?></span>
                                <?php } else { ?>
                                <span><?php echo $row['name']; ?></span>
                                <?php } ?>
                                <span><?php echo $row['datetime2']; ?></span>
                                <span>조회 <?php echo $row['wr_hit']; ?></span>
                                <?php if ($is_good) { ?>
                                <span>추천 <?php echo $row['wr_good']; ?></span>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="p-8 text-center text-mg-text-muted">
                등록된 게시글이 없습니다.
            </div>
            <?php } ?>
        </div>

        <!-- 관리자 버튼 -->
        <?php if ($is_checkbox) { ?>
        <div class="mt-4 flex gap-2">
            <button type="submit" name="btn_submit" value="선택삭제" class="btn btn-secondary text-sm">삭제</button>
            <button type="submit" name="btn_submit" value="선택복사" class="btn btn-secondary text-sm">복사</button>
            <button type="submit" name="btn_submit" value="선택이동" class="btn btn-secondary text-sm">이동</button>
        </div>
        <?php } ?>
    </form>

    <!-- 페이지네이션 -->
    <?php if ($total_page > 1) { ?>
    <div class="mt-6 flex justify-center">
        <nav class="flex items-center gap-1">
            <?php echo $write_pages; ?>
        </nav>
    </div>
    <?php } ?>

    <!-- 검색 -->
    <div class="mt-6">
        <form class="card" method="get" action="<?php echo G5_BBS_URL; ?>/board.php">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <div class="flex gap-2">
                <select name="sfl" class="input w-auto">
                    <option value="wr_subject" <?php echo $sfl == 'wr_subject' ? 'selected' : ''; ?>>제목</option>
                    <option value="wr_content" <?php echo $sfl == 'wr_content' ? 'selected' : ''; ?>>내용</option>
                    <option value="wr_subject||wr_content" <?php echo $sfl == 'wr_subject||wr_content' ? 'selected' : ''; ?>>제목+내용</option>
                    <option value="mb_id,1" <?php echo $sfl == 'mb_id,1' ? 'selected' : ''; ?>>회원ID</option>
                    <option value="wr_name,1" <?php echo $sfl == 'wr_name,1' ? 'selected' : ''; ?>>글쓴이</option>
                </select>
                <input type="text" name="stx" value="<?php echo $stx; ?>" class="input flex-1" placeholder="검색어를 입력하세요">
                <button type="submit" class="btn btn-primary">검색</button>
            </div>
        </form>
    </div>
</div>
