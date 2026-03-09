-- 전투 스킬 + 훈련 과정 아이콘 색상 컬럼 추가 (game-icons.net 연동)

-- mg_battle_skill
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_battle_skill' AND COLUMN_NAME = 'sk_icon_color');
SET @sql = IF(@col = 0,
    'ALTER TABLE mg_battle_skill ADD COLUMN sk_icon_color VARCHAR(10) DEFAULT \'#ffffff\' AFTER sk_icon',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- mg_training_class
SET @col2 = (SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_training_class' AND COLUMN_NAME = 'tc_icon_color');
SET @sql2 = IF(@col2 = 0,
    'ALTER TABLE mg_training_class ADD COLUMN tc_icon_color VARCHAR(10) DEFAULT \'#ffffff\' AFTER tc_icon',
    'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
