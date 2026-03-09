-- 맵 지역별 마커 스타일 컬럼 추가
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_map_region' AND COLUMN_NAME = 'mr_marker_style');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_map_region ADD COLUMN mr_marker_style VARCHAR(20) DEFAULT \'pin\' COMMENT \'마커 스타일 (pin/circle/diamond/flag)\' AFTER mr_map_y', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
