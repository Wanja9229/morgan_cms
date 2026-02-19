<?php
/**
 * Morgan Edition - Mission Calendar Widget
 *
 * 미션 달력 위젯: 월별 미션 기간을 달력으로 표시
 */

if (!defined('_GNUBOARD_')) exit;

class MG_Calendar_Widget extends MG_Widget_Base {
    protected $type = 'calendar';
    protected $name = '미션 달력';
    protected $allowed_cols = array(6, 8, 12);
    protected $default_config = array(
        'title' => '미션 달력',
        'show_closed' => true
    );

    public function render($config) {
        $config = array_merge($this->default_config, (array)$config);

        $missions = $this->getMissions($config['show_closed']);

        return $this->renderSkin(array(
            'config' => $config,
            'title' => $config['title'],
            'missions' => $missions
        ));
    }

    /**
     * 이번 달 + 다음 달 미션 조회
     */
    private function getMissions($show_closed = true) {
        global $g5;

        $first_day = date('Y-m-01');
        $last_day = date('Y-m-t', strtotime('+1 month'));

        $status_cond = $show_closed ? "IN ('active', 'closed')" : "= 'active'";

        $sql = "SELECT pm_id, pm_title, pm_cycle, pm_mode, pm_status,
                    pm_start_date, pm_end_date, pm_point, pm_bonus_point, pm_tags,
                    bo_table
                FROM {$g5['mg_prompt_table']}
                WHERE pm_status {$status_cond}
                AND (
                    (pm_start_date BETWEEN '{$first_day}' AND '{$last_day}')
                    OR (pm_end_date BETWEEN '{$first_day}' AND '{$last_day}')
                    OR (pm_start_date <= '{$first_day}' AND pm_end_date >= '{$last_day}')
                )
                ORDER BY pm_start_date ASC";
        $result = sql_query($sql);
        $list = array();
        while ($row = sql_fetch_array($result)) {
            $list[] = array(
                'pm_id' => (int)$row['pm_id'],
                'title' => $row['pm_title'],
                'cycle' => $row['pm_cycle'],
                'status' => $row['pm_status'],
                'start' => $row['pm_start_date'] ? date('Y-m-d', strtotime($row['pm_start_date'])) : '',
                'end' => $row['pm_end_date'] ? date('Y-m-d', strtotime($row['pm_end_date'])) : '',
                'point' => (int)$row['pm_point'],
                'bonus' => (int)$row['pm_bonus_point'],
                'bo_table' => $row['bo_table'],
            );
        }
        return $list;
    }

    public function renderConfigForm($config) {
        $config = array_merge($this->default_config, (array)$config);
        ob_start();
        ?>
        <div class="mg-form-group">
            <label class="mg-form-label">제목</label>
            <input type="text" name="widget_config[title]" value="<?php echo htmlspecialchars($config['title']); ?>" class="mg-form-input">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">종료된 미션 표시</label>
            <select name="widget_config[show_closed]" class="mg-form-select">
                <option value="1" <?php echo $config['show_closed'] ? 'selected' : ''; ?>>표시</option>
                <option value="0" <?php echo !$config['show_closed'] ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>
        <?php
        return ob_get_clean();
    }
}

// 팩토리에 등록
MG_Widget_Factory::register('calendar', 'MG_Calendar_Widget');
