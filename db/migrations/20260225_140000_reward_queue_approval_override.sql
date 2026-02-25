-- 정산 승인 시 관리자 조정 기능 (유형 변경, 포인트 수동 입력, 메모)

SET @col1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_reward_queue' AND COLUMN_NAME='rq_override_rwt_id');
SET @sql1 = IF(@col1 = 0, 'ALTER TABLE mg_reward_queue ADD COLUMN rq_override_rwt_id INT DEFAULT NULL AFTER rq_reject_reason', 'SELECT 1');
PREPARE stmt1 FROM @sql1;
EXECUTE stmt1;
DEALLOCATE PREPARE stmt1;

SET @col2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_reward_queue' AND COLUMN_NAME='rq_override_point');
SET @sql2 = IF(@col2 = 0, 'ALTER TABLE mg_reward_queue ADD COLUMN rq_override_point INT DEFAULT NULL AFTER rq_override_rwt_id', 'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

SET @col3 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_reward_queue' AND COLUMN_NAME='rq_admin_note');
SET @sql3 = IF(@col3 = 0, 'ALTER TABLE mg_reward_queue ADD COLUMN rq_admin_note VARCHAR(255) DEFAULT NULL AFTER rq_override_point', 'SELECT 1');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;
