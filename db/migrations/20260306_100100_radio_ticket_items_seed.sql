-- 라디오 신청권 상품 시드 + 이용권 카테고리 정리
-- 의존: 20260306_100000_create_radio_requests.sql (테이블 + ENUM)

-- 이용권 카테고리 (없는 경우만)
INSERT IGNORE INTO mg_shop_category (sc_name, sc_icon, sc_order, sc_use)
SELECT '이용권', 'ticket', 6, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_category WHERE sc_name = '이용권');

-- 노래 신청권 (소모품)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('radio_song', '노래 신청권', '라디오에 원하는 곡을 신청할 수 있습니다. 사용 시 YouTube URL과 곡 제목을 입력하며, 관리자 승인 후 플레이리스트에 반영됩니다.', 100, '', '{"duration_hours":72}', 1,
 (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 1, 1);

-- 라디오 멘트권 (소모품)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('radio_ment', '라디오 멘트권', '라디오 멘트를 신청할 수 있습니다. 사용 시 멘트 내용을 입력하며 (200자 이내), 관리자 승인 후 멘트에 반영됩니다.', 100, '', '{}', 1,
 (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 1, 1);

-- 기존 이용권 아이템(이모티콘 등록권, 추가 의뢰권, 캐릭터 슬롯)도 이용권 카테고리로 이동
UPDATE mg_shop_item SET sc_id = (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1)
WHERE si_type IN ('char_slot', 'emoticon_reg', 'concierge_extra', 'radio_song', 'radio_ment')
  AND sc_id = 0;
