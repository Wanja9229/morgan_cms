<?php
/**
 * Morgan Edition - 로드비(기본형) 게시판 스킨
 *
 * Morgan 다크테마 디자인 (CSS 변수 활용)
 * 인라인 댓글 + 모달 글쓰기
 */

if (!defined('_GNUBOARD_')) exit;
include_once(G5_THEME_PATH.'/skin/board/_lb_common.php');
?>

<style>
/* ── 로드비(기본형) CSS ── */

/* 게시판 컨테이너 */
.lb-default-wrap {
    background: var(--mg-bg-primary);
    padding: 0;
}
.lb-default-board {
    width: 100%;
    margin: 0 auto;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* 게시판 헤더 */
.lb-def-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    background: var(--mg-bg-secondary);
    border-bottom: 2px solid var(--mg-accent);
    border-radius: 6px 6px 0 0;
}
.lb-def-header h1 {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--mg-text-primary);
    margin: 0;
}
.lb-def-header-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 0.8rem;
    color: var(--mg-text-muted);
}
.lb-def-header-meta a {
    color: var(--mg-accent);
    text-decoration: none;
    font-weight: 600;
}
.lb-def-header-meta a:hover { text-decoration: underline; }

/* 게시글 카드 */
.lb-def-card {
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 6px;
    padding: 16px;
    position: relative;
    transition: border-color 0.15s;
}
.lb-def-card:hover {
    border-color: color-mix(in srgb, var(--mg-accent) 40%, transparent);
}

/* 카드 헤더 (제목 + 메타) */
.lb-def-card-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
    gap: 12px;
}
.lb-def-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--mg-text-primary);
    word-break: break-word;
    flex: 1;
}
.lb-def-meta {
    display: flex;
    gap: 10px;
    align-items: center;
    font-size: 0.78rem;
    color: var(--mg-text-muted);
    flex-shrink: 0;
}
.lb-def-author {
    color: var(--mg-accent);
    font-weight: 600;
}

/* 카드 본문 */
.lb-def-body {
    font-size: 0.92rem;
    line-height: 1.65;
    color: var(--mg-text-secondary);
    white-space: pre-line;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}

/* 댓글 목록 */
.lb-def-comments {
    margin-bottom: 10px;
}
.lb-def-cmt {
    display: flex;
    gap: 8px;
    padding: 6px 0;
    font-size: 0.85rem;
    line-height: 1.5;
    border-bottom: 1px solid color-mix(in srgb, var(--mg-bg-tertiary) 60%, transparent);
}
.lb-def-cmt:last-child { border-bottom: none; }
.lb-def-cmt-author {
    color: var(--mg-accent);
    font-weight: 600;
    flex-shrink: 0;
}
.lb-def-cmt-text {
    color: var(--mg-text-secondary);
    word-break: break-word;
}
.lb-def-cmt-time {
    color: var(--mg-text-muted);
    font-size: 0.72rem;
    flex-shrink: 0;
    margin-left: auto;
}

/* 댓글 입력 */
.lb-def-cmt-form {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}
.lb-def-cmt-input {
    flex: 1;
    background: var(--mg-bg-tertiary);
    border: 1px solid transparent;
    border-radius: 4px;
    color: var(--mg-text-primary);
    font-size: 0.85rem;
    padding: 8px 12px;
    outline: none;
    transition: border-color 0.15s;
}
.lb-def-cmt-input:focus {
    border-color: var(--mg-accent);
}
.lb-def-cmt-input::placeholder {
    color: var(--mg-text-muted);
}
.lb-def-cmt-btn {
    background: var(--mg-accent);
    color: #000;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s;
    flex-shrink: 0;
}
.lb-def-cmt-btn:hover {
    background: var(--mg-accent-hover);
}
.lb-def-cmt-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* 글쓰기 버튼 */
.lb-def-write-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--mg-accent);
    color: #000;
    border: none;
    border-radius: 6px;
    padding: 10px 24px;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.15s, transform 0.1s;
    text-decoration: none;
}
.lb-def-write-btn:hover {
    background: var(--mg-accent-hover);
    transform: translateY(-1px);
}
.lb-def-write-area {
    text-align: center;
    margin-top: 8px;
}

/* 관리자 체크박스 */
.lb-def-chk {
    position: absolute;
    top: 8px;
    right: 8px;
    opacity: 0.4;
    cursor: pointer;
}
.lb-def-chk:hover { opacity: 1; }

/* 관리자 액션 */
.lb-def-admin-actions {
    display: flex;
    gap: 6px;
    justify-content: center;
    margin-top: 12px;
}
.lb-def-admin-btn {
    background: var(--mg-bg-tertiary);
    color: var(--mg-text-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 4px;
    padding: 6px 14px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.15s;
}
.lb-def-admin-btn:hover {
    background: var(--mg-bg-secondary);
    border-color: var(--mg-accent);
    color: var(--mg-text-primary);
}

/* 빈 상태 */
.lb-def-empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--mg-text-muted);
}
.lb-def-empty-icon {
    font-size: 2.5rem;
    margin-bottom: 12px;
    opacity: 0.4;
}
.lb-def-empty h3 {
    font-size: 1rem;
    color: var(--mg-text-secondary);
    margin: 0;
}

/* 페이지네이션 */
.lb-def-pagination {
    padding: 12px 16px;
    background: var(--mg-bg-secondary);
    border-radius: 0 0 6px 6px;
    border-top: 1px solid var(--mg-bg-tertiary);
}

/* ── 모달 ── */
.lb-modal-overlay {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
    z-index: 50;
}
.lb-modal-overlay.hidden, .lb-def-modal.hidden { display: none !important; }

.lb-def-modal {
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto;
    z-index: 51;
    background: var(--mg-bg-secondary);
    border: 1px solid var(--mg-bg-tertiary);
    border-radius: 8px;
    box-shadow: 0 16px 48px rgba(0,0,0,0.4);
    animation: lb-def-modal-in 0.2s ease-out;
}
@keyframes lb-def-modal-in {
    from { opacity: 0; transform: translate(-50%, -48%) scale(0.96); }
    to   { opacity: 1; transform: translate(-50%, -50%) scale(1); }
}

.lb-def-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 20px;
    border-bottom: 1px solid var(--mg-bg-tertiary);
}
.lb-def-modal-header h3 {
    font-size: 1rem;
    font-weight: 700;
    color: var(--mg-text-primary);
    margin: 0;
}
.lb-def-modal-close {
    background: none; border: none;
    color: var(--mg-text-muted);
    font-size: 1.2rem; cursor: pointer;
    padding: 4px; border-radius: 4px;
    transition: background 0.15s, color 0.15s;
    line-height: 1;
}
.lb-def-modal-close:hover {
    background: var(--mg-bg-tertiary);
    color: var(--mg-text-primary);
}

.lb-def-modal-body { padding: 20px; }
.lb-def-modal-body label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--mg-text-muted);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.lb-def-modal-body input[type="text"],
.lb-def-modal-body textarea {
    width: 100%;
    background: var(--mg-bg-tertiary);
    border: 1px solid transparent;
    border-radius: 4px;
    color: var(--mg-text-primary);
    font-size: 0.9rem;
    padding: 10px 12px;
    outline: none;
    box-sizing: border-box;
    transition: border-color 0.15s;
    margin-bottom: 16px;
}
.lb-def-modal-body input[type="text"]:focus,
.lb-def-modal-body textarea:focus {
    border-color: var(--mg-accent);
}
.lb-def-modal-body textarea {
    height: 180px;
    resize: vertical;
    line-height: 1.6;
    font-family: inherit;
}

.lb-def-modal-footer {
    padding: 14px 20px;
    border-top: 1px solid var(--mg-bg-tertiary);
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}
.lb-def-modal-footer .btn-cancel {
    background: var(--mg-bg-tertiary);
    color: var(--mg-text-secondary);
    border: none; border-radius: 4px;
    padding: 8px 18px; font-size: 0.85rem;
    cursor: pointer; transition: all 0.15s;
}
.lb-def-modal-footer .btn-cancel:hover {
    background: var(--mg-bg-primary);
    color: var(--mg-text-primary);
}
.lb-def-modal-footer .btn-submit {
    background: var(--mg-accent);
    color: #000; border: none; border-radius: 4px;
    padding: 8px 22px; font-size: 0.85rem; font-weight: 700;
    cursor: pointer; transition: background 0.15s;
}
.lb-def-modal-footer .btn-submit:hover {
    background: var(--mg-accent-hover);
}

/* 모바일 */
@media screen and (max-width: 768px) {
    .lb-default-board { padding: 8px; gap: 8px; }
    .lb-def-card { padding: 12px; }
    .lb-def-card-head { flex-direction: column; gap: 4px; }
    .lb-def-meta { flex-wrap: wrap; }
    .lb-def-cmt-form { flex-direction: column; }
    .lb-def-cmt-btn { width: 100%; }
    .lb-def-cmt { flex-wrap: wrap; }
    .lb-def-cmt-time { margin-left: 0; width: 100%; margin-top: 2px; }
    .lb-def-modal { width: 95%; }
}
</style>

<div id="bo_list" class="mg-inner lb-default-wrap">

    <!-- 게시판 헤더 -->
    <div class="lb-def-header">
        <h1><?php echo $board['bo_subject'] ?? $bo_table; ?></h1>
        <div class="lb-def-header-meta">
            <?php if ($admin_href) { ?>
            <a href="<?php echo $admin_href; ?>">관리</a>
            <?php } ?>
            <span><?php echo number_format($total_count); ?>건</span>
        </div>
    </div>

    <!-- 게시글 목록 -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <div class="lb-default-board">
            <?php if (count($list) > 0) { ?>
                <?php foreach ($list as $i => $row) {
                    $comments = isset($comments_map[$row['wr_id']]) ? $comments_map[$row['wr_id']] : array();
                ?>
                <div class="lb-def-card">
                    <?php if ($is_checkbox) { ?>
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="lb-def-chk">
                    <?php } ?>

                    <div class="lb-def-card-head">
                        <span class="lb-def-title"><?php echo $row['subject'] ?? htmlspecialchars($row['wr_subject'] ?? ''); ?></span>
                        <div class="lb-def-meta">
                            <span class="lb-def-author"><?php echo $row['name'] ?? htmlspecialchars($row['wr_name'] ?? ''); ?></span>
                            <span><?php echo date('Y.m.d H:i', strtotime($row['wr_datetime'])); ?></span>
                        </div>
                    </div>

                    <div class="lb-def-body"><?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?></div>

                    <?php if (count($comments) > 0) { ?>
                    <div class="lb-def-comments">
                        <?php foreach ($comments as $cmt) { ?>
                        <div class="lb-def-cmt">
                            <span class="lb-def-cmt-author"><?php echo htmlspecialchars($cmt['wr_name']); ?></span>
                            <span class="lb-def-cmt-text"><?php echo htmlspecialchars($cmt['wr_content']); ?></span>
                            <span class="lb-def-cmt-time"><?php echo date('m.d H:i', strtotime($cmt['wr_datetime'])); ?></span>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if ($is_member || $board['bo_comment_level'] <= 1) { ?>
                    <div class="lb-def-cmt-form">
                        <input type="text" class="lb-def-cmt-input lb-cmt-input" placeholder="댓글 입력..." maxlength="1000"
                               onkeydown="if(event.key==='Enter'){lbSubmitComment(this.nextElementSibling,<?php echo $row['wr_id']; ?>);return false;}">
                        <button type="button" class="lb-def-cmt-btn" onclick="lbSubmitComment(this, <?php echo $row['wr_id']; ?>)">등록</button>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            <?php } else { ?>
                <div class="lb-def-empty">
                    <div class="lb-def-empty-icon">&#128203;</div>
                    <h3>아직 작성된 글이 없습니다</h3>
                </div>
            <?php } ?>

            <!-- 글쓰기 버튼 -->
            <?php if ($write_href) { ?>
            <div class="lb-def-write-area">
                <button type="button" class="lb-def-write-btn" onclick="lbOpenWrite()">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="8" y1="3" x2="8" y2="13"/><line x1="3" y1="8" x2="13" y2="8"/></svg>
                    글쓰기
                </button>
            </div>
            <?php } ?>

            <?php if ($is_checkbox) { ?>
            <div class="lb-def-admin-actions">
                <button type="submit" name="btn_submit" value="선택삭제" class="lb-def-admin-btn">삭제</button>
                <button type="submit" name="btn_submit" value="선택복사" class="lb-def-admin-btn">복사</button>
                <button type="submit" name="btn_submit" value="선택이동" class="lb-def-admin-btn">이동</button>
            </div>
            <?php } ?>
        </div>
    </form>

    <?php if ($total_page > 1) { ?>
    <div class="lb-def-pagination">
        <nav class="flex items-center justify-center gap-1"><?php echo $write_pages; ?></nav>
    </div>
    <?php } ?>

    <!-- 글쓰기 모달 -->
    <?php if ($write_href) { ?>
    <div id="lb_modal_overlay" class="lb-modal-overlay hidden" onclick="if(document._mgMdTarget===this)lbCloseWrite()"></div>
    <div id="lb_write_modal" class="lb-def-modal hidden">
        <div class="lb-def-modal-header">
            <h3>새 글 작성</h3>
            <button type="button" class="lb-def-modal-close" onclick="lbCloseWrite()">&times;</button>
        </div>
        <form id="lb_write_form" action="<?php echo G5_BBS_URL; ?>/write_update.php" method="post" onsubmit="return lbSubmitPost(this);" autocomplete="off">
            <input type="hidden" name="w" value="">
            <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
            <input type="hidden" name="wr_id" value="0">
            <input type="hidden" name="sca" value="<?php echo $sca; ?>">
            <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
            <input type="hidden" name="stx" value="<?php echo $stx; ?>">
            <input type="hidden" name="spt" value="<?php echo $spt; ?>">
            <input type="hidden" name="page" value="<?php echo $page; ?>">
            <input type="hidden" name="token" value="">
            <input type="hidden" name="html" value="html1">

            <div class="lb-def-modal-body">
                <?php include(G5_THEME_PATH.'/skin/board/_lb_modal_inner.php'); ?>
            </div>
            <div class="lb-def-modal-footer">
                <button type="button" class="btn-cancel" onclick="lbCloseWrite()">취소</button>
                <button type="submit" class="btn-submit">등록</button>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<?php include(G5_THEME_PATH.'/skin/board/_lb_modal_assets.php'); ?>

<script>
// 기본형 댓글 등록
function lbSubmitComment(btn, parentId) {
    var wrap = btn.closest('.lb-def-cmt-form');
    var input = wrap.querySelector('.lb-cmt-input');
    var content = input.value.trim();
    if (!content) { input.focus(); return; }

    var fd = new FormData();
    fd.append('action', 'comment');
    fd.append('bo_table', _lb_bo_table);
    fd.append('wr_parent', parentId);
    fd.append('content', content);

    btn.disabled = true;
    fetch(_lb_api_url, { method: 'POST', body: fd })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            btn.disabled = false;
            if (d.error) { alert(d.error); return; }
            var card = btn.closest('.lb-def-card');
            var comments = card.querySelector('.lb-def-comments');
            if (!comments) {
                comments = document.createElement('div');
                comments.className = 'lb-def-comments';
                card.insertBefore(comments, wrap);
            }
            var cmt = document.createElement('div');
            cmt.className = 'lb-def-cmt';
            cmt.innerHTML = '<span class="lb-def-cmt-author">' + d.comment.wr_name + '</span>'
                + '<span class="lb-def-cmt-text">' + d.comment.wr_content + '</span>'
                + '<span class="lb-def-cmt-time">방금</span>';
            comments.appendChild(cmt);
            input.value = '';
        })
        .catch(function() { btn.disabled = false; alert('네트워크 오류'); });
}
</script>
