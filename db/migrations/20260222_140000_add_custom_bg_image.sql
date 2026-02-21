-- =============================================
-- 커스텀 배경 이미지 업로드 권한 시스템
-- =============================================

-- 1. ch_profile_bg_image 컬럼 추가
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_character' AND COLUMN_NAME = 'ch_profile_bg_image');
SET @ddl = IF(@col_exists = 0,
    'ALTER TABLE mg_character ADD COLUMN ch_profile_bg_image VARCHAR(255) NOT NULL DEFAULT \'\' AFTER ch_profile_bg_color',
    'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. 커스텀 배경 이미지 권한 상점 아이템 등록
INSERT INTO mg_shop_item (sc_id, si_name, si_type, si_price, si_desc, si_use, si_consumable, si_effect, si_order, si_datetime)
SELECT 1, '커스텀 배경 이미지', 'etc', 500,
    '프로필 배경에 원하는 이미지를 직접 업로드할 수 있는 권한입니다. Vanta 이펙트와 함께 사용할 수 있습니다.',
    1, 0, '{"perm":"bg_custom_upload"}', 0, NOW()
FROM DUAL WHERE NOT EXISTS (
    SELECT 1 FROM mg_shop_item WHERE si_effect LIKE '%bg_custom_upload%'
);
