-- ============================================================
-- Morgan Edition 마스터 DB (mg_master) 스키마
-- 이 파일은 테넌트 DB가 아닌 마스터 DB에 적용
-- 적용: docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass mg_master < 이파일
-- ============================================================

-- 테넌트 레지스트리
CREATE TABLE IF NOT EXISTS tenants (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subdomain       VARCHAR(63) NOT NULL COMMENT '서브도메인 (영소문자, 숫자, 하이픈)',
    name            VARCHAR(200) NOT NULL DEFAULT '' COMMENT '커뮤니티 이름',

    -- DB 연결 정보
    db_host         VARCHAR(200) NOT NULL DEFAULT '' COMMENT '빈 값이면 마스터와 동일 호스트',
    db_name         VARCHAR(64) NOT NULL COMMENT '테넌트 DB명',
    db_user         VARCHAR(32) NOT NULL COMMENT '테넌트 DB 사용자',
    db_pass         VARCHAR(128) NOT NULL COMMENT '테넌트 DB 비밀번호',

    -- 상태
    status          ENUM('active','suspended','deleted') NOT NULL DEFAULT 'active',
    suspended_reason VARCHAR(500) DEFAULT NULL,

    -- 플랜 & 제한
    plan            ENUM('free','basic','pro') NOT NULL DEFAULT 'free',
    max_storage_mb  INT UNSIGNED NOT NULL DEFAULT 1024 COMMENT '저장 용량 상한 (MB)',
    max_members     INT UNSIGNED NOT NULL DEFAULT 100 COMMENT '회원 수 상한',

    -- 기능 옵션
    r2_enabled      TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Cloudflare R2 사용 여부',
    custom_domain   VARCHAR(200) DEFAULT NULL COMMENT '커스텀 도메인',

    -- 관리
    admin_email     VARCHAR(200) NOT NULL DEFAULT '' COMMENT '관리자 이메일',

    -- 타임스탬프
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_subdomain (subdomain),
    UNIQUE KEY uk_custom_domain (custom_domain),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 슈퍼 관리자
CREATE TABLE IF NOT EXISTS super_admins (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50) NOT NULL,
    password_hash   VARCHAR(255) NOT NULL COMMENT 'password_hash() 사용',
    email           VARCHAR(200) NOT NULL,
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at   DATETIME DEFAULT NULL,
    last_login_ip   VARCHAR(45) DEFAULT NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY uk_username (username),
    UNIQUE KEY uk_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 테넌트별 추가 설정
CREATE TABLE IF NOT EXISTS tenant_config (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    cf_key      VARCHAR(100) NOT NULL,
    cf_value    TEXT,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY uk_tenant_key (tenant_id, cf_key),
    CONSTRAINT fk_tc_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 프로비저닝/관리 감사 로그
CREATE TABLE IF NOT EXISTS provision_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id   INT UNSIGNED NOT NULL,
    action      VARCHAR(50) NOT NULL COMMENT 'create, suspend, activate, delete, backup, restore',
    detail      TEXT COMMENT 'JSON 형태의 상세 정보',
    admin_id    INT UNSIGNED DEFAULT NULL COMMENT '실행한 슈퍼 관리자 ID',
    ip_address  VARCHAR(45) DEFAULT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_tenant (tenant_id),
    INDEX idx_created (created_at),
    CONSTRAINT fk_pl_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
    CONSTRAINT fk_pl_admin FOREIGN KEY (admin_id) REFERENCES super_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
