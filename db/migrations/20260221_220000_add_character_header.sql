-- 캐릭터 헤더/배너 이미지 컬럼 추가
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_header');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_character ADD COLUMN ch_header VARCHAR(500) DEFAULT \'\' AFTER ch_image', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
