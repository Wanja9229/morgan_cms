<?php
/**
 * Morgan Edition - 캐릭터 프로필 보기
 */

include_once('./_common.php');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$ch_id = isset($_GET['ch_id']) ? (int)$_GET['ch_id'] : 0;

if (!$ch_id) {
    alert('잘못된 접근입니다.');
}

// 캐릭터 정보 조회
$sql = "SELECT c.*, s.side_name, s.side_desc, cl.class_name, cl.class_desc, m.mb_nick
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        LEFT JOIN {$g5['member_table']} m ON c.mb_id = m.mb_id
        WHERE c.ch_id = {$ch_id}";
$char = sql_fetch($sql);

if (!$char['ch_id']) {
    alert('존재하지 않는 캐릭터입니다.');
}

// 비공개 캐릭터 체크 (editing 상태는 본인만)
if ($char['ch_state'] == 'editing' || $char['ch_state'] == 'deleted') {
    if (!$is_member || $member['mb_id'] != $char['mb_id']) {
        alert('비공개 캐릭터입니다.');
    }
}

// 본인 캐릭터인지
$is_owner = $is_member && $member['mb_id'] == $char['mb_id'];

// 프로필 값 조회
$sql = "SELECT pf.*, pv.pv_value
        FROM {$g5['mg_profile_field_table']} pf
        LEFT JOIN {$g5['mg_profile_value_table']} pv ON pf.pf_id = pv.pf_id AND pv.ch_id = {$ch_id}
        WHERE pf.pf_use = 1
        ORDER BY pf.pf_order, pf.pf_id";
$result = sql_query($sql);

$profile_fields = array();
while ($row = sql_fetch_array($result)) {
    if (!empty($row['pv_value'])) {
        $profile_fields[] = $row;
    }
}

// 카테고리별 그룹핑
$grouped_fields = array();
foreach ($profile_fields as $field) {
    $category = $field['pf_category'] ?: '기본정보';
    $grouped_fields[$category][] = $field;
}

// 업적 쇼케이스 데이터
$achievement_showcase = array();
if (function_exists('mg_get_achievement_display')) {
    $achievement_showcase = mg_get_achievement_display($char['mb_id']);
}

// 관계 데이터
$char_relations = mg_get_relations($ch_id, 'active');

// 관계 신청 가능 여부: 로그인 + 타인 캐릭터 + 승인된 캐릭터
$can_request_relation = false;
$my_approved_characters = array();
if ($is_member && !$is_owner && $char['ch_state'] == 'approved') {
    $sql = "SELECT ch_id, ch_name, ch_thumb FROM {$g5['mg_character_table']}
            WHERE mb_id = '{$member['mb_id']}' AND ch_state = 'approved'
            ORDER BY ch_main DESC, ch_name";
    $result = sql_query($sql);
    while ($row = sql_fetch_array($result)) {
        $my_approved_characters[] = $row;
    }
    if (!empty($my_approved_characters)) {
        $can_request_relation = true;
    }
}

// (관계 아이콘 프리셋 제거됨 — 유저가 직접 설정)

$g5['title'] = $char['ch_name'].' - 캐릭터 프로필';

// 프로필 스킨 확인
$profile_skin_id = mg_get_profile_skin_id($char['mb_id']);
$skin_template = G5_THEME_PATH.'/skin/profile/default.php';

if ($profile_skin_id) {
    $valid_skins = mg_get_profile_skin_list();
    if (isset($valid_skins[$profile_skin_id])) {
        $candidate = G5_THEME_PATH.'/skin/profile/'.$profile_skin_id.'.php';
        if (file_exists($candidate)) {
            $skin_template = $candidate;
        }
    }
}

include_once(G5_THEME_PATH.'/head.php');

// 프로필 템플릿 렌더링
include($skin_template);
?>

<?php if ($can_request_relation) { ?>
<!-- 관계 신청 모달 -->
<div id="rel-request-modal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.6)">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="px-5 py-4 border-b border-mg-bg-tertiary flex justify-between items-center">
                <h3 class="font-bold text-mg-text-primary">관계 신청</h3>
                <button type="button" onclick="closeRelRequestModal()" class="text-mg-text-muted hover:text-mg-text-primary text-xl leading-none">&times;</button>
            </div>
            <div class="p-5 space-y-4">
                <!-- 대상 캐릭터 (자동 지정) -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">대상 캐릭터</label>
                    <div class="flex items-center gap-2 bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2">
                        <?php if ($char['ch_thumb']) { ?>
                        <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" class="w-6 h-6 rounded-full object-cover" alt="">
                        <?php } ?>
                        <span class="text-sm text-mg-text-primary"><?php echo htmlspecialchars($char['ch_name']); ?></span>
                    </div>
                </div>

                <!-- 내 캐릭터 선택 -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">내 캐릭터</label>
                    <select id="rr-from-ch" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary">
                        <?php foreach ($my_approved_characters as $mc) { ?>
                        <option value="<?php echo $mc['ch_id']; ?>"><?php echo htmlspecialchars($mc['ch_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <!-- 관계명 -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">관계명</label>
                    <input type="text" id="rr-label" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" placeholder="예: 첫사랑, 라이벌, 동료..." maxlength="50">
                </div>

                <!-- 색상 -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">관계선 색상</label>
                    <div class="flex items-center gap-3">
                        <input type="color" id="rr-color" value="#95a5a6" class="w-10 h-10 rounded border border-mg-bg-tertiary cursor-pointer" style="padding:2px;">
                        <span id="rr-color-hex" class="text-xs text-mg-text-muted">#95a5a6</span>
                    </div>
                </div>

                <!-- 메모 -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">한줄 메모 <span class="text-mg-text-muted">(선택)</span></label>
                    <input type="text" id="rr-memo" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" placeholder="메모..." maxlength="200">
                </div>
            </div>
            <div class="px-5 py-4 border-t border-mg-bg-tertiary flex justify-end gap-2">
                <button type="button" onclick="closeRelRequestModal()" class="px-4 py-2 text-sm text-mg-text-secondary hover:text-mg-text-primary rounded-lg hover:bg-mg-bg-tertiary transition-colors">취소</button>
                <button type="button" onclick="submitRelRequest()" class="px-4 py-2 text-sm bg-mg-accent hover:bg-mg-accent-hover text-white rounded-lg transition-colors">신청</button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var REL_API = '<?php echo G5_BBS_URL; ?>/relation_api.php';
    var TARGET_CH_ID = <?php echo $ch_id; ?>;

    // 모달
    window.openRelRequestModal = function() {
        document.getElementById('rel-request-modal').classList.remove('hidden');
    };
    window.closeRelRequestModal = function() {
        document.getElementById('rel-request-modal').classList.add('hidden');
    };

    // 색상 hex 표시
    var rrColor = document.getElementById('rr-color');
    if (rrColor) {
        rrColor.addEventListener('input', function() {
            document.getElementById('rr-color-hex').textContent = this.value;
        });
    }

    // 신청 제출
    window.submitRelRequest = function() {
        var fromCh = document.getElementById('rr-from-ch').value;
        var label = document.getElementById('rr-label').value.trim();
        var color = document.getElementById('rr-color').value;
        var memo = document.getElementById('rr-memo').value.trim();

        if (!label) { alert('관계명을 입력해주세요.'); return; }

        var data = new FormData();
        data.append('action', 'request');
        data.append('from_ch_id', fromCh);
        data.append('to_ch_id', TARGET_CH_ID);
        data.append('label', label);
        data.append('color', color);
        data.append('memo', memo);

        fetch(REL_API, { method: 'POST', body: data })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                alert(res.message);
                if (res.success) location.reload();
            });
    };

    // 모달 외부 클릭으로 닫기
    document.getElementById('rel-request-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRelRequestModal();
    });
})();
</script>
<?php } ?>

<?php if (!empty($char_relations)) { ?>
<!-- 인라인 관계도 JS -->
<script>
(function() {
    var graphToggle = document.getElementById('rel-graph-toggle');
    var graphWrap = document.getElementById('rel-graph-wrap');
    var graphLoaded = false;

    if (!graphToggle || !graphWrap) return;

    graphToggle.addEventListener('click', function() {
        var isHidden = graphWrap.classList.contains('hidden');
        if (isHidden) {
            graphWrap.classList.remove('hidden');
            graphToggle.textContent = '관계도 닫기';
            if (!graphLoaded) {
                graphLoaded = true;
                loadVisGraph();
            }
        } else {
            graphWrap.classList.add('hidden');
            graphToggle.textContent = '관계도 보기';
        }
    });

    var isOwner = <?php echo $is_owner ? 'true' : 'false'; ?>;
    var saveBtn = document.getElementById('rel-graph-save');
    var _network = null;

    function loadVisGraph() {
        var container = document.getElementById('rel-graph-container');
        var _cs = getComputedStyle(document.documentElement);
        var _clr = {
            accent: _cs.getPropertyValue('--mg-accent').trim() || '#f59e0b',
            accentHover: _cs.getPropertyValue('--mg-accent-hover').trim() || '#d97706',
            bgSec: _cs.getPropertyValue('--mg-bg-secondary').trim() || '#2b2d31',
            textPri: _cs.getPropertyValue('--mg-text-primary').trim() || '#f2f3f5',
            textSec: _cs.getPropertyValue('--mg-text-secondary').trim() || '#b5bac1',
            textMuted: _cs.getPropertyValue('--mg-text-muted').trim() || '#949ba4'
        };
        container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--mg-text-muted,#949ba4);">관계도 로딩중...</div>';

        // vis.js CDN 로드
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/vis-network@9.1.6/dist/dist/vis-network.min.css';
        document.head.appendChild(link);

        var script = document.createElement('script');
        script.src = 'https://unpkg.com/vis-network@9.1.6/dist/vis-network.min.js';
        script.onload = function() {
            fetch('<?php echo G5_BBS_URL; ?>/relation_graph_api.php?ch_id=<?php echo $ch_id; ?>&depth=2')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.nodes || data.nodes.length === 0) {
                        container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--mg-text-muted,#949ba4);">표시할 관계가 없습니다.</div>';
                        return;
                    }
                    container.innerHTML = '';
                    var savedLayout = data.layout || null;
                    var hasLayout = savedLayout && Object.keys(savedLayout).length > 0;

                    var nodes = new vis.DataSet(data.nodes.map(function(n) {
                        var isCurrent = n.ch_id === <?php echo $ch_id; ?>;
                        var nodeOpt = {
                            id: n.ch_id,
                            label: n.ch_name,
                            color: {
                                background: _clr.bgSec,
                                border: isCurrent ? _clr.accent : '#444',
                                hover: { background: _clr.bgSec, border: isCurrent ? _clr.accent : '#444' },
                                highlight: { background: _clr.bgSec, border: isCurrent ? _clr.accent : '#444' }
                            },
                            font: { color: _clr.textPri, size: 12 },
                            borderWidth: isCurrent ? 3 : 1,
                        };
                        // 저장된 좌표가 있으면 적용
                        if (hasLayout && savedLayout[String(n.ch_id)]) {
                            nodeOpt.x = savedLayout[String(n.ch_id)].x;
                            nodeOpt.y = savedLayout[String(n.ch_id)].y;
                            nodeOpt.fixed = !isOwner;
                        }
                        if (n.ch_thumb) {
                            nodeOpt.shape = 'circularImage';
                            nodeOpt.image = n.ch_thumb;
                        } else {
                            nodeOpt.shape = 'circle';
                            nodeOpt.size = 25;
                        }
                        return nodeOpt;
                    }));
                    var edges = new vis.DataSet(data.edges.map(function(e) {
                        var ec = e.edge_color || '#666';
                        return {
                            from: e.ch_id_a, to: e.ch_id_b,
                            label: e.label_display || '',
                            color: { color: ec, hover: ec, highlight: ec },
                            width: e.edge_width || 2,
                            font: { color: _clr.textSec, size: 10, strokeWidth: 3, strokeColor: '#1a1a1a' },
                            smooth: { type: 'continuous' }
                        };
                    }));

                    var options = {
                        physics: hasLayout
                            ? { enabled: false }
                            : { stabilization: { iterations: 200 }, barnesHut: { gravitationalConstant: -3000, springLength: 150 } },
                        interaction: {
                            dragNodes: isOwner,
                            dragView: false,
                            zoomView: false,
                            selectable: false,
                            hover: true
                        },
                        layout: {
                            improvedLayout: true,
                            randomSeed: <?php echo $ch_id; ?>
                        }
                    };

                    _network = new vis.Network(container, { nodes: nodes, edges: edges }, options);

                    // 저장된 좌표가 없으면 시뮬레이션 후 정지
                    if (!hasLayout) {
                        _network.once('stabilizationIterationsDone', function() {
                            _network.setOptions({ physics: false });
                        });
                    }

                    // 주인: 저장 버튼 표시
                    if (isOwner && saveBtn) {
                        saveBtn.classList.remove('hidden');
                    }

                    // 비주인: 클릭으로 캐릭터 이동
                    if (!isOwner) {
                        _network.on('click', function(params) {
                            if (params.nodes.length > 0) {
                                var nodeId = params.nodes[0];
                                if (nodeId !== <?php echo $ch_id; ?>) {
                                    window.location.href = '<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=' + nodeId;
                                }
                            }
                        });
                    }
                    // 주인: 더블클릭으로 이동 (싱글클릭은 드래그와 충돌)
                    if (isOwner) {
                        _network.on('doubleClick', function(params) {
                            if (params.nodes.length > 0) {
                                var nodeId = params.nodes[0];
                                if (nodeId !== <?php echo $ch_id; ?>) {
                                    window.location.href = '<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=' + nodeId;
                                }
                            }
                        });
                    }
                });
        };
        document.head.appendChild(script);
    }

    // 배치 저장
    if (isOwner && saveBtn) {
        saveBtn.addEventListener('click', function() {
            if (!_network) return;
            var positions = _network.getPositions();
            var layout = {};
            for (var id in positions) {
                layout[id] = { x: Math.round(positions[id].x), y: Math.round(positions[id].y) };
            }
            var fd = new FormData();
            fd.append('action', 'save_layout');
            fd.append('ch_id', <?php echo $ch_id; ?>);
            fd.append('layout', JSON.stringify(layout));
            fetch('<?php echo G5_BBS_URL; ?>/relation_api.php', { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        saveBtn.textContent = '저장됨';
                        setTimeout(function() { saveBtn.textContent = '배치 저장'; }, 1500);
                    } else {
                        alert(res.message);
                    }
                });
        });
    }
})();
</script>
<?php } ?>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
