-- 전투 스탯 확정(잠금) 컬럼 추가
-- 저장 후 스탯 변경은 초기화 아이템으로만 가능
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_stat' AND COLUMN_NAME = 'stat_locked');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_battle_stat ADD COLUMN stat_locked TINYINT(1) NOT NULL DEFAULT 0 AFTER stat_stress', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
