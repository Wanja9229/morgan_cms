-- 파견 시스템 UI 개편: 맵 좌표 + 설정 추가
-- ea_image 컬럼은 이미 존재
-- MySQL 8.0 호환: 프로시저로 컬럼 존재 체크

DELIMITER //
DROP PROCEDURE IF EXISTS _mg_add_expedition_map_cols//
CREATE PROCEDURE _mg_add_expedition_map_cols()
BEGIN
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_expedition_area' AND COLUMN_NAME='ea_map_x') THEN
    ALTER TABLE mg_expedition_area ADD COLUMN ea_map_x FLOAT DEFAULT NULL COMMENT '맵 X좌표 퍼센트' AFTER ea_order;
  END IF;
  IF NOT EXISTS (SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_expedition_area' AND COLUMN_NAME='ea_map_y') THEN
    ALTER TABLE mg_expedition_area ADD COLUMN ea_map_y FLOAT DEFAULT NULL COMMENT '맵 Y좌표 퍼센트' AFTER ea_map_x;
  END IF;
END//
DELIMITER ;
CALL _mg_add_expedition_map_cols();
DROP PROCEDURE IF EXISTS _mg_add_expedition_map_cols;

INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('expedition_ui_mode', 'list');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('expedition_map_image', '');
