-- 상점 시드 데이터 완전 동기화
-- 누락된 모든 상점 카테고리 + 아이템을 추가합니다

-- ============================================================
-- 1. 상점 카테고리 (6종)
-- ============================================================
INSERT IGNORE INTO mg_shop_category (sc_name, sc_desc, sc_icon, sc_order, sc_use) VALUES
('꾸미기', '칭호, 뱃지, 닉네임 효과', 'sparkles', 1, 1),
('이모티콘', '이모티콘, 스티커', 'face-smile', 2, 1),
('테두리', '프로필 테두리', 'square', 3, 1),
('장비', '캐릭터 장착 아이템', 'shield', 4, 1),
('기타', '기타 아이템', 'gift', 5, 1),
('이용권', NULL, 'ticket', 6, 1);

-- ============================================================
-- 2. 기본 꾸미기 아이템
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '황금 닉네임', '닉네임이 황금색으로 빛납니다', 500, 'nick_color', '{"nick_color":"#fbbf24"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '황금 닉네임' AND si_type = 'nick_color');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '무지개 효과', '닉네임에 무지개 효과가 적용됩니다', 1000, 'nick_effect', '{"nick_effect":"rainbow"}', 10, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '무지개 효과' AND si_type = 'nick_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '별 뱃지', '반짝이는 별 뱃지입니다', 200, 'badge', '{"badge_icon":"star","badge_color":"#fbbf24"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '별 뱃지' AND si_type = 'badge');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '테두리' LIMIT 1), '파란 테두리', '프로필에 파란 테두리가 적용됩니다', 300, 'profile_border', '{"border_color":"#3b82f6","border_style":"solid"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '파란 테두리' AND si_type = 'profile_border');

-- ============================================================
-- 3. 프로필 스킨 (19종)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '귀족 문장', '격식 있는 귀족 가문 스타일 프로필 스킨', 300, 'profile_skin', '{"skin_id":"noble_crest"}', -1, 0, 1, 1, 0
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '귀족 문장' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '요원 인사기록', '첩보 기관 스타일의 극비 인사기록 프로필', 300, 'profile_skin', '{"skin_id":"spy_dossier"}', -1, 0, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '요원 인사기록' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '길드 모험가 프로필', '판타지 양피지 스타일의 모험가 등록부', 300, 'profile_skin', '{"skin_id":"fantasy_parchment"}', -1, 0, 1, 1, 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '길드 모험가 프로필' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'NIB 수사 데이터베이스', '수사 기관 데이터베이스 조회 스타일', 350, 'profile_skin', '{"skin_id":"nib_database"}', -1, 0, 1, 1, 3
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'NIB 수사 데이터베이스' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'WANTED 수배전단', '서부 시대 수배 전단지 스타일', 250, 'profile_skin', '{"skin_id":"wanted_poster"}', -1, 0, 1, 1, 4
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'WANTED 수배전단' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'SNS 프로필', '소셜 미디어 프로필 페이지 스타일', 200, 'profile_skin', '{"skin_id":"sns_profile"}', -1, 0, 1, 1, 5
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'SNS 프로필' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '의료 차트', '병원 진료 차트 스타일', 300, 'profile_skin', '{"skin_id":"medical_chart"}', -1, 0, 1, 1, 6
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '의료 차트' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '타로 카드', '신비로운 타로 카드 스타일', 350, 'profile_skin', '{"skin_id":"tarot_card"}', -1, 0, 1, 1, 7
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '타로 카드' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '군 인사기록', '군대 인사기록부 스타일', 300, 'profile_skin', '{"skin_id":"military_record"}', -1, 0, 1, 1, 8
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '군 인사기록' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '아케이드 게임', '레트로 아케이드 게임 캐릭터 선택 화면', 250, 'profile_skin', '{"skin_id":"arcade_game"}', -1, 0, 1, 1, 9
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '아케이드 게임' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '신문 기사', '신문 1면 기사 스타일', 250, 'profile_skin', '{"skin_id":"newspaper"}', -1, 0, 1, 1, 10
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '신문 기사' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'Windows 98 클래식', '레트로 윈도우 98 UI 스타일 프로필 스킨', 250, 'profile_skin', '{"skin_id":"win98_classic"}', -1, 0, 1, 1, 11
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'Windows 98 클래식' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'DOS 터미널', 'MS-DOS 명령 프롬프트 스타일 프로필 스킨', 250, 'profile_skin', '{"skin_id":"dos_terminal"}', -1, 0, 1, 1, 12
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'DOS 터미널' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'macOS 모던', '애플 macOS 스타일 깔끔한 프로필 스킨', 300, 'profile_skin', '{"skin_id":"mac_modern"}', -1, 0, 1, 1, 13
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'macOS 모던' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'ECHO-4 전술 터미널', 'SF 사이버펑크 전술 단말기 프로필 스킨', 350, 'profile_skin', '{"skin_id":"echo_terminal"}', -1, 0, 1, 1, 14
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'ECHO-4 전술 터미널' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), 'VS Code IDE', '개발자 IDE 코드 에디터 스타일 프로필 스킨', 300, 'profile_skin', '{"skin_id":"vscode_json"}', -1, 0, 1, 1, 15
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = 'VS Code IDE' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '클래식 JRPG', '16비트 시대 일본 RPG 스타일 프로필 스킨', 300, 'profile_skin', '{"skin_id":"jrpg_classic"}', -1, 0, 1, 1, 16
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '클래식 JRPG' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '매거진 화보', '고급 패션 매거진 에디토리얼 프로필 스킨', 350, 'profile_skin', '{"skin_id":"magazine_editorial"}', -1, 0, 1, 1, 17
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '매거진 화보' AND si_type = 'profile_skin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '전설 카드', '판타지 TCG 전설 등급 카드 프로필 스킨', 350, 'profile_skin', '{"skin_id":"legendary_card"}', -1, 0, 1, 1, 18
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '전설 카드' AND si_type = 'profile_skin');

-- ============================================================
-- 4. 프로필 이펙트 (14종, Vanta.js)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '새 떼', 'Vanta.js BIRDS - 마우스에 반응하는 새 떼 애니메이션', 200, 'profile_effect', '{"bg_id":"birds"}', -1, 0, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '새 떼' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '안개', 'Vanta.js FOG - 몽환적인 안개 효과', 250, 'profile_effect', '{"bg_id":"fog"}', -1, 0, 1, 1, 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '안개' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '물결', 'Vanta.js WAVES - 물결치는 파도 표면', 200, 'profile_effect', '{"bg_id":"waves"}', -1, 0, 1, 1, 3
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '물결' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '구름', 'Vanta.js CLOUDS - 하늘 위 구름', 180, 'profile_effect', '{"bg_id":"clouds"}', -1, 0, 1, 1, 4
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '구름' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '먹구름', 'Vanta.js CLOUDS2 - 극적인 먹구름', 220, 'profile_effect', '{"bg_id":"clouds2"}', -1, 0, 1, 1, 5
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '먹구름' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '글로브', 'Vanta.js GLOBE - 와이어프레임 회전 지구본', 300, 'profile_effect', '{"bg_id":"globe"}', -1, 0, 1, 1, 6
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '글로브' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '네트워크', 'Vanta.js NET - 디지털 네트워크 연결', 250, 'profile_effect', '{"bg_id":"net"}', -1, 0, 1, 1, 7
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '네트워크' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '세포', 'Vanta.js CELLS - 유기적 세포 구조', 180, 'profile_effect', '{"bg_id":"cells"}', -1, 0, 1, 1, 8
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '세포' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '나뭇가지', 'Vanta.js TRUNK - 가지치기 성장 구조', 200, 'profile_effect', '{"bg_id":"trunk"}', -1, 0, 1, 1, 9
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '나뭇가지' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '지형도', 'Vanta.js TOPOLOGY - 3D 지형도', 220, 'profile_effect', '{"bg_id":"topology"}', -1, 0, 1, 1, 10
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '지형도' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '점 그리드', 'Vanta.js DOTS - 맥동하는 점 그리드', 180, 'profile_effect', '{"bg_id":"dots"}', -1, 0, 1, 1, 11
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '점 그리드' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '동심원', 'Vanta.js RINGS - 동심원 리플', 250, 'profile_effect', '{"bg_id":"rings"}', -1, 0, 1, 1, 12
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '동심원' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '수면', 'Vanta.js RIPPLE - 잔잔한 수면 리플', 200, 'profile_effect', '{"bg_id":"ripple"}', -1, 0, 1, 1, 13
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '수면' AND si_type = 'profile_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '빛 번짐', 'Vanta.js HALO - 우주적 빛 번짐 글로우', 350, 'profile_effect', '{"bg_id":"halo"}', -1, 0, 1, 1, 14
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '빛 번짐' AND si_type = 'profile_effect');

-- ============================================================
-- 5. 프로필 배경 색상 (8종, 카테고리 없음)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '미드나이트', '프로필 배경을 깊은 남색으로 물들입니다', 100, 'profile_bg', '{"color":"#0a192f"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '미드나이트' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '딥 퍼플', '프로필 배경을 보랏빛 어둠으로 물들입니다', 100, 'profile_bg', '{"color":"#1a1a2e"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '딥 퍼플' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '옵시디언', '프로필 배경을 칠흑 같은 어둠으로 물들입니다', 100, 'profile_bg', '{"color":"#0d1117"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '옵시디언' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '차콜', '프로필 배경을 짙은 회색으로 물들입니다', 100, 'profile_bg', '{"color":"#111827"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '차콜' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '버건디', '프로필 배경을 깊은 붉은빛으로 물들입니다', 100, 'profile_bg', '{"color":"#2a0a0f"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '버건디' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '다크 틸', '프로필 배경을 깊은 청록빛으로 물들입니다', 100, 'profile_bg', '{"color":"#0a1a1a"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '다크 틸' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '앰버 다크', '프로필 배경을 따뜻한 호박빛 어둠으로 물들입니다', 100, 'profile_bg', '{"color":"#1a150a"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '앰버 다크' AND si_type = 'profile_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '포레스트', '프로필 배경을 깊은 숲의 어둠으로 물들입니다', 100, 'profile_bg', '{"color":"#0a1a0d"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '포레스트' AND si_type = 'profile_bg');

-- ============================================================
-- 6. 인장 배경 (8종, 카테고리 없음)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '미드나이트', '인장 배경을 깊은 남색으로 물들입니다', 80, 'seal_bg', '{"color":"#0a192f"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '미드나이트' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '딥 퍼플', '인장 배경을 보랏빛 어둠으로 물들입니다', 80, 'seal_bg', '{"color":"#1a1a2e"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '딥 퍼플' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '옵시디언', '인장 배경을 칠흑 같은 어둠으로 물들입니다', 80, 'seal_bg', '{"color":"#0d1117"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '옵시디언' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '차콜', '인장 배경을 짙은 회색으로 물들입니다', 80, 'seal_bg', '{"color":"#111827"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '차콜' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '버건디', '인장 배경을 깊은 붉은빛으로 물들입니다', 80, 'seal_bg', '{"color":"#2a0a0f"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '버건디' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '다크 틸', '인장 배경을 깊은 청록빛으로 물들입니다', 80, 'seal_bg', '{"color":"#0a1a1a"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '다크 틸' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '앰버 다크', '인장 배경을 따뜻한 호박빛 어둠으로 물들입니다', 80, 'seal_bg', '{"color":"#1a150a"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '앰버 다크' AND si_type = 'seal_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT 0, '포레스트', '인장 배경을 깊은 숲의 어둠으로 물들입니다', 80, 'seal_bg', '{"color":"#0a1a0d"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '포레스트' AND si_type = 'seal_bg');

-- ============================================================
-- 7. 인장 프레임 (5종)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '골드 프레임', '우아한 이중 금색 테두리', 300, 'seal_frame', '{"border_style":"double","border_width":"3px","border_color":"#d4a843","border_radius":"12px"}', -1, 0, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '골드 프레임' AND si_type = 'seal_frame');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '실버 프레임', '깔끔한 은색 테두리', 250, 'seal_frame', '{"border_style":"solid","border_width":"2px","border_color":"#c0c0c0","border_radius":"12px"}', -1, 0, 1, 1, 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '실버 프레임' AND si_type = 'seal_frame');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '네온 프레임', '형광 초록 네온 테두리', 350, 'seal_frame', '{"border_style":"solid","border_width":"2px","border_color":"#00ff88","border_radius":"16px"}', -1, 0, 1, 1, 3
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '네온 프레임' AND si_type = 'seal_frame');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '점선 프레임', '앰버 색상 점선 테두리', 200, 'seal_frame', '{"border_style":"dashed","border_width":"2px","border_color":"#f59e0b","border_radius":"12px"}', -1, 0, 1, 1, 4
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '점선 프레임' AND si_type = 'seal_frame');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '그림자 프레임', '은은한 그림자 효과 테두리', 350, 'seal_frame', '{"border_style":"solid","border_width":"1px","border_color":"#3f4147","border_radius":"12px","box_shadow":"0 4px 20px rgba(0,0,0,0.4)"}', -1, 0, 1, 1, 5
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '그림자 프레임' AND si_type = 'seal_frame');

-- ============================================================
-- 8. 인장 이펙트 (5종)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '인장 배경: 안개', '인장에 안개처럼 흐르는 그라디언트 효과', 250, 'seal_effect', '{"bg_id":"fog","bg_color":"#1a1a2e"}', -1, 0, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '인장 배경: 안개' AND si_type = 'seal_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '인장 배경: 물결', '인장에 겹치는 물결 패턴 효과', 200, 'seal_effect', '{"bg_id":"waves","bg_color":"#0a192f"}', -1, 0, 1, 1, 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '인장 배경: 물결' AND si_type = 'seal_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '인장 배경: 셀', '인장에 맥동하는 유기적 셀 효과', 180, 'seal_effect', '{"bg_id":"cells","bg_color":"#0d1117"}', -1, 0, 1, 1, 3
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '인장 배경: 셀' AND si_type = 'seal_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '인장 배경: 네트워크', '인장에 빛나는 도트 그리드 효과', 220, 'seal_effect', '{"bg_id":"net","bg_color":"#111827"}', -1, 0, 1, 1, 4
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '인장 배경: 네트워크' AND si_type = 'seal_effect');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '인장 배경: 파문', '인장에 중심에서 퍼지는 파문 효과', 200, 'seal_effect', '{"bg_id":"ripple","bg_color":"#1e1e2e"}', -1, 0, 1, 1, 5
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '인장 배경: 파문' AND si_type = 'seal_effect');

-- ============================================================
-- 9. 인장 호버 (4종)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '앰버 글로우', '인장 요소에 앰버 색 발광 효과', 200, 'seal_hover', '{"hover_id":"glow_amber","css":"box-shadow: 0 0 12px rgba(245,159,10,0.5); transition: all 0.2s ease;"}', -1, 0, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '앰버 글로우' AND si_type = 'seal_hover');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '블루 글로우', '인장 요소에 파란색 발광 효과', 200, 'seal_hover', '{"hover_id":"glow_blue","css":"box-shadow: 0 0 12px rgba(59,130,246,0.5); transition: all 0.2s ease;"}', -1, 0, 1, 1, 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '블루 글로우' AND si_type = 'seal_hover');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '스케일업', '인장 요소에 확대 효과', 150, 'seal_hover', '{"hover_id":"scale","css":"transform: scale(1.05); transition: all 0.2s ease;"}', -1, 0, 1, 1, 3
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '스케일업' AND si_type = 'seal_hover');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '그림자 드랍', '인장 요소에 그림자 효과', 150, 'seal_hover', '{"hover_id":"shadow","css":"box-shadow: 0 4px 12px rgba(0,0,0,0.5); transition: all 0.2s ease;"}', -1, 0, 1, 1, 4
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '그림자 드랍' AND si_type = 'seal_hover');

-- ============================================================
-- 10. 칭호 뽑기 (2종, 카테고리 없음)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT 0, '접두칭호 뽑기', '랜덤으로 접두칭호 1종을 획득합니다. 이미 보유한 칭호가 나올 수 있습니다.', 100, 'title_prefix', '{}', -1, 1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '접두칭호 뽑기' AND si_type = 'title_prefix');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use, si_order)
SELECT 0, '접미칭호 뽑기', '랜덤으로 접미칭호 1종을 획득합니다. 이미 보유한 칭호가 나올 수 있습니다.', 100, 'title_suffix', '{}', -1, 1, 1, 1, 2
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '접미칭호 뽑기' AND si_type = 'title_suffix');

-- ============================================================
-- 11. 캐릭터 슬롯
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '장비' LIMIT 1), '추가 캐릭터 슬롯', '사용 시 캐릭터를 1개 더 생성할 수 있습니다.', 5000, 'char_slot', '{"slots": 1}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '추가 캐릭터 슬롯' AND si_type = 'char_slot');

-- ============================================================
-- 12. 이용권 아이템 (16종)
-- ============================================================
INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '이모티콘 등록권', '커스텀 이모티콘 셋 1개를 등록할 수 있는 권한입니다. 심사 요청 시 소비됩니다.', 300, 'emoticon_reg', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'emoticon_reg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '추가 의뢰권', '기본 의뢰 슬롯이 가득 찼을 때 추가로 1개 더 등록할 수 있습니다. 의뢰 등록 시 자동 소비됩니다.', 200, 'concierge_extra', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'concierge_extra');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '노래 신청권', '라디오에 원하는 곡을 신청할 수 있습니다. 관리자 검수에 따라 반영되지 않을 수 있습니다.', 100, 'radio_song', '{"duration_hours":72}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'radio_song');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '라디오 멘트권', '라디오 멘트를 신청할 수 있습니다. 관리자 검수에 따라 반영되지 않을 수 있습니다.', 50, 'radio_ment', '{"duration_hours":24}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'radio_ment');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '관계 슬롯 확장권', '특정 캐릭터의 관계 슬롯을 1개 추가합니다. 인벤토리에서 사용 시 캐릭터를 선택하여 적용합니다. (영구, 해제 불가)', 200, 'relation_slot', '{"slots":1}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'relation_slot');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '의뢰 지목권', '추첨 대신 지원자를 직접 선택할 수 있습니다 (1회 소모)', 300, 'concierge_direct_pick', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'concierge_direct_pick');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '역극 상단 노출권 (3일)', '역극 목록에서 3일간 상단에 고정 노출됩니다.', 500, 'rp_pin', '{"duration_hours":72}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'rp_pin');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '파견 시간 단축권', '파견 시간을 30% 단축합니다 (1회 소모)', 300, 'expedition_time', '{"reduce_percent":30}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_time');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '파견 보상 2배권', '파견 보상(포인트+재료)을 2배로 받습니다 (1회 소모)', 500, 'expedition_reward', '{"reward_multi":2}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_reward');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '스태미나 반감권', '파견 스태미나 소모를 50% 절감합니다 (1회 소모)', 400, 'expedition_stamina', '{"stamina_reduce_percent":50}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_stamina');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '파견 슬롯 추가권', '동시 파견 가능 수를 1개 추가합니다 (영구, 해제 불가)', 2000, 'expedition_slot', '{"slots":1}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_slot');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '글자수 확장권', '게시글 글자 제한을 30000자로 확장합니다 (영구)', 1500, 'write_expand', '{"max_chars":30000}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'write_expand');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '업적 쇼케이스 확장권', '업적 쇼케이스 슬롯을 1개 추가합니다 (영구, 최대 8개)', 1000, 'achievement_slot', '{"slots":1}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'achievement_slot');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '의뢰 보상 부스터', '의뢰 완료 보상 포인트를 30% 추가로 받습니다 (1회 소모)', 400, 'concierge_boost', '{"boost_percent":30}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'concierge_boost');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '꾸미기' LIMIT 1), '이름표 배경색 (앰버)', '닉네임에 배경색을 적용합니다', 300, 'nick_bg', '{"nick_bg":"#f59f0a","nick_bg_opacity":0.2}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'nick_bg');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '스태미나 회복 물약', '스태미나를 풀 충전합니다 (일일 상한 적용)', 500, 'stamina_recover', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'stamina_recover');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '스탯 초기화권', '배분한 전투 스탯을 초기화하고 재분배할 수 있습니다 (수업 보너스 유지)', 500, 'stat_reset', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'stat_reset');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '벌칙 무효화권', '룰렛 벌칙을 즉시 무효화합니다.', 300, 'roulette_nullify', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'roulette_nullify');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '랜덤 패스권', '슬롯 벌칙을 랜덤 회원에게 전달합니다.', 200, 'roulette_transfer_random', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'roulette_transfer_random');

INSERT INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), '지목 패스권', '슬롯 벌칙을 특정 회원에게 전달합니다.', 400, 'roulette_transfer_target', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'roulette_transfer_target');
