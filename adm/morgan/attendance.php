<?php
/**
 * Morgan Edition - 출석 설정
 */

$sub_menu = "800500";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 탭
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'settings';

// 현재 설정값
$attendance_game = mg_get_config('attendance_game', 'dice');
$dice_bonus_multiplier = mg_get_config('dice_bonus_multiplier', '2');
$attendance_streak_bonus_days = mg_get_config('attendance_streak_bonus_days', '7');
$dice_count = mg_get_config('dice_count', '5');
$dice_sides = mg_get_config('dice_sides', '6');
$dice_combo_enabled = mg_get_config('dice_combo_enabled', '1');
$dice_reroll_count = mg_get_config('dice_reroll_count', '2');

// 족보 정의 + 현재 설정 포인트 로드
include_once(G5_PLUGIN_PATH.'/morgan/games/MG_Game_Dice.php');
$combo_defs = MG_Game_Dice::getComboDefinitions();
foreach ($combo_defs as $key => $combo) {
    $configVal = mg_get_config($combo['config_key'], '');
    $combo_defs[$key]['current_bonus'] = ($configVal !== '') ? (int)$configVal : $combo['bonus'];
}

// 기간 설정 (통계용)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

if ($tab == 'stats') {
    // 일별 출석 통계
    $daily_stats = array();
    $sql = "SELECT DATE(at_date) as at_day, COUNT(*) as cnt
            FROM {$g5['mg_attendance_table']}
            WHERE at_date >= '$start_date' AND at_date <= '$end_date 23:59:59'
            GROUP BY at_day
            ORDER BY at_day DESC";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $daily_stats[] = $row;
    }

    // 전체 출석 횟수
    $sql = "SELECT COUNT(*) as total FROM {$g5['mg_attendance_table']}
            WHERE at_date >= '$start_date' AND at_date <= '$end_date 23:59:59'";
    $total_row = sql_fetch($sql);
    $total_attendance = isset($total_row['total']) ? (int)$total_row['total'] : 0;

    // 출석 회원 수 (중복 제외)
    $sql = "SELECT COUNT(DISTINCT mb_id) as cnt FROM {$g5['mg_attendance_table']}
            WHERE at_date >= '$start_date' AND at_date <= '$end_date 23:59:59'";
    $unique_row = sql_fetch($sql);
    $unique_members = isset($unique_row['cnt']) ? (int)$unique_row['cnt'] : 0;

    // 최근 출석 목록
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $rows = 30;
    $offset = ($page - 1) * $rows;

    $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_attendance_table']}
            WHERE at_date >= '$start_date' AND at_date <= '$end_date 23:59:59'";
    $cnt_row = sql_fetch($sql);
    $total_count = isset($cnt_row['cnt']) ? (int)$cnt_row['cnt'] : 0;
    $total_page = ceil($total_count / $rows);

    $sql = "SELECT a.*, m.mb_nick
            FROM {$g5['mg_attendance_table']} a
            LEFT JOIN {$g5['member_table']} m ON a.mb_id = m.mb_id
            WHERE a.at_date >= '$start_date' AND a.at_date <= '$end_date 23:59:59'
            ORDER BY a.at_datetime DESC
            LIMIT $offset, $rows";
    $stats_result = sql_query($sql);
}

$g5['title'] = '출석 관리';
require_once __DIR__.'/_head.php';

// 게임 종류 이름
$game_names = [
    'dice' => '주사위',
    'fortune' => '운세뽑기',
    'lottery' => '종이뽑기'
];
?>

<!-- 탭 -->
<div style="margin-bottom:1rem;display:flex;gap:0.5rem;">
    <a href="?tab=settings" class="mg-btn <?php echo $tab == 'settings' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">설정</a>
    <a href="?tab=stats" class="mg-btn <?php echo $tab == 'stats' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">통계</a>
</div>

<?php if ($tab == 'settings') { ?>
<!-- 설정 탭 -->
<form name="fattendance" id="fattendance" method="post" action="./attendance_update.php">
    <input type="hidden" name="token" value="">

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">출석 게임 설정</div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label">출석 게임 종류</label>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.75rem 1rem;background:var(--mg-bg-primary);border-radius:0.5rem;border:2px solid <?php echo $attendance_game == 'dice' ? 'var(--mg-accent)' : 'transparent'; ?>;">
                        <input type="radio" name="attendance_game" value="dice" <?php echo $attendance_game == 'dice' ? 'checked' : ''; ?>>
                        <span>주사위</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.75rem 1rem;background:var(--mg-bg-primary);border-radius:0.5rem;border:2px solid transparent;opacity:0.5;" title="준비 중">
                        <input type="radio" name="attendance_game" value="fortune" disabled>
                        <span>운세뽑기 (준비 중)</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.75rem 1rem;background:var(--mg-bg-primary);border-radius:0.5rem;border:2px solid transparent;opacity:0.5;" title="준비 중">
                        <input type="radio" name="attendance_game" value="lottery" disabled>
                        <span>종이뽑기 (준비 중)</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">주사위 게임 설정</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="dice_count">주사위 개수</label>
                    <input type="number" name="dice_count" id="dice_count" value="<?php echo $dice_count; ?>" class="mg-form-input" min="1" max="5">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">굴릴 주사위 개수 (1~5, 기본 5)</div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="dice_sides">주사위 면 수</label>
                    <input type="number" name="dice_sides" id="dice_sides" value="<?php echo $dice_sides; ?>" class="mg-form-input" min="6">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">주사위 면 수 (기본 6 = d6)</div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="dice_bonus_multiplier">연속 출석 보너스 배율</label>
                    <input type="number" name="dice_bonus_multiplier" id="dice_bonus_multiplier" value="<?php echo $dice_bonus_multiplier; ?>" class="mg-form-input" min="1" step="0.5">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">연속 출석 달성 시 포인트 배율 (예: 2 = 2배)</div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="attendance_streak_bonus_days">연속 출석 보너스 일수</label>
                    <input type="number" name="attendance_streak_bonus_days" id="attendance_streak_bonus_days" value="<?php echo $attendance_streak_bonus_days; ?>" class="mg-form-input" min="2">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">보너스가 적용되는 연속 출석 일수 (예: 7 = 7일 연속)</div>
                </div>
            </div>

            <div class="mg-alert mg-alert-info" style="margin-top:1rem;">
                <strong>포인트 계산:</strong> 족보 적중 시 해당 족보 포인트 지급, 꽝이면 주사위 합산만큼 지급.<br>
                <strong>크리티컬:</strong> 최대값(d6에서 6) 등장 시 ×1.5배 (야찌 제외).
                <strong>연속 출석:</strong> <?php echo $attendance_streak_bonus_days; ?>일 연속 시 ×<?php echo $dice_bonus_multiplier; ?>배.
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">족보 콤보 & 리롤</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="dice_combo_enabled">족보 콤보 시스템</label>
                    <select name="dice_combo_enabled" id="dice_combo_enabled" class="mg-form-input">
                        <option value="1" <?php echo $dice_combo_enabled == '1' ? 'selected' : ''; ?>>사용</option>
                        <option value="0" <?php echo $dice_combo_enabled == '0' ? 'selected' : ''; ?>>미사용</option>
                    </select>
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">족보 조합에 따른 보너스 포인트 (5d6 권장)</div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="dice_reroll_count">리롤 횟수</label>
                    <input type="number" name="dice_reroll_count" id="dice_reroll_count" value="<?php echo $dice_reroll_count; ?>" class="mg-form-input" min="0" max="5">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">다시 굴리기 기회 (0=리롤 없이 즉시 결과, 기본 2)</div>
                </div>
            </div>

            <!-- 족보별 포인트 설정 -->
            <div style="margin-top:1rem;">
                <div style="font-size:0.85rem;font-weight:600;color:var(--mg-text-primary);margin-bottom:0.5rem;">족보별 포인트 설정</div>
                <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-bottom:0.75rem;">족보 적중 시 지급할 포인트. 0으로 설정하면 해당 족보 비활성화. 꽝 = 주사위 합산 지급.</div>
                <table class="mg-table" style="font-size:0.8rem;">
                    <thead>
                        <tr>
                            <th>족보</th>
                            <th>조건</th>
                            <th style="text-align:right;width:120px;">포인트</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($combo_defs as $key => $combo) { ?>
                        <tr>
                            <td style="font-weight:600;color:var(--mg-accent);"><?php echo htmlspecialchars($combo['name']); ?></td>
                            <td style="color:var(--mg-text-secondary);"><?php echo htmlspecialchars($combo['desc']); ?></td>
                            <td style="text-align:right;">
                                <input type="number" name="<?php echo $combo['config_key']; ?>" value="<?php echo $combo['current_bonus']; ?>" class="mg-form-input" style="width:100px;text-align:right;" min="0">
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td style="color:var(--mg-text-muted);">꽝</td>
                            <td style="color:var(--mg-text-muted);">위 족보 미적중</td>
                            <td style="text-align:right;color:var(--mg-text-muted);font-size:0.75rem;">주사위 합산</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </div>
</form>

<?php } else { ?>
<!-- 통계 탭 -->

<!-- 검색 -->
<div class="mg-card" style="margin-bottom:1rem;">
    <div class="mg-card-body" style="padding:1rem;">
        <form name="fsearch" id="fsearch" method="get" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
            <input type="hidden" name="tab" value="stats">
            <span style="color:var(--mg-text-secondary);">기간:</span>
            <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="mg-form-input" style="width:auto;">
            <span style="color:var(--mg-text-muted);">~</span>
            <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="mg-form-input" style="width:auto;">
            <button type="submit" class="mg-btn mg-btn-primary">조회</button>
        </form>
    </div>
</div>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">총 출석</div>
        <div class="mg-stat-value"><?php echo number_format($total_attendance); ?>회</div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">출석 회원</div>
        <div class="mg-stat-value"><?php echo number_format($unique_members); ?>명</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;">
    <!-- 일별 통계 -->
    <div class="mg-card">
        <div class="mg-card-header">일별 출석 통계</div>
        <div class="mg-card-body" style="padding:0;max-height:500px;overflow-y:auto;">
            <table class="mg-table">
                <thead>
                    <tr>
                        <th>날짜</th>
                        <th style="text-align:right;">출석 수</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daily_stats as $stat) { ?>
                    <tr>
                        <td><?php echo $stat['at_day']; ?></td>
                        <td style="text-align:right;"><?php echo number_format($stat['cnt']); ?>명</td>
                    </tr>
                    <?php } ?>
                    <?php if (count($daily_stats) == 0) { ?>
                    <tr>
                        <td colspan="2" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">출석 기록이 없습니다.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 출석 목록 -->
    <div class="mg-card">
        <div class="mg-card-header">출석 상세 목록</div>
        <div class="mg-card-body" style="padding:0;overflow-x:auto;">
            <table class="mg-table">
                <thead>
                    <tr>
                        <th>회원ID</th>
                        <th>닉네임</th>
                        <th>게임</th>
                        <th>포인트</th>
                        <th>출석일시</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = sql_fetch_array($stats_result)) { ?>
                    <tr>
                        <td><?php echo $row['mb_id']; ?></td>
                        <td><?php echo $row['mb_nick']; ?></td>
                        <td><?php echo $game_names[$row['at_game_type']] ?? $row['at_game_type']; ?></td>
                        <td style="color:var(--mg-accent);">+<?php echo number_format($row['at_point']); ?>P</td>
                        <td><?php echo $row['at_datetime']; ?></td>
                        <td style="color:var(--mg-text-muted);"><?php echo $row['at_ip']; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if ($total_count == 0) { ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">출석 기록이 없습니다.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 페이지네이션 -->
<?php if ($total_page > 1) { ?>
<div class="mg-pagination">
    <?php
    $start_page_num = max(1, $page - 2);
    $end_page_num = min($total_page, $page + 2);
    $base_url = '?tab=stats&start_date='.$start_date.'&end_date='.$end_date.'&page=';

    if ($page > 1) {
        echo '<a href="'.$base_url.'1">&laquo;</a>';
        echo '<a href="'.$base_url.($page-1).'">&lsaquo;</a>';
    }

    for ($i = $start_page_num; $i <= $end_page_num; $i++) {
        if ($i == $page) {
            echo '<span class="active">'.$i.'</span>';
        } else {
            echo '<a href="'.$base_url.$i.'">'.$i.'</a>';
        }
    }

    if ($page < $total_page) {
        echo '<a href="'.$base_url.($page+1).'">&rsaquo;</a>';
        echo '<a href="'.$base_url.$total_page.'">&raquo;</a>';
    }
    ?>
</div>
<?php } ?>

<?php } ?>

<style>
@media (max-width: 900px) {
    div[style*="grid-template-columns:1fr 2fr"],
    div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php
require_once __DIR__.'/_tail.php';
?>
