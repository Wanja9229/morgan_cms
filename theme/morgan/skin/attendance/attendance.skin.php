<?php
/**
 * Morgan Edition - 출석체크 스킨
 */

if (!defined('_GNUBOARD_')) exit;

// Dice-Box 로더
include_once(G5_PLUGIN_PATH.'/dice-box/dice-box-loader.php');
?>

<div class="mg-inner">
    <!-- 출석 요약 -->
    <div class="card mb-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-sm text-mg-text-muted">연속 출석</p>
                <p class="text-3xl font-bold text-mg-accent"><?php echo $streakDays; ?><span class="text-lg">일</span></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-mg-text-muted">이번 달 출석</p>
                <p class="text-xl font-semibold text-mg-text-primary"><?php echo $monthlyCount; ?>일 <span class="text-mg-accent">(+<?php echo number_format($monthlyPoint); ?>P)</span></p>
            </div>
        </div>
    </div>

    <!-- 게임 영역 -->
    <div class="card mb-6">
        <h2 class="card-header mb-4">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            오늘의 출석체크
        </h2>

        <?php if ($hasAttended): ?>
            <!-- 이미 출석한 경우 -->
            <?php $resultData = json_decode($todayAttendance['at_game_result'], true); ?>
            <div class="text-center py-8">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-mg-success/20 flex items-center justify-center">
                    <svg class="w-10 h-10 text-mg-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-lg font-semibold text-mg-text-primary mb-2">오늘 출석 완료!</p>
                <?php if (!empty($resultData['dice'])): ?>
                <div class="mg-dice-result" style="margin-bottom:0.75rem;">
                    <?php foreach ($resultData['dice'] as $val): ?>
                    <span class="mg-die mg-die--settled" data-value="<?php echo $val; ?>"><?php echo $val; ?></span>
                    <?php endforeach; ?>
                </div>
                <?php elseif (!empty($resultData['star'])): ?>
                <?php
                    $fstar = (int)$resultData['star'];
                    $fcolors = [1=>'#6b7280', 2=>'#22c55e', 3=>'#3b82f6', 4=>'#a855f7', 5=>'#f59f0a'];
                    $fc = $fcolors[$fstar] ?? '#6b7280';
                ?>
                <div style="text-align:center;margin-bottom:0.75rem;">
                    <div style="font-size:1.5rem;color:<?php echo $fc; ?>;margin-bottom:0.5rem;"><?php echo str_repeat('★', $fstar) . str_repeat('☆', 5 - $fstar); ?></div>
                    <div style="font-size:1rem;color:var(--mg-text-primary);"><?php echo htmlspecialchars($resultData['text'] ?? ''); ?></div>
                </div>
                <?php elseif (!empty($resultData['number'])): ?>
                <?php
                    $lrank = (int)($resultData['rank'] ?? 5);
                    $lcolors = [1=>'#f59f0a', 2=>'#a855f7', 3=>'#3b82f6', 4=>'#22c55e', 5=>'#6b7280'];
                    $lc = $lcolors[$lrank] ?? '#6b7280';
                ?>
                <div style="text-align:center;margin-bottom:0.75rem;">
                    <div style="display:inline-flex;align-items:center;justify-content:center;width:3rem;height:3rem;border-radius:50%;background:<?php echo $lc; ?>;color:#fff;font-size:1.25rem;font-weight:700;margin-bottom:0.5rem;"><?php echo (int)$resultData['number']; ?></div>
                    <div style="font-size:1rem;color:<?php echo $lc; ?>;font-weight:600;"><?php echo htmlspecialchars($resultData['rankName'] ?? ''); ?> 당첨!</div>
                    <?php if (!empty($resultData['boardCompleted'])): ?>
                    <div style="font-size:0.85rem;color:var(--mg-accent);margin-top:0.25rem;">판 완성!</div>
                    <?php else: ?>
                    <div style="font-size:0.85rem;color:var(--mg-text-muted);margin-top:0.25rem;"><?php echo (int)$resultData['pickedCount']; ?>/<?php echo (int)$resultData['boardSize']; ?> 진행</div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <p class="text-mg-text-muted">
                    <?php
                    echo '+' . number_format($todayAttendance['at_point']) . 'P 획득';
                    if (!empty($resultData['comboName'])) echo ' (' . htmlspecialchars($resultData['comboName']) . '!)';
                    if (!empty($resultData['isBonus'])) echo ' (' . (int)mg_get_config('attendance_streak_bonus_days', 7) . '일 연속!)';
                    ?>
                </p>
            </div>
        <?php else: ?>
            <!-- 출석 전: 게임 UI -->
            <?php echo $game->renderUI(); ?>
        <?php endif; ?>
    </div>

    <!-- 이번 달 출석 달력 -->
    <div class="card">
        <h2 class="card-header mb-4">
            <svg class="w-5 h-5 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <?php echo date('Y년 n월'); ?> 출석 현황
        </h2>

        <?php
        $year = (int)date('Y');
        $month = (int)date('m');
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $daysInMonth = (int)date('t', $firstDay);
        $startWeekday = (int)date('w', $firstDay);
        ?>

        <div class="grid grid-cols-7 gap-1 text-center text-sm">
            <!-- 요일 헤더 -->
            <div class="py-2 text-mg-error font-medium">일</div>
            <div class="py-2 text-mg-text-muted font-medium">월</div>
            <div class="py-2 text-mg-text-muted font-medium">화</div>
            <div class="py-2 text-mg-text-muted font-medium">수</div>
            <div class="py-2 text-mg-text-muted font-medium">목</div>
            <div class="py-2 text-mg-text-muted font-medium">금</div>
            <div class="py-2 text-mg-accent font-medium">토</div>

            <!-- 빈 칸 (1일 전) -->
            <?php for ($i = 0; $i < $startWeekday; $i++): ?>
                <div class="py-2"></div>
            <?php endfor; ?>

            <!-- 날짜 -->
            <?php for ($day = 1; $day <= $daysInMonth; $day++):
                $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                $isToday = ($dateStr === $today);
                $attended = isset($monthlyAttendance[$dateStr]);
                $point = $attended ? $monthlyAttendance[$dateStr]['at_point'] : 0;
            ?>
                <div class="py-2 relative group <?php echo $isToday ? 'bg-mg-accent/20 rounded' : ''; ?>">
                    <span class="<?php echo $attended ? 'text-mg-accent font-bold' : 'text-mg-text-secondary'; ?> <?php echo $isToday ? 'text-mg-accent' : ''; ?>">
                        <?php echo $day; ?>
                    </span>
                    <?php if ($attended): ?>
                        <span class="absolute -top-1 -right-1 w-2 h-2 bg-mg-accent rounded-full"></span>
                        <span class="hidden group-hover:block absolute bottom-full left-1/2 -translate-x-1/2 bg-mg-bg-primary border border-mg-bg-tertiary text-xs px-2 py-1 rounded whitespace-nowrap z-10">
                            +<?php echo number_format($point); ?>P
                        </span>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>

        <div class="mt-4 pt-4 border-t border-mg-bg-tertiary flex items-center gap-4 text-sm text-mg-text-muted">
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 bg-mg-accent rounded-full"></span>
                출석 완료
            </span>
            <span class="flex items-center gap-1">
                <span class="w-4 h-4 bg-mg-accent/20 rounded"></span>
                오늘
            </span>
        </div>
    </div>
</div>

<!-- 게임 CSS -->
<style>
<?php echo $game->getCSS(); ?>

/* 달력 그리드 */
.grid-cols-7 {
    grid-template-columns: repeat(7, minmax(0, 1fr));
}

.mg-success { color: #22c55e; }
.bg-mg-success\/20 { background-color: rgba(34, 197, 94, 0.2); }
</style>

<!-- Dice-Box 3D (주사위 게임 + 출석 전에만 로드) -->
<?php if (!$hasAttended && $game->getCode() === 'dice'): ?>
<?php mg_dice_box_scripts(); ?>
<?php endif; ?>

<!-- 게임 JS -->
<script>
<?php echo $game->getJavaScript(); ?>
</script>
