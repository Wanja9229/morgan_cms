-- 의뢰 안정성 패치: 강제 완료 추적 + 추첨 가중치 제거
SET @dbname = DATABASE();

-- 1. mg_concierge: cc_force_completed 컬럼 추가
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge' AND COLUMN_NAME = 'cc_force_completed');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_concierge ADD COLUMN cc_force_completed TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''강제 완료 여부 (0=정상, 1=강제)'' AFTER cc_status', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. mg_concierge: cc_force_completed_by 컬럼 추가
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge' AND COLUMN_NAME = 'cc_force_completed_by');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_concierge ADD COLUMN cc_force_completed_by VARCHAR(20) DEFAULT NULL COMMENT ''강제 완료 실행자 mb_id'' AFTER cc_force_completed', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. mg_concierge_apply: ca_has_boost 컬럼 제거
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge_apply' AND COLUMN_NAME = 'ca_has_boost');
SET @sql = IF(@col_exists = 1, 'ALTER TABLE mg_concierge_apply DROP COLUMN ca_has_boost', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
