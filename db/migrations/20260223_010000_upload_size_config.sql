-- 파일 업로드 용량 설정 통합
-- 기존 파편화된 5개 키를 2개 통합 키로 교체

-- 새 통합 키 추가
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('upload_max_file', '5120');
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('upload_max_icon', '2048');

-- 기존 개별 키 삭제
DELETE FROM mg_config WHERE cf_key = 'emoticon_image_max_size';
DELETE FROM mg_config WHERE cf_key = 'seal_image_max_size';
DELETE FROM mg_config WHERE cf_key = 'lore_image_max_size';
DELETE FROM mg_config WHERE cf_key = 'lore_thumbnail_max_size';
DELETE FROM mg_config WHERE cf_key = 'prompt_banner_max_size';
