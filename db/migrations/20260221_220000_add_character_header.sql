-- 캐릭터 헤더/배너 이미지 컬럼 추가
ALTER TABLE mg_character ADD COLUMN IF NOT EXISTS ch_header VARCHAR(500) DEFAULT '' AFTER ch_image;
