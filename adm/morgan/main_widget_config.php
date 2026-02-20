<?php
/**
 * Morgan Edition - 위젯 설정 폼 (AJAX)
 */

require_once __DIR__.'/../_common.php';

if ($is_admin != 'super') {
    die('<p class="text-mg-error">권한이 없습니다.</p>');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');
include_once(MG_PLUGIN_PATH.'/widgets/widget.factory.php');

$widget_id = (int)$_GET['widget_id'];

if ($widget_id <= 0) {
    die('<p class="text-mg-error">잘못된 요청입니다.</p>');
}

// 위젯 정보 가져오기
$widget = sql_fetch("SELECT * FROM {$g5['mg_main_widget_table']} WHERE widget_id = {$widget_id}");
if (!$widget) {
    die('<p class="text-mg-error">존재하지 않는 위젯입니다.</p>');
}

$config = $widget['widget_config'] ? json_decode($widget['widget_config'], true) : array();

// 위젯 인스턴스 생성
$widget_instance = MG_Widget_Factory::create($widget['widget_type']);
if (!$widget_instance) {
    die('<p class="text-mg-error">위젯을 불러올 수 없습니다.</p>');
}

$widget_types = mg_get_widget_types();
$grid_columns = (int)mg_config('grid_columns', 12);
if ($grid_columns < 1) $grid_columns = 12;
$current_w = (int)($widget['widget_w'] ?: ($widget['widget_cols'] ?? 6));
$current_h = (int)($widget['widget_h'] ?: 2);
?>

<div class="mg-form-group">
    <label class="mg-form-label">위젯 타입</label>
    <input type="text" value="<?php echo htmlspecialchars($widget_instance->getName()); ?>" class="mg-form-input" readonly style="background:var(--mg-bg-tertiary);">
</div>

<div style="display:flex;gap:1rem;">
    <div class="mg-form-group" style="flex:1;">
        <label class="mg-form-label">가로 칸 수 (W) <small style="color:var(--mg-text-muted);">/ <?php echo $grid_columns; ?>칸</small></label>
        <input type="number" name="widget_w" class="mg-form-input" value="<?php echo $current_w; ?>" min="1" max="<?php echo $grid_columns; ?>" style="padding:0.375rem 0.5rem;">
    </div>
    <div class="mg-form-group" style="flex:1;">
        <label class="mg-form-label">세로 칸 수 (H)</label>
        <input type="number" name="widget_h" class="mg-form-input" value="<?php echo $current_h; ?>" min="1" max="40" style="padding:0.375rem 0.5rem;">
    </div>
</div>

<hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1rem 0;">

<?php echo $widget_instance->renderConfigForm($config); ?>
