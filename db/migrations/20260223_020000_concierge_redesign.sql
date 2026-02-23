-- 의뢰(Concierge) 시스템 리디자인
-- 포인트 스테이킹 + 지원 상한 + 수행 마감 기한 추가

-- mg_concierge 테이블 확장 (멱등성: 컬럼 존재 시 무시)
SET @dbname = DATABASE();

-- cc_point_total
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge' AND COLUMN_NAME = 'cc_point_total');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_concierge ADD COLUMN cc_point_total INT NOT NULL DEFAULT 0 COMMENT ''총 보상 포인트'' AFTER cc_match_mode', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- cc_max_applicants
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge' AND COLUMN_NAME = 'cc_max_applicants');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_concierge ADD COLUMN cc_max_applicants INT DEFAULT NULL COMMENT ''지원 인원 상한'' AFTER cc_max_members', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- cc_complete_deadline
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge' AND COLUMN_NAME = 'cc_complete_deadline');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_concierge ADD COLUMN cc_complete_deadline DATETIME DEFAULT NULL COMMENT ''수행 완료 기한'' AFTER cc_deadline', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 새 config 키
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('concierge_fee_rate', '0');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('concierge_apply_anonymous', '0');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('concierge_match_mode_allowed', 'both');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('concierge_point_min', '0');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('concierge_point_max', '1000');

-- 기존 고정 보상 키 제거
DELETE FROM mg_config WHERE cf_key = 'concierge_reward';
