<?php
/**
 * Morgan Edition - 의뢰 작성
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

// 회원 레벨 체크
$_lv = mg_check_member_level('concierge', $member['mb_level']);
if (!$_lv['allowed']) { alert_close("의뢰는 회원 레벨 {$_lv['required']} 이상부터 이용 가능합니다."); }

// 페널티 체크
$_penalty = mg_check_concierge_penalty($member['mb_id']);
if ($_penalty['banned']) {
    alert_close("의뢰 이용이 제한되었습니다. ({$_penalty['until']}까지, 미이행 {$_penalty['count']}회)");
}

$characters = mg_get_usable_characters($member['mb_id']);
$api_url = G5_BBS_URL . '/concierge_api.php';

$g5['title'] = '의뢰 등록';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <div class="mb-6">
        <a href="<?php echo G5_BBS_URL; ?>/concierge.php" class="text-sm text-mg-text-secondary hover:text-mg-accent inline-flex items-center gap-1 mb-3">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            목록으로
        </a>
        <h1 class="text-2xl font-bold text-mg-text-primary">의뢰 등록</h1>
    </div>

    <div class="card">
        <form id="concierge-form" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-mg-text-primary mb-1">의뢰 제목 *</label>
                <input type="text" id="cc_title" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg" required placeholder="의뢰 제목을 입력하세요">
            </div>

            <div>
                <label class="block text-sm font-medium text-mg-text-primary mb-1">의뢰 내용 *</label>
                <textarea id="cc_content" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg" rows="5" required placeholder="원하는 창작 방향, 세부 사항 등을 작성하세요"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-mg-text-primary mb-1">캐릭터 *</label>
                    <select id="ch_id" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg" required>
                        <option value="">캐릭터 선택</option>
                        <?php foreach ($characters as $ch) { ?>
                        <option value="<?php echo $ch['ch_id']; ?>"><?php echo htmlspecialchars($ch['ch_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-mg-text-primary mb-1">의뢰 유형</label>
                    <select id="cc_type" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg">
                        <option value="collaboration">합작</option>
                        <option value="illustration">일러스트</option>
                        <option value="novel">소설</option>
                        <option value="other">기타</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-mg-text-primary mb-1">모집 인원</label>
                    <input type="number" id="cc_max_members" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg" min="1" max="5" value="1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-mg-text-primary mb-1">매칭 방식</label>
                    <select id="cc_match_mode" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg">
                        <option value="direct">직접 선택</option>
                        <option value="lottery">추첨</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-mg-text-primary mb-1">수행 보상</label>
                    <div class="px-3 py-2 bg-mg-bg-tertiary text-mg-text-secondary rounded-lg text-sm"><?php echo mg_config('concierge_reward', 50); ?>P</div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-mg-text-primary mb-1">지원 마감일 *</label>
                <input type="datetime-local" id="cc_deadline" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg" required>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="px-6 py-2.5 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors">등록하기</button>
                <a href="<?php echo G5_BBS_URL; ?>/concierge.php" class="px-6 py-2.5 border border-mg-bg-tertiary text-mg-text-secondary rounded-lg hover:bg-mg-bg-tertiary transition-colors">취소</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('concierge-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var fd = new FormData();
    fd.append('action', 'create');
    fd.append('ch_id', document.getElementById('ch_id').value);
    fd.append('cc_title', document.getElementById('cc_title').value);
    fd.append('cc_content', document.getElementById('cc_content').value);
    fd.append('cc_type', document.getElementById('cc_type').value);
    fd.append('cc_max_members', document.getElementById('cc_max_members').value);
    fd.append('cc_match_mode', document.getElementById('cc_match_mode').value);
    fd.append('cc_deadline', document.getElementById('cc_deadline').value);

    fetch('<?php echo $api_url; ?>', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            alert(data.message);
            if (data.success) {
                location.href = '<?php echo G5_BBS_URL; ?>/concierge_view.php?cc_id=' + data.cc_id;
            }
        });
});
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
