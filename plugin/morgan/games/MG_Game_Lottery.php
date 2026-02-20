<?php
/**
 * Morgan Edition - 종이뽑기 미니게임
 *
 * 문방구 종이뽑기 스타일. 판(보드) 위 번호를 매일 하나씩 뽑아
 * 등수별 포인트를 획득. 판 완성 시 보너스.
 * 단일 play() 패턴 (멀티스텝 없음).
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Game_Base.php';

class MG_Game_Lottery extends MG_Game_Base {

    private $bonusMultiplier;
    private $streakBonusDays;

    // 등수별 색상
    private static $RANK_COLORS = [
        1 => '#f59f0a', // 금색
        2 => '#a855f7', // 보라
        3 => '#3b82f6', // 파랑
        4 => '#22c55e', // 초록
        5 => '#6b7280', // 회색
    ];

    // 등수별 라벨 (기본)
    private static $RANK_LABELS = [
        1 => '1등',
        2 => '2등',
        3 => '3등',
        4 => '4등',
        5 => '꽝',
    ];

    public function __construct() {
        $this->bonusMultiplier = (float)mg_get_config('dice_bonus_multiplier', 2);
        $this->streakBonusDays = (int)mg_get_config('attendance_streak_bonus_days', 7);
    }

    public function getCode(): string {
        return 'lottery';
    }

    public function getName(): string {
        return '종이뽑기';
    }

    public function getDescription(): string {
        return '매일 번호를 뽑아 등수별 포인트를 획득하세요! 판을 완성하면 보너스!';
    }

    /**
     * 게임 실행 (유저가 선택한 번호로 뽑기)
     */
    public function play(string $mb_id, int $chosenNumber = 0): array {
        if ($this->hasPlayedToday($mb_id)) {
            return ['success' => false, 'message' => '오늘은 이미 출석했습니다.'];
        }

        $board = $this->getActiveBoard();
        if (!$board) {
            return ['success' => false, 'message' => '종이뽑기 판이 설정되지 않았습니다.'];
        }

        $prizes = $this->loadPrizes();
        if (empty($prizes)) {
            return ['success' => false, 'message' => '등수 설정이 없습니다. 관리자에게 문의하세요.'];
        }

        $boardSize = (int)$board['glb_size'];

        $userState = $this->getUserState($mb_id, (int)$board['glb_id']);
        $picked = json_decode($userState['glu_picked'] ?: '[]', true) ?: [];

        $allNumbers = range(1, $boardSize);
        $remaining = array_values(array_diff($allNumbers, $picked));

        if (empty($remaining)) {
            $this->resetBoard($mb_id, (int)$board['glb_id']);
            $picked = [];
            $remaining = $allNumbers;
        }

        // 유저가 선택한 번호 검증
        if ($chosenNumber > 0 && $chosenNumber <= $boardSize && in_array($chosenNumber, $remaining)) {
            $pickedNumber = $chosenNumber;
        } else {
            // 잘못된 번호면 랜덤 (안전장치)
            $pickedNumber = $remaining[array_rand($remaining)];
        }

        // 번호 → 등수 판정
        $prizeResult = $this->determinePrize($pickedNumber, $boardSize, $prizes);

        // 포인트 계산
        $streak = $this->getStreakDays($mb_id);
        $basePoint = $prizeResult['point'];
        $isBonus = ($streak >= ($this->streakBonusDays - 1));
        $finalPoint = $isBonus ? (int)($basePoint * $this->bonusMultiplier) : $basePoint;

        // 진행 상태 업데이트
        $picked[] = $pickedNumber;
        $boardCompleted = (count($picked) >= $boardSize);
        $this->updateUserState($mb_id, (int)$board['glb_id'], $picked, $boardCompleted);

        // 판 완성 보너스
        $boardBonusPoint = 0;
        if ($boardCompleted) {
            $boardBonusPoint = (int)$board['glb_bonus_point'];
            $finalPoint += $boardBonusPoint;
        }

        $gameData = [
            'number'         => $pickedNumber,
            'rank'           => $prizeResult['rank'],
            'rankName'       => $prizeResult['name'],
            'basePoint'      => $basePoint,
            'isBonus'        => $isBonus,
            'streak'         => $streak + 1,
            'boardSize'      => $boardSize,
            'pickedCount'    => count($picked),
            'boardCompleted' => $boardCompleted,
            'boardBonus'     => $boardBonusPoint,
            'picked'         => $picked,
        ];

        $this->saveAttendance($mb_id, $finalPoint, $gameData);

        if ($boardCompleted) {
            $this->resetBoard($mb_id, (int)$board['glb_id']);
        }

        return [
            'success' => true,
            'point'   => $finalPoint,
            'message' => $pickedNumber . '번: ' . $prizeResult['name'] . '! +' . number_format($finalPoint) . 'P',
            'data'    => $gameData,
            'phase'   => 'finalize',
        ];
    }

    // --- Private helpers ---

    private function getActiveBoard(): ?array {
        global $g5;
        $row = sql_fetch("SELECT * FROM {$g5['mg_game_lottery_board_table']} WHERE glb_use = 1 ORDER BY glb_id LIMIT 1");
        return (!empty($row['glb_id'])) ? $row : null;
    }

    private function loadPrizes(): array {
        global $g5;
        $result = sql_query("SELECT * FROM {$g5['mg_game_lottery_prize_table']} WHERE glp_use = 1 ORDER BY glp_rank");
        $list = [];
        while ($row = sql_fetch_array($result)) {
            $list[] = $row;
        }
        return $list;
    }

    private function getUserState(string $mb_id, int $glb_id): array {
        global $g5;
        $mb_esc = sql_real_escape_string($mb_id);
        $row = sql_fetch("SELECT * FROM {$g5['mg_game_lottery_user_table']} WHERE mb_id = '{$mb_esc}' AND glb_id = {$glb_id}");

        if (empty($row['glu_id'])) {
            sql_query("INSERT INTO {$g5['mg_game_lottery_user_table']} (mb_id, glb_id, glu_picked, glu_count, glu_completed_count) VALUES ('{$mb_esc}', {$glb_id}, '[]', 0, 0)");
            $row = sql_fetch("SELECT * FROM {$g5['mg_game_lottery_user_table']} WHERE mb_id = '{$mb_esc}' AND glb_id = {$glb_id}");
        }

        return $row;
    }

    private function updateUserState(string $mb_id, int $glb_id, array $picked, bool $completed): void {
        global $g5;
        $mb_esc = sql_real_escape_string($mb_id);
        $pickedJson = sql_real_escape_string(json_encode($picked));
        $count = count($picked);
        $completedInc = $completed ? ', glu_completed_count = glu_completed_count + 1' : '';

        sql_query("UPDATE {$g5['mg_game_lottery_user_table']}
                   SET glb_id = {$glb_id}, glu_picked = '{$pickedJson}', glu_count = {$count}{$completedInc}
                   WHERE mb_id = '{$mb_esc}'");
    }

    private function resetBoard(string $mb_id, int $glb_id): void {
        global $g5;
        $mb_esc = sql_real_escape_string($mb_id);
        sql_query("UPDATE {$g5['mg_game_lottery_user_table']}
                   SET glu_picked = '[]', glu_count = 0
                   WHERE mb_id = '{$mb_esc}' AND glb_id = {$glb_id}");
    }

    /**
     * 번호 → 등수 판정 (시드 기반 셔플)
     */
    private function determinePrize(int $number, int $boardSize, array $prizes): array {
        $assignments = [];
        $idx = 0;
        foreach ($prizes as $p) {
            $count = min((int)$p['glp_count'], $boardSize - $idx);
            for ($i = 0; $i < $count && $idx < $boardSize; $i++) {
                $assignments[$idx] = $p;
                $idx++;
            }
        }
        $lastPrize = end($prizes);
        while ($idx < $boardSize) {
            $assignments[$idx] = $lastPrize;
            $idx++;
        }

        mt_srand(42);
        $indices = range(0, $boardSize - 1);
        for ($i = $boardSize - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $indices[$i];
            $indices[$i] = $indices[$j];
            $indices[$j] = $tmp;
        }
        mt_srand();

        $assignedIdx = $indices[$number - 1];
        $prize = $assignments[$assignedIdx];

        return [
            'rank'  => (int)$prize['glp_rank'],
            'name'  => $prize['glp_name'],
            'point' => (int)$prize['glp_point'],
        ];
    }

    /**
     * 결과 HTML (출석 완료 후 재방문 시)
     */
    public function renderResult(array $result): string {
        $data = $result['data'] ?? [];
        $number = (int)($data['number'] ?? 0);
        $rank = (int)($data['rank'] ?? 5);
        $rankName = htmlspecialchars($data['rankName'] ?? '');
        $point = (int)($result['point'] ?? 0);
        $streak = (int)($data['streak'] ?? 0);
        $isBonus = !empty($data['isBonus']);
        $basePoint = (int)($data['basePoint'] ?? 0);
        $boardCompleted = !empty($data['boardCompleted']);
        $boardBonus = (int)($data['boardBonus'] ?? 0);
        $pickedCount = (int)($data['pickedCount'] ?? 0);
        $boardSize = (int)($data['boardSize'] ?? 100);
        $color = self::$RANK_COLORS[$rank] ?? '#6b7280';

        $parts = [];
        $parts[] = $rankName . ' ' . number_format($basePoint) . 'P';
        if ($isBonus) {
            $parts[] = '연속출석 x' . $this->bonusMultiplier;
        }
        if ($boardCompleted && $boardBonus > 0) {
            $parts[] = '판 완성 +' . number_format($boardBonus) . 'P';
        }
        $breakdown = implode(' + ', $parts) . ' = ' . number_format($point) . 'P';

        $html = '<div class="mg-game-result">';
        $html .= '<div style="text-align:center; padding:1.5rem 1rem;">';

        $html .= '<div style="display:inline-flex;align-items:center;justify-content:center;width:3.5rem;height:3.5rem;border-radius:0.5rem;background:' . $color . ';color:#fff;font-size:1.5rem;font-weight:700;margin-bottom:0.75rem;">' . $number . '</div>';

        $html .= '<div style="font-size:1.1rem;color:' . $color . ';font-weight:600;margin-bottom:0.5rem;">' . $rankName . ' 당첨!</div>';

        $html .= '<div style="font-size:1.5rem; font-weight:700; color:var(--mg-accent);">+' . number_format($point) . 'P</div>';
        $html .= '<p style="font-size:0.8rem; color:var(--mg-text-muted); margin-top:0.5rem;">' . $breakdown . '</p>';

        $html .= '<div style="margin-top:0.75rem; font-size:0.85rem; color:var(--mg-text-secondary);">';
        if ($boardCompleted) {
            $html .= '판 완성! 새 판이 시작됩니다.';
        } else {
            $html .= $pickedCount . '/' . $boardSize . ' 진행 중';
        }
        $html .= '</div>';

        if ($streak > 0 && $isBonus) {
            $html .= '<div style="margin-top:0.5rem; font-size:0.85rem; color:var(--mg-accent);">' . $streak . '일 연속 출석 보너스!</div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * 게임 UI HTML — 문방구 종이뽑기 스타일
     */
    public function renderUI(): string {
        global $g5, $member;

        $board = $this->getActiveBoard();
        $boardSize = $board ? (int)$board['glb_size'] : 100;
        $bonusPoint = $board ? (int)$board['glb_bonus_point'] : 500;

        // 유저 진행 상태
        $picked = [];
        $pickedCount = 0;
        $completedCount = 0;
        if (!empty($member['mb_id']) && $board) {
            $mb_esc = sql_real_escape_string($member['mb_id']);
            $glb_id = (int)$board['glb_id'];
            $row = sql_fetch("SELECT * FROM {$g5['mg_game_lottery_user_table']} WHERE mb_id = '{$mb_esc}' AND glb_id = {$glb_id}");
            if (!empty($row['glu_id'])) {
                $picked = json_decode($row['glu_picked'] ?: '[]', true) ?: [];
                $pickedCount = count($picked);
                $completedCount = (int)$row['glu_completed_count'];
            }
        }

        $prizes = $this->loadPrizes();

        // 등수별 번호 매핑 (determinePrize와 동일한 시드)
        $assignments = [];
        $idx = 0;
        foreach ($prizes as $p) {
            $count = min((int)$p['glp_count'], $boardSize - $idx);
            for ($i = 0; $i < $count && $idx < $boardSize; $i++) {
                $assignments[$idx] = (int)$p['glp_rank'];
                $idx++;
            }
        }
        while ($idx < $boardSize) {
            $assignments[$idx] = 5;
            $idx++;
        }
        mt_srand(42);
        $indices = range(0, $boardSize - 1);
        for ($i = $boardSize - 1; $i > 0; $i--) {
            $j = mt_rand(0, $i);
            $tmp = $indices[$i];
            $indices[$i] = $indices[$j];
            $indices[$j] = $tmp;
        }
        mt_srand();

        $numberRankMap = [];
        for ($n = 1; $n <= $boardSize; $n++) {
            $numberRankMap[$n] = $assignments[$indices[$n - 1]];
        }

        // 등수별 이름 맵
        $prizeNameMap = [];
        foreach ($prizes as $p) {
            $prizeNameMap[(int)$p['glp_rank']] = $p['glp_name'];
        }

        $pickedJson = json_encode($picked);
        $colorsJson = json_encode(self::$RANK_COLORS);
        $prizeNameMapJson = json_encode($prizeNameMap, JSON_UNESCAPED_UNICODE);

        $cols = 10;
        if ($boardSize <= 25) $cols = 5;

        $progressPct = $boardSize > 0 ? round($pickedCount / $boardSize * 100) : 0;

        $html = '<div id="lottery-game-ui">';

        // 헤더: 진행도 바
        $html .= '<div class="lottery-header">';
        $html .= '<div class="lottery-progress-info">';
        $html .= '<span class="lottery-progress-label">진행 <strong>' . $pickedCount . '</strong><span class="lottery-dim">/' . $boardSize . '</span></span>';
        if ($completedCount > 0) {
            $html .= '<span class="lottery-dim">' . $completedCount . '판 완성</span>';
        }
        $html .= '</div>';
        $html .= '<div class="lottery-progress-bar"><div class="lottery-progress-fill" style="width:' . $progressPct . '%;"></div></div>';
        $html .= '<div class="lottery-bonus-info">판 완성 보너스 +' . number_format($bonusPoint) . 'P</div>';
        $html .= '</div>';

        // 등수 범례
        $html .= '<div class="lottery-legend">';
        foreach ($prizes as $p) {
            $rc = self::$RANK_COLORS[(int)$p['glp_rank']] ?? '#6b7280';
            $html .= '<span class="lottery-legend-item">';
            $html .= '<span class="lottery-legend-dot" style="background:' . $rc . ';"></span>';
            $html .= htmlspecialchars($p['glp_name']) . ' <span class="lottery-dim">' . number_format($p['glp_point']) . 'P</span>';
            $html .= '</span>';
        }
        $html .= '</div>';

        // 안내 문구
        $html .= '<p class="lottery-hint">뽑고 싶은 종이를 선택하세요</p>';

        // 그리드 보드
        $html .= '<div id="lottery-board" class="lottery-board" style="grid-template-columns:repeat(' . $cols . ', 1fr);">';
        for ($n = 1; $n <= $boardSize; $n++) {
            $isPicked = in_array($n, $picked);
            $rank = $numberRankMap[$n];
            $color = self::$RANK_COLORS[$rank] ?? '#6b7280';

            if ($isPicked) {
                $html .= '<div class="lottery-cell picked" data-number="' . $n . '" data-rank="' . $rank . '">';
                $html .= '<span class="lottery-cell-number" style="color:' . $color . ';">' . $n . '</span>';
                $html .= '</div>';
            } else {
                $html .= '<div class="lottery-cell" data-number="' . $n . '">';
                $html .= '<span class="lottery-cell-number">' . $n . '</span>';
                $html .= '<span class="lottery-cell-fold"></span>';
                $html .= '</div>';
            }
        }
        $html .= '</div>';

        // 결과 영역
        $html .= '<div id="game-result" style="margin-top:1rem;"></div>';

        $html .= '</div>';

        // JS 데이터 주입
        $html .= '<script>';
        $html .= 'var LOTTERY_PICKED = ' . $pickedJson . ';';
        $html .= 'var LOTTERY_RANK_COLORS = ' . $colorsJson . ';';
        $html .= 'var LOTTERY_PRIZE_NAMES = ' . $prizeNameMapJson . ';';
        $html .= 'var LOTTERY_BOARD_SIZE = ' . $boardSize . ';';
        $html .= '</script>';

        return $html;
    }

    /**
     * CSS — 문방구 종이뽑기 디자인
     */
    public function getCSS(): string {
        return '
/* 종이뽑기 레이아웃 */
#lottery-game-ui {
    padding: 0.5rem 0;
}

.lottery-header {
    margin-bottom: 1.25rem;
}

.lottery-progress-info {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
    color: var(--mg-text-secondary);
}

.lottery-progress-info strong {
    color: var(--mg-accent);
    font-size: 1.1rem;
}

.lottery-dim {
    color: var(--mg-text-muted);
    font-size: 0.8rem;
}

.lottery-progress-bar {
    height: 6px;
    background: var(--mg-bg-tertiary);
    border-radius: 3px;
    overflow: hidden;
}

.lottery-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--mg-accent), #fbbf24);
    border-radius: 3px;
    transition: width 0.5s ease;
}

.lottery-bonus-info {
    text-align: right;
    font-size: 0.75rem;
    color: var(--mg-text-muted);
    margin-top: 0.35rem;
}

.lottery-legend {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 1rem;
    font-size: 0.8rem;
    color: var(--mg-text-secondary);
}

.lottery-legend-item {
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

.lottery-legend-dot {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    display: inline-block;
}

.lottery-hint {
    text-align: center;
    font-size: 0.85rem;
    color: var(--mg-text-muted);
    margin-bottom: 1rem;
}

/* 보드 그리드 */
.lottery-board {
    display: grid;
    gap: 4px;
    max-width: 520px;
    margin: 0 auto;
    padding: 12px;
    background: var(--mg-bg-primary);
    border-radius: 0.75rem;
    border: 1px solid var(--mg-bg-tertiary);
}

/* 개별 칸 — 접힌 종이 */
.lottery-cell {
    position: relative;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 3px;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    user-select: none;

    /* 접힌 종이 느낌 */
    background: linear-gradient(145deg, #3a3c42 0%, #2f3136 50%, #292b2f 100%);
    border: 1px solid #404249;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2), inset 0 1px 0 rgba(255,255,255,0.04);
}

.lottery-cell-number {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--mg-text-muted);
    position: relative;
    z-index: 1;
}

/* 접힌 꼬리 (삼각형) */
.lottery-cell-fold {
    position: absolute;
    top: 0;
    right: 0;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 0 8px 8px 0;
    border-color: transparent var(--mg-bg-primary) transparent transparent;
    z-index: 2;
}

/* 호버 — 살짝 들어올림 */
.lottery-cell:not(.picked):hover {
    transform: translateY(-3px) scale(1.05);
    box-shadow: 0 4px 12px rgba(245, 159, 10, 0.15), 0 2px 4px rgba(0,0,0,0.3);
    border-color: var(--mg-accent);
    z-index: 5;
}

.lottery-cell:not(.picked):hover .lottery-cell-number {
    color: var(--mg-accent);
}

.lottery-cell:not(.picked):active {
    transform: translateY(-1px) scale(1.02);
}

/* 뽑힌 칸 — 펼쳐진 종이 */
.lottery-cell.picked {
    cursor: default;
    background: var(--mg-bg-secondary);
    border: 1px solid transparent;
    box-shadow: none;
}

.lottery-cell.picked .lottery-cell-number {
    font-weight: 700;
    font-size: 0.7rem;
}

.lottery-cell.picked .lottery-cell-fold {
    display: none;
}

/* 등수별 뽑힌 칸 색상 */
.lottery-cell.picked[data-rank="1"] { background: rgba(245,159,10,0.15); border-color: rgba(245,159,10,0.3); }
.lottery-cell.picked[data-rank="2"] { background: rgba(168,85,247,0.12); border-color: rgba(168,85,247,0.25); }
.lottery-cell.picked[data-rank="3"] { background: rgba(59,130,246,0.12); border-color: rgba(59,130,246,0.25); }
.lottery-cell.picked[data-rank="4"] { background: rgba(34,197,94,0.10); border-color: rgba(34,197,94,0.2); }
.lottery-cell.picked[data-rank="5"] { background: rgba(107,114,128,0.08); border-color: rgba(107,114,128,0.15); }

/* 뽑기 애니메이션 */
.lottery-cell.revealing {
    animation: lotteryPull 0.5s ease;
    z-index: 10;
}

@keyframes lotteryPull {
    0%   { transform: scale(1); }
    25%  { transform: scale(1.2) translateY(-8px); }
    50%  { transform: scale(1.3) translateY(-12px); opacity: 0.8; }
    75%  { transform: scale(1.1) translateY(-4px); opacity: 1; }
    100% { transform: scale(1); }
}

/* 처리 중 비활성 */
.lottery-board.busy .lottery-cell:not(.picked) {
    pointer-events: none;
    opacity: 0.6;
}

/* 반응형 */
@media (max-width: 640px) {
    .lottery-board {
        max-width: 100% !important;
        gap: 3px !important;
        padding: 8px;
    }
    .lottery-cell-number {
        font-size: 0.6rem !important;
    }
    .lottery-cell-fold {
        border-width: 0 6px 6px 0;
    }
}
';
    }

    /**
     * JavaScript — 칸 클릭으로 뽑기
     */
    public function getJavaScript(): string {
        $g5_bbs_url = G5_BBS_URL;

        return <<<JS
(function() {
    var board = document.getElementById('lottery-board');
    var hint = document.querySelector('.lottery-hint');
    if (!board) return;

    var rankColors = LOTTERY_RANK_COLORS || {};
    var prizeNames = LOTTERY_PRIZE_NAMES || {};
    var busy = false;

    board.addEventListener('click', function(e) {
        if (busy) return;

        // 클릭된 셀 찾기
        var cell = e.target.closest('.lottery-cell:not(.picked)');
        if (!cell) return;

        var number = parseInt(cell.getAttribute('data-number'));
        if (!number || number < 1) return;

        busy = true;
        board.classList.add('busy');
        if (hint) hint.textContent = '뽑는 중...';

        // 뽑기 애니메이션 시작
        cell.classList.add('revealing');

        fetch('{$g5_bbs_url}/attendance_play.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'play', game: 'lottery', number: number })
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (!result.success) {
                cell.classList.remove('revealing');
                board.classList.remove('busy');
                busy = false;
                if (hint) hint.textContent = '뽑고 싶은 종이를 선택하세요';
                alert(result.message);
                return;
            }

            var data = result.data || {};
            var rank = data.rank || 5;
            var rankName = data.rankName || (rank + '등');
            var color = rankColors[rank] || '#6b7280';
            var point = result.point || 0;

            // 결과 반영: 칸을 뒤집어서 결과 표시
            setTimeout(function() {
                cell.classList.remove('revealing');
                cell.classList.add('picked');
                cell.setAttribute('data-rank', rank);

                // 번호 색상 변경
                var numEl = cell.querySelector('.lottery-cell-number');
                if (numEl) numEl.style.color = color;

                // 접힌 꼬리 제거
                var fold = cell.querySelector('.lottery-cell-fold');
                if (fold) fold.style.display = 'none';

                if (hint) hint.style.display = 'none';
            }, 500);

            // 결과 HTML (아래에 표시)
            if (result.html) {
                setTimeout(function() {
                    var resultDiv = document.getElementById('game-result');
                    if (resultDiv) resultDiv.innerHTML = result.html;
                }, 800);
            }

            // 프로그레스 바 업데이트
            setTimeout(function() {
                var fill = document.querySelector('.lottery-progress-fill');
                var info = document.querySelector('.lottery-progress-info');
                if (fill && data.boardSize) {
                    var pct = Math.round(data.pickedCount / data.boardSize * 100);
                    fill.style.width = pct + '%';
                }
                if (info) {
                    var strong = info.querySelector('strong');
                    if (strong) strong.textContent = data.pickedCount;
                }
            }, 600);
        })
        .catch(function(err) {
            console.error('[Lottery] Error:', err);
            cell.classList.remove('revealing');
            board.classList.remove('busy');
            busy = false;
            if (hint) hint.textContent = '뽑고 싶은 종이를 선택하세요';
        });
    });
})();
JS;
    }
}
