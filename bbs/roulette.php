<?php
/**
 * Morgan Edition - 슬롯머신 페이지
 */
include_once('./_common.php');

if ($is_guest) {
    alert('회원만 이용하실 수 있습니다.', G5_BBS_URL.'/login.php');
}

include_once(G5_PLUGIN_PATH.'/morgan/morgan.php');
include_once(G5_PLUGIN_PATH.'/morgan/roulette.php');

if (!mg_roulette_enabled()) {
    alert_close('룰렛이 비활성화되어 있습니다.');
}

$g5['title'] = '슬롯';
include_once(G5_THEME_PATH.'/head.php');

// 데이터
$prizes = mg_roulette_get_prizes();
$jackpot_pool = (int)mg_config('roulette_jackpot_pool', 0);
$cost = (int)mg_config('roulette_cost', 100);
$daily_limit = (int)mg_config('roulette_daily_limit', 3);
$today_count = mg_roulette_today_count($member['mb_id']);
$can_spin = mg_roulette_can_spin($member['mb_id']);
$active_penalty = mg_roulette_get_active_penalty($member['mb_id']);
$my_point = (int)$member['mb_point'];
$roulette_board = mg_config('roulette_board', 'roulette');

// 최근 결과 피드 (최근 20건)
$feed_result = sql_query("SELECT rl.*, rp.rp_name, rp.rp_type, rp.rp_icon, rp.rp_color,
    m.mb_nick
    FROM {$g5['mg_roulette_log_table']} rl
    JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
    JOIN {$g5['member_table']} m ON rl.mb_id = m.mb_id
    WHERE rl.rl_source = 'spin'
    ORDER BY rl.rl_id DESC LIMIT 20");
$feed_items = array();
if ($feed_result !== false) {
    while ($row = sql_fetch_array($feed_result)) {
        $feed_items[] = $row;
    }
}

// prizes JSON for JS
$prizes_json = array();
foreach ($prizes as $p) {
    $prizes_json[] = array(
        'rp_id' => (int)$p['rp_id'],
        'rp_name' => $p['rp_name'],
        'rp_type' => $p['rp_type'],
        'rp_color' => $p['rp_color'],
        'rp_icon' => $p['rp_icon'],
    );
}
?>

<div id="ajax-content">
<div class="max-w-2xl mx-auto px-4 py-6">

    <!-- 잭팟 풀 -->
    <div class="text-center mb-6">
        <p class="text-sm text-mg-text-muted mb-1">현재 잭팟 풀</p>
        <p class="text-3xl font-bold text-mg-accent" id="jackpot-pool"><?php echo number_format($jackpot_pool); ?> P</p>
    </div>

    <!-- 슬롯머신 -->
    <?php if (count($prizes) < 2) { ?>
    <div class="bg-mg-bg-secondary rounded-lg p-8 text-center text-mg-text-muted">
        관리자가 슬롯 항목을 아직 등록하지 않았습니다.
    </div>
    <?php } else { ?>

    <div class="slot-machine-wrap">
        <div class="slot-body">
            <div class="slot-display">
                <div class="slot-reel-wrap">
                    <div class="slot-reel" id="reel-0"></div>
                </div>
                <div class="slot-reel-wrap">
                    <div class="slot-reel" id="reel-1"></div>
                </div>
                <div class="slot-reel-wrap">
                    <div class="slot-reel" id="reel-2"></div>
                </div>
                <div class="slot-scanline"></div>
                <div class="slot-payline"></div>
            </div>

            <!-- 결과 오버레이 -->
            <div class="slot-result" id="slot-result" style="display:none;">
                <div class="slot-result-icon" id="slot-result-icon"></div>
                <div class="slot-result-name" id="slot-result-name"></div>
                <div class="slot-result-desc" id="slot-result-desc"></div>
            </div>
        </div>

        <!-- 비용/횟수 정보 -->
        <div class="mt-4 text-center">
            <p class="text-sm text-mg-text-secondary mb-3">
                비용: <span class="text-mg-accent font-bold"><?php echo number_format($cost); ?> P</span>
                &nbsp;|&nbsp; 보유: <span class="font-bold" id="my-point"><?php echo number_format($my_point); ?> P</span>
                <?php if ($daily_limit > 0) { ?>
                &nbsp;|&nbsp; 오늘: <span id="today-count"><?php echo $today_count; ?></span>/<?php echo $daily_limit; ?>
                <?php } ?>
            </p>

            <button id="spin-btn" class="slot-lever-btn <?php echo $can_spin['ok'] ? '' : 'disabled'; ?>"
                <?php echo $can_spin['ok'] ? '' : 'disabled'; ?>>
                <i data-lucide="play" class="w-5 h-5 inline-block mr-1" style="vertical-align:-3px"></i> 돌리기
            </button>
            <?php if (!$can_spin['ok'] && $can_spin['reason']) { ?>
            <p class="text-xs text-red-400 mt-2"><?php echo $can_spin['reason']; ?></p>
            <?php } ?>
        </div>
    </div>

    <?php } ?>

    <!-- 활성 벌칙 상태 -->
    <?php if ($active_penalty && $active_penalty['rl_id']) { ?>
    <div class="bg-red-900/20 border border-red-800/30 rounded-lg p-4 mb-4 mt-6">
        <div class="flex items-center gap-2 mb-2">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-red-400"></i>
            <span class="font-medium text-red-300">벌칙 수행 중</span>
            <span class="text-xs px-2 py-0.5 rounded bg-red-900/40 text-red-300 ml-auto">
                <?php echo $active_penalty['rl_status'] === 'pending' ? '대기중' : '수행중'; ?>
            </span>
        </div>
        <p class="text-sm text-mg-text-secondary mb-2"><?php echo htmlspecialchars($active_penalty['rp_name'] ?? ''); ?></p>
        <?php if ($active_penalty['rp_desc']) { ?>
        <p class="text-xs text-mg-text-muted mb-3"><?php echo htmlspecialchars($active_penalty['rp_desc']); ?></p>
        <?php } ?>

        <?php if ($active_penalty['rl_status'] === 'pending') { ?>
        <div class="flex gap-2 flex-wrap">
            <button onclick="rouletteAction('nullify', <?php echo $active_penalty['rl_id']; ?>)"
                class="px-3 py-1.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-primary hover:bg-mg-bg-tertiary/80 transition-colors">
                <i data-lucide="shield-off" class="w-3 h-3 inline-block mr-1" style="vertical-align:-1px"></i> 무효화
            </button>
            <button onclick="rouletteAction('transfer_random', <?php echo $active_penalty['rl_id']; ?>)"
                class="px-3 py-1.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-primary hover:bg-mg-bg-tertiary/80 transition-colors">
                <i data-lucide="shuffle" class="w-3 h-3 inline-block mr-1" style="vertical-align:-1px"></i> 랜덤 패스
            </button>
            <button onclick="rouletteTransferTarget(<?php echo $active_penalty['rl_id']; ?>)"
                class="px-3 py-1.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-primary hover:bg-mg-bg-tertiary/80 transition-colors">
                <i data-lucide="user-check" class="w-3 h-3 inline-block mr-1" style="vertical-align:-1px"></i> 지목 패스
            </button>
        </div>
        <?php } ?>

        <?php if ($active_penalty['rl_expires_at']) { ?>
        <p class="text-xs text-mg-text-muted mt-2">만료: <?php echo date('m/d H:i', strtotime($active_penalty['rl_expires_at'])); ?></p>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 최근 결과 피드 -->
    <div class="mt-6">
        <h2 class="text-sm font-semibold text-mg-text-muted uppercase tracking-wide mb-3">최근 결과</h2>
        <?php if (empty($feed_items)) { ?>
        <p class="text-sm text-mg-text-muted text-center py-4">아직 결과가 없습니다.</p>
        <?php } else { ?>
        <div class="space-y-1">
            <?php foreach ($feed_items as $fi) {
                $type_class = '';
                $type_icon = '';
                if ($fi['rp_type'] === 'reward') { $type_class = 'text-green-400'; $type_icon = 'gift'; }
                elseif ($fi['rp_type'] === 'penalty') { $type_class = 'text-red-400'; $type_icon = 'skull'; }
                elseif ($fi['rp_type'] === 'jackpot') { $type_class = 'text-yellow-400'; $type_icon = 'crown'; }
                else { $type_class = 'text-mg-text-muted'; $type_icon = 'minus'; }
            ?>
            <div class="flex items-center gap-3 bg-mg-bg-secondary rounded px-3 py-2 border border-mg-bg-tertiary">
                <i data-lucide="<?php echo $type_icon; ?>" class="w-4 h-4 <?php echo $type_class; ?> flex-shrink-0"></i>
                <span class="text-sm text-mg-text-primary font-medium"><?php echo htmlspecialchars($fi['mb_nick'] ?? ''); ?></span>
                <span class="text-sm <?php echo $type_class; ?>"><?php echo htmlspecialchars($fi['rp_name'] ?? ''); ?></span>
                <span class="text-xs text-mg-text-muted ml-auto flex-shrink-0"><?php echo substr($fi['rl_datetime'], 5, 11); ?></span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 룰렛 로그 게시판 링크 -->
    <div class="mt-6 text-center">
        <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $roulette_board; ?>"
            class="inline-flex items-center gap-1.5 text-sm text-mg-text-muted hover:text-mg-accent transition-colors">
            <i data-lucide="book-open" class="w-4 h-4"></i>
            룰렛 로그 게시판 바로가기 &rarr;
        </a>
    </div>

</div>
</div>

<style>
/* ── 슬롯머신 ── */
.slot-machine-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 1.5rem;
}

.slot-body {
    position: relative;
    width: 100%;
    max-width: 380px;
    background: #0a0a0a;
    border: 2px solid #333;
    border-radius: 12px;
    padding: 24px 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,.6), inset 0 0 30px rgba(0,0,0,.8);
}

.slot-display {
    display: flex;
    gap: 8px;
    justify-content: center;
    position: relative;
    padding: 8px 0;
}

.slot-reel-wrap {
    width: 100px;
    height: 180px;
    overflow: hidden;
    border-radius: 6px;
    background: #111;
    border: 1px solid #2a2a2a;
    position: relative;
    -webkit-mask-image: linear-gradient(to bottom, transparent 0%, black 25%, black 75%, transparent 100%);
    mask-image: linear-gradient(to bottom, transparent 0%, black 25%, black 75%, transparent 100%);
}

.slot-reel {
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: none;
}

.slot-reel .slot-item {
    width: 100%;
    height: 60px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    gap: 2px;
}

.slot-reel .slot-item-icon {
    font-size: 1.4rem;
    line-height: 1;
}

.slot-reel .slot-item-name {
    font-size: 0.7rem;
    font-weight: 600;
    color: #999;
    text-align: center;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.slot-scanline {
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(255,255,255,.012) 2px,
        rgba(255,255,255,.012) 4px
    );
    pointer-events: none;
    border-radius: 6px;
}

.slot-payline {
    position: absolute;
    left: 8px;
    right: 8px;
    top: 50%;
    height: 2px;
    background: var(--mg-accent);
    opacity: 0.4;
    transform: translateY(-1px);
    box-shadow: 0 0 8px rgba(245,159,10,.3);
    pointer-events: none;
}

/* 결과 오버레이 */
.slot-result {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: rgba(10,10,10,.92);
    border-radius: 10px;
    z-index: 10;
    animation: slotResultIn .4s ease;
}

@keyframes slotResultIn {
    from { opacity: 0; transform: scale(.9); }
    to { opacity: 1; transform: scale(1); }
}

.slot-result-icon { font-size: 2.5rem; margin-bottom: 8px; }
.slot-result-name { font-size: 1.3rem; font-weight: 800; color: #fff; margin-bottom: 4px; }
.slot-result-desc { font-size: 0.85rem; color: var(--mg-text-muted); }

.slot-result.type-reward .slot-result-name { color: #4ade80; }
.slot-result.type-penalty .slot-result-name { color: #f87171; }
.slot-result.type-jackpot .slot-result-name { color: #fbbf24; text-shadow: 0 0 12px rgba(251,191,36,.5); }
.slot-result.type-blank .slot-result-name { color: #6b7280; }

/* 레버 버튼 */
.slot-lever-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 36px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, var(--mg-accent), #d97706);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all .2s;
    box-shadow: 0 4px 12px rgba(245,159,10,.25);
}

.slot-lever-btn:hover:not(.disabled):not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245,159,10,.35);
}

.slot-lever-btn:active:not(.disabled):not(:disabled) {
    transform: translateY(0);
}

.slot-lever-btn.disabled,
.slot-lever-btn:disabled {
    background: var(--mg-bg-tertiary);
    color: var(--mg-text-muted);
    cursor: not-allowed;
    box-shadow: none;
}

.slot-lever-btn.spinning {
    animation: slotBtnPulse 1s infinite;
}

@keyframes slotBtnPulse {
    0%, 100% { box-shadow: 0 4px 12px rgba(245,159,10,.25); }
    50% { box-shadow: 0 4px 20px rgba(245,159,10,.5); }
}

@media (max-width: 480px) {
    .slot-body { padding: 16px 10px; }
    .slot-reel-wrap { width: 80px; height: 150px; }
    .slot-reel .slot-item { height: 50px; }
    .slot-reel .slot-item-icon { font-size: 1.1rem; }
    .slot-reel .slot-item-name { font-size: 0.6rem; }
}
</style>

<script>
var SLOT = {
    prizes: <?php echo json_encode($prizes_json, JSON_UNESCAPED_UNICODE); ?>,
    spinning: false,
    reelHeight: 60,

    init: function() {
        if (!document.getElementById('reel-0')) return;
        for (var i = 0; i < 3; i++) {
            this.fillReel(i);
        }
    },

    fillReel: function(reelIdx) {
        var reel = document.getElementById('reel-' + reelIdx);
        if (!reel) return;
        reel.innerHTML = '';
        var items = this.prizes.slice();
        for (var s = 0; s < reelIdx; s++) {
            items.push(items.shift());
        }
        for (var set = 0; set < 4; set++) {
            for (var j = 0; j < items.length; j++) {
                reel.appendChild(this.createItemEl(items[j]));
            }
        }
        reel.style.transform = 'translateY(-' + this.reelHeight + 'px)';
    },

    createItemEl: function(prize) {
        var div = document.createElement('div');
        div.className = 'slot-item';
        var iconName = prize.rp_icon || 'help-circle';
        div.innerHTML = '<span class="slot-item-icon"><i data-lucide="' + iconName + '" style="width:1.4rem;height:1.4rem;color:' + (prize.rp_color || '#999') + ';"></i></span>' +
            '<span class="slot-item-name" style="color:' + (prize.rp_color || '#999') + '">' +
            this.truncate(prize.rp_name, 8) + '</span>';
        if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [div] });
        return div;
    },

    truncate: function(str, max) {
        return str.length > max ? str.substring(0, max) + '…' : str;
    },

    spin: function() {
        if (this.spinning || this.prizes.length < 2) return;
        var btn = document.getElementById('spin-btn');
        if (btn.disabled) return;

        this.spinning = true;
        btn.disabled = true;
        btn.classList.add('spinning');
        btn.innerHTML = '<i data-lucide="loader-2" class="w-5 h-5 inline-block mr-1 animate-spin" style="vertical-align:-3px"></i> 돌리는 중…';
        if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });

        var resultEl = document.getElementById('slot-result');
        if (resultEl) resultEl.style.display = 'none';

        var self = this;
        fetch('<?php echo G5_BBS_URL; ?>/roulette_spin.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
            body: JSON.stringify({})
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.error) {
                if (typeof mgToast === 'function') mgToast(data.error, 'error');
                self.resetBtn();
                return;
            }
            self.animateReels(data);
        })
        .catch(function() {
            if (typeof mgToast === 'function') mgToast('오류가 발생했습니다.', 'error');
            self.resetBtn();
        });
    },

    animateReels: function(data) {
        var self = this;
        var prize = data.prize;
        var n = this.prizes.length;

        var targetIdx = 0;
        for (var i = 0; i < n; i++) {
            if (this.prizes[i].rp_id === prize.rp_id) { targetIdx = i; break; }
        }

        var reelDelays = [0, 400, 800];
        var reelDurations = [2000, 2600, 3200];

        for (var r = 0; r < 3; r++) {
            this.animateOneReel(r, targetIdx, reelDurations[r], reelDelays[r]);
        }

        var totalTime = reelDelays[2] + reelDurations[2] + 300;
        setTimeout(function() {
            self.onSpinComplete(data);
        }, totalTime);
    },

    animateOneReel: function(reelIdx, targetIdx, duration, delay) {
        var self = this;
        var reel = document.getElementById('reel-' + reelIdx);
        if (!reel) return;

        var n = this.prizes.length;
        var h = this.reelHeight;
        var wrap = reel.parentElement;
        if (wrap) {
            var itemEl = reel.querySelector('.slot-item');
            if (itemEl) h = itemEl.offsetHeight || h;
        }

        setTimeout(function() {
            reel.innerHTML = '';
            var items = self.prizes.slice();

            for (var s = 0; s < reelIdx * 3; s++) {
                items.push(items.shift());
            }

            var totalSets = 10;
            for (var set = 0; set < totalSets; set++) {
                for (var j = 0; j < items.length; j++) {
                    reel.appendChild(self.createItemEl(items[j]));
                }
            }
            var prevIdx = (targetIdx - 1 + n) % n;
            var nextIdx = (targetIdx + 1) % n;
            reel.appendChild(self.createItemEl(self.prizes[prevIdx]));
            reel.appendChild(self.createItemEl(self.prizes[targetIdx]));
            reel.appendChild(self.createItemEl(self.prizes[nextIdx]));

            var stopAt = (totalSets * n + 1) * h - h;

            reel.style.transition = 'none';
            reel.style.transform = 'translateY(0)';

            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    reel.style.transition = 'transform ' + duration + 'ms cubic-bezier(.15,.6,.2,1)';
                    reel.style.transform = 'translateY(-' + stopAt + 'px)';
                });
            });
        }, delay);
    },

    onSpinComplete: function(data) {
        this.spinning = false;
        var prize = data.prize;

        var resultEl = document.getElementById('slot-result');
        var iconEl = document.getElementById('slot-result-icon');
        var nameEl = document.getElementById('slot-result-name');
        var descEl = document.getElementById('slot-result-desc');

        if (resultEl) {
            resultEl.className = 'slot-result type-' + prize.rp_type;
            resultEl.style.display = '';
            if (iconEl) {
                var iconName = prize.rp_icon || 'help-circle';
                iconEl.innerHTML = '<i data-lucide="' + iconName + '" style="width:2.5rem;height:2.5rem;color:' + (prize.rp_color || '#999') + ';"></i>';
                if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [iconEl] });
            }
            if (nameEl) nameEl.textContent = prize.rp_name;
            if (descEl) {
                if (prize.rp_type === 'jackpot') descEl.textContent = '잭팟 풀 전액 획득!';
                else if (prize.rp_type === 'penalty') descEl.textContent = '벌칙 당첨…';
                else if (prize.rp_type === 'reward') descEl.textContent = '보상 획득!';
                else descEl.textContent = '다음 기회에!';
            }
        }

        var poolEl = document.getElementById('jackpot-pool');
        if (poolEl && data.pool !== undefined) poolEl.textContent = Number(data.pool).toLocaleString() + ' P';

        var ptEl = document.getElementById('my-point');
        if (ptEl && data.my_point !== undefined) ptEl.textContent = Number(data.my_point).toLocaleString() + ' P';

        var tcEl = document.getElementById('today-count');
        if (tcEl && data.today_count !== undefined) tcEl.textContent = data.today_count;

        var msg = '', type = 'info';
        if (prize.rp_type === 'jackpot') { msg = '🏆 잭팟!! ' + prize.rp_name; type = 'success'; }
        else if (prize.rp_type === 'reward') { msg = '🎉 ' + prize.rp_name; type = 'success'; }
        else if (prize.rp_type === 'penalty') { msg = '😈 벌칙: ' + prize.rp_name; type = 'warning'; }
        else { msg = '💨 꽝!'; type = 'info'; }
        if (typeof mgToast === 'function') mgToast(msg, type, 5000);

        if (prize.rp_type === 'penalty' || prize.rp_type === 'jackpot') {
            setTimeout(function() { location.reload(); }, 2500);
        } else {
            this.resetBtn();
            if (!data.can_spin) {
                var btn = document.getElementById('spin-btn');
                if (btn) {
                    btn.disabled = true;
                    btn.classList.add('disabled');
                }
            }
        }
    },

    resetBtn: function() {
        this.spinning = false;
        var btn = document.getElementById('spin-btn');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('spinning', 'disabled');
            btn.innerHTML = '<i data-lucide="play" class="w-5 h-5 inline-block mr-1" style="vertical-align:-3px"></i> 돌리기';
            if (typeof lucide !== 'undefined') lucide.createIcons({ nodes: [btn] });
        }
    }
};

function rouletteAction(action, rl_id, target_mb_id) {
    var body = { action: action, rl_id: rl_id };
    if (target_mb_id) body.target_mb_id = target_mb_id;

    fetch('<?php echo G5_BBS_URL; ?>/roulette_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify(body)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.error) {
            if (typeof mgToast === 'function') mgToast(data.error, 'error');
        } else {
            if (typeof mgToast === 'function') mgToast(data.message || '처리 완료', 'success');
            setTimeout(function() { location.reload(); }, 1000);
        }
    });
}

function rouletteTransferTarget(rl_id) {
    var target = prompt('떠넘길 회원의 아이디를 입력하세요:');
    if (target) rouletteAction('transfer_target', rl_id, target.trim());
}

// 초기화 — SPA/일반 양쪽 대응
(function() {
    function initSlot() {
        SLOT.init();
        var spinBtn = document.getElementById('spin-btn');
        if (spinBtn && !spinBtn._bound) {
            spinBtn._bound = true;
            spinBtn.addEventListener('click', function() { SLOT.spin(); });
        }
    }

    if (document.getElementById('reel-0')) {
        initSlot();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSlot);
    }
    window.addEventListener('mg:pageLoaded', initSlot, { once: true });
})();
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
