-- 메인 빌더 2D 그리드 캔버스 전환
-- mg_main_widget에 x,y,w,h 컬럼 추가
-- 주의: ADD COLUMN IF NOT EXISTS는 MySQL 8.0 일부 버전 미지원 → 프로시저 방식 사용

SET @col_x = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_main_widget' AND COLUMN_NAME = 'widget_x');
SET @sql_x = IF(@col_x = 0, 'ALTER TABLE mg_main_widget ADD COLUMN widget_x int NOT NULL DEFAULT 0 COMMENT \'가로 시작 위치 (0~11)\'', 'SELECT 1');
PREPARE stmt_x FROM @sql_x; EXECUTE stmt_x; DEALLOCATE PREPARE stmt_x;

SET @col_y = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_main_widget' AND COLUMN_NAME = 'widget_y');
SET @sql_y = IF(@col_y = 0, 'ALTER TABLE mg_main_widget ADD COLUMN widget_y int NOT NULL DEFAULT 0 COMMENT \'세로 시작 위치 (0~)\'', 'SELECT 1');
PREPARE stmt_y FROM @sql_y; EXECUTE stmt_y; DEALLOCATE PREPARE stmt_y;

SET @col_w = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_main_widget' AND COLUMN_NAME = 'widget_w');
SET @sql_w = IF(@col_w = 0, 'ALTER TABLE mg_main_widget ADD COLUMN widget_w int NOT NULL DEFAULT 6 COMMENT \'가로 칸 수 (1~12)\'', 'SELECT 1');
PREPARE stmt_w FROM @sql_w; EXECUTE stmt_w; DEALLOCATE PREPARE stmt_w;

SET @col_h = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_main_widget' AND COLUMN_NAME = 'widget_h');
SET @sql_h = IF(@col_h = 0, 'ALTER TABLE mg_main_widget ADD COLUMN widget_h int NOT NULL DEFAULT 2 COMMENT \'세로 칸 수 (1~)\'', 'SELECT 1');
PREPARE stmt_h FROM @sql_h; EXECUTE stmt_h; DEALLOCATE PREPARE stmt_h;

-- 기존 데이터: widget_cols → widget_w 복사 (widget_w가 기본값 6인 경우만)
UPDATE mg_main_widget SET widget_w = widget_cols WHERE widget_w = 6 AND widget_cols != 6;

-- 그리드 설정
INSERT IGNORE INTO mg_config (cf_key, cf_value, cf_desc) VALUES ('grid_cell_height', '80', '그리드 1칸 세로 높이(px)');
INSERT IGNORE INTO mg_config (cf_key, cf_value, cf_desc) VALUES ('grid_rows', '40', '그리드 세로 칸 수');
