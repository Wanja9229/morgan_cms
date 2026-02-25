-- 재료 보상 시스템 리팩토링: 전역 → 게시판별 + 출석별 JSON 설정
-- br_material_comment: 댓글 작성 재료 보상 (신규)

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_board_reward' AND COLUMN_NAME='br_material_comment');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE mg_board_reward ADD COLUMN br_material_comment TEXT DEFAULT NULL AFTER br_material_list', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 전역 재료 보상 설정 삭제 (게시판별로 이관)
DELETE FROM mg_config WHERE cf_key IN ('pioneer_write_reward', 'pioneer_comment_reward', 'pioneer_rp_reward', 'pioneer_attendance_reward');
