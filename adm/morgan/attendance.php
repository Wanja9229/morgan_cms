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
$dice_reroll_count = mg_get_config('dice_reroll_count', '2');

// 족보 정의 + 현재 설정 포인트 로드
include_once(G5_PLUGIN_PATH.'/morgan/games/MG_Game_Dice.php');
$combo_defs = MG_Game_Dice::getComboDefinitions();
foreach ($combo_defs as $key => $combo) {
    $configVal = mg_get_config($combo['config_key'], '');
    $combo_defs[$key]['current_bonus'] = ($configVal !== '') ? (int)$configVal : $combo['bonus'];
}

// 기간 설정 (통계용) — 날짜 형식 검증
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date)) $start_date = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) $end_date = date('Y-m-d');

if ($tab == 'fortune') {
    $fortune_list = array();
    $result = sql_query("SELECT * FROM {$g5['mg_game_fortune_table']} ORDER BY gf_sort, gf_id");
    while ($row = sql_fetch_array($result)) {
        $fortune_list[] = $row;
    }
}

if ($tab == 'lottery') {
    // 판 설정
    $lottery_board = sql_fetch("SELECT * FROM {$g5['mg_game_lottery_board_table']} WHERE glb_id = 1");
    if (empty($lottery_board['glb_id'])) {
        $lottery_board = ['glb_id' => 1, 'glb_size' => 100, 'glb_bonus_point' => 500, 'glb_use' => 1];
    }

    // 등수 목록
    $lottery_prizes = array();
    $result = sql_query("SELECT * FROM {$g5['mg_game_lottery_prize_table']} ORDER BY glp_rank");
    while ($row = sql_fetch_array($result)) {
        $lottery_prizes[] = $row;
    }

    // 등수별 개수 합계
    $prize_total_count = 0;
    foreach ($lottery_prizes as $p) {
        $prize_total_count += (int)$p['glp_count'];
    }
}

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
<div style="margin-bottom:1rem;display:flex;gap:0.5rem;flex-wrap:wrap;">
    <a href="?tab=settings" class="mg-btn <?php echo $tab == 'settings' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">설정</a>
    <a href="?tab=dice" class="mg-btn <?php echo $tab == 'dice' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">주사위 관리</a>
    <a href="?tab=fortune" class="mg-btn <?php echo $tab == 'fortune' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">운세 관리</a>
    <a href="?tab=lottery" class="mg-btn <?php echo $tab == 'lottery' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">종이뽑기 관리</a>
    <a href="?tab=stats" class="mg-btn <?php echo $tab == 'stats' ? 'mg-btn-primary' : 'mg-btn-secondary'; ?>">통계</a>
</div>

<?php if ($tab == 'settings') { ?>
<!-- 설정 탭 -->
<form name="fattendance" id="fattendance" method="post" action="./attendance_update.php">
    <input type="hidden" name="token" value="">

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">출석 게임 종류</div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label">사용할 미니게임</label>
                <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.75rem 1rem;background:var(--mg-bg-primary);border-radius:0.5rem;border:2px solid <?php echo $attendance_game == 'dice' ? 'var(--mg-accent)' : 'transparent'; ?>;">
                        <input type="radio" name="attendance_game" value="dice" <?php echo $attendance_game == 'dice' ? 'checked' : ''; ?>>
                        <span>주사위</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.75rem 1rem;background:var(--mg-bg-primary);border-radius:0.5rem;border:2px solid <?php echo $attendance_game == 'fortune' ? 'var(--mg-accent)' : 'transparent'; ?>;">
                        <input type="radio" name="attendance_game" value="fortune" <?php echo $attendance_game == 'fortune' ? 'checked' : ''; ?>>
                        <span>운세뽑기</span>
                    </label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.75rem 1rem;background:var(--mg-bg-primary);border-radius:0.5rem;border:2px solid <?php echo $attendance_game == 'lottery' ? 'var(--mg-accent)' : 'transparent'; ?>;">
                        <input type="radio" name="attendance_game" value="lottery" <?php echo $attendance_game == 'lottery' ? 'checked' : ''; ?>>
                        <span>종이뽑기</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">연속 출석 보너스</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="dice_bonus_multiplier">보너스 배율</label>
                    <input type="number" name="dice_bonus_multiplier" id="dice_bonus_multiplier" value="<?php echo $dice_bonus_multiplier; ?>" class="mg-form-input" min="1" step="0.5">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">연속 출석 달성 시 포인트 배율 (예: 2 = 2배)</div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="attendance_streak_bonus_days">보너스 필요 일수</label>
                    <input type="number" name="attendance_streak_bonus_days" id="attendance_streak_bonus_days" value="<?php echo $attendance_streak_bonus_days; ?>" class="mg-form-input" min="2">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">보너스가 적용되는 연속 출석 일수 (예: 7 = 7일 연속)</div>
                </div>
            </div>
            <div class="mg-alert mg-alert-info" style="margin-top:1rem;">
                모든 미니게임에 공통 적용됩니다. <?php echo $attendance_streak_bonus_days; ?>일 연속 출석 시 포인트 ×<?php echo $dice_bonus_multiplier; ?>배.
            </div>
        </div>
    </div>

    <div style="display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </div>
</form>

<?php } elseif ($tab == 'dice') { ?>
<!-- 주사위 관리 탭 -->
<form name="fdice" id="fdice" method="post" action="./attendance_update.php">
    <input type="hidden" name="mode" value="dice_settings">

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">리롤 설정</div>
        <div class="mg-card-body">
            <div class="mg-form-group">
                <label class="mg-form-label" for="dice_reroll_count">리롤 횟수</label>
                <input type="number" name="dice_reroll_count" id="dice_reroll_count" value="<?php echo $dice_reroll_count; ?>" class="mg-form-input" style="max-width:200px;" min="0" max="5">
                <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">다시 굴리기 기회 (0=리롤 없이 즉시 결과, 기본 2)</div>
            </div>

            <div class="mg-alert mg-alert-info" style="margin-top:1rem;">
                <strong>포인트 계산:</strong> 족보 적중 시 해당 족보 포인트 지급, 꽝이면 주사위 합산만큼 지급.
            </div>
        </div>
    </div>

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">족보별 포인트</div>
        <div class="mg-card-body" style="padding:0;overflow-x:auto;">
            <table class="mg-table" style="font-size:0.85rem;">
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

    <div style="display:flex;gap:0.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">저장</button>
    </div>
</form>

<?php } elseif ($tab == 'fortune') { ?>
<!-- 운세 관리 탭 -->

<!-- 헤더 + 추가 버튼 -->
<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <span>운세 목록 (<?php echo count($fortune_list); ?>개)</span>
        <button type="button" class="mg-btn mg-btn-primary" style="font-size:0.8rem;padding:0.4rem 0.75rem;" onclick="openFortuneModal()">+ 추가</button>
    </div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:120px;">별점</th>
                    <th>텍스트</th>
                    <th style="width:80px;text-align:right;">포인트</th>
                    <th style="width:60px;text-align:center;">등장</th>
                    <th style="width:120px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $star_colors = [1=>'#6b7280', 2=>'#22c55e', 3=>'#3b82f6', 4=>'#a855f7', 5=>'#f59f0a'];
                foreach ($fortune_list as $f) {
                    $star = (int)$f['gf_star'];
                    $color = $star_colors[$star] ?? '#6b7280';
                ?>
                <tr>
                    <td>
                        <span style="color:<?php echo $color; ?>;"><?php echo str_repeat('★', $star) . str_repeat('☆', 5 - $star); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($f['gf_text']); ?></td>
                    <td style="text-align:right;color:var(--mg-accent);font-weight:600;">+<?php echo number_format($f['gf_point']); ?>P</td>
                    <td style="text-align:center;">
                        <form method="post" action="./attendance_update.php" style="display:inline;">
                            <input type="hidden" name="mode" value="fortune_toggle">
                            <input type="hidden" name="gf_id" value="<?php echo $f['gf_id']; ?>">
                            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:0.8rem;padding:0.2rem 0.5rem;border-radius:0.25rem;color:#fff;<?php echo $f['gf_use'] ? 'background:#22c55e;' : 'background:#6b7280;'; ?>" title="<?php echo $f['gf_use'] ? '등장중 — 클릭하면 제외' : '제외됨 — 클릭하면 등장'; ?>">
                                <?php echo $f['gf_use'] ? 'ON' : 'OFF'; ?>
                            </button>
                        </form>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:0.25rem;justify-content:center;">
                            <button type="button" class="mg-btn mg-btn-secondary" style="font-size:0.75rem;padding:0.25rem 0.5rem;" onclick="openFortuneModal(<?php echo $f['gf_id']; ?>, <?php echo $star; ?>, <?php echo htmlspecialchars(json_encode($f['gf_text']), ENT_QUOTES); ?>, <?php echo (int)$f['gf_point']; ?>)">수정</button>
                            <form method="post" action="./attendance_update.php" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                <input type="hidden" name="mode" value="fortune_delete">
                                <input type="hidden" name="gf_id" value="<?php echo $f['gf_id']; ?>">
                                <button type="submit" class="mg-btn" style="font-size:0.75rem;padding:0.25rem 0.5rem;background:var(--mg-error);color:#fff;">삭제</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($fortune_list)) { ?>
                <tr>
                    <td colspan="5" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">등록된 운세가 없습니다.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 등장 확률 설정 -->
<div class="mg-card" style="margin-top:1.5rem;">
    <div class="mg-card-header">별점별 등장 확률</div>
    <div class="mg-card-body">
        <form method="post" action="./attendance_update.php">
            <input type="hidden" name="mode" value="fortune_weights">
            <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:end;">
                <?php
                $w_colors = [1=>'#6b7280', 2=>'#22c55e', 3=>'#3b82f6', 4=>'#a855f7', 5=>'#f59f0a'];
                $w_defaults = [1=>5, 2=>4, 3=>3, 4=>2, 5=>1];
                for ($s = 1; $s <= 5; $s++) {
                    $w_val = (int)mg_get_config('fortune_weight_' . $s, $w_defaults[$s]);
                ?>
                <div style="text-align:center;min-width:60px;">
                    <div style="color:<?php echo $w_colors[$s]; ?>;font-size:0.85rem;margin-bottom:0.25rem;"><?php echo str_repeat('★', $s); ?></div>
                    <input type="number" name="fortune_weight_<?php echo $s; ?>" value="<?php echo $w_val; ?>" class="mg-form-input" style="width:60px;text-align:center;" min="0" max="99">
                </div>
                <?php } ?>
                <button type="submit" class="mg-btn mg-btn-primary" style="padding:0.4rem 0.75rem;font-size:0.8rem;">저장</button>
            </div>
            <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.5rem;">
                숫자가 클수록 자주 등장합니다. 0으로 설정하면 해당 별점은 등장하지 않습니다.
            </div>
        </form>
    </div>
</div>

<!-- 추가/수정 모달 -->
<div id="fortune-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border-radius:0.75rem;padding:1.5rem;width:100%;max-width:480px;border:1px solid var(--mg-bg-tertiary);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 id="fortune-modal-title" style="font-size:1rem;font-weight:600;color:var(--mg-text-primary);margin:0;">운세 추가</h3>
            <button type="button" onclick="closeFortuneModal()" style="background:none;border:none;color:var(--mg-text-muted);cursor:pointer;font-size:1.5rem;line-height:1;">&times;</button>
        </div>
        <form method="post" action="./attendance_update.php">
            <input type="hidden" name="mode" id="fortune-mode" value="fortune_add">
            <input type="hidden" name="gf_id" id="fortune-id" value="">

            <div class="mg-form-group" style="margin-bottom:1rem;">
                <label class="mg-form-label">별점</label>
                <select name="gf_star" id="fortune-star" class="mg-form-input">
                    <?php for ($s = 1; $s <= 5; $s++) { ?>
                    <option value="<?php echo $s; ?>"><?php echo str_repeat('★', $s); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="mg-form-group" style="margin-bottom:1rem;">
                <label class="mg-form-label">운세 텍스트</label>
                <input type="text" name="gf_text" id="fortune-text" class="mg-form-input" required placeholder="운세 문구를 입력하세요">
            </div>
            <div class="mg-form-group" style="margin-bottom:1.5rem;">
                <label class="mg-form-label">포인트</label>
                <input type="number" name="gf_point" id="fortune-point" class="mg-form-input" value="10" min="0" required>
            </div>
            <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                <button type="button" onclick="closeFortuneModal()" class="mg-btn mg-btn-secondary">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary" id="fortune-submit-btn">추가</button>
            </div>
        </form>
    </div>
</div>

<script>
function openFortuneModal(id, star, text, point) {
    var modal = document.getElementById('fortune-modal');
    var isEdit = typeof id !== 'undefined';

    document.getElementById('fortune-mode').value = isEdit ? 'fortune_edit' : 'fortune_add';
    document.getElementById('fortune-id').value = id || '';
    document.getElementById('fortune-star').value = star || 1;
    document.getElementById('fortune-text').value = text || '';
    document.getElementById('fortune-point').value = point || 10;
    document.getElementById('fortune-modal-title').textContent = isEdit ? '운세 수정' : '운세 추가';
    document.getElementById('fortune-submit-btn').textContent = isEdit ? '수정' : '추가';

    modal.style.display = 'flex';
}
function closeFortuneModal() {
    document.getElementById('fortune-modal').style.display = 'none';
}
document.getElementById('fortune-modal').addEventListener('click', function(e) {
    if (e.target === this) closeFortuneModal();
});
</script>

<?php } elseif ($tab == 'lottery') { ?>
<!-- 종이뽑기 관리 탭 -->

<!-- 판 설정 -->
<form method="post" action="./attendance_update.php">
    <input type="hidden" name="mode" value="lottery_board">

    <div class="mg-card" style="margin-bottom:1.5rem;">
        <div class="mg-card-header">판 설정</div>
        <div class="mg-card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label" for="glb_size">판 크기 (칸 수)</label>
                    <input type="number" name="glb_size" id="glb_size" value="<?php echo (int)$lottery_board['glb_size']; ?>" class="mg-form-input" min="10" max="200">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">판 위의 전체 칸 수 (기본 100)</div>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label" for="glb_bonus_point">판 완성 보너스</label>
                    <input type="number" name="glb_bonus_point" id="glb_bonus_point" value="<?php echo (int)$lottery_board['glb_bonus_point']; ?>" class="mg-form-input" min="0">
                    <div style="font-size:0.75rem;color:var(--mg-text-muted);margin-top:0.25rem;">판 전체를 뽑았을 때 추가 포인트</div>
                </div>
            </div>
            <?php if ($prize_total_count != (int)$lottery_board['glb_size']) { ?>
            <div class="mg-alert mg-alert-warning" style="margin-top:1rem;">
                등수별 개수 합계(<?php echo $prize_total_count; ?>)가 판 크기(<?php echo (int)$lottery_board['glb_size']; ?>)와 다릅니다. 부족한 칸은 최저 등수로 자동 채워집니다.
            </div>
            <?php } ?>
        </div>
    </div>

    <div style="display:flex;gap:0.5rem;margin-bottom:1.5rem;">
        <button type="submit" class="mg-btn mg-btn-primary">판 설정 저장</button>
    </div>
</form>

<!-- 등수 설정 -->
<div class="mg-card">
    <div class="mg-card-header" style="display:flex;justify-content:space-between;align-items:center;">
        <span>등수 설정 (<?php echo count($lottery_prizes); ?>개)</span>
        <button type="button" class="mg-btn mg-btn-primary" style="font-size:0.8rem;padding:0.4rem 0.75rem;" onclick="openLotteryModal()">+ 추가</button>
    </div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:60px;">등수</th>
                    <th>이름</th>
                    <th style="width:80px;text-align:right;">개수</th>
                    <th style="width:100px;text-align:right;">포인트</th>
                    <th style="width:60px;text-align:center;">등장</th>
                    <th style="width:120px;text-align:center;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank_colors = [1=>'#f59f0a', 2=>'#a855f7', 3=>'#3b82f6', 4=>'#22c55e', 5=>'#6b7280'];
                foreach ($lottery_prizes as $p) {
                    $rc = $rank_colors[(int)$p['glp_rank']] ?? '#6b7280';
                ?>
                <tr>
                    <td><span style="color:<?php echo $rc; ?>;font-weight:600;"><?php echo (int)$p['glp_rank']; ?>등</span></td>
                    <td><?php echo htmlspecialchars($p['glp_name']); ?></td>
                    <td style="text-align:right;"><?php echo number_format($p['glp_count']); ?>개</td>
                    <td style="text-align:right;color:var(--mg-accent);font-weight:600;">+<?php echo number_format($p['glp_point']); ?>P</td>
                    <td style="text-align:center;">
                        <form method="post" action="./attendance_update.php" style="display:inline;">
                            <input type="hidden" name="mode" value="lottery_toggle">
                            <input type="hidden" name="glp_id" value="<?php echo $p['glp_id']; ?>">
                            <button type="submit" style="background:none;border:none;cursor:pointer;font-size:0.8rem;padding:0.2rem 0.5rem;border-radius:0.25rem;color:#fff;<?php echo $p['glp_use'] ? 'background:#22c55e;' : 'background:#6b7280;'; ?>" title="<?php echo $p['glp_use'] ? '등장중 — 클릭하면 제외' : '제외됨 — 클릭하면 등장'; ?>">
                                <?php echo $p['glp_use'] ? 'ON' : 'OFF'; ?>
                            </button>
                        </form>
                    </td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:0.25rem;justify-content:center;">
                            <button type="button" class="mg-btn mg-btn-secondary" style="font-size:0.75rem;padding:0.25rem 0.5rem;" onclick="openLotteryModal(<?php echo $p['glp_id']; ?>, <?php echo (int)$p['glp_rank']; ?>, <?php echo htmlspecialchars(json_encode($p['glp_name']), ENT_QUOTES); ?>, <?php echo (int)$p['glp_count']; ?>, <?php echo (int)$p['glp_point']; ?>)">수정</button>
                            <form method="post" action="./attendance_update.php" style="display:inline;" onsubmit="return confirm('정말 삭제하시겠습니까?');">
                                <input type="hidden" name="mode" value="lottery_delete">
                                <input type="hidden" name="glp_id" value="<?php echo $p['glp_id']; ?>">
                                <button type="submit" class="mg-btn" style="font-size:0.75rem;padding:0.25rem 0.5rem;background:var(--mg-error);color:#fff;">삭제</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($lottery_prizes)) { ?>
                <tr>
                    <td colspan="6" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">등록된 등수가 없습니다.</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 등수 추가/수정 모달 -->
<div id="lottery-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
    <div style="background:var(--mg-bg-secondary);border-radius:0.75rem;padding:1.5rem;width:100%;max-width:480px;border:1px solid var(--mg-bg-tertiary);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
            <h3 id="lottery-modal-title" style="font-size:1rem;font-weight:600;color:var(--mg-text-primary);margin:0;">등수 추가</h3>
            <button type="button" onclick="closeLotteryModal()" style="background:none;border:none;color:var(--mg-text-muted);cursor:pointer;font-size:1.5rem;line-height:1;">&times;</button>
        </div>
        <form method="post" action="./attendance_update.php">
            <input type="hidden" name="mode" id="lottery-mode" value="lottery_add">
            <input type="hidden" name="glp_id" id="lottery-id" value="">

            <div class="mg-form-group" style="margin-bottom:1rem;">
                <label class="mg-form-label">등수</label>
                <input type="number" name="glp_rank" id="lottery-rank" class="mg-form-input" value="1" min="1" max="10" required>
            </div>
            <div class="mg-form-group" style="margin-bottom:1rem;">
                <label class="mg-form-label">이름</label>
                <input type="text" name="glp_name" id="lottery-name" class="mg-form-input" required placeholder="예: 1등">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">개수 (한 판당)</label>
                    <input type="number" name="glp_count" id="lottery-count" class="mg-form-input" value="1" min="1" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">포인트</label>
                    <input type="number" name="glp_point" id="lottery-point" class="mg-form-input" value="10" min="0" required>
                </div>
            </div>
            <div style="display:flex;gap:0.5rem;justify-content:flex-end;">
                <button type="button" onclick="closeLotteryModal()" class="mg-btn mg-btn-secondary">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary" id="lottery-submit-btn">추가</button>
            </div>
        </form>
    </div>
</div>

<script>
function openLotteryModal(id, rank, name, count, point) {
    var modal = document.getElementById('lottery-modal');
    var isEdit = typeof id !== 'undefined';

    document.getElementById('lottery-mode').value = isEdit ? 'lottery_edit' : 'lottery_add';
    document.getElementById('lottery-id').value = id || '';
    document.getElementById('lottery-rank').value = rank || 1;
    document.getElementById('lottery-name').value = name || '';
    document.getElementById('lottery-count').value = count || 1;
    document.getElementById('lottery-point').value = point || 10;
    document.getElementById('lottery-modal-title').textContent = isEdit ? '등수 수정' : '등수 추가';
    document.getElementById('lottery-submit-btn').textContent = isEdit ? '수정' : '추가';

    modal.style.display = 'flex';
}
function closeLotteryModal() {
    document.getElementById('lottery-modal').style.display = 'none';
}
document.getElementById('lottery-modal').addEventListener('click', function(e) {
    if (e.target === this) closeLotteryModal();
});
</script>

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
                    <?php while ($stats_result && $row = sql_fetch_array($stats_result)) { ?>
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
