<?php
/**
 * 로드비 스킨 공통 헤더
 *
 * list.skin.php 상단에서 include
 * 제공: $comments_map, $lb_characters, $lb_main_ch_id, 에디터 CDN 출력
 */

if (!defined('_GNUBOARD_')) exit;

include_once(G5_PATH.'/plugin/morgan/morgan.php');

$write_table = $g5['write_prefix'] . $bo_table;

// 댓글 일괄 로드 (N+1 방지)
$wr_ids = array_map(function($r){ return (int)$r['wr_id']; }, $list);
$comments_map = array();
if (count($wr_ids) > 0) {
    $ids_in = implode(',', $wr_ids);
    $cmt_result = sql_query("SELECT * FROM {$write_table} WHERE wr_parent IN ({$ids_in}) AND wr_is_comment = 1 ORDER BY wr_datetime ASC");
    if ($cmt_result !== false) {
        while ($cmt = sql_fetch_array($cmt_result)) {
            $comments_map[(int)$cmt['wr_parent']][] = $cmt;
        }
    }
}

// 캐릭터 목록 (글쓰기 모달용)
$lb_characters = array();
$lb_main_ch_id = 0;
if ($is_member && function_exists('mg_get_usable_characters')) {
    $lb_characters = mg_get_usable_characters($member['mb_id']);
    foreach ($lb_characters as $ch) {
        if ($ch['ch_main']) { $lb_main_ch_id = $ch['ch_id']; break; }
    }
}

// 에디터 nonce
$lb_ed_nonce = '';
if (function_exists('ft_nonce_create')) {
    $lb_ed_nonce = ft_nonce_create('toastui');
} else {
    // editor.lib.php에서 ft_nonce_create 로드
    $editor_lib = G5_PATH.'/plugin/editor/'.$config['cf_editor'].'/editor.lib.php';
    if (file_exists($editor_lib)) {
        include_once($editor_lib);
        if (function_exists('ft_nonce_create')) {
            $lb_ed_nonce = ft_nonce_create('toastui');
        }
    }
}

$lb_upload_url = G5_EDITOR_URL.'/'.$config['cf_editor'].'/imageUpload/upload.php?_nonce='.urlencode($lb_ed_nonce);
