-- 메인 빌더 2D 그리드 캔버스 전환
-- mg_main_widget에 x,y,w,h 컬럼 추가

ALTER TABLE mg_main_widget
  ADD COLUMN IF NOT EXISTS widget_x int NOT NULL DEFAULT 0 COMMENT '가로 시작 위치 (0~11)',
  ADD COLUMN IF NOT EXISTS widget_y int NOT NULL DEFAULT 0 COMMENT '세로 시작 위치 (0~)',
  ADD COLUMN IF NOT EXISTS widget_w int NOT NULL DEFAULT 6 COMMENT '가로 칸 수 (1~12)',
  ADD COLUMN IF NOT EXISTS widget_h int NOT NULL DEFAULT 2 COMMENT '세로 칸 수 (1~)';

-- 기존 데이터: widget_cols → widget_w 복사 (widget_w가 기본값 6인 경우만)
UPDATE mg_main_widget SET widget_w = widget_cols WHERE widget_w = 6 AND widget_cols != 6;

-- 그리드 설정
INSERT IGNORE INTO mg_config (cf_key, cf_value, cf_desc) VALUES ('grid_cell_height', '80', '그리드 1칸 세로 높이(px)');
INSERT IGNORE INTO mg_config (cf_key, cf_value, cf_desc) VALUES ('grid_rows', '40', '그리드 세로 칸 수');
