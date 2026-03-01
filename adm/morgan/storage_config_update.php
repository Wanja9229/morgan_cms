<?php
/**
 * Morgan Edition - 스토리지 설정 저장
 */

$sub_menu = "800102";
require_once __DIR__.'/../_common.php';

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

// Morgan 플러그인 로드
include_once(G5_PATH.'/plugin/morgan/morgan.php');

// 저장할 설정 항목
$config_keys = array(
    'mg_storage_driver',
    'mg_r2_account_id',
    'mg_r2_access_key_id',
    'mg_r2_secret_access_key',
    'mg_r2_bucket_name',
    'mg_r2_endpoint',
    'mg_r2_public_url',
);

foreach ($config_keys as $key) {
    if (!isset($_POST[$key])) continue;
    $value = trim($_POST[$key]);

    $sql = "SELECT COUNT(*) as cnt FROM {$g5['mg_config_table']} WHERE cf_key = '".sql_escape_string($key)."'";
    $row = sql_fetch($sql);
    $cnt = isset($row['cnt']) ? (int)$row['cnt'] : 0;

    if ($cnt > 0) {
        sql_query("UPDATE {$g5['mg_config_table']} SET cf_value = '".sql_escape_string($value)."' WHERE cf_key = '".sql_escape_string($key)."'");
    } else {
        sql_query("INSERT INTO {$g5['mg_config_table']} (cf_key, cf_value) VALUES ('".sql_escape_string($key)."', '".sql_escape_string($value)."')");
    }
}

// 드라이버 변경 시 싱글턴 리셋
require_once(G5_PLUGIN_PATH.'/morgan/storage/MG_Storage.php');
MG_Storage::reset();

goto_url('./storage_config.php');
