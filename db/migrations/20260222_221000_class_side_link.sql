-- mg_class에 side_id 추가 (진영별 클래스 지원)
-- side_id = 0: 공용 (모든 진영), side_id > 0: 해당 진영 전용
SET @col_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_class' AND COLUMN_NAME = 'side_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_class ADD COLUMN side_id INT DEFAULT 0 COMMENT \'소속 진영 (0=공용)\' AFTER class_id', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- side_color 컬럼 제거 (프론트엔드 미사용)
SET @col_exists2 = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_side' AND COLUMN_NAME = 'side_color');
SET @sql2 = IF(@col_exists2 > 0, 'ALTER TABLE mg_side DROP COLUMN side_color', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
