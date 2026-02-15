<?php
/**
 * Morgan Edition - 의뢰 상세
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert_close('로그인이 필요합니다.');
}

$cc_id = isset($_GET['cc_id']) ? (int)$_GET['cc_id'] : 0;
$cc = mg_get_concierge($cc_id);
if (!$cc) {
    alert_close('의뢰를 찾을 수 없습니다.');
}

$is_owner = ($cc['mb_id'] === $member['mb_id']);
$my_apply = null;
foreach ($cc['applies'] as $a) {
    if ($a['mb_id'] === $member['mb_id']) {
        $my_apply = $a;
        break;
    }
}

$type_labels = array('collaboration' => '합작', 'illustration' => '일러스트', 'novel' => '소설', 'other' => '기타');
$type_label = isset($type_labels[$cc['cc_type']]) ? $type_labels[$cc['cc_type']] : $cc['cc_type'];

$api_url = G5_BBS_URL . '/concierge_api.php';

$g5['title'] = $cc['cc_title'] . ' - 의뢰';
include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 뒤로 -->
    <div class="mb-4">
        <a href="<?php echo G5_BBS_URL; ?>/concierge.php" class="text-sm text-mg-text-secondary hover:text-mg-accent inline-flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            목록으로
        </a>
    </div>

    <!-- 의뢰 정보 -->
    <div class="card mb-4">
        <div class="flex items-start gap-3 mb-4">
            <?php if ($cc['ch_thumb']) { ?>
            <img src="<?php echo htmlspecialchars($cc['ch_thumb']); ?>" class="w-12 h-12 rounded-full object-cover">
            <?php } else { ?>
            <div class="w-12 h-12 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted">?</div>
            <?php } ?>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <?php if ($cc['cc_tier'] === 'urgent') { ?>
                    <span class="px-1.5 py-0.5 text-xs font-bold rounded bg-mg-accent/20 text-mg-accent">긴급</span>
                    <?php } ?>
                    <h1 class="text-xl font-bold text-mg-text-primary"><?php echo htmlspecialchars($cc['cc_title']); ?></h1>
                </div>
                <div class="flex items-center gap-3 text-sm text-mg-text-muted">
                    <span><?php echo htmlspecialchars($cc['ch_name']); ?> (<?php echo htmlspecialchars($cc['mb_nick']); ?>)</span>
                    <span><?php echo $type_label; ?></span>
                    <span><?php echo $cc['cc_match_mode'] === 'lottery' ? '추첨' : '직접 선택'; ?></span>
                </div>
            </div>
        </div>

        <div class="text-mg-text-primary leading-relaxed mb-4 whitespace-pre-wrap"><?php echo htmlspecialchars($cc['cc_content']); ?></div>

        <div class="flex flex-wrap gap-4 text-sm text-mg-text-secondary border-t border-mg-bg-tertiary pt-3">
            <span>모집 인원: <?php echo $cc['cc_max_members']; ?>명</span>
            <span>마감: <?php echo substr($cc['cc_deadline'], 0, 16); ?></span>
            <span>등록일: <?php echo substr($cc['cc_datetime'], 0, 16); ?></span>
            <span>
                상태:
                <?php
                switch ($cc['cc_status']) {
                    case 'recruiting': echo '<span class="text-mg-accent font-medium">모집중</span>'; break;
                    case 'matched': echo '<span class="text-yellow-400 font-medium">진행중</span>'; break;
                    case 'completed': echo '<span class="text-mg-success font-medium">완료</span>'; break;
                    case 'expired': echo '<span class="text-mg-text-muted">만료</span>'; break;
                    case 'cancelled': echo '<span class="text-mg-text-muted">취소</span>'; break;
                }
                ?>
            </span>
        </div>

        <!-- 의뢰자 액션 -->
        <?php if ($is_owner && $cc['cc_status'] === 'recruiting') { ?>
        <div class="flex gap-2 mt-4 pt-3 border-t border-mg-bg-tertiary">
            <?php if ($cc['cc_match_mode'] === 'lottery') { ?>
            <button onclick="doLottery()" class="px-4 py-2 bg-mg-accent text-mg-bg-primary rounded-lg text-sm font-medium hover:bg-mg-accent-hover transition-colors">추첨 실행</button>
            <?php } ?>
            <button onclick="doCancel()" class="px-4 py-2 border border-mg-bg-tertiary text-mg-text-secondary rounded-lg text-sm hover:bg-mg-bg-tertiary transition-colors">의뢰 취소</button>
        </div>
        <?php } ?>
    </div>

    <!-- 지원자 목록 (의뢰자에게만 또는 매칭 후) -->
    <?php if ($is_owner || $cc['cc_status'] !== 'recruiting') { ?>
    <div class="card mb-4">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">
            지원자 (<?php echo count($cc['applies']); ?>/<?php echo $cc['cc_max_members']; ?>명)
        </h2>
        <?php if (empty($cc['applies'])) { ?>
        <p class="text-sm text-mg-text-muted text-center py-4">아직 지원자가 없습니다.</p>
        <?php } else { ?>
        <div class="space-y-2">
            <?php foreach ($cc['applies'] as $a) {
                $a_status_class = '';
                $a_status_text = '';
                switch ($a['ca_status']) {
                    case 'pending': $a_status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $a_status_text = '대기'; break;
                    case 'selected': $a_status_class = 'bg-mg-success/20 text-mg-success'; $a_status_text = '선정'; break;
                    case 'rejected': $a_status_class = 'bg-mg-bg-tertiary text-mg-text-muted'; $a_status_text = '미선정'; break;
                }
            ?>
            <div class="flex items-center gap-3 p-3 bg-mg-bg-primary rounded-lg">
                <?php if ($a['ch_thumb']) { ?>
                <img src="<?php echo htmlspecialchars($a['ch_thumb']); ?>" class="w-8 h-8 rounded-full object-cover">
                <?php } else { ?>
                <div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-mg-text-primary"><?php echo htmlspecialchars($a['ch_name']); ?></span>
                        <span class="text-xs text-mg-text-muted">(<?php echo htmlspecialchars($a['mb_nick']); ?>)</span>
                        <span class="px-2 py-0.5 text-xs rounded <?php echo $a_status_class; ?>"><?php echo $a_status_text; ?></span>
                        <?php if ($a['ca_has_boost']) { ?>
                        <span class="text-xs text-mg-accent">확률UP</span>
                        <?php } ?>
                    </div>
                    <?php if ($a['ca_message']) { ?>
                    <div class="text-xs text-mg-text-secondary mt-1"><?php echo htmlspecialchars($a['ca_message']); ?></div>
                    <?php } ?>
                </div>
                <?php if ($is_owner && $cc['cc_status'] === 'recruiting' && $cc['cc_match_mode'] === 'direct' && $a['ca_status'] === 'pending') { ?>
                <label class="flex items-center gap-1 cursor-pointer">
                    <input type="checkbox" class="match-checkbox" value="<?php echo $a['ca_id']; ?>">
                    <span class="text-xs text-mg-text-muted">선택</span>
                </label>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <?php if ($is_owner && $cc['cc_status'] === 'recruiting' && $cc['cc_match_mode'] === 'direct') { ?>
        <button onclick="doMatch()" class="mt-3 px-4 py-2 bg-mg-accent text-mg-bg-primary rounded-lg text-sm font-medium hover:bg-mg-accent-hover transition-colors">선택한 지원자 매칭</button>
        <?php } ?>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 지원 폼 (모집중 + 본인 아닌 + 미지원) -->
    <?php if ($cc['cc_status'] === 'recruiting' && !$is_owner && !$my_apply) { ?>
    <div class="card mb-4" id="apply-section">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">지원하기</h2>
        <div class="space-y-3">
            <div>
                <label class="block text-sm text-mg-text-secondary mb-1">캐릭터 선택</label>
                <select id="apply-ch-id" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg">
                    <option value="">캐릭터를 선택하세요</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-mg-text-secondary mb-1">지원 메시지 (선택)</label>
                <textarea id="apply-message" class="w-full px-3 py-2 bg-mg-bg-tertiary border border-mg-bg-tertiary text-mg-text-primary rounded-lg" rows="2" placeholder="간단한 소개나 의견을 남겨주세요"></textarea>
            </div>
            <button onclick="doApply()" class="px-4 py-2 bg-mg-accent text-mg-bg-primary rounded-lg text-sm font-medium hover:bg-mg-accent-hover transition-colors">지원하기</button>
        </div>
    </div>
    <?php } elseif ($my_apply) { ?>
    <div class="card mb-4">
        <p class="text-sm text-mg-text-secondary">
            이미 지원했습니다.
            상태: <span class="font-medium <?php echo $my_apply['ca_status'] === 'selected' ? 'text-mg-success' : ($my_apply['ca_status'] === 'rejected' ? 'text-mg-text-muted' : 'text-mg-accent'); ?>">
                <?php echo $my_apply['ca_status'] === 'selected' ? '선정됨' : ($my_apply['ca_status'] === 'rejected' ? '미선정' : '대기중'); ?>
            </span>
        </p>
    </div>
    <?php } ?>

    <!-- 결과물 -->
    <?php if (!empty($cc['results'])) { ?>
    <div class="card">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">결과물</h2>
        <div class="space-y-2">
            <?php foreach ($cc['results'] as $r) { ?>
            <div class="flex items-center gap-3 p-2 bg-mg-bg-primary rounded-lg text-sm">
                <span class="text-mg-text-primary"><?php echo htmlspecialchars($r['performer_nick'] ?? ''); ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo urlencode($r['bo_table']); ?>&wr_id=<?php echo $r['wr_id']; ?>" class="text-mg-accent hover:underline">게시글 보기</a>
                <span class="text-xs text-mg-text-muted"><?php echo substr($r['cr_datetime'], 0, 16); ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>

<script>
var API = '<?php echo $api_url; ?>';
var CC_ID = <?php echo $cc_id; ?>;

<?php if ($cc['cc_status'] === 'recruiting' && !$is_owner && !$my_apply) { ?>
// 캐릭터 목록 로드
fetch(API + '?action=my_characters', { credentials: 'same-origin' })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;
        var sel = document.getElementById('apply-ch-id');
        data.characters.forEach(function(ch) {
            var opt = document.createElement('option');
            opt.value = ch.ch_id;
            opt.textContent = ch.ch_name;
            sel.appendChild(opt);
        });
    });
<?php } ?>

function doApply() {
    var ch_id = document.getElementById('apply-ch-id').value;
    var message = document.getElementById('apply-message').value;
    if (!ch_id) { alert('캐릭터를 선택해주세요.'); return; }

    var fd = new FormData();
    fd.append('action', 'apply');
    fd.append('cc_id', CC_ID);
    fd.append('ch_id', ch_id);
    fd.append('ca_message', message);

    fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) { alert(data.message); if (data.success) location.reload(); });
}

function doMatch() {
    var checked = document.querySelectorAll('.match-checkbox:checked');
    if (checked.length === 0) { alert('지원자를 선택해주세요.'); return; }

    var ids = [];
    checked.forEach(function(c) { ids.push(parseInt(c.value)); });

    if (!confirm(ids.length + '명을 선택합니다. 매칭하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('action', 'match');
    fd.append('cc_id', CC_ID);
    fd.append('selected_ca_ids', JSON.stringify(ids));

    fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) { alert(data.message); if (data.success) location.reload(); });
}

function doLottery() {
    if (!confirm('추첨을 실행하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('action', 'lottery');
    fd.append('cc_id', CC_ID);

    fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) { alert(data.message); if (data.success) location.reload(); });
}

function doCancel() {
    if (!confirm('의뢰를 취소하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('action', 'cancel');
    fd.append('cc_id', CC_ID);

    fetch(API, { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function(r) { return r.json(); })
        .then(function(data) { alert(data.message); if (data.success) location.href = '<?php echo G5_BBS_URL; ?>/concierge.php'; });
}
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
