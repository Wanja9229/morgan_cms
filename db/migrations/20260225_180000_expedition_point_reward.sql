-- 파견지 참가자 포인트 보상 컬럼 추가
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_expedition_area' AND COLUMN_NAME = 'ea_point_min');
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mg_expedition_area ADD COLUMN ea_point_min INT DEFAULT 0 AFTER ea_partner_point, ADD COLUMN ea_point_max INT DEFAULT 0 AFTER ea_point_min',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 기존 파견지에 기본 포인트 보상 설정
UPDATE mg_expedition_area SET ea_point_min = 5, ea_point_max = 10 WHERE ea_point_min = 0 AND ea_point_max = 0 AND ea_stamina_cost <= 2;
UPDATE mg_expedition_area SET ea_point_min = 8, ea_point_max = 15 WHERE ea_point_min = 0 AND ea_point_max = 0 AND ea_stamina_cost <= 3;
UPDATE mg_expedition_area SET ea_point_min = 10, ea_point_max = 25 WHERE ea_point_min = 0 AND ea_point_max = 0 AND ea_stamina_cost > 3;
