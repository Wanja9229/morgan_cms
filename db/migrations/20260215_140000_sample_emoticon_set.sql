-- 기본 이모티콘 세트 + admin 소유 등록
-- 이미 존재하면 스킵 (em_code UNIQUE 제약)

INSERT IGNORE INTO mg_emoticon_set (es_id, es_name, es_desc, es_preview, es_price, es_order, es_use, es_creator_id, es_status, es_datetime)
VALUES (1, '기본 이모티콘', '기본 제공 이모티콘 세트', CONCAT(@@global.hostname, '/data/emoticon/1/smile.svg'), 0, 1, 1, 'admin', 'approved', NOW());

UPDATE mg_emoticon_set SET es_preview = REPLACE(es_preview, @@global.hostname, '') WHERE es_id = 1;

INSERT IGNORE INTO mg_emoticon (es_id, em_code, em_image, em_order) VALUES
(1, ':smile:', '/data/emoticon/1/smile.svg', 1),
(1, ':heart:', '/data/emoticon/1/heart.svg', 2),
(1, ':thumbsup:', '/data/emoticon/1/thumbsup.svg', 3),
(1, ':star:', '/data/emoticon/1/star.svg', 4),
(1, ':fire:', '/data/emoticon/1/fire.svg', 5),
(1, ':cry:', '/data/emoticon/1/cry.svg', 6),
(1, ':angry:', '/data/emoticon/1/angry.svg', 7),
(1, ':sparkle:', '/data/emoticon/1/sparkle.svg', 8);

INSERT IGNORE INTO mg_emoticon_own (mb_id, es_id, eo_datetime) VALUES ('admin', 1, NOW())