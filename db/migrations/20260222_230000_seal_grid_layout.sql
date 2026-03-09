-- M8: 인장 그리드 빌더 - seal_layout 컬럼 추가
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_seal' AND COLUMN_NAME = 'seal_layout');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_seal ADD COLUMN seal_layout TEXT DEFAULT NULL AFTER seal_text_color', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
