-- 귀족 문장 프로필 스킨 상품 등록
INSERT IGNORE INTO mg_shop_item (sc_id, si_type, si_name, si_desc, si_price, si_effect, si_stock, si_display, si_use)
VALUES (1, 'profile_skin', '귀족 문장', '격식 있는 귀족 가문 스타일 프로필 스킨', 300, '{"skin_id":"noble_crest"}', -1, 1, 1);
