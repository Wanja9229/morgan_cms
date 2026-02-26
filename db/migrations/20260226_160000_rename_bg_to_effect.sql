-- 프로필/인장 배경 → 이펙트로 재분류
-- 기존 profile_bg (Vanta.js 효과) → profile_effect
-- 기존 seal_bg (CSS 애니메이션 효과) → seal_effect
-- 새로운 profile_bg = 배경 색상 아이템 (구매제)
-- 새로운 seal_bg = 배경 색상 아이템 (구매제)

-- Step 1: 타입 이름 변경 (효과 아이템)
UPDATE mg_shop_item SET si_type = 'profile_effect' WHERE si_type = 'profile_bg';
UPDATE mg_item_active SET ia_type = 'profile_effect' WHERE ia_type = 'profile_bg';
UPDATE mg_shop_item SET si_type = 'seal_effect' WHERE si_type = 'seal_bg';
UPDATE mg_item_active SET ia_type = 'seal_effect' WHERE ia_type = 'seal_bg';

-- Step 2: 커스텀 배경 이미지 아이템 삭제 (si_id=49, etc 타입)
DELETE FROM mg_item_active WHERE si_id IN (SELECT si_id FROM mg_shop_item WHERE si_type = 'etc' AND si_effect LIKE '%bg_custom_upload%');
DELETE FROM mg_inventory WHERE si_id IN (SELECT si_id FROM mg_shop_item WHERE si_type = 'etc' AND si_effect LIKE '%bg_custom_upload%');
DELETE FROM mg_shop_item WHERE si_type = 'etc' AND si_effect LIKE '%bg_custom_upload%';

-- Step 3: 프로필 배경 색상 아이템 (8종)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable) VALUES
('profile_bg', '미드나이트', '프로필 배경을 깊은 남색으로 물들입니다', 100, '', '{"color":"#0a192f"}', 1, 0, 0),
('profile_bg', '딥 퍼플', '프로필 배경을 보랏빛 어둠으로 물들입니다', 100, '', '{"color":"#1a1a2e"}', 1, 0, 0),
('profile_bg', '옵시디언', '프로필 배경을 칠흑 같은 어둠으로 물들입니다', 100, '', '{"color":"#0d1117"}', 1, 0, 0),
('profile_bg', '차콜', '프로필 배경을 짙은 회색으로 물들입니다', 100, '', '{"color":"#111827"}', 1, 0, 0),
('profile_bg', '버건디', '프로필 배경을 깊은 붉은빛으로 물들입니다', 100, '', '{"color":"#2a0a0f"}', 1, 0, 0),
('profile_bg', '다크 틸', '프로필 배경을 깊은 청록빛으로 물들입니다', 100, '', '{"color":"#0a1a1a"}', 1, 0, 0),
('profile_bg', '앰버 다크', '프로필 배경을 따뜻한 호박빛 어둠으로 물들입니다', 100, '', '{"color":"#1a150a"}', 1, 0, 0),
('profile_bg', '포레스트', '프로필 배경을 깊은 숲의 어둠으로 물들입니다', 100, '', '{"color":"#0a1a0d"}', 1, 0, 0);

-- Step 4: 인장 배경 색상 아이템 (8종)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable) VALUES
('seal_bg', '미드나이트', '인장 배경을 깊은 남색으로 물들입니다', 80, '', '{"color":"#0a192f"}', 1, 0, 0),
('seal_bg', '딥 퍼플', '인장 배경을 보랏빛 어둠으로 물들입니다', 80, '', '{"color":"#1a1a2e"}', 1, 0, 0),
('seal_bg', '옵시디언', '인장 배경을 칠흑 같은 어둠으로 물들입니다', 80, '', '{"color":"#0d1117"}', 1, 0, 0),
('seal_bg', '차콜', '인장 배경을 짙은 회색으로 물들입니다', 80, '', '{"color":"#111827"}', 1, 0, 0),
('seal_bg', '버건디', '인장 배경을 깊은 붉은빛으로 물들입니다', 80, '', '{"color":"#2a0a0f"}', 1, 0, 0),
('seal_bg', '다크 틸', '인장 배경을 깊은 청록빛으로 물들입니다', 80, '', '{"color":"#0a1a1a"}', 1, 0, 0),
('seal_bg', '앰버 다크', '인장 배경을 따뜻한 호박빛 어둠으로 물들입니다', 80, '', '{"color":"#1a150a"}', 1, 0, 0),
('seal_bg', '포레스트', '인장 배경을 깊은 숲의 어둠으로 물들입니다', 80, '', '{"color":"#0a1a0d"}', 1, 0, 0);

-- Step 5: 관리자 인벤토리에 신규 아이템 추가
INSERT IGNORE INTO mg_inventory (mb_id, si_id, iv_count, iv_datetime)
SELECT 'admin', si_id, 1, NOW() FROM mg_shop_item WHERE si_type IN ('profile_bg', 'seal_bg') AND si_use = 1;
