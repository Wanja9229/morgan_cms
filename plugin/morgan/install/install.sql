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
    `ch_is_npc` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'NPC 여부',
    `side_id` int DEFAULT NULL COMMENT '세력 ID',
    `class_id` int DEFAULT NULL COMMENT '종족 ID',
    `ch_thumb` varchar(500) DEFAULT NULL COMMENT '두상 이미지',
    `ch_image` varchar(500) DEFAULT NULL COMMENT '전신 이미지',
    `ch_header` varchar(500) DEFAULT '' COMMENT '헤더/배너 이미지',
    `ch_profile_skin` varchar(50) DEFAULT '' COMMENT '프로필 스킨',
    `ch_profile_bg` varchar(50) DEFAULT '' COMMENT '프로필 배경 이펙트',
    `ch_profile_bg_color` varchar(7) NOT NULL DEFAULT '#f59f0a' COMMENT '프로필 배경색',
    `ch_profile_bg_image` varchar(255) NOT NULL DEFAULT '' COMMENT '커스텀 배경 이미지',
    `ch_exp` int NOT NULL DEFAULT 0 COMMENT '경험치',
    `ch_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    `ch_update` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '수정일',
    `ch_graph_layout` text COMMENT '관계도 레이아웃 (JSON)',
    `ch_title_prefix_id` int DEFAULT NULL COMMENT '접두칭호 ID',
    `ch_title_suffix_id` int DEFAULT NULL COMMENT '접미칭호 ID',
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
    `side_id` int DEFAULT 0 COMMENT '소속 진영 (0=공용)',
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
    `widget_x` int NOT NULL DEFAULT 0 COMMENT '가로 시작 위치 (0~)',
    `widget_y` int NOT NULL DEFAULT 0 COMMENT '세로 시작 위치 (0~)',
    `widget_w` int NOT NULL DEFAULT 6 COMMENT '가로 칸 수 (1~12)',
    `widget_h` int NOT NULL DEFAULT 2 COMMENT '세로 칸 수 (1~)',
    PRIMARY KEY (`widget_id`),
    INDEX `idx_row_id` (`row_id`),
    CONSTRAINT `fk_widget_row` FOREIGN KEY (`row_id`) REFERENCES `mg_main_row`(`row_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='메인 페이지 위젯';

-- 2.7 메인 페이지 시드 데이터
INSERT IGNORE INTO `mg_main_row` (`row_id`, `row_order`, `row_use`) VALUES (1, 1, 1);

INSERT IGNORE INTO `mg_main_widget` (`widget_id`, `row_id`, `widget_type`, `widget_order`, `widget_cols`, `widget_x`, `widget_y`, `widget_w`, `widget_h`, `widget_config`, `widget_use`) VALUES
(1, 1, 'text', 0, 12, 0, 0, 12, 2, '{"content":"환영합니다.","font_size":"base","font_weight":"normal","text_align":"center","padding":"normal","text_color":"#f2f3f5","bg_color":"#2b2d31"}', 1);

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
    `si_type` enum('title','badge','nick_color','nick_effect','profile_border','equip','emoticon_set','emoticon_reg','furniture','material','seal_bg','seal_frame','seal_hover','seal_effect','profile_skin','profile_bg','profile_effect','char_slot','concierge_extra','title_prefix','title_suffix','radio_song','radio_ment','relation_slot','concierge_direct_pick','rp_pin','expedition_time','expedition_reward','expedition_stamina','expedition_slot','write_expand','achievement_slot','concierge_boost','nick_bg','stamina_recover','battle_weapon','battle_armor','battle_accessory','battle_consumable','battle_skill_book','etc') NOT NULL DEFAULT 'etc' COMMENT '타입',
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
    UNIQUE KEY `uk_type_name` (`si_type`, `si_name`),
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
    `gf_type` varchar(20) NOT NULL DEFAULT 'shop' COMMENT '선물 유형 (shop/inventory)',
    `gf_message` varchar(200) DEFAULT NULL COMMENT '메시지',
    `gf_status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending' COMMENT '상태',
    `gf_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '선물 일시',
    PRIMARY KEY (`gf_id`),
    INDEX `idx_to_status` (`mb_id_to`, `gf_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='선물';

-- ======================================
-- 4. 샘플 데이터
-- ======================================

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
    `rt_pinned_until` datetime DEFAULT NULL COMMENT '상단 노출 만료일시',
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

-- 4.5 기본 상점 아이템 (중복 방지)
INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT 1, '황금 닉네임', '닉네임이 황금색으로 빛납니다', 500, 'nick_color', '{"nick_color":"#fbbf24"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '황금 닉네임' AND si_type = 'nick_color');

INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT 1, '무지개 효과', '닉네임에 무지개 효과가 적용됩니다', 1000, 'nick_effect', '{"nick_effect":"rainbow"}', 10, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '무지개 효과' AND si_type = 'nick_effect');

INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT 3, '파란 테두리', '프로필에 파란 테두리가 적용됩니다', 300, 'profile_border', '{"border_color":"#3b82f6","border_style":"solid"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '파란 테두리' AND si_type = 'profile_border');

INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT 1, '별 뱃지', '반짝이는 별 뱃지입니다', 200, 'badge', '{"badge_icon":"star","badge_color":"#fbbf24"}', -1, 0, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_name = '별 뱃지' AND si_type = 'badge');

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

-- qna (질문답변) - memo 스킨, 회원 글쓰기, 관리자만 댓글, 비밀글 활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'qna',
    `gr_id` = 'community',
    `bo_subject` = '질문답변',
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

-- log (로그(일반)) - basic 스킨, 회원 글쓰기/댓글, 비밀글 비활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'log',
    `gr_id` = 'community',
    `bo_subject` = '로그(일반)',
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

-- qa (문의) - memo 스킨, 회원 글쓰기/댓글, 비밀글 활성화
INSERT INTO `g5_board` SET
    `bo_table` = 'qa',
    `gr_id` = 'community',
    `bo_subject` = '문의',
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

-- gallery (로그(이미지)) - gallery 스킨, 회원 글쓰기/댓글, 이미지 목록
INSERT INTO `g5_board` SET
    `bo_table` = 'gallery',
    `gr_id` = 'community',
    `bo_subject` = '로그(이미지)',
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
    `bo_order` = 6,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `g5_write_qa` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `g5_write_gallery` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- 8. (상점 아이템 타입은 CREATE TABLE에서 전체 ENUM 정의됨)

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='개척 재료 종류';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='유저별 재료 보유량';

-- 유저별 노동력
CREATE TABLE IF NOT EXISTS `mg_user_stamina` (
    `us_id` int(11) NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `us_current` int(11) NOT NULL DEFAULT 10 COMMENT '현재 노동력',
    `us_max` int(11) NOT NULL DEFAULT 10 COMMENT '일일 최대',
    `us_recovered_today` int(11) NOT NULL DEFAULT 0 COMMENT '오늘 회복한 스태미나',
    `us_last_reset` date DEFAULT NULL COMMENT '마지막 리셋 날짜',
    PRIMARY KEY (`us_id`),
    UNIQUE KEY `mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='유저별 노동력';

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
    `fc_map_x` decimal(5,2) DEFAULT NULL COMMENT '맵 X좌표',
    `fc_map_y` decimal(5,2) DEFAULT NULL COMMENT '맵 Y좌표',
    `fc_stamina_cost` int(11) NOT NULL DEFAULT 0 COMMENT '필요 총 노동력',
    `fc_stamina_current` int(11) NOT NULL DEFAULT 0 COMMENT '현재 투입된 노동력',
    `fc_order` int(11) NOT NULL DEFAULT 0 COMMENT '표시 순서',
    `fc_complete_date` datetime DEFAULT NULL COMMENT '완공일',
    PRIMARY KEY (`fc_id`),
    KEY `fc_status` (`fc_status`),
    KEY `fc_order` (`fc_order`),
    KEY `fc_unlock` (`fc_unlock_type`, `fc_unlock_target`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='개척 시설';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='시설별 필요 재료';

-- 시설 재료 비용 시드 데이터 (우체국=fc_id 1, mt_id 1~6)
INSERT IGNORE INTO `mg_facility_material_cost` (`fc_id`, `mt_id`, `fmc_required`, `fmc_current`) VALUES
(1, 1, 10, 0),
(1, 2, 10, 0),
(1, 3, 10, 0),
(1, 4, 10, 0),
(1, 5, 10, 0),
(1, 6, 10, 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='시설 기여 기록';

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='시설 명예의 전당';

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
    `br_stamina` int NOT NULL DEFAULT 0 COMMENT '스태미나 회복',
    `br_bonus_500` int NOT NULL DEFAULT 0 COMMENT '500자 이상 보너스',
    `br_bonus_1000` int NOT NULL DEFAULT 0 COMMENT '1000자 이상 보너스',
    `br_bonus_image` int NOT NULL DEFAULT 0 COMMENT '이미지 첨부 보너스',
    `br_material_use` tinyint NOT NULL DEFAULT 0 COMMENT '재료 드롭 사용',
    `br_material_chance` int NOT NULL DEFAULT 30 COMMENT '드롭 확률 (0~100)',
    `br_material_list` text COMMENT '드롭 대상 재료 JSON ["wood","stone"]',
    `br_material_comment` text COMMENT '댓글 재료 드롭 설정 JSON',
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
    `rq_override_rwt_id` int DEFAULT NULL COMMENT '관리자 변경 보상 유형',
    `rq_override_point` int DEFAULT NULL COMMENT '관리자 변경 포인트',
    `rq_admin_note` varchar(255) DEFAULT NULL COMMENT '관리자 메모',
    PRIMARY KEY (`rq_id`),
    INDEX `idx_status` (`rq_status`, `rq_datetime`),
    INDEX `idx_mb_id` (`mb_id`),
    INDEX `idx_bo_wr` (`bo_table`, `wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='정산 대기열';

-- 개척 시설 시드 데이터 (예시 1개)
INSERT INTO `mg_facility` (`fc_name`, `fc_desc`, `fc_image`, `fc_icon`, `fc_status`, `fc_unlock_type`, `fc_unlock_target`, `fc_stamina_cost`, `fc_stamina_current`, `fc_order`, `fc_complete_date`) VALUES
('우체국', '우체국을 건설합니다. 익명으로 편지를 전달할 수 있게 됩니다.', '', 'envelope', 'building', 'board', 'vent', 1000, 0, 1, NULL)
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
INSERT IGNORE INTO `mg_achievement` (`ac_name`, `ac_desc`, `ac_icon`, `ac_category`, `ac_type`, `ac_condition`, `ac_reward`, `ac_rarity`, `ac_hidden`, `ac_order`, `ac_use`, `ac_datetime`) VALUES
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
    `slot_6` int DEFAULT NULL,
    `slot_7` int DEFAULT NULL,
    `slot_8` int DEFAULT NULL,
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
    `seal_bg_color` varchar(7) DEFAULT '' COMMENT '배경 색상',
    `seal_layout` text DEFAULT NULL COMMENT '그리드 레이아웃 JSON',
    `seal_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '최종 수정일',
    PRIMARY KEY (`seal_id`),
    UNIQUE KEY `idx_mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='인장 (시그니처 카드)';

-- (상점 아이템 타입은 CREATE TABLE에서 전체 ENUM 정의됨)

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
    `le_desc` text DEFAULT NULL COMMENT '시대 설명',
    `le_order` int NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `le_use` tinyint NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`le_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='타임라인 시대';

-- 13.5 타임라인 이벤트
CREATE TABLE IF NOT EXISTS `mg_lore_event` (
    `lv_id` int NOT NULL AUTO_INCREMENT,
    `le_id` int NOT NULL COMMENT '시대 ID',
    `la_id` int DEFAULT 0 COMMENT '연결 문서 ID',
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

-- 13.6 위키 시드 데이터 (비워둠 — 테넌트가 자체 세계관 설정)
-- lore_category, lore_article, lore_section, lore_era, lore_event 모두 빈 상태로 시작

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

-- 14.3 프롬프트 시드 데이터 (비워둠 — 테넌트가 자체 미션 설정)

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

-- 15.2 관계 아이콘 시드 (비워둠 — 테넌트 관리자가 직접 설정)

-- 15.3 관계 데이터
CREATE TABLE IF NOT EXISTS `mg_relation` (
    `cr_id` int NOT NULL AUTO_INCREMENT,
    `ch_id_a` int NOT NULL COMMENT '캐릭터A (항상 작은쪽 ID)',
    `ch_id_b` int NOT NULL COMMENT '캐릭터B (항상 큰쪽 ID)',
    `ch_id_from` int NOT NULL COMMENT '신청자 캐릭터 ID',
    `ri_id` int DEFAULT 0 COMMENT '아이콘 ID',
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

-- =====================================================
-- 탐색 파견 시스템 (Phase 19)
-- =====================================================

CREATE TABLE IF NOT EXISTS `mg_expedition_area` (
    `ea_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '파견지 ID',
    `ea_name` varchar(100) NOT NULL COMMENT '파견지 이름',
    `ea_desc` text COMMENT '설명',
    `ea_icon` varchar(200) DEFAULT NULL COMMENT '아이콘',
    `ea_stamina_cost` int(11) NOT NULL DEFAULT 2 COMMENT '필요 스태미나',
    `ea_duration` int(11) NOT NULL DEFAULT 60 COMMENT '소요 시간(분)',
    `ea_status` enum('active','hidden','locked') NOT NULL DEFAULT 'active' COMMENT '상태',
    `ea_unlock_facility` int(11) DEFAULT NULL COMMENT '해금 조건 시설 ID',
    `ea_partner_point` int(11) NOT NULL DEFAULT 10 COMMENT '파트너 보상 포인트',
    `ea_point_min` int DEFAULT 0,
    `ea_point_max` int DEFAULT 0,
    `ea_order` int(11) NOT NULL DEFAULT 0 COMMENT '정렬 순서',
    `ea_map_x` float DEFAULT NULL COMMENT '맵 X좌표 퍼센트',
    `ea_map_y` float DEFAULT NULL COMMENT '맵 Y좌표 퍼센트',
    `ea_image` varchar(255) DEFAULT NULL COMMENT '배경 이미지',
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
    UNIQUE KEY `uk_area_material` (`ea_id`, `mt_id`),
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
    `el_items_used` varchar(500) DEFAULT NULL COMMENT '사용된 소모품 JSON',
    PRIMARY KEY (`el_id`),
    INDEX `idx_expedition_mb` (`mb_id`, `el_status`),
    INDEX `idx_expedition_ch` (`ch_id`),
    INDEX `idx_expedition_partner` (`partner_ch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파견 기록';

-- =====================================================
-- 의뢰 매칭 시스템 (Phase 20)
-- =====================================================

CREATE TABLE IF NOT EXISTS `mg_concierge` (
    `cc_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '의뢰 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '의뢰자 회원 ID',
    `ch_id` int(11) NOT NULL COMMENT '의뢰자 캐릭터 ID',
    `cc_title` varchar(255) NOT NULL COMMENT '의뢰 제목',
    `cc_content` text NOT NULL COMMENT '의뢰 내용',
    `cc_type` enum('collaboration','illustration','novel','other') NOT NULL DEFAULT 'collaboration' COMMENT '의뢰 유형',
    `cc_max_members` int(11) NOT NULL DEFAULT 1 COMMENT '선정 인원',
    `cc_max_applicants` int(11) DEFAULT NULL COMMENT '지원 인원 상한 (NULL=무제한)',
    `cc_match_mode` enum('direct','lottery') NOT NULL DEFAULT 'direct' COMMENT '매칭 방식',
    `cc_point_total` int(11) NOT NULL DEFAULT 0 COMMENT '총 보상 포인트 (의뢰자 선불 차감)',
    `cc_reward_memo` varchar(200) DEFAULT NULL COMMENT '추가 보상 메모 (구두약속)',
    `cc_deadline` datetime NOT NULL COMMENT '모집 마감일',
    `cc_complete_deadline` datetime DEFAULT NULL COMMENT '수행 완료 기한',
    `cc_status` enum('recruiting','matched','completed','expired','cancelled','force_closed') NOT NULL DEFAULT 'recruiting' COMMENT '상태',
    `cc_force_completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '강제 완료 여부 (0=정상, 1=강제)',
    `cc_force_completed_by` varchar(20) DEFAULT NULL COMMENT '강제 완료 실행자 mb_id',
    `cc_highlight` datetime DEFAULT NULL COMMENT '하이라이트 만료 시각',
    `cc_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록일',
    PRIMARY KEY (`cc_id`),
    INDEX `idx_concierge_mb` (`mb_id`, `cc_status`),
    INDEX `idx_concierge_status` (`cc_status`, `cc_deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='의뢰';

CREATE TABLE IF NOT EXISTS `mg_concierge_apply` (
    `ca_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '지원 ID',
    `cc_id` int(11) NOT NULL COMMENT '의뢰 ID',
    `mb_id` varchar(20) NOT NULL COMMENT '지원자 회원 ID',
    `ch_id` int(11) NOT NULL COMMENT '지원자 캐릭터 ID',
    `ca_message` text COMMENT '지원 메시지',
    `ca_status` enum('pending','selected','rejected','force_closed') NOT NULL DEFAULT 'pending' COMMENT '상태',
    `ca_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '지원일',
    PRIMARY KEY (`ca_id`),
    INDEX `idx_apply_cc` (`cc_id`, `ca_status`),
    INDEX `idx_apply_mb` (`mb_id`),
    UNIQUE KEY `idx_apply_unique` (`cc_id`, `mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='의뢰 지원';

CREATE TABLE IF NOT EXISTS `mg_concierge_result` (
    `cr_id` int(11) NOT NULL AUTO_INCREMENT,
    `cc_id` int(11) NOT NULL COMMENT '의뢰 ID',
    `ca_id` int(11) NOT NULL COMMENT '지원(수행자) ID',
    `bo_table` varchar(20) NOT NULL COMMENT '게시판 테이블명',
    `wr_id` int(11) NOT NULL COMMENT '게시글 ID',
    `cr_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '완료일',
    PRIMARY KEY (`cr_id`),
    INDEX `idx_result_cc` (`cc_id`),
    INDEX `idx_result_post` (`bo_table`, `wr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='의뢰 완료 연결';

-- ======================================
-- 18. 마이그레이션 이력
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_migrations` (
    `mig_id` int NOT NULL AUTO_INCREMENT,
    `mig_file` varchar(200) NOT NULL,
    `mig_applied_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`mig_id`),
    UNIQUE KEY `idx_mig_file` (`mig_file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='마이그레이션 이력';

-- ======================================
-- 19. 스태프 권한 시스템
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_staff_role` (
    `sr_id` int NOT NULL AUTO_INCREMENT,
    `sr_name` varchar(100) NOT NULL,
    `sr_description` text,
    `sr_permissions` text NOT NULL COMMENT 'JSON: {"mg_lore":"r,w,d",...}',
    `sr_color` varchar(7) DEFAULT '#f59f0a' COMMENT '뱃지 색상',
    `sr_sort` int DEFAULT 0,
    `sr_created` datetime DEFAULT CURRENT_TIMESTAMP,
    `sr_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`sr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='스태프 역할';

CREATE TABLE IF NOT EXISTS `mg_staff_member` (
    `sm_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL,
    `sr_id` int NOT NULL,
    `sm_created` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`sm_id`),
    UNIQUE KEY `uk_mb_role` (`mb_id`, `sr_id`),
    INDEX `idx_mb` (`mb_id`),
    INDEX `idx_role` (`sr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='스태프 멤버';

-- ======================================
-- 20. 미니게임 시스템
-- ======================================

-- 20.1 운세뽑기
CREATE TABLE IF NOT EXISTS `mg_game_fortune` (
    `gf_id` int NOT NULL AUTO_INCREMENT,
    `gf_star` tinyint NOT NULL DEFAULT 1 COMMENT '별 개수 1~5',
    `gf_text` varchar(255) NOT NULL COMMENT '운세 텍스트',
    `gf_point` int NOT NULL DEFAULT 10 COMMENT '획득 포인트',
    `gf_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    `gf_sort` int NOT NULL DEFAULT 0,
    PRIMARY KEY (`gf_id`),
    UNIQUE KEY `uk_star_text` (`gf_star`, `gf_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='운세뽑기';

INSERT IGNORE INTO `mg_game_fortune` (`gf_star`, `gf_text`, `gf_point`, `gf_use`, `gf_sort`) VALUES
(1, '오늘은 조용히 쉬는 게 좋겠어요', 10, 1, 1),
(2, '작은 행운이 찾아올 수 있어요', 25, 1, 2),
(3, '좋은 소식이 들려올 조짐이에요', 50, 1, 3),
(4, '하는 일마다 술술 풀리겠어요', 100, 1, 4),
(5, '모든 일이 완벽하게 맞아떨어져요', 200, 1, 5);

-- 20.2 종이뽑기
CREATE TABLE IF NOT EXISTS `mg_game_lottery_prize` (
    `glp_id` int NOT NULL AUTO_INCREMENT,
    `glp_rank` tinyint NOT NULL DEFAULT 1 COMMENT '등수 1~5',
    `glp_name` varchar(50) NOT NULL DEFAULT '' COMMENT '상 이름',
    `glp_count` int NOT NULL DEFAULT 1 COMMENT '한 판당 개수',
    `glp_point` int NOT NULL DEFAULT 10 COMMENT '포인트 보상',
    `glp_item_id` int DEFAULT NULL COMMENT '상점 아이템 ID',
    `glp_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`glp_id`),
    INDEX `idx_rank` (`glp_rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='종이뽑기 등수';

CREATE TABLE IF NOT EXISTS `mg_game_lottery_board` (
    `glb_id` int NOT NULL AUTO_INCREMENT,
    `glb_size` int NOT NULL DEFAULT 100 COMMENT '판 크기',
    `glb_bonus_point` int NOT NULL DEFAULT 500 COMMENT '판 완성 보너스',
    `glb_bonus_item_id` int DEFAULT NULL COMMENT '완성 보너스 아이템',
    `glb_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    PRIMARY KEY (`glb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='종이뽑기 판';

CREATE TABLE IF NOT EXISTS `mg_game_lottery_user` (
    `glu_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `glb_id` int NOT NULL DEFAULT 1 COMMENT '현재 판 ID',
    `glu_picked` text COMMENT '뽑은 번호 JSON',
    `glu_count` int NOT NULL DEFAULT 0 COMMENT '뽑은 개수',
    `glu_completed_count` int NOT NULL DEFAULT 0 COMMENT '완료한 판 수',
    PRIMARY KEY (`glu_id`),
    UNIQUE KEY `idx_mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='종이뽑기 사용자';

INSERT IGNORE INTO `mg_game_lottery_board` (`glb_id`, `glb_size`, `glb_bonus_point`, `glb_use`) VALUES (1, 100, 500, 1);

INSERT IGNORE INTO `mg_game_lottery_prize` (`glp_rank`, `glp_name`, `glp_count`, `glp_point`, `glp_use`) VALUES
(1, '1등', 1, 500, 1),
(2, '2등', 3, 200, 1),
(3, '3등', 6, 100, 1),
(4, '4등', 15, 50, 1),
(5, '5등', 75, 10, 1);

-- ======================================
-- 21. 세계관 맵 지역
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_map_region` (
    `mr_id` int NOT NULL AUTO_INCREMENT,
    `mr_name` varchar(100) NOT NULL COMMENT '지역명',
    `mr_desc` text COMMENT '지역 설명',
    `mr_image` varchar(500) DEFAULT NULL COMMENT '지역 이미지 URL',
    `mr_map_x` float DEFAULT NULL COMMENT '맵 X좌표 (%)',
    `mr_map_y` float DEFAULT NULL COMMENT '맵 Y좌표 (%)',
    `mr_marker_style` varchar(20) DEFAULT 'pin' COMMENT '마커 스타일 (pin/circle/diamond/flag)',
    `mr_order` int DEFAULT 0 COMMENT '정렬 순서',
    `mr_use` tinyint(1) DEFAULT 1 COMMENT '사용 여부',
    `mr_created` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`mr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='세계관 맵 지역';

-- ======================================
-- 22. 칭호 뽑기 시스템
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_title_pool` (
    `tp_id` int NOT NULL AUTO_INCREMENT,
    `tp_type` enum('prefix','suffix') NOT NULL COMMENT '접두/접미',
    `tp_name` varchar(50) NOT NULL COMMENT '칭호 이름',
    `tp_use` tinyint(1) NOT NULL DEFAULT 1 COMMENT '사용 여부',
    `tp_order` int NOT NULL DEFAULT 0 COMMENT '정렬',
    PRIMARY KEY (`tp_id`),
    INDEX `idx_type_use` (`tp_type`, `tp_use`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='칭호 풀';

CREATE TABLE IF NOT EXISTS `mg_member_title` (
    `mt_id` int NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `tp_id` int NOT NULL COMMENT '칭호 풀 ID',
    `mt_datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '획득일',
    PRIMARY KEY (`mt_id`),
    UNIQUE KEY `uk_mb_tp` (`mb_id`, `tp_id`),
    INDEX `idx_mb_id` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='유저 보유 칭호';

CREATE TABLE IF NOT EXISTS `mg_title_setting` (
    `mb_id` varchar(20) NOT NULL COMMENT '회원 ID',
    `prefix_tp_id` int DEFAULT NULL COMMENT '접두칭호 ID',
    `suffix_tp_id` int DEFAULT NULL COMMENT '접미칭호 ID',
    PRIMARY KEY (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='프로필 칭호 설정';

-- ======================================
-- 23. 파견 이벤트 시스템
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_expedition_event` (
    `ee_id` int NOT NULL AUTO_INCREMENT,
    `ee_name` varchar(100) NOT NULL DEFAULT '',
    `ee_desc` text,
    `ee_icon` varchar(255) NOT NULL DEFAULT '',
    `ee_effect_type` enum('point_bonus','point_penalty','material_bonus','material_penalty','reward_loss') NOT NULL DEFAULT 'point_bonus',
    `ee_effect` JSON,
    `ee_order` int NOT NULL DEFAULT 0,
    `ee_created` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파견 이벤트';

CREATE TABLE IF NOT EXISTS `mg_expedition_event_area` (
    `eea_id` int NOT NULL AUTO_INCREMENT,
    `ea_id` int NOT NULL DEFAULT 0,
    `ee_id` int NOT NULL DEFAULT 0,
    `eea_chance` int NOT NULL DEFAULT 10,
    PRIMARY KEY (`eea_id`),
    INDEX `idx_ea_id` (`ea_id`),
    INDEX `idx_ee_id` (`ee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='파견 이벤트-지역 매핑';

-- ======================================
-- 24. 라디오 위젯
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_radio_config` (
    `config_id`       INT PRIMARY KEY DEFAULT 1,
    `is_active`       TINYINT NOT NULL DEFAULT 1,
    `play_mode`       ENUM('sequential','random') NOT NULL DEFAULT 'sequential',
    `weather_mode`    ENUM('api','manual') NOT NULL DEFAULT 'manual',
    `weather_city`    VARCHAR(100) NULL,
    `weather_api_key` VARCHAR(100) NULL,
    `manual_temp`     INT NULL,
    `manual_weather`  VARCHAR(20) NULL,
    `ment_mode`       ENUM('sequential','random') NOT NULL DEFAULT 'sequential',
    `ment_interval`   INT NOT NULL DEFAULT 12,
    `weather_cache`   JSON NULL,
    `weather_cached_at` DATETIME NULL,
    `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='라디오 설정';

INSERT IGNORE INTO `mg_radio_config` (`config_id`) VALUES (1);

CREATE TABLE IF NOT EXISTS `mg_radio_playlist` (
    `track_id`    INT AUTO_INCREMENT PRIMARY KEY,
    `youtube_url` VARCHAR(255) NOT NULL,
    `youtube_vid` VARCHAR(20) NOT NULL,
    `title`       VARCHAR(200) NOT NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `is_active`   TINYINT NOT NULL DEFAULT 1,
    `expires_at`  DATETIME NULL DEFAULT NULL,
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='라디오 플레이리스트';

CREATE TABLE IF NOT EXISTS `mg_radio_ments` (
    `ment_id`     INT AUTO_INCREMENT PRIMARY KEY,
    `content`     VARCHAR(200) NOT NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `is_active`   TINYINT NOT NULL DEFAULT 1,
    `expires_at`  DATETIME NULL DEFAULT NULL,
    `created_at`  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='라디오 멘트';

CREATE TABLE IF NOT EXISTS `mg_radio_requests` (
    `rr_id`         INT AUTO_INCREMENT PRIMARY KEY,
    `rr_type`       ENUM('song','ment') NOT NULL,
    `mb_id`         VARCHAR(255) NOT NULL,
    `rr_title`      VARCHAR(200) NOT NULL DEFAULT '',
    `rr_youtube_url` VARCHAR(500) DEFAULT NULL,
    `rr_youtube_vid` VARCHAR(20) DEFAULT NULL,
    `rr_content`    VARCHAR(200) DEFAULT NULL,
    `rr_status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `rr_duration_hours` INT NULL DEFAULT NULL,
    `rr_created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `rr_updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`rr_status`),
    INDEX `idx_mb` (`mb_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='라디오 신청';

-- 24.1 이용권 카테고리
INSERT INTO `mg_shop_category` (`sc_name`, `sc_desc`, `sc_icon`, `sc_order`) VALUES
('이용권', '시스템 이용권', 'ticket', 6)
ON DUPLICATE KEY UPDATE `sc_name` = VALUES(`sc_name`);

-- 24.2 라디오 신청권 상품
INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1),
       '노래 신청권', '라디오에 원하는 곡을 신청할 수 있습니다. 관리자 승인 후 플레이리스트에 반영됩니다.', 100, 'radio_song', '{"duration_hours":72}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'radio_song');

INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1),
       '라디오 멘트권', '라디오 멘트를 신청할 수 있습니다. 관리자 승인 후 반영됩니다. (200자 이내)', 100, 'radio_ment', '{"duration_hours":24}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'radio_ment');

INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1),
       '관계 슬롯 확장권', '특정 캐릭터의 관계 슬롯을 1개 추가합니다. 인벤토리에서 사용 시 캐릭터를 선택하여 적용합니다. (영구, 해제 불가)', 200, 'relation_slot', '{"slots":1}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'relation_slot');

INSERT INTO `mg_shop_item` (`sc_id`, `si_name`, `si_desc`, `si_price`, `si_type`, `si_effect`, `si_stock`, `si_consumable`, `si_display`, `si_use`)
SELECT (SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1),
       '의뢰 지목권', '추첨 대신 지원자를 직접 선택할 수 있습니다 (1회 소모)', 300, 'concierge_direct_pick', '{}', -1, 1, 1, 1
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'concierge_direct_pick');

-- 24.3 신규 시스템 아이템 9종
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'rp_pin', '역극 상단 노출권 (3일)', '역극 목록에서 3일간 상단에 고정 노출됩니다.', 500, '', '{"duration_hours":72}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'rp_pin');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'expedition_time', '파견 시간 단축권', '파견 시간을 30% 단축합니다 (1회 소모)', 300, '', '{"reduce_percent":30}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_time');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'expedition_reward', '파견 보상 2배권', '파견 보상(포인트+재료)을 2배로 받습니다 (1회 소모)', 500, '', '{"reward_multi":2}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_reward');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'expedition_stamina', '스태미나 반감권', '파견 스태미나 소모를 50% 절감합니다 (1회 소모)', 400, '', '{"stamina_reduce_percent":50}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_stamina');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'expedition_slot', '파견 슬롯 추가권', '동시 파견 가능 수를 1개 추가합니다 (영구, 해제 불가)', 2000, '', '{"slots":1}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'expedition_slot');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'write_expand', '글자수 확장권', '게시글 글자 제한을 30000자로 확장합니다 (영구)', 1500, '', '{"max_chars":30000}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'write_expand');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'achievement_slot', '업적 쇼케이스 확장권', '업적 쇼케이스 슬롯을 1개 추가합니다 (영구, 최대 8개)', 1000, '', '{"slots":1}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'achievement_slot');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'concierge_boost', '의뢰 보상 부스터', '의뢰 완료 보상 포인트를 30% 추가로 받습니다 (1회 소모)', 400, '', '{"boost_percent":30}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'concierge_boost');

INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'nick_bg', '이름표 배경색 (앰버)', '닉네임에 배경색을 적용합니다', 300, '', '{"nick_bg":"#f59f0a","nick_bg_opacity":0.2}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 0, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'nick_bg');

-- 스태미나 회복 물약
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'stamina_recover', '스태미나 회복 물약', '스태미나를 풀 충전합니다 (일일 상한 적용)', 500, '', '{}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'stamina_recover' AND si_name = '스태미나 회복 물약');

-- ======================================
-- 25. 히든 이벤트 시스템
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_hidden_event` (
    `event_id`        INT AUTO_INCREMENT PRIMARY KEY,
    `title`           VARCHAR(100) NOT NULL,
    `image_path`      VARCHAR(255) NOT NULL,
    `reward_type`     ENUM('point','material','stamina') DEFAULT 'point',
    `reward_id`       INT NULL COMMENT '재료 mt_id (reward_type=material)',
    `reward_amount`   INT NOT NULL DEFAULT 100,
    `probability`     DECIMAL(5,2) NOT NULL DEFAULT 5.00 COMMENT '출현 확률 (%)',
    `daily_limit`     INT DEFAULT 1 COMMENT '이벤트별 유저당 일일 수령',
    `daily_total`     INT DEFAULT 5 COMMENT '전체 일일 수령 한도',
    `active_from`     DATETIME NULL,
    `active_until`    DATETIME NULL,
    `is_active`       TINYINT DEFAULT 1,
    `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='히든 이벤트';

CREATE TABLE IF NOT EXISTS `mg_event_token` (
    `token_id`        VARCHAR(64) PRIMARY KEY,
    `event_id`        INT NOT NULL,
    `mb_id`           VARCHAR(50) NOT NULL,
    `issued_at`       DATETIME DEFAULT CURRENT_TIMESTAMP,
    `expires_at`      DATETIME NOT NULL,
    `claimed`         TINYINT DEFAULT 0,
    `claimed_at`      DATETIME NULL,
    INDEX `idx_mb_claimed` (`mb_id`, `claimed`),
    INDEX `idx_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='이벤트 토큰';

CREATE TABLE IF NOT EXISTS `mg_event_claim` (
    `claim_id`        INT AUTO_INCREMENT PRIMARY KEY,
    `mb_id`           VARCHAR(50) NOT NULL,
    `event_id`        INT NOT NULL,
    `reward_type`     VARCHAR(20) NOT NULL,
    `reward_amount`   INT NOT NULL,
    `claimed_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    `ip_address`      VARCHAR(45),
    INDEX `idx_mb_date` (`mb_id`, `claimed_at`),
    INDEX `idx_event_date` (`event_id`, `claimed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='이벤트 수령 로그';

CREATE TABLE IF NOT EXISTS `mg_event_suspicious` (
    `log_id`          INT AUTO_INCREMENT PRIMARY KEY,
    `mb_id`           VARCHAR(50) NOT NULL,
    `reason`          VARCHAR(100) NOT NULL,
    `details`         TEXT NULL,
    `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_mb` (`mb_id`),
    INDEX `idx_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='이벤트 의심 행동 로그';

-- ======================================
-- 26. 사이드바 위젯 설정
-- ======================================

CREATE TABLE IF NOT EXISTS `mg_user_widget` (
    `uw_id`           INT AUTO_INCREMENT PRIMARY KEY,
    `mb_id`           VARCHAR(20) NOT NULL,
    `widget_name`     VARCHAR(50) NOT NULL,
    `widget_order`    INT NOT NULL DEFAULT 0,
    `widget_visible`  TINYINT(1) NOT NULL DEFAULT 1,
    `widget_config`   TEXT DEFAULT NULL,
    `created_at`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_mb_widget` (`mb_id`, `widget_name`),
    KEY `idx_mb_order` (`mb_id`, `widget_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='유저 위젯 설정';

SET FOREIGN_KEY_CHECKS = 1;
