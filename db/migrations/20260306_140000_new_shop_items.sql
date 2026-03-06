-- 신규 상점 아이템 9종 + DDL 변경
-- 역극 상단 노출, 파견 부스터, 글자수 확장, 업적 슬롯, 의뢰 부스터, 이름표

-- ──────────────────────────────────────────────
-- DDL: mg_rp_thread.rt_pinned_until 컬럼 추가
-- ──────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_rp_thread' AND COLUMN_NAME = 'rt_pinned_until');
SET @ddl = IF(@col_exists = 0,
  'ALTER TABLE mg_rp_thread ADD COLUMN rt_pinned_until DATETIME NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ──────────────────────────────────────────────
-- DDL: mg_expedition_log.el_items_used 컬럼 추가
-- ──────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_expedition_log' AND COLUMN_NAME = 'el_items_used');
SET @ddl = IF(@col_exists = 0,
  'ALTER TABLE mg_expedition_log ADD COLUMN el_items_used VARCHAR(500) NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ──────────────────────────────────────────────
-- DDL: mg_user_achievement_display 슬롯 6~8 추가
-- ──────────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_user_achievement_display' AND COLUMN_NAME = 'slot_6');
SET @ddl = IF(@col_exists = 0,
  'ALTER TABLE mg_user_achievement_display ADD COLUMN slot_6 int NULL DEFAULT NULL, ADD COLUMN slot_7 int NULL DEFAULT NULL, ADD COLUMN slot_8 int NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ──────────────────────────────────────────────
-- DDL: mg_shop_item.si_type ENUM 확장 (새 타입 9종)
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
       'nick_bg','etc')
  COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'etc' COMMENT '타입';

-- ──────────────────────────────────────────────
-- FIX: profile_effect/seal_effect 빈 타입 복구
-- (20260226_160000에서 ENUM 없이 UPDATE하여 빈 값이 됨)
-- ──────────────────────────────────────────────
UPDATE mg_shop_item SET si_type = 'profile_effect' WHERE si_type = '' AND si_effect LIKE '%vanta%' OR (si_type = '' AND si_name IN ('새 떼','안개','물결','구름','먹구름','글로브','네트워크','세포','나뭇가지','지형도','점 그리드','동심원','수면','빛 번짐'));
UPDATE mg_shop_item SET si_type = 'seal_effect' WHERE si_type = '' AND si_name LIKE '인장 배경:%';
UPDATE mg_item_active SET ia_type = 'profile_effect' WHERE ia_type = '';
UPDATE mg_item_active SET ia_type = 'seal_effect' WHERE ia_type = '' AND si_id IN (SELECT si_id FROM mg_shop_item WHERE si_type = 'seal_effect');

-- ──────────────────────────────────────────────
-- SEED: 상점 아이템 9종
-- ──────────────────────────────────────────────

-- 1) 역극 상단 노출권 (3일)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('rp_pin', '역극 상단 노출권 (3일)', '역극 목록에서 3일간 상단에 고정 노출됩니다.', 500, '', '{"duration_hours":72}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 2) 파견 시간 단축권
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('expedition_time', '파견 시간 단축권', '파견 시간을 30% 단축합니다 (1회 소모)', 300, '', '{"reduce_percent":30}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 3) 파견 보상 2배권
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('expedition_reward', '파견 보상 2배권', '파견 보상(포인트+재료)을 2배로 받습니다 (1회 소모)', 500, '', '{"reward_multi":2}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 4) 스태미나 반감권
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('expedition_stamina', '스태미나 반감권', '파견 스태미나 소모를 50% 절감합니다 (1회 소모)', 400, '', '{"stamina_reduce_percent":50}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 5) 파견 슬롯 추가권
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('expedition_slot', '파견 슬롯 추가권', '동시 파견 가능 수를 1개 추가합니다 (영구, 해제 불가)', 2000, '', '{"slots":1}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 6) 글자수 확장권
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('write_expand', '글자수 확장권', '게시글 글자 제한을 30000자로 확장합니다 (영구)', 1500, '', '{"max_chars":30000}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 7) 업적 쇼케이스 확장권
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('achievement_slot', '업적 쇼케이스 확장권', '업적 쇼케이스 슬롯을 1개 추가합니다 (영구, 최대 8개)', 1000, '', '{"slots":1}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 8) 의뢰 보상 부스터
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('concierge_boost', '의뢰 보상 부스터', '의뢰 완료 보상 포인트를 30% 추가로 받습니다 (1회 소모)', 400, '', '{"boost_percent":30}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 1, 1);

-- 9) 이름표 배경색 (앰버) — 장착형 (si_consumable=0)
INSERT IGNORE INTO mg_shop_item (si_type, si_name, si_desc, si_price, si_image, si_effect, si_use, sc_id, si_consumable, si_display) VALUES
('nick_bg', '이름표 배경색 (앰버)', '닉네임에 배경색을 적용합니다', 300, '', '{"nick_bg":"#f59f0a","nick_bg_opacity":0.2}', 1,
 COALESCE((SELECT sc_id FROM mg_shop_category WHERE sc_name = '이용권' LIMIT 1), 0), 0, 1);
