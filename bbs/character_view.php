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

// 관계 아이콘 (신청 모달용)
$relation_icons = array();
if ($can_request_relation) {
    $relation_icons = mg_get_relation_icons(true);
}

$g5['title'] = $char['ch_name'].' - 캐릭터 프로필';

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 뒤로가기 -->
    <a href="javascript:history.back();" class="inline-flex items-center gap-1 text-sm text-mg-text-muted hover:text-mg-accent transition-colors mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        <span>뒤로</span>
    </a>

    <!-- 프로필 헤더 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="md:flex">
            <!-- 이미지 -->
            <div class="md:w-64 lg:w-80 flex-shrink-0">
                <div class="aspect-square bg-mg-bg-tertiary">
                    <?php if ($char['ch_thumb']) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="<?php echo $char['ch_name']; ?>" class="w-full h-full object-cover">
                    <?php } else { ?>
                    <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                        <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <?php } ?>
                </div>
            </div>

            <!-- 기본 정보 -->
            <div class="flex-1 p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <!-- 배지 -->
                        <div class="flex items-center gap-2 mb-2">
                            <?php if ($char['ch_main']) { ?>
                            <span class="bg-mg-accent text-white text-xs px-2 py-0.5 rounded-full">대표</span>
                            <?php } ?>
                            <?php
                            $state_labels = array(
                                'editing' => array('수정중', 'bg-gray-500'),
                                'pending' => array('승인대기', 'bg-yellow-500'),
                                'approved' => array('승인됨', 'bg-green-500'),
                            );
                            $state = $state_labels[$char['ch_state']] ?? array('', '');
                            if ($state[0]) {
                            ?>
                            <span class="<?php echo $state[1]; ?> text-white text-xs px-2 py-0.5 rounded-full"><?php echo $state[0]; ?></span>
                            <?php } ?>
                        </div>

                        <!-- 이름 -->
                        <h1 class="text-3xl font-bold text-mg-text-primary"><?php echo $char['ch_name']; ?></h1>

                        <!-- 세력/종족 -->
                        <div class="flex items-center gap-3 mt-2 text-mg-text-secondary">
                            <?php if ($char['side_name']) { ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                                </svg>
                                <?php echo $char['side_name']; ?>
                            </span>
                            <?php } ?>
                            <?php if ($char['class_name']) { ?>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4 text-mg-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                <?php echo $char['class_name']; ?>
                            </span>
                            <?php } ?>
                        </div>

                        <!-- 오너 정보 -->
                        <div class="mt-4 text-sm text-mg-text-muted">
                            <span class="text-mg-text-secondary">@<?php echo $char['mb_nick']; ?></span>
                            <span class="mx-2">·</span>
                            <span><?php echo date('Y.m.d', strtotime($char['ch_datetime'])); ?> 등록</span>
                        </div>
                    </div>

                    <!-- 액션 버튼 -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <?php if ($can_request_relation) { ?>
                        <button type="button" onclick="openRelRequestModal()" class="inline-flex items-center gap-1 text-sm bg-mg-accent hover:bg-mg-accent-hover text-white px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span>관계 신청</span>
                        </button>
                        <?php } ?>
                        <?php if ($is_owner) { ?>
                        <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="inline-flex items-center gap-1 text-sm bg-mg-bg-tertiary hover:bg-mg-bg-primary text-mg-text-secondary px-3 py-1.5 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>수정</span>
                        </a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 업적 쇼케이스 -->
    <?php if (!empty($achievement_showcase)) {
        $ach_rarity_colors = array(
            'common' => '#949ba4', 'uncommon' => '#22c55e', 'rare' => '#3b82f6',
            'epic' => '#a855f7', 'legendary' => '#f59e0b',
        );
        $ach_rarity_labels = array(
            'common' => 'Common', 'uncommon' => 'Uncommon', 'rare' => 'Rare',
            'epic' => 'Epic', 'legendary' => 'Legendary',
        );
    ?>
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden mb-6">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="font-medium text-mg-text-primary">업적 쇼케이스</h2>
            <a href="<?php echo G5_BBS_URL; ?>/achievement.php" class="text-xs text-mg-accent hover:underline">전체보기</a>
        </div>
        <div class="p-4 flex gap-3 flex-wrap justify-center">
            <?php foreach ($achievement_showcase as $acd) {
                $a_icon = $acd['tier_icon'] ?: ($acd['ac_icon'] ?: '');
                $a_name = $acd['tier_name'] ?: $acd['ac_name'];
                $a_rarity = $acd['ac_rarity'] ?: 'common';
                $a_color = $ach_rarity_colors[$a_rarity] ?? '#949ba4';
            ?>
            <div class="flex flex-col items-center p-3 rounded-lg min-w-[80px]" style="border:2px solid <?php echo $a_color; ?>;" title="<?php echo htmlspecialchars($a_name); ?>">
                <?php if ($a_icon) { ?>
                <img src="<?php echo htmlspecialchars($a_icon); ?>" alt="<?php echo htmlspecialchars($a_name); ?>" class="w-10 h-10 object-contain">
                <?php } else { ?>
                <span class="text-2xl">🏆</span>
                <?php } ?>
                <span class="text-xs text-mg-text-secondary mt-1 text-center leading-tight max-w-[70px] truncate"><?php echo htmlspecialchars($a_name); ?></span>
                <span class="text-[10px] mt-0.5" style="color:<?php echo $a_color; ?>;"><?php echo $ach_rarity_labels[$a_rarity] ?? ''; ?></span>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- 프로필 상세 -->
    <?php if (count($grouped_fields) > 0) { ?>
    <div class="space-y-4">
        <?php foreach ($grouped_fields as $category => $fields) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
            <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary">
                <h2 class="font-medium text-mg-text-primary"><?php echo $category; ?></h2>
            </div>
            <div class="p-4">
                <dl class="space-y-4">
                    <?php foreach ($fields as $field) { ?>
                    <div>
                        <dt class="text-sm font-medium text-mg-text-muted mb-1"><?php echo $field['pf_name']; ?></dt>
                        <dd class="text-mg-text-primary">
                            <?php
                            if ($field['pf_type'] == 'url') {
                                echo '<a href="'.htmlspecialchars($field['pv_value']).'" target="_blank" class="text-mg-accent hover:underline">'.htmlspecialchars($field['pv_value']).'</a>';
                            } elseif ($field['pf_type'] == 'textarea') {
                                echo nl2br(htmlspecialchars($field['pv_value']));
                            } else {
                                echo htmlspecialchars($field['pv_value']);
                            }
                            ?>
                        </dd>
                    </div>
                    <?php } ?>
                </dl>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } ?>

    <!-- 캐릭터 관계 -->
    <?php if (!empty($char_relations)) { ?>
    <div class="mt-6 bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden">
        <div class="px-4 py-3 bg-mg-bg-tertiary/50 border-b border-mg-bg-tertiary flex items-center justify-between">
            <h2 class="font-medium text-mg-text-primary">관계</h2>
            <button type="button" id="rel-graph-toggle" class="text-xs text-mg-accent hover:underline">관계도 보기</button>
        </div>
        <div class="divide-y divide-mg-bg-tertiary">
            <?php foreach ($char_relations as $rel) {
                $is_a = ($ch_id == $rel['ch_id_a']);
                $other_name = $is_a ? $rel['name_b'] : $rel['name_a'];
                $other_thumb = $is_a ? $rel['thumb_b'] : $rel['thumb_a'];
                $other_ch_id = $is_a ? $rel['ch_id_b'] : $rel['ch_id_a'];
                $my_label = $is_a ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']);
                $my_icon = $is_a ? ($rel['cr_icon_a'] ?: $rel['ri_icon']) : ($rel['cr_icon_b'] ?: $rel['ri_icon']);
            ?>
            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" class="px-4 py-3 flex items-center gap-3 hover:bg-mg-bg-tertiary/30 transition-colors">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" class="w-9 h-9 rounded-full object-cover flex-shrink-0" alt="">
                <?php } else { ?>
                <div class="w-9 h-9 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted text-sm flex-shrink-0">?</div>
                <?php } ?>
                <span class="text-base"><?php echo $my_icon; ?></span>
                <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($my_label); ?></span>
                <span class="text-sm font-medium text-mg-text-primary ml-auto"><?php echo htmlspecialchars($other_name); ?></span>
            </a>
            <?php } ?>
        </div>

        <!-- 인라인 관계도 (토글) -->
        <div id="rel-graph-wrap" class="hidden border-t border-mg-bg-tertiary">
            <div id="rel-graph-container" style="height:400px; background:#1a1a1a;"></div>
        </div>
    </div>
    <?php } ?>

    <!-- 소유자 인장 -->
    <?php if (function_exists('mg_render_seal')) { echo mg_render_seal($char['mb_id'], 'full'); } ?>
</div>

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

                <!-- 아이콘 팔레트 -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">관계 아이콘</label>
                    <div id="rr-icon-palette" class="flex flex-wrap gap-2">
                        <?php foreach ($relation_icons as $icon) { ?>
                        <button type="button" class="rr-icon-btn w-10 h-10 flex items-center justify-center rounded-lg border border-mg-bg-tertiary hover:border-mg-accent transition-colors text-lg" data-ri-id="<?php echo $icon['ri_id']; ?>" title="<?php echo htmlspecialchars($icon['ri_label']); ?>">
                            <?php echo $icon['ri_icon']; ?>
                        </button>
                        <?php } ?>
                    </div>
                    <input type="hidden" id="rr-ri-id">
                </div>

                <!-- 관계명 -->
                <div>
                    <label class="block text-sm text-mg-text-secondary mb-1">관계명</label>
                    <input type="text" id="rr-label" class="w-full bg-mg-bg-primary border border-mg-bg-tertiary rounded-lg px-3 py-2 text-sm text-mg-text-primary" placeholder="예: 첫사랑, 라이벌, 동료..." maxlength="50">
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

    // 아이콘 팔레트
    document.querySelectorAll('.rr-icon-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.rr-icon-btn').forEach(function(b) {
                b.classList.remove('!border-mg-accent', 'bg-mg-accent/10');
            });
            this.classList.add('!border-mg-accent', 'bg-mg-accent/10');
            document.getElementById('rr-ri-id').value = this.dataset.riId;
        });
    });

    // 신청 제출
    window.submitRelRequest = function() {
        var fromCh = document.getElementById('rr-from-ch').value;
        var riId = document.getElementById('rr-ri-id').value;
        var label = document.getElementById('rr-label').value.trim();
        var memo = document.getElementById('rr-memo').value.trim();

        if (!riId) { alert('아이콘을 선택해주세요.'); return; }
        if (!label) { alert('관계명을 입력해주세요.'); return; }

        var data = new FormData();
        data.append('action', 'request');
        data.append('from_ch_id', fromCh);
        data.append('to_ch_id', TARGET_CH_ID);
        data.append('ri_id', riId);
        data.append('label', label);
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

    function loadVisGraph() {
        var container = document.getElementById('rel-graph-container');
        container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#949ba4;">관계도 로딩중...</div>';

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
                        container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#949ba4;">표시할 관계가 없습니다.</div>';
                        return;
                    }
                    container.innerHTML = '';
                    var nodes = new vis.DataSet(data.nodes.map(function(n) {
                        var nodeOpt = {
                            id: n.ch_id,
                            label: n.ch_name,
                            color: { background: n.ch_id === <?php echo $ch_id; ?> ? '#f59e0b' : '#2b2d31', border: n.ch_id === <?php echo $ch_id; ?> ? '#d97706' : '#444' },
                            font: { color: '#f2f3f5', size: 12 },
                            borderWidth: n.ch_id === <?php echo $ch_id; ?> ? 3 : 1,
                        };
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
                        return {
                            from: e.ch_id_a, to: e.ch_id_b,
                            label: e.label_display || '',
                            color: { color: e.edge_color || '#666', highlight: '#f59e0b' },
                            width: e.edge_width || 2,
                            font: { color: '#b5bac1', size: 10, strokeWidth: 3, strokeColor: '#1a1a1a' },
                            smooth: { type: 'continuous' }
                        };
                    }));
                    var network = new vis.Network(container, { nodes: nodes, edges: edges }, {
                        physics: { stabilization: { iterations: 100 }, barnesHut: { gravitationalConstant: -3000, springLength: 150 } },
                        interaction: { hover: true, zoomView: true, dragView: true },
                        layout: { improvedLayout: true }
                    });
                    // 노드 클릭 시 해당 캐릭터 페이지로 이동
                    network.on('doubleClick', function(params) {
                        if (params.nodes.length > 0) {
                            var nodeId = params.nodes[0];
                            if (nodeId !== <?php echo $ch_id; ?>) {
                                window.location.href = '<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=' + nodeId;
                            }
                        }
                    });
                });
        };
        document.head.appendChild(script);
    }
})();
</script>
<?php } ?>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
