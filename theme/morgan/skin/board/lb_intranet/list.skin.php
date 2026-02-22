<?php
/**
 * Morgan Edition - 로드비(인트라넷) 게시판 스킨
 *
 * 헌터 WRO 인트라넷 / 기밀문서 디자인
 * 인라인 댓글 + 모달 글쓰기
 */

if (!defined('_GNUBOARD_')) exit;
include_once(G5_THEME_PATH.'/skin/board/_lb_common.php');
?>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">

<style>
/* ── 로드비(인트라넷) CSS ── */
.lb-intranet-wrap { background: #0f172a; padding: 0; }
.wro-report-board {
    display: flex; flex-direction: column; gap: 20px;
    font-family: 'Roboto', 'Noto Sans KR', sans-serif; width: 100%; padding: 20px;
}
.intel-card {
    background: rgba(15, 23, 42, 0.95); border: 1px solid #1e40af;
    border-left: 4px solid #3b82f6; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.5);
    position: relative; overflow: hidden;
}
.intel-header {
    background: rgba(30, 58, 138, 0.3); padding: 10px 20px;
    border-bottom: 1px solid #1e40af; display: flex; justify-content: space-between; align-items: center;
}
.doc-id { color: #60a5fa; font-family: 'Courier New', monospace; font-size: 0.85rem; letter-spacing: 1px; }
.doc-status {
    font-size: 0.7rem; padding: 2px 8px; border-radius: 2px; font-weight: bold;
    background: #0f172a; border: 1px solid #1e40af; color: #94a3b8;
}
.doc-status.active { background: rgba(21,128,61,0.2); border-color: #16a34a; color: #4ade80; }
.intel-meta { padding: 12px 20px; font-size: 0.9rem; color: #94a3b8; border-bottom: 1px dashed #334155; }
.intel-meta strong { color: #cbd5e1; margin-right: 5px; }
.intel-body { padding: 20px; color: #e2e8f0; line-height: 1.6; font-size: 0.95rem; white-space: pre-line; }
.intel-addendum {
    background: #020617; padding: 15px 20px; font-size: 0.85rem; border-top: 1px solid #1e293b;
}
.addendum-header { color: #475569; font-size: 0.7rem; font-weight: bold; margin-bottom: 10px; letter-spacing: 1px; text-transform: uppercase; }
.addendum-row { margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid rgba(255,255,255,0.05); }
.addendum-row:last-child { border-bottom: none; }
.addendum-row .user { color: #3b82f6; font-weight: bold; margin-right: 5px; }
.addendum-row .text { color: #94a3b8; }
.intel-input { padding: 15px 20px; background: rgba(30,41,59,0.3); border-top: 1px solid #1e40af; }
.input-label { display: block; font-size: 0.7rem; color: #60a5fa; margin-bottom: 8px; font-weight: bold; }
.input-group { display: flex; gap: 0; }
.input-group .lb-cmt-input {
    flex-grow: 1; background: #0f172a; border: 1px solid #334155;
    color: #fff; padding: 10px; outline: none; font-family: inherit; font-size: 0.9rem;
}
.input-group .lb-cmt-input:focus { border-color: #3b82f6; }
.input-group button {
    background: #1d4ed8; color: #fff; border: 1px solid #1d4ed8;
    padding: 0 20px; font-weight: bold; cursor: pointer; transition: background 0.2s;
}
.input-group button:hover { background: #2563eb; }
.doc-footer { text-align: right; margin-top: 20px; padding: 0 20px 20px; }
.report-btn {
    display: inline-block; background: rgba(14,165,233,0.1); border: 1px solid #0ea5e9;
    color: #0ea5e9; padding: 10px 25px; text-decoration: none; transition: all 0.3s;
    font-size: 0.9rem; font-weight: bold; letter-spacing: 1px; cursor: pointer;
}
.report-btn:hover { background: #0ea5e9; color: #000; box-shadow: 0 0 15px rgba(14,165,233,0.4); }
.empty-hunter {
    font-family: 'Courier New', monospace; color: #38bdf8;
    border: 1px dashed #0c4a6e; background: rgba(15,23,42,0.5);
    position: relative; padding: 60px 20px; text-align: center;
}
.h-scan-bar { width: 60%; height: 2px; background: #38bdf8; box-shadow: 0 0 10px #38bdf8; margin: 0 auto 20px; opacity: 0.5; }
.hunter-sys-msg {
    margin-top: 30px; background: rgba(15,23,42,0.95);
    border: 1px solid #0ea5e9; box-shadow: 0 0 15px rgba(14,165,233,0.1);
}
.hunter-sys-msg .sys-meta {
    background: rgba(14,165,233,0.1); border-bottom: 1px solid rgba(14,165,233,0.2);
    padding: 8px 15px; display: flex; justify-content: center; gap: 15px;
    color: #38bdf8; font-family: 'Courier New', monospace; font-size: 0.8rem; letter-spacing: 1px;
}
.hunter-sys-msg .sys-content { padding: 20px; text-align: center; font-size: 0.9rem; color: #bae6fd; letter-spacing: 1px; }
.scan-line-decoration {
    height: 2px; background: linear-gradient(90deg, transparent, #0ea5e9, transparent);
    animation: hunter-scan-loop 3s linear infinite;
}
@keyframes hunter-scan-loop { 0% { background-position: -100% 0; } 100% { background-position: 200% 0; } }

/* 게시판 헤더 */
.lb-board-header-intra {
    display: flex; align-items: center; justify-content: space-between;
    padding: 15px 20px; background: rgba(30,58,138,0.2); border-bottom: 1px solid #1e40af;
}
.lb-board-header-intra h1 { font-family: 'Roboto', sans-serif; color: #60a5fa; font-size: 0.9rem; font-weight: bold; letter-spacing: 2px; margin: 0; }
.lb-board-header-intra .meta { font-size: 0.75rem; color: #475569; }

/* 모달 */
.lb-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(3px); z-index: 50; }
.lb-modal-overlay.hidden, .lb-modal.hidden { display: none !important; }
.lb-write-modal {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; z-index: 51;
    background: rgba(15,23,42,0.98); border: 1px solid #0ea5e9;
    box-shadow: 0 0 30px rgba(14,165,233,0.2); font-family: 'Roboto', sans-serif;
    animation: hud-scan 0.3s ease-out;
}
@keyframes hud-scan { from { opacity: 0; transform: translate(-50%, -45%); } to { opacity: 1; transform: translate(-50%, -50%); } }
.hunter-header {
    background: rgba(14,165,233,0.1); border-bottom: 1px solid #0ea5e9;
    padding: 12px 20px; display: flex; justify-content: space-between; align-items: center;
    color: #0ea5e9; font-weight: bold; letter-spacing: 1px;
}
.hunter-header .btn-close { background: transparent; border: 1px solid #0ea5e9; color: #0ea5e9; padding: 2px 8px; cursor: pointer; font-size: 0.7rem; }
.hunter-header .btn-close:hover { background: #0ea5e9; color: #000; }
.hunter-body { padding: 20px; position: relative; color: #fff; }
.hunter-body .grid-bg {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background-image: linear-gradient(rgba(14,165,233,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(14,165,233,0.05) 1px, transparent 1px);
    background-size: 20px 20px; pointer-events: none;
}
.hunter-body label { display: block; color: #38bdf8; font-size: 0.75rem; margin-bottom: 6px; font-weight: bold; position: relative; z-index: 1; }
.hunter-body input, .hunter-body textarea {
    background: rgba(0,0,0,0.5); border: 1px solid #1e40af; color: #e2e8f0;
    width: 100%; padding: 10px; box-sizing: border-box; outline: none; margin-bottom: 15px;
    font-family: inherit; font-size: 0.95rem; position: relative; z-index: 1;
}
.hunter-body input:focus, .hunter-body textarea:focus { border-color: #0ea5e9; background: rgba(14,165,233,0.05); }
.hunter-body textarea { height: 150px; resize: none; }
.hunter-footer { padding: 15px 20px; border-top: 1px solid #1e40af; text-align: right; background: rgba(0,0,0,0.3); }
.hunter-footer .btn-submit { background: #0284c7; color: #fff; border: none; padding: 10px 30px; font-weight: bold; cursor: pointer; transition: all 0.3s; }
.hunter-footer .btn-submit:hover { background: #0ea5e9; box-shadow: 0 0 15px #0ea5e9; }

/* 관리자 체크박스 */
.lb-chk { position: absolute; top: 12px; right: 10px; z-index: 2; opacity: 0.5; }
.lb-chk:hover { opacity: 1; }

/* 모바일 */
@media screen and (max-width: 768px) {
    .wro-report-board { padding: 10px; gap: 15px; }
    .intel-header { flex-direction: column; align-items: flex-start; gap: 8px; }
    .doc-status { align-self: flex-start; }
    .input-group { flex-direction: column; }
    .input-group button { width: 100%; margin-top: 5px; padding: 12px; }
    .report-btn { width: 100%; text-align: center; box-sizing: border-box; display: block; }
}
</style>

<div id="bo_list" class="mg-inner lb-intranet-wrap">

    <!-- 게시판 헤더 -->
    <div class="lb-board-header-intra">
        <h1>WRO: <?php echo htmlspecialchars($board['bo_subject']); ?></h1>
        <div class="meta">
            <?php if ($admin_href) { ?>
            <a href="<?php echo $admin_href; ?>" style="color:#0ea5e9; margin-right:10px;">[ ADMIN ]</a>
            <?php } ?>
            <?php echo number_format($total_count); ?> RECORDS
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

        <div class="wro-report-board">
            <?php if (count($list) > 0) { ?>
                <?php foreach ($list as $i => $row) {
                    $comments = isset($comments_map[$row['wr_id']]) ? $comments_map[$row['wr_id']] : array();
                ?>
                <div class="intel-card">
                    <?php if ($is_checkbox) { ?>
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="lb-chk">
                    <?php } ?>

                    <div class="intel-header">
                        <div class="doc-id">DOC-#<?php echo $row['wr_id']; ?></div>
                        <div class="doc-status active">ACTIVE</div>
                    </div>

                    <div class="intel-meta">
                        <strong>FROM:</strong> <?php echo $row['name'] ?? htmlspecialchars($row['wr_name'] ?? ''); ?><br>
                        <strong>SUBJECT:</strong> <?php echo $row['subject'] ?? htmlspecialchars($row['wr_subject'] ?? ''); ?>
                    </div>

                    <div class="intel-body"><?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?></div>

                    <?php if (count($comments) > 0) { ?>
                    <div class="intel-addendum">
                        <div class="addendum-header">ADDENDUM LOG (<?php echo count($comments); ?>)</div>
                        <?php foreach ($comments as $cmt) { ?>
                        <div class="addendum-row">
                            <span class="user">[<?php echo htmlspecialchars($cmt['wr_name']); ?>]:</span>
                            <span class="text"><?php echo htmlspecialchars($cmt['wr_content']); ?></span>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if ($is_member || $board['bo_comment_level'] <= 1) { ?>
                    <div class="intel-input">
                        <span class="input-label">FILE NEW ENTRY:</span>
                        <div class="input-group">
                            <input type="text" class="lb-cmt-input" placeholder="Type tactical update..." maxlength="1000"
                                   onkeydown="if(event.key==='Enter'){lbSubmitComment(this.nextElementSibling,<?php echo $row['wr_id']; ?>);return false;}">
                            <button type="button" onclick="lbSubmitComment(this, <?php echo $row['wr_id']; ?>)">SUBMIT</button>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty-hunter">
                    <div class="h-scan-bar"></div>
                    <h2 style="margin:0; font-size:2rem; letter-spacing:3px;">SECTOR CLEAR</h2>
                    <p style="margin-top:10px; font-size:0.8rem; opacity:0.8;">
                        NO ANOMALIES DETECTED IN THIS AREA.<br>SCAN COMPLETE.
                    </p>
                </div>
            <?php } ?>

            <!-- 시스템 메시지 -->
            <div class="hunter-sys-msg">
                <div class="sys-meta">
                    <span>[WRO_NET]</span>
                    <span>HQ_SERVER</span>
                </div>
                <div class="sys-content">>> DATA LOGS SYNCHRONIZED. CONTINUOUS SURVEILLANCE ACTIVE.</div>
                <div class="scan-line-decoration"></div>
            </div>

            <!-- 글쓰기 버튼 -->
            <?php if ($write_href) { ?>
            <div class="doc-footer">
                <button type="button" class="report-btn" onclick="lbOpenWrite()">FILE NEW REPORT</button>
            </div>
            <?php } ?>

            <?php if ($is_checkbox) { ?>
            <div style="margin-top:15px; display:flex; gap:5px; padding:0 20px;">
                <button type="submit" name="btn_submit" value="선택삭제" class="report-btn" style="font-size:0.75rem; padding:5px 15px;">DELETE</button>
                <button type="submit" name="btn_submit" value="선택복사" class="report-btn" style="font-size:0.75rem; padding:5px 15px;">COPY</button>
                <button type="submit" name="btn_submit" value="선택이동" class="report-btn" style="font-size:0.75rem; padding:5px 15px;">MOVE</button>
            </div>
            <?php } ?>
        </div>
    </form>

    <?php if ($total_page > 1) { ?>
    <div style="padding:15px 20px; background:#0f172a; border-top:1px solid #1e40af;">
        <nav class="flex items-center justify-center gap-1"><?php echo $write_pages; ?></nav>
    </div>
    <?php } ?>

    <!-- 글쓰기 모달 -->
    <?php if ($write_href) { ?>
    <div id="lb_modal_overlay" class="lb-modal-overlay hidden" onclick="lbCloseWrite()"></div>
    <div id="lb_write_modal" class="lb-write-modal lb-modal hidden">
        <div class="hunter-header">
            <span>SEC_LV: 4</span>
            <span>TACTICAL REPORT FORM</span>
            <button type="button" class="btn-close" onclick="lbCloseWrite()">CLOSE</button>
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
            <div class="hunter-body">
                <div class="grid-bg"></div>
                <?php include(G5_THEME_PATH.'/skin/board/_lb_modal_inner.php'); ?>
            </div>
            <div class="hunter-footer">
                <button type="submit" class="btn-submit">TRANSMIT DATA</button>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<?php include(G5_THEME_PATH.'/skin/board/_lb_modal_assets.php'); ?>

<script>
// 인트라넷 댓글 등록
function lbSubmitComment(btn, parentId) {
    var wrap = btn.closest('.intel-input');
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
            var card = btn.closest('.intel-card');
            var addendum = card.querySelector('.intel-addendum');
            if (!addendum) {
                addendum = document.createElement('div');
                addendum.className = 'intel-addendum';
                addendum.innerHTML = '<div class="addendum-header">ADDENDUM LOG</div>';
                card.insertBefore(addendum, wrap);
            }
            var row = document.createElement('div');
            row.className = 'addendum-row';
            row.innerHTML = '<span class="user">[' + d.comment.wr_name + ']:</span> <span class="text">' + d.comment.wr_content + '</span>';
            addendum.appendChild(row);
            input.value = '';
        })
        .catch(function() { btn.disabled = false; alert('네트워크 오류'); });
}
</script>
