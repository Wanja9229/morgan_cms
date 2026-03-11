-- 스탯 초기화권 아이템 추가
-- si_type ENUM에 stat_reset 추가

ALTER TABLE mg_shop_item MODIFY si_type ENUM('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','seal_hover','seal_effect','profile_skin','profile_bg','profile_effect','char_slot','concierge_extra','title_prefix','title_suffix','radio_song','radio_ment','relation_slot','concierge_direct_pick','rp_pin','expedition_time','expedition_reward','expedition_stamina','expedition_slot','write_expand','achievement_slot','concierge_boost','nick_bg','stamina_recover','battle_weapon','battle_armor','battle_accessory','battle_consumable','battle_skill_book','stat_reset','etc') NOT NULL DEFAULT 'etc' COMMENT '타입';

-- 시드 상품
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'stat_reset', '스탯 초기화권', '배분한 전투 스탯을 초기화하고 재분배할 수 있습니다 (수업 보너스 유지)', 500, '', '{}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'stat_reset');
