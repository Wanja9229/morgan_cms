-- 라디오 플레이리스트/멘트 만료 시스템
-- 승인된 곡/멘트에 노출 기간을 설정하여 자동 순환

-- 플레이리스트에 만료일 추가
SET @has_col = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_radio_playlist' AND COLUMN_NAME = 'expires_at');
SET @sql = IF(@has_col = 0,
    'ALTER TABLE mg_radio_playlist ADD COLUMN expires_at DATETIME NULL DEFAULT NULL AFTER is_active',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 멘트에 만료일 추가
SET @has_col2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_radio_ments' AND COLUMN_NAME = 'expires_at');
SET @sql2 = IF(@has_col2 = 0,
    'ALTER TABLE mg_radio_ments ADD COLUMN expires_at DATETIME NULL DEFAULT NULL AFTER is_active',
    'SELECT 1');
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;

-- 신청 테이블에 기간 저장 컬럼 추가
SET @has_col3 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_radio_requests' AND COLUMN_NAME = 'rr_duration_hours');
SET @sql3 = IF(@has_col3 = 0,
    'ALTER TABLE mg_radio_requests ADD COLUMN rr_duration_hours INT NULL DEFAULT NULL AFTER rr_status',
    'SELECT 1');
PREPARE stmt3 FROM @sql3;
EXECUTE stmt3;
DEALLOCATE PREPARE stmt3;

-- 기존 상품에 기본 duration 설정 (노래: 72시간, 멘트: 24시간)
UPDATE mg_shop_item SET si_effect = '{"duration_hours":72}'
WHERE si_type = 'radio_song' AND (si_effect IS NULL OR si_effect = '' OR si_effect = '{}');

UPDATE mg_shop_item SET si_effect = '{"duration_hours":24}'
WHERE si_type = 'radio_ment' AND (si_effect IS NULL OR si_effect = '' OR si_effect = '{}');
