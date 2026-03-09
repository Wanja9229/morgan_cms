-- 전투 스탯 테이블에 CON(근성), LUK(행운) 컬럼 추가

SET @col1 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_stat' AND COLUMN_NAME = 'stat_con');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE mg_battle_stat ADD COLUMN stat_con int DEFAULT 5 AFTER stat_int', 'SELECT 1');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @col2 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_stat' AND COLUMN_NAME = 'stat_luk');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE mg_battle_stat ADD COLUMN stat_luk int DEFAULT 5 AFTER stat_con', 'SELECT 1');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
