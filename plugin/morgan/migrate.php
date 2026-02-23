<?php
/**
 * Morgan DB Migration Runner
 *
 * db/migrations/ 폴더의 SQL 파일을 순서대로 자동 적용합니다.
 * 파일명 형식: YYYYMMDD_HHMMSS_description.sql
 * morgan.php에서 세션 기반 캐시로 호출 (파일 수 변경 시만 실행)
 */

if (!defined('_GNUBOARD_')) exit;

function mg_run_migrations() {
    global $g5;

    $table = $g5['mg_migrations_table'];

    // mg_migrations 테이블 부트스트랩
    $check = sql_query("SHOW TABLES LIKE '{$table}'", false);
    if (!$check || !sql_num_rows($check)) {
        sql_query("CREATE TABLE IF NOT EXISTS `{$table}` (
            `mig_id` INT AUTO_INCREMENT PRIMARY KEY,
            `mig_file` VARCHAR(200) NOT NULL UNIQUE,
            `mig_applied_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    // 마이그레이션 디렉토리 스캔
    $mig_dir = G5_PATH . '/db/migrations';
    if (!is_dir($mig_dir)) return;

    $sql_files = glob($mig_dir . '/*.sql');
    $php_files = glob($mig_dir . '/*.php');
    $files = array_merge($sql_files ?: array(), $php_files ?: array());
    if (!$files) return;
    sort($files);

    // 이미 적용된 목록
    $applied = array();
    $result = sql_query("SELECT mig_file FROM `{$table}`");
    while ($row = sql_fetch_array($result)) {
        $applied[] = $row['mig_file'];
    }

    // 미적용 마이그레이션 실행
    foreach ($files as $file) {
        $filename = basename($file);
        if (in_array($filename, $applied)) continue;

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if ($ext === 'php') {
            // PHP 마이그레이션: 파일을 include하여 실행
            $success = true;
            try {
                include($file);
            } catch (Exception $e) {
                $success = false;
                error_log("[Morgan Migration] PHP FAILED: {$filename} — " . $e->getMessage());
            }
        } else {
            // SQL 마이그레이션: mysqli_multi_query로 전체 실행
            // (PREPARE/EXECUTE, ADD COLUMN IF NOT EXISTS 등 복합 구문 지원)
            $sql_content = file_get_contents($file);
            if (!trim($sql_content)) continue;

            $success = true;
            $link = $g5['connect_db'];

            if (@mysqli_multi_query($link, $sql_content)) {
                // 모든 결과 세트를 소비해야 다음 쿼리가 정상 동작
                do {
                    if ($r = mysqli_store_result($link)) {
                        mysqli_free_result($r);
                    }
                } while (mysqli_more_results($link) && mysqli_next_result($link));

                // 마지막 에러 확인
                if (mysqli_errno($link)) {
                    $success = false;
                    error_log("[Morgan Migration] FAILED: {$filename} — " . mysqli_error($link));
                }
            } else {
                $success = false;
                error_log("[Morgan Migration] FAILED: {$filename} — " . mysqli_error($link));
            }
        }

        if ($success) {
            $filename_esc = sql_real_escape_string($filename);
            sql_query("INSERT INTO `{$table}` (mig_file) VALUES ('{$filename_esc}')");
        }
    }
}
