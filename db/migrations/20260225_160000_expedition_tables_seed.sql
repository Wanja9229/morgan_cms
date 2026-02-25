-- 파견 시스템 테이블 생성 + 시드 데이터
-- Phase 18-B 기본 테이블 (install.sql에는 있으나 마이그레이션에 누락)

CREATE TABLE IF NOT EXISTS `mg_expedition_area` (
    `ea_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '파견지 ID',
    `ea_name` varchar(100) NOT NULL COMMENT '파견지 이름',
    `ea_desc` text COMMENT '설명',
    `ea_icon` varchar(200) DEFAULT NULL COMMENT '아이콘',
    `ea_image` varchar(255) DEFAULT NULL COMMENT '배경 이미지',
    `ea_stamina_cost` int(11) NOT NULL DEFAULT 2 COMMENT '필요 스태미나',
    `ea_duration` int(11) NOT NULL DEFAULT 60 COMMENT '소요 시간(분)',
    `ea_status` enum('active','hidden','locked') NOT NULL DEFAULT 'active' COMMENT '상태',
    `ea_unlock_facility` int(11) DEFAULT NULL COMMENT '해금 조건 시설 ID',
    `ea_partner_point` int(11) NOT NULL DEFAULT 10 COMMENT '파트너 보상 포인트',
    `ea_order` int(11) NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `ea_map_x` FLOAT DEFAULT NULL COMMENT '맵 X좌표 퍼센트',
    `ea_map_y` FLOAT DEFAULT NULL COMMENT '맵 Y좌표 퍼센트',
    PRIMARY KEY (`ea_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파견지 정의';

CREATE TABLE IF NOT EXISTS `mg_expedition_drop` (
    `ed_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '드롭 ID',
    `ea_id` int(11) NOT NULL COMMENT '파견지 ID',
    `mt_id` int(11) NOT NULL COMMENT '재료 종류 ID',
    `ed_min` int(11) NOT NULL DEFAULT 1 COMMENT '최소 획득량',
    `ed_max` int(11) NOT NULL DEFAULT 1 COMMENT '최대 획득량',
    `ed_chance` int(11) NOT NULL DEFAULT 100 COMMENT '드롭 확률(0~100)',
    `ed_is_rare` tinyint(1) NOT NULL DEFAULT 0 COMMENT '레어 드롭 여부',
    PRIMARY KEY (`ed_id`),
    INDEX `idx_drop_area` (`ea_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파견지별 드롭 테이블';

CREATE TABLE IF NOT EXISTS `mg_expedition_log` (
    `el_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '파견 로그 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '파견 보낸 회원',
    `ch_id` int(11) NOT NULL COMMENT '선택한 캐릭터 ID',
    `partner_mb_id` varchar(20) DEFAULT NULL COMMENT '파트너 회원 ID',
    `partner_ch_id` int(11) DEFAULT NULL COMMENT '파트너 캐릭터 ID',
    `ea_id` int(11) NOT NULL COMMENT '파견지 ID',
    `el_stamina_used` int(11) NOT NULL DEFAULT 0 COMMENT '소모 스태미나',
    `el_start` datetime NOT NULL COMMENT '파견 시작 시각',
    `el_end` datetime NOT NULL COMMENT '파견 완료 예정 시각',
    `el_status` enum('active','complete','claimed','cancelled') NOT NULL DEFAULT 'active' COMMENT '상태',
    `el_rewards` text COMMENT '획득 보상 JSON',
    PRIMARY KEY (`el_id`),
    INDEX `idx_expedition_mb` (`mb_id`, `el_status`),
    INDEX `idx_expedition_ch` (`ch_id`),
    INDEX `idx_expedition_partner` (`partner_ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파견 기록';

-- 시드 데이터: 파견지 5개
INSERT IGNORE INTO `mg_expedition_area` (`ea_id`, `ea_name`, `ea_desc`, `ea_icon`, `ea_stamina_cost`, `ea_duration`, `ea_status`, `ea_unlock_facility`, `ea_partner_point`, `ea_order`) VALUES
(1, '숲 외곽', '울창한 숲의 가장자리. 목재를 쉽게 구할 수 있다.', 'tree', 2, 60, 'active', NULL, 10, 1),
(2, '폐광산', '오래 전 버려진 광산. 석재와 철광석이 묻혀 있다.', 'building-office', 3, 120, 'active', NULL, 15, 2),
(3, '고대 도서관', '먼지 쌓인 서가 사이에서 귀중한 서적을 찾을 수 있다.', 'book-open', 4, 180, 'active', 6, 20, 3),
(4, '수정 동굴', '깊은 지하에서 마법석이 자라는 신비로운 동굴.', 'sparkles', 5, 240, 'active', 7, 25, 4),
(5, '유리 공방 터', '옛 장인의 공방 잔해. 유리 재료를 수거할 수 있다.', 'squares-2x2', 3, 90, 'active', NULL, 12, 5);

-- 시드 데이터: 드롭 테이블 (이미 존재하면 스킵)
-- mt_id: 7=목재, 8=석재, 9=철광석, 10=유리, 11=책, 12=마법석
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 1, 7, 1, 3, 100, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=1 AND mt_id=7);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 1, 11, 1, 1, 10, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=1 AND mt_id=11);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 2, 8, 1, 3, 100, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=2 AND mt_id=8);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 2, 9, 1, 2, 70, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=2 AND mt_id=9);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 2, 12, 1, 1, 8, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=2 AND mt_id=12);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 3, 11, 1, 3, 100, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=3 AND mt_id=11);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 3, 12, 1, 1, 30, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=3 AND mt_id=12);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 4, 12, 1, 2, 100, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=4 AND mt_id=12);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 4, 9, 1, 2, 50, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=4 AND mt_id=9);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 4, 10, 1, 1, 15, 1 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=4 AND mt_id=10);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 5, 10, 1, 2, 100, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=5 AND mt_id=10);
INSERT INTO `mg_expedition_drop` (`ea_id`, `mt_id`, `ed_min`, `ed_max`, `ed_chance`, `ed_is_rare`)
SELECT 5, 8, 1, 1, 60, 0 FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `mg_expedition_drop` WHERE ea_id=5 AND mt_id=8);

-- 파견 설정
INSERT IGNORE INTO `mg_config` (`cf_key`, `cf_value`) VALUES ('expedition_ui_mode', 'list');
INSERT IGNORE INTO `mg_config` (`cf_key`, `cf_value`) VALUES ('expedition_map_image', '');
INSERT IGNORE INTO `mg_config` (`cf_key`, `cf_value`) VALUES ('expedition_max_slots', '1');
