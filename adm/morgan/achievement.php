<?php
/**
 * Morgan Edition - 업적 관리
 */

$sub_menu = "801200";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'list';

// ==========================================
// AJAX: 회원 검색
// ==========================================
if (isset($_GET['ajax_member_search'])) {
    header('Content-Type: application/json');
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    $members = array();
    if (mb_strlen($q) >= 1) {
        $q_esc = sql_real_escape_string($q);
        $sql = "SELECT mb_id, mb_nick FROM {$g5['member_table']}
                WHERE (mb_id LIKE '%{$q_esc}%' OR mb_nick LIKE '%{$q_esc}%')
                AND mb_leave_date = '' LIMIT 20";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $members[] = $row;
        }
    }
    echo json_encode($members);
    exit;
}

// ==========================================
// AJAX: 단일 업적 데이터
// ==========================================
if (isset($_GET['ajax_achievement'])) {
    header('Content-Type: application/json');
    $ac_id = (int)$_GET['ac_id'];
    $ac = sql_fetch("SELECT * FROM {$g5['mg_achievement_table']} WHERE ac_id = {$ac_id}");
    if ($ac) {
        // 단계 목록도 함께
        $tiers = array();
        $tr = sql_query("SELECT * FROM {$g5['mg_achievement_tier_table']} WHERE ac_id = {$ac_id} ORDER BY at_level");
        while ($row = sql_fetch_array($tr)) {
            $tiers[] = $row;
        }
        $ac['tiers'] = $tiers;
    }
    echo json_encode($ac ?: null);
    exit;
}

// 카테고리 / 조건 유형
$categories = mg_achievement_categories();
$condition_types = array(
    'write_count' => '글 작성 수',
    'comment_count' => '댓글 작성 수',
    'rp_reply_count' => 'RP 이음 수',
    'rp_create_count' => 'RP 개설 수',
    'attendance_streak' => '연속 출석일',
    'attendance_total' => '누적 출석일',
    'point_current' => '보유 포인트',
    'shop_buy_count' => '상점 구매 횟수',
    'pioneer_stamina_total' => '개척 노동력 총 투입',
    'pioneer_material_total' => '개척 재료 총 투입',
    'pioneer_facility_count' => '시설 참여 수',
    'emoticon_own_count' => '이모티콘 보유 수',
    'character_count' => '캐릭터 수',
    'like_count' => '좋아요 수',
    'manual' => '수동 부여 (관리자)',
);

// 업적 목록
$achievements = array();
$sql = "SELECT a.*,
        (SELECT COUNT(*) FROM {$g5['mg_achievement_tier_table']} WHERE ac_id = a.ac_id) as tier_count,
        (SELECT COUNT(DISTINCT mb_id) FROM {$g5['mg_user_achievement_table']} WHERE ac_id = a.ac_id AND ua_completed = 1) as completed_count,
        (SELECT COUNT(DISTINCT mb_id) FROM {$g5['mg_user_achievement_table']} WHERE ac_id = a.ac_id AND ua_progress > 0) as progress_count
    FROM {$g5['mg_achievement_table']} a
    ORDER BY a.ac_category, a.ac_order, a.ac_id";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $achievements[] = $row;
}

// 카테고리 필터
$filter_cat = isset($_GET['category']) ? $_GET['category'] : '';

// Tab: achievers — 특정 업적의 달성자 목록
$achievers = array();
$achiever_ac = null;
if ($tab == 'achievers' && isset($_GET['ac_id'])) {
    $ac_id = (int)$_GET['ac_id'];
    $achiever_ac = sql_fetch("SELECT * FROM {$g5['mg_achievement_table']} WHERE ac_id = {$ac_id}");
    if ($achiever_ac) {
        $ua_sql = "SELECT ua.*, m.mb_nick
            FROM {$g5['mg_user_achievement_table']} ua
            LEFT JOIN {$g5['member_table']} m ON ua.mb_id = m.mb_id
            WHERE ua.ac_id = {$ac_id}
            ORDER BY ua.ua_completed DESC, ua.ua_tier DESC, ua.ua_progress DESC";
        $ua_result = sql_query($ua_sql);
        while ($row = sql_fetch_array($ua_result)) {
            $achievers[] = $row;
        }
    }
}

// Tab: tiers — 단계 관리
$edit_ac = null;
$edit_tiers = array();
if ($tab == 'tiers' && isset($_GET['ac_id'])) {
    $ac_id = (int)$_GET['ac_id'];
    $edit_ac = sql_fetch("SELECT * FROM {$g5['mg_achievement_table']} WHERE ac_id = {$ac_id}");
    if ($edit_ac) {
        $tr = sql_query("SELECT * FROM {$g5['mg_achievement_tier_table']} WHERE ac_id = {$ac_id} ORDER BY at_level");
        while ($row = sql_fetch_array($tr)) {
            $edit_tiers[] = $row;
        }
    }
}

$g5['title'] = '업적 관리';
require_once __DIR__.'/_head.php';

$update_url = G5_ADMIN_URL . '/morgan/achievement_update.php';
$self_url = G5_ADMIN_URL . '/morgan/achievement.php';

// 희귀도 라벨
$rarity_labels = array(
    'common' => 'Common',
    'uncommon' => 'Uncommon',
    'rare' => 'Rare',
    'epic' => 'Epic',
    'legendary' => 'Legendary',
);
$rarity_colors = array(
    'common' => '#949ba4',
    'uncommon' => '#22c55e',
    'rare' => '#3b82f6',
    'epic' => '#a855f7',
    'legendary' => '#f59e0b',
);
?>

<!-- 탭 네비게이션 -->
<div class="mg-tabs" style="margin-bottom:1.5rem;">
    <a href="?tab=list" class="mg-tab <?php echo in_array($tab, array('list', 'tiers', 'achievers')) ? 'active' : ''; ?>">업적 목록</a>
    <a href="?tab=grant" class="mg-tab <?php echo $tab == 'grant' ? 'active' : ''; ?>">수동 부여</a>
</div>

<?php if ($tab == 'list') { ?>
<!-- ================================ -->
<!-- 업적 목록 -->
<!-- ================================ -->

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
        <a href="?tab=list" class="mg-btn <?php echo !$filter_cat ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm">전체</a>
        <?php foreach ($categories as $ck => $cv) { ?>
        <a href="?tab=list&category=<?php echo $ck; ?>" class="mg-btn <?php echo $filter_cat == $ck ? 'mg-btn-primary' : 'mg-btn-secondary'; ?> mg-btn-sm"><?php echo $cv; ?></a>
        <?php } ?>
    </div>
    <button type="button" class="mg-btn mg-btn-primary" onclick="newAchievement()">+ 업적 추가</button>
</div>

<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:900px;">
            <thead>
                <tr>
                    <th style="width:50px;">ID</th>
                    <th>업적명</th>
                    <th style="width:80px;">카테고리</th>
                    <th style="width:100px;">유형</th>
                    <th style="width:70px;">단계</th>
                    <th style="width:80px;">희귀도</th>
                    <th style="width:80px;">달성자</th>
                    <th style="width:60px;">상태</th>
                    <th style="width:200px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $filtered = $achievements;
                if ($filter_cat) {
                    $filtered = array_filter($achievements, function($a) use ($filter_cat) {
                        return $a['ac_category'] == $filter_cat;
                    });
                }
                if (empty($filtered)) {
                ?>
                <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">등록된 업적이 없습니다.</td></tr>
                <?php } else { foreach ($filtered as $ac) {
                    $cat_label = $categories[$ac['ac_category']] ?? $ac['ac_category'];
                    $rarity = $ac['ac_rarity'] ?: 'common';
                    $r_color = $rarity_colors[$rarity] ?? '#949ba4';
                ?>
                <tr>
                    <td><?php echo $ac['ac_id']; ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($ac['ac_name']); ?></strong>
                        <?php if ($ac['ac_hidden']) { ?>
                        <span class="mg-badge" style="font-size:0.65rem;vertical-align:middle;">숨김</span>
                        <?php } ?>
                        <br><small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars(mb_substr($ac['ac_desc'], 0, 40)); ?></small>
                    </td>
                    <td><span class="mg-badge"><?php echo $cat_label; ?></span></td>
                    <td style="text-align:center;">
                        <?php if ($ac['ac_type'] == 'progressive') { ?>
                        <span class="mg-badge mg-badge-primary">단계형</span>
                        <?php } else { ?>
                        <span class="mg-badge">일회성</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($ac['ac_type'] == 'progressive') { ?>
                        <a href="?tab=tiers&ac_id=<?php echo $ac['ac_id']; ?>" style="color:var(--mg-accent);"><?php echo (int)$ac['tier_count']; ?>단계</a>
                        <?php } else { echo '-'; } ?>
                    </td>
                    <td style="text-align:center;">
                        <span style="color:<?php echo $r_color; ?>;font-weight:600;font-size:0.8rem;"><?php echo $rarity_labels[$rarity] ?? $rarity; ?></span>
                    </td>
                    <td style="text-align:center;">
                        <a href="?tab=achievers&ac_id=<?php echo $ac['ac_id']; ?>" style="color:var(--mg-accent);">
                            <?php echo (int)$ac['completed_count']; ?>명
                        </a>
                        <?php if ((int)$ac['progress_count'] > (int)$ac['completed_count']) { ?>
                        <br><small style="color:var(--mg-text-muted);">진행 <?php echo (int)$ac['progress_count']; ?></small>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($ac['ac_use']) { ?>
                        <span style="color:var(--mg-success);cursor:pointer;" onclick="toggleAch(<?php echo $ac['ac_id']; ?>, 0)" title="클릭하여 비활성화">&check;</span>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);cursor:pointer;" onclick="toggleAch(<?php echo $ac['ac_id']; ?>, 1)" title="클릭하여 활성화">&cross;</span>
                        <?php } ?>
                    </td>
                    <td style="text-align:center;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editAchievement(<?php echo $ac['ac_id']; ?>)">편집</button>
                        <?php if ($ac['ac_type'] == 'progressive') { ?>
                        <a href="?tab=tiers&ac_id=<?php echo $ac['ac_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">단계</a>
                        <?php } ?>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteAch(<?php echo $ac['ac_id']; ?>, '<?php echo addslashes($ac['ac_name']); ?>')">삭제</button>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 업적 추가/편집 모달 -->
<div id="ach-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:600px;">
        <div class="mg-modal-header">
            <h3 id="ach-modal-title">업적 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeAchModal()">&times;</button>
        </div>
        <form id="ach-form">
            <input type="hidden" name="mode" value="save">
            <input type="hidden" name="ac_id" id="f_ac_id" value="0">

            <div class="mg-modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group" style="grid-column:1/3;">
                        <label class="mg-form-label">업적 이름 *</label>
                        <input type="text" name="ac_name" id="f_ac_name" class="mg-form-input" required>
                    </div>
                    <div class="mg-form-group" style="grid-column:1/3;">
                        <label class="mg-form-label">설명</label>
                        <textarea name="ac_desc" id="f_ac_desc" class="mg-form-input" rows="2"></textarea>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">카테고리</label>
                        <select name="ac_category" id="f_ac_category" class="mg-form-input">
                            <?php foreach ($categories as $ck => $cv) { ?>
                            <option value="<?php echo $ck; ?>"><?php echo $cv; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">유형</label>
                        <select name="ac_type" id="f_ac_type" class="mg-form-input" onchange="toggleAchType()">
                            <option value="progressive">단계형 (Progressive)</option>
                            <option value="onetime">일회성 (One-time)</option>
                        </select>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">아이콘 (이미지 URL)</label>
                        <input type="text" name="ac_icon" id="f_ac_icon" class="mg-form-input" placeholder="/data/achievement/icon.png">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">희귀도</label>
                        <select name="ac_rarity" id="f_ac_rarity" class="mg-form-input">
                            <?php foreach ($rarity_labels as $rk => $rv) { ?>
                            <option value="<?php echo $rk; ?>"><?php echo $rv; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="ac_order" id="f_ac_order" class="mg-form-input" value="0" min="0">
                    </div>
                    <div class="mg-form-group" style="display:flex;align-items:flex-end;">
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                            <input type="checkbox" name="ac_use" id="f_ac_use" value="1" checked>
                            <span>활성화</span>
                        </label>
                        <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-left:1rem;">
                            <input type="checkbox" name="ac_hidden" id="f_ac_hidden" value="1">
                            <span>숨김 업적</span>
                        </label>
                    </div>
                </div>

                <!-- 달성 조건 -->
                <div style="border-top:1px solid var(--mg-border, var(--mg-bg-tertiary));padding-top:1rem;margin-top:0.5rem;">
                    <label class="mg-form-label" style="font-size:1rem;font-weight:600;">달성 조건</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:0.5rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">조건 유형</label>
                            <select name="condition_type" id="f_cond_type" class="mg-form-input" onchange="toggleCondFields()">
                                <?php foreach ($condition_types as $ctk => $ctv) { ?>
                                <option value="<?php echo $ctk; ?>"><?php echo $ctv; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mg-form-group" id="cond_board_group">
                            <label class="mg-form-label">특정 게시판 (선택)</label>
                            <input type="text" name="condition_board" id="f_cond_board" class="mg-form-input" placeholder="비워두면 전체">
                            <small style="color:var(--mg-text-muted);">bo_table 값</small>
                        </div>
                    </div>
                    <div id="onetime-fields">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="mg-form-group">
                                <label class="mg-form-label">목표값</label>
                                <input type="number" name="condition_target" id="f_cond_target" class="mg-form-input" value="1" min="1">
                            </div>
                        </div>
                    </div>
                    <div id="progressive-note" style="display:none;">
                        <div class="mg-alert mg-alert-info" style="margin-top:0.5rem;">
                            단계형 업적의 목표값과 보상은 단계별로 설정합니다. 저장 후 [단계] 버튼으로 관리하세요.
                        </div>
                    </div>
                </div>

                <!-- 보상 (일회성만) -->
                <div id="onetime-reward" style="border-top:1px solid var(--mg-border, var(--mg-bg-tertiary));padding-top:1rem;margin-top:0.5rem;">
                    <label class="mg-form-label" style="font-size:1rem;font-weight:600;">달성 보상</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-top:0.5rem;">
                        <div class="mg-form-group">
                            <label class="mg-form-label">보상 유형</label>
                            <select name="reward_type" id="f_reward_type" class="mg-form-input" onchange="toggleRewardFields()">
                                <option value="">보상 없음</option>
                                <option value="point">포인트</option>
                                <option value="material">재료</option>
                                <option value="item">상점 아이템</option>
                            </select>
                        </div>
                        <div class="mg-form-group" id="reward_amount_group" style="display:none;">
                            <label class="mg-form-label">수량</label>
                            <input type="number" name="reward_amount" id="f_reward_amount" class="mg-form-input" value="0" min="0">
                        </div>
                        <div class="mg-form-group" id="reward_code_group" style="display:none;">
                            <label class="mg-form-label">코드/ID</label>
                            <input type="text" name="reward_code" id="f_reward_code" class="mg-form-input" placeholder="mt_code 또는 si_id">
                        </div>
                    </div>
                </div>
            </div>

            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeAchModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
var _updateUrl = '<?php echo $update_url; ?>';
var _selfUrl = '<?php echo $self_url; ?>';

function newAchievement() {
    document.getElementById('ach-modal-title').textContent = '업적 추가';
    document.getElementById('f_ac_id').value = '0';
    document.getElementById('f_ac_name').value = '';
    document.getElementById('f_ac_desc').value = '';
    document.getElementById('f_ac_category').value = 'activity';
    document.getElementById('f_ac_type').value = 'progressive';
    document.getElementById('f_ac_icon').value = '';
    document.getElementById('f_ac_rarity').value = 'common';
    document.getElementById('f_ac_order').value = '0';
    document.getElementById('f_ac_use').checked = true;
    document.getElementById('f_ac_hidden').checked = false;
    document.getElementById('f_cond_type').value = 'write_count';
    document.getElementById('f_cond_board').value = '';
    document.getElementById('f_cond_target').value = '1';
    document.getElementById('f_reward_type').value = '';
    document.getElementById('f_reward_amount').value = '0';
    document.getElementById('f_reward_code').value = '';
    toggleAchType();
    toggleCondFields();
    toggleRewardFields();
    document.getElementById('ach-modal').style.display = 'flex';
}

function editAchievement(acId) {
    fetch(_selfUrl + '?ajax_achievement=1&ac_id=' + acId)
    .then(function(r) { return r.json(); })
    .then(function(ac) {
        if (!ac) { alert('업적을 찾을 수 없습니다.'); return; }

        document.getElementById('ach-modal-title').textContent = '업적 편집: ' + ac.ac_name;
        document.getElementById('f_ac_id').value = ac.ac_id;
        document.getElementById('f_ac_name').value = ac.ac_name || '';
        document.getElementById('f_ac_desc').value = ac.ac_desc || '';
        document.getElementById('f_ac_category').value = ac.ac_category || 'activity';
        document.getElementById('f_ac_type').value = ac.ac_type || 'progressive';
        document.getElementById('f_ac_icon').value = ac.ac_icon || '';
        document.getElementById('f_ac_rarity').value = ac.ac_rarity || 'common';
        document.getElementById('f_ac_order').value = ac.ac_order || '0';
        document.getElementById('f_ac_use').checked = ac.ac_use == 1;
        document.getElementById('f_ac_hidden').checked = ac.ac_hidden == 1;

        // 조건 JSON 파싱
        var cond = {};
        try { cond = JSON.parse(ac.ac_condition || '{}'); } catch(e) {}
        document.getElementById('f_cond_type').value = cond.type || 'manual';
        document.getElementById('f_cond_board').value = cond.board || '';
        document.getElementById('f_cond_target').value = cond.target || '1';

        // 보상 JSON 파싱
        var reward = {};
        try { reward = JSON.parse(ac.ac_reward || '{}'); } catch(e) {}
        document.getElementById('f_reward_type').value = reward.type || '';
        document.getElementById('f_reward_amount').value = reward.amount || '0';
        document.getElementById('f_reward_code').value = reward.mt_code || reward.si_id || '';

        toggleAchType();
        toggleCondFields();
        toggleRewardFields();
        document.getElementById('ach-modal').style.display = 'flex';
    })
    .catch(function() { alert('데이터 로드 실패'); });
}

function closeAchModal() {
    document.getElementById('ach-modal').style.display = 'none';
}

document.getElementById('ach-modal').addEventListener('click', function(e) {
    if (e.target === this) closeAchModal();
});

function toggleAchType() {
    var type = document.getElementById('f_ac_type').value;
    var isOnetime = (type === 'onetime');
    document.getElementById('onetime-fields').style.display = isOnetime ? '' : 'none';
    document.getElementById('onetime-reward').style.display = isOnetime ? '' : 'none';
    document.getElementById('progressive-note').style.display = isOnetime ? 'none' : '';
}

function toggleCondFields() {
    var type = document.getElementById('f_cond_type').value;
    var showBoard = ['write_count', 'comment_count'].indexOf(type) !== -1;
    document.getElementById('cond_board_group').style.display = showBoard ? '' : 'none';
}

function toggleRewardFields() {
    var type = document.getElementById('f_reward_type').value;
    document.getElementById('reward_amount_group').style.display = type ? '' : 'none';
    document.getElementById('reward_code_group').style.display = (type === 'material' || type === 'item') ? '' : 'none';
}

// 업적 저장
document.getElementById('ach-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);

    // 체크박스 보정
    fd.set('ac_use', document.getElementById('f_ac_use').checked ? '1' : '0');
    fd.set('ac_hidden', document.getElementById('f_ac_hidden').checked ? '1' : '0');

    // 조건 JSON 빌드
    var cond = { type: fd.get('condition_type') };
    var board = fd.get('condition_board');
    if (board) cond.board = board;
    if (fd.get('ac_type') === 'onetime') {
        cond.target = parseInt(fd.get('condition_target')) || 1;
    }
    fd.set('ac_condition', JSON.stringify(cond));

    // 보상 JSON 빌드
    var rtype = fd.get('reward_type');
    var reward = {};
    if (rtype) {
        reward.type = rtype;
        reward.amount = parseInt(fd.get('reward_amount')) || 0;
        if (rtype === 'material') reward.mt_code = fd.get('reward_code');
        if (rtype === 'item') reward.si_id = parseInt(fd.get('reward_code')) || 0;
    }
    fd.set('ac_reward', JSON.stringify(reward));

    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || '저장 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
});

// 삭제
function deleteAch(acId, name) {
    if (!confirm('"' + name + '" 업적을 삭제하시겠습니까?\n단계, 달성 기록이 모두 삭제됩니다.')) return;
    var fd = new FormData();
    fd.append('mode', 'delete');
    fd.append('ac_id', acId);
    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
        else alert(data.message || '삭제 실패');
    })
    .catch(function() { alert('요청 실패'); });
}

// 토글
function toggleAch(acId, newState) {
    var fd = new FormData();
    fd.append('mode', 'toggle');
    fd.append('ac_id', acId);
    fd.append('ac_use', newState);
    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
        else alert(data.message || '변경 실패');
    })
    .catch(function() { alert('요청 실패'); });
}

function escHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
</script>

<?php } elseif ($tab == 'tiers') { ?>
<!-- ================================ -->
<!-- 단계 관리 -->
<!-- ================================ -->

<?php if (!$edit_ac) { ?>
<div class="mg-alert mg-alert-error">업적을 찾을 수 없습니다.</div>
<a href="?tab=list" class="mg-btn mg-btn-secondary">&larr; 업적 목록</a>
<?php } else { ?>

<div style="margin-bottom:1rem;">
    <a href="?tab=list" class="mg-btn mg-btn-secondary mg-btn-sm">&larr; 업적 목록</a>
    <span style="margin-left:0.5rem;font-size:1.125rem;font-weight:600;"><?php echo htmlspecialchars($edit_ac['ac_name']); ?> — 단계 관리</span>
</div>

<div class="mg-card" style="margin-bottom:1.5rem;">
    <div class="mg-card-header"><h3>등록된 단계 (<?php echo count($edit_tiers); ?>개)</h3></div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table">
            <thead>
                <tr>
                    <th style="width:50px;">단계</th>
                    <th>단계명</th>
                    <th style="width:100px;">목표값</th>
                    <th>아이콘</th>
                    <th>보상</th>
                    <th style="width:100px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($edit_tiers)) { ?>
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--mg-text-muted);">등록된 단계가 없습니다.</td></tr>
                <?php } else { foreach ($edit_tiers as $t) {
                    $t_reward = json_decode($t['at_reward'] ?: '{}', true);
                    $reward_desc = '';
                    if (!empty($t_reward['type'])) {
                        if ($t_reward['type'] == 'point') $reward_desc = '+' . number_format($t_reward['amount'] ?? 0) . 'P';
                        elseif ($t_reward['type'] == 'material') $reward_desc = ($t_reward['mt_code'] ?? '') . ' x' . ($t_reward['amount'] ?? 0);
                        elseif ($t_reward['type'] == 'item') $reward_desc = '아이템 #' . ($t_reward['si_id'] ?? '?');
                    }
                ?>
                <tr>
                    <td style="text-align:center;font-weight:600;"><?php echo $t['at_level']; ?></td>
                    <td><strong><?php echo htmlspecialchars($t['at_name']); ?></strong></td>
                    <td style="text-align:center;"><?php echo number_format($t['at_target']); ?></td>
                    <td>
                        <?php if ($t['at_icon']) { ?>
                        <img src="<?php echo htmlspecialchars($t['at_icon']); ?>" style="width:24px;height:24px;object-fit:contain;">
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);">기본</span>
                        <?php } ?>
                    </td>
                    <td><?php echo $reward_desc ?: '<span style="color:var(--mg-text-muted);">없음</span>'; ?></td>
                    <td style="text-align:center;">
                        <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editTier(<?php echo $t['at_id']; ?>)">편집</button>
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteTier(<?php echo $t['at_id']; ?>)">삭제</button>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 단계 추가/편집 폼 -->
<div class="mg-card">
    <div class="mg-card-header"><h3 id="tier-form-title">단계 추가</h3></div>
    <div class="mg-card-body">
        <form id="tier-form">
            <input type="hidden" name="mode" value="save_tier">
            <input type="hidden" name="ac_id" value="<?php echo $edit_ac['ac_id']; ?>">
            <input type="hidden" name="at_id" id="tf_at_id" value="0">

            <div style="display:grid;grid-template-columns:80px 1fr 120px;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">단계</label>
                    <input type="number" name="at_level" id="tf_at_level" class="mg-form-input" value="<?php echo count($edit_tiers) + 1; ?>" min="1" required>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">단계명 *</label>
                    <input type="text" name="at_name" id="tf_at_name" class="mg-form-input" required placeholder="예: 글쟁이 I">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">목표값 *</label>
                    <input type="number" name="at_target" id="tf_at_target" class="mg-form-input" value="10" min="1" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">아이콘 URL</label>
                    <input type="text" name="at_icon" id="tf_at_icon" class="mg-form-input" placeholder="비워두면 기본">
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">보상 유형</label>
                    <select name="tier_reward_type" id="tf_reward_type" class="mg-form-input" onchange="toggleTierReward()">
                        <option value="">없음</option>
                        <option value="point">포인트</option>
                        <option value="material">재료</option>
                        <option value="item">아이템</option>
                    </select>
                </div>
                <div class="mg-form-group" id="tf_reward_amount_group" style="display:none;">
                    <label class="mg-form-label">수량</label>
                    <input type="number" name="tier_reward_amount" id="tf_reward_amount" class="mg-form-input" value="0" min="0">
                </div>
                <div class="mg-form-group" id="tf_reward_code_group" style="display:none;">
                    <label class="mg-form-label">코드/ID</label>
                    <input type="text" name="tier_reward_code" id="tf_reward_code" class="mg-form-input" placeholder="mt_code / si_id">
                </div>
            </div>
            <div style="display:flex;gap:0.5rem;">
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
                <button type="button" class="mg-btn mg-btn-secondary" onclick="resetTierForm()">초기화</button>
            </div>
        </form>
    </div>
</div>

<script>
var _updateUrl = '<?php echo $update_url; ?>';
var _acId = <?php echo $edit_ac['ac_id']; ?>;

function toggleTierReward() {
    var t = document.getElementById('tf_reward_type').value;
    document.getElementById('tf_reward_amount_group').style.display = t ? '' : 'none';
    document.getElementById('tf_reward_code_group').style.display = (t === 'material' || t === 'item') ? '' : 'none';
}

function editTier(atId) {
    // 기존 단계 데이터에서 찾기
    var tiers = <?php echo json_encode($edit_tiers); ?>;
    var t = tiers.find(function(x) { return x.at_id == atId; });
    if (!t) return;

    document.getElementById('tier-form-title').textContent = '단계 편집';
    document.getElementById('tf_at_id').value = t.at_id;
    document.getElementById('tf_at_level').value = t.at_level;
    document.getElementById('tf_at_name').value = t.at_name;
    document.getElementById('tf_at_target').value = t.at_target;
    document.getElementById('tf_at_icon').value = t.at_icon || '';

    var reward = {};
    try { reward = JSON.parse(t.at_reward || '{}'); } catch(e) {}
    document.getElementById('tf_reward_type').value = reward.type || '';
    document.getElementById('tf_reward_amount').value = reward.amount || '0';
    document.getElementById('tf_reward_code').value = reward.mt_code || reward.si_id || '';
    toggleTierReward();
}

function resetTierForm() {
    document.getElementById('tier-form-title').textContent = '단계 추가';
    document.getElementById('tf_at_id').value = '0';
    document.getElementById('tf_at_level').value = '<?php echo count($edit_tiers) + 1; ?>';
    document.getElementById('tf_at_name').value = '';
    document.getElementById('tf_at_target').value = '10';
    document.getElementById('tf_at_icon').value = '';
    document.getElementById('tf_reward_type').value = '';
    document.getElementById('tf_reward_amount').value = '0';
    document.getElementById('tf_reward_code').value = '';
    toggleTierReward();
}

function deleteTier(atId) {
    if (!confirm('이 단계를 삭제하시겠습니까?')) return;
    var fd = new FormData();
    fd.append('mode', 'delete_tier');
    fd.append('at_id', atId);
    fd.append('ac_id', _acId);
    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
        else alert(data.message || '삭제 실패');
    })
    .catch(function() { alert('요청 실패'); });
}

document.getElementById('tier-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var fd = new FormData(this);

    // 보상 JSON 빌드
    var rtype = fd.get('tier_reward_type');
    var reward = {};
    if (rtype) {
        reward.type = rtype;
        reward.amount = parseInt(fd.get('tier_reward_amount')) || 0;
        if (rtype === 'material') reward.mt_code = fd.get('tier_reward_code');
        if (rtype === 'item') reward.si_id = parseInt(fd.get('tier_reward_code')) || 0;
    }
    fd.set('at_reward', JSON.stringify(reward));

    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) location.reload();
        else alert(data.message || '저장 실패');
    })
    .catch(function() { alert('요청 실패'); });
});
</script>

<?php } // end edit_ac ?>

<?php } elseif ($tab == 'achievers') { ?>
<!-- ================================ -->
<!-- 달성자 목록 -->
<!-- ================================ -->

<?php if (!$achiever_ac) { ?>
<div class="mg-alert mg-alert-error">업적을 찾을 수 없습니다.</div>
<a href="?tab=list" class="mg-btn mg-btn-secondary">&larr; 업적 목록</a>
<?php } else { ?>

<div style="margin-bottom:1rem;">
    <a href="?tab=list" class="mg-btn mg-btn-secondary mg-btn-sm">&larr; 업적 목록</a>
    <span style="margin-left:0.5rem;font-size:1.125rem;font-weight:600;"><?php echo htmlspecialchars($achiever_ac['ac_name']); ?> — 달성자 (<?php echo count($achievers); ?>명)</span>
</div>

<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:800px;">
            <thead>
                <tr>
                    <th style="width:120px;">회원 ID</th>
                    <th style="width:120px;">닉네임</th>
                    <th style="width:80px;">진행도</th>
                    <th style="width:80px;">단계</th>
                    <th style="width:60px;">완료</th>
                    <th style="width:120px;">부여자</th>
                    <th>부여 사유</th>
                    <th style="width:150px;">달성일</th>
                    <th style="width:70px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($achievers)) { ?>
                <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">달성자가 없습니다.</td></tr>
                <?php } else { foreach ($achievers as $ua) { ?>
                <tr>
                    <td><small><?php echo htmlspecialchars($ua['mb_id']); ?></small></td>
                    <td><strong><?php echo htmlspecialchars($ua['mb_nick'] ?: '-'); ?></strong></td>
                    <td style="text-align:center;"><?php echo number_format($ua['ua_progress']); ?></td>
                    <td style="text-align:center;"><?php echo $ua['ua_tier'] ?: '-'; ?></td>
                    <td style="text-align:center;">
                        <?php echo $ua['ua_completed'] ? '<span style="color:var(--mg-success);">&check;</span>' : '<span style="color:var(--mg-text-muted);">&cross;</span>'; ?>
                    </td>
                    <td>
                        <?php if ($ua['ua_granted_by']) { ?>
                        <span class="mg-badge mg-badge-primary" style="font-size:0.7rem;">수동</span>
                        <small><?php echo htmlspecialchars($ua['ua_granted_by']); ?></small>
                        <?php } else { ?>
                        <span style="color:var(--mg-text-muted);font-size:0.8rem;">자동</span>
                        <?php } ?>
                    </td>
                    <td><small style="color:var(--mg-text-muted);"><?php echo htmlspecialchars($ua['ua_grant_memo'] ?: ''); ?></small></td>
                    <td><small><?php echo $ua['ua_datetime']; ?></small></td>
                    <td style="text-align:center;">
                        <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="revokeAch('<?php echo addslashes($ua['mb_id']); ?>', <?php echo $achiever_ac['ac_id']; ?>, '<?php echo addslashes($ua['mb_nick'] ?: $ua['mb_id']); ?>')">회수</button>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
var _updateUrl = '<?php echo $update_url; ?>';

function revokeAch(mbId, acId, nick) {
    if (!confirm(nick + ' 의 업적을 회수하시겠습니까?\n진행도가 초기화됩니다.')) return;
    var fd = new FormData();
    fd.append('mode', 'revoke');
    fd.append('mb_id', mbId);
    fd.append('ac_id', acId);
    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message || '회수되었습니다.');
            location.reload();
        } else {
            alert(data.message || '회수 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
}
</script>

<?php } // end achiever_ac ?>

<?php } elseif ($tab == 'grant') { ?>
<!-- ================================ -->
<!-- 수동 부여 -->
<!-- ================================ -->

<div class="mg-alert mg-alert-info" style="margin-bottom:1.5rem;">
    세계관 이벤트, 특수 공헌 등 자동 판별이 불가능한 업적을 관리자가 직접 부여합니다.
    모든 유형의 업적을 수동으로 부여할 수 있으며, 보상도 함께 지급됩니다.
</div>

<div class="mg-card">
    <div class="mg-card-header"><h3>업적 수동 부여</h3></div>
    <div class="mg-card-body">
        <form id="grant-form">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">부여할 업적 *</label>
                    <select name="ac_id" id="g_ac_id" class="mg-form-input" required>
                        <option value="">선택하세요</option>
                        <?php
                        $cur_cat = '';
                        foreach ($achievements as $ac) {
                            if (!$ac['ac_use']) continue;
                            $cat = $categories[$ac['ac_category']] ?? $ac['ac_category'];
                            if ($cat !== $cur_cat) {
                                if ($cur_cat) echo '</optgroup>';
                                echo '<optgroup label="' . htmlspecialchars($cat) . '">';
                                $cur_cat = $cat;
                            }
                            echo '<option value="' . $ac['ac_id'] . '">' . htmlspecialchars($ac['ac_name']) . ' (' . ($ac['ac_type'] == 'progressive' ? '단계형' : '일회성') . ')</option>';
                        }
                        if ($cur_cat) echo '</optgroup>';
                        ?>
                    </select>
                </div>
                <div class="mg-form-group">
                    <label class="mg-form-label">보상 지급</label>
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;margin-top:0.5rem;">
                        <input type="checkbox" name="give_reward" id="g_give_reward" value="1" checked>
                        <span>보상(포인트/재료/아이템) 함께 지급</span>
                    </label>
                </div>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">부여 사유</label>
                <input type="text" name="memo" id="g_memo" class="mg-form-input" placeholder="예: 대재앙 이벤트 참여, 1기 마을 주민">
            </div>

            <!-- 회원 검색 -->
            <div class="mg-form-group">
                <label class="mg-form-label">대상 회원 검색</label>
                <div style="display:flex;gap:0.5rem;">
                    <input type="text" id="g_search" class="mg-form-input" placeholder="닉네임 또는 ID 입력" style="flex:1;">
                    <button type="button" class="mg-btn mg-btn-secondary" onclick="searchMembers()">검색</button>
                </div>
            </div>

            <!-- 검색 결과 -->
            <div id="g_search_results" style="display:none;margin-bottom:1rem;">
                <div style="background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);border-radius:0.375rem;max-height:200px;overflow-y:auto;padding:0.5rem;">
                    <div id="g_results_list"></div>
                </div>
            </div>

            <!-- 선택된 회원 -->
            <div class="mg-form-group">
                <label class="mg-form-label">선택된 회원 (<span id="g_selected_count">0</span>명)</label>
                <div id="g_selected_members" style="display:flex;flex-wrap:wrap;gap:0.5rem;min-height:32px;padding:0.5rem;background:var(--mg-bg-primary);border:1px solid var(--mg-bg-tertiary);border-radius:0.375rem;">
                    <span id="g_empty_msg" style="color:var(--mg-text-muted);font-size:0.85rem;">회원을 검색하여 추가하세요.</span>
                </div>
            </div>

            <div style="display:flex;gap:0.5rem;margin-top:1rem;">
                <button type="submit" class="mg-btn mg-btn-primary">일괄 부여</button>
                <button type="button" class="mg-btn mg-btn-secondary" onclick="clearGrantForm()">초기화</button>
            </div>
        </form>
    </div>
</div>

<script>
var _updateUrl = '<?php echo $update_url; ?>';
var _selfUrl = '<?php echo $self_url; ?>';
var _selectedMembers = {};

function searchMembers() {
    var q = document.getElementById('g_search').value.trim();
    if (!q) return;

    fetch(_selfUrl + '?ajax_member_search=1&q=' + encodeURIComponent(q))
    .then(function(r) { return r.json(); })
    .then(function(members) {
        var container = document.getElementById('g_results_list');
        if (!members.length) {
            container.innerHTML = '<div style="padding:0.5rem;color:var(--mg-text-muted);font-size:0.85rem;">검색 결과가 없습니다.</div>';
        } else {
            var html = '';
            members.forEach(function(m) {
                var selected = _selectedMembers[m.mb_id] ? ' disabled style="opacity:0.5;"' : '';
                html += '<div style="display:flex;justify-content:space-between;align-items:center;padding:0.375rem 0.5rem;border-bottom:1px solid var(--mg-bg-tertiary);">';
                html += '<span><strong>' + escHtml(m.mb_nick) + '</strong> <small style="color:var(--mg-text-muted);">' + escHtml(m.mb_id) + '</small></span>';
                html += '<button type="button" class="mg-btn mg-btn-primary mg-btn-sm" onclick="addMember(\'' + escAttr(m.mb_id) + '\', \'' + escAttr(m.mb_nick) + '\')"' + selected + '>추가</button>';
                html += '</div>';
            });
            container.innerHTML = html;
        }
        document.getElementById('g_search_results').style.display = '';
    })
    .catch(function() { alert('검색 실패'); });
}

document.getElementById('g_search').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); searchMembers(); }
});

function addMember(mbId, mbNick) {
    if (_selectedMembers[mbId]) return;
    _selectedMembers[mbId] = mbNick;
    renderSelected();
}

function removeMember(mbId) {
    delete _selectedMembers[mbId];
    renderSelected();
}

function renderSelected() {
    var container = document.getElementById('g_selected_members');
    var keys = Object.keys(_selectedMembers);
    document.getElementById('g_selected_count').textContent = keys.length;
    document.getElementById('g_empty_msg').style.display = keys.length ? 'none' : '';

    // 기존 태그 제거
    container.querySelectorAll('.member-tag').forEach(function(el) { el.remove(); });

    keys.forEach(function(mbId) {
        var tag = document.createElement('span');
        tag.className = 'member-tag';
        tag.style.cssText = 'display:inline-flex;align-items:center;gap:0.25rem;padding:0.25rem 0.5rem;background:var(--mg-bg-tertiary);border-radius:0.25rem;font-size:0.8rem;';
        tag.innerHTML = escHtml(_selectedMembers[mbId]) + ' <small style="color:var(--mg-text-muted);">' + escHtml(mbId) + '</small>' +
            '<button type="button" onclick="removeMember(\'' + escAttr(mbId) + '\')" style="background:none;border:none;color:var(--mg-error);cursor:pointer;padding:0 2px;font-size:1rem;line-height:1;">&times;</button>';
        container.appendChild(tag);
    });
}

function clearGrantForm() {
    _selectedMembers = {};
    renderSelected();
    document.getElementById('g_ac_id').value = '';
    document.getElementById('g_memo').value = '';
    document.getElementById('g_search').value = '';
    document.getElementById('g_search_results').style.display = 'none';
}

document.getElementById('grant-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var acId = document.getElementById('g_ac_id').value;
    if (!acId) { alert('업적을 선택하세요.'); return; }

    var mbIds = Object.keys(_selectedMembers);
    if (!mbIds.length) { alert('대상 회원을 선택하세요.'); return; }

    if (!confirm(mbIds.length + '명에게 업적을 부여하시겠습니까?')) return;

    var fd = new FormData();
    fd.append('mode', 'grant');
    fd.append('ac_id', acId);
    fd.append('memo', document.getElementById('g_memo').value);
    fd.append('give_reward', document.getElementById('g_give_reward').checked ? '1' : '0');
    mbIds.forEach(function(id) { fd.append('mb_ids[]', id); });

    fetch(_updateUrl, { method: 'POST', body: fd })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            alert(data.message || '부여 완료');
            clearGrantForm();
        } else {
            alert(data.message || '부여 실패');
        }
    })
    .catch(function() { alert('요청 실패'); });
});

function escHtml(str) {
    var d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}
function escAttr(str) {
    return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}
</script>

<?php } ?>

<style>
@media (max-width: 768px) {
    div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>

<?php
require_once __DIR__.'/_tail.php';
?>
