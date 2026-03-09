<?php
/**
 * Morgan Edition - 훈련 과정 관리
 */

$sub_menu = "801930";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

global $g5;

// 훈련 과정 목록
$list = array();
$result = sql_query("SELECT * FROM {$g5['mg_training_class_table']} ORDER BY tc_order, tc_id");
if ($result) {
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
}

// 스탯 라벨
$stat_labels = array(
    'none'     => '없음',
    'stat_hp'  => 'HP',
    'stat_str' => '근력(STR)',
    'stat_dex' => '민첩(DEX)',
    'stat_int' => '지능(INT)',
    'stat_con' => '체력(CON)',
    'stat_luk' => '행운(LUK)',
);

$g5['title'] = '훈련 과정 관리';
require_once __DIR__.'/_head.php';
?>

<!-- 추가 버튼 -->
<div style="margin-bottom:1rem;text-align:right;">
    <a href="<?php echo G5_ADMIN_URL; ?>/morgan/training_class_form.php" class="mg-btn mg-btn-primary">훈련 과정 추가</a>
</div>

<!-- 훈련 과정 목록 -->
<div class="mg-card">
    <div class="mg-card-header">
        <h3 class="mg-card-title">훈련 과정 목록</h3>
    </div>
    <div class="mg-card-body" style="padding:0;overflow-x:auto;">
        <table class="mg-table" style="min-width:900px;table-layout:fixed;">
            <thead>
                <tr>
                    <th style="width:55px;">순서</th>
                    <th style="width:150px;">이름</th>
                    <th style="width:100px;">스탯</th>
                    <th style="width:70px;">증가량</th>
                    <th style="width:80px;">필요횟수</th>
                    <th style="width:80px;">수강료</th>
                    <th style="width:80px;">스트레스</th>
                    <th style="width:80px;">반복제한</th>
                    <th style="width:60px;">사용</th>
                    <th style="width:120px;">관리</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $tc) { ?>
                <tr>
                    <td style="text-align:center;"><?php echo (int)$tc['tc_order']; ?></td>
                    <td>
                        <?php if ($tc['tc_icon']) { echo mg_icon($tc['tc_icon'], 'w-5 h-5') . ' '; } ?>
                        <strong><?php echo htmlspecialchars($tc['tc_name'] ?? ''); ?></strong>
                    </td>
                    <td style="text-align:center;">
                        <?php echo isset($stat_labels[$tc['tc_stat']]) ? $stat_labels[$tc['tc_stat']] : htmlspecialchars($tc['tc_stat'] ?? ''); ?>
                    </td>
                    <td style="text-align:center;"><?php echo (int)$tc['tc_stat_amount']; ?></td>
                    <td style="text-align:center;"><?php echo (int)$tc['tc_required']; ?></td>
                    <td style="text-align:center;"><?php echo number_format((int)$tc['tc_cost']); ?></td>
                    <td style="text-align:center;">
                        <?php
                        $stress = (int)$tc['tc_stress'];
                        if ($stress > 0) {
                            echo '<span style="color:#ef4444;">+' . $stress . '</span>';
                        } elseif ($stress < 0) {
                            echo '<span style="color:#22c55e;">' . $stress . '</span>';
                        } else {
                            echo '0';
                        }
                        ?>
                    </td>
                    <td style="text-align:center;">
                        <?php echo (int)$tc['tc_max_repeat'] > 0 ? (int)$tc['tc_max_repeat'] . '회' : '<span style="color:var(--mg-text-muted);">무제한</span>'; ?>
                    </td>
                    <td style="text-align:center;">
                        <?php if ((int)$tc['tc_use']) { ?>
                            <span class="mg-badge mg-badge-success">사용</span>
                        <?php } else { ?>
                            <span class="mg-badge">미사용</span>
                        <?php } ?>
                    </td>
                    <td style="white-space:nowrap;">
                        <div style="display:flex;gap:4px;flex-wrap:nowrap;">
                            <a href="<?php echo G5_ADMIN_URL; ?>/morgan/training_class_form.php?tc_id=<?php echo $tc['tc_id']; ?>" class="mg-btn mg-btn-secondary mg-btn-sm">수정</a>
                            <button type="button" class="mg-btn mg-btn-danger mg-btn-sm" onclick="deleteClass(<?php echo $tc['tc_id']; ?>)">삭제</button>
                        </div>
                    </td>
                </tr>
                <?php } ?>
                <?php if (empty($list)) { ?>
                <tr>
                    <td colspan="10" style="text-align:center;padding:3rem;color:var(--mg-text-muted);">
                        등록된 훈련 과정이 없습니다.
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function deleteClass(tc_id) {
    if (!confirm('이 훈련 과정을 삭제하시겠습니까?')) return;

    var form = document.createElement('form');
    form.method = 'post';
    form.action = '<?php echo G5_ADMIN_URL; ?>/morgan/training_class_update.php';
    form.innerHTML = '<input type="hidden" name="w" value="d"><input type="hidden" name="tc_id" value="' + tc_id + '">';
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php
require_once __DIR__.'/_tail.php';
?>
