<?php
/**
 * Morgan Edition - 파견 이벤트 관리
 */

$sub_menu = "801115";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

global $g5;

// 이벤트 목록
$events = array();
$result = sql_query("SELECT * FROM {$g5['mg_expedition_event_table']} ORDER BY ee_order, ee_id");
while ($row = sql_fetch_array($result)) {
    // 연결된 파견지 수
    $link_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM {$g5['mg_expedition_event_area_table']} WHERE ee_id = {$row['ee_id']}");
    $row['link_count'] = (int)$link_cnt['cnt'];
    $events[] = $row;
}

// 재료 목록 (material_bonus/penalty용)
$material_types = mg_get_material_types();

$g5['title'] = '파견 이벤트';
require_once __DIR__.'/_head.php';
?>

<!-- 안내 -->
<div class="mg-alert mg-alert-info" style="margin-bottom:1rem;">
    파견 이벤트는 파견 완료 시 확률적으로 발동되어 추가 보상이나 패널티를 부여합니다.
    이벤트를 생성한 후 파견지 관리에서 파견지에 매칭하세요.
</div>

<!-- 통계 -->
<div class="mg-stats-grid">
    <div class="mg-stat-card">
        <div class="mg-stat-label">전체 이벤트</div>
        <div class="mg-stat-value"><?php echo count($events); ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">보상 이벤트</div>
        <div class="mg-stat-value" style="color:#10b981;"><?php
            echo count(array_filter($events, function($e) { return in_array($e['ee_effect_type'], array('point_bonus','material_bonus')); }));
        ?></div>
    </div>
    <div class="mg-stat-card">
        <div class="mg-stat-label">패널티 이벤트</div>
        <div class="mg-stat-value" style="color:#ef4444;"><?php
            echo count(array_filter($events, function($e) { return in_array($e['ee_effect_type'], array('point_penalty','material_penalty','reward_loss')); }));
        ?></div>
    </div>
</div>

<!-- 이벤트 추가 -->
<div style="margin-bottom:1rem;text-align:right;">
    <button type="button" class="mg-btn mg-btn-primary" onclick="openEventModal()">이벤트 추가</button>
</div>

<!-- 이벤트 목록 -->
<div class="mg-card">
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:800px;">
            <thead>
                <tr>
                    <th style="width:50px;">순서</th>
                    <th style="width:60px;">아이콘</th>
                    <th style="width:180px;">이벤트명</th>
                    <th>설명</th>
                    <th style="width:120px;">효과</th>
                    <th style="width:100px;">효과 상세</th>
                    <th style="width:80px;">연결</th>
                    <th style="width:110px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)) { ?>
                <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">등록된 이벤트가 없습니다.</td></tr>
                <?php } ?>
                <?php
                $type_labels = array(
                    'point_bonus' => array('포인트 보상', '#10b981'),
                    'point_penalty' => array('포인트 차감', '#ef4444'),
                    'material_bonus' => array('재료 보상', '#3b82f6'),
                    'material_penalty' => array('재료 손실', '#f59e0b'),
                    'reward_loss' => array('보상 감소', '#ef4444'),
                );
                foreach ($events as $ev) {
                    $tl = $type_labels[$ev['ee_effect_type']] ?? array($ev['ee_effect_type'], '#949ba4');
                    $effect = json_decode($ev['ee_effect'], true) ?: array();
                ?>
                <tr>
                    <td style="text-align:center;"><?php echo $ev['ee_order']; ?></td>
                    <td style="text-align:center;"><?php
                        if ($ev['ee_icon']) echo mg_icon($ev['ee_icon'], 'w-5 h-5');
                        else echo '<span style="color:var(--mg-text-muted);">-</span>';
                    ?></td>
                    <td><strong><?php echo htmlspecialchars($ev['ee_name']); ?></strong></td>
                    <td style="font-size:0.85rem;color:var(--mg-text-muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?php echo htmlspecialchars(mb_substr($ev['ee_desc'] ?? '', 0, 50, 'UTF-8')); ?>
                    </td>
                    <td>
                        <span style="font-size:0.75rem;padding:2px 8px;border-radius:4px;background:<?php echo $tl[1]; ?>22;color:<?php echo $tl[1]; ?>;">
                            <?php echo $tl[0]; ?>
                        </span>
                    </td>
                    <td style="font-size:0.8rem;color:var(--mg-text-secondary);">
                        <?php
                        switch ($ev['ee_effect_type']) {
                            case 'point_bonus': echo '+'.number_format($effect['amount'] ?? 0).'P'; break;
                            case 'point_penalty': echo '-'.number_format($effect['amount'] ?? 0).'P'; break;
                            case 'material_bonus':
                                $mt_name = '재료';
                                foreach ($material_types as $mt) { if ($mt['mt_id'] == ($effect['mt_id'] ?? 0)) $mt_name = $mt['mt_name']; }
                                echo $mt_name.' x'.($effect['count'] ?? 0);
                                break;
                            case 'material_penalty':
                                echo '재료 -'.($effect['count'] ?? 0).'개';
                                break;
                            case 'reward_loss':
                                $parts = array();
                                if (!empty($effect['point_loss'])) $parts[] = '-'.$effect['point_loss'].'P';
                                if (!empty($effect['material_loss_pct'])) $parts[] = '재료 -'.$effect['material_loss_pct'].'%';
                                echo implode(', ', $parts) ?: '-';
                                break;
                        }
                        ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ($ev['link_count'] > 0) { ?>
                        <span class="mg-badge"><?php echo $ev['link_count']; ?>곳</span>
                        <?php } else { ?>
                        <span style="font-size:0.75rem;color:var(--mg-text-muted);">없음</span>
                        <?php } ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <div style="display:flex;gap:4px;">
                            <button type="button" class="mg-btn mg-btn-secondary mg-btn-sm" onclick="editEvent(<?php echo $ev['ee_id']; ?>)">수정</button>
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteEvent(<?php echo $ev['ee_id']; ?>)">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- 이벤트 모달 -->
<div id="event-modal" class="mg-modal" style="display:none;">
    <div class="mg-modal-content" style="max-width:600px;">
        <div class="mg-modal-header">
            <h3 id="modal-title">이벤트 추가</h3>
            <button type="button" class="mg-modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form id="event-form" method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/expedition_event_update.php" enctype="multipart/form-data">
            <input type="hidden" name="w" id="form_w" value="">
            <input type="hidden" name="ee_id" id="form_ee_id" value="">

            <div class="mg-modal-body">
                <div class="mg-form-group">
                    <label class="mg-form-label">이벤트명 *</label>
                    <input type="text" name="ee_name" id="ee_name" class="mg-form-input" required placeholder="예: 금화 주머니 발견!">
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">설명 (유저에게 표시)</label>
                    <textarea name="ee_desc" id="ee_desc" class="mg-form-input" rows="2" placeholder="이벤트 발동 시 유저에게 보여줄 텍스트"></textarea>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">아이콘</label>
                        <?php mg_icon_input('ee_icon', '', array('delete_name' => 'del_icon', 'placeholder' => 'gift, fire 등')); ?>
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">정렬 순서</label>
                        <input type="number" name="ee_order" id="ee_order" class="mg-form-input" value="0">
                    </div>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">효과 유형 *</label>
                    <select name="ee_effect_type" id="ee_effect_type" class="mg-form-select" onchange="toggleEffectFields()">
                        <option value="point_bonus">포인트 보상 (+)</option>
                        <option value="point_penalty">포인트 차감 (-)</option>
                        <option value="material_bonus">추가 재료 보상</option>
                        <option value="material_penalty">재료 손실</option>
                        <option value="reward_loss">보상 전체 감소</option>
                    </select>
                </div>

                <!-- 효과 필드: 포인트 -->
                <div id="effect-point" class="mg-form-group">
                    <label class="mg-form-label">포인트 수치</label>
                    <input type="number" name="effect_amount" id="effect_amount" class="mg-form-input" min="1" value="100" placeholder="증감할 포인트">
                </div>

                <!-- 효과 필드: 재료 보상 -->
                <div id="effect-material" class="mg-form-group" style="display:none;">
                    <label class="mg-form-label">재료</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <select name="effect_mt_id" id="effect_mt_id" class="mg-form-select" style="flex:1;">
                            <?php foreach ($material_types as $mt) { ?>
                            <option value="<?php echo $mt['mt_id']; ?>"><?php echo htmlspecialchars($mt['mt_name']); ?></option>
                            <?php } ?>
                        </select>
                        <span style="color:var(--mg-text-muted);">x</span>
                        <input type="number" name="effect_count" id="effect_count" class="mg-form-input" min="1" value="1" style="width:80px;">
                    </div>
                </div>

                <!-- 효과 필드: 재료 손실 -->
                <div id="effect-mat-penalty" class="mg-form-group" style="display:none;">
                    <label class="mg-form-label">손실 재료 개수 (획득한 보상 중 랜덤)</label>
                    <input type="number" name="effect_loss_count" id="effect_loss_count" class="mg-form-input" min="1" value="1">
                </div>

                <!-- 효과 필드: 보상 감소 -->
                <div id="effect-reward-loss" style="display:none;">
                    <div class="mg-form-group">
                        <label class="mg-form-label">포인트 차감</label>
                        <input type="number" name="effect_point_loss" id="effect_point_loss" class="mg-form-input" min="0" value="0">
                    </div>
                    <div class="mg-form-group">
                        <label class="mg-form-label">재료 보상 감소율 (%)</label>
                        <input type="number" name="effect_material_loss_pct" id="effect_material_loss_pct" class="mg-form-input" min="0" max="100" value="50">
                    </div>
                </div>
            </div>

            <div class="mg-modal-footer">
                <button type="button" class="mg-btn mg-btn-secondary" onclick="closeModal()">취소</button>
                <button type="submit" class="mg-btn mg-btn-primary">저장</button>
            </div>
        </form>
    </div>
</div>

<script>
var events = <?php echo json_encode($events); ?>;

function toggleEffectFields() {
    var type = document.getElementById('ee_effect_type').value;
    document.getElementById('effect-point').style.display = (type === 'point_bonus' || type === 'point_penalty') ? '' : 'none';
    document.getElementById('effect-material').style.display = (type === 'material_bonus') ? '' : 'none';
    document.getElementById('effect-mat-penalty').style.display = (type === 'material_penalty') ? '' : 'none';
    document.getElementById('effect-reward-loss').style.display = (type === 'reward_loss') ? '' : 'none';
}

function openEventModal() {
    document.getElementById('modal-title').textContent = '이벤트 추가';
    document.getElementById('form_w').value = '';
    document.getElementById('form_ee_id').value = '';
    document.getElementById('event-form').reset();
    mgIconReset('ee_icon');
    toggleEffectFields();
    document.getElementById('event-modal').style.display = 'flex';
}

function editEvent(ee_id) {
    var ev = events.find(function(e) { return e.ee_id == ee_id; });
    if (!ev) return;

    document.getElementById('modal-title').textContent = '이벤트 수정';
    document.getElementById('form_w').value = 'u';
    document.getElementById('form_ee_id').value = ee_id;
    document.getElementById('ee_name').value = ev.ee_name;
    document.getElementById('ee_desc').value = ev.ee_desc || '';
    document.getElementById('ee_order').value = ev.ee_order;
    document.getElementById('ee_effect_type').value = ev.ee_effect_type;
    mgIconSet('ee_icon', ev.ee_icon || '');

    var effect = {};
    try { effect = JSON.parse(ev.ee_effect) || {}; } catch(e) {}

    // 효과 필드 채우기
    document.getElementById('effect_amount').value = effect.amount || 100;
    document.getElementById('effect_mt_id').value = effect.mt_id || '';
    document.getElementById('effect_count').value = effect.count || 1;
    document.getElementById('effect_loss_count').value = effect.count || 1;
    document.getElementById('effect_point_loss').value = effect.point_loss || 0;
    document.getElementById('effect_material_loss_pct').value = effect.material_loss_pct || 50;

    toggleEffectFields();
    document.getElementById('event-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('event-modal').style.display = 'none';
}

function deleteEvent(ee_id) {
    if (!confirm('이 이벤트를 삭제하시겠습니까?\n파견지에 연결된 매칭도 함께 삭제됩니다.')) return;
    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/expedition_event_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="ee_id" value="' + ee_id + '">';
    document.body.appendChild(form);
    form.submit();
}

// 모달 외부 클릭 닫기
document.getElementById('event-modal').addEventListener('click', function(e) {
    if (e.target === this && document._mgMdTarget === this) closeModal();
});
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
