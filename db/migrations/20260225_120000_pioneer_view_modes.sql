-- 개척 시스템 뷰 모드 (카드뷰/거점뷰) 지원
-- fc_map_x, fc_map_y: 거점뷰에서 시설 마커 좌표 (%)

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_facility' AND COLUMN_NAME = 'fc_map_x');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `mg_facility` ADD COLUMN `fc_map_x` DECIMAL(5,2) DEFAULT NULL AFTER `fc_unlock_target`, ADD COLUMN `fc_map_y` DECIMAL(5,2) DEFAULT NULL AFTER `fc_map_x`', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 설정: 뷰 모드 (card / base)
INSERT IGNORE INTO `mg_config` (`cf_key`, `cf_value`) VALUES ('pioneer_view_mode', 'card');
-- 설정: 거점 이미지 URL
INSERT IGNORE INTO `mg_config` (`cf_key`, `cf_value`) VALUES ('pioneer_map_image', '');
