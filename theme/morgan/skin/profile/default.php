<?php
/**
 * Morgan Edition - 캐릭터 프로필 기본 스킨
 *
 * 사용 가능한 변수:
 * $char, $grouped_fields, $achievement_showcase, $char_relations,
 * $is_owner, $can_request_relation, $my_approved_characters, $profile_skin_id
 */
if (!defined('_GNUBOARD_')) exit;
?>

<div class="mg-inner" style="max-width:800px;">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>뒤로</span>
    </a>

    <!-- 프로필 헤더 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <?php if ($char_header) { ?>
        <div style="max-height:13rem;overflow:hidden;">
            <img src="<?php echo $char_header; ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <?php } ?>
        <div class="md:flex">
            <!-- 이미지 -->
            <div class="md:w-64 lg:w-80 flex-shrink-0">
                <div class="aspect-square bg-mg-bg-tertiary">
                    <?php if ($char['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="<?php echo $char['ch_name']; ?>" class="w-full h-full object-cover">
                    <?php } else { ?>
                    <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- 기본 정보 -->
            <div class="flex-1 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <!-- 배지 -->
                        <div class="flex items-center gap-2 mb-2">
                            <?php if ($char['ch_main']) { ?>
                            <span class="bg-mg-accent text-white text-xs px-2 py-0.5 rounded-full">대표</span>
                            <?php } ?>
                            <?php
                            $state_labels = array(
                                'editing' => array('수정중', 'bg-gray-500'),
                                'pending' => array('승인대기', 'bg-yellow-500'),
                                'approved' => array('승인됨', 'bg-green-500'),
                            );
                            $state = $state_labels[$char['ch_state']] ?? array('', '');
                            if ($state[0]) {
                            ?>
                            <span class="<?php echo $state[1]; ?> text-white text-xs px-2 py-0.5 rounded-full"><?php echo $state[0]; ?></span>
                            <?php } ?>
                        </div>

                        <!-- 이름 -->
                        <h1 class="text-3xl font-bold text-mg-text-primary"><?php echo $char['ch_name']; ?></h1>

                        <!-- 세력/종족 -->
                        <div class="flex items-center gap-3 mt-2 text-mg-text-secondary">
                            <?php if ($char['side_name'] && mg_config('use_side', '1') == '1') { ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                </svg>
                                <?php echo $char['side_name']; ?>
                            </span>
                            <?php } ?>
                            <?php if ($char['class_name'] && mg_config('use_class', '1') == '1') { ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                <?php echo $char['class_name']; ?>
                            </span>
                            <?php } ?>
                        </div>

                        <!-- 오너 정보 -->
                        <div class="mt-4 text-sm text-mg-text-muted">
                            <span class="text-mg-text-secondary">@<?php echo $char['mb_nick']; ?></span>
                            <span class="mx-2">&middot;</span>
                            <span><?php echo date('Y.m.d', strtotime($char['ch_datetime'])); ?> 등록</span>
                        </div>
                    </div>

                    <!-- 액션 버튼 -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <?php if ($can_request_relation) { ?>
                        <button type="button" onclick="openRelRequestModal()" class="inline-flex items-center gap-1 text-sm bg-mg-accent hover:bg-mg-accent-hover text-white px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>관계 신청</span>
                        </button>
                        <?php } ?>
                        <?php if ($is_owner) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="inline-flex items-center gap-1 text-sm bg-mg-bg-tertiary hover:bg-mg-bg-primary text-mg-text-secondary px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>수정</span>
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 업적 쇼케이스 -->
    <?php if (!empty($achievement_showcase)) {
        $ach_rarity_colors = array(
            'common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6',
            'epic' => '#a855f7', 'legendary' => '#f59e0b',
        );
        $ach_rarity_labels = array(
            'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare',
            'epic' => 'Epic', 'legendary' => 'Legendary',
        );
    ?>
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="font-medium text-mg-text-primary">업적 쇼케이스</h2>
            <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-xs text-mg-accent hover:underline">전체보기</a>
        </div>
        <div class="p-4 flex gap-3 flex-wrap justify-center">
            <?php foreach ($achievement_showcase as $acd) {
                $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                $a_name = $acd['tier_name'] ?: $acd['ac_name'];
                $a_rarity = $acd['ac_rarity'] ?: 'common';
                $a_color = $ach_rarity_colors[$a_rarity] ?? '#949ba4';
            ?>
            <div class="flex flex-col items-center p-3 rounded-lg min-w-[80px]" style="border:2px solid <?php echo $a_color; ?>;" title="<?php echo htmlspecialchars($a_name); ?>">
                <?php if ($a_icon) { ?>
                <img src="<?php echo htmlspecialchars($a_icon); ?>" alt="<?php echo htmlspecialchars($a_name); ?>" class="w-10 h-10 object-contain">
                <?php } else { ?>
                <svg class="w-8 h-8" style="color:var(--mg-accent);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3h14l-1.405 4.544A5.001 5.001 0 0112 13a5.001 5.001 0 01-5.595-5.456L5 3zm7 10v4m-4 4h8m-4-4v4"/></svg>
                <?php } ?>
                <span class="text-xs text-mg-text-secondary mt-1 text-center leading-tight max-w-[70px] truncate"><?php echo htmlspecialchars($a_name); ?></span>
                <span class="text-[10px] mt-0.5" style="color:<?php echo $a_color; ?>;"><?php echo $ach_rarity_labels[$a_rarity] ?? ''; ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- 프로필 상세 -->
    <?php if (count($grouped_fields) > 0) { ?>
    <div class="space-y-4">
        <?php foreach ($grouped_fields as $category => $fields) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary"><?php echo $category; ?></h2>
            </div>
            <div class="p-4">
                <dl class="space-y-4">
                    <?php foreach ($fields as $field) { ?>
                    <div>
                        <dt class="text-sm font-medium text-mg-text-muted mb-1"><?php echo $field['pf_name']; ?></dt>
                        <dd class="text-mg-text-primary">
                            <?php echo mg_render_profile_value($field); ?>
                        </dd>
                    </div>
                    <?php } ?>
                </dl>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 캐릭터 관계 -->
    <?php if (!empty($char_relations)) { ?>
    <div class="mt-6 bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="font-medium text-mg-text-primary">관계</h2>
            <div class="flex items-center gap-2">
                <?php if ($is_owner) { ?>
                <button type="button" id="rel-graph-save" class="text-xs text-mg-text-muted hover:text-mg-accent hidden">배치 저장</button>
                <?php } ?>
                <button type="button" id="rel-graph-toggle" class="text-xs text-mg-accent hover:underline">관계도 보기</button>
            </div>
        </div>
        <div class="divide-y divide-mg-bg-tertiary">
            <?php foreach ($char_relations as $rel) {
                $is_a = ($char['ch_id'] == $rel['ch_id_a']);
                $other_name = $is_a ? $rel['name_b'] : $rel['name_a'];
                $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                $my_label = $is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']);
                $rel_color = $rel['cr_color'] ?: '#95a5a6';
            ?>
            <div class="px-4 py-3 flex items-center gap-3">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                <?php } else { ?>
                <div class="w-9 h-9 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm flex-shrink-0">?</div>
                <?php } ?>
                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($rel_color); ?>"></span>
                <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($my_label); ?></span>
                <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" class="text-sm font-medium text-mg-accent hover:underline ml-auto"><?php echo htmlspecialchars($other_name); ?></a>
            </div>
            <?php } ?>
        </div>

        <!-- 인라인 관계도 (토글) -->
        <div id="rel-graph-wrap" class="hidden border-t border-mg-bg-tertiary">
            <div id="rel-graph-container" style="height:400px; background:#1a1a1a;"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

<style>
/* default 스킨 호버 효과 */

/* 프로필 카드 */
.mg-inner > .bg-mg-bg-secondary {
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}
.mg-inner > .bg-mg-bg-secondary:hover {
    border-color: var(--mg-accent);
    box-shadow: 0 4px 20px rgba(245, 159, 10, 0.08);
}

/* 헤더 배너 */
.mg-inner > .bg-mg-bg-secondary > div:first-child img {
    transition: transform 0.6s ease;
}
.mg-inner > .bg-mg-bg-secondary > div:first-child:hover img {
    transform: scale(1.03);
}

/* 초상화 */
.mg-inner .aspect-square img {
    transition: transform 0.4s ease, filter 0.4s ease;
}
.mg-inner .aspect-square:hover img {
    transform: scale(1.05);
    filter: brightness(1.1);
}

/* 관계 항목 */
.mg-inner .divide-y > div {
    transition: background-color 0.25s ease, padding-left 0.25s ease;
}
.mg-inner .divide-y > div:hover {
    background-color: rgba(245, 159, 10, 0.04);
    padding-left: 1.25rem;
}

/* 프로필 필드 카드 */
.mg-inner .space-y-4 > div > .bg-mg-bg-secondary {
    transition: border-color 0.3s ease;
}
.mg-inner .space-y-4 > div > .bg-mg-bg-secondary:hover {
    border-color: var(--mg-text-muted);
}

/* 업적 배지 */
.mg-inner .flex.gap-3 > div[style*="border"] {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.mg-inner .flex.gap-3 > div[style*="border"]:hover {
    transform: scale(1.08);
    box-shadow: 0 0 12px rgba(245, 159, 10, 0.2);
}

/* 액션 버튼 강화 */
.mg-inner .bg-mg-accent {
    transition: background-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
}
.mg-inner .bg-mg-accent:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 159, 10, 0.3);
}
.mg-inner .bg-mg-bg-tertiary {
    transition: background-color 0.2s ease, transform 0.2s ease;
}
</style>
