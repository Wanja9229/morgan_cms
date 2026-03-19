<?php
/**
 * Morgan Edition - 내 캐릭터 관리
 */

include_once('./_common.php');

// 로그인 체크
if (!$is_member) {
    alert('로그인이 필요합니다.', G5_BBS_URL.'/login.php');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$g5['title'] = '내 캐릭터';

// 내 캐릭터 목록 조회
$sql = "SELECT c.*, s.side_name, cl.class_name
        FROM {$g5['mg_character_table']} c
        LEFT JOIN {$g5['mg_side_table']} s ON c.side_id = s.side_id
        LEFT JOIN {$g5['mg_class_table']} cl ON c.class_id = cl.class_id
        WHERE c.mb_id = '{$member['mb_id']}'
        AND c.ch_state != 'deleted'
        ORDER BY c.ch_main DESC, c.ch_datetime DESC";
$result = sql_query($sql);

$characters = array();
while ($row = sql_fetch_array($result)) {
    $characters[] = $row;
}

// 반려 이력 조회 (editing 상태인 캐릭터의 최근 로그가 reject인 경우)
$rejected_info = array();
$editing_ids = array();
foreach ($characters as $char) {
    if ($char['ch_state'] == 'editing') {
        $editing_ids[] = (int)$char['ch_id'];
    }
}
if (!empty($editing_ids)) {
    $ids_str = implode(',', $editing_ids);
    $rej_result = sql_query("SELECT cl.ch_id, cl.log_memo
        FROM {$g5['mg_character_log_table']} cl
        INNER JOIN (
            SELECT ch_id, MAX(log_id) as max_id
            FROM {$g5['mg_character_log_table']}
            WHERE ch_id IN ({$ids_str})
            GROUP BY ch_id
        ) latest ON cl.log_id = latest.max_id
        WHERE cl.log_action = 'reject'");
    if ($rej_result !== false) {
        while ($rej = sql_fetch_array($rej_result)) {
            $rejected_info[$rej['ch_id']] = $rej['log_memo'] ?? '';
        }
    }
}

// 최대 캐릭터 수 (기본값 + 슬롯 아이템 보너스)
$max_characters = mg_get_max_characters($member['mb_id']);
$current_count = count($characters);
$can_create = $current_count < $max_characters;

// 탭
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'characters';

// 관계 데이터 (관계 탭용)
$all_received = array();
$all_sent = array();
$all_accepted = array();
$all_active = array();
$relation_pending_count = 0;

if (!empty($characters)) {
    $my_ch_ids = array();
    $my_ch_map = array();
    foreach ($characters as $ch) {
        $my_ch_ids[] = (int)$ch['ch_id'];
        $my_ch_map[(int)$ch['ch_id']] = $ch;
    }
    $ch_ids_str = implode(',', $my_ch_ids);

    // 받은 대기 신청 (대상이 내 캐릭터이고, 신청자가 대상과 다른 것)
    $sql = "SELECT r.*, ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, cb.ch_name AS name_b, cb.ch_thumb AS thumb_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'pending'
            AND ((r.ch_id_a IN ({$ch_ids_str}) AND r.ch_id_from != r.ch_id_a)
              OR (r.ch_id_b IN ({$ch_ids_str}) AND r.ch_id_from != r.ch_id_b))
            ORDER BY r.cr_id DESC";
    $result = sql_query($sql);
    if ($result) { while ($row = sql_fetch_array($result)) { $all_received[] = $row; } }
    $relation_pending_count = count($all_received);

    // 보낸 대기 신청
    $sql = "SELECT r.*, ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, cb.ch_name AS name_b, cb.ch_thumb AS thumb_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'pending' AND r.ch_id_from IN ({$ch_ids_str})
            ORDER BY r.cr_id DESC";
    $result = sql_query($sql);
    if ($result) { while ($row = sql_fetch_array($result)) { $all_sent[] = $row; } }

    // 로그 대기중 (accepted)
    $sql = "SELECT r.*, ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, cb.ch_name AS name_b, cb.ch_thumb AS thumb_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'accepted'
            AND (r.ch_id_a IN ({$ch_ids_str}) OR r.ch_id_b IN ({$ch_ids_str}))
            ORDER BY r.cr_id DESC";
    $result = sql_query($sql);
    if ($result) { while ($row = sql_fetch_array($result)) { $all_accepted[] = $row; } }

    // 활성 관계
    $sql = "SELECT r.*, ca.ch_name AS name_a, ca.ch_thumb AS thumb_a, cb.ch_name AS name_b, cb.ch_thumb AS thumb_b
            FROM {$g5['mg_relation_table']} r
            JOIN {$g5['mg_character_table']} ca ON r.ch_id_a = ca.ch_id
            JOIN {$g5['mg_character_table']} cb ON r.ch_id_b = cb.ch_id
            WHERE r.cr_status = 'active'
            AND (r.ch_id_a IN ({$ch_ids_str}) OR r.ch_id_b IN ({$ch_ids_str}))
            ORDER BY r.cr_id DESC";
    $result = sql_query($sql);
    if ($result) { while ($row = sql_fetch_array($result)) { $all_active[] = $row; } }
}

include_once(G5_THEME_PATH.'/head.php');
?>

<div class="mg-inner">
    <!-- 페이지 헤더 -->
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-mg-text-primary">내 캐릭터</h1>
        <?php if ($tab === 'characters' && $can_create) { ?>
        <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="btn btn-primary inline-flex items-center gap-2">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>새 캐릭터</span>
        </a>
        <?php } ?>
    </div>

    <!-- 탭 -->
    <div class="flex gap-1 mb-6 border-b border-mg-bg-tertiary">
        <a href="<?php echo G5_BBS_URL; ?>/character.php?tab=characters" class="px-4 py-2.5 text-sm font-medium transition-colors <?php echo $tab === 'characters' ? 'text-mg-accent border-b-2 border-mg-accent' : 'text-mg-text-muted hover:text-mg-text-primary'; ?>">
            캐릭터 (<?php echo $current_count; ?>/<?php echo $max_characters; ?>)
        </a>
        <a href="<?php echo G5_BBS_URL; ?>/character.php?tab=relations" class="px-4 py-2.5 text-sm font-medium transition-colors flex items-center gap-1.5 <?php echo $tab === 'relations' ? 'text-mg-accent border-b-2 border-mg-accent' : 'text-mg-text-muted hover:text-mg-text-primary'; ?>">
            관계
            <?php if ($relation_pending_count > 0) { ?>
            <span class="bg-mg-accent text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo $relation_pending_count; ?></span>
            <?php } ?>
        </a>
    </div>

    <?php if ($tab === 'relations') { ?>
    <!-- ==================== 관계 탭 ==================== -->

    <!-- 받은 신청 -->
    <div class="mb-6">
        <h2 class="text-lg font-bold text-mg-text-primary mb-3 flex items-center gap-2">
            받은 신청
            <?php if ($relation_pending_count > 0) { ?>
            <span class="text-sm bg-mg-accent text-white px-2 py-0.5 rounded-full"><?php echo $relation_pending_count; ?></span>
            <?php } ?>
        </h2>
        <?php if (empty($all_received)) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 text-center text-mg-text-muted text-sm">받은 관계 신청이 없습니다.</div>
        <?php } else { ?>
        <div class="space-y-3">
            <?php foreach ($all_received as $rel) {
                $from_is_a = ($rel['ch_id_from'] == $rel['ch_id_a']);
                $from_name = $from_is_a ? $rel['name_a'] : $rel['name_b'];
                $from_thumb = $from_is_a ? $rel['thumb_a'] : $rel['thumb_b'];
                $from_label = $from_is_a ? $rel['cr_label_a'] : $rel['cr_label_b'];
                $from_memo = $from_is_a ? $rel['cr_memo_a'] : $rel['cr_memo_b'];
                $to_name = $from_is_a ? $rel['name_b'] : $rel['name_a'];
            ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
                <div class="flex items-start gap-3">
                    <?php if ($from_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$from_thumb; ?>" class="w-11 h-11 rounded-full object-cover flex-shrink-0" alt="">
                    <?php } else { ?>
                    <div class="w-11 h-11 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                    <?php } ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($from_name); ?></span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-mg-text-muted"></i>
                            <span class="font-medium text-mg-accent"><?php echo htmlspecialchars($to_name); ?></span>
                        </div>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($rel['cr_color'] ?: '#95a5a6'); ?>"></span>
                            <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($from_label); ?></span>
                        </div>
                        <?php if ($from_memo) { ?>
                        <p class="text-xs text-mg-text-muted mt-1"><?php echo htmlspecialchars($from_memo); ?></p>
                        <?php } ?>
                        <p class="text-xs text-mg-text-muted mt-1"><?php echo date('Y.m.d H:i', strtotime($rel['cr_datetime'])); ?></p>
                    </div>
                </div>
                <div class="flex gap-2 mt-3 ml-14">
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $from_is_a ? $rel['ch_id_b'] : $rel['ch_id_a']; ?>&tab=relation" class="btn btn-primary text-sm" style="padding:0.375rem 1rem;">승인하기</a>
                    <button type="button" onclick="rejectRelation(<?php echo $rel['cr_id']; ?>)" class="bg-mg-bg-tertiary hover:bg-red-500/20 text-mg-text-secondary hover:text-red-400 text-sm px-4 py-1.5 rounded-lg transition-colors">거절</button>
                </div>
            </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 로그 대기중 (accepted) -->
    <?php if (!empty($all_accepted)) {
        $rellog_board = mg_config('relation_log_board', 'rellog');
        $require_log = mg_config('relation_require_log', '1');
    ?>
    <div class="mb-6">
        <h2 class="text-lg font-bold text-mg-text-primary mb-3 flex items-center gap-2">
            로그 대기중
            <span class="text-sm bg-yellow-500/20 text-yellow-400 px-2 py-0.5 rounded-full"><?php echo count($all_accepted); ?></span>
        </h2>
        <div class="space-y-3">
            <?php foreach ($all_accepted as $rel) {
                $my_side = in_array((int)$rel['ch_id_a'], $my_ch_ids) ? 'a' : 'b';
                $my_name = ($my_side === 'a') ? $rel['name_a'] : $rel['name_b'];
                $other_name = ($my_side === 'a') ? $rel['name_b'] : $rel['name_a'];
                $other_thumb = ($my_side === 'a') ? $rel['thumb_b'] : $rel['thumb_a'];
                $my_label = ($my_side === 'a') ? ($rel['cr_label_a'] ?: $rel['cr_label_b']) : ($rel['cr_label_b'] ?: $rel['cr_label_a']);
                $my_wr = ($my_side === 'a') ? $rel['cr_wr_id_a'] : $rel['cr_wr_id_b'];
                $other_wr = ($my_side === 'a') ? $rel['cr_wr_id_b'] : $rel['cr_wr_id_a'];
                $my_submitted = !empty($my_wr);
                $other_submitted = !empty($other_wr);
                $my_ch_id_rel = ($my_side === 'a') ? (int)$rel['ch_id_a'] : (int)$rel['ch_id_b'];
            ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
                <div class="flex items-start gap-3">
                    <?php if ($other_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" class="w-11 h-11 rounded-full object-cover flex-shrink-0" alt="">
                    <?php } else { ?>
                    <div class="w-11 h-11 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                    <?php } ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-mg-accent"><?php echo htmlspecialchars($my_name); ?></span>
                            <i data-lucide="arrow-left-right" class="w-3.5 h-3.5 text-mg-text-muted"></i>
                            <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($other_name); ?></span>
                        </div>
                        <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($my_label); ?></span>
                        <div class="flex gap-3 mt-1.5 text-xs">
                            <span class="<?php echo $my_submitted ? 'text-green-400' : 'text-yellow-400'; ?>">내 로그: <?php echo $my_submitted ? '제출 완료' : '미제출'; ?></span>
                            <span class="<?php echo $other_submitted ? 'text-green-400' : 'text-mg-text-muted'; ?>">상대 로그: <?php echo $other_submitted ? '제출 완료' : '미제출'; ?></span>
                        </div>
                    </div>
                    <?php if (!$my_submitted) { ?>
                    <a href="<?php echo G5_BBS_URL; ?>/board.php?bo_table=<?php echo $rellog_board; ?>&w=write&cr_id=<?php echo $rel['cr_id']; ?>" class="text-xs bg-mg-accent text-white px-3 py-1.5 rounded-lg hover:opacity-80 transition-opacity flex-shrink-0">로그 작성</a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- 보낸 신청 -->
    <?php if (!empty($all_sent)) { ?>
    <div class="mb-6">
        <h2 class="text-lg font-bold text-mg-text-primary mb-3">보낸 신청</h2>
        <div class="space-y-3">
            <?php foreach ($all_sent as $rel) {
                $to_is_a = ($rel['ch_id_from'] != $rel['ch_id_a']);
                $to_name = $to_is_a ? $rel['name_a'] : $rel['name_b'];
                $to_thumb = $to_is_a ? $rel['thumb_a'] : $rel['thumb_b'];
                $my_name = $to_is_a ? $rel['name_b'] : $rel['name_a'];
                $my_label = $to_is_a ? $rel['cr_label_b'] : $rel['cr_label_a'];
            ?>
            <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4">
                <div class="flex items-center gap-3">
                    <?php if ($to_thumb) { ?>
                    <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$to_thumb; ?>" class="w-11 h-11 rounded-full object-cover flex-shrink-0" alt="">
                    <?php } else { ?>
                    <div class="w-11 h-11 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                    <?php } ?>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-mg-accent"><?php echo htmlspecialchars($my_name); ?></span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5 text-mg-text-muted"></i>
                            <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($to_name); ?></span>
                        </div>
                        <span class="text-sm text-mg-text-secondary"><?php echo htmlspecialchars($my_label); ?></span>
                        <p class="text-xs text-mg-text-muted mt-1"><?php echo date('Y.m.d H:i', strtotime($rel['cr_datetime'])); ?></p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="text-xs text-yellow-400 bg-yellow-500/10 px-2 py-1 rounded">대기중</span>
                        <button type="button" onclick="cancelRelation(<?php echo $rel['cr_id']; ?>, <?php echo $rel['ch_id_from']; ?>)" class="text-xs text-red-400 hover:text-red-300 p-1.5 rounded hover:bg-mg-bg-tertiary transition-colors" title="취소">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>

    <!-- 활성 관계 -->
    <div class="mb-6">
        <h2 class="text-lg font-bold text-mg-text-primary mb-3">활성 관계 (<?php echo count($all_active); ?>)</h2>
        <?php if (empty($all_active)) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-6 text-center text-mg-text-muted text-sm">활성 관계가 없습니다.</div>
        <?php } else { ?>
        <div class="space-y-3">
            <?php foreach ($all_active as $rel) {
                $my_side = in_array((int)$rel['ch_id_a'], $my_ch_ids) ? 'a' : 'b';
                $my_name = ($my_side === 'a') ? $rel['name_a'] : $rel['name_b'];
                $other_name = ($my_side === 'a') ? $rel['name_b'] : $rel['name_a'];
                $other_thumb = ($my_side === 'a') ? $rel['thumb_b'] : $rel['thumb_a'];
                $other_ch_id = ($my_side === 'a') ? $rel['ch_id_b'] : $rel['ch_id_a'];
                $my_label = ($my_side === 'a') ? $rel['cr_label_a'] : $rel['cr_label_b'];
                $other_label = ($my_side === 'a') ? $rel['cr_label_b'] : $rel['cr_label_a'];
            ?>
            <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $other_ch_id; ?>" class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary p-4 flex items-center gap-3 hover:border-mg-accent/50 transition-colors block">
                <?php if ($other_thumb) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$other_thumb; ?>" class="w-11 h-11 rounded-full object-cover flex-shrink-0" alt="">
                <?php } else { ?>
                <div class="w-11 h-11 rounded-full bg-mg-bg-tertiary flex items-center justify-center text-mg-text-muted flex-shrink-0">?</div>
                <?php } ?>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-medium text-mg-accent"><?php echo htmlspecialchars($my_name); ?></span>
                        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:<?php echo htmlspecialchars($rel['cr_color'] ?: '#95a5a6'); ?>"></span>
                        <span class="font-medium text-mg-text-primary"><?php echo htmlspecialchars($other_name); ?></span>
                    </div>
                    <div class="text-sm text-mg-text-secondary">
                        <?php echo htmlspecialchars($my_label); ?>
                        <?php if ($other_label && $other_label !== $my_label) { ?>
                        <span class="text-mg-text-muted">/ <?php echo htmlspecialchars($other_label); ?></span>
                        <?php } ?>
                    </div>
                </div>
                <i data-lucide="chevron-right" class="w-4 h-4 text-mg-text-muted flex-shrink-0"></i>
            </a>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

    <!-- 관계 탭 JS -->
    <script>
    var REL_API = '<?php echo G5_BBS_URL; ?>/relation_api.php';

    function rejectRelation(crId) {
        mgConfirm('이 관계 신청을 거절하시겠습니까?', function() {
            var fd = new FormData();
            fd.append('action', 'reject');
            fd.append('cr_id', crId);
            fetch(REL_API, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    mgToast(d.message || (d.success ? '거절했습니다.' : '오류'), d.success ? 'success' : 'error');
                    if (d.success) location.reload();
                });
        });
    }

    function cancelRelation(crId, chId) {
        mgConfirm('보낸 신청을 취소하시겠습니까?', function() {
            var fd = new FormData();
            fd.append('action', 'delete');
            fd.append('cr_id', crId);
            fd.append('my_ch_id', chId);
            fetch(REL_API, { method: 'POST', body: fd })
                .then(function(r) { return r.json(); })
                .then(function(d) {
                    mgToast(d.message || (d.success ? '취소했습니다.' : '오류'), d.success ? 'success' : 'error');
                    if (d.success) location.reload();
                });
        });
    }
    </script>

    <?php } else { ?>
    <!-- ==================== 캐릭터 탭 ==================== -->

    <!-- 캐릭터 목록 -->
    <?php if (count($characters) > 0) { ?>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($characters as $char) { ?>
        <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary overflow-hidden hover:border-mg-accent/50 transition-colors group">
            <!-- 썸네일 -->
            <div class="aspect-square bg-mg-bg-tertiary relative overflow-hidden">
                <?php if ($char['ch_thumb']) { ?>
                <img src="<?php echo MG_CHAR_IMAGE_URL.'/'.$char['ch_thumb']; ?>" alt="<?php echo $char['ch_name']; ?>" class="w-full h-full object-cover">
                <?php } else { ?>
                <div class="w-full h-full flex items-center justify-center text-mg-text-muted">
                    <i data-lucide="user" class="w-16 h-16"></i>
                </div>
                <?php } ?>

                <!-- 상태 배지 -->
                <div class="absolute top-2 left-2 flex gap-1">
                    <?php if ($char['ch_main']) { ?>
                    <span class="bg-mg-accent text-white text-xs px-2 py-0.5 rounded-full">대표</span>
                    <?php } ?>
                    <?php
                    $is_rejected = isset($rejected_info[$char['ch_id']]);
                    if ($is_rejected) {
                        $state = array('반려됨', 'bg-red-500');
                    } else {
                        $state_labels = array(
                            'editing' => array('수정중', 'bg-gray-500'),
                            'pending' => array('승인대기', 'bg-yellow-500'),
                            'approved' => array('승인됨', 'bg-green-500'),
                        );
                        $state = $state_labels[$char['ch_state']] ?? array('', '');
                    }
                    ?>
                    <span class="<?php echo $state[1]; ?> text-white text-xs px-2 py-0.5 rounded-full"><?php echo $state[0]; ?></span>
                </div>

                <!-- 호버 액션 -->
                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                    <a href="<?php echo G5_BBS_URL; ?>/character_form.php?ch_id=<?php echo $char['ch_id']; ?>" class="bg-mg-bg-secondary hover:bg-mg-bg-tertiary text-mg-text-primary p-2 rounded-lg transition-colors" title="수정">
                        <i data-lucide="pencil" class="w-5 h-5"></i>
                    </a>
                    <a href="<?php echo G5_BBS_URL; ?>/character_view.php?ch_id=<?php echo $char['ch_id']; ?>" class="bg-mg-bg-secondary hover:bg-mg-bg-tertiary text-mg-text-primary p-2 rounded-lg transition-colors" title="프로필">
                        <i data-lucide="eye" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>

            <!-- 정보 -->
            <div class="p-4">
                <h3 class="font-bold text-mg-text-primary text-lg truncate"><?php echo $char['ch_name']; ?></h3>
                <div class="flex items-center gap-2 mt-1 text-sm text-mg-text-muted">
                    <?php if ($char['side_name']) { ?>
                    <span><?php echo $char['side_name']; ?></span>
                    <?php } ?>
                    <?php if ($char['side_name'] && $char['class_name']) { ?>
                    <span class="text-mg-bg-tertiary">|</span>
                    <?php } ?>
                    <?php if ($char['class_name']) { ?>
                    <span><?php echo $char['class_name']; ?></span>
                    <?php } ?>
                </div>
                <?php if ($is_rejected && $rejected_info[$char['ch_id']]) { ?>
                <p class="text-xs mt-2 px-2 py-1 rounded" style="background:rgba(239,68,68,0.1);color:#f87171;">
                    반려 사유: <?php echo htmlspecialchars(mb_strimwidth($rejected_info[$char['ch_id']], 0, 50, '...')); ?>
                </p>
                <?php } else { ?>
                <p class="text-xs text-mg-text-muted mt-2">
                    <?php echo date('Y.m.d', strtotime($char['ch_datetime'])); ?> 등록
                </p>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
    <!-- 빈 상태 -->
    <div class="bg-mg-bg-secondary rounded-xl border border-mg-bg-tertiary py-16 px-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-mg-bg-tertiary flex items-center justify-center">
            <i data-lucide="user" class="w-8 h-8 text-mg-text-muted"></i>
        </div>
        <h3 class="text-lg font-medium text-mg-text-primary mb-2">아직 캐릭터가 없습니다</h3>
        <p class="text-mg-text-muted mb-6">첫 번째 캐릭터를 만들어보세요!</p>
        <a href="<?php echo G5_BBS_URL; ?>/character_form.php" class="btn btn-primary inline-flex items-center gap-2 px-6 py-2.5">
            <i data-lucide="plus" class="w-5 h-5"></i>
            <span>캐릭터 만들기</span>
        </a>
    </div>
    <?php } ?>
    <?php } // end tab ?>
</div>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>
