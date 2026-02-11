-- Morgan Edition - Database Schema
-- Version: 1.0
-- Phase 2: Core Tables

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ======================================
-- 1. 캐릭터 관련 테이블
-- ======================================

-- 1.1 캐릭터 기본 정보
CREATE TABLE IF NOT EXISTS `mg_character` (
    `ch_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '소유자 회원 ID',
    `ch_name` varchar(100) NOT NULL COMMENT '캐릭터 이름',
    `ch_state` enum('editing','pending','approved','deleted') NOT NULL DEFAULT 'editing' COMMENT '상태',
    `ch_type` enum('main','sub','npc') NOT NULL DEFAULT 'main' COMMENT '유형',
    `ch_main` tinyint(1) NOT NULL DEFAULT 0 COMMENT '대표 캐릭터 여부',
    `side_id` int DEFAULT NULL COMMENT '세력 ID',
    `class_id` int DEFAULT NULL COMMENT '종족 ID',
    `ch_thumb` varchar(500) DEFAULT NULL COMMENT '두상 이미지',
    `ch_image` varchar(500) DEFAULT NULL COMMENT '전신 이미지',
    `ch_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    `ch_update` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    PRIMARY KEY (`ch_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_state` (`ch_state`),
    INDEX `idx_main` (`mb_id`, `ch_main`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='캐릭터';

-- 1.2 캐릭터 승인 로그
CREATE TABLE IF NOT EXISTS `mg_character_log` (
    `log_id` int NOT NULL AUTO_INCREMENT,
    `ch_id` int NOT NULL COMMENT '캐릭터 ID',
    `log_action` enum('submit','approve','reject','edit') NOT NULL COMMENT '액션',
    `log_memo` text COMMENT '메모 (반려 사유 등)',
    `admin_id` varchar(20) DEFAULT NULL COMMENT '처리자 ID',
    `log_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '처리일시',
    PRIMARY KEY (`log_id`),
    INDEX `idx_ch_id` (`ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='캐릭터 승인 로그';

-- 1.3 프로필 양식
CREATE TABLE IF NOT EXISTS `mg_profile_field` (
    `pf_id` int NOT NULL AUTO_INCREMENT,
    `pf_code` varchar(50) NOT NULL COMMENT '항목 코드',
    `pf_name` varchar(100) NOT NULL COMMENT '표시명',
    `pf_type` enum('text','textarea','select','multiselect','url','image') NOT NULL DEFAULT 'text' COMMENT '입력 타입',
    `pf_options` text COMMENT '선택지 (JSON)',
    `pf_placeholder` varchar(200) DEFAULT NULL COMMENT '힌트 텍스트',
    `pf_help` text COMMENT '도움말',
    `pf_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '필수 여부',
    `pf_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `pf_category` varchar(50) DEFAULT '기본정보' COMMENT '분류/섹션',
    `pf_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`pf_id`),
    UNIQUE KEY `idx_code` (`pf_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='프로필 양식';

-- 1.4 프로필 값
CREATE TABLE IF NOT EXISTS `mg_profile_value` (
    `pv_id` int NOT NULL AUTO_INCREMENT,
    `ch_id` int NOT NULL COMMENT '캐릭터 ID',
    `pf_id` int NOT NULL COMMENT '프로필 항목 ID',
    `pv_value` text COMMENT '입력값',
    PRIMARY KEY (`pv_id`),
    UNIQUE KEY `idx_ch_pf` (`ch_id`, `pf_id`),
    INDEX `idx_ch_id` (`ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='프로필 값';

-- 1.5 세력
CREATE TABLE IF NOT EXISTS `mg_side` (
    `side_id` int NOT NULL AUTO_INCREMENT,
    `side_name` varchar(100) NOT NULL COMMENT '세력명',
    `side_desc` text COMMENT '설명',
    `side_image` varchar(500) DEFAULT NULL COMMENT '이미지',
    `side_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `side_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`side_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='세력';

-- 1.6 종족
CREATE TABLE IF NOT EXISTS `mg_class` (
    `class_id` int NOT NULL AUTO_INCREMENT,
    `class_name` varchar(100) NOT NULL COMMENT '종족명',
    `class_desc` text COMMENT '설명',
    `class_image` varchar(500) DEFAULT NULL COMMENT '이미지',
    `class_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `class_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='종족';

-- ======================================
-- 2. 시스템 테이블
-- ======================================

-- 2.1 Morgan Edition 설정
CREATE TABLE IF NOT EXISTS `mg_config` (
    `cf_id` int NOT NULL AUTO_INCREMENT,
    `cf_key` varchar(50) NOT NULL COMMENT '설정 키',
    `cf_value` text COMMENT '설정 값',
    `cf_desc` varchar(200) DEFAULT NULL COMMENT '설명',
    PRIMARY KEY (`cf_id`),
    UNIQUE KEY `idx_key` (`cf_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Morgan Edition 설정';

-- 2.2 출석
CREATE TABLE IF NOT EXISTS `mg_attendance` (
    `at_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `at_date` date NOT NULL COMMENT '출석 날짜',
    `at_point` int NOT NULL DEFAULT 0 COMMENT '지급 포인트',
    `at_game_type` varchar(20) DEFAULT 'dice' COMMENT '게임 종류',
    `at_game_result` text COMMENT '게임 결과 (JSON)',
    `at_ip` varchar(45) DEFAULT NULL COMMENT 'IP 주소',
    `at_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '출석 시간',
    PRIMARY KEY (`at_id`),
    UNIQUE KEY `idx_mb_date` (`mb_id`, `at_date`),
    INDEX `idx_date` (`at_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='출석';

-- 2.3 알림
CREATE TABLE IF NOT EXISTS `mg_notification` (
    `noti_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '수신자 회원 ID',
    `noti_type` varchar(50) NOT NULL COMMENT '알림 유형',
    `noti_title` varchar(200) NOT NULL COMMENT '제목',
    `noti_content` text COMMENT '내용',
    `noti_url` varchar(500) DEFAULT NULL COMMENT '링크',
    `noti_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '읽음 여부',
    `noti_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
    PRIMARY KEY (`noti_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_read` (`mb_id`, `noti_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='알림';

-- 2.4 글-캐릭터 연결
CREATE TABLE IF NOT EXISTS `mg_write_character` (
    `wc_id` int NOT NULL AUTO_INCREMENT,
    `bo_table` varchar(20) NOT NULL COMMENT '게시판 테이블명',
    `wr_id` int NOT NULL COMMENT '글 ID',
    `ch_id` int NOT NULL COMMENT '캐릭터 ID',
    `wc_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '연결일',
    PRIMARY KEY (`wc_id`),
    UNIQUE KEY `idx_board_write` (`bo_table`, `wr_id`),
    INDEX `idx_ch_id` (`ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='글-캐릭터 연결';

-- 2.5 메인 페이지 행
CREATE TABLE IF NOT EXISTS `mg_main_row` (
    `row_id` int NOT NULL AUTO_INCREMENT,
    `row_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `row_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`row_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메인 페이지 행';

-- 2.6 메인 페이지 위젯
CREATE TABLE IF NOT EXISTS `mg_main_widget` (
    `widget_id` int NOT NULL AUTO_INCREMENT,
    `row_id` int NOT NULL COMMENT '행 ID',
    `widget_type` varchar(50) NOT NULL COMMENT '위젯 타입',
    `widget_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `widget_cols` int NOT NULL DEFAULT 12 COMMENT '컬럼 너비 (1-12)',
    `widget_config` text COMMENT '위젯 설정 (JSON)',
    `widget_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`widget_id`),
    INDEX `idx_row_id` (`row_id`),
    CONSTRAINT `fk_widget_row` FOREIGN KEY (`row_id`) REFERENCES `mg_main_row`(`row_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메인 페이지 위젯';

-- ======================================
-- 3. 상점 관련 테이블
-- ======================================

-- 3.1 상점 카테고리
CREATE TABLE IF NOT EXISTS `mg_shop_category` (
    `sc_id` int NOT NULL AUTO_INCREMENT,
    `sc_name` varchar(50) NOT NULL COMMENT '카테고리명',
    `sc_desc` varchar(200) DEFAULT NULL COMMENT '설명',
    `sc_icon` varchar(100) DEFAULT NULL COMMENT '아이콘',
    `sc_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `sc_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`sc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='상점 카테고리';

-- 3.2 상점 상품
CREATE TABLE IF NOT EXISTS `mg_shop_item` (
    `si_id` int NOT NULL AUTO_INCREMENT,
    `sc_id` int NOT NULL COMMENT '카테고리 ID',
    `si_name` varchar(100) NOT NULL COMMENT '상품명',
    `si_desc` text COMMENT '설명',
    `si_image` varchar(500) DEFAULT NULL COMMENT '이미지',
    `si_price` int NOT NULL COMMENT '가격',
    `si_type` enum('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','furniture','etc') NOT NULL DEFAULT 'etc' COMMENT '타입',
    `si_effect` text COMMENT '효과 데이터 (JSON)',
    `si_stock` int NOT NULL DEFAULT -1 COMMENT '재고 (-1=무제한)',
    `si_stock_sold` int NOT NULL DEFAULT 0 COMMENT '판매 수량',
    `si_limit_per_user` int NOT NULL DEFAULT 0 COMMENT '1인당 제한 (0=무제한)',
    `si_sale_start` datetime DEFAULT NULL COMMENT '판매 시작일',
    `si_sale_end` datetime DEFAULT NULL COMMENT '판매 종료일',
    `si_consumable` tinyint(1) NOT NULL DEFAULT 0 COMMENT '소모품 여부',
    `si_display` tinyint(1) NOT NULL DEFAULT 1 COMMENT '노출 여부',
    `si_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 가능 여부',
    `si_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `si_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    PRIMARY KEY (`si_id`),
    INDEX `idx_category` (`sc_id`),
    INDEX `idx_display` (`si_display`, `si_use`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='상점 상품';

-- 3.3 구매 로그
CREATE TABLE IF NOT EXISTS `mg_shop_log` (
    `sl_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '구매자',
    `si_id` int NOT NULL COMMENT '상품 ID',
    `sl_price` int NOT NULL COMMENT '구매 가격',
    `sl_type` enum('purchase','gift_send','gift_receive') NOT NULL DEFAULT 'purchase' COMMENT '유형',
    `sl_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '일시',
    PRIMARY KEY (`sl_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_si_id` (`si_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='구매 로그';

-- 3.4 인벤토리
CREATE TABLE IF NOT EXISTS `mg_inventory` (
    `iv_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `si_id` int NOT NULL COMMENT '상품 ID',
    `iv_count` int NOT NULL DEFAULT 1 COMMENT '보유 수량',
    `iv_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '획득일',
    PRIMARY KEY (`iv_id`),
    UNIQUE KEY `idx_mb_si` (`mb_id`, `si_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='인벤토리';

-- 3.5 아이템 적용
CREATE TABLE IF NOT EXISTS `mg_item_active` (
    `ia_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `si_id` int NOT NULL COMMENT '상품 ID',
    `ia_type` varchar(20) NOT NULL COMMENT '적용 타입',
    `ch_id` int DEFAULT NULL COMMENT '캐릭터 ID (캐릭터별 적용 시)',
    `ia_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '적용일',
    PRIMARY KEY (`ia_id`),
    INDEX `idx_mb_type` (`mb_id`, `ia_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='아이템 적용';

-- 3.6 선물
CREATE TABLE IF NOT EXISTS `mg_gift` (
    `gf_id` int NOT NULL AUTO_INCREMENT,
    `mb_id_from` varchar(20) NOT NULL COMMENT '보내는 사람',
    `mb_id_to` varchar(20) NOT NULL COMMENT '받는 사람',
    `si_id` int NOT NULL COMMENT '상품 ID',
    `gf_message` varchar(200) DEFAULT NULL COMMENT '메시지',
    `gf_status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending' COMMENT '상태',
    `gf_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '선물 일시',
    PRIMARY KEY (`gf_id`),
    INDEX `idx_to_status` (`mb_id_to`, `gf_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='선물';

-- ======================================
-- 4. 샘플 데이터
-- ======================================

-- 3.1 기본 프로필 양식
INSERT INTO `mg_profile_field` (`pf_code`, `pf_name`, `pf_type`, `pf_placeholder`, `pf_required`, `pf_order`, `pf_category`) VALUES
('age', '나이', 'text', '예: 25세, 불명', 0, 1, '기본정보'),
('gender', '성별', 'select', NULL, 0, 2, '기본정보'),
('height', '키', 'text', '예: 175cm', 0, 3, '외형'),
('personality', '성격', 'textarea', '캐릭터의 성격을 설명해주세요', 0, 4, '성격'),
('appearance', '외형', 'textarea', '외모 특징을 설명해주세요', 0, 5, '외형'),
('background', '배경', 'textarea', '캐릭터의 배경 스토리', 0, 6, '기타')
ON DUPLICATE KEY UPDATE `pf_name` = VALUES(`pf_name`);

-- 3.2 성별 옵션 추가
UPDATE `mg_profile_field` SET `pf_options` = '["남성","여성","기타","불명"]' WHERE `pf_code` = 'gender';

-- 4.3 기본 설정값
INSERT INTO `mg_config` (`cf_key`, `cf_value`, `cf_desc`) VALUES
('character_approval', '1', '캐릭터 승인제 사용 (0: 즉시승인, 1: 관리자승인)'),
('character_max', '10', '회원당 최대 캐릭터 수'),
('attendance_point', '100', '출석 기본 포인트'),
('attendance_bonus', '500', '연속 출석 보너스 (7일)'),
('theme_primary_color', '#f59f0a', '테마 메인 컬러'),
('shop_use', '1', '상점 사용 여부'),
('shop_gift_use', '1', '선물 기능 사용 여부'),
('point_name', 'P', '포인트 단위'),
('rp_use', '1', '역극 기능 사용 여부'),
('rp_require_reply', '0', '판 세우기 전 필요 이음 수'),
('rp_max_member_default', '0', '기본 최대 참여자 수 (0=무제한)'),
('rp_max_member_limit', '20', '참여자 상한선'),
('rp_content_min', '20', '최소 글자 수'),
('emoticon_use', '1', '이모티콘 기능 사용 여부'),
('emoticon_creator_use', '1', '유저 이모티콘 제작 허용'),
('emoticon_commission_rate', '10', '판매 수수료율 (%)'),
('emoticon_min_count', '8', '셋 당 최소 이모티콘 수'),
('emoticon_max_count', '30', '셋 당 최대 이모티콘 수'),
('emoticon_image_max_size', '512', '이모티콘 이미지 최대 크기 (KB)'),
('emoticon_image_size', '128', '이모티콘 이미지 권장 크기 (px)')
ON DUPLICATE KEY UPDATE `cf_value` = VALUES(`cf_value`);

-- ======================================
-- 5. 역극(RP) 관련 테이블
-- ======================================

-- 5.1 역극 스레드
CREATE TABLE IF NOT EXISTS `mg_rp_thread` (
    `rt_id` int NOT NULL AUTO_INCREMENT,
    `rt_title` varchar(500) NOT NULL COMMENT '제목',
    `rt_content` text NOT NULL COMMENT '시작글',
    `rt_image` varchar(500) DEFAULT NULL COMMENT '첨부 이미지',
    `mb_id` varchar(20) NOT NULL COMMENT '판장 회원 ID',
    `ch_id` int NOT NULL COMMENT '판장 캐릭터 ID',
    `rt_max_member` int NOT NULL DEFAULT 0 COMMENT '최대 참여자 (0=무제한)',
    `rt_status` enum('open','closed','deleted') NOT NULL DEFAULT 'open' COMMENT '상태',
    `rt_reply_count` int NOT NULL DEFAULT 0 COMMENT '이음 수',
    `rt_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '생성일',
    `rt_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '최근 활동일',
    PRIMARY KEY (`rt_id`),
    INDEX `idx_status` (`rt_status`),
    INDEX `idx_update` (`rt_update`),
    INDEX `idx_mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='역극 스레드';

-- 5.2 역극 이음 (댓글)
CREATE TABLE IF NOT EXISTS `mg_rp_reply` (
    `rr_id` int NOT NULL AUTO_INCREMENT,
    `rt_id` int NOT NULL COMMENT '역극 ID',
    `rr_content` text NOT NULL COMMENT '내용',
    `rr_image` varchar(500) DEFAULT NULL COMMENT '첨부 이미지',
    `mb_id` varchar(20) NOT NULL COMMENT '작성자 회원 ID',
    `ch_id` int NOT NULL COMMENT '작성 캐릭터 ID',
    `rr_context_ch_id` int NOT NULL DEFAULT 0 COMMENT '대화 맥락 캐릭터 ID',
    `rr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '작성일',
    PRIMARY KEY (`rr_id`),
    INDEX `idx_rt_id` (`rt_id`),
    INDEX `idx_context_ch` (`rt_id`, `rr_context_ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='역극 이음';

-- 5.3 역극 참여자
CREATE TABLE IF NOT EXISTS `mg_rp_member` (
    `rm_id` int NOT NULL AUTO_INCREMENT,
    `rt_id` int NOT NULL COMMENT '역극 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '참여자 회원 ID',
    `ch_id` int NOT NULL COMMENT '참여 캐릭터 ID',
    `rm_reply_count` int NOT NULL DEFAULT 0 COMMENT '이음 횟수',
    `rm_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '참여 시작일',
    PRIMARY KEY (`rm_id`),
    UNIQUE KEY `idx_rt_mb` (`rt_id`, `mb_id`),
    INDEX `idx_mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='역극 참여자';

-- 5.4 역극 완결 기록
CREATE TABLE IF NOT EXISTS `mg_rp_completion` (
    `rc_id` int NOT NULL AUTO_INCREMENT,
    `rt_id` int NOT NULL COMMENT '역극 스레드 ID',
    `ch_id` int NOT NULL COMMENT '완결 캐릭터 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '캐릭터 소유자',
    `rc_mutual_count` int NOT NULL DEFAULT 0 COMMENT '판장과의 상호 이음 수',
    `rc_total_replies` int NOT NULL DEFAULT 0 COMMENT '해당 캐릭터 총 이음 수',
    `rc_rewarded` tinyint NOT NULL DEFAULT 0 COMMENT '보상 지급 여부 (1=지급)',
    `rc_point` int NOT NULL DEFAULT 0 COMMENT '지급된 포인트',
    `rc_status` enum('completed','revoked') NOT NULL DEFAULT 'completed',
    `rc_type` enum('manual','auto') NOT NULL DEFAULT 'manual',
    `rc_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `rc_by` varchar(20) DEFAULT NULL COMMENT '처리자 (수동시 판장 mb_id, 자동시 NULL)',
    PRIMARY KEY (`rc_id`),
    UNIQUE KEY `idx_rt_ch` (`rt_id`, `ch_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_datetime` (`rc_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='역극 완결 기록';

-- 5.5 잇기 누적 보상 추적
CREATE TABLE IF NOT EXISTS `mg_rp_reply_reward_log` (
    `rrl_id` int NOT NULL AUTO_INCREMENT,
    `rt_id` int NOT NULL COMMENT '스레드 ID',
    `rrl_reply_count` int NOT NULL COMMENT '보상 지급 시점 누적 이음 수',
    `rrl_point` int NOT NULL COMMENT '지급 포인트 (인당)',
    `rrl_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`rrl_id`),
    INDEX `idx_rt_id` (`rt_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='잇기 누적 보상 추적';

-- ======================================
-- 7. 이모티콘 관련 테이블
-- ======================================

-- 7.1 이모티콘 셋
CREATE TABLE IF NOT EXISTS `mg_emoticon_set` (
    `es_id` int NOT NULL AUTO_INCREMENT,
    `es_name` varchar(100) NOT NULL COMMENT '셋 이름',
    `es_desc` text COMMENT '설명',
    `es_preview` varchar(500) DEFAULT NULL COMMENT '미리보기 이미지',
    `es_price` int NOT NULL DEFAULT 0 COMMENT '가격 (포인트)',
    `es_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `es_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    `es_creator_id` varchar(20) DEFAULT NULL COMMENT '제작자 회원 ID (NULL=관리자)',
    `es_status` enum('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft' COMMENT '승인 상태',
    `es_reject_reason` text COMMENT '반려 사유',
    `es_sales_count` int NOT NULL DEFAULT 0 COMMENT '판매 수',
    `es_total_revenue` int NOT NULL DEFAULT 0 COMMENT '누적 판매액',
    `es_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    `es_update` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    PRIMARY KEY (`es_id`),
    INDEX `idx_creator` (`es_creator_id`),
    INDEX `idx_status` (`es_status`),
    INDEX `idx_use` (`es_use`, `es_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='이모티콘 셋';

-- 7.2 이모티콘 개별 이미지
CREATE TABLE IF NOT EXISTS `mg_emoticon` (
    `em_id` int NOT NULL AUTO_INCREMENT,
    `es_id` int NOT NULL COMMENT '셋 ID',
    `em_code` varchar(50) NOT NULL COMMENT '이모티콘 코드 (:smile:)',
    `em_image` varchar(500) NOT NULL COMMENT '이미지 경로',
    `em_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    PRIMARY KEY (`em_id`),
    INDEX `idx_es_id` (`es_id`),
    UNIQUE INDEX `idx_code` (`em_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='이모티콘';

-- 7.3 이모티콘 보유
CREATE TABLE IF NOT EXISTS `mg_emoticon_own` (
    `eo_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `es_id` int NOT NULL COMMENT '셋 ID',
    `eo_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '구매일',
    PRIMARY KEY (`eo_id`),
    UNIQUE INDEX `idx_mb_es` (`mb_id`, `es_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='이모티콘 보유';

-- 4.4 기본 상점 카테고리
INSERT INTO `mg_shop_category` (`sc_name`, `sc_desc`, `sc_icon`, `sc_order`) VALUES
('꾸미기', '칭호, 뱃지, 닉네임 효과', 'sparkles', 1),
('이모티콘', '이모티콘, 스티커', 'face-smile', 2),
('테두리', '프로필 테두리', 'square', 3),
('장비', '캐릭터 장착 아이템', 'shield', 4),
('기타', '기타 아이템', 'gift', 5)
ON DUPLICATE KEY UPDATE `sc_name` = VALUES(`sc_name`);

-- ======================================
-- 6. 기본 게시판 (GnuBoard5)
-- ======================================

-- 6.1 게시판 그룹: community
INSERT IGNORE INTO `g5_group` (`gr_id`, `gr_subject`, `gr_device`, `gr_admin`, `gr_use_access`, `gr_order`)
VALUES ('community', '커뮤니티', 'both', '', 0, 0);

-- 6.2 기본 게시판 5종
-- notice (공지사항) - basic 스킨, 관리자만 글쓰기, 댓글 비활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'notice',
    `gr_id` = 'community',
    `bo_subject` = '공지사항',
    `bo_device` = 'both',
    `bo_admin` = '',
    `bo_list_level` = 1,
    `bo_read_level` = 1,
    `bo_write_level` = 10,
    `bo_reply_level` = 10,
    `bo_comment_level` = 10,
    `bo_upload_level` = 10,
    `bo_download_level` = 1,
    `bo_html_level` = 1,
    `bo_link_level` = 1,
    `bo_count_modify` = 1,
    `bo_count_delete` = 1,
    `bo_read_point` = 0,
    `bo_write_point` = 0,
    `bo_comment_point` = 0,
    `bo_download_point` = 0,
    `bo_use_category` = 0,
    `bo_category_list` = '',
    `bo_use_sideview` = 0,
    `bo_use_file_content` = 0,
    `bo_use_secret` = 0,
    `bo_use_dhtml_editor` = 1,
    `bo_select_editor` = '',
    `bo_use_rss_view` = 0,
    `bo_use_good` = 0,
    `bo_use_nogood` = 0,
    `bo_use_name` = 0,
    `bo_use_signature` = 0,
    `bo_use_ip_view` = 0,
    `bo_use_list_view` = 0,
    `bo_use_list_file` = 0,
    `bo_use_list_content` = 0,
    `bo_table_width` = 100,
    `bo_subject_len` = 60,
    `bo_mobile_subject_len` = 30,
    `bo_page_rows` = 15,
    `bo_mobile_page_rows` = 15,
    `bo_new` = 24,
    `bo_hot` = 100,
    `bo_image_width` = 835,
    `bo_skin` = 'theme/basic',
    `bo_mobile_skin` = 'theme/basic',
    `bo_include_head` = '_head.php',
    `bo_include_tail` = '_tail.php',
    `bo_content_head` = '',
    `bo_mobile_content_head` = '',
    `bo_content_tail` = '',
    `bo_mobile_content_tail` = '',
    `bo_insert_content` = '',
    `bo_gallery_cols` = 4,
    `bo_gallery_width` = 202,
    `bo_gallery_height` = 150,
    `bo_mobile_gallery_width` = 125,
    `bo_mobile_gallery_height` = 100,
    `bo_upload_count` = 2,
    `bo_upload_size` = 1048576,
    `bo_reply_order` = 1,
    `bo_use_search` = 1,
    `bo_order` = 1,
    `bo_count_write` = 0,
    `bo_count_comment` = 0,
    `bo_write_min` = 0,
    `bo_write_max` = 0,
    `bo_comment_min` = 0,
    `bo_comment_max` = 0,
    `bo_notice` = '',
    `bo_use_email` = 0,
    `bo_use_cert` = '',
    `bo_use_sns` = 0,
    `bo_use_captcha` = 0,
    `bo_sort_field` = '',
    `bo_1_subj` = '', `bo_2_subj` = '', `bo_3_subj` = '', `bo_4_subj` = '', `bo_5_subj` = '',
    `bo_6_subj` = '', `bo_7_subj` = '', `bo_8_subj` = '', `bo_9_subj` = '', `bo_10_subj` = '',
    `bo_1` = '', `bo_2` = '', `bo_3` = '', `bo_4` = '', `bo_5` = '',
    `bo_6` = '', `bo_7` = '', `bo_8` = '', `bo_9` = '', `bo_10` = ''
ON DUPLICATE KEY UPDATE `bo_subject` = VALUES(`bo_subject`);

-- qna (문의) - memo 스킨, 회원 글쓰기, 관리자만 댓글, 비밀글 활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'qna',
    `gr_id` = 'community',
    `bo_subject` = '문의',
    `bo_device` = 'both',
    `bo_admin` = '',
    `bo_list_level` = 1,
    `bo_read_level` = 1,
    `bo_write_level` = 2,
    `bo_reply_level` = 2,
    `bo_comment_level` = 10,
    `bo_upload_level` = 2,
    `bo_download_level` = 1,
    `bo_html_level` = 1,
    `bo_link_level` = 1,
    `bo_count_modify` = 1,
    `bo_count_delete` = 1,
    `bo_read_point` = 0,
    `bo_write_point` = 5,
    `bo_comment_point` = 1,
    `bo_download_point` = 0,
    `bo_use_category` = 0,
    `bo_category_list` = '',
    `bo_use_sideview` = 0,
    `bo_use_file_content` = 0,
    `bo_use_secret` = 1,
    `bo_use_dhtml_editor` = 1,
    `bo_select_editor` = '',
    `bo_use_rss_view` = 0,
    `bo_use_good` = 0,
    `bo_use_nogood` = 0,
    `bo_use_name` = 0,
    `bo_use_signature` = 0,
    `bo_use_ip_view` = 0,
    `bo_use_list_view` = 0,
    `bo_use_list_file` = 0,
    `bo_use_list_content` = 0,
    `bo_table_width` = 100,
    `bo_subject_len` = 60,
    `bo_mobile_subject_len` = 30,
    `bo_page_rows` = 15,
    `bo_mobile_page_rows` = 15,
    `bo_new` = 24,
    `bo_hot` = 100,
    `bo_image_width` = 835,
    `bo_skin` = 'theme/memo',
    `bo_mobile_skin` = 'theme/memo',
    `bo_include_head` = '_head.php',
    `bo_include_tail` = '_tail.php',
    `bo_content_head` = '',
    `bo_mobile_content_head` = '',
    `bo_content_tail` = '',
    `bo_mobile_content_tail` = '',
    `bo_insert_content` = '',
    `bo_gallery_cols` = 4,
    `bo_gallery_width` = 202,
    `bo_gallery_height` = 150,
    `bo_mobile_gallery_width` = 125,
    `bo_mobile_gallery_height` = 100,
    `bo_upload_count` = 2,
    `bo_upload_size` = 1048576,
    `bo_reply_order` = 1,
    `bo_use_search` = 1,
    `bo_order` = 2,
    `bo_count_write` = 0,
    `bo_count_comment` = 0,
    `bo_write_min` = 0,
    `bo_write_max` = 0,
    `bo_comment_min` = 0,
    `bo_comment_max` = 0,
    `bo_notice` = '',
    `bo_use_email` = 0,
    `bo_use_cert` = '',
    `bo_use_sns` = 0,
    `bo_use_captcha` = 0,
    `bo_sort_field` = '',
    `bo_1_subj` = '', `bo_2_subj` = '', `bo_3_subj` = '', `bo_4_subj` = '', `bo_5_subj` = '',
    `bo_6_subj` = '', `bo_7_subj` = '', `bo_8_subj` = '', `bo_9_subj` = '', `bo_10_subj` = '',
    `bo_1` = '', `bo_2` = '', `bo_3` = '', `bo_4` = '', `bo_5` = '',
    `bo_6` = '', `bo_7` = '', `bo_8` = '', `bo_9` = '', `bo_10` = ''
ON DUPLICATE KEY UPDATE `bo_subject` = VALUES(`bo_subject`);

-- owner (오너게시판) - memo 스킨, 회원 글쓰기/댓글, 비밀글 비활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'owner',
    `gr_id` = 'community',
    `bo_subject` = '오너게시판',
    `bo_device` = 'both',
    `bo_admin` = '',
    `bo_list_level` = 1,
    `bo_read_level` = 1,
    `bo_write_level` = 2,
    `bo_reply_level` = 2,
    `bo_comment_level` = 2,
    `bo_upload_level` = 2,
    `bo_download_level` = 1,
    `bo_html_level` = 1,
    `bo_link_level` = 1,
    `bo_count_modify` = 1,
    `bo_count_delete` = 1,
    `bo_read_point` = 0,
    `bo_write_point` = 5,
    `bo_comment_point` = 1,
    `bo_download_point` = 0,
    `bo_use_category` = 0,
    `bo_category_list` = '',
    `bo_use_sideview` = 0,
    `bo_use_file_content` = 0,
    `bo_use_secret` = 0,
    `bo_use_dhtml_editor` = 1,
    `bo_select_editor` = '',
    `bo_use_rss_view` = 0,
    `bo_use_good` = 1,
    `bo_use_nogood` = 0,
    `bo_use_name` = 0,
    `bo_use_signature` = 0,
    `bo_use_ip_view` = 0,
    `bo_use_list_view` = 0,
    `bo_use_list_file` = 0,
    `bo_use_list_content` = 0,
    `bo_table_width` = 100,
    `bo_subject_len` = 60,
    `bo_mobile_subject_len` = 30,
    `bo_page_rows` = 15,
    `bo_mobile_page_rows` = 15,
    `bo_new` = 24,
    `bo_hot` = 100,
    `bo_image_width` = 835,
    `bo_skin` = 'theme/memo',
    `bo_mobile_skin` = 'theme/memo',
    `bo_include_head` = '_head.php',
    `bo_include_tail` = '_tail.php',
    `bo_content_head` = '',
    `bo_mobile_content_head` = '',
    `bo_content_tail` = '',
    `bo_mobile_content_tail` = '',
    `bo_insert_content` = '',
    `bo_gallery_cols` = 4,
    `bo_gallery_width` = 202,
    `bo_gallery_height` = 150,
    `bo_mobile_gallery_width` = 125,
    `bo_mobile_gallery_height` = 100,
    `bo_upload_count` = 2,
    `bo_upload_size` = 1048576,
    `bo_reply_order` = 1,
    `bo_use_search` = 1,
    `bo_order` = 3,
    `bo_count_write` = 0,
    `bo_count_comment` = 0,
    `bo_write_min` = 0,
    `bo_write_max` = 0,
    `bo_comment_min` = 0,
    `bo_comment_max` = 0,
    `bo_notice` = '',
    `bo_use_email` = 0,
    `bo_use_cert` = '',
    `bo_use_sns` = 0,
    `bo_use_captcha` = 0,
    `bo_sort_field` = '',
    `bo_1_subj` = '', `bo_2_subj` = '', `bo_3_subj` = '', `bo_4_subj` = '', `bo_5_subj` = '',
    `bo_6_subj` = '', `bo_7_subj` = '', `bo_8_subj` = '', `bo_9_subj` = '', `bo_10_subj` = '',
    `bo_1` = '', `bo_2` = '', `bo_3` = '', `bo_4` = '', `bo_5` = '',
    `bo_6` = '', `bo_7` = '', `bo_8` = '', `bo_9` = '', `bo_10` = ''
ON DUPLICATE KEY UPDATE `bo_subject` = VALUES(`bo_subject`);

-- vent (앓이란) - postit 스킨, 회원 글쓰기, 댓글 비활성화, 익명 활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'vent',
    `gr_id` = 'community',
    `bo_subject` = '앓이란',
    `bo_device` = 'both',
    `bo_admin` = '',
    `bo_list_level` = 1,
    `bo_read_level` = 1,
    `bo_write_level` = 2,
    `bo_reply_level` = 10,
    `bo_comment_level` = 10,
    `bo_upload_level` = 2,
    `bo_download_level` = 1,
    `bo_html_level` = 1,
    `bo_link_level` = 1,
    `bo_count_modify` = 1,
    `bo_count_delete` = 1,
    `bo_read_point` = 0,
    `bo_write_point` = 5,
    `bo_comment_point` = 0,
    `bo_download_point` = 0,
    `bo_use_category` = 0,
    `bo_category_list` = '',
    `bo_use_sideview` = 0,
    `bo_use_file_content` = 0,
    `bo_use_secret` = 0,
    `bo_use_dhtml_editor` = 0,
    `bo_select_editor` = '',
    `bo_use_rss_view` = 0,
    `bo_use_good` = 0,
    `bo_use_nogood` = 0,
    `bo_use_name` = 0,
    `bo_use_signature` = 0,
    `bo_use_ip_view` = 0,
    `bo_use_list_view` = 0,
    `bo_use_list_file` = 0,
    `bo_use_list_content` = 0,
    `bo_table_width` = 100,
    `bo_subject_len` = 60,
    `bo_mobile_subject_len` = 30,
    `bo_page_rows` = 20,
    `bo_mobile_page_rows` = 20,
    `bo_new` = 24,
    `bo_hot` = 100,
    `bo_image_width` = 835,
    `bo_skin` = 'theme/postit',
    `bo_mobile_skin` = 'theme/postit',
    `bo_include_head` = '_head.php',
    `bo_include_tail` = '_tail.php',
    `bo_content_head` = '',
    `bo_mobile_content_head` = '',
    `bo_content_tail` = '',
    `bo_mobile_content_tail` = '',
    `bo_insert_content` = '',
    `bo_gallery_cols` = 4,
    `bo_gallery_width` = 202,
    `bo_gallery_height` = 150,
    `bo_mobile_gallery_width` = 125,
    `bo_mobile_gallery_height` = 100,
    `bo_upload_count` = 0,
    `bo_upload_size` = 0,
    `bo_reply_order` = 1,
    `bo_use_search` = 0,
    `bo_order` = 4,
    `bo_count_write` = 0,
    `bo_count_comment` = 0,
    `bo_write_min` = 0,
    `bo_write_max` = 0,
    `bo_comment_min` = 0,
    `bo_comment_max` = 0,
    `bo_notice` = '',
    `bo_use_email` = 0,
    `bo_use_cert` = '',
    `bo_use_sns` = 0,
    `bo_use_captcha` = 0,
    `bo_sort_field` = '',
    `bo_1_subj` = '익명', `bo_2_subj` = '', `bo_3_subj` = '', `bo_4_subj` = '', `bo_5_subj` = '',
    `bo_6_subj` = '', `bo_7_subj` = '', `bo_8_subj` = '', `bo_9_subj` = '', `bo_10_subj` = '',
    `bo_1` = 'anonymous', `bo_2` = '', `bo_3` = '', `bo_4` = '', `bo_5` = '',
    `bo_6` = '', `bo_7` = '', `bo_8` = '', `bo_9` = '', `bo_10` = ''
ON DUPLICATE KEY UPDATE `bo_subject` = VALUES(`bo_subject`);

-- log (로그) - gallery 스킨, 회원 글쓰기/댓글, 비밀글 비활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'log',
    `gr_id` = 'community',
    `bo_subject` = '로그',
    `bo_device` = 'both',
    `bo_admin` = '',
    `bo_list_level` = 1,
    `bo_read_level` = 1,
    `bo_write_level` = 2,
    `bo_reply_level` = 2,
    `bo_comment_level` = 2,
    `bo_upload_level` = 2,
    `bo_download_level` = 1,
    `bo_html_level` = 1,
    `bo_link_level` = 1,
    `bo_count_modify` = 1,
    `bo_count_delete` = 1,
    `bo_read_point` = 0,
    `bo_write_point` = 5,
    `bo_comment_point` = 1,
    `bo_download_point` = 0,
    `bo_use_category` = 0,
    `bo_category_list` = '',
    `bo_use_sideview` = 0,
    `bo_use_file_content` = 0,
    `bo_use_secret` = 0,
    `bo_use_dhtml_editor` = 1,
    `bo_select_editor` = '',
    `bo_use_rss_view` = 0,
    `bo_use_good` = 1,
    `bo_use_nogood` = 0,
    `bo_use_name` = 0,
    `bo_use_signature` = 0,
    `bo_use_ip_view` = 0,
    `bo_use_list_view` = 0,
    `bo_use_list_file` = 1,
    `bo_use_list_content` = 0,
    `bo_table_width` = 100,
    `bo_subject_len` = 60,
    `bo_mobile_subject_len` = 30,
    `bo_page_rows` = 16,
    `bo_mobile_page_rows` = 12,
    `bo_new` = 24,
    `bo_hot` = 100,
    `bo_image_width` = 835,
    `bo_skin` = 'theme/gallery',
    `bo_mobile_skin` = 'theme/gallery',
    `bo_include_head` = '_head.php',
    `bo_include_tail` = '_tail.php',
    `bo_content_head` = '',
    `bo_mobile_content_head` = '',
    `bo_content_tail` = '',
    `bo_mobile_content_tail` = '',
    `bo_insert_content` = '',
    `bo_gallery_cols` = 4,
    `bo_gallery_width` = 202,
    `bo_gallery_height` = 150,
    `bo_mobile_gallery_width` = 125,
    `bo_mobile_gallery_height` = 100,
    `bo_upload_count` = 5,
    `bo_upload_size` = 5242880,
    `bo_reply_order` = 1,
    `bo_use_search` = 1,
    `bo_order` = 5,
    `bo_count_write` = 0,
    `bo_count_comment` = 0,
    `bo_write_min` = 0,
    `bo_write_max` = 0,
    `bo_comment_min` = 0,
    `bo_comment_max` = 0,
    `bo_notice` = '',
    `bo_use_email` = 0,
    `bo_use_cert` = '',
    `bo_use_sns` = 0,
    `bo_use_captcha` = 0,
    `bo_sort_field` = '',
    `bo_1_subj` = '', `bo_2_subj` = '', `bo_3_subj` = '', `bo_4_subj` = '', `bo_5_subj` = '',
    `bo_6_subj` = '', `bo_7_subj` = '', `bo_8_subj` = '', `bo_9_subj` = '', `bo_10_subj` = '',
    `bo_1` = '', `bo_2` = '', `bo_3` = '', `bo_4` = '', `bo_5` = '',
    `bo_6` = '', `bo_7` = '', `bo_8` = '', `bo_9` = '', `bo_10` = ''
ON DUPLICATE KEY UPDATE `bo_subject` = VALUES(`bo_subject`);

-- 6.3 게시판별 글 테이블 (g5_write_*)
CREATE TABLE IF NOT EXISTS `g5_write_notice` (
    `wr_id` int(11) NOT NULL AUTO_INCREMENT,
    `wr_num` int(11) NOT NULL DEFAULT '0',
    `wr_reply` varchar(10) NOT NULL DEFAULT '',
    `wr_parent` int(11) NOT NULL DEFAULT '0',
    `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
    `wr_comment` int(11) NOT NULL DEFAULT '0',
    `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
    `ca_name` varchar(255) NOT NULL DEFAULT '',
    `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
    `wr_subject` varchar(255) NOT NULL DEFAULT '',
    `wr_content` text NOT NULL,
    `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
    `wr_link1` text NOT NULL,
    `wr_link2` text NOT NULL,
    `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
    `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
    `wr_hit` int(11) NOT NULL DEFAULT '0',
    `wr_good` int(11) NOT NULL DEFAULT '0',
    `wr_nogood` int(11) NOT NULL DEFAULT '0',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `wr_password` varchar(255) NOT NULL DEFAULT '',
    `wr_name` varchar(255) NOT NULL DEFAULT '',
    `wr_email` varchar(255) NOT NULL DEFAULT '',
    `wr_homepage` varchar(255) NOT NULL DEFAULT '',
    `wr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wr_file` tinyint(4) NOT NULL DEFAULT '0',
    `wr_last` varchar(19) NOT NULL DEFAULT '',
    `wr_ip` varchar(255) NOT NULL DEFAULT '',
    `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
    `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
    `wr_1` varchar(255) NOT NULL DEFAULT '',
    `wr_2` varchar(255) NOT NULL DEFAULT '',
    `wr_3` varchar(255) NOT NULL DEFAULT '',
    `wr_4` varchar(255) NOT NULL DEFAULT '',
    `wr_5` varchar(255) NOT NULL DEFAULT '',
    `wr_6` varchar(255) NOT NULL DEFAULT '',
    `wr_7` varchar(255) NOT NULL DEFAULT '',
    `wr_8` varchar(255) NOT NULL DEFAULT '',
    `wr_9` varchar(255) NOT NULL DEFAULT '',
    `wr_10` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`wr_id`),
    KEY `wr_seo_title` (`wr_seo_title`),
    KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
    KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `g5_write_qna` (
    `wr_id` int(11) NOT NULL AUTO_INCREMENT,
    `wr_num` int(11) NOT NULL DEFAULT '0',
    `wr_reply` varchar(10) NOT NULL DEFAULT '',
    `wr_parent` int(11) NOT NULL DEFAULT '0',
    `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
    `wr_comment` int(11) NOT NULL DEFAULT '0',
    `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
    `ca_name` varchar(255) NOT NULL DEFAULT '',
    `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
    `wr_subject` varchar(255) NOT NULL DEFAULT '',
    `wr_content` text NOT NULL,
    `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
    `wr_link1` text NOT NULL,
    `wr_link2` text NOT NULL,
    `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
    `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
    `wr_hit` int(11) NOT NULL DEFAULT '0',
    `wr_good` int(11) NOT NULL DEFAULT '0',
    `wr_nogood` int(11) NOT NULL DEFAULT '0',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `wr_password` varchar(255) NOT NULL DEFAULT '',
    `wr_name` varchar(255) NOT NULL DEFAULT '',
    `wr_email` varchar(255) NOT NULL DEFAULT '',
    `wr_homepage` varchar(255) NOT NULL DEFAULT '',
    `wr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wr_file` tinyint(4) NOT NULL DEFAULT '0',
    `wr_last` varchar(19) NOT NULL DEFAULT '',
    `wr_ip` varchar(255) NOT NULL DEFAULT '',
    `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
    `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
    `wr_1` varchar(255) NOT NULL DEFAULT '',
    `wr_2` varchar(255) NOT NULL DEFAULT '',
    `wr_3` varchar(255) NOT NULL DEFAULT '',
    `wr_4` varchar(255) NOT NULL DEFAULT '',
    `wr_5` varchar(255) NOT NULL DEFAULT '',
    `wr_6` varchar(255) NOT NULL DEFAULT '',
    `wr_7` varchar(255) NOT NULL DEFAULT '',
    `wr_8` varchar(255) NOT NULL DEFAULT '',
    `wr_9` varchar(255) NOT NULL DEFAULT '',
    `wr_10` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`wr_id`),
    KEY `wr_seo_title` (`wr_seo_title`),
    KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
    KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `g5_write_owner` (
    `wr_id` int(11) NOT NULL AUTO_INCREMENT,
    `wr_num` int(11) NOT NULL DEFAULT '0',
    `wr_reply` varchar(10) NOT NULL DEFAULT '',
    `wr_parent` int(11) NOT NULL DEFAULT '0',
    `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
    `wr_comment` int(11) NOT NULL DEFAULT '0',
    `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
    `ca_name` varchar(255) NOT NULL DEFAULT '',
    `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
    `wr_subject` varchar(255) NOT NULL DEFAULT '',
    `wr_content` text NOT NULL,
    `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
    `wr_link1` text NOT NULL,
    `wr_link2` text NOT NULL,
    `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
    `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
    `wr_hit` int(11) NOT NULL DEFAULT '0',
    `wr_good` int(11) NOT NULL DEFAULT '0',
    `wr_nogood` int(11) NOT NULL DEFAULT '0',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `wr_password` varchar(255) NOT NULL DEFAULT '',
    `wr_name` varchar(255) NOT NULL DEFAULT '',
    `wr_email` varchar(255) NOT NULL DEFAULT '',
    `wr_homepage` varchar(255) NOT NULL DEFAULT '',
    `wr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wr_file` tinyint(4) NOT NULL DEFAULT '0',
    `wr_last` varchar(19) NOT NULL DEFAULT '',
    `wr_ip` varchar(255) NOT NULL DEFAULT '',
    `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
    `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
    `wr_1` varchar(255) NOT NULL DEFAULT '',
    `wr_2` varchar(255) NOT NULL DEFAULT '',
    `wr_3` varchar(255) NOT NULL DEFAULT '',
    `wr_4` varchar(255) NOT NULL DEFAULT '',
    `wr_5` varchar(255) NOT NULL DEFAULT '',
    `wr_6` varchar(255) NOT NULL DEFAULT '',
    `wr_7` varchar(255) NOT NULL DEFAULT '',
    `wr_8` varchar(255) NOT NULL DEFAULT '',
    `wr_9` varchar(255) NOT NULL DEFAULT '',
    `wr_10` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`wr_id`),
    KEY `wr_seo_title` (`wr_seo_title`),
    KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
    KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `g5_write_vent` (
    `wr_id` int(11) NOT NULL AUTO_INCREMENT,
    `wr_num` int(11) NOT NULL DEFAULT '0',
    `wr_reply` varchar(10) NOT NULL DEFAULT '',
    `wr_parent` int(11) NOT NULL DEFAULT '0',
    `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
    `wr_comment` int(11) NOT NULL DEFAULT '0',
    `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
    `ca_name` varchar(255) NOT NULL DEFAULT '',
    `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
    `wr_subject` varchar(255) NOT NULL DEFAULT '',
    `wr_content` text NOT NULL,
    `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
    `wr_link1` text NOT NULL,
    `wr_link2` text NOT NULL,
    `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
    `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
    `wr_hit` int(11) NOT NULL DEFAULT '0',
    `wr_good` int(11) NOT NULL DEFAULT '0',
    `wr_nogood` int(11) NOT NULL DEFAULT '0',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `wr_password` varchar(255) NOT NULL DEFAULT '',
    `wr_name` varchar(255) NOT NULL DEFAULT '',
    `wr_email` varchar(255) NOT NULL DEFAULT '',
    `wr_homepage` varchar(255) NOT NULL DEFAULT '',
    `wr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wr_file` tinyint(4) NOT NULL DEFAULT '0',
    `wr_last` varchar(19) NOT NULL DEFAULT '',
    `wr_ip` varchar(255) NOT NULL DEFAULT '',
    `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
    `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
    `wr_1` varchar(255) NOT NULL DEFAULT '',
    `wr_2` varchar(255) NOT NULL DEFAULT '',
    `wr_3` varchar(255) NOT NULL DEFAULT '',
    `wr_4` varchar(255) NOT NULL DEFAULT '',
    `wr_5` varchar(255) NOT NULL DEFAULT '',
    `wr_6` varchar(255) NOT NULL DEFAULT '',
    `wr_7` varchar(255) NOT NULL DEFAULT '',
    `wr_8` varchar(255) NOT NULL DEFAULT '',
    `wr_9` varchar(255) NOT NULL DEFAULT '',
    `wr_10` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`wr_id`),
    KEY `wr_seo_title` (`wr_seo_title`),
    KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
    KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `g5_write_log` (
    `wr_id` int(11) NOT NULL AUTO_INCREMENT,
    `wr_num` int(11) NOT NULL DEFAULT '0',
    `wr_reply` varchar(10) NOT NULL DEFAULT '',
    `wr_parent` int(11) NOT NULL DEFAULT '0',
    `wr_is_comment` tinyint(4) NOT NULL DEFAULT '0',
    `wr_comment` int(11) NOT NULL DEFAULT '0',
    `wr_comment_reply` varchar(5) NOT NULL DEFAULT '',
    `ca_name` varchar(255) NOT NULL DEFAULT '',
    `wr_option` set('html1','html2','secret','mail') NOT NULL DEFAULT '',
    `wr_subject` varchar(255) NOT NULL DEFAULT '',
    `wr_content` text NOT NULL,
    `wr_seo_title` varchar(255) NOT NULL DEFAULT '',
    `wr_link1` text NOT NULL,
    `wr_link2` text NOT NULL,
    `wr_link1_hit` int(11) NOT NULL DEFAULT '0',
    `wr_link2_hit` int(11) NOT NULL DEFAULT '0',
    `wr_hit` int(11) NOT NULL DEFAULT '0',
    `wr_good` int(11) NOT NULL DEFAULT '0',
    `wr_nogood` int(11) NOT NULL DEFAULT '0',
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `wr_password` varchar(255) NOT NULL DEFAULT '',
    `wr_name` varchar(255) NOT NULL DEFAULT '',
    `wr_email` varchar(255) NOT NULL DEFAULT '',
    `wr_homepage` varchar(255) NOT NULL DEFAULT '',
    `wr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `wr_file` tinyint(4) NOT NULL DEFAULT '0',
    `wr_last` varchar(19) NOT NULL DEFAULT '',
    `wr_ip` varchar(255) NOT NULL DEFAULT '',
    `wr_facebook_user` varchar(255) NOT NULL DEFAULT '',
    `wr_twitter_user` varchar(255) NOT NULL DEFAULT '',
    `wr_1` varchar(255) NOT NULL DEFAULT '',
    `wr_2` varchar(255) NOT NULL DEFAULT '',
    `wr_3` varchar(255) NOT NULL DEFAULT '',
    `wr_4` varchar(255) NOT NULL DEFAULT '',
    `wr_5` varchar(255) NOT NULL DEFAULT '',
    `wr_6` varchar(255) NOT NULL DEFAULT '',
    `wr_7` varchar(255) NOT NULL DEFAULT '',
    `wr_8` varchar(255) NOT NULL DEFAULT '',
    `wr_9` varchar(255) NOT NULL DEFAULT '',
    `wr_10` varchar(255) NOT NULL DEFAULT '',
    PRIMARY KEY (`wr_id`),
    KEY `wr_seo_title` (`wr_seo_title`),
    KEY `wr_num_reply_parent` (`wr_num`,`wr_reply`,`wr_parent`),
    KEY `wr_is_comment` (`wr_is_comment`,`wr_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- mission (프롬프트 미션) - prompt 스킨, 회원 글쓰기/댓글, 추천 활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'mission',
    `gr_id` = 'community',
    `bo_subject` = '프롬프트 미션',
    `bo_device` = 'both',
    `bo_admin` = '',
    `bo_list_level` = 1,
    `bo_read_level` = 1,
    `bo_write_level` = 2,
    `bo_reply_level` = 2,
    `bo_comment_level` = 2,
    `bo_upload_level` = 2,
    `bo_download_level` = 1,
    `bo_html_level` = 1,
    `bo_link_level` = 1,
    `bo_count_modify` = 1,
    `bo_count_delete` = 1,
    `bo_read_point` = 0,
    `bo_write_point` = 0,
    `bo_comment_point` = 1,
    `bo_download_point` = 0,
    `bo_use_category` = 0,
    `bo_category_list` = '',
    `bo_use_sideview` = 0,
    `bo_use_file_content` = 0,
    `bo_use_secret` = 0,
    `bo_use_dhtml_editor` = 1,
    `bo_select_editor` = '',
    `bo_use_rss_view` = 0,
    `bo_use_good` = 1,
    `bo_use_nogood` = 0,
    `bo_use_name` = 0,
    `bo_use_signature` = 0,
    `bo_use_ip_view` = 0,
    `bo_use_list_view` = 0,
    `bo_use_list_file` = 0,
    `bo_use_list_content` = 0,
    `bo_table_width` = 100,
    `bo_subject_len` = 60,
    `bo_mobile_subject_len` = 30,
    `bo_page_rows` = 15,
    `bo_mobile_page_rows` = 15,
    `bo_new` = 24,
    `bo_hot` = 100,
    `bo_image_width` = 835,
    `bo_skin` = 'theme/prompt',
    `bo_mobile_skin` = 'theme/prompt',
    `bo_include_head` = '_head.php',
    `bo_include_tail` = '_tail.php',
    `bo_content_head` = '',
    `bo_mobile_content_head` = '',
    `bo_content_tail` = '',
    `bo_mobile_content_tail` = '',
    `bo_insert_content` = '',
    `bo_gallery_cols` = 4,
    `bo_gallery_width` = 202,
    `bo_gallery_height` = 150,
    `bo_mobile_gallery_width` = 125,
    `bo_mobile_gallery_height` = 100,
    `bo_upload_count` = 2,
    `bo_upload_size` = 2097152,
    `bo_reply_order` = 1,
    `bo_use_search` = 1,
    `bo_order` = 10,
    `bo_count_write` = 0,
    `bo_count_comment` = 0,
    `bo_write_min` = 0,
    `bo_write_max` = 0,
    `bo_comment_min` = 0,
    `bo_comment_max` = 0,
    `bo_notice` = '',
    `bo_use_email` = 0,
    `bo_use_cert` = '',
    `bo_use_sns` = 0,
    `bo_use_captcha` = 0,
    `bo_sort_field` = '',
    `bo_1_subj` = '', `bo_2_subj` = '', `bo_3_subj` = '', `bo_4_subj` = '', `bo_5_subj` = '',
    `bo_6_subj` = '', `bo_7_subj` = '', `bo_8_subj` = '', `bo_9_subj` = '', `bo_10_subj` = '',
    `bo_1` = '', `bo_2` = '', `bo_3` = '', `bo_4` = '', `bo_5` = '',
    `bo_6` = '', `bo_7` = '', `bo_8` = '', `bo_9` = '', `bo_10` = ''
ON DUPLICATE KEY UPDATE `bo_subject` = VALUES(`bo_subject`);

-- ======================================
-- 8. 상점 아이템 타입 확장 (이모티콘 등록권)
-- ======================================
ALTER TABLE `mg_shop_item` MODIFY `si_type`
    enum('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','etc')
    NOT NULL DEFAULT 'etc' COMMENT '타입';

-- ======================================
-- 9. 개척 시스템 (Pioneer System)
-- ======================================

-- 재료 종류 정의
CREATE TABLE IF NOT EXISTS `mg_material_type` (
    `mt_id` int(11) NOT NULL AUTO_INCREMENT,
    `mt_name` varchar(50) NOT NULL COMMENT '재료 이름',
    `mt_code` varchar(30) NOT NULL COMMENT '코드 (wood, stone 등)',
    `mt_icon` varchar(200) NOT NULL DEFAULT '' COMMENT '아이콘 이미지/이모지',
    `mt_desc` varchar(200) NOT NULL DEFAULT '' COMMENT '설명',
    `mt_order` int(11) NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    PRIMARY KEY (`mt_id`),
    UNIQUE KEY `mt_code` (`mt_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='개척 재료 종류';

-- 기본 재료 데이터
INSERT INTO `mg_material_type` (`mt_name`, `mt_code`, `mt_icon`, `mt_desc`, `mt_order`) VALUES
('목재', 'wood', '🪵', '나무를 가공한 기본 건축 재료', 1),
('석재', 'stone', '🪨', '돌을 다듬어 만든 기본 건축 재료', 2),
('철광석', 'iron', '⛏️', '금속 가공에 필요한 광물', 3),
('유리', 'glass', '🪟', '모래를 녹여 만든 투명한 재료', 4),
('책', 'book', '📚', '지식이 담긴 서적', 5),
('마법석', 'crystal', '💎', '마력이 깃든 희귀한 보석', 6);

-- 유저별 재료 보유량
CREATE TABLE IF NOT EXISTS `mg_user_material` (
    `um_id` int(11) NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `mt_id` int(11) NOT NULL COMMENT '재료 종류',
    `um_count` int(11) NOT NULL DEFAULT 0 COMMENT '보유 수량',
    PRIMARY KEY (`um_id`),
    UNIQUE KEY `mb_mt` (`mb_id`, `mt_id`),
    KEY `mt_id` (`mt_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='유저별 재료 보유량';

-- 유저별 노동력
CREATE TABLE IF NOT EXISTS `mg_user_stamina` (
    `us_id` int(11) NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `us_current` int(11) NOT NULL DEFAULT 10 COMMENT '현재 노동력',
    `us_max` int(11) NOT NULL DEFAULT 10 COMMENT '일일 최대',
    `us_last_reset` date DEFAULT NULL COMMENT '마지막 리셋 날짜',
    PRIMARY KEY (`us_id`),
    UNIQUE KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='유저별 노동력';

-- 시설 정의
CREATE TABLE IF NOT EXISTS `mg_facility` (
    `fc_id` int(11) NOT NULL AUTO_INCREMENT,
    `fc_name` varchar(100) NOT NULL COMMENT '시설 이름',
    `fc_desc` text COMMENT '설명',
    `fc_image` varchar(500) NOT NULL DEFAULT '' COMMENT '시설 이미지',
    `fc_icon` varchar(100) NOT NULL DEFAULT '' COMMENT '아이콘',
    `fc_status` enum('locked','building','complete') NOT NULL DEFAULT 'locked' COMMENT '상태',
    `fc_unlock_type` varchar(50) NOT NULL DEFAULT '' COMMENT '해금 대상 타입 (board, shop, gift, achievement, history, fountain)',
    `fc_unlock_target` varchar(100) NOT NULL DEFAULT '' COMMENT '해금 대상 ID (게시판: bo_table, 그 외: 식별자)',
    `fc_stamina_cost` int(11) NOT NULL DEFAULT 0 COMMENT '필요 총 노동력',
    `fc_stamina_current` int(11) NOT NULL DEFAULT 0 COMMENT '현재 투입된 노동력',
    `fc_order` int(11) NOT NULL DEFAULT 0 COMMENT '표시 순서',
    `fc_complete_date` datetime DEFAULT NULL COMMENT '완공일',
    PRIMARY KEY (`fc_id`),
    KEY `fc_status` (`fc_status`),
    KEY `fc_order` (`fc_order`),
    KEY `fc_unlock` (`fc_unlock_type`, `fc_unlock_target`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='개척 시설';

-- 시설별 필요 재료
CREATE TABLE IF NOT EXISTS `mg_facility_material_cost` (
    `fmc_id` int(11) NOT NULL AUTO_INCREMENT,
    `fc_id` int(11) NOT NULL COMMENT '시설 ID',
    `mt_id` int(11) NOT NULL COMMENT '재료 종류',
    `fmc_required` int(11) NOT NULL DEFAULT 0 COMMENT '필요 수량',
    `fmc_current` int(11) NOT NULL DEFAULT 0 COMMENT '현재 투입된 수량',
    PRIMARY KEY (`fmc_id`),
    UNIQUE KEY `fc_mt` (`fc_id`, `mt_id`),
    KEY `mt_id` (`mt_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='시설별 필요 재료';

-- 기여 기록
CREATE TABLE IF NOT EXISTS `mg_facility_contribution` (
    `fcn_id` int(11) NOT NULL AUTO_INCREMENT,
    `fc_id` int(11) NOT NULL COMMENT '시설 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `fcn_type` enum('stamina','material') NOT NULL COMMENT '기여 유형',
    `mt_id` int(11) DEFAULT NULL COMMENT '재료 종류 (type=material일 때)',
    `fcn_amount` int(11) NOT NULL DEFAULT 0 COMMENT '투입량',
    `fcn_datetime` datetime NOT NULL COMMENT '투입 시각',
    PRIMARY KEY (`fcn_id`),
    KEY `fc_id` (`fc_id`),
    KEY `mb_id` (`mb_id`),
    KEY `fcn_type` (`fcn_type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='시설 기여 기록';

-- 명예의 전당 (완공 후 확정)
CREATE TABLE IF NOT EXISTS `mg_facility_honor` (
    `fh_id` int(11) NOT NULL AUTO_INCREMENT,
    `fc_id` int(11) NOT NULL COMMENT '시설 ID',
    `fh_rank` int(11) NOT NULL COMMENT '순위 (1, 2, 3)',
    `fh_category` varchar(30) NOT NULL COMMENT '카테고리 (stamina, wood, stone 등)',
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `fh_amount` int(11) NOT NULL DEFAULT 0 COMMENT '총 기여량',
    PRIMARY KEY (`fh_id`),
    KEY `fc_id` (`fc_id`),
    KEY `fh_category` (`fh_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='시설 명예의 전당';

-- ======================================
-- ======================================
-- 10. 보상 시스템
-- ======================================

-- 10.1 게시판별 보상 설정
CREATE TABLE IF NOT EXISTS `mg_board_reward` (
    `br_id` int NOT NULL AUTO_INCREMENT,
    `bo_table` varchar(20) NOT NULL,
    `br_mode` enum('auto','request','off') NOT NULL DEFAULT 'off',
    `br_point` int NOT NULL DEFAULT 0 COMMENT '기본 포인트',
    `br_bonus_500` int NOT NULL DEFAULT 0 COMMENT '500자 이상 보너스',
    `br_bonus_1000` int NOT NULL DEFAULT 0 COMMENT '1000자 이상 보너스',
    `br_bonus_image` int NOT NULL DEFAULT 0 COMMENT '이미지 첨부 보너스',
    `br_material_use` tinyint NOT NULL DEFAULT 0 COMMENT '재료 드롭 사용',
    `br_material_chance` int NOT NULL DEFAULT 30 COMMENT '드롭 확률 (0~100)',
    `br_material_list` text COMMENT '드롭 대상 재료 JSON ["wood","stone"]',
    `br_daily_limit` int NOT NULL DEFAULT 0 COMMENT '일일 보상 횟수 (0=무제한)',
    `br_like_use` tinyint NOT NULL DEFAULT 1 COMMENT '좋아요 보상 활성화 (0=비활성)',
    PRIMARY KEY (`br_id`),
    UNIQUE KEY `idx_bo_table` (`bo_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게시판별 보상 설정';

-- 10.2 좋아요 보상 로그
CREATE TABLE IF NOT EXISTS `mg_like_log` (
    `ll_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '좋아요 누른 회원',
    `target_mb_id` varchar(20) NOT NULL COMMENT '좋아요 받은 회원',
    `bo_table` varchar(20) NOT NULL COMMENT '게시판',
    `wr_id` int NOT NULL COMMENT '게시글 ID',
    `ll_giver_point` int NOT NULL DEFAULT 0 COMMENT '누른 사람 보상',
    `ll_receiver_point` int NOT NULL DEFAULT 0 COMMENT '받은 사람 보상',
    `ll_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ll_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_target` (`target_mb_id`),
    INDEX `idx_datetime` (`ll_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='좋아요 보상 로그';

-- 10.3 일일 좋아요 카운터
CREATE TABLE IF NOT EXISTS `mg_like_daily` (
    `ld_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL,
    `ld_date` date NOT NULL,
    `ld_count` int NOT NULL DEFAULT 0 COMMENT '오늘 사용 횟수',
    `ld_targets` text COMMENT '오늘 좋아요 준 대상 JSON',
    PRIMARY KEY (`ld_id`),
    UNIQUE KEY `idx_mb_date` (`mb_id`, `ld_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='일일 좋아요 카운터';

-- 10.4 보상 요청 유형 (request 모드용)
CREATE TABLE IF NOT EXISTS `mg_reward_type` (
    `rwt_id` int NOT NULL AUTO_INCREMENT,
    `bo_table` varchar(20) DEFAULT NULL COMMENT '게시판 (NULL=전체 적용)',
    `rwt_name` varchar(100) NOT NULL COMMENT '유형 이름',
    `rwt_point` int NOT NULL DEFAULT 0 COMMENT '포인트 보상',
    `rwt_material` text COMMENT '재료 보상 JSON',
    `rwt_desc` varchar(255) DEFAULT '' COMMENT '유저 가이드 텍스트',
    `rwt_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `rwt_use` tinyint NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`rwt_id`),
    INDEX `idx_bo_table` (`bo_table`, `rwt_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='보상 요청 유형';

-- 10.5 정산 대기열
CREATE TABLE IF NOT EXISTS `mg_reward_queue` (
    `rq_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '요청 회원',
    `bo_table` varchar(20) NOT NULL COMMENT '게시판',
    `wr_id` int NOT NULL COMMENT '게시글 ID',
    `rwt_id` int NOT NULL COMMENT '보상 유형 ID',
    `rq_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `rq_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `rq_process_datetime` datetime DEFAULT NULL COMMENT '처리일',
    `rq_process_mb_id` varchar(20) DEFAULT NULL COMMENT '처리 스탭',
    `rq_reject_reason` varchar(255) DEFAULT NULL COMMENT '반려 사유',
    PRIMARY KEY (`rq_id`),
    INDEX `idx_status` (`rq_status`, `rq_datetime`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_bo_wr` (`bo_table`, `wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='정산 대기열';

-- 개척 시스템 기본 설정
-- ======================================
INSERT INTO `mg_config` (`cf_key`, `cf_value`, `cf_desc`) VALUES
('pioneer_enabled', '1', '개척 시스템 활성화'),
('pioneer_stamina_default', '10', '기본 일일 노동력'),
('pioneer_write_reward', 'wood:1', '글 작성 시 재료 보상'),
('pioneer_comment_reward', 'random:1:30', '댓글 작성 시 재료 보상 (30% 확률)'),
('pioneer_rp_reward', 'stone:1', 'RP 이음 시 재료 보상'),
('pioneer_attendance_reward', 'random:1:100', '출석 시 재료 보상')
ON DUPLICATE KEY UPDATE `cf_key` = `cf_key`;

-- 샘플 시설 (앓이란, 역극 게시판 해금)
INSERT INTO `mg_facility` (`fc_name`, `fc_desc`, `fc_icon`, `fc_status`, `fc_unlock_type`, `fc_unlock_target`, `fc_stamina_cost`, `fc_order`) VALUES
('앓이란 게시판', '캐릭터의 앓이를 공유하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', 'heart', 'locked', 'board', 'ailiran', 100, 1),
('역극 게시판', '역할극을 진행하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', 'theater', 'locked', 'board', 'roleplay', 150, 2),
('상점', '포인트로 아이템을 구매할 수 있는 상점입니다.', 'shopping-bag', 'locked', 'shop', '', 200, 3),
('선물함', '다른 유저에게 선물을 보낼 수 있습니다.', 'gift', 'locked', 'gift', '', 120, 4)
ON DUPLICATE KEY UPDATE `fc_name` = VALUES(`fc_name`);

-- ======================================
-- 11. 업적 시스템 (Achievement System)
-- ======================================

-- 11.1 업적 정의
CREATE TABLE IF NOT EXISTS `mg_achievement` (
    `ac_id` int NOT NULL AUTO_INCREMENT,
    `ac_name` varchar(100) NOT NULL COMMENT '업적 이름',
    `ac_desc` text COMMENT '설명',
    `ac_icon` varchar(500) DEFAULT NULL COMMENT '아이콘 이미지 경로',
    `ac_category` varchar(30) NOT NULL DEFAULT 'activity' COMMENT '카테고리 (activity, rp, pioneer, social, collection, special)',
    `ac_type` enum('progressive','onetime') NOT NULL DEFAULT 'onetime' COMMENT '유형',
    `ac_condition` text COMMENT '달성 조건 JSON {"type":"write_count","target":100}',
    `ac_reward` text COMMENT '일회성 업적 보상 JSON {"type":"point","amount":500}',
    `ac_rarity` enum('common','uncommon','rare','epic','legendary') DEFAULT NULL COMMENT '희귀도 (NULL=자동산정)',
    `ac_hidden` tinyint NOT NULL DEFAULT 0 COMMENT '숨겨진 업적 (조건 ???)',
    `ac_order` int NOT NULL DEFAULT 0 COMMENT '표시 순서',
    `ac_use` tinyint NOT NULL DEFAULT 1 COMMENT '사용 여부',
    `ac_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ac_id`),
    INDEX `idx_category` (`ac_category`, `ac_order`),
    INDEX `idx_use` (`ac_use`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='업적 정의';

-- 11.2 단계형 업적의 각 단계
CREATE TABLE IF NOT EXISTS `mg_achievement_tier` (
    `at_id` int NOT NULL AUTO_INCREMENT,
    `ac_id` int NOT NULL COMMENT '업적 ID',
    `at_level` int NOT NULL COMMENT '단계 (1, 2, 3...)',
    `at_name` varchar(100) NOT NULL COMMENT '단계 이름 (글쟁이 I, 글쟁이 II...)',
    `at_target` int NOT NULL COMMENT '목표 수치 (10, 50, 100...)',
    `at_icon` varchar(500) DEFAULT NULL COMMENT '단계별 아이콘 (NULL=업적 기본 아이콘)',
    `at_reward` text COMMENT '단계별 보상 JSON',
    PRIMARY KEY (`at_id`),
    UNIQUE KEY `idx_ac_level` (`ac_id`, `at_level`),
    INDEX `idx_ac_id` (`ac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='업적 단계';

-- 11.3 유저별 달성 상태
CREATE TABLE IF NOT EXISTS `mg_user_achievement` (
    `ua_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `ac_id` int NOT NULL COMMENT '업적 ID',
    `ua_progress` int NOT NULL DEFAULT 0 COMMENT '현재 진행값',
    `ua_tier` int NOT NULL DEFAULT 0 COMMENT '달성한 최고 단계 (0=미달성)',
    `ua_completed` tinyint NOT NULL DEFAULT 0 COMMENT '완전 달성 여부',
    `ua_granted_by` varchar(20) DEFAULT NULL COMMENT '수동 부여자 (NULL=자동)',
    `ua_grant_memo` varchar(255) DEFAULT NULL COMMENT '부여 사유',
    `ua_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '최종 갱신 시각',
    PRIMARY KEY (`ua_id`),
    UNIQUE KEY `idx_mb_ac` (`mb_id`, `ac_id`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_ac_id` (`ac_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='유저별 업적 달성';

-- 11.4 프로필 쇼케이스
CREATE TABLE IF NOT EXISTS `mg_user_achievement_display` (
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `slot_1` int DEFAULT NULL COMMENT '업적 ID',
    `slot_2` int DEFAULT NULL,
    `slot_3` int DEFAULT NULL,
    `slot_4` int DEFAULT NULL,
    `slot_5` int DEFAULT NULL,
    PRIMARY KEY (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='업적 쇼케이스';

-- ======================================
-- 12. 인장 시스템 (Seal / Signature Card)
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_seal` (
    `seal_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `seal_use` tinyint NOT NULL DEFAULT 1 COMMENT '인장 사용 여부',
    `seal_tagline` varchar(100) DEFAULT NULL COMMENT '한마디',
    `seal_content` text COMMENT '자유 영역 텍스트',
    `seal_image` varchar(500) DEFAULT NULL COMMENT '이미지 경로',
    `seal_link` varchar(500) DEFAULT NULL COMMENT '링크 URL',
    `seal_link_text` varchar(100) DEFAULT NULL COMMENT '링크 텍스트',
    `seal_text_color` varchar(7) DEFAULT NULL COMMENT '텍스트 색상',
    `seal_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '최종 수정일',
    PRIMARY KEY (`seal_id`),
    UNIQUE KEY `idx_mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='인장 (시그니처 카드)';

-- 상점 아이템 타입 확장 (인장 배경/프레임)
ALTER TABLE `mg_shop_item` MODIFY `si_type`
    enum('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','etc')
    NOT NULL DEFAULT 'etc' COMMENT '타입';

-- ======================================
-- 13. 세계관 위키 (Lore Wiki)
-- ======================================

-- 13.1 위키 카테고리
CREATE TABLE IF NOT EXISTS `mg_lore_category` (
    `lc_id` int NOT NULL AUTO_INCREMENT,
    `lc_name` varchar(100) NOT NULL COMMENT '카테고리명',
    `lc_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `lc_use` tinyint NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`lc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='위키 카테고리';

-- 13.2 위키 문서
CREATE TABLE IF NOT EXISTS `mg_lore_article` (
    `la_id` int NOT NULL AUTO_INCREMENT,
    `lc_id` int NOT NULL COMMENT '카테고리 ID',
    `la_title` varchar(200) NOT NULL COMMENT '문서 제목',
    `la_subtitle` varchar(300) DEFAULT NULL COMMENT '부제목',
    `la_thumbnail` varchar(500) DEFAULT NULL COMMENT '썸네일 이미지',
    `la_summary` text DEFAULT NULL COMMENT '한줄 요약',
    `la_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `la_use` tinyint NOT NULL DEFAULT 1 COMMENT '공개 여부',
    `la_hit` int NOT NULL DEFAULT 0 COMMENT '조회수',
    `la_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '작성일',
    `la_updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    PRIMARY KEY (`la_id`),
    INDEX `idx_lc_id` (`lc_id`, `la_order`),
    INDEX `idx_use` (`la_use`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='위키 문서';

-- 13.3 위키 섹션
CREATE TABLE IF NOT EXISTS `mg_lore_section` (
    `ls_id` int NOT NULL AUTO_INCREMENT,
    `la_id` int NOT NULL COMMENT '문서 ID',
    `ls_name` varchar(200) NOT NULL COMMENT '섹션 제목',
    `ls_type` enum('text','image') NOT NULL DEFAULT 'text' COMMENT '콘텐츠 타입',
    `ls_content` text DEFAULT NULL COMMENT '텍스트 내용',
    `ls_image` varchar(500) DEFAULT NULL COMMENT '이미지 경로',
    `ls_image_caption` varchar(300) DEFAULT NULL COMMENT '이미지 캡션',
    `ls_order` int NOT NULL DEFAULT 0 COMMENT '섹션 순서',
    PRIMARY KEY (`ls_id`),
    INDEX `idx_la_id` (`la_id`, `ls_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='위키 섹션';

-- 13.4 타임라인 시대
CREATE TABLE IF NOT EXISTS `mg_lore_era` (
    `le_id` int NOT NULL AUTO_INCREMENT,
    `le_name` varchar(200) NOT NULL COMMENT '시대명',
    `le_period` varchar(100) DEFAULT NULL COMMENT '기간 표기',
    `le_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `le_use` tinyint NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`le_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='타임라인 시대';

-- 13.5 타임라인 이벤트
CREATE TABLE IF NOT EXISTS `mg_lore_event` (
    `lv_id` int NOT NULL AUTO_INCREMENT,
    `le_id` int NOT NULL COMMENT '시대 ID',
    `lv_year` varchar(50) DEFAULT NULL COMMENT '연도 표기',
    `lv_title` varchar(200) NOT NULL COMMENT '이벤트 제목',
    `lv_content` text DEFAULT NULL COMMENT '이벤트 설명',
    `lv_image` varchar(500) DEFAULT NULL COMMENT '이벤트 이미지',
    `lv_is_major` tinyint NOT NULL DEFAULT 0 COMMENT '주요 이벤트 여부',
    `lv_order` int NOT NULL DEFAULT 0 COMMENT '시대 내 순서',
    `lv_use` tinyint NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`lv_id`),
    INDEX `idx_le_id` (`le_id`, `lv_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='타임라인 이벤트';

-- 13.6 위키 기본 설정
INSERT INTO `mg_config` (`cf_key`, `cf_value`, `cf_desc`) VALUES
('lore_use', '1', '세계관 위키 사용 여부'),
('lore_image_max_size', '2048', '위키 이미지 최대 크기 (KB)'),
('lore_thumbnail_max_size', '500', '위키 썸네일 최대 크기 (KB)'),
('lore_articles_per_page', '12', '위키 페이지당 문서 수')
ON DUPLICATE KEY UPDATE `cf_key` = `cf_key`;

-- ======================================
-- 14. 프롬프트 미션 시스템
-- ======================================

-- 14.1 프롬프트(미션) 정의
CREATE TABLE IF NOT EXISTS `mg_prompt` (
    `pm_id` int NOT NULL AUTO_INCREMENT,
    `bo_table` varchar(20) NOT NULL COMMENT '대상 게시판',
    `pm_title` varchar(200) NOT NULL COMMENT '미션 제목',
    `pm_content` text DEFAULT NULL COMMENT '미션 설명',
    `pm_cycle` enum('weekly','monthly','event') NOT NULL DEFAULT 'weekly' COMMENT '주기',
    `pm_mode` enum('auto','review','vote') NOT NULL DEFAULT 'review' COMMENT '보상 모드',
    `pm_point` int NOT NULL DEFAULT 0 COMMENT '기본 참여 보상 (포인트)',
    `pm_bonus_point` int NOT NULL DEFAULT 0 COMMENT '우수작 추가 보상',
    `pm_bonus_count` int NOT NULL DEFAULT 0 COMMENT '우수작 선정 인원',
    `pm_material_id` int DEFAULT NULL COMMENT '재료 보상 ID',
    `pm_material_qty` int NOT NULL DEFAULT 0 COMMENT '재료 보상 수량',
    `pm_min_chars` int NOT NULL DEFAULT 0 COMMENT '최소 글자수 (0=제한없음)',
    `pm_max_entry` int NOT NULL DEFAULT 1 COMMENT '인당 최대 제출 수',
    `pm_banner` varchar(500) DEFAULT NULL COMMENT '배너 이미지 경로',
    `pm_tags` varchar(200) DEFAULT NULL COMMENT '태그 (쉼표 구분)',
    `pm_status` enum('draft','active','closed','archived') NOT NULL DEFAULT 'draft' COMMENT '상태',
    `pm_start_date` datetime DEFAULT NULL COMMENT '시작일',
    `pm_end_date` datetime DEFAULT NULL COMMENT '종료일',
    `pm_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    `pm_admin_id` varchar(20) DEFAULT NULL COMMENT '등록 관리자',
    PRIMARY KEY (`pm_id`),
    INDEX `idx_prompt_board` (`bo_table`, `pm_status`, `pm_end_date`),
    INDEX `idx_prompt_status` (`pm_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='프롬프트 미션';

-- 14.2 제출(참여) 기록
CREATE TABLE IF NOT EXISTS `mg_prompt_entry` (
    `pe_id` int NOT NULL AUTO_INCREMENT,
    `pm_id` int NOT NULL COMMENT '프롬프트 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '제출 회원',
    `wr_id` int NOT NULL DEFAULT 0 COMMENT '연결된 게시글 ID',
    `bo_table` varchar(20) NOT NULL COMMENT '게시판',
    `pe_status` enum('submitted','approved','rejected','rewarded') NOT NULL DEFAULT 'submitted' COMMENT '상태',
    `pe_point` int NOT NULL DEFAULT 0 COMMENT '지급된 포인트',
    `pe_is_bonus` tinyint NOT NULL DEFAULT 0 COMMENT '우수작 선정 여부',
    `pe_admin_id` varchar(20) DEFAULT NULL COMMENT '검수 관리자',
    `pe_admin_memo` varchar(300) DEFAULT NULL COMMENT '검수 메모',
    `pe_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '제출일',
    `pe_review_date` datetime DEFAULT NULL COMMENT '검수일',
    PRIMARY KEY (`pe_id`),
    INDEX `idx_entry_prompt` (`pm_id`, `pe_status`),
    INDEX `idx_entry_member` (`mb_id`, `pm_id`),
    INDEX `idx_entry_write` (`bo_table`, `wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='프롬프트 제출';

-- 14.3 프롬프트 기본 설정
INSERT INTO `mg_config` (`cf_key`, `cf_value`, `cf_desc`) VALUES
('prompt_enable', '1', '프롬프트 시스템 사용 여부'),
('prompt_show_closed', '3', '종료된 프롬프트 표시 개수'),
('prompt_notify_submit', '1', '제출 시 관리자 알림'),
('prompt_notify_approve', '1', '승인 시 유저 알림'),
('prompt_notify_reject', '1', '반려 시 유저 알림'),
('prompt_banner_max_size', '1024', '배너 이미지 최대 크기 (KB)')
ON DUPLICATE KEY UPDATE `cf_key` = `cf_key`;

SET FOREIGN_KEY_CHECKS = 1;
