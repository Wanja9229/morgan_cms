<?php
/**
 * Morgan Edition - Postit Board List Skin
 *
 * lino.it 스타일의 포스트잇 보드
 * 익명 앓이란 등에 적합한 포스트잇 노트 그리드
 */

if (!defined('_GNUBOARD_')) exit;

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// list.php 컨텍스트에서 누락되는 변수 기본값 (write.php에서만 설정됨)
if (!isset($is_name)) $is_name = (!$is_member);
if (!isset($is_password)) $is_password = (!$is_member);
if (!isset($is_secret)) $is_secret = ($board['bo_use_secret'] ?? 0);

// 포스트잇 배경색 배열
$postit_colors = array(
    'bg-amber-900/30',
    'bg-rose-900/30',
    'bg-blue-900/30',
    'bg-emerald-900/30',
    'bg-violet-900/30',
    'bg-cyan-900/30',
    'bg-orange-900/30',
);

// 포스트잇 상단 악센트 색상 (배경과 매칭)
$postit_accents = array(
    'bg-amber-500',
    'bg-rose-500',
    'bg-blue-500',
    'bg-emerald-500',
    'bg-violet-500',
    'bg-cyan-500',
    'bg-orange-500',
);
?>

<div id="bo_list" class="mg-inner">

    <!-- 게시판 헤더 -->
    <div class="card mb-4">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-mg-text-primary"><?php echo $board['bo_subject']; ?></h1>
                <p class="text-sm text-mg-text-muted">총 <?php echo number_format($total_count); ?>개의 포스트잇</p>
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
                <button type="button" onclick="togglePostitWrite()" class="btn btn-primary">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    새 포스트잇
                </button>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 인라인 작성 폼 (토글) -->
    <?php if ($write_href) { ?>
    <div id="postit_write_form" class="hidden mb-4">
        <div class="card border border-mg-accent/30">
            <h3 class="text-lg font-bold text-mg-text-primary mb-4">
                <svg class="w-5 h-5 inline-block mr-1 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                새 포스트잇 붙이기
            </h3>
            <form name="fpostitwrite" id="fpostitwrite" action="<?php echo $action_url ?? G5_BBS_URL.'/write_update.php'; ?>" method="post" onsubmit="return fpostit_submit(this);" autocomplete="off">
                <input type="hidden" name="w" value="">
                <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
                <input type="hidden" name="wr_id" value="0">
                <input type="hidden" name="sca" value="<?php echo $sca; ?>">
                <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
                <input type="hidden" name="stx" value="<?php echo $stx; ?>">
                <input type="hidden" name="spt" value="<?php echo $spt; ?>">
                <input type="hidden" name="page" value="<?php echo $page; ?>">
                <input type="hidden" name="token" value="">
                <input type="hidden" name="wr_subject" id="postit_wr_subject" value="<?php echo date('Y-m-d H:i'); ?> 포스트잇">

                <!-- 이름 (비회원) -->
                <?php if ($is_name) { ?>
                <div class="mb-3">
                    <input type="text" name="wr_name" value="<?php echo $name ?? ''; ?>" class="input" placeholder="이름" required>
                </div>
                <?php } ?>

                <!-- 비밀번호 (비회원) -->
                <?php if ($is_password) { ?>
                <div class="mb-3">
                    <input type="password" name="wr_password" class="input" placeholder="비밀번호" required>
                </div>
                <?php } ?>

                <!-- 내용 -->
                <div class="mb-3">
                    <textarea name="wr_content" id="postit_wr_content" rows="4" class="input w-full resize-none" placeholder="마음속 이야기를 적어주세요..." required></textarea>
                </div>

                <!-- 비밀글 옵션 -->
                <?php if ($is_secret) { ?>
                <div class="mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="secret" value="secret" class="w-4 h-4 rounded">
                        <span class="text-sm text-mg-text-secondary">비밀글</span>
                    </label>
                </div>
                <?php } ?>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="togglePostitWrite()" class="btn btn-secondary">취소</button>
                    <button type="submit" class="btn btn-primary">붙이기</button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>

    <!-- 포스트잇 그리드 -->
    <form name="fboardlist" id="fboardlist" action="<?php echo G5_BBS_URL; ?>/board_list_update.php" method="post">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table; ?>">
        <input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
        <input type="hidden" name="stx" value="<?php echo $stx; ?>">
        <input type="hidden" name="spt" value="<?php echo $spt; ?>">
        <input type="hidden" name="sca" value="<?php echo $sca; ?>">
        <input type="hidden" name="page" value="<?php echo $page; ?>">
        <input type="hidden" name="sw" value="">

        <?php if (count($list) > 0) { ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
            <?php foreach ($list as $i => $row) {
                $color_index = $row['wr_id'] % 7;
                $postit_bg = $postit_colors[$color_index];
                $postit_accent = $postit_accents[$color_index];

                // 내용 100자 자르기 (HTML 태그 제거)
                $raw_content = strip_tags($row['content'] ?? $row['wr_content'] ?? '');
                $short_content = mb_strlen($raw_content) > 100 ? mb_substr($raw_content, 0, 100) . '...' : $raw_content;

                // 작성자 표시: 익명 게시판이면 "익명", 아니면 이름
                $display_name = $row['name'] ?: '익명';
                if ($board['bo_use_name'] == 0 && !$row['mb_id']) {
                    $display_name = '익명';
                }
            ?>
            <!-- 포스트잇 카드 -->
            <div class="group relative">
                <?php if ($is_checkbox) { ?>
                <div class="absolute top-2 left-2 z-10">
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $row['wr_id']; ?>" id="chk_<?php echo $i; ?>"
                           class="w-4 h-4 rounded opacity-0 group-hover:opacity-100 transition-opacity">
                </div>
                <?php } ?>

                <div class="<?php echo $postit_bg; ?> rounded-lg shadow-lg cursor-pointer hover:shadow-xl hover:-translate-y-1 transition-all duration-200 overflow-hidden"
                     onclick="openPostit(<?php echo $row['wr_id']; ?>)">

                    <!-- 상단 악센트 바 -->
                    <div class="<?php echo $postit_accent; ?> h-1"></div>

                    <div class="p-4">
                        <!-- 내용 -->
                        <p class="text-sm text-mg-text-secondary leading-relaxed mb-3 break-words min-h-[3rem]"><?php echo nl2br(htmlspecialchars($short_content)); ?></p>

                        <!-- 하단 메타 -->
                        <div class="flex items-center justify-between text-xs text-mg-text-muted mt-auto pt-2 border-t border-white/5">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <?php echo htmlspecialchars($display_name); ?>
                            </span>
                            <span><?php echo $row['datetime2']; ?></span>
                        </div>

                        <?php if ($row['comment_cnt']) { ?>
                        <div class="mt-2 text-xs text-mg-accent">
                            <svg class="w-3 h-3 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <?php echo $row['comment_cnt']; ?>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- 모달: 전체 내용 보기 -->
                <div id="postit_modal_<?php echo $row['wr_id']; ?>" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" onclick="closePostit(<?php echo $row['wr_id']; ?>)">
                    <!-- 오버레이 -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

                    <!-- 모달 카드 -->
                    <div class="relative w-full max-w-2xl max-h-[80vh] flex flex-col <?php echo $postit_bg; ?> rounded-lg shadow-lg overflow-hidden" onclick="event.stopPropagation()">
                        <!-- 상단 악센트 바 -->
                        <div class="<?php echo $postit_accent; ?> h-1.5 flex-shrink-0"></div>

                        <!-- 모달 헤더 -->
                        <div class="flex items-center justify-between p-4 border-b border-white/10 flex-shrink-0">
                            <div class="flex items-center gap-2 text-sm text-mg-text-muted">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-mg-text-secondary font-medium"><?php echo htmlspecialchars($display_name); ?></span>
                                <span class="text-mg-text-muted">&middot;</span>
                                <span><?php echo $row['datetime2']; ?></span>
                            </div>
                            <button type="button" onclick="closePostit(<?php echo $row['wr_id']; ?>)" class="text-mg-text-muted hover:text-mg-text-primary transition-colors p-1 -mr-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        <!-- 모달 본문 -->
                        <div class="p-4 overflow-y-auto flex-1">
                            <div class="prose prose-invert max-w-none text-mg-text-secondary leading-relaxed text-sm">
                                <?php echo $row['content'] ?? nl2br(htmlspecialchars($row['wr_content'] ?? '')); ?>
                            </div>
                        </div>

                        <!-- 모달 하단 버튼 -->
                        <div class="flex items-center justify-between p-4 border-t border-white/10 flex-shrink-0">
                            <div class="flex items-center gap-2 text-xs text-mg-text-muted">
                                <span>조회 <?php echo $row['wr_hit']; ?></span>
                                <?php if ($row['comment_cnt']) { ?>
                                <span>&middot;</span>
                                <span>댓글 <?php echo $row['comment_cnt']; ?></span>
                                <?php } ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <?php
                                // 상세 보기 링크
                                if ($row['href']) { ?>
                                <a href="<?php echo $row['href']; ?>" class="btn btn-secondary text-xs py-1 px-3" onclick="event.stopPropagation()">상세보기</a>
                                <?php } ?>
                                <?php
                                // 삭제 버튼 (본인 또는 관리자)
                                $can_delete = false;
                                if ($is_admin) {
                                    $can_delete = true;
                                } elseif ($is_member && $member['mb_id'] == $row['mb_id']) {
                                    $can_delete = true;
                                }
                                if ($can_delete) { ?>
                                <a href="<?php echo G5_BBS_URL; ?>/delete.php?bo_table=<?php echo $bo_table; ?>&amp;wr_id=<?php echo $row['wr_id']; ?>"
                                   onclick="event.stopPropagation(); return confirm('이 포스트잇을 삭제하시겠습니까?');"
                                   class="btn btn-secondary text-xs py-1 px-3 text-mg-error hover:bg-mg-error/20">삭제</a>
                                <?php } ?>
                                <button type="button" onclick="closePostit(<?php echo $row['wr_id']; ?>)" class="btn btn-secondary text-xs py-1 px-3">닫기</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 mx-auto mb-4 text-mg-text-muted/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <p class="text-mg-text-muted text-lg mb-2">아직 포스트잇이 없습니다</p>
            <p class="text-mg-text-muted text-sm">첫 번째 포스트잇을 붙여보세요!</p>
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

</div>

<script>
// 포스트잇 모달 열기
function openPostit(id) {
    document.getElementById('postit_modal_' + id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

// 포스트잇 모달 닫기
function closePostit(id) {
    document.getElementById('postit_modal_' + id).classList.add('hidden');
    document.body.style.overflow = '';
}

// 인라인 작성 폼 토글
function togglePostitWrite() {
    var form = document.getElementById('postit_write_form');
    if (form) {
        form.classList.toggle('hidden');
        if (!form.classList.contains('hidden')) {
            // 폼이 열리면 제목에 현재 시간 설정 및 textarea 포커스
            var now = new Date();
            var y = now.getFullYear();
            var m = String(now.getMonth() + 1).padStart(2, '0');
            var d = String(now.getDate()).padStart(2, '0');
            var h = String(now.getHours()).padStart(2, '0');
            var mi = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('postit_wr_subject').value = y + '-' + m + '-' + d + ' ' + h + ':' + mi + ' 포스트잇';
            document.getElementById('postit_wr_content').focus();
        }
    }
}

// 인라인 작성 폼 제출 검증
function fpostit_submit(f) {
    if (!f.wr_content.value.trim()) {
        alert('내용을 입력해주세요.');
        f.wr_content.focus();
        return false;
    }
    return true;
}

// ESC 키로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modals = document.querySelectorAll('[id^="postit_modal_"]:not(.hidden)');
        modals.forEach(function(modal) {
            modal.classList.add('hidden');
        });
        document.body.style.overflow = '';
    }
});
</script>
