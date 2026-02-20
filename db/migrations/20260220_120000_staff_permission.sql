-- 스태프 권한 시스템 (M5)
-- 역할 기반 관리자 권한 배정 + g5_auth 동기화

CREATE TABLE IF NOT EXISTS `mg_staff_role` (
  `sr_id` int NOT NULL AUTO_INCREMENT,
  `sr_name` varchar(100) NOT NULL,
  `sr_description` text,
  `sr_permissions` text NOT NULL COMMENT 'JSON: {"mg_lore":"r,w,d","mg_character":"r,w,d",...}',
  `sr_color` varchar(7) DEFAULT '#f59f0a' COMMENT '뱃지 색상',
  `sr_sort` int DEFAULT '0',
  `sr_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `sr_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `mg_staff_member` (
  `sm_id` int NOT NULL AUTO_INCREMENT,
  `mb_id` varchar(20) NOT NULL,
  `sr_id` int NOT NULL,
  `sm_created` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`sm_id`),
  UNIQUE KEY `uk_mb_role` (`mb_id`,`sr_id`),
  KEY `idx_mb` (`mb_id`),
  KEY `idx_role` (`sr_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
