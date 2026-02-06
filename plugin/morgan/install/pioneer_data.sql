SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 재료 종류
INSERT INTO mg_material_type (mt_name, mt_code, mt_icon, mt_desc, mt_order) VALUES
('목재', 'wood', '🪵', '나무를 가공한 기본 건축 재료', 1),
('석재', 'stone', '🪨', '돌을 다듬어 만든 기본 건축 재료', 2),
('철광석', 'iron', '⛏️', '금속 가공에 필요한 광물', 3),
('유리', 'glass', '🪟', '모래를 녹여 만든 투명한 재료', 4),
('책', 'book', '📚', '지식이 담긴 서적', 5),
('마법석', 'crystal', '💎', '마력이 깃든 희귀한 보석', 6);

-- 샘플 시설
INSERT INTO mg_facility (fc_name, fc_desc, fc_icon, fc_status, fc_unlock_type, fc_unlock_target, fc_stamina_cost, fc_order) VALUES
('앓이란 게시판', '캐릭터의 앓이를 공유하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', '❤️', 'locked', 'board', 'ailiran', 100, 1),
('역극 게시판', '역할극을 진행하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', '🎭', 'locked', 'board', 'roleplay', 150, 2),
('상점', '포인트로 아이템을 구매할 수 있는 상점입니다.', '🛒', 'locked', 'shop', '', 200, 3),
('선물함', '다른 유저에게 선물을 보낼 수 있습니다.', '🎁', 'locked', 'gift', '', 120, 4);
