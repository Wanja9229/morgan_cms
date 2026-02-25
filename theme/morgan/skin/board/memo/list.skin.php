<?php
/**
 * Morgan Edition - Memo Board List Skin (Accordion)
 *
 * 아코디언 스타일 메모 목록 - 제목 클릭 시 내용이 인라인으로 펼쳐짐
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
                <p class="text-sm text-mg-text-muted">총 <?php echo number_format($total_count); ?>개의 메모</p>
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
                    메모 쓰기
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

    <!-- 메모 목록 (아코디언) -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <div class="space-y-2">
            <?php if (count($list) > 0) { ?>
                <?php foreach ($list as $i => $row) { ?>
                <div class="card p-0 overflow-hidden <?php echo $row['is_notice'] ? 'border border-mg-accent/30' : ''; ?>">
                    <!-- 아코디언 헤더 (클릭 영역) -->
                    <div class="flex items-center gap-3 p-4 cursor-pointer hover:bg-mg-bg-tertiary/30 transition-colors select-none" onclick="toggleMemo(<?php echo $row['wr_id']; ?>)">
                        <?php if ($is_checkbox) { ?>
                        <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="flex-shrink-0" onclick="event.stopPropagation();">
                        <?php } ?>

                        <!-- 펼침/접힘 아이콘 -->
                        <svg id="memo_icon_<?php echo $row['wr_id']; ?>" class="w-4 h-4 text-mg-text-muted flex-shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>

                        <div class="flex-1 min-w-0">
                            <!-- 제목 줄 -->
                            <div class="flex items-center gap-2">
                                <?php if ($row['is_notice']) { ?>
                                <span class="badge badge-accent flex-shrink-0">공지</span>
                                <?php } ?>
                                <span class="text-mg-text-primary font-medium truncate">
                                    <?php echo $row['subject']; ?>
                                </span>
                                <?php if ($row['wr_option'] && strpos($row['wr_option'], 'secret') !== false) { ?>
                                <svg class="w-4 h-4 text-mg-warning flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <?php } ?>
                                <?php if ($row['comment_cnt']) { ?>
                                <span class="text-xs text-mg-accent flex-shrink-0">[<?php echo $row['comment_cnt']; ?>]</span>
                                <?php } ?>
                            </div>

                            <!-- 메타 정보 -->
                            <div class="flex items-center gap-3 text-xs text-mg-text-muted mt-1">
                                <?php
                                $row_char = isset($mg_list_chars[$row['wr_id']]) ? $mg_list_chars[$row['wr_id']] : null;
                                if ($row_char) {
                                ?>
                                <span class="flex items-center gap-1">
                                    <?php if ($row_char['ch_thumb']) { ?>
                                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$row_char['ch_thumb']; ?>" alt="" class="w-4 h-4 rounded-full object-cover">
                                    <?php } ?>
                                    <span class="text-mg-text-secondary"><?php echo htmlspecialchars($row_char['ch_name']); ?></span>
                                </span>
                                <span class="text-mg-text-muted">@<?php echo mg_render_nickname($row['mb_id'], $row['wr_name'], $row_char['ch_id']); ?></span>
                                <?php } else { ?>
                                <span><?php echo $row['mb_id'] ? mg_render_nickname($row['mb_id'], $row['wr_name']) : htmlspecialchars($row['wr_name']); ?></span>
                                <?php } ?>
                                <span><?php echo $row['datetime2']; ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- 아코디언 내용 (숨김 상태) -->
                    <div id="memo_content_<?php echo $row['wr_id']; ?>" class="hidden">
                        <div class="border-t border-mg-bg-tertiary px-4 py-4 bg-mg-bg-primary/50">
                            <?php if ($row['wr_option'] && strpos($row['wr_option'], 'secret') !== false) { ?>
                                <?php
                                // 비밀글 권한 체크: 본인 또는 관리자만 볼 수 있음
                                $is_owner = ($is_member && $member['mb_id'] === $row['mb_id']);
                                $is_admin_user = ($is_admin === 'super' || $is_admin === 'group' || $is_admin === 'board');
                                if ($is_owner || $is_admin_user) {
                                ?>
                                <div class="prose prose-invert max-w-none text-mg-text-secondary text-sm leading-relaxed">
                                    <?php echo $row['content'] ?? conv_content($row['wr_content'] ?? '', 1); ?>
                                </div>
                                <?php } else { ?>
                                <div class="flex items-center gap-2 text-mg-warning text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    비밀글입니다.
                                </div>
                                <?php } ?>
                            <?php } else { ?>
                            <div class="prose prose-invert max-w-none text-mg-text-secondary text-sm leading-relaxed">
                                <?php echo $row['content'] ?? conv_content($row['wr_content'] ?? '', 1); ?>
                            </div>
                            <?php } ?>

                            <!-- 펼침 영역 하단 버튼 -->
                            <div class="flex items-center justify-between mt-4 pt-3 border-t border-mg-bg-tertiary">
                                <div class="flex items-center gap-2 text-xs text-mg-text-muted">
                                    <span>조회 <?php echo $row['wr_hit']; ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo $row['href']; ?>" class="btn btn-secondary text-xs px-3 py-1">
                                        상세보기
                                    </a>
                                    <?php if (!empty($row['reply_href'])) { ?>
                                    <a href="<?php echo $row['reply_href']; ?>" class="btn btn-secondary text-xs px-3 py-1">
                                        답변
                                    </a>
                                    <?php } ?>
                                    <?php if (!empty($row['edit_href'])) { ?>
                                    <a href="<?php echo $row['edit_href']; ?>" class="btn btn-secondary text-xs px-3 py-1">
                                        수정
                                    </a>
                                    <?php } ?>
                                    <?php if (!empty($row['delete_href'])) { ?>
                                    <a href="<?php echo $row['delete_href']; ?>" onclick="return confirm('정말 삭제하시겠습니까?');" class="btn btn-secondary text-xs px-3 py-1 text-red-400 hover:text-red-300">
                                        삭제
                                    </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            <?php } else { ?>
            <div class="card">
                <div class="p-8 text-center text-mg-text-muted">
                    등록된 메모가 없습니다.
                </div>
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

<script>
function toggleMemo(id) {
    var el = document.getElementById('memo_content_' + id);
    var icon = document.getElementById('memo_icon_' + id);

    el.classList.toggle('hidden');

    // 아이콘 회전 (펼침: 90도, 접힘: 0도)
    if (el.classList.contains('hidden')) {
        icon.style.transform = 'rotate(0deg)';
    } else {
        icon.style.transform = 'rotate(90deg)';
    }
}
</script>
