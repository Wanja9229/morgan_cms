-- 관계도 노드 배치 저장용 컬럼 (JSON: {"ch_id": {"x": num, "y": num}, ...})
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_graph_layout');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_character ADD COLUMN ch_graph_layout TEXT DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
