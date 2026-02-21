-- 캐릭터별 프로필 스킨/배경 효과 선택 컬럼
-- 기존 회원 단위(mg_item_active) → 캐릭터 단위로 변경
SET @col_skin = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_character' AND COLUMN_NAME='ch_profile_skin');
SET @col_bg = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='mg_character' AND COLUMN_NAME='ch_profile_bg');

SET @sql_skin = IF(@col_skin = 0, 'ALTER TABLE mg_character ADD COLUMN ch_profile_skin VARCHAR(50) DEFAULT \'\' AFTER ch_header', 'SELECT 1');
SET @sql_bg = IF(@col_bg = 0, 'ALTER TABLE mg_character ADD COLUMN ch_profile_bg VARCHAR(50) DEFAULT \'\' AFTER ch_profile_skin', 'SELECT 1');

PREPARE stmt1 FROM @sql_skin; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;
PREPARE stmt2 FROM @sql_bg; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;
