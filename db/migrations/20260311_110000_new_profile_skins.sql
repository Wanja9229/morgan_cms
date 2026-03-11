-- 8개 신규 프로필 스킨 상점 아이템 추가
INSERT IGNORE INTO mg_shop_item (sc_id, si_name, si_desc, si_price, si_type, si_effect, si_stock, si_consumable, si_display, si_use)
VALUES
(1, 'Windows 98 클래식', '레트로 윈도우 98 UI 스타일 프로필 스킨', 250, 'profile_skin', '{"skin_id":"win98_classic"}', -1, 0, 1, 1),
(1, 'DOS 터미널', 'MS-DOS 명령 프롬프트 스타일 프로필 스킨', 250, 'profile_skin', '{"skin_id":"dos_terminal"}', -1, 0, 1, 1),
(1, 'macOS 모던', '애플 macOS 스타일 깔끔한 프로필 스킨', 300, 'profile_skin', '{"skin_id":"mac_modern"}', -1, 0, 1, 1),
(1, 'ECHO-4 전술 터미널', 'SF 사이버펑크 전술 단말기 프로필 스킨', 350, 'profile_skin', '{"skin_id":"echo_terminal"}', -1, 0, 1, 1),
(1, 'VS Code IDE', '개발자 IDE 코드 에디터 스타일 프로필 스킨', 300, 'profile_skin', '{"skin_id":"vscode_json"}', -1, 0, 1, 1),
(1, '클래식 JRPG', '16비트 시대 일본 RPG 스타일 프로필 스킨', 300, 'profile_skin', '{"skin_id":"jrpg_classic"}', -1, 0, 1, 1),
(1, '매거진 화보', '고급 패션 매거진 에디토리얼 프로필 스킨', 350, 'profile_skin', '{"skin_id":"magazine_editorial"}', -1, 0, 1, 1),
(1, '전설 카드', '판타지 TCG 전설 등급 카드 프로필 스킨', 350, 'profile_skin', '{"skin_id":"legendary_card"}', -1, 0, 1, 1);
