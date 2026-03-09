-- 타임라인 시대 설명 컬럼 추가 + 이벤트-문서 연결 컬럼 추가

SET @col1 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_lore_era' AND COLUMN_NAME = 'le_desc');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE mg_lore_era ADD COLUMN le_desc TEXT DEFAULT NULL AFTER le_period', 'SELECT 1');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @col2 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_lore_event' AND COLUMN_NAME = 'la_id');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE mg_lore_event ADD COLUMN la_id INT DEFAULT 0 AFTER le_id', 'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
