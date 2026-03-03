-- 세계관 지도와 파견 지도 이미지 분리
-- 기존: expedition_map_image를 세계관/파견 양쪽에서 공유
-- 변경: 세계관 → lore_map_image, 파견 → expedition_map_image (유지)

-- 기존 expedition_map_image 값을 lore_map_image로 복사 (이미 세계관 용도로도 사용 중이었으므로)
INSERT IGNORE INTO mg_config (cf_key, cf_value)
SELECT 'lore_map_image', cf_value
FROM mg_config
WHERE cf_key = 'expedition_map_image';
