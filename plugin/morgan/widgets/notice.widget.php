<?php
/**
 * Morgan Edition - Notice Widget
 *
 * 공지사항 게시판 글 목록 표시
 */

if (!defined('_GNUBOARD_')) exit;

class MG_Notice_Widget extends MG_Widget_Base {
    protected $type = 'notice';
    protected $name = '공지사항';
    protected $allowed_cols = array(3, 4, 6, 8, 12);
    protected $default_config = array(
        'title' => '공지사항',
        'bo_table' => '',
        'rows' => 5,
        'show_date' => true,
        'show_icon' => true
    );

    public function render($config) {
        $config = array_merge($this->default_config, (array)$config);

        $notices = array();
        if ($config['bo_table']) {
            $notices = $this->getNotices($config['bo_table'], (int)$config['rows']);
        }

        return $this->renderSkin(array(
            'config' => $config,
            'title' => $config['title'],
            'notices' => $notices,
            'bo_table' => $config['bo_table'],
            'show_date' => $config['show_date'],
            'show_icon' => $config['show_icon']
        ));
    }

    private function getNotices($bo_table, $limit = 5) {
        global $g5;

        $bo_table = preg_replace('/[^a-z0-9_]/i', '', $bo_table);
        $write_table = $g5['write_prefix'].$bo_table;

        // 테이블 존재 확인
        $result = sql_query("SHOW TABLES LIKE '{$write_table}'");
        if (!sql_num_rows($result)) {
            return array();
        }

        // 공지글 먼저, 그 다음 최신글
        $sql = "SELECT wr_id, wr_subject, wr_datetime,
                       CASE WHEN wr_option LIKE '%notice%' THEN 1 ELSE 0 END as is_notice
                FROM {$write_table}
                WHERE wr_is_comment = 0
                ORDER BY is_notice DESC, wr_num, wr_reply
                LIMIT {$limit}";
        $result = sql_query($sql);

        $notices = array();
        while ($row = sql_fetch_array($result)) {
            $row['href'] = G5_BBS_URL.'/board.php?bo_table='.$bo_table.'&wr_id='.$row['wr_id'];
            $notices[] = $row;
        }

        return $notices;
    }

    public function renderConfigForm($config) {
        global $g5;

        $config = array_merge($this->default_config, (array)$config);

        // 게시판 목록 가져오기
        $boards = array();
        $sql = "SELECT bo_table, bo_subject FROM {$g5['board_table']} ORDER BY bo_subject";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result)) {
            $boards[] = $row;
        }

        ob_start();
        ?>
        <div class="mg-form-group">
            <label class="mg-form-label">제목</label>
            <input type="text" name="widget_config[title]" value="<?php echo htmlspecialchars($config['title']); ?>" class="mg-form-input">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">게시판 선택</label>
            <select name="widget_config[bo_table]" class="mg-form-select">
                <option value="">선택하세요</option>
                <?php foreach ($boards as $board): ?>
                <option value="<?php echo $board['bo_table']; ?>" <?php echo $config['bo_table'] == $board['bo_table'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($board['bo_subject']); ?> (<?php echo $board['bo_table']; ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">표시 개수</label>
            <input type="number" name="widget_config[rows]" value="<?php echo (int)$config['rows']; ?>" min="1" max="20" class="mg-form-input" style="width:100px;">
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">날짜 표시</label>
            <select name="widget_config[show_date]" class="mg-form-select">
                <option value="1" <?php echo $config['show_date'] ? 'selected' : ''; ?>>표시</option>
                <option value="0" <?php echo !$config['show_date'] ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>
        <div class="mg-form-group">
            <label class="mg-form-label">아이콘 표시</label>
            <select name="widget_config[show_icon]" class="mg-form-select">
                <option value="1" <?php echo $config['show_icon'] ? 'selected' : ''; ?>>표시</option>
                <option value="0" <?php echo !$config['show_icon'] ? 'selected' : ''; ?>>숨김</option>
            </select>
        </div>
        <?php
        return ob_get_clean();
    }
}

// 팩토리에 등록
MG_Widget_Factory::register('notice', 'MG_Notice_Widget');
