-- ======================================
-- 개척 시스템 업데이트 (시설 해금 연동)
-- ======================================

-- mg_facility 테이블에 해금 관련 컬럼 추가
ALTER TABLE `mg_facility`
ADD COLUMN `fc_unlock_type` varchar(50) NOT NULL DEFAULT '' COMMENT '해금 대상 타입 (board, shop, gift, achievement, history, fountain)' AFTER `fc_status`,
ADD COLUMN `fc_unlock_target` varchar(100) NOT NULL DEFAULT '' COMMENT '해금 대상 ID (게시판: bo_table, 그 외: 식별자)' AFTER `fc_unlock_type`,
ADD INDEX `fc_unlock` (`fc_unlock_type`, `fc_unlock_target`);

-- 기존 fc_unlock_key, fc_unlock_value 컬럼 삭제 (필요시)
-- ALTER TABLE `mg_facility` DROP COLUMN `fc_unlock_key`, DROP COLUMN `fc_unlock_value`;

-- 샘플 시설 데이터 (앓이란, 역극 게시판, 상점, 선물함)
INSERT INTO `mg_facility` (`fc_name`, `fc_desc`, `fc_icon`, `fc_status`, `fc_unlock_type`, `fc_unlock_target`, `fc_stamina_cost`, `fc_order`) VALUES
('앓이란 게시판', '캐릭터의 앓이를 공유하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', 'heart', 'locked', 'board', 'ailiran', 100, 1),
('역극 게시판', '역할극을 진행하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', 'theater', 'locked', 'board', 'roleplay', 150, 2),
('상점', '포인트로 아이템을 구매할 수 있는 상점입니다.', 'shopping-bag', 'locked', 'shop', '', 200, 3),
('선물함', '다른 유저에게 선물을 보낼 수 있습니다.', 'gift', 'locked', 'gift', '', 120, 4)
ON DUPLICATE KEY UPDATE `fc_name` = VALUES(`fc_name`);
