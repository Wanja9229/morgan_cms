<?php
/**
 * Morgan Edition - 룰렛 페이지
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

$g5['title'] = '운명의 룰렛';
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

// 최근 결과 (피드)
$feed_result = sql_query("SELECT rl.*, rp.rp_name, rp.rp_type, rp.rp_icon, rp.rp_color,
    m.mb_nick, m.mb_id
    FROM {$g5['mg_roulette_log_table']} rl
    JOIN {$g5['mg_roulette_prize_table']} rp ON rl.rp_id = rp.rp_id
    JOIN {$g5['member_table']} m ON rl.mb_id = m.mb_id
    WHERE rl.rl_source = 'spin'
    ORDER BY rl.rl_id DESC LIMIT 50");
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

    <!-- 탭 -->
    <div class="flex border-b border-mg-bg-tertiary mb-4">
        <button class="px-4 py-2 text-sm font-medium border-b-2 border-mg-accent text-mg-accent" id="tab-wheel" onclick="switchTab('wheel')">
            <i data-lucide="disc" class="w-4 h-4 inline-block mr-1" style="vertical-align:-2px"></i> 룰렛
        </button>
        <button class="px-4 py-2 text-sm font-medium border-b-2 border-transparent text-mg-text-muted hover:text-mg-text-primary" id="tab-feed" onclick="switchTab('feed')">
            <i data-lucide="scroll-text" class="w-4 h-4 inline-block mr-1" style="vertical-align:-2px"></i> 결과 피드
        </button>
    </div>

    <!-- 룰렛 탭 -->
    <div id="panel-wheel">
        <?php if (count($prizes) < 2) { ?>
        <div class="bg-mg-bg-secondary rounded-lg p-8 text-center text-mg-text-muted">
            관리자가 룰렛 항목을 아직 등록하지 않았습니다.
        </div>
        <?php } else { ?>

        <!-- 룰렛 휠 -->
        <div class="flex flex-col items-center mb-6">
            <div class="relative" style="width:300px;height:300px;">
                <canvas id="roulette-canvas" width="300" height="300" style="border-radius:50%;"></canvas>
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1" style="width:0;height:0;border-left:10px solid transparent;border-right:10px solid transparent;border-top:18px solid var(--mg-accent);z-index:10;"></div>
            </div>

            <div class="mt-4 text-center">
                <p class="text-sm text-mg-text-secondary mb-2">
                    비용: <span class="text-mg-accent font-bold"><?php echo number_format($cost); ?> P</span>
                    &nbsp;|&nbsp; 보유: <span class="font-bold" id="my-point"><?php echo number_format($my_point); ?> P</span>
                    <?php if ($daily_limit > 0) { ?>
                    &nbsp;|&nbsp; 오늘: <span id="today-count"><?php echo $today_count; ?></span>/<?php echo $daily_limit; ?>
                    <?php } ?>
                </p>

                <button id="spin-btn" class="px-8 py-3 rounded-lg font-bold text-lg transition-all <?php echo $can_spin['ok'] ? 'bg-mg-accent text-white hover:bg-mg-accent-hover' : 'bg-mg-bg-tertiary text-mg-text-muted cursor-not-allowed'; ?>"
                    <?php echo $can_spin['ok'] ? '' : 'disabled'; ?>>
                    돌리기
                </button>
                <?php if (!$can_spin['ok'] && $can_spin['reason']) { ?>
                <p class="text-xs text-red-400 mt-2"><?php echo $can_spin['reason']; ?></p>
                <?php } ?>
            </div>
        </div>

        <?php } ?>

        <!-- 활성 벌칙 상태 -->
        <?php if ($active_penalty && $active_penalty['rl_id']) { ?>
        <div class="bg-red-900/20 border border-red-800/30 rounded-lg p-4 mb-4">
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
                    <i data-lucide="shuffle" class="w-3 h-3 inline-block mr-1" style="vertical-align:-1px"></i> 랜덤 떠넘기기
                </button>
                <button onclick="rouletteTransferTarget(<?php echo $active_penalty['rl_id']; ?>)"
                    class="px-3 py-1.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-primary hover:bg-mg-bg-tertiary/80 transition-colors">
                    <i data-lucide="user-check" class="w-3 h-3 inline-block mr-1" style="vertical-align:-1px"></i> 지목 떠넘기기
                </button>
            </div>
            <?php } elseif ($active_penalty['rp_require_log'] || in_array($active_penalty['rp_reward_type'], array('log', 'log_nickname', 'log_image'))) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $roulette_board; ?>&w=w&rl_id=<?php echo $active_penalty['rl_id']; ?>"
                class="inline-block px-3 py-1.5 text-xs rounded bg-mg-accent text-white hover:bg-mg-accent-hover transition-colors">
                <i data-lucide="pencil" class="w-3 h-3 inline-block mr-1" style="vertical-align:-1px"></i> 벌칙 로그 작성
            </a>
            <?php } ?>

            <?php if ($active_penalty['rl_expires_at']) { ?>
            <p class="text-xs text-mg-text-muted mt-2">만료: <?php echo date('m/d H:i', strtotime($active_penalty['rl_expires_at'])); ?></p>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 결과 피드 탭 -->
    <div id="panel-feed" class="hidden">
        <?php if (empty($feed_items)) { ?>
        <div class="bg-mg-bg-secondary rounded-lg p-8 text-center text-mg-text-muted">
            아직 결과가 없습니다.
        </div>
        <?php } else { ?>
        <div class="space-y-2">
            <?php foreach ($feed_items as $fi) {
                $type_class = '';
                $type_icon = '';
                if ($fi['rp_type'] === 'reward') { $type_class = 'text-green-400'; $type_icon = 'gift'; }
                elseif ($fi['rp_type'] === 'penalty') { $type_class = 'text-red-400'; $type_icon = 'skull'; }
                elseif ($fi['rp_type'] === 'jackpot') { $type_class = 'text-yellow-400'; $type_icon = 'crown'; }
                else { $type_class = 'text-mg-text-muted'; $type_icon = 'minus'; }
            ?>
            <div class="flex items-center gap-3 bg-mg-bg-secondary rounded-lg px-4 py-2.5 border border-mg-bg-tertiary">
                <i data-lucide="<?php echo $type_icon; ?>" class="w-4 h-4 <?php echo $type_class; ?> flex-shrink-0"></i>
                <span class="text-sm text-mg-text-primary font-medium"><?php echo htmlspecialchars($fi['mb_nick'] ?? ''); ?></span>
                <span class="text-sm <?php echo $type_class; ?>"><?php echo htmlspecialchars($fi['rp_name'] ?? ''); ?></span>
                <span class="text-xs text-mg-text-muted ml-auto flex-shrink-0"><?php echo substr($fi['rl_datetime'], 5, 11); ?></span>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 벌칙 로그 게시판 -->
    <div class="mt-8 border-t border-mg-bg-tertiary pt-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-mg-text-primary">벌칙 수행 로그</h2>
            <?php if ($active_penalty && $active_penalty['rl_status'] === 'active') { ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $roulette_board; ?>&w=w&rl_id=<?php echo $active_penalty['rl_id']; ?>"
                class="text-xs px-3 py-1.5 rounded bg-mg-accent text-white hover:bg-mg-accent-hover transition-colors">
                벌칙 로그 작성
            </a>
            <?php } ?>
        </div>
        <?php
        // 벌칙 로그 게시판 최근 글
        $log_board = $roulette_board;
        $write_table = $g5['write_prefix'] . $log_board;
        $log_result = sql_query("SELECT wr_id, wr_subject, mb_id, wr_datetime, wr_comment
            FROM {$write_table} WHERE wr_is_comment = 0 ORDER BY wr_id DESC LIMIT 10");
        ?>
        <?php if ($log_result !== false) { ?>
        <div class="space-y-1">
            <?php while ($lr = sql_fetch_array($log_result)) { ?>
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $log_board; ?>&wr_id=<?php echo $lr['wr_id']; ?>"
                class="flex items-center gap-3 px-3 py-2 rounded hover:bg-mg-bg-tertiary/50 transition-colors">
                <span class="text-sm text-mg-text-primary flex-1 truncate"><?php echo htmlspecialchars($lr['wr_subject'] ?? ''); ?></span>
                <span class="text-xs text-mg-text-muted"><?php echo htmlspecialchars($lr['mb_id']); ?></span>
                <span class="text-xs text-mg-text-muted"><?php echo substr($lr['wr_datetime'], 5, 11); ?></span>
            </a>
            <?php } ?>
        </div>
        <?php } else { ?>
        <p class="text-sm text-mg-text-muted text-center py-4">게시판이 준비되지 않았습니다.</p>
        <?php } ?>
        <div class="mt-2 text-center">
            <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $log_board; ?>"
                class="text-xs text-mg-text-muted hover:text-mg-text-primary transition-colors">전체 보기 &rarr;</a>
        </div>
    </div>
</div>
</div>

<script>
var ROULETTE = {
    prizes: <?php echo json_encode($prizes_json, JSON_UNESCAPED_UNICODE); ?>,
    spinning: false,
    canvas: null,
    ctx: null,
    currentAngle: 0,

    init: function() {
        this.canvas = document.getElementById('roulette-canvas');
        if (!this.canvas) return;
        this.ctx = this.canvas.getContext('2d');
        this.draw(0);
    },

    draw: function(rotation) {
        if (!this.canvas || !this.ctx) return;
        var ctx = this.ctx;
        var w = this.canvas.width, h = this.canvas.height;
        var cx = w/2, cy = h/2, r = w/2 - 4;
        var n = this.prizes.length;
        if (n < 2) return;
        var arc = (Math.PI * 2) / n;

        ctx.clearRect(0, 0, w, h);

        for (var i = 0; i < n; i++) {
            var angle = rotation + arc * i - Math.PI/2;
            ctx.beginPath();
            ctx.moveTo(cx, cy);
            ctx.arc(cx, cy, r, angle, angle + arc);
            ctx.closePath();
            ctx.fillStyle = this.prizes[i].rp_color || '#6b7280';
            ctx.fill();
            ctx.strokeStyle = 'rgba(0,0,0,0.3)';
            ctx.lineWidth = 1;
            ctx.stroke();

            // 텍스트
            ctx.save();
            ctx.translate(cx, cy);
            ctx.rotate(angle + arc/2);
            ctx.fillStyle = '#fff';
            ctx.font = 'bold 11px Pretendard, sans-serif';
            ctx.textAlign = 'center';
            var name = this.prizes[i].rp_name;
            if (name.length > 6) name = name.substring(0, 6) + '…';
            ctx.fillText(name, r * 0.6, 4);
            ctx.restore();
        }

        // 중심 원
        ctx.beginPath();
        ctx.arc(cx, cy, 20, 0, Math.PI*2);
        ctx.fillStyle = '#1e1f22';
        ctx.fill();
        ctx.strokeStyle = 'var(--mg-accent, #f59f0a)';
        ctx.lineWidth = 3;
        ctx.stroke();
    },

    spin: function() {
        if (this.spinning || this.prizes.length < 2) return;
        var btn = document.getElementById('spin-btn');
        if (btn.disabled) return;

        this.spinning = true;
        btn.disabled = true;
        btn.textContent = '돌리는 중...';

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
                self.spinning = false;
                btn.disabled = false;
                btn.textContent = '돌리기';
                return;
            }
            self.animateSpin(data);
        })
        .catch(function() {
            if (typeof mgToast === 'function') mgToast('오류가 발생했습니다.', 'error');
            self.spinning = false;
            btn.disabled = false;
            btn.textContent = '돌리기';
        });
    },

    animateSpin: function(data) {
        var self = this;
        var n = this.prizes.length;
        var arc = (Math.PI * 2) / n;

        // 당첨 칸 인덱스 찾기
        var targetIdx = 0;
        for (var i = 0; i < n; i++) {
            if (this.prizes[i].rp_id === data.prize.rp_id) { targetIdx = i; break; }
        }

        // 목표 각도: 해당 칸 중앙이 12시(상단)에 오도록
        var targetAngle = -(arc * targetIdx + arc/2) + Math.PI * 2 * (5 + Math.random() * 3);
        var startAngle = this.currentAngle;
        var duration = 4000;
        var startTime = null;

        function ease(t) {
            return 1 - Math.pow(1 - t, 4);
        }

        function animate(ts) {
            if (!startTime) startTime = ts;
            var elapsed = ts - startTime;
            var progress = Math.min(elapsed / duration, 1);
            var eased = ease(progress);
            var angle = startAngle + (targetAngle - startAngle) * eased;
            self.draw(angle);
            self.currentAngle = angle;

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                self.onSpinComplete(data);
            }
        }
        requestAnimationFrame(animate);
    },

    onSpinComplete: function(data) {
        this.spinning = false;
        var btn = document.getElementById('spin-btn');
        var prize = data.prize;

        // 잭팟 풀 업데이트
        var poolEl = document.getElementById('jackpot-pool');
        if (poolEl && data.pool !== undefined) {
            poolEl.textContent = Number(data.pool).toLocaleString() + ' P';
        }

        // 포인트 업데이트
        var ptEl = document.getElementById('my-point');
        if (ptEl && data.my_point !== undefined) {
            ptEl.textContent = Number(data.my_point).toLocaleString() + ' P';
        }

        // 오늘 횟수
        var tcEl = document.getElementById('today-count');
        if (tcEl && data.today_count !== undefined) {
            tcEl.textContent = data.today_count;
        }

        // 결과 표시
        var msg = '', type = 'info';
        if (prize.rp_type === 'jackpot') {
            msg = '🏆 잭팟!! ' + prize.rp_name;
            type = 'success';
        } else if (prize.rp_type === 'reward') {
            msg = '🎉 ' + prize.rp_name;
            type = 'success';
        } else if (prize.rp_type === 'penalty') {
            msg = '😈 벌칙: ' + prize.rp_name;
            type = 'warning';
        } else {
            msg = '꽝!';
            type = 'info';
        }
        if (typeof mgToast === 'function') mgToast(msg, type, 5000);

        // 페이지 리로드 (벌칙 상태 갱신)
        if (prize.rp_type === 'penalty' || prize.rp_type === 'jackpot') {
            setTimeout(function() { location.reload(); }, 2000);
        } else {
            // 계속 돌릴 수 있는지 체크
            if (data.can_spin) {
                btn.disabled = false;
                btn.textContent = '돌리기';
            } else {
                btn.disabled = true;
                btn.textContent = '돌리기';
                btn.classList.remove('bg-mg-accent', 'hover:bg-mg-accent-hover');
                btn.classList.add('bg-mg-bg-tertiary', 'text-mg-text-muted', 'cursor-not-allowed');
            }
        }
    }
};

function switchTab(tab) {
    var tabs = ['wheel', 'feed'];
    tabs.forEach(function(t) {
        var panel = document.getElementById('panel-' + t);
        var btn = document.getElementById('tab-' + t);
        if (t === tab) {
            panel.classList.remove('hidden');
            btn.classList.add('border-mg-accent', 'text-mg-accent');
            btn.classList.remove('border-transparent', 'text-mg-text-muted');
        } else {
            panel.classList.add('hidden');
            btn.classList.remove('border-mg-accent', 'text-mg-accent');
            btn.classList.add('border-transparent', 'text-mg-text-muted');
        }
    });
}

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
    function initRoulette() {
        ROULETTE.init();
        var spinBtn = document.getElementById('spin-btn');
        if (spinBtn) spinBtn.addEventListener('click', function() { ROULETTE.spin(); });
    }

    // SPA 진입: executeScripts()에서 실행 시 canvas가 이미 DOM에 있음
    if (document.getElementById('roulette-canvas')) {
        initRoulette();
    }
    // 일반 페이지 로드
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initRoulette);
    }
    // SPA mg:pageLoaded (fallback)
    window.addEventListener('mg:pageLoaded', initRoulette, { once: true });
})();
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
