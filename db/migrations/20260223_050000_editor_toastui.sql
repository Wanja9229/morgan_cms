-- 전역 에디터를 toastui로 변경 + 모든 일반 게시판 DHTML 에디터 활성화
UPDATE g5_config SET cf_editor = 'toastui' LIMIT 1;
UPDATE g5_board SET bo_use_dhtml_editor = 1 WHERE bo_table NOT IN ('lb_corkboard','lb_intranet','lb_terminal','lordby');

-- write_concierge_result 테이블 접두사 수정 (g5_ 누락 보정)
-- 이미 올바른 이름이면 무시됨
SET @tbl_exists = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'write_concierge_result');
SET @tbl_correct = (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'g5_write_concierge_result');
SET @need_rename = IF(@tbl_exists > 0 AND @tbl_correct = 0, 1, 0);
SET @sql = IF(@need_rename, 'RENAME TABLE write_concierge_result TO g5_write_concierge_result', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
