-- 히든 이벤트 시스템 Phase 1
-- 2026-03-04

-- 1. 이벤트 정의
CREATE TABLE IF NOT EXISTS mg_hidden_event (
    event_id        INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(100) NOT NULL,
    image_path      VARCHAR(255) NOT NULL,
    reward_type     ENUM('point','material') DEFAULT 'point',
    reward_id       INT NULL COMMENT '재료 mt_id (reward_type=material)',
    reward_amount   INT NOT NULL DEFAULT 100,
    probability     DECIMAL(5,2) NOT NULL DEFAULT 5.00 COMMENT '출현 확률 (%)',
    daily_limit     INT DEFAULT 1 COMMENT '이벤트별 유저당 일일 수령',
    daily_total     INT DEFAULT 5 COMMENT '전체 일일 수령 한도',
    active_from     DATETIME NULL,
    active_until    DATETIME NULL,
    is_active       TINYINT DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. 일회용 토큰
CREATE TABLE IF NOT EXISTS mg_event_token (
    token_id        VARCHAR(64) PRIMARY KEY,
    event_id        INT NOT NULL,
    mb_id           VARCHAR(50) NOT NULL,
    issued_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at      DATETIME NOT NULL,
    claimed         TINYINT DEFAULT 0,
    claimed_at      DATETIME NULL,
    INDEX idx_mb_claimed (mb_id, claimed),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. 수령 로그
CREATE TABLE IF NOT EXISTS mg_event_claim (
    claim_id        INT AUTO_INCREMENT PRIMARY KEY,
    mb_id           VARCHAR(50) NOT NULL,
    event_id        INT NOT NULL,
    reward_type     VARCHAR(20) NOT NULL,
    reward_amount   INT NOT NULL,
    claimed_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address      VARCHAR(45),
    INDEX idx_mb_date (mb_id, claimed_at),
    INDEX idx_event_date (event_id, claimed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. 의심 행동 로그
CREATE TABLE IF NOT EXISTS mg_event_suspicious (
    log_id          INT AUTO_INCREMENT PRIMARY KEY,
    mb_id           VARCHAR(50) NOT NULL,
    reason          VARCHAR(100) NOT NULL,
    details         TEXT NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mb (mb_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
