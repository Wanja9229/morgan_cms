<?php
/**
 * Morgan Edition - 상품 상세 스킨
 */

if (!defined('_GNUBOARD_')) exit;

// 상품 타입명
$item_type_names = array(
    'title' => '칭호',
    'badge' => '뱃지',
    'nick_color' => '닉네임 색상',
    'nick_effect' => '닉네임 효과',
    'profile_border' => '프로필 테두리',
    'profile_skin' => '프로필 스킨',
    'profile_bg' => '프로필 배경',
    'equip' => '장비',
    'emoticon_set' => '이모티콘',
    'emoticon_reg' => '이모티콘 등록권',
    'furniture' => '가구',
    'material' => '재료',
    'seal_bg' => '인장 배경',
    'seal_frame' => '인장 프레임',
    'etc' => '기타'
);

// 효과 데이터 (mg_get_shop_item()에서 이미 디코딩됨)
$effect = is_array($item['si_effect']) ? $item['si_effect'] : (json_decode($item['si_effect'], true) ?: array());

// 재고 표시
$stock_text = '무제한';
if ($item['si_stock'] > 0) {
    $remain = $item['si_stock'] - $item['si_stock_sold'];
    $stock_text = "{$remain}개 남음";
}
?>

<div class="mg-inner">
    <!-- 뒤로가기 -->
    <div class="mb-4">
        <a href="<?php echo G5_BBS_URL; ?>/shop.php<?php echo $item['sc_id'] ? '?sc_id='.$item['sc_id'] : ''; ?>" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            상점으로 돌아가기
        </a>
    </div>

    <div class="card">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- 이미지 -->
            <div class="md:w-1/3">
                <div class="aspect-square bg-mg-bg-tertiary rounded-lg overflow-hidden relative">
                    <?php if ($item['si_image']) { ?>
                    <img src="<?php echo $item['si_image']; ?>" alt="<?php echo htmlspecialchars($item['si_name']); ?>" class="w-full h-full object-cover">
                    <?php } else { ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-20 h-20 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <?php } ?>

                    <!-- 상태 오버레이 -->
                    <?php if ($status != 'selling') { ?>
                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                        <?php if ($status == 'sold_out') { ?>
                        <span class="px-4 py-2 bg-mg-error text-white text-lg font-bold rounded">SOLD OUT</span>
                        <?php } elseif ($status == 'coming_soon') { ?>
                        <span class="px-4 py-2 bg-mg-accent text-white text-lg font-bold rounded">COMING SOON</span>
                        <?php } elseif ($status == 'ended') { ?>
                        <span class="px-4 py-2 bg-mg-bg-tertiary text-mg-text-muted text-lg font-bold rounded">판매 종료</span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- 상품 정보 -->
            <div class="md:w-2/3">
                <!-- 카테고리/타입 -->
                <div class="flex gap-2 mb-2">
                    <?php if ($category) { ?>
                    <span class="text-xs px-2 py-1 bg-mg-bg-tertiary text-mg-text-muted rounded"><?php echo htmlspecialchars($category['sc_name']); ?></span>
                    <?php } ?>
                    <span class="text-xs px-2 py-1 bg-mg-accent/20 text-mg-accent rounded"><?php echo $item_type_names[$item['si_type']] ?? $item['si_type']; ?></span>
                </div>

                <!-- 상품명 -->
                <h1 class="text-2xl font-bold text-mg-text-primary mb-4"><?php echo htmlspecialchars($item['si_name']); ?></h1>

                <!-- 가격 -->
                <div class="text-3xl font-bold text-mg-accent mb-4">
                    <?php echo mg_point_format($item['si_price']); ?>
                </div>

                <!-- 정보 테이블 -->
                <div class="bg-mg-bg-primary rounded-lg p-4 mb-4">
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-mg-text-muted">재고</dt>
                            <dd class="text-mg-text-primary"><?php echo $stock_text; ?></dd>
                        </div>
                        <?php if ($item['si_limit_per_user'] > 0) { ?>
                        <div class="flex justify-between">
                            <dt class="text-mg-text-muted">구매 제한</dt>
                            <dd class="text-mg-text-primary">1인당 <?php echo $item['si_limit_per_user']; ?>개</dd>
                        </div>
                        <?php } ?>
                        <?php if ($my_count > 0) { ?>
                        <div class="flex justify-between">
                            <dt class="text-mg-text-muted">내 보유</dt>
                            <dd class="text-mg-accent font-medium"><?php echo $my_count; ?>개</dd>
                        </div>
                        <?php } ?>
                        <?php if ($item['si_consumable']) { ?>
                        <div class="flex justify-between">
                            <dt class="text-mg-text-muted">소모품</dt>
                            <dd class="text-mg-warning">사용 시 소모</dd>
                        </div>
                        <?php } ?>
                        <?php if ($item['si_sale_end']) { ?>
                        <div class="flex justify-between">
                            <dt class="text-mg-text-muted">판매 종료</dt>
                            <dd class="text-mg-error"><?php echo date('Y-m-d H:i', strtotime($item['si_sale_end'])); ?></dd>
                        </div>
                        <?php } ?>
                    </dl>
                </div>

                <!-- 내 포인트 -->
                <div class="flex items-center justify-between bg-mg-bg-primary rounded-lg p-4 mb-4">
                    <span class="text-mg-text-muted">내 <?php echo mg_point_name(); ?></span>
                    <span class="text-xl font-bold <?php echo $my_point >= $item['si_price'] ? 'text-mg-accent' : 'text-mg-error'; ?>">
                        <?php echo mg_point_format($my_point); ?>
                    </span>
                </div>

                <!-- 버튼 -->
                <div class="flex gap-3">
                    <?php if ($status == 'selling') { ?>
                        <?php if ($can_buy === true) { ?>
                        <button type="button" onclick="buyItem(<?php echo $item['si_id']; ?>)" class="flex-1 bg-mg-accent hover:bg-mg-accent-hover text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            구매하기
                        </button>
                        <?php } else { ?>
                        <button type="button" disabled class="flex-1 bg-mg-bg-tertiary text-mg-text-muted font-bold py-3 px-6 rounded-lg cursor-not-allowed">
                            <?php echo $can_buy; ?>
                        </button>
                        <?php } ?>

                        <?php if ($gift_use == '1' && $can_buy === true) { ?>
                        <button type="button" onclick="openGiftModal(<?php echo $item['si_id']; ?>)" class="bg-mg-warning hover:opacity-80 text-white font-bold py-3 px-6 rounded-lg transition-opacity">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                            </svg>
                        </button>
                        <?php } ?>
                    <?php } else { ?>
                    <button type="button" disabled class="flex-1 bg-mg-bg-tertiary text-mg-text-muted font-bold py-3 px-6 rounded-lg cursor-not-allowed">
                        <?php
                        if ($status == 'sold_out') echo '품절';
                        elseif ($status == 'coming_soon') echo '판매 예정';
                        else echo '판매 종료';
                        ?>
                    </button>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- 설명 -->
        <?php if ($item['si_desc']) { ?>
        <div class="mt-6 pt-6 border-t border-mg-bg-tertiary">
            <h2 class="font-bold text-mg-text-primary mb-3">상품 설명</h2>
            <div class="text-mg-text-secondary whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($item['si_desc'])); ?></div>
        </div>
        <?php } ?>

        <!-- 효과 미리보기 -->
        <?php if (!empty($effect)) { ?>
        <div class="mt-6 pt-6 border-t border-mg-bg-tertiary">
            <h2 class="font-bold text-mg-text-primary mb-3">효과 미리보기</h2>
            <div class="bg-mg-bg-primary rounded-lg p-4">
                <?php if ($item['si_type'] == 'title' && !empty($effect['title'])) { ?>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 text-sm rounded" style="background:<?php echo $effect['title_color'] ?? '#5865f2'; ?>20;color:<?php echo $effect['title_color'] ?? '#5865f2'; ?>">
                        <?php echo htmlspecialchars($effect['title']); ?>
                    </span>
                    <span class="text-mg-text-primary"><?php echo $member['mb_nick']; ?></span>
                </div>
                <?php } elseif ($item['si_type'] == 'nick_color' && !empty($effect['nick_color'])) { ?>
                <span style="color:<?php echo $effect['nick_color']; ?>;font-weight:bold;"><?php echo $member['mb_nick']; ?></span>
                <?php } elseif ($item['si_type'] == 'nick_effect' && !empty($effect['nick_effect'])) { ?>
                <span class="nick-effect-<?php echo $effect['nick_effect']; ?>"><?php echo $member['mb_nick']; ?></span>
                <?php } elseif ($item['si_type'] == 'badge' && !empty($effect['badge_icon'])) {
                    $badge_icon = $effect['badge_icon'];
                    $badge_is_image = (strpos($badge_icon, '/') !== false || strpos($badge_icon, 'http') === 0);
                ?>
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg" style="background:<?php echo $effect['badge_color'] ?? '#fbbf24'; ?>20;">
                    <?php if ($badge_is_image) { ?>
                    <img src="<?php echo htmlspecialchars($badge_icon); ?>" alt="" class="w-6 h-6 object-contain">
                    <?php } else { ?>
                    <span style="color:<?php echo $effect['badge_color'] ?? '#fbbf24'; ?>">
                        <?php echo mg_heroicon($badge_icon, 'w-5 h-5'); ?>
                    </span>
                    <?php } ?>
                    <span class="text-sm font-medium text-mg-text-primary"><?php echo htmlspecialchars($item['si_name']); ?></span>
                </div>
                <?php } else { ?>
                <span class="text-mg-text-muted">효과 미리보기가 제공되지 않는 상품입니다.</span>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!-- 선물 모달 -->
<div id="giftModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-mg-bg-secondary rounded-lg p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-bold text-mg-text-primary mb-4">선물 보내기</h3>
        <form id="giftForm" onsubmit="sendGift(event)">
            <input type="hidden" name="si_id" value="<?php echo $item['si_id']; ?>">
            <div class="mb-4">
                <label class="block text-sm text-mg-text-muted mb-1">받는 사람 (회원 ID)</label>
                <input type="text" name="mb_id_to" required class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2 text-mg-text-primary focus:border-mg-accent focus:outline-none" placeholder="회원 ID 입력">
            </div>
            <div class="mb-4">
                <label class="block text-sm text-mg-text-muted mb-1">메시지 (선택)</label>
                <textarea name="message" rows="3" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-4 py-2 text-mg-text-primary focus:border-mg-accent focus:outline-none resize-none" placeholder="선물과 함께 전할 메시지"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeGiftModal()" class="flex-1 bg-mg-bg-tertiary text-mg-text-secondary py-2 rounded-lg hover:bg-mg-bg-primary transition-colors">
                    취소
                </button>
                <button type="submit" class="flex-1 bg-mg-warning text-white font-bold py-2 rounded-lg hover:opacity-80 transition-opacity">
                    선물 보내기
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.aspect-square { aspect-ratio: 1/1; }

@media (min-width: 768px) {
    .md\:flex-row { flex-direction: row; }
    .md\:w-1\/3 { width: 33.333333%; }
    .md\:w-2\/3 { width: 66.666667%; }
}

/* 닉네임 효과 */
.nick-effect-glow {
    text-shadow: 0 0 10px currentColor, 0 0 20px currentColor;
}
.nick-effect-rainbow {
    background: linear-gradient(90deg, #ff0000, #ff7f00, #ffff00, #00ff00, #0000ff, #4b0082, #8b00ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    animation: rainbow 3s linear infinite;
    background-size: 200% 100%;
}
@keyframes rainbow {
    0% { background-position: 0% 50%; }
    100% { background-position: 200% 50%; }
}
.nick-effect-shake {
    animation: shake 0.5s ease-in-out infinite;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}
.nick-effect-gradient {
    background: linear-gradient(90deg, #5865f2, #eb459e);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>

<script>
function buyItem(si_id) {
    if (!confirm('이 상품을 구매하시겠습니까?\n\n가격: <?php echo mg_point_format($item['si_price']); ?>')) {
        return;
    }

    fetch('<?php echo G5_BBS_URL; ?>/shop_buy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'si_id=' + si_id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message + '\n\n남은 포인트: ' + data.new_point + 'P');
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

function openGiftModal() {
    document.getElementById('giftModal').classList.remove('hidden');
}

function closeGiftModal() {
    document.getElementById('giftModal').classList.add('hidden');
}

function sendGift(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    fetch('<?php echo G5_BBS_URL; ?>/shop_gift.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeGiftModal();
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

// ESC로 모달 닫기
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeGiftModal();
});
</script>
