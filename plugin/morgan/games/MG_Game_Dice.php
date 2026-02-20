<?php
/**
 * Morgan Edition - 주사위 게임 (5d6 족보 + Dice-Box 3D)
 *
 * 출석체크 시 5d6 주사위를 굴려 족보 기반 포인트 획득
 * - 족보: 퍼펙트(1000P) ~ 트리플(100P), 꽝=주사위 합
 * - 족보별 포인트는 관리자가 설정 가능
 * - 리롤: 킵할 주사위 선택 후 나머지 다시 굴리기 (기본 2회)
 * - 연속 출석: N일 연속 시 배율 적용
 * - Dice-Box 3D 물리 애니메이션 연동
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Game_Base.php';

class MG_Game_Dice extends MG_Game_Base {
    private $bonusMultiplier = 2;
    private $streakBonusDays = 7;
    private $diceCount = 5;
    private $diceSides = 6;
    private $rerollCount = 2;

    // 족보 정의 (검사 순서: 높은 것부터)
    // bonus 값은 관리자 설정으로 덮어씀
    private static $COMBOS = [
        'yahtzee'        => ['name' => '퍼펙트',         'bonus' => 1000, 'desc' => '5개 동일',       'config_key' => 'dice_combo_yahtzee'],
        'four_kind'      => ['name' => '포카인드',       'bonus' => 500,  'desc' => '4개 동일',       'config_key' => 'dice_combo_four_kind'],
        'large_straight' => ['name' => '라지 스트레이트', 'bonus' => 400,  'desc' => '5개 연속',       'config_key' => 'dice_combo_large_straight'],
        'full_house'     => ['name' => '풀하우스',       'bonus' => 300,  'desc' => '3개+2개 동일',   'config_key' => 'dice_combo_full_house'],
        'small_straight' => ['name' => '스몰 스트레이트', 'bonus' => 200,  'desc' => '4개 연속',       'config_key' => 'dice_combo_small_straight'],
        'triple'         => ['name' => '트리플',         'bonus' => 100,  'desc' => '3개 동일',       'config_key' => 'dice_combo_triple'],
    ];

    // 관리자 설정 반영된 콤보 포인트
    private $comboPoints = [];

    public function __construct() {
        $this->bonusMultiplier = (float)mg_get_config('dice_bonus_multiplier', 2);
        $this->streakBonusDays = (int)mg_get_config('attendance_streak_bonus_days', 7);
        $this->diceCount = max(1, min(5, (int)mg_get_config('dice_count', 5)));
        $this->diceSides = max(6, (int)mg_get_config('dice_sides', 6));
        $this->rerollCount = max(0, min(5, (int)mg_get_config('dice_reroll_count', 2)));

        // 관리자 설정 포인트 로드
        foreach (self::$COMBOS as $key => $combo) {
            $configVal = mg_get_config($combo['config_key'], '');
            $this->comboPoints[$key] = ($configVal !== '') ? (int)$configVal : $combo['bonus'];
        }
    }

    public function getCode(): string {
        return 'dice';
    }

    public function getName(): string {
        return '주사위';
    }

    public function getDescription(): string {
        $maxCombo = $this->comboPoints['yahtzee'] ?? 1000;
        $desc = "{$this->diceCount}d{$this->diceSides} 주사위를 굴려 포인트를 획득합니다. 족보 적중 시 최대 {$maxCombo}P!";
        if ($this->rerollCount > 0) {
            $desc .= " (리롤 {$this->rerollCount}회)";
        }
        return $desc;
    }

    protected function getStreakDays(string $mb_id): int {
        global $g5;
        $mb_id = sql_real_escape_string($mb_id);
        $today = date('Y-m-d');
        $checkDays = max($this->streakBonusDays, 7);

        $sql = "SELECT at_date FROM {$g5['mg_attendance_table']}
                WHERE mb_id = '{$mb_id}'
                AND at_date >= DATE_SUB('{$today}', INTERVAL {$checkDays} DAY)
                AND at_date < '{$today}'
                ORDER BY at_date DESC";
        $result = sql_query($sql);

        $streak = 0;
        $checkDate = date('Y-m-d', strtotime('-1 day'));
        while ($row = sql_fetch_array($result)) {
            if ($row['at_date'] == $checkDate) {
                $streak++;
                $checkDate = date('Y-m-d', strtotime($checkDate . ' -1 day'));
            } else {
                break;
            }
        }
        return $streak;
    }

    // ========================================
    // 족보 검사 (야찌 룰)
    // ========================================

    /**
     * 주사위 값으로 족보 체크
     * @param array $dice  주사위 값 배열 [3,5,3,2,5]
     * @return array ['key'=>'full_house', 'name'=>'풀하우스', 'bonus'=>300] 또는 빈 배열
     */
    public function checkCombo(array $dice): array {
        if (count($dice) < 2) return [];

        $counts = array_count_values($dice);
        $freqs = array_values($counts);
        rsort($freqs);

        $sorted = $dice;
        sort($sorted);

        // 퍼펙트: 5개 동일
        if ($freqs[0] >= 5 && $this->comboPoints['yahtzee'] > 0) {
            return ['key' => 'yahtzee', 'name' => self::$COMBOS['yahtzee']['name'], 'bonus' => $this->comboPoints['yahtzee']];
        }

        // 포카인드: 4개 동일
        if ($freqs[0] >= 4 && $this->comboPoints['four_kind'] > 0) {
            return ['key' => 'four_kind', 'name' => self::$COMBOS['four_kind']['name'], 'bonus' => $this->comboPoints['four_kind']];
        }

        // 라지 스트레이트: 5개 연속
        if (self::hasConsecutive($sorted, 5) && $this->comboPoints['large_straight'] > 0) {
            return ['key' => 'large_straight', 'name' => self::$COMBOS['large_straight']['name'], 'bonus' => $this->comboPoints['large_straight']];
        }

        // 풀하우스: 3+2
        if ($freqs[0] == 3 && isset($freqs[1]) && $freqs[1] >= 2 && $this->comboPoints['full_house'] > 0) {
            return ['key' => 'full_house', 'name' => self::$COMBOS['full_house']['name'], 'bonus' => $this->comboPoints['full_house']];
        }

        // 스몰 스트레이트: 4개 연속
        if (self::hasConsecutive($sorted, 4) && $this->comboPoints['small_straight'] > 0) {
            return ['key' => 'small_straight', 'name' => self::$COMBOS['small_straight']['name'], 'bonus' => $this->comboPoints['small_straight']];
        }

        // 트리플: 3개 동일
        if ($freqs[0] >= 3 && $this->comboPoints['triple'] > 0) {
            return ['key' => 'triple', 'name' => self::$COMBOS['triple']['name'], 'bonus' => $this->comboPoints['triple']];
        }

        return [];
    }

    /**
     * 정렬된 배열에서 N개 연속 존재 여부
     */
    private static function hasConsecutive(array $sorted, int $n): bool {
        $unique = array_values(array_unique($sorted));
        $cnt = count($unique);
        if ($cnt < $n) return false;

        $consecutive = 1;
        for ($i = 1; $i < $cnt; $i++) {
            if ($unique[$i] == $unique[$i-1] + 1) {
                $consecutive++;
                if ($consecutive >= $n) return true;
            } else {
                $consecutive = 1;
            }
        }
        return false;
    }

    /**
     * 족보 목록 반환 (관리자 표시용, 설정 포인트 반영)
     */
    public function getComboList(): array {
        $list = [];
        foreach (self::$COMBOS as $key => $combo) {
            $list[$key] = $combo;
            $list[$key]['bonus'] = $this->comboPoints[$key] ?? $combo['bonus'];
        }
        return $list;
    }

    /**
     * 족보 기본 정의 반환 (static, 설정 키 포함)
     */
    public static function getComboDefinitions(): array {
        return self::$COMBOS;
    }

    /** 주사위 면 표시용 (숫자) */
    private static function dieFaceHtml(int $value): string {
        return (string)$value;
    }

    // ========================================
    // 포인트 계산
    // ========================================

    /**
     * 최종 포인트 계산
     * 족보 히트 = 족보 고정 포인트, 꽝 = 주사위 합
     * + 연속 출석 배율
     */
    public function calculatePoint(array $dice, int $streak): array {
        $total = array_sum($dice);

        // 족보 체크
        $comboBonus = 0;
        $comboName = '';
        $comboKey = '';
        $combo = $this->checkCombo($dice);
        if (!empty($combo)) {
            $comboBonus = $combo['bonus'];
            $comboName = $combo['name'];
            $comboKey = $combo['key'];
        }

        // 기본 포인트: 족보 히트 → 족보 포인트, 꽝 → 주사위 합
        $basePoint = ($comboBonus > 0) ? $comboBonus : $total;

        // 연속 출석 보너스
        $isBonus = ($streak >= ($this->streakBonusDays - 1));
        $finalPoint = $isBonus ? (int)($basePoint * $this->bonusMultiplier) : $basePoint;

        return [
            'basePoint' => $basePoint,
            'comboBonus' => $comboBonus,
            'comboName' => $comboName,
            'comboKey' => $comboKey,
            'isBonus' => $isBonus,
            'finalPoint' => $finalPoint,
            'diceTotal' => $total,
        ];
    }

    // ========================================
    // 멀티스텝 게임 (roll → reroll → finalize)
    // ========================================

    private function getSessionState(): ?array {
        if (isset($_SESSION['mg_dice_game'])) {
            $state = $_SESSION['mg_dice_game'];
            if (time() - $state['started'] > 300) {
                unset($_SESSION['mg_dice_game']);
                return null;
            }
            return $state;
        }
        return null;
    }

    private function setSessionState(array $state): void {
        $_SESSION['mg_dice_game'] = $state;
    }

    private function clearSessionState(): void {
        unset($_SESSION['mg_dice_game']);
    }

    public function startRoll(string $mb_id): array {
        if ($this->hasPlayedToday($mb_id)) {
            return ['success' => false, 'message' => '오늘은 이미 출석하셨습니다.'];
        }

        // 이미 굴린 세션이 있으면 기존 결과 반환 (무한 리롤 방지)
        $existing = $this->getSessionState();
        if ($existing && $existing['mb_id'] === $mb_id) {
            $combo = $this->checkCombo($existing['dice']);
            return [
                'success' => true,
                'phase' => 'roll',
                'dice' => $existing['dice'],
                'rerolls_left' => $existing['rerolls_left'],
                'combo' => $combo,
                'dice_sides' => $this->diceSides,
                'message' => '이미 주사위가 굴려져 있습니다.',
            ];
        }

        $dice = [];
        for ($i = 0; $i < $this->diceCount; $i++) {
            $dice[] = rand(1, $this->diceSides);
        }

        if ($this->rerollCount <= 0) {
            return $this->finalizeDirectly($mb_id, $dice);
        }

        $this->setSessionState([
            'dice' => $dice,
            'rerolls_left' => $this->rerollCount,
            'started' => time(),
            'mb_id' => $mb_id,
        ]);

        $combo = $this->checkCombo($dice);

        return [
            'success' => true,
            'phase' => 'roll',
            'dice' => $dice,
            'rerolls_left' => $this->rerollCount,
            'combo' => $combo,
            'dice_sides' => $this->diceSides,
            'message' => '다시 굴릴 주사위를 선택하세요.',
        ];
    }

    public function doReroll(string $mb_id, array $held): array {
        $state = $this->getSessionState();
        if (!$state) {
            return ['success' => false, 'message' => '게임 세션이 만료되었습니다. 다시 시작해주세요.'];
        }
        if ($state['mb_id'] !== $mb_id) {
            return ['success' => false, 'message' => '잘못된 요청입니다.'];
        }
        if ($state['rerolls_left'] <= 0) {
            return ['success' => false, 'message' => '리롤 기회가 없습니다.'];
        }
        if (count($held) !== count($state['dice'])) {
            return ['success' => false, 'message' => '잘못된 요청입니다.'];
        }

        $dice = $state['dice'];
        $rerolledIndices = [];
        for ($i = 0; $i < count($dice); $i++) {
            if (empty($held[$i])) {
                $dice[$i] = rand(1, $this->diceSides);
                $rerolledIndices[] = $i;
            }
        }

        $state['dice'] = $dice;
        $state['rerolls_left']--;
        $this->setSessionState($state);

        $combo = $this->checkCombo($dice);

        return [
            'success' => true,
            'phase' => 'reroll',
            'dice' => $dice,
            'rerolls_left' => $state['rerolls_left'],
            'rerolled_indices' => $rerolledIndices,
            'combo' => $combo,
            'dice_sides' => $this->diceSides,
            'message' => $state['rerolls_left'] > 0
                ? '리롤 완료! 다시 선택하거나 결과를 확정하세요.'
                : '마지막 리롤 완료! 결과를 확정하세요.',
        ];
    }

    public function finalize(string $mb_id): array {
        $state = $this->getSessionState();
        if (!$state) {
            return ['success' => false, 'message' => '게임 세션이 만료되었습니다. 다시 시작해주세요.'];
        }
        if ($state['mb_id'] !== $mb_id) {
            return ['success' => false, 'message' => '잘못된 요청입니다.'];
        }
        if ($this->hasPlayedToday($mb_id)) {
            $this->clearSessionState();
            return ['success' => false, 'message' => '오늘은 이미 출석하셨습니다.'];
        }

        $dice = $state['dice'];
        $this->clearSessionState();
        return $this->finalizeDirectly($mb_id, $dice);
    }

    private function finalizeDirectly(string $mb_id, array $dice): array {
        $streak = $this->getStreakDays($mb_id);
        $calc = $this->calculatePoint($dice, $streak);

        $gameData = [
            'dice' => $dice,
            'diceTotal' => $calc['diceTotal'],
            'diceCount' => $this->diceCount,
            'diceSides' => $this->diceSides,
            'basePoint' => $calc['basePoint'],
            'comboBonus' => $calc['comboBonus'],
            'comboName' => $calc['comboName'],
            'comboKey' => $calc['comboKey'],
            'streak' => $streak + 1,
            'isBonus' => $calc['isBonus'],
        ];

        $this->saveAttendance($mb_id, $calc['finalPoint'], $gameData);

        $message = "{$calc['finalPoint']}P를 획득했습니다!";
        if (!empty($calc['comboName'])) {
            $message = $calc['comboName'] . '! ' . $message;
        }
        if ($calc['isBonus']) {
            $message .= " ({$this->streakBonusDays}일 연속 보너스!)";
        }

        return [
            'success' => true,
            'phase' => 'finalize',
            'point' => $calc['finalPoint'],
            'message' => $message,
            'data' => $gameData,
        ];
    }

    // ========================================
    // 기존 play() 호환
    // ========================================

    public function play(string $mb_id): array {
        if ($this->hasPlayedToday($mb_id)) {
            return ['success' => false, 'point' => 0, 'message' => '오늘은 이미 출석하셨습니다.', 'data' => []];
        }

        $dice = [];
        for ($i = 0; $i < $this->diceCount; $i++) {
            $dice[] = rand(1, $this->diceSides);
        }

        return $this->finalizeDirectly($mb_id, $dice);
    }

    // ========================================
    // UI 렌더링
    // ========================================

    public function renderResult(array $result): string {
        if (!$result['success']) {
            return '<div class="mg-game-result mg-game-result--fail">
                <p class="text-mg-text-muted">' . htmlspecialchars($result['message']) . '</p>
            </div>';
        }

        $data = $result['data'];
        $dice = isset($data['dice']) ? $data['dice'] : [];
        $point = $result['point'];
        $sides = isset($data['diceSides']) ? $data['diceSides'] : 6;

        $diceHtml = '';
        foreach ($dice as $val) {
            $diceHtml .= '<span class="mg-die mg-die--settled" data-value="' . $val . '">' . self::dieFaceHtml((int)$val) . '</span>';
        }

        $bonusHtml = '';
        if (!empty($data['comboName'])) {
            $bonusHtml .= '<span class="mg-badge mg-badge--combo">' . htmlspecialchars($data['comboName']) . '</span> ';
        } else {
            $bonusHtml .= '<span class="mg-badge mg-badge--miss">꽝</span> ';
        }
        if (!empty($data['isBonus'])) {
            $bonusHtml .= '<span class="mg-badge mg-badge--warning">' . $this->streakBonusDays . '일 연속!</span>';
        }

        $comboInfo = '';
        if (!empty($data['comboName'])) {
            $comboInfo = '<p class="mg-game-combo-info">' . htmlspecialchars($data['comboName']) . ' +' . number_format($data['comboBonus']) . 'P</p>';
        } else {
            $comboInfo = '<p class="mg-game-combo-info mg-game-combo-miss">합산 ' . $data['diceTotal'] . '</p>';
        }

        // 포인트 계산식 — 항상 표시
        $base = $data['basePoint'];
        $parts = [];
        if (!empty($data['comboName'])) {
            $parts[] = $data['comboName'] . ' ' . number_format($base) . 'P';
        } else {
            $parts[] = '합산 ' . number_format($base) . 'P';
        }
        if (!empty($data['isBonus'])) {
            $parts[] = '연속출석 ×' . $this->bonusMultiplier;
        }
        $pointBreakdown = '<p class="mg-game-breakdown">' . implode(' → ', $parts) . ' = ' . number_format($point) . 'P</p>';

        return '<div class="mg-game-result mg-game-result--success">
            <div class="mg-dice-result">' . $diceHtml . '</div>
            ' . $comboInfo . '
            ' . ($bonusHtml ? '<p class="mg-game-bonus">' . $bonusHtml . '</p>' : '') . '
            <p class="mg-game-point">+' . number_format($point) . 'P</p>
            ' . $pointBreakdown . '
            <p class="mg-game-streak">연속 출석 ' . $data['streak'] . '일째</p>
        </div>';
    }

    public function renderUI(): string {
        global $member;
        $mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';

        // 기존 세션이 있으면 복원 데이터 준비
        $session = $this->getSessionState();
        $hasSession = ($session && isset($session['mb_id']) && $session['mb_id'] === $mb_id && $mb_id);
        $sessionJson = '';
        if ($hasSession) {
            $combo = $this->checkCombo($session['dice']);
            $sessionJson = htmlspecialchars(json_encode([
                'dice' => $session['dice'],
                'rerolls_left' => $session['rerolls_left'],
                'combo' => $combo,
            ], JSON_UNESCAPED_UNICODE), ENT_QUOTES);
        }

        // 주사위 HTML
        $diceHtml = '';
        for ($i = 0; $i < $this->diceCount; $i++) {
            if ($hasSession && isset($session['dice'][$i])) {
                $val = (int)$session['dice'][$i];
                $diceHtml .= '<div class="mg-die mg-die--settled" id="die' . $i . '" data-index="' . $i . '" data-value="' . $val . '">' . self::dieFaceHtml($val) . '</div>';
            } else {
                $diceHtml .= '<div class="mg-die mg-die--idle" id="die' . $i . '" data-index="' . $i . '">?</div>';
            }
        }

        $rerollInfo = '';
        if ($this->rerollCount > 0) {
            $rerollInfo = '<div class="mg-reroll-area" id="reroll-area" style="display:none;">
                <div class="mg-combo-display" id="combo-display"></div>
                <div class="mg-reroll-status" id="reroll-status"></div>
                <div class="mg-reroll-buttons">
                    <button type="button" class="mg-btn mg-btn-secondary" id="btn-reroll" style="display:none;">다시 굴리기</button>
                    <button type="button" class="mg-btn mg-btn-primary" id="btn-finalize" style="display:none;">결과 확정</button>
                </div>
            </div>';
        }

        // 족보 모달
        $comboRows = '';
        foreach ($this->getComboList() as $key => $combo) {
            $comboRows .= '<tr><td style="font-weight:600;color:var(--mg-accent);">' . htmlspecialchars($combo['name']) . '</td>'
                . '<td style="color:var(--mg-text-secondary);">' . htmlspecialchars($combo['desc']) . '</td>'
                . '<td style="text-align:right;font-weight:600;">' . number_format($combo['bonus']) . 'P</td></tr>';
        }
        $comboModal = '<div id="combo-modal" class="mg-modal" style="display:none;">
            <div class="mg-modal-backdrop" id="combo-modal-close"></div>
            <div class="mg-modal-content">
                <div class="mg-modal-header">
                    <span>족보</span>
                    <button type="button" class="mg-modal-close-btn" id="combo-modal-close-btn">&times;</button>
                </div>
                <div class="mg-modal-body">
                    <table class="mg-combo-table">
                        <thead><tr><th>족보</th><th>조건</th><th style="text-align:right;">포인트</th></tr></thead>
                        <tbody>' . $comboRows . '
                            <tr><td style="color:var(--mg-text-muted);">꽝</td><td style="color:var(--mg-text-muted);">미적중</td><td style="text-align:right;color:var(--mg-text-muted);">주사위 합산</td></tr>
                        </tbody>
                    </table>
                    <div class="mg-combo-rules">
                        <p class="mg-combo-rules-title">보너스 규칙</p>
                        <ul>
                            <li><span style="color:var(--mg-accent);font-weight:600;">연속출석 ×' . $this->bonusMultiplier . '</span> — ' . $this->streakBonusDays . '일 연속 출석 시 포인트 ×' . $this->bonusMultiplier . '</li>
                        </ul>
                        <p class="mg-combo-rules-formula">계산: 기본P → 연속출석 = 최종P</p>
                    </div>
                </div>
            </div>
        </div>';

        $btnStyle = $hasSession ? ' style="display:none;"' : '';

        return '<div class="mg-game-ui mg-game-dice" data-count="' . $this->diceCount . '" data-sides="' . $this->diceSides . '" data-rerolls="' . $this->rerollCount . '"' . ($sessionJson ? ' data-session="' . $sessionJson . '"' : '') . '>
            <div id="dice-box-container" class="dice-box-overlay"></div>
            <div class="mg-dice-tray" id="dice-tray">
                <div class="mg-dice-container" id="dice-container">' . $diceHtml . '</div>
            </div>
            ' . $rerollInfo . '
            <div class="mg-game-bottom">
                <button type="button" class="mg-btn mg-btn-primary mg-btn-lg" id="btn-play-game"' . $btnStyle . '>주사위 굴리기</button>
                <button type="button" class="mg-btn-text" id="btn-combo-info">족보 보기</button>
            </div>
            ' . $comboModal . '
        </div>
        <div id="game-result" style="margin-top:1rem;"></div>';
    }

    public function getJavaScript(): string {
        // 관리자 설정 포인트 반영된 콤보 목록
        $comboList = $this->getComboList();
        $comboJson = json_encode($comboList, JSON_UNESCAPED_UNICODE);
        return <<<JS
(function() {
    var btnPlay = document.getElementById('btn-play-game');
    if (!btnPlay) return;

    var gameUI = document.querySelector('.mg-game-dice');
    var diceCount = parseInt(gameUI.dataset.count) || 5;
    var diceSides = parseInt(gameUI.dataset.sides) || 6;
    var maxRerolls = parseInt(gameUI.dataset.rerolls) || 0;

    var dies = [];
    for (var i = 0; i < diceCount; i++) {
        dies.push(document.getElementById('die' + i));
    }

    var held = new Array(diceCount).fill(0);
    var currentDice = [];
    var rerollsLeft = maxRerolls;
    var phase = 'idle';

    var btnReroll = document.getElementById('btn-reroll');
    var btnFinalize = document.getElementById('btn-finalize');
    var rerollArea = document.getElementById('reroll-area');
    var comboDisplay = document.getElementById('combo-display');
    var rerollStatus = document.getElementById('reroll-status');

    // 족보 모달
    var comboInfoBtn = document.getElementById('btn-combo-info');
    var comboModalEl = document.getElementById('combo-modal');
    var comboModalCloseEl = document.getElementById('combo-modal-close');
    var comboModalCloseBtn = document.getElementById('combo-modal-close-btn');
    if (comboInfoBtn && comboModalEl) {
        comboInfoBtn.addEventListener('click', function() { comboModalEl.style.display = 'flex'; });
    }
    if (comboModalCloseEl) {
        comboModalCloseEl.addEventListener('click', function() { comboModalEl.style.display = 'none'; });
    }
    if (comboModalCloseBtn) {
        comboModalCloseBtn.addEventListener('click', function() { comboModalEl.style.display = 'none'; });
    }

    var COMBOS = {$comboJson};

    // Dice-Box 3D
    var diceBoxReady = false;
    function ensureDiceBox() {
        if (diceBoxReady) return Promise.resolve(true);
        if (typeof MorganDice === 'undefined') return Promise.resolve(false);
        return MorganDice.init('#dice-box-container').then(function() {
            diceBoxReady = true;
            return true;
        }).catch(function(e) {
            console.warn('[DiceGame] DiceBox init failed:', e);
            return false;
        });
    }
    // 모듈 로드 시 즉시 초기화 시도
    ensureDiceBox();
    window.addEventListener('MorganDiceLoaded', function() { ensureDiceBox(); });

    // 리롤 마스크: 1=다시 굴림, 0=유지
    var rerollMask = new Array(diceCount).fill(0);

    // 주사위 면 표시 (숫자)

    // 세션 복원: 새로고침 시 이미 굴린 결과가 있으면 hold 상태로
    var savedSession = gameUI.dataset.session ? JSON.parse(gameUI.dataset.session) : null;
    if (savedSession) {
        currentDice = savedSession.dice;
        rerollsLeft = savedSession.rerolls_left;
        // 3D 주사위도 복원 (세션 값으로 강제 배치)
        ensureDiceBox().then(function(ready) {
            if (ready && currentDice.length > 0) {
                try {
                    MorganDice.clear();
                    MorganDice.roll(diceCount + 'd' + diceSides + '@' + currentDice.join(','));
                } catch(e) { console.warn('[DiceGame] 3D session restore error:', e); }
            }
        });
        if (rerollsLeft <= 0) {
            doFinalize();
        } else {
            enterHoldPhase(savedSession.combo);
        }
    }

    // 리롤 주사위 토글
    dies.forEach(function(die, idx) {
        die.addEventListener('click', function() {
            if (phase !== 'hold') return;
            rerollMask[idx] = rerollMask[idx] ? 0 : 1;
            die.classList.toggle('mg-die--reroll', !!rerollMask[idx]);
        });
    });

    // ===== 주사위 굴리기 =====
    btnPlay.addEventListener('click', function() {
        if (phase === 'rolling' || phase === 'finalizing') return;
        phase = 'rolling';
        btnPlay.disabled = true;
        btnPlay.textContent = '굴리는 중...';
        rerollMask = new Array(diceCount).fill(0);

        dies.forEach(function(die) {
            die.classList.remove('mg-die--idle', 'mg-die--settled', 'mg-die--reroll', 'mg-die--combo');
            die.classList.add('mg-die--rolling');
            die.style.cursor = '';
        });

        var rollIntervals = [];
        dies.forEach(function(die, idx) {
            var interval = setInterval(function() {
                die.textContent = Math.floor(Math.random() * diceSides) + 1;
            }, 60 + idx * 15);
            rollIntervals.push(interval);
        });

        apiCall('roll', {}).then(function(result) {
            if (!result.success) {
                rollIntervals.forEach(function(iv) { clearInterval(iv); });
                resetUI();
                alert(result.message);
                return;
            }

            // Dice-Box 3D — 서버 값으로 강제 표시
            var diceVals = (result.dice || []).join(',');
            ensureDiceBox().then(function(ready) {
                if (ready) {
                    try { MorganDice.clear(); MorganDice.roll(diceCount + 'd' + diceSides + '@' + diceVals); } catch(e) { console.warn('[DiceGame] 3D roll error:', e); }
                }
            });

            currentDice = result.dice || [];
            rerollsLeft = result.rerolls_left || 0;

            settleAllDice(rollIntervals, currentDice, function() {
                if (maxRerolls > 0 && result.phase === 'roll') {
                    enterHoldPhase(result.combo);
                } else {
                    showFinalResult(result);
                }
            });
        }).catch(function(err) {
            rollIntervals.forEach(function(iv) { clearInterval(iv); });
            resetUI();
            alert('오류가 발생했습니다. 다시 시도해주세요.');
        });
    });

    // ===== 리롤 =====
    if (btnReroll) {
        btnReroll.addEventListener('click', function() {
            if (phase !== 'hold' || rerollsLeft <= 0) return;
            var rerollCount = rerollMask.filter(function(r) { return r; }).length;
            if (rerollCount === 0) { alert('다시 굴릴 주사위를 선택하세요.'); return; }
            phase = 'rolling';
            btnReroll.disabled = true;
            btnFinalize.disabled = true;

            // held = 리롤마스크 반전 (서버: held=1은 유지)
            var held = rerollMask.map(function(r) { return r ? 0 : 1; });

            var rollIntervals = [];
            dies.forEach(function(die, idx) {
                if (rerollMask[idx]) {
                    die.classList.remove('mg-die--settled', 'mg-die--reroll', 'mg-die--combo');
                    die.classList.add('mg-die--rolling');
                    var interval = setInterval(function() {
                        die.textContent = Math.floor(Math.random() * diceSides) + 1;
                    }, 60 + idx * 15);
                    rollIntervals.push({ idx: idx, interval: interval });
                }
            });

            apiCall('reroll', { held: held }).then(function(result) {
                if (!result.success) {
                    rollIntervals.forEach(function(ri) { clearInterval(ri.interval); });
                    phase = 'hold';
                    btnReroll.disabled = false;
                    btnFinalize.disabled = false;
                    alert(result.message);
                    return;
                }

                currentDice = result.dice || [];
                rerollsLeft = result.rerolls_left || 0;

                // Dice-Box 3D — 유지 주사위는 그 자리, 리롤만 애니메이션
                var rerolledIndices = [];
                var rerolledVals = [];
                rerollMask.forEach(function(r, idx) {
                    if (r) { rerolledIndices.push(idx); rerolledVals.push(currentDice[idx]); }
                });
                if (diceBoxReady && rerolledIndices.length > 0) {
                    try {
                        // 3D 주사위가 없으면 (새로고침 후 등) 전체 새로 굴리기
                        if (!MorganDice._box || !MorganDice._box.diceList || MorganDice._box.diceList.length === 0) {
                            MorganDice.clear();
                            MorganDice.roll(diceCount + 'd' + diceSides + '@' + currentDice.join(','));
                        } else {
                            MorganDice.rerollForced(rerolledIndices, rerolledVals);
                        }
                    } catch(e) { console.warn('[DiceGame] 3D reroll error:', e); }
                }

                var delay = 0;
                rollIntervals.forEach(function(ri) {
                    (function(riCopy, d) {
                        setTimeout(function() {
                            clearInterval(riCopy.interval);
                            settleOneDie(riCopy.idx, currentDice[riCopy.idx]);
                        }, 450 + d * 250);
                    })(ri, delay);
                    delay++;
                });

                dies.forEach(function(die, idx) {
                    if (!rerollMask[idx]) die.textContent = currentDice[idx];
                });

                var totalDelay = 450 + Math.max(0, delay - 1) * 250 + 400;
                setTimeout(function() {
                    if (rerollsLeft <= 0) {
                        // 리롤 소진 → 자동 확정
                        doFinalize();
                    } else {
                        enterHoldPhase(result.combo);
                    }
                }, totalDelay);
            }).catch(function(err) {
                rollIntervals.forEach(function(ri) { clearInterval(ri.interval); });
                phase = 'hold';
                btnReroll.disabled = false;
                btnFinalize.disabled = false;
                alert('오류가 발생했습니다.');
            });
        });
    }

    // ===== 결과 확정 =====
    if (btnFinalize) {
        btnFinalize.addEventListener('click', function() {
            if (phase !== 'hold') return;
            doFinalize();
        });
    }

    function doFinalize() {
        phase = 'finalizing';
        if (btnReroll) btnReroll.style.display = 'none';
        if (btnFinalize) btnFinalize.style.display = 'none';

        apiCall('finalize', {}).then(function(result) {
            if (!result.success) {
                alert(result.message);
                resetUI();
                return;
            }
            showFinalResult(result);
        }).catch(function(err) {
            alert('오류가 발생했습니다.');
            resetUI();
        });
    }

    function showFinalResult(result) {
        var resultContainer = document.getElementById('game-result');
        if (resultContainer) resultContainer.innerHTML = result.html || '';

        btnPlay.style.display = 'none';
        var tray = document.getElementById('dice-tray');
        if (tray) tray.style.display = 'none';
        if (rerollArea) rerollArea.style.display = 'none';
    }

    // ===== 헬퍼 함수 =====
    function apiCall(action, data) {
        data.game = 'dice';
        data.action = action;
        return fetch(g5_bbs_url + '/attendance_play.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data)
        }).then(function(r) { return r.json(); });
    }

    function settleAllDice(rollIntervals, diceValues, callback) {
        dies.forEach(function(die, idx) {
            setTimeout(function() {
                clearInterval(rollIntervals[idx]);
                settleOneDie(idx, diceValues[idx]);
                if (idx === dies.length - 1) {
                    setTimeout(callback, 250);
                }
            }, 300 + idx * 200);
        });
    }

    function settleOneDie(idx, value) {
        var die = dies[idx];
        var slowCount = 0;
        var slowInterval = setInterval(function() {
            slowCount++;
            die.textContent = Math.floor(Math.random() * diceSides) + 1;
            if (slowCount >= 3) {
                clearInterval(slowInterval);
                die.textContent = value;
                die.classList.remove('mg-die--rolling');
                die.classList.add('mg-die--settled');
                die.dataset.value = value;
                die.classList.add('mg-die--bounce');
                setTimeout(function() { die.classList.remove('mg-die--bounce'); }, 300);
            }
        }, 60);
    }

    function enterHoldPhase(combo) {
        phase = 'hold';
        rerollMask = new Array(diceCount).fill(0);
        dies.forEach(function(die) {
            die.classList.remove('mg-die--reroll');
            die.style.cursor = 'pointer';
        });

        if (comboDisplay && combo && combo.name) {
            comboDisplay.innerHTML = '<span class="mg-combo-badge">' + combo.name + ' ' + combo.bonus + 'P</span>';
            comboDisplay.style.display = '';
            highlightComboDice(currentDice, combo.key);
        } else if (comboDisplay) {
            var sum = currentDice.reduce(function(a,b){return a+b;},0);
            comboDisplay.innerHTML = '<span class="mg-no-combo">합산 ' + sum + 'P</span>';
            comboDisplay.style.display = '';
        }

        if (rerollArea) rerollArea.style.display = '';
        updateRerollStatus();

        if (btnReroll && rerollsLeft > 0) {
            btnReroll.style.display = '';
            btnReroll.disabled = false;
        } else if (btnReroll) {
            btnReroll.style.display = 'none';
        }
        if (btnFinalize) { btnFinalize.style.display = ''; btnFinalize.disabled = false; }
        btnPlay.style.display = 'none';
    }

    function highlightComboDice(dice, comboKey) {
        if (!comboKey) return;
        var counts = {};
        dice.forEach(function(v) { counts[v] = (counts[v] || 0) + 1; });
        var comboIndices = [];
        if (['yahtzee','four_kind','triple','full_house'].indexOf(comboKey) !== -1) {
            var pairs = [];
            Object.keys(counts).forEach(function(v) { if (counts[v] >= 2) pairs.push(parseInt(v)); });
            dice.forEach(function(v, i) { if (pairs.indexOf(v) !== -1) comboIndices.push(i); });
        } else if (['large_straight','small_straight'].indexOf(comboKey) !== -1) {
            comboIndices = dice.map(function(v, i) { return i; });
        }
        comboIndices.forEach(function(i) { if (dies[i]) dies[i].classList.add('mg-die--combo'); });
    }

    function resetUI() {
        if (diceBoxReady) MorganDice.clear();
        phase = 'idle';
        btnPlay.disabled = false;
        btnPlay.textContent = '주사위 굴리기';
        btnPlay.style.display = '';
        dies.forEach(function(die) {
            die.classList.remove('mg-die--rolling', 'mg-die--settled', 'mg-die--reroll', 'mg-die--bounce', 'mg-die--combo');
            die.classList.add('mg-die--idle');
            die.innerHTML = '?';
            die.style.cursor = '';
        });
        rerollMask = new Array(diceCount).fill(0);
        if (rerollArea) rerollArea.style.display = 'none';
        if (comboDisplay) comboDisplay.style.display = 'none';
    }

    function updateRerollStatus() {
        if (!rerollStatus) return;
        if (rerollsLeft > 0) {
            rerollStatus.innerHTML = '<span class="mg-reroll-count">리롤 <strong>' + rerollsLeft + '</strong>회 남음</span> <span class="mg-reroll-guide">· 다시 굴릴 주사위를 탭하세요</span>';
        } else {
            rerollStatus.innerHTML = '<span class="mg-reroll-count mg-reroll-count--zero">리롤 소진</span> <span class="mg-reroll-guide">· 결과를 확정하세요</span>';
        }
    }
})();
JS;
    }

    public function getCSS(): string {
        return <<<'CSS'
.mg-game-dice { text-align: center; padding: 1.5rem; }
.dice-box-overlay { margin-bottom: 1rem; }
.mg-dice-tray {
    background: linear-gradient(145deg, #1a1b1e, #252730);
    border-radius: 16px; padding: 2rem 1.5rem; margin-bottom: 1rem;
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.4), 0 1px 0 rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.06);
}
.mg-dice-container { display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; }
.mg-die {
    width: 68px; height: 68px;
    background: linear-gradient(145deg, #e8e8e8, #d4d4d4);
    border: 2px solid #bbb; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.75rem; font-weight: 800; color: #333;
    position: relative;
    transition: transform 0.3s, box-shadow 0.3s, border-color 0.2s;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.8);
    user-select: none;
}
.mg-die--idle { color: #999; font-size: 1.5rem; }
.mg-die--rolling {
    animation: die-tumble 0.4s ease-in-out infinite;
    color: #666; border-color: var(--mg-accent);
    box-shadow: 0 0 20px rgba(245,159,10,0.3), 0 4px 12px rgba(0,0,0,0.25);
}
@keyframes die-tumble {
    0%   { transform: rotateX(0deg) rotateY(0deg) scale(1); }
    20%  { transform: rotateX(72deg) rotateY(30deg) scale(1.05) translateY(-4px); }
    40%  { transform: rotateX(144deg) rotateY(-20deg) scale(0.98); }
    60%  { transform: rotateX(216deg) rotateY(40deg) scale(1.04) translateY(-3px); }
    80%  { transform: rotateX(288deg) rotateY(-30deg) scale(0.97); }
    100% { transform: rotateX(360deg) rotateY(0deg) scale(1); }
}
.mg-die--settled {
    background: linear-gradient(145deg, #f5f5f5, #e0e0e0);
    border-color: #ccc;
    transform: scale(1); padding: 0;
}
.mg-die--bounce { animation: die-bounce 0.4s ease-out; }
@keyframes die-bounce {
    0% { transform: scale(1.15) translateY(-8px); }
    40% { transform: scale(0.95) translateY(2px); }
    70% { transform: scale(1.03) translateY(-1px); }
    100% { transform: scale(1) translateY(0); }
}
.mg-die--reroll {
    border-color: #ef4444 !important;
    box-shadow: 0 0 16px rgba(239,68,68,0.3), 0 4px 12px rgba(0,0,0,0.3);
    transform: scale(1.05); opacity: 0.8;
}
.mg-die--reroll::after {
    content: '↻'; position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%);
    font-size: 0.85rem; font-weight: 700; color: #ef4444;
}
.mg-die--combo { box-shadow: 0 0 12px rgba(139,92,246,0.4), 0 4px 12px rgba(0,0,0,0.3); border-color: #8b5cf6 !important; }
.mg-reroll-area { margin: 1.5rem 0; }
.mg-reroll-status { color: var(--mg-text-secondary); font-size: 0.85rem; margin-bottom: 1rem; }
.mg-reroll-count { color: var(--mg-accent); font-weight: 600; }
.mg-reroll-count--zero { color: #ef4444; }
.mg-reroll-guide { color: var(--mg-text-muted); }
.mg-reroll-buttons { display: flex; justify-content: center; gap: 0.75rem; flex-wrap: wrap; }
.mg-combo-display { margin-bottom: 0.75rem; }
.mg-combo-badge {
    display: inline-block; padding: 0.4rem 1rem;
    background: linear-gradient(135deg, #7c3aed, #8b5cf6);
    border-radius: 2rem; color: #fff; font-weight: 700; font-size: 0.9rem;
    animation: combo-pop 0.5s ease-out;
}
@keyframes combo-pop {
    0% { transform: scale(0.5); opacity: 0; }
    60% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}
.mg-no-combo {
    display: inline-block; padding: 0.3rem 0.75rem;
    background: rgba(255,255,255,0.05); border-radius: 2rem;
    color: var(--mg-text-muted); font-size: 0.8rem;
}
.mg-game-bottom {
    display: flex; flex-direction: column; align-items: center; gap: 0.75rem; margin-top: 1.5rem;
}
.mg-btn-text {
    background: none; border: none; color: var(--mg-text-muted); font-size: 0.8rem;
    cursor: pointer; padding: 0.25rem 0.5rem; text-decoration: underline;
    transition: color 0.2s;
}
.mg-btn-text:hover { color: var(--mg-text-primary); }
.mg-game-dice .mg-btn {
    display: inline-block; padding: 0.6rem 1.25rem; border-radius: 0.5rem;
    font-weight: 600; font-size: 0.9rem; cursor: pointer; border: none;
    transition: background 0.2s, opacity 0.2s;
}
.mg-game-dice .mg-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.mg-game-dice .mg-btn-primary {
    background: var(--mg-accent); color: #000;
}
.mg-game-dice .mg-btn-primary:hover:not(:disabled) { background: var(--mg-accent-hover); }
.mg-game-dice .mg-btn-secondary {
    background: var(--mg-bg-tertiary); color: var(--mg-text-primary);
    border: 1px solid rgba(255,255,255,0.1);
}
.mg-game-dice .mg-btn-secondary:hover:not(:disabled) { background: rgba(255,255,255,0.12); }
.mg-btn-lg { padding: 1rem 2rem; font-size: 1.125rem; }
.mg-game-result { text-align: center; padding: 1.5rem; }
.mg-game-result--success { animation: result-pop 0.4s ease-out; }
@keyframes result-pop { 0% { transform: scale(0.8); opacity: 0; } 60% { transform: scale(1.05); } 100% { transform: scale(1); opacity: 1; } }
.mg-dice-result { display: flex; justify-content: center; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
.mg-dice-result .mg-die { width: 56px; height: 56px; }
.mg-game-combo-info { font-size: 1.1rem; font-weight: 700; color: #8b5cf6; margin: 0.5rem 0; }
.mg-game-combo-miss { color: var(--mg-text-muted) !important; font-weight: 400 !important; font-size: 0.9rem !important; }
.mg-game-point { font-size: 2rem; font-weight: bold; color: var(--mg-accent); margin: 0.5rem 0; }
.mg-game-bonus { margin: 0.5rem 0; }
.mg-badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; }
.mg-badge--combo { background: linear-gradient(135deg, #7c3aed, #8b5cf6); color: #fff; }
.mg-badge--miss { background: rgba(255,255,255,0.08); color: var(--mg-text-muted); }
.mg-badge--warning { background: var(--mg-warning, #f59e0b); color: #000; }
.mg-game-breakdown { color: var(--mg-text-secondary); font-size: 0.8rem; margin: 0.25rem 0 0.5rem; }
.mg-game-streak { color: var(--mg-text-muted); font-size: 0.875rem; margin-top: 0.5rem; }
/* 족보 모달 */
.mg-modal {
    display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    z-index: 9999; align-items: center; justify-content: center;
}
.mg-modal-backdrop {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.6); cursor: pointer;
}
.mg-modal-content {
    position: relative; background: var(--mg-bg-secondary); border-radius: 12px;
    max-width: 420px; width: 90%; max-height: 80vh; overflow-y: auto;
    box-shadow: 0 16px 48px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.08);
    animation: modal-pop 0.25s ease-out;
}
@keyframes modal-pop { 0% { transform: scale(0.9); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
.mg-modal-header {
    display: flex; justify-content: space-between; align-items: center;
    padding: 1rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.06);
    font-weight: 700; font-size: 1rem; color: var(--mg-text-primary);
}
.mg-modal-close-btn {
    background: none; border: none; color: var(--mg-text-muted); font-size: 1.5rem;
    cursor: pointer; padding: 0; line-height: 1; transition: color 0.2s;
}
.mg-modal-close-btn:hover { color: var(--mg-text-primary); }
.mg-modal-body { padding: 1rem 1.25rem; }
.mg-combo-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.mg-combo-table th {
    text-align: center; padding: 0.5rem 0.5rem; color: var(--mg-text-muted);
    border-bottom: 1px solid rgba(255,255,255,0.08); font-weight: 600; font-size: 0.75rem;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.mg-combo-table td {
    padding: 0.6rem 0.5rem; border-bottom: 1px solid rgba(255,255,255,0.04);
}
.mg-combo-rules {
    margin-top: 1rem; padding-top: 0.75rem;
    border-top: 1px solid rgba(255,255,255,0.06);
}
.mg-combo-rules-title {
    font-weight: 600; font-size: 0.85rem; color: var(--mg-text-primary);
    margin-bottom: 0.5rem;
}
.mg-combo-rules ul {
    list-style: none; padding: 0; margin: 0 0 0.5rem;
}
.mg-combo-rules li {
    font-size: 0.8rem; color: var(--mg-text-secondary);
    padding: 0.25rem 0; padding-left: 0.75rem;
    position: relative;
}
.mg-combo-rules li::before {
    content: ''; position: absolute; left: 0; top: 0.65rem;
    width: 4px; height: 4px; border-radius: 50%;
    background: var(--mg-text-muted);
}
.mg-combo-rules-formula {
    font-size: 0.75rem; color: var(--mg-text-muted);
    background: rgba(255,255,255,0.03); border-radius: 0.375rem;
    padding: 0.4rem 0.6rem; font-family: monospace;
}
@media (max-width: 640px) {
    .mg-die { width: 56px; height: 56px; font-size: 1.4rem; }
    .mg-dice-result .mg-die { width: 48px; height: 48px; }
    .mg-dice-container { gap: 0.5rem; }
    .mg-btn-lg { padding: 0.75rem 1.5rem; font-size: 1rem; }
}
CSS;
    }
}
