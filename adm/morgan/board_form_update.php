<?php
/**
 * Morgan Edition - 게시판 추가/수정 처리
 */

$sub_menu = "800180";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

$w = isset($_POST['w']) ? $_POST['w'] : '';
$bo_table = isset($_POST['bo_table']) ? preg_replace('/[^a-z0-9_]/', '', strtolower($_POST['bo_table'])) : '';
$old_bo_table = isset($_POST['old_bo_table']) ? $_POST['old_bo_table'] : '';

$redirect_url = G5_ADMIN_URL.'/morgan/board_list.php';

// 삭제
if ($w === 'd') {
    $bo_table = isset($_POST['bo_table']) ? $_POST['bo_table'] : '';
    if (!$bo_table) {
        alert('게시판을 선택해주세요.');
    }

    $board = sql_fetch("SELECT * FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
    if (!$board['bo_table']) {
        alert('존재하지 않는 게시판입니다.');
    }

    // 게시판 삭제 처리
    include_once(G5_ADMIN_PATH.'/board_delete.inc.php');

    alert('게시판이 삭제되었습니다.', $redirect_url);
}

// 추가/수정
if (!$bo_table) {
    alert('게시판 테이블명을 입력해주세요.');
}

if (strlen($bo_table) > 20) {
    alert('테이블명은 20자 이내로 입력해주세요.');
}

$gr_id = isset($_POST['gr_id']) ? clean_xss_tags($_POST['gr_id']) : '';
if (!$gr_id) {
    alert('그룹을 선택해주세요.');
}

$bo_subject = isset($_POST['bo_subject']) ? clean_xss_tags($_POST['bo_subject']) : '';
if (!$bo_subject) {
    alert('게시판 제목을 입력해주세요.');
}

// 폼 데이터 수집
$fields = array(
    'gr_id' => $gr_id,
    'bo_subject' => $bo_subject,
    'bo_device' => isset($_POST['bo_device']) ? $_POST['bo_device'] : 'both',
    'bo_skin' => isset($_POST['bo_skin']) ? $_POST['bo_skin'] : 'basic',
    'bo_mobile_skin' => isset($_POST['bo_mobile_skin']) ? $_POST['bo_mobile_skin'] : 'basic',
    'bo_admin' => isset($_POST['bo_admin']) ? clean_xss_tags($_POST['bo_admin']) : '',
    'bo_list_level' => (int)($_POST['bo_list_level'] ?? 1),
    'bo_read_level' => (int)($_POST['bo_read_level'] ?? 1),
    'bo_write_level' => (int)($_POST['bo_write_level'] ?? 1),
    'bo_reply_level' => (int)($_POST['bo_reply_level'] ?? 1),
    'bo_comment_level' => (int)($_POST['bo_comment_level'] ?? 1),
    'bo_link_level' => (int)($_POST['bo_link_level'] ?? 1),
    'bo_upload_level' => (int)($_POST['bo_upload_level'] ?? 1),
    'bo_download_level' => (int)($_POST['bo_download_level'] ?? 1),
    'bo_html_level' => (int)($_POST['bo_html_level'] ?? 1),
    'bo_read_point' => (int)($_POST['bo_read_point'] ?? 0),
    'bo_write_point' => (int)($_POST['bo_write_point'] ?? 0),
    'bo_comment_point' => (int)($_POST['bo_comment_point'] ?? 0),
    'bo_download_point' => (int)($_POST['bo_download_point'] ?? 0),
    'bo_use_category' => isset($_POST['bo_use_category']) ? 1 : 0,
    'bo_category_list' => isset($_POST['bo_category_list']) ? clean_xss_tags($_POST['bo_category_list']) : '',
    'bo_use_good' => isset($_POST['bo_use_good']) ? 1 : 0,
    'bo_use_nogood' => isset($_POST['bo_use_nogood']) ? 1 : 0,
    'bo_use_secret' => isset($_POST['bo_use_secret']) ? 1 : 0,
    'bo_use_search' => isset($_POST['bo_use_search']) ? 1 : 0,
    'bo_use_sideview' => isset($_POST['bo_use_sideview']) ? 1 : 0,
    'bo_page_rows' => (int)($_POST['bo_page_rows'] ?? 15),
    'bo_mobile_page_rows' => (int)($_POST['bo_mobile_page_rows'] ?? 15),
    'bo_subject_len' => (int)($_POST['bo_subject_len'] ?? 60),
    'bo_mobile_subject_len' => (int)($_POST['bo_mobile_subject_len'] ?? 30),
    'bo_new' => (int)($_POST['bo_new'] ?? 24),
    'bo_hot' => (int)($_POST['bo_hot'] ?? 100),
    'bo_upload_count' => (int)($_POST['bo_upload_count'] ?? 2),
    'bo_upload_size' => (int)($_POST['bo_upload_size'] ?? 1048576),
    'bo_image_width' => (int)($_POST['bo_image_width'] ?? 600),
    'bo_order' => (int)($_POST['bo_order'] ?? 0),
);

if ($w === 'u' && $old_bo_table) {
    // 수정
    $board = sql_fetch("SELECT * FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($old_bo_table)."'");
    if (!$board['bo_table']) {
        alert('존재하지 않는 게시판입니다.');
    }

    $set_clause = array();
    foreach ($fields as $key => $value) {
        if (is_string($value)) {
            $set_clause[] = "{$key} = '".sql_real_escape_string($value)."'";
        } else {
            $set_clause[] = "{$key} = {$value}";
        }
    }

    sql_query("UPDATE {$g5['board_table']} SET ".implode(', ', $set_clause)." WHERE bo_table = '".sql_real_escape_string($old_bo_table)."'");

    alert('게시판이 수정되었습니다.', './board_form.php?w=u&bo_table='.$old_bo_table);

} else {
    // 추가
    $exists = sql_fetch("SELECT bo_table FROM {$g5['board_table']} WHERE bo_table = '".sql_real_escape_string($bo_table)."'");
    if ($exists['bo_table']) {
        alert('이미 존재하는 게시판 테이블명입니다.');
    }

    // 테이블 생성
    $write_table = $g5['write_prefix'].$bo_table;

    // 그누보드 write 테이블 생성 SQL
    $create_sql = "CREATE TABLE IF NOT EXISTS `{$write_table}` (
        `wr_id` int(11) NOT NULL AUTO_INCREMENT,
        `wr_num` int(11) NOT NULL DEFAULT '0',
        `wr_reply` varchar(10) NOT NULL DEFAULT '',
        `wr_parent` int(11) NOT NULL DEFAULT '0',
        `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
        `wr_comment` int(11) NOT NULL DEFAULT '0',
        `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
        `ca_name` varchar(255) NOT NULL DEFAULT '',
        `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
        `wr_subject` varchar(255) NOT NULL DEFAULT '',
        `wr_content` text NOT NULL,
        `wr_link1` text NOT NULL,
        `wr_link2` text NOT NULL,
        `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
        `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
        `wr_hit` int(11) NOT NULL DEFAULT '0',
        `wr_good` int(11) NOT NULL DEFAULT '0',
        `wr_nogood` int(11) NOT NULL DEFAULT '0',
        `wr_name` varchar(255) NOT NULL DEFAULT '',
        `wr_password` varchar(255) NOT NULL DEFAULT '',
        `wr_email` varchar(255) NOT NULL DEFAULT '',
        `wr_homepage` varchar(255) NOT NULL DEFAULT '',
        `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
        `mb_id` varchar(20) NOT NULL DEFAULT '',
        `wr_ip` varchar(255) NOT NULL DEFAULT '',
        `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
        `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
        `wr_file` tinyint(4) NOT NULL DEFAULT '0',
        `wr_1` varchar(255) NOT NULL DEFAULT '',
        `wr_2` varchar(255) NOT NULL DEFAULT '',
        `wr_3` varchar(255) NOT NULL DEFAULT '',
        `wr_4` varchar(255) NOT NULL DEFAULT '',
        `wr_5` varchar(255) NOT NULL DEFAULT '',
        `wr_6` varchar(255) NOT NULL DEFAULT '',
        `wr_7` varchar(255) NOT NULL DEFAULT '',
        `wr_8` varchar(255) NOT NULL DEFAULT '',
        `wr_9` varchar(255) NOT NULL DEFAULT '',
        `wr_10` varchar(255) NOT NULL DEFAULT '',
        `wr_last` varchar(19) NOT NULL DEFAULT '',
        PRIMARY KEY (`wr_id`),
        KEY `wr_num_reply` (`wr_num`,`wr_reply`),
        KEY `wr_is_comment` (`wr_is_comment`),
        KEY `mb_id` (`mb_id`),
        KEY `wr_parent` (`wr_parent`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

    sql_query($create_sql);

    // 게시판 레코드 추가
    $fields['bo_table'] = $bo_table;

    // 추가 기본값
    $fields['bo_count_modify'] = 1;
    $fields['bo_count_delete'] = 1;
    $fields['bo_table_width'] = 100;
    $fields['bo_gallery_cols'] = 4;
    $fields['bo_gallery_width'] = 200;
    $fields['bo_gallery_height'] = 150;
    $fields['bo_mobile_gallery_width'] = 125;
    $fields['bo_mobile_gallery_height'] = 100;
    $fields['bo_reply_order'] = 1;
    $fields['bo_include_head'] = '_head.php';
    $fields['bo_include_tail'] = '_tail.php';

    $columns = array();
    $values = array();
    foreach ($fields as $key => $value) {
        $columns[] = $key;
        if (is_string($value)) {
            $values[] = "'".sql_real_escape_string($value)."'";
        } else {
            $values[] = $value;
        }
    }

    sql_query("INSERT INTO {$g5['board_table']} (".implode(', ', $columns).") VALUES (".implode(', ', $values).")");

    // 디렉토리 생성
    $board_path = G5_DATA_PATH.'/file/'.$bo_table;
    if (!is_dir($board_path)) {
        @mkdir($board_path, 0755, true);
    }

    alert('게시판이 생성되었습니다.', './board_form.php?w=u&bo_table='.$bo_table);
}
