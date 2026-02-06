<?php
/**
 * Morgan Edition - 주사위 게임
 *
 * 출석체크 시 랜덤 포인트 획득
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Game_Base.php';

class MG_Game_Dice extends MG_Game_Base {
    private $minPoint = 10;
    private $maxPoint = 100;
    private $bonusMultiplier = 2; // 연속 출석 시 보너스 배율
    private $streakBonusDays = 7; // 보너스 적용 연속 일수

    public function __construct() {
        // mg_get_config로 직접 설정값 로드
        $this->minPoint = (int)mg_get_config('dice_min', 10);
        $this->maxPoint = (int)mg_get_config('dice_max', 100);
        $this->bonusMultiplier = (float)mg_get_config('dice_bonus_multiplier', 2);
        $this->streakBonusDays = (int)mg_get_config('attendance_streak_bonus_days', 7);
    }

    public function getCode(): string {
        return 'dice';
    }

    public function getName(): string {
        return '주사위';
    }

    public function getDescription(): string {
        return "주사위를 굴려 {$this->minPoint}~{$this->maxPoint}P 사이의 랜덤 포인트를 획득합니다.";
    }

    /**
     * 연속 출석 일수 계산 (설정된 보너스 일수 기준으로 조회)
     */
    protected function getStreakDays(string $mb_id): int {
        global $g5;

        $mb_id = sql_real_escape_string($mb_id);
        $today = date('Y-m-d');
        $checkDays = max($this->streakBonusDays, 7); // 최소 7일

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

    public function play(string $mb_id): array {
        // 이미 출석했는지 확인
        if ($this->hasPlayedToday($mb_id)) {
            return [
                'success' => false,
                'point' => 0,
                'message' => '오늘은 이미 출석하셨습니다.',
                'data' => []
            ];
        }

        // 주사위 결과 (1~6)
        $dice1 = rand(1, 6);
        $dice2 = rand(1, 6);
        $diceTotal = $dice1 + $dice2;

        // 포인트 계산 (min~max 범위에서 주사위 결과에 비례)
        // 주사위 합: 2~12, 정규화하여 min~max 범위로
        $ratio = ($diceTotal - 2) / 10; // 0 ~ 1
        $basePoint = $this->minPoint + (int)(($this->maxPoint - $this->minPoint) * $ratio);

        // 연속 출석 보너스
        $streak = $this->getStreakDays($mb_id);
        $isBonus = ($streak >= ($this->streakBonusDays - 1)); // 오늘 포함
        $finalPoint = $isBonus ? (int)($basePoint * $this->bonusMultiplier) : $basePoint;

        // 더블 보너스 (같은 눈)
        $isDouble = ($dice1 === $dice2);
        if ($isDouble) {
            $finalPoint = (int)($finalPoint * 1.5);
        }

        // 출석 기록 저장
        $gameData = [
            'dice1' => $dice1,
            'dice2' => $dice2,
            'diceTotal' => $diceTotal,
            'basePoint' => $basePoint,
            'streak' => $streak + 1,
            'isBonus' => $isBonus,
            'isDouble' => $isDouble
        ];
        $this->saveAttendance($mb_id, $finalPoint, $gameData);

        // 메시지 생성
        $message = "{$finalPoint}P를 획득했습니다!";
        if ($isDouble) {
            $message .= ' (더블!)';
        }
        if ($isBonus) {
            $message .= " ({$this->streakBonusDays}일 연속 보너스!)";
        }

        return [
            'success' => true,
            'point' => $finalPoint,
            'message' => $message,
            'data' => $gameData
        ];
    }

    public function renderResult(array $result): string {
        if (!$result['success']) {
            return '<div class="mg-game-result mg-game-result--fail">
                <p class="text-mg-text-muted">' . htmlspecialchars($result['message']) . '</p>
            </div>';
        }

        $data = $result['data'];
        $dice1 = $data['dice1'];
        $dice2 = $data['dice2'];
        $point = $result['point'];

        $bonusText = '';
        if (!empty($data['isDouble'])) {
            $bonusText .= '<span class="mg-badge mg-badge--success">더블!</span> ';
        }
        if (!empty($data['isBonus'])) {
            $bonusText .= '<span class="mg-badge mg-badge--warning">' . $this->streakBonusDays . '일 연속!</span>';
        }

        return '<div class="mg-game-result mg-game-result--success">
            <div class="mg-dice-result">
                <span class="mg-dice" data-value="' . $dice1 . '"></span>
                <span class="mg-dice" data-value="' . $dice2 . '"></span>
            </div>
            <p class="mg-game-point">+' . number_format($point) . 'P</p>
            ' . ($bonusText ? '<p class="mg-game-bonus">' . $bonusText . '</p>' : '') . '
            <p class="mg-game-streak">연속 출석 ' . $data['streak'] . '일째</p>
        </div>';
    }

    public function renderUI(): string {
        return '<div class="mg-game-ui mg-game-dice">
            <div class="mg-dice-container">
                <span class="mg-dice mg-dice--idle" id="dice1"></span>
                <span class="mg-dice mg-dice--idle" id="dice2"></span>
            </div>
            <p class="mg-game-desc">' . htmlspecialchars($this->getDescription()) . '</p>
            <button type="button" class="mg-btn mg-btn-primary mg-btn-lg" id="btn-play-game">
                🎲 주사위 굴리기
            </button>
        </div>';
    }

    public function getJavaScript(): string {
        return <<<'JS'
(function() {
    const btnPlay = document.getElementById('btn-play-game');
    const dice1 = document.getElementById('dice1');
    const dice2 = document.getElementById('dice2');
    const resultContainer = document.getElementById('game-result');

    if (!btnPlay) return;

    btnPlay.addEventListener('click', async function() {
        // 버튼 비활성화
        btnPlay.disabled = true;
        btnPlay.textContent = '🎲 굴리는 중...';

        // 주사위 애니메이션
        dice1.classList.add('mg-dice--rolling');
        dice2.classList.add('mg-dice--rolling');

        try {
            // API 호출
            const response = await fetch(g5_bbs_url + '/attendance_play.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ game: 'dice' })
            });

            const result = await response.json();

            // 애니메이션 종료 후 결과 표시
            setTimeout(() => {
                dice1.classList.remove('mg-dice--rolling');
                dice2.classList.remove('mg-dice--rolling');

                if (result.success && result.data) {
                    dice1.dataset.value = result.data.dice1;
                    dice2.dataset.value = result.data.dice2;
                }

                // 결과 HTML 표시
                if (resultContainer) {
                    resultContainer.innerHTML = result.html || '';
                }

                // 성공 시 버튼 숨기기
                if (result.success) {
                    btnPlay.style.display = 'none';
                } else {
                    btnPlay.disabled = false;
                    btnPlay.textContent = '🎲 주사위 굴리기';
                }
            }, 1000);

        } catch (error) {
            console.error('Game error:', error);
            dice1.classList.remove('mg-dice--rolling');
            dice2.classList.remove('mg-dice--rolling');
            btnPlay.disabled = false;
            btnPlay.textContent = '🎲 주사위 굴리기';
            alert('오류가 발생했습니다. 다시 시도해주세요.');
        }
    });
})();
JS;
    }

    public function getCSS(): string {
        return <<<'CSS'
.mg-game-dice {
    text-align: center;
    padding: 2rem;
}

.mg-dice-container {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.mg-dice {
    width: 60px;
    height: 60px;
    background: var(--mg-bg-tertiary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    color: var(--mg-text-primary);
    position: relative;
}

.mg-dice::before {
    content: '?';
}

.mg-dice[data-value="1"]::before { content: '⚀'; }
.mg-dice[data-value="2"]::before { content: '⚁'; }
.mg-dice[data-value="3"]::before { content: '⚂'; }
.mg-dice[data-value="4"]::before { content: '⚃'; }
.mg-dice[data-value="5"]::before { content: '⚄'; }
.mg-dice[data-value="6"]::before { content: '⚅'; }

.mg-dice--rolling {
    animation: dice-roll 0.15s infinite;
}

@keyframes dice-roll {
    0% { transform: rotate(0deg) scale(1); }
    25% { transform: rotate(90deg) scale(1.1); }
    50% { transform: rotate(180deg) scale(1); }
    75% { transform: rotate(270deg) scale(1.1); }
    100% { transform: rotate(360deg) scale(1); }
}

.mg-game-desc {
    color: var(--mg-text-muted);
    font-size: 0.875rem;
    margin-bottom: 1.5rem;
}

.mg-game-result {
    text-align: center;
    padding: 1.5rem;
}

.mg-game-result--success {
    animation: result-pop 0.3s ease-out;
}

@keyframes result-pop {
    0% { transform: scale(0.8); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.mg-dice-result {
    display: flex;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.mg-dice-result .mg-dice {
    width: 80px;
    height: 80px;
    font-size: 2.5rem;
    background: var(--mg-accent);
    color: #000;
}

.mg-game-point {
    font-size: 2rem;
    font-weight: bold;
    color: var(--mg-accent);
    margin: 0.5rem 0;
}

.mg-game-bonus {
    margin: 0.5rem 0;
}

.mg-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
}

.mg-badge--success {
    background: var(--mg-success, #22c55e);
    color: #fff;
}

.mg-badge--warning {
    background: var(--mg-warning, #f59e0b);
    color: #000;
}

.mg-game-streak {
    color: var(--mg-text-muted);
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.mg-btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}
CSS;
    }
}
