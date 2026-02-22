-- 캐릭터 슬롯 아이템: si_type enum에 char_slot 추가
ALTER TABLE mg_shop_item MODIFY COLUMN si_type enum('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','profile_skin','profile_bg','char_slot','etc') NOT NULL DEFAULT 'etc';

-- 기본 캐릭터 제한을 1로 변경
UPDATE mg_config SET cf_value = '1' WHERE cf_key IN ('max_characters', 'character_max');

-- 추가 캐릭터 슬롯 상품 등록
INSERT IGNORE INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_limit_per_user, si_consumable, si_display, si_use, si_order)
SELECT 4, '추가 캐릭터 슬롯', '사용 시 캐릭터를 1개 더 생성할 수 있습니다.', 5000, 'char_slot', '{"slots": 1}', -1, 0, 1, 1, 1, 0
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'char_slot' AND si_name = '추가 캐릭터 슬롯');
