-- mg_battle_skill.sk_code에 UNIQUE 인덱스 추가 (INSERT IGNORE 중복 방지)
-- 이미 존재하면 무시
SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_skill' AND INDEX_NAME = 'uk_sk_code');

SET @sql = IF(@idx_exists = 0,
    'ALTER TABLE mg_battle_skill ADD UNIQUE INDEX uk_sk_code (sk_code)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
