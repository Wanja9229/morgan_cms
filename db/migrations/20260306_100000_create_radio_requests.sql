-- 라디오 신청권 시스템: 노래 신청 + 멘트 신청 테이블
CREATE TABLE IF NOT EXISTS mg_radio_requests (
    rr_id         INT AUTO_INCREMENT PRIMARY KEY,
    rr_type       ENUM('song','ment') NOT NULL,
    mb_id         VARCHAR(255) NOT NULL,
    rr_title      VARCHAR(200) NOT NULL DEFAULT '',
    rr_youtube_url VARCHAR(500) DEFAULT NULL,
    rr_youtube_vid VARCHAR(20) DEFAULT NULL,
    rr_content    VARCHAR(200) DEFAULT NULL,
    rr_status     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rr_created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    rr_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (rr_status),
    INDEX idx_mb (mb_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- mg_shop_item.si_type ENUM에 radio_song, radio_ment 추가
-- (etc 앞에 삽입 — 기존 값은 보존됨)
SET @col_type = (SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_shop_item' AND COLUMN_NAME = 'si_type');
SET @has_radio = (SELECT IF(@col_type LIKE '%radio_song%', 1, 0));
SET @sql = IF(@has_radio = 0,
    "ALTER TABLE mg_shop_item MODIFY si_type ENUM('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','seal_hover','profile_skin','profile_bg','char_slot','concierge_extra','title_prefix','title_suffix','radio_song','radio_ment','etc') NOT NULL DEFAULT 'etc'",
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
