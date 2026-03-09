<?php
/**
 * Morgan Edition - 훈련 과정 등록/수정 폼
 */

$sub_menu = "801930";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_PATH.'/plugin/morgan/morgan.php');

global $g5;

$tc_id = isset($_GET['tc_id']) ? (int)$_GET['tc_id'] : 0;
$w = $tc_id > 0 ? 'u' : '';

// 기본값
$tc = array(
    'tc_id'         => 0,
    'tc_name'       => '',
    'tc_desc'       => '',
    'tc_icon'       => '',
    'tc_stat'       => 'none',
    'tc_stat_amount'=> 1,
    'tc_required'   => 1,
    'tc_cost'       => 0,
    'tc_stress'     => 0,
    'tc_max_repeat' => 0,
    'tc_order'      => 0,
    'tc_use'        => 1,
);

if ($tc_id > 0) {
    $row = sql_fetch("SELECT * FROM {$g5['mg_training_class_table']} WHERE tc_id = {$tc_id}");
    if ($row && $row['tc_id']) {
        $tc = $row;
    } else {
        alert('훈련 과정을 찾을 수 없습니다.', G5_ADMIN_URL.'/morgan/training_class.php');
    }
}

// 스탯 옵션
$stat_options = array(
    'none'     => '없음',
    'stat_hp'  => 'HP',
    'stat_str' => '근력(STR)',
    'stat_dex' => '민첩(DEX)',
    'stat_int' => '지능(INT)',
    'stat_con' => '체력(CON)',
    'stat_luk' => '행운(LUK)',
);

$g5['title'] = $w === 'u' ? '훈련 과정 수정' : '훈련 과정 등록';
require_once __DIR__.'/_head.php';
?>

<div class="mg-card">
    <div class="mg-card-header">
        <h3 class="mg-card-title"><?php echo $w === 'u' ? '훈련 과정 수정' : '훈련 과정 등록'; ?></h3>
    </div>
    <div class="mg-card-body">
        <form method="post" action="<?php echo G5_ADMIN_URL; ?>/morgan/training_class_update.php">
            <input type="hidden" name="w" value="<?php echo $w; ?>">
            <input type="hidden" name="tc_id" value="<?php echo (int)$tc['tc_id']; ?>">

            <div class="mg-form-group">
                <label class="mg-form-label">이름 *</label>
                <input type="text" name="tc_name" class="mg-form-input" value="<?php echo htmlspecialchars($tc['tc_name'] ?? ''); ?>" required placeholder="예: 검술 훈련">
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">설명</label>
                <textarea name="tc_desc" class="mg-form-input" rows="3" placeholder="훈련 과정에 대한 설명"><?php echo htmlspecialchars($tc['tc_desc'] ?? ''); ?></textarea>
            </div>

            <div class="mg-form-group">
                <label class="mg-form-label">아이콘</label>
                <?php mg_game_icon_picker('tc_icon', $tc['tc_icon'] ?? '', array('color' => $tc['tc_icon_color'] ?? '#ffffff')); ?>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">스탯 *</label>
                    <select name="tc_stat" class="mg-form-select">
                        <?php foreach ($stat_options as $val => $label) { ?>
                        <option value="<?php echo $val; ?>" <?php echo ($tc['tc_stat'] ?? 'none') === $val ? 'selected' : ''; ?>><?php echo $label; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">스탯 증가량</label>
                    <input type="number" name="tc_stat_amount" class="mg-form-input" value="<?php echo (int)$tc['tc_stat_amount']; ?>" min="0">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">필요 횟수</label>
                    <input type="number" name="tc_required" class="mg-form-input" value="<?php echo (int)$tc['tc_required']; ?>" min="1">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">스탯 증가에 필요한 수강 횟수</p>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">수강료 (포인트)</label>
                    <input type="number" name="tc_cost" class="mg-form-input" value="<?php echo (int)$tc['tc_cost']; ?>" min="0">
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">스트레스</label>
                    <input type="number" name="tc_stress" class="mg-form-input" value="<?php echo (int)$tc['tc_stress']; ?>">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">음수 = 스트레스 감소 (명상 등)</p>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="mg-form-group">
                    <label class="mg-form-label">반복 제한</label>
                    <input type="number" name="tc_max_repeat" class="mg-form-input" value="<?php echo (int)$tc['tc_max_repeat']; ?>" min="0">
                    <p style="margin-top:0.25rem;font-size:0.85rem;color:var(--mg-text-muted);">0 = 무제한</p>
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">정렬 순서</label>
                    <input type="number" name="tc_order" class="mg-form-input" value="<?php echo (int)$tc['tc_order']; ?>">
                </div>

                <div class="mg-form-group">
                    <label class="mg-form-label">사용 여부</label>
                    <select name="tc_use" class="mg-form-select">
                        <option value="1" <?php echo (int)$tc['tc_use'] === 1 ? 'selected' : ''; ?>>사용</option>
                        <option value="0" <?php echo (int)$tc['tc_use'] === 0 ? 'selected' : ''; ?>>미사용</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:1.5rem;display:flex;gap:0.5rem;">
                <button type="submit" class="mg-btn mg-btn-primary"><?php echo $w === 'u' ? '수정' : '등록'; ?></button>
                <a href="<?php echo G5_ADMIN_URL; ?>/morgan/training_class.php" class="mg-btn mg-btn-secondary">목록으로</a>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__.'/_tail.php';
?>
