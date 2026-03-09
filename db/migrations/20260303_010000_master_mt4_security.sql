-- ============================================================
-- MT-4 보안 강화: 마스터 DB 스키마 변경
-- 적용 대상: 마스터 DB (mg_master)
-- 적용: docker exec morgan_mysql mysql -u morgan_user -pmorgan_pass mg_master < 이파일
-- ============================================================

-- 스토리지 사용량 캐시 (매 업로드마다 du 실행 방지)
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tenants' AND COLUMN_NAME = 'storage_used_mb');
SET @sql = IF(@col = 0, 'ALTER TABLE tenants ADD COLUMN storage_used_mb DECIMAL(10,2) NOT NULL DEFAULT 0.00 COMMENT \'현재 스토리지 사용량 (MB)\' AFTER max_storage_mb', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 온보딩 Rate Limit 추적
CREATE TABLE IF NOT EXISTS onboard_rate_limit (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ip_address  VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
