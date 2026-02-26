<?php
/**
 * Morgan Edition - 운세뽑기 미니게임
 *
 * 랜덤 운세 카드 뽑기. 별점(1~5) 기반 가중치 추첨 + 포인트.
 * 단일 play() 패턴 (멀티스텝 없음).
 */

if (!defined('_GNUBOARD_')) exit;

require_once __DIR__ . '/MG_Game_Base.php';

class MG_Game_Fortune extends MG_Game_Base {

    private $bonusMultiplier;
    private $streakBonusDays;

    // 별점별 확률 기본값 (%, 합계 100 이하)
    private static $DEFAULT_WEIGHTS = [
        1 => 40,
        2 => 25,
        3 => 20,
        4 => 10,
        5 => 5,
    ];

    private $starWeights = [];

    // 별점별 색상
    private static $STAR_COLORS = [
        1 => '#6b7280', // 회색
        2 => '#22c55e', // 초록
        3 => '#3b82f6', // 파랑
        4 => '#a855f7', // 보라
        5 => '#f59f0a', // 금색
    ];

    public function __construct() {
        $this->bonusMultiplier = (float)mg_get_config('dice_bonus_multiplier', 2);
        $this->streakBonusDays = (int)mg_get_config('attendance_streak_bonus_days', 7);

        // 별점별 가중치 로드
        foreach (self::$DEFAULT_WEIGHTS as $star => $default) {
            $this->starWeights[$star] = max(0, (int)mg_get_config('fortune_weight_' . $star, $default));
        }
    }

    public function getCode(): string {
        return 'fortune';
    }

    public function getName(): string {
        return '운세뽑기';
    }

    public function getDescription(): string {
        return '오늘의 운세를 뽑아보세요! 별이 많을수록 높은 포인트를 획득합니다.';
    }

    /**
     * 게임 실행
     */
    public function play(string $mb_id): array {
        if ($this->hasPlayedToday($mb_id)) {
            return ['success' => false, 'message' => '오늘은 이미 출석했습니다.'];
        }

        // DB에서 운세 로드
        $fortunes = $this->loadFortunes();
        if (empty($fortunes)) {
            return ['success' => false, 'message' => '운세 데이터가 없습니다. 관리자에게 문의하세요.'];
        }

        // 가중치 랜덤 선택
        $fortune = $this->weightedRandom($fortunes);

        // 포인트 계산
        $streak = $this->getStreakDays($mb_id);
        $basePoint = (int)$fortune['gf_point'];
        $isBonus = ($streak >= ($this->streakBonusDays - 1));
        $finalPoint = $isBonus ? (int)($basePoint * $this->bonusMultiplier) : $basePoint;

        $gameData = [
            'gf_id'     => (int)$fortune['gf_id'],
            'star'      => (int)$fortune['gf_star'],
            'text'      => $fortune['gf_text'],
            'basePoint' => $basePoint,
            'isBonus'   => $isBonus,
            'streak'    => $streak + 1,
        ];

        // 출석 저장
        $this->saveAttendance($mb_id, $finalPoint, $gameData);

        return [
            'success' => true,
            'point'   => $finalPoint,
            'message' => $fortune['gf_text'] . ' +' . number_format($finalPoint) . 'P',
            'data'    => $gameData,
            'phase'   => 'finalize',
        ];
    }

    /**
     * DB에서 사용 중인 운세 전체 로드
     */
    private function loadFortunes(): array {
        global $g5;
        $result = sql_query("SELECT * FROM {$g5['mg_game_fortune_table']} WHERE gf_use = 1 ORDER BY gf_sort, gf_id");
        $list = [];
        while ($row = sql_fetch_array($result)) {
            $list[] = $row;
        }
        return $list;
    }

    /**
     * 별점 확률(%) 기반 랜덤 선택
     *
     * 각 별점의 확률(%)에 따라 먼저 별점을 결정한 뒤,
     * 해당 별점 내에서 균등 랜덤으로 운세를 선택한다.
     */
    private function weightedRandom(array $fortunes): array {
        // 별점별 운세 그룹화
        $byStars = [];
        foreach ($fortunes as $f) {
            $byStars[(int)$f['gf_star']][] = $f;
        }

        // 확률이 0보다 큰 별점만 후보
        $candidates = [];
        foreach ($byStars as $star => $items) {
            $pct = $this->starWeights[$star] ?? 0;
            if ($pct > 0) {
                $candidates[] = ['star' => $star, 'pct' => $pct, 'items' => $items];
            }
        }

        // 모든 확률이 0이면 균등 랜덤 폴백
        if (empty($candidates)) {
            return $fortunes[array_rand($fortunes)];
        }

        // 누적 확률로 별점 선택 (0.01% 단위 정밀도)
        $total = 0;
        foreach ($candidates as $c) {
            $total += $c['pct'];
        }
        $roll = mt_rand(1, $total * 100) / 100; // 0.01 ~ total

        $cumulative = 0;
        $chosen = $candidates[0];
        foreach ($candidates as $c) {
            $cumulative += $c['pct'];
            if ($roll <= $cumulative) {
                $chosen = $c;
                break;
            }
        }

        // 선택된 별점 내 균등 랜덤
        return $chosen['items'][array_rand($chosen['items'])];
    }

    /**
     * 결과 HTML (출석 완료 후 재방문 시)
     */
    public function renderResult(array $result): string {
        $data = $result['data'] ?? [];
        $star = (int)($data['star'] ?? 1);
        $text = htmlspecialchars($data['text'] ?? '');
        $point = (int)($result['point'] ?? 0);
        $streak = (int)($data['streak'] ?? 0);
        $isBonus = !empty($data['isBonus']);
        $basePoint = (int)($data['basePoint'] ?? 0);
        $color = self::$STAR_COLORS[$star] ?? '#6b7280';

        $stars = str_repeat('★', $star) . str_repeat('☆', 5 - $star);

        // 포인트 내역
        $parts = [];
        $parts[] = '운세 ' . number_format($basePoint) . 'P';
        if ($isBonus) {
            $parts[] = '연속출석 ×' . $this->bonusMultiplier;
        }
        $breakdown = implode(' → ', $parts) . ' = ' . number_format($point) . 'P';

        $html = '<div class="mg-game-result">';
        $html .= '<div style="text-align:center; padding:1.5rem 1rem;">';

        // 별점
        $html .= '<div style="font-size:1.5rem; color:' . $color . '; margin-bottom:0.75rem;">' . $stars . '</div>';

        // 운세 텍스트
        $html .= '<div style="font-size:1.1rem; color:var(--mg-text-primary); margin-bottom:1rem;">' . $text . '</div>';

        // 포인트
        $html .= '<div style="font-size:1.5rem; font-weight:700; color:var(--mg-accent);">+' . number_format($point) . 'P</div>';
        $html .= '<p style="font-size:0.8rem; color:var(--mg-text-muted); margin-top:0.5rem;">' . $breakdown . '</p>';

        // 연속 출석
        if ($streak > 0) {
            $html .= '<div style="margin-top:0.75rem; font-size:0.85rem; color:var(--mg-text-secondary);">';
            $html .= $streak . '일 연속 출석 중';
            if ($isBonus) $html .= ' <span style="color:var(--mg-accent);">보너스 적용!</span>';
            $html .= '</div>';
        }

        $html .= '</div></div>';
        return $html;
    }

    /**
     * 게임 UI HTML
     */
    public function renderUI(): string {
        $html = '<div id="fortune-game-ui" style="text-align:center; padding:1rem 0;">';

        // 카드
        $html .= '<div id="fortune-card" style="perspective:800px; width:200px; height:280px; margin:0 auto 1.5rem;">';
        $html .= '<div id="fortune-card-inner" style="position:relative; width:100%; height:100%; transition:transform 0.8s cubic-bezier(0.4,0,0.2,1); transform-style:preserve-3d;">';

        // 앞면 (뒤집기 전)
        $html .= '<div id="fortune-card-front" style="position:absolute; inset:0; backface-visibility:hidden; border-radius:1rem; background:linear-gradient(135deg, #2b2d31 0%, #1e1f22 100%); border:2px solid var(--mg-accent); display:flex; flex-direction:column; align-items:center; justify-content:center; cursor:pointer;" onclick="document.getElementById(\'btn-play-game\').click();">';
        $html .= '<div style="font-size:3rem; margin-bottom:0.5rem; color:var(--mg-accent);">?</div>';
        $html .= '<div style="font-size:1rem; color:var(--mg-text-secondary);">터치하여 뽑기</div>';
        $html .= '</div>';

        // 뒷면 (결과)
        $html .= '<div id="fortune-card-back" style="position:absolute; inset:0; backface-visibility:hidden; border-radius:1rem; background:linear-gradient(135deg, #2b2d31 0%, #1e1f22 100%); border:2px solid var(--mg-accent); display:flex; flex-direction:column; align-items:center; justify-content:center; transform:rotateY(180deg); padding:1.5rem;">';
        $html .= '<div id="fortune-result-stars" style="font-size:1.5rem; margin-bottom:0.75rem;"></div>';
        $html .= '<div id="fortune-result-text" style="font-size:1rem; color:var(--mg-text-primary); text-align:center; line-height:1.5; margin-bottom:1rem;"></div>';
        $html .= '<div id="fortune-result-point" style="font-size:1.75rem; font-weight:700; color:var(--mg-accent);"></div>';
        $html .= '<div id="fortune-result-breakdown" style="font-size:0.75rem; color:var(--mg-text-muted); margin-top:0.25rem;"></div>';
        $html .= '</div>';

        $html .= '</div></div>'; // card-inner, card

        // 버튼
        $html .= '<button type="button" id="btn-play-game" style="display:inline-flex; align-items:center; gap:0.5rem; padding:0.75rem 2rem; background:var(--mg-accent); color:#000; font-weight:600; font-size:1rem; border:none; border-radius:0.75rem; cursor:pointer;">운세 뽑기</button>';

        // 결과 영역
        $html .= '<div id="game-result" style="margin-top:1rem;"></div>';

        $html .= '</div>';
        return $html;
    }

    /**
     * CSS
     */
    public function getCSS(): string {
        return '
#fortune-card-inner.flipped {
    transform: rotateY(180deg);
}
#fortune-card-front:hover {
    border-color: var(--mg-accent-hover);
    box-shadow: 0 0 20px rgba(245,159,10,0.15);
}
#btn-play-game:hover {
    opacity: 0.9;
}
#btn-play-game:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
@media (max-width: 640px) {
    #fortune-card {
        width: 170px !important;
        height: 240px !important;
    }
}
';
    }

    /**
     * JavaScript
     */
    public function getJavaScript(): string {
        $g5_bbs_url = G5_BBS_URL;

        return <<<JS
(function() {
    var btnPlay = document.getElementById('btn-play-game');
    var cardInner = document.getElementById('fortune-card-inner');
    if (!btnPlay || !cardInner) return;

    var starColors = {1:'#6b7280', 2:'#22c55e', 3:'#3b82f6', 4:'#a855f7', 5:'#f59f0a'};

    btnPlay.addEventListener('click', function() {
        if (btnPlay.disabled) return;
        btnPlay.disabled = true;
        btnPlay.textContent = '뽑는 중...';

        fetch('{$g5_bbs_url}/attendance_play.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ action: 'play', game: 'fortune' })
        })
        .then(function(r) { return r.json(); })
        .then(function(result) {
            if (!result.success) {
                alert(result.message);
                btnPlay.disabled = false;
                btnPlay.textContent = '운세 뽑기';
                return;
            }

            var data = result.data || {};
            var star = data.star || 1;
            var color = starColors[star] || '#6b7280';
            var starsStr = '';
            for (var i = 0; i < 5; i++) starsStr += (i < star) ? '★' : '☆';

            // 뒷면에 결과 주입
            document.getElementById('fortune-result-stars').textContent = starsStr;
            document.getElementById('fortune-result-stars').style.color = color;

            document.getElementById('fortune-result-text').textContent = data.text || '';
            document.getElementById('fortune-result-point').textContent = '+' + Number(result.point).toLocaleString() + 'P';

            // 내역
            var parts = ['운세 ' + Number(data.basePoint).toLocaleString() + 'P'];
            if (data.isBonus) parts.push('연속출석 보너스');
            document.getElementById('fortune-result-breakdown').textContent = parts.join(' + ');

            // 카드 뒤집기
            cardInner.classList.add('flipped');

            // 버튼 숨기기
            setTimeout(function() {
                btnPlay.style.display = 'none';
            }, 400);

            // 최종 결과 HTML
            if (result.html) {
                setTimeout(function() {
                    var resultDiv = document.getElementById('game-result');
                    if (resultDiv) resultDiv.innerHTML = result.html;
                }, 1200);
            }
        })
        .catch(function(err) {
            console.error('[Fortune] Error:', err);
            btnPlay.disabled = false;
            btnPlay.textContent = '운세 뽑기';
        });
    });
})();
JS;
    }
}
