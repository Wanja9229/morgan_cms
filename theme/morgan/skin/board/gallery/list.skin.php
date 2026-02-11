<?php
/**
 * Morgan Edition - Gallery Board List Skin
 *
 * 카드 그리드 형태의 갤러리 게시판 스킨
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

<div id="bo_list" class="mg-inner">

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

    <!-- 갤러리 그리드 -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <?php if (count($list) > 0) { ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <?php foreach ($list as $i => $row) {
                // 본문에서 첫 번째 이미지 추출
                $thumb_url = '';
                if (preg_match('/<img[^>]+src=["\']?([^"\'>\s]+)/i', $row['wr_content'], $m)) {
                    $thumb_url = $m[1];
                }

                // 캐릭터 정보
                $row_char = isset($mg_list_chars[$row['wr_id']]) ? $mg_list_chars[$row['wr_id']] : null;
            ?>
            <div class="card p-0 overflow-hidden group hover:ring-1 hover:ring-mg-accent/50 transition-all <?php echo $row['is_notice'] ? 'ring-1 ring-mg-accent/30' : ''; ?>">

                <?php if ($is_checkbox) { ?>
                <div class="absolute top-2 left-2 z-10">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="w-4 h-4 rounded">
                </div>
                <?php } ?>

                <!-- 썸네일 영역 -->
                <a href="<?php echo $row['href']; ?>" class="block relative" style="aspect-ratio: 4/3;">
                    <?php if ($thumb_url) { ?>
                    <img src="<?php echo $thumb_url; ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    <?php } else { ?>
                    <div class="w-full h-full bg-mg-bg-tertiary flex items-center justify-center">
                        <svg class="w-12 h-12 text-mg-text-muted/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <?php } ?>

                    <?php if ($row['is_notice']) { ?>
                    <span class="absolute top-2 right-2 badge badge-accent">공지</span>
                    <?php } ?>

                    <?php if ($row['comment_cnt']) { ?>
                    <span class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-0.5 rounded-full flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <?php echo $row['comment_cnt']; ?>
                    </span>
                    <?php } ?>
                </a>

                <!-- 카드 내용 -->
                <div class="p-3">
                    <!-- 카테고리 -->
                    <?php if ($row['ca_name']) { ?>
                    <span class="text-xs text-mg-accent mb-1 inline-block"><?php echo $row['ca_name']; ?></span>
                    <?php } ?>

                    <!-- 제목 -->
                    <a href="<?php echo $row['href']; ?>" class="block text-sm font-medium text-mg-text-primary hover:text-mg-accent truncate mb-2" title="<?php echo strip_tags($row['subject']); ?>">
                        <?php echo $row['subject']; ?>
                    </a>

                    <!-- 작성자 정보 -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <?php if ($row_char) { ?>
                            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $row_char['ch_id']; ?>" class="flex items-center gap-1.5 hover:text-mg-accent transition-colors min-w-0">
                                <?php if ($row_char['ch_thumb']) { ?>
                                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$row_char['ch_thumb']; ?>" alt="" class="w-5 h-5 rounded-full object-cover flex-shrink-0">
                                <?php } ?>
                                <span class="text-xs text-mg-text-secondary truncate"><?php echo htmlspecialchars($row_char['ch_name']); ?></span>
                            </a>
                            <?php } else { ?>
                            <span class="text-xs text-mg-text-secondary truncate"><?php echo $row['name']; ?></span>
                            <?php } ?>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-mg-text-muted flex-shrink-0">
                            <span><?php echo $row['datetime2']; ?></span>
                            <span class="flex items-center gap-0.5">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <?php echo $row['wr_hit']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="card p-12 text-center text-mg-text-muted">
            <svg class="w-16 h-16 mx-auto mb-4 text-mg-text-muted/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p>등록된 게시글이 없습니다.</p>
        </div>
        <?php } ?>

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
