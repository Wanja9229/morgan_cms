-- 프로필 스킨 10종 상점 아이템 등록 (noble_crest 제외 — 이미 등록됨)
INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '요원 인사기록', 'profile_skin', 300, '첩보 기관 스타일의 극비 인사기록 프로필', 1, '{"skin_id":"spy_dossier"}', 1, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%spy_dossier%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '길드 모험가 프로필', 'profile_skin', 300, '판타지 양피지 스타일의 모험가 등록부', 1, '{"skin_id":"fantasy_parchment"}', 2, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%fantasy_parchment%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, 'NIB 수사 데이터베이스', 'profile_skin', 350, '수사 기관 데이터베이스 조회 스타일', 1, '{"skin_id":"nib_database"}', 3, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%nib_database%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, 'WANTED 수배전단', 'profile_skin', 250, '서부 시대 수배 전단지 스타일', 1, '{"skin_id":"wanted_poster"}', 4, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%wanted_poster%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, 'SNS 프로필', 'profile_skin', 200, '소셜 미디어 프로필 페이지 스타일', 1, '{"skin_id":"sns_profile"}', 5, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%sns_profile%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '의료 차트', 'profile_skin', 300, '병원 진료 차트 스타일', 1, '{"skin_id":"medical_chart"}', 6, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%medical_chart%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '타로 카드', 'profile_skin', 350, '신비로운 타로 카드 스타일', 1, '{"skin_id":"tarot_card"}', 7, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%tarot_card%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '군 인사기록', 'profile_skin', 300, '군대 인사기록부 스타일', 1, '{"skin_id":"military_record"}', 8, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%military_record%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '아케이드 게임', 'profile_skin', 250, '레트로 아케이드 게임 캐릭터 선택 화면', 1, '{"skin_id":"arcade_game"}', 9, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%arcade_game%');

INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_effect, si_order, si_datetime)
SELECT 1, '신문 기사', 'profile_skin', 250, '신문 1면 기사 스타일', 1, '{"skin_id":"newspaper"}', 10, NOW()
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_skin' AND si_effect LIKE '%newspaper%');
