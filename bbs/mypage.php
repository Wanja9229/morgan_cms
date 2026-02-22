<?php
/**
 * Morgan Edition - 마이 페이지
 * 유저 정보 허브: 프로필, 캐릭터, 인장, 인벤토리, 업적, 선물함 등
 */

include_once('./_common.php');
include_once(G5_PATH.'/plugin/morgan/morgan.php');

if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

$mb = $member;

// 대표 캐릭터
$main_char = null;
if (function_exists('mg_get_main_character')) {
    $main_char = mg_get_main_character($mb['mb_id']);
}

// 전체 캐릭터 수
$char_count = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_character_table']} WHERE mb_id = '{$mb['mb_id']}' AND ch_state != 'deleted'");
$char_count = (int)$char_count['cnt'];

// 인벤토리
$inventory_items = array();
$inventory_count = 0;
if (function_exists('mg_get_inventory')) {
    $inv_data = mg_get_inventory($mb['mb_id'], 0, 1, 8);
    $inventory_items = isset($inv_data['items']) ? $inv_data['items'] : array();
    $inventory_count = isset($inv_data['total']) ? $inv_data['total'] : 0;
}

// 선물함
$pending_gifts = array();
$gift_count = 0;
if (function_exists('mg_get_pending_gifts')) {
    $pending_gifts = mg_get_pending_gifts($mb['mb_id'], 5);
    $gift_count = count($pending_gifts);
}

// 업적
$achievements = array();
$achievement_total = 0;
$achievement_completed = 0;
if (function_exists('mg_get_achievements')) {
    $all_ach = mg_get_achievements();
    $achievement_total = count($all_ach);
    $user_ach_result = sql_query("SELECT ua.*, a.ac_name, a.ac_icon, a.ac_rarity, a.ac_category, a.ac_type
        FROM {$g5['mg_user_achievement_table']} ua
        JOIN {$g5['mg_achievement_table']} a ON ua.ac_id = a.ac_id
        WHERE ua.mb_id = '{$mb['mb_id']}'
        ORDER BY ua.ua_completed DESC, ua.ua_datetime DESC");
    while ($row = sql_fetch_array($user_ach_result)) {
        $achievements[] = $row;
        if ($row['ua_completed']) $achievement_completed++;
    }
}

// 업적 쇼케이스
$achievement_showcase = array();
if (function_exists('mg_get_achievement_display')) {
    $achievement_showcase = mg_get_achievement_display($mb['mb_id']);
}

// 인장 데이터
$_seal_enabled = mg_config('seal_enable', '1') == '1' || mg_config('seal_enable', '1') == 1;
$seal = null;
if ($_seal_enabled && function_exists('mg_get_seal')) {
    $seal = mg_get_seal($mb['mb_id']);
}

// 출석 정보
$attendance_today = false;
$attendance_streak = 0;
$att_row = sql_fetch("SELECT * FROM {$g5['mg_attendance_table']} WHERE mb_id = '{$mb['mb_id']}' AND att_date = CURDATE()");
if ($att_row && $att_row['att_date']) {
    $attendance_today = true;
}
$streak_row = sql_fetch("SELECT att_streak FROM {$g5['mg_attendance_table']} WHERE mb_id = '{$mb['mb_id']}' ORDER BY att_date DESC LIMIT 1");
$attendance_streak = $streak_row ? (int)$streak_row['att_streak'] : 0;

// 칭호
$active_title = '';
if (function_exists('mg_get_active_items')) {
    $title_items = mg_get_active_items($mb['mb_id'], 'title');
    if (!empty($title_items)) {
        $active_title = $title_items[0]['si_name'];
    }
}

$g5['title'] = '마이 페이지';
include_once(G5_THEME_PATH.'/head.php');

$ach_rarity_colors = array(
    'common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6',
    'epic' => '#a855f7', 'legendary' => '#f59e0b',
);
?>

<div class="mg-inner">

    <!-- 프로필 헤더 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="p-6">
            <div class="flex items-center gap-5">
                <!-- 아바타 -->
                <div class="w-20 h-20 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold text-3xl flex-shrink-0">
                    <?php echo mb_substr($mb['mb_nick'], 0, 1); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3 mb-1">
                        <h1 class="text-2xl font-bold text-mg-text-primary"><?php echo get_text($mb['mb_nick']); ?></h1>
                        <?php if ($active_title) { ?>
                        <span class="text-sm text-mg-accent bg-mg-accent/10 px-2 py-0.5 rounded-full"><?php echo htmlspecialchars($active_title); ?></span>
                        <?php } ?>
                    </div>
                    <p class="text-sm text-mg-text-muted mb-3">@<?php echo $mb['mb_id']; ?> · Lv.<?php echo $mb['mb_level']; ?> · <?php echo date('Y.m.d', strtotime($mb['mb_datetime'])); ?> 가입</p>

                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="flex items-center gap-1.5 text-sm">
                            <span class="text-mg-text-muted"><?php echo function_exists('mg_point_name') ? mg_point_name() : '포인트'; ?></span>
                            <span class="font-semibold text-mg-accent"><?php echo function_exists('mg_point_format') ? mg_point_format($mb['mb_point']) : number_format($mb['mb_point']).'P'; ?></span>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm">
                            <span class="text-mg-text-muted">캐릭터</span>
                            <span class="font-semibold text-mg-text-primary"><?php echo $char_count; ?>개</span>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm">
                            <span class="text-mg-text-muted">출석</span>
                            <?php if ($attendance_today) { ?>
                            <span class="font-semibold text-green-400"><?php echo $attendance_streak; ?>일 연속</span>
                            <?php } else { ?>
                            <a href="<?php echo G5_BBS_URL; ?>/attendance.php" class="font-semibold text-mg-accent hover:underline">출석하기</a>
                            <?php } ?>
                        </div>
                        <div class="flex items-center gap-1.5 text-sm">
                            <span class="text-mg-text-muted">업적</span>
                            <span class="font-semibold text-mg-text-primary"><?php echo $achievement_completed; ?>/<?php echo $achievement_total; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 바로가기 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
            <h2 class="text-sm font-semibold text-mg-text-primary">바로가기</h2>
        </div>
        <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <a href="<?php echo G5_BBS_URL; ?>/attendance.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">출석체크</span>
            </a>
            <?php if ($_seal_enabled) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/seal_edit.php" data-no-spa class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">인장 편집</span>
            </a>
            <?php } ?>
            <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">업적</span>
            </a>
            <a href="<?php echo G5_BBS_URL; ?>/notification.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">알림</span>
            </a>
            <a href="<?php echo G5_URL; ?>/bbs/memo.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">쪽지</span>
            </a>
            <a href="<?php echo G5_URL; ?>/bbs/scrap.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">스크랩</span>
            </a>
            <a href="<?php echo G5_URL; ?>/bbs/point.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">포인트 내역</span>
            </a>
            <a href="<?php echo G5_URL; ?>/bbs/profile.php" class="flex items-center gap-2 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">
                <svg class="w-5 h-5 text-mg-accent flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-sm text-mg-text-secondary">회원정보 수정</span>
            </a>
        </div>
    </div>

    <!-- 대표 캐릭터 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                대표 캐릭터
            </h2>
            <a href="<?php echo G5_BBS_URL; ?>/character.php" class="text-xs text-mg-accent hover:underline">캐릭터 관리</a>
        </div>
        <div class="p-4">
            <?php if ($main_char) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $main_char['ch_id']; ?>" class="flex items-center gap-4 group">
                <div class="w-16 h-16 rounded-lg bg-mg-bg-tertiary flex-shrink-0 overflow-hidden">
                    <?php if (!empty($main_char['ch_thumb'])) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$main_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    <?php } else { ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <?php } ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-mg-text-primary group-hover:text-mg-accent transition-colors"><?php echo htmlspecialchars($main_char['ch_name']); ?></p>
                    <?php if (!empty($main_char['ch_side'])) { ?>
                    <p class="text-xs text-mg-text-muted"><?php echo htmlspecialchars($main_char['ch_side']); ?></p>
                    <?php } ?>
                </div>
            </a>
            <?php } else { ?>
            <div class="text-center py-6">
                <p class="text-sm text-mg-text-muted mb-2">등록된 캐릭터가 없습니다</p>
                <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="text-sm text-mg-accent hover:underline">캐릭터 만들기</a>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 업적 쇼케이스 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                업적
            </h2>
            <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-xs text-mg-accent hover:underline">전체보기</a>
        </div>
        <div class="p-4">
            <?php if (!empty($achievement_showcase)) { ?>
            <!-- 쇼케이스 -->
            <div class="flex gap-3 flex-wrap mb-4">
                <?php foreach ($achievement_showcase as $acd) {
                    $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                    $a_name = $acd['tier_name'] ?: $acd['ac_name'];
                    $a_rarity = $acd['ac_rarity'] ?: 'common';
                    $a_color = $ach_rarity_colors[$a_rarity] ?? '#949ba4';
                ?>
                <div class="flex flex-col items-center p-3 rounded-lg min-w-[80px]" style="border:2px solid <?php echo $a_color; ?>;" title="<?php echo htmlspecialchars($a_name); ?>">
                    <?php if ($a_icon) { ?>
                    <img src="<?php echo htmlspecialchars($a_icon); ?>" alt="" class="w-10 h-10 object-contain">
                    <?php } else { ?>
                    <span class="text-2xl">&#127942;</span>
                    <?php } ?>
                    <span class="text-xs text-mg-text-secondary mt-1 text-center leading-tight max-w-[70px] truncate"><?php echo htmlspecialchars($a_name); ?></span>
                </div>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 최근 업적 진행 -->
            <?php
            $recent_ach = array_slice($achievements, 0, 4);
            if (!empty($recent_ach)) { ?>
            <div class="space-y-2">
                <?php foreach ($recent_ach as $ua) {
                    $a_color = $ach_rarity_colors[$ua['ac_rarity']] ?? '#949ba4';
                ?>
                <div class="flex items-center gap-3 p-2 bg-mg-bg-primary rounded-lg">
                    <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0" style="border:1px solid <?php echo $a_color; ?>;">
                        <?php if ($ua['ac_icon']) { ?>
                        <img src="<?php echo htmlspecialchars($ua['ac_icon']); ?>" alt="" class="w-6 h-6 object-contain">
                        <?php } else { ?>
                        <span class="text-sm">&#127942;</span>
                        <?php } ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-mg-text-primary truncate"><?php echo htmlspecialchars($ua['ac_name']); ?></p>
                        <?php if ($ua['ua_completed']) { ?>
                        <p class="text-xs text-green-400">달성!</p>
                        <?php } elseif ($ua['ac_type'] == 'progressive') { ?>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-1.5 bg-mg-bg-tertiary rounded-full overflow-hidden">
                                <?php
                                // 현재 티어의 목표치 계산
                                $next_tier = sql_fetch("SELECT at_target FROM {$g5['mg_achievement_tier_table']} WHERE ac_id = {$ua['ac_id']} AND at_level = ".((int)$ua['ua_tier'] + 1));
                                $target = $next_tier ? (int)$next_tier['at_target'] : (int)$ua['ua_progress'];
                                $pct = $target > 0 ? min(100, round($ua['ua_progress'] / $target * 100)) : 0;
                                ?>
                                <div class="h-full rounded-full" style="width:<?php echo $pct; ?>%;background:<?php echo $a_color; ?>;"></div>
                            </div>
                            <span class="text-xs text-mg-text-muted"><?php echo $ua['ua_progress']; ?>/<?php echo $target; ?></span>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
            <?php } else { ?>
            <div class="text-center py-4">
                <p class="text-sm text-mg-text-muted">아직 진행 중인 업적이 없습니다</p>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- 2컬럼: 인벤토리 + 선물함 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

        <!-- 인벤토리 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h2 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    인벤토리
                    <?php if ($inventory_count > 0) { ?>
                    <span class="text-xs text-mg-text-muted">(<?php echo $inventory_count; ?>)</span>
                    <?php } ?>
                </h2>
                <a href="<?php echo G5_BBS_URL; ?>/inventory.php" class="text-xs text-mg-accent hover:underline">전체보기</a>
            </div>
            <div class="p-4">
                <?php if (count($inventory_items) > 0) { ?>
                <div class="grid grid-cols-4 gap-2">
                    <?php foreach ($inventory_items as $inv_item) { ?>
                    <div class="aspect-square bg-mg-bg-tertiary rounded-lg flex items-center justify-center relative group cursor-pointer hover:ring-2 hover:ring-mg-accent transition-all" title="<?php echo htmlspecialchars($inv_item['si_name']); ?>">
                        <?php if (!empty($inv_item['si_image'])) { ?>
                        <img src="<?php echo $inv_item['si_image']; ?>" alt="" class="w-full h-full object-cover rounded-lg">
                        <?php } else { ?>
                        <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        <?php } ?>
                        <?php if ($inv_item['iv_count'] > 1) { ?>
                        <span class="absolute bottom-0.5 right-0.5 bg-mg-bg-primary/90 text-xs px-1 rounded text-mg-text-secondary"><?php echo $inv_item['iv_count']; ?></span>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
                <?php } else { ?>
                <div class="text-center py-6">
                    <p class="text-sm text-mg-text-muted mb-2">보유한 아이템이 없습니다</p>
                    <a href="<?php echo G5_BBS_URL; ?>/shop.php" class="text-sm text-mg-accent hover:underline">상점 가기</a>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- 선물함 -->
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
                <h2 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                    <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                    </svg>
                    선물함
                    <?php if ($gift_count > 0) { ?>
                    <span class="bg-mg-error text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $gift_count; ?></span>
                    <?php } ?>
                </h2>
                <a href="<?php echo G5_BBS_URL; ?>/gift.php" class="text-xs text-mg-accent hover:underline">전체보기</a>
            </div>
            <div class="p-4">
                <?php if ($gift_count > 0) { ?>
                <ul class="space-y-2">
                    <?php foreach ($pending_gifts as $gift) { ?>
                    <li class="flex items-center gap-3 p-2 bg-mg-bg-primary rounded-lg">
                        <div class="w-10 h-10 bg-mg-bg-tertiary rounded flex-shrink-0 flex items-center justify-center overflow-hidden">
                            <?php if (!empty($gift['si_image'])) { ?>
                            <img src="<?php echo $gift['si_image']; ?>" alt="" class="w-full h-full object-cover rounded">
                            <?php } else { ?>
                            <svg class="w-5 h-5 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                            </svg>
                            <?php } ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-mg-text-primary truncate"><?php echo htmlspecialchars($gift['si_name'] ?: '선물'); ?></p>
                            <p class="text-xs text-mg-text-muted">from <?php echo htmlspecialchars($gift['from_nick'] ?: $gift['mb_id_from']); ?></p>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
                <?php } else { ?>
                <div class="text-center py-6">
                    <p class="text-sm text-mg-text-muted">받은 선물이 없습니다</p>
                </div>
                <?php } ?>
            </div>
        </div>

    </div>

    <!-- 내 인장 -->
    <?php if ($_seal_enabled) { ?>
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="text-sm font-semibold text-mg-text-primary flex items-center gap-2">
                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                내 인장
            </h2>
            <a href="<?php echo G5_BBS_URL; ?>/seal_edit.php" data-no-spa class="text-xs text-mg-accent hover:underline">편집</a>
        </div>
        <div class="p-4">
            <?php if ($seal) { ?>
            <div style="max-width:800px;margin:0 auto;">
                <?php echo mg_render_seal($mb['mb_id'], 'full'); ?>
            </div>
            <p class="text-xs text-mg-text-muted mt-2">상태: <?php echo $seal['seal_use'] ? '<span class="text-green-400">ON</span>' : '<span class="text-mg-text-muted">OFF</span>'; ?></p>
            <?php } else { ?>
            <div class="text-center py-6">
                <p class="text-sm text-mg-text-muted mb-2">인장이 설정되지 않았습니다</p>
                <a href="<?php echo G5_BBS_URL; ?>/seal_edit.php" data-no-spa class="text-sm text-mg-accent hover:underline">인장 만들기</a>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>


</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
