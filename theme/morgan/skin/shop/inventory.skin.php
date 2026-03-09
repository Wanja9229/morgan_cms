<?php
/**
 * Morgan Edition - 인벤토리 스킨
 */

if (!defined('_GNUBOARD_')) exit;

// 상품 타입명 (morgan.php 단일 소스)
$item_type_names = $mg['shop_type_labels'];

// 관계 슬롯 확장권용: 내 캐릭터 목록 + 관계 슬롯 정보
$_relslot_chars = array();
if ($is_member) {
    $_rs_result = sql_query("SELECT ch_id, ch_name, ch_thumb FROM {$g5['mg_character_table']}
                             WHERE mb_id = '{$member['mb_id']}' AND ch_state = 'approved' ORDER BY ch_id");
    if ($_rs_result) {
        while ($_rs_row = sql_fetch_array($_rs_result)) {
            $_rs_row['rel_count'] = mg_get_relation_count($_rs_row['ch_id']);
            $_rs_row['rel_max'] = mg_get_max_relations($_rs_row['ch_id']);
            $_relslot_chars[] = $_rs_row;
        }
    }
}
?>

<div class="mg-inner">
    <!-- 상단: 제목 -->
    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
        <h1 class="text-2xl font-bold text-mg-text-primary flex items-center gap-2">
            <i data-lucide="box" class="w-6 h-6 text-mg-accent"></i>
            인벤토리
        </h1>
        <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            상점으로
        </a>
    </div>

    <!-- 카테고리 탭 (상점과 동일 구조) -->
    <div class="mb-6 overflow-x-auto">
        <div class="flex gap-2 min-w-max">
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="px-4 py-2 rounded-lg font-medium transition-colors <?php echo (!$is_emoticon_tab && !$is_material_tab && empty($tab)) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                전체
            </a>
            <?php foreach ($type_groups as $group_key => $group) {
                if ($group_key === 'material') continue; // 재료는 별도 특수 탭
            ?>
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php?tab=<?php echo $group_key; ?>" class="px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-1 <?php echo (!$is_emoticon_tab && !$is_material_tab && $tab === $group_key) ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <?php echo mg_icon($group['icon'], 'w-4 h-4'); ?>
                <?php echo htmlspecialchars($group['label']); ?>
            </a>
            <?php } ?>
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php?tab=material" class="px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-1 <?php echo $is_material_tab ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <?php echo mg_icon('cube', 'w-4 h-4'); ?>
                재료
            </a>
            <?php if ($emoticon_use == '1') { ?>
            <a href="<?php echo G5_BBS_URL; ?>/inventory.php?tab=emoticon" class="px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-1 <?php echo $is_emoticon_tab ? 'bg-mg-accent text-white' : 'bg-mg-bg-secondary text-mg-text-secondary hover:bg-mg-bg-tertiary'; ?>">
                <?php echo mg_icon('face-smile', 'w-4 h-4'); ?>
                이모티콘
            </a>
            <?php } ?>
        </div>
    </div>

    <?php if ($is_material_tab) { ?>
    <!-- ========== 재료 탭 콘텐츠 ========== -->
    <div class="card mb-4">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-mg-accent flex-shrink-0 mt-0.5"></i>
            <div class="text-sm text-mg-text-secondary">
                <p>파견, 게시판 활동 등으로 획득한 재료입니다. 개척 시설 건설에 사용됩니다.</p>
            </div>
        </div>
    </div>

    <?php if (!empty($my_materials)) { ?>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($my_materials as $mat) {
            $count = (int)($mat['um_count'] ?? 0);
            $icon = $mat['mt_icon'] ?? '';
            $name = htmlspecialchars($mat['mt_name'] ?? '');
            $desc = htmlspecialchars($mat['mt_desc'] ?? '');
        ?>
        <div class="card p-4 text-center">
            <div class="w-14 h-14 mx-auto mb-3 rounded-xl bg-mg-bg-tertiary flex items-center justify-center text-2xl">
                <?php echo $icon ?: '📦'; ?>
            </div>
            <h3 class="font-medium text-mg-text-primary mb-1"><?php echo $name; ?></h3>
            <p class="text-2xl font-bold text-mg-accent mb-1"><?php echo number_format($count); ?></p>
            <?php if ($desc) { ?>
            <p class="text-xs text-mg-text-muted"><?php echo $desc; ?></p>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
    <div class="card py-16 text-center">
        <i data-lucide="package" class="w-16 h-16 mx-auto text-mg-text-muted mb-4"></i>
        <p class="text-mg-text-muted mb-4">보유한 재료가 없습니다.</p>
        <a href="<?php echo G5_BBS_URL; ?>/shop.php?tab=material" class="inline-flex items-center gap-1 text-mg-accent hover:underline">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            상점에서 구매하기
        </a>
    </div>
    <?php } ?>

    <?php } elseif ($is_emoticon_tab) { ?>
    <!-- ========== 이모티콘 탭 콘텐츠 ========== -->

    <!-- 안내 -->
    <div class="card mb-4">
        <div class="flex items-start gap-3">
            <i data-lucide="info" class="w-5 h-5 text-mg-accent flex-shrink-0 mt-0.5"></i>
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
            <i data-lucide="image" class="w-5 h-5 text-mg-accent"></i>
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
            <i data-lucide="pencil-line" class="w-5 h-5 text-mg-accent"></i>
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
                <span class="text-xs text-mg-accent"><?php echo $item_type_names[$active_item['si_type']] ?? $active_item['si_type']; ?></span>
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
            $item = $inv;
            $is_active = in_array($item['si_id'], $active_si_ids);
            $is_usable = in_array($item['si_type'], ['title', 'badge', 'nick_color', 'nick_effect', 'profile_border', 'profile_skin', 'profile_bg', 'profile_effect', 'seal_bg', 'seal_effect', 'seal_frame', 'seal_hover', 'char_slot', 'expedition_slot', 'write_expand', 'achievement_slot', 'nick_bg']);
        ?>
        <div class="card p-0 overflow-hidden <?php echo $is_active ? 'ring-2 ring-mg-accent' : ''; ?>">
            <!-- 이미지 -->
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden">
                <?php if ($item['si_image']) { ?>
                <img src="<?php echo $item['si_image']; ?>" alt="" class="w-full h-full object-cover">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center">
                    <i data-lucide="package" class="w-12 h-12 text-mg-text-muted"></i>
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
                    <div style="display:flex;gap:0.25rem;">
                        <button type="button" onclick="useItem(<?php echo $item['si_id']; ?>)" style="flex:1;" class="btn-primary text-sm font-medium py-2 rounded-lg transition-colors">
                            사용
                        </button>
                        <button type="button" onclick="openGiftModal(<?php echo $item['si_id']; ?>, '<?php echo htmlspecialchars(addslashes($item['si_name']), ENT_QUOTES); ?>')" style="flex-shrink:0;width:2.5rem;" class="bg-mg-bg-tertiary text-mg-text-secondary text-sm py-2 rounded-lg hover:bg-mg-accent hover:text-white transition-colors" title="선물하기">
                            <i data-lucide="gift" class="w-4 h-4 mx-auto"></i>
                        </button>
                    </div>
                    <?php } ?>
                <?php } elseif ($item['si_type'] === 'emoticon_reg') { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <a href="<?php echo G5_BBS_URL; ?>/emoticon_create.php" class="text-xs text-mg-accent hover:underline" style="flex:1;text-align:center;">이모티콘 등록하기</a>
                </div>
                <?php } elseif ($item['si_type'] === 'stamina_recover') { ?>
                <button type="button" onclick="useStaminaRecover(<?php echo $item['si_id']; ?>)" class="w-full btn-primary text-sm font-medium py-2 rounded-lg transition-colors">
                    ⚡ 스태미나 회복
                </button>
                <?php } elseif ($item['si_type'] === 'concierge_extra') { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <span class="text-xs text-mg-text-muted" style="flex:1;text-align:center;">의뢰 등록 시 자동 사용</span>
                </div>
                <?php } elseif ($item['si_type'] === 'concierge_direct_pick') { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <span class="text-xs text-mg-text-muted" style="flex:1;text-align:center;">추첨 시 사용 가능</span>
                </div>
                <?php } elseif ($item['si_type'] === 'rp_pin') { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <span class="text-xs text-mg-text-muted" style="flex:1;text-align:center;">역극 목록에서 사용</span>
                </div>
                <?php } elseif (in_array($item['si_type'], array('expedition_time', 'expedition_reward', 'expedition_stamina'))) { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <span class="text-xs text-mg-text-muted" style="flex:1;text-align:center;">파견 시 자동 선택</span>
                </div>
                <?php } elseif ($item['si_type'] === 'concierge_boost') { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <span class="text-xs text-mg-text-muted" style="flex:1;text-align:center;">의뢰 완료 시 자동 적용</span>
                </div>
                <?php } elseif ($item['si_type'] === 'relation_slot') { ?>
                <button type="button" onclick="openRelSlotModal(<?php echo $item['si_id']; ?>, '<?php echo htmlspecialchars(addslashes($item['si_name']), ENT_QUOTES); ?>')" class="w-full btn-primary text-sm font-medium py-2 rounded-lg transition-colors">
                    캐릭터에 사용
                </button>
                <?php } elseif ($item['si_type'] === 'radio_song') { ?>
                <button type="button" onclick="openRadioSongModal(<?php echo $item['si_id']; ?>)" class="w-full btn-primary text-sm font-medium py-2 rounded-lg transition-colors">
                    🎵 노래 신청
                </button>
                <?php } elseif ($item['si_type'] === 'radio_ment') { ?>
                <button type="button" onclick="openRadioMentModal(<?php echo $item['si_id']; ?>)" class="w-full btn-primary text-sm font-medium py-2 rounded-lg transition-colors">
                    💬 멘트 신청
                </button>
                <?php } else { ?>
                <div style="display:flex;gap:0.25rem;align-items:center;">
                    <span class="text-xs text-mg-text-muted" style="flex:1;text-align:center;">사용 불가</span>
                    <?php if (!$is_active) { ?>
                    <button type="button" onclick="openGiftModal(<?php echo $item['si_id']; ?>, '<?php echo htmlspecialchars(addslashes($item['si_name']), ENT_QUOTES); ?>')" style="flex-shrink:0;width:2.5rem;" class="bg-mg-bg-tertiary text-mg-text-secondary text-sm py-2 rounded-lg hover:bg-mg-accent hover:text-white transition-colors" title="선물하기">
                        <i data-lucide="gift" class="w-4 h-4 mx-auto"></i>
                    </button>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <?php } else { ?>
    <!-- 아이템 없음 -->
    <div class="card py-16 text-center">
        <i data-lucide="box" class="w-16 h-16 mx-auto text-mg-text-muted mb-4"></i>
        <p class="text-mg-text-muted mb-4">
            <?php echo $tab ? '해당 카테고리에 보유한 아이템이 없습니다.' : '보유한 아이템이 없습니다.'; ?>
        </p>
        <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="inline-flex items-center gap-1 text-mg-accent hover:underline">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            상점에서 구매하기
        </a>
    </div>
    <?php } ?>
    <?php } ?>

    <!-- 하단 링크 -->
    <div class="mt-6 flex gap-4 justify-center">
        <a href="<?php echo G5_BBS_URL; ?>/gift.php" class="text-mg-text-muted hover:text-mg-accent transition-colors flex items-center gap-1">
            <i data-lucide="gift" class="w-4 h-4"></i>
            선물함
        </a>
    </div>
</div>

<!-- 선물 모달 -->
<div id="gift-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border-radius:0.75rem;padding:1.5rem;width:90%;max-width:400px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="font-size:1.1rem;font-weight:600;color:var(--mg-text-primary);display:flex;align-items:center;gap:0.5rem;">
                <i data-lucide="gift" class="w-5 h-5" style="color:var(--mg-accent);"></i>
                아이템 선물
            </h3>
            <button type="button" onclick="closeGiftModal()" style="color:var(--mg-text-muted);font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>

        <div id="gift-item-name" style="padding:0.75rem;background:var(--mg-bg-primary);border-radius:0.5rem;margin-bottom:1rem;font-size:0.9rem;color:var(--mg-text-primary);font-weight:500;"></div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.85rem;color:var(--mg-text-secondary);margin-bottom:0.25rem;">받는 사람 (회원 ID)</label>
            <input type="text" id="gift-mb-id-to" placeholder="회원 아이디 입력" style="width:100%;padding:0.5rem 0.75rem;background:var(--mg-bg-tertiary);border:1px solid transparent;border-radius:0.5rem;color:var(--mg-text-primary);font-size:0.9rem;outline:none;" onfocus="this.style.borderColor='var(--mg-accent)'" onblur="this.style.borderColor='transparent'">
            <p id="gift-recipient-info" style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;"></p>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.85rem;color:var(--mg-text-secondary);margin-bottom:0.25rem;">메시지 (선택)</label>
            <textarea id="gift-message" rows="2" maxlength="200" placeholder="선물과 함께 보낼 메시지" style="width:100%;padding:0.5rem 0.75rem;background:var(--mg-bg-tertiary);border:1px solid transparent;border-radius:0.5rem;color:var(--mg-text-primary);font-size:0.9rem;resize:vertical;outline:none;" onfocus="this.style.borderColor='var(--mg-accent)'" onblur="this.style.borderColor='transparent'"></textarea>
        </div>

        <div style="display:flex;gap:0.5rem;">
            <button type="button" onclick="closeGiftModal()" style="flex:1;padding:0.6rem;background:var(--mg-bg-tertiary);color:var(--mg-text-secondary);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;">취소</button>
            <button type="button" id="gift-submit-btn" onclick="submitGift()" style="flex:1;padding:0.6rem;background:var(--mg-button);color:var(--mg-button-text);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;font-weight:500;">선물 보내기</button>
        </div>
    </div>
</div>

<!-- 노래 신청 모달 -->
<div id="radio-song-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border-radius:0.75rem;padding:1.5rem;width:90%;max-width:440px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="font-size:1.1rem;font-weight:600;color:var(--mg-text-primary);display:flex;align-items:center;gap:0.5rem;">
                🎵 노래 신청
            </h3>
            <button type="button" onclick="closeRadioModal('song')" style="color:var(--mg-text-muted);font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.85rem;color:var(--mg-text-secondary);margin-bottom:0.25rem;">YouTube URL <span style="color:var(--mg-error);">*</span></label>
            <input type="text" id="rs-youtube-url" placeholder="https://www.youtube.com/watch?v=..." style="width:100%;padding:0.5rem 0.75rem;background:var(--mg-bg-tertiary);border:1px solid transparent;border-radius:0.5rem;color:var(--mg-text-primary);font-size:0.9rem;outline:none;" onfocus="this.style.borderColor='var(--mg-accent)'" onblur="this.style.borderColor='transparent'">
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.85rem;color:var(--mg-text-secondary);margin-bottom:0.25rem;">곡 제목 <span style="color:var(--mg-error);">*</span></label>
            <input type="text" id="rs-title" placeholder="곡 제목을 입력해주세요" maxlength="200" style="width:100%;padding:0.5rem 0.75rem;background:var(--mg-bg-tertiary);border:1px solid transparent;border-radius:0.5rem;color:var(--mg-text-primary);font-size:0.9rem;outline:none;" onfocus="this.style.borderColor='var(--mg-accent)'" onblur="this.style.borderColor='transparent'">
        </div>

        <div style="padding:0.75rem;background:var(--mg-bg-primary);border-radius:0.5rem;margin-bottom:1rem;font-size:0.75rem;color:var(--mg-text-muted);line-height:1.5;">
            ⚠️ 관리자 검수에 따라 신청곡이 반영되지 않을 수 있습니다.<br>
            신청 시 신청권 1개가 소모됩니다.
        </div>

        <div style="display:flex;gap:0.5rem;">
            <button type="button" onclick="closeRadioModal('song')" style="flex:1;padding:0.6rem;background:var(--mg-bg-tertiary);color:var(--mg-text-secondary);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;">취소</button>
            <button type="button" id="rs-submit-btn" onclick="submitRadioSong()" style="flex:1;padding:0.6rem;background:var(--mg-button);color:var(--mg-button-text);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;font-weight:500;">신청하기</button>
        </div>
    </div>
</div>

<!-- 멘트 신청 모달 -->
<div id="radio-ment-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border-radius:0.75rem;padding:1.5rem;width:90%;max-width:440px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="font-size:1.1rem;font-weight:600;color:var(--mg-text-primary);display:flex;align-items:center;gap:0.5rem;">
                💬 라디오 멘트 신청
            </h3>
            <button type="button" onclick="closeRadioModal('ment')" style="color:var(--mg-text-muted);font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.85rem;color:var(--mg-text-secondary);margin-bottom:0.25rem;">멘트 내용 <span style="color:var(--mg-error);">*</span></label>
            <textarea id="rm-content" rows="4" maxlength="200" placeholder="라디오에서 읽어줄 멘트를 입력해주세요" style="width:100%;padding:0.5rem 0.75rem;background:var(--mg-bg-tertiary);border:1px solid transparent;border-radius:0.5rem;color:var(--mg-text-primary);font-size:0.9rem;resize:vertical;outline:none;" onfocus="this.style.borderColor='var(--mg-accent)'" onblur="this.style.borderColor='transparent'"></textarea>
            <p id="rm-char-count" style="font-size:0.75rem;color:var(--mg-text-muted);text-align:right;margin-top:0.25rem;">0 / 200</p>
        </div>

        <div style="padding:0.75rem;background:var(--mg-bg-primary);border-radius:0.5rem;margin-bottom:1rem;font-size:0.75rem;color:var(--mg-text-muted);line-height:1.5;">
            ⚠️ 관리자 검수에 따라 멘트가 반영되지 않을 수 있습니다.<br>
            신청 시 멘트권 1개가 소모됩니다.
        </div>

        <div style="display:flex;gap:0.5rem;">
            <button type="button" onclick="closeRadioModal('ment')" style="flex:1;padding:0.6rem;background:var(--mg-bg-tertiary);color:var(--mg-text-secondary);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;">취소</button>
            <button type="button" id="rm-submit-btn" onclick="submitRadioMent()" style="flex:1;padding:0.6rem;background:var(--mg-button);color:var(--mg-button-text);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;font-weight:500;">신청하기</button>
        </div>
    </div>
</div>

<!-- 관계 슬롯 확장 - 캐릭터 선택 모달 -->
<div id="relslot-modal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.6);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border-radius:0.75rem;padding:1.5rem;width:90%;max-width:440px;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
            <h3 style="font-size:1.1rem;font-weight:600;color:var(--mg-text-primary);display:flex;align-items:center;gap:0.5rem;">
                관계 슬롯 사용
            </h3>
            <button type="button" onclick="closeRelSlotModal()" style="color:var(--mg-text-muted);font-size:1.5rem;line-height:1;background:none;border:none;cursor:pointer;">&times;</button>
        </div>

        <div id="relslot-item-name" style="padding:0.75rem;background:var(--mg-bg-primary);border-radius:0.5rem;margin-bottom:1rem;font-size:0.9rem;color:var(--mg-text-primary);font-weight:500;"></div>

        <div style="margin-bottom:1rem;">
            <label style="display:block;font-size:0.85rem;color:var(--mg-text-secondary);margin-bottom:0.5rem;">슬롯을 추가할 캐릭터 선택</label>
            <?php if (empty($_relslot_chars)) { ?>
            <p style="font-size:0.85rem;color:var(--mg-text-muted);padding:1rem;text-align:center;">승인된 캐릭터가 없습니다.</p>
            <?php } else { ?>
            <div style="display:flex;flex-direction:column;gap:0.5rem;" id="relslot-char-list">
                <?php foreach ($_relslot_chars as $_rsc) { ?>
                <label style="display:flex;align-items:center;gap:0.75rem;padding:0.75rem;background:var(--mg-bg-primary);border-radius:0.5rem;cursor:pointer;border:2px solid transparent;transition:border-color 0.15s;" class="relslot-char-option" onmouseenter="this.style.borderColor='var(--mg-bg-tertiary)'" onmouseleave="if(!this.querySelector('input').checked)this.style.borderColor='transparent'">
                    <input type="radio" name="relslot_ch_id" value="<?php echo $_rsc['ch_id']; ?>" style="accent-color:var(--mg-accent);width:1rem;height:1rem;flex-shrink:0;">
                    <?php if ($_rsc['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$_rsc['ch_thumb']; ?>" style="width:2rem;height:2rem;border-radius:50%;object-fit:cover;flex-shrink:0;" alt="">
                    <?php } else { ?>
                    <div style="width:2rem;height:2rem;border-radius:50%;background:var(--mg-bg-tertiary);display:flex;align-items:center;justify-content:center;color:var(--mg-text-muted);font-size:0.75rem;flex-shrink:0;">?</div>
                    <?php } ?>
                    <div style="flex:1;min-width:0;">
                        <span style="font-size:0.9rem;color:var(--mg-text-primary);font-weight:500;"><?php echo htmlspecialchars($_rsc['ch_name']); ?></span>
                        <span style="font-size:0.75rem;color:var(--mg-text-muted);margin-left:0.5rem;">관계 <?php echo $_rsc['rel_count']; ?>/<?php echo $_rsc['rel_max']; ?></span>
                    </div>
                </label>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <div style="display:flex;gap:0.5rem;">
            <button type="button" onclick="closeRelSlotModal()" style="flex:1;padding:0.6rem;background:var(--mg-bg-tertiary);color:var(--mg-text-secondary);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;">취소</button>
            <button type="button" id="relslot-submit-btn" onclick="submitRelSlot()" style="flex:1;padding:0.6rem;background:var(--mg-button);color:var(--mg-button-text);border:none;border-radius:0.5rem;cursor:pointer;font-size:0.9rem;font-weight:500;">사용</button>
        </div>
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
    mgConfirm(action, function() {
        var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo G5_BBS_URL; ?>/emoticon_create_update.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            location.reload();
        } else {
            mgToast('오류가 발생했습니다.', 'error');
        }
    };
    xhr.send('action=toggle_sale&es_id=' + esId + '&es_use=' + newUse);
    });
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
            mgToast(data.message, 'error');
        }
    })
    .catch(error => {
        mgToast('오류가 발생했습니다.', 'error');
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
            mgToast(data.message, 'error');
        }
    })
    .catch(error => {
        mgToast('오류가 발생했습니다.', 'error');
        console.error(error);
    });
}

// 선물 모달
var _giftSiId = 0;
// 스태미나 회복 물약 사용
function useStaminaRecover(si_id) {
    fetch('<?php echo G5_BBS_URL; ?>/inventory_use.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=stamina_check&si_id=' + si_id
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success && data.info) {
            // 상한 도달 경고
            var info = data.info;
            if (info.remaining_limit === 0) {
                mgToast('일일 스태미나 회복 상한에 도달했습니다.', 'warning');
                return;
            }
            if (info.deficit <= 0) {
                mgToast('스태미나가 이미 최대입니다.', 'warning');
                return;
            }
        }
        // 정상 또는 부분 회복 안내
        var info = data.info || {};
        var msg = '스태미나 회복 물약을 사용하시겠습니까?\n';
        msg += '현재 스태미나: ' + (info.current || '?') + '/' + (info.max || '?') + '\n';
        if (info.daily_limit > 0 && info.recoverable < info.deficit) {
            msg += '⚠️ 일일 회복 상한으로 ' + info.recoverable + '만 회복 가능합니다.\n';
            msg += '(일일 상한: ' + info.daily_limit + ', 오늘 사용: ' + info.recovered_today + ')';
        } else {
            msg += '풀 충전됩니다. (회복량: ' + info.deficit + ')';
        }
        mgConfirm(msg, function() {
            fetch('<?php echo G5_BBS_URL; ?>/inventory_use.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=use&si_id=' + si_id
            })
            .then(function(r) { return r.json(); })
            .then(function(result) {
                mgToast(result.message || '사용 완료', 'success');
                if (result.success) location.reload();
            });
        });
    });
}

var _giftCheckTimer = null;

function openGiftModal(si_id, itemName) {
    _giftSiId = si_id;
    document.getElementById('gift-item-name').textContent = itemName;
    document.getElementById('gift-mb-id-to').value = '';
    document.getElementById('gift-message').value = '';
    document.getElementById('gift-recipient-info').textContent = '';
    document.getElementById('gift-modal').style.display = 'flex';
}

function closeGiftModal() {
    document.getElementById('gift-modal').style.display = 'none';
    _giftSiId = 0;
}

// 모달 외부 클릭 닫기
document.getElementById('gift-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeGiftModal();
});

// 받는 사람 ID 입력 시 닉네임 확인
document.getElementById('gift-mb-id-to').addEventListener('input', function() {
    clearTimeout(_giftCheckTimer);
    var val = this.value.trim();
    var info = document.getElementById('gift-recipient-info');
    if (!val) { info.textContent = ''; return; }
    _giftCheckTimer = setTimeout(function() {
        fetch('<?php echo G5_BBS_URL; ?>/ajax_member_check.php?mb_id=' + encodeURIComponent(val))
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.exists) {
                info.textContent = d.mb_nick + ' 님에게 선물합니다.';
                info.style.color = 'var(--mg-success, #22c55e)';
            } else {
                info.textContent = '존재하지 않는 회원입니다.';
                info.style.color = 'var(--mg-error, #ef4444)';
            }
        })
        .catch(function() { info.textContent = ''; });
    }, 400);
});

// ─── 라디오 신청 모달 ───
var _radioSiId = 0;

function openRadioSongModal(si_id) {
    _radioSiId = si_id;
    document.getElementById('rs-youtube-url').value = '';
    document.getElementById('rs-title').value = '';
    document.getElementById('radio-song-modal').style.display = 'flex';
}

function openRadioMentModal(si_id) {
    _radioSiId = si_id;
    document.getElementById('rm-content').value = '';
    document.getElementById('rm-char-count').textContent = '0 / 200';
    document.getElementById('radio-ment-modal').style.display = 'flex';
}

function closeRadioModal(type) {
    document.getElementById('radio-' + type + '-modal').style.display = 'none';
    _radioSiId = 0;
}

// 멘트 글자수 카운트
document.getElementById('rm-content').addEventListener('input', function() {
    document.getElementById('rm-char-count').textContent = this.value.length + ' / 200';
});

// 모달 외부 클릭 닫기
document.getElementById('radio-song-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeRadioModal('song');
});
document.getElementById('radio-ment-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeRadioModal('ment');
});

function submitRadioSong() {
    var url = document.getElementById('rs-youtube-url').value.trim();
    var title = document.getElementById('rs-title').value.trim();
    if (!url || !title) {
        mgToast('YouTube URL과 곡 제목을 모두 입력해주세요.', 'warning');
        return;
    }
    var btn = document.getElementById('rs-submit-btn');
    btn.disabled = true;
    btn.textContent = '처리 중...';

    fetch('<?php echo G5_BBS_URL; ?>/radio_api.php?action=request_song', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ youtube_url: url, title: title })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mgToast(data.message, 'success');
            closeRadioModal('song');
            location.reload();
        } else {
            mgToast(data.message, 'error');
            btn.disabled = false;
            btn.textContent = '신청하기';
        }
    })
    .catch(function() {
        mgToast('오류가 발생했습니다.', 'error');
        btn.disabled = false;
        btn.textContent = '신청하기';
    });
}

function submitRadioMent() {
    var content = document.getElementById('rm-content').value.trim();
    if (!content) {
        mgToast('멘트 내용을 입력해주세요.', 'warning');
        return;
    }
    var btn = document.getElementById('rm-submit-btn');
    btn.disabled = true;
    btn.textContent = '처리 중...';

    fetch('<?php echo G5_BBS_URL; ?>/radio_api.php?action=request_ment', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content: content })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mgToast(data.message, 'success');
            closeRadioModal('ment');
            location.reload();
        } else {
            mgToast(data.message, 'error');
            btn.disabled = false;
            btn.textContent = '신청하기';
        }
    })
    .catch(function() {
        mgToast('오류가 발생했습니다.', 'error');
        btn.disabled = false;
        btn.textContent = '신청하기';
    });
}

// ─── 관계 슬롯 모달 ───
var _relSlotSiId = 0;

function openRelSlotModal(si_id, itemName) {
    _relSlotSiId = si_id;
    document.getElementById('relslot-item-name').textContent = itemName;
    // 라디오 버튼 초기화
    var radios = document.querySelectorAll('input[name="relslot_ch_id"]');
    radios.forEach(function(r) { r.checked = false; r.closest('label').style.borderColor = 'transparent'; });
    document.getElementById('relslot-modal').style.display = 'flex';
}

function closeRelSlotModal() {
    document.getElementById('relslot-modal').style.display = 'none';
    _relSlotSiId = 0;
}

// 라디오 선택 시 하이라이트
document.querySelectorAll('.relslot-char-option input').forEach(function(radio) {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.relslot-char-option').forEach(function(l) { l.style.borderColor = 'transparent'; });
        if (this.checked) this.closest('label').style.borderColor = 'var(--mg-accent)';
    });
});

document.getElementById('relslot-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeRelSlotModal();
});

function submitRelSlot() {
    var selected = document.querySelector('input[name="relslot_ch_id"]:checked');
    if (!selected) {
        mgToast('캐릭터를 선택해주세요.', 'warning');
        return;
    }
    var btn = document.getElementById('relslot-submit-btn');
    btn.disabled = true;
    btn.textContent = '처리 중...';

    fetch('<?php echo G5_BBS_URL; ?>/inventory_use.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=use&si_id=' + _relSlotSiId + '&ch_id=' + selected.value
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mgToast(data.message, 'success');
            closeRelSlotModal();
            location.reload();
        } else {
            mgToast(data.message, 'error');
            btn.disabled = false;
            btn.textContent = '사용';
        }
    })
    .catch(function() {
        mgToast('오류가 발생했습니다.', 'error');
        btn.disabled = false;
        btn.textContent = '사용';
    });
}

function submitGift() {
    var mbIdTo = document.getElementById('gift-mb-id-to').value.trim();
    var message = document.getElementById('gift-message').value.trim();

    if (!mbIdTo) {
        mgToast('받는 사람의 회원 아이디를 입력해주세요.', 'warning');
        return;
    }
    if (!_giftSiId) return;

    var btn = document.getElementById('gift-submit-btn');
    btn.disabled = true;
    btn.textContent = '처리 중...';

    fetch('<?php echo G5_BBS_URL; ?>/inventory_gift.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'si_id=' + _giftSiId + '&mb_id_to=' + encodeURIComponent(mbIdTo) + '&message=' + encodeURIComponent(message)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            mgToast(data.message, 'success');
            closeGiftModal();
            location.reload();
        } else {
            mgToast(data.message, 'error');
            btn.disabled = false;
            btn.textContent = '선물 보내기';
        }
    })
    .catch(function(error) {
        mgToast('오류가 발생했습니다.', 'error');
        btn.disabled = false;
        btn.textContent = '선물 보내기';
        console.error(error);
    });
}
</script>
