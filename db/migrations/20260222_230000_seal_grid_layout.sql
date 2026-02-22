-- M8: 인장 그리드 빌더 - seal_layout 컬럼 추가
ALTER TABLE mg_seal ADD COLUMN IF NOT EXISTS seal_layout TEXT DEFAULT NULL AFTER seal_text_color;
