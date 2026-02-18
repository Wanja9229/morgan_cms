-- 관계도 노드 배치 저장용 컬럼 (JSON: {"ch_id": {"x": num, "y": num}, ...})
ALTER TABLE mg_character ADD COLUMN IF NOT EXISTS ch_graph_layout TEXT DEFAULT NULL;
