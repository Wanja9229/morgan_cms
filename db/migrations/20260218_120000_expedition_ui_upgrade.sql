-- 파견 시스템 UI 개편: 맵 좌표 + 설정 추가

SET @col1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_expedition_area' AND COLUMN_NAME='ea_map_x');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE mg_expedition_area ADD COLUMN ea_map_x FLOAT DEFAULT NULL COMMENT \'맵 X좌표 퍼센트\' AFTER ea_order', 'SELECT 1');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @col2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_expedition_area' AND COLUMN_NAME='ea_map_y');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE mg_expedition_area ADD COLUMN ea_map_y FLOAT DEFAULT NULL COMMENT \'맵 Y좌표 퍼센트\' AFTER ea_map_x', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('expedition_ui_mode', 'list');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('expedition_map_image', '');
