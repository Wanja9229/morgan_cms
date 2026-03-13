-- 관계 로그 시스템: 양쪽 로그 제출 후 관계 성립

-- 1. cr_status ENUM 확장 (accepted 추가)
ALTER TABLE mg_relation MODIFY cr_status ENUM('pending','accepted','active','rejected') NOT NULL DEFAULT 'pending' COMMENT '상태';

-- 2. 로그 글 ID 컬럼 추가
SET @dbname = DATABASE();

SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_relation' AND COLUMN_NAME = 'cr_wr_id_a';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mg_relation ADD COLUMN cr_wr_id_a INT DEFAULT NULL COMMENT ''A쪽 관계 로그 글 ID''',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_relation' AND COLUMN_NAME = 'cr_wr_id_b';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mg_relation ADD COLUMN cr_wr_id_b INT DEFAULT NULL COMMENT ''B쪽 관계 로그 글 ID''',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @col_exists FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'mg_relation' AND COLUMN_NAME = 'cr_bo_table';
SET @sql = IF(@col_exists = 0,
    'ALTER TABLE mg_relation ADD COLUMN cr_bo_table VARCHAR(20) DEFAULT NULL COMMENT ''관계 로그 게시판''',
    'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. rellog 게시판 등록
INSERT IGNORE INTO g5_board (bo_table, gr_id, bo_subject, bo_device, bo_list_level, bo_read_level, bo_write_level, bo_reply_level, bo_comment_level, bo_upload_level, bo_download_level, bo_html_level, bo_link_level, bo_read_point, bo_write_point, bo_comment_point, bo_download_point, bo_use_dhtml_editor, bo_page_rows, bo_mobile_page_rows, bo_subject_len, bo_mobile_subject_len, bo_new, bo_hot, bo_image_width, bo_skin, bo_mobile_skin, bo_include_head, bo_include_tail, bo_gallery_cols, bo_gallery_width, bo_gallery_height, bo_mobile_gallery_width, bo_mobile_gallery_height)
VALUES ('rellog', 'community', '관계 로그', 'both', 1, 1, 2, 2, 2, 2, 2, 1, 1, 0, 0, 0, 0, 1, 15, 15, 60, 30, 24, 100, 835, 'theme/basic', 'theme/basic', '_head.php', '_tail.php', 4, 202, 150, 172, 150);

-- 4. rellog write 테이블 생성 (sql_mode 완화 필요)
SET @old_sql_mode = @@sql_mode;
SET sql_mode = '';
CREATE TABLE IF NOT EXISTS g5_write_rellog LIKE g5_write_free;
SET sql_mode = @old_sql_mode;

-- 5. mg_config 기본값
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES
('relation_log_board', 'rellog');
