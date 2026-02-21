-- 맵 지역별 마커 스타일 컬럼 추가
ALTER TABLE mg_map_region ADD COLUMN IF NOT EXISTS mr_marker_style VARCHAR(20) DEFAULT 'pin' COMMENT '마커 스타일 (pin/circle/diamond/flag)' AFTER mr_map_y;
