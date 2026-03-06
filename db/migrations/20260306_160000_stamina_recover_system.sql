-- 스태미나 회복 시스템: DDL + ENUM + 시드
-- us_recovered_today 컬럼, br_stamina 컬럼, reward_type ENUM 확장, stamina_recover 아이템

-- ──────────────────────────────────────────────
-- DDL: mg_user_stamina.us_recovered_today 컬럼 추가
-- ──────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_user_stamina' AND COLUMN_NAME = 'us_recovered_today');
SET @ddl = IF(@col_exists = 0,
  'ALTER TABLE mg_user_stamina ADD COLUMN us_recovered_today int NOT NULL DEFAULT 0 COMMENT ''오늘 회복한 스태미나''',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ──────────────────────────────────────────────
-- DDL: mg_board_reward.br_stamina 컬럼 추가
-- ──────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_board_reward' AND COLUMN_NAME = 'br_stamina');
SET @ddl = IF(@col_exists = 0,
  'ALTER TABLE mg_board_reward ADD COLUMN br_stamina int NOT NULL DEFAULT 0 COMMENT ''스태미나 회복'' AFTER br_point',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ──────────────────────────────────────────────
-- DDL: mg_hidden_event.reward_type ENUM 확장 (stamina 추가)
-- ──────────────────────────────────────────────
ALTER TABLE mg_hidden_event MODIFY COLUMN reward_type
  ENUM('point','material','stamina') DEFAULT 'point';

-- ──────────────────────────────────────────────
-- DDL: mg_shop_item.si_type ENUM 확장 (stamina_recover 추가)
-- ──────────────────────────────────────────────
ALTER TABLE mg_shop_item MODIFY COLUMN si_type
  ENUM('title','badge','nick_color','nick_effect','profile_border','equip',
       'emoticon_set','emoticon_reg','furniture','material',
       'seal_bg','seal_frame','seal_hover','seal_effect',
       'profile_skin','profile_bg','profile_effect','char_slot',
       'concierge_extra','title_prefix','title_suffix',
       'radio_song','radio_ment','relation_slot','concierge_direct_pick',
       'rp_pin','expedition_time','expedition_reward','expedition_stamina',
       'expedition_slot','write_expand','achievement_slot','concierge_boost',
       'nick_bg','stamina_recover','etc')
  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'etc' COMMENT '타입';

-- ──────────────────────────────────────────────
-- SEED: 스태미나 회복 물약
-- ──────────────────────────────────────────────
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display)
SELECT 'stamina_recover', '스태미나 회복 물약', '스태미나를 풀 충전합니다 (일일 상한 적용)', 500, '', '{}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM mg_shop_item WHERE si_type = 'stamina_recover' AND si_name = '스태미나 회복 물약');
