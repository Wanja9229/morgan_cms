<?php
/**
 * Morgan Tenant Manager — 프로비저닝 엔진
 *
 * 테넌트 생성/정지/활성화/삭제를 처리한다.
 * 슈퍼 관리자 패널(adm/super/)에서 호출.
 */

if (!defined('_GNUBOARD_')) exit;

class TenantManager
{
    /** @var mysqli 마스터 DB 연결 */
    private $masterLink;

    /** @var mysqli|null 프로비저닝 전용 연결 (CREATE DATABASE 권한) */
    private $provisionLink = null;

    /** @var array 오류 메시지 */
    private $errors = array();

    /** @var array 프로비저닝 로그 */
    private $log = array();

    public function __construct($masterLink)
    {
        $this->masterLink = $masterLink;
    }

    /**
     * 테넌트 프로비저닝 (전체 자동화)
     *
     * @param string $subdomain    서브도메인
     * @param string $name         커뮤니티 이름
     * @param string $adminEmail   관리자 이메일
     * @param string $adminId      관리자 아이디
     * @param string $plan         플랜 (free/basic/pro)
     * @return array|false         성공 시 ['tenant_id'=>int, 'admin_password'=>string], 실패 시 false
     */
    public function provision($subdomain, $name, $adminEmail, $adminId = 'admin', $plan = 'free')
    {
        $this->errors = array();
        $this->log = array();

        // 1. 유효성 검사
        if (!$this->validateSubdomain($subdomain)) {
            return false;
        }

        // 2. DB 자격증명 생성
        $dbName = 'mg_t_' . preg_replace('/[^a-z0-9]/', '_', $subdomain);
        $dbUser = 'mg_t_' . substr(md5($subdomain . microtime()), 0, 8);
        $dbPass = bin2hex(random_bytes(16));
        $this->addLog('DB 자격증명 생성: ' . $dbName);

        // 3. 프로비저닝 DB 연결
        if (!$this->getProvisionLink()) {
            $this->errors[] = '프로비저닝 DB 연결 실패';
            return false;
        }

        // 4. DB + 사용자 생성
        if (!$this->createDatabase($dbName, $dbUser, $dbPass)) {
            return false;
        }

        // 5. 테넌트 DB 연결
        $dbHost = defined('MG_PROVISION_DB_HOST') ? MG_PROVISION_DB_HOST : MG_MASTER_DB_HOST;
        $tenantLink = @mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
        if (!$tenantLink) {
            $this->errors[] = '테넌트 DB 연결 실패: ' . mysqli_connect_error();
            return false;
        }
        mysqli_set_charset($tenantLink, 'utf8mb4');
        // sql_mode 비우기 (gnuboard 호환)
        mysqli_query($tenantLink, "SET SESSION sql_mode = ''");

        // 6. gnuboard 스키마 로드
        $gnuboardSql = G5_PATH . '/install/gnuboard5.sql';
        if (!$this->loadSqlFile($tenantLink, $gnuboardSql, true)) {
            mysqli_close($tenantLink);
            return false;
        }
        $this->addLog('gnuboard 스키마 로드 완료');

        // 7. Morgan 스키마 로드
        $morganSql = G5_PLUGIN_PATH . '/morgan/install/install.sql';
        if (!$this->loadSqlFile($tenantLink, $morganSql)) {
            mysqli_close($tenantLink);
            return false;
        }
        $this->addLog('Morgan 스키마 로드 완료');

        // 8. 시드 데이터 로드
        $seedSql = G5_PLUGIN_PATH . '/morgan/install/seed.sql';
        if (file_exists($seedSql)) {
            if (!$this->loadSqlFile($tenantLink, $seedSql, false, array(
                '{ADMIN_ID}'    => $adminId,
                '{ADMIN_EMAIL}' => $adminEmail,
            ))) {
                mysqli_close($tenantLink);
                return false;
            }
            $this->addLog('시드 데이터 로드 완료');
        }

        // 9. 마이그레이션 실행
        $migrationCount = $this->runMigrations($tenantLink);
        $this->addLog("마이그레이션 {$migrationCount}개 실행 완료");

        // 10. write 테이블 생성 (게시판용)
        $this->createWriteTables($tenantLink);
        $this->addLog('write 테이블 생성 완료');

        // 11. 관리자 계정 생성
        $adminPass = $this->createAdminAccount($tenantLink, $adminId, $adminEmail);
        if (!$adminPass) {
            mysqli_close($tenantLink);
            return false;
        }
        $this->addLog('관리자 계정 생성 완료');

        mysqli_close($tenantLink);

        // 12. 마스터 DB에 테넌트 레코드 INSERT
        $tenantId = $this->insertTenantRecord($subdomain, $name, $dbName, $dbUser, $dbPass, $adminEmail, $plan);
        if (!$tenantId) {
            return false;
        }
        $this->addLog('마스터 DB 레코드 생성: tenant_id=' . $tenantId);

        // 13. 파일 디렉토리 생성
        $this->createDirectories($tenantId);
        $this->addLog('파일 디렉토리 생성 완료');

        // 14. 프로비저닝 로그 기록
        $this->logProvision($tenantId, 'create', '프로비저닝 완료: ' . $subdomain);

        // 15. 테넌트 캐시 무효화
        $this->invalidateCache($subdomain);

        return array(
            'tenant_id'      => $tenantId,
            'admin_id'       => $adminId,
            'admin_password' => $adminPass,
            'db_name'        => $dbName,
        );
    }

    /**
     * 테넌트 정지
     */
    public function suspend($tenantId, $reason = '')
    {
        $id = (int)$tenantId;
        $esc_reason = mysqli_real_escape_string($this->masterLink, $reason);
        $result = mysqli_query($this->masterLink,
            "UPDATE tenants SET status='suspended', suspended_reason='{$esc_reason}' WHERE id={$id} AND status='active'"
        );

        if ($result && mysqli_affected_rows($this->masterLink) > 0) {
            $this->logProvision($id, 'suspend', $reason);
            $this->invalidateCacheById($id);
            return true;
        }
        $this->errors[] = '정지 처리 실패';
        return false;
    }

    /**
     * 테넌트 활성화
     */
    public function activate($tenantId)
    {
        $id = (int)$tenantId;
        $result = mysqli_query($this->masterLink,
            "UPDATE tenants SET status='active', suspended_reason=NULL WHERE id={$id} AND status='suspended'"
        );

        if ($result && mysqli_affected_rows($this->masterLink) > 0) {
            $this->logProvision($id, 'activate', '활성화');
            $this->invalidateCacheById($id);
            return true;
        }
        $this->errors[] = '활성화 처리 실패';
        return false;
    }

    /**
     * 테넌트 소프트 삭제 (DB는 유지, status만 변경)
     */
    public function softDelete($tenantId)
    {
        $id = (int)$tenantId;
        $result = mysqli_query($this->masterLink,
            "UPDATE tenants SET status='deleted' WHERE id={$id} AND status != 'deleted'"
        );

        if ($result && mysqli_affected_rows($this->masterLink) > 0) {
            $this->logProvision($id, 'delete', '소프트 삭제');
            $this->invalidateCacheById($id);
            return true;
        }
        $this->errors[] = '삭제 처리 실패';
        return false;
    }

    /**
     * 오류 메시지 반환
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * 프로비저닝 로그 반환
     */
    public function getLog()
    {
        return $this->log;
    }

    // ================================================================
    // 내부 메서드
    // ================================================================

    private function validateSubdomain($subdomain)
    {
        if (!preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $subdomain)) {
            $this->errors[] = '서브도메인은 영소문자, 숫자, 하이픈만 사용 가능합니다 (1~63자)';
            return false;
        }

        // 예약어 차단
        $reserved = array('admin', 'www', 'api', 'ftp', 'mail', 'smtp', 'pop', 'imap', 'ns1', 'ns2', 'cdn', 'static', 'assets', 'test', 'dev', 'staging');
        if (in_array($subdomain, $reserved)) {
            $this->errors[] = "'{$subdomain}'는 예약된 서브도메인입니다";
            return false;
        }

        // 중복 검사
        $esc = mysqli_real_escape_string($this->masterLink, $subdomain);
        $result = mysqli_query($this->masterLink,
            "SELECT id FROM tenants WHERE subdomain = '{$esc}' LIMIT 1"
        );
        if ($result && mysqli_num_rows($result) > 0) {
            $this->errors[] = "'{$subdomain}' 서브도메인은 이미 사용 중입니다";
            return false;
        }

        return true;
    }

    private function getProvisionLink()
    {
        if ($this->provisionLink) return $this->provisionLink;

        $host = defined('MG_PROVISION_DB_HOST') ? MG_PROVISION_DB_HOST : MG_MASTER_DB_HOST;
        $user = defined('MG_PROVISION_DB_USER') ? MG_PROVISION_DB_USER : MG_MASTER_DB_USER;
        $pass = defined('MG_PROVISION_DB_PASS') ? MG_PROVISION_DB_PASS : MG_MASTER_DB_PASS;

        $this->provisionLink = @mysqli_connect($host, $user, $pass);
        if (!$this->provisionLink) {
            error_log('[TenantManager] Provision DB connect failed: ' . mysqli_connect_error());
            return false;
        }
        mysqli_set_charset($this->provisionLink, 'utf8mb4');
        return $this->provisionLink;
    }

    private function createDatabase($dbName, $dbUser, $dbPass)
    {
        $link = $this->provisionLink;
        $escDb = mysqli_real_escape_string($link, $dbName);
        $escUser = mysqli_real_escape_string($link, $dbUser);
        $escPass = mysqli_real_escape_string($link, $dbPass);

        // DB 생성 (이미 존재하면 재사용)
        try {
            mysqli_query($link, "CREATE DATABASE `{$escDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (\mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'database exists') === false) {
                $this->errors[] = 'DB 생성 실패: ' . $e->getMessage();
                return false;
            }
            // 이미 존재하면 계속 진행 (재시도 허용)
        }

        // 사용자 생성 + 권한 부여
        // MySQL 8+ 호환: CREATE USER IF NOT EXISTS
        try {
            mysqli_query($link, "CREATE USER IF NOT EXISTS '{$escUser}'@'%' IDENTIFIED BY '{$escPass}'");
        } catch (\mysqli_sql_exception $e) {
            // 이미 존재하면 무시
        }
        try {
            mysqli_query($link, "GRANT ALL PRIVILEGES ON `{$escDb}`.* TO '{$escUser}'@'%'");
        } catch (\mysqli_sql_exception $e) {
            $this->errors[] = '권한 부여 실패: ' . $e->getMessage();
            return false;
        }
        try { mysqli_query($link, "FLUSH PRIVILEGES"); } catch (\mysqli_sql_exception $e) {}

        $this->addLog("DB 생성: {$dbName}, 사용자: {$dbUser}");
        return true;
    }

    /**
     * SQL 파일 로드 + 실행
     *
     * @param mysqli $link        DB 연결
     * @param string $filePath    SQL 파일 경로
     * @param bool   $skipDrop    DROP TABLE 문 건너뛰기
     * @param array  $replacements 플레이스홀더 치환
     * @return bool
     */
    private function loadSqlFile($link, $filePath, $skipDrop = false, $replacements = array())
    {
        if (!file_exists($filePath)) {
            $this->errors[] = 'SQL 파일 없음: ' . $filePath;
            return false;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            $this->errors[] = 'SQL 파일 읽기 실패: ' . $filePath;
            return false;
        }

        // 플레이스홀더 치환
        if ($replacements) {
            $sql = str_replace(array_keys($replacements), array_values($replacements), $sql);
        }

        // 주석 제거
        $sql = preg_replace('/^--.*$/m', '', $sql);

        // 세미콜론으로 분리
        $statements = explode(';', $sql);

        foreach ($statements as $stmt) {
            $stmt = trim($stmt);
            if ($stmt === '') continue;

            // DROP TABLE 건너뛰기
            if ($skipDrop && preg_match('/^\s*DROP\s+TABLE/i', $stmt)) {
                continue;
            }

            try {
                mysqli_query($link, $stmt);
            } catch (\mysqli_sql_exception $e) {
                $error = $e->getMessage();
                // 이미 존재하는 테이블/컬럼, 중복 키 등은 무시
                if (strpos($error, 'already exists') !== false ||
                    strpos($error, 'Duplicate') !== false) {
                    continue;
                }
                $this->errors[] = 'SQL 실행 오류 (' . basename($filePath) . '): ' . $error;
                error_log('[TenantManager] SQL Error: ' . $error . ' / Statement: ' . substr($stmt, 0, 200));
                // 치명적이지 않은 오류는 계속 진행
            }
        }

        return true;
    }

    /**
     * 마이그레이션 실행 (신규 테넌트 프로비저닝용)
     *
     * db/migrations/ — 메인 테넌트 전용: install+seed로 스키마 완성이므로 실행하지 않고 적용 완료로 기록만
     * db/patches/    — 모든 테넌트 공통: 실제 실행
     *
     * @return int 실행된 패치 수
     */
    private function runMigrations($link)
    {
        // mg_migrations 테이블 존재 확인
        try {
            $result = mysqli_query($link, "SHOW TABLES LIKE 'mg_migrations'");
            if (!$result || mysqli_num_rows($result) === 0) {
                mysqli_query($link, "CREATE TABLE IF NOT EXISTS mg_migrations (
                    mig_id INT AUTO_INCREMENT PRIMARY KEY,
                    mig_file VARCHAR(200) NOT NULL UNIQUE,
                    mig_applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            }
        } catch (\mysqli_sql_exception $e) {
            // 무시
        }

        // --- 1) db/migrations/ 파일을 실행하지 않고 적용 완료로 기록 ---
        // (install.sql + seed.sql로 스키마가 이미 완성된 상태이므로
        //  DDL 재실행이나 문빌 전용 시드 데이터 삽입을 방지)
        $migrationDir = G5_PATH . '/db/migrations';
        if (is_dir($migrationDir)) {
            $migFiles = array_merge(
                glob($migrationDir . '/*.sql') ?: array(),
                glob($migrationDir . '/*.php') ?: array()
            );
            foreach ($migFiles as $file) {
                $filename = basename($file);
                $esc = mysqli_real_escape_string($link, $filename);
                try {
                    mysqli_query($link, "INSERT IGNORE INTO mg_migrations (mig_file) VALUES ('{$esc}')");
                } catch (\mysqli_sql_exception $e) {
                    // 무시
                }
            }
            $this->addLog('db/migrations/ ' . count($migFiles) . '개 파일 스킵 (적용 완료로 기록)');
        }

        // --- 2) db/patches/ 실제 실행 (모든 테넌트 공통) ---
        $patchDir = G5_PATH . '/db/patches';
        if (!is_dir($patchDir)) return 0;

        $patchFiles = array_merge(
            glob($patchDir . '/*.sql') ?: array(),
            glob($patchDir . '/*.php') ?: array()
        );
        if (!$patchFiles) return 0;
        sort($patchFiles);

        $count = 0;
        foreach ($patchFiles as $file) {
            $filename = basename($file);

            // 이미 적용된 패치 스킵
            $esc = mysqli_real_escape_string($link, $filename);
            try {
                $check = mysqli_query($link, "SELECT mig_id FROM mg_migrations WHERE mig_file = '{$esc}'");
                if ($check && mysqli_num_rows($check) > 0) {
                    continue;
                }
            } catch (\mysqli_sql_exception $e) {
                // 계속 진행
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($ext === 'php') {
                try {
                    include($file);
                } catch (\Exception $e) {
                    error_log('[TenantManager] Patch PHP error (' . $filename . '): ' . $e->getMessage());
                }
            } else {
                // SQL 파일 실행
                $sql = file_get_contents($file);
                if (!trim($sql)) {
                    // 빈 파일 → 기록만
                    try {
                        mysqli_query($link, "INSERT IGNORE INTO mg_migrations (mig_file) VALUES ('{$esc}')");
                    } catch (\mysqli_sql_exception $e) {}
                    continue;
                }

                try {
                    if (mysqli_multi_query($link, $sql)) {
                        do {
                            if ($result = mysqli_store_result($link)) {
                                mysqli_free_result($result);
                            }
                        } while (mysqli_next_result($link));
                    }
                } catch (\mysqli_sql_exception $e) {
                    error_log('[TenantManager] Patch error (' . $filename . '): ' . $e->getMessage());
                }
            }

            // 실행 기록
            try {
                mysqli_query($link, "INSERT IGNORE INTO mg_migrations (mig_file) VALUES ('{$esc}')");
            } catch (\mysqli_sql_exception $e) {
                // 무시
            }
            $count++;
        }

        return $count;
    }

    /**
     * 게시판용 write 테이블 생성
     */
    private function createWriteTables($link)
    {
        $result = mysqli_query($link, "SELECT bo_table FROM g5_board");
        if (!$result) return;

        while ($row = mysqli_fetch_assoc($result)) {
            $table = 'g5_write_' . $row['bo_table'];
            $esc = mysqli_real_escape_string($link, $table);

            // 이미 존재하면 스킵
            $check = mysqli_query($link, "SHOW TABLES LIKE '{$esc}'");
            if ($check && mysqli_num_rows($check) > 0) continue;

            // gnuboard 표준 write 테이블 구조
            $sql = "CREATE TABLE IF NOT EXISTS `{$esc}` (
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
                `mb_id` varchar(20) NOT NULL DEFAULT '',
                `wr_password` varchar(255) NOT NULL DEFAULT '',
                `wr_name` varchar(255) NOT NULL DEFAULT '',
                `wr_email` varchar(255) NOT NULL DEFAULT '',
                `wr_homepage` varchar(255) NOT NULL DEFAULT '',
                `wr_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                `wr_file` tinyint(4) NOT NULL DEFAULT '0',
                `wr_last` varchar(19) NOT NULL DEFAULT '',
                `wr_ip` varchar(255) NOT NULL DEFAULT '',
                `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
                `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
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
                PRIMARY KEY (`wr_id`),
                KEY `wr_num_reply` (`wr_num`,`wr_reply`),
                KEY `wr_is_comment` (`wr_is_comment`,`wr_id`),
                KEY `mb_id` (`mb_id`,`wr_is_comment`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

            mysqli_query($link, $sql);
        }
    }

    /**
     * 관리자 계정 생성
     *
     * @return string|false 생성된 비밀번호
     */
    private function createAdminAccount($link, $adminId, $adminEmail)
    {
        $password = bin2hex(random_bytes(6)); // 12자리 랜덤 비밀번호

        // PBKDF2 해시 생성 (gnuboard create_hash() 호환)
        $hash = $this->createPasswordHash($password);

        $esc_id = mysqli_real_escape_string($link, $adminId);
        $esc_email = mysqli_real_escape_string($link, $adminEmail);
        $esc_hash = mysqli_real_escape_string($link, $hash);
        $now = date('Y-m-d H:i:s');

        $sql = "INSERT INTO g5_member SET
            mb_id = '{$esc_id}',
            mb_password = '{$esc_hash}',
            mb_name = '관리자',
            mb_nick = '관리자',
            mb_email = '{$esc_email}',
            mb_level = 10,
            mb_mailling = '1',
            mb_open = '1',
            mb_nick_date = '{$now}',
            mb_email_certify = '{$now}',
            mb_datetime = '{$now}',
            mb_ip = '127.0.0.1'";

        if (mysqli_query($link, $sql)) {
            return $password;
        }

        $this->errors[] = '관리자 계정 생성 실패: ' . mysqli_error($link);
        return false;
    }

    /**
     * PBKDF2 비밀번호 해시 생성 (create_hash() 호환)
     * format: algorithm:iterations:salt:hash
     */
    private function createPasswordHash($password)
    {
        $algorithm = 'sha256';
        $iterations = 12000;
        $salt = base64_encode(random_bytes(24));
        $hash = base64_encode(
            hash_pbkdf2($algorithm, $password, $salt, $iterations, 24, true)
        );
        return $algorithm . ':' . $iterations . ':' . $salt . ':' . $hash;
    }

    /**
     * 파일 디렉토리 생성
     */
    private function createDirectories($tenantId)
    {
        $base = G5_DATA_PATH . '/tenants/' . $tenantId;
        $dirs = array(
            $base,
            $base . '/character',
            $base . '/emoticon',
            $base . '/shop',
            $base . '/seal',
            $base . '/lore',
            $base . '/prompt',
            $base . '/morgan',
            $base . '/morgan/side_icons',
            $base . '/morgan/material',
            $base . '/rp',
            $base . '/expedition',
            $base . '/widget',
            $base . '/file',
            $base . '/editor',
        );

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }

        // .htaccess 보호 (루트)
        $htaccess = $base . '/.htaccess';
        if (!file_exists($htaccess)) {
            @file_put_contents($htaccess, "<Files ~ \"\\.(php|sql|inc)$\">\nDeny from all\n</Files>\n");
        }
    }

    /**
     * 마스터 DB에 테넌트 레코드 삽입
     *
     * @return int|false 테넌트 ID
     */
    private function insertTenantRecord($subdomain, $name, $dbName, $dbUser, $dbPass, $adminEmail, $plan)
    {
        $dbHost = defined('MG_PROVISION_DB_HOST') ? MG_PROVISION_DB_HOST : '';

        $sql = sprintf(
            "INSERT INTO tenants (subdomain, name, db_host, db_name, db_user, db_pass, status, plan, admin_email)
             VALUES ('%s', '%s', '%s', '%s', '%s', '%s', 'active', '%s', '%s')",
            mysqli_real_escape_string($this->masterLink, $subdomain),
            mysqli_real_escape_string($this->masterLink, $name),
            mysqli_real_escape_string($this->masterLink, $dbHost),
            mysqli_real_escape_string($this->masterLink, $dbName),
            mysqli_real_escape_string($this->masterLink, $dbUser),
            mysqli_real_escape_string($this->masterLink, $dbPass),
            mysqli_real_escape_string($this->masterLink, $plan),
            mysqli_real_escape_string($this->masterLink, $adminEmail)
        );

        if (mysqli_query($this->masterLink, $sql)) {
            return mysqli_insert_id($this->masterLink);
        }

        $this->errors[] = '마스터 레코드 생성 실패: ' . mysqli_error($this->masterLink);
        return false;
    }

    /**
     * 프로비저닝 감사 로그
     */
    private function logProvision($tenantId, $action, $detail = '')
    {
        $adminId = isset($_SESSION['sa_id']) ? (int)$_SESSION['sa_id'] : 'NULL';
        $ip = isset($_SERVER['REMOTE_ADDR']) ? "'" . mysqli_real_escape_string($this->masterLink, $_SERVER['REMOTE_ADDR']) . "'" : 'NULL';

        $sql = sprintf(
            "INSERT INTO provision_log (tenant_id, action, detail, admin_id, ip_address)
             VALUES (%d, '%s', '%s', %s, %s)",
            (int)$tenantId,
            mysqli_real_escape_string($this->masterLink, $action),
            mysqli_real_escape_string($this->masterLink, $detail),
            $adminId,
            $ip
        );

        mysqli_query($this->masterLink, $sql);
    }

    /**
     * 테넌트 캐시 무효화
     */
    private function invalidateCache($subdomain)
    {
        $cacheFile = G5_DATA_PATH . '/cache/tenant/' . $subdomain . '.json';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
    }

    private function invalidateCacheById($tenantId)
    {
        $result = mysqli_query($this->masterLink,
            "SELECT subdomain FROM tenants WHERE id = " . (int)$tenantId
        );
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $this->invalidateCache($row['subdomain']);
        }
    }

    private function addLog($msg)
    {
        $this->log[] = date('H:i:s') . ' - ' . $msg;
    }

    public function __destruct()
    {
        if ($this->provisionLink) {
            @mysqli_close($this->provisionLink);
        }
    }
}
