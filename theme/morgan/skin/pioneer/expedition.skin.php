<?php
/**
 * Morgan Edition - 탐색 파견 스킨
 */

if (!defined('_GNUBOARD_')) exit;

$expedition_api = G5_BBS_URL . '/expedition_api.php';
?>

<div class="mg-inner" id="expedition-app">
    <!-- 탭 네비게이션 -->
    <div class="flex gap-2 mb-6 border-b border-mg-bg-tertiary pb-3">
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php" class="px-4 py-2 text-sm font-medium text-mg-text-secondary hover:text-mg-text-primary rounded-lg transition-colors">시설 건설</a>
        <a href="<?php echo G5_BBS_URL; ?>/pioneer.php?view=expedition" class="px-4 py-2 text-sm font-medium text-mg-accent bg-mg-accent/10 rounded-lg">탐색 파견</a>
    </div>

    <!-- 상단: 스태미나 + 슬롯 -->
    <div class="card mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-mg-accent"><?php echo mg_icon('bolt', 'w-6 h-6'); ?></span>
                <div>
                    <div class="text-xs text-mg-text-muted">노동력</div>
                    <div class="font-bold text-mg-accent" id="stamina-display"><?php echo $my_stamina['current']; ?> / <?php echo $my_stamina['max']; ?></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-mg-text-secondary"><?php echo mg_icon('map', 'w-6 h-6'); ?></span>
                <div>
                    <div class="text-xs text-mg-text-muted">파견 슬롯</div>
                    <div class="font-bold text-mg-text-primary" id="slot-display">- / -</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 진행 중인 파견 -->
    <div id="active-section" style="display:none;" class="mb-6">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">진행 중인 파견</h2>
        <div id="active-list" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
    </div>

    <!-- 파견 보내기 -->
    <div class="card mb-6" id="dispatch-section">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-4">파견 보내기</h2>

        <!-- STEP 1: 캐릭터 선택 -->
        <div id="step-character" class="mb-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-mg-accent text-mg-bg-primary text-xs font-bold">1</span>
                <span class="text-sm font-medium text-mg-text-primary">캐릭터 선택</span>
            </div>
            <div id="character-list" class="flex flex-wrap gap-2">
                <div class="text-sm text-mg-text-muted p-4">불러오는 중...</div>
            </div>
        </div>

        <!-- STEP 2: 파트너 선택 -->
        <div id="step-partner" class="mb-4" style="display:none;">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-mg-accent text-mg-bg-primary text-xs font-bold">2</span>
                <span class="text-sm font-medium text-mg-text-primary">파트너 선택 <span class="text-mg-text-muted font-normal">(선택사항, +20% 보너스)</span></span>
            </div>
            <div id="partner-list" class="flex flex-wrap gap-2"></div>
        </div>

        <!-- STEP 3: 파견지 선택 -->
        <div id="step-area" style="display:none;">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-mg-accent text-mg-bg-primary text-xs font-bold">3</span>
                <span class="text-sm font-medium text-mg-text-primary">파견지 선택</span>
            </div>
            <div id="area-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3"></div>
        </div>
    </div>

    <!-- 파견 이력 -->
    <div class="card">
        <h2 class="text-lg font-semibold text-mg-text-primary mb-3">최근 파견 이력</h2>
        <div id="history-list">
            <div class="text-sm text-mg-text-muted text-center py-4">불러오는 중...</div>
        </div>
    </div>
</div>

<!-- 보상 수령 모달 -->
<div id="reward-modal" class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" style="display:none;">
    <div class="bg-mg-bg-secondary rounded-xl max-w-sm w-full p-6">
        <div class="text-center mb-4">
            <div class="text-2xl mb-2">🎉</div>
            <h3 class="text-lg font-bold text-mg-text-primary">파견 완료!</h3>
        </div>
        <div id="reward-items" class="space-y-2 mb-4"></div>
        <button onclick="closeRewardModal()" class="w-full px-4 py-3 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors">확인</button>
    </div>
</div>

<script>
(function() {
    var API = '<?php echo $expedition_api; ?>';
    var selected = { ch_id: 0, partner_ch_id: 0, ea_id: 0 };
    var timerIntervals = [];

    // === 초기 로드 ===
    loadStatus();
    loadCharacters();
    loadHistory();

    // === API 호출 ===
    function api(action, params, method) {
        method = method || 'GET';
        var url = API + '?action=' + action;
        var opts = { method: method, credentials: 'same-origin' };

        if (method === 'POST') {
            var fd = new FormData();
            fd.append('action', action);
            if (params) Object.keys(params).forEach(function(k) { fd.append(k, params[k]); });
            opts.body = fd;
        } else {
            if (params) Object.keys(params).forEach(function(k) { url += '&' + k + '=' + encodeURIComponent(params[k]); });
        }

        return fetch(url, opts).then(function(r) { return r.json(); });
    }

    // === 상태 로드 ===
    function loadStatus() {
        api('status').then(function(data) {
            if (!data.success) return;
            document.getElementById('stamina-display').textContent = data.stamina.current + ' / ' + data.stamina.max;
            document.getElementById('slot-display').textContent = data.used_slots + ' / ' + data.max_slots;

            renderActive(data.active);

            // 슬롯 꽉 차면 파견 섹션 안내
            if (data.used_slots >= data.max_slots) {
                document.getElementById('dispatch-section').querySelector('h2').insertAdjacentHTML('afterend',
                    '<p class="text-sm text-mg-text-muted mb-4">파견 슬롯이 모두 사용 중입니다.</p>');
            }
        });
    }

    // === 진행 중 파견 렌더 ===
    function renderActive(list) {
        var section = document.getElementById('active-section');
        var container = document.getElementById('active-list');

        timerIntervals.forEach(clearInterval);
        timerIntervals = [];

        if (!list || list.length === 0) {
            section.style.display = 'none';
            return;
        }
        section.style.display = 'block';
        container.innerHTML = '';

        list.forEach(function(exp) {
            var card = document.createElement('div');
            card.className = 'card border-2 ' + (exp.is_complete ? 'border-mg-accent' : 'border-mg-bg-tertiary');

            var partnerHtml = '';
            if (exp.partner_ch_name) {
                partnerHtml = '<div class="text-xs text-mg-text-muted mt-1">파트너: ' + escHtml(exp.partner_ch_name) +
                    ' (' + escHtml(exp.partner_nick || '') + ')</div>';
            }

            var actionHtml = '';
            if (exp.is_complete) {
                actionHtml = '<button class="w-full px-4 py-2 bg-mg-accent text-mg-bg-primary font-medium rounded-lg hover:bg-mg-accent-hover transition-colors" onclick="claimExpedition(' + exp.el_id + ')">보상 수령</button>';
            } else {
                actionHtml = '<div class="mb-2"><div class="flex justify-between text-xs text-mg-text-muted mb-1"><span>진행 중</span><span id="timer-' + exp.el_id + '">' + formatTime(exp.remaining_seconds) + '</span></div>' +
                    '<div class="h-2 bg-mg-bg-primary rounded-full overflow-hidden"><div class="h-full bg-mg-accent transition-all" id="bar-' + exp.el_id + '" style="width:' + exp.progress + '%"></div></div></div>' +
                    '<button class="w-full px-3 py-1.5 text-sm border border-mg-bg-tertiary text-mg-text-secondary rounded-lg hover:bg-mg-bg-tertiary transition-colors" onclick="cancelExpedition(' + exp.el_id + ')">취소</button>';

                // 타이머
                (function(id, remaining, total) {
                    var iv = setInterval(function() {
                        remaining--;
                        if (remaining <= 0) {
                            clearInterval(iv);
                            loadStatus();
                            return;
                        }
                        var tEl = document.getElementById('timer-' + id);
                        var bEl = document.getElementById('bar-' + id);
                        if (tEl) tEl.textContent = formatTime(remaining);
                        if (bEl) bEl.style.width = Math.min(100, ((total - remaining) / total) * 100) + '%';
                    }, 1000);
                    timerIntervals.push(iv);
                })(exp.el_id, exp.remaining_seconds, exp.total_seconds);
            }

            card.innerHTML =
                '<div class="flex items-center gap-3 mb-3">' +
                    '<div class="text-2xl">' + (exp.ea_icon ? '' : '🗺️') + '</div>' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="font-semibold text-mg-text-primary truncate">' + escHtml(exp.ea_name || '파견지') + '</div>' +
                        '<div class="text-xs text-mg-text-muted">' + escHtml(exp.ch_name || '') + '</div>' +
                        partnerHtml +
                    '</div>' +
                '</div>' + actionHtml;

            container.appendChild(card);
        });
    }

    // === 캐릭터 목록 ===
    function loadCharacters() {
        api('my_characters').then(function(data) {
            var container = document.getElementById('character-list');
            if (!data.success || !data.characters || data.characters.length === 0) {
                container.innerHTML = '<div class="text-sm text-mg-text-muted p-4">사용 가능한 캐릭터가 없습니다.</div>';
                return;
            }

            container.innerHTML = '';
            data.characters.forEach(function(ch) {
                var btn = document.createElement('button');
                btn.className = 'flex items-center gap-2 px-3 py-2 bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg hover:border-mg-accent transition-colors text-left';
                btn.setAttribute('data-ch-id', ch.ch_id);
                btn.innerHTML =
                    (ch.ch_thumb ? '<img src="' + escHtml(ch.ch_thumb) + '" class="w-8 h-8 rounded-full object-cover">' : '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>') +
                    '<span class="text-sm text-mg-text-primary">' + escHtml(ch.ch_name) + '</span>';
                btn.onclick = function() { selectCharacter(ch.ch_id, this); };
                container.appendChild(btn);
            });
        });
    }

    function selectCharacter(ch_id, el) {
        selected.ch_id = ch_id;
        selected.partner_ch_id = 0;

        // UI 선택 표시
        document.querySelectorAll('#character-list button').forEach(function(b) {
            b.classList.remove('border-mg-accent', 'ring-1', 'ring-mg-accent');
            b.classList.add('border-mg-bg-tertiary');
        });
        el.classList.remove('border-mg-bg-tertiary');
        el.classList.add('border-mg-accent', 'ring-1', 'ring-mg-accent');

        // 파트너 로드
        loadPartners(ch_id);

        // 파견지 로드
        loadAreas();
    }

    // === 파트너 목록 ===
    function loadPartners(ch_id) {
        var section = document.getElementById('step-partner');
        var container = document.getElementById('partner-list');

        api('partner_candidates', { ch_id: ch_id }).then(function(data) {
            if (!data.success || !data.candidates || data.candidates.length === 0) {
                section.style.display = 'block';
                container.innerHTML = '<div class="text-sm text-mg-text-muted p-2">관계가 맺어진 캐릭터가 없습니다. <a href="' + '<?php echo G5_BBS_URL; ?>/relation.php' + '" class="text-mg-accent hover:underline">관계 맺기</a></div>';
                return;
            }

            section.style.display = 'block';
            container.innerHTML = '';

            // 선택 안 함 버튼
            var skipBtn = document.createElement('button');
            skipBtn.className = 'flex items-center gap-2 px-3 py-2 bg-mg-bg-primary border border-mg-accent ring-1 ring-mg-accent rounded-lg text-left';
            skipBtn.setAttribute('data-ch-id', '0');
            skipBtn.innerHTML = '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">-</div><span class="text-sm text-mg-text-primary">혼자 보내기</span>';
            skipBtn.onclick = function() { selectPartner(0, this); };
            container.appendChild(skipBtn);

            data.candidates.forEach(function(p) {
                var btn = document.createElement('button');
                btn.className = 'flex items-center gap-2 px-3 py-2 bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg hover:border-mg-accent transition-colors text-left';
                btn.setAttribute('data-ch-id', p.ch_id);
                btn.innerHTML =
                    (p.ch_thumb ? '<img src="' + escHtml(p.ch_thumb) + '" class="w-8 h-8 rounded-full object-cover">' : '<div class="w-8 h-8 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm">?</div>') +
                    '<div><div class="text-sm text-mg-text-primary">' + escHtml(p.ch_name) + '</div>' +
                    '<div class="text-xs text-mg-text-muted">' + escHtml(p.relation_label || '') + '</div></div>';
                btn.onclick = function() { selectPartner(p.ch_id, this); };
                container.appendChild(btn);
            });
        });
    }

    function selectPartner(ch_id, el) {
        selected.partner_ch_id = ch_id;

        document.querySelectorAll('#partner-list button').forEach(function(b) {
            b.classList.remove('border-mg-accent', 'ring-1', 'ring-mg-accent');
            b.classList.add('border-mg-bg-tertiary');
        });
        el.classList.remove('border-mg-bg-tertiary');
        el.classList.add('border-mg-accent', 'ring-1', 'ring-mg-accent');
    }

    // === 파견지 목록 ===
    function loadAreas() {
        var section = document.getElementById('step-area');

        api('areas').then(function(data) {
            if (!data.success) return;
            section.style.display = 'block';

            var container = document.getElementById('area-list');
            container.innerHTML = '';

            if (!data.areas || data.areas.length === 0) {
                container.innerHTML = '<div class="col-span-full text-sm text-mg-text-muted text-center py-4">등록된 파견지가 없습니다.</div>';
                return;
            }

            data.areas.forEach(function(area) {
                var locked = !area.is_unlocked;
                var card = document.createElement('div');
                card.className = 'card border border-mg-bg-tertiary ' + (locked ? 'opacity-50' : 'cursor-pointer hover:border-mg-accent transition-colors');

                var durH = Math.floor(area.ea_duration / 60);
                var durM = area.ea_duration % 60;
                var durText = (durH > 0 ? durH + '시간 ' : '') + (durM > 0 ? durM + '분' : '');

                var dropsHtml = '';
                if (area.drops && area.drops.length > 0) {
                    area.drops.forEach(function(d) {
                        var cls = d.ed_is_rare == 1 ? 'text-purple-400 font-semibold' : 'text-mg-text-secondary';
                        dropsHtml += '<span class="inline-flex items-center gap-1 text-xs ' + cls + '" title="' + escHtml(d.mt_name) + ' ' + d.ed_min + '~' + d.ed_max + '개 (' + d.ed_chance + '%)">' +
                            escHtml(d.mt_name) + ' ' + d.ed_chance + '%' + (d.ed_is_rare == 1 ? ' ★' : '') + '</span> ';
                    });
                }

                card.innerHTML =
                    '<div class="flex items-start gap-3 mb-3">' +
                        '<span class="text-2xl flex-shrink-0">' + (locked ? '🔒' : '🗺️') + '</span>' +
                        '<div class="flex-1 min-w-0">' +
                            '<div class="font-semibold text-mg-text-primary">' + escHtml(area.ea_name) + '</div>' +
                            (area.ea_desc ? '<div class="text-xs text-mg-text-muted mt-0.5 line-clamp-2">' + escHtml(area.ea_desc) + '</div>' : '') +
                        '</div>' +
                    '</div>' +
                    '<div class="flex flex-wrap gap-3 text-xs text-mg-text-secondary mb-3">' +
                        '<span class="inline-flex items-center gap-1"><span class="text-mg-accent">⚡</span> ' + area.ea_stamina_cost + '</span>' +
                        '<span class="inline-flex items-center gap-1">⏱ ' + durText.trim() + '</span>' +
                        '<span class="inline-flex items-center gap-1">👥 +' + area.ea_partner_point + 'P</span>' +
                    '</div>' +
                    '<div class="flex flex-wrap gap-2">' + dropsHtml + '</div>' +
                    (locked ? '<div class="text-xs text-mg-text-muted mt-2">🔒 ' + escHtml(area.unlock_facility_name || '시설') + ' 건설 필요</div>' : '');

                if (!locked) {
                    card.onclick = function() { startExpedition(area.ea_id, area.ea_name, area.ea_stamina_cost); };
                }

                container.appendChild(card);
            });
        });
    }

    // === 파견 시작 ===
    window.startExpedition = function(ea_id, ea_name, cost) {
        if (!selected.ch_id) {
            alert('캐릭터를 먼저 선택해주세요.');
            return;
        }
        if (!confirm(ea_name + ' 파견을 보내시겠습니까?\n(노동력 ' + cost + ' 소모)')) return;

        api('start', {
            ch_id: selected.ch_id,
            ea_id: ea_id,
            partner_ch_id: selected.partner_ch_id || ''
        }, 'POST').then(function(data) {
            if (data.success) {
                selected.ch_id = 0;
                selected.partner_ch_id = 0;
                loadStatus();
                loadCharacters();
                loadHistory();
                document.getElementById('step-partner').style.display = 'none';
                document.getElementById('step-area').style.display = 'none';
            }
            alert(data.message);
        });
    };

    // === 보상 수령 ===
    window.claimExpedition = function(el_id) {
        api('claim', { el_id: el_id }, 'POST').then(function(data) {
            if (data.success) {
                showRewardModal(data.rewards);
                loadStatus();
                loadHistory();
            } else {
                alert(data.message);
            }
        });
    };

    // === 파견 취소 ===
    window.cancelExpedition = function(el_id) {
        if (!confirm('파견을 취소하시겠습니까?\n노동력은 반환되지 않습니다.')) return;

        api('cancel', { el_id: el_id }, 'POST').then(function(data) {
            alert(data.message);
            if (data.success) {
                loadStatus();
                loadHistory();
            }
        });
    };

    // === 보상 모달 ===
    function showRewardModal(rewards) {
        var container = document.getElementById('reward-items');
        container.innerHTML = '';

        if (rewards && rewards.items && rewards.items.length > 0) {
            rewards.items.forEach(function(item) {
                var cls = item.is_rare ? 'border-purple-500 bg-purple-500/10' : 'border-mg-bg-tertiary bg-mg-bg-primary';
                var nameClass = item.is_rare ? 'text-purple-400 font-semibold' : 'text-mg-text-primary';
                container.innerHTML +=
                    '<div class="flex items-center justify-between p-3 rounded-lg border ' + cls + '">' +
                        '<span class="' + nameClass + '">' + escHtml(item.mt_name) + (item.is_rare ? ' ★' : '') + '</span>' +
                        '<span class="font-bold text-mg-text-primary">x' + item.amount + '</span>' +
                    '</div>';
            });
        } else {
            container.innerHTML = '<div class="text-center text-mg-text-muted py-2">획득한 재료가 없습니다.</div>';
        }

        document.getElementById('reward-modal').style.display = 'flex';
    }

    window.closeRewardModal = function() {
        document.getElementById('reward-modal').style.display = 'none';
    };

    document.getElementById('reward-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRewardModal();
    });

    // === 이력 ===
    function loadHistory() {
        api('history', { limit: 10 }).then(function(data) {
            var container = document.getElementById('history-list');
            if (!data.success || !data.history || data.history.length === 0) {
                container.innerHTML = '<div class="text-sm text-mg-text-muted text-center py-4">파견 이력이 없습니다.</div>';
                return;
            }

            var html = '<div class="space-y-2">';
            data.history.forEach(function(h) {
                var statusBadge = '';
                if (h.el_status === 'claimed') {
                    statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-success/20 text-mg-success">수령완료</span>';
                } else if (h.el_status === 'cancelled') {
                    statusBadge = '<span class="px-2 py-0.5 text-xs rounded bg-mg-bg-tertiary text-mg-text-muted">취소</span>';
                }

                var rewardsText = '';
                if (h.el_rewards_parsed && h.el_rewards_parsed.items && h.el_rewards_parsed.items.length > 0) {
                    var parts = [];
                    h.el_rewards_parsed.items.forEach(function(item) {
                        parts.push(item.mt_name + ' x' + item.amount + (item.is_rare ? '★' : ''));
                    });
                    rewardsText = parts.join(', ');
                } else if (h.el_status === 'claimed') {
                    rewardsText = '(드롭 없음)';
                }

                var dateText = (h.el_start || '').substring(5, 16);

                html += '<div class="flex items-center gap-3 p-2 bg-mg-bg-primary rounded-lg text-sm">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><span class="text-mg-text-primary font-medium">' + escHtml(h.ea_name || '') + '</span>' + statusBadge + '</div>' +
                        '<div class="text-xs text-mg-text-muted mt-0.5">' + escHtml(h.ch_name || '') +
                        (h.partner_ch_name ? ' + ' + escHtml(h.partner_ch_name) : '') +
                        ' · ' + dateText + '</div>' +
                        (rewardsText ? '<div class="text-xs text-mg-text-secondary mt-0.5">' + escHtml(rewardsText) + '</div>' : '') +
                    '</div></div>';
            });
            html += '</div>';
            container.innerHTML = html;
        });
    }

    // === 유틸 ===
    function formatTime(seconds) {
        if (seconds <= 0) return '완료';
        var h = Math.floor(seconds / 3600);
        var m = Math.floor((seconds % 3600) / 60);
        var s = seconds % 60;
        if (h > 0) return h + '시간 ' + (m < 10 ? '0' : '') + m + '분';
        return m + '분 ' + (s < 10 ? '0' : '') + s + '초';
    }

    function escHtml(str) {
        if (!str) return '';
        var d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }
})();
</script>
