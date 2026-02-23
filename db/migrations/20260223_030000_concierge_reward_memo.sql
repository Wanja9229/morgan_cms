-- 의뢰 추가 보상 메모 (구두약속) 컬럼 추가
SET @dbname = DATABASE();

SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_concierge' AND COLUMN_NAME = 'cc_reward_memo');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_concierge ADD COLUMN cc_reward_memo VARCHAR(200) DEFAULT NULL COMMENT ''추가 보상 메모 (구두약속)'' AFTER cc_point_total', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
