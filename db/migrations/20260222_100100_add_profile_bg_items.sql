-- 프로필 배경 효과 상품 6종 등록 (이미 존재하면 건너뜀)
INSERT INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use, si_order)
SELECT 1, 'profile_bg', '별의 바다', '잔잔히 반짝이는 별들이 프로필 배경을 수놓습니다.', 200, '{"bg_id":"starfield"}', -1, 1, 1, 10
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"starfield"%');

INSERT INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use, si_order)
SELECT 1, 'profile_bg', '오로라 베일', '극지방의 오로라처럼 부드러운 빛이 흘러갑니다.', 250, '{"bg_id":"aurora"}', -1, 1, 1, 20
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"aurora"%');

INSERT INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use, si_order)
SELECT 1, 'profile_bg', '부유하는 입자', '미세한 빛 입자가 천천히 떠오릅니다.', 200, '{"bg_id":"particles"}', -1, 1, 1, 30
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"particles"%');

INSERT INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use, si_order)
SELECT 1, 'profile_bg', '그리드 펄스', '은은한 격자무늬가 맥동하듯 흐릅니다.', 180, '{"bg_id":"grid_pulse"}', -1, 1, 1, 40
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"grid_pulse"%');

INSERT INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use, si_order)
SELECT 1, 'profile_bg', '앰버 시머', '모건 시그니처 앰버 색상의 시머 효과입니다.', 300, '{"bg_id":"amber_shimmer"}', -1, 1, 1, 50
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"amber_shimmer"%');

INSERT INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use, si_order)
SELECT 1, 'profile_bg', '심연의 안개', '깊은 곳에서 피어오르는 어두운 안개입니다.', 220, '{"bg_id":"deep_mist"}', -1, 1, 1, 60
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type='profile_bg' AND si_effect LIKE '%"deep_mist"%');
