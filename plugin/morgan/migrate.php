<?php
/**
 * Morgan DB Migration Runner
 *
 * db/migrations/ — 메인 테넌트 전용 마이그레이션 (DDL + 시드)
 * db/patches/    — 모든 테넌트 공통 데이터 패치
 *
 * 파일명 형식: YYYYMMDD_HHMMSS_description.sql (또는 .php)
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

    // 이미 적용된 목록
    $applied = array();
    $result = sql_query("SELECT mig_file FROM `{$table}`");
    while ($row = sql_fetch_array($result)) {
        $applied[] = $row['mig_file'];
    }

    // --- 1) db/migrations/ (메인 테넌트 전용) ---
    $mig_dir = G5_PATH . '/db/migrations';
    if (is_dir($mig_dir)) {
        $files = _mg_scan_migration_dir($mig_dir);
        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $applied)) continue;

            // 마스터 DB 전용 파일 스킵
            if (stripos($filename, 'master') !== false) continue;

            _mg_execute_migration($file, $table);
        }
    }

    // --- 2) db/patches/ (모든 테넌트 공통) ---
    $patch_dir = G5_PATH . '/db/patches';
    if (is_dir($patch_dir)) {
        $files = _mg_scan_migration_dir($patch_dir);
        foreach ($files as $file) {
            $filename = basename($file);
            if (in_array($filename, $applied)) continue;

            _mg_execute_migration($file, $table);
        }
    }
}

/**
 * 디렉토리에서 .sql + .php 파일 스캔 (정렬)
 */
function _mg_scan_migration_dir($dir) {
    $sql_files = glob($dir . '/*.sql') ?: array();
    $php_files = glob($dir . '/*.php') ?: array();
    $files = array_merge($sql_files, $php_files);
    sort($files);
    return $files;
}

/**
 * 마이그레이션/패치 파일 1개 실행 + 적용 기록
 */
function _mg_execute_migration($file, $table) {
    global $g5;

    $filename = basename($file);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if ($ext === 'php') {
        // PHP 마이그레이션: 파일을 include하여 실행
        try {
            include($file);
        } catch (Exception $e) {
            error_log("[Morgan Migration] PHP FAILED: {$filename} — " . $e->getMessage());
        }
    } else {
        // SQL 마이그레이션: mysqli_multi_query로 전체 실행
        $sql_content = file_get_contents($file);
        if (!trim($sql_content)) {
            // 빈 파일은 적용 완료로 기록만
            $filename_esc = sql_real_escape_string($filename);
            sql_query("INSERT IGNORE INTO `{$table}` (mig_file) VALUES ('{$filename_esc}')");
            return;
        }

        $errors = array();
        $link = $g5['connect_db'];

        if (@mysqli_multi_query($link, $sql_content)) {
            $stmt_idx = 0;
            do {
                if ($r = mysqli_store_result($link)) {
                    mysqli_free_result($r);
                }
                if (mysqli_errno($link)) {
                    $errors[] = "stmt[{$stmt_idx}]: " . mysqli_error($link);
                }
                $stmt_idx++;
            } while (mysqli_more_results($link) && mysqli_next_result($link));
        } else {
            error_log("[Morgan Migration] FAILED: {$filename} — " . mysqli_error($link));
        }

        if ($errors) {
            error_log("[Morgan Migration] PARTIAL ERRORS in {$filename}: " . implode(' | ', $errors));
        }
    }

    // 항상 기록 — 멱등성 보장 전제
    $filename_esc = sql_real_escape_string($filename);
    sql_query("INSERT IGNORE INTO `{$table}` (mig_file) VALUES ('{$filename_esc}')");
}

/**
 * 마이그레이션+패치 파일 총 수 반환 (세션 캐시 판단용)
 */
function mg_count_migration_files() {
    $count = 0;
    $mig_dir = G5_PATH . '/db/migrations';
    if (is_dir($mig_dir)) {
        $count += count(glob($mig_dir . '/*.sql') ?: array());
        $count += count(glob($mig_dir . '/*.php') ?: array());
    }
    $patch_dir = G5_PATH . '/db/patches';
    if (is_dir($patch_dir)) {
        $count += count(glob($patch_dir . '/*.sql') ?: array());
        $count += count(glob($patch_dir . '/*.php') ?: array());
    }
    return $count;
}
