<?php
/**
 * Morgan Edition - Profile Skin
 *
 * 회원 프로필 조회 페이지
 */

if (!defined('_GNUBOARD_')) exit;

$is_self = ($member['mb_id'] === $mb['mb_id']);

// 대표 캐릭터
$profile_char = null;
if (function_exists('mg_get_main_character')) {
    $profile_char = mg_get_main_character($mb['mb_id']);
}

// 활성 칭호
$profile_titles = function_exists('mg_get_active_titles') ? mg_get_active_titles($mb['mb_id'], 0) : array('prefix' => '', 'suffix' => '');
?>

<div class="mg-inner" style="max-width:640px;margin:0 auto;">
    <div class="card">
        <!-- 프로필 헤더 -->
        <div class="flex items-center gap-5 mb-6">
            <div class="w-20 h-20 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-accent font-bold text-3xl flex-shrink-0">
                <?php if ($profile_char && !empty($profile_char['ch_thumb'])) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$profile_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover rounded-full">
                <?php } else { ?>
                <?php echo mb_substr($mb['mb_nick'], 0, 1); ?>
                <?php } ?>
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-xl font-bold text-mg-text-primary mb-1">
                    <?php
                    if ($profile_titles['prefix'] || $profile_titles['suffix']) {
                        $parts = array();
                        if ($profile_titles['prefix']) $parts[] = htmlspecialchars($profile_titles['prefix']);
                        if ($profile_titles['suffix']) $parts[] = htmlspecialchars($profile_titles['suffix']);
                        echo '<span class="mg-title">「'.implode(' ', $parts).'」</span> ';
                    }
                    echo get_text($mb['mb_nick']);
                    ?>
                </h1>
                <p class="text-sm text-mg-text-muted">@<?php echo $mb['mb_id']; ?> · Lv.<?php echo $mb['mb_level']; ?></p>
            </div>
        </div>

        <!-- 정보 테이블 -->
        <div class="space-y-3 mb-6">
            <div class="flex items-center justify-between py-2 border-b border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-muted">가입일</span>
                <span class="text-sm text-mg-text-primary">
                    <?php if ($member['mb_level'] >= $mb['mb_level']) { ?>
                    <?php echo substr($mb['mb_datetime'], 0, 10); ?> (<?php echo number_format($mb_reg_after); ?>일째)
                    <?php } else { ?>
                    <span class="text-mg-text-muted">비공개</span>
                    <?php } ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-muted">최종 접속</span>
                <span class="text-sm text-mg-text-primary">
                    <?php if ($member['mb_level'] >= $mb['mb_level']) { ?>
                    <?php echo $mb['mb_today_login'] ? substr($mb['mb_today_login'], 0, 10) : '-'; ?>
                    <?php } else { ?>
                    <span class="text-mg-text-muted">비공개</span>
                    <?php } ?>
                </span>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-muted">포인트</span>
                <span class="text-sm font-semibold text-mg-accent">
                    <?php echo function_exists('mg_point_format') ? mg_point_format($mb['mb_point']) : number_format($mb['mb_point']).'P'; ?>
                </span>
            </div>
            <?php if ($mb_homepage) { ?>
            <div class="flex items-center justify-between py-2 border-b border-mg-bg-tertiary">
                <span class="text-sm text-mg-text-muted">홈페이지</span>
                <a href="<?php echo $mb_homepage; ?>" target="_blank" rel="noopener" class="text-sm text-mg-accent hover:underline truncate max-w-[300px]"><?php echo $mb_homepage; ?></a>
            </div>
            <?php } ?>
        </div>

        <!-- 자기소개 -->
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-mg-text-primary mb-3">자기소개</h2>
            <div class="p-4 bg-mg-bg-primary rounded-lg text-sm text-mg-text-secondary leading-relaxed">
                <?php echo $mb_profile; ?>
            </div>
        </div>

        <!-- 대표 캐릭터 -->
        <?php if ($profile_char) { ?>
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-mg-text-primary mb-3">대표 캐릭터</h2>
            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $profile_char['ch_id']; ?>" class="flex items-center gap-3 p-3 bg-mg-bg-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors group">
                <div class="w-12 h-12 rounded-lg bg-mg-bg-tertiary flex-shrink-0 overflow-hidden">
                    <?php if (!empty($profile_char['ch_thumb'])) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$profile_char['ch_thumb']; ?>" alt="" class="w-full h-full object-cover">
                    <?php } else { ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-mg-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <?php } ?>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-mg-text-primary group-hover:text-mg-accent transition-colors"><?php echo htmlspecialchars($profile_char['ch_name']); ?></p>
                    <?php if (!empty($profile_char['ch_side'])) { ?>
                    <p class="text-xs text-mg-text-muted"><?php echo htmlspecialchars($profile_char['ch_side']); ?></p>
                    <?php } ?>
                </div>
            </a>
        </div>
        <?php } ?>

        <!-- 인장 -->
        <?php
        $_seal_on = function_exists('mg_config') && (mg_config('seal_enable', '1') == '1');
        $profile_seal = null;
        if ($_seal_on && function_exists('mg_get_seal')) {
            $profile_seal = mg_get_seal($mb['mb_id']);
        }
        if ($profile_seal && $profile_seal['seal_use']) { ?>
        <div class="mb-6">
            <h2 class="text-sm font-semibold text-mg-text-primary mb-3">인장</h2>
            <div style="max-width:600px;">
                <?php echo mg_render_seal($mb['mb_id'], 'full'); ?>
            </div>
        </div>
        <?php } ?>

        <!-- 하단 버튼 -->
        <div class="flex gap-3">
            <?php if ($is_self) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/member_confirm.php" class="btn btn-primary flex-1 text-center">회원정보 수정</a>
            <a href="<?php echo G5_BBS_URL; ?>/mypage.php" class="btn flex-1 text-center" style="background:var(--mg-bg-tertiary);color:var(--mg-text-primary);">마이페이지</a>
            <?php } else { ?>
            <a href="<?php echo G5_URL; ?>/bbs/memo.php?me_recv_mb_id=<?php echo $mb['mb_id']; ?>" class="btn btn-primary flex-1 text-center">쪽지 보내기</a>
            <?php } ?>
        </div>
    </div>
</div>
