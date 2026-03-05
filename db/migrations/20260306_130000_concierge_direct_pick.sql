-- 의뢰 지목권 (concierge_direct_pick) 추가
-- 추첨 전용 모드에서 지목권을 사용하면 직접 선택 가능

ALTER TABLE mg_shop_item MODIFY si_type ENUM(
    'title','badge','nick_color','nick_effect','profile_border','equip',
    'emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame',
    'seal_hover','profile_skin','profile_bg','char_slot','concierge_extra',
    'title_prefix','title_suffix','radio_song','radio_ment','relation_slot',
    'concierge_direct_pick','etc'
) NOT NULL DEFAULT 'etc' COMMENT '타입';

INSERT IGNORE INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT sc_id, '의뢰 지목권', '추첨 대신 지원자를 직접 선택할 수 있습니다 (1회 소모)', 300, 'concierge_direct_pick', '{}', -1, 1, 1, 1
FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1;
