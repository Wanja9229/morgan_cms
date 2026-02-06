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
$allowed_cols = isset($widget_types[$widget['widget_type']]) ? $widget_types[$widget['widget_type']]['allowed_cols'] : array(12);
?>

<div class="mg-form-group">
    <label class="mg-form-label">위젯 타입</label>
    <input type="text" value="<?php echo htmlspecialchars($widget_instance->getName()); ?>" class="mg-form-input" readonly style="background:var(--mg-bg-tertiary);">
</div>

<div class="mg-form-group">
    <label class="mg-form-label">컬럼 너비</label>
    <select name="widget_cols" class="mg-form-select">
        <?php
        $col_labels = array(12 => '전체', 8 => '2/3', 6 => '절반', 4 => '1/3', 3 => '1/4', 2 => '1/6');
        $sorted_cols = array(12, 8, 6, 4, 3, 2);
        foreach ($sorted_cols as $col):
            if (!in_array($col, $allowed_cols)) continue;
        ?>
        <option value="<?php echo $col; ?>" <?php echo $widget['widget_cols'] == $col ? 'selected' : ''; ?>><?php echo $col; ?>칸 (<?php echo $col_labels[$col]; ?>)</option>
        <?php endforeach; ?>
    </select>
</div>

<hr style="border:0;border-top:1px solid var(--mg-bg-tertiary);margin:1rem 0;">

<?php echo $widget_instance->renderConfigForm($config); ?>
