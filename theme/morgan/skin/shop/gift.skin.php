<?php
/**
 * Morgan Edition - 선물함 스킨
 */

if (!defined('_GNUBOARD_')) exit;

// 상태명
$status_names = array(
    'pending' => '대기 중',
    'accepted' => '수락됨',
    'rejected' => '거절됨'
);

$status_colors = array(
    'pending' => 'text-mg-warning',
    'accepted' => 'text-mg-success',
    'rejected' => 'text-mg-error'
);
?>

<div class="mg-inner">
    <!-- 상단: 제목 -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-mg-text-primary flex items-center gap-2">
            <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
            선물함
            <?php if ($pending_count > 0) { ?>
            <span class="ml-2 px-2 py-0.5 bg-mg-error text-white text-sm font-bold rounded-full"><?php echo $pending_count; ?></span>
            <?php } ?>
        </h1>
        <div class="flex gap-2">
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                인벤토리
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                상점
            </a>
        </div>
    </div>

    <!-- 탭 -->
    <div class="mb-6 flex gap-2">
        <a href="<?php echo G5_BBS_URL; ?>/gift.php?tab=pending" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo $tab == 'pending' ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
            받은 선물
            <?php if ($pending_count > 0) { ?>
            <span class="ml-1 px-1.5 py-0.5 bg-white/20 rounded text-xs"><?php echo $pending_count; ?></span>
            <?php } ?>
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/gift.php?tab=sent" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo $tab == 'sent' ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
            보낸 선물
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/gift.php?tab=received" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo $tab == 'received' ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
            선물 내역
        </a>
    </div>

    <!-- 대기 중인 선물 -->
    <?php if ($tab == 'pending') { ?>
        <?php if (count($pending_gifts) > 0) { ?>
        <div class="space-y-4">
            <?php foreach ($pending_gifts as $gift) { ?>
            <div class="card flex flex-col sm:flex-row gap-4" id="gift-<?php echo $gift['gf_id']; ?>" <?php if (isset($gift['gf_type']) && $gift['gf_type'] === 'inventory') { ?>data-gift-type="inventory"<?php } ?>>
                <!-- 이미지 -->
                <div class="w-full sm:w-24 flex-shrink-0">
                    <div class="aspect-square bg-mg-bg-tertiary rounded-lg overflow-hidden">
                        <?php if ($gift['si_image']) { ?>
                        <img src="<?php echo $gift['si_image']; ?>" alt="" class="w-full h-full object-cover">
                        <?php } else { ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- 정보 -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div>
                            <h3 class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($gift['si_name'] ?: '(삭제된 상품)'); ?></h3>
                            <p class="text-sm text-mg-text-muted">
                                <span class="text-mg-accent"><?php echo $gift['from_nick'] ?: $gift['mb_id_from']; ?></span>님이 보냄
                            </p>
                        </div>
                        <?php $is_inv_gift = (isset($gift['gf_type']) && $gift['gf_type'] === 'inventory'); ?>
                        <?php if ($is_inv_gift) { ?>
                        <span style="font-size:0.7rem;padding:0.15rem 0.4rem;background:var(--mg-bg-tertiary);border-radius:0.25rem;color:var(--mg-text-muted);">인벤토리</span>
                        <?php } else { ?>
                        <span class="text-mg-accent font-medium"><?php echo mg_point_format($gift['si_price']); ?></span>
                        <?php } ?>
                    </div>

                    <?php if ($gift['gf_message']) { ?>
                    <div class="bg-mg-bg-primary rounded-lg p-3 mb-3 text-sm text-mg-text-secondary">
                        "<?php echo nl2br(htmlspecialchars($gift['gf_message'])); ?>"
                    </div>
                    <?php } ?>

                    <div class="flex gap-2 text-sm">
                        <span class="text-mg-text-muted"><?php echo $gift['gf_datetime']; ?></span>
                    </div>
                </div>

                <!-- 버튼 -->
                <div class="flex sm:flex-col gap-2 flex-shrink-0">
                    <button type="button" onclick="acceptGift(<?php echo $gift['gf_id']; ?>)" class="flex-1 sm:flex-none bg-mg-accent hover:bg-mg-accent-hover text-white font-medium px-4 py-2 rounded-lg transition-colors">
                        수락
                    </button>
                    <button type="button" onclick="rejectGift(<?php echo $gift['gf_id']; ?>)" class="flex-1 sm:flex-none bg-mg-bg-tertiary hover:bg-mg-error hover:text-white text-mg-text-secondary font-medium px-4 py-2 rounded-lg transition-colors">
                        거절
                    </button>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="card py-16 text-center">
            <svg class="w-16 h-16 mx-auto text-mg-text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
            <p class="text-mg-text-muted">받은 선물이 없습니다.</p>
        </div>
        <?php } ?>
    <?php } ?>

    <!-- 보낸 선물 -->
    <?php if ($tab == 'sent') { ?>
        <?php if (count($sent_gifts) > 0) { ?>
        <div class="card overflow-x-auto">
            <table class="w-full text-sm" style="min-width:480px">
                <thead class="bg-mg-bg-primary">
                    <tr>
                        <th class="px-4 py-3 text-left text-mg-text-muted font-medium">상품</th>
                        <th class="px-4 py-3 text-left text-mg-text-muted font-medium">받는 사람</th>
                        <th class="px-4 py-3 text-center text-mg-text-muted font-medium">상태</th>
                        <th class="px-4 py-3 text-right text-mg-text-muted font-medium">일시</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-mg-bg-tertiary">
                    <?php foreach ($sent_gifts as $gift) { ?>
                    <tr>
                        <td class="px-4 py-3">
                            <span class="text-mg-text-primary"><?php echo htmlspecialchars($gift['si_name'] ?: '(삭제된 상품)'); ?></span>
                            <?php if (isset($gift['gf_type']) && $gift['gf_type'] === 'inventory') { ?>
                            <span style="font-size:0.65rem;padding:0.1rem 0.3rem;background:var(--mg-bg-tertiary);border-radius:0.2rem;color:var(--mg-text-muted);margin-left:0.25rem;">인벤토리</span>
                            <?php } ?>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-mg-accent"><?php echo $gift['to_nick'] ?: $gift['mb_id_to']; ?></span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="<?php echo $status_colors[$gift['gf_status']]; ?>"><?php echo $status_names[$gift['gf_status']]; ?></span>
                        </td>
                        <td class="px-4 py-3 text-right text-mg-text-muted">
                            <?php echo substr($gift['gf_datetime'], 0, 10); ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="card py-16 text-center">
            <p class="text-mg-text-muted">보낸 선물이 없습니다.</p>
        </div>
        <?php } ?>
    <?php } ?>

    <!-- 받은 선물 내역 -->
    <?php if ($tab == 'received') { ?>
        <?php if (count($received_gifts) > 0) { ?>
        <div class="card overflow-x-auto">
            <table class="w-full text-sm" style="min-width:400px">
                <thead class="bg-mg-bg-primary">
                    <tr>
                        <th class="px-4 py-3 text-left text-mg-text-muted font-medium">상품</th>
                        <th class="px-4 py-3 text-left text-mg-text-muted font-medium">보낸 사람</th>
                        <th class="px-4 py-3 text-right text-mg-text-muted font-medium">일시</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-mg-bg-tertiary">
                    <?php foreach ($received_gifts as $gift) { ?>
                    <tr>
                        <td class="px-4 py-3">
                            <span class="text-mg-text-primary"><?php echo htmlspecialchars($gift['si_name'] ?: '(삭제된 상품)'); ?></span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-mg-accent"><?php echo $gift['from_nick'] ?: $gift['mb_id_from']; ?></span>
                        </td>
                        <td class="px-4 py-3 text-right text-mg-text-muted">
                            <?php echo substr($gift['gf_datetime'], 0, 10); ?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } else { ?>
        <div class="card py-16 text-center">
            <p class="text-mg-text-muted">받은 선물 내역이 없습니다.</p>
        </div>
        <?php } ?>
    <?php } ?>
</div>

<style>
.aspect-square { aspect-ratio: 1/1; }

@media (min-width: 640px) {
    .sm\:flex-row { flex-direction: row; }
    .sm\:w-24 { width: 6rem; }
    .sm\:flex-col { flex-direction: column; }
    .sm\:flex-none { flex: none; }
}
</style>

<script>
function acceptGift(gf_id) {
    fetch('<?php echo G5_BBS_URL; ?>/gift_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=accept&gf_id=' + gf_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('gift-' + gf_id).remove();
            // 선물 카운트 업데이트
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('오류가 발생했습니다.');
        console.error(error);
    });
}

function rejectGift(gf_id) {
    var giftEl = document.getElementById('gift-' + gf_id);
    var isInv = giftEl && giftEl.querySelector('[data-gift-type="inventory"]');
    var msg = isInv ? '이 선물을 거절하시겠습니까?\n거절 시 아이템이 보낸 사람에게 반환됩니다.' : '이 선물을 거절하시겠습니까?\n거절 시 보낸 사람에게 포인트가 환불됩니다.';
    if (!confirm(msg)) {
        return;
    }

    fetch('<?php echo G5_BBS_URL; ?>/gift_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=reject&gf_id=' + gf_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById('gift-' + gf_id).remove();
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert('오류가 발생했습니다.');
        console.error(error);
    });
}
</script>
