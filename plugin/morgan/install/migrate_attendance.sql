-- Morgan Edition - 출석 테이블 마이그레이션
-- 기존 테이블에 게임 관련 컬럼 추가
-- 각 쿼리를 개별 실행하세요

-- 1. 게임 종류 컬럼
ALTER TABLE `mg_attendance` ADD COLUMN `at_game_type` varchar(20) DEFAULT 'dice' COMMENT '게임 종류' AFTER `at_point`;

-- 2. 게임 결과 컬럼
ALTER TABLE `mg_attendance` ADD COLUMN `at_game_result` text COMMENT '게임 결과 (JSON)' AFTER `at_game_type`;

-- 3. IP 컬럼
ALTER TABLE `mg_attendance` ADD COLUMN `at_ip` varchar(45) DEFAULT NULL COMMENT 'IP 주소' AFTER `at_game_result`;

-- 4. 기본 게임 설정 추가
INSERT INTO `mg_config` (`cf_key`, `cf_value`, `cf_desc`) VALUES
('attendance_game', 'dice', '출석체크 미니게임 종류'),
('game_dice_min', '10', '주사위 최소 포인트'),
('game_dice_max', '100', '주사위 최대 포인트'),
('game_dice_bonus_multiplier', '2', '7일 연속 보너스 배율')
ON DUPLICATE KEY UPDATE `cf_value` = VALUES(`cf_value`);
