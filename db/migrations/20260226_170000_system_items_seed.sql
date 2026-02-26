-- 시스템 아이템 시드: 캐릭터 슬롯, 이모티콘 등록권, 추가 의뢰권

-- 캐릭터 슬롯 (영구, 소모품)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('char_slot', '캐릭터 슬롯 +1', '캐릭터를 1개 더 생성할 수 있습니다. 사용 즉시 영구 적용됩니다.', 500, '', '{"slots":1}', 1, 0, 1, 1);

-- 이모티콘 등록권 (소모품)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('emoticon_reg', '이모티콘 등록권', '커스텀 이모티콘 셋 1개를 등록할 수 있는 권한입니다. 심사 요청 시 소비됩니다.', 300, '', '{}', 1, 0, 1, 1);

-- 추가 의뢰권 (소모품 - 의뢰 등록 시 기본 슬롯 초과 시 자동 소비)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('concierge_extra', '추가 의뢰권', '기본 의뢰 슬롯이 가득 찼을 때 추가로 1개 더 등록할 수 있습니다. 의뢰 등록 시 자동 소비됩니다.', 200, '', '{}', 1, 0, 1, 1);

-- 관리자 인벤토리에 테스트용 지급
INSERT IGNORE INTO mg_inventory (mb_id, si_id, iv_count, iv_datetime)
SELECT 'admin', si_id, 5, NOW() FROM mg_shop_item WHERE si_type IN ('char_slot', 'emoticon_reg', 'concierge_extra') AND si_use = 1;

-- max_characters 기본값을 1로 설정 (없는 경우에만)
INSERT IGNORE INTO mg_config (cf_key, cf_value) VALUES ('max_characters', '1');
