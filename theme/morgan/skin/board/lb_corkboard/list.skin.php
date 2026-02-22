<?php
/**
 * Morgan Edition - 로드비(코르크보드) 게시판 스킨
 *
 * 라이칸 뒷골목 벽보 디자인
 * 인라인 댓글 + 모달 글쓰기
 */

if (!defined('_GNUBOARD_')) exit;
include_once(G5_THEME_PATH.'/skin/board/_lb_common.php');
?>

<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nanum+Pen+Script&family=East+Sea+Dokdo&family=Black+Han+Sans&display=swap">

<style>
/* ── 로드비(코르크보드) CSS ── */
.lb-cork-wrap {
    background: #1a1814;
    padding: 0;
}
.lycan-wall {
    width: 100%;
    margin: 0 auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 30px;
    font-family: 'Noto Sans KR', sans-serif;
}

/* 게시글 쪽지 */
.sticky-note {
    background: #e8d0b0;
    color: #221;
    width: 100%;
    box-sizing: border-box;
    padding: 30px;
    box-shadow: 5px 5px 15px rgba(0,0,0,0.4);
    position: relative;
    transform: rotate(-0.5deg);
    transition: transform 0.2s;
    border: 1px solid #d7ccc8;
}
.sticky-note:nth-child(even) {
    transform: rotate(0.5deg);
    background: #e6d2b5;
}
.sticky-note:hover { transform: scale(1.01) rotate(0deg); z-index: 10; }

/* 마스킹 테이프 */
.masking-tape {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%) rotate(-2deg);
    width: 140px;
    height: 35px;
    background: rgba(255, 255, 255, 0.4);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
    clip-path: polygon(2% 0, 100% 2%, 98% 100%, 0% 98%);
    backdrop-filter: blur(2px);
}

/* 쪽지 내용 */
.note-header {
    font-size: 0.9rem;
    color: #5d4037;
    opacity: 0.8;
    border-bottom: 2px dashed rgba(62, 39, 35, 0.2);
    padding-bottom: 10px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.note-header .subject {
    font-family: 'Black Han Sans', sans-serif;
    font-size: 1.1rem;
    color: #3e2723;
}
.note-header-sub {
    font-size: 0.8rem;
    text-align: right;
}
.note-header-sub .writer { display: block; font-weight: bold; }

.note-body {
    font-family: 'Black Han Sans', sans-serif;
    font-size: 1.25rem;
    line-height: 1.6;
    margin-bottom: 25px;
    color: #3e2723;
    white-space: pre-line;
}

/* 댓글 영역 */
.note-comments {
    background: rgba(62, 39, 35, 0.05);
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 4px;
    border: 1px solid rgba(62, 39, 35, 0.1);
}
.comment-scrap {
    font-size: 0.95rem;
    border-bottom: 1px dotted rgba(62, 39, 35, 0.3);
    padding: 6px 0;
    line-height: 1.4;
}
.comment-scrap:last-child { border-bottom: none; }
.comment-scrap strong { color: #5d4037; margin-right: 5px; }

/* 댓글 입력 */
.note-input-wrap {
    display: flex;
    border-top: 2px solid rgba(62, 39, 35, 0.1);
    padding-top: 15px;
    align-items: center;
}
.scribble-input {
    border: none;
    background: transparent;
    border-bottom: 2px solid rgba(62, 39, 35, 0.3);
    flex-grow: 1;
    font-family: 'Nanum Pen Script', cursive;
    font-size: 1.3rem;
    outline: none;
    color: #3e2723;
    padding: 5px;
}
.scribble-input::placeholder { color: rgba(62, 39, 35, 0.4); }
.scribble-btn {
    background: none; border: none; cursor: pointer;
    font-size: 1.5rem; margin-left: 10px; opacity: 0.8;
    transition: transform 0.2s;
}
.scribble-btn:hover { transform: scale(1.2) rotate(10deg); }

/* 글쓰기 버튼 */
.spray-action { text-align: center; margin-top: 30px; }
.spray-action a {
    display: inline-block;
    background: #212121;
    color: #ffa000;
    padding: 12px 35px;
    font-weight: bold;
    text-decoration: none;
    border: 3px solid #ffa000;
    font-family: 'Impact', sans-serif;
    letter-spacing: 1px;
    transform: rotate(-2deg);
    box-shadow: 3px 3px 0 #ffa000;
    transition: all 0.2s;
    cursor: pointer;
}
.spray-action a:hover {
    transform: rotate(0deg) scale(1.05);
    background: #ffa000; color: #000; box-shadow: 3px 3px 0 #000;
}

/* 관리자 공지 */
.lycan-admin-notice {
    position: relative;
    width: 100%;
    margin: 40px auto 20px;
    padding: 40px;
    box-sizing: border-box;
    background-color: #d7ccc8;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.1'/%3E%3C/svg%3E");
    border: 1px solid #a1887f;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    transform: rotate(1deg);
}
.lycan-admin-notice .notice-title {
    margin: 0 0 20px 0;
    font-family: 'Impact', sans-serif;
    font-size: 2.2rem;
    color: #3e2723;
    text-align: center;
    border-bottom: 4px solid #3e2723;
    padding-bottom: 10px;
    letter-spacing: 2px;
}
.lycan-admin-notice .notice-text {
    font-family: 'Noto Sans KR', sans-serif;
    font-size: 1.15rem;
    color: #4e342e;
    line-height: 1.7;
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
}
.lycan-admin-notice .notice-warning {
    font-family: 'Nanum Pen Script', cursive;
    font-size: 1.6rem;
    color: #b71c1c;
    transform: rotate(-2deg);
    margin-top: 15px;
    background: rgba(183, 28, 28, 0.1);
    padding: 10px;
    text-align: center;
}
.lycan-admin-notice .notice-signature {
    text-align: right;
    font-family: "East Sea Dokdo", sans-serif;
    font-size: 1.8rem;
    color: #3e2723;
    margin-top: 20px;
    opacity: 0.9;
}
.lycan-admin-notice .highlight {
    color: #b71c1c; text-decoration: underline; text-underline-offset: 4px;
}

/* 장식 (못, 도장) */
.rusty-nail {
    position: absolute; width: 14px; height: 14px;
    background: radial-gradient(circle at 30% 30%, #757575, #212121);
    border-radius: 50%; box-shadow: 1px 1px 3px rgba(0,0,0,0.7);
    border: 1px solid #424242;
}
.rusty-nail.top-left { top: 12px; left: 12px; }
.rusty-nail.top-right { top: 12px; right: 12px; }
.rusty-nail.bottom-left { bottom: 12px; left: 12px; }
.rusty-nail.bottom-right { bottom: 12px; right: 12px; }
.stamp-mark {
    position: absolute; bottom: 30px; right: 40px;
    font-family: 'Impact', sans-serif; font-size: 4rem;
    color: rgba(183, 28, 28, 0.25);
    border: 6px solid rgba(183, 28, 28, 0.25);
    padding: 5px 20px; border-radius: 8px;
    transform: rotate(-15deg); pointer-events: none;
    text-transform: uppercase; letter-spacing: 5px;
}

/* 빈 상태 */
.empty-lycan {
    font-family: 'Impact', sans-serif; color: #a1887f;
    text-align: center; padding: 60px 20px;
}
.l-scratch {
    font-size: 4rem; color: #8d6e63; letter-spacing: -2px;
    opacity: 0.2; transform: rotate(-5deg);
}

/* 모달 */
.lb-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(3px); z-index: 50; }
.lb-modal-overlay.hidden, .lb-write-modal.hidden { display: none !important; }
.lb-write-modal.theme-lycan {
    position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-1deg);
    width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto; z-index: 51;
    background: #d7ccc8;
    padding: 30px;
    box-shadow: 10px 10px 30px rgba(0,0,0,0.6);
    font-family: 'Gulim', sans-serif;
    animation: paper-slap 0.3s ease-out;
}
@keyframes paper-slap { from { transform: translate(-50%, -80%) rotate(5deg); opacity: 0; } }

.lb-write-modal .tape-top {
    position: absolute; top: -18px; left: 50%; transform: translateX(-50%) rotate(1deg);
    width: 120px; height: 40px; background: rgba(255,255,255,0.4);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}
.lb-write-modal .btn-close-lycan {
    position: absolute; top: 10px; right: 10px; background: none; border: none;
    font-size: 1.8rem; cursor: pointer; color: #3e2723;
}
.lycan-body h3 {
    margin: 0 0 20px 0; color: #3e2723; text-align: center;
    border-bottom: 3px solid #3e2723; font-family: "East Sea Dokdo", sans-serif; font-size: 2rem;
}
.lycan-body input, .lycan-body textarea {
    background: transparent; border: none; width: 100%;
    font-family: 'Nanum Pen Script', cursive; font-size: 1.4rem;
    outline: none; color: #3e2723; box-sizing: border-box;
}
.lycan-body input { border-bottom: 1px dashed #8d6e63; margin-bottom: 15px; padding: 5px; }
.lycan-body textarea {
    height: 180px; background-image: linear-gradient(transparent 95%, #bcaaa4 95%);
    background-size: 100% 1.8rem; line-height: 1.8rem; resize: none; padding: 5px;
}
.lycan-body .divider { height: 1px; background: rgba(62,39,35,0.2); margin: 10px 0; }
.lycan-footer { text-align: center; margin-top: 25px; }
.lycan-footer .btn-submit {
    background: #3e2723; color: #efebe9; padding: 10px 40px; border: 2px solid #5d4037;
    font-size: 1.2rem; cursor: pointer; transform: rotate(1deg); font-family: "East Sea Dokdo", sans-serif;
    transition: all 0.2s;
}
.lycan-footer .btn-submit:hover { background: #5d4037; transform: rotate(0deg) scale(1.05); }

/* 게시판 헤더 */
.lb-board-header-cork {
    display: flex; align-items: center; justify-content: space-between;
    padding: 15px 20px;
    background: #d7ccc8;
    border-bottom: 3px solid #a1887f;
    margin-bottom: 10px;
}
.lb-board-header-cork h1 {
    font-family: "East Sea Dokdo", sans-serif;
    color: #3e2723; font-size: 1.5rem; margin: 0;
}
.lb-board-meta-cork {
    font-size: 0.8rem; color: #5d4037;
    font-family: 'Noto Sans KR', sans-serif;
}
.lb-board-meta-cork a { color: #b71c1c; text-decoration: none; font-weight: bold; }
.lb-board-meta-cork a:hover { text-decoration: underline; }

/* 관리자 체크박스 */
.lb-chk { position: absolute; top: 5px; right: 10px; opacity: 0.5; }
.lb-chk:hover { opacity: 1; }

/* 모바일 */
@media screen and (max-width: 768px) {
    .lycan-wall { padding: 10px; gap: 20px; }
    .sticky-note {
        padding: 20px;
        transform: rotate(0deg) !important;
        margin-bottom: 10px;
    }
    .sticky-note:nth-child(even) { transform: rotate(0deg) !important; }
    .note-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    .note-header-sub { text-align: left; width: 100%; }
    .note-input-wrap { flex-direction: column; align-items: stretch; }
    .scribble-btn {
        margin-top: 10px; align-self: flex-end; width: 100%;
        background: rgba(62, 39, 35, 0.1); border-radius: 4px; padding: 5px;
    }
    .lycan-admin-notice { padding: 25px 20px; }
    .lycan-admin-notice .notice-title { font-size: 1.6rem !important; }
    .lycan-admin-notice .notice-text { font-size: 1rem !important; }
    .stamp-mark {
        font-size: 2.5rem; bottom: 15px; right: 15px; border-width: 4px;
    }
    .lb-write-modal.theme-lycan { width: 95%; padding: 20px; transform: translate(-50%, -50%) rotate(0deg); }
}
</style>

<div id="bo_list" class="mg-inner lb-cork-wrap">

    <!-- 게시판 헤더 -->
    <div class="lb-board-header-cork">
        <h1><?php echo $board['bo_subject'] ?? $bo_table; ?></h1>
        <div class="lb-board-meta-cork">
            <?php if ($admin_href) { ?>
            <a href="<?php echo $admin_href; ?>" style="margin-right:10px;">[ADMIN]</a>
            <?php } ?>
            <?php echo number_format($total_count); ?>건
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

        <div class="lycan-wall">
            <?php if (count($list) > 0) { ?>
                <?php foreach ($list as $i => $row) {
                    $comments = isset($comments_map[$row['wr_id']]) ? $comments_map[$row['wr_id']] : array();
                ?>
                <div class="sticky-note" style="position:relative;">
                    <!-- 마스킹 테이프 -->
                    <div class="masking-tape"></div>

                    <?php if ($is_checkbox) { ?>
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>" class="lb-chk">
                    <?php } ?>

                    <div class="note-header">
                        <span class="subject"><?php echo $row['subject'] ?? htmlspecialchars($row['wr_subject'] ?? ''); ?></span>
                        <div class="note-header-sub">
                            <span class="writer">By. <?php echo $row['name'] ?? htmlspecialchars($row['wr_name'] ?? ''); ?></span>
                            <span class="date"><?php echo date('Y.m.d H:i:s', strtotime($row['wr_datetime'])); ?></span>
                        </div>
                    </div>

                    <div class="note-body"><?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?></div>

                    <?php if (count($comments) > 0) { ?>
                    <div class="note-comments">
                        <?php foreach ($comments as $cmt) { ?>
                        <div class="comment-scrap">
                            <strong><?php echo htmlspecialchars($cmt['wr_name']); ?>:</strong> <?php echo htmlspecialchars($cmt['wr_content']); ?>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <?php if ($is_member || $board['bo_comment_level'] <= 1) { ?>
                    <div class="note-input-wrap">
                        <input type="text" class="scribble-input lb-cmt-input" placeholder="낙서 남기기..." maxlength="1000"
                               onkeydown="if(event.key==='Enter'){lbSubmitComment(this.nextElementSibling,<?php echo $row['wr_id']; ?>);return false;}">
                        <button type="button" class="scribble-btn" onclick="lbSubmitComment(this, <?php echo $row['wr_id']; ?>)">&#9998;</button>
                    </div>
                    <?php } ?>
                </div>
                <?php } ?>
            <?php } else { ?>
                <div class="empty-lycan">
                    <div class="l-scratch">NO SCENT</div>
                    <h2 style="margin:10px 0; font-size:1.5rem; color:#78716c;">
                        "이곳엔 아무런 흔적도 없다."
                    </h2>
                </div>
            <?php } ?>

            <!-- 관리자 공지 (TODO: 관리자 설정으로 내용/표시여부 제어) -->
            <?php /* 비활성 — 추후 관리자 설정 연동
            <div class="lycan-admin-notice">
                <div class="rusty-nail top-left"></div>
                <div class="rusty-nail top-right"></div>
                <div class="rusty-nail bottom-left"></div>
                <div class="rusty-nail bottom-right"></div>

                <div class="notice-content">
                    <h3 class="notice-title">/// PACK RULES ///</h3>
                    <p class="notice-text">
                        잡담만 써두지 말고 팔릴만한 <strong class="highlight">정보</strong>를 공유하라고.
                    </p>
                    <p class="notice-warning">
                        * 저번에 보드 깨먹은 놈은 자수해라.
                    </p>
                    <div class="notice-signature">
                        - 관리자
                    </div>
                </div>
                <div class="stamp-mark">OBEY</div>
            </div>
            */ ?>

            <!-- 글쓰기 버튼 -->
            <?php if ($write_href) { ?>
            <div class="spray-action">
                <a href="javascript:void(0);" onclick="lbOpenWrite()">낙서 남기기</a>
            </div>
            <?php } ?>

            <?php if ($is_checkbox) { ?>
            <div style="margin-top:15px; display:flex; gap:5px; justify-content:center;">
                <button type="submit" name="btn_submit" value="선택삭제" style="background:#3e2723;color:#efebe9;border:1px solid #5d4037;padding:6px 15px;cursor:pointer;">삭제</button>
                <button type="submit" name="btn_submit" value="선택복사" style="background:#3e2723;color:#efebe9;border:1px solid #5d4037;padding:6px 15px;cursor:pointer;">복사</button>
                <button type="submit" name="btn_submit" value="선택이동" style="background:#3e2723;color:#efebe9;border:1px solid #5d4037;padding:6px 15px;cursor:pointer;">이동</button>
            </div>
            <?php } ?>
        </div>
    </form>

    <?php if ($total_page > 1) { ?>
    <div style="padding:15px 20px; background:#1a1814; border-top:2px solid #3e2723;">
        <nav class="flex items-center justify-center gap-1"><?php echo $write_pages; ?></nav>
    </div>
    <?php } ?>

    <!-- 글쓰기 모달 -->
    <?php if ($write_href) { ?>
    <div id="lb_modal_overlay" class="lb-modal-overlay hidden" onclick="lbCloseWrite()"></div>
    <div id="lb_write_modal" class="lb-write-modal theme-lycan hidden">
        <div class="tape-top"></div>
        <button type="button" class="btn-close-lycan" onclick="lbCloseWrite()">&#10006;</button>

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

            <div class="lycan-body">
                <h3>뒷골목 낙서</h3>
                <?php include(G5_THEME_PATH.'/skin/board/_lb_modal_inner.php'); ?>
            </div>
            <div class="lycan-footer">
                <button type="submit" class="btn-submit">압정으로 박기 &#128204;</button>
            </div>
        </form>
    </div>
    <?php } ?>
</div>

<?php include(G5_THEME_PATH.'/skin/board/_lb_modal_assets.php'); ?>

<script>
// 코르크보드 댓글 등록
function lbSubmitComment(btn, parentId) {
    var wrap = btn.closest('.note-input-wrap');
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
            var block = btn.closest('.sticky-note');
            var comments = block.querySelector('.note-comments');
            if (!comments) {
                comments = document.createElement('div');
                comments.className = 'note-comments';
                block.insertBefore(comments, wrap);
            }
            var scrap = document.createElement('div');
            scrap.className = 'comment-scrap';
            scrap.innerHTML = '<strong>' + d.comment.wr_name + ':</strong> ' + d.comment.wr_content;
            comments.appendChild(scrap);
            input.value = '';
        })
        .catch(function() { btn.disabled = false; alert('네트워크 오류'); });
}
</script>
