-- 타임라인 시대 설명 컬럼 추가 + 이벤트-문서 연결 컬럼 추가
ALTER TABLE mg_lore_era ADD COLUMN IF NOT EXISTS le_desc TEXT DEFAULT NULL AFTER le_period;
ALTER TABLE mg_lore_event ADD COLUMN IF NOT EXISTS la_id INT DEFAULT 0 AFTER le_id;
