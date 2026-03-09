-- NPC 캐릭터 플래그 추가
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_is_npc');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_character ADD COLUMN ch_is_npc tinyint(1) NOT NULL DEFAULT 0 COMMENT \'NPC 여부\' AFTER ch_main', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
