<?php
/**
 * Morgan Edition - 인벤토리 스킨
 */

if (!defined('_GNUBOARD_')) exit;

// 상품 타입명
$item_type_names = array(
    'title' => '칭호',
    'badge' => '뱃지',
    'nick_color' => '닉네임 색상',
    'nick_effect' => '닉네임 효과',
    'profile_border' => '프로필 테두리',
    'equip' => '장비',
    'emoticon_set' => '이모티콘',
    'furniture' => '가구',
    'etc' => '기타'
);
?>

<div class="max-w-6xl mx-auto">
    <!-- 상단: 제목 -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-mg-text-primary flex items-center gap-2">
            <svg class="w-6 h-6 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            인벤토리
        </h1>
        <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            상점으로
        </a>
    </div>

    <!-- 카테고리 탭 -->
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-2 min-w-max">
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo (!$is_emoticon_tab && $sc_id == 0) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                전체
            </a>
            <?php foreach ($categories as $cat) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php?sc_id=<?php echo $cat['sc_id']; ?>" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo (!$is_emoticon_tab && $sc_id == $cat['sc_id']) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <?php echo htmlspecialchars($cat['sc_name']); ?>
            </a>
            <?php } ?>
            <?php if ($emoticon_use == '1') { ?>
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php?tab=emoticon" class="px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-1 <?php echo $is_emoticon_tab ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/></svg>
                이모티콘
            </a>
            <?php } ?>
        </div>
    </div>

    <?php if ($is_emoticon_tab) { ?>
    <!-- ========== 이모티콘 탭 콘텐츠 ========== -->

    <!-- 안내 -->
    <div class="card mb-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-mg-accent flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-mg-text-secondary space-y-1">
                <p>게시글, 댓글 작성 시 보유한 이모티콘을 사용할 수 있습니다.</p>
                <p>이모티콘은 <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=emoticon" class="text-mg-accent hover:underline">상점 &gt; 이모티콘</a> 탭에서 구매할 수 있습니다.</p>
                <?php if ($creator_enabled) { ?>
                <p>직접 이모티콘을 제작하여 다른 유저에게 판매할 수도 있습니다. <strong>이모티콘 등록권</strong>을 구매한 후 셋을 만들어 심사를 요청하세요.</p>
                <p class="text-xs text-mg-text-muted">문제가 되는 이모티콘은 관리자가 반려할 수 있습니다. 판매 수수료: <?php echo (int)mg_config('emoticon_commission_rate', 10); ?>%</p>
                <?php } ?>
            </div>
        </div>
    </div>

    <!-- 보유 이모티콘 셋 -->
    <div class="card mb-4">
        <div class="card-header">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            보유 이모티콘 <span class="text-mg-accent ml-1"><?php echo count($my_emoticon_sets); ?></span>
        </div>
        <?php if (!empty($my_emoticon_sets)) { ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($my_emoticon_sets as $set) { ?>
            <div class="flex items-center gap-3 p-3 bg-mg-bg-primary rounded-lg">
                <?php if ($set['es_preview']) { ?>
                <img src="<?php echo htmlspecialchars($set['es_preview']); ?>" alt="" class="w-12 h-12 object-contain flex-shrink-0">
                <?php } else { ?>
                <div class="w-12 h-12 bg-mg-bg-tertiary rounded flex items-center justify-center text-mg-text-muted flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="1.5"/><path stroke-linecap="round" stroke-width="1.5" d="M8 14s1.5 2 4 2 4-2 4-2"/><circle cx="9" cy="10" r="1" fill="currentColor" stroke="none"/><circle cx="15" cy="10" r="1" fill="currentColor" stroke="none"/></svg>
                </div>
                <?php } ?>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($set['es_name']); ?></p>
                    <p class="text-xs text-mg-text-muted"><?php echo (int)$set['em_count']; ?>개</p>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } else { ?>
        <div class="p-8 text-center text-mg-text-muted">
            <p class="mb-2">보유한 이모티콘 셋이 없습니다.</p>
            <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=emoticon" class="text-mg-accent hover:underline text-sm">상점에서 구매하기</a>
        </div>
        <?php } ?>
    </div>

    <!-- 크리에이터 섹션 -->
    <?php if ($creator_enabled) { ?>
    <div class="card">
        <div class="card-header">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            내가 만든 이모티콘
        </div>

        <div class="space-y-4">
            <!-- 등록권 상태 -->
            <div class="flex items-center justify-between p-3 bg-mg-bg-primary rounded-lg">
                <div>
                    <span class="text-sm text-mg-text-secondary">이모티콘 등록권</span>
                    <span class="ml-2 font-bold text-mg-accent"><?php echo $reg_check['count']; ?>개</span>
                </div>
                <?php if ($reg_check['can']) { ?>
                <a href="<?php echo G5_BBS_URL; ?>/emoticon_create.php" class="btn btn-primary text-sm">새 이모티콘 만들기</a>
                <?php } else { ?>
                <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="btn btn-secondary text-sm">등록권 구매하기</a>
                <?php } ?>
            </div>

            <!-- 제작 목록 -->
            <?php if (!empty($creator_sets)) { ?>
            <?php
            $status_labels = array(
                'draft' => array('text' => '작성중', 'class' => 'bg-mg-bg-tertiary text-mg-text-muted'),
                'pending' => array('text' => '심사중', 'class' => 'bg-mg-warning/20 text-mg-warning'),
                'approved' => array('text' => '승인', 'class' => 'bg-mg-success/20 text-mg-success'),
                'rejected' => array('text' => '반려', 'class' => 'bg-mg-error/20 text-mg-error'),
            );
            ?>
            <div class="space-y-2">
                <?php foreach ($creator_sets as $cset) {
                    $st = isset($status_labels[$cset['es_status']]) ? $status_labels[$cset['es_status']] : array('text' => $cset['es_status'], 'class' => '');
                ?>
                <div class="flex items-center justify-between p-3 bg-mg-bg-primary rounded-lg">
                    <div class="flex items-center gap-3 min-w-0">
                        <?php if ($cset['es_preview']) { ?>
                        <img src="<?php echo htmlspecialchars($cset['es_preview']); ?>" alt="" class="w-10 h-10 object-contain flex-shrink-0">
                        <?php } else { ?>
                        <div class="w-10 h-10 bg-mg-bg-tertiary rounded flex items-center justify-center text-mg-text-muted flex-shrink-0 text-sm">?</div>
                        <?php } ?>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-mg-text-primary truncate"><?php echo htmlspecialchars($cset['es_name']); ?></p>
                            <p class="text-xs text-mg-text-muted">
                                <?php echo (int)$cset['em_count']; ?>개 |
                                <?php echo number_format((int)$cset['es_price']); ?>P |
                                판매 <?php echo (int)$cset['es_sales_count']; ?>개
                                <?php if ($cset['es_status'] === 'approved') { ?>
                                | <?php echo $cset['es_use'] ? '판매중' : '판매중지'; ?>
                                <?php } ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo $st['class']; ?>"><?php echo $st['text']; ?></span>
                        <?php if ($cset['es_status'] === 'approved') { ?>
                        <button type="button" onclick="toggleSale(<?php echo $cset['es_id']; ?>, <?php echo $cset['es_use'] ? 0 : 1; ?>)" class="text-xs px-2 py-0.5 rounded <?php echo $cset['es_use'] ? 'bg-mg-bg-tertiary text-mg-text-muted hover:text-mg-error' : 'bg-mg-accent/20 text-mg-accent'; ?>">
                            <?php echo $cset['es_use'] ? '판매중지' : '판매하기'; ?>
                        </button>
                        <?php } ?>
                        <?php if (in_array($cset['es_status'], array('draft', 'rejected'))) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/emoticon_create.php?es_id=<?php echo $cset['es_id']; ?>" class="text-xs text-mg-accent hover:underline">수정</a>
                        <?php } ?>
                    </div>
                </div>
                <?php if ($cset['es_status'] === 'rejected' && $cset['es_reject_reason']) { ?>
                <div class="ml-13 px-3 py-2 bg-mg-error/10 rounded text-xs text-mg-error">
                    반려 사유: <?php echo htmlspecialchars($cset['es_reject_reason']); ?>
                </div>
                <?php } ?>
                <?php } ?>
            </div>
            <?php } else { ?>
            <p class="text-center text-mg-text-muted text-sm py-4">아직 만든 이모티콘 셋이 없습니다.</p>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <?php } else { ?>
    <!-- ========== 일반 인벤토리 콘텐츠 ========== -->

    <!-- 사용 중인 아이템 요약 -->
    <?php if (count($active_items) > 0) { ?>
    <div class="card mb-6">
        <h2 class="text-sm font-medium text-mg-text-muted mb-3">현재 사용 중</h2>
        <div class="flex flex-wrap gap-2">
            <?php foreach ($active_items as $active) {
                $active_item = mg_get_shop_item($active['si_id']);
                if (!$active_item) continue;
            ?>
            <div class="flex items-center gap-2 bg-mg-bg-primary rounded-lg px-3 py-1.5">
                <span class="text-xs text-mg-accent"><?php echo $item_type_names[$active_item['si_type']]; ?></span>
                <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($active_item['si_name']); ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- 인벤토리 그리드 -->
    <?php if (count($inventory) > 0) { ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($inventory as $inv) {
            $item = $inv['item'];
            $is_active = in_array($item['si_id'], $active_si_ids);
            $is_usable = in_array($item['si_type'], ['title', 'badge', 'nick_color', 'nick_effect', 'profile_border']);
        ?>
        <div class="card p-0 overflow-hidden <?php echo $is_active ? 'ring-2 ring-mg-accent' : ''; ?>">
            <!-- 이미지 -->
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden">
                <?php if ($item['si_image']) { ?>
                <img src="<?php echo $item['si_image']; ?>" alt="" class="w-full h-full object-cover">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <?php } ?>

                <!-- 수량 배지 -->
                <?php if ($inv['iv_count'] > 1) { ?>
                <span class="absolute top-2 right-2 px-2 py-0.5 bg-mg-bg-primary/90 text-xs text-mg-accent font-bold rounded">
                    x<?php echo $inv['iv_count']; ?>
                </span>
                <?php } ?>

                <!-- 사용 중 표시 -->
                <?php if ($is_active) { ?>
                <div class="absolute top-2 left-2 px-2 py-0.5 bg-mg-accent text-white text-xs font-bold rounded">
                    사용 중
                </div>
                <?php } ?>

                <!-- 타입 배지 -->
                <span class="absolute bottom-2 left-2 px-2 py-0.5 bg-mg-bg-primary/80 text-xs text-mg-text-muted rounded">
                    <?php echo $item_type_names[$item['si_type']] ?? $item['si_type']; ?>
                </span>
            </div>

            <!-- 정보 -->
            <div class="p-3">
                <h3 class="font-medium text-mg-text-primary truncate mb-2"><?php echo htmlspecialchars($item['si_name']); ?></h3>

                <!-- 버튼 -->
                <?php if ($is_usable) { ?>
                    <?php if ($is_active) { ?>
                    <button type="button" onclick="unuseItem(<?php echo $item['si_id']; ?>)" class="w-full bg-mg-bg-tertiary text-mg-text-secondary text-sm py-2 rounded-lg hover:bg-mg-bg-primary transition-colors">
                        해제
                    </button>
                    <?php } else { ?>
                    <button type="button" onclick="useItem(<?php echo $item['si_id']; ?>)" class="w-full bg-mg-accent text-white text-sm font-medium py-2 rounded-lg hover:bg-mg-accent-hover transition-colors">
                        사용
                    </button>
                    <?php } ?>
                <?php } else { ?>
                <div class="text-xs text-mg-text-muted text-center py-2">
                    사용 불가
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php } else { ?>
    <!-- 아이템 없음 -->
    <div class="card py-16 text-center">
        <svg class="w-16 h-16 mx-auto text-mg-text-muted mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
        </svg>
        <p class="text-mg-text-muted mb-4">
            <?php echo $sc_id ? '해당 카테고리에 보유한 아이템이 없습니다.' : '보유한 아이템이 없습니다.'; ?>
        </p>
        <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="inline-flex items-center gap-1 text-mg-accent hover:underline">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            상점에서 구매하기
        </a>
    </div>
    <?php } ?>
    <?php } ?>

    <!-- 하단 링크 -->
    <div class="mt-6 flex gap-4 justify-center">
        <a href="<?php echo G5_BBS_URL; ?>/gift.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
            선물함
        </a>
    </div>
</div>

<style>
.aspect-square { aspect-ratio: 1/1; }

.grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
@media (min-width: 640px) {
    .sm\:grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
@media (min-width: 1024px) {
    .lg\:grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
}
</style>

<script>
function toggleSale(esId, newUse) {
    var action = newUse ? '판매를 시작하시겠습니까?' : '판매를 중지하시겠습니까?';
    if (!confirm(action)) return;

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/emoticon_create_update.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            location.reload();
        } else {
            alert('오류가 발생했습니다.');
        }
    };
    xhr.send('action=toggle_sale&es_id=' + esId + '&es_use=' + newUse);
}

function useItem(si_id) {
    fetch('<?php echo G5_BBS_URL; ?>/inventory_use.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=use&si_id=' + si_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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

function unuseItem(si_id) {
    fetch('<?php echo G5_BBS_URL; ?>/inventory_use.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=unuse&si_id=' + si_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
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
