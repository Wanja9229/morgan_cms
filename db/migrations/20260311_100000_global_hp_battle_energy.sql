-- Global HP: mg_battle_energy에 current_hp, max_hp 추가
-- 전투 HP를 슬롯(per-battle)이 아닌 글로벌(per-character)로 관리

SET @col1 = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_energy' AND COLUMN_NAME = 'current_hp');
SET @sql1 = IF(@col1 = 0,
    'ALTER TABLE mg_battle_energy ADD COLUMN current_hp INT NOT NULL DEFAULT 0 AFTER last_charge_at',
    'SELECT 1');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @col2 = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_energy' AND COLUMN_NAME = 'max_hp');
SET @sql2 = IF(@col2 = 0,
    'ALTER TABLE mg_battle_energy ADD COLUMN max_hp INT NOT NULL DEFAULT 100 AFTER current_hp',
    'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- 기존 캐릭터 HP 초기화 (max_hp = base_hp(100) + stat_hp*10)
UPDATE mg_battle_energy ben
JOIN mg_battle_stat bs ON ben.ch_id = bs.ch_id
SET ben.max_hp = 100 + (bs.stat_hp * 10),
    ben.current_hp = CASE WHEN ben.current_hp = 0 THEN 100 + (bs.stat_hp * 10) ELSE ben.current_hp END
WHERE ben.max_hp = 100 AND ben.current_hp = 0;
