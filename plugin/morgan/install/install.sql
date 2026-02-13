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

-- 2.7 메인 페이지 시드 데이터
INSERT INTO `mg_main_row` (`row_order`, `row_use`) VALUES (1, 1);

INSERT INTO `mg_main_widget` (`row_id`, `widget_type`, `widget_order`, `widget_cols`, `widget_config`, `widget_use`) VALUES
(1, 'image', 1, 8, '{"image_url":"","alt_text":"메인 이미지","max_width":"100%","border_radius":"none","align":"center"}', 1),
(1, 'image', 0, 4, '{"image_url":"","alt_text":"","max_width":"100%","border_radius":"none","align":"center"}', 1),
(1, 'text', 2, 3, '{"content":"환영합니다.","font_size":"base","font_weight":"normal","text_align":"left","padding":"normal","text_color":"#f2f3f5","bg_color":"#2b2d31"}', 1),
(1, 'slider', 3, 12, '{}', 1);

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

-- 4.3 기본 설정값 (전체)
INSERT INTO `mg_config` (`cf_key`, `cf_value`, `cf_desc`) VALUES
-- 사이트 기본
('site_name', 'Morgan Edition', '사이트 이름'),
('point_name', 'P', '포인트 단위'),
('login_point', '10', '로그인 포인트'),
('theme_primary_color', '#f59f0a', '테마 메인 컬러'),
('color_accent', '#f57c0a', '액센트 색상'),
('color_button', '#f59f0a', '버튼 색상'),
('color_border', '#313338', '테두리 색상'),
('color_bg_primary', '#1e1f22', '기본 배경 색상'),
('color_bg_secondary', '#2b2d31', '보조 배경 색상'),
('bg_opacity', '4', '배경 불투명도'),
('bg_image', '', '배경 이미지'),
-- 캐릭터
('character_approval', '1', '캐릭터 승인제 사용 (0: 즉시승인, 1: 관리자승인)'),
('character_max', '10', '회원당 최대 캐릭터 수'),
('character_create_point', '100', '캐릭터 생성 포인트'),
('max_characters', '10', '최대 캐릭터 수'),
('show_main_character', '1', '대표 캐릭터 표시'),
('use_side', '1', '세력 사용'),
('use_class', '1', '종족 사용'),
-- 출석
('attendance_point', '100', '출석 기본 포인트'),
('attendance_bonus', '500', '연속 출석 보너스 (7일)'),
('attendance_game', 'dice', '출석체크 미니게임 종류'),
('game_dice_min', '10', '주사위 최소 포인트'),
('game_dice_max', '100', '주사위 최대 포인트'),
('game_dice_bonus_multiplier', '2', '7일 연속 보너스 배율'),
-- 상점
('shop_use', '1', '상점 사용 여부'),
('shop_gift_use', '1', '선물 기능 사용 여부'),
-- 역극
('rp_use', '1', '역극 기능 사용 여부'),
('rp_require_reply', '0', '판 세우기 전 필요 이음 수'),
('rp_max_member_default', '0', '기본 최대 참여자 수 (0=무제한)'),
('rp_max_member_limit', '20', '참여자 상한선'),
('rp_content_min', '0', '최소 글자 수'),
-- 이모티콘
('emoticon_use', '1', '이모티콘 기능 사용 여부'),
('emoticon_creator_use', '1', '유저 이모티콘 제작 허용'),
('emoticon_commission_rate', '10', '판매 수수료율 (%)'),
('emoticon_min_count', '8', '셋 당 최소 이모티콘 수'),
('emoticon_max_count', '30', '셋 당 최대 이모티콘 수'),
('emoticon_image_max_size', '512', '이모티콘 이미지 최대 크기 (KB)'),
('emoticon_image_size', '128', '이모티콘 이미지 권장 크기 (px)'),
-- 개척
('pioneer_enabled', '1', '개척 시스템 활성화'),
('pioneer_stamina_default', '10', '기본 일일 노동력'),
('pioneer_write_reward', 'wood:1', '글 작성 시 재료 보상'),
('pioneer_comment_reward', 'random:1:30', '댓글 작성 시 재료 보상 (30% 확률)'),
('pioneer_rp_reward', 'stone:1', 'RP 이음 시 재료 보상'),
('pioneer_attendance_reward', 'random:1:100', '출석 시 재료 보상'),
-- 캡챠
('recaptcha_site_key', '', 'reCAPTCHA 사이트 키'),
('recaptcha_secret_key', '', 'reCAPTCHA 시크릿 키'),
('captcha_register', '1', '회원가입 캡챠'),
('captcha_write', '0', '글쓰기 캡챠'),
('captcha_comment', '0', '댓글 캡챠'),
-- 인장
('seal_enable', '1', '인장 시스템 ON/OFF'),
('seal_tagline_max', '50', '한마디 최대 글자수'),
('seal_content_max', '300', '자유 영역 최대 글자수'),
('seal_image_upload', '1', '이미지 업로드 허용'),
('seal_image_url', '1', '외부 이미지 URL 허용'),
('seal_image_max_size', '500', '이미지 최대 크기(KB)'),
('seal_link_allow', '1', '링크 허용'),
('seal_trophy_slots', '3', '트로피 슬롯 수'),
('seal_show_in_rp', '1', '역극에서 인장 표시'),
('seal_show_in_comment', '0', '댓글에서 인장 표시'),
('seal_compact_in_rp', '1', '역극에서 compact 모드'),
-- 세계관 위키
('lore_use', '1', '세계관 위키 사용 여부'),
('lore_image_max_size', '2048', '위키 이미지 최대 크기 (KB)'),
('lore_thumbnail_max_size', '500', '위키 썸네일 최대 크기 (KB)'),
('lore_articles_per_page', '12', '위키 페이지당 문서 수'),
-- 프롬프트
('prompt_enable', '1', '프롬프트 시스템 사용 여부'),
('prompt_show_closed', '3', '종료된 프롬프트 표시 개수'),
('prompt_notify_submit', '1', '제출 시 관리자 알림'),
('prompt_notify_approve', '1', '승인 시 유저 알림'),
('prompt_notify_reject', '1', '반려 시 유저 알림'),
('prompt_banner_max_size', '1024', '배너 이미지 최대 크기 (KB)')
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

-- 4.5 기본 상점 아이템
INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_image`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_stock_sold`, `si_limit_per_user`, `si_sale_start`, `si_sale_end`, `si_consumable`, `si_display`, `si_use`, `si_order`, `si_datetime`) VALUES
(1, '황금 닉네임', '닉네임이 황금색으로 빛납니다', NULL, 500, 'nick_color', '{"nick_color":"#fbbf24"}', -1, 0, 0, NULL, NULL, 0, 1, 1, 0, NOW()),
(1, '무지개 효과', '닉네임에 무지개 효과가 적용됩니다', NULL, 1000, 'nick_effect', '{"nick_effect":"rainbow"}', 10, 0, 0, NULL, NULL, 0, 1, 1, 0, NOW()),
(3, '파란 테두리', '프로필에 파란 테두리가 적용됩니다', NULL, 300, 'profile_border', '{"border_color":"#3b82f6","border_style":"solid"}', -1, 0, 0, NULL, NULL, 0, 1, 1, 0, NOW()),
(1, '별 뱃지', '반짝이는 별 뱃지입니다', NULL, 200, 'badge', '{"badge_icon":"star","badge_color":"#fbbf24"}', -1, 0, 0, NULL, NULL, 0, 1, 1, 0, NOW());

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
INSERT IGNORE INTO `mg_material_type` (`mt_name`, `mt_code`, `mt_icon`, `mt_desc`, `mt_order`) VALUES
('목재', 'wood', 'rectangle-stack', '나무를 가공한 기본 건축 재료', 1),
('석재', 'stone', 'archive-box', '돌을 다듬어 만든 기본 건축 재료', 2),
('철광석', 'iron', 'cube', '금속 가공에 필요한 광물', 3),
('유리', 'glass', 'squares-2x2', '모래를 녹여 만든 투명한 재료', 4),
('책', 'book', 'book-open', '지식이 담긴 서적', 5),
('마법석', 'crystal', 'sparkles', '마력이 깃든 희귀한 보석', 6);

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

-- 시설 재료 비용 시드 데이터 (우체국=fc_id 4, mt_id 1~6)
INSERT IGNORE INTO `mg_facility_material_cost` (`fc_id`, `mt_id`, `fmc_required`, `fmc_current`) VALUES
(4, 1, 10, 0),
(4, 2, 10, 0),
(4, 3, 10, 0),
(4, 4, 10, 0),
(4, 5, 10, 0),
(4, 6, 10, 0);

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
    `br_dice_use` tinyint NOT NULL DEFAULT 0 COMMENT '댓글 주사위 활성화',
    `br_dice_once` tinyint NOT NULL DEFAULT 1 COMMENT '1인 1회 제한',
    `br_dice_max` int NOT NULL DEFAULT 100 COMMENT '주사위 최대값',
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

-- 개척 시설 시드 데이터
INSERT INTO `mg_facility` (`fc_name`, `fc_desc`, `fc_image`, `fc_icon`, `fc_status`, `fc_unlock_type`, `fc_unlock_target`, `fc_stamina_cost`, `fc_stamina_current`, `fc_order`, `fc_complete_date`) VALUES
('역극 게시판', '역할극을 진행하는 공간입니다. 개척을 완료하면 이용할 수 있습니다.', '', 'sparkles', 'complete', 'board', 'roleplay', 150, 150, 2, '2026-02-09 16:02:36'),
('상점', '포인트로 아이템을 구매할 수 있는 상점입니다.', '', 'shopping-cart', 'complete', 'shop', '', 200, 200, 3, '2026-02-06 16:52:22'),
('선물함', '다른 유저에게 선물을 보낼 수 있습니다.', '', 'gift', 'complete', 'gift', '', 120, 120, 4, '2026-02-06 16:50:46'),
('우체국', '우체국을 건설합니다. 익명으로 편지를 전달할 수 있게 됩니다. (앓이란 해금)', '', 'envelope', 'building', 'board', 'vent', 1000, 0, 0, NULL)
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

-- 11.1a 업적 시드 데이터
INSERT INTO `mg_achievement` (`ac_name`, `ac_desc`, `ac_icon`, `ac_category`, `ac_type`, `ac_condition`, `ac_reward`, `ac_rarity`, `ac_hidden`, `ac_order`, `ac_use`, `ac_datetime`) VALUES
('글쟁이', '게시글을 작성하여 활동하세요', NULL, 'activity', 'progressive', '{"type":"write_count","target":100}', '{"type":"point","amount":500}', 'common', 0, 1, 1, NOW()),
('수다쟁이', '댓글을 많이 남겨보세요', NULL, 'activity', 'progressive', '{"type":"comment_count","target":200}', '{"type":"point","amount":500}', 'common', 0, 2, 1, NOW()),
('역극 마스터', 'RP 답글을 꾸준히 작성하세요', NULL, 'rp', 'progressive', '{"type":"rp_reply_count","target":100}', '{"type":"point","amount":1000}', 'uncommon', 0, 3, 1, NOW()),
('개근왕', '출석체크를 꾸준히 하세요', NULL, 'activity', 'progressive', '{"type":"attendance_count","target":365}', '{"type":"point","amount":2000}', 'rare', 0, 4, 1, NOW()),
('쇼핑홀릭', '상점에서 아이템을 구매하세요', NULL, 'collection', 'progressive', '{"type":"shop_buy_count","target":50}', '{"type":"point","amount":500}', 'common', 0, 5, 1, NOW()),
('첫 발자국', '첫 게시글을 작성하세요', NULL, 'activity', 'onetime', '{"type":"write_count","target":1}', '{"type":"point","amount":100}', 'common', 0, 10, 1, NOW()),
('캐릭터 마스터', '캐릭터를 3개 이상 만드세요', NULL, 'character', 'onetime', '{"type":"character_count","target":3}', '{"type":"point","amount":300}', 'uncommon', 0, 11, 1, NOW()),
('역극 개막', '첫 역극을 개설하세요', NULL, 'rp', 'onetime', '{"type":"rp_create_count","target":1}', '{"type":"point","amount":200}', 'common', 0, 12, 1, NOW()),
('전설의 시작', '레벨 10에 도달하세요', NULL, 'special', 'onetime', '{"type":"level","target":10}', '{"type":"title","value":"legend_start"}', 'legendary', 0, 20, 1, NOW()),
('개척자', '개척 시설에 기여하세요', NULL, 'pioneer', 'progressive', '{"type":"pioneer_contribute","target":50}', '{"type":"point","amount":1000}', 'rare', 0, 6, 1, NOW());

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

-- 11.2a 업적 단계 시드 데이터
INSERT IGNORE INTO `mg_achievement_tier` (`ac_id`, `at_level`, `at_name`, `at_target`, `at_icon`, `at_reward`) VALUES
(1, 1, '견습 작가', 5, NULL, '{"type":"point","amount":50}'),
(1, 2, '초보 작가', 20, NULL, '{"type":"point","amount":100}'),
(1, 3, '중급 작가', 50, NULL, '{"type":"point","amount":200}'),
(1, 4, '숙련 작가', 100, NULL, '{"type":"point","amount":500}'),
(2, 1, '수줍은 한마디', 10, NULL, '{"type":"point","amount":50}'),
(2, 2, '말걸기 달인', 50, NULL, '{"type":"point","amount":100}'),
(2, 3, '수다의 왕', 200, NULL, '{"type":"point","amount":300}'),
(3, 1, '역극 입문', 10, NULL, '{"type":"point","amount":100}'),
(3, 2, '역극 숙련', 30, NULL, '{"type":"point","amount":200}'),
(3, 3, '역극 달인', 60, NULL, '{"type":"point","amount":500}'),
(3, 4, '역극 마스터', 100, NULL, '{"type":"point","amount":1000}'),
(4, 1, '3일 연속', 3, NULL, '{"type":"point","amount":30}'),
(4, 2, '일주일 개근', 7, NULL, '{"type":"point","amount":70}'),
(4, 3, '한달 개근', 30, NULL, '{"type":"point","amount":300}'),
(4, 4, '분기 개근', 90, NULL, '{"type":"point","amount":900}'),
(4, 5, '1년 개근', 365, NULL, '{"type":"point","amount":3650}'),
(5, 1, '첫 구매', 1, NULL, '{"type":"point","amount":30}'),
(5, 2, '단골 손님', 10, NULL, '{"type":"point","amount":100}'),
(5, 3, 'VIP 고객', 30, NULL, '{"type":"point","amount":300}'),
(5, 4, '쇼핑홀릭', 50, NULL, '{"type":"point","amount":500}'),
(10, 1, '초보 개척자', 5, NULL, '{"type":"point","amount":50}'),
(10, 2, '숙련 개척자', 20, NULL, '{"type":"point","amount":200}'),
(10, 3, '마스터 개척자', 50, NULL, '{"type":"point","amount":1000}');

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

-- 13.6 위키 시드 데이터
INSERT INTO `mg_lore_category` (`lc_name`, `lc_order`, `lc_use`) VALUES
('세계관', 1, 1),
('세력', 2, 1),
('조직', 3, 1),
('지역', 4, 1);

INSERT INTO `mg_lore_article` (`lc_id`, `la_title`, `la_subtitle`, `la_thumbnail`, `la_summary`, `la_order`, `la_use`, `la_hit`, `la_created`, `la_updated`) VALUES
(1, '달그늘', '평범한 인간은 인지할 수 없는 세계의 이면', '', '뱀파이어, 라이칸스로프, 헌터가 공존하는 달 그림자 아래의 세계', 1, 1, 15, NOW(), NOW()),
(3, '주요 패밀리', '콘크리트 정글을 지배하는 야수들의 연맹', '', '블랙후프 — 라이칸스로프 5개 패밀리로 구성된 현대적 마피아 연맹', 1, 1, 10, NOW(), NOW()),
(4, '에제이카 주', '세 세력이 충돌하는 미국의 가상 도시', '', '가장 많은 클랜, 가장 거대한 패밀리, 가장 중요한 볼트가 모인 달그늘의 중심지', 1, 1, 13, NOW(), NOW());

INSERT INTO `mg_lore_section` (`la_id`, `ls_name`, `ls_type`, `ls_content`, `ls_image`, `ls_image_caption`, `ls_order`) VALUES
(1, '개요', 'text', '달그늘은 평범한 인간이 인지할 수 없는 세계의 이면입니다.\n피를 마시며 영생을 이어가는 뱀파이어, 인간들 틈에 숨어든 야수 라이칸스로프, 이들과 대립하며 인류의 존속을 비호하는 헌터. 달그늘 아래의 세계에서는 이러한 세 개의 세력이 대립하고 화합하며, 적대하고 협력하며, 공멸하고 공존하며 살아가고 있습니다.\n\n여러분은 이 달 그림자 아래로 발을 들이게 된 저주받은 죄인의 자손입니다.\n세 개의 세력 중 한 곳에 속한 당신은 스스로의 목표를 위해 이 괴물들의 틈바구니를 살아나가게 됩니다.\n달그늘 아래의 주민들은 모두가 평범한 인간을 초월한 능력을 가지게 되고, 언젠가는 평범한 일상 속에서 살아갈 수 없게 마모되고 맙니다.\n\n그렇기에 달그늘 아래의 주민들은 달의 그림자 아래의 세계, 달그늘 속에 머무를 수 밖에 없습니다.\n문명은 끝없이 발전하여 밤의 어둠을 몰아내고, 현대 사회에는 괴물들이 숨어들 틈이 없는 것처럼 보이지만, 달그늘 아래의 주민들은 모두 각자의 방식으로 인간들 사이에 숨어 현재를 살아갑니다.', '', '', 1),
(1, '세 개의 세력', 'text', '달그늘 아래의 주민들은 모두가 평범한 인간을 초월한 능력을 가지게 되고, 언젠가는 평범한 일상 속에서 살아갈 수 없게 마모되고 맙니다.\n그렇기에 달그늘 아래의 주민들은 달의 그림자 아래의 세계, 달그늘 속에 머무를 수밖에 없습니다.\n\n뱀파이어 (Vampire)\n피를 마시며 영생을 이어가는 밤의 귀족들입니다.\n7죄종의 저주를 이어받아 각기 다른 권능과 약점을 가지며, 클랜이라 불리는 혈연 중심의 가문 체계로 에제이카 주의 상류층을 장악하고 있습니다. 늙지 않는 육체와 막대한 부를 가졌으나, 태양 아래서는 한 줌의 재가 되며 끝없는 갈증에 시달립니다.\n\n라이칸스로프 (Lycanthrope)\n인간의 가죽을 쓴 야수들입니다.\n도시의 어둠 속에 적응하여 마피아, 갱단, 용병이 되었으며, 패밀리라 불리는 피보다 진한 유대로 묶인 조직을 이룹니다. 수화를 통해 헌터와 뱀파이어를 압도하는 신체 능력을 발휘하지만, 내면의 야성에 잠식되어 광증에 빠질 위험을 항상 안고 살아갑니다.\n\n헌터 (Hunter)\n연금술로 괴물을 사냥하는 인간들입니다.\n낮에는 평범한 시민으로 위장해 살아가지만, 밤이 되면 몸에 새긴 연금 회로를 불태우며 괴물들과 맞섭니다. 볼트라 불리는 문명으로 위장된 거점을 중심으로 활동하며, 인류를 구하기 위해 싸우면서도 역설적으로 괴물이 저지른 짓을 덮어주는 청소부 노릇을 해야만 합니다.', '', '', 2),
(1, '배경: 에제이카 주', 'text', '달그늘은 달빛이 닿는 곳 어디든 존재하지만, Under the Moonveil은 그 중에서도 현대 미국의 가상의 지역인 에제이카 주를 무대로 합니다.\n이곳은 가장 많은 정통 흡혈귀 클랜이 모여있고, 가장 거대한 라이칸스로프 패밀리가 자리하고 있으며, 헌터들에게 가장 중요한 볼트들이 모여있는 도시입니다.\n\n에제이카 주는 미국의 축소판이나 다름없습니다.\n꿈을 꾸는 이들에게는 천사가 나팔을 부는 기회의 도시이지만, 그 꿈을 붙잡지 못한 이들이 머무는 슬럼은 지옥이며,\n어느 주보다 공명하고 정대한 법치를 따르지만, 온갖 불법과 외도를 걷는 이들이 도처에 도사리며,\n온갖 인간 군상이 모여 어울리는 것처럼 보이지만, 그 틈에 숨은 괴물들이 서로를 찌를 기회를 엿보고 있는 도시입니다.', '', '', 3),
(1, '달그늘 아래의 삶', 'text', '이중생활\n\n문명은 끝없이 발전하여 밤의 어둠을 몰아내고, 현대 사회에는 괴물들이 숨어들 틈이 없는 것처럼 보이지만, 달그늘 아래의 주민들은 모두 각자의 방식으로 인간들 사이에 숨어 현재를 살아갑니다.\n\n뱀파이어는 낮에는 관 속에서 잠들지만, 밤이 되면 펜트하우스에서 도시를 내려다보며 권력을 행사합니다.\n라이칸스로프는 패밀리의 일원로서 낮에는 인간들, 밤에는 다른 세력들에 맞서 거리에 자신들이 머물 자리를 세워 나갑니다.\n헌터는 낮에는 카페에서 라떼를 만들거나 회사에 출근하지만, 밤이 되면 연금 회로를 불태우며 괴물을 사냥합니다.\n\n이들에게 가장 중요한 것은 \'일상\'을 유지하는 것입니다. 정체가 들통나는 순간, 사냥꾼은 사냥감이 되고 지배자는 추방당합니다.\n\n영원한 긴장\n\n이 변화무쌍한 도시에서 흡혈귀 클랜들의 적통은 의심받고, 라이칸스로프 패밀리 간의 유대는 무너지고, 볼트에 모인 헌터들은 비의의 완성을 바라지 않습니다.\n\n세 세력은 표면적으로 평화를 유지하지만, 어제의 동맹이 오늘의 적이 되는 위태로운 균형 위에서 살아갑니다.\n달 그림자 아래에서 가장 밝게 빛나는 에제이카 주를 향해 부나방처럼 모여든 달그늘 아래의 주민들은 스스로의 목표를 따라 에제이카 주의 밤을 거닙니다.\n\n결국 달그늘 아래의 세계를 알게 된 이들은 서로와 어울리는 수밖에 없습니다.\n그 방법이 서로를 해치는 것뿐이라 하더라도….', '', '', 4),
(1, '베일 (The Veil)', 'text', '달그늘은 뱀파이어, 라이칸스로프, 헌터들이 살아가는 또 하나의 세계를 의미하지만, 그 어원은 위대한 초월자 카인이 그를 배신한 세 명의 선지자에게 내린 베일의 저주에서 기인합니다.\n이 저주는 단순한 마법이 아니라 세계를 관통하는 물리 법칙처럼 작용하며, 달그늘 아래의 존재들이 평범한 세상에 드러나는 것을 철저히 차단하여 이들이 살아가는 인지의 틈을 만듭니다.\n하지만 오랜 시간이 흐른 현재는 오히려 세상의 시선에서 달그늘 아래의 주민들을 숨겨주는 베일이 되었습니다.\n\n카인의 저주\n\n평범한 인간은 결코 달그늘 아래의 존재들을 올바르게 인식할 수 없습니다.\n카인의 저주로 인해 인간은 초자연적인 현상을 목격하는 즉시 무의식적으로 가장 합리적인 \'현실의 정보\'로 치환하여 받아들입니다.\n라이칸스로프의 포효는 맹견의 짖는 소리로, 뱀파이어의 권능은 가스 누출로 인한 집단 환각으로, 헌터와 괴물의 전투는 갱단의 총격전으로 기록됩니다.\n달그늘 아래의 주민들이 아무리 발버둥 쳐도, 그들의 행위는 결코 인류의 역사에 \'진실\'로 남지 못합니다.\n\n에스더의 조율\n\n하지만 저주로도 감춰지지 않는 거대한 균열이 발생할 때가 있습니다. 에사우가 도심을 먹어치우거나, 한낮에 전쟁이 벌어지는 경우입니다.\n이때 탐욕의 진혈종 에스더가 개입합니다. 그녀는 달그늘의 중재자로서 광역 정신 간섭과 미디어 통제를 수행하여, 도시 전체의 인식을 \'대형 재난\'이나 \'사고\' 쪽으로 유도하여 파국을 막습니다.\n그녀의 목적은 달그늘의 수호나 지배보다는 자신들의 터전을 지키기 위한 정원 가꾸기에 가깝습니다.\n\nWRO의 뒷수습\n\n인식이 조작되었다면, 남은 것은 물리적 증거입니다.\n세계구제기구(WRO)는 현장에 봉쇄선을 치고, 부서진 잔해를 치우고, 가짜 보고서를 작성하여 관공서와 언론을 입막음합니다.\n헌터들은 세상을 구하기 위해 괴물과 싸우지만, 역설적으로 세상의 평온을 위해 괴물이 저지른 짓을 덮어주는 청소부 노릇을 해야만 합니다.', '', '', 5),
(2, '개요', 'text', '블랙 후프는 최초의 5대 부족 중 4개의 부족이 연합하여 결성한 현대적 마피아 연맹(Syndicate)입니다.\n이들은 과거 숲에 은거하던 드루이드(이케니)와 결별하고, 도시의 문명과 어둠 속에 적응하여 에제이카 주 전역을 지배하는 거대 범죄 조직으로 거듭났습니다.\n조직은 5개의 \'패밀리(Family)\'로 나뉘어 있으며, 각 패밀리는 야수의 신체 부위에 비유되는 고유한 역할과 직책을 수행합니다.', '', '', 1),
(2, '블랙후프 패밀리 (The Blackhoof)', 'text', 'Symbol: Bison / Role: Capo\n\"야수의 단단한 발굽, 앞서가는 개척자들\"\n\n직책: 카포 (Capo / 운영자)\n\n조직의 \'심장\'이자 \'다리\'인 본가(Main Family)입니다.\n들소의 지칠 줄 모르는 지구력으로 에제이카 주 전역으로 뻗어 나가는 운송과 물류(Logistics) 루트를 장악하고 있습니다.\n조직 전체의 자금 흐름과 사업 방향을 결정하며, 멈추지 않는 엔진처럼 조직을 이끌고 나가는 실세들입니다.\n\n특징\n- 물류 장악: 트럭킹, 해운, 철도 운송망을 통해 합법/불법 물자를 유통합니다.\n- 리더십: 연맹의 창립 가문으로서 다른 패밀리들을 조율하고 지휘합니다.\n- 성향: 묵직함, 추진력, 개척자 정신.', '', '', 2),
(2, '윈터팽 패밀리 (The Winterfang)', 'text', 'Symbol: Wolf / Role: Enforcer\n\"야수의 날카로운 이빨, 투쟁에 앞장서는 집행자들\"\n\n직책: 인포서 (Enforcer / 행동대장)\n\n조직의 \'칼\'이자 \'사냥개\'입니다.\n가장 호전적인 늑대들로 구성되어 있으며, 조직의 무력이 필요한 곳에 최우선으로 투입됩니다.\n적대 세력과의 전쟁, 청부 살인, 채무 징수 등 가장 거칠고 피 튀기는 현장에는 언제나 그들이 있습니다.\n무리(Pack) 지어 사냥하는 늑대처럼 완벽한 팀워크로 타겟을 물어뜯습니다.\n\n특징\n- 무력 행사: 해결사, 용병, 사설 경호 등 전투 관련 업무를 전담합니다.\n- 충성심: 조직에 대한 충성심이 가장 강하며, 배신자를 용납하지 않습니다.\n- 성향: 호전성, 팀워크, 잔혹함.', '', '', 3),
(2, '아이언포 패밀리 (The Ironpaw)', 'text', 'Symbol: Bear / Role: Reggente\n\"야수의 견고한 가죽, 규율을 수호하는 감찰관들\"\n\n직책: 레젠테 (Reggente / 섭정 & 감찰관)\n\n조직의 \'방패\'이자 \'규율\'입니다.\n이들은 압도적인 피지컬과 방어력으로 조직의 주요 거점(카지노, 금고)을 철통같이 방어합니다.\n또한 우직하고 배신을 모르는 성품 덕분에, 조직 내부의 배신자를 색출하고 단죄하는 \'내부 감찰\'의 권한을 가집니다.\n조직원들이 가장 두려워하는 것은 적이 아니라, 묵묵히 다가와 어깨에 손을 올리는 레젠테들입니다.\n\n특징\n- 절대 방어: 어떤 공격에도 물러서지 않는 탱커 역할을 수행합니다.\n- 내부 통제: 조직의 규율(Omertà)을 어긴 자를 처형하거나 징계합니다.\n- 성향: 원칙주의, 과묵함, 압도적 무력.', '', '', 4),
(2, '탱글테일 패밀리 (The Tangletail)', 'text', 'Symbol: Rat / Role: Fixer\n\"야수의 예민한 신경망, 모든 곳에 얽혀있는 설계자들\"\n\n직책: 픽서 (Fixer / 설계자)\n\n조직의 \'눈\'이자 \'설계자\'입니다.\n도시의 가장 낮은 하수구부터 가장 높은 펜트하우스의 환풍구까지, 이들의 정보망이 닿지 않는 곳은 없습니다.\n단순히 정보를 파는 것을 넘어, 정보를 이용해 판을 짜고, 경찰을 매수하고, 증거를 인멸하여 조직이 움직일 길을 닦아놓습니다.\n\n에제이카 시의 가장 큰 지하 경매장인 버로우의 주최자들이기도 합니다.\n\n특징\n- 정보 수집: 도청, 해킹, 미행을 통해 도시의 비밀을 수집합니다.\n- 사태 해결: 사고가 터졌을 때 뒷수습을 하거나 여론을 조작합니다.\n- 성향: 교활함, 은밀함, 기회주의.', '', '', 5),
(2, '골드메인 패밀리 (The Goldmane)', 'text', 'Symbol: Lion / Role: Consigliere\n\"야수의 빛나는 갈기, 정재계를 주무르는 참모들\"\n\n직책: 콘실리에리 (Consigliere / 고문)\n\n조직의 \'머리\'이자 \'얼굴\'입니다.\n피 냄새 대신 최고급 향수 냄새를 풍기는 이들은 법원과 의회, 사교계에서 활동합니다.\n조직의 검은 돈을 세탁하여 합법적인 자금으로 바꾸고, 법률적인 공격을 방어하며, 정재계 로비를 통해 블랙 후프를 \'필요악\'으로 포장하는 엘리트 참모진입니다.\n\n특징\n- 대외 협력: 뱀파이어 클랜이나 인간 권력자들과의 협상을 담당합니다.\n- 자금 세탁: 페이퍼 컴퍼니와 투자를 통해 자금의 출처를 숨깁니다.\n- 성향: 오만함, 지적 능력, 귀족주의.', '', '', 6),
(2, '이케니 (Iceni Family)', 'text', 'Alias: The Druids / Keepers of the Old Ways\n\"이빨을 버리고 지혜를 취한 숲의 현자들\"\n\n역할: 드루이드 (Druid / 영적 지도자)\n\n블랙 후프와 달리 도시 문명을 거부하고 숲을 지키는 보수적인 은둔자들입니다.\n과거 평화를 위해 스스로 \'고대종의 인자(야수의 힘)\'를 포기했기에, 이들에게서는 더 이상 강력한 괴물이 태어나지 않습니다.\n하지만 그 대가로 야수의 본능을 제어하는 지혜와, \'진실의 마도서\'를 수호하는 권한을 얻었습니다.\n블랙 후프는 이들을 시대착오적이라 비웃지만, 야수의 \'광증(Rabies)\'을 치료할 수 있는 유일한 치유사이기에 그들을 존중할 수밖에 없습니다.', '', '', 7),
(3, '개요', 'text', '달그늘은 달빛이 닿는 곳 어디든 존재하지만, Under the Moonveil은 그 중에서도 현대 미국의 가상의 지역인 에제이카 주를 무대로 합니다.\n이곳은 가장 많은 정통 흡혈귀 클랜이 모여있고, 가장 거대한 라이칸스로프 패밀리가 자리하고 있으며, 헌터들에게 가장 중요한 볼트들이 모여있는 도시입니다.\n\n에제이카 주는 미국의 축소판이나 다름없습니다.\n꿈을 꾸는 이들에게는 천사가 나팔을 부는 기회의 도시이지만, 그 꿈을 붙잡지 못한 이들이 머무는 슬럼은 지옥이며,\n어느 주보다 공명하고 정대한 법치를 따르지만, 온갖 불법과 외도를 걷는 이들이 도처에 도사리며,\n온갖 인간 군상이 모여 어울리는 것처럼 보이지만, 그 틈에 숨은 괴물들이 서로를 찌를 기회를 엿보고 있는 도시입니다.', '', '', 1),
(3, '이중생활', 'text', '뱀파이어는 낮에는 관 속에서 잠들지만, 밤이 되면 펜트하우스에서 도시를 내려다보며 권력을 행사합니다.\n라이칸스로프는 패밀리의 일원로서 낮에는 인간들, 밤에는 다른 세력들에 맞서 거리에 자신들이 머물 자리를 세워 나갑니다.\n헌터는 낮에는 카페에서 라떼를 만들거나 회사에 출근하지만, 밤이 되면 연금 회로를 불태우며 괴물을 사냥합니다.\n\n이들에게 가장 중요한 것은 \'일상\'을 유지하는 것입니다. 정체가 들통나는 순간, 사냥꾼은 사냥감이 되고 지배자는 추방당합니다.', '', '', 2),
(3, '영원한 긴장', 'text', '이 변화무쌍한 도시에서 흡혈귀 클랜들의 적통은 의심받고, 라이칸스로프 패밀리 간의 유대는 무너지고, 볼트에 모인 헌터들은 비의의 완성을 바라지 않습니다.\n\n세 세력은 표면적으로 평화를 유지하지만, 어제의 동맹이 오늘의 적이 되는 위태로운 균형 위에서 살아갑니다.\n달 그림자 아래에서 가장 밝게 빛나는 에제이카 주를 향해 부나방처럼 모여든 달그늘 아래의 주민들은 스스로의 목표를 따라 에제이카 주의 밤을 거닙니다.\n\n결국 달그늘 아래의 세계를 알게 된 이들은 서로와 어울리는 수밖에 없습니다.\n그 방법이 서로를 해치는 것뿐이라 하더라도….', '', '', 3);

INSERT INTO `mg_lore_era` (`le_name`, `le_period`, `le_order`, `le_use`) VALUES
('신화 시대 (The Mythic Age)', '기원 불명 ~ 대홍수', 0, 1),
('암흑 시대 (The Dark Age)', '5세기 ~ 15세기', 1, 1),
('각성의 시대 (The Awakening)', '15세기 ~ 16세기', 2, 1),
('언령의 계약 (The Verbal Covenant)', '1553년', 3, 1),
('산업화 시대 (The Industrial Age)', '18세기 ~ 19세기 말', 4, 1),
('근현대 (Modern Era)', '20세기 ~ 현재', 5, 1),
('현재 (Present)', 'Storm Eve', 6, 1);

INSERT INTO `mg_lore_event` (`le_id`, `lv_year`, `lv_title`, `lv_content`, `lv_image`, `lv_is_major`, `lv_order`, `lv_use`) VALUES
(1, '', '태초의 초월자 카인', '스스로를 카인이라 칭한 첫번째 자손이 인류와 함께하며 기적을 베풀던 시대. 인류도 초월자의 선도를 따라 달그늘과 낮의 구분이 존재하지 않았다.', '', 0, 1, 1),
(1, '', '대홍수와 3인의 선지자', '인류의 죄악을 씻어내기 위한 대홍수 이후, 카인은 생존한 인류를 이끌 3명의 선지자(의사, 교사, 환자)를 선택하여 자신의 지식을 전수한다.', '', 0, 2, 1),
(1, '', '배신의 밤 (The Night of Betrayal)', '환자의 간계로 세 선지자가 카인을 배신하고 살해. 이로써 달그늘(Moonveil)이 탄생했다.\n\n- 교사: 카인의 피를 탐하여 뱀파이어의 시조가 됨\n- 환자: 카인의 육체를 탐하여 라이칸스로프의 기원이 됨\n- 의사: 카인의 지식을 탐하여 마도서를 남김', '', 1, 3, 1),
(1, '', '질투의 원죄', '질투의 진혈종 레비아탄의 간계에 빠져, 오만의 진혈종 아벨이 인간들에게 토벌당하고 실전(失傳)됨.', '', 0, 4, 1),
(2, '5~10세기', '밤의 지배자들', '인류가 밤을 지배하지 못했던 시대, 뱀파이어들의 황금기. 낮의 세계조차 암흑에 빠진 이 시기는 달그늘 주민들, 그 중에서도 뱀파이어들에게 전성기였다.', '', 0, 1, 1),
(2, '10~13세기', '끓는 가마솥의 융성', '탐욕의 진혈종 에스더가 이끄는 \'끓는 가마솥\' 클랜이 최대 세력으로 부상. 종족을 초월하여 뱀파이어와 라이칸이 뒤섞인 거대한 사바트(Sabbat)가 시작된다.', '', 0, 2, 1),
(3, '15세기', '마도서의 발굴과 역병 의사', '의사가 남긴 다섯 마도서가 발견되기 시작. 이를 기반으로 미신과 신앙에 의지해 괴물에 대항하는 초기 헌터 집단 \'역병 의사\'가 태동한다.', '', 0, 1, 1),
(3, '1547년', '축제의 파국 (Catastrophe Sabbati)', '에스더의 초대로 사바트에 참석한 최초의 메기스투스(언령)가 괴물들의 광기에 충격을 받고 폭주. 달그늘 역사상 최악의 대학살이 발생한다.\n\n- 이 사건을 빌미로 이케니(드루이드)가 끓는 가마솥과의 전면전을 선포.\n- 탐욕 혈통의 뱀파이어와 다수의 라이칸이 사망하며 구세력 붕괴.', '', 1, 2, 1),
(4, '1553년', '삼자 계약의 체결', '공멸을 막기 위해 언령의 메기스투스, 에스더, 이케니 부족장이 맺은 절대적 구속력을 가진 계약.\n\n에스더 (탐욕)\n- 희생: 혈족 증식 포기\n- 획득: 영구 중재자 지위\n→ \'끓는 가마솥\'의 몰락과 중재자 등극\n\n이케니 (드루이드)\n- 희생: 고대종 인자 영구 봉인\n- 획득: 광증 치료 및 마도서 수호\n→ \'블랙 후프\' 등 5개 부족의 이탈\n\n메기스투스 (인류)\n- 희생: 마법 포기 및 은거\n- 목표: 인간의 자주권 확보\n→ 진실의 마도서 봉인 및 \'과학\'으로 선회', '', 1, 1, 1),
(5, '19세기', '강철의 안개', '산업 혁명과 함께 헌터들이 연금술을 기계 공학에 접목하기 시작. 마법(신비)이 아닌 기술(과학)로 괴물을 사냥하는 현대적 헌터의 시초가 됨.', '', 0, 1, 1),
(5, '19세기 말', '엘리자의 실험', '성녀 엘리자(색욕)가 뱀파이어, 라이칸, 인간의 장점을 합친 \'완전생물\' 창조를 시도. 이는 레비아탄의 질투를 사 훗날 그녀가 파멸하는 원인이 된다.', '', 0, 2, 1),
(6, '1920년대', '블랙 후프의 결성', '금주법 시대의 혼란을 틈타, 키건이 이케니와 결별한 5개 부족을 규합하여 기업형 마피아 연맹 \'블랙후프\'를 창설. 러스티 네일을 거점으로 뒷세계를 장악함.', '', 0, 1, 1),
(6, '1960년대', '붉게 지는 성녀 (Red Dead Saint)', '레비아탄의 사주를 받은 키건이 성녀 엘리자를 암살. 도시가 붕괴 직전까지 갔으나, 에스더가 폐허 위에 중립 도시를 재건하고 \'세인트 일라이자\'라 명명함.', '', 1, 2, 1),
(6, '1980년대', 'WRO 공식 출범', '소련 군부를 조종하던 카르밀라(분노)가 최초의 볼트에 봉인되며 냉전은 종식을 향하기 시작한다. 이를 계기로 헌터 조직이 통합되어 WRO가 공식 출범한다.', '', 1, 3, 1),
(6, '1998년', '비요른의 최후와 인그레이브', '메기스투스 스털링이 아이언포 패밀리의 고대종(곰)을 사냥하여 최초의 인그레이브를 제련. 헌터의 무력이 괴물들을 위협하는 수준에 도달함.\n고대종을 잃은 아이언포는 세력이 약화되었으나, 돈나 시그리드의 주도 아래 블랙 후프 내 감찰(레젠테) 역할을 자처하며 재건 중이다.', '', 0, 4, 1),
(6, '2015년', '에쉰베이(Eshin Bay) 완공', '거대 자본과 기술이 집약된 신도심 \'에쉰베이\' 완공. 구도심의 낡은 질서를 대체하는 새로운 탐욕의 상징이 세워짐.', '', 0, 5, 1),
(7, 'NOW', '폭풍전야', '에제이카 주를 둘러싼 위태로운 평화가 흔들리고 있다.\n\n- 오만의 진혈(아벨)이 경매장 \'더 버로우\'에 등장\n- 식탐(에사우)의 수면기 종료 임박 징후\n- 새로운 메기스투스들의 각성', '', 1, 1, 1);

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

-- 14.3 프롬프트 시드 데이터
INSERT INTO `mg_prompt` (`bo_table`, `pm_title`, `pm_content`, `pm_cycle`, `pm_mode`, `pm_point`, `pm_bonus_point`, `pm_bonus_count`, `pm_material_id`, `pm_material_qty`, `pm_min_chars`, `pm_max_entry`, `pm_banner`, `pm_tags`, `pm_status`, `pm_start_date`, `pm_end_date`, `pm_created`, `pm_admin_id`) VALUES
('mission', '달빛 아래의 조우', '달그늘의 경계가 흐려지는 밤, 당신의 캐릭터는 뜻밖의 존재와 마주하게 됩니다.\n\n평소라면 절대 만날 수 없었을 상대 — 다른 세력, 다른 종족, 혹은 이미 사라진 줄 알았던 누군가.\n\n달빛이 만들어낸 이 기묘한 만남에서, 당신의 캐릭터는 어떤 선택을 하게 될까요?\n\n자유롭게 단편을 작성해 주세요. 다른 참여자의 캐릭터를 언급하는 것도 환영합니다.', 'weekly', 'review', 300, 500, 3, NULL, 0, 500, 1, NULL, '세계관,단편,주간', 'active', '2026-02-10 00:00:00', '2026-02-16 23:59:59', NOW(), 'admin'),
('mission', '잊혀진 서신', '오래된 서재의 먼지 낀 선반 사이에서, 당신의 캐릭터는 한 통의 서신을 발견합니다.\n\n낡은 봉투 안에는 누군가가 절박하게 남긴 편지가 들어 있습니다. 발신인은 불명, 하지만 내용은 현재의 달그늘과 깊은 관련이 있어 보입니다.\n\n이 서신의 내용은 무엇이며, 당신의 캐릭터는 이를 어떻게 받아들일까요?\n\n편지의 내용을 직접 작성하거나, 편지를 발견한 뒤의 이야기를 단편으로 풀어주세요.', 'weekly', 'review', 300, 500, 3, NULL, 0, 500, 1, NULL, '세계관,단편,주간', 'active', '2026-02-10 00:00:00', '2026-02-16 23:59:59', NOW(), 'admin'),
('mission', '달그늘 인물 열전', '당신이 가장 애정하는 달그늘의 NPC 또는 설정 속 인물에 대해 글을 작성해 주세요.\n\n해당 인물이 어떤 존재인지, 어떤 매력이 있는지, 당신의 캐릭터와는 어떤 관계인지 등을 자유롭게 서술합니다.\n\n기존 세계관에 등장하는 인물이어도 좋고, 당신이 상상한 설정 속 인물이어도 좋습니다.\n\n추천수 상위 3명에게 보너스 보상이 지급됩니다.', 'monthly', 'vote', 200, 800, 3, NULL, 0, 300, 1, NULL, '세계관,인물,월간,콘테스트', 'active', '2026-02-01 00:00:00', '2026-02-28 23:59:59', NOW(), 'admin'),
('mission', '겨울 끝자락의 소원', '긴 겨울이 끝나가고, 달그늘에도 서서히 봄의 기운이 찾아옵니다.\n\n계절이 바뀌는 이 시기, 당신의 캐릭터가 품고 있는 소원은 무엇인가요?\n\n짧은 독백, 일기, 편지, 단편 등 형식 자유. 캐릭터의 내면을 들여다보는 글을 작성해 주세요.\n\n제출 즉시 자동으로 보상이 지급됩니다.', 'event', 'auto', 200, 0, 0, NULL, 0, 200, 1, NULL, '캐릭터,감성,자유형식', 'active', '2026-02-01 00:00:00', '2026-02-28 23:59:59', NOW(), 'admin'),
('mission', '그림자 속의 대화', '달그늘의 뒷골목, 혹은 숲의 깊은 곳에서 벌어지는 비밀스러운 대화.\n\n당신의 캐릭터가 누군가와 나누는 은밀한 이야기를 작성해 주세요.\n\n대화체 위주의 단편을 권장하며, 역극 파트너가 있다면 합작도 환영합니다.\n\n※ 이 프롬프트는 종료되었습니다.', 'weekly', 'review', 300, 500, 2, NULL, 0, 400, 1, NULL, '세계관,대화,주간', 'closed', '2026-02-03 00:00:00', '2026-02-09 23:59:59', NOW(), 'admin');

-- ======================================
-- 15. 캐릭터 관계 시스템
-- ======================================

-- 15.1 관계 아이콘 정의
CREATE TABLE IF NOT EXISTS `mg_relation_icon` (
    `ri_id` int NOT NULL AUTO_INCREMENT,
    `ri_category` varchar(30) NOT NULL COMMENT '카테고리 (love, friendship, family, rival, mentor, etc)',
    `ri_icon` varchar(20) NOT NULL COMMENT '이모지/아이콘',
    `ri_label` varchar(50) NOT NULL COMMENT '표시명',
    `ri_color` varchar(7) NOT NULL DEFAULT '#95a5a6' COMMENT '엣지 기본 색상',
    `ri_width` tinyint NOT NULL DEFAULT 2 COMMENT '엣지 기본 굵기',
    `ri_image` varchar(200) DEFAULT NULL COMMENT '커스텀 이미지 경로',
    `ri_order` int NOT NULL DEFAULT 0 COMMENT '정렬순',
    `ri_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`ri_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='관계 아이콘';

-- 15.2 기본 아이콘 데이터
INSERT INTO `mg_relation_icon` (`ri_category`, `ri_icon`, `ri_label`, `ri_color`, `ri_width`, `ri_order`) VALUES
('love', '♡', '연인', '#e74c3c', 2, 1),
('love', '♡♡', '깊은 사랑', '#c0392b', 3, 2),
('friendship', '☆', '친구', '#3498db', 2, 3),
('friendship', '★', '절친', '#2980b9', 2, 4),
('family', '🏠', '가족', '#27ae60', 2, 5),
('rival', '⚔', '라이벌', '#e67e22', 2, 6),
('mentor', '📖', '스승/제자', '#9b59b6', 2, 7),
('etc', '🔗', '동료/지인', '#95a5a6', 2, 8),
('etc', '❓', '복잡한 관계', '#f39c12', 2, 9);

-- 15.3 관계 데이터
CREATE TABLE IF NOT EXISTS `mg_relation` (
    `cr_id` int NOT NULL AUTO_INCREMENT,
    `ch_id_a` int NOT NULL COMMENT '캐릭터A (항상 작은쪽 ID)',
    `ch_id_b` int NOT NULL COMMENT '캐릭터B (항상 큰쪽 ID)',
    `ch_id_from` int NOT NULL COMMENT '신청자 캐릭터 ID',
    `ri_id` int NOT NULL COMMENT '아이콘 ID',
    `cr_label_a` varchar(50) NOT NULL COMMENT 'A쪽 관계명',
    `cr_label_b` varchar(50) DEFAULT NULL COMMENT 'B쪽 관계명 (NULL이면 A와 동일)',
    `cr_icon_a` varchar(20) DEFAULT NULL COMMENT 'A쪽 개별 아이콘',
    `cr_icon_b` varchar(20) DEFAULT NULL COMMENT 'B쪽 개별 아이콘',
    `cr_memo_a` text COMMENT 'A쪽 메모',
    `cr_memo_b` text COMMENT 'B쪽 메모',
    `cr_color` varchar(7) DEFAULT NULL COMMENT '커스텀 엣지 색상 (NULL이면 아이콘 기본색)',
    `cr_status` enum('pending','active','rejected') NOT NULL DEFAULT 'pending' COMMENT '상태',
    `cr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '신청일',
    `cr_accept_datetime` datetime DEFAULT NULL COMMENT '승인일',
    PRIMARY KEY (`cr_id`),
    UNIQUE KEY `idx_relation_pair` (`ch_id_a`, `ch_id_b`),
    INDEX `idx_relation_a` (`ch_id_a`, `cr_status`),
    INDEX `idx_relation_b` (`ch_id_b`, `cr_status`),
    INDEX `idx_relation_from` (`ch_id_from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='캐릭터 관계';

SET FOREIGN_KEY_CHECKS = 1;
