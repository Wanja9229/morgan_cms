<?php
/**
 * Morgan Edition - 로드비(터미널) 게시판 스킨
 *
 * 뱀파이어 터미널 콘솔 디자인
 * 인라인 댓글 + 모달 글쓰기
 */

if (!defined('_GNUBOARD_')) exit;
include_once(G5_THEME_PATH.'/skin/board/_lb_common.php');
?>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Courier+Prime&display=swap">

<style>
/* ── 로드비(터미널) CSS ── */
.lb-terminal-wrap { background: #050505; padding: 0; }
.vampire-console {
    font-family: 'Courier Prime', 'Nanum Gothic Coding', monospace;
    color: #ff3333; margin: 0 auto; background: #050505;
    padding: 20px; border: 1px solid #330000;
    box-shadow: 0 0 20px rgba(255, 0, 0, 0.1); width: 100%;
}
.console-log-block { margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px dashed #441111; }
.log-meta {
    font-size: 0.85rem; opacity: 0.9; margin-bottom: 10px;
    display: flex; justify-content: space-between; align-items: center;
    background: rgba(50, 0, 0, 0.2); padding: 5px 10px; border-left: 3px solid #ff3333;
}
.log-meta .timestamp { color: #888; }
.log-meta .user-id { color: #ff5555; font-weight: bold; }
.log-meta .command { color: #fff; margin-left: 10px; font-weight: 700; font-size: 1.1rem; text-shadow: 0 0 5px rgba(255,255,255,0.5); }
.log-meta-sub { display: flex; gap: 10px; align-items: center; }
.log-content {
    font-size: 1rem; line-height: 1.6; color: #ffcccc;
    padding-left: 20px; border-left: 1px solid #550000; margin-bottom: 15px; white-space: pre-line;
}
.log-replies { margin-left: 20px; font-size: 0.9rem; color: #aaa; border-top: 1px dotted #330000; padding-top: 10px; }
.reply-line { margin-bottom: 6px; word-break: break-all; }
.reply-user { color: #cc4444; font-weight: bold; margin-right: 5px; }
.log-input-area {
    margin-top: 10px; display: flex; align-items: center;
    background: rgba(50, 0, 0, 0.3); padding: 8px; border: 1px solid #330000;
}
.log-input-area .prompt { margin-right: 10px; color: #00ff00; font-weight: bold; font-size: 0.9rem; }
.lb-cmt-input {
    background: transparent; border: none; color: #fff; flex-grow: 1;
    font-family: inherit; outline: none; font-size: 0.95rem;
}
.cmd-btn {
    background: #330000; border: 1px solid #ff0000; color: #ff0000;
    cursor: pointer; font-family: inherit; font-size: 0.8rem; padding: 4px 12px; transition: all 0.2s;
}
.cmd-btn:hover { background: #ff0000; color: #000; box-shadow: 0 0 10px #f00; }
.system-msg { border: 1px solid #00ff00; background: rgba(0,255,0,0.05); padding: 10px; }
.system-msg .log-meta { border-left-color: #00ff00; }
.system-msg .user-id, .system-msg .timestamp { color: #00ff00; }
.system-msg .log-content { color: #00ff00; border-left-color: #004400; text-shadow: 0 0 5px rgba(0,255,0,0.5); }
.terminal-input {
    margin-top: 30px; border-top: 1px solid #330000; padding-top: 15px;
    display: flex; align-items: center; font-size: 1.1rem;
}
.terminal-input .prompt { color: #00ff00; margin-right: 10px; }
.terminal-input .cmd-btn { background: transparent; border: none; color: #fff; font-size: 1.1rem; cursor: pointer; text-decoration: underline; padding: 0; }
.terminal-input .cmd-btn:hover { color: #ff3333; text-shadow: 0 0 5px #f00; }
.terminal-input .cursor { display: inline-block; width: 10px; height: 1.2rem; background: #ff3333; margin-left: 5px; animation: v-blink 1s infinite; }
.empty-vampire {
    font-family: 'Courier New', monospace; color: #ff3333;
    background: rgba(20, 0, 0, 0.3); border: 1px dashed #500; padding: 60px 20px; text-align: center;
}
.v-blink { animation: v-blink 1s infinite; font-weight: bold; }
@keyframes v-blink { 50% { opacity: 0; } }

/* 모달 */
.lb-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(3px); z-index: 50; }
.lb-modal-overlay.hidden, .lb-modal.hidden { display: none !important; }
.lb-write-modal {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; z-index: 51;
    background: #050505; border: 2px solid #a00;
    box-shadow: 0 0 30px rgba(180,0,0,0.3); font-family: 'Courier New', monospace;
    animation: terminal-open 0.3s ease-out;
}
@keyframes terminal-open { from { opacity: 0; transform: translate(-50%, -45%) scale(0.9); } to { opacity: 1; transform: translate(-50%, -50%) scale(1); } }
.vamp-header { background: #300; color: #f55; padding: 12px 20px; border-bottom: 1px solid #a00; display: flex; justify-content: space-between; align-items: center; font-weight: bold; letter-spacing: 1px; }
.vamp-header .btn-close { background: none; border: none; color: #f00; cursor: pointer; font-weight: bold; font-size: 1.2rem; }
.vamp-body { padding: 20px; color: #f00; }
.vamp-body input, .vamp-body textarea {
    background: rgba(20,0,0,0.5); border: none; border-bottom: 1px solid #500;
    color: #fff; width: 100%; padding: 10px; box-sizing: border-box;
    font-family: inherit; font-size: 1rem; outline: none; margin-bottom: 15px;
}
.vamp-body input:focus, .vamp-body textarea:focus { border-bottom-color: #f00; background: rgba(50,0,0,0.5); }
.vamp-body textarea { height: 150px; resize: none; color: #fcc; }
.vamp-body .input-line { margin-bottom: 15px; }
.vamp-body .prompt { color: #a00; display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.8rem; }
.vamp-footer { text-align: right; padding: 15px 20px; border-top: 1px solid #500; background: rgba(30,0,0,0.5); }
.vamp-footer .btn-submit { background: #a00; color: #000; border: none; padding: 10px 25px; font-weight: bold; cursor: pointer; font-family: inherit; transition: 0.2s; }
.vamp-footer .btn-submit:hover { background: #f00; color: #fff; box-shadow: 0 0 15px #f00; }

/* 게시판 헤더 */
.lb-board-header { display: flex; align-items: center; justify-content: space-between; padding: 10px 20px; background: rgba(50,0,0,0.2); border-bottom: 1px solid #330000; margin-bottom: 10px; }
.lb-board-header h1 { font-family: 'Courier Prime', monospace; color: #ff3333; font-size: 1rem; margin: 0; }
.lb-board-meta { font-size: 0.75rem; color: #888; font-family: 'Courier Prime', monospace; }

/* 관리자 체크박스 */
.lb-chk { position: absolute; top: 5px; right: 10px; opacity: 0.5; }
.lb-chk:hover { opacity: 1; }

/* 모바일 */
@media screen and (max-width: 768px) {
    .vampire-console { padding: 10px; }
    .log-meta { flex-direction: column; align-items: flex-start; gap: 5px; }
    .log-meta .command { margin-left: 0; font-size: 1.1rem; }
    .log-content { padding-left: 10px; font-size: 0.95rem; }
    .log-input-area { flex-direction: column; align-items: stretch; background: rgba(50,0,0,0.5); }
    .log-input-area .prompt { display: none; }
    .lb-cmt-input { margin-bottom: 10px; border-bottom: 1px solid #500; padding-bottom: 5px; }
    .cmd-btn { width: 100%; padding: 8px; text-align: center; }
    .terminal-input { flex-direction: column; align-items: flex-start; gap: 5px; }
    .terminal-input .cursor { display: none; }
}
</style>

<div id="bo_list" class="mg-inner lb-terminal-wrap">

    <!-- 게시판 헤더 -->
    <div class="vampire-console" style="border-bottom: none; padding-bottom: 0;">
        <div class="lb-board-header">
            <h1>root@echo:~/<?php echo $bo_table; ?></h1>
            <div class="lb-board-meta">
                <?php if ($admin_href) { ?>
                <a href="<?php echo $admin_href; ?>" style="color:#00ff00; margin-right:10px;">[ ADMIN ]</a>
                <?php } ?>
                total: <?php echo number_format($total_count); ?> records
            </div>
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

        <div class="vampire-console" style="border-top: none;">
            <?php if (count($list) > 0) { ?>
                <?php foreach ($list as $i => $row) {
                    $comments = isset($comments_map[$row['wr_id']]) ? $comments_map[$row['wr_id']] : array();
                ?>
                <div class="console-log-block" style="position:relative;">
                    <?php if ($is_checkbox) { ?>
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="lb-chk">
                    <?php } ?>

                    <div class="log-meta">
                        <span class="command"><?php echo $row['subject'] ?? htmlspecialchars($row['wr_subject'] ?? ''); ?></span>
                        <div class="log-meta-sub">
                            <span class="timestamp">[<?php echo date('Y.m.d H:i:s', strtotime($row['wr_datetime'])); ?>]</span>
                            <span class="user-id"><?php echo $row['name'] ?? htmlspecialchars($row['wr_name'] ?? ''); ?></span>
                        </div>
                    </div>

                    <div class="log-content"><?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?></div>

                    <?php if (count($comments) > 0) { ?>
                    <div class="log-replies">
                        <?php foreach ($comments as $cmt) { ?>
                        <div class="reply-line">
                            <span class="reply-user">&uarr; [<?php echo htmlspecialchars($cmt['wr_name']); ?>]:</span>
                            <span class="reply-text"><?php echo htmlspecialchars($cmt['wr_content']); ?></span>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if ($is_member || $board['bo_comment_level'] <= 1) { ?>
                    <div class="log-input-area">
                        <span class="prompt">reply@echo:~$</span>
                        <input type="text" class="lb-cmt-input" placeholder="Enter command to reply..." maxlength="1000"
                               onkeydown="if(event.key==='Enter'){lbSubmitComment(this.nextElementSibling,<?php echo $row['wr_id']; ?>);return false;}">
                        <button type="button" class="cmd-btn" onclick="lbSubmitComment(this, <?php echo $row['wr_id']; ?>)">ENTER</button>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty-vampire">
                    <div style="text-align:left; display:inline-block;">
                        <span style="opacity:0.7">> SEARCHING_DATABASE...</span><br>
                        <span style="opacity:0.7">> FILTER: "RECENT_POSTS"</span><br><br>
                        <span style="color:#ff5555; font-weight:bold;">> ERROR: 404_DATA_NOT_FOUND</span><br>
                        <span class="v-blink">_</span>
                    </div>
                </div>
            <?php } ?>

            <!-- 시스템 메시지 -->
            <div class="console-log-block system-msg">
                <div class="log-meta">
                    <span class="timestamp">[SYSTEM]</span>
                    <span class="user-id">root</span>
                </div>
                <div class="log-content">*** CONNECTION SECURE. ENCRYPTION LEVEL: MAX ***</div>
            </div>

            <!-- 글쓰기 버튼 -->
            <?php if ($write_href) { ?>
            <div class="terminal-input">
                <span class="prompt">user@input:~$</span>
                <button type="button" class="cmd-btn" onclick="lbOpenWrite()">sh write_message.sh</button>
                <span class="cursor">_</span>
            </div>
            <?php } ?>

            <?php if ($is_checkbox) { ?>
            <div style="margin-top:15px; display:flex; gap:5px;">
                <button type="submit" name="btn_submit" value="선택삭제" class="cmd-btn">DEL</button>
                <button type="submit" name="btn_submit" value="선택복사" class="cmd-btn">CP</button>
                <button type="submit" name="btn_submit" value="선택이동" class="cmd-btn">MV</button>
            </div>
            <?php } ?>
        </div>
    </form>

    <?php if ($total_page > 1) { ?>
    <div style="padding:15px 20px; background:#050505; border-top:1px solid #330000;">
        <nav class="flex items-center justify-center gap-1"><?php echo $write_pages; ?></nav>
    </div>
    <?php } ?>

    <!-- 글쓰기 모달 -->
    <?php if ($write_href) { ?>
    <div id="lb_modal_overlay" class="lb-modal-overlay hidden" onclick="if(document._mgMdTarget===this)lbCloseWrite()"></div>
    <div id="lb_write_modal" class="lb-write-modal lb-modal hidden">
        <div class="vamp-header">
            <span>root@scarlet-echo:~/write_msg</span>
            <button type="button" class="btn-close" onclick="lbCloseWrite()">[X]</button>
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
            <div class="vamp-body">
                <?php include(G5_THEME_PATH.'/skin/board/_lb_modal_inner.php'); ?>
            </div>
            <div class="vamp-footer">
                <button type="submit" class="btn-submit">./EXECUTE_PROTOCOL.sh</button>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<?php include(G5_THEME_PATH.'/skin/board/_lb_modal_assets.php'); ?>

<script>
// 터미널 댓글 등록
function lbSubmitComment(btn, parentId) {
    var wrap = btn.closest('.log-input-area');
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
            var block = btn.closest('.console-log-block');
            var replies = block.querySelector('.log-replies');
            if (!replies) {
                replies = document.createElement('div');
                replies.className = 'log-replies';
                block.insertBefore(replies, wrap);
            }
            var line = document.createElement('div');
            line.className = 'reply-line';
            line.innerHTML = '<span class="reply-user">&uarr; [' + d.comment.wr_name + ']:</span> <span class="reply-text">' + d.comment.wr_content + '</span>';
            replies.appendChild(line);
            input.value = '';
        })
        .catch(function() { btn.disabled = false; alert('네트워크 오류'); });
}
</script>
