-- 인벤토리 선물 기능: mg_gift 테이블에 gf_type 컬럼 추가
-- shop = 상점에서 구매 선물, inventory = 인벤토리에서 보유 아이템 선물
SET @col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mg_gift' AND COLUMN_NAME = 'gf_type');
SET @sql = IF(@col = 0, 'ALTER TABLE mg_gift ADD COLUMN gf_type VARCHAR(20) NOT NULL DEFAULT \'shop\' AFTER si_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
