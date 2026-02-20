-- 그리드 가로 칸 수 설정 추가 (정사각형 셀 전환)
-- grid_cell_height는 더 이상 사용하지 않음 (프론트도 JS로 정사각형 셀 계산)

INSERT IGNORE INTO mg_config (cf_key, cf_value, cf_desc) VALUES ('grid_columns', '12', '그리드 가로 칸 수 (기본 12)');
