-- 관계 슬롯 확장권 시스템
-- 캐릭터당 기본 관계 수 제한 + 상점에서 슬롯 확장

-- si_type ENUM에 relation_slot 추가
ALTER TABLE mg_shop_item MODIFY si_type ENUM(
    'title','badge','nick_color','nick_effect','profile_border','equip',
    'emoticon_set','emoticon_reg','furniture','material',
    'seal_bg','seal_frame','seal_hover','profile_skin','profile_bg',
    'char_slot','concierge_extra','title_prefix','title_suffix',
    'radio_song','radio_ment','relation_slot','etc'
) NOT NULL DEFAULT 'etc' COMMENT '타입';

-- 관계 슬롯 확장권 시드 상품
INSERT IGNORE INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1),
       '관계 슬롯 확장권', '특정 캐릭터의 관계 슬롯을 1개 추가합니다. 인벤토리에서 사용 시 캐릭터를 선택하여 적용합니다. (영구, 해제 불가)', 200, 'relation_slot', '{"slots":1}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'relation_slot');
